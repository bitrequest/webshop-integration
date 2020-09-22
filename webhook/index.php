<?php
header("Content-Type: application/json");

// security
$host = $_SERVER["HTTP_HOST"];
$origin = $_SERVER["HTTP_ORIGIN"];
$post_origin = (strpos($origin, $host)) ? "local" : "remote"; // check if post request is local or remote
if ($post_origin == "local") { // only allow local requests
    $security_object = array(
        "host" => $host,
        "origin" => $origin,
        "post_origin" => $post_origin
    );

    $txdata = $_POST["txdata"];
    $payment = $txdata["payment"];
    $ccvalue = $txdata["receivedcc"];
    $currencyname = $txdata["currencyname"];
    $fiatvalue = $txdata["fiatvalue"];
    $txhash = $txdata["txhash"];
    $iscrypto = $txdata["iscrypto"];

    $data = $_POST["data"];
    $meta = $_POST["meta"];
    $contact = $_POST["contact"];

    // response json:
    $json_object = array(
        "txdata" => $txdata,
        "data" => $data,
        "meta" => $meta,
        "contact" => $contact,
        "security" => $security_object
    );

    $ccval_string = $ccvalue . " " . $payment;
    $value_string = ($iscrypto == "true") ? $ccval_string : $ccval_string . " (" . $fiatvalue . " " . $currencyname . ")";

    $post_data = json_encode(array(
        "result" => $json_object
    ) , JSON_PRETTY_PRINT);

    // response email:
    //$recipient = "recipient@domain.com"; // uncomment and enter your recipient email-address here
    //$sender = "sender@domain.com"; // uncomment and enter your sender email-address here
    $subject = "You've received a new " . $payment . " payment";
    $message = "<body style='font-family:helvetica,arial,sans-serif;font-size:12px;color:#5d5d5d;padding:20px'>
			<p style='line-height:1.3em'>
				<strong>Amount</strong> " . $value_string . "<br/><br/>
				<strong>Purchased</strong> " . $_POST["data"]["t"] . "<br/><br/>
				<strong>post_data: </strong><br/>
				<pre>" . $post_data . "</pre><br/>
				<a href='https://app.bitrequest.io/?p=requests&txhash=" . $txhash . "' target='_blank'>View transaction</a>
			</p>
		</body>";
    $headers = array(
        "MIME-Version" => "1.0",
        "Content-type" => "text/html; charset=UTF-8",
        "From" => $sender,
        "Reply-To" => $sender,
        "X-Mailer" => "PHP/" . phpversion()
    );
    if ($contact !== NULL) {
        $confirm_subject = "Thank you for your purchase at " . $data["n"];
        mail($contact["email"], $confirm_subject, $message, $headers);
    }
    $mailresult = mail($recipient, $subject, $message, $headers);
    if ($mailresult) {
        $json_object["status"] = "success";
    }
    else {
        $json_object["status"] = "error";
    }
    echo json_encode(array(
        "result" => $json_object
    ) , JSON_PRETTY_PRINT);
}
else {
    $json_object = array(
        "status" => "error",
        "message" => "cross origin not allowed"
    );
    echo json_encode(array(
        "result" => $json_object
    ) , JSON_PRETTY_PRINT);
}
?>
