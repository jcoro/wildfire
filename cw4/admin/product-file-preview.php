<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: product-file-preview.php
File Date: 2012-05-12
Description: delivers file as browser download based on sku id in url
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
$_ENV["request.cwpage"]["accesslevel"] = CWauth("any");
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "File Download";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "File Download Preview";
// sku id must be provided to get file 
if (!isset($_GET["sku"])) $_GET["sku"] = 0;
$downloadOK = false;
// GET FILE 
if (is_numeric($_GET["sku"]) && $_GET["sku"] > 0) {
    $filePath = CWcreateDownloadPath(null, null, $_GET["sku"], null);
    // if file exists 
    if (strlen(trim($filePath))) {
        $downloadOK = true;
    }
}
// START OUTPUT 
// if file is available, deliver as downloadable 
if ($downloadOK) {
    try {
        // download details - server path and saved filename 
        $pathVar = ((strpos($filePath, "\\") !== false) ? "\\" : "/");
        $fpArr = explode($pathVar, $filePath);
        $fileName = $fpArr[sizeof($fpArr)-1];
        $fileDir = substr($filePath, strlen($filePath)-strlen($fileName));
        // get friendly filename 
        $downloadName = CWqueryGetSkuFile($_GET["sku"]);
        if (!strlen(trim($downloadName))) {
            $downloadName = $fileName;
        }
        // get file size 
        $fileSize = 0;
        $dlFile = "";
        if (file_exists($filePath)) {
            $fileSize = filesize($filePath);
            $dlFileObj = fopen($filePath, "rb");
            $dlFile = fread($dlFileObj, $fileSize);
            // set headers 
            header("Content-Length: ".$fileSize);
            header("Content-Disposition: attachment; filename=".$downloadName);
            // serve up file content 
            echo $dlFile;
            die;
        }
    // on error, reload, show unavailable message 
    } catch (Exception $e) {
        header("Location: ".$_SERVER["SCRIPT_NAME"]);
    }
    // if file is not available, show message 
} else {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Product File Preview</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
                <link href="theme/<?php $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php
    include('cwadminapp/inc/cw-inc-admin-scripts.php');
?>
	</head>
 <?php
    // body gets a class to match the filename 
    $page = explode('.',$_ENV["request.cw"]["thisPage"]);
    $page_First = $page[0];
?>
	<body <?php echo 'class="'.$page_First.'"'; ?>>
		<div id="CWadminWrapper">
			<!-- Main Content Area -->
			<div id="CWadminPage">
				<!-- inside div to provide padding -->
				<div class="CWinner">
                                    <?php if (strlen(trim($_ENV["request.cwpage"]["heading1"]))) { ?><h1><?php echo trim($_ENV["request.cwpage"]["heading1"]); ?></h1><?php } ?>
				</div>
				<!-- Page Content Area -->
				<div id="CWadminContent">
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p><strong>Download unavailable.</strong> <br><br>Verify the file has been uploaded and saved correctly.</p>
				</div>
			</div>
		</div>

	</body>
</html>
<?php
}
// /end if downloadok 
?>