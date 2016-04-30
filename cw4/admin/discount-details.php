<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: discount-details.php
File Date: 2012-07-07
Description: Displays discount add/edit/delete form
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("merchant,developer");
// PAGE PARAMS 
// default values for sort / active or archived
if (!isset($_GET["sortby"])) $_GET["sortby"] = "discount_merchant_id";
if (!isset($_GET["sortdir"])) $_GET["sortdir"] = "asc";
// define showtab to set up default tab display 
if (!isset($_GET["showtab"])) $_GET["showtab"] = 1;
// ID of discount (for updates/deletions) 
if (!isset($_GET["discount_id"])) $_GET["discount_id"] = 0;
if (!isset($_POST["discount_id"])) $_POST["discount_id"] = $_GET["discount_id"];
// BASE URL 
$_ENV["request.cwpage"]["baseURL"] = $_ENV["request.cw"]["thisPage"];
// /////// 
// ADD NEW DISCOUNT 
// /////// 
if (isset($_POST["AddDiscount"])) {
	// CHECKBOX PARAMS 
	if (!isset($_POST["discount_show_description"])) $_POST["discount_show_description"] = 0;
	if (!isset($_POST["discount_global"])) $_POST["discount_global"] = 0;
	if (!isset($_POST["discount_exclusive"])) $_POST["discount_exclusive"] = 0;
	if (!isset($_POST["discount_archive"])) $_POST["discount_archive"] = 0;
	// QUERY: Add new discount (all discount form variables) 
	// this query returns the discount id, or an error like '0-fieldname' 
	$newDiscountID = CWqueryInsertDiscount(
		$_POST["discount_merchant_id"],
		$_POST["discount_name"],
		$_POST["discount_amount"],
		$_POST["discount_calc"],
		$_POST["discount_description"],
		$_POST["discount_show_description"],
		$_POST["discount_type"],
		$_POST["discount_promotional_code"],
		$_POST["discount_start_date"],
		$_POST["discount_end_date"],
		$_POST["discount_limit"],
		$_POST["discount_customer_limit"],
		$_POST["discount_global"],
		$_POST["discount_exclusive"],
		$_POST["discount_priority"],
		$_POST["discount_archive"]
	);
	// query checks for duplicate fields 
	if (substr($newDiscountID,0,2) == '0-') {
		$dupField = substr($newDiscountID,2);
		CWpageMessage("alert","Error: ".$dupField." already exists");
		// update complete: return to page showing message 
	} else {
		CWpageMessage("confirm","Discount Added: Complete additional conditions as needed");
		header("Location: ".$_ENV["request.cw"]["thisPage"]."?discount_id=".$newDiscountID."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&showtab=2");
		exit;
	}
}
// /////// 
// /END ADD NEW DISCOUNT 
// /////// 
// /////// 
// UPDATE DISCOUNT 
// /////// 
if (isset($_POST["discount_id"]) && $_POST["discount_id"] > 0 && isset($_POST["discount_name"])) {
	// CHECKBOX PARAMS 
	if (!isset($_POST["discount_show_description"])) $_POST["discount_show_description"] = 0;
	if (!isset($_POST["discount_global"])) $_POST["discount_global"] = 0;
	if (!isset($_POST["discount_exclusive"])) $_POST["discount_exclusive"] = 0;
	if (!isset($_POST["discount_archive"])) $_POST["discount_archive"] = 0;
	if (!isset($_POST["discount_filter_customer_type"])) $_POST["discount_filter_customer_type"] = 0;
	if (!isset($_POST["discount_customer_type"])) $_POST["discount_customer_type"] = 0;
	if (!isset($_POST["discount_filter_customer_id"])) $_POST["discount_filter_customer_id"] = 0;
	if (!isset($_POST["discount_filter_cart_total"])) $_POST["discount_filter_cart_total"] = 0;
	if (!isset($_POST["discount_filter_cart_qty"])) $_POST["discount_filter_cart_qty"] = 0;
	if (!isset($_POST["discount_filter_item_qty"])) $_POST["discount_filter_item_qty"] = 0;
	if (!isset($_POST["discount_association_method"])) $_POST["discount_association_method"] = "";
	// list of numeric fields 
	$_ENV["request.cwpage"]["numericList"] = array('discount_amount','discount_limit','discount_customer_limit','discount_cart_total_max','discount_cart_total_min','discount_item_qty_min','discount_item_qty_max','discount_cart_qty_min','discount_cart_qty_max');
	// force numeric values or replace with 0 
	foreach ($_ENV["request.cwpage"]["numericList"] as $key => $f) {
		$fieldName = trim($f);
		// set the value to 0 if not numeric or not defined 
		if (!isset($_POST[$fieldName]) || !is_numeric($_POST[$fieldName])) {
			$_POST[$fieldName] = 0;
		}
	}
	// QUERY: update discount record (all discount form variables) 
	$updateDiscountID = CWqueryUpdateDiscount(
		$_POST["discount_id"],
		$_POST["discount_merchant_id"],
		$_POST["discount_name"],
		$_POST["discount_amount"],
		$_POST["discount_calc"],
		$_POST["discount_description"],
		$_POST["discount_show_description"],
		$_POST["discount_type"],
		$_POST["discount_promotional_code"],
		$_POST["discount_start_date"],
		$_POST["discount_end_date"],
		$_POST["discount_limit"],
		$_POST["discount_customer_limit"],
		$_POST["discount_global"],
		$_POST["discount_exclusive"],
		$_POST["discount_priority"],
		$_POST["discount_archive"],
		$_POST["discount_association_method"],
		$_POST["discount_filter_customer_type"],
		$_POST["discount_customer_type"],
		$_POST["discount_filter_customer_id"],
		$_POST["discount_customer_id"],
		$_POST["discount_filter_cart_total"],
		$_POST["discount_cart_total_max"],
		$_POST["discount_cart_total_min"],
		$_POST["discount_filter_item_qty"],
		$_POST["discount_item_qty_min"],
		$_POST["discount_item_qty_max"],
		$_POST["discount_filter_cart_qty"],
		$_POST["discount_cart_qty_min"],
		$_POST["discount_cart_qty_max"]
		);
	// query checks for duplicate fields 
	if (substr($updateDiscountID,0,2) == '0-') {
		$dupField = substr(updateDiscountID,2);
		CWpageMessage("alert","Error: ".$dupField." already exists");
	} else {
		// update complete: return to page showing message 
		CWpageMessage("confirm","Discount Updated")>
		header("Location: ".$_ENV["request.cw"]["thisPage"]."?discount_id=".$_POST["discount_id"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."");
		exit;
	}
}
// /////// 
// /END UPDATE DISCOUNT 
// /////// 
// /////// 
// DELETE DISCOUNT 
// /////// 
if (isset($_GET["deleteDisc"]) && is_numeric($_GET["deleteDisc"])) {
	if (!isset($_GET["returnUrl"])) $_GET["returnUrl"] = "discounts.php?useralert=".CWurlSafe('Unable to delete: discount '.$_GET["deleteDisc"].' not found')."";
	// QUERY: delete customer record (id from url)
	$deleteDiscount = CWqueryDeleteDiscount($_GET["deleteDisc"]);
	header("Location: ".$_GET["returnUrl"]);
	exit;
}
// /////// 
// /END DELETE DISCOUNT 
// /////// 
// /////// 
// CHANGE ASSOCIATION METHOD
// /////// 
if (isset($_POST["discount_association_method"]) && strlen(trim($_POST["discount_association_method"]))) {
	$updateQuery = CWqueryUpdateDiscountAssociationMethod($_POST["discount_id"],$_POST["discount_association_method"]);
	CWpageMessage("confirm","Association method updated. Complete selections below");
	header("Location: ".$_ENV["request.cw"]["thisPage"]."?discount_id=".$_POST["discount_id"]."&showtab=3&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."");
	exit;
}
// /////// 
// /END CHANGE ASSOCIATION METHOD
// /////// 
// /////// 
// UPDATE CATEGORIES
// /////// 
if (isset($_POST["category_id"])) {
	// QUERY: get categories assigned to this discount 
	$listDiscCats = CWquerySelectDiscountRelCategories($_POST["discount_id"]);
	$listDiscScndCats = CWquerySelectDiscountRelSecondaries($_POST["discount_id"]);
	if (!isset($listDiscCats["discount2category_category_id"])) $currentCats = array();
	else $currentCats = $listDiscCats["discount2category_category_id"];
	if (!isset($listDiscScndCats["discount2category_category_id"])) $currentScndCats = array();
	else $currentScndCats = $listDiscScndCats["discount2category_category_id"];
	if (isset($_POST["disabledCheckedCat"])) {
		$tArr = explode(",", $_POST["disabledCheckedCat"]);
		foreach ($tArr as $key => $i) {
			if (strlen(trim($i)) && !in_array(trim($i), $_POST["category_id"])) {
				$_POST["category_id"][] = trim($i);
			}
		}
	}
	if (isset($_POST["disabledCheckedScnd"])) {
		$tArr = explode(",", $_POST["disabledCheckedScnd"]);
		foreach ($tArr as $key => $i) {
			if (strlen(trim($i)) && !in_array(trim($i), $_POST["secondary_id"])) {
				$_POST["secondary_id"][] = trim($i);
			}
		}
	}
	// check existing cat IDs, remove any not in new list 
	foreach ($currentCats as $key => $i) {
		if (!in_array(trim($i), $_POST["category_id"])) {
			// QUERY: delete category 
			$deleteCat = CWqueryDeleteDiscountCat($_POST["discount_id"],$i);
		}
	}
	// check existing secondary IDs, remove any not in new list 
	foreach ($currentScndCats as $key => $i) {
		if (!in_array(trim($i), $_POST["secondary_id"])) {
			// QUERY: delete secondary 
			$deleteCat = CWqueryDeleteDiscountScndCat($_POST["discount_id"],$i);
		}
	}
	// loop list of submitted cat IDs 
	foreach ($_POST["category_id"] as $key => $i) {
		// insert any that don't already exist 
		if (strlen(trim($i)) && !in_array(trim($i), $currentCats)) {
			// QUERY: insert category 
			$addCat = CWqueryInsertDiscountCat($_POST["discount_id"],$i);
		}
	}
	// loop list of submitted secondary IDs 
	foreach ($_POST["secondary_id"] as $key => $i) {
		// insert any that don't already exist 
		if (strlen(trim($i)) && !in_array(trim($i), $currentScndCats)) {
			// QUERY: insert secondary 
			$addCat = CWqueryInsertDiscountScndCat($_POST["discount_id"],$i);
		}
	}
	// clear stored discount data from memory 
	try {
		unset($_ENV["application.cw"]["discountData"]);
		CWsetApplicationRefresh();
		CWinitApplication();
	} catch(Exception $e) {
	}
	// redirect to page with message and tab shown 
	CWpageMessage('confirm','Associated '.$_ENV["application.cw"]["adminLabelCategories"].' and '.$_ENV["application.cw"]["adminLabelSecondaries"].' saved');
	header("Location: ".$_ENV["request.cw"]["thisPage"]."?discount_id=".$_POST["discount_id"]."&showtab=3&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."");
	exit;
}
// /////// 
// /END UPDATE CATEGORIES
// /////// 
// /////// 
// ADD ASSOCIATED SKUS 
// /////// 
if (isset($_POST["discount_sku_id"]) && is_array($_POST["discount_sku_id"])) {
	$insertCt = 0;
	foreach ($_POST["discount_sku_id"] as $key => $pp) {
		if (strlen(trim($pp)) && trim($pp) != 0) {
			$insertCt++;
			// QUERY: insert associated skus (discount id, sku id)
			$addDiscSku = CWqueryInsertDiscountSku($_POST["discount_id"],trim($pp));
		}
	}
	// handle plurals 
	if ($insertCt > 1) {
		$s = 's';
	} else {
		$s = '';
	}
	// clear stored discount data from memory 
	try {
		unset($_ENV["application.cw"]["discountData"]);
		CWsetApplicationRefresh();
		CWinitApplication();
	} catch(Exception $e) {
	}
	$confirmMsg = $insertCt." Associated SKU".$s." created";
	CWpageMessage("confirm",$confirmMsg);
	header("Location: ".$_ENV["request.cw"]["thisPage"]."?discount_id=".$_POST["discount_id"]."&showtab=3&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."");
	exit;
}
// /////// 
// /END ADD ASSOCIATED SKUS 
// /////// 
// /////// 
// DELETE ASSOCIATED SKUS 
// /////// 
if (isset($_POST["deletesku_id"]) && is_array($_POST["deletesku_id"])) {
	// QUERY: delete discount records (discount, list of products) 
	$delProds = CWqueryDeleteDiscountSKU($_POST["discount_id"],implode(",", $_POST["deletesku_id"]));
	// set up confirmation 
	if (sizeof($_POST["deletesku_id"]) > 1) {
		$s = 's';
	} else {
		$s = '';
	}
	// clear stored discount data from memory 
	try {
		unset($_ENV["application.cw"]["discountData"]);
		CWsetApplicationRefresh();
		CWinitApplication();
	} catch(Exception $e) {
	}
	// redirect to page with message and tab shown 
	CWpageMessage('confirm',sizeof($_POST["deletesku_id"])." associated sku".$s." deleted");
	header("Location: ".$_ENV["request.cw"]["thisPage"]."?discount_id=".$_POST["discount_id"]."&showtab=3&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."");
	exit;
}
// /////// 
// /END DELETE ASSOCIATED SKUS 
// /////// 
// /////// 
// ADD ASSOCIATED PRODUCTS 
// /////// 
if (isset($_POST["discount_product_id"]) && is_array($_POST["discount_product_id"])) {
	$insertCt = 0;
	foreach ($_POST["discount_product_id"] as $key => $pp) {
		if (strlen(trim($pp)) && trim($pp) != 0) {
			$insertCt++;
			// QUERY: insert associated products (discount id, product id)
			$addDiscProd = CWqueryInsertDiscountProduct($_POST["discount_id"],trim($pp));
		}
	}
	// clear stored discount data from memory 
	try {
		unset($_ENV["application.cw"]["discountData"]);
		CWsetApplicationRefresh();
		CWinitApplication();
	} catch(Exception $e) {
	}
	// handle plurals 
	if ($insertCt > 1) {
		$s = 's';
	} else {
		$s = '';
	}
	$confirmMsg = $insertCt." Associated Product".$s." created";
	CWpageMessage("confirm",$confirmMsg);
	header("Location: ".$_ENV["request.cw"]["thisPage"]."?discount_id=".$_POST["discount_id"]."&showtab=3&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."");
	exit;
}
// /////// 
// /END ADD ASSOCIATED PRODUCTS 
// /////// 
// /////// 
// DELETE ASSOCIATED PRODUCTS 
// /////// 
if (isset($_POST["deleteproduct_id"]) && is_array($_POST["deleteproduct_id"])) {
	// QUERY: delete discount records (discount, list of products) 
	$delProds = CWqueryDeleteDiscountProduct($_POST["discount_id"],implode(",", $_POST["deleteproduct_id"]));
	// set up confirmation 
	if (sizeof($_POST["deleteproduct_id"]) > 1) {
		$s = 's';
	} else {
		$s = '';
	}
	// clear stored discount data from memory 
	try {
		unset($_ENV["application.cw"]["discountData"]);
		CWsetApplicationRefresh();
		CWinitApplication();
	} catch(Exception $e) {
	}
	// redirect to page with message and tab shown 
	CWpageMessage('confirm',sizeof($_POST["deleteproduct_id"])." associated product".$s." deleted");
	header("Location: ".$_ENV["request.cw"]["thisPage"]."?discount_id=".$_POST["discount_id"]."&showtab=3&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."");
	exit;
}
// /////// 
// /END DELETE ASSOCIATED PRODUCTS 
// /////// 
// /////// 
// DEFAULT: LOOKUP DISCOUNT DETAILS 
// /////// 
// verify discount id is numeric 
if (!is_numeric($_POST["discount_id"])) { $_POST["discount_id"] = 0; }
// QUERY: get discount by id 
$discountQuery = CWquerySelectDiscounts($_POST["discount_id"]);
// QUERY: get discount order details (discount id,number of orders to return) 
$ordersQuery = CWquerySelectDiscountOrderDetails($_POST["discount_id"],50);
// QUERY: get discount types 
$discountTypesQuery = CWquerySelectDiscountTypes();
// QUERY: get all customer types 
$typesQuery = CWquerySelectCustomerTypes();
// NOTE: additional queries for 'edit' mode are after the form params, below 
// if discount id specified but not found, return to list page 
if ($_POST["discount_id"] > 0 && $discountQuery["totalRows"] == 0) {
	header("Location: discounts.php?useralert=".CWurlSafe('Discount Details Unavailable')."");
}
// edit / add new 
if ($discountQuery["totalRows"]) {
	$_ENV["request.cwpage"]["editMode"] = 'edit';
	$_ENV["request.cwpage"]["headtext"] = "Discount Details&nbsp;&nbsp;&nbsp;<span class='subhead'>".$discountQuery["discount_name"][0]." (ID: ".$discountQuery["discount_merchant_id"][0].")</span>";
} else {
	$_ENV["request.cwpage"]["editMode"] = 'add';
	$_ENV["request.cwpage"]["headtext"] = "Discount Management: Add New Discount";
}
// /////// 
// /END LOOKUP DISCOUNT 
// /////// 
// FORM DEFAULTS 
// type of discount: sku_cost | sku_ship | order_total | ship_total 
if (!isset($_POST["discount_type"])) $_POST["discount_type"] = ((isset($discountQuery["discount_type"][0])) ? $discountQuery["discount_type"][0] : "" );
// calculation: fixed | percent 
if (!isset($_POST["discount_calc"])) $_POST["discount_calc"] = ((isset($discountQuery["discount_calc"][0])) ? $discountQuery["discount_calc"][0] : "" );
// amount (dollar value or percentage) 
if (!isset($_POST["discount_amount"])) $_POST["discount_amount"] = ((isset($discountQuery["discount_amount"][0])) ? $discountQuery["discount_amount"][0] : "" );
// merchant id: in-store 'part number' for this discount 
if (!isset($_POST["discount_merchant_id"])) $_POST["discount_merchant_id"] = ((isset($discountQuery["discount_merchant_id"][0])) ? $discountQuery["discount_merchant_id"][0] : "" );
// discount name: title of promotion e.g. "Spring Widgets Sale" 
if (!isset($_POST["discount_name"])) $_POST["discount_name"] = ((isset($discountQuery["discount_name"][0])) ? $discountQuery["discount_name"][0] : "" );
// description of discount e.g. "Get 20% Off when you spend more than $100"
if (!isset($_POST["discount_description"])) $_POST["discount_description"] = ((isset($discountQuery["discount_description"][0])) ? $discountQuery["discount_description"][0] : "" );
// show the description in the cart, emails, etc 
if (!isset($_POST["discount_show_description"])) $_POST["discount_show_description"] = ((isset($discountQuery["discount_show_description"][0])) ? $discountQuery["discount_show_description"][0] : "" );
// global discount: y/n (discount applies to all items or only specific skus) 
if (!isset($_POST["discount_global"])) $_POST["discount_global"] = ((isset($discountQuery["discount_global"][0])) ? $discountQuery["discount_global"][0] : "" );
// exclusive discount: y/n (discount can be used with other discounts) 
if (!isset($_POST["discount_exclusive"])) $_POST["discount_exclusive"] = ((isset($discountQuery["discount_exclusive"][0])) ? $discountQuery["discount_exclusive"][0] : "" );
// priority: if exclusive, determines applied discount  
if (!isset($_POST["discount_priority"])) $_POST["discount_priority"] = ((isset($discountQuery["discount_priority"][0])) ? $discountQuery["discount_priority"][0] : "" );
// promo code: if used, discount must enter this to invoke discount 
if (!isset($_POST["discount_promotional_code"])) $_POST["discount_promotional_code"] = ((isset($discountQuery["discount_promotional_code"][0])) ? $discountQuery["discount_promotional_code"][0] : "" );
// start date 
if (!isset($_POST["discount_start_date"]) && isset($discountQuery["discount_start_date"][0]) && $discountQuery["discount_start_date"][0] && strtotime($discountQuery["discount_start_date"][0]) !== false) $_POST["discount_start_date"] = strtotime($discountQuery["discount_start_date"][0]);
else $_POST["discount_start_date"] = strtotime("-1 day");
// end date 
if (!isset($_POST["discount_end_date"]) && isset($discountQuery["discount_type"][0]) && $discountQuery["discount_end_date"][0] && strtotime($discountQuery["discount_end_date"][0]) !== false) $_POST["discount_end_date"] = strtotime($discountQuery["discount_end_date"][0]);
else $_POST["discount_end_date"] = "";
// use limit: number of times this discount can be used before it becomes invalid (0 = no limit) 
if (!isset($_POST["discount_limit"])) $_POST["discount_limit"] = ((isset($discountQuery["discount_limit"][0])) ? $discountQuery["discount_limit"][0] : "" );
// discount limit: number of times a customer can use this discount (0 = no limit) 
if (!isset($_POST["discount_customer_limit"])) $_POST["discount_customer_limit"] = ((isset($discountQuery["discount_customer_limit"][0])) ? $discountQuery["discount_customer_limit"][0] : "" );
// archived: yes/no - only active discounts are available to the cart 
if (!isset($_POST["discount_archive"])) $_POST["discount_archive"] = ((isset($discountQuery["discount_archive"][0])) ? $discountQuery["discount_archive"][0] : "" );
// FILTERING PARAMS 
if (!isset($_POST["discount_filter_customer_type"])) $_POST["discount_filter_customer_type"] = ((isset($discountQuery["discount_filter_customer_type"][0])) ? $discountQuery["discount_filter_customer_type"][0] : "" );
if (!isset($_POST["discount_customer_type"])) $_POST["discount_customer_type"] = ((isset($discountQuery["discount_customer_type"][0])) ? $discountQuery["discount_customer_type"][0] : "" );
if (!isset($_POST["discount_filter_customer_id"])) $_POST["discount_filter_customer_id"] = ((isset($discountQuery["discount_filter_customer_id"][0])) ? $discountQuery["discount_filter_customer_id"][0] : "" );
if (!isset($_POST["discount_customer_id"])) $_POST["discount_customer_id"] = ((isset($discountQuery["discount_customer_id"][0])) ? $discountQuery["discount_customer_id"][0] : "" );
if (!isset($_POST["discount_filter_cart_total"])) $_POST["discount_filter_cart_total"] = ((isset($discountQuery["discount_filter_cart_total"][0])) ? $discountQuery["discount_filter_cart_total"][0] : "" );
if (!isset($_POST["discount_cart_total_max"])) $_POST["discount_cart_total_max"] = ((isset($discountQuery["discount_cart_total_max"][0])) ? $discountQuery["discount_cart_total_max"][0] : "" );
if (!isset($_POST["discount_cart_total_min"])) $_POST["discount_cart_total_min"] = ((isset($discountQuery["discount_cart_total_min"][0])) ? $discountQuery["discount_cart_total_min"][0] : "" );
if (!isset($_POST["discount_filter_item_qty"])) $_POST["discount_filter_item_qty"] = ((isset($discountQuery["discount_filter_item_qty"][0])) ? $discountQuery["discount_filter_item_qty"][0] : "" );
if (!isset($_POST["discount_item_qty_max"])) $_POST["discount_item_qty_max"] = ((isset($discountQuery["discount_item_qty_max"][0])) ? $discountQuery["discount_item_qty_max"][0] : "" );
if (!isset($_POST["discount_item_qty_min"])) $_POST["discount_item_qty_min"] = ((isset($discountQuery["discount_item_qty_min"][0])) ? $discountQuery["discount_item_qty_min"][0] : "" );
if (!isset($_POST["discount_filter_cart_qty"])) $_POST["discount_filter_cart_qty"] = ((isset($discountQuery["discount_filter_cart_qty"][0])) ? $discountQuery["discount_filter_cart_qty"][0] : "" );
if (!isset($_POST["discount_cart_qty_max"])) $_POST["discount_cart_qty_max"] = ((isset($discountQuery["discount_cart_qty_max"][0])) ? $discountQuery["discount_cart_qty_max"][0] : "" );
if (!isset($_POST["discount_cart_qty_min"])) $_POST["discount_cart_qty_min"] = ((isset($discountQuery["discount_cart_qty_min"][0])) ? $discountQuery["discount_cart_qty_min"][0] : "" );
// PAGE SETTINGS 
// Page Browser Window Title <title> 
$_ENV["request.cwpage"]["title"] = "Manage Discounts";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = $_ENV["request.cwpage"]["headtext"];
// Page request.cwpage.subheading (instructions) <h2> 
$_ENV["request.cwpage"]["subhead"] = "";
if (isset($discountQuery["discount_description"][0]) && strlen(trim($discountQuery["discount_description"][0]))) { $_ENV["request.cwpage"]["subhead"] .= $discountQuery["discount_description"][0]; }
$_ENV["request.cwpage"]["heading2"] = $_ENV["request.cwpage"]["subhead"];
// current menu marker 
if ($_ENV["request.cwpage"]["editMode"] == 'add') {
	$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"];
} else if ($discountQuery["discount_archive"][0] == 1) {
	$_ENV["request.cwpage"]["currentNav"] = 'discounts.php?view=arch';
} else {
	$_ENV["request.cwpage"]["currentNav"] = 'discounts.php';
}
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"] = 1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"] = 1;
// if editing, get advanced details 
if ($_ENV["request.cwpage"]["editMode"] == 'edit') {
	// queries for item selection 
	// QUERY: get categories, secondary cats (all) 
	$listC = CWquerySelectCategories();
	$listSC = CWquerySelectScndCategories();
	// QUERY: get categories assigned to this discount 
	$listDiscCats = CWquerySelectDiscountRelCategories($_POST["discount_id"]);
	// QUERY: get secondary categories assigned to this discount 
	$listDiscScndCats = CWquerySelectDiscountRelSecondaries($_POST["discount_id"]);
	// QUERY: get all products related to this discount 
	$discountProductsQuery = CWquerySelectDiscountProducts($_POST["discount_id"],null,true);
	// QUERY: get all skus related to this discount 
	$discountSkusQuery = CWquerySelectDiscountSkus($_POST["discount_id"], null, null, true);
	// Create a list of assigned categories for the checkboxes 
	if (isset($listDiscCats["discount2category_category_id"])) $listRelCats = $listDiscCats["discount2category_category_id"];
	else $listRelCats = array();
	// Create a list of assigned secondary categories for the select menus 
	if (isset($listDiscScndCats["discount2category_category_id"])) $listRelScndCats = $listDiscScndCats["discount2category_category_id"];
	else $listRelScndCats = array();
	// dynamic form elements, save as variables for use on multiple tabs 
	$_ENV["request.cwpage"]["discountSubmitButton"] = '<input name="updateDiscount" type="submit" class="CWformButton" id="updateDiscount" value="Save Discount">';
	$_ENV["request.cwpage"]["discountArchiveButton"] = "<a class=\"CWbuttonLink\" onClick=\"return confirm('Archive Discount ".CWstringFormat($_POST["discount_name"])."?');\" title=\"Archive Discount: ".CWstringFormat($_POST["discount_name"])."\"
		href=\"discounts.php?archiveid=".$_POST["discount_id"]."\">Archive Discount</a>";
	$_ENV["request.cwpage"]["discountActivateButton"] = "<a class=\"CWbuttonLink\" title=\"Reactivate Discount: ".CWstringFormat($_POST["discount_name"])."\"
		href=\"discounts.php?reactivateid=".$_POST["discount_id"]."\">Activate Discount</a>";
	$_ENV["request.cwpage"]["discountDeleteButton"] = "<a class=\"CWbuttonLink deleteButton\" onClick=\"return confirm('Delete Discount ".CWstringFormat($discountQuery["discount_merchant_id"][0]).": ".CWstringFormat($discountQuery["discount_name"][0])."?')\"
		href=\"discount-details.php?deleteDisc=".$_POST["discount_id"]."&returnUrl=discounts.php?userconfirm=Discount Deleted\">Delete Discount</a>";
}
// START OUTPUT ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php
include("cwadminapp/inc/cw-inc-admin-scripts.php");
// page js ?>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			// global discount hides association options
			jQuery('input#discount_global').click(function(){
				if (jQuery(this).prop('checked')==true){
					jQuery('#CWadminTabWrapper ul.CWtabList a[href=#tab3]').parents('li').hide();
					jQuery(this).addClass('toggle');
				} else if (jQuery(this).hasClass('toggle') == true) {
					jQuery('#CWadminTabWrapper ul.CWtabList a[href="#tab3"]').parents('li').show();
				};
			});
			// sku shipping: partial sku_ship discounts not allowed
			var $skuShipSelect = function(el){
				var thisVal = jQuery(el).find('option:selected').val();
				var amountBox = jQuery('input#discount_amount');
				var typeSel = jQuery('select#discount_calc');
				if (thisVal=='sku_ship'){
					jQuery(amountBox).val('100').hide();
					jQuery(typeSel).val('percent').after('<p id="skuShipNote">Free Shipping</p>').hide();
				} else {
					jQuery(amountBox).show();
					jQuery(typeSel).show();
					jQuery('p#skuShipNote').remove();
				};
			};
			// run on change of discount type
			jQuery('select#discount_type').change(function(){
				$skuShipSelect(jQuery(this));
			});
			// also run on page load
			$skuShipSelect(jQuery('select#discount_type#'));
			// associated product click-to-select
			jQuery('#tblDiscProdSelect tr td').not(':has(a),:has(input)').css('cursor','pointer').click(function(event){
			if (event.target.type != 'checkbox') {
			jQuery(this).siblings('td.firstCheck').find(':checkbox').trigger('click');
			}
			}).hover(
			function(){
			jQuery(this).addClass('hoverCell');
			},
			function(){
			jQuery(this).removeClass('hoverCell');
			});
		});
		</script>
	</head>
<?php
// body gets a class to match the filename 
$page = explode('.',$_ENV["request.cw"]["thisPage"]);
$page_First = $page[0];
?>   
	<body <?php echo 'class="'.$page_First.'"'; ?>>
		<div id="CWadminWrapper">
			<!-- Navigation Area -->
			<div id="CWadminNav">
				<div class="CWinner">
					<?php include("cwadminapp/inc/cw-inc-admin-nav.php"); ?>
				</div>
				<!-- /end CWinner -->
			</div>
			<!-- /end CWadminNav -->
			<!-- Main Content Area -->
			<div id="CWadminPage">
				<!-- inside div to provide padding -->
				<div class="CWinner">
<?php
// page start content / dashboard 
include("cwadminapp/inc/cw-inc-admin-page-start.php");
if (strlen(trim($_ENV["request.cwpage"]["heading1"]))) { echo "<h1>".trim($_ENV["request.cwpage"]["heading1"])."</h1>"; }
if (strlen(trim($_ENV["request.cwpage"]["heading2"]))) { echo "<h2>".trim($_ENV["request.cwpage"]["heading2"])."</h2>"; }
?>
					<!-- Admin Alert - message shown to user -->
<?php
include("cwadminapp/inc/cw-inc-admin-alerts.php");
?>
					<!-- Page Content Area -->
					<div id="CWadminContent">
<?php
						// /////// 
						// ADD/UPDATE CUSTOMER 
						// /////// ?>
							<!-- TABBED LAYOUT -->
							<div id="CWadminTabWrapper">
								<!-- TAB LINKS -->
								<ul class="CWtabList">
									<?php // tab 1 ?>
									<li><a href="#tab1" title="Discount Info">Discount Details</a></li>
<?php
// tab 2 
if ($_ENV["request.cwpage"]["editMode"] == 'edit') {
?>
									<li><a href="#tab2" title="Conditions">Conditions</a></li>
<?php
}
// tab 3 
if (isset($discountQuery["discount_global"][0]) && $discountQuery["discount_global"][0] == 0 && $_ENV["request.cwpage"]["editMode"] == 'edit') {
?>
									<li><a href="#tab3" title="Associated Items">Associated Items</a></li>
<?php
}
// tab 4 
if ($ordersQuery["totalRows"]) {
?>
										<li><a href="#tab4" title="Usage History">Usage History</a></li>
<?php
}
?>
								</ul>
								<div class="CWtabBox">
									<?php // Discount Primary Info Form ?>
									<form name="discountDetails" method="post" action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate">
									<?php // FIRST TAB (status) ?>
									<div id="tab1" class="tabDiv">
										<h3>Discount Configuration</h3>
										<table class="noBorder">
										<tr><td>
										<?php // discount general info table ?>
													<table class="CWformTable wide">
														<tr class="headerRow">
														<th colspan="2">Discount Parameters</th>
														</tr>
														<?php // reference ID: merchant id code for discount ?>
														<tr>
															<th class="label" style="width:180px;">Reference ID</th>
															<td>
																<input name="discount_merchant_id" class="{required:true}" title="Reference ID is required" size="35" type="text" id="discount_merchant_id" value="<?php echo htmlentities($_POST["discount_merchant_id"]); ?>">
																<span class="smallPrint">For internal use only - must be unique</span>
															</td>
														</tr>
														<?php // discount name shown to use ?>
														<tr>
															<th class="label">Discount Name</th>
															<td>
																<input name="discount_name" class="{required:true}" title="Discount Name is required" size="35" type="text" id="discount_name" value="<?php echo htmlentities($_POST["discount_name"]); ?>">
																<span class="smallPrint">Discount heading shown to customer</span>
															</td>
														</tr>
														<?php // discount description ?>
														<tr>
															<th class="label">Description</th>
															<td><textarea name="discount_description" class="" title="Discount Description (optional)" cols="34" rows="2" id="discount_description"><?php echo $_POST["discount_description"]; ?></textarea></td>
														</tr>
														<?php // show description ?>
														<tr>
															<th class="label">Show Description</th>
															<td>
																<input name="discount_show_description" type="checkbox" class="" value="1"<?php if ($_POST["discount_show_description"] == 1) { ?> checked="checked"<?php } ?> id="discount_show_description">
																<span class="smallPrint">Show this description when the discount is activated</span>
															</td>
														</tr>

														<?php // promo code ?>
														<tr>
															<th class="label">Promotional Code</th>
															<td>
																<input name="discount_promotional_code" class="" title="Promotional Code" size="35" type="text" maxlength="100" id="discount_promotional_code" value="<?php echo $_POST["discount_promotional_code"]; ?>">
																<span class="smallPrint"><br>(Optional: if left blank, discount is applied automatically)</span>
															</td>
														</tr>
														<?php // start/end dates ?>
														<tr>
															<th class="label">Start Date</th>
															<td>
<?php
if ($_POST["discount_start_date"]) $_POST["discount_start_date"] = cartweaverScriptDate($_POST["discount_start_date"]);
?>
																<input name="discount_start_date" type="text" class="date_input_future {required:true}" title="Start date is required" value="<?php echo $_POST["discount_start_date"]; ?>" size="10" id="discount_start_date">
															</td>
														</tr>
														<tr>
															<th class="label">End Date</th>
															<td>
<?php
if ($_POST["discount_end_date"]) $_POST["discount_end_date"] =  cartweaverScriptDate($_POST["discount_end_date"]);
?>
																<input name="discount_end_date" type="text" class="date_input_future" value="<?php echo $_POST["discount_end_date"]; ?>" size="10" id="discount_end_date">
																<span class="smallPrint">(Optional: blank = no expiration)</span>
															</td>
														</tr>
														<?php // limits ?>
														<tr>
															<th class="label">Limit Total Uses</th>
															<td>
																<input name="discount_limit" type="text" class="{required:true}" title="Discount limit number is required" value="<?php if (is_numeric($_POST["discount_limit"])) { echo $_POST["discount_limit"]; } else { echo "0"; } ?>" size="6" id="discount_limit" onkeyup="extractNumeric(this,2,true)" onblur="checkValue(this)">
																<span class="smallPrint">Total number of times this discount may be applied. 0 = no limit</span>
															</td>
														</tr>
														<tr>
															<th class="label">Limit Customer Uses
																<br><span class="smallPrint">* Note: requires login</span></th>
															<td>
																<input name="discount_customer_limit" type="text" class="{required:true}" title="Discount customer limit number is required" value="<?php if (is_numeric($_POST["discount_customer_limit"])) { echo $_POST["discount_customer_limit"]; } else { echo "0"; } ?>" size="6" id="discount_customer_limit" onkeyup="extractNumeric(this,2,true)" onblur="checkValue(this)">
																<span class="smallPrint">Total number of times a customer may use this discount. 0 = no limit</span>
															</td>
														</tr>
													</table>
<?php
													// /end general info 
													// discount calculation table ?>
													<table class="CWformTable wide">
														<tr class="headerRow">
														<th colspan="2">Discount Calculation</th>
														</tr>
														<?php // discount type ?>
														<tr>
															<th class="label">Discount Applies To</th>
															<td>
															<select name="discount_type" id="discount_type">
<?php
for ($n=0; $n<$discountTypesQuery["totalRows"]; $n++) {
?>
																<option value="<?php echo $discountTypesQuery["discount_type"][$n]; ?>"<?php if ($_POST["discount_type"] == trim($discountTypesQuery["discount_type"][$n])) { ?> selected="selected"<?php } ?>><?php echo $discountTypesQuery["discount_type_description"][$n]; ?></option>
<?php
}
?>
															</select>
															</td>
														</tr>
														<?php // amount / calculation type ?>
														<tr>
															<th class="label" style="width:180px;">Amount/Rate</th>
															<td>
															<input name="discount_amount" class="{required:true}" title="Discount Amount is required" size="6" type="text" id="discount_amount" value="<?php echo $_POST["discount_amount"]; ?>" onkeyup="extractNumeric(this,2,true)" onblur="checkValue(this)">
															<select name="discount_calc" id="discount_calc">
																<option value="fixed"<?php if ($_POST["discount_calc"] == 'fixed') { ?> selected="selected"<?php } ?>>Fixed Amount</option>
																<option value="percent"<?php if ($_POST["discount_calc"] == 'percent') { ?> selected="selected"<?php } ?>>Percentage</option>
															</select>
															</td>
														</tr>
														<?php // global ?>
														<tr>
															<th class="label">Global Discount</th>
															<td>
																<input name="discount_global" type="checkbox" class="" value="1"<?php if ($_POST["discount_global"] == 1) { ?> checked="checked"<?php } ?> id="discount_global">
																<span class="smallPrint">Associate this discount with all active products</span>
															</td>
														</tr>
														<?php // exclusive ?>
														<tr>
															<th class="label">Exclusive</th>
															<td>
																<input name="discount_exclusive" type="checkbox" class="" value="1"<?php if ($_POST["discount_exclusive"] == 1) { ?> checked="checked"<?php } ?> id="discount_exclusive">
																<span class="smallPrint">If checked, discount cannot be used with other discounts of this type</span>
															</td>
														</tr>
														<?php // priority ?>
														<tr>
															<th class="label">Discount Priority</th>
															<td>
																<input name="discount_priority" type="text" class="{required:true}" maxlength="5" title="Discount priority number is required" value="<?php if (is_numeric($_POST["discount_priority"])) { echo $_POST["discount_priority"]; } else { echo "0"; } ?>" size="6" id="discount_priority" onkeyup="extractNumeric(this,2,true)"  onblur="checkValue(this)">
																<span class="smallPrint">Numeric only: if exclusive, higher priority discount is applied</span>
															</td>
														</tr>
													</table>
											</td></tr>
										</table>
<?php
										// /end discount calculation 
										// FORM BUTTONS ?>
										<div class="CWformButtonWrap"<?php if ($_ENV["request.cwpage"]["editMode"] == 'add') { ?> style="text-align:center"<?php } ?>>
<?php
// if editing 
if ($_ENV["request.cwpage"]["editMode"] == 'edit') {
	// submit button 
	echo $_ENV["request.cwpage"]["discountSubmitButton"];
	if ($_POST["discount_archive"] != 1) {
		// archive button 
		echo $_ENV["request.cwpage"]["discountArchiveButton"];
	} else {
		// activate 
		echo $_ENV["request.cwpage"]["discountActivateButton"];
	}
	// If there are no orders show delete button 
	if ($ordersQuery["totalRows"] == 0) {
		echo $_ENV["request.cwpage"]["discountDeleteButton"];
	} else {
?>
													<p>(Orders placed, delete disabled)</p>
<?php
	}
	// hidden fields ?>
												<input name="discount_id" type="hidden" id="discount_id" value="<?php echo $discountQuery["discount_id"][0]; ?>">
<?php
// if adding a new discount 
} else {
?>
												<div style="text-align:center;">
												<input name="AddDiscount" type="submit" class="CWformButton" id="AddDiscount" value="&raquo;&nbsp;Next">
												</div>
<?php
}
?>
										</div>
										<?php // /end form buttons ?>
									</div>
<?php
// /end tab 1 

if ($_ENV["request.cwpage"]["editMode"] == 'edit') {
	// SECOND TAB (conditions) ?>
										<div id="tab2" class="tabDiv">
											<h3>Configure Discount Requirements</h3>
											<table class="noBorder">
												<tr>
													<td>
														<?php // discount conditions table ?>
														<table class="CWformTable wide">
															<tr class="headerRow">
																<th>Condition</th>
																<th style="width:40px;">Active</th>
																<th>Filter</th>
															</tr>
															<?php // customer type ?>
															<tr>
																<th class="label" style="width:180px;">Customer Type</th>
																<td>
																	<input name="discount_filter_customer_type" type="checkbox" id="discount_filter_customer_type" title="Enable customer type filtering"<?php if ($_POST["discount_filter_customer_type"] == 1) { ?> checked="checked"<?php } ?>value="1">
																</td>
																<td class="noLink">
<?php
	for ($n=0; $n<$typesQuery["totalRows"]; $n++) {
?>
																	<label>
																	<input type="checkbox" name="discount_customer_type" value="<?php echo $typesQuery["customer_type_id"][$n]; ?>"<?php if (in_array($typesQuery["customer_type_id"][$n], $discountQuery["discount_customer_type"])) { ?> checked="checked"<?php } ?>>
																	<?php echo $typesQuery["customer_type_name"][$n]; ?><br></label>
<?php
	}
?>
																</td>
															</tr>
															<?php // customer id ?>
															<tr>
																<th class="label">Customer ID</th>
																<td>
																	<input name="discount_filter_customer_id" type="checkbox" id="discount_filter_customer_id" title="Enable customer ID filtering"<?php if ($_POST["discount_filter_customer_id"] == 1) { ?> checked="checked"<?php } ?> value="1">
																</td>
																<td>
																	<textarea name="discount_customer_id" class="" title="Discount Customer ID" cols="34" rows="4" id="discount_customer_id"><?php echo $_POST["discount_customer_id"]; ?></textarea>
																	<span class="smallPrint"><br>Enter a customer ID (e.g. F45A2F10-25-09), or list of IDs with commas</span>
																</td>
															</tr>
															<?php // cart total ?>
															<tr>
																<th class="label">Cart Total</th>
																<td>
																	<input name="discount_filter_cart_total" type="checkbox" id="discount_filter_cart_total" title="Enable cart total filtering"<?php if ($_POST["discount_filter_cart_total"] == 1) { ?> checked="checked"<?php } ?> value="1">
																</td>
																<td>
																	Min: <input name="discount_cart_total_min" type="text" id="discount_cart_total_min" size="6" value="<?php echo $_POST["discount_cart_total_min"]; ?>" onKeyUp="extractNumeric(this,2,true)"  onblur="checkValue(this)">&nbsp;&nbsp;
																	Max: <input name="discount_cart_total_max" type="text" id="discount_cart_total_max" size="6" value="<?php echo $_POST["discount_cart_total_max"]; ?>" onKeyUp="extractNumeric(this,2,true)"  onblur="checkValue(this)">&nbsp;&nbsp;
																<span class="smallPrint"><br>Applies to cart subtotal before tax or shipping</span>
																</td>
															</tr>
															<?php // cart qty ?>
															<tr>
																<th class="label">Cart Quantity</th>
																<td>
																	<input name="discount_filter_cart_qty" type="checkbox" id="discount_filter_cart_qty" title="Enable cart quantity filtering"<?php if ($_POST["discount_filter_cart_qty"] == 1) {?> checked="checked"<?php } ?> value="1">
																</td>
																<td>
																	Min: <input name="discount_cart_qty_min" type="text" id="discount_cart_qty_min" size="6" value="<?php echo $_POST["discount_cart_qty_min"]; ?>" onKeyUp="extractNumeric(this,2,true)"  onblur="checkValue(this)">&nbsp;&nbsp;
																	Max: <input name="discount_cart_qty_max" type="text" id="discount_cart_qty_max" size="6" value="<?php echo $_POST["discount_cart_qty_max"]; ?>" onKeyUp="extractNumeric(this,2,true)"  onblur="checkValue(this)">&nbsp;&nbsp;
																<span class="smallPrint"><br>Applies to total number of items in cart</span>
																</td>
															</tr>
															<?php // item qty ?>
															<tr>
																<th class="label">Item Quantity</th>
																<td>
																	<input name="discount_filter_item_qty" type="checkbox" id="discount_filter_item_qty" title="Enable item quantity filtering"<?php if ($_POST["discount_filter_item_qty"] == 1) {?> checked="checked"<?php } ?> value="1">
																</td>
																<td>
																	Min: <input name="discount_item_qty_min" type="text" id="discount_item_qty_min" size="6" value="<?php echo $_POST["discount_item_qty_min"]; ?>" onKeyUp="extractNumeric(this,2,true)"  onblur="checkValue(this)">&nbsp;&nbsp;
																	Max: <input name="discount_item_qty_max" type="text" id="discount_item_qty_max" size="6" value="<?php echo $_POST["discount_item_qty_max"]; ?>" onKeyUp="extractNumeric(this,2,true)"  onblur="checkValue(this)">&nbsp;&nbsp;
																<span class="smallPrint"><br>Applies to quantity of each associated item</span>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
											<?php // FORM BUTTONS ?>
											<div class="CWformButtonWrap">
<?php
	// submit button 
	echo $_ENV["request.cwpage"]["discountSubmitButton"];
	// archive button 
	if ($_POST["discount_archive"] != 1) {
		// archive button 
		echo $_ENV["request.cwpage"]["discountArchiveButton"];
	} else {
		// activate 
		echo $_ENV["request.cwpage"]["discountActivateButton"];
	}
	// If there are no orders show delete button 
	if ($ordersQuery["totalRows"] == 0) {
		echo $_ENV["request.cwpage"]["discountDeleteButton"];
	} else {
?>
													<p>(Orders placed, delete disabled)</p>
<?php
	}
?>
											</div>
											<?php // /end form buttons ?>
										</div>
<?php
	// /end tab 2 
}
?>
								</form>
<?php
// /End Discount Primary Info Form 

// tab 3 
if (isset($discountQuery["discount_global"][0]) && $discountQuery["discount_global"][0] == 0 && $_ENV["request.cwpage"]["editMode"] == 'edit') {
	// THIRD TAB (associated items) ?>
										<div id="tab3" class="tabDiv">
											<h3>Associated Items</h3>
												<table class="CWformTable wide">
												<tr>
												<th class="label">
												Select Association Method
												</th>
												<td>
													<?php // discount method selection form ?>
													<form name="discountMethod" action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" method="post">
														<?php // select method ?>
														<select name="discount_association_method" id="discount_association_method">
															<option value="categories"<?php if ($discountQuery["discount_association_method"][0] == 'categories') { ?> selected="selected"<?php } ?>>Categories</option>
															<option value="products"<?php if ($discountQuery["discount_association_method"][0] == 'products') { ?> selected="selected"<?php } ?>>Products</option>
															<option value="skus"<?php if ($discountQuery["discount_association_method"][0] == 'skus') { ?> selected="selected"<?php } ?>>Skus</option>
														</select>
														<?php // submit selection ?>
														<input name="changeMethod" type="submit" class="submitButton" value="Select">
														<span class="smallPrint">&nbsp;&nbsp;Note: only the selected method will apply</span>
														<input type="hidden" name="discount_id" value="<?php echo $discountQuery["discount_id"][0]; ?>">
														<input name="discount_archive" type="hidden" id="discount_archive" value="<?php echo $discountQuery["discount_archive"][0]; ?>">
													</form>
												</td>
												</tr>
												</table>
												<p>&nbsp;</p>
<?php
	// ASSOCIATED ITEMS: SHOW EXISTING 
	// set default if not already in place 
	if (!strlen(trim($discountQuery["discount_association_method"][0]))) {
		$discountQuery["discount_association_method"][0] = 'products';
	}
	// products 
	if ($discountQuery["discount_association_method"][0] == 'products') {
?>
												<div class="prodSel products">
<?php
		// include product selection 
		include("cwadminapp/inc/cw-inc-admin-discount-products.php");
?>
												</div>
<?php
	} else if ($discountQuery["discount_association_method"][0] == 'skus') {
		// skus ?>
												<div class="prodSel skus">
<?php
		// include sku selection 
		include("cwadminapp/inc/cw-inc-admin-discount-skus.php");
?>
												</div>
<?php
	} else if ($discountQuery["discount_association_method"][0] == 'categories') {
		// categories ?>
												<div class="prodSel categories">
													<h3>Associated Categories</h3>
													<?php // categories form ?>
													<form name="discountCats" method="post" action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate">
													<table class="CWformTable wide">
														<tr class="headerRow">
															<th>Select <?php echo $_ENV["application.cw"]["adminLabelCategories"]; ?></th>
														</tr>
														<tr>
															<td>
<?php
		$disabledBoxes = '';
		$disabledCheckedCat = array();
		$splitC = 0;
?>
																<div class="formSubCol">
<?php
		for ($n=0; $n<$listC["totalRows"]; $n++) {
			$checkboxCode = "<label ";
			if ($listC["category_archive"][$n] == 1) { $checkboxCode .= " class=\"disabled\""; }
			$checkboxCode .= ">
																	<input type=\"checkbox\" name=\"category_id[".$n."]\" value=\"".$listC["category_id"][$n]."\"";
			if ($listC["category_archive"][$n] == 1) { $checkboxCode .= " disabled=\"disabled\""; }
			if (in_array($listC["category_id"][$n], $listRelCats)) { $checkboxCode .= " checked=\"checked\""; }
			$checkboxCode .= ">
																	&nbsp;".$listC["category_name"][$n]."
																	</label><br>";
			if ($listC["category_archive"][$n] == 1) {
				$catsArchived = 1;
			}
			// break into two columns 
			if (($n >= ($listC["totalRows"]/2) - .5) && $splitC == 0 && $listC["category_archive"][$n] != 1) {
				$splitC = 1;
				// create new div in code output to page 
				$checkboxCode .= '<' . '/div>' . '<' . 'div class="formSubCol">';
			}
			// show enabled cats first, then archived 
			if ($listC["category_archive"][$n] != 1) {
				echo $checkboxCode;
			} else {
				if (in_array($listC["category_id"][$n], $listRelCats)) {
					$disabledCheckedCat[] = $listC["category_id"][$n];
				}
				$disabledBoxes .= $checkBoxCode;
			}
		}
?>
																</div>
<?php
		if (strlen(trim($disabledBoxes))) {
?>
																<div class="clear"></div>
<?php
			echo $disabledBoxes;
			if (sizeof($disabledCheckedCat)) {
				echo "<input type=\"hidden\" name=\"disabledCheckedCat\" value=\"".implode(",", $disabledCheckedCat)."\" />";
			}
		}
		// if some cats are archived, show note 
		if (isset($catsArchived)) {
?>
																	<span class="smallPrint">
																		<br>Archived <?php echo strtolower($_ENV["application.cw"]["adminLabelCategories"]); ?> are disabled.
																		<br><a href="categories-main.php?view=arch">Activate</a> <?php echo strtolower($_ENV["application.cw"]["adminLabelCategories"]); ?> to select.
																		<br>
																	</span>
<?php
		}
?>
															</td>
														</tr>
														<tr class="headerRow">
															<th>Select <?php echo $_ENV["application.cw"]["adminLabelSecondaries"]; ?></th>
														</tr>
														<tr>
															<td>
<?php
		$disabledBoxes = '';
		$disabledCheckedScnd = array();
		$splitSC = 0;
?>
																<div class="formSubCol">
<?php
		for ($n=0; $n<$listSC["totalRows"]; $n++) {
			$checkboxCode = "<label ";
			if ($listSC["secondary_archive"][$n] == 1) { $checkboxCode .= " class=\"disabled\""; }
			$checkboxCode .= ">
																	<input type=\"checkbox\" name=\"secondary_id[".$n."]\" value=\"".$listSC["secondary_id"][$n]."\"";
			if ($listSC["secondary_archive"][$n] == 1) { $checkboxCode .= " disabled=\"disabled\""; }
			if (in_array($listSC["secondary_id"][$n], $listRelScndCats)) { $checkboxCode .= " checked=\"checked\""; }
			$checkboxCode .= ">
																	&nbsp;".$listSC["secondary_name"][$n]."
																	</label><br>";
			if ($listSC["secondary_archive"][$n] == 1) {
				$scndcatsArchived = 1;
			}
			// break into two columns 
			if (($n >= ($listSC["totalRows"]/2) - .5) && $splitSC == 0 && $listSC["secondary_archive"][$n] != 1) {
				$splitSC = 1;
				// create new div in code output to page 
				$checkboxCode .= '<' . '/div>' . '<' . 'div class="formSubCol">';
			}
			// show enabled cats first, then archived 
			if ($listSC["secondary_archive"][$n] != 1) {
				echo $checkboxCode;
			} else {
				if (in_array($listSC["secondary_id"][$n], $listRelScndCats)) {
					$disabledCheckedScnd[] = $listC["secondary_id"][$n];
				}
				$disabledBoxes .= $checkBoxCode;
			}
?>
																</div>
<?php
			if (strlen(trim($disabledBoxes))) {
?>
																<div class="clear"></div>
<?php
				echo $disabledBoxes;
				if (sizeof($disabledCheckedScnd)) {
					echo "<input type=\"hidden\" name=\"disabledCheckedScnd\" value=\"".implode(",", $disabledCheckedScnd)."\" />";
				}
			}
		}
		// if some cats are archived, show note 
		if (isset($scndcatsArchived)) {
?>
																	<div class="smallPrint">
																		Archived <?php echo strtolower($_ENV["application.cw"]["adminLabelSecondaries"]); ?> are disabled.
																		<br><a href="categories-secondary.php?view=arch">Activate</a> <?php echo strtolower($_ENV["application.cw"]["adminLabelSecondaries"]); ?> to select.
																	</div>
<?php
		}
?>
															</td>
														</tr>
													</table>
													<?php // /end cats/secondaries table ?>
													<div style="clear:both">
														<input name="saveDiscCats" type="submit" class="CWformButton" id="saveDiscCats" value="Save Selection">
														<input name="discount_id" type="hidden" value="<?php echo $_POST["discount_id"]; ?>">
														<?php // hidden fields force processing when no boxes selected (clear all) ?>
														<input name="category_id[]" type="hidden" value="">
														<input name="secondary_id[]" type="hidden" value="">
													</div>
													</form>
													<?php // /end categories form ?>
												</div>
<?php
	}
?>
										</div>
<?php
}
// /end tab 3 

if ($ordersQuery["totalRows"]) {
	// FOURTH TAB (discount usage details) ?>
										<div id="tab4" class="tabDiv">
											<h3>Discount Usage</h3>
											<table id="tblOrderDetails" class="wide CWinfoTable" style="width:735px;">
												<thead>
												<tr class="sortRow">
													<th class="noSort">View Order</th>
													<th class="order_id">Order ID</th>
													<th width="75" class="order_date">Date</th>
													<th class="noSort">Customer</th>
												</tr>
												</thead>
												<tbody>
<?php
	for ($n=0; $n<$ordersQuery["totalRows"]; $n++) {
		$order_id = $ordersQuery["discount_usage_order_id"][$n];
?>
												<tr>
													<td style="text-align:center">
														<a href="order-details.php?order_id=<?php echo $order_id; ?>&amp;returnUrl=<?php echo urlencode($_ENV["request.cwpage"]["baseURL"]); ?>">
														<img src="img/cw-edit.gif" alt="View Order Details" width="15" height="15"></a>
													</td>
													<td>
														<a href="order-details.php?order_id=<?php echo $order_id; ?>" class="productLink">
<?php
		if (strlen($order_id) > 16) { echo "...".substr($order_id, strlen($order_id) - 16); } else { echo $order_id; }
?>
														</a>
													</td>
													<td style="text-align:right;"><?php echo cartweaverDate($ordersQuery["discount_usage_datetime"][$n]); ?></td>
													<td class="noLink">
														<a href="customer-details.php?customer_id=<?php echo $ordersQuery["discount_usage_customer_id"][$n]; ?>" class="columnLink"><?php echo $ordersQuery["customer_last_name"][$n]; ?>, <?php echo $ordersQuery["customer_first_name"][$n]; ?></a>
													</td>
												</tr>
<?php
	}
?>
												</tbody>
											</table>
										</div>
<?php
}
// /END tab 4 ?>
								</div>
								<?php // /END tab content ?>
							</div>
<?php
						// /////// 
						// END ADD/UPDATE CUSTOMER 
						// /////// ?>
						<div class="clear"></div>
					</div>
					<!-- /end Page Content -->
				</div>
				<!-- /end CWinner -->
<?php
// page end content / debug 
include("cwadminapp/inc/cw-inc-admin-page-end.php");
?>
				<!-- /end CWadminPage-->
				<div class="clear"></div>
					</div>
					<!-- /end CWadminPage -->
					<div class="clear"></div>
				</div>
				<!-- /end CWinner -->
			</div>
<?php
// page end content / debug 
include("cwadminapp/inc/cw-inc-admin-page-end.php");
?>
			<!-- /end CWadminPage-->
			<div class="clear"></div>
		</div>
		<!-- /end CWadminWrapper -->
	</body>
</html>