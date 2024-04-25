<?php
require('fpdf.php');

class PDF extends FPDF
{
	public $title;
	public $subtitle;
	public $label;
	public $user;
// Page header
function Header()
{
    // Logo
    //$this->Image('logo.png',10,6,15);
    // Arial bold 15
    $this->SetFont('Arial','B',14);
    // Move to the right
    //$this->Cell(80);
    // Title
    $this->Cell(0,8,'logo',0,0,'C');
	$this->Ln();
	$this->SetFont('Arial','',12);
	$this->Cell(0,8,'address','B',1,'C');
	//$this->Ln();
	$this->SetFont('Arial','',12);
	$this->Cell(0,9,$this->title,0,0,'C');
	$this->Ln();
	$this->SetFont('Arial','',10);
	$this->Cell(0,9,$this->subtitle,0,0,'C');
    //Line break
    $this->Ln();
	$this->Cell(0,7,$this->label,0,0,'L');
	$this->Ln();
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
	$today = getdate();
    $ldate = $today['month'].' '.$today['mday'].', '.$today['year'].' '.$today['hours'].':'.$today['minutes'].':'.$today['seconds'];
	$this->Cell(0,10,'Prepared by: '.$this->user.' ; '.$ldate,'T',0,'L');
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}
?>
