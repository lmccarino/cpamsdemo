<?php

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['trans'])) {
    http_response_code(404);
    exit();
}

require "routines.php";

// Function to handle exceptions
function handleException($e) {
    return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
}

// Function to authenticate user
function authenticateUser($username, $password, $ip) {
    $command = "CALL validateuser('$username', '$password', '$ip')";
    return getrow($command);
}

// Function to generate token
function generateToken($userdata) {
    $generatedtoken = savetk($userdata->userid, $userdata->fullname, $userdata->image, $userdata->office, $userdata->role, $userdata->email, $userdata->signature);
    return $generatedtoken['@token'];
}

// Function to validate token
function validateUserToken($token, $ip) {
    $ldate = date("Y-m-d");
    $command = "SELECT * FROM tk WHERE token = '$token' AND ip = '$ip' AND DATE(ldate) = '$ldate' AND odate IS NULL;";
    return getrow($command);
}

// Authenticate user if token is not provided
if (!isset($_POST['token'])) {
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        http_response_code(404);
        exit();
    }
    
    $ip       = $_SERVER['REMOTE_ADDR'];
    $email    = $_POST['username'];
    $password = $_POST['password'];

    $row = authenticateUser($email, $password, $ip);

    if (!empty($row)) {
        $userdata = new stdClass();
        $userdata->userid = $row['userid'];
        $userdata->fullname = $row['fullname'];
        $userdata->image = 'person.jpg';
        $userdata->email = $row['emailaddress'];
        $userdata->office = $row['office'];
        $userdata->role = '';
        $userdata->signature = $row['signature'];

        $generatedtoken = generateToken($userdata);

        echo json_encode(["success" => true, "message" => "Account authenticated.", "data" => ["token" => $generatedtoken]]);
        exit();
    } else {
        http_response_code(404);
        exit();
    }
}

// Validate token if provided
$ip    = $_SERVER['REMOTE_ADDR'];
$token = $_POST['token'] ?? '';

if (!empty($token)) {
    $authenticated = (object)validateUserToken($token, $ip);

    if (empty($authenticated)) {
        echo json_encode(["success" => false, "message" => "Token Expired", "data" => []]);
        exit();
    }
}

// API Class
class API {
    private function getTransmittalQuery($id) {
        return "SELECT 
                    t.idtransmittals AS id, 
                    t.soa, 
                    t.checkno AS `check`, 
                    t.amount, 
                    t.approveddate AS approve,
                    t.created AS `date`,
                    o.officename AS `provider` 
                FROM transmittals AS t
                LEFT JOIN office AS o ON t.provcode = o.officecode 
                WHERE t.idtransmittals = $id LIMIT 1";
    }

    private function getRAFQuery($id) {
        return "SELECT 
                    ad.rafNum AS raf,
                    ad.amtApproved AS amount,
                    ad.dateApproved As `date`, 
                    TRIM(CONCAT(p.benLName, ', ', p.benFName, ' ', p.benMName, ' ', p.suffix)) AS fullname, 
                    p.benSex AS sex 
                FROM assistdetail AS ad
                LEFT JOIN patient AS p ON p.idpatient = ad.idpatient
                WHERE ad.idtransmittals = $id
                ORDER BY p.benLName, p.benFName";
    }

    public function find($id) {
        try {
            if (!is_numeric($id) || $id == 0)
                throw new Exception("Invalid request id.");

            $transmittalQuery = $this->getTransmittalQuery($id);
            $result = getrow($transmittalQuery);
            
            if ($result) {
                $id = $result['id'];
                $rafQuery = $this->getRAFQuery($id);
                $rafResult = gettable($rafQuery);
                $result['rafs'] = $rafResult;
                return json_encode(["success" => true, "message" => "successfully retrieved.", "data" => $result]);
            } else {
                throw new Exception("Record not found.");
            }
        } catch (Exception $e) {
            return handleException($e);
        }
    }

    public function update($id, $check, $amount, $userdataid, $tk) {
        try {
            if (!is_numeric($id) || $id == 0)
                throw new Exception("Invalid request id.");
        
            $transmittalQuery = $this->getTransmittalQuery($id);
            $result = getrow($transmittalQuery);
            
            if (empty($result))
                throw new Exception("Record not found.");
            
            $command ="CALL paymenttransmittal($id, '$check', $amount, $userdataid, '$tk')";
            $result = getrow($command);

            if (isset($result['id']) && $result['id'] > 0)
                return json_encode(["success" => true, "message" => "Payment updated.", "data" => []]);
            else 
                throw new Exception("Failed to update record.");
        } catch (Exception $e) {
            return handleException($e);
        }
    }

    public function undo($id, $userdataid, $tk) {
        try {
            if (!is_numeric($id) || $id == 0)
                throw new Exception("Invalid request id.");
        
            $transmittalQuery = $this->getTransmittalQuery($id);
            $result = getrow($transmittalQuery);
            
            if (empty($result))
                throw new Exception("Record not found.");
            
            $command = "CALL undopaymenttransmittal($id, $userdataid, '$tk')";
            $result = getrow($command);

            if (isset($result['id']) && $result['id'] > 0)
                return json_encode(["success" => true, "message" => "Undo payment successfully.", "data" => []]);
            else 
                throw new Exception("Failed to update record.");
        } catch (Exception $e) {
            return handleException($e);
        }
    }
}

$api = new API();

$trans = $_POST['trans'] ?? '';

switch ($trans) {
    case 'FIND':
        echo $api->find($_POST['id'] ?? '');
        break;
    case 'UPDATE':
        echo $api->update($_POST['id'] ?? '', $_POST['check'] ?? '', $_POST['amount'] ?? 0, 0, $token);
        break;
    case 'UNDO':
        echo $api->undo($_POST['id'] ?? '', $authenticated->userid ?? 0, $token);
        break;
    default:
        http_response_code(404);
        exit();
}