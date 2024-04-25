<?php

require_once('../tcpdf/tcpdf.php');
require '../connect.php';
include '../clientsServedController.php';
ini_set('error_reporting', E_STRICT);

$datefrom = $_GET['datefrom'];
$dateto = $_GET['dateto'];
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

    
        $this->Image($image_file2, 175, 5, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // $this->Image($image_file1, 155, 5, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);


    } 
     public function Footer() {
        // Position at 15 mm from bottom
         $this->SetY(-15);
        // // Set font
         $this->SetFont('helvetica', '', 8);
        // // Page number
        $this->Cell(0, 10, 'Masterlist of Approved Assistance per Provider - Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

//$pdf = new MYPDF();
$pdf = new MYPDF('P', 'mm', array(215.9, 330.2), true, 'UTF-8', false);
//$pdf->setPageOrientation('L');

$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, 40);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('CPAMS');
$pdf->SetTitle('Masterlist of Approved Assistance (Provider)');
$pdf->SetSubject('Masterlist of Approved Assistance (Provider)');

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

$txt='Masterlist of Approved Assistance by Provider';

$txt2 = 'From '.$from.' To '.$to;

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('Helvetica', '', 10);
$pdf->Write(0, $txt2, '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('Helvetica','',10);
$count=0;
$tbl_header = "";
$tbl_footer = "";
$tbl = "";
$pdf->Ln(5);


$tbl_header = '<br/><br/><table style="width: 100%;" border="1">
					<tr>
						<th style="width:7%; text-align: left;">#</th>
						<th style="width:25%">Assistance Type</th>
						<th style="width:12%">RAF No.</th>
						<th style="width:30%">Patient</th>
						<th>Date Approved</th>
						<th style="width:12%;">Amount Approved</th>
					</tr>';

if ($provcat == 'ALL'){
		$sql = "SELECT a.idpatient, o.officename, o.location, a.provCode 
			FROM assistdetail as a 
			INNER JOIN office as o on a.provCode = o.officecode
			WHERE a.dateApproved >= '$datefrom' and a.dateApproved <= '$dateto' and a.procloc = '$procloc'
			GROUP BY a.provCode";
		$query = mysqli_query($conn, $sql); 

		while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){

			$officename = $row['officename'];
			$provider = $row['provCode'];
			$location = $row['location'];


			$pdf->Cell(15, 0, 'Provider: ', 0, 0, 'L', 0, '', 0);
			$pdf->SetFont('Helvetica', 'B', 9);
			$pdf->Cell(0, 0, strtoupper($officename). ' ('.strtoupper($location).')', 0, 0, 'L', 0, '', 0);
			$pdf->Ln(5);

			$sql2 = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, o.officename, concat(b.assistCode, '-', b.assistDesc) 
			as assistCode, a.provCode , concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname 
			FROM assistdetail as a 
			join assistsched as b on a.idassistsched = b.idassistsched 
			JOIN patient on a.idpatient = patient.idpatient
			JOIN office as o on a.provCode = o.officecode
			WHERE a.dateApproved >= '$datefrom' and a.dateApproved <= '$dateto' and a.procloc = '$procloc' and a.provCode = '$provider'";
		

			$query2= mysqli_query($conn, $sql2); 
			$totalamount = 0;
			$totalamt = 0;
			while ($row2=mysqli_fetch_array($query2,MYSQLI_ASSOC)){

				$rafNum =   $row2['rafNum'];
				$idpatient =$row2['idpatient'];
				$patientname =$row2['patientname'];
				$assistCode =$row2['assistCode'];
				$dateApproved =date('m/d/Y', strtotime($row2['dateApproved']));
				$amtApproved = number_format((float)$row2['amtApproved'], 2, '.', ',');
				$totalamt += $row2['amtApproved'];
				$totalamount =  number_format((float)$totalamt, 2, '.', ',');

				$tbl .= '
				<tr>
					<td>'.++$count.'</td>
					<td>'.$assistCode.'</td>
					<td>'.$rafNum.'</td>
					<td>'.$patientname.'</td>
					<td>'.$dateApproved.'</td>
					<td align="right">'.$amtApproved.'</td>

				</tr>
				';
				
				$tbl_footer = '<tr>
				<td colspan="4" style="text-align: right; font-weight: bold;">TOTAL </td>
				<td colspan="2" style="text-align: right; font-weight: bold;">PHP '.number_format($totalamt, 2).'</td>						
			</tr></table>';

				
			}
				$pdf->SetFont('Helvetica', '', 10);
				$content = $tbl_header.$tbl.$tbl_footer;
				$pdf->writeHTML( $content, true, false, false, false, '');
				$tbl = "";


		}

		
}

else { 
		$sql = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(b.assistCode, '-', b.assistDesc) as assistCode, a.provCode, concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname FROM assistdetail as a 
			join assistsched as b on a.idassistsched = b.idassistsched 
			join patient on a.idpatient = patient.idpatient
			where a.dateApproved >= '$datefrom' and a.dateApproved <= '$dateto' and a.provCode = '$provider' and a.procloc = '$procloc'";

		$sqlprov = "SELECT officename, location FROM office where officecode = '$provider'";
		$queryprov = mysqli_query($conn, $sqlprov); 
		while ($rowprov=mysqli_fetch_array($queryprov,MYSQLI_ASSOC)) { 
			$prov =   $rowprov['officename'].' - '.$rowprov['location'];
			$pdf->Ln(5);
			$pdf->Cell(15, 0, 'Provider: ', 0, 0, 'L', 0, '', 0);
			$pdf->SetFont('Helvetica', 'B', 9);
			$pdf->Cell(0, 0, strtoupper($prov), 0, 0, 'L', 0, '', 0);
		}

		$query = mysqli_query($conn, $sql); 

		while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
			$rafNum =   $row['rafNum'];
			$idpatient =$row['idpatient'];
			$patientname =$row['patientname'];
			$assistCode =$row['assistCode'];
			$dateApproved =date('m/d/Y', strtotime($row['dateApproved']));
			$amtApproved = number_format((float)$row['amtApproved'], 2, '.', ',');
			$totalamt += $row['amtApproved'];
			$totalamount =  number_format((float)$totalamt, 2, '.', ',');
			$tbl .= '
			<tr>
				<td>'.++$count.'</td>
				<td>'.$assistCode.'</td>
				<td>'.$rafNum.'</td>
				<td>'.$patientname.'</td>
				<td>'.$dateApproved.'</td>
				<td align="right">'.$amtApproved.'</td>

			</tr>
			';
			
			$tbl_footer = '<tr>
				<td colspan="4" style="text-align: right; font-weight: bold;">TOTAL </td>
				<td colspan="2" style="text-align: right; font-weight: bold;">PHP '.number_format($totalamt, 2).'</td></tr></table>';


		}
		
		$pdf->Ln(1);
		$pdf->SetFont('Helvetica', '', 10);
		$content = $tbl_header.$tbl.$tbl_footer;
		$pdf->writeHTML( $content, true, false, false, false, '');
}


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





$pdf->Output('assistancebyprovider.pdf', 'I');
 ?>

