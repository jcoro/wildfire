<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-nav.php
File Date: 2012-02-01
Description:
Creates and displays sample sidebar category navigation for cartweaver,
with option to include custom page links
Debugging link requires application.cw.debugDisplayLink = true in admin
and user logged into store admin while viewing display pages.
NOTE:
Add your own links, manually or as a list from a query.
Just prepend or append links using this format:
group(numeric)|url|linktext,
The first value, numeric group, can be in any order, no sequence required
See cw-mod-searchnav for all possible attributes
==========================================================
*/
// array of links for start of menu (optional) 
$topLinks = "";
$topLinks.= "1001|index.php|Home^
4|sample_search.php|Sample Pages^
4|sample_search.php|Search Examples^
4|sample_products.php|Product Configuration^
4|sample_specials.php|Specials &amp; Features^";
// array of links for end of menu (optional) 
$endLinks = "";
$endLinks.= "2001|cart.php|View Cart";
// debugging link ( if logged in to store admin
//and enabled in admin settings )
if($_ENV["application.cw"]["debugDisplayLink"]) {
	$endLinks.= "^2002|";
	$endLinks.= $_ENV["request.cw"]["thisPageQS"]."&debug=#".$_ENV["application.cw"]["debugPassword"];
	if(isset($_SESSION["cw"]["debug"]) && $_SESSION["cw"]["debug"] == true) {
		$endLinks.= "Disable" ;
	} else {
		$endLinks.= "Enable";
	}
	$endLinks.= "Debugging";
}
// custom handling of current link markers for cart pages 
$urlshowcart = explode('/',$_POST['urlShowCart']);
$urlshowcart_last = $urlshowcart[count($urlshowcart) - 1];
$urlcheckout = explode('/',$_POST['urlCheckout']);
$urlcheckout_last = $urlcheckout[count($urlcheckout) - 1];
$urlconfirmorder = explode('/',$_POST['urlConfirmOrder']);
$urlconfirmorder_last = $urlconfirmorder[count($urlconfirmorder) - 1];
$urldetails = explode('/',$_ENV["request.cwpage"]["urlDetails"]);
$urldetails_last = $urldetails[count($urldetails) - 1];
if($_ENV["request.cw"]["thisPage"] == $urlshowcart_last || $_ENV["request.cw"]["thisPage"] == $urlcheckout_last || $_ENV["request.cw"]["thisPage"] == $urlconfirmorder_last ) {
	 $currentURL = $_ENV["request.cw"]["thisPage"];
}
elseif($_ENV["request.cw"]["thisPage"] == $urldetails_last) {
	$currentURL = $_ENV["request.cwpage"]["urlResults"];
} else {
	$currentURL = $_ENV["request.cw"]["thisPageQS"];
}
if(isset($_ENV["request.cwpage"]["categoryID"]) && $_ENV["request.cwpage"]["categoryID"] > 0 && in_array('category=',$currentUrl)) {
	if(!in_array('?', $currentUrl)) {
		$currentUrl = $currentUrl . '?';
	}
	$currentUrl = trim($currentURL) . '&category='.$_ENV["request.cwpage"]["categoryID"];
}
if(isset($_ENV["request.cwpage"]["secondaryID"]) && $_ENV["request.cwpage"]["secondaryID"] > 0 && in_array('secondary=',$currentUrl)) {
	if(!in_array('?', $currentUrl)) {
		$currentUrl = $currentUrl . '?';
	}
	$currentUrl = trim($currentURL) . '&secondary='.$_ENV["request.cwpage"]["secondaryID"];
}
// search keywords 
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "form",
	"form_keywords" => true,
	"form_keywords_text" => "Search Site",
	"form_category" => false,
	"form_secondary" => false,
	"form_id" => "searchSidebar");
include("cw4/cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
// menu (using content from above) 
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "list",
	"show_empty" => false,
	"show_secondary" => true,
	"show_product_count" => true,
	"relate_cats" => $_ENV["application.cw"]["appEnableCatsRelated"],
	"prepend_links" => $topLinks,
	"appendlinks" => $endLinks,
	"current_url" => $currentURL);
	include("cw4/cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
// javascript / css menu code for page head 
$headcontent = "";
// menu javacript 
$headcontent.= "<script type=\"text/javascript\">
	jQuery(document).ready(function() {
	 // show/hide function for nested menu
	 jQuery('#leftCol ul.CWnav li ul').hide().parent('li').children('a').prepend('&raquo;&nbsp;').click(function() {
	 	jQuery(this).parent('li').children('ul').show(500);
	 	jQuery(this).parent('li').siblings('li').children('ul').hide(500);
	 	return false;
	 });
	 // trigger click to open menu to current page
	 jQuery('#leftCol ul.CWnav > li > ul > li > a').parents('#leftCol ul.CWnav > li ').children('a.currentLink').trigger('click').removeClass('currentLink');
	// manual highlighting for search keywords
	";
$urlresult = explode('/',$_ENV["request.cwpage"]["urlResults"]);
if ($_ENV["request.cw"]["thisPage"] == $urlresult[count($urlresult)-1] && isset($_GET['keywords'])) {
	// if cat or secondary defined 
	if ($_ENV["request.cwpage"]["categoryID"] > 0 || $_ENV["request.cwpage"]["secondaryID"] > 0) { 
		$headcontent .= "var matchLink = '".$urlresult[count($urlresult)-1]."?' ";
		if ($_ENV["request.cwpage"]["categoryID"] > 0) { 
			$headcontent .= " + 'category=". $_ENV["request.cwpage"]["categoryID"]."'";
			if ($_ENV["request.cwpage"]["secondaryID"] > 0) {
				$headcontent .= " + '&'";
			}
		} 
		if ($_ENV["request.cwpage"]["secondaryID"] > 0) {
			$headcontent .= " + 'secondary=". $_ENV["request.cwpage"]["secondaryID"]."'";
		}
		$headcontent.= ";
	";
	}
	$headcontent.= "jQuery('#leftCol ul > li > a[href=\"' + matchLink + '\"]').addClass('currentLink').parents('li').parents('ul').siblings('a').trigger('click').removeClass('currentLink');";
	// if no cat, highlight 'all products' ";
} else { 
	$headcontent .= "jQuery('#leftCol ul > li > a[href=\"". $_ENV["request.cwpage"]["urlResults"]. "\"]').addClass('currentLink');";
}
$headcontent.= "
});
</script>";
echo $headcontent;
?>