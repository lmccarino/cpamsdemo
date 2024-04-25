function searchthis(xform){
	let els =xform.elements;
	let cont = 0;
	
	if (!els.namedItem('lastname').value && !els.namedItem('firstname').value && !els.namedItem('birthdate').value) { toastr.warning('Invalid Entry'); cont = -1;}
	if (cont == -1){ return true; } 
	
	var data = $(xform).serialize();
	filterdata(data);
	//$.get('controllers/verifypatientController.php',data, function (d) { filterdata(d);});
	return true;
}
function clearsearch(xform){
	let els =xform.elements;
	els.namedItem('lastname').value = '';
	els.namedItem('firstname').value = '';
	els.namedItem('birthdate').value = '';
}
function getassistperiod(data){
	let xidpatient = data['idpatient'];
	let tk = qs['tk'];
	if (!tk) {tk=localStorage.getItem("tk");}
	$.get('controllers/verifypatientController.php',{"idpatient":xidpatient,"trans":"getassistperiod","tk":tk}, function (d) { filltotalamount(d);},'json').fail(function() {
			offlineerror();
			});	
	return true;
}
function filltotalamount(d){
	let xid = "idtotal"+d['idpatient'];
	let xtotal = Number(d['total']);
	let style = '';
	if (xtotal >= 10000) {
		style = 'style="color:red;"';
	} else {
		style = 'style="color:#2b7a78;"';
	}  
	document.getElementById(xid).innerHTML = "<strong "+style+">"+xtotal.toLocaleString("en-US", {style:"currency", currency:"Php"})+"</strong>";
	
}
function filterdata(d){
	d = d+"&tk="+qs['tk'];
	var table = $('#ds').DataTable( {"destroy":true, 
		dom: 'lfrtip', 
		"ajax": {"url": "controllers/verifypatientController.php?"+d, "error": function (xhr, error, code) {
            offlineerror();
        }},
        "columns": [
					{"className":      'details-control',
														"orderable":      false,
														"data":           null,
														"defaultContent": ''},
					{ "className":'details2',"data": "idpatient"},
					{ "className":'details2',"data": "benLName"},
					{ "className":'details2',"data": "benFName"},
					{ "className":'details2',"data": "benMName"},
					{ "className":'details2',"data": "benBDate"},
					{ "className":'details2',"data": "philsysid"},
					{ "className":"select","data": null, "defaultContent": '<small><button type="button" class="btn btn-warning btn-sm"><small>Select</small></button></small>'}
		        ],
        "order": [[3, 'desc']],
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
			getassistperiod(row.data())
			
        }
    } );
	$('#ds tbody').on('click', 'td.details2', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		let d = row.data();
		let xstring = "<div style='margin-bottom: 5px;'><strong>Sex:</strong> " + d['benSex'] + "</div>";
		xstring += "<div style='margin-bottom: 5px;'><strong>Address:</strong> " + d['benAddrSt'] + "</div>";
		xstring += "<div style='margin-bottom: 5px;'><strong>Contact No.:</strong> " + d['benContact'] + "</div>";
		xstring += "<div style='margin-bottom: 5px;'><strong>Philhealth No.:</strong> " + d['philhealthno'] + "</div>";
		xstring += "<div style='margin-bottom: 5px;'><strong>District:</strong> " + d['distName'] + "</div>";
		xstring += "<div style='margin-bottom: 5px;'><strong>Barangay:</strong> " + d['brgyName'] + "</div>";

		document.getElementById('contentremarks').innerHTML = xstring;
		document.getElementById('remarksModalTitle').innerHTML = 'Patient Information';
		$('#remarksModal').modal('show');
        
    } );
	$('#ds tbody').on('click', 'td.select', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		let d = row.data();
		window.opener.setData(d['idpatient']);
	});
	
	table.on( 'draw', function () {
		if (!qs['select']) {$('.select').hide();} else {$('.select').show();} 
	} );
}
function format(row){
var id = row['idpatient'];

var elem = document.getElementById(id);
if (elem){
  elem.parentNode.removeChild(id);
}
var xcontent = '<table class="table table-responsive" id="idpatient'+id+'">';
    xcontent += '<thead><tr><th>RAFNum</th><th>Requestor</th><th>Provider</th><th>Amount Approved</th><th>Date Approved</th><th>Assistance Type</th><th>CMO Note</th><th></th></tr></thead><tbody></tbody></table>';
	xcontent +='<strong>Total assistance received within the month:</strong>  <span id="idtotal'+id+'" ></span>';
	return xcontent;
}

function getchild(data){
var tableid = '#idpatient'+data['idpatient'];

var tk = qs['tk'];
	var table = $(tableid).DataTable( {"destroy":true,"searching":false,
		dom: 'lfrtip', 
		"ajax": {"url":"controllers/verifypatientController.php?trans=getdetails&idpatient="+data['idpatient']+"&tk="+tk, 
		         "error": function (xhr, error, code) {offlineerror();}
				},
        "columns": [
					{ "data": "rafnum"},
					{ "data": "requestor"},
					{ "data": "officename"},
					{ "data": "amtApproved", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
					{ "data": "dateApproved"},
					{ "data": "assistCode"},
					{ "className":'details2',"data": "noteTag"},
					{"className":"remarks","data": null, "defaultContent": '<small><button type="button" class="btn btn-info btn-sm"><small>Remarks</small></button></small>'},
		        ],
			"order": [[3, 'desc']],
    } );
	
	$(tableid+' tbody').off();
	
	$(tableid+' tbody').on('click', 'td.remarks', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		let d = row.data();
			document.getElementById('contentremarks').innerHTML = d['remarks'];
			document.getElementById('remarksModalTitle').innerHTML = 'Interview Remarks';
			$('#remarksModal').modal('show');
			
        
    } );
	
}
function startload(){

var otk = document.getElementsByName('tk');
for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
{
    otk[x].value = qs['tk'];
}

	
	
	
}