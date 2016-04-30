<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-config.php
File Date: 2012-04-29
Description:
Handles Datasource Settings for Application Global Connection
==========================================================
*/
// //////////// 
// SET THESE VALUES FOR YOUR SITE 
// //////////// 
// the location of your database mysql.wildfirehealth.com
$_ENV["request.cwapp"]["db_hostname"] = '';
// the username for your DSN (can be blank if not required by your server) 
$_ENV["request.cwapp"]["db_user"] = '';
// the password for your DSN (can be blank if not required by your server) 
$_ENV["request.cwapp"]["db_password"] = '';
// the name for your cartweaver database 
// NOTE: this must be unique for your Cartweaver site 
$_ENV["request.cwapp"]["db_databasename"] = '';
if (!isset($_ENV["NODBLINK"]) || !$_ENV["NODBLINK"]) {
	$_ENV["request.cwapp"]["db_link"] = @mysql_connect($_ENV["request.cwapp"]["db_hostname"], $_ENV["request.cwapp"]["db_user"], $_ENV["request.cwapp"]["db_password"], true);
	if (!$_ENV["request.cwapp"]["db_link"]) {
		if (!isset($_ENV["request.cwpage"]["userAlert"])) {
			$_ENV["request.cwpage"]["userAlert"] = array();
		}
		$_ENV["request.cwpage"]["userAlert"][] = trim(str_replace("\\\"", "\"", str_replace("\\'", "'", "Could not connect: " . mysql_error() . "<br />Please check your settings in the cw4/cwconfig/cw-config.php file and create a corresponding database to continue.")));
	} else {
		mysql_select_db($_ENV["request.cwapp"]["db_databasename"]);
		if(!isset($_ENV["application.cw"]["dbok"]) || !$_ENV["application.cw"]["dbok"]) {
			try {
				$checkDB ="SELECT config_value FROM cw_config_items WHERE config_variable = 'appVersionNumber'";
				if (!function_exists("CWpageMessage")) {
					$myDir = getcwd();
					chdir(dirname(__FILE__));
					// global functions 
					require_once("../cwapp/func/cw-func-global.php");
					chdir($myDir);
				}
				$result = mysql_query($checkDB,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage("Datasource Unavailable");
				if ($result !== false) {
					if(mysql_num_rows($result)) { $_ENV["application.cw"]["dbok"] = true; } else { CWpageMessage('Datasource Unavailable'); }
				}
			}
			catch(Exception $e) {
				$_ENV["request.cwpage"]["userAlert"][] = 'Datasource Unavailable' ; 	
			}
		}
		if ($_ENV["application.cw"]["dbok"] && !$_ENV["request.cwapp"]["db_link"]) {
			unset($_ENV["application.cw"]["dbok"]);
		}
	}
}
// Cartweaver Version: used for support/update purposes updates version in database if changed here 
$_ENV["request.cw"]["versionNumber"] = '4.01.02';
?>