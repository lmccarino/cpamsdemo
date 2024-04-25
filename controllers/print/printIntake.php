<?php

require_once('../tcpdf/tcpdf.php');
require '../routines.php';
class MYPDF extends tcpdf {

    //Page header
	public $fullname;
	
	
    public function Header() {
        // Logo
        $image_file = '../../images/CSWDO.JPG';
        // Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
        $this->Image($image_file, 10, 10, 190, 0, 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);
		$this->Ln(54);$this->SetX(0);
		$this->SetFont ('times', 'B', 12);
		$this->Cell(0, 0, 'INTAKE/INTERVIEW FORM', 0, false, 'C', 0, '', 0, false, 'M', 'M');
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

$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, 40);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('LINGAP');
$pdf->SetTitle('INTAKE FORM');
$pdf->SetSubject('INTAKE FORM LINGAP');
$pdf->SetKeywords('INTAKE FORM LINGAP');

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
$pdf->SetMargins(PDF_MARGIN_LEFT, 35, PDF_MARGIN_RIGHT);
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
$pdf->SetFont ('times', '', 12);
// ---------------------------------------------------------

$pdf->setFontSubsetting(false);
$myobj = validatetoken();
/*if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User"));
	die('security error');
} else {$userid = $myobj->userid;}
*/
$tk = $_REQUEST['tk'];


$tagvs = array(
 'p' => array(0 => array('n' => 1, 'h' => 1), 1 => array('n' => 1, 'h' => 1)),
 'li' => array(0 => array('n' => 1, 'h' => 0), 1 => array('n' => 1, 'h' => 0)),
 'ol' => array(0 => array('n' => 0, 'h' => 0), 1 => array('n' => 0, 'h' => 0)),
 'ul' => array(0 => array('n' => 0, 'h' => 0), 1 => array('n' => 0, 'h' => 0))
 
 );
$idassistdetails = $_REQUEST['idassistdetails'];;
$command = "select * from assistdetail where idassistdetails = $idassistdetails";
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
$data = '<br/><br/><table><tr><td width="80%"></td><td width="20%" style="text-align:center">'.date_format($dateApproved,"F d Y").'<br/></td></tr></table>';
$data .="<p></p><p></p><table>";
$data .='<tr><td width="80%">Name of Client: <u>'.$row['benFName'].' '.$row['benMName'].' '.$row['benLName'].' '.$row['suffix'].'</u></td><td align="right" width="80%">Age: <u>'.$age->y.'</u></td></tr>';
$data .='<tr><td width="80%">Addresss: <u>'.$row['benAddrSt'].', BARANGAY '.$row['brgyName'].'</u></td><td></td></tr>'; 
$data .= '</table><p></p><p></p>';
$data .='<table><tr><td><u>Dependents</u></td><td><u>Relation to Head</u></td><td align = "right"><u>Age</u></td></tr>';
for($i = 0; $i < count($adependents); $i++) {
	$dependent = $adependents[$i];
    $data .='<tr><td>'.$dependent['depName'].'</td><td>'.$dependent['depRelation'].'</td><td align = "right">'.$dependent['depAge'].'</td></tr>';
}
$data .="</table>";
$data .="<br/><br/><br/><br/>";
$data .="<p><strong>REMARKS:</strong></p>";
$data .="<p>".$row['remarks']."</p>";
$data .="<p></p><p></p><p></p><p></p>";
$data .='<table><tr><td align="center"><u>'.$row['sworker'].'</u><br/>Social Worker</td><td align="center"><u>'.$row['requestor']."</u><br/>Requestor<br/>".$row['relation']."</td></tr></table>";
 $pdf->setHtmlVSpace($tagvs);
	//$pdf->AddPage('P','GOVERNMENTLEGAL');
	$pdf->AddPage('P','LETTER');
	//$data = str_replace("<p>", '<p style ="height:0px">', $data);
	//die($data);
	//die(htmlspecialchars($data, ENT_QUOTES));
	//$pdf->Ln(5);
	//$pdf->Ln(19);
	$pdf->Ln(3);
		$pdf->SetFont ('times', 'B', 11);
		//$pdf->Cell(0, 0, 'ASSIGNMENT ORDER', 0, 1, 'C', 0, '', 1);
		$pdf->SetFont ('times', '', 11);
		$pdf->Ln(0);
		//$pdf->Write(0, '(Attestation and Recommendation)', '', 0, 'C', true, 0, false, false, 0);
		$pdf->Ln(25);
	$pdf->writeHTML($data, true, 0, true, true);
	$pdf->SetFont ('times', 'I', 8);
	$pdf->Ln(3);
	//$pdf->Cell(0, 0, "RAF#:".$raf['rafNum'], 0, 1, 'L', 0, '', 0);
	$bx = $pdf->GetX()+5;
	$y = $pdf->GetY()+5;
	$style = array('border' => false,'padding' => 0,'fgcolor' => array(0,0,0),'bgcolor' => false);
	//$pdf->write2DBarcode('https://cpams2.davaocity.gov.ph/verifyintakeform.php?idassistdetails='.$idassistdetails, 'QRCODE,M', $bx, $y, 30, 30, $style, 'N');
	
	$pdf->Output('certIntake.pdf', 'I');
//============================================================+
// END OF FILE
//============================================================+
?>