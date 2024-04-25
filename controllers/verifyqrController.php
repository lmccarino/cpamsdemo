<?php

require "routines.php";

$id = $_REQUEST['id'];

$myobj = new stdClass();

$command = "SELECT ad.rafNum, CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS patientname,
			o.officename AS provider, 
		    CONCAT(ast.assistCode,' - ',ast.assistDesc) AS assistance,
		    ad.amtApproved, ad.dateApproved
		FROM assistdetail as ad
		LEFT JOIN patient as p ON ad.idpatient = p.idpatient
		LEFT JOIN office as o ON ad.provCode = o.officecode
		LEFT JOIN assistsched as ast ON ad.idassistsched = ast.idassistsched
		WHERE ad.idassistdetails = '$id'";	

$myobj->data = gettable($command);

echo json_encode($myobj);

?>