<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, all Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-discount.php
File Date: 2012-07-09
Description:
Manages product/sku, shipping, or order discount calculations and related queries
Dependencies: product functions, global functions in head of calling page
==========================================================
*/
/**
 * Get discounts applicable to any order
 * 
 */
function CWgetCartDiscountData($cart, $customer_id=null, $customer_type=null, $promo_code=null, $compare_date=null, $return_rejects=null, $discount_id=null) {
	if ($customer_id === null) $customer_id = 0;
	if ($customer_type === null) $customer_type = 0;
	if ($promo_code === null) $promo_code = "";
	if ($compare_date === null) $compare_date = CWtime();
	if ($return_rejects === null) $return_rejects = true;
	if ($discount_id === null) $discount_id = 0;
	// NOTES:
	//Quantity and sku ids are provided to check for discount requirements - the discount amount is only calculated for the order total.
	//The discount matching selections are processed in the order they appear in the code.
	//The first filter to cause a rejection will prevent any further checks,
	//and will return an error message specific to that rejection.
	//To change the priority of the filtering and response messages,
	//the order of the filter checks may be rearranged below.
	//Exclusive discount checking should be left last.
	$discountQuery = '';
	$skuId = '';
	$discountData = array();
	$responseStruct = array();
	$matchStatus = '';
	$matchResponse = '';
	$matchAmount = 0;
	$matchPercent= 0;
	$exclusiveFound = false;
	$exclusiveFoundName = '';
	$matchQuery = '';
	$catsQuery = '';
	$scndCatsQuery = '';
	$discountUsedCt = '';
	$customerUsedCt = '';
	$cartTotal = 0;
	$cartQty = 0;
	// get actual totals from cart data 
	if (isset($cart["carttotals"]["sub"])) {
		$cartTotal = $cart["carttotals"]["sub"];
	}
	if (isset($cart["carttotals"]["skuCount"])) {
		$cartQty = $cart["carttotals"]["skuCount"];
	}
	// If no ID provided, get ALL POSSIBLE MATCHES - stored in application memory on first run 
	if ($discount_id == 0) {
		// get all active discounts 
		$discountQuery = CWgetDiscountData();
		// if id provided, get only this discount 
	} else {
		$discountQuery = CWgetDiscountData(null, $discount_id);
	}
	// /// /// 
	// /// START DISCOUNT FILTERS /// 
	// /// /// 
	// loop matching discounts, comparing each, returning OK message or reason for rejection 
	for ($i=0; $i<$discountQuery["totalRows"]; $i++) {
		$responseStruct = array();
		$matchStatus = '';
		$matchResponse = '';
		// match discount type 
		if (strtolower(trim($discountQuery["discount_type"][$i])) != 'order_total') {
			$matchStatus = false;
			$matchResponse = 'Discount type '.$discountQuery["discount_type"][$i].' not applicable';
			// if an exclusive match has already been found, all others are set to false 
		} else if ($exclusiveFound) {
			$matchStatus = false;
			$matchResponse = 'Exclusive discount '.$exclusiveFoundName.' cannot be used with other offers';
		}
		// add fixed values into response 
		$responseStruct["discount_id"] = $discountQuery["discount_id"][$i];
		$responseStruct["discount_merchant_id"] = $discountQuery["discount_merchant_id"][$i];
		$responseStruct["discount_name"] = $discountQuery["discount_name"][$i];
		$responseStruct["discount_promotional_code"] = $discountQuery["discount_promotional_code"][$i];
		$responseStruct["discount_exclusive"] = $discountQuery["discount_exclusive"][$i];
		$responseStruct["discount_priority"] = $discountQuery["discount_priority"][$i];
		$responseStruct["discount_type"] = $discountQuery["discount_type"][$i];
		// get conditional values 
		if ($discountQuery["discount_show_description"][$i]) {
			$responseStruct["discount_description"] = $discountQuery["discount_description"][$i];
		} else {
			$responseStruct["discount_description"] = "";
		}
		if ($discountQuery["discount_global"][$i]) {
			$responseStruct["association_method"] = "global";
		} else {
			$responseStruct["association_method"] = $discountQuery["discount_association_method"][$i];
		}
		// get or create remaining values - amount, percent, response message 
		// PROMO CODE: if provided, or if discount requires one, check for matching string 
		if ($matchStatus !== false && (strlen(trim($promo_code)) || strlen(trim($discountQuery["discount_promotional_code"][$i])))) {
			$promoCodeList = explode("^", $promo_code);
			if (in_array($discountQuery["discount_promotional_code"][$i], $promoCodeList) === false && trim($discountQuery["discount_promotional_code"][$i]) != $promo_code) {
				$matchStatus = false;
				$matchResponse = 'Promo code '.trim($promo_code).' not matched';
			}
		}
		// DATE: start/stop dates 
		if ($matchStatus !== false) {
			$endDatePlusOne = ""; if ($discountQuery["discount_end_date"][$i] != "") $endDatePlusOne = strtotime($discountQuery["discount_end_date"][$i]) + 24*3600; //add one day to end date
			if (!(strtotime($discountQuery["discount_start_date"][$i]) <= cartweaverStrtotime($compare_date) && ($discountQuery["discount_end_date"][$i] == '' || $endDatePlusOne >= cartweaverStrtotime($compare_date)))) {
				$matchStatus = false;
				$matchResponse = 'Discount not currently available';
			}
		}
		// PRODUCT ASSOCIATIONS 
		// if not global, check matching records (sku, product, category association methods) 
		if ($matchStatus !== false && $discountQuery["discount_global"][$i] != 1) {
			// for products, assume no match until one is found 
			$matchStatus = false;
			// loop cart items 
			for ($cartLine = 0; $cartLine < sizeof($cart["cartitems"]); $cartLine++) {
				$skuId = $cart["cartitems"][$cartLine]["skuID"];
				$skuqty = $cart["cartitems"][$cartLine]["quantity"];
				$productId = CWgetProductBySku($cart["cartitems"][$cartLine]["skuID"]);
				// look up matching items by association type 
				switch ($discountQuery["discount_association_method"][$i]) {
					// PRODUCTS: straight match by product id 
					case "products":
						$matchQuerySql = "SELECT discount2product_discount_id
											FROM cw_discount_products
											WHERE discount2product_product_id = ".$productId."
											AND discount2product_discount_id = ".$discountQuery["discount_id"][$i]."";
						$matchQuery = CWqueryGetRS($matchQuerySql);
						if ($matchQuery["totalRows"]) {
							$matchStatus = true;
						}
						break;

					// SKUS: straight match by sku id 
					case "skus":
						$matchQuerySql = "SELECT discount2sku_discount_id
											FROM cw_discount_skus
											WHERE discount2sku_sku_id = ".$skuId."
											AND discount2sku_discount_id = ".$discountQuery["discount_id"][$i]."";
						$matchQuery = CWqueryGetRS($matchQuerySql);
						if ($matchQuery["totalRows"]) {
							$matchStatus = true;
						}
						break;

					// CATEGORIES: get categories & secondaries the product belongs to, check for match 
					case "categories":
						// QUERY: get categories for this product 
						$catsQuery = CWquerySelectRelCategories($productId);
						$scndcatsQuery = CWquerySelectRelScndCategories($productId);
						$catList = "0";
						$scndCatList = "0";
						if (isset($catsQuery["category_id"]) && is_array($catsQuery["category_id"])) {
							$catList = implode(",", $catsQuery["category_id"]);
						}
						if (isset($scndCatsQuery["secondary_id"]) && is_array($scndCatsQuery["secondary_id"])) {
							$scndCatList = implode(",", $scndcatsQuery["secondary_id"]);
						}
						// check for match by type and id 
						$matchQuerySql = "SELECT discount_category_id
											FROM cw_discount_categories
											WHERE discount2category_discount_id = ".$discountQuery["discount_id"][$i]."
											AND ((
												discount_category_type = 1
												AND discount2category_category_id in(".$catList.")
												) OR (
												discount_category_type = 2
												AND discount2category_category_id in(".$scndCatList.")
											))";
						$matchQuery = CWqueryGetRS($matchQuerySql);
						if ($matchQuery["totalRows"]) {
							$matchStatus = true;
						}
						break;
				}
				// /end product association types 
				// ITEM QTY 
				if ($matchStatus !== false && $skuqty > 0 && $discountQuery["discount_filter_item_qty"][$i] == 1) {
					if (($skuqty < $discountQuery["discount_item_qty_min"][$i]) && ($discountQuery["discount_item_qty_min"][$i] != 0)) {
						$matchStatus = false;
						$matchResponse = 'Add '.($discountQuery["discount_item_qty_min"][$i] - $skuqty).' more of this item to your cart to activate this discount';
					} else if (($discountQuery["discount_filter_item_qty"][$i] != 0) && ($discountQuery["discount_item_qty_max"][$i] != 0) && ($skuqty > $discountQuery["discount_item_qty_max"][$i])) {
						$matchStatus = "false";
						$matchResponse = 'Only available for quantities of '.$discountQuery["discount_item_qty_max"][$i].' or less';
					}
				}
				// /end item qty 
				// end the loop if at least one item matches 
				if ($matchStatus === true){
					break;
				}
			}
			// if no match was found, set message 
			if ($matchStatus === false) {
				$matchResponse = 'Discount does not apply to items in cart';
			}
			// /end product associations 
		}
		// CUSTOMER ID 
		if (($matchStatus !== false) && ($customer_id != 0) && strlen(trim($customer_id)) && strlen(trim($discountQuery["discount_customer_id"][$i])) && ($discountQuery["discount_filter_customer_id"][$i] == 1)) {
			$customerIdList = explode(",", str_replace(" ", "", $discountQuery["discount_customer_id"][$i]));
			if (!in_array($customer_id, $customerIdList)) {
				$matchStatus = false;
				$matchResponse = 'Discount not available for current customer';
			}
		// if no customer id provided, return false if filtered by id 
		} else if (($matchStatus !== false) && ($discountQuery["discount_filter_customer_id"][$i] == 1)) {
			$matchStatus = false;
			$matchResponse = 'Customer ID not matched. Create an account to use this discount';
		}
		// CUSTOMER TYPE 
		if (($matchStatus !== false) && ($customer_type != 0) && strlen(trim($customer_type)) && strlen(trim($discountQuery["discount_customer_type"][$i])) && ($discountQuery["discount_filter_customer_type"][$i] == 1)) {
			$customerTypeList = explode(",", str_replace(" ", "", $discountQuery["discount_customer_type"][$i]));
			if (!in_array($customer_type, $customerTypeList)) {
				$matchStatus = false;
				$matchResponse = 'Discount not available for current customer type';
			}
		} else if ($discountQuery["discount_filter_customer_type"][$i] == 1) {
			// if no customer type provided, return false if filtered by type 
			$matchStatus = false;
			$matchResponse = 'Customer type not matched. Create an account to use this discount';
		}
		// DISCOUNT LIMIT (past usage) 
		if ($matchStatus !== false && $discountQuery["discount_limit"][$i] != 0) {
			// function returns number of times  
			$discountUsedCt = CWGetDiscountUsage($discountQuery["discount_id"][$i]);
			if ($discountQuery["discount_limit"][$i] <= $discountUsedCt) {
				$matchStatus = false;
				$matchResponse = 'Discount limited use has expired';
			}
		}
		// CUSTOMER DISCOUNT LIMIT (past usage) 
		if ($matchStatus !== false && $discountQuery["discount_customer_limit"][$i] != 0 && $_ENV["application.cw"]["customerAccountEnabled"]) {
			// if this is a limited use discount, we must have a customer id 
			if (strlen(trim($customer_id)) && $customer_id !== 0 && $customer_id !== "0") {
				$customerUsedCt = CWGetDiscountCustomerUsage($discountQuery["discount_id"][$i], $customer_id);
				if ($discountQuery["discount_customer_limit"][$i] <= $customerUsedCt) {
					$matchStatus = false;
					$matchResponse = 'Discount has been used maximum number of times by current customer';
				}
			} else {
				// if no customer id available, cannot be used 
				$matchStatus = false;
				$matchResponse = 'Log in or create an account to use this discount';
			}
		}
		// CART TOTAL 
		if ($matchStatus !== false && $cartTotal > 0 && $discountQuery["discount_filter_cart_total"][$i] == 1) {
			if ($cartTotal < $discountQuery["discount_cart_total_min"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Add '.cartweaverMoney($discountQuery["discount_cart_total_min"][$i] - $cartTotal).' to your cart to activate this discount';
			} else if ($discountQuery["discount_cart_total_max"][$i] != 0 && $cartTotal > $discountQuery["discount_cart_total_max"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Discount only available for orders up to '.cartweaverMoney($discountQuery["discount_cart_total_max"][$i]).'';
			}
		}
		// CART QTY 
		if ($matchStatus !== false && $cartQty > 0 && $discountQuery["discount_filter_cart_qty"][$i] == 1) {
			if ($cartQty < $discountQuery["discount_cart_qty_min"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Add '.($discountQuery["discount_cart_qty_min"][$i] - $cartQty).' more item(s) to your cart to activate this discount';
			} else if ($discountQuery["discount_cart_qty_max"][$i] != 0 && $cartQty > $discountQuery["discount_cart_qty_max"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Only available for orders containing '.($discountQuery["discount_cart_qty_max"][$i]).' or fewer items';
			}
		}
		// EXCLUSIVE DISCOUNTS: set match for all others to false 
		// if any discount is exclusive (should catch first matching record, see 'sort by' in discountQuery)
		// NOTE: THIS MUST REMAIN LAST, at the end of filter selections 
		if ($matchStatus !== false && $discountQuery["discount_exclusive"][$i] == 1) {
			$exclusiveFound = true;
			$exclusiveFoundName = $discountQuery["discount_name"][$i];
		}
		// if discount is a match, calculate amount, set status
		if ($matchStatus !== false) {
			$matchStatus = true;
			// PERCENTAGE 
			if ($discountQuery["discount_calc"][$i] == 'percent') {
				$matchPercent = $discountQuery["discount_amount"][$i];
				$matchAmount = 0;
			// FIXED COST 
			} else {
				$matchPercent = 0;
				$matchAmount = $discountQuery["discount_amount"][$i];
			}
			// /end percentage or fixed 
		// if no match, all values 0 
		} else {
			$matchPercent = 0;
			$matchAmount = 0;
		}
		// /end if match 
		// add values to current record 
		$responseStruct["discount_match_response"] = $matchResponse;
		$responseStruct["discount_match_status"] = $matchStatus;
		$responseStruct["discount_amount"] = $matchAmount;
		$responseStruct["discount_percent"] = $matchPercent;
		// add current record data to the structure being returned 
		if ($return_rejects || $matchStatus) {
			if (!isset($discountData["discountResponse"])) $discountData["discountResponse"] = array();
			$discountData["discountResponse"][] = $responseStruct;
		}
	}
	// /end loop discount query 
	return $discountData;
}

// // ---------- // Get discount totals for any cart // ---------- // 
function CWgetCartDiscountTotals($cart, $customer_id=null, $customer_type=null, $promo_code=null, $compare_date=null) {
	if ($customer_id === null) $customer_id = 0;
	if ($customer_type === null) $customer_type = 0;
	if ($promo_code === null) $promo_code = "";
	if ($compare_date === null) $compare_date = CWtime();
	$discountData = array();
	$responseData = array();
	$discountItem = array();
	$discountTotals = array();
	$discountAmount = 0;
	$discountPercent = 0;
	$discountID = '';
	$cartTotal = 0;
	$discountData["discountResponse"] = array();
	$discountTotals["amount"] = 0;
	$discountTotals["percent"] = 0;
	$discountTotals["id"] = '';
	// get actual totals from cart data 
	if (isset($cart["carttotals"]["sub"])) {
		$cartTotal = $cart["carttotals"]["sub"];
	}
	// get all matching discounts 
	$discountData = CWgetCartDiscountData($cart, $customer_id, $customer_type, $promo_code, $compare_date, false);
	if (isset($discountData["discountResponse"])) {
		$responseData = $discountData["discountResponse"];
	}
	// loop the discount data 
	foreach ($responseData as $i => $discountItem) {
		// get needed info about this discount 
		$discountPercent = $discountItem["discount_percent"];
		$discountAmount = $discountItem["discount_amount"];
		$discountID = $discountItem["discount_id"];
		// if discount is an amount, get the percentage that is of the total order 
		if ($discountPercent == 0 && $discountAmount > 0 && $cartTotal > 0) {
			// get rounded 2 decimal percentage by rounding and multiplying 
			$discountPercent = round(($discountAmount / $cartTotal * 100) * 100) / 100;
		// and if discount is a percent, turn it into an amount 
		} else if ($cartTotal > 0) {
			$discountAmount = min(array($cartTotal, round(($cartTotal * $discountPercent/100) * 100) / 100));
		}
		// sum totals, percentages 
		$discountTotals["amount"] += $discountAmount;
		$discountTotals["percent"] += $discountPercent;
		// return list of matching discount IDs 
		if ($discountTotals["id"]) $discountTotals["id"] .= ",";
		$discountTotals["id"] .= $discountID;
	}
	// percent cannot be over 100 
	if ($discountTotals["percent"] > 100) {
		$discountTotals["percent"] = 100;
	}
	// total cannot be higher than cart total 
	if ($discountTotals["amount"] > $cartTotal) {
		$discountTotals["amount"] = $cartTotal;
	}
	return $discountTotals;
}

// // ---------- // Get discount amount for any cart // ---------- // 
function CWgetCartDiscountAmount($cart=null, $customer_id=null, $customer_type=null, $promo_code=null, $compare_date=null) {
	if ($cart === null) $cart = 0;
	if ($customer_id === null) $customer_id = 0;
	if ($customer_type === null) $customer_type = 0;
	if ($promo_code === null) $promo_code = "";
	if ($compare_date === null) $compare_date = CWtime();
	$discountAmount = 0;
	$discountData = CWgetCartDiscountTotals($cart, $customer_id, $customer_type, $promo_code, $compare_date);
	if (is_numeric($discountData["amount"])) {
		$discountAmount = $discountData["amount"];
	}
	return $discountAmount;
}

// // ---------- // Get shipping discounts applicable to any cart // ---------- // 
function CWgetShipDiscountData($cart, $customer_id=null, $customer_type=null, $promo_code=null, $compare_date=null, $return_rejects=null, $discount_id=null) {
	if ($customer_id === null) $customer_id = 0;
	if ($customer_type === null) $customer_type = 0;
	if ($promo_code === null) $promo_code = "";
	if ($compare_date === null) $compare_date = CWtime();
	if ($return_rejects === null) $return_rejects = true;
	if ($discount_id === null) $discount_id = 0;
	// NOTES:
	//Quantity and sku ids are provided to check for discount requirements - the discount amount is only calculated for the order total.
	//The discount matching selections are processed in the order they appear in the code.
	//The first filter to cause a rejection will prevent any further checks,
	//and will return an error message specific to that rejection.
	//To change the priority of the filtering and response messages,
	//the order of the filter checks may be rearranged below.
	//Exclusive discount checking should be left last.
	$discountQuery = '';
	$skuId = '';
	$discountData = array();
	$responseStruct = array();
	$matchStatus = '';
	$matchResponse = '';
	$matchAmount = 0;
	$matchPercent= 0;
	$exclusiveFound = false;
	$exclusiveFoundName = '';
	$matchQuery = '';
	$catsQuery = '';
	$scndCatsQuery = '';
	$discountUsedCt = '';
	$customerUsedCt = '';
	$cartTotal = 0;
	$cartQty = 0;
	// get actual totals from cart data 
	if (isset($cart["carttotals"]["sub"])) {
		$cartTotal = $cart["carttotals"]["sub"];
	}
	if (isset($cart["carttotals"]["skuCount"])) {
		$cartQty = $cart["carttotals"]["skuCount"];
	}
	// If no ID provided, get ALL POSSIBLE MATCHES - stored in application memory on first run 
	if ($discount_id == 0) {
		// get all active discounts 
		$discountQuery = CWgetDiscountData();
	// if id provided, get only this discount 
	} else {
		$discountQuery = CWgetDiscountData(null, $discount_id);
	}
	// /// /// 
	// /// START DISCOUNT FILTERS /// 
	// /// /// 
	// loop matching discounts, comparing each, returning OK message or reason for rejection 
	for ($i=0; $i<$discountQuery["totalRows"]; $i++) {
		$responseStruct = array();
		$matchStatus = "";
		$matchResponse = "";
		// match discount type 
		if (strtolower(trim($discountQuery["discount_type"][$i])) != 'ship_total') {
			$matchStatus = false;
			$matchResponse = 'Discount type '.$discountQuery["discount_type"][$i].' not applicable';
		// if an exclusive match has already been found, all others are set to false 
		} else if ($exclusiveFound) {
			$matchStatus = false;
			$matchResponse = 'Exclusive discount '.$exclusiveFoundName.' cannot be used with other offers';
		}
		// add fixed values into response 
		$responseStruct["discount_id"] = $discountQuery["discount_id"][$i];
		$responseStruct["discount_merchant_id"] = $discountQuery["discount_merchant_id"][$i];
		$responseStruct["discount_name"] = $discountQuery["discount_name"][$i];
		$responseStruct["discount_promotional_code"] = $discountQuery["discount_promotional_code"][$i];
		$responseStruct["discount_exclusive"] = $discountQuery["discount_exclusive"][$i];
		$responseStruct["discount_priority"] = $discountQuery["discount_priority"][$i];
		$responseStruct["discount_type"] = $discountQuery["discount_type"][$i];
		// get conditional values 
		if ($discountQuery["discount_show_description"][$i]) {
			$responseStruct["discount_description"] = $discountQuery["discount_description"][$i];
		} else {
			$responseStruct["discount_description"] = '';
		}
		if ($discountQuery["discount_global"][$i]) {
			$responseStruct["association_method"] = "global";
		} else {
			$responseStruct["association_method"] = $discountQuery["discount_association_method"][$i];
		}
		// get or create remaining values - amount, percent, response message 
		// PROMO CODE: check for matching string 
		if ($matchStatus !== false && (strlen(trim($promo_code)) || strlen(trim($discountQuery["discount_promotional_code"][$i])))) {
			// if not found in the list, and not a direct match 
			$pcArr = explode("^", $promo_code);
			if (!in_array($discountQuery["discount_promotional_code"][$i], $pcArr) && trim($discountQuery["discount_promotional_code"][$i]) != trim($promo_code)) {
				$matchStatus = false;
				$matchResponse = 'Promo code '.trim($promo_code).' not matched';
			}
		}
		// DATE: start/stop dates 
		if ($matchStatus !== false) {
			$endDatePlusOne = ""; if ($discountQuery["discount_end_date"][$i] != "") $endDatePlusOne = strtotime($discountQuery["discount_end_date"][$i]) + 24*3600; //add one day to end date
			if (!(strtotime($discountQuery["discount_start_date"][$i]) <= cartweaverStrtotime($compare_date) && ($discountQuery["discount_end_date"][$i] == '' || $endDatePlusOne >= cartweaverStrtotime($compare_date)))) {
				$matchStatus = false;
				$matchResponse = 'Discount not currently available';
			}
		}
		// PRODUCT ASSOCIATIONS 
		// if not global, check matching records (sku, product, category association methods) 
		if ($matchStatus !== false && $discountQuery["discount_global"][$i] != 1) {
			// for products, assume no match until one is found 
			$matchStatus = false;
			// loop cart items 
			for ($cartLine=0; $cartLine<sizeof($cart["cartitems"]); $cartLine++) {
				$skuId = $cart["cartitems"][$cartLine]["skuID"];
				$skuqty = $cart["cartitems"][$cartLine]["quantity"];
				$productId = CWgetProductBySku($cart["cartitems"][$cartLine]["skuID"]);
				// look up matching items by association type 
				switch ($discountQuery["discount_association_method"][$i]) {
					// PRODUCTS: straight match by product id 
					case "products":
						$matchQuerySql = "SELECT discount2product_discount_id
											FROM cw_discount_products
											WHERE discount2product_product_id = ".$productId."
											AND discount2product_discount_id = ".$discountQuery["discount_id"][$i]."";
						$matchQuery = CWqueryGetRS($matchQuerySql);
						if ($matchQuery["totalRows"]) {
							$matchStatus = true;
						}
						break;

					// SKUS: straight match by sku id 
					case "skus":
						$matchQuerySql = "SELECT discount2sku_discount_id
											FROM cw_discount_skus
											WHERE discount2sku_sku_id = ".$skuId."
											AND discount2sku_discount_id = ".$discountQuery["discount_id"][$i]."";
						$matchQuery = CWqueryGetRS($matchQuerySql);
						if ($matchQuery["totalRows"]) {
							$matchStatus = true;
						}
						break;

					// CATEGORIES: get categories & secondaries the product belongs to, check for match 
					case "categories":
						// QUERY: get categories for this product 
						$catsQuery = CWquerySelectRelCategories($productId);
						$scndcatsQuery = CWquerySelectRelScndCategories($productId);
						$catList = "0";
						$scndCatList = "0";
						if (isset($catsQuery["category_id"]) && is_array($catsQuery["category_id"])) {
							$catList = implode(",", $catsQuery["category_id"]);
						}
						if (isset($scndCatsQuery["secondary_id"]) && is_array($scndCatsQuery["secondary_id"])) {
							$scndCatList = implode(",", $scndcatsQuery["secondary_id"]);
						}
						// check for match by type and id 
						$matchQuerySql = "SELECT discount_category_id
											FROM cw_discount_categories
											WHERE discount2category_discount_id = ".$discountQuery["discount_id"][$i]."
											AND ((
												discount_category_type = 1
												AND discount2category_category_id in(".$catList.")
												) OR (
												discount_category_type = 2
												AND discount2category_category_id in(".$scndCatList.")
											))";
						$matchQuery = CWqueryGetRS($matchQuerySql);
						if ($matchQuery["totalRows"]) {
							$matchStatus = true;
						}
						break;
				}
				// /end product association types 
				// ITEM QTY 
				if ($matchStatus !== false && $skuqty > 0 && $discountQuery["discount_filter_item_qty"][$i] == 1) {
					if ($skuqty < $discountQuery["discount_item_qty_min"][$i] && $discountQuery["discount_item_qty_min"][$i] != 0) {
						$matchStatus = false;
						$matchResponse = 'Add '.($discountQuery["discount_item_qty_min"][$i] - $cartQty).' more of this item to your cart to activate this discount';
					} else if ($discountQuery["discount_filter_item_qty"][$i] != 0 && $discountQuery["discount_item_qty_max"][$i] != 0 && $skuqty > $discountQuery["discount_item_qty_max"][$i]) {
						$matchStatus = false;
						$matchResponse = 'Only available for quantities of '.($discountQuery["discount_item_qty_max"][$i]).' or fewer items';
					}
				}
				// /end item qty 
				// end loop if one product matches 
				if ($matchStatus === true) { break; }
			}
			// if no match was found, set message 
			if ($matchStatus !== true) {
				$matchResponse = 'Discount does not apply to items in cart';
			}
			// /end product associations 
		}
		// CUSTOMER ID 
		if ($matchStatus !== false && $customer_id != 0 && strlen(trim($customer_id)) && strlen(trim($discountQuery["discount_customer_id"][$i])) && $discountQuery["discount_filter_customer_id"][$i] == 1) {
			$customerIdList = str_replace(" ", "", $discountQuery["discount_customer_id"][$i]);
			$customerIdArr = explode(",", $customerIdList);
			if (!in_array($customer_id, $customerIdArr)) {
				$matchStatus = false;
				$matchResponse = 'Discount not available for current customer';
			}
		// if no customer id provided, return false if filtered by id 
		} else if ($matchStatus !== false && $discountQuery["discount_filter_customer_id"][$i] == 1) {
			$matchStatus = false;
			$matchResponse = 'Customer ID not matched. Create an account to use this discount';
		}
		// CUSTOMER TYPE 
		if ($matchStatus !== false && $customer_type != 0 && strlen(trim($customer_type)) && strlen(trim($discountQuery["discount_customer_type"][$i])) && $discountQuery["discount_filter_customer_type"][$i] == 1) {
			$customerTypeList = explode(",", str_replace(" ", "", $discountQuery["discount_customer_type"][$i]));
			if (!in_array($customer_type, $customerTypeList)) {
				$matchStatus = false;
				$matchResponse = 'Discount not available for current customer type';
			}
		// if no customer type provided, return false if filtered by type 
		} else if ($matchStatus !== false && $discountQuery["discount_filter_customer_type"][$i] == 1) {
			$matchStatus = false;
			$matchResponse = 'Customer type not matched. Create an account to use this discount';
		}
		// DISCOUNT LIMIT (past usage) 
		if ($matchStatus !== false && $discountQuery["discount_limit"][$i] != 0) {
			// function returns number of times  
			$discountUsedCt = CWgetDiscountUsage($discountQuery["discount_id"][$i]);
			if ($discountQuery["discount_limit"][$i] <= $discountUsedCt) {
				$matchStatus = false;
				$matchResponse = 'Discount limited use has expired';
			}
		}
		// CUSTOMER DISCOUNT LIMIT (past usage) 
		if ($matchStatus !== false && $discountQuery["discount_customer_limit"][$i] != 0 && $_ENV["application.cw"]["customerAccountEnabled"]) {
			// if this is a limited use discount, we must have a customer id 
			if (strlen(trim($customer_id)) && $customer_id !== 0 && $customer_id !== "0") {
				$customerUsedCt = CWgetDiscountCustomerUsage($discountQuery["discount_id"][$i], $customer_id);
				if ($discountQuery["discount_customer_limit"][$i] <= $customerUsedCt) {
					$matchStatus = false;
					$matchResponse = 'Discount has been used maximum number of times by current customer';
				}
			// if no customer id available, cannot be used 
			} else {
				$matchStatus = false;
				$matchResponse = 'Log in or create an account to use this discount';
			}
		}
		// CART TOTAL 
		if ($matchStatus !== false && $cartTotal > 0 && $discountQuery["discount_filter_cart_total"][$i] == 1) {
			if ($cartTotal < $discountQuery["discount_cart_total_min"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Add '.cartweaverMoney($discountQuery["discount_cart_total_min"][$i] - $cartTotal).' to your cart to activate this discount';
			} else if ($discountQuery["discount_cart_total_max"][$i] != 0 && $cartTotal > $discountQuery["discount_cart_total_max"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Discount only available for orders up to '.cartweaverMoney($discountQuery["discount_cart_total_max"][$i]).'';
			}
		}
		// CART QTY 
		if ($matchStatus !== false && $cartQty > 0 && $discountQuery["discount_filter_cart_qty"][$i] == 1) {
			if ($cartQty < $discountQuery["discount_cart_qty_min"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Add '.($discountQuery["discount_cart_qty_min"][$i] - $cartQty).' more item(s) to your cart to activate this discount';
			} else if ($discountQuery["discount_cart_qty_max"][$i] != 0 && $cartQty > $discountQuery["discount_cart_qty_max"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Only available for orders containing '.($discountQuery["discount_cart_qty_max"][$i]).' or fewer items';
			}
		}
		// EXCLUSIVE DISCOUNTS: set match for all others to false 
		// if any discount is exclusive (should catch first matching record, see 'sort by' in discountQuery)
		// NOTE: THIS MUST REMAIN LAST, at the end of filter selections 
		if ($matchStatus !== false && $discountQuery["discount_exclusive"][$i] == 1) {
			$exclusiveFound = true;
			$exclusiveFoundName = $discountQuery["discount_name"][$i];
		}
		// if discount is a match, calculate amount, set status
		if ($matchStatus !== false) {
			$matchStatus = true;
			// PERCENTAGE 
			if ($discountQuery["discount_calc"][$i] == 'percent') {
				$matchPercent = $discountQuery["discount_amount"][$i];
				$matchAmount = 0;
			// FIXED COST 
			} else {
				$matchPercent = 0;
				$matchAmount = $discountQuery["discount_amount"][$i];
			}
			// /end percentage or fixed 
		// if no match, all values 0 
		} else {
			$matchPercent = 0;
			$matchAmount = 0;
		}
		// /end if match 
		// add values to current record 
		$responseStruct["discount_match_response"] = $matchResponse;
		$responseStruct["discount_match_status"] = $matchStatus;
		$responseStruct["discount_amount"] = $matchAmount;
		$responseStruct["discount_percent"] = $matchPercent;
		// add current record data to the structure being returned 
		if ($return_rejects === true || $matchStatus === true) {
			if (!isset($discountData["discountResponse"])) $discountData["discountResponse"] = array();
			$discountData["discountResponse"][] = $responseStruct;
		}
	}
	// /end loop discount query 
	return $discountData;
}

// // ---------- // Get shipping discount totals for any cart // ---------- // 
function CWgetShipDiscountTotals($cart, $customer_id=null, $customer_type=null, $promo_code=null, $compare_date=null) {
	if ($customer_id === null) $customer_id = 0;
	if ($customer_type === null) $customer_type = 0;
	if ($promo_code === null) $promo_code = "";
	if ($compare_date === null) $compare_date = CWtime();
	$discountData = array();
	$responseData = array();
	$discountItem = array();
	$discountTotals = array();
	$discountAmount = 0;
	$discountPercent = 0;
	$discountID = '';
	$discountData["discountResponse"] = array();
	$discountTotals["amount"] = 0;
	$discountTotals["percent"] = 0;
	$discountTotals["id"] = '';
	// get all matching discounts 
	$discountData = CWgetShipDiscountData($cart, $customer_id, $customer_type, $promo_code, $compare_date, false);
	if (isset($discountData["discountResponse"])) {
		$responseData = $discountData["discountResponse"];
	}
	// loop the discount data 
	foreach ($responseData as $i => $discountItem) {
		// get needed info about this discount 
		$discountPercent = $discountItem["discount_percent"];
		$discountAmount = $discountItem["discount_amount"];
		$discountID = $discountItem["discount_id"];
		// sum totals, percentages 
		$discountTotals["amount"] += $discountAmount;
		$discountTotals["percent"] += $discountPercent;
		// return list of matching discount IDs 
		if ($discountTotals["id"]) $discountTotals["id"] .= ",";
		$discountTotals["id"] .= $discountID;
	}
	// percent cannot be over 100 
	if ($discountTotals["percent"] > 100) {
		$discountTotals["percent"] = 100;
	}
	return $discountTotals;
}

// // ---------- // Get shipping discount amount for any cart // ---------- // 
function CWgetShipDiscountAmount($cart=0, $customer_id=0, $customer_type=0, $promo_code="", $compare_date=null) {
	if ($compare_date === null) {
		$compare_date = CWtime();
	}
	$discountAmount = 0>
	$discountData = CWgetShipDiscountTotals($cart, $customer_id, $customer_type, $promo_code, $compare_date);
	if (is_numeric($discountData["amount"])) {
		$discountAmount = $discountData["amount"];
	}
	return $discountAmount;
}

// // ---------- // Get discounts applicable to any sku // ---------- // 
function CWgetSkuDiscountData($sku_id, $discount_type=null, $sku_qty=null, $cart_qty=null, $order_total=null, $customer_id=null, $customer_type=null, $promo_code=null, $compare_date=null, $return_rejects=null, $discount_id=null) {
	if ($discount_type === null) $discount_type = "sku_cost";
	if ($sku_qty === null) $sku_qty = 0;
	if ($cart_qty === null) $cart_qty = 0;
	if ($order_total === null) $order_total = 0;
	if ($customer_id === null) $customer_id = 0;
	if ($customer_type === null) $customer_type = 0;
	if ($promo_code === null) $promo_code = "";
	if ($compare_date === null) $compare_date = CWtime();
	if ($return_rejects === null) $return_rejects = true;
	if ($discount_id === null) $discount_id = 0;
	// NOTES:
	//Quantity is provided to check for discount requirements - the currency amount of the discount is only calculated for a single item.
	//The discount matching selections are processed in the order they appear in the code.
	//The first filter to cause a rejection will prevent any further checks,
	//and will return an error message specific to that rejection.
	//To change the priority of the filtering and response messages,
	//the order of the filter checks may be rearranged below.
	//Exclusive discount checking should be left last.
	$discountQuery = '';
	$skuQuery = '';
	$discountData = array();
	$responseStruct = array();
	$matchStatus = '';
	$matchResponse = '';
	$matchAmount = 0;
	$matchPercent= 0;
	$exclusiveFound = false;
	$exclusiveFoundName = '';
	$matchQuery = '';
	$catsQuery = '';
	$scndCatsQuery = '';
	$discountUsedCt = '';
	$customerUsedCt = '';
	$catList = '0';
	$scndCatList = '0';
	// QUERY: get details about sku 
	$skuQuery = CWquerySkuDetails($sku_id);
	// If no ID provided, get ALL POSSIBLE MATCHES - stored in application memory on first run 
	if ($discount_id == 0) {
		// get all active discounts 
		$discountQuery = CWgetDiscountData();
	// if id provided, get only this discount 
	} else {
		$discountQuery = CWgetDiscountData(null, $discount_id);
	}
	// /// /// 
	// /// START DISCOUNT FILTERS /// 
	// /// /// 
	// loop matching discounts, comparing each, returning OK message or reason for rejection 
	for ($i=0; $i<$discountQuery["totalRows"]; $i++) {
		$responseStruct = array();
		$matchStatus = "";
		$matchResponse = "";
$tracingOn = false;
if ($tracingOn) {
$oldStatus = $matchStatus;
}
		// match discount type 
		if (trim($discountQuery["discount_type"][$i]) != trim($discount_type)) {
			$matchStatus = false;
			$matchResponse = 'Discount type '.$discountQuery["discount_type"][$i].' not applicable';
		// if an exclusive match has already been found, all others are set to false 
		} else if ($exclusiveFound) {
			$matchStatus = false;
			$matchResponse = 'Exclusive discount '.$exclusiveFoundName.' cannot be used with other offers';
		}
		// add fixed values into response 
		$responseStruct["discount_id"] = $discountQuery["discount_id"][$i];
		$responseStruct["discount_merchant_id"] = $discountQuery["discount_merchant_id"][$i];
		$responseStruct["discount_name"] = $discountQuery["discount_name"][$i];
		$responseStruct["discount_promotional_code"] = $discountQuery["discount_promotional_code"][$i];
		$responseStruct["discount_exclusive"] = $discountQuery["discount_exclusive"][$i];
		$responseStruct["discount_priority"] = $discountQuery["discount_priority"][$i];
		$responseStruct["discount_type"] = $discountQuery["discount_type"][$i];
if ($tracingOn) {
echo "<h1>{$responseStruct["discount_name"]}</h1>
<pre>";
var_dump($discount_type);
var_dump($matchStatus);
var_dump($matchResponse);
var_dump($responseStruct);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// get conditional values (global discount, show description y/n) 
		if ($discountQuery["discount_show_description"][$i]) {
			$responseStruct["discount_description"] = $discountQuery["discount_description"][$i];
		} else {
			$responseStruct["discount_description"] = '';
		}
		if ($discountQuery["discount_global"][$i]) {
			$responseStruct["association_method"] = "global";
		} else {
			$responseStruct["association_method"] = $discountQuery["discount_association_method"][$i];
		}
		// SET DISCOUNT TOTALS: get or create remaining values - amount, percent, response message 
		// PROMO CODE: check for matching string 
		if ($matchStatus !== false && strlen(trim($discountQuery["discount_promotional_code"][$i]))) {
			// if not found in the list, and not a direct match 
			$pcArr = explode("^", $promo_code);
			if (!in_array($discountQuery["discount_promotional_code"][$i], $pcArr) && trim($discountQuery["discount_promotional_code"][$i]) != trim($promo_code)) {
				$matchStatus = false;
				$matchResponse = 'Promo code '.trim($promo_code).' not matched';
			}
		}
if ($tracingOn && $oldStatus !== $matchStatus) {
echo "<h2>Promo Code</h2>
<pre>";
var_dump($matchStatus);
var_dump($matchResponse);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// DATE: start/stop dates 
		if ($matchStatus !== false) {
			$endDatePlusOne = ""; if ($discountQuery["discount_end_date"][$i] != "") $endDatePlusOne = strtotime($discountQuery["discount_end_date"][$i]) + 24*3600; //add one day to end date
			if (!(strtotime($discountQuery["discount_start_date"][$i]) <= cartweaverStrtotime($compare_date) && ($discountQuery["discount_end_date"][$i] == '' || $endDatePlusOne >= cartweaverStrtotime($compare_date)))) {
				$matchStatus = false;
				$matchResponse = 'Discount not currently available';
			}
		}
if ($tracingOn && $oldStatus !== $matchStatus) {
echo "<h2>Date</h2>
<pre>";
var_dump($matchStatus);
var_dump($matchResponse);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// PRODUCT ASSOCIATIONS 
		// if not global, check matching records (sku, product, category association methods) 
		if ($matchStatus !== false && $discountQuery["discount_global"][$i] != 1) {
			switch ($discountQuery["discount_association_method"][$i]) {
				// PRODUCTS: straight match by product id 
				case "products":
					$matchQuerySql = "SELECT discount2product_discount_id
										FROM cw_discount_products
										WHERE discount2product_product_id = ".$skuQuery["sku_product_id"]."
										AND discount2product_discount_id = ".$discountQuery["discount_id"][$i]."";
					$matchQuery = CWqueryGetRS($matchQuerySql);
					if (!$matchQuery["totalRows"]) {
						$matchStatus = false;
						$matchResponse = 'Discount does not apply to selected item';
					}
					break;

				// SKUS: straight match by sku id 
				case "skus":
					$matchQuerySql = "SELECT discount2sku_discount_id
										FROM cw_discount_skus
										WHERE discount2sku_sku_id = ".$sku_id."
										AND discount2sku_discount_id = ".$discountQuery["discount_id"][$i]."";
					$matchQuery = CWqueryGetRS($matchQuerySql);
					if (!$matchQuery["totalRows"]) {
						$matchStatus = false;
						$matchResponse = 'Discount does not apply to selected item';
					}
					break;

				// CATEGORIES: get categories & secondaries the product belongs to, check for match 
				case "categories":
					// QUERY: get categories for this product 
					$catsQuery = CWquerySelectRelCategories($skuQuery["sku_product_id"][0]);
					$scndCatsQuery = CWquerySelectRelScndCategories($skuQuery["sku_product_id"][0]);
					$catList = "0";
					$scndCatList = "0";
					if (isset($catsQuery["category_id"]) && is_array($catsQuery["category_id"])) {
						$catList = implode(",", $catsQuery["category_id"]);
					}
					if (isset($scndCatsQuery["secondary_id"]) && is_array($scndCatsQuery["secondary_id"])) {
						$scndCatList = implode(",", $scndCatsQuery["secondary_id"]);
					}
					// check for match by type and id 
					$matchQuerySql = "SELECT discount_category_id
										FROM cw_discount_categories
										WHERE discount2category_discount_id = ".$discountQuery["discount_id"][$i]."
										AND ((
											discount_category_type = 1
											AND discount2category_category_id in(".$catList.")
											) OR (
											discount_category_type = 2
											AND discount2category_category_id in(".$scndCatList.")
										))";
					$matchQuery = CWqueryGetRS($matchQuerySql);
					if (!$matchQuery["totalRows"]) {
						$matchStatus = false;
						$matchResponse = 'Discount does not apply to selected item';
					}
					break;
			}
		}
		// /end product associations 
if ($tracingOn && $oldStatus !== $matchStatus) {
echo "<h2>Product</h2>
<pre>";
var_dump($matchStatus);
var_dump($matchResponse);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// CUSTOMER ID 
		if ($matchStatus !== false && $customer_id != 0 && strlen(trim($customer_id)) && strlen(trim($discountQuery["discount_customer_id"][$i])) && $discountQuery["discount_filter_customer_id"][$i] == 1) {
			$customerIdList = str_replace(" ", "", $discountQuery["discount_customer_id"][$i]);
			$customerIdArr = explode(",", $customerIdList);
			if (!in_array($customer_id, $customerIdArr)) {
				$matchStatus = false;
				$matchResponse = 'Discount not available for current customer';
			}
		// if no customer id provided, return false if filtered by id 
		} else if ($matchStatus !== false && $discountQuery["discount_filter_customer_id"] == 1) {
			$matchStatus = false;
			$matchResponse = 'Customer ID not matched. Create an account to use this discount';
		}
if ($tracingOn && $oldStatus !== $matchStatus) {
echo "<h2>Customer ID</h2>
<pre>";
var_dump($matchStatus);
var_dump($matchResponse);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// CUSTOMER TYPE 
		if ($matchStatus !== false && $customer_type != 0 && strlen(trim($customer_type)) && strlen(trim($discountQuery["discount_customer_type"][$i])) && $discountQuery["discount_filter_customer_type"][$i] == 1) {
			$customerTypeList = explode(",", str_replace(" ", "", $discountQuery["discount_customer_type"][$i]));
			if (!in_array($customer_type, $customerTypeList)) {
				$matchStatus = false;
				$matchResponse = 'Discount not available for current customer type';
			}
		// if no customer type provided, return false if filtered by type 
		} else if ($matchStatus !== false && $discountQuery["discount_filter_customer_type"] == 1) {
			$matchStatus = false;
			$matchResponse = 'Customer type not matched. Create an account to use this discount';
		}
if ($tracingOn && $oldStatus !== $matchStatus) {
echo "<h2>Customer Type</h2>
<pre>";
var_dump($matchStatus);
var_dump($matchResponse);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// DISCOUNT LIMIT (past usage) 
		if ($matchStatus !== false && $discountQuery["discount_limit"][$i] != 0) {
			// function returns number of times  
			$discountUsedCt = CWgetDiscountUsage($discountQuery["discount_id"][$i]);
			if ($discountQuery["discount_limit"][$i] <= $discountUsedCt) {
				$matchStatus = false;
				$matchResponse = 'Discount limited use has expired';
			}
		}
if ($tracingOn && $oldStatus !== $matchStatus) {
echo "<h2>Discount Limit</h2>
<pre>";
var_dump($matchStatus);
var_dump($matchResponse);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// CUSTOMER DISCOUNT LIMIT (past usage) 
		if ($matchStatus !== false && $discountQuery["discount_customer_limit"][$i] != 0 && $_ENV["application.cw"]["customerAccountEnabled"]) {
			// if this is a limited use discount, we must have a customer id 
			if (strlen(trim($customer_id)) && $customer_id !== 0 && $customer_id !== "0") {
				$customerUsedCt = CWgetDiscountCustomerUsage($discountQuery["discount_id"][$i], $customer_id);
				if ($discountQuery["discount_customer_limit"][$i] <= $customerUsedCt) {
					$matchStatus = false;
					$matchResponse = 'Discount has been used maximum number of times by current customer';
				}
			// if no customer id available, cannot be used 
			} else {
				$matchStatus = false;
				$matchResponse = 'Log in or create an account to use this discount';
			}
		}
if ($tracingOn && $oldStatus !== $matchStatus) {
echo "<h2>Customer Discount Limit</h2>
<pre>";
var_dump($matchStatus);
var_dump($matchResponse);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// CART TOTAL 
		if ($matchStatus !== false && $order_total > 0 && $discountQuery["discount_filter_cart_total"][$i] == 1) {
			if ($order_total < $discountQuery["discount_cart_total_min"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Add '.cartweaverMoney($discountQuery["discount_cart_total_min"][$i] - $order_total).' to your cart to activate this discount';
			} else if ($discountQuery["discount_cart_total_max"][$i] != 0 && $order_total > $discountQuery["discount_cart_total_max"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Discount only available for orders up to '.cartweaverMoney($discountQuery["discount_cart_total_max"][$i]).'';
			}
		// if cart total is 0 (general product discount lookup), return false against any required quantity 
		} else if ($matchStatus !== false && $order_total == 0 && $discountQuery["discount_filter_cart_total"][$i] == 1) {
			$matchStatus = false;
			$matchResponse = 'Add '.cartweaverMoney($discountQuery["discount_cart_total_min"][$i]).' to your cart to activate this discount';
		}
if ($tracingOn && $oldStatus !== $matchStatus) {
echo "<h2>Cart Total</h2>
<pre>";
var_dump($matchStatus);
var_dump($matchResponse);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// ITEM QTY 
		if ($matchStatus !== false && $sku_qty > 0 && $discountQuery["discount_filter_item_qty"][$i] == 1) {
			if ($sku_qty < $discountQuery["discount_item_qty_min"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Add '.($discountQuery["discount_item_qty_min"][$i] - $sku_qty).' more of this item to your cart to activate this discount';
			} else if ($discountQuery["discount_filter_item_qty"][$i] != 0 && $sku_qty > $discountQuery["discount_item_qty_max"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Only available for quantities of '.($discountQuery["discount_item_qty_max"][$i]).' or less';
			}
		// if quantity is 0 (general product discount lookup), return false against any required quantity 
		} else if ($matchStatus !== false && $sku_qty == 0 && $discountQuery["discount_filter_item_qty"][$i] == 1) {
			$matchStatus = false;
			$matchResponse = 'Add '.$discountQuery["discount_item_qty_min"][$i].' of this item to your cart to activate this discount';
		}
if ($tracingOn && $oldStatus !== $matchStatus) {
echo "<h2>Item Qty</h2>
<pre>";
var_dump($matchStatus);
var_dump($matchResponse);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// CART QTY 
		if ($matchStatus !== false && $cart_qty > 0 && $discountQuery["discount_filter_cart_qty"][$i] == 1) {
			if ($cart_qty < $discountQuery["discount_cart_qty_min"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Add '.($discountQuery["discount_cart_qty_min"][$i] - $cart_qty).' more item(s) to your cart to activate this discount';
			} else if ($discountQuery["discount_cart_qty_max"][$i] != 0 && $cart_qty > $discountQuery["discount_cart_qty_max"][$i]) {
				$matchStatus = false;
				$matchResponse = 'Only available for orders containing '.($discountQuery["discount_cart_qty_max"][$i]).' or fewer items';
			}
		// if quantity is 0 (general product discount lookup), return false against any required quantity 
		} else if ($matchStatus !== false && $cart_qty == 0 && $discountQuery["discount_filter_cart_qty"][$i] == 1) {
			$matchStatus = false;
			$matchResponse = 'Add '.$discountQuery["discount_cart_qty_min"][$i].' item(s) to your cart to activate this discount';
		}
if ($tracingOn && $oldStatus !== $matchStatus) {
echo "<h2>Cart QTY</h2>
<pre>";
var_dump($matchStatus);
var_dump($matchResponse);
echo "</pre>";
$oldStatus = $matchStatus;
}
		// EXCLUSIVE DISCOUNTS: set match for all others to false 
		// if any discount is exclusive (should catch first matching record, see 'sort by' in discountQuery)
		// NOTE: THIS MUST REMAIN LAST, at the end of filter selections 
		if ($matchStatus !== false && $discountQuery["discount_exclusive"][$i] == 1) {
			$exclusiveFound = true;
			$exclusiveFoundName = $discountQuery["discount_name"][$i];
		}
		// if discount is a match, calculate amount, set status
		if ($matchStatus !== false) {
			$matchStatus = true;
			// PERCENTAGE 
			if ($discountQuery["discount_type"][$i] == "sku_ship" && $discountQuery["discount_calc"][$i] == 'percent') {
				$matchPercent = $discountQuery["discount_amount"][$i];
				$matchAmount = 0;
			} else if ($discountQuery["discount_type"][$i] == "sku_cost" && $discountQuery["discount_calc"][$i] == 'percent') {
				$matchPercent = $discountQuery["discount_amount"][$i];
				$matchAmount = min(array($skuQuery["sku_price"],round(($skuQuery["sku_price"] * $discountQuery["discount_amount"][$i]/100)*100)/100));
			// FIXED COST 
			} else {
				$matchPercent = 0;
				$matchAmount = min(array($skuQuery["sku_price"],$discountQuery["discount_amount"][$i]));
			}
			// /end percentage or fixed 
		// if no match, all values 0 
		} else {
			$matchPercent = 0;
			$matchAmount = 0;
		}
		// /end if match 
		// add values to current record 
		$responseStruct["discount_match_response"] = $matchResponse;
		$responseStruct["discount_match_status"] = $matchStatus;
		$responseStruct["discount_amount"] = $matchAmount;
		$responseStruct["discount_percent"] = $matchPercent;
		// add current record data to the structure being returned 
		if ($return_rejects || $matchStatus) {
			if (!isset($discountData["discountResponse"])) $discountData["discountResponse"] = array();
			$discountData["discountResponse"][] = $responseStruct;
if ($tracingOn) {
echo "<h2>SUCCESS!</h2>
<pre>";
var_dump($responseStruct);
echo "</pre>";
$oldStatus = $matchStatus;
}
		}
	}
	// /end loop discount query 
	return $discountData;
}

// // ---------- // Get discount data for any sku // ---------- // 
function CWgetSkuDiscountTotals($sku_id, $discount_type=null, $sku_qty=null, $cart_qty=null, $order_total=null, $customer_id=null, $customer_type=null, $promo_code=null, $compare_date=null) {
	if ($discount_type === null) $discount_type = "sku_cost";
	if ($sku_qty === null) $sku_qty = 0;
	if ($cart_qty === null) $cart_qty = 0;
	if ($order_total === null) $order_total = 0;
	if ($customer_id === null) $customer_id = 0;
	if ($customer_type === null) $customer_type = 0;
	if ($promo_code === null) $promo_code = "";
	if ($compare_date === null) $compare_date = CWtime();
	$discountData = array();
	$responseData = array();
	$discountItem = array();
	$discountTotals = array();
	$discountAmount = 0;
	$discountPercent = 0;
	$discountID = '';
	$skuQuery = '';
	$discountData["discountResponse"] = array();
	$discountTotals["amount"] = 0;
	$discountTotals["percent"] = 0;
	$discountTotals["id"] = '';
	// QUERY: get details about sku 
	$skuQuery = CWquerySkuDetails($sku_id);
	// get all matching discounts 
	$discountData = CWgetSkuDiscountData($sku_id, $discount_type, $sku_qty, $cart_qty, $order_total, $customer_id, $customer_type, $promo_code, $compare_date, false);
//echo "<h1>SKU DISCOUNT DATA</h1><pre>";
//var_dump($discountData);
//echo "</pre>";
	if (isset($discountData["discountResponse"])) {
		$responseData = $discountData["discountResponse"];
	}
	// loop the discount data 
	foreach ($responseData as $i => $discountItem) {
		// get needed info about this discount 
		$discountPercent = $discountItem["discount_percent"];
		$discountAmount = $discountItem["discount_amount"];
		$discountID = $discountItem["discount_id"];
		// sum totals, percentages 
		$discountTotals["amount"] += $discountAmount;
		$discountTotals["percent"] += $discountPercent;
		// amount cannot be higher than sku cost 
		if ($discount_type == 'sku_cost' && $skuQuery["sku_price"] > 0 && $discountTotals["amount"] > $skuQuery["sku_price"]) {
			$discountTotals["amount"] = $skuQuery["sku_price"];
		// for sku_ship, only 100% of amount is allowed (deducted from ship range calculation) 
		} else if ($discount_type == 'sku_ship') {
			$discountTotals["amount"] = $skuQuery["sku_price"];
		}
		// return list of matching discount IDs 
		if ($discountTotals["id"]) $discountTotals["id"] .= ",";
		$discountTotals["id"] .= $discountID;
	}
	// percent cannot be over 100 
	if ($discountTotals["percent"] > 100) {
		$discountTotals["percent"] = 100;
	}
	return $discountTotals;
}

// // ---------- // Get discount amount for any sku // ---------- // 
function CWgetSkuDiscountAmount($sku_id, $discount_type=null, $sku_qty=null, $cart_qty=null, $order_total=null, $customer_id=null, $customer_type=null, $promo_code=null, $compare_date=null) {
	if ($discount_type === null) $discount_type = "sku_cost";
	if ($sku_qty === null) $sku_qty = 0;
	if ($cart_qty === null) $cart_qty = 0;
	if ($order_total === null) $order_total = 0;
	if ($customer_id === null) $customer_id = 0;
	if ($customer_type === null) $customer_type = 0;
	if ($promo_code === null) $promo_code = "";
	if ($compare_date === null) $compare_date = CWtime();
	$discountAmount = 0;
	$discountData = CWgetSkuDiscountTotals($sku_id, $discount_type, $sku_qty, $cart_qty, $order_total, $customer_id, $customer_type, $promo_code, $compare_date);
	if (is_numeric($discountData["amount"])) {
		$discountAmount = $discountData["amount"];
	}
	return $discountAmount;
}

// // ---------- // Get discount usage count // ---------- // 
function CWgetDiscountUsage($discount_id=0) {
	$discountCt = 0>
	$prodQuery = ''>
	$prodQuerySql = "SELECT discount_usage_discount_id
		FROM cw_discount_usage
		WHERE discount_usage_discount_id = ".$discount_id."";
	$prodQuery = CWqueryGetRS($prodQuerySql);
	$discountCt = $prodQuery["totalRows"];
	return $discountCt;
}

// // ---------- // Get discount usage count per customer // ---------- // 
function CWgetDiscountCustomerUsage($discount_id="", $customer_id="") {
	$discountCt = 0;
	$prodQuery = '';
	$prodQuerySql = "SELECT discount_usage_discount_id
		FROM cw_discount_usage
		WHERE discount_usage_discount_id = ".$discount_id."
		AND discount_usage_customer_id = '".$customer_id."'";
	$prodQuery = CWqueryGetRS($prodQuerySql);
	$discountCt = $prodQuery["totalRows"];
	return $discountCt;
}

// // ---------- // Get discount usage by order ID // ---------- // 
function CWgetOrderDiscounts($order_id=0) {
	$discountsQuery = '';
	$discountsQuerySql = "SELECT *
		FROM cw_discount_usage
		WHERE discount_usage_order_id = '".trim($order_id)."'";
	$discountsQuery = CWqueryGetRS($discountsQuerySql);
	return $discountsQuery;
}

// // ---------- // Match a single promo code to a customer's cart // ---------- // 
function CWmatchPromoCode($promo_code="", $cart=null, $customer_id=0, $set_promocode_session=true) {
	// once status is true, a match is found, all others are skipped 
	$matchStatus = false;
	$matchData = array();
	$cartTotal = 0;
	$cartQty = 0;
	$promoQuery = '';
	$promoType = '';
	$promoID = 0;
	// get actual totals from cart data 
	if (isset($cart["carttotals"]["sub"])) {
		$cartTotal = $cart["carttotals"]["sub"];
	}
	if (isset($cart["carttotals"]["skuCount"])) {
		$cartQty = $cart["carttotals"]["skuCount"];
	}
	// only run if promo_code is not null  
	if (strlen(trim($promo_code))) {
		// remove default promo delimiter to avoid submitting a list of codes 
		$promo_code = str_replace("^", " ", $promo_code);
		// get information about the discount by promocode 
		$promoQuery = CWgetDiscountbyPromoCode(trim($promo_code));
		$promoID = $promoQuery["discount_id"][0];
		$promoType = $promoQuery["discount_type"][0];
		// check for cart discounts 
		if ($promoType == 'order_total' && !$matchStatus) {
			$discountData = CWgetCartDiscountData($cart, $customer_id, null, $promo_code, null, null, $promoID);
			// if a match is found 
			if (isset($discountData["discountResponse"])) {
				$matchData = $discountData["discountResponse"][0];
				$matchStatus = true;
			}
		}
		// /end cart discounts 
		// check for shipping discounts 
		if ($promoType == 'ship_total' && !$matchStatus) {
			$discountData = CWgetShipDiscountData($cart, $customer_id, null, $promo_code, null, null, $promoID);
			// if a match is found 
			if (isset($discountData["discountResponse"])) {
				$matchData = $discountData["discountResponse"][0];
				$matchStatus = true;
			}
		}
		// /end ship discounts 
		// check for sku discounts 
		if ($promoType == 'sku_cost' && !$matchStatus) {
			// loop skus in provided cart structure 
			for ($i=0; $i<sizeof($cart["cartitems"]); $i++) {
				// get sku cost discounts 
				$discountData =  CWgetSKUDiscountData($cart["cartitems"][$i]["skuID"], 'sku_cost', $cart["cartitems"][$i]["quantity"], $cartQty, $cartTotal, $customer_id, null, $promo_code, null, null, $promoID);
				// if a match is found 
				if (isset($discountData["discountResponse"])) {
					$matchData = $discountData["discountResponse"][0];
					$matchStatus = true;
					break;
				}
			}
		}
		if ($promoType == 'sku_ship' && !$matchStatus) {
			// loop skus in provided cart structure 
			for ($i=0; $i<sizeof($cart["cartitems"]); $i++) {
				// get sku cost discounts 
				$discountData =  CWgetSKUDiscountData($cart["cartitems"][$i]["skuID"], 'sku_ship', $cart["cartitems"][$i]["quantity"], $cartQty, $cartTotal, $customer_id, null, $promo_code, null, null, $promoID);
				// if a match is found 
				if (isset($discountData["discountResponse"])) {
					$matchData = $discountData["discountResponse"][0];
					$matchStatus = true;
					break;
				}
			}
		}
		// /end sku discounts 
		// if matched, set into session where applicable 
		if ($matchStatus && $set_promocode_session) {
			// if not already matched, add to list of applied discounts in user's session 
			$dpcArr = explode("^", $_SESSION["cwclient"]["discountPromoCode"]);
			if (!in_array($promo_code, $dpcArr)) {
				// append id to list (default delimiter) 
				if ($_SESSION["cwclient"]["discountApplied"]) $_SESSION["cwclient"]["discountApplied"] .= ",";
				$_SESSION["cwclient"]["discountApplied"] .= $promoID;
				// append string to list (^ delimiter, allows commas in strings) 
				if ($_SESSION["cwclient"]["discountPromoCode"]) $_SESSION["cwclient"]["discountPromoCode"] .= "^";
				$_SESSION["cwclient"]["discountPromoCode"] .= $promo_code;
			}
		}
	}
	// /end promocode exists 
	// return structure of matched discount data, or rejection message 
	return $matchData;
}

// // ---------- // Get discount details by promocode // ---------- // 
function CWgetDiscountbyPromoCode($promo_code="-") {
	$discQuery = '';
	$discQuerySql = "SELECT *
		FROM cw_discounts
		WHERE discount_promotional_code = '".trim($promo_code)."'";
	$discQuery = CWqueryGetRS($discQuerySql);
	return $discQuery;
}

// // ---------- // Get discount details by id // ---------- // 
function CWgetDiscountDetails($discount_id=0) {
	$discQuery = '';
	$discQuerySql = "SELECT discount_id, discount_name, discount_amount, discount_merchant_id,
		discount_promotional_code, discount_type, discount_show_description, discount_description
		FROM cw_discounts
		WHERE discount_id = ".$discount_id."";
	$discQuery = CWqueryGetRS($discQuerySql);
	return $discQuery;
}

// // ---------- // Get discount description by id // ---------- // 
function CWgetDiscountDescription($discount_id=0, $show_description=true, $show_promocode=true) {
	$discQuery = '';
	$discDescrip = '';
	$discQuerySql = "SELECT discount_description, discount_name, discount_promotional_code, discount_show_description
		FROM cw_discounts
		WHERE discount_id = ".$discount_id."";
	$discQuery = CWqueryGetRS($discQuerySql);
	// get name 
	$discDescrip = $discQuery["discount_name"][0];
	// add promo code 
	if ($show_promocode && strlen(trim($discQuery["discount_promotional_code"][0]))) {
		$discDescrip .= ' ('.$discQuery["discount_promotional_code"][0].')';
	}
	// add description 
	if ($show_description && $discQuery["discount_show_description"][0] != 0) {
		$discDescrip .= '<br><span class="CWdiscountDescription">' . $discQuery["discount_description"][0] . '</span>';
	}
	return $discDescrip;
}

// // ---------- // get discount data, set into application scope // ---------- // 
function CWgetDiscountData($refresh_data=null, $discount_id=null) {
	if ($refresh_data === null) $refresh_data = false;
	if ($discount_id === null) $discount_id = 0;
	$discountQuery = array("totalRows" => 0);
	$codeList = array();
	// get all active discounts 
	if ($refresh_data || $discount_id != 0 || !isset($_ENV["application.cw"]["discountData"]["activeDiscounts"])) {
		// get all columns, exclusive discounts first
		//	  sort by priority (lower priority number comes first),
		//	  then higher percentage rate, then highest fixed amount 
		$discountQuerySql = "SELECT * 
			FROM cw_discounts
			WHERE NOT discount_archive = 1";
		if ($discount_id > 0) {
			$discountQuerySql .= " AND discount_id = ".$discount_id."";
		}
		$discountQuerySql .= " ORDER BY discount_exclusive DESC, discount_priority, discount_calc DESC, discount_amount DESC, discount_merchant_id";
		$discountQuery = CWqueryGetRS($discountQuerySql);
		// if listing all discounts, set into application scope 
		if (!($discount_id > 0)) {
			$_ENV["application.cw"]["discountData"]["activeDiscounts"] = $discountQuery;
			// set list of available promo codes into application memory for fast lookup 
			if ($discountQuery["totalRows"]) {
				for ($c=0; $c<sizeof($discountQuery["discount_promotional_code"]); $c++) {
					if (strlen(trim($discountQuery["discount_promotional_code"][$c]))) {
						// add to list 
						$codeList[] = $discountQuery["discount_promotional_code"][$c];
					}
				}
			}
			$_ENV["application.cw"]["discountData"]["promocodes"] = $codeList;
		}
	} else if (!$refresh_data) {
		// if we already have this in memory, use the stored query 
		$discountQuery = $_ENV["application.cw"]["discountData"]["activeDiscounts"];
	}
	return $discountQuery;
}