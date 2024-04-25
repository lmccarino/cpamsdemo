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
			dom: 'lfrtBip', 
			buttons: [
				'copy', {extend : 'excelHtml5',title : 'FOR CSWDO'
				, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true}, 
				{extend: 'pdf', 
				title : 'FOR CSWDO'
				, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true},
				{extend: 'print',
				title : 'FOR CSWDO',
				messageTop: caption, messageBottom: '<br/> Prepared by: '+ fullname + ' ' +stoday, footer:true
				
				}
			],
			// "ajax": "controllers/approvedController.php?trans=LIST&tk="+tk,
			"ajax": {
				"url": "controllers/approvedController.php?trans=LIST6&tk="+tk,
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
				{ "data": "benLName" },
				{ "data": "benFName" },
				{ "data": "benMName" },				
				{ "data": "billAmount" },             
				{ "data": "amtApproved", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
				{ "data": "dateApproved" },       
				{ "data": function (o) { return recordTask(o); }},
				//{"className":'taskBtn',"defaultContent": '<div class="dropdown"><button type="button" data-toggle="tooltip" title="Override" class="btn btn-light" ><i class="fa-solid fa-list-check" aria-hidden="true"></i></button><div class="dropdown-content"><a><i class="fa-solid fa-rotate-left btnDisApprove"></i> DisApprove </a><a><i class="fa-solid fa-print btnGL"></i> GL </a></a><a><i class="fa-solid fa-print btnIntake"></i> Intake </a></a><a><i class="fa-solid fa-print btnCert"></i> Certificate </a></div></div>'}	
			],
			"order": [[5, 'asc']],
			"footerCallback": function ( row, data, start, end, display ) {
            							var api = this.api(), data;
										var intVal = function ( i ) {
               						 		return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 :
                    						typeof i === 'number' ?
                        					i : 0;
            							};
										total = api.column( 4 ).data().reduce( function (a, b) {
                    						return intVal(a) + intVal(b);
                						}, 0 );
										$( api.column( 4 ).footer() ).html( total.toLocaleString('en-US',{style:'currency',currency:'PHP'}) );
        							}
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

	if (raf.trim() == "") {
		fr = $("#dteFrom").val();	
		if (fr == "") {return false;}
		to = $("#dteTo").val();	
		if (to == "") {return false;}
		ajx = "controllers/approvedController.php?trans=LIST5&tk=" + tk + "&fr=" + fr + "&to=" + to;
	}
	else {
		ajx = "controllers/approvedController.php?trans=LIST4&tk="+tk+"&search=" + raf;
	}
	
	$('#ds').dataTable().fnDestroy();
	if ($('#ds tbody').empty()) {

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
			"ajax": ajx,
			"columns": [
				{ "data": "benLName" },
				{ "data": "benFName" },
				{ "data": "benMName" },
				
				{ "data": "billAmount" },             
				{ "className":'details',"data": "amtApproved", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
				{ "data": "dateApproved" },             
				{ "data": function (o) { return recordTask(o); }},
				//{"className":'taskBtn',"defaultContent": '<div class="dropdown"><button type="button" data-toggle="tooltip" title="Override" class="btn btn-light" ><i class="fa-solid fa-list-check" aria-hidden="true"></i></button><div class="dropdown-content"><a><i class="fa-solid fa-rotate-left btnDisApprove"></i> DisApprove </a><a><i class="fa-solid fa-print btnGL"></i> GL </a></a><a><i class="fa-solid fa-print btnIntake"></i> Intake </a></a><a><i class="fa-solid fa-print btnCert"></i> Certificate </a></div></div>'}	
			],
			"order": [[0, 'desc']],

			"footerCallback": function ( row, data, start, end, display ) {
            							var api = this.api(), data;
										var intVal = function ( i ) {
               						 		return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 :
                    						typeof i === 'number' ?
                        					i : 0;
            							};
										total = api.column( 4 ).data().reduce( function (a, b) {
                    						return intVal(a) + intVal(b);
                						}, 0 );
										$( api.column( 4 ).footer() ).html( total.toLocaleString('en-US',{style:'currency',currency:'PHP'}) );
        							}
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
	var id = "'"+o['idassistdetails']+"'";
	var bill = "'"+o['billAmount']+"'";
	var lname = "'"+o['benLName']+"'";
	var fname = "'"+o['benFName']+"'";
	var mname = "'"+o['benMName']+"'";
	var note = "'"+o['noteTag']+"'";
	var amt = "'"+o['amtApproved']+"'";
	var raf = "'"+o['rafNum']+"'";
	var dte = "'"+o['dateReceive']+"'";
	var loc = "'"+o['procloc']+"'";
	var addr = "'"+o['procloc']+"'";
	var brgy = "'"+o['brgyCode']+"'";
	var cas = "'"+o['hospCase']+"'";
	var provider = "'"+o['provCode']+"'";
	var rem = "'"+o['remTag']+"'";

	var callDisapprove = "showDisapprove("+id+", "+bill+", "+lname+", "+fname+", "+mname+", "+note+", "+amt+",  "+raf+", "+dte+", "+loc+", "+addr+", "+brgy+", "+cas+", "+provider+", "+rem+")";
	var callReapprove = "showReapprove("+id+", "+bill+", "+lname+", "+fname+", "+mname+", "+note+", "+amt+",  "+raf+", "+dte+", "+loc+", "+addr+", "+brgy+", "+cas+", "+provider+", "+rem+")";
	//var callDisapprove = "showDisapprove("+id+")";
	var callIntake = "showIntake("+id+")";
	var callEligibility = "showEligibility("+id+")";
	var callIndigency = "showIndigency("+id+")";
	var callGL = "showGL("+id+")";
	var row = '<div class="overridedropdown">'+
					'<button type="button" title="Task" class="btn overridedropbtn">'+
						'<i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>'+
					'</button>'+
					'<div class="overridedropdown-content text-end">'+
						'<a onclick="'+callGL+'" class="btn btn-info mx-1 py-1 px-2">GL</a>'+
						'<a onclick="'+callIntake+'" class="btn btn-primary mx-1 py-1 px-2">Intake</a>'+
						'<a onclick="'+callEligibility+'" class="btn btn-warning mx-1 py-1 px-2">Eligibility</a>'+
						'<a onclick="'+callIndigency+'" class="btn btn-secondary mx-1 py-1 px-2 me-5">Indigency</a>'+
					'</div>'+
				'</div>';	
	return row;
}

function showEligibility(id) {
	$.ajax({
		url: 'controllers/showOverridePrintsController.php',
        type: "POST",      
        cache: false, 
        data: {
        	trans:'certificate',
        	id: id
        },        
		success: function(result){		
			console.log(result);	
			$('#print-gl-modal').modal('show');
			// $('#pdf-container').prop('hidden',false);
			// $('#pdf-container').attr('src', 'controllers/intakeform/'+result);

			$('#print-gl-container').prop('hidden',false);
			$('#print-gl-container').attr('src', 'controllers/certeligibility/'+result);
		},
		error: function(result){
			console.log(result);		
		}
    });      
}


function showIndigency(id) {
	$.ajax({
		url: 'controllers/showOverridePrintsController.php',
        type: "POST",      
        cache: false, 
        data: {
        	trans:'indigency',
        	id: id
        },        
		success: function(result){		
			console.log(result);	
			$('#print-gl-modal').modal('show');
			// $('#pdf-container').prop('hidden',false);
			// $('#pdf-container').attr('src', 'controllers/intakeform/'+result);

			$('#print-gl-container').prop('hidden',false);
			$('#print-gl-container').attr('src', 'controllers/certindigency/'+result);
		},
		error: function(result){
			console.log(result);		
		}
    });      
}

function showIntake(id) {	
	$.ajax({
		url: 'controllers/showOverridePrintsController.php',
        type: "POST",      
        cache: false, 
        data: {
        	trans:'intakeform',
        	id: id
        },        
		success: function(result){		
			console.log(result);	
			$('#print-gl-modal').modal('show');
			// $('#pdf-container').prop('hidden',false);
			// $('#pdf-container').attr('src', 'controllers/intakeform/'+result);

			$('#print-gl-container').prop('hidden',false);
			$('#print-gl-container').attr('src', 'controllers/intakeform/'+result);
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
	$('#print-gl-container').attr('src', 'controllers/print/printGL.php?id='+id);	
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

function showReapprove(id, bill, lname, fname, mname, note, amt, raf, dte, loc, addr, brgy, cas, provider, rem) {
	var button = document.getElementById('btnSave');
	button.innerText = button.textContent = 'ReApprove GL';

	$("#trans").val("REAPPROVE");	
	$("#id").val(id);	
	$("#lname").val(lname);	
	$("#fname").val(fname);	
	$("#mname").val(mname);	
	$("#remarks").val(rem);	
	$("#noteTag").val("");	
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
	x.elements.namedItem('brgy').value=data['brgyCode'];
	x.elements.namedItem('case').value=data['hospCase'];
	x.elements.namedItem('provider').value=data['provCode'];
	x.elements.namedItem('note').value=data['noteTag'];
	x.elements.namedItem('bill').value=data['billAmount'];
	x.elements.namedItem('amt').value=data['amtApproved'];
	x.elements.namedItem('noteTag').value=data['noteTag'];
}

function saveThis(thisform){
	var data =  $("#entryform").serialize();
	var id =  $("#id").val();

	$.ajax({
		url: 'controllers/approvedController.php',
		cache: false,
		type: 'get',
		data : data,
		dataType: 'json',
		success: function(result) {
			if (result == id) {
				$('#ds').DataTable().ajax.reload(null,false);
				toastr.success('Override/Reapproval successfully done...');
				$('#overrideModal').modal("hide");

				notify();
			} else {
				toastr.error('Disapproved Failed.  Please check details...');
			}  
		},
		error: function(xhr, status, error) {
			console.log(error);
			alert(xhr.responseText);
		}
	});
 }


function cancelthis(thisform){
	$('#ds').DataTable().ajax.reload(false,false);
	//document.getElementsByName('myfields').disabled = true;
	$('#officeModal').modal('hide');
}


//setTimeout(checkmodule, 1000);

function notify(){

	$.get("controllers/extractrolesController.php",{"tk":qs['tk'],"trans":"getroles","roles[]":"TEAM LEADER"}, function (d){
		// let  x = document.getElementById('entryform').elements;
		// let xrafNum = x.namedItem('rafNum').value;
		// let xpatient = x.namedItem('benFName').value+' '+x.namedItem('benMName').value+' '+x.namedItem('benLName').value+' '+x.namedItem('suffix').value;

		let patient = $("#lname").val() + ', '+ $("#fname").val() +' '+$("#mname").val();
		let rafno = $("#raf").val() ;

		let i = 0;

		while (i < d.length) {
			let d1 = d[i];
			let xmessage ="Good day "+ d1['fullname']+", CPAMSver2 is requesting for override for patient "+patient+" with RAF#: "+rafno+". Thank you";
			let xdata = {"trans":"sendmsg", "tk":qs['tk'], "cellno":d1['cellno'],"email":d1['emailaddress'],"message":xmessage }
			let xdata1 = {"trans":"add_notification", "tk":qs['tk'], "message":xmessage, "user_id":d1['userid'],"title":"CPAMS - Override"};

			$.post("controllers/notifyController.php",xdata,"json").fail(function() {offlineerror();}); 
			$.get("https://cpams2.davaocity.gov.ph/controllers/notificationController.php",xdata1,"json").fail(function() {offlineerror();}); 
			i++;
		}

		toastr.success('Team Leaders successfully notified.');

	},"json").fail(function() {offlineerror();});
	// toastr.success('Forwarded for override');
}