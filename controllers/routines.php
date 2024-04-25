<?php
function getrow($command, $none=true){
	require "connect.php";
	$row = array();
	if ($result=$conn->query($command)){
		if ($none) {$row = $result->fetch_assoc();}
	} else $row = array('error');
	$conn->close();
	unset($conn);
	
	return $row;
}
function getrowRead($command, $none=true){
	require "connectR.php";
	$row = array();
	if ($result=$connRead->query($command)){
		if ($none) {$row = $result->fetch_assoc();}
	} else $row = array('error');
	$connRead->close();
	unset($connRead);
	
	return $row;
}
function gettable($command){
	$table = array();
	require "connectR.php";
	if ($result=$connRead->query($command)){
		$table = $result->fetch_all(MYSQLI_ASSOC);
	} 
	$connRead->close();
	unset($connRead);
	
	return $table;
}
function getmultitable($command){
	$acom = explode(";",$command);
	require "connect.php";
	$tables = array();
	$conn->multi_query($command);
	//die($acom[0]."-".$acom[1]."-".$acom[2]."-".$acom[3]);
	for ($x = 0; $x < count($acom)-1; $x++){
			if ($result=$conn->store_result()) {
				$tables[] = $result->fetch_all(MYSQLI_ASSOC);
			} else { $tables[] =array(error=>$acom[$x]);}
			$conn->next_result();
	}
	$conn->close();
	unset($conn);
	
	return $tables;
}
function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $ip = substr($ip, 0, strrpos($ip, ":", 0));
    return $ip;
}
function savetk($userid, $fullname, $image, $office, $role, $email, $signature){
	$ip = getUserIpAddr();
	$ldate = date('Y-m-d H:i:s');
	$command = "CALL inserttk($userid, '$fullname', '$image', '$office', '$role', '$email', '$ldate', '$ip', '$signature')";
	$token = getrow($command);
	return $token;
}
function validatetoken(){
	$token = $_REQUEST['tk'];
	$myobj = new stdClass();
if (!empty($token)){
	
	$ip = getUserIpAddr();
	$ldate = date("Y-m-d");
	//$command = "select * from tk where idtk = $token and ip = '$ip' and ldate = '$ldate'";
	$command = "select * from tk where token = '$token' and ip = '$ip' and date(ldate) = '$ldate' and odate is null;";
	$data = getrow($command);
	if (!empty($data) ){
		$myobj->userid = $data['userid'];
		$myobj->fullname = $data['fullname'];
		$myobj->image =  $data['image'];
		$myobj->location = $data['location'];	
		$myobj->token = $token;
		$myobj->error = false;
		$myobj->tkform = $data['tkform'];
		$myobj->signature = $data['signature'];

		$command2 ="CALL updatetk('$token','$ip')";
		$data2 = getrow($command2);
	}
} else {$myobj->error = true;}
return $myobj;
}
function logout(){
	$tk = $_REQUEST['tk'];
	$command ="CALL logouttk('$tk')";
	
	getrow($command,false);
	return $tk;
}
function gentkform($token, $userid){
		$command ="CALL gentkform('$token', $userid)";
		$row = getrow($command);
		$tkform = $row['@tkform'];
 return $tkform;
}
function cleartkform($token, $userid){
	$command = "CALL cleartkform('$token', $userid)";
	getrow($command,false);
}
function hasRole($userid, $role){
	$command = "select r.name from rolesusers ru left join roles r ON r.idroles = ru.idroles where ru.idusers = $userid and r.name = '$role'";
	$row = getrow($command);
	return (!empty($row)) ? true : false;
}
date_default_timezone_set("Asia/Manila");
$mysys = new stdClass();
$mysys->baseurl = $_SERVER['SERVER_NAME']."/";
$mysys->uri = "";
if (isset($_SERVER['HTTPS'])){
	$mysys->prtcol = 'https://';
} else {
	$mysys->prtcol = 'http://';
}
$mysys->path = $mysys->prtcol.$mysys->baseurl.$mysys->uri;
?>
