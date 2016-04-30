<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-productpaging.php
File Date: 2012-02-01
Description:
Shows number of products found and pagination links for product listings
==========================================================
*/
// clean up form and url variables 
$myDir = getcwd();
chdir(dirname(__FILE__));
include("../inc/cw-inc-sanitize.php");
chdir($myDir);
if(!isset($_ENV["request.cwpage"]["resultsMaxRows"])) { $_ENV["request.cwpage"]["resultsMaxRows"] = $_ENV["application.cw"]["appDisplayPerPage"]; }
if(!isset($module_settings["paging_var"])) { $module_settings["paging_var"]="page";}
if(!isset($_GET[$module_settings["paging_var"]])) {$_GET[$module_settings["paging_var"]] == 1;}
if(!isset($module_settings["current_page"])) { $module_settings["current_page"] = $_GET[$module_settings["paging_var"]];}
if(!isset($module_settings["base_url"])) {$module_settings["base_url"]=$_SERVER['SCRIPT_NAME'];}
if(!isset($module_settings["show_all"])) {$module_settings["show_all"]=false;}
if(!isset($module_settings["results_per_page"])) {$module_settings["results_per_page"] = $_ENV["request.cwpage"]["resultsMaxRows"];}
if(!isset($module_settings["total_records"])) {$module_settings["total_records"]=0;}
if(!isset($module_settings["show_page_numbers"])) {$module_settings["show_page_numbers"]=true;}
if(!isset($module_settings["max_links"])) {$module_settings["max_links"]=$_ENV["application.cw"]["appDisplayPageLinksMax"];}
// leave these blank to omit 
if(!isset($module_settings["all_link_text"])) {$module_settings["all_link_text"] ="(Show All)";}
if(!isset($module_settings["all_page_text"])) {$module_settings["all_page_text"] = "(Show ".$module_settings["results_per_page"]." per page)";}
if(!isset($module_settings["prev_link_text"])) {$module_settings["prev_link_text"] ="&laquo;&nbsp;Previous";}
if(!isset($module_settings["next_link_text"])) {$module_settings["next_link_text"] ="Next&nbsp;&raquo;";}
if(!isset($module_settings["link_delimiter"])) { $module_settings["link_delimiter"] = " | ";}
if(!isset($module_settings["before_current_page"])) { $module_settings["before_current_page"] ="<strong>";}
if(!isset($module_settings["after_current_page"])) { $module_settings["after_current_page"] ="</strong>";}
if(strpos($module_settings["base_url"], "?") === false) {
	$module_settings["base_url"] .= '?';
}
else if(strpos($module_settings["base_url"], "=") !== false && substr($module_settings["base_url"],strlen($module_settings["base_url"])-1) != '&') {
	$module_settings["base_url"] .= '&';
}
$totalPages = ceil($module_settings["total_records"]/$module_settings["results_per_page"]);
?>
<div class="CWproductPaging">
<?php // number of products ?>
 <p class="CWsearchCount">[<strong> <?php echo $module_settings["total_records"];?> </strong> ] Item<?php if($module_settings["total_records"] != 1) { echo "s";} else { echo "&nbsp;"; } ?></p>
<?php
// paging links: only show if more than 1 page of products being shown 
if ($totalPages > 1 || $module_settings["show_all"]) {
?>
		<p class="CWpagingLinks">
<?php
	// if not showing all, show links 
	if (!$module_settings["show_all"]) {
		// display "Previous" link 
		if (strlen(trim($module_settings["prev_link_text"]))) {
?>
			<span class="CWpagingPrev">
<?php
			// display Page Numbers 
			if ($module_settings["current_page"] > 1) {
?>
					<a href="<?php echo $module_settings["base_url"].$module_settings["paging_var"]; ?>=<?php echo max(array($module_settings["current_page"]-1, 1)); ?>"><?php echo trim($module_settings["prev_link_text"]); ?></a><?php echo $module_settings["link_delimiter"]; ?>
<?php
			} else {
				echo trim($module_settings["prev_link_text"]);
			}
?>
			</span>
<?php
		}
		// display Page Numbers 
		if($module_settings["show_page_numbers"]) {
			// centerpage (1/2 of total links shown) 
			$centerPage = ceil(($module_settings["max_links"] + 1) /2);
			// start page 
			if($module_settings["current_page"] < $module_settings["max_links"]) {
				$startPage = 1;
			} else {
				$startPage = max(array(1, ($module_settings["current_page"] - $module_settings["max_links"] + $centerPage)));
			}
			// end page 
			$endPage = min(array(($startPage + $module_settings["max_links"] - 1),$totalPages));
?>
				<span class="CWpagingNumbers">
<?php
			// loop through links to provided numbered navigation 
			for($pageNum=$startPage; $pageNum<=$endPage; $pageNum++) {
				if($pageNum == $module_settings["current_page"]) {
					echo $module_settings["before_current_page"].$pageNum.$module_settings["after_current_page"];
				} else {
?>
						<a href="<?php echo $module_settings["base_url"].$module_settings["paging_var"]; ?>=<?php echo $pageNum; ?>"><?php echo $pageNum; ?></a>
<?php
				}
				if($pageNum != $totalPages || ($pageNum == $totalPages && strlen(trim($module_settings["next_link_text"])))) {
					echo $module_settings["link_delimiter"];
				}
			}
?>
				</span>
<?php
		}
		// display "Next" link 
		if(strlen(trim($module_settings["next_link_text"]))) {
?>
			<span class="CWpagingNext">
<?php
			if($module_settings["current_page"] < $totalPages) {
?>
					<a href="<?php echo $module_settings["base_url"].$module_settings["paging_var"]; ?>=<?php echo min(array(($module_settings["current_page"]+1),$totalPages)); ?>"><?php echo trim($module_settings["next_link_text"]); ?></a>
<?php
			} else {
				trim($module_settings["next_link_text"]);
			}
?>
			</span>
<?php
		}
		// display "All" link 
		if(strlen(trim($module_settings["all_link_text"]))) {
?>
					<a class="CWpagingAll" href="<?php echo $module_settings["base_url"]; ?>showall=1"><?php echo trim($module_settings["all_link_text"]); ?></a>
<?php
		}
	// if showing all 
	} else {
		// display "Paging" link 
		if(strlen(trim($module_settings["all_page_text"]))) {
?>
					<a class="CWpagingAll" href="<?php echo $module_settings["base_url"]; ?>showall=0"><?php echo trim($module_settings["all_page_text"]); ?></a>
<?php
		}
	// /end if show all 
	}
?>
		</p>
<?php
}
?>
</div>