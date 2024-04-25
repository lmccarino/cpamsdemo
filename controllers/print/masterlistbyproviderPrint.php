<?php

require '../fpdf183/fpdf.php';
require 'fpdfextension.php';
require '../connect.php';
include '../clientsServedController.php';
include '../convertEncoding.php';

setlocale(LC_CTYPE, 'en_US');

$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';
$procloc = $_GET['procloc'];
$provider = $_GET['provider'];
$provcat = $_GET['provcat'];
$acode = $_GET['acode'];
$preparedby = $_GET['preparedby'];
$notedby = $_GET['notedby'];

$clientsServed = new ClientsServed();
$convertEncoding = new convertEncoding();

$from = date('F d, Y', strtotime($datefrom));
$to = date('F d, Y', strtotime($dateto));

$pdf=new FPDF_CellFit('P','mm','Letter');
$font = 'Arial';
$lineheight = 4;

$pdf->AddPage();
$pdf->SetTitle("Masterlist of Approved Assistance (Provider)");
$pdf->SetMargins(10, 13, 10);

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
$pdf->Cell(0,$lineheight, "Masterlist of Approved Assistance by Provider", '', 0, 'C');
$pdf->Ln(5);
$pdf->SetFont($font, '', 10);
$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');

$pdf->Ln(5);
$count=0;

if (($provcat == 'ALL') && ($acode=='ALL')){
	$sql = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, o.officename, o.location, concat(CASE WHEN b.assistCode IS NOT NULL THEN concat(b.assistCode, CASE WHEN TRIM(b.assistDesc) != '' THEN concat ('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END) as assistCode, a.provCode , concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname 
	FROM assistdetail as a 
	LEFT join assistsched as b on a.idassistsched = b.idassistsched 
	LEFT JOIN patient on a.idpatient = patient.idpatient
	JOIN office as o on a.provCode = o.officecode
	WHERE a.dateApproved >= '$datefrom' and a.dateApproved <= '$dateto' AND a.status = 'APPROVED'
		  and (
		  	('$procloc' = 'ALL')
		  	OR
		  	('$procloc' != 'ALL' and a.procloc = '$procloc')
		  ) 
	ORDER BY a.provCode asc, a.dateApproved desc";

	$query = mysqli_query($conn, $sql); 

	$previousProvCode = "";
	$totalamount = 0;
	$totalamt = 0;
	$totalrows = $numResults = mysqli_num_rows($query);
	$rowcount = 0;
 	while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
		
		if($previousProvCode != "" && $previousProvCode != $row['provCode']){
			 $pdf->SetWidths(array(149,47));
			 $pdf->SetAligns(array('R','R'));
			 $pdf->SetFont($font,'B',10);
		     $pdf->Row(array('TOTAL','Php '.number_format($totalamt, 2)));
		}

		 if($previousProvCode != $row['provCode']){
		 	$officename = $row['officename'];
			$provider = $row['provCode'];
			$location = $row['location'];
			$pdf->Ln(5);
		 	$pdf->SetFont($font, 'B', 9);
			$pdf->Cell(0,$lineheight, 'Provider: ' . strtoupper($officename). ' ('.strtoupper($location).')', 0, 0, 'L', 0, '', 0);
		 	$pdf->Ln(5);
			$pdf->SetWidths(array(15,52,22,60,25,22));
			$pdf->SetAligns(array('C','C','C','C','C','C'));
			$pdf->Row(array('#','Assistance Type','RAF No.','Patient', 'Date Approved', 'Amount Approved'));

		 	$previousProvCode = $provider;
		 	$pdf->SetFont($font, '', 9);
		 	$totalamount = 0;
		 	$totalamt = 0;
		 }
		$rafNum 		= $row['rafNum'];
		$idpatient 		= $row['idpatient'];
		$patientname 	= $convertEncoding->convertEncode($row['patientname']);
		$assistCode 	= $row['assistCode'];
		$dateApproved 	= date('m/d/Y', strtotime($row['dateApproved']));
		$amtApproved 	= number_format((float)$row['amtApproved'], 2, '.', ',');
		$totalamt 		+= $row['amtApproved'];
		$totalamount 	= number_format((float)$totalamt, 2, '.', ',');

		$pdf->SetFont($font,'',9);
        $pdf->SetAligns(array('L','L','C','L','R','R'));
        $pdf->Row(array(++$count, $assistCode,$rafNum,$patientname,$dateApproved,$amtApproved));

        if($rowcount == $totalrows-1){
			 $pdf->SetWidths(array(149,47));
			 $pdf->SetAligns(array('R','R'));
			 $pdf->SetFont($font,'B',10);
		     $pdf->Row(array('TOTAL','Php '.number_format($totalamt, 2)));
        }

        $rowcount++;
		
	}
}
else if (($provcat == 'ALL') && ($acode<>'ALL')) {
	$sql = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, o.officename, o.location, concat(CASE WHEN b.assistCode IS NOT NULL THEN concat(b.assistCode, CASE WHEN TRIM(b.assistDesc) != '' THEN concat ('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END) as assistCode, a.provCode , concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname 
	FROM assistdetail as a 
	LEFT join assistsched as b on a.idassistsched = b.idassistsched 
	LEFT JOIN patient on a.idpatient = patient.idpatient
	JOIN office as o on a.provCode = o.officecode
	WHERE a.dateApproved >= '$datefrom' and a.dateApproved <= '$dateto' AND a.status = 'APPROVED'
	AND (('$procloc' = 'ALL') OR('$procloc' != 'ALL' and a.procloc = '$procloc')) 
	AND (CASE WHEN b.assistCode IS NOT NULL THEN CONCAT(b.assistCode, CASE WHEN TRIM(b.assistDesc) != '' THEN CONCAT('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END) LIKE '$acode%'
	ORDER BY a.provCode asc, a.dateApproved desc"; 

	$query = mysqli_query($conn, $sql); 

	$previousProvCode = "";
	$totalamount = 0;
	$totalamt = 0;
	$totalrows = $numResults = mysqli_num_rows($query);
	$rowcount = 0;
 	while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
		
		if($previousProvCode != "" && $previousProvCode != $row['provCode']){
			 $pdf->SetWidths(array(149,47));
			 $pdf->SetAligns(array('R','R'));
			 $pdf->SetFont($font,'B',10);
		     $pdf->Row(array('TOTAL','Php '.number_format($totalamt, 2)));
		}

		 if($previousProvCode != $row['provCode']){
		 	$officename = $row['officename'];
			$provider = $row['provCode'];
			$location = $row['location'];
			$pdf->Ln(5);
		 	$pdf->SetFont($font, 'B', 9);
			$pdf->Cell(0,$lineheight, 'Provider: ' . strtoupper($officename). ' ('.strtoupper($location).')', 0, 0, 'L', 0, '', 0);
		 	$pdf->Ln(5);
			$pdf->SetWidths(array(15,52,22,60,25,22));
			$pdf->SetAligns(array('C','C','C','C','C','C'));
			$pdf->Row(array('#','Assistance Type','RAF No.','Patient', 'Date Approved', 'Amount Approved'));

		 	$previousProvCode = $provider;
		 	$pdf->SetFont($font, '', 9);
		 	$totalamount = 0;
		 	$totalamt = 0;
		 }
		$rafNum 		= $row['rafNum'];
		$idpatient 		= $row['idpatient'];
		$patientname 	= $convertEncoding->convertEncode($row['patientname']);
		$assistCode 	= $row['assistCode'];
		$dateApproved 	= date('m/d/Y', strtotime($row['dateApproved']));
		$amtApproved 	= number_format((float)$row['amtApproved'], 2, '.', ',');
		$totalamt 		+= $row['amtApproved'];
		$totalamount 	= number_format((float)$totalamt, 2, '.', ',');

		$pdf->SetFont($font,'',9);
        $pdf->SetAligns(array('L','L','C','L','R','R'));
        $pdf->Row(array(++$count, $assistCode,$rafNum,$patientname,$dateApproved,$amtApproved));

        if($rowcount == $totalrows-1){
			 $pdf->SetWidths(array(149,47));
			 $pdf->SetAligns(array('R','R'));
			 $pdf->SetFont($font,'B',10);
		     $pdf->Row(array('TOTAL','Php '.number_format($totalamt, 2)));
        }

        $rowcount++;
		
	}
}
else if ($acode<>'ALL') { 
	$sql = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, 
			CONCAT(CASE WHEN b.assistCode IS NOT NULL THEN CONCAT(b.assistCode, CASE WHEN TRIM(b.assistDesc) != '' THEN CONCAT('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END) AS assistCode, 
			a.provCode, CONCAT(patient.benLName, ', ', patient.benFName, ' ', patient.benMName) AS patientname 
			FROM assistdetail AS a 
			LEFT JOIN assistsched AS b ON a.idassistsched = b.idassistsched 
			LEFT JOIN patient ON a.idpatient = patient.idpatient
			WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto' AND a.provCode = '$provider' AND a.status = 'APPROVED' 
			AND (('$procloc' = 'ALL') OR ('$procloc' != 'ALL' AND a.procloc = '$procloc')) 
			AND (CASE WHEN b.assistCode IS NOT NULL THEN CONCAT(b.assistCode, CASE WHEN TRIM(b.assistDesc) != '' THEN CONCAT('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END) LIKE '$acode%'
			ORDER BY a.dateApproved desc";

	$sqlprov = "SELECT officename, location FROM office where officecode = '$provider'";
	$queryprov = mysqli_query($conn, $sqlprov); 
	while ($rowprov=mysqli_fetch_array($queryprov,MYSQLI_ASSOC)) { 
		$prov =   $rowprov['officename'].' - '.$rowprov['location'];
		$pdf->Ln(10);
		$pdf->SetFont($font, 'B', 9);
		$pdf->Cell(0,$lineheight, 'Provider: '.strtoupper($prov), 0, 0, 'L', 0, '', 0);
		$pdf->Ln(5);
		$pdf->SetWidths(array(15,52,22,60,25,22));
		$pdf->SetAligns(array('C','C','C','C','C','C'));
		$pdf->Row(array('#','Assistance Type','RAF No.','Patient', 'Date Approved', 'Amount Approved'));
	}

	$query = mysqli_query($conn, $sql); 
	$totalamount = 0;
	$totalamt = 0;
	$count=0;

	while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
		$rafNum =   $row['rafNum'];
		$idpatient =$row['idpatient'];
		$patientname = $convertEncoding->convertEncode($row['patientname']);
		$assistCode =$row['assistCode'];
		$dateApproved =date('m/d/Y', strtotime($row['dateApproved']));
		$amtApproved = number_format((float)$row['amtApproved'], 2, '.', ',');
		$totalamt += $row['amtApproved'];
		$totalamount =  number_format((float)$totalamt, 2, '.', ',');
		$pdf->SetFont($font,'',9);
        $pdf->SetAligns(array('L','L','C','L','R','R'));
        $pdf->Row(array(++$count, $assistCode,$rafNum,$patientname,$dateApproved,$amtApproved));
	}
	 $pdf->SetWidths(array(149,47));
	 $pdf->SetAligns(array('R','R'));
	 $pdf->SetFont($font,'B',10);
     $pdf->Row(array('TOTAL','Php '.number_format($totalamt, 2)));
}

else { 
	$sql = "SELECT a.dateApproved, a.amtApproved, a.rafNum, a.idpatient, concat(CASE WHEN b.assistCode IS NOT NULL THEN concat(b.assistCode, CASE WHEN TRIM(b.assistDesc) != '' THEN concat ('-', b.assistDesc) ELSE '' END) ELSE a.assistCode END) as assistCode, a.provCode, concat(patient.benLName,', ',patient.benFName, ' ',patient.benMName) as patientname 
		FROM assistdetail as a 
		LEFT JOIN assistsched as b on a.idassistsched = b.idassistsched 
		LEFT JOIN patient on a.idpatient = patient.idpatient
		where a.dateApproved >= '$datefrom' and a.dateApproved <= '$dateto' and a.provCode = '$provider' AND a.status = 'APPROVED' 
			  and (
			  	('$procloc' = 'ALL')
			  	OR
			  	('$procloc' != 'ALL' and a.procloc = '$procloc')
			  )
		ORDER BY a.dateApproved DESC";

	$sqlprov = "SELECT officename, location FROM office where officecode = '$provider'";
	$queryprov = mysqli_query($conn, $sqlprov); 
	while ($rowprov=mysqli_fetch_array($queryprov,MYSQLI_ASSOC)) { 
		$prov =   $rowprov['officename'].' - '.$rowprov['location'];
		$pdf->Ln(10);
		$pdf->SetFont($font, 'B', 9);
		$pdf->Cell(0,$lineheight, 'Provider: '.strtoupper($prov), 0, 0, 'L', 0, '', 0);
		$pdf->Ln(5);
		$pdf->SetWidths(array(15,52,22,60,25,22));
		$pdf->SetAligns(array('C','C','C','C','C','C'));
		$pdf->Row(array('#','Assistance Type','RAF No.','Patient', 'Date Approved', 'Amount Approved'));
	}

	$query = mysqli_query($conn, $sql); 
	$totalamount = 0;
	$totalamt = 0;
	$count=0;

	while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
		$rafNum =   $row['rafNum'];
		$idpatient =$row['idpatient'];
		$patientname = $convertEncoding->convertEncode($row['patientname']);
		$assistCode =$row['assistCode'];
		$dateApproved =date('m/d/Y', strtotime($row['dateApproved']));
		$amtApproved = number_format((float)$row['amtApproved'], 2, '.', ',');
		$totalamt += $row['amtApproved'];
		$totalamount =  number_format((float)$totalamt, 2, '.', ',');
		$pdf->SetFont($font,'',9);
        $pdf->SetAligns(array('L','L','C','L','R','R'));
        $pdf->Row(array(++$count, $assistCode,$rafNum,$patientname,$dateApproved,$amtApproved));
	}
	 $pdf->SetWidths(array(149,47));
	 $pdf->SetAligns(array('R','R'));
	 $pdf->SetFont($font,'B',10);
     $pdf->Row(array('TOTAL','Php '.number_format($totalamt, 2)));
}

$pdf->Ln(10);
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

