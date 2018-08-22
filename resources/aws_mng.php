<?php
class amazonManager {
	public $aws_access_key_id;
	public $aws_secret_key;
	public $endpoint;
	public $uri;
	public $service;
	public $associateTag;

	public function __construct($access_key_id, $secret_key, $Tag) {		
		$this->aws_access_key_id = $access_key_id;
		$this->aws_secret_key    = $secret_key;
		$this->endpoint          = "webservices.amazon.com";
		$this->uri               = "/onca/xml";
		$this->service           = "AWSECommerceService";
		$this->associateTag      = $Tag;
	}


	public function ToAscii($string) {
		$strlen = strlen($string);
		$charCode = array();
		for ($i = 1; $i < $strlen; $i++) {
			$charCode[] = ord(substr($string, $i, 1));
		}
		$result = implode("",$charCode);
		return $result;
	}

	public function main_search($search_index, $keywords, $sort) {
		$aws_access_key_id = $this->aws_access_key_id;
		$result            = array();
		$params            = array(
			"Service"        => "AWSECommerceService",
			"Operation"      => "ItemSearch",
			"AWSAccessKeyId" => $this->aws_access_key_id,
			"AssociateTag"   => $this->associateTag,
			"Availability"   => "Available",
			"SearchIndex"    => $search_index,
			"ResponseGroup"  => "ItemIds",
			"Condition"      => "New",
			"Keywords"       => $keywords,
			"Sort" 		 	 => $sort,
		);
		if (!isset($params["Timestamp"])) {
			$params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
		}

		ksort($params);
		$pairs = array();
		foreach ($params as $key => $value) {
			array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
		}
		$canonical_query_string = join("&", $pairs);
		$string_to_sign         = "GET\n".$this->endpoint."\n".$this->uri."\n".$canonical_query_string;
		$signature              = base64_encode(hash_hmac("sha256", $string_to_sign, $this->aws_secret_key, true));
		$request_url            = 'http://'.$this->endpoint.$this->uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);
		$ch                     = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$xml = simplexml_load_string($response, 'SimpleXmlElement', LIBXML_NOERROR+LIBXML_ERR_FATAL+LIBXML_ERR_NONE);
		return (int) $xml->Items->TotalPages;
	}

	public function item_search($search_index, $keywords, $q, $sort) {
		$aws_access_key_id = $this->aws_access_key_id;
		$result            = array();
		$params            = array(
			"Service"        => "AWSECommerceService",
			"Operation"      => "ItemSearch",
			"AWSAccessKeyId" => $this->aws_access_key_id,
			"AssociateTag"   => $this->associateTag,
			"Availability"   => "Available",
			"SearchIndex"    => $search_index,
			"ItemPage"       => $q,
			"ResponseGroup"  => "ItemIds",
			"Condition"      => "New",
			"Keywords"       => $keywords,
			"Sort" 		 	 => $sort,
		);
		if (!isset($params["Timestamp"])) {
			$params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
		}

		ksort($params);
		$pairs = array();
		foreach ($params as $key => $value) {
			array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
		}
		$canonical_query_string = join("&", $pairs);
		$string_to_sign         = "GET\n".$this->endpoint."\n".$this->uri."\n".$canonical_query_string;
		$signature              = base64_encode(hash_hmac("sha256", $string_to_sign, $this->aws_secret_key, true));
		$request_url            = 'http://'.$this->endpoint.$this->uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);
		$ch                     = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$xml = simplexml_load_string($response, 'SimpleXmlElement', LIBXML_NOERROR+LIBXML_ERR_FATAL+LIBXML_ERR_NONE);
		#print_r($xml);die();
		if (isset($xml->Items->Item)) {
			foreach ($xml->Items->Item as $root) {
				$item_detail         = array();
				$item_detail['asin'] = (string) $root->ASIN;
				array_push($result, $item_detail);
			}

		}
		return $result;
	}

	public function floatvalue($val) {
		$val = str_replace(",", ".", $val);
		$val = preg_replace('/\.(?=.*\.)/', '', $val);
		return floatval($val);
	}

	public function search_item($asin) {
		$params = array(
			"Service"        => $this->service,
			"Operation"      => "ItemLookup",
			"AWSAccessKeyId" => $this->aws_access_key_id,
			"AssociateTag"   => $this->associateTag,
			"ItemId"         => $asin,
			"IdType"         => "ASIN",
			"ResponseGroup"  => "BrowseNodes,Images,ItemAttributes,Offers",
		);
		if (!isset($params["Timestamp"])) {
			$params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
		}
		ksort($params);
		$pairs = array();
		foreach ($params as $key => $value) {
			array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
		}
		$canonical_query_string = join("&", $pairs);
		$string_to_sign         = "GET\n".$this->endpoint."\n".$this->uri."\n".$canonical_query_string;
		$signature              = base64_encode(hash_hmac("sha256", $string_to_sign, $this->aws_secret_key, true));
		$request_url            = 'http://'.$this->endpoint.$this->uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);
		$url                    = "http://webservices.amazon.com/onca/xml";

		#Accediendo al url encoding xml
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$xml = simplexml_load_string($response);
		$result = array();
		#echo "<pre>";
		#print_r($xml);
		#print_r($xml->Items->Request->ItemLookupRequest->ItemId);
		#die();

		if (isset($xml->Items->Item)) {

			foreach ($xml->Items->Request->ItemLookupRequest->ItemId as $sku_id) {

				foreach ($xml->Items->Item as $root) {
					$category_p='';

					if( (string)$sku_id == (string)$root->ASIN){

						$categories = array();
						$available  = 0;
						$flag_seller= 0;
						$j          = 1;
						$valida=0;
						
						if (isset($root->BrowseNodes->BrowseNode)){
							$nodos = $root->BrowseNodes->BrowseNode;

							while ($valida==0) {
								if(isset($categories, $nodos->Name)){
									array_push($categories, $nodos->Name);
									$category_p=$nodos->Name;
								}
								$categoria_p = (string) $nodos->Name;

								if (isset($nodos->Ancestors)){
									$nodos=$nodos->Ancestors->BrowseNode;
								}else{
									$valida=1;
								}

							}
							$categories = (string) implode(',', array_reverse($categories));
						}		
						$item_feature = "";
						foreach ($root->ItemAttributes->Feature as $feature) {
							$item_feature .= ".- ".$feature;
						}
						/*make images string*/
						$images_set = $root->ImageSets->ImageSet;
						$images     = "";
						if (isset($root->LargeImage->URL)){
							$images 	= $root->LargeImage->URL;
							if ( count($images_set) >= 1 ){
								$images .= "~^~";
							}
						}
						$j          = 1;
						for ($i = 0; $i < count($images_set); $i++) {
							$images .= $images_set[$i]->LargeImage->URL;
							if ($j < count($images_set)) {
								$images .= "~^~";
								$j++;
							}
						}

						/*make main array */
						$avaliability = (isset($root->Offers->Offer->OfferListing->Availability))?$root->Offers->Offer->OfferListing->Availability:0;

						if($avaliability == 'Usually ships in 24 hours'){
							$available = 1;
						}elseif($avaliability == 'Usually ships in 1-2 business days' || $avaliability == 'Usually ships in 1 to 2 days' || $avaliability == 'Usually ships in 2 to 3 days' || $avaliability == 'Usually ships in 2-3 business days'){
							$available = 2;
						}else{
							$available = 0;
						}

						$sale_price   = 0;
						if (isset($root->Offers->Offer->OfferListing->SalePrice->FormattedPrice)) {
							$sale_price = substr($root->Offers->Offer->OfferListing->SalePrice->FormattedPrice, 1);
						} else {
							if (isset($root->Offers->Offer->OfferListing->Price->FormattedPrice)) {
								$sale_price = substr($root->Offers->Offer->OfferListing->Price->FormattedPrice, 1);
							}else{
								if (isset($root->OfferSummary->LowestNewPrice->FormattedPrice)) {
									$sale_price = substr($root->OfferSummary->LowestNewPrice->FormattedPrice, 1);
									$flag_seller=1;	
								}	
							}
						}

						if(isset($root->ItemAttributes->ProductGroup)){
							if($root->ItemAttributes->ProductGroup == "Pantry"){
								$sale_price = $sale_price+8;
							}

						}


						if(isset($root->ItemAttributes->ProductTypeName)){
							$Binding  = (string) $root->ItemAttributes->ProductTypeName;
						}else{
							$Binding  = $categoria_p;
						}

						$item_detail                          = array();
						
						$item_detail['asin']                  = (string) $root->ASIN;
						$item_detail['product_type']          = $Binding;
						$item_detail['ean']                   = (string) $root->ItemAttributes->EAN;
						$item_detail['product_category']      = $categories;
						$item_detail['product_title_english'] = (string) $root->ItemAttributes->Title;
						$item_detail['specification_english'] = $item_feature;
						$item_detail['brand']                 = (string) $root->ItemAttributes->Brand;
						$item_detail['model']                 = (string) $root->ItemAttributes->Model;
						$item_detail['image_url']             = $images;
						$item_detail['UPC']                   = (string) $root->ItemAttributes->UPC;
						$item_detail['currency']              = (string) $root->ItemAttributes->ListPrice->CurrencyCode;
						$item_detail['sale_price']            = (float) $this->floatvalue($sale_price);
						$item_detail['quantity']              = (string) $root->OfferSummary->TotalNew;
						$item_detail['condition']             = (isset($root->Offers->Offer->OfferAttributes->Condition))?(string) $root->Offers->Offer->OfferAttributes->Condition:0;
						$item_detail['weight_unit']           = 'lb';
						if (isset($root->ItemAttributes->PackageDimensions->Weight)){
							$item_detail['package_weight']        = (float) $root->ItemAttributes->PackageDimensions->Weight/100;
						}else{
							$item_detail['package_weight']        = (float) $root->ItemAttributes->ItemDimensions->Weight;
						}
						$item_detail['dimension_unit']        = 'in';
						$item_detail['package_width']         = (float) $root->ItemAttributes->PackageDimensions->Width/100;
						$item_detail['package_height']        = (float) $root->ItemAttributes->PackageDimensions->Height/100;
						$item_detail['package_length']        = (float) $root->ItemAttributes->PackageDimensions->Length/100;
						$item_detail['clothingSize']          = (string) ($root->ItemAttributes->ClothingSize)?$root->ItemAttributes->ClothingSize:NULL;
						$item_detail['color']                 = (string) (($root->ItemAttributes->Color[0])?substr($root->ItemAttributes->Color[0], 0, 200):NULL);
						$item_detail['department']            = (string) (($root->ItemAttributes->Department[0])?$root->ItemAttributes->Department[0]:NULL);
						$item_detail['is_prime']              = (string) (isset($root->Offers->Offer->OfferListing->IsEligibleForPrime))?(($root->Offers->Offer->OfferListing->IsEligibleForPrime != 1)?0:1):0;
						$item_detail['item_height']           = (float) $root->ItemAttributes->ItemDimensions->Height/100;
						$item_detail['item_length']           = (float) $root->ItemAttributes->ItemDimensions->Length/100;
						$item_detail['item_width']            = (float) $root->ItemAttributes->ItemDimensions->Width/100;
						$item_detail['item_weight']            = (float) $root->ItemAttributes->ItemDimensions->Weight/100;
						$item_detail['url']                   = (string) $root->DetailPageURL;
						$item_detail['ParentASIN']            = (string) $root->ParentASIN;
						$item_detail['avaliable']			  = (string) $available;
						$item_detail['category_p']			  = (string) $category_p;



						if (isset($sale_price) && $available <> 0 ) {
							$item_detail['notavaliable']  = 0;
						} else {
							$item_detail['notavaliable']  = 1;
						}

						if ($sale_price==0){
							$item_detail['notavaliable']  = 1;
						}

						if ($flag_seller == 1 && $item_detail['is_prime'] == 0){
							$item_detail['notavaliable']  = 1;	
						}

						array_push($result, $item_detail);
					}	
				}

				
				if(!isset($item_detail['notavaliable'])){
					$item_detaile                 = array();
					$item_detaile['notavaliable'] = 2;
					$item_detaile['asin']         = (string) $sku_id;
					array_push($result, $item_detaile);

				}
				$item_detail                          = array();
			}	
		} else{

			if (isset($xml->Items->Request->Errors)) {

				#if ($xml->Items->Request->Errors->Error->Code == "AWS.ECommerceService.ItemNotAccessible"){
				foreach ($xml->Items->Request->ItemLookupRequest->ItemId as $asins) {
					$item_detail                 = array();
					$item_detail['notavaliable'] = 2;
					$item_detail['asin']         = (string) $asins;
					array_push($result, $item_detail);
				}
				#}

			}

		}

		return $result;
		
	}



	###########################################

	public function search_child($asin) {
		$params = array(
			"Service"        => $this->service,
			"Operation"      => "ItemLookup",
			"AWSAccessKeyId" => $this->aws_access_key_id,
			"AssociateTag"   => $this->associateTag,
			"ItemId"         => $asin,
			"ResponseGroup"  => "VariationMatrix",
		);
		if (!isset($params["Timestamp"])) {
			$params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
		}
		ksort($params);
		$pairs = array();
		foreach ($params as $key => $value) {
			array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
		}
		$canonical_query_string = join("&", $pairs);
		$string_to_sign         = "GET\n".$this->endpoint."\n".$this->uri."\n".$canonical_query_string;
		$signature              = base64_encode(hash_hmac("sha256", $string_to_sign, $this->aws_secret_key, true));
		$request_url            = 'http://'.$this->endpoint.$this->uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);
		$url                    = "http://webservices.amazon.com/onca/xml";

		#Accediendo al url encoding xml
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$xml = simplexml_load_string($response);
		$result = array();
		#echo "<pre>";
		#print_r($xml);
		#die();
		#$result=$xml->Items->Item->Variations;
		$item_detail                 = array();

		if (isset($xml->Items->Item->Variations)) {
			foreach ($xml->Items->Item->Variations->Item as $root) {
				$item_detail['asin']         = (string) $root->ASIN;
				array_push($result, $item_detail);
			}
		} else {
			$item_detail['notavaliable'] = 1;
			$item_detail['asin']         = $asin;
			array_push($result, $item_detail);

		}
		
		return $result;
	}

	###########################################




}
#$aws  = new amazonManager('AKIAJAY2S72P4TZL743A','SojHd1Tq+wbgcH91oGn1aDoS25vPpTxwLo2paS/a','Sebaspte-20');		#05

#$aws  = new amazonManager('AKIAJMDKLZSBYDBQH4FA','54UzR5Un3zTOuU59ZMV23dQ6bT7qNW5LJoy+exbw','Sebaspte-20');		#11

#$aws  = new amazonManager('AKIAISPROQVUE2BS5Y5A','FZXgRcC2+1VVCo4K1tAhdV/HcM3PFx00Ag74IhOc','Setoba-20');			#12

#$aws  = new amazonManager('AKIAJHWYQZXMM3IUCBMA','LnUfVXkdf9aaJBJ+2/ubpmi2xgGpMW8jcx4GLNHw','Setoba-20');			#13

#$aws  = new amazonManager('AKIAJZCBIVV3XAEMW7VA','HDD+Ea6AVsJOrvlcqV0nssNAQbQAKXJqjVgR7N2E','Jesirodrigu3z-20');	#14

#$aws  = new amazonManager('AKIAI2VR6RY64ZEQPJZQ','q6uleU9eQU8YWO0Gv4/7q6/0/f0QVo4ISFj1kbuQ','jesirodrigu3z-20');	#15

#$aws  = new amazonManager('AKIAI3H6L6IHLGZ7VXWA','xJi2wZ/sxg3nBvD0dBnvyO5DyTdizPDjRnqXwq3u','Santiespi2000-20');	#16

#$aws  = new amazonManager('AKIAIM2EYANNK5NAEP2Q','zvhPAk5MqgJUk7OnBQ3WEN1RgavE1gJpkgP98yOF','Santiespi2000-20');	#order_meli

#$aws  = new amazonManager('AKIAIYEUT4YA3UTEPUNA','iiVSzwY9BI0CvSLcUshrJTv2q800GBvck3YVotnV','Tobon90-20');			#order_cbt

#$aws  = new amazonManager('AKIAJYFFGK3OUHCTTD2Q','H988c7di0ORaS4UwyO76bFFcAKHgWuq/4zAcONvs','Tobon90-20');			#create1

#$aws  = new amazonManager('AKIAJEBTOLCUCVUCN4MQ','ZN5cqxT8O+eSkuLfuX1ZS3TEuA5RmJbgfbmOOvyo','Karengonza10-20');	#create2

#$aws  = new amazonManager('AKIAIRFL65AVBR5EHS2Q','1+vkYHjFmvDdM1bFm1hv+QW9Pyzo/yaFeWXtDSyJ','Karengonza10-20');	#16

#--------------------nuevas--------------------------

#$aws  = new amazonManager('AKIAI2RUGCLISFQ7I37Q','il2XVc0VdfIkTFTPrH2dIBLsfCeLFvOKTaSpm/wE','paocastro90-20');		#create

#$aws  = new amazonManager('AKIAJFRPRT5KNOUKJILA','a3rhWuRr4DtgFQOs27yx30xI5ZveGGfu68orDHfT','paocastro90-20');		#create



#print_r($aws->search_item('B0761SCYKR,B00HSSLPWO,B004SCKMH2,B00PNHWP0I,B01CED9V3C,B00N1UTZ8E,B00AL19MIO,B00II6JS4C,B00F4AXFEO,B0127CV8AO'));
#print_r($aws->search_item('B000PKXD6S,B005KBRQHE,B0077QS5RU,B000227MP2,B0053PWNV6,B003ASIZJC,B001PL7QMI,B001FSHJZU,B004Q28JEC,B0050L0Y7I'));

#print_r($aws->search_item('B001F7AHXM'));
#print_r($aws->search_child('B01C2H003U'));
?>