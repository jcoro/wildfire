<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: countries.php
File Date: 2012-07-09
Description: Displays product management table
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
if(!isset($_GET['view'])) { $_GET['view'] = "active"; }
// default form values
if(!isset($_POST['country_id'])) { $_POST['country_id'] = 0; }
if(!isset($_POST['stateprov_code'])) { $_POST['stateprov_code'] = ""; }
if(!isset($_POST['stateprov_name'])) { $_POST['stateprov_name'] = ""; }
if(!isset($_POST['country_sort'])) { $_POST['country_sort'] = 1; }
if(!isset($_POST['country_name'])) { $_POST['country_name'] = ""; }
// default for error handling 
if(!isset($_ENV["request.cwpage"]["errorMessage"])) { $_ENV["request.cwpage"]["errorMessage"] = ""; }
// list of null region names to skip during lookups 
if(!isset($_ENV["request.cwpage"]["nullRegionNames"])) { $_ENV["request.cwpage"]["nullRegionNames"] = '"none","all"'; }
// defaults for display loops/counters 
if(!isset($UsedStateList)) { $UsedStateList = "0"; }
if(!isset($UsedShippingMethodList)) { $UsedShippingMethodList = "0"; }
if(!isset($UsedCountryList)) { $UsedCountryList = "0"; }
if(!isset($CustomerCountryList)) { $CustomerCountryList = "0"; }
if(!isset($CustomerStateList)) { $CustomerStateList = "0"; }
$countryCounter = 0;
$stateCounter = 0;
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("view,userconfirm,useralert,clickadd,country");
// create the base url out of serialized url variables 
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// ACTIVE VS. ARCHIVED 
if (strpos(strtolower($_GET["view"]), 'arch') !== false) {
	$_ENV["request.cwpage"]["viewType"] = 'Archived';
	$_ENV["request.cwpage"]["recordsArchived"] = 1;
	$_ENV["request.cwpage"]["subHead"] = 'Archived Countries are not shown in the store';
} else {
	$_ENV["request.cwpage"]["viewType"] = 'Active';
	$_ENV["request.cwpage"]["recordsArchived"] = 0;
	$_ENV["request.cwpage"]["subHead"] = 'Manage active Countries and Regions, or add a new Country';
}
// QUERY: Get all available countries for dropdown selection on add new form 
$countriesQuery=CWquerySelectCountries($_ENV["request.cwpage"]["recordsArchived"]);
// QUERY: Get all states and countries (active/archived) 
$statesQuery = CWquerySelectCountryStates($_ENV["request.cwpage"]["recordsArchived"]);
if (isset($statesQuery['country_id']) && is_array($statesQuery['country_id'])) $iCountryList = implode(',',$statesQuery['country_id']);
else $iCountryList = "0";
// QUERY: get all states with a user-defined code 
$countryIdsQuery = CWquerySelectStateCountryIDs();		
if($countryIdsQuery['totalRows']) {
	$CheckStateList =implode(',',$countryIdsQuery['stateprov_country_id']);
}
// QUERY: get all ship methods that have orders 
$usedShipMethodsQuery = CWquerySelectOrderShipMethods();
if($usedShipMethodsQuery['totalRows']) {
	$UsedShippingMethodList = implode(',',$usedShipMethodsQuery['order_ship_method_id']);
}
// QUERY: get all states with customer record matches 
$usedStatesQuery = CWquerySelectCustomerStates();
if($usedStatesQuery['totalRows']) {
	$UsedStateList = implode(',',$usedStatesQuery['customer_state_stateprov_id']);
}
// QUERY: get all shipping method countries with orders attached (ids to omit) 
$usedCountriesQuery = CWquerySelectShipCountries($UsedShippingMethodList);
if($usedCountriesQuery['totalRows']) {
	$UsedCountryList = implode(',',$usedCountriesQuery['ship_method_country_country_id']);
}
// QUERY: get all states/countries with customer address matches 
$customerStatesQuery = CWquerySelectCustomerCountries();
if($customerStatesQuery['totalRows']) {
	$CustomerCountryList = implode(',',$customerStatesQuery['stateprov_country_id']);
	$CustomerStateList = implode(",",$customerStatesQuery['customer_state_stateprov_id']);
}
// /////// 
// ADD NEW COUNTRY / REGION 
// /////// 
// if submitting the 'add new' form (region name not blank)  
if(strlen(trim($_POST['stateprov_name'])) && $_ENV["request.cwpage"]["recordsArchived"] == 0) {
	// ADD NEW COUNTRY 
	if(strlen(trim($_POST['country_name']))) {	
		// QUERY: insert new country (name, code, sort order, archived, default)
		$newCountryID = CWqueryInsertCountry(
			trim($_POST['country_name']),
			trim($_POST['stateprov_code']),
			$_POST['country_sort'],
			0,
			0
		);
		// if no error returned from insert query 
		if((substr($newCountryID,0,2) != '0-')) {	
			// set up confirmation message 
			CWpageMessage("confirm",'Country ' . $_POST['country_name'] . ' Added');
			$_ENV["request.cwpage"]["newCountryID"]=$newCountryID;
			// if we have an insert error, show message, do not insert 
		} else {
			$newVarLst1=explode('-',$newCountryID);
			$_ENV["request.cwpage"]["errorMessage"]=$newVarLst1[count($newVarLst1)-1];
			CWpageMessage("alert",$_ENV["request.cwpage"]["errorMessage"]);
			$_GET['clickadd']=1;
		}
		// end duplicate error check 
		// if not adding a new country 
	} else {
		$_ENV["request.cwpage"]["newCountryID"] = $_POST['country_id'];
	}
	// /END country insert 
	// ADD NEW REGION 
	// if no error from country insert, continue with region insert 
	if(!(strlen(trim($_ENV["request.cwpage"]["errorMessage"])))) {
		// if region is 'none' or 'all', add a placeholder record 
		if(strtolower($_POST['stateprov_name']) == 'all' || strtolower($_POST['stateprov_name']) == 'none' || $_POST['stateprov_name'] == '') {
		// QUERY: insert stateprov record (name, code, country ID) 
			$newRegionID = CWqueryInsertStateProv(
				'All',
				'All',
				$_ENV["request.cwpage"]["newCountryID"]
				);
			// if not none or all, add an actual record 
		} else {
			// QUERY: insert stateprov record (name, code, country ID) 
			$newRegionID = CWqueryInsertStateProv(
				trim($_POST['stateprov_name']),
				trim($_POST['stateprov_code']),
				$_ENV["request.cwpage"]["newCountryID"]
				);
			// QUERY: archive any placeholder regions for this country (archive y/n, country ID, name, code to match) 
			$archiveRegion = CWqueryArchiveStateProv(
				1,
				$_ENV["request.cwpage"]["newCountryID"],
				$_ENV["request.cwpage"]["nullRegionNames"],
				''
				);
		}
		// end placeholder or actual record 
		// if no error returned from insert query 
		if((substr($newRegionID,0,2) != '0-')) {
			// set up confirmation message 
			CWpageMessage("confirm","Region " . $_POST['stateprov_name'] . " Added");
		} else {
			// if we have an insert error, show message, do not insert 
			$newvarforList2=explode('-',$newRegionID);
			CWpageMessage("alert",$newvarforList2[count($newvarforList2)-1]);
			$_GET['clickadd']=1;
		}
		// end duplicate error check 
	}
	// / END ADD NEW REGION
	// if no error, return showing message to clear form fields 
	if(!(strlen(trim($_ENV["request.cwpage"]["errorMessage"])))) {
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ". $_ENV["request.cwpage"]["baseURL"]."&country=".$_ENV["request.cwpage"]["newCountryID"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]).'&clickadd=1');	
		exit;
	}
}
// /////// 
// /END ADD NEW REGION 
// /////// 
// /////// 
// UPDATE/DELETE REGIONS 
// /////// 
// look for at least one valid ID field 
if (isset($_POST['country_id0'])) {
	$loopCt = 0;
	$updateStPrvCt = 0;
	$updateCountryCt = 0;
	$deleteStPrvCt = 0;
	$deleteCountryCt = 0;
	$archiveStPrvCt = 0;
	$archiveCountryCt = 0;
	$activeStPrvCt = 0;
	$activeCountryCt = 0;
	// Loop through all of the submitted states 
	for ($id=0; $id < $_POST['stateCounter']; $id++) {
		$stateID = $_POST['stateprov_id'.$id];
		// DELETE REGION 
		if(isset($_POST['stprv_Delete'.$id])) {
			// QUERY: delete associated tax regions 
			$deleteTaxRegions = CWqueryDeleteTaxRegion($stateID);
			// QUERY: delete the state 
			$deleteStateProv = CWqueryDeleteStateProv($stateID);
			// increment the delete counter 
			$deleteStPrvCt++;
			// UPDATE REGION if not deleting <!--- /////// --->
		} else {
			// if marked for archiving 
			if(isset($_POST['stateprov_archive'.$id])) {
				$archiveRecord=1;
			} else {
				$archiveRecord=0;
			}
			// /end if archiving 
			// QUERY: determine previous active/archive status of region 
			$regionPrevArchive = CWquerySelectStateProvDetails($stateID,'','');	
			if($regionPrevArchive['stateprov_archive']==1) {
				$prevArchive = 1;
			} else {
				$prevArchive = 0;
			}
			// QUERY: update the region (id, name, code, archive)
			$updatedStateProvID = CWqueryUpdateStateProv(
				$stateID,
				$_POST['stateprov_name'.$id],
				$_POST['stateprov_code'.$id],
				$archiveRecord
				);
			// if no error returned from update query 
			if(!(substr($updatedStateProvID,0,2) == '0-')) {
				// increment archive/active counters 
				if($prevArchive == 1 && $archiveRecord == 0) {
					$activeStPrvCt++;
				}
				elseif($prevArchive == 0 && $archiveRecord == 1) {
					$archiveStPrvCt++;
				} else {
					// increment the update counter 
					$updateStPrvCt++;
				}
				// if we have an insert error, show message, do not insert 
			} else {
				$updatedStateProvIDLast=explode('-',$updatedStateProvID);
				CWpageMessage("alert",$updatedStateProvIDLast[count($updatedStateProvIDLast)-1]);
				$_GET['clickadd']=1;
			}
			// end duplicate error check 
		}
		// end delete / update 
	}
	// UPDATE COUNTRIES 
	// Loop through all of the submitted countries 
	for($id=0; $id < $_POST['countryCounter']; $id++) {
		$countryID = $_POST['country_id'.$id];
		// DELETE COUNTRY 
		if(isset($_POST['country_Delete'.$id])) {
			// QUERY: get list of states for this country (stateprov ID, stateprov Name, stateprov Code, country ID) 
			$stateListQuery = CWquerySelectStateProvDetails(0,'','',$countryID);
			// if we have states for this country 
			if($stateListQuery['totalRows']) {
				$deleteStates = $stateListQuery['stateprov_id'];
				// delete all states 
				foreach ($deleteStates as $key => $stID) {
					$deleteStateProv = CWqueryDeleteStateProv($stID);
					// increment counter 
					$deleteStPrvCt++;
				}
			}
			// /end if we have states 
			// QUERY: delete tax regions associated with deleted country (record id, state id, country id) 
			$deleteTaxRegion = CWqueryDeleteTaxRegion(0,0,$countryID);
			// QUERY: get number of states related to this country 
			$countStates = CWquerySelectStateProvDetails(0,'','',$countryID);
			// QUERY: delete all states related to this country (record ID, country ID) 
			$deleteStateProv = CWqueryDeleteStateProv(0,$countryID);
			$deleteStPrvCt += $countStates['totalRows'];
			// QUERY:  get ship ranges list for deletion (country ID, archived ) 
			$shipRangesQuery = CWquerySelectShippingCountryRanges($countryID,2);
			$deletelist = $shipRangesQuery['ship_range_id'];
			// QUERY: delete ship ranges for country 
			for($delID=0 ; $delID < count($deleteList) ; $delID++) {
				$deleteShipRange = CWqueryDeleteShippingRange($delID);
			}
			// QUERY: get ship methods ID list for deletion (country ID, archived)
			$shipMethodsQuery = CWquerySelectShippingMethods($countryID,2);
			$deleteList = $shipMethodsQuery['ship_method_id'];
			// QUERY: delete ship methods for country 
			foreach ($deleteList as $key => $delID) {
				$deleteShipMethod = CWqueryDeleteShippingMethod($delID);
			}
			// QUERY: delete the actual country 
			$deleteCountry = CWqueryDeleteCountry($countryID);
			$deleteCountryCt++;
			// /end Delete country 
		} else {
			// UPDATE COUNTRY 
			if(!(isset($_POST['country_archive'.$id]))) $_POST['country_archive'.$id]=$_ENV["request.cwpage"]["recordsArchived"];
			// default country y/n 
			if(isset($_POST['defCountry']) && $_POST['defCountry'] == $countryID) {	
				$defaultCountry=1;
			} else {
				$defaultCountry=0;
			}
			// SET APPLICATION VARIABLE FOR DEFAULT COUNTRY 
			if($_ENV["application.cw"]["defaultCountryID"] != $countryID && $defaultCountry == 1) {
				$_ENV["application.cw"]["defaultCountryID"] = $countryID;
				$_SESSION["application.cwstorage"] = $_ENV["application.cw"];
				CWsetApplicationRefresh();
				CWpageMessage("confirm","Default Country Set");
			}
			// QUERY: update country (id, name, code, archive y/n, sort, default y/n) 
			$updatedCountryID = CWqueryUpdateCountry(
				$countryID,
				$_POST['country_name'.$id],
				$_POST['country_code'.$id],
				$_POST['country_archive'.$id],
				$_POST['country_sort'.$id],
				$defaultCountry
				);
			// if no error returned from update query 
			if(!(substr($updatedCountryID,0,2) == '0-')) {
				// increment archive/active counters 
				if($_ENV["request.cwpage"]["recordsArchived"] == 1 && $_POST['country_archive'.$id] == 0) {
					$activeCountryCt++;
				}
				elseif($_ENV["request.cwpage"]["recordsArchived"] == 0 && $_POST['country_archive'.$id] == 1) {
					$archiveCountryCt++;
				} else {
					// increment update counter
					$updateCountryCt++;
				}
				// if we have an insert error, show message, do not insert 
			} else {
				$newvarforList3=explode('-',$updatedCountryID);
				CWpageMessage("alert",$newvarforList3[count($newvarforList3)-1]);
				$_GET['clickadd']=1;				
			}
			// end duplicate error check 
			if(isset($_POST['defCountry']) && $_POST['defCountry'] == $countryID) {
				$_ENV["application.cw"]["defaultCountryID"] = $countryID;
				$_SESSION["application.cwstorage"] = $_ENV["application.cw"];
				CWsetApplicationRefresh();
			}
		}
		// end delete/update 
	}
	// handle placeholder regions 
	// QUERY: get all countries with stateprovs active 
	$activeCountries = CWquerySelectUserStateProvCountries();
	// set up list of country IDs 
	if (isset($activeCountries['country_id']) && is_array($activeCountries['country_id'])) $countryIDlist = implode(',', $activeCountries['country_id']);
	else $countryIDlist = "";
	// QUERY: archive placeholder state for countries not in active list (archive, country IDs, name, code, omit) 
	$archiveRegion = CWqueryArchiveStateProv(1,$countryIDlist,'','All,None',0);
	// QUERY: unarchive placeholder state for remaining countries (archive, country IDs, name, code, omit) 
	$unarchiveRegion = CWqueryArchiveStateProv(1,$countryIDlist,'','All,None',$countryIDlist);	
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars("userconfirm,useralert,country");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	CWpageMessage("confirm","Changes Saved");
	// save alert text 
	$_ENV["request.cwpage"]["userAlertText"]='';
	if($archiveStPrvCt > 0) {	
		$_ENV["request.cwpage"]["userAlertText"] .= $archiveStPrvCt.' Region'; if($archiveStPrvCt > 1) { $_ENV["request.cwpage"]["userAlertText"] .= 's'; } $_ENV["request.cwpage"]["userAlertText"] .= ' Archived';	
	}
	if($activeStPrvCt > 0) {	
		$_ENV["request.cwpage"]["userAlertText"] .= $activeStPrvCt.' Region'; if($activeStPrvCt > 1) { $_ENV["request.cwpage"]["userAlertText"] .= 's'; } $_ENV["request.cwpage"]["userAlertText"] .= ' Activated';	
	}
	if($deleteStPrvCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"] .= $deleteStPrvCt.' Region'; if($deleteStPrvCt > 1) { $_ENV["request.cwpage"]["userAlertText"] .= 's'; } $_ENV["request.cwpage"]["userAlertText"] .= ' Deleted';
	}
	if($archiveCountryCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"] .= $archiveCountryCt.' Countr'; if($archiveCountryCt > 1) { $_ENV["request.cwpage"]["userAlertText"] .= 'iess'; } else { $_ENV["request.cwpage"]["userAlertText"] .= 'y'; } $_ENV["request.cwpage"]["userAlertText"] .= ' Archived';
	}
	if($activeCountryCt >0) {
		$_ENV["request.cwpage"]["userAlertText"] .= $activeCountryCt.' Countr'; if($activeCountryCt > 1) { $_ENV["request.cwpage"]["userAlertText"] .= 'iess'; } else { $_ENV["request.cwpage"]["userAlertText"] .= 'y'; } $_ENV["request.cwpage"]["userAlertText"] .= ' Activated';
	}
	if($deleteCountryCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"] .= $deleteCountryCt.' Countr'; if($deleteCountryCt > 1) { $_ENV["request.cwpage"]["userAlertText"] .='iess'; } else { $_ENV["request.cwpage"]["userAlertText"] .= 'y'; } $_ENV["request.cwpage"]["userAlertText"] .= ' Deleted';
	}
	CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
	// return to page as submitted, clearing form scope 
	if ($deleteCountryCt > 0 || $archiveCountryCt > 0) {
		$_ENV["request.cwpage"]["relocateURL"] .= "&country=0";
	} else if (isset($_GET["country"])) {
		$_ENV["request.cwpage"]["relocateURL"] .= "&country=".$_GET["country"];
	}
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_ENV["request.cwpage"]["relocateURL"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
	exit;
}
// /////// 
// /END UPDATE/DELETE REGIONS 
// /////// 
// default country 
if (!isset($_GET["country"]) && isset($_ENV["application.cw"]["defaultCountryID"])) {
	$_GET["country"] = $_ENV["application.cw"]["defaultCountryID"];
}
// PAGE SETTINGS 
// Page Browser Window Title <title> 
$_ENV["request.cwpage"]["title"]='Manage Countries and Regions';	
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"]='Countries and Regions Managemant';
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"]=$_ENV["request.cwpage"]["subHead"];
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"];
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"]=1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"]=1;
// START OUTPUT ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo $_ENV["application.cw"]["companyName"];?> : <?php echo $_ENV["request.cwpage"]["title"];?></title>
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
			// add new show-hide
			jQuery('form#addNewform').hide();
			jQuery('a#showAddNewformLink').click(function() {
				jQuery(this).hide();
				jQuery('form#addNewform').show().find('input.focusField').focus();
				return false;
			});
			// auto-click the link if adding
<?php
if(isset($_GET['clickadd'])) {
?>
				jQuery('a#showAddNewformLink').click();
<?php
}
?>
			// change country with select 
			jQuery('#countrySel').change(function(){
			 	var newUrl = jQuery(this).find('option:selected').attr('value');
			 	window.location = newUrl;
			});
			// add country form input swap
			jQuery('#addCountryLink').click(function(){
				jQuery('#country_id').hide();
				jQuery('#country_name').show().attr('disabled',false).parents('td').siblings('td').children('#stateprov_name').attr('value','All').attr('defaultValue','All');
				jQuery('#stateprov_nam').parents('td').hide();
				jQuery('#stateprov_name_label').hide();
				jQuery(this).hide().siblings('a').show();
				return false;
			});
			jQuery('#hideCountryLink').click(function(){
				jQuery('#country_id').show();
				jQuery('#country_name').hide().attr('disabled',true).parents('td').siblings('td').children('#stateprov_name').attr('value','').attr('defaultValue','');
				jQuery('#stateprov_nam').parents('td').show();
				jQuery('#stateprov_name_label').show();
				jQuery(this).hide().siblings('a').show();
				return false;
			});
			// don't allow default country to be archived
			jQuery('input.archiveCountry').click(function(){
				var isChecked = false;
				if (jQuery(this).prop('checked')==true){
				isChecked = true;
			};
			// if sibling radio is checked, no click is allowed
			if (jQuery(this).parent('td').parent('tr').find('input[type=radio]').prop('checked')==true){
				if (isChecked == true){
					// show message, uncheck box
					alert('Default country cannot be archived\nChoose a new default country first');
					jQuery(this).prop('checked',false);
					}
				};
			});
			// don't allow archived country to be set as default
			jQuery('input[name="defCountry"]').click(function(){
				if (jQuery(this).prop('checked')==true){
					if (jQuery(this).parent('td').parent('tr').find('input.archiveCountry').prop('checked')==true){
					alert('Archived country cannot be set as default\nUncheck archive box for this country first');
					jQuery(this).prop('checked',false);
					};
				};
			});

			});
		</script>
		<?php // /END PAGE JAVASCRIPT ?>
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
if(strlen(trim($_ENV["request.cwpage"]["heading1"]))) { echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]).'</h1>'; }
if(strlen(trim($_ENV["request.cwpage"]["heading2"]))) { echo '<h2>'.trim($_ENV["request.cwpage"]["heading2"]).'</h2>'; }
?>
					<!-- Admin Alert - message shown to user -->
<?php
include("cwadminapp/inc/cw-inc-admin-alerts.php");        
?>
					<!-- Page Content Area -->
					<div id="CWadminContent">
						<!-- //// PAGE CONTENT ////  -->
						<?php // LINKS FOR VIEW TYPE ?>
						<div class="CWadminControlWrap">
							<strong>
<?php
if(isset($_GET['view']) && $_GET['view'] == 'arch') {
?>
    							<a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>">View Active</a>
<?php
} else {
?>
    							<a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?view=arch">View Archived</a>
<?php
								// link for add-new form 
	if($_ENV["request.cwpage"]["recordsArchived"] == 0) {
?>
									&nbsp;&nbsp;<a class="CWbuttonLink" id="showAddNewformLink" href="#">Add New Country / Region</a>
<?php
	}
}
?>
			</strong>
						</div>
<?php
// /END LINKS FOR VIEW TYPE 
// /////// 
// ADD NEW COUNTRY 
// /////// 
if($_ENV["request.cwpage"]["recordsArchived"] == 0) {        
	// form ?>				
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate" name="addNewform" id="addNewform" method="post">
								<p>&nbsp;</p>
								<h3>Add New Country / Region</h3>
								<table class="CWinfoTable wide">
									<thead>
									<tr>
										<th width="250">Country Name</th>
										<th id="stateprov_name_label">Region Name</th>
										<th>Code</th>
										<th>Sort</th>
									</tr>
									</thead>
									<tbody>
									<tr>
                                        <?php // location ?>
										<td style="text-align:center">
											<div>
												<select name="country_id" id="country_id">
<?php
	for ($i=0; $i<$countriesQuery['totalRows']; $i++) {
?>
													<option value="<?php echo $countriesQuery['country_id'][$i];?>"<?php if ($countriesQuery['country_id'][$i] == $_GET["country"]) { ?> selected="selected"<?php } ?>><?php echo $countriesQuery['country_name'][$i];?></option>
<?php
	}
?>
												</select>
												<?php // new country ?>
												<input name="country_name" type="text" size="18" maxlength="100" class="required" title="Country Name is required" id="country_name" style="display:none;"  disabled="disabled" value="<?php echo $_POST['country_name'];?>">
												<a href="#" id="addCountryLink">Add New</a>
												<a href="#" id="hideCountryLink" style="display:none;">Cancel</a>
											</div>
											<br>
											<input name="SubmitAdd" type="submit" class="CWformButton" id="SubmitAdd" value="Save New Region">
										</td>
                                        <?php // region name ?>
										<td><input name="stateprov_name" type="text" size="28" maxlength="100" class="required" value="<?php echo $_POST['stateprov_name'];?>" title="Region Name is required" id="stateprov_name" onblur="checkValue(this)"> </td>
                                        <?php // region code ?>
										<td><input name="stateprov_code" type="text" size="5" maxlength="35" class="required" value="<?php echo $_POST['stateprov_code'];?>" title="Code is required" id="stateprov_code"> </td>
                                        <?php // sort order ?>
										<td>
											<input name="country_sort" type="text" id="country_sort" size="4" maxlength="7" class="required sort" title="Sort order is required" value="<?php echo $_POST['country_sort'];?>" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">
										</td>
									</tr>
									</tbody>
								</table>
							</form>
							<p>&nbsp;</p>
<?php
}
// /////// 
// /END ADD NEW COUNTRY 
// /////// 
// /////// 
// EDIT RECORDS 
// /////// ?>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>&view=<?php echo $_GET['view'];?>&country=<?php echo $_GET["country"]; ?>" name="recordform" id="recordform" method="post" class="CWobserve">
<?php
// if no records found, show message 
if(!($statesQuery['totalRows'])) {
?>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>No <?php echo $_ENV["request.cwpage"]["viewType"];?> Countries available.</strong> <br><br></p>
<?php
// if records found 
} else {
	// output records 
	// Container table ?>
								<table class="CWinfoTable wide">
									<thead>
									<tr class="headerRow">
										<th><?php echo $_ENV["request.cwpage"]["viewType"];?> Countries<?php if (!$_ENV["request.cwpage"]["recordsArchived"]) { ?>/Regions<?php } ?></th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td>
<?php
	// country selection 
	if (!$_ENV["request.cwpage"]["recordsArchived"]) {
?>
											<label>&nbsp;&nbsp;Manage Country:
											<select name="countrySel" id="countrySel">
												<option value="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?country=0">All Countries</option>
<?php
		$lastCountryID = "";
		for ($i=0; $i<$statesQuery["totalRows"]; $i++) {
			$lastCountryID = $statesQuery["country_id"][$i];
?>
													<option value="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?country=<?php echo $statesQuery["country_id"][$i]; ?>"<?php if ($_GET["country"] == $statesQuery["country_id"][$i]) { ?> selected="selected"<?php } ?>><?php echo $statesQuery["country_name"][$i]; ?></option>
<?php
			while ($i<$statesQuery["totalRows"] && $lastCountryID == $statesQuery["country_id"][$i]) {
				$i++;
			}
			$i--;
		}
?>
											</select>
											</label>
<?php
	}
?>
                                            <?php // submit button ?>
											<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
											<div style="clear:right;"></div>
<?php
	// country table 
	// output countries and states 
	$lastCountryID = "";
	for ($i=0; $i<$statesQuery["totalRows"]; $i++) {
		$lastCountryID = $statesQuery['country_id'][$i];
		if ($_GET["country"] == 0 || $_GET["country"] == $statesQuery['country_id'][$i] || $_ENV["request.cwpage"]["recordsArchived"]) {
?>
							
											<table class="CWinfoTable">
												<tr class="headerRow">
													<th width="<?php if ($_ENV["request.cwpage"]["recordsArchived"]) { ?>280<?php } else { ?>200<?php } ?>"><h3><?php echo $statesQuery['country_name'][$i];?></h3></th>
													<th width="100">Sort</th>
<?php
			if (!$_ENV["request.cwpage"]["recordsArchived"]) {
?>
													<th width="80">Default</th>
<?php
			}
?>
													<th width="80">Delete</th>
													<th width="80"><?php if ($_ENV["request.cwpage"]["recordsArchived"]) { ?>Activate<?php }else {?>Archive<?php }?></th>
												</tr>
												<tr>
                                                    <?php // country code ?>
													<td style="text-align:right;">
														Code: <input type="text" name="country_code<?php echo $countryCounter; ?>" value="<?php echo $statesQuery['country_code'][$i];?>" size="8" onblur="checkValue(this)">
                                                        <?php // hidden ID field ?>
														&nbsp;&nbsp;&nbsp;ID: <?php echo $statesQuery['country_id'][$i]; ?>
														<input name="country_id<?php echo $countryCounter; ?>" type="hidden" size="2" id="country_id<?php echo $countryCounter;?>" value="<?php echo $statesQuery['country_id'][$i]; ?>">
                                                        <?php // country name ?>
														<input type="hidden" name="country_name<?php echo $countryCounter; ?>" value="<?php echo $statesQuery['country_name'][$i]; ?>" size="25">
													</td>
                                                    <?php // sort ?>
													<td>
														<input name="country_sort<?php echo $countryCounter; ?>" type="text" value="<?php echo $statesQuery['country_sort'][$i]; ?>" size="3" class="sort" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">
													</td>
<?php
			if (!$_ENV["request.cwpage"]["recordsArchived"]) {
				// default radio 
?>
													<td style="text-align:center">
														<input name="defCountry" type="radio" <?php if($statesQuery['country_default_country'][$i] == 1) {?>checked="checked"<?php }?> class="formRadio" value="<?php echo $statesQuery['country_id'][$i]; ?>">
													</td>
<?php
			}
			// delete 
?>
													<td style="text-align:center">
														<input name="country_Delete<?php echo $countryCounter; ?>" value="1" type="checkbox" class="formCheckbox radioGroup" rel="group<?php echo $i;?>"<?php if (ListFind($CheckStateList,$statesQuery['country_id'][$i]) || ListFind($UsedCountryList,$statesQuery['country_id'][$i]) || ListFind($CustomerCountryList,$statesQuery['country_id'][$i])) { ?> disabled="disabled"<?php }?>>
													</td>
                                                    <?php // archive ?>
													<td style="text-align:center">
														<input name="country_archive<?php echo $countryCounter; ?>" value="<?php if($_ENV["request.cwpage"]["recordsArchived"]) {?>0<?php }else {?>1<?php }?>" type="checkbox" class="formCheckbox radioGroup archiveCountry" rel="group<?php echo $i;?>"
														<?php if(ListFind($CustomerCountryList,$statesQuery['country_id'][$i]) && $_ENV["request.cwpage"]["recordsArchived"] != 1) { ?>onclick="if(this.checked) return confirm('This country has customers associated with it. Are you sure you want to archive?')"<?php } ?>>
													</td>
												</tr>
<?php
			// only show states for active countries 
			if ($_ENV["request.cwpage"]["recordsArchived"] != 1) {
?>
												<tr>
													<td colspan="5" align="center">
<?php
				$haveActiveState = false;
				$stateTable = "";
				if (ListValueCount($iCountryList,$statesQuery['country_id'][$i]) > 1) {
					$stateTable .= '<table class="formTable infoTable CWstripe">
										 <tr class="sortRow">
											<th width="200">Region Name</th>
											<th width="90">Code</th>
											<th>Ship Ext</th>
											<th>Delete</th>
											<th>Archive</th>
										 </tr>
';
					while ($i<$statesQuery["totalRows"] && $statesQuery['country_id'][$i] == $lastCountryID) {
						if(strtolower($statesQuery['stateprov_code'][$i]) != 'none' && strtolower($statesQuery['stateprov_code'][$i]) != 'all' ) {
							if($statesQuery['stateprov_archive'][$i] != 1 && !$haveActiveState) {
								$haveActiveState=true;
							}
							$stateTable .= '
										 <tr>
											<td><input type="text" name="stateprov_name'.$stateCounter.'" value="'.$statesQuery['stateprov_name'][$i].'" size="18"></td>
											<td>
													<input type="hidden" name="stateprov_id'.$stateCounter.'" value="'.$statesQuery['stateprov_id'][$i].'">
													<input type="text" name="stateprov_code'.$stateCounter.'" value="'.$statesQuery['stateprov_code'][$i].'" size="6">
											</td>
											<td>'.$statesQuery['stateprov_ship_ext'][$i].'%</td>
											<td style="text-align:center">
												<input type="checkbox" class="formCheckbox radioGroup checkAllDel'.$statesQuery['country_id'][$i].'" name="stprv_Delete'.$stateCounter.'" rel="sp'.$statesQuery['stateprov_id'][$i].'" value="1"';
							if (ListFindNoCase($UsedStateList,$statesQuery['stateprov_id'][$i]) !=0 || ListFindNoCase($CustomerStateList,$statesQuery['stateprov_id'][$i])) { $stateTable .= ' disabled="disabled"'; }
							$stateTable .= '>';
							$stateTable .= '</td>
											<td style="text-align:center"><input type="checkbox" class="formCheckbox radioGroup checkAllArch'.$statesQuery['country_id'][$i].'" name="stateprov_archive'.$stateCounter.'" rel="sp'.$statesQuery['stateprov_id'][$i].'" value="1"';
							if ($statesQuery["stateprov_archive"][$i] == 1) { $stateTable .= ' checked="checked"'; } if (ListFindNoCase($CustomerStateList,$statesQuery['stateprov_id'][$i]) != 0) { $stateTable .= ' onclick="if(this.checked) return confirm(\'This state has customers associated with it. Are you sure you want to archive?\')"'; }
							$stateTable .= ' />';
							$stateTable .= '</td>
										</tr>
		';
							$stateCounter++;
						}
						$i++;
					}
					$i--;
					$stateTable .= '</table>';
				}
				if (!$haveActiveState) {
?>
															There are no active states for this country
<?php
				}
				echo $stateTable;
?>
													</td>
												</tr>
<?php
			} else {
				while ($i<$statesQuery["totalRows"] && $statesQuery['country_id'][$i] == $lastCountryID) {
					$i++;
				}
				$i--;
			}
			$countryCounter++;
?>
											</table>
<?php
		}
	}
?>
                                            <?php // hidden counter fields ?>
											<input type="hidden" name="countryCounter" value="<?php echo $countryCounter; ?>">
											<input type="hidden" name="stateCounter" value="<?php echo $stateCounter; ?>">
											<?php // submit button ?>
											<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
                                            <?php // if we have disabled delete boxes, explain ?>
											<span class="smallPrint" style="float:right;">
												Note: countries or regions with associated customer records cannot be deleted
											</span>
										</td>
									</tr>
									</tbody>
								</table>
<?php
	// end records table 
}
// /end country id 
?>
    
    				</form>
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
