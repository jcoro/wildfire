<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: product-image-upload.php
File Date: 2012-02-01
Description: Uploads images to the appropriate folders and sets
the name of the image in the input of the product form.
Included via iFrame into product-details.php image upload areas
Important Notes:
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
// max image size 
$MaxSizeKB = $_ENV["application.cw"]["adminProductImageMaxKB"];
$MaxSize = $MaxSizeKB * 1024;
// image handling
require_once("cwadminapp/inc/class.cropcanvas.php");
// set up the upload group to upload to 
if(!isset($_GET['uploadGroup']) || ($_GET['uploadGroup'] == '') ) { $_GET['uploadGroup'] = 1; }
$_ENV["request.cwpage"]["inputGroupNo"] = $_GET['uploadGroup'];
// file types to allow 
$_ENV["request.cwpage"]["acceptedMimeList"] = "image/jpeg, image/pjpeg, image/jpg, image/png, image/gif";
// handle errors and confirmations 
$_REQUEST["uploadError"] = "";
$_REQUEST["uploadSuccess"]= "";
// BASE URL 
// for this page the base url is the standard current page variable 
$_ENV["request.cwpage"]["baseURL"] = $_ENV["request.cw"]["thisPage"] .'?uploadGroup='.$_ENV["request.cwpage"]["inputGroupNo"];
// QUERY: get the list of image types for this group 
$getGroupTypes = CWquerySelectImageTypes($_ENV["request.cwpage"]["inputGroupNo"]);
// set up base folder locations 
// parent URL must end with a trailing slash, i.e. "../Assets/" 
if(!isset($_GET['previewFolder'])) { $_GET['previewFolder'] = "admin_preview"; }
$_REQUEST['imgParentUrl'] = $_ENV["request.cwpage"]["adminImgPrefix"].$_ENV["application.cw"]["appImagesDir"]; 
$_REQUEST['imgParentPath'] = realpath($_REQUEST['imgParentUrl']);
$systemSlash = "/";
if (strpos($_REQUEST['imgParentPath'], "\\") !== false) $systemSlash = "\\";
$_REQUEST['imgOrigPath'] = $_REQUEST['imgParentPath'].$systemSlash."orig".$systemSlash;
$_REQUEST['imgPreviewPath'] = $_REQUEST['imgParentPath'].$systemSlash."admin_preview".$systemSlash;
$imgDir = $_REQUEST['imgParentUrl'].'/'.$_GET['previewFolder']."/"; //$imgParentUrl.$_GET['previewFolder']."/";
// UPLOAD IMAGE 
if((isset($_POST['frmAction'])) && strtolower($_POST['frmAction']) == 'upload') {
	// Check for file size as reported by the HTTP header
	if($_FILES['imagefileName']['size']=='') {
		$_REQUEST["uploadError"] = "Your browser reported a badly-formed HTTP header. This could be caused by an error, a bug in your browser or the settings on your proxy/firewall";
	}
	else if($_FILES['imagefileName']['size'] > $MaxSize) {
		$_REQUEST["uploadError"] = "The selected file's size is greater than " .$MaxSizeKB. " kilobytes which is the maximum size allowed, please select another image and try again";
	} else if ( $_FILES['imagefileName']['error'] !== UPLOAD_ERR_OK ) {
		$_REQUEST["uploadError"] = "The selected file had issues. It was not successfully uploaded.";	    
	}
	// if no errors 
	if(!strlen(trim($_REQUEST["uploadError"]))) {
	    try {
			//ini_set("memory_limit", "1000M");
			// make the orig folder if not exists 
			if(!is_dir($_REQUEST['imgOrigPath'])) {
				mkdir($_REQUEST['imgOrigPath']);
			}
			// upload the file 
			$img_filename = $_FILES['imagefileName']['name'];
			// clean up the filename 
			// replace whitespace characters with "-" 
			$img_filename = preg_replace("/\s+/", "", $img_filename);
			// remove apostrophes and other unwanteds, leave space , hyphen and dot 
			$img_filename = preg_replace("/[^a-zA-Z0-9-\.]/", "", $img_filename);
			// if file with new name already exists, add datetimestamp to filename, show warning 
			$img_filepathname = $_REQUEST['imgOrigPath'].$img_filename;
			$UploadRenamed = '';
			if (file_exists($img_filepathname) && (strcmp($img_filename, $_FILES['imagefileName']['tmp_name']) != 0 ) ) {
				$UploadRenamed = 'Duplicate filename exists, file was renamed';
				$imgTimeStamp = date('YmdHis');
				$extIdx = strrpos($img_filepathname,'.');
				$img_filepathname = substr($img_filepathname,0,$extIdx) . '-' . $imgTimeStamp . substr($img_filepathname,$extIdx);
				$extIdx = strrpos($img_filename,'.');
				$img_filename = substr($img_filename,0,$extIdx) . '-' . $imgTimeStamp . substr($img_filename,$extIdx);
			}
			$moved = move_uploaded_file($_FILES['imagefileName']['tmp_name'],$img_filepathname);
			if( $moved === FALSE ) { 
				$_REQUEST["uploadError"] = "Error Moving The Original File: ".$_FILES['imagefileName']['name']."<br>";
			} else {
				// Start image processing 
				// get image info 
				$cc = new canvasCrop();
				if ($cc->loadImage($img_filepathname) === FALSE) {
					//$_FILES['imagefileName']['name']);
					preg_match("/\.([^\.]+)$/", $img_filepathname, $m);
					$_REQUEST["uploadError"] = "Cannot upload files of type ".$m[1];
				}
				else {
					$getGroupTypes["imagetype_folder"][] = "admin_preview";
					$getGroupTypes["imagetype_max_width"][] = 160;
					$getGroupTypes["imagetype_max_height"][] = 240;
					$getGroupTypes["imagetype_crop_width"][] = "";
					$getGroupTypes["imagetype_crop_height"][] = "";
					$getGroupTypes["totalRows"]++;
					$rawW = $cc->getOrigWidth();
					$rawH = $cc->getOrigHeight();
					for( $ii = 0; $ii < $getGroupTypes["totalRows"]; $ii++) {
						// establish destination dimensions 
						$newW = $getGroupTypes['imagetype_max_width'][$ii];
						$newH = $getGroupTypes['imagetype_max_height'][$ii];
						// cropping dimensions 
						$cropW = $getGroupTypes['imagetype_crop_width'][$ii];
						$cropH = $getGroupTypes['imagetype_crop_height'][$ii];
						// allow for null crop values (0 = no cropping) 
						if( !is_numeric($cropW)) $cropW = 0;
						if( !is_numeric($cropH)) $cropH = 0;
						if ($newW > $rawW) $newW = $rawW;
						if ($newH > $rawH) $newH = $rawH;
						if ($cropW > $rawW) $cropW = $rawW;
						if ($cropH > $rawH) $cropH = $rawH;
						// set destination folder 
						$imgFolder = $_REQUEST['imgParentPath'].$systemSlash.$getGroupTypes['imagetype_folder'][$ii];
						// give the file object a full path 
						$newPhoto = $imgFolder.$systemSlash.$img_filename;
						// make the folder if not exists 
						if(!is_dir($imgFolder)) {
							mkdir($imgFolder);
						}
						// IMAGE MANIPULATION 
						// cropping 
//echo $imgFolder."<br>";
						if( ( ($cropW > 0) && ($cropW < $rawW) ) 
							|| ($cropH > 0) && ($cropH < $rawH) ) {
							// scale down to  smaller dimension
							if( $rawW < $rawH ) {
								$scale = $cropW / $rawW;
							} else {
								$scale = $cropH / $rawH;
							}
							$scale *= 100;
							// set to crop dimension by shorter side, to fill space 
							$cc->cropToPercent($scale, $scale);
							// get info (w/h) for resized image 
							$centerX = $cc->getFinalWidth() / 2;
							$centerY = $cc->getFinalHeight() / 2;
							// find x/y for top left of crop selection 
							$cropX = $centerX - $cropW/2;
							$cropY = $centerY - $cropH/2;
							// crop image 
							$cc->cropToDimensions($cropX, $cropY, $cropX + $cropW, $cropY + $cropH );
							// write image, set quality (higher than .95 makes a large file size)
							$saved = $cc->saveImage($newPhoto);
							$cc->resetFinalImage();
						// resize (scale to fit) - make images smaller only, no stretching/enlarging 
						} elseif ( ($newW < $rawW) || ($newH < $rawH) ) {
							// copy the image
							$saved = copy($img_filepathname, $newPhoto);
							// write image, set quality (higher than .95 makes a large file size)
							resizeimage($newPhoto, $newW, $newH, 95);
						} else {
							// just copy the image
							$saved = copy($img_filepathname, $newPhoto);
						}
					}
				}
				@ImageDestroy($cc->_imgOrig);
			}
			// if we still have no errors here, set up the confirmation 
			// if filename was changed, append show message 
			if( strlen($UploadRenamed) > 0 )
				$_REQUEST["uploadSuccess"] = $UploadRenamed;
			else // if the file was uploaded withou being changed 
				$_REQUEST["uploadSuccess"] = "The image, ".$img_filename.", was uploaded successfully";
			// add message to save changes 
			$_REQUEST["uploadSuccess"] .= '<br><em>Save Product to apply image change</em>';
			$_ENV["request.cwpage"]["uploadedImageURL"] = $imgDir.$img_filename; // show admin_preview image
			$_ENV["request.cwpage"]["uploadedFileName"] = $img_filename;
		} 
		catch (Exception $e ) { // if errors, parse out the message 
			$_REQUEST["uploadError"] = "An error (".$e->getCode().") occurred on line ".$e->getLine()." of file: ".$e->getFile().":<br>".$e->getMessage();
		}
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Product Image Upload</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV['application.cw']['adminThemeDirectory']; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php 
include("cwadminapp/inc/cw-inc-admin-scripts.php");
// javascript 
?>
		<script type="text/javascript">
			jQuery(document).ready( function() {
				// show loading graphic when submit button clicked
				jQuery('#CWfileSubmit').click(function() {
				jQuery(this).parents('form').hide().siblings('#loadingGraphic').show();
				});
				// end show loading graphic
				// put the value in the related input on the calling page
<?php
if(isset($_ENV["request.cwpage"]["uploadedFileName"]) && strlen(trim($_ENV["request.cwpage"]["uploadedFileName"]))) {
?>
				    // set up variables for the input on the calling page, and filename value to insert
				    var callingInputID = '#Image<?php echo $_ENV["request.cwpage"]["inputGroupNo"]; ?>';
				    var filenameString = '<?php echo $_ENV["request.cwpage"]["uploadedFileName"]; ?>';
				    // this function puts a value in a form element on the parent page
				    var $insertFunction= function(elementVar,textVar) {
				    jQuery(elementVar,parent.document.body).val(textVar);
				    };
				    // call the function, insert the filename to the calling input
				    $insertFunction(callingInputID,filenameString);
<?php 
}
?>
			// end parent input value
			} ) ;
		</script>
	</head>
	<body id="CWimageUploadWrap">
<?php
// if not submitting a form 
if(!(isset($_POST['frmAction']))) {
// UPLOAD FORM ?>	
			<form name="CWimageUploadForm" id="CWimageUploadForm" action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>" enctype="multipart/form-data" method="post">
				<table class="CWformTable narrow">
					<tr>
						<th class="label">Upload an Image</th>
						<td>
							<input name="imagefileName" class="CWfileInput" id="imagefileName" type="file">
							<?php // hidden field for processing ?>
							<input type="hidden" name="frmAction" value="upload">
						</td>
					</tr>
				</table>
				<span>
					<input type="submit" name="CWfileSubmit" class="CWformButton" id="CWfileSubmit" value="Start Upload">
				</span>
			</form>
			<!-- hidden loading graphic -->
			<div id="loadingGraphic" style="display:none;padding:20px;">
				<img src="img/cw-loading-graphic.gif">
			</div>
<?php
	// if the form was submitted 
} else {
	// if we have an error 
	if(strlen(trim($_REQUEST["uploadError"]))) {
?>	
				<div class="alert"><?php echo $_REQUEST["uploadError"];?><br><a href="<?php echo $_ENV["request.cw"]["thisPage"];?>" class="resetUpload">Try Again</a></div>
<?php
		// if no error 
	} else if (strlen(trim($_REQUEST["uploadSuccess"]))) {
?>	
				<div class="alert confirm"><?php echo $_REQUEST["uploadSuccess"];?></div>
				<img src="<?php echo $_ENV["request.cwpage"]["uploadedImageURL"];?>" class="productImagePreview">
<?php
	}
	// /END error or success 
}
// /END if image was uploaded 
?>
	</body>
</html>
