<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: session-timer.php
File Date: 2012-03-21
Description: returns number of minutes since user logged in
Related javascript is in cw-inc-admin-scripts.php
==========================================================
*/
require_once("Application.php");
if(!(isset($_GET['renew']))) $_GET['renew']=0;
if(!(isset($_SESSION["cw"]["lastRequest"]))) $_SESSION["cw"]["lastRequest"]=mktime(0, 0, 0, date("m"), date("d"), date("y")+1);
// the ping script uses a url variable. if not included, set the request time 
if($_GET['renew'] == 0) $_SESSION["cw"]["lastRequest"] = date();
$_SESSION["cw"]["minutesIdle"] = (date() - $_SESSION["cw"]["lastRequest"])/60;
// output the time idle 
echo $_SESSION["cw"]["minutesIdle"];
?>