<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-product.php
File Date: 2012-05-12
Description: Handles all actions for the Cartweaver product form (product-details.php)
Manages Products, SKUs, and Upsell Items by running related queries
Catches errors and returns each in the request scope with a specific name
(see error parsing, confirmation and other event handling in product-details.php)
Dependencies: Requires admin query functions to be included in calling page
==========================================================
*/
// // ---------- Update Existing Product ---------- // 

function CWfuncProductUpdate($product_id,$product_name,$product_on_web=0,$product_ship_charge=0,$product_tax_group_id=0,
	$product_sort=0,$product_out_of_stock_message=NULL,$product_custom_info_label=NULL,$product_description=NULL,
	$product_preview_description=NULL,$product_special_description=NULL,$product_keywords=NULL,$has_orders=0,
	$product_options=NULL,$product_categories=NULL,$product_ScndCategories=NULL) {
	// /////// 
	// UPDATE PRODUCT 
	// /////// 
	
	try {
		$updateProd = CWqueryUpdateProduct($product_id,$product_name,$product_on_web,$product_ship_charge,
				$product_tax_group_id,$product_sort,$product_out_of_stock_message,$product_custom_info_label,
				$product_description, $product_preview_description,$product_special_description,$product_keywords);
		// CATEGORY ACTIONS 
		// Delete current Category Relationships 
		$deleteCats = CWqueryDeleteProductCat($product_id);
		// INSERT the new ones  
		foreach ($product_categories as $key => $catID) {
			$insertCat = CWqueryInsertProductCat($product_id,$catID);
		}
		// Delete current Subcategory Relationships 
		$deleteCats = CWqueryDeleteProductScndCat($product_id);
		// INSERT the new ones  
		foreach ($product_ScndCategories as $key => $catID) {
			$insertCat = CWqueryInsertProductScndCat($product_id,$catID);
		}
		// /END CATEGORY OPTIONS 
		// OPTION ACTIONS 
		// only change options if there are no orders on this product 
		if(!$has_orders) {
			// If we've removed a product option, remove the related sku options 
			// get current list of relative option record IDs 
			$rsGetOptnRelIDs = CWquerySelectRelOptions($product_id,implode(",",$product_options));
			// get skus for this product 
			$rsGetSKUIDs = CWquerySelectSkus($product_id);
			// if we have both skus and relative options, delete them 
			if($rsGetOptnRelIDs['totalRows'] && $rsGetSKUIDs['totalRows']) {
				$listOptionRelIDs = implode(',',$rsGetSKUIDs['sku_id']);
				$listSkuIDs = implode(',',$rsGetOptnRelIDs['sku_option_id']);
				$deleteskuOpts = CWqueryDeleteRelSKUOptions($listOptionRelIDs,$listSkuIDs);
			}
			// Delete Product Option Information 
			$deleteProdOpts = CWqueryDeleteRelProductOptions($product_id);
			// Add Selected Product Options 
			foreach ($product_options as $key => $optID) {
				$createOpt = CWqueryInsertProductOptions($product_id,$optID);
			}
		}
		// /END has orders check 
		// /END OPTION ACTIONS 
		// IMAGE ACTIONS 
		// get number of unique upload groups 
		$getImageCount = CWquerySelectImageUploadGroups(); 
		// loop the image count query 
		for($i=0; $i<$getImageCount['totalRows']; $i++) {
			// set up field name and number to use 
			$imgNo = $getImageCount['imagetype_upload_group'][$i];
			$imageFieldName = "Image".$imgNo;
			$formField = $_POST[$imageFieldName];
			$formFieldVal = $formField;
			if(!isset($_POST['imageID'.$imgNo])) { $_POST['imageID'.$imgNo] = ""; }
			$imageIDField = $_POST['imageID'.$imgNo];
			$imageIDVal = $imageIDField;
			$FORMImageName = $formFieldVal;
			$FORMImageID = $imageIDVal;
			// get the image types that go with this upload group value 
			$getImageTypes = CWquerySelectImageTypes($imgNo);
			// if the image name is blank but an ID is associated, DELETE 
			if($FORMImageName == "" && $FORMImageID != "") {
				// Loop the query and delete all related images 
				for($ii=0; $ii<$getImageTypes['totalRows']; $ii++) {
					$deleteImg = CWqueryDeleteProductImage($product_id, $getImageTypes['imagetype_id'][$ii]);
				}
			// If the image is not blank , but an ID was already given, UPDATE 
			}
			elseif($FORMImageName != "" && $FORMImageID != "") {
				// Loop the query and update all related images 
				for($ii=0; $ii<$getImageTypes['totalRows']; $ii++) {
					$updateImg = CWqueryUpdateProductImage($product_id,$getImageTypes['imagetype_id'][$ii],$FORMImageName);
				}
				// If  the image ID is blank, not existing before now, INSERT 
			}
			elseif($FORMImageName != "" && $FORMImageID == "") {
				// Loop the query and update all related images 
				for($ii=0; $ii<$getImageTypes['totalRows']; $ii++) {
					$insertImg = CWqueryInsertProductImage($product_id,$getImageTypes['imagetype_id'][$ii],$FORMImageName,$getImageTypes['imagetype_sortorder'][$ii]);
				}
			}
		}
		// end of for loop
	// end IMAGE ACTIONS 
	} catch(Exception $e) {
		$_POST['productUpdateError'] = $e->getMessage();
	}
	// /////// 
	// /END UPDATE PRODUCT 
	// /////// 
}

	// // ---------- Add New Product ---------- // 
	 function CWfuncProductAdd($product_merchant_product_id,$product_name,$product_on_web=0,$product_ship_charge=0,$product_tax_group_id=0,$product_sort=0,$product_out_of_stock_message=NULL,$product_custom_info_label=NULL,$product_description=NULL,$product_preview_description=NULL,$product_special_description=NULL,$product_keywords=NULL,$has_orders=0,$product_options=array(),$product_categories=array(),$product_ScndCategories=array()) {

	// /////// 
	// ADD NEW PRODUCT 
	// /////// 
	try {
		if (!strlen(trim($product_merchant_product_id))) {
			$_ENV["request.cwpage"]["productinsertError"] = "Please enter a Product ID";
		} else if (!strlen(trim($product_name))) {
			$_ENV["request.cwpage"]["productinsertError"] = "Please enter a Product Name";
		} else {
			// check for existing product with same merchant ID (part number) 
			$rsCheckMerchantID = CWquerySelectMerchantID($product_merchant_product_id);
			// if the product Merchant ID exists, show an alert to the user, focus the merchant ID field 
			if($rsCheckMerchantID['totalRows']) {
				$_ENV["request.cwpage"]["productinsertError"] = "Product already exists, please enter a new Product ID";
				$_POST['productExists'] = 1;
				// if the merchant ID does not already exist, run processing 
			} else {
				// INSERT PRODUCT 
				// this function returns the last inserted product ID
				//for further processing after creating the new product
				$newproduct_id = CWqueryInsertProduct($product_merchant_product_id,$product_name,$product_on_web,$product_ship_charge,$product_tax_group_id,$product_sort,$product_out_of_stock_message,$product_custom_info_label,
	$product_description,$product_preview_description,$product_special_description,$product_keywords);
				// CATEGORY ACTIONS 
				// Create new Category Relationships 
				foreach ($product_categories as $key => $ii) {
					if (strlen(trim($ii))) {
						$insertCat = CWqueryInsertProductCat($newproduct_id,$ii);
					}
				}
				// Create new Secondary Category Relationships 
				foreach ($product_ScndCategories as $key => $ii) {
					if (strlen(trim($ii))) {
						$insertCat = CWqueryInsertProductScndCat($newproduct_id,$ii);
					}
				}
				// /END CATEGORY ACTIONS 
				// OPTION ACTIONS 
				// Add Selected Product Options 
				foreach ($product_options as $key => $ii) {
					if (strlen(trim($ii))) {
						$createOpt = CWqueryInsertProductOptions($newproduct_id,$ii);
					}
				}
				// /END OPTION ACTIONS 
				// IMAGE ACTIONS 
				// get number of unique upload groups 
				$getImageCount = CWquerySelectImageUploadGroups();
				// loop the image count query 
				for($i=0; $i<$getImageCount['totalRows']; $i++) {
					// set up field name and number to use 
					$imgNo = $getImageCount['imagetype_upload_group'][$i];
					$imageFieldName = "Image".$imgNo;
					$formField = $_POST[$imageFieldName];
					$formFieldVal = $formField;
					if($_POST['imageID'.$imgNo] == '') { $_POST['imageID'.$imgNo] = ""; }
					$imageIDField = $_POST['imageID'.$imgNo];
					$imageIDVal = $imageIDField;
					$FORMImageName = $formFieldVal;
					$FORMImageID = $imageIDVal;
					// get the image types that go with this upload group value 
					$getImageTypes = CWquerySelectImageTypes($imgNo);
					// Loop all fields, if value is not blank, INSERT  
					if($FORMImageName != "") {
						for($ii=0; $ii<$getImageTypes['totalRows']; $ii++) {
							$insertImg = CWqueryInsertProductImage($newproduct_id,$getImageTypes['imagetype_id'][$ii],$FORMImageName,$getImageTypes['imagetype_sortorder'][$ii]);
						}
					}
					// /END loop/insert 
				}
				// /END loop image count query 
				// /END IMAGE ACTIONS 
				// set up the id so we can use it further down the page 
				$_ENV["request.cwpage"]["newProductID"] = $newproduct_id;
			}
		}
	}
		// END IF rsCheckproduct_id.RecordCount NEQ 0 
	catch(Exception $e) {
		$_ENV["request.cwpage"]["productinsertError"] = $e->getMessage();
	}
	// /////// 
	// /END ADD NEW PRODUCT 
	// /////// 
}

	// // ---------- Delete Product ---------- // 
function CWfuncProductDelete($product_id) {
	try
	{
		// Get any product SKUs 
		$getSkus = CWquerySelectSkus($product_id);
		// If we have skus, delete them along with related options 
		if($getSkus['totalRows']) {
			$SKUList = implode(',',$getSkus['sku_id']);
			// Delete options 
			$deleteOpts = CWqueryDeleteRelSkuOptions(0,$SKUList);
			// Delete SKUs 
			$deleteskus = CWqueryDeleteSKUs($SKUList);
		} else {
		    $SKUList = '0';
		}

		// Delete Product Option Information 
		$deleteProdOpts = CWqueryDeleteRelProductOptions($product_id);

		// Delete all Category Relationships 
		$deleteCats = CWqueryDeleteProductCat($product_id);

		// Delete all Secondary Category Relationships 
		$deleteCats = CWqueryDeleteProductScndCat($product_id);

		// Delete Product Image Information 
		$deleteImages = CWqueryDeleteProductImage($product_id,0); 

		// Delete Product Up-sell Records and relative upsells 
		$deleteUpsell = CWqueryDeleteUpsell($product_id,0,1);

		// Delete Product Discount records
		$deleteDiscounts = CWqueryDeleteProductDiscount($product_id);

		// Delete Related Sku Discount records 
		$deleteSkuDiscounts = CWqueryDeleteSKUDiscount($SKUList);

		// Delete Product 
		$deleteproduct = CWqueryDeleteProduct($product_id); 
	}
	catch(Exception $e) {
		$_POST['productDeleteError'] = $e->getMessage();
	}
		
}



// // ---------- Update SKU ---------- // 
function CWfuncSkuUpdate($sku_id,$sku_product_id,$sku_price=0,$sku_ship_base=0,$sku_alt_price=0,$sku_weight=0,$sku_stock=0,$sku_on_web=1,$sku_sort=0,$sku_str_options=NULL) {
    
	try
	{

		// update SKU details 	
		$updateProd = CWqueryUpdateSKU(
					$sku_id,
					$sku_product_id,
					$sku_price,
					$sku_ship_base,
					$sku_alt_price,
					$sku_weight,
					$sku_stock,
					$sku_on_web,
					$sku_sort);
		// DELETE current SKU options 
		if (sizeof($sku_str_options)) {
			$deleteSkuOpts = CWqueryDeleteRelSKUOptions(0,$sku_id);
			// INSERT the new SKU option relationships  
			$nOpts = count($sku_str_options);
			for($ii=0; $ii<$nOpts; $ii++) {
				// insert sku option 
				$createSKUOption = CWqueryInsertRelSKUOption($sku_id,$sku_str_options[$ii]);
			}
		}

	}
	catch(Exception $e) {
		$_ENV["request.cwpage"]["skuUpdateError"] = $e->getMessage();
	}
}
// // ---------- Add New SKU ---------- // 
// $sku_str_options definded to expect an array
function CWfuncSkuAdd($sku_merchant_sku_id,$sku_product_id,$sku_price=0,$sku_ship_base=0,$sku_alt_price=0,$sku_weight=0,$sku_stock=0,$sku_on_web=1,$sku_sort=0,$sku_str_options=array()) {
	$newSKUID = 0;
	try
	{	    
		// Check to make sure the sku_id is unique 
		$rsCheckUniqueSKU = CWquerySelectSKUID($sku_merchant_sku_id);

		if($rsCheckUniqueSKU['totalRows'] != 0) {
			// if the sku already exists 
			$_POST['skuInsertError'] = "SKU ID <em>".$sku_merchant_sku_id."</em> already exists. Please choose a different SKU ID";
		} else {
			// insert the new sku, get the ID 
			$newSKUID = CWqueryInsertSKU(
							$sku_merchant_sku_id,
							$sku_product_id,
							$sku_price,
							$sku_ship_base,
							$sku_alt_price,
							$sku_weight,
							$sku_stock,
							$sku_on_web,
							$sku_sort);
			// Add SKU Options 
			$nOpts = count($sku_str_options);
			
			for($ii=0; $ii<$nOpts; $ii++) {
				// insert sku option 
				$createSKUOption = CWqueryInsertRelSKUOption($newSKUID,$sku_str_options[$ii]);
			}
		}
		// END IF - rsCheckUniqueSKU.RecordCount eq 0 
	}
	catch(Exception $e) {
		$_ENV['request.cwpage']['skuInsertError'] = $e->getMessage();
	}
	return $newSKUID;
}

// // ---------- Delete SKU---------- // 
function CWfuncSKUDelete($sku_id) {
	try
	{
		// First, see if it is in use : function returns number of orders (none = 0) 
		$rsCheckSKUUse = CWqueryCountSKUOrders($sku_id);
		// if not in use on any orders 
		if($rsCheckSKUUse == 0) {
			// delete options 
			$deleteOptions = CWqueryDeleteRelSkuOptions(0,$sku_id);
			// delete discounts 
			$deleteOptions = CWqueryDeleteSkuDiscount($sku_id);
			// delete sku 
			$deletesku = CWqueryDeleteSKUs($sku_id);
			// if it IS in use, can't delete - show message 
		} else {
			$_ENV["request.cwpage"]["userAlertText"] = "SKUs may not be deleted once orders have been placed. To remove from inventory, set <em>on Web</em> to <em>No</em>";
			CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
		}
	}
	catch(Exception $e) {
		$_POST['skuDeleteError'] = $e->getMessage();
	}
}

// // ---------- Add New Upsell ---------- // 
function CWfuncUpsellAdd($product_id,$listupsell_id) {
	// note: set to returntype any for instances where an error message is returned 
	try
	{
		// prepare for errors 
		$_ENV["request.cwpage"]["upsellInsertError"] = '';
		$insertCt = 0;
		// loop the list of inserted ids 
		for($upsellID=0; $upsellID < count($listupsell_id); $upsellID++) {	
			$checkDupUpsell = CWquerySelectUpsell($product_id,$listupsell_id[$upsellID]);

			// if not a duplicate, insert the upsell, increase count of inserted items 
			if($checkDupUpsell['totalRows'] == 0) {
				$insertUpsell = CWqueryInsertUpsell($product_id,$listupsell_id[$upsellID]);
				$insertCt = $insertCt + 1;
			} else {
				// if it is a duplicate, show a specific error, don't count as inserted 
				$getDupName = CWquerySelectProductDetails($product_id);
				$_ENV["request.cwpage"]["upsellInsertError"] .= '<strong>'.$checkDupUpsell['product_name'].'</strong> is already associated <br>with this Product. Record not added.<br>';
			}
		}
		// return the number of upsells created 
		if((is_numeric($insertCt)) && $insertCt > 0 ) {
			return $insertCt;
		} else {
			return 0;
		}
	}
	catch(Exception $e) {
		$_ENV["request.cwpage"]["upsellInsertError"] = $e->getMessage();
	}
}

function CWfuncupselldelete($product_id=0,$upsell_id=0) {
	try
	{
		$deleteUpsell = CWqueryDeleteUpsell($product_id,$upsell_id);
	}
	catch(Exception $e) {
		$_ENV["request.cwpage"]["upselldeleteError"] = $e->getMessage();
	}
}
?>