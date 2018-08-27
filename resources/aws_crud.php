<?php
require_once 'db_mng.php';
require_once 'aws_mng.php';
require_once 'scratch_mng.php';

/**
* 
*/
class aws_mng 
{
	private $aws;
	private $connection;
	private $scratch;

	function __construct($key, $secret_key, $tag)
	{
		$this->aws	  = new amazonManager($key, $secret_key, $tag);
		$this->connection = new Connect();
		$this->scratch    = new Amazon();
	}

	function update(){
		$sql = "select upper(sku) as sku from aws_sku order by id desc offset 3670;";
		$result = pg_query($sql);
		$list = array();
		$cant = 0;
		$i = 1;
		while ($sku = pg_fetch_object($result)) {
			$cant = count($list);
			if ($cant < 10) {
				array_push($list, $sku->sku);
			}else{
				$list_str = implode(",",$list);
				$list = array();
				$cant = 0;
			    #-----Search funtion
				$result_aws = $this->aws->search_item("$list_str");
				if(isset($result_aws)){
					foreach ($result_aws as $aws_result) {
						$date = date('Y-m-d h:i:s');
						switch ($aws_result['notavaliable']) {
							case 0:
							case 1:
							$sku = $aws_result['asin'];
							$sql_ = "SELECT id FROM aws_items WHERE sku = '$sku'";
							$val = 0;
							$item = pg_fetch_object(pg_query($sql_));
							if ($item) {
								$sale_price = $aws_result['sale_price'];
								$quantity   = ($aws_result['quantity'] == null) ? $aws_result['quantity']: 0;
								$package_weight   = $aws_result['package_weight'];
								$sql_statement = "UPDATE aws_items SET sale_price = '$sale_price', quantity='$quantity', package_weight='$package_weight', update_date = '$date' WHERE id = $item->id";
							}else{
								$product_type     = pg_escape_string(utf8_encode($aws_result['product_type']));
								$title	    = pg_escape_string(utf8_encode($aws_result['product_title_english']));
								$description      = pg_escape_string(utf8_encode($aws_result['specification_english']));
								$product_category = pg_escape_string(utf8_encode($aws_result['product_category']));
								$product_category_p = pg_escape_string(utf8_encode($aws_result['category_p']));
								$brand	    = pg_escape_string(utf8_encode($aws_result['brand']));
								$department       = pg_escape_string(utf8_encode($aws_result['department']));
								$clothingsize     = pg_escape_string(utf8_encode($aws_result['clothingSize']));
								$color	    = pg_escape_string(utf8_encode($aws_result['color']));
								$model	    = pg_escape_string(utf8_encode($aws_result['model']));
								$ean	      = $aws_result['ean'];
								$image_url	= $aws_result['image_url'];
								$upc	      = $aws_result['UPC'];
								$currency	 = $aws_result['currency'];
								$sale_price       = $aws_result['sale_price'];
								$quantity	 = ($aws_result['quantity'] == null) ? $aws_result['quantity']: 0;
								$condition	= $aws_result['condition'];
								$weight_unit      = $aws_result['weight_unit'];
								$package_weight   = $aws_result['package_weight'];
								$package_width   = $aws_result['package_width'];
								$package_height   = $aws_result['package_height'];
								$package_length   = $aws_result['package_length'];
								$is_prime	 = $aws_result['is_prime'];
								$item_height      = $aws_result['item_height'];
								$item_length      = $aws_result['item_length'];
								$item_width       = $aws_result['item_width'];
								$item_weight       = $aws_result['item_weight'];
								$sku	      = $aws_result['asin'];
								$avaliable	= $aws_result['avaliable'];
								$url	      = $aws_result['url'];
								$sku_padre	= $aws_result['ParentASIN'];
								$description_es   = substr($description,0,4600);
								$text_es = "$title---$description_es---$product_category";
								$resultSearch = $this->scratch->translate($text_es);
								$resultSearch = explode('---', $resultSearch);
								$title_es = pg_escape_string(utf8_encode($resultSearch[0]));
								$description_es = pg_escape_string(utf8_encode($resultSearch[1]));
								$product_category_es = (isset($resultSearch[2])) ? pg_escape_string(utf8_encode($resultSearch[2])) : "";
								$sql_statement = "INSERT INTO aws_items (sku, product_type, ean, product_category, product_title_english,specification_english, brand, model, image_url, upc, currency,sale_price, quantity, condition, weight_unit, package_weight,package_height, package_length, clothingsize, color, department, is_prime, item_height, item_length, item_width, create_date, update_date, avaliable, url, package_width, sku_padre, title_spanish, specification_spanish, product_category_es) VALUES ('$sku', '$product_type', '$ean', '$product_category', '$title', '$description', '$brand', '$model', '$image_url', '$upc', '$currency','$sale_price', '$quantity', '$condition', '$weight_unit', '$package_weight','$package_height', '$package_length', '$clothingsize', '$color', '$department', '$is_prime', '$item_height', '$item_length', '$item_width', '$date', '$date', '$avaliable', '$url', '$package_width', '$sku_padre', '$title_es', '$description_es','$product_category_es')";
								$val = 1;
							}
							break;

							case 2:
							$sql_statement = "UPDATE aws_sku SET active = 'false'";
							$var = 2;
							break;
						}
						$result_query = pg_query($sql_statement);
						switch ($val) {
							case 0:
							case 2:
							$type_ok = "Actualizado con éxito";
							$type_bad = "No se pudo actualizar";
							break;
							case 1:
							$type_ok = "Creado con éxito";
							$type_bad = "No se pudo Insertar";
							break;
						}
						if ($result_query > 0) {
							echo "$i - $sku $type_ok $date\n";
						}else{
							echo "$i - $sku $type_bad $date\n";
						}
						$i++;		
					}
				}
				sleep(10);
			}
		}
	}
}
$key = 'AKIAJFRPRT5KNOUKJILA';
$secret_key ='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';
$tag = 'paocastro90-20';

$t = new aws_mng($key,$secret_key,$tag);
$t->update();
