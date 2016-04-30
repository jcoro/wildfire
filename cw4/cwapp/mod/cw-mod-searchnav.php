<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-searchnav.php
File Date: 2012-07-03
Description: Shows navigation menu or search form for product catalog
NOTES:
Searchtype options include:
- List: shows nested <ul><li> markup, used for navigation menus
- Links: shows rows of <a> links, used for store navigation links
- Form: shows search form, which can be submitted to search products on results page
- Breadcrumb: creates breadcrumb style navigation
*See cw-sampleproducts.php for examples of usage
and various attributes for each display type
==========================================================
*/
// default url variables for search selections 
if(!isset($_GET['keywords'])) { $_GET['keywords'] = ""; }
if(!isset($_GET['category'])) { $_GET['category'] = 0; }
if(!isset($_GET['category'])) { $_GET['category'] = 0; }
if(!isset($_GET['secondary'])) { $_GET['secondary'] = 0; }
if(!isset($_GET['product'])) { $_GET['product'] = 0; }
// determines the type of search or navigation to display
if (!isset($module_settings)) $module_settings = array();
//List | Links | Form | Breadcrumb 
if(!isset($module_settings["search_type"])) { $module_settings["search_type"] = "Links"; }
// form action / menu target page (default = product listings main page)
if(!isset($module_settings["action_page"])) { $module_settings["action_page"] = $_ENV["request.cwpage"]["urlResults"]; }
// show secondary categories 
if(!isset($module_settings["show_empty"])) { $module_settings["show_empty"] = $_ENV["application.cw"]["appDisplayEmptyCategories"]; }
// show secondary categories 
if(!isset($module_settings["show_secondary"])) { $module_settings["show_secondary"] = true; }
// relate secondary categories to primary cats 
if(!isset($module_settings["relate_cats"])) { $module_settings["relate_cats"] = $_ENV["application.cw"]["appEnableCatsRelated"]; }
// for horizontal links, the separator is placed between all category links 
if(!isset($module_settings["separator"])) { $module_settings["separator"] = " | "; }
// link to show as current (blank = CW automated) 
if(!isset($module_settings["current_url"])) { $module_settings["current_url"] = $_ENV["request.cw"]["thisPageQS"]; }
// text for 'all items' link (blank = not shown) 
if(!isset($module_settings["all_categories_label"])) { $module_settings["all_categories_label"] = "All Products"; }
// label for all secondary categories link (blank = not shown )
if(!isset($module_settings["all_secondaries_label"])) { $module_settings["all_secondaries_label"] = "All"; }
// label for all products link in breadcrumb nav (blank = not shown )
if(!isset($module_settings["all_products_label"])) { $module_settings["all_products_label"] = "All Items"; }
// text item tagged on to end of menu, overridden by search keywords 
if(!isset($module_settings["end_label"])) { $module_settings["end_label"] = ""; }
// show number of products in each category 
if(!isset($module_settings["show_product_count"])) { $module_settings["show_product_count"] = true; }
// for list or links type, formatted url/labels to insert before 
if(!isset($module_settings["prepend_links"])) { $module_settings["prepend_links"] = ""; }
// for list or links type, formatted url/labels to insert after 
if(!isset($module_settings["append_links"])) { $module_settings["append_links"] = ""; }
// id applied to menu <ul> 
if(!isset($module_settings["menu_id"])) { $module_settings["menu_id"] = ""; }
// class applied to menu <ul> 
if(!isset($module_settings["menu_class"])) { $module_settings["menu_class"] = ""; }
// Form Search Options
// form id applied to the search <form> tag 
if(!isset($module_settings["form_id"])) { $module_settings["form_id"] = "CWproductSearch"; }
// show form keyword field 
if(!isset($module_settings["form_keywords"])) { $module_settings["form_keywords"] = false; }
// default text entered in the keyword search field 
if(!isset($module_settings["form_keywords_text"])) { $module_settings["form_keywords_text"] = "Search Products"; }
// category list in form 
if(!isset($module_settings["form_category"])) { $module_settings["form_category"] = false; }
// text for the first entry in the category list 
if(!isset($module_settings["form_category_label"])) { $module_settings["form_category_label"] = "All ".$_ENV["application.cw"]["adminLabelCategories"]; }
// show secondary category list in form 
if(!isset($module_settings["form_secondary"])) { $module_settings["form_secondary"] = false; }
// text for the first entry in the secondary category list 
if(!isset($module_settings["form_secondary_label"])) { $module_settings["form_secondary_label"] = "All ".$_ENV["application.cw"]["adminLabelSecondaries"]; }
// text for the search button 
if(!isset($module_settings["form_button_label"])) { $module_settings["form_button_label"] = "Search"; }
// for breadcrumb type, insert leading nav elements
if(!isset($module_settings["prepend_breadcrumb"])) { $module_settings["prepend_breadcrumb"] = "<a href='".$_ENV["request.cwpage"]["urlResults"]."' class='CWlink'>Store</a>"; }
$myDir = getcwd();
chdir(dirname(__FILE__));
// global functions 
include("../inc/cw-inc-functions.php");
// clean up form and url variables 
include("../inc/cw-inc-sanitize.php");
chdir($myDir);
// if submitted, set the value for the keywordslabel to the submitted value 
if(strlen(trim($_GET['keywords']))) {
	$module_settings["form_keywords_text"] = CWcleanString($_GET['keywords']);
}
// If no value was supplied for the buttonlabel, then set it to Search 
if($module_settings["form_button_label"] == "") {
	$module_settings["form_button_label"] == "Search";
}
if(!isset($_ENV["request.cwpage"]["categoryID"])) { $_ENV["request.cwpage"]["categoryID"] = $_GET['category']; }
if(!isset($_ENV["request.cwpage"]["secondaryID"])) { $_ENV["request.cwpage"]["secondaryID"] = $_GET['secondary']; }
if(!isset($_ENV["request.cwpage"]["productID"])) { $_ENV["request.cwpage"]["productID"] = $_GET['product']; }
if(!isset($_ENV["request.cwpage"]["allCount"])) { $_ENV["request.cwpage"]["allCount"] = 0; }
// clean up &lt; and &gt; in visible attributes 
$findCharsList = array("&lt;","&gt;");
$replaceCharsList = array("<",">");
for ($i=0; $i<sizeof($findCharsList); $i++) { $module_settings["separator"] = str_replace($findCharsList[$i], $replaceCharsList[$i], $module_settings["separator"]); }
// flag for relating secondaries 
if($module_settings["relate_cats"] && (($module_settings["search_type"] == 'form' && $module_settings["form_secondary"]) || ($module_settings["search_type"] != 'form' && $module_settings["show_secondary"]))) {
	$module_settings["relate_cats"] = true;
}
// starting values for list functions 
$lastLinkcount = 0;
$selectedgroup = 0;
$isstarted = 0;
$firstParent = 1;
$firstChild = 1;
// QUERY: get categories 
$categoryQuery = CWquerySelectCategories($module_settings["show_empty"]);
if($module_settings["show_secondary"] || $module_settings["form_secondary"]) {
	// QUERY: get secondaries 
	$secondaryQuery = CWquerySelectSecondaries($module_settings["show_empty"], $module_settings["relate_cats"]);
}
// START OUTPUT
// ///  BEGIN SEARCH TYPE SELECTION  ///  
switch($module_settings["search_type"]) {
	// LIST - nested ul/li markup 
	case "list":
		$menuPages = '';
		if(strlen(trim($module_settings["prepend_links"]))) {
			$menuPages.= trim($module_settings["prepend_links"]);
			if(substr(trim($module_settings["prepend_links"]),strlen(trim($module_settings["prepend_links"]))-1) != "^") {
				$menuPages.= '^';
			}
		}
		// link for all categories 
		if(strlen(trim($module_settings["all_categories_label"]))) {
			$menuPages .= '0|'.$module_settings["action_page"].'|'.trim($module_settings["all_categories_label"]).'^';
		}
		for($i=0; $i<$categoryQuery['totalRows']; $i++) {
			$menuPages.= $categoryQuery["category_id"][$i].'|'.$module_settings["action_page"].'?category='.$categoryQuery["category_id"][$i].'|'.$categoryQuery["category_name"][$i];
			if($module_settings["show_product_count"]) {
				$menuPages.= " [".$categoryQuery['catProdCount'][$i]."]";
			}
			$menuPages.= '^';
			if($module_settings["show_secondary"]) {
				if(strlen(trim($module_settings["all_secondaries_label"]))) {
					$menuPages.= $categoryQuery["category_id"][$i].'|'.$module_settings["action_page"].'?category='.$categoryQuery["category_id"][$i].'|'.trim($module_settings["all_secondaries_label"]);
					if($module_settings["show_product_count"]) {
						$menuPages.= " [".$categoryQuery['catProdCount'][$i]."]";
					}
					$menuPages.= '^';
				}
				if($module_settings["relate_cats"]) {
					$filterArr = array();
					if ($categoryQuery["category_id"][$i] != 0) {
						$filterArr[] = array("category_id", $categoryQuery["category_id"][$i]);
					}
					$relateQuery = CWqueryBasicFilterRS($secondaryQuery, $filterArr);
					for($j=0; $j<$relateQuery['totalRows']; $j++) {
						$menuPages .= $relateQuery['category_id'][$j].'|'.$module_settings["action_page"].'?category='.$categoryQuery["category_id"][$i].'&secondary='.$relateQuery['secondary_id'][$j].'|'.$relateQuery['secondary_name'][$j];
						if($module_settings["show_product_count"]) {
							$menuPages .= " [".$relateQuery['catProdCount'][$j]."]";
						}
						$menuPages.= '^';
					}
				} else {
					// if not related 
					for ($k=0; $k<$secondaryQuery['totalRows']; $k++) {
						$menuPages .= $categoryQuery['category_id'][$i].'|'.$module_settings["action_page"].'?category='.$categoryQuery['category_id'][$i].'&secondary='.$secondaryQuery['secondary_id'][$k].'|'.$secondaryQuery['secondary_name'][$k];
						if($module_settings["show_product_count"]) {
							$menuPages .= " [".$secondaryQuery['catProdCount'][$k]."]";
						}
						$menuPages.= '^';
					}
				}
			}
		}
		if(strlen(trim($module_settings["append_links"]))) {
			$menuPages .= trim($module_settings["append_links"]);
		}
		// pass page list to menu markup function 
		$displayList = CWcreateNav($menuPages,$module_settings["current_url"],$module_settings["menu_id"],null,"^",$module_settings["menu_class"]);
		echo $displayList;
		break;
	// /END LIST 
	// LINKS - horizontal display of <a> elements with separator 
	case "links":
		$linkCt = 1000;
		$menuPages = '';
		if(strlen(trim($module_settings["prepend_links"]))) {
			$menuPages .= trim($module_settings["prepend_links"]);
			if(substr(trim($module_settings["prepend_links"]),strlen(trim($module_settings["prepend_links"]))-1) != "^") {
				$menuPages .= '^';
			}
		}
		// link for all categories 
		if(strlen(trim($module_settings["all_categories_label"]))) {
			$menuPages.= '1|'.$module_settings["action_page"].'|'.trim($module_settings["all_categories_label"]).'^';
		}
		$selCat = -1;
		// top level links 
		for($i=0; $i<$categoryQuery['totalRows']; $i++) {
			$_ENV["request.cwpage"]["allCount"] = 0;
			$menuPages .= '1|'.$module_settings["action_page"].'?category='.$categoryQuery['category_id'][$i].'|'.$categoryQuery['category_name'][$i]."";
			if($module_settings["show_product_count"]) {
				if ($selCat < 0 && $categoryQuery["category_id"][$i] == $_ENV["request.cwpage"]["categoryID"]) $selCat = $i;
				$menuPages.= " [".$categoryQuery['catProdCount'][$i]."]";
			}
			$menuPages .= "^";
			// set count for current category 'all' link 
			if ($_ENV["request.cwpage"]["categoryID"] == $categoryQuery["category_id"][$i] && strlen(trim($module_settings["all_secondaries_label"])) && $module_settings["show_product_count"]) {
				$_ENV["request.cwpage"]["allCount"] = $categoryQuery["catProdCount"][$i];
			}
		}
		// secondary links (not shown for related links in 'all' cat) 
		if($module_settings["show_secondary"] && !($module_settings["relate_cats"] == true && $_ENV["request.cwpage"]["categoryID"] == 0)) {
			if(strlen(trim($module_settings["all_secondaries_label"]))) {
				$menuPages.= '2|'.$module_settings["action_page"];
				if($_ENV["request.cwpage"]["categoryID"] != 0) {
					$menuPages.= '?category='.$_ENV["request.cwpage"]["categoryID"];
				}
				$menuPages.= '|'.trim($module_settings["all_secondaries_label"]);
				if($module_settings["show_product_count"]) {
					$menuPages .= " [".$_ENV["request.cwpage"]["allCount"]."]";
				}
				$menuPages .= "^";
			}
			if ($module_settings["relate_cats"]) {
				$filterArr = array();
				if ($_ENV["request.cwpage"]["categoryID"] != 0) {
					$filterArr[] = array("category_id", $_ENV["request.cwpage"]["categoryID"]);
				}
				$relateQuery = CWqueryBasicFilterRS($secondaryQuery, $filterArr);
				for($j=0; $j<$relateQuery['totalRows']; $j++) {
					$menuPages .= '2|'.$module_settings["action_page"]."?";
					if ($_ENV["request.cwpage"]["categoryID"] != 0) {
						$menuPages .= 'category='.$relateQuery["category_id"][$j].'&';
					}
					$menuPages .= 'secondary='.$relateQuery['secondary_id'][$j].'|'.$relateQuery['secondary_name'][$j];
					if($module_settings["show_product_count"]) {
						$menuPages .= " [".$relateQuery['catProdCount'][$j]."]";
					}
					$menuPages .= '^';
					$linkCt++;
				}
			} else {
				// if not related 
				for ($k=0; $k<$secondaryQuery['totalRows']; $k++) {
					$menuPages .= '2|'.$module_settings["action_page"].'?secondary='.$secondaryQuery['secondary_id'][$k].'|'.$secondaryQuery['secondary_name'][$k];
					if($module_settings["show_product_count"]) {
						$menuPages .= " [".$secondaryQuery['catProdCount'][$k]."]";
					}
					$menuPages .= '^';
					$linkCt++;
				}
			}
		}
		if (strlen(trim($module_settings["append_links"]))) {
			$menuPages .= $module_settings["append_links"];
		}
		// pass page list to menu markup function 
		$displayList = CWcreateLinks($menuPages,$_ENV["request.cwpage"]["categoryID"],$_ENV["request.cwpage"]["secondaryID"],$module_settings["separator"], NULL, "^");
		echo $displayList;
		break;
	// / END LINKS 
	// FORM - search form with optional elements 
	case "form":
?>
            	<form name="<?php echo $module_settings["form_id"]; ?>" id="<?php echo $module_settings["form_id"]; ?>" method="get" action="<?php echo $module_settings["action_page"]; ?>">
<?php
		// keywords 
		if($module_settings["form_keywords"]) {
?>
                    <input name="keywords" id="<?php echo $module_settings["form_id"]; ?>-keywords" type="text" value="<?php echo CWremoveEncoded($module_settings["form_keywords_text"]); ?>" onFocus="if(this.value == defaultValue) {this.value=''}">
<?php					
		}
		// category 
		if($module_settings["form_category"]) { 
?>
					<select name="category" id="<?php echo $module_settings["form_id"]; ?>-category">
<?php
			if (strlen(trim($module_settings["form_category_label"]))) {
?>
							<option value="0" class="search0"><?php echo trim($module_settings["form_category_label"]); ?></option>
<?php
			}
			// populate dropdown with results of category query 
			for ($i=0; $i<$categoryQuery["totalRows"]; $i++) {
?>
							<option value="<?php echo $categoryQuery["category_id"][$i]; ?>" class="search<?php echo $categoryQuery["category_id"][$i]; ?>"<?php if (isset($_ENV["request.cwpage"]["categoryID"]) && $categoryQuery["category_id"][$i] == $_ENV["request.cwpage"]["categoryID"]) { ?> selected="selected"<?php } ?>><?php echo $categoryQuery["category_name"][$i]; if ($module_settings["show_product_count"]) { ?> (<?php echo $categoryQuery["catProdCount"][$i]; ?>)<?php } ?></option>
<?php
			}
?>
					</select>
<?php					
		}
		// secondary 
		if($module_settings["form_secondary"]) {
?>
					<select name="secondary" id="<?php echo $module_settings["form_id"]; ?>-secondary">
<?php
			// if label provided, show initial value 
			if (strlen(trim($module_settings["form_secondary_label"]))) {
?>
							<option value="0" selected="selected" class="search0"><?php echo trim($module_settings["form_secondary_label"]); ?></option>
<?php
			}
			// populate dropdown with results of secondary query  
			for ($i=0; $i<$secondaryQuery["totalRows"]; $i++) {
				// if id is valid 
				if ($secondaryQuery["secondary_id"][$i] > 0) {
					// if relating categories to secondaries, add class for selection script ?>
								<option value="<?php echo $secondaryQuery["secondary_id"][$i]; ?>"<?php if (isset($secondaryQuery["category_id"][$i]) && $secondaryQuery["category_id"][$i] > 0) { ?> class="search<?php echo $secondaryQuery["category_id"][$i]; ?>"<?php } if (isset($_ENV["request.cwpage"]["secondaryID"]) && $secondaryQuery["secondary_id"][$i] == $_ENV["request.cwpage"]["secondaryID"]) { ?> selected="selected"<?php } ?>><?php echo $secondaryQuery["secondary_name"][$i]; if ($module_settings["show_product_count"]) { ?> (<?php echo $secondaryQuery["catProdCount"][$i]; ?>)<?php } ?></option>
<?php
				}
			}
?>
					</select>
<?php
		}
?>
                    <input name="" type="submit" class="CWformButtonSearch" value="<?php echo $module_settings["form_button_label"];?>">
                </form>              
<?php
		// script for related selections 
		$formScriptVar = ((isset($_ENV["request.cwpage"][$module_settings["form_id"]."FormScript"])) ? $_ENV["request.cwpage"][$module_settings["form_id"]."FormScript"] : null );
		if(!isset($formScriptVar)) {
			$formScriptVar = '';
			$formScriptVar .= "<script type=\"text/javascript\">
			jQuery(document).ready(function() {
				// form parent element (by id provided in attributes)
				var form_parent = '#".$module_settings["form_id"]."';
				// on submit, empty keywords value, remove duplicate secondaries
				jQuery(form_parent).submit(function() {
					if (jQuery('#".$module_settings["form_id"]."-keywords').val() == '".$module_settings["form_keywords_text"]."') {
						jQuery('#".$module_settings["form_id"]."-keywords').val('');
					};
					jQuery('#".$module_settings["form_id"]."-secondary-temp').remove('');
				});";
			// if relating secondaries to main level categories 
			if($module_settings["relate_cats"] && !isset($_POST['productCatsScript'])) { 
				// related selections: create hidden copy of secondary select element
				$formScriptVar.= "
				jQuery(form_parent + ' #".$module_settings["form_id"]."-secondary').clone().appendTo(form_parent).attr('id','".$module_settings["form_id"]."-secondary-temp').hide();
				// function to restore secondary options matching a given class
				var \$restoreSeconds = function(selectList,matchClass) {
						var temp_id = jQuery(selectList).attr('id') + '-temp';
						var temp_select = jQuery(form_parent + ' #".$module_settings["form_id"]."-secondary-temp');
						jQuery(temp_select).find('option.' + matchClass).each(function() {
							jQuery(this).clone().appendTo(jQuery(form_parent + ' #".$module_settings["form_id"]."-secondary'));
						});
				};
				// function to reset all secondary options
				var \$resetSeconds = function(selectList,matchClass) {
						var temp_id = jQuery(selectList).attr('id') + '-temp';
						var temp_select = jQuery(form_parent + ' #".$module_settings["form_id"]."-secondary-temp');
						jQuery(temp_select).find('option').each(function() {
							jQuery(this).clone().appendTo(jQuery(form_parent + ' #".$module_settings["form_id"]."-secondary'));
						});
				};
				
				// hide non-matching secondaries on changing main category selection
				jQuery(form_parent + ' ' + '#".$module_settings["form_id"]."-category').change(function() {
					// class to match
					var keepClass = jQuery(this).find('option:selected').attr('class');
					// remove all options
					jQuery(form_parent + ' ' + '#".$module_settings["form_id"]."-secondary').children('option').remove();
						// reset all
						if (keepClass == 'search0') {
							\$resetSeconds(this);
						// reset matching only
						} else {
							// restore default option
							\$restoreSeconds(this,'search0');
							// restore matching
							\$restoreSeconds(this,keepClass);
						};
				});
				// end hide non-matching secondaries
				// trigger change on page load
				jQuery(form_parent + ' ' + '#".$module_settings["form_id"]."-category').trigger('change');
				jQuery
				";
			}
			$formScriptVar.="});
</script>";
			CWinsertHead($formScriptVar);
		}
		break;
	// / END FORM 
	// BREADCRUMB - category, secondary, product  
	case "breadcrumb":
		// category 
		if ($_ENV["request.cwpage"]["categoryID"] > 0) {
			$catURL = $module_settings["action_page"]."?category=".$_ENV["request.cwpage"]["categoryID"];
			// if we have the category name 
			if (isset($_ENV["request.cwpage"]["categoryName"]) && strlen(trim($_ENV["request.cwpage"]["categoryName"]))) {
				$catName = trim($_ENV["request.cwpage"]["categoryName"]);
				// if no name, get it 
			} else {
				$catQuery = CWquerySelectCatDetails($_ENV["request.cwpage"]["categoryID"]);
				$catName = ((isset($catQuery['category_name'][0])) ? trim($catQuery['category_name'][0]) : "" );
			}
			// create the link 
			$catLink = '<a href="'.$catURL.'" class="CWlink">'.$catName.'</a>';
			// if no category is defined 
		} else {
			// if using all categories link 
			if(strlen(trim($module_settings["all_categories_label"]))) {
				$catURL = $module_settings["action_page"];
				$catName = trim($module_settings["all_categories_label"]);
				$catLink = '<a href="'.$catURL.'" class="CWlink">'.$catName.'</a>';
			} else {
				$catLink = '';
			}
		}
		// secondary 
		if ($_ENV["request.cwpage"]["secondaryID"] > 0) {
			$secondURL = $module_settings["action_page"]."?";
			if ($_ENV["request.cwpage"]["categoryID"] > 0) {
				$secondURL.= "category=".$_ENV["request.cwpage"]["categoryID"]."&";
			}
			$secondURL .= "secondary=".$_ENV["request.cwpage"]["secondaryID"];
			// if we have the secondary name 
			if (isset($_ENV["request.cwpage"]["secondaryName"]) && strlen(trim($_ENV["request.cwpage"]["secondaryName"]))) {
				$secondName = trim($_ENV["request.cwpage"]["secondaryName"]);
			} else {
				$secondQuery = CWquerySelectSecondaryCatDetails($_ENV["request.cwpage"]["secondaryID"]);
				$secondName = ((isset($secondQuery['secondary_name'][0])) ? trim($secondQuery['secondary_name'][0]) : "" );
			}
			// create the link 
			$secondLink = '<a href="'.$secondURL.'" class="CWlink">'.$secondName.'</a>';
			// if no secondary ID defined 
		} else {
			// if using all secondaries link and a category is defined 
			if (strlen(trim($module_settings["all_secondaries_label"])) && $_ENV["request.cwpage"]["categoryID"] > 0) {
				$secondURL = $module_settings["action_page"]."?category=".$_ENV["request.cwpage"]["categoryID"];
				$secondLink = '<a href="'.$secondURL.'" class="CWlink">'.$module_settings["all_secondaries_label"].'</a>';
			} else {
				$secondLink = '';
			}
		}
		// product 
		if($_ENV["request.cwpage"]["productID"] > 0) {
			// if we have the product name 
			if(isset($_ENV["request.cwpage"]["productName"]) && strlen(trim($_ENV["request.cwpage"]["productName"]))) {
				$prodName = trim($_ENV["request.cwpage"]["productName"]);
				// if no name, get it 
			} else {
				$prodQuery = CWquerySelectProductDetails($_ENV["request.cwpage"]["productID"]);
				$prodName = trim($prodQuery['product_name'][0]);
			}
			// if no product ID defined 
		} else {
			if(strlen(trim($module_settings["all_products_label"])) && $_ENV["request.cwpage"]["secondaryID"] > 0) {
				$prodName = trim($module_settings["all_products_label"]);
			} else {
				$prodName = '';
			}
		}
		// if searching by keyword 
		if(isset($_ENV["request.cwpage"]["keywords"]) && strlen(trim($_ENV["request.cwpage"]["keywords"]))) {
			$endLabel = 'Searching for &quot;'.trim($_ENV["request.cwpage"]["keywords"]).'&quot;';
		} else {
			$endLabel = $module_settings["end_label"];
		}
		// build the structure 
		$navMarkup = '';
		if (strlen(trim($module_settings["prepend_breadcrumb"]))) {
			$navMarkup .= $module_settings["separator"].$module_settings["prepend_breadcrumb"];
		}
		if (strlen(trim($catLink))) {
			$navMarkup .= $module_settings["separator"].$catLink;
		}
		if (strlen(trim($secondLink)) && $module_settings["show_secondary"]) {
			$navMarkup .= $module_settings["separator"].$secondLink;
		}
		if (strlen(trim($prodName))) {
			$navMarkup .= $module_settings["separator"].$prodName;
		}
		if(strlen(trim($endLabel))) {
			$navMarkup .= $module_settings["separator"].$endLabel;
		}
		
		// output the breadcrumb nav 
		if(strlen(trim($navMarkup))) {
?>
					<div class="CWbreadcrumb"><?php echo trim($navMarkup); ?></div>
<?php
		}
		break;
	// / END BREADCRUMB 
}
?>