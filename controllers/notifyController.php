<?php
require "routines.php";

class SMSNOTIFICATION {
    protected $gateway  = "https://messagingsuite.smart.com.ph";
    protected $username = "josejr.barber@davaocity.gov.ph";
    protected $password = "Junb2021";

    protected function request($url,$params=[],$headers=[],$method="POST")
    {
		$crl = curl_init();
		curl_setopt($crl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($crl, CURLOPT_POST,true);
		curl_setopt($crl, CURLOPT_URL, $url);
		curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode( $params ) );
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true );
	
		$rest = curl_exec($crl);

		if ($rest === false)
            return ['success' => false, 'message' => 'Sending request failed.', 'data' => curl_error($crl)];

        curl_close($crl);
        return ['success' => true, 'message' => 'Request successfully sent.', 'data' => json_decode($rest)];
    }

    public function authenticate()
    {
        $url        = $this->gateway . '/rest/auth/login';
		$headers[]  = 'Content-type: application/json';
		$data       = [
			"username" => $this->username,
			"password" => $this->password,
		];

        $result = $this->request($url,$data,$headers);
        if($result['success'] && isset($result['data']->accessToken))
            return ['success' => true, 'message' => 'Login successful.', 'data' => $result['data']];
        else
            return ['success' => false, 'message' => 'Failed to login', 'data' => []];
    }
    
    public function send($nos,$msg)
    {		
		return $this->globe($nos,$msg);
    }
	
	private function globe($nos,$msg)
	{
		$url = "https://api.m360.com.ph/v3/api/broadcast";
		$headers[]  = 'Content-type: application/json';
		$data       = [
			"app_key" => "FguxNMoeIEaYzusc",
			"app_secret" => "QR8XaMlEOK6Ke21bc1SelL3xxInCwak2",
			"msisdn" => $nos[0],
			"content" => $msg,
			"shortcode_mask" => "LingapDavao"
		];

        $result = $this->request($url,$data,$headers);
        return $result;
	}
	
	private function old($nos,$msg)
	{
		$authenticate = $this->authenticate();

        if(!$authenticate['success'])
            return $authenticate;

        $url        = $this->gateway . '/rest/messages/sms';
		$headers[]  = 'Content-type: application/json';
		$headers[]  = 'Authorization: Bearer ' . $authenticate['data']->accessToken;
		$data       = [
			"endpoints" => $nos,
			"message"   => [ "text" => $msg ],
		];

        $result = $this->request($url,$data,$headers);
        return $result;
	}
}

function sendmsg(){
	$email = $_REQUEST['email'];
	$cellno = $_REQUEST['cellno'];
	$message = $_REQUEST['message'];
$error = "";
//$message = htmlspecialchars($_REQUEST['message'],ENT_QUOTES, "UTF-8");
//https://sms.davaocity.gov.ph/Home/Websend?cellno=09&message="
//https://info.davaocity.gov.ph/Home/sendsysinfo?email&subject&content&sender  -- post method

	
	$content = "
	
		<html>
		<head>
			<title>CPAMSv2 - City Public Assistance Management System</title>
		</head>
		<body>
			<h1><img src='https://cpams2.davaocity.gov.ph/images/davaocitylogo.png' style='width:75px'><img src='https://cpams2.davaocity.gov.ph/images/lifeishere.jpg' style='width:150px'></h1>
			<p>$message</p>
			<p>&nbsp;</p>
            <p>Very truly yours,</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>Davao City CPAMS Team</p>
            <p>This is a system generated email.  Please do not reply.  This is a test message<p>
            <p>&nbsp;</p>
            <div><i>This email and its attachments are intended solely for the addressee(s) as indicated above and may contain confidential and/or privileged information which may be legally protected from disclosure. If you are not the intended recipient or if this message has been addressed to you in error, please immediately alert the sender by reply email and then delete this message and its attachments. Please be advised that any use, dissemination, copying, or storage of this message or its attachments is strictly prohibited.</i></div>
		</body>
		</html>
	";
	//$email = ''; //a nice trick by emptying email to bypass email notification below (remove line when email is needed again)
	if (!empty($email)) {
		$ch1 = curl_init();
		$url1= "https://info.davaocity.gov.ph/home/sendsysinfo";
		$data1 = http_build_query(array("email"=>$email,"subject"=>"CPAMS Notification ID#: ".mt_rand(),"content"=>$content,"sender"=>"CPAMSv2, City Government of Davao"));
					
					curl_setopt($ch1,CURLOPT_SSL_VERIFYPEER,FALSE);
					curl_setopt($ch1,CURLOPT_FOLLOWLOCATION,TRUE);
					curl_setopt($ch1,CURLOPT_RETURNTRANSFER,TRUE);
					curl_setopt($ch1,CURLOPT_ENCODING,"");
					curl_setopt($ch1,CURLOPT_MAXREDIRS,10);
					curl_setopt($ch1,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
					curl_setopt($ch1,CURLOPT_CUSTOMREQUEST,"POST");
					curl_setopt($ch1,CURLOPT_POSTFIELDS,$data1);
					curl_setopt($ch1,CURLOPT_URL,$url1);
					curl_setopt($ch1,CURLOPT_TIMEOUT,80);
					$response2 = curl_exec($ch1);
					if (curl_error($ch1)){
						$error =  '1.  Request Error: '. curl_error($ch1);
					}
					curl_close($ch1);
	}
	
	
	
	
	
	
	
	if ( !empty($cellno) ) {

		// $ch2 = curl_init();
		// $url2= "https://cpams2.davaocity.gov.ph/controllers/smsController.php";
		// // $url2= "https://sms.davaocity.gov.ph/home/send";
		// //$url2 = "https://sms.davaocity.gov.ph/Home/Websend";
		// //$msg = "Good Day $receiver, CPAMS is requesting for override on patient $patient with RAF No. $rafNum.";
		// $data2 = http_build_query(array("trans"=>"send","cellno"=>$cellno,"message"=>$message));
		// // $data2 = http_build_query(array("cellno"=>$cellno,"message"=>$message));
		// $getUrl2 = $url2."?".$data2;
		// //echo "<br/>".$getUrl2;
		// curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,FALSE);
		// curl_setopt($ch2,CURLOPT_FOLLOWLOCATION,TRUE);
		// curl_setopt($ch2,CURLOPT_RETURNTRANSFER,TRUE);
		// curl_setopt($ch2,CURLOPT_URL,$getUrl2);
		// curl_setopt($ch2,CURLOPT_TIMEOUT,80);
		// $response2 = curl_exec($ch2);
		// if (curl_error($ch2)){
		// 	$error = $error. ' 2.  Request Error: '. curl_error($ch2);
		// }
		// curl_close($ch2);

		$sms = new SMSNOTIFICATION();
		$nos[] = $cellno;
		$sms->send($nos,$message);
	}

$error = $error. ' Sent Commands Completed';
echo json_encode($error);
}

function informRequestor() {
	
	$idassistdetails = $_REQUEST['idassistdetails'];
	$command = "SELECT ass.idassistdetails, p.idpatient, concat(p.benFName, ', ', p.benLName) as name, p.benContact, concat(ofc.officename, ' Office') as office FROM assistdetail ass LEFT JOIN patient p ON p.idpatient = ass.idpatient LEFT JOIN office ofc ON ofc.idoffice = ass.procloc WHERE ass.idassistdetails = $idassistdetails AND ass.status = 'APPROVED'";
	$patient = getrow($command);
	$office = ucwords(strtolower($patient['office']));
	$cellno  = $patient['benContact'];
	$receiver= $patient['name']; //var_dump($patient);
	if ( !empty($cellno) ) {

		$ch2 = curl_init();
		$url2= "https://cpams2.davaocity.gov.ph/controllers/smsController.php";
		$message = "Good Day $receiver, you may claim your Guarantee Letter at our $office. Thank you";
		$data2 = http_build_query(array("trans"=>"send","cellno"=>$cellno,"message"=>$message));
		$getUrl2 = $url2."?".$data2;
		//echo "<br/>".$getUrl2;
		curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch2,CURLOPT_FOLLOWLOCATION,TRUE);
		curl_setopt($ch2,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch2,CURLOPT_URL,$getUrl2);
		curl_setopt($ch2,CURLOPT_TIMEOUT,80);
		$response2 = curl_exec($ch2);
		if (curl_error($ch2)){
			$error = $error. ' 2.  Request Error: '. curl_error($ch2);
			echo json_encode(['error' => 'Error Phone number']);
		} else {
			echo json_encode(['msg' => 'Message sent!']);
		}
		curl_close($ch2);
		
	} else echo json_encode(['error' => 'Error Phone number']);
}

$myobj = validatetoken();
if (empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
} else {$userid = $myobj->userid;}
$trans = $_REQUEST['trans'];

if ($trans=="sendmsg"){
	sendmsg();
	
}
if ($trans=='insufficient'){
	$role = "SUPERVISOR";
	$receivers = getroles($role);
	for($i = 0; $i < count($receivers); $i++) {
      $receiver = $receivers[$i];
	  insufficientfund($receiver['fullname'], $receiver['cellno'], $receiver['emailaddress']);
	}
	
}
if ($trans=='available'){
	informRequestor();
}
?>