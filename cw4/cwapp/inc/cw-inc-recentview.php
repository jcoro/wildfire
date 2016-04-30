<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-recentview.php
File Date: 2012-02-01
Description:
Shows recently viewed products based on list in client scope
==========================================================
*/
// default for output 
$recentLoopCt = 0;
if(!(isset($_ENV["application.cw"]["appDisplayProductViews"]))) $_ENV["application.cw"]["appDisplayProductViews"] = 0;
// current page defaults 
if(!(isset($_GET['product']))) $_GET['product'] = 0; 
if(!(isset($_ENV["request.cwpage"]["productID"]))) $_ENV["request.cwpage"]["productID"] = $_GET['product']; 
// product function required for productAvailable lookup 
$myDir = getcwd();
chdir(dirname(__FILE__));
require_once("cw-inc-functions.php");
chdir($myDir);
if(isset($_ENV["application.cw"]["appDisplayProductViews"]) && $_ENV["application.cw"]["appDisplayProductViews"] > 0 && isset($_SESSION["cwclient"]["cwProdViews"]) && strlen(trim($_SESSION["cwclient"]["cwProdViews"])) && trim($_SESSION["cwclient"]["cwProdViews"]) != $_ENV["request.cwpage"]["productID"]) {
?>		
		<div class="CWproductRecentProducts CWcontent">
			<h3>Recently Viewed:</h3>
<?php
	$pvArr = $_SESSION["cwclient"]["cwProdViews"];
	if (!is_array($pvArr) && strlen(trim($pvArr))) $pvArr = explode(",", $pvArr);
	else if (!is_array($pvArr)) $pvArr = array();
	foreach ($pvArr as $key => $pp) {
		// don't show current product 
		if($pp != $_ENV["request.cwpage"]["productID"] && $recentLoopCt < $_ENV["application.cw"]["appDisplayProductViews"]) {
			if(CWproductAvailable($pp)) {
				// show the product include 
				$myDir = getcwd();
				chdir(dirname(__FILE__));
				$module_settings = array(
					"product_id" => $pp,
					"add_to_cart" => false,
					"show_description" => false,
					"show_image" => true,
					"show_price" => false,
					"image_class" => "CWimgRecent",
					"image_position" => "above",
					"title_position" => "above",
					"details_link_text" => "&raquo; details");
				include("../mod/cw-mod-productpreview.php");
				unset($module_settings);
				chdir($myDir);
				$recentLoopCt++;
			}
		}
	}
?>
			<div class="CWclear"></div>
		</div>
<?php
}
?>