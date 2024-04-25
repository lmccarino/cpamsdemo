function startload() {
	document.getElementById('details').style.display = "block";
	
	
	var tk=qs['tk'];
	var table = $('#ds').DataTable( {
		"ajax": "controllers/rolesController.php?trans=getdetails&tk="+tk,
        "columns": [
            {"className":      'details-control',
												 "orderable":      false,
												 "data":           null,
												 "defaultContent": ''},
			{ "data": "name" },
			{ "data": "active" },
			{"className":'prevbutton',"defaultContent": '<button type="button" data-toggle="tooltip" title="Privileges" class="btn btn-sm btn-info mx-1 py-1 px-2" >Privileges</button>'},	

			{"className":'deleteclassbutton1',"defaultContent": '<button type="button" data-toggle="tooltip" title="Delete" class="btn btn-sm btn-danger mx-1 py-1 px-2" >Delete</button>'}	
        ],
        "order": [[0, 'desc']]
    } );
	$('#ds tbody').off();
	$('#ds tbody').on( 'click', 'td.deleteclassbutton1', function (o) {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		deletethis(row.data());
	} );
	$('#ds tbody').on( 'click', 'td.prevbutton', function (o) {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		priveledges(row.data());
		
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
    } );
	
	initload();
	
}

function priveledges(data){
var idroles = data['idroles'];
var tk=qs['tk'];
let rolename = data['name'];
	$.get("controllers/rolesController.php", {trans:"priviledges",idroles: idroles,"tk":tk}, function(data) {configuredata(data);},'json');
	
	sheader ="<h5 class='text-center'>"+rolename+"</h5>";
	
	document.getElementById('modaltitle').innerHTML =sheader;
}
function configuredata(data){
var id = data['idroles'];
var access = data['access'];

var content='<form id="accessfrm" style="width:100%"><div class="row"><input type="hidden"  name="idroles" value="'+id+'"><input type="hidden" name="trans" value="access"><input type="hidden" name="tk" value="'+qs['tk']+'">';
for(var key in menu) {
			var ckey ="'"+key+"'";
			var detail = menu[key];
			if (detail['url']){
				var c = checkaccess1(key,access);
				let a = checkaccess2(key,"add",access);
				let e = checkaccess2(key,"edit",access);
				let d = checkaccess2(key,"delete",access);
				content +=	"<div class='col-sm-6'>"+
								"<div class='border rounded-top pt-0 m-2'>"+
									"<div class='input-group-text'>"+
										"<input type='checkbox' name='menu[]' value='"+key+"' "+c+" class='form-check-input'/>"+'&nbsp;'+key+
									"</div>"+
									"<div class='mt-0 mx-2 p-2'>"+
										" <input type='checkbox' name='add"+key+"' value='1' "+a+" >&nbsp; ADD &nbsp;&nbsp;<input type='checkbox' name='edit"+key+"' value='1' "+e+" >&nbsp; EDIT &nbsp;&nbsp;<input type='checkbox' name='delete"+key+"' value='1' "+d+" >&nbsp; DELETE &nbsp;&nbsp;"+
									"</div>"+
								"</div>"+
							"</div>";
				// content +="<div class='col-12 text-center'><input type='checkbox' name='menu[]' value='"+key+"' "+c+" >"+key+" - <input type='checkbox' name='add"+key+"' value='1' "+a+" >ADD <input type='checkbox' name='edit"+key+"' value='1' "+e+" >EDIT <input type='checkbox' name='delete"+key+"' value='1' "+d+" >DELETE</div><div class='row'><br><br></div>";
			} else {
				content +='<div class="row mt-1">'+
							'<small class="badge badge-pill mx-3 py-1" style="width:auto;">'+key+'</small>';
				content +="<div class='row g-1 py-1 mt-0 mb-2 border-top'>";
				content += showlist(key,access);
				content +="</div>";
				content +="</div>";
			}
}
content+="<div class='col-12'><button class='btn btn-primary' type='button' onclick='saveaccess()' style='float: right;'>Save</button></div>";
content+="</div></form>";
document.getElementById('modaldetails').innerHTML = content;
$("#prevModal").modal("show");

}
function showlist(key, access){
var sdetails='';
var stitle = menu[key];
 for(var details in stitle){
	if (details != "icon"){
		var c = checkaccess1(details,access);
		let a = checkaccess2(details,"add",access);
		let e = checkaccess2(details,"edit",access);
		let d = checkaccess2(details,"delete",access);
		let kdetails = details.replaceAll(" ", "_");

		sdetails +=	"<div class='col-sm-6'>"+
						"<div class='border rounded-top pt-0 m-2'>"+
							"<div class='input-group-text'>"+
								"<input type='checkbox' name='menu[]' value='"+details+"' "+c+" class='form-check-input mt-0'/>"+'&nbsp;'+details+
							"</div>"+
							"<div class='mt-0 mx-2 p-2'>"+
								" <input type='checkbox' name='add"+kdetails+"' value='1' "+a+" >&nbsp; ADD &nbsp;&nbsp;<input type='checkbox' name='edit"+kdetails+"' value='1' "+e+" >&nbsp; EDIT &nbsp;&nbsp;<input type='checkbox' name='delete"+kdetails+"' value='1' "+d+" >&nbsp; DELETE &nbsp;&nbsp;"+
							"</div>"+
						"</div>"+
					"</div>";
		// sdetails +="<div class='col-12'><input type='checkbox' name='menu[]' value='"+details+"' "+c+" >"+details+" - <input type='checkbox' name='add"+kdetails+"' value='1' "+a+" >ADD <input type='checkbox' name='edit"+kdetails+"' value='1' "+e+" >EDIT <input type='checkbox' name='delete"+kdetails+"' value='1' "+d+" >DELETE</div>";
	}
 }
return sdetails;
}
function checkaccess1(details, access){
	
	for (x = 0; x < access.length; x++){ 
		var module = access[x];
		if (details==module['access']) {
					return 'checked';
		}
	}
	
	return '';
}
function checkaccess2(details, mode ,access){
	
	for (x = 0; x < access.length; x++){ 
		var module = access[x];
		if (details==module['access']) {
			if (mode=='add'){
				if (module['add']=="1"){
					return 'checked';
				}
			}
			if (mode=='edit'){
				if (module['edit']=="1"){
					return 'checked';
				}
			}
			if (mode=='delete'){
				if (module['delete']=="1"){
					return 'checked';
				}
			}
		}
	}
	return '';
}
function format(row){
var idroles = row['idroles'];
var elem = document.getElementById(idroles);
if (elem){
  elem.parentNode.removeChild(idroles);
}
  return '<div class="col-12" id="'+idroles+'"></div>';
}
function deletethis(data){
if (maccess['ldelete']<1){
	toastr.warning('Record cannot be deleted');
	return false;
}
bootbox.confirm({
    message: 'Delete '+ data['name'] +'?',
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
			{$.get("controllers/rolesController.php",{trans:"delete",idroles:data['idroles'],"tk":tk},function(data){
										if (data['idroles']>-1){
											toastr.success('Record Deleted');
									        $('#ds').DataTable().ajax.reload(null,false);} else { toastr.warning('Record cannot be deleted');}
							        },'json');
					}

		}
    }
});

}
function getchild(data){
var idroles = '#'+data['idroles'];
document.getElementsByName('myfields')[0].disabled = true;
//var nform = $('#entryform').clone(true);
var tform = document.getElementById('form'+data['idroles']);
if (tform){
	tform.parentNode.removeChild('form'+data['idroles']);
}
var nform = $('#entryform').clone(true).prop('id','form'+data['idroles']);
nform.appendTo( idroles );

var x = document.getElementById('form'+data['idroles']);
	x.elements.namedItem('tk').value=qs['tk'];
	x.elements.namedItem('trans').value='UPDATE';
	x.elements.namedItem('idroles').value=data['idroles'];
	x.elements.namedItem('name').value=data['name'];
	x.elements.namedItem('active').value=data['active'];
	x.elements.namedItem('myfields').disabled = true;
	if (maccess['ledit']>0) {x.elements.namedItem('butupdate').style.display='inline';} else {x.elements.namedItem('butupdate').style.display='none';}
	x.elements.namedItem('butsave').style.display='none';
	x.elements.namedItem('butcancel').style.display='none';
    
}
function additem(){
	var myfield = document.getElementsByName('myfields')[0];
	myfield.disabled = false;
    document.getElementsByName('idroles')[0].value = -1;
	document.getElementsByName('active')[0].value = 'Y';
	document.getElementsByName('name')[0].value= '';
	document.getElementsByName('trans')[0].value='ADD';
	document.getElementById('updatebut').style.display='none';
	document.getElementById('savebut').style.display='inline';
	document.getElementById('cancelbut').style.display='inline';
	$("#officeModal").modal("show");
}
function updatethis(thisform){
thisform.elements.namedItem('butupdate').style.display='none';
thisform.elements.namedItem('butsave').style.display='inline';
thisform.elements.namedItem('butcancel').style.display='inline';
thisform.elements.namedItem('myfields').disabled = false;

	
	return false;
	
}

function savethis(thisform){
var cont = 1;

	
	var x1 = thisform.getElementsByTagName("input");
	for (var i = 0; i < x1.length; i++){
		
		if (x1[i].name == 'name') { if (!x1[i].value){ toastr.warning('Invalid name'); cont = -1;} }
	}
	
	if (cont > -1){
			
		var data = $(thisform).serialize();
		
		$.ajax({
            url: 'controllers/rolesController.php',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			dataType: 'json',
			type: 'GET',
            success: function(data) {
				    if (data["idroles"] > -1){
							
							$('#ds').DataTable().ajax.reload(null,false);
							
						toastr.success('Record Saved');
					} else {
						toastr.warning('Saving Failed.  Please check details');
					}
			}
		});
		
		$("#officeModal").modal("hide");
		
	}
	return false;
}

function cancelthis(thisform){
	$('#ds').DataTable().ajax.reload(null,false);
	//document.getElementsByName('myfields').disabled = true;
	$('#officeModal').modal('hide');
}
function saveaccess(){
$("#prevModal").modal("hide");
$("#mySave").modal("show");
var data = new FormData(document.getElementById('accessfrm'));
		$.ajax({
            url: 'controllers/rolesController.php',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			dataType: 'json',
			type: 'POST',
            success: function(data) {
				if (data['id']> -1) {toastr.success('Record saved');} else {toastr.warning('Record failed to saved');}
				$("#mySave").modal("hide");
			}
		});
}

let tmodule = false;
function checkmodule(){
	
		
		maccess = getmoduleaccess('Roles');
		if (maccess['name']){ 
			
			if (maccess['ladd']<1) {
				$(".addbutton").hide();
			}
			if (maccess['ldelete']<1) {
				$(".deleteclassbutton1").hide();
			}
			if (maccess['ledit']<1) {
				$(".updatebutton").hide();
			}
		}
	
};
setTimeout(checkmodule, 1000);
