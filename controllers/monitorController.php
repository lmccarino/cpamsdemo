<?php
require "routines.php";


function getdetails(){
	
	$myobj = new stdClass();
	$command = "select * from (select lingapfund.*, users.fullname from lingapfund left join users on lingapfund.userid = users.userid order by idlingapfund desc limit 20) var1 order by idlingapfund asc";
	
	$myobj->data = gettable($command);
	
	echo json_encode($myobj);
}

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User"));
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
if ($trans=='getdetails'){
	getdetails();
}

?>