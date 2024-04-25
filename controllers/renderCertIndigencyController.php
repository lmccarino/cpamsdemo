<?php

require_once('tcpdf/tcpdf.php');
require 'routines.php';
class MYPDF extends tcpdf {

    //Page header
	public $fullname;
	
    public function Header() {
        // Logo
        $image_file = '../images/CSWDO.JPG';
        // Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
        $this->Image($image_file, 40, 30, 540, 0, 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);
		$this->Ln(150);$this->SetX(0);
		$this->SetFont ('times', 'BU', 12);
		$this->Cell(0, 0, 'CERTIFICATE OF INDIGENCY', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		//$this->Write(0, "INTAKE/INTERVIEW FORM", '', 0, 'L', true, 0, false, false, 0);
		//$this->Ln(1);$this->SetX(40);
		//$this->Write(0, $this->province, '', 0, 'L', true, 0, false, false, 0);
		
		
    }
	public function Footer() {
		$this->SetX(40);
		$this->SetY(-20);
		$this->SetFont ('times', 'I', 8);
		$this->Cell(0, 0, $this->fullname.'  '.date("m-d-Y H:i:s"), 0, false, 'L', 0, '', 0, false, 'M', 'M');
		
	}

    }
// initiate PDF
$pdf = new MYPDF();
$pdf->setPageUnit('pt');
//$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, 40);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('LINGAP');
$pdf->SetTitle('CERTIFICATE OF INDIGENCY');
$pdf->SetSubject('CERTIFICATE OF INDIGENCY');
$pdf->SetKeywords('CERTIFICATE OF INDIGENCY');

// set default header data
// set header and footer fonts

$pdf->setHeaderFont(Array('helvetica', '', 10));
//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// remove default header/footer
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);


// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(40, 40, 40);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}
$pdf->SetFont ('helvetica', '', 12);
// ---------------------------------------------------------

$pdf->setFontSubsetting(false);
$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User"));
	die('security error');
} else {$userid = $myobj->userid;}


$tagvs = array(
 'p' => array(0 => array('n' => 1, 'h' => 1), 1 => array('n' => 1, 'h' => 1)),
 'li' => array(0 => array('n' => 1, 'h' => 0), 1 => array('n' => 1, 'h' => 0)),
 'ol' => array(0 => array('n' => 0, 'h' => 0), 1 => array('n' => 0, 'h' => 0)),
 'ul' => array(0 => array('n' => 0, 'h' => 0), 1 => array('n' => 0, 'h' => 0))
 
 );
$idassistdetails = $_REQUEST['idassistdetails'];
$tk=$_REQUEST['tk'];
$command = "select assistdetail.*, assistsched.assistCode from assistdetail left join assistsched on assistdetail.idassistsched = assistsched.idassistsched where idassistdetails = $idassistdetails";
$raf = getrow($command);

$dateApproved = new DateTime();
$stamp1 = strtotime($raf['dateApproved']);
$dateApproved = $dateApproved->setTimestamp($stamp1);
$command = "select intake.*, patient.*, users.fullname, distbrgy.brgyName from intake left join patient on intake.idpatient = patient.idpatient left join users on intake.userid = users.userid left join distbrgy on patient.brgyCode = distbrgy.brgyCode where idassistdetails = $idassistdetails";
$row = getrow($command);
$pdf->fullname = $row['fullname'];
$today = new DateTime();
$bdate = new DateTime();
$stamp = strtotime($row['benBDate']);
$bdate = $bdate->setTimestamp($stamp);
$age = $today->diff($bdate);
$adependents = array();
$adependents = json_decode($row['details'],true);
 
if (empty($row['effectivitydate'])){
	$fromDate = date('m/d/Y');
	$toDate = date('m/d/Y', strtotime(' + 1 year'));
} else {
	$fromDate1 = date($row['effectivitydate']);
	$Date=date_create($row['effectivitydate']);
	$fromDate = date_format($Date,"m/d/Y");
	$toDate = date('m/d/Y', strtotime($fromDate1. ' + 1 years'));
}
$command = "select * from roles where name = 'CSSDOSIGNATORY' and active ='Y' limit 1";
$roles = getrow($command);
$idroles = $roles['idroles'];
$command = "select users.* from users left join rolesusers on users.userid = rolesusers.idusers where rolesusers.idroles = $idroles and users.active ='Y'";
$signatory = getrow($command);
$data = '<br/><br/><table><tr><td width="50%"></td><td width="50%" style="text-align:right"><small>Effective Date: from '.$fromDate.' to '.$toDate.'</small></td></tr></table>';
$data .="<p></p><p><strong>TO WHOM IT MAY CONCERN:</strong></p><p></p><p></p>";
$data .='<p align="justify">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify <u>'.$row['benFName']." ".$row['benMName']."  ".$row['benLName']."  ".$row['suffix']." </u> , ".$age->y." years old, married/single, and a ";
$data .="bonafide resident of <u>".$row['benAddrSt'].", BARANGAY ".$row['brgyName']."</u> Davao City Region 11, has been found to be indigent and eligible for government intervention.</p>";
$data .="</p>";
$data .='<p align ="justify">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This certification is issued upon request of the above mentioned client in relation to his/her desire to avail and be granted <strong>('.$raf['assistCode']." BILL )</strong>, under the office ";
$data .="of the City Mayor's Office and/or City Social Welfare & Development Office, all of Davao City.</p>";


$data .="<p>&nbsp;</p><p>Issued <u>".date_format($dateApproved,"jS")."</u> day of <u>".date_format($dateApproved,"F Y")."</u> at Davao City, Philippines.</p>";

$data .="<p></p><p></p>";
$data1 ='<table><tr><td align="center"><u>'.$row['sworker'].'</u><br/>Social Worker</td>';
$data1 .='<td align="center"><u>'.$signatory['fullname']."</u><br/>Officer-In-Charge<br/>CSWDO</td></tr></table>";
 $pdf->setHtmlVSpace($tagvs);
	//$pdf->AddPage('P','GOVERNMENTLEGAL');
	$pdf->AddPage('P','LETTER');
	//$data = str_replace("<p>", '<p style ="height:0px">', $data);
	//die($data);
	//die(htmlspecialchars($data, ENT_QUOTES));
	//$pdf->Ln(5);
	//$pdf->Ln(19);
	$pdf->SetX(20);
	$pdf->Ln(3);
		$pdf->SetFont ('times', 'B', 11);
		//$pdf->Cell(0, 0, 'ASSIGNMENT ORDER', 0, 1, 'C', 0, '', 1);
		$pdf->SetFont ('times', '', 11);
		$pdf->Ln(0);
		//$pdf->Write(0, '(Attestation and Recommendation)', '', 0, 'C', true, 0, false, false, 0);
		$pdf->Ln(120);
	$data = '<div style="line-height:150%">'.$data.'</div>';
	$pdf->writeHTML($data, true, 0, true, true);
		$sigimage = $signatory['signature'];
	$sigfullpath = '../signatures/'.$sigimage;
				$y = $pdf->GetY();
				
				$pdf->SetXY(410, $y+22);
				$ext =  strtoupper(pathinfo($sigfullpath, PATHINFO_EXTENSION));
				$pdf->Image($sigfullpath, '', '', 70, 0, $ext, '', '', true, 300);
				
				$x = $pdf->GetX();
				$pdf->SetXY($x+45, $y+17);
				$pdf->Image('../images/logo.png', '', '', 25, 0, 'PNG', '', '', true, 300);
				$im = imagecreate(150, 90);
				$bg = imagecolorallocate($im, 255, 255, 255);
				$textcolor = imagecolorallocate($im, 0, 0, 0);
				$fullname=$signatory['fullname'];
				imagestring($im, 5, 0, 0, 'Digitally', $textcolor);
				imagestring($im, 5, 0, 14, 'stamped', $textcolor);
				imagestring($im, 2, 0, 28, "$fullname", $textcolor);
				imagestring($im, 5, 0, 42, date("Y/m/d"), $textcolor);
				imagestring($im, 5, 0, 55, date("H:i:s"), $textcolor);
				imagestring($im, 5, 0, 68, 'UTC+08:00', $textcolor);
				ob_start();
				$img = imagepng($im);
				$imgdata = ob_get_contents();
				ob_end_clean();
				$x = $pdf->GetX();
				$x = $x + 26;
				$pdf->SetXY($x, $y+10);
				$pdf->Image('@'.$imgdata,'','',75,0,'PNG','','',true, 300);
				imagedestroy($im);
				$pdf->SetXY(40, $y+50);
		$pdf->writeHTML($data1, true, 0, true, true);
	$pdf->SetFont ('times', 'I', 8);
	$pdf->Ln(3);
	//$pdf->Cell(0, 0, "RAF#:".$raf['rafNum'], 0, 1, 'L', 0, '', 0);
	$bx = $pdf->GetX()+5;
	$y = $pdf->GetY()+5;
	$style = array('border' => false,'padding' => 0,'fgcolor' => array(0,0,0),'bgcolor' => false);
	//$pdf->write2DBarcode('https://cpams2.davaocity.gov.ph/verifycertindigency.php?idassistdetails='.$idassistdetails, 'QRCODE,M', $bx, $y, 60,60, $style, 'N');

		//$pdf->Output('certIndigency.pdf', 'I');

	$stamp = getdate();
	$fname = $idassistdetails."_".$stamp['year'].$stamp['mon'].$stamp['mday'].$stamp['hours'].$stamp['minutes'].$stamp['seconds'].".pdf";
	if (stristr(PHP_OS, 'WIN')) {
			$fullpath = realpath('')."\\certindigency\\".$fname;
	} else {
		$fullpath = realpath('')."/certindigency/".$fname;
	}
		// Removed By: Teddy C. 09/14/2023 09:03.
		// $pdf->Output($fullpath,'F');
		// $command ="CALL savecertindigency($idassistdetails, '$fname',$userid, '$tk')";

		// $row = getrow($command);
		// //$fileinfo = pathinfo($fullpath);
		// //header('Content-Type: application/pdf');
		// //header('Content-Length: ' . filesize($fullpath));
		// //readfile($fullpath);
		// End Teddy C.

		

	// Author: Teddy C. 09/14/2023 09:03.
	// Generate the PDF and store it in a variable
	include 'utilityController.php';
	
	$filestring = $pdf->Output($filename . '.pdf', 'S');

	// Create a temporary file and store the generated PDF
	$tempfile = tmpfile();
	fwrite($tempfile, $filestring);
	fseek($tempfile, 0);
	$tempfileUri = stream_get_meta_data($tempfile)['uri'];

	// Upload the temporary file to the server
	$result = Utility::uploadToServer($tempfileUri, 'indigency', 'generatedpdf');

	// Close and delete the temporary file
	fclose($tempfile);

	if($result['success'] == true){
		$generatedfilename = $result['message'];
		$command ="CALL savecertindigency($idassistdetails, '$generatedfilename',$userid, '$tk')";
		$row = getrow($command);
	}
	// End Teddy C.
		
	

//============================================================+
// END OF FILE
//============================================================+
?>
