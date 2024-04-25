<?php
require "routines.php";

function add_device($user_id,$firebase_id,$os='Unknown OS',$browser='Unknown Browser') {
	$myobj = new stdClass();
	$command = "SELECT * FROM account_devices where user_id = '$user_id' AND firebase_id = '$firebase_id'";
	$myobj->data = getrow($command);

	if(empty($myobj->data))
	{
		$myobj = new stdClass();
		$data = [
			'user_id' => $user_id,
			'firebase_id' => $firebase_id,
			'os' => $os,
			'browser' => $browser,
			'created_at' => date('Y-m-d H:i:s')
		];

		$arr = [];
		foreach($data as $k => $v) {
			$arr[] = (is_numeric($v)) ? "$v" : "'$v'";
		}
		$paramStr = implode(",", $arr);
		$command = "CALL insert_account_devices($paramStr);";
		$myobj->data = getrow($command);
		
		echo json_encode($myobj);
	}
	else echo json_encode(["message" => "Device already registered", "id" => $myobj->data['firebase_id']]);
}

//TODO uncomment validatetoken below when prod
// $myobj = validatetoken();
// $myobj = new stdClass();
// $myobj->userid = 2;
// if (empty($myobj->userid)){
//     header('Location:index.html?message='.urlencode("Invalid User"));
// } else {
// 	$userid = $myobj->userid;
// }

$trans = $_REQUEST['trans'];
if ($trans=='add_device') {
	add_device(
		$_REQUEST['user_id'],
		$_REQUEST['firebase_id']
	);
}
?>