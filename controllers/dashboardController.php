<?php
require "routines.php";

function getTotalApproved($span, $myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
        $command = "SELECT
                        COUNT(*) AS total,
                        ROUND(SUM(amtApproved), 2) AS amount
                    FROM assistdetail
                    WHERE `status` = 'APPROVED'
                        AND (
                            ($span = 1 AND date(dateApproved) = date(now()))
                            OR
                            ($span = 2 AND week(dateApproved) = week(now()))
                            OR
                            ($span = 3 AND month(dateApproved) = month(now()))
                        );";
    
		$table->data = getrow($command);

	} else $table->data = ['total' => 0, 'amount' => 0];
	
    echo json_encode($table);
}

function getTotalOverride($span, $myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
        $command = "SELECT
                        ROUND(SUM(debit), 2) AS amount,
                        COUNT(*) AS total
                    FROM lingapfund
                        WHERE 
                            (details LIKE 'OVERRIDE%' OR details LIKE 'REAPPROVED%')
                            AND
                            (
                                ($span = 1 AND date(dateSBal) = date(now()))
                                OR
                                ($span = 2 AND week(dateSBal) = week(now()))
                                OR
                                ($span = 3 AND month(dateSBal) = month(now()))
                            );";
    
		$table->data = getrow($command);
	} else $table->data = ['total' => 0, 'amount' => 0];
    echo json_encode($table);
}

function getTotalCancelled($span, $myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
        $command = "SELECT
                        COUNT(*) AS total,
                        ROUND(SUM(amtApproved), 2) AS amount
                    FROM assistdetail
                    WHERE `status` = 'CANCELLED'
                        AND (
                            ($span = 1 AND date(dateCancel) = date(now()))
                            OR
                            ($span = 2 AND week(dateCancel) = week(now()))
                            OR
                            ($span = 3 AND month(dateCancel) = month(now()))
                        );";
    
		$table->data = getrow($command);

	} else $table->data = ['total' => 0, 'amount' => 0];
	
    echo json_encode($table);
}

function getTotalApprovedAmount($span, $myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
    $command = "SELECT ROUND(SUM(assistdetail.amtApproved), 2) as total
                FROM assistdetail 
                WHERE assistdetail.`status` = 'APPROVED' 
                      AND (
                        ($span = 1 AND date(dateApproved) = date(now()))
                        OR
                        ($span = 2 AND week(dateApproved) = week(now()))
                        OR
                        ($span = 3 AND month(dateApproved) = month(now()))
                      );";
    
		$table->data = getrow($command);
	} else $table->data = ['total' => 0];
	
    echo json_encode($table);
}

function getTotalApprovedAmountByCategory($span, $myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
    $command = "SELECT office.provCat as category, SUM(assistdetail.amtApproved) as amount
                FROM assistdetail
                INNER JOIN office ON office.officecode = assistdetail.provCode
                WHERE assistdetail.`status` = 'APPROVED' 
                      AND (
                        ($span = 1 AND date(dateApproved) = date(now()))
                        OR
                        ($span = 2 AND week(dateApproved) = week(now()))
                        OR
                        ($span = 3 AND month(dateApproved) = month(now()))
                      )
                GROUP BY office.provCat;";
    
		$table->data = gettable($command);
	} else $table->data = [];
    echo json_encode($table);
}

function getRemainingBalance($myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
        $command = "SELECT ROUND(balAmount, 2) as amount, ROUND(balCritLevel, 2) as critical
                    FROM lingapfund
                    ORDER BY dateSBal DESC
                    LIMIT 1;";
    
		$table->data = getrow($command);

        $command = "SELECT ROUND(balAmount, 2) as total
                    FROM lingapfund
                    WHERE details = 'REPLENISH ALLOTMENT'
                    ORDER BY dateSBal DESC
                    LIMIT 1;";
    
		$table->data['total'] = getrow($command)['total']??0;
	} else $table->data = ['amount' => 0, 'critical' => 0, 'total' => 0];
    echo json_encode($table);
}

function getTotalApprovedByGender($span, $myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
    $command = "SELECT patient.benSex as sex, COUNT(*) as total
                FROM assistdetail
                INNER JOIN patient ON patient.idpatient = assistdetail.idpatient
                WHERE   (patient.benSex IS NOT NULL OR TRIM(patient.benSex) != '')
                        AND assistdetail.`status` = 'APPROVED'
                        AND (
                            ($span = 1 AND date(dateApproved) = date(now()))
                            OR
                            ($span = 2 AND week(dateApproved) = week(now()))
                            OR
                            ($span = 3 AND month(dateApproved) = month(now()))
                        )
                GROUP BY patient.benSex;";
    
		$table->data = gettable($command);
	} else $table->data = [];
    echo json_encode($table);
}

function getTotalApprovedByPlatform($span, $myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
    $command = "SELECT typeClient, COUNT(*) as total
                FROM assistdetail
                WHERE (typeClient IS NOT NULL OR TRIM(typeClient) != '')
                        AND assistdetail.`status` = 'APPROVED'
                        AND (
                            ($span = 1 AND date(dateApproved) = date(now()))
                            OR
                            ($span = 2 AND week(dateApproved) = week(now()))
                            OR
                            ($span = 3 AND month(dateApproved) = month(now()))
                        )
                GROUP BY typeClient";
    
		$table->data = gettable($command);
	} else $table->data = [];
    echo json_encode($table);
}

function getTotalApprovedByAge($span, $myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
    $command = "SELECT TIMESTAMPDIFF(YEAR, patient.benBDate, Now()) AS age, COUNT(*) as total
                FROM assistdetail
                INNER JOIN patient ON patient.idpatient = assistdetail.idpatient
                WHERE patient.benBDate IS NOT NULL
                      AND assistdetail.`status` = 'APPROVED'
                      AND (
                        ($span = 1 AND date(dateApproved) = date(now()))
                        OR
                        ($span = 2 AND week(dateApproved) = week(now()))
                        OR
                        ($span = 3 AND month(dateApproved) = month(now()))
                      )
                GROUP BY age;";
    
		$table->data = gettable($command);
	} else $table->data = [];
    echo json_encode($table);
}

function getTotalApprovedByBarangay($span, $myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
    $command = "SELECT brgyName as barangay, COUNT(*) as total
                FROM assistdetail
                INNER JOIN patient ON patient.idpatient = assistdetail.idpatient
                INNER JOIN distbrgy ON distbrgy.brgyCode = patient.brgyCode
                WHERE assistdetail.`status` = 'APPROVED'
                      AND (
                        ($span = 1 AND date(dateApproved) = date(now()))
                        OR
                        ($span = 2 AND week(dateApproved) = week(now()))
                        OR
                        ($span = 3 AND month(dateApproved) = month(now()))
                      )
                GROUP BY brgyName;";
    
		$table->data = gettable($command);
	} else $table->data = [];
    echo json_encode($table);
}

function getLoginLogs($myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
        $command = "SELECT t.fullname, t.image, t.office, t.ip,
                            CASE
                                WHEN t.odate IS NULL AND t.udate >= DATE_SUB(NOW(), INTERVAL 2 HOUR) THEN 'Online'
                                ELSE 'Offline'
                            END AS online_status
                    FROM tk t
                    INNER JOIN (
                        SELECT fullname, image, office, ip, MAX(ldate) AS latest_ldate
                        FROM tk
                        WHERE DATE(ldate) = DATE(NOW())
                        GROUP BY fullname, image, office, ip
                    ) latest ON t.fullname = latest.fullname 
                            AND t.image = latest.image 
                            AND t.office = latest.office 
                            AND t.ip = latest.ip 
                            AND t.ldate = latest.latest_ldate
                    ORDER BY t.ldate DESC; -- or ORDER BY t.ldate DESC for descending order
";
    
		$table->data = gettable($command);
	} else $table->data = [];
    echo json_encode($table);
}

function getCashFlow($myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
        $command = "SELECT ROUND(balAmount, 2) AS amount, dateSBal AS date, ROUND(credit, 2) AS credit, ROUND(debit, 2) AS debit, details, users.fullname AS officer
                    FROM cpams2.lingapfund
                    LEFT JOIN users on users.userid = lingapfund.userid
                    ORDER BY idlingapfund DESC
                    LIMIT 15;";
    
		$table->data = gettable($command);
	} else $table->data = [];
    echo json_encode($table);
}

function getHistoryList($myobj){
    $table = new stdClass();
	if (hasRole($myobj->userid, 'SUPERVISOR') || hasRole($myobj->userid, 'TEAM LEADER')) {
	    $command  = "SELECT 
                        history.*, 
                        users.fullname AS officer
                    FROM history 
                    LEFT JOIN users ON users.userid = history.userid 
                    ORDER BY history.id DESC
                    LIMIT 30;";
    
        $table->data = gettable($command);
    } else $table->data = [];
    echo json_encode($table);
}

$trans = $_REQUEST['trans']??'';
$span  = $_REQUEST['span']??1;
$myobj = validatetoken();
if ($trans=='total_approved') {
	getTotalApproved($span, $myobj);
}
if ($trans=='total_override') {
	getTotalOverride($span, $myobj);
}
if ($trans=='total_cancelled') {
	getTotalCancelled($span, $myobj);
}
if ($trans=='total_approved_amount_by_category') {
	getTotalApprovedAmountByCategory($span, $myobj);
}
if ($trans=='total_remaining_balance') {
	getRemainingBalance($myobj);
}
if ($trans=='total_approved_by_gender') {
	getTotalApprovedByGender($span, $myobj);
}
if ($trans=='total_approved_by_platform') {
	getTotalApprovedByPlatform($span, $myobj);
}
if ($trans=='total_approved_by_age') {
	getTotalApprovedByAge($span, $myobj);
}
if ($trans=='total_approved_by_barangay') {
	getTotalApprovedByBarangay($span, $myobj);
}
if ($trans=='login_logs') {
	getLoginLogs($myobj);
}
if ($trans=='cash_flow') {
	getCashFlow($myobj);
}
if ($trans=='history') {
	getHistoryList($myobj);
}

?>