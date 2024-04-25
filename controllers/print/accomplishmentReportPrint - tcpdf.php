<?php

require_once('../tcpdf/tcpdf.php');
require '../connect.php';
include '../clientsServedController.php';

ini_set('error_reporting', E_STRICT);
$datefrom = $_GET['datefrom'].' 00:00:00';
$dateto = $_GET['dateto'].' 23:59:59';

$from = date('F d, Y', strtotime($datefrom));
$to = date('F d, Y', strtotime($dateto));
$preparedby = $_GET['preparedby'];
$notedby = $_GET['notedby'];

$frm = date('M d, Y', strtotime($datefrom));
$t = date('M d, Y', strtotime($dateto));

$clientsServed = new ClientsServed();

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
        $this->Cell(0, 10, 'Accomplishment Report - Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF();

$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, 40);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('CPAMS');
$pdf->SetTitle('Accomplishment Report');
$pdf->SetSubject('Accomplishment Report');

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
$txt='Accomplishment Report';

$txt2 = 'From '.$from.' To '.$to;

$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('Helvetica', '', 10);
$pdf->Write(0, $txt2, '', 0, 'C', true, 0, false, false, 0);
$pdf->SetFont('Helvetica','BU',10);
$tbl_header = "";
$tbl_footer = "";
$tbl = "";
$tbl_header2 = "";
$tbl_footer2 = "";
$tbl2 = "";

$pdf->Ln(5);
$pdf->Cell(15, 0, 'Lingap - Government Desk', 0, 0, 'L', 0, '', 0);
$pdf->Ln(3);

$tbl_header = '<br/><br/><table style="width: 100%;" border="0.5">
                    <tr>
                        <th style="text-align: center;"><strong>Assistance Type</strong></th>
                        <th style="text-align: center;"><strong>Clients</strong></th>
                        <th style="text-align: center;"><strong>Expenditures</strong></th>
                    </tr>';


$sql = "SELECT 
        CASE WHEN b.assistCode IS NOT NULL THEN b.assistCode ELSE a.assistCode END AS acode,  
        COUNT(a.idpatient) AS patients, 
        SUM(a.amtApproved) AS totalAmount
        FROM assistdetail AS a 
        LEFT JOIN assistsched AS b ON a.idassistsched = b.idassistsched
        LEFT JOIN office AS o ON a.provCode = o.officecode
        WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
        AND o.provcat = 'GOVERNMENT'
        AND (b.assistCode IS NOT NULL OR a.assistCode IS NOT NULL) GROUP BY acode";

$query = mysqli_query($conn, $sql); 
while ($row=mysqli_fetch_array($query,MYSQLI_ASSOC)){
    $totalamt += $row['totalAmount'];
    $totalnum += $row['patients'];

$tbl .= '
            <tr>
                <td>'.$row['acode'].'</td>
                <td align = "right">'.number_format($row['patients']).'</td>
                <td align = "right">'.number_format($row['totalAmount'], 2).'</td>
            </tr>
            ';

$tbl_footer = '<tr>
                <td style="text-align: right;"><strong>TOTAL</strong></td>
                <td style="text-align: right;"><strong>'.number_format($totalnum).'</strong></td>
                <td style="text-align: right;"><strong>Php '.number_format($totalamt, 2).'</strong></td></tr></table>';



}

$tbl_header_expenditures = '<br/><br/><table style="width: 65%;" border="0.5">
                    <tr>
                        <th style="text-align: center;"><strong>Period</strong></th>
                        <th style="text-align: center;"><strong>Total Expenditures</strong></th>
                    </tr>';
$tbl_expenditures .= '
            <tr>
                <td>'.$frm.' - '.$t.'</td>
                <td align = "right">'.number_format($totalamt, 2).'</td>
            </tr></table>';


$pdf->Ln(1);
$pdf->SetFont('Helvetica', '', 10);
$content = $tbl_header.$tbl.$tbl_footer;
$pdf->writeHTML($content, true, false, false, false, '');

$pdf->Ln(3);
$pdf->SetFont('Helvetica', 'BU', 10);
$pdf->Cell(15, 0, 'Total Expenditures', 0, 0, 'L', 0, '', 0);
$pdf->Ln(3);
$pdf->SetFont('Helvetica', '', 10);
$content_expenditures = $tbl_header_expenditures.$tbl_expenditures;
$pdf->writeHTML($content_expenditures, true, false, false, false, '');


$tbl_header2 = '<br/><br/><table style="width: 100%;" border="0.5">
                    <tr>
                        <th style="text-align: center;"><strong>Assistance Type</strong></th>
                        <th style="text-align: center;"><strong>Clients</strong></th>
                        <th style="text-align: center;"><strong>Expenditures</strong></th>
                    </tr>';



$sql2 = "SELECT 
        CASE WHEN b.assistCode IS NOT NULL THEN b.assistCode ELSE a.assistCode END AS acode,  
        COUNT(a.idpatient) AS patients, 
        SUM(a.amtApproved) AS totalAmount
        FROM assistdetail AS a 
        LEFT JOIN assistsched AS b ON a.idassistsched = b.idassistsched
        LEFT JOIN office AS o ON a.provCode = o.officecode
        WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
        AND o.provcat = 'PRIVATE'
        AND (b.assistCode IS NOT NULL OR a.assistCode IS NOT NULL) GROUP BY acode";

$query2 = mysqli_query($conn, $sql2); 
while ($row2=mysqli_fetch_array($query2,MYSQLI_ASSOC)){
    $totalamt2 += $row2['totalAmount'];
    $totalnum2 += $row2['patients'];

 $tbl2 .= '
            <tr>
                <td>'.$row2['acode'].'</td>
                <td align = "right">'.number_format($row2['patients']).'</td>
                <td align = "right">'.number_format($row2['totalAmount'], 2).'</td>
            </tr>
            ';

  $tbl_footer2 = '<tr>
                <td style="text-align: right;"><strong>TOTAL</strong></td>
                <td style="text-align: right;"><strong>'.number_format($totalnum2).'</strong></td>
                <td style="text-align: right;"><strong>Php '.number_format($totalamt2, 2).'</strong></td></tr></table>';
}

$tbl_header_expenditures2 = '<br/><br/><table style="width: 65%;" border="0.5">
                    <tr>
                        <th style="text-align: center;"><strong>Period</strong></th>
                        <th style="text-align: center;"><strong>Total Expenditures</strong></th>
                    </tr>';
$tbl_expenditures2 .= '
            <tr>
                <td>'.$frm.' - '.$t.'</td>
                <td align = "right">'.number_format($totalamt2, 2).'</td>
            </tr></table>';


$pdf->SetFont('Helvetica','BU',10);
$pdf->Ln(5);
if ($totalamt2 >0){
$pdf->Cell(15, 0, 'Lingap - Private Desk', 0, 0, 'L', 0, '', 0);
$pdf->Ln(3);
$pdf->SetFont('Helvetica','',10);
$content2 = $tbl_header2.$tbl2.$tbl_footer2;
$pdf->writeHTML($content2, true, false, false, false, '');

$pdf->Ln(3);
$pdf->SetFont('Helvetica', 'BU', 10);
$pdf->Cell(15, 0, 'Total Expenditures', 0, 0, 'L', 0, '', 0);
$pdf->Ln(3);
$pdf->SetFont('Helvetica', '', 10);
$content_expenditures2 = $tbl_header_expenditures2.$tbl_expenditures2;
$pdf->writeHTML($content_expenditures2, true, false, false, false, '');

}

$sql3 = "SELECT 
        CASE WHEN b.assistCode IS NOT NULL THEN b.assistCode ELSE a.assistCode END AS acode,  
        COUNT(a.idpatient) AS patients, 
        SUM(a.amtApproved) AS totalAmount
        FROM assistdetail AS a 
        LEFT JOIN assistsched AS b ON a.idassistsched = b.idassistsched
        LEFT JOIN office AS o ON a.provCode = o.officecode
        WHERE a.dateApproved >= '$datefrom' AND a.dateApproved <= '$dateto'
        AND o.provcat IN ('GOVERNMENT', 'PRIVATE')
        AND (b.assistCode IS NOT NULL OR a.assistCode IS NOT NULL) GROUP BY acode";
$query3 = mysqli_query($conn, $sql3); 
while ($row3=mysqli_fetch_array($query3,MYSQLI_ASSOC)){
    $totalamt3 += $row3['totalAmount'];
    $totalnum3 += $row3['patients'];
}

$pdf->Ln(5);
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Cell(15, 0, 'Total Number of Clients : '.number_format($totalnum3) , 0, 0, 'L', 0, '', 0);
$pdf->Ln(5);
$pdf->Cell(15, 0, 'Total Expenditures         : Php '.number_format($totalamt3, 2) , 0, 0, 'L', 0, '', 0);

$pdf->Ln(25);
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
$pdf->Output('accomplishmentreport.pdf', 'I');

?>


