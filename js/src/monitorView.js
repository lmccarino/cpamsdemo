
function getdetails(){
	var tk = qs['tk'];
	var today = new Date();
	var options = { year: 'numeric', month: 'long', day: 'numeric' };
	var stoday = today.toLocaleDateString("en-US", options);
	var caption = "Adjustment Transactions on Alloment As of "+ stoday;
	let xtitle = "Fund Balance Details";
		var table = $('#ds').DataTable( {"destroy":true, "searching":false, "pageLength": 20,
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
		"ajax": "controllers/monitorController.php?trans=getdetails&tk="+tk,
        "columns": [
			
			{ "data": "dateSBal"},
			{ "data": "credit", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
            { "data": "debit", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
			{ "data": "balAmount",  "render": $.fn.dataTable.render.number( ',', '.', 2 ,'') },
			{ "data": "details"},
				{"data":"balCritLevel", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
			{ "data": "fullname"}
		        ],
        "order": [[0, 'asc']]
		
    } );
	
	$('#ds tbody').off();
	
}

function startload(){

var otk = document.getElementsByName('tk');
for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
{
    otk[x].value = qs['tk'];
}

	reauthenticate();	
}

function reauthenticate(){
	_registered = false;
	$('#content').prop('hidden',true);

	let tk = qs['tk'];
	let data = 'trans=verify&tk='+tk;
	window.fetch('controllers/webauthnController.php?' + data, {
		method: 'GET', // *GET, POST, PUT, DELETE, etc.
		cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
	}
	).then(function (response) {
		return response.json();
	}).then(function (json) {
		if (json.success === false) {
			throw new Error(json.msg);
		}
		if (json['error']) {
			bootbox.alert("Device not yet registered...");
			return false;
		}

		json.publicKey.user.id = Uint8Array.from(json.publicKey.user.id, c => c.charCodeAt(0)),
			json.publicKey.challenge = Uint8Array.from(json.publicKey.challenge, c => c.charCodeAt(0));

		if (json.publicKey.excludeCredentials) {
			for (let cred of json.publicKey.excludeCredentials) {
				cred.id = Uint8Array.from(cred.id, c => c.charCodeAt(0));
			}
		}

		return json;

	}).then(function (options) {
		return navigator.credentials.create(options);
	}).then(function (cred) {
		let clientDataJSON = arrayBufferToBase64(cred.response.clientDataJSON);
		$.get('controllers/webauthnController.php', { "clientDataJSON": clientDataJSON, trans: "verify2", "tk": qs['tk'] }, function (data) {
			if (data['error']) {
				toastr.error('Device not yet registered');
				reauthenticate();	
			} else {
				_registered = true;
				$('#content').prop('hidden',false);
				getdetails();	
			}
		}, 'json');
	});
}