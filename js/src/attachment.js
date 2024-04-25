function getattachments(keyvalue){
var skeyname=document.getElementById('keyname').value; 
var tk = qs['tk'];
	txt="trans2=getattachments&keyname="+skeyname+"&keyvalue="+keyvalue+"&tk="+tk+"&end=end";
	$.ajax({
            type: "GET",
            url: "controllers/attachmentController.php",
            data: txt,
			dataType: 'json',
            cache: false,
            success: function(data) {
					var cbody="";
					cidpayables ="";
					var count = 0;
					for (var i = 0; i < data.length; i++){
						count += 1;
						var imgdet= data[i];
						cbody += formatattachments(imgdet);	  	
						//if (count == 4) { 
						//	cbody += "<div class='row'></div>";
						//	count = 0;}
					}	
					document.getElementById('divimages').innerHTML = cbody + "<div class='row'></div>";
            }
    });
}

function formatattachments(imgdet){
	var cbody ='';
	var a1 = imgdet['imagename'];
	var id1="'"+imgdet['idattachments']+"'";
	var a = a1.split(".");
	var ext = a.pop();
	cbody ='<div id="attachment'+imgdet['idattachments']+'">';
	cbody +='<div id="path'+imgdet['idattachments']+'" style="display:none" class="col-12">'+imgdet['imagename']+'</div>';
	cbody += '<a href="#" onclick="showimg('+id1+');return false;">';
	cbody += '<img src="images/paperclip.jpg" id="img'+imgdet['idattachments']+'">'+imgdet['label']+'</a>';
	cbody += ' <a href="#" type="button" onclick="deletethis('+id1+');return false;" class="btn btn-danger btn-sm">Remove</a>';
	cbody += '</div><br/>';  
	return cbody;
}
function addimages(){
  $("#myImage").modal("show");
}
function checkfilesize()
{
    var x = document.getElementById("inputForm");
    var i;
	n=1;
	let els = x.elements;
	if (!els.namedItem('tk').value) {els.namedItem('tk').value=qs['tk'];}
    for (i = 0; i < x.length; i++) {
        var ctype1 = x.elements[i].type;
        if (ctype1 == "file") { 
            var input = x.elements[i];
            if (!input) { alert("Um, couldn't find the fileinput element.");
            }
            else if (!input.files) {
                alert("This browser doesn't seem to support the `files` property of file inputs.");
				n=0;
            }
            else if (!input.files[0]) {
                n=0;
            }
            else {
                file = input.files[0];
                if (file.size >5000000) {
                    alert("File " + file.name + " must not be more than 5MB in size");
					n=0;
                    return false;
                }
            }
            
        }
    }

	if (n==1) {
		var someform = document.getElementById('inputForm');
		var data = new FormData(someform);
		$.ajax({
            url: 'controllers/attachmentController.php',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			dataType: 'json',
			type: 'POST',
            success: function(data) {
				    if (data["idattachments"] > -1){
						var gg = document.getElementById('divimages').innerHTML;
						var cbody = formatattachments(data);
						document.getElementById('divimages').innerHTML = gg + cbody;
					}
					
					$("#myImage").modal("hide");
				
			}
		});
	}
    return true;
        
}
function deletethis(id){
  var path1 = 'path'+id;	
  var source = "attachments/"+document.getElementById(path1).innerHTML;
  var tk = qs['tk'];
  var txt='trans2=delimage&id='+id+'&tk='+tk+'&src='+encodeURI(source);
  if(!confirm("Remove this image?")){return false;}
  $.ajax({
            type: "GET",
            url: "controllers/attachmentController.php",
            data: txt,
			dataType: 'json',
            cache: false,
            success: function(data) {
                if (data['id']=="-1") {
                  alert("Failed to delete this Image...")  ;
                } else {
                  var divid='attachment'+id;
                  document.getElementById(divid).style.display = "none";
                  
                }
            
            }
              
          });
}
function showimg(id){
	    var path1 = 'path'+id;
		var imgid = 'img'+id;
		var img = document.getElementById(imgid);
		var path = document.getElementById(path1).innerHTML;
			
		var a = path.split(".");
		var astr = a.pop();
		if (astr.toUpperCase() == 'PDF') {
			//window.open('attachments/'+path,"_blank");
			let params = 'menubar=no,width=0, height=0`,location=no, status=no, toolbar=no';
			let tk=qs['tk'];
			window.open('controllers/attachmentController.php?id='+id+"&tk="+tk+"&trans2=showpdf",'pdfwindow',params);
		} else {
			let params = 'menubar=no,width=0, height=0`,location=no, status=no, toolbar=no';
			let tk=qs['tk'];
			window.open('controllers/attachmentController.php?id='+id+"&tk="+tk+"&trans2=clickimg",'imgwindow',params);
		   	//document.getElementById('imgmodal').src = 'attachments/'+path;
			//$('#myShowImage').modal('toggle');
		}
		
}
function showpdf(filename){
let params = 'menubar=no,width=0, height=0`,location=no, status=no, toolbar=no';
let tk=qs['tk'];
	window.open('controllers/attachmentController.php?iddcrform='+iddcrform+"&tk="+tk+"&trans=showpdf",'pdfwindow',params);
}
function clickimage(el){
let params = 'menubar=no,width=0, height=0`,location=no, status=no, toolbar=no';
let source = encodeURI(el.src);
let tk=qs['tk'];
	window.open(source,'pdfwindow',params);
	
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
function hidemyImage(){
	$('#myImage').modal('toggle');
}
function hidemyShowImage(){
	$('#myShowImage').modal('toggle');
}
function startattachment() {
	$("#attachmentdiv").load("attachment.html");
}