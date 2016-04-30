<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: product-file-upload.php
File Date: 2012-05-18
Description: Uploads files to the appropriate server location and sets
the name of the file in the input of the sku form.
Included via iFrame into product-details.php sku file upload areas
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
// max file size 
$maxSizeKB = $_ENV["application.cw"]["appDownloadsMaxKb"];
$maxSize = $maxSizeKB * 1024;
// default upload 
if (!isset($_GET["uploadSku"])) $_GET["uploadSku"] = 0;
if (!isset($_ENV["request.cwpage"]["skuID"])) $_ENV["request.cwpage"]["skuID"] = $_GET["uploadSku"];
if (!isset($_ENV["request.cwpage"]["fileExtDirs"])) $_ENV["request.cwpage"]["fileExtDirs"] = $_ENV["application.cw"]["appDownloadsFileExtDirs"];
if (!isset($_ENV["request.cwpage"]["maskFilenames"])) $_ENV["request.cwpage"]["maskFilenames"] = $_ENV["application.cw"]["appDownloadsMaskFilenames"];
// file types to allow 
if (!isset($_ENV["application.cw"]["appDownloadsFileTypes"])) $_ENV["application.cw"]["appDownloadsFileTypes"] = "";
$_ENV["request.cwpage"]["acceptedMimeList"] = 'application/octet-stream,' . trim($_ENV["application.cw"]["appDownloadsFileTypes"]);

// handle errors and confirmations 
$_REQUEST["uploadError"] = "";
$_REQUEST["uploadSuccess"] = "";
// BASE URL 
// for this page the base url is the standard current page variable 
$_ENV["request.cwpage"]["baseUrl"] = $_ENV["request.cw"]["thisPage"] . '?uploadSku=' . $_ENV["request.cwpage"]["skuID"];
// function: create parent path based on download settings 
$_REQUEST["fileParentPath"] = CWcreateDownloadPath();
// temp path 
$_REQUEST["fileFolderChar"] = ((strpos($_REQUEST["fileParentPath"], "\\") !== false) ? "\\" : "/");
$_REQUEST["fileTempPath"] = $_REQUEST["fileParentPath"] . 'temp' . $_REQUEST["fileFolderChar"];
// UPLOAD FILE 
if (isset($_POST['frmAction']) && $_POST['frmAction'] == 'upload') {
    // Check for file size as reported by the HTTP header 
    if ($_FILES['skuFileName']['size'] == "") {
        $_REQUEST["uploadError"] = "Your browser reported a badly-formed HTTP header. This could be caused by an error, a bug in your browser or the settings on your proxy/firewall";
    } else if ($_FILES['skuFileName']['size'] > $maxSize) {
        $_REQUEST["uploadError"] = "The selected file's size is greater than " . $maxSizeKB . " kilobytes which is the maximum size allowed, please select another file or adjust your settings";
    }
	//check for allowed file type
	$ftArr = $_ENV["application.cw"]["appDownloadsFileTypes"];
	if (!is_array($ftArr) && strlen(trim($ftArr))) $ftArr = explode(",", $ftArr);
	else if (!is_array($ftArr)) $ftArr = array();
	if (!in_array($_FILES['skuFileName']['type'], $ftArr)) {
		if (!isset($_REQUEST["uploadError"])) $_REQUEST["uploadError"] = "";
		if ($_REQUEST["uploadError"]) $_REQUEST["uploadError"] .= ", ";
	   $_REQUEST["uploadError"] .= "The selected file type &quot;" . $_FILES['skuFileName']['type'] . "&quot; is not allowed.";
	}
    // if no errors 
    if (!strlen(trim($_REQUEST["uploadError"]))) {
        try {
			// make the parent folder if not exists 
            if (!file_exists($_REQUEST["fileParentPath"]) || is_file($_REQUEST["fileParentPath"])) {
                mkdir($_REQUEST["fileParentPath"], 0777);
            }
            // if renamed at upload, renaming is automatic, show warning 
            if ($wasRenamed) {
                $_REQUEST["uploadRenamed"] = 'Duplicate filename exists, file was renamed';
            }
            // clean up the filename 
            // replace whitespace characters with "-" 
            $newFileName = preg_replace("/[\s]/","-",$_FILES['skuFileName']['name']);
            // remove apostrophes and other unwanteds, leave space , hyphen and dot 
            $newFileName = preg_replace("/[^a-zA-Z0-9-..]/","",$newFileName);
            // the file extension 
            $fileExt = trim(substr($newFileName,strrpos($newFileName, '.')+1));
            // set up file location 
            if ($_ENV["request.cwpage"]["fileExtDirs"] && strlen($fileExt)) {
                $_REQUEST["fileUploadPath"] = $_REQUEST["fileParentPath"] . $fileExt;
                // create needed directory if it doesn't already exist 
                if (!file_exists($_REQUEST["fileUploadPath"]) || is_file($_REQUEST["fileUploadPath"])) {
                    mkdir($_REQUEST["fileUploadPath"]);
                }
            } else {
                $_REQUEST["fileUploadPath"] = $_REQUEST["fileParentPath"];
            }
			$pathChar = ((strpos($_REQUEST["fileUploadPath"], "/") !== false) ? "/" : "\\");
            $_REQUEST["fileUploadPath"] = CWtrailingChar($_REQUEST["fileUploadPath"],'add',$pathChar);
            // if file with new name already exists, add datetimestamp to filename, show warning 
            $wasRenamed = false;
            if (file_exists($_REQUEST["fileUploadPath"] . $newFileName) && $newFileName != $orig_file_name) {
                $_REQUEST["uploadRenamed"] = 'Duplicate filename exists, file was renamed';
                $newExt = substr($orig_file_name, strrpos($orig_file_name, "."));
                $newFileName = substr($newFileName, 0, strrpos($newFileName, "."))."-".date("YmdHis").$newExt;
                $wasRenamed = true;
            }
            // file id 
            if ($_ENV["request.cwpage"]["maskFilenames"]) {
                $newFileID = session_id() . "." . $fileExt;
            } else {
                $newFileID = $newFileName;
            }
			if (!file_exists($_REQUEST["fileUploadPath"])) {
				mkdir($_REQUEST["fileUploadPath"]);
			}			
            // rename original if needed 
            $moved = move_uploaded_file($_FILES["skuFileName"]["tmp_name"], $_REQUEST["fileUploadPath"] . $newFileID);
            // if we still have no errors here, set up the confirmation 
            // if filename was changed, append show message 
            if (!empty($_REQUEST["uploadRenamed"])) {
                $_REQUEST["uploadSuccess"] = $_REQUEST["uploadRenamed"];
            } else {
                // if the file was uploaded without being changed 
                $_REQUEST["uploadSuccess"] = "The file ".$newFileName." was uploaded";
            }
            // add message to save changes 
            $_REQUEST["uploadSuccess"] = $_REQUEST["uploadSuccess"] . '<br><em>Save SKU to apply file change</em>';
            $_REQUEST["savedFileName"] = $newFileName;
            $_REQUEST["uploadedFileName"] = $newFileID;
            // if errors, parse out the message 
        } catch (Exception $e) {
            $_REQUEST["uploadError"] = "Error : ".$e->getMessage()."
                    ";
            if (strlen(trim($e->getTraceAsString()))) {
                $_REQUEST["uploadError"] .= "<div class=\"smallPrint\">
                    ".$e->getTraceAsString()."<br><br>
                    ".$e->getFile().", line ".$e->getLine()."
                </div>";
            }
        }
    }
    // end if no errors 
}
// end upload action 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Product File Upload</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php
include('cwadminapp/inc/cw-inc-admin-scripts.php');
// javascript
?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				// show loading graphic when submit button clicked
				jQuery('#CWfileSubmit').click(function(){
				jQuery(this).parents('form').hide().siblings('#loadingGraphic').show();
				});
				// end show loading graphic
				// put the value in the related input on the calling page
<?php
if (isset($_REQUEST["savedFileName"]) && strlen(trim($_REQUEST["savedFileName"]))) {
?>
				// set up variables for the input on the calling page, and filename value to insert
				var callingInputID = '#sku_download_file-<?php echo $_ENV["request.cwpage"]["skuID"]; ?>';
				var filenameString = '<?php echo $_REQUEST["savedFileName"]; ?>';
				var callingDownloadInputID = '#sku_download_id-<?php echo $_ENV["request.cwpage"]["skuID"]; ?>';
				var fileIDString = '<?php echo $_REQUEST["uploadedFileName"]; ?>';
				// this function puts a value in a form element on the parent page
				var $insertFunction= function(elementVar,textVar){
				jQuery(elementVar,parent.document.body).val(textVar);
				};
				// call the function, insert the filename to the calling input
				$insertFunction(callingInputID,filenameString);
				$insertFunction(callingDownloadInputID,fileIDString);
<?php
}
?>
			// end parent input value
			});
		</script>
	</head>
	<body id="CWfileUploadWrap">
<?php
// if not submitting a form 
if (!isset($_POST["frmAction"])) {
    // UPLOAD FORM 
?>
			<form name="CWfileUploadForm" id="CWfileUploadForm" action="<?php echo $_ENV["request.cwpage"]["baseUrl"]; ?>" enctype="multipart/form-data" method="post">
				<table class="CWformTable narrow">
					<tr>
						<th class="label">Upload a File</th>
						<td>
							<input name="skuFileName" class="CWfileInput" id="newFileName" type="file">
							<?php // hidden field for processing ?>
							<input type="hidden" name="frmAction" value="upload">
						</td>
					</tr>
				</table>
				<span>
					<input type="submit" name="CWfileSubmit" class="CWformButton" id="CWfileSubmit" value="Start Upload">
				</span>
			</form>
			<?php // hidden loading graphic ?>
			<div id="loadingGraphic" style="display:none;padding:20px;">
				<img src="img/cw-loading-graphic.gif">
			</div>
<?php
    // if the form was submitted 
} else {
    // if we have an error 
    if (strlen(trim($_REQUEST["uploadError"]))) {
?>
                        <div class="alert"><?php echo $_REQUEST["uploadError"]; ?><br><a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>" class="resetUpload">Try Again</a></div>
<?php
        // if no error 
    } else if (strlen(trim($_REQUEST["uploadSuccess"]))) {
?>
                        <div class="alert confirm"><?php echo $_REQUEST["uploadSuccess"]; ?></div>
<?php
    }
    // /END error or success 
}
// /END if image was uploaded 
?>
	</body>
</html>