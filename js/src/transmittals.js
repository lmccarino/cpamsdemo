let _list = [];

function startload() {
    searchTransmittal();
	getProviders();
}

function print(id) {
   	$('#modal-print').modal('show');
   	$('#modal-print-container').prop('hidden',false);	
   	$('#modal-print-container').attr('src', `controllers/print/transmittalPrint.php?id=${id}&tk=${qs['tk']}`);	
}

function clearSearch() {
	document.querySelectorAll('#searchForm input').forEach(input => input.value = null);
}

function getProviders() {
	$.ajax({
		url: 'controllers/transmittalController.php',
		type: "POST",      
		cache: false,
		dataType: 'json',
		data: {
			trans: "GETPROVIDERS",
			tk: qs['tk']
		},
		success: function(result) {
			if (result.success){
				// Dictionary to store options grouped by category
				let optionsByCategory = {};

				// Group options by category
				result.data.forEach(optionText => {
					if (!optionsByCategory[optionText.category]) {
						optionsByCategory[optionText.category] = [];
					}
					optionsByCategory[optionText.category].push(optionText);
				});

				// Append options grouped by category to the <select> element
				Object.keys(optionsByCategory).forEach(category => {
					// Create an <optgroup> element for each category
					let optgroup = $("<optgroup></optgroup>").attr("label", category);
					
					// Append options to the optgroup
					optionsByCategory[category].forEach(optionText => {
						const option = $("<option></option>")
							.text(`${optionText.provider}`)
							.val(optionText.code);
						optgroup.append(option);
					});

					// Append the optgroup to the <select> element
					$("#transmittal-provcode").append(optgroup);
				});
			}
			else{
				toastr.error(result.message);
			}
		},
		error: function(result){
			Swal.fire({
				title: 'Fetch Error',
				text: "An error occur loading providers. Please reload page.",
				icon: 'warning',
				width: 300
			})
			console.log(result);		
		}
	});
}

function getTransmittalRafs(id) {
	$('#raf-rows').html(`
		<tr>
			<td colspan="7" class="text-center">
				<div class="spinner-border text-success" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
			</td>
		</tr>
	`);
	$.ajax({
		url: 'controllers/transmittalController.php',
		type: "POST",      
		cache: false,
		dataType: 'json',
		data: {
			trans: "GETTRANSMITTALRAFS",
			id: id,
			tk: qs['tk']
		},
		success: function(result) {
			if (result.success){

				let rows = '';
				let counter = 1;

				result.data.forEach(item => {
					let approvedAmountValue = parseFloat(item.amtApproved);
					let formattedApprovedAmount = approvedAmountValue.toLocaleString('en-US', {
						minimumFractionDigits: 2,
						maximumFractionDigits: 2
					});
					
					rows += `
						<tr>
							<td>${counter++}</td>
							<td>${item.benLName}, ${item.benFName} ${item.benMName} ${item.suffix}</td>
							<td>${item.rafNum}</td>
							<td>₱ ${formattedApprovedAmount}</td>
							<td>${item.dateApproved}</td>
							<td>
								<a style="cursor:pointer;" class="text-danger btn-remove-raf" data-id="${item.idassistdetails}" data-name="${item.benLName}, ${item.benFName} ${item.benMName} ${item.suffix}"><i class="fas fa-trash-alt"></i></a>
							</td>
						</tr>
					`;
				});
				
				$('#raf-rows').html(rows);
			}
			else{
				$('#raf-rows').html('<tr><td colspan="7" class="text-danger text-center">No record/s found.</td></tr>');
			}
		},
		error: function(result){
			Swal.fire({
				title: 'Fetch Error',
				text: "An error occur loading providers. Please reload page.",
				icon: 'warning',
				width: 300
			})
			$('#raf-rows').html('<tr><td colspan="7" class="text-danger text-center">Error in loading RAFs.</td></tr>');
			console.log(result);		
		}
	});
}

function searchTransmittal() {
	document.getElementById('details').style.display = "block";
	const tk=qs['tk'];
    
	if ($("#searchForm").serialize().trim().length <= 33 )
		return false;

	_list = [];

    const ajx = `controllers/transmittalController.php?trans=SEARCHTRANSMITTALS&tk=${tk}&${$("#searchForm").serialize()}`;
	
	$('#ds').dataTable().fnDestroy();

	if ($('#ds tbody').empty()) {
		var table = $('#ds').DataTable({
			dom: 'lfrtip', 
			ajax: ajx,
			columns: [
				{ data: "created" },
				{ data: "idtransmittals" },
				{ 
					data: "providerName",
					// className: 'text-nowrap' // Add CSS class to the "providerName" column
				},
				{ data: "soa" },
				{ data: "checkno" },
				{ 
					data: function (o) { return recordTask(o) },
					className: 'text-nowrap' // Add CSS class to the column returned by recordTask function
				}
			],
			order: [[0, 'desc']]
		});
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

function recordTask(o) {
	_list.push(o);

	var id = "'"+o['idtransmittals']+"'";

	var callEdit   = "showEdit("+id+")";
	var callDelete = "showDelete("+id+")";
	var callPrint  = "print("+id+")";

	return `
		<span class="text-nowrap">
			<a onclick="${callPrint}" class="btn btn-sm btn-success"><i class="fas fa-print"></i></a>
			<a onclick="${callEdit}" class="btn btn-sm btn-primary"><i class="fas fa-pencil"></i></a>
			<a onclick="${callDelete}" class="btn btn-sm btn-danger ml-1"><i class="fas fa-trash-alt"></i></a>
		</span>
	`;	
}

function showEdit(id){
	openForm(id);
}

function showDelete(id){
	Swal.fire({
		title: 'Delete Transmittal',
		text: "Do you want to delete this transmittal?",
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		confirmButtonText: 'Confirm',
		showLoaderOnConfirm: true,
		allowOutsideClick: () => !swal.isLoading(),
		width: 320,
		preConfirm: () => {
			return $.ajax({
						url: 'controllers/transmittalController.php',
						type: "POST",      
						cache: false,
						dataType: 'json',
						data: {
							trans: "DELETE",
							id: id,
							tk: qs['tk']
						},
						success: function(result) {
							if (result.success){
								toastr.success(result.message);
								$('#btnSave').prop('hidden',true);
								searchTransmittal();
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

function resetForm(){

}

function openForm(id=null) {
	$('#btnPrint').prop('hidden',true);
	$('#btnSave').prop('hidden',false);
	$('#raf-list').prop('hidden',true);
	
	if(id != null){
		let selected = [];

		$.each(_list, function (index, item) {
			if(item.idtransmittals == id){
				$('#btnPrint').prop('hidden',false);
				$('#raf-list').prop('hidden',false);
				var button = document.getElementById('btnSave');
				button.innerText = button.textContent = 'Update';

				$("#trans").val("EDIT");	
				$("#tk").val(qs['tk']);	
	
				$("#transmittal-id").val(item.idtransmittals);	
				$("#transmittal-encoded").val(item.created);	
				$("#transmittal-provcode").val(item.provcode);	
				$("#transmittal-soa").val(item.soa);	
			
				getTransmittalRafs(id);

				$('#modal-form').modal("show");
			}
	
		});
	}
	else{
		var button = document.getElementById('btnSave');
		button.innerText = button.textContent = 'Add';
				
		$("#trans").val("ADD");	
		$("#tk").val(qs['tk']);	

		$("#transmittal-id").val('');	
		$("#transmittal-encoded").val('');	
		$("#transmittal-provcode").val('');	
		$("#transmittal-soa").val('');	
	
		$('#modal-form').modal("show");
	}
	
}

$("#entryform").submit(function(event) {
	event.preventDefault();
	var data =  $("#entryform").serialize();
	var id =  $("#id").val();
	var trans = $('#trans').val();
	
	Swal.fire({
		title: (trans == "ADD") ? 'Add' : 'Modify',
		text: `This action will ${(trans == "ADD") ? 'add' : 'modify'} this transmittal.`,
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Save',
		showLoaderOnConfirm: true,
		width: 300,
		allowOutsideClick: () => !swal.isLoading(),
		preConfirm: () => {
			return $.ajax({
						url: 'controllers/transmittalController.php',
						cache: false,
						type: 'get',
						data : data,
						dataType: 'json',
						success: function(result) {
							var button = document.getElementById('btnSave');
							button.innerHTML = `
								<span class="spinner-border spinner-border-sm p-2 text-secondary" aria-hidden="true"></span>
								<span class="visually-hidden" role="status">Loading...</span>
							`;
							
							if (result.success) {
								toastr.success(`${(trans == "ADD") ? 'Add' : 'Modify'} successfully done...`);
								var table = $('#ds').DataTable();
								var deferred = $.Deferred();

								// Reload data via AJAX
								table.ajax.reload(null, false);

								// Listen for the draw event to know when the reload is finished
								table.one('draw', function() {
									// This code will execute when the table has finished reloading
									console.log("Data reloaded successfully.");

									// Additional actions you want to perform after reload can be added here
									$('#modal-form').modal("hide");
									openForm(result.data.id);

									// Resolve the deferred object
									deferred.resolve();
								});

								// Use the deferred object to execute code after the reload is finished
								deferred.then(function() {
									// This code will execute after the reload is finished
									console.log("Deferred resolved. Reload finished.");
								});

							} else {
								toastr.error(`${(trans == "ADD") ? 'Add' : 'Modify'} Failed. Please check details...`);
							}
						},
						error: function(xhr, status, error) {
							Swal.close();
							Swal.fire({
								title: 'Saving Error',
								text: "An error occur while saving information. Please try again.",
								icon: 'warning',
								width: 300
							})
						}
					});
		}
	});
 });

 $('#input-search-raf').keypress(function(event) {
    if (event.which === 13) { // Check if Enter key is pressed
        // Your code to execute when Enter key is pressed
        // For example, you can call a function or perform an action here
        // Example:
        searchRAF($(this).val());
    }
});

 $('#btn-search-raf').click(function(){
	searchRAF($('#input-search-raf').val());
 });

 function searchRAF(id){
	Swal.fire({
		title: "Search RAF",
		text: `Loading RAF information, Please wait...`,
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Save',
		showLoaderOnConfirm: true,
		didOpen: () => {
            Swal.clickConfirm()
		},
		width: 330,
		allowOutsideClick: () => !swal.isLoading(),
		preConfirm: () => {
			return $.ajax({
						url: 'controllers/transmittalController.php',
						cache: false,
						type: 'get',
						data : {
							'trans': 'GETRAF',
							'id': id,
							'code': $("#transmittal-provcode").val(),
							'tk': qs['tk']
						},
						dataType: 'json',
						success: function(result) {
							if (result.success) {
								Swal.fire({
									title: "Patient Information",
									html: `
										<table style="width: 100%; font-size: 11.5px">
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
												${result.data.benAddrSt}, ${result.data.brgyName}, ${result.data.distName}
											</td>
										</tr>
										<tr>
											<td class="text-muted fw-bold" colspan="2" style="font-size: 16px; padding-top: 5px; padding-bottom: 5px">Assistance Details</td>
										</tr>
										<tr>
											<td class="text-muted align-top">RAF Number <span class="float-end">:</span></td>
											<td class="fw-bold text-dark ps-1">
												${result.data.rafNum}
											</td>
										</tr>
										<tr>
											<td class="text-muted align-top">Approved<span class="float-end">:</span></td>
											<td class="fw-bold text-dark ps-1">
												${result.data.dateApproved}
											</td>
										</tr>
										<tr>
											<td class="text-muted align-top">Provider <span class="float-end">:</span></td>
											<td class="fw-bold text-dark ps-1">
												${result.data.assistCode}
											</td>
										</tr>
										<tr>
											<td class="text-muted align-top">Billing <span class="float-end">:</span></td>
											<td class="fw-bold text-dark ps-1">
												${result.data.billAmount}
											</td>
										</tr>
										<tr>
											<td class="text-muted align-top">Granted <span class="float-end">:</span></td>
											<td class="fw-bold text-dark ps-1">
												${result.data.amtApproved}
											</td>
										</tr>
									</table>
									`,
									icon: 'info',
									showCancelButton: true,
									confirmButtonColor: '#3085d6',
									cancelButtonColor: '#d33',
									confirmButtonText: 'Add',
									showLoaderOnConfirm: true,
									width: 330,
									allowOutsideClick: () => !swal.isLoading(),
									preConfirm: () => {
										return $.ajax({
													url: 'controllers/transmittalController.php',
													cache: false,
													type: 'get',
													data : {
														'trans': 'ADDRAFTOTRANSMITTAL',
														'id': result.data.idassistdetails,
														'transmittal': $("#transmittal-id").val(),
														'tk': qs['tk']
													},
													dataType: 'json',
													success: function(result) {
														if (result.success) {
															getTransmittalRafs($("#transmittal-id").val());
															$('#input-search-raf').val('');
														} else {
															toastr.error(`Search RAF Failed. ${result.message}`);
														}
													},
													error: function(xhr, status, error) {
														Swal.close();
														Swal.fire({
															title: 'Search Error',
															text: "An error occur while searching information. Please try again.",
															icon: 'warning',
															width: 300
														})
													}
												});
									}
								});
							} else {
								toastr.error(`Search RAF Failed. Details: ${result.message}`);
							}
						},
						error: function(xhr, status, error) {
							Swal.close();
							Swal.fire({
								title: 'Search Error',
								text: "An error occur while searching information. Please try again.",
								icon: 'warning',
								width: 300
							})
						}
					});
		}
	});
 }

 $(document).delegate('#btnPrint','click',function(){
	print($("#transmittal-id").val());
 });

 $(document).delegate('.btn-remove-raf','click',function(){
	let id   = $(this).attr('data-id');
	let name = $(this).attr('data-name');
	Swal.fire({
		title: 'Remove RAF',
		html: `Do you want to remove <b>${name}</b> to this transmittal?`,
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		confirmButtonText: 'Confirm',
		showLoaderOnConfirm: true,
		allowOutsideClick: () => !swal.isLoading(),
		width: 320,
		preConfirm: () => {
			return $.ajax({
						url: 'controllers/transmittalController.php',
						type: "POST",      
						cache: false,
						dataType: 'json',
						data: {
							trans: "REMOVERAFTOTRANSMITTAL",
							id: id,
							tk: qs['tk']
						},
						success: function(result) {
							if (result.success){
								getTransmittalRafs($("#transmittal-id").val());
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
 });