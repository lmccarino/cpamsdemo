<?php
require "routines.php";

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
} else {$userid = $myobj->userid;}


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

function getassistance(){
	$table = new stdClass();
	$command = "SELECT DISTINCT assistCode 
				FROM assistsched 
				ORDER BY assistCode";
	$table->data = gettable($command);
	echo json_encode($table);
}

function getdist(){
	$table = new stdClass();
	$command = "SELECT DISTINCT distName 
				FROM distbrgy 
				ORDER BY distName";
	$table->data = gettable($command);
	echo json_encode($table);
}


function searchdetails(){
	
	$myobj = new stdClass();
	$datefrom = $_REQUEST['datefrom'].' 00:00:00';
	$dateto = $_REQUEST['dateto'].' 23:59:59';
	$procloc = $_REQUEST['procloc'];
	$provider = $_REQUEST['provider'];
	$provcat = $_REQUEST['provcat'];

	 if  (($procloc == 'ALL')&&($provcat == 'ALL')){
		$command = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(o.officename,' (', CASE WHEN
				b.assistCode IS NOT NULL THEN concat(b.assistCode, CASE WHEN
				TRIM(b.assistDesc) != '' THEN concat('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END ,')') as assistCode, a.provCode , concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname 
			 FROM assistdetail as a 
			 LEFT JOIN assistsched as b on a.idassistsched = b.idassistsched 
			 LEFT JOIN patient on a.idpatient = patient.idpatient
			 LEFT JOIN office as o on a.provCode = o.officecode
			 WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto' AND a.status = 'APPROVED'";
	} 
	
	 elseif  (($procloc != 'ALL')&&($provcat != 'ALL')){   
		$command = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(CASE WHEN b.assistCode IS NOT NULL THEN concat(b.assistCode, CASE WHEN TRIM(b.assistDesc) != '' THEN concat ('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END) as assistCode, a.provCode, concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname 
			FROM assistdetail as a 
			LEFT JOIN assistsched as b on a.idassistsched = b.idassistsched 
			LEFT JOIN patient on a.idpatient = patient.idpatient
			where a.dateApproved >= '$datefrom' and a.dateApproved <= '$dateto' and a.provCode = '$provider' and a.procloc = '$procloc' AND a.status = 'APPROVED'";
	}
	elseif ($provcat == 'ALL'){
		$command = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(o.officename,' (', CASE WHEN
			b.assistCode IS NOT NULL THEN concat(b.assistCode, CASE WHEN
			TRIM(b.assistDesc) != '' THEN concat('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END ,')') as assistCode, a.provCode , concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname 
			FROM assistdetail as a 
			LEFT JOIN assistsched as b on a.idassistsched = b.idassistsched 
			LEFT JOIN patient on a.idpatient = patient.idpatient
			LEFT JOIN office as o on a.provCode = o.officecode
			WHERE a.dateApproved >= '$datefrom' and a.dateApproved <= '$dateto' and a.procloc = '$procloc' AND a.status = 'APPROVED'";

	}  
	elseif  ($procloc == 'ALL'){
		$command = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(o.officename,' (', CASE WHEN
			b.assistCode IS NOT NULL THEN concat(b.assistCode, CASE WHEN
			TRIM(b.assistDesc) != '' THEN concat('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END ,')') as assistCode, a.provCode , concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname 
			FROM assistdetail as a 
			LEFT JOIN assistsched as b on a.idassistsched = b.idassistsched 
			LEFT JOIN patient on a.idpatient = patient.idpatient
			LEFT JOIN office as o on a.provCode = o.officecode
			WHERE a.dateApproved >= '$datefrom' and a.dateApproved <= '$dateto' and a.provCode = '$provider' AND a.status = 'APPROVED'";
	} 
	

	$myobj->data = gettable($command);
	echo json_encode($myobj);

}

function getsignatory($officeid){

	$table = new stdClass();
	$command = "SELECT
				DISTINCT u.userid, o.officecode, u.fullname
				FROM office AS o
				LEFT JOIN users AS u ON o.officecode = u.office
				LEFT JOIN rolesusers AS r ON u.userid = r.idusers
				WHERE o.idoffice = '$officeid'
				AND u.active = 'Y' AND (u.position <> 'DEV' OR u.position is null)
				AND (r.idroles = '10' OR r.idroles = '12') 
				ORDER BY u.fullname ASC";

	$table->data = gettable($command);
	
	$command = "SELECT idoffice, officename FROM `office` WHERE officeType = 'PROCESSING CENTER'";
	$table->offices = gettable($command);
	array_unshift($table->offices, ['idoffice' => 0, 'officename' => 'ALL']);

	echo json_encode($table);
}

function getUsers(){

	$table = new stdClass();
	$command = "SELECT
				DISTINCT u.userid, o.officecode, u.fullname
				FROM office AS o
				LEFT JOIN users AS u ON o.officecode = u.office
				LEFT JOIN rolesusers AS r ON u.userid = r.idusers
				WHERE u.active = 'Y' AND (u.position <> 'DEV' AND u.position <> 'VIP' OR u.position is null)
				ORDER BY u.fullname ASC";

	$table->data = gettable($command);
	
	$command = "SELECT idoffice, officename FROM `office` WHERE officeType = 'PROCESSING CENTER'";
	$table->offices = gettable($command);
	array_unshift($table->offices, ['idoffice' => 0, 'officename' => 'ALL']);

	echo json_encode($table);
}

$trans = $_REQUEST['trans'];
if ($trans=='search'){
	searchdetails();
}
if ($trans=='getprocloc'){
	getprocloc();
}
if ($trans=='getassistance'){
	getassistance();
}
if ($trans=='getdist'){
	getdist();
}
if ($trans=='getprovider'){
	$provcat = $_REQUEST['provcat'];
	getprovider($provcat);
}

if ($trans=='getsignatory'){
	$officeid = $_REQUEST['office'];
	getsignatory($officeid);
}

if ($trans=='getsysuser'){
	getUsers();
}
?>