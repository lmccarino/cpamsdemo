<?php
require "routines.php";

function getprocloc(){
	$table = new stdClass();
	$command = "select * from office where officeType = 'PROCESSING CENTER'  order by `officename`";
	$table->data = gettable($command);
	echo json_encode($table);
}

function getprovider($provcat){
	$table = new stdClass();
	$command = "select * from office where provCat = '$provcat'  order by `officename`";
	$table->data = gettable($command);
	echo json_encode($table);
}

function searchdetails(){
	
	$myobj = new stdClass();
	$datefrom = $_REQUEST['datefrom'];
	$dateto = $_REQUEST['dateto'];
	$procloc = $_REQUEST['procloc'];
	$provider = $_REQUEST['provider'];

	$command = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, a.assistCode, a.provCode, concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname FROM assistdetail as a left join patient on a.idpatient = patient.idpatient where a.dateApproved >= '$datefrom' and a.dateApproved <= '$dateto' and a.provCode = '$provider' and a.procloc = '$procloc'";

	$myobj->data = gettable($command);
	
	echo json_encode($myobj);

}
$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
if ($trans=='search'){
	searchdetails();
}
if ($trans=='getprocloc'){
	getprocloc();
}

if ($trans=='getprovider'){
	$provcat = $_REQUEST['provcat'];
	getprovider($provcat);
}

?>