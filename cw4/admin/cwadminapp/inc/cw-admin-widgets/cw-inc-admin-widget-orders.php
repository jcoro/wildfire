<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-widget-orders.php
File Date: 2012-07-07
Description: displays recent orders on admin home page
Uses the select orders query (order search) to return orders by date
==========================================================
*/
$showCt = $_ENV["application.cw"]["adminWidgetOrders"];
if($showCt > 0) {
	// QUERY: get recent orders (status, datestart, dateend, IDstring, customer, maxorders) 
	$recentOrdersQuery = CWquerySelectOrders(0,0,0,'','',$showCt);
	// start output ?>
	<div class="CWadminHomeWidget">
		<h3>Recent Orders</h3>
<?php
	// ORDERS TABLE 
	// if no records found, show message 
	if(!$recentOrdersQuery['totalRows']) {
?>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p><strong>No orders found.</strong></p>
<?php
	}else{
		// if we have some records to show ?>
			<table class="CWstripe CWwidgetTable">
				<thead>
				<tr>
					<th width="45" class="order_date">Date</th>
					<th width="80" class="order_id">Order ID</th>
					<th>Customer</th>
					<th width="60" class="order_total">Total</th>
					<th width="55" class="shipstatus_name">Status</th>
				</tr>
				</thead>
				<tbody> 
<?php
		for($i=0; $i<$recentOrdersQuery['totalRows']; $i++) {
			// OUTPUT ORDERS 
			// simple var for status 
			$status = $recentOrdersQuery['shipstatus_name'][$i];
			$statusID = $recentOrdersQuery['order_status'][$i];
			// output the row 
			// date ?>
				<tr>
					<td style="white-space: nowrap;"><div class="tablePad"></div><strong><?php  echo date('M  d',strtotime($recentOrdersQuery['order_date'][$i]));?></strong></td>
<?php 
			// order id 
			if(strlen($recentOrdersQuery['order_id'][$i]) > 8) {
				$showID='...'. substr($recentOrdersQuery['order_id'][$i],0,-8);
			} else {
				$showID=$recentOrdersQuery['order_id'][$i];
			}
?>
					<td style="text-align:left;"><strong><a class="productLink" href="order-details.php?order_id=<?php echo $recentOrdersQuery['order_id'][$i]; ?>"><?php echo $showID;?></a></strong></td>
					<?php // customer name ?>
					<td><?php echo $recentOrdersQuery['customer_last_name'][$i]; ?>, <?php echo $recentOrdersQuery['customer_first_name'][$i]; ?></td>
					<td class="decimal">
<?php
			// order total 
			echo cartweaverMoney($recentOrdersQuery['order_total'][$i]);?>
					</td>
<?php
			// status 
			if($statusID == 1) {  
				$status = '<strong>'.$status.'</strong>';
			}?>
					<td><?php echo $status;?></td>
				</tr>
				</tbody>
<?php
		// /END OUTPUT ORDERS 
		}
?>                
			</table>
			<?php // footer links ?>
			<div class="tableFooter"><a href="orders.php">View all Order History</a></div>
<?php
	}
?>
	</div>
<?php 
	// /END if records found 
}
?>