<?php
require "routines.php";
function getroles($role){
	$command = "select * from roles where name = '$role' and active ='Y' limit 1";
	$roles = getrow($command);
	$idroles = $roles['idroles'];
	$command = "select users.userid, users.fullname, users.cellno, users.emailaddress, users.office from users left join rolesusers on users.userid = rolesusers.idusers where rolesusers.idroles = $idroles and users.active ='Y' and users.userid <> 77";
	$receivers = gettable($command);
return $receivers;
}
$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];
if ($trans=="getroles") {
	$finalusers = array();
	$roles = $_REQUEST['roles'];
	foreach ($roles as $role) {
		$receivers = getroles($role);
		foreach($receivers as $receiver) {
			if ( empty( $finalusers[$receiver['userid']] ) )
				$finalusers[$receiver['userid']] = $receiver;
		}
	}
	echo json_encode(array_values($finalusers));
}
?>