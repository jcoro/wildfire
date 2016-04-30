<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-adminqueries.php
File Date: 2012-07-03
Description: include database queries as functions with variable arguments
See each function for argument types, values and ordering
==========================================================
*/

function CWquerySearchProducts($search_string='%',$search_by='',$search_cat=0,$search_scndcat=0,$search_sortby='product_name',$search_sortdir='asc',$search_archived=false,$show_ct=0,$doSort=false) {
	$rsSearchProducts = "";
	$searchFor = strtolower($search_string);
	$rsSearchProducts = "SELECT
						cw_products.product_id,
						cw_products.product_name,
						cw_products.product_on_web,
						cw_products.product_merchant_product_id,
						cw_products.product_date_modified
						FROM cw_products";
// if using categories 
	if($search_cat > 0) {
		$rsSearchProducts.=" , cw_product_categories_primary cc";
	}
// if using secondary categories 
	if($search_scndcat > 0) {
		$rsSearchProducts.=" , cw_product_categories_secondary sc";
	}
	$rsSearchProducts.=" WHERE 1 = 1 AND";
// archived vs active 
	if(!$search_archived) {   // formerly if(!$search_archived) which was 
		    $rsSearchProducts.=" NOT";
	}	
	$rsSearchProducts.=" cw_products.product_archive = 1";
	$searchFor = CWqueryParam($searchFor);
// add search_by options, make case insensitive 
	if($search_by == "prodID") {
		 $rsSearchProducts.=" AND ".$_ENV["application.cw"]["sqlLower"]."(cw_products.product_merchant_product_id) LIKE '%".$searchFor."%'";	
	}
	if($search_by == "description") {
		 $rsSearchProducts.=" AND (
				 ".$_ENV["application.cw"]["sqlLower"]."(cw_products.product_description) LIKE '%".$searchFor."%'
				OR ".$_ENV["application.cw"]["sqlLower"]."(cw_products.product_preview_description) LIKE '%".$searchFor."%')";
	}
	if($search_by == "prodName") {
		$rsSearchProducts.=" AND ".$_ENV["application.cw"]["sqlLower"]."(cw_products.product_name) LIKE '%".$searchFor."%'";
	} else {
		// any field 	
		$rsSearchProducts.=" AND (";
		$rsSearchProducts.=	$_ENV["application.cw"]["sqlLower"]."(cw_products.product_id) LIKE '%".$searchFor."%'
								OR
							".$_ENV["application.cw"]["sqlLower"]."(cw_products.product_description) LIKE '%".$searchFor."%'
								OR
							".$_ENV["application.cw"]["sqlLower"]."(cw_products.product_preview_description) LIKE '%".$searchFor."%'
								OR
							".$_ENV["application.cw"]["sqlLower"]."(cw_products.product_name) LIKE '%".$searchFor."%'
								OR
							".$_ENV["application.cw"]["sqlLower"]."(cw_products.product_merchant_product_id) LIKE '%".$searchFor."%'";
		if($_ENV["application.cw"]["adminProductKeywordsEnabled"]) {
			$rsSearchProducts.=" OR ".$_ENV["application.cw"]["sqlLower"]."(cw_products.product_keywords) LIKE '%".$searchFor."%'";
		}
		$rsSearchProducts.=")";				
	}
	if($search_cat >0) {
		$rsSearchProducts.=" AND cc.product2category_category_id = ".CWqueryParam($search_cat)."
				AND cc.product2category_product_id = cw_products.product_id";
	}
	if($search_scndcat >0) {
		$rsSearchProducts.=" AND sc.product2secondary_secondary_id = ".CWqueryParam($search_scndcat)."
							AND sc.product2secondary_product_id = cw_products.product_id";
	}
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsSearchProducts, $_GET["sortby"]) !== false) {
			$rsSearchProducts .= " ".CWqueryGetSort($rsSearchProducts, $_GET["sortby"], $_GET["sortdir"], CWqueryParam($search_sortby)." ".CWqueryParam($search_sortdir));
		} else {
			$rsSearchProducts .= " ORDER BY ".CWqueryParam($search_sortby)." ".CWqueryParam($search_sortdir);
		}
	} else {
		$rsSearchProducts .= " ORDER BY ".CWqueryParam($search_sortby)." ".CWqueryParam($search_sortdir);
	}
	if ($show_ct > 0) {
		$rsSearchProducts .= " LIMIT ".$show_ct;
	}
	return CWqueryGetRS($rsSearchProducts);
}


function CWquerySelectProductDetails($product_id) {
	$rsProductDetails = CWqueryGetRS("SELECT * FROM cw_products WHERE product_id = '".CWqueryParam($product_id)."'");
	
	return $rsProductDetails;
}
// // ---------- Update a Product ---------- // 
// id and name are required . 
// optional values 
function CWqueryUpdateProduct($product_id,$product_name,$product_on_web=0,$product_ship_charge=0,
$product_tax_group_id=0,$product_sort=0,$product_out_of_stock_message=NULL,$product_custom_info_label=NULL,
$product_description=NULL,$product_preview_description=NULL,$product_special_description=NULL,$product_keywords=NULL) {
	$query ="UPDATE cw_products
			SET
			product_name = '".CWqueryParam($product_name)."',
			product_on_web = '".CWqueryParam($product_on_web)."',
			product_ship_charge = '".CWqueryParam($product_ship_charge)."',
			product_tax_group_id = '".CWqueryParam($product_tax_group_id)."',
			product_sort = '".CWqueryParam($product_sort)."',
			product_out_of_stock_message = '".CWqueryParam($product_out_of_stock_message)."',
			product_custom_info_label = '".CWqueryParam($product_custom_info_label)."',
			product_description = '".CWqueryParam($product_description)."',
			product_preview_description = '".CWqueryParam($product_preview_description)."',
			product_special_description = '".CWqueryParam($product_special_description)."',
			product_keywords = '".CWqueryParam($product_keywords)."',
			product_date_modified = '".CWqueryParam(date("Y-m-d H:i:s", CWtime()))."'
			WHERE
			product_id ='".CWqueryParam($product_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);	
}
// // ---------- Insert a Product ---------- // 
// merchant id and name are required . 
// optional values 
function CWqueryInsertProduct($product_merchant_product_id,$product_name,$product_on_web=0,$product_ship_charge=0,$product_tax_group_id=0,$product_sort=0,$product_out_of_stock_message=NULL,$product_custom_info_label=NULL,$product_description=NULL,$product_preview_description=NULL,$product_special_description=NULL,$product_keywords=NULL) {
	$query="INSERT INTO
			cw_products
			(product_merchant_product_id,
			product_name,
			product_on_web,
			product_ship_charge,
			product_tax_group_id,
			product_sort,
			product_out_of_stock_message,
			product_custom_info_label,
			product_description,
			product_preview_description,
			product_special_description,
			product_keywords,
			product_archive,
			product_date_modified
			)
	VALUES (
			'".CWqueryParam($product_merchant_product_id)."',
			'".CWqueryParam($product_name)."',
			'".CWqueryParam($product_on_web)."',
			'".CWqueryParam($product_ship_charge)."',
			'".CWqueryParam($product_tax_group_id)."',
			'".CWqueryParam($product_sort)."',
			'".CWqueryParam($product_out_of_stock_message)."',
			'".CWqueryParam($product_custom_info_label)."',
			'".CWqueryParam($product_description)."',
			'".CWqueryParam($product_preview_description)."',
			'".CWqueryParam($product_special_description)."',
			'".CWqueryParam($product_keywords)."',
			0,
			'".CWqueryParam(date("Y-m-d H:i:s", CWtime()))."'
			)";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
// Get the ID of the new product for further inserts 
	$newproduct_id = mysql_insert_id();
// return the numeric new id 
	return $newproduct_id;
}


function CWqueryDeleteProduct($product_id) {
	$query = "DELETE FROM cw_products WHERE product_id = '".CWqueryParam($product_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}



function CWquerySelectMerchantID($merchant_id) {
	$rsSelectMerchantID="SELECT product_merchant_product_id
						 FROM cw_products 
						 WHERE product_merchant_product_id = '".CWqueryParam($merchant_id)."'";
	return CWqueryGetRS($rsSelectMerchantID);
}


// // ---------- Get Product Related Categories ---------- // 
function CWquerySelectRelCategories($product_id) {
	$rsRelCategories="SELECT rr.product2category_category_id, cc.category_name
						FROM
						cw_product_categories_primary rr,
						cw_categories_primary cc
						WHERE rr.product2category_product_id = '".CWqueryParam($product_id)."'
						AND cc.category_id = rr.product2category_category_id";
	return CWqueryGetRS($rsRelCategories);
}

// // ---------- Get Product Related Secondary Categories ---------- // 
function CWquerySelectRelScndCategories($product_id) {
	$rsRelScndCategories="SELECT rr.product2secondary_secondary_id, sc.secondary_name
						FROM
						cw_product_categories_secondary rr,
						cw_categories_secondary sc
						WHERE rr.product2secondary_product_id = '".$product_id."'
						AND sc.secondary_id = rr.product2secondary_secondary_id";
	return CWqueryGetRS($rsRelScndCategories);
}



// // ---------- Delete Product Category Record(s) ---------- // 

function CWqueryDeleteProductCat($product_id,$category_id=0) {
	$query = "DELETE FROM cw_product_categories_primary
	WHERE product2category_product_id = '".CWqueryParam($product_id)."'";	
	if($category_id > 0) {
	 	$query.=" AND product2category_category_id = '".CWqueryParam($category_id)."'";
	}
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}



// // ---------- Insert Related Category Record ---------- // 

function CWqueryInsertProductCat($product_id,$category_id) {
	$query="INSERT INTO cw_product_categories_primary
			(product2category_product_id, product2category_category_id )
			VALUES ('".CWqueryParam($product_id)."', '".CWqueryParam($category_id)."')";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

	// // ---------- Delete Product Secondary Category Record(s) ---------- // 
	
function CWqueryDeleteProductScndCat($product_id,$scndcat_id=0) {
	$query="DELETE FROM cw_product_categories_secondary
			WHERE product2secondary_product_id = '".CWqueryParam($product_id)."'";
	if($scndcat_id > 0) {
		$query.="AND product2secondary_secondary_id = '".CWqueryParam($scndcat_id)."'";
	}
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	
}
	// // ---------- Insert Related Secondary Category Record ---------- // 

function CWqueryInsertProductScndCat($product_id,$scndcat_id) {
	$query = "INSERT INTO cw_product_categories_secondary
				(product2secondary_product_id, product2secondary_secondary_id )
				VALUES ('".CWqueryParam($product_id)."', '".CWqueryParam($scndcat_id)."')";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}




// // ---------- Get Related Products for a Specific Product ---------- // 
function CWquerySelectUpsellProducts($product_id,$doSort=false) {
	$rsSelectUpsell="SELECT p.product_id,
						p.product_merchant_product_id,
						p.product_name,
						u.upsell_id
						FROM cw_products p, cw_product_upsell u
						WHERE p.product_id = u.upsell_2product_id
						AND u.upsell_product_id = '".CWqueryParam($product_id)."'";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsSelectUpsell, $_GET["sortby"]) !== false) {
			$rsSelectUpsell .= " ".CWqueryGetSort($rsSelectUpsell, $_GET["sortby"], $_GET["sortdir"], "p.product_name ASC, p.product_merchant_product_id ASC");
		} else {
			$rsSelectUpsell .= " ORDER BY p.product_name, p.product_merchant_product_id";
		}
	} else {
		$rsSelectUpsell .= " ORDER BY p.product_name, p.product_merchant_product_id";
	}
	return CWqueryGetRS($rsSelectUpsell);
}

// // ---------- Get Reciprocal Related Products for a Specific Product ---------- 
function CWquerySelectReciprocalUpsellProducts($product_id,$doSort=false) {
	$rsSelectRecipUpsell="SELECT p.product_id,
							p.product_merchant_product_id,
							p.product_name,
							u.upsell_id
							FROM cw_products p, cw_product_upsell u
							WHERE p.product_id = u.upsell_product_id
							AND u.upsell_2product_id = '".CWqueryParam($product_id)."'";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsSelectRecipUpsell, $_GET["sortby"]) !== false) {
			$rsSelectRecipUpsell .= " ".CWqueryGetSort($rsSelectRecipUpsell, $_GET["sortby"], $_GET["sortdir"], "p.product_name ASC");
		} else {
			$rsSelectRecipUpsell .= " ORDER BY p.product_name";
		}
	} else {
		$rsSelectRecipUpsell .= " ORDER BY p.product_name";
	}
	return CWqueryGetRS($rsSelectRecipUpsell);
}

// // ---------- List All Active Product Categories ---------- // 

function CWquerySelectActiveCategories($product_id=0) {
	$rsListCategories="SELECT DISTINCT cc.category_id, cc.category_name, cc.category_sort
						FROM
						cw_categories_primary cc,
						cw_product_categories_primary rr,
						cw_products pp
						WHERE cc.category_name <> ''
						AND pp.product_archive <> 1
						AND pp.product_on_web <> 0
						AND rr.product2category_category_id = cc.category_id
						AND rr.product2category_product_id = pp.product_id";
	if($product_id) {
		$rsListCategories.=" AND pp.product_id = '".CWqueryParam($product_id)."'";
	}
	$rsListCategories.=" ORDER BY category_sort, category_name";
	return CWqueryGetRS($rsListCategories);
}
// // ---------- List All Active Product Secondary Categories ---------- // 

function CWquerySelectActiveScndCategories($filter_cat=0,$product_id=0) {
	$rsListScndCategories="SELECT DISTINCT sc.secondary_id, sc.secondary_name, sc.secondary_sort
							FROM cw_categories_secondary sc
							,cw_product_categories_secondary rr
							, cw_products pp";
	if($filter_cat > 0) {
		$rsListScndCategories.="
							,cw_product_categories_primary cr";
	}
	$rsListScndCategories.="
							WHERE secondary_archive <> 1
							AND pp.product_archive <> 1
							AND pp.product_on_web <> 0
							AND rr.product2secondary_secondary_id = sc.secondary_id
							AND rr.product2secondary_product_id = pp.product_id";
	if($filter_cat > 0) {
		$rsListScndCategories.=" AND cr.product2category_category_id = '".CWqueryParam($filter_cat)."'
							AND cr.product2category_product_id = pp.product_id";
	}
	if($product_id > 0) {
		$rsListScndCategories.=" AND pp.product_id = ".CWqueryParam($product_id);
	}
	$rsListScndCategories.=" ORDER BY secondary_sort, secondary_name";
	return CWqueryGetRS($rsListScndCategories);
}

// // ---------- List Product Options ---------- // 

function CWquerySelectOptions($product_id=0) {
	$rsListOptions="SELECT DISTINCT
					cw_option_types.optiontype_id,
					cw_option_types.optiontype_name";
	if($product_id > 0) {
		$rsListOptions.=" ,
						cw_options.option_id,
						cw_options.option_name,
						cw_options.option_sort";
	}
	$rsListOptions.=" FROM";
	if($product_id > 0) {
		$rsListOptions.=" cw_products
							INNER JOIN (( cw_option_types
							INNER JOIN cw_options ON cw_option_types.optiontype_id = cw_options.option_type_id)
							INNER JOIN cw_product_options ON cw_option_types.optiontype_id = cw_product_options.
							product_options2optiontype_id) ON cw_products.product_id = cw_product_options.
							product_options2prod_id WHERE cw_products.product_id= '".CWqueryParam($product_id)."' 
							AND NOT cw_options.option_archive = 1";
	} else {
		$rsListOptions.=" cw_option_types
						WHERE NOT optiontype_archive = 1
						AND NOT optiontype_deleted = 1";
	}
	$rsListOptions.=" ORDER BY
					cw_option_types.optiontype_name";
	if($product_id > 0) {
		$rsListOptions.=" ,cw_options.option_name
						,cw_options.option_sort";
	}

	return CWqueryGetRS($rsListOptions);
}

function CWquerySelectOptionsGroup($product_id=0) {
	$rsListOptions="SELECT DISTINCT
					cw_option_types.optiontype_id,
					cw_option_types.optiontype_name";
	if($product_id > 0) {
		$rsListOptions.=" ,
						cw_options.option_id,
						cw_options.option_name,
						cw_options.option_sort";
	}
	$rsListOptions.=" FROM";
	if($product_id > 0) {
		$rsListOptions.=" cw_products
							INNER JOIN (( cw_option_types
							INNER JOIN cw_options ON cw_option_types.optiontype_id = cw_options.option_type_id)
							INNER JOIN cw_product_options ON cw_option_types.optiontype_id = cw_product_options.
							product_options2optiontype_id) ON cw_products.product_id = cw_product_options.
							product_options2prod_id WHERE cw_products.product_id= '".CWqueryParam($product_id)."' 
							AND NOT cw_options.option_archive = 1";
	} else {
		$rsListOptions.=" cw_option_types
						WHERE NOT optiontype_archive = 1
						AND NOT optiontype_deleted = 1";
	}
	$rsListOptions.=" group by cw_option_types.optiontype_name  ORDER BY
					cw_option_types.optiontype_name";
	if($product_id > 0) {
		$rsListOptions.=" ,cw_options.option_name
						,cw_options.option_sort";
	}

	return CWqueryGetRS($rsListOptions);		
}

// // ---------- List Related Product Options  ---------- // 

function CWquerySelectRelOptions($product_id,$product_options=NULL) {
	$rsGetOptnRelIDs="SELECT r.sku_option_id
						FROM (cw_skus s
						INNER JOIN cw_sku_options r
						ON s.sku_id = r.sku_option2sku_id)
						INNER JOIN cw_options so
						ON so.option_id = r.sku_option2option_id
						WHERE s.sku_product_id = '".CWqueryParam($product_id)."'";	
	$poption = explode(',',$product_options);	
// only use this if there are some options 			
	if(count($poption)>0) {
		$rsGetOptnRelIDs.="AND so.option_type_id IN ('".CWqueryParam($product_options)."')";
	}
	$rsGetOptnRelIDs.="ORDER BY so.option_sort";
	return CWqueryGetRS($rsGetOptnRelIDs);
}


// // ---------- Delete Related Product Options ---------- // 

function CWqueryDeleteRelProductOptions($product_id) {
	$query="DELETE FROM cw_product_options
			WHERE product_options2prod_id = '".CWqueryParam($product_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Insert Related Product Options ---------- // 

function CWqueryInsertProductOptions($product_id,$optiontype_id) {
	$query="INSERT INTO cw_product_options
				(product_options2prod_id, product_options2optiontype_id)
				VALUES ('".CWqueryParam($product_id)."', '".CWqueryParam($optiontype_id)."' )";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}
// // ---------- Archive a Product ---------- // 
function CWqueryArchiveProduct($product_id) {// set archive = 1 
	$query = "UPDATE cw_products
				SET product_archive = 1
				WHERE product_id = '".CWqueryParam($product_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Reactivate an Archived Product ---------- // 

function CWqueryReactivateProduct($product_id) {// set archive = 0 
	$query = "UPDATE cw_products
			SET product_archive = 0
			WHERE product_id = '".CWqueryParam($product_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// Reactivate all options associated with this product 
	$rsArchivedOptions = "SELECT cw_options.option_id
						FROM cw_options
						INNER JOIN ((cw_products
						INNER JOIN cw_skus
						ON cw_products.product_id = cw_skus.sku_product_id)
						INNER JOIN cw_sku_options ON cw_skus.sku_id = cw_sku_options.sku_option2sku_id)
						ON cw_options.option_id = cw_sku_options.sku_option2option_id
						WHERE cw_products.product_id = '".CWqueryParam($product_id)."'
						AND cw_options.option_archive = 1";
	$rsArchivedOptions = mysql_query($rsArchivedOptions,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$rsArchivedOptions);
	if(mysql_num_rows($rsArchivedOptions)!=0) {
		$ArchivedOptions='';	
		while($row = mysql_fetch_array($rsArchivedOptions)) {
			$ArchivedOptions.= $row['option_id'].',';
		}
		$ArchivedOptions = trim($ArchivedOptions,',');
		$query="UPDATE cw_options SET option_archive = 0 WHERE option_id IN (".CWqueryParam($ArchivedOptions).")";
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	}
}

// // ---------- List Top Selling Products ---------- // 

function CWquerySelectTopProducts($show_ct=15) {
	$rsTopProductsQuery="";
	$sortQuery ="";
	$rsTopProductsQuery="SELECT count(*) as prod_counter,
						p.product_id,
						p.product_name,
						p.product_merchant_product_id,
						p.product_date_modified
						FROM cw_products p
						, cw_order_skus o
						, cw_skus s
						WHERE o.ordersku_sku = s.sku_id
						AND s.sku_product_id = p.product_id
						GROUP BY p.product_id,
						p.product_name, p.product_merchant_product_id,
						p.product_date_modified
						ORDER BY prod_counter DESC, product_date_modified";
	if ($show_ct > 0) {
		$rsTopProductsQuery .= " LIMIT ".CWqueryParam($show_ct);
	}
	return CWqueryGetRS($rsTopProductsQuery);
}

// // ---------- Delete Product Discount Records ---------- // 
function CWqueryDeleteProductDiscount($product_id) {
	$deleteQuery = "DELETE FROM cw_discount_products
					WHERE discount2product_product_id = ".CWqueryParam($product_id)."";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($deleteQuery,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$deleteQuery);
}


// // ---------- Delete SKU Discount Records ---------- // 
function CWqueryDeleteSkuDiscount($sku_list) {
	$deleteQuery = "DELETE FROM cw_discount_skus
					WHERE discount2sku_sku_id IN (".CWqueryParam($sku_list).")";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($deleteQuery,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$deleteQuery);
}


// // ---------- List Product Skus ---------- // 
function CWquerySelectSkus($product_id,$doSort=false) {
	$rsGetSKUs="SELECT sku_id, sku_product_id, sku_merchant_sku_id, sku_price, sku_ship_base, sku_alt_price, sku_weight, sku_stock, sku_on_web, sku_sort, sku_download_file, sku_download_id, sku_download_limit, sku_download_version FROM cw_skus
					WHERE sku_product_id = '".CWqueryParam($product_id)."'";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsGetSKUs, $_GET["sortby"]) !== false) {
			$rsGetSKUs .= " ".CWqueryGetSort($rsGetSKUs, $_GET["sortby"], $_GET["sortdir"], "sku_sort ASC");
		} else {
			$rsGetSKUs .= " ORDER BY sku_sort";
		}
	} else {
		$rsGetSKUs .= " ORDER BY sku_sort";
	}
	return CWqueryGetRS($rsGetSKUs);
}

// // ---------- Get SKU Options ---------- // 

function CWquerySelectSkuOptions($sku_id) {
	$rsSkuOptions="SELECT
					ot.optiontype_name,
					so.option_name,
					r.sku_option_id,
					r.sku_option2option_id,
					so.option_type_id
				FROM (
					cw_option_types ot
						INNER JOIN cw_options so
						ON (ot.optiontype_id = so.option_type_id)
						AND (ot.optiontype_id = so.option_type_id))
						INNER JOIN cw_sku_options r
						ON so.option_id = r.sku_option2option_id
				WHERE
					r.sku_option2sku_id='".CWqueryParam($sku_id)."'
				AND NOT ot.optiontype_deleted = 1
				ORDER BY so.option_sort, ot.optiontype_name";
	return CWqueryGetRS($rsSkuOptions);
}


	// // ---------- Insert a SKU ---------- // 

// product id and sku merchant id are required 
// optional values 
 function CWqueryInsertSKU($sku_merchant_sku_id,$sku_product_id,$sku_price=0,$sku_ship_base=0,$sku_alt_price=0,$sku_weight=0,$sku_stock=0,$sku_on_web=1,$sku_sort=0) {
	$query="INSERT INTO cw_skus (
				sku_merchant_sku_id,
				sku_product_id,
				sku_price,
				sku_alt_price,
				sku_ship_base,
				sku_weight,
				sku_stock,
				sku_on_web,
				sku_sort
				)
				VALUES
				(
					'".CWqueryParam($sku_merchant_sku_id)."',
					'".CWqueryParam($sku_product_id)."',
					".CWqueryParam(CWsqlNumber($sku_price)).",
					".CWqueryParam(CWsqlNumber($sku_alt_price)).",
					".CWqueryParam(CWsqlNumber($sku_ship_base)).",
					'".CWqueryParam($sku_weight)."',
					'".CWqueryParam($sku_stock)."',
					'".CWqueryParam($sku_on_web)."',
					'".CWqueryParam($sku_sort)."'
			)";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
// Get the ID of the new SKU for further inserts 
	$rsNewSKUID = "SELECT sku_id
					FROM cw_skus
					WHERE sku_merchant_sku_id = '".CWqueryParam($sku_merchant_sku_id)."'";
	$res = mysql_query($rsNewSKUID,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$rsNewSKUID);
	$row=mysql_fetch_array($res);
	return $row['sku_id'];				
}
// // ---------- Update a SKU ---------- // 
// product id and sku id are required 
// optional values 
function CWqueryUpdateSKU($sku_id,$sku_product_id,$sku_price=0,$sku_ship_base=0,$sku_alt_price=0,$sku_weight=0,$sku_stock=0,$sku_on_web=1,$sku_sort=0) {
	$query = "UPDATE cw_skus
							SET
							sku_product_id='".CWqueryParam($sku_product_id)."',
							sku_price=".CWqueryParam(CWsqlNumber($sku_price)).",
							sku_ship_base=".CWqueryParam(CWsqlNumber($sku_ship_base)).",
							sku_alt_price=".CWqueryParam(CWsqlNumber($sku_alt_price)).",
							sku_weight='".CWqueryParam($sku_weight)."',
							sku_stock= '".CWqueryParam($sku_stock)."',
							sku_on_web= '".CWqueryParam($sku_on_web)."',
							sku_sort= '".CWqueryParam($sku_sort)."'
							WHERE sku_id='".CWqueryParam($sku_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Delete SKUS ---------- // 

function CWqueryDeleteSKUs($sku_list) {
	$query="DELETE FROM cw_skus
	WHERE sku_id IN ('".CWqueryParam($sku_list)."')";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Insert Related SKU Option ---------- // 

function CWqueryInsertRelSKUOption($sku_id,$option_id) {
	$query = "INSERT INTO cw_sku_options
				(sku_option2sku_id, sku_option2option_id)
				VALUES ('".CWqueryParam($sku_id)."', '".CWqueryParam($option_id)."' )";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Delete Related SKU Options ---------- // 

function CWqueryDeleteRelSKUOptions($option_list,$sku_list) {
	$query="DELETE FROM cw_sku_options
			WHERE sku_option_id NOT IN ('".CWqueryParam($option_list)."')
			AND sku_option2sku_id IN ('".CWqueryParam($sku_list)."')";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Check for existing SKU ID ---------- // 

function CWquerySelectSKUID($sku_merchant_sku_id) {
	$query="SELECT sku_merchant_sku_id FROM cw_skus WHERE sku_merchant_sku_id = '".CWqueryParam($sku_merchant_sku_id)."'";
	return CWqueryGetRS($query);
}

// // ---------- Count Orders for Product SKUS ---------- // 

function CWqueryCountSKUOrders($sku_list) {
	$rsCheckForOrders="SELECT Count(ordersku_id) as orderCount
							FROM cw_order_skus
							WHERE ordersku_sku IN(".CWqueryParam($sku_list).") ";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	$res = mysql_query($rsCheckForOrders,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$rsCheckForOrders);
	$row=mysql_fetch_array($res);
	return $row['orderCount'];
}

// // ---------- List Upload Groups ---------- // 

function CWquerySelectImageUploadGroups() {
	$rsGetImageCount ="SELECT  DISTINCT  imagetype_upload_group
							FROM cw_image_types
							ORDER BY imagetype_upload_group";
	return CWqueryGetRS($rsGetImageCount);
}
// // ---------- List Image Types ---------- // 

function CWquerySelectImageTypes($upload_group_id=null, $imagetype_id=null) {
	if ($upload_group_id === null) $upload_group_id = 0;
	if ($imagetype_id === null) $imagetype_id = 0;
	$rsListImageTypes = "SELECT  *
							FROM cw_image_types
							WHERE 1=1";
	if($upload_group_id > 0) {
		$rsListImageTypes.= " AND imagetype_upload_group = '".CWqueryParam($upload_group_id)."'";
	}
	if($imagetype_id > 0) {
		$rsListImageTypes.= " AND imagetype_id = '".CWqueryParam($imagetype_id)."'";
	}
	$rsListImageTypes.= " ORDER BY imagetype_sortorder, imagetype_name";
	return CWqueryGetRS($rsListImageTypes);
}

// // ---------- // Add Image Type // ---------- // 
function CWqueryInsertImageType($imagetype_upload_group, $imagetype_name, $imagetype_sortorder, $imagetype_folder, $imagetype_max_width=null, $imagetype_max_height=null, $imagetype_crop_width=null, $imagetype_crop_height=null) {
	if ($imagetype_max_width === null) $imagetype_max_width = 120;
	if ($imagetype_max_height === null) $imagetype_max_height = 120;
	if ($imagetype_crop_width === null) $imagetype_crop_width = 0;
	if ($imagetype_crop_height === null) $imagetype_crop_height = 0;
	$query = "INSERT INTO cw_image_types(
			imagetype_name,
			imagetype_sortorder,
			imagetype_folder,
			imagetype_max_width,
			imagetype_max_height,
			";
	if ($imagetype_crop_width > 0) {
		$query .= "imagetype_crop_width,
			";
	}
	if ($imagetype_crop_height > 0) {
		$query .= "imagetype_crop_height,
			";
	}
	$query .= "imagetype_upload_group
			) VALUES (
			'".CWqueryParam(trim($imagetype_name))."',
			".CWqueryParam($imagetype_sortorder).",
			'".CWqueryParam(trim($imagetype_folder))."',
			".CWqueryParam($imagetype_max_width).",
			".CWqueryParam($imagetype_max_height).",
			";
	if ($imagetype_crop_width > 0) {
		$query .= CWqueryParam($imagetype_crop_width).",
			";
	}
	if ($imagetype_crop_height > 0) {
		$query .= CWqueryParam($imagetype_crop_height).",
			";
	}
	$query .= CWqueryParam($imagetype_upload_group)."
			)";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	$res = mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- // Update Image Type // ---------- // 
function CWqueryUpdateImageType($imagetype_id, $imagetype_upload_group, $imagetype_name=null, $imagetype_sortorder=null, $imagetype_folder=null, $imagetype_max_width=null, $imagetype_max_height=null, $imagetype_crop_width=null, $imagetype_crop_height=null) {
	if ($imagetype_name === null) $imagetype_name = "";
	if ($imagetype_sortorder === null) $imagetype_sortorder = -1;
	if ($imagetype_folder === null) $imagetype_folder = "";
	if ($imagetype_max_width === null) $imagetype_max_width = 0;
	if ($imagetype_max_height === null) $imagetype_max_height = 0;
	if ($imagetype_crop_width === null) $imagetype_crop_width = 0;
	if ($imagetype_crop_height === null) $imagetype_crop_height = 0;
	$rsUpdateImageType = "UPDATE cw_image_types
			SET
			";
	if (strlen(trim($imagetype_name))) {
		$rsUpdateImageType .= "imagetype_name = '".CWqueryParam(trim($imagetype_name))."',
			";
	}
	if ($imagetype_sortorder > -1) {
		$rsUpdateImageType .= "imagetype_sortorder = '".CWqueryParam($imagetype_sortorder)."',
			";
	}
	if (strlen(trim($imagetype_folder))) {
		$rsUpdateImageType .= "imagetype_folder = '".CWqueryParam(trim($imagetype_folder))."',
			";
	}
	if ($imagetype_max_width > 0) {
		$rsUpdateImageType .= "imagetype_max_width = '".CWqueryParam($imagetype_max_width)."',
			";
	}
	if ($imagetype_max_height > 0) {
		$rsUpdateImageType .= "imagetype_max_height = '".CWqueryParam($imagetype_max_height)."',
			";
	}
	if ($imagetype_crop_width > 0) {
		$rsUpdateImageType .= "imagetype_crop_width = '".CWqueryParam($imagetype_crop_width)."',
			";
	}
	if ($imagetype_crop_height > 0) {
		$rsUpdateImageType .= "imagetype_crop_height = '".CWqueryParam($imagetype_crop_height)."',
			";
	}
	$rsUpdateImageType .= "imagetype_upload_group = ".CWqueryParam($imagetype_upload_group)."
			WHERE imagetype_id = ".CWqueryParam($imagetype_id);
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	$res = mysql_query($rsUpdateImageType,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$rsUpdateImageType);
}

// // ---------- // Delete Image Type // ---------- // 
function CWqueryDeleteImageType($imagetype_id) {
	$query = "DELETE FROM cw_image_types
			WHERE imagetype_id = ".CWqueryParam($imagetype_id);
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	$res = mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}


// // ---------- List Product Images ---------- // 


function CWquerySelectProductImages($product_id=0,$imagetype_id=0,$imagetype_upload_group=0) {
	$rsProductImages = "SELECT *
							FROM cw_product_images ii,
							cw_image_types tt
							WHERE tt.imagetype_id = ii.product_image_imagetype_id";
	if($product_id > 0) {
		$rsProductImages.= " AND ii.product_image_product_id = '".CWqueryParam($product_id)."'";
	}
	if($imagetype_id > 0) {
		$rsProductImages.= " AND ii.product_image_imagetype_id = '".CWqueryParam($imagetype_id)."'";
	}
	if($imagetype_upload_group > 0) {
		$rsProductImages.= " AND tt.imagetype_upload_group = '".CWqueryParam($imagetype_upload_group)."'";
	}
	$rsProductImages.= " ORDER BY ii.product_image_sortorder, tt.imagetype_sortorder";
	return CWqueryGetRS($rsProductImages);
}

// // ---------- List Product Images by Filename ---------- // 

function CWquerySelectProductImageFiles($img_filename) {
	$rsProductImages = "SELECT *
							FROM cw_product_images
							WHERE product_image_filename = '".CWqueryParam($img_filename)."'
							ORDER BY tt.imagetype_sortorder";
	return CWqueryGetRS($rsProductImages);
}
// // ---------- Delete Product Image Record ---------- // 

function CWqueryDeleteProductImage($product_id,$imagetype_id) {
	$query="DELETE FROM cw_product_images
			WHERE product_image_product_id = '".CWqueryParam($product_id)."'";
	if($imagetype_id>0) {
		$query.="AND product_image_imagetype_id = '".CWqueryParam($imagetype_id)."'";
	}
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}
// // ---------- Delete Product Images by Filename ---------- // 

function CWqueryDeleteProductImageFile($img_filename,$product_id=0) {
	$query = "DELETE from cw_product_images
				WHERE product_image_filename = '".CWqueryParam($img_filename)."'";
	if($product_id > 0) {
		$query.="AND product_image_product_id = '".CWqueryParam($product_id)."'";
	}
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}
// // ---------- Update Product Image Record ---------- // 

function CWqueryUpdateProductImage($product_id,$imagetype_id,$image_filename) {
	$query="UPDATE cw_product_images
	SET product_image_filename = '".CWqueryParam($image_filename)."'
	WHERE product_image_product_id = '".CWqueryParam($product_id)."'
	AND product_image_imagetype_id = '".CWqueryParam($imagetype_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}
// // ---------- Insert New Product Image Record ---------- // 
	
function CWqueryInsertProductImage($product_id,$imagetype_id,$image_filename,$imagetype_sortorder) {
	$query = "INSERT INTO cw_product_images (
				product_image_product_id,
				product_image_imagetype_id,
				product_image_filename,
				product_image_sortorder
			) VALUES (
				'".CWqueryParam($product_id)."'
				,'".CWqueryParam($imagetype_id)."'
				,'".CWqueryParam($image_filename)."'
				,'".CWqueryParam($imagetype_sortorder)."'
			)";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}
// // ---------- List Available Products for Upsell Selection ---------- // 

function CWquerySelectUpsellSelections($upsell_cat=0,$upsell_scndcat=0,$omitted_products=0,$doSort=false) {
	$rsUpsellProducts = "SELECT pp.product_name, pp.product_id, pp.product_merchant_product_id
							FROM cw_products pp";
	if($upsell_cat > 0) {
		$rsUpsellProducts.= ", cw_product_categories_primary cc";	
	}
	if($upsell_scndcat > 0) {
		$rsUpsellProducts.= ", cw_product_categories_secondary sc";	
	}
	$rsUpsellProducts.= "  WHERE NOT pp.product_archive = 1"; 
	$omit_pro = $omitted_products;
	if (!is_array($omit_pro) && strlen(trim($omit_pro))) $omit_pro = explode(',',$omit_pro);
	else if (!is_array($omit_pro)) $omit_pro = array();
	if(count($omit_pro)) {
		$rsUpsellProducts.= " AND NOT pp.product_id in(".$omitted_products.")";
	}
	// category / secondary cat 
	if($upsell_cat > 0 && $_ENV["application.cw"]["adminProductUpsellByCatEnabled"]) {
		$rsUpsellProducts.= " AND cc.product2category_category_id = '".$upsell_cat."'";
		$rsUpsellProducts.= " AND cc.product2category_product_id = pp.product_id";
	}
	if($upsell_scndcat > 0 && $_ENV["application.cw"]["adminProductUpsellByCatEnabled"]) {
		$rsUpsellProducts.= " AND sc.product2secondary_secondary_id = '".$upsell_scndcat."'";
		$rsUpsellProducts.= " AND sc.product2secondary_product_id = pp.product_id";
	}
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsUpsellProducts, $_GET["sortby"]) !== false) {
			$rsUpsellProducts .= " ".CWqueryGetSort($rsUpsellProducts, $_GET["sortby"], $_GET["sortdir"], "pp.product_name ASC");
		} else {
			$rsUpsellProducts .= " ORDER BY pp.product_name";
		}
	} else {
		$rsUpsellProducts .= " ORDER BY pp.product_name";
	}
	return CWqueryGetRS($rsUpsellProducts);
}

// // ---------- Check for Existing Upsell Record ---------- // 
function CWquerySelectUpsell($product_id,$upsell_rel_ID=0) {
	$rsSelectUpsell = "SELECT upsell_id
							FROM cw_product_upsell
							WHERE upsell_product_id = '".CWqueryParam($product_id)."'";
	if($upsell_rel_ID > 0) {
		$rsSelectUpsell.= " AND upsell_2product_id = '".CWqueryParam($upsell_rel_ID)."'";
	}
	return CWqueryGetRS($rsSelectUpsell);
}

// // ---------- Insert Upsell Record ---------- // 
function CWqueryInsertUpsell($upsell_product_id,$upsell_2product_id) {
	$query = "INSERT INTO cw_product_upsell (upsell_product_id, upsell_2product_id)
	VALUES ('".CWqueryParam($upsell_product_id)."','".CWqueryParam($upsell_2product_id)."')";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}
 
 
 
 
 
 
 
function CWqueryDeleteUpsell($product_id=0,$upsell_id=0,$delete_both=0) {
	$query="DELETE FROM cw_product_upsell
			WHERE 1=1 ";
	if($product_id > 0) {
		$query.="AND (upsell_product_id='".CWqueryParam($upsell_id)."' ";
		if($delete_both) {
			$query.="OR upsell_2product_id = ".CWqueryParam($product_id)." ";
		}
		$query.=")";
	}
	if($upsell_id>0) {
		$query.="AND upsell_id='".CWqueryParam($upsell_id)."'";
	}
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}



function CWquerySelectOrder($order_id=0) {
	$rsOrder = "SELECT *
					FROM cw_orders
					WHERE order_id = '".CWqueryParam($order_id)."'";
	return CWqueryGetRS($rsOrder);
}

// ---------- Select Order Details w/ sku info, customer info, etc ---------- // 

function CWquerySelectOrderDetails($order_id=0) {
	$rsOrderDetails = "SELECT
	ss.shipstatus_name,
	o.*,
	c.customer_first_name,
	c.customer_last_name,
	c.customer_id,
	c.customer_email,
	p.product_name,
	p.product_id,
        p.product_custom_info_label,
	s.sku_id,
	s.sku_merchant_sku_id,
	sm.ship_method_name,
	os.ordersku_sku,
	os.ordersku_unique_id,
	os.ordersku_quantity,
	os.ordersku_unit_price,
	os.ordersku_sku_total,
	os.ordersku_tax_rate,
	os.ordersku_discount_amount,
	(o.order_total - (o.order_tax + o.order_shipping + o.order_shipping_tax)) as order_SubTotal
FROM (
	cw_products p
	INNER JOIN cw_skus s
	ON p.product_id = s.sku_product_id)
	INNER JOIN ((cw_customers c
		INNER JOIN (cw_order_status ss
			RIGHT JOIN (cw_ship_methods sm
				RIGHT JOIN cw_orders o
				ON sm.ship_method_id = o.order_ship_method_id)
			ON ss.shipstatus_id = o.order_status)
		ON c.customer_id = o.order_customer_id)
		INNER JOIN cw_order_skus os
		ON o.order_id = os.ordersku_order_id)
	ON s.sku_id = os.ordersku_sku
WHERE o.order_id = '".CWqueryParam($order_id)."'
ORDER BY
	p.product_name,
	s.sku_sort,
	s.sku_merchant_sku_id";
	return CWqueryGetRS($rsOrderDetails);
}

// // ---------- // Get Custom Info Text: CWgetCustomInfo() // ---------- // 
function CWgetCustomInfo($phrase_id) {
    $lookup_id = $phrase_id;
    $returnVal = '';
    $infoQuery = '';

    $infoQuery = mysql_query("SELECT data_content FROM cw_order_sku_data WHERE data_id = ".CWqueryParam($lookup_id),$_ENV["request.cwapp"]["db_link"]);
    if (mysql_num_rows($infoQuery)) {
        $qd = mysql_fetch_assoc($infoQuery);
        $returnVal = $qd['data_content'];
    }

    return $returnVal;
}

// ---------- Select Orders (Search) ---------- // 

function CWquerySelectOrders($status_id=null,$date_start=null,$date_end=null,$id_str=null,$cust_name=null,$max_orders=null,$doSort=false) {
	if ($status_id === null) $status_id = 0;
	if ($date_start === null) $date_start = 0;
	if ($date_end === null) $date_end = 0;
	if ($id_str === null) $id_str = "";
	if ($cust_name === null) $cust_name = "";
	if ($max_orders === null) $max_orders = 0;
	$rsOrders = "";
	if ($date_start) $date_start1 = date("Y-m-d", cartweaverStrtotime($date_start));
	else $date_start1 = 0;
	if ($date_end) $date_end1 = date("Y-m-d", cartweaverStrtotime($date_end)) + " 23:59:59";
	else $date_end1 = date("Y-m-d") + " 23:59:59";
	$rsOrders = "SELECT
		cw_customers.customer_first_name,
		cw_customers.customer_last_name,
		cw_customers.customer_zip,
		cw_customers.customer_id,
		cw_orders.order_id,
		cw_orders.order_date,
		cw_orders.order_status,
		cw_orders.order_address1,
		cw_orders.order_city,
		cw_orders.order_state,
		cw_orders.order_zip,
		cw_orders.order_total,
		cw_order_status.shipstatus_name
		FROM ((cw_customers
		INNER JOIN cw_orders
		ON cw_customers.customer_id = cw_orders.order_customer_id)
		INNER JOIN cw_order_status
		ON cw_order_status.shipstatus_id = cw_orders.order_status)
		WHERE 1 = 1";
	if ($date_start1 != 0) {
		$rsOrders.= ' and cw_orders.order_date >= '."'".CWqueryParam($date_start1)."'";
	}
	if ($date_end1 != 0) {
		$rsOrders.= ' and cw_orders.order_date <= '."'".CWqueryParam($date_end1)."'";
	}
	if($status_id != 0) {
		$rsOrders.= " AND order_status = '".CWqueryParam($status_id)."'"; 
	}
	if($id_str != "") {
		$rsOrders.= " AND order_id  like '%".CWqueryParam($id_str)."%'";
	}
	if($cust_name != "") {
		$rsOrders.= " AND customer_first_name like '%".CWqueryParam($cust_name)."%'  
			OR customer_last_name like '%".CWqueryParam($cust_name)."%'	 OR
			customer_id like '%".CWqueryParam($cust_name)."%'";
	}
	$rsOrders.= " GROUP BY
		cw_orders.order_id,
		cw_orders.order_date,
		cw_orders.order_status,
		cw_orders.order_address1,
		cw_orders.order_city,
		cw_orders.order_state,
		cw_orders.order_zip,
		cw_orders.order_total,
		cw_order_status.shipstatus_name,
		cw_customers.customer_first_name,
		cw_customers.customer_last_name,
		cw_customers.customer_zip,
		cw_customers.customer_id";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsOrders, $_GET["sortby"]) !== false) {
			$rsOrders .= " ".CWqueryGetSort($rsOrders, $_GET["sortby"], $_GET["sortdir"], "cw_orders.order_date DESC");
		} else {
			$rsOrders .= " ORDER BY cw_orders.order_date DESC";
		}
	} else {
		$rsOrders .= " ORDER BY cw_orders.order_date DESC";
	}
	if ($max_orders > 0) $rsOrders .= " LIMIT ".CWqueryParam($max_orders);
	return CWqueryGetRS($rsOrders);
}


// // ---------- // Get Payments Related to Order // ---------- // 

function CWorderPayments($order_id=0) {
	$rsOrderPayments = "SELECT *
							FROM cw_order_payments
							WHERE order_id = '".CWqueryParam($order_id)."'";
	return CWqueryGetRS($rsOrderPayments);
}


// // ---------- // Get Payment Totals for Order // ---------- // 

function CWorderPaymentTotal($order_id=0) {
	$paymentTotal = 0;
	$paymentQuery = CWorderPayments($order_id);
	$paymentTotal = array_sum($paymentQuery["payment_amount"]);
	if(!is_numeric($paymentTotal)) {
		$paymentTotal = 0;
	}
	return $paymentTotal;		
}

// // ---------- Select SKUS in an order ---------- // 

function CWquerySelectOrderSkus($order_id) {
	$rsOrderSkus = "SELECT
						os.ordersku_sku,
						os.ordersku_quantity,
						os.ordersku_unit_price,
						os.ordersku_sku_total,
						s.sku_id,
						s.sku_merchant_sku_id,
						c.customer_id,
						ss.shipstatus_name as orderstatus,
						o.order_id,
						o.order_total
						FROM (
						cw_products p
						INNER JOIN cw_skus s
						ON p.product_id = s.sku_product_id)
						INNER JOIN ((cw_customers c
						INNER JOIN (cw_order_status ss
						RIGHT JOIN (cw_ship_methods sm
						RIGHT JOIN cw_orders o
						ON sm.ship_method_id = o.order_ship_method_id)
						ON ss.shipstatus_id = o.order_status)
						ON c.customer_id = o.order_customer_id)
						INNER JOIN cw_order_skus os
						ON o.order_id = os.ordersku_order_id)
						ON s.sku_id = os.ordersku_sku
						WHERE o.order_id = '".CWqueryParam($order_id)."'
						ORDER BY
						p.product_name,
						s.sku_sort";
	return CWqueryGetRS($rsOrderSkus);
}








// // ---------- Update Order ---------- // 

// ID and Status are required 
// optional arguments 
function CWqueryUpdateOrder($order_id,$order_status,$order_ship_date='',$order_ship_charge=0,$order_tracking_id='',$order_notes='',$order_return_date='',$order_return_amount=0) {
	//echo 'dgdfgfg'.$order_ship_date.'d';
	$query = "UPDATE cw_orders
			SET order_status='".$order_status."'
			 ";
	if($order_ship_date!= '' && cartweaverStrtotime($order_ship_date) !== false) {
		$order_ship_date = "'".CWqueryParam(@date('Y-m-d H:i:s',cartweaverStrtotime($order_ship_date)))."'";
	}
	else {
		$order_ship_date.="Null";
	}
	if($order_return_date != '' && cartweaverStrtotime($order_return_date) !== false) {
		$order_return_date = "'".CWqueryParam(@date('Y-m-d H:i:s',cartweaverStrtotime($order_return_date)))."'";
	}
	else {
		$order_return_date.="Null";
	}
	$query.=",order_ship_date=".$order_ship_date.",
				order_actual_ship_charge=".CWqueryParam(CWsqlNumber($order_ship_charge)).",
				order_ship_tracking_id='".CWqueryParam($order_tracking_id)."',
				order_notes='".CWqueryParam($order_notes)."',
				order_return_date = ".$order_return_date.",
				order_return_amount = ".CWqueryParam(CWsqlNumber($order_return_amount))."
				WHERE order_id='".CWqueryParam($order_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}





// // ---------- Delete Order ---------- // 

function CWqueryDeleteOrder($order_id) {// delete the order skus 
	// delete the order skus 
	$query = "DELETE FROM cw_order_skus WHERE ordersku_order_id='".CWqueryParam($order_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// delete the order discounts 
	$query = "DELETE FROM cw_discount_usage WHERE discount_usage_order_id='".CWqueryParam($order_id)."'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// delete the order record 
	$query = "DELETE FROM cw_orders WHERE order_id='".CWqueryParam($order_id)."'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}




// // ---------- Select Order Status Code Value(s) ---------- // 

function CWquerySelectOrderStatus($shipstatus_id=0) {
	$orderStatusQuery="SELECT *
						FROM cw_order_status";
	if($shipstatus_id > 0) {
		$orderStatusQuery.=" WHERE shipstatus_id = '".CWqueryParam($shipstatus_id)."'";
	}	
	$orderStatusQuery.=" ORDER BY shipstatus_sort ASC";
	return CWqueryGetRS($orderStatusQuery);
} 



	// // ---------- Update Order Status Code Values ---------- // 

// ID is required 
// others are optional 
function CWqueryUpdateShipStatus($status_id,$status_name=NULL,$status_sort=0) {
	$query = "UPDATE cw_order_status
			SET
			shipstatus_sort='".CWqueryParam($status_sort)."'";
	if(trim($status_name)!="") {
		$query.=",shipstatus_name='".CWqueryParam($status_name)."'"; 
	}
	$query.="WHERE shipstatus_id='".CWqueryParam($status_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}





// // ---------- Select Customers (search) ---------- // 
function CWquerySelectCustomers($cust_name=NULL,$id_str=NULL,$cust_email=NULL,$cust_addr=NULL,$order_str=NULL,$show_ct=0, $doSort=false) {
	$rsCustomers="SELECT customer_id_a AS customer_id, customer_type_id_a AS customer_type_id, customer_first_name_a AS customer_first_name, customer_last_name_a AS customer_last_name, customer_address1_a AS customer_address1, customer_address2_a AS customer_address2, customer_city_a AS customer_city, customer_zip_a AS customer_zip, customer_ship_name_a AS customer_ship_name, customer_ship_company_a AS customer_ship_company, customer_ship_address1_a AS customer_ship_address1, customer_ship_address2_a AS customer_ship_address2, customer_ship_city_a AS customer_ship_city, customer_ship_zip_a AS customer_ship_zip, customer_phone_a AS customer_phone, customer_phone_mobile_a AS customer_phone_mobile, customer_email_a AS customer_email, customer_username_a AS customer_username, customer_password_a AS customer_password, customer_guest_a AS customer_guest, customer_company_a AS customer_company, stateprov_name_a AS stateprov_name, customer_state_destination_a AS customer_state_destination, order_customer_id_a AS order_customer_id, top_order_date_a AS top_order_date, SUM(total_spending_a) AS total_spending, 
		(SELECT cw_orders.order_id FROM cw_orders WHERE cw_orders.order_customer_id = order_customer_id_a ORDER BY cw_orders.order_date DESC LIMIT 1) AS order_id, 
		(SELECT cw_orders.order_total FROM cw_orders WHERE cw_orders.order_customer_id = order_customer_id_a ORDER BY cw_orders.order_date DESC LIMIT 1) AS order_total
	
	FROM (
	
					SELECT  cw_customers.customer_id AS customer_id_a,
						cw_customers.customer_type_id AS customer_type_id_a,
						cw_customers.customer_first_name AS customer_first_name_a,
						cw_customers.customer_last_name AS customer_last_name_a,
						cw_customers.customer_address1 AS customer_address1_a,
						cw_customers.customer_address2 AS customer_address2_a,
						cw_customers.customer_city AS customer_city_a,
						cw_customers.customer_zip AS customer_zip_a,
						cw_customers.customer_ship_name AS customer_ship_name_a,
						cw_customers.customer_ship_company AS customer_ship_company_a,
						cw_customers.customer_ship_address1 AS customer_ship_address1_a,
						cw_customers.customer_ship_address2 AS customer_ship_address2_a,
						cw_customers.customer_ship_city AS customer_ship_city_a,
						cw_customers.customer_ship_zip AS customer_ship_zip_a,
						cw_customers.customer_phone AS customer_phone_a,
						cw_customers.customer_phone_mobile AS customer_phone_mobile_a,
						cw_customers.customer_email AS customer_email_a,
						cw_customers.customer_username AS customer_username_a,
						cw_customers.customer_password AS customer_password_a,
						cw_customers.customer_guest AS customer_guest_a,
						cw_customers.customer_company AS customer_company_a,
						cw_stateprov.stateprov_name AS stateprov_name_a,
						cw_customer_stateprov.customer_state_destination AS customer_state_destination_a,
						cw_orders.order_customer_id AS order_customer_id_a,
						SUM(order_total) as total_spending_a,
						MAX(order_date) as top_order_date_a
		
					FROM (((cw_customers
					INNER JOIN cw_customer_stateprov
					ON cw_customers.customer_id = cw_customer_stateprov.customer_state_customer_id)
		
					INNER JOIN cw_stateprov
					ON cw_stateprov.stateprov_id = cw_customer_stateprov.customer_state_stateprov_id)
		
					LEFT OUTER JOIN cw_orders
					ON cw_orders.order_customer_id = cw_customers.customer_id)
				WHERE cw_customer_stateprov.customer_state_destination='BillTo'";
	// customer name 
	if(trim($cust_name)!='') {
		$rsCustomers.=" AND (
				".$_ENV["application.cw"]["sqlLower"]."(cw_customers.customer_first_name) like '%".CWqueryParam(strtolower($cust_name))."%'
				OR ".$_ENV["application.cw"]["sqlLower"]."(cw_customers.customer_last_name) like '%".CWqueryParam(strtolower($cust_name))."%'
				OR ".$_ENV["application.cw"]["sqlLower"]."(cw_customers.customer_company) like '%".CWqueryParam(strtolower($cust_name))."%'
			)";
	}
	// customer ID 
	if(trim($id_str)!='') {
		$rsCustomers.=" AND ".$_ENV["application.cw"]["sqlLower"]."(cw_customers.customer_id) like '%".CWqueryParam(strtolower($id_str))."%'";
	}
	// customer Email 
	if(trim($cust_email)!='') {
		$rsCustomers.=" AND ".$_ENV["application.cw"]["sqlLower"]."(cw_customers.customer_email) like '%".CWqueryParam(strtolower($cust_email))."%'";
	}
	// customer address 
	if(trim($cust_addr)!='') {
		$rsCustomers.=" AND (
						".$_ENV["application.cw"]["sqlLower"]."(cw_customers.customer_address1) like '%".CWqueryParam(strtolower($cust_addr))."%'
						OR ".$_ENV["application.cw"]["sqlLower"]."(cw_customers.customer_address2) like '%".CWqueryParam(strtolower($cust_addr))."%'
						OR ".$_ENV["application.cw"]["sqlLower"]."(cw_customers.customer_city) like '%".CWqueryParam(strtolower($cust_addr))."%'
						OR ".$_ENV["application.cw"]["sqlLower"]."(cw_customers.customer_zip) like '%".CWqueryParam(strtolower($cust_addr))."%'
						OR ".$_ENV["application.cw"]["sqlLower"]."(cw_stateprov.stateprov_name) like '%".CWqueryParam(strtolower($cust_addr))."%'
						OR ".$_ENV["application.cw"]["sqlLower"]."(cw_customer_stateprov.customer_state_destination) like '%".CWqueryParam(strtolower($cust_addr))."%'
						)";
	}
	if(trim($order_str)!='') {
		$rsCustomers.=" AND customer_id IN (SELECT order_customer_id
						FROM cw_orders
						WHERE ".$_ENV["application.cw"]["sqlLower"]."(cw_orders.order_id) like '%".CWqueryParam(strtolower($order_str))."%')";
	}
	$rsCustomers.=" GROUP BY
						cw_customers.customer_id,
						cw_customers.customer_type_id,
						cw_customers.customer_first_name,
						cw_customers.customer_last_name,
						cw_customers.customer_address1,
						cw_customers.customer_address2,
						cw_customers.customer_city,
						cw_customers.customer_zip,
						cw_customers.customer_ship_name,
						cw_customers.customer_ship_company,
						cw_customers.customer_ship_address1,
						cw_customers.customer_ship_address2,
						cw_customers.customer_ship_city,
						cw_customers.customer_ship_zip,
						cw_customers.customer_phone,
						cw_customers.customer_phone_mobile,
						cw_customers.customer_email,
						cw_customers.customer_username,
						cw_customers.customer_password,
						cw_customers.customer_guest,
						cw_customers.customer_company,
						cw_stateprov.stateprov_name,
						cw_customer_stateprov.customer_state_destination,
						cw_orders.order_customer_id
					ORDER BY cw_customers.customer_last_name, cw_customers.customer_first_name, cw_customers.customer_id
					
		) a GROUP BY customer_id_a, customer_type_id_a, customer_first_name_a, customer_last_name_a, customer_address1_a, customer_address2_a, customer_city_a, customer_zip_a, customer_ship_name_a, customer_ship_company_a, customer_ship_address1_a, customer_ship_address2_a, customer_ship_city_a, customer_ship_zip_a, customer_phone_a, customer_phone_mobile_a, customer_email_a, customer_username_a, customer_password_a, customer_guest_a, customer_company_a, stateprov_name_a, customer_state_destination_a, order_customer_id_a, top_order_date_a, order_total, order_id ";
	if (isset($_GET["sortby"]) && $_GET["sortby"] == "order_date") $_GET["sortby"] = "top_order_date";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsCustomers, $_GET["sortby"]) !== false) {
			$rsCustomers .= " ".CWqueryGetSort($rsCustomers, $_GET["sortby"], $_GET["sortdir"], "total_spending DESC");
		} else {
			$rsCustomers .= " ORDER BY total_spending DESC";
		}
	} else {
		$rsCustomers .= " ORDER BY total_spending DESC";
	}
	if($show_ct<=0) {
		$show_ct = 10000;
	}
	$rsCustomers .= " LIMIT ".$show_ct;
	return CWqueryGetRS($rsCustomers);
}


// // ---------- Select Customer Details ---------- // 

function CWquerySelectCustomerDetails($customer_id=NULL) {	
	$rsCustomerDetails="SELECT cw_customers.customer_id,
						cw_customers.customer_type_id,
						cw_customers.customer_first_name,
						cw_customers.customer_last_name,
						cw_customers.customer_address1,
						cw_customers.customer_address2,
						cw_customers.customer_city,
						cw_customers.customer_zip,
						cw_customers.customer_ship_name,
						cw_customers.customer_ship_company,
						cw_customers.customer_ship_address1,
						cw_customers.customer_ship_address2,
						cw_customers.customer_ship_city,
						cw_customers.customer_ship_zip,
						cw_customers.customer_phone,
						cw_customers.customer_phone_mobile,
						cw_customers.customer_email,
						cw_customers.customer_company,
						cw_customers.customer_username,
						cw_customers.customer_password,
						cw_customers.customer_guest,
						cw_stateprov.stateprov_name,
						cw_stateprov.stateprov_id,
						cw_customer_stateprov.customer_state_destination,
						cw_countries.country_name,
						cw_countries.country_id

						FROM (((cw_customers
								INNER JOIN cw_customer_stateprov
								ON cw_customers.customer_id = cw_customer_stateprov.customer_state_customer_id)
					
								INNER JOIN cw_stateprov
								ON cw_stateprov.stateprov_id = cw_customer_stateprov.customer_state_stateprov_id)
					
								INNER JOIN cw_countries
								ON cw_countries.country_id = cw_stateprov.stateprov_country_id)
					
							WHERE cw_customer_stateprov.customer_state_destination='BillTo'
							AND ".$_ENV["application.cw"]["sqlLower"]."(customer_id) = '".CWqueryParam(strtolower($customer_id))."'";
	return CWqueryGetRS($rsCustomerDetails);
}







// // ---------- Select Customer Shipping Details ---------- // 

function CWquerySelectCustomerShipping($customer_id=NULL) {
	$rscustomerShpping = "SELECT cw_customers.customer_id,
							cw_customers.customer_type_id,
							cw_customers.customer_first_name,
							cw_customers.customer_last_name,
							cw_customers.customer_ship_name,
							cw_customers.customer_ship_address1,
							cw_customers.customer_ship_address2,
							cw_customers.customer_ship_city,
							cw_customers.customer_ship_zip,
							cw_stateprov.stateprov_name,
							cw_stateprov.stateprov_id,
							cw_customer_stateprov.customer_state_destination,
							cw_countries.country_name,
							cw_countries.country_id
			
						FROM (((cw_customers
						INNER JOIN cw_customer_stateprov
						ON cw_customers.customer_id = cw_customer_stateprov.customer_state_customer_id)
			
						INNER JOIN cw_stateprov
						ON cw_stateprov.stateprov_id = cw_customer_stateprov.customer_state_stateprov_id)
			
						INNER JOIN cw_countries
						ON cw_countries.country_id = cw_stateprov.stateprov_country_id)
			
					WHERE cw_customer_stateprov.customer_state_destination='ShipTo'
					 AND ".$_ENV["application.cw"]["sqlLower"]."(customer_id) = '".CWqueryParam(strtolower($customer_id))."'";
	return CWqueryGetRS($rscustomerShpping);
}

// // ---------- Select Customer Orders ---------- // 

function CWquerySelectCustomerOrders($customer_id=0,$max_return=0) {
	$rsCustOrders = "SELECT *
						FROM cw_orders
						WHERE order_customer_id = '".CWqueryParam($customer_id)."'
						ORDER BY order_date DESC";
	return CWqueryGetRS($rsCustOrders);
}





// // ---------- Select Customer Order Details ---------- // 

function CWquerySelectCustomerOrderDetails($customer_id=NULL,$max_return=0) {
	$rsCustomerOrderDetails = "SELECT
									cw_orders.order_id,
									cw_orders.order_date,
									cw_orders.order_total,
									cw_order_skus.ordersku_sku,
									cw_skus.sku_merchant_sku_id,
									cw_products.product_name,
									cw_products.product_id
								FROM
									cw_products
									INNER JOIN (cw_skus
										INNER JOIN (cw_orders
											INNER JOIN cw_order_skus
											ON cw_orders.order_id = cw_order_skus.ordersku_order_id)
										ON cw_skus.sku_id = cw_order_skus.ordersku_sku)
									ON cw_products.product_id = cw_skus.sku_product_id
								WHERE
									cw_orders.order_customer_id = '".CWqueryParam($customer_id)."'
								ORDER BY
									cw_orders.order_date DESC";
	return CWqueryGetRS($rsCustomerOrderDetails);
}

// // ---------- Select Customer Types ---------- // 

function CWquerySelectCustomerTypes() {
	$rsCustTypes = "SELECT *
						FROM cw_customer_types
						ORDER BY customer_type_name";
	return CWqueryGetRS($rsCustTypes);
}




// // ---------- Insert Customer---------- // 

function CWqueryInsertCustomer($customer_type_id=0,$customer_firstname=0,$customer_lastname=0,$customer_email=NULL,$customer_username=NULL,$customer_password=NULL,$customer_company=NULL,$customer_phone=NULL,$customer_phone_mobile=NULL,$customer_address1=NULL,$customer_address2=NULL,$customer_city=NULL,$customer_state=0,$customer_zip=NULL,$customer_ship_name=NULL,$customer_ship_company=NULL,$customer_ship_address1=NULL,$customer_ship_address2=NULL,$customer_ship_city=NULL,$customer_ship_state=0,$customer_ship_zip=NULL) {// make sure email and username are unique 
	$checkDupEmail="";
	$checkDupusername="";
	$newUUID = uniqid();
	$newCustID = '';
	$checkDupEmail = CWqueryGetRS("SELECT customer_email
						FROM cw_customers
						WHERE customer_email = '".CWqueryParam(trim($customer_email))."'
						AND NOT customer_guest = 1");
	if($checkDupEmail["totalRows"] && $_ENV["application.cw"]["customerAccountRequired"]) {
		// if we have a dup, stop and return a message 
		$newCustID = '0-Email';
	} else {
		// if no dup email, contine 
		$checkDupusername = CWqueryGetRS("SELECT customer_username
							FROM cw_customers
							WHERE customer_username = '".CWqueryParam($customer_username)."'
							AND NOT customer_guest = 1");
		if($checkDupusername["totalRows"] && $_ENV["application.cw"]["customerAccountRequired"]) {
			// if we have a dup, stop and return a message 
			$newCustID = '0-username';	
		} else {
			// if no dup username, contine 
			$newCustID = substr($newUUID,0,6).date('y-d-m',CWtime());
			// insert main customer record 
			$query = "INSERT INTO cw_customers
											(
											customer_id
											,customer_type_id
											,customer_first_name
											,customer_last_name
											,customer_email
											,customer_username
											,customer_password
											,customer_company
											,customer_address1
											,customer_address2
											,customer_city
											,customer_zip
											,customer_ship_name
											,customer_ship_company
											,customer_ship_address1
											,customer_ship_address2
											,customer_ship_city
											,customer_ship_zip
											,customer_phone
											,customer_phone_mobile
											,customer_date_modified
											,customer_date_added
											)
											VALUES
											(
												'".CWqueryParam($newCustID)."',
												'".CWqueryParam($customer_type_id)."',
												'".CWqueryParam($customer_firstname)."',
												'".CWqueryParam($customer_lastname)."',";
			if(trim($customer_email)!='') {
				$query.="'".CWqueryParam($customer_email)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_username)!='') {
				$query.="'".CWqueryParam($customer_username)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_password)!='') {
				$query.="'".CWqueryParam($customer_password)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_company)!='') {
				$query.="'".CWqueryParam($customer_company)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_address1)!='') {
				$query.="'".CWqueryParam($customer_address1)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_address2)!='') {
				$query.="'".CWqueryParam($customer_address2)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_city)!='') {
				$query.="'".CWqueryParam($customer_city)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_zip)!='') {
				$query.="'".CWqueryParam($customer_zip)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_ship_name)!='') {
				$query.="'".CWqueryParam($customer_ship_name)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_ship_company)!='') {
				$query.="'".CWqueryParam($customer_ship_company)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_ship_address1)!='') {
				$query.="'".CWqueryParam($customer_ship_address1)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_ship_address2)!='') {
				$query.="'".CWqueryParam($customer_ship_address2)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_ship_city)!='') {
				$query.="'".CWqueryParam($customer_ship_city)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_ship_zip)!='') {
				$query.="'".CWqueryParam($customer_ship_zip)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_phone)!='') {
				$query.="'".CWqueryParam($customer_phone)."',";		
			} else {
				$query.="NULL,";
			}
			if(trim($customer_phone_mobile)!='') {
				$query.="'".CWqueryParam($customer_phone_mobile)."',";		
			} else {
				$query.="NULL,";
			}
			$query.="'".CWqueryParam(@date('Y-d-m h:i:s',@time()))."', '".CWqueryParam(@date('Y-d-m h:i:s',@time()))."')";
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
			// Insert Billing state 
			if($customer_state>0) {
				$query="INSERT INTO cw_customer_stateprov
						(
						customer_state_customer_id,
						customer_state_stateprov_id,
						customer_state_destination
						)
						VALUES
						(
						'".CWqueryParam($newCustID)."',
						'".CWqueryParam($customer_state)."',
						'BillTo'
						)";
				mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);	
			}
			// Insert Shipping State 
			if($customer_ship_state >0) {
				$query="INSERT INTO cw_customer_stateprov
						(
						customer_state_customer_id,
						customer_state_stateprov_id,
						customer_state_destination
						)
						VALUES
						(
						'".CWqueryParam($newCustID)."',
						".CWqueryParam($customer_state).",
						'ShipTo'
						)";
				mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
			}
		}
		// /END check username dup 
	}
	// /END check email dup 
	// pass back the ID of the new customer, or error 0 message 
	return $newCustID;
}
 




// // ---------- Update Customer---------- // 
// ID and Name are required 
// Others optional, default NULL 
function CWqueryUpdateCustomer($customer_id=0,$customer_type_id=0,$customer_firstname=0,$customer_lastname=0,$customer_email=NULL,$customer_username=NULL,$customer_password=NULL,$customer_company=NULL,$customer_phone=NULL,$customer_phone_mobile=NULL,$customer_address1=NULL,$customer_address2=NULL,$customer_city=NULL,$customer_state=0,$customer_zip=NULL,$customer_ship_name=NULL,$customer_ship_company=NULL,$customer_ship_address1=NULL,$customer_ship_address2=NULL,$customer_ship_city=NULL,$customer_ship_state=0,$customer_ship_zip=NULL) {
	$checkDupEmail='';
	$checkDupusername='';
	$updateCustID='';
	
	// verify email and username are unique 
	// check email 
	if(trim($customer_email)!='' && $_ENV["application.cw"]["customerAccountRequired"]) {
		$checkDupEmail = CWqueryGetRS("SELECT customer_email
						FROM cw_customers
						WHERE customer_email = '".CWqueryParam(trim($customer_email))."'
						AND NOT customer_id='".CWqueryParam($customer_id)."'
						AND NOT customer_guest = 1");
		// if we have a dup, return a message 
		if($checkDupEmail["totalRows"]) {
			$updateCustID = '0-Email';
		}
	}
	// check username 
	if(trim($customer_username)!='' && $_ENV["application.cw"]["customerAccountRequired"]) {
		$checkDupusername = CWqueryGetRS("SELECT customer_username
							FROM cw_customers
							WHERE customer_username = '".CWqueryParam(trim($customer_username))."'
							AND NOT customer_id='".CWqueryParam($customer_id)."'
							AND NOT customer_guest = 1");
		if($checkDupusername["totalRows"]) {
			$updateCustID = '0-username';
		}
	}
	// if no duplicates 
	if((substr($updateCustID,0,2) != '0-')) {
		// update main customer record 
		 $query = "UPDATE cw_customers SET
				customer_type_id = '".CWqueryParam($customer_type_id)."'
				,customer_first_name = '".CWqueryParam($customer_firstname)."'
				, customer_last_name = '".CWqueryParam($customer_lastname)."'
				, customer_email='";
		if(trim($customer_email)!='') {
			$query.=CWqueryParam($customer_email);
		}
		$query.="', customer_username='";
		if(trim($customer_username)!='') {
			$query.=CWqueryParam($customer_username);
		}
		$query.="', customer_password='";
		if(trim($customer_password)!='') {
			$query.=CWqueryParam($customer_password);
		}
		$query.="', customer_company='";
		if(trim($customer_company)!='') {
			$query.=CWqueryParam($customer_company);
		}
		$query.="', customer_address1='";
		if(trim($customer_address1)!='') {
			$query.=CWqueryParam($customer_address1);
		}
		$query.="', customer_address2='";
		if(trim($customer_address2)!='') {
			$query.=CWqueryParam($customer_address2);
		}
		$query.="', customer_city='";
		if(trim($customer_city)!='') {
			$query.=CWqueryParam($customer_city);
		}
		$query.="', customer_zip='";
		if(trim($customer_zip)!='') {
			$query.=CWqueryParam($customer_zip);
		}
		$query.="', customer_ship_address1='";
		if(trim($customer_zip)!='') {
			$query.=CWqueryParam($customer_zip);
		}
		$query.="', customer_ship_address1='";
		if(trim($customer_ship_address1)!='') {
			$query.=CWqueryParam($customer_ship_address1);
		}
		$query.="', customer_ship_company='";
		if(trim($customer_ship_company)!='') {
			$query.=CWqueryParam($customer_ship_company);
		}
		$query.="', customer_ship_name='";
		if(trim($customer_ship_name)!='') {
			$query.=CWqueryParam($customer_ship_name);
		}
		$query.="', customer_ship_address2='";
		if(trim($customer_ship_address2)!='') {
			$query.=CWqueryParam($customer_ship_address2);
		}
		$query.="', customer_ship_city='";
		if(trim($customer_ship_city)!='') {
			$query.=CWqueryParam($customer_ship_city);
		}
		$query.="', customer_ship_zip='";
		if(trim($customer_ship_zip)!='') {
			$query.=CWqueryParam($customer_ship_zip);
		}
		$query.="', customer_phone='";
		if(trim($customer_phone)!='') {
			$query.=CWqueryParam($customer_phone);
		}
		$query.="', customer_phone_mobile='";
		if(trim($customer_phone_mobile)!='') {
			$query.=CWqueryParam($customer_phone_mobile);
		}
		 $query.="', customer_date_modified ='".CWqueryParam(@date('Y-d-m h:i:s',time())). "'
				WHERE customer_id='".CWqueryParam($customer_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		// Update Billing state 
		if($customer_state>0) {
				$query1="UPDATE cw_customer_stateprov SET
					customer_state_stateprov_id = '".CWqueryParam($customer_state)."'
					 WHERE customer_state_customer_id = '".CWqueryParam($customer_id)."' AND customer_state_destination = 'BillTo'";
			mysql_query($query1,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query1);
		}
		// Update Shipping State 
		if($customer_ship_state>0) {
			 $query2="UPDATE cw_customer_stateprov SET
					customer_state_stateprov_id = '".CWqueryParam($customer_ship_state)."'
					WHERE customer_state_customer_id = '".CWqueryParam($customer_id)."' AND customer_state_destination = 'ShipTo'";
			mysql_query($query2,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query2);
		}
		$updateCustID = $customer_id;		
	}
	// /END check dups 
	return $updateCustID;
}






// // ---------- Delete Customer ---------- // 

function CWqueryDeleteCustomer($customer_id) {
	$query = "DELETE FROM cw_customer_stateprov
					WHERE customer_state_customer_id = '".CWqueryParam($customer_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	$query = "DELETE FROM cw_customers 
				WHERE customer_id='".CWqueryParam($customer_id)."'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);

}

// // ---------- Select Top Customers ---------- // 
function CWquerySelectTopCustomers($show_ct=0) {
	$rsTopCustomers="SELECT 
					  cw_customers.customer_id,
					  cw_customers.customer_first_name,
					  cw_customers.customer_last_name,
					  cw_customers.customer_email,
					  cw_orders.order_id,
					  MAX(order_total) AS order_total,
					  MAX(order_date) AS top_order_date,
					  SUM(order_total) AS total_spending
					FROM
					  cw_customers,
					  cw_orders
					WHERE
					  cw_orders.order_customer_id = cw_customers.customer_id
					  GROUP BY
  						cw_customers.customer_id
					ORDER BY
					  SUM(order_total) DESC,
					  customer_id DESC";
	if ($show_ct > 0) {
		$rsTopCustomers .= " LIMIT ".CWqueryParam($show_ct);
	}
	return CWqueryGetRS($rsTopCustomers);
}



// //////////////// 
// DISCOUNT QUERIES 
// //////////////// 

// // ---------- Select Discount Types ---------- // 
function CWquerySelectDiscountTypes($show_archived=0) {
	$rsGetTypes = "SELECT discount_type,
			discount_type_description,
			discount_type_archive
			FROM cw_discount_types ";
	if (!$show_archived) {
		$rsGetTypes .= "WHERE NOT discount_type_archive = 1 ";
	}
	$rsGetTypes .= "ORDER BY discount_type_order";
	return CWqueryGetRS($rsGetTypes);
}

// // ---------- Get ALL active or archived discounts ---------- // 
function CWquerySelectStatusDiscounts($discounts_active,$doSort=false) {
	$compareTo = 1;
	$rsStatusDiscounts = "";
	// set up opposite value so we can query with 'not' 
	if ($discounts_active == 1) {
		$compareTo = 0;
	}
	$rsStatusDiscounts = "SELECT discount_id,
		dd.discount_merchant_id,
		dd.discount_name,
		dd.discount_description,
		dd.discount_promotional_code,
		dd.discount_calc,
		dd.discount_amount,
		dd.discount_type,
		dd.discount_start_date,
		dd.discount_end_date,
		dd.discount_limit,
		dd.discount_customer_limit,
		dd.discount_global,
		dd.discount_exclusive,
		dd.discount_priority,
		dd.discount_archive,
		dd.discount_show_description,
		dd.discount_filter_customer_type,
		dd.discount_customer_type,
		dd.discount_filter_customer_id,
		dd.discount_customer_id,
		dd.discount_filter_cart_total,
		dd.discount_cart_total_max,
		dd.discount_cart_total_min,
		dd.discount_filter_item_qty,
		dd.discount_item_qty_min,
		dd.discount_item_qty_max,
		dd.discount_filter_cart_qty,
		dd.discount_cart_qty_min,
		dd.discount_cart_qty_max,
		dd.discount_association_method,
		dt.discount_type_description
		FROM cw_discounts dd, cw_discount_types dt
		WHERE dd.discount_type = dt.discount_type
		AND NOT dd.discount_archive = ".CWqueryParam($compareTo)."";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsStatusDiscounts, $_GET["sortby"]) !== false) {
			$rsStatusDiscounts .= " ".CWqueryGetSort($rsStatusDiscounts, $_GET["sortby"], $_GET["sortdir"], "discount_name ASC");
		} else {
			$rsStatusDiscounts .= " ORDER BY discount_name";
		}
	} else {
		$rsStatusDiscounts .= " ORDER BY discount_name";
	}
	return CWqueryGetRS($rsStatusDiscounts);
}

// // ---------- Select Discounts by ID ---------- // 
function CWquerySelectDiscounts($id_list="") {
	$rsGetDiscounts = "SELECT discount_id,
			discount_merchant_id,
			discount_name,
			discount_description,
			discount_promotional_code,
			discount_calc,
			discount_amount,
			discount_type,
			discount_start_date,
			discount_end_date,
			discount_limit,
			discount_customer_limit,
			discount_global,
			discount_exclusive,
			discount_priority,
			discount_archive,
			discount_show_description,
			discount_filter_customer_type,
			discount_customer_type,
			discount_filter_customer_id,
			discount_customer_id,
			discount_filter_cart_total,
			discount_cart_total_max,
			discount_cart_total_min,
			discount_filter_item_qty,
			discount_item_qty_min,
			discount_item_qty_max,
			discount_filter_cart_qty,
			discount_cart_qty_min,
			discount_cart_qty_max,
			discount_association_method
			FROM cw_discounts";
	if (strlen(trim($id_list))) {
		$rsGetDiscounts .= " WHERE discount_id IN (".CWqueryParam($id_list).")";
	}
	return CWqueryGetRS($rsGetDiscounts);
}

// // ---------- Select Discount Order Details ---------- // 
function CWquerySelectDiscountOrderDetails($discount_id=0,$max_records=100,$order_by="") {
	$rsGetDiscountOrders = "SELECT uu.discount_usage_order_id,
			uu.discount_usage_customer_id,
			uu.discount_usage_datetime,
			cc.customer_first_name,
			cc.customer_last_name
			FROM cw_discount_usage uu, cw_customers cc
			WHERE uu.discount_usage_discount_id = ".CWqueryParam($discount_id)."
			AND uu.discount_usage_customer_id = cc.customer_id";
	if (strlen(trim($order_by))) {
		$rsGetDiscountOrders .= " ORDER BY ".CWqueryParam($order_by)."";
	} else {
		$rsGetDiscountOrders .= " ORDER BY discount_usage_datetime desc";
	}
	if ($max_records > 0) {
		$rsGetDiscountOrders .= " LIMIT ".CWqueryParam($max_records)."";
	}
	return CWqueryGetRS($rsGetDiscountOrders);
}

// // ---------- Insert Discount ---------- // 
function CWqueryInsertDiscount($discount_merchant_id,$discount_name,$discount_amount,$discount_calc,$discount_description="",$discount_show_description=0,$discount_type="",$discount_promotional_code="",$discount_start_date="",$discount_end_date="",$discount_limit=0,$discount_customer_limit=0,$discount_global=0,$discount_exclusive=0,$discount_priority=0,$discount_archive=0) {
	$discount_id = 0;
	$checkDupMerchID = '';
	$checkDupPromoCode = '';
	$insertDiscID = '';
	$getnewDiscID = '';
	// verify merchant ID is unique 
	if (strlen(trim($discount_merchant_id))) {
		$checkDupMerchID = CWqueryGetRS("SELECT discount_merchant_id
											FROM cw_discounts
											WHERE discount_merchant_id = '".CWqueryParam(trim($discount_merchant_id))."'
											AND NOT discount_id='".CWqueryParam($discount_id)."'");
		// if we have a dup, return a message 
		if ($checkDupMerchID["totalRows"]) {
			$insertDiscID = '0-Merchant ID';
		}
	}
	// verify promocode is unique 
	if (strlen(trim($discount_promotional_code))) {
		$checkDupPromoCode = CWqueryGetRS("SELECT discount_promotional_code
											FROM cw_discounts
											WHERE discount_promotional_code = '".CWqueryParam(trim($discount_promotional_code))."'
											AND NOT discount_id='".CWqueryParam($discount_id)."'");
		// if we have a dup, return a message 
		if ($checkDupPromoCode["totalRows"]) {
			$insertDiscID = '0-Promo Code';
		}
	}
	// if no duplicates 
	if (substr($insertDiscID,0,2) != '0-') {
		// insert discount record 
		$query = "INSERT INTO cw_discounts
					(
					 discount_merchant_id
					,discount_name
					,discount_amount
					,discount_calc
					,discount_description
					,discount_show_description
					,discount_type
					,discount_promotional_code
					,discount_start_date
					,discount_end_date
					,discount_limit
					,discount_customer_limit
					,discount_global
					,discount_exclusive
					,discount_priority
					,discount_archive
					,discount_association_method
					)
					VALUES
					(
					'".CWqueryParam($discount_merchant_id)."'
					,'".CWqueryParam($discount_name)."'
					,".CWqueryParam($discount_amount)."
					,'".CWqueryParam($discount_calc)."'";
		$query .= ","; if (strlen(trim($discount_description))) { $query .= "'".CWqueryParam($discount_description)."'"; } else { $query .= "NULL"; }
		$query .= ","; if (strlen(trim($discount_show_description))) { $query .= "".CWqueryParam($discount_show_description).""; } else { $query .= "1"; }
		$query .= ","; if (strlen(trim($discount_type))) { $query .= "'".CWqueryParam($discount_type)."'"; } else { $query .= "NULL"; }
		$query .= ","; if (strlen(trim($discount_promotional_code))) { $query .= "'".CWqueryParam($discount_promotional_code)."'"; } else { $query .= "NULL"; }
		$query .= ","; if (strlen(trim($discount_start_date))) { $query .= "'".CWqueryParam(date("Y-m-d",cartweaverStrtotime($discount_start_date, $_ENV["request.cw"]["scriptDateMask"])))."'"; } else { $query .= "NULL"; }
		$query .= ","; if (strlen(trim($discount_end_date))) { $query .= "'".CWqueryParam(date("Y-m-d",cartweaverStrtotime($discount_end_date, $_ENV["request.cw"]["scriptDateMask"])))."'"; } else { $query .= "NULL"; }
		$query .= ","; if (strlen(trim($discount_limit))) { $query .= "".CWqueryParam($discount_limit).""; } else { $query .= "1"; }
		$query .= ","; if (strlen(trim($discount_customer_limit))) { $query .= "".CWqueryParam($discount_customer_limit).""; } else { $query .= "1"; }
		$query .= ","; if (strlen(trim($discount_global))) { $query .= "".CWqueryParam($discount_global).""; } else { $query .= "1"; }
		$query .= ","; if (strlen(trim($discount_exclusive))) { $query .= "".CWqueryParam($discount_exclusive).""; } else { $query .= "0"; }
		$query .= ","; if (strlen(trim($discount_priority))) { $query .= "".CWqueryParam($discount_priority).""; } else { $query .= "1"; }
		$query .= ","; if (strlen(trim($discount_archive))) { $query .= "".CWqueryParam($discount_archive).""; } else { $query .= "1"; }
		$query .= ",'products'
					)";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$insertDiscID = mysql_insert_id();
	}
	// clear stored discount data from memory 
	unset($_ENV["application.cw"]["discountData"]);
	CWsetApplicationRefresh();
	CWinitApplication();
	CWinitRequest();
	return $insertDiscID;
}

// // ---------- Update Discount (includes filtering data) ---------- // 
function CWqueryUpdateDiscount($discount_id,$discount_merchant_id,$discount_name,$discount_amount,$discount_calc,$discount_description="",$discount_show_description=0,$discount_type="",$discount_promotional_code="",$discount_start_date="",$discount_end_date="",$discount_limit=0,$discount_customer_limit=0,$discount_global=0,$discount_exclusive=0,$discount_priority=0,$discount_archive=0,$discount_association_method="",$discount_filter_customer_type=0,$discount_customer_type="",$discount_filter_customer_id=0,$discount_customer_id="",$discount_filter_cart_total=0,$discount_cart_total_max=0,$discount_cart_total_min=0,$discount_filter_item_qty=0,$discount_item_qty_min=0,$discount_item_qty_max=0,$discount_filter_cart_qty=0,$discount_cart_qty_min=0,$discount_cart_qty_max=0) {
	$checkDupMerchID = '';
	$checkDupPromoCode = '';
	$updateDiscID = '';
	// verify merchant ID is unique 
	if (strlen(trim($discount_merchant_id))) {
		$checkDupMerchID = CWqueryGetRS("SELECT discount_merchant_id
											FROM cw_discounts
											WHERE discount_merchant_id = '".CWqueryParam(trim($discount_merchant_id))."'
											AND NOT discount_id='".CWqueryParam($discount_id)."'");
		// if we have a dup, return a message 
		if ($checkDupMerchID["totalRows"]) {
			$updateDiscID = '0-Merchant ID';
		}
	}
	// verify promocode is unique 
	if (strlen(trim($discount_promotional_code))) {
		$checkDupPromoCode = CWqueryGetRS("SELECT discount_promotional_code
											FROM cw_discounts
											WHERE discount_promotional_code = '".CWqueryParam(trim($discount_promotional_code))."'
											AND NOT discount_id='".CWqueryParam($discount_id)."'");
		// if we have a dup, return a message 
		if ($checkDupPromoCode["totalRows"]) {
			$updateDiscID = '0-Promo Code';
		}
	}
	// if no duplicates 
	if (substr($updateDiscID,0,2) != '0-') {
		// update main discount record 
		$query = "UPDATE cw_discounts SET
					discount_merchant_id = '".CWqueryParam($discount_merchant_id)."'
					,discount_name = '".CWqueryParam($discount_name)."'
					,discount_amount = ".CWqueryParam($discount_amount)."
					,discount_calc = '".CWqueryParam($discount_calc)."'
					,discount_description=";
		if (strlen(trim($discount_description))) { $query .= "'".CWqueryParam($discount_description)."'"; } else { $query .= "NULL"; }
		$query .= ",discount_show_description=";
		if (strlen(trim($discount_show_description))) { $query .= "".CWqueryParam($discount_show_description).""; } else { $query .= "1"; }
		$query .= ",discount_type=";
		if (strlen(trim($discount_type))) { $query .= "'".CWqueryParam($discount_type)."'"; } else { $query .= "NULL"; }
		$query .= ",discount_promotional_code=";
		if (strlen(trim($discount_promotional_code))) { $query .= "'".CWqueryParam($discount_promotional_code)."'"; } else { $query .= "NULL"; }
		$query .= ",discount_start_date=";
		if (strlen(trim($discount_start_date))) { $query .= "'".CWqueryParam(date("Y-m-d",cartweaverStrtotime($discount_start_date, $_ENV["request.cw"]["scriptDateMask"])))."'"; } else { $query .= "NULL"; }
		$query .= ",discount_end_date=";
		if (strlen(trim($discount_end_date))) { $query .= "'".CWqueryParam(date("Y-m-d",cartweaverStrtotime($discount_end_date, $_ENV["request.cw"]["scriptDateMask"])))."'"; } else { $query .= "NULL"; }
		$query .= ",discount_limit=";
		if (strlen(trim($discount_limit))) { $query .= "".CWqueryParam($discount_limit).""; } else { $query .= "1"; }
		$query .= ",discount_customer_limit=";
		if (strlen(trim($discount_customer_limit))) { $query .= "".CWqueryParam($discount_customer_limit).""; } else { $query .= "1"; }
		$query .= ",discount_global=";
		if (strlen(trim($discount_global))) { $query .= "".CWqueryParam($discount_global).""; } else { $query .= "1"; }
		$query .= ",discount_exclusive=";
		if (strlen(trim($discount_exclusive))) { $query .= "".CWqueryParam($discount_exclusive).""; } else { $query .= "0"; }
		$query .= ",discount_priority=";
		if (strlen(trim($discount_priority))) { $query .= "".CWqueryParam($discount_priority).""; } else { $query .= "1"; }
		$query .= ",discount_archive=";
		if (strlen(trim($discount_archive))) { $query .= "".CWqueryParam($discount_archive).""; } else { $query .= "1"; }
		// filtering conditions 
		$query .= ",discount_filter_customer_type=";
		if (strlen(trim($discount_filter_customer_type))) { $query .= "".CWqueryParam($discount_filter_customer_type).""; } else { $query .= "0"; }
		$query .= ",discount_customer_type=";
		if (strlen(trim($discount_customer_type))) { $query .= "'".CWqueryParam(trim(str_replace(" ","",$discount_customer_type)))."'"; } else { $query .= "NULL"; }
		$query .= ",discount_filter_customer_id=";
		if (strlen(trim($discount_filter_customer_id))) { $query .= "".CWqueryParam($discount_filter_customer_id).""; } else { $query .= "0"; }
		$query .= ",discount_customer_id=";
		if (strlen(trim($discount_customer_id))) { $query .= "'".CWqueryParam(trim(str_replace(" ","",$discount_customer_id)))."'"; } else { $query .= "NULL"; }
		$query .= ",discount_filter_cart_total=";
		if (strlen(trim($discount_filter_cart_total))) { $query .= "".CWqueryParam($discount_filter_cart_total).""; } else { $query .= "0"; }
		$query .= ",discount_cart_total_max=";
		if (strlen(trim($discount_cart_total_max))) { $query .= "".CWqueryParam($discount_cart_total_max).""; } else { $query .= "0"; }
		$query .= ",discount_cart_total_min=";
		if (strlen(trim($discount_cart_total_min))) { $query .= "".CWqueryParam($discount_cart_total_min).""; } else { $query .= "0"; }
		$query .= ",discount_filter_item_qty=";
		if (strlen(trim($discount_filter_item_qty))) { $query .= "".CWqueryParam($discount_filter_item_qty).""; } else { $query .= "0"; }
		$query .= ",discount_item_qty_min=";
		if (strlen(trim($discount_item_qty_min))) { $query .= "".CWqueryParam($discount_item_qty_min).""; } else { $query .= "0"; }
		$query .= ",discount_item_qty_max=";
		if (strlen(trim($discount_item_qty_max))) { $query .= "".CWqueryParam($discount_item_qty_max).""; } else { $query .= "0"; }
		$query .= ",discount_filter_cart_qty=";
		if (strlen(trim($discount_filter_cart_qty))) { $query .= "".CWqueryParam($discount_filter_cart_qty).""; } else { $query .= "0"; }
		$query .= ",discount_cart_qty_min=";
		if (strlen(trim($discount_cart_qty_min))) { $query .= "".CWqueryParam($discount_cart_qty_min).""; } else { $query .= "0"; }
		$query .= ",discount_cart_qty_max=";
		if (strlen(trim($discount_cart_qty_max))) { $query .= "".CWqueryParam($discount_cart_qty_max).""; } else { $query .= "0"; }
		// only update method if value is provided 
		if (strlen(trim($discount_association_method)) && $discount_global != 1) {
			$query .= ",discount_association_method=".CWqueryParam($discount_association_method)."";
		}
		$query .= " WHERE discount_id=".CWqueryParam($discount_id)."";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$updateDiscID = $discount_id;
	}
	// clear stored discount data from memory 
	unset($_ENV["application.cw"]["discountData"]);
	CWsetApplicationRefresh();
	CWinitApplication();
	CWinitRequest();
	return $updateDiscID;
}

// // ---------- Update Discount Association Method ---------- // 
function CWqueryUpdateDiscountAssociationMethod($discount_id,$discount_association_method) {
	$updateMethod = '';
	if (strlen(trim($discount_association_method))) {
		$query = "UPDATE cw_discounts
					SET discount_association_method = '".CWqueryParam(trim($discount_association_method))."'
					WHERE discount_id='".CWqueryParam($discount_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	}
	// clear stored discount data from memory 
	unset($_ENV["application.cw"]["discountData"]);
	CWsetApplicationRefresh();
	CWinitApplication();
	CWinitRequest();
}

// // ---------- Delete Discount ---------- // 
function CWqueryDeleteDiscount($discount_id) {
	// delete relative product records 
	$query = "DELETE FROM cw_discount_products WHERE discount2product_discount_id = ".CWqueryParam($discount_id)."";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// delete relative sku records 
	$query = "DELETE FROM cw_discount_skus WHERE discount2sku_discount_id = ".CWqueryParam($discount_id)."";
	// delete relative category records 
	$query = "DELETE FROM cw_discount_categories WHERE discount2category_category_id = ".CWqueryParam($discount_id)."";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// delete discount 
	$query = "DELETE FROM cw_discounts WHERE discount_id=".CWqueryParam($discount_id)."";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// clear stored discount data from memory 
	unset($_ENV["application.cw"]["discountData"]);
	CWsetApplicationRefresh();
	CWinitApplication();
	CWinitRequest();
}

// // ---------- List Available Products for Discount Selection ---------- // 
function CWqueryDiscountProductSelections($discount_cat=0,$discount_scndcat=0,$search_string="%",$search_by="",$omitted_products="0",$show_archived=false,$doSort=false) {
	$searchFor = strtolower($search_string);
	$rsDiscountProducts = "SELECT pp.product_name, pp.product_id
							, pp.product_merchant_product_id
							FROM cw_products pp";
	if ($discount_cat > 0) {
		$rsDiscountProducts .= " INNER JOIN cw_product_categories_primary cc";
	}
	if ($discount_scndcat > 0) {
		$rsDiscountProducts .= " INNER JOIN cw_product_categories_secondary sc";
	}
	$rsDiscountProducts .= " WHERE 1 = 1";
	if (!$show_archived) {
		$rsDiscountProducts .= " AND NOT product_archive = 1";
	}
	if (strlen(trim($omitted_products))) {
		$rsDiscountProducts .= " AND NOT product_id in(".CWqueryParam($omitted_products).")";
	}
	$searchFor = CWqueryParam($searchFor);
	// add search_by options, make case insensitive 
	if ($search_by == "prodID") {
	    $rsDiscountProducts .= " AND ".$_ENV["application.cw"]["sqlLower"]."(pp.product_merchant_product_id) LIKE '%".$searchFor."%'";
	} else if ($search_by == "description") {
	    $rsDiscountProducts .= " AND (".$_ENV["application.cw"]["sqlLower"]."(pp.product_description) LIKE '%".$searchFor."%'
			OR ".$_ENV["application.cw"]["sqlLower"]."(pp.product_preview_description) LIKE '%".$searchFor."%')";
	} else if ($search_by == "prodName") {
		$rsDiscountProducts .= " AND ".$_ENV["application.cw"]["sqlLower"]."(pp.product_name) LIKE '%".$searchFor."%'";
	} else {
		// any field 
		$rsDiscountProducts .= " AND (
			".$_ENV["application.cw"]["sqlLower"]."(pp.product_name) LIKE '%".$searchFor."%'
			OR
			".$_ENV["application.cw"]["sqlLower"]."(pp.product_description) LIKE '%".$searchFor."%'
			OR
			".$_ENV["application.cw"]["sqlLower"]."(pp.product_preview_description) LIKE '%".$searchFor."%'
			OR
			".$_ENV["application.cw"]["sqlLower"]."(pp.product_name) LIKE '%".$searchFor."%'
			OR
			".$_ENV["application.cw"]["sqlLower"]."(pp.product_merchant_product_id) LIKE '%".$searchFor."%'";
		if ($_ENV["application.cw"]["adminProductKeywordsEnabled"]) {
			$rsDiscountProducts .= " OR
				".$_ENV["application.cw"]["sqlLower"]."(pp.product_keywords) LIKE '%".$searchFor."%'";
		}
		$rsDiscountProducts .= ")";
	}
	// category / secondary cat 
	if ($discount_cat > 0) {
		$rsDiscountProducts .= " AND cc.product2category_category_id = ".CWqueryParam($discount_cat)."
			AND cc.product2category_product_id = pp.product_id";
	}
	if ($discount_scndcat > 0) {
		$rsDiscountProducts .= " AND sc.product2secondary_secondary_id = ".CWqueryParam($discount_scndcat)."
			AND sc.product2secondary_product_id = pp.product_id";
	}
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsDiscountProducts, $_GET["sortby"]) !== false) {
			$rsDiscountProducts .= " ".CWqueryGetSort($rsDiscountProducts, $_GET["sortby"], $_GET["sortdir"], "pp.product_sort ASC, pp.product_name ASC");
		} else {
			$rsDiscountProducts .= " ORDER BY pp.product_sort, pp.product_name";
		}
	} else {
		$rsDiscountProducts .= " ORDER BY pp.product_sort, pp.product_name";
	}
	return CWqueryGetRS($rsDiscountProducts);
}

// // ---------- List Available Skus for Discount Selection ---------- // 
function CWqueryDiscountSkuSelections($discount_cat=0,$discount_scndcat=0,$search_string="%",$search_by="",$omitted_skus="0",$show_archived=false) {
	$searchFor = strtolower($search_string);
	$rsDiscountSkus = "SELECT
				pp.product_name,
				pp.product_id,
				pp.product_merchant_product_id,
				ss.sku_id,
				ss.sku_merchant_sku_id,
				ss.sku_price
				FROM cw_products pp
				INNER join cw_skus ss";
	if ($discount_cat > 0) {
		$rsDiscountSkus .= " INNER JOIN cw_product_categories_primary cc";
	}
	if ($discount_scndcat > 0) {
		$rsDiscountSkus .= " INNER JOIN cw_product_categories_secondary sc";
	}
	$rsDiscountSkus .= " WHERE ss.sku_product_id = pp.product_id";
	if (!$show_archived) {
		$rsDiscountSkus .= " AND NOT product_archive = 1";
	}
	if (strlen(trim($omitted_skus))) {
		$rsDiscountSkus .= " AND NOT sku_id in(".CWqueryParam($omitted_skus).")";
	}
	$searchFor = CWqueryParam($searchFor);
	// add search_by options, make case insensitive 
	if ($search_by == "prodID") {
	    $rsDiscountSkus .= " AND ".$_ENV["application.cw"]["sqlLower"]."(pp.product_merchant_product_id) LIKE '%".$searchFor."%'";
	} else if ($search_by == "description") {
	    $rsDiscountSkus .= " AND (".$_ENV["application.cw"]["sqlLower"]."(pp.product_description) LIKE '%".$searchFor."%'
			OR ".$_ENV["application.cw"]["sqlLower"]."(pp.product_preview_description) LIKE '%".$searchFor."%')";
	} else if ($search_by == "prodName") {
		$rsDiscountSkus .= " AND ".$_ENV["application.cw"]["sqlLower"]."(pp.product_name) LIKE '%".$searchFor."%'";
	} else if ($search_by == "skuName") {
		$rsDiscountSkus .= " AND ".$_ENV["application.cw"]["sqlLower"]."(ss.sku_merchant_sku_id) LIKE '%".$searchFor."%'";
	} else {
		// any field 
		$rsDiscountSkus .= " AND (
			".$_ENV["application.cw"]["sqlLower"]."(pp.product_name) LIKE '%".$searchFor."%'
			OR
			".$_ENV["application.cw"]["sqlLower"]."(pp.product_description) LIKE '%".$searchFor."%'
			OR
			".$_ENV["application.cw"]["sqlLower"]."(pp.product_preview_description) LIKE '%".$searchFor."%'
			OR
			".$_ENV["application.cw"]["sqlLower"]."(pp.product_name) LIKE '%".$searchFor."%'
			OR
			".$_ENV["application.cw"]["sqlLower"]."(pp.product_name) LIKE '%".$searchFor."%'
			OR
			".$_ENV["application.cw"]["sqlLower"]."(ss.sku_merchant_sku_id) LIKE '%".$searchFor."%'";
		if ($_ENV["application.cw"]["adminProductKeywordsEnabled"]) {
			$rsDiscountSkus .= " OR
				".$_ENV["application.cw"]["sqlLower"]."(pp.product_keywords) LIKE '%".$searchFor."%'";
		}
		$rsDiscountSkus .= ")";
	}
	// category / secondary cat 
	if ($discount_cat > 0) {
		$rsDiscountSkus .= " AND cc.product2category_category_id = ".CWqueryParam($discount_cat)."
			AND cc.product2category_product_id = pp.product_id";
	}
	if ($discount_scndcat > 0) {
		$rsDiscountSkus .= " AND sc.product2secondary_secondary_id = ".CWqueryParam($discount_scndcat)."
			AND sc.product2secondary_product_id = pp.product_id";
	}
	$rsDiscountSkus .= " AND NOT ss.sku_on_web = 0
		ORDER BY pp.product_sort, pp.product_name, ss.sku_sort, ss.sku_merchant_sku_id";
	return CWqueryGetRS($rsDiscountSkus);
}

// // ---------- Select Existing Discount Product Records ---------- // 
function CWquerySelectDiscountProducts($discount_id,$show_archived=false,$doSort=false) {
	$rsGetproducts = "SELECT
		pp.product_id,
		pp.product_merchant_product_id,
		pp.product_name,
		pp.product_description,
		pp.product_sort,
		dp.discount2product_discount_id as discount_id
		FROM cw_discount_products dp, cw_products pp
		WHERE dp.discount2product_discount_id = ".CWqueryParam($discount_id)."
		AND pp.product_id = dp.discount2product_product_id
		AND NOT pp.product_on_web = 0";
	if (!$show_archived) {
		$rsGetproducts .= " AND NOT pp.product_archive = 1";
	}
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsGetproducts, $_GET["sortby"]) !== false) {
			$rsGetproducts .= " ".CWqueryGetSort($rsGetproducts, $_GET["sortby"], $_GET["sortdir"], "product_sort ASC");
		} else {
			$rsGetproducts .= " ORDER BY product_sort";
		}
	} else {
		$rsGetproducts .= " ORDER BY product_sort";
	}
	return CWqueryGetRS($rsGetproducts);
}

// // ---------- Select Existing Discount SKU Records ---------- // 
function CWquerySelectDiscountSKUs($discount_id,$product_id=0,$show_archived=false,$doSort=false) {
	$rsGetskus = "SELECT
		ss.sku_id,
		ss.sku_merchant_sku_id,
		ss.sku_sort,
		pp.product_name,
		pp.product_id,
		pp.product_merchant_product_id
		FROM
		(cw_discount_skus ds
			INNER JOIN	cw_skus ss
			ON ss.sku_id = ds.discount2sku_sku_id)
			INNER JOIN cw_products pp
			ON ss.sku_product_id = pp.product_id
		WHERE ds.discount2sku_discount_id = ".CWqueryParam($discount_id)."
		AND NOT ss.sku_on_web = 0
		AND NOT pp.product_on_web = 0";
	if (!$show_archived) {
		$rsGetskus .= " AND NOT pp.product_archive = 1";
	}
	if ($product_id != 0) {
		$rsGetskus .= " AND pp.product_id = ".CWqueryParam($product_id)."";
	}
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsGetskus, $_GET["sortby"]) !== false) {
			$rsGetskus .= " ".CWqueryGetSort($rsGetskus, $_GET["sortby"], $_GET["sortdir"], "pp.product_sort ASC, pp.product_name ASC");
		} else {
			$rsGetskus .= " ORDER BY pp.product_sort, pp.product_name";
		}
	} else {
		$rsGetskus .= " ORDER BY pp.product_sort, pp.product_name";
	}
	return CWqueryGetRS($rsGetskus);
}

// // ---------- Delete Discount Product Record ---------- // 
function CWqueryDeleteDiscountProduct($discount_id,$product_id=0) {
	$query = "DELETE FROM
		cw_discount_products
		WHERE
		 discount2product_discount_id = ".CWqueryParam($discount_id)."";
	if (strlen(trim($product_id)) && $product_id != 0) {
		$query .= " AND discount2product_product_id in(".CWqueryParam($product_id).")";
	}
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Delete Discount SKU Record ---------- // 
function CWqueryDeleteDiscountSKU($discount_id,$SKU_id=0) {
	$query = "DELETE FROM
		cw_discount_skus
		WHERE
		 discount2sku_discount_id = ".CWqueryParam($discount_id)."";
	if (strlen(trim($SKU_id)) && $SKU_id != 0) {
		$query .= " AND discount2sku_sku_id in(".CWqueryParam($SKU_id).")";
	}
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Insert Associated Product Record ---------- // 
function CWqueryInsertDiscountProduct($discount_id,$product_id) {
	$query = "INSERT INTO cw_discount_products
		(discount2product_discount_id, discount2product_product_id)
		VALUES (".CWqueryParam($discount_id).", ".CWqueryParam($product_id).")";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Insert Associated Sku Record ---------- // 
function CWqueryInsertDiscountSku($discount_id,$sku_id) {
	$query = "INSERT INTO cw_discount_skus
		(discount2sku_discount_id, discount2sku_sku_id)
		VALUES (".CWqueryParam($discount_id).", ".CWqueryParam($sku_id).")";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Archive a Discount ---------- // 
function CWqueryArchiveDiscount($discount_id) {
	// set archive = 1 
	$query = "UPDATE cw_discounts
		SET discount_archive = 1
		WHERE discount_id = ".CWqueryParam($discount_id)."";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// clear stored discount data from memory 
	unset($_ENV["application.cw"]["discountData"]);
	CWsetApplicationRefresh();
	CWinitApplication();
	CWinitRequest();
}

// // ---------- Reactivate an Archived Discount ---------- // 
function CWqueryReactivateDiscount($discount_id) {
	// set archive = 0 
	$query = "UPDATE cw_discounts
		SET discount_archive = 0
		WHERE discount_id = ".CWqueryParam($discount_id)."";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// clear stored discount data from memory 
	unset($_ENV["application.cw"]["discountData"]);
	CWsetApplicationRefresh();
	CWinitApplication();
	CWinitRequest();
}

// // ---------- Get Discount Related Categories ---------- // 
function CWquerySelectDiscountRelCategories($discount_id) {
	$rsRelCategories = "SELECT rr.discount2category_category_id, cc.category_name
		FROM
		cw_discount_categories rr,
		cw_categories_primary cc
		WHERE rr.discount2category_discount_id = ".CWqueryParam($discount_id)."
		AND cc.category_id = rr.discount2category_category_id
		AND rr.discount_category_type = 1";
	return CWqueryGetRS($rsRelCategories);
}

// // ---------- Get Discount Related Secondary Categories ---------- // 
function CWquerySelectDiscountRelSecondaries($discount_id) {
	$rsRelScndCategories = "SELECT rr.discount2category_category_id, cc.secondary_name
		FROM
		cw_discount_categories rr,
		cw_categories_secondary cc
		WHERE rr.discount2category_discount_id = ".CWqueryParam($discount_id)."
		AND cc.secondary_id = rr.discount2category_category_id
		AND rr.discount_category_type = 2";
	return CWqueryGetRS($rsRelScndCategories);
}

// // ---------- Delete Discount Category Record(s) ---------- // 
function CWqueryDeleteDiscountCat($discount_id,$category_id=0) {
	$query = "DELETE FROM cw_discount_categories
		WHERE discount2category_discount_id = ".CWqueryParam($discount_id)."";
	if ($category_id > 0) {
		$query .= " AND discount2category_category_id = ".CWqueryParam($category_id)."";
	}
	$query .= " AND discount_category_type = 1";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Insert Associated Category Record ---------- // 
function CWqueryInsertDiscountCat($discount_id,$category_id) {
	$query = "INSERT INTO cw_discount_categories
	(discount2category_discount_id, discount2category_category_id,discount_category_type )
	VALUES (".CWqueryParam($discount_id).",".CWqueryParam($category_id).",1)";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Delete Discount Secondary Category Record(s) ---------- // 
function CWqueryDeleteDiscountScndCat($discount_id,$category_id=0) {
	$query = "DELETE FROM cw_discount_categories
		WHERE discount2category_discount_id = ".CWqueryParam($discount_id)."";
	if ($category_id > 0) {
		$query .= " AND discount2category_category_id = ".CWqueryParam($category_id)."";
	}
	$query .= " AND discount_category_type = 2";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- Insert Associated Secondary Category Record ---------- // 
function CWqueryInsertDiscountScndCat($discount_id,$category_id) {
	$query = "INSERT INTO cw_discount_categories
	(discount2category_discount_id, discount2category_category_id,discount_category_type )
	VALUES (".CWqueryParam($discount_id).",".CWqueryParam($category_id).",2)";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- // Get discount usage by order ID // ---------- // 
function CWquerySelectOrderDiscounts($order_id=0) {
	$discountsQuery = "SELECT *
		FROM cw_discount_usage
		WHERE discount_usage_order_id = '".CWqueryParam(trim($order_id))."'";
	return CWqueryGetRS($discountsQuery);
}

// // ---------- // Get discount description by id // ---------- // 
function CWgetDiscountDescription($discount_id=0,$show_description=true,$show_promocode=true) {
	$discDescrip = "";
	$discQuery = CWqueryGetRS("SELECT discount_description, discount_name, discount_promotional_code, discount_show_description
		FROM cw_discounts
		WHERE discount_id = ".CWqueryParam($discount_id)."");
	// get name 
	if (isset($discQuery["discount_name"][0])) $discDescrip = $discQuery["discount_name"][0];
	// add promo code 
	if ($show_promocode && $discQuery["totalRows"] && strlen(trim($discQuery["discount_promotional_code"][0]))) {
		$discDescrip .= ' ('.$discQuery["discount_promotional_code"][0].')';
	}
	// add description 
	if ($discQuery["totalRows"] && $discQuery["discount_show_description"][0] != 0) {
		$discDescrip .= '<br><span class="CWdiscountDescription">'.$discQuery["discount_description"][0].'</span>';
	}
	return $discDescrip;
}



// /////////////// 
// CATEGORY QUERIES 
// /////////////// 



// // ---------- Get Category Details ---------- // 

function CWquerySelectCatDetails($cat_id,$cat_name=NULL) {
	$rsSelectCatDetails = "SELECT *
								FROM cw_categories_primary
								WHERE ";
	if($cat_id) {
		$rsSelectCatDetails.= "category_id = '".CWqueryParam($cat_id)."'";
	} else {
		$rsSelectCatDetails.= "".$_ENV["application.cw"]["sqlLower"]."('category_name') = '".CWqueryParam(strtolower($cat_name))."'";
	}
	return CWqueryGetRS($rsSelectCatDetails);
}



// // ---------- Get ALL active or archived categories ---------- // 

function CWquerySelectStatusCategories($cats_active=1,$doSort=false) {
	$compareTo = 1;
	$rsStatusCats = "";
	// set up opposite value so we can query with 'not' 
	if($cats_active == 1) {
		$compareTo = 0;
	}
	$rsStatusCats = "SELECT
						category_id,
						category_name,
						category_archive,
						category_sort,
						category_description,
						count(product2category_product_id) as catProdCount
						FROM cw_categories_primary
						LEFT OUTER JOIN cw_product_categories_primary
						ON cw_product_categories_primary.product2category_category_id = cw_categories_primary.category_id
						WHERE NOT category_archive =".CWqueryParam($compareTo)."
						GROUP BY
						category_id,
						category_name,
						category_archive,
						category_sort,
						category_description";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsStatusCats, $_GET["sortby"]) !== false) {
			$rsStatusCats .= " ".CWqueryGetSort($rsStatusCats, $_GET["sortby"], $_GET["sortdir"], "category_sort ASC, category_name ASC");
		} else {
			$rsStatusCats .= " ORDER BY category_sort, category_name";
		}
	} else {
		$rsStatusCats .= " ORDER BY category_sort, category_name";
	}
	return CWqueryGetRS($rsStatusCats);
}






// // ---------- Insert Category ---------- // 

function CWqueryInsertCategory($cat_name,$cat_sort=0,$cat_archive=0,$cat_description=NULL) {
	$newCatID = '';
	$dupCheck = CWquerySelectCatDetails(0,trim($cat_name));
	// if we have a duplicate, return an error message 
	if($dupCheck['totalRows']) {
		$newCatID = '0-Name';
		// if no duplicate, insert 
	} else {
	$query= "INSERT INTO cw_categories_primary (
				category_name,
				category_sort,
				category_archive,
				category_description
				) VALUES (
				'".CWqueryParam(trim($cat_name))."',
				'".CWqueryParam($cat_sort)."',
				'".CWqueryParam($cat_archive)."',
				'".CWqueryParam(trim($cat_description))."'
				)" ;
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$newCatID = mysql_insert_id();
	}
	
	return $newCatID;
}







// // ---------- Update Category ---------- // 

function CWqueryUpdateCategory($cat_id,$cat_name,$cat_sort,$cat_archive,$cat_description) {
	$updateCatID = '';
	// first look up existing category 
	$dupCheck = CWquerySelectCatDetails(0,trim($cat_name));
	// if we have a duplicate, return an error message 
	if($dupCheck['totalRows'] && !($dupCheck['category_id'] == $cat_id)) {
		$updateCatID = '0-Name';
	} else {
		$query = "UPDATE cw_categories_primary SET
				category_name = '".CWqueryParam(trim($cat_name))."',
				category_sort = '".CWqueryParam($cat_sort)."',
				category_archive = '".CWqueryParam($cat_archive)."',
				category_description = '".CWqueryParam(trim($cat_description))."'
				WHERE category_id = '".CWqueryParam($cat_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$updateCatID = $cat_id;
	}
	return $updateCatID;
}



// // ---------- Delete Category  ---------- // 

function CWqueryDeleteCategory($cat_id) {
	// delete product relationships 
	$query = "DELETE FROM cw_product_categories_primary 
					WHERE product2category_category_id = '".CWqueryParam($cat_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// delete discount relationships 
	$query = "DELETE FROM cw_discount_categories 
					WHERE discount2category_category_id = '".CWqueryParam($cat_id)."' AND discount_category_type = 1";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// delete category 
	$query = "DELETE FROM cw_categories_primary 
				WHERE category_id = '".CWqueryParam($cat_id)."'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}




// // ---------- Get Secondary Category Details ---------- // 

function CWquerySelectSecondaryCatDetails($cat_id,$cat_name=NULL) {
	$rsSelectCatDetails = "SELECT *
							FROM cw_categories_secondary
							WHERE ";
	if($cat_id) {
		$rsSelectCatDetails.= "secondary_id = '".CWqueryParam($cat_id)."'";
	} else {
		$rsSelectCatDetails.= "".$_ENV["application.cw"]["sqlLower"]."(secondary_name) = '".CWqueryParam(strtolower($cat_name))."'";
	}
	return CWqueryGetRS($rsSelectCatDetails);
}



// // ---------- Get ALL active or archived secondary categories ---------- // 

function CWquerySelectStatusSecondaryCategories($cats_active=1,$doSort=false) {
	$compareTo = 1;
	$rsStatusCats = "";
	// set up opposite value so we can query with 'not' 
	if($cats_active == 1) {
		$compareTo = 0;
	}
	$rsStatusCats = "SELECT secondary_id,
						secondary_name,
						secondary_archive,
						secondary_sort,
						secondary_description,
						count(product2secondary_product_id) as catProdCount
						FROM cw_categories_secondary
						LEFT OUTER JOIN cw_product_categories_secondary
						ON cw_product_categories_secondary.product2secondary_secondary_id = cw_categories_secondary.secondary_id
						WHERE NOT secondary_archive = '".CWqueryParam($compareTo)."'
						GROUP BY
						secondary_id,
						secondary_name,
						secondary_archive,
						secondary_sort,
						secondary_description";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsStatusCats, $_GET["sortby"]) !== false) {
			$rsStatusCats .= " ".CWqueryGetSort($rsStatusCats, $_GET["sortby"], $_GET["sortdir"], "secondary_sort ASC, secondary_name ASC");
		} else {
			$rsStatusCats .= " ORDER BY secondary_sort, secondary_name";
		}
	} else {
		$rsStatusCats .= " ORDER BY secondary_sort, secondary_name";
	}
	return CWqueryGetRS($rsStatusCats);
}




// // ---------- Insert Secondary Category ---------- // 

function CWqueryInsertSecondaryCategory($cat_name,$cat_sort=0,$cat_archive=0,$cat_description=NULL) {
	$newCatID = '';
	// first look up existing category 
	$dupCheck = CWquerySelectSecondaryCatDetails(0,trim($cat_name));
	// if we have a duplicate, return an error message 
	if($dupCheck['totalRows']) {
		$newCatID = '0-Name';
	}
	// if no duplicate, insert 
	else
	{
		$query = "INSERT INTO cw_categories_secondary (
				secondary_name,
				secondary_sort,
				secondary_archive,
				secondary_description
				) VALUES (
				'".CWqueryParam(trim($cat_name))."',
				'".CWqueryParam($cat_sort)."',
				'".CWqueryParam($cat_archive)."',
				'".CWqueryParam(trim($cat_description))."')";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		$res = mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		// get ID for return value 
		$newCatID = mysql_insert_id();	
	}
	return $newCatID;
}





// // ---------- Update Secondary Category ---------- // 

function CWqueryUpdateSecondaryCategory($cat_id,$cat_name,$cat_sort=0,$cat_archive=0,$cat_description=NULL) {// first look up existing category 
	$dupCheck = CWquerySelectSecondaryCatDetails(0,trim($cat_name));
	// if we have a duplicate, return an error message 
	if($dupCheck['totalRows'] && $dupCheck['secondary_id'][0] != $cat_id) {
		$updateCatID = '0-Name';
	// if no duplicate, insert 
	} else {
		$query = " UPDATE cw_categories_secondary SET
				secondary_name = '".CWqueryParam($cat_name)."',
				secondary_sort = '".CWqueryParam($cat_sort)."',
				secondary_archive = '".CWqueryParam($cat_archive)."',
				secondary_description = '".CWqueryParam($cat_description)."'
				WHERE secondary_id = '".CWqueryParam($cat_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		$res = mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$updateCatID = $cat_id;
	}
	return $updateCatID; 	
}





// // ---------- Delete Secondary Category  ---------- // 

function CWqueryDeleteSecondaryCategory($cat_id) {
		// delete product relationships 
	$query = "DELETE FROM cw_product_categories_secondary
				WHERE product2secondary_secondary_id = '".CWqueryParam($cat_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// delete discount relationships 
	$query = "DELETE FROM cw_discount_categories 
					WHERE discount2category_category_id = '".CWqueryParam($cat_id)."' AND discount_category_type = 2";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// delete category 
	$query = "DELETE FROM cw_categories_secondary
			WHERE secondary_id = '".CWqueryParam($cat_id)."'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}







// // ---------- Get ALL active or archived option groups ---------- // 

function CWquerySelectStatusOptionGroups($options_active=0,$doSort=false) {
	$compareTo = 1;
	$rsStatusOptionGroups = "";
	// set up opposite value so we can query with 'not' 
	if($options_active == 1) {
		$compareTo = 0;
	}
	$rsStatusOptionGroups = "SELECT optiontype_id, optiontype_name, optiontype_required, optiontype_archive, optiontype_sort, optiontype_text,
									count(product_options2prod_id) as optionProdCount
									FROM (cw_option_types
										LEFT OUTER JOIN cw_product_options
										ON cw_product_options.product_options2optiontype_id = cw_option_types.optiontype_id)
									WHERE NOT optiontype_archive = '".CWqueryParam($compareTo)."'
									AND NOT optiontype_deleted = 1
									GROUP BY optiontype_id, optiontype_name, optiontype_required, optiontype_archive, optiontype_sort, optiontype_text";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsStatusOptionGroups, $_GET["sortby"]) !== false) {
			$rsStatusOptionGroups .= " ".CWqueryGetSort($rsStatusOptionGroups, $_GET["sortby"], $_GET["sortdir"], "optiontype_sort ASC");
		} else {
			$rsStatusOptionGroups .= " ORDER BY optiontype_sort";
		}
	} else {
		$rsStatusOptionGroups .= " ORDER BY optiontype_sort";
	}
	return CWqueryGetRS($rsStatusOptionGroups);
}




// // ---------- Update Option Group ---------- // 

function CWqueryUpdateOptionGroup($optiongroup_id,$optiongroup_name,$optiongroup_sort=0,$optiongroup_archive=0,$optiongroup_text=NULL) {
	$query = "UPDATE cw_option_types SET
					optiontype_name = '".CWqueryParam(trim($optiongroup_name))."',
					optiontype_sort = '".CWqueryParam($optiongroup_sort)."',
					optiontype_archive = '".CWqueryParam($optiongroup_archive)."',
					optiontype_text = '".CWqueryParam(trim($optiongroup_text))."'
					WHERE optiontype_id = '".CWqueryParam($optiongroup_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}





// // ---------- Get Option Group Details ---------- // 

function CWquerySelectOptionGroupDetails($optiongroup_id,$optiongroup_name=NULL) {
	$rsSelectOptionGroupDetails = "SELECT  optiontype_id, optiontype_name, optiontype_required, optiontype_archive, optiontype_sort, optiontype_text,
										count(product_options2prod_id) as optionProdCount
										FROM cw_option_types
										LEFT OUTER JOIN cw_product_options
										ON cw_product_options.product_options2optiontype_id = cw_option_types.optiontype_id WHERE ";
	if($optiongroup_id) {
		$rsSelectOptionGroupDetails.= "optiontype_id ='".CWqueryParam($optiongroup_id)."' ";
	} else {
		$rsSelectOptionGroupDetails.= $_ENV["application.cw"]["sqlLower"]."(optiontype_name)='".CWqueryParam(strtolower($optiongroup_name))."'";
	}
	$rsSelectOptionGroupDetails.= " GROUP BY optiontype_id, optiontype_name, optiontype_required, 	optiontype_archive, optiontype_sort, optiontype_text";
	return CWqueryGetRS($rsSelectOptionGroupDetails);
}

// // ---------- Get Option Order SKUs ---------- // 

function CWquerySelectOptionGroupOrders($optiongroup_id,$count_orders=0) {
	$rsSelectOptionGroupOrders = '';
	// get a list of all option IDs in this group 
	$groupOptionsQuery = CWquerySelectGroupOptions($optiongroup_id);
	if (!isset($groupOptionsQuery['option_id']) || !is_array($groupOptionsQuery['option_id'])) $groupOptionsQuery['option_id'] = array();
	$optionIDlist = implode(',',$groupOptionsQuery['option_id']);
	// if there are options in this group 
	if(count($groupOptionsQuery['option_id'])) {
		// look for order skus with any id in the list 
		$rsSelectOptionGroupOrders = "SELECT
										cw_order_skus.ordersku_discount_amount,
										cw_order_skus.ordersku_id,
										cw_order_skus.ordersku_order_id,
										cw_order_skus.ordersku_quantity,
										cw_order_skus.ordersku_sku,
										cw_order_skus.ordersku_sku_total,
										cw_order_skus.ordersku_taxrate_id,
										cw_order_skus.ordersku_tax_rate,
										cw_order_skus.ordersku_unit_price,
										cw_sku_options.sku_option2option_id,
										cw_sku_options.sku_option2sku_id,
										cw_sku_options.sku_option_id
										FROM cw_order_skus, cw_sku_options
										WHERE cw_sku_options.sku_option2sku_id = cw_order_skus.ordersku_sku
										AND sku_option2option_id in('".CWqueryParam($optionIDlist)."')";
		if($count_orders) {
			$rsSelectOptionGroupOrders.= " GROUP by
											cw_order_skus.ordersku_discount_amount,
											cw_order_skus.ordersku_id,
											cw_order_skus.ordersku_order_id,
											cw_order_skus.ordersku_quantity,
											cw_order_skus.ordersku_sku,
											cw_order_skus.ordersku_sku_total,
											cw_order_skus.ordersku_taxrate_id,
											cw_order_skus.ordersku_tax_rate,
											cw_order_skus.ordersku_unit_price,
											cw_sku_options.sku_option2option_id,
											cw_sku_options.sku_option2sku_id,
											cw_sku_options.sku_option_id";
		}
	} else {
		// if there are no options  
		$rsSelectOptionGroupOrders= "SELECT
										cw_order_skus.ordersku_discount_amount,
										cw_order_skus.ordersku_id,
										cw_order_skus.ordersku_order_id,
										cw_order_skus.ordersku_quantity,
										cw_order_skus.ordersku_sku,
										cw_order_skus.ordersku_sku_total,
										cw_order_skus.ordersku_taxrate_id,
										cw_order_skus.ordersku_tax_rate,
										cw_order_skus.ordersku_unit_price,
										cw_sku_options.sku_option2option_id,
										cw_sku_options.sku_option2sku_id,
										cw_sku_options.sku_option_id
										FROM cw_order_skus, cw_sku_options
										WHERE cw_sku_options.sku_option2sku_id = cw_order_skus.ordersku_sku
										AND sku_option2option_id = 0";
	}
	return CWqueryGetRS($rsSelectOptionGroupOrders);
}


// // ---------- Get Option Details ---------- // 
function CWquerySelectOptionDetails($option_id,$option_name=NULL,$option_group=0) {
	$rsSelectOptionDetails = "SELECT * FROM cw_options WHERE ";
	if($option_id) {
		$rsSelectOptionDetails .= "option_id = '".CWqueryParam($option_id)."'";
	} else {
		$rsSelectOptionDetails .= $_ENV["application.cw"]["sqlLower"]."(option_name) = '".CWqueryParam(strtolower($option_name))."'";
	}
	if ($option_group > 0) {
		$rsSelectOptionDetails .= " AND option_type_id = ".CWqueryParam($option_group)."";
	}
	return CWqueryGetRS($rsSelectOptionDetails);
}



// // ---------- Get Options in Group ---------- // 

function CWquerySelectGroupOptions($optiongroup_id, $doSort=false) {
	$rsSelectGroupOptions = "SELECT option_id, option_type_id, option_name, option_sort, option_archive, option_text,
								count(sku_option2sku_id) as optionSkuCount
								FROM cw_options
								LEFT OUTER JOIN cw_sku_options
								ON cw_sku_options.sku_option2option_id = cw_options.option_id
								WHERE option_type_id = '".CWqueryParam($optiongroup_id)."'
								GROUP BY option_id, option_type_id, option_name, option_sort, option_archive, option_text";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsSelectGroupOptions, $_GET["sortby"]) !== false) {
			$rsSelectGroupOptions .= " ".CWqueryGetSort($rsSelectGroupOptions, $_GET["sortby"], $_GET["sortdir"], "option_sort ASC, option_name ASC");
		} else {
			$rsSelectGroupOptions .= " ORDER BY option_sort, option_name";
		}
	} else {
		$rsSelectGroupOptions .= " ORDER BY option_sort, option_name";
	}
	return CWqueryGetRS($rsSelectGroupOptions);
}

	
	
	
	
	// // ---------- Insert Option ---------- // 
function CWqueryInsertOption($option_name,$option_group,$option_sort=0,$option_text=NULL,$option_archive=0) {// first look up existing option by name 
	$dupCheck = CWquerySelectOptionDetails(0,trim($option_name),$option_group);
	// if we have a duplicate, return an error message 
	if($dupCheck['totalRows']) {
		$newOptionID = '0-Name';
	}
	// if no duplicate, insert 
	else
	{
		$query = "INSERT INTO cw_options (
				option_name,
				option_type_id,
				option_sort,
				option_text,
				option_archive
				) VALUES (
				'".CWqueryParam($option_name)."',
				'".CWqueryParam($option_group)."',
				'".CWqueryParam($option_sort)."',
				'".CWqueryParam($option_text)."',
				'".CWqueryParam($option_archive)."')";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		// get ID for return value 
		$newOptionID = mysql_insert_id();
	}
	return $newOptionID;
}


// // ---------- Update Option ---------- // 
function CWqueryUpdateOption($option_id,$option_name,$option_group,$option_sort=0,$option_archive=0,$option_text=NULL) {
	$updateOptionID ='';
	// first look up existing option by name 
	$dupCheck = CWquerySelectOptionDetails(0,trim($option_name),$option_group);
	// if we have a duplicate, return an error message 
	if($dupCheck['totalRows'] && (!$dupCheck['option_id'] == $option_id)) {
		$updateOptionID = '0-Name';
	}
	// if no duplicate, insert 
	else
	{
		$query = "UPDATE cw_options
				SET
				option_name = '".CWqueryParam($option_name)."',
				option_sort = '".CWqueryParam($option_sort)."',
				option_archive = '".CWqueryParam($option_archive)."',
				option_text = '".CWqueryParam($option_text)."'
				WHERE option_id = '".CWqueryParam($option_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$updateOptionID = $option_id;
	}
	return $updateOptionID;
}

// // ---------- Archive Option ---------- // 

function CWqueryArchiveOption($option_id,$option_archive=0) {
	$query = "UPDATE cw_options
				SET option_archive = '".CWqueryParam($option_archive)."'
				WHERE option_id = '".CWqueryParam($option_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}






// // ---------- Delete Option ---------- // 

function CWqueryDeleteOption($option_id) {
	$query = "DELETE FROM cw_options
					WHERE option_id = '".CWqueryParam($option_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}
	
	
	
	// // ---------- Insert Option Group ---------- // 

function CWqueryInsertOptionGroup($optiongroup_name,$optiongroup_sort=0,$optiongroup_text=NULL,$optiongroup_archive=0) {
	$newOptionGroupID = '0-Name';
		// first look up existing option by name 
		$dupCheck = CWquerySelectOptionGroupDetails(0,trim($optiongroup_name));
		// if we have a duplicate, return an error message
		//note: relational query with 'count' will always return one row 
		if($dupCheck['totalRows'] && $dupCheck['optiontype_id'] > 0) {
			$newOptionGroupID = '0-Name';
		}
		// if no duplicate, insert 
		else
		{
			 $query = "INSERT INTO cw_option_types(
					optiontype_name,
					optiontype_sort,
					optiontype_text,
					optiontype_archive
					) VALUES (
					'".CWqueryParam(trim($optiongroup_name))."',
					'".CWqueryParam($optiongroup_sort)."',
					'".CWqueryParam(trim($optiongroup_text))."',
					'".CWqueryParam($optiongroup_archive)."')";
			mysql_query($query);
			
			
			// get ID for return value 
			 $newOptionGroupID = mysql_insert_id();
				
			
		}
		return $newOptionGroupID;
}







// // ---------- Delete Option Group ---------- // 
function CWqueryDeleteOptionGroup($optiongroup_id) {
	// get a list of all option IDs in this group 
	$groupOptionsQuery = CWquerySelectGroupOptions($optiongroup_id);
	// get all orderSKUs related to this option group 
	$optionOrdersQuery = CWquerySelectOptionGroupOrders($optiongroup_id,0);	
	if (!isset($optionOrdersQuery['sku_option2option_id']) || !is_array($optionOrdersQuery['sku_option2option_id'])) $optionOrdersQuery['sku_option2option_id'] = array();
	$optionOrders = implode(',',$optionOrdersQuery['sku_option2option_id']);
	// lists for counting 
	$deletedCt = 0;
	$archivedCt = 0;
	$relatedCt = 0;
	$archivedOption = '';
	$deleteOption = '';
	$checkOptionsQuery = '';
	// loop through the option IDs 
	for($i=0;$i<$groupOptionsQuery['totalRows'];$i++) {
		// if the option has no skus it can be archived, and/or deleted
		if($groupOptionsQuery['optionSkuCount'][$i] > 1) {
			// if not already archived, archive the option 
			if($groupOptionsQuery['option_archive'][$i] != 1) {
				// archive option record (id,archive) 
				$archiveOption = CWqueryArchiveOption($option_id,1);
				
			}
			// /END if not already archived 	
			// if the option has no orders, it can be deleted 
			// check for the option ID in the list 
			if(!ListFind($optionOrders,$option_id)) {
				// delete option record (id) 
				$deleteOption = CWqueryDeleteOption($option_id,1);
				// count as deleted 
				$deletedCt = $deletedCt + 1;
			} else {
				// if not deleted, count as archived 
				$archivedCt = $archivedCt + 1;
			}
			// /END delete (no orders) 
		} else {
			// if this option has skus, it cannot be deleted 
			// count as related 
			$relatedCt = $relatedCt + 1;
		}
	}
	// check for any remaining options 
	$checkOptionsQuery = CWquerySelectGroupOptions($optiongroup_id);
	// at this point, all unattached options have been deleted, or archived 
	// if we still have some options, but no active products attached, mark the group 'deleted' in database 
	if($checkOptionsQuery['totalRows'] > 0 && $relatedCt == 0) {
		// mark deleted, do not remove record 
		$query = "UPDATE cw_option_types
					SET optiontype_archive = 1,
					optiontype_deleted = 1
					WHERE optiontype_id = ".CWqueryParam($optiongroup_id)."";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);			
	}
	elseif($checkOptionsQuery['totalRows']==0) {
		// else if all the options were deleted, delete the group 
		// delete, removing record from database
		$query	=	"DELETE from cw_option_types
					WHERE optiontype_id = ".CWqueryParam($optiongroup_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	}	
	// /END if options still exist 
}








// // ---------- Get Active or Archived Shipping Methods ---------- // 

function CWquerySelectStatusShipMethods($records_active=1) {
	$compareTo = 1;
	$rsStatusShipping = '';
	// set up opposite value so we can query with 'not' 
	if($records_active == 1) {
		$compareTo = 0;
	}
	$rsStatusShipping = "SELECT cw_ship_methods.*,
							cw_countries.country_name,
							cw_countries.country_id
							FROM (cw_ship_methods
								INNER JOIN cw_ship_method_countries
								ON cw_ship_methods.ship_method_id = cw_ship_method_countries.ship_method_country_method_id)
									INNER JOIN cw_countries
									ON cw_ship_method_countries.ship_method_country_country_id = cw_countries.country_id
							WHERE NOT ship_method_archive = '".CWqueryParam($compareTo)."'
							ORDER BY cw_countries.country_sort, cw_countries.country_name,
							cw_ship_methods.ship_method_sort, cw_ship_methods.ship_method_name";
	return CWqueryGetRS($rsStatusShipping);
}






// // ---------- Get Shipping Method Details ---------- // 

function CWquerySelectShippingMethodDetails($method_id) {
	$rsShipMethodDetails = '';
		$rsShipMethodDetails = "";
		return;
}



// // ---------- Insert Ship Method ---------- // 

function CWqueryInsertShippingMethod($record_name,$country_id,$record_rate=0,$record_sort=0,$record_calctype="localcacl",$record_archive=0) {
	$NewID = "";
	$getNewID = "";
	try
	{
		$query = "INSERT INTO cw_ship_methods
					(
					ship_method_sort,
					ship_method_name,
					ship_method_rate,
					ship_method_calctype,
					ship_method_archive
					)
					VALUES
					(
					'".CWqueryParam($record_sort)."',
					'".CWqueryParam($record_name)."',
					'".CWqueryParam($record_rate)."',
					'".CWqueryParam($record_calctype)."',
					0
					)";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$getNewID = '';
		// get the new method id we just added 
		$getNewID = "SELECT ship_method_id AS newID
							FROM cw_ship_methods
							ORDER BY ship_method_id DESC";
		$result = CWqueryGetRS($getNewID);
		//return $result;	
		$newID = $getNewID['newID']; 
		// now add country / method relationship 
		$query = "INSERT INTO
				cw_ship_method_countries
				(
				ship_method_country_method_id,
				ship_method_country_country_id
				)
				VALUES (
				'".CWqueryParam($result['newID'][0])."',
				'".CWqueryParam($country_id)."'
				) ";
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	}
	catch(Exception $e) {
		$newID = '0-Error: ' . $e->getMessage();
	}
	return $newID;
}




// // ---------- Update Ship Method ---------- // 

function CWqueryUpdateShippingMethod($record_id,$record_name,$record_rate=0,$record_sort=0,$record_calctype=NULL,$record_archive=0) {if($record_archive =='')
	$record_archive=0;
	$query = "UPDATE cw_ship_methods
			SET
			ship_method_name = '".CWqueryParam($record_name)."',
			ship_method_rate = '".CWqueryParam($record_rate)."',
			ship_method_archive = '".CWqueryParam($record_archive)."',";
	if($record_calctype != '') {
		$query.= "ship_method_calctype = '".CWqueryParam($record_calctype)."',";
	}
	$query.= "ship_method_sort = '".CWqueryParam($record_sort)."'
			WHERE ship_method_id = '".CWqueryParam($record_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}



// // ---------- Delete Ship Method ---------- // 

function CWqueryDeleteShippingMethod($record_id) {// DELETE Country Relationship 
	$query = "DELETE FROM cw_ship_method_countries
			WHERE ship_method_country_method_id = '".CWqueryParam($record_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// DELETE Method 
	$query = "DELETE FROM cw_ship_methods
			WHERE ship_method_id = '".CWqueryParam($record_id)."'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}



// // ---------- Get All Shipping Methods by country ---------- // 

function CWquerySelectShippingMethods($country_id=0,$record_archive=0) {
	$rsGetMethods = "SELECT
						cw_ship_methods.ship_method_name,
						cw_ship_methods.ship_method_id,
						cw_ship_methods.ship_method_calctype,
						cw_countries.country_name,
						cw_countries.country_id
						FROM (cw_ship_methods
						INNER JOIN cw_ship_method_countries
						ON cw_ship_methods.ship_method_id = cw_ship_method_countries.ship_method_country_method_id)
						INNER JOIN cw_countries
						ON cw_ship_method_countries.ship_method_country_country_id = cw_countries.country_id
						 WHERE 1=1 ";
	if($record_archive != 2) {
		$rsGetMethods.= " AND ship_method_archive = '".CWqueryParam($record_archive)."'";
	}
	if($country_id > 0) {
		$rsGetMethods.= " AND cw_ship_method_countries.ship_method_country_country_id = '".CWqueryParam($country_id)."'";
	}
	$rsGetMethods.= " ORDER BY
						cw_countries.country_sort,
						cw_countries.country_name,
						cw_ship_methods.ship_method_sort,
						cw_ship_methods.ship_method_name";
	return CWqueryGetRS($rsGetMethods);
}




// // ---------- Get Orders with a specific Ship Method ---------- // 

function CWquerySelectShippingMethodOrders($method_id) {
	$rsShipMethodOrders = "SELECT order_id
								FROM cw_orders
								WHERE order_ship_method_id = '".CWqueryParam($method_id)."'";
	return CWqueryGetRS($rsShipMethodOrders);
}





// // ---------- Get Shipping Ranges by country ---------- // 

function CWquerySelectShippingCountryRanges($country_id=0,$record_archive=0) {
	$rsShipRanges = "SELECT
						cw_ship_ranges.*,
						cw_ship_methods.ship_method_name,
						cw_ship_methods.ship_method_id,
						cw_ship_methods.ship_method_calctype,
						cw_countries.country_name
						FROM cw_ship_ranges
							INNER JOIN
							((cw_ship_method_countries
							INNER JOIN cw_countries
								ON cw_ship_method_countries.ship_method_country_country_id = cw_countries.country_id)
							INNER JOIN cw_ship_methods
								ON cw_ship_method_countries.ship_method_country_method_id = cw_ship_methods.ship_method_id)
								ON cw_ship_ranges.ship_range_method_id = cw_ship_methods.ship_method_id
								WHERE 1=1";
	if($record_archive != 2) {
		$rsShipRanges.= " AND cw_ship_methods.ship_method_archive = '".CWqueryParam($record_archive)."'";
	}
	if($country_id > 0) {
		$rsShipRanges.= " AND cw_ship_method_countries.ship_method_country_country_id ='".CWqueryParam($country_id)."'";
	}
	$rsShipRanges.= " ORDER BY
						cw_countries.country_sort,
						cw_countries.country_name,
						cw_ship_methods.ship_method_sort,
						cw_ship_methods.ship_method_name,
						cw_ship_ranges.ship_range_from,
						cw_ship_ranges.ship_range_to";
	return CWqueryGetRS($rsShipRanges);
}







// // ---------- Insert Ship Range ---------- // 

function CWqueryInsertShippingRange($method_id,$range_from,$range_to,$range_amount) {
	$query = "INSERT INTO cw_ship_ranges
				(ship_range_method_id,
				ship_range_from,
				ship_range_to,
				ship_range_amount
				) VALUES (
				'".CWqueryParam($method_id)."',
				'".CWqueryParam($range_from)."',
				'".CWqueryParam($range_to)."',
				'".CWqueryParam(CWsqlNumber($range_amount))."'
				)";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}




// // ---------- Update Ship Range ---------- // 

function CWqueryUpdateShippingRange($range_id,$range_from,$range_to,$range_amount) {
	$query = "UPDATE cw_ship_ranges
				SET
				ship_range_from = '".CWqueryParam($range_from)."',
				ship_range_to = '".CWqueryParam($range_to)."',
				ship_range_amount = '".CWqueryParam(CWsqlNumber($range_amount))."'
				    WHERE ship_range_id = '".CWqueryParam($range_id)."'
				";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}




// // ---------- Delete Ship Range ---------- // 

function CWqueryDeleteShippingRange($record_id) {
		// DELETE ship range 
	$query = "DELETE FROM cw_ship_ranges
				WHERE ship_range_id = '".CWqueryParam($record_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}





// // ---------- Get Shipping Ranges with a specific Ship Method ---------- // 

function CWquerySelectShippingMethodRanges($method_id) {
	$rsShipMethodRanges = "SELECT ship_range_id
								FROM cw_ship_ranges
								WHERE ship_range_method_id = '".CWqueryParam($method_id)."'";
	return CWqueryGetRS($rsShipMethodRanges);
}




// // ---------- Update StateProv Shipping Extension ---------- // 

function CWqueryUpdateShippingExtension($update_id,$ship_ext,$tax_ext) {
	$query = "UPDATE cw_stateprov
				SET stateprov_ship_ext = '".CWqueryParam(CWsqlNumber($ship_ext))."'";
	if($tax_ext >= 0) {
		$query.= ", stateprov_tax = '".CWqueryParam(CWsqlNumber($tax_ext))."'";
	}
	$query.= " WHERE stateprov_id = '".CWqueryParam($update_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}





// // ---------- Get Tax Rates by region ---------- // 

function CWquerySelectTaxRegionRates($group_id,$doSort=false) {
	$rsTaxRates = "SELECT
						cw_tax_regions.tax_region_id,
						cw_tax_regions.tax_region_label,
						cw_tax_rates.tax_rate_region_id,
						cw_stateprov.stateprov_name,
						cw_countries.country_name,";
	if($_ENV["application.cw"]["appDbType"] == 'mysql') {
		$rsTaxRates.= "CONCAT(cw_stateprov.stateprov_name,' : ',cw_countries.country_name) AS region_location,";
	} else {
		$rsTaxRates.= "cw_stateprov.stateprov_name + ' : ' + cw_countries.country_name AS region_location,";
	}
	$rsTaxRates.= " cw_tax_rates.tax_rate_id,
						cw_tax_rates.tax_rate_percentage
						FROM (cw_stateprov
							RIGHT JOIN (cw_countries
							RIGHT JOIN cw_tax_regions ON cw_countries.country_id = cw_tax_regions.tax_region_country_id)
							ON cw_stateprov.stateprov_id = cw_tax_regions.tax_region_state_id)
							INNER JOIN cw_tax_rates
							ON cw_tax_regions.tax_region_id = cw_tax_rates.tax_rate_region_id
						WHERE cw_tax_rates.tax_rate_group_id = '".CWqueryParam($group_id)."'";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsTaxRates, $_GET["sortby"]) !== false) {
			$rsTaxRates .= " ".CWqueryGetSort($rsTaxRates, $_GET["sortby"], $_GET["sortdir"], "tax_region_id ASC");
		} else {
			$rsTaxRates .= " ORDER BY tax_region_id";
		}
	} else {
		$rsTaxRates .= " ORDER BY tax_region_id";
	}
	return CWqueryGetRS($rsTaxRates);
}







function CWquerySelectTaxGroupRates($region_id,$doSort=false) {
	$rsTaxRates = "SELECT cw_tax_rates.tax_rate_id,
						cw_tax_rates.tax_rate_percentage,
						cw_tax_groups.tax_group_name,
						cw_tax_groups.tax_group_id
						FROM cw_tax_groups, cw_tax_rates
						WHERE cw_tax_groups.tax_group_id = cw_tax_rates.tax_rate_group_id
						AND cw_tax_rates.tax_rate_region_id = '".$region_id."'";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsTaxRates, $_GET["sortby"]) !== false) {
			$rsTaxRates .= " ".CWqueryGetSort($rsTaxRates, $_GET["sortby"], $_GET["sortdir"], "tax_group_name ASC");
		} else {
			$rsTaxRates .= " ORDER BY tax_group_name";
		}
	} else {
		$rsTaxRates .= " ORDER BY tax_group_name";
	}
	return CWqueryGetRS($rsTaxRates);
}






// // ---------- Get Tax Regions with StateProv/Country information ---------- // 
function CWquerySelectTaxRegions($region_id,$omit_list=0,$region_name=NULL,$doSort=false) {
	$rsTaxRegions = "SELECT
					cw_tax_regions.*,
					cw_stateprov.stateprov_name,
					cw_countries.country_name,";
					if($_ENV["application.cw"]["appDbType"] == 'mysql') {
						$rsTaxRegions.= " CONCAT(cw_stateprov.stateprov_name,' : ',cw_countries.country_name) AS region_location,";
					} else {
						$rsTaxRegions.= " cw_stateprov.stateprov_name + ' : ' + cw_countries.country_name AS region_location,";
					}
	$rsTaxRegions.= "cw_tax_groups.tax_group_name,
						cw_tax_groups.tax_group_id
						FROM (((cw_stateprov
								RIGHT JOIN cw_tax_regions
								ON cw_stateprov.stateprov_id = cw_tax_regions.tax_region_state_id)
						
								RIGHT JOIN cw_countries
								ON cw_countries.country_id = cw_tax_regions.tax_region_country_id)
						
								LEFT JOIN cw_tax_groups
								ON cw_tax_regions.tax_region_ship_tax_group_id = cw_tax_groups.tax_group_id)";
	// omit any specified IDs 
	$rsTaxRegions.= " WHERE tax_region_id NOT IN ('".CWqueryParam($omit_list)."')";
	// match ID 
	if($region_id != 0) {
		$rsTaxRegions.= " AND tax_region_id = '".CWqueryParam($region_id)."'";
	}
	// match Name 
	if(strlen(trim($region_name))) {
		$rsTaxRegions.= " AND tax_region_label = '".CWqueryParam($region_name)."'";
	}
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsTaxRegions, $_GET["sortby"]) !== false) {
			$rsTaxRegions .= " ".CWqueryGetSort($rsTaxRegions, $_GET["sortby"], $_GET["sortdir"], "cw_countries.country_name ASC, cw_stateprov.stateprov_name ASC");
		} else {
			$rsTaxRegions .= " ORDER BY cw_countries.country_name, cw_stateprov.stateprov_name";
		}
	} else {
		$rsTaxRegions .= " ORDER BY cw_countries.country_name, cw_stateprov.stateprov_name";
	}
	return CWqueryGetRS($rsTaxRegions);
}





// // ---------- Insert Tax Region ---------- // 
function CWqueryInsertTaxRegion($region_name,$country_id=0,$state_id=0,$tax_id=0,$show_tax_id=false,$ship_tax_method=NULL,$ship_tax_group=0) {
	$getNewRecordID = '';
	$newRecordID = '';
	// first look up existing tax region 
	$dupCheck = CWquerySelectTaxRegions(0,0,trim($region_name));
	// if we have a duplicate, return an error message 
	
	if($dupCheck['totalRows'] != 0) {
		
		$newRecordID = "0-Name '".$region_name."' already exists";
	} else {
		$query = "INSERT INTO cw_tax_regions (
					tax_region_label,
					tax_region_country_id,
					tax_region_state_id,
					tax_region_tax_id,
					tax_region_show_id,
					tax_region_ship_tax_method,
					tax_region_ship_tax_group_id
					) VALUES (
					'".CWqueryParam($region_name)."',
					'".CWqueryParam($country_id)."',
					'".CWqueryParam($state_id)."',
					'".CWqueryParam($tax_id)."',
					'".CWqueryParam($show_tax_id)."',
					'".CWqueryParam($ship_tax_method)."',
					'".CWqueryParam($ship_tax_group)."'
					)";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		// get ID for return value 
		$newRecordID = mysql_insert_id();
	}
	return $newRecordID;
}




// // ---------- Update Tax Region ---------- // 

function CWqueryUpdateTaxRegion($region_id,$tax_label=NULL,$tax_id=NULL,$show_tax_id=NULL,$tax_method=NULL,$tax_group_id=0) {
	$updatedID = '';
	// first look up existing tax region 
	$dupCheck = CWquerySelectTaxRegions(0,$region_id,trim($tax_label));
	// if we have a duplicate, return an error message 
	if($dupCheck['totalRows']) {
		$updatedID = "0-Name '".$tax_label."' already exists";
	} else {
	  $query = "UPDATE cw_tax_regions
					SET
					tax_region_label = '".CWqueryParam($tax_label)."',
					tax_region_tax_id = '".CWqueryParam($tax_id)."',
					tax_region_show_id = '".CWqueryParam($show_tax_id)."',
					tax_region_ship_tax_method = '".CWqueryParam($tax_method)."',
					tax_region_ship_tax_group_id = '".CWqueryParam($tax_group_id)."'
					WHERE tax_region_id = '".CWqueryParam($region_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$updatedID = $region_id;
	}
	return $updatedID;
}	



// // ---------- Delete Tax Region ---------- // 

function CWqueryDeleteTaxRegion($record_id=0,$state_id=0,$country_id=0) {
	$query = "DELETE FROM cw_tax_regions
				WHERE 1=1";
	if($record_id > 0) {
		$query.= " AND tax_region_id = '".CWqueryParam($record_id)."'";
	}
	if($state_id > 0) {
		$query.= " AND tax_region_state_id = '".CWqueryParam($state_id)."'";
	}
	if($country_id > 0) {
		$query.= " AND tax_region_country_id = '".CWqueryParam($country_id)."'";
	}
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- List All Tax Groups ---------- // 
function CWquerySelectTaxGroups($show_archived=false,$group_id=0) {
	$rsTaxGroups = "SELECT *
		FROM cw_tax_groups
		WHERE ";
	if (!$show_archived) {
		$rsTaxGroups .= "NOT";
	}
	$rsTaxGroups .= " tax_group_archive = 1";
	if ($group_id > 0) {
		$rsTaxGroups .= " AND tax_group_id = ".CWqueryParam($group_id)."";
	}
	$rsTaxGroups .= " ORDER BY tax_group_name ASC";
	return CWqueryGetRS($rsTaxGroups);
}

// // ---------- Get Products related to a tax group ---------- // 

function CWquerySelectTaxGroupProducts($group_id,$doSort=false) {
	$rsProductTaxGroups = "SELECT product_tax_group_id, product_name, product_id
								FROM cw_products
								WHERE product_tax_group_id = '".CWqueryParam($group_id)."'";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsProductTaxGroups, $_GET["sortby"]) !== false) {
			$rsProductTaxGroups .= " ".CWqueryGetSort($rsProductTaxGroups, $_GET["sortby"], $_GET["sortdir"], "product_tax_group_id ASC");
		} else {
			$rsProductTaxGroups .= " ORDER BY product_tax_group_id";
		}
	} else {
		$rsProductTaxGroups .= " ORDER BY product_tax_group_id";
	}
	return CWqueryGetRS($rsProductTaxGroups);
}


// // ---------- Get Tax Group details by id or name ---------- // 

function CWquerySelectTaxGroupDetails($group_id,$omit_list=0,$group_name=NULL) {
	$rsTaxGroup = "SELECT *
				FROM cw_tax_groups
				WHERE tax_group_id NOT IN ('".$omit_list."')";
	if($group_id > 0) {
		$rsTaxGroup.= "AND tax_group_id = '".$group_id."'";
	}
	if(strlen(trim($group_name))) {
		$rsTaxGroup.= "AND tax_group_name = '".$group_name."'";
	}
	return CWqueryGetRS($rsTaxGroup);
}






function CWqueryInsertTaxGroup($group_name,$group_archive=0,$group_code="00000") {
	$newRecordID = '';
	$getNewRecordID = '';
	// first look up existing tax group 
	$dupCheck = CWquerySelectTaxGroupDetails(0,0,trim($group_name));
	// if we have a duplicate, return an error message 
	if($dupCheck['totalRows']) {
		$newRecordID = "0-Name '".$group_name."'' already exists";
	} else {
		$query = "INSERT INTO cw_tax_groups (
				tax_group_name,
				tax_group_archive,
				tax_group_code
				)
				VALUES
				(
				'".CWqueryParam(trim($group_name))."',
				".CWqueryParam($group_archive).",
				'".CWqueryParam(trim($group_code))."'
				)";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		// get ID for return value 
		$newRecordID = mysql_insert_id();
	}
	return $newRecordID;
}





// // ---------- Delete Tax Group ---------- // 

function CWqueryDeleteTaxGroup($record_id) {
	// DELETE tax group 
	$query = "DELETE FROM cw_tax_groups
				WHERE tax_group_id = '".CWqueryParam($record_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}






// // ---------- Update Tax Group ---------- // 
function CWqueryUpdateTaxGroup($record_id,$group_archive=0,$group_name=null,$group_code=null) {
	if ($group_name === null) $group_name = "";
	if ($group_code === null) $group_code = "";
	$updatedID = '';
	if(strlen(trim($group_name))) {
		// check for duplicates 
		$dupCheck = CWquerySelectTaxGroupDetails(0,$record_id,trim($group_name));
		// if we have a duplicate, return an error message 
		if($dupCheck['totalRows']) {
			$updatedID = '0-Name';
		}
	}
	if(!strlen(trim($updatedID))) {
		// if no duplicate, insert 
		$query = "UPDATE cw_tax_groups
				SET tax_group_archive = '".CWqueryParam($group_archive)."'";
		// if group name provided 
		if(strlen(trim($group_name))) {
			$query.= ",tax_group_name = '".CWqueryParam(trim($group_name))."'";
		}
		// if group code provided 
		if(strlen(trim($group_code))) {
			$query.= ",tax_group_code = '".CWqueryParam(trim($group_code))."'";
		}
		$query.= " WHERE tax_group_id = '".CWqueryParam($record_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$updatedID = $record_id;
	}
	return $updatedID;
}



// // ---------- Insert Tax rate ---------- // 

function CWqueryInsertTaxRate($region_id,$group_id,$tax_rate=0) {
	$newID = '';
	$query = "INSERT INTO cw_tax_rates (
			tax_rate_region_id,
			tax_rate_group_id,
			tax_rate_percentage
			)
			VALUES
			(
			'".CWqueryParam($region_id)."',
			'".CWqueryParam($group_id)."',
			'".CWqueryParam($tax_rate)."'
			)";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// get the new ID 
	$newID = mysql_insert_id();
	return $newID;
}



// // ---------- Update Tax rate ---------- // 

function CWqueryUpdateTaxRate($record_id,$tax_rate=0) {
	$query = "UPDATE cw_tax_rates
				SET
				tax_rate_percentage = '".CWqueryParam($tax_rate)."'
				WHERE tax_rate_id = '".CWqueryParam($record_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}




// // ---------- Delete Tax rate ---------- // 
function CWqueryDeleteTaxRate($record_id) {
	$query = "DELETE FROM cw_tax_rates
				WHERE tax_rate_id = '".CWqueryParam($record_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
} 



// // ---------- Update Product Tax Group ---------- // 

function CWqueryUpdateProductTaxGroup($record_id,$group_id) {
	$query = "UPDATE cw_products
				SET product_tax_group_id = '".CWqueryParam($group_id)."'
				WHERE product_id = '".CWqueryParam($record_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- // Set mandatory settings when not using localtax // ---------- // 
function CWsetNonLocalTaxOptions() {
	$query = "UPDATE cw_config_items
				SET config_value = 'Groups'
				WHERE config_variable = 'taxSystem'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	$query = "UPDATE cw_config_items
				SET config_value = 'shipping'
				WHERE config_variable = 'taxChargeBasedOn'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	$query = "UPDATE cw_config_items
				SET config_value = 'false'
				WHERE config_variable = 'taxUseDefaultCountry'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	$query = "UPDATE cw_config_items
				SET config_value = 'false'
				WHERE config_variable = 'taxDisplayOnProduct'";
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}




// /////////////////// 
// COUNTRY / REGION QUERIES
// /////////////////// 

// // ---------- Get ALL State/Provs ---------- // 

function CWquerySelectStates($country_id=0,$doSort=false) {
	$rsGetStateList = "SELECT
							cw_countries.*,
							cw_stateprov.*
							FROM cw_countries, cw_stateprov
							WHERE cw_countries.country_id = cw_stateprov.stateprov_country_id
							AND
							cw_stateprov.stateprov_archive = 0
								AND cw_countries.country_archive = 0";
	if($country_id > 0) {
		$rsGetStateList.= " AND cw_countries.country_id = '".CWqueryParam($country_id)."'";
	}
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsGetStateList, $_GET["sortby"]) !== false) {
			$rsGetStateList .= " ".CWqueryGetSort($rsGetStateList, $_GET["sortby"], $_GET["sortdir"], "cw_countries.country_sort ASC,
							cw_countries.country_id ASC,
							cw_stateprov.stateprov_name ASC");
		} else {
			$rsGetStateList .= " ORDER BY
							cw_countries.country_sort,
							cw_countries.country_name,
							cw_countries.country_id,
							cw_stateprov.stateprov_name";
		}
	} else {
		$rsGetStateList .= " ORDER BY
							cw_countries.country_sort,
							cw_countries.country_name,
							cw_countries.country_id,
							cw_stateprov.stateprov_name";
	}
	return CWqueryGetRS($rsGetStateList);
}





// // ---------- Get ALL Countries ---------- // 

function CWquerySelectCountries($show_archived=1) {
	$rsGetCountryList = "SELECT country_id, country_name
						FROM cw_countries";
	if($show_archived == 0) {
		$rsGetCountryList.= " WHERE NOT country_archive = 1";
	}
	$rsGetCountryList.= " ORDER BY country_name";
	return CWqueryGetRS($rsGetCountryList);
}




// // ---------- Get ALL State/Provs by country ---------- // 

function CWquerySelectCountryStates($states_archived=0) {
	$rsCountryStatesList = "SELECT
								cw_countries.*,
								cw_stateprov.*
								FROM cw_countries
								LEFT JOIN cw_stateprov
								ON cw_countries.country_id = cw_stateprov.stateprov_country_id where 1=1";
	if($states_archived != 2) {
		$rsCountryStatesList.= " and cw_countries.country_archive = '".CWqueryParam($states_archived).  "'";
	}
	$rsCountryStatesList.= " ORDER BY country_sort, country_name, stateprov_name ";
	return CWqueryGetRS($rsCountryStatesList);
}





// // ---------- Get Country IDs for user defined states ---------- // 

function CWquerySelectStateCountryIDs() {
	$rsGetStateCountryIDs = "SELECT DISTINCT stateprov_country_id
								FROM cw_stateprov
								WHERE NOT ".$_ENV["application.cw"]["sqlLower"]."(stateprov_name) in('none','all')";
	return CWqueryGetRS($rsGetStateCountryIDs);
}



// // ---------- Get Customer Regions ---------- // 

function CWquerySelectCustomerCountries() {
	$rsSelectCustomerCountries = "SELECT DISTINCT
									c.customer_state_stateprov_id,
									s.stateprov_country_id
									FROM cw_customer_stateprov c, cw_stateprov s
									WHERE s.stateprov_id = c.customer_state_stateprov_id";
	return CWqueryGetRS($rsSelectCustomerCountries);
}



// // ---------- Get ship methods with orders ---------- // 

function CWquerySelectOrderShipMethods() {
	$rsUsedShippingMethodList = "SELECT DISTINCT order_ship_method_id
									FROM cw_orders";
	return CWqueryGetRS($rsUsedShippingMethodList);
}



// // ---------- Get states with customer address matches ---------- // 
function CWquerySelectCustomerStates() {
	$rsUsedStatesList = "SELECT DISTINCT customer_state_stateprov_id
							FROM cw_customer_stateprov";
	return CWqueryGetRS($rsUsedStatesList);
}





// // ---------- Get country shipping methods with orders attached ---------- // 

function CWquerySelectShipCountries($omit_list=0) {
	$rsUsedCountryList = "SELECT DISTINCT ship_method_country_country_id
							FROM cw_ship_method_countries
							WHERE ship_method_country_method_id
							NOT IN ('".CWqueryParam($omit_list)."')";
	return CWqueryGetRS($rsUsedCountryList);
}





// // ---------- Get Country Details ---------- // 

function CWquerySelectCountryDetails($country_id,$country_name,$country_code,$omit_list='') {
	// look up country 
	$rsSelectCountry = "SELECT *
						FROM cw_countries
						WHERE 1 = 1";
	if($country_id && $country_id > 0) {
		$rsSelectCountry.= " AND country_id = '".CWqueryParam($country_id)."'";
	}
	if(strlen(trim($country_name)) > 0) {
		$rsSelectCountry.= " AND country_name = '".CWqueryParam($country_name)."'";
	}
	if(strlen(trim($country_code)) > 0) {
		$rsSelectCountry.= " AND country_code = '".CWqueryParam($country_code)."'";
	}
	if(strlen(trim($omit_list)) > 0 && $omit_list != 0) {
		$rsSelectCountry.= " AND NOT country_id in('".CWqueryParam($omit_list)."')";
	}
	return CWqueryGetRS($rsSelectCountry);
}





// // ---------- Insert Country ---------- // 

function CWqueryInsertCountry($country_name,$country_code,$country_sort=0,$country_archive=0,$country_default=0) {
	$newRecordID = '';
	$getNewID = '';
	$errorMsg = '';
	// check for duplicates by name 
	$dupNameCheck = CWquerySelectCountryDetails(0,$country_name,'');
	// if we have a duplicate, return an error message 
	if($dupNameCheck['totalRows']) {
		$errorMsg = $errorMsg . "<br>Country Name '".$country_name."' already exists";
	}
	// check for duplicates by code 
	$dupCodeCheck = CWquerySelectCountryDetails(0,'',$country_code);
	// if we have a duplicate, return an error message 
	if($dupCodeCheck['totalRows']) {
		$errorMsg = $errorMsg . "<br>Code '".$country_code."' already exists";
	}
	// if no duplicate, insert 
	if(!strlen(trim($errorMsg))) {
		$query = "INSERT INTO cw_countries
				(
				country_name,
				country_code,
				country_sort,
				country_archive,
				country_default_country
				)
				VALUES (
				'".CWqueryParam($country_name)."',
				'".CWqueryParam($country_code)."',
				'".CWqueryParam($country_sort)."',
				'".CWqueryParam($country_archive)."',
				'".CWqueryParam($country_default)."')";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		// Get the new ID 
		$newRecordID = mysql_insert_id();
	}
	// if we did have a duplicate, return error code 
	else
	{
		$newRecordID = "0-Error: '".$errorMsg."'";
	}
	return $newRecordID;		
}




// // ---------- Update Country ---------- // 
function CWqueryUpdateCountry($country_id,$country_name="",$country_code,$country_archive=2,$country_sort=0,$country_default=0) {
	$errorMsg = '';
	$updatedID = '';
	// check for duplicates by name 
	$dupNameCheck = CWquerySelectCountryDetails(0,$country_name,'',$country_id);
	// if we have a duplicate, return an error message 
	if($dupNameCheck['totalRows']) {
		$errorMsg = $errorMsg . "<br>Country Name '".$country_name."' already exists";
	}
	// check for duplicates by code 
	$dupCodeCheck = CWquerySelectCountryDetails(0,'',$country_code,$country_id);
	// if we have a duplicate, return an error message 
	if($dupCodeCheck['totalRows']) {
		$errorMsg = $errorMsg & "<br>Code '".$country_code."' already exists";
	}
	// if no duplicate, insert 
	if(!strlen(trim($errorMsg))) {
		$query = "UPDATE cw_countries
			SET country_id = country_id";
		if(strlen(trim($country_name))) {
			$query.= ",country_name = '".CWqueryParam(trim($country_name))."'";
		}
		if(strlen(trim($country_code))) {
			$query.= ",country_code = '".CWqueryParam(trim($country_code))."'";
		}
		if($country_archive != 2) {
			$query.= ",country_archive = '".CWqueryParam($country_archive)."'";
		}
		if($country_sort != 0) {
			$query.= ",country_sort = '".CWqueryParam($country_sort)."'";
		}
		$query.= ",country_default_country = '".CWqueryParam($country_default)."'
					WHERE country_id = '".CWqueryParam($country_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$updatedID = $country_id;
		if ($country_default != 0) {
			$query = "UPDATE cw_countries SET country_default_country=0 WHERE NOT country_id = '".$country_id."'";
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		}
	} else {
		// if error message, return string 
		$updatedID = "0-Error: '".$errorMsg."'";
	}// /end if error message 
	return $updatedID;
}






		//- Delete Country ---------- // 

function CWqueryDeleteCountry($country_id) {
	$query = "DELETE FROM cw_countries
				WHERE country_id = '".CWqueryParam($country_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}





// // ---------- Get State/Prov Details ---------- // 

function CWquerySelectStateProvDetails($stateprov_id,$stateprov_name="",$stateprov_code="",$country_id=0,$omit_list='') {
	// look up stateprov 
	$rsSelectStateProv = "SELECT *
							FROM cw_stateprov
							WHERE 1 = 1";
	if($stateprov_id > 0) {
		$rsSelectStateProv.= " AND stateprov_id = '".CWqueryParam($stateprov_id)."'";
	}
	if(strlen($stateprov_name) > 0) {
			$rsSelectStateProv.= " AND stateprov_name = '".CWqueryParam($stateprov_name)."'";
	}
	if(strlen($stateprov_code) > 0) {
			$rsSelectStateProv.= " AND stateprov_code = '".CWqueryParam($stateprov_code)."'";
	}
	if($country_id > 0) {
		$rsSelectStateProv.= " AND stateprov_country_id = '".CWqueryParam($country_id)."'";
	}
	if(trim($omit_list) != 0) {
		$rsSelectStateProv.= " AND NOT stateprov_id in('".CWqueryParam($omit_list)."')";
	}
	return CWqueryGetRS($rsSelectStateProv);		
}




// // ---------- Insert State/Prov ---------- // 

function CWqueryInsertStateProv($stateprov_name,$stateprov_code,$country_id,$stateprov_archive=0,$stateprov_tax=0,$stateprov_ship_ext=0) {
	$newRecordID = '';
	$getNewID = '';
	$errorMsg = '';
	// check for duplicates by name, in given country 
	$dupNameCheck = CWquerySelectStateProvDetails(0,$stateprov_name,'',$country_id);
	// if we have a duplicate, return an error message 
	if($dupNameCheck['totalRows']) {
		$errorMsg = $errorMsg . "<br>Region Name '".$stateprov_name."' already exists";
	}
	// check for duplicates by code, in given country 
	$dupCodeCheck = CWquerySelectStateProvDetails(0,'',$stateprov_code,$country_id);
	// if we have a duplicate, return an error message 
	if($dupCodeCheck['totalRows'] != 0) {
		$errorMsg = $errorMsg . "<br>Code '".$stateprov_code."' already exists";
	}
	if(!strlen(trim($errorMsg))) {
		// insert record 
		 $query = "INSERT INTO cw_stateprov
				(
				stateprov_name,
				stateprov_code,
				stateprov_country_id,
				stateprov_archive,
				stateprov_tax,
				stateprov_ship_ext
				)
				VALUES
				(
				'".CWqueryParam($stateprov_name)."',
				'".CWqueryParam($stateprov_code)."',
				'".CWqueryParam($country_id)."',
				'".CWqueryParam($stateprov_archive)."',
				'".CWqueryParam($stateprov_tax)."',
				'".CWqueryParam($stateprov_ship_ext)."')";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		// Get the new ID 
		$newRecordID = mysql_insert_id();
	} else {
		// if we did have a duplicate, return error code 
		$newRecordID = '0-Error: '.$errorMsg;
	}
	return $newRecordID;
}





// // ---------- Update State/Prov ---------- // 
function CWqueryUpdateStateProv($stateprov_id,$stateprov_name=NULL,$stateprov_code=NULL,$stateprov_archive=2) {
	$updatedID = '';
	$errorMsg = '';
	$checkCountry = '';
	$countryID = '';
	// get country of this stateprov 
	$checkCountry = CWquerySelectStateProvDetails($stateprov_id,'','',0);
	$countryID = (($checkCountry['totalRows']) ? $checkCountry['stateprov_country_id'][0] : 0 );
	// check for duplicates by name, in given country 
	$dupNameCheck = CWquerySelectStateProvDetails(0,$stateprov_name,'',$countryID,$stateprov_id);
	// if we have a duplicate, return an error message 
	if($dupNameCheck['totalRows']) {
		$errorMsg = $errorMsg . "<br>Region Name '".$stateprov_name."' already exists";
	}
	// check for duplicates by code, in given country 
	$dupCodeCheck = CWquerySelectStateProvDetails(0,'',$stateprov_code,$countryID,$stateprov_id);
	// if we have a duplicate, return an error message 
	if($dupCodeCheck['totalRows']) {
		$errorMsg = $errorMsg . "<br>Code '".$stateprov_code."' already exists";
	}
	// if no duplicate, update 
	if(!strlen(trim($errorMsg))) {
		$query = "UPDATE cw_stateprov
				SET stateprov_id = stateprov_id";
		if(strlen(trim($stateprov_name))) {
			$query.= ",stateprov_name = '".CWqueryParam($stateprov_name)."'";
		}
		if(strlen(trim($stateprov_code))) {
			$query.= ",stateprov_code = '".CWqueryParam($stateprov_code)."'";
		}
		if($stateprov_archive != 2) {
			$query.= ",stateprov_archive = '".CWqueryParam($stateprov_archive)."'";
		}
		$query.= " WHERE stateprov_id='".CWqueryParam($stateprov_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$updatedID = $stateprov_id;
	} else {
		// if error message, return string 
		$updatedID  = "0-Error: '".$errorMsg."'";
	}
	return $updatedID;
}




// // ---------- Archive State/Prov ---------- // 

function CWqueryArchiveStateProv($stateprov_archive,$country_id=0,$stateprov_name='',$stateprov_code='',$omit_id_list=0) {
	$query = "UPDATE cw_stateprov
				SET stateprov_archive = ".CWqueryParam($stateprov_archive)."
				WHERE 1=1";
	if($country_id != 0) {
		$query.= " AND stateprov_country_id in('".CWqueryParam($country_id)."')";
	}
	if(trim($stateprov_name) != '') {
		$query.= " AND ".$_ENV["application.cw"]["sqlLower"]."(stateprov_name) in('".CWqueryParam(strtolower($stateprov_name))."')";
	}
	if(trim($stateprov_code) != '') {
		$query.= " AND ".$_ENV["application.cw"]["sqlLower"]."(stateprov_code) in('".CWqueryParam(strtolower($stateprov_code))."')";
	}
	if($omit_id_list != 0) {
		$query.= " AND NOT stateprov_country_id in(".CWqueryParam($omit_id_list).")";
	}
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}





// // ---------- Delete State/Prov ---------- // 

function CWqueryDeleteStateProv($state_id,$country_id=0) {
	$query = "DELETE FROM cw_stateprov
				WHERE 1 = 1";
	if($state_id > 0) {
		$query.= " AND stateprov_id = '".CWqueryParam($state_id)."'";
	}
	if($country_id > 0) {
		$query.= " AND stateprov_country_id = '".CWqueryParam($country_id)."'";
	}
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}





// // ---------- Get Countries with active stateprovs ---------- // 

function CWquerySelectUserStateProvCountries() {
	$rsCheckActive="SELECT DISTINCT c.country_id
					FROM cw_countries c, cw_stateprov s
					WHERE c.country_id = s.stateprov_country_id
					AND s.stateprov_archive = 0
					AND NOT ".$_ENV["application.cw"]["sqlLower"]."(s.stateprov_code) in('none,all')";
	return CWqueryGetRS($rsCheckActive);
}




// // ---------- Get ALL categories ---------- // 

function CWquerySelectCategories() {
	$rsCategories = "SELECT *
						FROM cw_categories_primary
						ORDER BY category_sort, category_name";
	return CWqueryGetRS($rsCategories);
}



	// // ---------- Get ALL secondary categories ---------- // 

function CWquerySelectScndCategories() {
	$rsScndCategories = "SELECT * FROM cw_categories_secondary
							ORDER BY secondary_sort, secondary_name";
	return CWqueryGetRS($rsScndCategories);
}





// // ---------- Get All Credit Cards ---------- // 
function CWquerySelectCreditCards($card_code="",$doSort=false) {
	$rsCCardList = "SELECT * FROM cw_credit_cards";
	if (strlen(trim($card_code))) {
		$rsCCardList .= " WHERE creditcard_code='".trim($card_code)."'";
	}
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsCCardList, $_GET["sortby"]) !== false) {
			$rsCCardList .= " ".CWqueryGetSort($rsCCardList, $_GET["sortby"], $_GET["sortdir"], "creditcard_name ASC");
		} else {
			$rsCCardList .= " ORDER BY creditcard_name";
		}
	} else {
		$rsCCardList .= " ORDER BY creditcard_name";
	}
	return CWqueryGetRS($rsCCardList);
}





	
// // ---------- Get Credit Card Details ---------- // 

function CWquerySelectCreditCardDetails($creditcard_id,$creditcard_name,$creditcard_code,$omit_id_list=0) {
	$rsSelectcreditCard = "SELECT *
							FROM cw_credit_cards
							WHERE 1 = 1";
	if($creditcard_id > 0) {
		$rsSelectcreditCard.= " AND creditcard_id = '".CWqueryParam($creditcard_id)."'";
	}
	if(strlen(trim($creditcard_name))) {
		$rsSelectcreditCard.= " AND creditcard_name = '".CWqueryParam($creditcard_name)."'";
	}
	if(strlen(trim($creditcard_code))) {
		$rsSelectcreditCard.= " AND creditcard_code = '".CWqueryParam($creditcard_code)."'";
	}
	if($omit_id_list != 0) {
		$rsSelectcreditCard.= " AND NOT creditcard_id in('".CWqueryParam($omit_id_list)."')";
	}
	return CWqueryGetRS($rsSelectcreditCard);
}


// // ---------- Insert Credit Card ---------- // 

function CWqueryInsertCreditCard($creditcard_name,$creditcard_code) {
	$newRecordID = '';
		$getNewID = '';
		$errorMsg = '';
		// check for duplicates by name  
		$dupNameCheck = CWquerySelectCreditCardDetails(0,$creditcard_name,'');
		// if we have a duplicate, return an error message 
		if($dupNameCheck['totalRows'] !=0) {
			$errorMsg = $errorMsg . "<br>Card Name '".$creditcard_name."' already exists";
		}
		
		
		// check for duplicates by code 
		$dupCodeCheck = CWquerySelectCreditCardDetails(0,'',$creditcard_code);
		// if we have a duplicate, return an error message 
		if($dupCodeCheck['totalRows'] !='') {
			$errorMsg = $errorMsg . "<br>Code '".$creditcard_code."' already exists";
		}
		// if no duplicate, insert 
		if(strlen(trim($errorMsg)) == '') {
			// insert record 
			 $query = "INSERT INTO cw_credit_cards
					(
					creditcard_name,
					creditcard_code
					)
					VALUES
					(
					'".CWqueryParam($creditcard_name)."',
					'".CWqueryParam($creditcard_code)."')";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-admin.php");
				chdir($myDir);
			}
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
			// Get the new ID 
			$newRecordID = mysql_insert_id();
		}
		// if we did have a duplicate, return error code 
		else
		{
			$newRecordID = "0-Error: '".$errorMsg."'";
		}
	return $newRecordID;
	}





	// // ---------- Update Credit Card ---------- // 

	function CWqueryUpdateCreditCard($creditcard_id,$creditcard_name=NULL,$creditcard_code=NULL) {
	$updatedID = '';
		$errorMsg = '';
		// check for duplicates by name  
		$dupNameCheck = CWquerySelectCreditCardDetails(0,$creditcard_name,'',$creditcard_id);
		// if we have a duplicate, return an error message 
		if($dupNameCheck['totalRows'] !=0) {
			$errorMsg = $errorMsg . "<br>Card Name '".$creditcard_name."' already exists";
		}
		
		// check for duplicates by code 
		$dupCodeCheck = CWquerySelectCreditCardDetails(0,'',$creditcard_code,$creditcard_id);
		// if we have a duplicate, return an error message 
		if($dupCodeCheck['totalRows'] !=0) {
			$errorMsg = $errorMsg . "<br>Code '".$creditcard_code."' already exists";
		}
		// if no duplicate, update 
		if(strlen(trim($errorMsg)) =='' ) {
			$query = "UPDATE cw_credit_cards
					SET
					creditcard_archive = creditcard_archive";
			if(strlen(trim($creditcard_code))) {
				$query.= ",creditcard_code = '".CWqueryParam($creditcard_code)."'";
			}
			if(strlen(trim($creditcard_name))) {
				$query.= ",creditcard_name = '".CWqueryParam($creditcard_name)."'";
			}
			$query.= " WHERE creditcard_id = '".CWqueryParam($creditcard_id)."'";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-admin.php");
				chdir($myDir);
			}
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
			$updatedID = $creditcard_id;
		}
		// if error message, return string 
		else
		{
			$updatedID = "0-Error: '".$errorMsg."'";
		}
	return $updatedID;
	}





	// // ---------- Delete Credit Card ---------- // 

function CWqueryDeleteCreditCard($creditcard_id) {
	$query = "DELETE FROM cw_credit_cards
			WHERE creditcard_id= '".CWqueryParam($creditcard_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}





			// // ---------- Select All Config Groups ---------- // 

function CWquerySelectConfigGroups($doSort=false) {
	$rsConfigGroups = "SELECT * FROM cw_config_groups";
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsConfigGroups, $_GET["sortby"]) !== false) {
			$rsConfigGroups .= " ".CWqueryGetSort($rsConfigGroups, $_GET["sortby"], $_GET["sortdir"], "config_group_sort ASC, config_group_name ASC");
		} else {
			$rsConfigGroups .= " ORDER BY config_group_sort, config_group_name";
		}
	} else {
		$rsConfigGroups .= " ORDER BY config_group_sort, config_group_name";
	}
	return CWqueryGetRS($rsConfigGroups);
}




		// // ---------- Get Config Group Details ---------- // 

function CWquerySelectConfigGroupDetails($config_group_id,$config_group_name,$omit_id_list=0) {
	$rsSelectConfigGroup = "SELECT *
							FROM cw_config_groups
							WHERE 1 = 1";
	if($config_group_id > 0) {
		$rsSelectConfigGroup.= " AND config_group_id = '".CWqueryParam($config_group_id)."'";
	}
	if(strlen(trim($config_group_name))) {
		$rsSelectConfigGroup.= " AND config_group_name = '".CWqueryParam($config_group_name)."'";
	}
	if($omit_id_list != 0) {
		$rsSelectConfigGroup.= " AND NOT config_group_id in('".CWqueryParam($omit_id_list)."')";
	}
	return CWqueryGetRS($rsSelectConfigGroup);
}




		// // ---------- Insert Config Group ---------- // 

	function CWqueryInsertConfigGroup($config_group_name,$config_group_sort=1,$config_group_showmerchant=0,$config_group_protected=0) {
	$newRecordID = '';
		$getNewID = '';
		$errorMsg = '';
		// check for duplicates by name  
		$dupNameCheck = CWquerySelectConfigGroupDetails(0,$config_group_name);
		// if we have a duplicate, return an error message 
		if($dupNameCheck['totalRows']) {
			$errorMsg = $errorMsg . "<br>Group Name '".$config_group_name."' already exists";
		}
		// if no duplicate, insert 
		if(!strlen(trim($errorMsg))) {
			// insert record 
			$query = "INSERT INTO cw_config_groups
					(
					config_group_name,
					config_group_sort,
					config_group_show_merchant,
					config_group_protected
					)
					VALUES (
					'".CWqueryParam($config_group_name)."',
					'".CWqueryParam($config_group_sort)."',
					'".CWqueryParam($config_group_showmerchant)."',
					'".CWqueryParam($config_group_protected)."'
					)";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-admin.php");
				chdir($myDir);
			}
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
			// Get the new ID 
			$newRecordID = mysql_insert_id();
		}
		// if we did have a duplicate, return error code 
		else
		{
			$newRecordID = "0-Error: '".$errorMsg."'";
		}
	return $newRecordID;
	}





		// // ---------- Update Config Group ---------- // 

	function CWqueryUpdateConfigGroup($config_group_id,$config_group_name=NULL,$config_group_sort=1,$config_group_showmerchant=0) {
	$updatedID = '';
		$errorMsg = '';
		// check for duplicates by name  
		$dupNameCheck = CWquerySelectConfigGroupDetails(0,$config_group_name,$config_group_id);
		// if we have a duplicate, return an error message 
		if($dupNameCheck['totalRows']) {
			$errorMsg = $errorMsg . "<br>Config Group Name '".$config_group_name."' already exists";
		}
		
		// if no duplicate, update 
		if(!strlen(trim($errorMsg))) {
			$query = "UPDATE cw_config_groups
					SET
					config_group_sort = '".CWqueryParam($config_group_sort)."',
					config_group_show_merchant = '".CWqueryParam($config_group_showmerchant)."'";
			if(strlen(trim($config_group_name))) {
				$query.= ",config_group_name = '".CWqueryParam($config_group_name)."'";
			}
			$query.= " WHERE config_group_id = '".CWqueryParam($config_group_id)."'";	
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-admin.php");
				chdir($myDir);
			}
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
			$updatedID = $config_group_id;	
		}
		// if error message, return string 
		else
		{
			$updatedID = "0-Error: '".$errorMsg."'";
		}
	return $updatedID;	
	}





// // ---------- Delete Config Group ---------- // 
function CWqueryDeleteConfigGroup($group_id) {
	$deleteCheck='';
	$deleteVar='';
	try{
		// check for protected items by group ID  
		$deleteCheck = CWquerySelectConfigItems($group_id);
		// if any items are found, do not delete 
		if($deleteCheck['totalRows']>0) {
			$returnString = "0- Config Group contains ".$deleteCheck['totalRows']." items and cannot be deleted";
		} else {
			// if no items are found 
			$query = "DELETE FROM cw_config_groups
						WHERE config_group_id = ".CWqueryParam($group_id)."";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-admin.php");
				chdir($myDir);
			}
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);		
			// return a message confirming deletion 
			$returnString = "Config Group deleted";			
		}
	}catch(Exception $e) {
		// if any errors occur during the operation, return an error message 
		$returnString = '0-'.$e->getMessage().';' ;
	}
	return $returnString;
}
	

 


	// // ---------- Get Config Items in a given group ---------- // 

function CWquerySelectConfigItems($config_group_id) {
	$rsSelectConfigItems="";
	$rsSelectConfigItems="SELECT *
							FROM cw_config_items
							WHERE config_group_id = '".CWqueryParam($config_group_id)."'";
	if((!isset($_SESSION["cw"]["accessLevel"]) )&& (ListFindNoCase('merchant,developer',$_SESSION["cw"]["accessLevel"]))) {
		$rsSelectConfigItems.="AND config_show_merchant = 1";
	}
	$rsSelectConfigItems.="ORDER BY config_sort, config_name";
	return CWqueryGetRS($rsSelectConfigItems);
}




		// // ---------- Get Config Item Details ---------- // 

// ID, name and variable are required, can be passed in as 0 or '' 
// others are optional 
function CWquerySelectConfigItemDetails($config_id,$config_name,$config_variable,$group_id=0,$omit_id_list=0) {
	$rsSelectConfigItem="";
	// look up config item 
	$rsSelectConfigItem="SELECT *
							FROM cw_config_items
							WHERE 1 = 1";
	if($config_id>0) {
		$rsSelectConfigItem.=" AND config_id = '".CWqueryParam($config_id)."'";
	}
	if(trim($config_name)!='') {
		$rsSelectConfigItem.=" AND config_name = '".CWqueryParam($config_name)."'";
	}
	if(trim($config_variable)!='') {
		$rsSelectConfigItem.=" AND config_variable = '".CWqueryParam($config_variable)."'";
	}
	if($omit_id_list!=0) {
		$rsSelectConfigItem.=" AND NOT config_id in(".CWqueryParam($omit_id_list).")";
	}
	return CWqueryGetRS($rsSelectConfigItem);
}
 
 
 
 
 
 
 
// // ---------- Insert Config Item ---------- // 
function CWqueryInsertConfigItem($group_id,$config_variable,$config_name,$config_value,$config_type,$config_description=NULL,$config_possibles=NULL,$config_showmerchant=NULL,$config_sort=1,$config_size=25,$config_rows=5,$config_protected=0,$config_required=0) {
	$newRecordID = '';
	$getNewRecordID = '';
	$errorMsg = '';
	
	// check for duplicates: look up existing config item by name 
	$dupNameCheck = CWquerySelectConfigItemDetails(0,trim($config_name),'',$group_id);
	// if we have a duplicate, return an error message 
	if($dupNameCheck['totalRows'] > 0) {
		$errorMsg .= (($errorMsg) ? "," : "")."Item Name '".$config_name."' already exists";
	}
	// check for duplicates by variable 
	$dupVarCheck = CWquerySelectConfigItemDetails(0,'',trim($config_variable),$group_id);
	// if we have a duplicate, return an error message 
	if($dupVarCheck['totalRows'] !=0) {
		$errorMsg .= (($errorMsg) ? "," : "")."Variable '".$config_variable."' already exists";
	}
	
	if((trim($errorMsg) == '')) {
		$query = "INSERT INTO cw_config_items (
					config_group_id,
					config_variable,
					config_name,
					config_value,
					config_type,
					config_description,
					config_possibles,
					config_show_merchant,
					config_sort,
					config_size,
					config_rows,
					config_protected,
					config_required
		
				)
				VALUES(
					".CWqueryParam($group_id).",
					'".CWqueryParam(trim($config_variable))."',
					'".CWqueryParam(trim($config_name))."',
					'".CWqueryParam(trim($config_value))."',
					'".CWqueryParam(trim($config_type))."',
					'".CWqueryParam(trim($config_description))."',
					'".CWqueryParam(trim($config_possibles))."',
					".CWqueryParam($config_showmerchant).",
					".CWqueryParam($config_sort).",
					".CWqueryParam($config_size).",
					".CWqueryParam($config_rows).",
					".CWqueryParam($config_protected).",
					".CWqueryParam($config_required)."
					)";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$newRecordID = mysql_insert_id();	
	} else {
		$newRecordID = '0-'.$errorMsg;
	}
	return $newRecordID;
	
}





	// // ---------- Update Config Item ---------- // 

function CWqueryUpdateConfigItem($config_id,$group_id,$config_variable,$config_name,$config_value,$config_type=NULL,$config_description=NULL,$config_possibles=NULL,$config_showmerchant=0,$config_sort=0,$config_size=0,$config_rows=0,$config_protected=2,$config_required=2) {
	$updatedID="";
	$errorMsg="";
	// check for duplicates: look up existing config item by name 
	$dupNameCheck = CWquerySelectConfigItemDetails(0,trim($config_name),'',$group_id,$config_id);
	// if we have a duplicate, return an error message 
	if ($dupNameCheck["totalRows"]) {
		$errorMsg .= (($errorMsg) ? "," : "")."Item Name '".$config_name."' already exists";
	}
	// check for duplicates by variable 
	$dupVarCheck = CWquerySelectConfigItemDetails(0,'',trim($config_variable),$group_id,$config_id);
	// if we have a duplicate, return an error message 
	if ($dupVarCheck["totalRows"]) {
		$errorMsg .= (($errorMsg) ? "," : "")."Variable '".$config_variable."' already exists";
	}
	if (is_array($config_value)) $config_value = implode(",", $config_value);
	// if no duplicate, update 
	if(!(trim($errorMsg)!='')) {
		$query = "UPDATE cw_config_items
				SET config_group_id = '".CWqueryParam($group_id)."'
					,config_variable = '".CWqueryParam(trim($config_variable))."'
					,config_name = '".CWqueryParam(trim($config_name))."'
					,config_value = '".CWqueryParam(trim($config_value))."'";
		if(trim($config_type)!='') {
			$query.=",config_type = '".CWqueryParam(trim($config_type))."'";
		}
		if(trim($config_description)!='') {
			$query.=",config_description = '".CWqueryParam(trim($config_description))."'";
		}
		if(trim($config_possibles)!='') {
			$query.=",config_possibles = '".CWqueryParam(trim($config_possibles))."'";
		}
		if(trim($config_showmerchant)!='') {
			$query.=",config_show_merchant = '".CWqueryParam(trim($config_showmerchant))."'";
		}
		if($config_sort!=0) {
			$query.=",config_sort = '".CWqueryParam($config_sort)."'";
		}
		if($config_size!=0) {
			$query.=",config_size = '".CWqueryParam($config_size)."'";
		}
		if($config_rows!=0) {
			$query.=",config_rows = '".CWqueryParam($config_rows)."'";
		}
		if($config_protected!=2) {
			$query.=",config_protected = '".CWqueryParam($config_protected)."'";
		}
		if($config_required!=2) {
			$query.=",config_required = '".CWqueryParam($config_required)."'";
		}

		$query.=" WHERE config_id = '".CWqueryParam($config_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$updatedID = $config_id;
	}
	
	return $updatedID;
	
}




	// // ---------- Delete Config Item ---------- // 

function CWqueryDeleteConfigItem($config_id) {
	$deleteCheck="";
	$deleteVar="";
// check for record protected by ID  	
	$deleteCheck=CWquerySelectConfigItemDetails($config_id,'','');
	try
	{
		// if the item is not 'protected' 
		if($deleteCheck['config_protected'][0]==0) {
			$query="DELETE FROM cw_config_items
					WHERE config_id = '".CWqueryParam($config_id)."'
					AND config_protected = 0";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-admin.php");
				chdir($myDir);
			}
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);	
			// delete the corresponding CW application scope 
			// return a message confirming deletion 
			$returnString = "Config Item '".$deleteCheck[config_name][0]."' deleted" ;
		} else {
			// if the variable is protected, return an error 
			$returnString = "0- Config Item '".$deleteCheck[config_name][0]."' cannot be deleted";
			
		}

				
	}
	catch(Exception $e) {
		// if any errors occur during the operation, return an error message 
		$returnString = '0-'.$e->getMessage().';';
	}
	return $returnString;
}
 
// // ---------- // Get Config Group Name // ---------- // 
function CWgetConfigGroupName($config_group_id) {
	$returnStr = '';
	$rsSelectConfigGroup = CWqueryGetRS("SELECT config_group_name
		FROM cw_config_groups
		WHERE config_group_id = ".CWqueryParam($config_group_id)."");
	if (isset($rsSelectConfigGroup["config_group_name"][0])) {
		$returnStr = $rsSelectConfigGroup["config_group_name"][0];
	}
	return $returnStr;
}

	// // ---------- Order Status Menu ---------- // 

function CWqueryNavOrders($menu_counter=1) {global $ShipStatusMenu;
// get all ship status types 	
	$orderStatusQuery= CWquerySelectOrderStatus();
	if(!isset($ShipStatusMenu) || (!(trim($ShipStatusMenu)!=''))) {
		$shipStatusMenu="";
		if($orderStatusQuery['totalRows']>0) {
			$shipStatusMenu="";
			for($i=0;$i<$orderStatusQuery['totalRows'];$i++) {
				$shipStatusMenu.= $menu_counter."|orders.php?status=".$orderStatusQuery['shipstatus_id'][$i]."|".$orderStatusQuery['shipstatus_name'][$i].",";
			}
		}
	}
	//print_r($shipStatusMenu); exit;
	return $shipStatusMenu;
}




// // ---------- Config Groups Menu ---------- // 

function CWqueryNavConfig($menu_counter=1,$omit_list='',$return_rows=99) {
	$configMenu="";
	$rsNavQuery="SELECT config_group_name, config_group_id
					FROM cw_config_groups
					WHERE 1=1 ";
	if (!isset($_SESSION["cw"]["accessLevel"]) || ($_SESSION["cw"]["accessLevel"] != 'developer')) {
		$rsNavQuery .= " AND config_group_show_merchant = 1 ";
	}
	if($omit_list != '') {
		 $rsNavQuery .= " AND NOT config_group_id in(".CWqueryParam($omit_list).")";
	}
	$rsNavQuery .= " ORDER BY config_group_sort, config_group_name";
	$rsNavConfig = CWqueryGetRS($rsNavQuery);
	// build list - if only 1 row being returned, label it 'custom settings' 
	for ($nr = 0; ($nr < $return_rows && $nr < $rsNavConfig["totalRows"]); $nr++) {
		$configMenu .= $menu_counter."|config-settings.php?group_id=".$rsNavConfig["config_group_id"][$nr]."|";
		if ($return_rows == 1) { 
			$configMenu .= "Custom Settings";
		} else {
			$configMenu .= $rsNavConfig['config_group_name'][$nr];
		}
		$configMenu .= ',';
	}
	return $configMenu;
}
 
 
 
 
	// // ---------- Text Items Menu ---------- // 

function CWqueryNavText($menu_counter=1,$return_rows=99) {
	$rsNavText = '';
	$configMenu = '';
// get config groups to show to the user 
	$rsNavText="SELECT text_group_name, text_group_id, text_group_language
				FROM cw_text_groups
				ORDER BY text_group_sort, text_group_name";
	$result = CWqueryGetRS($rsNavText);
// build list - if only 1 row being returned, label it 'text items' 				
	for($i=0;$i<$return_rows;$i++) {
		if(trim($result['text_group_name'][$i])!='') { 
			$configMenu.=$menu_counter."|text-items.php?group_id=".$text_group_id."|";
		    if($return_rows == 1) {
				$configMenu.='Text Items';
			} else {
				$configMenu.=$result['text_group_name'][$i]."(".$result['text_group_language'][$i].")";
			}
		}	
	}
	return $configMenu;
}







	// // ---------- Get all active Admin Users ---------- // 

function CWquerySelectAdminUsers($record_id=0,$username=NULL,$omit_levels=NULL,$doSort=false) {
	$rsGetAdminUsers = "SELECT *
						FROM cw_admin_users
						WHERE 1 = 1 ";
						
// user id 
	if($record_id>0) {
		$rsGetAdminUsers.=" AND admin_user_id = '".CWqueryParam($record_id)."'";
	}
// user name 
	if(trim($username)!='') {
		$rsGetAdminUsers.=" AND admin_username = '".CWqueryParam($username)."'";
	}
// levels to omit 
	if(trim($omit_levels)!='') {
		$rsGetAdminUsers.=" AND NOT admin_access_level in('".CWqueryParam($omit_levels)."')";
	}
	if ($doSort) {
		if (isset($_GET["sortby"]) && isset($_GET["sortdir"]) && CWqueryCanSort($rsGetAdminUsers, $_GET["sortby"]) !== false) {
			$rsGetAdminUsers .= " ".CWqueryGetSort($rsGetAdminUsers, $_GET["sortby"], $_GET["sortdir"], "");
		} else {
			$rsGetAdminUsers .= "";
		}
	} else {
		$rsGetAdminUsers .= "";
	}
	return CWqueryGetRS($rsGetAdminUsers);
}

	// // ---------- Insert Admin User ---------- // 

 function CWqueryInsertUser($username,$password,$user_level,$user_title,$user_email="") {
	$newUserID="";
// first look up existing user by username 
	$dupCheck = CWquerySelectAdminUsers(0,$username);
// if we have a duplicate, return an error message 	
	if($dupCheck['totalRows']>0) {
		$newUserID = '0-username';
	} else {
		// if no duplicate, insert 
		$query = "INSERT INTO cw_admin_users
				(
				admin_username,
				admin_password,
				admin_access_level,
				admin_user_alias,
				admin_user_email
				)
				VALUES
				(
				'".CWqueryParam($username)."',
				'".CWqueryParam($_POST['admin_password'])."',
				'".CWqueryParam($_POST['admin_access_level'])."',
				'".CWqueryParam($user_title)."',
				'".CWqueryParam($user_email)."'
				)";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
	// get ID for return value 	
		$newUserID = mysql_insert_id();		
		return $newUserID;
	}
 }
 
 
 
 
	// // ---------- Update Admin User ---------- // 

function CWqueryUpdateUser($record_id,$username,$password,$user_level,$user_title,$user_email) {
	$updateuserid = "";
// first look up existing user by username 
	$dupCheck = CWquerySelectAdminUsers(0,$username);
// if we have a duplicate, return an error message 
	if(($dupCheck['totalRows'] > 0) && ($dupCheck['admin_username']==$username)) {	$updateuserid = '0-username';} else {
		// if no duplicate, insert 
		 $query = "UPDATE cw_admin_users
					SET
					admin_username = '".CWqueryParam($username)."',
					admin_password = '".CWqueryParam($password)."',
					admin_user_alias = '".CWqueryParam($user_title)."',
					admin_user_email = '".CWqueryParam($user_email)."',
					admin_access_level = '".CWqueryParam($user_level)."'
					WHERE admin_user_id = '".CWqueryParam($record_id)."'";
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-admin.php");
			chdir($myDir);
		}
		mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
		$updateuserid = $record_id;				
	}
	return $updateuserid;
	
}




	// // ---------- Log On / Look Up User ---------- // 

function CWquerySelectUserLogin($usernamestr=NULL,$passwordstr=NULL,$list_max=1) {
	$rsSelectUsers = "SELECT * FROM cw_admin_users WHERE ";
	$rsSelectUsers .= " admin_username = '".trim(CWqueryParam($usernamestr))."' ";
	$rsSelectUsers.= " AND ";
	$rsSelectUsers .= " admin_password = '".trim(CWqueryParam($passwordstr))."' ";
	return CWqueryGetRS($rsSelectUsers);
}




	// // ---------- Update User Logon Date---------- // 

function CWqueryUpdateUserDate($username,$prev_date) {
	$query="UPDATE cw_admin_users
		SET admin_last_login = '".CWqueryParam(date("Y-m-d H:i:s",$prev_date))."',
		admin_login_date = '".CWqueryParam(date("Y-m-d H:i:s",CWtime()))."'
		WHERE admin_username = '".CWqueryParam($username)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}
// // ---------- Delete Admin User --------

function CWqueryDeleteUser($record_id) {
	$query = "DELETE FROM cw_admin_users
			WHERE admin_user_id='".CWqueryParam($record_id)."'";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-admin.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);		
}
?>
