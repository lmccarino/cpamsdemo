<?php

require '../fpdf183/fpdf.php';
require 'fpdfextension.php';
require '../connect.php';
include '../clientsServedController.php';
include '../convertEncoding.php';
setlocale(LC_CTYPE, 'en_US');

$clientsServed = new ClientsServed();
$convertEncoding = new convertEncoding();

$dtefrom = $_REQUEST['from'].' 00:00:00';
$dteto = $_REQUEST['to'].' 23:59:59';
$provcat = $_REQUEST['cat'];
$procloc = $_REQUEST['loc'];
$preparedby = $_REQUEST['preparedby'];
$notedby = $_REQUEST['notedby'];

$from = date('F d, Y', strtotime($dtefrom));
$to = date('F d, Y', strtotime($dteto));

$sql = "SELECT officename, provCat FROM office WHERE idoffice = '$procloc'";
$query = mysqli_query($conn, $sql); 
while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)) {
	$code = $row['officename'];
	$category = $row['provCat'];
}


$pdf=new FPDF_CellFit('P','mm','Letter');
$font = 'Arial';
$lineheight = 4;

$pdf->AddPage();
$pdf->SetTitle("Masterlist of Approved Assistance (Beneficiary)");
$pdf->SetMargins(8, 13, 8);

$pdf->Ln();
$pdf->Image('../../images/davaocity-logo.jpg',8,6,25,25);
$pdf->Image('../../images/lingap.jpg',183,6,25,28);

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
$pdf->SetFont($font, 'B', 10);
$pdf->Cell(0,$lineheight, "Masterlist of Approved Assistance - Beneficiary/Patient", '', 0, 'C');
$pdf->Ln(5);
$pdf->SetFont($font, '', 9);
$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, "Provider Category: ".$provcat, '', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, "Processing Location: ".( (!empty($code)) ? " $code":"ALL" ), '', 0, 'C');
$pdf->Ln(8);
$count=0;

$pdf->SetWidths(array(11,17,48,68,23,18,17));
$pdf->SetAligns(array('C','C','C','C','C','C','C'));
$pdf->SetFont($font, 'B', 8);
$pdf->Row(array('#','RAF No.','Name of Beneficiary / Patient', 'Provider', 'Assistance Type' ,'Date Approved', 'Amount Approved'));
$total = 0;

if ( (!empty($code)) ) {
	$code = "AND a.procloc = $procloc";
} else
	$code = "";
	
if ($provcat == 'ALL'){
	$sql = "SELECT a.rafNum, 
			CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS patientname,
			o.officename AS providerName, CASE WHEN sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, a.amtApproved, 
			a.dateApproved
		FROM assistdetail as a 
		LEFT JOIN patient as p ON a.idpatient = p.idpatient
		LEFT JOIN office as o ON a.provCode = o.officecode
		INNER JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
		WHERE a.dateApproved BETWEEN '$dtefrom' AND '$dteto' AND a.status = 'APPROVED' $code
		ORDER BY a.dateApproved DESC";
}
else {
	$sql = "SELECT a.rafNum, 
			CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS patientname,
			o.officename AS providerName, CASE WHEN sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, a.amtApproved, 
			a.dateApproved
		FROM assistdetail as a 
		LEFT JOIN patient as p ON a.idpatient = p.idpatient
		LEFT JOIN office as o ON a.provCode = o.officecode
		INNER JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
		WHERE a.dateApproved BETWEEN '$dtefrom' AND '$dteto' AND a.status = 'APPROVED'
		AND o.provCat = '$provcat' $code
		ORDER BY a.dateApproved DESC";
}


$query = mysqli_query($conn, $sql); 

while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)) 
{ 
	$rafNum =   $row['rafNum'];
	$patientname =$convertEncoding->convertEncode($row['patientname']);
	$officename =$row['providerName']??'';
	$assistCode =$row['assistCode'];
	$dateApproved =date('m/d/Y', strtotime($row['dateApproved']));
	$amtApproved = number_format((float)($row['amtApproved']??0), 2, '.', ',');
	$totalamt += ($row['amtApproved']??0);
	$totalamount =  number_format((float)$totalamt, 2, '.', ',');

	$pdf->SetFont($font, '', 8.7);
    $pdf->SetAligns(array('C','L','L','L','L','R','R'));
    $pdf->Row(array(++$count, $rafNum, $patientname, $officename, $assistCode, $dateApproved,$amtApproved));
}

$pdf->SetWidths(array(167,35));
$pdf->SetAligns(array('R','R'));
$pdf->SetFont($font,'B',10);
$pdf->Row(array('TOTAL','Php '.number_format($totalamt, 2)));

$pdf->Ln(12);
$pdf->SetFont($font, 'B', 10);
$pdf->Cell(125, 0, 'Prepared by: ', 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, 'Noted by: ', 0, 0, 'L', 0, '', 0);
$pdf->Ln(14);
$pdf->SetFont($font, 'B', 9);
$pdf->Cell(125, 0, $clientsServed->getUser($preparedby,'fullname'), 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, $clientsServed->getUser($notedby,'fullname'), 0, 0, 'L', 0, '', 0);
$pdf->Ln(5);
$pdf->SetFont($font, '', 9);
$pdf->Cell(125, 0, $clientsServed->getUser($preparedby,'position'), 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, $clientsServed->getUser($notedby,'position'), 0, 0, 'L', 0, '', 0);

ob_end_clean();
$pdf->Output();
?>