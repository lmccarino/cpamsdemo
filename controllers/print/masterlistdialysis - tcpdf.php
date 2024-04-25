<?php

require_once('../tcpdf/tcpdf.php');
require '../connect.php';
include '../clientsServedController.php';
// ini_set('error_reporting', E_STRICT);

$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';
$procloc = $_GET['procloc'];
$provider = $_GET['provider'];
$provcat = $_GET['provcat'];
$preparedby = $_GET['preparedby'];
$notedby = $_GET['notedby'];

$clientsServed = new ClientsServed();

$from = date('F d, Y', strtotime($datefrom));
$to = date('F d, Y', strtotime($dateto));


class MYPDF extends tcpdf {   

    public function Header() {    
		
        $image_file = '../../images/davaocity-logo.jpg';
        $image_file1 = '../../images/lifeishere.jpg';
        $image_file2 = '../../images/lingap.jpg';
        
        $this->Ln(4);
        $this->SetFont('Helvetica', 'B', 10);
        // Title
        $this->Cell(0, 5, "Republic of the Philippines", 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(4);

        $this->Cell(0, 5, "Davao City", 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(4);

        $this->Cell(0, 5, "Office of the City Mayor", 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(4);
        $this->Cell(0, 5, "Lingap Para sa Mahirap", 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->Image($image_file, 15, 5, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

       // $this->Image($image_file1, 245, 5, 40, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Image($image_file2, 175, 5, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);



    } 
     public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('Helvetica', '', 9);
        // Page number
        $this->Cell(0, 10, 'Masterlist of Approved Assistance (Dialysis) - Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF();
//$pdf->setPageOrientation('L');

$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, 40);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('CPAMS');
$pdf->SetTitle('Masterlist of Approved Assistance - Dialysis');
$pdf->SetSubject('Masterlist of Approved Assistance - Dialysis');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);

$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 12);

$txt='Masterlist of Approved Assistance - Dialysis';

$txt2 = 'From '.$from.' To '.$to;

$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('Helvetica', '', 10);
$pdf->Write(0, $txt2, '', 0, 'C', true, 0, false, false, 0);
$pdf->Write(0, 'Provider Category: '.$provcat, '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('Helvetica','',15);
$count=0;
$tbl_header = "";
$tbl_footer = "";
$tbl = "";

$pdf->Ln(4);

if ($provcat == 'ALL')
{
	$sql = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) AS patientname, a.provCode, o.officename FROM assistdetail AS a LEFT JOIN patient ON a.idpatient = patient.idpatient LEFT JOIN office AS o ON a.provCode = o.officecode LEFT JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched WHERE a.dateApproved <= '$dateto' AND a.dateApproved >= '$datefrom' AND (sc.assistCode = 'DIALYSIS' OR a.assistCode = 'DIALYSIS') ORDER BY o.officename ASC, a.dateApproved DESC";

	$query = mysqli_query($conn, $sql); 
}

else {
	$sql = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) AS patientname, a.provCode, o.officename FROM assistdetail AS a LEFT JOIN patient ON a.idpatient = patient.idpatient LEFT JOIN office AS o ON a.provCode = o.officecode LEFT JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched WHERE a.dateApproved <= '$dateto' AND a.dateApproved >= '$datefrom' AND (sc.assistCode = 'DIALYSIS' OR a.assistCode = 'DIALYSIS') AND o.provcat = '$provcat' ORDER BY o.officename ASC, a.dateApproved DESC";

	$query = mysqli_query($conn, $sql); 
}

while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)) 
{ 
	$rafNum =   $row['rafNum'];
	$patientname =htmlspecialchars($row['patientname']);
	// echo $patientname;
	// echo "<br/>";
	$officename =$row['officename']??'';
	$dateApproved =date('m/d/Y', strtotime($row['dateApproved']));
	$amtApproved = number_format((float)($row['amtApproved']??0), 2, '.', ',');
	$totalamt += ($row['amtApproved']??0);
	$totalamount =  number_format((float)$totalamt, 2, '.', ',');

	$tbl_header = '<br/><br/><table style="width: 100%; font-size: 9px;" border="0.5">
					<tr>
						<th style="width:7%; text-align: left;"><strong>#</strong></th>
						<th style="width:12%;"><strong>RAF No.</strong></th>
						<th style="width:30%"><strong>Patient</strong></th>
						<th style="width:30%"><strong>Provider</strong></th>
						<th style="width:12%"><strong>Date Approved</strong></th>
						<th style="text-align:right; width:12%;"><strong>Amount Approved</strong></th>
					</tr>';
	$tbl_footer = '<tr>
				<td colspan="4" style="text-align: right; font-weight: bold;">TOTAL </td>
				<td colspan="2" style="text-align: right; font-weight: bold;">PHP '.number_format($totalamt, 2).'</td>						
			</tr></table>';
	$tbl .= '
		<tr>
			<td>'.++$count.'</td>
			<td>'.$rafNum.'</td>
			<td>'.$patientname.'</td>
			<td>'.$officename.'</td>
			<td>'.$dateApproved.'</td>
			<td align="right">'.$amtApproved.'</td>
		</tr>
		';
}
	// exit();

$pdf->SetFont('Helvetica', '', 10);
$content = $tbl_header.$tbl.$tbl_footer;
$pdf->writeHTML( $content, true, false, false, false, '');

$pdf->Ln(5);
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Cell(110, 0, 'Prepared by: ', 0, 0, 'L', 0, '', 0);
$pdf->Cell(15, 0, 'Noted by: ', 0, 0, 'L', 0, '', 0);
$pdf->Ln(16);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(110, 0, $clientsServed->getUser($preparedby,'fullname'), 0, 0, 'L', 0, '', 0);
$pdf->Cell(110, 0, $clientsServed->getUser($notedby,'fullname'), 0, 0, 'L', 0, '', 0);
$pdf->Ln(5);
$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(110, 0, $clientsServed->getUser($preparedby,'position'), 0, 0, 'L', 0, '', 0);
$pdf->Cell(110, 0, $clientsServed->getUser($notedby,'position'), 0, 0, 'L', 0, '', 0);

ob_end_clean();
$pdf->Output('masterlistdialysis.pdf', 'I');

 ?>

