<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: application.php
File Date: 2012-02-01
Description:
controls application variables and global functions
Note: Includes Global Settings via the cw-config.php File
==========================================================
*/
// // ---------- ERROR ---------- // 
function onError($errno, $errstr, $errfile, $errline) {
	// call error page for errors 
	if ((!isset($_SESSION["cw"]["errorsOff"]) || !$_SESSION["cw"]["errorsOff"]) && strpos(strtolower($_SERVER["SCRIPT_NAME"]), "error-exception.php") === false) {
		$errpost = "";
		foreach ($_POST as $key => $value) {
			$errpost .= "&".$key."=".$value;
		}
		header("Location: error-exception.php?errno=".$errno."&errline=".$errline."&errfile=".urlencode($errfile)."&errstr=".urlencode($errstr)."&errpage=".urlencode($_SERVER["SCRIPT_NAME"])."&errqs=".urlencode($_SERVER["QUERY_STRING"])."&errpost=".$errpost."&errget=".$errget);
	}
}
ob_start("CWinsertHeadScripts");
// //////////// 
// Edit the CWconfig file to change site settings 
// //////////// 
if (!isset($_ENV["application.cw"])) $_ENV["application.cw"] = array();
if (!isset($_ENV["application.cwtext"])) $_ENV["application.cwtext"] = array();
if (!isset($_ENV["request.cw"])) $_ENV["request.cw"] = array();
if (!isset($_ENV["request.cwapp"])) $_ENV["request.cwapp"] = array();
if (!isset($_ENV["request.cwpage"])) $_ENV["request.cwpage"] = array();
if (!isset($_ENV["variables"])) $_ENV["request.cwpage"] = array();
require_once("cwadminapp/func/cw-func-adminqueries.php");//included for isValidEmail function
require_once("../cwconfig/cw-config.php");
require_once("cwadminapp/inc/cw-inc-admin-sanitize.php");//clean form and url vars 
require_once("../cwapp/appcfc/cw-app-cfcstart.php");
//added to keep the "application.cw[text]" variables in session, like the application set in CF
// global application settings 
if (isset($_SESSION["application.cwstorage"]) && isset($_SESSION["application.cwtextstorage"])) {
	$_ENV["application.cw"] = $_SESSION["application.cwstorage"];
	$_ENV["application.cwtext"] = $_SESSION["application.cwtextstorage"];
}
if (!isset($_SESSION["cw"])) $_SESSION["cw"] = array();
if (!isset($_SESSION["cwclient"])) $_SESSION["cwclient"] = array();
// //////////// 
// //////////// 
// //////////// 
// //////////// 
// No Need to Edit Below This Line 
// //////////// 
// //////////// 
// //////////// 
// //////////// 
// // ---------- REQUEST START ---------- // 
function onRequestStart($useralert=NULL, $userconfirm=NULL) {
	if (!isset($_ENV["request.cwpage"]["useralert"])) $_ENV["request.cwpage"]["useralert"] = "";
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["application.cw"]["db_hostname"])) $_ENV["request.cwapp"]["db_hostname"] = "";
	if (!isset($_ENV["application.cw"]["db_databasename"])) $_ENV["request.cwapp"]["db_databasename"] = "";
	if (!isset($_ENV["application.cw"]["db_username"])) $_ENV["request.cwapp"]["db_username"] = "";
	if (!isset($_ENV["application.cw"]["db_password"])) $_ENV["request.cwapp"]["db_password"] = "";
	// global variable for path to cwapp/ directory - end with trailing slash
	//(front end mail and init functions in /cwapp/func/ are used by admin)
	//
	if(!isset($_ENV["cwapppath"])) { $_ENV["cwapppath"] = "../cwapp/";}
	// verify DSN 
	if(!isset($_ENV["application.cw"]["dbok"]) || !$_ENV["application.cw"]["dbok"]) {
		try {
			$checkDB ="SELECT config_value FROM cw_config_items WHERE config_variable = 'appVersionNumber'";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cwadminapp/func/cw-func-admin.php");
				chdir($myDir);
			}
			$result = mysql_query($checkDB,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage("Datasource Unavailable");
			if ($result !== false) {
				if(mysql_num_rows($result)) { $_ENV["application.cw"]["dbok"] = true; } else { $_ENV["request.cwpage"]["logonerror"] = 'Datasource Unavailable'; }
			} else { $_ENV["request.cwpage"]["logonerror"] = 'Datasource Unavailable'; }
		}
		catch(Exception $e) {
			$_ENV["request.cwpage"]["logonerror"] = 'Datasource Unavailable' ; 	
		}
	}
	if ($_ENV["application.cw"]["dbok"] && !$_ENV["request.cwapp"]["db_link"]) {
		unset($_ENV["application.cw"]["dbok"]);
	}
	// if the dsn is ok 
	if(isset($_ENV["application.cw"]["dbok"]) && $_ENV["application.cw"]["dbok"]) {
		// global settings 
		require_once($_ENV["cwapppath"]."func/cw-func-init.php");
		//added to keep the "application.cw[text]" variables in session, like the application set in CF
		if ((CWcheckApplicationRefresh()) || (!isset($_SESSION["application.cwstorage"]) || !isset($_SESSION["application.cwtextstorage"]) || $_SESSION["application.cwstorage"]["companyName"] == "") ||
			(!isset($_ENV["application.cw"]["companyName"]) || (
				isset($_GET["resetapplication"]) && $_GET["resetapplication"] == $_ENV["application.cw"]["debugPassword"] &&
				isset($_SESSION["cw"]["loggedIn"]) && $_SESSION["cw"]["loggedIn"] == '1' &&
				isset($_SESSION["cw"]["accessLevel"]) && (strpos($_SESSION["cw"]["accessLevel"], 'developer') !== false || strpos($_SESSION["cw"]["accessLevel"], 'merchant') !== false)))) {
			// initialize application scope variables 
			$initApplication = CWinitApplication();
		}
		// initialize request scope variables 
		$initRequest = CWinitRequest();
		// LOG IN 
		// Verify the user is logged in. 
		// these pages are not redirected (list)
		$loginExceptions = 'product-image-upload.php,product-image-select.php';
		if($_ENV["request.cw"]["thisPage"] != "index.php" && (!isset($_SESSION["cw"]["loggedIn"]) || $_SESSION["cw"]["loggedIn"] == 0)) {
			$strURL = $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
			if(strstr($_SERVER['SCRIPT_NAME'], "helpfiles") !== false) {
				header("Location: ../index.php?accessdenied=".urlencode($strURL));
			} else {
				// if not in our excepted file list 
				$login = explode('/',$_SERVER['SCRIPT_NAME']);
				$loginlast = $login[count($login) - 1];
				if(!ListFindNoCase($loginExceptions,$loginlast)) {
					// remove trigger variables for returning after login  
					$relocUrl = str_replace('logout=1','',$strURL);
					$relocUrl = str_replace('&timeout=1','',$relocUrl);
					// set up url to log out to 
					$logoutUrl = 'index.php?';
					// if redirecting due to timeout, add trigger to login page querystring 
					$logoutUrl = $logoutUrl.'accessdenied='.urlencode($relocUrl);
					if(isset($_GET['timeout']) && $_GET['timeout'] == 1) {
						$logoutUrl = $logoutUrl.'&timeout=1';
					}
					header("Location: ".$logoutUrl);
					exit;
				} else {
					// if it is an excepted page, show message, stop processing 
					echo "<span style=\"font-size:12px; font-family:Arial, sans-serif;color:#990000;\">Log In to Continue</span>";
					exit;
				}
			}
			if (file_exists("db-sql.php")) {
				unlink("db-sql.sql");
			}
			if (file_exists("db-setup.php")) {
				unlink("db-setup.php");
			}
		}
		// session defaults 
		if(!isset($_SESSION["cw"]["debug"])) { $_SESSION["cw"]["debug"] = false; }
		// block database injection attempts by redirecting to home page 
		if((strlen(trim($_ENV["application.cw"]["appSiteUrlHttp"])) && (stristr($_SERVER['QUERY_STRING'],'cast(') || stristr($_SERVER['QUERY_STRING'],'declare') || stristr($_SERVER['QUERY_STRING'],'EXEC(@')))) {
			header("Location: ".$_ENV["application.cw"]["appSiteUrlHttp"]);
			exit;
		}
		// error handling 
		if($_ENV["application.cw"]["adminErrorHandling"]) {
			set_error_handler("onError", E_ALL);
		}
	}
	else {
		// if db is not ok, send to home page 
		if(strstr($_SERVER['SCRIPT_NAME'], 'index.php') === false) {
			header("Location: index.php");
			exit;
		}
		else {
			//redirect to db-setup.php
			if (file_exists("db-setup.php")) {
				header("Location: db-setup.php");
				exit;
			}
		}
	}
	// / end db ok check 
	// log out 
	if((isset($_GET['logout'])) && $_GET['logout'] != 0) {
		if(!isset($_GET['accessdenied'])) { $_GET['accessdenied'] = ""; }	
		if(!isset($_GET['pagenotfound'])) { $_GET['pagenotfound'] = ""; }
		// clear the session 
		foreach ($_SESSION as $key => $value) {
			if(!(strtolower($key) == 'sessionid' || strtolower($key) == 'cfid')) {
				// leave session id alone to avoid errors 
				unset($_SESSION[$key]);
			}
		}
		// redirect to login page 
		header("Location: index.php?accessdenied=".$_GET['accessdenied']."&pagenotfound=".$_GET['pagenotfound']."");
		exit;
	}
	//fix for date masks
	if (isset($_ENV["application.cw"]["globalDateMask"]) && (
		(strstr($_ENV["application.cw"]["globalDateMask"], "yy") !== false) ||
		(strstr($_ENV["application.cw"]["globalDateMask"], "mm") !== false) ||
		(strstr($_ENV["application.cw"]["globalDateMask"], "dd") !== false))) {
		if (strstr($_ENV["application.cw"]["globalDateMask"], "dd") !== false) {
			$_ENV["application.cw"]["globalDateMask"] = str_replace("dd", "d", $_ENV["application.cw"]["globalDateMask"]);
		} else {
			$_ENV["application.cw"]["globalDateMask"] = str_replace("d", "j", $_ENV["application.cw"]["globalDateMask"]);
		}
		if (strstr($_ENV["application.cw"]["globalDateMask"], "mm") !== false) {
			$_ENV["application.cw"]["globalDateMask"] = str_replace("mm", "m", $_ENV["application.cw"]["globalDateMask"]);
		} else {
			$_ENV["application.cw"]["globalDateMask"] = str_replace("m", "n", $_ENV["application.cw"]["globalDateMask"]);
		}
		if (strstr($_ENV["application.cw"]["globalDateMask"], "yyyy") !== false) {
			$_ENV["application.cw"]["globalDateMask"] = str_replace("yyyy", "Y", $_ENV["application.cw"]["globalDateMask"]);
		} else {
			$_ENV["application.cw"]["globalDateMask"] = str_replace("yy", "y", $_ENV["application.cw"]["globalDateMask"]);
		}
	}
	if( !isset($_ENV["application.cw"]["globalTimeMask"])) 
	    $_ENV["application.cw"]["globalTimeMask"] = " H:i:s T";
	
	$_SESSION["application.cwstorage"] = $_ENV["application.cw"];
	$_SESSION["application.cwtextstorage"] = $_ENV["application.cwtext"];
}
// /END REQUEST START 

// CALLBACK Function for output buffer
function CWinsertHeadScripts($buffer) {
	$insertStr = "";
	if (isset($_ENV["CWHEADSCRIPTS"]) && sizeof($_ENV["CWHEADSCRIPTS"])) {
		$insertStr = implode("
", $_ENV["CWHEADSCRIPTS"]);
	}
	if (strlen(trim($insertStr))) {
		$insertIndex = stripos($buffer, "</head");
		if ($insertIndex === false) {
			$buffer = $insertStr . $buffer;
		}
		else {
			$buffer = substr($buffer, 0, $insertIndex) . $insertStr . substr($buffer, $insertIndex);
		}
	}
	return $buffer;
}

function CWinsertHead($string) {
	if (!isset($_ENV["CWHEADSCRIPTS"])) $_ENV["CWHEADSCRIPTS"] = array();
	$_ENV["CWHEADSCRIPTS"][] = $string;
}

onRequestStart();
?>