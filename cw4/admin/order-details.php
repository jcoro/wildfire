<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: order-details.php
File Date: 2012-07-11
Description: Displays order details and status options
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// include the mail functions 
// mail functions 
if (!(function_exists("CWsendMail"))) {
	require_once("../cwapp/func/cw-func-mail.php");
}
// avatax returns use the front end tax functions 
if (strtolower($_ENV["application.cw"]["taxCalctype"]) == 'avatax' && !function_exists("CWgetAvalaraTax")) {
	require_once("../cwapp/func/cw-func-tax.php");
}
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"]=CWauth("any");
// SPECIAL STATUS CODES 
// shipping status (change this number if status options are altered) 
$_ENV["request.cwpage"]["paidStatusCode"]=3;
$_ENV["request.cwpage"]["shippedStatusCode"]=4;
$_ENV["request.cwpage"]["cancelledStatusCode"]=5;
$_ENV["request.cwpage"]["returnedStatusCode"]=6;
// PAGE PARAMS 
if(!(isset($_ENV["application.cw"]["adminRecordsPerPage"]))) $_ENV["application.cw"]["adminRecordsPerPage"]=30;
// default values for seach/sort
if(!(isset($_GET['pagenumresults']))) $_GET['pagenumresults'] = 1;
if(!(isset($_GET['status']))) $_GET['status'] = 0;
// start/end dates 
if (!isset($_GET['startDate'])) {
	$_GET['startDate'] = cartweaverDate(date("Y-m-d", CWtime())." -3Months");
}
if (isset($_GET['startDate']) && $_GET["startDate"] && cartweaverStrtotime($_GET['startDate']) !== false) {
	if(!isset($_GET['endDate'])) $_GET['endDate'] = cartweaverDate(date("Y-m-d", CWtime()));
} else {
	if(!isset($_GET['endDate'])) $_GET['endDate'] = "";
}
if(!(isset($_GET['orderStr']))) $_GET['orderStr'] = '';
if(!(isset($_GET['custName']))) $_GET['custName'] = '';
if(!(isset($_GET['maxrows']))) $_GET['maxrows'] = $_ENV["application.cw"]["adminRecordsPerPage"];	
if(!(isset($_GET['sortby']))) $_GET['sortby'] = 'order_date';
if(!(isset($_GET['sortdir']))) $_GET['sortdir'] = 'asc';
if(!(isset($_GET["returnUrl"]))) $_GET["returnUrl"] = 'orders.php';
// define showtab to set up default tab display 
if(!(isset($_GET['showtab']))) $_GET['showtab'] = 1;
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep=CWremoveUrlVars("view,userconfirm,useralert,sortby,sortdir");
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"]=CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// Set local variable for storing display line item tax and discount preferences 
if(!(isset($_ENV["application.cw"]["taxDisplayLineItem"]))) $_ENV["application.cw"]["taxDisplayLineItem"]='false';
if(!(isset($_ENV["application.cw"]["taxSystemLabel"]))) $_ENV["application.cw"]["taxSystemLabel"]='false';
if(!(isset($_ENV["application.cw"]["discountDisplayLineItem"]))) $_ENV["application.cw"]["discountDisplayLineItem"]='false';
$taxDisplayLineItem=$_ENV["application.cw"]["taxDisplayLineItem"];
$discountDisplayLineItem=$_ENV["application.cw"]["discountDisplayLineItem"];
// /////// 
// DELETE ORDER 
// /////// 
if((isset($_GET["deleteOrder"]))) {
	// QUERY: delete order (order id) 
	$deleteOrder = CWqueryDeleteOrder($_GET['deleteOrder']);	
	header("Location: ".$_GET["returnUrl"]);
}
// /////// 
// /END DELETE ORDER 
// /////// 
// /////// 
// LOOKUP ORDER 
// /////// 
// make sure we have a valid order ID 
if(!(isset($_GET['order_id']))) $_GET['order_id']='';
// if not id specified, return to list page 
if(!strlen(trim($_GET['order_id']))) {	
	header("location: orders.php");
} else {
	// if we do have an order, run queries 
    // QUERY: check that order exists (order id) 
	$checkOrder=CWquerySelectOrder($_GET['order_id']);
	// QUERY: get order and related details (order id) 
	$orderQuery=CWquerySelectOrderDetails($_GET['order_id']);
	// if one order not found, redirect to list 
	if($checkOrder['totalRows'] != 1) {
		CWpageMessage("alert","Order Not Found");
		header("location: order.php?useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
	} else {
		// if one order is found 
		// if order details not found, i.e. no skus exist 
		if(!($orderQuery['totalRows']))
			CWpageMessage("alert","Incomplete Order Data (ID: ".$_GET['order_id']); 		
	}
	// /end order check 
	// set up columns for display 
	if ($orderQuery['totalRows'] && $orderQuery["order_discount_total"][0] <= 0) {
		$discountDisplayLineItem = false;
	}
	$CartColumnCount=0;
	if($taxDisplayLineItem) $CartColumnCount=$CartColumnCount+2;
	if($discountDisplayLineItem) $CartColumnCount=$CartColumnCount + 1;
	// order ID is ok in URL, run other queries 
	// QUERY: get all available shipping status options 
	$orderStatusQuery=CWquerySelectOrderStatus();
	$rsCWSums = array("totalRows" => 1, "TotalDiscount" => array(0), "SubTotal" => array(0), "TotalTax" => array(0));
	for($i=0;$i < $orderQuery['totalRows']; $i++) {
		$rsCWSums["TotalDiscount"][0] += ($orderQuery["ordersku_discount_amount"][$i] * $orderQuery["ordersku_quantity"][$i]);
		$rsCWSums["SubTotal"][0] += ($orderQuery["ordersku_unit_price"][$i] * $orderQuery["ordersku_quantity"][$i]);
	}
	$rsCWSums["TotalTax"][0] = (($orderQuery['totalRows']) ? array_sum($orderQuery["ordersku_tax_rate"]) : 0 );
	// QUERY: get applied discounts 
	$discountsQuery = (($orderQuery['totalRows']) ? CWquerySelectOrderDiscounts($orderQuery["order_id"][0]) : array("totalRows" => 0) );
	// set up discount list 
	if (!isset($discountsQuery["discount_usage_discount_id"]) || !is_array($discountsQuery["discount_usage_discount_id"])) $discountsQuery["discount_usage_discount_id"] = array();
	$discountList = implode(",", $discountsQuery["discount_usage_discount_id"]);
	// set up descriptions content 
	// reset description list 
	$_ENV["request.cwpage"]["discountDescriptions"] = array();
	if (strlen($discountList)) {
		// loop list of applied discounts 
		foreach ($discountsQuery["discount_usage_discount_id"] as $key => $d) {
			// lookup description 
			$discountDescription = CWgetDiscountDescription($d);
			// if description exists, add it to a list 
			$_ENV["request.cwpage"]["discountDescriptions"][] = trim($discountDescription);
		}
	}
	// QUERY: get payments for this order 
	$paymentsQuery = CWorderPayments($_GET['order_id']);
}
// /////// 
// /END LOOKUP ORDER 
// /////// 
// /////// 
// UPDATE ORDER 
// /////// 
if(isset($_POST['Update'])) {
	// if set to 'shipped' or 'returned' and we don't have a date, show an error 
	if ($_POST["order_status"] == $_ENV["request.cwpage"]["shippedStatusCode"] && (!strlen(trim($_POST["order_ship_date"])) || cartweaverStrtotime(trim($_POST["order_ship_date"]), $_ENV["request.cw"]["scriptDateMask"]) === false)) {
		CWpageMessage("alert","Ship Date is Required");
	} else if ($_POST["order_status"] == $_ENV["request.cwpage"]["returnedStatusCode"] && (!strlen(trim($_POST["order_return_date"])) || cartweaverStrtotime(trim($_POST["order_return_date"]), $_ENV["request.cw"]["scriptDateMask"]) === false)) {
		CWpageMessage("alert","Return Date is Required");
	} else {
		// if date is ok, update the order 
		// QUERY: update order details (order form variables) 
		$updateOrder = CWqueryUpdateOrder($_POST['orderID'],
										$_POST["order_status"],
										$_POST['order_ship_date'],
										$_POST['order_actual_ship_charge'],
										$_POST['order_ship_tracking_id'],
										$_POST['order_notes'],
										$_POST['order_return_date'],
										$_POST['order_return_amount']);
		// set up confirmation message 
		CWpageMessage("confirm","Order Updated");
		$addAlert = false;
		if ($_POST["order_status"] == $_ENV["request.cwpage"]["shippedStatusCode"] && $orderQuery['order_status'][0] != $_ENV["request.cwpage"]["shippedStatusCode"]) {
            // build the order details content 
			$mailBody= CWtextOrderDetails($_POST['orderID']);
			$mailContent=''.$_ENV["application.cw"]["mailDefaultOrderShippedIntro"].'

'.$mailBody.'

'.$_ENV["application.cw"]["mailDefaultOrderShippedEnd"].'
';
			// send the content to the customer 
			$confirmationResponse = CWsendMail($mailContent, 'Order Shipment Notification',$orderQuery['customer_email'][0]);
			// add the response to the page message 
			$hadError = false;
			foreach ($confirmationResponse as $key => $response) {
				if (stripos($response, "error") !== false) {
					$hadError = true;
				}
			}
			if ($hadError) {
				CWpageMessage("alert", implode(", ", $confirmationResponse));
				$addAlert = true;
			} else {
				CWpageMessage("confirm", "Shipping Status Updated: ".implode(", ", $confirmationResponse));
			}
		// if changing to status paid in full, and using avatax, send order details 
		} else if ($_POST["order_status"] == $_ENV["request.cwpage"]["paidStatusCode"] &&
				$orderQuery['totalRows'] && $orderQuery["order_status"][0] < $_ENV["request.cwpage"]["paidStatusCode"] &&
				strtolower($_ENV["application.cw"]["taxCalctype"]) == 'avatax') {
			$postTax = CWpostAvalaraTax($orderQuery["order_id"][0]);
		// if changing to status cancelled, and using avatax, send cancellation 
		} else if (($_POST["order_status"] == $_ENV["request.cwpage"]["cancelledStatusCode"] || $_POST["order_status"] == $_ENV["request.cwpage"]["returnedStatusCode"]) &&
				$orderQuery['totalRows'] && $orderQuery["order_status"][0] >= $_ENV["request.cwpage"]["paidStatusCode"] &&
				strtolower($_ENV["application.cw"]["taxCalctype"]) == 'avatax') {
			$refundTax = CWpostAvalaraTax($orderQuery["order_id"][0], true);
		}
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		$urlAlert = '&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]);
		if ($addAlert) {
			$urlAlert .= '&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]);
		}
		// update complete: return to page showing message 
		header("Location: ".$_ENV["request.cw"]["thisPage"]."?order_id=".$_GET['order_id'].$urlAlert);
		exit;
	}
}
// /////// 
// /END UPDATE ORDER 
// /////// 
// set up subheading 
$_ENV["request.cwpage"]["subHead"]= "
Date/Time: ".(($orderQuery['totalRows']) ? date($_ENV["application.cw"]["globalDateMask"],CWtime($orderQuery['order_date'][0])) : "" )."&nbsp;&nbsp;&nbsp;
Status: ".(($orderQuery['totalRows']) ? $orderQuery['shipstatus_name'][0] : "" )."&nbsp;&nbsp;&nbsp;
Total: ".(($orderQuery['totalRows']) ? cartweaverMoney($orderQuery['order_total'][0]) : "")."&nbsp;&nbsp;&nbsp;
SKUs: ".$orderQuery['totalRows']."
";
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Order Details";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Order Details&nbsp;&nbsp;&nbsp;<span class='subHead'>ID: ".(($orderQuery['totalRows']) ? $orderQuery["order_id"][0] : "")."&nbsp;&nbsp;&nbsp;</span>
";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = $_ENV["request.cwpage"]["subHead"];
// current menu marker 
// if order has a known status, mark that status link in admin 
if ($orderQuery['totalRows'] && $orderQuery['order_status'][0] > 0 && array_search($orderQuery['order_status'][0], $orderStatusQuery['shipstatus_id']) !== false) {
	$_ENV["request.cwpage"]["currentNav"] = 'orders.php?status='.$orderQuery['order_status'][0];
} else {
	$_ENV["request.cwpage"]["currentNav"] = 'order.php';
}
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"]=1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"]=1;
// Set default form values for the form 
if (!(isset($_POST["order_status"]))) $_POST["order_status"] = (($orderQuery['totalRows']) ? $orderQuery['order_status'][0] : "" );
if (!(isset($_POST['order_ship_date']))) $_POST['order_ship_date'] = (($orderQuery['totalRows']) ? $orderQuery['order_ship_date'][0] : "" );
if (!(isset($_POST['order_return_date']))) $_POST['order_return_date'] = (($orderQuery['totalRows']) ? $orderQuery['order_return_date'][0] : "" );
if (!(isset($_POST['order_return_amount']))) $_POST['order_return_amount'] = (($orderQuery['totalRows']) ? $orderQuery['order_return_amount'][0] : "" );
if (!(isset($_POST['order_ship_tracking_id']))) $_POST['order_ship_tracking_id'] = (($orderQuery['totalRows']) ? $orderQuery['order_ship_tracking_id'][0] : "" );
if (!(isset($_POST['order_notes']))) $_POST['order_notes'] = (($orderQuery['totalRows']) ? $orderQuery['order_notes'][0] : "" );
//this code moved from inline as we have to redirect before page content is written
if(!($_GET['startDate'])) {
	CWpageMessage("alert","Invalid Date Range");
	header("Location: ".$_ENV["request.cw"]["thisPage"].'?useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
}			
// START OUTPUT ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
	<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"];?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
      <?php
	 
		include("cwadminapp/inc/cw-inc-admin-scripts.php");
		// PAGE JAVASCRIPT ?>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			// hide shipRow if status is not 'shipped' or higher
			if((jQuery('#order_status').find('option:selected').val() != <?php echo $_ENV["request.cwpage"]["shippedStatusCode"]; ?>) && (jQuery('#order_ship_date').val() == '' )){
			jQuery('tr.shipRow').hide();
			};
			// hide returnRow if status is not 'returned' or higher
			if((jQuery('#order_status').find('option:selected').val() != <?php echo $_ENV["request.cwpage"]["returnedStatusCode"]; ?>) && (jQuery('#order_return_date').val() == '' )){
			jQuery('tr.returnRow').hide();
			};
			// show/hide shipRow based on change of shipping value
			jQuery('#order_status').change(function(){
			 if (jQuery(this).find('option:selected').val() == <?php echo $_ENV["request.cwpage"]["shippedStatusCode"]; ?>){
				jQuery('tr.returnRow').hide();
				jQuery('tr.shipRow').show();
			 } else if (jQuery(this).find('option:selected').val() == <?php echo $_ENV["request.cwpage"]["returnedStatusCode"]; ?>){
				jQuery('tr.shipRow').hide();
				jQuery('tr.returnRow').show();
			 } else if(jQuery('#order_ship_date').val() == '' ) {
			jQuery('tr.shipRow').hide();
			 };
			});
		});
		</script>
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
    <?php 
		include('cwadminapp/inc/cw-inc-admin-nav.php');
				?>
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
	echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]).'</h1>';
}
if(strlen(trim($_ENV["request.cwpage"]["heading2"]))) {
	echo "<h2>".trim($_ENV["request.cwpage"]["heading2"]).'</h2>';
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
							<?php // Order Search Form ?>
	<?php
//Check for start date moved to head, as we cannot change locations after the page has started writing
include("cwadminapp/inc/cw-inc-search-order.php");   
	?>
						</div>
                        <?php
						// /END SEARCH 
						// /////// 
						// UPDATE ORDER 
						// /////// ?>
						<form name="OrderStatus" method="post" action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate CWobserve">
		<?php
if($orderQuery['totalRows']) {						
		?>						<table class="wider CWinfoTable">
									<tr class="headerRow">
											<th style="width:50%">Order Details</th>
											<th>Shipping Information</th>
									</tr>
									<tr class="dataRow">
										<td class="padTop">
											<p><strong>Order ID: <?php echo $_GET['order_id']; ?></strong></p>
											<p>Placed: <?php echo (($orderQuery['totalRows']) ? date($_ENV["application.cw"]["globalDateMask"],CWtime($orderQuery['order_date'][0])) : "" ) ?></p>
											<p><strong>Sold To: <a href="customer-details.php?customer_id=<?php echo $orderQuery['customer_id'][0]; ?>"> <?php echo $orderQuery['customer_first_name'][0]; ?> <?php echo $orderQuery['customer_last_name'][0]; ?>  </a></strong></p>
											<p>Email: <?php echo $orderQuery['customer_email'][0]; ?></p>
											<p>Customer ID: <?php echo $orderQuery['customer_id'][0]; ?></p>
										</td>
										<td class="padTop">
											<p><span class="infoLabel">Ship To: </span><?php echo $orderQuery['order_ship_name'][0]; ?></p>
											<?php
												if (strlen(trim($orderQuery['order_company'][0]))) {
														echo "<p>".$orderQuery['order_company'][0].",</p>";
												}
											?>
											
											<p><?php echo $orderQuery['order_address1'][0]; ?></p>

											<?php
												if (strlen(trim($orderQuery['order_address2'][0]))) {
														echo "<p>".$orderQuery['order_address2'][0].",</p>";
												}
											?>
																						
											<p>
											<?php echo $orderQuery['order_city'][0].', '. $orderQuery['order_state'][0]." ".$orderQuery['order_zip'][0].', '. $orderQuery['order_country'][0]; ?>
											</p>
											
											<?php
											if($orderQuery['order_ship_method_id'][0] != 0){
												echo "<p> Ship Via ".$orderQuery['ship_method_name'][0]."</p>";
											}
											?>
										</td>
										</tr>
								</table>
								<!-- TABBED LAYOUT -->
								<div id="CWadminTabWrapper">
									<!-- TAB LINKS -->
									<ul class="CWtabList">
										<?php // tab 1 ?>
										<li><a href="#tab1" title="Order Status">Order Status</a></li>
										<?php // tab 2 ?>
										<li><a href="#tab2" title="Order Details">Order Contents</a></li>
										<?php // tab 3 ?>
										<li><a href="#tab3" title="Payment Details">Payment Details</a></li>
									</ul>
									<?php // TAB CONTENT ?>
									<div class="CWtabBox">
										<?php // FIRST TAB (status) ?>
										<div id="tab1" class="tabDiv">
											<h3>Order Status</h3>
											<table class="CWformTable wide">
												<tr>
													<?php // Order Status ?>
													<th class="label" style="width:180px">
														Status:
													</th>
													<td>
														<select name="order_status" id="order_status">
<?php
	// If order status is NOT Shipped or Returned 
	if ($orderQuery["order_status"][0] != $_ENV["request.cwpage"]["shippedStatusCode"] &&
		$orderQuery["order_status"][0] != $_ENV["request.cwpage"]["returnedStatusCode"]) {
		for($i=0;$i <$orderStatusQuery['totalRows'];$i++ ) {
?>
															<option value="<?php  echo $orderStatusQuery['shipstatus_id'][$i]; ?>"<?php if ($_POST["order_status"] == $orderStatusQuery["shipstatus_id"][$i]) { ?> selected="selected"<?php } ?>><?php echo $orderStatusQuery['shipstatus_name'][$i];?></option>
<?php
		}
	} else if ($orderQuery["totalRows"] && $orderQuery["order_status"][0] == $_ENV["request.cwpage"]["returnedStatusCode"]) {
		// if order is returned, no status changes allowed 
?>
																<option value="<?php echo $_ENV["request.cwpage"]["returnedStatusCode"]; ?>" selected="selected">Returned</option>
<?php
	} else {
		// If order status is shipped 
?>
																<option value="<?php echo $_ENV["request.cwpage"]["shippedStatusCode"]; ?>" selected="selected">Shipped</option>
																<option value="<?php echo $_ENV["request.cwpage"]["returnedStatusCode"]; ?>">Returned</option>
<?php
	}
?>
														</select>
<?php
	// save order details 
	if($orderQuery['order_status'][0] != $_ENV["request.cwpage"]["cancelledStatusCode"] && $orderQuery['order_status'][0] != $_ENV["request.cwpage"]["returnedStatusCode"]) {
?>
															<input name="Update" type="submit" class="CWformButton" id="Update" value="Save Order">
<?php 
	}
	// view formatted order message contents 
?>
                
														&nbsp;&nbsp;<a class="smallPrint" href="order-email.php?order_id=<?php echo $_GET['order_id'];?>" rel="external">View Order Email</a>
														<?php // hidden field for order id ?>
														<input name="orderID" type="hidden" value="<?php echo $orderQuery["order_id"][0];?>">
													</td>
												</tr>
												<?php // Shipping method ?>
												<tr class="dataRow">
													<th class="label">Shipping Method: </th>
													<td><p><?php echo $orderQuery['ship_method_name'][0];?></p></td>
												</tr>
                                                <?php
												// 'tr.shipRow' is hidden for unshipped status types 
												// Ship date - required for status 'shipped' ?>
												<tr class="shipRow">
													<th class="label">
														Ship Date:
													</th>
													<td>
														<input name="order_ship_date" id="order_ship_date" class="date_input {required: function() {return (jQuery('select[name=order_status]').val() == '<?php echo $_ENV["request.cwpage"]["shippedStatusCode"]; ?>')}}" title="Ship Date is required for the selected status" type="text" size="12" value="<?php if ($_POST['order_ship_date'] && cartweaverStrtotime($_POST['order_ship_date']) !== false) { echo cartweaverScriptDate($_POST['order_ship_date']); } ?>">
													</td>
												</tr>
												<tr class="shipRow">
													<th class="label">Tracking ID: </th>
													<td>
<?php
	if($orderQuery['order_status'][0] != $_ENV["request.cwpage"]["cancelledStatusCode"]) {
?>
            		<input name="order_ship_tracking_id" type="text" id="order_ship_tracking_id" size="35" value="<?php echo  $orderQuery['order_ship_tracking_id'][0];?>">
<?php
	} else {
		echo $orderQuery['order_ship_tracking_id'][0];
	}
?>								</td>
												</tr>
												<tr class="shipRow">
													<th class="label">Actual Shipping Cost: </th>
													<td>
														<input name="order_actual_ship_charge" size="8" type="text" value="<?php echo $orderQuery['order_actual_ship_charge'][0];?>" onkeyup="extractNumeric(this,2,false)">
													</td>
												</tr>
												<tr class="returnRow">
													<th class="label">Return Date: </th>
													<td>
														<input name="order_return_date" id="order_return_date" class="date_input {required: function(){return (jQuery('select[name=order_status]').val() == '<?php echo $_ENV["request.cwpage"]["returnedStatusCode"]; ?>')}}" title="Return Date is required for the selected status" type="text" size="12" value="<?php if ($_POST['order_return_date'] && cartweaverStrtotime($_POST['order_return_date']) !== false) { echo cartweaverScriptDate($_POST['order_return_date']); } ?>">
													</td>
												</tr>
												<tr class="returnRow">
													<th class="label">Return Amount: </th>
													<td>
														<input name="order_return_amount" size="8" type="text" value="<?php echo cartweaverMoney($orderQuery["order_return_amount"][0]); ?>" onkeyup="extractNumeric(this,2,false)">
													</td>
												</tr>
<?php
	if(strlen(trim($orderQuery['order_comments'][0]))) {
?>	
            						<tr>
					<th class="label">Order Comments: </th>
						<td><?php echo $orderQuery['order_comments'][0];?></td>
													</tr>
<?php
	}
?>	
									<tr>
									<tr>
									<th class="label">Store Notes: </th>
											<td><textarea name="order_notes" cols="34" rows="10" id="order_notes"><?php echo $orderQuery['order_notes'][0];?></textarea></td>
													</tr>
												</tr>
											</table>
										</div>
										<?php // SECOND TAB (contents) ?>
										<div id="tab2" class="tabDiv">
											<h3>Order Contents</h3>
											<table id="tblOrderDetails" class="CWinfoTable wide">
												<tr class="headerRow">
													<th>Product Name</th>
													<th>Qty.</th>
													<th>Price</th>
<?php
	if($discountDisplayLineItem) {
?>										<th>Discount</th>
<?php
	}
	if($taxDisplayLineItem) {
?>										<th>Subtotal</th>
														<th>Tax</th>
<?php
	}
?>	
				<th>Total</th>
												</tr>
<?php 
	$rowCount=0;
	$lastOrderSKU = 'asdf';
	for($i=0;$i < $orderQuery['totalRows'];$i++) {
		if ($orderQuery['ordersku_sku'][$i] != $lastOrderSKU) {
			$lastOrderSKU = $orderQuery['ordersku_sku'][$i];
			// QUERY: get options for this order SKU (sku id) 
			$optionsQuery=CWquerySelectSkuOptions($orderQuery['ordersku_sku'][$i]);
			$rowCount++;
?>								<tr class="dataRow">
													<td>
														<a href="product-details.php?productid=<?php  echo $orderQuery['product_id'][$i];?>" class="columnLink"><?php echo $orderQuery['product_name'][$i];?></a> (<?php echo $orderQuery['sku_merchant_sku_id'][$i]?>)
<?php
			// show SKU options 
			for($ii=0;$ii < $optionsQuery['totalRows'];$ii++) {
?>											<br>
															<strong style="margin-left: 10px;"><?php echo $optionsQuery['optiontype_name'][$ii];?></strong>: <?php echo $optionsQuery['option_name'][$ii];?>
<?php 
			}
                        if ($orderQuery['ordersku_unique_id'][$i] != $orderQuery['ordersku_sku'][$i]) {
                            //get custom sku information
                            $newVarForList1 = explode('-',$orderQuery['ordersku_unique_id'][$i]);
                            $phraseID = $newVarForList1[count($newVarForList1) - 1];
                            $phraseText = CWgetCustomInfo($phraseID);
                            if ($phraseText) {
?>											<br>
															<strong style="margin-left: 10px;"><?php echo $orderQuery['product_custom_info_label'][$i];?></strong>: <?php echo $phraseText;?>
                                                                                                                        
<?php
                            }
                        }
?>								
													</td>
													<td style="text-align:center;"><?php echo $orderQuery['ordersku_quantity'][$i];?></td>
													<td style="text-align:right;"><?php echo cartweaverMoney($orderQuery['ordersku_unit_price'][$i]);?></td>
<?php
			if($discountDisplayLineItem) {
	?><td style="text-align:right;"><?php
				if($orderQuery['ordersku_discount_amount'][$i]) {
					echo cartweaverMoney($orderQuery['ordersku_discount_amount'][$i] * $orderQuery['ordersku_quantity'][$i]);
				}
	?></td><?php
			}
			if($taxDisplayLineItem) {
?>								<td style="text-align:right;"><?php echo cartweaverMoney($orderQuery['ordersku_unit_price'][$i]-($orderQuery['ordersku_discount_amount'][$i] * $orderQuery['ordersku_quantity'][$i])); ?></td>
    													<td style="text-align:right;"><?php  echo cartweaverMoney($orderQuery['ordersku_tax_rate'][$i]);?></td>
														<td style="text-align:right;"><?php echo cartweaverMoney($orderQuery['ordersku_unit_price'][$i] - $orderQuery['ordersku_discount_amount'][$i] * $orderQuery['ordersku_quantity'][$i] + $orderQuery['ordersku_tax_rate'][$i]); ?></td>
<?php
			} else {
?>
														<td style="text-align:right;"><?php echo cartweaverMoney(($orderQuery['ordersku_unit_price'][$i]-$orderQuery['ordersku_discount_amount'][$i]) * $orderQuery['ordersku_quantity'][$i]); ?></td>
<?php
			}
?>
												</tr>
<?php
		}
	}
	if ($rsCWSums["totalRows"]) {
?>
                                                <tr class="dataRow">
                                                    <th colspan="2" style="text-align:right;">Subtotal: </th>
                                                    <td style="text-align:right;"><?php  echo cartweaverMoney($rsCWSums["SubTotal"][0]);?></td>
<?php
		if($discountDisplayLineItem) {
?>
															<td style="text-align:right;"><?php if($rsCWSums["TotalDiscount"][0] !=0) { echo cartweaverMoney($rsCWSums["TotalDiscount"][0]); } ?></td>
<?php
		}
		if($taxDisplayLineItem) {
?>
															<td style="text-align:right;"><?php echo cartweaverMoney($rsCWSums["SubTotal"][0]-$rsCWSums["TotalDiscount"][0]);?></td>
															<td style="text-align:right;"><?php echo cartweaverMoney($rsCWSums["TotalTax"][0]);?></td>
															<td style="text-align:right;"><?php echo cartweaverMoney($rsCWSums["SubTotal"][0] + $rsCWSums["TotalTax"][0] - $rsCWSums["TotalDiscount"][0]);?></td>
<?php
		} else {
?>
															<td style="text-align:right;"><?php echo cartweaverMoney($orderQuery['order_total'][0] - ($orderQuery['order_shipping'][0] + $orderQuery['order_tax'][0])); ?></td>
<?php
		}
?>
                            						</tr>
<?php
		// global cart discounts 
		if ($orderQuery["order_discount_total"][0] > 0 && $orderQuery["order_discount_total"][0] > $rsCWSums["TotalDiscount"][0]) {
?>
														<tr class="dataRow">
															<th colspan="2" style="text-align:right;">Additional Discounts: </th>
															<td style="text-align:right;"></td>
<?php
			if ($discountDisplayLineItem) {
?>
																<td style="text-align:right;"><?php if ($rsCWSums["TotalDiscount"][0] != 0) { ?><em>- <?php echo cartweaverMoney($orderQuery["order_discount_total"][0]-$rsCWSums["TotalDiscount"][0]); ?></em><?php } ?></td>
<?php
			}
			if ($taxDisplayLineItem) {
?>
																<td style="text-align:right;"><?php if (!$discountDisplayLineItem) { ?><em>- <?php echo cartweaverMoney($orderQuery["order_discount_total"][0]-$rsCWSums["TotalDiscount"][0]); ?></em><?php } ?></td>
																<td style="text-align:right;">&nbsp;</td>
																<td style="text-align:right;"><?php echo cartweaverMoney($rsCWSums["SubTotal"][0]+$rsCWSums["TotalTax"][0]-$orderQuery["order_discount_total"][0]); ?></td>
<?php
			} else {
?>
																<td style="text-align:right;"><?php echo cartweaverMoney($orderQuery["order_total"][0]-($orderQuery["order_shipping"][0]+$orderQuery["order_tax"][0]) - $orderQuery["order_discount_total"][0]); ?></td>
<?php
			}
?>
														</tr>
<?php
		}
	}
	if($orderQuery['order_ship_method_id'][0] != 0) {
?>
													<tr class="dataRow">
														<th colspan="2" style="text-align:right;" valign="top"> Ship By: <?php echo $orderQuery['ship_method_name'][0];?></th>
														<td style="text-align:right;"><?php echo cartweaverMoney($orderQuery['order_shipping'][0] + $orderQuery['order_ship_discount_total'][0]); ?></td>
<?php
		// If showing line item discounts, show shipping discount in cell 
		if($discountDisplayLineItem) {
?>
                    									<td style="text-align:right;" valign="top">&nbsp;<?php if($orderQuery['order_ship_discount_total'][0] !=0) { ?><em>- <?php echo $orderQuery['order_ship_discount_total'][0]; ?></em><?php } ?></td>
<?php
		}
		// If showing line item discounts, show shipping taxes and subtotals in cells 
		if($taxDisplayLineItem) {
?>
                            							<td style="text-align:right;" valign="top"><?php echo cartweaverMoney($orderQuery['order_shipping'][0]); ?></td>
														<td style="text-align:right;" valign="top"><?php echo cartweaverMoney($orderQuery['order_shipping_tax'][0]); ?></td>
														<td style="text-align:right;" valign="top"><?php echo cartweaverMoney($orderQuery['order_shipping_tax'][0] + $orderQuery['order_shipping'][0]); ?></td>
<?php
		} else {
?>
														<td style="text-align:right;" valign="top"><?php echo cartweaverMoney($orderQuery['order_shipping'][0]); ?></td>
<?php
		}
?>
                            						</tr>
							
<?php		
	}
	if(!$taxDisplayLineItem) {
?>
													<tr class="dataRow">
														<th colspan="<?php echo $CartColumnCount + 3;?>" style="text-align:right;"><?php echo $_ENV["application.cw"]["taxSystemLabel"];?>: </th>
														<td style="text-align:right;"><?php echo cartweaverMoney($orderQuery['order_tax'][0]);?></td>
													</tr>
<?php
	}
	// Display ORDER TOTAL 
?>
												<tr class="dataRow">
													<th colspan="<?php echo $CartColumnCount +3;?>" style="text-align:right;">Order Total: </th>
													<td style="text-align:right;"><strong><?php echo cartweaverMoney($orderQuery['order_total'][0]);?></strong></td>
												</tr>
                                                
<?php
	// DISCOUNT DETAILS 
	if(isset($discountsQuery['totalRows']) && $discountsQuery['totalRows'] > 0) {
?>
													<tr class="dataRow">
														<th colspan="2" style="text-align:right;">Applied Discounts</th>
														<td colspan="<?php echo $CartColumnCount +2;?>"><?php
		// APPLIED DISCOUNTS 
		if (count($_ENV["request.cwpage"]["discountDescriptions"])) {
?>
                                                                                                                    <div class="CWcartDiscounts">
                                                                                                                    <p class="CWdiscountHeader">Discounts applied to this order:</p>
<?php
			// loop descriptions, get ID for linking 
			$loopCt = 0;
			foreach ($_ENV["request.cwpage"]["discountDescriptions"] as $key => $i) {
				$linkid = $discountsQuery["discount_usage_discount_id"][$loopCt];
				// remove line breaks, show on one line 
				$discText = str_replace("<br>", ": ", $i);
				if (substr($discText, 0, 1) != ":") {
					if ($linkid > 0) {
						?><p><a href="discount-details.php?discount_id=<?php echo $linkid; ?>"><?php echo $discText; ?></a></p><?
					} else {
						?><p><?php echo $discText; ?></p><?php
					}
				}
				$loopCt++;
			}
?>
                                                                                                                    </div>
<?php
                }
?>
                                                                                                                </td>
                                                                                                        </tr>
<?php
        }
?>
                                                                                        </table>
										</div>
										<?php // THIRD TAB (Payment Details) ?>
										<div id="tab3" class="tabDiv">
											<h3>Payment Details</h3>
<?php
	// if we have some payments 
	if($paymentsQuery['totalRows'] > 0) {
?>					<table id="tblOrderPayments" class="CWinfoTable wide">
												<tr class="headerRow">
													<th>Date/Time</th>
													<th>Amount</th>
													<th>Payment Method</th>
													<th>Type</th>
													<th>Status</th>
													<th>Transaction ID</th>
												</tr>
<?php
		// output payment data 
		for($iii=0;$iii < $paymentsQuery['totalRows'];$iii++) {
?>
												<tr class="dataRow">
													<td>
													<span class="dateStamp">
														<?php
                                                        echo cartweaverDate($paymentsQuery['payment_timestamp'][$iii]);
														?>&nbsp;&nbsp;<?php date("H:g", strtotime($paymentsQuery['payment_timestamp'][$iii])); ?>
													</span>
													</td>
													<td><?php echo cartweaverMoney($paymentsQuery['payment_amount'][0]);?></td>
													<td><?php echo $paymentsQuery['payment_method'][0]; ?></td>
													<td><?php echo $paymentsQuery['payment_type'][0];?></td>
													<td><?php echo $paymentsQuery['payment_status'][0];?></td>
													<td><?php echo $paymentsQuery['payment_trans_id'][0];?></td>
												</tr>
												<tr class="CWtransData">
													<td colspan="6">
													<p><strong>Transaction Data</strong></p>
													<textarea style="width:680px;height:110px;" readonly="readonly"><?php echo  $paymentsQuery['payment_trans_response'][0];?></textarea>
													</td>
												</tr>
<?php
		}
?></table>
<?php
		// if we don't have any payments 
	} else {
?>
												<p>&nbsp;</p>
												<p>&nbsp;</p>
												<p>No payments have been applied to this order.</p>
<?php 
	}
?>
										</div>
<?php
	// delete button 
	if($orderQuery['order_status'][0] != $_ENV["request.cwpage"]["shippedStatusCode"] && $orderQuery['order_status'][0] != $_ENV["request.cwpage"]["returnedStatusCode"]) {
?>
                                        <div class="CWformButtonWrap clear">
                                            <p>&nbsp;</p>
                                            <p>&nbsp;</p>
                                            <a href="order-details.php?deleteOrder=<?php echo $_GET['order_id'];?>&returnUrl=orders.php?useralert=Order Deleted" onClick="return confirm('Delete Order ID <?php echo $_GET['order_id'];?>?')" class="CWbuttonLink deleteButton">Delete Order</a>
                                        </div>
										<div class="clear"></div>
<?php
	}
?>						</div>
<?php
	// /END tab content 
	// /////// 
	// /END UPDATE ORDER 
	// /////// ?>
								</div>
<?php
	// /END tab wrapper 
}
// /END if orderQuery.recordCount ?>
						</form>
					</div>
					<!-- /end Page Content -->
					<div class="clear"></div>
				</div>
				<!-- /end CWinner -->
			</div>
			<!-- /end CWadminPage-->
<?php
// page end content / debug 
include("cwadminapp/inc/cw-inc-admin-page-end.php"); 
?><div class="clear"></div>
		</div>
		<!-- /end CWadminWrapper -->
	</body>
</html>