<?php

require_once('../tcpdf/tcpdf.php');
require '../connect.php';

$dtefrom = $_REQUEST['from'].' 00:00:00';
$dteto = $_REQUEST['to'].' 23:59:59';
$provcat = $_REQUEST['cat'];
$procloc = $_REQUEST['loc'];
$preparedby = $_REQUEST['preparedby'];
$notedby = $_REQUEST['notedby'];

class MYPDF extends tcpdf {   

    public function Header() {    		
	   	$image_file = '../../images/davaocity-logo.jpg';
	    $image_file1 = '../../images/lingap.jpg';

	    $this->SetFont('Helvetica', 'B', 10);
	    $this->Cell(0, 5, "Republic of the Philippines", 0, false, 'C', 0, '', 0, false, 'M', 'M');
	    $this->Ln(5);
	    $this->Cell(0, 5, "Davao City", 0, false, 'C', 0, '', 0, false, 'M', 'M');
	    $this->Ln(5);
	    $this->Cell(0, 5, "Office of the City Mayor", 0, false, 'C', 0, '', 0, false, 'M', 'M');
	    $this->Ln(5);
	    $this->SetFont('Helvetica', '', 10);
	    $this->Cell(0, 5, "Lingap Para sa Mahirap", 0, false, 'C', 0, '', 0, false, 'M', 'M');
	    $this->Image($image_file, 15, 5, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	    $this->Image($image_file1, 175, 5, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    } 

     public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('Helvetica', '', 8);
        // Page number
        $this->Cell(0, 10, 'Masterlist of Approved Assistance (Beneficiary) - Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF('P', 'mm', array(215.9, 330.2), true, 'UTF-8', false);

$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, 40);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('CPAMS');
$pdf->SetTitle('Masterlist by Beneficiary');
$pdf->SetSubject('Masterlist by Beneficiary');

$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);

$pdf->AddPage();

$sql = "SELECT officename, provCat FROM office WHERE idoffice = '$procloc'";
$query = mysqli_query($conn, $sql); 
while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)) {
	$code = $row['officename'];
	$category = $row['provCat'];
}

$pdf->Ln(5);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Write(0, 'Masterlist of Approved Assistance - Beneficiary', '', 0, 'C', true, 0, false, false, 0);

$pdf->SetFont('Helvetica', '', 10);
$pdf->Ln(1);
$pdf->Write(0, 'From '.date('F d, Y', strtotime($dtefrom)).' To '.date('F d, Y', strtotime($dteto)), '', 0, 'C', true, 0, false, false, 0);
$pdf->Ln(7);

$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(35,5,'Processing Location: ',0,0,'L');
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(90,5,((!empty($code)) ? " $code":"ALL" ),0,0,'L');

$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(20,5,'Category: ',0,0,'L');
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(35,5,$provcat,0,0,'L');

$pdf->Ln(7);
$pdf->SetFont('Helvetica', 'B', 10);
$tblheader = '<table style="width: 100%; font-size: 8.5pt; text-align:center;" border="0.5" cellpadding="2">
				<tr>
					<td><strong>RAF No.</strong></td>
					<td><strong>BENEFICIARY</strong></td>
					<td ><strong>PROVIDER</strong></td>
					<td><strong>ASSISTANCE</strong></td>
					<td><strong>DATE APPROVED</strong></td>
					<td><strong>AMOUNT</strong></td>
				</tr>				

';

$pdf->SetFont('Helvetica', '', 10);

$total = 0;

if ( (!empty($code)) ) {
	$code = "AND a.procloc = $procloc";
} else
	$code = "";

if ($provcat == 'ALL'){
	$sql = "SELECT a.rafNum, 
			CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS beneficiary,
			o.officename AS provider, CASE WHEN sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, a.amtApproved, 
			a.dateApproved
		FROM assistdetail as a 
		LEFT JOIN patient as p ON a.idpatient = p.idpatient
		LEFT JOIN office as o ON a.provCode = o.officecode
		INNER JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
		WHERE a.dateApproved BETWEEN '$dtefrom' AND '$dteto' AND a.status = 'APPROVED' $code
		ORDER BY a.idpatient";
}
else {
$sql = "SELECT a.rafNum, 
			CONCAT(p.benLName,', ',p.benFName,' ',p.benMName) AS beneficiary,
			o.officename AS provider, CASE WHEN sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, a.amtApproved, 
			a.dateApproved
		FROM assistdetail as a 
		LEFT JOIN patient as p ON a.idpatient = p.idpatient
		LEFT JOIN office as o ON a.provCode = o.officecode
		INNER JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
		WHERE a.dateApproved BETWEEN '$dtefrom' AND '$dteto' AND a.status = 'APPROVED'
		AND o.provCat = '$provcat' $code
		ORDER BY a.idpatient";
	}
$query = mysqli_query($conn, $sql); 
while ($value=mysqli_fetch_array($query,MYSQLI_ASSOC)) {

	$tblbody .= '<tr>
				<td>'.$value['rafNum'].'</td>
				<td>'.$value['beneficiary'].'</td>
				<td>'.$value['provider'].'</td>
				<td>'.$value['assistCode'].'</td>				
				<td>'.date('m/d/Y', strtotime($value['dateApproved'])).'</td>
				<td>'.number_format($value['amtApproved'], 2).'</td>
			</tr>';		

	$total += $value['amtApproved'];

}

$tblbody .= '<tr>
				<td colspan="5" style="text-align: right; font-weight: bold;">TOTAL </td>
				<td style="text-align: center; font-weight: bold;">PHP '.number_format($total, 2).'</td>						
			</tr>';		

$tblfooter = '</table>';

$content = $tblheader.$tblbody.$tblfooter;
$pdf->writeHTML($content, true, false, false, false, '');

$pdf->Ln(7);
$prepby = '';
$sql = "SELECT fullname, position FROM users WHERE userid = '$preparedby'";		
$query = mysqli_query($conn, $sql); 
while ($value=mysqli_fetch_array($query,MYSQLI_ASSOC)) {
	$prepby = $value['fullname'];
	$prepos = $value['position'];
}


$noteby = '';
$sql = "SELECT fullname, position FROM users WHERE userid = '$notedby'";		
$query = mysqli_query($conn, $sql); 
while ($value=mysqli_fetch_array($query,MYSQLI_ASSOC)) {
	$noteby = $value['fullname'];
	$notepos = $value['position'];

}


$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Cell(110, 0, 'Prepared by: ', 0, 0, 'L', 0, '', 0);
$pdf->Cell(15, 0, 'Noted by: ', 0, 0, 'L', 0, '', 0);
$pdf->Ln(16);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(110, 0, $prepby, 0, 0, 'L', 0, '', 0);
$pdf->Cell(110, 0, $noteby, 0, 0, 'L', 0, '', 0);
$pdf->Ln(5);
$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(110, 0, $prepos, 0, 0, 'L', 0, '', 0);
$pdf->Cell(110, 0, $notepos, 0, 0, 'L', 0, '', 0);

ob_end_clean();

$pdf->Output();


?>