<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-auth-paypal.php
File Date: 2012-07-09
Description: PayPal payment processing

NOTE: Setting up accounts and integrating with third party processors is not
a supported feature of Cartweaver. For information and support concerning
payment processors contact the appropriate processor tech support web site or
personnel. Cartweaver includes this integration code as a courtesy with no
guarantee or warranty expressed or implied. Payment processors may make changes
to their protocols or practices that may affect the code provided here.
If so, updates and modifications are the sole responsibility of the user.

Additional PayPal options:
(at the time of this revision, the PayPal transaction variables are listed here)
https://www.paypal.com/cgi-bin/webscr?cmd=p/pdn/howto_checkout-outside#methodone
================================================================
RETURN VARIABLES: paymenttransid, paymenttransresponse, paymentstatus
Values: Transaction ID, Gateway Response Message, Payment Status (approved|denied|none(no response))
These are returned to the containing page or template
in the request.trans scope, e.g. 'request.trans.paymentTransID'
*/
// /// CONFIGURATION / SETUP /// 
// CWauth Payment Configuration 
// USER SETTINGS  [ START ] ==================================================== 
// ============================================================================= 
// PAYPAL SETTINGS 
// Enter Paypal Login (email address) 
global $caller;
if (!isset($settings)) $settings = array();
$settings["paypalLogin"] = 'XXXXXXXXXXXX';
// Enter Currency Code (USD, AUD, CAD, EUR, GBP, JPY)
$settings["currencyCode"] = 'USD';
// Transaction Title (Name for purchase shown to PayPal user) 
$settings["transactionTitle"] = $_ENV["application.cw"]["companyName"] . ' Purchase';
// URL to Post To 
// paypal LIVE URL 
//$settings["authUrl"] = 'https://www.paypal.com/cgi-bin/webscr';
// paypal SANDBOX/TESTING URL 
$settings["authUrl"] = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
// OPTIONAL PAYMENT METHOD VARIABLES 
// method image: an optional logo url (relative or full url) associated with this payment option at checkout
//  Note: be sure to use https prefix if linking to remote image 
$settings["methodImg"] = '';
// shippay message: optional message shown to customer on shipping selection 
$settings["methodSelectMessage"] = 'Pay with PayPal';
// submit message: optional message shown to customer on final order submission 
$settings["methodSubmitMessage"] = 'Click to pay with PayPal';
// confirm message: optioal message shown to customer after order is approved 
$settings["methodConfirmMessage"] = 'PayPal transaction complete';
// send errors: email notice of processing issues (blank = disabled) 
$settings["errorEmail"] = $_ENV["application.cw"]["developerEmail"];
// SUBMIT TEXT 
// submit button value 
$settings["submitText"] = '&raquo;&nbsp;Click to Pay with PayPal';
// processing/loading message 
$settings["loadingText"] = 'Submitting to PayPal...';
// ============================================================================ 
// USER SETTINGS [ END ] ====================================================== 
// METHOD SETTINGS : do not change 
// method name: the user-friendly name shown for this payment option at checkout 
$settings["methodName"] = 'PayPal';
// method type: processor (off-site processing), gateway (integrated credit card form) 
$settings["methodType"] = 'processor';
// key field: transaction variable specific to this payment method 
$settings["methodTransKeyField"] = "\$_POST['txn_id']";
// notification URLs (ipn / paypal transactions) 
$settings["ipnUrl"] = $_ENV["application.cw"]["appSiteUrlHttp"] . $_ENV["application.cw"]["appCWStoreRoot"] . $_ENV["application.cw"]["appPageConfirmOrder"];
$settings["cancelUrl"] = $_ENV["application.cw"]["appSiteUrlHttp"] . $_ENV["application.cw"]["appCWStoreRoot"] . $_ENV["application.cw"]["appPageConfirmOrder"] . '?mode=cancel';
$settings["returnUrl"] = $_ENV["application.cw"]["appSiteUrlHttp"] . $_ENV["application.cw"]["appCWStoreRoot"] . $_ENV["application.cw"]["appPageConfirmOrder"] . '?mode=return';
// default processing values 
if (!isset($_POST["fieldNames"])) $_POST["fieldNames"] = array();
// order functions 
$myDir = getcwd();
chdir(dirname(__FILE__));
if (!isset($_ENV["request.cwapp"]["db_link"])) {
	require_once("../../../Application.php");
}
// global functions
if (!function_exists("CWtime")) {
	require_once("../func/cw-func-global.php");
}
// order functions
if (!function_exists("CWquerySelectOrder")) {
	require_once("../func/cw-func-order.php");
}
// mail functions 
if (!function_exists("CWsendMail")) {
	include("../func/cw-func-mail.php");
}
// clean up form and url variables 
include("../inc/cw-inc-sanitize.php");
chdir($myDir);
// defaults for processing below 
if (!isset($auth_settings)) $auth_settings = array();
if (!isset($auth_settings["trans_data"])) $auth_settings["trans_data"] = "";
if (!isset($auth_settings["auth_mode"])) $auth_settings["auth_mode"] = "";
if (!isset($_ENV["request.trans"]["errorMessage"])) $_ENV["request.trans"]["errorMessage"] = "";
// CONFIG MODE 
// CONFIG DATA : Auth_Mode "config" 
// CONFIG MODE 
// if called in 'config mode', provide configuration data as 'CWauthMethod' struct 
$caller = array();
if (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == "config") {
	// clear scope from previous file operations (in case of multiple payment methods in use) 
	$caller["CWauthMethod"] = array();
	// these values get parsed into application.cw.authMethodData (add more here to have them available sitewide) 
	$caller["CWauthMethod"]["methodName"] = $settings["methodName"];
	$caller["CWauthMethod"]["methodType"] = $settings["methodType"];
	$caller["CWauthMethod"]["methodImg"] = $settings["methodImg"];
	$caller["CWauthMethod"]["methodSelectMessage"] = $settings["methodSelectMessage"];
	$caller["CWauthMethod"]["methodSubmitMessage"] = $settings["methodSubmitMessage"];
	$caller["CWauthMethod"]["methodConfirmMessage"] = $settings["methodConfirmMessage"];
	$caller["CWauthMethod"]["methodTransKeyField"] = $settings["methodTransKeyField"];
	// CAPTURE MODE 
	// CAPTURE MODE : Auth_Mode "capture" : Form for submission to off-site processor 
	// CAPTURE MODE 
	// if called in 'process' mode, process order 
} elseif (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == "capture") {
	// verify transaction data is ok 
	if (!is_array($auth_settings["trans_data"])) {
		if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
		$_ENV["request.trans"]["errorMessage"] .= 'Payment Data Incomplete';
	// if data is ok, run processing 
	} else {
		// simplify to 'tdata' struct 
		$tdata = $auth_settings["trans_data"];
		// defaults for transaction data 
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
	}
	// /end if data ok 
	// paypal transaction form with hidden inputs ?>
		<form action="<?php echo trim($settings["authUrl"]); ?>" id="CWformPaypalProcess" method="post">
			<div>
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="<?php echo $settings["paypalLogin"]; ?>">
				<input type="hidden" name="notify_url" value="<?php echo $settings["ipnUrl"]; ?>">
				<input type="hidden" name="return" value="<?php echo $settings["returnUrl"]; ?>&amp;orderid=<?php echo $tdata["orderID"]; ?>">
				<input type="hidden" name="cancel_return" value="<?php echo $settings["cancelUrl"]; ?>&amp;orderid=<?php echo $tdata["orderID"]; ?>">
				<?php // cart details ?>
				<input type="hidden" name="item_name" value="<?php echo $settings["transactionTitle"]; ?>">
				<input type="hidden" name="quantity" value="1">
				<input type="hidden" name="amount" value="<?php echo number_format($tdata["paymentAmount"], 2, ".", ""); ?>">
				<input type="hidden" name="invoice" value="<?php echo $tdata["orderID"]; ?>">
				<?php // override customer's stored paypal info with order data ?>
				<input type="hidden" name="address_override" value="1">
				<?php // customer info fields ?>
				<input type="hidden" name="first_name" value="<?php echo $tdata["customerNameFirst"]; ?>">
				<input type="hidden" name="last_name" value="<?php echo $tdata["customerNameLast"]; ?>">
				<input type="hidden" name="address1" value="<?php echo $tdata["customerAddress1"]; ?>">
				<input type="hidden" name="address2" value="<?php echo $tdata["customerAddress2"]; ?>">
				<input type="hidden" name="city" value="<?php echo $tdata["customerCity"]; ?>">
				<input type="hidden" name="state" value="<?php echo $tdata["customerState"]; ?>">
				<input type="hidden" name="zip" value="<?php echo $tdata["customerZip"]; ?>">
				<input type="hidden" name="country" value="<?php echo $tdata["customerCountry"]; ?>">
				<input type="hidden" name="currency_code" value="<?php echo $settings["currencyCode"]; ?>">
				<?php // paypal defaults ?>
				<input type="hidden" name="no_shipping" value="1">
				<input type="hidden" name="no_note" value="1">
			</div>
			<div>
				<?php // submit button ?>
				<div class="center top40 bottom40">
				<input name="submit" id="paypal_submit" type="submit" class="CWformButton" value="<?php echo $settings["submitText"]; ?>">
				<?php // submit link : javascript replaces button with this link ?>
				<a style="display:none;" href="#" class="CWcheckoutLink" id="CWlinkAuthSubmit"><?php echo $settings["submitText"]; ?></a>
				<br>
					<div style="display:none;" id="ppLoading">
						<img style="margin-top:35px;" src="<?php echo $_ENV["request.cw"]["assetSrcDir"]; ?>css/theme/cw-loading-wide.gif" height="15" width="128">
					</div>
				</div>
			</div>
		</form>
<?php
	// javascript for form display & function 
	$ppFormjs = "
	<script type=\"text/javascript\">
	jQuery(document).ready(function(){
		// hide standard button
		jQuery('#paypal_submit').hide();
		// show link, change text
		jQuery('#CWlinkAuthSubmit').text('".$settings["loadingText"]."').show();
		// add loading graphic below button
		jQuery('#ppLoading').show();
		// clicking link submits form
		jQuery('#CWlinkAuthSubmit').click(function(){
			jQuery('#paypal_submit').trigger('click');
		});
		// auto submit the form by triggering a click of the submit button
		jQuery('#paypal_submit').delay('300').trigger('click');
	});
	</script>";
	CWinsertHead($ppFormjs);
	// /end CAPTURE mode 

// PROCESS MODE 
// PROCESS MODE (form post from PayPal IPN) 
// PROCESS MODE 
// verify form post with txn_id from paypal, and no variables in url 
} else if (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == 'process') {
	// read post from PayPal, add "cmd" variable to string, and post back (no other changes allowed) 
	$str="cmd=_notify-validate";
	foreach ($_POST as $ff => $fval) {
		// date gets handled separately 
		if ($ff != "payment_date" && !is_array($fval)) {
			$str .= "&".strtolower($ff)."=".urlencode($fval);
		}
	}
	// encode and add date here if given 
	if (isset($_POST["payment_date"])) {
		$str .= "&payment_date=".urlencode($_POST["payment_date"]);
	}
	// post  back to PayPal system to validate 
	$pp_curl = curl_init();
	curl_setopt($pp_curl, CURLOPT_URL, trim($settings["authUrl"])."?".$str);
	curl_setopt($pp_curl, CURLOPT_HEADER, 0);
	curl_setopt($pp_curl, CURLOPT_POST, 0);
	curl_setopt($pp_curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($pp_curl, CURLOPT_RETURNTRANSFER, 1);
	$textResponse = curl_exec($pp_curl);
	curl_close($pp_curl);
	// default form values (set to null, if not included in content above) 
	if (!isset($_POST["txn_type"])) $_POST["txn_type"] = "";
	if (!isset($_POST["invoice"])) $_POST["invoice"] = "";
	if (!isset($_POST["mc_gross"])) $_POST["mc_gross"] = "";
	if (!isset($_POST["payment_status"])) $_POST["payment_status"] = "";
	if (!isset($_POST["pending_reason"])) $_POST["pending_reason"] = "";
	if (!isset($_POST["payment_type"])) $_POST["payment_type"] = "";
	// create list of form values for response text 
	$_ENV["request.trans"]["paymentTransResponse"] = "";
	foreach ($_POST as $ff => $fval) {
		$_ENV["request.trans"]["paymentTransResponse"] .= $ff . '=' . $fval . chr(13);
	}
	// add heading to response data 
	$_ENV["request.trans"]["paymentTransResponse"] = 'PayPal Form Values: ' . chr(13) . '===' . chr(13) . $_ENV["request.trans"]["paymentTransResponse"];
	// set values into page request 
	$_ENV["request.trans"]["orderID"] = trim($_POST["invoice"]);
	$_ENV["request.trans"]["paymentAmount"] = $_POST["mc_gross"];
	$_ENV["request.trans"]["paymentTransID"] = $_POST["txn_id"];
	$_ENV["request.trans"]["paymentStatus"] = strtolower(trim($_POST["payment_status"]));
	// take action based on response from http request (VERIFIED|INVALID)
	$responseStr = strtolower(trim($textResponse));
	switch ($responseStr) {
		// if order is VERIFIED 
		case "verified":
			// verify form value from paypal is correct 
			if ($_POST["txn_type"] == "web_accept") {
				// take action based on value of "payment_status" field from paypal 
				switch ($_ENV["request.trans"]["paymentStatus"]) {
					// COMPLETED status from PayPal 
					case "completed":
						// QUERY: check for duplicate transaction (payment) 
						$transQuery = CWqueryGetTransaction($_ENV["request.trans"]["paymentTransID"]);
						// if Duplicate Transaction exists, we have a duplicate post 
						if ($transQuery["totalRows"] > 0) {
							// set error message 
							if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
							$_ENV["request.trans"]["errorMessage"] .= 'Duplicate Payment - Verify Order Balance With Merchant';
						// if Not a Duplicate 
						} else {
							// QUERY: Verify Order Exists by ID ('invoice' number from paypal) 
							$orderQuery = CWquerySelectOrderDetails($_ENV["request.trans"]["orderID"]);
							// if order is valid 
							if ($orderQuery["totalRows"] > 0) {
								// verify payment amount not greater than order total 
								if ($orderQuery["order_total"][0] >= $_ENV["request.trans"]["paymentAmount"]) {
									// payment is ok, save payment 
									try {
										// QUERY: insert payment to database,
										//returns payment id if successful, or 0-based message if not
										//
										$insertedPayment = CWsavePayment(
													$_ENV["request.trans"]["orderID"],
													$settings["methodName"],
													$settings["methodType"],
													$_ENV["request.trans"]["paymentAmount"],
													'approved',
													$_ENV["request.trans"]["paymentTransID"],
													$_ENV["request.trans"]["paymentTransResponse"]
													);
										// if an error is returned 
										if (substr($insertedPayment,0,2) == '0-') {
											if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
											$_ENV["request.trans"]["errorMessage"] .= 'Payment Insertion Error: '.str_replace("0-", "", $insertedPayment).'';
											// if no error, update order status, handle remaining balance 
										} else {
											// get transactions related to this order (including the one just inserted) 
											$orderPaymentTotal = round(CWorderPaymentTotal($_ENV["request.trans"]["orderID"])*100)/100;
											// set balance due 
											$_ENV["request.trans"]["orderBalance"] = $orderQuery["order_total"][0] - $orderPaymentTotal;
											// if order is paid in full (0 or less) 
											if ($_ENV["request.trans"]["orderBalance"] <= 0) {
												// QUERY: update order status to paid in full (3) 
												$updateOrderStatus = CWqueryUpdateOrder($_ENV["request.trans"]["orderID"],3);
												// SEND EMAIL TO CUSTOMER 
												// build the order details content 
												$mailBody = CWtextOrderDetails($_ENV["request.trans"]["orderID"], true);
												$mailContent = $_ENV["application.cw"]["mailDefaultOrderPaidIntro"]."
".chr(13).chr(13)."
".$mailBody."
".chr(13).chr(13)."
".$_ENV["application.cw"]["mailDefaultOrderPaidEnd"];
												// send the content to the customer 
												$confirmationResponse = CWsendMail($mailContent, 'Payment Confirmation', $orderQuery["customer_email"][0]);
												// SEND EMAIL TO MERCHANT 
												$merchantMailContent = "A payment has been processed at ".$_ENV["application.cw"]["companyName"]."
".chr(10).chr(13)."
".$mailBody."
".chr(10).chr(13)."
Log in to manage this order and view payment details: ".$_ENV["application.cw"]["appSiteUrlHttp"]."/".$_ENV["application.cw"]["appCWAdminDir"];
												// send to merchant 
												$confirmationResponse = CWsendMail($merchantMailContent, 'Payment Notification: '.$_ENV["request.trans"]["orderID"], $_ENV["application.cw"]["companyEmail"]);
												// /end send email 
												// if a balance is still owed after a payment was made
											} else if ($_ENV["request.trans"]["orderBalance"] > 0) {
												// QUERY: update order status to partial payment (2) 
												$updateOrderStatus = CWqueryUpdateOrder($_ENV["request.trans"]["orderID"], 2);
												if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
												$_ENV["request.trans"]["errorMessage"] .= 'Balance of '.cartweaverMoney($_ENV["request.trans"]["orderBalance"],'local').' due';
											}
											// /end balance due check 
										}
										// /end insertion error check 
									} catch (Exception $e) {
										// capture any errors from processing 
										if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
										$_ENV["request.trans"]["errorMessage"] .= 'Payment Insertion Error: '.$e->getMessage();
									}
									// /end valid payment 
									// if payment is over order, we have a mismatch 
								} else {
									if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
									$_ENV["request.trans"]["errorMessage"] .= 'Invalid Payment Amount';
								}
								// /end verify payment amount 
								// if order is invalid (no matching order by ID) 
							} else {
								if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
								$_ENV["request.trans"]["errorMessage"] .= 'Invalid Payment - No Matching Order, Payment Not Applied';
							}
							// /end if order is invalid 
						}
						// /end duplicate yes/no 
						break;
					// /end Completed Status 

					// if status is not 'completed' 
					default:
						if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
						$_ENV["request.trans"]["errorMessage"] .= 'Invalid Order Status - '.$_ENV["request.trans"]["paymentStatus"];
						break;
					// /end non 'completed' status 
				}
				// /end switch for payment_status 
			// if txn_type is not 'web_accept' 
			} else {
				if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
				$_ENV["request.trans"]["errorMessage"] .= 'Invalid Paypal Transaction Type - '.$_POST["txn_type"];
			}
			// /end txn_type eq 'web_accept' 
			break;
		// /end value=verified 

		// if order is INVALID 
		case "invalid":
			if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
			$_ENV["request.trans"]["errorMessage"] .= 'Invalid PayPal Transaction - Payment Not Accepted';
			break;
		// /end value=invalid 
	}
	// /end switch for PayPoint response 
	// IF ERRORS: notify admin of any errors 
	if (strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
		$mailContent = "The following errors were reported while processing an attempted PayPal payment:".chr(13)."
===".chr(13)."
Order: ".$_ENV["request.trans"]["orderID"].chr(13)."
Transaction ID: ".$_ENV["request.trans"]["paymentTransID"].chr(13)."
===".chr(13)."
";
		$errArr = $_ENV["request.trans"]["errorMessage"];
		if (!is_array($errArr) && strlen($errArr)) $errArr = explode(",", $errArr);
		else if (!is_array($errArr)) $errArr = array();
		foreach ($errArr as $key => $ee) {
			$mailContent .= "
".$ee.chr(13);
		}
		$mailContent .= "
===".chr(13)."
".$_ENV["request.trans"]["paymentTransResponse"]."
===".chr(13)."
Order Details:".chr(13)."
".CWtextOrderDetails($_ENV["request.trans"]["orderID"]);
		// if enabled, send the error message to the site admin 
		if (isset($settings["errorEmail"]) && isValidEmail($settings["errorEmail"])) {
			$confirmationResponse = CWsendMail($mailContent, 'PayPal Processing Error',$_ENV["application.cw"]["developerEmail"]);
		}
		// /end send email 
	}
	// /end if errors 
	// prevent processor from triggering further response from our page 
	exit;
	// /END PROCESS MODE 
}
?>