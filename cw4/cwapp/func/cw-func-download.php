<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, all Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-downloads.php
File Date: 2012-05-12
Description: Handles download-related functions for the Cartweaver download add-on
Dependencies: Requires cw-func-admin in calling page for string manipulation functions
==========================================================
*/

// // ---------- // check file download permission for user // ---------- // 
function CWcheckCustomerDownload($sku_id, $customer_id, $order_id=null, $status_codes=null) {
	if ($order_id === null) $order_id = 0;
	if ($status_codes === null) $status_codes = $_ENV["application.cw"]["appDownloadStatusCodes"];
	$returnStr = '';
	$orderQuery = '';
	$orderOk = false;
	$dlQuery = '';
	$skuQuery = '';
	// verify customer has purchased this item 
	$orderSQL = "SELECT oo.order_id, oo.order_status
	FROM cw_order_skus os, cw_orders oo
	WHERE os.ordersku_sku = ".CWqueryParam($sku_id)."
	AND os.ordersku_order_id = oo.order_id
	AND oo.order_customer_id = '".CWqueryParam($customer_id)."'";
	if ($order_id != 0) {
		$orderSQL .= "
		AND oo.order_id = '".CWqueryParam($order_id)."'";
	}
	$orderQuery = CWqueryGetRS($orderSQL);
	if ($orderQuery["totalRows"]) {
		// check order status (paid or shipped allows download) - use listFind in case multiple purchases are found 
		$scArr = $status_codes;
		if (!is_array($scArr) && strlen(trim($scArr))) $scArr = explode(",", $status_codes);
		else if (!is_array($scArr)) $scArr = array();
		$scArr2 = $orderQuery["order_status"];
		if (!is_array($scArr2) && strlen(trim($scArr2))) $scArr2 = explode(",", $status_codes);
		else if (!is_array($scArr2)) $scArr2 = array();
		if (sizeof($scArr) && $orderQuery["totalRows"]) {
			foreach ($scArr as $i) {
				if (in_array($i, $scArr2)) {
					$orderOk = true;
					break;
				}
			}
		}
		// if the order status is ok 
		if ($orderOk) {
			// check number of downloads for this customer 
			$dlQuery = CWqueryGetRS("SELECT dl_id
			FROM cw_downloads
			WHERE dl_sku_id = ".CWqueryParam($sku_id)."
			AND dl_customer_id = '".CWqueryParam($customer_id)."'");
			// check download total for this sku 
			$skuQuery = CWqueryGetRS("SELECT sku_download_limit
			FROM cw_skus
			WHERE sku_id = ".CWqueryParam($sku_id));
			// set value for maximum downloads allowed 
			if (!isset($skuQuery["sku_download_limit"][0]) || (isset($skuQuery["sku_download_limit"][0]) && !is_numeric($skuQuery["sku_download_limit"][0]))) {
				$maxDownloads = 0;
			} else {
				$maxDownloads = $skuQuery["sku_download_limit"][0];
			}
			// if 0, no limit is imposed, return simple '0' response 
			if ($maxDownloads == 0) {
				$returnStr = 0;
			// if limit has been reached, return message 
			} else if ($dlQuery["totalRows"] >= $maxDownloads) {
				$returnStr = '0-The maximum number of downloads for this file has been reached';
			// if tracking downloads, return number remaining 
			} else {
				$returnStr = $maxDownloads - $dlQuery["totalRows"];
			}
		// any other status returns a message 
		} else if ($orderQuery["totalRows"] == 1) {
			$returnStr = '0-Item not available';
		// if no order is found 
		} else if ($orderQuery["totalRows"] == 0) {
			$returnStr = '0-Unavailable - missing purchase confirmation';
		}
	} else if ($orderQuery["totalRows"] == 0) {
		$returnStr = '0-Unavailable - missing purchase confirmation';
	}
	return $returnStr;
}

// // ---------- // get customer downloadable items // ---------- // 
function CWselectCustomerDownloads($customer_id) {
	$dlQuery = CWqueryGetRS("SELECT DISTINCT p.product_id,
		p.product_name,
		p.product_preview_description,
		p.product_date_modified,
		p.product_on_web,
		p.product_archive,
		s.sku_on_web,
		s.sku_id,
		s.sku_download_id,
		s.sku_download_version,
		o.order_date,
		o.order_id,
		os.ordersku_unique_id,
		os.ordersku_unit_price,
		os.ordersku_quantity
		FROM cw_products p, 
		cw_order_skus os, 
		cw_skus s,
		cw_orders o
		WHERE os.ordersku_sku = s.sku_id
		AND s.sku_product_id = p.product_id
		AND NOT s.sku_download_id IS NULL
		AND NOT s.sku_download_id = ''
		AND NOT s.sku_download_file IS NULL
		AND NOT s.sku_download_file = ''
		AND NOT p.product_archive = 1
		AND NOT s.sku_on_web = 0
		AND o.order_customer_id = '".CWqueryParam($customer_id)."'
		AND o.order_id = os.ordersku_order_id
		ORDER BY p.product_name, p.product_id, o.order_date DESC");

	return $dlQuery;
}

// // ---------- // record customer download // ---------- // 
function CWrecordCustomerDownload($sku_id, $customer_id, $file_name=null, $file_version=null) {
	if ($file_name === null) $file_name = "";
	if ($file_version === null) $file_version = "";
	$skuQuery = CWqueryGetRS("SELECT sku_download_version, sku_download_file
		FROM cw_skus
		WHERE sku_id = ".CWqueryParam($sku_id)."
		AND NOT sku_download_id = ''
		AND NOT sku_download_id IS NULL");
	if (isset($skuQuery["sku_download_file"][0]) && strlen(trim($skuQuery["sku_download_file"][0]))) {
		$file_name = trim($skuQuery["sku_download_file"][0]);
	}
	if (isset($skuQuery["sku_download_version"][0]) && strlen(trim($skuQuery["sku_download_version"][0]))) {
		$file_version = trim($skuQuery["sku_download_version"][0]);
	}
	// insert customer download record 
	$query = "INSERT INTO cw_downloads (
		dl_sku_id,
		dl_customer_id,
		dl_timestamp,
		dl_file,
		dl_version,
		dl_remote_addr
	) VALUES (
		".CWqueryParam($sku_id).",
		'".CWqueryParam($customer_id)."',
		'".CWqueryParam(date("Y-m-d H:i:s"))."',
		'".CWqueryParam($file_name)."',
		'".CWqueryParam($file_version)."',
		'".CWqueryParam($_SERVER['REMOTE_ADDR'])."'		
	)";
	if (!function_exists("CWpageMessage")) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		// global functions 
		require_once("cw-func-global.php");
		chdir($myDir);
	}
	mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

// // ---------- // get current file download version // ---------- // 
function CWgetDownloadVersion($sku_id) {
	$returnStr = '';
	$skuVersQuery = CWqueryGetRS("SELECT sku_download_version as vers
		FROM cw_skus
		WHERE sku_id = ".CWqueryParam($sku_id)."
		AND NOT sku_download_id = ''
		AND NOT sku_download_id IS NULL");
	if (isset($skuVersQuery["vers"][0]) && strlen(trim($skuVersQuery["vers"][0]))) {
		$returnStr = trim($skuVersQuery["vers"][0]);
	}
	return $returnStr;
}

// // ---------- // get customer previous download version date // ---------- // 
function CWgetCustomerDownloadData($sku_id, $customer_id) {
	$skuData = array();
	$returnStruct = array();
	$skuData["date"] = "";
	$skuData["version"] = "";
	$skuDataQuery = CWqueryGetRS("SELECT dl_timestamp, dl_version
		FROM cw_downloads
		WHERE dl_sku_id = ".$sku_id."
		AND dl_customer_id = '".$customer_id."'");
	if (isset($skuDataQuery["dl_version"][0]) && strlen(trim($skuDataQuery["dl_version"][0]))) {
		$skuData["version"] = trim($skuDataQuery["dl_version"][0]);
	}
	if (isset($skuDataQuery["dl_timestamp"][0]) && strlen(trim($skuDataQuery["dl_timestamp"][0]))) {
		$skuData["date"] = trim($skuDataQuery["dl_timestamp"][0]);
	}
	$returnStruct = $skuData;
	return $returnStruct;
}

// // ---------- // get file download path // ---------- // 
function CWgetDownloadPath($downloads_dir = null, $file_root = null, $sku_id = null, $ext_dirs = null) {
    if ($downloads_dir === null) $downloads_dir = $_ENV["application.cw"]["appDownloadsDir"];
    if ($file_root === null) $file_root = CWtrailingChar(expandPath(""), 'remove'); //realpath(CWtrailingChar($_ENV["application.cw"]["siteRoot"]));
    if ($sku_id === null) $sku_id = 0;
    if ($ext_dirs === null) $ext_dirs = $_ENV["application.cw"]["appDownloadsFileExtDirs"];
    $returnPath = '';
    $downloads_dir = CWleadingChar(CWtrailingChar(str_replace('\\','/',$downloads_dir)),'remove');
    $pathChar = ((strpos($file_root, "/") !== false) ? "/" : "\\");
    // Download directory: if outside of site root (path starting with ../) 
    if (substr($downloads_dir,0,3) == "../") {
        // recurse up one level from site root for each "../" in the path - limit 2 for security purposes 
        $loopCt = 0;
        $dirArr = explode("/", $downloads_dir);
        foreach ($dirArr as $i) {
            if ($i == '..') {
                $loopCt++;
                $frArr = explode($pathChar, $file_root);
                $file_root = substr($file_root, 0, strlen($file_root) - strlen($frArr[sizeof($frArr)-1]) - 1);
            }
            // can only go up two levels to avoid putting stuff in other people's domains 
            if ($loopCt >= 2) break;
        }
        $returnPath = $file_root . $pathChar . CWleadingChar(CWtrailingChar(str_replace('../','',$downloads_dir),'remove'), 'remove', '/');
        // if using the recursive path, directory must already exist 
        if (!file_exists($returnPath) || is_file($returnPath)) {
            // set to site root w/ last folder name given in path 
            $dirArr = explode("/", CWtrailingChar($downloads_dir, 'remove'));
            $returnPath = expandPath(CWleadingChar(CWtrailingChar($_ENV["application.cw"]["siteRoot"]) . $dirArr[sizeof($dirArr)-1], 'remove', '/'));
        }
    // if a standard directory name, append to file root 
    } else {
        $returnPath = expandPath(CWtrailingChar($_ENV["application.cw"]["siteRoot"]) . $downloads_dir);
    }
    // clean up parent path, remove any funky path traversing stuff 
    $returnPath = str_replace('../','',$returnPath);
    $returnPath = str_replace('./','',$returnPath);
    // upload path can be manually set here, must be a full path, and existing directory 
    if (isset($_ENV["application.cw"]["appDownloadsPath"]) && file_exists($_ENV["application.cw"]["appDownloadsPath"]) && !is_file($_ENV["application.cw"]["appDownloadsPath"])) {
        $returnPath = $_ENV["application.cw"]["appDownloadsPath"];
    }
    // make sure path has trailing slash 
    $returnPath = CWtrailingChar($returnPath,'add','/');
    // if a sku id is provided 
    if ($sku_id > 0) {
        // get the sku file info 
        $skuFileQuery = CWqueryGetRS("SELECT sku_download_file, sku_download_id
		FROM cw_skus
		WHERE sku_id = ".CWqueryParam($sku_id)."
		AND NOT sku_download_id = ''
		AND NOT sku_download_id IS NULL");
        // if a sku file is found 
        if ($skuFileQuery["totalRows"] == 1) {
            // add file directory if using extension dirs 
            if ($ext_dirs) {
                $dirArr = explode(".", $skuFileQuery["sku_download_id"][0]);
                $fileExtDir = trim($dirArr[sizeof($dirArr)-1]) . $pathChar;
            } else {
                $fileExtDir = '';
            }
            // add file id to path 
            $returnPath .= $fileExtDir . trim($skuFileQuery["sku_download_id"][0]);
        }
    }
    return $returnPath;
}

// // ---------- // create url for download // ---------- // 
function CWcreateDownloadURL($sku_id, $downloads_page=null, $downloads_dir=null, $ext_dirs=null) {
	if ($downloads_page === null) $downloads_page = $_ENV["application.cw"]["appPageDownload"];
	if ($downloads_dir === null) $downloads_dir = $_ENV["application.cw"]["appDownloadsDir"];
	if ($ext_dirs === null) $ext_dirs = $_ENV["application.cw"]["appDownloadsFileExtDirs"];
	$returnStr = '';
	$parentPath = '';
	$fileExtDir = '';
	$filePath = '';
	$skuFileQuery = '';
	$filePath = CWgetDownloadPath($downloads_dir, null, $sku_id, $ext_dirs);
	// verify file exists 
	if (file_exists($filePath)) {
		// if file exists, return link 
		$returnStr = $downloads_page."?sku=".$sku_id;
	}
	return $returnStr;
}

// // ---------- // get SKU filename by sku id // ---------- // 
function CWqueryGetSkuFile($sku_id) {
	$returnStr = '';
	$skuFileQuery = '';
	if ($sku_id > 0) {
		// get the sku file info 
		$skuFileQuery = CWqueryGetRS("SELECT sku_download_file
		FROM cw_skus
		WHERE sku_id = ".CWqueryParam($sku_id)."
		AND NOT sku_download_file = ''
		AND NOT sku_download_file IS NULL");
		if ($skuFileQuery["totalRows"]) {
			$returnStr = trim($skuFileQuery["sku_download_file"][0]);
		}
	}
	return $returnStr;
}
?>