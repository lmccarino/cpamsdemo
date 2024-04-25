let _list = [];

function startload() {
	document.getElementById('details').style.display = "block";
	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options);

	var billformat = '';
	var amtformat = '';

	_list = [];
	
	$('#ds').dataTable().fnDestroy();
	if ($('#ds tbody').empty()) {
		var tk=qs['tk'];

		var table = $('#ds').DataTable( {
			dom: 'lfrtBip', 
			buttons: [
				'copy', {extend : 'excelHtml5',title : 'FOR OVERRIDES'
				, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true}, 
				{extend: 'pdf', 
				title : 'FOR OVERRIDES'
				, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true},
				{extend: 'print',
				title : 'FOR OVERRIDES',
				messageTop: caption, messageBottom: '<br/> Prepared by: '+ fullname + ' ' +stoday, footer:true
				
				}
			],
			"ajax": {
				"url": "controllers/overrideController.php?trans=LIST&tk=" + tk + "&office=" + soffice,	
				"dataSrc": function (json) {
					if(json.data.length > 0){
						for(let i = 0; i < json.data.length; i++){
							json.data[i]['dateReceive'] = formatDate(json.data[i]['dateReceive']);
							json.data[i]['billAmount'] = numberWithCommas(parseFloat(json.data[i]['billAmount']).toFixed(2));
							json.data[i]['amtApproved'] = numberWithCommas(parseFloat(json.data[i]['amtApproved']).toFixed(2));
						}
					}

					return json.data;
				}			
			},
			"columns": [
				// {"className":      'details-control',
				// 									"orderable":      false,
				// 									"data":           null,
				// 									"defaultContent": ''},
				{ "data": "rafNum" },
				{ "data": "benLName" },
				{ "data": "benFName" },
				{ "data": "benMName" },
				{ "data": "noteTag" },
				{ "data": "dateReceive" },  
				{ "data": "billAmount" },               
				{ "data": "amtApproved" },             
				             
				// { "data": "amtApproved" },               
				{ "data": function (o) { return linkOverride(o); }},
				//{"className":'deleteclassbutton1',"defaultContent": '<button type="button" data-toggle="tooltip" title="Override" class="btn btn-light" ><i class="fas fa-trash-alt" aria-hidden="true"></i></button>'}	
			],
			"order": [[0, 'desc']]
		} );
	}


	$('#ds tbody').off();

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

function formatDate(date) {
	date = new Date(date);
	var hours = date.getHours();
	var minutes = date.getMinutes();
	var ampm = hours >= 12 ? 'PM' : 'AM';
	hours = hours % 12;
	hours = hours ? hours : 12; // the hour '0' should be '12'
	minutes = minutes < 10 ? '0'+minutes : minutes;
	var strTime = hours + ':' + minutes + ' ' + ampm;
	return (date.getMonth()+1) + "/" + date.getDate() + "/" + date.getFullYear() + "  " + strTime;
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function linkOverride(o) {
	_list.push(o);

	// var id = "'"+o['idassistdetails']+"'";
	// var bill = "'"+o['billAmount']+"'";
	// var lname = "'"+o['benLName']+"'";
	// var fname = "'"+o['benFName']+"'";
	// var mname = "'"+o['benMName']+"'";
	// var note = "'"+o['noteTag']+"'";
	// var amt = "'"+o['amtApproved']+"'";
	// var raf = "'"+o['rafNum']+"'";
	// var dte = "'"+o['dateReceive']+"'";
	// var loc = "'"+o['officecode']+"'";
	// var addr = "'"+o['procloc']+"'";
	// var brgy = "'"+o['brgyCode']+"'";
	// var cas = "'"+o['hospcase']+"'";
	// var provider = "'"+o['provCode']+"'";

	// var callproc = "showOverride("+id+", "+bill+", "+lname+", "+fname+", "+mname+", "+note+", "+amt+",  "+raf+", "+dte+", "+loc+", "+addr+", "+brgy+", "+cas+", "+provider+")";
	var callproc = `showOverride(${o['idassistdetails']})`;
	// var row ='<button type="button" data-toggle="tooltip" title="Override Amount" class="btn btn-link" onclick="'+callproc+'">'+o['amtApproved']+'</button>';
	var row = '<div class="overridedropdown">'+
					'<button type="button" title="Task" class="btn overridedropbtn" onclick="'+callproc+'">'+
						'<i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>'+
					'</button>'+					
				'</div>';	
	return row;
}

function showOverride(id) {
    $.each(_list, function (index, item) {
		if(item.idassistdetails == id){
			document.getElementById('note').readOnly = false;

			$("#id").val(item.idassistdetails);	
			$("#lname").val(item.benLName);	
			$("#fname").val(item.benFName);	
			$("#mname").val(item.benMName);	
			$("#note").val(item.noteTag);	
			$("#bill").val(item.billAmount);	
			$("#amt").val(item.amtApproved);	
			$("#tk").val(qs['tk']);	

			$('#raf').val(item.rafNum);	
			$('#dte').val(item.dateReceive);	
			$('#loc').val(item.officecode);	
			$('#addr').val(item.procloc);	
			$('#brgy').val(item.brgyCode);	
			$('#case').val(item.hospcase);	
			$('#provider').val(item.provCode);	

			$('#overrideModal').modal("show");
		}
	});
}

// function showOverride(id, bill, lname, fname, mname, note, amt, raf, dte, loc, addr, brgy, cas, provider) {
// 	document.getElementById('note').readOnly = false;
	
// 	$("#id").val(id);	
// 	$("#lname").val(lname);	
// 	$("#fname").val(fname);	
// 	$("#mname").val(mname);	
// 	$("#note").val(note);	
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
	//x.elements.namedItem('trans').value='UPDATE';
	//x.elements.namedItem('idoffice').value=data['idoffice'];
	x.elements.namedItem('raf').value=data['rafNum'];
	x.elements.namedItem('dte').value=data['dateReceive'];
	x.elements.namedItem('loc').value=data['procloc'];
	x.elements.namedItem('lname').value=data['benLName'];
	x.elements.namedItem('fname').value=data['benFName'];
	x.elements.namedItem('mname').value=data['benMName'];
	x.elements.namedItem('mname').value=data['benMName'];
	x.elements.namedItem('addr').value=data['benAddrSt'];
	x.elements.namedItem('brgy').value=data['brgyCode'];
	x.elements.namedItem('case').value=data['hospCase'];
	x.elements.namedItem('provider').value=data['provCode'];
	x.elements.namedItem('note').value=data['noteTag'];
	x.elements.namedItem('bill').value=data['billAmount'];
	x.elements.namedItem('amt').value=data['amtApproved'];
	
	//x.elements.namedItem('myfields').disabled = true;
	
	//x.elements.namedItem('butsave').style.display='none';
	//x.elements.namedItem('butcancel').style.display='none';
}

function saveThis(thisform){
	let $amt = parseFloat($("#amtApproved").val());	
	// Replaced By: Teddy C. 09/15/2023 13:01.
	// let $allocated = parseFloat((($("#amt").val()).replace(',','')));	
	// let $bill = parseFloat((($("#bill").val()).replace(',','')));	 
	// ====================
	let $allocated = parseFloat((($("#amt").val()).replace(/,/g, '')));	
	let $bill = parseFloat((($("#bill").val()).replace(/,/g, '')));	 
	// End Teddy C.

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
		toastr.warning('Invalid amount! Override should not be greater than the bill amount...');
		return false;
	}
	if($('#note').val()==''){
		toastr.warning('Please enter Reason for Override.');
		return false;
	}
	if(($('#case').val()).includes('MEDICINE') && $bill != $amt){
		toastr.warning('Bill amount and Approved amount must be the same. Please adjust amount of medicine in RAF Correction.');
		return false;
	}

	// document.getElementById("btnGL").style.display = "block";
	// document.getElementById("btnIntake").style.display = "block";
	// document.getElementById("btnCert").style.display = "block";
	// document.getElementById("btnSave").style.display = "none";

	//var data =  $(thisform).serialize();

	$('#amt').val(`${$('#amt').val().replace(',','')}`);
	var data =  $("#entryform").serialize();
	var id =  $("#id").val();

	// Author: Teddy C. 09/15/2023 13:01.
	// $('#btnSave').html(`
	//   <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
	//   <span role="status">Loading...</span>
	// `).prop('disabled',true);
	// End Teddy C.

	Swal.fire({
		title: 'Override',
		html: `Do you want to override this application.`,
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Override',
		cancelButtonText: 'Cancel',
		showLoaderOnConfirm: true,
		width: 300,
		allowOutsideClick: () => !swal.isLoading(),
		preConfirm: () => {
			return $.ajax({
						url: 'controllers/overrideController.php',
						cache: false,
						type: 'get',
						data : data,
						dataType: 'json',
						success: function(data) {
							if (data["@id"] > -1){
								$('#ds').DataTable().ajax.reload(null,false);
								_list = [];
								toastr.success('Override successfully done...');
								$('#overrideModal').modal("hide");
								document.getElementById('note').readOnly = true;
								saveGL(id);
								notify();
							} else {
								toastr.warning('Override Failed. Might be insufficient of fund please check...');
								console.log(data)
							}  
						},
						error: function(xhr, status, error) {
							console.log(error);
							alert(xhr.responseText);
						},
						complete: function(){
							// Author: Teddy C. 09/15/2023 13:01.
							// $('#btnSave').html('Approve').prop('disabled',false);
							// End Teddy C.
						}
					});
		}
	});
}


function cancelthis(thisform){
	$('#ds').DataTable().ajax.reload(false,false);
	//document.getElementsByName('myfields').disabled = true;
	$('#officeModal').modal('hide');
}


let tmodule = false;
function checkmodule(){
	if (!tmodule){
		
		maccess = getmoduleaccess('Office');
		if (maccess['name']){ 
			tmodule = true;
			if (maccess['ladd']<1) {
				$(".addbutton").hide();
			}
		}
	}
};
setTimeout(checkmodule, 1000);


function saveGL(id){	
	let documents = 0;
	Swal.fire({
		title: 'Generating Documents',
		text: "(0/4) downloading document",
		icon: 'info',
		confirmButtonColor: '#3085d6',
		confirmButtonText: 'Generate',
		showLoaderOnConfirm: true,
	    didOpen: () => {
	        Swal.clickConfirm()
	    },
		allowOutsideClick: () => !swal.isLoading(),
		preConfirm: () => {
			return $.when(
						$.get("controllers/renderGuaranteeLetterController.php",{"idassistdetails":id},"json").always(function(){
							Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
						}),
						$.get("controllers/renderCertEligibilityController.php",{"tk":qs['tk'],"idassistdetails":id},"json").always(function(){
							Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
						}),
						$.get("controllers/renderCertIndigencyController.php",{"tk":qs['tk'],"idassistdetails":id},"json").always(function(){
							Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
						}),
						$.get("controllers/renderIntakeFormController.php",{"tk":qs['tk'],"idassistdetails":id},"json").always(function(){
							Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
						})
					).then(function () {
						// Success
					}).fail(function () {
						// Handle errors here
						Swal.close(); // Close the modal
					});
		}
	});
}

function notify(){

	let amt = $("#amtApproved").val();	
	let allocated = $("#amt").val();	

	$.get("controllers/extractrolesController.php",{"tk":qs['tk'],"trans":"getroles","roles[]":["SUPERVISOR","COS"]}, function (d){
		// let  x = document.getElementById('entryform').elements;
		// let xrafNum = x.namedItem('rafNum').value;
		// let xpatient = x.namedItem('benFName').value+' '+x.namedItem('benMName').value+' '+x.namedItem('benLName').value+' '+x.namedItem('suffix').value;

		let patient = $("#lname").val() + ', '+ $("#fname").val() +' '+$("#mname").val();
		let rafno = $("#raf").val() ;
		let reason = $("#note").val() ;
		let i = 0;

		while (i < d.length) {
			let d1 = d[i];
			let xmessage ="Good day "+ d1['fullname']+", CPAMSver2 informs you that an override has been approved for Patient: "+patient+", RAF#: "+rafno+" from the amount of Php "+allocated+" to Php "+amt+". Reason: "+reason+". Thank you";
			let xdata = {"trans":"sendmsg", "tk":qs['tk'], "cellno":d1['cellno'],"email":d1['emailaddress'],"message":xmessage }
			// let xdata1 = {"trans":"add_notification", "tk":qs['tk'], "message":xmessage, "user_id":d1['userid'],"title":"CPAMS - Override"};

			$.post("controllers/notifyController.php",xdata,"json").fail(function() {offlineerror();}); 
			// $.get("https://cpams2.davaocity.gov.ph/controllers/notificationController.php",xdata1,"json").fail(function() {offlineerror();}); 
			i++;
		}

		toastr.success('COS & Supervisors already notified');

	},"json").fail(function() {offlineerror();});
	// toastr.success('Forwarded for override');
}