<?php
	// Account details
	$apiKey = urlencode('9EB+kbz5tEg-BbeRg9TDGxjSDQgurjJbOn9Tc9bFzG');
	
	// Message details
	$numbers = array(919561119771);
	$sender = urlencode('TXTLCL');
	$message = rawurlencode('This is your message, hello world');
 
	$numbers = implode(',', $numbers);
 
	// Prepare data for POST request
	$data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
 
	// Send the POST request with cURL
	$ch = curl_init('https://api.textlocal.in/send/');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	
	// Process your response here
	echo $response;
?>