<?php
require "routines.php";

function verifytoken($token){
    try {
        $myobj = new stdClass();
        if (!empty($token)){
            $ip = getUserIpAddr();
            $ldate = date("Y-m-d");
            $command = "select * from tk where token = '$token' and ip = '$ip' and date(ldate) = '$ldate' and odate is null and odate is null and udate >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
            $data = getrow($command);
            if (!empty($data))
                echo json_encode(['success'=>true, 'message'=>'', 'data'=>[]]);
            else
                echo json_encode(['success'=>false, 'message'=>'', 'data'=>[]]);
        } 
        else {
            echo json_encode(['success'=>false, 'message'=>'', 'data'=>[]]);
        }
    } catch (Exception $e) {
        echo json_encode(['success'=>false, 'message'=>$e->getMessage(), 'data'=>$e]);
    }
}

$trans = $_REQUEST['trans'];
if ($trans=='verifytoken'){
	verifytoken($_REQUEST['tk']);
}
?>