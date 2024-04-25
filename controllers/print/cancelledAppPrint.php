<?php

require '../fpdf183/fpdf.php';
require 'fpdfextension.php';
require '../connect.php';
include '../clientsServedController.php';
include '../convertEncoding.php';

setlocale(LC_CTYPE, 'en_US');

$clientsServed = new ClientsServed();
$convertEncoding = new convertEncoding();

$dtefrom = $_REQUEST['datefrom'].' 00:00:00';
$dteto = $_REQUEST['dateto'].' 23:59:59';
$procloc = $_REQUEST['location'];
$preparedby = $_REQUEST['preparedby'];
$notedby = $_REQUEST['notedby'];

$from = date('F d, Y', strtotime($dtefrom));
$to = date('F d, Y', strtotime($dteto));

$sql = "SELECT officename FROM office WHERE idoffice = '$procloc'";
$query = mysqli_query($conn, $sql); 
while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)) {
	$code = $row['officename'];
}

$pdf=new FPDF_CellFit('L','mm','Letter');
$font = 'Arial';
$lineheight = 4;

$pdf->AddPage();
$pdf->SetTitle("Masterlist of Cancelled Assistance");
$pdf->SetMargins(9, 13, 9);

$pdf->Ln();
$pdf->Image('../../images/davaocity-logo.jpg',8,6,25,25);
$pdf->Image('../../images/lingap.jpg',245,6,25,28);

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
$pdf->Cell(0,$lineheight, "Masterlist of Cancelled Assistance", '', 0, 'C');
$pdf->Ln(5);
$pdf->SetFont($font, '', 9);
$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, "Processing Location: ".( (!empty($code)) ? " $code":"ALL" ), '', 0, 'C');
$pdf->Ln(8);
$count=0;

$pdf->SetWidths(array(11,17,46,70,25,18,42,35));
$pdf->SetAligns(array('C','C','C','C','C','C','C','C'));
$pdf->SetFont($font, 'B', 9);
$pdf->Row(array('#','RAF No.','Beneficiary', 'Provider', 'Assistance Type' ,'Date Cancelled', 'Cancelled by', 'Reason for Cancellation'));
$total = 0;

if ( (!empty($code)) ) {
	$code = "AND a.procloc = $procloc";
} else
	$code = "";
	
	$sql = "SELECT 
				a.rafNum, 
				CONCAT(p.benLName, ', ', p.benFName, ' ', LEFT(p.benMName, 1)) AS patientname,
				o.officename AS providerName, 
			CASE WHEN sc.assistCode IS NOT NULL 
				THEN sc.assistCode 
				ELSE a.assistCode 
			END AS assistCode, 
				a.dateCancel, 
				u.fullname,
				SUBSTRING(a.remarks, LOCATE('CANCELLED BY ', a.remarks) + LENGTH('CANCELLED BY ') + LENGTH(SUBSTRING_INDEX(SUBSTRING_INDEX(a.remarks, 'CANCELLED BY ', -1), ' ', 1)) + 1) AS reasonCancellation
			FROM assistdetail AS a 
			LEFT JOIN patient AS p ON a.idpatient = p.idpatient
			LEFT JOIN office AS o ON a.provCode = o.officecode
			LEFT JOIN users AS u ON SUBSTRING_INDEX(SUBSTRING_INDEX(a.remarks, 'CANCELLED BY ', -1), ' ', 1) = u.userid
			INNER JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
			WHERE a.dateCancel BETWEEN '$dtefrom' AND '$dteto' 
			AND (u.position <> 'DEV' AND u.position <> 'VIP' OR u.position IS NULL)
			AND a.status = 'CANCELLED' $code
			ORDER BY a.dateCancel DESC";

	$query = mysqli_query($conn, $sql); 

	while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)) 
	{ 
		$rafNum = $row['rafNum'];
		$patientname = $convertEncoding->convertEncode($row['patientname']);
		$officename = $row['providerName']??'';
		$assistCode = $row['assistCode'];
		$dateCancel = date('m/d/Y', strtotime($row['dateCancel']));
		$fullname = $convertEncoding->convertEncode($row['fullname']);
		$reasonCancellation = $convertEncoding->convertEncode($row['reasonCancellation']);

		$pdf->SetFont($font, '', 9);
		$pdf->SetAligns(array('C','L','L','L','L','R','L','L'));
		$pdf->Row(array(++$count, $rafNum, $patientname, $officename, $assistCode, $dateCancel, $fullname, $reasonCancellation));
	}

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