<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: admin-users.php
File Date: 2012-04-29
Description: Displays admin user management
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
// default values for sort 
if(!isset($_GET['sortby'])) { $_GET['sortby'] = "admin_user_alias"; }
if(!isset($_GET['sortdir'])) { $_GET['sortdir'] = "asc"; }
// default form values 
if(!isset($_POST['admin_user_alias'])) { $_POST['admin_user_alias'] = ""; }
if(!isset($_POST['admin_user_email'])) { $_POST['admin_user_email'] = ""; }
if(!isset($_POST['admin_username'])) { $_POST['admin_username'] = ""; }
if(!isset($_POST['admin_password'])) { $_POST['admin_password'] = ""; }
if(!isset($_POST['admin_access_level'])) { $_POST['admin_access_level'] = ""; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("view,userconfirm,useralert,clickadd,sortby,sortdir");
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeUrl($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// QUERY: get all users (ID (0=all), username [blank], levels to omit) 
if (ListFindNoCase('developer',$_ENV["request.cwpage"]["accessLevel"])) {
	// if developer, get all 
	$usersQuery = CWquerySelectAdminUsers(0,null,null,true);
} else {
	// if merchant, don't get developers 
	$usersQuery = CWquerySelectAdminUsers(0,'','developer',true);
}
// /////// 
// ADD NEW ADMIN USER 
// /////// 
if (isset($_POST['admin_username']) && strlen(trim($_POST["admin_username"]))) {
	// QUERY: insert new user (username, password, user level, name, email)
	// this query returns the category id, or an error like '0-fieldname' 
	$newUserID = CWqueryInsertUser(
					trim($_POST["admin_username"]),
					trim($_POST["admin_password"]),
					trim($_POST["admin_access_level"]),
					trim($_POST["admin_user_alias"]),
					trim($_POST["admin_user_email"])
					);
	// if no error returned from insert query 
	if (substr($newUserID,0,2) != '0-') {
		// update complete: return to page showing message 
		CWpageMessage("confirm","User '".$_POST["admin_username"]."' Added");
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&sortby=".$_GET["sortby"]."&sortdir=".$_GET["sortdir"]."&clickadd=1");
		// if we have an insert error, show message, do not insert 
	} else {
		$dupField = explode("-", $newUserID);
		$dupField = $dupField[sizeof($dupField)-1];
		CWpageMessage("alert","Error: ".$dupField." '".$_POST["admin_username"]."' already exists");
		$_GET["clickadd"] = 1;
	}
	// /END duplicate/error check 
}
// /////// 
// /END ADD NEW ADMIN USER 
// /////// 
// /////// 
// UPDATE / DELETE ADMIN USERS 
// /////// 
// look for at least one valid ID field 
if (isset($_POST["admin_user_id0"])) {
	if (!isset($_POST["deleteRecord"])) $_POST["deleteRecord"] = array();
	$loopCt = 0;
	$updateCt = 0;
	$deleteCt = 0;
	// loop record ids, handle each one as needed 
	foreach ($_POST["recordIDlist"] as $key => $ID) {
		// DELETE RECORDS 
		// if the record ID is marked for deletion 
		if (in_array(trim($_POST["admin_user_id".$loopCt]), $_POST["deleteRecord"])) {
			// QUERY: delete record (record id) 
			$deleteRecord = CWqueryDeleteUser($ID);
			$deleteCt++;
			// if not deleting, update 
		} else {
			// UPDATE RECORDS 
			$userEmail = $_POST["admin_user_email".$loopCt];
			if (!isValidEmail($userEmail)) {
				$insertError = "Error: email address '".$userEmail."' is not valid";
			} else {
				// QUERY: update admin user (ID, username, password, user level, name, email)
				$updateRecordID = CWqueryUpdateUser(
									$_POST["admin_user_id".$loopCt],
									$_POST["admin_username".$loopCt],
									$_POST["admin_password".$loopCt],
									$_POST["admin_access_level".$loopCt],
									$_POST["admin_user_alias".$loopCt],
									$_POST["admin_user_email".$loopCt]
									);
				// if no error returned from insert query 
				if (substr($updateRecordID,0,2) == '0-') {
					$errorName = $_POST["admin_username".$loopCt];
					CWpageMessage("alert","Error: User Name '".$errorName."' already exists");
					// update complete: continue processing 
				} else {
					$updateCt++;
					$getDefaultUsers = CWquerySelectUserLogin($_SESSION["cw"]["loggedUser"],'admin',6);
					if($getDefaultUsers['totalRows'] > 0 && !($_ENV["application.cw"]["appTestModeEnabled"])) {
						$_SESSION["cw"]["userAlert"] = 'IMPORTANT: Default password (admin) still in use. Create a new user account, or <a href="admin-users.php">change the password</a>.';
					} else {
						$_SESSION["cw"]["userAlert"] = '';
					}
				}
				// end duplicate check 
			}
		}
		// /END delete vs. update 
		$loopCt++;
	}
	// if we have errors, return showing details about last errant record 
	if (isset($insertError)) {
		if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
		if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&useralert=".CWurlSafe($insertError)."&clickadd=1");
		// if no errors, return showing message 
	} else {
		// get the vars to keep by omitting the ones we don't want repeated 
		$varsToKeep = CWremoveUrlVars("userconfirm,useralert,method");
		// set up the base url 
		$_ENV["request.cwpage"]["relocateURL"] = CWserializeUrl($varsToKeep,$_ENV["request.cw"]["thisPage"]);
		// save confirmation text 
		if ($updateCt > 0) {
			CWpageMessage("confirm","Changes Saved");
		}
		// save alert text 
		if ($deleteCt > 0) {
			$_ENV["request.cwpage"]["alertText"] = "".$deleteCt." Record";
			if ($deleteCt > 1) { $_ENV["request.cwpage"]["alertText"] .= "s"; }
			$_ENV["request.cwpage"]["alertText"] .= " Deleted";
			CWpageMessage("alert",$_ENV["request.cwpage"]["alertText"]);
			if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
			if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
			$_ENV["request.cwpage"]["relocateURL"] .= '&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]);
		}
		// return to page as submitted, clearing form scope 
		header("Location: ".$_ENV["request.cwpage"]["relocateURL"]);
	}
	// /end if no errors 
}
// /////// 
// /END UPDATE / DELETE ADMIN USERS 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title <title> 
$_ENV["request.cwpage"]["title"] = "Manage Admin Users";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Admin User Management";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Manage user account details or add new admin users";
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
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"];?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php
include("cwadminapp/inc/cw-inc-admin-scripts.php");
// PAGE JAVASCRIPT ?>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			// add new show-hide
			jQuery('form#addNewForm').hide();
			jQuery('a#showAddNewFormLink').click(function(){
				jQuery(this).hide();
				jQuery('form#addNewForm').show().find('input.focusField').focus();
				return false;
			});
			// auto-click the link if adding
<?php
if (isset($_GET['clickadd'])) {
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
					<?php include("cwadminapp/inc/cw-inc-admin-nav.php"); ?>
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
if (strlen(trim($_ENV["request.cwpage"]["heading1"]))) { echo "<h1>".trim($_ENV["request.cwpage"]["heading1"])."</h1>"; }
if (strlen(trim($_ENV["request.cwpage"]["heading2"]))) { echo "<h2>".trim($_ENV["request.cwpage"]["heading2"])."</h2>"; }
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
							<strong><a class="CWbuttonLink" id="showAddNewFormLink" href="#">Add New Admin User</a></strong>
						</div>
<?php
// /////// 
// ADD NEW RECORD 
// /////// ?>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>&clickadd=1" class="CWvalidate" name="addNewForm" id="addNewForm" method="post">
							<p>&nbsp;</p>
							<h3>Add New Admin User</h3>
							<table class="CWinfoTable wide">
								<thead>
								<tr>
									<th width="165">Access Level</th>
									<th>Login Name</th>
									<th>Login Password</th>
									<th>Name / Title</th>
									<th>Email</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<?php // access level ?>
									<td>
										<div>
											<select name="admin_access_level">
												<option value="service"<?php if ($_POST["admin_access_level"] == 'service') { ?> selected="selected"<?php } ?>>Customer Service (service)</option>
												<option value="manager"<?php if ($_POST["admin_access_level"] == 'manager') { ?> selected="selected"<?php } ?>>Store Manager (manager)</option>
												<option value="merchant"<?php if ($_POST["admin_access_level"] == 'merchant') { ?> selected="selected"<?php } ?>>Site Owner (merchant)</option>
<?php
if ($_POST["admin_access_level"] == 'service') {
?>
													<option value="developer"<?php if ($_POST["admin_access_level"] == 'developer') { ?> selected="selected"<?php } ?>>Developer (developer)</option>
<?php
}
?>
											</select>
										</div>
										<br>
										<input name="SubmitAdd" type="submit" class="CWformButton" id="SubmitAdd" value="Save New Admin User">
									</td>
									<?php // username ?>
									<td>
										<input name="admin_username" type="text" class="{required:true,minlength:2}" maxlength="45" title="Username is required" value="<?php echo $_POST["admin_username"]; ?>" size="15">
									</td>
									<?php // password ?>
									<td>
										<input name="admin_password" type="text" class="{required:true,minlength:5}" maxlength="45" title="Password must be at least 6 characters" value="<?php echo $_POST["admin_password"]; ?>" size="15">
									</td>
									<?php // name / title ?>
									<td>
										<input name="admin_user_alias" type="text" class="required" maxlength="45" title="User's name or title required" value="<?php echo $_POST["admin_user_alias"]; ?>" size="15">
									</td>
									<?php // email ?>
									<td>
										<input name="admin_user_email" type="text" class="{email:true}" maxlength="75" title="Email must be a valid address (e.g. user@domain.com) or left blank" value="<?php echo $_POST["admin_user_email"]; ?>" size="15">
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
if ($usersQuery["totalRows"]) {
?>
								<div class="CWadminControlWrap">
									<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
									<div style="clear:right;"></div>
								</div>
								<?php // /END submit button ?>
								<h3>Active Admin Users</h3>
<?php
}
// if no records found, show message 
if (!$usersQuery["totalRows"]) {
?>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>No Admin User accounts available.</strong> <br><br></p>
<?php
    // if records found 
} else {
?>
								<input type="hidden" value="<?php echo $usersQuery["totalRows"]; ?>" name="userCounter">
								<?php // save changes submit button ?>
								<div style="clear:right;"></div>
								<?php // Records Table ?>
								<table class="CWstripe CWsort" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
									<thead>
									<tr class="sortRow">
										<th class="admin_username">Login Name</th>
										<th class="admin_password">Password</th>
										<th class="admin_access_level">Access Level</th>
										<th class="admin_user_alias">Name / Title</th>
										<th class="admin_user_email">Email</th>
										<th width="82" style="text-align:center;" class="noSort">
											<input type="checkbox" class="checkAll" rel="userDelete">Delete
										</th>
									</tr>
									</thead>
									<tbody>
<?php
	for ($n=0; $n<$usersQuery["totalRows"]; $n++) {
		// show delete or user level as disabled under these conditions 
		if ($usersQuery["admin_username"][$n] == $_SESSION["cw"]["loggedUser"]) {
			$currentDisabled = 1;
		} else {
			$currentDisabled = 0;
		}
?>
									<tr>
										<?php // username ?>
										<td>
											<strong><?php echo $usersQuery["admin_username"][$n]; ?></strong>
											<br>
											<span class="smallPrint"><?php echo cartweaverDate($usersQuery["admin_login_date"][$n])." ".date("H:g:i", strtotime($usersQuery["admin_login_date"][$n])).""; ?></span>
											<?php // hidden fields used for processing update/delete ?>
											<input name="admin_username<?php echo $n; ?>" type="hidden" value="<?php echo $usersQuery["admin_username"][$n]; ?>">
											<input name="admin_user_id<?php echo $n; ?>" type="hidden" value="<?php echo $usersQuery["admin_user_id"][$n]; ?>">
											<input name="recordIDlist[<?php echo $n; ?>]" type="hidden" value="<?php echo $usersQuery["admin_user_id"][$n]; ?>">
										</td>
										<?php // password ?>
										<td>
											<input name="admin_password<?php echo $n; ?>" type="text" class="{required:true,minlength:6}" maxlength="45" title="Password must be at least 6 characters" value="<?php echo $usersQuery["admin_password"][$n]; ?>" size="12">
										</td>
										<?php // access level ?>
										<td>
<?php
		if ($currentDisabled == 1) {
			echo $usersQuery["admin_access_level"][$n];
?>
												<input name="admin_access_level<?php echo $n; ?>" type="hidden" value="<?php echo $usersQuery["admin_access_level"][$n]; ?>">
<?php
		} else {
?>
												<select name="admin_access_level<?php echo $n; ?>">
													<option value="service"<?php if ($usersQuery["admin_access_level"][$n] == 'service') { ?> selected="selected"<?php } ?>>Customer Service (service)</option>
													<option value="manager"<?php if ($usersQuery["admin_access_level"][$n] == 'manager') { ?> selected="selected"<?php } ?>>Store Manager (manager)</option>
													<option value="merchant"<?php if ($usersQuery["admin_access_level"][$n] == 'merchant') { ?> selected="selected"<?php } ?>>Site Owner (merchant)</option>
<?php
			if ($usersQuery["admin_access_level"][$n] == 'developer') {
?>
														<option value="developer"<?php if ($usersQuery["admin_access_level"][$n] == 'developer') { ?> selected="selected"<?php } ?>>Developer (developer)</option>
<?php
			}
?>
												</select>
<?php
		}
?>
										</td>
										<?php // name / title ?>
										<td>
											<input name="admin_user_alias<?php echo $n; ?>" type="text" class="required" maxlength="45" title="User's name or title required" value="<?php echo $usersQuery["admin_user_alias"][$n]; ?>" size="12">
										</td>
										<?php // email ?>
										<td>
											<input name="admin_user_email<?php echo $n; ?>" type="text" class="{email:true}" maxlength="75" title="Email must be a valid address (e.g. user@domain.com) or left blank" value="<?php echo $usersQuery["admin_user_email"][$n]; ?>" size="12">
										</td>
										<?php // delete ?>
										<td style="text-align:center">
											<input type="checkbox" value="<?php echo $usersQuery["admin_user_id"][$n]; ?>" class="formCheckbox userDelete" name="deleteRecord[<?php echo $n; ?>]"<?php if ($currentDisabled) { ?> disabled="disabled"<?php } ?>>
										</td>
									</tr>
<?php
	}
?>
									</tbody>
								</table>
<?php
    // show the submit button here if we have a long list 
    if ($usersQuery["totalRows"] > 10) {
?>
									<input name="SubmitUpdate" type="submit" class="CWformButton" id="SubmitUpdate" value="Save Changes">
<?php
	}
	// explain delete restriction ?>
								<span class="smallPrint" style="float:right;">
									Note: You cannot delete your own account
								</span>
<?php
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