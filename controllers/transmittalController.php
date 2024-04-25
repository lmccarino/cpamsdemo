<?php
require "routines.php";

function add($soa, $code, $userid, $tk) {
    try {
        $command ="CALL inserttransmittal('$soa', '$code', $userid, '$tk')";
        $result = getrow($command);

		if (isset($result['id']) && $result['id'] > 0)
	        return json_encode(["success" => true, "message" => "Transmittal successfully inserted.", "data" => $result]);
        else 
            throw new Exception("Failed to insert record.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function edit($id, $soa, $code, $userid, $tk) {
    try {
        if(!is_numeric($id) || $id == 0)
            throw new Exception("Invalid request id.");

		$qry =  "SELECT * FROM transmittals WHERE idtransmittals = $id LIMIT 1";
		$result = getrow($qry);
		
		if ($result){
            $command ="CALL updatetransmittal('$id', '$soa', '$code', $userid, '$tk')";
            $result = getrow($command);

            if (isset($result['id']) && $result['id'] > 0)
                return json_encode(["success" => true, "message" => "Transmittal successfully updated.", "data" => $result]);
            else 
                throw new Exception("Failed to insert record.");
        }
        else 
            throw new Exception("Record not found.");
    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function getAll() {
    try {
		$qry =  "SELECT * FROM transmittals ORDER BY idtransmittals DESC";
		$result = gettable($qry);
		
		if ($result)
	        return json_encode(["success" => true, "message" => "successfully retrieved.", "data" => $result]);
        else 
            throw new Exception("Returning empty list.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function delete($id, $userid) {
    try {
        if(!is_numeric($id) || $id == 0)
            throw new Exception("Invalid request id.");
        
        $rafQuery =  "SELECT 1 FROM assistdetail AS ad WHERE ad.idtransmittals = $id LIMIT 1";
        $rafResult = getrow($rafQuery);

        if(!empty($rafResult))
            throw new Exception("Unable to delete. Remove raf before deleting.");

        $command ="CALL deletetransmittal($id, $userid)";
        $result = getrow($command);
		
		if (isset($result['id']) && $result['id'] == $id)
	        return json_encode(["success" => true, "message" => "successfully deleted.", "data" => $result]);
        else 
            throw new Exception("Record not found.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function getRAF($id,$code) {
    try {
        if(!is_numeric($id) || $id == 0)
            throw new Exception("Invalid request id.");

		$qry =  "SELECT ad.*, p.benLName, p.benFName, p.benMName, p.suffix, p.benSex, p.benBDate, p.benAddrSt, brgy.brgyName, brgy.distName
                 FROM assistdetail AS ad
                 LEFT JOIN patient AS p ON p.idpatient = ad.idpatient
                 LEFT JOIN distbrgy AS brgy ON brgy.brgyCode = p.brgyCode
                 WHERE ad.rafnum = $id AND ad.status = 'APPROVED'
                 LIMIT 1";
		$result = getrow($qry);
		
		if ($result){
            if($result['idtransmittals'] > 0)
                throw new Exception("RAF already assigned to transmittal " . $result['idtransmittals']);
            elseif($result['provCode'] != $code)
                throw new Exception("RAF Provider is not the same as Transmittal Provider.");
            else 
                return json_encode(["success" => true, "message" => "successfully retrieved.", "data" => $result]);
        }
        else 
            throw new Exception("Record not found.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function addToTransmittal($id,$transmittal,$userid,$tk) {
    try {
        if(!is_numeric($id) || $id == 0)
            throw new Exception("Invalid request id.");

        $command ="CALL assignraftransmittal($id,$transmittal, $userid, '$tk')";
        $result = getrow($command);

        if (isset($result['id']) && $result['id'] > 0)
            return json_encode(["success" => true, "message" => "RAF successfully added to transmittal.", "data" => $result]);
        else 
            throw new Exception("Failed to insert record.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function removeToTransmittal($id,$userid,$tk) {
    try {
        if(!is_numeric($id) || $id == 0)
            throw new Exception("Invalid request id.");

        $command ="CALL removeassignraftransmittal($id, $userid, '$tk')";
        $result = getrow($command);

        if (isset($result['id']) && $result['id'] > 0)
            return json_encode(["success" => true, "message" => "RAF successfully removed to transmittal.", "data" => $result]);
        else 
            throw new Exception("Failed to insert record.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function get($id) {
    try {
        if(!is_numeric($id) || $id == 0)
            throw new Exception("Invalid request id.");

		$qry =  "SELECT * FROM transmittals WHERE idtransmittals = $id LIMIT 1";
		$result = getrow($qry);
		
		if ($result)
	        return json_encode(["success" => true, "message" => "successfully retrieved.", "data" => $result]);
        else 
            throw new Exception("Record not found.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function getProviders() {
    try {
		$qry =  "SELECT 
                    officecode AS code, 
                    officename AS provider,
                    location AS address,
                    provCat AS category 
                FROM office 
                WHERE officeType = 'PROVIDER' AND active = 'Y'
                ORDER BY provider ASC";

		$result = gettable($qry);
		
		if ($result)
	        return json_encode(["success" => true, "message" => "successfully retrieved.", "data" => $result]);
        else 
            throw new Exception("Failed to retrieve list.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function getTransmittalRafs($id) {
    try {
        if(!is_numeric($id) || $id == 0)
            throw new Exception("Invalid request id.");

		$qry =  "SELECT ad.*, p.benLName, p.benFName, p.benMName, p.suffix, p.benSex, p.benBDate, p.benAddrSt
                 FROM assistdetail AS ad
                 LEFT JOIN patient AS p ON p.idpatient = ad.idpatient
                 WHERE ad.idtransmittals = $id
                 ORDER BY p.benLName, p.benFName";

		$result = gettable($qry);
		
		if ($result)
	        return json_encode(["success" => true, "message" => "successfully retrieved.", "data" => $result]);
        else 
            throw new Exception("Record not found.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function search() {
    try {
    	$from = htmlspecialchars(strtoupper(trim($_REQUEST['search_date_from'])),ENT_QUOTES,"UTF-8");
    	$to   = htmlspecialchars(strtoupper(trim($_REQUEST['search_date_to'])),ENT_QUOTES,"UTF-8");
    	$id   = htmlspecialchars(strtoupper(trim($_REQUEST['search_transmittal_id'])),ENT_QUOTES,"UTF-8");
    	$soa  = htmlspecialchars(strtoupper(trim($_REQUEST['search_soa_num'])),ENT_QUOTES,"UTF-8");

        $find = "SELECT 
                    t.*, 
                    o.officename AS providerName, 
                    o.provcat AS providerCategory
                FROM transmittals as t
                LEFT JOIN office AS o ON t.provcode = o.officecode 
                WHERE ";

        $xwhere ='';

        if (!empty($id)) {
            $xwhere = "idtransmittals = '$id'";
        } 
        if (!empty($soa)) {
            $xwhere = ((empty($xwhere)) ? '' : $xwhere . ' AND ') . "soa = '$soa'";
        }
        if (!empty($from) && isValidDate($from)) {
            $xwhere = ((empty($xwhere)) ? '' : $xwhere . ' AND ') . "created >= '$from'";
        }
        if (!empty($to) && isValidDate($to)) {
            $xwhere = ((empty($xwhere)) ? '' : $xwhere . ' AND ') . "created <= '$to'";
        }
        if (empty($id) && empty($soa) && empty($from) && empty($to)) {
            $xwhere = "1 = 1 ORDER BY created DESC";
        } 

        $find .= $xwhere;
		$result = gettable($find);
		
		if ($result)
	        return json_encode(["success" => true, "message" => "successfully retrieved.", "data" => $result]);
        else 
            throw new Exception("Record not found.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function isValidDate($date, $format = 'Y-m-d') {
    $dateTime = \DateTime::createFromFormat($format, $date);
    return $dateTime && $dateTime->format($format) === $date;
}

$myobj = validatetoken();

if (empty($myobj->userid)) {
    header('Location:../index.html?message='.urlencode("Invalid User"));
	die('system error');
} else {$userid = $myobj->userid;}

$trans = $_REQUEST['trans'];

if ($trans=='ADD') {
	echo add($_REQUEST['soa'],$_REQUEST['code'],$userid,$_REQUEST['tk']);
}

if ($trans=='EDIT') {
	echo edit($_REQUEST['idtransmittals'],$_REQUEST['soa'],$_REQUEST['code'],$userid,$_REQUEST['tk']);
}

if ($trans=='GETALL') {
	echo getAll();
}

if ($trans=='GET') {
	echo get($_REQUEST['id']??'');
}

if ($trans=='GETPROVIDERS') {
	echo getProviders();
}

if ($trans=='SEARCHTRANSMITTALS') {
	echo search();
}

if ($trans=='DELETE') {
	echo delete($_REQUEST['id']??'',$userid);
}

if ($trans=='GETRAF') {
	echo getRAF($_REQUEST['id']??'',$_REQUEST['code']);
}

if ($trans=='ADDRAFTOTRANSMITTAL') {
	echo addToTransmittal($_REQUEST['id']??'',$_REQUEST['transmittal']??'',$userid,$_REQUEST['tk']);
}

if ($trans=='REMOVERAFTOTRANSMITTAL') {
	echo removeToTransmittal($_REQUEST['id']??'',$userid,$_REQUEST['tk']);
}

if ($trans=='GETTRANSMITTALRAFS') {
	echo getTransmittalRafs($_REQUEST['id']);
}