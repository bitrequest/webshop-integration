<?php
	header("Content-Type: application/json");
	
	$txdata = $_POST["txdata"];
    $payment = $txdata["payment"];
    $ccvalue = $txdata["receivedcc"];
    $currencyname = $txdata["currencyname"];
    $fiatvalue = $txdata["fiatvalue"];
	$txhash = $txdata["txhash"];
	$iscrypto = $txdata["iscrypto"];
	$contactdetails = $_POST["contact"];
    
    // response json:
	$json_object = array(
		"txdata" => $txdata,
		"data" => $_POST["data"],
		"meta" => $_POST["meta"],
		"meta" => $contactdetails
	);
	
	$ccval_string = $ccvalue . " " . $payment;
	$value_string = ($iscrypto == "true") ? $ccval_string :
		$ccval_string . " (" . $fiatvalue . " " . $currencyname . ")";
		
	$post_data = json_encode(array("result" => $json_object), JSON_PRETTY_PRINT);
		
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
	if($contactdetails !== NULL) {
		mail($contactdetails["email"], "Thank you for your purchase", $message, $headers);
	}
	$mailresult = mail($recipient, $subject, $message, $headers);
	if($mailresult) { 
		$json_object["status"] = "success";
	} else {
	    $json_object["status"] = "error";
	}
	echo json_encode(array("result" => $json_object), JSON_PRETTY_PRINT);
?>