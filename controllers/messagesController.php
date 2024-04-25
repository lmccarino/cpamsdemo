<?php
require "routines.php";


function getdetails($userid){
	$command ="select * from notifications where user_id = $userid";
	$myobj = new stdClass();
	
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



?>