<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, all Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-download.php
File Date: 2012-06-27
Description: Handles download-related functions for the Cartweaver download add-on
Dependencies: Requires cw-func-admin in calling page for string manipulation functions
==========================================================
*/

// // ---------- // create file download path // ---------- // 
function CWcreateDownloadPath($downloads_dir=null, $file_root=null, $sku_id=null, $ext_dirs=null) {
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
    if ($sku_id === null) $sku_id = 0;
    if ($downloads_page === null) $downloads_page = $_ENV["application.cw"]["appPageDownload"];
    if ($downloads_dir === null) $downloads_dir = $_ENV["application.cw"]["appDownloadsDir"];
    if ($ext_dirs === null) $ext_dirs = $_ENV["application.cw"]["appDownloadsFileExtDirs"];
    $returnStr = '';
    $filePath = CWcreateDownloadPath($downloads_dir, null, $sku_id, $ext_dirs);
    // verify file exists 
    if (file_exists($filePath) && is_file($filePath)) {
        // if file exists, return link --->
        $returnStr = $downloads_page."?sku=".$sku_id;
    }
    return $returnStr;
}

// // ---------- // attach file values to SKU record // ---------- // 
function CWqueryUpdateSkuFile($sku_id, $file_name=null, $file_version=null, $download_id=null, $download_limit=null) {
    if ($file_name === null) $file_name = "";
    if ($file_version === null) $file_version = "0.00";
    if ($download_id === null) $download_id = $file_name;
    if ($download_limit === null) $download_limit = 0;
    $updatedID = '0';
    // id cannot be blank 
    if (!strlen(trim($download_id))) {
        $download_id = trim($file_name);
    }
    $query = "UPDATE cw_skus
                SET
                sku_download_file = '".CWqueryParam($file_name)."'
                ";
    if ($file_version != '0.00') {
        $query .= ",sku_download_version = '".CWqueryParam($file_version)."'
                ";
    }
    $query .= ",sku_download_id = '".CWqueryParam($download_id)."'
		,sku_download_limit = '".$download_limit."'
                WHERE sku_id = '".CWqueryParam($sku_id)."'";
    if (!function_exists("CWpageMessage")) {
            $myDir = getcwd();
            chdir(dirname(__FILE__));
            // global functions 
            require_once("cw-func-global.php");
            chdir($myDir);
    }
    mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
    $updatedID = $sku_id;
    return $updatedID;
}

// // ---------- // get SKU filename by sku id // ---------- // 
function CWqueryGetSkuFile($sku_id) {
    $returnStr = '';
    $skuFileQuery = '';
    if ($sku_id > 0) {
	// get the sku file info 
        $skuFileQuery = CWqueryGetRS("SELECT sku_download_file
		FROM cw_skus
		WHERE sku_id = '".CWqueryParam($sku_id)."'
		AND NOT sku_download_file = ''
		AND NOT sku_download_file IS NULL");
        if ($skuFileQuery["totalRows"]) {
            $returnStr = trim($skuFileQuery["sku_download_file"][0]);
        }
    }
    return $returnStr;
}

// // ---------- // get all sku filenames // ---------- // 
function CWquerySelectSkuFiles() {
    $rsProductFiles = CWqueryGetRS("SELECT ss.sku_download_file, ss.sku_download_id,
		ss.sku_id, ss.sku_merchant_sku_id,
		pp.product_name,pp.product_id
		FROM cw_skus ss, cw_products pp
		WHERE ss.sku_product_id = pp.product_id
		AND NOT ss.sku_download_file = ''
		AND NOT ss.sku_download_file IS NULL");
    return $rsProductFiles;
}

// // ---------- // remove filename from skus // ---------- // 
function CWqueryDeleteSkuFile($download_key) {
    $query = "UPDATE cw_skus
			SET sku_download_file = '',
			sku_download_id = '',
			sku_download_version = ''
			WHERE sku_download_id = '".trim(CWqueryParam($download_key))."'";
    if (!function_exists("CWpageMessage")) {
            $myDir = getcwd();
            chdir(dirname(__FILE__));
            // global functions 
            require_once("cw-func-global.php");
            chdir($myDir);
    }
    mysql_query($query,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$query);
}

function CWgetFolderContents($folder, $files=true, $recurse=false, $filesObj=null) {
    if ($filesObj === null) $filesObj = array("totalRows" => 0);
	if (file_exists($folder)) {
		$fold = opendir($folder);
		if ($fold) {
			while (($file = readdir($fold)) !== false) {
				if ($file == "." || $file == "..") continue;
				if (is_dir(CWtrailingChar($folder).$file) && $recurse) {
					$filesObj = CWgetFolderContents(CWtrailingChar($folder).$file, $files, $recurse, $filesObj);
					if (!$files) {
						if (!isset($filesObj["directories"])) $filesObj["directories"] = array();
						if (!isset($filesObj["paths"])) $filesObj["paths"] = array();
						if (!isset($filesObj["folders"])) $filesObj["folders"] = array();
						$filesObj["directories"][] = $file;
						$filesObj["paths"][] = CWtrailingChar($folder).$file;
						$filesObj["folders"][] = $folder;
						$filesObj["totalRows"]++;
					}
				} else if ($files) {
					if (!isset($filesObj["files"])) $filesObj["files"] = array();
					if (!isset($filesObj["paths"])) $filesObj["paths"] = array();
					if (!isset($filesObj["folders"])) $filesObj["folders"] = array();
					$filesObj["files"][] = $file;
					$filesObj["paths"][] = CWtrailingChar($folder).$file;
					$filesObj["folders"][] = $folder;
					$filesObj["totalRows"]++;
				}
			}
		}
	}
	return $filesObj;
}
?>