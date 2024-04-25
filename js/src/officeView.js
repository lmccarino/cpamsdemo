function startload() {
	document.getElementById('details').style.display = "block";
	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options);
	
	var tk=qs['tk'];
	var table = $('#ds').DataTable( {
		dom: 'lfrtBip', 
		buttons: [
            'copy', {extend : 'excelHtml5',title : 'List of Offices'
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true}, 
			{extend: 'pdf', 
			title : 'List of Offices'
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true},
			{extend: 'print',
			title : 'List of Offices',
			 messageTop: caption, messageBottom: '<br/> Prepared by: '+ fullname + ' ' +stoday, footer:true
			
			}
        ],
		"ajax": "controllers/officeController.php?trans=getdetails&tk="+tk,
        "columns": [
            {"className":      'details-control',
												 "orderable":      false,
												 "data":           null,
												 "defaultContent": ''},
			{ "data": "officecode" },
			{ "data": "officename" },
			{ "data": "officeType" },
			{"className":'deleteclassbutton1',"defaultContent": '<button type="button" data-toggle="tooltip" title="Delete" class="btn btn-sm btn-danger" >Delete</button>'}	
        ],
        "order": [[0, 'desc']]
    } );
	$('#ds tbody').off();
	$('#ds tbody').on( 'click', 'td.deleteclassbutton1', function (o) {
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
    } );
	
	initload();
	
	
	
}
function disableprovider(cb){
let xform = cb.form.elements;
	if (cb.selectedIndex == 0){
		xform.namedItem('provCat').disabled = false;
		
	} else {
		xform.namedItem('provCat').selectedIndex = 0;
		xform.namedItem('provCat').disabled = true;
	}
}


function format(row){
var idoffice = row['idoffice'];
var elem = document.getElementById(idoffice);
if (elem){
  elem.parentNode.removeChild(idoffice);
}
  return '<div class="col-12" id="'+idoffice+'"></div>';
}
function deletethis(data){
if (maccess['ldelete']<1){
	toastr.error('Record cannot be deleted');
	return false;
}
bootbox.confirm({
    message: 'Delete '+ data['officename'] +'?',
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
			$.get("controllers/officeController.php",{trans:"delete",idoffice:data['idoffice'],"tk":tk},function(data){
										if (data['idoffice']>-1){
											toastr.success('Record Deleted');
											
									        $('#ds').DataTable().ajax.reload(null,false);} else { toastr.danger('Record cannot be deleted');}
							        },'json');
		}
    }
});
}






function getchild(data){
var idoffice = '#'+data['idoffice'];
document.getElementsByName('myfields')[0].disabled = true;
//var nform = $('#entryform').clone(true);
var tform = document.getElementById('form'+data['idoffice']);
if (tform){
	tform.parentNode.removeChild('form'+data['idoffice']);
}
var nform = $('#entryform').clone(true).prop('id','form'+data['idoffice']);
nform.appendTo( idoffice );

var x = document.getElementById('form'+data['idoffice']);
	x.elements.namedItem('tk').value=qs['tk'];
	x.elements.namedItem('trans').value='UPDATE';
	x.elements.namedItem('idoffice').value=data['idoffice'];
	x.elements.namedItem('officecode').value=data['officecode'];
	x.elements.namedItem('officename').value=data['officename'];
	x.elements.namedItem('location').value=data['location'];
	x.elements.namedItem('contactperson').value=data['contactperson'];
	x.elements.namedItem('emailaddress').value=data['emailaddress'];
	x.elements.namedItem('provCat').value=data['provCat'];
	x.elements.namedItem('contactno').value=data['contactno'];
	x.elements.namedItem('officeType').value=data['officeType'];
	x.elements.namedItem('active').value=data['active'];
	
	x.elements.namedItem('myfields').disabled = true;
	if (maccess['ledit']>0) {x.elements.namedItem('butupdate').style.display='inline';} else {x.elements.namedItem('butupdate').style.display='none';}
	x.elements.namedItem('butsave').style.display='none';
	x.elements.namedItem('butcancel').style.display='none';
    
}
function additem(){
	var myfield = document.getElementsByName('myfields')[0];
	myfield.disabled = false;
    document.getElementsByName('idoffice')[0].value = -1;
	document.getElementsByName('active')[0].value = 'Y';
	document.getElementsByName('officeType')[0].value = '';
    document.getElementsByName('officecode')[0].value= '';
	document.getElementsByName('officename')[0].value= '';
	document.getElementsByName('contactperson')[0].value= '';
	document.getElementsByName('location')[0].value= '';
	document.getElementsByName('emailaddress')[0].value= '';
	document.getElementsByName('contactno')[0].value='';
	document.getElementsByName('provCat')[0].value='';
	
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
		
		if (x1[i].name == 'officecode') { if (!x1[i].value){ toastr.warning('Invalid Office Code');  cont = -1; }}
		if (x1[i].name == 'officename') { if (!x1[i].value){ toastr.warning('Invalid Office Name');  cont = -1; }}
	}
	
	if (cont > -1){
	let els = thisform.elements;
		els.namedItem('provCat').disabled = false;
		
		var data = $(thisform).serialize();
		
		$.ajax({
            url: 'controllers/officeController.php',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			dataType: 'json',
			type: 'GET',
            success: function(data) {
				    if (data["idoffice"] > -1){
							
							$('#ds').DataTable().ajax.reload(null,false);
							
							
						toastr.success('Record Saved');
					} else {
						toastr.error('Saving Failed.  Please check details');
					}
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