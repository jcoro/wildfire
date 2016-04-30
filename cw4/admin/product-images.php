<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: product-images.php
File Date: 2012-07-03
Description: Handles image file viewing and management
Note:
CW stores a default preview thumbnail in the admin_preview folder
This is the directory we search for the list below. A different directory
may be passed in via the url.
Original images are stored in /orig/ - if size or storage are a concern,
these can be safely deleted via FTP with no harm to this page's functions
==========================================================
*/
require_once("Application.php");
// time out the page if it takes too long - avoid server overload 
if (!ini_get("safe_mode") && !in_array("set_time_limit", explode(",", ini_get("disable_functions")))) @set_time_limit(9000);
// GLOBAL INCLUDES 
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accesslevel"] = CWauth("manager,merchant,developer");
// PAGE PARAMS 
if(!isset($_ENV["application.cw"]["adminProductPaging"])) { $_ENV["application.cw"]["adminProductPaging"] = 1; }
if(!isset($_ENV["application.cw"]["adminRecordsPerPage"])) { $_ENV["application.cw"]["adminRecordsPerPage"] = 30; }
if(!isset($_GET['sortby'])) { $_GET['sortby'] = "imagetype_max_width"; }
if(!isset($_GET['sortdir'])) { $_GET['sortdir'] = "asc"; }
// interface mode (list|type) 
if(!isset($_GET['mode'])) { $_GET['mode'] = "list"; }
// default values for seach/sort
if(!isset($_GET['pagenumresults'])) { $_GET['pagenumresults'] = 1; }
if(!isset($_GET['search'])) { $_GET['search'] = ""; }
if(!isset($_GET['find'])) { $_GET['find'] = ""; }
if(!isset($_GET['maxrows'])) { $_GET['maxrows'] = $_ENV["application.cw"]["adminRecordsPerPage"]; }
if(!isset($_GET['sortby'])) { $_GET['sortby'] = "fileName"; }
if(!isset($_GET['sortdir'])) { $_GET['sortdir'] = "asc"; }
switch ($_GET['sortby']) {
	case 'origsize':
		$_GET['sortby'] = 'origSize';
		break;
	case 'filename':
		$_GET['sortby'] = 'fileName';
		break;
	case 'filedate':
		$_GET['sortby'] = 'fileDate';
		break;
	case 'fileinuse':
		$_GET['sortby'] = 'fileInUse';
		break;
}
// default values for display 
if(!isset($ImagePath)) { $ImagePath = ""; }
if(!isset($ImageSRC)) { $ImageSRC = ""; }
$session_id = session_id();
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,pagenumresults,userconfirm,useralert,dellist,delidle,delorig,delall,userid,mode");
// create the base url out of serialized url variables 
$_ENV["request.cwpage"]["baseURL"] = CWserializeUrl($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// image types only available to developer 
if (strtolower($_GET["mode"]) ==  'type' && $_SESSION["cw"]["accessLevel"] != 'developer') {
	header("Location: ".$_ENV["request.cwpage"]["baseURL"]."mode=list");
	exit;
}
// link for mode switch 
if(!isset($_ENV["request.cwpage"]["viewLink"])) { $_ENV["request.cwpage"]["viewLink"] = ""; }
// PROCESSING FOR PAGE MODE 
switch (strtolower($_GET["mode"])) {
	// IMAGE TYPES/SIZES 
	case "type":
		// QUERY: get all image types 
		$imageTypesQuery = CWquerySelectImageTypes();
		$_ENV["request.cwpage"]["subHead"] = 'Set maximum image height and width';
		$_ENV["request.cwpage"]["viewLink"] = '<a href="'.$_ENV["request.cwpage"]["baseURL"].'&mode=list">View Image List</a>';
		// /////// 
		// UPDATE IMAGE TYPES 
		// /////// 
		if (isset($_POST["imagetype_idlist"]) && sizeof($_POST["imagetype_idlist"])) {
			$loopCt = 0;
			$updateCt = 0;
			foreach ($_POST["imagetype_idlist"] as $key => $id) {
				// verify numeric sort order 
				if (!is_numeric($_POST["imagetype_sortorder".$loopCt])) {
					$_POST["imagetype_sortorder".$loopCt] = 0;
				}
				// QUERY: update imagetype record 
				try {
					$updateType = CWqueryUpdateImageType(
									$_POST["imagetype_id".$loopCt],
									$_POST["imagetype_upload_group".$loopCt],
									null,
									$_POST["imagetype_sortorder".$loopCt],
									null,
									$_POST["imagetype_max_width".$loopCt],
									$_POST["imagetype_max_height".$loopCt]
									);
					$updateCt++;
				// handle errors 
				} catch (Exception $e) {
					CWpageMessage("alert","Image Type Update Error ".$e->getMessage());
				}
				$loopCt++;
			}
			// get the vars to keep by omitting the ones we don't want repeated 
			$varsToKeep = CWremoveUrlVars("userconfirm,useralert");
			// set up the base url 
			$_ENV["request.cwpage"]["relocateUrl"] = CWserializeUrl($varsToKeep,$_ENV["request.cw"]["thisPage"]);
			// return to page as submitted, clearing form scope 
			if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
			if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
			header("Location: ".$_ENV["request.cwpage"]["relocateUrl"]."&userconfirm=".CWurlSafe('Changes Saved')."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
			exit;
		}
		// /////// 
		// /END UPDATE IMAGE TYPES 
		// /////// 
		break;
		
	default:
		// LIST VIEW 
		// variable to show image thumbnails in list view 
		if(!isset($_GET['showImages'])) { $_GET['showImages'] = $_ENV["application.cw"]["adminProductImageSelectorThumbsEnabled"]; }
		// set up base folder locations 
		// use this directory to show image preview in list 
		if(!isset($_GET['previewFolder'])) { $_GET['previewFolder'] = "admin_preview"; }
		// use this directory to link to original uploaded files  
		$_REQUEST['origFolder'] = "orig";
		// list folder can be passed in through URL: CW uses the default 'admin preview' to check all images 
		if(!isset($_GET['listFolder'])) { $_GET['listFolder'] = "admin_preview"; }
		// parent URL must end with a trailing slash, i.e. / 
		$_REQUEST['imgParentUrl'] = $_ENV["request.cwpage"]["adminImgPrefix"].$_ENV["application.cw"]["appImagesDir"]; 
		$_REQUEST['imgParentPath'] = realpath($_REQUEST['imgParentUrl']);
		$systemSlash = "/";
		if (strpos($_REQUEST['imgParentPath'], "\\") !== false) $systemSlash = "\\";
		// the directory to pull preview images from 
		$_REQUEST['imgPreviewDir'] = $_REQUEST['imgParentUrl']."/".$_GET['previewFolder']."/";
		// the folder to look at to build our list 
		$_REQUEST['imgListPath'] = $_REQUEST['imgParentPath'].$systemSlash.$_GET['listFolder'].$systemSlash;
		// the folder to look in for original images 
		$_REQUEST['imgOrigPath'] = $_REQUEST['imgParentPath'].$systemSlash.$_REQUEST['origFolder'].$systemSlash;
		// the directory to pull original images from 
		$_REQUEST['imgOrigDir'] = $_REQUEST['imgParentUrl']."/".$_REQUEST['origFolder']."/";
		// QUERY: get Images in specified preview directory 
		$_REQUEST['imgParentPath'] .= $systemSlash;
		$dir = opendir($_REQUEST['imgListPath']);
		$imagesQuery = array();
		while (false !== ($filename = readdir($dir))) {
		  if ( strlen($filename)>0 && is_file($_REQUEST['imgListPath'].$filename) && strtolower($filename) != strtolower($_ENV["application.cw"]["appImageDefault"]) && isImageFile($filename)) {    
			  $imagesQuery[] = $filename;
		  }
		}
		$dir1 = opendir($_REQUEST['imgOrigPath']);
		$origQuery = array();
		while (false !== ($filename1 = readdir($dir1))) {
		  if ( strlen($filename1)>0 && is_file($_REQUEST['imgOrigPath'].$filename1) && strtolower($filename1) != strtolower($_ENV["application.cw"]["appImageDefault"]) && isImageFile($filename1)) {
			  $origQuery[] = $filename1;
		  }
		}
		$productImagesQuery = CWquerySelectProductImages();
		// QUERY: get only unique image filenames 	
		$usedImagesQuery = array("totalRows" => 0, "product_image_filename" => array());
		for( $i = 0; $i < $productImagesQuery['totalRows']; $i++ ) {
			if (!in_array($productImagesQuery["product_image_filename"][$i], $usedImagesQuery["product_image_filename"])) {
				$usedImagesQuery["product_image_filename"][] = $productImagesQuery["product_image_filename"][$i];
			}
		}
		$usedImagesQuery["totalRows"] = count($usedImagesQuery["product_image_filename"]);
		// set up lists of filenames used for output 
		$_ENV["request.cwpage"]['allFileNames'] = $imagesQuery;
		$_ENV["request.cwpage"]['origFileNames'] = $origQuery;
		$_ENV["request.cwpage"]['usedFileNames'] = array();
		$_ENV["request.cwpage"]['idleFileNames'] = array();
		// set up lists of user levels that can delete originals 
		$_ENV["request.cwpage"]['deleteOrigLevels'] = 'developer';
		if(isset($_ENV["application.cw"]["adminImagesMerchantDeleteOrig"]) && ($_ENV["application.cw"]["adminImagesMerchantDeleteOrig"]==true)) {
			$_ENV["request.cwpage"]['deleteOrigLevels'] .= ',merchant';
		}
		// create our display query w/ image and product info 
		// loop the directory listing and insert specific info 
		$imageInfoQuery = array("totalRows" => 0, "fileName" => array(), "fileDate" => array(), "origSrc" => array(), "origSize" => array(), "origUrl" => array(), "previewUrl" => array(), "fileProducts" => array(), "fileInUse" => array()); // collect the results
		$imageExtensions = array("jpg","jpeg","pjpeg","pjpg","gif","png","tiff","bmp");
		for ( $j=0; $j < count($imagesQuery); $j++ ) {
			$name = $imagesQuery[$j];
			// only deal with known file types, and protect default image 
			$ext = substr($name, strrpos($name, "."));
			if( (in_array(strtolower($ext), $imageExtensions)===true) 
				&& (!isset($_ENV["application.cw"]["appImageDefault"])) || (strtolower($name) != strtolower($_ENV["application.cw"]["appImageDefault"]))) {
				// set up some info about this image 
				$img['fileName'] =$name;
				$fullFileName =  $_REQUEST['imgListPath'].$systemSlash.$img['fileName'];
				$dateString = $_ENV["application.cw"]["globalDateMask"]."".$_ENV["application.cw"]["globalTimeMask"];
				$img['fileDate'] = date( $dateString, filemtime($fullFileName));
				$img['previewFileSize'] = number_format((filesize($fullFileName)/1000), 2, '.', '').'&nbsp;kb';
				// if the original image exists  
				if(in_array($img['fileName'], $_ENV["request.cwpage"]['origFileNames'])===true) {	
					// get info about original 
					$img['origSrc']	=   $_REQUEST['imgOrigPath'].$img['fileName'];
					$img['origSize']=   filesize($img['origSrc']);
					$img['origUrl']	=   $_REQUEST['imgOrigDir'].$img['fileName'];		
				} else {
					$img['origSrc']	= '';
					$img['origSize'] = '';
					$img['origUrl']	= '';		
				}
				// set up preview url 
				$img['previewUrl'] = $_REQUEST['imgPreviewDir'].$img['fileName'];
				// end original image info 
				// is the image in use? 
				$img['fileProducts'] = array();
				if (in_array($img['fileName'], $usedImagesQuery['product_image_filename']) === true ) {
					$img['inUse'] = true;
					// if so get the products it is being used on 
					$imageProds = array("totalRows" => 0, "prodID" => array());
					for( $i=0; $i < $productImagesQuery['totalRows']; $i++ ) {
						if (!in_array($productImagesQuery["product_image_product_id"][$i], $imageProds["prodID"])
								&& strcmp($productImagesQuery["product_image_filename"][$i], $img['fileName'])==0 ) {
							$imageProds["prodID"][] = $productImagesQuery["product_image_product_id"][$i];
						}
					}
					$imageProds["totalRows"] = count($imageProds["prodID"]);
					// get the names for each product ID found 
					for($p=0;$p<$imageProds["totalRows"];$p++) {  
						$prodQuery = CWqueryGetRS("SELECT product_name FROM cw_products WHERE product_id = '".CWqueryParam($imageProds["prodID"][$p])."'");
						for( $q=0; $q<$prodQuery['totalRows']; $q++) {
							$img['fileProducts'][] = array($imageProds["prodID"][$p], $prodQuery['product_name'][$q]);
						}
					}
					$_ENV["request.cwpage"]['usedFileNames'][] = $img['fileName'];
				} else {
					// if not in use 	
					$img['inUse'] = false;
					$_ENV["request.cwpage"]['idleFileNames'][] = $img['fileName'];
				}
				// end if in use 
				// add data to display query 
				$imageInfoQuery['fileName'][] = $img['fileName'];
				$imageInfoQuery['fileDate'][] = $img['fileDate'];
				$imageInfoQuery['origSrc'][] = $img['origSrc'];
				$imageInfoQuery['origSize'][] = $img['origSize'];
				$imageInfoQuery['origUrl'][] = $img['origUrl'];
				$imageInfoQuery['previewUrl'][] = $img['previewUrl'];
				$imageInfoQuery['fileProducts'][] = $img['fileProducts'];
				$imageInfoQuery['fileInUse'][] = $img['inUse'];
				$imageInfoQuery["totalRows"]++;
			}
			// /end file type check 
		}
		// end create info query 
		// QUERY: make sortable 
		$imageInfoQuery = CWsortableQuery($imageInfoQuery);
		// /////// 
		// DELETE IMAGES 
		// /////// 
		$_ENV["request.cwpage"]['deleteList'] = array();
		// delete by form 
		if(!isset($_POST['dellist'])) { $_POST['dellist'] = array(); }
		if((isset($_POST['deleteSelected'])) && $_POST['deleteSelected'] == $session_id) {
			$_ENV["request.cwpage"]['deleteList'] = $_POST['dellist'];
		}
		// delete unused / original 
		if(!isset($_GET['delidle'])) { $_GET['delidle'] = "";}
		if(!isset($_GET['delorig'])) { $_GET['delorig'] = "";}
		if(!isset($_GET['userid'])) { $_GET['userid'] = "";}
		// verify user posting the url is the user logged in 
		if($_GET['delidle'] == 'true' && $_GET['userid'] == $session_id) {
			$_ENV["request.cwpage"]['deleteList'] = $_ENV["request.cwpage"]['idleFileNames'];
		}
		if($_GET['delorig'] == 'true' && $_GET['userid'] == $session_id) {
			$_ENV["request.cwpage"]['deleteList'] = $_ENV["request.cwpage"]['origFileNames'];
		}
		// delete ALL - only for developer 
		if (!isset($_GET["delall"])) $_GET["delall"] = "";
		if (!isset($_GET["userid"])) $_GET["userid"] = "";
		// verify user posting the url is the user logged in 
		if((ListFindNoCase('merchant,developer',$_ENV["request.cwpage"]["accesslevel"])) && $_GET['delall'] == "true" && $_GET['userid'] == $session_id) {
			$_ENV["request.cwpage"]['deleteList'] = $_ENV["request.cwpage"]['allFileNames'];
		}
		// if we have a list of at least one filename 
		if(sizeof($_ENV["request.cwpage"]['deleteList'])) {
			// if deleting only originals (delorig) 
			if($_GET['delorig'] == 'true') {
				$dirsList = $_REQUEST['origFolder'];
			} else {
				// if deleting from all locations (standard delete) 
				// set up the list of directories to delete from 
				// get all image directories 
				$imgDirsQuery = CWquerySelectImageTypes();
				// get unique list of folder names 
				$imgDirs = array("totalRows" => 0, "imagetype_folder" => array());
				for ($n=0; $n<$imgDirsQuery["totalRows"]; $n++) {
					if (!in_array($imgDirsQuery["imagetype_folder"][$n], $imgDirs["imagetype_folder"])) {
						$imgDirs["imagetype_folder"][] = $imgDirsQuery["imagetype_folder"][$n];
					}
				}
				// set up list of directories to look in 
				$dirsList = $imgDirs["imagetype_folder"];
				// add  our defaults for original and preview images 
				$dirsList[] = $_REQUEST['origFolder'];
				$dirsList[] = $_GET['previewFolder'];
			}
			$delCt = 0;
			// convert to arrays for processing
			$delArray = $_ENV["request.cwpage"]['deleteList'];
			$dirsArray = $dirsList;
			foreach ($delArray as $key => $delFile) {
				try {
					$delFile = trim($delFile);
					// don't allow default file to be deleted 
					if((!isset($_ENV["application.cw"]["appImageDefault"])) || strtolower($_ENV["application.cw"]["appImageDefault"]) != strtolower($delFile)) {
						// delete the actual files from the server 
						for($ff=0; $ff<count($dirsArray); $ff++) {
							// set up the image file to delete 
							$delSrc = $_REQUEST['imgParentPath'].$dirsArray[$ff].$systemSlash.$delFile;
							// if the file exists, delete it 
							if(file_exists($delSrc)) {
								unlink($delSrc);
							}
						}
					}
					// / end protect default file 
					// delete all rel product/image records with this filename 
					// not if deleting only originals (delorig) 
					if($_GET['delorig'] != 'true' ) {
						$deleteImgFileRel = CWqueryDeleteProductImageFile($delFile);
					}
					$delCt++;
				// handle errors 
				} catch(Exception $e) {
					CWpageMessage("alert","Error: '".$e->getMessage()."'");
				}
			}
			if($delCt > 0) {
				if($delCt == 1) { $s = ''; } else { $s = 's'; }
				CWpageMessage("confirm","".$delCt." image".$s." deleted");
				if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
				if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
				header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."");
				exit;
			}
		}
		// /////// 
		// /END DELETE IMAGES 
		// /////// 
		// SUBHEADING 
		$_ENV["request.cwpage"]["subHead"] = "
			Images Uploaded: ".count($imagesQuery)."&nbsp;&nbsp
			In Use: ".count($_ENV["request.cwpage"]['usedFileNames'])."&nbsp;&nbsp
			Not In Use: ".count($_ENV["request.cwpage"]['idleFileNames'])."&nbsp;&nbsp";
		// link to edit image types 
		if ($_SESSION["cw"]["accessLevel"] == 'developer') {
			$_ENV["request.cwpage"]["viewLink"]='<a href="'.$_ENV["request.cwpage"]["baseURL"].'&mode=type">Manage Image Sizes</a>';
		}
		// /end list view --->
		break;
}
// /end mode 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Manage Product Images";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Image Management";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = $_ENV["request.cwpage"]["subHead"];
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = 'product-images.php';
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
include("cwadminapp/inc/cw-inc-admin-scripts.php");
// fancybox
?>
		<link href="js/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js"></script>
		<?php // PAGE JAVASCRIPT ?>
		<script type="text/javascript">
			// this takes the ID of a checkbox, the number to show in the alert
			function confirmDelete(boxID,prodCt) {
			// if this cat has products
				if (prodCt > 0) {
					if (prodCt > 1) {var prodWord = 'products'}else{var prodWord = 'product'};
				var confirmBox = '#'+ boxID;
					// if the box is checked and prodToggle is true
					if( jQuery(confirmBox).is(':checked') ) {
					clickConfirm = confirm("Warning: image in use!\nThis image will be unassigned for " + prodCt + " " + prodWord + ".\nContinue?");
					// if confirm is returned false
					if(!clickConfirm) {
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
	    if (numberChecked > 0) {
	    			if (numberChecked > 1) {var imgWord = 'images'}else{var imgWord = 'image'};
	    return confirm("Delete " + numberChecked+ " " + imgWord + " and unassign for all products?");
	    }else{
	    alert('No images selected');
		return false;
	    }
	};

// end script

// page jQuery
jQuery(document).ready(function() {
	// fancybox
		jQuery('a.zoomImg').each(function() {
			jQuery(this).fancybox({
			'titlePosition': 'inside',
			'padding': 3,
			'overlayShow': true,
			'showCloseButton': true,
			'hideOnOverlayClick':true,
			'hideOnContentClick': true
			});
		});
// containsNoCase function courtesy Rick Strahl:
// http://www.west-wind.com/weblog/posts/519980.aspx
$.expr[":"].containsNoCase = function(el, i, m) {
    var search = m[3];
    if (!search) return false;
    return eval("/" + search + "/i").test(jQuery(el).text());
};
// image search box
jQuery('#imageSearchBox').keyup(function(){
// use lowercase version of input string, to match classes on table rows (see 'tr' below)
var searchText = jQuery(this).val().toLowerCase();
// if blank, show all rows again
if (searchText == ''){
jQuery('#imageControlTable tbody tr').show();
}
// or filter all rows
else
{
// hide all rows, show the rows that match
jQuery('#imageControlTable tbody tr').hide();
// works for matching text or class
jQuery("#imageControlTable tbody tr:containsNoCase('"+searchText+"')").show();
//jQuery("#imageControlTable tbody tr:containsNoCase('"+ jQuery(this).val()+"')").show();
jQuery("#imageControlTable tbody tr[class*='"+ jQuery(this).val()+"']").show();
jQuery("#imageControlTable tbody tr a:containsNoCase('"+ jQuery(this).val()+"')").parents('tr').show();
}
});
// end image search

});
// end script
</script>
	</head>
<?php
// body gets a class to match the filename 
$page = explode('.',$_ENV["request.cw"]["thisPage"]); 
$pageFirst = $page[0];
?>
	<body <?php echo 'class=' .$pageFirst; ?>>
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
if(strlen(trim($_ENV["request.cwpage"]["heading1"]))) { echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]).'</h1>'; }
if(strlen(trim($_ENV["request.cwpage"]["heading2"]))) { echo '<h2>'.trim($_ENV["request.cwpage"]["heading2"]).$_ENV["request.cwpage"]["viewLink"].'</h2>'; }
// user alerts 
include("cwadminapp/inc/cw-inc-admin-alerts.php");
?>
					<!-- Page Content Area -->
					<div id="CWadminContent">
						<!-- //// PAGE CONTENT ////  -->
<?php
switch (strtolower($_GET["mode"])) {
	// IMAGE TYPES/SIZES 
	case "type":
?>
								<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>&mode=<?php echo $_GET["mode"]; ?>" name="imgTypesForm" id="imgTypesForm" method="post" class="CWobserve">
									<h3>Manage Image Sizes &amp; Dimensions</h3>
									<?php // submit button ?>
									<div class="CWadminControlWrap">
										<input name="UpdateImageTypes" type="submit" class="CWformButton" id="UpdateImageTypes" value="Save Changes">
										<div style="clear:right;"></div>
									</div>
<?php
		// /END submit button 
		// if no records found, show message 
		if (!$imageTypesQuery["totalRows"]) {
?>
										<p>&nbsp;</p>
										<p>&nbsp;</p>
										<p>&nbsp;</p>
										<p><strong>No image dimensions available</strong><br><br></p>
<?php
		} else {
			// SHOW IMAGE TYPES 
			$imageTypesQuery = CWsortableQuery($imageTypesQuery);
?>
									<table class="CWsort CWstripe" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>&mode=<?php echo $_GET["mode"]; ?>">
									<thead>
									<tr class="sortRow">
										<th class="noSort">Image Type</th>
										<th class="imagetype_max_height">Max. Height</th>
										<th class="imagetype_max_width">Max. Width</th>
									</tr>
									</thead>
									<tbody>
<?php
			$loopCt = 0;
			for ($i=0; $i<$imageTypesQuery["totalRows"]; $i++) {
				if ($imageTypesQuery["imagetype_user_edit"][$i] == 1) {
?>
									<tr>
										<td>
											<strong><?php echo $imageTypesQuery["imagetype_name"][$i]; ?></strong>
											<input type="hidden" name="imagetype_upload_group<?php echo $loopCt; ?>" value="<?php echo $imageTypesQuery["imagetype_upload_group"][$i]; ?>">
											<input type="hidden" name="imagetype_sortorder<?php echo $loopCt; ?>" value="<?php echo $imageTypesQuery["imagetype_sortorder"][$i]; ?>">
											<input type="hidden" name="imagetype_id<?php echo $loopCt; ?>" value="<?php echo $imageTypesQuery["imagetype_id"][$i]; ?>">
											<input type="hidden" name="imagetype_idlist[<?php echo $loopCt; ?>]" value="<?php echo $imageTypesQuery["imagetype_id"][$i]; ?>">
										</td>
										<td><input name="imagetype_max_height<?php echo $loopCt; ?>" type="text" size="15" value="<?php echo $imageTypesQuery["imagetype_max_height"][$i]; ?>" onkeyup="extractNumeric(this,0,true);"></td>
										<td><input name="imagetype_max_width<?php echo $loopCt; ?>" type="text" size="15" value="<?php echo $imageTypesQuery["imagetype_max_width"][$i]; ?>" onkeyup="extractNumeric(this,0,true);"></td>
									</tr>
<?php
					$loopCt++;
				}
			}
?>
									</tbody>
									</table>
									<span class="smallPrint" style="float:right;">
											Note: changes made here will not affect previously uploaded images
									</span>
<?php
		}
		// hidden input with all editable image sizes 
?>
								</form>
<?php
		// /end image types/sizes 
		break;
		
	// IMAGES TABLE 
	default:
		// if no records found, show message 
		if(!count($imagesQuery)) {
?>    
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p><strong>No images found.</strong></p>
<?php 
		} else {
			// form submits to same page 
?>
			    <form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" name="imgForm" id="imgForm" method="post" class="CWobserve">
				<div class="CWadminControlWrap productImageControls">
<?php
			// delete originals: if developer (or merchant if allowed) 
			if (ListFindNoCase($_ENV["request.cwpage"]['deleteOrigLevels'], $_ENV["request.cwpage"]["accesslevel"])) {
?>
					    <a class="CWbuttonLink deleteButton" href="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>&delorig=true&userid=<?php echo $session_id; ?>" onClick="return confirm('This will delete all stored original-size images.\nProduct display will not be altered, but original images will no longer be available.\n( <?php echo sizeof($_ENV["request.cwpage"]["origFileNames"]); ?> found )\nContinue?')">Delete Originals</a>
<?php
			}
			// delete unused (idle) images 
?>
				    <a class="CWbuttonLink" href="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>&delidle=true&userid=<?php echo $session_id; ?>" onClick="return confirm('This will delete all stored images not connected with any products.\n( <?php echo sizeof($_ENV["request.cwpage"]["idleFileNames"]); ?> found )\nContinue?')">Delete Unused</a>

<?php
			// delete all images 
			if (ListFindNoCase('merchant,developer', $_ENV["request.cwpage"]["accesslevel"])) {
?>
					    <a class="CWbuttonLink" href="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>&delall=true&userid=<?php echo $session_id; ?>" onClick="return confirm('This will delete ALL stored product images and cannot be undone.\n( <?php echo sizeof($imagesQuery); ?> found )\nContinue?')">Delete ALL</a>
<?php
			}
			// search
?>
				    <label for="imageSearchBox">Search: <input id="imageSearchBox" type="text" size="24"  value=""></label>
				    <?php // delete selected ?>
				    <input type="submit" class="submitButton" value="Delete Selected" id="DelSelected" onclick="return warn();">
				</div>
				<div style="clear:right;"></div>
				<?php // hidden field verifies logged in user is the same one trying to post the deletion ?>
				<input type="hidden" value="<?php echo $session_id; ?>" name="deleteSelected">
				<?php // if we have some records to show ?>
				<table id="imageControlTable" class="CWsort" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
				    <thead>
					<tr class="sortRow">
					    <th class="noSort" width="80" style="text-align:center;">Image</th>
					    <th class="filename">File</th>
					    <th class="fileDate">Modified</th>
					    <th class="noSort">Products</th>
					    <th class="origsize">Size</th>
					    <th class="noSort" width="50">Delete</th>
					    <th class="fileInUse" width="50">In Use</th>
					</tr>
				    </thead>
				    <tbody>
<?php
			// OUTPUT THE IMAGES 
			for ($i = 0; $i < $imageInfoQuery["totalRows"]; $i++) {
				// shade the used images 
				if ($imageInfoQuery['fileInUse'][$i] == true) {
					$rowClass = 'CWrowEven';
				} else {
					$rowClass = 'CWrowOdd';
				}
				// row has class of used or not used (odd/even) 
?>
                                              <tr class="<?php echo $rowClass?> <?php echo strtolower($imageInfoQuery['fileName'][$i]); ?>">

						<?php // image ?>
						<td style="text-align:center;" class="imageCell noLink noHover">
<?php
				if ((strlen(trim($imageInfoQuery['origSrc'][$i]))) && file_exists($imageInfoQuery['origSrc'][$i])) {
?>
						    <a href="<?php echo $imageInfoQuery['origUrl'][$i]; ?>" title="<?php echo CWstringFormat($imageInfoQuery['fileName'][$i]); ?>: Original Image" class="zoomImg"><img src="<?php echo $imageInfoQuery['previewUrl'][$i]; ?>" alt="image thumbnail"></a>
<?php
				} else {
?>
						    <img src="<?php echo $imageInfoQuery['previewUrl'][$i]; ?>" alt="image thumbnail">
<?php
				}
?>
						</td>
						<?php // file ?>
						<td class="noLink noHover">
<?php
				if ((strlen(trim($imageInfoQuery['origSrc'][$i]))) && file_exists($imageInfoQuery['origSrc'][$i])) {
?>
						    <a href="<?php echo $imageInfoQuery['origUrl'][$i]; ?>" title="<?php echo CWstringFormat($imageInfoQuery['fileName'][$i]); ?>: Original Image" class="detailsLink zoomImg"><?php echo $imageInfoQuery['fileName'][$i]; ?></a>
<?php
			    } else {
					echo $imageInfoQuery['fileName'][$i];
			    }
?>
						</td>
						<?php // modified ?>
						<td class="noLink noHover">
<?php
			    echo cartweaverDate($imageInfoQuery['fileDate'][$i])."
						<br>".strftime("%X", strtotime($imageInfoQuery['fileDate'][$i]));
?>
						</td>
						<?php // products ?>
						<td class="noLink noHover">
<?php
				$prodCt = 0;
				$prodIDlist = array();
				foreach ($imageInfoQuery['fileProducts'][$i] as $key => $pp) {
					$prodId = $pp[0];
					$prodIDlist[] = $prodId;
					$prodName = $pp[1];
					$prodCt++;
?>
						    <a href="product-details.php?productid=<?php echo $prodId; ?>&showtab=3" title="View Product"><?php echo $prodName; ?></a><br>
<?php
				}
?> 
						</td>
						<?php // size ?>
						<td class="noLink noHover">
<?php
				if (is_numeric($imageInfoQuery['origSize'][$i])) {
					echo mySQLFloat(round($imageInfoQuery['origSize'][$i] / 1000, 2)) . '&nbsp;kb';
				} else {
					echo '(N/A)';
				}
?>
						</td>
						<?php // delete ?>
						<td style="text-align:center;">
						    <input type="checkbox" name="dellist[<?php echo $i; ?>]" id="confirmBox<?php echo $i; ?>"
							   class="formCheckbox delBox<?php if (!$imageInfoQuery['fileInUse'][$i]) { ?> idleBox <?php } ?>"  value="<?php echo $imageInfoQuery['fileName'][$i]; ?>"
							   onclick="confirmDelete('confirmBox<?php echo $i; ?>',<?php echo $prodCt; ?>)">
						</td>
						<?php // in use ?>
						<td class="noLink">
<?php
				if ($imageInfoQuery['fileInUse'][$i]) {
					echo "Yes";
				} else {
					echo "No";
				}
?>
						</td>
					    </tr>
<?php
			}
?>
				</tbody>
			    </table>
			</form>                            
<?php 
		}
		// /END IMAGES TABLE 
		break;
}
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
