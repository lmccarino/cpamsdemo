<?php
	require '../fpdf183/fpdf.php';
	require 'fpdfextension.php';
	require '../connect.php';
	include '../clientsServedController.php';
	include '../convertEncoding.php';

	setlocale(LC_CTYPE, 'en_US');

	$datefrom = $_GET['datefrom'].' 00:00:00';
	$dateto = $_GET['dateto'].' 23:59:59';

	$from = date('F d, Y', strtotime($datefrom));
	$to = date('F d, Y', strtotime($dateto));
	$preparedby = $_GET['preparedby'];
	$notedby = $_GET['notedby'];
	$sysuser = $_GET['sysuser'];

	$sqluser =  "SELECT fullname from users where userid = '$sysuser'";
	$queryuser = mysqli_query($conn, $sqluser); 
		while ($rowuser=mysqli_fetch_array($queryuser,MYSQLI_ASSOC)){
			$fullname = $rowuser['fullname'];

	$frm = date('M d, Y', strtotime($datefrom));
	$t = date('M d, Y', strtotime($dateto));

	$clientsServed = new ClientsServed();
	$convertEncoding = new convertEncoding();

	$pdf=new FPDF_CellFit('P','mm','Letter');
	$font = 'Arial';
	$lineheight = 4;

	$pdf->AddPage();
	$pdf->SetTitle("List of Clients Catered by User");
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
	$pdf->Cell(0,$lineheight, "List of Clients Catered by User ", '', 0, 'C');
	$pdf->Ln(5);
	$pdf->SetFont($font, '', 11);
	$pdf->Cell(0,$lineheight, 'From '.$from.' To '.$to ,'', 0, 'C');
	$pdf->Ln(5);
	$pdf->SetFont($font, 'B', 10);
	$pdf->Cell(0,$lineheight, 'System User: ' .  $convertEncoding->convertEncode($fullname), 0, 0, 'L', 0, '', 0);
	}
	
	$pdf->Ln(10);

	$pdf->SetFont($font,'B',9);
	$pdf->SetWidths(array(12,17,58,50,23,20,20));
	$pdf->SetAligns(array('C','C','C','C','C','C','C'));
	$pdf->Row(array('#','RafNum', 'Beneficiary', 'Assistance Type', 'Status','Date', 'Amount Approved'));

	$sql = "SELECT 
				asst.rafNum, p.benLName, p.benFName, p.benMName,
				asst.assistCode, asst.idpatient, asst.status, 
				CASE 
					WHEN asst.status = 'APPROVED' THEN asst.amtApproved
					WHEN asst.status = 'CANCELLED' OR asst.status = 'RECEIVED' THEN 0
				END as amtApprovedorcancel,
				CASE 
					WHEN asst.status = 'APPROVED' THEN asst.dateApproved
					WHEN asst.status = 'CANCELLED' THEN asst.dateCancel
					WHEN asst.status = 'RECEIVED' THEN asst.dateReceive
				END as dateApprovedorCancelled
			FROM assistdetail asst 
			LEFT JOIN patient p ON asst.idpatient = p.idpatient
			WHERE 
				(asst.status = 'APPROVED' AND asst.dateApproved BETWEEN '$datefrom' AND '$dateto' AND asst.userID = '$sysuser') OR
				(asst.status = 'CANCELLED' AND asst.dateCancel BETWEEN '$datefrom' AND '$dateto' AND SUBSTRING_INDEX(SUBSTRING_INDEX(asst.remarks, 'CANCELLED BY ', -1), ' ', 1) = '$sysuser') OR
				(asst.status = 'RECEIVED' AND asst.dateReceive BETWEEN '$datefrom' AND '$dateto' AND asst.userID = '$sysuser')
			ORDER BY 
				CASE 
					WHEN asst.status = 'APPROVED' THEN asst.dateApproved
					WHEN asst.status = 'CANCELLED' THEN asst.dateCancel
					WHEN asst.status = 'RECEIVED' THEN asst.dateReceive
				END DESC";

	$totalProcessed = 0;
	$count = 0;
	$query = mysqli_query($conn, $sql); 
		while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
			$beneficiary = $row['benLName'].', '.$row['benFName'].' '.$row['benMName'];
			$dateApprovedorCancelled = date('m/d/Y', strtotime($row['dateApprovedorCancelled']));
			$amtApprovedorcancel = number_format((float)$row['amtApprovedorcancel'], 2, '.', ',');
			$pdf->SetFont($font,'',9);
			$pdf->SetAligns(array('C','L','L','L','L','C','R'));
			$pdf->Row(array(++$count, $row['rafNum'],  $convertEncoding->convertEncode($beneficiary), $row['assistCode'], $row['status'] ,$dateApprovedorCancelled, $amtApprovedorcancel));
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