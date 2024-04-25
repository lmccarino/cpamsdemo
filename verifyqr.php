<?php
require_once 'controllers/printGLController.php';
require_once 'controllers/numbertowords.php';

$id = $_REQUEST['id'];
$gldetails = new PrintGL();
$details = $gldetails->getDetails($id);
$row = $details->data;

$numtowords = new numbertowordconverter();
?>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">
	<link rel="apple-touch-icon" sizes="72x72" href="images/icons/72.png">
	<link rel="manifest" href="manifest.json">
	<link rel="icon" href="images/icons/72.png">	
	<link rel="stylesheet" href="css/main2.css">
	<title>CPAMS</title>
</head>

<body>
	<div class="container">
		<div class="row">
			<p class="fs-6 fw-bold text-center font-monospace mt-5">Guarantee Letter Verification</p>
			<hr>
		</div>

		<div class="row mt-2">
			<div class="col">
				<p class="fs-6 text-center">Patient: </p>
			</div>
			<div class="col">
				<p class="fs-6 fw-bold" id="patientname"> <?php echo $gldetails->getPatient($row['idpatient'], 'name'); ?></p>
			</div>
		</div>		
		<div class="row">
			<div class="col">
				<p class="fs-6 text-center">Assistance: </p>
			</div>
			<div class="col">
				<p class="fs-6 fw-bold" id="assistance"> <?php echo $gldetails->getAssistCode($row['idassistsched'], 'code'); ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p class="fs-6 text-center">Provider: </p>
			</div>
			<div class="col">
				<p class="fs-6 fw-bold" id="provider"> <?php echo $gldetails->getOfficer($row['provCode'], 'provider') ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p class="fs-6 text-center">Amount: </p>
			</div>
			<div class="col">
				<p class="fs-6 fw-bold" id="amount"> <?php echo number_format($row['amtApproved'],2); ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p class="fs-6 text-center">Date Approved: </p>
			</div>
			<div class="col">
				<p class="fs-6 fw-bold" id="dteapproved"> <?php echo date('F d, Y', strtotime($row['dateApproved'])); ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p class="fs-6 text-center">RAF No: </p>
			</div>
			<div class="col">
				<p class="fs-6 fw-bold" id="rafno"> <?php echo $row['rafNum']; ?></p>
			</div>
		</div>

		<input type="hidden" id="assistid" > 
	</div>

</body>




<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/toastr.min.js"></script>
<script src="js/bootbox.min.js"></script>
<script src="js/select2.min.js"></script>
<script src="js/src/main.js"></script>
<script src="js/src/verifyqr.js"></script>
