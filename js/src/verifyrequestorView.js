function searchthis(xform){
	let els =xform.elements;
	let cont = 0;
	
	if (!els.namedItem('requestor').value) { toastr.warning('Invalid Entry'); cont = -1;}
	if (cont == -1){ return true; } 
	
	var data = $(xform).serialize();
	filterdata(data);
	return true;
}

function clearsearch(xform){
	let els =xform.elements;
	els.namedItem('requestor').value = '';
}

function filterdata(d){
	d = d+"&tk="+qs['tk'];
	var table = $('#ds').DataTable( {"destroy":true, 
		dom: 'lfrtip', 
		"ajax": {"url": "controllers/verifyrequestorController.php?"+d, "error": function (xhr, error, code) {
            offlineerror();
        }},
        "columns": [
					{"className":      'details-control',
														"orderable":      false,
														"data":           null,
														"defaultContent": ''},
					{ "className":'details2',"data": "requestor"},
					{ "className":'details2',"data": "reqAddr"}
		        ],
        "order": [[2, 'desc']],
    } );
	
	$('#ds tbody').off();
	$('#ds tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
 
        if ( row.child.isShown() ) {
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            row.child( format(row.data())).show();
            tr.addClass('shown');
			getchild(row.data());
			
        }
    } );
}

function format(row){
	var id = row['requestor'].trim().toUpperCase();
	
	var elem = document.getElementById(id);
	if (elem){
	  elem.parentNode.removeChild(id);
	}
	var xcontent = '<table class="table table-responsive" id="req">';
		xcontent += '<thead><tr><th>RAFNum</th><th>Patient Name</th><th>Relationship to the Patient</th><th>Provider</th><th>Amount Approved</th><th>Date Approved</th><th>Assistance Type</th><th></th></tr></thead><tbody></tbody></table>';
		return xcontent;

	}
	

function getchild(data){
	
	var tableid = '#req';
	var tk = qs['tk'];
	var tableid = $('#req').DataTable( {"destroy":true, 
		dom: 'lfrtip', 
		"ajax": {"url": "controllers/verifyrequestorController.php?trans=getTransactions&requestor="+data['requestor']+"&tk="+tk, "error": function (xhr, error, code) {
            offlineerror();
        }},
        "columns": [
					{ "data": "rafnum"},
					{
						"data": null,
						"render": function (data, type, row) {
							return row.benLName + ', ' + row.benFName  + '  ' + row.benMName;
						}
					},
					{ "data": "relation"},
					{ "data": "provCode"},
					{ "data": "amtApproved", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
					{ "data": "dateApproved"},
					{ "data": "assistCode"}
		        ],
        "order": [[2, 'desc']],
    } );
	

	$(tableid+' tbody').off();

}

function startload(){

var otk = document.getElementsByName('tk');
for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
{
    otk[x].value = qs['tk'];
}
	
}