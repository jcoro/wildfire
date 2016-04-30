<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-shipdisplay.php
File Date: 2012-07-03
Description:
Creates and displays shipping details and selection options
NOTE: variables for each shipping method are set within that method's configuration file
all options are assembled and tracked in cw-func-init.php
==========================================================
*/
if (!isset($module_settings)) $module_settings = array();
// display mode (totals | select) 
if (!isset($module_settings["display_mode"])) $module_settings["display_mode"] = "select";
// edit shipping url (not used for select mode - blank = not shown) 
if (!isset($module_settings["edit_ship_url"])) $module_settings["edit_ship_url"] = $_ENV["request.cw"]["thisPage"]."?shipreset=1";
// show customer shipping address 
if (!isset($module_settings["show_address"])) $module_settings["show_address"] = true;
// edit address url (blank = not shown) 
if (!isset($module_settings["edit_address_url"])) $module_settings["edit_address_url"] = $_ENV["request.cw"]["thisPage"]."?custreset=1";
// get customer struct if not passed in 
if (!isset($module_settings["customer_data"])) $module_settings["customer_data"] = "";
// page for form base action 
if (!isset($_ENV["request.cwpage"]["hrefUrl"])) $_ENV["request.cwpage"]["hrefUrl"] = trim($_ENV["application.cw"]["appCWStoreRoot"]).$_ENV["request.cw"]["thisPage"];
$myDir = getcwd();
chdir(dirname(__FILE__));
// global functions 
include("../inc/cw-inc-functions.php");
// clean up form and url variables 
include("../inc/cw-inc-sanitize.php");
chdir($myDir);
// GET CUSTOMER INFO 
if ($module_settings["show_address"]) {
	// if customer data not passed in, get it here 
	if (!is_array($module_settings["customer_data"])) {
		// global queries
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		if (!function_exists(CWquerySelectProductDetails)) {
			include("../func/cw-func-query.php");
		}
		// customer functions
		if (!function_exists(CWgetCustomer)) {
			include("../func/cw-func-customer.php");
		}
		chdir($myDir);
		$CWcustomer = CWgetCustomer($_SESSION["cwclient"]["cwCustomerID"]);
	} else {
		$CWcustomer = $module_settings["customer_data"];
	}
}
// cart is a cart structure from CWcart function 
if (!isset($_SESSION["cwclient"]["cwCartID"])) $_SESSION["cwclient"]["cwCartID"] = 1;
if (!isset($module_settings["cart"])) $module_settings["cart"] = CWgetCart();
if (!isset($module_settings["cart"]["carttotals"]["shipOrderDiscounts"])) $module_settings["cart"]["carttotals"]["shipOrderDiscounts"] = 0;
// HANDLE SHIPPING FORM SUBMISSION 
if (!isset($_SESSION["cw"]["confirmShipID"])) $_SESSION["cw"]["confirmShipID"] = 0;
if (!isset($_SESSION["cw"]["confirmShipName"])) $_SESSION["cw"]["confirmShipName"] = "";
if (!isset($_SESSION["cw"]["confirmShip"])) $_SESSION["cw"]["confirmShip"] = false;
if (isset($_POST["selectShip"]) && $_POST["selectShip"] != 0) {
	$_SESSION["cw"]["confirmShipID"] = $_POST["selectShip"];
	$_SESSION["cw"]["confirmShipName"] = CWgetShipMethodName($_POST["selectShip"]);
	$_SESSION["cw"]["confirmShip"] = true;
	// refresh page 
	header("Location: ".$_ENV["request.cw"]["thisPageQS"]);
	exit;
}
// if shipreset exists in url, remove marker for selection 
if (isset($_GET["shipreset"]) && strlen(trim($_GET["shipreset"]))) {
	$_SESSION["cw"]["confirmShipID"] = 0;
	$_SESSION["cw"]["confirmShipName"] = "";
	$_SESSION["cw"]["confirmShip"] = false;
	$_SESSION["cwclient"]["cwShipTotal"] = 0;
// if shipconfirm exists in url, set method 
} else if (isset($_GET["shipconfirm"]) && is_numeric($_GET["shipconfirm"])) {
	$_SESSION["cw"]["confirmShipID"] = $_GET["shipconfirm"];
	$_SESSION["cw"]["confirmShip"] = true;
	header("Location: ".$_ENV["request.cw"]["thisPage"]);
	exit;
}
// QUERY: get all available shipping methods 
$allShipMethods = CWgetShipMethodDetails($_SESSION["cwclient"]["cwCartID"]);
// QUERY: get valid shipping options, set session values 
$shipMethodsQuery = CWgetShipMethodDetails($_SESSION["cwclient"]["cwCartID"],null,null,$_SESSION["cw"]["confirmShipID"]);
// if no methods available, set shippref to 0 
if ($shipMethodsQuery["totalRows"] == 0) {
	$_SESSION["cw"]["confirmShipID"] = 0;
	$_SESSION["cw"]["confirmShipName"] = "";
	$_SESSION["cw"]["confirmShip"] = false;
	$shipVal = 0;
	$_SESSION["cw"]["cwShipTotal"] = 0;
	// if only one method, set method id, trigger 'totals' mode 
} else if ($shipMethodsQuery["totalRows"] == 1) {
	$_SESSION["cw"]["confirmShipID"] = $shipMethodsQuery["ship_method_id"][0];
	$_SESSION["cw"]["confirmShipName"] = $shipMethodsQuery["ship_method_name"][0];
	// skip the single method (auto-confirm) 
	if (isset($_ENV["application.cw"]["shipDisplaySingleMethod"]) && $_ENV["application.cw"]["shipDisplaySingleMethod"] != true) {
		$_SESSION["cw"]["confirmShip"] = true;
	}
}
// get shipping total if method is confirmed 
if ($_SESSION["cw"]["confirmShip"] || $_SESSION["cw"]["confirmShipID"] > 0) {
	// if we don't have a valid rate yet 
	if (!(isset($_SESSION["cwclient"]["cwShipTotal"]) && $_SESSION["cwclient"]["cwShipTotal"] > 0)) {
		$shipVal = CWgetShipRate(
			$_SESSION["cw"]["confirmShipID"],
			$_SESSION["cwclient"]["cwCartID"],
			$shipMethodsQuery["ship_method_calctype"][0]);
	} else {
		$shipVal = $_SESSION["cwclient"]["cwShipTotal"];
	}
	// reset value of client var 
	if (is_numeric($shipVal)) {
		$_SESSION["cwclient"]["cwShipTotal"] = $shipVal;
	} else {
		$_SESSION["cwclient"]["cwShipTotal"] = 0;
	}
}
// if customer has specified shipping, or no ship methods are
//available (nothing to select), show totals mode  
if ($_SESSION["cw"]["confirmShip"] == true || $_SESSION["cw"]["confirmShipID"] > 0 || $shipMethodsQuery["totalRows"] == 0) {
	$module_settings["display_mode"] = 'totals';
}
// /// START OUTPUT /// 
// ADDRESS INFO 
if ($module_settings["show_address"]) {
?>
<p>
<?php
	// edit address link 
	if (strlen(trim($module_settings["edit_address_url"]))) {
?>
<span class="CWeditLink">&raquo;&nbsp;<a href="<?php $module_settings["edit_address_url"]; ?>">Edit Address</a></span>
<?php
	}
	// address text ?>
		<span class="label">Shipping To:</span>
		<?php echo $CWcustomer["shipname"]; ?>
		<br>
		<span class="label">&nbsp;</span>
<?php
	echo $CWcustomer["shipaddress1"];
	if (strlen(trim($CWcustomer["shipaddress2"]))) {
?>
		<br>
		<span class="label">&nbsp;</span>
<?php
		echo $CWcustomer["shipaddress2"];
	}
?>
		<br>
		<span class="label">&nbsp;</span>
        <?php echo $CWcustomer["shipcity"].", ".$CWcustomer["shipstateprovname"]; ?>
		<br>
		<span class="label">&nbsp;</span>
		<?php echo $CWcustomer["shipcountry"]; ?>
		<br>
		<span class="label">&nbsp;</span>
		<?php echo $CWcustomer["shipzip"]; ?>
</p>
<?php
}
// /end address 
// TOTALS 
if ($module_settings["display_mode"] == "totals") {
?>
	<h3 class="CWformTitle"><?php if (isset($_SESSION["cw"]["confirmShipID"]) && $_SESSION["cw"]["confirmShipID"] > 0 && $shipMethodsQuery["totalRows"] > 1) { ?>Shipping Method Selected<?php } else { ?>Shipping Details<?php } ?></h3>
<?php
	if (isset($shipVal)) {
		// shipval (shipping total) set above ?>
		<p>
<?php
		// link to change selection 
		if (strlen(trim($module_settings["edit_ship_url"])) && $allShipMethods["totalRows"] > 1) {
?>
			<span class="CWeditLink">&raquo;&nbsp;<a href="<?php echo $module_settings["edit_ship_url"]; ?>">Change Shipping</a></span>
<?php
		}
?>
		<span class="label">
			Shipping via:
		</span>
		<?php echo $_SESSION["cw"]["confirmShipName"]; ?>
		</p>
		<p class="clear">
		<span class="label">
			<strong>Shipping Total:</strong>
		</span>
		<?php // ship value ?>
		<strong>
<?php
		if (is_numeric($shipVal)) {
			echo cartweaverMoney($shipVal);
		} else {
			echo trim($shipVal);
		}
?>
		</strong>
		</p>
<?php
	}
	// link to continue if there's only one ship method 
	if (strlen(trim($module_settings["edit_ship_url"])) && $shipMethodsQuery["totalRows"] == 1) {
?>
		<div class="center top40 bottom40">
			<a id="CWlinkSkipShip" class="CWcheckoutLink" href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?shipconfirm=<?php echo $_SESSION["cw"]["confirmShipID"]; ?>" style="">Continue&nbsp;&raquo;</a>
		</div>
<?php
	}
	// SELECT (default) 
} else {
?>
	<h3 class="CWformTitle">Select Shipping Method</h3>
	<form id="CWformShipSelection" class="CWvalidate" action="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>" method="post">
<?php
	for ($i = 0; $i < $shipMethodsQuery["totalRows"]; $i++) {
		$shipVal = CWgetShipRate(
			$shipMethodsQuery["ship_method_id"][$i],
			$_SESSION["cwclient"]["cwCartID"],
			$shipMethodsQuery["ship_method_calctype"][$i]
			);
?>
			<div class="CWshipOption">
				<label>
				<?php // hidden link, shown with javascript ?>
				<a href="#" class="CWselectLink" style="display:none;">Select</a>
				<input type="radio" name="selectShip" class="required" value="<?php echo $shipMethodsQuery["ship_method_id"][$i]; ?>"<?php if (isset($_SESSION["cw"]["confirmShip"]) && $_SESSION["cw"]["confirmShip"] == $shipMethodsQuery["ship_method_id"][$i]) { ?> checked="checked"<?php } ?>>
				<?php // shipping type ?>
				<span class="CWshipName">
					<?php echo $shipMethodsQuery["ship_method_name"][$i]; ?>
				</span>
				<?php // shipping rate ?>
				<span class="CWshipOptionAmount">				
				<?php
		if (is_numeric($shipVal) && $shipVal > 0) {
			echo cartweaverMoney($shipVal);
		} else if (is_numeric($shipVal) && $shipVal == 0) {
			echo "Free Shipping";
		} else {
			echo $shipVal;
		}
?>
				</span>
				</label>
			</div>
<?php
	}
	// submit button, hidden with javascript ?>
		<div class="center CWclear top40">
			<input type="submit" class="CWformButton" id="CWshippingSelectSubmit" value="Submit Selection&nbsp;&raquo;">
		</div>
	</form>
<?php
	// javascript for selection 
	$shipSelectJS = "	<script type=\"text/javascript\">
	jQuery(document).ready(function(){
	// replace radio buttons with links
	jQuery('#CWformShipSelection').find('input:radio').each(function(){
		jQuery(this).hide().siblings('a.CWselectLink').show();
	});
	// clicking link submits form
	jQuery('#CWformShipSelection .CWcheckoutLink').show().click(function(){
		jQuery('form#CWformShipSelection').submit();
		return false;
	});
	// hide submit button
	jQuery('#CWshippingSelectSubmit').hide();
	// form submits on click of anything in label
	jQuery('#CWformShipSelection .CWshipOption > *').css('cursor','pointer').click(function(){
		jQuery(this).find('input:radio').prop('checked','checked');
		jQuery(this).parents('form').submit();
		return false;
	});
	});
	</script>";
	CWinsertHead($shipSelectJS);
}
?>