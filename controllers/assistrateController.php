<?php
	require "routines.php";

	function newRate($userid){
		require "connect.php";
		$code=$_REQUEST['rate'];
		$fr=$_REQUEST['from'];
		$to=$_REQUEST['to'];

		$command ="SELECT * FROM assistrate WHERE rateCode='$code' AND baseFrom <='$fr' AND baseTo >='$fr'";
		$x = 0;
		if ($result=mysqli_query($conn,$command))
		{
			$x=mysqli_num_rows($result);
			mysqli_free_result($result);
		}
		$conn->close();
		unset($conn);
		
		if ($x > 0) 
		{
			echo 0;
		}
		else {
			$tk = $_REQUEST['tk'];
			$code=$_REQUEST['rate'];
			$fr=$_REQUEST['from'];
			$to=$_REQUEST['to'];
			$amt=$_REQUEST['amt'];
		
			$command = "call newAssistRate('$code', $fr, $to, $amt,  $userid, '$tk')";
			$result = getrow($command);
			//echo $code . "-" . $fr. "-" . $to. "-" . $amt. "-" . $tk;
			$data = $result['@id'];
			//echo json_encode($data);
			echo $data;
		}
		
	}

	function editRate($userid){		
		$tk = $_REQUEST['tk'];
		$id=$_REQUEST['recid'];
		$fr=$_REQUEST['from'];
		$t=$_REQUEST['to'];
		$amt=$_REQUEST['amt'];

		$command = "call updateAssistRate($id, $fr, $t, $amt,  $userid, '$tk')";
		$result = getrow($command);
		$data = $result['@id'];
		echo $data;
	}

	function getdetails(){
		$command ="select * from assistrate";
		$myobj = new stdClass();
		$myobj->data = gettable($command);
		echo json_encode($myobj);
	}

	function getassistance(){
		$myobj = new stdClass();
		$rateCode = $_REQUEST['rateCode'];
		$command ="SELECT * FROM assistsched WHERE rateCode = '$rateCode'";
		$myobj->data = gettable($command);
		echo json_encode($myobj);
	}


	function updateSched($userid, $stat){
		$tk = $_REQUEST['tk'];
		$id=$_REQUEST['id'];
		$desc=$_REQUEST['assistDesc'];
		
		$command ="call updateAssistSched($id, $userid, '$tk', '$desc', '$stat')";
		
		getrow($command, false);
		//echo json_encode($data);
		echo $id;
	}

	function newSchedule($userid){
		$tk = $_REQUEST['tk'];
		$rate=$_REQUEST['rateCode'];
		$code=$_REQUEST['assistCode'];
		$desc=$_REQUEST['assistDesc'];
		
		require "connect.php";
		$command ="SELECT * FROM assistsched WHERE rateCode='$rate' AND assistCode='$code' AND assistDesc ='$desc'";
		$x = 0;
		if ($result=mysqli_query($conn,$command))
		{
			$x=mysqli_num_rows($result);
			mysqli_free_result($result);
		}
		$conn->close();
		unset($conn);

		
		if ($x > 0) 
		{
			echo 0;
		}
		else {
			$command ="call insertAssistSched('$rate', '$code','$desc',  $userid, '$tk')";
			$result = getrow($command);
			$data = $result['@id'];
			echo $data;
		}
	}

	function changeStatus($userid){
		$tk = $_REQUEST['tk'];
		$id=$_REQUEST['recid'];
		$stat=$_REQUEST['stat'];
		
		$command ="call assistRateStatus($id, $userid, '$tk', '$stat')";
		getrow($command, false);
		//echo json_encode($data);
		echo $id;
	}

	function changeStatus2($userid){
		$tk = $_REQUEST['tk'];
		$recid=$_REQUEST['recid'];
		$stat=$_REQUEST['stat'];
		
		$command ="call assistSchedStatus($recid, $userid, '$tk', '$stat')";
		getrow($command, false);
		//echo json_encode($data);
		echo $recid;
	}



	$myobj = validatetoken();

	if (empty($myobj->userid)){
		header('Location:../index.html?message='.urlencode("Invalid User"));
	} else {$userid = $myobj->userid;}

	$trans = $_REQUEST['trans'];

	if ($trans=='getdetails'){
		getdetails();
	}
	if ($trans=='getassistance'){
		getassistance();
	}
	if ($trans=='UPDATE'){
		updateSched($userid, 'Y');
	}
	if ($trans=='EXCLUDE'){
		updateSched($userid,'N');
	}
	if ($trans=='NEW'){
		newRate($userid);
	}
	if ($trans=='EDIT'){
		editRate($userid);
	}
	if ($trans=='NEW2'){
		newSchedule($userid);
	}
	if ($trans=='STATUS'){
		changeStatus($userid);
	}
	if ($trans=='STATUS2'){
		changeStatus2($userid);
	}

?>