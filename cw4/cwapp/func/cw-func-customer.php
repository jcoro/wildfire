<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, all Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-customer.php
File Date: 2012-02-01
Description:
Manages customer information, and related queries
Dependencies:
Requires cw-func-query to be included in calling page
==========================================================
*/
// // ---------- // CWgetCustomer // ---------- // 
function CWgetCustomer($customer_id = 0) {
	$customerQuery = '';
	$shippingQuery = '';
	$customer = array();
	$varname = '';
	if(strlen(trim($customer_id)) && trim($customer_id) != "0") {
		// get all customer data 
		$customerQuery = CWquerySelectCustomerDetails($customer_id);
		// if customer is found 
		if($customerQuery['totalRows'] == 1) {
			$customer["customerid"] = $customer_id;
			// write all vars into customer struct 
			foreach ($customerQuery as $cc => $valueList) {
				// remove customer_ prefix from all variables for easier use 
				$varname = str_ireplace('customer_','',$cc);
				$varname = strtolower(str_replace('_','',$varname));			
				// do not use ID, handled separately 
				if ($cc != "customer_id") {
					$customer[$varname] = $valueList[0];
				}
			}
			// QUERY: get customer's shipping info (customer id)
			$shippingQuery = CWquerySelectCustomerShipping($_SESSION["cwclient"]["cwCustomerID"]);
			// add shipping info to customer struct 
			$customer['shipcountry'] = $shippingQuery['country_name'][0];
			$customer['shipcountrycode'] = $shippingQuery['country_code'][0];
			$customer['shipcountryid'] = $shippingQuery['country_id'][0];
			$customer['shipstateprovname'] = $shippingQuery['stateprov_name'][0];
			$customer['shipstateprovid'] = $shippingQuery['stateprov_id'][0];
			// if no customer found 
		} else {
			$customer['customerid'] = 0;
		}
	}
	return $customer;		
}
// // ---------- Select Customer Details ---------- // 
function CWquerySelectCustomerDetails($customer_id = NULL) {
	$rsCustomerDetails = "SELECT cw_customers.customer_id,
							cw_customers.customer_type_id,
							cw_customers.customer_first_name,
							cw_customers.customer_last_name,
							cw_customers.customer_address1,
							cw_customers.customer_address2,
							cw_customers.customer_city,
							cw_customers.customer_zip,
							cw_customers.customer_ship_name,
							cw_customers.customer_ship_company,
							cw_customers.customer_ship_address1,
							cw_customers.customer_ship_address2,
							cw_customers.customer_ship_city,
							cw_customers.customer_ship_zip,
							cw_customers.customer_phone,
							cw_customers.customer_phone_mobile,
							cw_customers.customer_email,
							cw_customers.customer_company,
							cw_customers.customer_username,
							cw_customers.customer_password,
							cw_stateprov.stateprov_name,
							cw_stateprov.stateprov_id,
							cw_customer_stateprov.customer_state_destination,
							cw_countries.country_name,
							cw_countries.country_code,
							cw_countries.country_id
			
				FROM (((cw_customers
						INNER JOIN cw_customer_stateprov
						ON cw_customers.customer_id = cw_customer_stateprov.customer_state_customer_id)
			
						INNER JOIN cw_stateprov
						ON cw_stateprov.stateprov_id = cw_customer_stateprov.customer_state_stateprov_id)
			
						INNER JOIN cw_countries
						ON cw_countries.country_id = cw_stateprov.stateprov_country_id)
			
					WHERE cw_customer_stateprov.customer_state_destination='BillTo'
					AND ".$_ENV["application.cw"]["sqlLower"]."(customer_id) = '".CWqueryParam(strtolower($customer_id))."'";
	return CWqueryGetRS($rsCustomerDetails);
}
// // ---------- Select Customer Shipping Details ---------- // 
function CWquerySelectCustomerShipping($customer_id="") {
	$rscustomerShpping = "SELECT cw_customers.customer_id,
						cw_customers.customer_type_id,
						cw_customers.customer_first_name,
						cw_customers.customer_last_name,
						cw_customers.customer_ship_name,
						cw_customers.customer_ship_address1,
						cw_customers.customer_ship_address2,
						cw_customers.customer_ship_company,
						cw_customers.customer_ship_city,
						cw_customers.customer_ship_zip,
						cw_stateprov.stateprov_name,
						cw_stateprov.stateprov_id,
						cw_customer_stateprov.customer_state_destination,
						cw_countries.country_name,
						cw_countries.country_id,
						cw_countries.country_code
					FROM (((cw_customers
					INNER JOIN cw_customer_stateprov
					ON cw_customers.customer_id = cw_customer_stateprov.customer_state_customer_id)
		
					INNER JOIN cw_stateprov
					ON cw_stateprov.stateprov_id = cw_customer_stateprov.customer_state_stateprov_id)
		
					INNER JOIN cw_countries
					ON cw_countries.country_id = cw_stateprov.stateprov_country_id)
		
				WHERE cw_customer_stateprov.customer_state_destination='ShipTo'
				AND ".$_ENV["application.cw"]["sqlLower"]."(customer_id) = '".CWqueryParam(strtolower($customer_id))."'";
	return CWqueryGetRS($rscustomerShpping);
}

// // ---------- Select Customer Orders ---------- // 
function CWquerySelectCustomerOrders($customer_id=0,$max_return=0) {
	$rsCustOrders = "SELECT *
						FROM cw_orders
						WHERE order_customer_id = '".CWqueryParam($customer_id)."'
						ORDER BY order_date DESC";
	if ($max_return > 0) { $rsCustOrders .= " LIMIT ".CWqueryParam($max_return); }
	return CWqueryGetRS($rsCustOrders);
}

// // ---------- Select Customer Order Details ---------- // 
function CWquerySelectCustomerOrderDetails($customer_id=NULL,$max_return=0) {
	$rsCustomerOrderDetails = "SELECT
								cw_orders.order_ID,
								cw_orders.order_date,
								cw_orders.order_total,
								cw_order_skus.ordersku_sku,
								cw_skus.sku_merchant_sku_id,
								cw_products.product_name,
								cw_products.product_id
							FROM
								cw_products
								INNER JOIN (cw_skus
									INNER JOIN (cw_orders
										INNER JOIN cw_order_skus
										ON cw_orders.order_ID = cw_order_skus.ordersku_order_id)
									ON cw_skus.sku_id = cw_order_skus.ordersku_sku)
								ON cw_products.product_id = cw_skus.sku_product_id
							WHERE
								cw_orders.order_customer_id = '".CWqueryParam($customer_id)."'
							ORDER BY
								cw_orders.order_date DESC";
	if ($max_return > 0) { $rsCustomerOrderDetails .= " LIMIT ".CWqueryParam($max_return); }
	return CWqueryGetRS($rsCustomerOrderDetails);
}

// // ---------- Select Customer Types ---------- // 
function CWquerySelectCustomerTypes() {
	$rsCustTypes = "SELECT *
					FROM cw_customer_types
					ORDER BY customer_type_name";
	return CWqueryGetRS($rsCustTypes);
}

// // ---------- Insert Customer---------- // 
function CWqueryInsertCustomer($customer_type_id=null,$customer_firstname=null,$customer_lastname=null,$customer_email=null,$customer_username=null,$customer_password=null,$customer_company=null,$customer_phone=null,$customer_phone_mobile=null,$customer_address1=null,$customer_address2=null,$customer_city=null,$customer_state=null,$customer_zip=null,$customer_ship_name=null,$customer_ship_company=null,$customer_ship_address1=null,$customer_ship_address2=null,$customer_ship_city=null,$customer_ship_state=null,$customer_ship_zip=null,$prevent_duplicates=null,$customer_guest=null) {
	// make sure email and username are unique 
	if ($customer_type_id === null) $customer_type_id = "0";
	if ($customer_firstname === null) $customer_firstname = 0;
	if ($customer_lastname === null) $customer_lastname = 0;
	if ($customer_email === null) $customer_email = "";
	if ($customer_username === null) $customer_username = "";
	if ($customer_password === null) $customer_password = "";
	if ($customer_company === null) $customer_company = "";
	if ($customer_phone === null) $customer_phone = "";
	if ($customer_phone_mobile === null) $customer_phone_mobile = "";
	if ($customer_address1 === null) $customer_address1 = "";
	if ($customer_address2 === null) $customer_address2 = "";
	if ($customer_city === null) $customer_city = "";
	if ($customer_state === null) $customer_state = 0;
	if ($customer_zip === null) $customer_zip = "";
	if ($customer_ship_name === null) $customer_ship_name = "";
	if ($customer_ship_company === null) $customer_ship_company = "";
	if ($customer_ship_address1 === null) $customer_ship_address1 = "";
	if ($customer_ship_address2 === null) $customer_ship_address2 = "";
	if ($customer_ship_city === null) $customer_ship_city = "";
	if ($customer_ship_state === null) $customer_ship_state = 0;
	if ($customer_ship_zip === null) $customer_ship_zip = "";
	if ($prevent_duplicates === null) $prevent_duplicates = true;
	if ($customer_guest === null) $customer_guest = 0;
	// make sure email and username are unique 
	$checkDupEmail = "";
	$newCustID = "";
	$checkDupusername = "";
	$newUUID = uniqid();
	$randomStr = rand(100000,999999);
	// no duplicate checking for new guest accounts 
	if ($customer_guest) {
		$prevent_duplicates = false;
	}
	$checkDupEmail = "SELECT customer_email
						FROM cw_customers";
	// if checking for duplicates, check against existing username 
	if($prevent_duplicates) {
		$checkDupEmail .= " WHERE customer_email = '".CWqueryParam(trim($customer_email))."'
							AND NOT customer_guest = 1";
	// if ignoring duplicates, pass dummy string to match 
	} else {
		$checkDupEmail .= " WHERE customer_email = '".CWqueryParam($randomStr)."'";
	}
	$checkDupEmail = CWqueryGetRS($checkDupEmail);
	// if we have a dup, stop and return a message 
	if($checkDupEmail['totalRows']) {
		$newCustID = '0-Email';
	// if no dup email, contine 
	} else {
		$checkDupusername = "SELECT customer_username
								FROM cw_customers";
		// if checking for duplicates, check against existing username 
		if($prevent_duplicates) {
			$checkDupusername .= " WHERE customer_username = '".CWqueryParam(trim($customer_username))."'
									AND NOT customer_guest = 1";
		// if ignoring duplicates, pass dummy string to match 
		} else {
			$checkDupusername .= " WHERE customer_username = '".CWqueryParam($randomStr)."'";
		}
		$checkDupusername = CWqueryGetRS($checkDupusername);
		// if we have a dup, stop and return a message 
		if($checkDupusername['totalRows']) {
			$newCustID = '0-username';
		// if no dup username, continue 
		} else {
			$newCustID = substr($newUUID,0,6).date("y-d-m", CWtime());
			// insert main customer record 
			$query = "INSERT INTO cw_customers
					(
					customer_id
					,customer_type_id
					,customer_first_name
					,customer_last_name
					,customer_email
					,customer_username
					,customer_password
					,customer_guest
					,customer_company
					,customer_address1
					,customer_address2
					,customer_city
					,customer_zip
					,customer_ship_name
					,customer_ship_company
					,customer_ship_address1
					,customer_ship_address2
					,customer_ship_city
					,customer_ship_zip
					,customer_phone
					,customer_phone_mobile
					,customer_date_modified
					,customer_date_added
					)
					VALUES
					(
					'".CWqueryParam($newCustID)."',
					'".CWqueryParam($customer_type_id)."',
					'".CWqueryParam($customer_firstname)."',
					'".CWqueryParam($customer_lastname)."',";
			if(strlen(trim($customer_email))) { $query.="'".CWqueryParam($customer_email)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_username))) { $query.="'".CWqueryParam($customer_username)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_password))) { $query.="'".CWqueryParam($customer_password)."',"; } else { $query.="NULL,"; }
			$query.="'".CWqueryParam($customer_guest)."',";
			if(strlen(trim($customer_company))) { $query.="'".CWqueryParam($customer_company)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_address1))) { $query.="'".CWqueryParam($customer_address1)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_address2))) { $query.="'".CWqueryParam($customer_address2)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_city))) { $query.="'".CWqueryParam($customer_city)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_zip))) { $query.="'".CWqueryParam($customer_zip)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_ship_name))) { $query.="'".CWqueryParam($customer_ship_name)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_ship_company))) { $query.="'".CWqueryParam($customer_ship_company)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_ship_address1))) { $query.="'".CWqueryParam($customer_ship_address1)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_ship_address2))) { $query.="'".CWqueryParam($customer_ship_address2)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_ship_city))) { $query.="'".CWqueryParam($customer_ship_city)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_ship_zip))) { $query.="'".CWqueryParam($customer_ship_zip)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_phone))) { $query.="'".CWqueryParam($customer_phone)."',"; } else { $query.="NULL,"; }
			if(strlen(trim($customer_phone_mobile))) { $query.="'".CWqueryParam($customer_phone_mobile)."',"; } else { $query.="NULL,"; }
			$query.="'".CWqueryParam(date("Y-m-d H:i:s", CWtime()))."','".CWqueryParam(date("Y-m-d H:i:s", CWtime()))."')";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-global.php");
				chdir($myDir);
			}
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
			// Insert Billing state 
			if($customer_state>0) {
				$queryIns="INSERT INTO cw_customer_stateprov
						(
						customer_state_customer_id,
						customer_state_stateprov_id,
						customer_state_destination
						)
						VALUES
						(
						'".CWqueryParam($newCustID)."',
						".CWqueryParam($customer_state).",
						'BillTo'
						)";
				 mysql_query($queryIns,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$queryIns);		
			}
			// Insert Shipping State 
			if($customer_ship_state >0) {
				$queryIns2="INSERT INTO cw_customer_stateprov
						(
						customer_state_customer_id,
						customer_state_stateprov_id,
						customer_state_destination
						)
						VALUES
						(
						'".CWqueryParam($newCustID)."',
						".CWqueryParam($customer_ship_state).",
						'ShipTo'
						)";
				mysql_query($queryIns2,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$queryIns2);		
			}
		}
		// /END check username dup 					
	}
	// /END check email dup 
	// pass back the ID of the new customer, or error 0 message 
	return $newCustID;		
}

// // ---------- Update Customer---------- // 
function CWqueryUpdateCustomer($customer_id=null,$customer_type_id=null,$customer_firstname=null,$customer_lastname=null,$customer_email=null,$customer_username=null,$customer_password=null,$customer_company=null,$customer_phone=null,$customer_phone_mobile=null,$customer_address1=null,$customer_address2=null,$customer_city=null,$customer_state=null,$customer_zip=null,$customer_ship_name=null,$customer_ship_company=null,$customer_ship_address1=null,$customer_ship_address2=null,$customer_ship_city=null,$customer_ship_state=null,$customer_ship_zip=null,$prevent_duplicates=null) {
	// ID and Name are required 
	if ($customer_id === null) $customer_id = 0;
	if ($customer_type_id === null) $customer_type_id = 0;
	if ($customer_firstname === null) $customer_firstname = 0;
	if ($customer_lastname === null) $customer_lastname = 0;
	// Others optional, default NULL 
	if ($customer_email === null) $customer_email = "";
	if ($customer_username === null) $customer_username = "";
	if ($customer_password === null) $customer_password = "";
	if ($customer_company === null) $customer_company = "";
	if ($customer_phone === null) $customer_phone = "";
	if ($customer_phone_mobile === null) $customer_phone_mobile = "";
	if ($customer_address1 === null) $customer_address1 = "";
	if ($customer_address2 === null) $customer_address2 = "";
	if ($customer_city === null) $customer_city = "";
	if ($customer_state === null) $customer_state = 0;
	if ($customer_zip === null) $customer_zip = "";
	if ($customer_ship_name === null) $customer_ship_name = "";
	if ($customer_ship_company === null) $customer_ship_company = "";
	if ($customer_ship_address1 === null) $customer_ship_address1 = "";
	if ($customer_ship_address2 === null) $customer_ship_address2 = "";
	if ($customer_ship_city === null) $customer_ship_city = "";
	if ($customer_ship_state === null) $customer_ship_state = 0;
	if ($customer_ship_zip === null) $customer_ship_zip = "";
	// Validate unique email/username 
	if ($prevent_duplicates === null) $prevent_duplicates = true;
	$checkDupEmail = '';
	$checkDupusername = '';
	$updateCustID = '';
	$randomStr = rand(100000,999999);
	// verify email and username are unique 
	// check email 
	if(strlen(trim($customer_email))) {
		$checkDupEmail="SELECT customer_email
						FROM cw_customers";
		// if checking for duplicates, check against existing email 
		if($prevent_duplicates) {
			$checkDupEmail.= " WHERE customer_email = '".CWqueryParam(trim($customer_email))."'
								AND NOT customer_guest = 1";
		// if ignoring duplicates, pass dummy string to match 
		} else {
			$checkDupEmail.= " WHERE customer_email = '".CWqueryParam($randomStr)."'";
		}
		$checkDupEmail.= " AND NOT customer_id='".CWqueryParam($customer_id)."'";
		$checkDupEmail = CWqueryGetRS($checkDupEmail);
		// if we have a dup, return a message 
		if($checkDupEmail['totalRows']) {
			$updateCustID = '0-Email';
		}
	}
	// check username 
	if(strlen(trim($customer_username))) {
		$checkDupusername = "SELECT customer_username
							FROM cw_customers";
		// if checking for duplicates, check against existing username 
		if($prevent_duplicates) {
			$checkDupusername.= " WHERE customer_username = '".CWqueryParam(trim($customer_username))."'
									AND NOT customer_guest = 1";
		// if ignoring duplicates, pass dummy string to match 
		} else {
			$checkDupusername.= " WHERE customer_username = '".CWqueryParam($randomStr)."'";
		}
		$checkDupusername .= " AND NOT customer_id='".CWqueryParam($customer_id)."'";
		$checkDupusername = CWqueryGetRS($checkDupusername);
		// if we have a dup, return a message 
		if($checkDupusername['totalRows']) {
			$updateCustID = '0-username';
		}
	}
	// if no duplicates 
	if(!(substr($updateCustID,0,2) == '0-')) {
		// update main customer record 
		$query = "UPDATE cw_customers SET
				customer_type_id = ".CWqueryParam($customer_type_id)."
				,customer_first_name = '".CWqueryParam($customer_firstname)."'
				, customer_last_name = '".CWqueryParam($customer_lastname)."'
				, customer_email=";
		if(strlen(trim($customer_email))) { $query.="'".CWqueryParam($customer_email)."'"; } else { $query.="NULL"; }
		$query.=", customer_username=";
		if(strlen(trim($customer_username))) { $query.="'".CWqueryParam($customer_username)."'"; } else { $query.="NULL"; }
		$query.=", customer_password=";
		if(strlen(trim($customer_password))) { $query.="'".CWqueryParam($customer_password)."'"; } else { $query.="NULL"; }
		$query.=", customer_company=";
		if(strlen(trim($customer_company))) { $query.="'".CWqueryParam($customer_company)."'"; } else { $query.="NULL"; }
		$query.=", customer_address1=";
		if(strlen(trim($customer_address1))) { $query.="'".CWqueryParam($customer_address1)."'"; } else { $query.="NULL"; }
		$query.=", customer_address2=";
		if(strlen(trim($customer_address2))) { $query.="'".CWqueryParam($customer_address2)."'"; } else { $query.="NULL"; }
		$query.=", customer_city=";
		if(strlen(trim($customer_city))) { $query.="'".CWqueryParam($customer_city)."'"; } else { $query.="NULL"; }
		$query.=", customer_zip=";
		if(strlen(trim($customer_zip))) { $query.="'".CWqueryParam($customer_zip)."'"; } else { $query.="NULL"; }
		$query.=", customer_ship_name=";
		if(strlen(trim($customer_ship_name))) { $query.="'".CWqueryParam($customer_ship_name)."'"; } else { $query.="NULL"; }
		$query.=", customer_ship_company=";
		if(strlen(trim($customer_ship_company))) { $query.="'".CWqueryParam($customer_ship_company)."'"; } else { $query.="NULL"; }
		$query.=", customer_ship_address1=";
		if(strlen(trim($customer_ship_address1))) { $query.="'".CWqueryParam($customer_ship_address1)."'"; } else { $query.="NULL"; }
		$query.=", customer_ship_address2=";
		if(strlen(trim($customer_ship_address2))) { $query.="'".CWqueryParam($customer_ship_address2)."'"; } else { $query.="NULL"; }
		$query.=", customer_ship_city=";
		if(strlen(trim($customer_ship_city))) { $query.="'".CWqueryParam($customer_ship_city)."'"; } else { $query.="NULL"; }
		$query.=", customer_ship_zip=";
		if(strlen(trim($customer_ship_zip))) { $query.="'".CWqueryParam($customer_ship_zip)."'"; } else { $query.="NULL"; }
		$query.=", customer_phone=";
		if(strlen(trim($customer_phone))) { $query.="'".CWqueryParam($customer_phone)."'"; } else { $query.="NULL"; }
		$query.=", customer_phone_mobile=";
		if(strlen(trim($customer_phone_mobile))) { $query.="'".CWqueryParam($customer_phone_mobile)."'"; } else { $query.="NULL"; }
		$query.=", customer_date_modified ='".CWqueryParam(date("Y-m-d H:i:s", CWtime()))."'
				WHERE customer_id='".CWqueryParam($customer_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-global.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		// Update Billing state 
		if($customer_state > 0) {
			$query1="UPDATE cw_customer_stateprov SET
					customer_state_stateprov_id = ".CWqueryParam($customer_state)."
					WHERE customer_state_customer_id = '".CWqueryParam($customer_id)."' AND customer_state_destination = 'BillTo'";
			mysql_query($query1,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query1);
		}
		// Update Shipping State 
		if($customer_ship_state > 0) {
			$query2="UPDATE cw_customer_stateprov SET
					customer_state_stateprov_id = ".CWqueryParam($customer_ship_state)."
					WHERE customer_state_customer_id = '".CWqueryParam($customer_id)."' AND customer_state_destination = 'ShipTo'";
			mysql_query($query2,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query2);
		}
		$updateCustID = $customer_id;		
	}
	// /END check dups 
	return $updateCustID;
}

// // ---------- Delete Customer ---------- // 
function CWqueryDeleteCustomer($customer_id) {
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-global.php");
		chdir($myDir);
	}
	// delete customer state relationships 
	$query = "DELETE FROM cw_customer_stateprov WHERE customer_state_customer_id = '".CWqueryParam($customer_id)."'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// delete customer 
	$query1 = "DELETE FROM cw_customers WHERE customer_id='".CWqueryParam($customer_id)."'";
	mysql_query($query1,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query1);
}

// // ---------- // Customer Login // ---------- // 
function CWqueryCustomerLogin($login_username,$login_password) {
	$rsCustomerLogin = "SELECT customer_id, customer_ship_city, customer_username
						FROM cw_customers
						WHERE customer_username = '".CWqueryParam($login_username)."'
						AND customer_password = '".CWqueryParam($login_password)."'";
	return CWqueryGetRS($rsCustomerLogin);
}

// // ---------- // Customer Password Lookup // ---------- // 
function CWqueryCustomerLookup($customer_email) {
	$rsCustomerLookup = "SELECT customer_id, customer_username, customer_password, customer_email
						FROM cw_customers
						WHERE customer_email = '".CWqueryParam($customer_email)."' AND NOT customer_guest = 1";
	return CWqueryGetRS($rsCustomerLookup);
}
?>
