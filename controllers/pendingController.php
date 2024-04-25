<?php
require "routines.php";

function getprocloc($userid){
	$myobj = new stdClass();
	$command = "SELECT office.* FROM users left join office on users.office = office.officecode where userid = $userid";
	$row = getrow($command);
	$myobj->procloc = $row['idoffice'];
	$myobj->officename = $row['officename'];
	$myobj->officecode = $row['officecode'];
	$myobj->location = $row['location'];
	echo json_encode($myobj);
}
function getdetails(){
	$myobj = new stdClass();
	$procloc = $_REQUEST['procloc'];
	$command = "select rafNum, idassistdetails, assistCode, dateReceive, amtApproved, provCode, status, officename, assistdetail.idpatient, concat(patient.benFName,' ',patient.benMName,' ',patient.benLName,' ', patient.suffix) as patientname from assistdetail left join office on assistdetail.provCode = office.officecode left join patient on assistdetail.idpatient = patient.idpatient where procloc = $procloc and status = 'RECEIVED'";
	$myobj->data = gettable($command);
	echo json_encode($myobj);
}
function getraf(){
	$idassistdetails = $_REQUEST['idassistdetails'];
	$myobj = new stdClass();
	$command = "select * from assistdetail where idassistdetails = $idassistdetails limit 1";
	$row = getrow($command);
	$myobj->assistdetail = $row;
	echo json_encode($myobj);
	
	
}
function getintake(){
	$idassistdetails = $_REQUEST['idassistdetails'];
	$myobj = new stdClass();
	$command = "select * from intake where idassistdetails = $idassistdetails limit 1";
	$row = getrow($command);
	$myobj->intake = $row;
	echo json_encode($myobj);
	
	
}
$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User")); die('Invalid User');
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
if ($trans=='getprocloc'){
	getprocloc($userid);
}
if ($trans=='getdetails'){
	getdetails();
}
if ($trans=='getraf'){
	getraf();
}
if ($trans=='getintake'){
	getintake();
}