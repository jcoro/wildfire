<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-alerts.php
File Date: 2012-02-01
Description: Handles customer alerts for store pages, based on URL and/or request vars
NOTE: important messages can be persisted through session.cw.userAlert or session.cw.userConfirm
========================================================== 
*/

if(!(function_exists('CWtime'))) {
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	require_once("../func/cw-func-global.php");
	chdir($myDir);
}
// alert for application reset 
if(isset($_REQUEST['cwpageReset']) && $_REQUEST['cwpageReset'] && isset($_SESSION["cw"]["loggedIn"]) && $_SESSION["cw"]["loggedIn"] == 1 && isset($_SESSION["cw"]["accessLevel"]) && ListFindNoCase('developer,merchant',$_SESSION["cw"]["accessLevel"])) {
  CWpageMessage('confirm','Application Reset Complete');
}
// Handle any session alerts 
if(isset($_SESSION["cw"]['userAlert']) && strlen(trim($_SESSION["cw"]['userAlert']))) {
	CWpageMessage('alert',$_SESSION["cw"]['userAlert']);
	$_SESSION["cw"]['userAlert'] = '';
}
if(isset($_SESSION["cw"]['userConfirm']) && strlen(trim($_SESSION["cw"]['userConfirm']))) {
	 CWpageMessage('confirm',$_SESSION["cw"]['userConfirm']);
	 $_SESSION["cw"]['userConfirm'] = '';
}
// Handle any URL alerts 
if(isset($_GET['userAlert']) && strlen(trim($_GET['userAlert']))) {
	 CWpageMessage('alert',$_GET['userAlert']);
}
if(isset($_GET['userConfirm']) && strlen(trim($_GET['userConfirm']))) {
	 CWpageMessage('confirm',$_GET['userConfirm']);
}
// force alert to array from string 
if(isset($_ENV["request.cwpage"]["userAlert"]) && !is_array($_ENV["request.cwpage"]["userAlert"])) {
	$origStrTemp = trim($_ENV["request.cwpage"]["userAlert"]);
	$_ENV["request.cwpage"]["userAlert"] = array();
	array_push($_ENV["request.cwpage"]["userAlert"],$origStrTemp);
}
else if(!isset($_ENV["request.cwpage"]["userAlert"])) {
	  $_ENV["request.cwpage"]["userAlert"] = array();
	}
// force confirmation to array from string 
if(isset($_ENV["request.cwpage"]["userConfirm"]) && !is_array($_ENV["request.cwpage"]["userConfirm"])) {
	$origStrTemp = trim($_ENV["request.cwpage"]["userConfirm"]);
	$_ENV["request.cwpage"]["userConfirm"] = array();
	array_push($_ENV["request.cwpage"]["userConfirm"],$origStrTemp);
}
else if(!isset($_ENV["request.cwpage"]["userConfirm"])) {
	$_ENV["request.cwpage"]["userConfirm"] = array();
}
// loop the  alert arrays, creating output 
$_ENV["request.cwpage"]['displayAlert'] = "";
for ($aa = 0; $aa < count($_ENV["request.cwpage"]["userAlert"]); $aa++) {
  	if (strlen(trim($_ENV["request.cwpage"]["userAlert"][$aa]))) {
		$_ENV["request.cwpage"]['displayAlert'].= '<div class="alertText">';
		$_ENV["request.cwpage"]['displayAlert'].= str_replace('<br/>','',$_ENV["request.cwpage"]["userAlert"][$aa]);
		$_ENV["request.cwpage"]['displayAlert'].= '</div>';
	} 
}
for ($cc = 0; $cc < count($_ENV["request.cwpage"]["userConfirm"]); $aa++) {
  	if (strlen(trim($_ENV["request.cwpage"]["userConfirm"][$cc]))) { 
		$_ENV["request.cwpage"]['displayAlert'].= '<div class="confirmText">';
		$_ENV["request.cwpage"]['displayAlert'].= str_replace('<br/>','',$_ENV["request.cwpage"]["userConfirm"][$cc]);
		$_ENV["request.cwpage"]['displayAlert'].= '</div>';
	}
}
// if we have an alert, scroll page to top so it is shown 
if(strlen(trim($_ENV["request.cwpage"]['displayAlert']))) {
?>		
	<script type="text/javascript">
	jQuery(document).ready(function() {
	// scroll to top if showing alerts
		jQuery("html").scrollTop(0);
	});
	</script>
<?php 
} 
?>
<!-- user alert - message shown to user
NOTE: keep on one line for cross-browser script support -->
<div id="CWuserAlert" class="fadeOut CWcontent"
<?php if(!strlen(trim($_ENV["request.cwpage"]['displayAlert']))) {
?>	
style="display:none;"
<?php	
}
?> >
<?php
echo trim($_ENV["request.cwpage"]['displayAlert']);
?>
</div>