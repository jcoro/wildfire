<?php
/*<!---
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
--->*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// include the mail functions 
require_once("../cwapp/func/cw-func-mail.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("any");
// PAGE PARAMS 
if (!isset($_GET["orderid"])) $_GET["orderid"] = "";
// build sample email 
$sampleContent = "".$_ENV["application.cw"]["mailDefaultOrderReceivedIntro"]."

[SAMPLE ORDER CONTENTS - DEMO ONLY]
Order ID: 1111161122-FC4E

Ship To
====================
Wanda Buymore
1234 st
some town, Alabama
99999 United States

Order Contents
====================
Digital Point & Shoot Camera (DigitalPoint-n-Shoot-Blue)
Color: Blue
Quantity: 1
Price: $125.00
Item Total: $125.00


LawnPower Ride Lawn Mower (SKU: LawnPower Riding-6y)
Color: Yellow
HP: 6 HP
Quantity: 1
Price: $899.00
Item Total: $899.00


Order Totals
====================
Subtotal: $1,024.00
Shipping (UPS Ground): $17.21

ORDER TOTAL: $1,041.21

".$_ENV["application.cw"]["mailDefaultOrderReceivedEnd"]."";
$sampleSubject = "Email Message Subject Shown Here";
$mailContent = CWmailContents($sampleContent,$sampleSubject);
// show contents as HTML page 
echo $mailContent["messageHtml"];
?>