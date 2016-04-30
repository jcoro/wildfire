<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: admin/cwadminapp/inc/cw-inc-admin-discount-skus.php
File Date: 2012-02-01
Description: Handles skus management for discount-details.php
Note: requires related queries, processing and form javascript in discount-details.php
==========================================================
*/
global $discountSkusQuery, $listC, $listSC;
if (!isset($_GET["discountc"])) $_GET["discountc"] = 0;
if (!isset($_GET["discountsc"])) $_GET["discountsc"] = 0;
if (!isset($_GET["discountfind"])) $_GET["discountfind"] = "";
if (!isset($_GET["find"])) $_GET["find"] = "";
if (!isset($_GET["searchby"])) $_GET["searchby"] = "any";
if (!isset($_GET["sortby"])) $_GET["sortby"] = "product_name";
if (!isset($_GET["showimg"])) $_GET["showimg"] = false;
if ($_GET["showimg"] && $_GET["showimg"] == "true") $_GET["showimg"] = true;
else $_GET["showimg"] = false;
if (!isset($_ENV["application.cw"]["adminDiscountThumbsEnabled"])) $_ENV["application.cw"]["adminDiscountThumbsEnabled"] = 0;
$varsToKeep = CWremoveUrlVars("discountc,discountsc,showtab,useralert,userconfirm,searchby,find");
$_POST["resetURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]) . '&showtab=3';
// set up list of skus to omit from list (already related) 
$omittedSkus = "";
if (isset($discountSkusQuery["sku_id"]) && sizeof($discountSkusQuery["sku_id"])) {
	$omittedSkus = trim(implode(",",$discountSkusQuery["sku_id"]));
}
// QUERY: get all potential discount products (cat id, subcat id, keywords, fields to match, products to skip) 
$skusQuery = CWqueryDiscountSkuSelections(
				$_GET["discountc"],
				$_GET["discountsc"],
				$_GET["find"],
				$_GET["searchby"],
				$omittedSkus,
				null,
				true
				);
$varsToKeep = CWremoveUrlVars("sortby,sortdir,showtab,deldiscountid,sumitdiscountfilter,discountdelete,userconfirm,useralert,discountc,discountsc,showimg");
$_ENV["request.cw"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]) . '&showtab=3';
// javascript for show/hide sku details 
$discountSkujs = "
<script type=\"text/javascript\">
jQuery(document).ready(function(){
	jQuery('td.detailsShow').click(function(){
		var thisRel = jQuery(this).children('a.showSkusLink').attr('rel');
		jQuery(this).parents('tr.CWskuHeader').hide();
		jQuery(this).parents('tr').siblings('tr.CWskuDetails[rel=' + thisRel + ']').show();
		return false;
	});
	jQuery('td.detailsHide').click(function(){
		var thisRel = jQuery(this).children('a.hideSkusLink').attr('rel');
		jQuery(this).parents('tr.CWskuDetails').hide().prev('tr.CWskuHeader').show();
		jQuery(this).parents('tr').siblings('tr.CWskuDetails[rel=' + thisRel + ']').hide();
		return false;
	});
		jQuery('a.showAll').click(function(){
		jQuery(this).hide();
		jQuery(this).siblings('a.hideAll').show();
		jQuery('tr.CWskuHeader').hide();
		jQuery('tr.CWskuDetails').show();
	});

	jQuery('a.hideAll').click(function(){
		jQuery(this).hide();
		jQuery(this).siblings('a.showAll').show();
		jQuery('tr.CWskuDetails').hide();
		jQuery('tr.CWskuHeader').show();
	});
	jQuery('a.delAll').click(function(){
		jQuery('tr.CWskuHeader').hide();
		jQuery('tr.CWskuDetails').show();
		jQuery('a.hideAll').show();
		jQuery('a.showAll').hide();
		jQuery('#tblDiscSkus input[type=checkbox]').prop('checked',true);
	});
});
</script>
<style type=\"text/css\">
	a.showSkusLink, a.hideSkusLink{
	margin-left:11px;
	text-decoration:none !important;
	}
	td.detailsShow{
	padding:6px 7px !important;
	}
	a.delAll{
	float:right;
	margin-right:18px;
	}
	a.showAll, a.hideAll, a.delAll{
	text-decoration:none !important;
	}
</style>
";
CWinsertHead($discountSkujs);
?>
<!-- discount container -->
<table class="noBorder">
	<?php // display associated items if there are any ?>
	<tr>
		<td>
			<form action="<?php echo $_ENV["request.cw"]["baseURL"]; ?>" name="frmDeleteDiscountProd" method="post">
				<h3>Associated SKUs by Product <span class="smallPrint">
				<a href="#" class="showAll">Show All</a>
				<a href="#" class="hideAll" style="display:none;">Hide All</a>
				<a href="#" class="delAll">Delete All</a></span> </h3>
<?php
if ($discountSkusQuery["totalRows"]) {
?>
					<table class="CWsort CWformTable" id="tblDiscSkus" summary="<?php echo $_ENV["request.cw"]["baseURL"]; ?>">
						<thead>
						<tr class="headerRow sortRow">
<?php
	if ($_ENV["application.cw"]["adminDiscountThumbsEnabled"] != 0) {
?>
								<th class="noSort">Image</th>
<?php
	}
?>
							<th class="sku_name"<?php if ($_ENV["application.cw"]["adminDiscountThumbsEnabled"] == 0) { ?> colspan="2"<?php } ?>>Associated Product Name</th>
							<th class="product_merchant_product_id" style="width:215px;">Associated Product Id</th>
						</tr>
						</thead>
						<tbody>
<?php
	// group by product 
	$lastProdID = -1;
	for ($n=0; $n<$discountSkusQuery["totalRows"]; $n++) {
		if ($lastProdID != $discountSkusQuery["product_id"][$n]) {
			if ($lastProdID != -1) {
				echo "</table>
					</td>
				</tr>";
			}
			$lastProdID = $discountSkusQuery["product_id"][$n];
			// manual coloring by adding odd or even classes here ?>
						<tr class="CWrowOdd CWproductRow">
<?php
			if ($_ENV["application.cw"]["adminDiscountThumbsEnabled"] != 0) {
				$imageFnArr = explode("/",CWgetImage($discountSkusQuery["product_id"][$n],4));
				$imageFn = $imageFnArr[sizeof($imageFnArr)-1];
				if (strlen(trim($imageFn))) {
					$imageSrc = $_ENV["request.cwpage"]["adminImgPrefix"].$_ENV["application.cw"]["appImagesDir"]."/admin_preview/".$imageFn;
				} else {
					$imageSrc = "";
				}
?>
								<td class="imageCell" style="text-align:center;">
<?php
				if (strlen(trim($imageSrc)) && file_exists(expandPath($imageSrc))) {
?>
										<a href="product-details.php?productid=<?php echo $discountSkusQuery["product_id"][$n]; ?>" title="View product details"><img src="<?php echo $imageSrc; ?>" alt="View product details"></a>
<?php
				}
?>
								</td>
<?php
			}
?>
							<td<?php if ($_ENV["application.cw"]["adminDiscountThumbsEnabled"] == 0) { ?> colspan="2"<?php } ?>><strong><a class="detailsLink" href="product-details.php?productid=<?php echo $discountSkusQuery["product_id"][$n]; ?>" title="View product details"><?php echo $discountSkusQuery["product_name"][$n]; ?></a></strong></td>
							<td> <?php echo $discountSkusQuery["product_merchant_product_id"][$n]; ?> </td>
						</tr>
						<?php // skus header ?>
						<tr class="CWskuHeader">
						<td colspan="3" class="detailsShow">
							<?php // show/hide function usees 'rel' attribute of this link ?>
							<a href="#" class="showSkusLink" rel="prod<?php echo $discountSkusQuery["product_id"][$n]; ?>">&raquo;&nbsp;Show Skus</a>
						</td>
						</tr>
						<?php // sku details ?>
							<tr class="CWskuDetails" rel="prod<?php echo $discountSkusQuery["product_id"][$n]; ?>" style="display:none;">
								<td class="detailsHide">
									<?php // show/hide function usees 'rel' attribute of this link ?>
									<a href="#" style="font-weight:100;" class="hideSkusLink" rel="prod<?php echo $discountSkusQuery["product_id"][$n]; ?>">&laquo;&nbsp;Hide Skus</a>
								</td>
								<td colspan="2">
									<table style="width:100%;" class="CWformTable">
										<tr>
											<th style="text-align: center;width:230px;">
											SKU ID
											</th>
											<th style="text-align: center;">
											SKU Options
											</th>
											<th style="text-align: center; width:100px;">
											<input type="checkbox" name="deleteskuproduct_id" value="<?php echo $discountSkusQuery["product_id"][$n]; ?>" class="checkAll formCheckbox" rel="all<?php echo $discountSkusQuery["product_id"][$n]; ?>">
											Delete
											</th>
										</tr>
<?php
		}
		// ungroup the grouped sku output 
		// QUERY: get SKU options 
		$skuOptionsQuery = CWquerySelectSkuOptions($discountSkusQuery["sku_id"][$n]);
?>
										<tr>
											<td>
												<?php echo $discountSkusQuery["sku_merchant_sku_id"][$n]; ?>
											</td>
											<td>
<?php
		if ($skuOptionsQuery["totalRows"]) {
?>
												<p>
<?php
			for ($s=0; $s<$skuOptionsQuery["totalRows"]; $s++) {
				echo $skuOptionsQuery["optiontype_name"][$s].": ".$skuOptionsQuery["option_name"][$s]."<br>";
			}
?>
												</p>
<?php
		} else {
?>
											<p>No Options</p>
<?php
		}
?>
											</td>
											<td style="text-align: center;">
												<?php // delete checkbox ?>
												<input type="checkbox" name="deletesku_id[<?php echo $n; ?>]" value="<?php echo $discountSkusQuery["sku_id"][$n]; ?>" class="all<?php echo $discountSkusQuery["product_id"][$n]; ?> all0 formCheckbox">
											</td>
										</tr>
<?php
	}
?>
									</table>
								</td>
							</tr>
<?php
	// /end sku details 
	// delete products ?>
						<tr>
							<td colspan="4">
								<input id="DelDiscProduct" name="DeleteChecked" type="submit" class="deleteButton" value="Delete Selected">
								<input name="discount_id" type="hidden" id="discount_id" value="<?php echo $discountQuery["discount_id"][0]; ?>">
							</td>
						</tr>
						</tbody>
					</table>
<?php
	// if no products 
} else {
?>
					<p>&nbsp;</p>
					<p class="formText"><strong>Use the options below to add related skus</strong></p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
<?php
}
// anchor link for lower section ?>
				<a name="addNew"></a>
			</form>
		</td>
	</tr>
<?php
// /END existing products
// ADD NEW PRODUCTS ?>
	<tr>
		<td>
			<h3>Add Associated SKUs</h3>
			<table class="wide">
				<tr class="headerRow">
					<th>
						Available Associated Products (search by product or sku name)
					</th>
				</tr>
				<tr>
					<td>
							<?php // product/sku search form - anchor link on action returns to lower page area ?>
							<form name="filterDiscount" id="CWadminDiscountSearch" method="GET" action="<?php echo $_ENV["request.cw"]["baseURL"]; ?>#addNew">
							<div class="CWadminControlWrap">
								<span class="advanced pushRight"><strong>Find Products:</strong></span>
								<?php // keyword search ?>
								<label for="find">&nbsp;Keyword:</label>
								<input name="find" type="text" size="15" id="Find" value="<?php echo $_GET["find"]; ?>">
								<?php // search fields ?>
								<label for="searchBy">&nbsp;Search In:</label>
								<select name="searchBy" id="searchBy">
									<option value="any"<?php if ($_GET["searchby"] == "any") { ?> selected="selected"<?php } ?>>All Fields</option>
									<option value="prodID"<?php if ($_GET["searchby"] == "prodID") { ?> selected="selected"<?php } ?>>Product ID</option>
									<option value="prodName"<?php if ($_GET["searchby"] == "prodName") { ?> selected="selected"<?php } ?>>Product Name</option>
									<option value="descrip"<?php if ($_GET["searchby"] == "descrip") { ?> selected="selected"<?php } ?>>Description</option>
								</select>
								<?php // hidden fields ?>
								<input type="hidden" name="showtab" value="3">
								<input name="discount_id" type="hidden" value="<?php echo $_GET["discount_id"]; ?>">
<?php
// show images 
if ($_ENV["application.cw"]["adminDiscountThumbsEnabled"] != 0) {
?>
								<label for="showimg">&nbsp;&nbsp;Images:<input name="showimg" type="checkbox"<?php if ($_GET["showimg"]) { ?> checked="checked"<?php } ?> value="true"></label>
<?php
}
// submit button ?>
								<input type="submit" name="submitdiscountfilter" id="submitdiscountfilter" class="CWformButton" value="Go">
								<?php // categories ?>
								<br>
								<?php // show all / reset search ?>
								<span class="pushRight"><a href="<?php echo $_POST["resetURL"]; ?>&submitdiscountfilter=1#addNew" onclick="return confirm('Show All Products?')">Show All</a></span>
									<label for="discountc">&nbsp;<?php echo $_ENV["application.cw"]["adminLabelCategory"]; ?>:&nbsp;</label>
									<select name="discountc" id="discountc">
										<option value="0">All</option>
<?php
for ($n=0; $n<$listC["totalRows"]; $n++) {
?>
										<option value="<?php echo $listC["category_id"][$n]; ?>"<?php if ($_GET["discountc"] == $listC["category_id"][$n]) { ?> selected="selected"<?php } ?>><?php echo substr($listC["category_name"][$n], 0, 20); ?></option>
<?php
}
?>
									</select>
									<?php // subcategories ?>
									<label for="discountsc">&nbsp;<?php echo $_ENV["application.cw"]["adminLabelSecondary"]; ?>:&nbsp;</label>
									<select name="discountsc" id="discountsc">
										<option value="0">All</option>
<?php
for ($n=0; $n<$listSC["totalRows"]; $n++) {
?>
										<option value="<?php echo $listSC["secondary_id"][$n]; ?>"<?php if ($_GET["discountsc"] == $listSC["secondary_id"][$n]) { ?> selected="selected"<?php } ?>><?php echo substr($listSC["secondary_name"][$n], 0, 20); ?></option>
<?php
}
?>
									</select>
							</div>
							</form>
							<?php
							// /end product search form 
						// add product form ?>
						<form name="frmAddProduct" method="POST" action="<?php echo $_ENV["request.cw"]["baseURL"]; ?>">
							<!-- the select discount table-->
							<table class="CWsort CWstripe CWformTable" id="tblDiscProdSelect" summary="<?php echo $_ENV["request.cw"]["baseURL"]; ?>">
								<thead>
								<tr class="headerRow sortRow">
<?php
if ($_GET["showimg"]) {
?>
										<th class="img nosort">Image</th>
<?php
}
?>
									<th class="product_name">Product Name</th>
									<th class="product_merchant_product_id">Product ID</th>
									<th style="width:50px; text-align:center;" class="noSort">View</th>
								</tr>
								</thead>
<?php
// only show if search has been submitted (avoid showing all products on every page load) 
if (isset($_GET['submitdiscountfilter'])) {
?>
								<tbody>
<?php
	if ($skusQuery["totalRows"]) {
		$lastSkuID = -1;
		for ($n=0; $n<$skusQuery["totalRows"]; $n++) {
			if ($lastSkuID != $skusQuery["product_id"][$n]) {
				if ($lastSkuID != -1) {
					echo "</table>
						</td>
					</tr>";
				}
				$lastSkuID = $skusQuery["product_id"][$n];
?>
								<tr>
<?php
				// image cell 
				if ($_GET["showimg"]) {
					$imageFnArr = explode("/",CWgetImage($skusQuery["product_id"][$n],4));
					$imageFn = $imageFnArr[sizeof($imageFnArr)-1];
					if (strlen(trim($imageFn))) {
						$imageSrc = $_ENV["request.cwpage"]["adminImgPrefix"].$_ENV["application.cw"]["appImagesDir"]."/admin_preview/".$imageFn;
					} else {
						$imageSrc = "";
					}
?>
										<td class="imageCell" style="text-align:center;">
<?php
					if (strlen(trim($imageSrc)) && file_exists(expandPath($imageSrc))) {
?>
												<a href="product-details.php?productid=<?php echo $skusQuery["product_id"][$n]; ?>" title="View product details"><img src="<?php echo $imageSrc; ?>" alt="View product details"></a>
<?php
					}
?>
										</td>
<?php
				}
				// name ?>
									<td title="Select related product"><?php echo $skusQuery["product_name"][$n]; ?></td>
									<?php // merchant id ?>
									<td title="Select related product"><?php echo $skusQuery["product_merchant_product_id"][$n]; ?></td>
									<?php // view product link ?>
									<td style="width:50px;text-align:center">
										<a href="<?php echo $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["request.cwpage"]["urlDetails"]; ?>?product=<?php echo $skusQuery["product_id"][$n]; ?>" title="View on Web: <?php echo CWstringFormat($skusQuery["product_name"][$n]); ?>" rel="external" class="columnLink"><img alt="View on Web: <?php echo CWstringFormat($skusQuery["product_name"][$n]); ?>" src="img/cw-product-view.png"></a>
									</td>
								</tr>

							<?php // sku details ?>
							<tr>
                            	<td></td>
								<td colspan="<?php if ($_GET["showimg"]) { ?>3<?php } else { ?>2<?php } ?>">
									<table style="width:98%;" class="CWformTable">
										<tr>
											<th style="text-align: center;width:230px;">
											SKU ID
											</th>
											<th style="text-align: center;">
											SKU Options
											</th>
											<th style="text-align: center; width:100px;">
											<input type="checkbox" class="checkAll formCheckbox" rel="addall<?php echo $skusQuery["product_id"][$n]; ?>">
											Add
											</th>
										</tr>
<?php
				// ungroup the grouped sku output 
			}
			// QUERY: get SKU options 
			$skuOptionsQuery = CWquerySelectSkuOptions($skusQuery["sku_id"][$n]);
?>
										<tr>
											<td>
												<?php echo $skusQuery["sku_merchant_sku_id"][$n]; ?>
											</td>
											<td>
<?php
			if ($skuOptionsQuery["totalRows"]) {
?>
											<p>
<?php
				for ($s=0; $s<$skuOptionsQuery["totalRows"]; $s++) {
					echo $skuOptionsQuery["optiontype_name"][$s].": ".$skuOptionsQuery["option_name"][$s]."<br>";
				}
?>
											</p>
<?php
			} else {
?>
										<p>No Options</p>
<?php
			}
?>
											</td>
											<td style="text-align: center;">
												<?php // add item checkbox ?>
												<input type="checkbox" name="discount_sku_id[<?php echo $n; ?>]" value="<?php echo $skusQuery["sku_id"][$n]; ?>" class="addall<?php echo $skusQuery["product_id"][$n]; ?> all0 formCheckbox">
											</td>
										</tr>
<?php
		}
?>
									</table>
								</td>
							</tr>
<?php
		// /end sku details 
	}
?>
								</tbody>
<?php
}
?>
							</table>
<?php
if (!$skusQuery["totalRows"]) {
?>
								<p>&nbsp;</p>
								<p>No available skus found.</p>
								<p>&nbsp;</p>
<?php
} else {
	if (isset($_GET['submitdiscountfilter'])) {
?>
									<div style="clear:both">
										<input name="AddDiscSku" type="submit" class="CWformButton" id="AddDiscProd" value="Add Selected Skus">
									</div>
									<input name="discount_id" type="hidden" value="<?php echo $_GET["discount_id"]; ?>">
									<?php // This input field is not necessary for PHP <input name="discount_sku_id" type="hidden" value=""> ?>
									<input type="hidden" name="showtab" value="3">
<?php
	} else {
?>
									<p>&nbsp;</p>
									<p>Use the search controls to find and select SKUs</p>
									<p>&nbsp;</p>
<?php
	}
}
?>
						</form>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php // /end add new products ?>
</table>
<!-- /end discount container -->