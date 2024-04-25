<?php

require "routines.php";

class PrintGL 
{	
	function getDetails($id) {		
		$row = new stdClass();
		$command = "SELECT * FROM assistdetail WHERE idassistdetails = '$id'";	
		$row->data = getrow($command);	

		return $row;
	}

	function getOfficer($data, $col) {
		$row = new stdClass();
		$command = "SELECT * FROM office WHERE officecode = '$data'";	
		$row->data = getrow($command);	

		foreach ($row as $value){
			if($value['provCat']=='GOVERNMENT'){
				if($col=='contactperson')
					return $value['contactperson'];			
				else if($col=='position') 
					return $value['position'];			
				else if($col=='provider')
					return $value['officename'];
				else if($col=='location')
					return $value['location'];	
			} else {
				$contact = 'THE MANAGER';			
				if($col=='contactperson')
					return 'THE MANAGER'; //$contact;			
				else if($col=='position') 
					return $value['position'];			
				else if($col=='provider')
					return $value['officename'];
				else if($col=='location')
					return $value['location'];
			}
					
		}		
	}
	
	function getGLDate($row) {
		$date = empty($row['dateReissue']) ? $row['dateApproved'] : $row['dateReissue'];
		return strtotime($date);
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
	
	function getMedicines($idassistdetails) {
		$row = new stdClass();
		$command = "SELECT ph.pharmaname, pcm.amount 
					FROM pcom_details pcm
					INNER JOIN pcom_pharmacy ph ON ph.id = pcm.pcom_pharmacyid
					WHERE idassistdetails = $idassistdetails;";
		return gettable($command);		
	}

	function getSignatory($id, $col) {
		$row = new stdClass();
		$command = "SELECT * FROM signatory WHERE id = '$id'";	
		$row->data = getrow($command);	

		foreach ($row as $value){
			if($col=='name')
				return $value['aSignName'];			
			else if($col=='position') 
				return $value['aSignPosn'];		
		}		
	}

	function getUser($id) {
		$row = new stdClass();
		$command = "SELECT fullname FROM users WHERE userid = '$id'";	
		$row->data = getrow($command);	

		foreach ($row as $value){
			return $value['fullname'];				
		}		
	}

	function updatefilename($id, $filename){			
		$command ="CALL saveglform($id, '$filename')";
		getrow($command, false);		
	}
}




?>