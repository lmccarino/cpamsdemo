<?php
require "routines.php";

function updatedetails($userid) {
	return true;
	$rafNum = $_REQUEST['raf'] = $_REQUEST['rafNum'];
	$tk = $_REQUEST['tk'];
	
	$command ="SELECT userID FROM `assistdetail` where idassistdetails = ". $_REQUEST['idassistdetails'];
	$row = getrow($command);
	$encoderUser = $_REQUEST['userID'] = $row['userID'];
	
	$command = "CALL insertlogging('$rafNum', 'correction', '". str_replace("'", "\'", json_encode($_REQUEST)) ."', '$tk')";
	$row = getrow($command);
	
	/* patient */
	$idpatient = $_REQUEST['idpatient'];
	$benLName = htmlspecialchars(strtoupper(trim($_REQUEST['benLName'])),ENT_QUOTES,"UTF-8");
	$benFName = htmlspecialchars(strtoupper(trim($_REQUEST['benFName'])),ENT_QUOTES,"UTF-8");
	$benMName = htmlspecialchars(strtoupper(trim($_REQUEST['benMName'])),ENT_QUOTES,"UTF-8");
	$suffix = htmlspecialchars(strtoupper(trim($_REQUEST['suffix'])),ENT_QUOTES,"UTF-8");
	$benAddrSt = htmlspecialchars(strtoupper(trim($_REQUEST['benAddrSt'])),ENT_QUOTES,"UTF-8");
	$brgyCode = htmlspecialchars(strtoupper(trim($_REQUEST['brgyCode'])),ENT_QUOTES,"UTF-8");
	$benSex = htmlspecialchars(strtoupper(trim($_REQUEST['benSex'])),ENT_QUOTES,"UTF-8");
	$benPHealth = htmlspecialchars(strtoupper(trim($_REQUEST['benPHealth'])),ENT_QUOTES,"UTF-8");
	$benBDate = htmlspecialchars(strtoupper(trim($_REQUEST['benBDate'])),ENT_QUOTES,"UTF-8");
	$philsysid = htmlspecialchars(strtoupper(trim($_REQUEST['philsysid'])),ENT_QUOTES,"UTF-8");
	$benContact = htmlspecialchars(strtoupper(trim($_REQUEST['benContact'])),ENT_QUOTES,"UTF-8");
	if (empty($benBDate)){ $benBDate2 = 'null'; } else { $benBDate2 = "'".$benBDate."'";}

	$command ="CALL updatepatient($idpatient, '$benLName', '$benFName', '$benMName', '$benAddrSt', '$benSex', $benBDate2, '$benContact', '$benPHealth',  '$philsysid', $brgyCode, '$suffix', $encoderUser, '$tk')";
	$row = getrow($command);
	
	/* intake */
	$idintake = $_REQUEST['idintake'];
	$requestor = htmlspecialchars(strtoupper(trim($_REQUEST['requestor'])),ENT_QUOTES,"UTF-8");
	$reqAddr = htmlspecialchars(strtoupper(trim($_REQUEST['reqAddr'])),ENT_QUOTES,"UTF-8");
	$relation = htmlspecialchars(strtoupper(trim($_REQUEST['relation'])),ENT_QUOTES,"UTF-8");
	$sworker = htmlspecialchars(strtoupper(trim($_REQUEST['sworker'])),ENT_QUOTES,"UTF-8");
	$depName = $_REQUEST['depName'];
	$depRelation = $_REQUEST['depRelation'];
	$depAge = $_REQUEST['depAge'];
	$adependents = array();
	for ($i = 1; $i < count($depName); $i++) {
		$adependents[] = [
			'depName' => htmlspecialchars(strtoupper(trim($depName[$i])),ENT_QUOTES,"UTF-8"),
			'depRelation' => htmlspecialchars(strtoupper(trim($depRelation[$i])),ENT_QUOTES,"UTF-8"),
			'depAge' => trim($depAge[$i])
		];
	}
	$remarks = htmlspecialchars($_REQUEST['remarks'],ENT_QUOTES,"UTF-8");
	$str_details = json_encode($adependents);
	$command ="CALL updateintake($idintake, '$remarks', '$sworker', '$requestor', '$relation', $idpatient, '$str_details', '$reqAddr', $encoderUser, '$tk')";
	$row = getrow($command);
	
	/* assistdetail */
	$idassistdetails = $_REQUEST['idassistdetails'];
	$billAmount = $_REQUEST['billAmount'];
	$amtApproved = $_REQUEST['amtApproved'];
	$noteTag = $_REQUEST['noteTag'];
	$provCode = $_REQUEST['provCode'];
	$remTag = $_REQUEST['remTag'];
	$idassistsched = $_REQUEST['idassistsched'];
	
	$command = "SELECT amtApproved FROM assistdetail WHERE idassistdetails = $idassistdetails";
	$row = getrow($command);

	if ($row['amtApproved'] >= $amtApproved) { //accept changes when original Amount Approved matched or lowered
		$command = "CALL updaterafassistdetail($idassistdetails, $billAmount, $amtApproved, '$noteTag', '$provCode', '$remTag',  $idassistsched, $userid, '$tk')";
		$row = getrow($command);
	}
	
	$command = "SELECT assistCode FROM assistsched WHERE idassistsched = $idassistsched";
	$row = getrow($command);
	/* pcom_details */
	if (isset($_REQUEST['medProv']) && !empty($_REQUEST['medProv']) && $row['assistCode'] == 'MEDICINE') {
		$medProvOld = $_REQUEST['medProvOld'];
		$medProv = $_REQUEST['medProv'];
		$medAmount = $_REQUEST['medAmount'];
		for ($i = 1; $i < count($medProv); $i++)
			if (!empty($medAmount[$i]) && is_numeric($medAmount[$i])) {
				$pharmaOld = (!empty($medProvOld[$i])) ? $medProvOld[$i] : 0;
				$pharmaID = $medProv[$i];
				$xmedAmount = $medAmount[$i];
				$command ="CALL updatepcom_details2($idassistdetails, $idpatient, $pharmaOld, $pharmaID, $xmedAmount, $encoderUser, '$tk')";
				$row = getrow($command);
		}
	}
	
	searchdetails(); //send output here
}

function removemed($userid) {
	$pcom_detailsid = $_REQUEST['pcom_detailsid'];
	$tk = $_REQUEST['tk'];
	
	$command ="CALL deletepcom_details($pcom_detailsid, $userid, '$tk')";
	$row = getrow($command);
	echo json_encode(['pcom_detailsid' => $pcom_detailsid]);
}

function searchdetails() {
	
	$raf = htmlspecialchars(strtoupper(trim($_REQUEST['raf'])),ENT_QUOTES,"UTF-8");
	$result = ['idassistdetails' => -1];
	
	if (!empty($raf)) {
		$qry =  "SELECT
			ast.idassistdetails, ast.rafNum, date(ast.dateReceive) dateReceive,
			pat.idpatient, pat.benLName, pat.benFName, pat.benMName, pat.suffix, pat.benAddrSt, pat.brgyCode, pat.benSex, pat.benPHealth,
			pat.benBDate, pat.philsysid, pat.benContact,
			
			ast.billAmount, ast.amtApproved, ast.dateApproved, ast.noteTag,
			ast.procloc, ast.idassistsched,
			
			intk.idintake, intk.details, intk.sworker, intk.requestor, intk.reqAddr, intk.relation, intk.remarks,
			ofc.provCat, ast.provCode
		FROM `assistdetail` ast
		LEFT JOIN intake intk ON ast.idassistdetails = intk.idassistdetails
		INNER JOIN patient pat ON pat.idpatient = ast.idpatient
		INNER JOIN office ofc ON ofc.officecode = ast.provCode
		where rafNum = '$raf' and status = 'APPROVED'";
		$result = getrow($qry);
		
		if ($result) {
			$qry =  "SELECT det.* FROM `pcom_details` det WHERE idassistdetails = ". $result['idassistdetails'];
			$result['pcom_details'] = gettable($qry);
		} else $result = ['idassistdetails' => -1];
	}
	
	echo json_encode($result);
}

$myobj = validatetoken();
if (empty($myobj->userid)) {
    header('Location:../index.html?message='.urlencode("Invalid User"));
	die('system error');
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];

if ($trans=='UPDATE') {
	updatedetails($userid);
}
if ($trans=='search') {
	searchdetails();
}
if ($trans=='removemed') {
	removemed($userid);
}
?>