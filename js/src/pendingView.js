
function startload(){

var otk = document.getElementsByName('tk');
for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
{
    otk[x].value = qs['tk'];
}
	$.get("controllers/pendingController.php",{"tk":qs['tk'],"trans":"getprocloc"}, function(data){ fillusers(data);},"json").fail(function() {
			offlineerror();
	});
}
function fillusers(data){
	let procloc = data['procloc'];
	filterdata(procloc);
}

function filterdata(procloc){

document.getElementById('details').style.display="none";
document.getElementById('list').style.display="block";
	var tk = qs['tk'];
	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options);
	let xtitle = "Pending - Request Assist Form";
	var table = $('#ds').DataTable( {"destroy":true, 
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
		"ajax": "controllers/pendingController.php?trans=getdetails&tk="+tk+"&procloc="+procloc,
        "columns": [
			
			{ "data": "rafNum"},
            { "data": "patientname"},
			{ "data": "dateReceive" },
			{ "data": "assistCode"},
			{ "data": "officename"},
			{ "data": "amtApproved", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
			{ "className":'details',"data": null, "defaultContent": '<span class="badge bg-warning">Select</span>' }
		        ],
        "order": [[2, 'desc']]
    } );
	
	$('#ds tbody').off();
	$('#ds tbody').on('click', 'td.details', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
			let data = row.data();
			let xidassistdetails = data['idassistdetails'];
			let tk = qs['tk'];
			window.open("pending2View.html?idassistdetails="+xidassistdetails+"&tk="+tk+"&refresh=", "_self");
        
    } );
	
	
}