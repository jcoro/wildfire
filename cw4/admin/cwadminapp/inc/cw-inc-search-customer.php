<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-search-customer.php
File Date: 2012-02-01
Description: Search form for admin customer records
==========================================================
*/
global $customersQuery;
// defaults for sorting 
if(!(isset($_GET['sortby']))) { if (isset($_POST["sortby"])) { $_GET['sortby'] = $_POST['sortby']; } else { $_GET['sortby']=''; } }
if(!(isset($_GET['sortdir']))) { if (isset($_POST["sortdir"])) { $_GET['sortdir'] = $_POST['sortdir']; } else { $_GET['sortdir']=''; } }
// defaults for form post (url params are in containing page)
if(!(isset($_POST['custName']))) $_POST['custName']=$_GET['custName'];
if(!(isset($_POST['custID']))) $_POST['custID']=$_GET['custID'];
if(!(isset($_POST['custEmail']))) $_POST['custEmail']=$_GET['custEmail'];
if(!(isset($_POST['custAddr']))) $_POST['custAddr']=$_GET['custAddr'];
if(!(isset($_POST['orderStr']))) $_POST['orderStr']=$_GET['orderStr'];
// use session if not defined in url 
if (isset($_SESSION["cw"]["customersortby"]) && (!isset($_GET['sortby']) || $_GET['sortby'] == "")) {
	$_GET['sortby']=$_SESSION["cw"]["customersortby"];
}
if ($_GET['sortby'] == "") $_GET['sortby'] = "custName";
if (isset($_SESSION["cw"]["customersortdir"]) && (!isset($_GET['sortdir']) || $_GET['sortdir'] == "")) {
	$_GET['sortdir']=$_SESSION["cw"]["customersortdir"];
}
if ($_GET['sortdir'] == "") $_GET['sortdir'] = "custName";
// put new values in session for next time 
$_SESSION["cw"]["customersortby"]=$_GET['sortby'];
$_SESSION["cw"]["customersortdir"]=$_GET['sortdir'];
// QUERY: search customers (search form vars) 
$customersQuery = CWquerySelectCustomers(
	$_POST['custName'],
	$_POST['custID'],
	$_POST['custEmail'],
	$_POST['custAddr'],
	$_POST['orderStr'],
	null,
	true
);
// if only one record found, go to customer details 
if($customersQuery['totalRows']  == 1 && $_ENV["request.cw"]["thisPage"] != 'customer-details.php') {
	CWpageMessage("confirm","1 Customer Found: details below");
	header("Location: customer-details.php?customer_id=".$customersQuery["customer_id"][0].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&search=Search");
	exit;
}
// // SHOW FORM // ?>
<form name="formCustomerSearch" id="formCustomerSearch" method="get" action="customers.php">
	<a id="showSearchFormLink" href="#">Search Customers</a>
	<span class="advanced pushRight"><strong>Search Customers&nbsp;&raquo;</strong></span>
	<label for="searchCustName" style="padding-left:20px;">Name:</label>
	<input name="custName" type="text" value="<?php echo $_POST['custName'];?>" size="13" id="searchCustName">
	<label for="custID">&nbsp;&nbsp;ID:</label>
			
            <input name="custID" id="custID" type="text" value="<?php echo $_POST['custID'];?>" size="12" id="searchCustID" />
	<span class="advanced">
		<label for="custEmail">&nbsp;&nbsp;Email:</label>
		<input name="custEmail" id="custEmail" type="text" value="<?php echo $_POST['custEmail'];?>" size="12" id="searchCustEmail" />
	</span>
	&nbsp;&nbsp;<input name="Search" type="submit" class="CWformButton" id="Search" value="Search" style="margin-bottom: 2px;">
	<div class="subForm advanced">
		<span class="pushRight"><?php $newVarForList1=explode('&',$_SERVER['QUERY_STRING']); if(count($newVarForList1) > 2) {?><a href="<?php echo $_ENV["request.cw"]["thisPage"];?>?search=Search<?php if(isset($_GET['customer_id'])) {?>&customer_id=<?php echo $_GET['customer_id'];}?>">Reset Search</a><?php } else echo '&nbsp;';?></span>
		<label for="searchCustAddr" style="padding-left:23px;">Address:</label>
		<input name="custAddr" id="searchCustAddr" type="text" size="12" value="<?php echo $_POST['custAddr']; ?>">&nbsp;&nbsp;&nbsp;
		<label for="searchOrderStr">Order ID:</label>
		<input name="orderStr" id="searchOrderStr" type="text" size="12" value="<?php if(!($_POST['orderStr'] =='%')) {?><?php echo $_POST['orderStr'];}?>">&nbsp;&nbsp;&nbsp;
       <?php
		// rows per page 
	   	if($_ENV["application.cw"]["adminCustomerPaging"]) { 
		?>	
			<label for="maxRows">&nbsp;Per Page:</label>
			<select name="maxRows" id="maxRows">
<?php
		for ($mn=10; $mn<=100; $mn += 10) {
?>
					<option value="<?php echo $mn;?>"<?php if($mn == $_GET['maxrows']) {?> selected="selected"<?php } ?>><?php echo $mn;?></option>
				<?php } ?>
			</select>
        <?php } ?>
	</div>
</form>
<?php
// Set Variables for recordset Paging  
$maxRows_Results= $_GET['maxrows'];
$startRow_Results = min((($_GET['pagenumresults']-1) * $maxRows_Results)+1,max($customersQuery['totalRows'],1));
$endRow_Results = min(($startRow_Results+$maxRows_Results)-1,$customersQuery['totalRows']);
$TotalPages_Results=ceil($customersQuery['totalRows']/$maxRows_Results);
// SERIALIZE 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("pagenumresults","userconfirm","useralert");
$PagingURL = CWserializeURL($varsToKeep);
if ($_ENV["application.cw"]["adminCustomerPaging"]) {
	$_ENV["request.cwpage"]["pagingLinks"]='<span class="pagingLinks">Page '.$_GET['pagenumresults'] .' of '.$TotalPages_Results.'&nbsp;[Showing '.$customersQuery['totalRows']. ' Customer';
	if($customersQuery['totalRows'] != 1) $_ENV["request.cwpage"]["pagingLinks"] .='s';
	$_ENV["request.cwpage"]["pagingLinks"] .=']<br>';
	if($TotalPages_Results > 1) {
		if($_GET['pagenumresults'] > 1) {
			$_ENV["request.cwpage"]["pagingLinks"] .= '<a href="'.$_ENV["request.cw"]["thisPage"].'?pagenumresults=1&'. $PagingURL .'">First</a> | <a href="'.$_ENV["request.cw"]["thisPage"].'?pagenumresults=' .($_GET['pagenumresults']-1).'&'.$PagingURL.'">Previous</a>  |';
		} else {
			$_ENV["request.cwpage"]["pagingLinks"] .= 'First | Previous |';
		}
		if($_GET['pagenumresults']  < $TotalPages_Results) {
			$_ENV["request.cwpage"]["pagingLinks"] .= '<a href="'.$_ENV["request.cw"]["thisPage"].'?pagenumresults=' . ($_GET['pagenumresults']+1) . '&' . $PagingURL . '">Next</a> | <a href="'.$_ENV["request.cw"]["thisPage"].'?pagenumresults=' . $TotalPages_Results . '&' . $PagingURL .'">Last</a>
	';
		} else {
			$_ENV["request.cwpage"]["pagingLinks"] .='Next | Last';
		}
	}
	$_ENV["request.cwpage"]["pagingLinks"].='</span>';
} else {
	$_ENV["request.cwpage"]["pagingLinks"]='<span class="pagingLinks">[Showing ' . $customersQuery['totalRows']. ' Customer';
	if($customersQuery['totalRows'] != 1) $_ENV["request.cwpage"]["pagingLinks"] .='s';
	$_ENV["request.cwpage"]["pagingLinks"] .=']<br></span>';
}
?>