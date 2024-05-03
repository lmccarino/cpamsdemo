<?php

require "routines.php";

function find($id) {
    try {
        $command = "SELECT * FROM patient WHERE citizenid = '$id'";
        $result = getrow($command);
        
        if (empty($result))
            throw new Exception("Record not found.");

        $patient = $result['idpatient'];
        
        $command = "SELECT 
                        ad.rafNum AS raf,
                        ad.billAmount AS bill_amount,
                        ad.amtApproved AS approved_amount,
                        ad.dateApproved AS approved_date,
                        o.officename AS provider, 
                        `of`.officename AS location,
                        sc.assistDesc AS assistance 
                    FROM assistdetail AS ad
                    INNER JOIN assistsched AS sc ON sc.idassistsched = ad.idassistsched
                    LEFT JOIN office AS o ON o.officecode = ad.provCode
                    LEFT JOIN office AS `of` ON `of`.idoffice = ad.procloc
                    WHERE ad.idpatient = $patient;";
        $result = gettable($command);

        if (!empty($result))
            return json_encode(["success" => true, "message" => "Data fetched successfully.", "data" => $result]);
        else 
            throw new Exception("Failed to fetch record.");

    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

function fetchCitizenProfile($id) {
    // The URL you want to send the GET request to
    $url = "https://citizenscard.davaocity.gov.ph/application/api/admin/records?code=$id";
    // $url = "https://localhost:8080/dcis/application/api/admin/records?code=$id";
    // $url = "http://192.168.20.4:8080/dcis/application/api/admin/records?code=$id";

    // Initialize a cURL session
    $curl = curl_init();

    try {
        // Set the cURL options
        curl_setopt($curl, CURLOPT_URL, $url);  // Set the URL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  // Return the transfer as a string
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);  // Setting the maximum amount of time for cURL functions to execute

        // Execute the cURL session and fetch the response
        $response = curl_exec($curl);

        // Check for any cURL errors
        if (curl_errno($curl)) {
            throw new Exception('Error:' . curl_error($curl));
        }

        // Close the cURL session
        curl_close($curl);

        $result = json_decode($response);

        if (!empty($result) && $result->success)
            return json_encode(["success" => true, "message" => "Data fetched successfully.", "data" => $result->data]);
        else 
            throw new Exception("Failed to fetch record.");

    } catch (Exception $e) {
        // Close the cURL session
        curl_close($curl);

        return json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
    }
}

if($_REQUEST['trans'] == "GETRAFRECORDS")
    echo find($_REQUEST['code']);
if($_REQUEST['trans'] == "GETCITIZENPROFILE")
    echo fetchCitizenProfile($_REQUEST['code']);