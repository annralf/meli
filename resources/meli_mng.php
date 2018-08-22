<?php
require_once 'db_mng.php';
require_once 'scratch_mng.php';

/**
 * 
 */
class Meli
{
	private $connect; 
	public $shop_detail;
	public $scratch;

	function __construct($shop_id)
	{
		$this->connect = new Connect();
		$sql = "SELECT * FROM meli_detail WHERE id = $shop_id";
		$this->shop_detail = pg_fetch_object(pg_query($sql));
		$this->scratch    = new Amazon();

	}
	#categories Check
	public function leaf_category($category_id, $total_aws_category, $category_name) {
		$percent        = 0;
		$match_category = 0;
		$sql		= "SELECT * FROM meli_category_master WHERE padre ='$category_id';";
		$query_category_result = pg_query($sql);
		if ($total_aws_category == 1) {
			while ($category = pg_fetch_array($query_category_result)) {
				similar_text($category_name[0], htmlspecialchars_decode($category['definition']), $percent);
				if ($percent > $match_category) {
					$match_category = $percent;
					$category_id    = $category['id'];
				}
			}
			return $category_id;
		}else{
			while ($category = pg_fetch_array($query_category_result)) {
				for ($i = 0; $i < $total_aws_category; $i++) {
					similar_text($category_name[$i], htmlspecialchars_decode($category['definition']), $percent);
					if ($percent > $match_category) {
						$match_category = $percent;
						$category_id    = $category['id'];
					}
				}
			}}
			$sql_counter = "SELECT COUNT(*) FROM meli_category_master WHERE padre = '$category_id';";
			$count_category = pg_fetch_array(pg_query($sql_counter));
			if ($count_category['count'] > 3) {
				return $this->leaf_category($category_id, $total_aws_category, $category_name);

			} else {
				return $category_id;
			}
		}
		public function search_category($category_name) {
			$match_meta_category = 0;
			$match_sub_category  = 0;
			$match_category      = 0;
			$percent             = 0;	
			$url_translate = "https://translate.google.com/?hl=&langpair=en|es&text=$category_name";
			$resultSearch = $this->scratch->crawler_translate($url_translate);
			print_r($resultSearch);die();
			if ($resultSearch['notavaliable'] !== 1) {
				$title_es       = $resultSearch['title'];
				$description_es = $resultSearch['description'];
			}else{
				$title_es       = "N/T";
				$description_es = "N/T";
			}
			$category_name      = explode(",", $category_name);
			$total_aws_category = count($category_name);				
			$percent            = 0;
			$sql_padre = "SELECT padre, definition FROM meli_category_master WHERE padre = '0';";
			$result = pg_query($sql_padre);
			$match_category_padre = 0;
			$last_category_padre = 0;
			$meli_padre_id = "";
			foreach ($category_name as $key) {
				while ($category_padre = pg_fetch_object($result)) {
					similar_text($category_padre->definition, $key, $last_category_padre);
					if ($last_category_padre > $match_category_padre) {
						$match_category_padre = $last_category_padre;
						$meli_padre_id    = $category_padre->padre;
					}
				}
			}
			echo $meli_padre_id;die();
			#print_r($this->validateCategory($meli_padre_id));die();
			$sql = "SELECT COUNT(*) FROM meli_category_master WHERE padre = '$meli_padre_id';";
			$count_category = pg_fetch_array(pg_query($sql));
			if ($count_category['count'] > 1) {
				$root = $this->leaf_category($meli_padre_id, $total_aws_category, $category_name);
			} else {
				$root = $sub_category_id;
			}
			$category_id = $this->leaf_category($meli_padre_id, $total_aws_category, $category_name);
			$root = $this->validateCategory($category_id);
			$last = 0;

			if(isset($root->children_categories)){
				if (count($root->children_categories) > 0) {
					foreach ($root->children_categories as $key => $value) {
						similar_text($root->name, $value->name, $last);
						if ($last > $match_category) {
							$match_category = $last;
							$category_id    = $value->id;
						}
					}
					$child = $this->validateCategory($category_id);
					if (count($child->children_categories) > 0) {
						foreach ($child->children_categories as $key_ => $value_) {
							if ($value_->name == "Otros") {
								$category_id = $value_->id;
							}else{
								$category_id = $value_->id;							
							}
						}
					}
					$last = 0;
					$match_category = 0;
					$last_ = $this->validateCategory($category_id);
					if (count($last_->children_categories) > 0) {
						foreach ($last_->children_categories as $key_ => $valueL) {
							similar_text("Otr", $valueL->name, $last);
							if ($last > $match_category) {
								$match_category = $last;
								$category_id    = $valueL->id;
							}
						}
					}
					return $category_id;
				}else{
					return $category_id;
				}
			}else{
				return $category_id;
			}
		}

		public function set_price($weight,$base_price){
			$final_price = 0;
			$range_1 = 500;
			$range_2 = 1000;
			$range_3 = 2000;
			$range_4 = 4000;
			$pounds_value = 453.592;
			$weight = $weight*$pounds_value;
			$this->connect;
			$sql = "SELECT weight, price FROM shop_feeds;";
			$result = pg_query($sql);
			$match_weight = 0;
			$feed_price = 0;
	    #Range 1 weight from 0 to 500
			if ($weight > 0 && $weight <= $range_1) {
				$weight += $this->shop_detail->range_1;
			}
	    #Range 2 weight from 501 to 1000
			if ($weight > $range_1 && $weight <= $range_2) {
				$weight += $this->shop_detail->range_2;
			}
	    #Range 3 weight from 1001 to 2000
			if ($weight > $range_2 && $weight <= $range_3) {
				$weight += $this->shop_detail->range_3;
			}
	    #Range 4 weight from 2001 to 4000
			if ($weight > $range_4) {
				$weight += $this->shop_detail->range_4;
			}
			while ($feed = pg_fetch_object($result)) {
				if ($weight > $feed->weight && $match_weight < $feed->weight) {
					$match_weight = $feed->weight;
					$feed_price = $feed->price;
				}
			}
	    #final price 
			$sub_final_price = ceil(($base_price + $feed_price)*1.10);
			$final_price = ceil($sub_final_price*$this->shop_detail->price_cop);
			return $final_price;
		}

		public function validateCategory($category_id) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.mercadolibre.com/categories/'.$category_id);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

			$validation = json_decode(curl_exec($ch));
			curl_close($ch);

			return $validation;
		}

		public function validateCategory_by_user($category_id) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.mercadolibre.com/users/'.$this->user_name.'/shipping_modes?category_id='.$category_id);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

			$validation = json_decode(curl_exec($ch));
			curl_close($ch);

			return $validation;
		}

		public function validate($item) {
			$validation_url = "https://api.mercadolibre.com/items/validate?access_token=".$this->access_token;
			$ch             = curl_init();
			curl_setopt($ch, CURLOPT_URL, $validation_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($item));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

			$validation = json_decode(curl_exec($ch));
			curl_close($ch);
			return $validation;
		}
		public function update($item_id, $item) {
			$update_url = "https://api.mercadolibre.com/items/".$item_id."?access_token=".$this->access_token;
			$ch         = curl_init();
			$item       = json_encode($item);
			curl_setopt($ch, CURLOPT_URL, $update_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $item);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
			$update = json_decode(curl_exec($ch));
			curl_close($ch);
			return $update;
		}

		public function relist($item_id, $item) {
			$update_url = "https://api.mercadolibre.com/items/".$item_id."/relist?access_token=".$this->access_token;
			$ch         = curl_init();
			$item       = json_encode($item);
			curl_setopt($ch, CURLOPT_URL, $update_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $item);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
			$update = json_decode(curl_exec($ch));
			curl_close($ch);
			return $update;
		}
		public function create($item) {
			$show_url = "https://api.mercadolibre.com/items?access_token=".$this->access_token;
			$ch       = curl_init();
			curl_setopt($ch, CURLOPT_URL, $show_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($item));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$show = json_decode(curl_exec($ch));
			curl_close($ch);
			return $show;
		}

		public function show($item) {
			$show_url = "https://api.mercadolibre.com/items?ids=".$item."?access_token=".$this->access_token;
			$ch       = curl_init();
			curl_setopt($ch, CURLOPT_URL, $show_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
			$show = json_decode(curl_exec($ch));
			curl_close($ch);
			return $show;
		}
		/*public function newItem(){
			$this->connect;
			$id = $this->shop_detail->id;
			$sql = "SELECT * FROM meli_item_detail WHERE shop_id = $id;";
			$result = pg_query($sql);
			while ($item = pg_fetch_object($result)) {
				$new_item = array(
					'title' => $item->title,
					'category_id' => $this->
				);
			}
		}*/


	}

	$t = new Meli(1);
	#echo $t->set_price(34,1049.99);
	$category = "Departments";
	$category_id = $t->search_category($category);
	print_r($t->validateCategory($category_id));