<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-sanitize.php
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
		require_once("../func/cw-func-global.php");
		chdir($myDir);
	}
	// list of url variables that must be numeric 
	$_ENV["request.cwpage"]['numericQS'] = 'product,category,secondary,page,showall,cartconfirm,custreset,shipreset,authreset,logout';
	// remove unwanted content from form and url variables 
	if(isset($_POST)) {
	// clean up form submissions  skipping htmleditors
	foreach($_POST as $key => $data) {
		  $_POST[$key] = CWsafeHTML($data);		
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
	// verify numeric values for url ids 
	if (!(is_array($_ENV["request.cwpage"]['numericQS']))) $_ENV["request.cwpage"]['numericQS'] = explode(',',$_ENV["request.cwpage"]['numericQS']);
	for($i = 0; $i < count($_ENV["request.cwpage"]['numericQS']); $i++) {
		if(isset($_POST[$_ENV["request.cwpage"]['numericQS'][$i]]) && !is_numeric($_POST[$_ENV["request.cwpage"]['numericQS'][$i]])) {
			$_POST[$_ENV["request.cwpage"]['numericQS'][$i]] = intval($_POST[$_ENV["request.cwpage"]['numericQS'][$i]]);
		}
		if(isset($_GET[$_ENV["request.cwpage"]['numericQS'][$i]]) && !is_numeric($_GET[$_ENV["request.cwpage"]['numericQS'][$i]])) {
			$_GET[$_ENV["request.cwpage"]['numericQS'][$i]] = intval($_GET[$_ENV["request.cwpage"]['numericQS'][$i]]);
		}
	}
	$_ENV["request.cwpage"]['sanitized'] = true;
}
?>    

