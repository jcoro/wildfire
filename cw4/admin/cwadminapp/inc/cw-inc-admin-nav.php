<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-nav.php
File Date: 2012-02-01
Description: Creates admin navigation menu markup
Add to pageList section below to modify the menu
==========================================================
*/
// //////////// 
// Edit this list to add/remove menu links  
// //////////// 
if(!isset($_SESSION["cw"]["accessLevel"])) { $_SESSION["cw"]["accessLevel"] = ''; }
// note : starting number groups sub-lists 
$pageList='';
$pageList .= "1|admin-home.php|Admin Home,1|admin-home.php|Site Overview,";
// PRODUCTS MENU 
$newArray_dmm=array('developer','manager','merchant');
$newArray_dm=array('developer','merchant');
if(in_array($_SESSION["cw"]["accessLevel"],$newArray_dmm)) {
   $pageList .="2|products.php|Products,2|products.php|Active Products,2|products.php?view=arch|Archived Products,2|product-details.php|Add New Product,2|product-images.php|Product Images,2|product-files.php|Product Files,";
}
// ORDERS MENU 
$pageList .= "3|orders.php|Orders,
3|orders.php|All Orders,";
$pageList.= CWqueryNavOrders(3);
$pageList .=" 4|customers.php|Customers,4|customers.php|Manage Customers";
if(in_array($_SESSION["cw"]["accessLevel"],$newArray_dmm)) {
	$pageList .=" ,4|customer-details.php|Add New Customer,";
	// CATEGORIES MENU 
	$pageList  .=  "5|categories-main.php|Categories,5|categories-main.php|Main Category,"; 
	$cat = explode(',',$_ENV["application.cw"]["adminLabelCategories"]);
	$catnew = $cat[0];
	$pageList .="5|categories-main.php?clickadd=1|Add New Category,";
	$pageList.="5|categories-secondary.php|Secondary Category,";
	$sec = explode(',',$_ENV["application.cw"]["adminLabelSecondaries"]);
	$secnew = $sec[0];
	$pageList .= "5|categories-secondary.php?clickadd=1|Add New Secondary,";
	// OPTIONS MENU 
	$pageList .=  "6|options.php|Options,6|options.php|Manage Options,6|option-details.php|Add New Option Group";
	// DISCOUNTS MENU 
	if($_ENV["application.cw"]["discountsEnabled"] && in_array($_SESSION["cw"]["accessLevel"],$newArray_dm)) {
		$pageList .= ",7|discounts.php|Discounts,7|discounts.php|Active Discounts,7|discounts.php?view=arch|Archived Discounts,7|discount-details.php|Add New Discount,";
	}
}
// SHIPPING / TAX MENU 
if(in_array($_SESSION["cw"]["accessLevel"],$newArray_dm)) {
	$pageList .=",8|ship-methods.php|Shipping/".$_ENV["application.cw"]["taxSystemLabel"].",8|countries.php|Countries/Regions,";
	if(!(isset($_ENV["application.cw"]["taxSystem"]) && strtolower($_ENV["application.cw"]["taxSystem"]) != 'groups')) {
		$pageList .="8|ship-extensions.php|Locale Extensions,";
	} else {
		$pageList .= "8|ship-extensions.php|Ship/".$_ENV["application.cw"]["taxSystemLabel"]." Extensions,";
	}
	$pageList .= "8|ship-methods.php|Shipping Methods,8|ship-ranges.php|Shipping Ranges,";
	if($_SESSION["cw"]["accessLevel"] == 'developer' ) {
		$pageList .= "8|ship-status.php|Ship / Order Status,8|config-settings.php?group_id=6|Shipping Settings,8|config-settings.php?group_id=5|".$_ENV["application.cw"]["taxSystemLabel"]." Settings,";
	}
	if(!(isset($_ENV["application.cw"]["taxSystem"]) && strtolower($_ENV["application.cw"]["taxSystem"]) != 'groups')) {
		$pageList .= "8|tax-groups.php|".$_ENV["application.cw"]["taxSystemLabel"]." Groups,";
		if (strtolower($_ENV["application.cw"]["taxCalctype"]) == "localtax") {
			$pageList .= "8|tax-regions.php|".$_ENV["application.cw"]["taxSystemLabel"]." Regions,";
		}
	}
	// STORE SETTINGS 
	$pageList .= "9|config-settings.php?group_id=3|Store Settings,9|config-settings.php?group_id=3|Company Info,9|credit-cards.php|Credit Cards,";
	if($_SESSION["cw"]["accessLevel"] == 'developer' ) { 
		$pageList .= "9|config-settings.php?group_id=11|Discount Settings,";
	} 
	$pageList .= "9|config-settings.php?group_id=6|Shipping Settings,9|config-settings.php?group_id=5|Tax Settings,";
	// ADMIN SETTINGS 
	if($_SESSION["cw"]["accessLevel"] == 'developer' ) { 
		$pageList .= "10|config-settings.php?group_id=7|Admin Settings,";
	} else { 
		$pageList .= "10|admin-users.php|Admin Users,";
	}
	$pageList .="10|admin-users.php|Admin Users";
	if($_SESSION["cw"]["accessLevel"] == 'developer') {
		$pageList .= ",10|config-settings.php?group_id=7|Admin Controls,
			10|config-settings.php?group_id=15|Admin Widgets,
			10|config-settings.php?group_id=24|Product Admin,";
	}
	// CUSTOM VARIABLES 
	// if logged in as developer, top level link goes to 'config groups' 
	if($_SESSION["cw"]["accessLevel"] == 'developer' ) {
		$pageList .= "11|config-groups.php|Site Setup,
			11|config-groups.php|Configuration Variables,";
		if (isset($_ENV["application.cw"]["appDataDeleteEnabled"]) && $_ENV["application.cw"]["appDataDeleteEnabled"]) {
			$pageList .= "11|db-handler.php?mode=testdata|Delete Test Data,";
		}
		// if logged in as merchant, top level link is first available group 
	} else {
		// QUERY: function creates top level link for config section
		//(Nav Counter, IDs to Omit, number of rows to return) 
		$pageList .= CWqueryNavConfig(11,'3,5,6,7,11,15,24',1);
	}
	// create the rest of the config menu 
	// QUERY: function creates custom  settings links for config menu
	//(Nav Counter, IDs to Omit) 
	$pageList .= CWqueryNavConfig(11,'3,5,6,7,11,15,24');
}
// end user level check 
// starting values for list functions 
$lastLinkCount = 0; 
$selectedGroup = 0; 
$isstarted = 0; 
$firstParent = 1; 
$firstChild = 1;
// START OUTPUT
// admin logo ?>
<a href="admin-home.php" title=""><img src="img/cw-logo.png" alt="Admin Home" width="180" id="imgLogo"></a>
<?php
// //////////// 
// Do Not Edit Beyond This Point 
// //////////// 
// create dynamic 'on' states by looking at the URL of the current page OR current page 'currentNav' variable 
$menuStr='<ul id="CWadminNavUL">';
$linkCt = 0;
$pageList= explode(',', $pageList);
$thisPage = explode("/", $_SERVER["SCRIPT_NAME"]);
foreach ($pageList as $k => $pl) {
	if (strlen(trim($pl))) {
		$thisList = explode('|',$pl);
		$thisLink = trim($thisList[1]);
		$thisLinkGroup = trim($thisList[0]);
		if ((!isset($_ENV["request.cwpage"]["currentNav"]) && trim($thisPage[sizeof($thisPage)-1]) == trim($thisLink)) ||
			(isset($_ENV["request.cwpage"]["currentNav"]) && trim(strtolower(basename($_ENV["request.cwpage"]["currentNav"]))) == strtolower(trim($thisLink)))) {
			$selectedGroup = $thisLinkGroup;
		}
	}
}
// loop the list, create links 
foreach ($pageList as $k => $pl) {
	if (strlen(trim($pl))) {
		$thisList = explode('|',$pl);
		$thisLinkCount = trim($thisList[0]);
		$thisLink = trim($thisList[1]);
		$thisLinkText = trim($thisList[sizeof($thisList)-1]);
		// set up the class for each link 
		if ($linkCt == 0) {
			$thisClass = "firstLink";
		} else {
			$thisClass = "";
		}
		if ($selectedGroup == $thisLinkCount && $thisLinkCount != $lastLinkCount) {
			$thisClass .= " currentLink";
		}
		if ($isstarted == 0) {
			$menuStr .= "
	<li>";
			$isstarted = 1;
		} else {
			if ($thisLinkCount == $lastLinkCount) {
				if ($firstChild == 1) {
					$menuStr .= "
		<ul>
			<li>";
					$firstChild = 0;
					$firstParent = 1;
				} else {
					$menuStr .= "</li>
			<li>";
				}
			} else if ($firstParent == 1 && $firstChild == 0) {
				$menuStr .= "</li>
		</ul>
	</li>
	<li>";
				$firstChild = 1;
			} else if ($firstParent == 1 && $firstChild == 1) {
				$menuStr .= "</li>
		<li>";
			}
		}
		if (isset($_ENV["request.cwpage"]["currentNav"]) && trim($_ENV["request.cwpage"]["currentNav"]) == trim($thisLink) && strpos($thisClass, 'currentLink') === false) {
			$thisClass .= ' currentLink';
		}
		$menuStr .= '<a href="'.$thisLink.'"';
		if (strlen(trim($thisClass))) { $menuStr .= ' class="'.trim($thisClass).'"'; }
		$menuStr .= '>'.$thisLinkText.'</a>';
		$lastLinkCount = $thisLinkCount;
	}
	$linkCt++;
}
$menuStr .= "</li>
		</ul>
	</li>
</ul>";
echo $menuStr;
?>
