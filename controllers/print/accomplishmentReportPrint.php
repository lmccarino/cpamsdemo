<?php

require '../fpdf183/fpdf.php';
require 'fpdfextension.php';
require '../connect.php';
include '../clientsServedController.php';
setlocale(LC_CTYPE, 'en_US');

$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';

$from = date('F d, Y', strtotime($datefrom));
$to = date('F d, Y', strtotime($dateto));
$preparedby = $_GET['preparedby'];
$notedby = $_GET['notedby'];

$location_id = $_GET['location'];

$frm = date('M d, Y', strtotime($datefrom));
$t = date('M d, Y', strtotime($dateto));

$clientsServed = new ClientsServed();

$pdf=new FPDF_CellFit('P','mm','Letter');
$font = 'Arial';
$lineheight = 4;

$pdf->AddPage();
$pdf->SetTitle("Accomplishment Report");
$pdf->SetMargins(18, 13, 15);

$pdf->Ln();
$pdf->Image('../../images/davaocity-logo.jpg',14,6,25,25);
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
$pdf->Cell(0,$lineheight, "Accomplishment Report". ( (!empty($location)) ? " for $location":"" ), '', 0, 'C');
$pdf->Ln(5);
$pdf->SetFont($font, '', 11);
$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');


$totalnum = 0;
$totalamt = 0;
$totalnum2 = 0;
$totalamt2 = 0;
$totalnum3 = 0;
$totalamt3 = 0;

$pdf->Ln(10);
$pdf->SetFont($font, 'BU', 10);
$pdf->Cell(0,$lineheight, 'Lingap - Government Desk', 0, 0, 'L', 0, '', 0);
$pdf->Ln(5);

$pdf->SetFont($font,'B',10);
$pdf->SetWidths(array(62,63,63));
$pdf->SetAligns(array('C','C','C'));
$pdf->Row(array('Assistance Type','Clients', 'Expenditures'));

if ( (!empty($location)) ) {
	$location = "AND a.procloc = $location_id";
} else
	$location = "";

  $sql = "SELECT 
        CASE WHEN b.assistCode IS NOT NULL THEN b.assistCode ELSE a.assistCode END AS acode,  
        COUNT(a.idpatient) AS patients, 
        SUM(a.amtApproved) AS totalAmount
        FROM assistdetail AS a 
        LEFT JOIN assistsched AS b ON a.idassistsched = b.idassistsched
        LEFT JOIN office AS o ON a.provCode = o.officecode
        WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
        AND o.provcat = 'GOVERNMENT'
		AND a.status = 'APPROVED'
		$location
        AND (b.assistCode IS NOT NULL OR a.assistCode IS NOT NULL) GROUP BY acode";

$query = mysqli_query($conn, $sql); 
    while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
        $totalamt += $row['totalAmount'];
        $totalnum += $row['patients'];

        $pdf->SetFont($font,'',10);
        $pdf->SetAligns(array('L','R', 'R'));
        $pdf->Row(array($row['acode'], number_format($row['patients']) ,number_format($row['totalAmount'], 2)));
    }
    $pdf->SetFont($font,'B',10);
    $pdf->Row(array('TOTAL', number_format($totalnum),'Php '.number_format($totalamt, 2)));

    $pdf->Ln(5);
$pdf->SetFont($font, 'BU', 10);
$pdf->Cell(0,$lineheight, 'Lingap - Private Desk', 0, 0, 'L', 0, '', 0);
$pdf->Ln(5);

$pdf->SetFont($font,'B',10);
$pdf->SetWidths(array(62,63,63));
$pdf->SetAligns(array('C','C','C'));
$pdf->Row(array('Assistance Type','Clients', 'Expenditures'));

$sql2 = "SELECT 
        CASE WHEN b.assistCode IS NOT NULL THEN b.assistCode ELSE a.assistCode END AS acode,  
        COUNT(a.idpatient) AS patients, 
        SUM(a.amtApproved) AS totalAmount
        FROM assistdetail AS a 
        LEFT JOIN assistsched AS b ON a.idassistsched = b.idassistsched
        LEFT JOIN office AS o ON a.provCode = o.officecode
        WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
        AND o.provcat = 'PRIVATE'
		AND a.status = 'APPROVED'
		$location
        AND (b.assistCode IS NOT NULL OR a.assistCode IS NOT NULL) GROUP BY acode";

$query2 = mysqli_query($conn, $sql2); 
    while ($row2=mysqli_fetch_array($query2,MYSQLI_ASSOC)){
        $totalamt2 += $row2['totalAmount'];
        $totalnum2 += $row2['patients'];

        $pdf->SetFont('Arial','',10);
        $pdf->SetAligns(array('L','R', 'R'));
        $pdf->Row(array($row2['acode'], number_format($row2['patients']) ,number_format($row2['totalAmount'], 2)));
    }
    $pdf->SetFont($font,'B',10);
    $pdf->Row(array('TOTAL', number_format($totalnum2),'Php '.number_format($totalamt2, 2)));

$sql3 = "SELECT 
        CASE WHEN b.assistCode IS NOT NULL THEN b.assistCode ELSE a.assistCode END AS acode,  
        COUNT(a.idpatient) AS patients, 
        SUM(a.amtApproved) AS totalAmount
        FROM assistdetail AS a 
        LEFT JOIN assistsched AS b ON a.idassistsched = b.idassistsched
        LEFT JOIN office AS o ON a.provCode = o.officecode
        WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
        AND o.provcat IN ('GOVERNMENT', 'PRIVATE')
		AND a.status = 'APPROVED'
		$location
        AND (b.assistCode IS NOT NULL OR a.assistCode IS NOT NULL) GROUP BY acode";
$query3 = mysqli_query($conn, $sql3); 
while ($row3=mysqli_fetch_array($query3,MYSQLI_ASSOC)){
    $totalamt3 += $row3['totalAmount'];
    $totalnum3 += $row3['patients'];
}

$pdf->Ln(5);
$pdf->SetFont($font, 'B', 10);
$pdf->Cell(0,$lineheight, 'Total Number of Clients : '.number_format($totalnum3), 0, 0, 'L', 0, '', 0);

$pdf->Ln(5);
$pdf->Cell(0,$lineheight, 'Total Expenditures         : Php '.number_format($totalamt3, 2), 0, 0, 'L', 0, '', 0);

$pdf->Ln(25);
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


