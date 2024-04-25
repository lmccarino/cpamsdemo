<?php
require "routines.php";

if($_REQUEST['trans']=='intakeform') {
	$id = $_REQUEST['id'];

	$command ="SELECT intakeform FROM assistdetail WHERE idassistdetails = '$id'";
	$row = getrow($command);
	$fname = $row['intakeform'];

 	if (stristr(PHP_OS, 'WIN')) {
		$fullpath = realpath('')."\\intakeform\\".$fname;
	} else {
		$fullpath = realpath('')."/intakeform/".$fname;
	}
	
	echo $fname;
}

if($_REQUEST['trans']=='certificate') {
	$id = $_REQUEST['id'];

	$command ="SELECT certeligibility FROM assistdetail WHERE idassistdetails = '$id'";
	$row = getrow($command);
	$fname = $row['certeligibility'];

 	if (stristr(PHP_OS, 'WIN')) {
		$fullpath = realpath('')."\\intakeform\\".$fname;
	} else {
		$fullpath = realpath('')."/intakeform/".$fname;
	}
	
	echo $fname;
}


if($_REQUEST['trans']=='indigency') {
	$id = $_REQUEST['id'];

	$command ="SELECT certindigency FROM assistdetail WHERE idassistdetails = '$id'";
	$row = getrow($command);
	$fname = $row['certindigency'];

 	if (stristr(PHP_OS, 'WIN')) {
		$fullpath = realpath('')."\\certindigency\\".$fname;
	} else {
		$fullpath = realpath('')."/certindigency/".$fname;
	}
	
	echo $fname;
}
?>