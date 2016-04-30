<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: admin/cwadminapp/inc/cw-inc-admin-discount-products.php
File Date: 2012-03-21
Description:
fucntions for admin side of site
==========================================================
*/
global $discountQuery, $discountProductsQuery, $listC, $listSC;
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
$_ENV["request.cw"]["resetURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]) . '&showtab=3';
// set up list of products to omit from list (already related) 
$omittedProducts = "";
if (isset($discountProductsQuery["product_id"]) && sizeof($discountProductsQuery["product_id"])) {
	$omittedProducts = trim(implode(",",$discountProductsQuery["product_id"]));
}
// QUERY: get all potential discount products (cat id, subcat id, keywords, fields to match, products to skip) 
$productsQuery = CWqueryDiscountProductSelections(
					$_GET["discountc"],
					$_GET["discountsc"],
					$_GET["find"],
					$_GET["searchby"],
					$omittedProducts,
					null,
					true
					);
$varsToKeep = CWremoveUrlVars("sortby,sortdir,showtab,deldiscountid,sumitdiscountfilter,discountdelete,userconfirm,useralert,discountc,discountsc,showimg");
$_ENV["request.cw"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]) . '&showtab=3';
?>
<!-- discount container -->
<table class="noBorder">
	<?php // display associated items if there are any ?>
	<tr>
		<td>
			<form action="<?php echo $_ENV["request.cw"]["baseURL"]; ?>" name="frmDeleteDiscountProd" method="post">
				<h3>Associated Products</h3>
<?php
if ($discountProductsQuery["totalRows"]) {
?>
					<!-- existing discounts table -->
					<table class="CWsort CWstripe CWformTable" summary="<?php echo $_ENV["request.cw"]["baseURL"]; ?>">
						<thead>
						<tr class="headerRow sortRow">
<?php
	if ($_ENV["application.cw"]["adminDiscountThumbsEnabled"] != 0) {
?>
								<th class="noSort">Image</th>
<?php
	}
?>
							<th class="product_name">Associated Product Name</th>
							<th class="product_merchant_product_id">Associated Product Id</th>
							<th class="noSort" style="text-align:center;"><input type="checkbox" name="all0" class="checkAll" rel="all0" id="relProdAll0">Delete</th>
						</tr>
						</thead>
						<tbody>
<?php
	for ($n=0; $n<$discountProductsQuery["totalRows"]; $n++) {
?>
						<tr>
<?php
		if ($_ENV["application.cw"]["adminDiscountThumbsEnabled"] != 0) {
			$imageFnArr = explode("/",CWgetImage($discountProductsQuery["product_id"][$n],4));
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
										<a href="product-details.php?productid=<?php echo $discountProductsQuery["product_id"][$n]; ?>" title="View product details"><img src="<?php echo $imageSrc; ?>" alt="View product details"></a>
<?php
			}
?>
								</td>
<?php
		}
?>
							<td><strong><a class="detailsLink" href="product-details.php?productid=<?php echo $discountProductsQuery["product_id"][$n]; ?>" title="View product details"><?php echo $discountProductsQuery["product_name"][$n]; ?></a></strong></td>
							<td> <?php echo $discountProductsQuery["product_merchant_product_id"][$n]; ?> </td>
							<td style="text-align: center;">
								<input type="checkbox" name="deleteproduct_id[<?php echo $n; ?>]" value="<?php echo $discountProductsQuery["product_id"][$n]; ?>" class="all0 formCheckbox">
							</td>
						</tr>
<?php
	}
	// delete products ?>
						<tr>
							<td colspan="4">
								<input id="DelDiscProduct" name="DeleteChecked" type="submit" class="deleteButton" value="Delete Selected">
								<input name="discount_id<?php echo $n; ?>" type="hidden" id="discount_id" value="<?php echo $discountQuery["discount_id"][0]; ?>">
							</td>
						</tr>
						</tbody>
					</table>
<?php
} else {
	// if no products ?>
					<p>&nbsp;</p>
					<p class="formText"><strong>Use the options below to add related products</strong></p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
<?php
}
?>
			</form>
			<!-- /end existing products table -->
		</td>
	</tr>
	<?php // /END existing products ?>

	<tr>
		<td>
			<h3>Add Associated Products</h3>
			<table class="wide">
				<tr class="headerRow">
					<th>
						Available Associated Products (discount will be applied to all SKUs for selected items)
					</th>
				</tr>
				<tr>
					<td>
							<form name="filterDiscount" id="CWadminDiscountSearch" method="GET" action="<?php echo $_ENV["request.cw"]["baseURL"]; ?>">
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
								<span class="pushRight"><a href="<?php echo $_ENV["request.cw"]["resetURL"]; ?>&submitdiscountfilter=1#addNew" onclick="return confirm('Show All Products?')">Show All</a></span>
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
									<th style="width:75px;"  class="noSort checkHeader"><input type="checkbox" name="all1" class="checkAll" rel="all1" id="relProdAll1">Add</th>
									<th style="width:50px; text-align:center;" class="noSort">View</th>
								</tr>
								</thead>
<?php
// only show if search has been submitted (avoid showing all products on every page load) 
if (isset($_GET['submitdiscountfilter'])) {
?>
								<tbody>
<?php
	if ($productsQuery["totalRows"]) {
		for ($n=0; $n<$productsQuery["totalRows"]; $n++) {
?>
								<tr>
<?php
			// image cell 
			if ($_GET["showimg"]) {
				$imageFnArr = explode("/",CWgetImage($productsQuery["product_id"][$n],4));
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
												<a href="product-details.php?productid=<?php echo $productsQuery["product_id"][$n]; ?>" title="View product details"><img src="<?php echo $imageSrc; ?>" alt="View product details"></a>
<?php
				}
?>
										</td>
<?php
			}
			// name ?>
									<td title="Select related product"><?php echo $productsQuery["product_name"][$n]; ?></td>
									<?php // merchant id ?>
									<td title="Select related product"><?php echo $productsQuery["product_merchant_product_id"][$n]; ?></td>
									<?php // select box ?>
									<td title="Select related product" style="text-align:center" class="firstCheck">
										<input type="checkbox" name="discount_product_id[<?php echo $n; ?>]" value="<?php echo $productsQuery["product_id"][$n]; ?>" class="all1">
									</td>
									<?php // view product link ?>
									<td style="width:50px;text-align:center">
										<a href="<?php echo $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["request.cwpage"]["urlDetails"]; ?>?product=<?php echo $productsQuery["product_id"][$n]; ?>" title="View on Web: <?php echo CWstringFormat($productsQuery["product_name"][$n]); ?>" rel="external" class="columnLink"><img alt="View on Web: <?php echo $productsQuery["product_name"][$n]; ?>" src="img/cw-product-view.png"></a>
									</td>
								</tr>
<?php
		}
	}
?>
								</tbody>
<?php
}
?>
							</table>
<?php
if (!$productsQuery["totalRows"]) {
?>
								<p>&nbsp;</p>
								<p>No available products found.</p>
								<p>&nbsp;</p>
<?php
} else {
	if (isset($_GET["submitdiscountfilter"])) {
?>
									<div style="clear:both">
										<input name="AddDiscProd" type="submit" class="CWformButton" id="AddDiscProd" value="Add Selected Products">
									</div>
									<input name="discount_id" type="hidden" value="<?php echo $_GET["discount_id"]; ?>">
									<?php // This input field is not necessary for PHP <input name="discount_product_id" type="hidden" value=""> ?>
									<input type="hidden" name="showtab" value="3">
<?php
	} else {
?>
									<p>&nbsp;</p>
									<p>Use the search controls to find and select products</p>
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
</table>
<!-- /end discount container -->