<?php
	require '../fpdf183/fpdf.php';
	require 'fpdfextension.php';
	require '../connect.php';
	include '../clientsServedController.php';
	include '../convertEncoding.php';
	setlocale(LC_CTYPE, 'en_US');

	$datefrom = date('Y-m-d', strtotime('-1 day', strtotime($_GET['datefrom']))).' 23:00:00';
	$dateto = $_GET['datefrom'].' 23:00:00';

	$from = date('F d, Y', strtotime($datefrom));
	$to = date('F d, Y', strtotime($dateto));
	$preparedby = $_GET['preparedby'];
	$notedby = $_GET['notedby'];

	$frm = date('M d, Y', strtotime($datefrom));
	$t = date('M d, Y', strtotime($dateto));

	$clientsServed = new ClientsServed();
	$convertEncoding = new convertEncoding();

	$pdf=new FPDF_CellFit('P','mm','Letter');
	$font = 'Arial';
	$lineheight = 4;

	$pdf->AddPage();
	$pdf->SetTitle("Daily Encoder Report Individual");
	$pdf->SetMargins(8, 15, 8);

	$pdf->Ln();
	$pdf->Image('../../images/davaocity-logo.jpg',10,6,25,25);
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
	$pdf->Cell(0,$lineheight, "Daily Encoder Report Individual", '', 0, 'C');
	$pdf->Ln(5);
	$pdf->SetFont($font, '', 11);
	$pdf->Cell(0,$lineheight, 'For '.$to,'', 0, 'C');


	$totalnum = 0;
	$totalamt = 0;
	$totalnum2 = 0;
	$totalamt2 = 0;
	$totalnum3 = 0;
	$totalamt3 = 0;

	$pdf->Ln(10);

	$pdf->SetFont($font,'B',9);
	$pdf->SetWidths(array(12,44,17,45,35,18,31));
	$pdf->SetAligns(array('C','C','C','C','C','C','C'));
	$pdf->Row(array('#','System User', 'RafNum', 'Beneficiary', 'Type of Assistance', 'Duration (mins)', 'Office'));

	$sql = "SELECT 
				u.fullname,
				asst.rafNum, p.benLName, p.benFName, p.benMName,
				LEFT(p.benMName, 1) AS middleInitial,
				asst.assistCode, asst.idpatient,
				CONCAT(
					CASE 
						WHEN asst.timeConsume/60 < 1
						THEN 0
						ELSE asst.timeConsume DIV 60
					END,
					':',
					LPAD(asst.timeConsume MOD 60, 2, '0')
				) AS minutes,
				o.officename AS office
			FROM 
				assistdetail asst 
				LEFT JOIN patient p ON asst.idpatient = p.idpatient
				LEFT JOIN users u ON asst.userID = u.userid
				LEFT JOIN office o ON u.office = o.officecode
			WHERE 
				dateApproved BETWEEN '$datefrom' AND '$dateto'
			ORDER BY o.officename, u.fullname";

	$totalProcessed = 0;
	$count = 0;
	$query = mysqli_query($conn, $sql); 
		while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
			$beneficiary = $row['benLName'].', '.$row['benFName'].' '.$row['middleInitial'];
			$pdf->SetFont($font,'',9);
			$pdf->SetAligns(array('C','L','L','L','L','C','L'));
			$pdf->Row(array(++$count, $convertEncoding->convertEncode($row['fullname']), $row['rafNum'], $convertEncoding->convertEncode($beneficiary), $row['assistCode'], $row['minutes'] ,$row['office']));
		}

	$pdf->Ln(25);
	$pdf->SetFont($font, 'B', 11);
	$pdf->Cell(110, 0, 'Prepared by: ', 0, 0, 'L', 0, '', 0);
	$pdf->Cell(15, 0, 'Noted by: ', 0, 0, 'L', 0, '', 0);
	$pdf->Ln(16);
	$pdf->SetFont($font,'B', 10);
	$pdf->Cell(110, 0, $clientsServed->getUser($preparedby,'fullname'), 0, 0, 'L', 0, '', 0);
	$pdf->Cell(110, 0, $clientsServed->getUser($notedby,'fullname'), 0, 0, 'L', 0, '', 0);
	$pdf->Ln(5);
	$pdf->SetFont($font, '', 10);
	$pdf->Cell(110, 0, $clientsServed->getUser($preparedby,'position'), 0, 0, 'L', 0, '', 0);
	$pdf->Cell(110, 0, $clientsServed->getUser($notedby,'position'), 0, 0, 'L', 0, '', 0);

	$pdf->Output();
?>