<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, all Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-tax.php
File Date: 2012-03-02
Description: manages tax calculations and related queries
==========================================================
*/
// // ---------- // CWgetShipTax: Get shipping tax amount for cart shipping total // ---------- // 
function CWgetShipTax($country_id,$region_id,$taxable_total,$cart,$calc_type=null) {
	if ($calc_type === null) $calc_type = $_ENV["application.cw"]["taxCalctype"];
	// NO tax by default 
	$TaxAmount = 0;
	$TaxRate = 0 ;
	$rsShipTax = "";
	$rsMaxTax = "" ;
	switch (strtolower($calc_type)) {
		// default (localtax) 
		default;
			// if tax type is groups 
			if (strtolower($_ENV["application.cw"]["taxSystem"]) == "groups") {
				$rsShipTax = CWqueryGetRS("SELECT t.tax_rate_percentage as ShipTaxRate,
											r.tax_region_ship_tax_method as ship_tax_method
											FROM cw_tax_regions r
											RIGHT JOIN cw_tax_rates t
											ON r.tax_region_id = t.tax_rate_region_id
											WHERE
											NOT r.tax_region_ship_tax_method = 'No Tax'
											AND NOT r.tax_region_ship_tax_method = 'No Vat'
											AND r.tax_region_country_id = '".CWqueryParam($country_id)."'
											AND (r.tax_region_state_id = '".CWqueryParam($region_id)."'
												OR r.tax_region_state_id = 0)");
				// if tax record exist 
				if ($rsShipTax["totalRows"] > 0) {
					// Determine method of charging tax to shipping 
					// Charge tax based on the highest taxed item currently in the cart 
					if ($rsShipTax["ship_tax_method"][0] == "Highest Item Taxed") {
						// Check the cart to find the highest taxed item 
						$ShipTaxRate = 0;
						for ($i=0; $i<sizeof($cart["cartitems"]); $i++) {
							if ($cart["cartitems"][$i]["tax"] > $ShipTaxRate) {
								$ShipTaxRate = $cart["cartitems"][$i]["taxRates"]["displayTax"];
							}
						}
						$TaxAmount = CWcalculateTax($taxable_total, $ShipTaxRate);
						// /end highest item taxed 
					} else {
						// The tax rate is set to a specific tax group 
						$TaxAmount = CWcalculateTax($taxable_total, $rsShipTax["ShipTaxRate"][0]);
					}
				}
				// /end if tax records exist 
				// if type is not 'groups' 
			} else {
				$TaxRate = CWgetBasicTax($country_id, $region_id);
				$TaxAmount = CWcalculateTax($taxable_total, $TaxRate);
			}
			break;
			// /end localtax 
	}
	return $TaxAmount;
}

// // ---------- // CWgetBasicTax: get a general tax rate from db lookup // ---------- // 
function CWgetBasicTax($country_id,$region_id=0) {
	if($region_id == 0) {
		if(CWcountryHasStates($country_id)) {
			// // user is not logged in, and country has states, so no default tax can be assumed 
			return 0;
		}
		$rsbasicTax ="SELECT stateprov_tax as taxrate_percentage
							FROM cw_stateprov
							WHERE stateprov_country_id = ".CWqueryParam($country_id);
	} else {
		$rsbasicTax = "SELECT stateprov_tax as taxrate_percentage
							FROM cw_stateprov
							WHERE stateprov_id =".CWqueryParam($region_id);
	}
	$result = CWqueryGetRS($rsbasicTax);
	if($result['totalRows']> 0) {
		return $result['taxrate_percentage'][0];
	} else {
		return 0;
	}
}

// // ---------- // CWcountryHasStates: lookup states for any country // ---------- // 
function CWcountryHasStates($country_id) {
	$rsCWCountryHasStates = "SELECT COUNT(*) as TheStateCount
							FROM cw_countries c
							INNER JOIN cw_stateprov s
							ON c.country_id = s.stateprov_country_id
							WHERE stateprov_archive = 0
							AND stateprov_country_id = ".CWqueryParam($country_id)."
							AND stateprov_name <> 'None'";
	$res = CWqueryGetRS($rsCWCountryHasStates);
	if (!isset($res['TheStateCount']) || !sizeof($res['TheStateCount']) || $res['TheStateCount'][0] == 0) {
		return false;
	} else {
		return true;
	}
}

// // ---------- // CWcalculateTax: calculate tax on a given amount with a given percentage // ---------- // 
function CWcalculateTax($taxable_total,$tax_rate) {
	return CWdecimalRound($taxable_total * ($tax_rate/100));
}
// // ---------- // CWdecimalRound: round to 2 places // ---------- // 
function CWdecimalRound($number_value) {
	return round(($number_value * 100))/100;
}
// // ---------- // CWgetProductTax // ---------- // 
function CWgetProductTax($product_id,$country_id,$region_id,$calc_type=null) {
	if ($calc_type === null) $calc_type = $_ENV["application.cw"]["taxCalctype"];
	$rs = "";
	$taxRates = array();
	$temp = array();
	$taxRates['displayTax'] = 0;
	$taxRates['calcTax'] = 0;
	$taxRates['rates'] = array();
	// switch lookup based on calctype 
	switch (strtolower($calc_type)) {
		// localtax (default) 
		default:
			if (strtolower($_ENV["application.cw"]["taxSystem"]) == "groups") {
				// Get the product tax information, including current tax rate and tax type 
				$rs = "SELECT r.tax_rate_percentage as taxrate_percentage, tr.tax_region_label
		FROM cw_tax_regions tr
		RIGHT JOIN ((cw_tax_groups g
		INNER JOIN cw_products p
		ON g.tax_group_id = p.product_tax_group_id)
		LEFT JOIN cw_tax_rates r
		ON g.tax_group_id = r.tax_rate_group_id)
		ON tr.tax_region_id = r.tax_rate_region_id
		WHERE
			p.product_id = ".$product_id."
			AND (
				(
					tax_region_country_id = ".$country_id."
					AND tax_region_state_id = 0)
				OR
				(
					tax_region_country_id = ".$country_id."
					AND tax_region_state_id = ".$region_id." )
				)
		";
			} else {
				// "general" tax on one or more states -- all products 
				if ($region_id == 0) {
					if (CWcountryHasStates($country_id)) {
						// // user is not logged in, and country has states, so no default tax can be assumed 
						return $taxRates;
					}
					$rs = "SELECT stateprov_tax as taxrate_percentage,
			stateprov_code + ' ".$_ENV["application.cw"]["taxSystemLabel"]."' as tax_region_label,
			stateprov_name
			FROM cw_stateprov
			WHERE stateprov_country_id = ".CWqueryParam($country_id)."";
				} else {
					$rs = "SELECT stateprov_tax as taxrate_percentage,
			stateprov_code + ' ".$_ENV["application.cw"]["taxSystemLabel"]."' as tax_region_label,
			stateprov_name
			FROM cw_stateprov
			WHERE stateprov_id = ".CWqueryParam($region_id)."";
				}
			}
			$rs = CWqueryGetRS($rs);
			for ($i=0; $i<$rs["totalRows"]; $i++) {
				$temp = array();
				$taxRates["displayTax"] += $rs["taxrate_percentage"][$i];
				$temp["Label"] = $rs["tax_region_label"][$i];
				$temp["displayTax"] = $rs["taxrate_percentage"][$i];
				if ($temp["displayTax"] != 0) {
					$temp["calcTax"] = ($temp["displayTax"] / 100) + 1;
				} else {
					$temp["calcTax"] = 0;
				}
				$taxRates["rates"][] = $temp;
			}
			if ($taxRates["displayTax"] != 0) {
				$taxRates["appliedTax"] = $taxRates["displayTax"] / 100;
				$taxRates["calcTax"] = ($taxRates["displayTax"] / 100) + 1;
			}
			break;
	}
	return $taxRates;
}


// // ---------- // CWgetCartTax: get whole-cart taxes from API service (i.e. AvaTax) // ---------- // 
function CWgetCartTax($cart_id=null, $customer_id=null, $calc_type=null) {
	if ($cart_id === null) $cart_id = 0;
	if ($customer_id === null) $customer_id = 0;
	if ($calc_type === null) $calc_type = $_ENV["application.cw"]["taxCalctype"];
	$cartTaxData = array();
	$cartTaxData["error"] = '';
	$cartTaxData["xml"] = '';
	$cartTaxData["amounts"] = array();
	// override default/empty values with session 
	if ($cart_id == 0 && isset($_SESSION["cwclient"]["cwCartID"]) && $_SESSION["cwclient"]["cwCartID"] != 0) {
		$cart_id = $_SESSION["cwclient"]["cwCartID"];
	}
	if ($customer_id == 0 && isset($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] != 0) {
		$customer_id = $_SESSION["cwclient"]["cwCustomerID"];
	}
	// if cart id is not available
	if ($cart_id == 0) {
		$cartTaxData["error"] = 'Cart Data Unavailable';
	// if customer id is not available 
	} else if ($customer_id == 0) {
		$cartTaxData["error"] = 'Address Data Unavailable';
	// if customer id and cart id provided, continue processing 
	} else {
		switch (strtolower($calc_type)) {
			// AVALARA / AvaTax lookup 
			case "avatax":
				// get array of tax data from ava tax 
				$cartTaxData = CWgetAvalaraTax(
						$cart_id,
						$customer_id
					);
				break;
			// /end AvaTax 
			// default calc type (no processing) 
			default:
				$cartTaxData["error"] = 'Cart tax unavailable for method '.$calc_type;
				break;
		}
	}
	// end if cart id / customer id ok 
	// set posted order xml into request scope for use in checkout processing 
	$_ENV["request.cwpage"]["cartTaxXML"] = $cartTaxData["xml"];
	// return error message if any exists 
	if (strlen(trim($cartTaxData["error"]))) {
		$returnData = $cartTaxData["error"];
	// if no error, return amounts 
	} else {
		$returnData = $cartTaxData["amounts"];
	}
	// return error or amounts structure 
	return $returnData;
}

function CWgetTaxLineFromData($cartTaxData, $skuUniqueID) {
	if (is_array($cartTaxData)) {
		foreach ($cartTaxData as $key => $data) {
			if ($data["itemID"] == $skuUniqueID) {
				return $data;
			}
		}
	}
	return null;
}

// // ---------- // get Avalara tax // ---------- // 
function CWgetAvalaraTax($cart_id=null, $customer_id=null, $error_email=null, $avalara_transaction_type=null, $avalara_account=null, $avalara_license=null, $avalara_url=null, $avalara_default_tax_code=null, $avalara_ship_tax_code=null, $avalara_company_code=null) {
	if ($cart_id === null) $cart_id = 0;
	if ($customer_id === null) $customer_id = 0;
	if ($error_email === null) $error_email = $_ENV["application.cw"]["taxErrorEmail"];
	if ($avalara_transaction_type === null) $avalara_transaction_type = "SalesOrder";
	if ($avalara_account === null) $avalara_account = $_ENV["application.cw"]["avalaraID"];
	if ($avalara_license === null) $avalara_license = $_ENV["application.cw"]["avalaraKey"];
	if ($avalara_url === null) $avalara_url = $_ENV["application.cw"]["avalaraUrl"];
	if ($avalara_default_tax_code === null) $avalara_default_tax_code = $_ENV["application.cw"]["avalaraDefaultCode"];
	if ($avalara_ship_tax_code === null) $avalara_ship_tax_code = $_ENV["application.cw"]["avalaraDefaultShipCode"];
	if ($avalara_company_code === null) $avalara_company_code = $_ENV["application.cw"]["avalaraCompanyCode"];
	$xmlData = array();
	$cartTaxData = array();
	$cartTaxData["error"] = '';
	$cartTaxData["xml"] = '';
	$cartTaxData["response"] = '';
	$cartTaxData["amounts"] = array();
	$cartTaxData["responseData"] = '';
	$shipVal = "";
	$customerQuery = '';
	$itemTaxCode = '';
	$mailContent = '';
	$temp = '';
	// get customer id and cart id from session if not implicitly defined 
	if ($customer_id == 0 && isset($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] != 0) {
		$customer_id = $_SESSION["cwclient"]["cwCustomerID"];
	}
	if ($cart_id == 0 && isset($_SESSION["cwclient"]["cwCartID"]) && $_SESSION["cwclient"]["cwCartID"] != 0) {
		$cart_id = $_SESSION["cwclient"]["cwCartID"];
	}
	// DEBUG: uncomment to test server availability, should return an AvaTax response of some kind 
	/*
	$ava_curl = curl_init();
	curl_setopt($ava_curl, CURLOPT_URL, "https://development.avalara.net/1.0/address/validate.xml?line1=".str_replace(" ","+",$_ENV["application.cw"]["companyAddress1"])."&postalCode=".$_ENV["application.cw"]["companyZip"]."");
	$ava_headers = array("content-type: text/html", "date: ".gmdate("D, d M Y H:i:s e"), "Authorization: Basic ".base64_encode(trim($avalara_account).":".trim($avalara_license)));
	curl_setopt($ava_curl, CURLOPT_HTTPHEADER, $ava_headers); 
	curl_setopt($ava_curl, CURLOPT_HEADER, 0);
	curl_setopt($ava_curl, CURLOPT_HTTPGET, 1);
	curl_setopt($ava_curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ava_curl, CURLOPT_RETURNTRANSFER, 1);
	$textResponse = curl_exec($ava_curl);
	curl_close($ava_curl);
	// handle result 
	var_dump($textResponse);
	exit;
	*/
	// /END DEBUG 
	// handle errors 
	try {
		// get customer details 
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
		// if customer record exists 
		if ($customerQuery["totalRows"] && isset($customerQuery["customer_ship_zip"][0]) && strlen(trim($customerQuery["customer_ship_zip"][0]))) {
			// set up xml data 
			$xmlData["customerid"] = trim($customer_id);
			// delivery address 
			$xmlData["shipcompany"] = $customerQuery["customer_ship_company"][0];
			$xmlData["shipname"] = $customerQuery["customer_ship_name"][0];
			$xmlData["shipaddress1"] = $customerQuery["customer_ship_address1"][0];
			$xmlData["shipaddress2"] = $customerQuery["customer_ship_address2"][0];
			$xmlData["shipcity"] = $customerQuery["customer_ship_city"][0];
			$xmlData["shipstate"] = $customerQuery["stateprov_name"][0];
			$xmlData["shipcountry"] = $customerQuery["country_code"][0];
			$xmlData["shippostcode"] = $customerQuery["customer_ship_zip"][0];
			// ship from address 
			$xmlData["fromname"] = $_ENV["application.cw"]["companyName"];
			$xmlData["fromaddress1"] = $_ENV["application.cw"]["companyAddress1"];
			$xmlData["fromaddress2"] = $_ENV["application.cw"]["companyAddress2"];
			$xmlData["fromcity"] = $_ENV["application.cw"]["companyCity"];
			$xmlData["fromstate"] = $_ENV["application.cw"]["companyState"];
			$xmlData["fromcountry"] = $_ENV["application.cw"]["companyShipCountry"];
			$xmlData["frompostcode"] = $_ENV["application.cw"]["companyZip"];
			// get cart details 
			$taxCart = CWgetCart($cart_id, null, null, 'productName', null, null, null, null, 'none');
			// if cart has at least one item 
			if (sizeof($taxCart["cartitems"])) {
				// set up xml transaction content 
				$cartTaxData["xml"] = "<GetTaxRequest>
	<CustomerCode>".$customer_id."</CustomerCode>
	<DocDate>".date("Y-m-d")."</DocDate>
	<DocCode>".$taxCart["cartID"]."</DocCode>
	<DocType>".$avalara_transaction_type."</DocType>
	";
				if (strlen(trim($avalara_company_code))) {
					$cartTaxData["xml"] .= "<CompanyCode>".$avalara_company_code."</CompanyCode>
	";
				}
				if (strtolower($avalara_transaction_type) == 'salesinvoice') {
					$cartTaxData["xml"] .= "<Commit>1</Commit>
	";
				}
				$cartTaxData["xml"] .= "<Addresses>
		<Address>
			<AddressCode>1</AddressCode>
			<Line1>";
				if (strlen(trim($xmlData["shipaddress1"]))) {
					$cartTaxData["xml"] .= $xmlData["shipaddress1"];
				} else {
					$cartTaxData["xml"] .= "Unavailable";
				}
				$cartTaxData["xml"] .= "</Line1>";
				if (strlen(trim($xmlData["shipaddress2"]))) {
					$cartTaxData["xml"] .= "
			<Line2>".$xmlData["shipaddress2"]."</Line2>";
				}
				if (strlen(trim($xmlData["shipcity"]))) {
					$cartTaxData["xml"] .= "
			<City>".$xmlData["shipcity"]."</City>";
				}
				if (strlen(trim($xmlData["shipstate"]))) {
					$cartTaxData["xml"] .= "
			<Region>".$xmlData["shipstate"]."</Region>";
				}
				if (strlen(trim($xmlData["shipcountry"]))) {
					$cartTaxData["xml"] .= "
			<Country>".$xmlData["shipcountry"]."</Country>";
				}
				if (strlen(trim($xmlData["shippostcode"]))) {
					$cartTaxData["xml"] .= "
			<PostalCode>".$xmlData["shippostcode"]."</PostalCode>";
				}
				$cartTaxData["xml"] .= "
		</Address>
		<Address>
			<AddressCode>2</AddressCode>
			<Line1>";
				if (strlen(trim($xmlData["fromaddress1"]))) {
					$cartTaxData["xml"] .= $xmlData["fromaddress1"];
				} else {
					$cartTaxData["xml"] .= "Unavailable";
				}
				$cartTaxData["xml"] .= "</Line1>";
				if (strlen(trim($xmlData["fromaddress2"]))) {
					$cartTaxData["xml"] .= "
			<Line2>".$xmlData["fromaddress2"]."</Line2>";
				}
				if (strlen(trim($xmlData["fromcity"]))) {
					$cartTaxData["xml"] .= "
			<City>".$xmlData["fromcity"]."</City>";
				}
				if (strlen(trim($xmlData["fromstate"]))) {
					$cartTaxData["xml"] .= "
			<Region>".$xmlData["fromstate"]."</Region>";
				}
				if (strlen(trim($xmlData["fromcountry"]))) {
					$cartTaxData["xml"] .= "
			<Country>".$xmlData["fromcountry"]."</Country>";
				}
				if (strlen(trim($xmlData["frompostcode"]))) {
					$cartTaxData["xml"] .= "
			<PostalCode>".$xmlData["frompostcode"]."</PostalCode>";
				}
				$cartTaxData["xml"] .= "
		</Address>
	</Addresses>
	<Lines>";
				for ($i=0; $i<sizeof($taxCart["cartitems"]); $i++) {
					$itemTaxCode = CWgetSkuTaxCode($taxCart["cartitems"][$i]["skuID"]);
					$cartTaxData["xml"] .= "
		<Line>
			<LineNo>".($i+1)."</LineNo>
			<DestinationCode>1</DestinationCode>
			<OriginCode>2</OriginCode>
			<ItemCode>".$taxCart["cartitems"][$i]["skuUniqueID"]."</ItemCode>
			<TaxCode>";
					if (strlen(trim($itemTaxCode))) {
						$cartTaxData["xml"] .= trim($itemTaxCode);
					} else {
						$cartTaxData["xml"] .= $avalara_default_tax_code;
					}
					$cartTaxData["xml"] .= "</TaxCode>";
					if (strlen(trim($taxCart["cartitems"][$i]["merchSkuID"]))) {
						$cartTaxData["xml"] .= "
			<Description>".$taxCart["cartitems"][$i]["merchSkuID"]."</Description>";
					}
					$cartTaxData["xml"] .= "
			<Qty>".$taxCart["cartitems"][$i]["quantity"]."</Qty>
			<Amount>".$taxCart["cartitems"][$i]["subTotal"]."</Amount>
		</Line>";
				}
				$shipVal = ((isset($_SESSION["cwclient"]["cwShipTotal"])) ? $_SESSION["cwclient"]["cwShipTotal"] : 0);
				if (isset($taxCart["carttotals"]["shipDiscounts"])) $shipVal -= $taxCart["carttotals"]["shipDiscounts"];
				$shipVal = max(0,$shipVal);
				if ($_ENV["application.cw"]["taxChargeOnShipping"] && $shipVal > 0) {
					$cartTaxData["xml"] .= "
		<Line>
			<LineNo>".(sizeof($taxCart["cartitems"])+1)."</LineNo>
					<DestinationCode>1</DestinationCode>
					<OriginCode>2</OriginCode>
					<ItemCode>ShipTax</ItemCode>
					<TaxCode>".$avalara_ship_tax_code."</TaxCode>
					<Description>Order shipping/delivery</Description>
					<Qty>1</Qty>
					<Amount>".$shipVal."</Amount>
		</Line>";
				}
				$cartTaxData["xml"] .= "
	</Lines>
</GetTaxRequest>";
				// DEBUG: uncomment to show formatted XML request data 
				/*
				echo "
						<p>SENDING TO: ".$avalara_url."tax/get</p>
						<pre>".trim($cartTaxData["xml"])."</pre>
						Raw cart data:
						"; var_dump($taxCart);
				exit;
				*/
				// /END DEBUG 
				// //////////// 
				// //////////// 
				// send request 
				// //////////// 
				// //////////// 
				$ava_curl = curl_init();
				curl_setopt($ava_curl, CURLOPT_URL, $avalara_url."tax/get");
				$ava_headers = array("content-length: ".strlen(trim($cartTaxData["xml"])), "content-type: text/xml", "date: ".gmdate("D, d M Y H:i:s e"), "Authorization: Basic ".base64_encode(trim($avalara_account).":".trim($avalara_license)));
				curl_setopt($ava_curl, CURLOPT_HTTPHEADER, $ava_headers);
				curl_setopt($ava_curl, CURLOPT_HEADER, 0);
				curl_setopt($ava_curl, CURLOPT_POST, 1);
				curl_setopt($ava_curl, CURLOPT_POSTFIELDS, trim($cartTaxData["xml"]));
				curl_setopt($ava_curl, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ava_curl, CURLOPT_RETURNTRANSFER, 1);
				$cartTaxData["response"] = curl_exec($ava_curl);
				curl_close($ava_curl);
				// DEBUG: uncomment to show response from server 
				/*
				echo "<pre>".$cartTaxData["response"]."</pre>";
				exit;
				*/
				// /END DEBUG 
				// //////////// 
				// //////////// 
				// parse response 
				// //////////// 
				// //////////// 
				try {
					// parse response, add to returned structure 
					$cartTaxData["responseData"] = CWgetXMLParamsAvaTax($cartTaxData["response"]);
					// if status is success 
					if (isset($cartTaxData["responseData"]["GETTAXRESULT"]["RESULTCODE"]) && strtolower($cartTaxData["responseData"]["GETTAXRESULT"]["RESULTCODE"]) == "success") {
						// verify order id is correct 
						if (isset($cartTaxData["responseData"]["GETTAXRESULT"]["DOCCODE"]) && strtolower($cartTaxData["responseData"]["GETTAXRESULT"]["DOCCODE"]) == strtolower($taxCart["cartID"])) {
							// ///////////////////////////////// 
							// create structure of cart tax data 
							// ///////////////////////////////// 
							// total cart tax 
							$cartTaxData["amounts"]["totalCartTax"] = $cartTaxData["responseData"]["GETTAXRESULT"]["TOTALTAX"];
							// subtotals: shipping 
							$cartTaxData["amounts"]["totalShipTax"] = 0;
							// subtotals: cart lines 
							$cartTaxData["amounts"]["cartLines"] = array();
							// if at least one line returned 
							if (sizeof($cartTaxData["responseData"]["GETTAXRESULT"]["TAXLINES"]) > 0) {
								// set amounts for each line 
								if (isset($cartTaxData["responseData"]["GETTAXRESULT"]["TAXLINES"]["TAXLINE"])) {
									if ($_ENV["application.cw"]["taxChargeOnShipping"] && $taxCart["carttotals"]["shipSubtotal"] > 0) {
										$cartTaxData["amounts"]["totalShipTax"] = $cartTaxData["responseData"]["GETTAXRESULT"]["TAXLINES"]["TAXLINE"]["TAX"];
									} else {
										$cartTaxData["amounts"]["cartLines"][0] = array();
										$cartTaxData["amounts"]["cartLines"][0]["itemTax"] = $cartTaxData["responseData"]["GETTAXRESULT"]["TAXLINES"]["TAXLINE"]["TAX"];
										$cartTaxData["amounts"]["cartLines"][0]["itemID"] = $taxCart["cartitems"][0]["skuUniqueID"];
										$cartTaxData["amounts"]["cartLines"][0]["itemQty"] = $taxCart["cartitems"][0]["quantity"];
										$cartTaxData["amounts"]["cartLines"][0]["itemSubtotal"] = $taxCart["cartitems"][0]["subTotal"];
									}
								} else {
									foreach ($cartTaxData["responseData"]["GETTAXRESULT"]["TAXLINES"] as $i => $taxLine) {
										if (is_numeric($i)) {
											// get shipping tax if using shipping (since it is always last row, index should remain consistent) 
											if ($_ENV["application.cw"]["taxChargeOnShipping"] && $taxCart["carttotals"]["shipSubtotal"] > 0 && $i == sizeof($cartTaxData["responseData"]["GETTAXRESULT"]["TAXLINES"])-2) {
												$cartTaxData["amounts"]["totalShipTax"] = $taxLine["TAXLINE"]["TAX"];
											} else {
												$cartTaxData["amounts"]["cartLines"][$i] = array();
												$cartTaxData["amounts"]["cartLines"][$i]["itemTax"] = $taxLine["TAXLINE"]["TAX"];
												$cartTaxData["amounts"]["cartLines"][$i]["itemID"] = $taxCart["cartitems"][$i]["skuUniqueID"];
												$cartTaxData["amounts"]["cartLines"][$i]["itemQty"] = $taxCart["cartitems"][$i]["quantity"];
												$cartTaxData["amounts"]["cartLines"][$i]["itemSubtotal"] = $taxCart["cartitems"][$i]["subTotal"];
											}
										}
									}
								}
							}
							// /end if at least one line 
						// if order ID does not match 
						} else {
							$cartTaxData["error"] = 'Response Transaction ID (DocCode) does not match';
						}
						// /end if order id matches 
						// if not a success message 
					} else if (isset($cartTaxData["responseData"]["GETTAXRESULT"]["RESULTCODE"])) {
						$cartTaxData["error"] = $cartTaxData["responseData"]["GETTAXRESULT"]["RESULTCODE"];
						// parse out avalara error details for detailed response/error message 
						if (isset($cartTaxData["responseData"]["GETTAXRESULT"]["MESSAGES"]["MESSAGE"]["SUMMARY"])) {
							$cartTaxData["error"] .= ': '.$cartTaxData["responseData"]["GETTAXRESULT"]["MESSAGES"]["MESSAGE"]["SUMMARY"];
						}
					// if no message at all 
					} else {
						$cartTaxData["error"] = 'Incomplete response: no status returned';
					}
				// handle errors 
				} catch (Exception $e) {
					$cartTaxData["error"] = 'Invalid response: '.$e->getMessage();
				}
				// if cart is empty 
			} else {
				$cartTaxData["error"] = 'No items available for calculation';
			}
			// if no customer found 
		} else {
			$cartTaxData["error"] = 'Destination address unavailable';
		}
		// /end if customer exists 
		// handle errors 
	} catch (Exception $e) {
		echo "<pre>"; var_dump($e); echo "</pre>";
		exit;
		$cartTaxData["error"] = 'Tax retrieval incomplete: '.$e->getMessage();
	}
	// if enabled, send any error message to the site admin 
	if (strlen(trim($cartTaxData["error"])) && isValidEmail($error_email) && $_ENV["application.cw"]["taxSendLookupErrors"]) {
		$mailContent = "Cart ID: ".$cart_id.chr(13).
"Customer ID: ".$customer_id.chr(13).
"
Error: ".$cartTaxData["error"].chr(13);
		if (isset($ava_headers)) {
			$mailContent .= "
HEADERS:".implode("
    ", $ava_headers)."
ACCOUNT: ".$avalara_account."
LICENSE: ".$avalara_license."
URL: ".$avalara_url."
DEFAULT TAX CODE: ".$avalara_default_tax_code."
SHIP TAX CODE: ".$avalara_ship_tax_code."
";
		}
		$mailContent .= "
POSTED CART DATA:".chr(13).
htmlentities($cartTaxData["xml"]).chr(13).
"
AVALARA RESPONSE DATA:".chr(13).
htmlentities($cartTaxData["response"]).chr(13);
		// send email 
		$temp = CWsendMail($mailContent, 'AvaTax Processing Error', $error_email);
		$cartTaxData["error"] .= ' - email notification sent';
	}
	// /end send email 
	return $cartTaxData;
}

// // ---------- // post Avalara order tax // ---------- // 
function CWpostAvalaraTax($order_id,$refund_order=null,$error_email=null,$avalara_account=null,$avalara_license=null,$avalara_url=null,$avalara_default_tax_code=null,$avalara_ship_tax_code=null,$avalara_company_code=null) {
	if ($refund_order === null) $refund_order = false;
	if ($error_email === null) $error_email = $_ENV["application.cw"]["taxErrorEmail"];
	if ($avalara_account === null) $avalara_account = $_ENV["application.cw"]["avalaraID"];
	if ($avalara_license === null) $avalara_license = $_ENV["application.cw"]["avalaraKey"];
	if ($avalara_url === null) $avalara_url = $_ENV["application.cw"]["avalaraUrl"];
	if ($avalara_default_tax_code === null) $avalara_default_tax_code = $_ENV["application.cw"]["avalaraDefaultCode"];
	if ($avalara_ship_tax_code === null) $avalara_ship_tax_code = $_ENV["application.cw"]["avalaraDefaultShipCode"];
	if ($avalara_company_code === null) $avalara_company_code = $_ENV["application.cw"]["avalaraCompanyCode"];
	$xmlData = array();
	$orderTaxData = array();
	$orderTaxData["error"] = '';
	$orderTaxData["xml"] = '';
	$orderTaxData["response"] = '';
	$orderTaxData["amounts"] = array();
	$orderTaxData["responseData"] = '';
	$shipVal = "";
	$orderQuery = '';
	$itemTaxCode = '';
	$mailContent = '';
	$temp = '';
	$loopCt = 0;
	// DEBUG: uncomment to test server availability, should return an AvaTax response of some kind 
	/*
	$ava_curl = curl_init();
	curl_setopt($ava_curl, CURLOPT_URL, "https://development.avalara.net/1.0/address/validate.xml?line1=".str_replace(" ","+",$_ENV["application.cw"]["companyAddress1"])."&postalCode=".$_ENV["application.cw"]["companyZip"]."");
	$ava_headers = array("content-type: text/html", "date: ".gmdate("D, d M Y H:i:s e"), "Authorization: Basic ".base64_encode(trim($avalara_account).":".trim($avalara_license)));
	curl_setopt($ava_curl, CURLOPT_HTTPHEADER, $ava_headers); 
	curl_setopt($ava_curl, CURLOPT_HEADER, 0);
	curl_setopt($ava_curl, CURLOPT_HTTPGET, 1);
	curl_setopt($ava_curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ava_curl, CURLOPT_RETURNTRANSFER, 1);
	$textResponse = curl_exec($ava_curl);
	curl_close($ava_curl);
	// handle result 
	var_dump($textResponse);
	exit;
	*/
	// /END DEBUG 
	// handle errors 
	try {
		// get order details 
		$orderQuery = CWquerySelectOrderDetails($order_id);
		// if order record exists 
		if ($orderQuery["totalRows"]) {
			// set up xml data 
			$xmlData["customerid"] = $orderQuery["customer_id"][0];
			$xmlData["orderid"] = $orderQuery["order_id"][0];
			$xmlData["orderdate"] = $orderQuery["order_date"][0];
			if ($refund_order) {
				$xmlData["orderid"] = $xmlData["orderid"]."-REF";
			}
			// delivery address 
			$xmlData["shipcompany"] = $orderQuery["order_company"][0];
			$xmlData["shipname"] = $orderQuery["customer_first_name"][0]." ".$orderQuery["customer_last_name"][0];
			$xmlData["shipaddress1"] = $orderQuery["order_address1"][0];
			$xmlData["shipaddress2"] = $orderQuery["order_address2"][0];
			$xmlData["shipcity"] = $orderQuery["order_city"][0];
			$xmlData["shipstate"] = $orderQuery["order_state"][0];
			// country must be 2 letter code 
			if (strtolower($orderQuery["order_country"][0]) == 'united states') {
				$xmlData["shipcountry"] = "US";
			} else if (strtolower($orderQuery["order_country"][0]) == 'canada') {
				$xmlData["shipcountry"] = "CA";
			} else {
				$xmlData["shipcountry"] = $orderQuery["order_country"][0];
			}
			$xmlData["shippostcode"] = $orderQuery["order_zip"][0];
			// ship from address 
			$xmlData["fromname"] = $_ENV["application.cw"]["companyName"];
			$xmlData["fromaddress1"] = $_ENV["application.cw"]["companyAddress1"];
			$xmlData["fromaddress2"] = $_ENV["application.cw"]["companyAddress2"];
			$xmlData["fromcity"] = $_ENV["application.cw"]["companyCity"];
			$xmlData["fromstate"] = $_ENV["application.cw"]["companyState"];
			$xmlData["fromcountry"] = $_ENV["application.cw"]["companyShipCountry"];
			$xmlData["frompostcode"] = $_ENV["application.cw"]["companyZip"];
			// set up xml transaction content 
			$orderTaxData["xml"] = "<GetTaxRequest>
	<CustomerCode>".$xmlData["customerid"]."</CustomerCode>
	<DocDate>".date("Y-m-d")."</DocDate>
	<DocCode>".$xmlData["orderid"]."</DocCode>
	<DocType>SalesInvoice</DocType>
	";
				if (strlen(trim($avalara_company_code))) {
					$cartTaxData["xml"] .= "<CompanyCode>".$avalara_company_code."</CompanyCode>
	";
				}
				$orderTaxData["xml"] = "<Commit>1</Commit>
	<Addresses>
		<Address>
			<AddressCode>1</AddressCode>
			<Line1>";
			if (strlen(trim($xmlData["shipaddress1"]))) {
				$orderTaxData["xml"] .= $xmlData["shipaddress1"];
			} else {
				$orderTaxData["xml"] .= "Unavailable";
			}
			$orderTaxData["xml"] .= "</Line1>";
			if (strlen(trim($xmlData["shipaddress2"]))) {
				$orderTaxData["xml"] .= "
			<Line2>".$xmlData["shipaddress2"]."</Line2>";
			}
			if (strlen(trim($xmlData["shipcity"]))) {
				$orderTaxData["xml"] .= "
			<City>".$xmlData["shipcity"]."</City>";
			}
			if (strlen(trim($xmlData["shipstate"]))) {
				$orderTaxData["xml"] .= "
			<Region>".$xmlData["shipstate"]."</Region>";
			}
			if (strlen(trim($xmlData["shipcountry"]))) {
				$orderTaxData["xml"] .= "
			<Country>".$xmlData["shipcountry"]."</Country>";
			}
			if (strlen(trim($xmlData["shippostcode"]))) {
				$orderTaxData["xml"] .= "
			<PostalCode>".$xmlData["shippostcode"]."</PostalCode>";
			}
			$orderTaxData["xml"] .= "
		</Address>
		<Address>
			<AddressCode>2</AddressCode>
			<Line1>";
			if (strlen(trim($xmlData["fromaddress1"]))) {
				$orderTaxData["xml"] .= $xmlData["fromaddress1"];
			} else {
				$orderTaxData["xml"] .= "Unavailable";
			}
			$orderTaxData["xml"] .= "</Line1>";
			if (strlen(trim($xmlData["fromaddress2"]))) {
				$orderTaxData["xml"] .= "
			<Line2>".$xmlData["fromaddress2"]."</Line2>";
			}
			if (strlen(trim($xmlData["fromcity"]))) {
				$orderTaxData["xml"] .= "
			<City>".$xmlData["fromcity"]."</City>";
			}
			if (strlen(trim($xmlData["fromstate"]))) {
				$orderTaxData["xml"] .= "
			<Region>".$xmlData["fromstate"]."</Region>";
			}
			if (strlen(trim($xmlData["fromcountry"]))) {
				$orderTaxData["xml"] .= "
			<Country>".$xmlData["fromcountry"]."</Country>";
			}
			if (strlen(trim($xmlData["frompostcode"]))) {
				$orderTaxData["xml"] .= "
			<PostalCode>".$xmlData["frompostcode"]."</PostalCode>";
			}
			$orderTaxData["xml"] .= "
		</Address>
	</Addresses>
	<Lines>";
			$loopCt = 0;
			for ($i=0; $i<$orderQuery["totalRows"]; $i++) {
				$loopCt++;
				$itemTaxCode = CWgetSkuTaxCode($orderQuery["ordersku_sku"][$i]);
				$orderTaxData["xml"] .= "
		<Line>
			<LineNo>".$loopCt."</LineNo>
			<DestinationCode>1</DestinationCode>
			<OriginCode>2</OriginCode>
			<ItemCode>".$orderQuery["ordersku_unique_id"][$i]."</ItemCode>
			<TaxCode>";
				if (strlen(trim($itemTaxCode))) {
					$orderTaxData["xml"] .= trim($itemTaxCode);
				} else {
					$orderTaxData["xml"] .= $avalara_default_tax_code;
				}
				$orderTaxData["xml"] .= "</TaxCode>
			";
				if (strlen(trim($orderQuery["sku_merchant_sku_id"][$i]))) {
					$orderTaxData["xml"] .= "<Description>".$orderQuery["sku_merchant_sku_id"][$i]."</Description>
			";
				}
				$orderTaxData["xml"] .= "<Qty>".$orderQuery["ordersku_quantity"][$i]."</Qty>
			<Amount>";
				if ($refund_order) {
					$orderTaxData["xml"] .= "-";
				}
				$orderTaxData["xml"] .= $orderQuery["ordersku_sku_total"][$i]."</Amount>
		</Line>";
			}
			if ($_ENV["application.cw"]["taxChargeOnShipping"] && $orderQuery["order_shipping"][0] > 0) {
				$orderTaxData["xml"] .= "
		<Line>
			<LineNo>".($loopCt+1)."</LineNo>
			<DestinationCode>1</DestinationCode>
			<OriginCode>2</OriginCode>
			<ItemCode>ShipTax</ItemCode>
			<TaxCode>".$avalara_ship_tax_code."</TaxCode>
			<Description>Order shipping/delivery</Description>
			<Qty>1</Qty>
			<Amount>";
				if ($refund_order) {
					$orderTaxData["xml"] .= "-";
				}
				$orderTaxData["xml"] .= $orderQuery["order_shipping"][0]."</Amount>
		</Line>";
			}
			$orderTaxData["xml"] .= "
	</Lines>
</GetTaxRequest>";
			// DEBUG: uncomment to show formatted XML request data 
			/*
			echo "
					<p>SENDING TO: ".$avalara_url."tax/get</p>
					<pre>".trim($orderTaxData["xml"])."</pre>
					Raw order data:
					"; var_dump($orderQuery);
			exit;
			*/
			// /END DEBUG 
			// //////////// 
			// //////////// 
			// send request 
			// //////////// 
			// //////////// 
			$ava_curl = curl_init();
			curl_setopt($ava_curl, CURLOPT_URL, $avalara_url."tax/get");
			$ava_headers = array("content-length: ".strlen(trim($orderTaxData["xml"])), "content-type: text/xml", "date: ".gmdate("D, d M Y H:i:s e"), "Authorization: Basic ".base64_encode(trim($avalara_account).":".trim($avalara_license)));
			curl_setopt($ava_curl, CURLOPT_HTTPHEADER, $ava_headers);
			curl_setopt($ava_curl, CURLOPT_HEADER, 0);
			curl_setopt($ava_curl, CURLOPT_POST, 1);
			curl_setopt($ava_curl, CURLOPT_POSTFIELDS, trim($orderTaxData["xml"]));
			curl_setopt($ava_curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ava_curl, CURLOPT_RETURNTRANSFER, 1);
			$orderTaxData["response"] = curl_exec($ava_curl);
			curl_close($ava_curl);
			// DEBUG: uncomment to show response from server 
			/*
			echo "<pre>".$orderTaxData["response"]."</pre>";
			exit;
			*/
			// /END DEBUG 
			// //////////// 
			// //////////// 
			// parse response 
			// //////////// 
			// //////////// 
			try {
				// parse response, add to returned structure 
				$orderTaxData["responseData"] = CWgetXMLParamsAvaTax($orderTaxData["response"]);
				// if status is success 
				if (isset($orderTaxData["responseData"]["GETTAXRESULT"]["RESULTCODE"]) && strtolower($orderTaxData["responseData"]["GETTAXRESULT"]["RESULTCODE"]) == "success") {
					// if order ID does not match 
					// verify order id is correct 
					if (isset($orderTaxData["responseData"]["GETTAXRESULT"]["DOCCODE"]) && strtolower($orderTaxData["responseData"]["GETTAXRESULT"]["DOCCODE"]) == strtolower($xmlData["orderid"])) {
						$orderTaxData["error"] = 'Response Transaction ID (DocCode) does not match';
					}
					// /end if order id matches 
				// if not a success message 
				} else if (isset($orderTaxData["responseData"]["GETTAXRESULT"]["RESULTCODE"])) {
					$orderTaxData["error"] = $orderTaxData["responseData"]["GETTAXRESULT"]["RESULTCODE"];
					// parse out avalara error details for detailed response/error message 
					if (isset($orderTaxData["responseData"]["GETTAXRESULT"]["MESSAGES"]["MESSAGE"]["SUMMARY"])) {
						$orderTaxData["error"] .= ': '.$orderTaxData["responseData"]["GETTAXRESULT"]["MESSAGES"]["MESSAGE"]["SUMMARY"];
					}
				// if no message at all 
				} else {
					$orderTaxData["error"] = 'Incomplete response: no status returned';
				}
			// handle errors 
			} catch (Exception $e) {
				$orderTaxData["error"] = 'Invalid response: '.$e->getMessage();
			}
		// if no order found 
		} else {
			$orderTaxData["error"] = 'Order details unavailable';
		}
		// /end if customer exists 
	// handle errors 
	} catch (Exception $e) {
		$orderTaxData["error"] = 'Tax retrieval incomplete: '.$e->getMessage();
	}
	// if enabled, send any error message to the site admin 
	if (strlen(trim($orderTaxData["error"])) && isValidEmail($error_email)) {
		$mailContent = "Order ID: ".$order_id.chr(13).
"
Error: ".$orderTaxData["error"].chr(13).
"
AVALARA RESPONSE DATA:".chr(13).
htmlentities($orderTaxData["response"]).chr(13);
		// send email 
		$temp = CWsendMail($mailContent, 'AvaTax Processing Error', $error_email);
		$orderTaxData["error"] .= ' - email notification sent';
	}
	// /end send email 
	return $orderTaxData;
}


// // ---------- // CWgetSkuTaxCode // ---------- // 
function CWgetSkuTaxCode($sku_id) {
	$returnStr = '';
	$rs = CWqueryGetRS("SELECT tax_group_code
							FROM cw_tax_groups, cw_products, cw_skus
							WHERE cw_skus.sku_id = ".CWqueryParam($sku_id)."
							AND cw_products.product_id = cw_skus.sku_product_id
							AND cw_tax_groups.tax_group_id = cw_products.product_tax_group_id
							AND NOT cw_tax_groups.tax_group_archive = 1");
	if ($rs["totalRows"]) {
		$returnStr = $rs["tax_group_code"][0];
	}
	return $returnStr;
}


// XML Parsing functions
if (!function_exists("CWgetXMLResponseArrayAvaTax")) {
function CWgetXMLParamsAvaTax($textResponse) {
	$xmlVals = array();
	$xmlIndex = array();
	$xmlParse = xml_parser_create();
	xml_parse_into_struct($xmlParse, $textResponse, $xmlVals, $xmlIndex);
	xml_parser_free($xmlParse);
	$xmlParams = array();
	$xmlLevel = array();
	$xmlExtraElements = array();
	foreach ($xmlVals as $xmlElement) {
		switch ($xmlElement["type"]) {
			case "open":
				//open element
				if (array_key_exists("attributes", $xmlElement)) {
					//if attributes exist, add the level to a list
					$xmlLevel[$xmlElement["level"]] = $xmlElements["attributes"][0];
				} else {
					//set the level to the tag value
					$xmlLevel[$xmlElement["level"]] = $xmlElement["tag"];
				}
				break;
			case "complete":
				$xmlParams = CWgetXMLResponseArrayAvaTax($xmlParams, 1, $xmlElement, $xmlLevel);
				break;
			case "close":
				$xmlParams = CWcloseXMLTagAvaTax($xmlParams, 1, $xmlElement, $xmlLevel);
				break;
		}
	}
	return $xmlParams;
}

function CWcloseXMLTagAvaTax($fieldRef, $levelStart, $xmlElement, $xmlLevel) {
	$subElm = $xmlLevel[$levelStart];
	if ($levelStart < $xmlElement["level"]) {
		if (isset($fieldRef[0])) {
			$fieldRef[sizeof($fieldRef)-1][$subElm] = CWcloseXMLTagAvaTax($fieldRef[sizeof($fieldRef)-1][$subElm], $levelStart+1, $xmlElement, $xmlLevel);
		} else if (isset($fieldRef[$subElm])) {
			$fieldRef[$subElm] = CWcloseXMLTagAvaTax($fieldRef[$subElm], $levelStart+1, $xmlElement, $xmlLevel);
		}
	} else {
		if (isset($fieldRef[0])) {
			$fieldRef[sizeof($fieldRef)-1][$subElm]["_CLOSED"] = true;
		} else {
			$fieldRef[$subElm]["_CLOSED"] = true;
		}
	}
	return $fieldRef;
}

function CWgetXMLResponseArrayAvaTax($fieldRef, $levelStart, $xmlElement, $xmlLevel) {
	if ($levelStart < $xmlElement["level"]) {
		$isMulti = false;
		$newField = null;
		if ($levelStart < $xmlElement["level"]-1) {
			if (!isset($fieldRef[$xmlLevel[$levelStart]][0]) && isset($fieldRef[$xmlLevel[$levelStart]][$xmlLevel[$levelStart+1]]["_CLOSED"])) {
				//the field below has already been closed, move to array syntax
				$fieldRef[$xmlLevel[$levelStart]] = array(array($xmlLevel[$levelStart+1] => $fieldRef[$xmlLevel[$levelStart]][$xmlLevel[$levelStart+1]]));
				unset($fieldRef[$xmlLevel[$levelStart]][$xmlLevel[$levelStart+1]]);
				$isMulti = true;
				$newField = $fieldRef[$xmlLevel[$levelStart]][0];
			} else if (isset($fieldRef[$xmlLevel[$levelStart]][0])) {
				if (isset($fieldRef[$xmlLevel[$levelStart]][sizeof($fieldRef[$xmlLevel[$levelStart]])-1][$xmlLevel[$levelStart+1]]["_CLOSED"])) {
					//the last field is closed add an element to the array
					$fieldRef[$xmlLevel[$levelStart]][] = array();
				}
				$isMulti = true;
				$newField = $fieldRef[$xmlLevel[$levelStart]][sizeof($fieldRef[$xmlLevel[$levelStart]])-1];
			}
		}
		if ($newField === null) {
			if (!isset($fieldRef[$xmlLevel[$levelStart]])) {
				$fieldRef[$xmlLevel[$levelStart]] = array();
			}
			$newField = $fieldRef[$xmlLevel[$levelStart]];
		}
		$newField = CWgetXMLResponseArrayAvaTax($newField, $levelStart+1, $xmlElement, $xmlLevel);
		if ($isMulti) {
			$fieldRef[$xmlLevel[$levelStart]][sizeof($fieldRef[$xmlLevel[$levelStart]])-1] = $newField;
		} else {
			$fieldRef[$xmlLevel[$levelStart]] = $newField;
		}
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
?>