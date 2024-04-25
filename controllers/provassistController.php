<?php
require "routines.php";
function insertdetails($userid){
	$myobj = new stdClass();
	
	$officecode = htmlspecialchars(strtoupper($_REQUEST['officecode']),ENT_QUOTES,"UTF-8");
	$assistCode = htmlspecialchars(strtoupper($_REQUEST['assistCode']),ENT_QUOTES,"UTF-8");
	$idoffice = $_REQUEST['idoffice'];
	$tk = $_REQUEST['tk'];
	$command = "CALL insertprovassist('$officecode', '$assistCode', $userid, '$tk')";
	$result = getrow($command);
	if (array_key_exists('@id',$result)){
		$myobj->idprovassist = $result['@id'];
	}else {$myobj->idprovassist = -1;}
	$myobj->idoffice = $idoffice;
	$myobj->trans='ADD';
	echo json_encode($myobj);
}

function getdetails(){
	$table = new stdClass();
	$command = "select * from office where officeType = 'PROVIDER' and active ='Y' order by `officecode`";
	$table->data = gettable($command);
	echo json_encode($table);
	
}
function getassistance(){
	$table = new stdClass();
	$officecode = $_REQUEST['officecode'];
	$command = "select * from provassist where officecode = '$officecode'";
	$table->data = gettable($command);
	echo json_encode($table);
	
}

function deletedetail($userid){
	$myobj = new stdClass();
	$idoffice = $_REQUEST['idoffice'];
	$idprovassist = $_REQUEST['idprovassist'];
	$tk = $_REQUEST['tk'];
	$command ="CALL deleteprovassist($idprovassist, $userid, '$tk')";
	$myobj->idoffice=$idoffice;
	getrow($command,false);
	echo json_encode($myobj);
}
function updatedetails($userid){
	$myobj = new stdClass();
	$idoffice = $_REQUEST['idoffice'];
	$idprovassist = $_REQUEST['idprovassist'];
	$active = $_REQUEST['active'];
	$tk = $_REQUEST['tk'];
	$command ="CALL updateprovassist($idprovassist, '$active', $userid, '$tk')";
	$myobj->idoffice=$idoffice;
	getrow($command,false);
	echo json_encode($myobj);
}
$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
if ($trans=='ADD'){
	insertdetails($userid);
}
if ($trans=='UPDATE'){
	updatedetails($userid);
}
if ($trans=='getdetails'){
	getdetails();
}
if ($trans=='getassistance'){
	getassistance();
}
if ($trans=='delete'){
	deletedetail($userid);
}

?>