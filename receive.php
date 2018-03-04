<?php
	//接收request的body(可以接收除了Content-type為multipart/form-data的資料)
	$json_str = file_get_contents('php://input'); 
	$json_obj = json_decode($json_str); //轉成json格式
	
	$myfile = fopen("log.txt", "w+") or die("Unable to open file!"); //設定一個log.txt，用來印訊息
	fwrite($myfile, "\xEF\xBB\xBF".$json_str); //在字串前加上\xEF\xBB\xBF轉成utf8格式

	//產生回傳給line server的格式
	$sender_userid = $json_obj->events[0]->source->userId;
	$sender_txt = $json_obj->events[0]->message->text;
	$sender_replyToken = $json_obj->events[0]->replyToken;
	$line_server_url = 'https://api.line.me/v2/bot/message/push';
	switch ($sender_txt) {
    		case "push":
        		$response = array (
				"to" => $sender_userid,
				"messages" => array (
					array (
						"type" => "text",
						"text" => "Hello, YOU SAY ".$sender_txt
					)
				)
			);
        		break;
    		case "reply":
			$line_server_url = 'https://api.line.me/v2/bot/message/reply';
        		$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
					array (
						"type" => "text",
						"text" => "Hello, YOU SAY ".$sender_txt
					)
				)
			);
        		break;
		case "image":
			$line_server_url = 'https://api.line.me/v2/bot/message/reply';
        		$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
					array (
						"type" => "image",
						"originalContentUrl" => "https://www.w3schools.com/css/paris.jpg",
						"previewImageUrl" => "https://www.nasa.gov/sites/default/themes/NASAPortal/images/feed.png"
					)
				)
			);
        		break;
		case "location":
			$line_server_url = 'https://api.line.me/v2/bot/message/reply';
        		$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
					array (
						"type" => "location",
						"title" => "my location",
						"address" => "〒150-0002 東京都渋谷区渋谷２丁目２１−１",
            					"latitude" => 35.65910807942215,
						"longitude" => 139.70372892916203
					)
				)
			);
        		break;
		case "sticker":
			$line_server_url = 'https://api.line.me/v2/bot/message/reply';
        		$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
					array (
						"type" => "sticker",
						"packageId" => "1",
						"stickerId" => "1"
					)
				)
			);
        		break;
		case "button":
			$line_server_url = 'https://api.line.me/v2/bot/message/reply';
        		$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
					array (
						"type" => "template",
						"altText" => "this is a buttons template",
						"template" => array (
							"type" => "buttons",
							"thumbnailImageUrl" => "https://www.w3schools.com/css/paris.jpg",
							"title" => "Menu",
							"text" => "Please select",
							"actions" => array (
								array (
									"type" => "postback",
									"label" => "Buy",
									"data" => "action=buy&itemid=123"
								),
								array (
									"type" => "postback",
                   							"label" => "Add to cart",
                    							"data" => "action=add&itemid=123"
								)
							)
						)
					)
				)
			);
        		break;
    		default:
			$sender_txt=rawurlencode($sender_txt);
			$ch = curl_init('https://westus.api.cognitive.microsoft.com/luis/v2.0/apps/2a767935-e661-4c58-8d16-ad32fcbb5d95?subscription-key=2c842c8dba264856887b7d947d96fd05&staging=true&verbose=true&timezoneOffset=480&q='.$sender_txt);                                                                      
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                                                                          
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                                                                                     
			$result = json_decode(curl_exec($ch));
			$ans_txt = $result -> topScoringIntent -> intent;
			$response = array (
				"to" => $sender_userid,
				"messages" => array (
					array (
						"type" => "text",
						"text" => $ans_txt
					)
				)
			);
        		break;
	}

	
	
	fwrite($myfile, "\xEF\xBB\xBF".json_encode($result)); //在字串前加上\xEF\xBB\xBF轉成utf8格式
	fclose($myfile);
		
	//回傳給line server
	$header[] = "Content-Type: application/json";
	$header[] = "Authorization: Bearer cCubKq3mCMRx0RcZcoHLDP0r38pPEn5ZkqgTRT0c4fexsmrtN52Fs5kGkQxZYmED5pM1iDsG5M+1si8PS5dgKDs8xF6Qw0DNdddVrMkhc9WJmD1pRVtGqwY4rSNS+/AgkfGoI10hRps8GI//6k7f9AdB04t89/1O/w1cDnyilFU=";
	$ch = curl_init($line_server_url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
	$result = curl_exec($ch);
	curl_close($ch);
?>
