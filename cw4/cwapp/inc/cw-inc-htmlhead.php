<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-htmlhead.php
File Date: 2012-02-01
Description: Inserts global scripts and assets into page head
via the "onRequestStart" method (see Application.php)
==========================================================
*/
$cwhtmlhead = "";
// jQuery library file - must be loaded in page head before any other jQuery 
$cwhtmlhead .= "<script type=\"text/javascript\" src=\"".$_ENV["request.cw"]["assetSrcDir"]."js/jquery-1.7.1.min.js\"></script>

";
// global scripts for Cartweaver 
ob_start();
include("cw-inc-scripts.php");
$cwhtmlhead .= ob_get_contents();
ob_end_clean();
// core css, handles layout and structure, imports theme/cw-theme.css
//	  (uncomment to apply globally for cw pages) 
/*
$cwhtmlhead .= "<link href=\"".$_ENV["request.cw"]["assetSrcDir"]."/css/cw-core.css\" rel=\"stylesheet\" type=\"text/css\">
";
*/
CWinsertHead($cwhtmlhead);
?>

