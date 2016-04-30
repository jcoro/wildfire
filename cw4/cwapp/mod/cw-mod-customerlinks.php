<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-customerlinks.php
File Date: 2012-02-01
Description:
Displays links for View Cart | Check Out | # of Items in Cart | Log In / My Account | Logged in As
==========================================================
*/
// default attributes: return url is used for 'continue shopping' link  
if(!(isset($module_settings["return_url"]))) $module_settings["return_url"] = $_ENV["request.cwpage"]["urlResults"];
if(!(isset($module_settings["cart_quantity"]))) $module_settings["cart_quantity"] = 0;
if(!(isset($module_settings["cart_total"]))) $module_settings["cart_total"] = 0;
if(!(isset($module_settings["delimiter"]))) $module_settings["delimiter"] = " | ";
if(!(isset($module_settings["element_id"]))) $module_settings["element_id"] = "";
// determine which items are shown in this instance of the cartlinks display 
if(!(isset($module_settings["show_item_count"]))) $module_settings["show_item_count"] = false;
if(!(isset($module_settings["show_cart_total"]))) $module_settings["show_cart_total"] = false;
if(!(isset($module_settings["tax_calc_method"]))) $module_settings["tax_calc_method"] = "none";
if(!(isset($module_settings["show_view_cart"]))) $module_settings["show_view_cart"] = false;
if(!(isset($module_settings["show_checkout"]))) $module_settings["show_checkout"] = false;
if(!(isset($module_settings["show_login"]))) $module_settings["show_login"] = false;
if(!(isset($module_settings["show_logout"]))) $module_settings["show_logout"] = false;
if(!(isset($module_settings["show_account"]))) $module_settings["show_account"] = false;
if(!(isset($module_settings["show_loggedinas"]))) $module_settings["show_loggedinas"] = true;
if(!(isset($module_settings["logout_url"]))) $module_settings["logout_url"] = $_ENV["request.cwpage"]["urlCheckout"];
if(strpos($module_settings["logout_url"],'?') === false) $module_settings["logout_url"] = $module_settings["logout_url"].'?logout=1';
else $module_settings["logout_url"] = $module_settings["logout_url"].'&logout=1';
// cartlinks set up as delimited list, shown at end with single loop of list 
$cartlinks = array();
$myDir = getcwd();
chdir(dirname(__FILE__));
// global functions 
require_once("../inc/cw-inc-functions.php");
// clean up form and url variables 
require_once("../inc/cw-inc-sanitize.php");
chdir($myDir);
// get cart count, cart total from cart if not provided in attributes 
if (isset($_SESSION["cwclient"]["cwCartID"])) {
	if ($module_settings["cart_quantity"] == 0) {
		$module_settings["cart_quantity"] = number_format(CWgetCartCount($_SESSION["cwclient"]["cwCartID"]), 0, ".", ",");
	}
	if ($module_settings["cart_total"] == 0) {
		$module_settings["cart_total"] = CWgetCartTotal($_SESSION["cwclient"]["cwCartID"], $module_settings["tax_calc_method"]);
	}
}
// ITEM COUNT
if($module_settings["show_item_count"]) {
	// set up link 
	if($module_settings["cart_quantity"] > 0) {
		// single vs. plural 
		if($module_settings["cart_quantity"] != 1) $itemLabel = 'Items';
		else $itemLabel = 'Item';
		$itemCountText = $module_settings["cart_quantity"].' '.$itemLabel.' in Cart';
	} else {
		$itemCountText = 'Cart is Empty';
	}
	$cartlinks[] = '<span class="CWitemCountText">'.$itemCountText.'</span>';
}	
// / end item count 

// VIEW CART 
if($module_settings["show_view_cart"]) {
	$urlshowcart = explode('/',$_ENV["request.cwpage"]["urlShowCart"]);
	if($_ENV["request.cw"]["thisPage"] != $urlshowcart[count($urlshowcart)- 1]) {
		$viewCartLink = '<a class="CWviewCartLink" href="'.$_ENV["request.cwpage"]["urlShowCart"].'?returnUrl='.urlencode($module_settings["return_url"]).'">View Cart</a>';
		$cartlinks[] = $viewCartLink;
		 
	}
}	
// /end view cart 



// CART TOTAL 
if ($module_settings["show_cart_total"] && $module_settings["cart_total"] > 0) {
	$cartlinks[] = '<span class="CWitemCountText">'.cartweaverMoney($module_settings["cart_total"]).'</span>';
}
// /end cart total 

// CHECK OUT 
if(!isset($_ENV["request.cwpage"]["checkoutHref"])) $_ENV["request.cwpage"]["checkoutHref"] = $_ENV["request.cwpage"]["urlCheckout"];
if($module_settings["show_checkout"] && $module_settings["cart_quantity"] > 0) {
	$urlcheckout = explode('/',$_ENV["request.cwpage"]["urlCheckout"]);
	if($_ENV["request.cw"]["thisPage"] != $urlcheckout[count($urlcheckout)- 1]) {
		$checkOutLink = '<a class="CWcheckOutLink" href="'.$_ENV["request.cwpage"]["checkoutHref"].'">Check Out</a>';
		$cartlinks[] = $checkOutLink;
	}
}
// /end check out 

// LOG IN / MY ACCOUNT 
// if not logged in 
if(!(isset($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] != '0')) {
	// log in 
	if($module_settings["show_login"]) {
		$loginLink = '<a class="CWloginLink" href="'.$_ENV["request.cwpage"]["urlAccount"].'">Log In</a>';
		$cartlinks[] = $loginLink;
	}
// if logged in 
} else {
	// logged in as 
	if($module_settings["show_loggedinas"]  && isset($_SESSION["cwclient"]["cwCustomerName"]) && strlen(trim($_SESSION["cwclient"]["cwCustomerName"]))) {
		if($module_settings["show_account"]) {
			$displayName = '<a href="'.$_ENV["request.cwpage"]["urlAccount"].'">'.$_SESSION["cwclient"]["cwCustomerName"].'</a>';
		} else {
			$displayName = $_SESSION["cwclient"]["cwCustomerName"];
		}
		$loggedInText = 'Logged in as:'.'&nbsp; &nbsp; ' .$displayName;
		$cartlinks[] = '<span class="CWloggedInAs">'.$loggedInText.'</span>';
	}
	
	// my account 
	if($module_settings["show_account"]) {
		$accountLink = '<a href="'.$_ENV["request.cwpage"]["urlAccount"].'">My Account</a>';
		$cartlinks[] = $accountLink;
	}
	// log out 
	if($module_settings["show_logout"]) {
		$logoutLink = '<a class="CWlogoutLink" href="'.str_replace('?&','?',$module_settings["logout_url"]).'">Log Out</a>';
		$cartlinks[] = $logoutLink;
	}
}
// / end login/my account 
if (sizeof($cartlinks) > 0) {
	$linkStr = implode($module_settings["delimiter"], $cartlinks);
	// DISPLAY LINKS 
?>
<div class="CWcustomerLinks"<?php if(strlen(trim($module_settings["element_id"]))) { ?> id="<?php echo $module_settings["element_id"];?>"<?php } ?>>
<?php
	echo $linkStr;
?>
</div>
<?php
}
?>