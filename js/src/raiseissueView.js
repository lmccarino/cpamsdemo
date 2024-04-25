ClassicEditor
			.create( document.querySelector( '#editor' ), {
				
				toolbar: {
					items: [
						'heading',
						'|',
						'bold',
						'italic',
						'link',
						'bulletedList',
						'numberedList',
						'|',
						'outdent',
						'indent',
						'|',
						'imageUpload',
						'blockQuote',
						'insertTable',
						'mediaEmbed',
						'undo',
						'redo',
						'CKFinder',
						'alignment',
						'fontBackgroundColor',
						'fontColor',
						'fontSize',
						'fontFamily',
						'highlight',
						'horizontalLine',
						'pageBreak',
						'removeFormat',
						'specialCharacters',
						'strikethrough',
						'subscript',
						'superscript',
						'underline',
						'lineHeight'
						,'sourceEditing'
						,'maximize'
					]
				},
				language: 'en',
				image: {
					toolbar: [
						'imageTextAlternative',
						'imageStyle:full',
						'imageStyle:side'
					]
				},
				table: {
					contentToolbar: [
						'tableColumn',
						'tableRow',
						'mergeTableCells',
						'tableCellProperties',
						'tableProperties'
						
					]
				},
				licenseKey: '',
				ckfinder: {openerMethod: 'popup'}
				
			} )
			.then( editor => {
				window.editor = editor;
				
			} )
			.catch( error => {
				console.error( error );
			} );
let xtitle ='Raise Issue';
function adddetails(){
var x = document.getElementById('entryform').elements;
for (let e in x) {  

var elem = x[e];
  if (elem.tagName == "INPUT"){
		if (elem.type=="text"){
			elem.value = '';
		}
		if (elem.type=="date"){
		  elem.value = configuredate();
		
		}
		if (elem.type=="number"){
			elem.value =0;
		}
	}
	
  if (elem.tagName == "SELECT"){
	elem.selectedIndex = 0;
  }
  if (elem.tagName == "TEXTAREA"){
		elem.value ="";
		
  }
}

	x.namedItem('idissues').value = -1;
	x.namedItem('trans').value ="ADD";
	editor.setData('');
	document.getElementById('list').style.display="none";
	document.getElementById('details').style.display="block";
	document.getElementById('divattachment').style.display = "none";
	document.getElementById('submitbutton').style.display = "none";
	initload();
	$(".savebutton").show();
	
}
function submitdetails(){
let tk = qs['tk'];
let idissues = document.getElementById('idissues').value;
	$.get( "controllers/raiseissueController.php",{"tk":tk, "idissues":idissues, "trans":"submit"}, function( d ) {
		toastr.success('Record Saved');
		$('#ds').DataTable().ajax.reload(null,false);
		document.getElementById('savebut').style.display = "none";
		document.getElementById('submitbutton').style.display = "none";
		
	});
}
function savedetails(){
var myform = document.getElementById('entryform');

//var data = $(myform).serialize();
let els = myform.elements;
var cont = 1;

els.namedItem('ckcontent').value = editor.getData();
var data = new FormData(myform);
if (els.namedItem('subject').value == "") {toastr.warning('Invalid subject');cont = -1;}

if (els.namedItem('ckcontent').value == "") {toastr.warning('Invalid issue');cont = -1;}

if (cont > -1 ) {


$.ajax({
            url: 'controllers/raiseissueController.php',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			dataType: 'json',
			type: 'POST',
            success: function(data) {
						xform = document.getElementById('entryform').elements;
						xform.namedItem('trans').value = "UPDATE";
						document.getElementById('idissues').value = data['idissues'];
						
						
						document.getElementById('divattachment').style.display = "block";
						document.getElementById('keyvalue').value = data['idissues'];
						document.getElementById('keyname').value = 'idissues';
						getattachments(data['idissues']);
						initload();
						$('#ds').DataTable().ajax.reload(null,false);
						toastr.success('Record Saved');
						document.getElementById('submitbutton').style.display = "inline";
						
					}
			});
	


}
}

function filterdata(){

document.getElementById('details').style.display="none";
document.getElementById('list').style.display="block";
	var tk = qs['tk'];
	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options);

	var table = $('#ds').DataTable( {"destroy":true, 
		dom: 'lfrtBip', 
		buttons: [
            'copy', {extend : 'excelHtml5',title : 'Raised Issues'
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true}, 
			{extend: 'pdf', 
			title : 'Raised Issues'
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true},
			{extend: 'print',
			title : 'Raised Issues',
			 messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true
			
			}
        ],
		"ajax": "controllers/raiseissueController.php?trans=getallactive&tk="+tk,
        "columns": [
			{"className":'details-control',
												 "orderable":      false,
												 "data":           null,
												 "defaultContent": ''},
			{ "className":'details',"data": "idissues"},
            { "className":'details',"data": "subject"},
			{ "className":'details',"data": "datefiled" },
			{ "className":'details',"data": "status"},
			{ "data": function (d) {return trash(d);} }
		        ],
        "order": [[1, 'asc']]
    } );
	
	$('#ds tbody').off();
	$('#ds tbody').on('click', 'td.details', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );

		
			getdetails(row.data());
     
        
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
	setTimeout(checkmodule,1000);
	
}

function format(row){
var id = row['idissues'];

var elem = document.getElementById(id);
if (elem){
  elem.parentNode.removeChild(id);
}
var xcontent = '<table class="table table-sm" id="idissues'+id+'" style="width:100%">';
    xcontent += '<thead><tr><th>Remarks</th><th>Status</th><th>StartDate</th><th>EndDate</th><th>Receiver</th></tr></thead><tbody></tbody></table>';
	return xcontent;
}
function getchild(data){
var tableid = '#idissues'+data['idissues'];

var tk = qs['tk'];

	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options)+ '\n Ticket Number: '+data['idissues'];

	var table = $(tableid).DataTable( {"destroy":true,"searching":false,
		dom: 'lfrtBip', 
		buttons: [
            'copy', {extend : 'excelHtml5',title : 'Raised Issues'
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true}, 
			{extend: 'pdf', 
			title : 'Raised Issues'
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true},
			{extend: 'print',
			title : 'Raised Issues',
			 messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true
			
			}
        ],
		"ajax": "controllers/raiseissueController.php?trans=gettrans&idissues="+data['idissues']+"&tk="+tk,
        "columns": [
			
            { "className":'detail2',"data": "remarks"},
			{ "className":'details2',"data": "status"},
			{ "className":'details2',"data": "transdate"},
			{ "className":'details2',"data": "enddate"},
			{ "className":'details2',"data": "fullname"} 
			
		        ],
        "order": [[2, 'desc']]
    } );
	
	$(tableid+' tbody').off();
	$(tableid+' tbody').on('click', 'td.details2', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
 
        
			showdetails(row.data());
        
    } );
	
}
function showdetails(d){
	let xstring = "<label>Remarks</label><p>"+d['remarks']+"</p>";
	xstring += "<p>Status: <strong>"+d['status']+"</strong></p>";
	xstring += "<p>Transaction Date: <strong>"+d['transdate']+"</strong> <label>End Date: <strong>"+d['enddate']+"</strong>";
	xstring += "<p>Received by: <strong>"+d['fullname']+"</strong></p>";
	document.getElementById('divdetails').innerHTML = xstring;
	document.getElementById('titledetails').innerHTML = "Ticket Number: <strong>"+d['idissues']+"</strong>";
	$("#myDetails").modal("show");
}
function closemyDetails(){
	$("#myDetails").modal("hide");
}
function trash(d){
let x ="";
	if (d['status']=="DRAFT"){
		let params ="'"+d['idissues']+"','"+d['subject']+"'";
	
		
			x = '<button class="btn btn-link deletebutton" type="button" onclick="deletethisrec('+params+')"><span class="fas fa-trash-alt"></span></button>';

					
			}
	return x;
}



function getdetails(data){
	document.getElementById('list').style.display="none";
	document.getElementById('details').style.display="block";
	editor.setData(decodeHtml(data['content']));
	//document.getElementById('editor').value = data['content'];
	document.getElementById('subject').value = data['subject'];
	
	let xform = document.getElementById('entryform').elements;
						xtrans = xform.namedItem('trans');
						xtrans.value = "UPDATE";
	document.getElementById('idissues').value = data['idissues'];
	document.getElementById('datefiled').value = data['datefiled'];
	document.getElementById('filedby').value = fullname;
	document.getElementById('submitbutton').style.display = "inline";
	document.getElementById('keyvalue').value = data['idissues'];
	document.getElementById('keyname').value = 'idissues';
	initload();
	getattachments(data['idissues']);
	//document.getElementById('').value = 
	
}

function deletethisrec(idissues, subject){

bootbox.confirm({
    message: 'Delete '+ subject + ' ?',
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
			$.get("controllers/raiseissueController.php",{"trans":"delete","idissues":idissues,"tk":tk},function(data){
										if (data['idissues']>-1){
											toastr.success('Record Deleted');
											
									        $('#ds').DataTable().ajax.reload(null,false);} else { toastr.warning('Record cannot be deleted');}
							        },'json');
		}
    }
});
}

function closethis(){
document.getElementById('details').style.display ="none";
document.getElementById('list').style.display="block";
}


function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}





function startload(){

var otk = document.getElementsByName('tk');
for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
{
    otk[x].value = qs['tk'];
}

	
	filterdata();
	startattachment();
}


function checkmodule(){	
		
		maccess = getmoduleaccess('Raise Issue');
		
		if (maccess['name']){ 
			tmodule = true;
			
			if (maccess['ladd']<1) {
				
				$(".addbutton").hide();
			}
			
			if (maccess['ldelete'] < 1){
				$(".deletebutton").hide();
			}
			
		}
		
		
};
setTimeout(checkmodule, 1000);
function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}