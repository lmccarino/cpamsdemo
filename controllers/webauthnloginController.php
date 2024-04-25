<?php
require "routines.php";



function verify2(){
	$myobj = new stdClass();
	$myobj->error = false;
	$webauthnid = $_REQUEST['webauthnid'];
	$clientDataJSON = $_REQUEST['clientDataJSON'];
	$command = "select * from webauthn where webauthnid = '$webauthnid' and clientDataJSON = '$clientDataJSON' limit 1";
	$row = getrow($command);
	if (empty($row)) {
		$myobj->error = true;
	} else {
		$myobj->error = false;
		$myobj->userid = $row['userid'];
		$myobj->webauthnid = $webauthnid;
	}
	echo json_encode($myobj);
	
	
}
function getCreateArgs(){
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
         $url = "https://";   
    else  
         $url = "http://";   

	$url = $url. $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
	$parse = parse_url($url);
	$host = $parse['host'];
	$webauthnid = $_REQUEST['webauthnid'];
	$command = "select webauthn.authnid, webauthn.webauthnid, webauthn.challenge, users.fullname from webauthn left join users on webauthn.userid = users.userid where webauthnid = '$webauthnid' limit 1";
	
	$row = getrow($command);
	if (!empty($row)) {
		$authnid = $row['authnid'];
		$challenge = $row['challenge'];
		$fullname = $row['fullname'];
		$error = false;
	} else {
		$authnid = '';
		$challenge = '';
		$error = true;
		$fullname ='';
	}	
	$extensions = array("uvi"=>true,"loc"=>true,"uvm"=>true);
	$rp = array("name"=>"WebAuthn MAHUGANI", "id"=>$host);
	$user = array("displayName"=>$fullname,"id"=>$authnid,"name"=>$fullname);
	$pubKeyCredParams = array(array("type"=>"public-key","alg"=>-7),array("type"=>"public-key","alg"=>-257));
	$excludeCredentials = array(array("id"=>"None","type"=>"public-key","transports"=>array("internal")));
	$authenticatorSelection = array("authenticatorAttachment"=>"platform","userVerification"=>"required");
	$createArgs = array("publicKey"=>array("extensions"=>$extensions, "rp"=>$rp, "user"=>$user, "challenge"=>$challenge, "pubKeyCredParams"=>$pubKeyCredParams,"timeout"=>1800000,"attestation"=>"none","excludeCredentials"=>$excludeCredentials,"authenticatorSelection"=>$authenticatorSelection),"error" => $error);

	
	header('Content-Type: application/json');
    print(json_encode($createArgs));
	//echo json_encode($createArgs);
	}



$trans = $_REQUEST['trans'];


if ($trans=='getCreateArgs'){
	
		
		getCreateArgs();
	
}

if ($trans == 'verify2'){
		verify2();
}
?>