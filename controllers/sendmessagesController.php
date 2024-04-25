<?php
require "routines.php";


function getdetails($userid){
	$command ="select * from sendmessages where userid = $userid";
	$myobj = new stdClass();
	$myobj->data = gettable($command);
	echo json_encode($myobj);

	
}
function adddetails($userid){
	$message = htmlspecialchars($_REQUEST['message'],ENT_QUOTES,"UTF-8");
	$areceipients = $_REQUEST['receipients'];
	$receipients = implode(",",$areceipients);
	$title = $_REQUEST['title'];
	$status = $_REQUEST['status'];
	$tk = $_REQUEST['tk'];
	$command ="CALL insertsendmessages('$message', '$receipients', '$title', '$status', $userid, '$tk');";
	$row = getrow($command);
	echo json_encode($row);

	
}
function getreceipients($userid){
	$myobj = new stdClass();
	$command ="select userid, fullname from users where userid <> $userid and active ='Y'";
	$myobj->data = gettable($command);
	echo json_encode($myobj);
}

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User"));
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
if ($trans=='getdetails'){
	getdetails($userid);
}
if ($trans=='getreceipients'){
	getreceipients($userid);
}
if ($trans=='ADD'){
	adddetails($userid);
}



?>