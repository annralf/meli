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
			$category_name      = explode(",", trim($category_name));
			$total_aws_category = count($category_name);				
			$percent            = 0;
			$sql_padre = "SELECT id, padre, definition FROM meli_category_master WHERE padre = '0';";
			$result = pg_query($sql_padre);
			$match_category_padre = 0;
			$last_category_padre = 0;
			$meli_padre_id = "";
			$category_id_final;
			foreach ($category_name as $key) {
				while ($category_padre = pg_fetch_object($result)) {
					similar_text($category_padre->definition, $key, $last_category_padre);
					if ($last_category_padre > $match_category_padre) {
						$match_category_padre = $last_category_padre;
						$meli_padre_id    = $category_padre->id;
					}
				}
			}
			$sql = "SELECT COUNT(*) FROM meli_category_master WHERE padre = '$meli_padre_id';";
			$count_category = pg_fetch_array(pg_query($sql));
			if ($count_category['count'] > 1) {
				$root = $this->leaf_category($meli_padre_id, $total_aws_category, $category_name);
			} else {
				$root = $meli_padre_id;
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
					$category_id_final = $category_id;
				}else{
					$category_id_final = $category_id;
				}
			}else{
				$category_id_final = $category_id;
			}
			$category_info = $this->validateCategory($category_id_final);
			$shipping_mode = (array_search('me2', $category_info->settings->shipping_modes)) ? array_search('me2', $category_info->settings->shipping_modes) : array_search('custom', $category_info->settings->shipping_modes);
			$buying_mode = (array_search('buy_it_now', $category_info->settings->buying_modes)) ? array_search('buy_it_now', $category_info->settings->buying_modes): array_search('classified', $category_info->settings->buying_modes);
			$currency = array_search('COP', $category_info->settings->currencies);
			$category_info = array(
			    'category_id' => $category_info->id,
			    'buying_mode' => $category_info->settings->buying_modes[$buying_mode],
			    'shipping_mode' => $category_info->settings->shipping_modes[$shipping_mode],
			    'currency' => $category_info->settings->currencies[$currency],
			    'domain' => $category_info->settings->vip_subdomain
			);
			return $category_info;
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
			$validation_url = "https://api.mercadolibre.com/items/validate?access_token=".$this->shop_detail->access_token;
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
		public function newItem(){
			$this->connect;
			$id = $this->shop_detail->id;
			$sql = "SELECT * FROM meli_item_detail WHERE shop_id = $id LIMIT 1;";
			$result = pg_query($sql);
			$delivery_time = "TIEMPOS DE ENTREGA";
			$delivery_time .= "\n";
			$delivery_time .= "DE 10 A 15 DIAS HABILES";
			$delivery_time .= "\n";
			$delivery_time .= "&#9620;&#9620;&#9620;&#9620;&#9620;&#9620;&#9620;&#9620;&#9620;&#9620;&#9620;&#9620;&#9620;";
			$delivery_time .= "\n";
			while ($item = pg_fetch_object($result)) {
				$category_info = $this->search_category($item->category_id);
				$images      = explode("~^~", $item->pictures);
				$pictures = array();
				$i = 0;
				while ($i < count($images) && $i < 8) {
				    array_push($pictures, array('source' => $images[$i]));
				    $i++;					
				}
				$shipping = array();
				if($category_info['shipping_mode'] == 'me2'){
				    $shipping = array('mode'    => 'me2', 
						      'local_pick_up'    => false, 
						      'free_shipping'    => true ,
						      'free_methods' => array(),
						      'tags' => array('mandatory_free_shipping'));
				}else{
				    $shipping = array('mode'    => 'custom', 
						      'local_pick_up'    => false, 
						      'free_shipping'    => false , 
						      'costs' => array('description' => 'Pagar el Envío en mi Domicilio', 
						      'cost' => 1));
				}
				$avaliable_quantity = ($item->avaliable_quantity == 0) ? 3:$item->avaliable_quantity;
				$new_item = array(
					'title' => $item->title,
					'category_id' => $category_info['category_id'],
					'domain_id' => $category_info['domain'],
					'price' => $this->set_price($item->weight,$item->price),
					'currency_id' => $category_info['currency'],
					'available_quantity' => $avaliable_quantity,
					'buying_mode' => $category_info['buying_mode'],
					'listing_type_id' => 'gold_special',
					'condition' => 'new',
					'description' => $delivery_time.$item->complementary_description,
					'warranty' => $item->warranty,
					'pictures' => $pictures,
					'seller_custom_field' => $item->sku,
					'shipping' => $shipping
				);
				print_r($this->validate($new_item));
				
			}

		}
		public function updateItem(){
			$this->connect;
			$id = $this->shop_detail->id;
			$sql = "SELECT price, avaliable_quantity FROM meli_item_detail WHERE shop_id = $id;";
			$result = pg_query($sql);
			while ($item = pg_fetch_object($result)) {
				$new_item = array(
					'price' => $this->set_price($item->weight,$item->price),
					'available_quantity' => $item->avaliable_quantity,
				);
				print_r($new_item);
			}

		}


	}

	$t = new Meli(1);
	#echo $t->set_price(34,1049.99);
	$category = " Productos de oficina, categorías, artículos escolares y de oficina, accesorios de escritorio y organizadores del área de trabajo, alfombrillas para ratón y reposamuñecas, alfombrillas para ratón                                                       ";
	#$category_id = $t->search_category($category);
	#print_r($category_id);
	$t->newItem();