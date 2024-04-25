<?php require "routines.php";

function searchPatient(){
	$myobj = new stdClass();
    $rafnum = htmlspecialchars(strtoupper(trim($_REQUEST['search_raf_num'])),ENT_QUOTES,"UTF-8");
    $patientid = htmlspecialchars(strtoupper(trim($_REQUEST['search_patient_id'])),ENT_QUOTES,"UTF-8");
	$lastname  = htmlspecialchars(strtoupper(trim($_REQUEST['search_lastname'])),ENT_QUOTES,"UTF-8");
	$firstname = htmlspecialchars(strtoupper(trim($_REQUEST['search_firstname'])),ENT_QUOTES,"UTF-8");
	$dob       = $_REQUEST['search_dob'];

    if(!empty($rafnum)){
        $find = "SELECT 
                    patient.*, 
                    distbrgy.brgyName, 
                    distbrgy.distName 
                FROM patient 
                LEFT JOIN distbrgy ON patient.brgyCode = distbrgy.brgyCode 
                LEFT JOIN assistdetail ON patient.idpatient = assistdetail.idpatient
                WHERE assistdetail.rafNum = '$rafnum'";
    }
    else {
        $find = "SELECT 
                    patient.*, 
                    distbrgy.brgyName, 
                    distbrgy.distName 
                FROM patient 
                LEFT JOIN distbrgy ON patient.brgyCode = distbrgy.brgyCode 
                WHERE ";
            
        $xwhere ='';

        if (!empty($patientid)) {
            $xwhere = "idpatient = $patientid";
        }
        else {
            if (!empty($lastname)) {
                $lastname = $lastname.'%';
                $xwhere = "benLName like '$lastname'";
            } 
            if (!empty($firstname)) {
                $firstname = $firstname.'%';
                $xwhere = ((empty($xwhere)) ? '' : $xwhere . ' AND ') . "benFName LIKE '$firstname'";
            }
            if (!empty($dob)) {
                $xwhere = ((empty($xwhere)) ? '' : $xwhere . ' AND ') . "benBDate = '$dob'";
            }
        } 

        $find .= $xwhere;
    }
	
	$command = $find;
	$myobj->data = gettable($command);
	echo json_encode($myobj);
}

function updatePatient($userid){
    try{
        $myobj = new stdClass();
        
        $tk         = $_REQUEST['tk'];
        $idpatient  = $_REQUEST['idpatient'];        
        $benBDate   = $_REQUEST['benBDate'];

        if (empty($benBDate)){ $benBDate2 = 'null'; } else { $benBDate2 = "'".$benBDate."'";}

        $benLName   = htmlspecialchars(strtoupper($_REQUEST['benLName']),ENT_QUOTES,"UTF-8");
        $benFName   = htmlspecialchars(strtoupper($_REQUEST['benFName']),ENT_QUOTES,"UTF-8");
        $benMName   = htmlspecialchars(strtoupper($_REQUEST['benMName']),ENT_QUOTES,"UTF-8");
        $benAddrSt  = htmlspecialchars(strtoupper($_REQUEST['benAddrSt']),ENT_QUOTES,"UTF-8");
        $benSex     = htmlspecialchars(strtoupper($_REQUEST['benSex']),ENT_QUOTES,"UTF-8");
        $benContact = htmlspecialchars(strtoupper($_REQUEST['benContact']),ENT_QUOTES,"UTF-8");
        $philsysid  = htmlspecialchars(strtoupper($_REQUEST['philsysid']),ENT_QUOTES,"UTF-8");
        $brgyCode   = $_REQUEST['brgyCode'];
        $benPHealth = htmlspecialchars(strtoupper($_REQUEST['benPHealth']),ENT_QUOTES,"UTF-8");
        $suffix     = htmlspecialchars(strtoupper($_REQUEST['suffix']),ENT_QUOTES,"UTF-8");

        $command = "SELECT * FROM patient WHERE idpatient = '$idpatient' limit 1";
        $patientrecord = getrow($command);
        
        if(!(isset($patientrecord['idpatient']) && $patientrecord['idpatient'] == $idpatient))
            throw new Exception('Invalid patient information.');

    
        $command = "CALL modifypatient($idpatient, '$benLName', '$benFName', '$benMName', '$benAddrSt', '$benSex', $benBDate2, '$benContact', '$benPHealth',  '$philsysid', $brgyCode, '$suffix', $userid, '$tk')";
        $row = getrow($command);
            
        if(!(isset($row['$idpatient']) && $row['$idpatient'] == $idpatient)){
            $command = "CALL inserthistory('$idpatient', 'PATIENT', 'PATIENTCORRECTION', '". str_replace("'", "\'", json_encode($patientrecord)) ."', '". str_replace("'", "\'", json_encode($_REQUEST)) ."', '$userid', '$tk')";
            $row = getrow($command);

            echo json_encode(['success' => true, 'message' => 'Patient updated.', 'data' => $row]);
        }
        else
            throw new Exception('Error while updating patient information.');

	} catch (Exception $e) {
		echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => json_encode($_REQUEST)]);
	}
}

function validatePatient(){
	$myobj = new stdClass();
	$lastname    = htmlspecialchars(strtoupper(trim($_REQUEST['lastname'])),ENT_QUOTES,"UTF-8");
	$firstname   = htmlspecialchars(strtoupper(trim($_REQUEST['firstname'])),ENT_QUOTES,"UTF-8");
	$middlename  = htmlspecialchars(strtoupper(trim($_REQUEST['middlename'])),ENT_QUOTES,"UTF-8");
	$dob         = $_REQUEST['birthday'];

    $find = "SELECT 
                patient.*, 
                distbrgy.brgyName, 
                distbrgy.distName 
            FROM patient 
            LEFT JOIN distbrgy ON patient.brgyCode = distbrgy.brgyCode 
            WHERE benLName = '$lastname' AND benFName = '$firstname' AND benBDate = '$dob'";
            
    $xwhere ='';

    if (!empty($middlename)) {
        $xwhere = " AND benMName = '$middlename'";
    }

    $find .= $xwhere . ' LIMIT 1';
	
	$command = $find;
	$myobj->data = getrow($command);
	echo json_encode($myobj);
}

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User"));
	die('system error');
} else {$userid = $myobj->userid;}

$trans = $_REQUEST['trans'];

if ($trans=='SEARCHPATIENT'){
	searchPatient();
}

if ($trans=='UPDATEPATIENTINFORMATION'){
	updatePatient($userid);
}

if ($trans=='VALIDATEPATIENT'){
	validatePatient();
}