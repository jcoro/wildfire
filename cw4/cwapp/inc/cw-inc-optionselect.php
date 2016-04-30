<?php 
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-optionselect.php
File Date: 2012-02-01
Description:
standalone internal page to show option selection
used for add to cart function in cw-mod-productpreview.php
==========================================================
*/
if (!(isset($accessOK))) $accessOK = false; 
if (!(isset($optionCt))) $optionCt = 3; 
// defaults for lookup and form 
if (!(isset($_GET['action']))) $_GET['action'] = "";
if (!(isset($_GET['qty']))) $_GET['qty'] = true;
if (!(isset($_GET['intqty']))) $_GET['intqty'] = 1; 
if (!(isset($_GET['optiontype']))) $_GET['optiontype'] = "select"; 
// if product id is valid 
if (isset($_GET['product']) && $_GET['product'] > 0) {
	// global functions
	if(!isset($_ENV["request.cwapp"]["db_link"])) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		require_once("../../../Application.php");
		chdir($myDir);
	}
	if(!function_exists('CWtime')) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		require_once("../func/cw-func-global.php");
		chdir($myDir);
	}
	// global queries
	if(!function_exists('CWquerySelectProductDetails')) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		require_once("../func/cw-func-query.php");
		chdir($myDir);
	}
	// product functions
	if(!function_exists('CWgetProduct')) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		require_once("../func/cw-func-product.php");
		chdir($myDir);
	}
	// discount functions 
	if(!function_exists('CWgetDiscountAmount')) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		require_once("../func/cw-func-discount.php");
		chdir($myDir);
	}
	// QUERY: get product info needed to show selections 
	$product = CWgetProduct($_GET['product']);
	$product_optiontype_ids = $product['optiontype_ids'];
	// if product is valid 
	if(isset($product["product_id"]) && $product['product_id'] > 0 && count($product_optiontype_ids) > 0) {
		$accessOK = true;
		$optionCt = count($product_optiontype_ids);
	}
}
// height for form based on number of select elements 
// leaving room for alert/validation below submit button 
$formHeight = ($optionCt * 45) + 90;
// javascript for form validation  
$myDir = getcwd();
chdir(dirname(__FILE__));
include("cw-inc-script-validation.php");
chdir($myDir);
?>
<script type="text/javascript">
jQuery(document).ready(function() {
jQuery('form#CWformAddToCartWindow input.qty').keyup(function() {
	var windowSumVal = jQuery('form#CWformAddToCartWindow input.qty').sum();
	jQuery('form#CWformAddToCartWindow input#totalQty').val(windowSumVal);
}).click(function() {
	jQuery(this).focus();
});
});
</script>
<?php
// if product is not valid, or info is not correct 
if($accessOK == false) {
	exit;
}
?>
<div id="CWformWindow" class="CWcontent">
	<h2>
		Select Options for
	</h2>
	<h1>
		<?php echo $product['product_name']; ?>
	</h1>
	<!-- add to cart form w/ option selections -->
	<form action="<?php echo $_GET['action']; ?>" class="CWvalidate" style="min-height:<?php echo $formHeight;?>px" method="post" name="addToCart-<?php echo $product['product_id']; ?>" id="CWformAddToCartWindow">
<?php
// selection boxes 
if(count($product_optiontype_ids)) {
?>
		<div class="productPreviewSelect">
			<!-- product options -->
	<?php
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array(
		"product_id" => $product['product_id'],
		"product_options" => $product['optiontype_ids'],
		"display_type" => $_GET['optiontype'],
		"form_id" => "CWformAddToCartWindow",
		"price_id" => "CWproductPrices-".$product['product_id']."w");
	include("../mod/cw-mod-productoptions.php");
	unset($module_settings);
	chdir($myDir);
	?>                    
		</div>
<?php                
}
// custom input (show here if label provided in admin) 
if(strlen(trim($product['product_custom_info_label'])) && $_ENV["application.cw"]["adminProductCustomInfoEnabled"]) {
?>
		<!-- custom value -->
		<div class="CWcustomInfo">
			<label class="wide" for="customInfo">
				<?php echo trim($product['product_custom_info_label']); ?>
			</label>
			<input type="text" name="customInfo" class="custom" size="22" value="">
		</div>
<?php				
}
// if stock is ok 
if($_ENV["application.cw"]["appEnableBackOrders"] == true || $product['qty_max'] > 0) {
?>
		<!-- quantity/submit -->
		<div>
		<?php // dropdowns only (or table with no options): tables method includes its own quantity fields
	if($_GET['qty'] && !$_GET['optiontype'] == 'table' ) {
		// quantity input ?>
            <label for="qty">
                Quantity:
            </label>
<?php
		if($_ENV["application.cw"]["appDisplayProductQtyType"] == 'text') { 
		?>
			<input name="qty" id="qtyInput" type="text" value="<?php echo $_GET['intqty']?>" class="required qty number" title="Quantity is required" size="2" onkeyup="extractNumeric(this,0,false)">
		<?php						
		} else { 
		?>
			<select name="qty" class="required" title="Quantity">
			<?php
			for($ii = 0; $ii < $_ENV["request.cwpage"]["qtyMax"]; $ii++) {
			?>
				<option value="<?php echo $ii; ?>"><?php echo $ii; ?></option>
			<?php								
			}
			?>                            
			</select>
		<?php							
		}					
	} else { 
	?>
			<input type="hidden" value="1" name="qty">
			<span style="width:104px;display:block;float:left;">&nbsp;</span>
	<?php					
	}                 
			// / end quantity 
	?>
			<!-- submit button -->
			<input name="submit" type="submit" class="CWformButton" value="Add to Cart&nbsp;&raquo;">
		</div>
<?php			
} else { 
	// if stock is not ok ?>
            <div class="CWalertBox alertText">
                <?php echo $product['product_out_of_stock_message']; ?>
            </div>
<?php				
}            
			// hidden values ?>
			<input name="productID" type="hidden" value="<?php echo $product['product_id']; ?>">
	</form>
</div>
