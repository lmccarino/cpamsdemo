function startload(){
	var otk = document.getElementsByName('tk');
	for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
	{
	    otk[x].value = qs['tk'];
	}

	let xform = document.getElementById('searchform').elements;
	xform.namedItem('datefrom').value = configuredate();
	xform.namedItem('dateto').value = configuredate();

	var i = '1434';
	$.get("controllers/masterlistViewProviderController.php",{trans:"getsignatory",tk:qs['tk'], office:i}, function(data){
		var x = document.getElementById('searchform');
		for (a = 0;a < data['data'].length; a++) {
			var option = document.createElement("option");
				option.text  = data['data'][a]['fullname'];
				option.value = data['data'][a]['userid'];

			x.elements.namedItem('preparedby').add(option.cloneNode(true));
			x.elements.namedItem('notedby').add(option); 
		}
		
		if (x.elements.namedItem('location'))
		for (a = 0;a < data['offices'].length; a++) {
			var optionElement = document.createElement("option");
				optionElement.text  = data['offices'][a]['officename'];
				optionElement.value = data['offices'][a]['idoffice'];
			x.elements.namedItem('location').add(optionElement); 
		}
	},"json");

	$.get("controllers/masterlistViewProviderController.php",{trans:"getassistance",tk:qs['tk']}, function(data){
		var x = document.getElementById('searchform');
		
		var acode = x.elements.namedItem('acode');
		var row = data['data'];
		for (a = 0;a < row.length; a++){
			var option = document.createElement("option");
							option.text = row[a]['assistCode'];
							option.value = row[a]['assistCode'];
							acode.add(option); 
		}
	},"json");

	$.get("controllers/masterlistViewProviderController.php",{trans:"getdist",tk:qs['tk']}, function(data){
		var x = document.getElementById('searchform');
		
		var dist = x.elements.namedItem('dist');
		var row = data['data'];
		for (a = 0;a < row.length; a++){
			var option = document.createElement("option");
							option.text = row[a]['distName'];
							option.value = row[a]['distName'];
							dist.add(option); 
		}
	},"json");
} 

$("#location").change(function(){
		var i = "";		
		if (this.value == 0 )	var i = '1434';
		else  i = this.value;
		$.get("controllers/masterlistViewProviderController.php",{trans:"getsignatory",tk:qs['tk'], office:i}, function(data){
		var x = document.getElementById('searchform');
		var sign = x.elements.namedItem('preparedby');
		var row = data['data'];		
		sign.innerHTML = "";
		for (a = 0;a < row.length; a++){
			var option = document.createElement("option");
							option.text = row[a]['fullname'];
							option.value = row[a]['userid'];
							sign.add(option); 
		}

		var sign2 = x.elements.namedItem('notedby');
		var row2 = data['data'];		
		sign2.innerHTML = "";
		for (a = 0;a < row2.length; a++){
			var option2 = document.createElement("option");
							option2.text = row2[a]['fullname'];
							option2.value = row2[a]['userid'];
							sign2.add(option2); 
		}
	},"json");
});


$('#txtReport').change(function(){
	var optval = $('#txtReport').val();
		$('#assistanceSelect').removeAttr('style');
		$('#provloc').removeAttr('style');
	if (optval === "totalNumberbyBrgy") {
		$('#distSelect').attr('style', 'display: none;');
	} else if (optval === "diagnosisperBrgy") {
		$('#distSelect').removeAttr('style');
		$('#assistanceSelect').attr('style', 'display: none;');
		$('#provloc').attr('style', 'display: none;');
	} 
});	

$('#printReport').click(function(){
	var tk = qs['tk'];
	var xform = document.getElementById('searchform').elements;
	var sdatefrom = xform.namedItem('datefrom').value;
	var sdateto = xform.namedItem('dateto').value;
	var trans = xform.namedItem('trans').value;
	var preparedby = xform.namedItem('preparedby').value;
	var notedby = xform.namedItem('notedby').value;

	$('#print-container').prop('hidden', true);
	$('#print-container-loader').prop('hidden', false);

	if (isNaN(Date.parse(sdatefrom)) || isNaN(Date.parse(sdateto))) {
		toastr.error("Please input valid dates");
	} else if (notedby === "" || preparedby === "") {
		toastr.error("Please choose the Name of Signatory");
	} else {
		var dateFrom = new Date(sdatefrom);
		var dateTo = new Date(sdateto);

		if (dateFrom > dateTo) {
			toastr.error("Start date cannot be greater than end date");
		} else {
			var printModal = $('#print-modal');
			var printContainer = $('#print-container');
			var printSrc = '';

			switch (trans) {
				case 'clientsbybrgy':
					var provcat = xform.namedItem('provcat').value;
					var sacode = xform.namedItem('acode').value;
					var dist = xform.namedItem('dist').value;
					var location = xform.namedItem('location').value;
					var txtReport = xform.namedItem('txtReport').value;

					if (txtReport === "")  {
						toastr.error("Please choose type of report");
					} 
					else if (txtReport === "totalNumberbyBrgy") {
						if (provcat === "") {
							toastr.error("Please choose Provider Category");
						} else if (location === "") {
							toastr.error("Please choose Processing Location");
						} else {
							printSrc = (sacode === "ALL" || sacode === "") ?
								'controllers/print/clientsbybrgyPrint.php' :
								'controllers/print/clientsbybrgyAssistancePrint.php';
							printModal.modal('show');
							printContainer.attr('src', `${printSrc}?tk=${tk}&datefrom=${sdatefrom}&dateto=${sdateto}&preparedby=${preparedby}&notedby=${notedby}&provcat=${provcat}&acode=${sacode}&location=${location}`);
						}
					} else if (txtReport === "diagnosisperBrgy") {
						if (dist === "") {
							toastr.error("Please choose District");
						} else {
							printSrc = 'controllers/print/diagnosisperbrgyPrint.php';
							printModal.modal('show');
							printContainer.attr('src', `${printSrc}?tk=${tk}&datefrom=${sdatefrom}&dateto=${sdateto}&preparedby=${preparedby}&notedby=${notedby}&dist=${dist}`);
						}
					}
					break;

				case 'clientsServedSummary':
					printSrc = 'controllers/print/clientsServedSummaryPrint.php';
					printModal.modal('show');
					printContainer.attr('src', `${printSrc}?tk=${tk}&datefrom=${sdatefrom}&dateto=${sdateto}&preparedby=${preparedby}&notedby=${notedby}`);
					break;

				case 'accomplishment':
					location = xform.namedItem('location').value;
					printSrc = 'controllers/print/accomplishmentReportPrint.php';
					printModal.modal('show');
					printContainer.attr('src', `${printSrc}?tk=${tk}&datefrom=${sdatefrom}&dateto=${sdateto}&preparedby=${preparedby}&notedby=${notedby}&location=${location}`);
					break;

				case 'cancelledapp':
					location = xform.namedItem('location').value;
					printSrc = 'controllers/print/cancelledAppPrint.php';
					printModal.modal('show');
					printContainer.attr('src', `${printSrc}?tk=${tk}&datefrom=${sdatefrom}&dateto=${sdateto}&preparedby=${preparedby}&notedby=${notedby}&location=${location}`);
					break;

				case 'overrideapp':
					location = xform.namedItem('location').value;
					printSrc = 'controllers/print/overrideAppPrint.php';
					printModal.modal('show');
					printContainer.attr('src', `${printSrc}?tk=${tk}&datefrom=${sdatefrom}&dateto=${sdateto}&preparedby=${preparedby}&notedby=${notedby}&location=${location}`);
					break;
			}
		}
}

});

$('#print-container').on('load', (e) => {
	iframeLoaded(e);
});

function iframeLoaded(event) {
	$('#print-container').prop('hidden',false);
	$('#print-container-loader').prop('hidden',true);
}