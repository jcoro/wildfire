<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-product.php
File Date: 2012-02-01
Description: manages product-related functions
Dependencies: Requires cw-func-query to be included in calling page
==========================================================
*/
// /////////////// 
// PRODUCT QUERIES 
// /////////////// 
// // ---------- // Get Product Details: CWgetProduct() // ---------- // 
function CWgetProduct($product_id, $info_type=null, $customer_id=null, $promo_code=null, $customer_type=null) {
	if ($info_type === null) $info_type = "simple";
	if ($customer_id === null) $customer_id = 0;
	if ($promo_code === null) $promo_code = "";
	if ($customer_type === null) $customer_type = 0;
	$product=array();
	$detailsQuery = CWquerySelectProductDetails($product_id);
	$product['product_preview_description'] = ((isset($detailsQuery['product_preview_description'][0])) ? $detailsQuery['product_preview_description'][0] : "" );
	$datatype = trim($info_type);	
	$ii=NULL;
	$skuct=0;
	$skupricelow = 0;
	$skupricehigh = 0;
	$altpricelow = 0;
	$altpricehigh = 0;
	$discountamount = 0;
	$discountedprice = 0;
	$discpricelow = 0;
	$discpricehigh = 0;
	$skusQuery = '';
	$imagesQuery = '';
	$imagelist = array();
	$image_typelist = array();
	$imgct = 0;
	$catList = array();
	$catsQuery = '';
	$catct = 0;
	$optCt = 0;
	$optList = array();
	$optionsQuery = '';
	$upsellList = array();
	$upsellQuery = '';
	$recipQuery = '';
	$recipList = array();
	$product['qty_max'] = -99999;
	$product['price_low'] = 0;
	$product['price_high'] = 0;
	$product['price_alt_low'] = 0;
	$product['price_alt_high'] = 0;
	// use customer id from session if not provided 
	if ($customer_id == 0 && isset($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] != 0){
		$customer_id = $_SESSION["cwclient"]["cwCustomerID"];
	}
	// use customer type from session if not provtypeed 
	if ($customer_type == 0 && isset($_SESSION["cwclient"]["cwCustomerType"]) && $_SESSION["cwclient"]["cwCustomerType"] != 0){
		$customer_type = $_SESSION["cwclient"]["cwCustomerType"];
	}
	// use promo code from session if not provided 
	if ($promo_code == '' && isset($_SESSION['cwclient']['discountPromoCode']) && $_SESSION['cwclient']['discountPromoCode'] != '') {
		$promo_code = $_SESSION['cwclient']['discountPromoCode'];
	}
	foreach ($detailsQuery as $ii => $valueArr) {
		if (($datatype != 'mini' || in_array(strtolower($ii), array("product_id","product_name","product_preview_description","product_archive"))) && $ii != "totalRows") { //
			$product[strtolower($ii)] = $valueArr[0];
		}
	}
	// /end product struct
	// skus child struct 
	if($datatype == 'complex') {
		$product['skus'] = array();	
	}
	// query: get product skus 
	$skusQuery = CWquerySelectSKUs($product_id);
	// loop skus query 
	$idList = array();
//echo "0<pre>";
//var_dump($skusQuery);
//echo "</pre>";
	for($i=0; $i<$skusQuery['totalRows']; $i++ ) {
		$skuct++;
		// create numbered substruct for each sku 
		if($datatype == 'complex') {
			$product['skus']['sku'.$skuct] = array();
			foreach ($skusQuery as $ii => $valueArr) {
				if ($ii != "totalRows") {
					$product['skus']['sku'.$skuct][$ii] = $valueArr[$i];
				}
			}
		}
		$idList[] = $skusQuery['sku_id'][$i];
		// set high/low prices 
		if ($skusQuery['sku_price'][$i] > $skupricehigh) { 
			$skupricehigh = $skusQuery['sku_price'][$i];
			$product['price_high'] = $skusQuery['sku_price'][$i];
		}
		if ($skusQuery['sku_price'][$i] < $skupricelow || ($skupricelow == 0 && $skusQuery['sku_price'][$i] != 0)) {
			$skupricelow = $skusQuery['sku_price'][$i];
			$product['price_low'] = $skusQuery['sku_price'][$i];
		}
		// set alt price high/low 
		if ($skusQuery['sku_alt_price'][$i] > $altpricehigh) {
			$altpricehigh = $skusQuery['sku_alt_price'][$i];
			$product['price_alt_high'] = $skusQuery['sku_alt_price'][$i];
		}
		if ($skusQuery['sku_alt_price'][$i] < $altpricelow || ($altpricelow == 0 && $skusQuery['sku_alt_price'][$i] != 0)) {
			$altpricelow = $skusQuery['sku_alt_price'][$i];
			$product['price_alt_low'] = $skusQuery['sku_alt_price'][$i];
		}
		// set discount price high/low 
		if ($_ENV['application.cw']['discountsEnabled']) {
			// check for discounts applied to each sku 
			$discountAmount = CWgetSkuDiscountAmount($skusQuery['sku_id'][$i], 'sku_cost', null, null, $customer_id, $customer_type, $promo_code);
//echo "SKU: ".$skusQuery['sku_id'][$i]." - Amt: ".$discountAmount."<br />";
			// if a discount applies 
			if ($discountAmount > 0) {
				$discountedPrice = $skusQuery['sku_price'][$i] - $discountAmount;
				if ($discountedPrice > $discpricehigh) {
					$discpricehigh = $discountedPrice;
					$product['price_disc_high'] = $discountedPrice;
				}
				if ($discountedPrice < $discpricelow || ($discpricelow == 0 && $discountedPrice != 0)) {
					$discpricelow = $discountedPrice;
					$product['price_disc_low'] = $discountedPrice;
				}
			}
		}
		else {
			$product['price_disc_low'] = $discpricelow;
			$product['price_disc_high'] = $discpricelow;
		}
		if ($skusQuery['sku_stock'][$i] > $product['qty_max']) {
			$product['qty_max'] = $skusQuery['sku_stock'][$i];
		}
	}
	if ($product['price_low'] == 0) {
		$product['price_low'] = $product['price_high'];
	}
	if ($product['price_high'] == 0) {
		$product['price_high'] = $product['price_low'];
	}
	if ($datatype != 'mini') {
		$product['sku_ids'] = $idList;
	}
	// format price strings 
	$product['price_high'] = number_format($product['price_high'], 2, '.', '');
	$product['price_low'] = number_format($product['price_low'], 2, '.', '');
	$product['price_alt_high'] = number_format($product['price_alt_high'], 2, '.', '');
	$product['price_alt_low'] = number_format($product['price_alt_low'], 2, '.', '');
	// end skus/prices 
	// images child struct 
	if ($datatype == 'complex') {
		$product['images'] = array();
	}
	// query: get product images 
	$imagesQuery = CWquerySelectProductImages($product_id);
	// loop images query 
	for ($v=0; $v<$imagesQuery["totalRows"]; $v++) {
		$imgct++;
		if ($datatype == 'complex') {
			// create numbered substruct for each image 
			$product['images']["image".$imgct] = array();
			foreach ($imagesQuery as $ii => $valueArr) {
				if ($ii != "totalRows") {
					$product['images']["image".$imgct][$ii] = $valueArr[$v];
				}
			}
		}
		// set up list of image ids 
		$image_typelist[] = $imagesQuery['product_image_id'][$v];
		// set up list of image filenames 
		if (!in_array($imagesQuery['product_image_filename'][$v], $imagelist)) {
			$imagelist[] = $imagesQuery['product_image_filename'][$v];
		}
	}
	$product['image_ids'] = $image_typelist;
	$product['image_filenames'] = $imagelist;
	// /end images 
	// category(ies) child struct 
	if ($datatype != 'mini') {
		if ($datatype == 'complex') {
			$product["categories_primary"] = array();
		}
		// query: get product images 
		$catsQuery = CWquerySelectRelCategories($product_id);
		// loop images query 
		for ($n=0; $n<$catsQuery["totalRows"]; $n++) {
			$catct++;
			// create numbered substruct for each image 
			if ($datatype == 'complex') {
				$product["categories_primary"]["primary".$catct] = array();
				foreach ($catsQuery as $ii => $valueArr) {
					if ($ii != "totalRows") {
						$product["categories_primary"]["primary".$catct][$ii] = $valueArr[$n];
					}
				}
			}
			// add to list of ids 
			$catList[] = $catsQuery["category_id"][$n];
		}
		$product["categories_primary_ids"] = $catList;
	}
	// /end categories 
	// subcategory(ies) child struct 
	if ($datatype != 'mini') {
		if ($datatype == 'complex') {
			$product["categories_secondary"] = array();
		}
		$catsQuery = CWquerySelectRelScndCategories($product_id);
		// loop images query 
		$catct = 0;
		$catList = array();
		for ($n=0; $n<$catsQuery["totalRows"]; $n++) {
			$catct++;
			// create numbered substruct for each image 
			if ($datatype == 'complex') {
				$product["categories_secondary"]["secondary".$catct] = array();
				foreach ($catsQuery as $ii => $valueArr) {
					if ($ii != "totalRows") {
						$product["categories_secondary"]["secondary".$catct][$ii] = $valueArr[$n];
					}
				}
			}
			// add to list of ids 
			$catList[] = $catsQuery["secondary_id"][$n];
		}
		$product["categories_secondary_ids"] = $catList;
	}
	// end subcategories 
	// options child struct 
	$idList = array();
	if ($datatype != 'mini') {
		if ($datatype == 'complex') {
			$product["optiontypes"] = array();
		}
		// query: get options 
		$optionsQuery = CWquerySelectProductOptions($product_id);
		// loop images query 
		$lastTypeID = "";
		for ($n=0; $n<$optionsQuery["totalRows"]; $n++) {
			if ($optionsQuery["optiontype_id"][$n] != $lastTypeID) {
				$lastTypeID = $optionsQuery["optiontype_id"][$n];
				$optCt++;
				// create numbered substruct for each image 
				if ($datatype == 'complex') {
					$product["optiontypes"]["option".$optCt] = array();
					foreach ($optionsQuery as $ii => $valueArr) {
						if ($ii != "totalRows") {
							if ($ii == "option_values") {
								$optList = array();
								while ($n < $optionsQuery["totalRows"] && $optionsQuery["optiontype_id"][$n] == $lastTypeID) {
									if (!in_array($valueArr[$n], $optList)) {
										$optList[] = $valueArr[$n];
									}
									$product["optiontypes"]["option".$optCt][$ii] = $optList;
									$n++;
								}
								$n--;
							} else if ($ii != "sku_option_id") {
								$product["optiontypes"]["option".$optCt][$ii] = $valueArr[$n];
							}
						}
					}
				}
				// add to list of ids 
				if (!in_array($optionsQuery["optiontype_id"][$n], $idList)) {
					$idList[] = $optionsQuery["optiontype_id"][$n];
				}
			}
		}
		$product["optiontype_ids"] = $idList;
		$idList = array();
	}
	// /end options 
	// related products id list 
	if ($datatype != 'mini' && $_ENV["application.cw"]["appDisplayUpsell"] != false) {
		$upsellQuery = CWquerySelectUpsellProducts($product_id);
		for ($n=0; $n<$upsellQuery["totalRows"]; $n++) {
			$upsellList[] = $upsellQuery["product_id"][$n];
		}
	} else {
		$upsellList = array();
	}
	$product["related_product_ids"] = implode(",", $upsellList);
	// /end related 
	// reciprocal related products id list 
	if ($datatype != 'mini') {
		$recipQuery = CWquerySelectReciprocalUpsellProducts($product_id);
		for ($n=0; $n<$recipQuery["totalRows"]; $n++) {
			$recipList[] = $recipQuery["product_id"][$n];
		}
		$product["related_reciprocal_ids"] = implode(",", $recipList);
	}
	// /end reciprocal 
	return $product;
}

// // ---------- // Get Parent Product for Any SKU: CWgetProductBySku() // ---------- // 
function CWgetProductBySku($sku_id=0) {
    $returnVal = 0;
    $skuQuery = mysql_query("SELECT sku_product_id FROM cw_skus WHERE sku_id = ".CWqueryParam($sku_id),$_ENV["request.cwapp"]["db_link"]);
    if (mysql_num_rows($skuQuery)) {
        $qd = mysql_fetch_assoc($skuQuery);
        $returnVal = $qd['sku_product_id'];
    }

    return $returnVal;
}

// // ---------- // Get Custom Info Text: CWgetCustomInfo() // ---------- // 
function CWgetCustomInfo($phrase_id) {
    $lookup_id = $phrase_id;
    $returnVal = '';
    $infoQuery = '';

    $infoQuery = mysql_query("SELECT data_content FROM cw_order_sku_data WHERE data_id = ".CWqueryParam($lookup_id),$_ENV["request.cwapp"]["db_link"]);
    if (mysql_num_rows($infoQuery)) {
        $qd = mysql_fetch_assoc($infoQuery);
        $returnVal = $qd['data_content'];
    }

    return $returnVal;
}

// // ---------- // Get Sku Quantity: CWgetSkuQty() // ---------- // 
function CWgetSkuQty($sku_id) {

    $returnVal = 0;
    $skuQuery = '';

    $skuQuery = mysql_query("SELECT sku_stock FROM cw_skus WHERE sku_id = ".CWqueryParam($sku_id),$_ENV["request.cwapp"]["db_link"]);
    if (mysql_num_rows($skuQuery)) {
        $qd = mysql_fetch_assoc($skuQuery);
        $returnVal = $qd['sku_stock'];
    }

    return $returnVal;
}

// // ---------- // Get Product Display Status - true/false // ---------- // 
function CWproductAvailable($product_id)
{

    $productOK = false;
    $prodQuery = '';

    $prodQuery = CWqueryGetRS("
        SELECT DISTINCT p.product_id
        FROM cw_products p
        INNER JOIN cw_skus s
        ON p.product_id = s.sku_product_id
        WHERE NOT p.product_on_web = 0
        AND NOT p.product_archive = 1 " . ((!isset($_ENV["application.cw"]["appEnableBackOrders"]) || $_ENV["application.cw"]["appEnableBackOrders"] == 0) ? 'AND s.sku_stock > 0' : '') ."
        AND NOT s.sku_on_web = 0
        AND p.product_id = ".CWqueryParam($product_id));

    if ($prodQuery && $prodQuery["totalRows"]) {
        $productOK = true;
    }

    return $productOK;
}

// // ---------- // Get Product Image: CWgetImage() // ---------- // 
function CWgetImage($product_id, $image_type, $default_image="") {
	$imageSrc = '';
	$imagePath = '';
	$defaultImageSrc = '';
	$defaultImagePath = '';
	$imageQuery = '';
	$returnSrc = '';
	$imageQuery = CWqueryGetRS("SELECT cw_product_images.product_image_filename, cw_image_types.imagetype_folder
		FROM cw_image_types
		INNER JOIN cw_product_images
		ON cw_image_types.imagetype_id = cw_product_images.product_image_imagetype_id
		WHERE cw_product_images.product_image_product_id = ".CWqueryParam($product_id)."
		AND cw_product_images.product_image_imagetype_id = ".CWqueryParam($image_type)."");
	if ($imageQuery["totalRows"] > 0 || strlen(trim($default_image))) {
		// if going for default, we need the folder 
		if ($imageQuery["totalRows"] == 0) {
			$imageDirQuery = CWqueryGetRS("SELECT cw_image_types.imagetype_folder
				FROM cw_image_types
				INNER JOIN cw_product_images
				ON cw_image_types.imagetype_id = cw_product_images.product_image_imagetype_id
				WHERE cw_product_images.product_image_imagetype_id = ".CWqueryParam($image_type)."");
			if ($imageDirQuery["totalRows"]) $imageTypeDir = $imageDirQuery["imagetype_folder"][0];
			else $imageTypeDir = "orig";
		} else {
			$imageTypeDir = $imageQuery["imagetype_folder"][0];
		}
		$imageDir = $_ENV["application.cw"]["appCWContentDir"] . $_ENV["application.cw"]["appImagesDir"] . '/' . $imageTypeDir . '/';
		// Process the image 
		$defaultImageSrc = $imageDir . trim($default_image);
		$defaultImagePath = expandPath($defaultImageSrc);
		if ($imageQuery["totalRows"]) {
			$imageSrc = $imageDir . trim($imageQuery["product_image_filename"][0]);
			$imagePath = expandPath($imageSrc);
			// if the file exists, set up the path 
			if (file_exists($imagePath)) {
				$returnSrc = $imageSrc;
			// if file does not exist, attempt default image 
			} else if (strlen(trim($default_image)) && file_exists($defaultImagePath)) {
				$returnSrc = $defaultImageSrc;
			}
		} else if (strlen(trim($default_image)) && file_exists($defaultImagePath)) {
			$returnSrc = $defaultImageSrc;
		}
	}
	return $returnSrc;
}

// // ---------- // Get Top Selling Products (by sku) // ---------- // 
function CWgetBestSelling($max_products=5, $sub_ids=0)
{

    $productQuery = '';
    $sortQuery = '';
    $idList = $sub_ids;
    $keyIds = '';
    $itemsToAdd = '';

    if (!is_numeric($idListArray[0])) {
            $idList = '0';
    }

    $q_productQuery = mysql_query( "
    SELECT count(*) as prod_counter,
    p.product_id,
    p.product_name,
    p.product_preview_description,
    p.product_date_modified
    FROM cw_products p
    INNER JOIN cw_order_skus o
    INNER JOIN cw_skus s
    WHERE o.ordersku_sku = s.sku_id
    AND s.sku_product_id = p.product_id
    AND NOT p.product_on_web = 0
    AND NOT p.product_archive = 1
    AND NOT s.sku_on_web = 0
    GROUP BY product_id
    ORDER BY prod_counter DESC
    ",$_ENV["request.cwapp"]["db_link"]);

    $productQuery = array();
    while ($qd = mysql_fetch_assoc($q_productQuery)) {
        $productQuery[] = $qd;
    }

    foreach ($productQuery as $values) {
        $idList = $values['product_id'] . ",$idList";
    }

    // if not enough results, fill in from sub_ids 
    if (count($productQuery) < $max_products) {
            // number needed 
            $itemsToAdd = $max_products - count($productQuery);
            for ($i = 1; $i <= $itemsToAdd; $i++) {
                if (substr_count($idList, ',') >= $i) { 
                    $idListArray = explode(',', $idList);
                    $keyIds .= "," . $idList[$i];
                }
            }

            $q_resultsQuery = mysql_query("
            SELECT 0 as prod_counter,
            p.product_id,
            p.product_name,
            p.product_preview_description,
            p.product_date_modified
            FROM cw_products p
            WHERE p.product_id in(".CWqueryParam($keyIds).")
            AND NOT p.product_on_web = 0
            AND NOT p.product_archive = 1
            ORDER BY product_date_modified DESC
            ",$_ENV["request.cwapp"]["db_link"]);
    } else {
        $q_resultsQuery = mysql_query("
            SELECT count(*) as prod_counter,
            p.product_id,
            p.product_name,
            p.product_preview_description,
            p.product_date_modified
            FROM cw_products p
            INNER JOIN cw_order_skus o
            INNER JOIN cw_skus s
            WHERE o.ordersku_sku = s.sku_id
            AND s.sku_product_id = p.product_id
            AND NOT p.product_on_web = 0
            AND NOT p.product_archive = 1
            AND NOT s.sku_on_web = 0
            GROUP BY product_id
            ORDER BY prod_counter DESC
        ",$_ENV["request.cwapp"]["db_link"]);

    }

    // sort the results 
    $q_sortQuery = mysql_query("
    SELECT *
    FROM resultsQuery
    ORDER BY prod_counter DESC, product_date_modified
    ",$_ENV["request.cwapp"]["db_link"]);

    while ($qd = mysql_fetch_assoc($q_sortQuery)) {
        $sortQuery[] = $qd;
    }


    return $sortQuery;

}

// // ---------- // Get Category or Subcategory Text for Listings Page // ---------- // 
function CWgetListingText($category_id=0,$secondary_id=0) {
	$catQuery = '';
	$secondQuery = '';
	$catText = '';
	$secondText = '';
	// category description 
	if ($category_id > 0) {
		$catQuery_q = "
			SELECT category_description
			FROM cw_categories_primary
			WHERE category_id = '".CWqueryParam($category_id)."'";
		$catQuery = CWqueryGetRS($catQuery_q);
		// trim and save the output 
		if (isset($catQuery['category_description']) && is_array($catQuery['category_description']) && sizeof($catQuery['category_description']) && strlen(trim($catQuery['category_description'][0]))) {
			$catText = trim($catQuery['category_description'][0]);
		}
	}

	// secondary description 
	if ($secondary_id > 0) {
		$secondQuery_q = "
			SELECT secondary_description
			FROM cw_categories_secondary
			WHERE secondary_id = ".CWqueryParam($secondary_id);
		$secondQuery = CWqueryGetRS($secondQuery_q);
		// trim and save the output 
		if (isset($secondQuery['secondary_description']) && is_array($secondQuery['secondary_description']) && sizeof($secondQuery['secondary_description']) && strlen(trim($secondQuery['secondary_description'][0]))) {
			$secondText = trim($secondQuery['secondary_description'][0]);
		}
	}
	// combine content if both exist 
	$returnText = $catText . $secondText;
	return $returnText;
}

// // ---------- // Get Products by Customer: CWgetProductsByCustomer() // ---------- // 
function CWgetProductsByCustomer($customer_id) {
	$customerProductQuery = "SELECT DISTINCT p.product_id,
							p.product_name,
							p.product_preview_description,
							p.product_date_modified,
							p.product_on_web,
							p.product_archive,
							s.sku_on_web,
							s.sku_id,
							o.order_date,
							o.order_id,
							os.ordersku_unique_id,
							os.ordersku_unit_price,
							os.ordersku_quantity
							FROM cw_products p
							INNER JOIN cw_order_skus os
							INNER JOIN cw_skus s
							INNER JOIN cw_orders o
							WHERE os.ordersku_sku = s.sku_id
							AND s.sku_product_id = p.product_id
							AND o.order_customer_id = '".CWqueryParam($customer_id)."'
							AND o.order_id = os.ordersku_order_id
							ORDER BY p.product_name, o.order_date DESC";
	// GROUP BY p.product_id 
	return CWqueryGetRS($customerProductQuery);
}


// // ---------- Get Products (CARTWEAVER SEARCH) ---------- // 
function CWqueryProductSearch($category=null, $secondary=null, $keywords=null, $keywords_delimiters=null, $start_page=null, $max_rows=null ,$sort_by=null, $sort_dir=null, $start_row=null, $end_row=null, $search_skus=null, $match_type=null) {
	if ($category === null) $category = 0;
	if ($secondary === null) $secondary = 0;
	if ($keywords === null) $keywords = "";
	if ($keywords_delimiters === null) $keywords_delimiters = ",-|:";
	if ($start_page === null) $start_page = 1;
	if ($max_rows === null) $max_rows = 1000;
	if ($sort_by === null) $sort_by = "p.product_sort, p.product_name";
	if ($sort_dir === null) $sort_dir = "ASC";
	if ($start_row === null) $start_row = 0;
	if ($end_row === null) $end_row = 0;
	if ($search_skus === null) $search_skus = 1;
	if ($match_type === null) $match_type = "any";

	$sortbystr = trim($sort_by);
	$sortdirstr = strtolower(trim($sort_dir));
	$results = array();
	// delimiters for looping keyword list 
	$delimList = trim($keywords_delimiters);
	$delimChar = "";
	$delimEsc = "";
	$wordList = "";
	// count Search Results (sr) 
	$srCount = 0;
	// set up default list of IDs 
	$srIDs = '';
	$idList = '';
	// strip keyword values into a list of clean strings 
	if (strlen($delimList)) {
		for ($i=0; $i<strlen($delimList); $i++) {
			$delimChar = substr($delimList, $i, 1);
			$delimEsc .= "\\".$delimChar;
		}
	}
	// replace all non-permitted characters with a space, then replace spaces with comma for search list, make lowercase 
	$wordList = strtolower(str_replace(' ', ',', preg_replace("/[^a-zA-Z0-9".$delimEsc."]/", " ", str_replace('&quot;', ' ', $keywords))));
	// calculate start and end for final results 
	if ($start_row == 0) { $start_row = ($start_page * $max_rows) - $max_rows; } //+1
	if ($end_row == 0) { $end_row = $start_row + $max_rows - 1; } //-1
	// add actual column names to sortable strings: price|name|sort|id 
	if ($sortbystr == 'price') {
		$sortbystr = 's.sku_price';
	} elseif ($sortbystr == 'name') {
		$sortbystr = 'p.product_name';
	} elseif ($sortbystr == 'sort') {
		$sortbystr = 'p.product_sort';
	} elseif ($sortbystr == 'id') {
		$sortbystr = 's.sku_id';
	} else {
		$sortbystr = 'p.product_sort, p.product_name';
	}
	// verify and clean up sort direction string: asc|desc 
	if (!($sortdirstr == 'asc' || $sortdirstr == 'desc')) { $sortdirstr = 'asc'; }
	// default values 
	$query_rsProductSearch =  "
SELECT DISTINCT p.product_id, p.product_sort, p.product_name, MIN(s.sku_price), s.sku_id
FROM ";
	if ($secondary != 0) { $query_rsProductSearch .= "("; }
	$query_rsProductSearch .= "
	(cw_products p
	INNER JOIN cw_skus s
	ON p.product_id = s.sku_product_id)
";
	if ($secondary != 0) {
        $query_rsProductSearch .= "
	LEFT JOIN cw_product_categories_secondary sc
	ON p.product_id = sc.product2secondary_product_id)
";
	}
	if ($category != 0) {
        $query_rsProductSearch .= " 
        LEFT JOIN cw_product_categories_primary pc
        ON p.product_id = pc.product2category_product_id
        ";
	}
	$query_rsProductSearch .= "
	WHERE
        NOT p.product_on_web = 0
        AND NOT p.product_archive = 1 ";
	// if not allowing backorders, return stock gt 0 only 
	if ($_ENV['application.cw']['appEnableBackOrders'] == 0) {
		$query_rsProductSearch .= " AND s.sku_stock > 0 ";
	}
	// if keyword string provided 
	if (trim($keywords) != "") {
		// first condition holds optional or statements open 
		$query_rsProductSearch .= " AND (1=0 ";
		switch ($match_type) {
			case "all":
				// match all search terms 
				$query_rsProductSearch .= " OR ( ";
				$wordList_array = explode(",",$wordList);
				foreach ($wordList_array as $key => $searchTerm) {
					$query_rsProductSearch .= $_ENV["application.cw"]["sqlLower"]."(p.product_name) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
					if ($key != sizeof($wordList_array)-1) $query_rsProductSearch .= " AND ";
				}
				$query_rsProductSearch .= "
				)
				OR
				( ";
				foreach ($wordList_array as $key => $searchTerm) {
					$query_rsProductSearch .= $_ENV["application.cw"]["sqlLower"]."(p.product_preview_description) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
					if ($key != sizeof($wordList_array)-1) $query_rsProductSearch .= " AND ";
				}
				$query_rsProductSearch .= "
				)
				OR
				( ";
				foreach ($wordList_array as $key => $searchTerm) {
					$query_rsProductSearch .= $_ENV["application.cw"]["sqlLower"]."(p.product_special_description) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
					if ($key != sizeof($wordList_array)-1) $query_rsProductSearch .= " AND ";
				}
				$query_rsProductSearch .= "
				)
				OR
				( ";
				foreach ($wordList_array as $key => $searchTerm) {
					$query_rsProductSearch .= $_ENV["application.cw"]["sqlLower"]."(p.product_description) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
					if ($key != sizeof($wordList_array)-1) $query_rsProductSearch .= " AND ";
				}
				$query_rsProductSearch .= "
				)
				OR
				( ";
				foreach ($wordList_array as $key => $searchTerm) {
					$query_rsProductSearch .= $_ENV["application.cw"]["sqlLower"]."(p.product_keywords) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
					if ($key != sizeof($wordList_array)-1) $query_rsProductSearch .= " AND ";
				}
				$query_rsProductSearch .= "
				)
				";
				if ($search_skus) {
					$query_rsProductSearch .= " OR( ";
					foreach ($wordList_array as $key => $searchTerm) {
						$query_rsProductSearch .= $_ENV["application.cw"]["sqlLower"]."(s.sku_merchant_sku_id) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
						if ($key != sizeof($wordList_array)-1) $query_rsProductSearch .= " AND ";
					}
					$query_rsProductSearch .= "
					)
					";
				}
				break;

				// /end match=all 
				// match exact search phrase 
			case "phrase":
				// replace our word list delimiters with spaces 
				$searchTerm = $wordList;
				for ($i = 0; $i < strlen($delimList); $i++) {
					$delimChar = substr($delimList, $i, 1);
					$searchTerm = str_replace($delimList, " ", $delimChar);
				}
				$query_rsProductSearch .= "
				OR
				(
				".$_ENV["application.cw"]["sqlLower"]."(p.product_name) LIKE '%".CWqueryParam(trim($searchTerm))."%'
				)
				OR
				(
					".$_ENV["application.cw"]["sqlLower"]."(p.product_preview_description) LIKE '%".CWqueryParam(trim($searchTerm))."%'
				)
				OR
				(
					".$_ENV["application.cw"]["sqlLower"]."(p.product_special_description) LIKE '%".CWqueryParam(trim($searchTerm))."%'
				)
				OR
				(
					".$_ENV["application.cw"]["sqlLower"]."(p.product_description) LIKE '%".CWqueryParam(trim($searchTerm))."%'
				)
				OR
				(
					".$_ENV["application.cw"]["sqlLower"]."(p.product_keywords) LIKE '%".CWqueryParam(trim($searchTerm))."%'
				)";
				if ($search_skus) {
					$query_rsProductSearch .= "
					OR(
						".$_ENV["application.cw"]["sqlLower"]."(s.sku_merchant_sku_id) LIKE '%".CWqueryParam(trim($searchTerm))."%'
					)";
				}
				break;
			default:
				// /end match=all 
				// match any word (default) 
				// loop string of keywords 
				$wordList_array = explode(",",$wordList);
				foreach ($wordList_array as $key => $searchTerm) {
					$query_rsProductSearch .= " OR ".$_ENV["application.cw"]["sqlLower"]."(p.product_name) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
					$query_rsProductSearch .= " OR ".$_ENV["application.cw"]["sqlLower"]."(p.product_preview_description) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
					$query_rsProductSearch .= " OR ".$_ENV["application.cw"]["sqlLower"]."(p.product_special_description) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
					$query_rsProductSearch .= " OR ".$_ENV["application.cw"]["sqlLower"]."(p.product_description) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
					$query_rsProductSearch .= " OR ".$_ENV["application.cw"]["sqlLower"]."(p.product_keywords) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
				}
				if ($search_skus) {
					foreach ($wordList_array as $searchTerm) {
						$query_rsProductSearch .= "OR ".$_ENV["application.cw"]["sqlLower"]."(s.sku_merchant_sku_id) LIKE '%".CWqueryParam(trim($searchTerm))."%' ";
					}
				}
				// /end match=any 
		}
		$query_rsProductSearch .= " ) ";
	}
	// /end keywords 
	// categories / secondaries 
	if ($category != 0) {
		$query_rsProductSearch .= " AND pc.product2category_category_id = '".CWqueryParam($category)."'";
	}
	if ($secondary != 0) {
		$query_rsProductSearch .= " AND sc.product2secondary_secondary_id = '".CWqueryParam($secondary)."'";
	}
	// /end categories / secondaries 
	$query_rsProductSearch .= " AND NOT s.sku_on_web = 0 ";
	// group joined skus by product ID 
	$query_rsProductSearch .= " GROUP BY p.product_id ORDER BY ".CWqueryParam($sortbystr)." ".CWqueryParam($sortdirstr)."";
	// count Search Results (sr) 
	$rsProductSearch = CWqueryGetRS($query_rsProductSearch);
	$srCount = $rsProductSearch['totalRows'];
	$srIDs = array();
	$idList = array();
	if ($srCount > 0) {
		// set up default list of IDs 
		$srIDs = array();
		if (isset($rsProductSearch["product_id"]) && sizeof($rsProductSearch["product_id"])) { $srIDs = $rsProductSearch["product_id"]; }
		$idList = array();
		// if endrow gt actual records, set to match 
		if ($end_row > $srCount) {
			$end_row = $srCount;
		}
		// if startrow gt actual records, sort out start row 
		if ($start_row > $srCount) {
			$start_row = round($srCount/$max_rows) * $max_rows;//+1
		}
		// create new ID list 
		for ($ii = $start_row; $ii <= $end_row && $ii < sizeof($srIDs); $ii++) {
			$idList[] = $srIDs[$ii];//-1
		}
		// return '0' if no list is found 
	} else {
		$idList = array("0");
	}
	$results['idlist'] = implode(",", $idList);
	$results['count'] = sizeof($srIDs);
	return $results;
}


// // ---------- Get Product Details ---------- // 

function CWquerySelectProductDetails($product_id) {
    $rsProductDetails = "
       SELECT product_id,
        product_merchant_product_id,
        product_name,
        product_description,
        product_preview_description,
        product_sort,
        product_on_web,
        product_archive,
        product_ship_charge,
        product_tax_group_id,
        product_date_modified,
        product_special_description,
        product_keywords,
        product_out_of_stock_message,
        product_custom_info_label
        FROM cw_products
        WHERE product_id = ".CWqueryParam($product_id);

    return CWqueryGetRS($rsProductDetails);
}

// // ---------- List Product skus ---------- // 
function CWquerySelectskus($product_id, $omit_inactive=true) {
    $query = "
        SELECT sku_id,
        sku_merchant_sku_id,
        sku_product_id,
        sku_price,
        sku_weight,
        sku_stock,
        sku_on_web,
        sku_sort,
        sku_alt_price,
        sku_ship_base
        FROM cw_skus
        WHERE sku_product_id = ".CWqueryParam($product_id);

   if ($omit_inactive) {
        $query .= " AND not sku_on_web = 0 ";
        if (!isset($_ENV["application.cw"]["appEnableBackOrders"]) || $_ENV["application.cw"]["appEnableBackOrders"] == 0) {
            $query .= " AND cw_skus.sku_stock > 0";
        }
   }

    $query .= " ORDER BY sku_sort, sku_price";

    return CWqueryGetRS($query);
}

// // ---------- Get SKU Options ---------- // 
function CWquerySelectSkuOptions($product_id, $sku_id = 0) {

    $query = "
SELECT
        cw_option_types.optiontype_name,
        cw_skus.sku_id,
        cw_skus.sku_merchant_sku_id,
        cw_skus.sku_price,
        cw_skus.sku_alt_price,
        cw_skus.sku_ship_base,
        cw_skus.sku_sort,
        cw_options.option_name,
        cw_options.option_sort,
        cw_skus.sku_stock,
        cw_options.option_id
FROM cw_skus
INNER JOIN ((cw_option_types
        INNER JOIN cw_options
                ON cw_option_types.optiontype_id = cw_options.option_type_id)
                INNER JOIN cw_sku_options
                        ON cw_options.option_id = cw_sku_options.sku_option2option_id)
ON cw_skus.sku_id = cw_sku_options.sku_option2sku_id
WHERE
        cw_skus.sku_product_id = ".CWqueryParam($product_id)."
        AND cw_skus.sku_on_web = 1
";
    if (!isset($_ENV["application.cw"]["appEnableBackOrders"]) || $_ENV["application.cw"]["appEnableBackOrders"] == 0) {
        $query .= " AND cw_skus.sku_stock > 0 ";
    }

    if ($sku_id > 0) {
        $query .= " AND cw_skus.sku_id = $sku_id ";
    }

    $query .= " ORDER BY cw_skus.sku_sort, cw_skus.sku_merchant_sku_id, cw_options.option_sort";

    return CWqueryGetRS($query);
}

// // ---------- Get Sku Details // ---------- // 
function CWquerySkuDetails($sku_id) {
	$query = "SELECT
			s.sku_id,
			s.sku_merchant_sku_id,
			s.sku_product_id,
			s.sku_price,
			s.sku_weight,
			s.sku_stock,
			s.sku_on_web,
			s.sku_alt_price,
			s.sku_ship_base
			FROM cw_skus s
			WHERE sku_id = ".CWqueryParam($sku_id)."";
	$q = CWqueryGetRS($query);
	if ($q["totalRows"]) {
		$qd = array();
		foreach ($q as $colName => $valArr) {
			if ($colName != "totalRows") {
				$qd[$colName] = $valArr[0];
			}
		}
	}
	else {
		$qd = array("sku_id" => "",
			"sku_merchant_sku_id" => "",
			"sku_product_id" => "",
			"sku_price" => "",
			"sku_weight" => "",
			"sku_stock" => "",
			"sku_on_web" => "",
			"sku_alt_price" => "",
			"sku_ship_base" => "");
	}
	return $qd;
}

// // ---------- // Set SKU Stock Quantity // ---------- // 
function CWsetSkuStock($sku_id, $qty_purchased = 1) {
        $query = "UPDATE cw_skus
                SET sku_stock = sku_stock - ".CWqueryParam($qty_purchased)." WHERE sku_id = ".CWqueryParam($sku_id);
        mysql_query($query,$_ENV["request.cwapp"]["db_link"]);
}

// // ---------- List Product Images ---------- // 
function CWquerySelectProductImages($product_id=null, $imagetype_id=null, $imagetype_upload_group=null) {
	if ($product_id === null) $product_id = 0;
	if ($imagetype_id === null) $imagetype_id = 0;
	if ($imagetype_upload_group === null) $imagetype_upload_group = 0;
	$rsProductImages = "SELECT *
	FROM cw_product_images ii
	INNER JOIN cw_image_types tt
	WHERE tt.imagetype_id = ii.product_image_imagetype_id
	";
	if ($product_id > 0) {
		$rsProductImages .= "AND ii.product_image_product_id = ".CWqueryParam($product_id)."
	";
	}
	if ($imagetype_id > 0) {
		$rsProductImages .= "AND ii.product_image_imagetype_id = ".CWqueryParam($imagetype_id)."
	";
	}
	if ($imagetype_upload_group > 0) {
		$rsProductImages .= "AND tt.imagetype_upload_group = ".CWqueryParam($imagetype_upload_group)."
	";
	}
	$rsProductImages .= "ORDER BY ii.product_image_sortorder, tt.imagetype_sortorder";
	return CWqueryGetRS($rsProductImages);
}

// // ---------- Get Product Related Categories ---------- // 
function CWquerySelectRelCategories($product_id) {
	$rsRelCategories = "SELECT cc.category_id, cc.category_name
	FROM cw_product_categories_primary rr
	INNER JOIN cw_categories_primary cc
	WHERE rr.product2category_product_id = ".CWqueryParam($product_id)."
	AND cc.category_id = rr.product2category_category_id";
	return CWqueryGetRS($rsRelCategories);
}

// // ---------- Get Product Related Secondary Categories ---------- // 
function CWquerySelectRelScndCategories($product_id) {
	$rsRelScndCategories = "SELECT sc.secondary_id, sc.secondary_name
	FROM cw_product_categories_secondary rr
	INNER JOIN cw_categories_secondary sc
	WHERE rr.product2secondary_product_id = ".CWqueryParam($product_id)."
	AND sc.secondary_id = rr.product2secondary_secondary_id";
	return CWqueryGetRS($rsRelScndCategories);
}

// // ---------- Get Product Related Options  ---------- // 
function CWquerySelectProductOptions($product_id=0) {
    $query = "SELECT DISTINCT ";
    if ($product_id > 0) {
        $query .= " cw_sku_options.sku_option_id, ";
    }
    $query .= "cw_option_types.optiontype_id, cw_option_types.optiontype_name ";
    if ($product_id > 0) {
        $query .= " ,cw_options.option_name as option_values";
    }

    $query .= " FROM ";

    if ($product_id > 0) {
        $query .= "
        cw_products
        INNER JOIN ((((
                        cw_skus
                                INNER JOIN cw_sku_options
                                ON cw_sku_options.sku_option2sku_id = cw_skus.sku_id)
                                        INNER JOIN cw_options
                                        ON cw_options.option_id = cw_sku_options.sku_option2option_id)
                                                INNER JOIN cw_product_options
                                                ON cw_product_options.product_options2prod_id = ".CWqueryParam($product_id).")
                                                        INNER JOIN cw_option_types
                                                        ON cw_option_types.optiontype_id = cw_product_options.product_options2optiontype_id)
                        ON cw_skus.sku_product_id = ".CWqueryParam($product_id)."
        WHERE cw_products.product_id= ".CWqueryParam($product_id)."
        AND cw_option_types.optiontype_id = cw_options.option_type_id
        AND NOT cw_options.option_archive = 1 ";
    }
    else {
        $query .= "
        cw_skus
        INNER JOIN ((
                cw_sku_options
                                INNER JOIN cw_options
                                ON cw_options.option_id = cw_sku_options.sku_option2option_id)
                                                INNER JOIN cw_option_types
                                                ON cw_option_types.optiontype_id = cw_options.option_type_id)
                ON cw_sku_options.sku_option2sku_id = cw_skus.sku_id
        WHERE NOT optiontype_archive = 1
        AND NOT optiontype_deleted = 1 ";
    }
    
    $query .= " ORDER BY ";

    if ($product_id > 0) {
        $query .= " cw_option_types.optiontype_sort, ";
    }
    $query .= "cw_option_types.optiontype_name ";
    if ($product_id > 0) {
        $query .= ",cw_options.option_sort ";
    }
    $query .= ",cw_options.option_name";

    return CWqueryGetRS($query);
}

// // ---------- List Related Product Options  ---------- // 
function CWquerySelectRelOptions($product_id, $product_options=null, $allow_backorders=null) {
	if ($product_options === null) $product_options = "";
	if ($allow_backorders === null) $allow_backorders = $_ENV["application.cw"]["appEnableBackOrders"];
	$rsGetOptnRelIDs = "SELECT cw_sku_options.sku_option_id,
					 cw_options.option_name,
					 cw_options.option_sort,
					 cw_options.option_id,
					 cw_skus.sku_merchant_sku_id,
					 cw_skus.sku_id,
					 cw_skus.sku_stock,
					 cw_option_types.optiontype_text,
					 cw_option_types.optiontype_name
				FROM ((cw_skus
					INNER JOIN cw_sku_options
							ON cw_skus.sku_id = cw_sku_options.sku_option2sku_id)
							INNER JOIN cw_options
							ON cw_options.option_id = cw_sku_options.sku_option2option_id)
						INNER JOIN cw_option_types
								ON cw_option_types.optiontype_ID = cw_options.option_type_id
				WHERE cw_skus.sku_product_id = ".CWqueryParam($product_id)."
					AND NOT cw_skus.sku_on_web = 0
				";
	if ($allow_backorders != true) {
		$rsGetOptnRelIDs .= "AND cw_skus.sku_stock > 0
				";
	}
	// only use this if there are some options 
	$poArr = $product_options;
	if (!is_array($poArr)) $poArr = explode(",", $poArr);
	if (sizeof($poArr) > 0 && is_numeric($poArr[0])) {
		$rsGetOptnRelIDs .= "AND cw_options.option_type_id IN (".CWqueryParam(implode(",", $poArr)).")
				";
	}
	$rsGetOptnRelIDs .= "ORDER BY cw_options.option_sort,
			 cw_options.option_name";
	return CWqueryGetRS($rsGetOptnRelIDs);
}


// // ---------- Get Product Optiontypes IDs ---------- // 
function CWquerySelectOptiontypes($product_id = 0) {
    $query = "SELECT DISTINCT cw_option_types.optiontype_id, cw_option_types.optiontype_name ";

    if ($product_id > 0) {
        $query .= " , cw_options.option_id, cw_options.option_name, cw_options.option_sort ";
    }

    $query .= " FROM ";

    if ($product_id > 0) {
        $query .= " cw_products 
        INNER JOIN (( cw_option_types
                INNER JOIN cw_options ON cw_option_types.optiontype_id = cw_options.option_type_id)
                INNER JOIN cw_product_options ON cw_option_types.optiontype_id = cw_product_options.product_options2optiontype_id) ON cw_products.product_id = cw_product_options.product_options2prod_id
        WHERE cw_products.product_id= $product_id AND NOT cw_options.option_archive = 1 ";
    }
    else {

        $query .= " cw_option_types
                    WHERE NOT optiontype_archive = 1
                            AND NOT optiontype_deleted = 1
                            ";
    }

    $query .= " ORDER BY 
         cw_option_types.optiontype_sort,
         cw_option_types.optiontype_name ";

    if ($product_id > 0) {
        $query .= ",cw_options.option_name
                   ,cw_options.option_sort ";
    }

    return CWqueryGetRS($query);
}


// // ---------- Get Related Products for a Specific Product ---------- // 
function CWquerySelectUpsellProducts($product_id) {
	$rsSelectUpsell = "SELECT p.product_id,
	p.product_merchant_product_id,
	p.product_name,
	u.upsell_id
	FROM cw_products p, cw_product_upsell u, cw_skus s
	WHERE p.product_id = u.upsell_2product_id
	AND u.upsell_product_id = ".CWqueryParam($product_id)."
	AND s.sku_product_id = p.product_id
	AND NOT p.product_archive = 1 ";
	if (!isset($_ENV["application.cw"]["appEnableBackOrders"]) || !$_ENV["application.cw"]["appEnableBackOrders"]) {
		$rsSelectUpsell .= "AND s.sku_stock > 0 ";
	}
	$rsSelectUpsell .= "GROUP BY p.product_id
	ORDER BY p.product_name, p.product_merchant_product_id";
	return CWqueryGetRS($rsSelectUpsell);
}

// // ---------- Get Reciprocal Related Products for a Specific Product ---------- // 
function CWquerySelectReciprocalUpsellProducts($product_id) {
	$rsSelectRecipUpsell = "SELECT p.product_id,
	p.product_merchant_product_id,
	p.product_name,
	u.upsell_id
	FROM cw_products p, cw_product_upsell u, cw_skus s
	WHERE p.product_id = u.upsell_product_id
	AND u.upsell_2product_id = ".CWqueryParam($product_id)."
	AND s.sku_product_id = p.product_id
	AND NOT p.product_archive = 1 ";
	if (!isset($_ENV["application.cw"]["appEnableBackOrders"]) || !$_ENV["application.cw"]["appEnableBackOrders"]) {
		$rsSelectRecipUpsell .= "AND s.sku_stock > 0 ";
	}
	$rsSelectRecipUpsell .= "GROUP BY p.product_id
	ORDER BY p.product_name, p.product_merchant_product_id";
	return CWqueryGetRS($rsSelectRecipUpsell);
}


// // ---------- // Get New Products (by date_modified) // ---------- // 
function CWqueryNewProducts($max_products = 5)
{
    $query = "SELECT
        p.product_id,
        p.product_name,
        p.product_preview_description,
        p.product_date_modified
        FROM cw_products p
        WHERE NOT p.product_on_web = 0
        AND NOT p.product_archive = 1
        ORDER by p.product_date_modified DESC
        ";

    return CWqueryGetRS($query);
}

// // ---------- List Top Selling Products ---------- // 
function CWquerySelectTopProducts($show_ct=15) {
	if ($show_ct === null) $show_ct = 15;
	$rsTopProductsQuery = CWquerySortRS(CWqueryGetRS("SELECT count(*) as prod_counter,
		p.product_id,
		p.product_name,
		p.product_merchant_product_id,
		p.product_date_modified
		FROM cw_products p
		, cw_order_skus o
		, cw_skus s
		WHERE o.ordersku_sku = s.sku_id
		AND s.sku_product_id = p.product_id
		GROUP BY p.product_id, p.product_name, p.product_merchant_product_id, p.product_date_modified
		ORDER BY product_date_modified"), "desc", "prod_counter".(($show_ct > 0) ? " LIMIT ".CWqueryParam($show_ct) : "" ));
	return $rsTopProductsQuery;
}


// /////////////// 
// CATEGORY QUERIES 
// /////////////// 

// // ---------- Get All Active Categories ---------- // 
function CWquerySelectCategories($show_empty=NULL) {
	if ($show_empty === NULL) $show_empty = $_ENV["application.cw"]["appDisplayEmptyCategories"];
	$rsCatsSql = "";
	$rsCats = "SELECT
		category_id,
		category_name,
		category_archive,
		category_sort,
		category_description,
		count(distinct product2category_product_id) as catProdCount
		FROM cw_categories_primary
		";
	if ($show_empty) {
		$rsCats .= "LEFT OUTER JOIN cw_product_categories_primary
		";
	} else {
		$rsCats .= "INNER JOIN cw_product_categories_primary
		";
	}
	$rsCats .= "ON cw_product_categories_primary.product2category_category_id = cw_categories_primary.category_id
		LEFT OUTER JOIN cw_products
		ON cw_products.product_id = cw_product_categories_primary.product2category_product_id
		LEFT OUTER JOIN cw_skus
		ON cw_skus.sku_product_id = cw_products.product_id
		WHERE NOT category_archive = 1
		";
	if (!$show_empty) {
		$rsCats .= "AND NOT cw_products.product_on_web = 0
			AND NOT cw_products.product_archive = 1
			AND NOT cw_skus.sku_on_web = 0
			";
		if (!isset($_ENV["application.cw"]["appEnableBackOrders"]) || !$_ENV["application.cw"]["appEnableBackOrders"]) {
			$rsCats .= "AND cw_skus.sku_stock > 0
			";
		}
	}
	$rsCats .= "GROUP BY
		category_id,
		category_name,
		category_archive,
		category_sort,
		category_description
		ORDER BY
		category_sort,
		category_name";
	return CWqueryGetRS($rsCats);
}

// // ---------- Get All Active Secondary Categories ---------- // 
function CWquerySelectSecondaries($show_empty=false,$relate_cats=false,$cat_id=0)
{
    // related categories / subcats 
    if ($relate_cats) {
        $query = "
    SELECT
            DISTINCT
            category_name,
            category_id,
            secondary_id,
            secondary_name,
            secondary_archive,
            secondary_sort,
            secondary_description,
            category_sort,
            count(distinct product2secondary_product_id) as catProdCount
            FROM cw_categories_secondary
                    INNER JOIN (cw_categories_primary
                            INNER JOIN (cw_product_categories_primary
                                    INNER JOIN cw_product_categories_secondary
                                    ON cw_product_categories_secondary.product2secondary_product_id = cw_product_categories_primary.product2category_product_id)
                            ON cw_categories_primary.category_id = cw_product_categories_primary.product2category_category_id)
                    ON cw_categories_secondary.secondary_id = cw_product_categories_secondary.product2secondary_secondary_id

                    LEFT OUTER JOIN cw_products
                    ON cw_products.product_id = cw_product_categories_secondary.product2secondary_product_id
                    LEFT OUTER JOIN cw_skus
                    ON cw_skus.sku_product_id = cw_products.product_id

                    WHERE NOT secondary_archive = 1
    ";
        if ($cat_id > 0) {
            $query .= " AND cw_categories_primary.category_id = ".CWqueryParam($cat_id);
        }

        if (!$show_empty) {
            $query .= "
    AND NOT cw_products.product_on_web = 0
                            AND NOT cw_products.product_archive = 1
                            AND NOT cw_skus.sku_on_web = 0
            ";
            if (!isset($_ENV["application.cw"]["appEnableBackOrders"]) || !$_ENV["application.cw"]["appEnableBackOrders"]) {
                    $query .= " AND cw_skus.sku_stock > 0 ";
            }
        }

        $query .= "
            GROUP BY
            category_sort,
            category_name,
            secondary_id,
            secondary_name,
            secondary_archive,
            secondary_sort,
            secondary_description
            ORDER BY
            category_sort,
            category_name,
            secondary_sort,
            secondary_name
        ";
    }
    else {
        $query = "SELECT secondary_id,
                secondary_name,
                secondary_archive,
                secondary_sort,
                secondary_description,
                count(distinct product2secondary_product_id) as catProdCount 
                FROM cw_categories_secondary ";
        if ($show_empty) {
            $query .= "LEFT OUTER JOIN cw_product_categories_secondary ";
        }
        else {
            $query .= "INNER JOIN cw_product_categories_secondary ";
        }

        $query .= "ON  cw_product_categories_secondary.product2secondary_secondary_id = cw_categories_secondary.secondary_id
                LEFT OUTER JOIN cw_products
                ON cw_products.product_id = cw_product_categories_secondary.product2secondary_product_id
                LEFT OUTER JOIN cw_skus
                ON cw_skus.sku_product_id = cw_products.product_id
                WHERE NOT secondary_archive = 1 ";

        if (!$show_empty) {
            $query .= "AND NOT cw_products.product_on_web = 0
                        AND NOT cw_products.product_archive = 1
                        AND NOT cw_skus.sku_on_web = 0 ";
            if (!isset($_ENV["application.cw"]["appEnableBackOrders"]) || !$_ENV["application.cw"]["appEnableBackOrders"]) {
                $query .= " AND cw_skus.sku_stock > 0 ";
            }
        }

        $query .= "
        GROUP BY
        secondary_id,
        secondary_name,
        secondary_archive,
        secondary_sort,
        secondary_description
        ORDER BY
        secondary_sort,
        secondary_name
        ";
    }

    return CWqueryGetRS($query);

}


// // ---------- Get Category Details ---------- // 

function CWquerySelectCatDetails($cat_id,$cat_name=NULL) {
        $rsSelectCatDetails = "SELECT *
                                FROM cw_categories_primary
                                WHERE ";
        if($cat_id) {
                $rsSelectCatDetails.= "category_id = '".CWqueryParam($cat_id)."'";
        } else {
                $rsSelectCatDetails.= $_ENV["application.cw"]["sqlLower"]."('category_name') = '".CWqueryParam(strtolower($cat_name))."'";
        }
        return CWqueryGetRS($rsSelectCatDetails);
}


// // ---------- Get Secondary Category Details ---------- // 

function CWquerySelectSecondaryCatDetails($cat_id,$cat_name=NULL) {
        $rsSelectCatDetails = "SELECT *
								FROM cw_categories_secondary
								WHERE ";
        if($cat_id) {
                $rsSelectCatDetails.= "secondary_id = '".CWqueryParam($cat_id)."'";
        } else {
                $rsSelectCatDetails.= "".$_ENV["application.cw"]["sqlLower"]."(secondary_name) = '".CWqueryParam(strtolower($cat_name))."'";
        }
        return CWqueryGetRS($rsSelectCatDetails);
}

?>