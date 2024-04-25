let orginalValue = [];

function startload() {
	getbrgys();

	// Select all input elements within the form with the ID "searchForm"
	document.querySelectorAll('#entryform input,#entryform select').forEach(input => {
		// Add an event listener for the "focusout" event
		input.addEventListener('focusout', function() {
			if(input.value == orginalValue[input.id]){
				$(`#${input.id}-state`).html(``);
			}
			else {
				$(`#${input.id}-state`).html(`edited`);
			}
		});
	});
}

function getbrgys(){
	$.get("controllers/rafController.php",{"tk":qs['tk'],"trans":"getbrgys"}, function(data, xstatus){ fillbrgys(data);},"json").fail(function() {
		Swal.fire({
			title: 'Network Error',
			text: "Failed to load barangay list.",
			icon: 'warning',
			width: 300,
			showCancelButton: true,
			confirmButtonText: 'Retry'
		  }).then((result) => {
			if (result.isConfirmed) {
				getbrgys();
			}
		})
	});	
}

function fillbrgys(data){
	let  x = document.getElementById('entryform');
	let gl = x.elements.namedItem('brgyCode');
	
	let  row = data['brgys'];

	gl.innerHTML = "";

	for (a = 0;a < row.length; a++){
		let option = document.createElement("option");
			option.text = row[a]['brgyName'] +', '+ row[a]['distName'];
			option.value = row[a]['brgyCode'];
			gl.add(option); 
	
	}	

	gl.selectedIndex = 0;				
}

function searchPatient() {
	document.getElementById('details').style.display = "block";
	const tk=qs['tk'];
    
	if ($("#searchForm").serialize().trim().length <= 33 )
		return false;

    const ajx = `controllers/patientController.php?trans=SEARCHPATIENT&tk=${tk}&${$("#searchForm").serialize()}`;
	
	$('#ds').dataTable().fnDestroy();

	if ($('#ds tbody').empty()) {
		var table = $('#ds').DataTable( {
			dom: 'lfrtip', 
			"ajax": ajx,
			"columns": [
				{"className":      'details-control',
				 "orderable":      false,
				 "data":           null,
				 "defaultContent": ''
				},
				{ "data": "idpatient" },
				{ "data": "benLName" },
				{ "data": "benFName" },
				{ "data": "benMName" },
				{ "data": "suffix" },
				{ "data": "benBDate" },             
				{ "data": function (o) { return recordTask(o) }},
			],
			"order": [[0, 'desc']]
		} );
	}
	$('#ds tbody').off();

    $('#ds tbody').on('click', 'td.taskBtn', function (o) {
        let tr = $(this).closest('tr');
        let row = table.row( tr );
		deletethis(row.data());
	});

	$('#ds tbody').on('click', 'td.details-control', function () {
		let tr = $(this).closest('tr');
		let row = table.row(tr);

		console.log(row.data());

		if (row.child.isShown()) {
			row.child.hide();
			tr.removeClass('shown');
		}
		else {
			row.child(format(row.data())).show();
			// tr.addClass('shown');
			getchild(row.data());
		}
	});
}

function getchild(data){
	console.log(data);
	let tableid = `#idpatient${data['idpatient']}`;
	let tk = qs['tk'];

	let table = $(tableid).DataTable({
		"destroy":true,
		"searching":false,
		dom: 'lfrtip', 
		"ajax": {
			"url":"controllers/verifypatientController.php?trans=getdetails&idpatient="+data['idpatient']+"&tk="+tk, 
			"error": function (xhr, error, code) {offlineerror();}
		},
		"columns": [
			{"data": "rafnum"},
			{"data": "officename"},
			{"data": "status"},
			{"data": "amtApproved", "render": $.fn.dataTable.render.number( ',', '.', 2 ,'')},
			{"data": "dateApproved"},
			{"data": "assistCode"},
			{"className":'details2',"data": "noteTag"},
			{"className":"remarks","data": null, "defaultContent": '<small><button type="button" class="btn btn-info btn-sm"><small>Remarks</small></button></small>'},
		],
		"order": [[3, 'desc']],
		paging: false, // Disable pagination
		info: false,  // Disable the "Showing X to Y of Z entries" message
	} );
	
	$(tableid+' tbody').off();
	
	$(tableid+' tbody').on('click', 'td.remarks', function () {
		let tr = $(this).closest('tr');
		let row = table.row( tr );
		let d = row.data();

		
		Swal.fire({
			title: 'Remarks',
			html: `<span class="text-justify">${d['remarks']}</span>`,
		})
	});
}

function format(row){
	let id = row['idpatient'];
	let elem = document.getElementById(id);
	if (elem) elem.parentNode.removeChild(id);

	return `
		<table class="table table-responsive" id="idpatient${id}">
			<thead>
				<tr>
					<th>RAFNum</th>
					<th>Provider</th>
					<th>Status</th>
					<th>Amount</th>
					<th>Date</th>
					<th>Assistance</th>
					<th>CMO Note</th>
					<th></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	`;
}

function clearSearch() {
	document.querySelectorAll('#searchForm input').forEach(input => input.value = null);
}

function openPatientInformation(idpatient, benLName, benFName, benMName, suffix, benAddrSt, benSex, benBDate, benContact, benPHealth, philsysid, brgyCode, effectivitydate) {
	$('#btnSave').prop('hidden',false);
	
	document.querySelectorAll('#entryform input,#entryform select').forEach(input => {
		$(`#${input.id}-state`).html(``);
	});

	$('#history-container').prop('hidden', true);

	getHistory(idpatient,'PATIENT');

	orginalValue = {
		'patient-id': 				idpatient,
		'patient-lastname': 		benLName,
		'patient-firstname': 		benFName,
		'patient-middlename':		benMName,
		'patient-suffix': 			suffix,
		'patient-address': 			benAddrSt,
		'patient-sex': 				benSex,
		'patient-dob': 				benBDate,
		'patient-contact': 			benContact,
		'patient-philhealth-id': 	benPHealth,
		'patient-philsys-id': 		philsysid,
		'patient-barangay': 		brgyCode,
		'patient-encoded': 			effectivitydate
	};

	$('#modal-form').modal("show");
	$('#tk').val(qs['tk']);
	$('#patient-id').val(idpatient);
	$('#patient-encoded').val(effectivitydate);
	$('#patient-lastname').val(benLName);
	$('#patient-firstname').val(benFName);
	$('#patient-middlename').val(benMName);
	$('#patient-suffix').val(suffix);
	$('#patient-address').val(benAddrSt);
	$('#patient-barangay').val(brgyCode);
	$('#patient-dob').val(benBDate);
	$('#patient-sex').val(benSex);
	$('#patient-contact').val(benContact);
	$('#patient-philhealth-id').val(benPHealth);
	$('#patient-philsys-id').val(philsysid);
}

function recordTask(o) {
	let callOpenPatientInformation = `
		openPatientInformation(
			'${o['idpatient']}',
			'${o['benLName']}',
			'${o['benFName']}',
			'${o['benMName']}',
			'${o['suffix']}',
			'${o['benAddrSt']}',
			'${o['benSex']}',
			'${o['benBDate']}',
			'${o['benContact']}',
			'${o['benPHealth']}',
			'${o['philsysid']}',
			'${o['brgyCode']}',
			'${o['effectivitydate']}',
		);
  	`;

	return `
		<a onclick="${callOpenPatientInformation}" class="btn btn-sm btn-icon" style="color:#2b7a78"><i class="fas fa-pencil"></i></a>
	`;
}

$('#entryform').on('submit', function(e) {
	e.preventDefault(); // Prevent the default form submission behavior

	let changes = false;
	
	document.querySelectorAll('#entryform input, #entryform select').forEach(input => {
		if (input.id !== 'trans' && input.id !== 'tk') {
			if (input.value !== orginalValue[input.id]) {
				changes = true; // Set changes to true if any element's value has changed
			}
		}
	});
	
	if(changes){
		Swal.fire({
			title: 'Save Changes',
			text: "Do you want to save changes? Please take note that this may cause data inconsistency.",
			icon: 'info',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			confirmButtonText: 'Confirm',
			showLoaderOnConfirm: true,
			allowOutsideClick: () => !swal.isLoading(),
			width: 300,
			preConfirm: () => {
				return $.ajax({
							url: 'controllers/patientController.php',
							type: "POST",      
							cache: false,
							dataType: 'json',
							data: $(this).serialize(),        
							success: function(result) {
								if (result.success){
									toastr.success(result.message);
									$('#btnSave').prop('hidden',true);
									searchPatient();
								}
								else{
									toastr.error(result.message);
								}
							},
							error: function(result){
								// Handle errors here
								Swal.close(); // Close the modal
								Swal.fire({
									title: 'Saving Error',
									text: "An error occur while saving data. Please try again.",
									icon: 'warning',
									width: 300
								})
								console.log(result);		
							}
						});
			}
		});
	}
	else{
		Swal.fire({
			title: 'No Changes',
			text: "No changes were made. Please check.",
			icon: 'warning',
			width: 300
		})
	}
	
});

function compareArrayObjects(list) {
	const rows = [];

	$.each(list, function(index, item) {
		const changes = [];
		
		const olddumps = JSON.parse(item.olddumps);
		const newdumps = JSON.parse(item.newdumps);

	  	$.each(olddumps, function(key, value) {
			if(newdumps.hasOwnProperty(key))
				if (olddumps[key] !== newdumps[key])
					changes.push(`'${olddumps[key]}' to '${newdumps[key]}'`);
	  	});
  
	  	if(changes.length > 0) 
			rows.push({
				'date': item.created_at,
				'history': `changes are ${changes.join(', ')}`,
				'officer': item.officer
			});
	});

	return rows;
}

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