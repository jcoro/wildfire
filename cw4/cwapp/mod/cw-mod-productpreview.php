<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-productpreview.php
File Date: 2012-02-01
Description:
Shows product details for 'preview' view
NOTE:
See attributes for options - product title and image can be arranged above or below,
and other elements can be shown or hidden
Form height for modal options popup is set based on number of options and can be adjusted.
==========================================================
*/
$imgHtml='';
// id of product to show: required if sku_id not provided 
if(!(isset($module_settings["product_id"]))) $module_settings["product_id"] = 0;
// show add to cart option in preview 
if(!(isset($module_settings["add_to_cart"]))) $module_settings["add_to_cart"] = false;
// if using add to cart (with single sku), show quantity selector 
if(!(isset($module_settings["show_qty"]))) $module_settings["show_qty"] = true;
// if using add to cart, specify any sku to skip option selection 
if(!(isset($module_settings["sku_id"]))) $module_settings["sku_id"] = 0;
// show price in preview 
if(!(isset($module_settings["show_price"]))) $module_settings["show_price"] = true;
// show discount price (if applicable) 
if(!(isset($module_settings["show_discount"]))) $module_settings["show_discount"] = true;
// show msrp price (if applicable) 
if(!(isset($module_settings["show_alt"]))) $module_settings["show_alt"] = $_ENV["application.cw"]["adminProductAltPriceEnabled"];
// show preview description 
if(!(isset($module_settings["show_description"]))) $module_settings["show_description"] = false;
// show image 
if(!(isset($module_settings["show_image"]))) $module_settings["show_image"] = true;
// image type (identifier for size to be used) 
if(!(isset($module_settings["image_type"]))) $module_settings["image_type"] = 1;
// class for product image 
if(!(isset($module_settings["image_class"]))) $module_settings["image_class"] = 'CWimage';
// image above or below text elements 
if(!(isset($module_settings["image_position"]))) $module_settings["image_position"] = 'above';
// title (product name) above or below image 
if(!(isset($module_settings["title_position"]))) $module_settings["title_position"] = 'above';
// details page: blank '' = no links 
if(!(isset($module_settings["details_page"]))) $module_settings["details_page"] = $_ENV["request.cwpage"]["urlDetails"];
// details link text 
if(!(isset($module_settings["details_link_text"]))) $module_settings["details_link_text"] = '&raquo; details';
// options display type 
if(!(isset($module_settings["option_display_type"]))) $module_settings["option_display_type"] = $_ENV["application.cw"]["appDisplayOptionView"];
// show alt price y/n 
if(!(isset($_ENV["request.cwpage"]["useAltPrice"]))) $_ENV["request.cwpage"]["useAltPrice"] = $module_settings["show_alt"];		  
if(!(isset($_ENV["request.cwpage"]["altPriceLabel"]))) $_ENV["request.cwpage"]["altPriceLabel"] = $_ENV["application.cw"]["adminLabelProductAltPrice"];
if(!(isset($_ENV["request.cwpage"]["intQty"]))) $_ENV["request.cwpage"]["intQty"] = 1;
if(!(isset($_ENV["request.cwpage"]["qtyMax"]))) $_ENV["request.cwpage"]["qtyMax"] = 99;
// optionselect window url 
if(!(isset($_ENV["request.cw"]["assetSrcDir"]))) $_ENV["request.cw"]["assetSrcDir"] = $_ENV["application.cw"]["appCWContentDir"];
if(!(isset($_ENV["request.cwpage"]["formWindowBaseUrl"]))) $_ENV["request.cwpage"]["formWindowBaseUrl"] = $_ENV["request.cw"]["assetSrcDir"].'cwapp/inc/cw-inc-optionselect.php';
// default client location variables 
if(!(isset($_SESSION["cwclient"]["cwTaxCountryID"]))) $_SESSION["cwclient"]["cwTaxCountryID"] = 0;
if(!(isset($_SESSION["cwclient"]["cwTaxRegionID"]))) $_SESSION["cwclient"]["cwTaxRegionID"] = 0;
$myDir = getcwd();
chdir(dirname(__FILE__));
// global functions 
include("../inc/cw-inc-functions.php");
// clean up form and url variables 
include("../inc/cw-inc-sanitize.php");
chdir($myDir);
// if only a sku_id provided, get product ID 
if($module_settings["product_id"] == 0 && is_numeric($module_settings["sku_id"]) && $module_settings["sku_id"] > 0) {
	$lookupID = CWgetProductBySKU($module_settings["sku_id"]);
} else {
	$lookupID = $module_settings["product_id"];
}
// set up details url 
$detailsURL = $module_settings["details_page"] . '?product=' . $lookupID;
// get the product by ID 
if (is_numeric($lookupID) && $lookupID > 0) {
	$product = CWgetProduct($lookupID);
	// tax rate 
	if($_ENV["application.cw"]["taxDisplayOnProduct"]) {
		$product['taxrate'] = CWgetProductTax($lookupID, $_SESSION["cwclient"]["cwTaxCountryID"], $_SESSION["cwclient"]["cwTaxRegionID"]);
	} else {
		$product['taxrate'] = '';
	}
	// discounts 
	if($_ENV["application.cw"]["discountsEnabled"] && $module_settings["show_discount"] && (isset($product['price_disc_low']) && $product['price_disc_low'] != $product['price_low']) || (isset($product['price_disc_high']) && $product['price_disc_high'] != $product['price_high'])) {
		$product['hasDiscount'] = true;
	} else {
		$product['hasDiscount'] = false;	
	}
	// if only one sku 
	if((is_numeric($module_settings["sku_id"]) && $module_settings["sku_id"] > 0) || count($product['sku_ids']) == 1) {
		$skuIDArr = $product['sku_ids'];
		if(is_numeric($module_settings["sku_id"]) && $module_settings["sku_id"] > 0) {
			$lookupSku = $module_settings["sku_id"];
		} else if (is_numeric($skuIDArr[0])) {
			$lookupSku = $skuIDArr[0];
		}
		// QUERY: get sku details 
		$skuQuery = CWquerySkuDetails($lookupSku);
		$product['sku_ids'] = array($module_settings["sku_id"]);
		$product['price_alt_high'] = $skuQuery['sku_alt_price'];
		$product['price_alt_low'] = $skuQuery['sku_alt_price'];
		$product['price_high'] = $skuQuery['sku_price'];
		$product['price_low'] = $skuQuery['sku_price'];
		$product['qty_max'] = $skuQuery['sku_stock'];
	}
	// set up product image url and file 
	$productImg = CWgetImage($lookupID, $module_settings["image_type"], $_ENV["application.cw"]["appImageDefault"]);
	$imageFile = expandPath($productImg);
	// set up product title, to show above/below image 
	if (strlen(trim($product['product_name']))) {
		$titleHtml = '<div class="CWproductPreviewTitle">';
		
		        
$jcproductname = $product['product_name'];

$jcproductpart = explode(",", $jcproductname);

		
		
		// if linked 
		if (strlen(trim($module_settings["details_page"]))) {
			$titleHtml .= '<a href="' . $detailsURL. '" class="CWlink">'.$jcproductpart[0].' '.$jcproductpart[1].' <br /> '.$jcproductpart[2].'</a>';
			// not linked 
		} else {
			$titleHtml .= $product['product_name'];
		}
		$titleHtml .='</div>';
	}
	// save quantity input as a variable 
	$cwqtyinput ='<label for="qty">Quantity:</label>';
	if ($_ENV["application.cw"]["appDisplayProductQtyType"] == 'text') {
		$cwqtyinput .='<input name="qty" type="text" value="' .$_ENV["request.cwpage"]["intQty"].'" class="required qty number" title="Quantity is required" size="2" onkeyup="extractNumeric(this,0,false)">';
	} else {
		$cwqtyinput .=	'<select name="qty" class="required" title="Quantity">';
		for ($ii=1; $ii <= $_ENV["request.cwpage"]["qtyMax"]; $ii++) {
			$cwqtyinput .= '<option value="'.$ii.'"';
			if($ii == $_ENV["request.cwpage"]["intQty"]) {
				$cwqtyinput .= ' selected="selected"';
			}
			$cwqtyinput .= '>'.$ii.'</option>';
		}
		$cwqtyinput .='</select>';
	}
	// set up html for image, to show above/below other text elements 
	if ($module_settings["show_image"] != false && strlen(trim($productImg))) {
		// linked image, if url provided 
		if (strlen(trim($module_settings["details_page"]))) {  
			$imgHtml .= '<a href="'.$detailsURL.'" class="CWlink" title="'.$product['product_name'].'">
				<img src="'.$productImg.'" alt="'.htmlentities($product['product_name']).'" class="'.$module_settings["image_class"].'">
			</a>';
		// image without link 
		} else {
			$imgHtml .= '<img src="'.$productImg.'" alt="'.htmlentities($product['product_name']).'" class="'.$module_settings["image_class"].'">';
		}
	}
	// set up html for add to cart form 
	if ($module_settings["add_to_cart"]) {
		$cartFormHtml = "";
		// determine url vars to pass through with cart submission 
		$formActionVars = array();
		if(isset($_GET['category']) && $_GET['category'] > 0) { $formActionVars[] = 'category'; }
		if(isset($_GET['secondary']) && $_GET['secondary'] > 0) { $formActionVars[] = 'secondary'; }
		// url for submitting inline form (single sku) 
		$formActionUrl = CWserializeUrl(implode(",", $formActionVars), $_ENV["request.cwpage"]["urlDetails"]) . '&product='.$product['product_id'];
		// if multiple skus
		if (sizeof($product['optiontype_ids']) && $module_settings["sku_id"] == 0) {
			// button triggers option selection window 
			// height for form based on number of options 
			$otArr = $product['optiontype_ids'];
			if (!is_array($otArr) && strlen($otArr)) $otArr = explode(",", $otArr);
			else if (!is_array($otArr)) $otArr = array();
			$siArr = $product['sku_ids'];
			if (!is_array($siArr) && strlen($siArr)) $siArr = explode(",", $siArr);
			else if (!is_array($siArr)) $siArr = array();
			if($module_settings["option_display_type"] =='select') {    
				$formHeight = (count($otArr) * 45) + 270;
			} else {
				$formHeight = (count($siArr) * 30) + 270;
			}
			// set up url for option selection window
			//	  note: intqty must remain last in the querystring,
			//	  as all trailing data is removed by javascript in some instances 
			$formWindowURL = $_ENV["request.cwpage"]["formWindowBaseUrl"] .'?action='.urlencode($formActionUrl).'&product='.$product['product_id'].'&optiontype='.$module_settings["option_display_type"].'&qty='.$module_settings["show_qty"].'&intqty='.$_ENV["request.cwpage"]["intQty"];
			// link to open option selector window 
			$cartFormHtml = "
					<script type=\"text/javascript\">
					document.write('<form class=\"CWqtyForm\" id=\"CWqtyForm-".$product["product_id"]."\">');
					";
			if ($module_settings["show_qty"]) {
				$cartFormHtml .= "document.write('".$cwqtyinput."');
					";
			} else {
				$cartFormHtml .= "document.write('<input name=\"qty\" type=\"hidden\" value=\"1\" class=\"qty\">');
					";
			}
			$cartFormHtml .= "document.write('<input type=\"button\" value=\"Add to Cart&nbsp;&raquo;\" class=\"CWaddButton CWformButton\" id=\"CWaddButton-".$product["product_id"]."\">');
					document.write('<a style=\"display:none;\" href=\"".$formWindowURL."\" rel=\"".$formHeight."\" class=\"CWbuttonLink selOptions\">Add to Cart&nbsp;&raquo;</a>');
					document.write('</form>');
					</script>
					";
					// if no javascript, standard link is shown 
                    $cartFormHtml .= "<noscript>
					<a href=\"".$_ENV["request.cwpage"]["urlDetails"].'?product='.$product['product_id']."\" class=\"CWbuttonLink\">Add to Cart&nbsp;&raquo;</a>
					</noscript>
					";
			// if only one sku, show standard add to cart form, submits to product details page 
			} else {
				if($_ENV["application.cw"]["appEnableBackOrders"] == true || $product['qty_max'] > 0) {
					$cartFormHtml .= '<!-- add to cart form w/ option selections -->
					<form action="'.$formActionUrl.'" method="post" name="addToCart-'.$product['product_id'].'" id="addToCart-'.$product['product_id'].'" class="CWaddForm">
					';
					// custom input (show here if label provided in admin) 
					if (strlen(trim($product['product_custom_info_label'])) && $_ENV["application.cw"]["adminProductCustomInfoEnabled"]) {
						$cartFormHtml .= '<!-- custom value -->
					<div class="CWcustomInfo">
						<label class="wide" for="customInfo">'.trim($product['product_custom_info_label']).':</label>
						<input type="text" name="customInfo" class="custom" size="22" value="">
					</div>
					';
						if(is_numeric($module_settings["sku_id"]) && $module_settings["sku_id"] >0) {
							$cartFormHtml .= '<!-- sku id -->
					<input type="hidden" name="sku_id" value="'.$module_settings["sku_id"].'">
					';
						}
					}
					// if stock is ok 
					$cartFormHtml .= '<!-- quantity/submit -->
					<div>
					';
					// dropdowns only (or table with no options): tables method includes its own quantity fields 
					if($module_settings["show_qty"]) {            
						// quantity input 
						$cartFormHtml .= $cwqtyinput."
					";
					} else {
						$cartFormHtml .= '<input type="hidden" value="1" name="qty">
					';
					}
					// / end quantity 
					$cartFormHtml .= '<!-- submit button -->
					<input name="submit" type="submit" class="CWformButton" value="Add to Cart&nbsp;&raquo;">
				</div>
				';
					// hidden values 
					$cartFormHtml.=	'<input name="productID" type="hidden" value="'.$product['product_id'] . '">
			</form>
			';
				}
				// /end if qty ok 
			}
			// /end if only one sku
			$cartFormHtml .= '<!-- /end add to cart form-->';
	}
}
// /////// START OUTPUT /////// 
// if product info was found 
if(isset($module_settings["product_id"]) && is_numeric($module_settings["product_id"]) && $module_settings["product_id"] > 0) {
?>	
<div class="CWproduct CWcontent">
<?php // anchor link ?>
<a name="product-<?php echo $module_settings["product_id"];?>"></a>
<?php
	// title above image 
	if ($module_settings["title_position"] != 'below') {
		echo $titleHtml;
	}
	// if image above product info 
	if ($module_settings["image_position"] != 'below' && strlen(trim($imgHtml))) {
?>
        <!-- image -->
<?php
		echo $imgHtml;
	}
	// title below image 
	if($module_settings["title_position"] == 'below') {
		echo $titleHtml;
	}
?>


			<!-- special description -->
<?php
	if (strlen(trim($product["product_special_description"]))) {
?>
				<div class="CWproductSpecialDescription"><?php echo $product["product_special_description"]; ?></div>
<?php
	}
?>
			<!-- /end special description -->



<!-- price -->
<?php
	if($module_settings["show_price"] != false) {
?>
	<!-- price range -->
	<div id="CWproductPrices-<?php echo $module_settings["product_id"];?>" class="CWproductPreviewPrice">
		<div class="CWproductPrice<?php if ($product['hasDiscount'] == true) { ?> strike<?php } ?>">
			Price:
			<span class="CWproductPriceLow"><?php echo cartweaverMoney($product["price_low"],'local'); ?></span>
<?php
		if ($product["price_high"] > $product["price_low"]) {
?>
				<span class="priceDelim">-</span>
				<span class="CWproductPriceHigh"><?php echo cartweaverMoney($product["price_high"],'local'); ?></span>
<?php
		}
		// if showing taxes here (no discounts)
		if (is_array($product["taxrate"]) && isset($product["taxrate"]["calcTax"]) && $product["taxrate"]["calcTax"] > 0 && !$product["hasDiscount"]) {
			$calcrate = $product["taxrate"]["calcTax"];
			$displayrate = $product["taxrate"]["displayTax"];
?>
						<br>
						<span class="smallPrint">
							(<span class="CWproductTaxPriceLow"><?php echo cartweaverMoney($calcrate*$product["price_low"],'local'); ?></span>
<?php
			if ($product["price_high"] > $product["price_low"]) {
?>
								<span class="priceDelim">-</span>
								<span class="CWproductTaxPriceHigh"><?php echo cartweaverMoney($calcrate*$product["price_high"],'local'); ?></span>
<?php
			}
?>
							including <?php echo $displayrate; ?>% <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?>)
						</span>
<?php
		}
?>
		</div>

<?php
		// if showing discounts 
		if ($module_settings["show_discount"] && $product["hasDiscount"] == 'true') {
//echo "<pre>";
//var_dump($product);
//echo "</pre>";
?>
				<div class="CWproductPriceDisc alertText">
					Reduced:
					<span class="CWproductPriceDiscLow"><?php echo cartweaverMoney($product["price_disc_low"],'local'); ?></span>
<?php
			if ($product["price_disc_high"] > $product["price_disc_low"]) {
?>
						<span class="priceDelim">-</span>
						<span class="CWproductPriceDiscHigh"><?php echo cartweaverMoney($product["price_disc_high"],'local'); ?></span>
<?php
			}
			// if showing taxes here 
			if (is_array($product["taxrate"]) && isset($product["taxrate"]["calcTax"]) && $product["taxrate"]["calcTax"] > 0 && !$product["hasDiscount"]) {
				$calcrate = $product["taxrate"]["calcTax"];
				$displayrate = $product["taxrate"]["displayTax"];
?>
						<br>
						<span class="smallPrint">
							(<span class="CWproductTaxPriceLow"><?php echo cartweaverMoney($calcrate*$product["price_disc_low"],'local'); ?></span>
<?php
				if ($product["price_disc_high"] > $product["price_disc_low"]) {
?>
								<span class="priceDelim">-</span>
								<span class="CWproductTaxPriceHigh"><?php echo cartweaverMoney($calcrate*$product["price_disc_high"],'local'); ?></span>
<?php
				}
?>
							including <?php echo $displayrate; ?>% <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?>)
						</span>
<?php
			}
?>
				</div>
<?php
		}
		if ($_ENV["request.cwpage"]["useAltPrice"] == true) {
?>
			<div class="CWproductPriceAlt">
				<?php echo $_ENV["request.cwpage"]["altPriceLabel"]; ?>:
				<span class="CWproductPriceAltLow"><?php echo cartweaverMoney($product["price_alt_low"],'local'); ?></span>
<?php
			if ($product["price_alt_high"] > $product["price_alt_low"]) {
?>
					<span class="priceDelim">-</span>
					<span class="CWproductPriceAltHigh"><?php echo cartweaverMoney($product["price_alt_high"],'local'); ?></span>
<?php
			}
?>
			</div>
<?php
		}
		// free shipping message 
		if ($_ENV["application.cw"]["shipEnabled"] && $product["product_ship_charge"] == 0 && $_ENV["application.cw"]["appDisplayFreeShipMessage"]) {
?>
			<p><strong><?php echo trim($_ENV["application.cw"]["appFreeShipMessage"]); ?></strong></p>
<?php
		}
?>
	</div>
	<!-- /end price range -->
<?php
	}
?>
<!-- /end price -->
<?php
	// if image above product info 
	if ($module_settings["image_position"] == 'below' && strlen(trim($imgHtml))) {
?>
		<!-- image -->
		<?php echo $imgHtml; ?>
<?php
	}
?>

<!-- product description -->
<?php
	if ($module_settings["show_description"] != false && strlen(trim($product["product_preview_description"]))) {
?>
		<div class="CWproductPreviewDescription"><?php echo $product["product_preview_description"]; ?></div>
<?php
	}
?>
<!-- /end product description -->

<!-- details link -->
<?php
	if (strlen(trim($module_settings["details_link_text"]))) {
?>
<span class="CWproductDetailsLink"><a class="CWlink" href="<?php echo $detailsURL; ?>"><?php echo trim($module_settings["details_link_text"]); ?></a></span>
<?php
	}
?>
<!--/end details link-->

<!-- add to cart -->
<?php
	if ($module_settings["add_to_cart"]) {
		// for stock 0, show out of stock message if available 
		if (strlen(trim($product["product_out_of_stock_message"])) && $product["qty_max"] <= 0) {
?>
		<div class="CWalertBox alertText"><?php echo $product["product_out_of_stock_message"]; ?></div>
<?php
		// if ok, show add to cart form 
		} else {
			echo $cartFormHtml;
		}
	}
?>
<!-- /end add to cart -->

</div>
<!-- /end CWproduct -->
<?php
}
?>