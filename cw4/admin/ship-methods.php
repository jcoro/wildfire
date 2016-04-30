<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: ship-methods.php
File Date: 2012-07-03
Description: Manage Shipping Methods
==========================================================
Notes:
To add new shipping calculation types, see two comments below saying
"add new shipping methods here"
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"]= CWauth('merchant,developer');
// PAGE PARAMS 
// default value for active or archived view
if(!(isset($_GET['view']))) $_GET['view']='active';
// default form values 
if(!(isset($_POST['ship_method_name']))) $_POST['ship_method_name']='';
if(!(isset($_POST['ship_method_rate']))) $_POST['ship_method_rate']=0;
if(!(isset($_POST['ship_method_sort']))) $_POST['ship_method_sort']=0;
if(!(isset($_POST['ship_method_calctype']))) $_POST['ship_method_calctype']='localcalc';	
if(!(isset($_POST['country_id']))) $_POST['country_id']='';
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars('view,userconfirm,useralert,clickadd');
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// ACTIVE VS. ARCHIVED 
if($_GET['view']=='arch') {
	$_ENV["request.cwpage"]["viewType"]='Archive';
	$_ENV["request.cwpage"]["recordsArchived"]=1;
	$_ENV["request.cwpage"]["subHead"]='Manage archieve Shipping Methods or add a new Method';
} else {
	$_ENV["request.cwpage"]["viewType"]='Active';
	$_ENV["request.cwpage"]["recordsArchived"]=0;
	$_ENV["request.cwpage"]["subHead"]='Manage active Shipping Methods or add a new Method';	
}
// QUERY: Get all available countries 
$countriesQuery = CWquerySelectCountries(0);
// QUERY: Get all ship methods by status 
$methodsQuery = CWquerySelectStatusShipMethods($_ENV["request.cwpage"]["recordsArchived"]);
// /////// 
// ADD NEW SHIP METHOD 
// /////// 
// if submitting the 'add new' form, and  
if(isset($_POST['ship_method_name']) && strlen(trim($_POST['ship_method_name'])) && $_ENV["request.cwpage"]["recordsArchived"] == 0) {
	// QUERY: insert new ship method (name, country ID, rate, order, calctype, archived)
	$newRecordID = CWqueryInsertShippingMethod(
						trim($_POST['ship_method_name']),
						$_POST['country_id'],
						$_POST['ship_method_rate'],
						$_POST['ship_method_sort'],
						$_POST['ship_method_calctype'],
						0
						);
	// if no error returned from insert query 
	if(!(substr($newRecordID,0,2) == '0-')) {
		// update complete: return to page showing message 
		CWpageMessage("confirm","Shipping Method ".$_POST['ship_method_name']." Added");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&clickadd=1');
		exit;
	} else {
		// if we have an insert error, show message, do not insert 
		$newvarList=explode('-',$newRecordID);
		$_ENV["request.cwpage"]["errorMessage"]=$newvarList[count($newvarList)-1];
		CWpageMessage("alert",$_ENV["request.cwpage"]["errorMessage"]);
		$_GET['clickadd']=1;
	}
	// /END duplicate/error check 
}
// /////// 
// /END ADD SHIP METHOD 
// /////// 
// /////// 
// UPDATE / DELETE SHIP METHODS 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['ship_method_id0'])) {
	if(!(isset($_POST['deleteRecord']))) $_POST['deleteRecord']=array();
	$loopCt=0;
	$updateCt=0;
	$deleteCt=0;
	$archiveCt=0;
	$activeCt=0;
	// loop record ids, handle each one as needed 
	foreach ($_POST['recordIDlist'] as $key => $id) {
		// DELETE RECORDS 
		// if the record ID is marked for deletion 
		if(in_array($id, $_POST['deleteRecord'])) {
			// QUERY: delete record (record id) 
			$deleteRecord= CWqueryDeleteShippingMethod($id);
			$deleteCt++;
			// if not deleting, update 
		} else {
			// UPDATE RECORDS 
			// param for checkbox values 
			if(!isset($_POST['ship_method_archive'.$loopCt])) $_POST['ship_method_archive'.$loopCt]= $_ENV["request.cwpage"]["recordsArchived"];
			// verify numeric sort order 
			if(!(is_numeric($_POST['ship_method_sort'.$loopCt]))) $_POST['ship_method_sort'.$loopCt] = 0;
			// QUERY: update record (ID, name, rate, order, archived) 
			$updateRecord = CWqueryUpdateShippingMethod(
								$_POST['ship_method_id'.$loopCt],
								$_POST['ship_method_name'.$loopCt],
								$_POST['ship_method_rate'.$loopCt],
								$_POST['ship_method_sort'.$loopCt],
								$_POST['ship_method_calctype'.$loopCt],
								$_POST['ship_method_archive'.$loopCt]
								);
			if($_POST['ship_method_archive'.$loopCt] == 1 && $_ENV["request.cwpage"]["recordsArchived"] == 0) {
				$archiveCt++;
			}
			elseif($_POST["ship_method_archive".$loopCt] == 0 && $_ENV["request.cwpage"]["recordsArchived"] == 1) {
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
	$_ENV["request.cwpage"]["relocateURL"]=CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	CWpageMessage("confirm","Changes Saved");
	// save alert text 
	$_ENV["request.cwpage"]["userAlertText"]='';    
	if($archiveCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"].=$archiveCt. ' Record';
		if($archiveCt >1) $_ENV["request.cwpage"]["userAlertText"].='s';
		$_ENV["request.cwpage"]["userAlertText"].=" archived";
	}
	elseif($activeCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"].=$activeCt. ' Record';
		if($activeCt >1) $_ENV["request.cwpage"]["userAlertText"].='s';
		$_ENV["request.cwpage"]["userAlertText"].=" activated";	
	}
	if($deleteCt > 0) {
		if($activeCt || $archiveCt) $_ENV["request.cwpage"]["userAlertText"].='<br>';
		$_ENV["request.cwpage"]["userAlertText"].=  $deleteCt.' Record';
		if($deleteCt >1) $_ENV["request.cwpage"]["userAlertText"].='s';
		$_ENV["request.cwpage"]["userAlertText"].=" deleted";	
	}
	CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
	// return to page as submitted, clearing form scope 
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_ENV["request.cwpage"]["baseURL"].'?&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
	exit;
}
// /////// 
// END UPDATE / DELETE SHIP METHODS 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"]="Manage Shipping Method";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"]='Shipping Methods Management';
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"]='Add or edit shipping transport options by country';
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
                            	<a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?view=act">View Active</a>
<?php
} else {
?>
                        		<a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?view=arch">View Archived</a>
<?php
	// link for add-new form 
	if($_ENV["request.cwpage"]["recordsArchived"] ==0) {	
?>	
									&nbsp;&nbsp;<a class="CWbuttonLink" id="showAddNewFormLink" href="#">Add New Shipping Method</a>
<?php
	}
}
?>
							</strong>
						</div>
<?php
// /END LINKS FOR VIEW TYPE 
// /////// 
// ADD NEW METHOD 
// /////// 
if($_ENV["request.cwpage"]["recordsArchived"]==0) {
	// FORM ?>
							<form action="ship-methods.php" class="CWvalidate" name="addNewForm" id="addNewForm" method="post">
								<p>&nbsp;</p>
								<h3>Add New Shipping Method</h3>
								<table class="CWinfoTable wide">
									<thead>
									<tr>
										<th>Shipping Method</th>
										<th>Country</th>
										<th>Base Rate</th>
										<th>Sort</th>
										<th>Calculation</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<?php // ship method ?>
										<td style="text-align:center">
											<div>
												<input name="ship_method_name" type="text" size="20" class="required focusField" title="Shipping Method is required" id="ship_method_name" value="<?php echo $_POST['ship_method_name']; ?>">
											</div>
											<br>
											<input name="SubmitAdd" type="submit" class="CWformButton" id="SubmitAdd" value="Save New Shipping Method">
										</td>
										<?php // country ?>
										<td style="text-align:center">
											<select name="country_id">
<?php
	for($i=0;$i < $countriesQuery['totalRows'];$i++) {
?>
                        						<option value="<?php echo $countriesQuery['country_id'][$i]; ?>"<?php if($_POST['country_id'] == $countriesQuery['country_id'][$i]) {?> selected="selected"<?php }?> title="<?php echo $countriesQuery['country_name'][$i]; ?>"><?php echo substr($countriesQuery['country_name'][$i],0,22); ?><?php if(strlen($countriesQuery['country_name'][$i]) > 25) {?>...<?php }?></option>
<?php
	}
?>
                                            </select>
										</td>
										<?php // rate ?>
										<td><input name="ship_method_rate" type="text" size="6" maxlength="7" class="required" title="Rate is required" id="ship_method_rate" value="<?php echo $_POST['ship_method_rate']; ?>" onKeyUp="extractNumeric(this,2,true)"> </td>
										<?php // sort order ?>
										<td>
											<input name="ship_method_sort" type="text" id="ship_method_sort" size="4" maxlength="7" class="required sort" title="Sort order is required" value="<?php echo $_POST['ship_method_sort']; ?>" onKeyUp="extractNumeric(this,2,true)">
										</td>
										<?php // calculation type ?>
										<td style="text-align:center">
											<?php // add new shipping methods here ?>
											<select name="ship_method_calctype" id="ship_method_calctype">
											<option value="localcalc"<?php if($_POST['ship_method_calctype']=='localcalc') {?> selected="selected"<?php }?>>Local (CW)</option>
											<option value="upsgroundcalc"<?php if($_POST['ship_method_calctype']=='upsgroundcalc') {?> selected="selected"<?php }?>>UPS Ground</option>
											<option value="ups3daycalc"<?php if($_POST['ship_method_calctype']=='ups3daycalc') {?> selected="selected"<?php }?>>UPS 3-Day</option>
											<option value="upsnextdaycalc"<?php if($_POST['ship_method_calctype']=='upsnextdaycalc') {?> selected="selected"<?php }?>>UPS Next Day</option>
											<option value="fedexgroundcalc"<?php if($_POST['ship_method_calctype']=='fedexgroundcalc') {?> selected="selected"<?php }?>>FedEx Ground</option>
											<option value="fedexstandardovernightcalc"<?php if($_POST['ship_method_calctype']=='fedexstandardovernightcalc') {?> selected="selected"<?php }?>>FedEx Overnight</option>
											<option value="fedex2daycalc"<?php if($_POST['ship_method_calctype']=='fedex2daycalc') {?> selected="selected"<?php }?>>FedEx Two Day</option>
											</select>
										</td>
									</tr>
									</tbody>
								</table>
							</form>
						
<?php
}
// /////// 
// /END ADD NEW METHOD 
// /////// 
// /////// 
// EDIT RECORDS 
// /////// ?>
						<form action="ship-methods.php?view=<?php echo $_GET['view']; ?>" name="recordForm" id="recordForm" method="post" class="CWobserve">
<?php
// if no records found, show message 
if(!($methodsQuery['totalRows'])) { 
?>
                        		<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>No <?php echo $_ENV["request.cwpage"]["viewType"]; ?> Shipping Methods available.</strong> <br><br></p>
<?php
// if records found 
} else {
	// output records 
	// Container table ?>
								<table class="CWinfoTable wide">
									<thead>
									<tr class="headerRow">
										<th><?php echo $_ENV["request.cwpage"]["viewType"]; ?> Shipping Methods</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td>
										 <input type="hidden" value="<?php echo $_ENV["request.cwpage"]["viewType"]?>" name="checkstatus">
											<input type="hidden" value="<?php echo $methodsQuery['totalRows']; ?>" name="methodCounter">
											<?php // save changes submit button ?>
											<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
											<div style="clear:right;"></div>
<?php
    // LOOP METHODS BY COUNTRY 
    $disabledDeleteCt=0;
    $countryCt=0;
    $lastCountry = 0;
    // Country Table 
    for ($i=0; $i<$methodsQuery["totalRows"]; $i++) {
        if ($lastCountry != $methodsQuery["country_id"][$i]) {
            if ($i != 0) {
                echo "</table></td></tr></table>";
            }
            $lastCountry = $methodsQuery["country_id"][$i];
?>
											<table class="CWinfoTable">
												<tr class="headerRow">
													<th><h3><?php echo $methodsQuery['country_name'][$i]; ?></h3></th>
												</tr>
												<tr>
													<td>
                                <?php // Method Records Table ?>
														<table class="CWstripe">
															<tr>
																<th width="198">Shipping Method</th>
																<th width="24">ID</th>
																<th width="75">Base Rate</th>
																<th width="55">Sort</th>
																<th width="115">Calculation Type</th>
																<th width="85"><input type="checkbox" class="checkAll" name="checkAllDelete" rel="checkAllDel<?php echo $methodsQuery['country_id'][$i]; ?>">Delete</th>
																<th width="85">
																	<input type="checkbox" class="checkAll" name="checkAllArchive" rel="checkAllArch<?php echo $methodsQuery['country_id'][$i]; ?>">
<?php
			if($_GET['view'] =='arch') {
				?>Activate<?php
			} else {
				?>Archive<?php
			}
?>
                                								</th>
															</tr>
<?php
		}
		$methodOrdersQuery = CWquerySelectShippingMethodOrders($methodsQuery['ship_method_id'][$i]);
		$methodOrders = $methodOrdersQuery['totalRows'];	
		// QUERY: check for existing related ranges 
		$methodRangesQuery = CWquerySelectShippingMethodRanges($methodsQuery['ship_method_id'][$i]);
		$methodRanges = $methodRangesQuery['totalRows'];
?>
                                							<tr>
																<?php // method name ?>
																<td style="text-align:right">
																	<input name="ship_method_name<?php echo $i; ?>" type="text" id="ship_method_name<?php echo $i; ?>" size="12"  value="<?php echo $methodsQuery['ship_method_name'][$i]; ?>" onBlur="checkValue(this)">
																</td>
																<?php // ID ?>
																<td><?php echo $methodsQuery['ship_method_id'][$i]; ?></td>
																<?php // rate ?>
																<td style="text-align:center">
																	<input name="ship_method_rate<?php echo $i; ?>" id="ship_method_rate<?php echo $i; ?>" type="text" value="<?php echo number_format($methodsQuery['ship_method_rate'][$i],2) ; ?>" size="5" maxlength="7" onblur="checkValue(this)" onKeyUp="extractNumeric(this,2,true)">
																</td>
																<?php // sort order ?>
																<td style="text-align:center">
																	<input name="ship_method_sort<?php echo $i; ?>" type="text" value="<?php echo $methodsQuery['ship_method_sort'][$i]; ?>" size="3" maxlength="7" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">
																	<?php // hidden fields used for processing update/delete ?>
																	<input name="recordIDlist[<?php echo $i; ?>]" type="hidden" value="<?php echo $methodsQuery['ship_method_id'][$i]; ?>">
																	<input name="ship_method_id<?php echo $i; ?>" type="hidden" id="ship_method_id<?php echo $i; ?>" value="<?php echo $methodsQuery['ship_method_id'][$i]; ?>">
																</td>
																<?php // calculation type ?>
																<td style="text-align:center">
																	<?php // add new shipping methods here ?>
																	<select name="ship_method_calctype<?php echo $i; ?>" id="ship_method_calctype<?php echo $i + 1; ?>">
																	<option value="localcalc"<?php if($methodsQuery['ship_method_calctype'][$i] == 'localcalc') {?> selected="selected"<?php }?>>Local (CW)</option>
																	<option value="upsgroundcalc"<?php if($methodsQuery['ship_method_calctype'][$i] == 'upsgroundcalc') {?> selected="selected"<?php }?>>UPS Ground</option>
																	<option value="ups3daycalc"<?php if($methodsQuery['ship_method_calctype'][$i] == 'ups3daycalc') {?> selected="selected"<?php }?>>UPS 3 Day</option>
																	<option value="upsnextdaycalc"<?php if($methodsQuery['ship_method_calctype'][$i] == 'upsnextdaycalc') {?> selected="selected"<?php }?>>UPS Next Day</option>
                                                                    <option value="fedexgroundcalc"<?php if($methodsQuery['ship_method_calctype'][$i] == 'fedexgroundcalc') {?> selected="selected"<?php }?>>FedEx Ground</option>
                                                                    <option value="fedexstandardovernightcalc"<?php if($methodsQuery['ship_method_calctype'][$i] == 'fedexstandardovernightcalc') {?> selected="selected"<?php }?>>FedEx Overnight</option>
                                                                    <option value="fedex2daycalc"<?php if($methodsQuery['ship_method_calctype'][$i] == 'fedex2daycalc') {?> selected="selected"<?php }?>>FedEx Two Day</option>
																	</select>
																</td>
																<?php // delete ?>
																<td style="text-align:center">
																	<input type="checkbox" value="<?php echo $methodsQuery['ship_method_id'][$i]; ?>" class="checkAllDel<?php echo $methodsQuery['country_id'][$i]; ?> formCheckbox radioGroup" rel="group<?php echo $i; ?>" name="deleteRecord[<?php echo $i; ?>]"<?php if($methodOrders !=0 || $methodRanges !=0) {?> disabled="disabled"<?php }?>>
<?php
		if($methodOrders !=0 || $methodRanges !=0) {   		
			$disabledDeleteCt++;
		}
?>
																</td>
																<?php // archive ?>
																<td style="text-align:center">
                                                                    <input type="checkbox" value="<?php if( $_ENV["request.cwpage"]["viewType"]=='Active') { ?>1<?php } else { ?>0<?php } ?>" class="checkAllArch<?php echo $methodsQuery['country_id'][$i]; ?> formCheckbox radioGroup" rel="group<?php echo $i; ?>" name="ship_method_archive<?php echo $i; ?>">
																</td>
															</tr>
<?php
		// /END Country Table 
		$countryCt++;	
	}
	// /END Method Records Table ?>
                            							</table>
													</td>
												</tr>
                                            </table>
<?php
	// /END Loop Methods by Country 
	// show the submit button here if we have a long list 		
	if($countryCt > 1) {
?>
                                			<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
<?php
	}
	// if we have disabled delete boxes, explain 
	if($disabledDeleteCt) {
?>
											<span class="smallPrint" style="float:right;">Note: records with associated orders or active ship ranges cannot be deleted</span>
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