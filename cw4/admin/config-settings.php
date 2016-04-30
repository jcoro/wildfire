<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: config-settings.php
File Date: 2012-07-03
Description: Creates dynamic 'config' form for custom application settings
These settings and related form elements are managed via config-groups.php
==========================================================
*/
require_once("Application.php");
// GLOBAL INCLUDES 
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("merchant,developer");
// PAGE PARAMS 
// default group ID 
if(!isset($_GET['group_id'])) { $_GET['group_id'] = ""; }
// current record params 
if(!isset($_ENV["request.cwpage"]["groupName"])) { $_ENV["request.cwpage"]["groupName"] = ""; }
if(!isset($_ENV["request.cwpage"]["currentRecord"])) { $_ENV["request.cwpage"]["currentRecord"] = $_GET['group_id']; }
if(!isset($_ENV["request.cwpage"]["userAlert"])) { $_ENV["request.cwpage"]["userAlert"] = ""; }
if(!isset($_ENV["request.cwpage"]["userConfirm"])) { $_ENV["request.cwpage"]["userConfirm"] = ""; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,userconfirm,useralert,clickadd,resetapplication");
// create the base url for sorting out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: get config group details 
$configGroupQuery = CWquerySelectConfigGroupDetails($_ENV["request.cwpage"]["currentRecord"],'');
// QUERY: get config items in this group 
$itemsQuery = CWquerySelectConfigItems($_ENV["request.cwpage"]["currentRecord"]);
// set page variables 
$_ENV["request.cwpage"]["groupName"] = $configGroupQuery['config_group_name'];
// if developer, show link to edit config group 
if($_ENV["request.cwpage"]["accessLevel"] == 'developer') {
	$_POST['groupdetailslink'] = ' &nbsp; <a href="config-group-details.php?group_id='.  $_GET['group_id'].'">Edit Config Group</a>';
} else {
	$_POST['groupdetailslink'] = '';
}
// use rich text editor  y/n 
if(!isset($_ENV["request.cwpage"]["rte"] )) { $_ENV["request.cwpage"]["rte"] = 0; }	
// set up config fields
if (isset($itemsQuery['config_type'][0])){
	$config = implode(',',$itemsQuery['config_type']);
	if((in_array('texteditor',$itemsQuery['config_type'])) ) {
		$rteFields = array();
		$_ENV["request.cwpage"]["rte"] = 1;
	}
}
// Only allow developer is showadmin is not 1 in config group query 
if($configGroupQuery['config_group_show_merchant'][0] != 1 && !ListFindNoCase('developer',$_SESSION["cw"]["accessLevel"])) {
	$_POST['userDenied'] = 1;
}
// config items form 
if(!isset($_POST['config_name'] )) { $_POST['config_name'] = ""; }
if(!isset($_POST['config_variable'] )) { $_POST['config_variable'] = ""; }
if(!isset($_POST['config_value'])) { $_POST['config_value'] = ""; }
// /////// 
// UPDATE CONFIG ITEMS 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['config_ID0'])) {
	$loopCt = 0;
	$updateCt = 0;
	$deleteCt = 0;
	// loop record ids, handle each one as needed 
	foreach ($_POST['recordIDlist'] as $key => $ID) {
		// UPDATE RECORDS 
		// param value for checkboxes 
		if(!isset($_POST['config_value'.($loopCt)])) { $_POST['config_value'.($loopCt)] = '0'; }
		$updateRecordID = CWqueryUpdateConfigItem(
						$_POST['config_ID'.$loopCt],
						$_ENV["request.cwpage"]["currentRecord"],
						$_POST['config_Variable'.$loopCt],
						$_POST['config_Name'.$loopCt],
						$_POST['config_value'.$loopCt]);
		;
		
		// if an error is returned from update query 
		if((substr($updateRecordID,0,2)) == '0-') {
			$updtrecordid = explode('-',$updateRecordID);
			$updtrecordid_last = $updtrecordid[count($$updtrecordid) - 1];
			$errorMsg = $updtrecordid_last;
			CWpageMessage("alert",$errorMsg);
		} else {
			// create application variable  
			$_ENV["application.cw"][$_POST['config_Variable'.$loopCt]] = $_POST['config_value'.$loopCt];
			// update complete: continue processing 
			$updateCt++;
		}
		// end error check 
		$loopCt++;
	}
	if ($updateCt > 0) CWsetApplicationRefresh();
	// set mandatory tax settings if not localtax 
	if (isset($_ENV["application.cw"]["taxCalctype"]) && strtolower($_ENV["application.cw"]["taxCalctype"]) != "localtax") {
		$setTaxRequirements = CWsetNonLocalTaxOptions();
		CWpageMessage("alert","Note: Tax settings adjusted to match ".$_ENV["application.cw"]["taxCalctype"]." requirements");
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars("userconfirm,useralert,method,resetapplication");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	$_ENV["request.cwpage"]["userconfirmText"] = '';
	if($updateCt > 0) {
		$_ENV["request.cwpage"]["userconfirmText"].= $_ENV["request.cwpage"]["groupName"][0].' Update Complete';
	}
	CWpageMessage("confirm",$_ENV["request.cwpage"]["userconfirmText"]);
	// return to page as submitted, clearing form scope 
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_ENV["request.cw"]["thisPage"]."?group_id=".$_ENV["request.cwpage"]["currentRecord"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."&resetapplication=".$_ENV["application.cw"]["storePassword"]."");
	exit;
}
// /////// 
// /END UPDATE CONFIG ITEMS 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Manage Config Group";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Site Settings: ".$_ENV["request.cwpage"]["groupName"][0]."";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Manage application controls and other custom settings: ".$_POST['groupdetailslink']."";
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"]."?group_id=".$_ENV["request.cwpage"]["currentRecord"]."";
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"] = 1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"] = 0;
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
// PAGE JAVASCRIPT ?>        
		<script type="text/javascript">
		jQuery(document).ready(function() {
			// show help in config items list
			jQuery('#recordForm a.showHelpLink').click(function() {
				jQuery(this).parents('tr').next('tr.helpText').toggle();
				return false;
				}).parents('th').css('cursor','pointer').click(function() {
				jQuery(this).children('a').click();
				});
			// avatax admin console link
			<?php if ($_GET["group_id"] == 5 && strtolower($_ENV["application.cw"]["taxCalctype"]) == 'avatax') { ?>
				var avaTaxUrl = 'https://admin-<?php if (stripos($_ENV["application.cw"]["avalaraUrl"], "development") !== false) { ?>development<?php } else { ?>avatax<?php } ?>.avalara.net/login.aspx';
				var avaTaxLink = '<a href="' + avaTaxUrl + '" target="_blank" title="' + avaTaxUrl + '">AvaTax Admin Console</a>';
				jQuery(avaTaxLink).appendTo('h3 + p.subText').css('margin-left','400px');
			<?php } ?>
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
include("cwadminapp/inc/cw-inc-admin-page-start.php");
// page start content / dashboard 
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
if(!$configGroupQuery['totalRows'] == 1 || isset($_POST['userDenied'])  ||  ($_GET['group_id']=='')  ) { ?>
                        	<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>Invalid selection. Choose a different page from the admin menu.</p>
<?php						
	// if a group record is found 
} else { 
	// /////// 
	// UPDATE CONFIG ITEMS 
	// /////// ?>
							<p>&nbsp;</p>
							<h3><?php echo $_ENV["request.cwpage"]["groupName"][0]; ?></h3>
<?php
	if(strlen(trim($configGroupQuery['config_group_description'][0]))) { 
?>
                            	<p class="subText"><?php echo $configGroupQuery['config_group_description'][0];?></p>
<?php							
	}
?>                            
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" name="recordForm" id="recordForm" method="post" class="CWobserve CWvalidate">
                            <input type="hidden" name="currentRecord" value="<?php echo $_ENV["request.cwpage"]["currentRecord"]; ?>" >
								<table class="CWstripe CWformTable wide">
                                    <?php // output form fields ?>
									<tbody>
<?php
	for($i=0; $i<$itemsQuery['totalRows']; $i++) {
		// if the field name is valid 
		if((strlen(trim($itemsQuery['config_name'][$i]))) && strlen($itemsQuery['config_name'][$i])) {
			
			// create per-item values  
			$fieldType = $itemsQuery['config_type'][$i];
			$fieldName = "config_value".$i;
			$fieldID = $fieldName;
			// vat/tax labels get special treatment 
			if($_ENV["request.cwpage"]["currentRecord"] == 5 && !$itemsQuery['config_variable'][$i] == 'taxSystemLabel') {
				$fieldLabel = str_replace('Tax',$_ENV["application.cw"]["taxSystemLabel"],$itemsQuery['config_variable'][$i]);
			} else {
				$fieldLabel = $itemsQuery['config_name'][$i];
			}
			$fieldValue = $itemsQuery['config_value'][$i];
			$fieldOptions = $itemsQuery['config_possibles'][$i];
			$fieldHelp = $itemsQuery['config_description'][$i];
			// required fields 
			if($itemsQuery['config_required'][$i] == true) {
				$fieldClass = 'required';
			} else {
				$fieldClass = '';
			}
			// email fields 
			if((substr($itemsQuery['config_name'][$i],0,-2)) == 'email') {
				$fieldClass = $fieldClass.' email';
			}
			$fieldSize = $itemsQuery['config_size'][$i];
			$fieldRows = $itemsQuery['config_rows'][$i];
			// SET APPLICATION VARIABLES: add variable to application scope 
			$_ENV["application.cw"][$itemsQuery['config_variable'][$i]] = $itemsQuery['config_value'][$i];
			if ($itemsQuery['config_variable'][$i] == "globalLocale") {
				$localeInfo = explode(",", $_ENV["application.cw"][$itemsQuery['config_variable'][$i]]);
				$_ENV["application.cw"]["globalLocaleCodes"] = $_ENV["application.cw"][$itemsQuery['config_variable'][$i]];
				$_ENV["application.cw"][$itemsQuery['config_variable'][$i]] = $localeInfo[0];
				for ($n=sizeof($localeInfo)-1; $n >= 1; $n--) {
					$_ENV["application.cw"]["globalLocaleCode"] = setlocale(LC_ALL, $localeInfo[$n]);
					$testData = localeconv();
					if (strpos($localeInfo[$n], "UTF8") !== false && !$testData["currency_symbol"]) {
						$localeInfo[$n] = str_replace("UTF8", "UTF-8", $localeInfo[$n]);
						$_ENV["application.cw"]["globalLocaleCode"] = setlocale(LC_ALL, $localeInfo[$n]);
					}
					if ($_ENV["application.cw"]["globalLocaleCode"]) break;
				}
				$fieldValue = str_replace(",", "^comma^", $fieldValue);
			}
			// set up list of text editor fields 
			if($itemsQuery['config_type'][$i] == 'texteditor' && $_ENV["request.cwpage"]["rte"]) {
				$rteFields[] = $fieldName;
			}
			// CUSTOM FIELDS can be managed here, reference config_variable value 
			// Get Themes : Admin style directory 
			if($itemsQuery['config_variable'][$i] == 'adminThemeDirectory') {
				// get directories within admin/css/cw-theme 
				$tempDir = preg_replace("/\/+$/", "/", expandPath("cw4/admin/theme")."/");
				$dir = opendir($tempDir);
				while (false !== ($files = readdir($dir))) {
					if ($files && $files != "." && $files != ".." && is_dir($tempDir . $files)) {
						$getThemes[] = $files;
					}
				}
				$fieldOptions = '';
				// set up list of theme names for selection 
				for($n=0; $n<count($getThemes); $n++) {
					$fieldOptions.= $getThemes[$n].'|'.$getThemes[$n].chr(10).chr(13);
				}
			}
			// FormField function creates field by type 
			$inputEl = CWformField(
							$fieldType,
							$fieldName,
							$fieldID,
							$fieldLabel,
							$fieldValue,
							trim($fieldOptions),
							$fieldClass,
							$fieldSize,
							$fieldRows);
?>														
											<tr class="config-<?php echo $itemsQuery["config_variable"][$i]; ?>">
												<th class="label iconCell" title="<?php echo trim($itemsQuery['config_description'][$i]); ?>">
<?php
			// help link 
			if(strlen(trim($itemsQuery['config_description'][$i]))) { ?>
                                                    	<a href="#" class="showHelpLink"><img width="16" height="16" align="absmiddle" alt="" title="<?php echo CWstringFormat($itemsQuery['config_description'][$i]); ?>" src="img/cw-help.png"></a>
<?php
			}
			// label 
			echo $fieldLabel;
			// hidden form inputs ?>													
													<input type="hidden" name="configItem<?php echo $i;?>" value="<?php echo $itemsQuery['config_id'][$i]; ?>">
													<input type="hidden" name="config_Name<?php echo $i;?>" value="<?php echo $itemsQuery['config_name'][$i]; ?>">
													<input type="hidden" name="config_Variable<?php echo $i;?>" value="<?php echo $itemsQuery['config_variable'][$i]; ?>">
													<input type="hidden" name="config_ID<?php echo $i;?>" value="<?php echo $itemsQuery['config_id'][$i]; ?>">
													<input type="hidden" name="config_show_merchant<?php echo $i;?>" value="<?php echo $itemsQuery['config_show_merchant'][$i]; ?>">
													<input name="recordIDlist[<?php echo $i;?>]" type="hidden" value="<?php $itemsQuery['config_id'][$i]; ?>">
												</th>
                                                                                                <td><?php
			// show the form input here 
			echo $inputEl;?></td>
											</tr>
											<tr class="helpText config-<?php echo $itemsQuery["config_variable"][$i]; ?>">
												<td colspan="2">
													<?php // hidden help text ?>
													<div>
														<p><strong><?php echo $itemsQuery['config_description'][$i];?></strong>
<?php
			if(ListFindNoCase('merchant,developer',$_ENV["request.cwpage"]["accessLevel"])) {
				echo '<br>$_ENV["application.cw"]["'.$itemsQuery['config_variable'][$i].'"]';
			}
?>														
														</p>
													</div>
												</td>
											</tr>
<?php                                            
		}
		// / end valid name check 
	}
?>                                    
									</tbody>
								</table>
								<?php if (isset($itemsQuery['config_type'][0])){
									// submit button - save changes 
									echo '<input name="SubmitUpdate" type="submit" class="submitButton" id="UpdateConfigItems" value="Save Changes">';
								} else {
									// message for empty groups
									echo '<p>No settings have been configured for this group</p>';
								}?>
							</form>
<?php
	// RTE rich text editor fields 
	if($_ENV["request.cwpage"]["rte"]) {
		include("cwadminapp/inc/cw-inc-admin-script-editor.php");
	}
	// /////// 
	// /END UPDATE CONFIG ITEMS 
	// /////// 
}
// /end valid config group record ?>                        
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