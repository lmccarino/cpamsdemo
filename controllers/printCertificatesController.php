<?php

require "routines.php";

class PrintCertificates 
{	
	function getAssistDetail($id) {		
		$row = new stdClass();
		// $command = "SELECT * FROM assistdetail WHERE idassistdetails = '$id'";	
		$command = "SELECT ad.*, ac.assistCode, ac.assistDesc
					FROM assistdetail AS ad LEFT JOIN assistsched AS ac 
					ON ad.idassistsched = ac.idassistsched
					WHERE idassistdetails = '$id'";
		$row = getrow($command);	

		return $row;
	}

	function getOfficer($data) {
		$row = new stdClass();
		$command = "SELECT * FROM office WHERE officecode = '$data'";	
		$row = getrow($command);	
		
		return $row;
	}
	
	function getAssistCode($id, $col) {
		$row = new stdClass();
		$command = "SELECT assistCode, assistDesc FROM assistsched WHERE idassistsched = '$id'";	
		$row->data = getrow($command);	

		foreach ($row as $value){
			if($col=='code') {
				if ($value['assistCode'] == 'PROCEDURE' || $value['assistCode'] == 'LABORATORY')
					return $value['assistDesc'];
				return $value['assistCode'];
			}
			else if($col=='desc') 
				return $value['assistDesc'];		
		}		
	}

	function getPatient($id, $col) {
		$row = new stdClass();
		$command = "SELECT * FROM patient WHERE idpatient = '$id'";	
		$row->data = getrow($command);	

		foreach ($row as $value){
			if($col=='name')
				return $value['benLName'].', '.$value['benFName'].' '.$value['suffix'].' '.$value['benMName'];			
			else if($col=='address') 
				return $value['benAddrSt'];
			else if($col=='barangay') 
				return $value['brgyCode'];		
		}		
	}

	function getBarangay($code) {
		$row = new stdClass();
		$command = "SELECT brgyName FROM distbrgy WHERE brgyCode = '$code'";	
		$row->data = getrow($command);	

		foreach ($row as $value){
			return $value['brgyName'];		
		}		
	}

	function getPatientDetails($id) {
		$row = new stdClass();
		$command = "SELECT 
						CONCAT(p.benLName, ', ', p.benFName, ' ', p.suffix, ' ', p.benMName) AS patientname,
						p.benAddrSt AS patientaddress,
						(SELECT brgyName FROM distbrgy WHERE distbrgy.brgyCode = p.brgyCode) AS brgyname
					FROM patient as p WHERE idpatient = '$id'";
		$row = getrow($command);	

		return $row;	
	}

	function getMedicines($id) {
		$row = new stdClass();
		$command = "SELECT ph.pharmaname, pcm.amount 
					FROM pcom_details pcm
					INNER JOIN pcom_pharmacy ph ON ph.id = pcm.pcom_pharmacyid
					WHERE idassistdetails = $id;";

		return gettable($command);	
	}

	function getGLSignatory(){
		$row = new stdClass();
		$command = "SELECT 
						u.fullname, 						
						u.signature,
						u.signposition,
						r.idroles
					FROM users AS u 
					LEFT JOIN rolesusers AS ro ON u.userid = ro.idusers
					LEFT JOIN roles AS r ON ro.idroles = r.idroles
					WHERE r.name = 'GLSIGNATORY' AND r.active = 'Y' AND u.active = 'Y'
					LIMIT 1";
		$row = getrow($command);	

		return $row;	
	}

	function getReissueMsg($row) {
		$msg = '';
		
		if ( !empty($row['dateReissue']) ) {
			$date = date('m/d/Y h:i:s A', strtotime($row['dateApproved']));
			$msg = "Note: This is a Re-Issued Guarantee Letter. Original GL was dated '$date'";
		}
		return $msg;
	}
	
	function isReissued($row) {
		return empty($row['dateReissue']) ? false : true;
	}

	function getUser($id) {
		$row = new stdClass();
		$command = "SELECT fullname FROM users WHERE userid = '$id'";	
		$row->data = getrow($command);	

		foreach ($row as $value){
			return $value['fullname'];				
		}		
	}

	function getIntakePatient($id) {
		$row = new stdClass();
		$command = "SELECT
						i.remarks, i.sworker, i.requestor, i.relation, i.userid, i.details,	
						CONCAT(p.benFName,' ',p.benMName,' ',p.benLName, ' ', p.suffix) AS patientname,
						p.benAddrSt AS patientaddress, p.benBDate, p.effectivitydate, 
						(SELECT brgyName FROM distbrgy WHERE distbrgy.brgyCode = p.brgyCode) AS brgyname
					FROM intake AS i
					LEFT JOIN patient AS p ON i.idpatient = p.idpatient 
					WHERE i.idassistdetails =  '$id'";
		$row = getrow($command);	

		return $row;	
	}

	function getCSSDOSignatory(){
		$row = new stdClass();
		$command = "SELECT 
						u.fullname, 						
						u.signature,
						u.signposition,
						r.idroles
					FROM users AS u 
					LEFT JOIN rolesusers AS ro ON u.userid = ro.idusers
					LEFT JOIN roles AS r ON ro.idroles = r.idroles
					WHERE r.name = 'CSSDOSIGNATORY' AND r.active = 'Y' AND u.active = 'Y'
					LIMIT 1";
		$row = getrow($command);	

		return $row;	
	}
}

?>