<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-cartdisplay.php
File Date: 2012-02-01
Description:
Creates and displays cart details, with options for various uses
==========================================================
Mode "showcart" creates editable form for cart quantity updates, etc
Mode "summary" shows cart details without update/edit functions
Mode "totals" shows order totals only, useful for split-display or quick reports
*/
// cart is a cart structure from CWcart function 
if (!isset($module_settings["cart"])) $module_settings["cart"] = "";
if (!isset($_SESSION["cwclient"]["cwCartID"])) $_SESSION["cwclient"]["cwCartID"] = 1;
// show cart edit form or summary ( showcart | summary | totals ) 
if (!isset($module_settings["display_mode"])) $module_settings["display_mode"] = "showcart";
// show images next to products 
if (!isset($module_settings["show_images"])) $module_settings["show_images"] = false;
// show options and custom values for product 
if (!isset($module_settings["show_options"])) $module_settings["show_options"] = false;
// show sku next to product name 
if (!isset($module_settings["show_sku"])) $module_settings["show_sku"] = false;
// show continue shopping link y/n 
if (!isset($module_settings["show_continue"])) $module_settings["show_continue"] = false;
// show row w/ cart totals 
if (!isset($module_settings["show_total_row"])) $module_settings["show_total_row"] = true;
// show specific totals 
if (!isset($module_settings["show_tax_total"])) $module_settings["show_tax_total"] = true;
if (!isset($module_settings["show_discount_total"])) $module_settings["show_discount_total"] = true;
if (!isset($module_settings["show_ship_total"])) $module_settings["show_ship_total"] = true;
if (!isset($module_settings["show_order_total"])) $module_settings["show_order_total"] = true;
// show discount descriptions 
if (!isset($module_settings["show_discount_descriptions"])) $module_settings["show_discount_descriptions"] = true;
// show input for discount promo code 
if (!isset($module_settings["show_promocode_input"])) $module_settings["show_promocode_input"] = $_ENV["application.cw"]["discountsEnabled"];
// show payments made and balance due 
if (!isset($module_settings["show_payment_total"])) $module_settings["show_payment_total"] = false;
// link products to details page 
if (!isset($module_settings["link_products"])) $module_settings["link_products"] = false;
// product order (timeadded sorts by order added, othrwise by product name) 
if (!isset($module_settings["product_order"])) $module_settings["product_order"] = "";
// edit cart url (not used for showcart mode - blank = not shown at all) 
if (!isset($module_settings["edit_cart_url"])) $module_settings["edit_cart_url"] = $_ENV["request.cwpage"]["urlShowCart"];
// customer id: used for getting customer-specific discounts 
if (!isset($_SESSION["cwclient"]["cwCustomerID"])) $_SESSION["cwclient"]["cwCustomerID"] = 0;
if (!isset($module_settings["customer_id"])) $module_settings["customer_id"] = $_SESSION["cwclient"]["cwCustomerID"];
// promo codes: delimited list with ^ separator 
if (!isset($_ENV["request.cwpage"]["promocode"])) $_ENV["request.cwpage"]["promocode"] = array();
if (!isset($module_settings["promocode"])) $module_settings["promocode"] = $_ENV["request.cwpage"]["promocode"];
// page alerts and errors 
if (!isset($_ENV["request.cwpage"]["cartAlert"])) $_ENV["request.cwpage"]["cartAlert"] = "";
if (!isset($_ENV["request.cwpage"]["cartConfirm"])) $_ENV["request.cwpage"]["cartConfirm"] = "";
if (!isset($_ENV["request.cwpage"]["cartConfirmIDs"])) $_ENV["request.cwpage"]["cartConfirmIDs"] = "";
if (!isset($_ENV["request.cwpage"]["stockAlertIDs"])) $_ENV["request.cwpage"]["stockAlertIDs"] = "";
// parse alert id values out of session if available 
if (isset($_SESSION["cw"]["stockAlertIDs"])) {
	$_ENV["request.cwpage"]["stockAlertIDs"] = $_SESSION["cw"]["stockAlertIDs"];
	unset($_SESSION["cw"]["stockAlertIDs"]);
}
if (isset($_SESSION["cw"]["cartConfirmIDs"])) {
	$_ENV["request.cwpage"]["cartConfirmIDs"] = $_SESSION["cw"]["cartConfirmIDs"];
	unset($_SESSION["cw"]["cartConfirmIDs"]);
}
// values for showcart mode 
if ($module_settings["display_mode"] == 'showcart') {
	if (isset($_SESSION["cw"]["cartAlert"])) {
		$_ENV["request.cwpage"]["cartAlert"] = $_SESSION["cw"]["cartAlert"];
		unset($_SESSION["cw"]["cartAlert"]);
	}
	// alerts / confirmations 
	if (isset($_SESSION["cw"]["cartConfirm"])) {
		$_ENV["request.cwpage"]["cartConfirm"] = $_SESSION["cw"]["cartConfirm"];
		unset($_SESSION["cw"]["cartConfirm"]);
	}
	if (isset($_GET['addedID']) && strlen(trim($_GET['addedID']))) {
		if ($_ENV["request.cwpage"]["cartConfirmIDs"]) $_ENV["request.cwpage"]["cartConfirmIDs"] .= ",";
		$_ENV["request.cwpage"]["cartConfirmIDs"] .= $_GET['addedID'];
	}
	if (isset($_GET['alertID']) && strlen(trim($_GET['alertID'])) && $_ENV["application.cw"]["appEnableBackOrders"] != true) {
		if ($_ENV["request.cwpage"]["stockAlertIDs"]) $_ENV["request.cwpage"]["stockAlertIDs"] .= ",";
		$_ENV["request.cwpage"]["stockAlertIDs"] .= $_GET['alertID'];
	}
}
// custom errors can be passed in here 
$_ENV["request.cwpage"]["cartErrors"] = '';
// determine which columns to show 
if (!isset($_ENV["application.cw"]["taxDisplayLineItem"])) $_ENV["application.cw"]["taxDisplayLineItem"] = false;
if (!isset($_ENV["application.cw"]["discountDisplayLineItem"])) $_ENV["application.cw"]["discountDisplayLineItem"] = false;
if (!isset($_ENV["application.cw"]["taxChargeOnShipping"])) $_ENV["application.cw"]["taxChargeOnShipping"] = false;
if (!isset($_ENV["application.cw"]["shipDisplayInfo"])) $_ENV["application.cw"]["shipDisplayInfo"] = true;
// default country can be set in admin, used for calculations if customer selection not available 
if (!isset($_ENV["application.cw"]["defaultCountryID"])) $_ENV["application.cw"]["defaultCountryID"] = 0;
// set request values for control of display 
$_ENV["request.cwpage"]["shipDisplayInfo"] = $_ENV["application.cw"]["shipDisplayInfo"];
if ($module_settings["show_tax_total"]) {
	$_ENV["request.cwpage"]["taxDisplayLineItem"] = $_ENV["application.cw"]["taxDisplayLineItem"];
} else {
	$_ENV["request.cwpage"]["taxDisplayLineItem"] = false;
}
// if discounts are enabled, and at least one discount is applied 
if ($_ENV["application.cw"]["discountsEnabled"] && isset($module_settings["cart"]["carttotals"]["cartDiscounts"]) && $module_settings["cart"]["carttotals"]["cartDiscounts"] > 0) {
	$_ENV["request.cwpage"]["discountDisplayLineItem"] = $_ENV["application.cw"]["discountDisplayLineItem"];
} else {
	$_ENV["request.cwpage"]["discountDisplayLineItem"] = false;
}
$_ENV["request.cwpage"]["taxChargeOnShipping"] = $_ENV["application.cw"]["taxChargeOnShipping"];
// number of columns 
$_ENV["request.cwpage"]["cartColumnCount"] = 4;
// tax adds 2 columns 
if ($_ENV["request.cwpage"]["taxDisplayLineItem"]) {
	$_ENV["request.cwpage"]["cartColumnCount"] = $_ENV["request.cwpage"]["cartColumnCount"] + 2;
}
// discount adds a column 
if ($_ENV["request.cwpage"]["discountDisplayLineItem"]) {
	$_ENV["request.cwpage"]["cartColumnCount"] = $_ENV["request.cwpage"]["cartColumnCount"] + 1;
}
// remove checkbox adds a column 
if ($module_settings["display_mode"] == 'showcart') {
	$_ENV["request.cwpage"]["cartColumnCount"] = $_ENV["request.cwpage"]["cartColumnCount"] + 1;
}
$myDir = getcwd();
chdir(dirname(__FILE__));
// global functions 
require_once("../inc/cw-inc-functions.php");
// clean up form and url variables 
require_once("../inc/cw-inc-sanitize.php");
chdir($myDir);
// verify cart structure is valid, and ID matches client's session 
if (is_array($module_settings["cart"]) && isset($module_settings["cart"]["cartID"]) && $module_settings["cart"]["cartID"] == $_SESSION["cwclient"]["cwCartID"]) {
	$moduleCart = $module_settings["cart"];
// get cart if not already defined 
} else {
	// set defaults for tax region and country 
	if (!(isset($_SESSION["cwclient"]["cwTaxRegionID"]) && is_numeric($_SESSION["cwclient"]["cwTaxRegionID"]))) {
		$_SESSION["cwclient"]["cwTaxRegionID"] = 0;
	}
	// country can be set by default in admin, client selection/login overrides 
	if (!(isset($_SESSION["cwclient"]["cwTaxCountryID"]) && is_numeric($_SESSION["cwclient"]["cwTaxCountryID"]) && $_SESSION["cwclient"]["cwTaxCountryID"] > 0) && $_ENV["application.cw"]["taxUseDefaultCountry"]) {
		$_SESSION["cwclient"]["cwTaxCountryID"] = $_ENV["application.cw"]["defaultCountryID"];
	}
	// get new cart structure 
	$moduleCart = CWgetCart();
}
// /end get cart 
// cart defaults 
if (!isset($moduleCart["carttotals"]["productCount"])) $moduleCart["carttotals"]["productCount"] = 0;
if (!isset($moduleCart["carttotals"]["total"])) $moduleCart["carttotals"]["total"] = 0;
if (!isset($moduleCart["carttotals"]["itemCount"])) $moduleCart["carttotals"]["itemCount"] = 0;
if (!isset($moduleCart["cartitems"])) $moduleCart["cartitems"] = array();
// if cart is empty, set alert message - usually overridden by containing page 
if ($module_settings["display_mode"] == 'showcart' && $moduleCart["carttotals"]["itemCount"] == 0 && !strlen(trim($_ENV["request.cwpage"]["cartAlert"]))) {
	$_ENV["request.cwpage"]["cartAlert"] = "Cart is Empty";
}

// HANDLE FORM SUBMISSION 
// // UPDATE CART // 
if (isset($_POST["action"]) && $_POST["action"] == "update") {
	$valuesChanged = false;
	// handle deleted items 
	if (isset($_POST["remove"])) {
		$local_module_settings = $module_settings;
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		$module_settings = array(
			"cart_action" => "delete",
			"sku_unique_id" => implode(",", $_POST["remove"]));
		include("cw-mod-cartweaver.php");
		unset($module_settings);
		chdir($myDir);
		$module_settings = $local_module_settings;
	}
	// handle updated quantities 
	$unique_id_list = '';
	$sku_qty_list = '';
	// build list of unique id markers 
	for ($ii = 0; $ii < $_POST["productCount"]; $ii++) {
		$itemID = $_POST["sku_unique_id".$ii];
		$itemQty = $_POST["qty".$ii];
		if (!(is_numeric($itemQty) && $itemQty > 0)) {
			$itemQty = 0;
		}
		if (strlen($unique_id_list)) $unique_id_list .= ",";
		$unique_id_list .= $itemID;
		if (strlen($sku_qty_list)) $sku_qty_list .= ",";
		$sku_qty_list .= $itemQty;
	}
	// update all at once 
	$local_module_settings = $module_settings;
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array(
		"cart_action" => "update",
		"sku_unique_id" => $unique_id_list,
		"sku_qty" => $sku_qty_list);
	include("cw-mod-cartweaver.php");
	unset($module_settings);
	chdir($myDir);
	$module_settings = $local_module_settings;
	$valuesChanged = true;
	// reset any stored ship total, since quantities may have changed 
	if ($_SESSION["cwclient"]["cwCustomerID"] > 0) {
		$_SESSION["cwclient"]["cwShipTotal"] = 0;
	}
	// handle custom info changes 
	for ($ii = 0; $ii < $_POST["productCount"]; $ii++) {
		// if a value is passed in, and this sku is not already set to be removed 
		if (isset($_POST["customInfo".$ii]) && !(isset($_POST["remove"]) && ListFind(trim($_POST["remove"]), $_POST["sku_unique_id".$ii]))) {
			$customInfoStr = $_POST["customInfo".$ii];
			// check to see if string is different 
			$customInfoArr = explode("-", $_POST["sku_unique_id".$ii]);
			$oldStr = CWgetCustomInfo($customInfoArr[sizeof($customInfoArr)-1]);
			// if string is new, and we have a valid persisted quantity 
			if ($customInfoStr != $oldStr && is_numeric($_POST["qty".$ii]) && $_POST["qty".$ii] != 0) {
				// delete existing item from cart, hide alerts 
				$local_module_settings = $module_settings;
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				$module_settings = array(
					"cart_action" => "delete",
					"sku_unique_id" => $_POST["sku_unique_id".$ii],
					"alert_removed" => false);
				include("cw-mod-cartweaver.php");
				unset($module_settings);
				chdir($myDir);
				$module_settings = $local_module_settings;
				// add new custom info item, hide alerts 
				$local_module_settings = $module_settings;
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				$module_settings = array(
					"cart_action" => "add",
					"sku_id" => $customInfoArr[0],
					"sku_qty" => $_POST["qty".$ii],
					"sku_custom_info" => $customInfoStr,
					"alert_added" => false);
				include("cw-mod-cartweaver.php");
				unset($module_settings);
				chdir($myDir);
				$module_settings = $local_module_settings;
				// set marker for change value 
				$valuesChanged = true;
			}
		}
	}
	// if updates were made, show confirmation 
	if ($valuesChanged) {
		if ($_ENV["request.cwpage"]["cartConfirm"]) $_ENV["request.cwpage"]["cartConfirm"] .= ",";
		$_ENV["request.cwpage"]["cartConfirm"] .= 'Cart updates saved';
		// clear stored shipping total 
		$_SESSION["cwclient"]["cwShipTotal"] = 0;
	}
	// remove marker for order confirmed in checkout 
	$_SESSION["cw"]["confirmCart"] = false;
	// set message into session 
	$_SESSION["cw"]["cartConfirm"] = $_ENV["request.cwpage"]["cartConfirm"];
	// reload page 
	header("Location: ".$_ENV["request.cw"]["thisPage"]);
	exit;
}
// /END UPDATE CART 

// HANDLE PROMO CODE 
if ($_ENV["application.cw"]["discountsEnabled"] && isset($_POST["promocode"]) && strlen(trim($_POST["promocode"]))){
	// refresh list of available promo codes if it doesn't already exist 
	if (!(isset($_ENV["application.cw"]["discountData"]["promocodes"]))){
		$discountQuery = CWgetDiscountData(true);
	}
	// get all active discounts 
	unset($_ENV["application.cw"]["discountData"]);
	if ($_ENV["application.cw"]["discountsEnabled"] && !(isset($_ENV["application.cw"]["discountData"]["activeDiscounts"]))){
		$discountQuery = CWgetDiscountData(true);
	}
	// verify promo code matches an active discount 
	$promoCodeArr = $_ENV["application.cw"]["discountData"]["promocodes"];
	if(!(is_array($promoCodeArr))) $promoCodeArr = array();
	if (in_array(trim($_POST["promocode"]), $promoCodeArr)){
		$discountData = CWmatchPromoCode(trim($_POST["promocode"]),$moduleCart,$module_settings["customer_id"]);
		// if we have a match 
		if ($discountData["discount_match_status"]){
			$_ENV["request.cwpage"]["promoresponse"] = "Promo code ". trim($_POST["promocode"]) ." applied";
			// add to list if not already in the list 
			if (!(in_array(trim($_POST["promocode"]), $module_settings["promocode"]))){
				array_push($module_settings["promocode"],trim($_POST["promocode"]));
			}
		// if no match, show response message 
		} else if (isset($discountData["discount_match_response"]) && strlen(trim($discountData["discount_match_response"]))){
			$_ENV["request.cwpage"]["promoresponse"] = $discountData["discount_match_response"];
		}
		// get new cart structure (refresh cart w/ new discount) 
		$moduleCart = CWgetCart(null,null,null,null,null,null,$_POST['promocode']);
		// if not a valid promo code, show message to user 
	} else {
		// set message into page request for display below 
		$_ENV["request.cwpage"]["promoresponse"] = 'Promo code '. trim($_POST["promocode"]).' not available';
	}
	// if entered during checkout final step, client must reconfirm cart 
	if (!(strlen(trim($_ENV["request.cwpage"]["promoresponse"]))) && !(isset($_SESSION["cw"]["confirmCart"])) && $_SESSION["cw"]["confirmCart"]){
		$_SESSION["cw"]["confirmCart"] = false;
		header("Location: ".$_ENV["request.cw"]["thisPage"]."");
		exit;
	}

}
// /END PROMO CODE 

// handle stock alerts 
if($_ENV["application.cw"]["appEnableBackOrders"] != true) {
	// if quantity has changed, show alert, mark the row below by ID 
	for($cartLine = 0; $cartLine < count($moduleCart["cartitems"]); $cartLine++) {
		// get info struct for each item in cart 
		$moduleCartItem = $moduleCart["cartitems"][$cartLine];            
		// check cart item quantity against live total in database 
		$availQty = CWgetSkuQty($moduleCartItem["skuID"]);
		// if less available 
		if(is_numeric($availQty) && $availQty < $moduleCartItem["quantity"]) {
			// update in cart table
			$updateQty = CWcartUpdateItem($moduleCartItem["skuUniqueID"],$availQty);	
			// change in cart object  
			$moduleCart["cartitems"][$cartLine]["quantity"] = $availQty;
			// set up alert 
			if(strlen(trim($_ENV["request.cwpage"]["stockAlertIDs"]))) $_ENV["request.cwpage"]["stockAlertIDs"] .= ',';
			$_ENV["request.cwpage"]["stockAlertIDs"] .= $moduleCartItem['skuID'];
			$alertMsg = 'Quantity of some items has changed: totals adjusted';
			if(stristr($_ENV["request.cwpage"]["cartAlert"],$alertMsg) == false) {
				if(strlen(trim($_ENV["request.cwpage"]["cartAlert"]))) $_ENV["request.cwpage"]["cartAlert"] .= ',';
				$_ENV["request.cwpage"]["cartAlert"] .= $alertMsg;
			}
		}
	}
}
// /end stock alerts 

// if custom info can be edited, include script for show/hide links 
if($_ENV["application.cw"]["appDisplayCartCustomEdit"] && $module_settings["display_mode"] == 'showcart') {
	$editInfoScript='';
	$editInfoScript .= '<script type="text/javascript">
	jQuery(document).ready(function() {'."
		jQuery('span.CWcartChangeInput').hide();
		jQuery('span.CWcartChangeLink').show();
		jQuery('form#formUpdateCart a.CWchangeInfo').click(function() {jQuery(this).parents('span.CWcartChangeLink').hide().next('span.CWcartChangeInput').show().parents('span.CWcartCustomInfo').prev('span.CWcartCustomValue').hide();
			return false;
		});
	});
	</script>";
	CWinsertHead($editInfoScript);
}

// CALCULATE ORDER TOTALS 

// shipping totals 
if(isset($_SESSION["cwclient"]["cwShipCountryID"]) && $_SESSION["cwclient"]["cwShipCountryID"] > 0 && $_ENV["application.cw"]["shipEnabled"]) {    
	// if customer has confirmed shipping 
	if(isset($_SESSION["cw"]["confirmShipID"]) &&  $_SESSION["cw"]["confirmShipID"] > 0) {
		// if we don't have a valid rate stored yet 
		if(!(isset($_SESSION["cwclient"]["cwShipTotal"])) && $_SESSION["cwclient"]["cwShipTotal"]  > 0) {
			$shipVal = CWgetShipRate($_SESSION["cw"]["confirmShipID"],$moduleCart["cartID"],CWgetShipMethodCalctype($_SESSION["cw"]["confirmShipID"]));
		} else {
			$shipVal = $_SESSION["cwclient"]["cwShipTotal"];
		}
	} else {
		$shipVal = 0;
	}
	
	// subtract shipping discounts 
	if (!(isset($moduleCart["carttotals"]["shipOrderDiscounts"]) && is_numeric($moduleCart["carttotals"]["shipOrderDiscounts"]) && $moduleCart["carttotals"]["shipOrderDiscounts"] > 0)){
		$moduleCart["carttotals"]["shipOrderDiscounts"] = 0;
	}
	if (!(isset($moduleCart["carttotals"]["shipItemDiscounts"]) && is_numeric($moduleCart["carttotals"]["shipItemDiscounts"]) && $moduleCart["carttotals"]["shipItemDiscounts"] > 0)){
			$moduleCart["carttotals"]["shipItemDiscounts"] = 0;
	}
	// combine total of shipping discounts 
	$moduleCart["carttotals"]["shipDiscounts"] = $moduleCart["carttotals"]["shipOrderDiscounts"] + $moduleCart["carttotals"]["shipItemDiscounts"];

	// if value returned from ship rate function is numeric (no errors) 
	if (is_numeric($shipVal)){
		// cannot be greater than the shipping total 
		if ($moduleCart["carttotals"]["shipDiscounts"] > $shipVal){
			$moduleCart["carttotals"]["shipDiscounts"] = $shipVal;
		}
		// resulting total cannot be less than 0 
		$shipTotal = max(0,$shipVal - $moduleCart["carttotals"]["shipDiscounts"]);
		// set cart shipping total, store in client scope 
		if (is_numeric($shipVal) && $shipVal > 0){
			$_SESSION["cwclient"]["cwShipTotal"] = $shipVal;
			$moduleCart["carttotals"]["shipping"] = number_format($shipTotal,2);
		} else {
			$moduleCart["carttotals"]["shipping"] = 0;
			$_SESSION["cwclient"]["cwShipTotal"] = 0;
		}
		// shipping tax 
		if($_ENV["request.cwpage"]["taxChargeOnShipping"] && strtolower($_ENV["application.cw"]["taxCalctype"]) == 'localtax') {	
			$shipTaxVal = CWgetShipTax($_SESSION["cwclient"]["cwShipCountryID"],$_SESSION["cwclient"]["cwShipRegionID"],$moduleCart["carttotals"]["shipping"],$moduleCart);
		} else if (isset($_ENV["request.cwpage"]["cartShipTaxTotal"])) {
			$shipTaxVal = $_ENV["request.cwpage"]["cartShipTaxTotal"];
		} else {
			$shipTaxVal = 0;
		}
		$moduleCart["carttotals"]["shippingTax"] = number_format($shipTaxVal,2);
		$_SESSION["cwclient"]["cwShipTaxTotal"] = $moduleCart["carttotals"]["shippingTax"];
		$_SESSION["cwclient"]["cwTaxTotal"] = $moduleCart["carttotals"]["tax"];
		// /end shipping tax 
		// add shipping amounts to cart cost total 
		$moduleCart["carttotals"]["total"] += $moduleCart["carttotals"]["shipping"] + $moduleCart["carttotals"]["shippingTax"];
	// if the shipping rate is not numeric, we have an error 
	} else {
		$moduleCart["carttotals"]["shipping"] = 0;
		// set page message shown to customer 
		if (strlen(trim($shipVal))){
			$_ENV["request.cwpage"]["shipRateError"] = trim($shipval);
		}
	}
	// /end numeric value check 
}
// /end shipping totals 

// set order total into client scope 
$_SESSION["cwclient"]["cwOrderTotal"] = $moduleCart["carttotals"]["total"];
// get existing payments for this order 
if($module_settings["show_payment_total"] && isset($_SESSION["cwclient"]["cwCompleteOrderID"]) && $_SESSION["cwclient"]["cwCompleteOrderID"] > 0) {
	$orderPayments = CWorderPaymentTotal($_SESSION["cwclient"]["cwCompleteOrderID"]);
} else {
	$orderPayments = 0;			
}
// deduct existing payments 
$tempTotal = $moduleCart["carttotals"]["total"];
if($module_settings["show_payment_total"]) {
	$tempTotal -= $orderPayments;
}
// //////////// 
// START OUTPUT 
// //////////// 

// TOTALS ONLY 
if($module_settings["display_mode"] == 'totals') {
?>	
<p>
	<?php
    // discounts 
    if($module_settings["show_discount_total"] && $_ENV["application.cw"]["discountsEnabled"] && $moduleCart["carttotals"]['cartDiscounts'] > 0) {
    ?>	
    <span class="label">Item Total:</span><span class="CWtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["sub"] + $moduleCart["carttotals"]['cartDiscounts'],'local');?> </span><br>
    <span class="label CWdiscountText">Discounts:</span><span class="CWtotalText CWdiscountText">-<?php echo cartweaverMoney($moduleCart["carttotals"]['cartDiscounts'],'local');?> </span><br>
	<?php
    }
    // cart subtotal ?>
	<span class="label CWsubtotalText">Subtotal:</span><span class="CWtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["sub"],'local');?> </span>
	<?php
    // edit cart link 
    if(strlen(trim($module_settings["edit_cart_url"])) && !($module_settings["display_mode"] == 'showcart')) {
    ?>	
    <span class="CWeditLink">
        &raquo;&nbsp;<a href="<?php echo $module_settings["edit_cart_url"]; ?>">Edit Cart</a>
    </span>
	<?php
    }
    ?>
	<br>
	<?php
    // tax total 
    if($module_settings["show_tax_total"]) {
    ?>	
	<span class="label"><?php echo $_ENV["application.cw"]["taxSystemLabel"];?>:</span><span class="CWtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["tax"],'local');?> </span><br>
	<?php
    }
    // shipping total if shipping is selected 
    if($module_settings["show_ship_total"]) {
        if($_ENV["application.cw"]["shipEnabled"] && isset($_SESSION["cw"]["confirmShipID"]) && $_SESSION["cw"]["confirmShipID"] >  0 && isset($moduleCart["carttotals"]["shipping"])) {
		?>
        	<?php // shipping base cost ?>
	<span class="label">Shipping/Handling:</span><span class="CWtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["shipping"] + $moduleCart["carttotals"]['shipDiscounts'],'local'); ?> </span>
<?php
        // shipping discounts 
			if($_ENV["application.cw"]["discountsEnabled"] && $moduleCart["carttotals"]['shipDiscounts'] > 0 && $module_settings["show_discount_total"]) {
			?>	
    <br><span class="label CWdiscountText">Shipping Discounts:</span><span class="CWdiscountText CWtotalText">-<?php echo cartweaverMoney($moduleCart["carttotals"]['shipDiscounts'],'local'); ?> </span>
    <br><span class="label CWsubtotalText">Shipping Total:</span><span class="CWsubtotalText CWtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["shipping"],'local');?> </span>
			<?php
            }
            ?>
    <br>
			<?php
            // shipping value message (if error or other text returned for ship total above) 
            if(isset($_ENV["request.cwpage"]["shipRateError"])) {	
            ?>	
    <span class="label">&nbsp;</span><span class="CWtotalText"><?php echo $_ENV["request.cwpage"]["shipRateError"]; ?> </span><br>
			<?php
            }
            // shipping tax 
            if(isset($moduleCart["carttotals"]["shippingTax"]) && $moduleCart["carttotals"]["shippingTax"] > 0 && $_ENV["request.cwpage"]["taxChargeOnShipping"]  && $module_settings["show_tax_total"]) {
            ?>	
    <span class="label">Shipping <?php echo $_ENV["application.cw"]["taxSystemLabel"];?>:</span><span class="CWtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["shippingTax"],'local'); ?> </span><br>
		<?php
			} else {	
				$moduleCart["carttotals"]["shippingTax"] = 0;
			}
		}
	}
	?>
</p>
	<?php
    // BALANCE / PAYMENT TOTALS 
    // show original total 
    if($module_settings["show_order_total"]) {
    ?>	
<p class="CWtotal">
    <span class="label CWsubtotalText">Order Total:</span><span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["total"],'local'); ?> </span>
</p>
		<?php
        // show payments total 
        if($orderPayments > 0 && $module_settings["show_payment_total"]) {
        ?>	
<p class="CWtotal">
    <span class="label CWsubtotalText">Payments Made:</span><span class="CWtotalText CWsubtotalText">-<?php echo cartweaverMoney($orderPayments,'local');?> </span>
</p>
<?php // balance due ?>
<p class="CWtotal">
	<span class="label CWsubtotalText">Balance Due:</span><span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney($tempTotal,'local'); ?> </span>
</p>
		<?php
        }
    }
    // /end Totals 
// FULL CONTENT (showcart | summary) 
} else {
	// if showcart mode, wrap table in form to handle cart updates 
	if($module_settings["display_mode"] == 'showcart') {
?>	
<form name="updatecart" action="<?php echo trim($_ENV["application.cw"]["appCWStoreRoot"]).$_ENV["request.cw"]["thisPage"];?>" method="post" id="formUpdateCart">
<?php
	}
	// CART PRODUCTS TABLE 
	// products in cart ?>
	<table class="CWtable" id="CWcartProductsTable">
		<thead>
			<?php // alert row ?>
			<tr class="fadeOut CWalertRow">
				<?php // dynamic column span, depends on discounts or taxes on each line ?>
				<td colspan="<?php echo $_ENV["request.cwpage"]["cartColumnCount"];?>" class="noPad">
<?php
	if(isset($_ENV["request.cwpage"]["cartAlert"]) && strlen(trim($_ENV["request.cwpage"]["cartAlert"]))) {
?>			
                    <div class="CWalertBox alertText">
                        <div><?php echo str_replace(',','</div><div>',$_ENV["request.cwpage"]["cartAlert"]);?></div>
                    </div>
<?php
	}
	if(isset($_ENV["request.cwpage"]["cartConfirm"]) && strlen(trim($_ENV["request.cwpage"]["cartConfirm"]))) {
?>
                    <div class="CWconfirmBox confirmText">
                        <div><?php echo str_replace(',','</div><div>',$_ENV["request.cwpage"]["cartConfirm"]);?></div>
                    </div>
<?php
	}
?>
                </td>
            </tr>
			<?php // table headers ?>
            <tr class="headerRow">
                <?php // product name ?>
                <th>Item</th>
                <?php // quantity ?>
                <th class="center">Qty.</th>
                <?php // price ?>
                <th class="CWleft">Price</th>
                <?php // discounts ?>
<?php
	if($_ENV["request.cwpage"]["discountDisplayLineItem"]) {
?>	
				<th class="CWleft">Discount</th>
<?php
	}
?>
				<?php // taxes
	if($_ENV["request.cwpage"]["taxDisplayLineItem"]) {    
?> 
                <th>Subtotal</th>
                <th class="CWleft"><?php echo $_ENV["application.cw"]["taxSystemLabel"];?></th>
<?php
	}
	// total ?>
				<th class="CWleft">Total</th>		
<?php
	if($module_settings["display_mode"] == 'showcart') {    
?>  
             	<th class="center notBold smallPrint">
                	<input type="checkbox" rel="checkAllRemove" name="checkAllProducts" class="checkAll" tabindex="1">Remove</th>       
<?php	
	}
?>
			</tr>
        </thead>
		<tbody>
<?php 
    for($cartLine = 0; $cartLine < count($moduleCart["cartitems"]); $cartLine++) {
        // get info struct for each item in cart  
        $moduleCartItem = $moduleCart["cartitems"][$cartLine];
        // get image for item ( add to cart item info )
        if($module_settings["show_images"]) {
            $moduleCartItem["cartImg"] = CWgetImage($moduleCartItem["ID"],4,$_ENV["application.cw"]["appImageDefault"]);
        } else {
            $moduleCartItem["cartImg"] = '';
        }	
        // url for linked info 
        $moduleCartItem["itemurl"] = $_ENV["request.cwpage"]["urlDetails"].'?product='.$moduleCartItem["ID"];
        $rowClass = 'itemRow row-'.$cartLine;
        if(isset($_ENV["request.cwpage"]["stockAlertIDs"]) && ListFindNoCase($_ENV["request.cwpage"]["stockAlertIDs"],$moduleCartItem['skuUniqueID'])) {
            $rowClass = $rowClass . ' stockAlert';
        }
        if(isset($_ENV["request.cwpage"]["cartConfirmIDs"]) && ListFindNoCase($_ENV["request.cwpage"]["cartConfirmIDs"],$moduleCartItem['skuUniqueID'])) {
            $rowClass = $rowClass . ' cartConfirm';
        }
?>
            <tr class="<?php echo $rowClass;?>">
                <?php // product name, image, options ?>
                <td class="productCell">
                    <?php // product image
		if(strlen(trim($moduleCartItem["cartImg"]))) {          
?>	
					<div class="CWcartImage">
<?php 
			if($module_settings["link_products"]) {
?>
						<a href="<?php echo $moduleCartItem["itemurl"]; ?>" title="View Product"><img src="<?php echo $moduleCartItem["cartImg"];?>" alt="<?php echo $moduleCartItem["Name"]; ?>"></a>
<?php
			} else {
?>
                        <img src="<?php echo $moduleCartItem["cartImg"]; ?>" alt="<?php echo $moduleCartItem["Name"];?>">
<?php
			}
?>
					</div>
<?php
		}
		// product name ?>
                    <div class="CWcartItemDetails">
                        <span class="CWcartProdName">
<?php
		if($module_settings["link_products"]) {
?>	
                           	<a href="<?php echo $moduleCartItem["itemurl"]; ?>" title="View Product" class="CWlink"><?php echo $moduleCartItem["Name"]; ?></a>	
<?php
		} else {
			echo $moduleCartItem["Name"];
		}
		if($module_settings["show_sku"]) {
?>	
                            <br><span class="CWcartSkuName">(<?php echo $moduleCartItem["merchSkuID"];?>)</span>
<?php
		}
?>
						</span>
			<?php // sku options
		if(count($moduleCartItem["options"]) > 0 && $module_settings["show_options"]) {                
			// sort the array 
			for($outer = 0; $outer < count($moduleCartItem["options"]); $outer++) {
				for($inner = 0; $inner < count($moduleCartItem["options"])-1; $inner++ ) {
					// if sort comes first 
					if(floatval($moduleCartItem["options"][$inner]["Sort"]) < floatval($moduleCartItem["options"][$outer]["Sort"])) {           
						$tempVar = $moduleCartItem["options"][$inner];
						$moduleCartItem["options"][$inner] = $moduleCartItem["options"][$outer];
						$moduleCartItem["options"][$outer] = $tempVar;	
						// if not by sort, by name 
					} else if(floatval($moduleCartItem["options"][$inner]["Sort"]) == floatval($moduleCartItem["options"][$outer]["Sort"]) && strtolower($moduleCartItem["options"][$inner]["Name"]) > strtolower($moduleCartItem["options"][$outer]["Name"])) {
						$tempVar = $moduleCartItem["options"][$inner];
						$moduleCartItem["options"][$inner] = $moduleCartItem["options"][$outer];
						$moduleCartItem["options"][$outer] = $tempVar;	
					}
				}
			}
			$displayOptions = $moduleCartItem["options"];
			// loop the sorted array, show each option with its value 
			for($optionNumber = 0; $optionNumber < count($displayOptions);$optionNumber++) {
?>
                        <div class="CWcartOption">
                            <span class="CWcartOptionName"><?php echo $displayOptions[$optionNumber]["Name"]; ?>:</span>
                            <span class="CWcartOptionValue"><?php echo $displayOptions[$optionNumber]["Value"]; ?></span>
                        </div>
<?php
			}
		}
		// custom value 
		if($moduleCartItem["skuID"] != $moduleCartItem["skuUniqueID"] && $_ENV["application.cw"]["appDisplayCartCustom"]  && $module_settings["show_options"]) {
			$newVarForList1 = explode('-',$moduleCartItem["skuUniqueID"]);
			$phraseID = $newVarForList1[count($newVarForList1) - 1];
			$phraseText = CWgetCustomInfo($phraseID);
			
			// length of text to show before trimming 
			$trimLength = 20; 
			if(strlen(trim($phraseText))) {
?>	
						<div class="CWcartOption">
<?php
				if(strlen(trim($moduleCartItem["customInfoLabel"]))) {                 
?>
							<span class="CWcartCustomLabel"><?php echo $moduleCartItem["customInfoLabel"];?>:</span>
<?php
				}
?>
                            <span class="CWcartCustomValue"><?php
				echo substr(trim($phraseText),0,$trimLength);
				if(strlen(trim($phraseText)) > $trimLength) {
					echo '...';
				}
?></span>
<?php // if allowed to edit 
				if($_ENV["application.cw"]["appDisplayCartCustomEdit"] && $module_settings["display_mode"] == 'showcart') { 
?>
                            <span class="CWcartCustomInfo">
                                <span class="CWcartChangeLink" style="display:none;">[<a href="#" class="CWchangeInfo">x</a>]</span>
                                <span class="CWcartChangeInput">
                                    <input type="text" name="customInfo<?php echo $cartLine;?>" class="custom" size="22" value="<?php echo ($phraseText);?>" maxlength="255">
                                </span>
                            </span>
<?php
				}
?>
						</div>
<?php
			}
		}
		// free shipping message 
		if ($_ENV["application.cw"]["appDisplayFreeShipMessage"] && (!$moduleCartItem["shipCharge"] || (isset($moduleCartItem["shipDiscountsApplied"]["percent"]) && $moduleCartItem["shipDiscountsApplied"]["percent"] == 100))){
?>
    					<div class="CWcartOption CWshipText"><?php echo $_ENV["application.cw"]["appFreeShipMessage"]; ?></div>
<?php
		}
?>
					</div>
					<?php // hidden field for sku ?>
                    <div>
                        <input name="sku_unique_id<?php echo $cartLine; ?>" type="hidden" value="<?php echo $moduleCartItem["skuUniqueID"]; ?>">
                    </div>
                </td>
                <?php // qty ?>
                <td class="qtyCell center">
<?php
		if($module_settings["display_mode"] == 'showcart') {
?>		
                    <input name="qty<?php echo $cartLine;?>" type="text" value="<?php echo $moduleCartItem["quantity"];?>" size="2" onkeyup="this.value = extractNumeric(this.value)" class="qty">
                    <input name="qty_now<?php echo $cartLine;?>" type="hidden" value="<?php echo $moduleCartItem["quantity"];?>">
							
<?php
		} else {
			echo $moduleCartItem['quantity'];
		}
?>
                </td>
                <?php // price ?>
                <td class="priceCell">
                    <span class="CWcartPrice"><?php echo cartweaverMoney($moduleCartItem["price"],'local'); ?></span>
                </td>
                <?php // discounts
		if($_ENV["request.cwpage"]["discountDisplayLineItem"]) {
?>	
                <td class="priceCell">
                    <?php echo cartweaverMoney($moduleCartItem["discountAmount"],'local');?>
                </td>
<?php
		}
		// taxes (subtotal before tax, and the tax amount) 
		if($_ENV["request.cwpage"]["taxDisplayLineItem"]) {       
?>	
                <td class="priceCell"><?php echo cartweaverMoney($moduleCartItem["subTotal"],'local');?></td>
                <td class="priceCell"><?php echo cartweaverMoney($moduleCartItem["tax"],'local');?></td>	
<?php
		}
		// total ?>
                <td class="totalCell totalAmounts">
                    <span class="CWcartPrice"><?php echo cartweaverMoney($moduleCartItem["total"],'local');?></span>
                </td>
<?php
		if($module_settings["display_mode"] == 'showcart') {
?>
                <td class="checkboxCell center">
                    <?php // remove item checkbox ?>
                    <input name="remove[<?php echo $cartLine; ?>]" type="checkbox" class="formCheckbox checkAllRemove" value="<?php echo $moduleCartItem["skuUniqueID"];?>" rel="group1">
                </td>
<?php
		}
?>
			</tr>

	
<?php
if(isset($_SESSION["cwclient"]["cwShipCountryID"]) && $_SESSION["cwclient"]["cwShipCountryID"]==45 && $moduleCartItem["ID"]==126)  
echo "<div class='countryalert'><span style='color:#981B1E;'><strong>ALERT! Creatine cannot be shipped to Canada.  Please remove it from your cart to continue.</strong></span><br /></div>"
?>		
			
<?php
if(isset($_SESSION["cwclient"]["cwShipCountryID"]) && $_SESSION["cwclient"]["cwShipCountryID"]==45 && $moduleCartItem["ID"]==129)  
echo "<div class='countryalert'><span style='color:#981B1E;'><strong>ALERT! Herbal Energy cannot be shipped to Canada.  Please remove it from your cart to continue.</strong></span><br /><div class='countryalert'>"
?>				
			
			
			
			
			
			
<?php
	}
	// /end product rows 
	
	// TOTALS ROW 
	if($module_settings["show_total_row"] || $module_settings["show_discount_descriptions"]) {
?>	
            <tr class="totalRow">
                <td>
<?php
		// continue shopping 
		if($module_settings["show_continue"]) {
			if(isset($_ENV["request.cwpage"]["returnUrl"]) && strlen(trim($_ENV["request.cwpage"]["returnUrl"]))) {
?>
                    <p class="CWcontShop">
                        &raquo;&nbsp;<a href="<?php echo $_ENV["request.cwpage"]["returnUrl"];?>">Continue Shopping</a>
                    </p>
<?php
			}
		}
		// applied discount descriptions 
		if ($module_settings["show_discount_descriptions"]){
			// reset description list 
			$_ENV["request.cwpage"]["discountDescriptions"] = array();
			// loop list of applied discounts 
			$daArr = $_ENV["request.cwpage"]["discountsApplied"];
			if (!is_array($daArr) && strlen(trim($daArr))) $daArr = explode(",", $daArr);
			else if (!is_array($daArr)) $daArr = array();
			foreach ($daArr as $key => $d) {
				if (strlen(trim($d))) {
					// lookup description 
					$discountDescription = CWgetDiscountDescription($d);
					// if description exists, add it to a list 
					if (strlen(trim($discountDescription))) {
						$_ENV["request.cwpage"]["discountDescriptions"][] = trim($discountDescription);
					}
				}
			}
			// if we have descriptions to show 
			if (count($_ENV["request.cwpage"]["discountDescriptions"])){
?>
                    <div class="CWcartDiscounts">
                    	<p class="CWdiscountHeader">Discounts applied to this order:</p>
<?php
				foreach ($_ENV["request.cwpage"]["discountDescriptions"] as $key => $ii) {
					if (strlen(trim($ii))) {
						echo '<p>'.$ii.'</p>';
					}
				}
?>
                    </div>
<?php
			}
		}
?>
    			</td>
                <?php // text labels for totals ?>
                <td colspan="<?php echo $_ENV["request.cwpage"]["cartColumnCount"] - 3; ?>" class="CWright totalCell">
<?php
		if ($module_settings["show_total_row"]) {
			// discounts / subtotal 
			if($module_settings["show_discount_total"] && $_ENV["application.cw"]["discountsEnabled"] && $moduleCart["carttotals"]["cartDiscounts"] > 0) {
?>
                    <span class="label">Item Total:</span><br>
                    <span class="label CWdiscountText">Discounts: </span><br>
<?php
			}
			// subtotal label ?>
					<span class="label CWsubtotalText">Subtotal: </span>
<?php
			// tax label 
			if($module_settings["show_tax_total"]) {
?>
                    <br><span class="label"><?php echo $_ENV["application.cw"]["taxSystemLabel"];?>: </span>
<?php
			}
			// shipping label 
			if($_ENV["application.cw"]["shipEnabled"] && isset($_SESSION["cw"]["confirmShipID"]) && $_SESSION["cw"]["confirmShipID"] > 0 && isset($moduleCart["carttotals"]["shipping"])) {
				if($module_settings["show_ship_total"]) {
?>	
                    <br><span class="label">Shipping/Handling: </span>
<?php
					// shipping discounts 
					if($_ENV["application.cw"]["discountsEnabled"] && $moduleCart["carttotals"]["shipDiscounts"] > 0 && $module_settings["show_discount_total"]) {        
?>
                    <br /><span class="label CWdiscountText">Shipping Discounts: </span>
                    <br /><span class="label CWsubtotalText">Shipping Total: </span>
<?php
					}
					// shipping tax 
					if(isset($moduleCart["carttotals"]["shippingTax"]) && $moduleCart["carttotals"]["shippingTax"] > 0 && $_ENV["request.cwpage"]["taxChargeOnShipping"]) {
?>
                    <br><span class="label">Shipping <?php echo $_ENV["application.cw"]["taxSystemLabel"];?>: </span>
<?php
					}
				}
				// order total label 
				if($module_settings["show_order_total"]) {
?>	
                    <br><span class="label CWsubtotalText">Order Total: </span>
<?php
				}
			}
			// payment total 
			if($orderPayments > 0 && $module_settings["show_payment_total"]) {    
?>	
                    <br><span class="label CWsubtotalText">Payments Made: </span>
<?php 
			}
		}
?>
                </td>
                <?php // total amounts ?>
                <td class="totalCell">
<?php
		if($module_settings["show_total_row"]){
			// discounts / subtotal 
			if($module_settings["show_discount_total"] && $_ENV["application.cw"]["discountsEnabled"] && $moduleCart["carttotals"]['cartDiscounts'] > 0) {
?>
										<span class="CWtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["sub"] + $moduleCart["carttotals"]["cartDiscounts"],'local'); ?> </span><br>
										<span class="CWtotalText CWdiscountText">-<?php echo cartweaverMoney( $moduleCart["carttotals"]["cartDiscounts"],'local');?> </span><br>
<?php
			}
			// subtotal 
?>
				<span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney( $moduleCart["carttotals"]["sub"],'local'); ?> </span>
<?php
			if($module_settings["show_tax_total"]) {
				// tax total 
?>
				<br><span class="CWtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["tax"],'local'); ?> </span>
<?php
			}
			// if shipping is selected, shipping total 
			if($_ENV["application.cw"]["shipEnabled"] && isset($_SESSION["cw"]["confirmShipID"]) && $_SESSION["cw"]["confirmShipID"] > 0 && isset($moduleCart["carttotals"]["shipping"])) {
				if($module_settings["show_ship_total"]) {
					// shipping total 
?>
					<br><span class="CWtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["shipping"] +$moduleCart["carttotals"]["shipDiscounts"],'local'); ?></span>
<?php
					// shipping discounts 
					if($_ENV["application.cw"]["discountsEnabled"] && $moduleCart["carttotals"]["shipDiscounts"] > 0 && $module_settings["show_discount_total"]) {
?>	
                    <br><span class="CWtotalText CWdiscountText">-<?php echo cartweaverMoney($moduleCart["carttotals"]["shipDiscounts"],'local'); ?> </span>
                    <br><span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney($moduleCart["carttotals"]["shipping"],'local'); ?> </span>
<?php
					}
					// shipping tax 
					if(isset($moduleCart["carttotals"]["shippingTax"]) && $moduleCart["carttotals"]["shippingTax"] > 0 && $_ENV["application.cw"]["taxChargeOnShipping"]) {
?>
                    <br><span class="CWtotalText"><?php echo  cartweaverMoney($moduleCart["carttotals"]["shippingTax"],'local'); ?> </span>
<?php
					}
				}
				// complete order total (payment due amount) 
				if($module_settings["show_order_total"]) {
?>
                    <br><span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney($tempTotal,'local');?> </span>
<?php   
				}
			}
			// payment total 
			if($orderPayments > 0 && $module_settings["show_payment_total"]) {
?>
                    <br><span class="CWtotalText"><?php echo cartweaverMoney($orderPayments,'local');?> </span>
<?php 
			}
		}
?>
               	</td>
<?php
		if($module_settings["display_mode"] == 'showcart') {
?>	
                <td class="center">
                <?php // update button ?>
                <input name="updateCart" type="submit" class="CWformButtonSmall" id="update" value="Update">
                <input name="action" type="hidden" id="action" value="update">
                </td>
<?php
		}
?>
			</tr>
<?php
	}
?>	
      	</tbody>
	</table>
<?php
	// /end products table 
	if($module_settings["display_mode"] =='showcart') {
		// hidden input: number of products ?>
    <div>
        <input type="hidden" name="productCount" value="<?php echo count($moduleCart["cartitems"]);?>">
    </div>
</form>
<?php
	}
	
	// promocode input 
	if ($module_settings["show_promocode_input"]){
?>
	<div class="CWpromoCode">
		<form name="cartpromo" action="<?php echo trim($_ENV["application.cw"]["appCWStoreRoot"]).$_ENV["request.cw"]["thisPage"];?>" method="post" id="formCartPromo">
			<p>Enter promotional code: </p>
			<input type="text" name="promocode" id="CWpromocode" size="20" maxlength="255" value="">
			<input name="submitPromo" type="submit" class="CWformButtonSmall" id="CWsubmitPromo" value="Apply Code">
		</form>
<?php
    	if (isset($_ENV["request.cwpage"]["promoresponse"]) && strlen(trim($_ENV["request.cwpage"]["promoresponse"]))){
?>
		<div class="CWpromoResponse">
			<p><?php echo $_ENV["request.cwpage"]["promoresponse"]; ?></p>
		</div>
<?php
		}
?>
	</div>
<?php
	}
	// edit cart link 
	if(strlen(trim($module_settings["edit_cart_url"])) && !($module_settings["display_mode"] =='showcart')) {
?>	
    <p class="CWeditLink">
    &raquo;&nbsp;<a href="<?php echo $module_settings["edit_cart_url"]; ?>">Edit Cart</a>
    </p>
<?php
	}
}
// /END Display Mode 
?>