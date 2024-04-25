<?php
    $header = array();
    $header[] = 'Content-type: application/json';
    $header[] = 'Authorization: key=AAAAJj_7-rI:APA91bEbXA6El0d0bX9qxpO72wb9nNqsFA1qdALPexeFskDB_r3CGDNVFc0NMs_cICWfogpvOD7MDcekj-GynSRMvL-K7w6KhjMXYwtUblRdReKOZgUqnhzisIrlLRapXho3NLWoWDBE';
    $ch = curl_init();
    $url = "https://fcm.googleapis.com/fcm/send";
    $data = [
        "to" => $_REQUEST['tk'],
        "notification"=>[
            "title"=>"CPAMS v2.0 Notification",
            "body"=>"Test sample sent from server. " . date('Y-m-d h:i:sa') 
        ]
    ];
    $crl = curl_init();
    curl_setopt($crl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($crl, CURLOPT_POST,true);
    curl_setopt($crl, CURLOPT_URL, $url);
    curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode( $data ) );

    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true );
    // curl_setopt($crl, CURLOPT_SSL_VERIFYHOST, false); should be off on production
    // curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, false); shoule be off on production

    $rest = curl_exec($crl);
    if ($rest === false) {
        print_r(curl_error($crl));
    }
    curl_close($crl);
    print_r($rest);
?>