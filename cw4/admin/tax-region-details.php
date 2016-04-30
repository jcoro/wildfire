<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: tax-region-details.php
File Date: 2012-02-01
Description: Manage Tax Region Details
==========================================================
NOTE: tax_region_tax_id has no visible input, not used for default CW display,
but is available if a specific site modification requires it.
The taxID on invoices can be controlled via global Tax Settings
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
if(!isset($_GET['tax_region_id'])) $_GET['tax_region_id'] = 0;
if(!isset($_ENV["request.cwpage"]["currentRecord"] )) $_ENV["request.cwpage"]["currentRecord"] = $_GET['tax_region_id'];
if(!isset($_GET['sortby'] )) $_GET['sortby'] = "tax_group_name";
if(!isset($_GET['sortdir'] )) $_GET['sortdir'] = "asc";
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("view,userconfirm,useralert,clickadd,sortby,sortdir");
// create the base url for sorting out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_SERVER['SCRIPT_NAME']);
// QUERY: Get tax region (active/archived, region ID)
$taxRegionQuery = CWquerySelectTaxRegions($_ENV["request.cwpage"]["currentRecord"]);
$_ENV["request.cwpage"]["currentGroup"] = $taxRegionQuery['tax_group_id'][0];
$_ENV["request.cwpage"]["regionName"] = $taxRegionQuery['tax_region_label'][0];
// set up form params 
if($taxRegionQuery['totalRows']) {
	// Set default form variables 
	if(!isset($_POST['tax_region_label']) || $_POST['tax_region_label'] == '') $_POST['tax_region_label'] = $taxRegionQuery['tax_region_label'][0];
	if(!isset($_POST['tax_region_tax_id']) || $_POST['tax_region_tax_id'] == '') $_POST['tax_region_tax_id'] = $taxRegionQuery['tax_region_tax_id'][0];
	if(!isset($_POST['tax_region_show_id']) || $_POST['tax_region_show_id'] == '') $_POST['tax_region_show_id'] = $taxRegionQuery['tax_region_show_id'][0];
	if(strtolower($taxRegionQuery['tax_region_ship_tax_method'][0]) == "tax group") {
		if(!isset($_POST['ShippingTax']) || $_POST['ShippingTax'] == '') $_POST['ShippingTax'] = $taxRegionQuery['tax_region_ship_tax_group_id'][0];
	} else {
		if(!isset($_POST['ShippingTax']) || $_POST['ShippingTax'] == '') $_POST['ShippingTax'] = $taxRegionQuery['tax_region_ship_tax_method'][0];
	}
}
// QUERY: Get tax groups (id (none))
$taxGroupsQueryAll = CWquerySelectTaxGroupDetails(0);
// QUERY: Get tax rates by region (region ID)
$taxRatesQuery = CWquerySelectTaxGroupRates($_ENV["request.cwpage"]["currentRecord"],true);
// set up the existing groups 
if (isset($taxRatesQuery['tax_group_id']) && is_array($taxRatesQuery['tax_group_id'])) $_ENV["request.cwpage"]["currentGroups"] = implode(',',$taxRatesQuery['tax_group_id']);
else $_ENV["request.cwpage"]["currentGroups"] = "";
if(!strlen(trim($_ENV["request.cwpage"]["currentGroups"]))) {
	$_ENV["request.cwpage"]["currentGroups"] = 0;
}
// QUERY: Get available tax groups (id (none), list to omit)
$taxGroupsQuery = CWquerySelectTaxGroupDetails(0,$_ENV["request.cwpage"]["currentGroups"]);
// /////// 
// UPDATE TAX REGION 
// /////// 
if((isset($_POST['tax_region_label'])) && strlen($_POST['tax_region_label']) && isset($_POST["taxid"])) {
	if(is_numeric($_POST['ShippingTax'])) {
		// Shipping based on tax group 
		$insertTaxMethod = "Tax Group";
		$insertTaxGroup = $_POST['ShippingTax'];
	} else {
		$insertTaxMethod = $_POST['ShippingTax'];
		$insertTaxGroup = 0;
	}
	if(!isset($_POST['tax_region_show_id']) || $_POST['tax_region_show_id'] == '') $_POST['tax_region_show_id'] = 0;
	// QUERY: update tax region (region ID, tax label, tax ID, show tax ID y/n, tax method, tax group ID) 
	$updateTaxRegionID = CWqueryUpdateTaxRegion(
						$_ENV["request.cwpage"]["currentRecord"],
						$_POST['tax_region_label'],
						$_POST['tax_region_tax_id'],
						$_POST['tax_region_show_id'],
						$insertTaxMethod,
						$insertTaxGroup);
	if(substr($updateTaxRegionID,0,2) != '0-') {
		// update complete: return to page showing message 
		CWpageMessage("confirm","".$_ENV["application.cw"]["taxSystemLabel"]." Region Name Saved");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&currentRecord=".$_ENV["request.cwpage"]["currentRecord"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&sortby=".$_GET['sortby']."&sortdir=".$_GET['sortdir']."");
		exit;
	} else {
		// if we have an insert error, show message, do not insert 
		$updttaxregid = explode('-', $updateTaxRegionID);
		$updttaxregid_last = $updttaxregid[count($updttaxregid) - 1];
		CWpageMessage("alert",$updttaxregid_last);
	}
	// /END duplicate/error check 
}
// /////// 
// /END UPDATE TAX REGION 
// /////// 
// /////// 
// DELETE TAX REGION 
// /////// 
if((isset($_GET['deleteR']))) {
	if($_GET["returnUrl"] == '') { $_GET["returnUrl"] = "tax-regions.php?useralert=".CWurlSafe('Region Deleted').""; }
	// QUERY: delete customer record (id from url)
	$deleteRegion = CWqueryDeleteTaxRegion($_GET['deleteR']);
	header("Location: ".$_GET["returnUrl"]);
	exit;
}
// /////// 
// /END DELETE TAX REGION 
// /////// 
// /////// 
// INSERT TAX RATE 
// /////// 
if((isset($_POST['tax_group_id'])) && is_numeric($_POST['tax_group_id'])) {
	// QUERY: insert new tax rage (region ID, group ID, tax rate)  
	$insertTaxRateID = CWqueryInsertTaxRate($_ENV["request.cwpage"]["currentRecord"],$_POST['tax_group_id'],$_POST['taxRate']);
	// insert complete: return to page showing message 
	CWpageMessage("confirm","1 ".$_ENV["application.cw"]["taxSystemLabel"]." Rate Added");
	header("Location: ".$_ENV["request.cwpage"]["baseURL"]."currentRecord=".$_ENV["request.cwpage"]["currentRecord"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&sortby=".$_GET['sortby']."&sortdir=".$_GET['sortdir']."");
}
// /////// 
// /END INSERT TAX RATE 
// /////// 
// /////// 
// UPDATE / DELETE TAX RATES 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['tax_rate_id0'])) {
	if(!isset($_POST['deleteRecord'])) $_POST['deleteRecord'] = array();
	$loopCt = 0;
	$updateCt = 0;
	$deleteCt = 0;
	// loop record ids, handle each one as needed 
	foreach ($_POST["recordIDlist"] as $key => $ID) {
		// DELETE RECORDS 
		// if the record ID is marked for deletion 
		if (in_array($ID, $_POST['deleteRecord'])) {
			// QUERY: delete record (record id) 
			$deleteRecord = CWqueryDeleteTaxRate($ID);
			$deleteCt++;
			// if not deleting, update 
		} else {
			// UPDATE RECORDS 
			// param for checkbox values 
			if(!isset($_POST['TaxRate'.$loopCt])) $_POST['TaxRate'.$loopCt] = 0;
			// verify numeric tax region ID 
			if(!is_numeric($_POST['TaxRate'.$loopCt])) $_POST['TaxRate'.$loopCt] = 0;
			// QUERY: update record (ID, percentage ) 
			$updateRecord = CWqueryUpdateTaxRate($_POST['tax_rate_id'.$loopCt],$_POST['TaxRate'.$loopCt]);
			$updateCt++;
			// /END delete vs. update 
		}
		$loopCt++;
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars("userconfirm,useralert");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	CWpageMessage("confirm","Changes Saved");
	// save alert text 
	$_ENV["request.cwpage"]["userAlertText"] = '';
	if($deleteCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"].= $deleteCt." Record";
		if($deleteCt > 1) $_ENV["request.cwpage"]["userAlertText"].= "s";
		$_ENV["request.cwpage"]["userAlertText"].= " Deleted";
	}
	CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
	// return to page as submitted, clearing form scope 
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_ENV["request.cwpage"]["baseURL"]."currentRecord=".$_ENV["request.cwpage"]["currentRecord"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."");
	exit;
}
// /////// 
// /END UPDATE / DELETE TAX RATES 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Manage ".$_ENV["application.cw"]["taxSystemLabel"]."  Region";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Admin ".$_ENV["application.cw"]["taxSystemLabel"]." Region ".$_ENV["request.cwpage"]["regionName"]."";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Assign ".$_ENV["application.cw"]["taxSystemLabel"]." Regions and manage ".$_ENV["application.cw"]["taxSystemLabel"]." Rates";
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = "tax-regions.php";
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
<?php
		include("cwadminapp/inc/cw-inc-admin-scripts.php");
?>        
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
<?php 				include("cwadminapp/inc/cw-inc-admin-nav.php");
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
<?php
						// if a valid record is not found 
						if($taxRegionQuery['totalRows'] != 1) { ?>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>Invalid <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> region id. Please return to the <a href="tax-regions.php"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Region Listing</a> and choose a valid <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> region.</p>
<?php
						} else {
                        	// /////// 
							// UPDATE TAX REGION 
							// /////// 
							// FORM ?>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate CWobserve" name="updateTaxRegionForm" id="updateTaxRegionForm" method="post">
								<p>&nbsp;</p>
								<h3>Edit <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> Region Details</h3>
								<table class="CWinfoTable CWformTable">
									<tbody>
									<tr>
										<th class="label"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Label</th>
										<td><input name="tax_region_label" class="required" title="Tax Label is required" type="text" id="tax_region_label" size="30" value="<?php echo $_POST['tax_region_label']; ?>" onBlur="checkValue(this)"></td>
									</tr>
									<tr>
										<th class="label">Shipping <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Method</th>
										<td>
											<select name="ShippingTax" id="ShippingTax">
												<option value="No Tax"<?php if(strtolower($_POST['ShippingTax']) == "no tax") {?> selected="selected"<?php } ?>>No <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> on shipping</option>
												<option value="Highest Item Taxed" <?php if(strtolower($_POST['ShippingTax']) == "highest item taxed") { ?> selected="selected"<?php } ?>>Use Highest <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate applied to any item in the order</option>
												<optgroup label="Based on a <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group"> 
<?php
												for($i=0; $i<$taxGroupsQueryAll['totalRows']; $i++) { ?>
                                                	<option value="<?php echo $taxGroupsQueryAll['tax_group_id'][$i]; ?>"<?php if($_POST['ShippingTax'] == $taxGroupsQueryAll['tax_group_id'][$i]) {?> selected="selected"<?php } ?>><?php echo $taxGroupsQueryAll['tax_group_name'][$i]; ?></option>
<?php													
												}
?>                                                
                                                </optgroup>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="2" style="text-align:center">
											<?php // submit button ?>
											<input name="SubmitAdd" type="submit" class="CWformButton" id="SubmitAdd" value="Save Changes">
                                            <?php // hidden taxid - can be turned into a live input if needed ?>
											<input type="hidden" value="<?php echo $_POST["tax_region_tax_id"]; ?>" name="taxid">
<?php
										// delete link
										if($taxRatesQuery['totalRows'] == 0) { ?>
                                        	<a class="CWbuttonLink deleteButton" onClick="return confirm('Delete Tax Region <?php echo cwStringFormat($_POST['tax_region_label']); ?>?')" href="tax-region-details.php?deleteR=<?php echo $_GET['tax_region_id']; ?>&returnUrl=<?php echo urlencode('tax-regions.php?userconfirm=Region Deleted'); ?>">Delete Region</a>
<?php										
										} else { ?>
                                        	<p><br>(Delete all tax rates below before deleting this region)</p>
<?php											
										}
?>                                            
										</td>
									</tr>
									</tbody>
								</table>
							</form>
<?php
							// /////// 
							// /END UPDATE TAX REGION 
							// /////// 
							// /////// 
							// ADD NEW TAX RATE 
							// /////// 
							// FORM ?>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" name="addNewForm" id="addNewForm" method="post">
                                <p>&nbsp;</p>
								<h3>Add New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate</h3>
<?php
								// verify tax regions exist 
								if(!$taxGroupsQuery['totalRows']) { ?>
                                	<p>&nbsp;</p>
									<p>Create at least one active <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group to add a new <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate</p>
<?php									
								} else { ?>
                                	<table class="CWinfoTable">
										<thead>
										<tr>
											<th>Available <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Groups</th>
											<th><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate</th>
										</tr>
										</thead>
										<tbody>
										<tr>
											<?php // tax group selection ?>
											<td>
												<select name="tax_group_id">
<?php
													for($i=0; $i<$taxGroupsQuery['totalRows']; $i++) { ?>
                                                    	<option value="<?php echo $taxGroupsQuery['tax_group_id'][$i]; ?>"><?php echo $taxGroupsQuery['tax_group_name'][$i]; ?></option>
<?php													
													}
?>                                                
												</select>
												<br>
												<input name="SubmitAddTaxRate" type="submit" class="submitButton" id="SubmitAddTaxRegion" value="Save New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate" onBlur="checkValue(this)">
											</td>
											<?php // rate ?>
											<td><input name="taxRate" type="text" id="taxRate" size="6" maxlength="10" value="0.00" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">%</td>
										</tr>
										</tbody>
									</table>
									<p><a href="tax-groups.php">Add New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group</a></p>
									<p>&nbsp;</p>
<?php								
								}
							// / end verify tax regions exist ?>
					</form>
<?php
					// /////// 
					// /END ADD NEW TAX RATE
					// /////// 
					// /////// 
					// UPDATE TAX RATES 
					// /////// ?>
					<p>&nbsp;</p>
					<h3>Active <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rates</h3>
<?php
					// check for existing records 
					if($taxRatesQuery['totalRows'] == 0) { ?>
                    	<p>&nbsp;</p>
						<p>There are currently no <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> rates defined for this <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> region</p>
<?php
					} else {
						// if existing records found ?>
                    	<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" name="recordForm" id="recordForm" method="post" class="CWobserve">
							<table class="CWsort CWstripe CWinfoTable wide" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
								<thead>
								<tr class="sortRow">
									<th class="tax_group_name">Name</th>
									<th class="tax_rate_percentage" style="text-align:center"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate </th>
									<th width="85" style="text-align:center" class="noSort">
										<input type="checkbox" class="checkAll" name="checkAllDelete" rel="checkAllDel">Delete
									</th>
								</tr>
								</thead>
								<tbody>
<?php
								for($i=0; $i<$taxRatesQuery['totalRows']; $i++) { ?>
                                	<tr>
									<?php // name ?>
									<td>
										<a href="tax-group-details.php?tax_group_id=<?php echo $taxRatesQuery['tax_group_id'][$i]; ?>" class="detailsLink" title="Manage <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group details">
<?php
										echo $taxRatesQuery['tax_group_name'][$i];
?>                                        
										</a>
									</td>
									<?php // rate ?>
									<td>
										<input name="TaxRate<?php echo $i; ?>" type="text" id="TaxRate<?php echo $i; ?>" value="<?php echo $taxRatesQuery['tax_rate_percentage'][$i]; ?>" size="5" maxlength="6" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">
										%
									</td>
									<?php // delete ?>
									<td style="text-align:center;">
										<input name="deleteRecord[<?php echo $i; ?>]" type="checkbox" class="formCheckbox checkAllDel" value="<?php echo $taxRatesQuery['tax_rate_id'][$i]; ?>">
										<input type="hidden" name="tax_rate_id<?php echo $i; ?>" value="<?php echo $taxRatesQuery['tax_rate_id'][$i]; ?>">
										<input name="recordIDlist[<?php echo $i;?>]" type="hidden" value="<?php echo $taxRatesQuery['tax_rate_id'][$i]; ?>">
									</td>
								</tr>
<?php
								}
?>                                
								</tbody>
							</table>
							<input name="SubmitUpdate" type="submit" class="submitButton" id="UpdateTaxRates" value="Save Changes">
							<input type="hidden" value="<?php echo $taxRatesQuery['totalRows']; ?>" name="ratesCounter">
						</form>
<?php						
					}
					// /end check for existing records 
					// /////// 
					// /END UPDATE TAX RATES 
					// /////// 
			}
			// /end valid record ?>
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