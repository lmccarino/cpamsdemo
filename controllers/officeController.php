<?php
require "routines.php";
function insertdetails($userid){
	$myobj = new stdClass();
	$officename = htmlspecialchars(strtoupper($_REQUEST['officename']),ENT_QUOTES,"UTF-8");
	$emailaddress = htmlspecialchars(strtoupper($_REQUEST['emailaddress']),ENT_QUOTES,"UTF-8");
	$officecode = htmlspecialchars(strtoupper($_REQUEST['officecode']),ENT_QUOTES,"UTF-8");
	$location = htmlspecialchars(strtoupper($_REQUEST['location']),ENT_QUOTES,"UTF-8");
	$contactperson = htmlspecialchars(strtoupper($_REQUEST['contactperson']),ENT_QUOTES,"UTF-8");
	$contactno = htmlspecialchars(strtoupper($_REQUEST['contactno']),ENT_QUOTES,"UTF-8");
	
	$officeType = htmlspecialchars(strtoupper($_REQUEST['officeType']),ENT_QUOTES,"UTF-8");
	
	//$_REQUEST['branch'];
	$active=$_REQUEST['active'];
	
	$provCat = $_REQUEST['provCat'];
	
	$tk = $_REQUEST['tk'];
	$command ="CALL insertoffice('$officecode','$officename', '$location', '$contactperson', '$contactno', '$emailaddress', '$active', '$officeType', '$provCat', $userid, '$tk')";
	
	$myobj->idoffice = -1;
	$result = getrow($command);
	if (array_key_exists('@id',$result)){
		$myobj->idoffice = $result['@id'];
	}else {$myobj->idoffice = -1;}
	$myobj->officename = $officename;
	$myobj->officecode=$officecode;
	$myobj->location=$location;
	$myobj->active=$active;
	$myobj->trans='ADD';
	echo json_encode($myobj);
}
function updatedetails($userid){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$officename = htmlspecialchars(strtoupper($_REQUEST['officename']),ENT_QUOTES,"UTF-8");
	$emailaddress = htmlspecialchars(strtoupper($_REQUEST['emailaddress']),ENT_QUOTES,"UTF-8");
	$officecode = htmlspecialchars(strtoupper($_REQUEST['officecode']),ENT_QUOTES,"UTF-8");
	$location = htmlspecialchars(strtoupper($_REQUEST['location']),ENT_QUOTES,"UTF-8");
	$contactperson = htmlspecialchars(strtoupper($_REQUEST['contactperson']),ENT_QUOTES,"UTF-8");
	$contactno = htmlspecialchars(strtoupper($_REQUEST['contactno']),ENT_QUOTES,"UTF-8");
	$active=$_REQUEST['active'];
	$idoffice=$_REQUEST['idoffice'];
	$officeType = htmlspecialchars(strtoupper($_REQUEST['officeType']),ENT_QUOTES,"UTF-8");
	$branch='';
	//$_REQUEST['branch'];
	$provCat = $_REQUEST['provCat'];
	$command ="CALL updateoffice($idoffice, '$officecode', '$officename', '$location', '$contactperson', '$emailaddress', '$active', '$contactno', '$officeType', '$provCat', $userid, '$tk')";
	getrow($command,false);
	$myobj->idoffice = $idoffice;
	echo json_encode($myobj);

}
function getdetails(){
	$table = new stdClass();
	$command = "select * from office order by `officecode`";
	$table->data = gettable($command);
	echo json_encode($table);
	
}

function deletedetail($userid){
	$myobj = new stdClass();
	$idoffice = $_REQUEST['idoffice'];
	$command ="CALL deleteoffice($idoffice, $userid)";
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
if ($trans=='delete'){
	deletedetail($userid);
}
if ($trans=='course'){
	getcourse();
}
?>