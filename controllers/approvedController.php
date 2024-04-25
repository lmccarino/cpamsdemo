<?php
	require "routines.php";

	function updateDtl($userid){
		$tk = $_REQUEST['tk'];
		$id=$_REQUEST['id'];
		$noteTag=htmlspecialchars(strtoupper($_REQUEST['noteTag']),ENT_QUOTES,"UTF-8");
		$command ="call disapprove($id, $userid, '$tk', '$noteTag')";
		
		getrow($command, false);
		//echo json_encode($data);
		echo $id;
	}
	
	function cancelled($userid){
		$tk = $_REQUEST['tk'];
		$id=$_REQUEST['id'];
		$noteTag=htmlspecialchars(strtoupper($_REQUEST['noteTag']),ENT_QUOTES,"UTF-8");
		$command ="call cancelassistdetail($id, $userid, '$tk', '$noteTag')";
		
		getrow($command, false);
		//echo json_encode($data);
		echo $id;
	}

	function ReApprove($userid){
		$tk = $_REQUEST['tk'];
		$id=$_REQUEST['id'];
		$rem=htmlspecialchars(strtoupper($_REQUEST['noteTag']),ENT_QUOTES,"UTF-8");
		$amt=$_REQUEST['amt'];
		
		$command = "SELECT rafNum FROM assistdetail WHERE idassistdetails = $id";
		$row = getrow($command);
		$rafNum = $row['rafNum'];
		
		$command = "CALL insertlogging('$rafNum', 'reapprove', '". str_replace("'", "\'", json_encode($_REQUEST)) ."', '$tk')";
		$row = getrow($command);
	
		$command ="call reapprove($id, $userid, $amt, '$tk', '$rem')";
		getrow($command, false);
		
		echo $id;
	}

	function getList(){
		$id = officeId();
		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, patient.benMName, 
					assistdetail.idassistdetails, assistdetail.rafNum, assistdetail.assistCode, 
					assistdetail.billAmount, assistdetail.amtApproved, assistdetail.dateApproved, 
					assistdetail.noteTag, assistdetail.remTag, assistdetail.dateReceive, 
					assistdetail.hospCase, assistdetail.provCode, assistdetail.procloc, 
					patient.brgyCode, patient.benAddrSt, patient.suffix, patient.benBDate,
					CONCAT(assistsched.assistCode, ' - ',assistsched.assistDesc) AS hospitalcase, 
					office.officecode, assistdetail.dateReceive 
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient 
					LEFT JOIN assistsched ON assistdetail.idassistsched = assistsched.idassistsched	
					LEFT JOIN office ON assistdetail.procloc = office.idoffice
					WHERE assistdetail.status = 'APPROVED' AND DATE(dateApproved) = DATE(now())  
					AND procloc = $id";
		
		$table->data = gettable($command);
		echo json_encode($table);
	}

	function getList2($raf){
		$id = officeId();
		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, patient.benMName, 
					assistdetail.idassistdetails, assistdetail.rafNum, assistdetail.assistCode, 
					assistdetail.billAmount, assistdetail.amtApproved, assistdetail.dateApproved, 
					assistdetail.noteTag, assistdetail.remTag, assistdetail.dateReceive, assistdetail.hospCase, 
					assistdetail.provCode, assistdetail.procloc, patient.brgyCode, patient.benAddrSt, 
					patient.suffix, patient.benBDate,
					CONCAT(assistsched.assistCode, ' - ',assistsched.assistDesc) AS hospitalcase, 
					office.officecode, assistdetail.dateReceive 
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient 
					LEFT JOIN assistsched ON assistdetail.idassistsched = assistsched.idassistsched	
					LEFT JOIN office ON assistdetail.procloc = office.idoffice
					WHERE assistdetail.status = 'APPROVED' AND assistdetail.rafnum='$raf' AND procloc = $id";
		
		$table->data = gettable($command);
		echo json_encode($table);
	}

	function getList3($fr, $to){
		$id = officeId();
		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, patient.benMName, assistdetail.idassistdetails, assistdetail.rafNum, assistdetail.assistCode, 
					assistdetail.billAmount, assistdetail.amtApproved, assistdetail.dateApproved, assistdetail.noteTag, assistdetail.remTag, assistdetail.dateReceive, assistdetail.hospCase, assistdetail.provCode,
					assistdetail.procloc, patient.brgyCode, patient.benAddrSt, patient.suffix, patient.benBDate
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient WHERE assistdetail.status = 'APPROVED' AND DATE(dateApproved) >= '$fr' AND DATE(dateApproved) <= '$to'  AND procloc = $id";
		
		$table->data = gettable($command);
		echo json_encode($table);
	}

	function getList4($raf){
		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, patient.benMName, assistdetail.idassistdetails, assistdetail.rafNum, assistdetail.assistCode, 
					assistdetail.billAmount, assistdetail.amtApproved, assistdetail.dateApproved, assistdetail.noteTag, assistdetail.remTag, assistdetail.dateReceive, assistdetail.hospCase, assistdetail.provCode,
					assistdetail.procloc, patient.brgyCode, patient.benAddrSt, patient.suffix, patient.benBDate
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient WHERE assistdetail.status = 'APPROVED' AND assistdetail.rafnum='$raf'";
		
		$table->data = gettable($command);
		echo json_encode($table);
	}

	function getList5($fr, $to){
		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, patient.benMName, assistdetail.idassistdetails, assistdetail.rafNum, assistdetail.assistCode, 
					assistdetail.billAmount, assistdetail.amtApproved, assistdetail.dateApproved, assistdetail.noteTag, assistdetail.remTag, assistdetail.dateReceive, assistdetail.hospCase, assistdetail.provCode,
					assistdetail.procloc, patient.brgyCode, patient.benAddrSt, patient.suffix, patient.benBDate
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient WHERE assistdetail.status = 'APPROVED' AND DATE(dateApproved) >= '$fr' AND DATE(dateApproved) <= '$to'";
		
		$table->data = gettable($command);
		echo json_encode($table);
	}

	function getList6(){
		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, patient.benMName, assistdetail.idassistdetails, assistdetail.rafNum, assistdetail.assistCode, 
					assistdetail.billAmount, assistdetail.amtApproved, assistdetail.dateApproved, assistdetail.noteTag, assistdetail.remTag, assistdetail.dateReceive, assistdetail.hospCase, assistdetail.provCode,
					assistdetail.procloc, patient.brgyCode, patient.benAddrSt, patient.suffix, patient.benBDate
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient WHERE assistdetail.status = 'APPROVED' AND DATE(dateApproved) = DATE(now())";
		
		$table->data = gettable($command);
		echo json_encode($table);
	}
	
	function getList7($fr, $to){
		$id = officeId();
		
		$status = (!empty($_REQUEST['status'])) ? $_REQUEST['status'] : '';
		if ( isset($_REQUEST['lastName']) && !empty( trim($_REQUEST['lastName']) ) ) {
			$lastName = "AND patient.benLName LIKE '". strtoupper(trim($_REQUEST['lastName'])) ."%'";
		} else $lastName = '';
		
		if ( isset($_REQUEST['firstName']) && !empty( trim($_REQUEST['firstName']) ) ) {
			$firstName = "AND patient.benFName LIKE '". strtoupper(trim($_REQUEST['firstName'])) ."%'";
		} else $firstName = '';
		
		if ( isset($_REQUEST['status']) && !empty( trim($_REQUEST['status']) ) ) {
			$status = "assistdetail.status = '". strtoupper(trim($_REQUEST['status'])) ."'";
		} else $status = "assistdetail.status IN('APPROVED', 'CANCELLED')";
		
		if ( isset($_REQUEST['raf']) && !empty( trim($_REQUEST['raf']) ) ) {
			$rafNum = "AND assistdetail.rafNum = '". strtoupper(trim($_REQUEST['raf'])) ."'";
		} else $rafNum = "";
		
		if ( isset($fr) && !empty( trim($fr) ) ) {
			$date = "AND DATE(dateApproved) >= '$fr'";
		} else $date = "";
		
		if ( isset($to) && !empty( trim($to) ) && strlen( $date ) > 0 ) {
			$date .= "AND DATE(dateApproved) <= '$to'";
		} else if ( empty($date) ) $date = "";
		
		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, patient.benMName, assistdetail.idassistdetails, assistdetail.rafNum, assistdetail.assistCode, 
					assistdetail.billAmount, assistdetail.amtApproved, assistdetail.dateApproved, assistdetail.dateReissue, assistdetail.noteTag, assistdetail.remTag, assistdetail.dateReceive, assistdetail.provCode,
					assistdetail.procloc, patient.brgyCode, patient.benAddrSt, patient.suffix, patient.benBDate, assistdetail.status, intake.requestor, intake.remarks, office.officecode, distbrgy.brgyName
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient 
					LEFT JOIN intake on assistdetail.idassistdetails = intake.idassistdetails 
					LEFT JOIN office on office.idoffice = assistdetail.procloc 
					LEFT JOIN distbrgy on patient.brgyCode = distbrgy.brgyCode 

					WHERE 
					    $status
						$rafNum
						$lastName
						$firstName
						$date
						-- AND procloc = $id
					ORDER BY assistdetail.dateApproved DESC";
		
		$table->data = gettable($command);
		echo json_encode($table);
	}

	function officeId(){
		$office = $_REQUEST['office'];
		$command ="SELECT idoffice FROM office WHERE officecode='$office'";
		$row=getrow($command);
		$id=$row['idoffice'];
		return $id;
	}

	//die($_REQUEST['tk']);
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
	if ($trans=='CANCELLED'){
		cancelled($userid);
	}
	if ($trans=='REAPPROVE'){
		ReApprove($userid);
	}
	if ($trans=='LIST'){
		getList();
	}
	if ($trans=='LIST2'){
		$raf = $_REQUEST['search'];
		getList2($raf);
	}
	if ($trans=='LIST3'){
		$fr = $_REQUEST['fr'];
		$to = $_REQUEST['to'];
		getList3($fr, $to);
	}
	if ($trans=='LIST4'){
		$raf = $_REQUEST['search'];
		getList4($raf);
	}
	if ($trans=='LIST5'){
		$fr = $_REQUEST['fr'];
		$to = $_REQUEST['to'];
		getList5($fr, $to);
	}
	if ($trans=='LIST6'){
		getList6();
	}
	if ($trans=='LIST7'){
		$fr = $_REQUEST['fr'];
		$to = $_REQUEST['to'];
		getList7($fr, $to);
	}
?>