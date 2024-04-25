let atemplates = [];
let rafNumValid = false;
let providers;
let assistcodes;
function gettemplates(){
	$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"gettemplates"}, function(data){ filltemplates(data);},"json").fail(function() {
	offlineerror();
	});	
}
function filltemplates(data){
	atemplates = data['templates'];
}
function addMedicine(){
	$("#divMedicine").clone(false).removeAttr("id").addClass('divMedicine').appendTo($("#divMedicines")).show();
}
function removeMedicine(el){
	let ppDiv = el.parentNode.parentNode;
	$.get("controllers/rafViewController.php", {
		'pcom_detailsid': ppDiv.children[0].value,
		'trans': 'removemed',
		'tk': qs['tk']
	}, function(data){ 
		ppDiv.parentNode.removeChild(ppDiv);
		computeMed(); 
	},"json")
	.fail(function() { offlineerror(); });
}

function removeDependent(el){
	let pDiv = el.parentNode;
	let ppDiv = pDiv.parentNode;
	ppDiv.parentNode.removeChild(ppDiv);
}
function getbrgys(){
	$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getbrgys"}, function(data, xstatus){ fillbrgys(data);},"json").fail(function() {
	offlineerror();
	});	
}
function getsworkers(){
	$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getsworkers"}, function(data, xstatus){ fillsworkers(data);},"json").fail(function() {
	offlineerror();
	});	
}
function getassistcode(el){
	let officecode = el.value;
	el.title = el.options[el.selectedIndex].text;
	$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getassistcode","officecode":officecode}, function(data, xstatus){ fillassistcode(data); },"json").fail(function() {
	offlineerror();
	});
}
function fillbrgys(data){
	let  x = document.getElementById('rafForm');
	let gl = x.elements.namedItem('brgyCode');
	
	let  row = data['brgys'];
		gl.innerHTML = "";
		for (a = 0;a < row.length; a++){
			var option = document.createElement("option");
					option.text = row[a]['brgyName'] +', '+ row[a]['distName'];
					option.value = row[a]['brgyCode'];
					gl.add(option); 
		
		}	
	gl.selectedIndex = 0;				
}
function fillsworkers(data){
	let  x = document.getElementById('rafForm');
	let gl = x.elements.namedItem('sworker');
	
	let  row = data['sworkers'];
		gl.innerHTML = "";
		for (a = 0;a < row.length; a++){
			var option = document.createElement("option");
					option.text = row[a]['fullname'];
					option.value = row[a]['fullname'];
					gl.add(option); 
		
		}	
	gl.selectedIndex = 0;
}
function fillproviders(cat, prov, idassistsched) {
	let  x = document.getElementById('rafForm');
	let gl = x.elements.namedItem('provCode');
	var selected = 0;

	let  row = providers[cat];
	gl.innerHTML = "";
	for (a = 0;a < row.length; a++) {
		var option = document.createElement("option");
			option.text = row[a]['officename'] +', '+ row[a]['location'];
			option.value = row[a]['officecode'];
			gl.add(option);
		if (prov == row[a]['officecode']) {
			selected = a;
		}
	}	
	gl.selectedIndex = selected;
	gl.title = gl.options[gl.selectedIndex].text;
	
	//getassistcode(gl);
	let officecode = gl.value;
	let erateCode = x.elements.namedItem('rateCode');
	gl = x.elements.namedItem('idassistsched');
	gl.innerHTML = "";
	
	$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getassistcode","officecode":officecode}, function(data, xstatus) {
		arateCode = row = data['assistCode'];
		for (a = 0;a < row.length; a++){
			var option = document.createElement("option");
					option.text = row[a]['assistCode']+", "+row[a]['assistDesc'];
					option.value = row[a]['idassistsched'];
					gl.add(option);
			if (idassistsched == row[a]['idassistsched']) selected = a;
		}
		gl.selectedIndex = selected;
		erateCode.value = row[selected]['rateCode'];
		gl.title = gl.options[gl.selectedIndex].text;
		if (gl.title.includes('MEDICINE') && $('.meds').is(":hidden")) {
			$('.meds').show();
		}
	},"json").fail(function() { offlineerror(); });
}
function fillmeds(data){
	let  x = document.getElementById('rafForm');
	let gl = x.elements.namedItem('medProv');
	
	let  row = data['providers'];
		gl.innerHTML = "";
		for (a = 0;a < row.length; a++){
			var option = document.createElement("option");
					option.text = row[a]['pharmaname'];
					option.value = row[a]['id'];
					gl.add(option); 
		
		}	
		gl.selectedIndex = 0;				
}
function computeMed(){
	let frm = document.getElementById('rafForm').elements;
	let elm = frm.namedItem('billAmount');
	let xbillAmount = 0;

	for (let i=0; i < frm.medAmount.length; i++) {
		xbillAmount += Number(frm.medAmount[i].value);
	}
	elm.value = xbillAmount;
	computerate2();
}
function fillassistcode(data){
	assistcodes = data;
	let  x = document.getElementById('rafForm');
	let gl = x.elements.namedItem('idassistsched');
	let erateCode = x.elements.namedItem('rateCode');
	
	let  row = data['assistCode'];
	arateCode = data['assistCode'];
		gl.innerHTML = "";
		for (a = 0;a < row.length; a++){
			var option = document.createElement("option");
					option.text = row[a]['assistCode']+", "+row[a]['assistDesc'];
					option.value = row[a]['idassistsched'];
					gl.add(option); 
		
		}	
		gl.selectedIndex = 0;
		erateCode.value = arateCode[0]['rateCode'];
		gl.title = gl.options[gl.selectedIndex].text;
		let billAmount = x.elements.namedItem('billAmount');
		computerate(billAmount);
		assigntemplates();
		
}
function assigntemplates(){
	let  x = document.getElementById('rafForm');
	let sel = x.elements.namedItem('idassistsched');
	let xtext = sel.options[sel.selectedIndex].text;
	let acode = xtext.split(",");
	let xcode = acode[0];
	let prov = x.elements.namedItem('provCode');
	let provName = prov.options[prov.selectedIndex].text;
	let gender = (x.elements.namedItem('benSex').value == 'MALE') ? 'He' : 'She';
	let content = '';
	let repz = {'$GENDER': gender, '$PROVIDER': provName, '$HOSPITAL': provName, '$FUNERAL': provName};
	for (i= 0; atemplates.length; i++){
		if (atemplates[i]['assistcode'] == xcode) {
			content = atemplates[i]['content'];
			for (var k in repz) {
				if (repz.hasOwnProperty(k) && repz[k]) content = content.replace(k, repz[k]);
			}
			x.elements.namedItem('remarks').innerHTML = content;
			break;
		}
	}
	
}
function setrateCode(el){
	let  x = document.getElementById('rafForm');
	let gl = x.elements.namedItem('idassistsched');
	let i = gl.selectedIndex;
	let erateCode = x.elements.namedItem('rateCode');
	erateCode.value = arateCode[i]['rateCode'];
	gl.title = gl.options[gl.selectedIndex].text;
	let billAmount = x.elements.namedItem('billAmount');
	computerate(billAmount);
	assigntemplates();
	medicine(gl.title);
}
function medicine(assistance){
	let  x = document.getElementById('rafForm');
	let billAmount = x.elements.namedItem('billAmount');
		
	if (assistance.startsWith('MEDICINE')) {
		$('.meds').show();
		var medInput = $('.divMedicine').children().find('input');
			if (medInput.length == 0) {
				addMedicine();
			} else if (medInput.length > 0 && Number(medInput[0].value) < 1) {
			  $('.divMedicine').remove();
			  addMedicine();
			}
		billAmount.readOnly = true; //make readOnly
		//billAmount.value = 0;
	} else {
		$('.meds').hide();
		billAmount.readOnly = false;
	}
}
function computerate(el){
	let xform = el.form.elements;
	let xbillAmount = Number(el.value);
	let xrateCode = xform.namedItem('rateCode').value;
	
	$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getrate","billAmount":xbillAmount,"rateCode":xrateCode}, function(data){ 
			let  x = document.getElementById('rafForm').elements;
			x.namedItem('amtApproved').value = data['amtApproved'];
			let cont = 1;
			if (!x.namedItem('rafNum').value){  cont = -1;}
			if (!x.namedItem('benLName').value){  cont = -1;}
			if (!x.namedItem('benFName').value){  cont = -1;}
			if (!x.namedItem('benMName').value){  cont = -1;}
			if (!x.namedItem('benBDate').value){ cont = -1;}
			if (!x.namedItem('requestor').value){  cont = -1;}
			if (!x.namedItem('billAmount').value){ ; cont = -1;}
			if (!x.namedItem('remarks').value){  cont = -1;}
			if (cont > -1){	
				document.getElementById('controlbuttons').style.display = "inline";
			}
		},"json").fail(function() {
			offlineerror();
		});
}
function computerate2(){
	let  els = document.getElementById('rafForm').elements;
	let el = els.namedItem('billAmount');
	let xbillAmount = Number(el.value);
	let xrateCode = els.namedItem('rateCode').value;
	
	$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getrate","billAmount":xbillAmount,"rateCode":xrateCode}, function(data){ 
			let  x = document.getElementById('rafForm').elements;
			x.namedItem('amtApproved').value = data['amtApproved'];
			let cont = 1;
			if (!x.namedItem('rafNum').value){  cont = -1;}
			if (!x.namedItem('benLName').value){  cont = -1;}
			if (!x.namedItem('benFName').value){  cont = -1;}
			if (!x.namedItem('benBDate').value){ cont = -1;}
			if (!x.namedItem('requestor').value){  cont = -1;}
			if (!x.namedItem('billAmount').value){ ; cont = -1;}
			if (!x.namedItem('remarks').value){  cont = -1;}
			
			if (cont > -1){	
			    document.getElementById('controlbuttons').style.display = "inline";
				x.namedItem('savebutton').style.display ="inline";
			} else {
				
				document.getElementById('controlbuttons').style.display = "none";
			}
		},"json").fail(function() {
			offlineerror();
		});
}
function filltotalamount(d){
	let xid = "idtotal"+d['idpatient'];
	let xtotal = Number(d['total']);
	document.getElementById('idtotal').innerHTML = "<strong>"+xtotal.toLocaleString("en-US", {style:"currency", currency:"Php"})+"</strong>";

}
function savethis(xform){
let cont = 1;
let els = xform.elements;
	let assistance = els.namedItem('idassistsched').options[els.namedItem('idassistsched').selectedIndex].text;
	if (!els.namedItem('benLName').value){ toastr.error('Patient Last name is empty'); cont = -1;}
	if (!els.namedItem('benFName').value){ toastr.error('Patient First name is empty'); cont = -1;}
	if (!els.namedItem('benBDate').value){ toastr.error('Patient Birthday is empty'); cont = -1;}
	if (!els.namedItem('brgyCode').value){ toastr.error('Brgy is empty'); cont = -1;}
	if (!els.namedItem('requestor').value){ toastr.error('Requestor is empty'); cont = -1;}
	if (!els.namedItem('reqAddr').value){ toastr.error('Requestor Address is empty'); cont = -1;}
	if (!els.namedItem('relation').value){ toastr.error('Requestor Relation is empty'); cont = -1;}
	if (!els.namedItem('billAmount').value){ toastr.error('Bill Amount is empty'); cont = -1;}
	if (!els.namedItem('remarks').value){ toastr.error('Interview remarks is empty'); cont = -1;}
	if (Number(els.namedItem('amtApproved').value) > Number(els.namedItem('amtApprovedOrig').value))
	{ toastr.error('Amount is larger than previously approved!'); cont = -1; }
	if (cont == -1){
		return false;
	}
	let tk = '';
	if (qs['tk']) {tk = qs['tk'];} else {tk = localStorage.getItem('tk');}
	if (! els.namedItem('tk').value) { els.namedItem('tk').value = tk;}
	els.namedItem('assistCode').value = els.namedItem('idassistsched').options[els.namedItem('idassistsched').selectedIndex].text;
	var data = new FormData(xform);
	$.ajax({
		url: 'controllers/rafViewController.php',
		data: data,
		cache: false,
		contentType: false,
		processData: false,
		dataType: 'json',
		type: 'POST',
		success: function(data) {
			
			if (data["idassistdetails"] > -1) {
				
				displayResult(data);
				document.getElementById('controlbuttons').style.display ="none";
				document.getElementById('reportbuttons').style.display ="block";
				toastr.success('Changes saved');
			} else {
				toastr.error('Error saving..');
			}
		},	
		error: function (d){ offlineerror();}
	});
}
function intakeform(){
	var id = document.getElementById('idassistdetails').value;
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/print/printIntake.php?idassistdetails='+id);	
}
function gletter(){
	var id = document.getElementById('idassistdetails').value;
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/print/printGL.php?id='+id);	
}
function celigibility(){
	var id = document.getElementById('idassistdetails').value;
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/print/printEligibility.php?idassistdetails='+id);
}
function cindigency(){
	var id = document.getElementById('idassistdetails').value;
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/print/printIndigency.php?idassistdetails='+id);	
}

function copyaddressfunc(){
	if($('#copyaddress').is(":checked")){
		let address = $("input[name=benAddrSt]").val();
		let brgy = $("select[name=brgyCode]").find(":selected").text();
		$("input[name=reqAddr]").val(address + ', ' + brgy);
	}
	else{
		$("input[name=reqAddr]").val('');
	}
}

function copypatientfunc(){
	if($('#copypatient').is(":checked")){
		$("input[name=requestor]").val(`${$("input[name=benLName]").val()}, ${$("input[name=benFName]").val()} ${$("input[name=benMName]").val()} ${$("input[name=suffix]").val()}`);
		$('#copyaddress').trigger('click');
	}
	else{
		$("input[name=requestor]").val('');
	}
}
/* end code duplication!! please */



function startload(tk){
  var otk = document.getElementsByName('tk');
  for(var x=0; x < otk.length; x++)   // comparison should be "<" not "<="
    otk[x].value = qs['tk'];

  getbrgys();
  getsworkers();
  getproviders();
  let arateCode =[];
  let xelements = document.getElementById('rafForm').elements;
  xelements.namedItem('trans').value = "UPDATE";
  gettemplates();
  $.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getmeds"}, function(data, xstatus){ fillmeds(data);},"json").fail(function() {
	offlineerror();
  });
}

function getproviders() {
	$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getprovidersAllv2"}, function(data, xstatus){ providers = data; },"json").fail(function() {
		offlineerror();
	});
}

function displayResult(data) {
	var rafFrm = document.getElementById('rafForm').elements;
	
	for (var k in data)
	  if (Object.prototype.hasOwnProperty.call(data, k) && rafFrm.namedItem(k)) {
		rafFrm.namedItem(k).value = data[k];
	}
	rafFrm.namedItem('amtApprovedOrig').value = data['amtApproved'];
	
	$('#divDependents').children().not(':first').remove();
	var intakeDetails = JSON.parse( data.details );
	for (var i = 0; i < intakeDetails.length; i++) {
		var nform = $('#divDependent').clone(true);
		var tmpEl = nform.children().find('input');
		tmpEl[0].value = intakeDetails[i].depName;
		tmpEl[1].value = intakeDetails[i].depRelation;
		tmpEl[2].value = intakeDetails[i].depAge;
		
		nform.appendTo( '#divDependents' ).show();
	}

	$('#divMedicines').children().not(':first').remove();
	$('.meds').hide();
	var pcomDetails = data.pcom_details;
	for (var i = 0; i < pcomDetails.length; i++) {
		var nform = $('#divMedicine').clone(false).removeAttr("id").addClass('divMedicine');
		var tmpEl = nform.children().find('input,select');
		nform.children()[0].value = pcomDetails[i].pcom_detailsid;
		tmpEl[0].value = pcomDetails[i].pcom_pharmacyid;
		tmpEl[1].value = pcomDetails[i].amount;
		nform.appendTo( '#divMedicines' ).show();
	}
	if (pcomDetails.length > 0) $('.meds').show();
	
	fillproviders(data.provCat, data.provCode, data.idassistsched);
	document.getElementById('controlbuttons').style.display ="none";
	document.getElementById('reportbuttons').style.display ="none";
}

function searchthis(frm) {
	let els = frm.elements;
	els.namedItem('tk').value = qs['tk'];

	if (els.namedItem('raf').value.length != 8) {
		toastr.error('Invalid RAF format!');
		return;
	}
	
	var data = $(frm).serialize();
	$.ajax({
		url: 'controllers/rafViewController.php',
		data: data,
		dataType: "json",
		type: 'POST',
		success: function(data) {

			if (data["idassistdetails"] > -1) {
				
				displayResult(data);
				toastr.success('RAF Found');
			} else {
				toastr.error('No RAF found!');
			}
		},
		fail: function() {
			offlineerror();
		}
	});
}