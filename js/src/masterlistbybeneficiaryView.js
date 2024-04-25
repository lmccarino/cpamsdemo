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

$('#printreport').click(function(){ 
	var tk = qs['tk'];
	let xform = document.getElementById('searchform').elements;
	let sdatefrom = xform.namedItem('datefrom').value;
	let sdateto = xform.namedItem('dateto').value;
	let preparedby = xform.namedItem('preparedby').value;
	let notedby = xform.namedItem('notedby').value;
	let provcat = xform.namedItem('provCat').value;

	let location = "";
	
	if (xform.namedItem('location')) {
	  location = xform.namedItem('location').value;
	}

	$('#print-container').prop('hidden',true);
	$('#print-container-loader').prop('hidden',false);

	if (isNaN(Date.parse(sdatefrom)) || isNaN(Date.parse(sdateto)))  {
		toastr.error("Please input valid dates");
	}
	else if (provcat === "")  {
		toastr.error("Please choose Provider Category");
	}  
	else if ((notedby === "")||(preparedby === ""))  {
		toastr.error("Please choose the Name of Signatory");
	}  
	else {
		var dateFrom = new Date(sdatefrom);
		var dateTo = new Date(sdateto);
	
		if (dateFrom > dateTo) {
			toastr.error("Start date cannot be greater than end date");
		} else {
			$('#print-modal').modal('show');
			$('#print-container').attr('src', 'controllers/print/masterlistbybeneficiaryPrint.php?from='+sdatefrom+'&to='+sdateto+'&cat='+provcat+'&loc='+location+'&preparedby='+preparedby+'&notedby='+notedby);	
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