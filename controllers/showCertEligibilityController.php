<?php
require "routines.php";
$idassistdetails = $_REQUEST['idassistdetails'];
$command ="select certeligibility from assistdetail where idassistdetails = $idassistdetails";
$row = getrow($command);
$fname = $row['certeligibility'];

if (stristr(PHP_OS, 'WIN')) {
	$fullpath = realpath('')."\\certeligibility\\".$fname;
} else {
	$fullpath = realpath('')."/certeligibility/".$fname;
}


	// Author: Teddy C. 09/14/2023 09:11.
	// $fileinfo = pathinfo($fullpath);
	// header('Content-Type: application/pdf');
	// header('Content-Length: ' . filesize($fullpath));
	// readfile($fullpath);
	// End Teddy C.

	
// Author: Teddy C. 09/14/2023 09:11.
// Check if the file exists locally
if (file_exists($fullpath)) {
    $fileinfo = pathinfo($fullpath);

    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($fullpath));
    readfile($fullpath);
} else {
    // File doesn't exist locally, attempt to fetch it from the external URL
    $fileContent = file_get_contents("https://lingapblob.blob.core.windows.net/eligibility/" . $fname);
    
    if ($fileContent !== false) {
        header('Content-Type: application/pdf');
        header('Content-Length: ' . strlen($fileContent));
        echo $fileContent;
    } else {
        // Handle the case where the file doesn't exist both locally and externally
        echo "File not found.";
    }
}
// End Teddy C.
?>