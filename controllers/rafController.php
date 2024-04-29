<?php
require "routines.php";
function getbrgys(){
	$myobj = new stdClass();
	$command = "select * from distbrgy order by brgyName";
	$myobj->brgys = gettable($command);
	echo json_encode($myobj);
	
}
function getsworkers($userid){
	$myobj = new stdClass();
	$command = "select * from roles where name='SOCIAL WORKER' and active ='Y' limit 1";
	$row = getrowRead($command);
	$idroles = $row['idroles'];
	
	$command = "select office from `users` where userid=$userid and active ='Y' limit 1";
	$row = getrowRead($command);
	$office = $row['office'];
	
	$command = "select users.fullname, users.userid from users left join  rolesusers on users.userid = rolesusers.idusers where rolesusers.idroles = $idroles and users.active ='Y' and users.office = '$office' order by fullname";
	$myobj->sworkers = gettable($command);
	echo json_encode($myobj);
	
}
function getproviders(){
	$myobj = new stdClass();
	$command = "SELECT * FROM office where officeType='PROVIDER' and provCat ='GOVERNMENT' and active ='Y';";
	$myobj->providers = gettable($command);
	echo json_encode($myobj);
	
}
function getmeds(){
	$myobj = new stdClass();
	$command = "SELECT * FROM pcom_pharmacy WHERE active ='Y';";
	$myobj->providers = gettable($command);
	echo json_encode($myobj);
	
}
function getPproviders(){
	$myobj = new stdClass();
	$command = "SELECT * FROM office where officeType='PROVIDER' and provCat ='PRIVATE' and active ='Y' ORDER BY officename;";
	$myobj->providers = gettable($command);
	echo json_encode($myobj);
	
}
function getprovidersAll(){
	$myobj = new stdClass();
	$command = "SELECT * FROM office where officeType='PROVIDER' and active ='Y';";
	$myobj->providers = gettable($command);
	echo json_encode($myobj);
}
function getprovidersAllv2(){
	$myobj = new stdClass();
	$providers = [];
	$command = "SELECT * FROM office where officeType='PROVIDER' and provCat ='PRIVATE' and active ='Y';";
	$providers['PRIVATE'] = gettable($command);
	$command = "SELECT * FROM office where officeType='PROVIDER' and provCat ='GOVERNMENT' and active ='Y';";
	$providers['GOVERNMENT'] = gettable($command);
	
	echo json_encode($myobj->providers = $providers);
	
}
function getassistcode(){
	$myobj = new stdClass();
	$officecode = $_REQUEST['officecode'];
	$command ="SELECT provassist.*, assistsched.assistDesc, assistsched.rateCode, assistsched.idassistsched FROM provassist left join assistsched on provassist.assistCode = assistsched.assistCode where officecode='$officecode' and provassist.active ='Y' and assistsched.assistStatus ='Y' order by provassist.assistCode";
	$myobj->assistCode = gettable($command);
	echo json_encode($myobj);
	
}
function loadpatient(){
	$myobj = new stdClass();
	$myobj->error = false;
	$idpatient = $_REQUEST['idpatient'];
	$command = "SELECT 
					p.*,
    				EXISTS (SELECT 1 FROM assistdetail AS ad WHERE p.idpatient = ad.idpatient AND ad.assistCode LIKE '%FUNERAL%' AND ad.status = 'APPROVED') AS deceased
				FROM patient AS p 
				WHERE p.idpatient = $idpatient";
	// $command = "select * from patient where idpatient = $idpatient";
	$row = getrow($command);
	if (empty($row)){ $myobj->error = true;} else { $myobj->patient = $row;}
	echo json_encode($myobj);
}

function loadintake(){
	$myobj = new stdClass();
	$myobj->error = false;
	$idpatient = $_REQUEST['idpatient'];
	$command = "select * from intake where idpatient = $idpatient order by idintake desc limit 1";
	$row = getrow($command);
	if (empty($row)){ $myobj->error = true;} else { $myobj->intake = $row;}
	echo json_encode($myobj);
}
function getrate(){
	$myobj = new stdClass();
	$billAmount = $_REQUEST['billAmount'];
	$rateCode = $_REQUEST['rateCode'];
	$command ="SELECT * FROM assistrate where rateCode ='$rateCode' and $billAmount >= baseFrom  and $billAmount <= baseTo";
	$row = getrow($command);
	$myobj->amtApproved = 0;
	if (empty($row)){
		$command ="SELECT * FROM assistrate where rateCode ='$rateCode' and baseFrom is null  and baseTo is null";
		$row = getrow($command);
	}
	if (!empty($row)){
		if ($billAmount < $row['assistAmount']) {
			$assistAmount = $billAmount;
		} else {
			$assistAmount = $row['assistAmount'];
		}
		$myobj->amtApproved = $assistAmount;
	}
	echo json_encode($myobj);
}
function adddetails($userid, $fullname){
	$myobj = new stdClass();
	$rafNum = htmlspecialchars(strtoupper($_REQUEST['rafNum']),ENT_QUOTES,"UTF-8");
	$billAmount = $_REQUEST['billAmount'];
	$amtApproved = $_REQUEST['amtApproved'];
	$noteTag = htmlspecialchars($_REQUEST['noteTag'],ENT_QUOTES,"UTF-8");
	$provCode = $_REQUEST['provCode'];
	$remTag = $_REQUEST['remTag'];
	$idpatient = $_REQUEST['idpatient'];
	$idassistsched = $_REQUEST['idassistsched'];
	$tk = $_REQUEST['tk'];
	
	$command = "SELECT count(rafNum) counter FROM assistdetail WHERE rafNum = '$rafNum'";
	$row = getrow($command);
	if ($row['counter'] > 0) {
		$myobj->error = 'RAF Number already exists!';
		echo json_encode($myobj);
		return;
	}
	
	$command = "CALL insertlogging('$rafNum', 'insert', '". str_replace("'", "\'", json_encode($_REQUEST)) ."', '$tk')";
	$row = getrow($command);
	
	$command = "SELECT idoffice FROM users left join office on users.office = office.officecode where userid = $userid";
	$row = getrow($command);
	$procloc = $row['idoffice'];
	$idpatient = $_REQUEST['idpatient'];
	$benBDate = $_REQUEST['benBDate'];
	if (empty($benBDate)){ $benBDate2 = 'null'; } else { $benBDate2 = "'".$benBDate."'";}
	$benLName = htmlspecialchars(strtoupper($_REQUEST['benLName']),ENT_QUOTES,"UTF-8");
	$benFName = htmlspecialchars(strtoupper($_REQUEST['benFName']),ENT_QUOTES,"UTF-8");
	$benMName = htmlspecialchars(strtoupper($_REQUEST['benMName']),ENT_QUOTES,"UTF-8");
	$benAddrSt = htmlspecialchars(strtoupper($_REQUEST['benAddrSt']),ENT_QUOTES,"UTF-8");
	$benSex = htmlspecialchars(strtoupper($_REQUEST['benSex']),ENT_QUOTES,"UTF-8");
	$benContact = htmlspecialchars(strtoupper($_REQUEST['benContact']),ENT_QUOTES,"UTF-8");
	$assistCode = $_REQUEST['assistCode'];
	$philsysid = htmlspecialchars(strtoupper($_REQUEST['philsysid']),ENT_QUOTES,"UTF-8");
	$citizenid = strtoupper($_REQUEST['citizenid']);
	$brgyCode = $_REQUEST['brgyCode'];
	$benPHealth = htmlspecialchars(strtoupper($_REQUEST['benPHealth']),ENT_QUOTES,"UTF-8");
	$suffix = htmlspecialchars(strtoupper($_REQUEST['suffix']),ENT_QUOTES,"UTF-8");
	$timeConsume = htmlspecialchars(strtoupper($_REQUEST['timeConsume']),ENT_QUOTES,"UTF-8");
	$typeClient = htmlspecialchars(strtoupper($_REQUEST['typeClient']),ENT_QUOTES,"UTF-8");

	$effectivitydate = $_REQUEST['effectivitydate'];
	if (empty($idpatient)){ $idpatient = -1;}
	if ($idpatient == -1){
		$command ="CALL insertpatient('$benLName', '$benFName', '$benMName', '$benAddrSt', '$benSex', $benBDate2, '$benContact', '$benPHealth',  '$philsysid',  '$citizenid', $brgyCode, '$suffix', $userid, '$tk')";
		$row = getrow($command);
		$idpatient = $row['@id'];
	} else {
		$command ="CALL updatepatient($idpatient, '$benLName', '$benFName', '$benMName', '$benAddrSt', '$benSex', $benBDate2, '$benContact', '$benPHealth',  '$philsysid',  '$citizenid', $brgyCode, '$suffix', $userid, '$tk')";
		
		$row = getrow($command);
	}
	$command = "CALL insertassistdetail('$rafNum', $billAmount, $amtApproved, '$noteTag', '$provCode', '$remTag',  $idpatient, $procloc, $idassistsched,'$assistCode', $timeConsume, $userid, '$tk', '$typeClient')";
	
	$row = getrow($command);
	$idassistdetails = $row['@id'];
	
	
	$remarks = htmlspecialchars($_REQUEST['remarks'],ENT_QUOTES,"UTF-8");
	$requestor = htmlspecialchars(strtoupper($_REQUEST['requestor']),ENT_QUOTES,"UTF-8");
	$reqAddr = htmlspecialchars(strtoupper($_REQUEST['reqAddr']),ENT_QUOTES,"UTF-8");
	$relation = htmlspecialchars(strtoupper($_REQUEST['relation']),ENT_QUOTES,"UTF-8");
	$sworker = htmlspecialchars(strtoupper($_REQUEST['sworker']),ENT_QUOTES,"UTF-8");
	$depName = $_REQUEST['depName'];
	$depRelation = $_REQUEST['depRelation'];
	$depAge = $_REQUEST['depAge'];
	$dependents_json = new stdClass();
	$adependents = array();
	$i = 1;
	while($i < count($depName))
	{
		$dependents_json->depName = htmlspecialchars(strtoupper($depName[$i]),ENT_QUOTES,"UTF-8");
		$dependents_json->depRelation = htmlspecialchars(strtoupper($depRelation[$i]),ENT_QUOTES,"UTF-8");
		$dependents_json->depAge = $depAge[$i];
		$i++;
		$adependents[] = $dependents_json;
		$dependents_json = new stdClass();
		
	}
	$str_details = json_encode($adependents);
	//if ($idintake == -1) {
		$command ="CALL insertintake('$remarks', '$sworker', '$requestor', '$relation', $idpatient, '$str_details', '$reqAddr', $idassistdetails, $userid, '$tk')";
	//} else {
	//	$command ="CALL updateintake($idintake, '$remarks', '$sworker', '$requestor', '$relation', '$details', '$reqAddr', $userid, '$tk')";

	//}
	$tag = $_REQUEST['tag'];
	
	$row = getrow($command);
	$idintake = $row['@id'];
	
	$command = "SELECT assistCode FROM assistsched where idassistsched = $idassistsched";
	$row = getrow($command);
	$medIds = [];
	if (isset($_REQUEST['medProv']) && !empty($_REQUEST['medProv']) && $row['assistCode'] == 'MEDICINE') {
		$medProv = $_REQUEST['medProv'];
		$medAmount = $_REQUEST['medAmount'];
			for ($i = 1; $i < count($medProv); $i++) {
				$pharmaID = $medProv[$i];
				$xmedAmount = $medAmount[$i];
				$command ="CALL insertpcom_details($idassistdetails, $idpatient, $pharmaID, $xmedAmount, $userid, '$tk')";
				$row = getrow($command);
				$medIds[] = $row['@id'];
			}
	}
	
	$myobj->idintake = $idintake;
	$myobj->idassistdetails = $idassistdetails;
	$myobj->idpatient = $idpatient;
	$myobj->tag = $tag;
	$myobj->medIds = $medIds;
	echo json_encode($myobj);
	
}

function updatedetails($userid, $fullname){
	$myobj = new stdClass();
	$idassistdetails = $_REQUEST['idassistdetails'];
	$rafNum = htmlspecialchars(strtoupper($_REQUEST['rafNum']),ENT_QUOTES,"UTF-8");
	$billAmount = $_REQUEST['billAmount'];
	$amtApproved = $_REQUEST['amtApproved'];
	$noteTag = htmlspecialchars($_REQUEST['noteTag'],ENT_QUOTES,"UTF-8");
	$provCode = $_REQUEST['provCode'];
	$remTag = $_REQUEST['remTag'];
	$idpatient = $_REQUEST['idpatient'];
	$idassistsched = $_REQUEST['idassistsched'];
	$tk = $_REQUEST['tk'];
	
	$command = "SELECT idassistdetails FROM assistdetail WHERE rafNum = '$rafNum' limit 1";
	$row = getrow($command);
	if (!empty($row) && $row['idassistdetails'] != $idassistdetails) { /* this makes the rafNum editable but on the condition that it doesn't exist */
		$myobj->error = 'RAF Number already exists!';
		echo json_encode($myobj);
		return;
	}
	
	$command = "CALL insertlogging('$rafNum', 'update', '". str_replace("'", "\'", json_encode($_REQUEST)) ."', '$tk')";
	$row = getrow($command);
	
	$command = "SELECT idoffice FROM users left join office on users.office = office.officecode where userid = $userid";
	$row = getrow($command);
	$procloc = $row['idoffice'];
	$idpatient = $_REQUEST['idpatient'];
	$benBDate = $_REQUEST['benBDate'];
	if (empty($benBDate)){ $benBDate2 = 'null'; } else { $benBDate2 = "'".$benBDate."'";}
	$benLName = htmlspecialchars(strtoupper($_REQUEST['benLName']),ENT_QUOTES,"UTF-8");
	$benFName = htmlspecialchars(strtoupper($_REQUEST['benFName']),ENT_QUOTES,"UTF-8");
	$benMName = htmlspecialchars(strtoupper($_REQUEST['benMName']),ENT_QUOTES,"UTF-8");
	$benAddrSt = htmlspecialchars(strtoupper($_REQUEST['benAddrSt']),ENT_QUOTES,"UTF-8");
	$benSex = htmlspecialchars(strtoupper($_REQUEST['benSex']),ENT_QUOTES,"UTF-8");
	$benContact = htmlspecialchars(strtoupper($_REQUEST['benContact']),ENT_QUOTES,"UTF-8");
	
	$philsysid = htmlspecialchars(strtoupper($_REQUEST['philsysid']),ENT_QUOTES,"UTF-8");
	$citizenid = strtoupper($_REQUEST['citizenid']);
	$brgyCode = $_REQUEST['brgyCode'];
	$benPHealth = htmlspecialchars(strtoupper($_REQUEST['benPHealth']),ENT_QUOTES,"UTF-8");
	$suffix = htmlspecialchars(strtoupper($_REQUEST['suffix']),ENT_QUOTES,"UTF-8");
	$typeClient = htmlspecialchars(strtoupper($_REQUEST['typeClient']),ENT_QUOTES,"UTF-8");

	if (empty($idpatient)){ $idpatient = -1;}
	if ($idpatient == -1){
		$command ="CALL insertpatient('$benLName', '$benFName', '$benMName', '$benAddrSt', '$benSex', $benBDate2, '$benContact', '$benPHealth',  '$philsysid',  '$citizenid', $brgyCode, '$suffix', $userid, '$tk')";
		$row = getrow($command);
		$idpatient = $row['@id'];
	} else {
		$command ="CALL updatepatient($idpatient, '$benLName', '$benFName', '$benMName', '$benAddrSt', '$benSex', $benBDate2, '$benContact', '$benPHealth',  '$philsysid',  '$citizenid', $brgyCode, '$suffix', $userid, '$tk')";
		
		$row = getrow($command);
	}
	$command = "CALL updateassistdetail($idassistdetails, '$rafNum', $billAmount, $amtApproved, '$noteTag', '$provCode', '$remTag',  $idpatient, $procloc, $idassistsched, $userid, '$tk', '$typeClient')";
	
	$row = getrow($command);
		
	$remarks = htmlspecialchars($_REQUEST['remarks'],ENT_QUOTES,"UTF-8");
	$requestor = htmlspecialchars(strtoupper($_REQUEST['requestor']),ENT_QUOTES,"UTF-8");
	$reqAddr = htmlspecialchars(strtoupper($_REQUEST['reqAddr']),ENT_QUOTES,"UTF-8");
	$relation = htmlspecialchars(strtoupper($_REQUEST['relation']),ENT_QUOTES,"UTF-8");
	$sworker = htmlspecialchars(strtoupper($_REQUEST['sworker']),ENT_QUOTES,"UTF-8");
	$depName = $_REQUEST['depName'];
	$depRelation = $_REQUEST['depRelation'];
	$depAge = $_REQUEST['depAge'];
	$dependents_json = new stdClass();
	$adependents = array();
	$i = 1;
	while($i < count($depName))
	{
		$dependents_json->depName = htmlspecialchars(strtoupper($depName[$i]),ENT_QUOTES,"UTF-8");
		$dependents_json->depRelation = htmlspecialchars(strtoupper($depRelation[$i]),ENT_QUOTES,"UTF-8");
		$dependents_json->depAge = $depAge[$i];
		$i++;
		$adependents[] = $dependents_json;
		$dependents_json = new stdClass();
		
	}
	$str_details = json_encode($adependents);
	$idintake = $_REQUEST['idintake'];
	$command ="CALL updateintake($idintake, '$remarks', '$sworker', '$requestor', '$relation', $idpatient, '$str_details', '$reqAddr', $userid, '$tk')";
	$tag = $_REQUEST['tag'];
	$row = getrow($command);
	
	$command = "SELECT assistCode FROM assistsched where idassistsched = $idassistsched";
	$row = getrow($command);
	$medIds = [];
	if (isset($_REQUEST['medProv']) && !empty($_REQUEST['medProv']) && $row['assistCode'] == 'MEDICINE') {
		$medProv = $_REQUEST['medProv'];
		$medAmount = $_REQUEST['medAmount'];
		$medProvId = $_REQUEST['medProvId'];
		for ($i = 1; $i < count($medProv); $i++) {
			$pharmaID = $medProv[$i];
			$xmedAmount = $medAmount[$i];
			$pharmaOld = (!empty($medProvId[$i])) ? $medProvId[$i] : 0;
			if ($pharmaOld == 0) {
				$command ="CALL insertpcom_details($idassistdetails, $idpatient, $pharmaID, $xmedAmount, $userid, '$tk')";
			} else {
				$command ="CALL updatepcom_details2($idassistdetails, $idpatient, $pharmaOld, $pharmaID, $xmedAmount, $userid, '$tk')";
			}
			$row = getrow($command);
			$medIds[] = $row['@id'];
		}
	}
	
	$myobj->idintake = $idintake;
	$myobj->idassistdetails = $idassistdetails;
	$myobj->idpatient = $idpatient;
	$myobj->tag = $tag;
	$myobj->medIds = $medIds;
	echo json_encode($myobj);
	
}

function approvedetails($userid){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$idassistdetails = $_REQUEST['idassistdetails'];
	$amtApproved = $_REQUEST['amtApproved'];
	$rafNum = htmlspecialchars(strtoupper($_REQUEST['rafNum']),ENT_QUOTES,"UTF-8"); 
	
	$command = "CALL insertlogging('$rafNum', 'approved', '". str_replace("'", "\'", json_encode($_REQUEST)) ."', '$tk')";
	$row = getrow($command);
	
	$command ="CALL approveRAF($idassistdetails, $amtApproved, '$rafNum', $userid, '$tk')";
	$row = getrow($command);
	$myobj->id = $row['@id'];
	$myobj->balAmount = $row['@balAmount'];
	$myobj->balCritLevel = $row['@balCritLevel'];
	$myobj->idassistdetails = $idassistdetails;
	echo json_encode($myobj);
}
function overridedetails($userid){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$idassistdetails = $_REQUEST['idassistdetails'];
	$command ="CALL foroverride($idassistdetails, $userid, '$tk')";
	$row = getrow($command);
	$myobj->id = $row['@id'];
	
	$myobj->idassistdetails = $idassistdetails;
	echo json_encode($myobj);
}
function gettemplates(){
	$myobj = new stdClass();
	$command = "select * from templates where active = 'Y'";
	$myobj->templates = gettable($command);
	echo json_encode($myobj);
}
function validateRafnum(){
	$myobj = new stdClass();
	$rafNum = $_REQUEST['rafnum'];
	$command = "select rafNum from assistdetail where rafNum = '$rafNum'";
	$row = getrow($command);
	$myobj->valid = (!empty($row)) ? false : true;
	echo json_encode($myobj);
}
function reissue($userid){
	$myobj = new stdClass();
	$tk = $_REQUEST['tk'];
	$idassistdetails = $_REQUEST['idassistdetails'];
	$command ="CALL reissue($idassistdetails, $userid, '$tk')";
	$row = getrow($command);
	$myobj->id = $row['@id'];
	$myobj->idassistdetails = $idassistdetails;
	$myobj->msg = 'Reissue Succesful!';
	echo json_encode($myobj);
}
$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User")); die('Invalid User');
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
if ($trans=='getbrgys'){
	getbrgys();
}
if ($trans=='getsworkers'){
	getsworkers($userid);
}
if ($trans=='getproviders'){
	getproviders();
}
if ($trans=='getmeds'){
    getmeds();
}
if ($trans=='getPproviders'){
	getPproviders();
}
if ($trans=='getprovidersAll'){
	getprovidersAll();
}
if ($trans=='getprovidersAllv2'){
	getprovidersAllv2();
}
if ($trans=='getassistcode'){
	getassistcode();
}
if ($trans=='getrate'){
	getrate();
}
if ($trans=='loadpatient'){
	loadpatient();
}
if ($trans=='loadintake'){
	loadintake();
}
if ($trans=='ADD'){
	adddetails($userid, $myobj->fullname);
}
if ($trans=='UPDATE'){
	updatedetails($userid, $myobj->fullname);
}
if ($trans=='approve'){
	approvedetails($userid);
}
if ($trans=='override'){
	overridedetails($userid);
}
if ($trans=='gettemplates'){
	gettemplates();
}
if ($trans=='validateRafnum'){
	validateRafnum();
}
if ($trans=='reissue'){
	reissue($userid);
}