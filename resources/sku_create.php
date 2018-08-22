<?php
include 'scratch_mng.php';
require_once 'db_mng.php';

$conn    = new Connect();
$k	 = 1;
$key     ="Samsung,AMD,Intel,Adata,Asus,Corsair,Crucial,Dell,EVGA,Gigabyte,Kingston,Lenovo,MSI,Micron,SanDisk,Nzxt,Seagate,RAZER,Google,Toshiba,Steelseries,Motorola,Sony,HP,Elegoo,Aukey,Havit,Redragon,Hcman,Wester Digital,Vengance,HyperX Kingston";
$j       = 1;

$searchKey =explode(",", $key);
$skus_list = "";
$sku_counter = 1;
$scratch = new Amazon();

function newSearch($type, $url){
    $scratch = new Amazon();
    $result  = $scratch->crawler_create($url,$type);
    global $skus_list, $sku_counter;
    if ($result['notavaliable'] == 0) {
    	$sku = explode(",", $result['skus']);
	foreach ($sku as $key) {
		$skus_list .= "('$key'),";
		$sku_counter++;
	}
    }
    if (isset($result['pages'])) {
    	return strtoupper($result['pages']);
    }else{
	return 0;
    }
}

foreach ($searchKey as $keywords) {
	$keywords = trim($keywords);
	$url     = "https://www.amazon.com/s/gp/search/ref=sr_nr_p_85_0?fst=as%3Aoff&rh=i%3Aaps%2Ck%3A$keywords%2Cp_76%3A2661625011%2Cp_85%3A2470955011&sort=price-desc-rank&keywords=$keywords&ie=UTF8&qid=1519921342&rnid=2470954011";
	$result = newSearch(1,$url);
	if ($result > 0) {
		for ($i=2; $i < $result; $i++) { 
			$url = "https://www.amazon.com/s/ref=sr_pg_$i?fst=as%3Aoff&rh=i%3Aaps%2Ck%3A$keywords%2Cp_76%3A2661625011%2Cp_85%3A2470955011%2Cp_n_condition-type%3A6461716011&page=$i&sort=price-desc-rank&keywords=$keywords&ie=UTF8&qid=1523033887";
			newSearch(2,$url);
		}
	}
}
$skus_list  = substr($skus_list,0,-1);
$sql_str    = "INSERT INTO aws_sku (sku) VALUES $skus_list";
$sql_result = pg_query($sql_str);
if ($sql_result > 0) {
	echo "Was Ok Total Insert $sku_counter";
}else{
    echo "Was Wrong";
}