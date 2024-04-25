<?php
	require "routines.php";

	//function updateDtl($userid){
	function updateDtl($userid){
		//$myobj = new stdClass();
		$tk = $_REQUEST['tk'];
		$id=$_REQUEST['id'];
		$amt=$_REQUEST['amtApproved'];
		$amt2=$_REQUEST['amt'];
		$raf=$_REQUEST['raf'];
		$overridenote = htmlspecialchars(strtoupper(trim($_REQUEST['notetag'])),ENT_QUOTES,"UTF-8");
		
		$command = "CALL insertlogging('$raf', 'override', '". str_replace("'", "\'", json_encode($_REQUEST)) ."', '$tk')";
		$row = getrow($command);
	
		$command ="CALL override($id, $amt, $amt2, $userid, '$tk', '$raf', '$overridenote')";
		$data = getrow($command);
		//$myobj->tk = $userid;
		echo json_encode($data);
		//echo $id;
	}

	function getList(){
		$id = officeId();
		$table = new stdClass();
		$command = "SELECT patient.idpatient, patient.benLName, patient.benFName, 
					patient.benMName, assistdetail.idassistdetails, assistdetail.rafNum, 
					assistdetail.assistCode, assistdetail.billAmount, assistdetail.amtApproved, 
					assistdetail.noteTag, assistdetail.dateReceive, assistdetail.hospCase, 
					assistdetail.provCode, assistdetail.procloc, patient.brgyCode, 
					patient.benAddrSt, patient.suffix, patient.benBDate,
					CONCAT(assistsched.assistCode, ' - ',assistsched.assistDesc) AS hospcase,
					office.officecode, assistdetail.dateReceive
					FROM assistdetail INNER JOIN  patient ON  assistdetail.idpatient = patient.idpatient 
					LEFT JOIN assistsched ON assistdetail.idassistsched = assistsched.idassistsched	
					LEFT JOIN office ON assistdetail.procloc = office.idoffice
					WHERE assistdetail.status = 'OVERRIDE' AND assistdetail.procloc = $id";
		
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
		header('Location:index.html?message='.urlencode("Invalid User"));
	} 
	else 
	{
		$userid = $myobj->userid;
	}

	$trans = $_REQUEST['trans'];
	if ($trans=='OVERRIDE'){
		updateDtl($userid);
		//updateDtl();
	}
	if ($trans=='LIST'){
		getList();
	}
?>