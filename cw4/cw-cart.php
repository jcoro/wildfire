<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-cart.php
File Date: 2012-02-28
Description: Shows items in cart with all related functions
NOTES:
Requires cart functions include
Uses cart.carttotals.itemcount (as 'itemcount' var) to determine display
session.cwclient.cwcartid is set in cw-func-init
and stored in a cookie in cw-inc-pageend
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
// cartID is set in CW init functions, default here just in case 
if(!isset($_SESSION["cwclient"]["cwCartID"])) $_SESSION["cwclient"]["cwCartID"] = 0;
// customerID needed to handle account / billing 
if(!isset($_SESSION["cwclient"]["cwCustomerID"])) $_SESSION["cwclient"]["cwCustomerID"] = 0;
// tax location settings 
if(!isset($_SESSION["cwclient"]["cwTaxRegionID"])) $_SESSION["cwclient"]["cwTaxRegionID"] = 0;
if(!isset($_SESSION["cwclient"]["cwTaxCountryID"])) $_SESSION["cwclient"]["cwTaxCountryID"] = 0;
// discount defaults 
if (!isset($_SESSION["cwclient"]["discountPromoCode"])) $_SESSION["cwclient"]["discountPromoCode"] = "";
if (!isset($_SESSION["cwclient"]["discountApplied"])) $_SESSION["cwclient"]["discountApplied"] = "";
// country can be set by default in admin, client selection/login overrides 
if (!(isset($_SESSION["cwclient"]["cwTaxCountryID"]) && is_numeric($_SESSION["cwclient"]["cwTaxCountryID"]) && $_SESSION["cwclient"]["cwTaxCountryID"] > 0) && $_ENV["application.cw"]["taxUseDefaultCountry"]) {
	$_SESSION["cwclient"]["cwTaxCountryID"] = $_ENV["application.cw"]["defaultCountryID"];
}
// only show tax totals if a country is defined 
if($_SESSION["cwclient"]["cwTaxCountryID"] == 0) {
	$_ENV["request.cwpage"]["showTax"] = false;
} else {
	$_ENV["request.cwpage"]["showTax"] = true; 
}
// page variables - request scope can be overridden per product as needed
//params w/ default values are in application.cfc 
if(!isset($_ENV["request.cwpage"]["cartHeading"])) $_ENV["request.cwpage"]["cartHeading"] = '';
if(!isset($_ENV["request.cwpage"]["cartText"])) $_ENV["request.cwpage"]["cartText"] = ''; 
// check out page (skip address if already submitted) 
if(!isset($_ENV["request.cwpage"]["checkoutHref"])) $_ENV["request.cwpage"]["checkoutHref"] = $_ENV["request.cwpage"]["urlCheckout"]; 
if(isset($_SESSION["cw"]["confirmAddress"]) && $_SESSION["cw"]["confirmAddress"]) $_ENV["request.cwpage"]["checkoutHref"] = $_ENV["request.cwpage"]["urlCheckout"];

$myDir = getcwd();
chdir(dirname(__FILE__));
// clean up form and url variables 
include("cwapp/inc/cw-inc-sanitize.php");
// CARTWEAVER REQUIRED FUNCTIONS 
include("cwapp/inc/cw-inc-functions.php");
chdir($myDir);
// // GET CART DETAILS // 
$cwcart = CWgetCart();
// simple var for number of items in cart 
$itemcount = $cwcart["carttotals"]["itemCount"];
// /////// START OUTPUT /////// 
// breadcrumb navigation 
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "breadcrumb",
	"separator" => " &raquo; ",
	"end_label" => "View Cart",
	"all_categories_label" => "",
	"all_secondaries_label" => "",
	"all_products_label" => "");
include("cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
// show cart details ?>
<div id="CWcart" class="CWcontent">
	<h1>Shopping Cart</h1>
<?php
// if items exist in the cart (if cart does not exist this will be 0) 
if ($itemcount > 0) {
	// subheading 
	if (strlen(trim($_ENV["request.cwpage"]["cartHeading"]))) {
?>
			<h2><?php echo $_ENV["request.cwpage"]["cartHeading"]; ?></h2>
<?php
	}
	// text above cart table 
	if (strlen(trim($_ENV["request.cwpage"]["cartText"]))) {
?>
			<div><?php echo $_ENV["request.cwpage"]["cartText"]; ?></div>
<?php
	}
	// CART TABLE AND UPDATE FORM    
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array(
		"cart" => $cwcart,
		"display_mode" => "showcart",
		"product_order" => $_ENV["application.cw"]["appDisplayCartOrder"],
		"show_images" => $_ENV["application.cw"]["appDisplayCartImage"],
		"show_sku" => $_ENV["application.cw"]["appDisplayCartSku"],
		"show_options" => true,
		"show_continue" => true,
		"link_products" => true,
		"show_tax_total" => $_ENV["request.cwpage"]["showTax"]);
	include("cwapp/mod/cw-mod-cartdisplay.php");
	unset($module_settings);
	chdir($myDir);
	// CHECKOUT BUTTON ?>
		<p class="CWright"><a href="
		<?php
$prodidarray=array();
		
for ($lineitem=0; $lineitem<$itemcount; $lineitem++){
$prodidarray[] = $cwcart["cartitems"][$lineitem]["ID"];
}	

		if(isset($_SESSION["cwclient"]["cwShipCountryID"]) && $_SESSION["cwclient"]["cwShipCountryID"]==45 && ((in_array(129, $prodidarray)) || (in_array(126, $prodidarray))))
		{
		echo $_ENV["request.cwpage"]["urlShowCart"];
		}
		else
		{
		echo $_ENV["request.cwpage"]["checkoutHref"]; 
		}
		
		
		?>" class="CWcheckoutLink" id="CWlinkCheckout">Check Out&nbsp;&raquo;</a></p>
		
		<?php
	// if no products, show message with links 
} else {
?>
		<div class="CWconfirmBox confirmText">
			Cart is Empty
<?php
	if ($_ENV["application.cw"]["customerAccountEnabled"]) {
?>
				<br>
<?php
		// link to log in / my account 
		if (isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0") {
			$loginLinkText = 'Go to your account';
		} else {
			$loginLinkText = 'Log in';
		}
?>
				<a href="<?php echo $_ENV["request.cwpage"]["urlAccount"]; ?>"><?php echo $loginLinkText; ?></a> to see previously purchased items
<?php
	}
?>
			<br>
<?php
	// search link 
	if (strlen(trim($_ENV["request.cwpage"]["urlSearch"]))) {
?>
				<span class="smallPrint">
					<a href="<?php echo $_ENV["request.cwpage"]["urlSearch"]; ?>">Search for Products</a>
				</span>
<?php
	}
	// continue shopping link 
	if (isset($_ENV["request.cwpage"]["returnUrl"])) {
?>
				<span class="smallPrint">
<?php
		if (strlen(trim($_ENV["request.cwpage"]["returnUrl"]))) { ?>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php } ?>
					<a href="<?php echo $_ENV["request.cwpage"]["returnUrl"]; ?>">Return to Store</a>
				</span>
<?php
	}
?>
		</div>
<?php
	// clear stored values related to cart 
	try {
		unset($_SESSION["cw"]["cartAlert"]);
		unset($_SESSION["cw"]["cartConfirm"]);
	} catch (Exception $e) { }
}
?>
	<!-- clear floated content -->
	<div class="CWclear"></div>
</div>
<!-- / end #CWcart -->
<?php
// recently viewed products 
//$myDir = getcwd();
//chdir(dirname(__FILE__));
//include("cwapp/inc/cw-inc-recentview.php");
//chdir($myDir);
// page end / debug 
$myDir = getcwd();
chdir(dirname(__FILE__));
include("cwapp/inc/cw-inc-pageend.php");
chdir($myDir);
?>