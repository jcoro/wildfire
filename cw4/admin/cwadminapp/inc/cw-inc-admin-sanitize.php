<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-sanitize.php
File Date: 2012-05-14
Description:
Cleans up all form and url variables for processing
==========================================================
*/
// verify only included once in any page 
if(!(isset($_ENV["request.cwpage"]['sanitized']) && $_ENV["request.cwpage"]['sanitized'])) {
	// global functions
	if(!(function_exists('CWtime'))) {
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		require_once("../func/cw-func-admin.php");
		chdir($myDir);
	}


// remove unwanted content from form and url variables 
if(isset($_POST)) {
	// clean up form submissions  skipping htmleditors
	foreach($_POST as $key => $data) {
		if ( (strpos($key,'category_description')!==false)
				   ||strpos($key,'secondary_description')!==false
				   ||strpos($key,'product_description')!==false
				   ||strpos($key,'product_preview_description')!==false
				   ||strpos($key,'product_special_description')!==false
				   ||preg_match("#(.*?)(config_value)(.*?)#", $key)
				   ){
		 //do nothing and skip
		} else {
		  $_POST[$key] = CWsafeHTMLAdmin($data);	
		}	
		
	}
}
	
	$urlVars = CWremoveUrlVars();
	if(!is_array($urlVars) && sizeof($urlVars)) $urlVars = explode(',',$urlVars);
	elseif (!is_array($urlVars)) $urlVars = array();
	if(count($urlVars)) {
		// clean up form submissions 
		for($v = 0; $v < count($urlVars); $v++) {
			// skip errors for variables with unformed values 
			try {
				if ($urlVars[$v] && isset($_GET[$urlVars[$v]])) {
					// clean up each value 
					$_GET[$urlVars[$v]] = CWsafeHTML($_GET[$urlVars[$v]]);
				}
			}
			catch(Exception $e) {
				echo $e->getMessage();
			}
		}
	}

	$_ENV["request.cwpage"]['sanitized'] = true;
}
?>    
