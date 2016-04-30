<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-confirm.php
File Date: 2012-03-2
Description: displays order confirmation, runs post-transaction processing
NOTES:
Enable "test mode" in CW admin, to persist client/session variables for testing
Variable $_SESSION["cwclient"]["cwCartID"] is set in cw-func-init.php
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
// orderID 
if (!isset($_SESSION["cwclient"]["cwCompleteOrderID"])) $_SESSION["cwclient"]["cwCompleteOrderID"] = 0;
// customerID needed to handle account / billing 
if (!isset($_SESSION["cwclient"]["cwCustomerID"])) $_SESSION["cwclient"]["cwCustomerID"] = 0;
// error message / confirmation from transaction 
if (!isset($_ENV["request.trans"]["errorMessage"])) $_ENV["request.trans"]["errorMessage"] = "";
if (!isset($_ENV["request.trans"]["confirmMessage"])) $_ENV["request.trans"]["confirmMessage"] = "";
// page heading 
if (!isset($_ENV["request.cwpage"]["confirmHeading"])) $_ENV["request.cwpage"]["confirmHeading"] = "Order Confirmation";
// show order details in page yes/no 
if (!isset($_ENV["request.cwpage"]["displayOrderDetails"])) $_ENV["request.cwpage"]["displayOrderDetails"] = true;
// url vars 
if (!isset($_GET["orderid"])) $_GET["orderid"] = 0;
// mode (confirm:default/gateway|return:returning/processor|cancel:order cancelled) 
if (!isset($_GET["mode"])) $_GET["mode"] = "confirm";
$myDir = getcwd();
chdir(dirname(__FILE__));
// clean up form and url variables 
include("cwapp/inc/cw-inc-sanitize.php");
// CARTWEAVER REQUIRED FUNCTIONS 
include("cwapp/inc/cw-inc-functions.php");
chdir($myDir);
// API CALLBACKS 
// HANDLE GATEWAY API RESPONSES (e.g. PayPal IPN) by key field:
//loop available payment methods, check for key field present 
// do not process on 'return' mode from paypal, other processors 
if ($_GET["mode"] != 'return') {
	$amArr = $_ENV["application.cw"]["authMethods"];
	if (!is_array($amArr) && strlen($amArr)) $amArr = explode(",", $amArr);
	elseif (!is_array($amArr)) $amArr = array();
	foreach ($amArr as $key => $i) {
		$i--;
		if (!isset($_ENV["application.cw"]["authMethodData"][$i]["methodID"])) $_ENV["application.cw"]["authMethodData"][$i]["methodID"] = "";
		if (!isset($_ENV["application.cw"]["authMethodData"][$i]["methodName"])) $_ENV["application.cw"]["authMethodData"][$i]["methodName"] = "";
		if (!isset($_ENV["application.cw"]["authMethodData"][$i]["methodFileName"])) $_ENV["application.cw"]["authMethodData"][$i]["methodFileName"] = "";
		if (!isset($_ENV["application.cw"]["authMethodData"][$i]["methodType"])) $_ENV["application.cw"]["authMethodData"][$i]["methodType"] = "";
		if (!isset($_ENV["application.cw"]["authMethodData"][$i]["methodConfirmMessage"])) $_ENV["application.cw"]["authMethodData"][$i]["methodConfirmMessage"] = "";
		if (!isset($_ENV["application.cw"]["authMethodData"][$i]["methodTransKeyField"])) $_ENV["application.cw"]["authMethodData"][$i]["methodTransKeyField"] = "";
		if (!isset($CWauth)) $CWauth = array();
		$CWauth["methodID"] = $_ENV["application.cw"]["authMethodData"][$i]["methodID"];
		$CWauth["methodName"] = $_ENV["application.cw"]["authMethodData"][$i]["methodName"];
		$CWauth["methodFileName"] = $_ENV["application.cw"]["authMethodData"][$i]["methodFileName"];
		$CWauth["methodType"] = $_ENV["application.cw"]["authMethodData"][$i]["methodType"];
		$CWauth["methodConfirmMessage"] = $_ENV["application.cw"]["authMethodData"][$i]["methodConfirmMessage"];
		$CWauth["methodTransKeyField"] = $_ENV["application.cw"]["authMethodData"][$i]["methodTransKeyField"];
		// Check for Key Field: if a variable matching the 'transkeyfield' exists, and has a value 
		$evaluatedField = "";
		$evaluatedFieldSet = false;
		if (isset($CWauth["methodTransKeyField"]) && strlen(trim($CWauth["methodTransKeyField"]))) {
			eval("\$evaluatedFieldSet = isset(".$CWauth["methodTransKeyField"].");");
			if ($evaluatedFieldSet) {
				eval("\$evaluatedField = ".$CWauth["methodTransKeyField"].";");
			}
		}
		if ($evaluatedFieldSet && strlen(trim($evaluatedField))) {
			// invoke file as cfmodule in 'process' mode, process payment 
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			$auth_settings = array(
				"auth_mode" => "process");
			include("cwapp/auth/".$CWauth["methodFileName"]);
			unset($auth_settings);
			chdir($myDir);
		}
		// /end key field match / auth processing 
	}
	// output email to developer in test mode 
	try {
		if (isset($_ENV["application.cw"]["developerEmail"]) and isValidEmail($_ENV["application.cw"]["developerEmail"]) && isset($_ENV["application.cw"]["appTestModeEnabled"]) && $_ENV["application.cw"]["appTestModeEnabled"]) {
			ob_start();
			echo "
form:
";
			var_dump($_POST);
			echo "
server:
";
			var_dump($_SERVER);
			echo "
method data:
";
			var_dump($_ENV["application.cw"]["authMethodData"]);
			echo "
request.trans - transaction details:
";
			var_dump($_ENV["request.trans"]);
			$mailContent = ob_get_contents();
			ob_end_clean();
			$temp = CWsendMail($mailContent, 'running process', $_ENV["application.cw"]["developerEmail"]);
		}
		// additional debugging can be executed here, if there's any error with the email sending above 
	} catch (Exception $e) { }
	// /end test mode developer email 
}
// /end if not $_GET["return 
// /END API CALLBACKS 
// CONFIRMATION 
// QUERY: get order details 
$orderQuery = CWquerySelectOrderDetails($_SESSION["cwclient"]["cwCompleteOrderID"]);
// IF ORDER IS VALID 
if ($orderQuery["totalRows"] > 0 && $orderQuery["order_id"][0] == $_SESSION["cwclient"]["cwCompleteOrderID"]) {
	// VERIFY ORDER STATUS 
	// CANCELLING ORDER: check for order cancelled via url (url must be in ID and client memory) 
	if (trim($_GET["mode"]) == 'cancel' && trim($_GET["orderid"]) == $_SESSION["cwclient"]["cwCompleteOrderID"]) {
		// prevent misuse of cancel via url: only cancel if order status allows (partial / pending only) 
		if ($orderQuery["order_status"][0] < 3) {
			// QUERY: mark order status to cancelled 
			$updateOrder = CWqueryUpdateOrder($orderQuery["order_id"][0], 5);
			// refresh page to clear message and apply cancelled status 
			header("Location: ".$_ENV["request.cw"]["thisPage"]."?orderid=".$_GET["orderid"]);
			// if order was previously completed 
		} else if ($orderQuery["order_status"][0] >= 3) {
			if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
			$_ENV["request.trans"]["errorMessage"] .= 'This order is being processed and cannot be cancelled.';
			$_ENV["request.cwpage"]["displayOrderDetails"] = false;
		}
	// CANCELLED PREVIOUSLY: check for cancelled status in db 
	} else if ($orderQuery["order_status"][0] == 5) {
		// add error message for user 
		if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
		$_ENV["request.trans"]["errorMessage"] .= 'Payment Cancelled. Order will not be processed.';
		$_ENV["request.cwpage"]["displayOrderDetails"] = false;
		// require reconfirmation of order 
		$_SESSION["cw"]["confirmOrder"] = false;
		$_SESSION["cw"]["confirmAuthPref"] = false;
		$_SESSION["cw"]["confirmCart"] = false;
	// PARTIAL PAYMENT: check for balance due / partial payment status 
	} else if ($orderQuery["order_status"][0] == 2) {
		// get transactions related to this order (including the one just inserted) 
		$orderPaymentTotal = CWorderPaymentTotal($orderQuery["order_id"][0]);
		// set balance due 
		$_ENV["request.trans"]["orderBalance"] = $orderQuery["order_total"][0] - $orderPaymentTotal;
		$_SESSION["cw"]["paymentAlert"] = "Insufficient funds available - balance of ".cartweaverMoney($_ENV["request.trans"]["orderBalance"],'local')." due. <br>Please use another payment method to complete your order.";
		// redirect to payment page showing balance due 
		header("Location: ".$_ENV["request.cwpage"]["urlCheckout"]);
	// PENDING: check for pending status (no payments applied) 
	} else if ($orderQuery["order_status"][0] == 1) {
		// add error message for user 
		if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
		$_ENV["request.trans"]["errorMessage"] .= 'Order Pending. Payment processing incomplete.';
	// ALL GOOD: CONFIRM ORDER (status ok, balance ok, not cancelling) 
	} else {
		// check for payment preference in client memory 
		if (isset($_SESSION["cwclient"]["cwCustomerAuthPref"]) && is_numeric($_SESSION["cwclient"]["cwCustomerAuthPref"]) && $_SESSION["cwclient"]["cwCustomerAuthPref"] > 0) {
			// get confirmation message for active payment method, add to customer confirmation 
			if (!isset($_ENV["application.cw"]["authMethodData"][$_SESSION["cwclient"]["cwCustomerAuthPref"]-1]["methodConfirmMessage"])) {
				$_ENV["application.cw"]["authMethodData"][$_SESSION["cwclient"]["cwCustomerAuthPref"]-1]["methodConfirmMessage"] = "";
			}
			if ($_ENV["request.trans"]["confirmMessage"]) $_ENV["request.trans"]["confirmMessage"] .= ",";
			$_ENV["request.trans"]["confirmMessage"] .= $_ENV["application.cw"]["authMethodData"][$_SESSION["cwclient"]["cwCustomerAuthPref"]-1]["methodConfirmMessage"];
			// if client auth pref is 0, but we got this far, no payments are required in the store - show generic message 
		} else if ($_SESSION["cwclient"]["cwCustomerAuthPref"] == 0) {
			if ($_ENV["request.trans"]["confirmMessage"]) $_ENV["request.trans"]["confirmMessage"] .= ",";
			$_ENV["request.trans"]["confirmMessage"] .= "Thank you.";
		}
	}
	// /end status / cancel / balance due check 
	// /END VERIFY STATUS 
	// IF ORDER IS NOT VALID 
	// if client order id is invalid or does not exist, check for order id in URL
	//(refreshing or revisiting confirmation page after initial submission will trigger this) 
} else {
	// QUERY: get order details 
	$orderQuery = CWquerySelectOrderDetails($_GET["orderid"]);
	// if an order is found 
	if ($orderQuery["totalRows"]) {
		// if order is completed, show custom message 
		if ($orderQuery["order_status"][0] > 2) {
			$_ENV["request.cwpage"]["confirmHeading"] = "Order Complete";
			if ($_ENV["request.trans"]["confirmMessage"]) $_ENV["request.trans"]["confirmMessage"] .= ",";
			$_ENV["request.trans"]["confirmMessage"] .= "This transaction has been completed.";
			// hide order details 
			$_ENV["request.cwpage"]["displayOrderDetails"] = false;
			// if order is partial or pending, show generic 'processing' message 
		} else {
			$_ENV["request.cwpage"]["confirmHeading"] = "Order Processing";
			if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
			$_ENV["request.trans"]["errorMessage"] .= 'This order is being processed.';
			$_ENV["request.cwpage"]["displayOrderDetails"] = false;
		}
	}
}
// /END IF ORDER VALID 
// IF NO ORDER FOUND in url or client match 
if ($orderQuery["totalRows"] == 0) {
	// set page heading 
	$_ENV["request.cwpage"]["confirmHeading"] = "Invalid Order ID";
	if ($_ENV["request.trans"]["errorMessage"]) $_ENV["request.trans"]["errorMessage"] .= ",";
	$_ENV["request.trans"]["errorMessage"] .= 'Order Details Unavailable';
	// hide order details 
	$_ENV["request.cwpage"]["displayOrderDetails"] = false;
}
// / END IF NO ORDER FOUND 
// /////// START OUTPUT /////// 
// breadcrumb navigation 
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "breadcrumb",
	"separator" => " &raquo; ",
	"end_label" => "Check Out : <strong>Order Processing</strong>",
	"all_categories_label" => "",
	"all_secondaries_label" => "",
	"all_products_label" => "");
include("cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
// confirmation content ?>
<div id="CWconfirm" class="CWcontent">
	<h1><?php echo $_ENV["request.cwpage"]["confirmHeading"]; ?></h1>
<?php
// MESSAGE DISPLAY: show error / confirmation messages here 
if (strlen(trim($_ENV["request.trans"]["errorMessage"])) || strlen(trim($_ENV["request.trans"]["confirmMessage"]))) {
?>
		<div class="CWconfirmBox">
<?php
	// error messages 
	if (strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
		$emArr = explode(",", $_ENV["request.trans"]["errorMessage"]);
		foreach ($emArr as $key => $ee) {
?>
					<div class="alertText"><?php echo $ee; ?></div>
<?php
		}
		// show general message to accompany any errors ?>
				<div class="confirmText">Contact us for assistance&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="<?php echo $_ENV["request.cwpage"]["urlResults"]; ?>">Return to store</a></div>
<?php
	}
	// confirmation messages 
	if (strlen(trim($_ENV["request.trans"]["confirmMessage"]))) {
		$cmArr = explode(",", $_ENV["request.trans"]["confirmMessage"]);
		foreach ($cmArr as $key => $ee) {
?>
					<div class="confirmText"><?php echo $ee; ?></div>
<?php  
		}
		// if no errors (and order is paid in full) show general message 
		if (!strlen(trim($_ENV["request.trans"]["errorMessage"]))) {
?>
					<div class="confirmText">
						Your order will be processed shortly.
<?php
			if ($_ENV["application.cw"]["customerAccountEnabled"] && !(isset($_SESSION["cwclient"]["cwCustomerCheckout"]) && $_SESSION["cwclient"]["cwCustomerCheckout"] == 'guest')) {
?>
							<br>
<?php
				// link to log in / my account 
				$loginLinkText = 'Log in to your account';
?>
							<a href="<?php echo $_ENV["request.cwpage"]["urlAccount"]; ?>"><?php echo trim($loginLinkText); ?></a> to see order details.
<?php
			}
?>
					</div>
<?php
		}
	}
?>
		</div>
<?php
}
// /end message display 
// SHOW ORDER CONTENTS
if ($_ENV["request.cwpage"]["displayOrderDetails"]) {
?>
		<p><a href="javascript:window.print()">&raquo;&nbsp;Print This Page</a></p>
<?php
	// display order contents, passing in order query from above 
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array(
		"order_query" => $orderQuery,
		"display_mode" => "summary",
		"show_images" => $_ENV["application.cw"]["appDisplayCartImage"],
		"show_sku" => $_ENV["application.cw"]["appDisplayCartSku"],
		"show_options" => true);
	include("cwapp/mod/cw-mod-orderdisplay.php");
	unset($module_settings);
	chdir($myDir);
}
// ORDER PROCESS IS COMPLETE: CLEAR CART / ORDER CONTENTS 
// delete all stored cart values (DISABLED IN TEST MODE) 
if (!$_ENV["application.cw"]["appTestModeEnabled"]) {
	if (isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0") {
		$clearingID = true;
	} else {
		$clearingID = false;
	}
	// show log out message if client was logged in 
	if ($clearingID && $_ENV["application.cw"]["customerAccountEnabled"] && !(isset($_SESSION["cwclient"]["cwCustomerCheckout"]) && $_SESSION["cwclient"]["cwCustomerCheckout"] == 'guest')) {
?>
			<p>For added security, you have been logged out.</p>
<?php
	}
	// if a cart id is in the session, clear out the cart 
	if (isset($_SESSION["cwclient"]["cwCartID"])) {
		$clearCart = CWclearCart($_SESSION["cwclient"]["cwCartID"]);
	}
	// clear CW session values 
	unset($_SESSION["cw"]);
	unset($_SESSION["cwclient"]);
	// clear cart-related cookie vars (set to null/expired) 
	if ($_ENV["application.cw"]["appCookieTerm"] != 0) {
		$toUnset = array("cwCartID","cwCustomerID","cwUserName","cwCompleteOrderID","cwOrderTotal","cwTaxTotal","cwShipTotal","cwShipTaxTotal","discountPromoCode","discountApplied");
		foreach ($toUnset as $key => $cc) {
			setcookie($cc, "", 0);
		}
	}
	// in test mode this data is not cleared, refresh confirmation page allowed 
} else {
?>
		<p><strong>TEST MODE ENABLED: STORED VALUES NOT CLEARED FROM SESSION / CLIENT</strong></p>
<?php
}
?>
	<!-- clear floated content -->
	<div class="CWclear"></div>
</div>
<!-- / end CWconfirm -->
<?php
// page end / debug 
$myDir = getcwd();
chdir(dirname(__FILE__));
include("cwapp/inc/cw-inc-pageend.php");
chdir($myDir);
?>