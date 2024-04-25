<?php
require "routines.php";


function searchdetails(){
	
	$myobj = new stdClass();
	$datefrom = $_REQUEST['datefrom'].' 00:00:00';
	$dateto = $_REQUEST['dateto'].' 23:59:59';
	$provcat = $_REQUEST['provcat'];
	$preparedby = $_REQUEST['preparedby'];
	$notedby = $_REQUEST['notedby'];

	if ($provcat == 'ALL')
	{
		$command = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) AS patientname, a.provCode, o.officename FROM assistdetail AS a LEFT JOIN patient ON a.idpatient = patient.idpatient LEFT JOIN office AS o ON a.provCode = o.officecode  LEFT JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched WHERE a.dateApproved <= '$dateto' AND a.dateApproved >= '$datefrom' AND (sc.assistCode = 'DIALYSIS' OR a.assistCode = 'DIALYSIS') ORDER BY o.officename ASC, a.dateApproved DESC";
	}
	else {
		$command = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) AS patientname, a.provCode, o.officename FROM assistdetail AS a LEFT JOIN patient ON a.idpatient = patient.idpatient LEFT JOIN office AS o ON a.provCode = o.officecode  LEFT JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched  WHERE a.dateApproved <= '$dateto' AND a.dateApproved >= '$datefrom' AND (sc.assistCode = 'DIALYSIS' OR a.assistCode = 'DIALYSIS') AND o.provcat = '$provcat' ORDER BY o.officename ASC, a.dateApproved DESC";
	}
	$myobj->data = gettable($command);
	
	echo json_encode($myobj);
}
$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
if ($trans=='searchdialysis'){
	searchdetails();
}

?>