<?php

require_once('../tcpdf/tcpdf.php');
include '../clientsServedController.php';

ini_set('error_reporting', E_STRICT);
$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';

$from = date('M d, Y', strtotime($datefrom));
$to = date('M d, Y', strtotime($dateto));
$preparedby = $_GET['preparedby'];
$notedby = $_GET['notedby'];

class MYPDF extends tcpdf {   

    public function Header() {    
		
        $image_file = '../../images/davaocity-logo.jpg';
        $image_file1 = '../../images/lingap.jpg';

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

        $this->Image($image_file1, 175, 5, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);


    } 
     public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
        $this->Cell(0, 10, 'Summary of Clients Served - Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF();

$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, 40);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('CPAMS');
$pdf->SetTitle('Summary of Clients Served');
$pdf->SetSubject('Summary of Clients Served');

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
$pdf->Ln(3);
$txt='Summary of Clients Served ';

$txt2 = 'For the Period of '.$from.' To '.$to;

$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('Helvetica', '', 10);
$pdf->Write(0, $txt2, '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('Helvetica','',10);
$count=0;
$tbl_header = "";
$tbl_footer = "";
$tbl = "";

$pdf->Ln(5);

$tbl_header = '<br/><br/><table style="width: 100%; font-size:10pt;" border="1">
				<tr>
					<td style="text-align: center; width: 50%;" colspan="2"><strong>LINGAP PRIVATE</strong></td>
					<td style="text-align: center; width: 50%;" colspan="2"><strong>GOVERNMENT</strong></td>
				</tr>
				<tr>
					<td style="text-align: center; width: 25%;">CLIENTS SERVED</td>
					<td style="text-align: center; width: 25%">AMOUNT</td>
					<td style="text-align: center; width: 25%">CLIENTS SERVED</td>
					<td style="text-align: center; width: 25%">AMOUNT</td>
				</tr>';

$tbl_footer = ' </table>';

$clientsServed = new ClientsServed();
$totalprivate = $clientsServed->getTotal('PRIVATE',$datefrom, $dateto, 'totalserved');
$totalprivamt = $clientsServed->getTotal('PRIVATE',$datefrom, $dateto, 'totalamount');
$totalgovt = $clientsServed->getTotal('GOVERNMENT',$datefrom, $dateto, 'totalserved');
$totalgovtamt = $clientsServed->getTotal('GOVERNMENT',$datefrom, $dateto, 'totalamount');
$tblrow .= '
		<tr>
			<td style="text-align: center; width: 25%"><strong>'.number_format($totalprivate).'</strong></td>
			<td style="text-align: center; width: 25%"><strong>Php '.number_format($totalprivamt, 2, '.', ',').'</strong></td>
			<td style="text-align: center; width: 25%"><strong>'.number_format($totalgovt).'</strong></td>
			<td style="text-align: center; width: 25%"><strong>Php '.number_format($totalgovtamt, 2, '.', ',').'</strong></td>

		</tr>
		';


$pdf->SetFont('Helvetica', '', 10);
$content = $tbl_header.$tblrow.$tbl_footer;
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

$pdf->Output('clientsServedSummary.pdf', 'I');

 ?>

