<?php
  $json_str = file_get_contents('php://input'); //接收request的body
  $json_obj = json_decode($json_str); //轉成json格式
  
  $myfile = fopen("log.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
  fwrite($myfile, "\xEF\xBB\xBF".$json_str); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
  
  $sender_userid = $json_obj->events[0]->source->userId; //取得訊息發送者的id
  $sender_txt = $json_obj->events[0]->message->text; //取得訊息內容
  $sender_replyToken = $json_obj->events[0]->replyToken; //取得訊息的replyToken
  
  switch ($sender_txt) {
    case "push":
			$response = array (
				"to" => $sender_userid,
				"messages" => array (
					array (
						"type" => "text",
						"text" => "Hello. This is push. You say ".$sender_txt
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
  }
  // $sender_txt=rawurlencode($sender_txt); //因為使用get的方式呼叫luis api，所以需要轉碼
  // $ch = curl_init('https://westus.api.cognitive.microsoft.com/luis/v2.0/apps/de6c346b-c7ec-4fc2-b497-a4fbfc9d24ca?subscription-key=2c842c8dba264856887b7d947d96fd05&timezoneOffset=-360&q='.$sender_txt);                                                                      
  // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                                                                          
  // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // $result_str = curl_exec($ch);
  // fwrite($myfile, "\xEF\xBB\xBF".$result_str); //在字串前加上\xEF\xBB\xBF轉成utf8格式
  // $result = json_decode($result_str);
  // $ans_txt = $result -> topScoringIntent -> intent;
  // $response = array (
  //   "to" => $sender_userid,
  //   "messages" => array (
  //     array (
  //       "type" => "text",
  //       "text" => $ans_txt
  //     )
  //   )
  // );
  
  
  fwrite($myfile, "\xEF\xBB\xBF".json_encode($response)); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
  $header[] = "Content-Type: application/json";
  $header[] = "Authorization: Bearer gd1gyH+Pc5TROu9ku5u/5tDvFnffsU8nXU69zXuhTgE0dIS5nVGmx9Js8PwijeUqgFuwWXzyJ14/N5FUmp/UXsmSJbUsxMGA6AW1gozlf6cbEgSGLiC02BEaRa5wUSqE7df8FOANP1WjPW8Mh/TgtwdB04t89/1O/w1cDnyilFU=";
  $ch = curl_init("https://api.line.me/v2/bot/message/push");
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
  $result = curl_exec($ch);
  curl_close($ch);
?>
