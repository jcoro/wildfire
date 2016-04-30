<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: ship-status.php
File Date: 2012-02-01
Description: Displays shipping status codes management table
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
// param for nav status update 
if(!isset($_ENV["request.cwpage"]["updateStatus"])) { $_ENV["request.cwpage"]["updateStatus"] = 0; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,view,userconfirm,useralert,clickadd");
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: get all credit cards 
$shipStatusQuery = CWquerySelectOrderStatus();
// /////// 
// UPDATE STATUS 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['shipstatus_id0'])) {
	$loopCt = 0;
	$updateCt = 0;
	// loop record ids, handle each one as needed 
	foreach ($_POST['recordIDlist'] as $key => $ID) {
		// UPDATE RECORDS 
		// QUERY: update ship status (ID, name, code)
		$updateRecord = CWqueryUpdateShipStatus(
						$_POST['shipstatus_id'.$loopCt],
						$_POST['shipstatus_name'.$loopCt],
						$_POST['shipstatus_sort'.$loopCt]);
		$updateCt++;
		$loopCt++;
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars("userconfirm,useralert,method");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	CWpageMessage("confirm","Changes Saved");
	// return to page as submitted, clearing form scope 
	header("Location: ".$_ENV["request.cwpage"]["relocateURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."");
	exit;
}
// /////// 
// /END UPDATE STATUS 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title
//<title>
//
$_ENV["request.cwpage"]["title"] = "Manage Order Status Codes";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Order Status Code Management";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Manage order and shipping status codes";
// current menu marker 
//$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"];
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"] = 1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"] = 1;
?>
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
						// /////// 
						// EDIT RECORDS 
						// /////// ?>
						<p>&nbsp;</p>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" name="recordForm" id="recordForm" method="post" class="CWobserve">
<?php
							// /END submit button 
							// if no records found, show message 
							if(!$shipStatusQuery['totalRows']) { ?>
                            	<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>No Ship Status Codes available.</strong> <br><br></p>
<?php
								// if records found 
							} else { ?>
                            	<input type="hidden" value="<?php echo $shipStatusQuery['totalRows']; ?>" name="userCounter">
								<?php // save changes submit button ?>
								<div style="clear:right;"></div>
								<table class="CWinfoTable wide">
									<thead>
									<tr class="headerRow">
										<th>Active Status Codes</th>
									</tr>
									</thead>
									<tr>
										<td>
											<?php // save changes / submit button ?>
											<div class="CWadminControlWrap">
												<input name="SubmitUpdate" type="submit" class="CWformButton floatLeft" id="SubmitUpdate" value="Save Changes">
												<div style="clear:both;"></div>
											</div>
											<?php // Records Table ?>
											<table class="CWstripe" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
												<thead>
												<tr class="sortRow">
													<th width="180">Status Code</th>
													<th width="75">Sort</th>
												</tr>
												</thead>
												<tbody>
<?php
												for($i=0; $i<$shipStatusQuery['totalRows']; $i++) { ?>
                                                	<tr class="dataRow">
													<?php // card name ?>
													<td>
														<?php // hidden fields used for processing updates ?>
														<p><strong><?php echo $shipStatusQuery['shipstatus_name'][$i]; ?></strong></p>
														<input name="shipstatus_name<?php echo $i; ?>" type="hidden" value="<?php echo $shipStatusQuery['shipstatus_name'][$i]; ?>">
														<input name="shipstatus_id<?php echo $i; ?>" type="hidden" value="<?php echo $shipStatusQuery['shipstatus_id'][$i]; ?>">
														<input name="recordIDlist[<?php echo $i; ?>]" type="hidden" value="<?php echo $shipStatusQuery['shipstatus_id'][$i]; ?>">
													</td>
													<?php // sort ?>
													<td>
														<input name="shipstatus_sort<?php echo $i; ?>" type="text" value="<?php echo $shipStatusQuery['shipstatus_sort'][$i]; ?>" class="sort" size="5" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)">
													</td>
												</tr>
<?php													
												}
?>                                                
												</tbody>
											</table>
										</td>
									</tr>
								</table>
<?php
								// show the submit button here if we have a long list 
								if($shipStatusQuery['totalRows'] > 10) { ?>
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
