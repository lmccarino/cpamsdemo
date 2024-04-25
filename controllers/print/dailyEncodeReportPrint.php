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
	$fromToStr = $from." to ".$to;

	$frm = date('M d, Y', strtotime($datefrom));
	$t = date('M d, Y', strtotime($dateto));

	$clientsServed = new ClientsServed();
	$convertEncoding = new convertEncoding();

	$pdf=new FPDF_CellFit('P','mm','Letter');
	$font = 'Arial';
	$lineheight = 4;

	$pdf->AddPage();
	$pdf->SetTitle("Number of Clients Served per User");
	$pdf->SetMargins(8, 13, 10);

	$pdf->Ln();
	$pdf->Image('../../images/davaocity-logo.jpg',12,6,25,25);
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
	$pdf->Cell(0,$lineheight, "Number of Clients Served per User", '', 0, 'C');
	$pdf->Ln(5);
	$pdf->SetFont($font, '', 10);
	$pdf->Cell(0,$lineheight, 'From '.$fromToStr,'', 0, 'C');

	$pdf->Ln(10);

	$pdf->SetFont($font,'B',10);
	$pdf->SetWidths(array(12,60,30,30,30,45));
	$pdf->SetAligns(array('C','C','C','C','C','C'));
	$pdf->Row(array('#','Name','Total Approved', 'Total Override', 'Total Cancelled', 'Office'));
	$count = 0;

	$sql = "SELECT 
	u.fullname,
	a.userID,
	a.approved_count,
	COALESCE(c.cancelled_count, 0) AS cancelled_count,
	COALESCE(over_ride.override_count, 0) + COALESCE(overrid.override_count, 0) AS total_override_count,
	o.officename AS office
FROM (
	SELECT 
		asst.userID,
		COUNT(*) AS approved_count
	FROM 
		assistdetail as asst 
	WHERE 
		asst.status = 'APPROVED' AND asst.dateApproved BETWEEN '$datefrom' AND '$dateto'
	GROUP BY 
		asst.userID
) a
LEFT JOIN users u ON a.userID = u.userid 
LEFT JOIN office o ON u.office = o.officecode AND o.officecode IS NOT NULL
LEFT JOIN (
	SELECT 
		SUBSTRING_INDEX(SUBSTRING_INDEX(asst_cancelled.remarks, 'CANCELLED BY ', -1), ' ', 1) AS cancelled_user,
		COUNT(*) AS cancelled_count
	FROM 
		assistdetail as asst_cancelled
	WHERE 
		asst_cancelled.status = 'CANCELLED' 
		AND asst_cancelled.dateCancel BETWEEN '$datefrom' AND '$dateto'
	GROUP BY 
		cancelled_user
) c ON u.userid = c.cancelled_user

LEFT JOIN (
	SELECT 
		SUBSTRING_INDEX(SUBSTRING_INDEX(asst_override.remarks, 'REAPPROVED BY ', -1), ' ', 1) AS override_user,
		COUNT(DISTINCT t.primaryid) AS override_count
	FROM 
		assistdetail as asst_override 
		LEFT JOIN translog as t ON t.primaryid = asst_override.idassistdetails
	WHERE 
		t.transdate BETWEEN '$datefrom' AND '$dateto'
		AND asst_override.status = 'APPROVED' 
		AND t.remarks = 'REAPPROVE'
	GROUP BY 
		override_user
) over_ride ON u.userid = over_ride.override_user

LEFT JOIN (
	SELECT 
		l_override.userid AS override_user, 
		COUNT(*) AS override_count
	FROM 
		lingapfund as l_override 
		LEFT JOIN assistdetail as asst_over ON SUBSTRING_INDEX(SUBSTRING_INDEX(l_override.details, 'OVERRIDE RAFnum: ', -1), ' ', 1) = asst_over.rafNum
	WHERE 
		l_override.details LIKE 'OVERRIDE%' AND asst_over.status = 'APPROVED' 
		AND l_override.dateSBal BETWEEN '$datefrom' AND '$dateto'
	GROUP BY 
		override_user
) overrid ON u.userid = overrid.override_user
WHERE (u.position <> 'DEV' AND u.position <> 'VIP' OR u.position IS NULL)
ORDER BY 
	a.approved_count DESC, o.officename, u.fullname";

	$total_approved = 0;
	$total_cancelled = 0;
	$total_override_count = 0;

	$query = mysqli_query($conn, $sql);
		while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
			$total_approved += $row['approved_count'];
			$total_cancelled += $row['cancelled_count'];
			$total_override_count += $row['total_override_count'];


			$pdf->SetFont($font,'',9);
			$pdf->SetAligns(array('C','L','R','R','R','L'));
			$pdf->Row(array(++$count, $convertEncoding->convertEncode($row['fullname']), number_format($row['approved_count']), number_format($row['total_override_count']), number_format($row['cancelled_count']), $row['office'] ));
	}

	$pdf->SetFont($font,'B',10);
	$pdf->SetWidths(array(72,30,30,30,45));
	$pdf->SetAligns(array('R','R','R','R','C'));
	$pdf->Row(array('TOTAL', number_format($total_approved), number_format($total_override_count), number_format($total_cancelled),''));

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