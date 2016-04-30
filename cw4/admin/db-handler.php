<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: db-handler.php
File Date: 2012-02-01
Description: Handles database operations
Note:
Run the cleanup script with caution! It will permanently remove _all_ data from your CW tables.

==========================================================
*/
// time out the page if it takes too long - avoid server overload for massive product deletions 
if (!ini_get("safe_mode") && !in_array("set_time_limit", explode(",", ini_get("disable_functions")))) @set_time_limit(9000);
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// product functions
require_once("cwadminapp/func/cw-func-product.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("developer");
// PAGE PARAMS 
if (!isset($_GET["mode"])) $_GET["mode"] = "";
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("userconfirm,useralert,mode");
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// data delete must be enabled 
if ($_GET["mode"] == 'testdata' && !$_ENV["application.cw"]["appDataDeleteEnabled"]) {
	CWpageMessage("alert","Data Deletion Disabled");
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&mode=declined&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."");
}
// GET TOTALS 
// products 
if (!isset($_ENV["application.cw"]["listProducts"])) $_ENV["application.cw"]["listProducts"] = array();
$_ENV["request.cwpage"]["productCt"] = sizeof($_ENV["application.cw"]["listProducts"]);
// categories 
if (!isset($_ENV["application.cw"]["listCategories"])) $_ENV["application.cw"]["listCategories"] = array();
$_ENV["request.cwpage"]["categoryCt"] = sizeof($_ENV["application.cw"]["listCategories"]);
// secondary categories 
if (!isset($_ENV["application.cw"]["listSubCategories"])) $_ENV["application.cw"]["listSubCategories"] = array();
$_ENV["request.cwpage"]["secondaryCt"] = sizeof($_ENV["application.cw"]["listSubCategories"]);
// customers 
$customerQuery = CWquerySelectCustomers();
$_ENV["request.cwpage"]["customerCt"] = $customerQuery["totalRows"];
// discounts 
$discountQuery = CWquerySelectDiscounts();
$_ENV["request.cwpage"]["discountCt"] = $discountQuery["totalRows"];
// orders 
$orderQuery = CWquerySelectOrders();
$_ENV["request.cwpage"]["orderCt"] = $orderQuery["totalRows"];
// options 
$optionsQuery = CWquerySelectOptions();
$_ENV["request.cwpage"]["optionCt"] = $optionsQuery["totalRows"];
// shipping 
$shippingQuery = CWquerySelectShippingMethods();
$_ENV["request.cwpage"]["shippingCt"] = $shippingQuery["totalRows"];
// taxes 
$taxGroupsQuery = CWquerySelectTaxGroups();
$_ENV["request.cwpage"]["taxCt"] = $taxGroupsQuery["totalRows"];
// list of tables for auto increment reset 
$resetTables = array();
// /////// 
// DELETE TEST DATA 
// /////// 
if ($_GET["mode"] == 'testdata' && isset($_POST['testDataDelete']) && $_POST["testDataDelete"] == 'true') {
	// defaults for checkboxes 
	if (!isset($_POST["emptyCart"])) $_POST["emptyCart"] = "false";
	if (!isset($_POST["emptyCats"])) $_POST["emptyCats"] = "false";
	if (!isset($_POST["emptyCustomers"])) $_POST["emptyCustomers"] = "false";
	if (!isset($_POST["emptyDiscounts"])) $_POST["emptyDiscounts"] = "false";
	if (!isset($_POST["emptyOptions"])) $_POST["emptyOptions"] = "false";
	if (!isset($_POST["emptyOrders"])) $_POST["emptyOrders"] = "false";
	if (!isset($_POST["emptyProducts"])) $_POST["emptyProducts"] = "false";
	if (!isset($_POST["emptyShipping"])) $_POST["emptyShipping"] = "false";
	if (!isset($_POST["emptySubcats"])) $_POST["emptySubcats"] = "false";
	if (!isset($_POST["emptyTax"])) $_POST["emptyTax"] = "false";
	if (!isset($_POST["resetIncrement"])) $_POST["resetIncrement"] = "false";
	// DELETE SELECTED ITEMS 
	// cart 
	if ($_POST["emptyCart"] == "true") {
		$rsClear = "DELETE from cw_cart";
		mysql_query($rsClear) or die(mysql_error());
		if ($_POST["resetIncrement"] == "true") {
			$resetTables[] = 'cw_cart';
		}
	}
	// categories 
	if ($_POST["emptyCats"] == "true") {
		$rsClear = "DELETE from cw_categories_primary";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_discount_categories WHERE discount_category_type = 1";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_product_categories_primary";
		mysql_query($rsClear) or die(mysql_error());
		if ($_POST["resetIncrement"] == "true") {
			$resetTables[] = 'cw_categories_primary';
			$resetTables[] = 'cw_product_categories_primary';
		}
	}
	// secondary categories 
	if ($_POST["emptySubcats"] == "true") {
		$rsClear = "DELETE from cw_categories_secondary";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_discount_categories WHERE discount_category_type = 2";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_product_categories_secondary";
		mysql_query($rsClear) or die(mysql_error());
		if ($_POST["resetIncrement"] == "true") {
			$resetTables[] = 'cw_categories_secondary';
			$resetTables[] = 'cw_product_categories_secondary';
			if ($_POST["emptyCats"] == "true") {
				$resetTables[] = 'cw_discount_categories';
			}
		}
	}
	// products 
	if ($_POST["emptyProducts"] == "true") {
		// function clears out selective related data 
		foreach ($_ENV["application.cw"]["listProducts"] as $key => $prodid) {
			CWfuncProductDelete($prodid);
		}
		// empty all product tables for complete cleanout 
		$rsClear = "DELETE from cw_products";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_skus";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_discount_products";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_discount_skus";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_product_upsell";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_product_options";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_product_images";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_product_categories_primary";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_product_categories_secondary";
		mysql_query($rsClear) or die(mysql_error());
		if ($_POST["resetIncrement"] == "true") {
			$resetTables[] = 'cw_products';
			$resetTables[] = 'cw_skus';
			$resetTables[] = 'cw_discount_products';
			$resetTables[] = 'cw_discount_skus';
			$resetTables[] = 'cw_product_upsell';
			$resetTables[] = 'cw_product_options';
			$resetTables[] = 'cw_categories_primary';
			$resetTables[] = 'cw_product_images';
			$resetTables[] = 'cw_product_categories_primary';
			$resetTables[] = 'cw_product_categories_secondary';
		}
	}
	// options 
	if ($_POST["emptyOptions"] == "true") {
		$rsClear = "DELETE from cw_options";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_option_types";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_product_options";
		mysql_query($rsClear) or die(mysql_error());
		if ($_POST["resetIncrement"] == "true") {
			$resetTables[] = 'cw_options';
			$resetTables[] = 'cw_option_types';
			$resetTables[] = 'cw_product_options';
		}
	}
	// customers 
	if ($_POST["emptyCustomers"] == "true") {
		$rsClear = "DELETE from cw_customers";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_customer_stateprov";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_orders";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_order_sku_data";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_order_skus";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_order_payments";
		mysql_query($rsClear) or die(mysql_error());
		if ($_POST["resetIncrement"] == "true") {
			$resetTables[] = 'cw_customers';
			$resetTables[] = 'cw_customer_stateprov';
			$resetTables[] = 'cw_orders';
			$resetTables[] = 'cw_order_sku_data';
			$resetTables[] = 'cw_order_skus';
			$resetTables[] = 'cw_order_payments';
		}
	}
	// discounts 
	if ($_POST["emptyDiscounts"] == "true") {
		$rsClear = "DELETE from cw_discounts";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_discount_products";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_discount_skus";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_discount_categories";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_discount_usage";
		mysql_query($rsClear) or die(mysql_error());
		if ($_POST["resetIncrement"] == "true") {
			$resetTables[] = 'cw_discounts';
			$resetTables[] = 'cw_discount_products';
			$resetTables[] = 'cw_discount_skus';
			$resetTables[] = 'cw_discount_categories';
			$resetTables[] = 'cw_discount_usage';
		}
	}
	// orders 
	if ($_POST["emptyOrders"] == "true") {
		$rsClear = "DELETE from cw_orders";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_order_sku_data";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_order_skus";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_order_payments";
		mysql_query($rsClear) or die(mysql_error());
		if ($_POST["resetIncrement"] == "true") {
			$resetTables[] = 'cw_orders';
			$resetTables[] = 'cw_order_sku_data';
			$resetTables[] = 'cw_order_skus';
			$resetTables[] = 'cw_order_payments';
		}
	}
	// shipping 
	if ($_POST["emptyShipping"] == "true") {
		$rsClear = "DELETE from cw_ship_methods";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_ship_ranges";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_ship_method_countries";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "UPDATE cw_stateprov SET stateprov_ship_ext = 0";
		mysql_query($rsClear) or die(mysql_error());
		if ($_POST["resetIncrement"] == "true") {
			$resetTables[] = 'cw_ship_methods';
			$resetTables[] = 'cw_ship_ranges';
			$resetTables[] = 'cw_ship_method_countries';
			$resetTables[] = 'cw_stateprov';
		}
	}
	// taxes 
	if ($_POST["emptyTax"] == "true") {
		$rsClear = "DELETE from cw_tax_groups";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_tax_rates";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "DELETE from cw_tax_regions";
		mysql_query($rsClear) or die(mysql_error());
		$rsClear = "UPDATE cw_stateprov SET stateprov_tax = 0";
		mysql_query($rsClear) or die(mysql_error());
		if ($_POST["resetIncrement"] == "true") {
			$resetTables[] = 'cw_tax_groups';
			$resetTables[] = 'cw_tax_rates';
			$resetTables[] = 'cw_tax_regions';
			$resetTables[] = 'cw_stateprov';
		}
	}
	// reset auto increment on listed tables 
	if (sizeof($resetTables)) {
		foreach ($resetTables as $key => $t) {
			$rsReset = "ALTER TABLE ".$t." auto_increment = 1";
			mysql_query($rsReset) or die(mysql_error());
		}
	}
	// return to page showing confirmation, resetting application vars
	CWpageMessage("confirm","Data Deletion Complete");
	header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&mode=testdata&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&resetapplication=".$_ENV["application.cw"]["storePassword"]."");
}
// /////// 
// /END DELETE TEST DATA 
// /////// 
// SUBHEADING 
$_ENV["request.cwpage"]["subHead"] = "Existing Products: ".$_ENV["request.cwpage"]["productCt"]."&nbsp;&nbsp;
".$_ENV["application.cw"]["adminLabelCategories"].": ".$_ENV["request.cwpage"]["categoryCt"]."&nbsp;&nbsp;
".$_ENV["application.cw"]["adminLabelSecondaries"].": ".$_ENV["request.cwpage"]["secondaryCt"]."&nbsp;&nbsp;";
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Manage Data";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["title"] = "Database Management";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = $_ENV["request.cwpage"]["subHead"];
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = "db-handler.php";
if (strlen(trim($_GET["mode"]))) { $_ENV["request.cwpage"]["currentNav"] = 'db-handler.php?mode='.$_GET['mode']; }
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"] = 1;
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
// fancybox ?>
		<link href="js/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js"></script>
		<?php // PAGE JAVASCRIPT ?>
		<script type="text/javascript">
			// this takes the ID of a checkbox, the number to show in the alert
			function confirmDelete(boxID,itemCt){
			// if this cat has itemucts
				if (itemCt > 0){
					if (itemCt > 1){var itemWord = 'records'}else{var itemWord = 'record'};
				var confirmBox = '#'+ boxID;
					// if the box is checked and itemToggle is true
					if( jQuery(confirmBox).is(':checked') ){
					clickConfirm = confirm("Warning: \n" + itemCt + ' ' + itemWord + " will be deleted, along with all related data.\nContinue?");
					// if confirm is returned false
					if(!clickConfirm){
						jQuery(confirmBox).prop('checked','');
					};

					};
					// end if checked
				};
				// end if prodct
			};
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
// user alerts 
include("cwadminapp/inc/cw-inc-admin-alerts.php");
?>
					<!-- Page Content Area -->
					<div id="CWadminContent">
						<!-- //// PAGE CONTENT ////  -->
						<p>&nbsp;</p>
<?php
// PAGE MODE 
switch ($_GET["mode"]) {
	// DELETE TEST DATA 
	case "testdata":
?>
						<h3>Select Items for Deletion</h3>
						<p class="subText">CAUTION! Deletions are permanent. It is <em>highly recommended</em> to create a backup of your database before proceeding.</p>
<?php
		// DATA CLEANUP FORM 
		// form submits to same page ?>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>&mode=testdata" name="cleanupForm" id="cleanupForm" method="post">
								<table class="CWstripe CWformTable wide">
								<tbody>
								<?php // products ?>
								<tr>
									<th class="label">
										<p><strong>Delete Products</strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="emptyProducts" id="emptyProducts"
											class="formCheckbox delBox" value="true"
											<?php if ($_ENV["request.cwpage"]["productCt"] > 0) { ?>onclick="confirmDelete('emptyProducts',<?php $_ENV["request.cwpage"]["productCt"]; ?>)"<?php } ?>>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Removes all data stored in cw_orders, along with all relative order data.</p>
									</td>
								</tr>

								<?php // options ?>
								<tr>
									<th class="label">
										<p><strong>Delete Options</strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="emptyOptions" id="emptyOptions"
											class="formCheckbox delBox" value="true"
											<?php if ($_ENV["request.cwpage"]["optionCt"] > 0) { ?>onclick="confirmDelete('emptyOptions',<?php $_ENV["request.cwpage"]["optionCt"]; ?>)"<?php } ?>>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Removes all product options, option groups, and related product option data.</p>
									</td>
								</tr>

								<?php // cart ?>
								<tr>
									<th class="label">
										<p><strong>Delete Cart Data</strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="emptyCart" id="emptyCart"
											class="formCheckbox delBox" value="true"
											>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Removes all data stored in cw_cart, from open or unfinished orders.</p>
									</td>
								</tr>

								<?php // categories ?>
								<tr>
									<th class="label">
										<p><strong>Delete Categories</strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="emptyCats" id="emptyCats"
											class="formCheckbox delBox" value="true"
											<?php if ($_ENV["request.cwpage"]["categoryCt"] > 0) { ?>onclick="confirmDelete('emptyCats',<?php $_ENV["request.cwpage"]["categoryCt"]; ?>)"<?php } ?>>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Removes all data stored in cw_categories_primary, along with relative product/category data.</p>
									</td>
								</tr>

								<?php // secondary categories ?>
								<tr>
									<th class="label">
										<p><strong>Delete <?php echo $_ENV["application.cw"]["adminLabelSecondaries"]; ?></strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="emptySubcats" id="emptySubcats"
											class="formCheckbox delBox" value="true"
											<?php if ($_ENV["request.cwpage"]["secondaryCt"] > 0) { ?>onclick="confirmDelete('emptySubcats',<?php $_ENV["request.cwpage"]["secondaryCt"]; ?>)"<?php } ?>>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Removes all data stored in cw_categories_secondary, along with relative product/category data.</p>
									</td>
								</tr>

								<?php // customers ?>
								<tr>
									<th class="label">
										<p><strong>Delete Customers</strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="emptyCustomers" id="emptyCustomers"
											class="formCheckbox delBox" value="true"
											<?php if ($_ENV["request.cwpage"]["customerCt"] > 0) { ?>onclick="confirmDelete('emptyCustomers',<?php $_ENV["request.cwpage"]["customerCt"]; ?>)"<?php } ?>>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Removes all data stored in cw_customers, and all stored orders, along with relative customer data.</p>
									</td>
								</tr>

								<?php // discounts ?>
								<tr>
									<th class="label">
										<p><strong>Delete Discounts</strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="emptyDiscounts" id="emptyDiscounts"
											class="formCheckbox delBox" value="true"
											<?php if ($_ENV["request.cwpage"]["discountCt"] > 0) { ?>onclick="confirmDelete('emptyDiscounts',<?php $_ENV["request.cwpage"]["discountCt"]; ?>)"<?php } ?>>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Removes all data stored in cw_discounts, along with relative discount data.</p>
									</td>
								</tr>

								<?php // orders ?>
								<tr>
									<th class="label">
										<p><strong>Delete Orders</strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="emptyOrders" id="emptyOrders"
											class="formCheckbox delBox" value="true"
											<?php if ($_ENV["request.cwpage"]["orderCt"] > 0) { ?>onclick="confirmDelete('emptyOrders',<?php $_ENV["request.cwpage"]["orderCt"]; ?>)"<?php } ?>>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Removes all data stored in cw_orders, along with all relative order data.</p>
									</td>
								</tr>

								<?php // shipping ?>
								<tr>
									<th class="label">
										<p><strong>Delete Shipping Data</strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="emptyShipping" id="emptyShipping"
											class="formCheckbox delBox" value="true"
											<?php if ($_ENV["request.cwpage"]["shippingCt"] > 0) { ?>onclick="confirmDelete('emptyShipping',<?php $_ENV["request.cwpage"]["shippingCt"]; ?>)"<?php } ?>>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Removes all shipping ranges, methods and regions.</p>
									</td>
								</tr>

								<?php // taxes ?>
								<tr>
									<th class="label">
										<p><strong>Delete <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Data</strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="emptyTax" id="emptyTax"
											class="formCheckbox delBox" value="true"
											<?php if ($_ENV["request.cwpage"]["taxCt"] > 0) { ?>onclick="confirmDelete('emptyTax',<?php $_ENV["request.cwpage"]["taxCt"]; ?>)"<?php } ?>>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Removes all <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> groups, rates and regions.</p>
									</td>
								</tr>
								<?php // reset auto increment ?>
								<tr>
									<th class="label">
										<p><strong>Reset Auto Increment Value</strong></p>
									</th>
									<td class="noLink noHover">
											<input type="checkbox" name="resetIncrement" id="resetIncrement"
											class="formCheckbox delBox" value="true">
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<p>Resets all emptied tables to ID 1 during the deletion process</p>
									</td>
								</tr>
								</tbody>
								</table>
								<p>&nbsp;</p>
								<p><strong>WARNING: Deletions cannot be undone. All selected data will be permanently erased!</strong></p>
								<p>&nbsp;</p>
								<?php // submit button ?>
								<input name="submitDelete" type="submit" class="submitButton" id="submitDelete" value="Start Cleanup">
								<?php // hidden field, required for operation ?>
								<input type="hidden" name="testDataDelete" value="true">
							</form>
<?php
		break;
		// /END TEST DATA MODE 
		// DEFAULT MODE (no mode defined) 
	default:
?>
						<p><strong>Access Denied</strong></p>
<?php
		break;
		// /END DEFAULT MODE 
}
?>
					</div>
					<!-- /end Page Content -->
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