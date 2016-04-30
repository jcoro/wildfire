<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: ship-ranges.php
File Date: 2012-07-07
Description: Manage Shipping Ranges
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
// default value for active or archived view
if(!isset($_GET['view'])) { $_GET['view'] = "active"; }
// add-new method dropdown default value  
if(!isset($_GET['method'])) { $_GET['method'] = 0; }
// default form values 
if(!isset($_POST['ship_range_method_id'])) { $_POST['ship_range_method_id'] = 0; }
if(!isset($_POST['ship_range_From'])) { $_POST['ship_range_From'] = ""; }
if(!isset($_POST['ship_range_to'])) { $_POST['ship_range_to'] = ""; }
if(!isset($_POST['ship_range_amount'])) { $_POST['ship_range_amount'] = 0; }
if(!isset($_POST['country_id'])) { $_POST['country_id'] = ""; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("view,userconfirm,useralert,clickadd,method");
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: get all active shipping methods 
$methodsQuery = CWquerySelectShippingMethods();
// QUERY: get all ship ranges by country 
$rangesQuery = CWquerySelectShippingCountryRanges();
// /////// 
// ADD NEW SHIP RANGE 
// /////// 
if((isset($_POST['ship_range_method_id'])) && $_POST['ship_range_method_id'] > 0) {
	// make sure end is greater than start 
	if($_POST['ship_range_to'] > $_POST['ship_range_from']) {
		// QUERY: insert new ship range (method ID, from, to, amount)
		$newRecordID = CWqueryInsertShippingRange(
						$_POST['ship_range_method_id'],
						$_POST['ship_range_from'],
						$_POST['ship_range_to'],
						$_POST['ship_range_amount']);
		CWpageMessage("confirm","Shipping Range Added");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&clickadd=1&method=".$_POST['ship_range_method_id']."");
		exit;
	} else {
		CWpageMessage("alert","Error: To amount must be greater than From");
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."&clickadd=1&method=".$_POST['ship_range_method_id']."");
		exit;
	}
}
// /////// 
// /END ADD NEW SHIP RANGE 
// /////// 
// /////// 
// UPDATE / DELETE SHIP RANGES 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['ship_range_id0'])) {
	if(!isset($_POST['deleteRecord'])) { $_POST['deleteRecord'] = array(); }
	$loopCt = 0;
	$updateCt = 0;
	$deleteCt = 0;
	// loop record ids, handle each one as needed 
	foreach ($_POST['recordIDlist'] as $key => $ID) {
		// DELETE RECORDS 
		// if the record ID is marked for deletion 
		if(in_array($ID, $_POST['deleteRecord'])) {
			// QUERY: delete record (record id) 
			$deleteRecord = CWqueryDeleteShippingRange($ID);
			$deleteCt++;
			// if not deleting, update 
		} else {
			// UPDATE RECORDS 
			// make sure end is greater than start 
			if($_POST['ship_range_to'.$loopCt] > $_POST['ship_range_from'.$loopCt]) {
				// QUERY: update ship range (range ID, from, to, amount)
				$updateRecord = CWqueryUpdateShippingRange(
								$_POST['ship_range_id'.$loopCt],
								$_POST['ship_range_from'.$loopCt],
								$_POST['ship_range_to'.$loopCt],
								$_POST['ship_range_amount'.$loopCt]);
				$updateCt++;
			} else {
				$insertError = "Error: Range starting amount ".$_POST['ship_range_from'.$loopCt]." must be less than end amount ".$_POST['ship_range_to'.$loopCt]."";	
			}
		}
		// /END delete vs. update 
		$loopCt++;
	}
	// if we have errors, return showing details about last errant record 
	if(isset($insertError))
	{
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&useralert=".CWurlsafe($insertError)."&clickadd=1");
		exit;
		// if no errors, return showing message 
	} else {
		// get the vars to keep by omitting the ones we don't want repeated 
		$varsToKeep = CWremoveUrlVars("userconfirm,useralert,method");
		// set up the base url 
		$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
		// save confirmation text 
		CWpageMessage("confirm","Changes Saved");
		// save alert text 
		$_ENV["request.cwpage"]["userAlertText"] = '';
		if($deleteCt > 0) {
			$_ENV["request.cwpage"]["userAlertText"].= $deleteCt." Record";
			if($deleteCt > 1) {
				$_ENV["request.cwpage"]["userAlertText"].= "s";
			}
			$_ENV["request.cwpage"]["userAlertText"].= " Deleted";
		}
		echo $_ENV["request.cwpage"]["userAlertText"];
		CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
		// return to page as submitted, clearing form scope 
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&useralert=".CWurlsafe($_ENV["request.cwpage"]["userAlert"])."");
	}
// /end if no errors 
}
// /////// 
// /END UPDATE / DELETE SHIP RANGES 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Manage Shipping Ranges";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Shipping Ranges &amp; Rates";
// Page Subheading (instructions) <h2> 
$head2text = '';
$head2text.= "Manage shipping rates scale";
if($_ENV["application.cw"]["shipChargeBasedOn"] != 'none') {
	$head2text.= " based on ";
	$head2text.= strtolower($_ENV["application.cw"]["shipChargeBasedOn"]);	
}
//echo $head2text;
$_ENV["request.cwpage"]["heading2"] = $head2text; 
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"];
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
			// method selector adds first input value
			jQuery('#methodSelect').change(function() {
				var selVal = jQuery(this).val();
				var changeClass = 'to' + selVal;
				//alert(changeClass);
				var lastTo = 'input[class*=' + changeClass + ']:last';
				var lastToVal = jQuery(lastTo).val();
				var newFromVal = Math.round((Number(lastToVal) + .01) * 100) / 100;
				if(!(isNaN(newFromVal)==true)) {
				jQuery('input[name=ship_range_from]').val(newFromVal);
				} else {
				jQuery('input[name=ship_range_from]').val(0);
				};
				jQuery('input[name=ship_range_to]').focus();
			});

			// add new show-hide
			jQuery('form#addNewForm').hide();
			jQuery('a#showAddNewFormLink').click(function() {
				jQuery(this).hide();
				jQuery('form#addNewForm').show().find('input.focusField').focus();
				jQuery('#methodSelect').trigger('change');
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
$page_First = $page[count($page)-1];
?>   
	<body <?php echo 'class="'.$page_First.'"'; ?>>
		<div id="CWadminWrapper">
			<!-- Navigation Area -->
			<div id="CWadminNav">
				<div class="CWinner">
<?php  include("cwadminapp/inc/cw-inc-admin-nav.php"); ?> 
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
							<strong><a class="CWbuttonLink" id="showAddNewFormLink" href="#">Add New Shipping Range</a></strong>
						</div>
<?php
// /////// 
// ADD NEW RECORD 
// /////// ?>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]."&clickadd=1"; ?>" class="CWvalidate" name="addNewForm" id="addNewForm" method="post">
							<p>&nbsp;</p>
							<h3>Add New Shipping Range</h3>
							<table class="CWinfoTable wide">
								<thead>
								<tr>
									<th>Shipping Method / Country</th>
									<th>From</th>
									<th>To</th>
									<th>Rate</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<?php // country/method ?>
									<td style="text-align:center">
										<div>
											<select name="ship_range_method_id" id="methodSelect">
<?php
$lastCountryName = "asdflkjh";
for($i=0; $i<$methodsQuery['totalRows']; $i++) {
	$newCountry = ($lastCountryName != $methodsQuery['country_name'][$i]);
	if ($newCountry) {
		if ($lastCountryName != "asdflkjh") {
			echo "</optgroup>";
		}
		$lastCountryName = $methodsQuery['country_name'][$i];
?>
                                             	<optgroup label="<?php echo $methodsQuery['country_name'][$i]; ?>">
<?php
	}
?>
													<option value="<?php echo $methodsQuery['ship_method_id'][$i]; ?>"<?php if($methodsQuery['ship_method_id'][$i] == $_GET['method']) { ?> selected="selected"<?php } ?>><?php echo $methodsQuery['ship_method_name'][$i]; ?></option>
<?php
}
?>
                                                </optgroup>
											</select>
										</div>
										<br>
										<input name="SubmitAdd" type="submit" class="CWformButton" id="SubmitAdd" value="Save New Shipping Range">
									</td>
									<?php // from ?>
									<td>
										<input name="ship_range_from" type="text" class="required" title="Beginning range value required (numeric)" value="0" size="11" maxlength="11" onKeyUp="extractNumeric(this,2,true)">
									</td>
									<?php // to ?>
									<td>
										<input name="ship_range_to" type="text" class="required focusField" title="Ending range value required (numeric)" size="11" maxlength="11" onKeyUp="extractNumeric(this,2,true)">
									</td>
									<?php // rate ?>
									<td>
										<input name="ship_range_amount" type="text" class="required" title="Ship range amount required (numeric)" size="7" maxlength="11" onKeyUp="extractNumeric(this,2,true)">
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
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" name="recordForm" id="recordForm" method="post" class="CWobserve">
<?php
// if no records found, show message 
if(!$rangesQuery['totalRows']) { ?>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>No Shipping Ranges available.</strong> <br><br></p>
<?php
} else {
	// output records 
	// Container table ?>
                                <table class="CWinfoTable wide">
									<thead>
									<tr class="headerRow">
										<th>Active Shipping Ranges</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td>
											<input type="hidden" value="<?php echo $rangesQuery['totalRows']; ?>" name="rangeCounter">
											<?php // save changes submit button ?>
											<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
											<div style="clear:right;"></div>
<?php
	// LOOP RANGES BY COUNTRY 
	$countryCt = 0;
	$nonLocalMethods = '';
	$presCountry = "";
	$prevMethod = 0;
	$rowCt = 1;
	for ($i=0; $i<$rangesQuery['totalRows']; $i++) {
		$presCountry = $rangesQuery['country_name'][$i];
?>
                                                <table class="CWinfoTable">
												<tr class="headerRow">
													<th><h3><?php echo $rangesQuery['country_name'][$i]; ?></h3></th>
												</tr>
												<tr>
													<td>
														<?php // Ranges Table ?>
														<table class="CWstripe">
<?php
		// set up counter for varying methods within this country group
		$prevMethod = 0;
		$rowCt = 1;
		while ($i < $rangesQuery['totalRows'] && $rangesQuery['country_name'][$i] == $presCountry) {
			$nextMethod = $rangesQuery['ship_method_id'][$i];
			$checkClass = 'range'.$nextMethod;
			$fromClass = 'from'.$nextMethod;
			$toClass = 'to'.$nextMethod;
			// spacer row 
			if($nextMethod != $prevMethod) {
				if($prevMethod != 0) {
?>
																	<tr>
																	<td colspan="5">&nbsp;</td>
																	</tr>
<?php
				}
				$rowCt = 1;
				// if localcalc, show standard header 
				if($rangesQuery['ship_method_calctype'][$i] == 'localcalc') { ?>
                                                    <tr class="headerRow">
                                                        <th width="182"><?php echo $rangesQuery['ship_method_name'][$i]; ?></th>
                                                        <th width="105">From</th>
                                                        <th width="105">To</th>
                                                        <th width="84">Rate</th>
                                                        <th width="82" style="text-align:center;"><input type="checkbox" class="checkAll" rel="<?php echo $checkClass; ?>">Delete</th>
                                                    </tr>
<?php
				}
			}
?>	
													<tr<?php if($rangesQuery['ship_method_calctype'][$i] != 'localcalc') { ?> style="display:none;"<?php } ?>>
														<?php // method name ?>
														<td style="text-align:left;">
<?php
		// hidden fields used for processing update/delete 
		echo $rowCt;
?>
                                                        <input name="ship_range_id<?php echo $i; ?>" type="hidden" value="<?php echo $rangesQuery['ship_range_id'][$i]; ?>">
                                                            <input name="recordIDlist[<?php echo $i;?>]" type="hidden" value="<?php echo $rangesQuery['ship_range_id'][$i]; ?>">
														</td>
														<?php // from ?>
														<td>
														<?php if($nextMethod != $prevMethod) { ?>
															<input type="hidden"name="ship_range_from<?php echo $i;?>" id="ship_range_from<?php echo $i; ?>" value="0">&nbsp;0&nbsp;
														<?php } else { ?>
                                                            <input name="ship_range_from<?php echo $i; ?>" type="text" class="<?php echo $fromClass; ?>" id="ship_range_from<?php echo $i; ?>" size="9" maxlength="11" value="<?php echo $rangesQuery['ship_range_from'][$i]; ?>" onBlur="checkValue(this)" onKeyUp="extractNumeric(this,2,true)">
														<?php } ?>                                                                
														</td>
														<?php // to ?>
                                                        <td>
                                                            <input name="ship_range_to<?php echo $i; ?>" type="text" class="<?php echo $toClass; ?>" id="ship_range_to<?php echo $i; ?>" size="9" maxlength="11" value="<?php echo $rangesQuery['ship_range_to'][$i]; ?>" onblur="checkValue(this)" onKeyUp="extractNumeric(this,2,true)">
                                                        </td>
														<?php // rate ?>
                                                        <td>
                                                            <input name="ship_range_amount<?php echo $i; ?>" type="text" id="ship_range_amount<?php echo $i; ?>" size="5" maxlength="11" value="<?php echo number_format($rangesQuery['ship_range_amount'][$i], 2); ?>" onblur="checkValue(this)" onKeyUp="extractNumeric(this,2,true)">
                                                        </td>
														<?php // delete ?>
                                                        <td style="text-align:center">
                                                            <input type="checkbox" value="<?php echo $rangesQuery['ship_range_id'][$i]; ?>" class="formCheckbox <?php echo $checkClass; ?>" name="deleteRecord[<?php echo $i; ?>]">
                                                        </td>
                                                    </tr>
<?php
			$rowCt = $rowCt + 1;
			$prevMethod = $nextMethod;
			// if not localcalc, show message 
			if($rangesQuery['ship_method_calctype'][$i] != 'localcalc') {
			if(!ListFind($nonLocalMethods,$rangesQuery['ship_method_id'][$i])) {
			?>
													<tr class="headerRow">
															<th><strong>
															<?php echo $rangesQuery['ship_method_name'][$i]; ?></strong>
															</th>
															<th colspan="4">Calculation set to "<?php echo $rangesQuery['ship_method_calctype'][$i]; ?>"</th>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="5"><a href="ship-methods.php">Set this shipping method to Local calculation (localcalc)</a> to enable shipping ranges </td>
                                                    </tr>
			<?php	
					if (strlen(trim($nonLocalMethods))) $nonLocalMethods .= ",";
					$nonLocalMethods .= $rangesQuery['ship_method_id'][$i];
				}
				// /end if not localcalc 
			}
			$i++;
		}
		$i--;
		// /END Method Records Table 
	}
	?>
	<?php
	for($i=0; $i<$methodsQuery['totalRows']; $i++) {
				if(!(in_array($methodsQuery['ship_method_name'][$i], $rangesQuery['ship_method_name']))) {
		
	?>
		
													<tr class="headerRow">
															<th><strong>
															<?php echo $methodsQuery['ship_method_name'][$i]; ?></strong>
															</th>
															<th colspan="4">Calculation set to "<?php echo $methodsQuery['ship_method_calctype'][$i]; ?>"</th>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="5"><a href="ship-methods.php">Set this shipping method to Local calculation (localcalc)</a> to enable shipping ranges </td>
                                                    </tr>
	<?php 
		}
	}
	?>
	
	<?php //END Country Table ?>
	
	
	
											</table>
                                    	</td>
                                	</tr>
                            	</table>
<?php
	// /END Loop Methods by Country 
	// show the submit button here if we have a long list 
	if($countryCt > 1) { ?>
												<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
<?php
	}
?>	
											<span class="smallPrint" style="float:right;">
												Note: The first range of any method must start with 0
											</span>										
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