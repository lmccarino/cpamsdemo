<?php

class Utility {
	public static function uploadToServer($file,$containerName,$mode=''){
		try {
            if($mode == 'generatedpdf'){
                $filetoUpload = $file;
                $fileType = mime_content_type($filetoUpload); // Determine the file type
                $file_ext = 'pdf';
            }
            else{
                $filetoUpload = $file;
                $fileType = $file['type'];
                $tmp = explode('.', $file['name']);
                $file_ext = end($tmp);
            }

			$blobName = hash('sha256', uniqid() . microtime(true) . rand()) . "." . $file_ext;

			$accesskey = "+EBv3SmaNVGvIjbYbeCl+bmvRxv6wMtfOPmYM+lMDryqPb06eILJuMlry5dc/oFpoHLkX7GLd86z+AStQgp0TQ==";
			$storageAccount = 'lingapblob';

			$destinationURL = "https://$storageAccount.blob.core.windows.net/$containerName/$blobName";

			$currentDate = gmdate("D, d M Y H:i:s T", time());
			$handle = fopen($filetoUpload, "r");
			$fileLen = filesize($filetoUpload);

			$headerResource = "x-ms-blob-cache-control:max-age=3600\nx-ms-blob-type:BlockBlob\nx-ms-date:$currentDate\nx-ms-version:2015-12-11";
			$urlResource = "/$storageAccount/$containerName/$blobName";

			$arraysign = array();
			$arraysign[] = 'PUT';               /*HTTP Verb*/  
			$arraysign[] = '';                  /*Content-Encoding*/  
			$arraysign[] = '';                  /*Content-Language*/  
			$arraysign[] = $fileLen;            /*Content-Length (include value when zero)*/  
			$arraysign[] = '';                  /*Content-MD5*/  
			$arraysign[] = $fileType;         /*Content-Type*/  
			$arraysign[] = '';                  /*Date*/  
			$arraysign[] = '';                  /*If-Modified-Since */  
			$arraysign[] = '';                  /*If-Match*/  
			$arraysign[] = '';                  /*If-None-Match*/  
			$arraysign[] = '';                  /*If-Unmodified-Since*/  
			$arraysign[] = '';                  /*Range*/  
			$arraysign[] = $headerResource;     /*CanonicalizedHeaders*/
			$arraysign[] = $urlResource;        /*CanonicalizedResource*/

			$str2sign = implode("\n", $arraysign);

			$sig = base64_encode(hash_hmac('sha256', urldecode(utf8_encode($str2sign)), base64_decode($accesskey), true));  
			$authHeader = "SharedKey $storageAccount:$sig";

			$headers = [
				'Authorization: ' . $authHeader,
				'x-ms-blob-cache-control: max-age=3600',
				'x-ms-blob-type: BlockBlob',
				'x-ms-date: ' . $currentDate,
				'x-ms-version: 2015-12-11', // 2021-04-10
				'Content-Type: ' . $fileType,
				'Content-Length: ' . $fileLen
			];

			$ch = curl_init($destinationURL);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_INFILE, $handle); 
			curl_setopt($ch, CURLOPT_INFILESIZE, $fileLen); 
			curl_setopt($ch, CURLOPT_UPLOAD, true); 
			$result = curl_exec($ch);

			$err = curl_error($ch);

			$error = "";
			if($err)
				$error = 'Error: ' . $err;

			curl_close($ch);

			if($error == '')
				return ['success' => true, 'message' => $blobName];
			else
				return ['success' => false, 'message' => $error];
		} catch (Exception $e) {
			return ['success' => false, 'message' => $e->getMessage()];
		}
		
	}
}