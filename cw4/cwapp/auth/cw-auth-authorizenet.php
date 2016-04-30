<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-auth-authorizenet.php
File Date: 2012-07-09
Description: Authorize.net payment processing.

NOTE: Setting up accounts and integrating with third party processors is not
a supported feature of Cartweaver. For information and support concerning
payment processors contact the appropriate processor tech support web site or
personnel. Cartweaver includes this integration code as a courtesy with no
guarantee or warranty expressed or implied. Payment processors may make changes
to their protocols or practices that may affect the code provided here.
If so, updates and modifications are the sole responsibility of the user.
================================================================
RETURN VARIABLES: paymenttransid, paymenttransresponse, paymentstatus
Values: Transaction ID, Gateway Response Message, Payment Status (approved|denied|none(no response))
These are returned to the containing page or template
in the request.trans scope, e.g. 'request.trans.paymentTransID'
API: http://developer.authorize.net/guides/AIM/
EXPLANATION OF FIELDS: http://developer.authorize.net/guides/AIM/Appendix_B/Appendix_B_Alphabetized_List_of_API_Fields.htm
 */
// /// CONFIGURATION / SETUP /// 
// CWauth Payment Configuration 
// USER SETTINGS  [ START ] ==================================================== 
// ============================================================================= 
// AUTHORIZE.NET SETTINGS 
// Enter API Login ID: 
global $caller;
$settings["authNetLogin"] = "xxxxxxxxxxxx";
// Enter Transaction Key: 
$settings["transactionKey"] = "xxxxxxxxxxxx";
// Test Mode - set to false for live transactions, or if using developer URL above: 
$settings["testMode"] = "True";
// URL to Post To 
// LIVE ACCOUNTS URL (use for standard authorize.net account in active OR test mode) 
//$settings["authUrl"] = 'https://secure.authorize.net/gateway/transact.dll';
// DEVELOPER URL (use for authorize.net developer accounts only) 
$settings["authUrl"] = 'https://test.authorize.net/gateway/transact.dll';
// OPTIONAL PAYMENT METHOD VARIABLES 
// method image: an optional logo url (relative or full url) associated with this payment option at checkout
//  Note: be sure to use https prefix if linking to remote image 
$settings["methodImg"] = '';
// shippay message: optional message shown to customer on shipping selection 
$settings["methodSelectMessage"] = 'Pay with your credit card using Authorize.net';
// submit message: optional message shown to customer before payment submission 
$settings["methodSubmitMessage"] = 'Click to pay with Authorize.net';
// confirm message: optional message shown to customer after order is approved 
$settings["methodConfirmMessage"] = 'Transaction authorized';
// send errors: email notice of processing issues (blank = disabled) 
$settings["errorEmail"] = $_ENV["application.cw"]["developerEmail"];
// ============================================================================ 
// USER SETTINGS [ END ] ====================================================== 
// METHOD SETTINGS : do not change 
// method name: the user-friendly name shown for this payment option at checkout 
$settings["methodName"] = 'Authorize.net';
// method type: processor (off-site processing), gateway (integrated credit card form) 
$settings["methodType"] = 'gateway';
// mail functions 
if (!function_exists("CWsendMail")) {
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	require_once("../func/cw-func-mail.php");
	chdir($myDir);
}
// CONFIG DATA : These variables are returned to the calling page in the "CWauthMethod" scope 
// if called in 'config mode', provide configuration data 
$caller = array();
if (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == "config") {
	// these values get parsed into application.cw.authMethodData (add more here to have them available sitewide) 
	$caller["CWauthMethod"]["methodName"] = $settings["methodName"];
	$caller["CWauthMethod"]["methodType"] = $settings["methodType"];
	$caller["CWauthMethod"]["methodImg"] = $settings["methodImg"];
	$caller["CWauthMethod"]["methodSelectMessage"] = $settings["methodSelectMessage"];
	$caller["CWauthMethod"]["methodSubmitMessage"] = $settings["methodSubmitMessage"];
	$caller["CWauthMethod"]["methodConfirmMessage"] = $settings["methodConfirmMessage"];
// PROCESS ORDER 
// if called in 'process' mode, process order 
} elseif (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == "process") {
	if (!isset($auth_settings["trans_data"])) $auth_settings["trans_data"] = "";
	// verify transaction data is ok 
	if (!is_array($auth_settings["trans_data"])) {
		$_ENV["request.trans"]["errorMessage"] = 'Payment Data Incomplete';
	// if data is ok, run processing 
	} else {
		// simplify to 'tdata' struct 
		$tdata = $auth_settings["trans_data"];
		// DEFAULTS FOR TRANSACTION DATA 
		// card data 
		if (!isset($tdata["cardtype"])) $tdata["cardtype"] = "";
		if (!isset($tdata["cardname"])) $tdata["cardname"] = "";
		if (!isset($tdata["cardnumber"])) $tdata["cardnumber"] = "";
		if (!isset($tdata["cardccv"])) $tdata["cardccv"] = "";
		if (!isset($tdata["cardexpm"])) $tdata["cardexpm"] = "";
		if (!isset($tdata["cardexpy"])) $tdata["cardexpy"] = "";
		// customer 
		if (!isset($tdata["customerID"])) $tdata["customerID"] = "";
		if (!isset($tdata["customerCompany"])) $tdata["customerCompany"] = "";
		if (!isset($tdata["customerEmail"])) $tdata["customerEmail"] = "";
		if (!isset($tdata["customerPhone"])) $tdata["customerPhone"] = "";
		if (!isset($tdata["customerAddress1"])) $tdata["customerAddress1"] = "";
		if (!isset($tdata["customerAddress2"])) $tdata["customerAddress2"] = "";
		if (!isset($tdata["customerCity"])) $tdata["customerCity"] = "";
		if (!isset($tdata["customerState"])) $tdata["customerState"] = "";
		if (!isset($tdata["customerZip"])) $tdata["customerZip"] = "";
		if (!isset($tdata["customerCountry"])) $tdata["customerCountry"] = "";
		// order/payment 
		if (!isset($tdata["orderID"])) $tdata["orderID"] = "";
		if (!isset($tdata["paymentAmount"])) $tdata["paymentAmount"] = 0;
		// shipping details 
		if (!isset($tdata["customerShipCompany"])) $tdata["customerShipCompany"] = "";
		if (!isset($tdata["customerShipAddress1"])) $tdata["customerShipAddress1"] = "";
		if (!isset($tdata["customerShipAddress2"])) $tdata["customerShipAddress2"] = "";
		if (!isset($tdata["customerShipCity"])) $tdata["customerShipCity"] = "";
		if (!isset($tdata["customerShipState"])) $tdata["customerShipState"] = "";
		if (!isset($tdata["customerShipZip"])) $tdata["customerShipZip"] = "";
		if (!isset($tdata["customerShipCountry"])) $tdata["customerShipCountry"] = "";
		// PASS TRANSACTION TO GATEWAY 
		try {
			// authorize.net form fields 
			$cardName = explode(" ", substr($tdata["cardname"], 0, 50));
			$cardLast = ""; for ($cn=1; $cn<sizeof($cardName); $cn++) { if ($cn != 1) { $cardLast .= " "; } $cardLast .= $cardName[$cn]; };
			$anetPost = "x_login=".$settings["authNetLogin"].
						"&x_tran_key=".$settings["transactionKey"].
						"&x_test_request=".$settings["testMode"].
						"&x_version=3.0".
						"&x_type=AUTH_CAPTURE".
						"&x_method=CC".
						"&x_delim_data=TRUE".
						"&x_delim_char=,".
						"&x_invoice_num=".$tdata["orderID"].
						"&x_relay_response=false".
						// payment data 
						"&x_amount=".number_format($tdata["paymentAmount"], 2, ".", "").
						"&x_card_num=".$tdata["cardnumber"].
						"&x_exp_date=".$tdata["cardexpm"].$tdata["cardexpy"].
						"&x_card_code=".$tdata["cardccv"].
						// customer data 
						"&x_first_name=".$cardName[0].
						"&x_last_name=".$cardLast.
						"&x_company=".substr($tdata["customerCompany"], 0, 50).
						"&x_address=".substr($tdata["customerAddress1"], 0, 60).
						"&x_city=".substr($tdata["customerCity"], 0, 40).
						"&x_state=".substr($tdata["customerState"], 0, 40).
						"&x_zip=".substr($tdata["customerZip"], 0, 20).
						"&x_country=".substr($tdata["customerCountry"], 0, 60).
						"&x_phone=".substr($tdata["customerPhone"], 0, 25).
						"&x_email=".substr($tdata["customerEmail"], 0, 255).
						"&x_cust_id=".substr($tdata["customerID"], 0, 20).
						"&x_customer_ip=".$_SERVER["REMOTE_ADDR"].
						// shipping data 
						"&x_ship_to_company=".substr($tdata["customerShipCompany"], 0, 50).
						"&x_ship_to_address=".substr($tdata["customerShipAddress1"], 0, 50).
						"&x_ship_to_city=".substr($tdata["customerShipCity"], 0, 50).
						"&x_ship_to_state=".substr($tdata["customerShipState"], 0, 50).
						"&x_ship_to_zip=".substr($tdata["customerShipZip"], 0, 50).
						"&x_ship_to_country=".substr($tdata["customerShipCountry"], 0, 50);
			$anet_curl = curl_init();
			curl_setopt($anet_curl, CURLOPT_URL, $settings["authUrl"]);
			curl_setopt($anet_curl, CURLOPT_HEADER, 0);
			curl_setopt($anet_curl, CURLOPT_POST, 1);
			curl_setopt($anet_curl, CURLOPT_POSTFIELDS, $anetPost);
			curl_setopt($anet_curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($anet_curl, CURLOPT_RETURNTRANSFER, 1);
			$textResponse = curl_exec($anet_curl);
			curl_close($anet_curl);
			// PROCESS RESULT 
			$_ENV["request.trans"]["httpResponse"] = $textResponse;
			$respArr = explode(",", $textResponse);
			$_ENV["request.trans"]["paymentTransStatus"] = $respArr[0];
			$_ENV["request.trans"]["paymentTransID"] = $respArr[6];
			$_ENV["request.trans"]["paymentTransResponse"] = $respArr[3];
			// transfer auth.net numeric status to our values (approved|denied|none) 
			if ($_ENV["request.trans"]["paymentTransStatus"] == 1) {
				$_ENV["request.trans"]["paymentStatus"] = 'approved';
			} else {
				$_ENV["request.trans"]["paymentStatus"] = 'denied';
				if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
				$_ENV["request.trans"]["errorMessage"] .= $_ENV["request.trans"]["paymentTransResponse"];
			}
		} catch (Exception $e) {
			$_ENV["request.trans"]["paymentStatus"] = 'denied';
			$_ENV["request.trans"]["errorMessage"] = 'Data Error - Payment Not Processed';
			$_ENV["request.trans"]["errorTrace"] = "Error Details:".chr(13).
$e->getMessage().chr(13).
"Error Trace:".chr(13).
$e->getTraceAsString();
		}
		// IF ERRORS: notify admin of any errors 
		if (strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
			// list of values not included in email message 
			$secureVals = array("CARDNUMBER","CARDCCV","CARDEXPM","CARDEXPY","CARDNAME","CUSTOMER_CARDNAME","CUSTOMER_CARDNUMBER","CUSTOMER_CARDEXPM","CUSTOMER_CARDEXPY","CUSTOMER_CARDCCV");
			// set up mail content 
			$mailContent = "
One or more problems were reported by Authorize.net
while attempting to process a transaction:".chr(13);
			if (isset($_ENV["request.trans"]["paymentStatus"]) && strtolower($_ENV["request.trans"]["paymentStatus"]) == 'approved') {
				$mailContent .= "
Authorize.net reported a payment with status 'approved', but other errors may have occurred.";
			} else if (isset($_ENV["request.trans"]["paymentStatus"])) {
				$mailContent .= "
A status of '".$_ENV["request.trans"]["paymentStatus"]."' was reported while attempting to process a payment.";
			}
			$mailContent .= "
Details of the transaction are below.".chr(13).
"===".chr(13).
"Transaction Error Details:".chr(13);
			$emArr = $_ENV["request.trans"]["errorMessage"];
			if (!is_array($emArr) && strlen(trim($emArr))) $emArr = explode(",", $emArr);
			else if (!is_array($emArr)) $emArr = array();
			foreach ($emArr as $key => $ee) {
				$mailContent .= $ee.chr(13);
			}
			if (isset($_ENV["request.trans"]["errorMessage"])) {
				$mailContent .= "===".chr(13)."
Auth.net Response:".chr(13).
$_ENV["request.trans"]["errorMessage"];
			}
			if (isset($_ENV["request.trans"]["paymentTransResponse"])) {
				$mailContent .= "===".chr(13)."
Transaction Response:".chr(13).
$_ENV["request.trans"]["paymentTransResponse"];
			}
			$mailContent .= "
===".chr(13);
			if (isset($tdata)) {
				$mailContent .= "Transaction Data:
===".chr(13);
				foreach ($tdata as $key => $colItem) {
					$mailContent .= htmlentities($key)."=";
					if (!in_array($key, $secureVals)) {
						$mailContent .= htmlentities($colItem);
					} else {
						$mailContent .= "x";
					}
					$mailContent .= chr(13);
				}
			}
			$mailContent .= "
===".chr(13)."
Form Values:".chr(13)."
";
			foreach ($_POST as $key => $colItem) {
				$mailContent .= htmlentities($key)."=";
				if (!in_array($key, $secureVals)) {
					$mailContent .= htmlentities($colItem);
				} else {
					$mailContent .= "x";
				}
				$mailContent .= chr(13);
			}
			$mailContent .= "
===".chr(13)."
";
			if (isset($_ENV["request.trans"]["errorTrace"])) {
				$mailContent .= "Error Details:".chr(13)."
".$_ENV["request.trans"]["errorTrace"]."
";
			}
			$mailContent .= "
Order: ".$_ENV["request.trans"]["orderID"].chr(13)."
Transaction ID: ".$_ENV["request.trans"]["paymentTransID"].chr(13)."
===".chr(13)."
Order Details:".chr(13)."
".CWtextOrderDetails($_ENV["request.trans"]["orderID"]);
			// if enabled, send the error message to the site admin 
			if (isset($settings["errorEmail"]) && isValidEmail($settings["errorEmail"])) {
				$confirmationResponse = CWsendMail($mailContent, 'Authorize.net Processing Error',$settings["errorEmail"]);
			}
		}
		// /end if errors 
		// CLEAR TRANSACTION DATA 
		unset($settings);
		unset($tdata);
	}
	// /end if transaction data is ok 
// /END PROCESS ORDER MODE 
}
// /END MODE SELECTION 
?>