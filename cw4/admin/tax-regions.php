<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: tax-regions.php
File Date: 2012-05-05
Description: Manage Tax Regions
==========================================================
*/
//NOTE: showTaxID has no visible input, not used for default CW display,
//but is available if a specific site modification requires it.
//The taxID on invoices can be controlled via global Tax Settings
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("manager,developer");
// PAGE PARAMS 
if(!isset($_GET['tax_region_id'])) { $_GET['tax_region_id'] = 0; }
if(!isset($_GET['country_id'])) { $_GET['country_id'] = $_ENV["application.cw"]["defaultCountryID"]; }
if(!isset($_ENV["request.cwpage"]["currentID"])) { $_ENV["request.cwpage"]["currentID"] = $_GET['country_id']; }
// default form values 
if(!isset($_POST['ShowTaxID'])) { $_POST['ShowTaxID'] = false; }
if(!isset($_POST['taxregion_Name'])) { $_POST['taxregion_Name'] = ""; }
// default values for sort 
if(!isset($_GET['sortby'])) { $_GET['sortby'] = "region_Location"; }
if(!isset($_GET['sortdir'])) { $_GET['sortdir'] = "asc"; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("view,userconfirm,useralert,clickadd");
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: Get tax groups (active/archived )
$taxGroupsQuery = CWquerySelectTaxGroups(0);
// QUERY: Get tax regions (id (0=all))
$taxRegionsQuery = CWquerySelectTaxRegions(0,null,null,true);
// QUERY: Get states for select menu 
$countriesQuery = CWquerySelectCountries(0);
// QUERY: Get states for select menu 
$statesQuery = CWquerySelectStates();
// /////// 
// ADD NEW TAX REGION 
// /////// 
// if submitting the 'add new' form, and  
if(isset($_POST['taxregion_Name']) && strlen(trim($_POST['taxregion_Name']))) {
	// determine tax method/group values 
	if(is_numeric($_POST['ShippingTax'])) {
		$insertTaxMethod = "Tax Group";
		$insertTaxGroup = $_POST['ShippingTax'];
	} else {
		$insertTaxMethod = $_POST['ShippingTax'];
		$insertTaxGroup = 0 ;
	}
	// verify numeric values 
	if(!is_numeric($_POST['CountryID'])) {
		$_POST['CountryID'] = 0;
	}
	if(!is_numeric($_POST['StateID'])) {
		$_POST['StateID'] = 0;
	}
	if(!is_numeric($_POST['TaxID'])) {
		$_POST['TaxID'] = 0;
	}
	if(!is_numeric($insertTaxGroup)) {
		$insertTaxGroup = 0;
	}
	// QUERY: insert new tax region (name, country, state, show tax, tax method, tax group)
	$newRecordID = CWqueryInsertTaxRegion(
					$_POST['taxregion_Name'],
					$_POST['CountryID'],
					$_POST['StateID'],
					$_POST['TaxID'],
					$_POST['ShowTaxID'],
					$insertTaxMethod,
					$insertTaxGroup);
					
	// if no error returned from insert query 
	if(substr($newRecordID,0,2) != '0-') {
		// update complete: return to page showing message 
		CWpageMessage("confirm","".$_ENV["application.cw"]["taxSystemLabel"]." Region ".$_POST['taxregion_Name']." Added");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&clickadd=1");
		exit;
	}
	// if we have an insert error, show message, do not insert 
	else
	{
		$newid = explode('-',$newRecordID);
		$newid_last = $newid[count($newid) - 1];
		$_ENV["request.cwpage"]["errorMessage"] = $newid_last;
		CWpageMessage("alert",$_ENV["request.cwpage"]["errorMessage"]);
		$_GET['clickadd'] = 1;
	}
	// /END duplicate/error check 
}
// /////// 
// /END ADD TAX REGION 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Manage".$_ENV["application.cw"]["taxSystemLabel"]."Regions";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = $_ENV["application.cw"]["taxSystemLabel"]."Regions Management";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Manage active ".$_ENV["application.cw"]["taxSystemLabel"]." Regions or add a new ".$_ENV["application.cw"]["taxSystemLabel"]." Region";
// current menu marker 
//$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"];
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
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"];?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
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
			// related selects - class on option elements relates the selectors
			jQuery('#s1').change(function() {
			jQuery('#s2').val("");
			});
			// function to copy the select list, save clone for next use
			var $s2copy = function() {
			jQuery('#s2').clone().attr('id','s2-copy').insertBefore('#s2').hide();
			};
			$s2copy();
			// country/state change functions
			jQuery('#s1').change(function() {
			// get the class of the selected item
			var classtoshow = jQuery(this).children('option:selected').attr('class');
			// if class is null
			if (classtoshow == "") {
			jQuery('#s2').remove();
			jQuery('#s2-copy').show().attr('id','s2');
			$s2copy();
			}
			else
			// if class has value
			{
			// remove the existing state list
			jQuery('#s2').remove();
			// show the copy, change the id
			jQuery('#s2-copy').show().attr('id','s2');
			// create and save the copy (function above)
			$s2copy();
			// remove the unwanted options
			var selClass = '[class*=' + classtoshow + ']';
			jQuery('#s2').children('option').not(selClass).remove();
			}
			});
			// trigger selection of first option on page load
			jQuery('#s1').children('option:nth-child(1)').prop('selected',true).trigger('change');

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
<?php 			include("cwadminapp/inc/cw-inc-admin-nav.php");
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
						// if tax regions are not enabled 
						if(strtolower($_ENV["application.cw"]["taxSystem"]) != "groups") { ?>
							<div class="CWadminControlWrap">
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p class="formText"><strong><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Regions disabled. To enable, select '<?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> System: Groups' <a href="config-settings.php?group_id=5">here</a></strong></p>
							</div>
<?php
						} else if (strtolower($_ENV["application.cw"]["taxCalctype"]) != "localtax") {
?>
							<div class="CWadminControlWrap">
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p class="formText"><strong><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Regions disabled for calculation method '<?php echo $_ENV["application.cw"]["taxCalctype"]; ?>'. To enable, select 'Local Database' <a href="config-settings.php?group_ID=5">here</a></strong></p>
							</div>
<?php
						} else {
							// if using tax regions, proceed 
                        	// SHOW FORM LINK ?>
							<div class="CWadminControlWrap">
								<a class="CWbuttonLink" id="showAddNewFormLink" href="#">Add New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Region</a>
							</div>
<?php
							// /END SHOW FORM LINK 
							// /////// 
							// ADD NEW TAX REGION 
							// /////// 
							// FORM ?>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate" name="addNewForm" id="addNewForm" method="post">
								<p>&nbsp;</p>
								<h3>Add New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Region</h3>
								<table class="CWinfoTable">
									<tbody>
									<tr>
										<?php // country / state selectors ?>
										<th class="label"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Region: <div class="smallPrint">( Country / State )</div></th>
										<td>
											<div id="countryMenus">
												<?php // country ?>
												<select name="CountryID" id="s1">
													<option value="" class="any" selected="selected">Select Country</option>
<?php
													for($i=0; $i<$countriesQuery['totalRows']; $i++) {
														$optclass = str_replace(" ","-",strtolower($countriesQuery['country_name'][$i]));
?>
													<option class="<?php echo $optclass; ?>" value="<?php echo $countriesQuery['country_id'][$i]; ?>"><?php echo $countriesQuery['country_name'][$i]; ?></option>
<?php														
													}
?>                                                    
												</select>
												:
												<?php // state ?>
												<select name="StateID" id="s2">
													<option value="" class="any" selected="selected">Select State/Prov</option>
<?php
													$lastCountryName = "";
													for($i=0; $i<$statesQuery['totalRows']; $i++) {
														$optclass = str_replace(" ","-",strtolower($statesQuery['country_name'][$i]));
														$lastCountryName = $statesQuery['country_name'][$i];
?>
														<option value="0" class="<?php echo $optclass;?>">Entire Country</option>
<?php
														while ($i < $statesQuery['totalRows'] && $lastCountryName == $statesQuery['country_name'][$i]) {
?>
                                                        <option value="<?php echo $statesQuery['stateprov_id'][$i]; ?>" class="<?php echo $optclass; ?>"><?php echo $statesQuery['stateprov_name'][$i]; ?></option>
<?php
															$i++;
														}
														$i--;
													}
?>                                                    
												</select>
											</div>
										</td>
									</tr>
									<tr>
										<th class="label"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Label</th>
										<td><input name="taxregion_Name" class="required" title="<?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Label is required" type="text" id="taxregion_Name" size="30"></td>
									</tr>
									<tr>
										<th class="label"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Number</th>
										<td><input name="TaxID" type="text" id="TaxID" size="30"></td>
									</tr>
									<tr>
										<th class="label">Shipping <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Method</th>
										<td>
											<select name="ShippingTax" id="ShippingTax">
												<option value="No Tax">No <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> on shipping</option>
												<option value="Highest Item Taxed">Use <?php echo strtolower($_ENV["application.cw"]["taxSystemLabel"]); ?> rate applied to any item in the order</option>
												<optgroup label="Based on a Tax Group"> 
<?php
												for($i=0; $i<$taxGroupsQuery['totalRows']; $i++) { ?>
													<option value="<?php echo $taxGroupsQuery['tax_group_id'][$i]; ?>"><?php echo $taxGroupsQuery['tax_group_name'][$i]; ?></option>	
<?php
												}
?>                                                
                                                 </optgroup>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="2" style="text-align:center">
											<?php // submit button ?>
											<input name="SubmitAdd" type="submit" class="CWformButton" id="SubmitAdd" value="Save New <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Region">
										</td>
									</tr>
									</tbody>
								</table>
							</form>
<?php
							// /////// 
							// /END ADD TAX REGION 
							// /////// 
							// /////// 
							// LIST REGIONS 
							// /////// 
							// if no records found, show message 
							if(!$taxRegionsQuery['totalRows']) { ?>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>No <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Regions available.</strong> <br><br></p>
<?php
							} else {
                            	// output records 
								// Container table ?>
                                <p>&nbsp;</p>
								<table class="wide">
									<thead>
									<tr class="headerRow">
										<th>Active <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Regions</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td>
											<div style="clear:right;"></div>
											<?php // Method Records Table ?>
											<p>&nbsp;</p>
											<table class="CWinfoTable CWstripe CWsort" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
												<thead>
												<tr class="sortRow">
													<th class="noSort">Edit</th>
													<th class="region_Location">Location</th>
													<th class="tax_region_label"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Label </th>
													<th class="tax_region_tax_id"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> ID </th>
													<th class="tax_region_ship_tax_method">Ship <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Method </th>
												</tr>
												</thead>
												<tbody>
<?php
												for($i=0; $i<$taxRegionsQuery['totalRows']; $i++) { ?>
                                                	<tr>
													<td style="text-align:center;">
														<a href="tax-region-details.php?tax_region_id=<?php echo $taxRegionsQuery['tax_region_id'][$i]; ?>" title="Manage <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Region" class="detailsLink"><img src="img/cw-edit.gif" width="15" height="15" alt="Manage <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Region Details"></a>
													</td>
													<td>
														<strong><a href="tax-region-details.php?tax_region_id=<?php echo $taxRegionsQuery['tax_region_id'][$i]; ?>" class="detailsLink"><?php echo $taxRegionsQuery['country_name'][$i]; ?><?php if($taxRegionsQuery['stateprov_name'][$i] != "") { echo ":".$taxRegionsQuery['stateprov_name'][$i]; } ?></a></strong>
													</td>
													<td><?php echo $taxRegionsQuery['tax_region_label'][$i];?></td>
													<td><?php echo $taxRegionsQuery['tax_region_tax_id'][$i];?></td>
													<td><?php echo $taxRegionsQuery['tax_region_ship_tax_method'][$i]; ?>
<?php
														if($taxRegionsQuery['tax_group_name'][$i]!= "") { ?>
                                                        	:
                                                            <a href="TaxGroup.php?id=<?php echo $taxRegionsQuery['tax_region_ship_tax_group_id'][$i]; ?>" class="detailsLink"><?php echo $taxRegionsQuery['tax_group_name'][$i]; ?></a>
<?php														
														}
?>                                                    
													</td>
												</tr>
<?php												
												}
?>												
												</tbody>
											</table>
											<p>&nbsp;</p>
											<?php // /END Records Table ?>
										</td>
									</tr>
									</tbody>
								</table>
<?php
								// /END Output Records 
							}
							// /END if records found 
						}
						// /end if tax regions enabled 
						// /////// 
						// /END LIST REGIONS 
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