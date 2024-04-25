<?php

	require '../fpdf183/fpdf.php';
	require 'fpdfextension.php';
	require '../connect.php';
	include '../clientsServedController.php';
	include '../convertEncoding.php';

	setlocale(LC_CTYPE, 'en_US');

	$clientsServed = new ClientsServed();
	$convertEncoding = new convertEncoding();

	$dtefrom = $_REQUEST['datefrom'].' 00:00:00';
	$dteto = $_REQUEST['dateto'].' 23:59:59';
	$procloc = $_REQUEST['location'];
	$preparedby = $_REQUEST['preparedby'];
	$notedby = $_REQUEST['notedby'];

	$from = date('F d, Y', strtotime($dtefrom));
	$to = date('F d, Y', strtotime($dteto));

	$sql = "SELECT officename FROM office WHERE idoffice = '$procloc'";
	$query = mysqli_query($conn, $sql); 
	while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)) {
		$code = $row['officename'];
	}

	$pdf = new FPDF_CellFit('L', 'mm', array(215.9, 330.2));
	$font = 'Arial';
	$lineheight = 4;

	$pdf->AddPage();
	$pdf->SetTitle("Masterlist of Override Assistance");
	$pdf->SetMargins(9, 13, 9);

	$pdf->Ln();
	$pdf->Image('../../images/davaocity-logo.jpg',8,6,25,25);
	$pdf->Image('../../images/lingap.jpg',298,6,25,28);

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
	$pdf->SetFont($font, 'B', 10);
	$pdf->Cell(0,$lineheight, "Masterlist of Override Assistance", '', 0, 'C');
	$pdf->Ln(5);
	$pdf->SetFont($font, '', 9);
	$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');
	$pdf->Ln(5);
	$pdf->Cell(0,$lineheight, "Processing Location: ".( (!empty($code)) ? " $code":"ALL" ), '', 0, 'C');
	$pdf->Ln(8);
	$count=0;

	$pdf->SetWidths(array(11,17,48,50,25,19,19,19,42,63));
	$pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C'));
	$pdf->SetFont($font, 'B', 9);
	$pdf->Row(array('#','RAF No.','Beneficiary', 'Provider', 'Assistance Type' ,'Date Override', 'Previous Amount', 'Amount Override', 'Override by', 'Reason for Override'));
	$total = 0;

	if ( (!empty($code)) ) {
		$code = "AND a.procloc = $procloc";
	} else
		$code = "";
		
		$sql = "SELECT 
			a.rafNum, 
			CONCAT(p.benLName, ', ', p.benFName, ' ', LEFT(p.benMName, 1)) AS patientname,
			o.officename AS providerName, 
			COALESCE(sc.assistCode, a.assistCode) AS assistCode, 
			MAX(t.transdate) AS max_transdate,
			MAX(u.fullname) AS max_fullname,
			SUBSTRING(a.remarks, LOCATE('REAPPROVED BY ', a.remarks) + LENGTH('REAPPROVED BY ') + LENGTH(SUBSTRING_INDEX(SUBSTRING_INDEX(a.remarks, 'REAPPROVED BY ', -1), ' ', 1)) + 1) AS reasonReapproval,
			SUM(a.amtApproved) AS total_amtApproved,
			SUM(a.prevAmount) AS total_prevAmount
			FROM 
			assistdetail AS a 
			LEFT JOIN patient AS p ON a.idpatient = p.idpatient
			LEFT JOIN office AS o ON a.provCode = o.officecode
			LEFT JOIN users AS u ON SUBSTRING_INDEX(SUBSTRING_INDEX(a.remarks, 'REAPPROVED BY ', -1), ' ', 1) = u.userid
			LEFT JOIN translog as t ON t.primaryid = a.idassistdetails
			INNER JOIN assistsched AS sc ON sc.idassistsched = a.idassistsched 
			WHERE 
			t.transdate BETWEEN '$dtefrom' AND '$dteto'
			AND a.status = 'APPROVED' 
			AND (u.position <> 'DEV' AND u.position <> 'VIP' OR u.position IS NULL)
			AND t.remarks = 'REAPPROVE' $code
			GROUP BY 
			a.rafNum, p.benLName, p.benFName, p.benMName, o.officename, u.fullname, a.remarks, assistCode

			UNION

			SELECT 
				SUBSTRING_INDEX(SUBSTRING_INDEX(l.details, 'OVERRIDE RAFnum: ', -1), ' ', 1) AS rafNum, 
				CONCAT(p.benLName, ', ', p.benFName, ' ', LEFT(p.benMName, 1)) AS patientname,
				o.officename AS providerName, 
				COALESCE(sc.assistCode, a.assistCode) AS assistCode, 
				l.dateSBal,
				u.fullname,
				a.noteTag,
				a.amtApproved, 
				a.prevAmount
			FROM 
			lingapfund l
			LEFT JOIN 
			assistdetail AS a ON SUBSTRING_INDEX(SUBSTRING_INDEX(l.details, 'OVERRIDE RAFnum: ', -1), ' ', 1) = a.rafNum
			LEFT JOIN 
			patient AS p ON a.idpatient = p.idpatient
			LEFT JOIN 
			office AS o ON a.provCode = o.officecode
			LEFT JOIN 
			users AS u ON l.userid = u.userid 
			LEFT JOIN 
			assistsched AS sc ON sc.idassistsched = a.idassistsched 
			WHERE 
			l.details LIKE 'OVERRIDE%' 
			AND a.status = 'APPROVED'
			AND (u.position <> 'DEV' AND u.position <> 'VIP' OR u.position IS NULL)
			AND l.dateSBal BETWEEN '$dtefrom' AND '$dteto' $code
			ORDER BY max_transdate DESC";

		$query = mysqli_query($conn, $sql); 

		while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)) 
		{ 
			$rafNum = $row['rafNum'];
			$patientname =  $convertEncoding->convertEncode($row['patientname']);
			$officename = $row['providerName']??'';
			$assistCode = $row['assistCode'];
			$max_transdate = date('m/d/Y', strtotime($row['max_transdate']));
			$max_fullname =  $convertEncoding->convertEncode($row['max_fullname']);
			$reasonReapproval =  $convertEncoding->convertEncode($row['reasonReapproval']);
			$total_amtApproved = number_format((float)($row['total_amtApproved']??0), 2, '.', ',');
			$total_prevAmount = number_format((float)($row['total_prevAmount']??0), 2, '.', ',');

			$pdf->SetFont($font, '', 9);
			$pdf->SetAligns(array('C','L','L','L','L','R','R','R','L','L'));
			$pdf->Row(array(++$count, $rafNum, $patientname, $officename, $assistCode, $max_transdate, $total_prevAmount, $total_amtApproved, $max_fullname, strtoupper($reasonReapproval)));
		}

		$pdf->Ln(12);
		$pdf->SetFont($font, 'B', 10);
		$pdf->Cell(180, 0, 'Prepared by: ', 0, 0, 'L', 0, '', 0);
		$pdf->Cell(20, 0, 'Noted by: ', 0, 0, 'L', 0, '', 0);
		$pdf->Ln(14);
		$pdf->SetFont($font, 'B', 9);
		$pdf->Cell(180, 0, $clientsServed->getUser($preparedby,'fullname'), 0, 0, 'L', 0, '', 0);
		$pdf->Cell(20, 0, $clientsServed->getUser($notedby,'fullname'), 0, 0, 'L', 0, '', 0);
		$pdf->Ln(5);
		$pdf->SetFont($font, '', 9);
		$pdf->Cell(180, 0, $clientsServed->getUser($preparedby,'position'), 0, 0, 'L', 0, '', 0);
		$pdf->Cell(20, 0, $clientsServed->getUser($notedby,'position'), 0, 0, 'L', 0, '', 0);

		ob_end_clean();
		$pdf->Output();
?>