<?php
require "routines.php";

function add_notification($userid,$title,$message) {
	$myobj = new stdClass();
	$data = [
		'sent' => null,
		'readz' => null,
		'account_devices_id' => null,
		'user_id' => $userid,
		'title' => htmlspecialchars($title, ENT_QUOTES,"UTF-8"),
		'msg' => htmlspecialchars($message, ENT_QUOTES,"UTF-8"),
		'created_at' => date('Y-m-d H:i:s')
	];

	$arr = [];
	foreach($data as $k => $v) {
		$arr[] = (is_numeric($v)) ? "$v" : "'$v'";
	}
	$paramStr = implode(",", $arr);
	$command = "CALL insert_notifications($paramStr);";
	//die($command);
	$myobj->data = getrow($command);

	send_push_notification($data['user_id'],$data['title'],$data['msg']);

	echo json_encode($myobj);
}

function send_push_notification($user_id,$title,$body){
	$myobj = new stdClass();
	$devices = [];
	$command = "SELECT * FROM account_devices where user_id = '$user_id'";
	$myobj->data = gettable($command);

	foreach($myobj->data as $device)
		array_push($devices,$device['firebase_id']);

	if(!empty($devices)){
		$header = array();
		$header[] = 'Content-type: application/json';
		$header[] = 'Authorization: key=AAAAJj_7-rI:APA91bEbXA6El0d0bX9qxpO72wb9nNqsFA1qdALPexeFskDB_r3CGDNVFc0NMs_cICWfogpvOD7MDcekj-GynSRMvL-K7w6KhjMXYwtUblRdReKOZgUqnhzisIrlLRapXho3NLWoWDBE';
		$ch = curl_init();
		$url = "https://fcm.googleapis.com/fcm/send";
		$data = [
			"registration_ids" => $devices,
			"notification"=>[
				"title"=> $title,
				"body"=> $body . " " . date('Y-m-d h:i:sa') 
			]
		];
		$crl = curl_init();
		curl_setopt($crl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($crl, CURLOPT_POST,true);
		curl_setopt($crl, CURLOPT_URL, $url);
		curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode( $data ) );
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true );
	
		$rest = curl_exec($crl);
		if ($rest === false) {
			print_r(curl_error($crl));
		}
		curl_close($crl);
		print_r($rest);
	}
}
function get_user_notifications($user_id,$limit=5){
	$myobj = new stdClass();
	$command = "SELECT COUNT(*) AS count FROM notifications WHERE user_id = '$user_id' AND readz = '0000-00-00 00:00:00'";
	$total = getrow($command)['count']??0;

	$command = "SELECT * FROM notifications WHERE user_id = '$user_id' AND readz = '0000-00-00 00:00:00' ORDER BY notification_id DESC LIMIT $limit";
	$myobj->data = gettable($command);
	echo json_encode(['unread' => $total, 'messages' => $myobj->data]);
}
function read_notification($id){
	$myobj = new stdClass();
	$command = "CALL readnotification('$id')";
	$myobj->data = getrow($command);
	echo json_encode($myobj->data);
}

//TODO uncomment validatetoken below when prod
//$myobj = validatetoken();
$myobj = new stdClass();
$myobj->userid = 2;
if (empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
} else {
	$userid = $myobj->userid;
}

$trans = $_REQUEST['trans'];
if ($trans=='add_notification') {
	add_notification($_REQUEST['user_id'],$_REQUEST['title'],$_REQUEST['message']);
}
if ($trans=='send_push_notification'){
	send_push_notification($_REQUEST['user_id'],$_REQUEST['title'],$_REQUEST['message']);
}
if ($trans=='get_user_notifications'){
	get_user_notifications($_REQUEST['user_id']);
}
if ($trans=='read_notification'){
	read_notification($_REQUEST['id']);
}
?>