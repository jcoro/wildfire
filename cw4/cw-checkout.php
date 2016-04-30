<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-checkout.php
File Date: 2012-06-05
Description: displays multi-step checkout process
Variable "request.cwpage.currentStep" controls which content is visible to the user,
and triggers loading of the current step
Variable "request.cwpage.postToProcessor" controls action in final step.
- If true, this means order has been submitted using an external processor (e.g. PayPal),
and triggers submission of the processor's payment form, sending the customer to the processor for payment.
- Default is false, which shows the submit order form and submit order button.
Payment processing files are stored in cwapp/auth/ and are read dynamically into the application memory.
The payment method(s) available are selectable in the Cartweaver admin.
Specific code within each processing file contains values specific to that payment method or Gateway,
including messages shown to the user during the checkout process, and an optional logo or image for each method.
STEP IDS are used to target active steps in the process
Step 1 (#step1) - select new account or login
Step 2 (#step2) - address/user/billing/shipping info
Step 3 (#step3) - select shipping method, show selected method
Step 4 (#step4) - review and confirm order
Step 5 (#step5) - select payment method (if applicable) and/or submit payment
- also handles gateway responses, and secondary submission to processor if needed
==========================================================
*/
// set headers to prevent browser cache issues 
$gmt = date("Z") / (60*60);
if ($gmt == 0) {
	$gmt = "";
} else if ($gmt > 0) {
	$gmt = "+".$gmt;
}
header("Expires: ".date("D, d m Y H:i:s")." GMT".$gmt);
header("Pragma: no-cache");
header("Cache-Control: no-cache, no-store, proxy-revalidate, must-revalidate");
// customerID needed to handle account / billing 
if (!isset($_SESSION["cwclient"]["cwCustomerID"])) $_SESSION["cwclient"]["cwCustomerID"] = 0;
// default totals used between checkout steps 
if (!isset($_SESSION["cwclient"]["cwShipTotal"])) $_SESSION["cwclient"]["cwShipTotal"] = 0;
if (!isset($_SESSION["cwclient"]["cwOrderTotal"])) $_SESSION["cwclient"]["cwOrderTotal"] = 0;
// customer name for 'logged in as' link 
if (!isset($_SESSION["cwclient"]["cwCustomerName"])) $_SESSION["cwclient"]["cwCustomerName"] = "";
// var for type of checkout = guest / account 
if (!isset($_SESSION["cwclient"]["cwCustomerCheckout"])) $_SESSION["cwclient"]["cwCustomerCheckout"] = "account";
// errors from forms being submitted 
if (!isset($_ENV["request.cwpage"]["formErrors"])) $_ENV["request.cwpage"]["formErrors"] = array();
// form and link actions 
if (!isset($_ENV["request.cwpage"]["hrefUrl"])) $_ENV["request.cwpage"]["hrefUrl"] = trim($_ENV["application.cw"]["appCWStoreRoot"]).$_ENV["request.cw"]["thisPage"];
// confirm and submit page url 
if (!isset($_ENV["request.cwpage"]["placeOrderUrl"])) $_ENV["request.cwpage"]["placeOrderUrl"] = trim($_ENV["application.cw"]["appCWStoreRoot"]).$_ENV["request.cw"]["thisPage"];
// if order has been placed, hide initial steps 
if (!isset($_ENV["request.cwpage"]["orderFinal"])) $_ENV["request.cwpage"]["orderFinal"] = false;
// if true, order has been placed, show processor payment form in final step 
if (!isset($_ENV["request.cwpage"]["postToProcessor"])) $_ENV["request.cwpage"]["postToProcessor"] = false;
// show shipping (can be turned off with additional logic such as single method) 
if (!isset($_ENV["request.cwpage"]["shipDisplay"])) $_ENV["request.cwpage"]["shipDisplay"] = $_ENV["application.cw"]["shipEnabled"];
// default payment type 
if (!isset($_SESSION["cw"]["authType"])) $_SESSION["cw"]["authType"] = "";
// customer message entered with order 
if (!isset($_SESSION["cw"]["order_message"])) $_SESSION["cw"]["order_message"] = "";
if (!isset($_POST["order_message"])) $_POST["order_message"] = $_SESSION["cw"]["order_message"];
if (!isset($_ENV["request.cwpage"]["orderMessage"])) $_ENV["request.cwpage"]["orderMessage"] = $_POST["order_message"];
if (!isset($_SESSION["cwclient"]["cwCustomerID"]) || !strlen(trim($_SESSION["cwclient"]["cwCustomerID"]))) $_SESSION["cwclient"]["cwCustomerID"] = 0;
if (!isset($_SESSION["cwclient"]["cwCompleteOrderID"])) $_SESSION["cwclient"]["cwCompleteOrderID"] = 0;
// ignore payment, used for 0 balance orders 
if (!isset($_ENV["request.cwpage"]["bypassPayment"])) $_ENV["request.cwpage"]["bypassPayment"] = false;
if (!strlen(trim($_SESSION["cwclient"]["cwCustomerID"]))) {
	$_SESSION["cwclient"]["cwCustomerID"] = 0;
}
$myDir = getcwd();
chdir(dirname(__FILE__));
// clean up form and url variables 
include("cwapp/inc/cw-inc-sanitize.php");
// CARTWEAVER REQUIRED FUNCTIONS 
include("cwapp/inc/cw-inc-functions.php");
chdir($myDir);
// discount defaults 
if (!isset($_SESSION["cwclient"]["discountPromoCode"])) $_SESSION["cwclient"]["discountPromoCode"] = "";
if (!isset($_SESSION["cwclient"]["discountApplied"])) $_SESSION["cwclient"]["discountApplied"] = "";
// IF ORDER IS COMPLETE AND PAID IN FULL, send to confirmation page 
if (isset($_SESSION["cwclient"]["cwCompleteOrderID"]) && $_SESSION["cwclient"]["cwCompleteOrderID"] != 0 && CWorderStatus($_SESSION["cwclient"]["cwCompleteOrderID"]) > 2) {
	header("Location: ".$_ENV["request.cwpage"]["urlConfirmOrder"]."?orderid=".$_SESSION["cwclient"]["cwCompleteOrderID"]);
	exit;
// IF ORDER IS COMPLETE but not paid in full, disable all but final step, change 'submit' button text 
} else if (isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0" && isset($_SESSION["cwclient"]["cwCompleteOrderID"]) && $_SESSION["cwclient"]["cwCompleteOrderID"] != 0 && isset($_SESSION["cw"]["confirmOrder"]) && $_SESSION["cw"]["confirmOrder"]) {
	$_ENV["request.cwpage"]["orderFinal"] = true;
	$_ENV["request.cwpage"]["currentStep"] = 5;
	$_ENV["request.cwpage"]["submitValue"] = 'Submit Payment&nbsp;&raquo;';
	// if order is not complete, use default text 
} else {
	$_ENV["request.cwpage"]["submitValue"] = 'Place Order&nbsp;&raquo;';
}
// if authreset exists in url, remove marker for selection 
$authMethodsArr = $_ENV["application.cw"]["authMethods"];
if (!is_array($authMethodsArr) && strlen($authMethodsArr)) $authMethodsArr = explode(",", $authMethodsArr);
else if (!is_array($authMethodsArr)) $authMethodsArr = array();
if (isset($_GET["authreset"]) && $_GET["authreset"] == 1 && sizeof($authMethodsArr) > 1) {
	$_SESSION["cw"]["authPref"] = 0;
	$_SESSION["cw"]["confirmAuthPref"] = false;
	$_SESSION["cw"]["confirmOrder"] = false;
}
// PROCESS ORDER 
// PROCESS ORDER 
// PROCESS ORDER 
// if error set by confirmation page (e.g. balance due), add to transaction alerts for final step 
if (isset($_SESSION["cw"]["paymentAlert"]) && strlen(trim($_SESSION["cw"]["paymentAlert"]))) {
	if (!isset($_ENV["request.trans"])) $_ENV["request.trans"] = array();
	if (!isset($_ENV["request.trans"]["errorMessage"])) $_ENV["request.trans"]["errorMessage"] = "";
	//if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] = ",".$_ENV["request.trans"]["errorMessage"];
	$_ENV["request.trans"]["errorMessage"] = $_SESSION["cw"]["paymentAlert"].$_ENV["request.trans"]["errorMessage"];
}
// IF SUBMITTED: check for hidden field in order submission form, should match id of client submitting order 
if (isset($_POST["customer_submit_conf"]) && $_POST["customer_submit_conf"] == $_SESSION["cwclient"]["cwCustomerID"]) {
	// get cart structure 
	$processcart = CWgetCart();
	// only process a valid cart - if order has been placed and page is refreshed, or user comes 'back' from offsite gateway, no cart totals will be available 
	// NOTE: when auto-confirm is enabled, user will be taken to 'no payment applied' screen and order will remain status 'pending' 
	if (isset($processcart["carttotals"]["total"]) && is_numeric($processcart["carttotals"]["total"])) {
		// shipping totals 
		if (isset($_SESSION["cwclient"]["cwShipCountryID"]) && $_SESSION["cwclient"]["cwShipCountryID"] > 0 && $_ENV["application.cw"]["shipEnabled"]) {
			if (isset($_SESSION["cw"]["confirmShipID"]) && $_SESSION["cw"]["confirmShipID"] > 0) {
				// if we don't have a valid rate stored yet 
				if (!(isset($_SESSION["cwclient"]["cwShipTotal"]) && $_SESSION["cwclient"]["cwShipTotal"] > 0)) {
					$shipVal = CWgetShipRate(
									$_SESSION["cw"]["confirmShipID"],
									$_SESSION["cwclient"]["cwCartID"]);									
				} else {
					$shipVal = $_SESSION["cwclient"]["cwShipTotal"];
				}
			} else {
				$shipVal = 0;
			}
			// reset value of client var 
			if (is_numeric($shipVal)) {
				$_SESSION["cwclient"]["cwShipTotal"] = $shipVal;
			} else {
				$_SESSION["cwclient"]["cwShipTotal"] = 0;
			}
			// set cart shipping total 
			if (is_numeric($shipVal)) {
				$processcart["carttotals"]["shipping"] = number_format($shipVal, 2);
			} else {
				$processcart["carttotals"]["shipping"] = 0;
			}
			// /end shipping total 
			// shipping tax 
			if ($_ENV["application.cw"]["taxChargeOnShipping"] && strtolower($_ENV["application.cw"]["taxCalctype"]) == 'localtax') {
				$shipTaxVal = CWgetShipTax(
									$_SESSION["cwclient"]["cwShipCountryID"],
									$_SESSION["cwclient"]["cwShipRegionID"],
									$processcart["carttotals"]["shipping"],
									$processcart);
			} else if (isset($_ENV["request.cwpage"]["cartShipTaxTotal"])) {
				$shipTaxVal = $_ENV["request.cwpage"]["cartShipTaxTotal"];
			} else {
				$shipTaxVal = 0;
			}
			$processcart["carttotals"]["shippingTax"] = number_format($shipTaxVal, 2, ".", "");
			// /end shipping tax 
			// add shipping amounts to cart total 
			$processcart["carttotals"]["total"] += $processcart["carttotals"]["shipping"] + $processcart["carttotals"]["shippingTax"];
		}
		// /end shipping totals 
		// process the order, passing in the cart contents 
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		$module_settings = array(
			"cart" => $processcart,
			"form_data" => $_POST);
		include("cwapp/mod/cw-mod-orderprocess.php");
		unset($module_settings);
		chdir($myDir);
		// if errors are returned by order processing 
		if (isset($_ENV["request.trans"]["errorMessage"]) && strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
			$_ENV["request.cwpage"]["currentStep"] = 5;
		}
		// /end processing errors 
		// if errors are returned by form validation 
		if (isset($_ENV["request.trans"]["formErrors"]) && count($_ENV["request.trans"]["formErrors"])) {
			$_ENV["request.cwpage"]["formErrors"] = $_ENV["request.trans"]["formErrors"];
			if (!is_array($_ENV["request.cwpage"]["formErrors"])) $_ENV["request.cwpage"]["formErrors"] = explode(",", trim($_ENV["request.cwpage"]["formErrors"]));
			$_ENV["request.cwpage"]["currentStep"] = 5;
		}
		// /end form errors 
		// if using processor payment type, or if order was inserted, this will be 'true' from the orderprocess function above 
		if (isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0" && ((isset($_ENV["request.cwpage"]["postToProcessor"]) && $_ENV["request.cwpage"]["postToProcessor"]) || (isset($_ENV["request.trans"]["orderInserted"]) && $_ENV["request.trans"]["orderInserted"]))) {
			// set order as final, to hide initial content while still showing step headings 
			$_ENV["request.cwpage"]["orderFinal"] = true;
			$_ENV["request.cwpage"]["currentStep"] = 5;
		}
	// if cart is invalid 
	} else {
		// redirect user to show cart page 
		header("Location: ".$_ENV["request.cwpage"]["urlShowCart"]);
		exit;
	}
}
// /END PROCESS ORDER 
// step-approval defaults 
if (!isset($_SESSION["cw"]["confirmAddress"])) $_SESSION["cw"]["confirmAddress"] = false;
if (!isset($_SESSION["cw"]["confirmShip"])) $_SESSION["cw"]["confirmShip"] = false;
if (!isset($_SESSION["cw"]["confirmAuthPref"])) $_SESSION["cw"]["confirmAuthPref"] = false;
if (!isset($_SESSION["cw"]["confirmCart"])) $_SESSION["cw"]["confirmCart"] = false;
// client order confirmed is set to true below, *after* both shipping and payment options have been set 
if (!isset($_SESSION["cw"]["confirmOrder"])) $_SESSION["cw"]["confirmOrder"] = false;
// if cart is confirmed, mark in user's session 
if (isset($_GET["cartconfirm"]) && $_GET["cartconfirm"] == 1) {
	$_SESSION["cw"]["confirmCart"] = true;
}
// PROMO CODES 
if (isset($_POST["promocode"])) {
	// remove marker for order confirmed in checkout 
	$_SESSION["cw"]["confirmCart"] = false;
}
//VERIFY AT LEAST ONE CHECKOUT METHOD EXISTS: bypassed in 'test mode' 
if  (!(empty($_ENV["application.cw"]["authMethods"])		 && !(isset($_ENV["application.cw"]["appTestModeEnabled"]) && $_ENV["application.cw"]["appTestModeEnabled"]))) {	
// confirm payment selection from client memory (in case of repeat payments) 
$authMethodsArr = $_ENV["application.cw"]["authMethods"];
if (!is_array($authMethodsArr) && strlen($authMethodsArr)) $authMethodsArr = explode(",", $authMethodsArr);
else if (!is_array($authMethodsArr)) $authMethodsArr = array();
if ((!isset($_SESSION["cw"]["authPref"]) || $_SESSION["cw"]["authPref"] == 0) &&
	(isset($_SESSION["cwclient"]["cwCustomerAuthPref"]) && $_SESSION["cwclient"]["cwCustomerAuthPref"] > 0) &&
	(isset($_SESSION["cwclient"]["cwCustomerAuthPref"]) && in_array($_SESSION["cwclient"]["cwCustomerAuthPref"], $authMethodsArr))
	&& ($_SESSION["cwclient"]["cwOrderTotal"] + $_SESSION["cwclient"]["cwShipTotal"] > 0)) {
	// set in session memory, mark confirmed 
	$_SESSION["cw"]["authPref"] = $_SESSION["cwclient"]["cwCustomerAuthPref"];
	$_SESSION["cw"]["confirmAuthPref"] = true;
	// if method already set 
} else if (isset($_SESSION["cw"]["authPref"]) && isset($_SESSION["cwclient"]["cwCustomerAuthPref"]) && $_SESSION["cw"]["authPref"] == $_SESSION["cwclient"]["cwCustomerAuthPref"] && in_array($_SESSION["cwclient"]["cwCustomerAuthPref"], $authMethodsArr)) {
	$_SESSION["cw"]["confirmAuthPref"] = true;
	// if only one method exists 
} else if (isset($_ENV["application.cw"]["authMethods"]) && sizeof($authMethodsArr) == 1
	&& ($_SESSION["cwclient"]["cwOrderTotal"] + $_SESSION["cwclient"]["cwShipTotal"] > 0)) {
	$_SESSION["cw"]["authPref"] = $authMethodsArr[0];
	$_SESSION["cw"]["confirmAuthPref"] = true;
	// set client variable for payment type 
	$_SESSION["cwclient"]["cwCustomerAuthPref"] = $_SESSION["cw"]["authPref"];
	// if no auth methods exist 
} else if (isset($_ENV["application.cw"]["authMethods"]) && sizeof($authMethodsArr) < 1
	|| ($_SESSION["cwclient"]["cwOrderTotal"] + $_SESSION["cwclient"]["cwShipTotal"] <= 0)) {
	$_SESSION["cw"]["authPref"] = 0;
	$_SESSION["cw"]["confirmAuthPref"] = true;
	// set client variable for no payment 
	$_SESSION["cwclient"]["cwCustomerAuthPref"] = 0;
} else {
	$_SESSION["cw"]["confirmAuthPref"] = false;
}
// if client login, address, shipping, cart and payment are all confirmed, mark orderconfirmed in session memory 
if (isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0" && $_SESSION["cw"]["confirmAddress"] && $_SESSION["cw"]["confirmShip"] && $_SESSION["cw"]["confirmCart"] && $_SESSION["cw"]["confirmAuthPref"]) {
	$_SESSION["cw"]["confirmOrder"] = true;
} else {
	$_SESSION["cw"]["confirmOrder"] = false;
}
// //CURRENT STEP:
//MANAGE AVAILABLE STEPS IN THE CHECKOUT PROCESS
//
// default first step 
if (!isset($_ENV["request.cwpage"]["currentStep"])) $_ENV["request.cwpage"]["currentStep"] = 1;
// if not logged in or using guest checkout, show first step 
if ($_ENV["application.cw"]["customerAccountEnabled"] && (!isset($_SESSION["cwclient"]["cwCustomerID"]) || $_SESSION["cwclient"]["cwCustomerID"] === 0 || $_SESSION["cwclient"]["cwCustomerID"] === "0") && !isset($_GET["account"]) && !$_SESSION["cw"]["confirmOrder"] && !isset($_POST["customer_email"])) {
	$_ENV["request.cwpage"]["currentStep"] = 1;
// if logged in, or submitting customer form, show second step 
} else if (((isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0") || isset($_POST["customer_email"])) && $_ENV["request.cwpage"]["currentStep"] <= 1) {
	$_ENV["request.cwpage"]["currentStep"] = 2;
// if accounts are not enabled, set client var, skip login step 
} else if (!$_ENV["application.cw"]["customerAccountEnabled"]) {
	$_SESSION["cwclient"]["cwCustomerCheckout"] = 'guest';
	// advance to step 2 
	if ($_ENV["request.cwpage"]["currentStep"] <= 1) {
		$_ENV["request.cwpage"]["currentStep"] = 2;
	}
// if accounts are enabled, but optional, and account=0/1 is in url 
} else if ($_ENV["application.cw"]["customerAccountEnabled"] && !$_ENV["application.cw"]["customerAccountRequired"] && isset($_GET["account"]) && ($_GET["account"] == 0 || $_GET["account"] == 1)) {
	// advance to step 2 
	if ($_ENV["request.cwpage"]["currentStep"] <= 1) {
		$_ENV["request.cwpage"]["currentStep"] = 2;
		// if selecting to turn accounts off 
		if (isset($_GET["account"]) && $_GET["account"] == 0) {
			$_SESSION["cwclient"]["cwCustomerCheckout"] = 'guest';
		}
	}
}
// if shipping is reset, show shipping step 
if (isset($_GET["shipreset"]) && $_GET["shipreset"] == 1) {
	$_ENV["request.cwpage"]["currentStep"] = 3;
	$_SESSION["cw"]["confirmShip"] = false;
	$_SESSION["cwclient"]["cwShipTotal"] = 0;
	// if shipping confirmed, show next step 
} else if ($_SESSION["cw"]["confirmShip"] && $_SESSION["cw"]["confirmAddress"] && $_ENV["request.cwpage"]["currentStep"] <= 4) {
	$_ENV["request.cwpage"]["currentStep"] = 4;
}
// if address is reset 
if (isset($_GET["custreset"]) && $_GET["custreset"] == 1) {
	$_ENV["request.cwpage"]["currentStep"] = 1;
	$_SESSION["cw"]["confirmAddress"] = false;
	// if address confirmed, show third step 
} else if (strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0" && $_SESSION["cw"]["confirmAddress"] && $_ENV["request.cwpage"]["currentStep"] <= 3) {
	$_ENV["request.cwpage"]["currentStep"] = 3;
}
// if address, shipping and cart are all confirmed 
if (isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0" && $_SESSION["cw"]["confirmShip"] && $_SESSION["cw"]["confirmAddress"] && $_SESSION["cw"]["confirmCart"]) {
	$_ENV["request.cwpage"]["currentStep"] = 5;
}
// if not using shipping, auto-advance to confirm order step
if ($_ENV["request.cwpage"]["shipDisplay"] != 1 && $_ENV["request.cwpage"]["currentStep"] == 3) {
	$_ENV["request.cwpage"]["currentStep"] = 4;
}	
// /END SET CURRENT STEP 
// GET CUSTOMER DETAILS 
$CWcustomer = CWgetCustomer($_SESSION["cwclient"]["cwCustomerID"]);
// GET CART 
if (isset($_SESSION["cwclient"]["cwCartID"]) && $_SESSION["cwclient"]["cwCartID"] != 0) {
	$cwcart = CWgetCart();
} else if (isset($_COOKIE["cwCartID"]) && $_COOKIE["cwCartID"] != 0) {
	$_SESSION["cwclient"]["cwCartID"] = $_COOKIE["cwCartID"];
	$cwcart = CWgetCart();
} else {
	$_SESSION["cwclient"]["cwCartID"] = 0;
	$cwcart = CWgetCart();
}
// if cart is empty, and order has not been completed, redirect user to show cart page 
if (sizeof($cwcart["cartitems"]) == 0 && !(isset($_SESSION["cwclient"]["cwCompleteOrderID"]) && $_SESSION["cwclient"]["cwCompleteOrderID"] != 0 && isset($cwcart["carttotals"]["total"]) && $cwcart["carttotals"]["total"] > 0)) {
	header("Location: ".$_ENV["request.cwpage"]["urlShowCart"]);
	exit;
}
// if cart total is 0, bypass payment
if ($cwcart["carttotals"]["total"] <= 0 && $_SESSION["cwclient"]["cwShipTotal"] <= 0) {
	$_SESSION["cw"]["authPref"] = 0;
	$_SESSION["cwclient"]["cwCustomerAuthPref"] = 0;
	$_ENV["request.cwpage"]["bypassPayment"] = true;
	// if other confirmations are set, confirm the order, allow submission 
	if ((isset($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "") &&
		$_SESSION["cw"]["confirmAddress"] &&
		$_SESSION["cw"]["confirmShip"] &&
		$_SESSION["cw"]["confirmCart"]) {
			$_SESSION["cw"]["confirmAuthPref"] = true;
			$_SESSION["cw"]["confirmOrder"] = true;
	}
}
// set up checkout steps in breadcrumb nav 
if ($_ENV["request.cwpage"]["orderFinal"]) {
	$checkoutSteps = "
		:
		<strong>Submit Payment</strong>
		";
} else {
	$checkoutSteps = "
		:
		";
	ob_start();
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	include("cwapp/inc/cw-inc-checkoutsteps.php");
	chdir($myDir);
	$checkoutSteps .= ob_get_contents();
	ob_end_clean();
}
// /////// START OUTPUT /////// 
// indicate current step of process (section opened on page load)
//note: javascript is 0-based (0 = first step) 
// breadcrumb navigation $myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "breadcrumb",
	"separator" => " &raquo; ",
	"end_label" => "Check Out ".$checkoutSteps,
	"all_categories_label" => "",
	"all_secondaries_label" => "",
	"all_products_label" => "");
include("cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
// show login form ?>
<div id="CWcheckout" class="CWcontent">
	<h1>Check Out</h1>
<?php
// LOGIN / NEW ACCOUNT: STEP 1 
if ($_ENV["application.cw"]["customerAccountEnabled"]) {
?>
		<div class="CWformSection" id="step1">
			<h3 class="CWformSectionTitle CWactiveSection">New / Returning Customer</h3>
<?php
	if (!$_ENV["request.cwpage"]["orderFinal"]) {
		// login section ?>
				<div class="CWstepSection">
<?php
		// if not logged in 
		if ((!isset($_SESSION["cwclient"]["cwCustomerID"]) || !isset($_SESSION["cwclient"]["cwCustomerType"]) || ((isset($_SESSION["cwclient"]["cwCustomerID"]) && (!strlen($_SESSION["cwclient"]["cwCustomerID"]) || $_SESSION["cwclient"]["cwCustomerID"] === 0 || $_SESSION["cwclient"]["cwCustomerID"] === "0")) || (isset($_SESSION["cwclient"]["cwCustomerType"]) && ($_SESSION["cwclient"]["cwCustomerType"] === 0 || $_SESSION["cwclient"]["cwCustomerType"] === "0")))) || (isset($_SESSION["cwclient"]["cwCustomerCheckout"]) && strtolower($_SESSION["cwclient"]["cwCustomerCheckout"]) == "guest")) {
			// NEW CUSTOMERS INFO 
?>
						<div class="halfLeft">
							<h3 class="CWformTitle">NEW CUSTOMERS: Enter Details Below to Check Out</h3>
							<div class="center top40">
<?php
			// if accounts are enabled but not required 
			if (!$_ENV["application.cw"]["customerAccountRequired"]) {
				// if resetting from url, create a link to switch back 
				if (isset($_GET["account"]) && $_GET["account"] == 0) {
?>
										<a id="CWlinkResetLogin" class="CWcheckoutLink" href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>?account=1" style="">Create Account&nbsp;&raquo;</a>
<?php
				} else {
					// this link shows the next step w/ no page reload (default) 
?>
										<a id="CWlinkSkipLogin" class="CWcheckoutLink" href="#" style="">Create Account&nbsp;&raquo;</a>
<?php
				}
?>
									&nbsp;&nbsp;<a id="CWlinkGuestLogin" class="CWcheckoutLink" href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>?account=0&logout=1" style="">Guest Checkout&nbsp;&raquo;</a>
<?php
				// if accounts are required 
			} else {
?>
									<a id="CWlinkSkipLogin" class="CWcheckoutLink" href="#" style="">Check Out&nbsp;&raquo;</a>
<?php
			}
?>
							</div>
						</div>
<?php
			// LOGIN FORM 
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			$module_settings = array(
				"form_heading" => "RETURNING CUSTOMERS: Log In");
			include("cwapp/mod/cw-mod-formlogin.php");
			unset($module_settings);
			chdir($myDir);
		// if logged in 
		} else if (isset($_SESSION["cwclient"]["cwCustomerName"]) && strlen(trim($_SESSION["cwclient"]["cwCustomerName"]))) {
?>
						<h3 class="CWformTitle">Welcome Back: Verify Address Details Below</h3>
						<div class="sideSpace">
							<p>Logged in as
							<?php echo $_SESSION["cwclient"]["cwCustomerName"]; ?>&nbsp;&nbsp;
                            <?php // logout link ?>
							<span class="smallPrint"><a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>?logout=1">Not your account?</a></span>
							</p>
						</div>
<?php
		}
		// /end if logged in 
?>
				</div>
<?php
	}
	// /end if orderFinal ?>
		</div>
<?php
}
// /END LOGIN/NEW ACCOUNT: STEP 1
// CUSTOMER INFO: STEP 2 START ?>
	<div class="CWformSection" id="step2">
<?php
// customer account section 
if ($_SESSION["cw"]["confirmAddress"] || isset($_POST["customer_email"]) || (isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0") || (isset($_GET["account"]) && ($_GET["account"] == 1 || $_GET["account"] == 0)) || $_ENV["application.cw"]["customerAccountEnabled"] != true) {
	$altClass = " CWactiveSection";
} else {
	$altClass = "";
}
?>
		<h3 class="CWformSectionTitle<?php echo $altClass; ?>">Address &amp; Account Details</h3>
<?php
if ($_ENV["request.cwpage"]["orderFinal"] == false) {
	// CUSTOMER INFO FORM 
	// if account is enabled but not required, user can switch with link to url w/account=0 
	if ($_ENV["application.cw"]["customerAccountEnabled"] == true && $_ENV["application.cw"]["customerAccountRequired"] == false && isset($_GET["account"]) && $_GET["account"] == 0 && !(isset($_SESSION["cwclient"]["cwCustomerCheckout"]) && $_SESSION["cwclient"]["cwCustomerCheckout"] == 'account')) {
		$showAccount = false;
		$formAction = $_ENV["request.cwpage"]["hrefUrl"];
		// show customer account fields if customer is new (not logged in),
		//or is logged in as checkouttype = 'account 
	} else if ($_ENV["application.cw"]["customerAccountEnabled"] && ((!strlen($_SESSION["cwclient"]["cwCustomerID"]) || $_SESSION["cwclient"]["cwCustomerID"] === 0 || $_SESSION["cwclient"]["cwCustomerID"] === "0") || (isset($_GET["account"]) && $_GET["account"] == 1) || (isset($_SESSION["cwclient"]["cwCustomerCheckout"]) && $_SESSION["cwclient"]["cwCustomerCheckout"] == 'account'))) {
		$showAccount = true;
		$formAction = $_ENV["request.cwpage"]["hrefUrl"] . '?account=1';
	} else {
		$showAccount = false;
		$formAction = $_ENV["request.cwpage"]["hrefUrl"];
	}
?>
			<div class="CWstepSection">
<?php
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array(
		"submit_value" => "Confirm &amp; Continue&nbsp;&raquo;",
		"success_url" => $_ENV["request.cwpage"]["hrefUrl"],
		"form_action" => "",
		"show_account_info" => $showAccount);
	include("cwapp/mod/cw-mod-formcustomer.php");
	unset($module_settings);
	chdir($myDir);
?>
			</div>
<?php
}
// /end if orderFinal ?>
	</div>
<?php
// /END CUSTOMER INFO: STEP 2 
// SHIPPING METHOD: STEP 3 START 
if ($_ENV["request.cwpage"]["shipDisplay"]) {
?>
		<div class="CWformSection" id="step3">
			<h3 class="CWformSectionTitle<?php if ($_SESSION["cw"]["confirmAddress"]  && isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0") {?> CWactiveSection<?php }?>">Shipping Details</h3>
<?php
	// SHIPPING OPTIONS AND DETAILS 
	if ($_ENV["request.cwpage"]["orderFinal"] == false) {
?>
				<div class="CWstepSection">
<?php
		if ($_ENV["request.cwpage"]["currentStep"] > 1) {
			$shipCart = CWgetCart();
			// SHIPPING DETAILS ?>
						<div class="halfLeft">
<?php
			// shipping selection / total 
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			$module_settings = array(
				"cart" => $shipCart,
				"customer_data" => $CWcustomer,
				"show_address" => false);
			include("cwapp/mod/cw-mod-shipdisplay.php");
			unset($module_settings);
			chdir($myDir);
?>
						</div>
						<div class="halfRight"></div>
						<div class="CWclear"></div>
<?php
			// /end shipping selection 
		}
?>
				</div>
<?php
	}
	// /end if orderFinal ?>
		</div>
<?php
} else {
	$_SESSION["cw"]["confirmShip"] = true;
}
// /end if shipping enabled 
// /END SHIPPING METHOD: STEP 3 
// CONFIRM CART: STEP 4 START ?>
	<div class="CWformSection" id="step4">
		<h3 class="CWformSectionTitle<?php if ($_ENV["request.cwpage"]["currentStep"] >= 4) { ?> CWactiveSection<?php } ?>">Confirm Order</h3>
<?php
if ($_ENV["request.cwpage"]["orderFinal"] == false) {
?>
			<div class="CWstepSection">
<?php
	if ($_ENV["request.cwpage"]["currentStep"] > 3) {
		// cart summary ?>
					<div class="sideSpace">
<?php
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		$module_settings = array(
			"cart" => $cwcart,
			"display_mode" => "summary",
			"product_order" => $_ENV["application.cw"]["appDisplayCartOrder"],
			"show_images" => $_ENV["application.cw"]["appDisplayCartImage"],
			"show_sku" => $_ENV["application.cw"]["appDisplayCartSku"],
			"show_options" => true,
			"show_continue" => false,
			"show_total_row" => false,
			"link_products" => false,
			"edit_cart_url" => '');
		include("cwapp/mod/cw-mod-cartdisplay.php");
		unset($module_settings);
		chdir($myDir);
?>
					</div>
					<div class="halfLeft">
						<div class="center top40 bottom40">
							<a id="CWlinkConfirmCart" class="CWcheckoutLink" href="<?php

$itemcountco=(sizeof($cwcart["cartitems"]));


$prodidarray=array();
		
for ($lineitem=0; $lineitem<$itemcountco; $lineitem++){
$prodidarray[] = $cwcart["cartitems"][$lineitem]["ID"];
}	

		if(isset($_SESSION["cwclient"]["cwShipCountryID"]) && $_SESSION["cwclient"]["cwShipCountryID"]==45 && ((in_array(129, $prodidarray)) || (in_array(126, $prodidarray))))
		{
		echo $_ENV["request.cwpage"]["urlShowCart"];
		}
		else
		{
		echo $_ENV["request.cwpage"]["hrefUrl"] . '?cartconfirm=1';
		}

		?>" style="">Continue&nbsp;&raquo;</a>
						</div>
					</div>
					<div class="halfRight">
						<h3 class="CWformTitle">Order Totals</h3>
<?php
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		$module_settings = array(
			"display_mode" => "totals",
			"cart" => $cwcart,
			"show_payment_total" => true);	
		include("cwapp/mod/cw-mod-cartdisplay.php");
		unset($module_settings);
		chdir($myDir);
?>
					</div>
<?php
	}
?>
			</div>
<?php
}
?>
		<div class="CWclear"></div>
	</div>
<?php
// /END CONFIRM CART: STEP 4 
// SUBMIT ORDER: STEP 5 START ?>
	<div class="CWformSection" id="step5">
<?php
$authMethodsArr = $_ENV["application.cw"]["authMethods"];
if (!is_array($authMethodsArr) && strlen($authMethodsArr)) $authMethodsArr = explode(",", $authMethodsArr);
else if (!is_array($authMethodsArr)) $authMethodsArr = array();
if (sizeof($authMethodsArr) > 0 && !$_ENV["request.cwpage"]["bypassPayment"]) {
	$stepTitle = "Submit Payment";
} else {
	$stepTitle = "Submit Order";
}
?>
		<h3 class="CWformSectionTitle<?php if ($_ENV["request.cwpage"]["currentStep"] >= 5) { ?> CWactiveSection<?php } ?>"><?php echo $stepTitle; ?></h3>
		<div class="CWstepSection">
<?php
if ($_ENV["request.cwpage"]["currentStep"] >= 5) {
	// CHECKOUT INFO ?>
				<div class="CWclear"></div>
<?php
	// display any processing errors here 
	if (isset($_ENV["request.trans"]["formErrors"]) && count($_ENV["request.trans"]["formErrors"]) || isset($_ENV["request.trans"]["errorMessage"]) && strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
?>
					<div class="CWalertBox alertText">
<?php
		if (isset($_ENV["request.trans"]["formErrors"]) && count($_ENV["request.trans"]["formErrors"])) {
?>
							Error: Missing or Invalid Information
							<br>
<?php
		}
		if (strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
			$newErrorArr = explode(',',$_ENV["request.trans"]["errorMessage"]);
			foreach ($newErrorArr as $key => $mm) {
				echo $mm . '<br>';
			}
		}
?>
					</div>
<?php
	}
	// /end processing errors ?>
				<div class="halfLeft">
<?php
	// IF BOTH PAYMENT METHOD AND SHIPPING METHOD ARE SET 
	$authMethodsArr = $_ENV["application.cw"]["authMethods"];
	if (!is_array($authMethodsArr) && strlen($authMethodsArr)) $authMethodsArr = explode(",", $authMethodsArr);
	else if (!is_array($authMethodsArr)) $authMethodsArr = array();
	if ($_SESSION["cw"]["confirmOrder"] == true && !(isset($_GET["authreset"]) && sizeof($authMethodsArr) >= 2)) {
		// SUBMIT ORDER (DEFAULT): if submitting order, show form (w/ credit card inputs if using a gateway) 
		if ($_ENV["request.cwpage"]["postToProcessor"] == false) {
			if (!$_ENV["request.cwpage"]["bypassPayment"]) {
				// non-visible instance of the payment module included here to handle setting selection
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				$module_settings = array(
					"submit_url" => "",
					"selected_title" => "",
					"edit_auth_url" => "",
					"show_auth_logo" => false,
					"show_auth_name" => false);
				include("cwapp/mod/cw-mod-paymentdisplay.php");
				unset($module_settings);
				chdir($myDir);
			}
			// order submission form ?>
							<form name="CWformOrderSubmit" id="CWformOrderSubmit" class="CWvalidate" method="post" action="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>">
<?php
			// show submit order message
			//and, if auth type is 'gateway', show credit card inputs 
			// credit card fields / submission message ('capture' mode) 
			if (!$_ENV["request.cwpage"]["bypassPayment"]) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				$module_settings = array(
					"display_mode" => "capture",
					"submit_url" => $_ENV["request.cwpage"]["hrefUrl"],
					"edit_auth_url" => "");
				include("cwapp/mod/cw-mod-paymentdisplay.php");
				unset($module_settings);
				chdir($myDir);
			}
?>
								<div class="CWclear">
									<?php // order comments ?>
									<div class="center">
										<h3 class="CWformTitle">Additional Comments or Instructions</h3>
										<textarea name="order_message" id="order_message" rows="3" cols="35"><?php echo trim($_ENV["request.cwpage"]["orderMessage"]); ?></textarea>
									</div>
									<?php // submit button ?>
									<div class="center top40 bottom40">
										<input name="order_submit" id="order_submit" type="submit" class="CWformButton" value="<?php echo $_ENV["request.cwpage"]["submitValue"]; ?>">
										<?php // submit link : javascript replaces button with this link ?>
										<a style="display:none;" href="#" class="CWcheckoutLink" id="CWlinkOrderSubmit"><?php echo $_ENV["request.cwpage"]["submitValue"]; ?></a>
									</div>
									<div class="headSpace"></div>
								</div>
								<?php // hidden fields ?>
								<input type="hidden" name="customer_shippref" value="<?php echo $_SESSION["cw"]["confirmShip"]; ?>">
								<input type="hidden" name="customer_submit_conf" value="<?php echo $_SESSION["cwclient"]["cwCustomerID"]; ?>">
								<div class="CWclear"></div>
							</form>
<?php
		// POST TO PROCESSOR (paypal, etc, after order has been submitted) 
		} else if (isset($_SESSION["cw"]["authType"]) && $_SESSION["cw"]["authType"] == 'processor' && isset($_SESSION["cw"]["authPref"]) && $_SESSION["cw"]["authPref"] > 0 && isset($_SESSION["cw"]["authPrefName"]) && $_SESSION["cw"]["authPrefName"] != "") {
			// verify payment file exists 
			$authPrefID = $_SESSION["cw"]["authPref"]-1;
			if (!isset($_ENV["application.cw"]["authMethodData"][$authPrefID]["methodFileName"])) $_ENV["application.cw"]["authMethodData"][$authPrefID]["methodFileName"] = "";
			if (!isset($_ENV["application.cw"]["authMethodData"][$authPrefID]["methodName"])) $_ENV["application.cw"]["authMethodData"][$authPrefID]["methodName"] = "";
			$_ENV["request.trans"]["authFileName"] = $_ENV["application.cw"]["authMethodData"][$authPrefID]["methodFileName"];
			$_ENV["request.trans"]["authMethodName"] = $_ENV["application.cw"]["authMethodData"][$authPrefID]["methodName"];
			// verify auth file exists, and is same as expected in user's session 
			$authDirectory = preg_replace('/\/+$/', "/", preg_replace('/\/+$/', "", $_ENV["application.cw"]["siteRoot"]).'/'.$_ENV["application.cw"]["appCWContentDir"]).'cwapp/auth';
			if (substr($authDirectory, 0, 1) == "/") $authDirectory = substr($authDirectory, 1);
			$authFilePath = expandPath($authDirectory);
			// if file is ok, and authPref is same as user selection, invoke the auth include 
			if (file_exists($authFilePath . '/' . $_ENV["request.trans"]["authFileName"]) && $_ENV["request.trans"]["authMethodName"] == $_SESSION["cw"]["authPrefName"]) {
				// invoke payment file in 'capture' mode (shows submission form) 
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				$auth_settings = array(
					"auth_mode" => "capture",
					"trans_data" => $_ENV["request.trans"]["data"]);
				include($authFilePath . '/' . $_ENV["request.trans"]["authFileName"]);
				unset($auth_settings);
				chdir($myDir);
				// clear any stored promo codes 
				$_SESSION["cwclient"]["discountApplied"] = '';
				$_SESSION["cwclient"]["discountPromoCode"] = '';
				// set marker for clearing of cart 
				$_REQUEST["clearCart"] = true;
				// WIPE DATA - clear transaction data from request scope 
				unset($_ENV["request.trans"]["data"]);
				unset($_REQUEST["data"]);
			}
			// /end if file ok 
		}
		// /end POST TO PROCESSOR or SUBMIT ORDER 
		// IF PAYMENT METHOD IS NOT SET 
	} else {
		// payment display 
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		$module_settings = array(
			"submit_url" => $_ENV["request.cwpage"]["hrefUrl"],
			"selected_title" => "",
			"bypass_payment" => $_ENV["request.cwpage"]["bypassPayment"]);
		include("cwapp/mod/cw-mod-paymentdisplay.php");
		unset($module_settings);
		chdir($myDir);
	}
?>
				</div>
				<?php // ORDER TOTALS / PAYMENT DETAILS ?>
				<div class="halfRight">
					<?php // cart totals / details ?>
					<h3 class="CWformTitle">Order Totals</h3>
<?php
	// cart display: show totals only 
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array(
		"display_mode" => "totals",
		"cart" => CWgetCart(),
		"edit_cart_url" => "",
		"show_payment_total" => true);
	include("cwapp/mod/cw-mod-cartdisplay.php");
	unset($module_settings);
	chdir($myDir);
	// payment display: show payment selection 
	$authMethodsArr = $_ENV["application.cw"]["authMethods"];
	if (!is_array($authMethodsArr) && strlen($authMethodsArr)) $authMethodsArr = explode(",", $authMethodsArr);
	else if (!is_array($authMethodsArr)) $authMethodsArr = array();
	if ($_SESSION["cw"]["confirmOrder"] && !(isset($_GET["authreset"]) && sizeof($authMethodsArr) >= 2) && !$_ENV["request.cwpage"]["bypassPayment"]) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		$module_settings = array(
			"submit_url" => $_ENV["request.cwpage"]["hrefUrl"],
			"selected_title" => "");
		include("cwapp/mod/cw-mod-paymentdisplay.php");
		unset($module_settings);
		chdir($myDir);
	}
?>
				</div>
				<div class="CWclear"></div>
<?php
}
?>
			<div class="CWclear"></div>
		</div>
	</div>
	<?php // /END SUBMIT ORDER: STEP 5 ?>
</div>
<!-- /end #CWcheckout -->

<?php
// clear out the stored cart if marker is set 
if (isset($_SESSION["cwclient"]["cwCartID"]) && isset($_REQUEST["clearcart"]) && $_REQUEST["clearcart"]) {
	$clearCart = CWclearCart($_SESSION["cwclient"]["cwCartID"]);
}
// test credit card info for 'test mode' 
$checkoutjs = "";
if (isset($_ENV["application.cw"]["appTestModeEnabled"]) && $_ENV["application.cw"]["appTestModeEnabled"]) {
	$checkoutjs .= "<script type=\"text/javascript\">
jQuery(document).ready(function(){
	jQuery('#customer_cardnumber').val('4007000000027');
	jQuery('#customer_cardtype').val('visa');
	jQuery('#customer_cardname').val('Test User');
	jQuery('#customer_cardexpm').val('12');
	jQuery('#customer_cardexpy').val('2016');
	jQuery('#customer_cardccv').val('999');
//end jQuery
});
</script>
";
}
// javascript for checkout steps 
$checkoutjs .= "<script type=\"text/javascript\">
jQuery(document).ready(function(){
	// close steps on page load, attach click function for section headings
	jQuery('.CWstepSection').hide().siblings('.CWactiveSection').css('cursor','pointer').click(function(){
		if (jQuery(this).next('.CWstepSection').is(':hidden')){
		jQuery(this).next('.CWstepSection:hidden').slideDown(220);
		} else {
		jQuery(this).next('.CWstepSection:visible').slideUp(220);
		}
	});
	// open the current step
		var curStepId = 'step' + ".$_ENV["request.cwpage"]["currentStep"].";
	jQuery('#CWcheckout  #' + curStepId + ' .CWstepSection').slideDown(220);
	// function to show/hide each step
	// usage: \$toggleSteps([parent id of div to close e.g. 'step1'],[parent id of div to open e.g. 'step2']);
	var \$toggleSteps = function(prevStep,nextStep){
		jQuery('#CWcheckout #' + prevStep + ' .CWstepSection').slideUp(220);
		jQuery('#CWcheckout #' + nextStep + ' .CWstepSection').slideDown(220).siblings('.CWformSectionTitle').addClass('CWactiveSection');
		// connect to checkout steps breadcrumb links
		var stepClass = jQuery('#CWcheckout .CWstepSection #' + nextStep).parents('.CWformSection').attr('id');
		jQuery('#CWcheckoutStepLinks span.' + stepClass + ' a').addClass('currentLink');
	};
	// add headings to steps
	jQuery('#CWcheckout .CWformSectionTitle').each(function(index){
		var stepNum = index + 1;
		var stepCounter = '<span class=\"CWstepCounter\">' + stepNum + '</span>';
		jQuery(this).prepend(stepCounter);
	});
";
if (!$_ENV["request.cwpage"]["orderFinal"]) {
	$checkoutjs .= "	// activate dynamic checkout step links
	jQuery('#CWcheckoutStepLinks a').click(function(){
	var stepID = jQuery(this).parents('span').attr('class');
	if( jQuery(this).hasClass('currentLink') == true){
	jQuery('#' + stepID).siblings('div').find('.CWstepSection').slideUp(220);
	jQuery('#' + stepID).find('.CWstepSection').slideDown(220);
	}
	return false;
	});
";
}
$checkoutjs .= "	// new customer continue button
	jQuery('#CWlinkSkipLogin').click(function(){
	\$toggleSteps('step1','step2');
	return false;
	});
	// show submit link instead of button
	jQuery('#order_submit').hide();
	jQuery('#CWlinkOrderSubmit').show().click(function(){
	jQuery('form#CWformOrderSubmit').submit();
	return false;
	});
	// process form submission errors (list of errant form element IDs)
";
if (isset($_ENV["request.cwpage"]["formErrors"]) && count($_ENV["request.cwpage"]["formErrors"])) {
	foreach ($_ENV["request.cwpage"]["formErrors"] as $key => $ee) {
		if (strlen(trim($ee))) {
			$checkoutjs .= "	jQuery('#CWformOrderSubmit').find('".$ee."').addClass('warning');
";
		}
	}
}
$checkoutjs .= "});
</script>";
CWinsertHead($checkoutjs);
// page end / debug 
$myDir = getcwd();
chdir(dirname(__FILE__));
include('cwapp/inc/cw-inc-pageend.php');
chdir($myDir);
?>
<?php } //// END VERIFY AT LEAST ONE CHECKOUT METHOD EXISTS: bypassed in 'test mode' 
else { 

$errorText = 'CHECKOUT PROCESS OFFLINE <br>';
$errorText .=  'Payment transactions for this site are currently offline. <br>';
$errorText .=  'For troubleshooting, reset the application from within the store admin, and verify at least one active checkout method is available.This message can be bypassed by placing the store in "Test Mode" for development. <br>';
$errorText .=  'No orders or payments can be accepted until the problem is resolved.';	
?>
		<div class="CWconfirmBox confirmText">
			Checkout Process Offline<br>Payment transaction options for this store are currently unavailable.<br>We apologize for the inconvenience, and are working to correct the problem.
		</div>
<?php
$result = CWsendMail($errorText, $_ENV["application.cw"]["companyName"]." : Checkout Process Offline ",$_ENV["application.cw"]["developerEmail"]);

}  // END ELSE  VERIFY AT LEAST ONE CHECKOUT METHOD EXISTS: bypassed in 'test mode' ?>