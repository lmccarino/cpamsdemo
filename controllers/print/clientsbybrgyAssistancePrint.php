<?php

require '../fpdf183/fpdf.php';
require 'fpdfextension.php';
require '../connect.php';
include '../clientsServedController.php';
setlocale(LC_CTYPE, 'en_US');

$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';
$preparedby = $_GET['preparedby'];
$notedby = $_GET['notedby'];
$provcat = $_GET['provcat'];
$acode = $_GET['acode'];
$location_id = $_GET['location'];

$from = date('M d, Y', strtotime($datefrom));
$to = date('M d, Y', strtotime($dateto));
$clientsServed = new ClientsServed();

$pdf=new FPDF_CellFit('P','mm','Letter');
$font = 'Arial';
$lineheight = 4;

$pdf->AddPage();
$pdf->SetTitle("Catered Clients by Barangay");
$pdf->SetMargins(15, 13, 15);

$pdf->Ln();
$pdf->Image('../../images/davaocity-logo.jpg',8,6,25,25);
$pdf->Image('../../images/lingap.jpg',180,6,25,28);

$sql = "SELECT officename FROM `office` where idoffice = $location_id";
$query = mysqli_query($conn, $sql); 
while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)) {
	$location = $row['officename'];
}

// ========================================
// =                HEADER                =
// ========================================
$pdf->SetFont($font, '', 11);
$pdf->Cell(0,$lineheight, 'Republic of the Philippines', '', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, 'City of Davao', '', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, 'Office of the City Mayor', '', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, "Lingap Para sa Mahirap", '', 0, 'C');
$pdf->Ln(10);
$pdf->SetFont($font, 'B', 11);
$pdf->Cell(0,$lineheight, "Total Catered Clients by Barangay" ,'',0, 'C');
$pdf->Ln(5);
$pdf->SetFont($font, '', 10);
$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');
$pdf->Ln(8);
$pdf->Cell(0,$lineheight, "Provider Category: ".$provcat, '', 0, 'L');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, "Processing Location: ".( (!empty($location)) ? " $location":"ALL" ), '', 0, 'L');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, "Assistance Type: ".$acode, '', 0, 'L');

$pdf->Ln(10);
$count=0;

$pdf->SetFont($font, 'B', 11);
$pdf->SetWidths(array(50,90,48));
$pdf->SetAligns(array('C','C','C'));
$pdf->Row(array('DISTRICT','BARANGAY', 'TOTAL'));

if ($location == 'ALL') $location = "";

if ( (!empty($location)) ) {
	$location = "AND a.procloc = $location_id";
} else
	$location = "";

if ($provcat == 'ALL')
{
	$sql = "SELECT CASE WHEN
				sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, 
			a.idpatient, b.benLName, b.brgyCode, c.distName, c.brgyName
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND a.status = 'APPROVED' $location
			AND (CASE WHEN sc.assistCode IS NOT NULL THEN CONCAT(sc.assistCode, CASE WHEN TRIM(sc.assistDesc) != '' THEN CONCAT('-', sc.assistDesc) ELSE '' END) ELSE a.assistCode END) LIKE '$acode%' 
			ORDER BY c.distName ASC, c.brgyCode ASC";
	$query = mysqli_query($conn, $sql); 

	$sqlAssistance = "SELECT
            COUNT(CASE WHEN sc.assistCode = '$acode' OR a.assistCode = '$acode' THEN a.idpatient END) AS total
        FROM assistdetail as a
        LEFT JOIN patient as b ON a.idpatient = b.idpatient
        LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
        LEFT JOIN assistsched as sc ON sc.idassistsched = a.idassistsched
        WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
        AND a.status = 'APPROVED' $location";

		$queryAssistance = mysqli_query($conn, $sqlAssistance);
		$result = mysqli_fetch_assoc($queryAssistance);

		$total = $result['total'];
}
else{
	$sql = "SELECT 
			CASE WHEN sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, a.idpatient, a.procloc, b.benLName, b.brgyCode, c.distName, c.brgyName
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN office as o ON a.provCode = o.officecode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND o.provCat = '$provcat'
			AND a.status = 'APPROVED' $location
			AND (CASE WHEN sc.assistCode IS NOT NULL THEN CONCAT(sc.assistCode, CASE WHEN TRIM(sc.assistDesc) != '' THEN CONCAT('-', sc.assistDesc) ELSE '' END) ELSE a.assistCode END) LIKE '$acode%'
			ORDER BY c.distName ASC, c.brgyCode ASC";
	$query = mysqli_query($conn, $sql); 

	$sqlAssistance = "SELECT
            COUNT(CASE WHEN sc.assistCode = '$acode' OR a.assistCode = '$acode' THEN a.idpatient END) AS total
        FROM assistdetail as a
        LEFT JOIN patient as b ON a.idpatient = b.idpatient
        LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
		LEFT JOIN office as o ON a.provCode = o.officecode
        LEFT JOIN assistsched as sc ON sc.idassistsched = a.idassistsched
        WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
		AND o.provCat = '$provcat'
        AND a.status = 'APPROVED' $location";

		$queryAssistance = mysqli_query($conn, $sqlAssistance);
		$result = mysqli_fetch_assoc($queryAssistance);

		$total = $result['total'];
		
}

$grandTotal = $total;

$dist = null;
$prevDist = null;
$prevBrgy = null;

$rownum = 0;
$write = true;

$rows = [];
$tablerows = [];
while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
	array_push($rows,$row);

	if($row['brgyName'] != $prevBrgy)
		array_push($tablerows,[
			'brgyName' 	 => $row['brgyName'],
			'distName' 	 => $row['distName'],
			'total' 	 => 0,
		]);

	$prevBrgy = $row['brgyName'];
}

foreach ($rows as $row) {
	$key = array_search($row['brgyName'], array_column($tablerows, 'brgyName'));

		$tablerows[$key]['total']++;
	
}

$prevDist = null;
foreach($tablerows as $row) {
	$distName = utf8_decode($row['distName']);

	$pdf->SetFont($font,'',10);
	$pdf->SetAligns(array('L','L','C'));
	$pdf->Row(array((($prevDist != $distName) ? $distName : ''), utf8_decode($row['brgyName']), $row['total']));

	$prevDist = utf8_decode($row['distName']);
}

$pdf->SetWidths(array(140,48));
$pdf->SetAligns(array('L','C','C'));
$pdf->SetFont($font, 'B', 11);
$pdf->Row(array('OVER-ALL TOTAL', $total));

$pdf->Ln(10);
$pdf->SetFont($font, 'B', 11);
$pdf->Cell(125, 0, 'Prepared by: ', 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, 'Noted by: ', 0, 0, 'L', 0, '', 0);
$pdf->Ln(14);
$pdf->SetFont($font, 'B', 10);
$pdf->Cell(125, 0, $clientsServed->getUser($preparedby,'fullname'), 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, $clientsServed->getUser($notedby,'fullname'), 0, 0, 'L', 0, '', 0);
$pdf->Ln(5);
$pdf->SetFont($font, '', 10);
$pdf->Cell(125, 0, $clientsServed->getUser($preparedby,'position'), 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, $clientsServed->getUser($notedby,'position'), 0, 0, 'L', 0, '', 0);

ob_end_clean();

$pdf->Output();

 ?>

