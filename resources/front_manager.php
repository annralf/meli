<?php
require_once 'db_mng.php';

$conn = new Connect();

switch ($_POST['action']) {
	case 'get_aws_items':
	$sql;
	switch ($_POST['type']) {
		case 'detail':
		$sql = "SELECT id, sku, product_title_english, sale_price, url, update_date FROM aws_items ORDER BY id ASC LIMIT 2";
		$source = pg_query($sql);		
		$table = "";
		$i = 1;
		while ($items = pg_fetch_object($source)) {
			$table .="<tr class='$items->id'>";
			$table .="<td>$i</td>";
			$table .="<td>$items->sku</td>";
			$table .="<td style='width: 450px; word-wrap: break-word;'>$items->product_title_english</td>";
			$table .="<td>$ $items->sale_price USD</td>";
			$table .="<td style='width: 85px; word-wrap: break-word;'><a href='$items->url'>Ir a artículo $items->sku</a></td>";
			$table .="<td>$items->update_date</td>";
			$table .="<td><i class='fa fa-trash' aria-hidden='true' onclick='delete_item($items->id)' style='cursor:point;'></i></td>";
			$table .= "</tr>";
			$i++;
		}
		echo json_encode(array('result'=>$table));
		break;	    	
		case 'counter':
		$sql = "SELECT COUNT(*) FROM aws_items;";
		$result = pg_fetch_object(pg_query($sql));
		$counter = $result->count;
		$letter = "";
		if ($counter > 1000) {
			$counter = $counter/1000;
			$letter = "K";
		}
		if ($counter > 1000000) {
			$counter = $counter/1000000;
			$letter = "M";
		}
		echo json_encode(array('amount' => $counter, 'letter'=>$letter));
		break;
	}
	break;
	case 'get_aws_items_prime':
	$sql;
	switch ($_POST['type']) {
		case 'detail':
		$sql = "SELECT sku, product_title_english, sale_price, url, update_date FROM aws_items WHERE is_prime='1';";
		$source = pg_query($sql);		
		$table = "";
		$i = 1;
		while ($items = pg_fetch_object($source)) {
			$table .="<tr>";
			$table .="<td>$i</td>";
			$table .="<td>$items->sku</td>";
			$table .="<td style='width: 450px; word-wrap: break-word;'>$items->product_title_english</td>";
			$table .="<td>$ $items->sale_price USD</td>";
			$table .="<td style='width: 85px; word-wrap: break-word;'><a href='$items->url'>Ir a artículo $items->sku</a></td>";
			$table .="<td>$items->update_date</td>";
			$table .="<td><i class='fa fa-trash' aria-hidden='true' onclick='delete_item($items->id)' style='cursor:point;'></i></td>";
			$table .= "</tr>";
			$i++;
		}
		echo json_encode(array('result'=>$table));
		break;
		case 'counter':
		$sql = "SELECT COUNT(*) FROM aws_items WHERE is_prime='1';";
		$result = pg_fetch_object(pg_query($sql));
		$counter = $result->count;
		$letter = "";
		if ($counter > 1000) {
			$counter = $counter/1000;
			$letter = "K";
		}
		if ($counter > 1000000) {
			$counter = $counter/1000000;
			$letter = "M";
		}
		echo json_encode(array('amount' => $counter, 'letter'=>$letter));
		break;
	}
	break;


	case 'get_aws_items_noprime':
	$sql;
	switch ($_POST['type']) {
		case 'detail':
		$sql = "SELECT sku, product_title_english, sale_price, url, update_date FROM aws_items WHERE is_prime='0';";
		$source = pg_query($sql);		
		$table = "";
		$i = 1;
		while ($items = pg_fetch_object($source)) {
			$table .="<tr>";
			$table .="<td>$i</td>";
			$table .="<td>$items->sku</td>";
			$table .="<td style='width: 450px; word-wrap: break-word;'>$items->product_title_english</td>";
			$table .="<td>$ $items->sale_price USD</td>";
			$table .="<td style='width: 85px; word-wrap: break-word;'><a href='$items->url'>Ir a artículo $items->sku</a></td>";
			$table .="<td>$items->update_date</td>";
			$table .="<td><i class='fa fa-trash' aria-hidden='true' onclick='delete_item($items->id)' style='cursor:point;'></i></td>";
			$table .= "</tr>";
			$i++;
		}
		echo json_encode(array('result'=>$table));
		break;
		case 'counter':
		$sql = "SELECT COUNT(*) FROM aws_items WHERE is_prime='0';";
		$result = pg_fetch_object(pg_query($sql));
		$counter = $result->count;
		$letter = "";
		if ($counter > 1000) {
			$counter = $counter/1000;
			$letter = "K";
		}
		if ($counter > 1000000) {
			$counter = $counter/1000000;
			$letter = "M";
		}
		echo json_encode(array('amount' => $counter, 'letter'=>$letter));
		break;
	}
	break;	    	

	case 'get_meli_items':
	$sql;
	switch ($_POST['type']) {
		case 'detail':
		$sql = "SELECT mpid, sku, title, price, permalink, update_date FROM meli_sku_detail";
		$source = pg_query($sql);
		$table = "";
		$i = 1;
		while ($items = pg_fetch_object($source)) {
			$table .="<tr>";
			$table .="<td>$i</td>";
			$table .="<td>$items->mpid</td>";
			$table .="<td>$items->sku</td>";
			$table .="<td style='width: 450px; word-wrap: break-word;'>$items->title</td>";
			$table .="<td>$ $items->price COP</td>";
			$table .="<td style='width: 85px; word-wrap: break-word;'><a href='$items->permalink'>Ir a artículo $items->mpid</a></td>";
			$table .="<td>$items->update_date</td>";
			$table .= "</tr>";
			$i++;
		}
		echo json_encode(array('result'=>$table));
		break;
		case 'counter':
		$sql = "SELECT COUNT(*) FROM meli_items;";
		$result = pg_fetch_object(pg_query($sql));
		$counter = $result->count;
		$letter = "";
		if ($counter > 1000) {
			$counter = $counter/1000;
			$letter = "K";
		}
		if ($counter > 1000000) {
			$counter = $counter/1000000;
			$letter = "M";
		}
		echo json_encode(array('amount' => $counter, 'letter'=>$letter));
		break;
	}

	break;
	case 'get_usd_price':
	$result = pg_fetch_object(pg_query("SELECT price_cop FROM meli_price WHERE shop_id = '1';"));
	echo json_encode(array('result'=>$result->price_cop));
	break;

	case 'update_usd_price':
	$price = $_POST['dollar_price'];
	$sql = "UPDATE meli_price SET price_cop = '$price' WHERE shop_id = '1';";
	$result = pg_query($sql);
	if ($result > 0) {
		echo json_encode(array('result'=>1));
	}else{
		echo json_encode(array('result'=>0));		
	}
	break;

	case 'get_revenue':
	$result = pg_fetch_object(pg_query("SELECT revenue FROM meli_price WHERE shop_id = '1';"));
	echo json_encode(array('result'=>$result->revenue));
	break;
	case 'update_revenue':
	$revenue = $_POST['revenue'];
	$sql = "UPDATE meli_price SET revenue = '$revenue' WHERE shop_id = '1';";
	$result = pg_query($sql);
	if ($result > 0) {
		echo json_encode(array('result'=>1));
	}else{
		echo json_encode(array('result'=>0));		
	}
	break;

	case 'get_description':
	$result = pg_fetch_object(pg_query("SELECT * FROM system_meli_description;"));
	echo json_encode(array('product_description_dt' => $result->delivery_time,
	                       'product_description_ai' => $result->additional_information,
	                       'product_description_dd' => $result->delivery_details,
	                       'product_description_rp' => $result->retract_policity
	               ));
	break;
	case 'update_description':
	$product_description_dt = $_POST['product_description_dt'];
	$product_description_ai = $_POST['product_description_ai'];
	$product_description_dd = $_POST['product_description_dd'];
	$product_description_rp = $_POST['product_description_rp'];
	$sql = "UPDATE system_meli_description SET  delivery_time = '$product_description_dt', additional_information ='$product_description_ai',  delivery_details = '$product_description_dd',  retract_policity = '$product_description_rp';";
	$result = pg_query($sql);
	if ($result > 0) {
		echo json_encode(array('result'=>1));
	}else{
		echo json_encode(array('result'=>0));		
	}

	break;
	case 'get_warranty':
	$result = pg_fetch_object(pg_query("SELECT warranty FROM system_meli_warranty;"));
	echo json_encode(array('result'=>$result->warranty));
	break;
	case 'update_warranty':
	$warranty = $_POST['product_warrant'];
	$sql = "UPDATE system_meli_warranty SET warranty='$warranty'";
	$result = pg_query($sql);
	if ($result > 0) {
		echo json_encode(array('result'=>1));
	}else{
		echo json_encode(array('result'=>0));		
	}
	break;
    
	case 'get_message':
	$result = pg_fetch_object(pg_query("SELECT body_msg FROM system_msg_meli_order;"));
	echo json_encode(array('message_body' => $result->body_msg));
	break;

	case 'update_message':
	$message_body = $_POST['message_body'];
	$sql = "UPDATE system_msg_meli_order SET body_msg ='$message_body';";
	$result = pg_query($sql);
	if ($result > 0) {
		echo json_encode(array('result'=>1));
	}else{
		echo json_encode(array('result'=>0));		
	}
	break;
	case 'get_key_words':
	$result = pg_fetch_object(pg_query('SELECT key_words FROM system_aws_key_words'));
	echo json_encode(array('key_words' => explode(",", $result->key_words)));
	break;
	case 'update_key_words':
	$key_words = $_POST['key_words'];
	$result = pg_query("UPDATE system_aws_key_words SET key_words = '$key_words';");
	if ($result > 0) {
		echo json_encode(array('result'=>1));
	}else{
		echo json_encode(array('result'=>0));		
	}
	break;
	case 'login':
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	$sql = "SELECT name FROM system_users WHERE  user_name = '$user' AND password ='$pass';";
	$result = pg_fetch_object(pg_query($sql));
	if (isset($result->name)) {
		echo json_encode(array('result'=>1,'token'=>rand(10,99)));
	}else{
		echo json_encode(array('result'=>0));		
	}
	break;
	case 'create_user':
	$name = $_POST['name'];
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	$sql = "INSERT INTO system_users (name, user_name, password) VALUES ('$name','$user','$pass')";
	$result = pg_query($sql);
	if ($result > 0) {
		echo json_encode(array('result'=>1));
	}else{
		echo json_encode(array('result'=>0));		
	}
	break;
	case 'delete_user':
	$id = $_POST['id'];
	$sql = "DELETE FROM system_users WHERE id = $id";
	$result = pg_query($sql);
	if ($result > 0) {
		echo json_encode(array('result'=>1));
	}else{
		echo json_encode(array('result'=>0));		
	}
	break;
	case 'get_user':
	$sql = "SELECT id, name, user_name FROM system_users";
	$source = pg_query($sql);	    
	$table = "";
	$i = 1;
	while ($user = pg_fetch_object($source)) {
		$table += "<tr>";
		$table += "<td>$i</td>";
		$table += "<td>$user->name</td>";
		$table += "<td>$user->user_name</td>";
		$table += "<td><a onclick='delete_user('$user->id')'><i class='fa fa-trash' aria-hidden='true'></i></a></td>";
		$table += "<tr>";
		$i++;
	}
	echo json_encode(array('result'=>$table));
	break;
	case 'delete_item':
		$item = $_POST['item'];
		$sql = "DELETE FROM aws_items WHERE id = $item";
		$result = pg_query($sql);
		if ($result > 0) {
			echo json_encode(array('result'=>1));
		}else{
			echo json_encode(array('result'=>0));		
		}
		break;
}
