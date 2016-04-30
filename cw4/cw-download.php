<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-download.php
File Date: 2012-05-12
Description: delivers product as downloadable file, handles
authentication and access messages
==========================================================
*/
// default url variables 
if (!isset($_GET["sku"])) $_GET["sku"] = 0;
// default authentication 
if (!isset($_ENV["request.cwpage"]["dlok"])) $_ENV["request.cwpage"]["dlok"] = false;
if (!isset($_ENV["request.cwpage"]["dlerror"])) $_ENV["request.cwpage"]["dlerror"] = "Access Denied";
if (!isset($_SESSION["cwclient"]["cwCustomerID"])) $_SESSION["cwclient"]["cwCustomerID"] = 0;
if (!isset($_ENV["request.cwpage"]["loginurl"])) $_ENV["request.cwpage"]["loginurl"] = $_ENV["request.cwpage"]["urlAccount"];
$myDir = getcwd();
chdir(dirname(__FILE__));
// clean up form and url variables 
include("cwapp/inc/cw-inc-sanitize.php");
// CARTWEAVER REQUIRED FUNCTIONS 
include("cwapp/inc/cw-inc-functions.php");
chdir($myDir);
// check customer permission, downloads enabled 
if (!empty($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] != '0' && $_ENV["application.cw"]["appDownloadsEnabled"]) {
	// verify this customer can get this file 
	$downloadCheck = CWcheckCustomerDownload(
								$_GET["sku"],
								$_SESSION["cwclient"]["cwCustomerID"]
								);
	// if a "0-" error message is returned, no dl available, show text string 
	if (substr($downloadCheck,0,2) == '0-') {
		$_ENV["request.cwpage"]["dlerror"] = substr($downloadCheck,2);
	} else {
		$_ENV["request.cwpage"]["dlok"] = true;
	}
// if customer not logged in 
} else {
	// redirect to account main page if not logged in 
	header("Location: ".$_ENV["request.cwpage"]["loginurl"]);
	exit;
}
// if ok to this point, check file exists 
if ($_ENV["request.cwpage"]["dlok"]) {
	$dlPath = CWgetDownloadPath(null, null, $_GET["sku"], null);
	if (!strlen(trim($dlPath))) {
		$_ENV["request.cwpage"]["dlerror"] = 'File Unavailable';
	// if file exists 
	} else {
		// set this message, only seen by user if something goes wrong 
		$_ENV["request.cwpage"]["dlerror"] = 'Download Error: please contact customer service for assistance';
	}
}
// /////// START OUTPUT /////// 
// deliver file 
if ($_ENV["request.cwpage"]["dlok"]) {
	// handle errors w/ custom message 
	try {
		// store download data in customer record 
		CWrecordCustomerDownload(
				$_GET["sku"],
				$_SESSION["cwclient"]["cwCustomerID"]
				);
		// download details - server path and saved filename --->
		$pathChar = ((strpos($dlPath, "/") !== false) ? "/" : "\\");
		$dlpArr = explode($pathChar, $dlPath);
		$fileName = $dlpArr[sizeof($dlpArr)-1];
		$fileDir = substr($dlPath, 0, strlen($dlPath) - strlen($fileName));
		// get friendly filename 
		$downloadName = CWqueryGetSkuFile($_GET["sku"]);
		if (!strlen(trim($downloadName))) {
			$downloadName = $fileName;
		}
		// get file size 
		if (file_exists($dlPath)) {
			$fileSize = filesize($dlPath);
			// read the file as a binary download 
			$dlFile = fopen($dlPath, (($pathChar == "/") ? "rb" : "r"));
			// set headers 
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header("Content-Disposition: attachment; filename=".$downloadName);
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header("Content-Length: ".$fileSize);
			// serve up file content 
			ob_clean();
			flush();
			readfile($dlPath);
			exit;
		}
	// on error, reload, show unavailable message 
	} catch (Exception $e) {
		$_ENV["request.cwpage"]["dlerror"] = 'Download Error: please contact customer service for assistance';
		// add error details if available 
		if (strlen(trim($e->getMessage()))) {
			$_ENV["request.cwpage"]["dlerror"] = $_ENV["request.cwpage"]["dlerror"] . '<br>(Detail: '.$e->getMessage().')';
		}
	}
}
// /end if file ok 
// show message 
if (strlen(trim($_ENV["request.cwpage"]["dlerror"]))) {
?>
	<div class="CWcontent">
		<div class="CWalertBox<?php if (!$_ENV["request.cwpage"]["dlok"]) { ?> alertText<?php } ?>">
			<?php echo $_ENV["request.cwpage"]["dlerror"]; ?>
			<div class="confirmText">
				<br>Contact us for assistance&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="<?php echo $_ENV["request.cwpage"]["urlAccount"]; ?>">Return to Account</a>
			</div>
		</div>
	</div>
<?php
}
?>
	<div class="CWclear"></div>
</div>
<!-- / end #CWdetails -->
<?php
$myDir = getcwd();
chdir(dirname(__FILE__));
// recently viewed products 
include("cwapp/inc/cw-inc-recentview.php");
// page end / debug 
include("cwapp/inc/cw-inc-pageend.php");
chdir($myDir);
?>