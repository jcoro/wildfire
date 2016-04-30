<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-product-skus.php
File Date: 2012-05-12
Description: Manages skus as an include for product-details.php
==========================================================
NOTE:
Product / SKU actions are handled in the parent template, "product-details.php"
*/
//default values from ProductForm page, used for processing
if (!isset($_ENV["request.cwpage"]['listProductOptions'])) { $_ENV["request.cwpage"]['listProductOptions'] = ''; }
if (!isset($skusQuery)) { $skusQuery = ''; }
if (!isset($productOptionsRelQuery)) { $productOptionsRelQuery = ''; }
if (!isset( $_GET['skumode'] )) { $_GET['skumode'] = $_ENV["application.cw"]["adminSkuEditMode"]; }
if (!isset($_ENV["request.cwpage"]['adminSkuEditMode'])) { $_ENV["request.cwpage"]['adminSkuEditMode'] = $_GET['skumode']; }
// downloads enabled: shorten for easy reference, request scope can be overridden per instance 
$_ENV["request.cwpage"]["ddok"] = $_ENV["application.cw"]["appDownloadsEnabled"];
$varsToKeep = CWremoveUrlVars("showtab,useralert,userconfirm,sortby,sortdir,skumode");
$_ENV["request.cwpage"]['skuURL'] = CWserializeURL($varsToKeep, $_ENV["request.cw"]["thisPage"]) . '&showtab=4';
// SKU JAVASCRIPT 
$skuFormScript = '<script type="text/javascript">
		jQuery(document).ready(function() {
		// text and numeric inputs in the new sku form
		jQuery(' . "'form.CWSKUvalidate :input'" . ').change(function() {
			if(jQuery(this).val()!=' . "''" . ' && jQuery(this).val()!=' . "'0'" . ' && jQuery(this).val()!=jQuery(this)[0].defaultValue) {
			jQuery(this).addClass(' . "'changed'" . ');
			} else {
			jQuery(this).removeClass(' . "'changed');
			jQuery(this).removeClass('changed');
			}
		}).keyup(function() {
			jQuery(this).trigger('change');
		});
		// inputs in the edit sku form
		jQuery('form.updateSKU :input').change(function() {
		var defval = jQuery(this)[0].defaultValue;
		if (jQuery(this).val()!=defval) {
			jQuery(this).addClass('changed');
		} else {
			jQuery(this).removeClass('changed');
		}
		}).keyup(function() {
			jQuery(this).trigger('change');
		});
		// selects in the edit sku form
		jQuery('form.updateSKU select').change(function() {
		var defval = jQuery(this).find('option[defaultSelected=true]').val();
		if (jQuery(this).val()!=defval) {
			jQuery(this).addClass('changed');
		} else {
			jQuery(this).removeClass('changed');
		}
		}).keyup(function() {
			jQuery(this).trigger('change');
		});
		// list view
		";
if ($_ENV["request.cwpage"]['adminSkuEditMode'] == 'list'){
    $skuFormScript .="
		jQuery('tr.optionsRow').hide(); jQuery('a.showOptions').click(function() {
		jQuery(this).parents('tr').next('tr.optionsRow').toggle();
		return false;
		});
		jQuery('a.showOptions').parent('td').click(function() {
		jQuery(this).find('a.showOptions').click();
		});
		";
}
// javascript for file uploads 
$skuFormScript .="
// upload file
    jQuery('a.showFileUploader').click(function(){
        var thisSrcUrl = jQuery(this).attr('href');
        jQuery(this).parents('td').find('div.fileUpload').show().children('iframe').attr('src',thisSrcUrl);
        jQuery(this).parents('td').find('div.alert').hide();
        return false;
    });
    // end upload file
    
    // select file
    jQuery('a.showFileSelector').click(function(){
        var thisSrcUrl = jQuery(this).attr('href');
        jQuery(this).parents('td').find('div.fileUpload').show().children('iframe').attr('src',thisSrcUrl);
        jQuery(this).parents('td').find('div.alert').hide();
        return false;
    });
    // end select file
    
    // clear file
    jQuery('a.showFileSelector').click(function(){
        jQuery(this).parents().siblings('input.fileInput').val('').siblinlgs('img.productFilePreview').attr('src','');
        jQuery(this).parents('td').find('div.fileUpload').hide().children('iframe').attr('src','');
        jQuery(this).parents('td').find('div.alert').hide();
        return false;
    });
    //end clear file
});
	</script>";
CWinsertHead($skuFormScript);
// if we have some options (we can make more skus),
//or if we have no options and no skus yet (we can make 1 sku) 
if ($_ENV["request.cwpage"]['listProductOptions'] !== "" || (
	    $_ENV["request.cwpage"]['listProductOptions'] === "" && isset($skusQuery['totalRows']) && $skusQuery['totalRows'] < 1)) {
?>	
    <div class="CWformButtonWrap">
        <a id="showNewSkuFormLink" href="#" class="CWbuttonLink">Add New SKU</a>
        <a id="hideNewSkuFormLink" href="#" class="CWbuttonLink">Cancel New SKU</a>
    </div>
    <div class="clear">&nbsp;</div>
    <?php // NEW SKU FORM : loads hidden until link is clicked (or simulated click) ?>
	<!-- SKU FORM -->
    <form name="addSkuForm" id="addSkuForm" class="CWSKUvalidate" method="post" action="<?php echo $_ENV["request.cwpage"]['skuURL']; ?>&skumode=<?php echo $_GET['skumode']; ?>&sortby=<?php echo $_GET['sortby']; ?>&sortdir=<?php echo $_GET['sortdir']; ?>" style="display:none;">
        <!-- NEW SKU TABLE -->
        <table class="CWformTable wide">
    	<!-- table header -->
    	<tr>
    	    <th>
    		Add New SKU
    	    </th>
    	</tr>
    	<tr>
    	    <!-- table body -->
    	    <td>
    		<!-- form container -->
    		<table class="wide">
    		    <!-- form headers -->
    		    <tr class="headerRow">
    			<th>SKU Name</th>
    			<th>On Web</th>
    			<th valign="top">Price</th>
<?php
	if ($_ENV["application.cw"]["adminProductAltPriceEnabled"]) {
?>
				<th valign="top"><?php echo $_ENV["application.cw"]["adminLabelProductAltPrice"]; ?></th>
<?php
	}
?>
    			<th valign="top">Sort</th>
    			<th valign="top">Weight</th>
    			<th valign="top">Ship Cost</th>
    			<th valign="top">Stock</th>
    		    </tr>
    		    <!-- form inputs -->
    		    <tr>
    			<!-- sku Merchant ID (part number) -->
    			<td>
    			    <input name="sku_merchant_sku_id" type="text" size="17" value="<?php echo $_POST['sku_merchant_sku_id']; ?>" class="required" title="SKU Name is required">
    			</td>
    			<!-- show on web yes/no -->
    			<td>
    			    <select name="sku_on_web" id="sku_on_web">
<?php
	if (isset($_POST['sku_on_web']) && $_POST['sku_on_web'] == 0) {
?>
					<option value="1" >Yes</option>
					<option value="0" selected="selected">No</option>
<?php
	} else {
?>
					<option value="1" selected="selected">Yes</option>
					<option value="0">No</option>
<?php
	}
?>
    			    </select>
    			</td>
    			<!-- price -->
    			<td valign="top">
<?php
	if (!is_numeric($_POST['sku_price']) || $_POST['sku_price'] == '') {
		$_POST['sku_price'] = 0;
	}
?>
    			    <input name="sku_price" type="text" value="<?php echo $_POST['sku_price']; ?>" size="6"  maxlength="12" class="required"  title="SKU Price is required" onkeyup="extractNumeric(this,2,false)"  onblur="checkValue(this)">
    			</td>
    			<!-- alt price -->
<?php
	if ($_ENV["application.cw"]["adminProductAltPriceEnabled"]) {
?>	
				<td valign="top">
				    <input name="sku_alt_price" type="text" value="<?php echo $_POST['sku_alt_price']; ?>" size="6"maxlength="12" class="required"  title="SKU <?php echo $_ENV["application.cw"]["adminLabelProductAltPrice"]; ?> is required" onkeyup="extractNumeric(this,2,false)"  onblur="checkValue(this)">
				</td>
<?php
	}
?>
    			<!-- sort -->
    			<td valign="top">
    			    <input name="sku_sort" type="text" class="sort required" value="<?php echo $_POST['sku_sort']; ?>" size="5" maxlength="7" title="SKU Sort is required" onkeyup="extractNumeric(this,2,true)" onblur="checkValue(this)" >
    			</td>
    			<!-- weight -->
    			<td valign="top">
    			    <input name="sku_weight" type="text" value="<?php echo $_POST['sku_weight']; ?>" size="5" maxlength="7" class="required" title="SKU Weight is required" onkeyup="extractNumeric(this,2,false)" onblur="checkValue(this)">
    			</td>
    			<!-- ship base -->
    			<td valign="top">
    			    <input name="sku_ship_base" type="text" value="<?php echo $_POST['sku_ship_base']; ?>" size="6"maxlength="12" class="required" title="SKU Ship Cost is required" onkeyup="extractNumeric(this,2,false)" onblur="checkValue(this)">
    			</td>
    			<!-- stock -->
    			<td valign="top">
    			    <input name="sku_stock" type="text" value="<?php echo $_POST['sku_stock']; ?>" size="5" maxlength="7" class="required"  title="SKU Stock is required" onkeyup="extractNumeric(this,0,true)" onblur="checkValue(this)">
    			</td>
    		    </tr>
    		    <!-- /end form inputs -->
    		</table>
    		<!-- / end form container -->
    		<!-- SKU OPTIONS -->
<?php
	if (isset($productOptionsRelQuery['totalRows']) && $productOptionsRelQuery['totalRows']) {
?>	
			<!-- sku options container -->
			<table class="CWformTable">
			    <tr class="headerRow">
				<th colspan="2">
				    SKU Options
				</th>
			    </tr>
<?php
		// output options, 1 per row ---
		$lastOptionType = -1;
		$madeNewRow = FALSE;
		for ($i = 0; $i < $productOptionsRelQuery['totalRows']; $i++) {
			$lastOptionType = $productOptionsRelQuery['optiontype_id'][$i];
?>
				<tr>
                	<th class="label"><?php echo $productOptionsRelQuery['optiontype_name'][$i]; ?></th>
                    <td><select name="selOption<?php echo $i; ?>">
<?php
			if (!(isset($intChosenOption))) $intChosenOption = 0;
			// Get options from form submission if there is one
			if (isset($_POST['sku_id']) && isset($_POST["selOption".$i])) {
				$intChosenOption = $_POST["selOption".$i];
			}
			while ($i < $productOptionsRelQuery['totalRows'] && $lastOptionType == $productOptionsRelQuery['optiontype_id'][$i]) {
?>
							<option value="<?php echo $productOptionsRelQuery['option_id'][$i]; ?>"<?php if ($intChosenOption == $productOptionsRelQuery['option_id'][$i]) { echo " selected"; } ?>><?php echo $productOptionsRelQuery['option_name'][$i]; ?></option>
<?php
				$i++;
			}
			$i--;
?>
						</select>
                    </td>
                </tr>
<?php
		}
?>
			</table>
<?php
	}
        // file upload
        if ($_ENV["request.cwpage"]["ddok"]) {
            // build link for file uploader or selector 
            $fileUploadUrl="product-file-upload.php?uploadSku=0";
            // set up file preview link if file exists 
?>
                        <table class="CWformTable wide">
                            <tbody>
                                <tr class="headerRow">
                                    <th colspan="2">File Management</th>
                                </tr>
                                <tr>
                                    <th class="label">
                                        SKU Download File:
                                    </th>
                                    <?php // file upload field / content area ?>
                                    <td class="noHover">
                                        <input name="sku_download_file" id="sku_download_file-0" type="text" value="" class="fileInput" size="40">
                                        <?php // links w/ file upload field ?>
                                        <span class="fieldLinks">
                                        <?php // ICON: upload field ?>
                                        <a href="<?php echo $fileUploadUrl; ?>" title="Upload file" class="showFileUploader"><img src="img/cw-image-upload.png" alt="Upload file" class="iconLink"></a>
                                        <?php // ICON: clear filename field ?>
                                        <img src="img/cw-delete.png" title="Clear file field" class="iconLink clearFileLink">
                                        </span>
                                        <?php // file content area ?>
                                        <div class="productFileContent">
                                            <?php // file uploader ?>
                                            <div class="fileUpload" style="display:none;">
                                                <iframe width="460" height="94" frameborder="no" scrolling="false">
                                                </iframe>
                                            </div>
                                        </div>
                                    <?php // hidden input for file download id ?>
                                    <input name="sku_download_id" id="sku_download_id-0" type="hidden" value="">
                                    </td>
                                </tr>
                                <tr>
                                    <th class="label">
                                        Download Limit (0 = no limit):
                                    </th>
                                    <td class="noHover">
                                        <input name="sku_download_limit" type="text" size="10" value="<?php echo $_ENV["application.cw"]["appDownloadsLimitDefault"]; ?>" maxlength="7" onkeyup="extractNumeric(this,0,true)" onblur="checkValue(this);">
                                    </td>
                                </tr>
                                <tr>
                                    <th class="label">
                                        File Version (optional):
                                    </th>
                                    <td class="noHover">
                                        <input name="sku_download_version" type="text" size="10" value="">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
<?php       
        }
        // end file upload
?>
	   	    </td>
    	</tr>
        </table>
			<?php // end sku options output   ?>
        <div class="CWformButtonWrap">
    	<input name="AddSKU" id="AddSKUbutton" type="submit" class="submitButton" value="Add New SKU">
        </div>
		    
	<?php // hidden field for adding new vs. updating in processing code   ?>
        <input name="newsku" type="hidden" value="1">
    <?php // hidden field for product id    ?>
        <input name="sku_product_id" type="hidden" id="productID" value="<?php echo $_GET['productid'] ?>">
    </form>
<?php
} else {
?>	
    <div class="confirm">
        <br>
        <strong>&nbsp;&nbsp;No Options: only one sku allowed for this product</strong>
        <br>
        <br>
    </div>	
<?php
}
// end if we have some options 
// /////////////////////////////////////////////////// 
// /////////////// EXISTING SKUS ///////////////////// 
// /////////////////////////////////////////////////// 
?>
<!-- EXISTING SKUS -->
<?php
if (isset($skusQuery['totalRows']) && $skusQuery['totalRows'] > 0) {
	// if we have existing skus 
	// LIST VIEW 
	if ($_ENV["request.cwpage"]['adminSkuEditMode'] == 'list') {
		// form for updating all skus at once 
?>
	<form method="post" name="updateSKU" class="updateSKU CWobserve" action="<?php echo $_ENV["request.cwpage"]['skuURL']; ?>&skumode=<?php echo $_GET['skumode']; ?>&sortby=<?php echo $_GET['sortby']; ?>&sortdir=<?php echo $_GET['sortdir']; ?>">
	    <input name="sku_product_id" type="hidden" value="<?php echo $skusQuery['sku_product_id'][0]; ?>">  <?php // just use the first element of the array, should all be identical ?>
	    <input name="sku_editmode" type="hidden" value="<?php echo $_ENV["request.cwpage"]['adminSkuEditMode']; ?>">
<?php
		if ($_ENV["application.cw"]["adminSkuEditModeLink"] || in_array($_SESSION["cw"]["accessLevel"], array('developer'))) {
?>	
	        <a href="<?php echo $_ENV["request.cwpage"]['skuURL']; ?>&skumode=standard" class="CWbuttonLink SKUviewLink">Expanded View</a>
<?php
		}
		// save button 
?>
	    <input name="UpdateSKUbutton" ID="UpdateSKUbutton" type="submit" class="submitButton updateSKU" value="Save Changes">
	    <div class="clear"></div>
	    <table class="CWsort CWstripe CWformTable" summary="<?php echo $_ENV["request.cwpage"]['skuURL']; ?>&skumode=<?php echo $_GET['skumode']; ?>">
		<thead>
		    <tr class="headerRow sortRow">
                <th class="sku_merchant_sku_id">SKU</th>
                <th class="sku_on_web">On Web</th>
                <th class="sku_price">Price</th>
<?php
		if ($_ENV["application.cw"]["adminProductAltPriceEnabled"]) {
?>	
	    		<th class="sku_alt_price"><?php echo $_ENV["application.cw"]["adminLabelProductAltPrice"]; ?></th>
<?php
		}
?>
                <th class="sku_sort">Sort</th>
                <th class="sku_weight">Weight</th>
                <th class="sku_ship_base">Ship Cost</th>
                <th class="sku_stock">Stock</th>
                <th class="noSort" style="text-align:center;width:80px;">
                    <input name="all0" id="relProdAll0" type="checkbox" class="checkAll" rel="all0">Delete
                </th>
		    </tr>
		</thead>
		<tbody>
<?php
		// loop skus query 
		$rowCt = 0;
		$disableCt = 0;
		for ($i = 0; $i < $skusQuery['totalRows']; $i++) {
			$rowCt++;
			// QUERY: get SKU options 
			$skuOptionsQuery = CWquerySelectSkuOptions($skusQuery['sku_id'][$i]);
			// set up a list of sku options 
			$listSKUOptions = '';
			if (isset($skuOptionsQuery['sku_option2option_id']) && is_array($skuOptionsQuery['sku_option2option_id'])) {
				$listSKUOptions = implode(',', $skuOptionsQuery['sku_option2option_id']);
			}
			// QUERY: check for orders on this SKU --- >
			$ordersQuery = CWqueryCountSKUOrders($skusQuery['sku_id'][$i]);
			if ($ordersQuery > 0) {
				$HasOrders = 1;
				$DisabledText = ' disabled="disabled"';
			} else {
				$HasOrders = 0;
				$DisabledText = '';
			}
			// handle error if sku cannot be deleted 
			if ( isset($_POST['CantDeleteSKU']) ) {
				if ( $_GET['delete_sku_id'] == $skusQuery['sku_id'][$i] ) {
?>
					<div class="alert"><?php echo $_POST['CantDeleteSKU']; ?></div>
<?php
				}
			}
			// SKU DETAILS 	
?>
			<tr>
			    <td>
<?php
			echo $skusQuery['sku_merchant_sku_id'][$i];
			if ((isset($productOptionsRelQuery['totalRows']) && $productOptionsRelQuery['totalRows']) || ($_ENV["request.cwpage"]["ddok"] == true)) {
?>	
				<br>
				<a class="showOptions smallPrint" href="#">Options</a>
<?php
			}
?>
			    </td>
	    	    <!-- on web -->
			    <td>
				<select name="sku_on_web<?php echo $rowCt; ?>" id="sku_on_web<?php echo $rowCt ?>">
<?php
			if ($skusQuery['sku_on_web'][$i] != 0) {
?>	
				    <option value="1" selected="selected">
					Yes
				    <option value="0">
					No
<?php
			} else {
?>
				    <option value="1" >
					Yes
				    <option value="0" selected="selected">
					No
<?php
			} 
?>
			</select>
		    </td>
		    <!-- price -->
		    <td>
				<input name="sku_price<?php echo $rowCt; ?>" type="text" value="<?php echo number_format($skusQuery['sku_price'][$i], 2, '.', ''); ?>" size="5" maxlength="12" onblur="checkValue(this)" onKeyUp="extractNumeric(this,2,false)">
		    </td>
			    <!-- alt price -->
<?php
			if ($_ENV["application.cw"]["adminProductAltPriceEnabled"]) {
?>	
		    <td>
				<input name="sku_alt_price<?php echo $rowCt; ?>" type="text" value="<?php echo number_format($skusQuery['sku_alt_price'][$i], 2, '.', ''); ?>" size="5" maxlength="12" onblur="checkValue(this)" onKeyUp="extractNumeric(this,2,false)">
		    </td>
<?php
			}
?>
		    <!-- sort -->
		    <td>
				<input name="sku_sort<?php echo $rowCt; ?>" class="sort" type="text" value="<?php echo $skusQuery['sku_sort'][$i]; ?>" size="2" maxlength="7" onblur="checkValue(this)" onkeyup="extractNumeric(this,2,true)">
		    </td>
		    <!-- weight-->
		    <td>
				<input name="sku_weight<?php echo $rowCt; ?>" type="text" value="<?php echo $skusQuery['sku_weight'][$i]; ?>" size="3" maxlength="7" onblur="checkValue(this)" onkeyup="extractNumeric(this,2,false)">
		    </td>
		    <!-- ship base -->
		    <td>
				<input name="sku_ship_base<?php echo $rowCt; ?>" type="text" value="<?php echo number_format($skusQuery['sku_ship_base'][$i], 2, '.', ''); ?>" size="5"maxlength="7"onblur="checkValue(this)" onKeyUp="extractNumeric(this,2,false)">
		    </td>
		    <!-- stock-->
		    <td>
				<input name="sku_stock<?php echo $rowCt; ?>" type="text" value="<?php echo $skusQuery['sku_stock'][$i]; ?>" size="2" maxlength="7" onblur="checkValue(this)" onkeyup="extractNumeric(this,0,true)">
		    </td>
		    <?php // delete checkbox  ?>
		    <td style="text-align: center;" width="80">
<?php
			if ($HasOrders != 1) {
?>
				<input type="checkbox" name="deletesku_id<?php echo $rowCt; ?>" value="<?php echo $skusQuery['sku_id'][$i]; ?>" class="all0 formCheckbox" >
<?php
			} else {
				$disableCt++;
			}
			// hidden id fields 
?>
                <input name="sku_id<?php echo $rowCt; ?>" type="hidden" value="<?php echo $skusQuery['sku_id'][$i]; ?>">
                <input name="sku_id[<?php echo $rowCt; ?>]" type="hidden" value="<?php echo $skusQuery['sku_id'][$i]; ?>">
		    </td>
		    </tr>

		    <!-- SKU OPTIONS -->
<?php
			// if we have some product options to show 
			if ((isset($productOptionsRelQuery['totalRows']) && $productOptionsRelQuery['totalRows']) || ($_ENV["request.cwpage"]["ddok"] == true)) {
?>	
		    <tr class="optionsRow">
				<td colspan="9">
			<?php
				// if we have some product options to show 
				if (isset($productOptionsRelQuery['totalRows']) && $productOptionsRelQuery['totalRows']) {
			?>	

				<!-- sku options container -->
				    <table class="CWformTable">
                        <tr class="headerRow">
                            <th colspan="2">SKU Options</th>
                        </tr>
<?php
				// show options, 1 per row 
				if (!$HasOrders) {
					$previousOptionTypeName = "";
					for ($j = 0; $j < $productOptionsRelQuery['totalRows']; $j++) {
						$previousOptionTypeName = $productOptionsRelQuery['optiontype_name'][$j];
?>
						<tr>
							<th class="label noSort"><?php echo $productOptionsRelQuery['optiontype_name'][$j]; ?></th>
							<td>
								<select name="selOption<?php echo $j; ?>_<?php echo $rowCt; ?>"<?php echo $DisabledText; ?>>
<?php
						while ($j < $productOptionsRelQuery['totalRows'] && $previousOptionTypeName == $productOptionsRelQuery['optiontype_name'][$j]) {
							if ( ListFind($listSKUOptions, $productOptionsRelQuery['option_id'][$j],"," ) ) {
?>	
								    <option value="<?php echo $productOptionsRelQuery['option_id'][$j]; ?>" selected="selected"> <?php echo $productOptionsRelQuery['option_name'][$j]; ?></option>
<?php
							} else {						
?>
								    <option value="<?php echo $productOptionsRelQuery['option_id'][$j]; ?>"><?php echo $productOptionsRelQuery['option_name'][$j]; ?></option>
<?php
							}
							$j++;
						}
						$j--;
?>
								</select>
                            </td>
                        </tr>
<?php
					}
				} else {
					$previousOptionTypeName = "";
					for ($j = 0; $j < $productOptionsRelQuery['totalRows']; $j++) {
						$previousOptionTypeName = $productOptionsRelQuery['optiontype_name'][$j];
?>
						<tr>
                        	<th class="label"><?php echo $productOptionsRelQuery['optiontype_name'][$j]; ?></th>
                            <td>
<?php
						while ($j < $productOptionsRelQuery['totalRows'] && $previousOptionTypeName == $productOptionsRelQuery['optiontype_name'][$j]) {
							if (ListFind($listSKUOptions, $productOptionsRelQuery['option_id'][$j],",")) {
							    echo $productOptionsRelQuery['option_name'][$j];
?>
							    <input type="hidden" value="<?php echo $productOptionsRelQuery['option_id'][$j]; ?>" name="selOption<?php echo $j; ?>_<?php echo $rowCt; ?>" id="selOption<?php echo $j; ?>">
<?php
							}
							$j++;
						}
						$j--;
?>						    
							</td>
					    </tr>
<?php
					}
				}
?>
				    </table>
					<?php
						}
					?>
				    <!-- end product options -->
<?php
				// file upload
				if ($_ENV["request.cwpage"]["ddok"]) {
					if (!isset($skusQuery["sku_download_file"][$i])) $skusQuery["sku_download_file"][$i] = "";
					if (!isset($skusQuery["sku_download_id"][$i])) $skusQuery["sku_download_id"][$i] = "";
					if (!isset($skusQuery["sku_download_limit"][$i])) $skusQuery["sku_download_limit"][$i] = "";
					if (!isset($skusQuery["sku_download_version"][$i])) $skusQuery["sku_download_version"][$i] = "";
					// build the link for the file uploader selector 
					$fileUploadUrl="product-file-upload.php?uploadSku=".$skusQuery["sku_id"][$i];
					$fileSelectUrl="product-file-select.php?uploadSku=".$skusQuery["sku_id"][$i];
					// set up file preview link if file exists
?>
                                        <table class="CWformTable wide">
                                            <tbody>
                                                <tr class="headerRow">
                                                    <th colspan="2">File Management</th>
                                                </tr>
                                                <tr>
                                                    <th class="label">
                                                        SKU Download File:
<?php
					// file preview link 
					if (strlen(trim(CWcreateDownloadURL($skusQuery["sku_id"][$i]))) && strlen(trim($skusQuery["sku_download_file"][$i]))) {
						// if the file does not exist, the createdownloadurl function will return '' 
?>
                                            <div class="smallPrint">
                                                <a href="<?php echo CWcreateDownloadURL($skusQuery["sku_id"][$i],'product-file-preview.php'); ?>" rel="external">Download File</a>
                                            </div>
<?php
					}
?>
                                                    </th>
                                                    <?php // file upload filed / content area ?>
                                                    <td class="noHover">
                                                        <input name="sku_download_file<?php echo $rowCt; ?>" id="sku_download_file-<?php echo $skusQuery["sku_id"][$i]; ?>" type="text" value="<?php echo $skusQuery["sku_download_file"][$i]; ?>" class="fileInput" size="40">
                                                        <?php // links w/ file upload field ?>
                                                        <span class="fieldLinks">
                                                        <?php // ICON: upload file ?>
                                                        <a href="<?php echo $fileUploadUrl; ?>" title="Upload file" class="showFileUploader"><img src="img/cw-image-upload.png" alt="Upload file" class="iconLink"></a>
                                                        <?php // ICON: clear filename field ?>
                                                        <img src="img/cw-delete.png" title="Clear file field" class="iconLink clearFileLink">
                                                        </span>
<?php
					// if a file name is recorded, but file is not available, show error warning
					if (strlen(trim($skusQuery["sku_download_file"][$i])) && !strlen(trim(CWcreateDownloadURL($skusQuery["sku_id"][$i])))) {
?>
                                                        <div class="alert">File not available</div>
<?php
					}
					// file content area
?>
                                                        <div class="productFileContent">
                                                            <?php // file uploader ?>
                                                            <div class="fileUpload" style="display:none;">
                                                                <iframe width="460" height="94" frameborder="no" scrolling="false">
                                                                </iframe>
                                                            </div>
                                                        </div>
                                                    <?php // hidden input for download id ?>
                                                    <input name="sku_download_id<?php echo $rowCt; ?>" id="sku_download_id-<?php echo $skusQuery["sku_id"][$i]; ?>" type="hidden" value="<?php echo $skusQuery["sku_download_id"][$i]; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="label">
                                                        Download Limit (0 = no limit):
                                                    </th>
                                                    <td class="noHover">
                                                        <input name="sku_download_limit<?php echo $rowCt; ?>" type="text" size="10" value="<?php echo $skusQuery["sku_download_limit"][$i]; ?>" maxlength="7" onkeyup="extractNumeric(this,0,true)" onblur="checkValue(this);">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="label">
                                                        File Version (optional):
                                                    </th>
                                                    <td class="noHover">
                                                        <input name="sku_file_version<?php echo $rowCt; ?>" type="text" size="10" value="<?php echo $skusQuery["sku_download_version"][$i]; ?>">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
<?php
				}
				// /end if file upload 
				if ($HasOrders == 1) {
?>
					<span class="smallprint">
					<br>
					&nbsp;&nbsp;Note: orders placed, cannot change options
					<br>
					</span>
<?php
				}
?>
				</td>
			    </tr>
<?php
			}
			// end if we have some options 
		}
?>

		</tbody>
	    </table>
<?php
		// show message explaining disabled checkboxes 
		if ($disableCt) {
?>	
		    <span style="float: right;" class="smallPrint">Note:&nbsp;&nbsp;skus with associated orders cannot be deleted</span>
<?php
		}
		// the tab to return to when this form is submitted: changed dynamically when clicking on various tabs 
?>
	    <input name="returnTab" class="returnTab" type="hidden" value="1">
	</form>
<?php
		// / END LIST VIEW 
	} else { //if ($_ENV["request.cwpage"]['adminSkuEditMode'] == 'list') 
		// STANDARD VIEW 
		if ($_ENV["application.cw"]["adminSkuEditModeLink"] || in_array($_SESSION["cw"]["accessLevel"], array('developer'))) {
?>
	    <a href="<?php echo $_ENV["request.cwpage"]['skuURL']; ?>&skumode=list" class="CWbuttonLink SKUviewLink">List View</a>   
<?php
		}
?>
	<div class="clear"></div>
	<table class="CWformTable wide">
	    <tr class="headerRow">
		<th>
		    Current SKUs for Product '<?php echo $_ENV["request.cwpage"]["productName"]; ?>'
		</th>
	    </tr>
<?php
		// loop skus query 
		for ($i = 0; $i < $skusQuery['totalRows']; $i++) {
			// QUERY: get SKU options 
			$skuOptionsQuery = CWquerySelectSkuOptions($skusQuery['sku_id'][$i]);
			// set up a list of sku options 	    
			$listSKUOptions = '';
			if( $skuOptionsQuery['totalRows'] )
				$listSKUOptions = implode(',', $skuOptionsQuery['sku_option2option_id']);
			// QUERY: check for orders on this SKU 
			$ordersQuery = CWqueryCountSKUOrders($skusQuery['sku_id'][$i]);
			if ($ordersQuery > 0 ) {
				$HasOrders = 1;
				$DisabledText = '" disabled=""disabled"""';
			} else {
				$HasOrders = 0;
				$DisabledText = '';
			}
?>
	        <!-- SKU Name  -->
	        <tr>
	    	<td>
	    	    <table class="CWskuTable wide">
	    		<tr class="headerRow">
	    		    <th>
	    			SKU: <a name="<?php echo $skusQuery['sku_id'][$i]; ?>"><?php echo $skusQuery['sku_merchant_sku_id'][$i]; ?></a>
	    		    </th>
	    		</tr>
	    		<!-- SKU FORM -->
	    		<tr>
	    		    <td>
	    			<form method="post" name="updateSKU" class="updateSKU" action="<?php echo $_ENV["request.cwpage"]['skuURL']; ?>&skumode=<?php echo $_GET['skumode']; ?>">
<?php
			// handle error if sku cannot be deleted 
			if (isset($_POST['CantDeleteSKU'])) {
				if ($_GET['sku_id'] == $skusQuery['sku_id'][$i]) {
?>
					<div class="alert"><?php echo $_POST['CantDeleteSKU']; ?></div>
<?php
				}
			}
?>
	    			    <!-- SKU FORM Container -->
	    			    <table class="CWformTable">
	    				<!-- form headers -->
	    				<tr class="headerRow">
	    				    <th>On Web</th>
	    				    <th>Price</th>
<?php
			if ($_ENV["application.cw"]["adminProductAltPriceEnabled"]) {
?>
						    <th valign="top">
							    <?php echo $_ENV["application.cw"]["adminLabelProductAltPrice"]; ?>
						    </th>
<?php
			}
?>
	    				    <th>Sort</th>
	    				    <th>Weight</th>
	    				    <th>Ship Cost</th>
	    				    <th>Stock</th>
	    				</tr>
	    				<!-- form inputs -->
	    				<tr>
	    				    <!-- on web -->
	    				    <td>
	    					<select name="sku_on_web" id="sku_on_web">
<?php
			if ($skusQuery['sku_on_web'][$i] != 0) {
?>
							    <option value="1" selected="selected">
								Yes
							    <option value="0">
								No
<?php
			} else {
?>
							    <option value="1" >
								Yes
							    <option value="0" selected="selected">
								No
<?php
			}
?>
	    					</select>
	    				    </td>
	    				    <!-- price -->
	    				    <td>
	    			            <input name="sku_price" type="text" value="<?php echo number_format($skusQuery['sku_price'][$i], 2, '.', ''); ?>" size="6"maxlength="12" onblur="checkValue(this)" onKeyUp="extractNumeric(this,2,false)">
	    				    </td>
	    				    <!-- alt price -->
<?php
			if ($_ENV["application.cw"]["adminProductAltPriceEnabled"]) {
?>
						    <td>
							<input name="sku_alt_price" type="text" value="<?php echo number_format($skusQuery['sku_alt_price'][$i], 2, '.', ''); ?>" size="6" maxlength="12" onblur="checkValue(this)" onKeyUp="extractNumeric(this,2,false)">
						    </td>
<?php
			}
?>
	    				    <!-- sort -->
	    				    <td>
	    					<input name="sku_sort" class="sort" type="text" value="<?php echo $skusQuery['sku_sort'][$i]; ?>" size="5" maxlength="7" onblur="checkValue(this)" onkeyup="extractNumeric(this,2,true)">
	    				    </td>
	    				    <!-- weight-->
	    				    <td>
	    					<input name="sku_weight" type="text" value="<?php echo $skusQuery['sku_weight'][$i]; ?>" size="5" maxlength="7" onblur="checkValue(this)" onkeyup="extractNumeric(this,2,false)">
	    				    </td>
	    				    <!-- ship base -->
	    				    <td>
	    					<input name="sku_ship_base" type="text" value="<?php echo number_format($skusQuery['sku_ship_base'][$i], 2, '.', ''); ?>" size="6"maxlength="7"onblur="checkValue(this)" onKeyUp="extractNumeric(this,2,false)">
	    				    </td>
	    				    <!-- stock-->
	    				    <td>
	    					<input name="sku_stock" type="text" value="<?php echo $skusQuery['sku_stock'][$i]; ?>" size="5" maxlength="7" onblur="checkValue(this)" onkeyup="extractNumeric(this,0,true)">
	    				    </td>
	    				</tr>

	    				<!-- end inputs -->
	    			    </table>
	    			    <!-- end sku form container -->
	    			    <!-- SKU OPTIONS -->
<?php
			// if we have some product options to show 
			if (isset($productOptionsRelQuery['totalRows']) && $productOptionsRelQuery['totalRows']) {
?>
					<!-- sku options container -->
					<table class="CWformTable">
					    <tr class="headerRow">
						<th colspan="2">SKU Options</th> 
					    </tr>
<?php
				// show options, 1 per row 
				if (!$HasOrders) {
					$previousOptionTypeName = "";
					for ($j = 0; $j < $productOptionsRelQuery['totalRows']; $j++) {
						$previousOptionTypeName = $productOptionsRelQuery['optiontype_name'][$j];
?>
							<tr>
							    <th class="label"><?php echo $productOptionsRelQuery['optiontype_name'][$j]; ?>:</th>
							    <td>
								<select name="selOption<?php echo $j;?>" <?php echo $DisabledText; ?> >
<?php
						while ($j < $productOptionsRelQuery['totalRows'] && $previousOptionTypeName == $productOptionsRelQuery['optiontype_name'][$j]) {
							if ( ListFind($listSKUOptions, $productOptionsRelQuery['option_id'][$j], ",") ) {
?>				
									<option value="<?php echo $productOptionsRelQuery['option_id'][$j]; ?>" selected="selected"><?php echo $productOptionsRelQuery['option_name'][$j]; ?></option>
<?php
							} else {
?>	
									<option value="<?php echo $productOptionsRelQuery['option_id'][$j]; ?>"><?php echo $productOptionsRelQuery['option_name'][$j]; ?></option>
<?php
							}
							$j++;
						}
						$j--;
?>
                                </select>
                                </td>
                            </tr>
<?php
					}
				} else {
					$previousOptionTypeName = "";
					for ($j = 0; $j < $productOptionsRelQuery['totalRows']; $j++) {
						$previousOptionTypeName = $productOptionsRelQuery['optiontype_name'][$j];
?>
							<tr>
                                <th class="label"><?php echo $productOptionsRelQuery['optiontype_name'][$j]; ?>:</th>
                                <td>
<?php
						while ($j < $productOptionsRelQuery['totalRows'] && $previousOptionTypeName == $productOptionsRelQuery['optiontype_name'][$j]) {
							if ( ListFind($listSKUOptions, $productOptionsRelQuery['option_id'][$j], ",") ) {
								echo $productOptionsRelQuery['option_name'][$j];
?>
									<input type="hidden" value="<?php echo $productOptionsRelQuery['option_id'][$j]; ?>" name="selOption<?php echo $j; ?>" id="selOption<?php echo $j; ?>">
<?php
							}
							$j++;
						}
						$j--;
?>
                                </td>
                            </tr>	
<?php
					}
				}
?>
					</table>
					<!-- end product options -->
<?php
			}
			// file upload
			if ($_ENV["request.cwpage"]["ddok"]) {
				if (!isset($skusQuery["sku_download_file"][$i])) $skusQuery["sku_download_file"][$i] = "";
				if (!isset($skusQuery["sku_download_id"][$i])) $skusQuery["sku_download_id"][$i] = "";
				if (!isset($skusQuery["sku_download_limit"][$i])) $skusQuery["sku_download_limit"][$i] = "";
				if (!isset($skusQuery["sku_download_version"][$i])) $skusQuery["sku_download_version"][$i] = "";
				// build the link for the file uploader selector 
				$fileUploadUrl="product-file-upload.php?uploadSku=".$skusQuery["sku_id"][$i];
				$fileSelectUrl="product-file-select.php?uploadSku=".$skusQuery["sku_id"][$i];
				// set up file preview link if file exists
?>
                                        <table class="CWformTable wide">
                                            <tbody>
                                                <tr class="headerRow">
                                                    <th colspan="2">File Management</th>
                                                </tr>
                                                <tr>
                                                    <th class="label">
                                                        SKU Download File:
<?php
				// file preview link 
				if (strlen(trim(CWcreateDownloadURL($skusQuery["sku_id"][$i]))) && strlen(trim($skusQuery["sku_download_file"][$i]))) {
					// if the file does not exist, the createdownloadurl function will return '' 
?>
                                            <div class="smallPrint">
                                                <a href="<?php echo CWcreateDownloadURL($skusQuery["sku_id"][$i],'product-file-preview.php'); ?>" rel="external">Download File</a>
                                            </div>
<?php
				}
?>
                                                    </th>
                                                    <?php // file upload filed / content area ?>
                                                    <td class="noHover">
                                                        <input name="sku_download_file" id="sku_download_file-<?php echo $skusQuery["sku_id"][$i]; ?>" type="text" value="<?php echo $skusQuery["sku_download_file"][$i]; ?>" class="fileInput" size="40">
                                                        <?php // links w/ file upload field ?>
                                                        <span class="fieldLinks">
                                                        <?php // ICON: upload file ?>
                                                        <a href="<?php echo $fileUploadUrl; ?>" title="Upload file" class="showFileUploader"><img src="img/cw-image-upload.png" alt="Upload file" class="iconLink"></a>
                                                        <?php // ICON: clear filename field ?>
                                                        <img src="img/cw-delete.png" title="Clear file field" class="iconLink clearFileLink">
                                                        </span>
<?php
				// if a file name is recorded, but file is not available, show error warning
				if (strlen(trim($skusQuery["sku_download_file"][$i])) && !strlen(trim(CWcreateDownloadURL($skusQuery["sku_id"][$i])))) {
?>
                                                        <div class="alert">File not available</div>
<?php
				}
				// file content area
?>
                                                        <div class="productFileContent">
                                                            <?php // file uploader ?>
                                                            <div class="fileUpload" style="display:none;">
                                                                <iframe width="460" height="94" frameborder="no" scrolling="false">
                                                                </iframe>
                                                            </div>
                                                        </div>
                                                    <?php // hidden input for download id ?>
                                                    <input name="sku_download_id" id="sku_download_id-<?php echo $skusQuery["sku_id"][$i]; ?>" type="hidden" value="<?php echo $skusQuery["sku_download_id"][$i]; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="label">
                                                        Download Limit (0 = no limit):
                                                    </th>
                                                    <td class="noHover">
                                                        <input name="sku_download_limit" type="text" size="10" value="<?php echo $skusQuery["sku_download_limit"][$i]; ?>" maxlength="7" onkeyup="extractNumeric(this,0,true)" onblur="checkValue(this);">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="label">
                                                        File Version (optional):
                                                    </th>
                                                    <td class="noHover">
                                                        <input name="sku_file_version" type="text" size="10" value="<?php echo $skusQuery["sku_download_version"][$i]; ?>">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
<?php
			}
			// /end if file upload 
			if ($HasOrders == 1) {
?>	   
						<span class="smallprint">
						    <br>
						    &nbsp;&nbsp;Note: orders placed, cannot delete
						    <br>
						</span>
<?php
			}
			// Sku Form Buttons 
?>
						<div class="CWformButtonWrap">
<?php
			if ($HasOrders==0) {
				// delete sku 
?>
		    <a class="CWbuttonLink" href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?deletesku=<?php echo $skusQuery['sku_id'][$i]; ?>&productid=<?php echo $detailsQuery['product_id'][0]; ?>&showtab=4" onClick="return confirm('Delete this SKU? (This is permanent)')">Delete SKU</a>
<?php
			}
			// copy sku 
			if ($listProductOptions != '' || ($listProductOptions == '' && $skusQuery['totalRows'] < 1)) {
?>		
		    <a class="skuDupLink CWbuttonLink" href="#">Copy Sku</a>
<?php
			}
			// save button 
?>
		<input name="updateSKU" type="submit" class="submitButton updateSKU" value="Save SKU">
	    </div>
<?php
			if ($listProductOptions != '' || ($listProductOptions == '' && isset($skusQuery['totalRows']) && $skusQuery['totalRows'] < 1)) {
?>
		<div id="skuDup" style="display:none;">
		    <label>New SKU Name</label>
		    <input type="text" size="25" name="sku_merchant_sku_id" value="">
		    <input name="AddSKU" type="submit" class="CWformButton" value="Duplicate SKU">
		</div>
<?php
			}
?>
        <p>&nbsp;</p>
        <input name="sku_product_id" type="hidden" value="<?php echo $skusQuery['sku_product_id'][$i]; ?>">
        <input name="sku_id" type="hidden" value="<?php echo $skusQuery['sku_id'][$i]; ?>">
        <input name="sku_editmode" type="hidden" value="<?php echo $_ENV["request.cwpage"]['adminSkuEditMode']; ?>">
        <?php // the tab to return to when this form is submitted: changed dynamically when clicking on various tabs   ?>
        <input name="returnTab" class="returnTab" type="hidden" value="1">
    </form>
	    		    </td>
	    		</tr>
		    </table>
	    	</td>
	        </tr>
<?php
		}
?>
	<!-- /end list vs. standard view -->
	    </table>
<?php
	}
}
// /end if we have SKUs 
?>
<!-- force the outer container open -->
<div class="clear"></div>