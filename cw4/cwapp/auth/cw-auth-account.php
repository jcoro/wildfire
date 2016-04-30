<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-auth-account.php
File Date: 2012-07-09
Description: Creates functionality to process the order and show confirmation.
Bypasses any actual financial transaction, stores the order as complete / paid in full (or as pending, with modification below).
Use, updates and modifications of this file as a payment option are the sole responsibility of the user.

Custom validation, response messages and more can be injected in several locations - see processing code below.
( Tip: copy and rename this file in the cwapp/auth/ directory, then customize the new copy.
It will automatically appear in the Cartweaver Admin as a selectable payment option.)

NOTE: Setting up accounts and integrating with third party processors is not
a supported feature of Cartweaver. For information and support concerning
payment processors contact the appropriate processor tech support web site or
personnel. Cartweaver includes this integration code as a courtesy with no
guarantee or warranty expressed or implied. Payment processors may make changes
to their protocols or practices that may affect the code provided here.
If so, updates and modifications are the sole responsibility of the user.

================================================================
RETURN VARIABLES:
request.trans.paymentTransID,
request.trans.paymentTransResponse,
request.trans.paymentStatus

Values:
Transaction ID,
Transaction Response Message,
Payment Status (approved|denied|none(no response))

These are returned to the containing page or template
in the request scope, e.g. '#request.trans.paymentTransID#'
*/
// /// CONFIGURATION / SETUP /// 
// CWauth Payment Configuration 
// USER SETTINGS  [ START ] ==================================================== 
// ============================================================================= 
// OPTIONAL PAYMENT METHOD VARIABLES 
// method image: an optional logo url (relative or full url) associated with this payment option at checkout  
global $caller;
if (!isset($settings)) $settings = array();
$settings["methodImg"] = '';
// shippay message: optional message shown to customer on shipping selection 
$settings["methodSelectMessage"] = 'Charge to Account';
// submit message: optional message shown to customer on final order submission 
$settings["methodSubmitMessage"] = 'This order will be submitted without payment and held on file, pending direct payment or debit of your account.';
// confirm message: optioal message shown to customer after order is approved 
$settings["methodConfirmMessage"] = 'Thank You.';
// ============================================================================ 
// USER SETTINGS [ END ] ====================================================== 
// METHOD SETTINGS : do not change 
// method name: the user-friendly name shown for this payment option at checkout 
$settings["methodName"] = 'In-Store Account';
// method type: processor (off-site processing), gateway (integrated credit card form), account (in-store account) 
$settings["methodType"] = 'account';
// CONFIG DATA : These variables are returned to the calling page in the "CWauthMethod" scope 
$caller = array();
if (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == "config") {
	// clear scope from previous file operations (in case of multiple payment methods in use) 
	$caller["CWauthMethod"] = array();
	$caller["CWauthMethod"]["methodName"] = $settings["methodName"];
	$caller["CWauthMethod"]["methodType"] = $settings["methodType"];
	$caller["CWauthMethod"]["methodImg"] = $settings["methodImg"];
	$caller["CWauthMethod"]["methodSelectMessage"] = $settings["methodSelectMessage"];
	$caller["CWauthMethod"]["methodSubmitMessage"] = $settings["methodSubmitMessage"];
	$caller["CWauthMethod"]["methodConfirmMessage"] = $settings["methodConfirmMessage"];
// PROCESS ORDER 
// if called in 'process' mode, process order 
} elseif (isset($auth_settings["auth_mode"]) && $auth_settings["auth_mode"] == "process") {
	if (!isset($auth_settings["trans_data"])) $auth_settings["trans_data"] = "";
	// verify transaction data is ok 
	if (!is_array($auth_settings["trans_data"])) {
		$_ENV["request.trans"]["errorMessage"] = 'Payment Data Incomplete';
	// if data is ok, run processing 
	} else {
		// simplify to 'tdata' struct 
		$tdata = $auth_settings["trans_data"];
		if (!isset($tdata["customerID"])) $tdata["customerID"] = "";
		try {
			// RETURN THESE VARIABLES 
			$_ENV["request.trans"]["paymentTransStatus"] = 'approved';
			$_ENV["request.trans"]["paymentTransID"] = 'ACCT:' . $tdata["customerID"];
			$_ENV["request.trans"]["paymentTransResponse"] = 'Order charged to account';
			$_ENV["request.trans"]["paymentStatus"] = $_ENV["request.trans"]["paymentTransStatus"];
		} catch (Exception $e) {
			$_ENV["request.trans"]["errorMessage"] = 'Processing Error - Transaction Incomplete';
		}
		// WIPE TRANSACTION DATA 
		unset($settings);
		unset($tdata);
	}
	// /end if transaction data is ok 
	// /END PROCESS ORDER MODE 
}
// /END MODE SELECTION 
?>