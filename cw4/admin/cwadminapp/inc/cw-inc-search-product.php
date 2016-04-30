<?php  
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-search-product.php
File Date: 2012-04-18
Description: Search form for product listings
==========================================================
*/
// defaults for sorting 
if(!isset($_REQUEST['sortby']) || $_REQUEST['sortby'] == "") { $_REQUEST['sortby'] = "product_name";}
if(!isset($_REQUEST['sortdir']) || $_REQUEST['sortdir'] == "") { $_REQUEST['sortdir'] = "ASC";}
// use session if not defined in url 
if(isset($_SESSION["cwclient"]["cwproductSortBy"]) && !isset($_GET['sortby'])) {
	$_REQUEST['sortby'] = $_SESSION["cwclient"]["cwproductSortBy"];
	$_GET['sortby'] = $_SESSION["cwclient"]["cwproductSortBy"];
}
else if(isset($_GET['sortby']) && !isset($_GET['productid'])) {
	$_REQUEST['sortby'] = $_GET['sortby'];
}
if(isset($_SESSION["cwclient"]["cwproductSortDir"]) && !isset($_GET['sortdir'])) {
	$_REQUEST['sortdir'] = $_SESSION["cwclient"]["cwproductSortDir"];
	$_GET['sortdir'] = $_SESSION["cwclient"]["cwproductSortDir"];
}
else if(isset($_GET['sortdir']) && !isset($_GET['productid'])) {
	$_REQUEST['sortdir'] = $_GET['sortdir'];
}
// put new values in session for next time 
$_SESSION["cwclient"]["cwproductSortBy"] = $_REQUEST['sortby'];
$_SESSION["cwclient"]["cwproductSortDir"] = $_REQUEST['sortdir'];
// default for field to search 
if(!isset($_GET["searchBy"])) { $_GET['searchBy'] = ""; }
if(!isset($_GET["Find"])) { $_GET['Find'] = ""; }
// defaults for secondary categories based on top level category 
if (!isset($filterCat)) $filterCat = "0"; 
if (!isset($filterScndCat)) $filterScndCat = "0"; 
$filterScndCat = "0";
if(isset($_GET['searchC']) && $_GET['searchC'] > 0) {
	$filterCat = $_GET['searchC'];
}
if(isset($_GET['searchSC']) && $_GET['searchSC'] > 0) {
	$filterScndCat = $_GET['searchSC'];
}
// archive vs active 
if(isset($_ENV["request.cwpage"]["viewProdType"]) && stristr($_ENV["request.cwpage"]["viewProdType"], 'arch') !== false) {
	$searchArchived = TRUE;
} else {
	$searchArchived = FALSE;
}

// default search string (all/any) 
if(!isset($QueryFind)) { $QueryFind = "%"; }
// get all cats and subcats 
// QUERY: get all active categories 
$listActiveCats = CWquerySelectActiveCategories();
// QUERY: get all active secondary categories 
$listActiveScndCats = CWquerySelectActiveScndCategories($filterCat);
if ($_GET['Find'] != "") {
	// make search string case insensitive 
	$QueryFind = strtolower($_GET['Find']);
}
//dh added for no page num
$showLimit = 0;
if (!isset($_GET['pagenumresults']) || $_GET['pagenumresults'] == 0) $showLimit = $_GET['maxrows'];
// QUERY: search products (product search form vars) 
$productsQuery = CWquerySearchProducts($QueryFind,
					$_GET['searchBy'],
					$filterCat,
					$filterScndCat,
					$_GET['sortby'],
					$_GET['sortdir'],
					$searchArchived,
					$showLimit,
					true
					);

// if only one record found, go to productForm 
if ($searchArchived === FALSE && $productsQuery['totalRows'] == 1 && (($QueryFind != '%' && $QueryFind != '') || $filterCat > 0 || $filterScndCat > 0) && $_GET['search'] == 'search' && $_ENV["request.cw"]["thisPage"] != 'product-details.php') {
	CWpageMessage("confirm","1 Product Found: details below");
	header("Location: product-details.php?productid=".$productsQuery['product_id'][0]."&searchby=".$_GET['searchby']."&searchC=".$_GET['searchC']."&searchSC=".$_GET['searchSC']."&find=".$_GET['find']."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"]));
	exit;
}
// // SHOW FORM // ?>
<form name="formProductSearch" id="formProductSearch" method="get" action="products.php">
	<a id="showSearchFormLink" href="#">Search Products</a>
	<span class="advanced pushRight"><strong>Search Products&nbsp;&raquo;</strong></span>
	<label for="Find">&nbsp;Keyword:</label>
	<input name="Find" type="text" size="15" id="Find" value="<?php echo $_GET['find']; ?>">
	<label for="searchBy">&nbsp;Search In:</label>
	<select name="searchBy" id="searchBy">
		<option value="any" <?php if($_GET['searchby'] == "any") { echo "selected"; }?>>All Fields</option>
		<option value="prodID" <?php if($_GET['searchby'] == "prodID") { echo "selected"; }?>>Product ID</option>
		<option value="prodName" <?php if($_GET['searchby'] == "prodName") { echo "selected"; }?>>Product Name</option>
		<option value="descrip" <?php if($_GET['searchby'] == "descrip") { echo "selected"; }?>>Description</option>
	</select>
	<span class="advanced">
    
<?php
     if($_ENV["application.cw"]["adminProductPaging"] >= 1) {
			// rows per page 
			echo "&nbsp;Per Page:";
?>
	<select name="maxRows" id="maxRows">
<?php
		for ($mn=10; $mn<=100; $mn += 10) {
?>				
        <option value="<?php echo $mn; ?>"<?php if($mn == $_GET['maxrows']) { echo 'selected="selected"'; }?>><?php echo $mn; ?></option>
<?php                    
		}
?>			
    </select>
<?php
	}
?>		
	</span>
	&nbsp;&nbsp;
    <input name="Search" type="submit" class="CWformButton" id="Search" value="Search" style="margin-bottom: 2px;">
	<div class="subForm advanced">
<?php
	if($listActiveCats['totalRows'] > 1 || $listActiveScndCats['totalRows'] > 1) {
?>
		<span class="pushRight"><?php
		$listlen = explode('&',$_SERVER['QUERY_STRING']);
		if ($_SERVER['QUERY_STRING'] && count($listlen)) {
?><a href="<?php echo $_ENV['request.cw']['thisPage']; ?>?view=<?php echo $_GET['view']; ?>&search=search<?php if(isset($_GET['productid'])) { ?>&productid=<?php echo $_GET['productid']; } ?>">Reset Search</a><?php
		} else {
			echo "&nbsp;";
		}
?>
		</span>
<?php
	}
	
	// categories 
	if($listActiveCats['totalRows'] > 1) {
		$listFirst = explode(' ',$_ENV["application.cw"]["adminLabelCategory"]); ?>
			<label for="searchC">
             <?php echo $listFirst[0]; ?>
             :
             </label>
			<select name="searchC" id="searchC" onkeyup="this.change();" onchange="searchSelect(this);">
				<option value="">All</option>
<?php                
		for($i=0;$i<$listActiveCats['totalRows'];$i++) {
?>
                 <option value="<?php echo $listActiveCats['category_id'][$i]; ?>"<?php if($_GET['searchC'] == $listActiveCats['category_id'][$i]) { echo "selected" ; } ?>><?php echo substr($listActiveCats['category_name'][$i],0,15); ?> 
                 </option>
<?php
		}
?>				
			</select>
<?php
	} else {
?>			<input type="hidden" name="searchC" value="">
<?php
} 
// subcategories 
if($listActiveScndCats['totalRows'] > 1) {   $listFirst = explode(' ',$_ENV["application.cw"]["adminLabelSecondary"]); ?>
			&nbsp;	<label for="searchSC"><?php echo $listFirst[0] ?>:</label>
			<select name="searchSC" id="searchSC" onkeyup="this.change();" onchange="searchSelect(this);">
				<option value="">All</option>
<?php                
	for($i=0;$i<$listActiveScndCats['totalRows'];$i++) {
?>
                 <option value="<?php echo $listActiveScndCats['secondary_id'][$i]; ?>"<?php if($_GET['searchC'] == $listActiveScndCats['secondary_id'][$i]) { echo "selected" ; } ?>><?php echo substr($listActiveScndCats['secondary_name'][$i],0,15); ?></option>
<?php
	}
?>				
			</select>
<?php
} else {
?>			<input type="hidden" name="searchSC" value="">
<?php
}
?>
		
		<label for="view">Status:</label>
		<select name="view" id="view">
			<option value="active" <?php if(!strpos($_GET['view'],'arch')===true) {echo 'selected="selected"'; } ?>>Active</option>
			<option value="arch" <?php if(strpos($_GET['view'],'arch')!==false) { echo 'selected="selected"'; } ?>>Archived</option>
		</select>
	</div>
</form>
<?php
// Set Variables for recordset Paging  
$maxRows_Results= $_GET['maxrows'];
$startRow_Results = min((($_GET['pagenumresults']-1) * $maxRows_Results)+1,max($productsQuery['totalRows'],1));
$endRow_Results = min(($startRow_Results+$maxRows_Results)-1,$productsQuery['totalRows']);
$TotalPages_Results=ceil($productsQuery['totalRows']/$maxRows_Results);
// SERIALIZE 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("pagenumresults,userconfirm,useralert");
$pagingUrl = CWserializeURL($varsToKeep);
if($_ENV["application.cw"]["adminProductPaging"]) {
	$_ENV["request.cwpage"]["pagingLinks"] = '<span class="pagingLinks">Page '. $_GET['pagenumresults'].' of '. $TotalPages_Results .' [Showing '. $productsQuery['totalRows'].' Product';
	if($productsQuery['totalRows'] != 1) { $_ENV["request.cwpage"]["pagingLinks"] .= "s"; }
	$_ENV["request.cwpage"]["pagingLinks"] .= ']<br>';
	if($TotalPages_Results > 1) {
		if($_GET['pagenumresults'] > 1) {
			$_ENV["request.cwpage"]["pagingLinks"].='<a href="'.$_ENV['request.cw']['thisPage'].'?pagenumresults=1&sortby='.$_GET['sortby'] . '&'. $pagingUrl .'">First</a> | <a href="'.$_ENV['request.cw']['thisPage'].'?pagenumresults='.($_GET['pagenumresults']-1).'&sortby='.$_GET['sortby'] . '&'. $pagingUrl . '">Previous</a>  |';
		} else {
			$_ENV["request.cwpage"]["pagingLinks"].='First | Previous |';
		}
		if($_GET['pagenumresults'] < $TotalPages_Results) {
			$_ENV["request.cwpage"]["pagingLinks"].='	<a href="'.$_ENV['request.cw']['thisPage'].'?pagenumresults=' .($_GET['pagenumresults']+1) . '&sortby='.$_GET['sortby'] . '&'. $pagingUrl . '">Next</a> | <a href="'.$_ENV['request.cw']['thisPage'].'?pagenumresults=' . $TotalPages_Results . '&sortby='.$_GET['sortby'] . '&'. $pagingUrl  . '">Last</a>';
		} else {
			$_ENV["request.cwpage"]["pagingLinks"].= 'Next | Last';
		}
	}
	$_ENV["request.cwpage"]["pagingLinks"].='
</span>';
} else {
	$_ENV["request.cwpage"]["pagingLinks"] = '<span class="pagingLinks">
		[Showing '.$productsQuery['totalRows'].' Product';
	if($productsQuery['totalRows'] != 1) { $_ENV["request.cwpage"]["pagingLinks"] .="s"; }
    $_ENV["request.cwpage"]["pagingLinks"] .= ']<br>
	</span>';
}
// Submit form if Subcat/Category select changed 
$headcontent = "
<script type=\"text/javascript\">
function searchSelect(selBox) {
 	if (jQuery('input#Find').val() == '') {
		jQuery(selBox).parents('form').submit();
		}
	};
</script>";
CWinsertHead($headcontent);
?>