<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-search-order.php
File Date: 2012-02-01
Description: Search form for admin orders
==========================================================
*/
global $ordersQuery, $startRow_Results, $maxRows_Results, $endRow_Results, $TotalPages_Results;
// defaults for sorting 
if(!isset($_POST['sortby'] )) { $_POST['sortby'] = "order_date"; }
if(!isset($_POST['sortdir'])) { $_POST['sortdir'] = "DESC"; }
if(!isset($_GET['sortby'] )) { $_GET['sortby'] = $_POST['sortby']; }
if(!isset($_GET['sortdir'])) { $_GET['sortdir'] = $_POST['sortdir']; }
// defaults for form post (url params are in containing page)
// be sure dates are valid for locale 
$_ENV["request.cwpage"]["orderDateMask"] = $_ENV["application.cw"]["globalDateMask"];
if (isset($_GET["startDate"]) && cartweaverStrtotime($_GET["startDate"], $_ENV["request.cw"]["scriptDateMask"]) !== false) {
	if (!isset($_POST["startDate"])) {
		if ($_GET["startDate"]) {
			$_POST["startDate"] = cartweaverOrderDate($_GET["startDate"], $_ENV["request.cw"]["scriptDateMask"]);
		} else {
			$_POST["startDate"] = "";
		}
	}
} else {
	if (!isset($_POST["startDate"])) $_POST["startDate"] = cartweaverOrderDate("-3 months");
}
if (isset($_GET["endDate"]) && cartweaverStrtotime($_GET["endDate"], $_ENV["request.cw"]["scriptDateMask"]) !== false) {
	if (!isset($_POST["endDate"])) {
		if ($_GET["endDate"]) {
			$_POST["endDate"] = cartweaverOrderDate($_GET["endDate"], $_ENV["request.cw"]["scriptDateMask"]);
		} else {
			$_POST["endDate"] = "";
		}
	}
} else {
	if (!isset($_POST["endDate"])) $_POST["endDate"] = date($_ENV["request.cwpage"]["orderDateMask"]);
}
if(!isset($_POST['status'])) { $_POST['status'] = $_GET['status']; }
if(!isset($_POST['orderStr'])) { $_POST['orderStr'] = $_GET['orderStr']; }
if(!isset($_POST['custName'])) { $_POST['custName'] = $_GET['custName']; }
// use session if not defined in url 
if(isset($_SESSION["cw"]["ordersortby"]) && !isset($_GET['sortby'])) {
	$_POST['sortby'] = $_SESSION["cw"]["ordersortby"];
	$_GET['sortby'] = $_SESSION["cw"]["ordersortby"];
}
else if(isset($_GET['sortby'])) {
	$_POST['sortby'] = $_GET['sortby'];
}
if(isset($_SESSION["cw"]["ordersortdir"]) && !isset($_GET['sortdir'])) {
	$_POST['sortdir'] = $_SESSION["cw"]["ordersortdir"];
	$_GET['sortdir'] = $_SESSION["cw"]["ordersortdir"];
}
elseif(isset($_GET['sortdir'])) {
	$_POST['sortdir'] = $_GET['sortdir'];
}
// put new values in session for next time 
$_SESSION["cw"]["ordersortby"] = $_POST['sortby'];
$_SESSION["cw"]["ordersortdir"] = $_POST['sortdir'];
// QUERY: get all possible order status types 
$orderStatusQuery = CWquerySelectOrderStatus();
// QUERY: search orders (order search form vars) 
$ordersQuery = CWquerySelectOrders($_POST['status'],$_POST['startDate'],$_POST['endDate'],$_POST['orderStr'],$_POST['custName'],null,true);
// if only one record found, go to order details 
// if only one record found, go to order details 
if($ordersQuery['totalRows'] == 1 && $_ENV["request.cw"]["thisPage"] != 'order-details.php') {
	CWpageMessage("confirm","1 Order Found: details below");
	//cannot use a header redirect as it will fail in php, write a script tag and use document.location.href
	header("Location: order-details.php?order_id=".$ordersQuery['order_id'][0]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&search=Search");
}
/// // SHOW FORM // ?>
<form name="formOrderSearch" id="formOrderSearch" method="get" action="orders.php">
	<a id="showSearchFormLink" href="#">Search Orders</a>
	<span class="advanced pushRight"><strong>Search Orders&nbsp;&raquo;</strong></span>
	<label for="selectStartDate" style="padding-left:23px;">From:</label>
	<input name="startDate" type="text" class="date_input_past" value="<?php echo ($_POST["startDate"])? cartweaverScriptDate($_POST["startDate"]):''; ?>" size="10" id="selectStartDate">
	<label for="selectEndDate">&nbsp;&nbsp;To:</label>
	<input name="endDate" type="text" class="date_input_past" value="<?php echo ($_POST["endDate"])? cartweaverScriptDate($_POST["endDate"]):''; ?>" size="10" id="selectEndDate">
	<span class="advanced">
		<label for="status">&nbsp;&nbsp;Status:</label>
		<select name="status" id="status">
			<option value="0" <?php if($_POST['status'] == 0) {?>selected<?php } ?>>Any</option>
<?php
			for($i=0; $i<$orderStatusQuery['totalRows']; $i++) { ?>
            	<option value="<?php echo $orderStatusQuery['shipstatus_id'][$i]; ?>"<?php if($orderStatusQuery['shipstatus_id'][$i] == $_POST['status']) { echo ' selected="selected"'; } ?>><?php                
                echo $orderStatusQuery['shipstatus_name'][$i];
?></option>
<?php
			}
?>
		</select>
	</span>
	&nbsp;&nbsp;<input name="Search" type="submit" class="CWformButton" id="Search" value="Search" style="margin-bottom: 2px;">
	<div class="subForm advanced">
		<span class="pushRight"><?php if (count(explode('&',$_SERVER['QUERY_STRING'])) > 2) { ?><a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?search=Search">Reset Search</a><?php } else { ?>&nbsp;<?php } ?></span>
		<label for="orderStr">Order ID:</label>
		<input name="orderStr" id="orderStr" type="text" size="15" value="<?php echo $_POST['orderStr']; ?>">&nbsp;&nbsp;&nbsp;
		<label for="custName">Customer:</label>
		<input name="custName" id="custName" type="text" size="15" value="<?php echo $_POST['custName']; ?>">
<?php
		// rows per page 
		if($_ENV["application.cw"]["adminOrderPaging"]) { ?>
        	<label for="maxRows">&nbsp;Per Page:</label>
			<select name="maxRows" id="maxRows">
<?php
		for ($mn=10; $mn<=100; $mn += 10) {
?>
                	<option value="<?php echo $mn;?>"<?php if($mn == $_GET['maxrows']) {?> selected="selected"<?php } ?>><?php echo $mn;?></option>
<?php				
				}
?>
			</select>
<?php		
		}
?>        
	</div>
</form>
<?php
// Set Variables for recordset Paging 
$maxRows_Results = $_GET['maxrows'];
$startRow_Results = min((($_GET["pagenumresults"]-1) * $maxRows_Results) + 1,max($ordersQuery['totalRows'],1));
$endRow_Results = min(($startRow_Results + $maxRows_Results)-1,$ordersQuery['totalRows']);
$TotalPages_Results = ceil($ordersQuery['totalRows']/$maxRows_Results);
// SERIALIZE 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("pagenumresults,userconfirm,useralert")>
$pagingUrl = CWserializeURL($varsToKeep);
if ($_ENV["application.cw"]["adminOrderPaging"]) {
	$_ENV["request.cwpage"]["pagingLinks"] = "
<span class=\"pagingLinks\">
	Page ".$_GET['pagenumresults']." of ".$TotalPages_Results.
	"&nbsp;[Showing ". $ordersQuery['totalRows']." Order";
	if($ordersQuery['totalRows'] != 1)
		$_ENV["request.cwpage"]["pagingLinks"] .= "s";
	$_ENV["request.cwpage"]["pagingLinks"] .= "]<br>";
	if($TotalPages_Results > 1) {
		if($_GET['pagenumresults'] > 1) {
			$_ENV["request.cwpage"]["pagingLinks"].='<a href="'.$_ENV['request.cw']['thisPage'].'?pagenumresults=1&sortby='.$_GET['sortby'] .'">First</a> | <a href="'.$_ENV['request.cw']['thisPage'].'?pagenumresults='.($_GET['pagenumresults']-1).'&sortby='.$_GET['sortby'] . '">Previous</a>  |';
		} else {
			$_ENV["request.cwpage"]["pagingLinks"].='First | Previous |';
		}
		if($_GET['pagenumresults'] < $TotalPages_Results) {
			$_ENV["request.cwpage"]["pagingLinks"].='	<a href="'.$_ENV['request.cw']['thisPage'].'?pagenumresults=' .($_GET['pagenumresults']+1) . '&sortby='.$_GET['sortby'] . '">Next</a> | <a href="'.$_ENV['request.cw']['thisPage'].'?pagenumresults=' . $TotalPages_Results . '&sortby='.$_GET['sortby']  . '">Last</a>';
		} else {
			$_ENV["request.cwpage"]["pagingLinks"].= 'Next | Last';
		}
	}
	$_ENV["request.cwpage"]["pagingLinks"].='
</span>';
} else {
	$_ENV["request.cwpage"]["pagingLinks"] = "
<span class=\"pagingLinks\">
	[Showing ". $ordersQuery['totalRows'] . " Order";
	if($ordersQuery['totalRows'] != 1)
		$_ENV["request.cwpage"]["pagingLinks"] .= "s";
	$_ENV["request.cwpage"]["pagingLinks"] .= "]<br>
</span>";
}
?>