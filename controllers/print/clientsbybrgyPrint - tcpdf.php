<?php

require_once('../tcpdf/tcpdf.php');
require '../connect.php';
include '../clientsServedController.php';

//ini_set('error_reporting', E_STRICT);

$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';
$preparedby = $_GET['preparedby'];
$notedby = $_GET['notedby'];
$provcat = $_GET['provcat'];

$from = date('M d, Y', strtotime($datefrom));
$to = date('M d, Y', strtotime($dateto));
$clientsServed = new ClientsServed();

class MYPDF extends tcpdf {   
    public function Header() {    
		
        $image_file = '../../images/davaocity-logo.jpg';
        $image_file1 = '../../images/lifeishere.jpg';
        $image_file2 = '../../images/lingap.jpg';


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

       // $this->Image($image_file1, 155, 5, 40, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Image($image_file2, 175, 5, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);


    } 
     public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
        $this->Cell(0, 10, 'Total Catered Clients by Barangay - Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF();

$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, 40);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('CPAMS');
$pdf->SetTitle('Total Catered Clients by Barangay');
$pdf->SetSubject('Total Catered Clients by Barangay');

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

$txt='Total Catered Clients by Barangay';

$txt2 = 'For the Period of '.$from.' To '.$to;

$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('Helvetica', '', 9);
$pdf->Write(0, $txt2, '', 0, 'C', true, 0, false, false, 0);
$pdf->Write(0, 'Provider Category: '.$provcat, '', 0, 'C', true, 0, false, false, 0);

$pdf->SetFont('Helvetica','',10);
$count=0;
$tbl_header = "";
$tbl_footer = "";
$tbl = "";

$pdf->Ln(2);

$tbl_header = '<br/><br/><table style="width: 100%; font-size: 8.5pt;" border="1">
				<tr>
					<td style="text-align: center; width: 15%;" rowspan="2">DISTRICT</td>
					<td style="text-align: center; width: 20%;" rowspan="2">BARANGAY</td>
					<td style="text-align: center; width: 65%;" colspan="6">TYPE OF ASSISTANCE</td>
				</tr>
				<tr>
					<td style="text-align: center; width: 10%;">HOSPITAL</td>
					<td style="text-align: center; width: 9%">FUNERAL</td>
					<td style="text-align: center; width: 15%">LABORATORY</td>
					<td style="text-align: center; width: 9%">DIALYSIS</td>
					<td style="text-align: center; width: 12%">APPARATUS</td>
					<td style="text-align: center; width: 10%">TOTAL</td>
				</tr>';


$tbl_footer = ' </table>';

if ($provcat == 'ALL')
{
	$sql = "SELECT CASE WHEN
				sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, 
			a.idpatient, b.benLName, b.brgyCode, c.distName, c.brgyName
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode != 'MEDICINE' OR a.assistCode != 'MEDICINE')
			ORDER BY c.distName ASC, c.brgyCode ASC";
	$query = mysqli_query($conn, $sql); 

	$sqlHosp = "SELECT 
				count(a.idpatient) as totalhospital
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode = 'HOSPITAL' OR a.assistCode = 'HOSPITAL')";
	$queryHosp = mysqli_query($conn, $sqlHosp); 
	$totalhospital = mysqli_fetch_row($queryHosp);

	$sqlFuneral = "SELECT 
				count(a.idpatient) as totalfuneral
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode = 'FUNERAL' OR a.assistCode = 'FUNERAL')";
	$queryFuneral = mysqli_query($conn, $sqlFuneral); 
	$totalfuneral = mysqli_fetch_row($queryFuneral);

	$sqlLab= "SELECT 
				count(a.idpatient) as totalLab
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode = 'LABORATORY' OR a.assistCode = 'LABORATORY')";
	$queryLab = mysqli_query($conn, $sqlLab); 
	$totalLab = mysqli_fetch_row($queryLab);

	$sqlDialysis = "SELECT 
				count(a.idpatient) as totalDialysis
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode = 'DIALYSIS' OR a.assistCode ='DIALYSIS')";
	$queryDialysis = mysqli_query($conn, $sqlDialysis); 
	$totalDialysis = mysqli_fetch_row($queryDialysis);

	$sqlApparatus = "SELECT 
				count(a.idpatient) as totalApparatus
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode = 'APPARATUS' OR a.assistCode = 'APPARATUS')";
	$queryApparatus = mysqli_query($conn, $sqlApparatus); 
	$totalApparatus = mysqli_fetch_row($queryApparatus);
}
else{
	$sql = "SELECT 
			CASE WHEN sc.assistCode IS NOT NULL THEN sc.assistCode ELSE a.assistCode END AS assistCode, a.idpatient, a.procloc, b.benLName, b.brgyCode, c.distName, c.brgyName
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN office as o ON a.provCode = o.officecode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode != 'MEDICINE' OR a.assistCode != 'MEDICINE') AND o.provCat = '$provcat'
			ORDER BY c.distName ASC, c.brgyCode ASC";
	$query = mysqli_query($conn, $sql); 

	$sqlHosp = "SELECT 
				count(a.idpatient) as totalhospital
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN office as o ON a.provCode = o.officecode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode = 'HOSPITAL' OR a.assistCode = 'HOSPITAL') AND o.provCat = '$provcat'";
	$queryHosp = mysqli_query($conn, $sqlHosp); 
	$totalhospital = mysqli_fetch_row($queryHosp);

	$sqlFuneral = "SELECT 
				count(a.idpatient) as totalfuneral
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN office as o ON a.provCode = o.officecode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode = 'FUNERAL' OR a.assistCode = 'FUNERAL') AND o.provCat = '$provcat'";
	$queryFuneral = mysqli_query($conn, $sqlFuneral); 
	$totalfuneral = mysqli_fetch_row($queryFuneral);

	$sqlLab= "SELECT 
				count(a.idpatient) as totalLab
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN office as o ON a.provCode = o.officecode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode = 'LABORATORY' OR a.assistCode = 'LABORATORY') AND o.provCat = '$provcat'";
	$queryLab = mysqli_query($conn, $sqlLab); 
	$totalLab = mysqli_fetch_row($queryLab);

	$sqlDialysis = "SELECT 
				count(a.idpatient) as totalDialysis
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN office as o ON a.provCode = o.officecode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode = 'DIALYSIS' OR a.assistCode = 'DIALYSIS') AND o.provCat = '$provcat'";
	$queryDialysis = mysqli_query($conn, $sqlDialysis); 
	$totalDialysis = mysqli_fetch_row($queryDialysis);

	$sqlApparatus = "SELECT 
				count(a.idpatient) as totalApparatus
			FROM assistdetail as a 
			LEFT JOIN patient as b ON a.idpatient = b.idpatient
			LEFT JOIN distbrgy as c ON b.brgyCode = c.brgyCode
			LEFT JOIN office as o ON a.provCode = o.officecode
			LEFT JOIN assistsched as sc on sc.idassistsched = a.idassistsched
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
			AND (sc.assistCode = 'APPARATUS' OR a.assistCode = 'APPARATUS') AND o.provCat = '$provcat'";
	$queryApparatus = mysqli_query($conn, $sqlApparatus); 
	$totalApparatus = mysqli_fetch_row($queryApparatus);
}

$grandTotal = $totalhospital[0]+$totalfuneral[0]+$totalLab[0]+$totalDialysis[0]+$totalApparatus[0];
$dist = null;
$prevDist = null;
$prevBrgy = null;

$hospitalcnt = 0;
$funeralcnt = 0;
$labcnt = 0;
$dialysiscnt = 0;
$apparatuscnt = 0;
$total = 0;

$rownum = 0;
$write = true;

$rows = [];
$tablerows = [];
while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
	array_push($rows,$row);

	if($row['brgyName'] != $prevBrgy)
		array_push($tablerows,[
			'brgyName' 	 => $row['brgyName'],
			'distName' 	 => $row['distName'],
			'hospital' 	 => 0,
			'funeral' 	 => 0,
			'laboratory' => 0,
			'dialysis' 	 => 0,
			'apparatus'  => 0,
		]);

	$prevBrgy = $row['brgyName'];
}

foreach ($rows as $row) {
	$key = array_search($row['brgyName'], array_column($tablerows, 'brgyName'));

	if ($row['assistCode'] == 'HOSPITAL')
		$tablerows[$key]['hospital']++;
	if ($row['assistCode'] == 'FUNERAL')
		$tablerows[$key]['funeral']++;
	if ($row['assistCode'] == 'LABORATORY')
		$tablerows[$key]['laboratory']++;
	if ($row['assistCode'] == 'DIALYSIS')
		$tablerows[$key]['dialysis']++;
	if ($row['assistCode'] == 'APPARATUS')
		$tablerows[$key]['apparatus']++;
	
}

$tblrow = '';
$prevDist = null;
foreach($tablerows as $row) {
	$rowtotal = $row['hospital'] + $row['funeral'] + $row['laboratory'] + $row['dialysis'] + $row['apparatus'];

	$tblrow .= '
		<tr>
		<td style="width: 15%;">'. (($prevDist != $row['distName']) ? $row['distName'] : '') .'</td>
		<td style="width: 20%;">'. $row['brgyName'] .'</td>
		<td style="text-align: center; width: 10%">'.$row['hospital'].'</td>
		<td style="text-align: center; width: 9%">'.$row['funeral'].'</td>
		<td style="text-align: center; width: 15%">'.$row['laboratory'].'</td>
		<td style="text-align: center; width: 9%">'.$row['dialysis'].'</td>
		<td style="text-align: center; width: 12%">'.$row['apparatus'].'</td>
		<td style="text-align: center; width: 10%">'.$rowtotal. '</td>
		</tr>
	';

	$prevDist = $row['distName'];
}

$tbl .= $tblrow;

$tbl_overalltotal = ' <tr>
						<td style="text-align: left; width: 35%;"><strong>OVER-ALL TOTAL</strong></td>
						<td style="text-align: center; width: 10%">'.$totalhospital[0].'</td>
						<td style="text-align: center; width: 9%">'.$totalfuneral[0].'</td>
						<td style="text-align: center; width: 15%">'.$totalLab[0].'</td>
						<td style="text-align: center; width: 9%">'.$totalDialysis[0].'</td>
						<td style="text-align: center; width: 12%">'.$totalApparatus[0].'</td>
						<td style="text-align: center; width: 10%">'.$grandTotal.'</td>
					</tr>';

$pdf->SetFont('Helvetica', '', 10);
$content = $tbl_header.$tbl.$tbl_overalltotal.$tbl_footer;
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

$pdf->Output('clientsbybrgy.pdf', 'I');

 ?>

