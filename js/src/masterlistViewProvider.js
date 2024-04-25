function startload(){
	var otk = document.getElementsByName('tk');
	for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
	{
	    otk[x].value = qs['tk'];
	}

	let xform = document.getElementById('searchform').elements;
	xform.namedItem('datefrom').value = configuredate();
	xform.namedItem('dateto').value = configuredate();
		var x = document.getElementById('searchform');
		$.get("controllers/masterlistViewProviderController.php",{trans:"getprocloc",tk:qs['tk']}, function(data){
			
			var pl = x.elements.namedItem('procloc');
			var row = data['data'];
			for (a = 0;a < row.length; a++){
				var option = document.createElement("option");
								option.text = row[a]['officename'];
								option.value = row[a]['idoffice'];
								pl.add(option); 
			}
		},"json");

		$.get("controllers/masterlistViewProviderController.php",{trans:"getassistance",tk:qs['tk']}, function(data){
			
			var acode = x.elements.namedItem('acode');
			var row = data['data'];
			for (a = 0;a < row.length; a++){
				var option = document.createElement("option");
								option.text = row[a]['assistCode'];
								option.value = row[a]['assistCode'];
								acode.add(option); 
			}
		},"json");
} 



$("#procloc").change(function(){
		var i = "";		
		if (this.value == 'ALL')	var i = '1434';
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

$("#provcat").change(function(){
		var i = (this.value);
		$.get("controllers/masterlistViewProviderController.php",{trans:"getprovider",tk:qs['tk'], provcat:i}, function(data){
		var x = document.getElementById('searchform');
		var pr = x.elements.namedItem('provider');
		var row2 = data['data'];
		pr.innerHTML = "";
		for (b = 0;b < row2.length; b++){
			var option = document.createElement("option");
							option.text = row2[b]['officename'];
							option.value = row2[b]['officecode'];
							pr.add(option); 
		}
	},"json"); 
}  );

$('#printReport').click(function(){
	var tk = qs['tk'];
	let xform = document.getElementById('searchform').elements;
	let sdatefrom = xform.namedItem('datefrom').value;
	let sdateto = xform.namedItem('dateto').value;
	let sprocloc = xform.namedItem('procloc').value;
	let sprovider = xform.namedItem('provider').value;
	let sprovcat = xform.namedItem('provcat').value;
	let sacode = xform.namedItem('acode').value;
	let spreparedby = xform.namedItem('preparedby').value;
	let snotedby = xform.namedItem('notedby').value;

	$('#print-container').prop('hidden',true);
	$('#print-container-loader').prop('hidden',false);
	
	if (isNaN(Date.parse(sdatefrom)) || isNaN(Date.parse(sdateto)))  {
		toastr.error("Please input valid dates");
	}
	else if ((snotedby === "")||(spreparedby === ""))  {
		toastr.error("Please choose the Name of Signatory");
	}  
	else if (sprovcat === "")  {
		toastr.error("Please choose Provider Category");
	}
	else {
		var dateFrom = new Date(sdatefrom);
		var dateTo = new Date(sdateto);
	
		if (dateFrom > dateTo) {
			toastr.error("Start date cannot be greater than end date");
		} else {
			$('#print-modal').modal('show');
			$('#print-container').attr('src', 'controllers/print/masterlistbyproviderPrint.php?tk='+tk+'&datefrom='+sdatefrom+'&dateto='+sdateto+'&procloc='+sprocloc+'&provider='+sprovider+'&provcat='+sprovcat+'&acode='+sacode+'&preparedby='+spreparedby+'&notedby='+snotedby);  
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