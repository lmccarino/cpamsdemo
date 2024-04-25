<?php
require "routines.php";
function getattachments(){
	$keyname = $_REQUEST['keyname'];
	$keyvalue= $_REQUEST['keyvalue'];
	$command = "select * from attachments where keyname = '$keyname' and keyvalue = $keyvalue";
	require "connect.php";
	$data=array();
	if ($result=$conn->query($command)){
		$data = $result->fetch_all(MYSQLI_ASSOC);
	}
	$conn->close();
	unset($conn);
	$myJSON = json_encode($data);
	echo $myJSON;
	
}
function save($userid,$mysys){
	$myobj = new stdClass();
	$keyname = $_POST['keyname'];
	$keyvalue = $_POST['keyvalue'];
	$label =$_POST['label'];
	$label = htmlspecialchars(strtoupper($label),ENT_QUOTES,"UTF-8");
    $file = $_FILES['imagefile'];
	$picture = $file['name'];
    if ($picture != ""){   
        $source =$file['tmp_name'];
		$stamp = getdate();
		$imagename = $stamp['year'].$stamp['mon'].$stamp['mday'].$stamp['hours'].$stamp['minutes'].$stamp['seconds']."_".$picture;
		$save = '../attachments/'.$imagename; //This is the new file you saving
		move_uploaded_file($source, $save);
		$check = 1;
		//$conn_id = ftp_connect('localhost'); 
		//$login_result = ftp_login($conn_id, "dcr", "DCR@2019"); 
		//if ((!$conn_id) || (!$login_result)) { $check = 0; $myObj->message1 = "Failed to login";}
		//if ($check == 1) {
			//$upload = ftp_put($conn_id, $save, $source, FTP_BINARY); 
		//}
		//if (!$upload) { $check = 0; $myObj->message2 = "Failed to upload";} else {ftp_chmod($conn_id, 0775, $save);}
		//ftp_close($conn_id); 
		$label = $picture;
		$command = "CALL insertattachments('$keyname',$keyvalue, '$label', '$imagename',$userid)";
		$myobj->idattachments = -1;
		$myobj->imagename = $imagename;
		$myobj->label  = $label;
		if ($check == 1){
		require "connect.php";
		if ($result=$conn->query($command)){
			$row = $result->fetch_assoc();
			$myobj->idattachments = $row['@id'];
		}
		$conn->close();
		unset($conn);
		} else {$myobj->message = "Failed to save ".$save;}
		$myJSON = json_encode($myobj);
		echo $myJSON;
	}
}
function deleteimage($userid){
	$myobj = new stdClass();
	$src = '../'.$_REQUEST['src'];
	$id = $_REQUEST['id'];
	$myobj->id = -1;
	$command ="call deleteattachment($id,$userid)";
	$row = getrow($command);
	$myobj->id = $row['@id'];
	unlink($src);
	$myJSON = json_encode($myobj);
	echo $myJSON;
}
function showpdf(){
	$idattachments= $_REQUEST['id'];
	$command = "select * from attachments where idattachments = $idattachments";
	$row=getrow($command);
	$filename = '../attachments/'.$row['imagename'];
	$filename1 = $row['imagename'];
    //$fileinfo = pathinfo($filename);
    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($filename));
	header("Content-Disposition:inline;filename='$filename1'");
    readfile($filename);
}
function clickimage(){
	$source= $_REQUEST['source'];
	$asource = explode(".",$source);
	$count = count($asource);
	$afname = explode("/",$source);
	$count2 = count($afname);
	$i2 = $count2 -1;
	$filename1 = $afname[$i2];
	$i = $count - 1;
	$el = $asource[$i];
	$app = "application/".$el;
	$filename = $source;
	//$filename1 = $source;
    //$fileinfo = pathinfo($filename);
    header('Content-Type: $app');
    //header('Content-Length: ' . filesize($filename));
	//header("Content-Disposition:inline;filename='$filename1'");
    readfile($filename);
}
function clickimg(){
	$idattachments= $_REQUEST['id'];
	$command = "select * from attachments where idattachments = $idattachments";
	$row=getrow($command);
	$source = '../attachments/'.$row['imagename'];
	$asource = explode(".",$source);
	$count = count($asource);
	$afname = explode("/",$source);
	$count2 = count($afname);
	$i2 = $count2 -1;
	$filename1 = $afname[$i2];
	$i = $count - 1;
	$el = $asource[$i];
	$app = "application/".$el;
	$filename = $source;
	header('Content-Type: $app');
	readfile($filename); 
    
}
$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:../index.html?message='.urlencode("Invalid User")); die();
} else {$userid = $myobj->userid;}
$trans2 = $_REQUEST['trans2'];
if ($trans2=='attachment'){
	save($userid,$mysys);
}
if ($trans2=='getattachments'){
	getattachments();
}
if ($trans2=='delimage'){
	deleteimage($userid);
}
if ($trans2=='showpdf'){
	showpdf();
}
if ($trans2=='clickimage'){
	clickimage();
}
if ($trans2=='clickimg'){
	clickimg();
}
?>
