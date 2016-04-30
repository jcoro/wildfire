<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: product-image-select.php
File Date: 2012-05-01
Description: Allows preview/selection of images and sets
the name of the image in the records of the selected product.
Included via iFrame into product-details.php image upload areas
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// time out the page if it takes too long - avoid server overload 
if (!ini_get("safe_mode") && !in_array("set_time_limit", explode(",", ini_get("disable_functions")))) @set_time_limit(9000);
// BASE URL 
// for this page the base url is the standard current page variable 
$_ENV["request.cwpage"]["baseURL"] = $_ENV["request.cw"]["thisPage"];
// set up the upload group to insert filename to 
if(!isset($_GET['uploadGroup']) || $_GET['uploadGroup'] == '') { $_GET['uploadGroup'] = 1; }
$groupNo = $_GET['uploadGroup'];
// variable to show list of image thumbnails y/n 
// (note: the preview thumbnail is always shown on the product form) 
if(!isset($_GET['showImages']) || $_GET['showImages'] == '') { $_GET['showImages'] = $_ENV["application.cw"]["adminProductImageSelectorThumbsEnabled"]; }
// set up base folder locations 
// parent URL must end with a trailing slash, i.e. "../Assets/" 
if(!isset($_GET['previewFolder'])) { $_GET['previewFolder'] = "admin_preview"; }
// list folder is passed in based on smallest image size from product form 
if(!isset($_GET['listFolder'])) { $_GET['listFolder'] = "admin_preview"; }
$_REQUEST['imgParentUrl'] = $_ENV["request.cwpage"]["adminImgPrefix"].$_ENV["application.cw"]["appImagesDir"];
$_REQUEST['imgParentPath'] = realpath($_REQUEST['imgParentUrl']);
$systemSlash = "/";
if (strpos($_REQUEST['imgParentPath'], "\\") !== false) $systemSlash = "\\";
$_REQUEST['imgSelectPath'] = $_REQUEST['imgParentPath'].$systemSlash.$_GET['listFolder'].$systemSlash;
$imgDir = $_REQUEST['imgParentUrl']."/".$_GET['previewFolder']."/";
// get images 
$dir = opendir($_REQUEST['imgSelectPath']);
$selectImageList = array();
while (false !== ($files = readdir($dir))) {
	if (!is_dir($_REQUEST['imgSelectPath'].$files)) {
	    $selectImageList[] = $files;
	}
}	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Product Image Selector</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"];?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php
include("cwadminapp/inc/cw-inc-admin-scripts.php");
// javascript 
?>
		<script type="text/javascript">
		jQuery(document).ready(function() {
		// set up referring image function
			var callingInputID = '#Image<?php echo $groupNo; ?>';
			// this function puts a value in an element on the parent page
			var $insertFunction= function(elementVar,textVar) {
			//debug: alert text to insert, or element being targeted
			//alert(elementVar);
			//alert(textVar);
			jQuery(elementVar,parent.document.body).val(textVar);
			};
		// end referring input function

		// select image from list (dropdown)
		jQuery('#imageSelect').change(function() {
			var filenameString = jQuery(this).attr('value');
			var showImgURL = '<?php echo $imgDir;?>' + filenameString;
			// call the function, insert the filename to the calling input
			$insertFunction(callingInputID,filenameString);
			// show the image
			jQuery('#selectPreviewImg').show().attr('src',showImgURL).attr('alt',showImgURL);
		});
		// end select image list
<?php if($_GET['showImages']) { ?>		
				// select image link
				jQuery('#imageListTableWrap a.imgSelect').click(function() {
				var filenameString = jQuery(this).attr('href');
				var showImgURL = '<?php echo $imgDir;?>' + filenameString;
				// call the function, insert the filename to the calling input
				$insertFunction(callingInputID,filenameString);
				jQuery(this).parents('tr').addClass('currentImg').siblings('tr').removeClass('currentImg');
				return false;
				});
				// end select image link
				// image search box
				jQuery('#imageSearchBox').keyup(function() {
				// use lowercase version of input string, to match classes on table rows (see 'tr' below)
				var searchText = jQuery(this).val().toLowerCase();
				// if blank, show all rows again
				if (searchText == '') {
				jQuery('#imageListTable tr').show().removeClass('currentImg');
				}
				// or filter all rows
				else
				{
				// hide all rows, show the rows that match
				jQuery('#imageListTable tr').hide().removeClass('currentImg');
				jQuery("#imageListTable tr[class*='"+searchText+"']").show().removeClass('currentImg');
				}
				});
				// end image search
				// clicking anywhere in row same as clicking link
				jQuery('#imageListTable tr td').click(function() {
				jQuery(this).children('a:first').click();
				});
				// end click anywhere in row
				// image size links
				jQuery('#imgSizeControlUp').click(function() {
				var sizeTo = jQuery(this).attr('rel');
				jQuery('#imageListTableWrap a.imgSelect img').css('max-width', sizeTo + 'px').parents('td').css('width',sizeTo + 'px');
				if (sizeTo <= 160) {
				jQuery('#imgSizeControlDown').attr('rel',sizeTo/2);
				};
				if (sizeTo < 160) {
				var altSize = sizeTo * 2;
				jQuery(this).attr('rel',altSize);
				};
				return false;
				});
				jQuery('#imgSizeControlDown').click(function() {
				var sizeTo = jQuery(this).attr('rel');
				jQuery('#imageListTableWrap a.imgSelect img').css('max-width', sizeTo + 'px').parents('td').css('width',sizeTo + 'px');
				if (sizeTo > 20) {
				jQuery('#imgSizeControlUp').attr('rel',sizeTo);
				};
				if (sizeTo > 20) {
				var altSize = sizeTo / 2;
				jQuery(this).attr('rel',altSize);
				};
				return false;
				});
				// end image size links
	<?php 	} ?>
			// toggle between list or table view
			jQuery('#showImgTableLink').click(function() {
			jQuery('#imageSelectWrap').hide();
			jQuery('#imageSearchWrap').show();
			jQuery('#imageListTableWrap').show();
			return false;
			});
			jQuery('#showImgListLink').click(function() {
			jQuery('#imageSelectWrap').show();
			jQuery('#imageSearchWrap').hide();
			jQuery('#imageListTableWrap').hide();
			return false;
			});
			// end toggle list/table
			
			});
		</script>
	</head>
	<body id="CWimageSelectWrap">
		<p>Select an Image <?php if($_SESSION["cw"]["debug"]) { ?><span class="smallPrint">Directory: <?php echo $imgDir;?></span><?php } ?></p>
		<?php // SELECT LIST ?>
		<form id="CWimageSelectForm" name="CWimageSelectForm" onsubmit="return false">
			<!-- image selector -->
			<span id="imageSelectWrap" <?php if($_GET['showImages']) { ?>style="display:none;"<?php } ?>>
				Choose:
				<select id="imageSelect" name="imageSelect" onkeyup="this.blur();this.focus();">
<?php				
for($i=0; $i<count($selectImageList); $i++) {
	$name = $selectImageList[$i];
	$type = filetype($imgDir.$name);
	if( ($type == "file") 	
		&& isImageFile($imgDir.$name) 
		&& (!isset($_ENV["application.cw"]["appImageDefault"]) || !(strtolower($name) == strtolower($_ENV["application.cw"]["appImageDefault"])) )
		) {
?>
					<option value="<?php echo $name; ?>"><?php echo $name; ?></option>
<?php
	}
}
?>             	
				</select>
<?php
if($_GET['showImages']) {
?>  
					&nbsp;&nbsp;<a class="smallPrint" id="showImgTableLink" href="##">Show Images</a>
<?php
}	
// the preview image 
?>
				<div>
                	<img src="" alt="" class="productImagePreview" id="selectPreviewImg" style="display:none;">
				</div>
			</span>
			<!-- image search, hidden on load -->
			<span id="imageSearchWrap" <?php if(!$_GET['showImages']) { ?> style="display:none;"<?php } ?>>
				Size:&nbsp;&nbsp;&nbsp;<a href="#" rel="80" id="imgSizeControlUp"><img src="img/cw-size-up.png" alt="enlarge images"></a>
				&nbsp;<a rel="20" href="#" id="imgSizeControlDown"><img src="img/cw-size-down.png" alt="reduce images"></a>
				&nbsp;&nbsp;&nbsp;&nbsp;Search: <input id="imageSearchBox" name="imageSearchBox" size="15" value="">
				&nbsp;&nbsp;<a class="smallPrint" id="showImgListLink" href="##">Simple List</a>
			</span>
		</form>
		<?php // TABLE with thumbnails ?>
		<div id="imageListTableWrap" <?php if(!$_GET['showImages']) { ?> style="display:none;"<?php } ?>>
			<table class="CWformTable narrow" id="imageListTable">
<?php 
for($i=0; $i<count($selectImageList); $i++) {
	$name = $selectImageList[$i];
	$type = filetype($imgDir.$name); 
	
	if( ($type == "file") 	
		&& isImageFile($imgDir.$name) 
		&& (!isset($_ENV["application.cw"]["appImageDefault"]) || !(strtolower($selectImageList[$i]) == strtolower($_ENV["application.cw"]["appImageDefault"])) ) ) {
?>
						<!-- row has class matching lower-case value of image name -->
						<tr class="<?php echo strtolower($selectImageList[$i]); ?>">
                           <td style="width:40px;">
                               <a class="imgSelect" href="<?php echo $selectImageList[$i]; ?>">
                               <img src="<?php echo $imgDir.$selectImageList[$i]; ?>" alt="<?php echo $selectImageList[$i];?>"></a>
                           </td>
                           <td style="padding:3px 10px;">
                               <a class="imgSelect" href="<?php echo $selectImageList[$i];?>"><?php echo $selectImageList[$i];?></a>
                           </td>
						</tr>
<?php
	}
}
?>          
			</table>
		</div>
		<!-- hidden loading graphic -->
		<div id="loadingGraphic" style="display:none;padding:20px;">
			<img src="img/cw-loading-graphic.gif">
		</div>
	</body>
</html>
