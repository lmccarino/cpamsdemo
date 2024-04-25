<?php
require "routines.php";


function getdetails(){
	
	$myobj = new stdClass();
	// $command = "select * from (select lingapfund.*, users.fullname from lingapfund left join users on lingapfund.userid = users.userid where credit > 0 order by idlingapfund desc limit 10) var1 order by idlingapfund asc";
	$command = "SELECT fund.*, user.fullname 
				FROM lingapfund AS fund
				LEFT JOIN users AS user ON fund.userid = user.userid
				WHERE fund.details = 'REPLENISH ALLOTMENT'
				ORDER BY fund.idlingapfund DESC
				LIMIT 10";
	$myobj->data = gettable($command);
	
	echo json_encode($myobj);
}
function credit($userid){
	$myobj = new stdClass();
	$credit = $_REQUEST['credit'];
	$details = $_REQUEST['details'];
	$tk = $_REQUEST['tk'];
	$command ="CALL creditlingapfund($credit, '$details', $userid, '$tk')";
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
if ($trans=='credit'){
	credit($userid);
}
?>