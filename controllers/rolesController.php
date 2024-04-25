<?php
require "routines.php";
require "menu.php";
function getmenu($mymenu){
	//$string = file_get_contents("menu.json");
	//$json_a = json_decode($string, true);
	echo json_encode($mymenu);
}
function getdetails(){
	$myobj = new stdClass();
	$command = "select * from roles";
	$myobj->data = gettable($command);
	echo json_encode($myobj);
	
}
function priviledges($mymenu){
	$myobj = new stdClass();
	$idroles = $_REQUEST['idroles'];
	$command = "select * from rolesaccess where idroles=$idroles";
	$myobj->menu = $mymenu;
	$myobj->access = gettable($command);
	$myobj->idroles = $idroles;
	echo json_encode($myobj);	
}
function insertpriviledges($userid){
	$myobj = new stdClass();
	$menu = $_REQUEST['menu'];
	$idroles = $_REQUEST['idroles'];
	$tk = $_REQUEST['tk'];
	$myobj->id = 1; 

	$command = "CALL deleterolesaccess($idroles,$userid, '$tk')";
	$r = getrow($command,false);
	
	foreach ($menu as $title){
		$access = htmlspecialchars($title,ENT_QUOTES,"UTF-8");
		$add ='';
		$edit ='';
		$delete = '';
		$key = str_replace(" ", "_", $access);
		if (array_key_exists('add'.$key, $_REQUEST)){
			$add = "1";
		} 
		
		if (array_key_exists('edit'.$key, $_REQUEST)){
			$edit = "1";
		}
		if (array_key_exists('delete'.$key, $_REQUEST)){
			$delete = "1";
		}
		$command ="CALL insertrolesaccess($idroles, '$access', '$add', '$edit', '$delete', $userid, '$tk')";
		
		
		$r = getrow($command,false);
		
	}
	
	echo json_encode($myobj);
}

function insertdetails($userid){
	$myobj = new stdClass();
	$name = htmlspecialchars(strtoupper($_REQUEST['name']),ENT_QUOTES,"UTF-8");
	$tk = $_REQUEST['tk'];
	$command ="CALL insertroles('$name', 'Y', $userid, '$tk')";
	$row = getrow($command);
	$myobj->idroles = $row['@id'];
	echo json_encode($myobj);
	
}
function updatedetails($userid){
	$myobj = new stdClass();
	$name = htmlspecialchars(strtoupper($_REQUEST['name']),ENT_QUOTES,"UTF-8");
	$tk = $_REQUEST['tk'];
	$idroles = $_REQUEST['idroles'];
	$active = $_REQUEST['active'];
	$command ="CALL updateroles($idroles, '$name', '$active', $userid, '$tk')";
	$row = getrow($command);
	$myobj->idroles = $row['@id'];
	echo json_encode($myobj);

}
function deleterole($userid){
	$myobj = new stdClass();
	$idroles= $_REQUEST['idroles'];
	$tk = $_REQUEST['tk'];
	$command = "CALL deleteroles($idroles, $userid, '$tk')";
	$row=getrow($command);
	$myobj->idroles = $idroles;
	echo json_encode($myobj);
	
}

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:'.$mysys->path.'index.html?message='.urlencode("Invalid User"));
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

if ($trans=='getmenu'){
	getmenu($mymenu);
}
if ($trans=='priviledges') {
	priviledges($mymenu);
}
if ($trans=='access'){
	insertpriviledges($userid);
}
if ($trans=='delete'){
	deleterole($userid);
}
?>