<?php
require "routines.php";

function updatedetails($userid) {
    try{
	    $rafNum = $_REQUEST['raf'] = $_REQUEST['rafNum'];
        $idassistdetails = $_REQUEST['idassistdetails'];
        $idpatient       = $_REQUEST['idpatient'];
        $tk              = $_REQUEST['tk'];
        
        $command = "SELECT userID FROM `assistdetail` where idassistdetails = ". $_REQUEST['idassistdetails'];
        $row = getrow($command);

        $encoderUser = $_REQUEST['userID'] = $row['userID'];

        // UPDATE INTAKE
        $idintake    = $_REQUEST['idintake'];
        $requestor   = htmlspecialchars(strtoupper(trim($_REQUEST['requestor'])),ENT_QUOTES,"UTF-8");
        $reqAddr     = htmlspecialchars(strtoupper(trim($_REQUEST['reqAddr'])),ENT_QUOTES,"UTF-8");
        $relation    = htmlspecialchars(strtoupper(trim($_REQUEST['relation'])),ENT_QUOTES,"UTF-8");
        $sworker     = htmlspecialchars(strtoupper(trim($_REQUEST['sworker'])),ENT_QUOTES,"UTF-8");
        $depName     = $_REQUEST['depName'];
        $depRelation = $_REQUEST['depRelation'];
        $depAge      = $_REQUEST['depAge'];
        $adependents = array();
    
        for ($i = 1; $i < count($depName); $i++) {
            $adependents[] = [
                'depName'     => htmlspecialchars(strtoupper(trim($depName[$i])),ENT_QUOTES,"UTF-8"),
                'depRelation' => htmlspecialchars(strtoupper(trim($depRelation[$i])),ENT_QUOTES,"UTF-8"),
                'depAge'      => trim($depAge[$i])
            ];
        }
    
        $remarks     = htmlspecialchars($_REQUEST['remarks'],ENT_QUOTES,"UTF-8");
        $str_details = json_encode($adependents);

        $command = "SELECT * FROM intake WHERE idintake = '$idintake' limit 1";
        $intakerecord = getrow($command);

        $command ="CALL updateintake($idintake, '$remarks', '$sworker', '$requestor', '$relation', $idpatient, '$str_details', '$reqAddr', $encoderUser, '$tk')";
        $row = getrow($command);

        if(isset($intakerecord['details']) && $intakerecord['details'] != '')
            $intakerecord['details'] = json_decode($intakerecord['details']);

        $intakeentry = [
            'trans'     => 'UPDATEINTAKE',
            'remarks'   => $remarks,
            'sworker'   => $sworker,
            'requestor' => $requestor,
            'relation'  => $relation,
            'idpatient' => $idpatient,
            'details'   => json_decode($str_details),
            'reqAddr'   => $reqAddr,
            'userid'    => $userid
        ];

        if((isset($row['@id']) && $row['@id'] == $idintake)){
            $command = "CALL inserthistory('$idassistdetails', 'ASSISTDETAIL', 'RAFCORRECTION', '". str_replace("'", "\'", json_encode($intakerecord)) ."', '". str_replace("'", "\'", json_encode($intakeentry)) ."', '$userid', '$tk')";
            $row = getrow($command);
        }
        // END OF UPDATE INTAKE
        
        // UPDATE ASSISTDETAILS
        $billAmount    = $_REQUEST['billAmount'];
        $amtApproved   = $_REQUEST['amtApproved'];
        $noteTag       = $_REQUEST['noteTag'];
        $provCode      = $_REQUEST['provCode'];
        $remTag        = $_REQUEST['remTag'];
        $idassistsched = $_REQUEST['idassistsched'];
        $typeClient = $_REQUEST['typeClient'];


        $command = "SELECT * FROM assistdetail WHERE idassistdetails = '$idassistdetails' limit 1";
        $rafrecord = getrow($command);
        
        $command = "CALL modifyassisdetails($idassistdetails, $billAmount, $amtApproved, '$noteTag', '$provCode', '$remTag',  $idassistsched, $userid, '$tk', '$typeClient')";
        $row = getrow($command);

        $rafentry = [
            'trans'             => 'UPDATEASSISTDETAILINFORMATION',
            'idassistdetails'   => $idassistdetails,
            'billAmount'        => $billAmount,
            'amtApproved'       => $amtApproved,
            'noteTag'           => $noteTag,
            'provCode'          => $provCode,
            'remTag'            => $remTag,
            'idassistsched'     => $idassistsched,
            'userid'            => $userid,
            'tk'                => $tk,
            'typeClient'        => $typeClient
        ];

        if((isset($row['id']) && $row['id'] == $idassistdetails)){
            $command = "CALL inserthistory('$idassistdetails', 'ASSISTDETAIL', 'RAFCORRECTION', '". str_replace("'", "\'", json_encode($rafrecord)) ."', '". str_replace("'", "\'", json_encode($rafentry)) ."', '$userid', '$tk')";
            $row = getrow($command);
        }

        // END OF UPDATE ASSISTDETAILS
        
        $command = "SELECT assistCode FROM assistsched WHERE idassistsched = $idassistsched";
        $row = getrow($command);
        
        // UPDATE PCOM
        if (isset($_REQUEST['medProv']) && !empty($_REQUEST['medProv']) && $row['assistCode'] == 'MEDICINE') {
            $medProvOld = $_REQUEST['medProvOld'];
            $medProv    = $_REQUEST['medProv'];
            $medAmount  = $_REQUEST['medAmount'];

            for ($i = 1; $i < count($medProv); $i++)
                if (!empty($medAmount[$i]) && is_numeric($medAmount[$i])) {
                    $pharmaOld = (!empty($medProvOld[$i])) ? $medProvOld[$i] : 0;
                    $pharmaID = $medProv[$i];
                    $xmedAmount = $medAmount[$i];

                    $pcomrecord = new stdClass;
                    if($pharmaOld > 0){
                        $command = "SELECT * FROM pcom_details WHERE pcom_detailsid = '$pharmaOld' limit 1";
                        $pcomrecord = getrow($command);   
                    }

                    $transactiontitle = ($pharmaOld > 0) ? "UPDATEPCOM" : "NEWPCOM";

                    $pcomentry = [
                        'trans'            => 'UPDATEPCOMINFORMATION',
                        'idassistdetails'  => $idassistdetails,
                        'idpatient'        => $idpatient,
                        'pcom_detailsid'   => $pharmaOld,
                        'pcom_pharmacyid'  => $pharmaID,
                        'amount'           => $xmedAmount,
                        'userid'           => $encoderUser,
                        'tk'               => $tk,
                    ];

                    $command ="CALL updatepcom_details2($idassistdetails, $idpatient, $pharmaOld, $pharmaID, $xmedAmount, $encoderUser, '$tk')";
                    $row = getrow($command);
                    
                    if((isset($row['@id']) && is_numeric($row['@id']) && $row['@id'] > 0)){
                        $command = "CALL inserthistory('$idassistdetails', 'ASSISTDETAIL', '$transactiontitle', '". str_replace("'", "\'", json_encode($pcomrecord)) ."', '". str_replace("'", "\'", json_encode($pcomentry)) ."', '$userid', '$tk')";
                        $row = getrow($command);
                    }
                }
        }
        // END OF UPDATE PCOM
        
        echo json_encode(['success' => true, 'message' => 'RAF updated.', 'data' => getdetails()]);
	} catch (Exception $e) {
		echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => json_encode($_REQUEST)]);
	}
}

function removemed($userid) {
	$pcom_detailsid = $_REQUEST['pcom_detailsid'];
	$tk = $_REQUEST['tk'];
	
    $command = "SELECT * FROM pcom_details WHERE pcom_detailsid = '$pcom_detailsid' limit 1";
    $pcomrecord = getrow($command);   

	$command ="CALL deletepcom_details($pcom_detailsid, $userid, '$tk')";
	$row = getrow($command);

    $idassistdetails = $pcomrecord['idassistdetails'];

    $pcomentry = new stdClass;
    
    $command = "CALL inserthistory('$idassistdetails', 'ASSISTDETAIL', 'DELETEDPCOM', '". str_replace("'", "\'", json_encode($pcomrecord)) ."', '". str_replace("'", "\'", json_encode($pcomentry)) ."', '$userid', '$tk')";
    $row = getrow($command);

	echo json_encode(['pcom_detailsid' => $pcom_detailsid]);
}


function getdetails() {
	
	$raf = htmlspecialchars(strtoupper(trim($_REQUEST['raf'])),ENT_QUOTES,"UTF-8");
	$result = ['idassistdetails' => -1];
	
	if (!empty($raf)) {
		$qry =  "SELECT
                    ast.idassistdetails, ast.rafNum, date(ast.dateReceive) dateReceive,
                    pat.idpatient, pat.benLName, pat.benFName, pat.benMName, pat.suffix, pat.benAddrSt, pat.brgyCode, pat.benSex, pat.benPHealth,
                    pat.benBDate, pat.philsysid, pat.benContact,
                    
                    ast.billAmount, ast.amtApproved, ast.dateApproved, ast.noteTag,
                    ast.procloc, ast.idassistsched, ast.typeClient,
                    
                    intk.idintake, intk.details, intk.sworker, intk.requestor, intk.reqAddr, intk.relation, intk.remarks,
                    ofc.provCat, ast.provCode
                FROM `assistdetail` ast
                LEFT JOIN intake intk ON ast.idassistdetails = intk.idassistdetails
                INNER JOIN patient pat ON pat.idpatient = ast.idpatient
                INNER JOIN office ofc ON ofc.officecode = ast.provCode
                where rafNum = '$raf' and status IN ('APPROVED','OVERRIDE')";
		$result = getrow($qry);
		
		if ($result) {
			$qry =  "SELECT det.* FROM `pcom_details` det WHERE idassistdetails = ". $result['idassistdetails'];
			$result['pcom_details'] = gettable($qry);
		} else $result = ['idassistdetails' => -1];
	}
	
	return $result;
}

function searchdetails() {
	
	$raf = htmlspecialchars(strtoupper(trim($_REQUEST['raf'])),ENT_QUOTES,"UTF-8");
	$result = ['idassistdetails' => -1];
	
	if (!empty($raf)) {
		$qry =  "SELECT
                    ast.idassistdetails, ast.rafNum, date(ast.dateReceive) dateReceive,
                    pat.idpatient, pat.benLName, pat.benFName, pat.benMName, pat.suffix, pat.benAddrSt, pat.brgyCode, pat.benSex, pat.benPHealth,
                    pat.benBDate, pat.philsysid, pat.benContact,
                    
                    ast.billAmount, ast.amtApproved, ast.dateApproved, ast.noteTag,
                    ast.procloc, ast.idassistsched, ast.typeClient,
                    
                    intk.idintake, intk.details, intk.sworker, intk.requestor, intk.reqAddr, intk.relation, intk.remarks,
                    ofc.provCat, ast.provCode
                FROM `assistdetail` ast
                LEFT JOIN intake intk ON ast.idassistdetails = intk.idassistdetails
                INNER JOIN patient pat ON pat.idpatient = ast.idpatient
                INNER JOIN office ofc ON ofc.officecode = ast.provCode
                where rafNum = '$raf' and status IN ('APPROVED','OVERRIDE')";
		$result = getrow($qry);
		
		if ($result) {
			$qry =  "SELECT det.* FROM `pcom_details` det WHERE idassistdetails = ". $result['idassistdetails'];
			$result['pcom_details'] = gettable($qry);
		} else $result = ['idassistdetails' => -1];
	}
	
	echo json_encode($result);
}

$myobj = validatetoken();
if (empty($myobj->userid)) {
    header('Location:../index.html?message='.urlencode("Invalid User"));
	die('system error');
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];

if ($trans=='UPDATE') {
	updatedetails($userid);
}
if ($trans=='search') {
	searchdetails();
}
if ($trans=='removemed') {
	removemed($userid);
}
?>