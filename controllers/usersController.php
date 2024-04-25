<?php
require "routines.php";
require "menu.php";
function getmenu($mymenu){
	//$string = file_get_contents("menu.json");
	//$json_a = json_decode($string, true);
	echo json_encode($mymenu);
}
function priviledges($mymenu){
	$myobj = new stdClass();
	$userid = $_REQUEST['userid'];
	$token = $_REQUEST['tk'];
	//$command = "select * from access where userid=$userid";
	$command = "select rolesusers.idusers as userid, rolesaccess.access as name, sum(rolesaccess.add) as ladd, sum(rolesaccess.edit) as ledit, sum(rolesaccess.delete) as ldelete from rolesusers left join rolesaccess on rolesusers.idroles = rolesaccess.idroles where idusers = $userid group by `name`";
	$myobj->userid = $userid;
	$myobj->token = $token;
	//$string = file_get_contents("menu.json");
	//$myobj->menu = $string;
	//$json_a = json_decode($string, true);
	$myobj->menu = $mymenu;
	$myobj->access = gettable($command);
	
	echo json_encode($myobj);	
}

function getallroles(){
	$myobj = new stdClass();
	$command = "select * from roles where active = 'Y'";
	$myobj->allroles = gettable($command);
	
	echo json_encode($myobj);	
}
function getroles(){
	$myobj = new stdClass();
	$userid = $_REQUEST['userid'];
	$token = $_REQUEST['tk'];
	$command = "select * from rolesusers where idusers=$userid";
	$myobj->userid = $userid;
	$myobj->roles = gettable($command);
	
	echo json_encode($myobj);	
}


function saveroles($userid){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$roles = $_REQUEST['roles'];
	$idusers = $_REQUEST['userid'];
	$command = "DELETE FROM rolesusers WHERE idusers = $idusers and idrolesusers > 0";
	
	$r = getrow($command,false);
	foreach ($roles as $role){
		$idroles = $role;
		$command ="CALL insertroleusers($idroles, $idusers, $userid, '$tk')";
		$r = getrow($command, false);
	}
	$myobj->id = 1;
	echo json_encode($myobj);
}

function insertpriviledges($userid){
	$myobj = new stdClass();
	$menu = $_REQUEST['menu'];
	$userid1 = $_REQUEST['userid'];
	$command = "DELETE FROM access WHERE userid = $userid1 and idaccess > 0";
	$myobj->id = 1;
	$r = getrow($command,false);
	foreach ($menu as $title){
		$title2 = htmlspecialchars($title,ENT_QUOTES,"UTF-8");
		$command = "CALL insertaccess($userid1, '$title2', $userid)";
		$r = getrow($command,false);
		
	}
	
	echo json_encode($myobj);
}

function insertdetails($userid){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$fullname=strtoupper($_REQUEST['fullname']);
    $fullname=htmlspecialchars($fullname,ENT_QUOTES, "UTF-8");
    $remarks=strtoupper($_REQUEST['remarks']);
    $remarks=htmlspecialchars($remarks,ENT_QUOTES, "UTF-8");
    $emailaddress =$_REQUEST['emailaddress'];
    $password =$_REQUEST['password'];
   
    $office=strtoupper($_REQUEST['office']);
    $office=htmlspecialchars($office,ENT_QUOTES,"UTF-8");
    $cellno=htmlspecialchars($_REQUEST['cellno'],ENT_QUOTES,"UTF-8");
	$image=$_REQUEST['image1'];
	$file = $_FILES['imagefile'];
	$picture = $file['name'];
    if ($picture != ""){   
        $source =$file['tmp_name'];
		$stamp = getdate();
		$imagename = $stamp['year'].$stamp['mon'].$stamp['mday'].$stamp['hours'].$stamp['minutes'].$stamp['seconds']."_".$picture;
		$save = "../userimages/" . $imagename; //This is the new file you saving
		move_uploaded_file($source, $save);
		$check = 1;
		//$conn_id = ftp_connect(localhost); 
		//$login_result = ftp_login($conn_id, "holychild", "HolyChild@2018"); 
		//if ((!$conn_id) || (!$login_result)) { $check = 0;}
		//if ($check == 1) {
		//	$upload = ftp_put($conn_id, $save, $source, FTP_BINARY); 
		//}
		$image = $imagename;
		//if (!$upload) { $check = 0;$image = '';}
		//ftp_close($conn_id);
	} else {$image='';}
	$sig = $_REQUEST['sig1'];
	$sigfile = $_FILES['sigfile'];
	$signature = $sigfile['name'];
	if ($signature != ""){   
		$source =$sigfile['tmp_name'];
		$stamp = getdate();
		$imagename = $stamp['year'].$stamp['mon'].$stamp['mday'].$stamp['hours'].$stamp['minutes'].$stamp['seconds']."_".$signature;
		$save = "../signatures/" . $imagename; //This is the new file you saving
		move_uploaded_file($source, $save);
		$sig = $imagename;
		$check = 1;
	}
	$command = "CALL adduser('$password','$fullname', '$remarks', '$office', '$cellno', '$emailaddress','$image', '$sig',$userid, '$tk')";
	$myobj->userid = -1;
	require "connect.php";
	//die($command);
	if ($result=$conn->query($command)) {
		$row = $result->fetch_assoc();
		$myobj->userid = $row['@id'];
		$myobj->fullname = $fullname;
		
		$myobj->active='Y';
		$myobj->remarks=$remarks;
		$myobj->office=$office;
		$myobj->cellno=$cellno;
		$myobj->emailaddress=$emailaddress;
		$myobj->image=$image;
		$myobj->ip='';
		$myobj->login='';
		$myobj->image=$image;
		$myobj->signature =$sig;
		$myobj->trans='ADD';
	} 
	$conn->close();
	unset($conn);
	echo json_encode($myobj);
}
function updatedetails($userid1){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$userid = $_REQUEST['userid'];
	$password = htmlspecialchars($_REQUEST['password'],ENT_QUOTES,"UTF-8");
	$fullname = htmlspecialchars(strtoupper($_REQUEST['fullname']),ENT_QUOTES,"UTF-8");
	$active=$_REQUEST['active'];
	$remarks=$_REQUEST['remarks'];
	$office=$_REQUEST['office'];
	$cellno=$_REQUEST['cellno'];
	$image=$_REQUEST['image1'];
	$file = $_FILES['imagefile'];
	$picture = $file['name'];
	$office=strtoupper($_REQUEST['office']);
    $office=htmlspecialchars($office,ENT_QUOTES,"UTF-8");
    if ($picture != ""){   
        $source =$file['tmp_name'];
		$stamp = getdate();
		$imagename = $stamp['year'].$stamp['mon'].$stamp['mday'].$stamp['hours'].$stamp['minutes'].$stamp['seconds']."_".$picture;
		$save = "../userimages/" . $imagename; //This is the new file you saving
		move_uploaded_file($source, $save);
		$check = 1;
		
		$image = $imagename;
		
	} 
	$sig = $_REQUEST['sig1'];
	$sigfile = $_FILES['sigfile'];
	$signature = $sigfile['name'];
	if ($signature != ""){   
		$source =$sigfile['tmp_name'];
		$stamp = getdate();
		$imagename = $stamp['year'].$stamp['mon'].$stamp['mday'].$stamp['hours'].$stamp['minutes'].$stamp['seconds']."_".$signature;
		$save = "../signatures/" . $imagename; //This is the new file you saving
		move_uploaded_file($source, $save);
		$sig = $imagename;
		$check = 1;
	}
	if (empty($password)) {
		$command = "CALL edituser('$fullname',  '$remarks', '$office', '$cellno','$active','$image' , '$sig',$userid, $userid1, '$tk')";
	} else {	
		$command = "CALL edituser2('$password', '$fullname',  '$remarks', '$office', '$cellno','$active', '$image', '$sig', $userid, $userid1, '$tk')";
	}
	
	$myobj->userid = -1;
	require "connect.php";
	if ($result=$conn->query($command)) {
		$myobj->userid = $userid;
		$myobj->fullname = $fullname;
		$myobj->active='Y';
		$myobj->remarks=$remarks;
		$myobj->office=$office;
		$myobj->cellno=$cellno;
		$myobj->image=$image;
		$myobj->signature =$sig;
		$myobj->trans ='UPDATE';
	} 
	$conn->close();
	unset($conn);
	echo json_encode($myobj);
}
function getdetails(){
	$table = new stdClass();
	$command = "select users.*, office.officename from users left join office on users.office = office.officecode order by fullname";
	$table->data = gettable($command);
	echo json_encode($table);
}
function getoffices(){
	$table = new stdClass();
	$command = "select * from office where active = 'Y'  order by `officename`";
	$table->data = gettable($command);
	echo json_encode($table);
}
function getprofile(){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$command ="select userid from tk where token = '$tk'";
	$myobj->result = getrow($command);
	$userid = $myobj->result['userid'];
	$command = "select * from users where userid = $userid";
	$myobj->user = getrow($command);
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
if ($trans=='getoffices'){
	getoffices();
}
if ($trans=='getprofile'){
	getprofile();
}
if ($trans=='getroles'){
	getroles();
}
if ($trans=='getallroles'){
	getallroles();
}
if ($trans=='roles'){
	saveroles($userid);
}
?>