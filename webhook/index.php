<?php
$host = $_SERVER["HTTP_HOST"];
$post_data = json_encode(array(
    "post" => $_POST,
    "get" => $_GET
), JSON_PRETTY_PRINT);

// response email:
//$recipient = "recipient@domain.com"; // uncomment and enter your recipient email-address here
//$sender = "sender@domain.com"; // uncomment and enter your sender email-address here
$subject = "Post data";
$message = "<body style='font-family:helvetica,arial,sans-serif;font-size:12px;color:#5d5d5d;padding:20px'>
		<p style='line-height:1.3em'>
			<strong>post_data: </strong><br/>
			<pre>" . $post_data . "</pre><br/>
			<strong>Callback host: </strong><br/>
			<pre>" . $host . "</pre>
		</p>
	</body>";
$headers = array(
    "MIME-Version" => "1.0",
    "Content-type" => "text/html; charset=UTF-8",
    "From" => $sender,
    "Reply-To" => $sender,
    "X-Mailer" => "PHP/" . phpversion()
);
$mailresult = mail($recipient, $subject, $message, $headers);
return

?>
