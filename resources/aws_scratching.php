<?php
include 'scratch_mng.php';
require_once 'db_mng.php';

/**
 * 
 */
class awsitem 
{
	private $type;
	private $conn;
	private $scratch;
	private $commit;
	#1 means creations
	#2 means update
	#3 means translation
	function __construct($type)
	{
		$this->conn = new Connect();
		$this->scratch = new Amazon();
		$this->type = $type;
		$this->commit = "";
		switch ($this->type) {
			case 1:
				$this->insert_item();
				break;			
			case 2:
				$this->update_item();
				break;
			case 3:
				$this->translate_item();
				break;
		}
	}

    public function insert_item(){
	$sql = "SELECT sku FROM aws_sku  WHERE sku NOT IN (SELECT sku FROM aws_items)";
	$result = pg_query($sql);
	$i = 0;
	while ($item = pg_fetch_object($result)) {
		$url = "https://www.amazon.com/dp/$item->sku";
		$resultSearch = $this->scratch->crawler($url,$item->sku);
		if ($resultSearch['notavaliable'] == 1 || !isset($resultSearch['notavaliable'])) {
			$this->commit .= "UPDATE aws_sku SET active = 'false' WHERE sku = '$item->sku';";
			echo $i."-Not Avaliable".$item->sku."\n";	    

		}else{
				$sku		       =     $resultSearch['sku'];
				$product_typ	       =     $resultSearch['product_type'];
				$product_category      =     $resultSearch['product_category'];
				$product_title_english =     $resultSearch['product_title_english'];
				$specification_english =     $resultSearch['specification_english'];
				$brand		       =     $resultSearch['brand'];
				$model		       =     $resultSearch['model'];
				$image_url	       =     $resultSearch['image_url'];
				$sale_price	       =     $resultSearch['sale_price'];
				$quantity	       =     $resultSearch['quantity'];
			        $weight_unit           =     $resultSearch['weight_unit'];
				$dimension_unit        =     $resultSearch['dimension_unit'];
				$package_width         =     $resultSearch['package_width'];
				$package_height        =     $resultSearch['package_height'];
			        $package_length        =     $resultSearch['package_length'];
				$is_prime              =     $resultSearch['is_prime'];
			        $item_height           =     $resultSearch['item_height'];
				$item_length           =     $resultSearch['item_length'];
				$item_width	       =     $resultSearch['item_width'];
				$image_url             =     $resultSearch['url'];
			$this->commit .= "INSERT INTO aws_items (sku, product_type, product_category, product_title_english, specification_english, brand, model, image_url, sale_price, quantity, weight_unit, dimension_unit, package_width, package_height, package_length, is_prime, item_height, item_length, item_width, url) 
			VALUES ('$sku', '$product_type','$product_category', '$product_title_english', '$specification_english', '$brand', '$model', '$image_url', '$sale_price', '$quantity', '$weight_unit', '$dimension_unit', '$package_width', '$package_height', '$package_length', '$is_prime', '$item_height', '$item_length', '$item_width', '$url')";	
		    echo $i."-".$sku."\n";	    
		}
		  $i++;
	}
	pg_query($this->commit);
    }

    public function update_item(){
	$sql = "SELECT sku FROM aws_items";
	$result = pg_query($sql);
	while ($item = pg_fetch_object($result)) {
		$url = "https://www.amazon.com/dp/$item->sku";
		$resultSearch = $this->scratch->crawler($url,$item->sku);
		if ($resultSearch['notavaliable'] == 1 || !isset($resultSearch['notavaliable'])) {
			$this->commit .= "UPDATE aws_items SET active = 'false' WHERE sku = '$item->sku';";
		}else{
			$quantity   = $resultSearch['quantity'];
			$sale_price = $resultSearch['sale_price'];
			$this->commit .= "UPDATE aws_items SET active = 'true', quantity = '$quantity', sale_price = '$sale_price' WHERE sku = '$item->sku' WHERE sku = '$item->sku';";
		}
	}
	pg_query($this->commit);
    }

    public function translate_item(){
	$sql = "SELECT sku, product_title_english, specification_english FROM aws_items WHERE NOT IN (SELECT sku FROM aws_translations);";
	$result = pg_query($sql);
	while ($item = pg_fetch_object($result)) {
		$specification_english = urlencode(substr($item->specification_english,0,4600));
		$product_title_english = substr($result['product_title_english'],0,100);						 
		$url = "https://translate.google.com/?hl=&langpair=en|es&text=$specification_english~~~^~~~$product_title_english";
		$resultSearch = $this->scratch->crawler_translate($url);
		if ($resultSearch['notavaliable'] !== 1) {
			$title       = $resultSearch['title'];
			$description = $resultSearch['description'];
			$this->commit .= "INSERT INTO aws_translations (title, description) VALUES ('$title','$description');";
		}
	}
	pg_query($this->commit);
    }
}
$t = new awsitem(1);
