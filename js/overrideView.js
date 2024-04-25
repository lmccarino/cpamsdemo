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
			"ajax": "controllers/overrideController.php?trans=LIST&tk="+tk,
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

function linkOverride(o) {
	var id = "'"+o['idassistdetails']+"'";
	var bill = "'"+o['billAmount']+"'";
	var lname = "'"+o['benLName']+"'";
	var fname = "'"+o['benFName']+"'";
	var mname = "'"+o['benMName']+"'";
	var note = "'"+o['noteTag']+"'";
	var amt = "'"+o['amtApproved']+"'";
	var raf = "'"+o['rafNum']+"'";
	var dte = "'"+o['dateReceive']+"'";
	var loc = "'"+o['amtApproved']+"'";
	var addr = "'"+o['procloc']+"'";
	var brgy = "'"+o['brgyCode']+"'";
	var cas = "'"+o['hospCase']+"'";
	var provider = "'"+o['provCode']+"'";

	var callproc = "showOverride("+id+", "+bill+", "+lname+", "+fname+", "+mname+", "+note+", "+amt+",  "+raf+", "+dte+", "+loc+", "+addr+", "+brgy+", "+cas+", "+provider+")";
	var row ='<button type="button" data-toggle="tooltip" title="Override Amount" class="btn btn-link" onclick="'+callproc+'">'+o['amtApproved']+'</button>';
	return row;
}

function showOverride(id, bill, lname, fname, mname, note, amt, raf, dte, loc, addr, brgy, cas, provider) {
	$("#id").val(id);	
	$("#lname").val(lname);	
	$("#fname").val(fname);	
	$("#mname").val(mname);	
	$("#note").val(note);	
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
	x.elements.namedItem('remarks').value=data['remarks'];
	x.elements.namedItem('bill').value=data['billAmount'];
	x.elements.namedItem('amt').value=data['amtApproved'];
	x.elements.namedItem('note').value=data['noteTag'];
	
	//x.elements.namedItem('myfields').disabled = true;
	
	//x.elements.namedItem('butsave').style.display='none';
	//x.elements.namedItem('butcancel').style.display='none';
}

function saveThis(thisform){
	var $amt = $("#amtApproved").val();	
	if ($amt == "") {
		toastr.warning('Invalid amount! Please check...');
		return false;
	}
	if ($amt == "0") {
		toastr.warning('Invalid amount! Please check...');
		return false;
	}
	
	// document.getElementById("btnGL").style.display = "block";
	// document.getElementById("btnIntake").style.display = "block";
	// document.getElementById("btnCert").style.display = "block";
	// document.getElementById("btnSave").style.display = "none";

	//var data =  $(thisform).serialize();
	var data =  $("#entryform").serialize();
	var id =  $("#id").val();

	$.ajax({
		url: 'controllers/overrideController.php',
		cache: false,
		type: 'get',
		data : data,
		dataType: 'json',
		success: function(data) {
			if (data["@id"] > -1){
		     	$('#ds').DataTable().ajax.reload(null,false);

		     	generategl(id);
		     	toastr.success('Override successfully done...');
			 	$('#overrideModal').modal("hide");			 	
			 	
		 	} else {
		    	 toastr.warning('Override Failed. Might be insufficient of fund please check...');
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

function generategl(id){
	
	// $('#print-gl-modal').modal('show');
	// $('#print-gl-container').prop('hidden',true);
	$('#print-gl-container').attr('src', 'controllers/renderGuaranteeLetter.php?id='+id);	
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

