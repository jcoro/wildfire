<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-app-cfcstart.php
File Date: 2012-02-28
Description:
Global application settings
==========================================================
*/
// application name - build dynamically from server/dsn combo 
session_cache_expire(30);
session_start();
if (function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get")) {
	$timezone = @date_default_timezone_get();
	if (!$timezone) $timezone = "UTC";
	@date_default_timezone_set($timezone);
}
mysql_query("SET NAMES 'utf8'");
?>