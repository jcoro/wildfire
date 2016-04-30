<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: discounts.php
File Date: 2012-02-01
Description:
Description: Displays list of active/archived discounts
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("merchant,developer");
// PAGE PARAMS 
// default values for sort / active or archived
if (!isset($_GET["sortby"])) $_GET["sortby"] = "discount_merchant_id";
if (!isset($_GET["sortdir"])) $_GET["sortdir"] = "asc";
if (!isset($_GET["view"])) $_GET["view"] = "active";
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("userconfirm,useralert,sortby,sortdir,archiveid,reactivateid");
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// ACTIVE VS ARCHIVED 
if (strpos($_GET["view"], 'arch') !== false) {
	$_ENV["request.cwpage"]["viewType"] = 'Archived';
	$_ENV["request.subHead"]["viewType"] = 'Archived discounts are not available for customer use';
} else {
	$_ENV["request.cwpage"]["viewType"] = 'Active';
	$_ENV["request.subHead"]["viewType"] = 'Manage active discounts or add a new discount';
}
// /////// 
// ARCHIVE DISCOUNT 
// /////// 
if (isset($_GET["archiveid"]) && $_GET["archiveid"] > 0) {
	// QUERY: archive the product (product id) 
	$temp = CWqueryArchiveDiscount($_GET["archiveid"]);
	$confirmMsg = 'Discount Archived: Use Archived Discounts menu link to view or reactivate';
	CWpageMessage("confirm",$confirmMsg);
}
// /////// 
// /END ARCHIVE DISCOUNT 
// /////// 
// /////// 
// ACTIVATE DISCOUNT 
// /////// 
if (isset($_GET["reactivateid"]) && $_GET["reactivateid"] > 0) {
	// QUERY: reactivate product (product ID) 
	$temp = CWqueryReactivateDiscount($_GET["reactivateid"]);
	$_ENV["request.cwpage"]["userconfirmText"] = 'Discount Reactivated: <a href="discount-details.php?discount_id='.$_GET["reactivateid"].'">View Discount Details</a>';
	CWpageMessage("confirm", $_ENV["request.cwpage"]["userconfirmText"]);
}
// /////// 
// /END ACTIVATE DISCOUNT 
// /////// 
// QUERY: get all discounts 
if (strpos($_ENV["request.cwpage"]["viewType"], 'Arch') !== false) {
	$_ENV["request.cwpage"]["discountsArchived"] = 1;
	$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"] . '?view=arch';
	$_ENV["request.cwpage"]["heading2"] = 'Manage archived discounts <span class="smallPrint"><a href="'.$_ENV["request.cw"]["thisPage"].'">View active</a></span>';
} else {
	$_ENV["request.cwpage"]["discountsArchived"] = 0;
	$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"];
	$_ENV["request.cwpage"]["heading2"] = 'Manage active discounts';
}
$discountsQuery = CWquerySelectStatusDiscounts($_ENV["request.cwpage"]["discountsArchived"],true);
// PAGE SETTINGS 
// Page Browser Window Title <title> 
$_ENV["request.cwpage"]["title"] = "Manage Discounts";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Manage Discounts";
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
<?php include("cwadminapp/inc/cw-inc-admin-scripts.php"); ?>
	</head>
<?php
// body gets a class to match the filename 
$page = $_ENV["request.cw"]["thisPage"];
$pagenew = explode('.',$page);
$pagefirst = $pagenew[0]; ?>
	<body <?php echo 'class="'.$pagefirst.'"'; ?>>
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
if (strlen(trim($_ENV["request.cwpage"]["heading1"]))) echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]).'</h1>';
if (strlen(trim($_ENV["request.cwpage"]["heading2"]))) echo '<h2>'.trim($_ENV["request.cwpage"]["heading2"]).'</h2>';
?>
					<!-- Admin Alert - message shown to user -->
					<?php include("cwadminapp/inc/cw-inc-admin-alerts.php"); ?>
					<!-- Page Content Area -->
					<div id="CWadminContent">
					<!-- //// PAGE CONTENT ////  -->
<?php
// PRODUCTS TABLE 
// if no records found, show message 
if (!$discountsQuery["totalRows"]) {
?>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p><strong>No discounts found.</strong> <?php if ($_ENV["request.cwpage"]["viewType"] == 'Active') { ?><a href="discount-details.php">Add a new discount</a><?php } else { ?><a href="discounts.php">View active discounts</a><?php } ?></p>
<?php
} else {
	// if we have some records to show ?>
							<table class="CWsort CWstripe" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
								<thead>
								<tr class="sortRow">
									<th width="20">Edit</th>
									<th class="discount_name">Discount Name</th>
									<th class="discount_merchant_id">Reference ID</th>
									<th class="discount_promotional_code">Promo Code</th>
									<th class="discount_type">Applies To</th>
									<th class="discount_association_method">Association</th>
									<th class="discount_start_date">Start Date</th>
									<th class="discount_end_date">End Date</th>
									<?php // archive ?>
									<th class="noSort" width="50"><?php if ($_ENV["request.cwpage"]["viewType"] != 'Archived') { ?>Archive<?php } else { ?>Activate<?php } ?></th>
								</tr>
								</thead>
								<tbody>
<?php
	// OUTPUT THE DISCOUNTS 
	for ($n=0; $n<$discountsQuery["totalRows"]; $n++) {
?>	
								<tr>
									<?php // edit link ?>
									<td style="text-align:center;"><a href="discount-details.php?discount_id=<?php echo $discountsQuery["discount_id"][$n]; ?>" title="Edit Discount Details: <?php echo CWstringFormat($discountsQuery["discount_name"][$n]); ?>" class="columnLink"><img src="img/cw-edit.gif" alt="Edit <?php echo $discountsQuery["discount_name"][$n]; ?>" width="15" height="15" border="0"></a></td>
									<?php // discount name (linked) ?>
									<td>
										<strong><a class="discountLink" href="discount-details.php?discount_id=<?php echo $discountsQuery["discount_id"][$n]; ?>" title="Edit Discount Details: <?php echo CWstringFormat($discountsQuery["discount_name"][$n]); ?>"><?php echo $discountsQuery["discount_name"][$n]; ?></a></strong>
									</td>
									<?php // reference id ?>
									<td><?php echo $discountsQuery["discount_merchant_id"][$n]; ?></td>
									<?php // promo code ?>
									<td><?php echo $discountsQuery["discount_promotional_code"][$n]; ?></td>
									<?php // applies to ?>
									<td><?php echo $discountsQuery["discount_type_description"][$n]; ?></td>
									<?php // association ?>
									<td><?php if ($discountsQuery["discount_global"][$n] == 1) { ?>all items<?php } else { echo $discountsQuery["discount_association_method"][$n]; } ?></td>
									<?php // start date ?>
									<td><?php echo cartweaverDate($discountsQuery["discount_start_date"][$n]); ?></td>
									<?php // end date ?>
									<td<?php if ($discountsQuery["discount_end_date"][$n] && strtotime($discountsQuery["discount_end_date"][$n]) !== false && strtotime($discountsQuery["discount_end_date"][$n]) < CWtime()) { ?> class="warning"<?php } ?>><?php echo cartweaverDate($discountsQuery["discount_end_date"][$n]); ?></td>
<?php
		// ARCHIVE / ACTIVATE 
		// keep same page when archiving 
		// get the vars to keep by omitting the ones we don't want repeated 
		$varsToPass = CWremoveUrlVars("reactivateid,archiveid,userconfirm,useralert");
		// set up the base url 
		$passQS = CWserializeURL($varsToPass);
		// archive / activate button ?>
									<td style="text-align:center;">
										<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?<?php if ($_ENV["request.cwpage"]["viewType"] != 'Archived') { ?>archiveid<?php } else { ?>reactivateid<?php } ?>=<?php echo $discountsQuery["discount_id"][$n]; ?>&<?php echo $passQS; ?>" class="columnLink" title="<?php if ($_ENV["request.cwpage"]["viewType"] != 'Archived') { ?>Archive<?php } else { ?>Reactivate<?php } ?> Discount: <?php echo CWstringFormat($discountsQuery["discount_name"][$n]); ?>"><img src="img/<?php if ($_ENV["request.cwpage"]["viewType"] != 'Archived') { ?>cw-archive<?php } else { ?>cw-archive-restore<?php } ?>.gif" alt="<?php if ($_ENV["request.cwpage"]["viewType"] != 'Archived') { ?>Archive<?php } else { ?>Reactivate<?php } ?>" border="0"></a>
									</td>
								</tr>
<?php
	}
?>
								</tbody>
							</table>
<?php
}
// /END PRODUCTS TABLE ?>
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