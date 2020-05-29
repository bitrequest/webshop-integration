<?php
	header("Content-Type: application/json");
	
	$txdata = $_POST["txdata"];
    
    $payment = $txdata["payment"];
    $ccvalue = $txdata["receivedcc"];
    $currencyname = $txdata["currencyname"];
    $fiatvalue = $txdata["fiatvalue"];
	$txhash = $txdata["txhash"];
	$iscrypto = $txdata["iscrypto"];
    
    // response json:
	$json_object = array(
		"txdata" => $txdata,
		"data" => $_POST["data"],
		"meta" => $_POST["meta"]
	);
	
	$ccval_string = $ccvalue . " " . $payment;
	$value_string = ($iscrypto == "true") ? $ccval_string :
		$ccval_string . " (" . $fiatvalue . " " . $currencyname . ")";
		
	// response email:
	//$recipient = "example@domain.com"; // uncomment and enter you email address here
	$subject = "You've received a new " . $payment . " payment";
	$message = "<body style='margin:0;font-family:helvetica,arial,sans-serif;font-size:12px;color:#666;padding:20px'>
			<p style='line-height:1.3em'>You have received " . $value_string . "<br/><br/>
				<a href='https://app.bitrequest.io/?p=requests&txhash=" . $txhash . "' target='_blank'>View transaction</a><br/><br/>
				<strong>post_data: </strong><br/><br/>" . json_encode(array("result" => $json_object)) . "
				</p>
		</body>";
	$headers = array(
		"MIME-Version" => "1.0",
		"Content-type" => "text/html; charset=UTF-8",
	    "From" => "mail@bitrequest.io",
	    "Reply-To" => "mail@bitrequest.io",
	    "X-Mailer" => "PHP/" . phpversion()
	);
	$mailresult = mail($recipient, $subject, $message, $headers);
	if($mailresult) { 
		$json_object["result"] = "success";
	} else {
	    $json_object["result"] = "error";
	}
	echo json_encode(array("result" => $json_object));
?>