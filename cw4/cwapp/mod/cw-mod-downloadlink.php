<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-downloadlink.php
File Date: 2012-05-12
Description:
Displays links to download a file, with optional customer-related info
==========================================================
*/

// sku id is required 
if (!isset($module_settings["sku_id"])) $module_settings["sku_id"] = 0;
// other display attributes are optional 
if (!isset($module_settings["download_url"])) $module_settings["download_url"] = $_ENV["application.cw"]["appPageDownload"];
if (!isset($module_settings["download_text"])) $module_settings["download_text"] = "";
if (!isset($module_settings["show_file_name"])) $module_settings["show_file_name"] = true;
if (!isset($module_settings["show_file_size"])) $module_settings["show_file_size"] = true;
if (!isset($module_settings["show_file_version"])) $module_settings["show_file_version"] = true;
if (!isset($module_settings["show_remaining"])) $module_settings["show_remaining"] = true;
// customer specific data 
if (!isset($module_settings["customer_id"])) $module_settings["customer_id"] = 0;
if (!isset($module_settings["show_last_version"])) $module_settings["show_last_version"] = true;
if (!isset($module_settings["show_last_date"])) $module_settings["show_last_date"] = true;
$myDir = getcwd();
chdir(dirname(__FILE__));
// global functions 
require_once("../inc/cw-inc-functions.php");
// clean up form and url variables 
require_once("../inc/cw-inc-sanitize.php");
// create URL (if blank, file does not exist) 
$dlUrl = CWcreateDownloadURL($module_settings["sku_id"]);
if (strlen(trim($dlUrl))) {
	$dlOk = true;
} else {
	$dlOk = false;
}
// if file exists, continue processing 
if ($dlOk) {
	if ($module_settings["show_file_size"]) {
		// download details - server path and saved dlName 
		$dlPath = CWgetDownloadPath(null, null, $module_settings["sku_id"]);
		$dlFolderChar = ((strpos($dlPath, "\\") !== false) ? "\\" : "/" );
		$dlpArr = explode($dlFolderChar, $dlPath);
		$dlName = $dlpArr[sizeof($dlpArr)-1];
		$dlDir = substr($dlPath,0,strlen($dlPath)-strlen($dlName));
		$dlSize = 0;
		if (file_exists($dlPath)) {
			$dlSize = round(filesize($dlPath)/1000);
		}
	}
	// set up link text 
	$dlStr = trim($module_settings["download_text"]);
	$dlFn = CWqueryGetSkuFile($module_settings["sku_id"]);
	// get version 
	if ($module_settings["show_file_version"]) {
		$dlVersion = CWgetDownloadVersion($module_settings["sku_id"]);
	}
	// get customer download info 
	if ($module_settings["show_last_version"] || $module_settings["show_last_date"]) {
		// function returns a struct with date and version 
		$dlData = CWgetCustomerDownloadData($module_settings["sku_id"],$module_settings["customer_id"]);
	}
}

// if file exists, show link 
if ($dlOk) {
	if (strlen(trim($dlUrl))) {
		// if showing filename 
		if ($module_settings["show_file_name"]) {
?>
<?php echo trim($module_settings["download_text"]); ?> <a href="<?php echo $dlUrl; ?>"><?php echo $dlFn; ?></a>
<?php
		// standard link 
		} else {
?>
<a href="<?php echo $dlUrl; ?>"><?php echo trim($module_settings["download_text"]); ?><?php if ($module_settings["show_file_size"] && $dlSize > 0) { ?> (<?php echo trim(number_format($dlSize)); ?>kb)<?php } ?></a>
<?php
		}
		
		if ($module_settings["show_file_version"] && strlen($dlVersion)) {
?><br>Current Version: <?php echo $dlVersion; ?><?php
		}
		// number remaining 
		if ($module_settings["customer_id"] != 0 && $module_settings["show_remaining"]) {
			$downloadCheck = CWcheckCustomerDownload(
								$module_settings["sku_id"],
								$module_settings["customer_id"]
								);
			if (is_numeric($downloadCheck) && $downloadCheck > 0) {
?><br><span class="smallPrint"><?php echo $downloadCheck;?> download<?php if ($downloadCheck > 1) { ?>s<?php } ?> remaining</span>
<?php
			}
		}
		// /end show remaining
		// previous download data 
		if ($module_settings["customer_id"] != 0 && ($module_settings["show_last_version"] || $module_settings["show_last_date"])) {
?><br><span class="smallPrint"><?php
                // last download date 
			if ($dlData["date"] && $dlData["date"] != 0 && strtotime($dlData["date"]) !== false) {
?>Last Download: <?php echo cartweaverDate($dlData["date"]); ?><?php
			}
			// last download version 
			if (strlen(trim($dlData["version"]))) {
?> Version: <?php echo trim($dlData["version"]); ?><?php
			}
?></span><?php
		}
		// /end previous data 
	}
}
?>