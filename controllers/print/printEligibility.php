<?php
include '../numbertowords.php';
require_once('../tcpdf/tcpdf.php');

#require '../routines.php';
include '../printGLController.php';

class MYPDF extends tcpdf {

    //Page header
	public $fullname;
	
    public function Header() {
        // Logo	
        $image_file = '../../images/CSWDO.JPG';
        // Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
        $this->Image($image_file, 50, 30, 520, 0, 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);
		$this->Ln(150);$this->SetX(0);
		$this->SetFont ('times', 'BU', 12);
		$this->Cell(0, 0, 'CERTIFICATE OF ELIGIBILITY', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		//$this->Write(0, "INTAKE/INTERVIEW FORM", '', 0, 'L', true, 0, false, false, 0);
		//$this->Ln(1);$this->SetX(40);
		//$this->Write(0, $this->province, '', 0, 'L', true, 0, false, false, 0);
		
		
    }
	public function Footer() {
		$this->Ln();$this->SetX(40);
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
$pdf->SetTitle('CERTIFICATE OF ELIGIBILITY');
$pdf->SetSubject('CERTIFICATE OF ELIGIBILITY');
$pdf->SetKeywords('CERTIFICATE OF ELIGIBILITY');

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
$pdf->SetFont ('times', '', 11);
// ---------------------------------------------------------

$pdf->setFontSubsetting(false);
$myobj = validatetoken();
/*if (empty($myobj->userid)){
    header('Location:../../index.html?message='.urlencode("Invalid User"));
	die('security error');
} else {$userid = $myobj->userid;}
*/
$tk = $_REQUEST['tk'];


$tagvs = array(
 'p' => array(0 => array('n' => 1, 'h' => 0), 1 => array('n' => 1, 'h' => 0)),
 'li' => array(0 => array('n' => 1, 'h' => 0), 1 => array('n' => 1, 'h' => 0)),
 'ol' => array(0 => array('n' => 0, 'h' => 0), 1 => array('n' => 0, 'h' => 0)),
 'ul' => array(0 => array('n' => 0, 'h' => 0), 1 => array('n' => 0, 'h' => 0))
 
 );
$idassistdetails = $_REQUEST['idassistdetails'];;
$command = "select assistdetail.*, assistsched.assistCode from assistdetail left join assistsched on assistdetail.idassistsched = assistsched.idassistsched where idassistdetails = $idassistdetails";
$raf = getrow($command);
$n2words = new numbertowordconverter();
$awords = $n2words->numberTowords($raf['amtApproved']);

$command = "select intake.*, patient.*, users.fullname, distbrgy.brgyName from intake left join patient on intake.idpatient = patient.idpatient left join users on intake.userid = users.userid left join distbrgy on patient.brgyCode = distbrgy.brgyCode where idassistdetails = $idassistdetails";
$row = getrow($command);
$pdf->fullname = $row['fullname'];
$today = new DateTime();
$bdate = new DateTime();
$stamp = strtotime($row['benBDate']);
$bdate = $bdate->setTimestamp($stamp);
$age = $today->diff($bdate);

$gldetails = new PrintGL();
$details = $gldetails->getDetails($idassistdetails);

	
	$Date = $gldetails->getGLDate($details->data);
	$fromDate = new DateTime('now');
	$fromDate = date_format($fromDate->setTimestamp($Date),"m/d/Y");

$command = "select * from roles where name = 'CSSDOSIGNATORY' and active ='Y' limit 1";
$roles = getrow($command);
$idroles = $roles['idroles'];
$command = "select users.* from users left join rolesusers on users.userid = rolesusers.idusers where rolesusers.idroles = $idroles and users.active ='Y'";
$signatory = getrow($command);
$command = "select * from roles where name = 'GLSIGNATORY' and active ='Y' limit 1";
$roles2 = getrow($command);
$idroles2 = $roles2['idroles'];
$command = "select users.* from users left join rolesusers on users.userid = rolesusers.idusers where rolesusers.idroles = $idroles2 and users.active ='Y'";
$signatory2 = getrow($command);

$data ='<p></p><p align="justify">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify <u>'.$row['benFName']." ".$row['benMName']."  ".$row['benLName']."  ".$row['suffix']." </u> , ".$age->y." years old, residing at ";
$data .="<u>".$row['benAddrSt'].", BARANGAY ".$row['brgyName']."</u>, has been found eligible for <u>".$raf['assistCode']." BILL</u> Assistance after ";
$data .="interview and case study has been made.  Records of the case study report dated <u>".$fromDate."</u> are in the Confidential file of <u>Ugnayan</u> Section/District Office.</p>";
$data .='<p align ="justify">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'."Client is recommended for assistance in the amount of <u>$awords </u> (P ".number_format($raf['amtApproved'], 2).")";
$data .=" for the purpose of the payment of <u>".$raf['assistCode']." BILL</u> to be charged against the fund of the City Mayor's Office / City Social Services and Development Office of the City Mayor's Office and/or City Social Welfare & Development Office.";

$data .="<br/><br/>";


$data .="<p></p>";
$data .='<table><tr><td align="center"><u>'.$row['sworker'].'</u><br/>Social Worker</td>';
$data .='<td align="center"><u>'.$row['requestor']."</u><br/>Name/Signature of Requestor<br/>".$row['relation']."</td></tr></table>";
$data .='<p>Attested by:</p>';
 $pdf->setHtmlVSpace($tagvs);
	$pdf->AddPage('P','LETTER');
	//$pdf->AddPage('P','GOVERNMENTLEGAL');
	//$data = str_replace("<p>", '<p style ="height:0px">', $data);
	//die($data);
	//die(htmlspecialchars($data, ENT_QUOTES));
	//$pdf->Ln(5);
	//$pdf->Ln(19);
	$pdf->SetX(20);
	$pdf->Ln(3);
		
		$pdf->SetFont ('times', '', 11);
		
		$pdf->Ln(120);
	$data = '<div style="line-height:150%">'.$data.'</div>';
	$pdf->writeHTML($data, true, 0, true, true);
	$sigimage = $signatory['signature'];
	$sigimage2 = $signatory2['signature'];
	$sigfullpath = '../../signatures/'.$sigimage;
	$sigfullpath2 = '../../signatures/'.$sigimage2;
				$pdf->SetFont ('times', '', 11);
				$y = $pdf->GetY();
				
				$pdf->SetXY(145, $y+25);
				$ext =  strtoupper(pathinfo($sigfullpath, PATHINFO_EXTENSION));
				$pdf->Image($sigfullpath, '', '', 70, 0, $ext, '', '', true, 300);
				$x = $pdf->GetX();
				
				$pdf->SetXY($x+45, $y+17);
				$pdf->Image('../../images/logo.png', '', '', 25, 0, 'PNG', '', '', true, 300);
				$im = imagecreate(150, 90);
				$bg = imagecolorallocate($im, 255, 255, 255);
				$textcolor = imagecolorallocate($im, 0, 0, 0);
				$fullname=$signatory['fullname'];
				imagestring($im, 5, 0, 0, 'Digitally', $textcolor);
				imagestring($im, 5, 0, 14, 'stamped', $textcolor);
				imagestring($im, 2, 0, 28, "$fullname", $textcolor);
				imagestring($im, 5, 0, 42, date("Y/m/d", $Date), $textcolor);
				imagestring($im, 5, 0, 55, date("H:i:s", $Date), $textcolor);
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
				$pdf->SetXY(0, $y+50);
				$pdf->SetFont ('times', '', 11);
				$pdf->Ln(2);
				$y = $pdf->GetY();
				$data1 ='<table><tr><td align="center"><u><strong>'.$signatory['fullname'].'</strong></u><br/>Officer-In-Charge<br/>CSWDO</td>';
				$data1 .='<td align="center"><u><strong>SEBASTIAN Z.  DUTERTE</strong></u><br/>City Mayor</td></tr>';
				$data1 .='<tr><td></td><td>For the City Mayor</td></tr></table>';
				$pdf->writeHTML($data1, true, 0, true, true);
				
				$y = $pdf->GetY();
				$pdf->SetXY(350, $y+20);
				$ext =  strtoupper(pathinfo($sigfullpath2, PATHINFO_EXTENSION));
				$pdf->Image($sigfullpath2, '', '', 130, 0, $ext, '', '', true, 300);
				$x = $pdf->GetX();
				
				$pdf->SetXY($x+130, $y+17);
				$pdf->Image('../../images/logo.png', '', '', 25, 0, 'PNG', '', '', true, 300);
				$im = imagecreate(150, 90);
				$bg = imagecolorallocate($im, 255, 255, 255);
				$textcolor = imagecolorallocate($im, 0, 0, 0);
				$fullname=$signatory2['fullname'];
				imagestring($im, 5, 0, 0, 'Digitally', $textcolor);
				imagestring($im, 5, 0, 14, 'stamped', $textcolor);
				imagestring($im, 2, 0, 28, "$fullname", $textcolor);
				imagestring($im, 5, 0, 42, date("Y/m/d", $Date), $textcolor);
				imagestring($im, 5, 0, 55, date("H:i:s", $Date), $textcolor);
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
				$pdf->SetXY(0, $y+10);
				$pdf->SetFont ('times', '', 11);
				$pdf->Ln(2);
				
				$data2 ='<p></p><p></p><table><tr><td align="center"></td>';
				$data2 .='<td align="center"><u><strong>'.$signatory2['fullname'].'</strong></u><br/>Asst. City Administrator (Administration)</td></tr></table>';
				$pdf->writeHTML($data2, true, 0, true, true);
	$pdf->SetFont ('times', 'I', 8);
	$pdf->Ln(3);
	//$pdf->Cell(0, 0, "RAF#:".$raf['rafNum'], 0, 1, 'L', 0, '', 0);
	$bx = $pdf->GetX()+5;
	$y = $pdf->GetY()+5;
	$style = array('border' => false,'padding' => 0,'fgcolor' => array(0,0,0),'bgcolor' => false);
	//$pdf->write2DBarcode('https://cpams2.davaocity.gov.ph/verifyeligibility.php?idassistdetails='.$idassistdetails, 'QRCODE,M', $bx, $y, 60,60, $style, 'N');

	ob_end_clean();
	$pdf->Output('certEligibility.pdf', 'I');
//============================================================+
// END OF FILE
//============================================================+
?>
