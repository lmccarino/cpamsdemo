<?php

require '../fpdf183/fpdf.php';
require 'fpdfextension.php';
require '../connect.php';
include '../clientsServedController.php';
include '../convertEncoding.php';
setlocale(LC_CTYPE, 'en_US');

$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';
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
$pdf->SetTitle("List of Deceased Dialysis Patients");
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
$pdf->Cell(0,$lineheight, 'Lingap Para sa Mahirap', '', 0, 'C');
$pdf->Ln(10);
$pdf->SetFont($font, 'B', 11);
$pdf->Cell(0,$lineheight, 'List of Deceased Dialysis Patients', '', 0, 'C');
$pdf->Ln(5);
$pdf->SetFont($font, '', 10);
$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0,$lineheight, "Provider Category: ".$provcat, '', 0, 'C');

$pdf->Ln(5);
$count=0;

$pdf->SetWidths(array(20,80,40,50));
$pdf->SetAligns(array('C','C','C','C'));
$pdf->Row(array('#','Patient Name', 'Last Date Availed', 'Availed Assistance'));

$sql = "SELECT 
            a.idpatient, 
            CONCAT(patient.benLName, ', ', patient.benFName, ' ', patient.benMName) AS patientname
        FROM 
            assistdetail AS a 
            INNER JOIN patient ON a.idpatient = patient.idpatient 
            LEFT JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
			JOIN office as o on a.provCode = o.officecode
        WHERE 
            a.dateApproved BETWEEN '$datefrom' AND '$dateto' 
            AND (a.assistCode like 'DIALYSIS%' AND a.idpatient IN (SELECT idpatient FROM assistdetail WHERE assistCode like 'FUNERAL%' and dateApproved BETWEEN '$datefrom' AND '$dateto'))
            " . ($provcat == 'ALL' ? '' : "AND o.provcat = '$provcat'") . "
        GROUP BY 
            a.idpatient, 
            patient.benLName, 
            patient.benFName, 
            patient.benMName
        ORDER BY 
            patientname ASC";

$query = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
    $idpatient = $row['idpatient'];
    $patientname = $convertEncoding->convertEncode($row['patientname']);

    $sqldate = "SELECT dateApproved, assistCode
                FROM assistdetail
                WHERE idpatient = '$idpatient'
                ORDER BY dateApproved DESC
                LIMIT 1";

    $querydate = mysqli_query($conn, $sqldate);

    if ($rowdate = mysqli_fetch_array($querydate, MYSQLI_ASSOC)) {
        $dateApproved = date('m/d/Y', strtotime($rowdate['dateApproved']));
        $assistcode = $convertEncoding->convertEncode($rowdate['assistCode']);
    }

    $pdf->SetFont($font, '', 9);
    $pdf->SetAligns(array('L', 'L', 'C', 'L'));
    $pdf->Row(array(++$count, $patientname, $dateApproved ?? '', $assistcode ?? ''));
}


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

