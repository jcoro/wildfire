<?php
/*
==========================================================
 Application: Cartweaver 4 PHP
 Copyright 2002 - 2012, all Rights Reserved
 Developer: Application Dynamics, Inc. | Cartweaver.com
 Licensing: http://www.cartweaver.com/eula
 Support: http://www.cartweaver.com/support
 ==========================================================
 File: cw-func-shipping.php
 File Date: 2012-07-08
 Description: manages shipping methods, shipping costs, and related queries
 Dependencies:
 Requires cw-func-query and cw-func-cart to be included in calling page
 ==========================================================
 NOTES:
 Function: Calculate Shipping Rates (CWgetShipRate)
 - Calculation Types
 Shipping rates are figured either through "local calculation (localcalc)",
 where we use the ranges set in the CW admin for this method, by country,
 OR through another method, e.g. "ups calculation (upsgroundcalc)", which looks up
 shipping rates through the live UPS shipping API.
 Additional methods may be added, with corresponding options in the
 Shipping Methods area of the Cartweaver admin.
 - Local Calculation variables
 There are three possible variables used for calculating local shipping;
 a base shipping fee, shipping by range, and a shipping
 extension by location.  Shipping charges can be a result of any
 one of these or a combination. This criteria is set in the admin
 section on the Company Information Page.
 - UPS
 Enter your UPS Account details into variables in the CWgetShipRate function
 (see UPS VALUES in comments, below)
 For general purposes, the UPS rate is calculated using the values for
 Company Info in the CW admin, and the stored customer address details.
 The CWgetUpsRate function can be modified to accept variables for package dimensions,
 separate ship from address, additional UPS shipping methods, and custom ship to
 address info, along with other options in the UPS API (see UPS documentation for details)
 IMPORTANT: Values returned by this function may vary from actual shipping rates.
 UPS Uses "calculated weight" depending on package size and other variables.
 Your default packaging options can be set directly in the XML content
 for the CWgetUPSrate function, below.
    

// // ---------- // Get Shipping Cost // ---------- // 
*/
function CWgetShipRate($ship_method_id, $cart_id=null, $calc_type=null, $cart_weight=null, $cart_total=null, $charge_sku_base=null, $sku_base_amount=null, $customer_id=null, $ship_country_id=null, $ship_region_id=null, $range_type=null, $charge_method_base=null, $charge_extension=null, $discount_amount=null, $discount_amount=null, $discount_amount=null, $discount_amount=null, $discount_amount=null, $discount_amount=null, $ups_access_license=null, $ups_userid=null, $ups_password=null, $ups_url=null, $weight_uom=null, $fedex_access_key=null, $fedex_password=null, $fedex_account_number=null, $fedex_meternumber=null, $fedex_url=null) {
	if ($cart_id === null) $cart_id = 0;
	if ($calc_type === null) $calc_type = "localcalc";
	// default values for cart totals - this function can be used without a cart id 
	if ($cart_weight === null) $cart_weight = 0;
	if ($cart_total === null) $cart_total = 0;
	if ($charge_sku_base === null) $charge_sku_base = true;
	if ($sku_base_amount === null) $sku_base_amount = 0;
	// defaults in client scope for customer id, shipping country and region, can be passed in to override 
	if ($customer_id === null) $customer_id = $_SESSION["cwclient"]["cwCustomerID"];
	if ($ship_country_id === null) $ship_country_id = $_SESSION["cwclient"]["cwShipCountryID"];
	if ($ship_region_id === null) $ship_region_id = $_SESSION["cwclient"]["cwShipRegionID"];
	if ($range_type === null) $range_type = $_ENV["application.cw"]["shipChargeBasedOn"];
	if ($charge_method_base === null) $charge_method_base = $_ENV["application.cw"]["shipChargeBase"];
	if ($charge_extension === null) $charge_extension = $_ENV["application.cw"]["shipChargeExtension"];
	if ($discount_amount === null) $discount_amount = 0;
	// if using UPS, set or pass in these variables 
	if ($ups_access_license === null) $ups_access_license = $_ENV["application.cw"]["upsAccessLicense"];
	if ($ups_userid === null) $ups_userid = $_ENV["application.cw"]["upsUserID"];
	if ($ups_password === null) $ups_password = $_ENV["application.cw"]["upsPassword"];
	if ($ups_url === null) $ups_url = $_ENV["application.cw"]["upsUrl"];
	if ($weight_uom === null) $weight_uom = $_ENV["application.cw"]["shipWeightUOM"];
	// if using FedEx, set or pass in these variables 
	if ($fedex_access_key === null) $fedex_access_key = $_ENV["application.cw"]["fedexAccessKey"];
	if ($fedex_password === null) $fedex_password = $_ENV["application.cw"]["fedexPassword"];
	if ($fedex_account_number === null) $fedex_account_number = $_ENV["application.cw"]["fedexAccountNumber"];
	if ($fedex_meternumber === null) $fedex_meternumber = $_ENV["application.cw"]["fedexMeterNumber"];
	if ($fedex_url === null) $fedex_url = $_ENV["application.cw"]["fedexUrl"];
    // FUNCTION DEFAULTS: DO NOT CHANGE 
    $calcValue = 0;
    $methodQuery = '';
    $rangeValue = 0;
    $rateValue = 0;
    $extensionRate = 0;
    $shipSubCalc = 0;
    $cartWeight = 0;
    $cartTotal = 0;
    // get cart data 
    $shipCart = CWgetCart($cart_id);
    // get cart shipping discount total if not provided 
    if ($discount_amount == 0 && isset($shipCart["carttotals"]["shipOrderDiscounts"])
        && $shipCart["carttotals"]["shipOrderDiscounts"] > 0) {
		$discount_amount = $shipCart["carttotals"]["shipOrderDiscounts"];
    }
    // handle totals if cart id not provided 
    if ($cart_id > 0) {
		$cartWeight = $shipCart["carttotals"]["shipWeight"];
		$cartTotal = $shipCart["carttotals"]["base"]-$shipCart["carttotals"]["shipItemDiscounts"];
    } else {
		$cartWeight = $cart_weight;
		$cartTotal = $cart_total;
    }
    // get calctype for method if a method id is provided 
    if (isset($method_id)) {
		$calctype = CWgetShipMethodCalctype($method_id);
    } else {
		$calctype = trim($calc_type);
    }
    // capture errors 
	try {
		// get details of shipping method 
		$methodQuery = CWgetShipMethodDetails(
				$cart_id,
				$ship_country_id,
				$range_type,
				$ship_method_id,
				$cartWeight,
				$cartTotal
			);
		// base rate for this shipping method 
		if ($charge_method_base) {
			$baseShipRate = $methodQuery["ship_method_rate"][0];
		}
		// add sku base rate total from cart 
		if ($charge_sku_base) {
			// if no cart provided 
			if ($cart_id == 0) {
				$baseShipRate += $sku_base_amount;
			} else {
				// if cart provided 
				$baseShipRate += $shipCart["carttotals"]["shipProductBase"];
			}
		}
		// if total is 0 due to shipitem discounts, ship range should not be calculated 
		if ($shipCart["carttotals"]["base"] <= $shipCart["carttotals"]["shipItemDiscounts"]) {
			$range_type = 'none';
			$calctype = 'localcalc';
		// if weight and subtotal for shipping are both 0, no shipping applies 
		} elseif ($shipCart["carttotals"]["shipWeight"] == 0 && $shipCart["carttotals"]["shipSubtotal"] == 0) {
			$range_type = 'none';
			$calctype = 'localcalc';
		}
		// SWITCH FOR CALCULATION TYPES - look up by range, or remote 
		switch (strtolower($calctype)) {
			// note: copy any case block to add new ship calc types 
			// UPS GROUND 
			case "upsgroundcalc":
				// lookup ups rate, passing $through 
				$rateValue = CWgetUpsRate(
						$ups_access_license,
						$ups_userid,
						$ups_password,
						$ups_url,
						'03',
						$customer_id,
						$cartWeight,
						$weight_uom
						);
				// if numeric rate is not returned, handle error string 
				if (strlen(trim($rateValue)) && !is_numeric($rateValue)) {
					$calcValue = trim($rateValue);
				}
				break;
			// UPS 3-DAY 
			case "ups3daycalc":
				// lookup ups rate, passing $through 
				$rateValue = CWgetUpsRate(
						$ups_access_license,
						$ups_userid,
						$ups_password,
						$ups_url,
						'12',
						$customer_id,
						$cartWeight,
						$weight_uom
						);
				// if numeric rate is not returned, handle error string 
				if (strlen(trim($rateValue)) && !is_numeric($rateValue)) {
					$calcValue = trim($rateValue);
				}
				break;
			// UPS NEXT DAY 
			case "upsnextdaycalc":
				// lookup ups rate, passing $through 
				$rateValue = CWgetUpsRate(
						$ups_access_license,
						$ups_userid,
						$ups_password,
						$ups_url,
						'01',
						$customer_id,
						$cartWeight,
						$weight_uom
						);
				// if numeric rate is not returned, handle error string 
				if (strlen(trim($rateValue)) && !is_numeric($rateValue)) {
					$calcValue = trim($rateValue);
				}
				break;
			// note: copy any case block to add new ship calc types 
			// FedEx GROUND 
			case "fedexgroundcalc":
				// lookup ups rate, passing $through 
				$rateValue = CWgetFedexRate(
						$fedex_access_key,
						$fedex_password,
						$fedex_account_number,
						$fedex_meternumber,
						$fedex_url,
						'FEDEX_GROUND',
						$customer_id,
						$cartWeight,
						$weight_uom
						);
				// if numeric rate is not returned, handle error string 
				if (strlen(trim($rateValue)) && !is_numeric($rateValue)) {
					$calcValue = trim($rateValue);
				}
				break;
/*			// FedEx PRIORITY OVERNIGHT 
			case "fedexpriorityovernightcalc":
				// lookup ups rate, passing $through 
				$rateValue = CWgetFedexRate(
						$fedex_access_key,
						$fedex_password,
						$fedex_account_number,
						$fedex_meternumber,
						$fedex_url,
						'PRIORITY_OVERNIGHT',
						$customer_id,
						$cartWeight,
						$weight_uom
						);
				// if numeric rate is not returned, handle error string 
				if (strlen(trim($rateValue)) && !is_numeric($rateValue)) {
					$calcValue = trim($rateValue);
				}
				break;
*/			// FedEx STANDARD OVERNIGHT 
			case "fedexstandardovernightcalc":
				// lookup ups rate, passing $through 
				$rateValue = CWgetFedexRate(
						$fedex_access_key,
						$fedex_password,
						$fedex_account_number,
						$fedex_meternumber,
						$fedex_url,
						'STANDARD_OVERNIGHT',
						$customer_id,
						$cartWeight,
						$weight_uom
						);
				// if numeric rate is not returned, handle error string 
				if (strlen(trim($rateValue)) && !is_numeric($rateValue)) {
					$calcValue = trim($rateValue);
				}
				break;
/*			// FedEx FIRST OVERNIGHT 
			case "fedexfirstovernightcalc":
				// lookup fedex rate, passing $through 
				$rateValue = CWgetFedexRate(
						$fedex_access_key,
						$fedex_password,
						$fedex_account_number,
						$fedex_meternumber,
						$fedex_url,
						'FIRST_OVERNIGHT',
						$customer_id,
						$cartWeight,
						$weight_uom
						);
				// if numeric rate is not returned, handle error string 
				if (strlen(trim($rateValue)) && !is_numeric($rateValue)) {
					$calcValue = trim($rateValue);
				}
				break;
*/			// FedEx TWO DAY 
			case "fedex2daycalc":
				// lookup fedex rate, passing $through 
				$rateValue = CWgetFedexRate(
						$fedex_access_key,
						$fedex_password,
						$fedex_account_number,
						$fedex_meternumber,
						$fedex_url,
						'FEDEX_2_DAY',
						$customer_id,
						$cartWeight,
						$weight_uom
						);
				// if numeric rate is not returned, handle error string 
				if (strlen(trim($rateValue)) && !is_numeric($rateValue)) {
					$calcValue = trim($rateValue);
				}
				break;
/*			// FedEx EXPRESS SAVER 
			case "fedexexpresssavercalc":
				// lookup fedex rate, passing $through 
				$rateValue = CWgetFedexRate(
						$fedex_access_key,
						$fedex_password,
						$fedex_account_number,
						$fedex_meternumber,
						$fedex_url,
						'FEDEX_EXPRESS_SAVER',
						$customer_id,
						$cartWeight,
						$weight_uom
						);
				// if numeric rate is not returned, handle error string 
				if (strlen(trim($rateValue)) && !is_numeric($rateValue)) {
					$calcValue = trim($rateValue);
				}
				break;
*/			
			default:
				// get the range rate 
				if ($range_type != 'none') {
					$rangeValue = $methodQuery['ship_range_amount'][0];
				}
				break;
		}
		// add range or rate value to combined base rate subtotal (defaults 0, set above) 
		if (is_numeric($rangeValue) && is_numeric($rateValue)) {
			$shipSubCalc = $baseShipRate + $rangeValue + $rateValue;
		} else {
			$shipSubCalc = $baseShipRate;
		}
		// if using shipping locale extension 
		if ($charge_extension) {
			// look up extension by region id 
			$extensionRate = CWgetShipExtension($ship_region_id);
			$extensionCost = CWcalculateTax($shipSubCalc, $extensionRate);
			$shipSubCalc = $shipSubCalc + $extensionCost;
		}
		// handle ship discount percentages if amount not defined 
		if (isset($shipCart["carttotals"]["shipOrderDiscountPercent"]) && $shipCart["carttotals"]["shipOrderDiscountPercent"] > 0 && $discount_amount == 0) {
			// get rounded amount 
			$discount_amount = min(array($shipSubCalc, round(($shipSubCalc * ($shipCart["carttotals"]["shipOrderDiscountPercent"]/100))*100)/100));
		}
		// subtract any cart shipping discounts from total 
		$shipSubCalc = $shipSubCalc - $discount_amount;
		// round to 2 places 
		if (is_numeric($shipSubCalc) && is_numeric($calcValue)) {
			$calcValue = round($shipSubCalc*100)/100;
		}
		// cannot be less than 0 
		if ($calcValue < 0) {
			$calcValue = 0;
		}
		// handle errors 
	} catch (Exception $e) {
		$calcValue = 'Rate Unavailable';
	}
	// return a numeric value with discounts applied, or a string (e.g. an error message) 
	if (is_numeric($calcValue)) {
		$calcReturn = $calcValue;
	} else {
		$calcReturn = trim($calcValue);
	}
	return $calcReturn;
}

// // ---------- Get Shipping Extension for stateprov ---------- // 

function CWgetShipExtension($statprov_id) {
	//hint="ID of the stateprov to look up - pass in 0 to select all IDs">
	$rsSelectStateProv = '';
	$returnVal = 0;
	// look up stateprov 
	$query = "SELECT stateprov_ship_ext
	FROM cw_stateprov
	WHERE stateprov_id = ".CWqueryParam($statprov_id);
	$rsSelectStateProv = CWqueryGetRS($query);
	// if a valid rate was found 
	if ($rsSelectStateProv['totalRows'] > 0 && is_numeric($rsSelectStateProv['stateprov_ship_ext'][0])) {
		$returnVal = $rsSelectStateProv['stateprov_ship_ext'][0];
		// default to 0 if no match 
	} else {
		$returnVal = 0;
	}
	return $returnVal;
}

// // ---------- // Get Shipping Method(s) Details (w/ optional customer cart information) // ---------- // 
function CWgetShipMethodDetails($cart_id=null, $ship_country_id=null, $range_type=null,$ship_method_id=null,$cart_weight=null,$cart_total=null,$match_range=null) {
	if ($cart_id === null) $cart_id = 0;
	if ($ship_country_id === null) $ship_country_id = $_SESSION["cwclient"]["cwShipCountryID"];
	if ($range_type === null) $range_type = $_ENV["application.cw"]["shipChargeBasedOn"];
	if ($ship_method_id === null) $ship_method_id = 0;
	if ($cart_weight === null) $cart_weight = 0;
	if ($cart_total === null) $cart_total = 0;
	if ($match_range === null) $match_range = true;
	$shipCart = CWgetCart($cart_id);
	$rsShipMethods = '';
	$rangeValue = 0;
	// get cart weight, total 
	// get cart weight, total 
	if ($cart_id != 0) {
		if ($range_type == 'weight') {
			$rangeValue = $shipCart["carttotals"]["shipWeight"];
		} elseif ($range_type == 'subtotal') {
			$rangeValue = $shipCart["carttotals"]["shipSubtotal"];
		}
		// if cart ID not provided, get from other $
	} else {
		if ($range_type == 'weight') {
			$rangeValue = $cart_weight;
		} elseif ($range_type == 'subtotal') {
			$rangeValue = $cart_total;
		}
	}
	// if not using ranges 
	if ($range_type == 'none') {
		$rsShipMethods_query = "
		SELECT
				m.ship_method_id,
				m.ship_method_name,
				m.ship_method_rate,
				c.ship_method_country_country_id,
				m.ship_method_sort,
				m.ship_method_calctype,
				m.ship_method_archive
		FROM cw_ship_methods m,
				cw_ship_method_countries c
		WHERE
				c.ship_method_country_country_id = '".CWqueryParam($ship_country_id)."'
		AND c.ship_method_country_method_id = m.ship_method_id ";
		if ($ship_method_id > 0) {
			$rsShipMethods_query .= " AND m.ship_method_id = ".CWqueryParam($ship_method_id)." ";
		}
		$rsShipMethods_query .= " AND NOT ship_method_archive = 1
		ORDER BY
		ship_method_sort, ship_method_name ";
		$rsShipMethods = CWqueryGetRS($rsShipMethods_query);
	}
	else {
		// if using ranges, limit methods with a matching range 
		$rsShipMethods_query = "
		SELECT
				Min(r.ship_range_from) AS min_range_from,
				Max(r.ship_range_to) AS max_range_to,
				m.ship_method_id,
				m.ship_method_name,
				m.ship_method_rate,
				r.ship_range_amount,
				r.ship_range_from,
				r.ship_range_to,
				c.ship_method_country_country_id,
				m.ship_method_sort,
				m.ship_method_calctype,
				m.ship_method_archive
		FROM
				(cw_ship_method_countries c
				INNER JOIN cw_ship_methods m
				ON c.ship_method_country_method_id = m.ship_method_id)
				LEFT JOIN cw_ship_ranges r
				ON m.ship_method_id = r.ship_range_method_id ";
		// if looking up price by range, return only a single range 
		if ($match_range) {
			$rsShipMethods_query .= " WHERE (r.ship_range_from <= '".CWqueryParam($rangeValue)."' 
				AND r.ship_range_to >= '".CWqueryParam($rangeValue)."')
				OR NOT m.ship_method_calctype = 'localcalc'";
		}
		$rsShipMethods_query .= "
		GROUP BY
				m.ship_method_id,
				m.ship_method_name,
				c.ship_method_country_country_id,
				m.ship_method_Sort,
				m.ship_method_archive
		HAVING (
						((Min(r.ship_range_from) <= '".CWqueryParam($rangeValue)."')
						AND (Max(r.ship_range_to) >= '".CWqueryParam($rangeValue)."')
						AND (c.ship_method_country_country_id = '".CWqueryParam($ship_country_id)."')
						) OR (NOT m.ship_method_calctype = 'localcalc' AND c.ship_method_country_country_id = '".CWqueryParam($ship_country_id)."')
				) ";
		if ($ship_method_id > 0) {
			$rsShipMethods_query .= " AND m.ship_method_id = '".CWqueryParam($ship_method_id)."' ";
		}
		$rsShipMethods_query .= " AND NOT (m.ship_method_archive = 1)
		ORDER BY
				m.ship_method_sort";
		$rsShipMethods = CWqueryGetRS($rsShipMethods_query);
	}

	// /end if using ranges 
	return $rsShipMethods;
}

// // ---------- // Get shipping method calculation type by ID // ---------- // 
function CWgetShipMethodCalctype($ship_method_id=0) {
	$rsShipMethodName_query = "
	SELECT ship_method_calctype
	FROM cw_ship_methods
	WHERE ship_method_id = '".CWqueryParam($ship_method_id)."'";
	$rsShipMethodName = CWqueryGetRS($rsShipMethodName_query);
	return $rsShipMethodName['ship_method_calctype'][0];
}

// // ---------- // Get shipping method name by ID // ---------- // 
function CWgetShipMethodName($ship_method_id=0) {
	$query = "
	SELECT ship_method_name
	FROM cw_ship_methods
	WHERE ship_method_id = '".CWqueryParam($ship_method_id)."'";
	$rs = CWqueryGetRS($query);
	return $rs['ship_method_name'][0];
}

// // ---------- // UPS Lookup function // ---------- // 
function CWgetUpsRate($ups_license, $ups_userid, $ups_password, $ups_url=null, $ups_service_code=null, $customer_id=null, $weight_val=null, $weight_uom=null) {
	if ($ups_url === null) $ups_url = "https://onlinetools.ups.com/ups.app/xml/Rate";
	if ($ups_service_code === null) $ups_service_code = "01";
	if ($customer_id === null) $customer_id = 0;
	if ($weight_val === null) $weight_val = 1;
	if ($weight_uom === null) $weight_uom = "lbs";
	// DEBUG: uncomment to test server availability, should return "Rate" for server name 
    /*
	$ups_curl = curl_init();
	curl_setopt($ups_curl, CURLOPT_URL, "https://wwwcie.ups.com/ups.app/xml/Rate");
	curl_setopt($ups_curl, CURLOPT_HEADER, 0);
	curl_setopt($ups_curl, CURLOPT_HTTPGET, 1);
	curl_setopt($ups_curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ups_curl, CURLOPT_RETURNTRANSFER, 1);
	$textResponse = curl_exec($ups_curl);
	curl_close($ups_curl);
	// handle result 
	var_dump($textResponse);
	exit;
	*/
	$rateValue = 0;
	$xmlData = array();
	$customerQuery = "";
	try {
		$customerQuery = CWqueryGetRS("SELECT cw_customers.customer_id,
                                        cw_customers.customer_type_id,
                                        cw_customers.customer_first_name,
                                        cw_customers.customer_last_name,
                                        cw_customers.customer_phone,
                                        cw_customers.customer_ship_company,
                                        cw_customers.customer_ship_name,
                                        cw_customers.customer_ship_address1,
                                        cw_customers.customer_ship_address2,
                                        cw_customers.customer_ship_city,
                                        cw_customers.customer_ship_zip,
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
                        WHERE cw_customer_stateprov.customer_state_destination='ShipTo'
                        AND ".$_ENV["application.cw"]["sqlLower"]."(customer_id) = '".CWqueryParam(strtolower($customer_id))."'");
		// if customer found 
		if ($customerQuery["totalRows"]) {
			// set up ups vars 
			$xmlData["upslicense"] = trim($ups_license);
			$xmlData["upsuserid"] = trim($ups_userid);
			$xmlData["upspassword"] = trim($ups_password);
			$xmlData["upsURL"] = trim($ups_url);
			$xmlData["upsServiceCode"] = trim($ups_service_code);
			$xmlData["customerid"] = trim($customer_id);
			// weight 
			if (is_numeric($weight_val) && $weight_val > 0) {
				$xmlData["shipweight"] = round($weight_val);
			} else {
				$xmlData["shipweight"] = 1;
			}
			$xmlData["shipweightuom"] = trim($weight_uom);
			// delivery address 
			$xmlData["shipCompany"] = $customerQuery["customer_ship_company"][0];
			$xmlData["shipName"] = $customerQuery["customer_ship_name"][0];
			$xmlData["shipPhone"] = $customerQuery["customer_phone"][0];
			$xmlData["shipAddress1"] = $customerQuery["customer_ship_address1"][0];
			$xmlData["shipAddress2"] = $customerQuery["customer_ship_address2"][0];
			$xmlData["shipCity"] = $customerQuery["customer_ship_city"][0];
			$xmlData["shipState"] = $customerQuery["stateprov_name"][0];
			$xmlData["shipCountry"] = $customerQuery["country_code"][0];
			$xmlData["shipPostCode"] = $customerQuery["customer_ship_zip"][0];
			// ship from address 
			$xmlData["fromName"] = $_ENV["application.cw"]["companyName"];
			$xmlData["fromPhone"] = $_ENV["application.cw"]["companyPhone"];
			$xmlData["fromAddress1"] = $_ENV["application.cw"]["companyAddress1"];
			$xmlData["fromAddress2"] = $_ENV["application.cw"]["companyAddress2"];
			$xmlData["fromCity"] = $_ENV["application.cw"]["companyCity"];
			$xmlData["fromState"] = $_ENV["application.cw"]["companyState"];
			$xmlData["fromCountry"] = $_ENV["application.cw"]["companyShipCountry"];
			$xmlData["fromPostCode"] = $_ENV["application.cw"]["companyZip"];
			// assemble xml 
			$xmlStr = "<?xml version=\"1.0\" ?>
<AccessRequest xml:lang='en-US'>
	<AccessLicenseNumber>
		".$xmlData["upslicense"]."
	</AccessLicenseNumber>
	<UserId>
		".$xmlData["upsuserid"]."
	</UserId>
	<Password>
		".$xmlData["upspassword"]."
	</Password>
</AccessRequest>
<?xml version=\"1.0\" ?>
<RatingServiceSelectionRequest>
	<Request>
		<TransactionReference>
			<CustomerContext>
				Rate Request
			</CustomerContext>
			<XpciVersion>
				1.0
			</XpciVersion>
		</TransactionReference>
		<RequestAction>
			Rate
		</RequestAction>
		<RequestOption>
			Rate
		</RequestOption>
	</Request>
	";
			// pickup info: code type 01 = daily pickup, 03 = customer counter 
			$xmlStr .= "<PickupType>
		<Code>
			01
		</Code>
	</PickupType>
	<Shipment>
		<Description>
			UPS Shipping
		</Description>
		";
			// company info 
			$xmlStr .= "<Shipper>
			<Address>
				<AddressLine1>
					".$xmlData["fromAddress1"]."
				</AddressLine1>
				<AddressLine2>
					".$xmlData["fromAddress2"]."
				</AddressLine2>
				<City>
					".$xmlData["fromCity"]."
				</City>
				<StateProvinceCode>
					".$xmlData["fromState"]."
				</StateProvinceCode>
				<PostalCode>
					".$xmlData["fromPostCode"]."
				</PostalCode>
				<CountryCode>
					".$xmlData["fromCountry"]."
				</CountryCode>
			</Address>
		</Shipper>
		";
			// customer info 
			$xmlStr .= "<ShipTo>
			<CompanyName>
				".$xmlData["shipCompany"]."
			</CompanyName>
			<AttentionName>
				".$xmlData["shipName"]."
			</AttentionName>
			<PhoneNumber>
				".$xmlData["shipPhone"]."
			</PhoneNumber>
			<Address>
				<AddressLine1>
					".$xmlData["shipAddress1"]."
				</AddressLine1>
				<AddressLine2>
					".$xmlData["shipAddress2"]."
				</AddressLine2>
				<City>
					".$xmlData["shipCity"]."
				</City>
			   <PostalCode>
					".$xmlData["shipPostCode"]."
				</PostalCode>
				<CountryCode>
					".$xmlData["shipCountry"]."
				</CountryCode>
			</Address>
		</ShipTo>
		";
			// shipping from address 
			$xmlStr .= "<ShipFrom>
			<CompanyName>
				".$xmlData["fromName"]."
			</CompanyName>
			<PhoneNumber>
				".$xmlData["fromPhone"]."
			</PhoneNumber>
			<Address>
				<AddressLine1>
					".$xmlData["fromAddress1"]."
				</AddressLine1>
				<AddressLine2>
					".$xmlData["fromAddress2"]."
				</AddressLine2>
				<City>
					".$xmlData["fromCity"]."
				</City>
				<StateProvinceCode>
					".$xmlData["fromState"]."
				</StateProvinceCode>
				<PostalCode>
					".$xmlData["fromPostCode"]."
				</PostalCode>
				<CountryCode>
					".$xmlData["fromCountry"]."
				</CountryCode>
			</Address>
		</ShipFrom>
		<Service>
			<Code>
				".$xmlData["upsServiceCode"]."
			</Code>
		</Service>
		<Package>
			<PackagingType>
				<Code>
					";
			// 02 = package, 00 = unknown 
			$xmlStr .= "02
				</Code>
			</PackagingType>
			";
			// average large box dimensions provided, can be altered as needed 
			$xmlStr .= "<Dimensions>
			";
			// uom can be IN|CM 
			$xmlStr .= "<UnitOfMeasurement>
					<Code>
						IN
					</Code>
				</UnitOfMeasurement>
				<Length>
					18
				</Length>
				<Width>
					14
				</Width>
				<Height>
					9
				</Height>
			</Dimensions>
			<Description>
				Rate
			</Description>
			<PackageWeight>
				<UnitOfMeasurement>
					<Code>
						".$xmlData["shipweightuom"]."
					</Code>
				</UnitOfMeasurement>
				<Weight>
					".$xmlData["shipweight"]."
				</Weight>
			</PackageWeight>
		</Package>
		<ShipmentServiceOptions />
	</Shipment>
</RatingServiceSelectionRequest>";
			// send xml request 
			$ups_curl = curl_init();
			curl_setopt($ups_curl, CURLOPT_URL, $xmlData["upsURL"]);
			curl_setopt($ups_curl, CURLOPT_HEADER, 0);
			curl_setopt($ups_curl, CURLOPT_POST, 1);
			curl_setopt($ups_curl, CURLOPT_POSTFIELDS, $xmlStr);
			curl_setopt($ups_curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ups_curl, CURLOPT_RETURNTRANSFER, 1);
			$textResponse = curl_exec($ups_curl);
			curl_close($ups_curl);
			// handle result 
			if ($textResponse) {
				$xmlParams = CWgetXMLParams($textResponse);
				// if response is success code (1) 
				if ($xmlParams["RATINGSERVICESELECTIONRESPONSE"]["RESPONSE"]["RESPONSESTATUSCODE"] == 1) {
					// set the rate value as provided 
					$rateValue = $xmlParams["RATINGSERVICESELECTIONRESPONSE"]["RATEDSHIPMENT"]["TOTALCHARGES"]["MONETARYVALUE"];
					// if not success, get message 
				} else {
					$rateValue = 'Rate Unavailable';
					// use this line for full error response 
					//$rateValue = 'Rate Unavailable - ' . $xmlParams["RATINGSERVICESELECTIONRESPONSE"]["RESPONSE"]["ERROR"]["ERRORDESCRIPTION"];
				}
			} else {
				$rateValue = 'Rate Unavailable';
			}
			// if no cartweaver customer is found 
		} else {
			$rateValue = 'Address Data Unavailable';
		}
	} catch (Exception $e) {
		$rateValue = 'Rate Unavailable';
	}
	return $rateValue;
}

if (!function_exists("CWgetXMLResponseArray")) {
function CWgetXMLParams($textResponse) {
	$xmlVals = array();
	$xmlIndex = array();
	$xmlParse = xml_parser_create();
	xml_parse_into_struct($xmlParse, $textResponse, $xmlVals, $xmlIndex);
	xml_parser_free($xmlParse);
	$xmlParams = array();
	$xmlLevel = array();
	foreach ($xmlVals as $xmlElement) {
		switch ($xmlElement["type"]) {
			case "open":
				//open element
				if (array_key_exists("attributes", $xmlElement)) {
					//if attributes exist, add the level to a list 
					$xmlLevel[$xmlElement["level"]] = $xmlElement["attributes"];
				} else {
					//set the level to the tag value
					$xmlLevel[$xmlElement["level"]] = $xmlElement["tag"];
				}
				break;
			case "complete":
				$xmlParams = CWgetXMLResponseArray($xmlParams, 1, $xmlElement, $xmlLevel);
				break;
		}
	}
	return $xmlParams;
}

function CWgetXMLResponseArray($fieldRef, $levelStart, $xmlElement, $xmlLevel) {
	if ($levelStart < $xmlElement["level"]) {
		if (!isset($fieldRef[$xmlLevel[$levelStart]])) {
			$fieldRef[$xmlLevel[$levelStart]] = array();
		}
		$fieldRef[$xmlLevel[$levelStart]] = CWgetXMLResponseArray($fieldRef[$xmlLevel[$levelStart]], $levelStart+1, $xmlElement, $xmlLevel);
		return $fieldRef;
	}
	if (isset($xmlElement["value"])) {
		$fieldRef[$xmlElement["tag"]] = $xmlElement["value"];
	} else {
		$fieldRef[$xmlElement["tag"]] = "";
	}
	return $fieldRef;
}
}

// // ---------- // FedEx Lookup function // ---------- // 
function CWgetFedexRate($fedex_access_key,$fedex_password,$fedex_account_number, $fedex_meternumber, $fedex_url=null, $fedex_service=null, $customer_id=null, $weight_val=null, $weight_uom=null) {
	if ($fedex_url === null) $fedex_url = "https://gateway.fedex.com:443/GatewayDC";
	if ($fedex_service === null) $fedex_service = "FEDEX_GROUND";
	if ($customer_id === null) $customer_id = 0;
	if ($weight_val === null) $weight_val = 1;
	if ($weight_uom === null) $weight_uom = "LB";
	if (strtolower($weight_uom) == "lbs") $weight_uom = "LB";
	if (strtolower($weight_uom) == "kgs") $weight_uom = "KG";
	if (strtolower($weight_uom) == "oz") $weight_uom = "OZ";
	if (strtolower($weight_uom) == "g") $weight_uom = "G";
	// DEBUG: uncomment to test server availability, should return "Rate" for server name 
    /*
	$fedex_curl = curl_init();
	curl_setopt($fedex_curl, CURLOPT_URL, $fedex_url);
	curl_setopt($fedex_curl, CURLOPT_HEADER, 0);
	curl_setopt($fedex_curl, CURLOPT_HTTPGET, 1);
	curl_setopt($fedex_curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($fedex_curl, CURLOPT_RETURNTRANSFER, 1);
	$textResponse = curl_exec($fedex_curl);
	curl_close($fedex_curl);
	// handle result 
	var_dump($textResponse);
	exit;
	*/
	$rateValue = 0;
	$xmlData = array();
	$customerQuery = "";

	try {
		$customerQuery = CWqueryGetRS("SELECT cw_customers.customer_id,
                                        cw_customers.customer_type_id,
                                        cw_customers.customer_first_name,
                                        cw_customers.customer_last_name,
                                        cw_customers.customer_phone,
                                        cw_customers.customer_ship_company,
                                        cw_customers.customer_ship_name,
                                        cw_customers.customer_ship_address1,
                                        cw_customers.customer_ship_address2,
                                        cw_customers.customer_ship_city,
                                        cw_customers.customer_ship_zip,
                                        cw_stateprov.stateprov_code,
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
                        WHERE cw_customer_stateprov.customer_state_destination='ShipTo'
                        AND ".$_ENV["application.cw"]["sqlLower"]."(customer_id) = '".CWqueryParam(strtolower($customer_id))."'");
		// if customer found 
		if ($customerQuery["totalRows"]) {
			// set up fedex vars 
			$xmlData["fedexAccessKey"] = trim($fedex_access_key);
			$xmlData["fedexPassword"] = trim($fedex_password);
			$xmlData["fedexAccountNumber"] = trim($fedex_account_number);
			$xmlData["fedexMeterNumber"] = trim($fedex_meternumber);
			$xmlData["fedexURL"] = trim($fedex_url);
			$xmlData["fedexService"] = trim($fedex_service);
			$xmlData["customerid"] = trim($customer_id);
			// weight 
			if (is_numeric($weight_val) && $weight_val > 0) {
				$xmlData["shipweight"] = round($weight_val);
			} else {
				$xmlData["shipweight"] = 1;
			}
			$xmlData["shipweightuom"] = trim($weight_uom);
			// delivery address 
			$xmlData["shipCompany"] = $customerQuery["customer_ship_company"][0];
			$xmlData["shipName"] = $customerQuery["customer_ship_name"][0];
			$xmlData["shipPhone"] = $customerQuery["customer_phone"][0];
			$xmlData["shipAddress1"] = $customerQuery["customer_ship_address1"][0];
			$xmlData["shipAddress2"] = $customerQuery["customer_ship_address2"][0];
			$xmlData["shipCity"] = $customerQuery["customer_ship_city"][0];
			$xmlData["shipState"] = $customerQuery["stateprov_code"][0];
			$xmlData["shipCountry"] = $customerQuery["country_code"][0];
			$xmlData["shipPostCode"] = $customerQuery["customer_ship_zip"][0];
			// ship from address 
			$xmlData["fromName"] = $_ENV["application.cw"]["companyName"];
			$xmlData["fromPhone"] = $_ENV["application.cw"]["companyPhone"];
			$xmlData["fromAddress1"] = $_ENV["application.cw"]["companyAddress1"];
			$xmlData["fromAddress2"] = $_ENV["application.cw"]["companyAddress2"];
			$xmlData["fromCity"] = $_ENV["application.cw"]["companyCity"];
			$xmlData["fromState"] = $_ENV["application.cw"]["companyState"];
			$xmlData["fromCountry"] = $_ENV["application.cw"]["companyShipCountry"];
			$xmlData["fromPostCode"] = $_ENV["application.cw"]["companyZip"];
			// assemble xml 
			$xmlStr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:v10=\"http://fedex.com/ws/rate/v10\">
	<soapenv:Header/>
	<soapenv:Body>
		<v10:RateRequest>
			<v10:WebAuthenticationDetail>
				<v10:UserCredential>
					<v10:Key>".$xmlData["fedexAccessKey"]."</v10:Key>
					<v10:Password>".$xmlData["fedexPassword"]."</v10:Password>
				</v10:UserCredential>
			</v10:WebAuthenticationDetail>
			<v10:ClientDetail>
				<v10:AccountNumber>".$xmlData["fedexAccountNumber"]."</v10:AccountNumber>
				<v10:MeterNumber>".$xmlData["fedexMeterNumber"]."</v10:MeterNumber>
			</v10:ClientDetail>
			<v10:TransactionDetail>
				<v10:CustomerTransactionId>".$_SESSION["cwclient"]["cwCartID"]."</v10:CustomerTransactionId>
			</v10:TransactionDetail>
			<v10:Version>
				<v10:ServiceId>crs</v10:ServiceId>
				<v10:Major>10</v10:Major>
				<v10:Intermediate>0</v10:Intermediate>
				<v10:Minor>0</v10:Minor>
			</v10:Version>
			<v10:ReturnTransitAndCommit>true</v10:ReturnTransitAndCommit>
			<v10:RequestedShipment>
				<v10:ShipTimestamp>".date('c')."</v10:ShipTimestamp>
				<v10:DropoffType>REGULAR_PICKUP</v10:DropoffType>
				<v10:ServiceType>".$xmlData["fedexService"]."</v10:ServiceType>
				<v10:PackagingType>YOUR_PACKAGING</v10:PackagingType>
				<v10:Shipper>
					<v10:Contact>
						<v10:CompanyName>".$xmlData["fromName"]."</v10:CompanyName>
						<v10:PhoneNumber>".$xmlData["fromPhone"]."</v10:PhoneNumber>
					</v10:Contact>
					<v10:Address>
						<v10:City>".$xmlData["fromCity"]."</v10:City>
						<v10:StateOrProvinceCode>".$xmlData["fromState"]."</v10:StateOrProvinceCode>
						<v10:PostalCode>".$xmlData["fromPostCode"]."</v10:PostalCode>
						<v10:CountryCode>".$xmlData["fromCountry"]."</v10:CountryCode>
					</v10:Address>
				</v10:Shipper>
				<v10:Recipient>
					<v10:Contact>
						<v10:PersonName>".$xmlData["shipName"]."</v10:PersonName>
						<v10:CompanyName>".$xmlData["shipCompany"]."</v10:CompanyName>
						<v10:PhoneNumber>".$xmlData["shipPhone"]."</v10:PhoneNumber>
					</v10:Contact>
					<v10:Address>
						<v10:City>".$xmlData["shipCity"]."</v10:City>
						<v10:StateOrProvinceCode>".$xmlData["shipState"]."</v10:StateOrProvinceCode>
						<v10:PostalCode>".$xmlData["shipPostCode"]."</v10:PostalCode>
						<v10:CountryCode>".$xmlData["shipCountry"]."</v10:CountryCode>
					</v10:Address>
				</v10:Recipient>
				<v10:ShippingChargesPayment>
					<v10:PaymentType>SENDER</v10:PaymentType>
					<v10:Payor>
						<v10:AccountNumber>".$xmlData["fedexAccountNumber"]."</v10:AccountNumber>
						<v10:CountryCode>".$xmlData["fromCountry"]."</v10:CountryCode>
					</v10:Payor>
				</v10:ShippingChargesPayment>
				<v10:RateRequestTypes>ACCOUNT</v10:RateRequestTypes>
				<v10:PackageCount>1</v10:PackageCount>
				<v10:RequestedPackageLineItems>
					<v10:SequenceNumber>1</v10:SequenceNumber>
					<v10:GroupPackageCount>1</v10:GroupPackageCount>
					<v10:Weight>
						<v10:Units>".$xmlData["shipweightuom"]."</v10:Units>
						<v10:Value>".$xmlData["shipweight"]."</v10:Value>
					</v10:Weight>
					<v10:Dimensions>
						<v10:Length>18</v10:Length>
						<v10:Width>14</v10:Width>
						<v10:Height>9</v10:Height>
						<v10:Units>IN</v10:Units>
					</v10:Dimensions>
				</v10:RequestedPackageLineItems>
			</v10:RequestedShipment>
		</v10:RateRequest>
	</soapenv:Body>
</soapenv:Envelope>";


// DEBUG: uncomment to show xml being sent
//print('< rows="45" cols="180">'.$xmlStr.'</textarea>'); 
//exit;

			// send xml request 
			$fedex_curl = curl_init();
			curl_setopt($fedex_curl, CURLOPT_URL, $xmlData["fedexURL"]);
			curl_setopt($fedex_curl, CURLOPT_HEADER, 0);
			curl_setopt($fedex_curl, CURLOPT_POST, 1);
			curl_setopt($fedex_curl, CURLOPT_POSTFIELDS, $xmlStr);
			curl_setopt($fedex_curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($fedex_curl, CURLOPT_RETURNTRANSFER, 1);
			$textResponse = curl_exec($fedex_curl);
			curl_close($fedex_curl);
			
// DEBUG: uncomment to show xml response
//set_error_handler("FEDEXERROR", E_STRICT); //function at bottom of file
//error_reporting(E_STRICT);
//print('<textarea rows="45" cols="180">'.$textResponse.'</textarea>'); 
//exit;
			// handle result 
			if ($textResponse) {
				$xmlParams = CWgetXMLParamsFedEx($textResponse);
/*
// DEBUG: uncomment to show xml response formatted
echo $xmlData["fedexURL"]."<pre>";
echo htmlentities($xmlStr);
echo "</pre>";
echo "<pre>";
echo htmlentities($textResponse);
echo "</pre>";
echo "<pre>";
var_dump($xmlParams);
echo "</pre>";
//die();
*/
				if (isset($xmlParams["SOAPENV:ENVELOPE"]["SOAPENV:BODY"]["V10:RATEREPLY"])) {
					$response = $xmlParams["SOAPENV:ENVELOPE"]["SOAPENV:BODY"]["V10:RATEREPLY"];
					// if response is success code (1) 
					if (isset($response["V10:RATEREPLYDETAILS"])) {
						$cheapest = -1;
						if (isset($response["V10:RATEREPLYDETAILS"][0])) {
							//array of rates
							foreach ($response["V10:RATEREPLYDETAILS"] as $rates) {
								if (isset($rates["V10:RATEREPLYDETAILS"]["V10:RATEDSHIPMENTDETAILS"][0])) {
									//array of details
									foreach ($rates["V10:RATEREPLYDETAILS"]["V10:RATEDSHIPMENTDETAILS"] as $details) {
										if (isset($details["V10:RATEDSHIPMENTDETAILS"]["V10:SHIPMENTRATEDETAIL"]["V10:TOTALNETCHARGE"]["V10:AMOUNT"])) {
											$myCharge = $details["V10:RATEDSHIPMENTDETAILS"]["V10:SHIPMENTRATEDETAIL"]["V10:TOTALNETCHARGE"]["V10:AMOUNT"];
											if (isset($details["V10:RATEDSHIPMENTDETAILS"]["V10:SHIPMENTRATEDETAIL"]["V10:TOTALREBATES"]["V10:AMOUNT"])) {
												$myCharge -= $details["V10:RATEDSHIPMENTDETAILS"]["V10:SHIPMENTRATEDETAIL"]["V10:TOTALREBATES"]["V10:AMOUNT"];
											}
											if ($cheapest < 0 || $myCharge < $cheapest) {
												$cheapest = $myCharge;
											}
										}
									}
									//end array of details
								} else if (isset($rates["V10:RATEREPLYDETAILS"]["V10:RATEDSHIPMENTDETAILS"])) {
									//single detail line
									$details = $rates["V10:RATEREPLYDETAILS"]["V10:RATEDSHIPMENTDETAILS"];
									if (isset($details["V10:SHIPMENTRATEDETAIL"]["V10:TOTALNETCHARGE"]["V10:AMOUNT"])) {
										$myCharge = $details["V10:SHIPMENTRATEDETAIL"]["V10:TOTALNETCHARGE"]["V10:AMOUNT"];
										if (isset($details["V10:SHIPMENTRATEDETAIL"]["V10:TOTALREBATES"]["V10:AMOUNT"])) {
											$myCharge -= $details["V10:SHIPMENTRATEDETAIL"]["V10:TOTALREBATES"]["V10:AMOUNT"];
										}
										if ($cheapest < 0 || $myCharge < $cheapest) {
											$cheapest = $myCharge;
										}
									}
									//end single detail line
								}
							}
							//end array of rates
						} else if (isset($response["V10:RATEREPLYDETAILS"])) {
							//single rate line
							$rates = $response["V10:RATEREPLYDETAILS"];
							if (isset($rates["V10:RATEDSHIPMENTDETAILS"][0])) {
								//array of details
								foreach ($rates["V10:RATEDSHIPMENTDETAILS"] as $details) {
									if (isset($details["V10:RATEDSHIPMENTDETAILS"]["V10:SHIPMENTRATEDETAIL"]["V10:TOTALNETCHARGE"]["V10:AMOUNT"])) {
										$myCharge = $details["V10:RATEDSHIPMENTDETAILS"]["V10:SHIPMENTRATEDETAIL"]["V10:TOTALNETCHARGE"]["V10:AMOUNT"];
										if (isset($details["V10:RATEDSHIPMENTDETAILS"]["V10:SHIPMENTRATEDETAIL"]["V10:TOTALREBATES"]["V10:AMOUNT"])) {
											$myCharge -= $details["V10:RATEDSHIPMENTDETAILS"]["V10:SHIPMENTRATEDETAIL"]["V10:TOTALREBATES"]["V10:AMOUNT"];
										}
										if ($cheapest < 0 || $myCharge < $cheapest) {
											$cheapest = $myCharge;
										}
									}
								}
								//end array of details
							} else if (isset($rates["V10:RATEDSHIPMENTDETAILS"])) {
								//single detail line
								$details = $rates["V10:RATEDSHIPMENTDETAILS"];		
								if (isset($details["V10:SHIPMENTRATEDETAIL"]["V10:TOTALNETCHARGE"]["V10:AMOUNT"])) {
									$myCharge = $details["V10:SHIPMENTRATEDETAIL"]["V10:TOTALNETCHARGE"]["V10:AMOUNT"];
									if (isset($details["V10:SHIPMENTRATEDETAIL"]["V10:TOTALREBATES"]["V10:AMOUNT"])) {
										$myCharge -= $details["V10:SHIPMENTRATEDETAIL"]["V10:TOTALREBATES"]["V10:AMOUNT"];
									}
									if ($cheapest < 0 || $myCharge < $cheapest) {
										$cheapest = $myCharge;
									}
								} 
								//end single detail line
							}
							//end single rate line
						}
						// set the rate value as provided 
						if ($cheapest > 0) {
							$rateValue = $cheapest;
						}
					} else {
						$rateValue = 'Rate Unavailable';
						// show full error/reason in test mode
						
						if (isset($_ENV["application.cw"]["appTestModeEnabled"]) && $_ENV["application.cw"]["appTestModeEnabled"]){
							if (isset($response["V10:HIGHESTSEVERITY"]) && strtoupper($response[	"V10:HIGHESTSEVERITY"]) == "ERROR") {
							$rateValue = $response["V10:NOTIFICATIONS"]["V10:MESSAGE"];
							} else {
								$rateValue = "Lookup Error, Rate Unavailable";
								if (isset($response[0])) {
									foreach ($response as $error) {
										if ($error["V10:NOTIFICATIONS"]["V10:MESSAGE"] != ''){
											$rateValue .= (($rateValue != "") ? "<br />\n" : "").'<span class="smallPrint">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Reason: '.$error["V10:NOTIFICATIONS"]["V10:MESSAGE"].'</span>';
										};	
									}
								}
							}
						}
						// end test mode detailed errors
						
						}
				} else {
					$rateValue = 'Rate Unavailable';
				}
			} else {
				$rateValue = 'Rate Unavailable';
			}
			// if no cartweaver customer is found 
		} else {
			$rateValue = 'Address Data Unavailable';
		}
	} catch (Exception $e) {
		$rateValue = 'Rate Unavailable';
	}
	return $rateValue;
}

// XML Parsing functions
if (!function_exists("CWgetXMLResponseArrayFedEx")) {
function CWgetXMLParamsFedEx($textResponse) {
	$xmlVals = array();
	$xmlIndex = array();
	$xmlParse = xml_parser_create();
	xml_parse_into_struct($xmlParse, $textResponse, $xmlVals, $xmlIndex);
	xml_parser_free($xmlParse);
	$xmlParams = array();
	$xmlLevel = array();
	foreach ($xmlVals as $xmlElement) {
		switch ($xmlElement["type"]) {
			case "open":
				//open element, set the level to the tag value
				$xmlLevel[$xmlElement["level"]] = $xmlElement["tag"];
				break;
			case "complete":
				//we have a leaf-level tag
				$xmlParams = CWgetXMLResponseArrayFedEx($xmlParams, 1, $xmlElement, $xmlLevel);
				break;
			case "close":
				//close a tag and unset the corresponding level in the xmlLevel array
				$xmlParams = CWcloseXMLTagFedEx($xmlParams, 1, $xmlElement, $xmlLevel);
                                unset($xmlLevel[$xmlElement["level"]]);
				break;
		}
	}
	return $xmlParams;
}

function CWcloseXMLTagFedEx($fieldRef, $levelStart, $xmlElement, $xmlLevel) {
	$subElm = $xmlLevel[$levelStart];
	if ($levelStart < $xmlElement["level"]) {
		//we're at a branch level
		if ($levelStart > 1 && isset($fieldRef[0][$xmlLevel[$levelStart-1]][$subElm])) {
			//do array style handling
			$lastIndex = CWgetXMLLastNumericIndexFedEx($fieldRef);
			$fieldRef[$lastIndex][$xmlLevel[$levelStart-1]][$subElm] = CWcloseXMLTagFedEx($fieldRef[$lastIndex][$xmlLevel[$levelStart-1]][$subElm], $levelStart+1, $xmlElement, $xmlLevel);
		} else if (isset($fieldRef[$subElm])) {
			//do non-array handling
			$fieldRef[$subElm] = CWcloseXMLTagFedEx($fieldRef[$subElm], $levelStart+1, $xmlElement, $xmlLevel);
		}
	} else {
		if ($levelStart > 1 && isset($fieldRef[0][$xmlLevel[$levelStart-1]][$subElm])) {
			$lastIndex = CWgetXMLLastNumericIndexFedEx($fieldRef);
			$fieldRef[$lastIndex][$xmlLevel[$levelStart-1]][$subElm]["_CLOSED"] = true;
		} else {
			$fieldRef[$subElm]["_CLOSED"] = true;
		}
	}
	return $fieldRef;
}

function CWgetXMLResponseArrayFedEx($fieldRef, $levelStart, $xmlElement, $xmlLevel) {
	if ($levelStart < $xmlElement["level"]) {
		$isMulti = false;
		$newField = null;
		if (!isset($fieldRef[$xmlLevel[$levelStart]][0]) && isset($fieldRef[$xmlLevel[$levelStart]]["_CLOSED"])) {
			//we're looking at the open of a field that has already been closed
			//create an array of the existing field and make a newe element for the field we want to use
			$newFieldArr = array(array($xmlLevel[$levelStart] => $fieldRef[$xmlLevel[$levelStart]]),
						array($xmlLevel[$levelStart] => array()));
			$fieldRef[$xmlLevel[$levelStart]] = $newFieldArr;
			$isMulti = true;
			$newField = $fieldRef[$xmlLevel[$levelStart]][1][$xmlLevel[$levelStart]];
		} else if (isset($fieldRef[$xmlLevel[$levelStart]][0][$xmlLevel[$levelStart]])) {
			//we have an array of fields here, use the last field as our reference
			$lastIndex = CWgetXMLLastNumericIndexFedEx($fieldRef[$xmlLevel[$levelStart]]);
			$isMulti = true;
			if (isset($fieldRef[$xmlLevel[$levelStart]][$lastIndex][$xmlLevel[$levelStart]]["_CLOSED"])) {
				$lastIndex++;
				$fieldRef[$xmlLevel[$levelStart]][$lastIndex] = array($xmlLevel[$levelStart] => array());
			}
			$newField = $fieldRef[$xmlLevel[$levelStart]][$lastIndex][$xmlLevel[$levelStart]];
		}
		if ($newField === null) {
			//we didn't find an array, create the new field if needed and set it to the field reference
			if (!isset($fieldRef[$xmlLevel[$levelStart]])) {
				$fieldRef[$xmlLevel[$levelStart]] = array();
			}
			$newField = $fieldRef[$xmlLevel[$levelStart]];
		}
		//recursively call the function for the next level
		$newField = CWgetXMLResponseArrayFedEx($newField, $levelStart+1, $xmlElement, $xmlLevel);
		if ($isMulti) {
			//set the value in the array style for multiple copies
			$lastIndex = CWgetXMLLastNumericIndexFedEx($fieldRef[$xmlLevel[$levelStart]]);
			if ($lastIndex < 0) {
				$fieldRef[$xmlLevel[$levelStart]][0] = array();
				$lastIndex++;
			}
			$fieldRef[$xmlLevel[$levelStart]][$lastIndex][$xmlLevel[$levelStart]] = $newField;
		} else {
			//set the level to the new field
			$fieldRef[$xmlLevel[$levelStart]] = $newField;
		}
		return $fieldRef;
	}
	//we're at the leaf level, set the value
	if (isset($xmlElement["value"])) {
		$fieldRef[$xmlElement["tag"]] = $xmlElement["value"];
	} else {
		$fieldRef[$xmlElement["tag"]] = "";
	}
	return $fieldRef;
}

function CWgetXMLLastNumericIndexFedEx($tArr) {
    $retVal = -1;
    for ($counter=0; $counter<sizeof($tArr); $counter++) {
        if (array_key_exists($counter, $tArr)) {
            $retVal = $counter;
        } else {
            break;
        }
    }
    return $retVal;
}
//function FEDEXERROR($errno, $errstr, $errfile, $errline) {
//    echo $errno." - ".$errline." - ".$errstr."<br>".$errfile;
//    die();
//}
}
?>