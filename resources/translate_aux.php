<?php
require_once 'scratch_mng.php';
require_once 'db_mng.php';

$sql = "select id, product_category from  aws_items where product_category_es = ''";
$conn = new Connect();
$trans = new  Amazon();
$result = pg_query($sql);
$i = 0;

while ($item = pg_fetch_object($result)) {
	$resultSearch = pg_escape_string(utf8_encode($trans->translate($item->product_category)));
	$sentence = "update aws_items set product_category_es = '$resultSearch' where id = $item->id";
	$query = pg_query($sentence);
	if ($query > 0) {
	    echo "$i actualizado $item->id\n";
	}else{
	    echo "$i no actualizado $item->id\n";
	}
	$i++;
}