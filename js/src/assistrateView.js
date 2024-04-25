
function filterdata(){
	let tk = qs['tk'];
	var today = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ stoday;
		var table = $('#ds').DataTable( {"destroy":true, 
		dom: 'lfrtBip', 
		buttons: [
            'copy', {extend : 'excelHtml5',title : 'Patient Details'
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true}, 
			{extend: 'pdf', 
			title : 'Patient Details'
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true},
			{extend: 'print',
			title : 'Patient Details',
			 messageTop: caption, messageBottom: '<br/> Prepared by: '+ fullname + ' ' +stoday, footer:true
			
			}
        ],
		"ajax": {"url": "controllers/assistrateController.php?trans=getdetails&tk="+tk, "error": function (xhr, error, code) {
            offlineerror();
        }},
        "columns": [
			{"className":      'details-control',
												 "orderable":      false,
												 "data":           null,
												 "defaultContent": ''},
			{ "data": function (o) { return linkRate(o); }},
            { "data": "baseFrom", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
			{ "data": "baseTo", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
			{ "data": "assistAmount", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
			{ "data": "status"},
			{ "className":'editbutton',"defaultContent": '<button type="button" data-toggle="tooltip" title="Assistance Rate Status" class="btn btn-sm btn-info" >Activate Status</button>'}	
			],
        "order": [[1, 'asc']],
		
		
    } );
	
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
    } );
	$('#ds tbody').on('click', 'td.editbutton', function (o) {
		var tr = $(this).closest('tr');
		var row = table.row( tr );
		changeStatus(row.data());
	}) ;
	
	
}
function format(row){
	var id = row['idassistrate'];
	var idx = "'"+row['idassistrate']+"'";
	var code = "'"+row['rateCode']+"'";
	var divid = "div" + id;

	var elem = document.getElementById(id);
	if (elem){
  		elem.parentNode.removeChild(id);
	}
	//var xcontent = '<table class="table table-sm" id="rateCode'+id+'" style="width:100%">';
    //xcontent += '<thead><tr><th>Assistance</th><th>Description</th><th></th></tr></thead><tbody></tbody></table>';

	var xcontent = '<div id="'+divid+'" class="row g-1 py-2">';
	    xcontent += '<div class="offset-md-9 col-md-3 filterbutton"><button type="button" name="addbutton" onclick="showNewAssistance('+code+', '+idx+')" class="btn mt-2 addbutton" data-toggle="tooltip" title="Add Rate Schedule"><i class="fa fa-folder-plus"></i> Add Rate Schedule</button></div>';
	    xcontent += '<div class="row g-1 py-2 border-top border-bottom tablecontainer">';	
	    xcontent += '<table class="table table-sm" id="rateCode'+id+'">';
	    xcontent += '<thead><tr><th>Assistance</th><th>Description</th><th>Active</th><th></th></tr></thead><tbody></tbody></table>';
		//xcontent += '<div class="col-12 text-end"><button type="button" name="addbutton" onclick="showNewAssistance('+code+', '+tableid+')"  class="btn btn-primary addbutton" data-toggle="tooltip" title="Add Assistance"><span class="fa fa-folder-plus"></span></button></div>';
		xcontent += '</div></div>';

	return xcontent;
}

function getchild(data){
	var tableid = '#rateCode'+data['idassistrate'];

	var tk = qs['tk'];
	
	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options)+ '\n rateCode: '+data['rateCode'];
	let xtitle = "List of Assistance  - Assist Code:  "+ data['rateCode'];
	
	var table = $(tableid).DataTable( {"destroy":true,"searching":false,
		dom: 'lfrtBip', 
		buttons: [
            'copy', {extend : 'excelHtml5',title : xtitle
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true}, 
			{extend: 'pdf', 
			title : xtitle
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true},
			{extend: 'print',
			title : xtitle,
			 messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true
			}
        ],
		"ajax": {"url":"controllers/assistrateController.php?trans=getassistance&rateCode="+data['rateCode']+"&tk="+tk, 
		         "error": function (xhr, error, code) {offlineerror();}
				},
        "columns": [
			
            { "className":'detail2',"data": "assistCode"},
			{ "data": function (o) { return linkDesc(o); }},
			{ "data": "assistStatus"},
			{ "className":'editbutton',"defaultContent": '<button type="button" data-toggle="tooltip" title="Assistance Sched Status" class="btn btn-sm btn-success">Activate</button>'},	
		],
        "order": [[1, 'asc']]
    } );
	$(tableid+' tbody').off();
	$(tableid+' tbody').on( 'click', 'td.editbutton', function (o) {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		changeStatus2(row.data(), tableid);
	} );
}

function linkRate(o) {
	var id = "'"+o['idassistrate']+"'";
	var code = "'"+o['rateCode']+"'";
	var fr = "'"+o['baseFrom']+"'";
	var to = "'"+o['baseTo']+"'";
	var amt = "'"+o['assistAmount']+"'";
	
	var callproc = "showNewRate("+id+", "+code+", "+fr+", "+to+", "+amt+")";
	var row ='<button type="button" data-toggle="tooltip" title="Update Rate" class="btn btn-link editbutton" onclick="'+callproc+'">'+o['rateCode']+'</button>';
	return row;
}

function linkDesc(o) {
	
	var id = "'"+o['idassistsched']+"'";
	var code = "'"+o['assistCode']+"'";
	var desc = "'"+o['assistDesc']+"'";
	
	var callproc = "showUpdate("+id+", "+code+", "+desc+")";
	var row ='<button type="button" data-toggle="tooltip" title="Update Desc" class="btn btn-link editbutton" onclick="'+callproc+'">'+o['assistDesc']+'</button>';
	return row;
}

function showUpdate(id, code, desc) {
	var button = document.getElementById('btnSave');
	button.innerText = button.textContent = 'Update Description';

	$("#trans").val('UPDATE');	
	$("#id").val(id);	
	$("#assistDesc").val(desc);	
	$("#tk").val(qs['tk']);	
	$("#assistCode").val(code);
	
	document.getElementById('assistDesc').removeAttribute('readonly');

	$('#myModal').modal("show");
}

function changeStatus(row) {
	var stat =""+row['status']+"";
	var recid =""+row['idassistrate']+"";
	let xmessage = 'Activate Rate : ' + row['rateCode']+'?';

	//if (stat == null) {stat='Y';} 
	if (stat.trim() == 'Y') {
		xmessage = 'DeActivate Rate : ' + row['rateCode']+'?';
		stat ='N';
	}
	else {
		stat ='Y';	
	}

	bootbox.confirm({
		message: xmessage,
		buttons: {
			confirm: {
				label: 'Yes',
				className: 'btn-success'
			},
			cancel: {
				label: 'No',
				className: 'btn-warning'
			}
		},
		callback: function (result) {
			if (result){
				var tk=qs['tk'];
				$.get("controllers/assistrateController.php",{trans:"STATUS", "recid":recid, "stat":stat,"tk":tk},function(data)
				{
					toastr.success('Record Updated');
					$('#ds').DataTable().ajax.reload(null,false);
				},
				'json').fail(function() {offlineerror();});	
			}
		}
	});  
}

function exclude(data){
	var button = document.getElementById('btnSave');
	button.innerText = button.textContent = 'Exclude Assistance';
	$("#trans").val('EXCLUDE');	
	var id =""+data['idassistsched']+"";
	var desc = ""+data['assistDesc']+"";

	$("#id").val(id);	
	$("#assistDesc").val(desc);	
	$("#tk").val(qs['tk']);	
	document.getElementById('assistDesc').setAttribute('readonly', true);

	$('#myModal').modal("show");
}

function changeStatus2(data, tableid) {
	
	var stat =""+data['assistStatus']+"";
	var recid =""+data['idassistsched']+"";
	let xmessage = 'Activate Rate : ' + data['assistCode'] + '-' + data['assistDesc'] +'?';

	//if (stat == null) {stat='Y';} 
	if (stat.trim() == 'Y') {
		xmessage = 'DeActivate Rate : ' + data['assistCode'] + '-' + data['assistDesc'] +'?';
		stat ='N';
	}
	else {
		stat ='Y';	
	}

	bootbox.confirm({
		message: xmessage,
		buttons: {
			confirm: {
				label: 'Yes',
				className: 'btn-success'
			},
			cancel: {
				label: 'No',
				className: 'btn-warning'
			}
		},
		callback: function (result) {
			if (result){
				var tk=qs['tk'];
				$.get("controllers/assistrateController.php",{trans:"STATUS2", "recid":recid, "stat":stat,"tk":tk},function(data)
				{
					toastr.success('Record Updated');
					$(tableid).DataTable().ajax.reload(null,false);
				},
				'json').fail(function() {offlineerror();});	
			}
		}
	});  
}

function showNewAssistance(code, tableid) {
	//var code = ""+o['rateCode']+"";
	var button = document.getElementById('btnSave');
	button.innerText = button.textContent = 'Save New Schedule';
	
	$("#child").val('#rateCode' + tableid);	
	$("#trans").val('NEW2');	
	$("#id").val(id);	
	$("#rateCode").val(code);	
	$("#tk").val(qs['tk']);	
	//$("#assistCode").val(code);
	
	//document.getElementById('desc').removeAttribute('readonly');

	$('#myModal').modal("show");
}


function startload(){
	var otk = document.getElementsByName('tk');
	for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
	{
    	otk[x].value = qs['tk'];
	}
	filterdata();

	
}

function saveThis(thisform){
	var id =  $("#id").val();
	var trans =  $("#trans").val();
	var childId =  $("#child").val();
	alert(childId);
	var msg = "Exclude successfully done...";

	if (trans == "UPDATE") {
		msg = "Update successfully done...";
	}
	else if (trans == "NEW2") {
		msg = "Insert successfully done...";
	}
	var data =  $("#entryform").serialize();

	$.ajax({
		url: 'controllers/assistrateController.php',
		cache: false,
		type: 'get',
		data : data,
		dataType: 'json',
		success: function(data) {
			if (data == 1) {
				toastr.error('Assistance schedule exits.  Please check...');
			} else {
				$(childId).DataTable().ajax.reload(null,false);
				toastr.success(msg);
				$('#myModal').modal("hide");
			}  
		},
		error: function(xhr, status, error) {
			console.log(error);
			alert(xhr.responseText);
		}
	});
 }

 function showNewRate(id, code, fr, to, amt) {
	//var button = document.getElementById('btnSave');
	//button.innerText = button.textContent = 'Update Description';

	$("#tk").val(qs['tk']);	
	$("#recid").val(id);	
	$("#rate").val(code);	
	$("#from").val(fr);	
	$("#to").val(to);	
	$("#amt").val(amt);	
	if (code.trim() != "") {
		//document.getElementById('rate').setAttribute('readonly', true);
		$("#Rtrans").val("EDIT");	
		$("#rate").attr('readonly', "");
	}
	else {
		$("#Rtrans").val("NEW");	
		$("#rate").prop('readonly', false);
		//$("#rate").attr("readonly","true");
	}
	
	$('#ARmodal').modal("show");
}

 function saveRate(thisform){
	var rate =  $("#rate").val();
	if (rate.trim() == "") {
		toastr.warning('Invalid rate code! Please check...');
		return false;
	}
	let fr =  $("#from").val();
	if (fr.trim() =="") {fr=0;}
	let to =  $("#to").val();
	if (to.trim() == "") {to=0;};
	if (fr == 0) {
		toastr.warning('Invalid rate range! Please check...');
		return false;
	}
	if (fr > to) {
		toastr.warning('Invalid rate range! Please check...');
		return false;
	}

	var data =  $("#rateForm").serialize();
	var trans =  $("#Rtrans").val();
	
	//$('#ARmodal').modal("hide");
	$.ajax({
		url: 'controllers/assistrateController.php?trans=' + trans,
		cache: false,
		type: 'get',
		data : data,
		dataType: 'json',
		success: function(data) {
			if (data == 0) {
				toastr.error('Assistance rate exists.  Please check...');
			} 
			else {
				$('#ds').DataTable().ajax.reload(null,false);
				toastr.success("Transaction successfull...");
				$('#ARmodal').modal("hide");
			}  
		},
		error: function(xhr, status, error) {
			console.log(error);
			alert(xhr.responseText);
		}
	}); 
 }

 function checkmodule() {
	maccess = getmoduleaccess('Assist Rate');
	if (maccess['name']){ 
		tmodule = true;
		if (maccess['ladd']<1) {
			
			$(".addbutton").hide();
		}

		if (maccess['ledit']<1) {
			
			$(".savebutton").hide();
		}
		
		if (maccess['ldelete'] < 1){
			$(".savebutton").hide();
		}
	}
	else {
		$(".addbutton").hide();
		$(".savebutton").hide();
		$(".savebutton").hide();
	}
 };

 setTimeout(checkmodule, 1000);