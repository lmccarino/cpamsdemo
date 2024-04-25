function startload(){

let tk = qs['tk'];
let data = 'trans=verify&tk='+tk;
$("#mySave").modal("show");
	window.fetch('controllers/webauthnController.php?'+data, {
			method: 'GET', // *GET, POST, PUT, DELETE, etc.
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			}
		).then(function(response) {
			return response.json();
			}).then(function(json) {
					$("#mySave").modal("hide");
					if (json.success === false) {
						throw new Error(json.msg);
					}
					if (json['error']){
						bootbox.alert("Device not yet registered...");
						return false;
					}
					$("#mySave").modal("show");
					json.publicKey.user.id = Uint8Array.from( json.publicKey.user.id, c => c.charCodeAt(0)),
					json.publicKey.challenge = Uint8Array.from(json.publicKey.challenge, c => c.charCodeAt(0));

					if (json.publicKey.excludeCredentials) {
						for (let cred of json.publicKey.excludeCredentials) {
							cred.id = Uint8Array.from(cred.id, c => c.charCodeAt(0));
						}
					}
					
					return json;

				}).then(function(options){
					$("#mySave").modal("hide");
					return navigator.credentials.create(options);
				}).then(function(cred) {
					$("#mySave").modal("show");
					let clientDataJSON = arrayBufferToBase64(cred.response.clientDataJSON);
					$.get('controllers/webauthnController.php',{"clientDataJSON":clientDataJSON,trans:"verify2","tk":qs['tk']},function (data){
						$("#mySave").modal("hide");
						if (data['error']){
							//bootbox.alert('Device not yet registered');
							verifyagain();
						} else {						
							bootbox.alert('Device Registered');
						}
					},'json');
					
					
				});
				
}
function verifyagain(){
let tk = qs['tk'];
let data = 'trans=verify&tk='+tk;
$("#mySave").modal("show");
	window.fetch('controllers/webauthnController.php?'+data, {
			method: 'GET', // *GET, POST, PUT, DELETE, etc.
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			}
		).then(function(response) {
			return response.json();
			}).then(function(json) {
					
					json.publicKey.user.id = Uint8Array.from( json.publicKey.user.id, c => c.charCodeAt(0)),
					json.publicKey.challenge = Uint8Array.from(json.publicKey.challenge, c => c.charCodeAt(0));

					if (json.publicKey.excludeCredentials) {
						for (let cred of json.publicKey.excludeCredentials) {
							cred.id = Uint8Array.from(cred.id, c => c.charCodeAt(0));
						}
					}
					
					return json;

				}).then(function(options){
					$("#mySave").modal("hide");
					return navigator.credentials.create(options);
				}).then(function(cred) {
					$("#mySave").modal("show");
					let clientDataJSON = arrayBufferToBase64(cred.response.clientDataJSON);
					$.get('controllers/webauthnController.php',{"clientDataJSON":clientDataJSON,trans:"verify2","tk":qs['tk']},function (data){
						$("#mySave").modal("hide");
						if (data['error']){
							bootbox.alert('Device not yet registered');
						} else {						
							bootbox.alert('Device Registered');
						}
					},'json');
					
					
				});

}