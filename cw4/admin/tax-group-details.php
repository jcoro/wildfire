<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: tax-group-details.php
File Date: 2012-02-01
Description: Manage Tax Group Details
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"]=CWauth("merchant,developer");
// PAGE PARAMS 
// default value for active or archived view
if(!isset($_GET['view'])) $_GET['view']='active';
if(!isset($_GET['tax_group_id'])) $_GET['tax_group_id']=0;
if(!isset($_ENV["request.cwpage"]["currentRecord"])) $_ENV["request.cwpage"]["currentRecord"]=$_GET['tax_group_id'];
if(!isset($_GET['sortby'])) $_GET['sortby']='region_location';
if(!isset($_GET['sortdir'])) $_GET['sortdir']='asc';
// default form values 
if(!isset($_POST['tax_group_name'])) $_POST['tax_group_name']='';
if(!isset($_POST['tax_group_code'])) $_POST['tax_group_code']='';
if(!isset($_POST['tax_region_id'])) $_POST['tax_region_id']='';
if(!isset($_POST['TaxRate'])) $_POST['TaxRate']='';
if(!isset($_POST['RateCount'])) $_POST['RateCount']='';
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("view,userconfirm,useralert,clickadd,sortby,sortdir");
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"]=CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: Get tax group (active/archived, group ID)
$taxGroupQuery = CWquerySelectTaxGroups(0,$_ENV["request.cwpage"]["currentRecord"]);
$_ENV["request.cwpage"]["currentGroup"] = $taxGroupQuery['tax_group_id'][0];	
$_ENV["request.cwpage"]["groupName"] = $taxGroupQuery['tax_group_name'][0];
// QUERY: Get tax rates by region (group ID)
$taxRatesQuery = CWquerySelectTaxRegionRates($_ENV["request.cwpage"]["currentRecord"],true);
// set up the existing regions 
if (isset($taxRatesQuery['tax_rate_region_id']) && is_array($taxRatesQuery['tax_rate_region_id'])) $_ENV["request.cwpage"]["currentRegions"] = implode(',',$taxRatesQuery['tax_rate_region_id']);
else $_ENV["request.cwpage"]["currentRegions"] = "";
if(!(strlen(trim($_ENV["request.cwpage"]["currentRegions"])))) $_ENV["request.cwpage"]["currentRegions"] = 0;
// QUERY: Get tax regions (id (none), list to omit)
$taxRegionsQuery = CWquerySelectTaxRegions(0, $_ENV["request.cwpage"]["currentRegions"]);
// /////// 
// UPDATE TAX GROUP 
// /////// 
if (isset($_POST['tax_group_name']) && strlen(trim($_POST['tax_group_name']))) {
	// QUERY: update tax group (group id, arcnive y/n, tax group name) 
	$updateTaxGroupID = CWqueryUpdateTaxGroup($_ENV["request.cwpage"]["currentRecord"],
												0,
												$_POST['tax_group_name'],
												$_POST['tax_group_code']);
	if(!(substr($updateTaxGroupID,0,2) =='0-')) {
		// update complete: return to page showing message 
		CWpageMessage("confirm", $_ENV["application.cw"]["taxSystemLabel"] .' Group Saved');
		header("Location: ". $_ENV["request.cwpage"]["baseURL"] . '&userconfirm=' . CWurlSafe($_ENV["request.cwpage"]["userConfirm"]) . '&sortby=' . $_GET['sortby'] . '&sortdir=' . $_GET['sortdir']);
		exit;
	} else {
		// if we have an insert error, show message, do not insert 
		$newVarForList1=explode('-',$updateTaxGroupID);
		$dupField =$newVarForList1[count($newVarForList1)-1];
		CWpageMessage("alert","Error: " . $dupField . " '" . trim($_POST['.tax_group_name'])."' already exists");
	}
	// /END duplicate/error check 
}
// /////// 
// /END UPDATE TAX GROUP 
// /////// 
// /////// 
// INSERT TAX RATE 
// /////// 
if(isset($_POST['tax_region_id']) && is_numeric($_POST['tax_region_id'])) {
	$insertTaxRateID = CWqueryInsertTaxRate($_POST['tax_region_id'],$_ENV["request.cwpage"]["currentRecord"],$_POST['taxRate']);	
	// insert complete: return to page showing message 
	CWpageMessage("confirm","1 ". $_ENV["application.cw"]["taxSystemLabel"] . ' Rate Added');    
	header("Location: ".$_ENV["request.cwpage"]["baseURL"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]) .'&sortby=' .$_GET['sortby'] . '&sortdir='. $_GET['sortdir']);
}
// /////// 
// /END INSERT TAX RATE 
// /////// 
// /////// 
// UPDATE / DELETE TAX RATES 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['tax_rate_id0'])) {
	if(!(isset($_POST['deleteRecord']))) $_POST['deleteRecord']=array();
	$loopCt=0;
	$updateCt=0;
	$deleteCt=0;
	$archiveCt=0;
	$activeCt=0;
	// loop record ids, handle each one as needed 
	foreach ($_POST['recordIDlist'] as $key => $ID) { 
		// DELETE RECORDS 
		// if the record ID is marked for deletion 
		if(in_array($_POST['tax_rate_id'.$loopCt], $_POST['deleteRecord'])) {
			// QUERY: delete record (record id) 
			$deleteRecord = CWqueryDeleteTaxRate($ID); 
			$deleteCt++;
			// if not deleting, update 
		} else {
			// UPDATE RECORDS 
			// param for checkbox values 
			if(!(isset($_POST['TaxRate' . $loopCt]))) $_POST['TaxRate' . $loopCt]=0;
			// verify numeric tax group ID 
			if(!(is_numeric($_POST['TaxRate'.$loopCt]))) {
				$_POST['TaxRate'. $loopCt]=0;
			}
			// QUERY: update record (ID, percentage ) 
			$updateRecord = CWqueryUpdateTaxRate($_POST["tax_rate_id".$loopCt],$_POST["TaxRate" . $loopCt]);
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
	$_ENV["request.cwpage"]["userAlertText"]='';
	if($deleteCt > 0) {
		if($activeCt || $archiveCt) $_ENV["request.cwpage"]["userAlertText"] .='<br>';
		$_ENV["request.cwpage"]["userAlertText"] .= $deleteCt . ' Record';
		if($deleteCt > 1)
			$_ENV["request.cwpage"]["userAlertText"] .= 's';	
		$_ENV["request.cwpage"]["userAlertText"] .= ' Deleted';
	}
	CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
	// return to page as submitted, clearing form scope 
	header("Location: ".$_ENV["request.cwpage"]["relocateURL"].'&userconfirm=' . CWurlSafe($_ENV["request.cwpage"]["userConfirm"]) . '&useralert=' . CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
}
// /////// 
// /END UPDATE / DELETE TAX RATES 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"]="Manage " . $_ENV["application.cw"]["taxSystemLabel"] . ' Group';
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"]="Manage ". $_ENV["application.cw"]["taxSystemLabel"] . ' Groups: ' . $_ENV["request.cwpage"]["groupName"];
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"]="Assign ". $_ENV["application.cw"]["taxSystemLabel"] . ' Region and manage '.$_ENV["application.cw"]["taxSystemLabel"] . ' Rates';
// current menu marker 
$_ENV["request.cwpage"]["currentNav"]='tax-groups.php';
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"]=1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"]=1;
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
<?php 
include("cwadminapp/inc/cw-inc-admin-nav.php");             
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
if(strlen(trim($_ENV["request.cwpage"]["heading1"])))
	echo '<h1>'. trim($_ENV["request.cwpage"]["heading1"]).'</h1>';
if(strlen(trim($_ENV["request.cwpage"]["heading2"])))
	echo '<h2>'. trim($_ENV["request.cwpage"]["heading2"]).'</h2>';
?>
		<!-- Admin Alert - message shown to user -->
<?php
include("cwadminapp/inc/cw-inc-admin-alerts.php");
?>
		<!-- Page Content Area -->
		<!-- //// PAGE CONTENT ////  -->
		<?php // LINKS FOR VIEW OPTIONS ?>
					<div id="CWadminContent">
						<div class="CWadminControlWrap">
							<strong>
							<p><a href="tax-group-products.php?tax_group_id=<?php echo $_ENV["request.cwpage"]["currentRecord"]; ?>">Associated Products</a> </p>
							</strong>
						</div>
<?php
// /END LINKS FOR VIEW OPTIONS 
// if a valid record is not found 
if(!($taxGroupQuery['totalRows']) == 1) {            
?>
                        	<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>Invalid <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> group id. Please return to the <a href="tax-groups.php"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group Listing</a> and choose a valid <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> group.</p>
<?php
    // if a record is found 
} else {
    // /////// 
    // UPDATE TAX GROUP 
    // /////// 
    // FORM 
?>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate" name="updateTaxGroupForm" id="updateTaxGroupForm" method="post">
								<p>&nbsp;</p>
								<h3>Edit <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group</h3>
								<table class="CWinfoTable CWformTable">
									<thead>
									<tr>
										<th>Name</th>
										<th>Code</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<?php // name ?>
										<td>
											<input name="tax_group_name" type="text" size="25" class="required" title="Group Name is required" id="tax_group_name" value="<?php echo $taxGroupQuery['tax_group_name'][0]; ?>" onblur="checkValue(this)">
											<br>
											<?php // submit button ?>
											<input name="SubmitAddTaxGroup" type="submit" class="submitButton" id="SubmitAddTaxGroup" value="Change Name">
										</td>
										<?php // code ?>
										<td>
											<input name="tax_group_code" type="text" size="25" class="required" title="Group code is required" id="tax_group_code" value="<?php echo $taxGroupQuery['tax_group_code'][0]; ?>" onblur="checkValue(this)">
										</td>
									</tr>
									</tbody>
								</table>
							</form>
<?PHP
	// /////// 
	// /END UPDATE TAX GROUP 
	// /////// 
	// Tax Rates only available for localtax 
	if (strtolower($_ENV["application.cw"]["taxCalctype"]) == "localtax") {
		// /////// 
		// ADD NEW TAX RATE 
		// /////// 
		// FORM 
?>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate" name="addNewForm" id="addNewForm" method="post">
								<p>&nbsp;</p>
								<h3>Add New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate</h3>
<?php
		// verify tax regions exist 
		if(!($taxRegionsQuery['totalRows'])) {
?>
                        			<p>&nbsp;</p>
									<p>Create at least one active <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Region to add a new <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate</p>
<?php
		} else {
?>	
									<table class="CWinfoTable">
										<thead>
										<tr>
											
											<th>Available <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Regions</th>
											<th><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate</th>
											
										</tr>
										</thead>
										<tbody>
										<tr>
											<?php // tax region selection ?>
											<td>
												<select name="tax_region_id">
<?php 
			for($j =0 ;$j < $taxRegionsQuery['totalRows'] ; $j++) {		
?>
                                            		<option value="<?php echo $taxRegionsQuery['tax_region_id'][$j]; ?>"><?php echo $taxRegionsQuery['country_name'][$j]; if($taxRegionsQuery['stateprov_name'][$j] !='') { ?> : <?php echo $taxRegionsQuery['stateprov_name'][$j]; } ?> (<?php echo $taxRegionsQuery['tax_region_label'][$j]; ?>)</option>
<?php
			}
?>
												</select>
												<br>
												<input name="SubmitAddTaxRate" type="submit" class="submitButton" id="SubmitAddTaxGroup" value="Save New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate">
											</td>
											<?php // rate ?>
											<td><input name="taxRate" type="text" id="taxRate" size="6" maxlength="10" value="0.00" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">%</td>
										</tr>
										</tbody>
									</table>
									<p><a href="tax-regions.php">Add New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Region</a></p>
									<p>&nbsp;</p>
								
<?php
		}
		// / end verify tax regions exist 
?>
							</form>
<?php
		// /////// 
		// /END ADD NEW TAX RATE
		// /////// 
		// /////// 
		// UPDATE TAX RATES 
		// /////// 
		// check for existing records 
?>
							<p>&nbsp;</p>
							<h3>Active <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rates</h3>
<?php
		if(!($taxRatesQuery['totalRows'])) {      
?>	
                            	<p>&nbsp;</p>
								<p>There are currently no <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> rates defined for this <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> group</p>
<?php
		// if existing records found 
		} else {
?>
								<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" name="recordForm" id="recordForm" method="post" class="CWobserve">
									<table class="CWsort CWstripe CWinfoTable wide" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
										<thead>
										<tr class="sortRow">
											<th class="region_Location">Region</th>
											<th class="tax_region_label">Name</th>
											<th class="tax_rate_percentage" style="text-align:center"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate </th>
											<th width="85" style="text-align:center" class="noSort">
												<input type="checkbox" class="checkAll" name="checkAllDelete" rel="checkAllDel">Delete
											</th>
										</tr>
										</thead>
										<tbody>
										
<?php 
			for($j=0;$j < $taxRatesQuery['totalRows'] ; $j++) {
?>
										<tr>
											<?php // region ?>
											<td>
												<a href="tax-region-details.php?tax_region_id=<?php echo $taxRatesQuery['tax_region_id'][$j]; ?>" class="detailsLink" title="Manage <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Rate details"><?php echo $taxRatesQuery['country_name'][$j]; ?><?php if($taxRatesQuery['stateprov_name'][$j] !='') {?>: <?php echo $taxRatesQuery['stateprov_name'][$j];}?>
												</a>
											</td>
											<?php // name ?>
											<td>
												<a href="tax-region-details.php?id=<?php echo $taxRatesQuery['tax_region_id'][$j]; ?>" class="detailsLink">
												<?php echo $taxRatesQuery['tax_region_label'][$j]; ?>
												</a>
											</td>
											<?php // rate ?>
											<td>
												<input name="TaxRate<?php echo $j; ?>" type="text" id="TaxRate<?php echo $j; ?>" value="<?php echo $taxRatesQuery['tax_rate_percentage'][$j]; ?>" size="5" maxlength="6" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">
												%
											</td>
											<?php // delete ?>
											<td style="text-align:center;">
												<input name="deleteRecord[<?php echo $j; ?>]" type="checkbox" class="formCheckbox checkAllDel" value="<?php echo $taxRatesQuery['tax_rate_id'][$j]; ?>">
												<input type="hidden" name="tax_rate_id<?php echo $j; ?>" value="<?php echo $taxRatesQuery['tax_rate_id'][$j]; ?>">
												<input name="recordIDlist[<?php echo $j; ?>]" type="hidden" value="<?php echo $taxRatesQuery['tax_rate_id'][$j]; ?>">
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
	// /end if localtax 
}
// /end valid record 
?>
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