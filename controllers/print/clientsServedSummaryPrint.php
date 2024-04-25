<?php

require '../fpdf183/fpdf.php';
require 'fpdfextension.php';
include '../clientsServedController.php';
setlocale(LC_CTYPE, 'en_US');

$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';

$from = date('M d, Y', strtotime($datefrom));
$to = date('M d, Y', strtotime($dateto));
$preparedby = $_GET['preparedby'];
$notedby = $_GET['notedby'];

$pdf=new FPDF_CellFit('P','mm','Letter');
$font = 'Arial';
$lineheight = 4;

$pdf->AddPage();
$pdf->SetTitle("Summary of Clients Served");
$pdf->SetMargins(18, 13, 15);

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
$pdf->Cell(0,$lineheight, "Summary of Clients Served", '', 0, 'C');
$pdf->Ln(5);
$pdf->SetFont($font, '', 10);
$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');

$pdf->Ln(10);
$count=0;

$pdf->SetFont($font, 'B', 11);
$pdf->SetWidths(array(94,94));
$pdf->SetAligns(array('C','C'));
$pdf->Row(array('LINGAP PRIVATE','GOVERNMENT'));
$pdf->SetWidths(array(47,47,47,47));
$pdf->SetAligns(array('C','C','C','C'));
$pdf->Row(array('CLIENTS SERVED','AMOUNT', 'CLIENTS SERVED','AMOUNT'));

$pdf->SetFont($font, '', 11);
$clientsServed = new ClientsServed();
$totalprivate = $clientsServed->getTotal('PRIVATE',$datefrom, $dateto, 'totalserved');
$totalprivamt = $clientsServed->getTotal('PRIVATE',$datefrom, $dateto, 'totalamount');
$totalgovt = $clientsServed->getTotal('GOVERNMENT',$datefrom, $dateto, 'totalserved');
$totalgovtamt = $clientsServed->getTotal('GOVERNMENT',$datefrom, $dateto, 'totalamount');

$pdf->SetAligns(array('C','C','C','C'));
$pdf->Row(array( number_format($totalprivate), number_format($totalprivamt, 2, '.', ','), number_format($totalgovt), number_format($totalgovtamt, 2, '.', ',')));

$pdf->Ln(15);
$pdf->SetFont($font, 'B', 11);
$pdf->SetWidths(array(94,94));
$pdf->SetAligns(array('C','C'));
$pdf->Row(array('ONLINE','WALK-IN'));
$pdf->SetWidths(array(47,47,47,47));
$pdf->SetAligns(array('C','C','C','C'));
$pdf->Row(array('CLIENTS SERVED','AMOUNT', 'CLIENTS SERVED','AMOUNT'));

$pdf->SetFont($font, '', 11);
$totalonline= $clientsServed->getPlatform('ONLINE',$datefrom, $dateto, 'totalserved');
$totalonlineamt = $clientsServed->getPlatform('ONLINE',$datefrom, $dateto, 'totalamount');
$totalwalkin = $clientsServed->getPlatform('WALK-IN',$datefrom, $dateto, 'totalserved');
$totalwalkinamt = $clientsServed->getPlatform('WALK-IN',$datefrom, $dateto, 'totalamount');

$pdf->SetAligns(array('C','C','C','C'));
$pdf->Row(array( number_format($totalonline), number_format($totalonlineamt, 2, '.', ','), number_format($totalwalkin), number_format($totalwalkinamt, 2, '.', ',')));

$pdf->Ln(15);
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

