<?php

class SMS {
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


$myobj = new stdClass();
$myobj->userid = 2;
if (empty($myobj->userid)){
    header('Location:index.html?message='.urlencode("Invalid User"));
} else {
	$userid = $myobj->userid;
}

$trans = $_REQUEST['trans'];
if ($trans=='send') {
    $sms = new SMS();
    // $authenticate = $sms->authenticate();

    $cellno[] = $_REQUEST['cellno'];
    $message  = $_REQUEST['message'];
    
    $sms->send($cellno,$message);
}