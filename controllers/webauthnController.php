<?php
require "routines.php";

function getdevs($userid){
	$table = new stdClass();
	$command = "select * from webauthn where userid = $userid";
	$table->data = gettable($command);
	echo json_encode($table);
}
function verify($userid, $fullname){
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
         $url = "https://";   
    else  
         $url = "http://";   

	$url = $url. $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
	$parse = parse_url($url);
	$host = $parse['host'];
	$command = "select * from webauthn where userid = $userid limit 1";
	$row = getrow($command);
	if (!empty($row)) {
		$authnid = $row['authnid'];
		$challenge = $row['challenge'];
		$error = false;
	} else {
		$authnid = '';
		$challenge = '';
		$error = true;
	}	
	$extensions = array("uvi"=>true,"loc"=>true,"uvm"=>true);
	$rp = array("name"=>"WebAuthn KMJ", "id"=>$host);
	$user = array("displayName"=>$fullname,"id"=>$authnid,"name"=>$fullname);
	$pubKeyCredParams = array(array("type"=>"public-key","alg"=>-7),array("type"=>"public-key","alg"=>-257));
	$excludeCredentials = array(array("id"=>"None","type"=>"public-key","transports"=>array("internal")));
	$authenticatorSelection = array("authenticatorAttachment"=>"platform","userVerification"=>"required");
	$createArgs = array("publicKey"=>array("extensions"=>$extensions, "rp"=>$rp, "user"=>$user, "challenge"=>$challenge, "pubKeyCredParams"=>$pubKeyCredParams,"timeout"=>1800000,"attestation"=>"none","excludeCredentials"=>$excludeCredentials,"authenticatorSelection"=>$authenticatorSelection),"error " => $error);

	
	header('Content-Type: application/json');
    print(json_encode($createArgs));
	
}
function verify2($userid){
	$myobj = new stdClass();
	$myobj->error = false;
	$clientDataJSON = $_REQUEST['clientDataJSON'];
	$command = "select * from webauthn where userid = $userid and clientDataJSON = '$clientDataJSON' limit 1";
	$row = getrow($command);
	if (empty($row)) {
		$myobj->error = true;
	} else {
		$myobj->error = false;
	}
	echo json_encode($myobj);
	
	
}
function getCreateArgs($userid, $fullname){
	$myobj = new stdClass();
	$token = $_REQUEST['tk'];
	cleartkform($token, $userid);
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
         $url = "https://";   
    else  
         $url = "http://";   

	$url = $url. $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
	$parse = parse_url($url);
	$host = $parse['host'];
	$command = "select * from webauthn where userid = $userid limit 1";
	$row = getrow($command);
	$authnid = 1;
	if (!empty($row)) {
		$authnid = $row['authnid'];
		$challenge = $row['challenge'];
	} else {
		$authnid = hash('md5', "$userid".$host);
		$challenge = hash('sha224',"$userid");
	}		
	
	
	
	$extensions = array("uvi"=>true,"loc"=>true,"uvm"=>true);
	$rp = array("name"=>"WebAuthn KMJ", "id"=>$host);
	$user = array("displayName"=>$fullname,"id"=>$authnid,"name"=>$fullname);
	$pubKeyCredParams = array(array("type"=>"public-key","alg"=>-7),array("type"=>"public-key","alg"=>-257));
	$excludeCredentials = array(array("id"=>"None","type"=>"public-key","transports"=>array("internal")));
	$authenticatorSelection = array("authenticatorAttachment"=>"platform","userVerification"=>"required");
	$devdesc = $_REQUEST['devdesc'];
	$latitude = $_REQUEST['latitude'];
	$longitude = $_REQUEST['longitude'];
	$datereg = $_REQUEST['datereg'];
	$clientdesc = htmlspecialchars(strtoupper($_REQUEST['clientdesc']),ENT_QUOTES,"UTF-8");
	$command ="CALL insertwebauthn('$devdesc', '$clientdesc', '$authnid', '$challenge', $latitude, $longitude, '$datereg', $userid, '$token')";
	$row = getrow($command);
	$myobj = new stdClass();
	$myobj->idwebauthn = $row['@id'];
	$createArgs = array("publicKey"=>array("extensions"=>$extensions, "rp"=>$rp, "user"=>$user, "challenge"=>$challenge, "pubKeyCredParams"=>$pubKeyCredParams,"timeout"=>1800000,"attestation"=>"none","excludeCredentials"=>$excludeCredentials,"authenticatorSelection"=>$authenticatorSelection),"idwebauthn" => $row['@id']);

	
	header('Content-Type: application/json');
    print(json_encode($createArgs));
}
function savedev($userid){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$idwebauthn = $_REQUEST['idwebauthn'];
	$clientDataJSON = $_REQUEST['clientDataJSON'];
	
	$webauthnid = $_REQUEST['webauthnid'];
	$command = "CALL updatewebauthn($idwebauthn, '$clientDataJSON', '$webauthnid', $userid, '$tk')";
	getrow($command,false);
	
	$myobj->webauthnid = $webauthnid;
	$myobj->idwebauthn = $idwebauthn;
	echo json_encode($myobj);
}
function deletedev($userid){
	$token = $_REQUEST['tk'];
	//cleartkform($token, $userid);
	$idwebauthn = $_REQUEST['idwebauthn'];
	$command = "CALL deletewebauthn($idwebauthn, $userid, '$token')";
	getrow($command, false);
	$myobj = new stdClass();
	$myobj->idwebauthn = $idwebauthn;
	echo json_encode($myobj);

}

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
	die('Invalid User');
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
$token = $_REQUEST['tk'];

if ($trans=='getCreateArgs'){
	$tkform = $_REQUEST['tkform'];
	//die('$myobj: '.$myobj->tkform);
	//if ($tkform == $myobj->tkform){
		$fullname = $myobj->fullname;
		
		getCreateArgs($userid, $fullname);
	//}
}
if ($trans =='getdevs'){
		getdevs($userid);
}
if ($trans == 'savedev'){
		savedev($userid);
}
if ($trans == 'delete'){
		deletedev($userid);
}
if ($trans=='gentk'){
	$tkform = gentkform($token, $userid);
	echo json_encode($tkform);
}
if ($trans == 'verify'){
		verify($userid, $myobj->fullname);
}
if ($trans == 'verify2'){
		verify2($userid);
}
?>