<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-widget-products-recent.php
File Date: 2012-02-01
Description:
Displays recently added or modified products on admin home page
Uses the CWquerySearchProducts query to sort by date descending
==========================================================
*/
$showCt = 0;
if (isset($_ENV["application.cw"]["adminWidgetProductsRecent"])) $showCt = $_ENV["application.cw"]["adminWidgetProductsRecent"];
if($showCt > 0) {
	// QUERY: get recently added products (searchstring,searchby,searchcat,searchscndcat,searchsortby,searchsortdir,searcharchived)
   	$recentProductsQuery=CWquerySearchProducts('','',0,0,'product_date_modified','desc',FALSE,$showCt);
	// start output ?>
        <div class="CWadminHomeWidget">
            <h3>Recently Added / Updated Products</h3>
<?php 
	// PRODUCTS TABLE 
	// if no records found, show message 
	if(!(isset($recentProductsQuery['totalRows']))) {
?>
              <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p><strong>No products found.</strong></p>
<?php
	} else {	
		// if we have some records to show ?>
                <table class="CWwidgetTable CWstripe">
                    <thead>
                    <tr class="sortRow">
                        <th class="product_name">Product Name</th>
                        <th class="product_merchant_product_id">Product ID</th>
                        <?php // add date modified ?>
                        <th>Modified</th>
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
		for($i=0; $i < $recentProductsQuery['totalRows'] ; $i++) {
			// OUTPUT THE PRODUCTS ?>
				        <tr>
                            <td><div class="tablePad"></div>
                                <strong>
                                <a class="productLink" href="product-details.php?productid=<?php  echo $recentProductsQuery['product_id'][$i]?>" title="Edit Product: <?php echo $recentProductsQuery['product_name'][$i]; ?>"><?php echo $recentProductsQuery['product_name'][$i];?></a>
                        
				
                				</strong>
                            </td>
                            <td><?php echo $recentProductsQuery['product_merchant_product_id'][$i];?></td>
                            <td>
                               
                                <span class="dateStamp">
                                    <?php echo date('Y-m-d',strtotime($recentProductsQuery['product_date_modified'][$i]));?>
                                </span>
					</td>
<?php
			// view product link 
			if(isset($_ENV["application.cw"]["adminProductLinksEnabled"]) && $_ENV["application.cw"]["adminProductLinksEnabled"]) {
?>                            
                            <td style="text-align:center;"><a href="<?php echo $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["request.cwpage"]["urlDetails"]."?product=".$recentProductsQuery['product_id'][$i]; ?>" rel="external" class="columnLink" title="View on Web: <?php echo $recentProductsQuery['product_name'][$i]; ?>"><img src="img/cw-product-view.png" alt="View on Web: <?php echo $recentProductsQuery['product_name'][$i]; ?>"></a></td>
<?php 
			}
?>
                	
					</tr>
<?php
		}
?>
                    </tbody>
			</table>
            <div class="tableFooter"><a href="products.php">View all Products</a></div>
<?php	
	}
?>
</div>
<?php 
}
?>
