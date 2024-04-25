<?php
header_remove();
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 300);

require '../fpdf183/fpdf.php';
require 'fpdfextension.php';
require '../connect.php';
include '../clientsServedController.php';
setlocale(LC_CTYPE, 'en_US');

$datefrom = $_GET['datefrom'] . ' 00:00:00';
$dateto = $_GET['dateto'] . ' 23:59:59';
$preparedby = $_GET['preparedby'];
$notedby = $_GET['notedby'];
$dist = $_GET['dist'];

$from = date('M d, Y', strtotime($datefrom));
$to = date('M d, Y', strtotime($dateto));
$clientsServed = new ClientsServed();

$pdf=new FPDF_CellFit('L','mm',array(215.9,357));
$font = 'Arial';
$lineheight = 4;

$pdf->AddPage();
$pdf->SetTitle("Diagnosis per Barangay");
$pdf->SetMargins(6, 13, 6);

$pdf->Ln();
$pdf->Image('../../images/davaocity-logo.jpg', 8, 6, 25, 25);
if (($dist === 'TUGBOK') || ($dist === 'POBLACION')){
    $pdf->Image('../../images/lingap.jpg', 325, 6, 25, 28);
} else {
    $pdf->Image('../../images/lingap.jpg', 290, 6, 25, 28);
}

$pdf->SetFont($font, '', 11);
$pdf->Cell(0, $lineheight, 'Republic of the Philippines', '', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0, $lineheight, 'City of Davao', '', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0, $lineheight, 'Office of the City Mayor', '', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0, $lineheight, "Lingap Para sa Mahirap", '', 0, 'C');
$pdf->Ln(10);
$pdf->SetFont($font, 'B', 11);
$pdf->Cell(0, $lineheight, "Total Diagnosis per Barangay", '', 0, 'C');
$pdf->Ln(5);
$pdf->SetFont($font, '', 9);
$pdf->Cell(0, $lineheight, 'From ' . $from . ' To ' . $to, '', 0, 'C');
$pdf->Ln(5);
$pdf->Cell(0, $lineheight, "District: " . $dist, '', 0, 'C');

$pdf->Ln(10);

function fetchDataFromDatabase($datefrom, $dateto, $dist)
{
    global $conn;

    $sqlDiagnosis = "SELECT extracted_word, brgyName FROM diagnosis_view WHERE DATE(dateApproved) BETWEEN '$datefrom' AND '$dateto' 
                    AND statView = 'APPROVED' AND distName = '$dist' ORDER BY brgyCode ASC, extracted_word ASC";
    $queryDiagnosis = mysqli_query($conn, $sqlDiagnosis);

    if (!$queryDiagnosis) {
        die("Query execution failed: " . mysqli_error($conn));
    }

    $groupedWords = [];
    $tablerows = [];

    while ($rowD = mysqli_fetch_assoc($queryDiagnosis)) {
        $extracted_word = isset($rowD["extracted_word"]) ? mb_convert_encoding($rowD["extracted_word"], 'UTF-8', 'UTF-8') : '';
        $groupFound = false;

        foreach ($groupedWords as $groupKey => $group) {
            $intersection = array_intersect(explode(' ', $extracted_word), $group);
            $union = array_unique(array_merge(explode(' ', $extracted_word), $group));
            $jaccardSimilarity = count($intersection) / count($union);

            if ($jaccardSimilarity >= 0.3) {
                $groupedWords[$groupKey][] = $extracted_word;
                $groupFound = true;
                break;
            }
        }

        if (!$groupFound) {
            $groupedWords[] = [$extracted_word];
        }

        $key = array_search($rowD['brgyName'], array_column($tablerows, 'brgyName'));
        $extractedword = $rowD['extracted_word'];

        if ($key !== false) {
            if (!isset($tablerows[$key][$extractedword])) {
                $tablerows[$key][$extractedword] = 1;
            } else {
                $tablerows[$key][$extractedword]++;
            }
        } else {
            $rowData = [
                'brgyName' => $rowD['brgyName'],
            ];

            foreach ($groupedWords as $group) {
                foreach ($group as $word) {
                    $rowData[$word] = 0;
                }
            }

            $rowData[$extractedword] = 1;
            $tablerows[] = $rowData;
        }
        $rowDs[] = $rowD;
    }

    $numRows = mysqli_num_rows($queryDiagnosis);
    $distinctWords = array_unique(call_user_func_array('array_merge', $groupedWords));
    $allGroupedWords = $groupedWords;
    $allTablerows = $tablerows;

    return [$distinctWords, $allGroupedWords, $allTablerows];
}

function printHeaders($pdf, $allTablerows, $fixedColumnWidth, &$pageWidthHeader, $distinctWords, $dist)
{
    $pdf->Cell($fixedColumnWidth, 10, 'DIAGNOSES', 1, 0, 'C');
   
    if ($dist === 'TUGBOK') {
        $pdf->SetFont('Arial', '', 6.5);
        foreach ($allTablerows as $column) {
            $brgyName = isset($column['brgyName']) ? mb_convert_encoding($column['brgyName'], 'UTF-8', 'HTML-ENTITIES') : '';
            
            // Set the initial width based on the length of the original string
            $brgyWidth = min(20, $pdf->GetStringWidth($brgyName) + 2);
        
            // Check if the width needs to be adjusted due to truncation
            if ($pdf->GetStringWidth($brgyName) > $brgyWidth) {
                $maxChars = 12;
                $truncatedBrgy = substr($brgyName, 0, $maxChars) . '...';
                $brgyWidth = min(25, $pdf->GetStringWidth($truncatedBrgy) + 2);
            } else {
                // No truncation needed
                $truncatedBrgy = $brgyName;
            }
        
            $pdf->Cell($brgyWidth, 10, $truncatedBrgy, 1, 0, 'L');
        }
        $pdf->Cell(11, 10, 'TOTAL', 1, 0, 'C');
    } else {
        $pdf->SetFont('Arial', '', 7);
        foreach ($allTablerows as $column) {
            $brgyName = isset($column['brgyName']) ? mb_convert_encoding($column['brgyName'], 'UTF-8', 'HTML-ENTITIES') : '';
            
            // Set the initial width based on the length of the original string
            $brgyWidth = min(25, $pdf->GetStringWidth($brgyName) + 3);
        
            // Check if the width needs to be adjusted due to truncation
            if ($pdf->GetStringWidth($brgyName) > $brgyWidth) {
                $maxChars = 20;
                $truncatedBrgy = substr($brgyName, 0, $maxChars) . '...';
                $brgyWidth = min(30, $pdf->GetStringWidth($truncatedBrgy) + 3);
            } else {
                // No truncation needed
                $truncatedBrgy = $brgyName;
            }
        
            $pdf->Cell($brgyWidth, 10, $truncatedBrgy, 1, 0, 'L');
        }
        $pdf->Cell(13, 10, 'TOTAL', 1, 0, 'C');
    }
    $pdf->Ln();
}

function printDiagnosisAndDataRow($pdf, $allTablerows, $fixedColumnWidth, $distinctWords, &$pageWidthData, $maxPageHeight, $dist)
{
    $remainingHeight = $pdf->GetPageHeight() - $pdf->GetY();

    // Check if there is enough space for the data row, otherwise, add a new page
    if ($remainingHeight < 50) {
        $pdf->AddPage();
        $pageWidthData = 342;
        printHeaders($pdf, $allTablerows, $fixedColumnWidth, $pageWidthHeader, $distinctWords, $dist);
    }

    // Iterate over distinctWords for the "DIAGNOSES" column
    foreach ($distinctWords as $word) {
        $truncatedText = $word;
        if ($pdf->GetStringWidth($word) > $fixedColumnWidth) {
            $maxChars = 28;
            $truncatedText = substr($word, 0, $maxChars) . '...';
        }

        $pdf->Cell($fixedColumnWidth, 10, $truncatedText, 1, 0, 'L');

        // Calculate and print counts
        $totalCount = calculateAndPrintCounts($pdf, $allTablerows, $word, $fixedColumnWidth, $dist);

        // Move to the next line for the next set of data
        $pdf->Cell(($dist === 'TUGBOK') ? 11 : 13, 10, $totalCount, 1, 0, 'C');
        $pdf->Ln();

        // Check if there is enough space for the next row, otherwise, add a new page
        $remainingHeight = $pdf->GetPageHeight() - $pdf->GetY();

        if ($remainingHeight < 50) {
            $pdf->AddPage();
            $pageWidthData = 342;
            printHeaders($pdf, $allTablerows, $fixedColumnWidth, $pageWidthHeader, $distinctWords, $dist);
        }
    }

    // Add a line break after each data row
    $pdf->Ln();
}

function calculateAndPrintCounts($pdf, $allTablerows, $word, $fixedColumnWidth, $dist)
{
    $totalCount = 0;

    if ($dist === 'TUGBOK'){
        foreach ($allTablerows as $column) {
            $count = isset($column[$word]) ? $column[$word] : 0;
            $brgyName = isset($column['brgyName']) ? mb_convert_encoding($column['brgyName'], 'UTF-8', 'HTML-ENTITIES') : '';
            $brgyWidth = min(20, $pdf->GetStringWidth($brgyName) + 2);
    
            // Check if the width needs to be adjusted due to truncation
            if ($pdf->GetStringWidth($brgyName) > $brgyWidth) {
                $maxChars = 12;
                $truncatedBrgy = substr($brgyName, 0, $maxChars) . '...';
                $brgyWidth = min(25, $pdf->GetStringWidth($truncatedBrgy) + 2);
            } 

            $pdf->Cell($brgyWidth, 10, $count, 1, 0, 'C');
            $totalCount += $count;
        } 
    } else {
            foreach ($allTablerows as $column) {
                $count = isset($column[$word]) ? $column[$word] : 0;
                $brgyName = isset($column['brgyName']) ? mb_convert_encoding($column['brgyName'], 'UTF-8', 'HTML-ENTITIES') : '';
                $brgyWidth = min(25, $pdf->GetStringWidth($brgyName) + 3);
        
                // Check if the width needs to be adjusted due to truncation
                if ($pdf->GetStringWidth($brgyName) > $brgyWidth) {
                    $maxChars = 20;
                    $truncatedBrgy = substr($brgyName, 0, $maxChars) . '...';
                    $brgyWidth = min(30, $pdf->GetStringWidth($truncatedBrgy) + 3);
                } 

                $pdf->Cell($brgyWidth, 10, $count, 1, 0, 'C');
                $totalCount += $count;
            } 
    }
    return $totalCount;
}

// Usage
list($distinctWords, $allGroupedWords, $allTablerows) = fetchDataFromDatabase($datefrom, $dateto, $dist);

$pdf->SetFont($font, 'B', 9);
if ($dist === 'TUGBOK') {
    $fixedColumnWidth = 48;
} else {
    $fixedColumnWidth = 50;
}
$pageWidthHeader = 342;
$pageWidthData = 342;

$maxPageHeight = 180; 

// Print header row on each new page
printHeaders($pdf, $allTablerows, $fixedColumnWidth, $pageWidthHeader, $distinctWords, $dist);
printDiagnosisAndDataRow($pdf, $allTablerows, $fixedColumnWidth, $distinctWords, $pageWidthData, $maxPageHeight, $dist);

$pdf->Ln(10);
$pdf->SetFont($font, 'B', 10);
$preparedByLabel = 'Prepared by: ';
$preparedByWidth = ($dist === 'TUGBOK' || $dist === 'POBLACION') ? 225 : 125;

$pdf->Cell($preparedByWidth, 0, $preparedByLabel, 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, 'Noted by: ', 0, 0, 'L', 0, '', 0);
$pdf->Ln(14);

$pdf->SetFont($font, 'B', 10);
$pdf->Cell($preparedByWidth, 0, $clientsServed->getUser($preparedby, 'fullname'), 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, $clientsServed->getUser($notedby, 'fullname'), 0, 0, 'L', 0, '', 0);
$pdf->Ln(5);

$pdf->SetFont($font, '', 9);
$pdf->Cell($preparedByWidth, 0, $clientsServed->getUser($preparedby, 'position') ?? '', 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, $clientsServed->getUser($notedby, 'position'), 0, 0, 'L', 0, '', 0);



// Send the PDF output
$pdf->Output();


?>
