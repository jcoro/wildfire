<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-cartlinks.php
File Date: 2012-02-01
Description: Shows cart and customer account links on cart-related pages
Note: cw-mod-customerlinks is called twice with varying options
to create the left/right link grouping. This can be modified at will.
The returnUrl attribute is passed through to the cart,
for continue shopping link on cart page.
==========================================================
*/
?>
<div class="CWcartInfo CWcontent">
<?php
// hide content on processing pages if order has been placed, but not cleared from client scope 
$newVarForList1 = explode('/',$_ENV["request.cwpage"]["urlCheckout"]); 
$newVarForList2 = explode('/',$_ENV["request.cwpage"]["urlConfirmOrder"]);
if (!(($_ENV["request.cw"]["thisPage"] == $newVarForList1[count($newVarForList1)-1] || $_ENV["request.cw"]["thisPage"] == $newVarForList2[count($newVarForList2)-1]) && isset($_SESSION["cwclient"]["cwCompleteOrderID"]) && ($_SESSION["cwclient"]["cwCompleteOrderID"] != 0))) {
	// determine whether cart total includes tax 
	if ($_ENV["application.cw"]["taxDisplayOnProduct"]) {
		$_ENV["request.cwpage"]["totals_tax_type"] = $_ENV["application.cw"]["taxCalctype"];
	} else {
		$_ENV["request.cwpage"]["totals_tax_type"] = "none";
	}
	// cart links 
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array(
		"return_url" => $_ENV["request.cwpage"]["returnUrl"],
		"show_item_count" => true,
		"show_cart_total" => true,
		"show_view_cart" => true,
		"show_loggedinas" => false,
		"show_account" => false,
		"show_checkout" => true,
		"element_id" => "CWcartLinks",
		"tax_calc_method" => $_ENV["request.cwpage"]["totals_tax_type"]);
	include('../mod/cw-mod-customerlinks.php');
	unset($module_settings);
	chdir($myDir);
	// log in / logged in as 
	if ($_ENV["application.cw"]["customerAccountEnabled"] && !(isset($_SESSION["cwclient"]["cwCustomerCheckout"]) && $_SESSION["cwclient"]["cwCustomerCheckout"] == 'guest') && $_ENV["request.cw"]["thisPage"] != $newVarForList2[count($newVarForList2)-1]) {
		$module_settings = array(
			"show_login" => true,
			"show_logout" => true,
			"return_url" => $_ENV["request.cwpage"]["logoutUrl"],
			"show_loggedinas" => true,
			"show_account" => true,
			"element_id" => "CWaccountLinks");
		include('../mod/cw-mod-customerlinks.php');
		unset($module_settings);
		chdir($myDir);
	}
} else if ($_ENV["request.cw"]["thisPage"] == $newVarForList1[count($newVarForList1)-1]) {
?>	
			<p>Additional information or payment may be required. Complete the checkout process below</p>
<?php
} else if ($_ENV["request.cw"]["thisPage"] == $newVarForList2[count($newVarForList2)-1]) {
	// <p>Thank you. Your order is being processed.</p> 
}
// /end hide content ?>
    <div class="CWclear"></div>
</div>