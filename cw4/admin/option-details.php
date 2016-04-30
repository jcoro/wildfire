<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: option-details.php
File Date: 2012-05-01
Description: Displays option details for any option group, handles adding new option group
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
if(!isset($_ENV["request.cwpage"]["editMode"]) && isset($_GET['editmode'])) $_ENV["request.cwpage"]["editMode"]=$_GET['editmode'];
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth('manager,merchant,developer');
// PAGE PARAMS 
// default values for sort / active or archived
if(!isset($_GET['sortby'])) $_GET['sortby'] = "optiontype_sort";
if(!isset($_GET['sortdir'])) $_GET['sortdir'] = "asc";
if(!isset($_GET['view'])) $_GET['view'] = "active";
if(!isset($_GET['optiontype_id']) || !is_numeric($_GET['optiontype_id'])) $_GET['optiontype_id'] = 0;
// new vs. edit defaults 
if(!isset($_ENV["request.cwpage"]["editMode"])) $_ENV["request.cwpage"]["editMode"] = "edit";
// Param for delete allowed 
if(!isset($_ENV["request.cwpage"]["deleteOK"])) $_ENV["request.cwpage"]["deleteOK"] = 0;
if(!isset($_ENV["request.cwpage"]["relatedOrders"])) $_ENV["request.cwpage"]["relatedOrders"] = 0;
// Param for name of this option group 
if(!isset($_ENV["request.cwpage"]["groupName"])) $_ENV["request.cwpage"]["groupName"] = "";
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,view,userconfirm,useralert,clickadd");
// create the base url for sorting out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: get option group details 
$optionGroupQuery = CWquerySelectOptionGroupDetails($_GET['optiontype_id']);
// QUERY: get options in this group 
$optionsQuery = CWquerySelectGroupOptions($_GET['optiontype_id'], true);
// QUERY: get all orders using an option in this group 
$optionOrdersQuery = CWquerySelectOptionGroupOrders($_GET['optiontype_id'], 1);
// QUERY: get all orderSkus using an option in this group 
$optionOrderSkusQuery = CWquerySelectOptionGroupOrders($_GET['optiontype_id'], 0);
// QUERY: get all active option groups 
$optionGroupsActive = CWquerySelectStatusOptionGroups(0);
// QUERY: get all archived option groups: used for deleteok check 
$optionGroupsArchived = CWquerySelectStatusOptionGroups(1);
// form params 
if ($optionGroupQuery['totalRows'] && $optionGroupQuery['optiontype_archive'][0] == 1) {
	if(!isset($_POST['optiontype_archive'])) { $_POST['optiontype_archive'] = 1; }
} else {
	if(!isset($_POST['optiontype_archive'])) { $_POST['optiontype_archive'] = 0; }
}
if(!isset($_POST['option_Active'])) $_POST['option_Active'] = array();
if(!isset($_POST['option_archivePrev'])) $_POST['option_archivePrev'] = array();
if(!isset($_POST['deleteOption'])) $_POST['deleteOption'] = array();
// NEW VS. EDIT MODE
// EDIT  
// if one valid group is found and we have a valid ID:
//note joined query with a 'count' will always return at least 1 row 
if($optionGroupQuery['totalRows'] == 1 && $optionGroupQuery['optiontype_id'][0] > 0) {
	// set the name of this group 
	$_ENV["request.cwpage"]["groupName"] = $optionGroupQuery['optiontype_name'][0];
	// subheading 
	$_ENV["request.cwpage"]["subHead"] = "Manage ".$_ENV["request.cwpage"]["groupName"]." Options";
	if($optionGroupQuery['optiontype_archive'] == 1) {
		$_ENV["request.cwpage"]["subHead"] = $_ENV["request.cwpage"]["subHead"].'&nbsp;&nbsp;&nbsp;<em>Note: This option group is archived and will not be displayed</em>';
	}
	// count all the totals of the 'skucount' and 'prodcount' columns from queries 
	if (!isset($optionsQuery['optionSkuCount']) || !is_array($optionsQuery['optionSkuCount'])) $optionsQuery['optionSkuCount'] = array();
	if (!isset($optionGroupQuery['optionProdCount']) || !is_array($optionGroupQuery['optionProdCount'])) $optionGroupQuery['optionProdCount'] = array();
	$_ENV["request.cwpage"]["relatedSkus"] = array_sum($optionsQuery['optionSkuCount']);
	$_ENV["request.cwpage"]["relatedProducts"] = array_sum($optionGroupQuery['optionProdCount']);
	$_ENV["request.cwpage"]["relatedOrders"] = $optionOrdersQuery['totalRows'];
	// ok to delete? 
	if($_ENV["request.cwpage"]["relatedSkus"] + $_ENV["request.cwpage"]["relatedProducts"] < 1) {
		$_ENV["request.cwpage"]["deleteOK"] = 1;
	}
	// /end count totals 
	// ADD 
} else {
	// check for valid id: if id is 0 
	if($_GET['optiontype_id'] == 0) {
		$_ENV["request.cwpage"]["editMode"] = 'add';
		$_ENV["request.cwpage"]["subHead"] = "Add a new option group";
		// if id is not 0 but not found 
	} else {
		CWpageMessage("alert","Option Group ".$_GET['optiontype_id']." not found");
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location:options.php?&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."");
		exit;
	}
// end valid id 
}
// /END NEW VS. EDIT
// set up relocation url for confirmation 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("userconfirm,useralert");
// set up the base url 
$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// /////// 
// ADD NEW OPTION GROUP 
// /////// 
// the newoption fields are not available when first inserting a new group 
if((isset($_POST['optiontype_name'])) && strlen(trim($_POST['optiontype_name'])) && !isset($_POST['newoption_text'])) {
	$dupCheck = CWquerySelectOptionGroupDetails(0, trim($_POST['optiontype_name']));
	// QUERY: insert new option (name, sort, text, archive) 
	// this query returns the option group id, or an error like '0-fieldname' 
	$newOptionGroupID = CWqueryInsertOptionGroup(
							trim($_POST['optiontype_name']),
							trim($_POST['optiontype_sort']),
							trim($_POST['optiontype_text']),
							0);
	// if no error returned from insert query 
	if(!(substr($newOptionGroupID,0,2) == '0-')) {
		// update complete: return to page showing message 
		CWpageMessage("confirm","Option '".$_POST['optiontype_name']."' Added");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&optiontype_id=".$newOptionGroupID."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&clickadd=1&sortby=".$_GET['sortby']."&sortdir=".$_GET['sortdir']."");
		exit;
		// if we have an insert error, show message, do not insert 
	} else {
		$newOptid = explode('-', $newOptionGroupID);
		$newOptid_last = $newOptid[count($newOptid) - 1];
		$dupField = $newOptid_last;
		CWpageMessage("alert","Error: ".$dupField." already exists");
		$_GET['clickadd'] = 1;	
	}
	// /END duplicate/error check 
}
// /////// 
// /END ADD NEW OPTION GROUP 
// /////// 
// /////// 
// ADD NEW OPTION 
// /////// 
if((isset($_POST['newoption_name'])) && strlen(trim($_POST['newoption_name']))) {
	// QUERY: insert new option (name, group ID, sort, text, archive) 
	// this query returns the option id, or an error like '0-fieldname' 
	$newOptionID = CWqueryInsertOption(
						trim($_POST['newoption_name']),
						$_GET['optiontype_id'],
						trim($_POST['newoption_sort']),
						trim($_POST['newoption_text']),
						0);
	// if no error returned from insert query 
	if(!(substr($newOptionID,0,2) == '0-')) {
		// update complete: return to page showing message 
		CWpageMessage("confirm","Option '".$_POST['newoption_name']."' Added");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&sortby=".$_GET['sortby']."&sortdir=".$_GET['sortdir']."&clickadd=1");
                exit;
		// if we have an insert error, show message, do not insert 
	} else {
		$newOid = explode('-', $newOptionID);
		$newOid_last = $newOid[count($newOid) - 1];
		$dupField = $newOid_last;
		CWpageMessage("alert","Error: ".$dupField." already exists");
		$_GET['clickadd'] = 1;
	}
	// /end duplicate/error check 
	// /////// 
	// /END ADD NEW OPTION 
	// /////// 
	// /////// 
	// UPDATE OPTION GROUP 
	// /////// 
}
elseif((isset($_POST['optiontype_name'])) && strlen(trim($_POST['optiontype_name']))) {
	// ( other actions have been handled above, if the name field makes it this far, run update ) 
	$loopCt = 0;
	$updateCt = 0;
	$deleteCt = 0;
	$archiveCt = 0;
	$activeCt = 0;
	// verify numeric sort order 
	if(!isset($_POST['optiontype_sort']) || !is_numeric($_POST['optiontype_sort'])) $_POST['optiontype_sort'] = 0;
	// QUERY: update option group(id, name, sort, archive, text) 
	$updateGroup = CWqueryUpdateOptionGroup(
						$_GET['optiontype_id'],
						$_POST['optiontype_name'],
						$_POST['optiontype_sort'],
						$_POST['optiontype_archive'],
						$_POST['optiontype_text']);
	// /////// 
	// PROCESS ALL OPTIONS 
	// /////// 
	if(isset($_POST['optionIDlist'])) {
		// loop option IDs, handle each one as needed 
		foreach ($_POST['optionIDlist'] as $key => $id) {
			// /////// 
			// DELETE OPTIONS 
			// /////// 
			// if the option id is marked for deletion 
			if(in_array($_POST['option_id'.$loopCt], $_POST['deleteOption'])) {
				// QUERY: delete option (option id) 
				$deleteOption = CWqueryDeleteOption($id);
				$deleteCt = $deleteCt + 1;
				// if not deleting, update 
			} else {
				// /////// 
				// UPDATE OPTIONS 
				// /////// 
				// param for checkbox values 
				// verify numeric sort order 
				if(!is_numeric($_POST['option_sort'.$loopCt])) $_POST['option_sort'.$loopCt] = 0;
				// if the option id is marked for archiving
				//(note checkbox is for ACTIVE in this usage, so we archive the options NOT in the list) 
				if(!in_array($id, $_POST['option_Active'])) {
					$optionArchive = 1;
					// if it was not previously archived 
					if(!in_array($id, $_POST['option_archivePrev'])) {
						$archiveCt++;
					}
					// if not marked for archiving, activate 
				} else {
					$optionArchive = 0;
					if(in_array($id, $_POST['option_archivePrev'])) {
						$activeCt++;
					}
				}
				// QUERY: update option record (id, name, group, sort, archive, description) 
				$updateOptionID = CWqueryUpdateOption(
										$_POST['option_id'.$loopCt],
										$_POST['option_name'.$loopCt],
										$_GET["optiontype_id"],
										$_POST['option_sort'.$loopCt],
										$optionArchive,
										$_POST['option_text'.$loopCt]);
				// query checks for duplicate fields 
				if((substr($updateOptionID,0,2) == '0-')) {
					$errorName = $_POST['option_name'.$loopct];
					CWpageMessage("alert","Error: Name '".$errorName."' already exists");
					// update complete: continue processing 
				} else {
					$updateCt = $updateCt + 1;
				}
			// /END duplicate check 
			}
			// /END if deleting or updating 
			$loopCt++;
		}
		// return to page as submitted, clearing form scope 
		CWpageMessage("confirm","Changes Saved");
		$_ENV["request.cwpage"]["userAlertText"] = '';
		if($archiveCt > 0) {
			$_ENV["request.cwpage"]["userAlertText"].= $archiveCt." Option";
			if($archiveCt > 1) { 
				$_ENV["request.cwpage"]["userAlertText"].=  "s";
			}
			$_ENV["request.cwpage"]["userAlertText"].= " Deactivated";
		} else if ($activeCt > 0) {
			$_ENV["request.cwpage"]["userAlertText"].= $activeCt." Option";
			if($activeCt > 1) {
				$_ENV["request.cwpage"]["userAlertText"].= "s";
			}
			$_ENV["request.cwpage"]["userAlertText"].= " Activated";
		}
		if($deleteCt > 0) {
			$_ENV["request.cwpage"]["userAlertText"].= $deleteCt." Option";
			if($deleteCt > 1) { 
				$_ENV["request.cwpage"]["userAlertText"].=  "s";
			}
			$_ENV["request.cwpage"]["userAlertText"].= " Deleted";
		}
		CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cwpage"]["relocateURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."");
		exit;
	}
	else {
		//added for updated optiontype information
		CWpageMessage("confirm","Changes Saved");
		$_ENV["request.cwpage"]["userAlertText"] = '';
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cwpage"]["relocateURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."");
		exit;
	}
}
// /////// 
// /END ADD/UPDATE 
// /////// 
// /////// 
// DELETE OPTION GROUP 
// /////// 
if(isset($_GET['deleteGroup'])) {
	if(!isset($_GET["returnUrl"]) || $_GET["returnUrl"] == '') { $_GET["returnUrl"] = "options.php?useralert=Unable to delete: option group ".$_GET['deleteGroup']." not found"; }
	$_ENV["request.cwpage"]["returnUrl"] = $_GET["returnUrl"];
	try {
		// QUERY: delete group record (id from url)
		$deleteOrder = CWqueryDeleteOptionGroup($_GET['deleteGroup']);
		// handle errors 
	}
	catch(Exception $e) {
		CWpageMessage("alert","Deletion Error: ".$e->getMessage()."");
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		$_ENV["request.cwpage"]["returnUrl"] = "".$_ENV["request.cw"]["thisPage"]."?optiontype_id=".$_GET['optiontype_id']."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."";
	}
	header("Location: ".$_ENV["request.cwpage"]["returnUrl"]);
	exit;
}
// /////// 
// /END DELETE OPTION GROUP 
// /////// 
// default values for form inputs, persist entered values when returning insert errors 
if(!isset($_POST['optiontype_name']) || $_POST['optiontype_name'] == '') { $_POST['optiontype_name'] = ((isset($optionGroupQuery['optiontype_name'][0])) ? $optionGroupQuery['optiontype_name'][0] : "" ); }
if(!isset($_POST['optiontype_text']) || $_POST['optiontype_text'] == '') { $_POST['optiontype_text'] = ((isset($optionGroupQuery['optiontype_text'][0])) ? $optionGroupQuery['optiontype_text'][0] : "" ); }
if(!isset($_POST['optiontype_sort']) || $_POST['optiontype_sort'] == '') { $_POST['optiontype_sort'] = ((isset($optionGroupQuery['optiontype_sort'][0])) ? $optionGroupQuery['optiontype_sort'][0] : "" ); }
if(!strlen(trim($_POST['optiontype_sort']))) $_POST['optiontype_sort'] = 0;
// PAGE SETTINGS 
// Page Browser Window Title
$_ENV["request.cwpage"]["title"] = "Manage Product Options";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Product Option Management";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = $_ENV["request.cwpage"]["subHead"];
// current menu marker 
if($_ENV["request.cwpage"]["editMode"] == 'add') {
	$_ENV["request.cwpage"]["currentNav"] = 'option-details.php';
} else {
	$_ENV["request.cwpage"]["currentNav"] = 'options.php';
}
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"] = 1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"] = 1;
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("userconfirm,useralert");
// set up the base url 
$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
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
		// text editor javascript 
		if($_ENV["application.cw"]["adminEditorEnabled"] && $_ENV["application.cw"]["adminEditorCategoryDescrip"]) {
			include("cwadminapp/inc/cw-inc-admin-script-editor.php");
		}
		// PAGE JAVASCRIPT ?>        
		<script type="text/javascript">
		// confirm deletion
		function confirmDelete(prodCt,orderCt,newLocation) {
		// build the confirmation message
			if (prodCt == 1) {var prodLabel = 'product'} else {var prodLabel = 'products'};
			if (orderCt == 1) {var orderLabel = 'order'} else {var orderLabel = 'orders'};
			var confirmA = "This option group and all associated options will be permanently removed";
			var confirmB = ". \nCurrently used on " + prodCt + " " + prodLabel;
			var confirmC = ". \nAssociated with " + orderCt + " " + orderLabel;
			var confirmD = ".\nContinue?";
		// if this option group has products, show the product count in the alert
			var confirmStr = confirmA;
			if (prodCt > 0) {confirmStr = confirmStr + confirmB};
			if (orderCt > 0) {confirmStr = confirmStr + confirmC};
			confirmStr = confirmStr + confirmD;
			deleteConfirm = confirm(confirmStr);
			// if cancelled return false
			if(deleteConfirm) {
			window.location = newLocation;
			};
		};
		// end if product
		// select option group changes page location
		function groupSelect(selBox) {
		 	var viewID = jQuery(selBox).val();
			if (viewID >= 1) {
		 	window.location = '<?php echo $_ENV["request.cw"]["thisPage"]; ?>?optiontype_id=' + viewID;
			}
		};
		<?php // show/hide new option form, disable name input ?>
		// initialize jQuery
		jQuery(document).ready(function() {
			// add new option
			jQuery('#showNewOptionFormLink').click(function() {
				jQuery('#addOptionTable').show();
				jQuery(this).hide().siblings('a').show();
				jQuery('#newoption_name').removeAttr('disabled').focus();
				jQuery('#UpdateOptionGroup').hide();
				return false;
			});
			// cancel new option
			jQuery('#hideNewOptionFormLink').click(function() {
				jQuery('#addOptionTable').hide();
				jQuery('#newoption_name').attr('disabled','disabled');
				jQuery(this).hide().siblings('a').show();
				jQuery('#UpdateOptionGroup').show();
				return false;
			});

			// click button from url
			<?php if (isset($_GET["clickadd"]) || (!$optionsQuery["totalRows"] && $_ENV["request.cwpage"]["editMode"] != 'add')) { ?>
			jQuery('#showNewOptionFormLink').click();
			<?php } ?>
			// hide add new form when editing table below
			jQuery('table#productOptionsTable input, table#productOptionsTable textarea').focus(function() {
				jQuery('#hideNewOptionFormLink').click();
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
<?php 			include("cwadminapp/inc/cw-inc-admin-nav.php"); ?> 
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
						// /////// 
						// EDIT OPTION GROUP AND OPTIONS 
						// /////// ?>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate CWobserve" name="optionsForm" id="optionsForm" method="post">
							<?php // option group table ?>
							<table class="CWinfoTable wide">
								<thead>
								<tr class="headerRow">
									<th>Option Group Name</th>
									<th>Description</th>
									<th width="55">Order</th>
<?php
									if($_ENV["request.cwpage"]["editMode"] != 'add') { ?>
										<th width="55">Status</th> 
<?php
									}
?>                                    
								</tr>
								</thead>
								<tbody>
							
								<tr>
									<?php // option group name ?>
									<td>
<?php
										// if adding new, allow for input here 
										if($_ENV["request.cwpage"]["editMode"] =='add') { ?>
											<input name="optiontype_name" type="text" size="17"  class="required" value="<?php $_POST['optiontype_name']?>" title="Option Group Name is required" onBlur="checkValue(this)">
<?php
										} else {
											// if editing, show selection list 
											// select changes to new page ?>
                                                  <select name="optiontype_nameSelector" id="optionGroupSelect" onchange="groupSelect(this);">
                                        <?php
											if($optionGroupsActive['totalRows'] > 0) {
												echo '<optgroup label="Active">';
											}
											for($i=0; $i<$optionGroupsActive['totalRows']; $i++) { ?>
													<option value="<?php echo $optionGroupsActive['optiontype_id'][$i]; ?>"<?php if($optionGroupsActive['optiontype_id'][$i] == $_GET['optiontype_id']) { ?> selected="selected"<?php } ?>><?php echo $optionGroupsActive['optiontype_name'][$i]; ?></option>
										<?php
											}
											if($optionGroupsActive['totalRows'] > 0) {
												echo '</optgroup>';
											}
											if($optionGroupsArchived['totalRows'] > 0) {
												echo '<optgroup label="Archived">';
											}
											for($i=0; $i<$optionGroupsArchived['totalRows']; $i++) { ?>
													<option value="<?php echo $optionGroupsArchived['optiontype_id'][$i]; ?>"<?php if($optionGroupsArchived['optiontype_id'][$i] == $_GET['optiontype_id']) { ?> selected="selected"<?php } ?>><?php echo $optionGroupsArchived['optiontype_name'][$i]; ?></option>
										<?php
											}
											if($optionGroupsArchived['totalRows'] > 0) {
												echo '</optgroup>';
											}
											?>
											      </select>
											      <br>
											<span class="smallPrint"><a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>">Add New Group</a></span>
											<?php // name can't be edited, hidden ?>
											<input name="optiontype_name" type="hidden" value="<?php echo $_POST['optiontype_name']; ?>">
<?php
										}
?>
									</td>
									<?php // text description ?>
									<td>
										<textarea name="optiontype_text" cols="45" rows="1"><?php echo $_POST['optiontype_text']; ?></textarea>
									</td>
									<?php // sort order ?>
									<td><input name="optiontype_sort" type="text" size="3" maxlength="7" class="required sort" title="Sort Order is required" value="<?php echo $_POST['optiontype_sort']; ?>" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)"></td>
									<?php // active yes/no ?>
<?php
								if($_ENV["request.cwpage"]["editMode"] != 'add') { ?>
										<td>
                                        <?php
											// can only be archived if not attached to products 
											if($_ENV["request.cwpage"]["relatedProducts"] == 0) { ?>
												<select name="optiontype_archive">
<?php 
													if($_POST['optiontype_archive'] != 1) { ?>
														<option value="0" selected="selected">Active</option>
														<option value="1">Archived</option>
<?php 
													} else { ?>
														<option value="0" >Active</option>
														<option value="1" selected="selected">Archived</option>
<?php 												}
?>                                                        
												</select>
<?php 
											} else { ?>
                                                
												Active
												<input type="hidden" name="optiontype_archive" value="<?php echo $_POST['optiontype_archive']; ?>">
<?php
											}
?>                                                
										</td>
<?php 							}
?>                                        
								</tr>
                                <?php
								// /////// 
								// END ADD / EDIT OPTION GROUP 
								// /////// 
								// /////// 
								// EDIT OPTIONS 
								// /////// 
								// if editing 
								if($_ENV["request.cwpage"]["editMode"] != 'add') { ?>
									<tr>
										<td colspan="4">
											<div class="CWformButtonWrap">
												<?php // save changes / submit button ?>
												<input name="UpdateOptionGroup" type="submit" class="CWformButton" id="UpdateOptionGroup" value="<?php if($_ENV["request.cwpage"]["editMode"] == 'add') {?>Save Details<?php } else { ?>Save Changes<?php } ?>">
<?php
												// delete button 
												if($_ENV["request.cwpage"]["deleteOK"]) { 
													$confirmStr = CWurlSafe("Option Group Deleted");
?>
													<a href="#" onClick="confirmDelete(<?php echo $_ENV["request.cwpage"]["relatedProducts"]; ?>,<?php echo $_ENV["request.cwpage"]["relatedOrders"]; ?>,'<?php echo $_SERVER['SCRIPT_NAME']."?deleteGroup=".$_GET['optiontype_id']."&returnUrl=options.php?userconfirm=".$confirmStr.""; ?>')" class="CWbuttonLink deleteButton">Delete Option Group</a>													
<?php
												} else { ?>
													<span style="float: right;" class="smallPrint">Option group in use, cannot be deleted.</span>
<?php
												}
												// if no options exist, show alert ?>
<?php
												if(!$optionsQuery['totalRows']) { ?>
													<div class="clear"></div>
													<div class="alert clear">Create at least one option to activate this option group</div>
<?php
												}
?>                                                    
												<a class="CWbuttonLink" href="#" id="showNewOptionFormLink" style="display: block;">Add New Option</a>
												<a class="CWbuttonLink" href="#" id="hideNewOptionFormLink" style="display: none;">Cancel New Option</a>
											</div>
                                            <?php
											// /////// 
											// ADD NEW OPTION 
											// /////// ?>
											<table class="CWsort CWstripe" id="addOptionTable" style="display:none;">
												<thead>
												<tr class="sortRow">
													<th width="185" class="noSort">Option</th>
													<th class="noSort">Description</th>
													<th width="55" class="noSort">Order</th>
												</tr>
												</thead>
												<tbody>
												<tr>
													<?php // option name: loaded as disabled, show new option link enables it for validation ?>
													<td>
														<div>
															<input name="newoption_name" id="newoption_name" type="text" size="17" disabled="disabled" class="required" value="" title="Option Name is required">
														</div>
														<br>
														<input name="AddOption" type="submit" class="CWformButton" id="AddOption" value="Add Option">
													</td>
													<?php // option text ?>
													<td>
														<textarea name="newoption_text" cols="45" rows="1"></textarea>
													</td>
													<?php // order ?>
													<td>
														<input name="newoption_sort" type="text" size="3" maxlength="7" class="required sort" title="Sort Order is required" value="1" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">
													</td>
												</tr>
												</tbody>
											</table>
                                            <?php
											// /////// 
											// END ADD NEW OPTION 
											// /////// 
											// /////// 
											// EDIT OPTIONS 
											// /////// 
											// show this table if options exist 
											if($optionsQuery['totalRows']) {
												// options table ?>
												<table class="CWsort CWstripe" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" id="productOptionsTable">
													<thead>
													<tr class="sortRow">
														<th width="185" class="option_name">Option</th>
														<th class="option_text">Description</th>
														<th width="55" class="option_sort">Order</th>
														<th width="55" class="noSort">Active</th>
														<th width="55" class="noSort">Delete</th>
													</tr>
													</thead>
													<tbody>
<?php
													$disabledDeleteCt = 0;
													$disabledArchiveCt = 0;
													for($i=0; $i<$optionsQuery['totalRows']; $i++) { ?>
														<tr>
														<?php // option name and hidden fields ?>
														<td>
															<input name="option_name<?php echo $i; ?>" type="text" size="17" class="required" value="<?php echo $optionsQuery['option_name'][$i]; ?>" title="Option Name is required" onblur="checkValue(this)">
															<input name="option_id<?php echo $i; ?>" type="hidden" value="<?php echo $optionsQuery['option_id'][$i]; ?>">
															<input name="optionIDlist[<?php echo $i; ?>]" type="hidden" value="<?php echo $optionsQuery['option_id'][$i]; ?>">
														</td>
														<?php // option text ?>
														<td>
															<textarea name="option_text<?php echo $i; ?>" cols="45" rows="1"><?php echo $optionsQuery['option_text'][$i]; ?></textarea>
														</td>
														<?php // option order ?>
														<td>
															<input name="option_sort<?php echo $i; ?>" type="text" size="3" maxlength="7" class="required sort" title="Sort Order is required" value="<?php echo $optionsQuery['option_sort'][$i]; ?>" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">
														</td>
														<?php // archive ?>
														<td style="text-align:center">
															<?php // if we have skus related to this option, disable archive, use hidden field to pass in id to active list ?>
															<input type="checkbox" name="option_Active[<?php echo $i; ?>]" class="formCheckbox radioGroup" rel="group<?php echo $i; ?>" value="<?php echo $optionsQuery['option_id'][$i]; ?>"<?php if($optionsQuery['option_archive'][$i] != 1) {?> checked="checked"<?php } ?><?php
															if($optionsQuery['optionSkuCount'][$i] > 0) { 
																$disabledArchiveCt++;
																?> disabled="disabled"<?php
															} ?>>
<?php
															if($optionsQuery['optionSkuCount'][$i] > 0) { ?>
																<input type="hidden" name="option_Active[<?php echo $i; ?>]" value="<?php echo $optionsQuery['option_id'][$i]; ?>">
<?php
															}
															// hidden field used to determine new archive/activations 
															if($optionsQuery['option_archive'][$i] == 1) { ?>
																<input type="hidden" name="option_archivePrev[<?php echo $i; ?>]" value="<?php echo $optionsQuery['option_id'][$i]; ?>">
<?php
															}
?>                                                                
														</td>
														<?php // delete ?>
														<td style="text-align:center">
															<?php // if we have skus or orders related to this option, disable delete ?>
															<input type="checkbox" name="deleteOption[<?php echo $i; ?>]" id="confirmBox<?php echo $optionsQuery['option_id'][$i]; ?>" value="<?php echo $optionsQuery['option_id'][$i]; ?>" class="formCheckbox<?php if($optionsQuery['optionSkuCount'][$i] > 0) {?>deleteDisabled<?php } ?> radioGroup" rel="group<?php echo $i; ?>"<?php
															if (!isset($optionOrderSkusQuery['sku_option2option_id']) || !is_array($optionOrderSkusQuery['sku_option2option_id'])) $optionOrderSkusQuery['sku_option2option_id'] = array();
															if($optionsQuery['optionSkuCount'][$i] > 0 || in_array($optionsQuery["option_id"][$i], $optionOrderSkusQuery['sku_option2option_id'])) {
																$disabledDeleteCt = $disabledDeleteCt + 1;
																?> disabled="disabled"<?php
															} ?>>
														</td>
													</tr>
<?php
													}
?> 
											    </tbody>
												</table>
                                                 <?php
													// if we have disabled delete boxes, explain 
													if($disabledDeleteCt || $disabledArchiveCt) { ?>
												<span class="smallPrint" style="float:right;">
                                                 <?php
													if($disabledDeleteCt) { ?>
                                                    	Note:&nbsp;&nbsp;options with associated products or orders cannot be deleted	
<?php
													}
													// if we have disabled archive boxes 
													if($disabledArchiveCt) {
														if($disabledDeleteCt) { ?>
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
														} else { ?>
															Note:
<?php
														} ?>
                                                        options with associated products cannot be archived	
<?php
													}
?> 
												</span>
                                                <?php
													}
												// /END options table 
											}
											// end if no options exist ?>
										</td>
									</tr>                                              				
<?php
								}
								// /////// 
								// /END EDIT OPTIONS 
								// /////// ?>
								</tbody>
							</table>
                            <?php
							// /end optiongroup table 
							// if adding new 
							if($_ENV["request.cwpage"]["editMode"] == 'add') {
                            	// save changes / submit button ?>
								<div class="CWformButtonWrap">
									<input name="UpdateOptionGroup" type="submit" class="CWformButton" id="UpdateOptionGroup" value="<?php if($_ENV["request.cwpage"]["editMode"] == 'add') { ?>Save Details <?php } else { ?>Save Changes<?php } ?>">
								</div>
<?php
							}
?>                            
						</form>
                        <?php
						// /////// 
						// /END EDIT OPTION GROUP AND OPTIONS 
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
