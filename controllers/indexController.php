<?php
require "routines.php";

function getdetails(){
	$table = new stdClass();
	//$command = "select batchproc.*, document.description, sum(counter) as tcounter from batchproc left join document on batchproc.iddocument = document.iddocument group by iddocument";
	//$command = "select count(*) as recs, office.officename from employees left join services on employees.empno = services.empno left join office on services.officecode = office.officecode where services.edate  is null and employees.active ='Y' group by officename";
	$command = "SELECT count(*) as recs, office.officename, employees.active FROM services left join office on services.officecode = office.officecode left join employees on services.idemployees = employees.idemployees where employees.active = 'Y' group by office.officename";
	$table->data = gettable($command);
	echo json_encode($table);
}

function getgraph1(){
	$table = new stdClass();
	$command = "select IF(active='Y', 'ACTIVE', 'SEPARATED') as active, count(*) as recs from employees group by active";
	$table->data = gettable($command);
	echo json_encode($table);
}

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
} else {
	$userid = $myobj->userid;
	$token = $myobj->token;
}
$trans = $_REQUEST['trans'];
if ($trans=='gentk'){
	$tkform = gentkform($token, $userid);
	echo json_encode($tkform);
}
if ($trans=='getdetails'){
	getdetails();
}
if ($trans=='getgraph1'){
	getgraph1();
}
?>