let _list = [];

function startload() {
	document.getElementById('details').style.display = "block";
	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options);
	
	$('#ds').dataTable().fnDestroy();
	if ($('#ds tbody').empty()) {
		var tk=qs['tk'];

		var table = $('#ds').DataTable( {
			dom: 'lfrtip', 
			// "ajax": "controllers/approvedController.php?trans=LIST&tk="+tk,
			/*
			"ajax": {
				"url": "controllers/approvedController.php?trans=LIST&tk=" + tk + "&office=" + soffice,
				"dataSrc": function (json) {
					if(json.data.length > 0){
						for(let i = 0; i < json.data.length; i++){
							json.data[i]['billAmount'] = numberWithCommas(parseFloat(json.data[i]['billAmount']).toFixed(2));
							json.data[i]['amtApproved'] = numberWithCommas(parseFloat(json.data[i]['amtApproved']).toFixed(2));
						}
					}

					return json.data;
				}
			},
			"columns": [
				{"className":      'details-control',
													"orderable":      false,
													"data":           null,
													"defaultContent": ''},
				{ "data": "benLName" },
				{ "data": "benFName" },
				{ "data": "benMName" },
				{ "data": "noteTag" },
				{ "data": "billAmount" },             
				{ "data": "amtApproved"},
				{ "data": function (o) { return recordTask(o); }},
				//{"className":'taskBtn',"defaultContent": '<div class="dropdown"><button type="button" data-toggle="tooltip" title="Override" class="btn btn-light" ><i class="fa-solid fa-list-check" aria-hidden="true"></i></button><div class="dropdown-content"><a><i class="fa-solid fa-rotate-left btnDisApprove"></i> DisApprove </a><a><i class="fa-solid fa-print btnGL"></i> GL </a></a><a><i class="fa-solid fa-print btnIntake"></i> Intake </a></a><a><i class="fa-solid fa-print btnCert"></i> Certificate </a></div></div>'}	
			],
			"order": [[0, 'desc']]
			*/
		} );
	}
	$('#ds tbody').off();

    $('#ds tbody').on( 'click', 'td.taskBtn', function (o) {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		deletethis(row.data());
	} );

	$('#ds tbody').on('click', 'td.details-control', function () {
		var tr = $(this).closest('tr');
		var row = table.row( tr );

		if ( row.child.isShown() ) {
			// This row is already open - close it
			row.child.hide();
			tr.removeClass('shown');
		}
		else {
			// Open this row
			row.child( format(row.data())).show();
			tr.addClass('shown');
			getchild(row.data());
		}
	});
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function searchRaf() {
	
	document.getElementById('details').style.display = "block";
	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options);
	var raf = $("#rafSearch").val();	
	var fr= "";
	var to = "";
	var ajx= "";
	var tk=qs['tk'];
/*
	if (raf.trim() == "") {
		fr = $("#dteFrom").val();	
		if (fr == "") {return false;}
		to = $("#dteTo").val();	
		if (to == "") {return false;}
		ajx = "controllers/approvedController.php?trans=LIST3&tk=" + tk + "&fr=" + fr + "&to=" + to + "&office=" + soffice;
		//return false;
	}
	else {
		ajx = "controllers/approvedController.php?trans=LIST2&tk="+tk+"&search=" + raf + "&office=" + soffice;
	}
*/
	if ($("#searchForm").serialize().trim().length <= 33 )
		return false;
	
	_list = [];

	ajx = "controllers/approvedController.php?trans=LIST7&tk=" + tk + "&office=" + soffice + "&status=APPROVED&" + $("#searchForm").serialize();
	
	$('#ds').dataTable().fnDestroy();
	if ($('#ds tbody').empty()) {

		var table = $('#ds').DataTable( {
			dom: 'lfrtip', 
			"ajax": ajx,
			"columns": [
				{"className":      'details-control',
													"orderable":      false,
													"data":           null,
													"defaultContent": ''},
				{ "data": "rafNum" },
				{ "data": "benLName" },
				{ "data": "benFName" },
				{ "data": "benMName" },
				{ "data": "requestor" },
				{ "data": "assistCode" },
				{ "data": "billAmount" },             
				{ "data": "amtApproved"},
				{ "data": function (o) { return recordTask(o); }},
				//{"className":'taskBtn',"defaultContent": '<div class="dropdown"><button type="button" data-toggle="tooltip" title="Override" class="btn btn-light" ><i class="fa-solid fa-list-check" aria-hidden="true"></i></button><div class="dropdown-content"><a><i class="fa-solid fa-rotate-left btnDisApprove"></i> DisApprove </a><a><i class="fa-solid fa-print btnGL"></i> GL </a></a><a><i class="fa-solid fa-print btnIntake"></i> Intake </a></a><a><i class="fa-solid fa-print btnCert"></i> Certificate </a></div></div>'}	
			],
			"order": [[0, 'desc']]
		} );
	}
	$('#ds tbody').off();

    $('#ds tbody').on( 'click', 'td.taskBtn', function (o) {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		deletethis(row.data());
	} );

	$('#ds tbody').on('click', 'td.details-control', function () {
		var tr = $(this).closest('tr');
		var row = table.row( tr );

		if ( row.child.isShown() ) {
			// This row is already open - close it
			row.child.hide();
			tr.removeClass('shown');
		}
		else {
			// Open this row
			row.child( format(row.data())).show();
			tr.addClass('shown');
			getchild(row.data());
		}
	});
	
}

function recordTask(o) {
	_list.push(o);

	var id = "'"+o['idassistdetails']+"'";
	// var bill = "'"+o['billAmount']+"'";
	// var lname = "'"+o['benLName']+"'";
	// var fname = "'"+o['benFName']+"'";
	// var mname = "'"+o['benMName']+"'";
	// var note = "'"+o['noteTag']+"'";
	// var amt = "'"+o['amtApproved']+"'";
	// var raf = "'"+o['rafNum']+"'";
	// var dte = "'"+o['dateReceive']+"'";
	// var loc = "'"+o['procloc']+"'";
	// var addr = "'"+o['benAddrSt']+"'";
	// var brgy = "'"+o['brgyName']+"'";
	// var cas = "'"+(o['remarks'].trim().replace(/\r/g, ''))+"'";
	// var provider = "'"+o['provCode']+"'";
	// var rem = "'"+(o['remTag'].trim().replace(/\r/g, ''))+"'";
	// var amtApproved = "'"+o['amtApproved']+"'";
	// var assistCode = "'"+o['assistCode']+"'";

	let dataAssistCode	   = o['assistCode'];
	let dataAmountApproved = o['amtApproved'];
	let dataBillAmount     = o['billAmount'];
	let isDisable = false;

	if(dataAssistCode.includes('MEDICINE') && dataBillAmount != dataAmountApproved) isDisable = true;

	// Author: Teddy C. 09/21/2023 13:12.
	// let dateApproved = o['dateApproved'];
	// let allowRegenerate = false;
	// const now = new Date();
	// const formattedNow = now.toISOString().slice(0, 10);
	// const formattedApproved = dateApproved.split(' ')[0];

	// if (formattedApproved === formattedNow) {
	// 	allowRegenerate = true;
	// }
	// End Teddy C.

	// Author: Teddy C. 10/11/2023 09:07.
	let allowRegenerate = false;
	let allowRegenerateGL = false;
	const now = new Date();
	
	// Calculate the difference in days between Approved and Current Date.
	let dateApproved = o['dateApproved'];

	// UPDATED by Teddy C. 12/27/2023 15:26.
	if (role.includes('1') || role.includes('2')) allowRegenerate = true;
	// FROM
	// if(dateApproved){
	// 	const formattedApproved = dateApproved.split(' ')[0];
	// 	const approvedDate = new Date(formattedApproved);
	// 	const approvedDifference = Math.floor((now - approvedDate) / (1000 * 60 * 60 * 24));
	// 
	// 	if (approvedDifference <= 5) allowRegenerate = true;
	// }
	// END of UPDATED.

	// Calculate the difference in days between Reissue and Current Date.
	let dateReissued = o['dateReissue'];

	
	// UPDATED by Teddy C. 12/27/2023 15:26.
	if (role.includes('1') || role.includes('2')) allowRegenerateGL = true;
	// FROM
	// if(dateReissued){
	// 	const formattedReissued = dateReissued.split(' ')[0];
	// 	const reissuedDate = new Date(formattedReissued);
	// 	const reissuedDifference = Math.floor((now - reissuedDate) / (1000 * 60 * 60 * 24));
		
	// 	if (reissuedDifference <= 5) allowRegenerateGL = true;
	// }
	// END of UPDATED.

	// End Teddy C.
	
	let isCurrentYear = false;
	if(dateApproved){
		const formattedApproved = dateApproved.split(' ')[0];
		const approvedDate = new Date(formattedApproved);
		
		isCurrentYear = (approvedDate.getFullYear() === now.getFullYear());
	}



	// var callCancel = "showCancel("+id+", "+bill+", "+lname+", "+fname+", "+mname+", "+note+", "+amt+",  "+raf+", "+dte+", "+loc+", "+addr+", "+brgy+", "+cas+", "+provider+", "+rem+")";
	// var callReapprove = "showReapprove("+id+", "+bill+", "+lname+", "+fname+", "+mname+", "+note+", "+amt+",  "+raf+", "+dte+", "+loc+", "+addr+", "+brgy+", "+cas+", "+provider+", "+rem +", "+amtApproved+", "+assistCode+")";
	var callCancel = "showCancel("+id+")";
	var callReapprove = "showReapprove("+id+")";

	//var callDisapprove = "showDisapprove("+id+", "+bill+", "+lname+", "+fname+", "+mname+", "+note+", "+amt+",  "+raf+", "+dte+", "+loc+", "+addr+", "+brgy+", "+cas+", "+provider+", "+rem+")";
	//var callDisapprove = "showDisapprove("+id+")";
	var callIntake = "showIntake("+id+")";
	var callCert = "showEligibility("+id+")";
	var callSMS = "inform("+id+")";
	var callGL = "showGL("+id+")";
	var callIndigency = "showIndigency("+id+")";
	var callReissue = "reissue("+id+")";
	var callRegenerateEligibility = "regenerateEligibility("+id+")";
	var callRegenerateGL = "regenerateGL("+id+")";
	var callRegenerateDocuments = "regenerateDocuments("+id+")";
	let assistid = o['idassistdetails'];
	var row = '<div class="overridedropdown">'+
					'<button type="button" title="Task" class="btn overridedropbtn">'+
						'<i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>'+
					'</button>'+
					'<div class="overridedropdown-content">';
						if ((role.includes('1') || role.includes('2')) && isCurrentYear){
							row += '<a onclick="'+callCancel+'" class="btn btn-warning mx-1 py-1 px-2">Cancel</a>';
							row += '<a onclick="'+callReapprove+'" class="btn btn-success mx-1 py-1 px-2">Override</a>';
							if (!isDisable)
								row += '<a onclick="'+callReissue+'" class="btn btn-primary mx-1 py-1 my-1 px-2">Reissue</a>';
						}
						if (!isDisable)
							row += '<a onclick="'+callSMS+'" class="btn btn-primary mx-1 py-1 my-1 px-2">Send SMS</a>'+
							'<a onclick="'+callGL+'" class="btn btn-info mx-1 py-1 px-2">GL</a>'+
							'<a onclick="'+callIntake+'" class="btn btn-info mx-1 py-1 px-2">Intake</a>'+
							'<a onclick="'+callCert+'" class="btn btn-info mx-1 py-1 my-1 px-2">Eligibility</a>'+
							'<a onclick="'+callIndigency+'" class="btn btn-info mx-1 my-1 py-1 px-2">Indigency</a>'+
							`${((allowRegenerateGL === true || assistid == 300391) && isCurrentYear) ? `<a onclick="${callRegenerateGL}" class="btn btn-primary mx-1 py-1 my-1 px-2">Regenerate GL</a>` : ''}`+
							`${((allowRegenerate === true || assistid == 310436) && isCurrentYear) ? `<a onclick="${callRegenerateDocuments}" class="btn btn-primary mx-1 py-1 my-1 px-2">Regenerate Documents</a>` : ''}`+
					'</div>'+
				'</div>';	
	return row;
}

let regenerating = false;
function regenerateEligibility(id){
	if(regenerating == false){
		regenerating = true;
		$.get("controllers/renderCertEligibilityController.php",{"tk":qs['tk'],"idassistdetails":id},"json")
		.always(function () {
			regenerating = false;
	
			$('#print-gl-modal').modal('show');
			$('#print-gl-container').prop('hidden',false);	
			$('#print-gl-container').attr('src', 'controllers/showCertEligibilityController.php?idassistdetails='+id);	
		});
	}
}

function regenerateGL(id){
	Swal.fire({
		title: 'Regenerate Guarantee Letter',
		text: "This will replace the document you previously generated.",
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Generate',
		showLoaderOnConfirm: true,
		allowOutsideClick: () => !swal.isLoading(),
		preConfirm: () => {
			return $.get("controllers/renderGuaranteeLetterController.php",{"tk":qs['tk'],"idassistdetails":id},"json")
					.done(function (response) {
						// Success: response should be a JSON object
						$('#print-gl-modal').modal('show');
						$('#print-gl-container').prop('hidden', false);
						$('#print-gl-container').attr('src', 'controllers/showGLController.php?idassistdetails=' + id);
					})
					.fail(function (jqXHR, textStatus, errorThrown) {
						// Handle errors here
						Swal.fire({
							title: 'Error',
							text: 'An error occurred while fetching data.',
							icon: 'error'
						});

						// Close the modal
						$('#print-gl-modal').modal('hide');
					})
		}
	});
}

function regenerateDocuments(id){
	Swal.fire({
		title: 'Regenerate Documents',
		text: "This will replace the document you previously generated.",
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Generate',
		showLoaderOnConfirm: true,
		allowOutsideClick: () => !swal.isLoading(),
		preConfirm: () => {
			return $.when(
						$.get("controllers/renderGuaranteeLetterController.php", {"tk": qs['tk'], "idassistdetails": id}, "json"),
						$.get("controllers/renderCertEligibilityController.php", {"tk": qs['tk'], "idassistdetails": id}, "json"),
						$.get("controllers/renderCertIndigencyController.php", {"tk": qs['tk'], "idassistdetails": id}, "json"),
						$.get("controllers/renderIntakeFormController.php", {"tk": qs['tk'], "idassistdetails": id}, "json")
					).then(function () {
						Swal.fire(
							'Document Generated',
							'Your files have been uploaded to the file server.',
							'success'
						);
					}).fail(function () {
						// Handle errors here
						Swal.close(); // Close the modal
					});
		}
	});
}

function showEligibility(id) {
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	// $('#print-gl-container').attr('src', 'controllers/print/printEligibility.php?idassistdetails='+id);		
	$('#print-gl-container').attr('src', 'controllers/showCertEligibilityController.php?idassistdetails='+id);		

}

function showIndigency(id) {
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/showCertIndigencyController.php?idassistdetails='+id);	
}

function showIntake(id) {
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/showIntakeFormController.php?idassistdetails='+id);	
}
function reissue(xidassistdetails){
	Swal.fire({
		title: 'Reissue Guarantee Letter',
		text: "Do you want to reissue guarantee letter? This will also generate new Guarantee Letter.",
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Confirm',
		showLoaderOnConfirm: true,
		allowOutsideClick: () => !swal.isLoading(),
		preConfirm: () => {
			return $.ajax({
						url: 'controllers/rafController.php',
						type: "POST",      
						cache: false,
						dataType: 'json',
						data: {
							trans:'reissue',
							idassistdetails: xidassistdetails,
							tk: qs['tk']
						},        
						success: function(result) {
							if (result.msg){
								$.get("controllers/renderGuaranteeLetterController.php", {"tk": qs['tk'], "idassistdetails": xidassistdetails}, "json");
								toastr.success(result.msg);
							}
							else
								toastr.error(result.error);
						},
						error: function(result){
							console.log(result);		
						}
					});
		}
	});
}
function inform(xidassistdetails){
	$.ajax({
		url: 'controllers/notifyController.php',
		type: "POST",      
		cache: false,
		dataType: 'json',
		data: {
			trans:'available',
			idassistdetails: xidassistdetails,
			tk: qs['tk']
		},        
		success: function(result) {
			if (result.msg)
				toastr.success(result.msg);
			else
				toastr.error(result.error);
		},
		error: function(result){
			console.log(result);		
		}
	});
}
function showGL(id) {
	// alert(id);
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/showGLController.php?idassistdetails='+id);	
}

function showDisapprove(id, bill, lname, fname, mname, note, amt, raf, dte, loc, addr, brgy, cas, provider, rem) {
	var button = document.getElementById('btnSave');
	button.innerText = button.textContent = 'For Override';
	$("#trans").val("OVERRIDE");	
	$("#id").val(id);	
	$("#lname").val(lname);	
	$("#fname").val(fname);	
	$("#mname").val(mname);	
	$("#remarks").val(rem);	
	$("#noteTag").val(note);	
	$("#bill").val(bill);	
	$("#amt").val(amt);	
	$("#tk").val(qs['tk']);	

	$('#raf').val(raf);	
	$('#dte').val(dte);	
	$('#loc').val(loc);	
	$('#addr').val(addr);	
	$('#brgy').val(brgy);	
	$('#case').val(cas);	
	$('#provider').val(provider);	

	$('#overrideModal').modal("show");
}

// function showCancel(id, bill, lname, fname, mname, note, amt, raf, dte, loc, addr, brgy, cas, provider, rem) {
// 	var button = document.getElementById('btnSave');
// 	button.innerText = button.textContent = 'Cancel';
// 	$("#trans").val("CANCELLED");	
// 	$("#id").val(id);	
// 	$("#lname").val(lname);	
// 	$("#fname").val(fname);	
// 	$("#mname").val(mname);	
// 	$("#remarks").val(rem);	
// 	$("#noteTag").val(note);	
// 	$("#bill").val(bill);	
// 	$("#amt").val(amt);	
// 	$("#tk").val(qs['tk']);	

// 	$('#raf').val(raf);	
// 	$('#dte').val(dte);	
// 	$('#loc').val(loc);	
// 	$('#addr').val(addr);	
// 	$('#brgy').val(brgy);	
// 	$('#case').val(cas);	
// 	$('#provider').val(provider);	

// 	$('#overrideModal').modal("show");
// }
function showCancel(id) {
    $.each(_list, function (index, item) {
		if(item.idassistdetails == id){
			var button = document.getElementById('btnSave');
			button.innerText = button.textContent = 'Cancel';
			
			$("#trans").val("CANCELLED");	
			$("#id").val(item.idassistdetails);	
			$("#lname").val(item.benLName);	
			$("#fname").val(item.benFName);	
			$("#mname").val(item.benMName);	
			$("#remarks").val(item.remTag);	
			$("#noteTag").val(item.noteTag);	
			$("#bill").val(item.billAmount);	
			$("#amt").val(item.amtApproved);	
			$("#tk").val(qs['tk']);	
		
			$('#raf').val(item.rafNum);	
			$('#dte').val(item.dateReceive);	
			$('#loc').val(item.procloc);	
			$('#addr').val(item.benAddrSt);	
			$('#brgy').val(item.brgyName);	
			$('#case').val(item.remarks);	
			$('#provider').val(item.provCode);	
			$('#assistcode').val(item.assistCode);	
		
			$('#overrideModal').modal("show");
		}

	});
}

// function showReapprove(id, bill, lname, fname, mname, note, amt, raf, dte, loc, addr, brgy, cas, provider, rem, amtApproved, assistCode) {
// 	var button = document.getElementById('btnSave');
// 	button.innerText = button.textContent = 'Override';
// 	document.getElementById('amt').readOnly = false;

// 	$("#trans").val("REAPPROVE");	
// 	$("#id").val(id);	
// 	$("#lname").val(lname);	
// 	$("#fname").val(fname);	
// 	$("#mname").val(mname);	
// 	$("#remarks").val(rem);	
// 	$("#noteTag").val("");	
// 	$("#bill").val(bill);
// 	$("#amt").val(parseFloat(amt.replace(',','')));
// 	$("#allocated").val(amtApproved);
// 	$("#tk").val(qs['tk']);	

// 	$('#raf').val(raf);	
// 	$('#dte').val(dte);	
// 	$('#loc').val(loc);	
// 	$('#addr').val(addr);	
// 	$('#brgy').val(brgy);	
// 	$('#case').val(cas);	
// 	$('#provider').val(provider);	
// 	$('#assistcode').val(assistCode);	

// 	$('#overrideModal').modal("show");
// }

function showReapprove(id) {
	
    $.each(_list, function (index, item) {
		if(item.idassistdetails == id){
			var button = document.getElementById('btnSave');
			button.innerText = button.textContent = 'Override';
			document.getElementById('amt').readOnly = false;
		
			$("#trans").val("REAPPROVE");	
			$("#id").val(item.idassistdetails);	
			$("#lname").val(item.benLName);	
			$("#fname").val(item.benFName);	
			$("#mname").val(item.benMName);	
			$("#remarks").val(item.remTag);	
			$("#noteTag").val("");	
			$("#bill").val(item.billAmount);	
			$("#amt").val(parseFloat(item.amtApproved.replace(',','')));
			$("#allocated").val(item.amtApproved);
			$("#tk").val(qs['tk']);	
		
			$('#raf').val(item.rafNum);	
			$('#dte').val(item.dateReceive);	
			$('#loc').val(item.procloc);	
			$('#addr').val(item.benAddrSt);	
			$('#brgy').val(item.brgyName);	
			$('#case').val(item.remarks);	
			$('#provider').val(item.provCode);	
			$('#assistcode').val(item.assistCode);	

			$('#overrideModal').modal("show");
		}

	});
}


function format(row){
	var id = row['idpatient'];

	var elem = document.getElementById(id);
	if (elem){
  		elem.parentNode.removeChild(id);
	}
  	return '<div class="col-12" id="'+id+'"></div>';
}

function getchild(data){
    var idpatient = '#'+data['idpatient'];
    document.getElementsByName('myfields')[0].disabled = true;
    //var nform = $('#entryform').clone(true);
    var tform = document.getElementById('form'+data['idpatient']);
    if (tform){
        tform.parentNode.removeChild('form'+data['idpatient']);
    }
    var nform = $('#entryform').clone(true).prop('id','form'+data['idpatient']);
    nform.appendTo( idpatient);

    var x = document.getElementById('form'+data['idpatient']);
	x.elements.namedItem('tk').value=qs['tk'];
	x.elements.namedItem('raf').value=data['rafNum'];
	x.elements.namedItem('dte').value=data['dateReceive'];
	x.elements.namedItem('loc').value=data['procloc'];
	x.elements.namedItem('lname').value=data['benLName'];
	x.elements.namedItem('fname').value=data['benFName'];
	x.elements.namedItem('mname').value=data['benMName'];
	x.elements.namedItem('mname').value=data['benMName'];
	x.elements.namedItem('addr').value=data['benAddrSt'];
	x.elements.namedItem('brgy').value=data['brgyName'];
	x.elements.namedItem('case').value=data['remarks'];
	x.elements.namedItem('provider').value=data['provCode'];
	//x.elements.namedItem('note').value=data['noteTag'];
	x.elements.namedItem('bill').value=data['billAmount'];
	x.elements.namedItem('amt').value=data['amtApproved'];
	x.elements.namedItem('noteTag').value=data['noteTag'];
}

function saveThis(thisform){
	var data =  $("#entryform").serialize();
	var id =  $("#id").val();
	var trans = $('#trans').val();
	
	if (trans=="REAPPROVE"){
		var $amt =  parseFloat((($("#amt").val()).replace(',','')));
		var $allocated =  parseFloat((($("#allocated").val()).replace(',','')));
		var $bill =  parseFloat((($("#bill").val()).replace(',','')));

		if ($amt == "") {
			toastr.warning('Invalid amount! Please check...');
			return false;
		}
		if ($amt == "0") {
			toastr.warning('Invalid amount! Please check...');
			return false;
		}
		if ($amt <= $allocated) {
			toastr.warning('Invalid amount! Override should not be lesser or equal than allocated amount...');
			return false;
		}
		if ($amt > $bill) {
			console.log($amt);
			console.log($bill);
			toastr.warning('Invalid amount! Override should not be greater than the bill amount...');
			return false;
		}
		if(($('#assistcode').val()).includes('MEDICINE') && $bill != $amt){
			toastr.warning('Bill amount and Approved amount must be the same. Please adjust amount of medicine in RAF Correction.');
			return false;
		}
	}

	if($('#noteTag').val()==''){
		toastr.error('Please enter Reason for Cancel or Override.');
	}
	else {
		Swal.fire({
			title: (trans == "REAPPROVE") ? 'Override' : 'Cancel',
			text: `This action will ${(trans == "REAPPROVE") ? 'override' : 'cancel'} this RAF application.`,
			icon: 'info',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Save',
			showLoaderOnConfirm: true,
			width: 300,
			allowOutsideClick: () => !swal.isLoading(),
			preConfirm: () => {
				return $.ajax({
							url: 'controllers/approvedController.php',
							cache: false,
							type: 'get',
							data : data,
							dataType: 'json',
							success: function(result) {
								if (result == id) {
									$('#ds').DataTable().ajax.reload(null,false);
									toastr.success('Cancel/Override successfully done...');
									$('#overrideModal').modal("hide");
				
									if(trans=="REAPPROVE"){
										$.get("controllers/renderGuaranteeLetterController.php", {"tk": qs['tk'], "idassistdetails": id}, "json");
										$.get("controllers/renderCertEligibilityController.php", {"tk": qs['tk'], "idassistdetails": id}, "json");
										$.get("controllers/renderCertIndigencyController.php", {"tk": qs['tk'], "idassistdetails": id}, "json");
										$.get("controllers/renderIntakeFormController.php", {"tk": qs['tk'], "idassistdetails": id}, "json");
										notify();
									}
								} else {
									toastr.error('Cancelled/Override Failed. Please check details...');
								}
								document.getElementById('amt').readOnly = true;
							},
							error: function(xhr, status, error) {
								console.log(error);
								alert(xhr.responseText);
							}
						});
			}
		});
	}	
 }


function cancelthis(thisform){
	$('#ds').DataTable().ajax.reload(false,false);
	//document.getElementsByName('myfields').disabled = true;
	$('#officeModal').modal('hide');
}


//setTimeout(checkmodule, 1000);

function notify(){
	
	let allocated = $("#allocated").val();
	let amt = $("#amt").val();	

	$.get("controllers/extractrolesController.php",{"tk":qs['tk'],"trans":"getroles","roles[]":["SUPERVISOR","COS"]}, function (d){
		let patient = $("#lname").val() + ', '+ $("#fname").val() +' '+$("#mname").val();
		let rafno = $("#raf").val() ;
		let reason = $("#noteTag").val() ;

		let i = 0;
		while (i < d.length) {
			let d1 = d[i];
			let xmessage ="Good day "+ d1['fullname']+", CPAMSv2 informs you that an override has been approved for Patient: "+patient+" with RAF #: "+rafno+" from the amount of Php "+allocated+" to Php "+amt+". Reason: "+reason+". Thank you";
			let xdata = {"trans":"sendmsg", "tk":qs['tk'], "cellno":d1['cellno'],"email":d1['emailaddress'],"message":xmessage }
			let xdata1 = {"trans":"add_notification", "tk":qs['tk'], "message":xmessage, "user_id":d1['userid'],"title":"CPAMS - Override"};

			$.post("controllers/notifyController.php",xdata,"json").fail(function() {offlineerror();}); 
			$.get("https://cpams2.davaocity.gov.ph/controllers/notificationController.php",xdata1,"json").fail(function() {offlineerror();}); 
			i++;
		}

		toastr.success('COS & Supervisors already notified');
	},"json").fail(function() {offlineerror();});
	// toastr.success('Forwarded for override');
}