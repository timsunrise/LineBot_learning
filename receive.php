<?php
	//接收request的body(可以接收除了Content-type為multipart/form-data的資料)
	$json_str = file_get_contents('php://input'); 
	$json_obj = json_decode($json_str); //轉成json格式
	
	$myfile = fopen("log.txt", "w+") or die("Unable to open file!"); //設定一個log.txt，用來印訊息
	//fwrite($myfile, "\xEF\xBB\xBF".$json_str); //在字串前加上\xEF\xBB\xBF轉成utf8格式
	
	//產生回傳給line server的格式
	$sender_userid = $json_obj->events[0]->source->userId;
	$sender_txt = $json_obj->events[0]->message->text;
	$response = array (
		"to" => $sender_userid,
		"messages" => array (
			array (
				"type" => "text",
				"text" => "Hello, YOU SAY ".$sender_txt
			)
		)
	);
	
	fwrite($myfile, "\xEF\xBB\xBF".json_encode($response)); //在字串前加上\xEF\xBB\xBF轉成utf8格式
	fclose($myfile);
		
	//回傳給line server
	$header[] = "Content-Type: application/json";
	$header[] = "Authorization: Bearer XZlqgHtg16JT3GygJbLNGAV4qDrmwagS0xkEAb2iqxOiDyDIY3MnWaNlW1D3RsQJ4mypV/OZ/uxUbUz3TYMz+nv0lboLkuEP7nG7odyCtt1kJ3kXHAqGzCeEn7zRHO1WP+etXy/0lqF7P1lF6EFDVgdB04t89/1O/w1cDnyilFU=";
	$ch = curl_init('https://api.line.me/v2/bot/message/push');                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
	$result = curl_exec($ch);
	curl_close($ch);
?>
