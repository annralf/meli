<?php
#header("Access-Control-Allow-Origin: *");
#header('Content-Type: application/json');
#header('Content-Type application/json; charset=utf-8');

$test = $_POST['action'];

if ($test == "test") {
	http_response_code(200);
	$result = array('response'=>'OK');
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
}else{
    http_response_code(404);
    echo json_encode(array('msg' => "Falló la conexión a la base de datos", 'error' => 'error'), JSON_UNESCAPED_UNICODE);
}