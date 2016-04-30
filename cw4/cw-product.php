<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-product.php
File Date: 2012-03-21
Description: shows product details based on ID
with additional content including recent views, related products
Handles add to cart via included functions
NOTES: 	Product options are shown as a series of select boxes or a table
(can be overridden per product by setting request.cwpage.optionDisplayType to select|table)
Add To Cart submission is managed by passing the submitted form variables to cw-mod-cartweaver.php
(For custom use of cw-mod-cartweaver, variables can come from any scope as long as the required values are present)
==========================================================
*/
// default url variables 
if (!isset($_GET["category"])) $_GET["category"] = 0;
if (!isset($_GET["secondary"])) $_GET["secondary"] = 0;
if (!isset($_GET["product"])) $_GET["product"] = 0;
// default page variables 
if (!isset($_ENV["request.cwpage"]["productID"])) $_ENV["request.cwpage"]["productID"] = $_GET["product"];
if (!isset($_ENV["request.cwpage"]["productName"])) $_ENV["request.cwpage"]["productName"] = "";
if (!isset($_ENV["request.cwpage"]["categoryID"])) $_ENV["request.cwpage"]["categoryID"] = $_GET["category"];
if (!isset($_ENV["request.cwpage"]["categoryName"])) $_ENV["request.cwpage"]["categoryName"] = "";
if (!isset($_ENV["request.cwpage"]["secondaryID"])) $_ENV["request.cwpage"]["secondaryID"] = $_GET["secondary"];
if (!isset($_ENV["request.cwpage"]["secondaryName"])) $_ENV["request.cwpage"]["secondaryName"] = "";
if (!isset($_ENV["request.cwpage"]["hasDiscount"])) $_ENV["request.cwpage"]["hasDiscount"] = false;
if (!isset($_ENV["request.cwpage"]["stockOK"])) $_ENV["request.cwpage"]["stockOK"] = true;
if (!isset($_ENV["request.cwpage"]["optionDisplayType"])) $_ENV["request.cwpage"]["optionDisplayType"] = $_ENV["application.cw"]["appDisplayOptionView"];
if (!isset($_ENV["request.cwpage"]["cartAction"])) $_ENV["request.cwpage"]["cartAction"] = $_ENV["application.cw"]["appActionAddToCart"];
if (!isset($_ENV["request.cwpage"]["addToCartUrl"])) $_ENV["request.cwpage"]["addToCartUrl"] = "";
if (!isset($_ENV["request.cwpage"]["returnUrl"])) $_ENV["request.cwpage"]["returnUrl"] = "";
if (!isset($_ENV["request.cwpage"]["productTaxRate"])) $_ENV["request.cwpage"]["productTaxRate"] = "";
// default client location variables 
if (!isset($_SESSION["cwclient"]["cwTaxCountryID"])) $_SESSION["cwclient"]["cwTaxCountryID"] = 0;
if (!isset($_SESSION["cwclient"]["cwTaxRegionID"])) $_SESSION["cwclient"]["cwTaxRegionID"] = 0;
// page alerts and errors 
if (!isset($_ENV["request.cwpage"]["cartAlert"])) $_ENV["request.cwpage"]["cartAlert"] = "";
if (!isset($_ENV["request.cwpage"]["cartConfirm"])) $_ENV["request.cwpage"]["cartConfirm"] = "";
// form and link actions 
if (!isset($_ENV["request.cwpage"]["hrefUrl"])) $_ENV["request.cwpage"]["hrefUrl"] = $_ENV["application.cw"]["appCWStoreRoot"].$_ENV["request.cw"]["thisPage"];
if (!isset($_ENV["request.cwpage"]["adminURLPrefix"])) $_ENV["request.cwpage"]["adminURLPrefix"] = "";
$myDir = getcwd();
chdir(dirname(__FILE__));
// clean up form and url variables 
include("cwapp/inc/cw-inc-sanitize.php");
// CARTWEAVER REQUIRED FUNCTIONS 
include("cwapp/inc/cw-inc-functions.php");
chdir($myDir);
// if values were passed in session, put into page scope 
if (isset($_SESSION["cw"]["cartAlert"])) {
	$_ENV["request.cwpage"]["cartAlert"] = $_SESSION["cw"]["cartAlert"];
	unset($_SESSION["cw"]["cartAlert"]);
}
if (isset($_SESSION["cw"]["cartConfirm"])) {
	$_ENV["request.cwpage"]["cartConfirm"] = $_SESSION["cw"]["cartConfirm"];
	unset($_SESSION["cw"]["cartConfirm"]);
}
// initial quantity 
if (isset($_POST["qty"]) && $_POST["qty"] > 0) {
	$_ENV["request.cwpage"]["intQty"] = $_POST["qty"];
} else {
	$_ENV["request.cwpage"]["intQty"] = 1;
}
// form product id overrides
if (isset($_POST["productID"]) && is_numeric($_POST["productID"])) {
	$_ENV["request.cwpage"]["productID"] = $_POST["productID"];
}
// other form values 
if (!isset($_POST["customInfo"])) $_POST["customInfo"] = "";
// page variables - request scope can be overridden per product as needed
$_ENV["request.cwpage"]["useAltPrice"] = $_ENV["application.cw"]["adminProductAltPriceEnabled"];
$_ENV["request.cwpage"]["altPriceLabel"] = $_ENV["application.cw"]["adminLabelProductAltPrice"];
$_ENV["request.cwpage"]["imageZoom"] = $_ENV["application.cw"]["appEnableImageZoom"];
$_ENV["request.cwpage"]["continueShopping"] = $_ENV["application.cw"]["appActionContinueShopping"];
// if not defined in url, request can override 
if ($_GET["product"] == 0 && $_ENV["request.cwpage"]["productID"] != 0) {
	$_GET["product"] = $_ENV["request.cwpage"]["productID"];
}
// if no valid url id provided, redirect to listings page 
$_ENV["request.cwpage"]["relocateUrl"] = $_ENV["request.cwpage"]["urlResults"];
if (!(isset($_GET["product"]) && is_numeric($_GET["product"]) && $_GET["product"] > 0)) {
	header("Location: ".$_ENV["request.cwpage"]["relocateUrl"]."");
}
// return to store link 
if (!strlen(trim($_ENV["request.cwpage"]["returnUrl"]))) {
	switch ($_ENV["request.cwpage"]["continueShopping"]) {
		case "results":
			// get the most recent results page view  
			$pvArr = explode(",", $_SESSION["cw"]["pageViews"]);
			$pageArr = explode("/", $_ENV["request.cwpage"]["urlResults"]);
			foreach ($pvArr as $key => $vv) {
				if (stristr($vv, $pageArr[sizeof($pageArr-1)]) !== false) {
					$_ENV["request.cwpage"]["returnUrl"] = trim($vv);
					break;
				}
			}
			if (!strlen(trim($_ENV["request.cwpage"]["returnUrl"]))) {
				$_ENV["request.cwpage"]["returnUrl"] = $_ENV["request.cwpage"]["urlResults"]."?category=".$_ENV["request.cwpage"]["categoryID"]."&secondary=".$_ENV["request.cwpage"]["secondaryID"];
			}
			break;
		case "details":
			// get the most recent details page view 
			$pvArr = explode(",", $_SESSION["cw"]["pageViews"]);
			$pageArr = explode("/", $_ENV["request.cwpage"]["urlDetails"]);
			foreach ($pvArr as $key => $vv) {
				if (stristr($vv, $pageArr[sizeof($pageArr-1)]) !== false && stristr($vv, "product=".$_ENV["request.cwpage"]["productID"]) === false) {
					$_ENV["request.cwpage"]["returnUrl"] = trim($vv);
					break;
				}
			}
			if (!strlen(trim($_ENV["request.cwpage"]["returnUrl"]))) {
				$_ENV["request.cwpage"]["returnUrl"] = $_ENV["request.cwpage"]["urlResults"]."?product=".$_ENV["request.cwpage"]["productID"]."&category=".$_ENV["request.cwpage"]["categoryID"]."&secondary=".$_ENV["request.cwpage"]["secondaryID"];
			}
			break;
		case "home":
			$_ENV["request.cwpage"]["returnUrl"] = $_ENV["application.cw"]["appSiteUrlHttp"];
			break;
	}
}
// PRODUCT DETAILS 
$product = CWgetProduct($_ENV["request.cwpage"]["productID"],'complex');
// if product has no skus, or is archived, send user back to listings page 
if ((!(isset($product["sku_ids"]) && is_array($product["sku_ids"]) && sizeof($product["sku_ids"]))) || (isset($product["product_archive"]) && $product["product_archive"] == 1)) {
	header("Location: ".$_ENV["request.cwpage"]["relocateUrl"]);
	exit;
}
// if backorders are allowed, set max qty, check stock 
if ($_ENV["application.cw"]["appEnableBackOrders"]) {
	$_ENV["request.cwpage"]["qtyMax"] = 99;
	// if out of stock message is set, verify stock even for backorders 
	if (strlen(trim($product["product_out_of_stock_message"])) && $product["qty_max"] <= 0) {
		$_ENV["request.cwpage"]["stockOK"] = false;
	} else {
		$_ENV["request.cwpage"]["stockOK"] = true;
	}
// if backorders are not allowed, set actual quantity 
} else {
	$_ENV["request.cwpage"]["qtyMax"] = min(array(99, $product["qty_max"]));
	// in stock? 
	if ($_ENV["request.cwpage"]["qtyMax"] <= 0) {
		$_ENV["request.cwpage"]["stockOK"] = false;
	}
	// display type if only one sku, no table view available 
	$skuList = $product["sku_ids"];
	if (!is_array($skuList) && strlen(trim($skuList))) $skuList = explode(",", $skuList);
	else if (!is_array($skuList)) $skuList = array();
	if (sizeof($skuList) <= 1) {
		$_ENV["request.cwpage"]["optionDisplayType"] = 'select';
	}
}
// tax rate 
if ($_ENV["application.cw"]["taxDisplayOnProduct"]) {
	$_ENV["request.cwpage"]["productTaxRate"] = CWgetProductTax($_ENV["request.cwpage"]["productID"], $_SESSION["cwclient"]["cwTaxCountryID"], $_SESSION["cwclient"]["cwTaxRegionID"]);
} else {
	$_ENV["request.cwpage"]["productTaxRate"] = "";
}
// discounts 
if ($_ENV["application.cw"]["discountsEnabled"] && (isset($product["price_disc_low"]) && $product["price_disc_low"] != $product["price_low"]) || (isset($product["price_disc_high"]) && $product["price_disc_high"] != $product["price_high"])) {
	$_ENV["request.cwpage"]["hasDiscount"] = true;
} else {
	$_ENV["request.cwpage"]["hasDiscount"] = false;
}
// ADD TO CART / FORM SUBMISSION 
// if productID field is defined, and equal to ID in url 
if (isset($_POST["productID"]) && $_POST["productID"] == $_ENV["request.cwpage"]["productID"]) {
	// verify quantity 
	if (isset($_POST["qty"]) && !is_numeric($_POST["qty"])) {
		$_POST["qty"] = 1;
	}
	// pass all form variables to cartweaver tag 
	$formStruct = $_POST;
	// redirect after cart action 
	if (strtolower($_ENV["request.cwpage"]["cartAction"]) == 'goto' && !strlen(trim($_ENV["request.cwpage"]["addToCartUrl"]))) {
		$_ENV["request.cwpage"]["addToCartUrl"] = trim($_ENV["request.cwpage"]["urlShowCart"]);
	}
	// reset stored ship cost, since cart may have changed 
	$_SESSION["cwclient"]["cwShipTotal"] = 0;
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array(
		"product_id" => $_POST["productID"],
		"form_values" => $formStruct,
		"redirect" => $_ENV["request.cwpage"]["addToCartUrl"],
		"sku_custom_info" => $_POST["customInfo"]);
	include("cwapp/mod/cw-mod-cartweaver.php");
	unset($module_settings);
	chdir($myDir);
}
// get product image 
if (!isset($product["productImgMain"])) $product["productImgMain"] = array();
$product["productImgMain"] = CWgetImage($_ENV["request.cwpage"]["productID"],2,$_ENV["application.cw"]["appImageDefault"]);
// get product zoom image 
if (!isset($product["productImgZoom"])) $product["productImgZoom"] = array();
$product["productImgZoom"] = CWgetImage($_ENV["request.cwpage"]["productID"],3,$_ENV["application.cw"]["appImageDefault"]);
// make sure file exists for zoom link (avoid broken links) 
if (!file_exists(expandPath($product["productImgZoom"]))) {
	$_ENV["request.cwpage"]["imageZoom"] = 0;
}
// put required scripting into page head 
$headcontent = "";
// fancybox: image zoom / add to cart options for related products 
$headcontent .= "<script type=\"text/javascript\">
	jQuery(document).ready(function(){
";
// if showing zoom image 
if ($_ENV["request.cwpage"]["imageZoom"] != 0) {
	// settings apply to any link with class 'CWimageZoomLink' 
	$headcontent .= "		jQuery('a.CWimageZoomLink').each(function(){
			jQuery(this).fancybox({
			'titleShow': false,
			'titlePosition': 'inside',
			'padding': 3,
			'overlayShow': true,
			'showCloseButton': true,
			'hideOnOverlayClick':true,
			'hideOnContentClick': true,
			'autoDimensions': true,
			'showNavArrows':false
			});
		});
		// fancybox - see http://fancybox.net/api for available options
";
}
$headcontent .= "	});
</script>";
CWinsertHead($headcontent);
// /////// START OUTPUT /////// 
// breadcrumb navigation
/* 
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "breadcrumb",
	"separator" => " &raquo; ");
include("cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
*/
// show product details ?>
<div class="CWproduct CWcontent" id="CWdetails">
<?php
// if product is found, and on web = yes 
if ($product["product_on_web"] && $product["product_archive"] != 1 && ($_ENV["application.cw"]["appEnableBackOrders"] || $_ENV["request.cwpage"]["stockOK"])) {
?>
		
<?php
	if (strlen(trim($product["productImgMain"]))) {
?>
				<!-- product image wrapper -->
				<div class="CWproductImage">
<?php
		if ($_ENV["request.cwpage"]["imageZoom"]) {
			// show the image with zoom link ?>
						<a href="<?php echo $product["productImgZoom"]; ?>" class="CWimageZoomLink CWlink" title="<?php echo $product["product_name"]; ?>"><img src="<?php echo $product["productImgMain"]; ?>" alt="<?php echo $product["product_name"]; ?>: click to enlarge"></a>
						<div>
							<a href="<?php echo $product["productImgZoom"]; ?>" class="CWimageZoomLink CWlink" title="<?php echo $product["product_name"]; ?>">Click to Enlarge</a>
						</div>
<?php
		} else {
			// show only the image, no link, no zoom ?>
						<img src="<?php echo $product["productImgMain"]; ?>" alt="<?php echo htmlentities($product["product_name"]); ?>">
<?php
		}
?>
				</div>
				<!-- /end product image -->
<?php
	}
?>
			<!-- product info column -->
            <!-- product name -->
		
        
        
        <?php
$jcproductname = $product["product_name"];

$jcproductpart = explode(",", $jcproductname);
?>

<div class="jcbrand"><?php echo $jcproductpart[0]; ?> <br /></div>
<h2><?php echo $jcproductpart[1]; ?> <br /></h2>
<div class="jcflavor"><?php echo $jcproductpart[2]; ?> <br /></div>


		<!-- product image -->
			<div id="CWproductInfo">
				<!-- anchor for product info on submission -->
				<a name="skus"></a>
                
                
                				<!-- product description -->
<?php
	if (strlen(trim($product["product_description"]))) {
?>
					<div class="CWproductDescription"><?php echo $product["product_description"]; ?></div>

				<!-- /end product description -->
                
                
				<!-- price range -->
				<div id="CWproductPrices">
					<div class="CWproductPrice<?php if ($_ENV["request.cwpage"]["hasDiscount"]) { ?> strike<?php } ?>">
						Price:
						<span class="CWproductPriceLow"><?php echo cartweaverMoney($product["price_low"],'local'); ?></span>
<?php
	if ($product["price_high"] > $product["price_low"]) {
?>
							<span class="priceDelim">-</span>
							<span class="CWproductPriceHigh"><?php echo cartweaverMoney($product["price_high"],'local'); ?></span>
<?php
	}
	// if showing taxes here (no discount) 
	if (is_array($_ENV["request.cwpage"]["productTaxRate"]) && isset($_ENV["request.cwpage"]["productTaxRate"]["calcTax"]) && $_ENV["request.cwpage"]["productTaxRate"]["calcTax"] > 0 && !$_ENV["request.cwpage"]["hasDiscount"]) {
		$calcrate = $_ENV["request.cwpage"]["productTaxRate"]["calcTax"];
		$displayrate = $_ENV["request.cwpage"]["productTaxRate"]["displayTax"];
?>
							<br>
							<span class="smallPrint">
								(<span class="CWproductTaxPriceLow"><?php echo cartweaverMoney($calcrate * $product["price_low"],'local'); ?></span>
<?php
		if ($product["price_high"] > $product["price_low"]) {
?>
									<span class="priceDelim">-</span>
									<span class="CWproductTaxPriceHigh"><?php echo cartweaverMoney($calcrate * $product["price_high"],'local'); ?></span>
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
	// if showing discount price 
	if ($_ENV["request.cwpage"]["hasDiscount"]) {
?>
						<div class="CWproductPriceDisc alertText">
							Discount Price:
							<span class="CWproductPriceDiscLow"><?php echo cartweaverMoney($product["price_disc_low"],'local'); ?></span>
<?php
		if ($product["price_disc_high"] > $product["price_disc_low"]) {
?>
								<span class="priceDelim">-</span>
								<span class="CWproductPriceDiscHigh"><?php echo cartweaverMoney($product["price_disc_high"],'local'); ?></span>
<?php
		}
		// if showing taxes here 
		if (is_array($_ENV["request.cwpage"]["productTaxRate"]) && isset($_ENV["request.cwpage"]["productTaxRate"]["calcTax"]) && $_ENV["request.cwpage"]["productTaxRate"]["calcTax"] > 0) {
			$calcrate = $_ENV["request.cwpage"]["productTaxRate"]["calcTax"];
			$displayrate = $_ENV["request.cwpage"]["productTaxRate"]["displayTax"];
?>
							<br>
							<span class="smallPrint">
								(<span class="CWproductTaxPriceLow"><?php echo cartweaverMoney($calcrate * $product["price_disc_low"],'local'); ?></span>
<?php
			if ($product["price_disc_high"] > $product["price_disc_low"]) {
?>
									<span class="priceDelim">-</span>
									<span class="CWproductTaxPriceHigh"><?php echo cartweaverMoney($calcrate * $product["price_disc_high"],'local'); ?></span>
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
	// alt price (msrp) 
	if ($_ENV["request.cwpage"]["useAltPrice"]) {
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
	// determine url vars to pass through with cart submission 
	$formActionVars = 'product';
	if (isset($_GET["category"]) && $_GET["category"] > 0) {
		if ($formActionVars) $formActionVars .= ",";
		$formActionVars .= 'category';
	}
	if (isset($_GET["secondary"]) && $_GET["secondary"] > 0) {
		if ($formActionVars) $formActionVars .= ",";
		$formActionVars .= 'secondary';
	}
	$formActionUrl = CWserializeUrl($formActionVars, $_ENV["request.cwpage"]["hrefUrl"]);
?>
				<!-- add to cart form w/ option selections -->
				<form action="<?php echo $formActionUrl; ?>" class="CWvalidate" method="post" name="AddToCart" id="CWformAddToCart">
					<!-- product options: shows select lists or table based on variable setting for 'display type' -->
<?php
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array(
		"product_id" => $_ENV["request.cwpage"]["productID"],
		"product_options" => $product["optiontype_ids"],
		"display_type" => $_ENV["request.cwpage"]["optionDisplayType"],
		"tax_rate" => $_ENV["request.cwpage"]["productTaxRate"]);
	include("cwapp/mod/cw-mod-productoptions.php");
	unset($module_settings);
	chdir($myDir);
	// custom input (show here if label provided in admin) 
	if (strlen(trim($product["product_custom_info_label"])) && $_ENV["application.cw"]["adminProductCustomInfoEnabled"]) {
?>
						<!-- custom value -->
						<div class="CWcustomInfo">
							<label class="wide" for="customInfo"><?php echo trim($product["product_custom_info_label"]); ?>:</label>
							<input type="text" name="customInfo" class="custom" size="22" value="<?php echo $_POST["customInfo"]; ?>" maxlength="255">
						</div>
<?php
	}
	// if stock is ok 
	if ($_ENV["request.cwpage"]["stockOK"]) {
?>
						<!-- quantity/submit -->
						<div>
<?php
		// dropdowns only (or table with no options): tables method includes its own quantity fields 
		if ($_ENV["request.cwpage"]["optionDisplayType"] != 'table' || sizeof($product["optiontype_ids"]) < 1) {
			// quantity input ?>
								<label for="qty">Quantity:</label>
<?php
			if ($_ENV["application.cw"]["appDisplayProductQtyType"] == 'text') {
?>
									<input name="qty" id="qtyInput" type="text" value="<?php echo $_ENV["request.cwpage"]["intQty"]; ?>" class="{required:true,number:true,min:1} qty" title="Quantity is required" size="2" onkeyup="extractNumeric(this,0,false)">
<?php
			} else {
?>
									<select name="qty" class="{required:true,min:1}" title="Quantity">
<?php
				for ($ii=1; $ii<=$_ENV["request.cwpage"]["qtyMax"]; $ii++) {
?>
										<option value="<?php echo $ii; ?>"<?php if ($ii == $_ENV["request.cwpage"]["intQty"]) { ?> selected="selected"<?php } ?>><?php echo $ii; ?></option>
<?php
				}
?>
									</select>
<?php
			}
		}
		// / end quantity ?>
							<!-- submit button -->
							<div class="center CWclear">
								<input name="submit" type="submit" class="CWformButton" value="Add to Cart">
							</div>
						</div>
<?php
		// if stock is not ok 
	} else {
?>
						<div class="CWalertBox alertText"><?php echo $product["product_out_of_stock_message"]; ?></div>
<?php
	}
	// confirmation / alerts 
	if (strlen(trim($_ENV["request.cwpage"]["cartConfirm"]))) {
		// list confirmations, add view cart link ?>
						<div class="CWconfirmBox confirmText"><div><?php echo str_replace(',','</div><div>',$_ENV["request.cwpage"]["cartConfirm"]); ?></div><a href="<?php echo $_ENV["request.cwpage"]["urlShowCart"]; ?>">View Cart</a></div>
<?php
		unset($_SESSION["cw"]["cartConfirm"]);
	}
	if (strlen(trim($_ENV["request.cwpage"]["cartAlert"]))) {
?>
						<div class="CWalertBox alertText fadeOut"><div><?php echo str_replace(',','</div><div>',$_ENV["request.cwpage"]["cartAlert"]); ?></div></div>
<?php
		unset($_SESSION["cw"]["cartAlert"]);
	}
	// hidden values ?>
					<input name="productID" type="hidden" value="<?php echo $_ENV["request.cwpage"]["productID"]; ?>">
				</form>
				<!-- /end add to cart form-->

<?php
	// edit product link 
	if ($_ENV["application.cw"]["adminProductLinksEnabled"] && isset($_SESSION["cw"]["loggedIn"]) && $_SESSION["cw"]["loggedIn"] == 1 && isset($_SESSION["cw"]["accessLevel"]) && ListFindNoCase('developer,merchant',$_SESSION["cw"]["accessLevel"])) {
		//$_ENV["request.cwpage"]["adminURLPrefix"].
?>
				<p><a href="<?php echo $_ENV["application.cw"]["appCWAdminDir"]; ?>product-details.php?productid=<?php echo $_ENV["request.cwpage"]["productID"]; ?>" title="Edit Product" class="CWeditProductLink"><img src="<?php echo $_ENV["application.cw"]["appCWAdminDir"]; ?>img/cw-edit.gif" alt="Edit Product"></a></p>
<?php
	}
?>
			</div>
			<!-- /end product info -->
            
            <?php
		// continue shopping 
		if (isset($_ENV["request.cwpage"]["returnUrl"]) && strlen(trim($_ENV["request.cwpage"]["returnUrl"]))) {
?>
						<p class="CWcontShop">&raquo;&nbsp;<a href="<?php echo $_ENV["request.cwpage"]["returnUrl"]; ?>">Continue Shopping</a></p>
<?php
		}
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
            
            
            <?php 
if ($_ENV["request.cwpage"]["productID"] == 116)

{ include("cwapp/inc/cw-inc-jcvicpanes.php");} 

if ($_ENV["request.cwpage"]["productID"] == 115)

{ include("cwapp/inc/cw-inc-jcrdcpanes.php");} 

if ($_ENV["request.cwpage"]["productID"] == 120)

{ include("cwapp/inc/cw-inc-jcwspanes.php");} 

if ($_ENV["request.cwpage"]["productID"] == 121)

{ include("cwapp/inc/cw-inc-jcnpanes.php");} 

if ($_ENV["request.cwpage"]["productID"] == 122)

{ include("cwapp/inc/cw-inc-jccmpanes.php");} 

if ($_ENV["request.cwpage"]["productID"] == 123)

{ include("cwapp/inc/cw-inc-jcsbpanes.php");} 

if ($_ENV["request.cwpage"]["productID"] == 124)

{ include("cwapp/inc/cw-inc-jccicpanes.php");} 

if ($_ENV["request.cwpage"]["productID"] == 125)

{ include("cwapp/inc/cw-inc-jcfbpanes.php");}

if ($_ENV["request.cwpage"]["productID"] == 126)

{ include("cwapp/inc/cw-inc-jccpanes.php");} 

if ($_ENV["request.cwpage"]["productID"] == 127)

{ include("cwapp/inc/cw-inc-jcmagpanes.php");}

if ($_ENV["request.cwpage"]["productID"] == 128)

{ include("cwapp/inc/cw-inc-jcgdpanes.php");}

if ($_ENV["request.cwpage"]["productID"] == 129)

{ include("cwapp/inc/cw-inc-jcherlpanes.php");}
?>
            
            
            
            
			<!-- related products -->
<?php
	// if related products exist for this product 
	// will be null value if related products turned off in admin 
	if (strlen(trim($product["related_product_ids"]))) {
		// show all related products in this area ?>
				<div class="CWproductRelatedProducts">
					<h3>Related Items:</h3>
<?php
		// show related items in random order 
		$rprodList = CWlistRandom($product["related_product_ids"],4);
		// loop the list of related IDs from CWgetProduct above 
		$loopCt = 0;
		if (!is_array($rprodList)) $rprodList = explode(",", $rprodList);
		foreach ($rprodList as $key => $pp) {
			// count output for insertion of breaks or other formatting 
			$loopCt++;
			// show the product include 
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			$module_settings = array(
				"product_id" => $pp,
				"show_description" => false,
				"show_image" => true,
				"show_price" => true,
				"image_class" => "CWimgRelated",
				"image_position" => "above",
				"title_position" => "above",
				"details_page" => $_ENV["request.cwpage"]["hrefUrl"],
				"details_link_text" => "<span style='margin-left:0px;'>&raquo; details</span>",
				"add_to_cart" => false,
				"price_id" => "CWproductPrices-".$pp);
			include("cwapp/mod/cw-mod-productpreview.php");
			unset($module_settings);
			chdir($myDir);
			// divided every 2 products 
			if ($loopCt % $_ENV["application.cw"]["appDisplayUpsellColumns"] == 0) {
?>
							<div class="CWclear upsellDiv"></div>
<?php
			}
		}
?>
				</div>
<?php
	}
?>
			<!-- /end related products -->
<?php
	// if product not on web, or archived 
} else {
?>
		<div class="CWalertBox">
			No Product Selected
<?php
	if (isset($_ENV["request.cwpage"]["returnUrl"])) {
?>
			<a href="<?php echo $_ENV["request.cwpage"]["returnUrl"]; ?>" class="CWlink">Return to Store</a>
<?php
	}
?>
		</div>
<?php
}
?>
	<!-- clear floated content -->
	<div class="CWclear"></div>
</div>
<!-- / end #CWdetails -->
<?php
// recently viewed products 
include("cwapp/inc/cw-inc-recentview.php");
// page end / debug 
include("cwapp/inc/cw-inc-pageend.php");
?>