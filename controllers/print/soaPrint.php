<?php

require '../fpdf183/fpdf.php';
require 'fpdfextension.php';
require '../connect.php';
include '../clientsServedController.php';
include '../convertEncoding.php';

setlocale(LC_CTYPE, 'en_US');

$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';
$provider = $_GET['loc'];
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
$pdf->SetTitle("Statement of Account");
$pdf->SetMargins(10, 13, 10);

$pdf->Ln();
$pdf->Image('../../images/spmc.jpg',14,6,25,25);


$sqlprov = "SELECT * from office where officecode = '$provider'";
$queryprov = mysqli_query($conn, $sqlprov); 

while ($rowprov=mysqli_fetch_array($queryprov,MYSQLI_ASSOC)){

// ========================================
// =                HEADER                =
// ========================================
$pdf->SetFont($font, '', 11);
$pdf->Cell(0,$lineheight, $rowprov['officename'], '', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, $rowprov['location'], '', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, $rowprov['contactno'].' '.$rowprov['emailaddress'], '', 0, 'C');
$pdf->Ln(10);
$pdf->SetFont($font, 'B', 11);
$pdf->Cell(0,$lineheight, "Statement of Account Summary", '', 0, 'C');
$pdf->Ln(5);
$pdf->SetFont($font, '', 10);
$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');
}

$pdf->Ln(5);
$count=0;

$sql = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(CASE WHEN b.assistCode IS NOT NULL THEN concat(b.assistCode, CASE WHEN TRIM(b.assistDesc) != '' THEN concat ('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END) as assistCode, a.provCode, concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname 
		FROM assistdetail as a 
		LEFT JOIN assistsched as b on a.idassistsched = b.idassistsched 
		LEFT JOIN patient on a.idpatient = patient.idpatient
		where a.dateGLReceive >= '$datefrom' and a.dateGLReceive <= '$dateto' and a.provCode = '$provider' AND a.status = 'APPROVED' AND !isnull(a.dateGLReceive) 
		ORDER BY a.dateGLReceive DESC";
$query = mysqli_query($conn, $sql);
		$pdf->Ln(10);
		$pdf->SetFont($font, 'B', 9);
		$pdf->Cell(0,$lineheight, 'Customer Name: CITY GOVERNMENT OF DAVAO ', 0, 0, 'L', 0, '', 0);
		$pdf->Ln(5);
		$pdf->SetWidths(array(15,60,52,22,25,22));
		$pdf->SetAligns(array('C','C','C','C','C','C'));
		$pdf->Row(array('#','Patient','Assistance Type','RAF No.', 'Date Approved', 'Amount Approved'));
	
	$totalamount = 0;
	$totalamt = 0;
	$count=0;

	while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
		$rafNum =   $row['rafNum'];
		$idpatient =$row['idpatient'];
		$patientname = $convertEncoding->convertEncode($row['patientname']);
		$assistCode =$row['assistCode'];
		$dateApproved =date('m/d/Y', strtotime($row['dateApproved']));
		$amtApproved = number_format((float)$row['amtApproved'], 2, '.', ',');
		$totalamt += $row['amtApproved'];
		$totalamount =  number_format((float)$totalamt, 2, '.', ',');
		$pdf->SetFont($font,'',9);
        $pdf->SetAligns(array('L','L','L','L','R','R'));
        $pdf->Row(array(++$count, $patientname,$assistCode,$rafNum,$dateApproved,$amtApproved));
	}
	 $pdf->SetWidths(array(149,47));
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

