function startload() {
	document.getElementById('details').style.display = "block";
	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options);
	let xtitle ="List of Providers";
	var tk=qs['tk'];
	var table = $('#ds').DataTable( {
		dom: 'lfrtBip', 
		buttons: [
            'copy', {extend : 'excelHtml5',title : xtitle
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true}, 
			{extend: 'pdf', 
			title : xtitle
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true},
			{extend: 'print',
			title : xtitle,
			 messageTop: caption, messageBottom: '<br/> Prepared by: '+ fullname + ' ' +stoday, footer:true
			
			}
        ],
		"ajax": "controllers/provassistController.php?trans=getdetails&tk="+tk,
        "columns": [
            {"className":      'details-control',
												 "orderable":      false,
												 "data":           null,
												 "defaultContent": ''},
			{ "data": "officecode" },
			{ "data": "officename" },
			{ "data": "provCat" }
			//{"className":'deleteclassbutton1',"defaultContent": '<button type="button" data-toggle="tooltip" title="delete" class="btn btn-light" ><i class="fas fa-trash-alt" aria-hidden="true"></i></button>'}	
        ],
        "order": [[0, 'desc']]
    } );
	$('#ds tbody').off();
	
	$('#ds tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
 
        if ( row.child.isShown() ) {
            // This row is already open - close it
			deletechild(row.data());
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
	
	initload();
	
	
	
}

function deletechild(row){
var id = "idoffice"+row['idoffice'];
var divid = "div"+row['idoffice'];
let officecode = "'"+row['officecode']+"','"+row['idoffice']+"'";
var elem = document.getElementById(id);
let eldiv = document.getElementById(divid);
if (elem){
	$(id+' tbody').off();
	elem.parentNode.removeChild(elem);
	eldiv.parentNode.removeChild(eldiv);
  
}
}
function format(row){
var id = "idoffice"+row['idoffice'];
var divid = "div"+row['idoffice'];
let officecode = "'"+row['officecode']+"','"+row['idoffice']+"'";
var elem = document.getElementById(id);
let eldiv = document.getElementById(divid);
if (elem){
	$(id+' tbody').off();
	elem.parentNode.removeChild(elem);
	eldiv.parentNode.removeChild(eldiv);
  
}
var xcontent = '<div id="'+divid+'" class="row g-1 py-2">';
    xcontent += '<div class="offset-md-9 col-md-3 filterbutton"><button type="button" name="addbutton" onclick="additem('+officecode+')" class="btn mt-2 addbutton" data-toggle="tooltip" title="Add Assistance"><i class="fa fa-folder-plus"></i> Add Assistance</button></div>';						
    // xcontent += '<small class="badge badge-pill mx-3 py-1 w-15">List of Offices</small>';
    xcontent += '<div class="row g-1 py-2 border-top border-bottom tablecontainer">';
    xcontent += '<table class="table table-sm" id="'+id+'">';
    xcontent += '<thead><tr><th>Assistance</th><th>Active</th><th>Delete</th><th>DeActivate</th></tr></thead><tbody></tbody></table>';
	xcontent += '</div>';
	// xcontent += '<div class="col-12 text-end"><button type="button" name="addbutton" onclick="additem('+officecode+')"  class="btn btn-primary addbutton" data-toggle="tooltip" title="Add Assistance"><span class="fa fa-folder-plus"></span></button></div>';
	xcontent += '</div>';
	return xcontent;
}

function getchild(data){
var tableid = '#idoffice'+data['idoffice'];
let officecode = data['officecode'];
let providername = data['officename']+', '+data['location'];
let idoffice = data['idoffice'];

var tk = qs['tk'];

	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options);
	let xtitle = "List of Assistance Provided - "+providername;

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
		"ajax": {"url":"controllers/provassistController.php?trans=getassistance&officecode="+officecode+"&tk="+tk, 
		         "error": function (xhr, error, code) {offlineerror();}
				},
        "columns": [
			
            { "data": "assistCode"},
			{ "data": "active"},
			{"className":'deletebutton',"defaultContent": '<button type="button" class="btn btn-sm btn-danger">Delete</button>'},
			{"className":'updatebutton',"defaultContent": '<button type="button" class="btn btn-sm btn-success">Deactivate</button>'}	

     
			
			
		        ]
    } );
	
	$(tableid+' tbody').off();
	$(tableid+' tbody').on( 'click', 'td.deletebutton', function (o) {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		deletethis(row.data(), idoffice);
	} );
	$(tableid+' tbody').on( 'click', 'td.updatebutton', function (o) {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		deactivethis(row.data(), idoffice);
	} );
	
	
}



function deletethis(data, idoffice){
if (maccess['ldelete']<1){
	toastr.error('Record cannot be deleted');
	return false;
}

bootbox.confirm({
    message: 'Delete '+ data['assistCode'] +'?',
    buttons: {
        confirm: {
            label: 'Yes',
            className: 'btn-danger'
        },
        cancel: {
            label: 'No',
            className: 'btn-default'
        }
    },
    callback: function (result) {
        if (result){
			var tk=qs['tk'];
			$.get("controllers/provassistController.php",{trans:"delete",idprovassist:data['idprovassist'],"idoffice":idoffice,"tk":tk},function(data){
										
											toastr.success('Record Deleted');
											let tableid = '#idoffice'+data['idoffice'];
											$(tableid).DataTable().ajax.reload(null,false);
									        
							        },'json').fail(function() {offlineerror();});	
									
		}
    }
});
}
function deactivethis(data, idoffice){
let xmessage = 'DeActivate ' + data['assistCode']+'?';
let active ='N';
if (data['active']=='N'){
	xmessage = 'Activate '+data['assistCode']+'?';
	active ='Y';
}

bootbox.confirm({
    message: xmessage,
    buttons: {
        confirm: {
            label: 'Yes',
            className: 'btn-danger'
        },
        cancel: {
            label: 'No',
            className: 'btn-default'
        }
    },
    callback: function (result) {
        if (result){
			var tk=qs['tk'];
			$.get("controllers/provassistController.php",{trans:"UPDATE",idprovassist:data['idprovassist'],"idoffice":idoffice,"active":active,"tk":tk},function(data){
										
											toastr.success('Record Updated');
											let tableid = '#idoffice'+data['idoffice'];
											$(tableid).DataTable().ajax.reload(null,false);
									        
							        },'json').fail(function() {offlineerror();});	
									
		}
    }
});
}




function additem(officecode, idoffice){
	var x = document.getElementsByName('entryform')[0].elements;
	x.namedItem('officecode').value = officecode;
	x.namedItem('idprovassist').value = -1;
	x.namedItem('trans').value = 'ADD';
	x.namedItem('assistCode').selectedIndex = 0;
	x.namedItem('idoffice').value = idoffice;
	$("#officeModal").modal("show");
}
function updatethis(thisform){


	
	return false;
	
}

function savethis(thisform){
var cont = 1;
initload();
	
	
	
	if (cont > -1){
			
		var data = $(thisform).serialize();
		
		$.ajax({
            url: 'controllers/provassistController.php',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			dataType: 'json',
			type: 'GET',
            success: function(data) {
				    
							let tableid = '#idoffice'+data['idoffice'];
							$(tableid).DataTable().ajax.reload(null,false);
							
							
						toastr.success('Record Saved');
					
			},
			error: function (){offlineerror();
			}
		});
		
		$("#officeModal").modal("hide");
		
	}
	return false;
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