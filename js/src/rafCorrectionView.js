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
function addDependent(){
	var nform = $('#divDependent').clone(true);
	nform.appendTo( '#divDependents' );
	nform.show();

}
function removeMedicine(el){
	let ppDiv = el.parentNode.parentNode;
	$.get("controllers/rafCorrectionController.php", {
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
	computerate2(true);
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
			if (!x.namedItem('requestor').value){  cont = -1;}
			if (!x.namedItem('billAmount').value){ ; cont = -1;}
			if (!x.namedItem('remarks').value){  cont = -1;}
		},"json").fail(function() {
			offlineerror();
		});
}
function computerate2(calculate=false){
	let  els = document.getElementById('rafForm').elements;
	let el = els.namedItem('billAmount');
	let xbillAmount = Number(el.value);
	let xrateCode = els.namedItem('rateCode').value;
	
    let cont = 1;
    if (!els.namedItem('requestor').value){  cont = -1;}
    if (!els.namedItem('billAmount').value){ ; cont = -1;}
    if (!els.namedItem('remarks').value){  cont = -1;}
    
    if (cont > -1){	
        els.namedItem('savebutton').style.display ="inline";
    }

    if(calculate){
        $.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getrate","billAmount":xbillAmount,"rateCode":xrateCode}, function(data){ 
            els.namedItem('amtApproved').value = data['amtApproved'];
        },"json").fail(function() {
            offlineerror();
        });
    }
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
	if (!els.namedItem('requestor').value){ toastr.error('Requestor is empty'); cont = -1;}
	if (!els.namedItem('reqAddr').value){ toastr.error('Requestor Address is empty'); cont = -1;}
	if (!els.namedItem('relation').value){ toastr.error('Requestor Relation is empty'); cont = -1;}
	if (!els.namedItem('billAmount').value){ toastr.error('Bill Amount is empty'); cont = -1;}
	if (!els.namedItem('remarks').value){ toastr.error('Interview remarks is empty'); cont = -1;}
	if (!els.namedItem('typeClient').value){ toastr.error('Platform is empty'); cont = -1;}

	// if (Number(els.namedItem('amtApproved').value) != Number(els.namedItem('amtApprovedOrig').value))
	// { toastr.error('Amount is larger than previously approved!'); cont = -1; }
	if (cont == -1){
		return false;
	}
	let tk = '';
	if (qs['tk']) {tk = qs['tk'];} else {tk = localStorage.getItem('tk');}
	if (! els.namedItem('tk').value) { els.namedItem('tk').value = tk;}
	els.namedItem('assistCode').value = els.namedItem('idassistsched').options[els.namedItem('idassistsched').selectedIndex].text;
	var data = new FormData(xform);

    Swal.fire({
        title: 'RAF Correction',
        html: `Are you sure to save this changes?`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Save & Approve',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        width: 300,
        allowOutsideClick: () => !swal.isLoading(),
        preConfirm: () => {
            return $.ajax({
                        url: 'controllers/rafCorrectionController.php',
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        type: 'POST',
                        success: function(result) {
							if(result)
								if (result.success){
									if (result["data"]["idassistdetails"] > -1) {
										displayResult(result["data"]);

										if (assistance.startsWith('MEDICINE') && Number(els.namedItem('billAmount').value) > Number(els.namedItem('amtApproved').value)) {
											document.getElementById('reportremarks').style.display ="inline";
										}
										else {
											document.getElementById('reportbuttons').style.display ="inline";
											generateDocuments(result["data"]["idassistdetails"]);
										}
										toastr.success('Changes saved');
									} else {
										toastr.error('Error in saving...');
									}
								}
								else{
									toastr.error(result.message);
								}
                        },	
						error: function(xhr, status, error) {
							// Error callback
							console.log('XHR:', xhr);
							console.log('Status:', status);
							console.log('Error:', error);
							toastr.error('Error in submitting form, please try again or contact CITC.');
							Swal.close();
						}
                    });
        }
    });
}
function intakeform(){
	var id = document.getElementById('idassistdetails').value;
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/showIntakeFormController.php?idassistdetails='+id);	
}
function gletter(){
	var id = document.getElementById('idassistdetails').value;
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/showGLController.php?idassistdetails='+id);	
}
function celigibility(){
	var id = document.getElementById('idassistdetails').value;
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/showCertEligibilityController.php?idassistdetails='+id);
}
function cindigency(){
	var id = document.getElementById('idassistdetails').value;
	$('#print-gl-modal').modal('show');
	$('#print-gl-container').prop('hidden',false);
	$('#print-gl-container').attr('src', 'controllers/showCertIndigencyController.php?idassistdetails='+id);	
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

function displayResult(data, notice=false) {
	let allowUpdate = false;
	const now = new Date();
	
	// Calculate the difference in days between Approved and Current Date.
	let dateReceive = data['dateReceive'];

	if(dateReceive){
		const formattedRecieved = dateReceive.split(' ')[0];
		const recievedDate = new Date(formattedRecieved);
		const recievedDifference = Math.floor((now - recievedDate) / (1000 * 60 * 60 * 24));
		
		if (recievedDifference <= 5) allowUpdate = true;
	}
    
	// UPDATE: Disabled by Teddy C. 12/27/2023 15:22. TLs Requested no time limit on RAF Corrections.
    // if(!allowUpdate){
    //     Swal.fire({
    //         title: 'Disabled',
    //         html: `RAF correction is only allowed within <strong>5 days</strong> upon RAF approval.`,
    //         icon: 'warning',
    //         width: 300
    //     });
        
    //     return false;
    // }
	// End of UPDATE

	if(notice)
    	toastr.success('RAF Found');

	document.getElementById('controlbuttons').style.display = "inline";

	getHistory(data['idassistdetails'],'ASSISTDETAIL');

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
    
    Swal.fire({
        title: 'Loading',
        html: `<small>Loading collecting Information, please wait...</small>`,
        icon: 'info',
		confirmButtonColor: '#3085d6',
		confirmButtonText: 'Generate',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !swal.isLoading(),
	    didOpen: () => {
	        Swal.clickConfirm()
	    },
        width: 300,
        preConfirm: () => {
			document.getElementById('controlbuttons').style.display = "none";
            return  $.ajax({
                        url: 'controllers/rafCorrectionController.php',
                        data: data,
                        dataType: "json",
                        type: 'POST',
                        success: function(data) {
                            if (data["idassistdetails"] > -1) {
								displayResult(data,true);
                            } else {
                                toastr.error('No RAF found!');
                            }
                        },
                        fail: function(err) {
                            console.log(err);
                            toastr.error('Error in getting RAF information!');
                        }
                    });
        }
    });
}

function generateDocuments(id){	
	let documents = 0;
	Swal.fire({
		title: 'Documents',
		text: "Do you want to generate new documents?",
		icon: 'info',
		showCancelButton: true,
		cancelButtonColor: '#aaa',
		cancelButtonText: 'Later',
		confirmButtonColor: '#3085d6',
		confirmButtonText: 'Generate',
		showLoaderOnConfirm: true,
		width: 300,
		allowOutsideClick: () => !swal.isLoading(),
		preConfirm: () => {
			Swal.getHtmlContainer().textContent = `(0/4) downloading document`;
			return $.when(
						$.get("controllers/renderGuaranteeLetterController.php",{"idassistdetails":id},"json").always(function(){
							Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
						}),
						$.get("controllers/renderCertEligibilityController.php",{"tk":qs['tk'],"idassistdetails":id},"json").always(function(){
							Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
						}),
						$.get("controllers/renderCertIndigencyController.php",{"tk":qs['tk'],"idassistdetails":id},"json").always(function(){
							Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
						}),
						$.get("controllers/renderIntakeFormController.php",{"tk":qs['tk'],"idassistdetails":id},"json").always(function(){
							Swal.getHtmlContainer().textContent = `(${++documents}/4) downloading document`;
						})
					).then(function () {
						// Success
					}).fail(function () {
						// Handle errors here
						Swal.close(); // Close the modal
					});
		}
	});
}

function compareArrayObjects(list) {
    const rows = [];

    function compareObjects(oldObj, newObj, parentKey = '') {
        const changes = [];

        // Check if oldObj is an empty object
        if (Object.keys(oldObj).length === 0) {
            // Display all properties from newObj
            for (const key in newObj) {
                const newValue = newObj[key];
                const fullKey = parentKey ? `${parentKey}.${key}` : key;
                changes.push(`'${fullKey}': '${newValue}'`);
            }
        } else if (Object.keys(newObj).length === 0) {
            // Display all properties from oldObj
            for (const key in oldObj) {
                const oldValue = oldObj[key];
                const fullKey = parentKey ? `${parentKey}.${key}` : key;
                changes.push(`'${fullKey}': '${oldValue}'`);
            } 
        } else {
            for (const key in oldObj) {
                if (newObj.hasOwnProperty(key)) {
                    const oldValue = oldObj[key];
                    const newValue = newObj[key];
                    const fullKey = parentKey ? `${parentKey}.${key}` : key;

                    if (typeof oldValue === 'object' && typeof newValue === 'object') {
                        if (Array.isArray(oldValue) && Array.isArray(newValue)) {
                            const arrayChanges = compareArrays(oldValue, newValue);
                            if (arrayChanges.length > 0) {
                                changes.push(`'${fullKey}': [${arrayChanges}]`);
                            }
                        } else {
                            const objectChanges = compareObjects(oldValue, newValue, fullKey);
                            if (objectChanges.length > 0) {
                                changes.push(`'${fullKey}': { ${objectChanges} }`);
                            }
                        }
                    } else if (oldValue !== newValue) {
                        changes.push(`'${fullKey}': '${oldValue}' to '${newValue}'`);
                    }
                }
            }
        }

        return changes.join(', ');
    }

    function compareArrays(oldArray, newArray) {
        const arrayChanges = [];

        for (let i = 0; i < oldArray.length || i < newArray.length; i++) {
            const oldItem = oldArray[i];
            const newItem = newArray[i];

            if (typeof oldItem === 'object' && typeof newItem === 'object') {
                const objectChanges = compareObjects(oldItem, newItem);
                if (objectChanges.length > 0) {
                    arrayChanges.push(`{ ${objectChanges} }`);
                }
            } else if (oldItem !== newItem) {
                arrayChanges.push(`'${i}': '${oldItem}' to '${newItem}'`);
            }
        }

        return arrayChanges.join(', ');
    }

    $.each(list, function (index, item) {
        const olddumps = JSON.parse(item.olddumps);
        const newdumps = JSON.parse(item.newdumps);

        const changes = compareObjects(olddumps, newdumps);

        if (changes) {
            rows.push({
                'date': item.created_at,
                'transaction': item.actions,
                'history': `changes are ${changes}.`,
                'officer': item.officer
            });
        }
    });

    return rows;
}



// function compareArrayObjects(list) {
// 	const rows = [];
  
// 	function compareObjects(oldObj, newObj, parentKey = '') {
// 	  const changes = [];
  
// 	  for (const key in oldObj) {
// 		if (newObj.hasOwnProperty(key)) {
// 		  const oldValue = oldObj[key];
// 		  const newValue = newObj[key];
  
// 		  const fullKey = parentKey ? `${parentKey}.${key}` : key;
  
// 		  if (typeof oldValue === 'object' && typeof newValue === 'object') {
// 			if (Array.isArray(oldValue) && Array.isArray(newValue)) {
// 			  const arrayChanges = compareArrays(oldValue, newValue);
// 			  if (arrayChanges.length > 0) {
// 				changes.push(`'${fullKey}': [${arrayChanges}]`);
// 			  }
// 			} else {
// 			  const objectChanges = compareObjects(oldValue, newValue, fullKey);
// 			  if (objectChanges.length > 0) {
// 				changes.push(`'${fullKey}': { ${objectChanges} }`);
// 			  }
// 			}
// 		  } else if (oldValue !== newValue) {
// 			changes.push(`'${fullKey}': '${oldValue}' to '${newValue}'`);
// 		  }
// 		}
// 	  }
  
// 	  return changes.join(', ');
// 	}
  
// 	function compareArrays(oldArray, newArray) {
// 	  const arrayChanges = [];
  
// 	  for (let i = 0; i < oldArray.length || i < newArray.length; i++) {
// 		const oldItem = oldArray[i];
// 		const newItem = newArray[i];
  
// 		if (typeof oldItem === 'object' && typeof newItem === 'object') {
// 		  const objectChanges = compareObjects(oldItem, newItem);
// 		  if (objectChanges.length > 0) {
// 			arrayChanges.push(`{ ${objectChanges} }`);
// 		  }
// 		} else if (oldItem !== newItem) {
// 		  arrayChanges.push(`'${i}': '${oldItem}' to '${newItem}'`);
// 		}
// 	  }
  
// 	  return arrayChanges.join(', ');
// 	}
  
// 	$.each(list, function (index, item) {
// 	  const olddumps = JSON.parse(item.olddumps);
// 	  const newdumps = JSON.parse(item.newdumps);
  
// 	  const changes = compareObjects(olddumps, newdumps);
  
// 	  if (changes) {
// 		rows.push({
// 		  'date': item.created_at,
// 		  'transaction': item.actions,
// 		  'history': `changes are ${changes}.`,
// 		  'officer': item.officer
// 		});
// 	  }
// 	});
  
// 	return rows;
// }
  
  
  
function getHistory(id,reference){
	$.ajax({
		url: 'controllers/historyController.php',
		type: "POST",      
		cache: false,
		dataType: 'json',
		data: {
			trans: 'GETHISTORYLIST',
			referenceid: id,
			reference: reference,
			tk: qs['tk']
		},        
		success: function(result) {
			let history = compareArrayObjects(result['data']);
			if(history.length > 0){
				$('#history-container').prop('hidden', false);
				$rows = `<table style="width:100%; font-size: 11px">`;
	  			$.each(history, function(key, value) {
					$rows += `
						<tr>
							<td class="align-top text-nowrap" width="110px">${value.date}</td>
							<td class="align-top text-nowrap" width="110px">${value.transaction}</td>
							<td class="align-top">${value.history}</td>
							<td class="align-top text-nowrap pl-2" width="110px">${value.officer}</td>
						</tr>
					`
				});
				$rows += `</table>`;
				$('#history-list').html($rows);
			}

		},
		error: function(result){
			toastr.error('Error in loading history');
			console.log(result);		
		}
	});
}