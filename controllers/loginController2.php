<?php
require "routines.php";
$myobj = new stdClass();
$userid=$_REQUEST['userid'];
$webauthnid =$_REQUEST['webauthnid'];
$message ='';
$myobj->userid = -1;
if (!empty($userid)){
	
	$command="CALL validateuser2($userid, '$webauthnid')";

	$row = getrow($command);
	if (!empty($row)) {
			$myobj->userid = $row['userid'];
			$myobj->fullname = $row['fullname'];
			if (!empty($row['image'])){
				$myobj->image = $row['image'];
			} else {
				$myobj->image = 'person.jpg';
			}
			$myobj->email = $row['emailaddress'];
			$myobj->office = $row['office'];
			$myobj->role = '';
			$myobj->signature = $row['signature'];
			
			$tk = savetk($myobj->userid, $myobj->fullname, $myobj->image, $myobj->office, $myobj->role, $myobj->email, $myobj->signature);
			//$myobj->token = $tk['@id'];
			
			//$tk = savetk($myobj->userid, $myobj->fullname, $myobj->image, $myobj->schoolyear, $myobj->idsy, $myobj->location, $myobj->semester, $myobj->role, $myobj->email);
			$myobj->token = $tk['@token'];
		}
} else { 
	logout();
} 

echo json_encode($myobj);
?>