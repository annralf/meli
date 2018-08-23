<?php 
ini_set('display_errors', 1);
ini_set('log_errors', 1);
require_once 'db_mng.php';

$conn = new Connect();
$tokenMng = new MELIConnect(1);

if (isset($_POST['code'])) {
	$authorization_code = $_POST['code'];
	$application_id = $tokenMng->app_detail->application_id;
	$application_secret_key = $tokenMng->app_detail->secret_key;
	$url = "https://api.mercadolibre.com/oauth/token?grant_type=authorization_code&client_id=$application_id&client_secret=$application_secret_key&code=$code&redirect_uri=https://app.tokioexpress.co/resources/auth.php";
	 $connection = $tokenMng->app_detail->CURLRequest($url, 'POST',NULL);
	 echo $tokenMng->app_detail->set_access_token($connection->access_token, $connection->refresh_token);

}else{
	$application_id = $tokenMng->app_detail->application_id;
	$url = "https://auth.mercadolibre.com.co/authorization?response_type=code&client_id=$application_id";
	header("Location:".$url);
}