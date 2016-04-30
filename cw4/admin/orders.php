<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: orders.php
File Date: 2012-06-25
Description: Displays order management table
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("any");
// PAGE PARAMS 
if(!isset($_GET['pagenumresults'])) { $_GET['pagenumresults'] = 1; }
if(!isset($_GET['status'])) { $_GET['status'] = 0; }
if(!isset($_GET['orderStr'])) { $_GET['orderStr'] = ""; }
if(!isset($_GET['custName'])) { $_GET['custName'] = ""; }
if(!isset($_GET['maxrows'])) { $_GET['maxrows'] = $_ENV["application.cw"]["adminRecordsPerPage"]; }
if(!isset($_GET['sortby'])) { $_GET['sortby'] = "order_date"; }
if(!isset($_GET['sortdir'])) { $_GET['sortdir'] = "desc"; }
// start/end dates 
if (!isset($_GET['startDate'])) {
	$_GET['startDate'] = cartweaverScriptDate(strtotime("-3Months"));
}
if (isset($_GET['startDate']) && $_GET["startDate"] && cartweaverStrtotime($_GET['startDate'], $_ENV["request.cw"]["scriptDateMask"]) !== false) {
	if(!isset($_GET['endDate'])) $_GET['endDate'] = cartweaverScriptDate(CWtime());
} else {
	if(!isset($_GET['endDate'])) $_GET['endDate'] = "";
}
// default value for order type label
if(!isset($_ENV["request.cwpage"]["orderType"])) { $_ENV["request.cwpage"]["orderType"] = "All"; }
// starting value for order total row 
$_ENV["request.cwpage"]["orderTotal"] = 0;
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,pagenumresults,userconfirm,useralert,status");
// create the base url for sorting out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// get current page status if defined 
if($_GET['status'] > 0) {
	// QUERY: get order status to show 
	$getStatus = CWquerySelectOrderStatus($_GET['status']);
	//echo $_GET['status'];
	$_ENV["request.cwpage"]["orderType"] = $getStatus['shipstatus_name'][0];
}
elseif (isset($_POST['status']) && $_POST['status'] > 0) {
	$getStatus = CWquerySelectOrderStatus($_POST['status']);
	$_ENV["request.cwpage"]["orderType"] = $getStatus['shipstatus_name'][0];
}
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Manage Orders";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Order Management: " .$_ENV["request.cwpage"]["orderType"]. " Orders";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Use the search options and table links to view and manage orders";
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"];
if($_GET['status'] > 0) {
	$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"]. "?status=".$_GET['status'];
}
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"] = 1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"] = 1;
// START OUTPUT ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
 <?php 	include("cwadminapp/inc/cw-inc-admin-scripts.php"); ?>
	</head>
<?php
// body gets a class to match the filename 
$page = explode('.',$_ENV["request.cw"]["thisPage"]);
$page_First = $page[0];
?>
	<body <?php echo 'class="'.$page_First.'"'; ?>>
		<div id="CWadminWrapper">
			<!-- Navigation Area -->
			<div id="CWadminNav">
				<div class="CWinner">
 <?php include("cwadminapp/inc/cw-inc-admin-nav.php"); ?>             
				</div>
				<!-- /end CWinner -->
			</div>
			<!-- /end CWadminNav -->
			<!-- Main Content Area -->
			<div id="CWadminPage">
				<!-- inside div to provide padding -->
				<div class="CWinner">
<?php
// page start content / dashboard 
include("cwadminapp/inc/cw-inc-admin-page-start.php");
if(strlen(trim($_ENV["request.cwpage"]["heading1"]))) {
	echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]). '</h1>';
}
if(strlen(trim($_ENV["request.cwpage"]["heading2"]))) {
	echo '<h2>'.trim($_ENV["request.cwpage"]["heading2"]).'</h2>';
}
?>
					<!-- Admin Alert - message shown to user -->
<?php
include("cwadminapp/inc/cw-inc-admin-alerts.php");
?>
					<!-- Page Content Area -->
					<div id="CWadminContent">
						<!-- //// PAGE CONTENT ////  -->
						<?php // SEARCH ?>
						<div id="CWadminOrderSearch" class="CWadminControlWrap">
							<?php
// Order Search Form 
if (($_GET["startDate"] && cartweaverStrtotime($_GET["startDate"], $_ENV["request.cw"]["scriptDateMask"]) === false) || ($_GET["endDate"] && cartweaverStrtotime($_GET["endDate"], $_ENV["request.cw"]["scriptDateMask"]) === false)) {
	// if dates are invalid, redirect 
	CWpageMessage("alert","Invalid Date Range");
	header("Location: ".$_ENV["request.cw"]["thisPage"]."?useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
} else {
	include("cwadminapp/inc/cw-inc-search-order.php");
}
// if orders found, show the paging links 
if($ordersQuery['totalRows'] > 0) {
	echo $_ENV["request.cwpage"]["pagingLinks"];
	// set up the table display output 
	if(!isset($_ENV["application.cw"]["adminOrderPaging"])) { $_ENV["application.cw"]["adminOrderPaging"] = 1;}
	if(!$_ENV["application.cw"]["adminOrderPaging"]) {
		$startRow_Results = 1;
		$maxRows_Results = $ordersQuery['totalRows'];
	}
}
?>
						</div>
<?php
// /END SEARCH 
// ORDERS TABLE 
// if no records found, show message 
if(!$ordersQuery['totalRows']) {  ?>
                        	<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p><strong>No orders found.</strong> <br><br>Try a different search above.</p>
<?php
} else {
	// if we have some records to show ?>
							<table class="CWsort CWstripe" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
								<thead>
								<tr class="sortRow">
									<th class="noSort" width="50">View</th>
									<th width="85" class="order_date">Date</th>
									<th width="135" class="order_id">Order ID</th>
									<th class="customer_last_name">Customer</th>
									<th class="customer_zip">Ship To Address</th>
									<th width="75" class="order_total">Total</th>
									<th width="75" class="shipstatus_name">Status</th>
								</tr>
								</thead>
								<tbody>
<?php
	// OUTPUT ORDERS 
	for($i=$startRow_Results-1; $i<$ordersQuery['totalRows'] && $i<$endRow_Results; $i++) {
		// simple var for status 
		$status = $ordersQuery['shipstatus_name'][$i];
		$statusID = $ordersQuery['order_status'][$i];
		// set up location  
		$order_location = $ordersQuery['order_address1'][$i].', '. $ordersQuery['order_city'][$i]. ', '. $ordersQuery['order_state'][$i]. ' '.$ordersQuery['order_zip'][$i]; 
		// tabulate running total 
		$_ENV["request.cwpage"]["orderTotal"] += $ordersQuery['order_total'][$i];
		// output the row ?>
									<tr>
									<?php // details link ?>
									<td style="text-align:center;"><a href="order-details.php?order_id=<?php echo $ordersQuery['order_id'][$i];?>" title="View Order Details"><img src="img/cw-edit.gif" width="15" height="15" alt="View Order Details"></a></td>
									<?php // date ?>
									<td style="white-space: nowrap;"><strong><?php echo cartweaverDate($ordersQuery['order_date'][$i]);?></strong></td>
<?php 
		// order id 
		if(strlen($ordersQuery['order_id'][$i]) > 18) {
			$showID  = '...' . substr($ordersQuery['order_id'][$i],0,-18);
		} else {
			$showID = $ordersQuery['order_id'][$i];
		}
?>
									<td style="text-align:left;"><strong><a class="productLink" href="order-details.php?order_id=<?php echo $ordersQuery['order_id'][$i]; ?>"><?php echo $showID; ?></a></strong></td>
									<?php // customer name ?>
									<td><a href="customer-details.php?customer_id=<?php echo $ordersQuery['customer_id'][$i];?>" class="columnLink"><?php echo $ordersQuery['customer_last_name'][$i]. ', ' .$ordersQuery['customer_first_name'][$i];?></a></td>
									<?php // order location : remove blanks ( , , ) ?>
									<td><?php echo str_replace(", ,","",$order_location); ?></td>
									<?php // order total ?>
									<td style="text-align:right;"><?php echo cartweaverMoney($ordersQuery['order_total'][$i]); ?></td>
<?php
		// status 
		if($statusID == 1) {
			$status = "<strong>".$status."</strong>";
		}
?>							
									<td style="text-align:center;"><?php echo $status; ?></td>
								</tr>
<?php
	}
	// sum total row ?>
								<tr class="dataRow">
									<th colspan="5" style="text-align:right;"><strong>Total</strong></th>
									<td style="text-align:right;"><strong><?php echo cartweaverMoney($_ENV["request.cwpage"]["orderTotal"]); ?></strong></td>
									<td></td>
								</tr>
								<?php // /END OUTPUT ORDERS ?>
								</tbody>
							</table>
							<?php // footer links ?>
							<div class="tableFooter"><?php echo $_ENV["request.cwpage"]["pagingLinks"]; ?></div>
<?php
} // end of if
// /END if records found ?>     
					</div>
					<!-- /end Page Content -->
					<div class="clear"></div>
				</div>
				<!-- /end CWinner -->
			</div>
<?php 
// page end content / debug 
include("cwadminapp/inc/cw-inc-admin-page-end.php");
?>         
			<!-- /end CWadminPage-->
			<div class="clear"></div>
		</div>
		<!-- /end CWadminWrapper -->
	</body>
</html>