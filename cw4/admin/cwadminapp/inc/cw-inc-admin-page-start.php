<?php 
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-page-start.php
File Date: 2012-02-01
Description: Admin dashboard controls and other global page start elements
==========================================================
*/
// DEBUGGING 
if((isset($_GET['debug'])) && isset($_SESSION["cw"]["accessLevel"]) && strpos($_SESSION["cw"]["accessLevel"], 'developer') !== false && $_ENV["application.cw"]["debugEnabled"] == 'true') {
	if($_GET['debug'] == $_ENV["application.cw"]["storePassword"] && ($_SESSION["cw"]["debug"] !== true) && !(isset($_GET['resetapplication']) && $_GET['resetapplication'] == $_ENV["application.cw"]["storePassword"])) {
		echo "|2";
		$_SESSION["cw"]["debug"] = true;
	}
	$varsToKeep = CWremoveUrlVars('debug,resetapplication,userconfirm,useralert'); 
	$_POST['redirectURL'] = CWserializeURL($varsToKeep,$_SERVER['SCRIPT_NAME']);
	header("Location: ".$_POST['redirectURL'].'#debug-top');
	exit;
}
// view site link - default cw store root location if site http url not provided 
if (!(isset($_ENV["request.cwpage"]["viewSiteURL"]) && strlen(trim($_ENV["request.cwpage"]["viewSiteURL"])) > 1)) {
	$_ENV["request.cwpage"]["viewSiteURL"] = "../../";
	$_ENV["application.cw"]["adminProductLinksEnabled"] = false;
}
// custom view site link for product page 
$pagelasthvalue=explode('/',$_ENV["request.cw"]["thisPage"]);
if ($_ENV["application.cw"]["adminProductLinksEnabled"] && in_array('product-details.php',$pagelasthvalue) && isset($_GET['productid'])) {
	$_ENV["request.cwpage"]["viewSiteText"] = "View Product"; 
	$_ENV["request.cwpage"]["viewSiteURL"] = $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["request.cwpage"]["urlDetails"]."?product=".$_GET['productid'] ; 
}
// strip out debug 
$resetVarsToKeep = CWremoveUrlVars("debug,userconfirm,useralert,resetapplication");
$_ENV["request.cw"]["baseString"] = CWserializeURL($resetVarsToKeep, $_ENV["request.cw"]["thisPage"]);
$_ENV["request.cw"]["baseDebugLink"] = $_ENV["request.cw"]["baseString"]. '&debug=' . $_ENV["application.cw"]["storePassword"];
$_ENV["request.cw"]["baseResetLink"] = $_ENV["request.cw"]["baseString"]. '&resetapplication=' . $_ENV["application.cw"]["storePassword"];
// debug link url 
$un_ar = array('merchant','developer');
if((isset($_SESSION["cw"]["accessLevel"])) && in_array($_SESSION["cw"]["accessLevel"],$un_ar) && $_ENV["application.cw"]["debugEnabled"] == 'true') {
	// if debug is on, add reset control to debugging link (turn debugging back off to reset application) 
	if((isset($_SESSION["cw"]["debug"])) && $_SESSION["cw"]["debug"] == 'true') {
		$_ENV["request.cw"]["baseDebugLink"] = $_ENV["request.cw"]["baseDebugLink"].'&resetapplication='. $_ENV["application.cw"]["storePassword"] ;  
	}
}
// help link url 
if ($_ENV["request.cw"]["thisPage"] == 'config-settings.php' && isset($_GET["group_id"]) && $_GET["group_id"] > 0) {
	// for config settings, use the group name 
	$_ENV["request.cwpage"]["helpFileName"] = strtolower(str_replace(" ", "-", CWgetConfigGroupName($_GET["group_id"])));
} else {
	// other pages, use the page name 
	$tpArr = explode(".", $_ENV["request.cw"]["thisPage"]);
	$_ENV["request.cwpage"]["helpFileName"] = strtolower($tpArr[0]);
}
// remove non-alpha chars 
$_ENV["request.cwpage"]["helpFileName"] = preg_replace("/[\s]/", "-", $_ENV["request.cwpage"]["helpFileName"]);
$_ENV["request.cwpage"]["helpFileName"] = preg_replace("/[^a-zA-Z-]/", "", $_ENV["request.cwpage"]["helpFileName"]);
?>
<!-- admin dashboard controls/search -->
<div id="CWadminDashboard">
	<!-- admin help icon/link -->
	<div id="CWadminHelp">
		<a href="http://help.cartweaver.com/index.cfm?pagename=<?php echo $_ENV["request.cwpage"]["helpFileName"]; ?>" class="zoomHelp" rel="external" title="Help for this page">
		<img src="img/cw-help.png" alt="Help for this page" width="16" height="16" align="absmiddle">
		</a>
	</div>
	<!-- logged in as -->

    
   
	<span id="loggedInAs"> Logged in as <?php echo '<em>'.$_SESSION["cw"]["loggedUser"].'</em>'; ?></span><!-- log out -->
	<a id="logoutLink" href="<?php echo $_SERVER['SCRIPT_NAME'];?>?logout=1">Log Out</a>

	<!-- view site -->
	<?php if(isset($_ENV["request.cwpage"]["viewSiteURL"]) && strlen(trim($_ENV["request.cwpage"]["viewSiteURL"])) && isset($_ENV["request.cwpage"]["viewSiteText"]) && strlen(trim($_ENV["request.cwpage"]["viewSiteText"]))) { ?>
			<a id="viewSiteLink" href="<?php echo $_ENV["request.cwpage"]["viewSiteURL"]; ?>" rel="external"><?php echo $_ENV["request.cwpage"]["viewSiteText"]; ?></a>
<?php	} ?>

	<!-- reset / debugging -->
    
<?php $use_ar = array('developer');
	if((isset($_SESSION["cw"]["accessLevel"])) && in_array($_SESSION["cw"]["accessLevel"],$use_ar)) { ?>
			<a id="resetLink" href="<?php echo $_ENV["request.cw"]["baseResetLink"]; ?>">Reset</a>
			<?php if($_ENV["application.cw"]["debugEnabled"] == 'true') { ?>
						<a id="debugLink" href="<?php echo $_ENV["request.cw"]["baseDebugLink"]; ?>">Turn <?php if($_SESSION["cw"]["debug"]) { ?>Off<?php } else { ?>On <?php } ?> Debugging </a>
			<?php 	}
			
		}
?>
</div>

</div>