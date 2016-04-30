<?php 
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-related-products.php
File Date: 2012-02-01
Description: Handles Upsell management for product-details.php
Note: requires related queries, processing and form javascript in product-details.php
==========================================================
*/
if(!isset($_GET['upsellc'])) { $_GET['upsellc'] = "0"; }
if(!isset($_GET['upsellsc'])) { $_GET['upsellsc'] = "0"; }
if(!isset($_GET['upsellfind'])) { $_GET['upsellfind'] = ""; }
if(!isset($_ENV["application.cw"]["adminProductUpsellThumbsEnabled"] )) { $_ENV["application.cw"]["adminProductUpsellThumbsEnabled"] = "0" ;}
$varsToKeep = CWremoveUrlVars("upsellc,upsellsc,showtab,useralert,userconfirm");
$_POST['resetURL'] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]) . '&showtab=5';
// Force the initial category restriction 
if(isset($_ENV["application.cw"]["adminProductUpsellByCatEnabled"])) {
	if($listC['totalRows'] > 1 && $_GET['upsellc'] < 1) {
		$_GET['upsellc'] = $listC['category_id'][0];
	}
}

// set up list of products to omit from list (current product, and those already related) 
$omittedProducts = $_GET['productid'];
$product = '';
if ($productUpsellQuery['totalRows'] && isset($productUpsellQuery['product_id']) && count($productUpsellQuery['product_id']) > 0) {
    $product = implode(',',$productUpsellQuery['product_id']);
}
if(strlen(trim($product))) {
	$omittedProducts = $omittedProducts.','.trim($product);
}
// QUERY: get all potential upsell products (cat id, subcat id, list products to skip) 
$upsellsQuery = CWquerySelectUpsellSelections(
	$_GET['upsellc'],
	$_GET['upsellsc'],
	$omittedProducts,
	true
);
$varsToKeep = CWremoveUrlVars("sortby,sortdir,showtab,delupsellid,submitupsellfilter,upselldelete,userconfirm,useralert,upsellc,upsellsc");
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]).'&showtab=5';
?>
<!-- upsell container -->
<table class="noBorder">
	<tr>
		<td>
			<form action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>" name="frmDeleteUpsell" method="post">
			<h3>Related Products</h3>
<?php
if($productUpsellQuery['totalRows']) {
	// Display Up-sell items if there are any 
?>
					<!-- existing upsells table -->
					<table class="CWsort CWstripe CWformTable" summary="<?php echo $_ENV["request.cwpage"]["baseURL"];?>">
						<thead>
						<tr class="headerRow sortRow">
<?php
	if($_ENV["application.cw"]["adminProductUpsellThumbsEnabled"] != 0) {
?>
							<th class="noSort">Image</th>
<?php
	}     
?>
							<th class="product_name">Related Product Name</th>
							<th class="product_merchant_product_id">Related Product Id</th>
							<th class="noSort" style="text-align:center;"><input type="checkbox" name="all0" class="checkAll" rel="all0" id="relProdAll0">Delete</th>
						</tr>
						</thead>
						<tbody>
<?php
	for($i=0;$i<$productUpsellQuery['totalRows'];$i++) {
?>
							<tr>
<?php
		if($_ENV["application.cw"]["adminProductUpsellThumbsEnabled"] != 0) {	
			$img = explode('/',CWgetImage($productUpsellQuery['product_id'][$i],4));
			$imageFn = $img[count($img) - 1];
			if(strlen(trim($imageFn))) {
				$imageSrc = $_ENV["request.cwpage"]["adminImgPrefix"].$_ENV["application.cw"]["appImagesDir"]."/admin_preview/".$imageFn;
			} else { 
				$imageSrc = '';
			} 
?>
							    <td class="imageCell" style="text-align:center;">
<?php
			if(strlen(trim($imageSrc)) && file_exists(expandPath($imageSrc))) {
?>
								    <a href="<?php echo $_ENV["request.cw"]["thisPage"]."?productid=".$productUpsellQuery['product_id'][$i]; ?>" title="View product details"><img src="<?php echo $imageSrc ;?>" alt="View product details"></a>
<?php
			}
?>
							    </td>
<?php
		}	 
?>
								<td>
								    <strong><a class="detailsLink" href="<?php echo $_ENV["request.cw"]["thisPage"]."?productid=".$productUpsellQuery['product_id'][$i]?>" title="View product details"><?php echo $productUpsellQuery['product_name'][$i];?></a></strong>
								</td>
								<td><?php echo $productUpsellQuery['product_merchant_product_id'][$i];?></td>
								<td style="text-align: center;">
								    <input type="checkbox" name="deleteupsell_id[]" value="<?php echo $productUpsellQuery['upsell_id'][$i];?>" class="all0 formCheckbox">
								</td>
							</tr>
<?php
	} 
						// delete upsells 
?>
						<tr>
							<td colspan="4">
								<input id="DelUpsell" name="DeleteChecked" type="submit" class="deleteButton" value="Delete Selected">
							</td>
						</tr>
						</tbody>
					</table>
<?php
} else {
					// if no upsells 
?>
					<p>&nbsp;</p>
					<p class="formText"><strong>Use the options below to add related products</strong></p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
<?php
}
?>
			<!-- /end existing upsells table -->
			</form>
		</td>
	</tr>
<?php
// /END existing upsells
// Display Reciprocal Up-sell items if there are any 
if($productReciprocalUpsellQuery['totalRows']) {
?>	 
		<tr>
			<td>
				<form action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>" name="frmDeleteRecipUpsell" method="post">
					<!-- reciprocal upsells table -->
					<h3>Reciprocal Related Products</h3>
					<table class="CWsort CWstripe CWformTable" summary="<?php echo $_ENV["request.cwpage"]["baseURL"];?>">
						<thead>
						<tr class="headerRow">
<?php
	if($_ENV["application.cw"]["adminProductUpsellThumbsEnabled"] != 0) {
?>
							<th class="noSort">Image</th>
<?php
	}   
?>							
                                <th class="product_name">Related Product Name</th>
                                <th class="product_merchant_product_id">Related Product Id</th>
                                <th class="noSort" style="text-align:center;"><input type="checkbox" name="all4" class="checkAll" rel="all4" id="relProdAll4">Delete</th>
                            </tr>
                            </thead>
                            <tbody>
<?php
	for($j=0;$j<$productReciprocalUpsellQuery['totalRows'];$j++) {
?>	
							<tr>
<?php
		if($_ENV["application.cw"]["adminProductUpsellThumbsEnabled"] != 0) {	
			$img1 = explode('/',CWgetImage($productReciprocalUpsellQuery['product_id'][$j],4));
			$imageFn = $img1[count($img1) - 1];  
			if(strlen(trim($imageFn))) {
				$imageSrc = $_ENV["request.cwpage"]["adminImgPrefix"].$_ENV["application.cw"]["appImagesDir"]."/admin_preview/".$imageFn;
			} else {
				$imageSrc = '';
			}
?>								 
										<td class="imageCell" style="text-align:center;">
<?php
			if(strlen(trim($imageSrc)) && file_exists(expandPath($imageSrc))) {
?>											
										<a href="<?php $_ENV["request.cw"]["thisPage"]."?productid=".$productReciprocalUpsellQuery['product_id'][$j]?>" title="View product details"><img src="<?php echo $imageSrc ;?>" alt="View product details"></a>
<?php                                        
			}
?>
							 </td>
<?php
		}    
?>
									<td><strong><a class="detailsLink" href="<?php echo $_ENV["request.cw"]["thisPage"]."?productid=".$productReciprocalUpsellQuery['product_id'][$j]; ?>" title="View product details"><?php echo $productReciprocalUpsellQuery['product_name'][$j];?></a></strong></td>
									<td><?php echo $productReciprocalUpsellQuery['product_merchant_product_id'][$j];?></td>
									<td style="text-align: center;">
									<input type="checkbox" name="deleteupsell_id[]" value="<?php echo  $productReciprocalUpsellQuery['upsell_id'][$j];?>" class="all4 formCheckbox">
							</td>
						</tr>
<?php
	}
?>	
						<tr>
							<td colspan="4">
								<input id="DelUpsellRecip" name="DeleteChecked" type="submit" class="deleteButton" value="Delete Selected">
							</td>
						</tr>
						</tbody>
					</table>
					<!-- /end reciprocal upsells table -->
				</form>
			</td>
		</tr>
<?php
// delete upsells 
}
// /END reciprocal upsells
?>
	<tr>
		<td>
			<h3>Add Related Products</h3>
			<table class="wide">
				<tr class="headerRow">
					<th>
						Available Related Products
<?php
if($listC['totalRows']> 1 || $listSC['totalRows'] > 1 && $_ENV["application.cw"]["adminProductUpsellByCatEnabled"]) {
	echo "select by category";
}
?>
					</th>
				</tr>
				<tr>
					<td>
<?php
// depends on 'listc and listsc' queries from product search include 
if($listC['totalRows']>1 || $listSC['totalRows']> 1 && $_ENV["application.cw"]["adminProductUpsellByCatEnabled"]) {
?>
							<form name="filterUpsell" id="CWadminUpsellSearch" method="GET" action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>">
								<strong>Filter By: </strong>
<?php
	// categories
	if($listC['totalRows']> 1) {
?>
										&nbsp;Category:&nbsp;
										<select name="upsellc" id="upsellc" onchange="document.getElementById('CWadminUpsellSearch').submit()" onkeyup="this.change();">
<?php
		// <option value="">All</option>
		for($i=0;$i<$listC['totalRows'];$i++) {
?>
    										<option value="<?php echo $listC['category_id'][$i];?>"<?php if($_GET['upsellc']== $listC['category_id'][$i]) {?> selected<?php }?>><?php echo substr($listC['category_name'][$i], 0, 23);?>
                                            </option>
<?php
		}
?>
										</select>
<?php
	}
	// subcategories
	if($listSC['totalRows'] > 1) {
?>
										&nbsp;Subcategory:&nbsp;
										<select name="upsellsc" id="upsellsc" onchange="document.getElementById('CWadminUpsellSearch').submit()" onkeyup="this.change();" >
										<option value="0">All</option>
<?php                                        
		for($i=0;$i<$listSC['totalRows'];$i++) {
?>
    										    <option value="<?php echo $listSC['secondary_id'][$i];?>"<?php if($_GET['upsellsc'] == $listSC['secondary_id'][$i]) { ?> selected<?php } ?>><?php echo substr($listSC['secondary_name'][$i], 0, 23); ?></option>
<?php
		}
?>
										</select>
<?php
	}
	// hidden fields 
?>
								<input type="hidden" name="showtab" value="5">
								<input name="productid" type="hidden" value="<?php echo $_GET['productid'];?>">
								<?php // submit button  ?>
								<input type="submit" name="submitupsellfilter" id="submitupsellfilter" class="CWformButton" value="Go">
								<?php // reset search link  ?>
								<span>
									<a href="<?php echo $_POST['resetURL']; ?>">Reset Search</a>
								</span>
							</form>
<?php
}
// add upsell form 
?>
						<form name="frmAddUpsell" method="POST" action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>">
							<!-- the select upsell table-->
							<table class="CWsort CWstripe" id="tblUpsellSelect" summary="<?php echo $_ENV["request.cwpage"]["baseURL"];?>">
								<thead>
								<tr>
									<th class="product_name">Product Name</th>
									<th class="product_merchant_product_id">Product ID</th>
									<th style="width:75px;"  class="noSort checkHeader"><input type="checkbox" name="all1" class="checkAll" rel="all1" id="relProdAll1">Add</th>
									<th style="width:75px;"  class="noSort checkHeader"><input type="checkbox" name="all2" class="checkAll" rel="all2" id="relProdAll2">2-Way</th>
<?php
if ($_ENV["application.cw"]["adminProductLinksEnabled"]) {
?>
										<th style="width:50px; text-align:center;" class="noSort">View</th>
<?php
}
?>
								</tr>
								</thead>
								<tbody>
<?php
for($k=0;$k<$upsellsQuery['totalRows'];$k++) {      
?>								
								<tr>
									<td title="Select related product"><?php echo $upsellsQuery['product_name'][$k]; ?></td>
									<td title="Select related product"><?php echo $upsellsQuery['product_merchant_product_id'][$k];?></td>
									<td title="Select related product" style="text-align:center" class="firstCheck">
										<input type="checkbox" name="UpSellproduct_id[]" value="<?php echo $upsellsQuery['product_id'][$k]; ?>" class="all1">
									</td>
									<td title="Create two-way upsell" style="text-align:center" class="recipCheck">
										<input type="checkbox" name="UpSellProductRecip_ID[]" value="<?php echo $upsellsQuery['product_id'][$k];?>" class="all2">
									</td>
<?php
if ($_ENV["application.cw"]["adminProductLinksEnabled"]) {
?>
									<td style="width:50px;text-align:center">
										<a href="<?php echo $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["request.cwpage"]["urlDetails"]."?product=".$upsellsQuery['product_id'][$k]; ?>" title="View on Web: <?php echo ($upsellsQuery['product_name'][$k]);?>" rel="external" class="columnLink"><img alt="View on Web: <?php echo $upsellsQuery['product_name'][$k]; ?>" src="img/cw-product-view.png"></a>
									</td>
<?php
}
?>
								</tr>
<?php
}
?>
								</tbody>
							</table>
							<div style="clear:both">
								<input name="AddUpsell" type="submit" class="CWformButton" id="AddUpsell" value="Add Selected Products">
							</div>
							<input name="product_id" type="hidden" value="<?php echo $_GET['productid'];?>">
							<input type="hidden" name="showtab" value="5">
						</form>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<?php
// Display an Up-sell error if one exist
if(isset($_POST['UpSellProductIDError'])) {
?>
		<tr>
			<td>
				<div class="smallprint">
					<p><strong>**<?php echo $_POST['UpSellProductIDError'];?></strong></p>
				</div>
			</td>
		</tr>
<?php
}
?>
<!-- /end upsell container -->
</table>