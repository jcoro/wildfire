<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: options.php
File Date: 2012-02-01
Description: Displays option management table
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("manager,merchant,developer");
// PAGE PARAMS 
// default values for sort / active or archived
if(!(isset($_GET['sortby']))) $_GET['sortby']='optiontype_sort';
if(!(isset($_GET['sortdir']))) $_GET['sortdir']='asc';
if(!(isset($_GET['view']))) $_GET['view']='active';
// default form values 
if(!(isset($_POST['optiontype_id']))) $_POST['optiontype_id']='';
if(!(isset($_POST['optiontype_name']))) $_POST['optiontype_name']='';
if(!(isset($_POST['optiontype_Status']))) $_POST['optiontype_Status']=0;
if(!(isset($_POST['optiontype_sort']))) $_POST['optiontype_sort']=0;
if(!(isset($_POST['optionIDlist']))) $_POST['optionIDlist']=array();
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars('sortby,sortdir,view,userconfirm,useralert');
// create the base url for sorting out of serialized url variables
$_ENV["request.cwpage"]["baseURL"]=CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// ACTIVE / ARCHIVED 
// set up page title 
if(strstr($_GET['view'], "arch") !== false) {
	$_ENV["request.cwpage"]["viewType"] = 'Archived';
	$_ENV["request.cwpage"]["subHead"] = 'Archived product options are not shown in the store';
} else {
	$_ENV["request.cwpage"]["viewType"] = "Active";
	$_ENV["request.cwpage"]["subHead"] = 'Manage active product options or add a new option group';
}
if (strstr($_ENV["request.cwpage"]["viewType"], 'Arch') !== false) {
	$_ENV["request.cwpage"]["optionsArchived"] = 1;	
} else {
	$_ENV["request.cwpage"]["optionsArchived"] = 0;
}
// QUERY: get option groups 
$optionGroupsQuery=CWquerySelectStatusOptionGroups($_ENV["request.cwpage"]["optionsArchived"],true);
// /////// 
// UPDATE / DELETE OPTION GROUPS 
// /////// 
if(isset($_POST['UpdateOptions'])) {
	$loopCt=0;
	$updateCt=0;
	$deleteCt=0;
	$archiveCt=0;
	$activeCt=0;
	// loop all records from this page 
	foreach ($_POST['optionIDlist'] as $optKey => $id) {
		// param for status values 
		if(!(isset($_POST['optionType_Status'.$loopCt]))) $_POST['optionType_Status'.$loopCt] = $_ENV["request.cwpage"]["optionsArchived"];
		// if the option group ID is marked for deletion 
		$statusVal=$_POST['optionType_Status'.$loopCt];
		if($statusVal =='Deleted') {
			// QUERY: delete option group (option group ID) 
			// Note: marks record as deleted=1, does not actually delete, to avoid orders-placed errors 
			$deleteOptionGroup= CWqueryDeleteOptionGroup($id);
			$deleteCt++;
			// if not deleting, update 
		} else {
			// verify numeric sort order 
           	if(!(is_numeric($_POST['optiontype_sort'.$loopCt]))) $_POST['optionType_sort'][$loopCt] = 0;
			// determine archive 1/0, set up counts for confirmation message 
			if ($statusVal == 'Archived') {
				$optiontype_archive=1;
				if($_ENV["request.cwpage"]["optionsArchived"] ==0)
					$archiveCt++;
				else
					$updateCt++;
			} else {
				$optiontype_archive=0;
				if($_ENV["request.cwpage"]["optionsArchived"] == 1)
					$activeCt++;
				else
					$updateCt++;
			}
			// QUERY: update option group(id, name, sort, archive, text) 
			$updateOptionGroup = CWqueryUpdateOptionGroup(
				$_POST['optiontype_id'.$loopCt],
				$_POST['optiontype_name'.$loopCt],
				$_POST['optiontype_sort'.$loopCt],
				$optiontype_archive,
				$_POST['optiontype_text'.$loopCt]
				);
		}
		// /END if deleting or updating 
		$loopCt++;
	}
	// /END record loop 
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep=CWremoveUrlVars("userconfirm,useralert");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"]=CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	CWpageMessage("confirm","Changes Saved");
	// save alert text 
	$_ENV["request.cwpage"]["userAlertText"] = '';
	if($archiveCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"].= $archiveCt;
		if($archiveCt ==1)
			$_ENV["request.cwpage"]["userAlertText"].=' Option Group Archived';
		else
			$_ENV["request.cwpage"]["userAlertText"].=' Option Groups Archived';
	}
	elseif($activeCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"].= $activeCt;
		if($archiveCt ==1)
			$_ENV["request.cwpage"]["userAlertText"].=' Option Group Activated';
		else
			$_ENV["request.cwpage"]["userAlertText"].=' Option Groups Activated';
	}
	if($deleteCt > 0) {
		if($archiveCt > 0 || $activeCt > 0)
			$_ENV["request.cwpage"]["userAlertText"].='<br>';
		if ($deleteCt == 1)
			$_ENV["request.cwpage"]["userAlertText"].=$deleteCt.' Option Group Deleted';
		else
			$_ENV["request.cwpage"]["userAlertText"].=$deleteCt.' Option Groups Deleted';	
	}
	CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
// return to page as submitted, clearing form scope 
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_ENV["request.cw"]["thisPage"].'?userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
	exit;
}
// /////// 
// /END UPDATE / DELETE OPTION GROUPS 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"]='Manage Product Option';
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"]='Option Management: '.$_ENV["request.cwpage"]["viewType"].' Option Groups';
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"]=$_ENV["request.cwpage"]["subHead"];
// current menu marker 
$_ENV["request.cwpage"]["currentNav"]=$_ENV["request.cw"]["thisPage"];
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
			include('cwadminapp/inc/cw-inc-admin-scripts.php');
        // PAGE JAVASCRIPT ?>
		<script type="text/javascript">
		// confirm deletion
		function confirmDelete(selID,prodCt) {
		// if this option has products
		if (prodCt > 0) {
		if (prodCt > 1) {var prodWord = 'products'}else{var prodWord = 'product'};
		var confirmSelect = '#'+ selID;
			// if the dropdown has the value 'deleted'
				if( jQuery(confirmSelect).attr('value') == 'Deleted' ) {
				deleteConfirm = confirm("This option group will be permanently removed - currently used on " + prodCt + " " + prodWord + ".\nContinue?");
					// if confirm is returned false
					if(!deleteConfirm) {
						jQuery(confirmSelect).attr('value','<?php echo $_ENV["request.cwpage"]["viewType"]; ?>');
					};
				};
			// end if checked
			};
		// end if prodct
		};
		// end jquery
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
					if(strlen(trim($_ENV["request.cwpage"]["heading1"]))) {
						echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]).'</h1>';
					
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
						<?php // LINKS FOR VIEW TYPE ?>
						<div class="CWadminControlWrap">
							<strong>
                     <?php
					 	if($_GET['view'] =='arch') {
                    	?>
                            <a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>">View Active</a>
						<?php
						} else {
						 ?>
								<a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?view=arch">View Archived</a>
                            <?php
								// link for add-new form 
							if($_ENV["request.cwpage"]["optionsArchived"] ==0) {
                             ?>
									&nbsp;&nbsp;<a class="CWbuttonLink" href="option-details.php?editmode=add">Add New Option Group</a>
								<?php
							}
						}
							?>
							</strong>
						</div>
                        <?php
						// LINKS FOR VIEW TYPE 
						// /////// 
						// EDIT OPTIONS 
						// /////// ?>
						<form action="options.php" name="optionForm" id="optionForm" class="CWobserve" method="post" enctype="multipart/form-data">
                    <?php
						// save changes / submit button
						if($optionGroupsQuery['totalRows']) {		
							?>	<div class="CWadminControlWrap">
									<input name="UpdateOptions" type="submit" class="CWformButton" id="UpdateOptions" value="Save Changes">
									<div style="clear:right;"></div>
								</div>
							<?php		
						}
						// /end submit button 
						// if no records found, show message 
						if(!($optionGroupsQuery['totalRows'])) {
								?>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>No <?php strtolower($_ENV["request.cwpage"]["viewType"])?> Option Groups available</strong> <br><br></p>
                             <?php
							// if we have records to show 
						} else {
							// SHOW OPTION GROUPS 
						?>
								<table class="CWsort CWstripe" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
									<thead>
									<tr class="sortRow">
										<th class="noSort" style="text-align:center;" width="50">Edit</th>
										<th class="optiontype_name">Option Group Name</th>
										<th width="50" class="optiontype_id">ID</th>
										<th class="optiontype_text">Description</th>
										<th width="55" class="optiontype_sort">Order</th>
										<th width="65" class="optionProdCount">Products</th>
										<th width="95">Status</th>
									</tr>
									</thead>
									<tbody>
                                    <?php $disabledCt=0;
									for($i=0; $i< $optionGroupsQuery['totalRows'];$i++) {
										// output the row ?>
									<tr>
										<?php // details link ?>
										<td style="text-align:center;"><a href="option-details.php?optiontype_id=<?php echo $optionGroupsQuery['optiontype_id'][$i]; ?>" title="Manage Options in this Group" class="detailsLink"><img src="img/cw-edit.gif" width="15" height="15" alt="View Option Group Details"></a></td>
										<?php // option group name ?>
										<td>
											<strong><a href="option-details.php?optiontype_id=<?php echo $optionGroupsQuery['optiontype_id'][$i]; ?>" title="Manage Options in this Group" class="detailsLink"><?php echo $optionGroupsQuery['optiontype_name'][$i]; ?></a></strong>
											<?php // option group ID ?>
											<td><?php echo $optionGroupsQuery['optiontype_id'][$i]; ?></td>
											<?php // text description ?>
											<td>
												<input name="optiontype_text<?php echo $i; ?>"  type="text" size="15" value="<?php echo $optionGroupsQuery['optiontype_text'][$i]; ?>">
											</td>
											<?php // Sort Order ?>
											<td><input name="optiontype_sort<?php echo $i; ?>" type="text" size="3" maxlength="7" class="required sort" title="Sort Order is required" value="<?php echo $optionGroupsQuery['optiontype_sort'][$i]; ?>" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)"></td>
											<?php // Products ?>
											<td style="text-align:center;"><?php echo $optionGroupsQuery['optionProdCount'][$i]?></td>
											<?php // Status ?>
											<td>
											<?php
												// if no products are associated , or option is already archived, allow changes 
												if(!($optionGroupsQuery['optionProdCount'][$i]) > 0 || $optionGroupsQuery['optiontype_archive'][$i] ==1) {
													?>
													<select name="optionType_Status<?php  echo $i; ?>" class="optionStatusSelect" id="optionType_Status<?php echo $optionGroupsQuery['optiontype_id'][$i]; ?>" onChange="confirmDelete('optionType_Status<?php  echo $i; ?>'<?php echo $optionGroupsQuery['optionProdCount'][$i]; ?>)">
														<option value="Active"<?php if($optionGroupsQuery['optiontype_archive'][$i] != 1) { ?> selected="selected"<?php } ?>>Active</option>
														<option value="Archived"<?php if($optionGroupsQuery['optiontype_archive'][$i] == 1) { ?> selected="selected"<?php } ?>>Archived</option>
														<option value="Deleted">Deleted</option>
													</select>
												<?php 
									} else {
                                           $disabledCt++;
													?>Active
													<input type="hidden" name="optionType_Status<?php echo $i; ?>" value="<?php echo $optionGroupsQuery['optiontype_archive'][$i]?>">
												<?php 
									}
									// hidden values for managing updates ?>
												<input name="optiontype_name<?php echo $i; ?>" type="hidden" value="<?php echo $optionGroupsQuery['optiontype_name'][$i]; ?>">
											</td>
											<input name="optiontype_id<?php echo $i; ?>" type="hidden" value="<?php echo $optionGroupsQuery['optiontype_id'][$i]; ?>">
											<input name="optionIDlist[<?php echo $i; ?>]" type="hidden" value="<?php echo $optionGroupsQuery['optiontype_id'][$i]; ?>">
										</td>
									</tr>
									<?php
							}
									?>
									</tbody>
								</table>
							<?php
                                // if we have disabled status options, explain 
								if($disabledCt) {
                            ?>
								<span class="smallPrint" style="float:right;">
										Note:&nbsp;&nbsp;options with associated products or orders cannot be deleted
								</span>
								<?php 
                                 }
						}
							// /END if records found ?>
						</form>
                        <?php
						// /////// 
						// /END EDIT OPTIONS
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
			?><!-- /end CWadminPage-->
			<div class="clear"></div>
		</div>
		<!-- /end CWadminWrapper -->
	</body>
</html>
