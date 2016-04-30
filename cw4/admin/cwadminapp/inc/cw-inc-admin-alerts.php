<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-alerts.php
File Date: 2012-02-01
Description: Handles alerts for admin pages, based on URL and/or request vars
NOTE: important messages can be persisted through session.cw.useralert or session.cw.userconfirm
==========================================================
*/
// alert for default email 
if((isset($_ENV["application.cw"]["appTestModeEnabled"])) && !$_ENV["application.cw"]["appTestModeEnabled"] == true ) {
	if(isset($_ENV["application.cw"]["companyEmail"]) && strstr($_ENV["application.cw"]["companyEmail"], "@cartweaver") !== false) {
		CWpageMessage('alert','Please change your <a href="config-settings.php?group_id=3">company email</a> It is currently set to the Cartweaver default value.');
	}
}
// alert for application reset 
if((isset($_POST['cwpageReset'])) && $_POST['cwpageReset']) {
	CWpageMessage('confirm','Application Reset Complete');
}	
// Handle any session alerts 
if((isset($_SESSION["cw"]["userAlert"])) && strlen(trim($_SESSION["cw"]["userAlert"]))) {
	CWpageMessage('alert',$_SESSION["cw"]["userAlert"]);
}
if((isset($_SESSION["cw"]["userConfirm"])) && strlen(trim($_SESSION["cw"]["userConfirm"]))) {
	CWpageMessage('confirm',$_SESSION["cw"]["userConfirm"]);
}
// Handle any URL alerts 
if((isset($_GET['useralert']))) {
	CWpageMessage('alert',$_GET['useralert']);
}
if((isset($_GET['userconfirm']))) {
	CWpageMessage('confirm',$_GET['userconfirm']);
}
// force alert to array from string 
if(isset($_ENV["request.cwpage"]["userAlert"]) && !is_array($_ENV["request.cwpage"]["userAlert"])) {
	$origStrTemp = trim($_ENV["request.cwpage"]["userAlert"]);
	$_ENV["request.cwpage"]["userAlert"] = array();
	$_ENV["request.cwpage"]["userAlert"][] = $origStrTemp;
} else if(!isset($_ENV["request.cwpage"]["userAlert"])) {
	$_ENV["request.cwpage"]["userAlert"] = array();
}
// force alert to array from string 
if((isset($_ENV["request.cwpage"]["userConfirm"])) && !is_array($_ENV["request.cwpage"]["userConfirm"])) {
	$origStrTemp = trim($_ENV["request.cwpage"]["userConfirm"]);
	$_ENV["request.cwpage"]["userConfirm"] = array();
	$_ENV["request.cwpage"]["userConfirm"][] = $origStrTemp;
} else if(!isset($_ENV["request.cwpage"]["userConfirm"])) {
	$_ENV["request.cwpage"]["userConfirm"] = array();
}
$_ENV["request.cwpage"]["displayAlert"] = "";
// force confirmation to array from string 
// loop the  alert arrays, creating output 
if(isset($_ENV["request.cwpage"]["userAlert"]) && count($_ENV["request.cwpage"]["userAlert"])) {
	for($aa=0; $aa < count($_ENV["request.cwpage"]["userAlert"]); $aa++) {
		if(strlen(trim($_ENV["request.cwpage"]["userAlert"][$aa]))) {
			 $_ENV["request.cwpage"]["displayAlert"] .= '<div>'.trim($_ENV["request.cwpage"]["userAlert"][$aa]).'</div>'; 
		}
	}
}
if(isset($_ENV["request.cwpage"]["userConfirm"]) && count($_ENV["request.cwpage"]["userConfirm"])) {
	for($cc=0; $cc < count($_ENV["request.cwpage"]["userConfirm"]); $cc++) {
		if(strlen(trim($_ENV["request.cwpage"]["userConfirm"][$cc]))) { 
			 $_ENV["request.cwpage"]["displayAlert"] .= '<div class="confirm">'.trim($_ENV["request.cwpage"]["userConfirm"][$cc]).'</div>';
		}
	}
}

// if we have an alert, scroll page to top so it is shown 
if(strlen(trim($_ENV["request.cwpage"]["displayAlert"]))) {
?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
			// scroll to top if showing alerts
				jQuery("html").scrollTop(0);
			});
			</script>
<?php
}
// link to close the alert area 
$closeAlertLink = '<a href="#" id="closeAlertLink"><img src="img/cw-close-window.png" alt="Hide Alert"></a>';
?>
<!-- admin alert - message shown to user
NOTE: keep on one line for cross-browser script support -->
<div id="CWadminAlert" class="alert" <?php if(!strlen(trim($_ENV["request.cwpage"]["displayAlert"]))) { ?>style="display:none;"<?php } ?>><?php echo $closeAlertLink . trim($_ENV["request.cwpage"]["displayAlert"]); ?></div>
<?php // if no javascript, show alert ?>
<noscript>
<div id="CWadminAlertNoScript" class="alert">
	Scripts disabled: enable browser JavaScript to use this application
</div>
</noscript>