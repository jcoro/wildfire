<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: credit-cards.php
File Date: 2012-02-01
Description: Displays credit card management table
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
// default values for sort 
if(!isset($_GET['sortby'] )) { $_GET['sortby'] = "creditcard_name"; }
if(!isset($_GET['sortdir'])) { $_GET['sortdir'] = "asc"; }
// default form values 
if(!isset($_POST['creditcard_name'] )) { $_POST['creditcard_name'] = ""; }
if(!isset($_POST['creditcard_code'])) { $_POST['creditcard_code'] = ""; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,view,userconfirm,useralert,clickadd");
// create the base url for sorting out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: get all credit cards 
$creditcardsQuery = CWquerySelectCreditCards(null,true);
// /////// 
// ADD NEW CREDIT CARD 
// /////// 
if(isset($_POST['SubmitAdd'])) {
	if((isset($_POST['creditcard_name'])) && strlen(trim($_POST['creditcard_name']))) {
		// QUERY: insert new credit card (name, code)
		// this query returns the new id, or a 0- error 
		$newCreditCardID = CWqueryInsertCreditCard(
							trim($_POST['creditcard_name']),
							trim($_POST['creditcard_code']));
		// if no error returned from insert query 
		if(substr($newCreditCardID,0,2) != '0-') {
			// update complete: return to page showing message 
			CWpageMessage("confirm","Credit Card ".$_POST['creditcard_name']." Added");
			header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&sortby=".$_GET['sortby']."&sortdir=".$_GET['sortdir']."&clickadd=1");
			// if we have an insert error, show message, do not insert 
		} else {
			$newcreditcardid = explode('-',$newCreditCardID);
			$newcreditcardid_last = $newcreditcardid[count($$newcreditcardid) - 1];
			$errorMsg = $newcreditcardid_last;
			$_GET['clickadd'] = 1;
		}
		// /END duplicate/error check 
	}
}
// /////// 
// /END ADD NEW CREDIT CARD 
// /////// 
// /////// 
// UPDATE / DELETE CREDIT CARDS 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['SubmitUpdate'])) {
	$loopCt = 0;
	$updateCt = 0;
	$deleteCt = 0;
	// loop record ids, handle each one as needed 
	foreach ($_POST["recordIDlist"] as $key => $id) {
		// DELETE RECORDS 
		// if the record ID is marked for deletion 
		if(isset($_POST['deleteRecord'][$key])) {
			// QUERY: delete record (record id) 
			$deleteRecord = CWqueryDeleteCreditCard($_POST['deleteRecord'][$key]);
			$deleteCt++;
		} else {
			// if not deleting, update 
			// UPDATE RECORDS 
			// QUERY: update credit card (ID, name, code)
			$updateRecordID = CWqueryUpdateCreditCard(
							$_POST['creditcard_ID'.$loopCt],
							$_POST['creditcard_name'.$loopCt],
							$_POST['creditcard_code'.$loopCt]);
			// if no error returned from insert query 
			if((substr($updateRecordID,0,2)) == '0-') {
				$updtrecordid = explode('-',$updateRecordID);
				$errorMsg = $updtrecordid[count($$updtrecordid) - 1];
				// update complete: continue processing 
			} else {
				$updateCt++;
			}
			// end duplicate check 
		}
		$loopCt++;
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars("userconfirm,useralert,method");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	CWpageMessage("confirm","Changes Saved");
	// save alert text 
	$_ENV["request.cwpage"]["userAlertText"] = '';
	if($deleteCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"].= $deleteCt.' Record';
		if($deleteCt > 1) {
			$_ENV["request.cwpage"]["userAlertText"].= 's';
		}
		$_ENV["request.cwpage"]["userAlertText"].= ' Deleted';
	}
	echo $_ENV["request.cwpage"]["userAlertText"];
	CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
	// return to page as submitted, clearing form scope 
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_SERVER['SCRIPT_NAME']."?&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."");
	exit;
}
// /////// 
// /END UPDATE / DELETE CREDIT CARDS 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title <title> 
$_ENV["request.cwpage"]["title"] = "Manage Credit Cards";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Credit Card Management";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Manage accepted credit cards and codes";
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
                        <?php  // link for add-new form ?>
						<div class="CWadminControlWrap">
							<strong><a class="CWbuttonLink" id="showAddNewFormLink" href="#">Add New Credit Card</a></strong>
						</div>
<?php
// /////// 
// ADD NEW RECORD 
// /////// ?>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]."&clickadd=1"; ?>" class="CWvalidate" name="addNewForm" id="addNewForm" method="post">
							<p>&nbsp;</p>
							<h3>Add New Credit Card</h3>
							<table class="CWinfoTable wide">
								<thead>
								<tr>
									<th width="165">Card Name</th>
									<th>Card Code</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<?php // card name ?>
									<td>
										<div>
											<input name="creditcard_name" tabindex="1" type="text" class="{required:true,minlength:2}" title="Credit Card Name is required" value="<?php echo $_POST['creditcard_name']; ?>" size="21">
										</div>
										<br>
										<input name="SubmitAdd" type="submit" tabindex="3" class="CWformButton" id="SubmitAdd" value="Save New Credit Card">
									</td>
									<td>
										<input name="creditcard_code" type="text" tabindex="2" class="{required:true,minlength:2}" title="Credit Card Code is required" value="<?php echo $_POST['creditcard_code']; ?>" size="15">
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
						<p>&nbsp;</p>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" name="recordForm" id="recordForm" method="post" class="CWobserve">
<?php
// save changes / submit button 
if($creditcardsQuery['totalRows']) {
?>
                            	<div class="CWadminControlWrap">
									<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
									<div style="clear:right;"></div>
								</div>
								<h3>Accepted Credit Cards</h3>
<?php								
	// /END submit button 
}
// if no records found, show message 
if(!$creditcardsQuery['totalRows']) {
?>
                            	<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>No Credit Cards available.</strong> <br><br></p>
<?php							
} else {
	// if records found ?>
                            	<input type="hidden" value="<?php echo $creditcardsQuery['totalRows']; ?>" name="userCounter">
<?php
								// save changes submit button ?>
								<div style="clear:right;"></div>
								<?php // Records Table ?>
								<table class="CWstripe CWsort wide" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
									<thead>
									<tr class="sortRow">
										<th class="creditcard_name">Card Name</th>
										<th class="creditcard_code">Card Code</th>
										<th width="82" style="text-align:center;" class="noSort">
											<input type="checkbox" class="checkAll" rel="userDelete">Delete
										</th>
									</tr>
									</thead>
									<tbody>
<?php
	for($i=0; $i<$creditcardsQuery['totalRows']; $i++) { ?>
                                    	<tr>
                                        <?php // card name ?>
										<td>
											<input name="creditcard_name<?php echo $i; ?>" type="text" class="required" title="Credit card name required" value="<?php echo $creditcardsQuery['creditcard_name'][$i]; ?>" size="25">
<?php // hidden fields used for processing update/delete ?>
											<input name="creditcard_ID<?php echo $i; ?>" type="hidden" value="<?php echo $creditcardsQuery['creditcard_id'][$i];?>">
											<input name="recordIDlist[<?php echo $i; ?>]" type="hidden" value="<?php echo $creditcardsQuery['creditcard_id'][$i]; ?>">
										</td>
										<?php // code ?>
										<td>
											<input name="creditcard_code<?php echo $i;?>" type="text" class="required" title="Credit card code required" value="<?php echo $creditcardsQuery['creditcard_code'][$i]; ?>" size="12">
										</td>
                                        <?php // delete ?>
										<td style="text-align:center">
											<input type="checkbox" value="<?php echo $creditcardsQuery['creditcard_id'][$i]; ?>" class="formCheckbox userDelete" name="deleteRecord[<?php echo $i;?>]">
										</td>
									</tr>
<?php									
	}
?>                                    
									</tbody>
								</table>
<?php
	// show the submit button here if we have a long list 
	if($creditcardsQuery['totalRows'] > 10) {
?>
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
