<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================  
File: cw-mod-cartweaver.php
File Date: 2012-03-21
Description:
Creates the cart and handles all product interaction,
adding, updating and deleting products and skus.
Pass in a product ID and structure of form variables,
with optional cart_action for update / delete
All logic for cart interaction is handled internally.
Line items in the cart are kept separate based on the unique ID,
which is made up of sku id and custom data ID (for personalization / custom info option).
If no custom data is provided, the sku ID is used for the unique ID.
Notes:
// DEPENDENCIES
- uses query functions found in cw-func-cart.php
these query functions pass back record IDs and/or error messages
which are used to show the response to the customer
// ATTRIBUTES
- product_id: required for 'add' via selected options or table view.
integer ID of product to manage.
not required if sku_id is provided
- form_values: required if sku_id and sku_qty not provided.
structure of form values from 'add to cart' form,
or any struct with same variable names and values
- cart_action: optional. (add | update | delete) default is 'add'.
*	add (default): Add a SKU and quantity to the cart by passing in a structure
of form values or a sku_id and sku_qty (or lists of ids and quantities)
*	update: Update a SKU to a specific quantity in the cart as
defined by the sku_unique_id and sku_qty attributes. The sku_id is
required for the update action. Setting the quantity to 0 will
delete an item from the cart.
*	delete: Delete a SKU from the cart as defined by the sku_unique_id
attribute. The sku_unique_id is required for the delete action.
- sku_id (optional): The sku_id attribute passes either a specific SKU ID
or a comma delimited list of SKU IDs. The default is 0 if the attribute is not provided.
- sku_unique_id (required for update/delete): Provides a list of items to manage for the update and delete cartactions
- sku_qty (optional): The sku_qty attribute passes either a single quantity
or a comma delimited list of quantities.
If the length of the quantity list does not match, the first value is used.
(e.g. pass in '2' to apply quantity of 2 to all listed sku ids)
Can also be ommitted for 'delete' or 'add', default for add is 1.
When updating the cart, if 0 is passed, the SKU is deleted from the cart.
- sku_custom_info (optional): A string to apply to all skus being added to the cart,
such as personalized products.
- redirect: A url to send the user to after action is performed. Leave blank for no redirection.
// RESPONSE and ERRORS
- errors and confirmation messages to be displayed to the user are generated in the 'request.cwpage' scope.
see params below for items available
// TIPS & TRICKS
- to show all items in the cart at any point, call this function with a cart ID: CWgetCartItems()
==========================================================
*/
// default sku id and product id are 0 
if(!(isset($module_settings["product_id"]))) $module_settings["product_id"] = 0;  
// default form items collection: renamed to 'productData' for processing 
if(!(isset($module_settings["form_values"]))) $module_settings["form_values"] = array();  
// default action 
if(!(isset($module_settings["cart_action"]))) $module_settings["cart_action"] = 'add';  
// sku_id can be a list, or single ID 
if(!(isset($module_settings["sku_id"]))) $module_settings["sku_id"] = '0';  
// qty can be a list, or single, or omitted: default 1 if not provided 
if(!(isset($module_settings["sku_qty"]))) $module_settings["sku_qty"] = '1';  
// default for personalization (string) 
if(!(isset($module_settings["sku_custom_info"]))) $module_settings["sku_custom_info"] = '';  
// default for redirection 
if(!(isset($module_settings["redirect"]))) $module_settings["redirect"] = '';  
// show alerts after adding new products 
if(!(isset($module_settings["alert_added"]))) $module_settings["alert_added"] = true;  
// show alerts after removing products 
if(!(isset($module_settings["alert_removed"]))) $module_settings["alert_removed"] = true;  
// default error message 
if(!(isset($_ENV["request.cwpage"]["cartAlert"]))) $_ENV["request.cwpage"]["cartAlert"] = '';
// default success message 
if(!(isset($_ENV["request.cwpage"]["cartConfirm"]))) $_ENV["request.cwpage"]["cartConfirm"] = '';
// default success list of sku IDs inserted 
if(!(isset($_ENV["request.cwpage"]["cartSkusInserted"]))) $_ENV["request.cwpage"]["cartSkusInserted"] = '';
// default for items added  
if(!(isset($_ENV["request.cwpage"]["cartQtyInserted"]))) $_ENV["request.cwpage"]["cartQtyInserted"] = 0;
// default for quantity available 
if(!(isset($_ENV["request.cwpage"]["cartStockOk"]))) $_ENV["request.cwpage"]["cartStockOk"] = true;
// default for sku validation 
if(!(isset($_ENV["request.cwpage"]["cartAddOk"]))) $_ENV["request.cwpage"]["cartAddOk"] = true;
// default message for invalid sku options 
if(!(isset($_ENV["request.cwpage"]["optionAlertText"]))) $_ENV["request.cwpage"]["optionAlertText"] = "Selection could not be verified";
// default for list of quantity notices 
if(!(isset($_ENV["request.cwpage"]["stockAlertIDs"]))) $_ENV["request.cwpage"]["stockAlertIDs"] = '';
// default for quantity checking 
if(!(isset($_ENV["application.cw"]["appEnableBackOrders"]))) $_ENV["application.cw"]["appEnableBackOrders"] = true;
// product data from form value structure 
$productData = $module_settings["form_values"];
$myDir = getcwd();
chdir(dirname(__FILE__));
// global functions 
require_once("../inc/cw-inc-functions.php");
// clean up form and url variables 
require_once("../inc/cw-inc-sanitize.php");
chdir($myDir);
// start processing 
// // ADD TO CART // 
if($module_settings["cart_action"] == 'add') {
	// defaults for post-processing 
	if(!(isset($addSku)))	$addSku = array();
	if(!(isset($addSku["id"])))	$addSku["id"] = 0;
	if(!(isset($addSku["qty"]))) $addSku["qty"] = '';
	// validate product is active : will return false if id is 0, on web is no, or stock is less than 0 
	$_ENV["request.cwpage"]["cartStockOk"] = CWcartVerifyProduct($module_settings["product_id"],$_ENV["application.cw"]["appEnableBackOrders"]);
	// if product is Ok, or ID not provided, continue processing 
	if($_ENV["request.cwpage"]["cartStockOk"] || $module_settings["product_id"] == 0) {
		// DETERMINE ADD TO CART METHOD 
		// /////// 
		// MULTIPLE SKUS / TABLE (table display type, uses 'addSkus' field, product ID is required) 
		// /////// 
		if(isset($productData["addSkus"]) && is_numeric($productData["addSkus"]) && $productData["addSkus"] > 0 && $module_settings["product_id"] > 0) {
			$pdAddSkus = $productData["addSkus"];
			// loop addSkus counter field, insert sku id and qty 
			for ($ii=1; $ii <= $pdAddSkus; $ii++) {
				// collect sku values in a struct, clear previous iteration 
				$addSku = array();
				// establish insertion values 
				if(!(isset($productData['skuID'.$ii]))) $productData['skuID'.$ii] = 0;
				$addSku["idStr"] = $productData['skuID'.$ii];
				$addSku["id"] = $addSku["idStr"];
				$addSku["qty"] = $productData['qty' .$ii];
				// QTY: if not provided, use 0 (skips insertion, avoids errors) 
				if(!(is_numeric($addSku["qty"])) && $addSku["qty"] > 0) {		
					$addSku["qty"] = 0;
				}
				// handle custom info - write string to database (if quantity provided for adding product) 
				if(isset($productData['customInfo']) && strlen(trim($productData['customInfo'])) && $addSku["qty"] > 0) {
					// QUERY: inserts custom data to database, returns ID of saved data entry 
					$dataResponse = CWcartAddSkuData($addSku["id"],trim($productData['customInfo']));
					// if the response is numeric, it is the ID for the inserted string 
					if(is_numeric($dataResponse)) {
						// create the unique marker, combine sku id and custom data id 
						$addSku["uniqueID"] = $addSku["id"] . '-' . $dataResponse;               
					} else {
						// if an error was returned 
						// add this error to messages shown to user, use sku ID for unique marker 
					 	if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
						$_ENV["request.cwpage"]["cartAlert"] .= 'Custom value could not be saved'; 
						$addSku["uniqueID"] = $addSku["id"];	
					}
				} else {
					// if no custom info given, use sku ID for unique marker
					$addSku["uniqueID"] = $addSku["id"];
				}
				// QUERY: Insert SKU to Cart (sku id, unique custom string, quantity, check stock ) 
				// insert function returns the SKU ID and the qty (cartresponse.message / cartresponse.qty) on success, or an error message (cartresponse.message) 
				$cartResponse = CWcartAddItem($addSku["id"],$addSku["uniqueID"],$addSku["qty"],$_ENV["application.cw"]["appEnableBackOrders"]);
				// CREATE RESPONSE CONTENT 
				// if response is SKU ID, no error occurred, sku was inserted 
				if($cartResponse['message'] == $addSku["id"]) {	
					// if quantity was returned, track sku ID and total insertions 
					if($cartResponse['qty'] > 0) {
						if($_ENV["request.cwpage"]["cartSkusInserted"]) $_ENV["request.cwpage"]["cartSkusInserted"] .= ',';
						$_ENV["request.cwpage"]["cartSkusInserted"] .= $addSku["uniqueID"];
						$_ENV["request.cwpage"]["cartQtyInserted"] = $_ENV["request.cwpage"]["cartQtyInserted"] + $cartResponse['qty'];
					}
					// if inserted amount was less than requested 
					if($cartResponse['qty'] < $addSku["qty"] && $_ENV["application.cw"]["appEnableBackOrders"] != true) {
						$alertMsg = 'Limited quantity: unable to add item';
						$adjMsg = 'Limited quantity: totals adjusted';
						if($_ENV["request.cwpage"]["stockAlertIDs"]) $_ENV["request.cwpage"]["stockAlertIDs"] .= ',';
						$_ENV["request.cwpage"]["stockAlertIDs"] .= $addSku["uniqueID"];
						if(($cartResponse['qty'] == 0) && !(stristr($_ENV["request.cwpage"]["cartAlert"],$alertMsg))) {
							if($module_settings["alert_added"]) {
								if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
								$_ENV["request.cwpage"]["cartAlert"] .= $alertMsg;
							}
						} else if(!stristr($_ENV["request.cwpage"]["cartAlert"],$adjMsg)) {
							if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
							$_ENV["request.cwpage"]["cartAlert"] .= $adjMsg;
						}
					}
					// if error message returned instead of sku ID 
				} else {
					if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
					$_ENV["request.cwpage"]["cartAlert"] .= trim($cartResponse['message']);
				}
				// set up confirmation from inserted qty 
				if($_ENV["request.cwpage"]["cartQtyInserted"] > 0) {
					$insertedCt = $_ENV["request.cwpage"]["cartQtyInserted"];
					if($insertedCt == 1) $s='';
					else $s='s';	
					$_ENV["request.cwpage"]["cartConfirm"] = $insertedCt . ' item' . $s . ' added to cart';
				}
				// /END RESPONSE CONTENT 
			}
			// / end loop addSkus counter 
		// /END MULTIPLE SKUS / TABLE 
		// /////// 
		// LIST OF SKUS or SINGLE SKU 
		// /////// 
		} else if(isset($module_settings["sku_id"]) && $module_settings["sku_id"] != 0) {
			// treat the sku id as a list, loop over it (even if single sku) 
			$newQtyArr =  explode(',',$module_settings["sku_qty"]);
			$newIDArr =  explode(',',$module_settings["sku_id"]);
			for($ii = 0; $ii < count($newIDArr); $ii++) {
				// sku id to insert 
				$addSku["id"] = trim($newIDArr[$ii]);
				// quantity to insert 
				// if the qty was provided as a matching list 
				if(count($newQtyArr) == count($newIDArr)) {
					$addSku["qty"] = trim($newQtyArr[$ii]);
					// if qty is one number, use that for all items 
				} else if(count($newQtyArr) == 1) {
					$addSku["qty"] = $newQtyArr[0];
					// if none of these, use default 
				} else {
					$addSku["qty"] = 1;
				}
				// handle custom info - write string to database if quantity provided 
				if(strlen(trim($module_settings["sku_custom_info"])) && $addSku["qty"] > 0) {
					// QUERY: inserts custom data to database, returns ID of saved data entry 
					$dataResponse = CWcartAddSkuData($addSku["id"],trim($module_settings["sku_custom_info"]));
					// if the response is numeric, it is the ID for the inserted string 
					if(is_numeric($dataResponse)) {
						// create the unique marker, combine sku id and custom data id 
						$addSku["uniqueID"] = $addSku["id"] . '-'. $dataResponse;
						// if an error was returned 
					} else {
						// add this error to messages shown to user, use sku ID for unique marker 
						if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
						$_ENV["request.cwpage"]["cartAlert"] .= 'Custom value could not be saved ';
						$addSku["uniqueID"] = $addSku["id"];
					}
					// if no custom info given, use sku ID for unique marker
				} else {
					$addSku["uniqueID"] = $addSku["id"];
				}
				// QUERY: Insert SKU to Cart (sku id, unique custom string, quantity, check stock ) 
				// insert function returns the SKU ID and the qty (cartresponse.message / cartresponse.qty) on success, or an error message (cartresponse.message) 
				$cartResponse = CWcartAddItem($addSku["id"],$addSku["uniqueID"],$addSku["qty"],$_ENV["application.cw"]["appEnableBackOrders"]);
				// CREATE RESPONSE CONTENT 
				// if response is SKU ID, no error occurred, sku was inserted 
				if($cartResponse['message'] == $addSku["id"]) {
					// if quantity was returned, track sku ID and total insertions 
					if($cartResponse['qty'] > 0) {
						if($_ENV["request.cwpage"]["cartSkusInserted"]) $_ENV["request.cwpage"]["cartSkusInserted"] .= ',';
						$_ENV["request.cwpage"]["cartSkusInserted"] .= $addSku["uniqueID"];
						$_ENV["request.cwpage"]["cartQtyInserted"] = $_ENV["request.cwpage"]["cartQtyInserted"] + $cartResponses['qty'];
					}
					// if inserted amount was less than requested 
					if($cartResponse['qty']  < $addSku["qty"] && !$_ENV["application.cw"]["appEnableBackOrders"]) {
						$alertMsg = 'Limited quantity: unable to add item';
						$adjMsg = 'Limited quantity: totals adjusted';
						if($_ENV["request.cwpage"]["stockAlertIDs"]) $_ENV["request.cwpage"]["stockAlertIDs"] .= ',';
						$_ENV["request.cwpage"]["stockAlertIDs"] .= $addSku["uniqueID"];
						if($cartResponse['qty'] == 0 && !(stristr($_ENV["request.cwpage"]["cartAlert"],$alertMsg))) {
							if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
							$_ENV["request.cwpage"]["cartAlert"] .= $alertMsg;
						} else if(!(stristr($_ENV["request.cwpage"]["cartAlert"],$adjMsg))) {
							if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
							$_ENV["request.cwpage"]["cartAlert"] .= $adjMsg;
						}
					}
					// if error message returned instead of sku ID 
				} else {
					if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
					$_ENV["request.cwpage"]["cartAlert"] .= $cartResponse['message'];
				}
				// set up confirmation from inserted qty 
				if($_ENV["request.cwpage"]["cartQtyInserted"] > 0) {              
					$insertedCt = $_ENV["request.cwpage"]["cartQtyInserted"];
					if($insertedCt == 1) $s='';
					else $s='s';
					if($module_settings["alert_added"]) {
						$_ENV["request.cwpage"]["cartConfirm"] = $insertedCt .' item'. $s .' added to cart'; 
					}
					// if no qty and no message, we did not provide any qty 
				} else if($cartResponse['message'] == '') {
					$_ENV["request.cwpage"]["cartAlert"] = 'Select a quantity for insertion';
				}
				// /END RESPONSE CONTENT 
				// advance the loop counter 
			}
		// /END LIST OF SKUS or SINGLE SKU 
		// /////// 
		// SINGLE SKU from OPTIONS / SELECT (standard 'select' display type, parses 'optionsel' fields) 
		// /////// 
		} else {
			// loop through form_values, create list of option ids 
			$skuOptions = '';
			foreach ($productData as $fieldName => $fieldValue) {
				// if the form field is one of our select menus, and the value is valid, add the option id to the list and increase the count 
				if(strtolower(substr($fieldName,0,9)) == 'optionsel' && strtolower(substr($fieldName,-4)) != 'temp') {
					// if value was provided, add to list 
					if($fieldValue > 0) {
						if($skuOptions) $skuOptions .= ',';
						$skuOptions .= $fieldValue;
						// if value is blank, flag with 0 for error below 
					} else {
						if($skuOptions) $skuOptions .= ',';
						$skuOptions .= '0';
					}
				}
			}
			// BLANK OPTIONS 
			if(ListFind($skuOptions,'0')) {
				// set message for user 
				$skuRespose = 'error';
				// NO OPTIONS: if no options provided, look up single sku for this product id 
			} else if($skuOptions == '') {
				// QUERY: get single sku for this item 
				$skuResponse = CWcartGetSkuNoOptions($module_settings["product_id"]);
				// OPTION LIST: if options provided, look up sku by option ids 
			} else {
				// QUERY: get sku_id based on options  
				$skuResponse = CWcartGetSkuByOptions($module_settings["product_id"],$skuOptions);	
			}
			// /end if sku options provided, or empty 
			// verify numeric, valid SKU id 
			if (is_numeric($skuResponse)) {
				$addSku["id"] = $skuResponse;
			// set message for invalid sku 
			} else {
				if (!ListFindNoCase($_ENV["request.cwpage"]["cartAlert"],$_ENV["request.cwpage"]["optionAlertText"])) {
					if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
					$_ENV["request.cwpage"]["cartAlert"] .= $_ENV["request.cwpage"]["optionAlertText"];
				}
				$_ENV["request.cwpage"]["cartAddOk"] = false;
			}
			// add to cart if sku ID is ok 
			if ($_ENV["request.cwpage"]["cartAddOk"]) {
				// quantity: from productdata 
				if(isset($productData['qty']) && is_numeric($productData['qty'])) {	
					$addSku["qty"] = $productData['qty'];
				} else {
					$addSku["qty"] = 1;
				}
				// handle custom info - write string to database if quantity provided 
				if(isset($productData['customInfo']) && strlen(trim($productData['customInfo'])) && $addSku["qty"] > 0) {
					// QUERY: inserts custom data to database, returns ID of saved data entry 
					$dataResponse = CWcartAddSkuData($addSku["id"],trim($productData['customInfo']));
					// if the response is numeric, it is the ID for the inserted string 
					if(is_numeric($dataResponse)) {
						// create the unique marker, combine sku id and custom data id 
						$addSku["uniqueID"] = $addSku["id"] . '-' . $dataResponse;
						// if an error was returned 
					} else {
						// add this error to messages shown to user, use sku ID for unique marker 
						if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
						$_ENV["request.cwpage"]["cartAlert"] .= 'Custom value could not be saved'; 
						$addSku["uniqueID"] = $addSku["id"];
					}
					// if no custom info given, use sku ID for unique marker
				} else {
					$addSku["uniqueID"] = $addSku["id"];
				}
				// QUERY: Insert SKU to Cart (sku id, unique custom string, quantity, check stock ) 
				// insert function returns the SKU ID and the qty (cartresponse.message / cartresponse.qty) on success, or an error message (cartresponse.message) 
				$cartResponse = CWcartAddItem($addSku["id"],$addSku["uniqueID"],$addSku["qty"],$_ENV["application.cw"]["appEnableBackOrders"]);
				// CREATE RESPONSE CONTENT 
				// if response is SKU ID, no error occurred, sku was inserted 
				if($cartResponse['message'] == $addSku["id"]) {
					// if quantity was returned, track sku ID and total insertions 
					if($cartResponse['qty'] > 0) {
						if($_ENV["request.cwpage"]["cartSkusInserted"]) $_ENV["request.cwpage"]["cartSkusInserted"] .= ',';
						$_ENV["request.cwpage"]["cartSkusInserted"] .= $addSku["uniqueID"];
						$_ENV["request.cwpage"]["cartQtyInserted"] = $_ENV["request.cwpage"]["cartQtyInserted"] + $cartResponse['qty'];
					}
					// if inserted amount was less than requested 
					if($cartResponse['qty']  < $addSku["qty"] && $cartResponse['message'] != 0 && !$_ENV["application.cw"]["appEnableBackOrders"]) {
						$alertMsg = 'Limited quantity: unable to add item';
						$adjMsg = 'Limited quantity: totals adjusted';
						if($_ENV["request.cwpage"]["stockAlertIDs"]) $_ENV["request.cwpage"]["stockAlertIDs"] .= ',';
						$_ENV["request.cwpage"]["stockAlertIDs"] .= $addSku["uniqueID"];
						if($cartResponse['qty'] == 0 && !stristr($_ENV["request.cwpage"]["cartAlert"],$alertMsg)) {
							if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
							$_ENV["request.cwpage"]["cartAlert"] .= $alertMsg;
						} else if(!stristr($_ENV["request.cwpage"]["cartAlert"],$adjMsg)) {
							if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
							$_ENV["request.cwpage"]["cartAlert"] .= $adjMsg;
						}
					}
					// if error message returned instead of sku ID 
				} else {
					if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
					$_ENV["request.cwpage"]["cartAlert"] .= trim($cartResponse['message']);
				}
				// set up confirmation from inserted qty 
				if($_ENV["request.cwpage"]["cartQtyInserted"] > 0) {
					$insertedCt = $_ENV["request.cwpage"]["cartQtyInserted"];
					if($insertedCt == 1) $s='';
					else $s='s';
					if($module_settings["alert_added"]) {
						$_ENV["request.cwpage"]["cartConfirm"] = $insertedCt . ' item' . $s .' added to cart'; 
					}
					// if no qty and no message, we did not provide any qty 
				} else if($cartResponse['message'] == '') {
					$_ENV["request.cwpage"]["cartAlert"] = 'Select a quantity for insertion';
				}
				// /END RESPONSE CONTENT 
			// /end if request.cwpage.cartAddOk = true 
			}
		}
		// /END SINGLE SKU from OPTIONS / SELECT 
		
		// if product stockOk returns false 
	} else {		
		if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
		$_ENV["request.cwpage"]["cartAlert"] .= 'Product Unavailable';
	}
	// /end stock ok 
}
// / END ADD TO CART 

// // DELETE SKU FROM CART // 
if($module_settings["cart_action"] == 'delete') {	
	// treat the sku unique ids as a list, loop over it (even if single sku) 
	if(!(isset($_ENV["request.cwpage"]["deleteCt"]))) $_ENV["request.cwpage"]["deleteCt"] = 0;
	$newIDArr2 = explode(',',$module_settings["sku_unique_id"]);
	for ($ii = 0; $ii < count($newIDArr2); $ii++) {
		$delSku = array();
		// sku unique id to delete 
		$delSku['ID'] = trim($newIDArr2[$ii]);
		// QUERY: remove item from cart, return string or message 
		$deleteItem = CWcartDeleteItem($delSku['ID']);
		// if a message other than sku id is returned, show error message 
		if($deleteItem != trim($newIDArr2[$ii])) {	
			if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
			$_ENV["request.cwpage"]["cartAlert"] .= $deleteItem;
			// if id was returned, add to delete count 
		} else {
			$_ENV["request.cwpage"]["deleteCt"] = $_ENV["request.cwpage"]["deleteCt"] + 1;
		}
	}
	if($_ENV["request.cwpage"]["deleteCt"] > 0) {
		if($_ENV["request.cwpage"]["deleteCt"] == 1) $s = '';
		else $s = 's';
		if($module_settings["alert_removed"]) {
			$_ENV["request.cwpage"]["cartConfirm"] = $_ENV["request.cwpage"]["deleteCt"]. ' item'.$s.' removed from cart';
		}
	}
}
// /END DELETE SKU 

// // UPDATE QUANTITY IN CART // 
if($module_settings["cart_action"] == 'update') {
	// treat the sku unique id as a list, loop over it (even if single sku) 
	if(!(isset($_ENV["request.cwpage"]["deleteCt"]))) $_ENV["request.cwpage"]["deleteCt"] = 0;
	$newVarQtyArr = explode(',',$module_settings["sku_qty"]);
	$newVarIDArr = explode(',',$module_settings["sku_unique_id"]);
	for($ii = 0; $ii < count($newVarIDArr); $ii++) {
		$updateSku = array();
		$updateSku['uniqueID'] = trim($newVarIDArr[$ii]);
		// if qty is same, get from list 
		if(count($newVarQtyArr) == count($newVarIDArr)) {
			$updateSku['qty'] = $newVarQtyArr[$ii];
			// otherwise use first number in list (i.e. if only number) 
		} else {
			$updateSku['qty'] = $newVarQtyArr[0];
		}
		// if qty is 0, delete 
		if($updateSku['qty'] == 0) {
			// QUERY: remove item from cart, return string or message 
			$deleteItem = CWcartDeleteItem($updateSku['uniqueID']);
			// if a message other than sku id is returned, show error message 
			if($deleteItem != $updateSku['uniqueID']) {
				if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
				$_ENV["request.cwpage"]["cartAlert"] .= $deleteItem;
				// if id was returned, add to delete count 
			} else {
				$_ENV["request.cwpage"]["deleteCt"] = $_ENV["request.cwpage"]["deleteCt"] + 1;
			}
			// if greater than 0, update the qty 
		} else {
			// QUERY: update quantity in cart, return string or message 
			$cartResponse =	CWcartUpdateItem($updateSku['uniqueID'],$updateSku['qty'],$_ENV["application.cw"]["appEnableBackOrders"]);
			// if a message other than sku id is returned, show error message 
			if($cartResponse['message'] != $updateSku['uniqueID'] && !$cartResponse['qty'] == '') {
				if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
				$_ENV["request.cwpage"]["cartAlert"] .= 'Unable to update Item';
			}
			// if quantity does not match, show alert 
			if($cartResponse['qty']  < $updateSku['qty'] && $cartResponse['qty'] !== '' && !$_ENV["application.cw"]["appEnableBackOrders"]) {
				$adjMsg = 'Limited quantity: totals adjusted';
				if(stripos($_ENV["request.cwpage"]["cartAlert"],$adjMsg) === false) {
					if($_ENV["request.cwpage"]["cartAlert"]) $_ENV["request.cwpage"]["cartAlert"] .= ',';
					$_ENV["request.cwpage"]["cartAlert"] .= $adjMsg;
				}
				if($_ENV["request.cwpage"]["stockAlertIDs"]) $_ENV["request.cwpage"]["stockAlertIDs"] .= ',';
				$_ENV["request.cwpage"]["stockAlertIDs"] .= $updateSku['uniqueID'];
				$_SESSION["cw"]["stockAlertIDs"] = $_ENV["request.cwpage"]["stockAlertIDs"];
			}
		}
	}
	// put alerts into session scope 
	if(strlen(trim($_ENV["request.cwpage"]["cartAlert"]))) {
		$_SESSION["cw"]["cartAlert"] = $_ENV["request.cwpage"]["cartAlert"];
	}
	if($_ENV["request.cwpage"]["deleteCt"] > 0) {
		if($_ENV["request.cwpage"]["deleteCt"] == 1) $s = '';
		else $s = 's';
		if($module_settings["alert_removed"]) {
			$_ENV["request.cwpage"]["cartConfirm"] = $_ENV["request.cwpage"]["deleteCt"]. ' item'.$s.' removed from cart';
		}
	}
}
// /END UPDATE QUANTITY 

// redirect 
if(strlen(trim($module_settings["redirect"]))) {	
	// pass alerts/confirmations into session 
	if(isset($_ENV["request.cwpage"]["cartConfirm"]) && strlen(trim($_ENV["request.cwpage"]["cartConfirm"]))) {
		$_SESSION["cw"]["cartConfirm"] = trim($_ENV["request.cwpage"]["cartConfirm"]);
	}
	if(isset($_ENV["request.cwpage"]["cartAlert"]) && strlen(trim($_ENV["request.cwpage"]["cartAlert"]))) {
		$_SESSION["cw"]["cartAlert"] = trim($_ENV["request.cwpage"]["cartAlert"]);
	}
	// clear values if going to cart 
	$newVarForList2 = explode('/',$_ENV["request.cwpage"]['urlShowCart']);
	if(stristr($module_settings["redirect"],$newVarForList2[count($newVarForList2) - 1])) {
		$persistVars = '';
		// persist selection values if going to details page 
	} else {
		$persistVars = 'product,category,secondary';
	}
	$redirectURL = CWserializeUrl($persistVars,$module_settings["redirect"]);
	if (strpos($redirectURL, "=") !== false) $redirectURL .= "&";
	if(strlen(trim($_ENV["request.cwpage"]["cartSkusInserted"]))) {
		$redirectURL .= 'addedID=' . $_ENV["request.cwpage"]["cartSkusInserted"];
	}
	if(strlen(trim($_ENV["request.cwpage"]["stockAlertIDs"]))) {
		$redirectURL .= 'alertID='.$_ENV["request.cwpage"]["stockAlertIDs"];
	}
	// only redirect if the sku was valid 
	if ($_ENV["request.cwpage"]["cartAddOk"]) {
		header("Location: ".$redirectURL);
		exit;
	}
}
?>		
