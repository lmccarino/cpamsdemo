function startload(){
	return true;
	
		
}

function webauthn(){
let webauthnid = window.localStorage.getItem('webauthnid');

let formData = new FormData();
			formData.append('trans', 'getCreateArgs');
			formData.append('webauthnid', webauthnid);

window.fetch('controllers/webauthnloginController.php?', {
			
			method: "POST",
			body: formData
			}
		).then(function(response) {
			
			return response.json();
			}).then(function(json) {
					
					if (json.success === false) {
						throw new Error(json.msg);
					}
					if (json.error){
						bootbox.alert("Device not yet registered...");
						return false;
					}
					
					json.publicKey.user.id = Uint8Array.from( json.publicKey.user.id, c => c.charCodeAt(0)),
					json.publicKey.challenge = Uint8Array.from(json.publicKey.challenge, c => c.charCodeAt(0));

					if (json.publicKey.excludeCredentials) {
						for (let cred of json.publicKey.excludeCredentials) {
							cred.id = Uint8Array.from(cred.id, c => c.charCodeAt(0));
						}
					}
					
					return json;
				}).then(function(options){
					
					return navigator.credentials.create(options);
				}).then(function(cred) {
					
					let clientDataJSON = arrayBufferToBase64(cred.response.clientDataJSON);
					let webauthnid = window.localStorage.getItem('webauthnid');
					$.get('controllers/webauthnloginController.php',{"clientDataJSON":clientDataJSON,trans:"verify2","webauthnid":webauthnid},function (data){
					
						if (data['error']){
							//bootbox.alert('Device not yet registered');
							verifyagain();
						} else {						
							//bootbox.alert('proceed to login');
							verify2(data['userid'], data['webauthnid']);
						}
					},'json');
					
					
				});
}
function verifyagain(){

let webauthnid = window.localStorage.getItem('webauthnid');
let formData = new FormData();
			formData.append('trans', 'getCreateArgs');
			formData.append('webauthnid', webauthnid);

window.fetch('controllers/webauthnloginController.php?', {
			
			method: "POST",
			body: formData
			}
		).then(function(response) {
			
			return response.json();
			}).then(function(json) {
					
					if (json.success === false) {
						throw new Error(json.msg);
					}
					if (json.error){
						bootbox.alert("Device not yet registered...");
						return false;
					}
					
					json.publicKey.user.id = Uint8Array.from( json.publicKey.user.id, c => c.charCodeAt(0)),
					json.publicKey.challenge = Uint8Array.from(json.publicKey.challenge, c => c.charCodeAt(0));

					if (json.publicKey.excludeCredentials) {
						for (let cred of json.publicKey.excludeCredentials) {
							cred.id = Uint8Array.from(cred.id, c => c.charCodeAt(0));
						}
					}
					
					return json;
				}).then(function(options){
					
					return navigator.credentials.create(options);
				}).then(function(cred) {
					
					let clientDataJSON = arrayBufferToBase64(cred.response.clientDataJSON);
					let webauthnid = window.localStorage.getItem('webauthnid');
					$.get('controllers/webauthnloginController.php',{"clientDataJSON":clientDataJSON,trans:"verify2","webauthnid":webauthnid},function (data){
					
						if (data['error']){
							bootbox.alert('Device not yet registered');
							return false;
						} else {						
							
							verify2(data['userid'], data['webauthnid']);
						}
					},'json');
					
					
				});

}
function verify2(userid, webauthnid){
	
	$.get("controllers/loginController2.php",{"userid":userid,"webauthnid":webauthnid}, function(data){ processverification(data);},"json").fail(function() {
			offlineerror();
		});
	
}
