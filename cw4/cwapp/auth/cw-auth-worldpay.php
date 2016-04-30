<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-auth-worldpay.php
File Date: 2012-07-09
Description: WorldPay payment processing

NOTE: Setting up accounts and integrating with third party processors is not
a supported feature of Cartweaver. For information and support concerning
payment processors contact the appropriate processor tech support web site or
personnel. Cartweaver includes this integration code as a courtesy with no
guarantee or warranty expressed or implied. Payment processors may make changes
to their protocols or practices that may affect the code provided here.
If so, updates and modifications are the sole responsibility of the user.

Additional WorldPay options:
(at the time of this revision, the WorldPay transaction variables are listed here)
http://www.worldpay.com/support/bg/index.php?page=development&sub=integration&subsub=requirements&c=UK
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
// WORLDPAY SETTINGS 
// Enter WorldPay Install ID for this site 
global $caller;
if (!isset($settings)) $settings = array();
$settings["worldpayID"] = "xxxxxxxxxxxx";
// Enter Currency Code (USD, AUD, CAD, EUR, GBP, JPY... all ISO codes)
$settings["currencyCode"] = 'GBP';
// NOTE: to suppress the display of currency drop down at WorldPay send hideCurrency = true 
// WorldPay Test Status: set this to "100" to test, "0" to go live  
$settings["testmode"] = '100';
// Order Description: sent to worldpay as transaction desc 
$settings["orderDescription"] = 'Order Details';
// URL to Post To 
// LIVE ACCOUNTS URL (use for active transactions once worldpay is configured) 
// //$settings["authUrl"] = 'https://select.worldpay.com/wcc/purchase';
//
// TESTING URL (use for testing transactions) 
$settings["authUrl"] = 'https://secure-test.wp3.rbsworldpay.com/wcc/purchase';
// OPTIONAL PAYMENT METHOD VARIABLES 
// method image: an optional logo url (relative or full url) associated with this payment option at checkout
//  Note: be sure to use https prefix if linking to remote image 
$settings["methodImg"] = '';
// shippay message: optional message shown to customer on shipping selection 
$settings["methodSelectMessage"] = 'Pay securely using WorldPay';
// submit message: optional message shown to customer on final order submission 
$settings["methodSubmitMessage"] = 'Click Place Order to pay securely at WorldPay';
// confirm message: optioal message shown to customer after order is approved 
$settings["methodConfirmMessage"] = 'Thanks for using WorldPay for your transaction';
// SUBMIT TEXT 
// submit button value 
$settings["submitText"] = '&raquo;&nbsp;Click to Pay with WorldPay';
// processing/loading message 
$settings["loadingText"] = 'Submitting to WorldPay...';
// ============================================================================ 
// USER SETTINGS [ END ] ====================================================== 
// METHOD SETTINGS : do not change 
// method name: the user-friendly name shown for this payment option at checkout 
$settings["methodName"] = 'WorldPay';
// method type: processor (off-site processing), gateway (integrated credit card form) 
$settings["methodType"] = 'processor';
// key field: transaction variable specific to this payment method 
$settings["methodTransKeyField"] = "\$_POST['transId']";
// default processing values 
if (!isset($_POST["fieldNames"])) $_POST["fieldNames"] = "";
$myDir = getcwd();
chdir(dirname(__FILE__));
// global functions
if (!function_exists("CWtime")) {
	require_once("../func/cw-func-global.php");
}
// order functions
if (!function_exists('CWquerySelectOrder')) {
	include("../func/cw-func-order.php");
}
// mail functions 
if (!function_exists('CWsendMail')) {
	include("../func/cw-func-mail.php");
}
// clean up form and url variables 
include("../inc/cw-inc-sanitize.php");
chdir($myDir);
// defaults for processing below 
if (!isset($auth_settings["trans_data"])) $auth_settings["trans_data"] = "";
if (!isset($auth_settings["auth_mode"])) $auth_settings["auth_mode"] = "";
if (!isset($_ENV["request.trans"]["errorMessage"])) $_ENV["request.trans"]["errorMessage"] = "";
// CONFIG MODE 
// CONFIG DATA : Auth_Mode "config" 
// CONFIG MODE 
// if called in 'config mode', provide configuration data as 'CWauthMethod' struct 
if (!isset($caller)) $caller = array();
if (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == 'config') {
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
} else if (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == 'capture') {
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
		// country codes need a little handling (use GB instead of UK, and as a default) 
		if ($tdata["customerCountry"] == "UK") {
			$tdata["transactionCountry"] = "GB";
		} else if ($tdata["customerCountry"] == "UK") {
			$tdata["transactionCountry"] = "GB";
		} else {
			$tdata["transactionCountry"] = $tdata["customerCountry"];
		}
		// order/payment 
		if (!isset($tdata["orderID"])) $tdata["orderID"] = "";
		if (!isset($tdata["paymentAmount"])) $tdata["paymentAmount"] = 0;
	}
	// /end if data ok 
	// wp transaction form with hidden inputs ?>
		<form action="<?php echo trim($settings["authUrl"]); ?>" id="CWformWorldPayProcess" method="post">
			<div>
				<input type="hidden" name="instId" value="<?php echo $settings["worldpayID"]; ?>">
				<input type="hidden" name="testMode" value="<?php echo $settings["testmode"]; ?>">
				<input type="hidden" name="currency" value="<?php echo $settings["currencyCode"]; ?>">
				<input type="hidden" name="desc" value="<?php echo $settings["orderDescription"]; ?>">
				<input type="hidden" name="hideCurrency" value="true">
				<?php // cart details ?>
				<input type="hidden" name="cartId" value="<?php echo $tdata["orderID"]; ?>">
				<input type="hidden" name="amount" value="<?php echo number_format($tdata["paymentAmount"],2,".",""); ?>">
				<?php // customer info fields ?>
				<input type="hidden" name="name" value="<?php echo $tdata["customerNameFirst"]." ".$tdata["customerNameLast"]; ?>">
				<input type="hidden" name="address" value="<?php echo $tdata["customerAddress1"]." ".$tdata["customerAddress2"].", ".$tdata["customerCity"]; ?>">
				<input type="hidden" name="postcode" value="<?php echo $tdata["customerZip"]; ?>">
				<input type="hidden" name="country" value="<?php echo $tdata["transactionCountry"]; ?>">
				<input type="hidden" name="phone" value="<?php echo $tdata["customerPhone"]; ?>">
				<input type="hidden" name="email" value="<?php echo $tdata["customerEmail"]; ?>">
			</div>
			<div>
				<?php // submit button ?>
				<div class="center top40 bottom40">
				<input name="submit" id="worldpay_submit" type="submit" class="CWformButton" value="<?php echo $settings["submitText"]; ?>">
				<?php // submit link : javascript replaces button with this link ?>
				<a style="display:none;" href="#" class="CWcheckoutLink" id="CWlinkAuthSubmit"><?php echo $settings["submitText"]; ?></a>
				<br>
					<div style="display:none;" id="ppLoading">
						<img style="margin-top:35px;" src="<?php $_ENV["request.cw"]["assetSrcDir"]; ?>css/theme/cw-loading-wide.gif" height="15" width="128">
					</div>
				</div>
			</div>
		</form>
	<?php // javascript for form display & function 
	$wpFormjs = "
	<script type=\"text/javascript\">
	jQuery(document).ready(function(){
		// hide standard button
		jQuery('#worldpay_submit').hide();
		// show link, change text
		jQuery('#CWlinkAuthSubmit').text('".$settings["loadingText"]."').show();
		// add loading graphic below button
		jQuery('#ppLoading').show();
		// clicking link submits form
		jQuery('#CWlinkAuthSubmit').click(function(){
			jQuery('#worldpay_submit').trigger('click');
		});
		// auto submit the form by triggering a click of the submit button
		jQuery('#worldpay_submit').delay('300').trigger('click');
	});
	</script>
";
	CWinsertHead($wpFormjs);
// /end CAPTURE mode 
// PROCESS MODE 
// PROCESS MODE (form post from WorldPay callback) 
// PROCESS MODE 
// verify form post with transid from wp 
} else if (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == 'process') {
	// create list of form values for response text 
	$_ENV["request.trans"]["paymentTransResponse"] = "";
	foreach ($_POST as $ff => $fval) {
		if (!is_array($fval)) {
			$_ENV["request.trans"]["paymentTransResponse"] .= $ff."=".$fval.chr(13);
		}
	}
	// add heading to response data 
	$_ENV["request.trans"]["paymentTransResponse"] = 'WorldPay Form Values: ' . chr(13) . '===' . chr(13) . $_ENV["request.trans"]["paymentTransResponse"];
	// set values into page request 
	$_ENV["request.trans"]["orderID"] = trim($_POST["cartId"]);
	$_ENV["request.trans"]["paymentamount"] = $_POST["amount"];
	$_ENV["request.trans"]["paymentTransID"] = $_POST["transId"];
	// take action based on response from worldpay (Y|C|unknown)
	switch ($_POST["transstatus"]) {
		// if order is VERIFIED 
		case "Y":
			$_ENV["request.trans"]["paymentStatus"] = 'approved';
			// QUERY: check for duplicate transaction (payment) 
			$transQuery = CWqueryGetTransaction($_ENV["request.trans"]["paymentTransID"]);
			// if Duplicate Transaction exists, we have a duplicate post 
			if ($transQuery["totalRows"] > 0) {
				// set error message 
				if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
				$_ENV["request.trans"]["errorMessage"] .= 'Duplicate Payment - Verify Order Balance With Merchant';
				// if Not a Duplicate 
			} else {
				// QUERY: Verify Order Exists by ID ('cartid' from worldpay) 
				$orderQuery = CWquerySelectOrderDetails($_ENV["request.trans"]["orderID"]);
				// if order is valid 
				if ($orderQuery["totalRows"]) {
					// verify payment amount not greater than order total 
					if ($orderQuery["order_total"][0] >= $_ENV["request.trans"]["paymentamount"]) {
						// payment is ok, save payment 
						try {
							// QUERY: insert payment to database,
							//returns payment id if successful, or 0-based message if not
							//
							$insertedPayment = CWsavePayment(
										$_ENV["request.trans"]["orderID"],
										$settings["methodname"],
										$settings["methodtype"],
										$_ENV["request.trans"]["paymentamount"],
										'approved',
										$_ENV["request.trans"]["paymentTransID"],
										$_ENV["request.trans"]["paymentTransResponse"]
										);
							// if an error is returned 
							if (substr($insertedPayment,0,2) == '0-') {
								if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
								$_ENV["request.trans"]["errorMessage"] .= 'Payment Insertion Error: '.substr($insertedPayment,2);
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
									$confirmationResponse = CWsendMail($mailContent, 'Payment Confirmation',$orderQuery["customer_email"][0]);
									// SEND EMAIL TO MERCHANT 
									$merchantMailContent = "An payment has been processed at ".$_ENV["application.cw"]["companyName"]."
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
				// /end if order is valid 
			}
			// /end duplicate yes/no 
			break;
		// /end value=verified 

		// if order is INVALID 
		case "C":
			if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
			$_ENV["request.trans"]["errorMessage"] .= 'Authorization Failure - Unable to verify payment.';
			$_ENV["request.trans"]["paymentStatus"] = 'denied';
			break;
		// /end value=invalid 

		// if order is UNKNOWN 
		default:
			if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
			$_ENV["request.trans"]["errorMessage"] .= 'Authorization Error - Unknown payment status response.';
			$_ENV["request.trans"]["paymentStatus"] = 'none';
			break;
		// /end value=unknown 
	}
	// /end switch for worldpay response 
	// IF ERRORS: notify admin of any errors 
	if (strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
		$mailContent = "The following errors were reported while processing an attempted WorldPay payment:".chr(13)."
===
".chr(13)."
Order: ".$_ENV["request.trans"]["orderID"].chr(13)."
Transaction ID:".$_ENV["request.trans"]["paymentTransID"].chr(13)."
===
".chr(13)."
";
		$emArr = $_ENV["request.trans"]["errorMessage"];
		if (!is_array($emArr) && strlen($emArr)) $emArr = explode(",", $emArr);
		else if (!is_array($emArr)) $emArr = array();
		foreach ($emArr as $key => $ee) {
			$mailContent .= $ee."
".chr(13)."
";
		}
		$mailContent .= "
===
".chr(13).
$_ENV["request.trans"]["paymentTransResponse"]."
===
".chr(13)."
Order Details:
".chr(13)."
".CWtextOrderDetails($_ENV["request.trans"]["orderID"]);
		// send the error message to the site admin 
		if (isset($_ENV["application.cw"]["developerEmail"]) && isValidEmail($_ENV["application.cw"]["developerEmail"])) {
			$confirmationResponse = CWsendMail($mailContent, 'WorldPay Processing Error',$_ENV["application.cw"]["developerEmail"]);
		}
		// /end send email 
	}
	// /end if errors 
	// prevent processor callback from triggering further response from our page 
	die();
	// /END PROCESS MODE 
}