<?php require "routines.php";

function getHistoryList(){
	$myobj = new stdClass();
    $referenceid = htmlspecialchars(strtoupper(trim($_REQUEST['referenceid'])),ENT_QUOTES,"UTF-8");
	$reference  = htmlspecialchars(strtoupper(trim($_REQUEST['reference'])),ENT_QUOTES,"UTF-8");

	$command  = "SELECT 
                    history.*, 
                    users.fullname AS officer
                 FROM history 
                 LEFT JOIN users ON users.userid = history.userid 
                 WHERE history.referenceid = '$referenceid' AND history.reference = '$reference'
                 ORDER BY history.id DESC";

	$myobj->data = gettable($command);

	echo json_encode($myobj);
}

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User"));
	die('system error');
} else {$userid = $myobj->userid;}

$trans = $_REQUEST['trans'];

if ($trans=='GETHISTORYLIST'){
	getHistoryList();
}