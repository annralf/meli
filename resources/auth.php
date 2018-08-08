<?php 
require_once 'db_mng.php';

$conn = new Connect();

if (isset($_POST['code'])) {
    $authorization_code = $_POST['code'];
	
}else{
    $tokenMng = new MELIConnect(1);
    $application_id = $tokenMng->app_detail->application_id;
    $url = "http://auth.mercadolibre.com.co/authorization?response_type=code&client_id=$application_id";
    header("Location:".$url);
}