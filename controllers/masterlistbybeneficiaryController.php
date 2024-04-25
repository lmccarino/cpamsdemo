<?php 
require "routines.php";

$myobj = validatetoken();
if(empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
} else { 
	$userid = $myobj->userid;
}

if ($_REQUEST['trans'] == 'search'){
	searchdetails();
}

if ($_REQUEST['trans'] == 'getprocloc'){
	getprocloc();
}

if ($_REQUEST['trans'] == 'getsignatory'){
	getsignatory();
}

function searchdetails(){		
	$datefrom = $_REQUEST['datefrom'].' 00:00:00';
	$dateto = $_REQUEST['dateto'].' 23:59:59';
	$procloc = $_REQUEST['procloc'];
	$provCat = $_REQUEST['provCat'];

	if ($procloc == 'ALL' && $provCat == 'ALL'){
		$command = "SELECT a.rafNum, 
					CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS beneficiary,
					o.officename AS provider, 
					CASE WHEN sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, 
					a.amtApproved, a.dateApproved
				FROM assistdetail as a 
				LEFT JOIN patient as p ON a.idpatient = p.idpatient
				LEFT JOIN office as o ON a.provCode = o.officecode
				LEFT JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
				WHERE a.dateApproved BETWEEN '$datefrom' AND '$dateto'";	
	}
	elseif ($procloc != 'ALL' && $provCat != 'ALL'){    
		$command = "SELECT a.rafNum, 
						CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS beneficiary,
						o.officename AS provider, 
						CASE WHEN sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, 
						a.amtApproved, 	a.dateApproved
					FROM assistdetail as a 
					LEFT JOIN patient as p ON a.idpatient = p.idpatient
					LEFT JOIN office as o ON a.provCode = o.officecode
					INNER JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
					WHERE a.dateApproved BETWEEN '$datefrom' AND '$dateto'
					AND o.provCat = '$provCat' AND a.procloc = '$procloc'";	
	}
	elseif ($provCat == 'ALL'){
		$command = "SELECT a.rafNum, 
					CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS beneficiary,
					o.officename AS provider, 
					CASE WHEN sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, a.amtApproved, a.dateApproved
				FROM assistdetail as a 
				LEFT JOIN patient as p ON a.idpatient = p.idpatient
				LEFT JOIN office as o ON a.provCode = o.officecode
				INNER JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
				WHERE a.dateApproved BETWEEN '$datefrom' AND '$dateto'
				AND a.procloc = '$procloc'";	
	}
	elseif ($procloc == 'ALL'){
		$command = "SELECT a.rafNum, 
					CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS beneficiary,
					o.officename AS provider, CASE WHEN sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, a.amtApproved, a.dateApproved
				FROM assistdetail as a 
				LEFT JOIN patient as p ON a.idpatient = p.idpatient
				LEFT JOIN office as o ON a.provCode = o.officecode
				INNER JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
				WHERE a.dateApproved BETWEEN '$datefrom' AND '$dateto' 
				AND o.provCat = '$provCat'";	
	}

	$myobj = new stdClass();
	$myobj->data = gettable($command);
	
	echo json_encode($myobj);
}

function getprocloc(){		
	$table = new stdClass();
	$command = "SELECT idoffice, officename FROM office WHERE officeType='PROCESSING CENTER'";
	$table->data = gettable($command);
	echo json_encode($table);
}

function getsignatory(){
	$officeid = $_REQUEST['office'];

	$table = new stdClass();
	$command = "SELECT
					o.officecode, u.userid,  u.fullname
				FROM office as o
				LEFT JOIN users as u
				ON o.officecode = u.office
				WHERE o.idoffice = '$officeid'";
	$table->data = gettable($command);
	echo json_encode($table);
}



?>