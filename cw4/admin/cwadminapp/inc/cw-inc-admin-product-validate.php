<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-product-validate.php
File Date: 2012-02-01
Description: Here we validate the data submitted in the product form.
If the data fits the required parameters we send continue on with
the transaction. If not we halt the transaction and pass back error
messages to be displayed by the calling template.
==========================================================
*/
// We either updated or added a new product 
if(isset($_POST['Action'])) {
	if($_POST['Action'] == "AddProduct" && $_POST['.product_merchant_product_id'] == "") {
		$_ENV["request.cwpage"]["AddProductError"][] = "Product ID is required";
	}	
	if($_POST['product_name'] == "") {
		$_ENV["request.cwpage"]["AddProductError"][] = "Product ID is required";
	}
	if(! is_numeric($_POST['product_sort'])) {
		$_POST['product_sort'] = 0;
		$_ENV["request.cwpage"]["AddProductError"][] = "Sort Order must be numeric";
	}
	if(count($_ENV["request.cwpage"]["AddProductError"]) != 0) {
		foreach ($_ENV["request.cwpage"]["AddProductError"] as $list => $ll) {
			CWpageMessage("alert",$ll);
		}
	}
}
//If we're adding a new sku
if(isset($_POST['AddSKU'])) {
	if($_POST['sku_merchant_sku_id'] == "") {
		$_ENV["request.cwpage"]["AddSKUError"][] = "SKU Name is required";
	}
	if(! is_numeric($_POST['sku_price'])) {
		$_ENV["request.cwpage"]["AddSKUError"][] = "Valid Price is required";
	}	
	if(! is_numeric($_POST['sku_ship_base'])) {
		$_ENV["request.cwpage"]["AddSKUError"][] = "Ship Base must be numeric";
	}
	if(! is_numeric($_POST['sku_sort'])) {
		$_ENV["request.cwpage"]["AddSKUError"][] = "SKU Sort Order must be numeric";
	}
	if(! is_numeric($_POST['sku_weight'])) {
		$_ENV["request.cwpage"]["AddSKUError"][] = "SKU Weight must be numeric";
	}
	if(! is_numeric($_POST['sku_stock'])) {
		$_ENV["request.cwpage"]["AddSKUError"][] = "SKU Stock must be numeric";
	}
	if(count($_ENV["request.cwpage"]["AddSKUError"]) != 0) {
		foreach ($_ENV["request.cwpage"]["AddSKUError"] as $list => $ll) {
			CWpageMessage("alert",$ll);
		}
	}
}

?>