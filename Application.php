<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: Application.php
File Date: 2012-02-01
Description: controls application variables and global functions
Note: includes Global Settings via the cw-config.php File
==========================================================
*/
// // ---------- ERROR ---------- // 
function onError($errno, $errstr, $errfile, $errline) {
	// call error page for errors 
	if ((!isset($_SESSION["cw"]["errorsOff"]) || !$_SESSION["cw"]["errorsOff"]) && strpos(strtolower($_SERVER["SCRIPT_NAME"]), "cw-error-exception.php") === false) {
		$errpost = "";
		foreach ($_POST as $key => $value) {
			if (is_array($value)) {
				$value = implode(", ", $value);
			}
			$errpost .= "&".$key."=".$value;
		}
		$errget = "";
		foreach ($_GET as $key => $value) {
			if (is_array($value)) {
				$value = implode(", ", $value);
			}
			$errget .= "&".$key."=".$value;
		}
		header("Location: cw-error-exception.php?errno=".$errno."&errline=".$errline."&errfile=".urlencode($errfile)."&errstr=".urlencode($errstr)."&errpage=".urlencode($_SERVER["SCRIPT_NAME"])."&errqs=".urlencode($_SERVER["QUERY_STRING"])."&errpost=".$errpost."&errget=".$errget);
	}
}
// /END ERROR 
ob_start("CWinsertHeadScripts");
if (!isset($_ENV["application.cw"])) $_ENV["application.cw"] = array();
if (!isset($_ENV["application.cwtext"])) $_ENV["application.cwtext"] = array();
if (!isset($_ENV["request.cw"])) $_ENV["request.cw"] = array();
if (!isset($_ENV["request.cwapp"])) $_ENV["request.cwapp"] = array();
if (!isset($_ENV["request.cwpage"])) $_ENV["request.cwpage"] = array();
if (!isset($_ENV["variables"])) $_ENV["request.cwpage"] = array();
$myDir = getcwd();
chdir(dirname(__FILE__));
// //////////// 
// Include Global Values: open the cw-config file to change site settings 
// //////////// 
include("cw4/cwconfig/cw-config.php");
// //////////// 
// No Need to Edit Below This Line 
// //////////// 
// global application settings 
include("cw4/cwapp/appcfc/cw-app-cfcstart.php");
chdir($myDir);
//added to keep the "application.cw[text]" variables in session, like the application set in CF
// global application settings 
if (isset($_SESSION["application.cwstorage"]) && isset($_SESSION["application.cwtextstorage"])) {
	$_ENV["application.cw"] = $_SESSION["application.cwstorage"];
	$_ENV["application.cwtext"] = $_SESSION["application.cwtextstorage"];
}
if (!isset($_SESSION["cw"])) $_SESSION["cw"] = array();
if (!isset($_SESSION["cwclient"])) $_SESSION["cwclient"] = array();
// // ---------- REQUEST START ---------- // 
function onRequestStart() {
	// cartweaver inititalization 
	include("cw4/cwapp/appcfc/cw-app-onrequeststart.php");
	// error handling 
	if ($_ENV["application.cw"]["debugHandleErrors"]) {
		set_error_handler("onError", E_ALL);
	}
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