<?php

require "../routines.php";

class PrintTransmittals 
{	
	function getUser($id) {
		$row = new stdClass();
		$command = "SELECT fullname FROM users WHERE userid = '$id'";	
		$row->data = getrow($command);	

		foreach ($row as $value){
			return $value['fullname'];				
		}		
	}
	
	function get($id) {
		$qry =  "SELECT 
					t.*,
					o.officename AS providerName, 
					o.provcat AS providerCategory
				 FROM transmittals AS t
				 INNER JOIN office AS o ON t.provcode = o.officecode 
				 WHERE t.idtransmittals = $id LIMIT 1";
		$result = getrow($qry);

		return $result;
	}

	function getRAFs($id) {
		$qry =  "SELECT ad.*, p.benLName, p.benFName, p.benMName, p.suffix, p.benSex, p.benBDate, p.benAddrSt
                 FROM assistdetail AS ad
                 LEFT JOIN patient AS p ON p.idpatient = ad.idpatient
                 WHERE ad.idtransmittals = $id
                 ORDER BY p.benLName, p.benFName";
		$result = gettable($qry);

		return $result;
	}
}