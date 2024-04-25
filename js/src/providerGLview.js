function startload() {
	document.getElementById('details').style.display = "block";
	$('#ds').dataTable().fnDestroy();
	if ($('#ds tbody').empty()) {
		var tk=qs['tk'];

		var table = $('#ds').DataTable( {
			dom: 'lfrtip', 
	
		} );
	}
	$('#ds tbody').off();
}

function searchRaf() {
	document.getElementById('details').style.display = "block";
	var raf = $("#rafSearch").val();	
	var fr= "";
	var to = "";
	var ajx= "";
	var tk=qs['tk'];

	if (raf.trim() == "") {
		return false;
	}
	else {
		ajx = "controllers/providerGLcontroller.php?trans=LIST2&tk="+tk+"&search=" + raf + "&loc=" + soffice;
	}
  
	$('#ds').dataTable().fnDestroy();
	if ($('#ds tbody').empty()) {

		var table = $('#ds').DataTable( {
			dom: 'lfrtip', 
			"ajax": ajx,
			"columns": [
				{ "data": "rafNum" },
				{
					"data": null,
					"render": function (data, type, row) {
						return row.benLName + ', ' + row.benFName  + '  ' + row.benMName;
					}
				},
				{ "data": "assistCode"},
				{ "data": "billAmount" },             
				{ "data": "amtApproved"},
				{
					"className": 'combinedButtons',
					"render": function (data, type, row) {
						var tagReceiveButton = '<button type="button" data-toggle="tooltip" title="Tag Receive" class="btn btn-sm btn-info tagReceive"';
		
						let dateApproved = row.dateApproved;
						let dateReissue = row.dateReissue;
						let allowTagReceive = false;
						const now = new Date();
		
						if (dateApproved) {
							const formattedApproved = dateApproved.split(' ')[0];
							const approvedDate = new Date(formattedApproved);
							const approvedDifference = Math.floor((now - approvedDate) / (1000 * 60 * 60 * 24));
		
							if (approvedDifference <= 5) allowTagReceive = true;
						}
		
						if (dateReissue) {
							const formattedReissued = dateReissue.split(' ')[0];
							const reissuedDate = new Date(formattedReissued);
							const reissuedDifference = Math.floor((now - reissuedDate) / (1000 * 60 * 60 * 24));
		
							if (reissuedDifference <= 5) allowTagReceive = true;
						}
		
						if (row.dateGLReceive !== null || !allowTagReceive) {
							tagReceiveButton += ' disabled';
						}
		
						tagReceiveButton += '> <i class="fa-solid fa-tag" aria-hidden="true"></i> Receive GL</button>';
						
						var viewGLButton = '<button type="button" data-toggle="tooltip" title="View GL" class="btn btn-sm btn-success viewGL" style="margin-left: 5px;"> <i class="fa-solid fa-eye" aria-hidden="true"></i> GL</button>';
		
						//return '<div class="btn-group" role="group">' + tagReceiveButton + viewGLButton +'</div>';
						return '<div class="btn-group" role="group">' + tagReceiveButton +'</div>';
					}
				}

			],
			"order": [[0, 'desc']],
			"footerCallback": function ( row, data, start, end, display ) {
				var api = this.api(), data;
				var intVal = function ( i ) {
						return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 :
					typeof i === 'number' ?
					i : 0;
				};
				total = api.column(4).data().reduce( function (a, b) {
					return intVal(a) + intVal(b);
				}, 0 );
				var columnFooter = api.column(4).footer();

					if (columnFooter) {
						$(columnFooter).html(total.toLocaleString('en-US', {style: 'currency', currency: 'PHP'}));
					}

			}
		} );
	}
	$('#ds tbody').off();

	$('#ds tbody').on('click', 'button.tagReceive', function () {
		var tr = $(this).closest('tr');
		var row = table.row(tr).data();
		tagReceiveFunction(row);
	});
	
	$('#ds tbody').on('click', 'button.viewGL', function () {
		var tr = $(this).closest('tr');
		var row = table.row(tr).data();
		viewGLFunction(row);
	});
	
}

function viewGLFunction(data){
	var id = data['idassistdetails'];
	
	let params = 'menubar=no,width=0, height=0`,location=no, status=no, toolbar=no';
	window.open('controllers/showGLController.php?idassistdetails='+id,'pdfwindow',params);
}

function tagReceiveFunction(data) {
    var id = data['idassistdetails'];
        Swal.fire({
            title: 'GL Receive',
            text: "This will inform the CMO-Lingap that you have received this Guarantee Letter. Are you sure?",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Confirm',
            showLoaderOnConfirm: true,
			allowOutsideClick: () => !Swal.isLoading(),
			preConfirm: () => {
				Swal.showLoading();

				return $.ajax({
					url: 'controllers/providerGLcontroller.php',
					type: "POST",
					cache: false,
					dataType: 'json',
					data: {
						trans: 'tagreceive',
						idassistdetails: id, 
						tk: qs['tk']
					},
					success: function (result) {
						if (result.msg) {
							Swal.fire({
								icon: 'success',
								title: 'Success',
								text: result.msg
							});
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: result.error
							});
						}
					},
					error: function (result) {
						console.log(result);
					}
				}).finally(() => {
					Swal.close();
				});
			}
        });
}