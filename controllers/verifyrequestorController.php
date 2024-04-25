<?php
require "routines.php";

function getDistinctRequestor(){
	$myobj = new stdClass();
	$requestor = htmlspecialchars(strtoupper(trim($_REQUEST['requestor'])),ENT_QUOTES,"UTF-8");
	$command = "SELECT DISTINCT requestor, reqAddr
				FROM intake
				INNER JOIN assistdetail AS asst ON asst.idassistdetails = intake.idassistdetails
				WHERE requestor LIKE '$requestor%' AND asst.status = 'APPROVED'";
	

	$myobj->data = gettable($command); 
	
	echo json_encode($myobj);

}

function getTransactions(){
	$myobj = new stdClass();
	$requestor=htmlspecialchars(strtoupper($_REQUEST['requestor']),ENT_QUOTES,"UTF-8");
	$command ="SELECT pat.benLName, pat.benFName, pat.benMName, asst.rafnum, asst.provCode, asst.amtApproved, asst.dateApproved, asst.assistCode, intk.relation
	FROM intake AS intk
	INNER JOIN assistdetail AS asst ON asst.idassistdetails = intk.idassistdetails 
	INNER JOIN patient AS pat ON pat.idpatient = asst.idpatient 
	WHERE intk.requestor = '$requestor' AND asst.status ='APPROVED' 
	ORDER BY intk.idintake DESC" ;
	$myobj->data = gettable($command);
	
	echo json_encode($myobj);
}



$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User"));
	die('system error');
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];

if ($trans=='search'){
	getDistinctRequestor();
}
if ($trans=='getTransactions'){
	getTransactions();
}

?>