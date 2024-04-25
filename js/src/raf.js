let atemplates = [];

$(document).ready(function(){
	
});

function validatePatient(){
	let  x = document.getElementById('rafForm').elements;

	if(!(x.namedItem('idpatient').value > 0)){
		let filled = true;

		if (!x.namedItem('benLName').value) filled = false;
		if (!x.namedItem('benFName').value) filled = false;
		if (!x.namedItem('benBDate').value) filled = false;

		if(filled){
			$.get("controllers/patientController.php", 
				{
					"tk"		: qs['tk'],
					"trans"		: "VALIDATEPATIENT",
					"lastname"  : x.namedItem('benLName').value,
					"firstname" : x.namedItem('benFName').value,
					"middlename": x.namedItem('benMName').value,
					"birthday"	: x.namedItem('benBDate').value,
				}, 
			function(result){ 
				let id = -1;
				if (result?.data?.idpatient > 0) {
					id = result?.data?.idpatient;
					Swal.fire({
						icon: 'warning',
						title: "Patient Exists",
						html: `
							<table style="width: 100%; font-size: 11.5px">
								<tr>
									<td class="text-muted fw-bold" colspan="2" style="font-size: 16px; padding-bottom: 5px">Patient Details</td>
								</tr>
								<tr>
									<td class="text-muted align-top" width="75px">ID <span class="float-end">:</span></td>
									<td class="fw-bold text-dark ps-1">
										${result.data.idpatient}
									</td>
								</tr>
								<tr>
									<td class="text-muted align-top" width="75px">Fullname <span class="float-end">:</span></td>
									<td class="fw-bold text-dark ps-1">
										${result.data.benLName} ${result.data.suffix}, ${result.data.benFName} ${result.data.benMName}
									</td>
								</tr>
								<tr>
									<td class="text-muted align-top">Birthday <span class="float-end">:</span></td>
									<td class="fw-bold text-dark ps-1">
										${result.data.benBDate}
									</td>
								</tr>
								<tr>
									<td class="text-muted align-top">Address <span class="float-end">:</span></td>
									<td class="fw-bold text-dark ps-1">
										${result.data.benAddrSt.trim()}, ${result.data.brgyName.trim()}, ${result.data.distName.trim()}
									</td>
								</tr>
							</table>
						`,
						allowOutsideClick: false,
						showCancelButton:  true,
						confirmButtonText: "Use Information",
						cancelButtonText:  "New Patient",
						width: 300,
					}).then((result) => {
						if (result.isConfirmed) {
							x.namedItem('idpatient').value = id;
							loadpatient();
						} else {
							newpatient();
						}
					});
				}
			},"json")
			.fail(function(err) {
				console.log(err);
				toastr.error(err);
				Swal.close();
			}); 
		}
	}

}

function gettemplates(){
	$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"gettemplates"}, function(data){ filltemplates(data);},"json").fail(function() {
		offlineerror();
	});	
}

function filltemplates(data){
	atemplates = data['templates'];
}
		function addDependent(){
			var nform = $('#divDependent').clone(true);
			nform.appendTo( '#divDependents' );
			nform.show();

		}
		function addMedicine(){
			$("#divMedicine").clone(false).removeAttr("id").addClass('divMedicine').appendTo($("#divMedicines")).show();
		}
		function removeMedicine(el){
			let pDiv = el.parentNode;
			let ppDiv = pDiv.parentNode;
			
			if (ppDiv.children[0].value > 0) { // ajax when there's a pcom_id
				$.get("controllers/rafViewController.php", {
					'pcom_detailsid': ppDiv.children[0].value,
					'trans': 'removemed',
					'tk': qs['tk']
				}, function(data){ 
					ppDiv.parentNode.removeChild(ppDiv);
					computeMed(); 
				},"json")
				.fail(function() { offlineerror(); });
			} else {
				ppDiv.parentNode.removeChild(ppDiv);
				computeMed(); 
			}
			
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
			$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getassistcode","officecode":officecode}, function(data, xstatus){ fillassistcode(data);},"json").fail(function() {
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
			var selected = 0;
			
			let  row = data['sworkers'];
				gl.innerHTML = "";
				for (a = 0;a < row.length; a++){
					var option = document.createElement("option");
							option.text = row[a]['fullname'];
							option.value = row[a]['fullname'];
							gl.add(option); 
					if (row[a]['userid'] == userid)
						selected = a;
				
				}	
			gl.selectedIndex = selected;
		}
		function fillproviders(data){
			let  x = document.getElementById('rafForm');
			let gl = x.elements.namedItem('provCode');
			
			let  row = data['providers'];
				gl.innerHTML = "";
				for (a = 0;a < row.length; a++){
					var option = document.createElement("option");
							option.text = row[a]['officename'] +', '+ row[a]['location'];
							option.value = row[a]['officecode'];
							gl.add(option); 
				
				}	
				gl.selectedIndex = 0;
				
				getassistcode(gl);
				
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
			computerate2(true);
		}
		function fillassistcode(data){
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
					// if (!x.namedItem('benMName').value){  cont = -1;}
					if (!x.namedItem('benBDate').value){ cont = -1;}
					if (!x.namedItem('requestor').value){  cont = -1;}
					if (!x.namedItem('billAmount').value){ ; cont = -1;}
					if (!x.namedItem('remarks').value){  cont = -1;}
					if (cont > -1){	document.getElementById('controlbuttons').style.display = "inline";
						x.namedItem('approvebutton').style.display ="none";
						// x.namedItem('overridebutton').style.display ="none";
					}
				},"json").fail(function() {
					offlineerror();
				});
		}
		
function computerate2(calculate = false){
	let  els = document.getElementById('rafForm').elements;
	let el = els.namedItem('billAmount');
	let xbillAmount = Number(el.value);
	let xrateCode = els.namedItem('rateCode').value;
	
	if(calculate){
		$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getrate","billAmount":xbillAmount,"rateCode":xrateCode}, function(data){ 
			els.namedItem('amtApproved').value = data['amtApproved'];
		},"json").fail(function() {
			offlineerror();
		});
	}
	
	let cont = 1;
	if (!els.namedItem('rafNum').value){  cont = -1;}
	if (!els.namedItem('benLName').value){  cont = -1;}
	if (!els.namedItem('benFName').value){  cont = -1;}
	// if (!els.namedItem('benMName').value){  cont = -1;}
	if (!els.namedItem('requestor').value){  cont = -1;}
	if (!els.namedItem('billAmount').value){ ; cont = -1;}
	if (!els.namedItem('remarks').value){  cont = -1;}
	
	if (cont > -1){	
		document.getElementById('controlbuttons').style.display = "inline";
		els.namedItem('approvebutton').style.display ="none";
		// els.namedItem('overridebutton').style.display ="none";
		els.namedItem('savebutton').style.display ="inline";
	} else {
		document.getElementById('controlbuttons').style.display = "none";
	}
}

		function loadpatient(){
			let  els = document.getElementById('rafForm').elements;
			let xidpatient = els.namedItem('idpatient').value;

			clearpatient();

			if (Number(xidpatient)> 0){
				$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"loadpatient","idpatient":xidpatient}, function(d){ 
					if(d['error']){
						toastr.error('No Record Found');
						return false;
					}

					let data = d['patient'];
					let  els = document.getElementById('rafForm').elements;

					if(data['deceased'] == 1){
						toastr.warning('Patient already availed Funeral Assistance. Please check');
						return false;
					}
					els.namedItem('idpatient').value = xidpatient;
					els.namedItem('benLName').value = data['benLName'];
					els.namedItem('benFName').value = data['benFName'];
					els.namedItem('benMName').value = data['benMName'];
					els.namedItem('suffix').value = data['suffix'];
					els.namedItem('benAddrSt').value = data['benAddrSt'];
					els.namedItem('brgyCode').value = data['brgyCode'];
					els.namedItem('benSex').value = data['benSex'];
					els.namedItem('benPHealth').value = data['benPHealth'];
					els.namedItem('benBDate').value = data['benBDate'];
					els.namedItem('philsysid').value = data['philsysid'];
					els.namedItem('benContact').value = data['benContact'];
					els.namedItem('effectivitydate').value = data['effectivitydate'];
					els.namedItem('typeClient').value = 'WALK-IN';
					
					// Author: Teddy C. 09/19/2023 20:19.
					els.namedItem('benLName').readOnly = true;
					els.namedItem('benFName').readOnly = true;
					els.namedItem('benMName').readOnly = true;
					els.namedItem('suffix').readOnly = true;
					els.namedItem('benAddrSt').readOnly = false;
					els.namedItem('brgyCode').readOnly = false;
					els.namedItem('benSex').readOnly = true;
					els.namedItem('benPHealth').readOnly = true;
					els.namedItem('philsysid').readOnly = true;
					els.namedItem('benContact').readOnly = false;
					els.namedItem('effectivitydate').readOnly = true;
					els.namedItem('typeClient').readOnly = false;

					els.namedItem('myfields').disabled = false;
					// End Teddy C.

					getassistperiod(xidpatient);

					$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"loadintake","idpatient":xidpatient}, function(d){ 
						if(d['error']){
							return false;
						}
						let data = d['intake'];
						let  els = document.getElementById('rafForm').elements;
	
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
						
						els.namedItem('remarks').value = data['remarks'];
						
					},"json").fail(function() {
						offlineerror();
					});

				},"json").fail(function() {
					offlineerror();
				});
			}
		}
		function getassistperiod(xidpatient){
			
			let tk = qs['tk'];
			$.get('controllers/verifypatientController.php',{"idpatient":xidpatient,"trans":"getassistperiod","tk":tk}, function (d) { filltotalamount(d);},'json').fail(function() {
			offlineerror();
			});	
			return true;
		}
		function filltotalamount(d){
			let xid = "idtotal"+d['idpatient'];
			let xtotal = Number(d['total']);
			document.getElementById('idtotal').innerHTML = "<strong>"+xtotal.toLocaleString("en-US", {style:"currency", currency:"Php"})+"</strong>";
	
		}
		function newpatient(){
			let  els = document.getElementById('rafForm').elements;
					els.namedItem('idpatient').value = -1;
					els.namedItem('rafNum').value = '';
					els.namedItem('benLName').value = '';
					els.namedItem('benFName').value = '';
					els.namedItem('benMName').value = '';
					els.namedItem('suffix').value = '';
					els.namedItem('benAddrSt').value = '';
					els.namedItem('brgyCode').selectedIndex = 0;
					els.namedItem('benSex').value = '';
					els.namedItem('benBDate').value = '';
					els.namedItem('philsysid').value = '';
					els.namedItem('benContact').value = '';
					els.namedItem('effectivitydate').value = configuredate();
					els.namedItem('typeClient').value = 'WALK-IN';

					
					// Author: Teddy C. 09/19/2023 20:19.
					els.namedItem('benLName').readOnly = false;
					els.namedItem('benFName').readOnly = false;
					els.namedItem('benMName').readOnly = false;
					els.namedItem('suffix').readOnly = false;
					els.namedItem('benAddrSt').readOnly = false;
					els.namedItem('brgyCode').readOnly = false;
					els.namedItem('benSex').readOnly = false;
					els.namedItem('benPHealth').readOnly = false;
					els.namedItem('philsysid').readOnly = false;
					els.namedItem('benContact').readOnly = false;
					els.namedItem('effectivitydate').readOnly = false;
					els.namedItem('typeClient').readOnly = false;


					els.namedItem('myfields').disabled = false;
					// End Teddy C.
		}

		function clearpatient(){
			let  els = document.getElementById('rafForm').elements;
					els.namedItem('idpatient').value = '';
					els.namedItem('rafNum').value = '';
					els.namedItem('benLName').value = '';
					els.namedItem('benFName').value = '';
					els.namedItem('benMName').value = '';
					els.namedItem('suffix').value = '';
					els.namedItem('benAddrSt').value = '';
					els.namedItem('brgyCode').selectedIndex = 0;
					els.namedItem('benSex').value = '';
					els.namedItem('benBDate').value = '';
					els.namedItem('philsysid').value = '';
					els.namedItem('benContact').value = '';
					els.namedItem('effectivitydate').value = configuredate();
					els.namedItem('typeClient').value = 'WALK-IN';
					els.namedItem('benPHealth').value = '';
					
					els.namedItem('myfields').disabled = true;
					// End Teddy C.
		}
function updateSwalContent(content) {
	const swalContent = Swal.getContent();
	if (swalContent) {
		swalContent.innerHTML = content;
	}
}

function savethis(xform, xtag){
	let cont = 1;
	let els = xform.elements;
	let medicine = false;
	els.namedItem('tag').value = xtag;
	let assistance = els.namedItem('idassistsched').options[els.namedItem('idassistsched').selectedIndex].text;
	if (!els.namedItem('rafNum').value){ toastr.error('RAF Number is empty'); cont = -1;}
	if (els.namedItem('rafNum').value.length != 8) { toastr.error('Invalid Raf Number Format!'); cont = -1; }
	if (!els.namedItem('benLName').value){ toastr.error('Patient Last name is empty'); cont = -1;}
	if (!els.namedItem('benFName').value){ toastr.error('Patient First name is empty'); cont = -1;}
	// if (!els.namedItem('benMName').value){ toastr.error('Patient Middle name is empty'); cont = -1;}
	if (!els.namedItem('brgyCode').value){ toastr.error('Patient Barangay is empty'); cont = -1;}
	if (!els.namedItem('relation').value){ toastr.error('Patient Requestor detail is incomplete'); cont = -1;}
	if (!els.namedItem('benBDate').value){ toastr.error('Invalid Patient Birthday'); cont = -1;}
	if (!els.namedItem('requestor').value){ toastr.error('Requestor is empty'); cont = -1;}
	if (!els.namedItem('billAmount').value){ toastr.error('Bill Amount is empty'); cont = -1;}
	if (!els.namedItem('typeClient').value){ toastr.error('Platform is empty'); cont = -1;}
	if (els.namedItem('amtApproved').value == 0){ toastr.error('Amount Approved is 0'); cont = -1;}
	if (!els.namedItem('remarks').value){ toastr.error('Interview remarks is empty'); cont = -1;}
	if (assistance.startsWith('MEDICINE') && xtag == 'approve') {
		if (Number(els.namedItem('billAmount').value) > Number(els.namedItem('amtApproved').value)) {
			medicine = true;
		}
	}
	if (cont == -1){
		return false;
	}
	let tk = '';
	if (qs['tk']) {tk = qs['tk'];} else {tk = localStorage.getItem('tk');}
	if (! els.namedItem('tk').value) { els.namedItem('tk').value = tk;}
	els.namedItem('assistCode').value = els.namedItem('idassistsched').options[els.namedItem('idassistsched').selectedIndex].text;
	intervalTimer = clearInterval(intervalTimer);
	document.getElementById("timerz").style.display = 'none';
	var data = new FormData(xform);
	
	let title = "";
	let content = "";
	let buttonText = "";
	let icon = "";

	if(isNaN(els.namedItem('rafNum').value)){
		toastr.error('Raf Number is invalid!');
		return false;
	}

	if(xtag == 'save' || xtag == 'approve'){
		let brgyCodeSelect = els.namedItem('brgyCode'); // Get the select element
		let selectedTitle = brgyCodeSelect.options[brgyCodeSelect.selectedIndex].textContent; // Get the title of the selected option

		let providerSelect = els.namedItem('provCode'); // Get the select element
		let selectedProviderTitle = providerSelect.options[providerSelect.selectedIndex].textContent; // Get the title of the selected option

		let assistSelect = els.namedItem('idassistsched'); // Get the select element
		let selectedAssistTitle = assistSelect.options[assistSelect.selectedIndex].textContent; // Get the title of the selected option
		
		let billAmountValue = parseFloat(els.namedItem('billAmount').value);
		let formattedBillAmount = billAmountValue.toLocaleString('en-US', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});

		let approvedAmountValue = parseFloat(els.namedItem('amtApproved').value);
		let formattedApprovedAmount = approvedAmountValue.toLocaleString('en-US', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
		
		if(xtag == 'save')
			title = "Save Information";
			else if(xtag == 'approve' && !medicine)
				title = "Approve RAF";
				else if(xtag == 'approve' && medicine)
					title = "Require Override";
		
		let contentnote = '';

		if(medicine){
			contentnote = `
				<tr>
					<td class="text-danger text-center" colspan="2" style="padding-bottom: 10px">						
						Total medicine amount is greater than the approved amount. Please request for Override.
						Documents will not be generated unless Override is approved.
					</td>
				</tr>
			`;
		}

		content = `
			<table style="width: 100%; font-size: 11.5px">
				${contentnote}
				<tr>
					<td class="text-muted fw-bold" colspan="2" style="font-size: 16px; padding-bottom: 5px">Patient Details</td>
				</tr>
				<tr>
					<td class="text-muted align-top" width="75px">Fullname <span class="float-end">:</span></td>
					<td class="fw-bold text-dark ps-1">
						${els.namedItem('benLName').value} ${els.namedItem('suffix').value}, ${els.namedItem('benFName').value} ${els.namedItem('benMName').value}
					</td>
				</tr>
				<tr>
					<td class="text-muted align-top">Birthday <span class="float-end">:</span></td>
					<td class="fw-bold text-dark ps-1">
						${els.namedItem('benBDate').value}
					</td>
				</tr>
				<tr>
					<td class="text-muted align-top">Address <span class="float-end">:</span></td>
					<td class="fw-bold text-dark ps-1">
						${els.namedItem('benAddrSt').value.trim()}, ${selectedTitle}
					</td>
				</tr>
				<tr>
					<td class="text-muted fw-bold" colspan="2" style="font-size: 16px; padding-top: 5px; padding-bottom: 5px">Assistance Details</td>
				</tr>
				<tr>
					<td class="text-muted align-top">RAF Number <span class="float-end">:</span></td>
					<td class="fw-bold text-dark ps-1">
						${els.namedItem('rafNum').value}
					</td>
				</tr>
				<tr>
					<td class="text-muted align-top">Date <span class="float-end">:</span></td>
					<td class="fw-bold text-dark ps-1">
						${els.namedItem('dateReceive').value}
					</td>
				</tr>
				<tr>
					<td class="text-muted align-top">Provider <span class="float-end">:</span></td>
					<td class="fw-bold text-dark ps-1">
						${selectedProviderTitle}
					</td>
				</tr>
				<tr>
					<td class="text-muted align-top">Assistance <span class="float-end">:</span></td>
					<td class="fw-bold text-dark ps-1">
						${selectedAssistTitle}
					</td>
				</tr>
				<tr>
					<td class="text-muted align-top">Billing <span class="float-end">:</span></td>
					<td class="fw-bold text-dark ps-1">
						Php ${formattedBillAmount}
					</td>
				</tr>
				<tr>
					<td class="text-muted align-top">Granted <span class="float-end">:</span></td>
					<td class="fw-bold text-dark ps-1">
						Php ${formattedApprovedAmount}
					</td>
				</tr>
			</table>
		`;

		if(xtag == 'save')
			buttonText = "Save";
			else if(xtag == 'approve')
				buttonText = "Approve";

		icon = "info";
	}
	else if(xtag == 'override'){
		let approvedAmountValue = parseFloat(els.namedItem('amtApproved').value);
		let formattedApprovedAmount = approvedAmountValue.toLocaleString('en-US', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});

		title = "Request for Override";
		content = `Do you want to request for override this approved amount of Php <b>${formattedApprovedAmount}</b>`;
		buttonText = "Send Request";
		icon = "warning";

		console.log(xform);
	}
	
	Swal.fire({
		title: title,
		html: content,
		icon: icon,
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: buttonText,
		showLoaderOnConfirm: true,
		width: 300,
		allowOutsideClick: () => !swal.isLoading(),
		preConfirm: () => {
			return new Promise((resolve, reject) => {
					$.ajax({
						url: 'controllers/rafController.php',
						data: data,
						cache: false,
						contentType: false,
						processData: false,
						dataType: 'json',
						type: 'POST',
						success: function(data) {
							if (data.error) {
								toastr.error('RAF Number already exists!');
								Swal.close();
								return;
							}
						
							let  x = document.getElementById('rafForm');
							x.elements.namedItem('idassistdetails').value = data['idassistdetails'];
							x.elements.namedItem('idpatient').value = data['idpatient'];
							x.elements.namedItem('idintake').value = data['idintake'];
							x.elements.namedItem('trans').value = "UPDATE";

							// put med Ids
							if (data['medIds'] && data['medIds'].length > 0) {
								var medsx = x.elements.namedItem('medProvId[]');
								for (var i = 1; i <= data['medIds'].length; i++) {
									medsx[i].value = data['medIds'][i-1];
								}
							}
							
							let xamtApproved = x.elements.namedItem('amtApproved').value;
							let xrafNum = x.elements.namedItem('rafNum').value;
							document.getElementById('divattachment').style.display = "block";
							document.getElementById('keyvalue').value = data['idassistdetails'];
							document.getElementById('keyname').value = 'idassistdetails';
							getattachments(data['idassistdetails']);

							if (data['tag']=='save'){
								document.getElementById('controlbuttons').style.display = "inline";
								x.elements.namedItem('savebutton').style.display = "none";
								x.elements.namedItem('approvebutton').style.display = "inline";
								// x.elements.namedItem('overridebutton').style.display = "inline";

								// Author: Teddy C. 09/19/2023 20:19.
								// Switch to readonly fields after saving.
								let xelements = document.getElementById('rafForm').elements;
								els.namedItem('benLName').readOnly = true;
								els.namedItem('benFName').readOnly = true;
								els.namedItem('benMName').readOnly = true;
								els.namedItem('suffix').readOnly = true;
								els.namedItem('benAddrSt').readOnly = true;
								els.namedItem('brgyCode').readOnly = true;
								els.namedItem('benSex').readOnly = true;
								els.namedItem('benPHealth').readOnly = true;
								els.namedItem('philsysid').readOnly = true;
								els.namedItem('benContact').readOnly = true;
								els.namedItem('effectivitydate').readOnly = true;
								// End Teddy C.

								toastr.success('RAF saved');
								resolve();
								return;
							}
								
							if (data['tag']=='approve'){ 
								document.getElementById('controlbuttons').style.display = "none"; //disable double submission!
								$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"approve","idassistdetails":data['idassistdetails'],"amtApproved":xamtApproved,"rafNum":xrafNum}, function(d){ 
									if (d['id'] > -1) {
										let xdiff = Number(d['balAmount']) - Number(d['balCritLevel']);

										if(medicine) {
											toastr.success('RAF Approved! Please request for override.');
											
											document.getElementById('controlbuttons').style.display ="none";
											document.getElementById('forceoverridebutton').style.display ="block";
											document.getElementById('myfields').disabled = true;

											resolve();
											
											if(xdiff < 1){
												Swal.fire({
													title: 'Critical Level Fund',
													text: "Would you like to inform the supervisor regarding the Lingap fund reaching a critical level?",
													icon: 'info',
													showCancelButton: true,
													confirmButtonColor: '#3085d6',
													cancelButtonColor: '#aaa',
													cancelButtonText: 'Later',
													confirmButtonText: 'Notify',
													showLoaderOnConfirm: true,
													width: 300,
													allowOutsideClick: () => !swal.isLoading(),
													preConfirm: () => {
														return $.get("controllers/extractrolesController.php",{"tk":qs['tk'],"trans":"getroles","roles[]":"SUPERVISOR"}, function (d){
																	let i = 0;
																	while (i < d.length) {
																		let d1 = d[i];
																		let xmessage ="Good day "+ d1['fullname']+" CPAMS ver 2.0 is informing you that Lingap fund is within the critical level. Thank you";
																		let xdata1 = {"trans":"add_notification", "tk":qs['tk'], "message":xmessage, "user_id":d1['userid'],"title":"CPAMS - Funds in Critical Level"};
				
																		$.post("https://cpams2.davaocity.gov.ph/controllers/notificationController.php",xdata1,"json").fail(function(err) {
																			console.log(err);
																			toastr.error('Error in notifying supervisors');
																			Swal.close();
																		}); 
																		i++;
																	}
																	toastr.success('Fund balance on critical level');
																},"json").fail(function() {
																	toastr.error('Failed to notify Supervisor.');
																});
													}
												});
											}
										}
										else {
											toastr.success('RAF Approved');
											let documents = 0;
											const documentPromises = [];
	
											Swal.getHtmlContainer().textContent = `(0/4) downloading document`;
											
											documentPromises.push(
											  $.get("controllers/renderGuaranteeLetterController.php", {"tk": qs['tk'], "idassistdetails": d['idassistdetails']}, "json")
												.done(function(data) {
												  Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
												})
											);
											
											documentPromises.push(
											  $.get("controllers/renderCertEligibilityController.php", {"tk": qs['tk'], "idassistdetails": d['idassistdetails']}, "json")
												.done(function(data) {
												  Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
												})
											);
											
											documentPromises.push(
											  $.get("controllers/renderCertIndigencyController.php", {"tk": qs['tk'], "idassistdetails": d['idassistdetails']}, "json")
												.done(function(data) {
												  Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
												})
											);
											
											documentPromises.push(
											  $.get("controllers/renderIntakeFormController.php", {"tk": qs['tk'], "idassistdetails": d['idassistdetails']}, "json")
												.done(function(data) {
												  Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
												})
											);
											
											$.when.apply($, documentPromises).always(function() {											
												toastr.success('Documents generated.');
												document.getElementById('controlbuttons').style.display ="none";
												document.getElementById('reportbuttons').style.display ="block";
												document.getElementById('myfields').disabled = true;
												
												resolve();
												
												if(xdiff < 1){
													Swal.fire({
														title: 'Critical Level Fund',
														text: "Would you like to inform the supervisor regarding the Lingap fund reaching a critical level?",
														icon: 'info',
														showCancelButton: true,
														confirmButtonColor: '#3085d6',
														cancelButtonColor: '#aaa',
														cancelButtonText: 'Later',
														confirmButtonText: 'Notify',
														showLoaderOnConfirm: true,
														width: 300,
														allowOutsideClick: () => !swal.isLoading(),
														preConfirm: () => {
															return $.get("controllers/extractrolesController.php",{"tk":qs['tk'],"trans":"getroles","roles[]":"SUPERVISOR"}, function (d){
																		let i = 0;
																		while (i < d.length) {
																			let d1 = d[i];
																			let xmessage ="Good day "+ d1['fullname']+" CPAMS ver 2.0 is informing you that Lingap fund is within the critical level. Thank you";
																			let xdata1 = {"trans":"add_notification", "tk":qs['tk'], "message":xmessage, "user_id":d1['userid'],"title":"CPAMS - Funds in Critical Level"};
					
																			$.post("https://cpams2.davaocity.gov.ph/controllers/notificationController.php",xdata1,"json").fail(function(err) {
																				console.log(err);
																				toastr.error('Error in notifying supervisors');
																				Swal.close();
																			}); 
																			i++;
																		}
																		toastr.success('Fund balance on critical level');
																	},"json").fail(function() {
																		toastr.error('Failed to notify Supervisor.');
																	});
														}
													});
												}
											});
										}
									} 
									else if (d['id'] == -1) {
										Swal.close();
										
										Swal.fire({
											title: 'Insufficient Fund',
											text: "Would you like to notify the supervisor about the insufficient balance in the Lingap fund?",
											icon: 'info',
											showCancelButton: true,
											confirmButtonColor: '#3085d6',
											cancelButtonColor: '#aaa',
											confirmButtonText: 'Notify',
											cancelButtonText: 'Later',
											showLoaderOnConfirm: true,
											width: 300,
											allowOutsideClick: () => !swal.isLoading(),
											preConfirm: () => {
													return	$.get("controllers/extractrolesController.php",{"tk":qs['tk'],"trans":"getroles","roles[]":"SUPERVISOR"}, function (d){
																let i = 0;
																while (i < d.length) {
																	let d1 = d[i];
																	let xmessage ="Good day "+ d1['fullname']+" CPAMS ver 2.0 is informing you that Lingap cannot approve assistance due to insufficient Fund. thank you";
																	let xdata = {"trans":"sendmsg", "tk":qs['tk'], "message":xmessage, "cellno":d1['cellno'],"email":d1['emailaddress']}
																	let xdata1 = {"trans":"add_notification", "tk":qs['tk'], "message":xmessage, "user_id":d1['userid'],"title":"CPAMS - Insufficient Fund"};

																	$.post("controllers/notifyController.php",xdata,"json").fail(function(err) {
																		console.log(err);
																		toastr.error('Error in sending sms to supervisors');
																		Swal.close();
																	}); 
																	$.post("https://cpams2.davaocity.gov.ph/controllers/notificationController.php",xdata1,"json").fail(function(err) {
																		console.log(err);
																		toastr.error('Error in notifying supervisors');
																		Swal.close();
																	}); 
																	i++;
																}
																toastr.success('Supervisors already notified');
															},"json").fail(function(err) {
																console.log(err);
																toastr.error('Error in notifying supervisors');
																Swal.close();
																return;
															});
											}
										});
									}
									else {
										// Handle errors here
										toastr.error('Error in generating documents');
										Swal.close();
										return;
									}

								},"json").fail(function() {
									toastr.error('Error in approving raf. Please try again or contact CITC.');
									Swal.close();
									return;
								});
							}

							if (data['tag']=='override'){
								$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"override","idassistdetails":data['idassistdetails']}, function(d){ 
									$.get("controllers/extractrolesController.php",{"tk":qs['tk'],"trans":"getroles","roles[]":"SUPERVISOR"}, function (d){
										let  x = document.getElementById('rafForm').elements;
										let xrafNum = x.namedItem('rafNum').value;
										let xpatient = x.namedItem('benFName').value+' '+x.namedItem('benMName').value+' '+x.namedItem('benLName').value+' '+x.namedItem('suffix').value;

										let i = 0;

										while (i < d.length) {
											let d1 = d[i];
											let xmessage ="Good day "+ d1['fullname']+" CPAMSver2 is requesting for override for patient "+xpatient+" with RAF#: "+xrafNum+" by "+ fullname +" of "+ soffice;
											let xdata = {"trans":"sendmsg", "tk":qs['tk'], "cellno":d1['cellno'],"email":d1['emailaddress'],"message":xmessage }
											let xdata1 = {"trans":"add_notification", "tk":qs['tk'], "message":xmessage, "user_id":d1['userid'],"title":"CPAMS - Override"};

											$.post("controllers/notifyController.php",xdata,"json").fail(function() {offlineerror();}); 
											$.get("https://cpams2.davaocity.gov.ph/controllers/notificationController.php",xdata1,"json").fail(function() {offlineerror();}); 
											i++;
										}

										toastr.success('Supervisors already notified');
										toastr.success('RAF forwarded for override');
										setTimeout(newRaf, 5000);
										resolve();
										//newRaf();
									},"json").fail(function(err) {
										console.log(err);
										toastr.error('Error in sending notification to supervisor.');
										Swal.close();
										return;
									});
								}).fail(function(err) {
									console.log(err);
									toastr.error('Error in requesting override. Please try again or contact CITC.');
									Swal.close();
									return;
								});
							}
						},	
						error: function (d){ 
							console.log(d);
							toastr.error('Error in submitting form. Please try again or contact CITC.');
							Swal.close();
							return;
						},
						complete: function(){
							// When ajax is complete.
						}
					});
				});
			}
	});
}

function sendRequestOverride(){
	let  x = document.getElementById('rafForm');
	let id = x.elements.namedItem('idassistdetails').value;
	
	let approvedAmountValue = parseFloat(x.elements.namedItem('amtApproved').value);
	let formattedApprovedAmount = approvedAmountValue.toLocaleString('en-US', {
		minimumFractionDigits: 2,
		maximumFractionDigits: 2
	});

	if(id > 0){
		Swal.fire({
			title: 'Request for Override',
			html: `Do you want to send a request to override this approved amount of Php <b>${formattedApprovedAmount}</b>`,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Request',
			cancelButtonText: 'Cancel',
			showLoaderOnConfirm: true,
			width: 300,
			allowOutsideClick: () => !swal.isLoading(),
			preConfirm: () => {
				return $.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"override","idassistdetails":id}, function(d){ 
							document.getElementById('reportbuttons').style.display ="none";

							$.get("controllers/extractrolesController.php",{"tk":qs['tk'],"trans":"getroles","roles[]":"SUPERVISOR"}, function (d){
								let  x = document.getElementById('rafForm').elements;
								let xrafNum = x.namedItem('rafNum').value;
								let xpatient = x.namedItem('benFName').value+' '+x.namedItem('benMName').value+' '+x.namedItem('benLName').value+' '+x.namedItem('suffix').value;
					
								let i = 0;
					
								while (i < d.length) {
									let d1 = d[i];
									let xmessage ="Good day "+ d1['fullname']+" CPAMSver2 is requesting for override for patient "+xpatient+" with RAF#: "+xrafNum+" by "+ fullname +" of "+ soffice;
									let xdata = {"trans":"sendmsg", "tk":qs['tk'], "cellno":d1['cellno'],"email":d1['emailaddress'],"message":xmessage }
									let xdata1 = {"trans":"add_notification", "tk":qs['tk'], "message":xmessage, "user_id":d1['userid'],"title":"CPAMS - Override"};
					
									$.post("controllers/notifyController.php",xdata,"json").fail(function(err) {
										console.log(err);
										toastr.error('Error in sending notification.');
										Swal.close();
										return;
									}); 
									$.get("https://cpams2.davaocity.gov.ph/controllers/notificationController.php",xdata1,"json").fail(function(err) {
										console.log(err);
										toastr.error('Error in sending sms to supervisors.');
										Swal.close();
										return;
									}); 
									i++;
								}
					
								toastr.success('Supervisors already notified');
								toastr.success('RAF forwarded for override');
								setTimeout(newRaf, 5000);
								resolve();
							},"json").fail(function(err) {
								console.log(err);
								toastr.error('Error in sending notification to supervisor.');
								Swal.close();
								return;
							});
						}).fail(function(err) {
							console.log(err);
							toastr.error('Error in requesting override. Please try again or contact CITC.');
							Swal.close();
							return;
						});
			}
		});
	}
	else
		toastr.error('Cannot override, Assist Detail ID is empty');
	
}

		function newRaf(){
			
			location.reload();
		}
		function inform(){
			let  elem = document.getElementById('rafForm').elements;
			let xidassistdetails = elem.namedItem('idassistdetails').value;
			
			$.ajax({
				url: 'controllers/notifyController.php',
				type: "POST",
				dataType: 'json',
				cache: false, 
				data: {
					trans:'available',
					idassistdetails: xidassistdetails,
					tk: qs['tk']
				},        
				success: function(result) {
					if (result.msg)
						toastr.success(result.msg);
					else
						toastr.error(result.error);
				},
				error: function(result){
					console.log(result);		
				}
			});
		}
		function intakeform(){
			let  x = document.getElementById('rafForm').elements;
			let params = 'menubar=no,width=0, height=0`,location=no, status=no, toolbar=no';
			let xidassistdetails = x.namedItem('idassistdetails').value;
			window.open('controllers/showIntakeFormController.php?idassistdetails='+xidassistdetails,'pdfwindow',params); 
		
		}
		function gletter(){
				let  x = document.getElementById('rafForm').elements;
				let params = 'menubar=no,width=0, height=0`,location=no, status=no, toolbar=no';
				let xidassistdetails = x.namedItem('idassistdetails').value;
				window.open('controllers/showGLController.php?idassistdetails='+xidassistdetails,'pdfwindow',params);
		}
		function celigibility(){
			let params = 'menubar=no,width=0, height=0`,location=no, status=no, toolbar=no';
			let  x = document.getElementById('rafForm').elements;
			let xidassistdetails = x.namedItem('idassistdetails').value;
			window.open('controllers/showCertEligibilityController.php?idassistdetails='+xidassistdetails,'pdfwindow',params); 
		
		}
		function cindigency(){
			let params = 'menubar=no,width=0, height=0`,location=no, status=no, toolbar=no';
			let  x = document.getElementById('rafForm').elements;
			let xidassistdetails = x.namedItem('idassistdetails').value;
			window.open('controllers/showCertIndigencyController.php?idassistdetails='+xidassistdetails,'pdfwindow',params); 
		
		}
		let patientWindow;
		function verifypatient(){
			let params = 'menubar=no,width=0, height=0`,location=no, status=no, toolbar=no';
			let  x = document.getElementById('rafForm').elements;
			let xidassistdetails = x.namedItem('idassistdetails').value;
			let tk = qs['tk'];
			patientWindow = window.open('patientView.html?tk='+tk+'&select=1','patientwindow',params); 
		
		}
		function setData(data){
			let  x = document.getElementById('rafForm').elements;
			x.namedItem('idpatient').value = Number(data);
			loadpatient();
			patientWindow.close();
			
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
var intervalTimer;
function timerz(el){
	if (document.getElementsByName('trans')[0].value == 'ADD' && !intervalTimer) {
		
		var timeStart = new Date().getTime();
		document.getElementById("timeLapse").innerHTML = "0 h 0 m";
		document.getElementById("timerz").style.display = 'block';
		
		intervalTimer = setInterval(function() {

		  var now = new Date().getTime();
		  var distance = now - timeStart;

		  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		  var seconds = Math.floor(distance / 1000);

		  document.getElementById("timeLapse").innerHTML = hours + " h " + minutes + " m ";
		  document.getElementsByName('timeConsume')[0].value = seconds;
		}, 1000);
	}
	
	el.value = el.value.replace(/\D/g,'');
}