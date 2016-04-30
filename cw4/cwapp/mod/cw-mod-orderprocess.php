<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-orderprocess.php
File Date: 2012-02-01
Description:
Processes Order and Manages Interaction with various payment methods
==========================================================
*/
// customer cart 
if (!isset($_ENV["request.trans"])) $_ENV["request.trans"] =  array();
if (!isset($module_settings["cart"])) $module_settings["cart"] = array();
if (!isset($module_settings["cart"]["carttotals"])) $module_settings["cart"]["carttotals"] = array();
if (!isset($module_settings["cart"]["carttotals"]["total"])) $module_settings["cart"]["carttotals"]["total"] = 0;
if (!isset($module_settings["cart"]["carttotals"]["shipping"])) $module_settings["cart"]["carttotals"]["shipping"] = 0;
if (!isset($module_settings["cart"]["carttotals"]["shippingTax"])) $module_settings["cart"]["carttotals"]["shippingTax"] = 0;
if (!isset($module_settings["cart"]["carttotals"]["discountIDs"])) $module_settings["cart"]["carttotals"]["discountIDs"] = 0;
if (!isset($module_settings["cart"]["carttotals"]["cartDiscounts"])) $module_settings["cart"]["carttotals"]["cartDiscounts"] = 0;
if (!isset($module_settings["cart"]["carttotals"]["shipDiscounts"])) $module_settings["cart"]["carttotals"]["shipDiscounts"] = 0;
// submission data from form 
if (!isset($module_settings["form_data"])) $module_settings["form_data"] = array();
// defaults for form values 
if (!isset($module_settings["form_data"]["order_message"])) $module_settings["form_data"]["order_message"] = "";
if (!isset($module_settings["form_data"]["customer_submit_conf"])) $module_settings["form_data"]["customer_submit_conf"] = 0;
if (!isset($module_settings["form_data"]["customer_cardname"])) $module_settings["form_data"]["customer_cardname"] = "";
if (!isset($module_settings["form_data"]["customer_cardnumber"])) $module_settings["form_data"]["customer_cardnumber"] = "";
if (!isset($module_settings["form_data"]["customer_cardtype"])) $module_settings["form_data"]["customer_cardtype"] = "";
if (!isset($module_settings["form_data"]["customer_cardexpm"])) $module_settings["form_data"]["customer_cardexpm"] = "";
if (!isset($module_settings["form_data"]["customer_cardexpy"])) $module_settings["form_data"]["customer_cardexpy"] = "";
if (!isset($module_settings["form_data"]["customer_cardccv"])) $module_settings["form_data"]["customer_cardccv"] = "";
//if (!isset($module_settings["form_data"]["fieldnames"])) $module_settings["form_data"]["fieldnames"] = "";
// defaults for session values 
if (!isset($_SESSION["cw"]["authPref"])) $_SESSION["cw"]["authPref"] = "";
if (!isset($_SESSION["cw"]["authPrefName"])) $_SESSION["cw"]["authPrefName"] = "";
if (!isset($_SESSION["cw"]["authType"])) $_SESSION["cw"]["authType"] = "none";
if (!isset($_SESSION["cw"]["confirmShipID"])) $_SESSION["cw"]["confirmShipID"] = 0;
// defaults for client scope values 
if (!isset($_SESSION["cwclient"]["cwCustomerID"])) $_SESSION["cwclient"]["cwCustomerID"] = 0;
if (!isset($_SESSION["cwclient"]["cwCustomerCheckout"])) $_SESSION["cwclient"]["cwCustomerCheckout"] = "account";
// default list of error fields 
$_ENV["request.trans"]["formErrors"] = array();
// default confirmation message 
$_ENV["request.trans"]["confirmMessage"] = '';
// default error message 
$_ENV["request.trans"]["errorMessage"] = '';
$myDir = getcwd();
chdir(dirname(__FILE__));
// global functions 
require_once("../inc/cw-inc-functions.php");
// clean up form and url variables 
require_once("../inc/cw-inc-sanitize.php");
chdir($myDir);
// VALIDATE DATA PASSED IN 
// VERIFY CUSTOMER ID IS VALUD, AND CUSTOMER EXISTS 
if (!isset($_SESSION["cwclient"]["cwCustomerID"]) || !strlen(trim($_SESSION["cwclient"]["cwCustomerID"])) || trim($_SESSION["cwclient"]["cwCustomerID"]) == "0") {
	if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
	$_ENV["request.trans"]["errorMessage"] .= 'Invalid Customer ID - Please Log In to continue';
	// VERIFY CUSTOMER ID IS SAME AS PASSED VIA CART TRANSACTION 
} else if ($module_settings["form_data"]["customer_submit_conf"] != $_SESSION["cwclient"]["cwCustomerID"]) {
	if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
	$_ENV["request.trans"]["errorMessage"] .= 'Invalid Customer ID - Please verify account details';
	// VERIFY TRANSACTION ELEMENTS - cart, customer, order total 
} else {
	// QUERY: get customer details
	$CWcustomer = CWgetCustomer(trim($_SESSION["cwclient"]["cwCustomerID"]));
	// verify customer ID available in database 
	if(!(is_array($CWcustomer) && isset($CWcustomer["customerid"]) && strlen(trim($CWcustomer["customerid"])))) {
		if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
		$_ENV["request.trans"]["errorMessage"] .= 'Invalid Customer ID - Please Log In to continue';
	}
	// verify cart is a valid structure, with products 
	if (!is_array($module_settings["cart"]) || !isset($module_settings["cart"]["cartitems"])) {
		if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
		$_ENV["request.trans"]["errorMessage"] .= 'Invalid Cart';
	} else if (!(count($module_settings["cart"]["cartitems"]) > 0)) {
		if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
		$_ENV["request.trans"]["errorMessage"] .= 'Cart is Empty';
	}
	// verify order total is available, and numeric (0 is allowed) 
	if (!(isset($module_settings["cart"]["carttotals"]["total"]) && is_numeric($module_settings["cart"]["carttotals"]["total"]))) {
		if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
		$_ENV["request.trans"]["errorMessage"] .= 'Invalid Order Total';
	}
	// verify data (form values collection) is a valid structure 
	if (!(is_array($module_settings["form_data"]) && sizeof($module_settings["form_data"]))) {
		if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
		$_ENV["request.trans"]["errorMessage"] .= 'Invalid Form Submission';
	}
}
// TRANSACTION VARIABLE DEFAULTS 
// order id: actual id generated at insert to db 
if (!isset($_ENV["request.trans"]["orderID"])) { $_ENV["request.trans"]["orderID"] = 0; }
// payment id: database id of payment transaction, generated on success 
if (!isset($_ENV["request.trans"]["paymentTransID"])) { $_ENV["request.trans"]["paymentTransID"] = 0; }
// payment method, captured from session scope (set on payment method selection) 
if (!isset($_ENV["request.trans"]["paymentMethod"])) { $_ENV["request.trans"]["paymentMethod"] = $_SESSION["cw"]["authPrefName"]; }
// payment method id, captured from session 
if (!isset($_ENV["request.trans"]["paymentAuthID"])) { $_ENV["request.trans"]["paymentAuthID"] = $_SESSION["cw"]["authPref"]; }
// payment type, captured from session 
if (!isset($_ENV["request.trans"]["paymentType"])) { $_ENV["request.trans"]["paymentType"] = $_SESSION["cw"]["authType"]; }
// payment amount (default is order total) 
if (!isset($_ENV["request.trans"]["paymentAmount"])) { $_ENV["request.trans"]["paymentAmount"] = $module_settings["cart"]["carttotals"]["total"]; }
// payment status (none|approved|declined) 
if (!isset($_ENV["request.trans"]["paymentStatus"])) { $_ENV["request.trans"]["paymentStatus"] = "none"; }
// transaction id, returned from processor 
if (!isset($_ENV["request.trans"]["paymentTransID"])) { $_ENV["request.trans"]["paymentTransID"] = ""; }
// transaction response message or code (if applicable) from processor 
if (!isset($_ENV["request.trans"]["paymentTransResponse"])) { $_ENV["request.trans"]["paymentTransResponse"] = ""; }
// order approved for insertion (set to true on validation)  
if (!isset($_ENV["request.trans"]["orderApproved"])) { $_ENV["request.trans"]["orderApproved"] = false; }
// order inserted to database (set to true on insert)  
if (!isset($_ENV["request.trans"]["orderInserted"])) { $_ENV["request.trans"]["orderInserted"] = false; }
// order total 
if (!isset($_ENV["request.trans"]["orderTotal"])) { $_ENV["request.trans"]["orderTotal"] = $module_settings["cart"]["carttotals"]["total"]; }
// order balance due 
if (!isset($_ENV["request.trans"]["orderBalance"])) { $_ENV["request.trans"]["orderBalance"] = $module_settings["cart"]["carttotals"]["total"]; }
// data passed to payment include 
$_ENV["request.trans"]["data"] = array();
// defaults for payment file info 
if ($_ENV["request.trans"]["paymentAuthID"] > 0) {
	$transAuthID = $_ENV["request.trans"]["paymentAuthID"]-1;
	if (!isset($_ENV["application.cw"]["authMethodData"][$transAuthID]["methodFileName"])) $_ENV["application.cw"]["authMethodData"][$transAuthID]["methodFileName"] = "";
	if (!isset($_ENV["application.cw"]["authMethodData"][$transAuthID]["methodName"])) $_ENV["application.cw"]["authMethodData"][$transAuthID]["methodName"] = "";
	if (!isset($_ENV["application.cw"]["authMethodData"][$transAuthID]["methodConfirmMessage"])) $_ENV["application.cw"]["authMethodData"][$transAuthID]["methodConfirmMessage"] = "";
}
// IF ATTRIBUTES ARE VALID 
// if no error has been generated so far, run processing 
if (!strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
	// set form data into request variable ---]
	$_REQUEST["data"] = $module_settings["form_data"];
	// GATEWAY VALIDATION 
	// if using 'gateway', validate all required form fields 
	if ($_SESSION["cw"]["authType"] == 'gateway') {
		// delete form data scope 
		$module_settings["form_data"] = '';
		// CARD HOLDER NAME : required 
		if (!strlen(trim($_REQUEST["data"]["customer_cardname"]))) {
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Card Holder Name must be provided';
			$_ENV["request.trans"]["formErrors"] = array_push($_ENV["request.trans"]["formErrors"],'customer_cardname');
		}
		// CARD TYPE : required, must be valid card type 
		// QUERY: match submitted value against active card types 
		$creditCardsQuery = CWquerySelectCreditCards($_REQUEST["data"]["customer_cardtype"]);
		if (!(strlen(trim($_REQUEST["data"]["customer_cardtype"])) && $creditCardsQuery['totalRows'] == 1)) {
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Card Type must be selected';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardtype';
		}
		// CARD NUMBER : required, numeric only, varying lengths 
		if (!strlen(trim($_REQUEST["data"]["customer_cardnumber"])) && is_numeric($_REQUEST["data"]["customer_cardnumber"])) {
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Credit Card Number invalid or missing';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardnumber';
		} else if ($_REQUEST["data"]["customer_cardtype"] == 'amex' && strlen(trim($_REQUEST["data"]["customer_cardnumber"])) != 15) {
			// valid amex length 
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Invalid Amex Number';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardnumber';
		} else if ($_REQUEST["data"]["customer_cardtype"] == 'visa' && !(strlen(trim($_REQUEST["data"]["customer_cardnumber"])) == 13 || strlen(trim($_REQUEST["data"]["customer_cardnumber"])) == 16)) {
			// valid visa, other length  
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Invalid Visa Number';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardnumber';
		} else if ($_REQUEST["data"]["customer_cardtype"] != 'visa' && $_REQUEST["data"]["customer_cardtype"] != 'amex' && strlen(trim($_REQUEST["data"]["customer_cardnumber"])) != 16) {
			// all others 
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Invalid Card Number';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardnumber';
		}
		// EXPIRATION DATE 
		if (!(strlen(trim($_REQUEST["data"]["customer_cardexpm"])) == 2 && is_numeric(trim($_REQUEST["data"]["customer_cardexpm"])) && $_REQUEST["data"]["customer_cardexpm"] > 0 && $_REQUEST["data"]["customer_cardexpm"] < 13)) {
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Invalid Expiration Date (month)';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardexpm';
		}
		if(!(strlen(trim($_REQUEST["data"]["customer_cardexpy"])) == 4 && is_numeric(trim($_REQUEST["data"]["customer_cardexpy"])))) {
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Invalid Expiration Date (year)';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardexpy';
		}
		// compare expiration date to current month/year 
		if (is_numeric($_REQUEST["data"]["customer_cardexpy"]) && is_numeric($_REQUEST["data"]["customer_cardexpm"])) {
			$cardMYTime = strtotime(trim($_REQUEST["data"]["customer_cardexpy"]).'-'.trim($_REQUEST["data"]["customer_cardexpm"]).'-'.'01');
			$cardMY = date("Y-m-d", $cardMYTime);
		} else {
			$cardMYTime = 0;
			$cardMY = "00";
		}
		$minMYTime = strtotime("-1Month");
		$minMY = date("Y-m-d", $minMYTime);
		if (!($cardMYTime > $minMYTime)) {
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Card Expiration Date has passed';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardexpm';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardexpy';
		}
		// CCV 
		if(!(strlen(trim($_REQUEST["data"]["customer_cardccv"])) && is_numeric(trim($_REQUEST["data"]["customer_cardccv"])))) {
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Credit Card CCV code invalid or missing';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardccv';
		} else if ($_REQUEST["data"]["customer_cardtype"] == 'amex' && strlen(trim($_REQUEST["data"]["customer_cardccv"])) != 4) {
				// valid amex length 
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Invalid Amex CCV';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardccv';
		} else if ($_REQUEST["data"]["customer_cardtype"] != 'amex' && strlen(trim($_REQUEST["data"]["customer_cardccv"])) != 3) {
			// valid visa, other length 
			if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
			$_ENV["request.trans"]["errorMessage"] .= 'Invalid CCV code';
			$_ENV["request.trans"]["formErrors"][] = 'customer_cardccv';
		}
	}
	// /END GATEWAY VALIDATION 
	// handle customer comments 
	try {
		if (isset($_REQUEST["data"]["order_message"])) {
			// if customer comments have changed 
			if (isset($_SESSION["cw"]["order_message"]) && trim($_REQUEST["data"]["order_message"]) != trim($_SESSION["cw"]["order_message"])) {
				// QUERY: update order with new comments 
				$updateOrder = CWqueryUpdateOrder($_SESSION["cwclient"]["cwCompleteOrderID"], null, null, null, null, null, $_REQUEST["data"]["order_message"]);
			}
			// set message into session scope 
			$_SESSION["cw"]["order_message"] = trim($_REQUEST["data"]["order_message"]);
		}
	} catch (Exception $e) {
		$_SESSION["cw"]["order_message"] = '';
	}
	// IF NO ERRORS FROM VALIDATION 
	// if no validation error and no form errors 
	if (!strlen(trim($_ENV["request.trans"]["errorMessage"])) && !count($_ENV["request.trans"]["formErrors"])) {
		// MARK THE ORDER AS APPROVED 
		// status 1: order placed, no payment 
		$_ENV["request.trans"]["orderStatus"] = 1;
		// result: used for in-page procesing:
		//value 'approved' allows order to be saved below 
		$_ENV["request.trans"]["orderApproved"] = true;
		// CHECK FOR DUPLICATE SUBMISSION 
		// if a submitted order id is not in the user's session (avoid duplicates on retry of payment, or partial payment) 
		if (!(isset($_SESSION["cwclient"]["cwCompleteOrderID"]) && CWorderStatus($_SESSION["cwclient"]["cwCompleteOrderID"]) > 0)) {
			// create new order ID 
			$_ENV["request.trans"]["orderID"] = date("ymdHi", CWtime()).'-'.substr(str_replace('-','',$_SESSION["cwclient"]["cwCustomerID"]),0,4);
			// SAVE ORDER 
			if ($_ENV["request.trans"]["orderApproved"]) {
				// QUERY: INSERT ORDER to database, returns order id if successful
				//(attributes: order ID, order status code, transaction ID, cart structure,
				//customer data structure, shipping method id, order comments)
				try {
					$insertedOrder = CWsaveOrder(
										$_ENV["request.trans"]["orderID"],
										$_ENV["request.trans"]["orderStatus"],
										$module_settings["cart"],
										$CWcustomer,
										$_SESSION["cw"]["confirmShipID"],
										$_REQUEST["data"]["order_message"],
										$_SESSION["cwclient"]["cwCustomerCheckout"]);
					// if no errors from order insertion 
					if (substr($insertedOrder,0,2) != '0-') {
						// SEND EMAIL TO CUSTOMER 
						// build the order details content 
						$mailBody = CWtextOrderDetails($_ENV["request.trans"]["orderID"]);
						if ($_ENV["application.cw"]["mailSendOrderCustomer"]) {
							$mailContent = $_ENV["application.cw"]["mailDefaultOrderReceivedIntro"].chr(10).chr(13).$mailBody.chr(10).chr(13).$_ENV["application.cw"]["mailDefaultOrderReceivedEnd"];
							// send the content to the customer 
							$confirmationResponse = CWsendMail($mailContent, 'Order Confirmation',$CWcustomer["email"]);
						}
						// SEND EMAIL TO MERCHANT 
						if ($_ENV["application.cw"]["mailSendOrderMerchant"]) {
							$merchantMailContent = 'An order has been placed at '.$_ENV["application.cw"]["companyName"].':'.chr(10).chr(13).
$mailBody.chr(10).chr(13).
'Log in to manage this order: '.CWtrailingChar($_ENV["application.cw"]["appSiteUrlHttp"]).CWleadingChar($_ENV["application.cw"]["appCWAdminDir"], "remove");
							// send to merchant 
							$confirmationResponse = CWsendMail($merchantMailContent, 'Order Notification: '.$_ENV["request.trans"]["orderID"], $_ENV["application.cw"]["companyEmail"]);
						}
						// /end send email 
					// if insertion returns an error 
					} else {
						if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
						$_ENV["request.trans"]["errorMessage"] .= 'Unable to process order';
					}
				} catch (Exception $e) {
					if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
					$_ENV["request.trans"]["errorMessage"] .= 'Unable to process order: '.$e->getMessage().'';
				}
				// if returned string matches order ID, insertion was successful 
				if ($insertedOrder == $_ENV["request.trans"]["orderID"]) {
					$_ENV["request.trans"]["orderInserted"] = true;
					// store order id in session 
					$_SESSION["cwclient"]["cwCompleteOrderID"] = $_ENV["request.trans"]["orderID"];
					// message set for user 
					$_ENV["request.trans"]["confirmMessage"][] = 'Order Approved';
					// if an error is returned by order insertion 
				} else if (substr($insertedOrder, 0, 2) == '0-') {
					if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
					$_ENV["request.trans"]["errorMessage"] .= substr($insertedOrder, 2);
				} else {
					if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
					$_ENV["request.trans"]["errorMessage"] .= 'Unable to process order';
				}
				// /end if returned string ok 
			}
			// /end if order approved 
		// if order already exists in session 
		} else {
			// pass order id values through to request scope 
			$_ENV["request.trans"]["orderID"] = $_SESSION["cwclient"]["cwCompleteOrderID"];
			$_ENV["request.trans"]["orderInserted"] = true;
		}
		// /end if order already exists 
		// verify payment: get transactions related to this order 
		$orderPayments = round(CWorderPaymentTotal($_ENV["request.trans"]["orderID"])*100)/100;
		// set balance due 
		$_ENV["request.trans"]["orderBalance"] = $_ENV["request.trans"]["orderTotal"] - $orderPayments;
		// if balance due is 0, skip payment 
		if($_ENV["request.trans"]["orderBalance"] == 0) {
			$_SESSION["cw"]["authType"] = 'none';
		}
	}
	// /end if no validation or form errors 
}
// /end if no data error 
// PROCESS PAYMENT 
// PROCESS PAYMENT 
// PROCESS PAYMENT 
// if order was inserted, and we still have no errors (and payment method exists) 
if ($_ENV["request.trans"]["orderInserted"] && !strlen(trim($_ENV["request.trans"]["errorMessage"])) && $_ENV["request.trans"]["paymentAuthID"] > 0) {
	// verify amount due: get transactions related to this order 
	$orderPayments = CWorderPaymentTotal($_ENV["request.trans"]["orderID"]);
	// if some payments have been made 
	if ($orderPayments > 0) {
		// set balance due 
		$_ENV["request.trans"]["orderBalance"] = $_ENV["request.trans"]["orderTotal"] - $orderPayments;
		// if balance due is less than submitted amount, pay lower amount 
		if($_ENV["request.trans"]["orderBalance"] < $_ENV["request.trans"]["paymentAmount"] && $_ENV["request.trans"]["orderBalance"] > 0) {
			$_ENV["request.trans"]["paymentAmount"] = $_ENV["request.trans"]["orderBalance"];
		}
	}
	// COLLECT FORM DATA INTO STRUCT 
	// order values 
	if (!isset($_ENV["request.trans"]["data"])) $_ENV["request.trans"]["data"] = array();
	$_ENV["request.trans"]["data"]["orderID"] = $_ENV["request.trans"]["orderID"];
	$_ENV["request.trans"]["data"]["paymentAmount"] = number_format($_ENV["request.trans"]["paymentAmount"],2,".","");
	// customer values 
	$_ENV["request.trans"]["data"]["customerID"] = $_SESSION["cwclient"]["cwCustomerID"];
	$_ENV["request.trans"]["data"]["customerNameFirst"] = $CWcustomer['firstname'];
	$_ENV["request.trans"]["data"]["customerNameLast"] = $CWcustomer['lastname'];
	$_ENV["request.trans"]["data"]["customerCompany"] = $CWcustomer['company'];
	$_ENV["request.trans"]["data"]["customerPhone"] = $CWcustomer['phone'];
	$_ENV["request.trans"]["data"]["customerEmail"] = $CWcustomer['email'];
	$_ENV["request.trans"]["data"]["customerAddress1"] = $CWcustomer['address1'];
	$_ENV["request.trans"]["data"]["customerAddress2"] = $CWcustomer['address2'];
	$_ENV["request.trans"]["data"]["customerCity"] = $CWcustomer['city'];
	$_ENV["request.trans"]["data"]["customerState"] = $CWcustomer['stateprovname'];
	$_ENV["request.trans"]["data"]["customerZip"] = $CWcustomer['zip'];
	$_ENV["request.trans"]["data"]["customerCountry"] = $CWcustomer['countrycode'];
	// shipping values 
	$_ENV["request.trans"]["data"]["customerShipName"] = $CWcustomer['shipname'];
	$_ENV["request.trans"]["data"]["customerShipCompany"] = $CWcustomer['shipcompany'];
	$_ENV["request.trans"]["data"]["customerShipPhone"] = $CWcustomer['phone'];
	$_ENV["request.trans"]["data"]["customerShipEmail"] = $CWcustomer['email'];
	$_ENV["request.trans"]["data"]["customerShipAddress1"] = $CWcustomer['shipaddress1'];
	$_ENV["request.trans"]["data"]["customerShipAddress2"] = $CWcustomer['shipaddress2'];
	$_ENV["request.trans"]["data"]["customerShipCity"] = $CWcustomer['shipcity'];
	$_ENV["request.trans"]["data"]["customerShipState"] = $CWcustomer['shipstateprovname'];
	$_ENV["request.trans"]["data"]["customerShipCountry"] = $CWcustomer['shipcountrycode'];
	$_ENV["request.trans"]["data"]["customerShipZip"] = $CWcustomer['shipzip'];
	// payment method - get info from stored application structure 
	$_ENV["request.trans"]["authFileName"] = $_ENV["application.cw"]["authMethodData"][$_ENV["request.trans"]["paymentAuthID"]-1]["methodFileName"];
	// verify auth file exists, and is same as expected in user's session 
	$authDirectory = preg_replace('/\/+$/', "/", preg_replace('/\/+$/', "", $_ENV["application.cw"]["siteRoot"]).'/'.$_ENV["application.cw"]["appCWContentDir"]).'cwapp/auth';
	if (substr($authDirectory, 0, 1) == "/") $authDirectory = substr($authDirectory, 1);
	$authFilePath = expandPath($authDirectory.'/'.$_ENV["request.trans"]["authFileName"]);
        if (!file_exists($authFilePath)) {
            $authFilePath = '../'.$_ENV["application.cw"]["appCWContentDir"].'cwapp/auth/'.$_ENV["request.trans"]["authFileName"];
        }
	if (!(file_exists($authFilePath) && $_ENV["application.cw"]["authMethodData"][$_ENV["request.trans"]["paymentAuthID"]-1]["methodName"] == $_ENV["request.trans"]["paymentMethod"])) {
		$_SESSION["cw"]["authType"] = 'none';
		if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
		$_ENV["request.trans"]["errorMessage"] .= 'Payment Connection Unavailable';
	}
	// credit card values 
	if ($_SESSION["cw"]["authType"] == 'gateway') {
		$_ENV["request.trans"]["data"]["cardname"] = $_REQUEST["data"]["customer_cardname"];
		$_ENV["request.trans"]["data"]["cardnumber"] = $_REQUEST["data"]["customer_cardnumber"];
		$_ENV["request.trans"]["data"]["cardtype"] = $_REQUEST["data"]["customer_cardtype"];
		$_ENV["request.trans"]["data"]["cardexpm"] = $_REQUEST["data"]["customer_cardexpm"];
		$_ENV["request.trans"]["data"]["cardexpy"] = $_REQUEST["data"]["customer_cardexpy"];
		$_ENV["request.trans"]["data"]["cardccv"] = $_REQUEST["data"]["customer_cardccv"];
	}
	// credit card values 
	// IF NO ERRORS to this point, run processing 
	if (!strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
		// PROCESSOR  (e.g. PayPal - offsite transactions) 
		if ($_SESSION["cw"]["authType"] == 'processor') {
			// set variable used on containing page (checkout final step) 
			$_ENV["request.cwpage"]["postToProcessor"] = true;
			// GATEWAY (e.g. Authorize.net - in-page credit card transactions) 
			// ACCOUNT (also handle in-store account options this way) 
		} else if ($_SESSION["cw"]["authType"] == 'gateway' || $_SESSION["cw"]["authType"] == 'account') {
			// invoke payment file, passing in payment info structure 
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			$auth_settings = array(
				"auth_mode" => "process",
				"trans_data" => $_ENV["request.trans"]["data"]);
			include("../auth/".$_ENV["request.trans"]["authFileName"]);
			unset($auth_settings);
			chdir($myDir);
			// IF GATEWAY TRANSACTION IS OK 
			// if transaction ID exists, and payment was successful, redirect to confirmation page (if not already redirected by payment file) 
			if ($_ENV["request.trans"]["paymentStatus"] == 'approved' && strlen(trim($_ENV["request.trans"]["paymentTransID"]))) {
				// capture any errors related to insertion 
				try {
					// QUERY: insert payment to database,
					//returns payment id if successful, or 0-based message if not
					$insertedPayment = CWsavePayment(
											$_ENV["request.trans"]["orderID"],
											$_ENV["request.trans"]["paymentMethod"],
											$_ENV["request.trans"]["paymentType"],
											$_ENV["request.trans"]["paymentAmount"],
											$_ENV["request.trans"]["paymentStatus"],
											$_ENV["request.trans"]["paymentTransID"],
											$_ENV["request.trans"]["paymentTransResponse"]);	
					// if an error is returned 
					if (substr($insertedPayment,0,2) == '0-') {
						if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
						$_ENV["request.trans"]["errorMessage"] .= 'Payment Insertion Error:'.substr($insertedPayment, 2);
					// if no error 
					} else {

						// get transactions related to this order (including the one just inserted) 
						$orderPaymentTotal = round(CWorderPaymentTotal($_ENV["request.trans"]["orderID"])*100)/100;
						// set balance due 
						$_ENV["request.trans"]["orderBalance"] = $_ENV["request.trans"]["orderTotal"] - $orderPaymentTotal;
						// if order is paid in full (less than 1 cent) 
						if ($_ENV["request.trans"]["orderBalance"] < 0.01) {
							// QUERY: update order status to paid in full (3) 
							$updateOrderStatus = CWqueryUpdateOrder($_ENV["request.trans"]["orderID"],3);
							// if using avatax, post order xml to confirm payment 
							if (strtolower($_ENV["application.cw"]["taxCalctype"]) == 'avatax') {
								$_ENV["request.trans"]["orderXmlData"] = CWpostAvalaraTax($_ENV["request.trans"]["orderID"]);
							}
							// build the order details content 
							$mailBody = CWtextOrderDetails($_ENV["request.trans"]["orderID"], true);
							// SEND EMAIL TO CUSTOMER 
							if ($_ENV["application.cw"]["mailSendPaymentCustomer"]) {
								$mailContent = $_ENV["application.cw"]["mailDefaultOrderPaidIntro"].chr(13).chr(13).
$mailBody.chr(13).chr(13).
$_ENV["application.cw"]["mailDefaultOrderPaidEnd"];
								// send the content to the customer 
								$confirmationResponse = CWsendMail($mailContent, 'Payment Confirmation', $CWcustomer['email']);
							}
							// SEND EMAIL TO MERCHANT 
							if ($_ENV["application.cw"]["mailSendPaymentMerchant"]) {
								$merchantMailContent = "A payment has been processed at ".$_ENV["application.cw"]["companyName"].chr(10).chr(13).
$mailBody.chr(10).chr(13).
"Log in to manage this order and view payment details: ".CWtrailingChar($_ENV["application.cw"]["appSiteUrlHttp"]).CWleadingChar($_ENV["application.cw"]["appCWAdminDir"], "remove");
								// send to merchant 
								$confirmationResponse = CWsendMail($merchantMailContent, 'Payment Notification: '.$_ENV["request.trans"]["orderID"], $_ENV["application.cw"]["companyEmail"]);
							}
							// /end send email 
							// send user to confirmation page 
							header("Location: ".$_ENV["request.cwpage"]["urlConfirmOrder"]."?orderid=".$_ENV["request.trans"]["orderID"]);
							exit;
						// if a balance is still owed after a payment was made
						} else if ($_ENV["request.trans"]["orderBalance"] > 0.009) {
							// QUERY: update order status to partial payment (2) 
							$updateOrderStatus = CWqueryUpdateOrder($_ENV["request.trans"]["orderID"], 2);
							$balanceDueMessage = "Insufficient funds available - balance of ".cartweaverMoney($_ENV["request.trans"]["orderBalance"], 'local')." due. <br>Please use another payment method to complete your order.";
							if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
							$_ENV["request.trans"]["errorMessage"] .= trim($balanceDueMessage);
						}
						// /end balance due check 
					}
					// /end insertion error check 	
				} catch (Exception $e) {
					if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
					$_ENV["request.trans"]["errorMessage"] .= 'Payment Insertion Error: Do Not Resubmit';
				}
			// if transaction was denied 
			} else if ($_ENV["request.trans"]["paymentStatus"] == 'denied') {
				if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
				$_ENV["request.trans"]["errorMessage"] .= 'Transaction Declined - No Payment Processed';
			// if transaction is skipped 
			} else if ($_ENV["request.trans"]["paymentStatus"] == 'none') {
				// if order balance is 0, redirect to confirmation 
				if ($_ENV["request.trans"]["orderBalance"] == 0) {
					header("Location: ".$_ENV["request.cwpage"]["urlConfirmOrder"]);
					exit;
				// if a balance is still owed 
				} else {
					if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
					$_ENV["request.trans"]["errorMessage"] .= 'Invalid Authentication - No Payment Processed';
					if (strlen(trim($_ENV["request.trans"]["paymentTransResponse"]))) {
						if($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ',';
						$_ENV["request.trans"]["errorMessage"] .= trim($_ENV["request.trans"]["paymentTransResponse"]);
					}
				}
			}
			// WIPE DATA - clear transaction data from request scope 
			unset($_ENV["request.trans"]["data"]);
			unset($_REQUEST["data"]);
		} else if ($_SESSION["cw"]["authType"] == 'none') {
			// since we have no errors here, if payment type is none, order is done 
			header("Location: ".$_ENV["request.cwpage"]["urlConfirmOrder"]);
			exit;
		}
	}
	// /END PROCESS PAYMENT 
// if no payment method exists (payments not required) 
} else if ($_ENV["request.trans"]["paymentAuthID"] == 0) {
// set status to 'paid in full' 
	$updateOrderStatus = CWqueryUpdateOrder($_ENV["request.trans"]["orderID"], 3);
	header("Location: ".$_ENV["request.cwpage"]["urlConfirmOrder"]."?orderid=".$_ENV["request.trans"]["orderID"]);
	exit;
}
// /end if no error message - process payment 
?>