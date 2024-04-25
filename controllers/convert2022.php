<?php
require "routines.php";
//$myobj = new stdClass();
$command = "select assistdetail.rafNum, assistdetail.idassistdetails, allrequestassist2022.idpatient from assistdetail inner join allrequestassist2022 on assistdetail.rafNum = allrequestassist2022.rafNum where assistdetail.idpatient is null limit 5000";
$table = gettable($command);

for($i = 0; $i < count($table); $i++) {
	$row = $table[$i];
	$idpatient = $row['idpatient'];
	$idassistdetails = $row['idassistdetails'];
	
	$command = "update assistdetail set idpatient = $idpatient where idassistdetails = $idassistdetails";
	
	getrow($command,false);
	echo $command."<br/>";
}
echo "transaction done";
?>