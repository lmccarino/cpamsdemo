<?php

require "routines.php";

class BeneficiaryPrint 
{	
	
	function getLocation($id) {	
		$row = new stdClass();
		$command = "SELECT officecode FROM office WHERE idoffice = '$id'";	
		$row->data = getrow($command);	

		foreach ($row as $value){		
			return $value['officecode'];		
		}		
	}

	function getDetails($from, $to, $cat, $loc) {		
		$row = new stdClass();
		if ($cat == 'ALL'){
			$command = "SELECT a.rafNum, 
							CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS beneficiary,
							o.officename AS provider, a.assistCode, a.amtApproved, 
							a.dateApproved
						FROM assistdetail as a 
						LEFT JOIN patient as p ON a.idpatient = p.idpatient
						LEFT JOIN office as o ON a.provCode = o.officecode
						WHERE a.dateApproved BETWEEN '$from' AND '$to'
						AND a.procloc = '$loc'";	
		} 
		else
		{
			$command = "SELECT a.rafNum, 
							CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS beneficiary,
							o.officename AS provider, a.assistCode, a.amtApproved, 
							a.dateApproved
						FROM assistdetail as a 
						LEFT JOIN patient as p ON a.idpatient = p.idpatient
						LEFT JOIN office as o ON a.provCode = o.officecode
						WHERE a.dateApproved BETWEEN '$from' AND '$to'
						AND o.provCat = '$cat' AND a.procloc = '$loc'";	
		}
		$row->data = gettable($command);	

		return $row;
	}
}

?>