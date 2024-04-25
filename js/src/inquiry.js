
function startload(){
	var otk = document.getElementsByName('tk');
	for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
	{
	    otk[x].value = qs['tk'];
	}

	let xform = document.getElementById('searchForm').elements;
	//xform.namedItem('dteFrom').value = configuredate();
	//xform.namedItem('dteTo').value = configuredate();

	var x = document.getElementById('searchForm');
	var statElem = x.elements.namedItem('status');
	var data = ['Approved', 'Cancelled']
	
	for (a = 0;a < data.length; a++){
		var option = document.createElement("option");
						option.text = data[a];
						option.value = data[a];
						statElem.add(option); 
	}
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

	if ($("#searchForm").serialize().trim().length <= 7 )
		return false;
	
	ajx = "controllers/approvedController.php?trans=LIST7&tk=" + tk + "&office=" + soffice + "&" + $("#searchForm").serialize();

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
				{"className":      'details-control',
													"orderable":      false,
													"data":           null,
													"defaultContent": ''},
				{ "data": "rafNum" },
				{ "data": "benLName" },
				{ "data": "benFName" },
				{ "data": "benMName" },
				{ "data": "noteTag" },
				{ "data": "billAmount" },             
				{ "data": "amtApproved"},
				{ "data": "status"},
				//{ "data": function (o) { return recordTask(o); }},
				//{"className":'taskBtn',"defaultContent": '<div class="dropdown"><button type="button" data-toggle="tooltip" title="Override" class="btn btn-light" ><i class="fa-solid fa-list-check" aria-hidden="true"></i></button><div class="dropdown-content"><a><i class="fa-solid fa-rotate-left btnDisApprove"></i> DisApprove </a><a><i class="fa-solid fa-print btnGL"></i> GL </a></a><a><i class="fa-solid fa-print btnIntake"></i> Intake </a></a><a><i class="fa-solid fa-print btnCert"></i> Certificate </a></div></div>'}	
			],
			"order": [[0, 'desc']]
		} );
	}
	$('#ds tbody').off();

    $('#ds tbody').on( 'click', 'td.taskBtn', function (o) {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
		deletethis(row.data());
	} );
/*TODO delete this
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
	});*/
	
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
	var cas = "'"+o['hospitalcase']+"'";
	var provider = "'"+o['provCode']+"'";
	var rem = "'"+o['remTag']+"'";
	var amtApproved = "'"+o['amtApproved']+"'";

	var callCancel = "showCancel("+id+", "+bill+", "+lname+", "+fname+", "+mname+", "+note+", "+amt+",  "+raf+", "+dte+", "+loc+", "+addr+", "+brgy+", "+cas+", "+provider+", "+rem+")";
	//var callDisapprove = "showDisapprove("+id+", "+bill+", "+lname+", "+fname+", "+mname+", "+note+", "+amt+",  "+raf+", "+dte+", "+loc+", "+addr+", "+brgy+", "+cas+", "+provider+", "+rem+")";
	var callReapprove = "showReapprove("+id+", "+bill+", "+lname+", "+fname+", "+mname+", "+note+", "+amt+",  "+raf+", "+dte+", "+loc+", "+addr+", "+brgy+", "+cas+", "+provider+", "+rem +", "+amtApproved+")";
	//var callDisapprove = "showDisapprove("+id+")";
	var callIntake = "showIntake("+id+")";
	var callCert = "showCert("+id+")";
	var callGL = "showGL("+id+")";
	var callIndigency = "showIndigency("+id+")";
	var row = '<div class="overridedropdown"></div>';	
	return row;
}