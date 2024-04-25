let checking=false;
var menu;
var qs = get_query();
var fullname;
let useraccess=[];
let maccess=[];
var soffice;
var role;
let usignature;
let userid;
let token;
let xbase = location.origin;

function ready(){
	
	
	xbase = xbase+"/";
	//******************* 10/24/2022 [commented - start]
	// $("#header").load("header.html");
	// $("#header1").load("header1.html");
	// $("#header1").load("index.php");
	//******************* 10/24/2022 [commented - start]
	
	//******************* 11/2/2022 [added - start]
	var isMobile = navigator.userAgent.toLowerCase().match(/mobile/i);
	if (isMobile) { 
		$('#loginusercontainer').css('display','none');
		$('#time').css('display','none'); 
	} else{
		startTime();
		$('#loginusercontainer').css('display','inline');
	}
	//******************* 11/2/2022 [added - end]

	$.get("controllers/checkstatus.php",{"token":qs['tk']}, function(data, xstatus){ 
			// landingpage(data, xstatus); //******************* 10/24/2022 [added]
			signbutton(data, xstatus); //******************* 10/24/2022 [commented]
		},"json").fail(function() {
			offlineerror();
	});	


	
    
}
$(document).ready(function(){

  ready();

  const documentUrlSearch = new URL(window.location.href);
  const documentToken = documentUrlSearch.searchParams.get('tk');
  if(documentToken != undefined)
	verifytoken();

  jQuery.fn.dataTable.Api.register( 'sum()', function ( ) {
		return this.flatten().reduce( function ( a, b ) {
		if ( typeof a === 'string' ) {
			a = a.replace(/[^\d.-]/g, '') * 1;
		}
		if ( typeof b === 'string' ) {
			b = b.replace(/[^\d.-]/g, '') * 1;
		}

		return a + b;
		}, 0 );
	} );
	$('[data-toggle="tooltip"]').tooltip();
	
	

	
});
function offlineerror(){
		toastr.error('System is Offline, Please check your connection');
	   
	   return true;
}
// ******************************************************* 10/24/2022 [start]
// function landingpage(data, xstatus){
// 	if (data['userid']==-1){
// 		// window.location.open('index.php');
// 	} else {
// 		// window.location.open('index.html');
// 	}
// }
function openloginform(){
	// $("#header").html('').load("header.html",()=>{
	// 	$("#loginModal").modal('show');
	// });
	
	SignIn();
}
// ******************************************************* 10/24/2022 [end]

function signbutton(data, xstatus){
	
   if (xstatus != 'success' ){
	   toastr.error('System is Offline, Please check your connection');
	   
	   return true;
   } 
	
   if (data['userid']==-1){
	   
		$('.landingcontainer').show();
		// $('#header').hide();
			
		//document.getElementById('login').style.display = "block"; 
		$(".login").show();
		SignIn();			
		$(".logout").hide();
		
		//if (document.getElementById('menuicon')){document.getElementById('menuicon').style.display = "none";}
		//$('.menuicon').hide();alert('hide icon');
		if (document.getElementById('content')){document.getElementById('content').style.display = "none";}
		
   } else {

   		// ***************************************************** 10/26/2022 [added - start]
   		$('.landingcontainer').attr('style','display : none;');   		
   		$('body').addClass('logged');
		
		if (document.getElementById('header1')){
			$("#header1").load("header1.html",()=>{
				$('.login').attr('style', 'display:none;');
   		// ***************************************************** 10/26/2022 [added - end]

	   	// ***************************************************** 10/26/2022 [commented - start]
		// //document.getElementById('login').style.display = "none";
		// // $(".loginform").hide(); 
		// //document.getElementById('logout').innerHTML =  '<span><img src="userimages/'+data['image']+'"style="max-height:1.5em"><span id="fullname">'+data['fullname']+'</span></span>';
		// $(".logout")[0].innerHTML = '<span><img src="userimages/'+data['image']+'"style="max-height:1.5em"><span id="fullname">'+data['fullname']+'</span></span>';
		// //$(".logout")[1].innerHTML = $(".logout")[0].innerHTML;
		// //document.getElementById('logout').style.display = "block";
		// $(".login").hide();
		// $(".logout").show();
		// ***************************************************** 10/26/2022 [commented - end]

			menu = data['menu'];
				
			fullname = data['fullname'];
			soffice = data['office'];
			usignature = data['signature'];
			userid = data['userid'];
			role = data['role'];
			showmenu(data['userid'], data['token']);
			localStorage.setItem("tk", data['token']);
			document.getElementById('headerProfile').href = "usersView1.html?tk="+data['token']+"&refresh=";
		// **************************************************** 10/26/2022 [added - start]		
		// $(".header1 b")[0].H = '<span><img src="userimages/'+data['image']+'"style="max-height:1.5em"><span id="fullname">'+data['fullname']+'</span></span>';
			document.getElementById('usernamecontainer').innerHTML= fullname;
			profile = '<img src="userimages/'+data['image']+'"style="max-height:1.5em"/>';
			document.getElementById('profileimg').innerHTML= profile;

			shownotifications();
			if (document.getElementById('content') 
				&& $('.landingcontainer').length > 0
			    && (role.includes('1') || role.includes('2'))){document.getElementById('content').style.display = "block";}
	   	// document.getElementById('usernamecontainer').innerHTML = fullname;
	   	// $("#loginusercontainer").innerHTML = '<span><img src="userimages/'+data['image']+'"style="max-height:1.5em"><span id="fullname">'+data['fullname']+'</span></span>';
	   	// $(".loginusercontainer").innerHTML = '<span><img src="userimages/'+data['image']+'"style="max-height:1.5em"><span id="fullname">'+data['fullname']+'</span></span>';
	   	// alert('<span><img src="userimages/'+data['image']+'"style="max-height:1.5em"><span id="fullname">'+data['fullname']+'</span></span>');
	   	// console.log(fullname);
		// **************************************************** 10/26/2022 [added - end]		
		
			try {
				startload(data['token']);
			}
			catch(err) {
				setTimeout(function(){ startload(data['token']), 9000});
			}
			});		
		}
		if (document.getElementById('header')){
	   		$("#header").html('').load("header.html",()=>{
			$("#loginModal").modal('hide');
			
			if (localStorage.getItem("webauthnid")){
				
				document.getElementById('divwebauthn').style.display="block";
			} else {
				document.getElementById('divwebauthn').style.display="none";
			}

			});
		}
	   	// if((window.location.href).includes('index.html'))
	   		// window.location.href = `${window.location.href.replace('index.html','dashboard.html').split('?')[0]}?tk=${data['token']}&refresh=`;
		
		
}  
   
}

function shownotifications(){
	
	$.ajax({
		url: 'controllers/notificationController.php',
		cache: false,
		dataType: 'json',
		type: 'POST',
		data: { 
			trans: "get_user_notifications", 
			user_id: userid
		},
		success: function(result) {	
			let items = "";
			const urlSearch = new URL(window.location.href);
			const token = urlSearch.searchParams.get('tk');
			const urlParams = `?tk=${token}&refresh=`;

			if(result['unread'] > 0)
			{
				$('#noti-indicator').html(`
					<div class="indicator">
						<div class="noti-count">${result['unread']}</div>
					</div>
				`);
			}
			else
			{
				$('#noti-indicator').html('');
			}
			if(result['messages'].length > 0)
			{
				for(let i = 0; i < result['messages'].length; i++)
				{
					var message = result['messages'][i]['msg'];
					if(message.length > 7) message = message.substring(0,7) + '...';
					if(result['messages'][i]['readz'] =git= '0000-00-00 00:00:00'){
						items += `<a href="#" onclick="window.open('messagesView.html${urlParams}&message=${result['messages'][i]['notification_id']}','_self')" class="text-${(result['messages'][i]['readz'] = '0000-00-00 00:00:00') ? 'dark' : 'secondary'}">
									<b>${result['messages'][i]['title']}</b>
									<small>${message}</small>
									<small style="font-size:10px"><br/>${result['messages'][i]['created_at']}</small>
								</a>`;
					}
					else{
						items += `<a href="#" onclick="window.open('messagesView.html${urlParams}&message=${result['messages'][i]['notification_id']}','_self')" class="text-${(result['messages'][i]['readz'] = '0000-00-00 00:00:00') ? 'dark' : 'secondary'}">
									<span class="text-secondary"><b>${result['messages'][i]['title']}</b>
									<small>${message}</small>
									<small style="font-size:10px"><br/>${result['messages'][i]['created_at']}</small></span>
								</a>`;
					}
				}

				if(items != ""){
					items += `<a href="#" onclick="window.open('messagesView.html${urlParams}','_self')" class="btn-seeall text-center">See All Messages</a>`;
					$('#notiContent').html(items);
				}
				
			}
			else{
				$('#notiContent').html(`
					<a>No new notifications</a>
					<a href="#" onclick="window.open('messagesView.html${urlParams}','_self')" class="btn-seeall text-center">See All Messages</a>
				`);
			}
		},
		error: function(err){
			console.log(err);
		}
	});
}

function verifytoken(){
	if(!checking){
		const funcUrlSearch = new URL(window.location.href);
		const key = funcUrlSearch.searchParams.get('tk');

		checking = true;
		if(key != undefined){
			$.ajax({
				url: 'controllers/authenticateController.php',
				cache: false,
				dataType: 'json',
				type: 'POST',
				data: { 
					trans: "verifytoken", 
					tk: key
				},
				success: function(result) {	
					if(!result['success'])
						window.open('index.html', '_self');
				},
				error: function(err){
					console.log('Error: ');
					console.log(err);
				},
				complete: ()=>{
					checking = false;
					setTimeout(function(){verifytoken();}, 60000);
				}
			});
		}
	}
}

function showmenu(userid, token){
	$.get("controllers/usersController.php", {trans:"priviledges",userid: userid,"tk":token}, function(data) {
		var defaultPage = showmenu2(data); 
		var pg = window.location.pathname.split("/").pop();
		if((pg == 'index.html' || pg == '') && !defaultPage.includes('Dashboard')) {
			menudetails(defaultPage[0],token);
		}
	},'json');
}
function showmenu2(data){
	var userid = data['userid'];
	token = data['token'];
	menu = data['menu'];
	useraccess = data['access'];
	var content = '<div class="menutitle">CPAMS 2.0';
		content += '<a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>';
		content += '</div><div class="menulistcontianer" style="width: 100%;height: auto;padding-top: 50px;">';
		for(var key in menu) {
			var data = menu[key];
			let icon;
					if (data['icon']){
						icon = data['icon'];
					} else {
						icon = 'fas fa-table';
					}
			if (data['url']){
				
				if (checkaccess(key, useraccess)=='checked'){
					
				//'checked'){
					var url1 = data['url'];
					var n = url1.indexOf("?");
					var xurl ='';
					if (n== -1) {
						xurl = url1+'?tk='+token+'&refresh=';
					} else {xurl = url1+'&tk='+token+'&refresh=';}
					
					content +='<a href="'+xurl+'"><span class="'+icon+'" style="padding-right:10px;"></span> '+key+'</a>';
					content += '<div class="menudivider"></div>';
				}
			}
			else {
				var ckey ="'"+key+"','"+token+"'";
				content += '<a href="#" onclick="menudetails('+ckey+')"><span class="'+icon+'" style="padding-right:5px;"></span> '+key+'</a>';
				content += '<div class="menudivider"></div></div>';
			}
		}
	document.getElementById('mySidenav').innerHTML = content;
	$('.menuicon').show();
	//document.getElementById('content').style.display = 'block';
	var defaultPage = {
	  'Government': 'Encoding', 
	  'Pending': 'Encoding', 
	  'Private': 'Encoding',
	  'Verify Patient': 'Assessment',
	  'Dashboard': 'Dashboard'
	};
	var arr = [];
	for(x=0; x < useraccess.length; x++) {
	  if (defaultPage[useraccess[x].name] && !arr.includes(defaultPage[useraccess[x].name]))
		arr.push(defaultPage[useraccess[x].name]);
	}
	return arr;
}
function SignIn(){
	// **************************************************** 10/26/2022 [edited - start]	
	$('.menuicon').hide();
	$(".logout").hide();
	// $('#loginModal').modal('show');
	$("#header").html('').load("header.html",()=>{
		$("#loginModal").modal('show');
		
		if (localStorage.getItem("webauthnid")){
				
				document.getElementById('divwebauthn').style.display="block";
			} else {
				
				document.getElementById('divwebauthn').style.display="none";
			}
	});
	// **************************************************** 10/26/2022 [edited - end]	
}

function displaypassword(e){
	if($('#showpassword').is(":checked"))
		$('#passwordlog').attr('type','text');
	else
		$('#passwordlog').attr('type','password');
}

function verify(){
	document.getElementById('loginverifybutton').innerHTML = 'Verifying Account...';
	document.getElementById('loginverifybutton').disabled = true;
	
	var email = document.getElementById('emaillog').value;
	var password = document.getElementById('passwordlog').value;
	// console.log(email + ' | ' + password);
	$.get("controllers/loginController.php",{email:email,password:password}, function(data){ 
		processverification(data);		
	},"json")
	.fail(function(err) {
		console.log(err);
		offlineerror();
	})
	.always(function () {	
		document.getElementById('loginverifybutton').innerHTML = 'Log In';
		document.getElementById('loginverifybutton').disabled = false;
	});
	
}
function processverification(data){
	if (data['userid']==-1){
		bootbox.alert('Invalid Account');
		
	} else {
		tk = data['token'];
		token = data['token'];
		$.get("controllers/checkstatus.php",{"token":data['token']}, function(data, xstatus){ 
			signbutton(data, xstatus);
		},"json").fail(function() {
			offlineerror();
		});
		
		// Added by: Teddy C. 11/28/2022 11:22.
		// Fixed token error on login.
    	const urlParams = window.location.search;
		const urlLocation = window.location.href;
		window.location.href = `${urlLocation.replace(urlParams,'')}?tk=${token}&refresh=`;
		// End

		// $('#loginModal').modal('hide');
		$("#header").html('').load("header.html",()=>{
			$(".modal").modal('hide');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
		});
	}
}
function home(){
	let tk = qs['tk'];
	if (tk){
		location.href = "index.html?tk="+tk+"&refresh=";
	} else {
		location.href = "index.html";
	}
}
function SignOut(){
	$("#lModal").modal("show");
}	
function logout(){
	$("#lModal").modal("hide");
	let tk='';
	if(qs['tk']){ tk = qs['tk'];}
	localStorage.removeItem("tk");
	$.get("controllers/loginController.php",{"email":'', "tk":tk}, function(data) {window.open("index.html",'_self');},"json");
}
function lnot(){
	$("#lModal").modal("hide");
}
function openNav() {
    document.getElementById("mySidenav").style.width = "250px";
}

/* Set the width of the side navigation to 0 */
function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
}
function menudetails(key,token){
var content = menu[key];                                                   
var detail = '<div class="pagecontainer"><h3 class="text-center">'+key+'</h3><div class="row justify-content-md-center">';
// var detail = '<div class="col-12"><br/><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closePanel()"><span aria-hidden="true">&times;</span></button><h3 class="text-center">'+key+'</h3></div><div class="row row-cols-1 row-cols-md-2">';
//"<div class='row justify-content-center'>";
	
	for(var key in content){
		var dd = content[key];
		if (key != "icon"){
			if (checkaccess(key, useraccess)=='checked'){
			var details = content[key];
				if (details['display']){
					
				//var image;
				//if (details['image']){
				//	image = details['image'];
				//} else {
				//	image = 'buttons.jpg';
				//}
					var icon;
					if (details['icon']){
						icon = details['icon'];
					} else {
						icon = 'fas fa-table';
					}
				
					var url1 = details['url'];
					var n = url1.indexOf("?");
					var xurl ='';
					if (n== -1) {
						xurl = url1+'?tk='+token+'&refresh=';
					} else {xurl = url1+'&tk='+token+'&refresh=';}
					detail += '<div class="col col-sm-6" style="margin-bottom: 5px;"><div class="card"><a href="'+xurl+ '"><div class="card-body text-center"><i class="'+icon+'"></i><br><strong>'+key+'</strong></div></a></div></div>';
					// detail += '<div class="col-12 col-md-3 mb-4"><div class="card bg-light border-secondary"><div class="card-body text-center"><a href="'+xurl+ '"><i class="'+icon+'"></i><br><strong>'+key+'</strong></a></div></div></div>';
  
				
				
				//detail += '<div class="col-md-3 text-center"><br><a href="'+xurl+'" class="btn btn-light"> <img src="'+image+'"> '+key+'</a><br></div>';
				//'<div class="col-3"><a href="'+xurl+'"><div class="center-block text-center"><img src="'+image+'" class="img-responsive center-block"><span><strong>'+key+'</strong></span></div></a></div>';
				}	
			}
		}
		
	}
	detail += "</div>";
	document.getElementById('content').style.display = 'none';
	document.getElementById('menuPanel').style.display = 'block';
	document.getElementById('menuPanel').innerHTML = '<div class="container">'+detail+'</div>';
	
	closeNav();
}
function configuredate(){
var d = new Date(); 
	cmonth = d.getMonth()+1;
	cmonth = '' + cmonth;
	if (cmonth.trim().length==1){
		cmonth = '0'+cmonth;
	}
	cday = ''+d.getDate();
	if (cday.trim().length==1){
		cday = '0'+cday;
	}
	dstring = d.getFullYear() + "-" + cmonth + "-" + cday;
	return dstring;
}
function configuredate2(xdate){
var d = new Date(xdate); 
	cmonth = d.getMonth()+1;
	cmonth = '' + cmonth;
	if (cmonth.trim().length==1){
		cmonth = '0'+cmonth;
	}
	cday = ''+d.getDate();
	if (cday.trim().length==1){
		cday = '0'+cday;
	}
	dstring = d.getFullYear() + "-" + cmonth + "-" + cday;
	return dstring;
}
function get_query(){
	
    var url = location.href;
    var qs = url.substring(url.indexOf('?') + 1).split('&');
    for(var i = 0, result = {}; i < qs.length; i++){
        qs[i] = qs[i].split('=');
        result[qs[i][0]] = decodeURI(qs[i][1]);
    }
	 return result;
}
function startTime() {
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
	var mid = "AM";
    m = checkTime(m);
    s = checkTime(s);
	
	if (h>12) {
		mid ="PM";
		h = h - 12;
	}
	if (h<1){
		h="12";
	}
	if (h<10){
			h = "0" + h;
	}
	let eltime = document.getElementById('time');
	if (eltime){
		let xtime = h + ":" + m + ":" + s + " " + mid;
		
		eltime.innerHTML = xtime;
	}
    var t = setTimeout(startTime, 5000);
	
}
function checkTime(i) {
    if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
    return i;
}
function checkaccess(details, access){

	for (x = 0; x < access.length; x++){ 
		var module = access[x];
		if (details==module['name']) {
			return 'checked';
		}
	}
	return '';
}

function getmoduleaccess(modulename){
	for (x = 0; x < useraccess.length; x++){ 
		var module = useraccess[x];
		if (modulename==module['name']) {
			return module;
		}
	}
	return [];
}


function confirmModal(smessage){
	document.getElementById('cMessage').innerHTML=smessage;
	$("#cModal").modal("show");
}
function initload(){
var otk = document.getElementsByName('tk');
for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
	{
		otk[x].value = qs['tk'];
	}
}
function closePanel(){
	document.getElementById("menuPanel").style.display="none";
	document.getElementById("content").style.display="block";
}

function addCommas(nStr){
 nStr += '';
 var x = nStr.split('.');
 var x1 = x[0];
 var x2 = x.length > 1 ? '.' + x[1] : '';
 var rgx = /(\d+)(\d{3})/;
 while (rgx.test(x1)) {
  x1 = x1.replace(rgx, '$1' + ',' + '$2');
 }
 return x1 + x2;
}

function readURL(input, id) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
	  var sfilename = input.value;
	  var ext = sfilename.substr(sfilename.lastIndexOf('.') + 1);
	  if (ext.toUpperCase() == 'PDF'){$(id).attr('src', 'attachments/pdficon.jpg');} else {
      $(id)
        .attr('src', e.target.result);}
        
    };
	
    reader.readAsDataURL(input.files[0]);
  }
}
function gentkform(tk){
	$.get("controllers/webauthnController.php",{"trans":"gentk","tk":tk},function(data){$('input[name="tkform"]').val(data);},'json');
	return true;
}
function arrayBufferToBase64(buffer) {
            let binary = '';
            let bytes = new Uint8Array(buffer);
            let len = bytes.byteLength;
            for (let i = 0; i < len; i++) {
                binary += String.fromCharCode( bytes[ i ] );
            }
            return btoa(binary);
}
function arrayBufferToStr(buf) {
    return String.fromCharCode.apply(null, new Uint8Array(buf));
}

//  
function ShowNotification() {
  document.getElementById("notiContent").classList.toggle("show");
}
// 