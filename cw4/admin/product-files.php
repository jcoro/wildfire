<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: product-files.php
File Date: 2012-07-03
Description: Handles downloadable file viewing and management
==========================================================
*/
// time out the page if it takes too long - avoid server overload 
if (!ini_get("safe_mode") && !in_array("set_time_limit", explode(",", ini_get("disable_functions")))) @set_time_limit(9000);
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
require_once("cwadminapp/func/cw-func-download.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accesslevel"] = CWauth("manager,merchant,developer");
// PAGE PARAMS 
if (!isset($_ENV["application.cw"]["adminProductPaging"])) $_ENV["application.cw"]["adminProductPaging"] = 1;
if (!isset($_ENV["application.cw"]["adminRecordsPerPage"])) $_ENV["application.cw"]["adminRecordsPerPage"] = 30;
// default values for seach/sort 
if (!isset($_GET["pagenumresults"])) $_GET["pagenumresults"] = 1;
if (!isset($_GET["search"])) $_GET["search"] = "";
if (!isset($_GET["find"])) $_GET["find"] = "";
if (!isset($_GET["maxrows"])) $_GET["maxrows"] = $_ENV["application.cw"]["adminRecordsPerPage"];
if (!isset($_GET["sortby"])) $_GET["sortby"] = "fileInUse";
if (!isset($_GET["sortdir"])) $_GET["sortdir"] = "desc";
// default values for display 
$filePath = CWcreateDownloadPath();
$pathChar = ((strpos($filePath, "/") !== false) ? "/" : "\\");
// QUERY: get Files in specified directory 
$filesQuery = CWgetFolderContents($filePath, true, true, null);
// QUERY: get All active filenames related to any product 
$skuFilesQuery = CWquerySelectSkuFiles();
// QUERY: get only unique filenames 
$usedFilesQuery = array();
foreach ($skuFilesQuery as $key => $values) {
    if ($key == "totalRows") $usedFilesQuery[$key] = 0;
    else $usedFilesQuery[$key] = array();
}
if (isset($skuFilesQuery["sku_download_id"])) {
    foreach ($skuFilesQuery["sku_download_id"] as $id) {
        if (!in_array($id, $usedFilesQuery["sku_download_id"])) {
            $usedFilesQuery["sku_download_id"][] = $i;
            $usedFilesQuery["totalRows"]++;
        }
    }
}
// set up lists of filenames used for output 
$_ENV["request.cwpage"]["allFileNames"] = ((isset($filesQuery["name"])) ? $filesQuery["name"] : array());
$_ENV["request.cwpage"]["usedFileNames"] = array();
$_ENV["request.cwpage"]["idleFileNames"] = array();
// create our display query w/ file and product info 
$fileInfoQuery = array(
    "totalRows" => 0,
    "fileName" => array(),
    "fileKey" => array(),
    "fileDate" => array(),
    "fileSize" => array(),
    "fileUrl" => array(),
    "fileType" => array(),
    "fileProducts" => array(),
    "fileInUse" => array()
    );
// loop found files, set up info query 
for ($i=0; $i<$filesQuery["totalRows"]; $i++) {
    // set up some info about this file 
    $dl = array();
    $dl["fileKey"] = $filesQuery["files"][$i];
    $dl["fileType"] = substr($filesQuery["files"][$i], strrpos($filesQuery["files"][$i], ".")+1);
    $dl["fileDate"] = filemtime($filesQuery["paths"][$i]);
    $dl["fileSize"] = filesize($filesQuery["paths"][$i]);
    // is the file in use? 
    $dl["fileSkus"] = array();
    if (isset($usedFilesQuery["sku_download_id"]) && in_array($dl["fileKey"], $usedFilesQuery["sku_download_id"])) {
        $dl["inUse"] = true;
        // if so get the products and skus it is being used on 
        $getFileSkus = array();
        foreach ($skuFilesQuery as $key => $values) {
            if ($key == "totalRows") $getFileSkus[$key] = 0;
            else $getFileSkus[$key] = array();
        }
        if (isset($skuFilesQuery["sku_download_id"])) {
            foreach ($skuFilesQuery["sku_download_id"] as $index => $id) {
                if ($id == $dl["fileKey"]) {
                    foreach ($skuFilesQuery as $key => $values) {
                        if ($key != "totalRows") {
                            $getFileSkus[$key][] = $values[$index];
                        }
                    }
                    $getFileSkus["totalRows"]++;
                }
            }
        }
        // add file sku info to query 
        for ($n=0; $n<$getFileSkus["totalRows"]; $n++) {
            $dl["fileSkus"][] = $getFileSkus["product_id"][$n]."|".$getFileSkus["product_name"][$n]." [".$getFileSkus["sku_merchant_sku_id"][$n]."]";
        }
        $dl["fileUrl"] = CWcreateDownloadUrl($getFileSkus["sku_id"][$n],'product-file-preview.php');
        $dl["fileName"] = $getFileSkus["sku_download_file"][0];
        $_ENV["request.cwpage"]["usedFileNames"][] = $dl["fileName"];
    // if not in use 
    } else {
        $dl["inUse"] = false;
        $dl["fileUrl"] = '';
        $dl["fileName"] = $filesQuery["files"][$i];
        $_ENV["request.cwpage"]["idleFileNames"][] = $dl["fileName"];
    }
    // end if in use 

    // add data to display query 
    $fileInfoQuery['fileName'][] = $dl["fileName"];
    $fileInfoQuery['fileKey'][] = $dl["fileKey"];
    $fileInfoQuery['fileType'][] = $dl["fileType"];
    $fileInfoQuery['fileDate'][] = $dl["fileDate"];
    $fileInfoQuery['fileSize'][] = $dl["fileSize"];
    $fileInfoQuery['fileUrl'][] = $dl["fileUrl"];
    $fileInfoQuery['fileProducts'][] = $dl["fileSkus"];
    $fileInfoQuery['fileInUse'][] = $dl["inUse"];
    $fileInfoQuery['totalRows']++;
}
// end create info query 

// QUERY: make sortable 
$fileInfoQuery = CWsortableQuery($fileInfoQuery);


// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,pagenumresults,userconfirm,useralert,dellist,delidle,delorig,delall,userid");
// create the base url out of serialized url variables 
$_ENV["request.cwpage"]["baseUrl"] = CWserializeUrl($varsToKeep,$_ENV["request.cw"]["thisPage"]);

// /////// 
// DELETE FILES 
// /////// 
$_ENV["request.cwpage"]["deleteList"] = array();
// delete by form 
if (!isset($_POST["dellist"])) $_POST["dellist"] = array();
if (isset($_POST["deleteSelected"]) && $_POST["deleteSelected"] == session_id()) {
    $_ENV["request.cwpage"]["deleteList"] = $_POST["dellist"];
}
// delete unused / original 
if (!isset($_GET["delidle"])) $_GET["delidle"] = "";
if (!isset($_GET["delorig"])) $_GET["delorig"] = "";
if (!isset($_GET["userid"])) $_GET["userid"] = "";
// verify user posting the url is the user logged in 
if ($_GET["delidle"] == 'true' && $_GET["userid"] == session_id()) {
    $_ENV["request.cwpage"]["deleteList"] = $_REQUEST["request.cwpage"]["idleFileNames"];
}
if ($_GET["delorig"] == 'true' && $_GET["userid"] == session_id()) {
    $_ENV["request.cwpage"]["deleteList"] = $_REQUEST["request.cwpage"]["origFileNames"];
}
// delete ALL - only for developer 
if (!isset($_GET["delall"])) $_GET["delall"] = "";
if (!isset($_GET["userid"])) $_GET["userid"] = "";
// verify user posting the url is the user logged in 
if (in_array($_ENV["request.cwpage"]["accessLevel"], array("merchant", "developer"))
        && $_GET["delall"] == 'true'
        && $_GET["userid"] == session_id()) {
    $_ENV["request.cwpage"]["deleteList"] = $_ENV["request.cwpage"]["allFileNames"];
}
// if we have a list of at least one filename 
if (sizeof($_ENV["request.cwpage"]["deleteList"])) {
    $delCt = 0;
    // if deleting only originals (delorig) 
    foreach ($_ENV["request.cwpage"]["deleteList"] as $dd) {
        try {
            $delFile = trim($dd);
            // set up the file to delete 
            $delDir = CWcreateDownloadPath();
            if ($_ENV["application.cw"]["appDownloadsFileExtDirs"]) {
                $delExt = substr($dd, strrpos($dd, ".")+1);
                $delDir = CWtrailingChar($delDir,'add',$pathChar) . $delExt;
                $delSrc = CWtrailingChar($delDir,'add',$pathChar) . $delFile;
            }
            // if the file exists, delete it 
            if (file_exists($delSrc)) {
                unlink($delSrc);
                $delCt++;
            }
            // remove from any skus using this file ID 
            //$deleteSkuFileData = CWqueryDeleteSkuFile($delFile);
            // handle errors 
        } catch (Exception $e) {
            CWpageMessage("alert","Error: ".$e->getMessage()." ".$e->getTraceAsString());
        }
    }
    if ($delCt > 0) {
        if ($delCt == 1) $s = '';
        else $s = 's';
        CWpageMessage("confirm",$delCt." file".$s." deleted");
        if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
        if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
        header("Location: ".$_ENV["request.cwpage"]["baseUrl"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
        exit;
    }
}
// /////// 
// /END DELETE IMAGES 
// /////// 
// SUBHEADING 
$_ENV["request.cwpage"]["subhead"] = "
Files Uploaded: ".$filesQuery["totalRows"]."&nbsp;&nbsp;
In Use: ".sizeof($_ENV["request.cwpage"]["usedFileNames"])."&nbsp;&nbsp;
Not In Use: ".sizeof($_ENV["request.cwpage"]["idleFileNames"])."&nbsp;&nbsp;
File Path: ".$filePath;
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Manage Product Files";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "File Management";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = $_ENV["request.cwpage"]["subhead"];
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = 'product-files.php';
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"] = 1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"] = 1;
// START OUTPUT 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php
include('cwadminapp/inc/cw-inc-admin-scripts.php');
// PAGE JAVASCRIPT
?>
		<!--- PAGE JAVASCRIPT --->
		<script type="text/javascript">
			// this takes the ID of a checkbox, the number to show in the alert
			function confirmDelete(boxID,prodCt){
			// if this cat has products
				if (prodCt > 0){
					if (prodCt > 1){var prodWord = 'products'}else{var prodWord = 'product'};
				var confirmBox = '#'+ boxID;
					// if the box is checked and prodToggle is true
					if( jQuery(confirmBox).is(':checked') ){
					clickConfirm = confirm("Warning: file in use!\nThis file will be unassigned for " + prodCt + " " + prodWord + ".\nContinue?");
					// if confirm is returned false
					if(!clickConfirm){
						jQuery(confirmBox).prop('checked','');
					};

					};
					// end if checked
				};
				// end if prodct
			};

// alert for delete selected button
	function warn() {
	    var deleteBoxes = jQuery('input[name^="dellist"]');
	    var numberChecked = 0;
	    for (var i=0; i<deleteBoxes.length; i++) {
	         if (deleteBoxes[i].checked) { numberChecked += 1; }
	    }
	    if (numberChecked > 0){
	    			if (numberChecked > 1){var fileWord = 'files'}else{var fileWord = 'file'};
	    return confirm("Delete " + numberChecked+ " " + fileWord + " and unassign for all products?");
	    }else{
	    alert('No files selected');
            return false;
	    }
	};
// end script

// page jQuery
jQuery(document).ready(function(){
// containsNoCase function courtesy Rick Strahl:
// http://www.west-wind.com/weblog/posts/519980.aspx
$.expr[":"].containsNoCase = function(el, i, m) {
    var search = m[3];
    if (!search) return false;
    return eval("/" + search + "/i").test(jQuery(el).text());
};
// file search box
jQuery('#fileSearchBox').keyup(function(){
// use lowercase version of input string, to match classes on table rows (see 'tr' below)
var searchText = jQuery(this).val().toLowerCase();
// if blank, show all rows again
if (searchText == ''){
jQuery('#fileControlTable tbody tr').show();
}
// or filter all rows
else
{
// hide all rows, show the rows that match
jQuery('#fileControlTable tbody tr').hide();
// works for matching text or class
jQuery("#fileControlTable tbody tr:containsNoCase('"+searchText+"')").show();
//jQuery("#fileControlTable tbody tr:containsNoCase('"+ jQuery(this).val()+"')").show();
jQuery("#fileControlTable tbody tr[class*='"+ jQuery(this).val()+"']").show();
jQuery("#fileControlTable tbody tr a:containsNoCase('"+ jQuery(this).val()+"')").parents('tr').show();
}
});
// end file search

});
// end script
</script>
	</head>
 <?php
// body gets a class to match the filename 
$page = explode('.',$_ENV["request.cw"]["thisPage"]);
$page_First = $page[0];
?>
	<body <?php echo 'class="'.$page_First.'"'; ?>>
		<div id="CWadminWrapper">
			<!-- Navigation Area -->
			<div id="CWadminNav">
				<div class="CWinner">
<?php
include("cwadminapp/inc/cw-inc-admin-nav.php");
?>
				</div>
				<!-- /end CWinner -->
			</div>
			<!-- /end CWadminNav -->
			<!-- Main Content Area -->
			<div id="CWadminPage">
				<!-- inside div to provide padding -->
				<div class="CWinner">
<?php
// page start content / dashboard 
include("cwadminapp/inc/cw-inc-admin-page-start.php");

if(strlen(trim($_ENV["request.cwpage"]["heading1"])))
	echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]).'</h1>';    	
if(strlen(trim($_ENV["request.cwpage"]["heading2"]))) {
	echo '<h2>'.trim($_ENV["request.cwpage"]["heading2"]).'</h2>';    	
}
// user alerts 
include("cwadminapp/inc/cw-inc-admin-alerts.php");   		
?>
					<!-- Page Content Area -->
					<div id="CWadminContent">
						<!-- //// PAGE CONTENT ////  -->
<?php
// FILES TABLE 
// if no records found, show message 
if (!$filesQuery["totalRows"]) {
?>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p><strong>No files found.</strong></p>
<?php
} else {
    // form submits to same page 
?>
							<form action="<?php echo $_ENV["request.cwpage"]["baseUrl"]; ?>" name="imgForm" id="imgForm" method="post" class="CWobserve">
								<div class="CWadminControlWrap productFileControls">
									<?php // delete unused (idle) files ?>
									<a class="CWbuttonLink" href="<?php echo $_ENV["request.cwpage"]["baseUrl"]; ?>&delidle=true&userid=<?php echo session_id(); ?>" onclick="return confirm('This will delete all stored files not connected with any products.\n( <?php echo sizeof($_ENV["request.cwpage"]["idleFileNames"]); ?> found )\nContinue?')">Delete Unused</a>									
<?php
    // delete all files 
    if (in_array($_ENV["request.cwpage"]["accessLevel"], array("merchant","developer"))) {
?>
										<a class="CWbuttonLink" href="<?php echo $_ENV["request.cwpage"]["baseUrl"]; ?>&delall=true&userid=<?php echo session_id(); ?>" onclick="return confirm('This will ALL stored product files and cannot be undone.\n( <?php echo $filesQuery["totalRows"]; ?> found )\nContinue?')">Delete ALL</a>
<?php
    }
    // search 
?>
									<label for="fileSearchBox">Search: <input id="fileSearchBox" type="text" size="24" id="fileSearchBox" value=""></label>
									<?php // delete selected ?>
									<input type="submit" class="submitButton" value="Delete Selected" id="DelSelected" onclick="return warn();">
								</div>
								<div style="clear:right;"></div>
								<?php // hidden field verifies logged in user is the same one trying to post the deletion ?>
								<input type="hidden" value="<?php echo session_id(); ?>" name="deleteSelected">
								<?php // if we have some records to show ?>
								<table id="fileControlTable" class="CWsort" summary="<?php echo $_ENV["request.cwpage"]["baseUrl"]; ?>">
									<thead>
									<tr class="sortRow">
										<th class="filename">File</th>
										<th class="fileType">Type</th>
										<th class="fileDate">Modified</th>
										<th class="noSort">Products/Skus</th>
										<th class="fileSize">Size</th>
										<th class="noSort" width="50">Delete</th>
										<th class="fileInUse" width="50">In Use</th>
									</tr>
									</thead>
									<tbody>
<?php
    // OUTPUT THE FILES 
    for ($i=0; $i<$fileInfoQuery["totalRows"]; $i++) {
        // shade the used files 
        if ($fileInfoQuery["fileInUse"][$i]) {
            $rowClass = 'CWrowEven';
        } else {
            $rowClass = 'CWrowOdd';
        }
        // row has class of used or not used (odd/even) 
?>
									<tr class="<?php echo $rowClass." ".strtolower($fileInfoQuery["fileName"][$i]); ?>">
										<?php // file ?>
										<td class="noLink noHover">
<?php
        if (strlen(trim($fileInfoQuery["fileUrl"][$i]))) {
?>
												<a href="<?php echo $fileInfoQuery["fileUrl"][$i]; ?>" title="View/Download File"><?php echo $fileInfoQuery["fileName"][$i]; ?></a>
<?php
        } else {
            echo $fileInfoQuery["fileName"][$i];
        }
?>										</td>
										<?php // type ?>
										<td class="noLink noHover">
                                                                                    <?php echo $fileInfoQuery["fileType"][$i]; ?>
										</td>
										<?php // modified ?>
										<td class="noLink noHover">
                                                                                    <?php echo date($_ENV["application.cw"]["globalDateMask"], $fileInfoQuery["fileDate"][$i]); ?>
											<br><?php echo date("H:i:s", $fileInfoQuery["fileDate"][$i]); ?>
										</td>
										<?php // products ?>
										<td class="noLink noHover">
<?php
        $prodCt = 0;
        $prodIDlist = array();
        foreach ($fileInfoQuery["fileProducts"][$i] as $pp) {
            $prodId = substr($pp, 0, strpos($pp, "|"));
            $prodIDlist[] = $prodId;
            $prodName = substr($pp, strpos($pp, "|")+1);
            $prodCt++;
?>
												<a href="product-details.php?productid=<?php echo $prodId; ?>&showtab=4" title="View Product"><?php echo $prodName; ?></a><br>
<?php
        }
?>
										</td>
										<?php // size ?>
										<td class="noLink noHover"><?php if (is_numeric($fileInfoQuery["fileSize"][$i])) { echo number_format($fileInfoQuery["fileSize"][$i]/1000).'&nbsp;kb'; } else { ?>(N/A)<?php } ?></td>
										<?php // delete ?>
										<td style="text-align:center;">
											<input type="checkbox" name="dellist[<?php echo $i; ?>]" id="confirmBox<?php echo $i; ?>"
											class="formCheckbox delBox<?php if (!$fileInfoQuery["fileInUse"][$i]) { ?> idleBox<?php } ?>" value="<?php echo $fileInfoQuery["fileKey"][$i]; ?>"
											onclick="confirmDelete('confirmBox<?php echo $i ?>',<?php echo $prodCt; ?>)">
										</td>
										<?php // in use ?>
										<td class="noLink"><?php if ($fileInfoQuery["fileInUse"][$i]) { ?>Yes<?php } else { ?>No<?php } ?></td>
									</tr>
<?php
    }
?>
									</tbody>
								</table>
							</form>
<?php
}
// /END FILES TABLE 
?>
					</div>
					<!-- /end Page Content -->
				</div>
				<!-- /end CWinner -->
			</div>
<?php
// page end content / debug 
include("cwadminapp/inc/cw-inc-admin-page-end.php");
?>
			<!-- /end CWadminPage-->
			<div class="clear"></div>
		</div>
		<!-- /end CWadminWrapper -->
	</body>
</html>