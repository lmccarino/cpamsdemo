function startload(){
	var otk = document.getElementsByName('tk');
	for(var x=0; x < otk.length; x++)   
	{
	    otk[x].value = qs['tk'];
	}

	let xform = document.getElementById('searchform').elements;
	xform.namedItem('datefrom').value = configuredate();
	xform.namedItem('dateto').value = configuredate();

	$.get("controllers/providerGLcontroller.php",{trans:"getsignatory",tk:qs['tk'], loc:soffice}, function(data){
		var x = document.getElementById('searchform');
		for (a = 0;a < data['data'].length; a++) {
			var option = document.createElement("option");
				option.text  = data['data'][a]['fullname'];
				option.value = data['data'][a]['userid'];

			x.elements.namedItem('preparedby').add(option.cloneNode(true));
			x.elements.namedItem('notedby').add(option); 
		}
	},"json");
} 

$('#printReport').click(function(){
	var tk = qs['tk'];
	var xform = document.getElementById('searchform').elements;
	var sdatefrom = xform.namedItem('datefrom').value;
	var sdateto = xform.namedItem('dateto').value;
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

			printSrc = 'controllers/print/soaPrint.php';
			printModal.modal('show');
			printContainer.attr('src', `${printSrc}?tk=${tk}&datefrom=${sdatefrom}&dateto=${sdateto}&preparedby=${preparedby}&notedby=${notedby}&loc=${soffice}`);
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