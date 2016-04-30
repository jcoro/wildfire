<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-app-onrequeststart.php
File Date: 2012-05-19
Description:
Cartweaver onRequestStart method contents
==========================================================
*/
if (isset($_SESSION["application.cwstorage"]) && isset($_SESSION["application.cwtextstorage"])) {
	$_ENV["application.cw"] = $_SESSION["application.cwstorage"];
	$_ENV["application.cwtext"] = $_SESSION["application.cwtextstorage"];
}
$_ENV["application.cw"]["appIncludePath"] = "../";
if (!isset($_ENV["request.cwapp"]["datasourcename"])) $_ENV["request.cwapp"]["datasourcename"] = "";
if (!isset($_ENV["request.cwapp"]["datasourceusername"])) $_ENV["request.cwapp"]["datasourceusername"] = "";
if (!isset($_ENV["request.cwapp"]["datasourcepassword"])) $_ENV["request.cwapp"]["datasourcepassword"] = "";
// set up DSN query variables 
if (!(isset($_ENV["application.cw"]["db_hostname"]) && $_ENV["application.cw"]["db_hostname"] == $_ENV["request.cwapp"]["db_hostname"])) {
	$_ENV["application.cw"]["db_hostname"] = $_ENV["request.cwapp"]["db_hostname"];
	$_ENV["application.cw"]["db_user"] = $_ENV["request.cwapp"]["db_user"];
	$_ENV["application.cw"]["db_password"] = $_ENV["request.cwapp"]["db_password"];
	$_ENV["application.cw"]["db_databasename"] = $_ENV["request.cwapp"]["db_databasename"];
}
// remove temporary structure 
//do not remove temp structure, used in query functions
//unset($_ENV["request.cwapp"]);
// INIT FUNCTIONS set up global variables 
if (!function_exists("CWinitRequest")) {
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	include($_ENV["application.cw"]["appIncludePath"]."func/cw-func-init.php");
	chdir($myDir);
}
// initialize application scope variables 
$initApplication = CWinitApplication();
// initialize request scope variables 
$initRequest = CWinitRequest();
// product page request variables 
$urlDetailsArr = explode("/", $_ENV["request.cwpage"]["urlDetails"]);
$urlResultsArr = explode("/", $_ENV["request.cwpage"]["urlResults"]);
$urlDownloadArr = explode("/", $_ENV["request.cwpage"]["urlDownload"]);
if ($_ENV["request.cw"]["thisPage"] == $urlDetailsArr[sizeof($urlDetailsArr)-1] || $_ENV["request.cw"]["thisPage"] == $urlResultsArr[sizeof($urlResultsArr)-1]) {
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	include($_ENV["application.cw"]["appIncludePath"]."inc/cw-inc-productrequest.php");
	chdir($myDir);
}
// html head content (not needed for product pop-up or other modular files) 
if ((stristr($_SERVER['SCRIPT_NAME'], "cw-inc-") === false) && ($_ENV["request.cw"]["thisPage"] != $urlDownloadArr[sizeof($urlDownloadArr)-1])){
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	include($_ENV["application.cw"]["appIncludePath"]."inc/cw-inc-htmlhead.php");
	chdir($myDir);
}
// session defaults 
if (!isset($_SESSION["cw"]["debug"])) $_SESSION["cw"]["debug"] = false;
// block database injection attempts by redirecting to home page 
if (strlen(trim($_ENV["application.cw"]["appSiteUrlHttp"])) && (stristr($_SERVER['QUERY_STRING'], "cast(") !== false || stristr($_SERVER['QUERY_STRING'], "declare") !== false ||stristr($_SERVER['QUERY_STRING'], "EXEC(@") !== false)) {
	header("Location: ".$_ENV["application.cw"]["appSiteUrlHttp"]);
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
?>