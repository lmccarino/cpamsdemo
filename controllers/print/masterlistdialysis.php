<?php

require '../fpdf183/fpdf.php';
require 'fpdfextension.php';
require '../connect.php';
include '../clientsServedController.php';
include '../convertEncoding.php';
setlocale(LC_CTYPE, 'en_US');

$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';
$procloc = $_GET['procloc'];
$provider = $_GET['provider'];
$provcat = $_GET['provcat'];
$preparedby = $_GET['preparedby'];
$notedby = $_GET['notedby'];

$clientsServed = new ClientsServed();
$convertEncoding = new convertEncoding();

$from = date('F d, Y', strtotime($datefrom));
$to = date('F d, Y', strtotime($dateto));

$pdf=new FPDF_CellFit('P','mm','Letter');
$font = 'Arial';
$lineheight = 4;

$pdf->AddPage();
$pdf->SetTitle("Masterlist of Approved Assistance (Dialysis)");
$pdf->SetMargins(12, 13, 12);

$pdf->Ln();
$pdf->Image('../../images/davaocity-logo.jpg',14,6,25,25);
$pdf->Image('../../images/lingap.jpg',180,6,25,28);

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
$pdf->Cell(0,$lineheight, "Masterlist of Approved Assistance - Dialysis", '', 0, 'C');
$pdf->Ln(5);
$pdf->SetFont($font, '', 10);
$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, "Provider Category: ".$provcat, '', 0, 'C');

$pdf->Ln(5);
$count=0;

$pdf->SetWidths(array(15,20,55,60,23,23));
$pdf->SetAligns(array('C','C','C','C','C','C'));
$pdf->Row(array('#','RAF No.','Patient', 'Provider', 'Date Approved', 'Amount Approved'));
if ($provcat == 'ALL')
{
	$sql = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, 
	concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) AS patientname, 
	a.provCode, o.officename FROM assistdetail AS a 
	LEFT JOIN patient ON a.idpatient = patient.idpatient 
	LEFT JOIN office AS o ON a.provCode = o.officecode 
	LEFT JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
	WHERE a.dateApproved <= '$dateto' AND a.dateApproved >= '$datefrom' 
	AND a.status = 'APPROVED' AND (sc.assistCode = 'DIALYSIS' OR a.assistCode = 'DIALYSIS') 
	ORDER BY o.officename ASC, a.dateApproved DESC";

	$query = mysqli_query($conn, $sql); 
}

else {
	$sql = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, 
	concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) AS patientname, 
	a.provCode, o.officename FROM assistdetail AS a 
	LEFT JOIN patient ON a.idpatient = patient.idpatient 
	LEFT JOIN office AS o ON a.provCode = o.officecode 
	LEFT JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
	WHERE a.dateApproved <= '$dateto' AND a.dateApproved >= '$datefrom' 
	AND a.status = 'APPROVED' AND (sc.assistCode = 'DIALYSIS' OR a.assistCode = 'DIALYSIS') 
	AND o.provcat = '$provcat' ORDER BY o.officename ASC, a.dateApproved DESC";

	$query = mysqli_query($conn, $sql); 
}
	
while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)) 
{ 
	$rafNum =   $row['rafNum'];
	$patientname =$convertEncoding->convertEncode($row['patientname']);
	$officename =$row['officename']??'';
	$dateApproved =date('m/d/Y', strtotime($row['dateApproved']));
	$amtApproved = number_format((float)($row['amtApproved']??0), 2, '.', ',');
	$totalamt += ($row['amtApproved']??0);
	$totalamount =  number_format((float)$totalamt, 2, '.', ',');

	$pdf->SetFont($font,'',9);
    $pdf->SetAligns(array('L','L','L','L','R','R'));
    $pdf->Row(array(++$count, $rafNum, $patientname, $officename, $dateApproved,$amtApproved));
}
$pdf->SetWidths(array(150,46));
$pdf->SetAligns(array('R','R'));
$pdf->SetFont($font,'B',10);
$pdf->Row(array('TOTAL','Php '.number_format($totalamt, 2)));

$pdf->Ln(10);
$pdf->SetFont($font, 'B', 10);
$pdf->Cell(110, 0, 'Prepared by: ', 0, 0, 'L', 0, '', 0);
$pdf->Cell(15, 0, 'Noted by: ', 0, 0, 'L', 0, '', 0);
$pdf->Ln(16);
$pdf->SetFont($font, 'B', 9);
$pdf->Cell(110, 0, $clientsServed->getUser($preparedby,'fullname'), 0, 0, 'L', 0, '', 0);
$pdf->Cell(110, 0, $clientsServed->getUser($notedby,'fullname'), 0, 0, 'L', 0, '', 0);
$pdf->Ln(5);
$pdf->SetFont($font, '', 9);
$pdf->Cell(110, 0, $clientsServed->getUser($preparedby,'position'), 0, 0, 'L', 0, '', 0);
$pdf->Cell(110, 0, $clientsServed->getUser($notedby,'position'), 0, 0, 'L', 0, '', 0);

ob_end_clean();
$pdf->Output();
 
?>

