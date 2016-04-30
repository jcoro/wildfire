<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, all Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-order.php
File Date: 2012-04-29
Description: manages order insertion to database, and other related functions
Dependencies: 
Requires cw-func-query and cw-func-global to be included in calling page
==========================================================
*/

// // ---------- // Insert Order to Database // ---------- // 
function CWsaveOrder($order_id,$order_status,$cart=null,$customer=null,$ship_method=null,$message=null,$checkout_type=null) {
	if ($cart === null) $cart = array();
	if ($customer === null) $customer = array();
	if ($ship_method === null) $ship_method = 0;
	if ($message === null) $message = "";
	if ($checkout_type === null) $checkout_type = "";
	$rsCheckOrder = '';
	$rsInsertOrder = '';
	$returnStr = '';
	try {
		// check for unique order ID (no duplicates) 
		$rsCheckOrder = "SELECT order_ID
						FROM cw_orders
						WHERE order_id = '".CWqueryParam($order_id)."'";
		$rsCheckOrder = CWqueryGetRS($rsCheckOrder);
		// if a duplicate order is found 
		if($rsCheckOrder['totalRows'] > 0) {
			$returnStr = '0-Processing Error: Duplicate Order ID';
		// if no duplicate order 
		} else {
			$rsInsertOrder="INSERT INTO cw_orders (
						order_id,
						order_date,
						order_status,
						order_customer_id,
						order_tax,
						order_shipping,
						order_shipping_tax,
						order_total,
						order_ship_method_id,
						order_ship_name,
						order_company,
						order_address1,
						order_address2,
						order_city,
						order_state,
						order_zip,
						order_country,
						order_comments,
						order_checkout_type,
						order_discount_total,
						order_ship_discount_total
						) VALUES (
						'".CWqueryParam($order_id)."',
						'".CWqueryParam(date("Y-m-d H:i:s", CWtime()))."',
						'".CWqueryParam($order_status)."',
						'".CWqueryParam($customer['customerid'])."',
						'".CWqueryParam($cart["carttotals"]["tax"])."',
						'".CWqueryParam(($cart["carttotals"]["shipping"] - $cart["carttotals"]["shipDiscounts"]))."',
						'".CWqueryParam($cart["carttotals"]["shippingTax"])."',
						'".CWqueryParam($cart["carttotals"]["total"])."',
						'".CWqueryParam($ship_method)."',
						'".CWqueryParam($customer['shipname'])."',
						'".CWqueryParam($customer['shipcompany'])."',
						'".CWqueryParam($customer['shipaddress1'])."',
						'".CWqueryParam($customer['shipaddress2'])."',
						'".CWqueryParam($customer['shipcity'])."',
						'".CWqueryParam($customer['shipstateprovname'])."',
						'".CWqueryParam($customer['shipzip'])."',
						'".CWqueryParam($customer['shipcountry'])."',
						'".CWqueryParam($message)."',
						'".CWqueryParam($checkout_type)."',
						'".CWqueryParam($cart["carttotals"]["cartDiscounts"])."',
						'".CWqueryParam($cart["carttotals"]["shipDiscounts"])."'
						)";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-global.php");
				chdir($myDir);
			}
			mysql_query($rsInsertOrder) or CWpageMessage(mysql_error()."<br />".$rsInsertOrder);
			$rsInsertOrderid = mysql_insert_id();
			// INSERT SKUS 
			for ($lineNumber=0; $lineNumber < count($cart["cartitems"]); $lineNumber++) {
				// set the current product to a variable for easier reference 
				$product = $cart["cartitems"][$lineNumber];
				// QUERY: save order sku (order id, sku id, sku unique id, qty, price, subtotal, tax rate, discount id, discount amount) 
				$saveSku = CWsaveOrderSku(
								$order_id,
								$product["skuID"],
								$product["skuUniqueID"],
								$product["quantity"],
								$product["price"],
								$product["subTotal"],
								$product["tax"],
								$product["discountAmount"],
								$product["customInfoLabel"]);
				// QUERY: debit purchased quantity from stock on hand 
				$setQty = CWsetSkuStock($product["skuID"],$product["quantity"]);
				//if ($product["discount"]["discount_id"]) != 0 && !ListFind($appliedDiscounts, $product["discount"]["discount_id"])) {
					//if ($appliedDiscounts) $appliedDiscounts .= ",";
					//$appliedDiscounts .= $product["discount"]["discount_id"];
				//}
			 }
			 // RECORD DISCOUNT USAGE 
			 if (strlen(trim($cart["carttotals"]["discountids"])) && $cart["carttotals"]["discountids"] != 0) {
				 $didArr = $cart["carttotals"]["discountids"];
				 if (!is_array($didArr) && strlen($didArr)) $didArr = explode(",", $didArr);
				 elseif (!is_array($didArr)) $didArr = array();
				 foreach ($didArr as $key => $d) {
					// QUERY: get discount details 
					$discountQuery = CWgetDiscountDetails($d);
					$rsInsertDiscountUsage = "INSERT INTO cw_discount_usage(
						discount_usage_customer_id,
						discount_usage_datetime,
						discount_usage_order_id,
						discount_usage_discount_name,
						";
					if ($discountQuery["discount_show_description"][0] != 0) {
						$rsInsertDiscountUsage .= "discount_usage_discount_description,
						";
					}
					$rsInsertDiscountUsage .= "discount_usage_promocode,
						discount_usage_discount_id
						) VALUES (
						'".CWqueryParam($customer["customerid"])."',
						'".CWqueryParam(date("Y-m-d H:i:s", CWtime()))."',
						'".CWqueryParam($order_id)."',
						'".CWqueryParam($discountQuery["discount_name"][0])."',
						";
					if ($discountQuery["discount_show_description"][0] != 0) {
						$rsInsertDiscountUsage .= "'".CWqueryParam($discountQuery["discount_description"][0])."',
						";
					}
					$rsInsertDiscountUsage .= "'".CWqueryParam($discountQuery["discount_promotional_code"][0])."',
						".$d."
							)";
					if (!function_exists("CWpageMessage")) {
						$myDir = getcwd();
						chdir(dirname(__FILE__));
						// global functions 
						require_once("cw-func-global.php");
						chdir($myDir);
					}
					mysql_query($rsInsertDiscountUsage) or CWpageMessage(mysql_error()."<br />".$rsInsertDiscountUsage);	
				}
			}
			// return order id as confirmation string 
			$returnStr = $order_id;
		// /end check for duplicates 
		}
	} catch (Exception $e) {
	// if any errors, return message 
		$returnStr = '0-Processing Error';
		if(strlen(trim($e->getMessage()))) {
			if (stripos('duplicate',$e->getMessage()) !== false) {
				$returnStr .= ': Duplicate Order ID';
			} else {
				$returnStr .= $e->getMessage();
			}
		}
	}
	return $returnStr;
}


// // ---------- // Insert Order SKU // ---------- // 
function CWsaveOrderSku($order_id, $sku_id, $sku_unique_id=null, $sku_qty=null, $sku_price, $sku_subtotal=null, $sku_tax_rate=null, $sku_discount_amount=null, $sku_custom_info="") {
	if ($sku_unique_id === null) $sku_unique_id = $sku_id;
	if ($sku_qty === null) $sku_qty = 1;
	if ($sku_subtotal === null) $sku_subtotal = $sku_qty * $sku_price;
	if ($sku_tax_rate === null) $sku_tax_rate = 0;
	if ($sku_discount_amount === null) $sku_discount_amount = 0;
	if ($sku_custom_info === null) $sku_custom_info = "";
	$rsInsertSku = "INSERT INTO cw_order_skus(
			ordersku_order_id,
			ordersku_sku,
			ordersku_unique_id,
			ordersku_quantity,
			ordersku_unit_price,
			ordersku_sku_total,
			ordersku_tax_rate,
			ordersku_discount_amount,
			ordersku_customval
			) VALUES (
			'".CWqueryParam($order_id)."',
			".CWqueryParam($sku_id).",
			'".CWqueryParam($sku_unique_id)."',
			".CWqueryParam($sku_qty).",
			".CWqueryParam($sku_price).",
			".CWqueryParam($sku_subtotal).",
			".CWqueryParam($sku_tax_rate).",
			".CWqueryParam($sku_discount_amount).",
			'".CWqueryParam($sku_custom_info)."'
			)";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-global.php");
		chdir($myDir);
	}
	mysql_query($rsInsertSku) or CWpageMessage(mysql_error()."<br />".$rsInsertSku);
}


// // ---------- // Get Order Record // ---------- // 
function CWquerySelectOrder($order_id) {
	$rsOrder = "SELECT *
				FROM cw_orders
				WHERE order_ID = '".CWqueryParam($order_id)."'";
	return CWqueryGetRS($rsOrder);
}

// // ---------- Select Orders (Search) ---------- // 
function CWquerySelectOrders($customer_id=null,$status_id=null,$date_start=null,$date_end=null,$id_str=NULL,$cust_name=NULL,$max_orders=null) {
	if ($customer_id === null) $customer_id = 0;
	if ($status_id === null) $status_id = 0;
	if ($date_start === null) $date_start = 0;
	if ($date_end === null) $date_end = 0;
	if ($id_str === null) $id_str = "";
	if ($cust_name === null) $cust_name = "";
	if ($max_orders === null) $max_orders = 0;
	$rsOrders = "SELECT
					cw_customers.customer_first_name,
					cw_customers.customer_last_name,
					cw_customers.customer_zip,
					cw_customers.customer_id,
					cw_orders.order_ID,
					cw_orders.order_ship_name,
					cw_orders.order_date,
					cw_orders.order_company,
					cw_orders.order_status,
					cw_orders.order_address1,
					cw_orders.order_address2,
					cw_orders.order_city,
					cw_orders.order_state,
					cw_orders.order_zip,
					cw_orders.order_total,
					cw_order_status.shipstatus_name
					FROM ((cw_customers
					INNER JOIN cw_orders
					ON cw_customers.customer_id = cw_orders.order_customer_id)
					INNER JOIN cw_order_status
					ON cw_order_status.shipstatus_id = cw_orders.order_status)
					WHERE 1=1
					";
	if ($date_start != 0) {
		$rsOrders .= "AND cw_orders.order_date >= '".CWqueryParam(date("Y-m-d H:i:s", cartweaverStrtotime($date_start)))."'
					";
	}
	if ($date_end != 0) {
		$rsOrders .= "AND cw_orders.order_date <= '".CWqueryParam(date("Y-m-d H:i:s", cartweaverStrtotime($date_end)))."'
					";
	}
	if ($status_id) {
		$rsOrders .= "AND order_status = ".CWqueryParam($status_id)."
					";
	}
	if (strlen(trim($id_str))) {
		$rsOrders .= "AND ".$_ENV["application.cw"]["sqlLower"]."(order_ID) like '".CWqueryParam(strtolower($id_str))."'
					";
	}
	if (strlen(trim($cust_name))) {
		$rsOrders .= "AND (
					".$_ENV["application.cw"]["sqlLower"]."(customer_first_name) like '".CWqueryParam(strtolower($cust_name))."'
					OR ".$_ENV["application.cw"]["sqlLower"]."(customer_last_name) like '".CWqueryParam(strtolower($cust_name))."'
					OR ".$_ENV["application.cw"]["sqlLower"]."(customer_id) like '".CWqueryParam(strtolower($cust_name))."'
					)
					";
	}
	if ($customer_id != '') {
		$rsOrders .= "AND customer_id like '".CWqueryParam($customer_id)."'
					";
	}
	$rsOrders .= "GROUP BY
					cw_orders.order_ID,
					cw_orders.order_date,
					cw_orders.order_status,
					cw_orders.order_address1,
					cw_orders.order_city,
					cw_orders.order_state,
					cw_orders.order_zip,
					cw_orders.order_total,
					cw_order_status.shipstatus_name,
					cw_customers.customer_first_name,
					cw_customers.customer_last_name,
					cw_customers.customer_zip,
					cw_customers.customer_id
					ORDER BY cw_orders.order_date DESC";
	if ($max_orders > 0) {
		$rsOrders .= " LIMIT ".CWqueryParam($max_orders);
	}
	return CWqueryGetRS($rsOrders);
}
		
// // ---------- Select Order Details w/ sku info, customer info, etc ---------- // 
function CWquerySelectOrderDetails($order_id=0) {
	$rsOrderDetails = "SELECT
							ss.shipstatus_name,
							o.*,
							c.customer_first_name,
							c.customer_last_name,
							c.customer_id,
							c.customer_email,
							p.product_name,
							p.product_id,
							p.product_custom_info_label,
							p.product_out_of_stock_message,
							s.sku_id,
							s.sku_merchant_sku_id,
                                                        s.sku_download_id,
							sm.ship_method_name,
							os.ordersku_sku,
							os.ordersku_unique_id,
							os.ordersku_quantity,
							os.ordersku_unit_price,
							os.ordersku_sku_total,
							os.ordersku_tax_rate,
							os.ordersku_discount_amount,
							(o.order_total - (o.order_tax + o.order_shipping + o.order_shipping_tax)) as order_SubTotal
						FROM (
							cw_products p
							INNER JOIN cw_skus s
							ON p.product_id = s.sku_product_id)
							INNER JOIN ((cw_customers c
								INNER JOIN (cw_order_status ss
									RIGHT JOIN (cw_ship_methods sm
										RIGHT JOIN cw_orders o
										ON sm.ship_method_id = o.order_ship_method_id)
									ON ss.shipstatus_id = o.order_status)
								ON c.customer_id = o.order_customer_id)
								INNER JOIN cw_order_skus os
								ON o.order_ID = os.ordersku_order_id)
							ON s.sku_id = os.ordersku_sku
						WHERE o.order_ID = '".CWqueryParam($order_id)."'
						ORDER BY
							p.product_name,
							s.sku_sort,
							s.sku_merchant_sku_id";
	return CWqueryGetRS($rsOrderDetails);
}
		
// // ---------- // Insert Payment to Database // ---------- // 
function CWsavePayment($order_id,$payment_method,$payment_type,$payment_amount,$payment_status,$payment_trans_id,$payment_trans_response) {
	$rsInsertPayment = '';
	$returnStr = '';
	$getNewID = '';
	// verify order id is valid 
	if(CWorderStatus($order_id) == 0) {
		$returnStr = '0-No Matching Order';
	} else {
		try {
			$rsInsertPayment = "INSERT INTO cw_order_payments (
							order_id,
							payment_method,
							payment_type,
							payment_amount,
							payment_status,
							payment_trans_id,
							payment_trans_response,
							payment_timestamp
							) VALUES (
							'".CWqueryParam($order_id)."',
							'".CWqueryParam($payment_method)."',
							'".CWqueryParam($payment_type)."',
							'".CWqueryParam($payment_amount)."',
							'".CWqueryParam($payment_status)."',
							'".CWqueryParam($payment_trans_id)."',
							'".CWqueryParam($payment_trans_response)."',
							'".date("Y-m-d H:i:s")."'
							)";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-global.php");
				chdir($myDir);
			}
			mysql_query($rsInsertPayment) or CWpageMessage(mysql_error()."<br />".$rsInsertPayment);
			// get new transaction id 
			$returnStr = mysql_insert_id();
		}
		catch(Exception $e) {
			$returnStr = '0-'.$e->getMessage();
		}
	}
	return $returnStr;
}

// // ---------- // Get Order Status (numeric) // ---------- // 
function CWorderStatus($order_id) {
	$returnStatus = 0;
	$rsOrderStatus = CWqueryGetRS("SELECT order_status as statusCode
					FROM cw_orders
					WHERE order_id = '".CWqueryParam($order_id)."'");
	if($rsOrderStatus["totalRows"] == 1) {
		$returnStatus = $rsOrderStatus["statusCode"][0];
	}
	return $returnStatus;
}

// // ---------- Update Order ---------- // 
function CWqueryUpdateOrder($order_id,$order_status=null,$order_ship_date=null,$order_ship_charge=null,$order_tracking_id=null,$order_notes=null,$order_message=null) {
	// optional arguments 
	if ($order_status === null) $order_status = 0;
	if ($order_ship_date === null) $order_ship_date = "";
	if ($order_ship_charge === null) $order_ship_charge = 0;
	if ($order_tracking_id === null) $order_tracking_id = "";
	if ($order_notes === null) $order_notes = "";
	if ($order_message === null) $order_message = "";
	$query = "UPDATE cw_orders
			SET order_id='".CWqueryParam($order_id)."'";
	if($order_status > 0) {
		$query .= ", order_status='".CWqueryParam($order_status)."'";
	}
	$query .= ", order_ship_date=";
	if($order_ship_date != "" && date($order_ship_date)) {
		$query .= "'".CWqueryParam(date("Y-m-d H:i:s", cartweaverStrtotime($order_ship_date)))."'";
	} else {
		$query .= "Null";
	}
	if($order_ship_charge > 0) {
		$query .= ", order_actual_ship_charge='".CWqueryParam(CWsqlNumber($order_ship_charge))."'";
	}
	if(strlen(trim($order_tracking_id))) {
		$query .= ", order_ship_tracking_id='".CWqueryParam($order_tracking_id)."'";
	}
	if(strlen(trim($order_notes))) {
		$query .= ", order_notes='".CWqueryParam($order_notes)."'";
	}
	if(strlen(trim($order_message))) {
		$query .= ", order_comments='".CWqueryParam($order_message)."'";
	}
	$query.= " WHERE order_id='".CWqueryParam($order_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-global.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- // Get Payments Related to Order // ---------- // 
function CWorderPayments($order_id =0) {
	$rsOrderPayments = "SELECT *
						FROM cw_order_payments
						WHERE order_id = '".CWqueryParam($order_id)."'";
	return CWqueryGetRS($rsOrderPayments);
}

// // ---------- // Get Payments Types related to Order // ---------- // 
function CWorderPaymentTypes($order_id=0) {
	$rsOrderPayments = "SELECT payment_type, payment_method, payment_status
						FROM cw_order_payments
						WHERE order_id = '".CWqueryParam($order_id)."'";
	return CWqueryGetRS($rsOrderPayments);		
}

// // ---------- // Get Payment Totals for Order // ---------- // 
function CWorderPaymentTotal($order_id=0) {
	$paymentQuery = '';
	$paymentTotalsQuery = '';
	$returnTotal = 0;
	$paymentQuery = CWorderPayments($order_id);
	for ($i = 0; $i < $paymentQuery["totalRows"]; $i++) {
		if (strtolower($paymentQuery["payment_status"][$i]) == "approved" && is_numeric($paymentQuery["payment_amount"][$i])) {
			$returnTotal += $paymentQuery["payment_amount"][$i];
		}
	}
	return $returnTotal;
}

// // ---------- // Get Order by Transaction ID // ---------- // 
function CWqueryGetTransaction($transaction_id=0) {
	$rsTrans = "SELECT *
	FROM cw_order_payments
	WHERE payment_trans_id =".CWqueryParam($transaction_id);
	return CWqueryGetRS($rsTrans);
}
?>