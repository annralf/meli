<?php 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/tokioExpress/resources/test_service.php?action=test');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
$update = json_decode(curl_exec($ch));
curl_close($ch);
print_r($update);