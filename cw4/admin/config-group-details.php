<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: config-group-details.php
File Date: 2012-05-22
Description: Displays details and config items for any config group, handles adding new config item
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"]=CWauth("developer");
// PAGE PARAMS 
// default values for sort 
if(!(isset($_GET['sortby']))) $_GET['sortby']='config_sort';
if(!(isset($_GET['sortdir']))) $_GET['sortdir']='asc';
// default group ID 
if(!(isset($_GET['group_id']))) $_GET['group_id']=0;
// current record params 
if(!isset($_ENV["request.cwpage"]["groupName"])) $_ENV["request.cwpage"]["groupName"]="";
if(!isset($_ENV["request.cwpage"]["currentRecord"])) $_ENV["request.cwpage"]["currentRecord"]=$_GET['group_id'];
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("view,userconfirm,useralert,clickadd,sortby,sortdir");
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"]=CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: get config group details 
$configGroupQuery = CWquerySelectConfigGroupDetails($_GET['group_id'],'');
// if none found, send back to config groups page 
if($configGroupQuery['totalRows'] < 1) {
	header("Location: config-groups.php");
}
// QUERY: get config items in this group 
$itemsQuery = CWquerySelectConfigItems($_GET['group_id']);
// make query sortable 
// QUERY: get all  config groups 
$configGroupsListQuery = CWquerySelectConfigGroups();
// set page variables 
$_ENV["request.cwpage"]["groupName"] = $configGroupQuery['config_group_name'][0];
// use rich text editor  y/n 
if(!(isset($_ENV["request.cwpage"]["rte"])))
	$_ENV["request.cwpage"]["rte"]=0;
if(strpos($itemsQuery['config_type'][0],'texteditor') !== false && $_ENV["application.cw"]["adminEditorEnabled"] != 0) {
	$rteFields = array();
	$_ENV["request.cwpage"]["rte"]=1;
}
// form values 
// config group form 
if(!(isset($_POST['config_group_name']))) $_POST['config_group_name']=$_ENV["request.cwpage"]["groupName"];	
if(!(isset($_POST['config_group_sort']))) $_POST['config_group_sort']=$configGroupQuery['config_group_sort'][0];
if(!(isset($_POST['config_group_show_merchant']))) $_POST['config_group_show_merchant']=0;
// config items form 
if(!(isset($_POST['config_name']))) $_POST['config_name']='';
if(!(isset($_POST['config_variable']))) $_POST['config_variable']='';
if(!(isset($_POST['config_sort']))) $_POST['config_sort']=1;
if(!(isset($_POST['config_type']))) $_POST['config_type']='';
if(!(isset($_POST['config_value']))) $_POST['config_value']='';
if(!(isset($_POST['config_possibles']))) $_POST['config_possibles']='';
if(!(isset($_POST['config_description']))) $_POST['config_description']='';
if(!(isset($_POST['config_size']))) $_POST['config_size']=35;
if(!(isset($_POST['config_rows']))) $_POST['config_rows']=5;
if(!(isset($_POST['config_reqd']))) $_POST['config_reqd']=0;
if(!(isset($_POST['config_protected']))) $_POST['config_protected']=0;
if(!(isset($_POST['config_show_merchant']))) $_POST['config_show_merchant']=0;
// param for delete checkbox 
if(!(isset($_POST['deleteRecord']))) $_POST['deleteRecord'] = array();
if(!(isset($fieldProtectCt))) $fieldProtectCt=0;
// param for field type selector in list view
if(!(isset($_ENV["application.cw"]["configSelectType"]))) $_ENV["application.cw"]["configSelectType"]=1;
// /////// 
// UPDATE CONFIG GROUP 
// /////// 
// if the update_ID was submitted, and matches the url ID 
if(isset($_POST['update_ID']) && $_POST['update_ID'] == $_ENV["request.cwpage"]["currentRecord"]) {
// QUERY: update config group (ID, name, sort, show merchant)
	
	$updateRecordID = CWqueryUpdateConfigGroup(
						$_POST['update_ID'],
						$_POST['config_group_name'],
						$_POST['config_group_sort'],
						$_POST['config_group_show_merchant']
						);
	
// if no error returned from update query 
	
	
	if(!(substr($updateRecordID,0,2) == '0-')) {
	// update complete: return to page showing message 
		CWpageMessage("confirm","Config Group Saved");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"] . '&userconfirm=' . CWurlSafe($_ENV["request.cwpage"]["userConfirm"]) . '&sortby='  .$_GET['sortby'] . '&sortdir=' . $_GET['sortdir'] . '&resetapplication=' . $_ENV["application.cw"]["storePassword"]);
		exit;
	// if we have an insert error, show message, do not insert 
	} else {
		$newVarForList1=explode('-',$updateRecordID);
		CWpageMessage("alert",$newVarForList1[count($newVarForList1)-1]);
	}
// end error check 
}

// /////// 
// /END UPDATE CONFIG GROUP 
// /////// 
// /////// 
// ADD NEW CONFIG ITEM 
// /////// 
if(isset($_POST['config_name']) && isset($_POST['AddNewConfigurationItem'])) {
// QUERY: insert new config item (
//group ID, variable, name, value, type, description,
//possibles, showmerchant, sort, size, rows, protected, required)
// this query returns the new id, or a 0- error 
	 $newRecordID = CWqueryInsertConfigItem(
						$_ENV["request.cwpage"]["currentRecord"],
						trim($_POST['config_variable']),
						trim($_POST['config_name']),
						trim($_POST['config_value']),
						trim($_POST['config_type']),
						trim($_POST['config_description']),
						($_POST['config_possibles']),
						$_POST['config_show_merchant'],
						$_POST['config_sort'],
						$_POST['config_size'],
						$_POST['config_rows'],
						$_POST['config_protected'],
						$_POST['config_reqd']
						);
	// if no error returned from insert query 
	if(!(substr($newRecordID,0,2) == "0-")) {
		// update complete: return to page showing message 
		CWpageMessage("confirm","Config Item " . trim($_POST['config_name']). " Added");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"] . '&userconfirm=' . CWurlSafe($_ENV["request.cwpage"]["userConfirm"]) . '&sortby=' . $_GET['sortby'] . '&sortdir=' . $_GET['sortdir'] . '&resetapplication=' . $_ENV["application.cw"]["storePassword"]);
		exit;
	// if we have an insert error, show message, do not insert 
	} else {
		$newVarForList2=explode('-',$newRecordID);
		$errorMsg = $newVarForList2[count($newVarForList2) -1];	
		CWpageMessage("alert","Error: " .$errorMsg);
		$_GET['clickadd']==1;
	}
// /END duplicate/error check 
}
// /////// 
// /END ADD NEW CONFIG ITEM 
// /////// 
// /////// 
// UPDATE/DELETE CONFIG ITEMS 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['config_ID1'])) {
	$loopCt=1;
	$updateCt=0;
	$deleteCt=0;
	// loop record ids, handle each one as needed 
	foreach ($_POST["recordIDlist"] as $key => $id) {		
		// DELETE CONFIG ITEMS 
		// if the record ID is marked for deletion 
		if(in_array($_POST['config_ID'.$loopCt],$_POST['deleteRecord'])) {
		// QUERY: delete record (record id) : returns a message 
			$deleteRecord= CWqueryDeleteConfigItem($id);
			if(substr($deleteRecord,0,2) != '0-') {
				CWpageMessage("alert",$deleteRecord);
			} else {	
				$deleteCt++;
			}
		// if not deleting, update 
		} else {
		// UPDATE RECORDS 
		// param value for checkboxes 
			if(!(isset($_POST['config_value' . $loopCt]))) $_POST['config_value' . $loopCt] = "";
			// QUERY: update config item (
			//item ID, group ID, variable, name, value, type, description,
			//possibles, showmerchant, sort)
			$updateRecordID = CWqueryUpdateConfigItem(
								$_POST['config_ID'.$loopCt],
								$_ENV["request.cwpage"]["currentRecord"],
								$_POST['config_variable' . $loopCt],
								$_POST['config_name' .$loopCt],
								$_POST['config_value' . $loopCt],
								$_POST['config_type' . $loopCt],
								$_POST['config_description' . $loopCt],
								$_POST['config_possibles' . $loopCt],
								$_POST['config_show_merchant' . $loopCt],
								$_POST['config_sort' . $loopCt]);
			// if an error is returned from update query 
			if(substr($updateRecordID,0,2) =='0-') {
				$newVarForList3=explode('-',$updateRecordID);
				$errorMsg = $newVarForList3[count($newVarForList3)-1];
				CWpageMessage("alert",$errorMsg);
			} else {
				// if no error 
				// create application variable  
				$_POST['config_variable'.$loopCt] = $_POST["config_value".$loopCt];
				// update complete: continue processing 
				$updateCt++;
			}
			// /END delete vs. update 
		}
		// end error check 
		$loopCt++;
	}
	// set mandatory tax settings if not localtax 
	if (isset($_ENV["application.cw"]["taxCalctype"]) && strtolower($_ENV["application.cw"]["taxCalctype"]) != "localtax") {
		$setTaxRequirements = CWsetNonLocalTaxOptions();
		CWpageMessage("alert","Note: Tax settings adjusted to match #application.cw.taxCalcType# requirements");
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars("userconfirm,useralert,method,resetapplication");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	CWpageMessage("confirm","Changes Saved");
	// save alert text 
	$_ENV["request.cwpage"]["userAlertText"]='';
	if($deleteCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"] .= $deleteCt .' Record';
		if($deleteCt > 1) $_ENV["request.cwpage"]["userAlertText"] .= 's';
		$_ENV["request.cwpage"]["userAlertText"] .= ' Deleted';
	}
	CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
// return to page as submitted, clearing form scope 
	header("Location: " . $_ENV["request.cwpage"]["relocateURL"] . '&userconfirm=' . CWurlSafe($_ENV["request.cwpage"]["userConfirm"]) . '&useralert=' . CWurlSafe($_ENV["request.cwpage"]["userAlert"]) . '&resetapplication='. $_ENV["application.cw"]["storePassword"]);
	exit;
}
// /////// 
// /END UPDATE / DELETE CONFIG ITEMS 
// /////// 
// /////// 
// DELETE CONFIG GROUP 
// /////// 
if(isset($_POST['SubmitDelete'])) {
	// verify no items exist for this group 
	if($itemsQuery['totalRows'] > 0) {
		CWpageMessage("alert","This group contains " . $itemsQuery['totalRows'] .' active config items and cannot be deleted');
	} else {
		// delete group 
		// QUERY: delete group (group id) 
		$deleteGroup = CWqueryDeleteConfigGroup($_POST['deleteGroupID']);
		header("Location: config-groups.php?useralert=Config Group " . $_ENV["request.cwpage"]["groupName"] . ' deleted&resetapplication=' . $_POST['storePassword'] );
		exit;
	}
}
// /////// 
// /END DELETE CONFIG GROUP 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"]="Manage config Group";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"]='Manage Config Group: '. $_ENV["request.cwpage"]["groupName"];
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"]='Create Custom variables and manage variable Values '.'<a href="config-settings.php?group_id=' . $_GET['group_id'] .'">View Settings Page</a>';
// current menu marker 
$_ENV["request.cwpage"]["currentNav"]='config-groups.php';
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
// PAGE JAVASCRIPT 
?>
		<script type="text/javascript">
		// select option group changes page location
		function groupSelect(selBox) {
		 	var viewID = jQuery(selBox).val();
			if (viewID >= 1) {
			window.location = '<?php echo $_ENV["request.cw"]["thisPage"]; ?>?group_id=' + viewID;
			}
		};
		jQuery(document).ready(function() {
			// add new show-hide
			jQuery('form#addNewForm').hide();
			jQuery('a#showAddNewFormLink').click(function() {
				jQuery(this).hide().siblings('a').show();
				jQuery('form#addNewForm').show().find('input.focusField').focus();
				return false;
			});
			jQuery('a#hideAddNewFormLink').hide().click(function() {
				jQuery(this).hide().siblings('a').show();
				jQuery('form#addNewForm').hide();
				return false;
			});
			// auto-click the link if adding
<?php
if(isset($_GET['clickadd'])) {
?>	
				jQuery('a#showAddNewFormLink').click();
<?php
}
?>
			// show help in config items list
			jQuery('#recordForm a.showHelpLink').click(function() {
			jQuery(this).parents('td').siblings('td').children('.helpText').toggle();
			return false;
			}).parents('td').click(function() {
			jQuery(this).children('a').click();
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
if(strlen(trim($_ENV["request.cwpage"]["heading1"])))
	echo '<h1>'. trim($_ENV["request.cwpage"]["heading1"]).'</h1>';	
if(strlen(trim($_ENV["request.cwpage"]["heading2"])))
	echo '<h2>'. trim($_ENV["request.cwpage"]["heading2"]).'</h2>';
?>			
					<!-- Admin Alert - message shown to user -->
<?php
include("cwadminapp/inc/cw-inc-admin-alerts.php");     
?>
					<div id="CWadminContent">
						<!-- //// PAGE CONTENT ////  -->
<?php
// if a valid record is not found 
if(!($configGroupQuery['totalRows'] == 1)) {                    
?>	
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>Invalid config group id. Please return to the <a href="config-groups.php">Config Groups List</a> and choose a valid group.</p>
<?php
	// if a record is found 
} else {
	// /////// 
	// UPDATE CONFIG GROUP 
	// /////// 
	// FORM 
?>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>" class="CWobserve" name="updateConfigGroupForm" id="updateConfigGroupForm" method="post">
								<p>&nbsp;</p>
								<h3>Edit Config Group Details</h3>
								<table class="CWinfoTablesa">
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
												<?php // select changes to new page ?>
												<select name="config_group_id" id="config_group_id" onchange="groupSelect(this);">
<?php 
	for($i=0; $i < $configGroupsListQuery['totalRows']; $i++) {	
?>
													<option value="<?php echo $configGroupsListQuery['config_group_id'][$i];?>"<?php if($_ENV["request.cwpage"]["currentRecord"]==$configGroupsListQuery['config_group_id'][$i]) {?> selected="selected"<?php }?>><?php echo $configGroupsListQuery['config_group_name'][$i];?></option>
<?php
	}
?>
												</select>
												<div class="smallPrint"><a href="config-groups.php?clickadd=1">Add New Group</a></div>
											</div>
										</td>
										<?php // sort ?>
										<td>
											<input name="config_group_sort" type="text" class="{required:true}" title="Sort Order is required" value="<?php echo $_POST['config_group_sort'];?>" size="5" maxlength="7" onkeyup="extractNumeric(this,2,true)" onblur="checkValue(this)">
										</td>
                                        <?php // show merchant y/n ?>
										<td style="text-align:center">
											<input name="config_group_show_merchant" type="checkbox" <?php if(isset($_POST['config_group_show_merchanrt']) || $configGroupQuery['config_group_show_merchant'][0]) {?>checked="checked"<?php }?> class="formCheckbox" value="1">
											<br>
											<input name="SubmitAdd" type="submit" class="CWformButton" id="SubmitAdd" value="Save Group Details">
										</td>
									</tr>
									</tbody>
								</table>
								<input type="hidden" name="config_group_name" value="<?php echo $_ENV["request.cwpage"]["groupName"];?>">
								<input type="hidden" name="update_ID" value="<?php echo $_ENV["request.cwpage"]["currentRecord"];?>">
							</form>
<?php
	// /////// 
	// /END UPDATE CONFIG GROUP 
	// /////// 
	// /////// 
	// ADD NEW CONFIG ITEM 
	// /////// 
?>
							<p>&nbsp;</p>
							<h3>Add New Config Item in '<?php echo $_ENV["request.cwpage"]["groupName"];?>'</h3>
                                                        <?php // link for add-new form ?>
							<div class="CWadminControlWrap">
								<a class="CWbuttonLink" id="showAddNewFormLink" href="#">Add New Config Item</a>
								<a class="deleteLink" id="hideAddNewFormLink" href="#">Cancel</a>
							</div>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>" class="CWvalidate" name="addNewForm" id="addNewForm" method="post">
                            
                            <input type="hidden" name="currentRecord" value="<?php echo $_ENV["request.cwpage"]["currentRecord"];?>">
								<table class="CWinfoTable CWformTable">
									<tr>
										<th class="label">Item Name</th>
										<td><input name="config_name" type="text" size="25" maxlength="254" id="config_name" class="required focusField" title="Variable Name is required" value="<?php echo $_POST['config_name'];?>"></td>
									</tr>
									<tr>
										<th class="label">Variable</th>
										<td><input name="config_variable" type="text" size="25" maxlength="254" id="config_variable" class="required" title="Variable is required" value="<?php echo $_POST['config_variable'];?>"></td>
									</tr>
									<tr>
										<th class="label">Sort Order</th>
										<td><input name="config_sort" type="text" id="config_sort" class="required sort" maxlength="7" size="4" title="Sort Order is required" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)" value="<?php echo $_POST['config_sort'];?>"></td>
									</tr>
<?php 
	if(!(isset($fieldType)))
		$fieldType=$_POST['config_type'];
	$v=trim($fieldType);
?>
									<tr>
										<th class="label">Form Input Type</th>
										<td scope="col">
											<select name="config_type" id="config_type" class="required" title="Form Input Type is required">
												<option value="" <?php if($v=='') { ?>selected="selected"<?php }?>>Choose Form Field Type</option>
												<option value="text" <?php if($v=='text') {?> selected="selected"<?php }?> multivalue="false">Text Field</option>
												<option value="number" <?php if($v=='number') {?> selected="selected"<?php }?> multivalue="false">Numeric Input</option>
												<option value="textarea" <?php if($v=='textarea') {?> selected="selected"<?php }?> multivalue="false">Textarea</option>
												<option value="texteditor" <?php if($v=='texteditor') {?> selected="selected"<?php }?> multivalue="false">Text Editor (rich text)</option>
												<option value="boolean" <?php if($v=='boolean') {?> selected="selected"<?php }?> multivalue="false">Checkbox (single)</option>
												<option value="checkboxgroup" <?php if($v=='checkboxgroup') {?> selected="selected"<?php }?> multivalue="true">Checkbox Group</option>
												<option value="radio" <?php if($v == 'redio') {?> selected="selected"<?php }?> multivalue="true">Radio Group</option>
												<option value="select" <?php if($v=='select') {?> selected="selected"<?php }?> multivalue="true">Select List</option>
												<option value="multiselect" <?php if($v=='multiselect') {?> selected="selected"<?php }?> multivalue="true">Multiple Select List</option>
											</select>
										</td>
									</tr>
									<tr id="valueRow">
										<th class="label">Value</th>
										<td><input name="config_value" type="text" id="config_value" value="<?php echo $_POST['config_value'];?>"></td>
									</tr>
									<tr id="possiblesRow">
										<th class="label">Possible Values</th>
										<td>
											<textarea name="config_possibles" cols="40" rows="4" type="text" id="config_possibles"><?php echo $_POST['config_possibles'];?></textarea>
											<div class="smallPrint">Enter one Name|Value pair per line</div>
										</td>
									</tr>
									<tr>
										<th class="label">Help Description</th>
										<td>
											<textarea name="config_description" cols="40" rows="2" type="text" id="config_description"><?php echo $_POST['config_description'];?></textarea>
											<div class="smallPrint">The descriptive help context for config form users</div>
										</td>
									</tr>
									<tr id="sizeRow">
										<th class="label">Size</th>
										<td><input name="config_size" type="text" id="config_size" class="required" maxlength="3" size="3" title="Size of input is required" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)" value="<?php echo $_POST['config_size'];?>"></td>
									</tr>
									<tr id="rowsRow">
										<th class="label">Rows</th>
										<td><input name="config_rows" type="text" id="config_rows" class="required" maxlength="3" size="3" title="Number of rows is required" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)" value="<?php echo $_POST['config_rows'];?>"></td>
									</tr>
									<tr>
										<th class="label">Prevent Deletion</th>
										<td><input name="config_protected" type="checkbox" class="formCheckbox" id="config_protected" value="1"<?php if($_POST['config_protected']) {?> checked="checked"<?php }?>></td>
									</tr>
									<tr id="requiredRow">
										<th class="label">Required in Form</th>
										<td><input name="config_reqd" type="checkbox" class="formCheckbox" id="config_reqd" value="1"<?php if($_POST['config_reqd']) {?> checked="checked"<?php }?>></td>
									</tr>
									<tr>
										<th class="label">Show Merchant</th>
										<td>
                                        <input name="config_show_merchant" type="checkbox" class="formCheckbox" id="config_show_merchant" value="1"<?php if($_POST['config_show_merchant']) {?> checked="checked"<?php }?> /></td>
									</tr>
									<tr>
										<td colspan="2">
											<input type="submit" class="CWformButton" value="Add New Configuration Item" name="AddNewConfigurationItem">
										</td>
									</tr>
								</table>
							</form>
<?php
	// /////// 
	// /END ADD NEW CONFIG ITEM 
	// /////// 
	// /////// 
	// UPDATE CONFIG ITEMS 
	// /////// 
?>
							<p>&nbsp;</p>
							<h3>Active Config Items</h3>
<?php
	// check for existing records 
	if(!($itemsQuery['totalRows'])) {
?>
            					<p>&nbsp;</p>
								<p>There are currently no config items defined for this group</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<?php  // DELETE GROUP ?>
								<form action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>" name="deleteGroupForm" id="deleteGroupForm" method="post">
									<input name="deleteGroupID" type="hidden" value="<?php echo $_ENV["request.cwpage"]["currentRecord"];?>">
<?php // delete button ?>
									<p><input name="SubmitDelete" type="submit" class="deleteButton" id="DeleteGroup" value="Delete Group"></p>
								</form>
<?php
		// /END DELETE GROUP 
	// if existing records found 
	} else {
?>	
								<form action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>" name="recordForm" id="recordForm" method="post">
									<table class="CWsort CWstripe" summary="<?php echo $_ENV["request.cwpage"]["baseURL"];?>">
										<thead>
										<tr class="sortRow">
											<th class="noSort" style="text-align:center;" width="50">Edit</th>
											<th>Help</th>
											<th class="config_name" width="305">Variable Name</th>
											<th class="config_variable" width="145">Application Variable</th>
											<th class="config_value">Value</th>
										
<?php 
		if($_ENV["application.cw"]["configSelectType"]) {
?>
                    						<th class="config_type">Type</th>
<?php
		}
?>
                    						<th class="config_sort">Sort</th>
											<th class="noSort" width="50" style="text-align:center;">Delete</th>
										</tr>
										</thead>
										<tbody>
<?php 
		$fieldProtectCt=0;
		for($j=0 ; $j < $itemsQuery['totalRows'];$j++) {
			// create per-item values  
			$fieldName = "config_value".($j+1);
			$fieldID = $fieldName;
			$fieldLabel = $itemsQuery['config_name'][$j];
			$fieldValue = $itemsQuery['config_value'][$j];
			$fieldType = $itemsQuery['config_type'][$j];
			$fieldHelp = $itemsQuery['config_description'][$j];
			$fieldOptions = $itemsQuery['config_possibles'][$j];
			$fieldSize = $itemsQuery['config_size'][$j];
			$fieldRows = $itemsQuery['config_rows'][$j];
			// count protected fields 
			if(is_numeric($itemsQuery['config_protected'][$j])) {
				$fieldProtectCt += $itemsQuery['config_protected'][$j];
			}
			// required fields 
			if($itemsQuery['config_required'][$j]==true) {
				$fieldClass = 'required';
			} else {
				$fieldClass = '';
			}
			// email fields 
			if(substr($itemsQuery['config_name'][$j],0,-5)=='email') {
				if ($fieldClass) $fieldClass .= " ";
				$fieldClass .= 'email';
			}
			// add variable to application scope 
			$_ENV["application.cw"][$itemsQuery['config_variable'][$j]] = $itemsQuery['config_value'][$j];
			if ($itemsQuery['config_variable'][$j] == "globalLocale") {
				$localeInfo = explode(",", $_ENV["application.cw"][$itemsQuery['config_variable'][$j]]);
				$_ENV["application.cw"]["globalLocaleCodes"] = $_ENV["application.cw"][$itemsQuery['config_variable'][$j]];
				$_ENV["application.cw"][$itemsQuery['config_variable'][$j]] = $localeInfo[0];
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
			if($itemsQuery['config_type'][$j]=='texteditor' && $_ENV["request.cwpage"]["rte"]) {
				$rteFields[] = $fieldName;
			}
			// CUSTOM FIELDS can be managed here, reference config_variable value 
			// Get Themes : Admin style directory 
			if ($itemsQuery["config_variable"][$j] == 'adminThemeDirectory') {
				// get directories within admin/css/cw-theme 
				$getThemes = array();
				$tempDir = preg_replace("/\/+$/", "/", expandPath("cw4/admin/theme")."/");
				$dir = opendir($tempDir);
				while (false !== ($files = readdir($dir))) {
					if ($files && $files != "." && $files != ".." && is_dir($tempDir . $files)) {
						$getThemes[] = $files;
					}
				}
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
							$fieldOptions,
							$fieldClass,
							$fieldSize,
							$fieldRows
							);
			// output the rows 
?>
										<tr>
											<?php // details link ?>
											<td style="text-align:center;"><a href="config-item-details.php?item_id=<?php echo $itemsQuery['config_id'][$j]; ?>" title="Manage Config Item details" class="detailsLink"><img src="img/cw-edit.gif" width="15" height="15" alt="Edit"></a></td>
											<?php // help ?>
											<td class="noLink">
<?php 
			if(strlen(trim($itemsQuery['config_description'][$j]))) {       
?>
													<a href="##" class="showHelpLink"><img width="16" height="16" align="absmiddle" alt="" title="<?php  echo $itemsQuery['config_description'][$j];?>" src="img/cw-help.png"></a>
<?php
			} else {
?>
													(null)
<?php
			}
?>
											</td>
											<?php // name / help ?>
											<td>
												<a href="config-item-details.php?item_id=<?php echo $itemsQuery['config_id'][$j]; ?>" class="detailsLink" title="Manage Config Item details">
												<?php echo $fieldLabel; ?>
												</a>
												<?php // hidden help text ?>
												<div class="helpText" style="display:none;"><?php  echo $itemsQuery['config_description'][$j]; ?></div>
											</td>
											<?php // variable ?>
											<td>
												<a href="config-item-details.php?item_id=<?php echo $itemsQuery['config_id'][$j]; ?>" class="detailsLink" title="Manage Config Item details">
												<?php echo $itemsQuery['config_variable'][$j]; ?>
												</a>
											</td>
											<?php // value ?>
											<td>
												<?php echo $inputEl; ?>
											</td>
<?php
			// type 
			if ($_ENV["application.cw"]["configSelectType"]) {
?>
                                        	<td>
													<select name="config_type<?php echo $j+1; ?>" id="config_type<?php echo $j;?>" class="required">
<?php
				$v=trim($fieldType);				
?>
														<option value=""<?php if($v=='taxt') {?> selected="selected"<?php }?>>Choose Form Field Type</option>
														<option value="text"<?php if($v=='text') {?> selected="selected"<?php }?> multivalue="false">Text Field</option>
														<option value="textarea"<?php if($v=='textarea') {?> selected="selected"<?php }?> multivalue="false">Textarea</option>
														<option value="texteditor"<?php if($v=='texteditor') {?> selected="selected"<?php }?> multivalue="false">Text Editor (rich text)</option>
														<option value="boolean"<?php if($v=='boolean') {?> selected="selected"<?php }?> multivalue="false">Checkbox (single)</option>
														<option value="checkboxgroup"<?php if($v == 'checkboxgroup') {?> selected="selected"<?php }?> multivalue="true">Checkbox Group</option>
														<option value="radio"<?php if($v=='redio') {?> selected="selected"<?php }?> multivalue="true">Radio Group</option>
														<option value="select"<?php if($v == 'select') {?> selected="selected"<?php }?> multivalue="true">Select List</option>
														<option value="multiselect"<?php if($v == 'multiselect') {?> selected="selected"<?php }?> multivalue="true">Multiple Select List</option>
													</select>
												</td>
<?php
			}
			$configItemType = '';
			if(!($_ENV["application.cw"]["configSelectType"])) {
				$configItemType='';
				$configItemType.='<input name="config_type'.($j+1).'" type="hidden" value="'.$fieldType.'">';
			}
			// sort 
?>
											<td>
												<input name="config_sort<?php echo $j+1; ?>" type="text" size="3" maxlength="7" class="required sort" title="Sort Order is required" value="<?php echo $itemsQuery['config_sort'][$j];?>" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">
											</td>
											<?php // delete ?>
											<td style="text-align:center;">
												<input name="deleteRecord[<?php echo $j?>]" type="checkbox" class="formCheckbox" value="<?php echo $itemsQuery['config_id'][$j];?>"<?php if($itemsQuery['config_protected'][$j] != 0) {?> disabled="disabled"<?php }?>>
												<input type="hidden" name="configItem<?php echo $j+1; ?>" value="<?php echo $itemsQuery['config_id'][$j];?>">
												<input type="hidden" name="config_name<?php echo $j+1; ?>" value="<?php echo $itemsQuery['config_name'][$j];?>">
												<input type="hidden" name="config_variable<?php echo $j+1; ?>" value="<?php echo $itemsQuery['config_variable'][$j];?>">
												<input type="hidden" name="config_ID<?php echo $j+1; ?>" value="<?php echo $itemsQuery['config_id'][$j];?>">
												<input type="hidden" name="config_description<?php echo $j+1; ?>" value="<?php echo $itemsQuery['config_description'][$j];?>">
												<input type="hidden" name="config_possibles<?php echo $j+1; ?>" value="<?php echo $itemsQuery['config_possibles'][$j];?>">
												<input type="hidden" name="config_show_merchant<?php echo $j+1; ?>" value="<?php echo $itemsQuery['config_show_merchant'][$j];?>">
												<input name="recordIDlist[<?php echo $j; ?>]" type="hidden" value="<?php echo $itemsQuery['config_id'][$j];?>">
<?php
			if(!($_ENV["application.cw"]["configSelectType"]) && strlen(trim($configItemType))) {
				echo $configItemType;
			}
?>
											</td>
										</tr>
<?php
		}
?>
										</tbody>
									</table>
									<?php // submit button - save changes ?>
									<input name="SubmitUpdate" type="submit" class="submitButton" id="UpdateConfigItems" value="Save Changes">
								</form>
								<?php // note to user ?>
								<div class="smallPrint">
									Note:
<?php 
		if($fieldProtectCt) {
?>
                                	Some items are protected and may not be deleted.
<?php 
		} else {
?>
                                    	Delete all config variables to enable deleting the group.
<?php
		}
?>
								</div>
								
<?php
		// RTE rich text editor fields 
		if($_ENV["request.cwpage"]["rte"]) {
			include("cwadminapp/inc/cw-inc-admin-script-editor.php");
		}
	}
	// end if records exist 
	// /////// 
	// /END UPDATE CONFIG ITEMS 
	// /////// 
}
// /end valid config group record 
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
