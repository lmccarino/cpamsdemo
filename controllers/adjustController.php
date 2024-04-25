<?php
require "routines.php";


function getdetails(){
	
	$myobj = new stdClass();
	$command = "select * from (select lingapfund.*, users.fullname from lingapfund left join users on lingapfund.userid = users.userid where debit > 0 order by idlingapfund desc limit 10) var1 order by idlingapfund asc";
	
	$myobj->data = gettable($command);
	
	echo json_encode($myobj);
}
function debit($userid){
	$myobj = new stdClass();
	$debit = $_REQUEST['debit'];
	$details = $_REQUEST['details'];
	$tk = $_REQUEST['tk'];
	$command ="CALL debitlingapfund($debit, '$details', $userid, '$tk')";
	$row = getrow($command);
	$myobj->idlingapfund = $row['@id'];
	$myobj->balAmount = $row['@balAmount'];
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
if ($trans=='debit'){
	debit($userid);
}
?>