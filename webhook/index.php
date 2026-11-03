<?php
// Bitrequest webshop webhook: e-mails you when a checkout reports a payment.
//
// NOTIFICATION ONLY. Data relayed by the customer's browser can be forged by anyone:
// treat it as a heads-up and confirm the payment in your wallet before you ship.
// Lightning proxy callbacks are authenticated with CALLBACK_TOKEN, but still check
// the paid amount against your order.

const RECIPIENT = "";          // where notifications go, e.g. "orders@yourshop.com"
const SENDER = "";             // From address on your own domain, e.g. "noreply@yourshop.com"
const CALLBACK_TOKEN = "";     // optional, 32+ random chars; proxy callback_url: "https://yourshop.com/webhook/?token=<CALLBACK_TOKEN>"
const ALLOWED_ORIGINS = [];    // shop pages on other domains that may post here, e.g. ["https://yourshop.github.io"]; same origin always works
const MAX_MAILS_PER_HOUR = 20; // cap for unauthenticated (browser) notifications
const MAX_BODY_BYTES = 16384;
const STATE_FILE = "";         // rate limit / duplicate state, defaults to the system temp dir

header("Content-Type: application/json; charset=UTF-8");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store");

function respond($code, $message) {
	http_response_code($code);
	echo json_encode(["ok" => $code < 300, "message" => $message]);
	exit;
}

// Single-line, printable, length-capped text from untrusted input
function clean($value, $max = 200) {
	if (is_bool($value)) {
		return $value ? "true" : "false";
	}
	if (is_float($value)) {
		$value = rtrim(rtrim(sprintf("%.8F", $value), "0"), "."); // avoid "5.0E-6"
	}
	if (!is_scalar($value)) {
		return "";
	}
	$text = preg_replace("/[\p{Cc}\p{Cf}]+/u", " ", (string) $value);
	if ($text === null) {
		return ""; // invalid UTF-8
	}
	preg_match("/^.{0," . (int) $max . "}/us", trim($text), $match);
	return $match[0];
}

function field($array, $key, $max = 200) {
	return is_array($array) && array_key_exists($key, $array) ? clean($array[$key], $max) : "";
}

function same_origin() {
	$origin = parse_url($_SERVER["HTTP_ORIGIN"] ?? "");
	$host = $_SERVER["HTTP_HOST"] ?? "";
	if (!$origin || empty($origin["host"]) || $host === "") {
		return false;
	}
	$origin_host = $origin["host"] . (isset($origin["port"]) ? ":" . $origin["port"] : "");
	return strcasecmp($origin_host, $host) === 0;
}

// "null" (local files, sandboxed frames) is never allowed: any website can produce it
$origin = $_SERVER["HTTP_ORIGIN"] ?? "";
$cors_allowed = $origin !== "null" && in_array($origin, ALLOWED_ORIGINS, true);
if ($cors_allowed) {
	header("Access-Control-Allow-Origin: " . $origin);
	if (($_SERVER["REQUEST_METHOD"] ?? "") === "OPTIONS") {
		header("Access-Control-Allow-Methods: POST");
		header("Access-Control-Allow-Headers: Content-Type");
		header("Access-Control-Max-Age: 86400");
		http_response_code(204);
		exit;
	}
}
if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
	header("Allow: POST");
	respond(405, "method not allowed");
}
if (!filter_var(RECIPIENT, FILTER_VALIDATE_EMAIL) || !filter_var(SENDER, FILTER_VALIDATE_EMAIL)) {
	respond(500, "webhook not configured: set RECIPIENT and SENDER");
}
if ((int) ($_SERVER["CONTENT_LENGTH"] ?? 0) > MAX_BODY_BYTES) {
	respond(413, "payload too large");
}

if (isset($_GET["token"])) {
	// Lightning proxy callback (server to server, form-encoded)
	if (strlen(CALLBACK_TOKEN) < 32) {
		respond(500, "CALLBACK_TOKEN not set or shorter than 32 characters");
	}
	if (!is_string($_GET["token"]) || !hash_equals(CALLBACK_TOKEN, $_GET["token"])) {
		respond(403, "forbidden");
	}
	$verified = true;
	$pid = field($_POST, "pid", 64);
	$status = field($_POST, "status", 20);
	$hash = field($_POST, "hash", 128);
	$lines = [
		"Lightning proxy callback (token verified). The status comes from your node;",
		"check that the amount paid matches the order.",
		"",
		"Payment ID: " . $pid,
		"Status: " . $status,
		"Type: " . field($_POST, "rqtype", 20),
		"Invoice amount (msat): " . field($_POST, "amount", 30),
		"Amount paid (msat): " . field($_POST, "amount_paid", 30),
		"Payment hash: " . $hash,
		"Proxy: " . field($_POST, "proxy")
	];
	$dedupe_key = "ln|" . $pid . "|" . $status . "|" . $hash;
} else {
	// Relay from the checkout page. Requiring JSON forces a CORS preflight, which this script
	// only answers for ALLOWED_ORIGINS, so other websites can't make visitors' browsers post here.
	$content_type = strtolower(trim(explode(";", $_SERVER["CONTENT_TYPE"] ?? "")[0]));
	if ($content_type !== "application/json") {
		respond(415, "expected application/json");
	}
	if (!same_origin() && !$cors_allowed) {
		respond(403, "cross-origin request");
	}
	$raw = file_get_contents("php://input", false, null, 0, MAX_BODY_BYTES + 1);
	if ($raw === false || strlen($raw) > MAX_BODY_BYTES) {
		respond(413, "payload too large");
	}
	$payload = json_decode($raw, true);
	if (!is_array($payload) || !isset($payload["txdata"]) || !is_array($payload["txdata"])) {
		respond(400, "invalid payload");
	}
	$tx = $payload["txdata"];
	$data = $payload["data"] ?? null;
	$contact = $payload["contact"] ?? null;
	$verified = false;
	$pid = field($data, "pid", 64);
	$status = field($tx, "status", 20);
	$txhash = field($tx, "txhash", 128);
	$lines = [
		"UNVERIFIED: sent by the customer's browser and can be forged.",
		"Confirm the transaction in your wallet before fulfilling the order.",
		"",
		"Payment ID: " . $pid,
		"Title: " . field($data, "t"),
		"Status: " . $status,
		"Coin: " . field($tx, "payment", 40),
		"Requested: " . field($tx, "amount", 30) . " (" . field($tx, "currencyname", 60) . ")",
		"Received: " . field($tx, "receivedcc", 30) . " " . field($tx, "ccsymbol", 20),
		"Fiat value: " . field($tx, "fiatvalue", 30),
		"Receiving address: " . field($tx, "receiver", 128),
		"Transaction: " . $txhash,
		"Confirmations: " . field($tx, "confirmations", 10) . " (required: " . field($tx, "set_confirmations", 10) . ")"
	];
	if (is_array($contact)) {
		array_push($lines, "",
			"Contact: " . field($contact, "name"),
			"Address: " . field($contact, "address"),
			"Zip / city: " . field($contact, "zipcode", 20) . " " . field($contact, "city"),
			"Country: " . field($contact, "country", 60),
			"E-mail: " . field($contact, "email", 120)
		);
	}
	if (isset($payload["meta"])) {
		$lines[] = "Meta: " . clean(json_encode($payload["meta"], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 500);
	}
	$lines[] = "Origin: " . clean(parse_url($origin, PHP_URL_HOST) ?: "", 253);
	$lines[] = "Sender IP: " . clean($_SERVER["REMOTE_ADDR"] ?? "", 45);
	$dedupe_key = "web|" . $pid . "|" . $status . "|" . $txhash;
}

$state_path = STATE_FILE !== "" ? STATE_FILE : sys_get_temp_dir() . "/br_webhook_" . md5(__DIR__) . ".json";
$handle = @fopen($state_path, "c+");
if (!$handle || !flock($handle, LOCK_EX)) {
	respond(500, "state file not writable");
}
$now = time();
$key = hash("sha256", $dedupe_key);
$sent = [];
$recent_unverified = 0;
$state = json_decode(stream_get_contents($handle), true);
foreach ((is_array($state) ? $state : []) as $entry) {
	if (!is_array($entry) || count($entry) !== 3 || $now - (int) $entry[0] >= 86400) {
		continue;
	}
	if ($entry[1] === $key) {
		respond(200, "duplicate");
	}
	if (!$entry[2] && $now - (int) $entry[0] < 3600) {
		$recent_unverified++;
	}
	$sent[] = $entry;
}
if (!$verified && $recent_unverified >= MAX_MAILS_PER_HOUR) {
	respond(429, "rate limited");
}

$subject_pid = preg_match("/^[A-Za-z0-9._-]{1,64}$/", $pid) ? $pid : "no-pid";
$subject_status = preg_match("/^[a-z]{1,20}$/", $status) ? $status : "unknown";
$subject = "[Bitrequest] " . ($verified ? "" : "UNVERIFIED ") . $subject_status . " " . $subject_pid;
$headers = [
	"MIME-Version" => "1.0",
	"Content-Type" => "text/plain; charset=UTF-8",
	"Content-Transfer-Encoding" => "8bit",
	"From" => SENDER
];
if (!mail(RECIPIENT, $subject, implode("\r\n", $lines) . "\r\n", $headers)) {
	respond(500, "mail failed");
}

$sent[] = [$now, $key, $verified];
ftruncate($handle, 0);
rewind($handle);
fwrite($handle, json_encode($sent));
fflush($handle);
flock($handle, LOCK_UN);
fclose($handle);
respond(200, "sent");