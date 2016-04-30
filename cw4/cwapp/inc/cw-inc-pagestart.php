<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-incpagestart.php
File Date: 2012-02-01
Description: Manages global content at the start of each page
==========================================================
*/
?>
<div class="CWcontent">
<?php
$myDir = getcwd();
chdir(dirname(__FILE__));
// page alerts 
include("cw-inc-alerts.php");
// cart links 
include("cw-inc-cartlinks.php");

?>
</div>