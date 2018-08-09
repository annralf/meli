<?php

/**
 * 
 */
class Connect
{
	public $connector = NULL;

	function __construct(){
		$this->connector = pg_connect("host=173.208.145.134 port= 5432 dbname=anaguere_te user=anagu_te password=T0k10_N4g");
		if (!$this->connector) {
			http_response_code(500);
			die(json_encode(array('msg' => 'DB not connect'),JSON_UNESCAPED_UNICODE));
		}
	}

	public function close(){
		pg_close($this_connector);
	}

}

class CURLRequest{
	public $ch;
	function __construct($url, $type,$params){
		$this->ch = curl_init();		
		curl_setopt($this->ch, CURLOPT_URL, $url);
		switch ($type) {
			case 'POST':
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
			break;

			case 'GET':
			break;
		}
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_FRESH_CONNECT, true);
		curl_close($this->ch);
		return json_decode(curl_exec($this->ch));
	}
}

class MELIConnect {
	public $app_detail;
	public $connector;
	public $shop_id;

	function __construct($shop_id){
		$this->shop_id = $shop_id;
		$this->connector = new Connect();
		$sql = "SELECT * FROM  meli_shop WHERE id = $shop_id";
		$this->app_detail = pg_fetch_object(pg_query($sql));
	}

	function set_access_token($access_token,$refresh_token){
	    $shop_id = $this->shop_id;
	    $date = date("Y-m-d H:s:m");
	    $sql = "UPDATE meli_shop SET access_token = '$access_token', refresh_access_token= '$refresh_token', update_date=  '$date' WHERE id = $shop_id;";
	    $result = pg_query($sql);
	    if ($result > 0) {
	    	return 1;
	    }else{
		return 0;
	    }
	}
	function get_access_token(){
	    $shop_id = $this->shop_id;
	    $result = pg_fetch_object(pg_query("SELECT access_token, refresh_access_token FROM meli_shop WHERE id = $shop_id;"));
	    return $result;
	}

	function search_access_token($code){
	    $application_id = $this->app_detail->application_id;
	    $application_secret_key = $this->app_detail->secret_key;
	    $url = "https://api.mercadolibre.com/oauth/token?grant_type=authorization_code&client_id=$application_id&client_secret=$application_secret_key&code=$code&redirect_uri=https://app.tokioexpress.co/resources/auth.php";
	    $connection = new CURLRequest($url, 'GET',NULL);
	    $this->set_access_token($connection->access_token, $connection->refresh_token);
	}

	function search_refresh_token(){
	    $params = array(
		'grant_type'    => 'refresh_token',
		'client_id'     => $this->app_detail->application_id,
		'client_secret' => $this->app_detail->secret_key,
		'refresh_token' => $this->app_detail->refresh_access_token
	    );
	    $url = "https://api.mercadolibre.com/oauth/token";
	    $result = new CURLRequest($url,'POST',$params);
	    $this->set_access_token($result->access_token, $result->refresh_token);
	}
}
