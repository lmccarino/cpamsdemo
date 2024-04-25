<?php

    require_once('fpdf183/fpdf.php');
    require_once('fpdf183/fpdfextension.php');    
    
    include 'printCertificatesController.php';
    include 'numbertowords.php';

    $id = $_REQUEST['idassistdetails'];  
    $tk = $_REQUEST['tk'];
    
    $myobj = validatetoken();
    if (empty($myobj->userid)){
        header('Location:../index.html?message='.urlencode("Invalid User"));
        die('security error');
    } else {$userid = $myobj->userid;}


    $pdf = new FPDF_CellFit('P', 'mm', array(215.9,279.4));
    $pdf->AddPage();
    $pdf->SetTitle("Certificates");
    $pdf->SetMargins(18, 5, 15);
    setlocale(LC_CTYPE, 'en_US');

    $certdetails = new PrintCertificates();
    $numtowords = new numbertowordconverter();

    $font = 'Arial';
    $lineheight = 5;
 
    //********************************* GUARANTEE LETTER ********************************/

    $image_file = '../images/cmo-header-blue.png';
    // $pdf->Image($image_file, 0, 0, 220, 0, 'PNG'); 
    $pdf->Image($image_file, -22, 0, 270, 0, 'PNG'); 

    $assistdetail = $certdetails->getAssistDetail($id);

    $documentdate = (isset($assistdetail['dateReissue'])) ? date('F d, Y', strtotime($assistdetail['dateReissue'])) : date('F d, Y', strtotime($assistdetail['dateApproved']));

    $pdf->Ln(47);
    $pdf->SetFont('Times', 'B', 11);   
    $pdf->Cell(0,$lineheight, $documentdate, '', 0, 'L');

    $officer = $certdetails->getOfficer($assistdetail['provCode']);   
    $pdf->Ln(13);
    $pdf->Cell(0,$lineheight, ($officer['provCat']=='GOVERNMENT') ? $officer['contactperson'] : 'THE MANAGER', '', 0, 'L');
    $pdf->SetFont('Times', '', 11);
    $pdf->Ln();
    if($assistdetail['provCode'] == 'SPMC') {
        $pdf->Cell(0,$lineheight, $officer['position'], '', 0, 'L');
        $pdf->Ln();
    }        
    $pdf->Cell(0,$lineheight, $officer['officename'], '', 0, 'L');
    $pdf->Ln();
    $pdf->Cell(0,$lineheight, $officer['location'], '', 0, 'L');
    

    $pdf->Ln(14);
    $pdf->Cell(0,$lineheight, 'Dear Sir/Madam:', '', 0, 'L');
    $pdf->Ln(10);

    if ($assistdetail['assistCode'] == 'PROCEDURE' || $assistdetail['assistCode'] == 'LABORATORY') {
        $assistcode = $assistdetail['assistDesc'];
    } else {
        $assistcode = $assistdetail['assistCode'];
    }
                        
    $patient = $certdetails->getPatientDetails($assistdetail['idpatient']);

    $texthtml='<p style="text-indent:50px; text-align: justify; line-height: 2;">
        Please be informed that the '.
        $assistcode.' bill of <b>'.$patient['patientname'].'</b>, resident of Brgy. '.
        $patient['brgyname'].', Davao City in the amount of <b>'.
        $numtowords->numberTowords($assistdetail['amtApproved']).' (PhP '.number_format($assistdetail['amtApproved'],2).')</b> '.
        'only, has been approved by the City Mayor to be charged against the City Government of Davao. </p>' ;

    $pdf->WriteHTML($texthtml); 

    if($assistcode == 'MEDICINE') {
        $medicine = $certdetails->getMedicines($assistdetail['idassistdetails']);
        $pdf->Ln(10);
        $pdf->SetFont('times', 'BU', 10);    
        $pdf->Cell(0,$lineheight, 'Pharmacy', '', 0, 'L');

        $pdf->SetFont('times', '', 9);  
        $total = 0;     
        $pdf->Ln(5);

        for($i = 0; $i < count($medicine); $i++) {
            $pdf->Cell(45,$lineheight, $medicine[$i]['pharmaname'], '', 0, 'L');
            $pdf->Cell(18,$lineheight,  number_format($medicine[$i]['amount'],2), '', 0, 'R');
            $pdf->Ln(3.5);
            $total += $medicine[$i]['amount'];
        }
        $pdf->Ln(2);
        $pdf->SetFont('times', 'B', 10);       
        $pdf->Cell(45,$lineheight, 'TOTAL', 'T', 0, 'R');
        $pdf->Cell(18,$lineheight,  number_format($total, 2), 'T', 0, 'R');    

    }

    $pdf->Ln(10);
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(0,$lineheight, 'Please call us at 233 - 4961 or 0909-547-4763 if you have any additional questions or need more information.', '', 0, 'L');
       
    $pdf->Ln(8);
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(45,$lineheight, 'Thank you and God Bless.', '', 0, 'L');

    $pdf->Ln(8);
    $pdf->SetFont('times', 'B', 9);
    $pdf->Cell(45,$lineheight, '*This Guarantee Letter is valid for only five (5) working days upon issuance.', '', 0, 'L');

    $pdf->SetFont('times', '', 11);
    $pdf->Ln(13);
    $pdf->Cell(100,$lineheight,'','',0,'L');
    $pdf->Cell(25,$lineheight,'Very truly yours,','',0,'L');
    $pdf->Ln(10);
    $pdf->Cell(100,$lineheight,'','',0,'L');
    $pdf->Cell(25,$lineheight,'For the City Mayor:','',0,'L');

    $setX = $pdf->GetX();
    $setY = $pdf->GetY();
    $setY = $setY + 10;

    $signatory = $certdetails->getGLSignatory();

    $sigfullpath = '../signatures/'.$signatory['signature'];
    $ext =  strtoupper(pathinfo($sigfullpath, PATHINFO_EXTENSION));

    $pdf->Image($sigfullpath, 135, $setY-3, 40, 15, $ext); 

    $pdf->Image('../images/davaocity-logo.jpg', 180, $setY, 8, 8, 'JPG'); 
    $pdf->SetFont('times', 'B', 5);
    $pdf->SetY($setY-2);    
    $pdf->SetX(188);
    $pdf->Cell(15,5, "DIGITALLY",'', 0, 'L');
    $pdf->Ln(2);                
    $pdf->SetX(188);
    $pdf->Cell(10,5, "STAMPED",'', 0, 'L');
    $pdf->Ln(2);    
    $pdf->SetX(188);
    $pdf->Cell(10,5, date('m/d/Y', strtotime($assistdetail['dateApproved'])),'', 0, 'L');
    $pdf->Ln(2);
    $pdf->SetX(188);
    $pdf->Cell(10,5, date('H:i:s', strtotime($assistdetail['dateApproved'])),'', 0, 'L');

    $setX = $pdf->GetX();
    $setY = $pdf->GetY() + 5;
    $pdf->SetY($setY);
    $pdf->SetFont('times', 'B', 11);
    $pdf->Cell(100,5,'','',0,'L');

    $pdf->Cell(25,5,$signatory['fullname'],'',0,'L');
    $pdf->Ln();
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(103,5,'','',0,'L');
    $pdf->Cell(25,5,$signatory['signposition'],'',0,'L');
        
    $pdf->Ln(9);
    $setX = $pdf->GetX();
    $setY = $pdf->GetY()-30;

    $url = 'https://cpams2.davaocity.gov.ph/verifyqr.php?id='.$assistdetail['idassistdetails'];    
    $qrsrc = 'https://appbts.davaocity.gov.ph/application/api/plugins/qrcode?url='.$url.'&size=3&margin=3'; 
    $pdf->Image($qrsrc,$setX,$setY,32,32, 'PNG');

    $pdf->Ln(1);
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(10,5,'RAF #','',0,'L');
    $pdf->SetFont('times', 'B', 11);
    $pdf->Cell(20,5,' '.$assistdetail['rafNum'],'',0,'L');

    if ($certdetails->isReissued($assistdetail))
        $pdf->Ln(6);
    else
        $pdf->Ln(8);

    $pdf->SetFont('times', 'B', 7);
    $pdf->Cell(10,5,'DO NOT ACCEPT WITHOUT SEAL.','',0,'L');

    if ($certdetails->isReissued($assistdetail)) {
        $pdf->Ln(5);
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(10,5, $certdetails->getReissueMsg($assistdetail),'',0,'L');
    }
        
    // $footertext =  $certdetails->getUser($assistdetail['userID'])." @ ".date('m/d/Y H:i:s');
    $footertext =  $certdetails->getUser($userid)." @ ".date('m/d/Y H:i:s');

    // $content_height = 26; 
    // $page_height = $pdf->GetPageHeight();
    // $y_coordinate = $page_height - $content_height;

    // $pdf->SetY($y_coordinate);  
    // $pdf->SetFont('times', 'I', 9);   
    // $pdf->Cell(0,5,$footertext,'',0,'R');

    $pdf->SetFont('times', 'I', 9); 
    $pdf->FooterCertificate($footertext);

    //********************************* CERTIFICATE OF ELIGIBILITY ********************************/

    $pdf->AddPage();

    $image_file = '../images/CSWDO.jpg';
    $pdf->Image($image_file, 0, 0, 220, 0, 'JPG'); 

    $pdf->Ln(60);
    $pdf->SetFont('Times', 'B', 11);   
    $pdf->Cell(0,$lineheight, 'CERTIFICATE OF ELIGIBILITY', '', 0, 'C');

    $intake = $certdetails->getIntakePatient($id);

    $pdf->Ln(20);
    $pdf->SetFont('Times', '', 11);
   

    $today = new DateTime();
    $stamp = strtotime($intake['benBDate']);
    $birthdate = new DateTime();
    $birthdate->setTimestamp($stamp);

    $ageInterval = $today->diff($birthdate);
    $age = $ageInterval->y;

    $texthtml='<p style="text-indent:50px; text-align: justify; line-height: 1.5;">
            This is to certify <b>'.trim($intake['patientname']).'</b>, '.$age.' years old, residing at '.'<b>'.$intake['patientaddress'].', BARANGAY '.$intake['brgyname'].
            '</b> has been found eligible for <b>'.$assistcode.' BILL</b> Assistance  after interview and case study has been made. Records of the case study dated <b>'.
            date('m/d/Y', strtotime($assistdetail['dateApproved'])).'</b> are in the Confidential file of Ugnayan Section/District Office.</p>' ;
        

    $pdf->WriteHTML($texthtml);
    $pdf->Ln(10);

    $texthtml='<p style="text-indent:50px; text-align: justify; line-height: 2;">
            Client is recommended for assistance in the amount of <b>'.
            $numtowords->numberTowords($assistdetail['amtApproved']).' (PhP '.number_format($assistdetail['amtApproved'],2).')</b>'.
            " for the purpose of the payment of <b> $assistcode BILL</b> to be charged against the fund of the City Mayor's Office/". 
            "City Social Services and Development Office of the City Mayor's Office and/or City Social Welfare & Development Office.</p>";
        
    $pdf->WriteHTML($texthtml);
    $pdf->Ln(23);

    $pdf->SetFont('Times', 'B', 11); 
    $pdf->Cell(10,$lineheight, '', '', 0, 'C');
    $pdf->Cell(55,$lineheight, $intake['sworker'], 'B', 0, 'C');
    $pdf->Cell(30,$lineheight, '', '', 0, 'C');
    $pdf->Cell(70,$lineheight, $intake['requestor'], 'B', 0, 'C');

    $pdf->Ln();
    $pdf->SetFont('Times', '', 11); 
    $pdf->Cell(10,$lineheight, '', '', 0, 'C');
    $pdf->Cell(55,$lineheight, 'Social Worker', '', 0, 'C');
    $pdf->Cell(30,$lineheight, '', '', 0, 'C');
    $pdf->Cell(70,$lineheight, 'Name/Signature of Requestor', '', 0, 'C');

    $pdf->Ln();
    $pdf->SetFont('Times', '', 11); 
    $pdf->Cell(95,$lineheight, '', '', 0, 'C');
    $pdf->Cell(70,$lineheight, $intake['relation'], '', 0, 'C');

    $pdf->Ln(10);
    $pdf->Cell(0,$lineheight, 'Attested By:', '', 0, 'L');
    $pdf->Ln(15);

    $setX = $pdf->GetX();
    $setY = $pdf->GetY() - 10;

    $cssdosignatory = $certdetails->getCSSDOSignatory();

    $sigfullpath = '../signatures/'.$cssdosignatory['signature'];
    $ext =  strtoupper(pathinfo($sigfullpath, PATHINFO_EXTENSION));

    $pdf->Image($sigfullpath, 35, $setY, 35, 20, $ext); 

    $setY = $setY + 5;

    $pdf->Image('../images/davaocity-logo.jpg', 70, $setY, 8, 8, 'JPG'); 
    $pdf->SetFont('times', 'B', 5);
    $pdf->SetY($setY);    
    $pdf->SetX(78);
    $pdf->Cell(15,5, "DIGITALLY",'', 0, 'L');
    $pdf->Ln(2);                
    $pdf->SetX(78);
    $pdf->Cell(10,5, "STAMPED",'', 0, 'L');
    $pdf->Ln(2);    
    $pdf->SetX(78);
    $pdf->Cell(10,5, date('m/d/Y', strtotime($assistdetail['dateApproved'])),'', 0, 'L');
    $pdf->Ln(2);
    $pdf->SetX(78);
    $pdf->Cell(10,5, date('H:i:s', strtotime($assistdetail['dateApproved'])),'', 0, 'L');

    $setX = $pdf->GetX();
    $setY = $pdf->GetY() + 5;
    $pdf->SetY($setY);
    $pdf->SetFont('times', 'B', 11);
    $pdf->Cell(10,5,'','',0,'L');
    $pdf->Cell(55,5,$cssdosignatory['fullname'],'B',0,'L');
    $pdf->Cell(30,5,'','',0,'L');
    $pdf->Cell(70,5,'SEBASTIAN Z. DUTERTE','B',0,'C');

    $pdf->Ln();
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(10,5,'','',0,'L');
    $pdf->Cell(55,5,$cssdosignatory['signposition'],'',0,'C');
    $pdf->Cell(30,5,'','',0,'L');
    $pdf->Cell(70,5,'City Mayor','',0,'C');

    $pdf->Ln();
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(10,5,'','',0,'L');
    $pdf->Cell(55,5,'CSWDO','',0,'C');

    $pdf->Ln();
    $pdf->Cell(95,5,'','',0,'L');
    $pdf->Cell(70,5,'For the City Mayor:','',0,'L');

    $setX = $pdf->GetX();
    $setY = $pdf->GetY() + 10;

    $signatory = $certdetails->getGLSignatory();

    $sigfullpath = '../signatures/'.$signatory['signature'];
    $ext =  strtoupper(pathinfo($sigfullpath, PATHINFO_EXTENSION));

    $pdf->Image($sigfullpath, 135, $setY, 40, 15, $ext); 

    $pdf->Image('../images/davaocity-logo.jpg', 180, $setY, 8, 8, 'JPG'); 
    $pdf->SetFont('times', 'B', 5);
    $pdf->SetY($setY-2);    
    $pdf->SetX(188);
    $pdf->Cell(15,5, "DIGITALLY",'', 0, 'L');
    $pdf->Ln(2);                
    $pdf->SetX(188);
    $pdf->Cell(10,5, "STAMPED",'', 0, 'L');
    $pdf->Ln(2);    
    $pdf->SetX(188);
    $pdf->Cell(10,5, date('m/d/Y', strtotime($assistdetail['dateApproved'])),'', 0, 'L');
    $pdf->Ln(2);
    $pdf->SetX(188);
    $pdf->Cell(10,5, date('H:i:s', strtotime($assistdetail['dateApproved'])),'', 0, 'L');

    $setX = $pdf->GetX();
    $setY = $pdf->GetY() + 10;
    $pdf->SetY($setY);
    $pdf->SetFont('times', 'B', 11);
    $pdf->Cell(100,5,'','',0,'L');

    $pdf->Cell(25,5,$signatory['fullname'],'',0,'L');
    $pdf->Ln();
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(100,5,'','',0,'L');
    $pdf->Cell(25,5,$signatory['signposition'],'',0,'L');

    // $footertext =  $certdetails->getUser($intake['userid'])." @ ".date('m/d/Y H:i:s');
    $footertext =  $certdetails->getUser($userid)." @ ".date('m/d/Y H:i:s');

    // $content_height = 26; 
    // $page_height = $pdf->GetPageHeight();
    // $y_coordinate = $page_height - $content_height;

    // $pdf->SetY($y_coordinate);  
    // $pdf->SetFont('times', 'I', 9);   
    // $pdf->Cell(0,5,$footertext,'',0,'L');

    $pdf->SetFont('times', 'I', 9); 
    $pdf->FooterCertificate($footertext);

    //********************************* CERTIFICATE OF INDIGENCY ********************************/

    $pdf->AddPage();

    $image_file = '../images/CSWDO.jpg';
    $pdf->Image($image_file, 0, 0, 220, 0, 'JPG'); 

    $pdf->Ln(55);
    $pdf->SetFont('Times', 'B', 11);   
    $pdf->Cell(0,$lineheight, 'CERTIFICATE OF INDIGENCY', '', 0, 'C');

    if (empty($intake['effectivitydate'])){
        $datefrom = date('m/d/Y');
        $dateto = date('m/d/Y', strtotime(' + 1 year'));
    } else {
        $datefrom  = date($intake['effectivitydate']);
        $Date =date_create($intake['effectivitydate']);
        $datefrom = date_format($Date,"m/d/Y");
        $dateto = date('m/d/Y', strtotime($datefrom . ' + 1 years'));
    }

    $pdf->Ln(7);
    $pdf->SetFont('Times', '', 8);   
    $pdf->Cell(0,$lineheight, 'Effective Date: from '.$datefrom.' to '.$dateto, '', 0, 'R');

    $pdf->Ln(20);
    $pdf->SetFont('Times', 'B', 11);   
    $pdf->Cell(0,$lineheight, 'TO WHOM IT MAY CONCERN:', '', 0, 'L');

    $pdf->Ln(12);
    $pdf->SetFont('Times', '', 11);  

    $today = new DateTime();
    $stamp = strtotime($intake['benBDate']);
    $birthdate = new DateTime();
    $birthdate->setTimestamp($stamp);

    $ageInterval = $today->diff($birthdate);
    $age = $ageInterval->y;

    $texthtml='<p style="text-indent:50px; text-align: justify; line-height: 1.5;">
            This is to certify <b>'.trim($intake['patientname']).'</b>, '.$age.' years old, married/single, and a bonafide resident of '.
            '<b>'.$intake['patientaddress'].', BARANGAY '.$intake['brgyname'].'</b>, Davao City Region 11, has been found to be indigent '.
            'and eligible for government intervention.';

    $pdf->WriteHTML($texthtml);
    $pdf->Ln(10);

    $texthtml='<p style="text-indent:50px; text-align: justify; line-height: 2;">
            This certification is issued upon request of the above mentioned client in relation to his/her desire to avail and be granted '.
            '(<b>'.$assistcode." BILL </b>), under the office of the City Mayor's Office and/or City Social Welfare & Development Office,".
            'all of Davao City';
        
    $pdf->WriteHTML($texthtml);
    $pdf->Ln(10);

    $texthtml='<p style="text-align: justify; line-height: 2;">
            Issued this <b>'.date('jS', strtotime($assistdetail['dateApproved'])).' day of '.date('F Y', strtotime($assistdetail['dateApproved'])).
            '</b> at Davao City, Philippines';
        
    $pdf->WriteHTML($texthtml);

    $pdf->Ln(30);

    $setX = $pdf->GetX();
    $setY = $pdf->GetY() - 10;

    $sigfullpath = '../signatures/'.$cssdosignatory['signature'];
    $ext =  strtoupper(pathinfo($sigfullpath, PATHINFO_EXTENSION));

    $pdf->Image($sigfullpath, 130, $setY, 35, 20, $ext); 

    $setY = $setY + 5;

    $pdf->Image('../images/davaocity-logo.jpg', 165, $setY, 8, 8, 'JPG'); 
    $pdf->SetFont('times', 'B', 5);
    $pdf->SetY($setY);    
    $pdf->SetX(173);
    $pdf->Cell(15,5, "DIGITALLY",'', 0, 'L');
    $pdf->Ln(2);                
    $pdf->SetX(173);
    $pdf->Cell(10,5, "STAMPED",'', 0, 'L');
    $pdf->Ln(2);    
    $pdf->SetX(173);
    $pdf->Cell(10,5, date('m/d/Y', strtotime($assistdetail['dateApproved'])),'', 0, 'L');
    $pdf->Ln(2);
    $pdf->SetX(173);
    $pdf->Cell(10,5, date('H:i:s', strtotime($assistdetail['dateApproved'])),'', 0, 'L');

    $setX = $pdf->GetX();
    $setY = $pdf->GetY() + 5;
    $pdf->SetY($setY);
    $pdf->SetFont('Times', 'B', 11); 
    $pdf->Cell(10,$lineheight, '', '', 0, 'C');
    $pdf->Cell(55,$lineheight, $intake['sworker'], 'B', 0, 'C');
    $pdf->Cell(30,$lineheight, '', '', 0, 'C');
    $pdf->Cell(70,$lineheight, $cssdosignatory['fullname'], 'B', 0, 'C');

    $pdf->Ln();
    $pdf->SetFont('Times', '', 11); 
    $pdf->Cell(10,$lineheight, '', '', 0, 'C');
    $pdf->Cell(55,$lineheight, 'Social Worker', '', 0, 'C');
    $pdf->Cell(30,$lineheight, '', '', 0, 'C');
    $pdf->Cell(70,$lineheight, $cssdosignatory['signposition'], '', 0, 'C');

    // $footertext =  $certdetails->getUser($intake['userid'])." @ ".date('m/d/Y H:i:s');
    $footertext =  $certdetails->getUser($userid)." @ ".date('m/d/Y H:i:s');

    // $content_height = 26; 
    // $page_height = $pdf->GetPageHeight();
    // $y_coordinate = $page_height - $content_height;

    // $pdf->SetY($y_coordinate);  
    // $pdf->SetFont('times', 'I', 9);   
    // $pdf->Cell(0,5,$footertext,'',0,'L');

    $pdf->SetFont('times', 'I', 9); 
    $pdf->FooterCertificate($footertext);

    //********************************* INTAKE FORM ********************************/

    $pdf->AddPage();

    $image_file = '../images/CSWDO.jpg';
    $pdf->Image($image_file, 0, 0, 220, 0, 'JPG'); 

    $pdf->Ln(55);
    $pdf->SetFont('Times', 'B', 11);   
    $pdf->Cell(0,$lineheight, 'INTAKE/INTERVIEW FORM', '', 0, 'C');

    $documentdate = (isset($assistdetail['dateReissue'])) ? date('F d, Y', strtotime($assistdetail['dateReissue'])) : date('F d, Y', strtotime($assistdetail['dateApproved']));

    $pdf->Ln(18);
    $pdf->SetFont('Times', 'B', 11);   
    $pdf->Cell(0,$lineheight, $documentdate, '', 0, 'R');

    $pdf->Ln(12);
    $pdf->SetFont('Times', '', 11);
    $pdf->Cell(27,$lineheight, 'Name of Client: ', '', 0, 'L');
    $pdf->SetFont('Times', 'BU', 11);
    $pdf->Cell(88,$lineheight, trim($intake['patientname']), '', 0, 'L');

    $pdf->Ln(7);
    $pdf->SetFont('Times', '', 11);
    $pdf->Cell(15,$lineheight, 'Address: ', '', 0, 'L');
    $pdf->SetFont('Times', 'BU', 11);
    $pdf->Cell(100,$lineheight, $intake['patientaddress'], '', 0, 'L');

    $pdf->Ln(15);
    $pdf->SetWidths(array(80,60,25));   
    $pdf->SetFont('Times', 'B', 11);   
    $pdf->SetAligns(array('L','L','L'));
    $pdf->RowBorder(array('Dependents','Relation to Head', 'Age'),0);

    $pdf->SetFont('Times', '', 10);
    $pdf->SetAligns(array('L','L','L'));

    $dependents = array();
    $dependents = json_decode($intake['details'],true);

    for($i = 0; $i < count($dependents); $i++) {
        $dependent = $dependents[$i];
        $pdf->RowBorder(array($dependent['depName'],$dependent['depRelation'], $dependent['depAge']),0);
    }
    
    $pdf->Ln(15);
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(0,$lineheight, 'REMARKS: ', '', 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Times', '', 11);
    $pdf->MultiCell(0,$lineheight, $intake['remarks'], 0, 'L');

    $pdf->Ln(23);

    $pdf->SetFont('Times', 'B', 11); 
    $pdf->Cell(10,$lineheight, '', '', 0, 'C');
    $pdf->Cell(55,$lineheight, $intake['sworker'], 'B', 0, 'C');
    $pdf->Cell(30,$lineheight, '', '', 0, 'C');
    $pdf->Cell(70,$lineheight, $intake['requestor'], 'B', 0, 'C');

    $pdf->Ln();
    $pdf->SetFont('Times', '', 11); 
    $pdf->Cell(10,$lineheight, '', '', 0, 'C');
    $pdf->Cell(55,$lineheight, 'Social Worker', '', 0, 'C');
    $pdf->Cell(30,$lineheight, '', '', 0, 'C');
    $pdf->Cell(70,$lineheight, 'Name/Signature of Requestor', '', 0, 'C');

    $pdf->Ln();
    $pdf->SetFont('Times', '', 11); 
    $pdf->Cell(95,$lineheight, '', '', 0, 'C');
    $pdf->Cell(70,$lineheight, $intake['relation'], '', 0, 'C');

    // $footertext =  $certdetails->getUser($intake['userid'])." @ ".date('m/d/Y H:i:s');
    $footertext =  $certdetails->getUser($userid)." @ ".date('m/d/Y H:i:s');

    // $content_height = 26; 
    // $page_height = $pdf->GetPageHeight();
    // $y_coordinate = $page_height - $content_height;

    // $pdf->SetY($y_coordinate);  
    // $pdf->SetFont('times', 'I', 9);   
    // $pdf->Cell(0,5,$footertext,'',0,'L');

    $pdf->SetFont('times', 'I', 9); 
    $pdf->FooterCertificate($footertext);

    ob_end_clean();
    
    //Output PDF
    // $pdf->Output();

    //SAVE PDF

    $raffile = $assistdetail['rafNum'];    
    $stamp = getdate();
    $filestamp =  $stamp['year'].$stamp['mon'].$stamp['mday'].$stamp['hours'].$stamp['minutes'].$stamp['seconds'];
    $filename = $assistdetail['idassistdetails'].'_'.$filestamp.'.pdf';
         
    include 'utilityController.php';
    
    $filestring = $pdf->Output($filename . '.pdf', 'S');

    // Create a temporary file and store the generated PDF
    $tempfile = tmpfile();
    fwrite($tempfile, $filestring);
    fseek($tempfile, 0);
    $tempfileUri = stream_get_meta_data($tempfile)['uri'];

    // Upload the temporary file to the server
    $result = Utility::uploadToServer($tempfileUri, 'certificates', 'generatedpdf');

    // Close and delete the temporary file
    fclose($tempfile);

    if($result['success'] == true){
        $generatedfilename = $result['message'];
        $command ="CALL savecertificates($id, '$generatedfilename',$userid, '$tk')";
        $row = getrow($command);
    }