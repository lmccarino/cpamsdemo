
function startload(){
$("#raf").load("raf.html?v=1", initraf1());

var otk = document.getElementsByName('tk');
for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
{
    otk[x].value = qs['tk'];
}

	startattachment();
	
}
function initraf1(){
	

	 
			setTimeout(initraf, 1000);
		
}
function initraf(){
	
						addDependent();		
						getbrgys();
						getproviders();
						getsworkers();
						let arateCode =[];
						let xelements = document.getElementById('rafForm').elements;
						xelements.namedItem('trans').value = "UPDATE";
						//xelements.namedItem('idassistdetails').value = -1;
						xelements.namedItem('dateReceive').value = configuredate();
						//
						gettemplates();
						xelements.namedItem('rafNum').focus();
						
						
						
}				

function getproviders(){
			$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getprovidersAll"}, function(data, xstatus){ fillproviders(data); 
			setTimeout(getrafdetails, 1000);},"json").fail(function() {
			offlineerror();
			});	
		}
function getrafdetails(){
	let xidassistdetails = qs['idassistdetails'];
	document.getElementById('btnNew').style.display = "none";
	$.get("controllers/pendingController.php",{"tk":qs['tk'],"trans":"getraf","idassistdetails":xidassistdetails}, function(data, xstatus){ fillraf(data);},"json").fail(function() {
			offlineerror();
			});
	$.get("controllers/pendingController.php",{"tk":qs['tk'],"trans":"getintake","idassistdetails":xidassistdetails}, function(data, xstatus){ fillraf2(data);},"json").fail(function() {
			offlineerror();
			});
}
function fillraf(data){
	let els = document.getElementById('rafForm').elements;
	let d = data['assistdetail'];
	
	for (var key in d) {
		
		if (els.namedItem(key)){
			els.namedItem(key).value = d[key];
		}
		
	
	}
	els.namedItem('dateReceive').value = configuredate2(d['dateReceive']);
	
	let el = els.namedItem('idpatient');
	loadpatient(el);
	
	document.getElementById('controlbuttons').style.display = "inline";
	els.namedItem('approvebutton').style.display ="inline";
	// els.namedItem('overridebutton').style.display ="inline";
	els.namedItem('myfields').disabled = false;
	let el2 = els.namedItem('provCode');
	getassistcode(el2);
	
}
function fillraf2(data){
	let els = document.getElementById('rafForm').elements;
	let d = data['intake'];
	removeDependent2();
	for (var key in d) {
		
		if (els.namedItem(key)){
			els.namedItem(key).value = d[key];
		}
		if (key =="details"){
			
			let dep = d['details'];
			addDependent2(dep);
		}
		
	}
	setTimeout(checkremarks(d), 1000);
	
	
	
}
function checkremarks(d){
	let els = document.getElementById('rafForm').elements;
	if (!els.namedItem('remarks').value){
		els.namedItem('remarks').value = d['remarks'];
	}
	document.getElementsByName('provCode')[0].readOnly = true;
	document.getElementsByName('idassistsched')[0].readOnly = true;
	document.getElementsByName('billAmount')[0].readOnly  = true;
	
}
function addDependent2(dep){
	
	let jdeps = JSON.parse(dep);
	for (i=0; i< jdeps.length; i++){
		let d = jdeps[i];
		if (d.depName) {
			var nform = $('#divDependent').clone(true);
			nform.appendTo( '#divDependents' );
			nform.show();
			
			let x = document.getElementsByName('depName[]');
			let ncount = x.length;
			let ii = ncount - 1;
			document.getElementsByName('depName[]')[ii].value = d.depName;
			document.getElementsByName('depRelation[]')[ii].value = d.depRelation;
			document.getElementsByName('depAge[]')[ii].value = d.depAge;
			
			
		}
	}

}
function removeDependent2(){
			let divs = document.getElementsByName('divDependent');	
			for (i = 1;i < divs.length; i++){
				divs[i].parentNode.removeChild(divs[i]);
			}
}
function newRaf(){
			let tk = qs['tk'];
			window.open("pendingView.html?tk="+tk+"&refresh=", "_self");
		}