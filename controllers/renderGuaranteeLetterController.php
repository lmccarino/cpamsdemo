<?php

require_once('tcpdf/tcpdf.php');

include 'printGLController.php';
include 'numbertowords.php';

$id = $_REQUEST['idassistdetails'];

class MYPDF extends tcpdf {   

    public function Header() {    		
        $image_file = '../images/cmo-header.png';
        $this->Image($image_file, 0, 0, 0, 0, 'PNG', '', 'T', false, 300, 'C', false, false, 0, false, false, false);   
    } 

    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, $this->CustomFooterText, 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}


$pdf = new MYPDF('P', 'mm', array(215.9, 279.4), true, 'UTF-8', false);

$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, 40);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('CPAMS');
$pdf->SetTitle('Guarantee Letter');
$pdf->SetSubject('Guarantee Letter');

$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);

$pdf->AddPage();

$gldetails = new PrintGL();
$details = $gldetails->getDetails($id);

$numtowords = new numbertowordconverter();

foreach ($details as $row) {  
    $documentdate = (isset($row['dateReissue'])) ? date('F d, Y', strtotime($row['dateReissue'])) : date('F d, Y', strtotime($row['dateApproved']));

    $pdf->Ln(35);
    $pdf->SetFont('times', 'B', 11);    
    $pdf->Write(0, $documentdate, '', 0, 'L', true, 0, false, false, 0);
    // $pdf->Write(0, date('F d, Y', strtotime($row['dateApproved'])), '', 0, 'L', true, 0, false, false, 0);

    $pdf->Ln(7);
    $pdf->Write(0, $gldetails->getOfficer($row['provCode'], 'contactperson'), '', 0, 'L', true, 0, false, false, 0);
    $pdf->SetFont('times', '', 11);
    if ($row['provCode'] == 'SPMC')
	$pdf->Write(0, $gldetails->getOfficer($row['provCode'], 'position'), '', 0, 'L', true, 0, false, false, 0); 
    $pdf->Write(0, $gldetails->getOfficer($row['provCode'], 'provider'), '', 0, 'L', true, 0, false, false, 0);
    $pdf->Write(0, $gldetails->getOfficer($row['provCode'], 'location'), '', 0, 'L', true, 0, false, false, 0);

    $pdf->Ln(8);
    $pdf->Write(0, 'Dear Sir/Madam:', '', 0, 'L', true, 0, false, false, 0);
    $pdf->Ln(10);
    // $pdf->Cell(25,5,'','',0,'L');

    $brgycode = $gldetails->getPatient($row['idpatient'], 'barangay');
   
    $texthtml='<p style="text-indent:50px; line-height: 1.5; ">
            Please be informed that the '.
            $gldetails->getAssistCode($row['idassistsched'], 'code').' bill of <b>'.
            $gldetails->getPatient($row['idpatient'], 'name').'</b>, resident of Brgy. '.
            $gldetails->getBarangay($brgycode).', Davao City in the amount of <b>'.
            $numtowords->numberTowords($row['amtApproved']).'  (PhP '.number_format($row['amtApproved'],2).')</b> only,
            has been approved by the City Mayor to be charged against the City Government of Davao. </p>';

    $pdf->writeHTML($texthtml, true, false, true, false, 'L');

    $pdf->Ln(7);
    $pdf->Write(0, 'Please call us at 233 - 4961 or 0909-547-4763 if you have any additional questions or need more information.', '', 0, 'L', true, 0, false, false, 0);
	
	if ($gldetails->getAssistCode($row['idassistsched'], 'code') == 'MEDICINE') {
		$medz = $gldetails->getMedicines($row['idassistdetails']);
		$pdf->Ln(4);
		$pdf->SetFont('helvetica', '', 8);
		$texthtml='<p style="line-height: 1.4;"><b><u>Pharmacy</u></b></p>';
		
		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		$total = 0;
		$pdf->writeHTML($texthtml, true, false, true, false, 'L');
		for ($i = 0; $i < count($medz); $i++) {
			$pdf->MultiCell(50, 0, $medz[$i]['pharmaname'],              0, 'L', 0, 0, '', '', true, 0, false, true, 0);
			$pdf->MultiCell(40, 0, number_format($medz[$i]['amount'],2), 0, 'R', 0, 1, '', '', true, 0, false, true, 0);
			$total += $medz[$i]['amount'];
		}
		$pdf->SetFont('helvetica', 'B', 8);
		$pdf->MultiCell(70, 0, 'TOTAL',                    0, 'R', 0, 0, '', '', true, 0, false, true, 0);
		$pdf->MultiCell(20, 0, number_format($total, 2), 'T', 'R', 0, 1, '', '', true, 0, false, true, 0);
		
		for ($i = 4 - count($medz); $i > 0; $i--)
		$pdf->MultiCell(50, 0, '', 0, 'L', 0, 0, '', '', true, 0, false, true, 0);
	}
	
	if ($gldetails->getAssistCode($row['idassistsched'], 'code') != 'MEDICINE') $pdf->Ln(5);
    $pdf->Ln(2);
	$pdf->SetFont('times', '', 11);
    $pdf->Write(0, 'Thank you and God Bless.', '', 0, 'L', true, 0, false, false, 0);

	if ($gldetails->getAssistCode($row['idassistsched'], 'code') != 'MEDICINE') $pdf->Ln(5);
    $pdf->Ln(2);
    $pdf->SetFont('times', 'B', 9);
    $pdf->Write(0, '*This Guarantee Letter is valid for only five (5) working days upon issuance.', '', 0, 'L', true, 0, false, false, 0);

    $pdf->Ln(13);
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(100,5,'','',0,'L');
    $pdf->Cell(25,5,'Very truly yours,','',0,'L');
    $pdf->Ln(10);
    $pdf->Cell(100,5,'','',0,'L');
    $pdf->Cell(25,5,'For the City Mayor:','',0,'L');

    $setX = $pdf->GetX();
    $setY = $pdf->GetY();
    $setY = $setY + 10;

    // SIGNATORY CHANGES
    // FROM
    // $sigfullpath = '../images/esparcia_sign.png';
    // TO
    $sigfullpath = '../images/jpg_domingo_sign.jpg';
    // END CHANGES
    $ext =  strtoupper(pathinfo($sigfullpath, PATHINFO_EXTENSION));

    // SIGNATORY CHANGES
    // FROM
    $pdf->Image($sigfullpath, 130, $setY, 45, 8, $ext, '', '', false, 300, '', false, false, 0, false, false, false);   
    // TO
    // $pdf->Image($sigfullpath, 143, $setY-5, 20, 20, $ext, '', '', false, 300, '', false, false, 0, false, false, false);   
    // END CHANGES

    $pdf->Image('../images/davaocity-logo.jpg', 180, $setY, 8, 8, 'JPG', '', '', false, 300, '', false, false, 0, false, false, false); 
    $pdf->SetFont('times', 'B', 5);
    $pdf->SetY($setY-2);    
    $pdf->SetX(188);
    $pdf->Cell(15,5, "DIGITALLY",'', 0, 'L');
    $pdf->Ln(2);                
    $pdf->SetX(188);
    $pdf->Cell(10,5, "STAMPED",'', 0, 'L');
    $pdf->Ln(2);    
    $pdf->SetX(188);
    $pdf->Cell(10,5, date('m/d/Y', strtotime($row['dateApproved'])),'', 0, 'L');
    $pdf->Ln(2);
    $pdf->SetX(188);
    $pdf->Cell(10,5, date('H:i:s', strtotime($row['dateApproved'])),'', 0, 'L');


    $setX = $pdf->GetX();
    $setY = $pdf->GetY() + 5;
    // $pdf->SetXY($setX, $setY);
    $pdf->SetY($setY);
    $pdf->SetFont('times', 'B', 11);
    $pdf->Cell(100,5,'','',0,'L');

    // SIGNATORY CHANGES
    // FROM
    $pdf->Cell(25,5,$gldetails->getSignatory(1, 'name'),'',0,'L');
    $pdf->Ln();
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(105,5,'','',0,'L');
    $pdf->Cell(25,5,$gldetails->getSignatory(1, 'position'),'',0,'L');
    // TO
    // $pdf->Cell(25,5,'ATTY. JANIS LOUIS H. ESPARCIA','',0,'L');
    // $pdf->Ln();
    // $pdf->SetFont('times', '', 11);
    // $pdf->Cell(100,5,'','',0,'L');
    // $pdf->Cell(25,5,'Asst. City Administrator (Operations)','',0,'L');
    // END SIGNATORY CHANGES

	if ($gldetails->getAssistCode($row['idassistsched'], 'code') != 'MEDICINE') 
	$pdf->Ln(9);
    $setX = $pdf->GetX();
    $setY = $pdf->GetY() - 30;
    $style = array('border' => false,'padding' => 0,'fgcolor' => array(0,0,0),'bgcolor' => false);
    //$pdf->write2DBarcode('https://cpams2.davaocity.gov.ph/verifyqr.php?id='.$row['idassistdetails'], 'QRCODE,M', $setX, $setY, 25, 20, $style, 'N');
    $qrData = [
		strtolower($gldetails->getPatient($row['idpatient'], 'name')),
		strtolower($gldetails->getAssistCode($row['idassistsched'], 'code')),
		number_format($row['amtApproved'],2),
		date('M d, Y', strtotime($row['dateApproved']. ' + 4 days'))
	];
    $qrText = implode("\n", $qrData); //var_dump($qrText); die();
	if ($gldetails->getAssistCode($row['idassistsched'], 'code') != 'MEDICINE') {
		//$pdf->write2DBarcode($qrText, 'QRCODE,H', $setX, $setY, 30, 25, $style, 'N');
		$pdf->write2DBarcode('https://cpams2.davaocity.gov.ph/verifyqr.php?id='.$row['idassistdetails'], 'QRCODE,H', $setX, $setY, 30, 25, $style, 'N'); }
	else {
		//$pdf->write2DBarcode($qrText, 'QRCODE,H',     15, $setY, 30, 25, $style, 'N');
		$pdf->write2DBarcode('https://cpams2.davaocity.gov.ph/verifyqr.php?id='.$row['idassistdetails'], 'QRCODE,H', 15, $setY, 30, 25, $style, 'N'); }
    $pdf->Ln(1);
    // $pdf->Write(0, 'RAF#', '', 0, 'L', true, 0, false, false, 0);
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(10,5,'RAF #','',0,'L');
    $pdf->SetFont('times', 'B', 11);
    $pdf->Cell(20,5,$row['rafNum'],'',0,'L');
    
    // Revised By: Teddy C. 09/21/2023 15:49.
    // $pdf->Ln(8);
    // $pdf->SetFont('times', 'B', 7);
    // $pdf->Cell(10,5,'DO NOT ACCEPT WITHOUT SEAL.','',0,'L');
    //
	if ( $gldetails->isReissued($row) )
        $pdf->Ln(6);
    else
        $pdf->Ln(8);

    $pdf->SetFont('times', 'B', 7);
    $pdf->Cell(10,5,'DO NOT ACCEPT WITHOUT SEAL.','',0,'L');

    if ( $gldetails->isReissued($row) ) {
        $pdf->Ln(5);
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(10,5, $gldetails->getReissueMsg($row),'',0,'L');
    }
    // End Teddy C.

    $pdf->CustomFooterText = $gldetails->getUser($row['userID'])." @ ".date('m/d/Y H:i:s'); 

    $raffile = $row['rafNum'];    
    $stamp = getdate();
    $filestamp =  $stamp['year'].$stamp['mon'].$stamp['mday'].$stamp['hours'].$stamp['minutes'].$stamp['seconds'];
    $filename = $row['idassistdetails'].'_'.$filestamp.'.pdf';

    // $gldetails->updatefilename($row['idassistdetails'], $filename);
}

ob_end_clean();
// Removed by: Teddy C. 09/13/2023 15:23.
// if (stristr(PHP_OS, 'WIN')) {
// 		$fullpath = realpath('')."\\guaranteeletter\\".$filename;
// } else {
// 	$fullpath = realpath('')."/guaranteeletter/".$filename;
// }

// $pdf->Output($fullpath,'I');
// $command = "CALL saveglform($id, '$filename')";
// $row = getrow($command);
// End Removed.

// Author: Teddy C. 09/13/2023 15:23.
// Generate the PDF and store it in a variable
include 'utilityController.php';

$filestring = $pdf->Output($filename . '.pdf', 'S');

// Create a temporary file and store the generated PDF
$tempfile = tmpfile();
fwrite($tempfile, $filestring);
fseek($tempfile, 0);
$tempfileUri = stream_get_meta_data($tempfile)['uri'];

// Upload the temporary file to the server
$result = Utility::uploadToServer($tempfileUri, 'guarantee', 'generatedpdf');

// Close and delete the temporary file
fclose($tempfile);

if($result['success'] == true){
    $generatedfilename = $result['message'];
    $command = "CALL saveglform($id, '$generatedfilename')";
    $row = getrow($command);
}
// End Teddy C.
?>