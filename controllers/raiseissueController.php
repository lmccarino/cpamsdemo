<?php
require "routines.php";

function getactive($userid){
	$myobj = new stdClass();
	$command = "select issues.*, issuetrans.status from issues left join issuetrans on issues.idissues = issuetrans.idissues where issues.filedby = $userid and issuetrans.enddate is null";
	$myobj->data = gettable($command);
	echo json_encode($myobj);
	
}

function insertdetails($userid){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$content = htmlspecialchars($_REQUEST['ckcontent'],ENT_QUOTES,"UTF-8");
	$subject = htmlspecialchars(strtoupper($_REQUEST['subject']),ENT_QUOTES,"UTF-8");
	$command = "CALL insertissues('$content', '$subject', $userid, '$tk')";
	$row = getrow($command);
	$myobj->idissues = $row['@id'];
	echo json_encode($myobj);
	
}
function updatetdetails($userid){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$content = htmlspecialchars($_REQUEST['ckcontent'],ENT_QUOTES,"UTF-8");
	$subject = htmlspecialchars(strtoupper($_REQUEST['subject']),ENT_QUOTES,"UTF-8");
	$idissues = $_REQUEST['idissues'];
	$command ="CALL updateissues($idissues, '$content', '$subject', $userid, '$tk')";
	getrow($command,false);
	$myobj->idissues = $idissues;
	echo json_encode($myobj);
}
function deletedetails($userid){
	$myobj = new stdClass();
	$idissues= $_REQUEST['idissues'];
	
	$tk = $_REQUEST['tk'];
	$command = "CALL deleteissues($idissues, $userid, '$tk')";
	$row=getrow($command);
	$myobj->idissues = $idissues;
	echo json_encode($myobj);
	
}
function submit($userid){
	$myobj = new stdClass();
	$idissues= $_REQUEST['idissues'];
	$tk = $_REQUEST['tk'];
	$command = "CALL submittrans($idissues, $userid, '$tk')";
	$row = getrow($command);
	$myobj->idissues = $row['@id'];
	echo json_encode($myobj);
}
function gettrans(){
	$idissues = $_REQUEST['idissues'];
	$command ="select issuetrans.*, users.fullname from issuetrans left join users on issuetrans.userid = users.userid where idissues = $idissues";
	$myobj = new stdClass();
	$myobj->data = gettable($command);
	echo json_encode($myobj);

	
}

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User"));
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
if ($trans=='getallactive'){
	getactive($userid);
}
if ($trans=='ADD'){
	insertdetails($userid);
}
if ($trans=='UPDATE'){
	updatetdetails($userid);
}
if ($trans=='delete'){
	deletedetails($userid);
}
if ($trans=='submit'){
	submit($userid);
}
if ($trans=='gettrans'){
	gettrans();
}


?>