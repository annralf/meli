<?php 
ini_set('display_errors', 1);
ini_set('log_errors', 1);
require_once 'db_mng.php';

$conn = new Connect();
$tokenMng = new MELIConnect(1);

if (isset($_GET['code'])) {
	$authorization_code = $_GET['code'];
	$application_id = $tokenMng->app_detail->application_id;
	$application_secret_key = $tokenMng->app_detail->secret_key;
	$url = "https://api.mercadolibre.com/oauth/token?grant_type=authorization_code&client_id=$application_id&client_secret=$application_secret_key&code=$authorization_code&redirect_uri=https://app.tokioexpress.co/resources/auth.php";
	$connection = curl_conn($url);
	echo $tokenMng->set_access_token($connection->access_token, $connection->refresh_token);

}else{
	$application_id = $tokenMng->app_detail->application_id;
	if (isset($tokenMng->app_detail->refresh_access_token)) {
		$refresh_token = $tokenMng->app_detail->refresh_access_token;
		$application_secret_key = $tokenMng->app_detail->secret_key;
		$url = "https://api.mercadolibre.com/oauth/token?grant_type=refresh_token&client_id=$application_id&client_secret=$application_secret_key&refresh_token=$refresh_token";
		$connection = curl_conn($url);
		echo $tokenMng->set_access_token($connection->access_token, $connection->refresh_token);
	}else{
		$url = "https://auth.mercadolibre.com.co/authorization?response_type=code&client_id=$application_id";
		header("Location:".$url);	    
	}
}

function curl_conn($url){
	$ch             = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($item));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	$connection = json_decode(curl_exec($ch));
	curl_close($ch);
	return $connection;
}