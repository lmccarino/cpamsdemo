<?php

require "routines.php";

class PrintAll 
{	
    function getDetails($id) {		
		$row = new stdClass();
		$command = "SELECT * FROM assistdetail WHERE idassistdetails = '$id'";	
		$row->data = getrow($command);	

		return $row;
	}


}