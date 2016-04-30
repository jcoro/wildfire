<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: download.php
File Date: 2012-04-01
Description: delivers product as downloadable file, handles
authentication and access messages
==========================================================
*/
?><?php
// GLOBAL INCLUDES 
require_once("Application.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Download | <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="Description" content="">
		<?php // CARTWEAVER CSS ?>
		<link href="cw4/css/cw-core.css" rel="stylesheet" type="text/css">
	</head>
	<body class="cw">
<?php
// cart links, log in links, alerts 
include("cw4/cwapp/inc/cw-inc-pagestart.php");
?>
<?php
// CARTWEAVER INCLUDE: product details 
include("cw4/cw-download.php");
?>
	</body>
</html>