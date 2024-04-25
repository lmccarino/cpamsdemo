var tupdate = 0;
let allroles = [];
function readURL(input, id) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
	  var sfilename = input.value;
	  var ext = sfilename.substr(sfilename.lastIndexOf('.') + 1);
	  if (ext == 'pdf'){$(id).attr('src', 'attachments/pdficon.jpg');} else {
      $(id)
        .attr('src', e.target.result);}
        
    };
	
    reader.readAsDataURL(input.files[0]);
  }
}

function startload() {
	//document.getElementById('details').style.display = "block";
	
	
	var tk=qs['tk'];
	var table = $('#ds').DataTable( {
		"ajax": {"url":"controllers/usersController.php?trans=getdetails&tk="+tk,"error": function (xhr, error, code) {
            offlineerror();
        }
		},
        "columns": [
            {"className":      'details-control',
												 "orderable":      false,
												 "data":           null,
												 "defaultContent": ''},
			{ "data": "fullname"},
			{ "data": "officename"},
			{"className":'rolebutton',"defaultContent": '<button type="button" data-toggle="tooltip" title="Roles" class="btn btn-sm btn-success mx-1 py-1 px-2" >Roles</button>'}//,	

			// {"className":'prevbutton',"defaultContent": '<button type="button" data-toggle="tooltip" title="Privileges" class="btn btn-sm btn-info mx-1 py-1 px-2" >Privileges</button>'}	

        ],
        "order": [[3, 'desc']]
    } );
	
	$('#ds tbody').off(); 
	$('#ds tbody').on( 'click', 'td.prevbutton', function (o) {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		priveledges(row.data());
		
	} );
	$('#ds tbody').on( 'click', 'td.rolebutton', function (o) {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		roles(row.data());
		
	} );
	$('#ds tbody').on('click', 'td.details-control', function () {
		
        var tr = $(this).closest('tr');
        var row = table.row( tr );
 
        if ( row.child.isShown() ) {
            
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            if ( table.row( '.shown' ).length ) {
                  $('.details-control', table.row( '.shown' ).node()).click();
			}
            row.child( format(row.data())).show();
            tr.addClass('shown');
			getchild(row.data());
        }
    } );
	
	
	document.getElementById('details').style.display="none"; 
	{$.get("controllers/usersController.php",{trans:"getoffices",tk:qs['tk']}, function(data){
		var x = document.getElementById('entryform');
		var gl = x.elements.namedItem('office');
		var row = data['data'];
		gl.innerHTML = "";
		for (a = 0;a < row.length; a++){
			var option = document.createElement("option");
							option.text = row[a]['officename'];
							option.value = row[a]['officecode'];
							gl.add(option); 
		}
	},"json");}
	initload();
	getallroles();
}

function getallroles(){
	$.get("controllers/usersController.php",{trans:"getallroles",tk:qs['tk']}, function(data){
			allroles = data['allroles'];
			
			
	},"json");
	
}

function format(row){
var userid = row['userid'];
var elem = document.getElementById(userid);
if (elem){
  elem.parentNode.removeChild(userid);
}
  return '<div class="col-xs-12 well" id="'+userid+'"></div>';
}
function getchild(data){
var userid = '#'+data['userid'];

var tform = document.getElementById('form'+data['userid']);
if (tform){
	tform.parentNode.removeChild('form'+data['userid']);
}
var nform = $('#entryform').clone(true).prop('id','form'+data['userid']);
nform.appendTo( userid );
var x = document.getElementById('form'+data['userid']);
//var x = document.getElementById(userid);

	
	x.elements.namedItem('tk').value=qs['tk'];
	x.elements.namedItem('trans').value='UPDATE';
	x.elements.namedItem('userid').value=data['userid'];
	x.elements.namedItem('fullname').value=data['fullname'];
	x.elements.namedItem('active').value=data['active'];
	
	x.elements.namedItem('office').value=data['office'];
	x.elements.namedItem('login').value=data['login'];
	x.elements.namedItem('ip').value=data['ip'];
	x.elements.namedItem('cellno').value=data['cellno'];
	x.elements.namedItem('emailaddress').value=data['emailaddress'];
	x.elements.namedItem('remarks').value=data['remarks'];
	if (data['image']){
		document.getElementById('imgupload').src="userimages/"+data['image'];
		x.elements.namedItem('image1').value=data['image'];
	} else {
		document.getElementById('imgupload').src="userimages/person.jpg";
		x.elements.namedItem('image1').value="";
	}
	if (data['signature']){
		
		document.getElementById('sigupload').src="signatures/"+data['signature'];
		x.elements.namedItem('sig1').value=data['signature'];
	} else {
		document.getElementById('sigupload').src="signatures/signature.png";
		x.elements.namedItem('sig1').value="";
	}
	x.elements.namedItem('myfields').disabled = true;
	x.elements.namedItem('butupdate').style.display='inline';
	x.elements.namedItem('butsave').style.display='none';
	x.elements.namedItem('butcancel').style.display='none';
	x.elements.namedItem('butclose').style.display='none';
	
	
}
function additem(){
	document.getElementById('list').style.display="none";
	document.getElementById('details').style.display="block";
	var x = document.getElementById('entryform');
	x.elements.namedItem('userid').value=-1;
	x.elements.namedItem('tk').value=qs['tk'];
	x.elements.namedItem('fullname').value="";
	x.elements.namedItem('active').value="Y";
	
	x.elements.namedItem('office').value="";
	x.elements.namedItem('login').value="";
	x.elements.namedItem('ip').value="";
	x.elements.namedItem('cellno').value="";
	x.elements.namedItem('emailaddress').value="";
	x.elements.namedItem('remarks').value="";
	document.getElementById('imgupload').src="userimages/person.jpg";
	document.getElementById('sigfile').src="signatures/signature.png";
	x.elements.namedItem('image1').value="";
	x.elements.namedItem('sig1').value="";
	x.elements.namedItem('myfields').disabled = false;
	x.elements.namedItem('butclose').style.display='inline';
	x.elements.namedItem('butupdate').style.display='none';
	document.getElementsByName('trans')[0].value='ADD';
	document.getElementById('savebut').style.display='inline';
	document.getElementById('cancelbut').style.display='inline';
	
	
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

	
	var xsubject = thisform.elements.namedItem('emailaddress').value;
	var xdetails = thisform.elements.namedItem('fullname').value;
	var pass = thisform.elements.namedItem('password').value;
	var pass2 = thisform.elements.namedItem('password2').value;
	var xtrans = thisform.elements.namedItem('trans').value;
		
		if (!xsubject) { toastr.warning('Invalid Email Field'); cont = -1;} 
		var atpos = xsubject.indexOf("@");
		var dotpos = xsubject.lastIndexOf(".");
		if (atpos<1 || dotpos<atpos+2 || dotpos+2>=xsubject.length) {
			
			toastr.error('Invalid Email Field'); cont = -1;
		}
		if (xtrans=='ADD'){
			if (!pass) { toastr.error('Invalid Email Field'); cont = -1;} 
		}
		if (!xdetails) { toastr.error('Invalid Fullname Field'); cont = -1;} 
		if (pass !=pass2) { toastr.error('Invalid password Field'); cont = -1;} 
	
	
	if (cont > -1){
		
		var data = new FormData(thisform);
		//var data = $(thisform).serialize();
		
		$.ajax({
            url: 'controllers/usersController.php',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			dataType: 'json',
			type: 'POST',
            success: function(data) {
			
				    if (data["userid"] > -1){
							
						
						toastr.success('Record Saved');
						tupdate = true;
						document.getElementById('userid').value = data['userid'];
						document.getElementById('trans').value = 'UPDATE';
						document.getElementById('image1').value = data['image'];
						document.getElementById('sig1').value = data['signature'];
						$('#ds').DataTable().ajax.reload(false,false);
					} else {
						toastr.warning('Saving Failed.  Please check details');
					}
					
					
					
			}
		});
		thisform.elements.namedItem('myfields').disabled = true;
		thisform.elements.namedItem('butupdate').style.display='inline';
		thisform.elements.namedItem('butsave').style.display='none';
		thisform.elements.namedItem('butcancel').style.display='none';
		//$("#officeModal").modal("hide");
		
	}
	return true;
}

function cancelthis(thisform){
	$('#ds').DataTable().ajax.reload(false,false);
	//document.getElementsByName('myfields').disabled = true;
	$('#officeModal').modal('hide');
}
function closethis(thisform){
document.getElementById('details').style.display="none";
if (tupdate) {
	$('#ds').DataTable().ajax.reload(false,false);
	tupdate = false;
}
document.getElementById('list').style.display="block";
}
function priveledges(data){
var userid = data['userid'];
var tk=qs['tk'];
	$.get("controllers/usersController.php", {trans:"priviledges",userid: userid,"tk":tk}, function(data) {configuredata(data);},'json');
	var sheader ='';
	var src="userimages/person.jpg";
	if (data['image']) {
		src="userimages/"+data['image'];
	}
	sheader ="<div class='row px-2'><div class='col-md-10'>";
	key =data['fullname'];
	stat = (data['active'] == 'Y') ? 'Yes' : 'No';
	sheader +="<small>Name:</small> <strong>"+key+"</strong><br/>";
	sheader +="<small>Email:</small> <strong>"+data['emailaddress']+"</strong><br/>";	
	sheader +="<small>Active:</small> <strong>"+stat+"</strong></div>";
	sheader +="<div class='col-md-2'><img src='"+src+"' style='max-width:5em;'></div></div><hr/>";
	document.getElementById('modaltitle').innerHTML =sheader;
}
function roles(data){
var userid = data['userid'];
var tk=qs['tk'];
	$.get("controllers/usersController.php", {trans:"getroles",userid: userid,"tk":tk}, function(data) {configureroledata(data);},'json');
	var sheader ='';
	var src="userimages/person.jpg";
	if (data['image']) {
		src="userimages/"+data['image'];
	}
	sheader ="<div class='row px-2'><div class='col-md-10'>";
	key =data['fullname'];
	stat = (data['active'] == 'Y') ? 'Yes' : 'No';
	sheader +="<small>Name: </small><strong>"+key+"</strong><br/>";
	sheader +="<small>Email: </small><strong>"+data['emailaddress']+"</strong><br/>";	
	sheader +="<small>Active: </small><strong>"+stat+"</strong></div>";
	sheader +="<div class='col-md-2'><img src='"+src+"' style='max-width:5em;'></div></div><hr/>";
	document.getElementById('modaltitle2').innerHTML =sheader;
}

function configuredata(data){
var id = data['userid'];
var access = data['access'];

var content='<form id="accessfrm" style="width:100%"><div class="row"><input type="hidden"  name="userid" value="'+id+'"><input type="hidden" name="trans" value="access"><input type="hidden" name="tk" value="'+qs['tk']+'">';
for(var key in menu) {
			var ckey ="'"+key+"'";
			var detail = menu[key];
			if (detail['url']){
				var c = checkaccess(key,access);
				content +="<div class='col-6'>"+
										"<div class='p-1 m-1 input-group-text'>"+
											"<input type='checkbox' name='menu[]' value='"+'&nbsp;'+key+"' "+c+" class='form-check-input mt-0'>"+'&nbsp;'+key+"</div><div class='row'>"+
											"</div>"+
									"</div>";
			} else {
				content +='<div class="row mt-1">'+
										'<small class="badge badge-pill mx-3 py-1" style="width:auto;">'+key+'</small>';
				// content +="<div class='col-12 text-center'>"+key+"</div>";
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
function configureroledata(data){
var id = data['userid'];
var roles = data['roles'];

var content='<form id="rolefrm" class="p-3"><div class="row"><input type="hidden"  name="userid" value="'+id+'"><input type="hidden" name="trans" value="roles"><input type="hidden" name="tk" value="'+qs['tk']+'">';
for (a = 0;a < allroles.length; a++){ 
				var key = allroles[a];
				var c = checkrole(key['idroles'],roles);
				let idrole = key['idroles'];
				let details = '&nbsp;'+key['name'];
				content +="<div class='col-4'>"+
										"<div class='p-1 m-1 input-group-text'>"+
											"<input type='checkbox' name='roles[]' value='"+idrole+"' "+c+" class='form-check-input mt-0'>"+   
											  details+
										"</div>"+
									"</div>";
			
}
content+="<div class='col-12'><button class='btn btn-primary' type='button' onclick='saveroles()' style='float: right;'>Save</button></div>";
content+="</div></form>";
document.getElementById('modaldetails2').innerHTML = content;
$("#roleModal").modal("show");

}
function checkrole(idroles, roles){
	
	for (x = 0; x < roles.length; x++){ 
		let role = roles[x];
		if (idroles==role['idroles']) {
					return 'checked';
		}
	}
	
	return '';
}
function showlist(key, access){
var sdetails='';
var stitle = menu[key];
 for(var details in stitle){
	var c = checkaccess(details,access);
	sdetails +="<div class='col-6'>"+
								"<div class='p-1 m-1 input-group-text'>"+
									"<input type='checkbox' name='menu[]' value='"+'&nbsp;'+details+"' "+c+" class='form-check-input mt-0'>"+'&nbsp;'+details+
								"</div>"+
							"</div>";
 }
return sdetails;
}
function saveaccess(){
$("#prevModal").modal("hide");
$("#mySave").modal("show");
var data = new FormData(document.getElementById('accessfrm'));
		$.ajax({
            url: 'controllers/usersController.php',
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
function saveroles(){
$("#roleModal").modal("hide");
$("#mySave").modal("show");
var data = new FormData(document.getElementById('rolefrm'));
		$.ajax({
            url: 'controllers/usersController.php',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			dataType: 'json',
			type: 'POST',
            success: function(data) {
				$("#mySave").modal("hide");
				if (data['id']> -1) {toastr.success('Record saved');} else {toastr.warning('Record failed to saved');}
				
			}
		});
}