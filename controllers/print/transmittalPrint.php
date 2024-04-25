<?php

    require_once('../fpdf183/fpdf.php');
    require_once('../fpdf183/fpdfextension.php');   

    require 'printTransmittalsController.php';

    $tk = $_REQUEST['tk'];

    $myobj = validatetoken();
    if (empty($myobj->userid)){
        header('Location:../index.html?message='.urlencode("Invalid User"));
        die('security error');
    } else {$userid = $myobj->userid;}

    if(!(isset($_REQUEST['id']) && $_REQUEST['id'] > 0))
        die("INVALID TRANSMITTAL ID");

    $transmittals = new PrintTransmittals();
    $transmittal = $transmittals->get($_REQUEST['id']);
    $rafs = $transmittals->getRAFs($_REQUEST['id']);

    $pdf = new FPDF_CellFit('P', 'mm', array(215.9,279.4));
    $pdf->AddPage();

    $pdf->SetTitle("Certificates");
    $pdf->SetMargins(10, 5, 15);
    setlocale(LC_CTYPE, 'en_US');

    $pdf->Image('../../images/davaocitylogo.png',9,5,23,23);
	$pdf->Image('../../images/lingap.png',180,5,23,27.5);


    $pdf->Ln(); 
    $pdf->SetFont($font, '', 10);
    $pdf->Cell(0,5, 'Republic of the Philippines', '', 0, 'C');
    $pdf->Ln(5);		
    $pdf->Cell(0,5, 'Office of the City Mayor', '', 0, 'C');
    $pdf->Ln(6);	
    $pdf->SetFont($font, '', 11);	
    $pdf->Cell(0,5, 'Lingap Para sa Mahirap', '', 0, 'C');

    $pdf->Ln(15);
    $pdf->SetFont($font, 'B', 11);	
    $pdf->Cell(0,5, 'LIST OF LINGAP BENEFICIARIES', '', 0, 'C');

    $pdf->Ln();
    $pdf->SetFont($font, '', 11);	
    $pdf->Cell(0,5, 'Transmittal # ' . $transmittal['idtransmittals'], '', 0, 'C');

    $pdf->Ln(12);
    $pdf->SetFont($font, '', 11);	
    $pdf->Cell(18,5, 'SOA No.: ', 0, 0, 'L');
    $pdf->SetFont($font, 'B', 11);	
    $pdf->Cell(143,5, $transmittal['soa'], 0, 0, 'L');
    $pdf->SetFont($font, '', 11);	
    $pdf->Cell(12,5, 'Date: ', 0, 0, 'L');
    $pdf->SetFont($font, 'B', 11);	
    $pdf->Cell(22,5, date('m/d/Y', strtotime($transmittal['created'])), 0, 0, 'L');

    $pdf->Ln();
    $pdf->SetFont($font, '', 11);	
    $pdf->Cell(18,5, 'Provider : ', 0, 0, 'L');
    $pdf->SetFont($font, 'B', 11);	
    $pdf->MultiCell(177,5, $transmittal['providerName'], 0, 'L');

    $pdf->Ln(2);
    $pdf->SetFont($font, 'B', 9);	
    $pdf->Cell(15,5, 'NO.', 1, 0, 'C');
    $pdf->Cell(90,5, 'NAME', 1, 0, 'L');
    $pdf->Cell(25,5, 'RAF NO.', 1, 0, 'C');
    $pdf->Cell(25,5, 'GL DATE', 1, 0, 'C');
    $pdf->Cell(40,5, 'AMOUNT', 1, 0, 'C');

    $count = 1;
    $total = 0;

    $pdf->SetAutoPageBreak(true, 5);

    $pdf->Ln();   

    foreach($rafs as $raf){
        $total += $raf['amtApproved'];

        // $pdf->Ln();    
        // $pdf->SetFont($font, '', 9);	
        // $pdf->Cell(15,5, ($count++) . '.', 1, 0, 'R');
        // $pdf->Cell(90,5, ($raf['benLName'] . ', ' . $raf['benFName'] . $raf['benMName'] . $raf['suffix']), 1, 0, 'L');
        // $pdf->Cell(25,5, $raf['rafNum'], 1, 0, 'C');
        // $pdf->Cell(25,5, date('m/d/Y', strtotime($raf['dateApproved'])), 1, 0, 'C');
        // $pdf->Cell(40,5, 'Php ' . number_format($raf['amtApproved'], 2), 1, 0, 'R');

        $pdf->SetFont($font, '', 9);   
        $pdf->SetWidths(array(15,90,25,25,40));
        $pdf->SetAligns(array('C','L','C','C','R'));
        $rafname = utf8_decode(($raf['benLName'] . ', ' . $raf['benFName'] .' '. $raf['benMName'] .' '. $raf['suffix']));
        $pdf->Row(array(($count++) . '.', $rafname, $raf['rafNum'], date('m/d/Y', strtotime($raf['dateApproved'])), 'Php ' . number_format($raf['amtApproved'], 2)));
    }

      
    $pdf->SetFont($font, 'B', 9);	
    $pdf->Cell(155,5, 'TOTAL', 1, 0, 'R');
    $pdf->Cell(40,5, 'Php ' . number_format($total, 2), 1, 0, 'R');
  
    $pdf->Ln(20);  

    $pdf->SetFont($font, '', 9);	
    $pdf->Cell(45,5, 'Prepared By: ', 0, 0, 'L');
    $pdf->Cell(90,5, '', 0, 0, 'L');
    $pdf->Cell(45,5, 'Received By: ', 0, 0, 'L');

    $user = $transmittals->getUser($userid);


    $pdf->Ln(16);  
    $pdf->SetFont($font, 'B', 10);	
    $pdf->Cell(45,5, $user, 0, 0, 'C');
    $pdf->Cell(90,5, '', 0, 0, 'L');
    $pdf->Cell(45,5, '', 'B', 0, 'C');
    $pdf->Ln();  
    $pdf->SetFont($font, '', 7);	
    $pdf->Cell(45,2, date('m/d/Y H:i:s'), 0, 0, 'C');
    $pdf->Cell(90,5, '', 0, 0, 'L');
    $pdf->Cell(8,5, 'Date: ', 0, 0, 'L');
    $pdf->Cell(37,5, '', 'B', 0, 'L');

    // $footertext =  $transmittals->getUser($userid)." @ ".date('m/d/Y H:i:s');
    // $pdf->SetFont($font, 'I', 9); 
    // $pdf->FooterCertificate($footertext);


    ob_end_clean();

    $pdf->Output();