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
	$provType='';
	if (!empty($_REQUEST['provType'])) { $provType = $_REQUEST['provType'];}
	
	$tk = $_REQUEST['tk'];
	$command ="CALL insertoffice('$officecode','$officename', '$location', '$contactperson', '$contactno', '$emailaddress', '$active', '$officeType', '$provType', $userid, '$tk')";
	
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
	$provType='';
	if (!empty($_REQUEST['provType'])) { $provType = $_REQUEST['provType'];}
	$command ="CALL updateoffice($idoffice, '$officecode', '$officename', '$location', '$contactperson', '$emailaddress', '$active', '$contactno', '$officeType', '$provType', $userid, '$tk')";

	getrow($command,false);
	$myobj->idoffice = $idoffice;
	echo json_encode($myobj);

}
function searchdetails(){
	
	$myobj = new stdClass();
	$citizenid = trim($_REQUEST['citizenid']);
	$lastname = htmlspecialchars(strtoupper(trim($_REQUEST['lastname'])),ENT_QUOTES,"UTF-8");
	$firstname = htmlspecialchars(strtoupper(trim($_REQUEST['firstname'])),ENT_QUOTES,"UTF-8");
	$birthdate = $_REQUEST['birthdate'];
	$find = "select patient.*, distbrgy.brgyName, distbrgy.distName from patient left join distbrgy on patient.brgyCode = distbrgy.brgyCode where ";
	$xwhere ='';
	if (!empty($lastname)) {
		$lastname = $lastname.'%';
		$xwhere = "benLName like '$lastname'";
	} 
	if (!empty($citizenid)) {
		$xwhere = "citizenid = '$citizenid'";
	} 
	if (!empty($firstname)) {
		$firstname = $firstname.'%';
		if (empty($xwhere)) {
			$xwhere = "benFName like '$firstname'";
		} else {
			$xwhere = $xwhere." and benFName like '$firstname'";
		}
	}
	if (!empty($birthdate)) {
		if (empty($xwhere)) {
			$xwhere = "benBDate = '$birthdate'";
		} else {
			$xwhere =$xwhere." and benBDate = '$birthdate'";
		}
	}
	
	$command = $find.$xwhere;
	
	$myobj->data = gettable($command);
	
	echo json_encode($myobj);
}
function getdetails(){
	$myobj = new stdClass();
	$idpatient = $_REQUEST['idpatient'];
	$command ="select rafnum, provCode, amtApproved, dateApproved, tagCan,  noteTag, officename, assistCode, status, intake.requestor, intake.remarks, DATE_FORMAT(dateApproved ,' %b %d, %Y') dateds, DATE(dateApproved) datedss from assistdetail 
				left join office on assistdetail.provCode = office.officecode 
				left join intake on assistdetail.idassistdetails = intake.idassistdetails 
				where assistdetail.idpatient = $idpatient and assistdetail.status ='APPROVED' 
				order by assistdetail.dateApproved DESC" ;
	$myobj->data = gettable($command);
	
	echo json_encode($myobj);
}
function getassistperiod(){
	$myobj = new stdClass();
	$idpatient = $_REQUEST['idpatient'];
	$command ="select sum(amtApproved) as total from assistperiod where days < 30 and idpatient = $idpatient limit 1";
	$row  = getrowRead($command);
	if (empty($row)){
		$myobj->total = 0;
	} else {
		$myobj->total = $row['total'];
	}
	if ($row['total'] == null)
	{ $myobj->total = 0;}
	$myobj->idpatient = $idpatient;	
	
	echo json_encode($myobj);
}
$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User"));
	die('system error');
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
if ($trans=='search'){
	searchdetails();
}
if ($trans=='getassistperiod'){
	getassistperiod();
}
?>