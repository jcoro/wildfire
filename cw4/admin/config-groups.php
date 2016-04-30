<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: config-groups.php
File Date: 2012-02-01
Description: Displays config groups management table
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("developer");
// PAGE PARAMS 
// default values for sort 
if(!isset($_GET['sortby'])) { $_GET['sortby'] = "config_group_sort"; }
if(!isset($_GET['sortdir'] )) { $_GET['sortdir'] = "asc"; }
// default form values 
if(!isset($_POST['config_group_name'])) { $_POST['config_group_name'] = ""; }
if(!isset($_POST['config_group_sort'])) { $_POST['config_group_sort'] = 1; }
if(!isset($_POST['config_group_show_merchant'])) { $_POST['config_group_show_merchant'] = 0; }
// default alerts 
if(!isset($_ENV["request.cwpage"]["userAlert"])) { $_ENV["request.cwpage"]["userAlert"] = ""; }
if(!isset($_ENV["request.cwpage"]["userConfirm"])) { $_ENV["request.cwpage"]["userConfirm"] = ""; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,view,userconfirm,useralert,clickadd,resetapplication");
// create the base url for sorting out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: get all config groups 
$configGroupsQuery = CWquerySelectConfigGroups(true);
// /////// 
// ADD NEW CONFIG GROUP 
// /////// 
if((isset($_POST['config_group_name'])) && strlen(trim($_POST['config_group_name']))) {
	// QUERY: insert new config group (name, sort, show merchant)
	// this query returns the new id, or a 0- error 
	$newConfigGroupID = CWqueryInsertConfigGroup(
							trim($_POST['config_group_name']),
							$_POST['config_group_sort'],
							$_POST['config_group_show_merchant']);
	// if no error returned from insert query 
	if(substr($newConfigGroupID,0,2) != '0-') {
		// update complete: return to page showing message 
		$_ENV["request.cwpage"]["userconfirmText"] = "";
		$_ENV["request.cwpage"]["userconfirmText"].= "Config Group ".$_POST['config_group_name']." Added&nbsp;&nbsp;<a href='config-group-details.php?group_id=".$newConfigGroupID."'>Manage Details</a>";
		CWpageMessage("confirm",$_ENV["request.cwpage"]["userconfirmText"]);
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&sortby=".$_GET["sortby"]."&sortdir=".$_GET["sortdir"]."&clickadd=1&resetapplication=".$_ENV["application.cw"]["storePassword"]);
		exit;
		// if we have an insert error, show message, do not insert 
	} else {
		$newconfiggrpid = explode('-',$newConfigGroupID); 
		$errorMsg = $newconfiggrpid[count($newconfiggrpid) - 1];
		CWpageMessage("alert",$errorMsg);
		$_GET['clickadd']= 1;
	}
	// /END duplicate/error check 
}
// /////// 
// /END ADD NEW CONFIG GROUP 
// /////// 
// /////// 
// UPDATE CONFIG GROUPS 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['config_group_id0'])) {
	$loopCt = 0;
	$updateCt = 0;
	$deleteCt = 0;
	// loop record ids, handle each one as needed 
	foreach ($_POST['recordIDlist'] as $key => $ID) {
		// param for show merchant checkbox 
		if(!isset($_POST['config_group_show_merchant'.$loopCt])) { $_POST['config_group_show_merchant'.$loopCt] = '0'; }
		// UPDATE RECORDS 
		// QUERY: update config group (ID, name, sort, show merchant)
		$updateRecordID = CWqueryUpdateConfigGroup(
								$_POST['config_group_id'.$loopCt],
								$_POST['config_group_name'.$loopCt],
								$_POST['config_group_sort'.$loopCt],
								$_POST['config_group_show_merchant'.$loopCt]);
		if(substr($updateRecordID,0,2) == '0-') {
			$updtrecordid = explode('-', $updateRecordID);
			$errorMsg = $updtrecordid[count($updtrecordid) - 1];
			// update complete: continue processing 
		} else {
			$updateCt++;
		}
		// end error check 
		$loopCt++;
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars("userconfirm,useralert,method,resetapplication");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	CWpageMessage("confirm"," Changes Saved");
	// return to page as submitted, clearing form scope 
	if (strpos($_ENV["request.cwpage"]["relocateURL"], "?") === false) $_ENV["request.cwpage"]["relocateURL"] .= "?";
	header("Location: ".$_ENV["request.cwpage"]["relocateURL"]."&userconfirm=".$_ENV["request.cwpage"]["userConfirm"][0]."&useralert=".$_ENV["request.cwpage"]["userAlert"]."");
	exit;
}
// /////// 
// /END UPDATE / DELETE CONFIG GROUPS 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title
//<title>
//
$_ENV["request.cwpage"]["title"] = "Manage Config Groups";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Config Group Management";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Manage configuration groups for custom variables";
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] =$_ENV["request.cw"]["thisPage"];
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
// PAGE JAVASCRIPT ?>        
		<script type="text/javascript">
		jQuery(document).ready(function() {
			// add new show-hide
			jQuery('form#addNewForm').hide();
			jQuery('a#showAddNewFormLink').click(function() {
				jQuery(this).hide();
				jQuery('form#addNewForm').show().find('input.focusField').focus();
				return false;
			});
			// auto-click the link if adding
<?php
if(isset($_GET['clickadd'])) { ?>
			jQuery('a#showAddNewFormLink').click();
<?php				
}
?>			
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
						<?php // link for add-new form ?>
						<div class="CWadminControlWrap">
							<strong><a class="CWbuttonLink" id="showAddNewFormLink" href="#">Add New Config Group</a></strong>
						</div>
<?php
// /////// 
// ADD NEW RECORD 
// /////// ?>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>&clickadd=1" class="CWvalidate" name="addNewForm" id="addNewForm" method="post">
							<p>&nbsp;</p>
							<h3>Add New Config Group</h3>
							<table class="CWinfoTable wide">
								<thead>
								<tr>
									<th width="165">Config Group Name</th>
									<th>Sort</th>
									<th width="120" style="text-align:center">Show Merchant</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td>
                                        <?php // group name ?>
										<div>
											<input name="config_group_name" type="text" class="{required:true} focusField" title="Config Group Name is required" value="<?php echo $_POST['config_group_name']; ?>" size="21">
										</div>
                                        <?php // submit button ?>
										<br>
										<input name="SubmitAdd" type="submit" class="CWformButton" id="SubmitAdd" value="Save New Config Group">
									</td>
                                    <?php // sort order ?>
									<td>
										<input name="config_group_sort" type="text" class="{required:true}" title="Sort Order is required" value="<?php echo $_POST['config_group_sort']; ?>" size="5" maxlength="7" onblur="extractNumeric(this,2,true);checkValue(this);">
									</td>
                                    <?php // show merchant y/n ?>
									<td style="text-align:center">
										<input name="config_group_show_merchant" type="checkbox" class="formCheckbox" value="1">
									</td>
								</tr>
								</tbody>
							</table>
						</form>
<?php
// /////// 
// /END ADD NEW RECORD 
// /////// 
// /////// 
// EDIT RECORDS 
// /////// ?>
						<p>&nbsp;</p>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" name="recordForm" id="recordForm" method="post" class="CWobserve">
<?php
// save changes / submit button 
if($configGroupsQuery['totalRows']) { ?>
                                	<div class="CWadminControlWrap">
									<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
									<div style="clear:right;"></div>
								</div>
								<?php // /END submit button ?>
								<h3>Active Config Groups</h3>
<?php								
}
if(!$configGroupsQuery['totalRows']) { ?>
                                	<p>&nbsp;</p>
                                    <p>&nbsp;</p>
                                    <p>&nbsp;</p>
                                    <p><strong>No Config Groups available.</strong> <br><br></p>
<?php									
	// if records found 
} else { ?>
                                	<input type="hidden" value="<?php echo $configGroupsQuery['totalRows'];?>" name="userCounter">
								<?php // save changes submit button ?>
								<div style="clear:right;"></div>
								<?php // Records Table ?>
								<table class="CWstripe CWsort wide" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
									<thead>
									<tr class="sortRow">
										<th class="noSort" width="50">Edit</th>
										<th class="config_group_name">Config Group Name</th>
										<th class="config_group_sort">Sort</th>
										<th width="152" style="text-align:center;">
											<input type="checkbox" class="checkAll" rel="showMerchant">Show Merchant
										</th>
									</tr>
									</thead>
									<tbody>
<?php
	for($i=0; $i<$configGroupsQuery['totalRows']; $i++) { ?>
										<tr>
										<?php // details link ?>
										<td style="text-align:center;"><a href="config-group-details.php?group_id=<?php echo $configGroupsQuery['config_group_id'][$i];?>" title="Manage Config Group details" class="detailsLink"><img src="img/cw-edit.gif" width="15" height="15" alt="Manage Config Group Details"></a></td>
                                        <?php // group name ?>
										<td>
											<input name="config_group_name<?php echo $i; ?>" type="text" class="required" title="Config Group name required" value="<?php echo $configGroupsQuery['config_group_name'][$i]; ?>" size="25" onblur="checkValue(this)">
											<?php // hidden fields used for processing update/delete ?>
											<input name="config_group_id<?php echo $i; ?>" type="hidden" value="<?php echo $configGroupsQuery['config_group_id'][$i]; ?>">
											<input name="recordIDlist[<?php echo $i; ?>]" type="hidden" value="<?php echo $configGroupsQuery['config_group_id'][$i]; ?>">
										</td>
										<?php // sort ?>
										<td>
											<input name="config_group_sort<?php echo $i; ?>" type="text" class="required sort" title="Sort order required" value="<?php echo $configGroupsQuery['config_group_sort'][$i]; ?>" maxlength="7" size="5" onblur="extractNumeric(this,2,true);checkValue(this)">
										</td>
										<?php // show merchant ?>
										<td style="text-align:center">
											<input type="checkbox" name="config_group_show_merchant<?php echo $i; ?>"
                                             <?php if($configGroupsQuery['config_group_show_merchant'][$i] != 0) {?>
                                             checked="checked"<?php } ?> value="1" class="formCheckbox showMerchant">
										</td>
									</tr>
<?php
	}
?>                                    
									</tbody>
								</table>
<?php
	// show the submit button here if we have a long list 
	if($configGroupsQuery['totalRows'] > 10) { ?>
										<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
<?php								
	}
}
// /END if records found ?>                            
						</form>
<?php
// /////// 
// /END EDIT RECORDS 
// /////// ?>
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
