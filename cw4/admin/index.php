<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: admin/index.php
File Date: 2012-05-01
Description:
Login page for store admin
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
if(!isset($_ENV["application.cw"]["companyName"])) { $_ENV["application.cw"]["companyName"] = 'Cartweaver';}
if(!isset($_ENV["application.cw"]["adminThemeDirectory"])) { $_ENV["application.cw"]["adminThemeDirectory"] = 'default';}
// if logging on 
if(isset($_POST['adminUsername'])) { 
	// QUERY: log on, look up the submitted username and password 
	$getLogOn = CWquerySelectUserLogin($_POST['adminUsername'],$_POST['adminPassword']);
	// Record found, login  
	if($getLogOn['totalRows']) {
		if(!isset($LastLogin)) { $LastLogin = $getLogOn['admin_login_date'][0]; }
		if(!strlen(trim($LastLogin)) || !($LastLogin)) {
			$LastLogin = strtotime("2000-01-01 01:00:00");
		} else {
			$LastLogin = strtotime($LastLogin);
		}
		// Set the session vars 
		$_SESSION["cw"]["loggedIn"] = 1;
		// This session store the username 
		$_SESSION["cw"]["loggedUser"] = $getLogOn['admin_username'][0];
		$_SESSION["cw"]["lastLogin"] = $LastLogin;
		$_SESSION["cw"]["accessLevel"] = $getLogOn['admin_access_level'][0];
		// Store username inside a cookie if remember me was checked 
		if(isset($_POST['remember_me'])) {
			setcookie('CWAdminUsername',$_POST['adminUsername'],time()+(60*60*24*365));
		} else {
			// Else, clean any existing cookie 
			setcookie('CWAdminUsername',"",time());
		}
		// QUERY: update user logon date (user id, date) 
		$updateUser = CWqueryUpdateUserDate($_SESSION["cw"]["loggedUser"],$LastLogin);
		// QUERY: check for default 'admin' password and any of the default account names, show warning if exists 
		if(isset($_SESSION["cw"]["userAlert"])) {
			unset($_SESSION["cw"]["userAlert"]);
		}
		$getDefaultUsers = CWquerySelectUserLogin($_SESSION["cw"]["loggedUser"],'admin',6);
		if($getDefaultUsers['totalRows'] > 0 && !($_ENV["application.cw"]["appTestModeEnabled"])) {
			$_SESSION["cw"]["userAlert"] = 'IMPORTANT: Default password (admin) still in use. Create a new user account, or <a href="admin-users.php">change the password</a>.';
		}
		// ALERT IF NO AUTH METHODS EXIST 
		if (empty($_ENV["application.cw"]["authMethods"])
		 && !(isset($_ENV["application.cw"]["appTestModeEnabled"]) && $_ENV["application.cw"]["appTestModeEnabled"])) {
			 $_SESSION["cw"]["userAlert"] .= 'WARNING: Checkout Process Offline - payment transaction options for this store are currently unavailable.';
		}
		// QUERY: set installation date if not defined 
		if (!isset($_ENV["application.cw"]["appInstallationDate"]) || !$_ENV["application.cw"]["appInstallationDate"] || cartweaverStrtotime($_ENV["application.cw"]["appInstallationDate"]) === false) {
			$installDate = CWsetInstallationDate();
		}
		// REDIRECT AFTER LOGIN 
		// If the user requested a specific page, redirect there 
		if((isset($_POST['redirect_to'])) && strlen(trim($_POST['redirect_to']))) {
			//echo $_POST['redirect_to'];exit;
			header('Location: '.urldecode($_POST['redirect_to']));
			exit;
			// if no specific page requested, use the defaults 
		} else {
			// if store defaults still in place, developer goes to company settings by default  
			if(($_SESSION["cw"]["accessLevel"]) == 'developer'  && strstr($_ENV["application.cw"]["companyEmail"],'@cartweaver') !== false) {
				header("Location: config-settings.php?group_id=3");
				exit;
			} else {
				header("Location: admin-home.php");
				exit;
			}
		}
	} else {
		// Login failed 
		// Display an error message 
		$_ENV["request.cwpage"]["logonerror"] = "Log on unsuccessful. No match was found. Please try again or contact administrator" ;
	}
}
// set blank cookie value as a default 	
if(!isset($_COOKIE['CWAdminUsername'])) {
	$_COOKIE['CWAdminUsername'] = "";	
}
// REDIRECT FROM OTHER PAGES 	
if((isset($_GET['pagenotfound'])) && strlen(trim($_GET['pagenotfound']))) {
	$_ENV["request.cwpage"]["logonerror"] = "Page not found:".trim(urldecode($_POST['pageNotFound']));
}
else if((isset($_GET['timeout'])) && strlen(trim($_GET['timeout']))) {
	$_ENV["request.cwpage"]["logonerror"] = "Session timed out. Log in again to continue." ;
}
else if (isset($_GET["dbsetup"]) && $_GET["dbsetup"] == "ok") {
	$_ENV["request.cwpage"]["logonerror"] = "Database setup complete.";
}
// START OUTPUT ?>		
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		
<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : Log In</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
	</head>
	<?php // focus cursor on username or password field depending on saved value ?>
	<body onLoad="document.login.<?php if(isset($_COOKIE['CWAdminUsername']) && $_COOKIE['CWAdminUsername'] == "") { ?>
   adminUsername<?php } else {?>adminPassword<?php } ?>.focus();">
		<div id="CWadminWrapper">
			<div id="CWadminLoginWrap">
            	<?php 	if(isset($_ENV["request.cwpage"]["logonerror"])) { ?>
							<div class="alert"><?php echo $_ENV["request.cwpage"]["logonerror"];?></div>
         		<?php	}
				
		 				if(isset($_ENV["application.cw"]["dbok"])) {
					?>
							 <form action="index.php" method="post" name="login" id="login" enctype="multipart/form-data">
							<h2>
							<?php echo $_ENV["application.cw"]["companyName"]; ?>: Log In</h2>
							<table>
								<tr>
									<th class="rightText">Username</th>
									<td><input name="adminUsername" type="text" id="adminUsername" value="<?php echo $_COOKIE['CWAdminUsername'];  ?>" class="focusField" tabindex="1"> </td>
								</tr>
								<tr>
									<th class="rightText">Password</th>
									<td>
										<input name="adminPassword" type="password" id="adminPassword" tabindex="2">
									</td>
								</tr>
								<tr class="dataRow">
									<td colspan="2" class="centerText">
										<div id="siteReturnLink">
											<a href="<?php echo $_ENV["application.cw"]["appSiteUrlHttp"]; ?>" tabindex="10">Return to Site</a>
										</div>
										<input name="remember_me" type="checkbox" class="formCheckbox" tabindex="3" value="1"<?php if($_COOKIE['CWAdminUsername'] != "") { ?> checked<?php } ?>>
										Remember me
									</td>
								</tr>
							</table>
							<input name="Submit" type="submit" class="CWformButton" value="Log In" tabindex="4">
                            <?php
							// Store the path to the requested page inside an hidden field 
							if((isset($_GET['accessdenied'])) && $_GET['accessdenied'] && strstr($_GET['accessdenied'], 'logout') === false) { 
									?>
                                    	<input type="hidden" name="redirect_to" value="<?php echo urlencode($_GET['accessdenied'])?>">
										
							<?php 	} ?>
						</form>
			<?php 		} ?>
            
			</div>
		</div>
	</body>
</html>