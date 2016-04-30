<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-widget-products-bestselling.php
File Date: 2012-02-01
Description:
Displays top selling products on admin home page
Uses the specific Top Products query function
==========================================================
*/
$showCt = 0;
if (isset($_ENV["application.cw"]["adminWidgetProductsBestselling"])) $showCt = $_ENV["application.cw"]["adminWidgetProductsBestselling"];
if ($showCt > 0) {
	// QUERY: get best selling products (number of records to show) 
	$topProductsQuery=CWquerySelectTopProducts($showCt);
	// start output ?>
         <div class="CWadminHomeWidget">
         <h3>Best Selling Products</h3>
<?php
	// PRODUCTS TABLE 
	// if no records found, show message 
	if(!($topProductsQuery['totalRows'])) {
?>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p><strong>No products found.</strong></p>
<?php
	} else {
?>
                <table class="CWwidgetTable CWstripe">
                    <thead>
                    <tr class="sortRow">
                        <th class="product_name">Product Name</th>
                        <th class="product_merchant_product_id">Product ID</th>
                        <th>Sold</th>
<?php
		// add 'view on site' link 
		if(isset($_ENV["application.cw"]["adminProductLinksEnabled"]) && $_ENV["application.cw"]["adminProductLinksEnabled"]) {
?>
                    
							<th class="noSort" width="30">View</th>
<?php
		}
?>
                        </tr>
                        </thead>
                        <tbody>
<?php
		// OUTPUT THE PRODUCTS 
		for($i=0; $i<$topProductsQuery['totalRows']; $i++) {
?>
                        <tr>
                            <td><div class="tablePad"></div>
                                <strong>
                                
                                <a class="productLink" href="product-details.php?productid=<?php echo $topProductsQuery['product_id'][$i];?>" title="Edit Product: <?php echo $topProductsQuery['product_name'][$i];?>"><?php echo $topProductsQuery['product_name'][$i];?></a>
                                </strong>
                            </td>
                              <td><?php echo $topProductsQuery['product_merchant_product_id'][$i];?></td>                   
                            <?php // number sold ?>
                            <td><?php echo $topProductsQuery['prod_counter'][$i];?></td>
<?php
			// view product link 
			if(isset($_ENV["application.cw"]["adminProductLinksEnabled"]) && $_ENV["application.cw"]["adminProductLinksEnabled"]) {
?>
                            <td style="text-align:center;"><a href="<?php echo $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["request.cwpage"]["urlDetails"].'?product='.$topProductsQuery['product_id'][$i]; ?>" class="columnLink" rel="external" title="View on Web: <?php echo $topProductsQuery['product_name'][$i];  ?>"><img src="img/cw-product-view.png" alt="View on Web: <?php echo $topProductsQuery['product_name'][$i]; ?>"></a></td>
<?php
			} ?>
					</tr>
<?php
		} ?>                    
					</tbody>
			</table>
			<div class="tableFooter"><a href="products.php">View all Products</a></div>
<?php
		// /END PRODUCTS TABLE 
	}
?>
</div>
<?php
}
?>