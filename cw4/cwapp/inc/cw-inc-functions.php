<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-functions.php
File Date: 2012-04-01
Description: Includes all required functions for cartweaver pages
NOTE: more may be inserted, do not change the order of the default included function files
==========================================================
*/
try {
	// global functions
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	if(!function_exists('CWtime')) {
		require_once("../func/cw-func-global.php");
	}
	// global queries
	if(!function_exists('CWquerySelectProductDetails')) {
		require_once("../func/cw-func-query.php");
	}
	// shipping functions
	if(!function_exists('CWgetShipRate')) {
		require_once("../func/cw-func-shipping.php");
	}
	// tax functions
	if(!function_exists('CWgetShipTax')) {
		require_once("../func/cw-func-tax.php");
	}
	// cart functions
	if(!function_exists('CWgetCart')) {
		require_once("../func/cw-func-cart.php");
	}
	// product functions
	if(!function_exists('CWgetProduct')) {
		require_once("../func/cw-func-product.php");
	}
	// order functions 
	if(!function_exists('CWsaveOrder')) {
		require_once("../func/cw-func-order.php");
	}
	// discount functions 
	if(!function_exists('CWgetDiscountAmount')) {
		require_once("../func/cw-func-discount.php");
	}
	// customer functions
	if(!function_exists('CWgetCustomer')) {
		require_once("../func/cw-func-customer.php");
	}
	// mail functions 
	if(!function_exists('CWsendMail')) {
		require_once("../func/cw-func-mail.php");
	}
	// download functions 
	if(!function_exists('CWcreateDownloadURL')) {
		require_once("../func/cw-func-download.php");
	}
	chdir($myDir);
}
catch(Exception $e) {
	$_ENV["request.cwpage"]["functionError"] = 'Functions Error:'.$e->getMessage(); 
}
if(isset($_ENV["request.cwpage"]["functionError"])) {
   var_dump($_ENV["request.cwpage"]["functionError"]);
}

?>