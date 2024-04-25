<?php 

require "routines.php";

class ClientsServed
{
	public function getTotal($cat, $datefrom, $dateto, $col){
		$row = new stdClass();
		$command = "SELECT COUNT(a.idassistdetails) as totalserved, 
		SUM(a.amtApproved) as totalamount
		FROM assistdetail as a 
		LEFT JOIN office as b ON a.provCode = b.officecode
		WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
		AND a.status = 'APPROVED'
		AND b.provCat = '$cat' 
		GROUP BY b.provCat";
		$row->data = getrow($command);
		foreach ($row as $value){
			if($col=='totalserved')
				return $value['totalserved'];			
			else if($col=='totalamount') 
				return $value['totalamount'];				
		}	
	}

	public function getPlatform($type, $datefrom, $dateto, $col){
		$row = new stdClass();
		$command = "SELECT COUNT(a.idassistdetails) as totalserved, 
		SUM(a.amtApproved) as totalamount
		FROM assistdetail as a 
		WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
		AND a.status = 'APPROVED'
		AND a.typeClient = '$type' 
		GROUP BY a.typeClient";
		$row->data = getrow($command);
		foreach ($row as $value){
			if($col=='totalserved')
				return $value['totalserved'];			
			else if($col=='totalamount') 
				return $value['totalamount'];				
		}	
	}

	function getUser($id, $col) {
		$row = new stdClass();
		$command = "SELECT fullname, position FROM users WHERE userid = '$id'";	
		$row->data = getrow($command);	

		foreach ($row as $value){
			if($col=='fullname')
				return $value['fullname'];			
			else if($col=='position') 
				return $value['position'];		
		}		
	}
}
?>