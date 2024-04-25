<?php
require "routines.php";
function getdetails(){
	$myobj = new stdClass();
	$command = "select tk.*, office.officename from tk left join office on tk.office = office.officecode";
	$myobj->data = gettable($command);
	echo json_encode($myobj);
	
}
function gettrans(){
	$myobj = new stdClass();
	$tk2 = $_REQUEST['tk2'];
	$command = "select * from translog where tk = '$tk2'";
	
	$myobj->data = gettable($command);
	echo json_encode($myobj);
	
}

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:,,/index.html?message='.urlencode("Invalid User"));
	die('not authorized');
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
if ($trans=='getdetails'){
	getdetails();
}
if ($trans=='gettrans'){
	gettrans();
}

?>