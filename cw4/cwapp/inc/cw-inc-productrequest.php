<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-productrequest.php
File Date: 2012-02-01
Description:
- Validates and manages URL product and category details
- Sets up product name and category string for page title,
  with ids and text values in request.cwpage scope
- Sets up product/category description for page meta description

NOTE: Lookup functions are based on URL variables.
   	  If enabled, category and secondary are added to the URL scope dynamically
	     based on a product ID (product=xx) in url
	  Category names are pulled from lists stored in application scope.
	  (Reset Cartweaver application variables if values are not as expected.)
==========================================================
*/
// default page title (company name) 
$_ENV["request.cwpage"]["title"] = $_ENV["application.cw"]["companyName"];
// default description (blank by default) 
$_ENV["request.cwpage"]["description"] = '';
// defaults for url 
if(!(isset($_ENV["application.cw"]["listProducts"]))) $_ENV["application.cw"]["listProducts"] = array();
if(!(isset($_ENV["application.cw"]["listCategories"]))) $_ENV["application.cw"]["listCategories"] = array(); 
if(!(isset($_ENV["application.cw"]["listSubCategories"]))) $_ENV["application.cw"]["listSubCategories"] = array(); 
if(!(isset($_GET['product']))) $_GET['product'] = 0;
if(!(isset($_GET['category']))) $_GET['category'] = 0;
if(!(isset($_GET['secondary']))) $_GET['secondary'] = 0;

// if category and secondary are not defined in url, get via query for product page 
if(is_numeric($_GET['product']) && $_GET['product'] > 0 && $_ENV["application.cw"]["appDisplayProductCategories"]) {
	// get category if not provided 
	if(!is_numeric($_GET['category']) || $_GET['category'] == 0) {
		$rsRelCategories = CWqueryGetRS("
		SELECT cc.category_id, cc.category_name
		FROM cw_product_categories_primary rr
		INNER JOIN cw_categories_primary cc
		WHERE rr.product2category_product_id = '".CWqueryParam($_GET['product'])."'
		AND cc.category_id = rr.product2category_category_id
		ORDER BY cc.category_id");
		$_GET['category'] = $rsRelCategories['category_id'][0];
	}
	// get secondary if not provided 
//	if(!is_numeric($_GET['secondary']) || $_GET['secondary'] == 0) {
//		$rsRelScndCategories = CWqueryGetRS("
//		SELECT sc.secondary_id, sc.secondary_name
//		FROM cw_product_categories_secondary rr
//		INNER JOIN cw_categories_secondary sc
//		WHERE rr.product2secondary_product_id = '".CWqueryParam($_GET['product'])."'
//		AND sc.secondary_id = rr.product2secondary_secondary_id
//		ORDER BY sc.secondary_id");
//		$_GET['secondary'] = $rsRelScndCategories['secondary_id'][0];
//	}
	
}

// CATEGORY NAMES (for product listings and details pages) 
// PRIMARY 
// if a valid category is defined in url 
if(isset($_GET['category']) && is_numeric($_GET['category']) && $_GET['category'] > 0) {
	$_ENV["request.cwpage"]["categoryID"] = $_GET['category'];
	// get the value from saved list in application scope 
	if(isset($_ENV["application.cw"]["listCategories"][$_GET['category']])) {
		$_ENV["request.cwpage"]['categoryName'] = $_ENV["application.cw"]["listCategories"][$_GET['category']];
	} else {
		$_GET['categoryName'] = '';
	}
	// add to title 
	if(strlen(trim($_ENV["request.cwpage"]['categoryName']))) {
		$_ENV["request.cwpage"]["title"] = $_ENV["request.cwpage"]['categoryName']." | ".$_ENV["request.cwpage"]['title'];
	}
} else {
	$_ENV["request.cwpage"]['categoryName'] = '';
	$_ENV["request.cwpage"]["categoryID"] = 0;
}
// /end primary 

// SECONDARY 
// if a valid category is defined in url 
 if(isset($_GET['secondary']) && is_numeric($_GET['secondary']) && $_GET['secondary'] >0) {	
	$_ENV["request.cwpage"]["secondaryID"] = $_GET['secondary'];
	// get the value from saved list in application scope 
	if(isset($_ENV["application.cw"]["listSubCategories"][$_GET['secondary']])) {
		$_ENV["request.cwpage"]['secondaryName'] = $_ENV["application.cw"]["listSubCategories"][$_GET['secondary']];
	} else {
		$_ENV["request.cwpage"]['secondaryName'] = '';
	}
	// add to title 
	if(strlen(trim($_ENV["request.cwpage"]['secondaryName']))) {
		$_ENV["request.cwpage"]["title"] = $_ENV["request.cwpage"]['secondaryName']." | ".$_ENV["request.cwpage"]["title"];
	}
 } else {
		$_ENV["request.cwpage"]['secondaryName'] = '';
		$_ENV["request.cwpage"]["secondaryID"] = 0;
 }
// /end secondary 
// PRODUCT NAME/DESCRIPTION (details page only)
// if details page 
$urlDetails = explode("/",$_ENV["request.cwpage"]["urlDetails"]);
if($_ENV["request.cw"]["thisPage"] == $urlDetails[count($urlDetails) - 1]) {
	// if a valid product is defined in url 
	if(isset($_GET['product']) && is_numeric($_GET['product']) && $_GET['product'] > 0) {
		$_ENV["request.cwpage"]["productID"] = $_GET['product'];
		// get the product name from saved list in application scope 
		if(isset($_ENV["application.cw"]["listProducts"][$_GET['product']])) {
			$_ENV["request.cwpage"]["productName"] = $_ENV["application.cw"]["listProducts"][$_GET['product']];
		} else {
			$_ENV["request.cwpage"]["productName"] = 'Product Details';
		}
		// add to title 
		if(strlen(trim($_ENV["request.cwpage"]["productName"]))) {
			$_ENV["request.cwpage"]["title"] = $_ENV["request.cwpage"]["productName"]." | ".$_ENV["request.cwpage"]["title"];
		}
		// get description from products table 
		$rsDescription = CWqueryGetRS("SELECT product_preview_description as descripText
			FROM cw_products
			WHERE product_id = ".CWqueryParam($_GET["product"]));
	} else {
		$_ENV["request.cwpage"]["productName"] = '';
		$_ENV["request.cwpage"]["productID"] = 0;
	}
	// /end if valid product defined 
// CATEGORY DESCRIPTIONS 
} else {
	// if not product details page, check for category/secondary descriptions 
	if ($_ENV["request.cwpage"]["secondaryID"] > 0) {
		$rsDescription = CWqueryGetRS("SELECT secondary_description as descripText
			FROM cw_categories_secondary
			WHERE secondary_id = ".CWqueryParam($_ENV["request.cwpage"]["secondaryID"]));
	} else if ($_ENV["request.cwpage"]["categoryID"] > 0) {
		$rsDescription = CWqueryGetRS("SELECT category_description as descripText
			FROM cw_categories_primary
			WHERE category_id = ".CWqueryParam($_ENV["request.cwpage"]["secondaryID"]));
	}
	// /end category descriptions 
}
// /end if product details page 
// DESCRIPTION TEXT 
if (isset($rsDescription) && isset($rsDescription["descripText"][0]) && strlen(trim($rsDescription["descripText"][0]))) {
	$_ENV["request.cwpage"]["description"] = trim(preg_replace("/\<[^\>]*\>/","",trim($rsDescription["descripText"][0])).((isset($_ENV["request.cwpage"]["description"]) && strlen(trim($_ENV["request.cwpage"]["description"]))) ? ' '.$_ENV["request.cwpage"]["description"] : "" ));
}
?>