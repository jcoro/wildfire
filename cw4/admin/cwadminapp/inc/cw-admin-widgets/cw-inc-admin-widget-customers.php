<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-widget-customers.php
File Date: 2012-02-01
Description:
Displays top spending customers on admin home page
Uses the CWquerySearchProducts query to sort by date descending
==========================================================
*/
$showCt = $_ENV["application.cw"]["adminWidgetCustomers"];
if ($showCt > 0) {
	$topCustomersQuery = CWquerySelectTopCustomers($showCt);
	// start output 
?>	
		<div class="CWadminHomeWidget">
            <h3>Top Customers</h3>
<?php
            // PRODUCTS TABLE 
            // if no records found, show message 
			if(!$topCustomersQuery['totalRows']) {
				?>
            	<p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p><strong>No customers found.</strong></p>
<?php				
			} else {
            	// if we have some records to show 
				?>
			<table class="CWwidgetTable CWstripe">
				<thead>
				<tr class="sortRow">
					<th class="customer_last_name">Name</th>
					<th class="customer_email">Email</th>
					<th class="top_order_date">Order</th>
					<th width="35" class="order_total">Amt.</th>
					<th class="total_spending">Total</th>
				</tr>
				</thead>
				<tbody>
					
<?php
				// OUTPUT CUSTOMERS 
				for($i=0; $i<$topCustomersQuery['totalRows']; $i++) { 
					// set up email 
					if(strlen($topCustomersQuery['customer_email'][$i]) > 15) {
						$showEmail = substr($topCustomersQuery['customer_email'][$i],0,12) . '...';
					} else {
						$showEmail = $topCustomersQuery['customer_email'][$i];
					}
				// output the row 
?>                
				<tr>
					<?php // name  ?>
					<td><div class="tablePad"></div><strong><a class="productLink" href="customer-details.php?customer_id=<?php echo $topCustomersQuery['customer_id'][$i]; ?>"><?php echo $topCustomersQuery['customer_last_name'][$i]; ?>,<?php echo $topCustomersQuery['customer_first_name'][$i]; ?> </a></strong></td>
					<?php // email  ?>
					<td class="noLink"><?php  ?><a href="mailto:<?php echo $topCustomersQuery['customer_email'][$i]; ?>" class="columnLink"><?php echo $showEmail; ?></a><?php  ?></td>
<?php
					// order date 
					if(isset($topCustomersQuery['top_order_date'][$i]) && $topCustomersQuery['top_order_date'][$i]) {
						// if searching by order, we have this info already above 
						$customer_date = $topCustomersQuery['top_order_date'][$i];
					} else {
						// QUERY: get customer's last order via simple query (customer id, no. of rows to return) 
						//echo $topCustomersQuery['customer_id'][$i];
						$lastOrderQuery = CWquerySelectCustomerOrders($topCustomersQuery['customer_id'][$i],1);
						$customer_date = $lastOrderQuery['order_date'][0];
					} 
?>                    
					<td style="white-space: nowrap;"><?php echo date("Y-m-d",cartweaverStrtotime($customer_date));?></td>
					<?php // order total  ?>
					<td class="decimal">
<?php
						if(isset($topCustomersQuery['order_total'][$i]) && is_numeric($topCustomersQuery['order_total'][$i])) {
							echo cartweaverMoney($topCustomersQuery['order_total'][$i]);
						}
?>                    
					</td>
					<?php // grand total  ?>
					<td class="decimal">
						<?php echo cartweaverMoney($topCustomersQuery['total_spending'][$i]);?>                    
					</td>
				</tr>
				<?php 
				// /END OUTPUT CUSTOMERS 
				}
			}
			?>
                </tbody>
			</table>
			<?php // footer links  ?>
			<div class="tableFooter"><a href="customers.php">View all Customers</a></div></div>
<?php			
			}
?>            	
