<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: config-item-details.php
File Date: 2012-02-01
Description: Displays config item details
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
// default item ID 
if(!isset($_GET['item_id'])) { $_GET['item_id'] = 0; }
if(!isset($_ENV["request.cwpage"]["userAlert"] )) { $_ENV["request.cwpage"]["userAlert"] = ""; }
if(!isset($_ENV["request.cwpage"]["userConfirm"] )) { $_ENV["request.cwpage"]["userConfirm"] = ""; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("userconfirm,useralert,resetapplication");
// create the base url for sorting out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// current record 
if(!isset($_ENV["request.cwpage"]["currentRecord"])) { $_ENV["request.cwpage"]["currentRecord"] = $_GET['item_id']; }
// QUERY: get config item details (id, name, variable) 
$configItemQuery = CWquerySelectConfigItemDetails($_ENV["request.cwpage"]["currentRecord"],'','');
// if none found, send back to config groups page 
if ($configItemQuery['totalRows'] < 1) {
	header("Location: config-groups.php");
	exit;
}
// QUERY: get config group details 
$configGroupQuery = CWquerySelectConfigGroupDetails($configItemQuery['config_group_id'][0],'');
// current record name 
$_ENV["request.cwpage"]["itemName"] = ((isset($configItemQuery['config_name'][0])) ? $configItemQuery['config_name'][0] : "" );
$_ENV["request.cwpage"]["currentGroup"] = ((isset($configItemQuery['config_group_id'][0])) ? $configItemQuery['config_group_id'][0] : "" );
// current group name 
$_ENV["request.cwpage"]["groupName"] = ((isset($configItemQuery['config_group_name'][0])) ? $configGroupQuery['config_group_name'][0] : "" );
// /////// 
// UPDATE CONFIG ITEM 
// /////// 
// look for at least one valid ID field 
if (isset($_POST['update_ID'])) {
	// checkbox values 
	if (!isset($_POST['config_reqd']) || $_POST['config_reqd'] == '') { $_POST['config_reqd'] = 0; }
	if (!isset($_POST['config_show_merchant']) || $_POST['config_show_merchant'] == '') { $_POST['config_show_merchant'] = 0; }
	// UPDATE RECORD 
	// QUERY: update config item (
	//item ID, group ID, variable, name, value, type, description,
	//possibles, showmerchant, sort, size, rows, protected, required)
	$updateRecordID = CWqueryUpdateConfigItem(
						$_POST['config_ID'],
						$_ENV["request.cwpage"]["currentGroup"],
						$_POST['config_variable'],
						$_POST['config_name'],
						$_POST['config_value'],
						$_POST['config_type'],
						$_POST['config_description'],
						$_POST['config_possibles'],
						$_POST['config_show_merchant'],
						number_format($_POST['config_sort'],0),
						$_POST['config_size'],
						$_POST['config_rows'],
						$_POST['config_protected'],
						$_POST['config_reqd']);
	// if an error is returned from update query 
	if(substr($updateRecordID,0,2) == '0-') {
		$updtrecordid = explode('-', $updateRecordID);
		$updtrecordid_last = $updtrecordid[count($updtrecordid) - 1];
		CWpageMessage("alert",$errorMsg);
		// if no error 
	} else {
		// set the new application variable 
		$_ENV["application.cw"][$configItemQuery['config_variable'][0]] = $_POST['config_value'];
		// build confirmation message 
		CWpageMessage("confirm","Config Item Updated");
	}
	// end error check 
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars("userconfirm,useralert,method,resetapplication");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// return to page as submitted, clearing form scope 
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."&resetapplication=".$_ENV["application.cw"]["storePassword"]."");
	exit;
}
// /END UPDATE CONFIG ITEM 
// /////// 
// DELETE CONFIG ITEM 
// /////// 
if((isset($_POST['deleteItemID'])) && $_POST['deleteItemID'] == $_ENV["request.cwpage"]["currentRecord"]) {
	// QUERY: delete item (item id) 
	$deleteGroup = CWqueryDeleteConfigItem($_ENV["request.cwpage"]["currentRecord"]);
	// if item was deleted 
	if(substr($deleteGroup,0,2) != '0-') {
		CWpageMessage("alert","Config Item ".$_ENV["request.cwpage"]["itemName"]." deleted");
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: config-group-details.php?group_id=".$_ENV["request.cwpage"]["currentGroup"]."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."&resetapplication=".$_ENV["application.cw"]["storePassword"]."");
		exit;
	}
	// if item not deleted 
	else
	{
		CWpageMessage("alert","Config item ".$_ENV["request.cwpage"]["itemName"]." is protected and cannot be deleted");
	}
}
// /END DELETE CONFIG ITEM 
// default form values 
if(!isset($_POST['config_id'] )) { $_POST['config_id'] = $_ENV["request.cwpage"]["currentRecord"]; }
if(!isset($_POST['config_name'] )) { $_POST['config_name'] = ((isset($configItemQuery['config_name'][0])) ? $configItemQuery['config_name'][0] : "" ); }
if(!isset($_POST['config_variable'] )) { $_POST['config_variable'] = ((isset($configItemQuery['config_variable'][0])) ? $configItemQuery['config_variable'][0] : "" ); }
if(!isset($_POST['config_sort'] )) { $_POST['config_sort'] = ((isset($configItemQuery['config_sort'][0])) ? $configItemQuery['config_sort'][0] : "" ); }
if(!isset($_POST['config_type'] )) { $_POST['config_type'] = ((isset($configItemQuery['config_type'][0])) ? $configItemQuery['config_type'][0] : "" ); }
if(!isset($_POST['config_value'] )) { $_POST['config_value'] = ((isset($configItemQuery['config_value'][0])) ? $configItemQuery['config_value'][0] : "" ); }
if(!isset($_POST['config_possibles'] )) { $_POST['config_possibles'] = ((isset($configItemQuery['config_possibles'][0])) ? $configItemQuery['config_possibles'][0] : "" ); }
if(!isset($_POST['config_description'] )) { $_POST['config_description'] = ((isset($configItemQuery['config_description'][0])) ? $configItemQuery['config_description'][0] : "" ); }
if(!isset($_POST['config_size'] )) { $_POST['config_size'] = ((isset($configItemQuery['config_size'][0])) ? $configItemQuery['config_size'][0] : "" ); }
if(!isset($_POST['config_rows'] )) { $_POST['config_rows'] = ((isset($configItemQuery['config_rows'][0])) ? $configItemQuery['config_rows'][0] : "" ); }
if(!isset($_POST['config_reqd'] )) { $_POST['config_reqd'] = ((isset($configItemQuery['config_required'][0])) ? $configItemQuery['config_required'][0] : "" ); }
if(!isset($_POST['config_protected'] )) { $_POST['config_protected'] = ((isset($configItemQuery['config_protected'][0])) ? $configItemQuery['config_protected'][0] : "" ); }
if(!isset($_POST['config_show_merchant'])) { $_POST['config_show_merchant'] = ((isset($configItemQuery['config_show_merchant'][0])) ? $configItemQuery['config_show_merchant'][0] : "" ); }
// /END DELETE CONFIG ITEM 
// default form values 
// PAGE SETTINGS 
// Page Browser Window Title
//<title>
//
$_ENV["request.cwpage"]["title"] = "Update Config Item";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Manage Config Item:".$_ENV["request.cwpage"]["itemName"]."";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = 'In Group: '.$_ENV["request.cwpage"]["groupName"].'&nbsp; <a href="config-group-details.php?group_id='.$_ENV["request.cwpage"]["currentGroup"].'">View Group</a>';
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = 'config-groups.php';
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"] = 1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"] = 0;

// PAGE SETTINGS 
// Page Browser Window Title
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
						if(!$configItemQuery['totalRows'] == 1) { ?>
                        	<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>Invalid config item id. Please return to the <a href="config-group-details.php?group_id=<?php echo $_ENV["request.cwpage"]["currentGroup"]; ?>">Config Items List</a> and choose a valid group.</p>
<?php							
							// if a record is found 
						} else { 
                        	// /////// 
							// UPDATE CONFIG ITEM 
							// /////// ?>
							<p>&nbsp;</p>
							<h3>Edit Config Item Details</h3>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate" name="addNewForm" id="addNewForm" method="post">
								<table class="CWinfoTable CWformTable">
									<tr>
										<th class="label">Item Name</th>
										<td><input name="config_name" type="text" size="25" maxlength="254" id="config_name" class="required focusField" title="Variable Name is required" value="<?php echo $_POST['config_name']; ?>" onblur="checkValue(this)"></td>
									</tr>
									<tr>
										<th class="label">Variable</th>
										<td><input name="config_variable" type="text" size="25" maxlength="254" id="config_variable" class="required" title="Variable is required" value="<?php echo $_POST['config_variable']; ?>" onblur="checkValue(this)"></td>
									</tr>
									<tr>
										<th class="label">Sort Order</th>
										<td><input name="config_sort" type="text" id="config_sort" class="required sort" maxlength="7" size="4" title="Sort Order is required" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)" value="<?php echo $_POST['config_sort']; ?>"></td>
									</tr>
<?php
									if (!isset($fieldType)) { $fieldType = $_POST['config_type']; }
									$v = trim($fieldType);
?>                                    
									<tr>
										<th class="label">Form Input Type</th>
										<td scope="col">
											<select name="config_type" id="config_type" class="required" title="Form Input Type is required">
												<option value=""<?php if($v == '') { ?> selected="selected"<?php } ?>>Choose Form Field Type</option>
												<option value="text"<?php if($v == 'text') { ?> selected="selected"<?php } ?>multivalue="false">Text Field</option>
												<option value="number"<?php if($v == 'number') { ?> selected="selected"<?php } ?> multivalue="false">Numeric Input</option>
												<option value="textarea"<?php if($v == 'textarea') { ?> selected="selected"<?php } ?> multivalue="false">Textarea</option>
												<option value="texteditor"<?php if($v == 'texteditor') { ?> selected="selected"<?php } ?> multivalue="false">Text Editor (rich text)</option>
												<option value="boolean"<?php if($v == 'boolean') { ?> selected="selected"<?php } ?> multivalue="false">Checkbox (single)</option>
												<option value="checkboxgroup"<?php if($v == 'checkboxgroup') { ?> selected="selected"<?php } ?> multivalue="true">Checkbox Group</option>
												<option value="radio"<?php if($v == 'radio') { ?> selected="selected"<?php } ?> multivalue="true">Radio Group</option>
												<option value="select"<?php if($v == 'select') { ?> selected="selected"<?php } ?> multivalue="true">Select List</option>
												<option value="multiselect"<?php if($v == 'multiselect') { ?> selected="selected"<?php } ?> multivalue="true">Multiple Select List</option>
											</select>
										</td>
									</tr>
									<tr id="valueRow">
										<th class="label">Value</th>
										<td>
<?php
											// payment methods should not be edited here 
											if($_POST['config_variable'] == 'paymentMethods') { ?>
                                            	<div style="display:none;">
												<input name="config_value" type="text" id="config_value" value="<?php echo $_POST['config_value']; ?>">
												</div>
												<div class="smallPrint">Use config setting checkboxes to enable selected methods</div>
                                                                                                <?php // theme directory not edited here ?>
<?php												
											}
											elseif($_POST['config_variable'] == 'adminThemeDirectory') { ?>
                                            	<div style="display:none;">
												<input name="config_value" type="text" id="config_value" value="<?php echo $_POST['config_value']; ?>">
												</div>
												<div class="smallPrint">Use select box in config item settings to select active theme</div>
<?php												
											} else { ?>
                                            	<input name="config_value" type="text" id="config_value" value="<?php echo $_POST['config_value']; ?>">
<?php											
											}
?>                                            
										</td>
									</tr>
									<tr id="possiblesRow">
										<th class="label">Possible Values</th>
										<td>
<?php
											// payment methods should not be edited here 
											if($_POST['config_variable'] == 'paymentMethods') { ?>
                                            	<div style="display:none;">
													<textarea name="config_possibles" cols="40" rows="4" type="text" id="config_possibles"><?php echo $_POST['config_possibles']; ?></textarea>
												</div>
												<div class="smallPrint">Automatically generated via settings in cwapp/auth/ payment configuration files</div>
<?php											
											// theme directory not edited here 
											}
											elseif($_POST['config_variable'] == 'adminThemeDirectory') { ?>
											<div style="display:none;">
													<textarea name="config_possibles" cols="40" rows="4" type="text" id="config_possibles"><?php echo $_POST['config_possibles']; ?></textarea>
												</div>
												<div class="smallPrint">Automatically generated via theme directories in cw4/admin/theme/</div>
<?php												
											} else { ?>
                                            	<textarea name="config_possibles" cols="40" rows="4" type="text" id="config_possibles"><?php echo $_POST['config_possibles']; ?></textarea>
											<div class="smallPrint">Enter one Name|Value pair per line</div>
<?php											
											}
?>                                            
										</td>
									</tr>
									<tr>
										<th class="label">Help Description</th>
										<td>
											<textarea name="config_description" cols="40" rows="2" type="text" id="config_description"><?php echo $_POST['config_description']; ?></textarea>
											<div class="smallPrint">The descriptive help context for config form users</div>
										</td>
									</tr>
									<tr id="sizeRow">
										<th class="label">Size</th>
										<td><input name="config_size" type="text" id="config_size" class="required" maxlength="3" size="3" title="Size of input is required" onKeyUp="extractNumeric(this,2,true)" onBlur="checkValue(this)" value="<?php echo $_POST['config_size']; ?>"></td>
									</tr>
									<tr id="rowsRow">
										<th class="label">Rows</th>
										<td><input name="config_rows" type="text" id="config_rows" class="required" maxlength="3" size="3" title="Number of rows is required" onKeyUp="extractNumeric(this,2,true)" onBlur="checkValue(this)" value="<?php echo $_POST['config_rows']; ?>"></td>
									</tr>
									<tr id="requiredRow">
										<th class="label">Required in Form</th>
										<td><input name="config_reqd" type="checkbox" class="formCheckbox" id="config_reqd" value="1"<?php if($_POST['config_reqd'] == 1) { ?> checked="checked"<?php } ?>></td>
									</tr>
									<tr>
										<th class="label">Show Merchant</th>
										<td><input name="config_show_merchant" type="checkbox" class="formCheckbox" id="config_show_merchant" value="1"<?php if($_POST['config_show_merchant'] == 1) { ?> checked="checked"<?php } ?>></td>
									</tr>
									<tr>
										<td colspan="2">
											<input type="submit" class="CWformButton" value="Save Changes">
											<input type="hidden" name="config_ID" value="<?php echo $_ENV["request.cwpage"]["currentRecord"]?>">
											<input type="hidden" name="update_ID" value="<?php echo $_ENV["request.cwpage"]["currentRecord"]?>">
<?php
											if(is_numeric($configItemQuery['config_protected'][0])) { ?>
                                            	<input name="config_protected" type="hidden" value="<?php echo $configItemQuery['config_protected'][0]; ?>">
<?php											
											} else { ?>
                                            	<input name="config_protected" type="hidden" value="0">
<?php											 
											}
?>                                            
										</td>
									</tr>
								</table>
							</form>
<?php
							// /////// 
							// DELETE CONFIG ITEM 
							// /////// 
							if($configItemQuery['config_protected'][0] == 1) { ?>
                            	<p>This custom variable cannot be deleted</p>
<?php								
							} else { ?>
                            	<form method="post" name="deleteConfigItem">
									<input name="deleteItemID" type="hidden" value="<?php echo $_ENV["request.cwpage"]["currentRecord"]; ?>">
									<p><input name="deleteConfigItem" type="submit" class="deleteButton" value="Delete"></p>
								</form>
<?php							
							}
							// /////// 
							// /END DELETE CONFIG ITEM 
                                                        // /////// 
						}
						// /////// 
						// /END UPDATE CONFIG ITEM 
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
