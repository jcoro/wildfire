<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: tax-groups.php
File Date: 2012-02-01
Description: Manage Tax Groups
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
if(!isset($_GET['tax_group_id'])) $_GET['tax_group_id'] = 0;
// default form values 
if(!isset($_POST['tax_group_name'])) $_POST['tax_group_name'] = "";
if(!isset($_POST['tax_group_code'])) $_POST['tax_group_code'] = "";
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$VarToKeep= CWremoveUrlVars('view,userconfirm,useralert,clickadd');
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"]=CWserializeURL($VarToKeep,$_ENV["request.cw"]["thisPage"]);
// ACTIVE VS. ARCHIVED 
if($_GET['view'] == 'arch') {
	$_ENV["request.cwpage"]["viewType"]='Archived';
	$_ENV["request.cwpage"]["recordsArchived"]=1;
	$_ENV["request.cwpage"]["subHead"]="Archived ".$_ENV["application.cw"]["taxSystemLabel"] . ' Groups are ignored in the store';
} else {
	$_ENV["request.cwpage"]["viewType"]='Active';
	$_ENV["request.cwpage"]["recordsArchived"]=0;
	$_ENV["request.cwpage"]["subHead"]='Manage active '.$_ENV["application.cw"]["taxSystemLabel"].' Groups or add a new ' . $_ENV["application.cw"]["taxSystemLabel"] . ' Groups';

}
// QUERY: Get tax groups (active/archived )
$taxGroupsQuery = CWquerySelectTaxGroups($_ENV["request.cwpage"]["recordsArchived"]);
// /////// 
// ADD NEW TAX GROUP 
// /////// 
// if submitting the 'add new' form, and  
if(isset($_POST['tax_group_name']) && strlen(trim($_POST['tax_group_name']))) {
	// QUERY: insert new tax group (name, archived)
	$newRecordID=CWqueryInsertTaxGroup(trim($_POST['tax_group_name']),
										0,
										trim($_POST['tax_group_code']));
	// if no error returned from insert query 
	if(!(substr($newRecordID,0,2)=='0-')) {
		// update complete: return to page showing message 
		CWpageMessage("confirm",$_ENV["application.cw"]["taxSystemLabel"]." Group '".$_POST['tax_group_name']."' Added");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&clickadd=1');
		// if we have an insert error, show message, do not insert 
	} else {
		$newvarforlist=explode('-',$newrecordID);
		$_ENV["request.cwpage"]["errorMessage"]=$newvarforlist[count($newvarforlist)-1];
		CWpageMessage("alert",$_ENV["request.cwpage"]["errorMessage"]);
		$_GET['clickadd']=1;
	}
// /END duplicate/error check 
}
// /////// 
// /END ADD TAX GROUP 
// /////// 
// /////// 
// UPDATE / DELETE TAX GROUPS 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['tax_group_id0'])) {
	if(!(isset($_POST['deleteRecord']))) $_POST['deleteRecord']=array();
	$loopCt=0;
	$updateCt=0;
	$deleteCt=0;
	$archiveCt=0;
	$activeCt=0;
	// loop record ids, handle each one as needed 
	foreach ($_POST["recordIDlist"] as $key => $id) {
		// DELETE RECORDS 
		// if the record ID is marked for deletion 
		if(in_array($id, $_POST['deleteRecord'])) {
			// QUERY: delete record (record id) 
			$deleteRecord = CWqueryDeleteTaxGroup($id);
			$deleteCt++;
		// if not deleting, update 
		} else {
			// UPDATE RECORDS 
			// param for checkbox values 
			if(!(isset($_POST['tax_group_archive'.$loopCt]))) {					
				if($_GET['view'] == 'arch') $_POST['tax_group_archive'.$loopCt]=1;
				else $_POST['tax_group_archive'.$loopCt]=0;
			}
			// QUERY: update record (ID, archived) 
			$updateRecord = CWqueryUpdateTaxGroup($_POST['tax_group_id'.$loopCt],
													$_POST['tax_group_archive'.$loopCt]);
			if($_POST['tax_group_archive'.$loopCt] == 1 && $_ENV["request.cwpage"]["recordsArchived"] == 0) {
				$archiveCt++;
			}
			elseif($_POST['tax_group_archive'.$loopCt] == 0 && $_ENV["request.cwpage"]["recordsArchived"] == 1) {
				$activeCt++;
			} else {
				$updateCt++;
			}
			// /END delete vs. update 
		}
		$loopCt++;
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars('userconfirm,useralert');
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	CWpageMessage("confirm","Changes Saved");
	// save alert text 
	$_ENV["request.cwpage"]["userAlertText"]='';	
	if($archiveCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"] .=	$archiveCt.' Record';
		if($archiveCt > 1) $_ENV["request.cwpage"]["userAlertText"] .= 's';
		$_ENV["request.cwpage"]["userAlertText"] .= ' Archived';
	}
	elseif($activeCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"] .=	$activeCt.' Record';
		if($activeCt > 1) $_ENV["request.cwpage"]["userAlertText"] .= 's';
		$_ENV["request.cwpage"]["userAlertText"] .= ' Activated';
	}
	if($deleteCt > 0 ) {
		if($activeCt || $archiveCt) $_ENV["request.cwpage"]["userAlertText"] .= '<br>';
		$_ENV["request.cwpage"]["userAlertText"] .= $deleteCt.' Record';
		if($deleteCt > 1) $_ENV["request.cwpage"]["userAlertText"] .= 's';
		$_ENV["request.cwpage"]["userAlertText"] .=' Deleted';
	}
	CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
	// return to page as submitted, clearing form scope 
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_ENV["request.cwpage"]["relocateURL"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
	exit;
}
// /////// 
// END UPDATE / DELETE TAX GROUPS 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"]= "Manage ".$_ENV["application.cw"]["taxSystemLabel"].' Groups';
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = $_ENV["application.cw"]["taxSystemLabel"].' Groups Managemant : '.$_ENV["request.cwpage"]["viewType"];	
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"]= $_ENV["request.cwpage"]["subHead"];
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
				if(isset($_GET['clickadd'])) {
				?>
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
		echo '<h1>'.$_ENV["request.cwpage"]["heading1"].'</h1>';
		
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
	// if tax groups are not enabled 
	if(strtolower($_ENV["application.cw"]["taxSystem"]) != 'groups') {
	?>						
    					<div class="CWadminControlWrap">
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <p class="formText"><strong><?php echo $_ENV["application.cw"]["taxSystemLabel"].'Tax'; ?> groups disabled. To enable, select '<?php echo $_ENV["application.cw"]["taxSystemLabel"];//	
	} else {
							// LINKS FOR VIEW TYPE ?>
							<div class="CWadminControlWrap">
								<strong>
		<?php
		if($_GET['view']=='arch') {
				?>					<a href="<?php echo$_ENV["request.cw"]["thisPage"]; ?>">View Active</a>
		<?php
		} else {
		?>							<a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?view=arch">View Archived</a>
		<?php
									// link for add-new form 
			if($_ENV["request.cwpage"]["recordsArchived"] == 0) {	
		?>
        						&nbsp;&nbsp;
                                <a class="CWbuttonLink" id="showAddNewFormLink" href="#">Add New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group</a>
	
    		<?php
			}
		}
		?>
		</strong>
							</div>
			
      <?php
							// /END LINKS FOR VIEW TYPE 
							// /////// 
							// ADD NEW TAX GROUP 
							// /////// 
	  	if ($_ENV["request.cwpage"]["recordsArchived"] == 0) {
								// FORM ?>
								<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate" name="addNewForm" id="addNewForm" method="post">
									<p>&nbsp;</p>
									<h3>Add New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group</h3>
									<table class="CWinfoTable">
										<thead>
										<tr>
											<th><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group Name</th>
											<th><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group Code</th>
										</tr>
										</thead>
										<tbody>
										<tr>
											<?php // group name ?>
											<td style="text-align:center">
												<input name="tax_group_name" type="text" size="25" class="required focusField" title="Group Name is required" id="tax_group_name" value="<?php echo $_POST['tax_group_name']; ?>">
												<br>
												<?php // submit button ?>
												<input name="SubmitAdd" type="submit" class="CWformButton" id="SubmitAdd" value="Save New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group">
											</td>
											<?php // group code ?>
											<td style="text-align:center">
												<input name="tax_group_code" type="text" size="25" title="Group Code (optional)" id="tax_group_code" value="<?php echo $_POST['tax_group_code']; ?>">
											</td>
										</tr>
										</tbody>
									</table>
								</form>
		<?php
                            
		}
		// /////// 
		// /END ADD TAX GROUP 
		// /////// 
		// /////// 
		// EDIT RECORDS 
		// /////// ?>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>&view=<?php echo $_GET["view"]; ?>" name="recordForm" id="recordForm" method="post" class="CWobserve" enctype="multipart/form-data">
	<?php
		// if no records found, show message 
		if(!($taxGroupsQuery['totalRows'])) {?>
									<p>&nbsp;</p>
									<p>&nbsp;</p>
									<p>&nbsp;</p>
									<p><strong>No <?php echo $_ENV["request.cwpage"]["viewType"] . ' '. $_ENV["application.cw"]["taxSystemLabel"]; ?>Groups available.</strong> <br><br></p>
	<?php
		} else {
			// if records found 
			// output records 
			// Container table ?>
									<table class="CWinfoTable wide">
										<thead>
										<tr class="headerRow">
											<th><?php echo $_ENV["request.cwpage"]["viewType"] . ' ' . $_ENV["application.cw"]["taxSystemLabel"]; ?> Groups</th>
										</tr>
										</thead>
										<tbody>
										<tr>
											<td>
												<input type="hidden" value="<?php echo $taxGroupsQuery['totalRows']; ?>" name="Counter">
												<?php // save changes submit button ?>
												<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
												<div style="clear:right;"></div>
	<?php
		$disabledDeleteCt=0;
												// Method Records Table ?>
												<table class="CWinfoTable CWstripe">
													<tr>
														<th width="220"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group Name</th>
														<th width="85" style="text-align:center;"><input type="checkbox" class="checkAll" name="checkAllDelete" rel="checkAllDel">Delete</th>
														<th width="85" style="text-align:center;"><input type="checkbox" class="checkAll" name="checkAllArchive" rel="checkAllArch">					
			<?php
				if($_GET['view'] == 'arch') {
					echo 'Activate';
				} else {
					echo 'Archive';
				}
			?>
                 </th>
													</tr>
                                                    
                 <?php
				 for($i=0; $i < $taxGroupsQuery['totalRows'];$i++) {
						// QUERY: check for existing related products 
						$taxGroupProductsQuery = CWquerySelectTaxGroupProducts($taxGroupsQuery['tax_group_id'][$i]);
						$taxGroupProducts = $taxGroupProductsQuery['totalRows'];
						?>
													<tr>
														<?php // tax group name ?>
														<td style="text-align:right;">
															<?php // show row number ?>
															<strong><a href="tax-group-details.php?tax_group_id=<?php echo $taxGroupsQuery['tax_group_id'][$i]; ?>"><?php echo $taxGroupsQuery['tax_group_name'][$i]; ?><?php if (strlen(trim($taxGroupsQuery['tax_group_code'][$i]))) { ?> (<?php echo trim($taxGroupsQuery['tax_group_code'][$i]); ?>)<?php } ?></a></strong>
															<?php // hidden fields used for processing update/delete ?>
															<input name="recordIDlist[<?php echo $i; ?>]" type="hidden" value="<?php echo $taxGroupsQuery['tax_group_id'][$i]; ?>">
															<input name="tax_group_id<?php echo $i; ?>" type="hidden" id="tax_group_id<?php echo $i; ?>" value="<?php echo $taxGroupsQuery['tax_group_id'][$i]; ?>">
														</td>
														<?php // delete ?>
														<td style="text-align:center">
															<input type="checkbox" value="<?php echo $taxGroupsQuery['tax_group_id'][$i]; ?>" class="formCheckbox radioGroup checkAllDel" rel="group<?php echo $i; ?>" name="deleteRecord[<?php echo $i; ?>]"<?php if($taxGroupProducts != 0) {?> disabled="disabled"<?php }?>>
					
                    
                    <?php
						if($taxGroupProducts != 0) {
							$disabledDeleteCt++;
						}
						?>								</td>
														<?php // archive ?>
														<td style="text-align:center">
															<input type="checkbox" value="<?php if($_ENV["request.cwpage"]["viewType"] == 'Active') {?>1<?php } else {?>0<?php } ?>" class="checkAllArch formCheckbox radioGroup" rel="group<?php echo $i; ?>" name="tax_group_archive<?php echo $i; ?>">
														</td>
													</tr>
													
                                                    
          		<?php							  
				}
				// /END Method Records Table ?>
				 </table>
				<?php
				// if we have disabled delete boxes, explain 
				if($disabledDeleteCt) {
				?>
												<span class="smallPrint" style="float:right;">Note: records with associated orders cannot be deleted</span>
					<?php
				}
				?>
											</td>
										</tr>
										</tbody>
									</table>
					<?php
			// /END Output Records 
		}
		// /END if records found ?>
							</form>
		<?php
	}
	// /end if tax groups enabled 
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