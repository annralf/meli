<?php
include 'aux_func.php';
#main crawler function
class Amazon {
	private $ch;
	private $url;
	private $response;
	#Contruct class
	function __construct() {

	}

	function get_string_between($string, $start, $end) {
		$string = ' '.$string;
		$ini    = strpos($string, $start);
		if ($ini == 0) {return '';
	}
	$ini += strlen($start);
	$len = strpos($string, $end, $ini)-$ini;
	return substr($string, $ini, $len);
}

function remove_string_between($string, $start, $end) {
	$string = ' '.$string;
	$ini    = strpos($string, $start);

	$len  = strpos($string, $end, $ini)+strlen($end);
	$re   = substr($string, 0, $ini);
	$sult = substr($string, $len, strlen($string));
	return ($re.$sult);
}

public function floatvalue($val) {
	$val = str_replace(",", ".", $val);
	$val = preg_replace('/\.(?=.*\.)/', '', $val);
	return floatval($val);
}

function crawler($url, $sku) {
		#Getting Page code
	$this->url = $url;
	$this->ch  = curl_init();
	curl_setopt($this->ch, CURLOPT_URL, $url);
	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36 OPR/46.0.2597.57");
	curl_setopt($this->ch, CURLOPT_FRESH_CONNECT, true);
	$this->response = curl_exec($this->ch);
	curl_close($this->ch);
	$item_detail = array();
		#
	$title         = get_string_between($this->response, "itle>", "</title>");
	$title_replace = array('Amazon.com :', 'Amazon.com');
	$title         = str_replace($title_replace, "", $title);
	if (strpos($title, "we just need to make sure you're not a robot") > 0) {
		#if (!$type_json) {
		$item_detail['notavaliable'] = 1;
		$item_detail['message'] = "No Connection";
		$item_detail['asin']         = (string) $sku;
		#}
		return $item_detail;
	} else {
		$prime   = preg_match("#<span class='a-color-secondary'> & FREE shipping.</span></span>#i", $this->response);
		$stocka  = preg_match("#<div id=\"availability\" class=\"a-section a-spacing-none\">\s+<span class=\"a-size-medium a-color-success\">\s+.{0,50}#i", $this->response, $stock);
		if(isset($stock[0])){
			$stockb  = preg_match("#(?:<div id=\"availability\" class=\"a-section a-spacing-none\">\s+<span class=\"a-size-medium a-color-success\">\s+)?(.{0,50})#i", $stock[0], $stocka);
			$stock   = trim($stocka["1"], "\r\n");
		}
		$valorB  = preg_match("#<span id=\"priceblock_ourprice\" class=\"a-size-medium a-color-price\">.[0-9.,]+#i", $this->response, $price);
		if(!isset($price[0])){
			$item_detail['notavaliable'] = 1;
			$item_detail['message'] = "No Price";
			$item_detail['asin']         = $sku;
			return $item_detail;
		}else{
			$valorBb = preg_match("#(?:<span id=\"priceblock_ourprice\" class=\"a-size-medium a-color-price\">.)?([0-9.,]+)#i", $price[0], $priceB);
		}
			#Getting images
		$result_all_images = array();
		if (strpos($this->response, "<ul class=\"a-unordered-list a-nostyle a-button-list a-vertical a-spacing-top-micro\">") !== false) {
			$image   = $this->get_string_between($this->response, "<ul class=\"a-unordered-list a-nostyle a-button-list a-vertical a-spacing-top-micro\">", "/ul");
			$image_i = $this->get_string_between($image, "<li", "</li>");
			$image_i = $this->get_string_between($image_i, "<img", ">");
			$image_i = $this->get_string_between($image_i, "src=\"", "\"");
			$iter    = 0;
			while (($image_i !== "") and ($iter < 5)) {
				$iter++;
				if (strpos($image_i, ".gif") === false) {
					$image_i_large = str_replace("._SX38_SY50_CR,0,0,38,50_", "", $image_i);
					$image_i_large = str_replace("._SS40_", "", $image_i_large);
					array_push($result_all_images, $image_i_large);
					$image   = $this->remove_string_between($image, "<li", "</li>");
					$image_i = $this->get_string_between($image, "<li", "</li>");
					$image_i = $this->get_string_between($image_i, "<img", ">");
					$image_i = $this->get_string_between($image_i, "src=\"", "\"");
				}
			}
		} elseif (strpos($this->response, "<ul class=\"a-unordered-list a-nostyle a-button-list a-horizontal a-spacing-top-null\">") > 0) {
			$image   = $this->get_string_between($this->response, "<ul class=\"a-unordered-list a-nostyle a-button-list a-horizontal a-spacing-top-null\">", "/ul");
			$image_i = $this->get_string_between($image, "<li", "</li>");
			$image_i = $this->get_string_between($image_i, "<img", ">");
			$image_i = $this->get_string_between($image_i, "src=\"", "\"");
			while ($image_i !== "") {
				$image_i_large = str_replace("_AC_SX150_SY75_CR,0,0,150,75_.", "", $image_i);
				array_push($result_all_images, $image_i_large);
				$image   = $this->remove_string_between($image, "<li", "</li>");
				$image_i = $this->get_string_between($image, "<li", "</li>");
				$image_i = $this->get_string_between($image_i, "<img", ">");
				$image_i = $this->get_string_between($image_i, "src=\"", "\"");
			}

		} elseif (strpos($this->response, "<ul class=\"a-unordered-list a-nostyle a-button-list a-vertical a-spacing-top-extra-large\">") > 0) {
			$image   = $this->get_string_between($this->response, "<ul class=\"a-unordered-list a-nostyle a-button-list a-vertical a-spacing-top-extra-large\">", "/ul");
			$image_i = $this->get_string_between($image, "<li", "</li>");
			$image_i = $this->get_string_between($image_i, "<img", ">");
			$image_i = $this->get_string_between($image_i, "src=\"", "\"");
			while ($image_i !== "") {
				$image_i_large = str_replace("_US40_.", "", $image_i);
				array_push($result_all_images, $image_i_large);
				$image   = $this->remove_string_between($image, "<li", "</li>");
				$image_i = $this->get_string_between($image, "<li", "</li>");
				$image_i = $this->get_string_between($image_i, "<img", ">");
				$image_i = $this->get_string_between($image_i, "src=\"", "\"");
			}
		}
		$image_i = $this->get_string_between($this->response, "<div class=\"a-column a-span3 a-spacing-micro imageThumb thumb", "</div>");
		while ($image_i != "") {
			$image_i       = $this->get_string_between($image_i, "<img", ">");
			$image_i       = $this->get_string_between($image_i, "src=\"", "\"");
			$image_i_large = str_replace("._AC_SX60_CR,0,0,60,60_", "", "$image_i");
			array_push($result_all_images, $image_i_large);
			$this->response = $this->remove_string_between($this->response, "<div class=\"a-column a-span3 a-spacing-micro imageThumb thumb", "</div>");
			$image_i        = $this->get_string_between($this->response, "<div class=\"a-column a-span3 a-spacing-micro imageThumb thumb", "</div>");
		}
		$images = implode("~^~", $result_all_images);
			#End of images crawler
			#start Features crawler
		$feature = $this->get_string_between($this->response, "id=\"feature-bullets\"", "</ul>");
		if ($feature !== "") {
			$feature_i    = $this->get_string_between($feature, "<li>", "</li>");
			$feature_i    = $this->get_string_between($feature_i, "<span", "/span>");
			$feature_i    = $this->get_string_between($feature_i, ">", "<");
			$item_feature = "";
			while ($feature_i !== "") {
				$item_feature .= trim($feature_i);
				$feature   = $this->remove_string_between($feature, "<li>", "</li>");
				$feature_i = $this->get_string_between($feature, "<li>", "</li>");
				$feature_i = $this->get_string_between($feature_i, "<span class=\"a-list-item\">", "</span>");
			}
		}
			#end of feature crawler
			#start Dimensions
		$t      = preg_match("#Product Dimensions:\s+</span>\s+<span>([0-9]+)\sx\s([0-9]+)\sx\s([0-9]+)#i", $this->response, $d);
		$p      = preg_match("#Shipping Weight:\s+.{0,}\s+<span>([0-9,]+)\s(\w+)#i", $this->response, $l);
		if(!isset($l['1'])){
			$p      = preg_match("#Shipping Weight:+</b>\s+(\d+(\.\d{1,2})?)+\s(\w+)#i", $this->response, $l);
			if(!isset($l['1'])){
				$item_detail['notavaliable'] = 1;
				$item_detail['message'] = "No Weight";
				$item_detail['asin']         = $sku;
				return $item_detail;
			}else{
				$weight_type = $l[3];
				$weight = $l[1];				
			}
		}
		$weight = $l['1'];
		if (isset($l[3])) {
			$weight_type = $l[3];
		}else{
			$weight_type = $l[2];
		}
		if(strtolower($weight_type) == 'ounces'){
			$weight = $weight*0.0625;
		}
		$weight = $weight*100;
		
				#Model
		preg_match("#Item model number:\s+</span>\s+<span>(\w[a-z,\s&áéíóú,0-9]+)</span>#i", $this->response, $mod);
		if ($mod == null){
			preg_match("#Item model number:</b>\s+(\w[a-z,\s&áéíóú,0-9]+)</li>#i", $this->response, $mod);
		}

				#Product marca search
		preg_match("#<a id=\"brand\".{0,}\s+(.{0,})#i", $this->response, $marca);


				#Product Category search
		preg_match("#wayfinding-breadcrumbs_feature_div\"+\s*.*+\s+.*+\s+.*+\s+.*+\s(.+)+\s+\s+.*+\s+.*+\s+.*+\s+.*+\s+.*+\s+.*+\s+.*+\s(.+)+\s+.*+\s+.*\s+.*+\s+.*+\s+\s+.*+\s+\s+.*+\s+\s+.*+\s(.+)+\s+.*+\s+\s+.*+\s+\s+.*+\s+\s+.*+\s+\s+.*+\s+\s+.*+\s+\s+.*+\s(.+)#i", $this->response, $category);

		if (!isset($category[1])){
			preg_match("#wayfinding-breadcrumbs_feature_div\"+\s*.*+\s+.*+\s+.*+\s+.*+\s(.+)+\s+\s+.*+\s+.*+\s+.*+\s+.*+\s+.*+\s+.*+\s+.*+\s(.+)+\s+.*+\s+.*\s+.*+\s+.*+\s+\s+.*+\s+\s+.*+\s+\s+.*+\s(.+)#i", $this->response, $category);
		}
		if (!isset($category[1])){
			preg_match("#wayfinding-breadcrumbs_feature_div\"+\s*.*+\s+.*+\s+.*+\s+.*+\s(.+)+\s+\s+.*+\s+.*+\s+.*+\s+.*+\s+.*+\s+.*+\s+.*+\s(.+)#i", $this->response, $category);
		}
		if (!isset($category[1])){
			preg_match("#wayfinding-breadcrumbs_feature_div\"+\s*.*+\s+.*+\s+.*+\s+.*+\s(.+)#i", $this->response, $category);
		}
		if (!isset($category[1])){
			preg_match("#nav-a nav-b'><span class=\"nav-a-content\">(\w[a-z,\s&áéíóú,0-9]+)</span>#i", $this->response, $category);
		}


		$j          = 1;
		$categories = "";
		for ($i = 1; $i < count($category); $i++) {
			$categories .= trim((string) $category[$i]);
			if ($j < count($category)) {
				$categories .= ",";
				$j++;
			}
		}
				#Producto department search

		#echo $mod[1]."\n";


		$department = trim($category[1]);

		#end of dimensions
		$item_detail['notavaliable'] = 0;
		$item_detail['asin']         = $sku;
		$item_detail['product_type']          = (string) $department;
		$item_detail['product_category']      = $categories;
		$item_detail['product_title_english'] = $title;
		$item_detail['specification_english'] = isset($item_feature) ? $item_feature : null;
		$item_detail['brand']                 = (string) (isset($marca[1])) ? $marca[1] : null;
		$item_detail['model']                 = (string) (isset($mod[1])) ? $mod[1] : null; 
		$item_detail['image_url'] = $images;
		$item_detail['sale_price'] = (float) $this->floatvalue($priceB["1"]);
		$item_detail['quantity']       = 8;
		$item_detail['weight_unit']    = 'lb';
		$item_detail['package_weight'] = (float) $weight;
		$item_detail['dimension_unit'] = 'in';
		$item_detail['package_width']  = (float) (isset($d[1])) ? $d[1] : 0;
		$item_detail['package_height'] = (float) (isset($d[2])) ? $d[2] : 0;
		$item_detail['package_length'] = (float) (isset($d[3])) ? $d[3] : 0;
		$item_detail['is_prime'] = $prime;
		$item_detail['item_height']    = (float) (isset($d[2])) ? $d[2] : 0;
		$item_detail['item_length']    = (float) (isset($d[3])) ? $d[3] : 0;
		$item_detail['item_width']     = (float) (isset($d[1])) ? $d[1] : 0;
		$item_detail['url'] = $this->url;
		#print_r($item_detail);
		return $item_detail;

	}
}


///*********************************************

function crawler_create($url, $type) {


	#############################
	$header=array();            
	$header[]="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
	$header[]="Accept-Encoding: gzip, deflate";
	$header[]="Accept-Language: en-US,en;q=0.5";
	$header[]="Connection: keep-alive";

	$this->url = $url;
	$this->ch = curl_init($this->url);
	curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7');
	curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($this->ch, CURLOPT_ENCODING , "gzip");
	$this->response = curl_exec($this->ch);
	curl_close($this->ch);
	$item_detail = array();

	#############################

	$title         = get_string_between($this->response, "itle>", "</title>");
	$title_replace = array('Amazon.com :', 'Amazon.com');
	$title         = str_replace($title_replace, "", $title);
	if (strpos($title, "we just need to make sure you're not a robot") > 0 ) {
		#if (!$type_json) {
		$item_detail['notavaliable'] = 1;
		$item_detail['message'] = "No Connection";
		#}
		return $item_detail;

	}else if(preg_match("#nb_sb_noss_null#i",$this->response)){
		#if (!$type_json) {
		$item_detail['notavaliable'] = 1;
		$item_detail['message'] = "No Connection";
		#}
		return $item_detail;

	} else {

		if($type==1){	#principal

			if (preg_match("#</span> did not match any products.#i",$this->response)) {
				$item_detail['message'] = "not match any products";
				$item_detail['notavaliable'] = 1;
				#return $item_detail;
			}else{
				preg_match("#([0-9]+)</span>\s+<span class=\"pagnRA\"> <a title=\"Next Page\"#i", $this->response, $pages);
				if  (!$pages){
					preg_match("#([0-9]+)</a></span>\s+<span class=\"pagnRA\"> <a title=\"Next Page\"#i", $this->response, $pages);
				}
				preg_match_all("#data-asin=\"(B.........)\"#i", $this->response, $skus);
				$resultado = array_unique($skus[1]);
				$string="";
				foreach ($resultado as $key) {
					$string.=$key.",";
				}
				$string = substr($string, 0, (strlen($string)-1));
				$item_detail['notavaliable'] = 0;
				$item_detail['skus']    = $string;
				if(!isset($pages[1])){
					$item_detail['pages']   = 1;	
				}else{
					$item_detail['pages']   = $pages[1];
				}
			}
			return $item_detail;

		}else{
			if (preg_match("#</span> did not match any products.#i",$this->response)) {
				$item_detail['message'] = "No Connection";
				$item_detail['notavaliable'] = 1;
			}else{
				preg_match_all("#data-asin=\"(B.........)\"#i", $this->response, $skus);

				$resultado2 = array_unique($skus[1]);
				$string2="";
				foreach ($resultado2 as $key2) {
					$string2.=$key2.",";
				}
				$string2 = substr($string2, 0, (strlen($string2)-1));
				$item_detail['notavaliable'] = 0;
				$item_detail['skus']    = $string2;
				$item_detail['pages']    = 0;
			}    
			return $item_detail;
		}



	}
}


///*********************************************

function crawler_create2($url) {
		#Getting Page code
	$this->url = $url;
	$this->ch  = curl_init();
	curl_setopt($this->ch, CURLOPT_URL, $url);
	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36 OPR/46.0.2597.57");
	curl_setopt($this->ch, CURLOPT_FRESH_CONNECT, true);
	$this->response = curl_exec($this->ch);
	curl_close($this->ch);
	$item_detail = array();

	$title         = get_string_between($this->response, "itle>", "</title>");
	$title_replace = array('Amazon.com :', 'Amazon.com');
	$title         = str_replace($title_replace, "", $title);
	if (strpos($title, "we just need to make sure you're not a robot") > 0 ) {
		#if (!$type_json) {
		$item_detail['notavaliable'] = 1;
		$item_detail['message'] = "No Connection";
		#}
		return $item_detail;

	}else if(preg_match("#nb_sb_noss_null#i",$this->response)){
		#if (!$type_json) {
		$item_detail['notavaliable'] = 1;
		$item_detail['message'] = "No Connection";
		#}
		return $item_detail;

	} else {

		if (preg_match("#</span> did not match any products.#i",$this->response)) {
			$item_detail['message'] = "No Connection";
			$item_detail['notavaliable'] = 1;
			#return $item_detail;
		}else{
			preg_match_all("#data-asin=\"(B.........)\"#i", $this->response, $skus);
			
			print_r($skus);

			$resultado2 = array_unique($skus[1]);
			$string2="";
			foreach ($resultado2 as $key2) {
				$string2.=$key2.",";
			}
			$string2 = substr($string2, 0, (strlen($string2)-1));
			$item_detail['notavaliable'] = 0;
			$item_detail['skus2']    = $string2;
		}
		return $item_detail;
	}
}
///*********************************************





function crawler_translate($url) {

	#############################
	$header=array();            
	$header[]="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
	$header[]="Accept-Encoding: gzip, deflate";
	$header[]="Accept-Language: en-US,en;q=0.5";
	$header[]="Connection: keep-alive";

	$this->url = $url;
	$this->ch = curl_init($this->url);
	curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7');
	curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($this->ch, CURLOPT_ENCODING , "gzip");
	$this->response = curl_exec($this->ch);
	curl_close($this->ch);
	$item_detail = array();

	$title         = get_string_between($this->response, "itle>", "</title>");
	if ( $title == "Error 400 (Bad Request)!!1") {
		#if (!$type_json) {
		$item_detail['notavaliable'] = 1;
		$item_detail['message']      = "No Connection";
		#}
		return $item_detail;
	} else {

		#print_r($this->response);die();

		if (preg_match("#TRANSLATED_TEXT='(.*?)\~\~\~ \^ \~\~\~(.*?)';var#i", $this->response, $matches)){
			
			$var1=htmlspecialchars_decode($matches[1]);
			$var2=htmlspecialchars_decode($matches[2]);
			
			$item_detail['notavaliable'] = 0;
			$item_detail['description']		 = $var1;
			$item_detail['title']		 = $var2;
			if (!isset($var1)){
				$item_detail['notavaliable'] = 1;
			}if(!isset($var2)){
				$item_detail['notavaliable'] = 1;
			}
			
			return $item_detail;
		}else{
			$item_detail['notavaliable'] = 1;
			$item_detail['message']      = "No Connection";
			$item_detail['sku']         = (string) $sku;
			return $item_detail;
		}
	}
}

}
/*
$test = new Amazon();
print_r($test->crawler("https://www.amazon.com/dp/B003BMD75E", "B077NPGNR8", "23456"));
*/