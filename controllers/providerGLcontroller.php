<?php
	require "routines.php";

	function tagreceive($userid){
		$myobj = new stdClass();
		$tk = $_REQUEST['tk'];
		$id = $_REQUEST['idassistdetails'];
		$command ="call tagreceive($id, $userid, '$tk')";
		$row = getrow($command);
		$myobj->id = $row['@id'];
		$myobj->id = $id;
		$myobj->msg = 'Guarantee Letter was tagged Received!';
		echo json_encode($myobj);
	}

	function getList($loc){
        $date = Date('Y-m-d', strtotime('-7 days'));

		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, patient.benMName, assistdetail.idassistdetails, assistdetail.rafNum, assistdetail.assistCode, 
					assistdetail.billAmount, assistdetail.amtApproved, assistdetail.noteTag, assistdetail.remTag, assistdetail.dateReceive, assistdetail.hospCase, assistdetail.provCode,
					assistdetail.procloc, patient.brgyCode, patient.benAddrSt, patient.suffix, patient.benBDate
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient WHERE assistdetail.status = 'APPROVED' AND DATE(dateApproved) >= DATE('$date') AND provCode='$loc' ";
		
		$table->data = gettable($command);
		echo json_encode($table);
	}

	function getList2($raf, $loc){
		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, patient.benMName, assistdetail.idassistdetails, assistdetail.rafNum, assistdetail.assistCode, 
					assistdetail.billAmount, assistdetail.amtApproved, assistdetail.noteTag, assistdetail.remTag, assistdetail.dateReceive, assistdetail.hospCase, assistdetail.provCode,
					assistdetail.procloc, assistdetail.dateApproved, assistdetail.dateReissue, assistdetail.dateGLReceive, patient.brgyCode, patient.benAddrSt, patient.suffix, patient.benBDate
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient WHERE assistdetail.status = 'APPROVED' AND assistdetail.rafNum='$raf' AND assistdetail.provCode='$loc'";
		
		$table->data = gettable($command);
		echo json_encode($table);
	}

	function getList3($fr, $to, $loc){
		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, patient.benMName, assistdetail.idassistdetails, assistdetail.rafNum, assistdetail.assistCode, 
					assistdetail.billAmount, assistdetail.amtApproved, assistdetail.noteTag, assistdetail.remTag, assistdetail.dateReceive, assistdetail.hospCase, assistdetail.provCode,
					assistdetail.procloc, assistdetail.dateApproved, assistdetail.dateReissue, assistdetail.dateGLReceive, patient.brgyCode, patient.benAddrSt, patient.suffix, patient.benBDate
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient WHERE assistdetail.status = 'APPROVED' AND (( DATE(dateApproved) >= '$fr' AND DATE(dateApproved) <= '$to' ) OR ( DATE(dateReissue) >= '$fr' AND DATE(dateReissue) <= '$to' )) AND provCode='$loc'";
		
		$table->data = gettable($command);
		echo json_encode($table);
	}

	function getsignatory($loc){

		$table = new stdClass();
		$command = "SELECT
					DISTINCT u.userid, o.officecode, u.fullname
					FROM office AS o
					LEFT JOIN users AS u ON o.officecode = u.office
					LEFT JOIN rolesusers AS r ON u.userid = r.idusers
					WHERE o.officecode = '$loc'
					AND u.active = 'Y' AND (u.position <> 'DEV' OR u.position is null)
					AND (r.idroles = '11') 
					ORDER BY u.fullname ASC";
	
		$table->data = gettable($command);
		
		$command = "SELECT idoffice, officename FROM `office` WHERE officeType = 'PROVIDER'";
		$table->offices = gettable($command);
		array_unshift($table->offices, ['idoffice' => 0, 'officename' => 'ALL']);
	
		echo json_encode($table);
	}

	$myobj = validatetoken();
	if (empty($myobj->userid)){
		//die('test here');
		//header('Location:index.html?message='.urlencode("Invalid User"));
	} 
	else 
	{
		$userid = $myobj->userid;
	}
    
	$trans = $_REQUEST['trans'];
	if ($trans=='SSS'){
        $date = Date('Y-m-d', strtotime('-7 days'));
        echo $date;
	}
	if ($trans=='LIST'){
        $loc = $_REQUEST['loc'];
		getList($loc);
	}
	if ($trans=='LIST2'){
        $loc = $_REQUEST['loc'];
		$raf = $_REQUEST['search'];
		getList2($raf, $loc);
	}
	if ($trans=='LIST3'){
        $loc = $_REQUEST['loc'];
		$fr = $_REQUEST['fr'];
		$to = $_REQUEST['to'];
		getList3($fr, $to, $loc);
	}
	if ($trans=='tagreceive'){
		tagreceive($userid);
	}
	if ($trans=='getsignatory'){
		$loc = $_REQUEST['loc'];
		getsignatory($loc);
	}
	
?>