<?php
require "routines.php";
$myobj = new stdClass();
$myobj->userid = -1;
if (array_key_exists('token',$_REQUEST)){
		$token = $_REQUEST['token'];
} else { $token='';}

if (!empty($token)){
	$ip = getUserIpAddr();
	$ldate = date("Y-m-d");
	$command = "select * from tk where token = '$token' and ip = '$ip' and date(ldate) = '$ldate' and odate is null";
	//$command = "select * from tk where idtk = $token and ip = '$ip' and ldate = '$ldate'";
	
	$data = getrow($command);
	if (!empty($data)){
		$myobj->userid = $data['userid'];
		$myobj->fullname = $data['fullname'];
		$myobj->image=  $data['image'];
	//$myobj->schoolyear = $data['schoolyear'];
	//$myobj->idsy = $data['idsy'];
		$myobj->office = $data['office'];
	//$myobj->semester = $data['semester'];
	$myobj->role = $data['role'];
	if (hasRole($data['userid'], 'SUPERVISOR')) $myobj->role = '1';
	if (hasRole($data['userid'], 'TEAM LEADER')) $myobj->role = ( $myobj->role ) ? $myobj->role.'2' : '2';
	//$myobj->sylist = json_decode($data['sylist'], true);;
		$myobj->token = $token;
	}
	
}
echo json_encode($myobj);
?>