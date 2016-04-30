<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-productoptions.php
File Date: 2012-05-05
Description:
Displays product options for selection as part of the 'add to cart' form,
and adds javascript to page for function of dropdowns and form validation.

Options can be shown as related select lists, or as a table where multiple skus
can be selected at once.

Notes:
- IE does not allow for 'show/hide' of <option> elements, instead they are removed using jQuery $remove()
  To reset the dropdown, we restore specific options from a hidden copy of each select list, created onload with $clone()

// ATTRIBUTES

- product_id: id of the product to show
- display_type: optional - the type of options display being shown (table|select)
- product_options: optional - struct (from getproduct function)
- product_option_list: optional - list of product ids
- int_qty: optional - quantity shown by default
- max_qty: optional - max number of items available in qty dropdowns
- tax_rate: optional - a structure of tax info returned by the CWgetProductTax() function
                        (if not provided, a lookup is attempted within this routine, based on product ID)

==========================================================
*/
// id of product to show options for 
if(!(isset($module_settings["product_id"]))) $module_settings["product_id"] = 0;  
// display type 
if(!(isset($module_settings["display_type"]))) $module_settings["display_type"] = $_ENV["application.cw"]["appDisplayOptionView"];  
// list of option type ids 
if(!(isset($module_settings["product_options"]))) $module_settings["product_options"] = ''; 
// defaults for quantity 
if(!(isset($_ENV["application.cw"]["appDisplayProductQtyType"]))) $_ENV["application.cw"]["appDisplayProductQtyType"] = 'text';
if(!(isset($module_settings["int_qty"]))) $module_settings["int_qty"] = 1;
if(!(isset($module_settings["max_qty"]))) $module_settings["max_qty"] = '100';
// id of the parent form 
if(!(isset($module_settings["form_id"]))) $module_settings["form_id"] = "CWformAddToCart";
// id of the pricing area for this selection 
if(!(isset($module_settings["price_id"]))) $module_settings["price_id"] = "CWproductPrices";
// tax rate: passed in as a structure from calling page 
if(!(isset($module_settings["tax_rate"]))) $module_settings["tax_rate"] = '';
if(!(isset($module_settings["tax_calc_type"]))) $module_settings["tax_calc_type"] = $_ENV["application.cw"]["taxCalctype"];
// customer id: used for getting customer-specific discounts 
if (!isset($_SESSION["cwclient"]["cwCustomerID"])) $_SESSION["cwclient"]["cwCustomerID"] = 0;
if (!isset($module_settings["customer_id"])) $module_settings["customer_id"] = $_SESSION["cwclient"]["cwCustomerID"];
// promo code: used for getting customer-applied discounts 
if (!isset($_SESSION["cwclient"]["discountPromoCode"])) $_SESSION["cwclient"]["discountPromoCode"] = "";
if (!isset($module_settings["promo_code"])) $module_settings["promo_code"] = $_SESSION["cwclient"]["discountPromoCode"];
$myDir = getcwd();
chdir(dirname(__FILE__));
// clean up form and url variables 
include("../inc/cw-inc-sanitize.php");
// global functions 
include("../inc/cw-inc-functions.php");
chdir($myDir);
//  look up tax rate if not provided (only if using local calc) 
if($_ENV["application.cw"]["taxDisplayOnProduct"] && strtolower($module_settings["tax_calc_type"]) == "localtax") {
	if(!is_array($module_settings["tax_rate"]) && isset($_SESSION["cwclient"]["cwTaxRegionID"]) && $_SESSION["cwclient"]["cwTaxRegionID"] > 0) {
		$module_settings["tax_rate"] = CWgetProductTax($module_settings["product_id"], $_SESSION["cwclient"]["cwTaxCountryID"], $_SESSION["cwclient"]["cwTaxRegionID"]);
	}
} else {
	$module_settings["tax_rate"]='';
}
// if we have a list, use it 
if (!isset($optiondata)) $optiondata = array();
if (!is_array($module_settings["product_options"]) && strlen(trim($module_settings["product_options"]))) $module_settings["product_options"] = explode(",", $module_settings["product_options"]);
else if (!is_array($module_settings["product_options"])) $module_settings["product_options"] = array();
if(sizeof($module_settings["product_options"]) && is_numeric(trim($module_settings["product_options"][0]))) {
	$optiondata['idlist'] = $module_settings["product_options"];
// if no list, get from query based on product id 
} else {
	$optionTypesQuery = CWquerySelectOptionTypes($module_settings["product_id"]);
	$optiondata['idlist'] = array();
	for($i=0; $i<$optionTypesQuery['totalRows'];$i++) {
		if (!in_array($optionTypesQuery['optiontype_id'][$i], $optiondata['idlist'])) {
			$optiondata['idlist'][] = $optionTypesQuery['optiontype_id'][$i];
		}
	}
}
// query for all product skus and prices 
$skusQuery = CWquerySelectSkus($module_settings["product_id"]);
// get product data for building option lists 
$skuTableQuery = CWquerySelectSkuOptions($module_settings["product_id"]);    
// get options for this product 
if(!(isset($optionTypesQuery))) {    
	$optionTypesQuery = CWquerySelectOptionTypes($module_settings["product_id"]);
}
// only show table if we have valid options 
if($skuTableQuery['totalRows'] < 1 || $optionTypesQuery['totalRows']  < 1) {
	$module_settings["display_type"]='select';
}
// START OUTPUT 
// show options for selected display type 
switch($module_settings["display_type"]) {
	case 'table':
		// TABLES ('table') 
		// create array of options 
		$optionList = array();
		$lastOptionID = "";
		for($i=0;$i < $optionTypesQuery['totalRows'] ;$i++ ) {
			$lastOptionID = $optionTypesQuery["optiontype_id"][$i];
			$optionList[] = $optionTypesQuery["optiontype_name"][$i];
			while ($i < $optionTypesQuery["totalRows"] && $optionTypesQuery["optiontype_id"][$i] == $lastOptionID) $i++;
			$i--;
		}
		$optionLabels = $optionList;
		$optionCt = count($optionLabels);
		$optionArray = array();
		$prodCt = 0;
?>
				<table class="CWtable" id="CWoptionsTable">
				<?php // header row ?>
				<tr class="headerRow">
				<?php // SKU ?>
				<th>SKU</th>
<?php
		// OPTION NAMES 
		// output the headers for each column 
		for($ii=0;$ii < $optionCt; $ii++) {
?>	
                    <th><?php echo trim($optionLabels[$ii]);?></th>
<?php
		}
		// PRICE ?>
            <th>Price</th>
            <?php // QTY ?>
            <th>Qty.</th>
            </tr>
            <?php // /end header row
		// output product data with qty/add to cart 
		$lastMSkuID = "";
//echo "SKUS: <pre>"; var_dump($skuTableQuery); echo "</pre>";
		for($i=0; $i < $skuTableQuery['totalRows']; $i++) {
			$lastMSkuID = $skuTableQuery["sku_merchant_sku_id"][$i];
			// create a row for each SKU 
			$prodCt++;  
			// default qty value 
			$prodQtyField = ((isset($_POST['qty'.$prodCt])) ? $_POST['qty'.$prodCt] : '');
			$prodQtyVal = $prodQtyField;
			// set up the price 
			$currentPrice = $skuTableQuery['sku_price'][$i];
			$displayPrice = cartweaverMoney($skuTableQuery['sku_price'][$i],'local');	
			$skuCalcPrice = $currentPrice;
			$showDiscountTax=false;
			// if showing tax price 
			if($_ENV["application.cw"]["taxDisplayOnProduct"] && isset($module_settings["tax_rate"]["calcTax"]) && $module_settings["tax_rate"]["calcTax"] > 0) {
				$skuCalcPrice = round($skuTableQuery['sku_Price'][$i] * $module_settings["tax_rate"]["calcTax"] * 100)/100;
				$displayPrice = $displayPrice . '<br><span class="smallPrint">('. cartweaverMoney($skuCalcPrice).' including ' . $module_settings["tax_rate"]["displayTax"].'%'.$_ENV["application.cw"]["taxSystemLabel"].'</span>';
				$howDiscountTax = true;
			}
		    // if showing discounts 
			if($_ENV["application.cw"]["discountsEnabled"]) {
				// check for discounts applied to each sku 
				$discountAmount = CWgetSKUDiscountAmount($skuTableQuery["sku_id"][$i], 'sku_cost', null, null, null, $module_settings["customer_id"], null, $module_settings["promo_code"]);
				if($discountAmount > 0) {
					$discountedPrice = $skuCalcPrice - $discountAmount;
					if($showDiscountTax == true) {
						$discountTaxText = '<span class="smallPrint"> including ' . $module_settings["tax_rate"]["displayTax"].'% ' . $_ENV["application.cw"]["taxSystemLabel"] . '</span>';
					} else {
						$discountTaxText = '';
					}
					$displayPrice = '<span class="CWproductPriceOld">' . $displayPrice . '</span><br><span class="CWproductPriceDisc">' . cartweaverMoney($discountedPrice) . ' '.$discountTaxText . '</span>';
				}
			}
?>
    <tr>
        <?php // sku ?>
        <td><?php echo $skuTableQuery['sku_merchant_sku_id'][$i];?></td>
<?php
			// option values 
			// set all options to 'none'
			for($j=0 ; $j < $optionCt-1 ;$j++) {
				$optionArray[$j] = "none";
			}
			while ($i < $skuTableQuery["totalRows"] && $lastMSkuID == $skuTableQuery["sku_merchant_sku_id"][$i]) {
				// find the current option in the OptionNames list 
				$j = array_search($skuTableQuery["optiontype_name"][$i], $optionList);
				// Set the array to the option name 
				$optionArray[$j] = $skuTableQuery['option_name'][$i];
				// output each option for this sku 
				$i++;
			}
			$i--;
			for($jj=0;$jj < $optionCt ; $jj++) {
?>	
                	<td><?php echo $optionArray[$jj];?></td>
<?php
			}
			// price ?>
        <td><?php echo $displayPrice;?></td>
        <?php // qty ?>
        <td class="CWinputCell">
<?php
			if($_ENV["application.cw"]["appDisplayProductQtyType"] == 'text') {
?>	
            <input name="qty<?php echo $prodCt;?>" class="qty" type="text" value="<?php $prodQtyVal;?>" size="1" onkeyup="extractNumeric(this,0,false)">
<?php
			} else {
?>	
           			 <select name="qty<?php echo $prodCt;?>">
<?php
				for($ii=0;$ii < $module_settings["max_qty"]; $ii++) {
?>	
						<option value="<?php echo $ii;?>"<?php if($ii ==0 || $ii ==$prodQtyVal) {?> selected="selected"<?php }?>><?php echo $ii;?></option>
<?php 
				}
?>
            			</select>
<?php
			}
			// hidden field for sku id ?>
            <input name="skuID<?php echo $prodCt;?>" type="hidden" value="<?php echo  $skuTableQuery['sku_id'][$i];?>">
        </td>
    </tr>
<?php
		}
?>
    </table>
    <?php // addSkus field contains number of skus being submitted via table options ?>
    <div>
        <input type="hidden" name="addSkus" value="<?php echo $prodCt;?>">
        <?php // total qty used for validation of table sums ?>
        <input type="hidden" name="totalQty" id="totalQty" value="0">
    </div>
<?php
		// validation script for tables
		//(uses calculation script called from global scripts)
		//
		$formScriptVar = ((isset($_REQUEST[$module_settings["form_id"].'FormScript'])) ? $_REQUEST[$module_settings["form_id"].'FormScript'] : null);
		if(is_null($formScriptVar)) {
			$formScriptVar="
        <script type=\"text/javascript\">
        jQuery(document).ready(function() {
            jQuery('form#CWformAddToCart input.qty').keyup(function() {
                var sumVal = jQuery('form#CWformAddToCart input.qty').sum();
                //alert(sumVal);
                jQuery('form#CWformAddToCart input#totalQty').val(sumVal);
            });
        });
        </script>";
			CWinsertHead($formScriptVar);
		}
		break;
		// / END TABLES 
	default:
		// SELECT BOXES ('select')
		// loop the list of option IDs 
		foreach ($optiondata['idlist'] as $key => $ii) {
?>	
			<div class="CWoptionSel" id="opt<?php echo $ii; ?>">
				<div class="CWoptionInner">
<?php
			// get options for this product with sku ids 
			$optionQuery = CWquerySelectRelOptions($module_settings["product_id"], $ii);
			// label for select list element ?>
			<label for="optionSel<?php echo $ii; ?>"><?php echo (($optionQuery['totalRows']) ? $optionQuery['optiontype_name'][0] : "" ); ?>:</label>
<?php
			// select list 
			$selectName = 'optionSel'.$ii;
			if(!(isset($_POST[$selectName]))) $_POST[$selectName] = '';
			$selectVal = $_POST[$selectName];
?>
			<select name="<?php echo $selectName; ?>" id="<?php echo $selectName; ?>" class="CWoption required" title="<?php echo (($optionQuery['totalRows']) ? $optionQuery['optiontype_name'][0] : "" ); ?> is required" onkeyup="this.blur();this.focus();">
			<?php // placeholder select option ?>
			<option value="" class="sku0">-- Select --</option>
<?php
			// output options 
			$lastOptName = -1;
			for ($jj=0; $jj<$optionQuery["totalRows"]; $jj++) {
				$skuidclasses = "";
				// create list of classes for related scripting, save as variable 
				if ($lastOptName != $optionQuery['option_name'][$jj]) {
					$lastOptName = $optionQuery['option_name'][$jj];
					while ($jj < $optionQuery["totalRows"] && $optionQuery['option_name'][$jj] == $lastOptName) {
						$skuidclasses .= 'sku'. $optionQuery['sku_id'][$jj]. ' ';
						$jj++;
					}
					$jj--;
				}
				// show option with listed classes ?>
				<option value="<?php echo $optionQuery['option_id'][$jj]; ?>" class="<?php echo trim($skuidclasses); ?>"<?php if($optionQuery['option_id'][$jj] == $selectVal) { ?> selected="selected"<?php } ?>><?php echo $optionQuery['option_name'][$jj]; ?></option>
<?php
			}
?>
			</select>
		</div>
<?php
			// option description text 
			if ($optionQuery['totalRows'] && strlen(trim($optionQuery['optiontype_text'][0]))) {
?>	
					<div class="CWoptionText"><?php echo trim($optionQuery['optiontype_text'][0]); ?></div>
<?php
			}
?>
	</div>
	<?php // hidden clone div, for resetting selections ?>
	<div style="display:none;" class="CWoptionRes" id="res<?php echo $ii; ?>"></div>
<?php
		}
		// hidden sku ids placeholder field ?>
<div><input type="hidden" value="" name="availSkus" id="availSkus"></div>
<?php
		break;
		// / END SELECT BOXES
}
// / END OUTPUT 
// JAVASCRIPT FOR SELECTION CONTROLS 
if (count($optiondata['idlist'])) {	
	// content sent to <head> of calling page 
	$formScriptVar = ((isset($_REQUEST[$module_settings["form_id"] .'FormScript'])) ? $_REQUEST[$module_settings["form_id"] .'FormScript'] : null );
	if(!(isset($formScriptVar))) {
		$formScriptVar = "<!-- javascript for selection boxes -->
	<script type=\"text/javascript\">
	jQuery(document).ready(function(){
		// currency format function
		// currency format function
		var \$cwCurrencyFormat = function(start_value,currency_symbol,space_separator,cs_precedes,thousands_sep,decimal_point) {
			// default formatting elements
			var currency_symbol = currency_symbol || '".$_ENV["application.cw"]["currencySymbol"]."';
			var space_separator = space_separator || '".$_ENV["application.cw"]["currencySpace"]."';
			var cs_precedes = cs_precedes || ".$_ENV["application.cw"]["currencyPrecedes"].";
			var thousands_sep = thousands_sep || '".$_ENV["application.cw"]["currencyGroup"]."';
			var decimal_point = decimal_point || '".$_ENV["application.cw"]["currencyDecimal"]."';
			var ret_str = '';
			var num_arr = start_value.split('.');
			var reg_ex = /\d+(\d{3})/;
			var reg_match = num_arr[0].match(reg_ex);
			while (reg_match) {
				ret_str = thousands_sep + reg_match[1] + ret_str;
				num_arr[0] = num_arr[0].substring(0, (num_arr[0].length - reg_match[1].length));
				reg_match = num_arr[0].match(reg_ex);
		   }
		   ret_str = num_arr[0] + ret_str;
		   if (num_arr.length > 1) ret_str += decimal_point + num_arr[1];
		   if (cs_precedes) {
				ret_str = currency_symbol + space_separator + ret_str;
				} else {
				ret_str += space_separator + currency_symbol;
			}
			return ret_str;
		}
	
	// function runs on page load, or can be invoked: \$jLoad()
	var \$jLoad = function(){
		// variables for target price elements - can be changed as needed
		var price_parent = '#".$module_settings["price_id"]."';
		var form_parent = '#".$module_settings["form_id"]."';
		// debug
		// alert(price_parent + ' : ' + form_parent);

		// duplicate price area inside window (for window option form)
		var origPriceID = 'CWproductPrices-".$module_settings["product_id"]."';
		var windowPriceID = 'CWproductPrices-".$module_settings["product_id"]."w';
		jQuery('div#' + origPriceID).clone().insertBefore('#CWformAddToCartWindow').attr('id',windowPriceID).addClass('CWwindowPrice');

		// build string for selectors below
		var target_orig_low = price_parent + ' ' + '.CWproductPriceLow';
		var target_orig_high = price_parent + ' ' + '.CWproductPriceHigh';
		var target_tax_low = price_parent + ' ' + '.CWproductTaxPriceLow';
		var target_tax_high = price_parent + ' ' + '.CWproductTaxPriceHigh';
		var target_disc_low = price_parent + ' ' + '.CWproductPriceDiscLow';
		var target_disc_high = price_parent + ' ' + '.CWproductPriceDiscHigh';
		var target_alt_low = price_parent + ' ' + '.CWproductPriceAltLow';
		var target_alt_high = price_parent + ' ' + '.CWproductPriceAltHigh';

		// get original values
		var default_orig_low = jQuery(target_orig_low).text();
		var default_orig_high = jQuery(target_orig_high).text();
		var default_tax_low = jQuery(target_tax_low).text();
		var default_tax_high = jQuery(target_tax_high).text();
		var default_disc_low = jQuery(target_disc_low).text();
		var default_disc_high = jQuery(target_disc_high).text();
		var default_alt_low = jQuery(target_alt_low).text();
		var default_alt_high = jQuery(target_alt_high).text();

		// clear placeholder value on load
		jQuery('#availSkus').val();

		// create hidden copies of select elements
		jQuery(form_parent + ' ' +  'select.CWoption').each(function(){
			var res = jQuery(this).parents('div').next('div.CWoptionRes');
			// add temp id with separator -
			var tempID = jQuery(this).attr('id') + '-temp';
			var tempName = jQuery(this).attr('name') + '-temp';
			jQuery(this).clone().removeClass().addClass('CWoptionTemp').attr('id',tempID).attr('name',tempName).appendTo(res);
			// make sure res is hidden
			jQuery(res).hide();
		});

		// restore removed selection list
		var \$restoreSelect = function(selectList){
			// target the -temp copy of the select list
			var origId = jQuery(selectList).attr('id');
			var formID = jQuery(selectList).parents('form').attr('id');
			//alert(form_parent + ' ' + origId);
			var reserveList = jQuery(form_parent + ' ' + 'select#' + origId + '-temp');
			// show the hidden list, remove all options
			jQuery(selectList).show().children('option').remove();
			// hide the text value
			jQuery(selectList).siblings('span.CWselectedText').hide();
			// copy original options back to parent
			jQuery(reserveList).children('option').each(function(){
				jQuery(this).clone().appendTo(selectList);
			});
			// set the default non-selected option
			jQuery(selectList).children('option.sku0').prop('selected','selected');
		};

		// restore removed option to original parent
		var \$restoreOption = function(optionClass){
				// get all options in reserve copy elements
				jQuery(form_parent + ' ' + 'select.CWoptionTemp').children('option.' + optionClass).each(function(){
				// get the id of the original element
				var origId = jQuery(this).parents('select').attr('id').split('-');
				var origSelect = jQuery(form_parent + ' ' + 'select#' + origId[0]);
				// get the value of the currently restored option
				var restoreVal = jQuery(this).val();
				jQuery(origSelect).children('option[value=' + restoreVal + ']').remove();
				jQuery(this).clone().appendTo(origSelect).prop('selected','');
				// show parent with restored options
				jQuery(origSelect).show().siblings('span.CWselectedText').remove();
				//alert(origId[0]);
			});
		};

		// master function to set options
		var \$setOptions = function(selectList){

			// create array from skus query above
			// sku_id, orig_price, alt_price, discount_price
			var sku_arr = new Array();
			var id_arr = new Array();
";
		// loop query, build array of sku ids and prices 
		$rowCt=0;
		for($i=0;$i < $skusQuery['totalRows'];$i++) {
			$formScriptVar .= "
						sku_arr[".$rowCt."] = new Array();
						sku_arr[".$rowCt."][\"sku_id\"] = '".$skusQuery['sku_id'][$i]."';
						sku_arr[".$rowCt."][\"orig_price\"] = '".$skusQuery['sku_price'][$i]."';
						sku_arr[".$rowCt."][\"alt_price\"] = '".$skusQuery['sku_alt_price'][$i]."';
";
			// discount price defaults to regular price 
			$discountedPrice = $skusQuery["sku_price"][$i];
			// get actual discount if being used 
			if($_ENV["application.cw"]["discountsEnabled"]) {
				$discountAmount = CWgetSkuDiscountAmount(
													$skusQuery["sku_id"][$i],
													'sku_cost',
													null, null, null,
													$module_settings["customer_id"],
													null,
													$module_settings["promo_code"]);
				if ($discountAmount > 0) {
					$discountedPrice = $skusQuery["sku_price"][$i] - $discountAmount;
				}
			}
			$formScriptVar .= "
					sku_arr[".$rowCt."][\"discount_price\"] = '".$discountedPrice."';
";
			if (is_array($module_settings["tax_rate"]) && isset($module_settings["tax_rate"]["calcTax"]) && $module_settings["tax_rate"]["calcTax"] > 0) {
				// if discounts are active, apply to tax price 
				if($_ENV["application.cw"]["discountsEnabled"] && $discountedPrice != $skusQuery["sku_price"][$i] && is_numeric($discountedPrice)) {
					$formScriptVar .= "
					sku_arr[".$rowCt."][\"tax_price\"] = '".($discountedPrice * $module_settings["tax_rate"]["calcTax"])."';
";
				// if no discount 
				} else {
					$formScriptVar .= "
					sku_arr[".$rowCt."][\"tax_price\"] = '".($skusQuery["sku_price"][$i] * $module_settings["tax_rate"]["calcTax"])."';
";
				}
			}
			$formScriptVar .= "
					id_arr[".$rowCt."] = '".$skusQuery["sku_id"][$i]."';
					//debug
					//console.debug('".$rowCt." id array: ' + id_arr);
					//console.debug(id_arr[".$rowCt."]);
					//console.debug(sku_arr[".$rowCt."][\"sku_id\"]);
					//console.debug(sku_arr[".$rowCt."][\"discount_price\"]);
";
			$rowCt++;
		}
		$formScriptVar .= "
			// get the class(es) of the selected option that was changed
			var selOpt = jQuery(selectList).find('option:selected');
			var selClass = jQuery(selOpt).attr('class');
			// create array for list of classes
			var selected_arr = selClass.split(\" \");
			//console.debug('selected: ' + selected_arr);
			// placeholder array
			var keep_arr = new Array();
			var show_arr = new Array();
			// loop all option lists other than currently selected
			jQuery(form_parent + ' ' + 'select.CWoption').not(selectList).each(function(){
				// get array of classes for this element
				var selSiblingClass = jQuery(this).find('option:selected').attr('class');
				var sibling_arr = selSiblingClass.split(\" \");

				// if a non-blank option is selected, compare arrays
				if (sibling_arr[0] != 'sku0'){

					// loop the sibling array
						for(var i = 0;i < sibling_arr.length;i++){
						var matchClass = sibling_arr[i];
							//if in selected array, but not found in new array
							if (jQuery.inArray(matchClass,selected_arr) > -1 && jQuery.inArray(matchClass,keep_arr) == -1){
							// add to placeholder array
							keep_arr = keep_arr.concat(matchClass);

							}
						};
					// show selected skus list to firebug console if enabled
					//console.debug('sibling: ' + sibling_arr);
				}
			});
			// end loop all option lists

			// loop the new array
			for(var i = 0;i < keep_arr.length;i++){
				var keepClass = keep_arr[i];
				//if found in selected array
				if (jQuery.inArray(keepClass,selected_arr) != -1){
					//if not in show array
					if (jQuery.inArray(keepClass,show_arr) == -1){
						// copy to show array
						show_arr = show_arr.concat(keepClass);
					}
				}
			};
			if (show_arr == '' && selected_arr != 'sku0'){
				show_arr = selected_arr;
			}

			// show keeper list to console if enabled
			//console.debug('id array: ' + id_arr);
			//console.debug('keep: ' + keep_arr);

		// placeholder value in storage field
		if(show_arr != 'sku0'){
			jQuery('#availSkus').val(show_arr);
		} else {
			jQuery('#availSkus').val();
		};

		//----------- SELECTION FUNCTIONS ----------//
		//--- change display based on selections ---//
		//------------------------------------------//

			// if not resetting
				if(show_arr != ''){
					//alert('not reset');
					// remove keeper attribute from all options to clear selection
					jQuery(form_parent + ' ' + 'select.CWoption option').removeAttr('rel','');
					// set up pricing variables
					var orig_price_arr = new Array();
					var alt_price_arr = new Array();
					var disc_price_arr = new Array();
";
		if(isset($module_settings["tax_rate"]["calcTax"]) && $module_settings["tax_rate"]["calcTax"] > 0) {
			$formScriptVar .= "
					var tax_price_arr = new Array();
";
		}
		$formScriptVar .= "
					// loop classes in array
					for(var i = 0;i < show_arr.length;i++){
						var matchClass = show_arr[i];

						// get index of match to original sku_arr
						var skuID = matchClass.replace('sku','');
						var matchPos = jQuery.inArray(skuID,id_arr);
						// if matched
						if((matchClass != '')&&(matchPos != -1)){
							//add keeper attribute
						 	jQuery(\"'select.CWoption option.\" + matchClass + \"'\").attr('rel','keeper');
							// get skuID from the class name
							// set up price arrays

							// if the current value is not in the array, add it
							if (jQuery.inArray(sku_arr[matchPos][\"orig_price\"],orig_price_arr) == -1){
								orig_price_arr[i] = sku_arr[matchPos][\"orig_price\"];
							}
							if (jQuery.inArray(sku_arr[matchPos][\"alt_price\"],alt_price_arr) == -1){
								alt_price_arr[i] = sku_arr[matchPos][\"alt_price\"];
							}
							if (jQuery.inArray(sku_arr[matchPos][\"discount_price\"],disc_price_arr) == -1){
								disc_price_arr[i] = sku_arr[matchPos][\"discount_price\"];
							}
";
		if(isset($module_settings["tax_rate"]["calcTax"]) && $module_settings["tax_rate"]["calcTax"] > 0) {
			$formScriptVar .= "
							if (jQuery.inArray(sku_arr[matchPos][\"tax_price\"],tax_price_arr) == -1){
								tax_price_arr[i] = sku_arr[matchPos][\"tax_price\"];
							}
";
		}
		$formScriptVar .= "
						}
					}

						// sort the arrays
						orig_price_arr.sort(function(a,b){return a - b});
						alt_price_arr.sort(function(a,b){return a - b});
						disc_price_arr.sort(function(a,b){return a - b});
";
		if(isset($module_settings["tax_rate"]["calcTax"]) && $module_settings["tax_rate"]["calcTax"] > 0) {
			$formScriptVar .= "
						tax_price_arr.sort(function(a,b){return a - b});
";
		}
		$formScriptVar .= "
						// original price
						var origLen = orig_price_arr.length - 1;
						var val_orig_low = parseFloat(orig_price_arr[0]).toFixed(2);
						var val_orig_high = parseFloat(orig_price_arr[origLen]).toFixed(2);
						// set low price
						jQuery(target_orig_low).text(\$cwCurrencyFormat(val_orig_low));
						// hide or set high price
						if (origLen > 0){
						jQuery(target_orig_high).show().text(\$cwCurrencyFormat(val_orig_high)).siblings('.priceDelim').show();
						} else {
						jQuery(target_orig_high).hide().siblings('.priceDelim').hide();
						};

						// discount price
						var discLen = disc_price_arr.length - 1;
						var val_disc_low = parseFloat(disc_price_arr[0]).toFixed(2);
						var val_disc_high = parseFloat(disc_price_arr[discLen]).toFixed(2);
						// set low price
						jQuery(target_disc_low).text(\$cwCurrencyFormat(val_disc_low));
						// hide or set high price
						if (discLen > 0){
						jQuery(target_disc_high).text(\$cwCurrencyFormat(val_disc_high));
						} else {
						jQuery(target_disc_high).hide().siblings('.priceDelim').hide();
						};

						// tax price range
";
		if(isset($module_settings["tax_rate"]["calcTax"]) && $module_settings["tax_rate"]["calcTax"] > 0) {
			$formScriptVar .= "
						var taxLen = tax_price_arr.length - 1;
						var val_tax_low = parseFloat(tax_price_arr[0]).toFixed(2);
						var val_tax_high = parseFloat(tax_price_arr[taxLen]).toFixed(2);
						// set low price
						jQuery(target_tax_low).text(\$cwCurrencyFormat(val_tax_low));
						// hide or set high price
						if (taxLen > 0){
						jQuery(target_tax_high).text(\$cwCurrencyFormat(val_tax_high));
						} else {
						jQuery(target_tax_high).hide().siblings('.priceDelim').hide();
						};
";
		}
		$formScriptVar .= "
						// alt price
						var altLen = alt_price_arr.length - 1;
						var val_alt_low = parseFloat(alt_price_arr[0]).toFixed(2);
						var val_alt_high = parseFloat(alt_price_arr[altLen]).toFixed(2);
						// set low price
						jQuery(target_alt_low).text(\$cwCurrencyFormat(val_alt_low));
						// hide or set high price
						if (altLen > 0){
						jQuery(target_alt_high).text(\$cwCurrencyFormat(val_alt_high));
						} else {
						jQuery(target_alt_high).hide().siblings('.priceDelim').hide();
						};

					// end loop classes

					// keep the 'select' option
					jQuery(form_parent + ' ' + 'select.CWoption option.sku0').attr('rel','keeper');

					// remove all non-keeper prices
					jQuery(form_parent + ' ' + 'select.CWoption option[rel!=keeper]').remove();
				// if resetting
				} else {
					// restore original prices
					jQuery(price_parent).find('span').show();
					jQuery(target_orig_low).text(default_orig_low);
					jQuery(target_orig_high).text(default_orig_high);
					jQuery(target_tax_low).text(default_tax_low);
					jQuery(target_tax_high).text(default_tax_high);
					jQuery(target_disc_low).text(default_disc_low);
					jQuery(target_disc_high).text(default_disc_high);
					jQuery(target_alt_low).text(default_alt_low);
					jQuery(target_alt_high).text(default_alt_high);
				};
				// end if resetting

			// if only one option remains in a list, show the option
			jQuery(form_parent + ' ' + 'select.CWoption:visible').each(function(){
					// debug - alerts show values being set
					//alert('select id: ' + jQuery(this).attr('id'));
					var numOpts = jQuery(this).children('option').not('.sku0').length;
					//alert('active options: ' + numOpts);
					// if one option
					if (numOpts == 1){
					// set option to selected
					var keepOpt = jQuery(this).children('option').not('.sku0');
					var keepVal = jQuery(keepOpt).val();
					jQuery(keepOpt).prop('selected','selected');
					// set up the value to show
					var valueText = jQuery(keepOpt).text();

					// show reset link if more than one option originally
					var origOpts = jQuery('select#' + jQuery(this).attr('id') + '-temp').children('option[class!=sku0]').length;
					if (origOpts == 1){
					var resetLink = '';
					} else {
					var resetLink = '<a href=\"#\" class=\"CWselectReset\">[x]</a>';
					};
					var valueShow = '<span class=\"CWselectedText\">' + valueText + resetLink + '</span>';
					var selLabel = jQuery(this).prev('label');
					jQuery(selLabel).removeClass('warning');
					// add the value before the select list
					jQuery(valueShow).insertBefore(jQuery(this));
					// hide the select list - focus and blur triggers validation reset
					jQuery(this).trigger('focus').trigger('blur').removeClass('required').hide();
					};
			});
			// remove duplicates (ie)
			jQuery(form_parent + ' ' + '.CWselectedText + .CWselectedText').remove();
			// end if only one option
		// end handle selection

		// reset option when clicking
		jQuery(form_parent + ' ' + 'a.CWselectReset').click(function(){
			var parentSelect = jQuery(this).parents('span').siblings('select.CWoption');
			//alert(jQuery(parentSelect).attr('id'));
			jQuery(parentSelect).show();
			\$restoreSelect(parentSelect);
			// create array for selected classes
			var restoreOption = jQuery(parentSelect).children('option').not('.sku0');
			jQuery(restoreOption).each(function(){
				var restoreClass = jQuery(this).attr('class');
				var restore_arr = restoreClass.split(\" \");
					// loop classes in array
					for(var i = 0;i < restore_arr.length;i++){
						var matchClass = restore_arr[i];
						//alert(matchClass);
						// if matched, restore options
						if(matchClass != '' && matchClass !='sku0'){
								\$restoreOption(matchClass);
						}
					}
					// end loop classes
			});
			// run the set options function based on the first select box w/ more than one visible option
			jQuery(form_parent + ' ' + 'select.CWoption:visible').not('.required').addClass('required');
			\$setOptions(jQuery(form_parent + ' ' + 'select.CWoption:visible:first'));
			return false;
		});

		// end \$setOptions function
		};

";
		if($module_settings["display_type"] != 'table') {
			$formScriptVar .= "
		// run on page load, based on first select list
		\$setOptions(jQuery(form_parent + ' ' + 'select.CWoption:first'));
";
		}
		$formScriptVar .= "
	// run on change
	jQuery(form_parent + ' ' + 'select.CWoption').change(function(){
		\$setOptions(jQuery(this));
	});

	// end jLoad
	};
	// run entire script above as jLoad function when new form is invoked, passing in form name

	\$jLoad();
";
	// //example:
	// //\$jLoad(jQuery('#".$module_settings["form_id"]."'));
		$formScriptVar .= "
	});
	</script>
	<!-- end selection boxes script -->
";
		CWinsertHead($formScriptVar);
		// end content for head of page 
	}
}
// / END SELECT SCRIPT 
?>