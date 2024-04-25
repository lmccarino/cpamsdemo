

function filterdata(){
document.getElementById('details').style.display="none";
document.getElementById('list').style.display="block";
	var tk = qs['tk'];
	var txt = "trans=getdetails&tk="+tk;
	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options);

	var table = $('#ds').DataTable( {"destroy":true, 
		dom: 'lfrtBip', 
		buttons: [
            'copy', {extend : 'excelHtml5',title : 'Audit Logs'
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true}, 
			{extend: 'pdf', 
			title : 'Audit Logs'
			, messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true},
			{extend: 'print',
			title : 'Audit Logs',
			 messageTop: caption, messageBottom: '\n Prepared by: '+ fullname + ' ' +stoday, footer:true
			
			}
        ],
		"ajax": {"url":"controllers/auditlogsController.php?trans=getdetails&tk="+tk,
				"error": function (xhr, error, code) {offlineerror();}
		},
        "columns": [
			{"className":      'details-control',
												 "orderable":      false,
												 "data":           null,
												 "defaultContent": ''},
			{ "data": "userid"},
			{ "data": function (d) {return showimage(d);} },
			{ "data": "fullname" },
			{ "data": "officename" },
			{ "data": "ldate" },
			{ "data": "ip" },
			{ "data": "token" },
			{ "data": "odate" }
			
		        ],
        "order": [[5, 'desc']]
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
			
        }
    } );
}


function showimage(d){
var img = '<img src="userimages/person.png" class="img-responsive" style="max-width:1.5em">';
if (d['image']) {
	img = '<img src="userimages/'+d['image']+'" class="img-responsive" style="max-width:1.5em">';
}
return img;

}
function format(row){
var id = row['idtk'];

var elem = document.getElementById(id);
if (elem){
  elem.parentNode.removeChild(id);
}
var xcontent = '<table class="table table-sm" id="idtk'+id+'" style="width:100%">';
    xcontent += '<thead><tr><th>Date</th><th>Actions</th></tr></thead><tbody></tbody></table>';
	return xcontent;
}
function getchild(data){
var tableid = '#idtk'+data['idtk'];

var tk = qs['tk'];

	var today = new Date();
	var asDate = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "As of "+ asDate.toLocaleDateString("en-US", options);
	let xtitle = "List of Transactions - User:  "+ data['fullname'];

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
		"ajax": {"url":"controllers/auditlogsController.php?trans=gettrans&tk2="+data['token']+"&tk="+tk, 
		         "error": function (xhr, error, code) {offlineerror();}
				},
        "columns": [
			
            { "className":'detail2',"data": "transdate"},
			{ "className":'details2',"data": "remarks"}
			
		        ]
        
    } );
	
	$(tableid+' tbody').off();
	
	
}
function startload(){

var otk = document.getElementsByName('tk');
for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
{
    otk[x].value = qs['tk'];
}

	filterdata();
	
}