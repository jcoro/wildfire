<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-init.php
File Date: 2012-05-12
Description: handles initialization functions for the Cartweaver application
Values are set for each page request, and for the cart application
To reset the application variables, log in to the Cartweaver admin
as a developer-level user and click the "Reset" link
==========================================================
*/
global $v;
// DEFAULTS FOR CONFIG VARIABLES 
$Cartweaver_Include_Start_Dir = getcwd();
chdir(dirname(__FILE__));
require_once("../inc/cw-inc-configdefaults.php");
chdir($Cartweaver_Include_Start_Dir);
function ListValueCount($list,$value,$delimiter=",") {
	$delimiter = substr($delimiter,0,1);
	$a = explode($delimiter,$list);
	if (in_array($value,$a)) {
		$vCount = 0;
		for ($n=0; $n<count($a); $n++) {
			if(strtolower($a[$n]) == strtolower($value)) {
				$vCount++;
			}
		}
		return $vCount;
	}
	return 0;
}

function ListFind($list,$value,$delimiter=",") {
	$delimiter = substr($delimiter,0,1);
	if (is_array($list)) $a = $list;
	else $a = explode($delimiter,$list);
	for($i=0;$i<count($a);$i++) {
		if($a[$i] == $value) {
			return true ;
		}
	}
	return false;
}

function sqlDate($date_start) {
	$datearray=explode("/",$date_start);
	if(count($datearray) > 2) {
		$date =$datearray[2].'-'.$datearray[1].'-'.$datearray[0];
		return $date;	
	}
	else
		return 0;
} 

function ListFindNoCase($list,$value,$delimiter=",") {
	$delimiter = substr($delimiter,0,1);
	if (is_array($list)) $a = $list;
	else $a = explode($delimiter,$list);
	for ($i=0;$i<count($a);$i++) {
		if(strtolower($a[$i]) == strtolower($value)) {
			return true;
		}
	}
	return false;
}

// Format the date for displays
function cwDateFormat($dateString, $short=false) {
	if($dateString && $dateString != "") {
		$format = "%c";
		if($short) {
		    $format = "%x";
		}
		$time = strtotime($dateString);
		return strftime($format,$time);
	}
	return "";
}

//Format date for insert to mysql
function mySQLFloat($number) {
	return str_replace(",",".",$number);
}

// Format for the money
function cartweaverMoney($theNum) {
	$cwLocaleInfo = localeconv();
	$retStr = number_format($theNum, 
					2, 
					$cwLocaleInfo["mon_decimal_point"],
					$cwLocaleInfo["mon_thousands_sep"]);
	if ($cwLocaleInfo["p_cs_precedes"]) {
		if ($cwLocaleInfo["p_sep_by_space"]) {
			$retStr = " ".$retStr;
		}
		$retStr = $cwLocaleInfo["currency_symbol"].$retStr;
	} else {
		if ($cwLocaleInfo["p_sep_by_space"]) {
			$retStr .= " ";
		}
		$retStr .= $cwLocaleInfo["currency_symbol"];
	}
	return $retStr; 
		
}

if (!function_exists("cartweaverDate")) {
function cartweaverDate($tDate) {
	if (strpos($tDate, "-") !== false || strpos($tDate, "+") !== false) {
		return date($_ENV["application.cw"]["globalDateMask"], strtotime($tDate));
	}
	else if (cartweaverStrtotime($tDate)) {
		return date($_ENV["application.cw"]["globalDateMask"], cartweaverStrtotime($tDate));
	}
	return false;
}
function cartweaverScriptDate($tDate,$tMask=null) {
	if ($tMask === null) $tMask = $_ENV["request.cw"]["scriptDateMask"];
	if (strpos($tDate, "-") !== false || strpos($tDate, "+") !== false) {
		return date($_ENV["request.cw"]["scriptDateMask"], strtotime($tDate));
	}
	else if (cartweaverStrtotime($tDate)) {
		return date($_ENV["request.cw"]["scriptDateMask"], cartweaverStrtotime($tDate, $tMask));
	}
	return false;
}
function cartweaverOrderDate($tDate, $tMask=null) {
	if ($tMask === null) $tMask = $_ENV["request.cwpage"]["orderDateMask"];
	if (strpos($tDate, "-") !== false || strpos($tDate, "+") !== false) {
		return date($_ENV["request.cwpage"]["orderDateMask"], strtotime($tDate));
	}
	else if (cartweaverStrtotime($tDate, $tMask)) {
		return date($_ENV["request.cwpage"]["orderDateMask"], cartweaverStrtotime($tDate, $tMask));
	}
	return false;
}
function cartweaverStrtotime($tDate, $tMask=null) {
	if ($tMask === null) $tMask = $_ENV["application.cw"]["globalDateMask"];
	if (is_numeric($tDate) && (!strtotime($tDate) || (strpos($tDate, "/") === false && strpos($tDate, "+") === false && strpos($tDate, "-") === false))) return $tDate;
	if (strpos($tDate, "-") !== false || strpos($tDate, "+") !== false) return strtotime($tDate);
	if (strpos($tMask, "d/m") !== false && strpos($tDate, "/") !== false && strtotime("25/12/2011") === false) {
		$dateArr = explode("/", $tDate);
		return strtotime($dateArr[2]."-".$dateArr[1]."-".$dateArr[0]);
	} else if (strpos($tMask, "m/d") !== false && strpos($tDate, "/") !== false && strtotime("12/25/2011") === false) {
		$dateArr = explode("/", $tDate);
		return strtotime($dateArr[2]."-".$dateArr[0]."-".$dateArr[1]);
	}
	if (function_exists("date_parse_from_format")) {
		$thisDate = date_parse_from_format($tMask, $tDate);
		if ($thisDate) {
			return strtotime($thisDate["year"]."-".$thisDate["month"]."-".$thisDate["day"]);
		}
		return false;
	}
	return strtotime($tDate);
}
}

// Get the current directory path
function currentDirPath() {
	// If the following functionality doesn't work (99% of the time it will) try the above
	$dirNameArray = get_included_files();
	$dirName = array_shift($dirNameArray);
	$dir = dirname($dirName);
	if (!$dir) {
		if(!empty($_SERVER['PATH_TRANSLATED'])) {
			$dir = dirname($_SERVER["PATH_TRANSLATED"]);
		}elseif(!empty($_SERVER['SCRIPT_FILENAME'])) {
			$dir = dirname($_SERVER["SCRIPT_FILENAME"]);
		}
	}
	return $dir;
}


// Return file path in Windows or Unix
function localPath() {
	$locPath = currentDirPath();
	$locPath = str_replace("\\\\","/",$locPath);
	$locPath = str_replace("\\","/", $locPath);
	$locPath = str_replace("cw4\\Admin","",$locPath);
	$locPath = str_replace("cw4\\admin","",$locPath);
	$locPath = str_replace("cw4/Admin","",$locPath);
	$locPath = str_replace("cw4/admin","",$locPath);
    return $locPath;
}

// Get file path (for Linux and Windows)
function expandPath($file) {
	return preg_replace("/\/+$/", "", localPath()) . "/" . $file;
}

//find image file
function isImageFile($src) { return preg_match('/^.+\.(gif|png|jpe?g|bmp|tif)$/i', $src); }

//for valid email check
function isValidEmail($email) {
	return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email);
}

//for valid url check
function isValidURL($url) {
	return preg_match("/^http(s)?:\/\/[a-z0-9-]+(\.[a-z0-9-]+)*(:[0-9]+)?(\.*)?$/i", $url);
}

// for get Requested URL
function getRequestURL() {
    $requestURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
		$requestURL .= "s";
	$requestURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
		$requestURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
	} else {
    	$requestURL .= $_SERVER["SERVER_NAME"];
	}
	if (strpos($_SERVER["REQUEST_URI"], "?") !== false) {
		$requestURL .= substr($_SERVER["REQUEST_URI"], 0, strpos($_SERVER["REQUEST_URI"], "?"));
	} else {
		$requestURL .= $_SERVER["REQUEST_URI"];
	}
    return $requestURL;
}


if (!function_exists("CWqueryGetRS")) {
function CWqueryParam($query_param) {
	if (get_magic_quotes_gpc()) $query_param = stripslashes($query_param);
	return mysql_real_escape_string($query_param);
}
function CWqueryGetRS($sql_statement) {
	$resultReturn = array("totalRows" => 0);
	if ($_ENV["request.cwapp"]["db_link"] !== false) {
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-global.php");
			chdir($myDir);
		}
		$resultRS = mysql_query($sql_statement, $_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$sql_statement);
		if ($resultRS !== false) {
			$rr = 0;
			while ($rowResult = mysql_fetch_assoc($resultRS)) {
				foreach ($rowResult as $keyRS => $rowVal) {
					if ($rr == 0) {
						$resultReturn[$keyRS] = array();
					}
					$resultReturn[$keyRS][] = $rowVal;
				}
				$rr++;
			}
			$resultReturn["totalRows"] = $rr;
		}
	}
	return $resultReturn;	
}
}


function CWcheckApplicationRefresh() {
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$tFile = realpath("../../".$_ENV["application.cw"]["appImagesDir"]."/do-not-remove-appvariable-refresh.txt");
	chdir($myDir);
	if (file_exists($tFile)) {
		$fP = fopen($tFile, "r");
		$tTime = fread($fP, filesize($tFile));
		fclose($fP);
		if (!isset($_SESSION["cw"]["lastAppVariableRefresh"]) || $_SESSION["cw"]["lastAppVariableRefresh"] < $tTime) {
			return true;
		}
	}
	return false;
}
function CWsetApplicationRefresh() {
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$tFile = realpath("../../".$_ENV["application.cw"]["appImagesDir"]."/do-not-remove-appvariable-refresh.txt");
	chdir($myDir);
	if (file_exists($tFile)) { unlink($tFile); }
	$fP = fopen($tFile, "w+");
	fwrite($fP, time());
	fclose($fP);
}


function CWinitApplication($fullreset=false) { 
	global $v;
	$appProducts = array();
	$appCats = array();
	$appSubCats = array();
	$productsQuery = "";
	$catsQuery = "";
	$subcatsQuery = "";
	// Set Store/Company information in Application Variables 
	// RESET CW APPLICATION VARIABLES 
	// If the companyname application variable isn't set or the user has
	//		requested to reset the application variables (must be logged in) 
	if (!isset($_GET["resetapplication"])) $_GET["resetapplication"] = "";
	if (!isset($_ENV["application.cw"]["debugPassword"])) $_ENV["application.cw"]["debugPassword"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = array();
	if (!isset($_ENV["request.cwpage"]["categoryID"])) $_ENV["request.cwpage"]["categoryID"] = "";
	if (!isset($_ENV["request.cwpage"]["secondaryID"])) $_ENV["request.cwpage"]["secondaryID"] = "";
	// reset application.cw if key variable (company name) does not exist,
	// 	  or if logged in as admin and url.resetapplication=[debugPassword]
	if ($_ENV["request.cwapp"]["db_link"] === false || !isset($_ENV["application.cw"]["dbok"]) || !$_ENV["application.cw"]["dbok"]) {
		die("<p>Error: Database
Unavailable<br>Please refer to the Cartweaver installation instructions, and
verify the database and data connection are set up correctly.</p>");
	} else {
		if ($fullreset || (isset($_GET["dbsetup"]) && $_GET["dbsetup"] == "ok") || ((CWcheckApplicationRefresh()) ||
				(!isset($_ENV["application.cw"]["companyName"]) || $_ENV["application.cw"]["companyName"] == "") ||
				(!isset($_ENV["application.cw"]["authMethodData"]) || !sizeof($_ENV["application.cw"]["authMethodData"])) ||
				(
					$_GET["resetapplication"] == $_ENV["application.cw"]["debugPassword"] &&
					isset($_SESSION["cw"]["loggedIn"]) && $_SESSION["cw"]["loggedIn"] == '1' &&
					isset($_SESSION["cw"]["accessLevel"]) && (strpos($_SESSION["cw"]["accessLevel"], 'developer') !== false || strpos($_SESSION["cw"]["accessLevel"], 'merchant') !== false)))) {
			$_SESSION["cw"]["lastAppVariableRefresh"] = time();
			// turn off debugging 
			if (isset($_SESSION["cw"]["debug"])) $_SESSION["cw"]["debug"] = false;
			// Get all configuration settings from the database 
			$configQuery="";
			$configQuery ="SELECT config_type, config_variable, config_value FROM cw_config_items ORDER BY config_variable";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-global.php");
				chdir($myDir);
			}
			$result = mysql_query($configQuery,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error());
			if ($result !== false) {
				$_ENV["variables"]["persistedVars"] = array("db_hostname","db_user","db_password","db_databasename","appIncludePath","dbok");
				// clear out application.cw, except dsn settings 
				foreach ($_ENV["application.cw"] as $key => $i) {
					if (!in_array($key, $_ENV["variables"]["persistedVars"]) && isset($_ENV["application.cw"][$key])) {
						unset($_ENV["application.cw"][$key]);
					}
				}
				// set all configuration values as application variables 
				$i=0;
				while($row=mysql_fetch_array($result)) {
					try {
						$_ENV["application.cw"][$row['config_variable']] = trim($row['config_value']);
						switch ($row['config_type']) {
							case "boolean":
								if ($_ENV["application.cw"][$row['config_variable']] === "true") $_ENV["application.cw"][$row['config_variable']] = true;
								if ($_ENV["application.cw"][$row['config_variable']] === "false") $_ENV["application.cw"][$row['config_variable']] = false;
								if ($_ENV["application.cw"][$row['config_variable']] === "1") $_ENV["application.cw"][$row['config_variable']] = true;
								if ($_ENV["application.cw"][$row['config_variable']] === "0") $_ENV["application.cw"][$row['config_variable']] = false;
								break;
							case "number":
								$_ENV["application.cw"][$row['config_variable']] = intval($_ENV["application.cw"][$row['config_variable']]);
								break;
						}
						if ($row["config_variable"] == "globalLocale") {
							$localeInfo = explode(",", $_ENV["application.cw"][$row['config_variable']]);
							$_ENV["application.cw"]["globalLocaleCodes"] = $_ENV["application.cw"][$row['config_variable']];
							$_ENV["application.cw"][$row['config_variable']] = $localeInfo[0];
							for ($n=sizeof($localeInfo)-1; $n >= 1; $n--) {
								$_ENV["application.cw"]["globalLocaleCode"] = setlocale(LC_ALL, $localeInfo[$n]);
								$testData = localeconv();
								if (strpos($localeInfo[$n], "UTF8") !== false && !$testData["currency_symbol"]) {
									$localeInfo[$n] = str_replace("UTF8", "UTF-8", $localeInfo[$n]);
									$_ENV["application.cw"]["globalLocaleCode"] = setlocale(LC_ALL, $localeInfo[$n]);
								}
								if ($_ENV["application.cw"]["globalLocaleCode"]) break;
							}
						}
					}
					catch(Exception $e) {
						$_ENV["request.cwpage"]["userAlert"][] = 'Error with variable'.$row['config_variable'].':'.$e->getMessage().'<br>';
					}
				}
			}

			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-global.php");
				chdir($myDir);
			}
			// marker for confirmation message 
			$_ENV['request.cwpageReset'] = true;
			// debugging password 
			$_ENV["application.cw"]["storePassword"] = $_ENV["application.cw"]["debugPassword"];
			// declare variables for image display 
			$_ENV["application.cw"]["siteRoot"] = $_SERVER['SCRIPT_NAME'];
			$site = explode('/', $_ENV["application.cw"]["siteRoot"]);
			$sitelast = $site[count($site) - 1];
			$_ENV["application.cw"]["siteRoot"] = substr($_ENV["application.cw"]["siteRoot"],0,strlen($_ENV["application.cw"]["siteRoot"]) - strlen($sitelast));
			// remove admin path from site root variable (added if application variables are reset in the admin) 
			$_ENV["application.cw"]["siteRoot"] = str_replace($_ENV["application.cw"]["appCWAdminDir"], "", $_ENV["application.cw"]["siteRoot"]);
			$_ENV["application.cw"]["siteRoot"] = str_replace(str_replace($_ENV["application.cw"]["appCWAdminDir"], "/", "\\"), "", $_ENV["application.cw"]["siteRoot"]);
			// Set the cartweaver version for support purposes 
			if((isset($_ENV["request.cw"]["versionNumber"])) && $_ENV["request.cw"]["versionNumber"] != $_ENV["application.cw"]["appVersionNumber"]) {
				$_ENV["request.cwapp"]["db_link"];
				$Query="";
				$Query ="UPDATE cw_config_items
						SET
						config_value = '".CWqueryParam($_ENV["request.cw"]["versionNumber"])."'
						WHERE config_variable = 'appVersionNumber'";
				if (!function_exists("CWpageMessage")) {
					$myDir = getcwd();
					chdir(dirname(__FILE__));
					// global functions 
					require_once("cw-func-global.php");
					chdir($myDir);
				}
				$_ENV["application.cw"]["appVersionNumber"] = $_ENV["request.cw"]["versionNumber"];
			}
			// verify developer email is valid, or use company email 
			$testEmail = "";
			if ($_ENV["application.cw"]["developerEmail"] != "") {
				$mailAddresses = $_ENV["application.cw"]["developerEmail"];
				if (!is_array($mailAddresses) && strlen($mailAddresses)) $mailAddresses = explode(",", $mailAddresses);
				else if (!is_array($mailAddresses)) $mailAddresses = array();
				// loop address list, delivering mail and tracking success or errors 
				foreach ($mailAddresses as $key => $aa) {
					// verify mail valid 
					if (isValidEmail(trim($aa))) {
						if ($testEmail) $testEmail .= ",";
						$testEmail .= $aa;
					}
				}
			}
			if($testEmail == '') {
				$_ENV["application.cw"]["developerEmail"] = $_ENV["application.cw"]["companyEmail"];
			}
			// PAYMENT METHODS 
            $authDirectory = preg_replace('/\/+$/', "/", $_ENV["application.cw"]["appCWContentDir"]).'cwapp/auth';
			if (substr($authDirectory, 0, 1) == "/") $authDirectory = substr($authDirectory, 1);
			// get all available files in auth directory 
			$authDir = expandPath($authDirectory);
			$dir = opendir($authDir);
			if ($dir !== false) {
				$files = readdir($dir);
				while ($files !== false) {
					if (is_file($authDir . "/" . $files) && preg_match("/\.php$/", $files)) $listAuthFiles[] = $files;
					$files = readdir($dir);
				}
			}
			// set up array for payment options 
			$_ENV["variables"]["authoptions"] = array();
			// set up list of possible values 
			$_ENV["variables"]["authConfigOptions"] = array();
			// set up list of active IDs 
			$_ENV["variables"]["authMethods"] = array();
			// loop files 
			for($j=1; $j<=sizeof($listAuthFiles); $j++) {
				// data about this payment method is returned in the 'CWauthMethod' scope 
				$caller = array();
				$caller["CWauthMethod"] = array();
				// invoke file to get payment info 
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				$auth_settings = array(
					"auth_mode" => "config");
				include($authDir."/".$listAuthFiles[$j-1]."");
				unset($auth_settings);
				chdir($myDir);
				if(isset($caller["CWauthMethod"]) && sizeof($caller["CWauthMethod"])) {
					// add filename to struct 
					$caller["CWauthMethod"]["methodFileName"] = $listAuthFiles[$j-1];
					// add key array value to struct 
					$caller["CWauthMethod"]['methodID'] = $j;
					// add struct to array 
					$_ENV["variables"]["authoptions"][] = $caller["CWauthMethod"];
					// save possible values for paymentMethods config variable 
					$optionSet = $caller["CWauthMethod"]["methodName"] . '|' . $caller["CWauthMethod"]["methodFileName"];
					$_ENV["variables"]["authConfigOptions"][] = $optionSet;
					// increment the counter 
				}
			}
			// set the array of payment options data into application scope 
			$_ENV["application.cw"]["authMethodData"] = $_ENV["variables"]["authoptions"];
			// set the possible values of the config item "paymentMethods" 
			$query = "UPDATE cw_config_items SET config_possibles = '".CWqueryParam(implode(chr(10), $_ENV["variables"]["authConfigOptions"]))."' WHERE config_variable = 'paymentMethods'";
			if (!function_exists("CWpageMessage")) {
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				// global functions 
				require_once("cw-func-global.php");
				chdir($myDir);
			}
			mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error());
			// compare to active options list, removing others 
			for($i=0; $i<sizeof($_ENV["application.cw"]["authMethodData"]); $i++) {
				// if filename matches selected config value, add to list of active options 
				if(ListFindNoCase($_ENV["application.cw"]["paymentMethods"],$_ENV["application.cw"]["authMethodData"][$i]['methodFileName'])) {
					$_ENV["variables"]["authMethods"][] = $_ENV["application.cw"]["authMethodData"][$i]['methodID'];
				}
			}
			// save active methods to application scope 
			$_ENV["application.cw"]["authMethods"] = $_ENV["variables"]["authMethods"];
			// delete temp variables 
			unset($_ENV["variables"]["authMethods"]);
			unset($_ENV["variables"]["authConfigOptions"]);
			unset($_ENV["variables"]["persistedVars"]);
			// SET DEFAULT COUNTRY 
			$rsCWDefaultCountry = "SELECT country_id FROM cw_countries WHERE country_default_country = 1";
			$resultCountry = CWqueryGetRS($rsCWDefaultCountry);
			if($resultCountry['totalRows'] > 0) {
				$_ENV["application.cw"]["defaultCountryID"] = $resultCountry['country_id'][0];
			} else {
				$_ENV["application.cw"]["defaultCountryID"] = 0;
			}
			// DATABASE TYPES: manage SQL differences used in query functions 	
			if(!isset($_ENV["application.cw"]["appDbType"])) { $_ENV["application.cw"]["appDbType"] = "mySQL"; }
			// ms access 
			if($_ENV["application.cw"]["appDbType"] == 'MSAccess' || $_ENV["application.cw"]["appDbType"] == 'MSAccessJet') {
				$_ENV["application.cw"]["sqlLower"] = 'lcase';
				$_ENV["application.cw"]["sqlUpper"] = 'ucase';
				// mysql, mssql 
			} else {
				$_ENV["application.cw"]["sqlLower"] = 'lower';
				$_ENV["application.cw"]["sqlUpper"] = 'upper';
			}
			// SITE URLS 
			// clean up application URL variables 
			// add trailing slash to http and https urls 
			if(strlen(trim($_ENV["application.cw"]["appSiteUrlHttp"])) != 0 && substr($_ENV["application.cw"]["appSiteUrlHttp"],0,-1) != "/") {
				$_ENV["application.cw"]["appSiteUrlHttp"] .= "/";
			}
			if(strlen(trim($_ENV["application.cw"]["appSiteUrlHttps"])) != 0 && substr($_ENV["application.cw"]["appSiteUrlHttps"],0,-1) != "/") {
				$_ENV["application.cw"]["appSiteUrlHttps"] .= "/";
			}
			// remove trailing slash from storeroot, for use below 
			if (substr($_ENV["application.cw"]["appCWStoreRoot"],0,1) == '/' && strlen(trim($_ENV["application.cw"]["appCWStoreRoot"])) > 1) {
				$urlRoot = substr($_ENV["application.cw"]["appCWStoreRoot"],1);
			} else {
				$urlRoot = $_ENV["application.cw"]["appCWStoreRoot"];
			}
			// SECURE / STANDARD FULL URLS 
			// CHECKOUT PAGE uses HTTPS, if an https address is provided in the admin (site setup > global settings) 
			if(isValidURL($_ENV["application.cw"]["appSiteUrlHttps"]) && substr($_ENV["application.cw"]["appSiteUrlHttps"],0,6) == 'https:') {
				// verify checkout page is not already a full url 
				if(!preg_match('/https?\:\/\//i',$_ENV["application.cw"]["appPageCheckOut"])) {
					// add the prefix and any additional path, resulting in https:// url for the checkout page 
					$_ENV["application.cw"]["appPageCheckoutUrl"] = $_ENV["application.cw"]["appSiteUrlHttps"].$urlRoot.$_ENV["application.cw"]["appPageCheckOut"];
				}
			} else {
				// if not valid, or not in use, delete the variable, preventing caching of old settings 
				unset($_ENV["application.cw"]["appPageCheckoutUrl"]);
			}
			// ALL OTHER PAGES use HTTP 
			/*
			NOTE: these are available in the application scope as full urls at any time, but not enforced
			to override relative links in your store, set the values of the corresponding request.cwpage variables for these pages in CWinitRequest()
			*/
			if(isValidURL($_ENV["application.cw"]["appSiteUrlHttp"]) && substr($_ENV["application.cw"]["appSiteUrlHttp"],0,5) == 'http:') {
				if(preg_match('/https?\:\/\//i',$_ENV["application.cw"]["appPageResults"])  == 0) {
					$_ENV["application.cw"]["appPageResultsURL"] = $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["application.cw"]["appPageResults"];
				}
				if(preg_match('/https?\:\/\//i',$_ENV["application.cw"]["appPageDetails"]) == 0) {
					$_ENV["application.cw"]["appPageDetailsURL"] = $_ENV["application.cw"]["appSiteUrlHttps"].$_ENV["application.cw"]["appPageDetails"];
				}
				if(preg_match('/https?\:\/\//i',$_ENV["application.cw"]["appPageShowCart"]) == 0) {
					$_ENV["application.cw"]["appPageShowCartURL"] = $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["application.cw"]["appPageShowCart"];
				}
				if(preg_match('/https?\:\/\//i',$_ENV["application.cw"]["appPageConfirmOrder"]) == 0) {
					$_ENV["application.cw"]["appPageConfirmOrderURL"] = $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["application.cw"]["appPageConfirmOrder"];
				}
				$_ENV["application.cw"]["listProducts"] = $appProducts;
			}
			// PRODUCTS: store product names, IDs in application scope 
			$productsQuery = "SELECT product_name AS name, product_ID AS ID FROM cw_products ORDER BY product_ID";
			$resultproduct = CWqueryGetRS($productsQuery);
			for($k=0; $k<$resultproduct['totalRows']; $k++) {
				$appProducts[$resultproduct["ID"][$k]] = $resultproduct["name"][$k];
			}
			$_ENV["application.cw"]["listProducts"] = $appProducts;
			// CATEGORIES: store category names, IDs in application scope 
			$catsQuery = "SELECT category_name AS name, category_ID AS ID FROM cw_categories_primary ORDER BY category_ID";
			$resultcategory = CWqueryGetRS($catsQuery);
			for($n=0; $n<$resultcategory['totalRows']; $n++) {
				$appCats[$resultcategory["ID"][$n]] = $resultcategory["name"][$n];
			}
			$_ENV["application.cw"]["listCategories"] = $appCats;
			// SECONDARY CATEGORIES: store category names, IDs in application scope 
			// SECONDARY CATEGORIES: store category names, IDs in application scope 
			$subcatsQuery = "SELECT secondary_name AS name, secondary_ID AS ID FROM cw_categories_secondary ORDER BY secondary_ID";
			$resultsubcats = CWqueryGetRS($subcatsQuery);
			for($n=0; $n<$resultsubcats['totalRows']; $n++) {
				$appSubCats[$resultsubcats["ID"][$n]] = $resultsubcats["name"][$n];
			}
			$_ENV["application.cw"]["listSubCategories"] = $appSubCats;
			// SHIPPING METHODS: if no active ship methods, turn shipping off globally 
			if($_ENV["application.cw"]["shipEnabled"]) {
				$shipMethodsQuery = "SELECT m.ship_method_id, c.ship_method_country_country_id
									FROM cw_ship_methods m, cw_ship_method_countries c, cw_countries co
									WHERE c.ship_method_country_method_id = m.ship_method_id
									AND co.country_id = c.ship_method_country_country_id
									AND NOT m.ship_method_archive = 1
									AND NOT co.country_archive = 1";
				$resultshipMethods = CWqueryGetRS($shipMethodsQuery);
				if($resultshipMethods['totalRows'] < 1) {
					$_ENV["application.cw"]["shipEnabled"] = false;
				}
			}
			$_SESSION["application.cwstorage"] = $_ENV["application.cw"];
		}
	}
}
// // ---------- // CWinitRequest : initialize request scope variables, runs on every page request  // ---------- // 
function CWinitRequest() {
	// VERIFY APPLICATION VARS HAVE BEEN SET 
	if (!isset($_ENV["application.cw"]["appCWAdminDir"])) {
		$resetApp = CWinitApplication(true);
	}
	// GLOBAL FILE/PATH VARIABLES  
	// current page filename : request.cw.thisPage (can be overridden with request.path) 
	if(isset($_GET["path"])) {
		$path = explode('/',$_GET["path"]);
		$pathlast = $path[count($path) - 1];
		$_ENV["request.cw"]["thisPage"] = $pathlast;
	} else if(isset($_POST["path"])) {
		$path = explode('/',$_POST["path"]);
		$pathlast = $path[count($path) - 1];
		$_ENV["request.cw"]["thisPage"] = $pathlast;
	} else {
		$_ENV["request.cw"]["thisPage"] = basename($_SERVER['SCRIPT_FILENAME']);
	}
	// current page filename with query string : request.cw.thisPageQS 
	$_ENV["request.cw"]["thisPageQS"] = $_ENV["request.cw"]["thisPage"].'?'.$_SERVER['QUERY_STRING'];
	// default logout url 
	if(!isset($_ENV["request.cwpage"]["logoutUrl"])) { $_ENV["request.cwpage"]["logoutUrl"] = $_ENV["request.cw"]["thisPageQS"]; }
	// get page context for URL lookup and secure redirection 
	// if page is not a required secure page, redirect back to http 
	/*
	 NOTE: This process handles redirection away from the https prefix, back to http, where ssl protection is not needed.
	 	   Redirection _to_ the secure page is handled by the checkout URL in the request scope below.
	 	   Add other pages to the request.cw.sslPages variable, as a comma-delimited list (no spaces), to allow use of https without redirection
	 	   or disable the admin setting appHttpRedirectEnabled to turn this off completely
	 */
	$_ENV["request.cw"]["sslPages"] = $_ENV["application.cw"]["appPageCheckout"].','.$_ENV["application.cw"]["appPageAccount"];
	// if redirection from https back to http is enabled, and the current page is not in our list of secure-only pages (checkout, and any others added) 
	if (isset($_ENV["application.cw"]["appHttpRedirectEnabled"]) && $_ENV["application.cw"]["appHttpRedirectEnabled"] && stripos($_ENV["request.cw"]["sslPages"], $_ENV["request.cw"]["thisPage"]) === false) {
		// check for https in use - cgi values may vary between servers, so multiple flags are checked here 
		if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") {
			// if page is being requested securely, send to http version of same address 
			header("Location: http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]);
			exit;
		}
	}
	// entire page url: request.cw.thisUrl 
	$_ENV["request.cw"]["thisUrl"] = getRequestURL();
	// current directory url: request.cw.thisDir 
	$str = explode('/',$_ENV["request.cw"]["thisUrl"]);
	$strlast = $str[count($str) - 1];
	$_ENV["request.cw"]["thisDir"] = substr($_ENV["request.cw"]["thisUrl"], 0, (strlen($_ENV["request.cw"]["thisUrl"]) - strlen($strlast)));
	if (substr($_ENV["request.cw"]["thisDir"],-1) != "/") {
		$_ENV["request.cw"]["thisDir"] .= "/";
	}
	// append query string to url variable 
	if (strlen($_SERVER["QUERY_STRING"])) {
		$_ENV["request.cw"]["thisUrl"] .= "?".$_SERVER["QUERY_STRING"];
	}
	$str = explode('/',$_ENV["request.cw"]["thisDir"]);
	// current directory name: request.cw.thisDirName 
	if (sizeof($str) > 1) $_ENV["request.cw"]["thisDirName"] = $str[count($str) - 2];
	else $_ENV["request.cw"]["thisDirName"] = $str[count($str) - 1];
	// admin directory name: request.cw.adminDirName 
	$str = explode('/',$_ENV["application.cw"]["appCWAdminDir"]);
	if (sizeof($str) > 1) $_ENV["request.cw"]["adminDirName"] = $str[count($str) - 2];
	else $_ENV["request.cw"]["adminDirName"] = $str[count($str) - 1];
	// src directory for scripts, other relative assets 
	$_ENV["request.cw"]["assetSrcDir"] = $_ENV["application.cw"]["appCWContentDir"];
	// SET LOCALE 
	try {
		setlocale(LC_ALL,$_ENV["application.cw"]["globalLocaleCode"]);
		$testData = localeconv();
		if (strpos($_ENV["application.cw"]["globalLocaleCode"], "UTF8") !== false && !$testData["currency_symbol"]) {
			$_ENV["application.cw"]["globalLocaleCode"] = str_replace("UTF8", "UTF-8", $_ENV["application.cw"]["globalLocaleCode"]);
			setlocale(LC_ALL, $_ENV["application.cw"]["globalLocaleCode"]);
		}
	} catch(Exception $e) {
		setlocale(LC_ALL,'English_United States');
	}
	// LOCALE SETTINGS for js currency format 
	if (!(isset($_ENV["application.cw"]["currencyDecimal"]) && strlen(trim($_ENV["application.cw"]["currencyDecimal"])))) {
		try {
			$localeSettings = localeconv();
			// get currency symbol values for the request url 
			$_ENV["application.cw"]["currencyPrecedes"] = (($localeSettings["p_cs_precedes"]) ? true : false );
			$_ENV["application.cw"]["currencySymbol"] = $localeSettings["currency_symbol"];
			$_ENV["application.cw"]["currencyDecimal"] = $localeSettings["mon_decimal_point"];
			$_ENV["application.cw"]["currencyGroup"] = $localeSettings["mon_thousands_sep"];
			// get currency space separator 
			if ($localeSettings["p_sep_by_space"]) {
				$_ENV["application.cw"]["currencySpace"] = " ";
			} else {
				$_ENV["application.cw"]["currencySpace"] = "";
			}
		// set defaults on error 
		} catch (Exception $e) {
			$_ENV["application.cw"]["currencyPrecedes"] = false;
			$_ENV["application.cw"]["currencySymbol"] = "";
			$_ENV["application.cw"]["currencyDecimal"] = ".";
			$_ENV["application.cw"]["currencyGroup"] = ",";
			$_ENV["application.cw"]["currencySpace"] = "";
		}
	}
	// DEFAULT REQUEST VARS 
	// ADMIN ONLY 
	if($_ENV["request.cw"]["thisDirName"] == $_ENV["request.cw"]["adminDirName"]) {
		// Page Browser Window Title 
		if(!isset($_ENV["request.cwpage"]["title"]) || $_ENV["request.cwpage"]["title"] == '') { $_ENV["request.cwpage"]["title"] = "Store Administration"; }
		// Page Main Heading <h1> 
		if(!isset($_ENV["request.cwpage"]["heading1"]) || $_ENV["request.cwpage"]["heading1"] == '') { $_ENV["request.cwpage"]["heading1"] = "Store Administration"; }
		// Page Subheading (instructions) <h2> 
		if(!isset($_ENV["request.cwpage"]["heading2"]) || $_ENV["request.cwpage"]["heading2"] == '') { $_ENV["request.cwpage"]["heading2"] = "Manage Store Options"; }
		// location for view site link 
		if(!isset($_ENV["request.cwpage"]["viewSiteURL"]) || $_ENV["request.cwpage"]["viewSiteURL"] == '') { $_ENV["request.cwpage"]["viewSiteURL"] = $_ENV["application.cw"]["appSiteUrlHttp"]; }
		// view site text 
		if(!isset($_ENV["request.cwpage"]["viewSiteText"]) || $_ENV["request.cwpage"]["viewSiteText"] == '') { $_ENV["request.cwpage"]["viewSiteText"] = "View Site"; }
		// id for menu item to mark current 
		$scriptname = $_SERVER['SCRIPT_FILENAME'];
		if (strpos($scriptname, "/") !== false) {
			$scriptname = explode('/',$scriptname);
		} else {
			$scriptname = explode('\\',$scriptname);
		}
		$scriptname_last = $scriptname[count($scriptname) - 1];
		if(!isset($_ENV["request.cwpage"]["currentNav"]) || $_ENV["request.cwpage"]["currentNav"] == '') { $_ENV["request.cwpage"]["currentNav"] = $scriptname_last; }
		// prefix for relative image path from admin to images dir (default "../") 
		if(!isset($_ENV["request.cwpage"]["adminImgPrefix"]) || $_ENV["request.cwpage"]["adminImgPrefix"] == '') { $_ENV["request.cwpage"]["adminImgPrefix"] = "../"; }
	// FRONT END ONLY (site pages, not admin) 	
	} else {
		// page browser title 
		if(!isset($_ENV["request.cwpage"]["title"]) || $_ENV["request.cwpage"]["title"] == '') { $_ENV["request.cwpage"]["title"] = $_ENV["application.cw"]["companyName"]; }
		// prefix for relative links to admin (default "" - not used) 
		if(!isset($_ENV["request.cwpage"]["adminURLPrefix"]) || $_ENV["request.cwpage"]["adminURLPrefix"] == '') { $_ENV["request.cwpage"]["adminURLPrefix"] = "../"; }
	}
	// /END ADMIN/FRONT END ONLY 
	// STORE PAGES 
	$list = array("Results","Details","ShowCart","Checkout","ConfirmOrder","Account","Search","Download");
	for($pp=0; $pp<count($list); $pp++) {
		try
		{
			if(!isset($_ENV["request.cwpage"]["url".trim($list[$pp])]) || $_ENV["request.cwpage"]["url".trim($list[$pp])] == '') { $_ENV["request.cwpage"]["url".trim($list[$pp])] = trim($_ENV["application.cw"]["appCWStoreRoot"]).$_ENV["application.cw"]["appPage".trim($list[$pp])]; }
		}
		catch(Exception $e) {
			if(!isset($_ENV["request.cwpage"]["url".trim($list[$pp])]) || $_ENV["request.cwpage"]["url".trim($pp)] == '') { $_ENV["request.cwpage"]["url".trim($pp)] = ""; }
		}
	}
	// SECURE CHECKOUT PAGE 
	// if the Secure URL is different than the Site URL, the full https address will be used for the checkout and account pages 
	if (isset($_ENV["application.cw"]["appSiteUrlHttps"])) {
		$_ENV["request.cwpage"]["urlCheckout"] = $_ENV["application.cw"]["appSiteUrlHttps"] . $_ENV["application.cw"]["appPageCheckout"];
		$_ENV["request.cwpage"]["urlAccount"] = $_ENV["application.cw"]["appSiteUrlHttps"] . $_ENV["application.cw"]["appPageAccount"];
	}
	// CONFIRM COMPLETED ORDERS (for payments that don't return user to confirmation page automatically) 
	$chkoArr = explode("/", $_ENV["request.cwpage"]["urlCheckout"]);
	$confArr = explode("/", $_ENV["request.cwpage"]["urlConfirmOrder"]);
	if ($_ENV["request.cw"]["thisDirName"] != $_ENV["request.cw"]["adminDirName"] &&
		(isset($_ENV["application.cw"]["appOrderForceConfirm"]) && $_ENV["application.cw"]["appOrderForceConfirm"] == true) &&
		(isset($_SESSION["cwclient"]["cwCompleteOrderID"]) && $_SESSION["cwclient"]["cwCompleteOrderID"] != 0) &&
		!(isset($_ENV["application.cw"]["appTestModeEnabled"]) && $_ENV["application.cw"]["appTestModeEnabled"] == true) &&
		!($_ENV["request.cw"]["thisPage"] == $_ENV["request.cwpage"]["urlCheckout"] ||
			$_ENV["request.cw"]["thisPage"] == $_ENV["request.cwpage"]["urlConfirmOrder"] ||
			$_ENV["request.cw"]["thisPage"] == $chkoArr[sizeof($chkoArr)-1] ||
			$_ENV["request.cw"]["thisPage"] == $confArr[sizeof($confArr)-1] ||
			$_ENV["request.cw"]["thisPage"] == "reset.php")) {
		header("Location: ".$_ENV["request.cwpage"]["urlConfirmOrder"]);
	}
	// CHECK FOR COOKIES 
	if (!isset($_COOKIE["STORETEST"]) || $_COOKIE["STORETEST"] != "TESTING123") {
		$cookieSet = false;
		if (!isset($_GET["storecookie"])) {
			$redirPage = $_ENV["request.cw"]["thisPage"];
			if (substr($_ENV["request.cw"]["thisPage"], -1) != "?") {
				$redirPage .= "?";
			}
			if (isset($_SERVER["QUERY_STRING"]) && strlen(trim($_SERVER["QUERY_STRING"]))) {
				$redirPage .= $_SERVER["QUERY_STRING"]."&";
			}
			$cookieSet = setcookie("STORETEST", "TESTING123", 0);
		}
		if (!$cookieSet || !isset($_COOKIE["STORETEST"]) || $_COOKIE["STORETEST"] != "TESTING123") {
			$scArr = explode("/", $_ENV["request.cwpage"]["urlShowCart"]);
			$acctArr = explode("/", $_ENV["request.cwpage"]["urlAccount"]);
			if ($_ENV["request.cw"]["thisPage"] == $chkoArr[sizeof($chkoArr)-1] ||
				$_ENV["request.cw"]["thisPage"] == $scArr[sizeof($scArr)-1] ||
				$_ENV["request.cw"]["thisPage"] == $acctArr[sizeof($acctArr)-1]) {
				$_ENV["request.cwpage"]["userAlert"][] = 'Note: cookies must be enabled in your browser for proper cart functionality<br>';
			}
		}
	}
	// COOKIES EXPIRATION DATE
	if($_ENV["application.cw"]["appCookieTerm"] == 0) {
		$_ENV["request.cw"]["cookieExpire"] = time()-3600;
	}
	elseif(is_numeric($_ENV["application.cw"]["appCookieTerm"])) {
		$_ENV["request.cw"]["cookieExpire"] = mktime($_ENV["application.cw"]["appCookieTerm"]);
	} else {
		$_ENV["request.cw"]["cookieExpire"] = mktime(24*730);
	}
	// COOKIE VARS OVERWRITE BLANKS IN SESSION 
	if($_ENV["application.cw"]["appCookieTerm"] != 0) {
		// vars not to write to session 
		$_ENV["request.cw"]["noCookieSessionVars"] = array("sessionid","cwCustomerType","cwOrderTotal","cwShipCountryID","cwShipRegionID","cwShipTotal","cwShipTaxTotal","cwTaxCountryID","cwTaxRegionID","cwTaxTotal");
		foreach ($_COOKIE as $cc => $cookieVal) {
			// if cwclient variable does not already exist 
			if (strlen(trim($cc)) && !in_array($cc,$_ENV["request.cw"]["noCookieSessionVars"]) && isset($_COOKIE[$cc]) && strlen(trim($_COOKIE[$cc])) && substr($cc,0,1) != '_' && stripos($cc, "cfauthorization") === false && (!isset($_SESSION["cwclient"][$cc]) || !strlen(trim($_SESSION["cwclient"][$cc])))) {
				if (strpos($cookieVal, "CWPHPARRAY:") === 0) $cookieVal = explode(",", substr($cookieVal, 11));
				// create session.cwclient var 
				$_SESSION["cwclient"][$cc] = $cookieVal;
			}
		}
	}
	// CART ID 
	// set blank cookie if none exists (actual cookie value is set in cw-inc-pageend) 
	if(!isset($_COOKIE["cwCartID"])) {
		setcookie("cwCartID","0",$_ENV["request.cw"]["cookieExpire"]);
	}
	// client cart ID can be set by cookie, or by putting cart ID in url 
	if(!isset($_GET['cart']) || $_GET['cart'] == '') { $_GET['cart'] = ((isset($_COOKIE["cwCartID"])) ? $_COOKIE["cwCartID"] : "" ); }
	if(!isset($_SESSION["cwclient"]["cwCartID"]) || $_SESSION["cwclient"]["cwCartID"] == '') { $_SESSION["cwclient"]["cwCartID"] = $_GET['cart']; }
	// set user country default 
	if($_ENV["application.cw"]["taxUseDefaultCountry"]) {
		if(!isset($_SESSION["cwclient"]["cwTaxCountryID"]) || $_SESSION["cwclient"]["cwTaxCountryID"] == '') { $_SESSION["cwclient"]["cwTaxCountryID"] = $_ENV["application.cw"]["defaultCountryID"];  }
	}
	// if we have a cookie or url value saved 
	if($_GET['cart'] != 0) {
		$_SESSION["cwclient"]["cwCartID"] = $_GET['cart'];
		// prevent shipcost transfer if changing cart ids,
		// remove stored shipping value if it exists 
		if(isset($_SESSION["cwclient"]["shipTotal"]) && $_SESSION["cwclient"]["cwCartID"] != 0 && $_SESSION["cwclient"]["cwCartID"] != $_GET['cart']) {
			unset($_SESSION["cwclient"]["shipTotal"]);
		}
		// if not in url, and not defined in client, set new 
	} elseif(!isset($_SESSION["cwclient"]["cwCartID"]) || $_SESSION["cwclient"]["cwCartID"] == "" || $_SESSION["cwclient"]["cwCartID"] == 0) {
		$_SESSION["cwclient"]["cwCartID"] = date('YmdHs').rand(11111,99999);
	}
	// TRACK PAGE VIEWS 
	// set number of page views to save: this can be altered here 
	$viewSaveCt = 10;
	// set number of product views to save: this is set in admin 
	$viewProdCt = $_ENV["application.cw"]["appDisplayProductViews"];
	// if not already defined 
	if(!isset($_SESSION["cw"]["pageViews"]) || !strlen(trim($_SESSION["cw"]["pageViews"]))) {
		// set the current page as first item in list 
		$_SESSION["cw"]["pageViews"] = $_ENV["request.cw"]["thisPageQS"];
		// add the current page to the list 
	} else {
		// if not the same page as last view (refreshing) 
		$list1 = explode(',',$_SESSION["cw"]["pageViews"]);
		if(strtolower($list1[count($list1)-1]) != strtolower($_ENV["request.cw"]["thisPageQS"])) {
			// save previous page 
			$_SESSION["cw"]["pagePrev"] = $list1[count($list1)-1];
			// save current page to list 
			$list1[] = $_ENV["request.cw"]["thisPageQS"];
			$_SESSION["cw"]["pageViews"] = implode(",", $list1);
		}
		// only save the last xx pages 
		$pageCt = count($list1);
		if ($pageCt > $viewSaveCt) {
			$delCt = $pageCt - $viewSaveCt;
			for ($ii=0; $ii<$delCt; $ii++) {
				array_shift($list1);
				$_SESSION["cw"]["pageViews"] = implode(",",$list1);
			}
		}
	}
	// /end page views 
	// DISCOUNTS 
	// add discounts from user's session into page request 
	if (!isset($_SESSION["cwclient"]["discountApplied"])) $_SESSION["cwclient"]["discountApplied"] = "";
	if (!isset($_SESSION["cwclient"]["discountPromoCode"])) $_SESSION["cwclient"]["discountPromoCode"] = "";
	if ($_ENV["application.cw"]["discountsEnabled"]) {
		$_ENV["request.cwpage"]["discountsApplied"] = $_SESSION["cwclient"]["discountApplied"];
	// if discounts not active, set these values as null 
	} else {
		$_ENV["request.cwpage"]["discountsApplied"] = "";
	}
	// /end discounts 	
	// CONTINUE SHOPPING URL 
	// if previous page url is stored, and matches results/details admin preference, use the previous url 
	$_ENV["request.cwpage"]["continueShopping"] = $_ENV["application.cw"]["appActionContinueShopping"];
	$pagelist = explode('?',((isset($_SESSION["cw"]["pagePrev"])) ? $_SESSION["cw"]["pagePrev"] : "" ));
	$pagelist_first = $pagelist[0];
	if(isset($_SESSION["cw"]["pagePrev"]) && strlen(trim($_SESSION["cw"]["pagePrev"])) && $pagelist_first!= $_ENV["application.cw"]["appPageShowCart"] && ($_ENV["request.cwpage"]["continueShopping"] == 'results' && $pagelist_first == $_ENV["application.cw"]["appPageResults"]) || ($_ENV["request.cwpage"]["continueShopping"] == 'details' && $pagelist_first == $_ENV["application.cw"]["appPageDetails"])) {
		$_ENV["request.cwpage"]["returnUrl"] = $_SESSION["cw"]["pagePrev"];
	// if a matching previous page not stored, use client history to determine continue shopping address 	 
	} else {
		$returnUrlQS = '';
		// set up string for category , subcat and product 
		if(isset($_SESSION["cw"]["prodPrev"]) && is_numeric($_SESSION["cw"]["prodPrev"])) {
			$returnUrlQS = $returnUrlQS . 'product='.$_SESSION["cw"]["prodPrev"].'&';
		}
		if(isset($_SESSION["cw"]["productCatPrev"]) && is_numeric($_SESSION["cw"]["productCatPrev"])) {
			$returnUrlQS = $returnUrlQS . 'category='.$_SESSION["cw"]["productCatPrev"].'&';
		}
		if(isset($_SESSION["cw"]["productSecPrev"]) && is_numeric($_SESSION["cw"]["productSecPrev"])) {
			$returnUrlQS = $returnUrlQS . 'secondary='.$_SESSION["cw"]["productSecPrev"].'&';
		}
		if(strlen(trim($returnUrlQS))) {
			$returnUrlQS = '?'.trim($returnUrlQS);
		}
		if(isset($_GET['product']) && is_numeric($_GET['product']) && $_GET['product'] > 0) {
			$_ENV["request.cwpage"]["productID"] = $_GET['product'];
		} else {
			$_ENV["request.cwpage"]["productID"] = 0;
		}
		switch(strtolower($_ENV["request.cwpage"]["continueShopping"])) {
			case "results":
				// get the most recent results page view  
				if (trim($_SESSION["cw"]["pageViews"])) {
					$pvArr = explode(",", $_SESSION["cw"]["pageViews"]);
					foreach ($pvArr as $key => $vv) {
						if(stripos($vv, $_ENV["application.cw"]["appPageResults"]) !== false) {
							$_ENV["request.cwpage"]["returnUrl"] = trim($vv);
							break;
						}
					}
				}
				if(!(isset($_ENV["request.cwpage"]["returnUrl"]) && strlen(trim($_ENV["request.cwpage"]["returnUrl"])))) {
					$_ENV["request.cwpage"]["returnUrl"] = $_ENV["request.cwpage"]["urlResults"];
				}
				break;
			case "details":
				// get the most recent details page view 
				if (trim($_SESSION["cw"]["pageViews"])) {
					$pvArr = explode(",", $_SESSION["cw"]["pageViews"]);
					foreach ($_SESSION["cw"]["pageViews"] as $key => $vv) {
						if(stripos($vv, $_ENV["application.cw"]["appPageDetails"]) !== false && stripos($vv, 'product='.$_ENV["request.cwpage"]["productID"]) === false) {
							$_ENV["request.cwpage"]["returnUrl"] = trim($vv);
							break;
						}
					}
				}
				if(!(isset($_ENV["request.cwpage"]["returnUrl"]) && strlen(trim($_ENV["request.cwpage"]["returnUrl"])))) {
					$_ENV["request.cwpage"]["returnUrl"] = $_ENV["request.cwpage"]["urlDetails"].'?product='.$_ENV["request.cwpage"]["productID"].'&category='.$_ENV["request.cwpage"]["categoryID"].'&secondary='.$_ENV["request.cwpage"]["secondaryID"];
				}
				break;
			case "home":
				$_ENV["request.cwpage"]["returnUrl"] = $_ENV["application.cw"]["appSiteUrlHttp"];
				break;
		}
	}
	// returnUrl in url overrides 
	$listurl = explode('?',((isset($_GET["returnUrl"])) ? $_GET["returnUrl"] : "" ));
	$listurl_first = $listurl[0];
	if(isset($_GET["returnUrl"]) && strlen(trim($_GET["returnUrl"])) && ($listurl_first == $_ENV["application.cw"]["appPageDetails"] || $listurl_first == $_ENV["application.cw"]["appPageResults"])) {
		try {
			$_ENV["request.cwpage"]["returnUrl"] = urldecode(trim($_GET["returnUrl"]));
			$_ENV["request.cwpage"]["returnUrl"] = str_replace("<","",$_ENV["request.cwpage"]["returnUrl"]);
			$_ENV["request.cwpage"]["returnUrl"] = str_replace("&lt;","",$_ENV["request.cwpage"]["returnUrl"]);
			$_ENV["request.cwpage"]["returnUrl"] = str_replace(">","",$_ENV["request.cwpage"]["returnUrl"]);
			$_ENV["request.cwpage"]["returnUrl"] = str_replace("&gt;","",$_ENV["request.cwpage"]["returnUrl"]);
			$_ENV["request.cwpage"]["returnUrl"] = str_replace("'","",$_ENV["request.cwpage"]["returnUrl"]);
			$_ENV["request.cwpage"]["returnUrl"] = str_replace('"','',$_ENV["request.cwpage"]["returnUrl"]);
			$_ENV["request.cwpage"]["returnUrl"] = str_replace(';','',$_ENV["request.cwpage"]["returnUrl"]);
		} catch(Exception $e) {
			$_ENV["request.cwpage"]["returnUrl"] = $_ENV["request.cwpage"]["urlResults"];
		}
	}
	// /end continue shopping url 
	// save product info (if available) 
	// category 
	if(!isset($_SESSION["cw"]["productCatCurrent"]) || $_SESSION["cw"]["productCatCurrent"] == '') { $_SESSION["cw"]["productCatCurrent"] = 0; }
	if(isset($_GET['category']) && is_numeric($_GET['category']) && $_GET['category'] > 0) {
		// if not already previous category 
		if(!(isset($_GET['productcatprev'])) || $_GET['category'] != $_SESSION["cw"]["productCatCurrent"]) {
			// save the previous entry 
			$_SESSION["cw"]["productCatPrev"] = $_SESSION["cw"]["productCatCurrent"];
			// replace with current 
			$_SESSION["cw"]["productCatCurrent"] = $_GET['category'];
		}
	}
	// secondary 
	if(!isset($_SESSION["cw"]["productSecCurrent"]) || $_SESSION["cw"]["productSecCurrent"] == '') { $_SESSION["cw"]["productSecCurrent"] = 0; }
	if(isset($_GET['secondary']) && is_numeric($_GET['secondary']) && $_GET['secondary'] > 0) {
		// if not already previous category 
		if(!(isset($_GET['productsecprev'])) || $_GET['secondary'] != $_SESSION["cw"]["productSecCurrent"]) {
			// save the previous entry 
			$_SESSION["cw"]["productSecPrev"] = $_SESSION["cw"]["productSecCurrent"];
			// replace with current 
			$_SESSION["cw"]["productSecCurrent"] = $_GET['secondary'];
		}
	}
	// product ID 
	if(isset($_GET['product']) && is_numeric($_GET['product']) && $_GET['product'] > 0) {
		// if not already defined 
		if(!isset($_SESSION["cwclient"]["cwProdViews"]) || !strlen(trim($_SESSION["cwclient"]["cwProdViews"]))) {
			// set the current page as first item in list 
			$_SESSION["cwclient"]["cwProdViews"] = $_GET['product'];
			// add the current page to the list 
		} else {
			// save previous id 
			$listpro = explode(',',$_SESSION["cwclient"]["cwProdViews"]);
			$_SESSION["cw"]["prodPrev"] = $listpro[0];
			// if not already in the list 
			if(!ListFindNoCase($_SESSION["cwclient"]["cwProdViews"], $_GET['product'])) {
				// save current page to list
				//- prepend adds it to front of list for showing reverse view order 
				array_unshift($listpro, $_GET['product']);
				$_SESSION["cwclient"]["cwProdViews"] = implode(",", $listpro);
				// if it is already in the list, move to front 
			} else {
				$pos = ListFindNoCase($_SESSION["cwclient"]["cwProdViews"],$_GET['product']);
				unset($listpro[$pos]);
				$_SESSION["cwclient"]["cwProdViews"] = implode(",", $listpro);
			}
		}
	}
	// only save the first xx products 
	if (isset($_SESSION["cwclient"]["cwProdViews"])) {
		$len = explode(',',$_SESSION["cwclient"]["cwProdViews"]);
	} else {
		$len = array();
	}
	if(isset($_SESSION["cwclient"]["cwProdViews"]) && count($len) > 0 ) {
		$prodCt = count($len);
		// if the list is longer than the desired number of saved products 
		if($prodCt > $viewProdCt) {
			// add 1 before deleting : see cw-inc-footer for display
			//and 1 more in case current page is in the list, can still show requested number 
			$delStart = $viewProdCt + 2;
			for($ii=$delStart; $ii<$prodCt; $ii++) {
				unset($len[$ii]);
				$_SESSION["cwclient"]["cwProdViews"] = implode(",", $len);
			}
		}
	} else {
		// if none exist, create blank list placeholder 
		$_SESSION["cwclient"]["cwProdViews"] = '';
	}
	// /end product info 
	// PRODUCT VIEWS (COOKIE) 
	try {
		// only applies to front end 
		if($_ENV["request.cw"]["thisDirName"] != $_ENV["request.cw"]["adminDirName"]) {
			// if no products, read cookie in if it exists already 
			if(count($len) == 0 && isset($_COOKIE['cwProdViews'])) {
				$_SESSION["cwclient"]["cwProdViews"] = $_COOKIE['cwProdViews']; 
			}
		}
	} catch(Exception $e) {
		// fails silently, no error thrown 
	}
	// sort preferences 
	if(isset($_GET['sortby']) && strlen(trim($_GET['sortby']))) {
		$_SESSION["cwclient"]["cwProductSortBy"] = trim($_GET['sortby']);
	}
	if(isset($_GET['sortdir']) && strlen(trim($_GET['sortdir']))) {
		$_SESSION["cwclient"]["cwProductSortDir"] = trim($_GET['sortdir']);
	}
	if(isset($_GET['perpage']) && strlen(trim($_GET['perpage']))) {
		$_SESSION["cwclient"]["cwProductPerPage"] = trim($_GET['perpage']);
	}
	// LOG OUT 
	if(isset($_GET['logout']) && $_GET['logout'] == 1 || (isset($_ENV["request.cwpage"]["logout"]) && $_ENV["request.cwpage"]["logout"] == 1)) {
		// persist cart id to cookie
		if(isset($_SESSION["cwclient"]["cwCartID"]) && $_SESSION["cwclient"]["cwCartID"] != 0) {
			setcookie("cwCartID",$_SESSION["cwclient"]["cwCartID"]);
		}
		// if previously logged in, show message 
		if(isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0") {
			$_ENV["request.cwpage"]["userAlert"][] = 'Log Out Successful'; 
		}
		// clear "cw" scope, including all checkout confirmations 
		$cust = array("authType","confirmAuthPref","confirmCart","confirmAddress","confirmShip","confirmShipName","confirmOrder","confirmShipID");
		foreach ($cust as $key => $cc) {
			unset($_SESSION["cw"][trim($cc)]);
		}
		// clear "cwclient" scope, including session authentication and related cookie vars (set to null/expired) 
		$cust = array("cwCustomerName","cwCustomerID","cwCustomerType","cwOrderTotal","cwShipTaxTotal","cwShipTotal","cwTaxTotal","cwShipCountryID","cwShipRegionID","cwTaxCountryID","cwTaxRegionID");
		foreach ($cust as $key => $cc) {
			unset($_SESSION["cwclient"][trim($cc)]);
		}
		setcookie("cwCustomerID","",0);
	}
	// DEBUGGING OUTPUT 
	if(isset($_GET['debug']) && $_ENV["application.cw"]["debugEnabled"] == true) {
		// if password matches AND user is logged in 
		if($_GET['debug'] == $_ENV["application.cw"]["storePassword"] && isset($_SESSION["cw"]["loggedIn"]) && $_SESSION["cw"]["loggedIn"] == 1 && isset($_SESSION["cw"]["accessLevel"]) && ListFindNoCase('developer,merchant',$_SESSION["cw"]["accessLevel"]) && (!isset($_SESSION["cw"]["debug"]) || $_SESSION["cw"]["debug"] == false) && (!(isset($_GET['resetapplication']) && $_GET['resetapplication'] == $_ENV["application.cw"]["storePassword"]))) {
			$_GET['debug'] = true;
		} else {
			$_GET['debug'] = false;
		}
	// if debugging is turned off and we don't already have value set 
	}
	elseif($_ENV["application.cw"]["debugEnabled"] == false && (isset($_SESSION["cw"]["debug"]) && $_SESSION["cw"]["debug"] == false)) {
		$_SESSION["cw"]["debug"] == false;
	}
	// request.cw.now is timestamp with offset applied 
	$todayDate = date("Y-m-d g:i a");// current date
	$currentTime = time($todayDate); //Change date into time
	$_ENV["request.cw"]["now"] = $currentTime + (60*60*$_ENV["application.cw"]["globalTimeOffset"]); 
	// date mask 
	if (function_exists("CWlocaleDateMask")) {
		$_ENV["request.cw"]["localeDateMask"] = CWlocaleDateMask();
	} else {
		$_ENV["request.cw"]["localeDateMask"] = "yyyy-mm-dd";
	}
	// datepicker script only allows specific date masks 
	if (substr($_ENV["request.cw"]["localeDateMask"],0,1) == 'm' || substr($_ENV["request.cw"]["localeDateMask"],0,1) == 'n') {
		$_ENV["request.cw"]["scriptDateMask_JS"] = "mm/dd/yyyy";
		$_ENV["request.cw"]["scriptDateMask"] = "m/d/Y";
	}
	else if (substr($_ENV["request.cw"]["localeDateMask"],0,1) == 'd' || substr($_ENV["request.cw"]["localeDateMask"],0,1) == 'j') {
		$_ENV["request.cw"]["scriptDateMask_JS"] = "dd/mm/yyyy";
		$_ENV["request.cw"]["scriptDateMask"] = "d/m/Y";
	}
	else {
		$_ENV["request.cw"]["scriptDateMask_JS"] = "yyyy-mm-dd";
		$_ENV["request.cw"]["scriptDateMask"] = "Y-m-d";
	}
}
?>