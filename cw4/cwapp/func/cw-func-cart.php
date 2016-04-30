<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, all Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-cart.php
File Date: 2012-02-01
Description:
Manages cart contents, creates cart object and handles cart-related queries
Dependencies:
Requires cw-func-query, cw-func-tax to be included in calling page
==========================================================
*/

// // ---------- // CWgetCart // ---------- // 
function CWgetCart($cart_id=null,$tax_region_id=null,$tax_country_id=null,$product_order=null,$customer_id=null,$customer_type=null,$promo_code=null,$set_discount_request=null,$tax_calc_method=null) {
	if ($cart_id === null) $cart_id = 0;
	if ($tax_region_id === null) $tax_region_id = 0;
	if ($tax_country_id === null) $tax_country_id = 0;
	if ($product_order === null) $product_order = $_ENV["application.cw"]["appDisplayCartOrder"];
	if ($customer_id === null) $customer_id = 0;
	if ($customer_type === null) $customer_type = 0;
	if ($promo_code === null) $promo_code = "";
	if ($set_discount_request === null) $set_discount_request = true;
	if ($tax_calc_method === null) $tax_calc_method = $_ENV["application.cw"]["taxCalctype"];
	$cart = array();
	$rsCart = array("totalRows" => 0);
	$rsUnique = array("totalRows" => 0);
	$rsCartTotals = array("totalRows" => 0);
	$cartItem = array();
	$option = array();
	$discountTotals = array();
	$shipDiscountTotals = array();
	$cartTaxData = array();
	$dataStruct = array();
	$subStruct = array();
	$discountedTax = 0;

	// Set default values for cart totals 
	$cart["carttotals"] = array();
	$cart["carttotals"]["base"] = 0;
	$cart["carttotals"]["shipProductBase"] = 0;
	$cart["carttotals"]["sub"] = 0;
	$cart["carttotals"]["tax"] = 0;
	$cart["carttotals"]["cartItemTotal"] = 0;
	$cart["carttotals"]["weight"] = 0;
	$cart["carttotals"]["itemCount"] = 0;
	$cart["carttotals"]["skuCount"] = 0;
	// discount totals 
	$cart["carttotals"]["cartItemDiscounts"] = 0;
	$cart["carttotals"]["cartOrderDiscounts"] = 0;
	$cart["carttotals"]["cartDiscounts"] = 0;
	$cart["carttotals"]["shipItemDiscounts"] = 0;
	$cart["carttotals"]["shipOrderDiscounts"] = 0;
	$cart["carttotals"]["shipOrderDiscountPercent"] = 0;
	$cart["carttotals"]["shipDiscounts"] = 0;
	$cart["carttotals"]["discountids"] = "";
	// shipping discounts 
	$cart["carttotals"]["shipWeight"] = 0;
	$cart["carttotals"]["shipSubtotal"] = 0;
	
	// Set default cartItem information 
	$cart["cartitems"] = array();
	// use cart id from session if not provided 
	if ($cart_id == 0 && isset($_SESSION["cwclient"]["cwCartID"]) && $_SESSION["cwclient"]["cwCartID"] != 0 && $_SESSION["cwclient"]["cwCartID"] != "") {
		$cart_id = $_SESSION["cwclient"]["cwCartID"];
	}
	// use country id from session if not provided 
	if ($tax_country_id == 0 && isset($_SESSION["cwclient"]["cwTaxCountryID"]) && $_SESSION["cwclient"]["cwTaxCountryID"] != 0 && $_SESSION["cwclient"]["cwTaxCountryID"] != "") {
		$tax_country_id = $_SESSION["cwclient"]["cwTaxCountryID"];
	}
	// use region id from session if not provided 
	if ($tax_region_id == 0 && isset($_SESSION["cwclient"]["cwTaxRegionID"]) && $_SESSION["cwclient"]["cwTaxRegionID"] != 0 && $_SESSION["cwclient"]["cwTaxRegionID"] != "") {
		$tax_region_id = $_SESSION["cwclient"]["cwTaxRegionID"];
	}
	// use customer id from session if not provided 
	if ($customer_id == 0 && isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0") {
		$customer_id = $_SESSION["cwclient"]["cwCustomerID"];
		$customerQuery = CWquerySelectCustomerDetails($customer_id);
		if (isset($customerQuery["customer_type_id"][0])) $customer_type = $customerQuery["customer_type_id"][0];
	}
	// use promo codes from session if not provided 
	if (!strlen(trim($promo_code)) && isset($_SESSION["cwclient"]["discountPromoCode"]) && strlen(trim($_SESSION["cwclient"]["discountPromoCode"]))) {
		$promo_code = trim($_SESSION["cwclient"]["discountPromoCode"]);
	}
	// use product order from application if not provided 
	if (!strlen(trim($product_order)) && isset($_ENV["application.cw"]["appDisplayCartOrder"]) && strlen(trim($_ENV["application.cw"]["appDisplayCartOrder"]))) {
		$product_order = $_ENV["application.cw"]["appDisplayCartOrder"];
	}
	// collect cart and sku info 
	$rsCart = "SELECT
				p.product_id,
				p.product_name,
				p.product_ship_charge,
				p.product_custom_info_label,
				s.sku_merchant_sku_id,
				s.sku_id,
				ot.optiontype_name,
				op.option_name,
				op.option_sort,
				c.cart_sku_qty,
				c.cart_sku_unique_id,
				c.cart_sku_qty * s.sku_price AS TotalPrice,
				c.cart_sku_qty * s.sku_weight AS TotalWeight,
				s.sku_price,
				s.sku_weight,
				s.sku_ship_base
				FROM cw_products p
				INNER JOIN cw_skus s
				ON p.product_id = s.sku_product_id
				INNER JOIN cw_cart c
				ON c.cart_sku_id = s.sku_id
				LEFT JOIN cw_sku_options so
				ON s.sku_id = so.sku_option2sku_id
				LEFT JOIN cw_options op
				ON op.option_id = so.sku_option2option_id
				LEFT JOIN cw_option_types ot
				ON ot.optiontype_id = op.option_type_id
				WHERE c.cart_custcart_id = '".CWqueryParam($cart_id)."'
				AND NOT p.product_archive = 1
				AND NOT s.sku_on_web = 0
				AND NOT p.product_on_web = 0";
	if($product_order == 'timeAdded') {
		$rsCart.= " ORDER BY c.cart_line_id DESC";	
	} else {
		$rsCart.= " ORDER BY p.product_name,
					s.sku_sort, c.cart_sku_unique_id , op.option_sort, op.option_name";
	}
	$rsCart = CWqueryGetRS($rsCart);
	$cartQuery = $rsCart;
	// if items exist 
	if($cartQuery['totalRows'] != 0) {
		// add the ID to the cart structure 
		$cart["cartID"] = $cart_id;
		$rsUnique = array("totalRows" => 0, "cart_sku_unique_id" => array(), "cart_sku_qty" => array(), "TotalPrice" => array());
		$rsUniqueCheck = array();
		for ($i=0; $i<$cartQuery['totalRows']; $i++) {
			if (!in_array($cartQuery["cart_sku_unique_id"][$i]."||||||".$cartQuery["cart_sku_qty"][$i]."||||||".$cartQuery["TotalPrice"][$i], $rsUniqueCheck)) {
				$rsUniqueCheck[] = $cartQuery["cart_sku_unique_id"][$i]."||||||".$cartQuery["cart_sku_qty"][$i]."||||||".$cartQuery["TotalPrice"][$i];
				$rsUnique["cart_sku_unique_id"][] = $cartQuery["cart_sku_unique_id"][$i];
				$rsUnique["cart_sku_qty"][] = $cartQuery["cart_sku_qty"][$i];
				$rsUnique["TotalPrice"][] = $cartQuery["TotalPrice"][$i];
				$rsUnique["totalRows"]++;
			}
		}
		// get totals for use in discount lookup 
		$rsCartTotals = array("totalQty" => array_sum($rsUnique["cart_sku_qty"]), "TotalPrice" => array_sum($rsUnique["TotalPrice"]), "totalRows" => 1);
		// The user has some cartItems in their cart 
		$lastSkuUniqueID = -1;
		for($i=0; $i<$cartQuery['totalRows']; $i++) {
			$lastSkuUniqueID = $cartQuery["cart_sku_unique_id"][$i];
			// Increment the cartItem count 
			$cart["carttotals"]["itemCount"]++;
			$cart["carttotals"]["skuCount"]++;
			// Create an empty cartItem struct 
			$cartItem = array();
			$cartItem["options"] = array();
			// set up container for any applied discounts 
			$cartItem["discountsApplied"] = array();
			$cartItem["shipDiscountsApplied"] = array();
			// Set the cartItem base price, before *any* adjustments 
			$cartItem["BasePrice"] = $cartQuery["TotalPrice"][$i];
			$cartItem["price"] = $cartQuery["sku_price"][$i];
			$cart["carttotals"]["base"] = $cart["carttotals"]["base"] + $cartItem["BasePrice"];
			// Set the cartItem information values 
			$cartItem["Name"] = $cartQuery["product_name"][$i];
			$cartItem["ID"] = $cartQuery["product_id"][$i];
			$cartItem["customInfoLabel"] = $cartQuery["product_custom_info_label"][$i];
			$cartItem["skuID"] = $cartQuery["sku_id"][$i];
			$cartItem["skuUniqueID"] = $cartQuery["cart_sku_unique_id"][$i];
			$cartItem["merchSkuID"] = $cartQuery["sku_merchant_sku_id"][$i];
			$cartItem["quantity"] = $cartQuery["cart_sku_qty"][$i];
			// get discounts: look up and store discount details applied to any cart item 
			if($_ENV["application.cw"]["discountsEnabled"]) {
				// sku shipping discounts 
				$cartItem["shipDiscountsApplied"] =  CWgetSKUDiscountTotals($cartItem["skuID"],
																			 "sku_ship",
																			 //$cart["cartID"],
																			 $cartItem["quantity"],
																			 $rsCartTotals["totalQty"],
																			 $rsCartTotals["TotalPrice"],
																			 $customer_id,
																			 $customer_type,
																			 $promo_code);
				// amount is tracked for deduction from shipping ranges 
				$cartItem["shipDiscountAmount"] = $cartItem["shipDiscountsApplied"]["amount"];
				// add applied discounts to stored list 
				$shdiscArr = $cartItem["shipDiscountsApplied"]["id"];
				if (!is_array($shdiscArr) && strlen($shdiscArr)) $shdiscArr = explode(",", $shdiscArr);
				else $shdiscArr = array();
				$discIDs = $cart["carttotals"]["discountids"];
				if (!is_array($discIDs) && strlen($discIDs)) $discIDs = explode(",", $discIDs);
				else $discIDs = array();
				foreach ($shdiscArr as $key => $d) {
					if (!in_array($d, $discIDs)) {
						$discIDs[] = $d;
					}
				}
				$cart["carttotals"]["discountids"] = implode(",", $discIDs);
				// sku price discounts 
				$cartItem["discountsApplied"] = CWgetSKUDiscountTotals($cartItem["skuID"],
																			 "sku_cost",
																			 //$cart["cartID"],
																			 $cartItem["quantity"],
																			 $rsCartTotals["totalQty"],
																			 $rsCartTotals["TotalPrice"],
																			 $customer_id,
																			 $customer_type,
																			 $promo_code);
				// set totals for amount and price 
				$cartItem["discountAmount"] = $cartItem["discountsApplied"]["amount"];
				$cartItem["discountPrice"] = $cartItem["price"] - $cartItem["discountAmount"];
				// add applied discounts to stored list 
				$discArr = $cartItem["discountsApplied"]["id"];
				if (!is_array($discArr) && strlen($discArr)) $discArr = explode(",", $discArr);
				else $discArr = array();
				$discIDs = $cart["carttotals"]["discountids"];
				if (!is_array($discIDs) && strlen($discIDs)) $discIDs = explode(",", $discIDs);
				else $discIDs = array();
				foreach ($discArr as $key => $d) {
					if (!in_array($d, $discIDs)) {
						$discIDs[] = $d;
					}
				}
				$cart["carttotals"]["discountids"] = implode(",", $discIDs);
				// total of discounts applied to cart items 
				$cart["carttotals"]["cartItemDiscounts"] = round(($cart["carttotals"]["cartItemDiscounts"] + ($cartItem["quantity"] * $cartItem["discountAmount"]))*100)/100;
				$cart["carttotals"]["shipItemDiscounts"] = round(($cart["carttotals"]["shipItemDiscounts"] + ($cartItem["quantity"] * $cartItem["shipDiscountAmount"]))*100)/100;
			} else {
				$cartItem["discountAmount"] = 0;
				$cartItem["shipDiscountAmount"] = 0;
				$cartItem["discountPrice"] = $cartItem["price"];
			}
			// subtotal is the base price minus any applied discounts 
			$cartItem["subTotal"] = $cartItem["quantity"] * $cartItem["price"] - ($cartItem["quantity"] * $cartItem["discountAmount"]);
			$cart["carttotals"]["sub"] = $cart["carttotals"]["sub"] + $cartItem["subTotal"];
			// Set the weight and cost totals for shipping calculations 
			$cartItem["weight"] = $cartQuery["TotalWeight"][$i];
			$cart["carttotals"]["weight"] = $cart["carttotals"]["weight"]  + $cartItem["weight"];
			// if the item is set to use shipping 
			if ($cartQuery["product_ship_charge"][$i] == 1 && $_ENV["application.cw"]["shipEnabled"] && $cartItem["shipDiscountAmount"] < 1 && $cart["carttotals"]["shipItemDiscounts"] == 0) {
				$cartItem["shipCharge"] = true;
				$cart["carttotals"]["shipWeight"] += $cartItem["weight"];
				$cart["carttotals"]["shipSubtotal"] += $cartItem["subTotal"];
				// base shipping rate applied to each sku 
				$cartItem["shipBase"] = $cartQuery["sku_ship_base"][$i] * $cartItem["quantity"];
				$cart["carttotals"]["shipProductBase"] += $cartQuery["sku_ship_base"][$i];
			// if not using shipping for this item 
			} else {
				$cartItem["shipCharge"] = false;
			}
			if (strtolower($tax_calc_method) == 'localtax') {
				// calculate tax rates 
				$cartItem["taxRates"] = CWgetProductTax($cartItem["ID"], $tax_country_id, $tax_region_id);
				$cartItem["tax"] = round(($cartItem["subTotal"] * $cartItem["taxRates"]["calcTax"])*100)/100 - $cartItem["subTotal"];
				if($cartItem["tax"] < 0) {
					$cartItem["tax"] = 0;
				}
				// set item tax 
				$cart["carttotals"]["tax"] = $cart["carttotals"]["tax"] + $cartItem["tax"];
				// cartItem Total is the SubTotal plus any applicable taxes 
				$cartItem["total"] = $cartItem["subTotal"] + $cartItem["tax"];
			// set tax to 0 if not calculated here 
			} else {
				$cartItem["total"] = $cartItem["subTotal"];
				$cartItem["tax"] = 0;
			}
			// calculate cart subtotals 
			$cart["carttotals"]["cartItemTotal"] = $cart["carttotals"]["cartItemTotal"] + $cartItem["total"];
			// total at this stage is same as itemtotal (other calculations done on page) 
			$cart["carttotals"]["total"] = round($cart["carttotals"]["cartItemTotal"]*100)/100;
			// create cartItem options array 
			while ($i < $cartQuery["totalRows"] && $cartQuery["cart_sku_unique_id"][$i] == $lastSkuUniqueID) {
				// Loop through the grouped options 
				if($cartQuery["option_name"][$i] != "") {
					$option = array();
					$option["Name"] = $cartQuery["optiontype_name"][$i];
					$option["Value"] = $cartQuery["option_name"][$i];
					$option["Sort"] = $cartQuery["option_sort"][$i];
					$cartItem["options"][] = $option;
				}
				$i++;
			}
			$i--;
			$cart["cartitems"][] = $cartItem;
		}
		// /end products query 
		if($_ENV["application.cw"]["discountsEnabled"]) {
			// cart discounts 
			// get discounts applied to cart (pass in cart structure) 
			$discountTotals = CWgetCartDiscountTotals(
										$cart,
										$customer_id,
										$customer_type,
										$promo_code);
			$cart["carttotals"]["cartOrderDiscounts"] = $discountTotals["amount"];
			$cart["carttotals"]["sub"] -= $discountTotals["amount"];
			// adjust total of all product discounts 
			$cart["carttotals"]["cartDiscounts"] = $cart["carttotals"]["cartItemDiscounts"] + $cart["carttotals"]["cartOrderDiscounts"];
			// adjust tax total by same percent 
			$discountedTax = round($cart["carttotals"]["tax"] * (100-$discountTotals["percent"]))/100;
			// since order total already has tax added, remove difference of tax 
			$cart["carttotals"]["total"] -= ($cart["carttotals"]["tax"] - $discountedTax);
			$cart["carttotals"]["tax"] = $discountedTax;
			// adjust order total 
			$cart["carttotals"]["total"] -= $cart["carttotals"]["cartOrderDiscounts"];
			// add applied discounts to stored list 
			$dtIDs = $discountTotals["id"];
			if (!is_array($dtIDs) && strlen($dtIDs)) $dtIDs = explode(",", $dtIDs);
			else $dtIDs = array();
			$discIDs = $cart["carttotals"]["discountids"];
			if (!is_array($discIDs) && strlen($discIDs)) $discIDs = explode(",", $discIDs);
			else $discIDs = array();
			foreach ($dtIDs as $key => $d) {
				if (!in_array($d, $discIDs)) {
					$discIDs[] = $d;
				}
			}
			$cart["carttotals"]["discountids"] = implode(",", $discIDs);
			// /end order discounts 
			// shipping discounts 
			$shipDiscountTotals = CWgetShipDiscountTotals(
										$cart,
										$customer_id,
										$customer_type,
										$promo_code);
			$cart["carttotals"]["shipOrderDiscounts"] = $shipDiscountTotals["amount"];
			$cart["carttotals"]["shipOrderDiscountPercent"] = $shipDiscountTotals["percent"];
			// adjust order total 
			$cart["carttotals"]["shipDiscounts"] = $cart["carttotals"]["shipOrderDiscounts"];
			// add applied discounts to stored list 
			$dtIDs = $shipDiscountTotals["id"];
			if (!is_array($dtIDs) && strlen($dtIDs)) $dtIDs = explode(",", $dtIDs);
			else $dtIDs = array();
			$discIDs = $cart["carttotals"]["discountids"];
			if (!is_array($discIDs) && strlen($discIDs)) $discIDs = explode(",", $discIDs);
			else $discIDs = array();
			foreach ($dtIDs as $key => $d) {
				if (!in_array($d, $discIDs)) {
					$discIDs[] = $d;
				}
			}
			$cart["carttotals"]["discountids"] = implode(",", $discIDs);
			// /end shipping discounts 
			// set applied discounts into request scope 
			if ($set_discount_request) {
				$dtIDs = $cart["carttotals"]["discountids"];
				if (!is_array($dtIDs) && strlen($dtIDs)) $dtIDs = explode(",", $dtIDs);
				else $dtIDs = array();
				$discIDs = $_ENV["request.cwpage"]["discountsApplied"];
				if (!is_array($discIDs) && strlen($discIDs)) $discIDs = explode(",", $discIDs);
				else $discIDs = array();
				foreach ($dtIDs as $key => $d) {
					if (!in_array($d, $discIDs)) {
						$discIDs[] = $d;
					}
				}
				$_ENV["request.cwpage"]["discountsApplied"] = implode(",", $discIDs);
			}
		}
		// /end cart-level discounts 
		// if not using localtax taxes, get whole cart tax 
		if (strtolower($tax_calc_method) != 'localtax' && strtolower($tax_calc_method) != 'none' && $customer_id != 0) {
			// get all cart tax data (e.g. avatax) 
			$cartTaxData = CWgetCartTax(
									 $cart["cartID"],
									 $customer_id);
			// get tax for each item based on item id = skuuniqueid 
			foreach ($cart["cartitems"] as $loopCt => $i) {
				try {
					// find the product key in the cart that contains the unique id 
					if (isset($cartTaxData["cartLines"])) {
						$dataStruct = CWgetTaxLineFromData($cartTaxData["cartLines"], $i["skuUniqueID"]);
						// loop the found array, and get the Item ID 
						if ($dataStruct && is_array($dataStruct)) {
							foreach ($dataStruct as $subKey => $subStruct) {
								// if the matching key is 'itemid' 
								if (strtolower($subKey) == 'itemid') {
									// get tax amount 
									$cart["cartitems"][$loopCt]["tax"] = $dataStruct["itemTax"];
									// set item tax 
									$cart["carttotals"]["tax"] += $cart["cartitems"][$loopCt]["tax"];
									// cartitem total is the subtotal plus any applicable taxes 
									$cart["cartitems"][$loopCt]["total"] = $cart["cartitems"][$loopCt]["subTotal"] + $cart["cartitems"][$loopCt]["tax"];
									// calculate cart subtotals 
									$cart["carttotals"]["cartItemTotal"] += $cart["cartitems"][$loopCt]["tax"];
									$cart["carttotals"]["total"] = $cart["carttotals"]["total"] + $cart["cartitems"][$loopCt]["tax"];
								}
							}
						} else {
							$cart["cartitems"][$loopCt]["tax"] = 0;
						}
						// /end loop of found array 
					} else {
						$cart["cartitems"][$loopCt]["tax"] = 0;
					}
				} catch (Exception $e) {
					// on error, no tax is added for this item 
				}
			}
			// /end loop of cart items 
			// shipping tax 
			if (isset($cartTaxData["totalShipTax"]) && $cartTaxData["totalShipTax"] > 0) {
				// set into request scope for display on cart view/checkout page as needed 
				$_ENV["request.cwpage"]["cartShipTaxTotal"] = $cartTaxData["totalShipTax"];
			}
		}
		// /end whole cart tax 
	}
	// /end if items exist 
	return $cart;
}

// // ---------- // CWcartAddItem // ---------- // 
function CWcartAddItem($sku_id,$sku_unique_id=null,$sku_qty=null,$ignore_stock=null) {
	if ($sku_unique_id === null) $sku_unique_id = "";
	if ($sku_qty === null) $sku_qty = 1;
	if ($ignore_stock === null) $ignore_stock = false;
	$addskuResults = array();
	$rsCartskuExists = '';
	$oldQty = 0;
	$newQty = 0;
	$addskuResults["message"] = '';
	$addskuResults["qty"] = 0;
	if(!isset($_SESSION["cwclient"]["cwCartID"])) { $_SESSION["cwclient"]["cwCartID"] = 0; }
	// check for quantity provided (sku is skipped if qty = 0, no message returned 
	if(!isset($sku_qty)) { $sku_qty = 0;}
	if($sku_qty >= 1) {
		$rsCartskuExists = "SELECT cart_line_id, cart_sku_qty
							FROM cw_cart
							WHERE cart_custcart_id ='".CWqueryParam($_SESSION["cwclient"]["cwCartID"])."'
							AND
							(cart_sku_unique_id = '".CWqueryParam($sku_unique_id)."'";
		// if unique id is empty, can match sku id 
		if(!strlen(trim($sku_unique_id))) {
			$rsCartskuExists.= " OR cart_sku_unique_id = '".CWqueryParam($sku_id)."'";
		}
		$rsCartskuExists.= ")
							AND cart_sku_id = '".CWqueryParam($sku_id)."'";
		$rsCartskuExists = CWqueryGetRS($rsCartskuExists);
		// if existing, factor in for quantity check 
		if($rsCartskuExists['totalRows'] > 0) {
			$oldQty = $rsCartskuExists['cart_sku_qty'][0];
		} else {
			$oldQty = 0;
		}
		// if already existing, add provided qty to existing 
		if($oldQty > 0) {
			$newQty = $oldQty + $sku_qty;
		// if not already existing, use provided qty 
		} else {
			$newQty = $sku_qty;
		}
		// check quantity available 
		$rsCartskuQty = CWqueryGetRS("SELECT sku_stock
						FROM cw_skus
						WHERE sku_id = '".CWqueryParam($sku_id)."'");
		// if quantity not available, limit number added, show message 
		if($ignore_stock == false && $rsCartskuQty['totalRows'] && $rsCartskuQty['sku_stock'][0] < $newQty) {
			// set new quantity to number available (for update) 
			$newQty = $rsCartskuQty['sku_stock'][0];
			// add the number remaining (for new, and display to customer)
			$insertQty = $newQty - $oldQty;
		} else {
			$insertQty = $sku_qty;
		}
		// if sku already exists in cart 
		if($rsCartskuExists['totalRows'] > 0) {
			// UPDATE EXISTING sku IN CART 
			try {
				// set total to new quantity 
				$query = "UPDATE cw_cart
						SET cart_sku_qty = '".CWqueryParam($newQty)."',
						cart_dateadded = '".CWqueryParam(date("Y-m-d H:i:g"))."'
						WHERE cart_sku_id = '".CWqueryParam($sku_id)."'
						AND cart_sku_unique_id= '".CWqueryParam($sku_unique_id)."'
						AND cart_custcart_id = '".CWqueryParam($_SESSION["cwclient"]["cwCartID"])."'";
				if (!function_exists("CWpageMessage")) {
					$myDir = getcwd();
					chdir(dirname(__FILE__));
					// global functions 
					require_once("cw-func-global.php");
					chdir($myDir);
				}
				mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
				$addskuResults["message"] = $sku_id;
				$addskuResults["qty"] = $insertQty;
			} catch(Exception $e) {
				// handle any errors 
				$addskuResults["message"] = $e->getMessage();
				$addskuResults["qty"] = 0;
			}
			// /end update existing sku 
		} else {
			// if sku does not exist, insert it 
			// INSERT NEW sku TO CART 
			try {
				$queryIns = "INSERT INTO cw_cart
							(	
							cart_custcart_id,
							cart_sku_id,
							cart_sku_unique_id,
							cart_sku_qty,
							cart_dateadded)
							VALUES
							(
							'".CWqueryParam($_SESSION["cwclient"]["cwCartID"])."',
							'".CWqueryParam($sku_id)."',
							'".CWqueryParam($sku_unique_id)."',
							'".CWqueryParam($insertQty)."',
							'".CWqueryParam(date("Y-m-d H:i:s"))."'
							)";
				if (!function_exists("CWpageMessage")) {
					$myDir = getcwd();
					chdir(dirname(__FILE__));
					// global functions 
					require_once("cw-func-global.php");
					chdir($myDir);
				}
				mysql_query($queryIns,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$queryIns);
				$addskuResults["message"] = $sku_id;
				$addskuResults["qty"] = $insertQty;
			} catch(Exception $e) {
				$addskuResults["message"] = $e->getMessage();
				$addskuResults["qty"] = 0;
			}
			// /end insert sku 
		}
		// /end if sku already exists 
	}
	// /end check for quantity 
	return $addskuResults;
}

// // ---------- // CWcartDeleteItem // ---------- // 
function CWcartDeleteItem($sku_unique_id=0) {
	$skuDeleteResults = "DELETE FROM cw_cart
				 WHERE cw_cart.cart_sku_unique_id = '".CWqueryParam($sku_unique_id)."'
				 AND cart_custcart_id = '".CWqueryParam($_SESSION["cwclient"]["cwCartID"])."'";
	mysql_query($skuDeleteResults,$_ENV["request.cwapp"]["db_link"]);
	if (mysql_affected_rows()) {
		$skuDeleteResults = $sku_unique_id;
	} else {
		$skuDeleteResults = "Unable to remove item from cart";
	}
	return $skuDeleteResults;
}

// // ---------- // CWcartUpdateItem // ---------- // 
function CWcartUpdateItem($sku_unique_id=null,$sku_qty=null,$ignore_stock=null,$sku_new_unique_id=null) {
	if ($sku_unique_id === null) $sku_unique_id = 0;
	if ($sku_qty === null) $sku_qty = 0;
	if ($ignore_stock === null) $ignore_stock = false;
	if ($sku_new_unique_id === null) $sku_new_unique_id = "";
	// check quantity available 
	$rsCartskuQty = '';
	$updateSkuResults = array();
	$updateQty = '';
	$rsCartskuQty = "SELECT sku_stock
					FROM cw_skus
					INNER JOIN cw_cart
					WHERE cw_skus.sku_ID = cw_cart.cart_sku_id
					AND cw_cart.cart_sku_unique_id = '".CWqueryParam($sku_unique_id)."'";
	$rsCartskuQty = CWqueryGetRS($rsCartskuQty);
	// if quantity not available, limit number added, show message 
	if($ignore_stock == false && isset($rsCartskuQty['sku_qty'][0]) && $rsCartskuQty['sku_qty'][0]) {
		$updateQty = $rsCartskuQty['sku_stock'][0];
	// if stock is not checked, or if enough stock exists, insert the number requested 
	} else {
		$updateQty = $sku_qty;
	}
	if(is_numeric($updateQty) && $updateQty > 0) {
		try {
			$rsUpdateCartItem = "UPDATE cw_cart
								SET cart_sku_qty = '".CWqueryParam($updateQty)."'";
			if(strlen(trim($sku_new_unique_id))) {
				$rsUpdateCartItem.= ", cart_sku_unique_id = '".CWqueryParam(trim($sku_new_unique_id))."'";
			}
			$rsUpdateCartItem.= "WHERE cw_cart.cart_sku_unique_id = '".CWqueryParam($sku_unique_id)."'";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-global.php");
				chdir($myDir);
			}
			mysql_query($rsUpdateCartItem,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$rsUpdateCartItem);
			// create update results 
			$updateSkuResults["message"] = $sku_unique_id;
			$updateSkuResults["qty"] = $updateQty;
		} catch(Exception $e) {
			$updateSkuResults["message"] = $e->getMessage();
			$updateSkuResults["qty"] = 0;
		}
	} elseif($updateQty == 0) {
		// if quantity is 0, remove item from cart 
		try {
			$query = "DELETE FROM cw_cart
					WHERE cw_cart.cart_sku_unique_id = '".CWqueryParam($sku_unique_id)."'";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-global.php");
				chdir($myDir);
			}
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
			$updateSkuResults["message"] = $sku_unique_id;
		} catch(Exception $e) {
			$updateSkuResults["message"] = 'Unable to remove from cart';
		}
		// quantity is returned as empty string when deleting 
		$updateSkuResults["qty"] = '';
	}
	return $updateSkuResults;	
}

// // ---------- // CWcartGetskuNoOptions // ---------- // 
function CWcartGetskuNoOptions($product_id) {
	try
	{
		$rsskuLookup1 = "SELECT sku_id
						FROM cw_skus
						WHERE cw_skus.sku_product_id = '".CWqueryParam($product_id)."'";
		$skuLookupResults = CWqueryGetRS($rsskuLookup1);
		if ($skuLookupResults["totalRows"] > 0) {
			return $skuLookupResults['sku_id'][0];
		} else {
			return 0;
		}
	}
	catch(Exception $e) {
		$skuLookupResults = $e->getMessage();
	}
	return $skuLookupResults;
}

// // ---------- // CWcartGetskuByOptions // ---------- // 
function CWcartGetskuByOptions($product_id,$option_list) {
	$rsskuLookup1 = '';
	$rsskuLookup2 = '';
	$option = $option_list;
	if (!is_array($option)) $option = explode(',', $option_list);
	else $option_list = implode($option_list);
	$numOptions = count($option);
	$skuLookupResults = '';
	try
	{
		$rsskuLookup1 = CWqueryGetRS("SELECT cw_sku_options.sku_option2sku_id
						FROM cw_skus
						INNER JOIN cw_sku_options
							ON cw_skus.sku_id = cw_sku_options.sku_option2sku_id
							WHERE cw_skus.sku_product_id = '".CWqueryParam($product_id)."'
						GROUP BY cw_sku_options.sku_option2sku_id
						HAVING count(cw_sku_options.sku_option2sku_id) = '".CWqueryParam($numOptions)."'");
		if (isset($rsskuLookup1['sku_option2sku_id'])) {
			$sku_option2sku_id = implode(',', $rsskuLookup1['sku_option2sku_id']);
		} else { $sku_option2sku_id = ""; }
		if($sku_option2sku_id == "") {
			$sku_option2sku_id = 0;
		}
		$rsskuLookup2 = CWqueryGetRS("SELECT
							cw_sku_options.sku_option2sku_id
						FROM cw_sku_options
						WHERE
							cw_sku_options.sku_option2option_id In (".CWqueryParam($option_list).")
							AND cw_sku_options.sku_option2sku_id IN
							(".$sku_option2sku_id.")
						GROUP BY cw_sku_options.sku_option2sku_id
						HAVING Count(cw_sku_options.sku_option2sku_id)=".CWqueryParam($numOptions)."");
		if (isset($rsskuLookup2['sku_option2sku_id'])) {
			$sku_option2sku_id2 = implode(',', $rsskuLookup2['sku_option2sku_id']);
		} else { $sku_option2sku_id2 = ""; }
		if($sku_option2sku_id2 == "") {
			$sku_option2sku_id2 = 0;
		}
		$findsku = CWqueryGetRS("SELECT
					DISTINCT (sku_id) aS skuID,
					sku_Stock
					FROM  cw_sku_options, cw_skus
					WHERE sku_id IN ('".CWqueryParam($sku_option2sku_id2)."')
					AND sku_product_id = '".CWqueryParam($product_id)."'");
		if (isset($findsku['skuID'][0])) {
			$skuLookupResults = $findsku['skuID'][0];
		}
		else {
			$skuLookupResults = 0;
		}
	}
	catch(Exception $e) {
		$skuLookupResults = $e->getMessage();
	}
	return $skuLookupResults;
}

	// // ---------- // CWcartGetskuData // ---------- // 
	function CWcartGetskuData($sku_data_id=NULL) {
		//global /["request.cwapp"]["db_link"];
		$getData = '';
		$returnContent = '';
		$getData = "SELECT data_content
					FROM cw_order_sku_data
					WHERE data_id = '".CWqueryParam($sku_data_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-global.php");
			chdir($myDir);
		}
		$res = mysql_query($getData,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$getData);
		$row=mysql_fetch_array($res);
			$result['data_content'] = $row['data_content'];
			
			//return $result;	
		$returnContent	= 	$result['data_content'];
	return $returnContent;
}
// // ---------- // CWcartAddskuData // ---------- // 
function CWcartAddskuData($sku_id,$sku_data=NULL) {
	$addDataResults = '';
	$checkID = '';
	$getNewID = '';
	$addDataTS = date("Y-m-d H:i:s");
	if($_SESSION["cwclient"]["cwCartID"] == '') { $_SESSION["cwclient"]["cwCartID"] = 0; }
	try
	{
		$checkID = "SELECT data_id as dataID
					FROM cw_order_sku_data
					WHERE data_sku_id = '".CWqueryParam($sku_id)."'
					AND data_cart_id = '".CWqueryParam($_SESSION["cwclient"]["cwCartID"])."'
					AND data_content = '".CWqueryParam($sku_data)."'";
		$result = CWqueryGetRS($checkID);
		// if we already have a match,use that ID 
		if($result['totalRows'] > 0) {
			$addDataResults = $result['dataID'][0];
		} else {
			$query = "INSERT INTO cw_order_sku_data
					(
					data_sku_id,
					data_cart_id,
					data_content,
					data_date_added
					)VALUES(
					'".CWqueryParam($sku_id)."',
					'".CWqueryParam($_SESSION["cwclient"]["cwCartID"])."',
					'".CWqueryParam(htmlentities($sku_data))."',
					'".CWqueryParam($addDataTS)."')";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-global.php");
				chdir($myDir);
			}
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
			// get the new ID (from any db type) by matching all vars inserted 
			$getNewID = "SELECT data_id as newID
						FROM cw_order_sku_data
						WHERE data_sku_id = '".CWqueryParam($sku_id)."'
						AND data_cart_id = '".CWqueryParam($_SESSION["cwclient"]["cwCartID"])."'
						AND data_content = '".CWqueryParam(htmlentities($sku_data))."'
						AND data_date_added = '".CWqueryParam($addDataTS)."'";
			$NewIDres = CWqueryGetRS($getNewID);
			// if successful, message will be the ID from the data table 
			$addDataResults = $NewIDres['newID'][0];	
		}
	}
	// /end check for existing 
	catch(Exception $e) {
		// if any error, return the server error message in place of ID 
		$addDataResults = $e->getMessage();
	}
	return $addDataResults;
}
// // ---------- // CWcartVerifyProduct // ---------- // 
function CWcartVerifyProduct($product_id,$ignore_stock=false) {
	//global /["request.cwapp"]["db_link"];
	$rsProductStockCheck = '';
	$rsProductStockCheck = "SELECT product_id, sku_id, sku_stock
							FROM cw_products
							INNER JOIN cw_skus
							WHERE product_id = '".CWqueryParam($product_id)."'
							AND cw_skus.sku_product_id = '".CWqueryParam($product_id)."'";
	if(!$ignore_stock) {
		$rsProductStockCheck.= " AND cw_skus.sku_stock > 0";
	}
	$rsProductStockCheck.=	" AND NOT sku_on_web = 0
							AND NOT product_on_web = 0";
	$result = CWqueryGetRS($rsProductStockCheck);
	if($result['totalRows'] > 0) {
		return true;
	} else {
		return false;
	}
}


// // ---------- // CWgetCartTotal // ---------- // 
function CWgetCartTotal($cart_id=null,$tax_calc_method=null) {
	if ($cart_id === null) $cart_id = 0;
	if ($tax_calc_method === null) $tax_calc_method = "none";
	$cartTotal = 0;
	$cartData = array();
	$cartData["carttotals"] = array();
	$cartData["carttotals"]["total"] = 0;
	if ($cart_id != 0) {
		$cartData = CWgetCart($cart_id, null, null, null, null, null, null, null, $tax_calc_method);
		if (isset($cartData["carttotals"]["total"]) && $cartData["carttotals"]["total"] > 0) {
			$cartTotal = $cartData["carttotals"]["total"];
		}
	}
	return $cartTotal;
}


// // ---------- // CWgetCartItems // ---------- // 
function CWgetCartItems($cart_id=0) {
	$rsCartItems = "SELECT *
					FROM cw_cart
					WHERE cart_custcart_id ='".CWqueryParam($cart_id)."'";
	return CWqueryGetRS($rsCartItems);
}
// // ---------- // CWgetCartCount // ---------- // 
function CWgetCartCount($cart_id) {
	$rsCartCount = CWqueryGetRS("SELECT SUM(cart_sku_qty) AS CartCount, cart_custcart_id
					FROM cw_cart c, cw_skus s
					WHERE c.cart_custcart_id ='".CWqueryParam($cart_id)."'
						AND s.sku_id = c.cart_sku_id
						AND NOT c.cart_sku_id = 0
						AND NOT c.cart_sku_id IS NULL
						AND NOT c.cart_sku_qty = 0
						AND NOT s.sku_on_web = 0
					GROUP BY cart_custcart_id");
	if(isset($rsCartCount['CartCount'][0]) && is_numeric($rsCartCount['CartCount'][0])) {
		$returnCount = $rsCartCount['CartCount'][0];
	} else {
		$returnCount = 0;
	}
	return $returnCount;				
}
// // ---------- // CWclearCart // ---------- // 
function CWclearCart($cart_id) {
	$query = "DELETE FROM cw_cart
			WHERE cart_custcart_id='".CWqueryParam($cart_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-global.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}
?>