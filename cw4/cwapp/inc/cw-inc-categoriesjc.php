<?php
// STORE NAVIGATION 
if ($_ENV["request.cw"]["thisPage"] == $_ENV["application.cw"]["appPageDetails"] || $_ENV["request.cw"]["thisPage"] == $_ENV["application.cw"]["appPageResults"]){
	// category navigation 
	$module_settings = array(
		"search_type" =>'links',
		"show_empty" => true,
		"show_secondary" => true,
		"show_product_count" => false,
		"relate_cats" => $_ENV["application.cw"]["appEnableCatsRelated"],
		"all_categories_label" => "",
		"all_secondaries_label" => "");
	include("../mod/cw-mod-searchnav.php");
	unset($module_settings);
}
chdir($myDir);
?>