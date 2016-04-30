<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-auth-sagepay.php
File Date: 2012-07-09
Description: SagePay payment processing

NOTE: Setting up accounts and integrating with third party processors is not
a supported feature of Cartweaver. For information and support concerning
payment processors contact the appropriate processor tech support web site or
personnel. Cartweaver includes this integration code as a courtesy with no
guarantee or warranty expressed or implied. Payment processors may make changes
to their protocols or practices that may affect the code provided here.
If so, updates and modifications are the sole responsibility of the user.

SAGEPAY OPTIONS are documented in the Form Protocol And Integration Guidelines from SagePay
================================================================
RETURN VARIABLES: paymenttransid, paymenttransresponse, paymentstatus
Values: Transaction ID, Gateway Response Message, Payment Status (approved|denied|none(no response))
These are returned to the containing page or template
in the request.trans scope, e.g. '$_ENV["request.trans"]["paymentTransID"]'
*/
// /// CONFIGURATION / SETUP /// 
// CWauth Payment Configuration 
// USER SETTINGS  [ START ] ==================================================== 
// ============================================================================= 
// SAGEPAY SETTINGS 
// Enter SagePay Vendor Name
global $caller;
if (!isset($settings)) $settings = array();
$settings["sagepayVendorName"] = "xxxxxxxxxxxx";
// Enter Encryption Key 
$settings["encryptionPassword"] = "xxxxxxxxxxxx";
// Select Transaction Mode (simulator|test| live) 
$settings["transactionMode"] = 'test';
// Enter SagePay Simulator Vendor Name (optional) 
$settings["sagepaySimulatorVendorName"] = "xxxxxxxxxxxx";
// Enter SagePay Simulator Encryption Key (optional) 
$settings["simulatorEncryptionPassword"] = "xxxxxxxxxxxx";
// DEV NOTE: simulator account password is '1muswqqo' 
// Description of Transaction shown to customer on SagePay site 
$settings["transactionDescription"] = $_ENV["application.cw"]["companyName"] . ' Purchase';
// Send SagePay Transaction Emails (0 = none, 1 = customer & vendor, 2 = vendor only) 
$settings["transactionEmail"] = '1';
// Message to customer inserted into successful transaction emails 
$settings["emailHeader"] = "Thank you for your order. Contact ".$_ENV["application.cw"]["companyName"]." at ".$_ENV["application.cw"]["companyEmail"]." if you have any questions about this order.";
// Select Currency (GBP|EUR|USD) 
$settings["currency"] = 'GBP';
// Auto Submit SagePay form (turn off for debugging form values) 
$settings["autoSubmit"] = true;
// SagePay AVS/CV2, default 0 (0|1|2|3) 
$settings["AVSmode"] = 0;
// SagePay 3DSecure, default 0 (0|1|2|3)
$settings["3DsecureMode"] = 0;
// SagePay Simulator/Test/Live URLS 
if ($settings["transactionMode"] == 'simulator') {
	$settings["authUrl"] = 'https://test.sagepay.com/Simulator/VSPFormGateway.asp';
	$settings["sagepayVendorName"] = $settings["sagepaySimulatorVendorname"];
	$settings["encryptionPassword"] = $settings["simulatorEncryptionPassword"];
} else if ($settings["transactionMode"] == 'test') {
	$settings["authUrl"] = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
} else {
	$settings["authUrl"] = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
}
// OPTIONAL PAYMENT METHOD VARIABLES 
// method image: an optional logo url (relative or full url) associated with this payment option at checkout
//  Note: be sure to use https prefix if linking to remote image 
$settings["methodImg"] = '';
// shippay message: optional message shown to customer on shipping selection 
$settings["methodSelectMessage"] = 'Pay with SagePay';
// submit message: optional message shown to customer on final order submission 
$settings["methodSubmitMessage"] = 'Click to pay with SagePay';
// confirm message: optioal message shown to customer after order is approved 
$settings["methodConfirmMessage"] = 'SagePay transaction complete';
// SUBMIT TEXT 
// submit button value 
$settings["submitText"] = '&raquo;&nbsp;Click to Pay with SagePay';
// processing/loading message 
$settings["loadingText"] = 'Submitting to SagePay...';
// ============================================================================ 
// USER SETTINGS [ END ] ====================================================== 
// METHOD SETTINGS : do not change 
// method name: the user-friendly name shown for this payment option at checkout 
$settings["methodName"] = 'SagePay UK';
// method type: processor (off-site processing), gateway (integrated credit card form) 
$settings["methodType"] = 'processor';
// key field: transaction variable specific to this payment method 
$settings["methodTransKeyField"] = "\$_GET['crypt']";
// notification URLs (callback transactions) 
$settings["cancelURL"] = $_ENV["application.cw"]["appSiteUrlHttp"] . $_ENV["application.cw"]["appCWStoreRoot"] . $_ENV["application.cw"]["appPageConfirmOrder"] . '?mode=cancel';
$settings["confirmURL"] = $_ENV["application.cw"]["appSiteUrlHttp"] . $_ENV["application.cw"]["appCWStoreRoot"] . $_ENV["application.cw"]["appPageConfirmOrder"] . '?mode=confirm';
$settings["returnURL"] = $_ENV["application.cw"]["appSiteUrlHttp"] . $_ENV["application.cw"]["appCWStoreRoot"] . $_ENV["application.cw"]["appPageConfirmOrder"] . '?mode=return';
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
// crypt functions for encryption/decryption of order data 
if ($auth_settings["auth_mode"] == 'capture' || $auth_settings["auth_mode"] == 'process') {
	if (!function_exists("simpleXor")) {
	function simpleXor($crypt, $key) {
		$output = "";
		$result = 0;
		if (strlen($crypt) != 0) {
			for ($i=1; $i<strlen($crypt); $i++) {
				$output .= ($crypt{$i}^$key{$result});
				$result += 1;
				if ($result == strlen($key)) {
					$result = 0;
				}
			}
		}
		return $output;
	}
	}

	if (!function_exists("base64Decode")) {
	function base64Decode($scrambled) {
		return base64_decode(str_replace(" ","+",$scrambled));
	}
	}
}
// CONFIG MODE 
// CONFIG DATA : Auth_Mode "config" 
// CONFIG MODE 
// if called as in 'config mode', provide configuration data as 'CWauthMethod' struct
if (isset($auth_settings["auth_mode"]) and $auth_settings["auth_mode"] == 'config') {
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
		if (!isset($tdata["customerCompany"])) $tdata["customerCompany"] = "";
		if (!isset($tdata["customerShipAddress1"])) $tdata["customerShipAddress1"] = "";
		if (!isset($tdata["customerShipAddress2"])) $tdata["customerShipAddress2"] = "";
		if (!isset($tdata["customerShipCity"])) $tdata["customerShipCity"] = "";
		if (!isset($tdata["customerShipState"])) $tdata["customerShipState"] = "";
		if (!isset($tdata["customerShipZip"])) $tdata["customerShipZip"] = "";
		if (!isset($tdata["customerShipCountry"])) $tdata["customerShipCountry"] = "";
		// order/payment 
		if (!isset($tdata["orderID"])) $tdata["orderID"] = "";
		if (!isset($tdata["paymentAmount"])) $tdata["paymentAmount"] = 0;
		// SagePay 'crypt' combines all data into a single field 
		$tdata["combined"] = 'VendorTxCode=' . $tdata["orderID"];
		$tdata["combined"] .= '&Amount=' . number_format($tdata["paymentAmount"],2,".","");
		$tdata["combined"] .= '&Currency=' . $settings["currency"];
		$tdata["combined"] .= '&Description=' . $settings["transactionDescription"];
		$tdata["combined"] .= '&SuccessURL=' . $settings["confirmURL"];
		$tdata["combined"] .= '&FailureURL=' . $settings["cancelURL"];
		$tdata["combined"] .= '&CustomerName=' . $tdata["customerNameFirst"] . ' ' . $tdata["customerNameLast"];
		$tdata["combined"] .= '&CustomerEmail=' . $tdata["customerEmail"];
		$tdata["combined"] .= '&VendorEmail=' . $_ENV["application.cw"]["companyEmail"];
		$tdata["combined"] .= '&SendEmail=' . $settings["transactionEmail"];
		$tdata["combined"] .= '&eMailMessage=' . $settings["emailHeader"];
		$tdata["combined"] .= '&BillingSurname=' . $tdata["customerNameLast"];
		$tdata["combined"] .= '&BillingFirstNames=' . $tdata["customerNameFirst"];
		$tdata["combined"] .= '&BillingAddress1=' . $tdata["customerAddress1"];
		$tdata["combined"] .= '&BillingAddress2=' . $tdata["customerAddress2"];
		$tdata["combined"] .= '&BillingCity=' . $tdata["customerCity"];
		$tdata["combined"] .= '&BillingPostCode=' . $tdata["customerZip"];
		$tdata["combined"] .= '&BillingCountry=' . $tdata["customerCountry"];
		$tdata["combined"] .= '&BillingPhone=' . $tdata["customerPhone"];
		$tdata["combined"] .= '&DeliverySurname=' . $tdata["customerNameLast"];
		$tdata["combined"] .= '&DeliveryFirstNames=' . $tdata["customerNameFirst"];
		$tdata["combined"] .= '&DeliveryAddress1=' . $tdata["customerShipAddress1"];
		$tdata["combined"] .= '&DeliveryAddress2=' . $tdata["customerShipAddress2"];
		$tdata["combined"] .= '&DeliveryCity=' . $tdata["customerShipCity"];
		$tdata["combined"] .= '&DeliveryPostCode=' . $tdata["customerShipZip"];
		$tdata["combined"] .= '&DeliveryCountry=' . $tdata["customerShipCountry"];
		$tdata["combined"] .= '&ApplyAVSCV2=' . $settings["AVSmode"];
		$tdata["combined"] .= '&Apply3DSecure=' . $settings["3DsecureMode"];
		$tdata["crypt"] = simpleXor($tdata["combined"],$settings["encryptionPassword"]);
	}
	// /end if data ok 
	// sagepay transaction form with hidden inputs 
?>
		<form action="<?php echo trim($settings["authUrl"]); ?>" id="CWformSagePayProcess" method="post">
			<div>
				<input type="hidden" name="VPSProtocol" value="2.23">
				<input type="hidden" name="TxType" value="PAYMENT">
				<input type="hidden" name="Vendor" value="<?php echo $settings["sagepayVendorName"]; ?>">
				<input type="hidden" name="Crypt" value="<?php echo base64_encode($tdata["crypt"]); ?>">
			</div>
			<div>
				<?php // submit button ?>
				<div class="center top40 bottom40">
				<input name="submit" id="sagepay_submit" type="submit" class="CWformButton" value="<?php echo $settings["submitText"]; ?>">
				<?php // submit link : javascript replaces button with this link ?>
				<a style="display:none;" href="#" class="CWcheckoutLink" id="CWlinkAuthSubmit"><?php echo $settings["submitText"]; ?></a>
				<br>
					<div style="display:none;" id="spLoading">
						<img style="margin-top:35px;" src="<?php echo $_ENV["request.cw"]["assetSrcDir"] ?>css/theme/cw-loading-wide.gif" height="15" width="128">
					</div>
				</div>
			</div>
		</form>
<?php
	// javascript for form display & function 
	$spFormjs = "
	<script type=\"text/javascript\">
	jQuery(document).ready(function(){
		// hide standard button
		jQuery('#sagepay_submit').hide();
		// show link, change text
		jQuery('#CWlinkAuthSubmit').text('".$settings["loadingText"]."').show();
		// add loading graphic below button
		jQuery('#spLoading').show();
		// clicking link submits form
		jQuery('#CWlinkAuthSubmit').click(function(){
			jQuery('#sagepay_submit').trigger('click');
		});
		// auto submit the form by triggering a click of the submit button
		";
	if ($settings["autoSubmit"]) {
		$spFormjs .= "
		jQuery('#sagepay_submit').delay('300').trigger('click');";
	}
	$spFormjs .= "
	});
	</script>
	";
	CWinsertHead($spFormjs);
	// /end CAPTURE mode 

// PROCESS MODE 
// PROCESS MODE (form post from PayPal IPN) 
// PROCESS MODE 
// verify form post with txn_id from paypal, and no variables in url 
} else if (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == 'process') {
	// function to parse response 
	if (!function_exists("getSagePayResponse")) {
	function getSagePayResponse($crypt) {
		$structResult = array();
		$listArgs = explode("&", $crypt);
		foreach ($listArgs as $key => $r) {
			$splitArg = explode("=", $r);
			$structResult[$splitArg[0]] = $splitArg[sizeof($splitArg)-1];
		}
		return $structResult;
	}
	}
	// read response from SagePay 
	if (!isset($_GET["crypt"])) $_GET["crypt"] = "";
	$_ENV["request.trans"]["paymentTransResponse"] = base64Decode($_GET["crypt"]);
	// add heading to response data 
	$_ENV["request.trans"]["paymentTransResponse"] = 'SagePay Returned Values: ' . chr(13) . '===' . chr(13) . $_ENV["request.trans"]["paymentTransResponse"];
	// set values into page request 
	if (!isset($_ENV["request.trans"]["paymentData"]) || !is_array($_ENV["request.trans"]["paymentData"])) $_ENV["request.trans"]["paymentData"] = array();
	$_ENV["request.trans"]["paymentData"] = getSagePayResponse(simpleXor(base64Decode($_GET["crypt"]),$settings["encryptionPassword"]));
	if (!isset($_ENV["request.trans"]["paymentData"]["VPSTxId"])) $_ENV["request.trans"]["paymentData"]["VPSTxId"] = "";
	$_ENV["request.trans"]["orderID"] = $_ENV["request.trans"]["paymentData"]["VendorTxCode"];
	$_ENV["request.trans"]["paymentAmount"] = $_ENV["request.trans"]["paymentData"]["Amount"];
	$_ENV["request.trans"]["paymentTransID"] = $_ENV["request.trans"]["paymentData"]["VPSTxId"];
	$_ENV["request.trans"]["paymentStatus"] = $_ENV["request.trans"]["paymentData"]["Status"];
	// take action based on response from http request (VERIFIED|INVALID)
	$responseStr = $_ENV["request.trans"]["paymentStatus"];
	switch (strtoupper($responseStr)) {
		// if order is OK 
		case "OK":
			// QUERY: check for duplicate transaction (payment) 
			$transQuery = CWqueryGetTransaction($_ENV["request.trans"]["paymentTransID"]);
			// if Duplicate Transaction exists, we have a duplicate post 
			if ($transQuery["totalRows"] > 0) {
				// set error message 
				if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
				$_ENV["request.trans"]["errorMessage"] .= 'Duplicate Payment - Verify Order Balance With Merchant';
			// if Not a Duplicate 
			} else {
				// QUERY: Verify Order Exists by ID 
				$orderQuery = CWquerySelectOrderDetails($_ENV["request.trans"]["orderID"]);
				// if order is valid 
				if ($orderQuery["totalRows"] == 1) {
					// verify payment amount not greater than order total 
					if ($orderQuery["order_total"][0] >= $_ENV["request.trans"]["paymentAmount"]) {
						// payment is ok, save payment 
						try {
							// QUERY: insert payment to database,
							// returns payment id if successful, or 0-based message if not
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
									$updateOrderStatus = CWqueryUpdateOrder($_ENV["request.trans"]["orderID"],2);
									if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
									$_ENV["request.trans"]["errorMessage"] .= 'Balance of '.cartweaverMoney($_ENV["request.trans"]["orderBalance"]).' due';
								}
								// /end balance due check 
							}
							// /end insertion error check 
						} catch (Exception $e) {
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
		// /end value=OK 

		// if order is INVALID 
		default:
			if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
			$_ENV["request.trans"]["errorMessage"] .= 'Invalid SagePay Transaction - Payment Not Completed';
			break;
		// /end value=invalid 
	}
	// /end switch for sagepay status 

	// IF ERRORS: notify admin of any errors 
	if (strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
		$mailContent = "The following errors were reported while processing an attempted SagePay payment:".chr(13)."
===".chr(13)."
Order: ".$_ENV["request.trans"]["orderID"].chr(13)."
Transaction ID: ".$_ENV["request.trans"]["paymentTransID"].chr(13)."
===".chr(13);
		$messagesArr = explode(",", $_ENV["request.trans"]["errorMessage"]);
		foreach ($messagesArr as $key => $ee) {
			$mailContent .= "
".$ee.chr(13);
		}
		$mailContent .= "
===".chr(13)."
".$_ENV["request.trans"]["paymentTransResponse"]."
===".chr(13)."
Order Details:".chr(13)."
".CWtextOrderDetails($_ENV["request.trans"]["orderID"]);
		// send the error message to the site admin 
		if (isset($_ENV["application.cw"]["developerEmail"]) && isValidEmail($_ENV["application.cw"]["developerEmail"])) {
			$confirmationResponse = CWsendMail($mailContent, 'SagePay Processing Error', $_ENV["application.cw"]["developerEmail"]);
		}
		// /end send email 
	}
	// /end if errors 
	header("Location: ".$settings["returnURL"]);
	// /END PROCESS MODE 
}