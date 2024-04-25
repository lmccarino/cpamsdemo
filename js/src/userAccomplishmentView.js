function startload(){
	var otk = document.getElementsByName('tk');
	for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
	{
	    otk[x].value = qs['tk'];
	}

	let xform = document.getElementById('searchform').elements;
	xform.namedItem('datefrom').value = configuredate();
	xform.namedItem('dateto').value = configuredate();
	xform.namedItem('daily').value = configuredate();


	var i = '1434';
	$.get("controllers/masterlistViewProviderController.php",{trans:"getsignatory",tk:qs['tk'], office:i}, function(data){
		var x = document.getElementById('searchform');
		var sign = x.elements.namedItem('preparedby');
		var row = data['data'];		
		for (a = 0;a < row.length; a++){
			var option = document.createElement("option");
							option.text = row[a]['fullname'];
							option.value = row[a]['userid'];
							sign.add(option); 
		}

		var sign2 = x.elements.namedItem('notedby');
		var row2 = data['data'];		
		for (a = 0;a < row2.length; a++){
			var option2 = document.createElement("option");
							option2.text = row2[a]['fullname'];
							option2.value = row2[a]['userid'];
							sign2.add(option2); 
		}
	},"json");

	$.get("controllers/masterlistViewProviderController.php",{trans:"getsysuser",tk:qs['tk']}, function(data){
		var x = document.getElementById('searchform');
		var seluser = x.elements.namedItem('selectuser');
		var row3 = data['data'];		
		for (a = 0;a < row3.length; a++){
			var option3 = document.createElement("option");
							option3.text = row3[a]['fullname'];
							option3.value = row3[a]['userid'];
							seluser.add(option3); 
		}
	},"json");
}

$(document).ready(function(){
	$('#txtReport').change(function(){
		var optval = $('#txtReport').val();
		$('#userSelect').attr('style', 'display: none;');
		$('#dateDuration').attr('style', 'display: none;');
		$('#dailyRpt').attr('style', 'display: none;');
		if (optval == "clientlist") {
			$('#userSelect').removeAttr('style');
			$('#dateDuration').removeAttr('style');
		} else if (optval == "totalperUser") {
			$('#dateDuration').removeAttr('style');
		} else if (optval == "durationperUser") {
			$('#dailyRpt').removeAttr('style');
		}
		else {
			$('#userSelect').attr('style', 'display: none;');
			$('#dateDuration').attr('style', 'display: none;');
			$('#dailyRpt').attr('style', 'display: none;');
		}
	});	
});

$('#printReport').click(function(){
	var tk = qs['tk'];
	let xform = document.getElementById('searchform').elements;
	let sdatefrom = xform.namedItem('datefrom').value;
	let txtReport = xform.namedItem('txtReport').value;
	let preparedby = xform.namedItem('preparedby').value;
	let notedby = xform.namedItem('notedby').value;
	let sdateto = xform.namedItem('dateto').value;
	let sdaily = xform.namedItem('daily').value;
	let selectuser = xform.namedItem('selectuser').value;

	$('#print-container').prop('hidden',true);
	$('#print-container-loader').prop('hidden',false);

	if (txtReport === "")  {
		toastr.error("Please choose type of report");
	} 
	else if ((notedby === "")||(preparedby === ""))  {
		toastr.error("Please choose the Name of Signatory");
	}  
	else {
		if (txtReport == "durationperUser") {
			if (isNaN(Date.parse(sdaily))) {
				toastr.error("Please input valid date");
			} else{
				$('#print-modal').modal('show');
				$('#print-container').attr('src', 'controllers/print/dailyEncodeIndiPrint.php?tk='+tk+'&datefrom='+sdaily+'&notedby='+notedby+'&preparedby='+preparedby);  
			}
		} else {
			if (isNaN(Date.parse(sdatefrom)) || isNaN(Date.parse(sdateto)))  {
				toastr.error("Please input valid dates");
			} else {
				var dateFrom = new Date(sdatefrom);
				var dateTo = new Date(sdateto);
			
				if (dateFrom > dateTo) {
					toastr.error("Start date cannot be greater than end date");
				} else if (txtReport == "clientlist") {
					if (selectuser === "")  {
						toastr.error("Please choose System User");
					} else {
						$('#print-modal').modal('show');
						$('#print-container').attr('src', 'controllers/print/clientlist.php?tk='+tk+'&datefrom='+sdatefrom+'&dateto='+sdateto+'&sysuser='+selectuser+'&notedby='+notedby+'&preparedby='+preparedby);  
					}
				}  else if (txtReport == "totalperUser") {
					$('#print-modal').modal('show');
					$('#print-container').attr('src', 'controllers/print/dailyEncodeReportPrint.php?tk='+tk+'&datefrom='+sdatefrom+'&dateto='+sdateto+'&notedby='+notedby+'&preparedby='+preparedby);  
				} 
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