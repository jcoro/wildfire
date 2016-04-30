<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-productList.php
File Date: 2012-02-01
Description: Shows product listings, based on search/url vars
==========================================================
*/
// default url variables 
if (!isset($_GET['category'])) { $_GET['category'] = 0; }
if (!isset($_GET['secondary'])) { $_GET['secondary'] = 0; }
if (!isset($_GET['keywords'])) { $_GET['keywords'] = ""; }
if (!isset($_GET['page'])) { $_GET['page'] = 1; }
if (!isset($_GET['showall'])) { $_GET['showall'] = 0; }
if (!($_GET['showall'] == true || $_GET['showall'] == 1)) {
	$_GET['showall'] = 0;
}
if (!is_numeric($_GET['page'])) {
	$_GET['page'] = 1;
}
// sorting vars 
if (!isset($_SESSION["cwclient"]["cwProductSortBy"])) { $_SESSION["cwclient"]["cwProductSortBy"] = "sort"; }
if (!isset($_SESSION["cwclient"]["cwProductSortDir"])) { $_SESSION["cwclient"]["cwProductSortDir"] = "asc"; }
if (!isset($_SESSION["cwclient"]["cwProductPerPage"])) { $_SESSION["cwclient"]["cwProductPerPage"] = $_ENV["application.cw"]["appDisplayPerPage"]; }
if (!isset($_GET['sortby'])) { $_GET['sortby'] = $_SESSION["cwclient"]["cwProductSortBy"]; }
if (!isset($_GET['sortdir'])) { $_GET['sortdir'] = $_SESSION["cwclient"]["cwProductSortDir"]; }
if (!isset($_GET['perpage'])) { $_GET['perpage'] = $_SESSION["cwclient"]["cwProductPerPage"]; }
// page variables passed to search function
//can be overridden per page or passed in via URL 
if (!isset($_ENV["request.cwpage"]["categoryName"])) { $_ENV["request.cwpage"]["categoryName"] = ""; }
if (!isset($_ENV["request.cwpage"]["categoryID"])) { $_ENV["request.cwpage"]["categoryID"] = $_GET['category']; }
if (!isset($_ENV["request.cwpage"]["secondaryName"])) { $_ENV["request.cwpage"]["secondaryName"] = ""; }
if (!isset($_ENV["request.cwpage"]["secondaryID"])) { $_ENV["request.cwpage"]["secondaryID"] = $_GET['secondary']; }
if (!isset($_ENV["request.cwpage"]["keywords"])) { $_ENV["request.cwpage"]["keywords"] = $_GET['keywords']; }
if (!isset($_ENV["request.cwpage"]["resultsPage"])) { $_ENV["request.cwpage"]["resultsPage"] = $_GET['page']; }
if (!isset($_ENV["request.cwpage"]["resultsMaxRows"])) { $_ENV["request.cwpage"]["resultsMaxRows"] = $_GET['perpage']; }
if (!isset($_ENV["request.cwpage"]["showAll"])) { $_ENV["request.cwpage"]["showAll"] = $_GET['showall']; }
if (!isset($_ENV["request.cwpage"]["sortBy"])) { $_ENV["request.cwpage"]["sortBy"] = $_GET['sortby']; }
if (!isset($_ENV["request.cwpage"]["sortDir"])) { $_ENV["request.cwpage"]["sortDir"] = $_GET['sortdir']; }
if (!isset($_ENV["request.cwpage"]["storeLinkText"])) { $_ENV["request.cwpage"]["storeLinkText"] = $_ENV["application.cw"]["companyName"]; }
if (!isset($primaryText)) { $primaryText = ""; }
if (!isset($secondaryText)) { $secondaryText = ""; }
// if using sort order default, force 'ascending' order (1-9999) 
if (strtolower($_ENV["request.cwpage"]["sortBy"]) == "sort") {
	$_ENV["request.cwpage"]["sortDir"] = 'asc';
}
// form and link actions 
if (!isset($_ENV["request.cwpage"]["hrefUrl"])) { $_ENV["request.cwpage"]["hrefUrl"] = trim($_ENV["application.cw"]["appCWStoreRoot"]).$_ENV["request.cw"]["thisPage"]; }
if (!isset($_ENV["request.cwpage"]["adminURLPrefix"])) { $_ENV["request.cwpage"]["adminURLPrefix"] = ""; }
// set up sortable links 
if (!isset($_ENV["application.cw"]["appDisplayProductSort"])) { $_ENV["application.cw"]["appDisplayProductSort"] = true; }
if (!isset($_ENV["request.cwpage"]["showSortLinks"])) { $_ENV["request.cwpage"]["showSortLinks"] = $_ENV["application.cw"]["appDisplayProductSort"]; }
// toggle sort direction 
if ($_ENV["request.cwpage"]["showSortLinks"]) {
	if ($_ENV["request.cwpage"]["sortDir"] == 'asc') {
		$newSortDir = 'desc';
	} else {
		$newSortDir = 'asc';
	}
}
// verify sortby is allowed 
if (!in_array($_ENV["request.cwpage"]["sortBy"], array("name","price","id"))) {
	$_ENV["request.cwpage"]["sortBy"] = 'sort';
}
// verify per page is numeric, and one of our specified values 
if (!(is_numeric($_ENV["request.cwpage"]["resultsMaxRows"]) && ListFind($_ENV["application.cw"]["productPerPageOptions"],$_ENV["request.cwpage"]["resultsMaxRows"]))) {
	$_ENV["request.cwpage"]["resultsMaxRows"] = $_ENV["application.cw"]["appDisplayPerPage"];
}
// set up link to edit admin product listing (if logged in) 
if (!isset($_ENV["application.cw"]["adminProductLinksEnabled"])) { $_ENV["application.cw"]["adminProductLinksEnabled"] = false; }
// set up add to cart in listings 
if (!isset($_ENV["application.cw"]["appDisplayListingAddToCart"])) { $_ENV["application.cw"]["appDisplayListingAddToCart"] = false; }
// defaults for product search 
if (!isset($productsQuery)) { $productsQuery = array(); }
if (!isset($productsQuery["totalRows"])) { $productsQuery["totalRows"] = 0; }
if (!isset($_ENV["request.cwpage"]["productsPerRow"])) { $_ENV["request.cwpage"]["productsPerRow"] = $_ENV["application.cw"]["appDisplayColumns"]; }
$myDir = getcwd();
chdir(dirname(__FILE__));
// clean up form and url variables 
include("cwapp/inc/cw-inc-sanitize.php");
// CARTWEAVER REQUIRED FUNCTIONS 
include("cwapp/inc/cw-inc-functions.php");
chdir($myDir);
// page variables - request scope can be overridden per product as needed
$_ENV["request.cwpage"]["useAltPrice"] = $_ENV["application.cw"]["adminProductAltPriceEnabled"];
$_ENV["request.cwpage"]["altPriceLabel"] = $_ENV["application.cw"]["adminLabelProductAltPrice"];
$_ENV["request.cwpage"]["imageZoom"] = $_ENV["application.cw"]["appEnableImageZoom"];
$_ENV["request.cwpage"]["continueShopping"] = $_ENV["application.cw"]["appActionContinueShopping"];
// address used for redirection 
$_ENV["request.cwpage"]["relocateUrl"] = $_ENV["application.cw"]["appSiteUrlHttp"];
// address for continue shopping 
if (!isset($_ENV["request.cwpage"]["returnUrl"])) { $_ENV["request.cwpage"]["urlResults"]; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,perpage,showall,page,maxrows,submit,userconfirm,useralert");
// create the base url out of serialized url variables
if (!isset($_ENV["request.cwpage"]["baseUrl"])) { $_ENV["request.cwpage"]["baseUrl"] = CWserializeUrl($varsToKeep, $_ENV["request.cwpage"]["urlResults"]); }
if (strpos($_ENV["request.cwpage"]["baseUrl"], "=") !== false) $_ENV["request.cwpage"]["baseUrl"] .= "&";
// persist showall for sorting 
$_ENV["request.cwpage"]["baseSortUrl"] = $_ENV["request.cwpage"]["baseUrl"].'showall='.$_ENV["request.cwpage"]["showAll"];
if (strpos($_ENV["request.cwpage"]["baseSortUrl"], "=") !== false) $_ENV["request.cwpage"]["baseSortUrl"] .= "&";
// handle show all / paging (url.showall) 
if ($_ENV["request.cwpage"]["showAll"]) {
	$_ENV["request.cwpage"]["resultsPage"] = 1;
	$_ENV["request.cwpage"]["maxProducts"] = 1000;
} else {
	$_ENV["request.cwpage"]["maxProducts"] = $_ENV["request.cwpage"]["resultsMaxRows"];
}
// PRODUCT SEARCH QUERY 
$products = CWqueryProductSearch(
				$_ENV["request.cwpage"]["categoryID"],
				$_ENV["request.cwpage"]["secondaryID"],
				$_ENV["request.cwpage"]["keywords"],
				null,
				$_ENV["request.cwpage"]["resultsPage"],
				$_ENV["request.cwpage"]["maxProducts"],
				$_ENV["request.cwpage"]["sortBy"],
				$_ENV["request.cwpage"]["sortDir"],
				null,
				null,
				null,
				$_ENV["application.cw"]["appSearchMatchType"]);
$idlist = $products["idlist"];
$productCount = $products['count'];
// if only one is found, direct to that page 
$idArr = explode(",", $idlist);
if ($productCount == 1 && sizeof($idArr) == 1) {
	header("Location: ".$_ENV["request.cwpage"]["urlDetails"]."?product=".$idlist);
	exit;
}
// style width for results 
$listingW = (100/$_ENV["request.cwpage"]["productsPerRow"]).'%';
// javascript for form sort links 
$headcontent = "";
if ($_ENV["application.cw"]["appDisplayProductSortType"] == 'select' && $_ENV["request.cwpage"]["showSortLinks"]) {
	$headcontent .= "	<script type=\"text/javascript\">
			jQuery(document).ready(function(){
			jQuery('select.listingSortSelect,select.listingPerPage').change(function(){
			var submitUrl = jQuery(this).children('option:selected').val();
			window.location=submitUrl;
			});
		});
	</script>";
}
CWinsertHead($headcontent);
// /////// START OUTPUT /////// 
// product sorting 
if ($_ENV["request.cwpage"]["showSortLinks"]) {
	// links 
	if ($_ENV["application.cw"]["appDisplayProductSortType"] == 'links') {
?>
		<div class="CWlistingSortLinks">
			Sort by: <a href="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>sortby=name<?php if ($_ENV["request.cwpage"]["sortBy"] == 'name') { ?>&sortdir=<?php echo $newSortDir; } ?>"<?php if ($_ENV["request.cwpage"]["sortBy"] == 'name') { ?> class="currentLink"<?php } ?>>Product Name</a> <a href="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>sortby=price<?php if ($_ENV["request.cwpage"]["sortBy"] == 'price') { ?>&sortdir=<?php echo $newSortDir; } ?>"<?php if ($_ENV["request.cwpage"]["sortBy"] == 'price') { ?> class="currentLink"<?php } ?>>Price</a> <a href="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>sortby=id&sortdir=desc"<?php if ($_ENV["request.cwpage"]["sortBy"] == 'id') { ?> class="currentLink"<?php } ?>>Newly Added</a> <a href="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>sortby=sort&sortdir=asc"<?php if ($_ENV["request.cwpage"]["sortBy"] == 'sort') { ?> class="currentLink"<?php } ?>>Recommended Items</a>
		</div>
<?php
		// select dropdown 
	} else {
?>
		<div class="CWlistingSortSelect">
			<form name="listingSortForm" class="listingSortForm" action="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>">
<?php
		$ppoArr = $_ENV["application.cw"]["productPerPageOptions"];
		if (!is_array($ppoArr) && strlen(trim($ppoArr))) $ppoArr = explode(",", trim($ppoArr));
		else if (!is_array($ppoArr)) $ppoArr = array();
		if (sizeof($ppoArr) > 1 && $_ENV["request.cwpage"]["showAll"] != 1) {
?>
                <label for="listingPerPage">Per Page:</label>
                <select name="listingPerPage" class="listingSortSelect">
<?php
			foreach ($ppoArr as $key => $i) {
				if (is_numeric($i)) {
?>
                    <option value="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>perpage=<?php echo trim($i); ?>"<?php if ($_ENV["request.cwpage"]["resultsMaxRows"] == trim($i)) { ?> selected="selected"<?php } ?>><?php echo trim($i); ?></option>
<?php
				}
			}
?>
                </select>
<?php
		}
?>
                <label for="listingSortSelect">Sort By:</label>
                <select name="listingSortSelect" class="listingSortSelect">
                    <option value="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>sortby=id&sortdir=desc"<?php if ($_ENV["request.cwpage"]["sortBy"] == 'id') { ?> selected="selected"<?php } ?>>Newly Added</option>
                    <option value="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>sortby=price&sortdir=asc"<?php if ($_ENV["request.cwpage"]["sortBy"] == 'price' && $_ENV["request.cwpage"]["sortDir"] == 'asc') { ?> selected="selected"<?php } ?>>Price (Lowest First)</option>
                    <option value="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>sortby=price&sortdir=desc"<?php if ($_ENV["request.cwpage"]["sortBy"] == 'price' && $_ENV["request.cwpage"]["sortDir"] == 'desc') { ?> selected="selected"<?php } ?>>Price (Highest First)</option>
                    <option value="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>sortby=name&sortdir=asc"<?php if ($_ENV["request.cwpage"]["sortBy"] == 'name' && $_ENV["request.cwpage"]["sortDir"] == 'asc') { ?> selected="selected"<?php } ?>>Name (A-Z)</option>
                    <option value="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>sortby=name&sortdir=desc"<?php if ($_ENV["request.cwpage"]["sortBy"] == 'name' && $_ENV["request.cwpage"]["sortDir"] == 'desc') { ?> selected="selected"<?php } ?>>Name (Z-A)</option>
                    <option value="<?php echo $_ENV["request.cwpage"]["baseSortUrl"]; ?>sortby=sort&sortdir=asc"<?php if ($_ENV["request.cwpage"]["sortBy"] == 'sort') { ?> selected="selected"<?php } ?>>Recommended Items</option>
                </select>
            </form>
		</div>
<?php
	}
}
// /end sort links 

$myDir = getcwd();
chdir(dirname(__FILE__));
// breadcrumb navigation 
    /*
    $module_settings = array(
	"search_type" => "breadcrumb",
	"separator" => " &bull; ",
	"all_categories_label" => "",
	"all_secondaries_label" => "",
	"all_products_label" => "");
*/	
include("cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);

//paging and product count 
$module_settings = array(
	"results_per_page" => $_ENV["request.cwpage"]["resultsMaxRows"],
	"total_records" => $productCount,
	"max_links" => $_ENV["application.cw"]["appDisplayPageLinksMax"],
	"base_url" => $_ENV["request.cwpage"]["baseUrl"].'sortby='.$_ENV["request.cwpage"]["sortBy"].'&sortdir='.$_ENV["request.cwpage"]["sortDir"],
	"current_page" => $_ENV["request.cwpage"]["resultsPage"],
	"show_all" => $_ENV["request.cwpage"]["showAll"]);
include("cwapp/mod/cw-mod-productpaging.php");
unset($module_settings);
chdir($myDir);

// show listings ?>
<div id="CWlistings" class="CWcontent">
<!-- category/secondary/product name -->
<?php
if ($_ENV["request.cwpage"]["categoryID"] > 0 && isset($_POST['categoryName']) && strlen(trim($_POST['categoryName'])) && ($_ENV["application.cw"]["appEnableCatsRelated"] == true) || ($_ENV["application.cw"]["appEnableCatsRelated"] == false && $_ENV["request.cwpage"]["secondaryID"] == 0)) {
	$primaryText = '<a href="'.$_ENV["request.cwpage"]["urlResults"].'?category='.$_ENV["request.cwpage"]["categoryID"].'">'.$_ENV["request.cwpage"]["categoryName"].'</a>';
} else if (strlen(trim($_ENV["request.cwpage"]["storeLinkText"]))) {
	$primaryText = '<a href="'.$_ENV["request.cwpage"]["urlResults"].'">'.$_ENV["request.cwpage"]["storeLinkText"].'</a>';
}
if ($_ENV["request.cwpage"]["secondaryID"] > 0 && strlen(trim($_ENV["request.cwpage"]["secondaryName"]))) {
	$secondaryText = '<a href="'.$_ENV["request.cwpage"]["urlResults"].'?';
	if ($_ENV["request.cwpage"]["categoryID"] > 0) {
		$secondaryText .= 'category='.$_ENV["request.cwpage"]["categoryID"].'&';
	}
	$secondaryText .= 'secondary='.$_ENV["request.cwpage"]["secondaryID"].'">'.$_ENV["request.cwpage"]["secondaryName"].'</a>';
}
?>
	<h1 class="CWcategoryName">
<?php
if (isset($_ENV["request.cwpage"]["categoryName"])) {
   echo $_ENV["request.cwpage"]["categoryName"];
}

?>    
	</h1>
<?php
// category / subcategory descriptions 
$listingPrimaryText = CWgetListingText($_ENV["request.cwpage"]["categoryID"]);
$listingSecondaryText = CWgetListingText(0,$_ENV["request.cwpage"]["secondaryID"]);
// primary description 
// if related categories are selected, primary description is shown only for 'all' in category (no secondary category) 
if (($_ENV["application.cw"]["appEnableCatsRelated"] && strlen(trim($listingSecondaryText)) == 0 && strlen(trim($listingPrimaryText)) > 0) || (!$_ENV["application.cw"]["appEnableCatsRelated"] && strlen(trim($listingPrimaryText)) > 0)) {
?>
    	<div class="CWlistingText" id="CWprimaryDesc">
			<?php echo $listingPrimaryText; ?>
		</div>
<?php		
}
// secondary description 
if(strlen(trim($listingSecondaryText)) > 0) {
?>
        <div class="CWlistingText" id="CWsecondaryDesc">
			<?php echo $listingSecondaryText; ?>
		</div>
<?php			
}
// if products were found 
// if no products are returned 
if($products['count'] == 0) {
?>
    	<div class="CWalertBox">
				No Products Found
<?php
	if(strlen(trim($_ENV["request.cwpage"]["keywords"]))) {
		echo " for search term ".$_ENV["request.cwpage"]["keywords"];
	}
?>
				<br>
				<br>
				<a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>">View All Products</a>
		</div>
<?php	
}
// loop list of IDs from function above 
$loopCt = 1;
foreach ($idArr as $key => $pp) {
	// count output for insertion of breaks or other formatting 
	// show the product include ?>
       	<div class="CWlistingBox" style="width:33%">
<?php
	// product preview 
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array(
		"product_id" => $pp,
		"add_to_cart" => $_ENV["application.cw"]["appDisplayListingAddToCart"],
		"show_qty" => false,
		"show_description" => true,
		"show_image" => true,
		"show_price" => true,
		"image_class" => "CWimgResults",
		"image_position" => "above",
		"title_position" => "below",
		"details_link_text" => "<span style='margin-left:104px;'>&raquo; details</span>");
	include("cwapp/mod/cw-mod-productpreview.php");
	unset($module_settings);
	chdir($myDir);
	// edit product link 
	if ($_ENV["application.cw"]["adminProductLinksEnabled"] && isset($_SESSION["cw"]["loggedIn"]) && $_SESSION["cw"]["loggedIn"] == 1 && isset($_SESSION["cw"]["accessLevel"]) && ListFindNoCase('developer,merchant',$_SESSION["cw"]["accessLevel"])) {
		//$_ENV["request.cwpage"]["adminURLPrefix"].
?>





        	<p>
                <a href="<?php echo $_ENV["application.cw"]["appCWAdminDir"]; ?>product-details.php?productid=<?php echo $pp; ?>" class="CWeditProductLink" title="Edit Product"><img alt="Edit Product" src="<?php echo $_ENV["application.cw"]["appCWAdminDir"];?>img/cw-edit.gif"></a>
            </p>
<?php			
	}
?>
    </div>
<?php
	// divide rows 
	if ($loopCt % $_ENV["request.cwpage"]["productsPerRow"] == 0) {
?>
        	<div class="CWclear">
			</div>
<?php			
	}
	// advance counter 
	$loopCt++;
}
?>
<!-- clear floated content -->
	<div class="CWclear"></div>
</div>
<!-- / end #CWlistings -->
<!-- clear floated content -->
<div class="CWclear"></div>
<?php
$myDir = getcwd();
chdir(dirname(__FILE__));
// recently viewed products 
include("cwapp/inc/cw-inc-recentview.php");
// page end / debug 
include("cwapp/inc/cw-inc-pageend.php");
chdir($myDir);
?>