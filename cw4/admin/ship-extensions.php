<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: ship-extensions.php
File Date: 2012-05-01
Description: Manage Shipping Location Extensions
Note: Only active states and countries are shown.
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth('merchant,developer');
// PAGE PARAMS 
if(!(isset($_GET['country_id'])))
	$_GET['country_id']=$_ENV["application.cw"]["defaultCountryID"];
if(!(isset($_ENV["request.cwpage"]["currentID"]))) {
	if(!isset($_GET['country_id']))
		$_ENV["request.cwpage"]["currentID"]=1;
	else
		$_ENV["request.cwpage"]["currentID"]=$_GET['country_id'];
}
// default values for sort 
if(!(isset($_GET['sortby'])))
	$_GET['sorty']='stateprove_code';

if(!(isset($_GET['sortdir'])))
	$_GET['sortdir']='asc';
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars('view,userconfirm,useralert,sortby,sortdir');
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: Get all available countries 
$countriesQuery=CWquerySelectCountries(0);
// if only one country, set page ID to that value 
if ($countriesQuery["totalRows"] == 1) $_ENV["request.cwpage"]["currentID"] = $countriesQuery["country_id"][0];
// QUERY: Get all available active states for the country in url 
$stateprovQuery = CWquerySelectStates($_ENV["request.cwpage"]["currentID"],true);
// /////// 
// UPDATE SHIP EXTENSIONS 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['stateprov_ship_ext0'])) {
	$loopCt = 0;
	$updateCt = 0;
	// loop record ids, handle each one as needed 
	foreach ($_POST['recordIDlist'] as $key => $ID) {	
		// UPDATE RECORDS 
		// verify numeric values 
		if(!(is_numeric($_POST['stateprov_ship_ext'.$loopCt]))) {	
			$_POST['stateprov_ship_ext'.$ID]=0;
		}
		if(!(is_numeric($_POST['stateprov_tax'.$loopCt]))) {
			$_POST['stateprov_tax'.$ID]=0;
		}
		// set up tax rate depending on application setting 
		if(strtolower($_ENV["application.cw"]["taxSystem"]) == 'groups') {
			// value lt 0 is ignored by the update function 
			$taxExt= -1;
			// if not groups, use the live rate 
		} else {
			$taxExt=$_POST['stateprov_tax'.$loopCt];
		}
		// QUERY: update record (ID, name, rate, order, archived) 
		$updateRecord=CWqueryUpdateShippingExtension($_POST['stateprov_id'.$loopCt],$_POST['stateprov_ship_ext'.$loopCt],$taxExt);
		$updateCt++;
		$loopCt++;
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars('userconfirm,useralert');	
	// set up the base url 
	$_ENV["request.cwpage"]["relocateUrl"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	CWpageMessage("confirm","Changes Saved");
	// return to page as submitted, clearing form scope 
	header("Location: ".$_ENV["request.cwpage"]["relocateUrl"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));
	exit;
}
// /////// 
// END UPDATE / DELETE SHIP METHODS 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"]='Manage '.$_ENV["application.cw"]["taxSystemLabel"].' & Shipping Extensions';
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"]=$_ENV["application.cw"]["taxSystemLabel"].' & Shipping Extension Management';
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"]='Control ship or '.strtolower($_ENV["application.cw"]["taxSystemLabel"]).'cost extention by country';
// current menu marker 
// load form scripts 
$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"];
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
        <link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
			<!-- admin javascript -->
<?php
include("cwadminapp/inc/cw-inc-admin-scripts.php");
// PAGE JAVASCRIPT
?>
		<script type="text/javascript">
		// select country changes page location
		
		function groupSelect(selBox) {
			//return false;
		 	var viewID = jQuery(selBox).val();
			if (viewID >= 1) {
		 	window.location = '<?php echo $_ENV["request.cw"]["thisPage"].'?country_id=';?>' + viewID;
			}
		};
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
	echo '<h1>'. trim($_ENV["request.cwpage"]["heading1"]).'</h1>';
}
if(strlen(trim($_ENV["request.cwpage"]["heading2"]))) {				
	echo '<h2>'. trim($_ENV["request.cwpage"]["heading2"]).'</h2>';
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
// SELECT COUNTRY 
// /////// 
if($countriesQuery['totalRows'] > 1) {
?>
							<table class="CWinfoTable">
								<tr class="headerRow"><th>Select Country</th></tr>
								<tr>
									<td>
                                     <form name="selectCountryForm" action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>">
											<select name="selectCountry" id="selectCountry" onChange="groupSelect(this);">
<?php
	if($_ENV["request.cwpage"]["currentID"] == 0) {
?>
                                           		 <option value="0">Select Country</option>
<?php	
	}
	for($i=0;$i<$countriesQuery['totalRows'];$i++) {
?>
                                            	<option value="<?php echo $countriesQuery['country_id'][$i]; ?>"<?php if($_ENV["request.cwpage"]["currentID"] == $countriesQuery['country_id'][$i]) { ?> selected="selected"<?php } ?>><?php echo $countriesQuery['country_name'][$i] ; ?></option>	
<?php
	}
?>
                                        	</select>
										</form>
									</td>
								</tr>
							</table>
<?php 
}
// /////// 
// /END SELECT COUNTRY 
// /////// 
// /////// 
// EDIT RECORDS 
// /////// 
?>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>" name="recordForm" id="recordForm" method="post" class="CWobserve" enctype="multipart/form-data">
<?php
// if no records found, show message 
if(!($_ENV["request.cwpage"]["currentID"] > 0)) {
?>
        						<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>Select a country above</strong> <br>or use the <a href="countries.php">Countries</a> page to set up shipping regions.<br></p>
<?php
// if records found 
} else {
	// output records 
	// Container table 
?>
								<table class="CWinfoTable wide">
									<thead>
									<tr class="headerRow">
										<th>Active Shipping Extensions</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td>
<?php 
	// if no records found 
	if(!($stateprovQuery['totalRows'])) {
?>
            									<p>&nbsp;</p>
												<p>&nbsp;</p>
												<p>&nbsp;</p>
												<p><strong>No Shipping Extensions available for this country.</strong> <br><br></p>
<?php
	} else {
		// if records found 
?>	
												<input type="hidden" value="<?php echo $stateprovQuery['totalRows'];?>" name="stateprovCounter">
												<?php // save changes submit button ?>
												<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
												<div style="clear:right;"></div>
<?php
		// LOOP METHODS BY COUNTRY 
		$countryCt  = 0;
		$lastCountry = -1;
		for ($i=0; $i<$stateprovQuery["totalRows"]; $i++) {
			if ($lastCountry != $stateprovQuery['country_id'][$i]) {
				if ($lastCountry != -1) {
					echo "</tbody></table></td></tr></table>";
				}
				$lastCountry = $stateprovQuery['country_id'][$i];
				// Country Table 
?>
												<table class="CWinfoTable">
													<tr class="headerRow">
														<th><h3><?php echo $stateprovQuery['country_name'][0];?></h3></th>
													</tr>
                                                    
													<tr>
														<td>
															<?php // Method Records Table ?>
															<table class="CWstripe <?php if($stateprovQuery['totalRows'] > 1) {?>CWsort<?php }?>" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
																<thead>
																<tr class="headerRow sortRow">
<?php
				// state code and name 
				if($stateprovQuery['stateprov_code'][$i] != 'None') {
?>
																		<th style="text-align:center" class="stateprov_code"><a title="Sort by Code desc" href="ship-extensions.php?&sortby=stateprov_code&sortdir=desc" tabindex="1">Code</a></th>
																		<th style="text-align:center" class="stateprov_name"><a title="Sort by Name desc" href="ship-extensions.php?&sortby=stateprov_name&sortdir=desc" tabindex="1">Name</a></th>
<?php
				}
				// tax
				if (strtolower($_ENV["application.cw"]["taxCalctype"]) == "localtax") {
?>
																	<th style="text-align:center" class="stateprov_tax"><a title="Sort by Tax Ext. % asc" href="ship-extensions.php?&sortby=stateprov_tax&sortdir=asc" tabindex="1"><?php echo $_ENV["application.cw"]["taxSystemLabel"].' Ext. %';?></a></th>
<?php
				}
				// shipping 
?>
																	<th style="text-align:center" class="stateprov_ship_ext">
                                                                    <a title="Sort by Ship Ext. % asc" href="ship-extensions.php?&sortby=stateprov_ship_ext&sortdir=asc" tabindex="1">
                                                                    Ship Ext. % </a></th>
                                                                    
																</tr>
																</thead>
																<tbody>
<?php
			}
			if($stateprovQuery['stateprov_code'][$i] == "None") {
				$CurrentState = $stateprovQuery['country_name'][$i];
			} else {
				$CurrentState = $stateprovQuery['stateprov_name'][$i];
			}
?>
																<tr>
<?php
			// state code and name 
			if($stateprovQuery['stateprov_code'][$i] != "None") {
?>	
                                                                    <td><?php echo  $stateprovQuery['stateprov_code'][$i];?></td>
                                                                    <td><?php echo  $stateprovQuery['stateprov_name'][$i];?></td>
<?php
			}
			// tax 
			if (strtolower($_ENV["application.cw"]["taxCalctype"]) == "localtax") {
?>
                            <td style="text-align:center;">
<?php
				if(isset($_ENV["application.cw"]["taxSystem"]) && strtolower($_ENV["application.cw"]["taxSystem"]) == 'groups') {
?>
                                <input name="stateprov_tax<?php echo $i; ?>" type="text" readonly="readonly" value="<?php echo number_format($stateprovQuery['stateprov_tax'][$i],4);?>" onClick="alert('Using <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Groups. Set <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> System to General to enable editing rates here.')" size="6">
<?php
				} else {
?>
                                <input name="stateprov_tax<?php echo $i ?>" type="text" value="<?php echo number_format($stateprovQuery['stateprov_tax'][$i],4);?>" size="6" onKeyUp="extractNumeric(this,4,true)" onBlur="checkValue(this)">
<?php
				}
				// hidden id field 
?>
                                <input name="stateprov_id<?php echo $i; ?>" type="hidden" id="stateprov_id<?php echo $i; ?>" value="<?php echo $stateprovQuery['stateprov_id'][$i];?>">
                                <input name="recordIDlist[<?php echo $i; ?>]" type="hidden" value="<?php $stateprovQuery['stateprov_id'][$i];?>">
                            </td>
<?php
			}
			// shipping 
?>
																	<td style="text-align:center;">
																		<input name="stateprov_ship_ext<?php echo $i;?>" type="text" class="required" title="Ship Extension required (numeric)" value="<?php echo number_format($stateprovQuery['stateprov_ship_ext'][$i],4);?>" size="6" onKeyUp="extractNumeric(this,4,true)" onblur="checkValue(this)">
																	</td>
																</tr>

<?php
			// /END Country Table 
			$countryCt++;
		}
?>
                    											</tbody>
															</table>
															<?php // /END Method Records Table ?>
														</td>
													</tr></table>
<?php
		// /END Loop Methods by Country 
		// show the submit button here if we have a long list 
		if($countryCt > 1 || $stateprovQuery['totalRows'] > 9) {
?>
                						<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
							
<?php
        }
    }
    // /end if records exist 
?>
										</td>
									</tr>
									</tbody>
								</table>
<?php
    // /END Output Records 
}
// /END if records found 
?>
						</form>
<?php
// /////// 
// /END EDIT RECORDS 
// /////// 
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
