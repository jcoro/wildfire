<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: order-email.php
File Date: 2012-02-01
Description: Displays order email confirmation message contents
==========================================================
*/
require_once("Application.php");
// GLOBAL INCLUDES 
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// include the mail functions 
include('../cwapp/func/cw-func-mail.php');
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("any");
// PAGE PARAMS 
if(!isset($_GET['order_id'])) { $_GET['order_id'] = ""; }
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Order Details Email Content";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Order Details&nbsp;&nbsp;&nbsp;
<span class='subHead'>ID: '".$_GET['order_id']."'&nbsp;&nbsp;&nbsp;</span>";
// START OUTPUT ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
	</head>
<?php
// body gets a class to match the filename 
$page = explode('.',$_ENV["request.cw"]["thisPage"]);
$page_First = $page[0];
?>
	<body <?php echo 'class="'.$page_First.'"'; ?>>
		<!-- inside div to provide padding -->
		<div class="CWinner">
			<h2 style="text-align:center"><?php echo $_ENV["request.cwpage"]["heading1"]; ?></h2>
<?php 
			// get the order email contents 
			$mailContent = CWtextOrderDetails($_GET['order_id']);
			// show in text area to retain formatting ?>
			<form>
                <textarea rows="24" cols="64">
<?php 			echo $_ENV["application.cw"]["mailDefaultOrderShippedIntro"].chr(10).chr(13).chr(10).chr(13).$mailContent.chr(10).chr(13).chr(10).chr(13).$_ENV["application.cw"]["mailDefaultOrderShippedEnd"]; ?>                
                </textarea>
			</form>
		</div>
		<!-- /end CWinner -->
		<div class="clear"></div>
		<!-- /end CWadminPage-->
		<div class="clear"></div>
	</body>
</html>