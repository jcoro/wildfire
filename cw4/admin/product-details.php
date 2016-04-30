<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: product-details.php
File Date: 2012-05-23
Description:Manage product details, descriptions, images, skus and related products
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// download functions
require_once("cwadminapp/func/cw-func-download.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accesslevel"] = CWauth("manager,merchant,developer");
// PRODUCT INCLUDES 
// include the product functions 
require_once('cwadminapp/func/cw-func-product.php');
// BASE URL 
// for this page the base url is the standard current page variable 
$_ENV["request.cwpage"]["baseURL"] = $_ENV["request.cw"]["thisPage"];
// PAGE PARAMS 
if(!isset($_ENV["application.cw"]["adminRecordsPerPage"])){ //=='') {
	$_ENV["application.cw"]["adminRecordsPerPage"]=30;
}
// params for dynamic page headings 
if(!isset($_ENV["request.cwpage"]["productName"])) {
	$_ENV["request.cwpage"]["productName"]='Add New Product';
}
if(!isset($_ENV["request.cwpage"]["subHead"])) {
	$_ENV["request.cwpage"]["subHead"]='Add product details, descriptions and images';
}
if(!isset($_ENV["request.cwpage"]["currentNav"])) {
	$_ENV["request.cwpage"]["currentNav"]=$_ENV["request.cw"]["thisPage"];
}
if (!isset($detailsQuery)) $detailsQuery = array();
if(!isset($detailsQuery['totalRows'])) {
	$detailsQuery['totalRows']=0;
}

// global params 
if(!isset($SKUList)) $SKUList=0;
if(!isset($_POST['hasOrders'])) $_POST['hasOrders']=0;
if(!isset($ProductHasOrders)) $ProductHasOrders=0;
if(!isset($disabledText)) $disabledText='';
//Not sure where this came from
//if(!isset($FormFocus)) $FormFocus="productForm','product_name";
if(!isset($_GET['productid'])) $_GET['productid']=0;
if(!isset($_GET['showtab'])) $_GET['showtab']=1;
if(!isset($_ENV["request.cwpage"]["productlookupid"])) $_ENV["request.cwpage"]["productlookupid"]=0;
// default values for seach form
if(!isset($_GET['pagenumresults'])) $_GET['pagenumresults']=1;//0;
if(!isset($_GET['searchby'])) $_GET['searchby']='1';
if(!isset($_GET['search'])) $_GET['search']='';
if(!isset($_GET['matchtype'])) $_GET['matchtype']='anyMatch';
if(!isset($_GET['find'])) $_GET['find']='';
if(!isset($_GET['maxrows'])) $_GET['maxrows']=$_ENV["application.cw"]["adminRecordsPerPage"];
if(!isset($_GET['sortby'])) $_GET['sortby']='product_name';
if(!isset($_GET['sortdir'])) $_GET['sortdir']='asc';
if(!isset($_GET['view'])) $_GET['view']='';
// mode can be passed in via url, or request scope, to override application setting 
if(!isset($_GET['skumode'])) $_GET['skumode']=$_ENV["application.cw"]["adminSkuEditMode"];
// search in cats, subcats 
if(!isset($_GET['searchC']) || $_GET['searchC']=='') $_GET['searchC']=0;
if(!isset($_GET['seachSC']) || $_GET['searchSC']=='') $_GET['searcSC']=0;
// use upsell? 
$_ENV["request.cwpage"]["showUpsell"] = $_ENV["application.cw"]["appDisplayUpsell"]; 
// /////// 
// LOOKUP PRODUCT 
// /////// 
// run the query on url.id number (or default of 0 to get blank fields for new form)

if (is_numeric($_GET['productid'])) $_ENV["request.cwpage"]["productlookupid"]=$_GET['productid'];
// QUERY: get product details (product id) 
$detailsQuery=CWquerySelectProductDetails($_ENV["request.cwpage"]["productlookupid"]);

// if we have an ID in the url but not found in the query, return to products page 
	
if($_GET['productid'] > 0 && $detailsQuery['totalRows'] != 1)
	header('location:products.php');    
// /////// 
// /END LOOKUP PRODUCT 
// /////// 
// PRODUCT FORM DEFAULTS 
// Set visible form values - all values are null if query returns no results (new product) */
if($_ENV["application.cw"]["adminProductDefaultPrice"]=='')
	$_ENV["application.cw"]["adminProductDefaultPrice"]=0;
if(!isset($_POST['product_merchant_product_id'])) $_POST['product_merchant_product_id']='';
if(!isset($_POST['product_name'])) $_POST['product_name'] = ((isset($detailsQuery['product_name'][0])) ? $detailsQuery['product_name'][0] : "" );
if(!isset($_POST['product_sort'])) $_POST['product_sort'] = ((isset($detailsQuery['product_sort'][0])) ? $detailsQuery['product_sort'][0] : "" );
if(!isset($_POST['product_on_web'])) $_POST['product_on_web'] = ((isset($detailsQuery['product_on_web'][0])) ? $detailsQuery['product_on_web'][0] : "" );
if(!isset($_POST['product_tax_group_id'])) $_POST['product_tax_group_id'] = ((isset($detailsQuery['product_tax_group_id'][0])) ? $detailsQuery['product_tax_group_id'][0] : "" );
if(!isset($_POST['product_ship_charge'])) $_POST['product_ship_charge'] = ((isset($detailsQuery['product_ship_charge'][0])) ? $detailsQuery['product_ship_charge'][0] : "" );
if(!isset($_POST['product_preview_description'])) $_POST['product_preview_description'] = ((isset($detailsQuery['product_preview_description'][0])) ? $detailsQuery['product_preview_description'][0] : "" );
if(!isset($_POST['product_description'])) $_POST['product_description'] = ((isset($detailsQuery['product_description'][0])) ? $detailsQuery['product_description'][0] : "" );
if(!isset($_POST['product_special_description'])) $_POST['product_special_description'] = ((isset($detailsQuery['product_special_description'][0])) ? $detailsQuery['product_special_description'][0] : "" );
if(!isset($_POST['product_keywords'])) $_POST['product_keywords'] = ((isset($detailsQuery['product_keywords'][0])) ? $detailsQuery['product_keywords'][0] : "" );
if(!isset($_POST['product_out_of_stock_message'])) $_POST['product_out_of_stock_message'] = ((isset($detailsQuery['product_out_of_stock_message'][0])) ? $detailsQuery['product_out_of_stock_message'][0] : "" );
if(!isset($_POST['product_custom_info_label'])) $_POST['product_custom_info_label'] = ((isset($detailsQuery['product_custom_info_label'][0])) ? $detailsQuery['product_custom_info_label'][0] : "" );
if(!isset($_POST['hasOrders'])) $_POST['hasOrders']=0;
// SKU FORM DEFAULTS 
// base sku values 
if(!isset($_POST['sku_merchant_sku_id'])) $_POST['sku_merchant_sku_id']='';
if(!isset($_POST['sku_product_id'])) $_POST['sku_product_id']=$_GET['productid'];
if(!isset($_POST['sku_price'])) $_POST['sku_price']=$_ENV["application.cw"]["adminProductDefaultPrice"];
if(!isset($_POST['sku_ship_base'])) $_POST['sku_ship_base']=0;
if(!isset($_POST['sku_sort'])) $_POST['sku_sort']=1;
if(!isset($_POST['sku_weight'])) $_POST['sku_weight']=0;
if(!isset($_POST['sku_stock'])) $_POST['sku_stock']=0;
if(!isset($_POST['sku_alt_price'])) $_POST['sku_alt_price']=0;
if(!isset($_POST['sku_on_web'])) $_POST['sku_on_web']=1;
if(!isset($_POST['sku_delete'])) $_POST['sku_delete']=1;
$clickNewSkuCode = "<script type=\"text/javascript\">
// show/hide new sku form
jQuery(document).ready(function(){
jQuery('a#showNewSkuFormLink').click();
jQuery('form#addSkuForm').children('input:first').focus();
});
</script>
";
// params for add product/sku errors 
if(!isset($_ENV["request.cwpage"]["AddProductError"])) $_ENV["request.cwpage"]["AddProductError"]=array();
if(!isset($_ENV["request.cwpage"]["AddSKUError"])) $_ENV["request.cwpage"]["AddSKUError"]=array();
// include server-side form validation 
include('cwadminapp/inc/cw-inc-admin-product-validate.php');

// if no validation errors, we can proceed with add/update/delete functions 
if ((isset($_POST['action']) && count($_ENV["request.cwpage"]["AddProductError"]) == 0 && count($_ENV["request.cwpage"]["AddSKUError"]) == 0) ||
	 (isset($_GET['deleteproduct']) && is_numeric($_GET['deleteproduct']) && $_GET['deleteproduct'] > 0) ||
	 (isset($_POST['sku_id']) && count($_ENV["request.cwpage"]["AddProductError"]) == 0 && count($_ENV["request.cwpage"]["AddSKUError"]) == 0) ||
	 (isset($_POST['sku_id0']) && count($_ENV["request.cwpage"]["AddProductError"]) == 0 && count($_ENV["request.cwpage"]["AddSKUError"]) == 0) ||
	 (isset($_POST['AddSKU']) && count($_ENV["request.cwpage"]["AddProductError"]) == 0 && count($_ENV["request.cwpage"]["AddSKUError"]) == 0) ||
	 (isset($_GET['deletesku']) && is_numeric($_GET['deletesku']) && $_GET['deletesku'] > 0) ||
	 isset($_POST['AddUpsell']) || isset($_GET['delupsellid']) || isset($_GET['upselldelete']) || isset($_POST['deleteupsell_id'])) { 
	// /////// 
	// INSERT UPSELL 
	// /////// 
	if(isset($_POST['AddUpsell']) && isset($_POST['UpSellproduct_id'])) {
		// QUERY: insert upsells, returns number inserted 
		$addUpsell=CWfuncUpsellAdd($_POST['product_id'],$_POST['UpSellproduct_id']);

		$addRelCt=0;
		if(isset($_POST['UpSellProductRecip_ID'])) {
			for($relID=0;$relID<count($_POST['UpSellProductRecip_ID']);$relID++ ) {
				// QUERY: insert reciprocals one at a time, returns 1
				// quick adaptor for this function...
				$pidArray = array();
				$pidArray[] = $_POST['product_id'];
				$addRel=CWfuncUpsellAdd($_POST['UpSellProductRecip_ID'][$relID],$pidArray);
				
				if(isset($addRel) && $addRel > 0)
					$addRelCt=$addRelCt + $addRel;
			}
		}
		// if errors 
		if(isset($_ENV["request.cwpage"]["upsellInsertError"]) && strlen(trim($_ENV["request.cwpage"]["upsellInsertError"]))) {
			CWpageMessage('alert',$_ENV["request.cwpage"]["upsellInsertError"]);
		} else {
			if($addUpsell > 1)
				$s='s';
			else
				$s='';
		}
		$confirmMsg=$addUpsell.' Related Product'.$s.' created';
		if($addRelCt > 0) {
			$confirmMsg .= '<br> '.' '. $addRelCt.' Reciprocal Record'.$s.' created';
			CWpageMessage('confirm',$confirmMsg);
			if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
			if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
			header("Location: ".$_ENV["request.cw"]["thisPage"].'?productid='.$_POST['product_id'].'&showtab=5&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));
		}
	}
	// /END if errors 
	// /////// 
	// /END INSERT UPSELL 
	// /////// 
	// /////// 
	// DELETE UPSELL 
	// /////// 
	// delete single upsell via url var
	if(isset($_GET['delupsellid'])) {
		$deleteUpsell=CWfuncupselldelete(0,$_GET['delupsellid']);
		if(isset($_ENV["request.cwpage"]["upselldeleteError"]) && strlen(trim($_ENV["request.cwpage"]["upselldeleteError"]))) {
			CWpageMessage("alert",$_ENV["request.cwpage"]["upselldeleteError"]);
		} else {
			CWpageMessage("confirm","Related Product Deleted");
			if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
			if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
			header("Location: ".$_ENV["request.cw"]["thisPage"].'?productid='.$_GET['productid'].'&showtab=5&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));
		}
		// delete list of upsells via checkboxes
	 }
	 elseif(isset($_POST['deleteupsell_id'])) {
		$delCt=0;
		for ($i=0; $i<count($_POST['deleteupsell_id']); $i++) {
			$delID = $_POST['deleteupsell_id'][$i];
			echo " deleteUpsell |".$delID."|<br>";
			$deleteUpsell = CWfuncupselldelete(0,$delID);
			if(isset($_ENV["request.cwpage"]["upselldeleteError"]) && strlen(trim($_ENV["request.cwpage"]["upselldeleteError"])))
				CWpageMessage('alert',$_ENV["request.cwpage"]["upselldeleteError"]);
			else
			  $delCt++;
		}
		if($delCt > 1)
			$s='s';
		else
			$s='';
		// set up confirmation 
		$alertMsg=$delCt.' Related Product'.$s.' deleted';
		CWpageMessage("confirm",$alertMsg);
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ". $_ENV["request.cw"]["thisPage"] .'?productid=' .$_GET["productid"]. '&showtab=5&userconfirm=' .CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));
		// DELETE ALL UPSELLS via URL variable
	}	
	elseif (isset($_GET['upselldelete'])) {
		$deleteAllUpsell = CWfuncupselldelete($_GET['upselldelete'],0,1);
		// if we have any error 
		if(isset($_ENV["request.cwpage"]["upselldeleteError"]) && strlen(trim($_ENV["request.cwpage"]["upselldeleteError"]))) {
			CWpageMessage("alert",$_ENV["request.cwpage"]["upselldeleteError"]);
		// if no error 
		} else {
			CWpageMessage("confirm","All Related Products Deleted");
			if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
			if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
			header("Location: ".$_ENV["request.cw"]["thisPage"].'?productid='.$_GET["productid"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));
		}
	}
	// /////// 
	// /END DELETE UPSELL 
	// /////// 
	// /////// 
	// DELETE PRODUCT 
	// /////// 
	if(isset($_GET['deleteproduct'])) {
		$deleteProd=CWfuncProductDelete($_GET['deleteproduct']);
		if(isset($_POST['productDeleteError'])) {
			CWpageMessage("alert","Error: ".$_POST['productDeleteError']);
			if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
			if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
			header("location:product-details.php?productid=".$_GET['deleteproduct'].'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]));		
		} else {
			header("location:products.php?userconfirm=Product%20Deleted");
		}
		// if no error, return showing message 
		// /////// 
		// /END DELETE PRODUCT 
		// /////// 
		// /////// 
		// UPDATE PRODUCT 
		// /////// */
	}
	elseif(isset($_POST['action']) && $_POST['action'] =='updateProduct') {
		// additional form params here, default values not desired unless adding/editing product 
		// accessing form elements thru the post isnt working as it was, working around
		if(!(isset($_POST['product_options']))) $_POST['product_options']=array();
		if(!(isset($_POST['product_category_id']))) $_POST['product_category_id']=array();
		if(!(isset($_POST['product_scndcat_ID']))) $_POST['product_scndcat_ID']=array();
		if (isset($_POST["disabledCategories"]) && strlen(trim($_POST["disabledCategories"]))) {
			$tArr = explode(",", $_POST["disabledCategories"]);
			foreach ($tArr as $key => $catID) {
				if (!in_array($catID, $_POST['product_category_id'])) {
					$_POST['product_category_id'][] = $catID;
				}
			}
		}
		if (isset($_POST["disabledSecondaryCategories"]) && strlen(trim($_POST["disabledSecondaryCategories"]))) {
			$tArr = explode(",", $_POST["disabledSecondaryCategories"]);
			foreach ($tArr as $key => $catID) {
				if (!in_array($catID, $_POST['product_scndcat_ID'])) {
					$_POST['product_scndcat_ID'][] = $catID;
				}
			}
		}
		if (isset($_POST["disabledOptions"]) && strlen(trim($_POST["disabledOptions"]))) {
			$tArr = explode(",", $_POST["disabledOptions"]);
			foreach ($tArr as $key => $optID) {
				if (!in_array($optID, $_POST['product_options'])) {
					$_POST['product_options'][] = $optID;
				}
			}
		}
		// call the update product function - see function arguments for details 		
		$updateProd = CWfuncProductUpdate($_POST['product_id'],
			$_POST['product_name'],
			$_POST['product_on_web'],
			$_POST['product_ship_charge'],
			$_POST['product_tax_group_id'],
			$_POST['product_sort'],
			$_POST['product_out_of_stock_message'],
			$_POST['product_custom_info_label'],
			$_POST['product_description'],
			$_POST['product_preview_description'],
			$_POST['product_special_description'],
			$_POST['product_keywords'],
			$_POST['hasOrders'],
			$_POST['product_options'],
			$_POST['product_category_id'],
			$_POST['product_scndcat_ID']);
		if(isset($_POST['productUpdateError']))
			CWpageMessage("alert",$_POST['productUpdateError']);
		else
			CWpageMessage("confirm","Product Details Saved");
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cw"]["thisPage"].'?productid='.$_POST['product_id'].'&showtab='.$_POST['returnTab'].'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]).'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));
		exit;
		// /////// 
		// /END UPDATE PRODUCT 
		// /////// 
		// /////// 
		// ADD PRODUCT 
		// /////// 
	}
	elseif(isset($_POST['action']) && $_POST['action'] == 'AddProduct') {
		// additional form params here, default values not desired unless adding/editing product 
		if(!(isset($_POST['product_options']))) $_POST['product_options']=array();
		if(!(isset($_POST['product_category_id']))) $_POST['product_category_id']=array();
		if(!(isset($_POST['product_scndcat_ID']))) $_POST['product_scndcat_ID']=array();
		if (isset($_POST["disabledCategories"]) && strlen(trim($_POST["disabledCategories"]))) {
			$tArr = explode(",", $_POST["disabledCategories"]);
			foreach ($tArr as $key => $catID) {
				if (!in_array($catID, $_POST['product_category_id'])) {
					$_POST['product_category_id'][] = $catID;
				}
			}
		}
		if (isset($_POST["disabledSecondaryCategories"]) && strlen(trim($_POST["disabledSecondaryCategories"]))) {
			$tArr = explode(",", $_POST["disabledSecondaryCategories"]);
			foreach ($tArr as $key => $catID) {
				if (!in_array($catID, $_POST['product_scndcat_ID'])) {
					$_POST['product_scndcat_ID'][] = $catID;
				}
			}
		}
		if (isset($_POST["disabledOptions"]) && strlen(trim($_POST["disabledOptions"]))) {
			$tArr = explode(",", $_POST["disabledOptions"]);
			foreach ($tArr as $key => $optID) {
				if (!in_array($optID, $_POST['product_options'])) {
					$_POST['product_options'][] = $optID;
				}
			}
		}
		// duplicate product - use dup name field instead (if defined and not blank) 		
		if(isset($_POST['product_DupName']) && (trim($_POST['product_DupName']) !='') && ($_POST['product_DupName'] !=$_POST['product_name']) ){
			$_POST['product_name']=$_POST['product_DupName'];
		}
		// call the add product function - see function arguments for details 
		$updateProd=CWfuncProductAdd(
			$_POST['product_merchant_product_id'],
			$_POST['product_name'],
			$_POST['product_on_web'],
			$_POST['product_ship_charge'],
			$_POST['product_tax_group_id'],
			$_POST['product_sort'],
			$_POST['product_out_of_stock_message'],
			$_POST['product_custom_info_label'],
			$_POST['product_description'],
			$_POST['product_preview_description'],
			$_POST['product_special_description'],
			$_POST['product_keywords'],
			$_POST['hasOrders'],
			$_POST['product_options'],
			$_POST['product_category_id'],
			$_POST['product_scndcat_ID']
		    );
		// if we have no errors, redirect showing SKUs tab 
		if(!(isset($_ENV["request.cwpage"]["productinsertError"])) && isset($_ENV["request.cwpage"]["newProductID"])) {
			CWpageMessage("confirm","Product Added");
			if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
			if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
			header("Location: ".$_ENV["request.cw"]["thisPage"].'?showtab=4&productid='.$_ENV["request.cwpage"]["newProductID"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));
		} elseif(isset($_ENV["request.cwpage"]["productinsertError"])) {
			// if the eror is because product already exists, put some js in the page head to show the right content 
			if(isset($_ENV["request.cwpage"]['productExists'])) {
				$headcontent = "<script type=\"text/javascript\">
	jQuery(document).ready(function() {
	var url = document.location.toString();
	// if no anchor defined in url
	if (!(url.match('#tab'))) {
		 jQuery('#CWadminTabWrapper ul.CWtabList > li:first > a').click();
	};
	jQuery('#productDupLink').click();
	jQuery('input#product_merchant_product_id').focus().select();
	});
	</script>";
				CWinsertHead($headcontent);
				// end if error  
				// /////// 
				// /END ADD PRODUCT 
				// ///////
			}
			CWpageMessage("alert","Error: ".$_ENV["request.cwpage"]["productinsertError"] );
		}
	}

    // /////// 
    // /END product actions 
    // /////// 
    // /////// 
    // START SKU ACTIONS 
    // /////// 
    // /////// 
    // DELETE SKU 
    // ///////
    if(isset($_GET['deletesku']) && $_GET['deletesku'] > 0) {
	$deletesku=CWfuncSKUDelete($_GET['deletesku']);

	if(isset($_POST['skuDeleteError'])) {
		CWpageMessage("alert","Error: ".$_POST['skuDeleteError']);
	} else {
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cw"]["thisPage"].'?productid='.$_GET["productid"].'&showtab=4&&skumode='.$_GET['skumode'].'&userconfirm=SKU%20Deleted');
	}	
	// /////// 
	// /END DELETE SKU 
	// /////// 
	// /////// 
	// UPDATE/DELETE MULTIPLE SKUS 
	// /////// 
} elseif(isset($_POST['sku_editmode']) &&  $_POST['sku_editmode'] == 'list') {
    //unused ?
//	foreach($form as $fn => $value) {
	foreach($_POST as $fn => $value ) {
	    if(substr($fn,0,3)=='sku') {
//		$form['skufields'][$fn]=$form[$fn];
		$_POST['skufields'][$fn]=$_POST[$fn];
	    }
	} 
    
	// make sure at least one sku id exists to update   	    
	$sku = $_POST['sku_id'];
	if (!is_array($sku) && strlen($sku)) $sku = explode(",", $sku);
	else if (!is_array($sku)) $sku = array();
	if(sizeof($sku) > 0) {
		// loop the list of sku ids, updating each one 
		$loopLen=sizeof($sku);
		$_ENV["request.cwpage"]["skuupdatect"]=0;
		$_ENV["request.cwpage"]["skudelct"]=0;     
		for($skuCt=1; $skuCt <= $loopLen;$skuCt++) {
			try {
				// catch errors 
				// if deleting the sku 
				if(isset($_POST['deletesku_id'.$skuCt])) {
				    				    
					$deletesku = CWfuncSKUDelete($_POST['deletesku_id'.$skuCt]);

					if(isset($_ENV["request.cwpage"]['skuDeleteError']))
						CWpageMessage("alert","Error: ".$_POST['skuDeleteError']);
					else
						$_ENV["request.cwpage"]["skudelct"] = $_ENV["request.cwpage"]["skudelct"] +1;
					// if not deleting, update here 
				 } else {

					$strOptions=array();
					// Loop throught the form collection and grab all of the chosen options 
					foreach($_POST as $FieldName => $FieldValue) {
						if( (substr($FieldName,0,9) =='selOption') && ( substr($FieldName,strrpos($FieldName,"_"))=='_'.$skuCt ) && ($FieldValue !='choose') ) 
						{
						    array_push($strOptions,$FieldValue);
						}
					}
					// add options to the form scope to keep things together 
					$_POST['SKU_StrOptions'.$skuCt]=$strOptions;
				
					if(!(isset($_POST['sku_on_web'.$skuCt]))) $_POST['sku_on_web'.$skuCt]=1;
					if(!(isset($_POST['sku_alt_price'.$skuCt]))) $_POST['sku_alt_price'.$skuCt]=0;
					
					// update the sku 				
					$updateSKU=CWfuncSkuUpdate(
						$_POST['sku_id'.$skuCt],
						$_POST['sku_product_id'],
						$_POST['sku_price'.$skuCt],
						$_POST['sku_ship_base'.$skuCt],
						$_POST['sku_alt_price'.$skuCt],
						$_POST['sku_weight'.$skuCt],
						$_POST['sku_stock'.$skuCt],
						$_POST['sku_on_web'.$skuCt],
						$_POST['sku_sort'.$skuCt],
						$_POST['SKU_StrOptions'.$skuCt]);
                                        
					if(isset($_ENV["request.cwpage"]["skuUpdateError"])) {
						CWpageMessage("alert","Error: ".$_ENV["request.cwpage"]["skuUpdateError"]);
						unset($_ENV["request.cwpage"]["skuUpdateError"]);
					} else {
						if ($_ENV["application.cw"]["appDownloadsEnabled"] && isset($_POST['sku_download_file'.$skuCt])) {
							if (!is_numeric($_POST['sku_download_limit'.$skuCt])) {
								$_POST['sku_download_limit'.$skuCt] = 0;
							}
							$updateSkuFile = CWqueryUpdateSkuFile(
										$_POST['sku_id'.$skuCt],
										$_POST['sku_download_file'.$skuCt],
										$_POST['sku_file_version'.$skuCt],
										$_POST['sku_download_id'.$skuCt],
										$_POST['sku_download_limit'.$skuCt]
									);
							// catch any errors 
							if ($updateSkuFile == 0) {
								CWpageMessage("alert", "Error: file attachment not updated");
							}
						}
						$_ENV["request.cwpage"]["skuupdatect"]=$_ENV["request.cwpage"]["skuupdatect"]+1;
					}
				}
			}
			catch(Exception $e) {
				// handle page errors 
				CWpageMessage("alert","Error: invalid data for sku".$_POST['sku_id'.$skuCt].' '.$e);	
			}						
		}
		// if at least one update or delete was done, redirect showing alerts 
		if($_ENV["request.cwpage"]["skuupdatect"] >0 || $_ENV["request.cwpage"]["skudelct"] > 0) {
			CWpageMessage("confirm","Changes Saved");
			if($_ENV["request.cwpage"]["skudelct"] > 0)
				CWpageMessage("alert",$_ENV["request.cwpage"]["skudelct"].' SKUs deleted');
			if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
			if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
			header("Location: ".$_ENV["request.cw"]["thisPage"].'?showtab=4&productid='.$_GET["productid"].'&skumode='.$_GET['skumode'].'&sortby='.$_GET['sortby'].'&sortdir='.$_GET['sortdir'].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
		}
		// if no sku ids were submitted 
	} else {
		CWpageMessage("alert","Error: No sku data submitted");
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cw"]["thisPage"].'?showtab=4&productid='.$_GET["productid"].'&skumode='.$_GET['skumode'].'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]));	
	}
	// /end make sure sku id exists 
	// /////// 
	// UPDATE SINGLE SKU 
	// /////// 
} elseif(isset($_POST['sku_editmode']) && $_POST['sku_editmode'] == 'standard' && !(strlen(trim($_POST['sku_merchant_sku_id'])))) {
	$strOptions=array();
	// Loop throught the form collection and grab all of the chosen options 
	foreach($_POST as $FieldName => $FieldValue ) {
		if(substr($FieldName,0,9) == 'selOption' && $FieldValue != 'choose'){
			array_push($strOptions,$FieldValue);
		}
	}	
	
	// add options to the form scope to keep things together 
	$_POST['SKU_StrOptions']=$strOptions;
	$tempSku = $_POST['sku_id'];
	if (is_array($tempSku)) $tempSku = implode(",", $tempSku);
	$updateSKU= CWfuncSkuUpdate(
		$_POST['sku_id'],
		$_POST['sku_product_id'],
		$_POST['sku_price'],
		$_POST['sku_ship_base'],
		$_POST['sku_alt_price'],
		$_POST['sku_weight'],
		$_POST['sku_stock'],
		$_POST['sku_on_web'],
		$_POST['sku_sort'],
		$_POST['SKU_StrOptions']);
	if ($_ENV["application.cw"]["appDownloadsEnabled"] && isset($_POST['sku_download_file'])) {
		if (!is_numeric($_POST['sku_download_limit'])) {
			$_POST['sku_download_limit'] = 0;
		}
		$updateSkuFile = CWqueryUpdateSkuFile(
                    $_POST['sku_id'],
                    $_POST['sku_download_file'],
                    $_POST['sku_file_version'],
                    $_POST['sku_download_id'],
                    $_POST['sku_download_limit']
                );
		// catch any errors 
		if ($updateSkuFile == 0) {
			CWpageMessage("alert", "Error: file attachment not updated");
		}
	}
	if(isset($_ENV["request.cwpage"]["skuUpdateError"]))
		CWpageMessage("alert","Error: ".$_ENV["request.cwpage"]["skuUpdateError"]);
	else {
		CWpageMessage("confirm","SKU Updated");
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cw"]["thisPage"].'?showtab=4&productid='.$_GET["productid"].'&skumode='.$_GET['skumode'].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));	
	}
	// /////// 
	// /END UPDATE SKU 
	// /////// 
	// /////// 
	// ADD SKU 
	// /////// */
    } elseif(isset($_POST['newsku'])) {
	$strOptions = array();
	// Loop through the form collection and grab all of the chosen options 

	foreach($_POST as $FieldName => $FieldValue ) {
		if(substr($FieldName,0,9) == 'selOption' && $FieldValue != 'choose'){
			array_push($strOptions,$FieldValue);
		}
	}
	
	$_POST['SKU_StrOptions']=$strOptions;
	// add options to the form scope to keep things together 
	$insertSKU = CWfuncSkuAdd(
		$_POST['sku_merchant_sku_id'],
		$_POST['sku_product_id'],
		$_POST['sku_price'],
		$_POST['sku_ship_base'],
		$_POST['sku_alt_price'],
		$_POST['sku_weight'],
		$_POST['sku_stock'],
		$_POST['sku_on_web'],
		$_POST['sku_sort'],
		$_POST['SKU_StrOptions']
		);
	if ($insertSKU && $_ENV["application.cw"]["appDownloadsEnabled"] && isset($_POST['sku_download_file'])) {
		if (!is_numeric($_POST['sku_download_limit'])) {
			$_POST['sku_download_limit'] = 0;
		}
		$updateSkuFile = CWqueryUpdateSkuFile(
                    $insertSKU,
                    $_POST['sku_download_file'],
                    $_POST['sku_file_version'],
                    $_POST['sku_download_id'],
                    $_POST['sku_download_limit']
                );
		// catch any errors 
		if ($updateSkuFile == 0) {
			CWpageMessage("alert", "Error: file attachment not updated");
		}
	}
	if(!(isset($_ENV["request.cwpage"]['skuInsertError'])) && isset($_GET["productid"])) {
		CWpageMessage("confirm","SKU Added ");
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cw"]["thisPage"].'?showtab=4&productid='.$_GET["productid"].'&skumode='.$_GET['skumode'].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));
	// if we have an error, show the error, and show the add sku form again 
	}
	elseif($_ENV["request.cwpage"]['skuInsertError']) {
		// put the code to click New SKU link in the page (see above) 
		CWinsertHead($clickNewSkuCode);
		CWpageMessage("alert","Error: ".$_ENV["request.cwpage"]['skuInsertError']);
	}
	// /////// 
	// /END ADD SKU 
	// /////// 
	// /////// 
	// COPY SKU 
	// /////// */
    } elseif($_POST['sku_merchant_sku_id'] && strlen(trim($_POST['sku_merchant_sku_id']))) {
	// just like adding a sku above 
	// Loop through the form collection and grab all of the chosen options */
	$strOptions = array();
	
	foreach($_POST as $FieldName => $FieldValue ) {
		if(substr($FieldName,0,9) == 'selOption' && $FieldValue != 'choose'){
			array_push($strOptions,$FieldValue);
		}
	}
	
	// add options to the form scope to keep things together 
	$_POST['SKU_StrOptions']=$strOptions;
	$insertSKU = CWfuncSkuAdd(
		$_POST['sku_merchant_sku_id'],
		$_POST['sku_product_id'],
		$_POST['sku_price'],
		$_POST['sku_ship_base'],
		$_POST['sku_alt_price'],
		$_POST['sku_weight'],
		$_POST['sku_stock'],
		$_POST['sku_on_web'],
		$_POST['sku_sort'],
		$_POST['SKU_StrOptions']);

	if(!(isset($_POST['skuInsertError'])) && isset($_GET["productid"])) {
		CWpageMessage("confirm","SKU Copied");
		CWpageMessage("confirm","Change at least one product option and save the new sku to prevent duplication");
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cw"]["thisPage"].'?showtab=4&productid='.$_GET["productid"].'&skumode='.$_GET['skumode'].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));
	// if we have an error, show the error, and show the add sku form again 
	}
	elseif(isset($_POST['skuInsertError'])) {
		CWinsertHead($clickNewSkuCode);
		CWpageMessage("alert","Error: ".$_POST['skuInsertError']);
	}
	// /////// 
	// /end sku actions
	// /////// 
    } elseif(isset($_POST['skuInsertError']) && strlen(trim($_ENV["request.cwpage"]["AddSKUError"]))) {
		CWinsertHead($clickNewSkuCode);
    }
}
// /end error check - actions ok 
	
// QUERIES 
// QUERY: get list of options 
$productOptionsQuery=CWquerySelectOptions();

// QUERY: get selected options for this product (product ID) 
$productOptionsRelQuery=CWquerySelectOptions($_ENV["request.cwpage"]["productlookupid"]);


$skusQuery = CWquerySelectSkus($_ENV["request.cwpage"]["productlookupid"],true);
// list of sku IDs (used to check for orders below) 
if($skusQuery['totalRows']) {
	$SKUList=implode(',',$skusQuery['sku_id']);
	// if no skus, we can't show the upsell form yet 
} else {
	$_ENV["request.cwpage"]["showUpsell"] = 0;
	if($_GET["productid"] > 0)
		CWpageMessage("alert","Create at least one SKU to activate this product");
}
$listC=CWquerySelectCategories();
$listSC = CWquerySelectScndCategories();
$listProdCats = CWquerySelectRelCategories($_ENV["request.cwpage"]["productlookupid"]);
$listProdScndCats = CWquerySelectRelScndCategories($_ENV["request.cwpage"]["productlookupid"]);
$listProdImages = CWquerySelectProductImages($_ENV["request.cwpage"]["productlookupid"]);
$listImageTypes = CWquerySelectImageTypes();
$listUploadGroups = CWquerySelectImageUploadGroups();
$listTaxGroups = CWquerySelectTaxGroups();
// NEW VS EDIT
// if one valid product is found 
if($detailsQuery['totalRows'] == 1) {	
	$_ENV["request.cwpage"]["editMode"]='edit';
	$_ENV["request.cwpage"]["productName"]=htmlspecialchars($_POST['product_name']);
	// QUERY: count orders based on the list of skus
	$_ENV["request.cwpage"]["orderCount"]=CWqueryCountSKUOrders($SKUList);

	if($_ENV["request.cwpage"]["orderCount"] > 0) {
		$ProductHasOrders=1;
		$disabledText= " disabled=\"disabled\"";
	}
	// get upsell list 
	$productUpsellQuery= CWquerySelectUpsellProducts($_ENV["request.cwpage"]["productlookupid"],true);
	
	// get reciprocal upsell list 
	$productReciprocalUpsellQuery=CWquerySelectReciprocalUpsellProducts($_ENV["request.cwpage"]["productlookupid"], true);
	for($i=0;$i < $detailsQuery['totalRows'];$i++ ) {
		$_ENV["request.cwpage"]["subHead"] = "ID: ".$detailsQuery['product_merchant_product_id'][$i].
							"&nbsp;&nbsp;SKUs: ".$skusQuery['totalRows'].
							"&nbsp;&nbsp;Orders: ". $_ENV["request.cwpage"]["orderCount"];
		if(!isset($_ENV["request.cwpage"]["showUpsell"])) {
			$_ENV["request.cwpage"]["subHead"].= '&nbsp;&nbsp;Related Products:' .$productUpsellQuery['totalRows'];
		}
	}
	// IF ADDING NEW PRODUCT */
} else {
	$_ENV["request.cwpage"]["editMode"]='add';
}
// SET UP OPTIONS  / CATEGORIES 
$listProductOptions='';
if(isset($_POST['product_options'])){
	$listProductOptions = implode(",", $_POST['product_options']);
} else if ($_ENV["request.cwpage"]["editMode"] == 'edit') {
	// modified to eliminate duplicate entries
	for( $i = 0; $i < $productOptionsRelQuery['totalRows']; $i++ ) {
		if (!ListFind($listProductOptions,$productOptionsRelQuery['optiontype_id'][$i])) {
		    if ($listProductOptions != '') $listProductOptions .= ',';
			$listProductOptions .= $productOptionsRelQuery['optiontype_id'][$i];
		}
	}
}
// put in request scope for use in skus include 
$_ENV["request.cwpage"]['listProductOptions']=$listProductOptions;

// Create a list of assigned categories for the select menus 
$listRelCats='';
if(isset($_POST['product_category_id'])){
	$listRelCats = implode(",", $_POST['product_category_id']);
} elseif( isset($listProdCats['product2category_category_id']) && is_array($listProdCats['product2category_category_id']) ) {
	$listRelCats = implode(',',$listProdCats['product2category_category_id']);
}

// Create a list of assigned secondary categories for the select menus 
$listRelScndCats='';
if(isset($_POST['product_scndcat_ID'])) {
	$listRelScndCats = implode(",", $_POST['product_scndcat_ID']);
} elseif( isset($listProdScndCats['product2secondary_secondary_id']) && is_array($listProdScndCats['product2secondary_secondary_id']) ) {
	$listRelScndCats = implode(',',$listProdScndCats['product2secondary_secondary_id']);
}
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"]='Product Details: '.$_ENV["request.cwpage"]["productName"];
// Page Main Heading <h1> */
$_ENV["request.cwpage"]["heading1"]='Product Details: '.$_ENV["request.cwpage"]["productName"];
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"]=$_ENV["request.cwpage"]["subHead"];
// current menu marker 
if($_ENV["request.cwpage"]["editMode"]=='edit') {
	$_ENV["request.cwpage"]["currentNav"]='products.php';	
}
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"]=1;
// load table scripts
$_ENV["request.cwpage"]["isTablePage"]=1;
// dynamic form elements, save as variables for use on multiple tabs 
if($_ENV["request.cwpage"]["editMode"]=='edit') {
	$_ENV["request.cwpage"]["productSubmitButton"] ='<input name="updateProduct" type="button" class="submitButton" rel="productForm" id="updateProduct" value="Save Product">';	
	$_ENV["request.cwpage"]["productArchiveButton"]='<a class="CWbuttonLink" onClick="return confirm('."'Archive Product ".$_ENV["request.cwpage"]["productName"]."?')".';"'.' title="Archive Product: '.$_ENV["request.cwpage"]["productName"].'" href="products.php?archiveid='.$_ENV["request.cwpage"]["productlookupid"].'">Archive Product</a>';
	$_ENV["request.cwpage"]["productActivateButton"]='<a class="CWbuttonLink" title="Reactivate Product: '.$_ENV["request.cwpage"]["productName"].'" href="products.php?reactivateid='.$_ENV["request.cwpage"]["productlookupid"].'">'."Activate Product</a>";
	$_ENV["request.cwpage"]["productDeleteButton"]='<a class="CWbuttonLink deleteButton" onClick="return confirm'."('Delete Product ".$_ENV["request.cwpage"]["productName"]. " and all related information?'".');" title="Delete Product: '."'".$_ENV["request.cwpage"]["productName"].'" href="product-details.php?deleteproduct='.$_ENV["request.cwpage"]["productlookupid"].'">Delete Product</a>';
}
// START OUTPUT ?>
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
			<html>
        <head>
            <title><?php echo $_ENV["application.cw"]["companyName"].' : '.$_ENV["request.cwpage"]["title"];?></title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <?php // Don't Cache content ?>
            <meta http-equiv="Cache-Control" content="no-cache">
            <meta http-equiv="Pragma" content="no-cache">
            <!-- admin styles -->
            <link href="css/cw-layout.css" rel="stylesheet" type="text/css">
            <link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
            
            <!-- admin javascript -->
            <?php
        	include('cwadminapp/inc/cw-inc-admin-scripts.php');
		?>
            <link href="js/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css">
            <script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js"></script>
            <?php
            // text editor javascript 
        	if($_ENV["application.cw"]["adminEditorEnabled"] && $_ENV["application.cw"]["adminEditorProductDescrip"])
				include('cwadminapp/inc/cw-inc-admin-script-editor.php');
            // PAGE JAVASCRIPT ?>
            <script type="text/javascript">
		jQuery(document).ready(function(){
		// fancybox
		jQuery('a.zoomImg').each(function(){
			jQuery(this).fancybox({
			'titlePosition': 'inside',
			'padding': 3,
			'overlayShow': true,
			'showCloseButton': true,
			'hideOnOverlayClick':true,
			'hideOnContentClick': true
			});
		});
		// upload image
		jQuery('a.showImageUploader').click(function(){
			var thisSrcUrl = jQuery(this).attr('href');
		jQuery(this).parents('td').find('div.imageUpload').show().children('iframe').attr('src',thisSrcUrl);
		jQuery(this).parents('td').find('img.productImagePreview').hide();
		return false;
		});
		// end upload image

		// select image
		jQuery('a.showImageSelector').click(function(){
			var thisSrcUrl = jQuery(this).attr('href');
		jQuery(this).parents('td').find('div.imageUpload').show().children('iframe').attr('src',thisSrcUrl);
		jQuery(this).parents('td').find('img.productImagePreview').hide();
		return false;
		});
		// end select image

		// clear image
		jQuery('#tab3 .clearImageLink').click(function(){
		jQuery(this).parents().siblings('input.imageInput').val('').siblings('img.productImagePreview').attr('src','');
		jQuery(this).parents('td').find('div.imageUpload').hide().children('iframe').attr('src','');
		jQuery(this).parents('td').find('img.productImagePreview').attr('src','').attr('alt','').hide();
		return false;
		});
		// end clear image

		// duplicate product
		// show the dup product form
		jQuery('#productDupLink').click(function(){
		jQuery('#productDup').show();
		jQuery(this).hide();
		return false;
		});
		// submit the dup product form
		jQuery('#productDup_Submit').click(function(){
			jQuery('#productFormAction').val('AddProduct');
			jQuery('#productForm').submit();
		});
		// end duplicate product

		// duplicate SKU
		// show the dup sku form
		jQuery('#tab4 div.CWformButtonWrap a.skuDupLink').click(function(){
		jQuery(this).parents('div').siblings('#skuDup').toggle();
		return false;
		});
		// end duplicate SKU

		// tab selectors
		jQuery('#tab1complete').click(function(){
		jQuery('#CWadminTabWrapper ul.CWtabList > li:nth-child(2) > a').click();
		jQuery( 'html, body' ).animate( { scrollTop: 0 }, 0 );
		});
		jQuery('#tab2complete').click(function(){
		jQuery('#CWadminTabWrapper ul.CWtabList > li:nth-child(3) > a').click();
		jQuery( 'html, body' ).animate( { scrollTop: 0 }, 0 );
		});
		// tab return value for product form
		jQuery('#CWadminTabWrapper ul.CWtabList > li > a').click(function(){
			if (jQuery(this).attr('href') == '#tab1'){
			 jQuery('input.returnTab').attr('value','1')
			 } else if 	(jQuery(this).attr('href') == '#tab2'){
			 jQuery('input.returnTab').attr('value','2')
			 } else if 	(jQuery(this).attr('href') == '#tab3'){
			 jQuery('input.returnTab').attr('value','3')
			 } else if 	(jQuery(this).attr('href') == '#tab4'){
			 jQuery('input.returnTab').attr('value','4')
			 } else if 	(jQuery(this).attr('href') == '#tab5'){
			 jQuery('input.returnTab').attr('value','5')
			 };
		});
		// end tab selectors

		// sku links
		jQuery('#hideNewSkuFormLink').hide();
		jQuery('#showNewSkuFormLink').click(function(){
		jQuery(this).hide();
<?php
if($skusQuery['totalRows'] > 0) {
?>	
				jQuery(this).siblings('a').show();
<?php
}
?>
			jQuery('#addSkuForm').show();
			return false;
			});
		jQuery('#hideNewSkuFormLink').click(function(){
		jQuery(this).hide();
<?php
if($skusQuery['totalRows'] > 0) {
?>
				jQuery(this).siblings('a').show();
<?php
}
?>
			jQuery('#addSkuForm').hide();
			return false;
			});
			// show sku form if no skus exist yet
<?php
if($skusQuery['totalRows'] < 1 ) {
?>
				jQuery('#showNewSkuFormLink').click();
<?php
}
?>
			// end sku links
			// end upsell search form
			// upsell two-way checkboxes
			// function to click the box in the 'firstCheck' cell
			var $recipCheck = function(el){
			if (jQuery(el).attr('checked') == true || jQuery(el).attr('checked') == 'checked'){
			jQuery(el).parents('td').siblings('td.firstCheck').children('input[type=checkbox]').prop('checked',true);
			};
			};
			var $firstCheck = function(el){
			if (!(jQuery(el).attr('checked') == true || jQuery(el).attr('checked') == 'checked')){
			jQuery(el).parents('td').siblings('td.recipCheck').children('input[type=checkbox]').prop('checked',false);
			};
			};
			// run the function when clicking the two-way checkbox
			jQuery('td.recipCheck input[type=checkbox]').click(function(){
			$recipCheck(jQuery(this));
			});
			jQuery('td.firstCheck input[type=checkbox]').click(function(){
			$firstCheck(jQuery(this));
			});
			// run the function when clicking the two-way parent cell
			jQuery('td.recipCheck').click(function(event){
			if (event.target.type != 'checkbox') {
			$recipCheck(jQuery(this).children('input[type=checkbox]'));
			}
			});
			// run the function when clicking the two-way parent cell
			jQuery('td.firstCheck').click(function(event){
			if (event.target.type != 'checkbox') {
			$firstCheck(jQuery(this).children('input[type=checkbox]'));
			}
			});
			// run the function when clicking the two-way parent cell
			jQuery('input[rel=all2]').click(function(){
			jQuery('input.all2').each(function(){
			$recipCheck(jQuery(this));
			});
			});
			// run the function when clicking the two-way parent cell
			jQuery('input[rel=all1]').click(function(){
			jQuery('input.all1').each(function(){
			$firstCheck(jQuery(this));
			});
			});
			// end upsell checkboxes
			// upsell click-to-select
			jQuery('#tblUpsellSelect tr td').not(':has(a),:has(input)').css('cursor','pointer').click(function(event){
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
			jQuery('#relProdAll1').click(function(){
			if(jQuery(this).attr('checked')!=true){
			jQuery('#relProdAll2').prop('checked',false);
			};
			});
			jQuery('#relProdAll2').click(function(){
			if(jQuery(this).prop('checked')==true){
			jQuery('#relProdAll1').prop('checked',true);
			};
			});
			// end upsell click-to-select
			// only run this section for blank new product form
<?php
if($_ENV["request.cwpage"]["editMode"]=='add' && !(isset($_POST['action']))) {
?>
				// reset form to prevent cached values
				jQuery('#tab3 input.imageInput').val('');
<?php
}
?>
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
<?php
include("cwadminapp/inc/cw-inc-admin-nav.php");
?>
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

if(strlen(trim($_ENV["request.cwpage"]["heading1"])))
	echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]).'</h1>';    	
if(strlen(trim($_ENV["request.cwpage"]["heading2"]))) {
	echo '<h2>'.trim($_ENV["request.cwpage"]["heading2"]).'</h2>';    	
}
// user alerts 
include("cwadminapp/inc/cw-inc-admin-alerts.php");   		
?>
            <!-- Page Content Area -->
            <div id="CWadminContent">
            <!-- //// PAGE CONTENT ////  --> 
            <?php // include the search form ?>
            <div id="CWadminProductSearch" class="CWadminControlWrap">
<?php
include("cwadminapp/inc/cw-inc-search-product.php"); 			    
?>
        </div>
            <!-- TABBED LAYOUT -->
            <div id="CWadminTabWrapper">
<?php
// if product is archived, show message and option to activate 
if($_ENV["request.cwpage"]["editMode"]=='edit' && $detailsQuery['product_archive']==1 ) {
?>
           	 <p><strong>This product is archived. Reactivate to allow editing.</strong><br>
         	 <br>
        		</p>
            <p><?php echo $_ENV["request.cwpage"]["productActivateButton"];?></p>
<?php
} else {
?>
            <!-- TAB LINKS -->
            <ul class="CWtabList">
          <!-- main tab -->
          <li><a href="#tab1" title="General Information">Product Details</a></li>
          <!-- descrip -->
          <li><a href="#tab2" title="Descriptions">Descriptions</a></li>
          <!-- photos -->
          <li><a href="#tab3" title="Photos">Photos</a></li>
          <!-- SKUs -->
          <li<?php if($_ENV["request.cwpage"]["editMode"]=='add') { echo ' style="display:none"'; } ?>><a href="#tab4" title="SKUs">SKUs</a></li>
<?php 
	if($_ENV["request.cwpage"]["showUpsell"]) {
?>
          <li<?php if($_ENV["request.cwpage"]["editMode"] =='add') { echo ' style="display:none"'; } ?>><a href="#tab5" title="Related Products">Related Products</a></li>
<?php
	}
?>
        	</ul>
            <?php // TAB CONTENT ?>
            <div class="CWtabBox">
            <?php
            // ///////  
            // PRODUCT FORM  
            // /////// ?>
            <form method="post" id="productForm" name="productForm" class="CWvalidate CWobserve" action="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?productid=<?php echo $_GET['productid']; ?>&showtab=<?php echo $_GET['showtab']; ?>">
            <div id="tab1" class="tabDiv">
          <h3>General Information</h3>
          <?php // Form Table ?>
          <table class="CWformTable wide">
                <?php // product_id ?>
                <tr>
              <th class="label">ID (Part No.)</th>
              <td class="noHover">
<?php
	// if adding a new product 
	if($_ENV["request.cwpage"]["editMode"]=='add') {
?>
                    <input name="product_merchant_product_id" type="text" id="product_merchant_product_id" value="<?php echo $_POST['product_merchant_product_id']; ?>"  class="required" title="Product ID is required" size="25">
                    <input name="action" type="hidden" id="productFormAction" value="AddProduct">
<?php
	} else {
		// if editing a product
?>
                    <span class="formText"><?php echo $detailsQuery['product_merchant_product_id'][0]; ?></span>
                    <input name="product_id" type="hidden" value="<?php echo htmlspecialchars($detailsQuery['product_id'][0]); ?>">
                    <input name="action" type="hidden" id="productFormAction" value="updateProduct">
                    <?php // duplicate product ?>
                    <a href="#" id="productDupLink" class="CWbuttonLink">Copy Product</a>
                    <div id="productDup" style="display:none;">
                  <?php
                  // productDup_MerchantProductID  
                  // productDup_Name ?>
                  <label>New Product ID: </label><input id="product_merchant_product_id" name="product_merchant_product_id" type="text" value="<?php echo $detailsQuery['product_merchant_product_id'][0];?>" size="25"><br>
                  <label>New Product Name: </label><input id="product_DupName" name="product_DupName" type="text" value="<?php echo $detailsQuery['product_name'][0];?>" size="25"><br>
                  <input name="AddProduct" type="button" class="submitButton" id="productDup_Submit" value="Copy Product">
                </div>
<?php
	}
?>
                    </td>
            </tr>
                <?php // product_name ?>
                <tr>
		    <th class="label">Display Name</th>
		    <td><input name="product_name" type="text" value="<?php echo htmlspecialchars($_POST['product_name']);?>" size="30" class="required" title="Product Name is required"></td>
		</tr>
                <?php // product_on_web ?>
                <tr>
              <th class="label">Show In Store</th>
              <td><select name="product_on_web">
<?php
	if($_POST['product_on_web'] != "0") {
?>
                  <option value="1" selected="selected">Yes
                <option value="0">No
<?php
	} else {
?>
                <option value="1" >Yes
                <option value="0" selected="selected">No
<?php
	}
?>
              </select></td>
            </tr>
                <?php // product_ship_charge ?>
                <tr>
              <th class="label">Charge Shipping</th>
              <td><select name="product_ship_charge">
<?php
	if($_POST['product_ship_charge'] != "0") {
?>
		    <option value="1" selected >Yes
		    <option value="0">No
<?php
	} else {
?>
		    <option value="1">Yes
		    <option value="0" selected >No
<?php
	}
?>
              </select></td>
            </tr>
<?php // product_tax_group_id 
	if(isset($_ENV["application.cw"]["taxSystem"]) && strtolower($_ENV["application.cw"]["taxSystem"]) =='groups') {
?>
                <tr>
              <th class="label"><?php echo $_ENV["application.cw"]["taxSystemLabel"];?> Group</th>
              <td><select name="product_tax_group_id">
<?php
		if (strtolower($_ENV["application.cw"]["taxCalctype"]) == "localtax") {
?>
                  <option value="0">No <?php echo $_ENV["application.cw"]["taxSystemLabel"];?></option>
<?php
		}                           	
		for($i=0;$i < $listTaxGroups['totalRows'];$i++) {
?>
                  <option value="<?php echo $listTaxGroups['tax_group_id'][$i];?>" <?php if($_POST['product_tax_group_id'] == $listTaxGroups['tax_group_id'][$i] ) {?> selected="selected"<?php } ?> > <?php echo $listTaxGroups['tax_group_name'][$i]?></option>
<?php
		}
?>
                </select></td>
            </tr>
<?php
	} else {
?>
                <input type="hidden" name="product_tax_group_id" value="0">
<?php
	}
	// product_sort 
?>
                <tr>
              <th class="label">Results Sort Order</th>
              <td>
<?php
	// change null sort to 0 
	if(!isset($_POST["product_sort"]) || $_POST['product_sort']=='') $_POST['product_sort'] = 0;
?>
                    <input name="product_sort" type="text" class="sort" value="<?php echo $_POST['product_sort']; ?>" maxlength="7" size="5" onBlur="checkValue(this)" onKeyUp="extractNumeric(this,2,true)">
                    <div class="smallPrint"> Order in results listings (1-9999, 0 = default/abc) </div></td>
            </tr>
                <?php // categories ?>
                <tr>
              <th class="label"> <?php echo $_ENV["application.cw"]["adminLabelCategories"];?>
                    </th>
		    <td>
<?php
		$disabledBoxes = '';
		$splitC = 0;
?>
			<div class="formSubCol"> 
<?php
		$catsArchived = 0;
		$disabledValues = array();
		for ($i = 0; $i < $listC['totalRows']; $i++) {
			$checkboxCode = '';
			$checkboxCode .='<label ';
			if ($listC['category_archive'][$i] == 1)
				$checkboxCode .= ' class="disabled"';
			$checkboxCode .= '><input type="checkbox" name="product_category_id['.$i.']" value="' . $listC['category_id'][$i] . '" ';
			if ($listC['category_archive'][$i] == 1) {
				$checkboxCode .= ' disabled="disabled" ';
				$catsArchived = 1;
			}
			if (ListFind($listRelCats, $listC['category_id'][$i], ",") === TRUE ) {
				$checkboxCode .= ' checked="checked" ';
				if ($listC['category_archive'][$i] == 1) {
					$disabledValues[] = $listC['category_id'][$i];
				}
			}
			$checkboxCode .= '/>' . $listC['category_name'][$i] . '</label><br> ';
			if ($i + 1 >= ($listC['totalRows'] / 2 - .5) && $splitC == 0 && !($listC['category_name'][$i] == 1)) {
				$splitC = 1;
				$checkboxCode .='</div> <div class="formSubCol"> ';
			} 
			// show enabled cats first, then archived 
			if (!($listC['category_archive'][$i] == 1)) {
				echo $checkboxCode;
			} else {
				$disabledBoxes = $disabledBoxes . $checkboxCode;
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
		}
		if (sizeof($disabledValues)) {
			echo '<input type="hidden" name="disabledCategories" value="'.implode(",", $disabledValues).'" />';
		}
		// if some cats are archived, show note 
		if ($catsArchived == 1) {
?>
    			<div class="smallPrint"> Archived <?php echo strtolower($_ENV["application.cw"]["adminLabelCategories"]) ?>
    			    are disabled. <br>
    			    <a href="categories-main.php">Activate</a> <?php echo strtolower($_ENV["application.cw"]["adminLabelCategories"]) ?>
    			    to select. </div>
<?php
		}
?>
                    </td>
            </tr>
                <?php // secondary categories ?>
                <tr>
              <th class="label"><?php echo $_ENV["application.cw"]["adminLabelSecondaries"]; ?></th>
              <td>
<?php
		$disabledBoxes='';
		$splitSC=0; 
?>
                    <div class="formSubCol">
<?php
		$disabledValues = array();
		for($i=0;$i < $listSC['totalRows'];$i++ ) {	
			$checkboxCode='<label ';
			if($listSC['secondary_archive'][$i] == 1)
				$checkboxCode .=' class="disabled"';
			$checkboxCode .= "><input type=\"checkbox\" name=\"product_scndcat_ID[".$i."]\" value=\"".$listSC['secondary_id'][$i].'" ';
			if($listSC['secondary_archive'][$i] == 1) { 
				$checkboxCode .= ' disabled="disabled"';
				$scndcatsArchived=1;
			}
			if(ListFind($listRelScndCats,$listSC['secondary_id'][$i],",") === TRUE ) {
				$checkboxCode .= ' checked="checked"';
				if ($listSC['secondary_archive'][$i] == 1) {
					$disabledValues[] = $listC['secondary_id'][$i];
				}
			}
			$checkboxCode .= "/> ".$listSC['secondary_name'][$i].'</label><br>';
			if($i+1 >=( $listSC['totalRows']/2 - .5   ) && $splitSC == 0 && !($listSC['secondary_archive'][$i] == 1)) {
				$splitSC=1;
				echo "</div><div class=\"formSubCol\">";
			}									
			if(!($listSC['secondary_archive'][$i]==1)) {
				echo $checkboxCode;
			} else {
				$disabledBoxes=$disabledBoxes.$checkboxCode;
			}
		}
?>
                    </div>
<?php
		if(strlen(trim($disabledBoxes))) {
?>
                    <div class="clear"></div>
<?php
			echo $disabledBoxes;
		}
		if (sizeof($disabledValues)) {
			echo '<input type="hidden" name="disabledSecondaryCategories" value="'.implode(",", $disabledValues).'" />';
		}
		if(isset($scndcatsArchived)) {
?>
                    <div class="smallPrint"> Archived <?php echo strtolower($_ENV["application.cw"]["adminLabelSecondaries"]);?> are disabled. <br>
                  <a href="categories-secondary.php">Activate</a> <?php echo strtolower($_ENV["application.cw"]["adminLabelSecondaries"])?> to select. </div>
<?php 
		}
?>
                    </td>
            </tr>
                <?php // product_options ?>
                <tr>
              <th class="label">Product Options</th>
              <td>
<?php
		// if some options exist for this product 
		if($productOptionsQuery['totalRows']) {
			$splitO=0;
?>
			<div class="formSubCol"> 
<?php
			$disabledValues = array();
			for($i=0;$i< $productOptionsQuery['totalRows'];$i++) {  
?>			   
			    <label<?php if(strlen(trim($disabledText))) { ?> class="disabled"<?php } ?>>
				<input type="checkbox" name="product_options[<?php echo $i; ?>]" value="<?php echo $productOptionsQuery['optiontype_id'][$i];?>" <?php echo $disabledText;
				if( ListFind($listProductOptions,$productOptionsQuery['optiontype_id'][$i],",") === TRUE ) { 
					echo ' checked="checked"';
					if (strlen(trim($disabledText))) {
						$disabledValues[] = $productOptionsQuery['optiontype_id'][$i];
					}
				}?>>  &nbsp; <?php echo  $productOptionsQuery['optiontype_name'][$i] ?> 
			    </label>
			    <br>
<?php 
				// break into two columns 	
				if(($i+1 >=($productOptionsQuery['totalRows']/2 -.5)) && ($splitO == 0) && (strlen(trim($disabledText))==0) ) {		
					$splitO=1;
					// create new div in code output to page  
					echo '</div> <div class="formSubCol">';
				}
			}
			if (sizeof($disabledValues)) {
				echo '<input type="hidden" name="disabledOptions" value="'.implode(",", $disabledValues).'" />';
			}
?> 
			</div>
<?php
			// if we have orders, no changes are allowed 
			if($ProductHasOrders==1) {
?>
			    <div class="smallPrint">Orders placed, unable to remove or change options</div>
<?php 
			} 
		} else {
?>
                       <span class="formText">No options available</span>
<?php
		}
?>					
                    </td>
            </tr>
<?php
		// product_out_of_stock_message  
		// if the message is blank, and we have a default, use that instead 
		if($_ENV["request.cwpage"]["editMode"]=='add' && !strlen(trim($_POST['product_out_of_stock_message']))) {
			$_POST['product_out_of_stock_message']=$_ENV["application.cw"]["adminProductDefaultBackOrderText"];
		}
?>
                <tr>
              <th class="label">Out of Stock Message</th>
              <td><input name="product_out_of_stock_message" type="text" value="<?php echo htmlspecialchars($_POST['product_out_of_stock_message']);?>" size="45">
                    
                    <div class="smallPrint">Shown in place of 'add to cart' button if back orders are allowed and stock for all SKUs is 0. <br>
                  Note: leave blank to disable this function.</div></td>
            </tr>
<?php
		// product_custom_info_label  
		// if the message is blank, and we have a default, use that instead 
		if($_ENV["application.cw"]["adminProductCustomInfoEnabled"]) {
			if($_ENV["request.cwpage"]["editMode"]=='add' && !(strlen(trim($_POST['product_custom_info_label'])))) {
				$_POST['product_custom_info_label']=$_ENV["application.cw"]["adminProductDefaultCustomInfo"];
			}
?>
                <tr>
              <th class="label">Custom Info Label</th>
              <td><input name="product_custom_info_label" type="text" value="<?php echo htmlspecialchars($_POST['product_custom_info_label']);?>" size="30">
                  <div class="smallPrint">Allows customer to attach custom info to each product added to cart.<br>
                  Note: leave blank to disable this function.</div></td>
            </tr>
<?php
		}
?>
              </table>
          <!-- FORM BUTTONS -->
          <div class="CWformButtonWrap" <?php if($_ENV["request.cwpage"]["editMode"] == 'add') { ?>style="text-align:center"<?php } ?>> 
<?php
		if($_ENV["request.cwpage"]["editMode"]=='add') {
?>
                <input name="tab1complete" type="button" class="CWformButton" id="tab1complete" value="&raquo;&nbsp;Next&nbsp;">
<?php
		} else {
			if($ProductHasOrders==0) {
				echo $_ENV["request.cwpage"]["productDeleteButton"];
			}
			echo $_ENV["request.cwpage"]["productArchiveButton"].$_ENV["request.cwpage"]["productSubmitButton"];
			if($ProductHasOrders==1) {
?>
                <span style="float:right;margin-right:23px;margin-top:8px;" class="smallPrint">Note:&nbsp;&nbsp;products with associated orders cannot be deleted</span>
<?php
			}
		}
?>
              </div>
        </div>
            <!-- /end info tab (1)--> 
            <!-- DESCRIPTIONS TAB -->
            <div id="tab2" class="tabDiv">
            <h3>Product Descriptions and Specifications</h3>
            <?php // Form Table ?>
            <table class="CWformTable wide">
            <?php // product_preview_description ?>
            <tr>
		<th class="label">Preview Description</th>
		<td class="noHover"><textarea name="product_preview_description" class="textEdit" cols="72" rows="5"><?php echo $_POST['product_preview_description'];?></textarea></td>
	    </tr>        
       <?php // product_description ?>
            <tr>
		<th class="label">Full Description</th>
		<td class="noHover"><textarea name="product_description" class="textEdit" cols="72" rows="9"><?php echo $_POST['product_description'];?></textarea></td>
	    </tr>
<?php
		// product_Spec 
		if($_ENV["application.cw"]["adminProductSpecsEnabled"]) {	
?>
	    <tr>
	    <th class="label"><?php echo $_ENV["application.cw"]["adminLabelProductSpecs"];?></th>
	    <td class="noHover">
	    <textarea name="product_special_description" class="textEdit" cols="72" rows="9" >
		<?php echo $_POST['product_special_description'];?>
	    </textarea>
	    </td>
	    </tr>
<?php
		}
		// product_keywords
		if($_ENV["application.cw"]["adminProductKeywordsEnabled"]) {
?>
            <tr>
		<th class="label"> <?php echo $_ENV["application.cw"]["adminLabelProductKeywords"]; ?></th>
		<td class="noHover"><textarea name="product_keywords" cols="72" rows="3"><?php echo $_POST['product_keywords'];?></textarea></td>
            </tr>
<?php
		}
?>
	    </table>
            <!-- FORM BUTTONS -->
            <div class="CWformButtonWrap" <?php if($_ENV["request.cwpage"]["editMode"]=='add') {?>style="text-align:center"<?php }?>> 
<?php
		if($_ENV["request.cwpage"]["editMode"]=='add') {
?>
          <input name="tab2complete" type="button" class="CWformButton" id="tab2complete" value="&raquo;&nbsp;Next&nbsp;">
<?php
		} else {
			if($ProductHasOrders==0) {
				echo $_ENV["request.cwpage"]["productDeleteButton"];
			}
			echo $_ENV["request.cwpage"]["productArchiveButton"].$_ENV["request.cwpage"]["productSubmitButton"];
			if($ProductHasOrders==1) {
?>
          <span style="float:right;margin-right:23px;margin-top:8px;" class="smallPrint">Note:&nbsp;&nbsp;products with associated orders cannot be deleted</span>
<?php
			}
		}
?>
        </div>
            </div>
            <!-- /end descriptions tab (2)--> 
            <!-- IMAGES TAB -->
            <div id="tab3" class="tabDiv">
          <h3>Product Image<?php if($listUploadGroups['totalRows'] > 1) {echo 's';}?></h3>
              <table class="CWformTable wide">
<?php
		// loop the query of upload groups 
		for ($i = 0; $i < $listUploadGroups['totalRows']; $i++) {
			// set up field name and number to use 
			$imgNo = $listUploadGroups['imagetype_upload_group'][$i];
			$imageFieldName = 'Image' . $imgNo;
			// QUERY: get image types for this upload group 
			$getImageTypes = CWquerySelectImageTypes($imgNo);
			$query = "SELECT * FROM cw_image_types WHERE imagetype_upload_group=" . $imgNo . " ORDER by imagetype_max_width LIMIT 0,1 ";			
			$result = mysql_query($query, $_ENV["request.cwapp"]["db_link"]);
			$getSmallImgType = mysql_fetch_array($result);
			$getCurrentImageValue = CWquerySelectProductImages($_ENV["request.cwpage"]["productlookupid"], $getSmallImgType['imagetype_id'], $imgNo);
			if (isset($getCurrentImageValue['product_image_filename'][0]) && $_ENV["request.cwpage"]["productlookupid"] > 0) {
				$currentImageFileName = $getCurrentImageValue['product_image_filename'][0];
			} else {
				$currentImageFileName = '';
			}
			$listFolder = $_ENV["application.cw"]["siteRoot"] . 'admin_preview';
			$_ENV["request.cwpage"]['imgParentURL'] = $_ENV["request.cwpage"]["adminImgPrefix"] . $_ENV["application.cw"]["appImagesDir"] . '/';
			$imgDir = $_ENV["request.cwpage"]['imgParentURL'] . 'admin_preview/';
			if (!(isset($_POST[$imageFieldName]))) {
				$_POST[$imageFieldName] = $currentImageFileName;
			}
			$imageField = $_POST[$imageFieldName];
			$imageFieldVal = $imageField;
			$imageSRC = $imgDir . $imageFieldVal;
			$imgUploadURL = 'product-image-upload.php?uploadGroup=' . $imgNo;
			$imgSelectURL = 'product-image-select.php?uploadGroup=' . $imgNo . '&listfolder=' . $getSmallImgType['imagetype_folder'] . '&showImages=' . $_ENV["application.cw"]["adminProductImageSelectorThumbsEnabled"];
			// create input field for this upload group
?>
			     <tr>
			     <th class="label"> 
<?php
			if ($listUploadGroups['totalRows'] > 1) {
				echo 'Image ' . $imgNo;
			} else {
				echo 'Product Image';
			}
			if( strlen($_POST[$imageFieldName]) > 0 ) {
?> 
				<div class="smallPrint imgTypesList">
				VIEW IMAGES:<br>
<?php			
				for ($ii = 0; $ii < $getImageTypes['totalRows']; $ii++) {
					$fileURL = $_ENV["request.cwpage"]["adminImgPrefix"] . $_ENV["application.cw"]["appImagesDir"] . '/' . $getImageTypes['imagetype_folder'][$ii] . '/' . $_POST[$imageFieldName];
					// if the file exists 
					if (file_exists($fileURL)) {			
?>
					    <a href="<?php echo $fileURL; ?>" class="zoomImg" title="<?php echo CWstringFormat($getImageTypes['imagetype_name'][$ii]); ?>">
					    <?php echo $getImageTypes['imagetype_name'][$ii] . '[' . $getImageTypes['imagetype_id'][$ii] . ']'; ?></a>  <br>
<?php
						// show size dimensions if debugging on 
						if ($_SESSION["cw"]["debug"]) {
							echo ':' . $getImageTypes['imagetype_max_width'][$ii] . '(w) x ' . $getImageTypes['imagetype_max_width'][$ii] . '(h)<br>';
						}
					}
				}
				// show original 
				$fileURL = $_ENV["request.cwpage"]["adminImgPrefix"] . $_ENV["application.cw"]["appImagesDir"] . '/orig/' . $_POST[$imageFieldName];	// if the file exists 
				if (file_exists($fileURL)) {
?>
							<a href="<?php echo $fileURL; ?>" class="zoomImg" title="Original Image">Original Image</a> 
							<br>
<?php
			} 
?>				    
				</div>
<?php
		}
?>
                </th>
			    <?php // image field / content area ?>
			    <td class="noHover"><input name="<?php echo $imageFieldName; ?>" id="<?php echo $imageFieldName; ?>" type="text" value="<?php echo $_POST[$imageFieldName]; ?>" class="imageInput" size="40">

			    <span class="fieldLinks"> 
			<?php // ICON: upload image ?>
						
				<a href="<?php echo $imgUploadURL; ?>" title="Upload image" class="showImageUploader"><img src="img/cw-image-upload.png" alt="Upload image" class="iconLink"></a> 
				<a href="<?php echo $imgSelectURL; ?>" title="Choose existing image" class="showImageSelector"><img src="img/cw-image-select.png" alt="Choose existing image" class="iconLink"></a> 

				<img src="img/cw-delete.png" title="Clear image field" class="iconLink clearImageLink"> 
			    </span> 
			    <?php // image content area ?>
			    <div class="productImageContent"> 
				<?php // image uploader ?>
				<div class="imageUpload">
				    <iframe width="460" height="224" frameborder="no" scrolling="false"> </iframe>
				</div>
				<?php // image preview ?>
				<div class="imagePreview">
<?php
		if (trim($_POST[$imageFieldName]) == '' || !(file_exists($imageSRC))) {
?>
					<img id="image<?php echo $imgNo; ?>" src="" style="display: none;"> 
<?php
			// if the image field is not blank, and image is not found, show error 
			if (strlen(trim($_POST[$imageFieldName])) && !(file_exists($imageSRC))) {
?>
					    <div class="alert">Image file not found</div>
<?php
			} 
		} else {
			// if the image file is ok, show it 
?>
					    <img src="<?php echo $imageSRC; ?>" alt="Image Location: <?php echo $imageSRC; ?>" class="productImagePreview" id="image<?php echo $imgNo; ?>" style="border:none;"> 
<?php
		}
?>	
				</div>
				<!-- /end Image Preview --> 
			    </div>
				<?php // hidden image fields ?>
				<input name="imageID<?php echo $imgNo; ?>" type="hidden" value="<?php echo ((isset($getCurrentImageValue['product_image_id'][$i])) ? $getCurrentImageValue['product_image_id'][$i] : "" ); ?>">
				<input name="PhotoNumber<?php echo $imgNo; ?>" type="hidden" value="<?php echo $imgNo; ?>">
				<?php // modify thimageIDe image count, one per field ?>
				<input type="hidden" name="ImageCount<?php echo $imgNo; ?>" value="<?php echo $getImageTypes['totalRows']; ?>">
			    </td>
			</tr>
<?php
	}
?>
		</table>
				      <!-- FORM BUTTONS -->
		 <div class="CWformButtonWrap" <?php if ($_ENV["request.cwpage"]["editMode"] == 'add') { echo 'style="text-align:center"'; } ?>> 
<?php 
	if ($_ENV["request.cwpage"]["editMode"] == 'add') {
?>
			<input name="AddProduct" type="button" class="submitButton" rel="productForm" id="AddProduct" value="Save Product">
<?php
	} else {
		if ($ProductHasOrders == 0) {
			echo $_ENV["request.cwpage"]["productDeleteButton"];
		}
		echo $_ENV["request.cwpage"]["productArchiveButton"] . $_ENV["request.cwpage"]["productSubmitButton"];
		if ($ProductHasOrders == 1) {
?> 
							    <span style="float:right;margin-right:23px;margin-top:8px;" class="smallPrint">Note:&nbsp;&nbsp;products with associated orders cannot be deleted</span>
<?php
		}
	}
?>
		    </div>
		</div>
			    <!-- /end photos tab (3)--> 
			    <?php
			    // hidden fields  
			    // product has orders? 
			    // the tab to return to when this form is submitted: changed dynamically when clicking on various tabs ?>
					<input name="hasOrders" type="hidden" id="hasOrders" value="<?php echo $ProductHasOrders; ?>">
					<input name="returnTab" class="returnTab" type="hidden" value="<?php echo $_GET['showtab']; ?>">
					</form>
			    <?php
			    // ///////  
			    // /END PRODUCT FORM  
			    // ///////  
			    // SKUs TAB ?>
				    <div id="tab4" class="tabDiv">
<?php
	if ($_ENV["request.cwpage"]["editMode"] != 'add') {
?>
							  <h3>Product SKUs (Stock Keeping Units)</h3>
<?php
		include("cwadminapp/inc/cw-inc-admin-product-skus.php");
	}
?>				
				    </div>
			  
			  	    <!-- /end skus tab (4)-->
<?php
	// UPSELL TAB 
	if ($_ENV["request.cwpage"]["showUpsell"]) {
?>
					<div id="tab5" class="tabDiv">
<?php
		if ($_ENV["request.cwpage"]["editMode"] != 'add') {
			include("cwadminapp/inc/cw-inc-admin-related-products.php");
		}
?>
					</div>
<?php
	}
?>
				    <!-- /end upsell tab (5)--> 
				    <?php // END LAST TAB ?>
				    </div>
				    <!-- end CWtabBox --> 
				    <!-- / end tabs -->
<?php
}
?>
			    </div>
			    <?php // end if product is archived ?>
			</div>
			<!-- /end Page Content -->
			<div class="clear"></div>
			</div>
			<!-- /end CWinner -->
		    </div>
		    <!-- /end Content -->   
<?php
// page end content / debug 
include("cwadminapp/inc/cw-inc-admin-page-end.php");
?>
		    <!-- /end CWadminPage -->
		    <div class="clear"></div>
		</div>
		<!-- /end CWadminWrapper -->
            </body>
        </html>