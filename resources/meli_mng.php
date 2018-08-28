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
			if (isset($category_info->settings)) {
			    $shipping_mode = (array_search('me2', $category_info->settings->shipping_modes)) ? array_search('me2', $category_info->settings->shipping_modes) : array_search('custom', $category_info->settings->shipping_modes);
			    $buying_mode = (array_search('buy_it_now', $category_info->settings->buying_modes)) ? array_search('buy_it_now', $category_info->settings->buying_modes): array_search('classified', $category_info->settings->buying_modes);
			    $currency = array_search('COP', $category_info->settings->currencies);
			    $category_info = array(
				    'category_id' => $category_info->id,
				    'buying_mode' => $category_info->settings->buying_modes[$buying_mode],
				    'shipping_mode' => $category_info->settings->shipping_modes[$shipping_mode],
				    'currency' => $category_info->settings->currencies[$currency],
				    'domain' => $category_info->settings->vip_subdomain,
				    'max_title_length' => $category_info->settings->max_title_length,
				    'max_description_length' => $category_info->settings->max_description_length
			    );
			}else{
			    $category_info = array(
				    'category_id' => $category_info->id,
				    'buying_mode' => 'buy_it_now',
				    'shipping_mode' => 'me2',
				    'currency' => 'COP',
				    'domain' => 'articulo',
				    'max_title_length' => 60,
				    'max_description_length' => 400
				);
			}
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
			$update_url = "https://api.mercadolibre.com/items/".$item_id."?access_token=".$this->shop_detail->access_token;
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
			$update_url = "https://api.mercadolibre.com/items/".$item_id."/relist?access_token=".$this->shop_detail->access_token;
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
			$show_url = "https://api.mercadolibre.com/items?access_token=".$this->shop_detail->access_token;
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

		public function banner($item_id, $item) {
			$update_url = "https://api.mercadolibre.com/items/".$item_id."/description?access_token=".$this->shop_detail->access_token;
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

		public function show($item) {
			$show_url = "https://api.mercadolibre.com/items?ids=".$item."?access_token=".$this->shop_detail->access_token;
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
			$sql = "select * from meli_item_detail where id not in (select aws_id from meli_items where shop_id = $id) limit 100";
			$result = pg_query($sql);
			$description_title = "DESCRIPCION DEL PRODUCTO";
			$description_title .= "\n";
			$delivery_time  = "\n";
			$delivery_time .= "\n";
			$delivery_time .= "TIEMPOS DE ENTREGA";
			$delivery_time .= "\n";
			$delivery_time .= "• DE 10 A 15 DIAS HABILES";
			$delivery_time .= "\n";
			$delivery_time .= "\n";
			$complementary_description = "ESTE ES UN ARTICULO IMPORTADO DESDE JAPON Ó USA";
			$complementary_description .= "\n";
			$complementary_description .= "\n";
			$complementary_description .="MÉTODOS DE ENVÍO";
			$complementary_description .= "\n";
			$complementary_description .= "Nuestros envíos son totalmente gratis y cubren todo el territorio nacional de Colombia a través del correo 4-72";
			$complementary_description .= "\n";
			$complementary_description .= "\n";
			$complementary_description .= "EN CASO DE RETRACTO";
			$complementary_description .= "\n";
			$complementary_description .= "• Se puede realizar la devolución del producto en un periodo máximo de 5 días hábiles a partir de la entrega.";
			$complementary_description .= "\n";
			$complementary_description .= "• Los costos de retorno hacia los Estados Unidos son asumidos por el COMPRADOR, este varía de acuerdo con el peso y/o volumen del producto y no es reembolsable.";
			$k = 1;
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
					$costos = array();
					array_push($costos, array('description' => 'Pagar el Envío en mi Domicilio', 'cost' => 1));
					$shipping = array('mode'    => 'custom', 
					                  'local_pick_up'    => false, 
					                  'free_shipping'    => false , 
					                  'costs' => $costos);
				}
				$avaliable_quantity = ($item->avaliable_quantity == 0) ? 3:$item->avaliable_quantity;
				$title = $this->scratch->change_simbols($item->title);
				$description = str_replace(".-", "\n", $this->scratch->change_simbols($item->description));
				$complementary_description = $delivery_time.$complementary_description;
				$length = ($category_info['max_description_length'] - strlen($complementary_description)) -1; 
				$length_title = $category_info['max_title_length'] -1;
				if (strlen($title) >= $length_title) {
					$pos   = strrpos($title,' ', $length_title);
					if ($pos > $length_title) {
					    $pos -= $pos - $length_title;
					}
					$title = substr($title, 0, $pos);
				}
				if (strlen($description) >= $length) {
					$pos   = strpos($description, ' ', $length);
					$description = substr($description, 0, $pos);
				}
				$description = $description_title.$description.$complementary_description;
				$new_item = array(
					'title' => $title,
					'category_id' => $category_info['category_id'],
					'domain_id' => $category_info['domain'],
					'price' => $this->set_price($item->weight,$item->price),
					'currency_id' => $category_info['currency'],
					'available_quantity' => $avaliable_quantity,
					'buying_mode' => $category_info['buying_mode'],
					'listing_type_id' => 'gold_special',
					'condition' => 'new',
					'description' => array('plain_text' => $description),
					'warranty' => $item->warranty,
					'pictures' => $pictures,
					'seller_custom_field' => $item->sku,
					'shipping' => $shipping
				);
				$validation = $this->validate($new_item);
				if(!is_null($validation)){
					echo "$k - item no created wrong validation\n";
				}else{
					$meli_item = $this->create($new_item);
					if (isset($meli_item->id)) {
						$mpid = $meli_item->id;
						$title = $meli_item->title;
						$seller_id = $meli_item->seller_id;
						$category_id = $meli_item->category_id;
						$price = $meli_item->price;
						$base_price = $meli_item->base_price;
						$sold_quantity = $meli_item->sold_quantity;	     
						$start_time = $meli_item->start_time;
						$stop_time = $meli_item->stop_time;
						$permalink = $meli_item->permalink;
						$status = $meli_item->status;
						$aws_id = $item->id;
						$automatic_relist = $meli_item->automatic_relist;
						$date_created = $meli_item->date_created;
						$last_updated = $meli_item->last_updated;
						$shop_id = $this->shop_detail->id;
						$create_date = date('Y-m-d h:i:s');
						$update_date = date('Y-m-d h:i:s');
						$sql = "INSERT INTO meli_items(
						mpid, title, seller_id, category_id, price, base_price, sold_quantity, 
						start_time, stop_time, permalink, status, 
						aws_id, automatic_relist, date_created, last_updated, shop_id, 
						create_date, update_date) VALUES ('$mpid', '$title', '$seller_id', '$category_id', '$price', '$base_price', '$sold_quantity', '$start_time', '$stop_time', '$permalink', '$status', '$aws_id', '$automatic_relist', '$date_created', '$last_updated', '$shop_id', '$create_date', '$update_date');";
						$result = pg_query($sql);
						if ($result > 0) {
							echo "$k - item $mpid create at $create_date\n";
						}else{
							echo "$k - item $mpid not create at DB\n";					    
						}
					}else{
						echo "$k - item no created\n";
					}

				}
				$k++;
			}
		}
		public function updateItem(){
			$this->connect;
			$id = $this->shop_detail->id;
			$sql = "SELECT * FROM meli_item_update WHERE shop_id = '$id';";
			$result = pg_query($sql);
			$description_title = "DESCRIPCION DEL PRODUCTO";
			$description_title .= "\n";
			$delivery_time  = "\n";
			$delivery_time .= "\n";
			$delivery_time .= "TIEMPOS DE ENTREGA";
			$delivery_time .= "\n";
			$delivery_time .= "• DE 10 A 15 DIAS HABILES";
			$delivery_time .= "\n";
			$delivery_time .= "\n";
			$complementary_description = "ESTE ES UN ARTICULO IMPORTADO DESDE JAPON Ó USA";
			$complementary_description .= "\n";
			$complementary_description .= "\n";
			$complementary_description .="MÉTODOS DE ENVÍO";
			$complementary_description .= "\n";
			$complementary_description .= "Nuestros envíos son totalmente gratis y cubren todo el territorio nacional de Colombia a través del correo 4-72";
			$complementary_description .= "\n";
			$complementary_description .= "\n";
			$complementary_description .= "EN CASO DE RETRACTO";
			$complementary_description .= "\n";
			$complementary_description .= "• Se puede realizar la devolución del producto en un periodo máximo de 5 días hábiles a partir de la entrega.";
			$complementary_description .= "\n";
			$complementary_description .= "• Los costos de retorno hacia los Estados Unidos son asumidos por el COMPRADOR, este varía de acuerdo con el peso y/o volumen del producto y no es reembolsable.";

			while ($item = pg_fetch_object($result)) {
				$category_info = $this->search_category($item->category_id);
				$avaliable_quantity = ($item->avaliable_quantity == 0) ? 3:$item->avaliable_quantity;
				$title = $this->scratch->change_simbols($item->title);
				$description = str_replace(".-", "\n", $this->scratch->change_simbols($item->description));
				$complementary_description = $delivery_time.$complementary_description;
				$length = ($category_info['max_description_length'] - (strlen($complementary_description) + strlen($description_title))) -1; 
				$length_title = $category_info['max_title_length'] -1;
				if (strlen($title) >= $length_title) {
					$pos   = strpos($title,' ', $length_title);
					$title = substr($title, 0, $pos);
				}
				if (strlen($description) >= $length) {
					$pos   = strpos($description, ' ', $length);
					$description = substr($description, 0, $pos);
				}
				$description = $description_title.$description.$complementary_description;
				$update_item = array(
					'title' => $title,
					'price' => $this->set_price($item->weight,$item->price),
					'available_quantity' => $avaliable_quantity
				);
				$update = $this->banner($item->mpid,array('plain_text' => $description));
				print_r($update);
				#print_r($this->update($item->mpid, $update_item));
			}

		}


	}

	$t = new Meli(1);
	#echo $t->set_price(34,1049.99);
	$category = " Productos de oficina, categorías, artículos escolares y de oficina, accesorios de escritorio y organizadores del área de trabajo, alfombrillas para ratón y reposamuñecas, alfombrillas para ratón                                                       ";
	#$category_id = $t->search_category($category);
	$t->newItem();