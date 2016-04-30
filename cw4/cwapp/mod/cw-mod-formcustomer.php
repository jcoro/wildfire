<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-formcustomer.php
File Date: 2012-02-01
Description:
Creates and displays customer information form, handles updates
==========================================================
*/ 
if(!(isset($_SESSION["cwclient"]["cwCustomerID"]))) $_SESSION["cwclient"]["cwCustomerID"] = 0;
if(!(isset($_SESSION["cwclient"]["cwCustomerType"]))) $_SESSION["cwclient"]["cwCustomerType"] = 0;
if(!(isset($_SESSION["cwclient"]["cwCustomerName"]))) $_SESSION["cwclient"]["cwCustomerName"] = 0;
if(!(isset($_SESSION["cwclient"]["cwShipRegionID"]))) $_SESSION["cwclient"]["cwShipRegionID"] = 0;
if(!(isset($_SESSION["cwclient"]["cwShipCountryID"]))) $_SESSION["cwclient"]["cwShipCountryID"] = 0;
if(!(isset($_SESSION["cwclient"]["cwTaxRegionID"]))) $_SESSION["cwclient"]["cwTaxRegionID"] = 0;
if(!(isset($_SESSION["cwclient"]["cwTaxCountryID"]))) $_SESSION["cwclient"]["cwTaxCountryID"] = 0;
if(!(isset($_SESSION["cwclient"]["cwCustomerCheckout"]))) $_SESSION["cwclient"]["cwCustomerCheckout"] = "account";
// page to relocate to on success (if blank, go to same page) 
if(!(isset($module_settings["success_url"]))) $module_settings["success_url"] = $_ENV["request.cwpage"]["urlShowCart"];
if(!(strlen(trim($module_settings["success_url"])))) $module_settings["success_url"] = $_ENV["request.cw"]["thisPage"];
// page for form base action 	
if(!(isset($_ENV["request.cwpage"]["hrefUrl"]))) $_ENV["request.cwpage"]["hrefUrl"] = trim($_ENV["application.cw"]["appCWStoreRoot"]). $_ENV["request.cw"]["thisPage"];  
// page for form to post to 
if(!(isset($module_settings["form_action"]))) $module_settings["form_action"] = $_ENV["request.cwpage"]["hrefUrl"];    
// text on submit button 
if(!(isset($module_settings["submit_value"]))) $module_settings["submit_value"] = "Submit Details&nbsp;&raquo;";       
// persist confirmation status in session 
if(!(isset($module_settings["mark_confirmed"]))) $module_settings["mark_confirmed"] = true;
if(!(isset($_SESSION["cw"]["confirmAddress"]))) $_SESSION["cw"]["confirmAddress"] = false;    	
// show username/pw fields 
if(!(isset($module_settings["show_account_info"]))) $module_settings["show_account_info"] = true;    
// show shipping fields 
if(!(isset($_ENV["application.cw"]["shipDisplayInfo"]))) $_ENV["application.cw"]["shipDisplayInfo"] = true;
if(!(isset($_ENV["application.cw"]["shipEnabled"]))) $_ENV["application.cw"]["shipEnabled"] = true;
if($_ENV["application.cw"]["shipEnabled"]) $shipDisplayInfo = $_ENV["application.cw"]["shipDisplayInfo"];
else $shipDisplayInfo = false;
if(!(isset($_ENV["application.cw"]["appDisplayCountryType"]))) $_ENV["application.cw"]["appDisplayCountryType"] = "single";
// customer guest account 
if (!isset($_ENV["request.cwpage"]["customerGuest"])) $_ENV["request.cwpage"]["customerGuest"] = 0;
// check for duplicate username / pw 
if (!(isset($_ENV["request.cwpage"]["dupCheck"]))) $_ENV["request.cwpage"]["dupCheck"] = true;
// country/state display type (single|split) 
if(!(isset($_ENV["request.cwpage"]["stateSelectType"]))) $_ENV["request.cwpage"]["stateSelectType"] = $_ENV["application.cw"]["appDisplayCountryType"];
// custom errors can be passed in here 
$_ENV["request.cwpage"]["formErrors"] = array();	
// global functions 
$myDir = getcwd();
chdir(dirname(__FILE__));
require_once("../inc/cw-inc-functions.php");
// clean up form and url variables 
require_once("../inc/cw-inc-sanitize.php");
chdir($myDir);  

// HANDLE FORM SUBMISSION 
if(isset($_POST['customer_email'])) {    
	// validate required fields (server side validation controlled here - each field contains rules for javascript validation separately) 
	$requiredTextFields = array('customer_first_name','customer_last_name','customer_phone','customer_address1','customer_city','customer_billing_state','customer_zip');
	// shipping fields, if used 
	if($shipDisplayInfo) {
		array_push($requiredTextFields,'customer_ship_name','customer_ship_address1','customer_ship_city','customer_ship_state','customer_ship_zip');	
	}
	// validate username and pw if accounts are enabled 
	if($module_settings["show_account_info"]) {    
		array_push($requiredTextFields,'customer_username','customer_password');
	}
	foreach ($requiredTextFields as $ff) {
		// verify some content exists for each field 
		if(!(strlen(trim($_POST[trim($ff)]))) && !(in_array(trim($ff), $_ENV["request.cwpage"]["formErrors"]))) {
			$_ENV["request.cwpage"]["formErrors"][] = trim($ff);
		}
	}
	// validate email 
	if(!isValidEmail($_POST['customer_email']) && !(in_array('customer_email', $_ENV["request.cwpage"]["formErrors"]))) {
		$_ENV["request.cwpage"]["formErrors"][] = 'customer_email';
		CWpageMessage("alert","Error: Email must be a valid email address");
	}
	// validate customer account fields 
	if(!$module_settings["show_account_info"]) {
		// bypass duplicate  
		$_ENV["request.cwpage"]["dupCheck"] = false;
		// bypass account info w/ default username/pw 
		// username defaults to email address 
		if(!(strlen(trim($_POST['customer_username']))))
			$_POST['customer_username'] = $_POST['customer_email'];   
		// password defaults to random number 
		if(!(strlen(trim($_POST['customer_password'])))) {
			$pwVal = mt_rand(1000000,9999999);
			$_POST['customer_password'] = $pwVal;
			$_POST['customer_password2'] = $pwVal;
		}
		// if using account info, validate each field 
	} else {
		// validate username length 
		if((strlen(trim($_POST['customer_username']))) < 6) {
			$_ENV["request.cwpage"]["formErrors"][] = 'customer_username';
			CWpageMessage("alert","Error: Username must be at least 6 characters");
		}
		// validate password length 
		if((strlen(trim($_POST['customer_password']))) < 6) {
			$_ENV["request.cwpage"]["formErrors"][] = 'customer_password';
			CWpageMessage("alert","Error: Password must be at least 6 characters");
		}
		// validate password confirmation 
		if(!(strlen(trim($_POST['customer_password']))) && trim($_POST['customer_password']) == trim($_POST['customer_password2'] )) {
			$_ENV["request.cwpage"]["formErrors"][] = 'customer_password2';
			CWpageMessage("alert","Error: Password confirmation must match password");
		}
	}
	// if errors exist 
	if(!(count($_ENV["request.cwpage"]["formErrors"]) > 0)) {
		// record submission in customer's session 
		$_SESSION["cw"]["confirmAddress"] = true;
		// replace shipping values with customer values if not using shipping 
		if(!($shipDisplayInfo)) {
			$_POST['customer_ship_name'] = $_POST['customer_first_name'].' ' . $_POST['customer_last_name'];
			$_POST['customer_ship_company'] = $_POST['customer_company'];
			$_POST['customer_ship_address1'] = $_POST['customer_address1'];
			$_POST['customer_ship_address2'] = $_POST['customer_address2'];
			$_POST['customer_ship_city'] = $_POST['customer_city'];
			$_POST['customer_ship_state'] = $_POST['customer_billing_state'];
			$_POST['customer_ship_zip'] = $_POST['customer_zip'];
		}
		// if logged in, run update instead of insert 
		if(isset($_SESSION["cwclient"]["cwCustomerID"]) && strlen($_SESSION["cwclient"]["cwCustomerID"]) && $_SESSION["cwclient"]["cwCustomerID"] !== 0 && $_SESSION["cwclient"]["cwCustomerID"] !== "0") {
			// skip duplicate check if accounts are not required, and not changing username or user type 
			if(isset($_ENV["application.cw"]["customerAccountRequired"]) && !$_ENV["application.cw"]["customerAccountRequired"] && $_POST["customer_username"] == $_SESSION["cwclient"]["cwCustomerName"] && $_POST["customer_type_id"] == $_SESSION["cwclient"]["cwCustomerType"] && $_POST["customer_type_id"] != 0) {
				$_ENV["request.cwpage"]["dupCheck"] = false;
			} else {
				$_ENV["request.cwpage"]["dupCheck"] = $module_settings["show_account_info"];
			}
			// /////// 
			// UPDATE CUSTOMER 
			// /////// 
			// QUERY: update customer record (all customer form variables) 
			$updateCustomerID = CWqueryUpdateCustomer(
				$_SESSION["cwclient"]["cwCustomerID"],
				$_POST['customer_type_id'],
				$_POST['customer_first_name'],
				$_POST['customer_last_name'],
				$_POST['customer_email'],
				$_POST['customer_username'],
				$_POST['customer_password'],
				$_POST['customer_company'],
				$_POST['customer_phone'],
				$_POST['customer_phone_mobile'],
				$_POST['customer_address1'],
				$_POST['customer_address2'],
				$_POST['customer_city'],
				$_POST['customer_billing_state'],
				$_POST['customer_zip'],
				$_POST['customer_ship_name'],
				$_POST['customer_ship_company'],
				$_POST['customer_ship_address1'],
				$_POST['customer_ship_address2'],
				$_POST['customer_ship_city'],
				$_POST['customer_ship_state'],
				$_POST['customer_ship_zip'],
				$_ENV["request.cwpage"]["dupCheck"]
				);
			// the query function above checks for duplicate fields, if results starts with 0-, a dup field was found 
			if(substr($updateCustomerID,0,2) == "0-") {    
				$newVarForList1 = explode('-',$updateCustomerID);	
				$dupField = $newVarForList1[count($newVarForList1) - 1];
				CWpageMessage("alert","Error: " . $dupField ." already exists");
				$_ENV["request.cwpage"]["formErrors"][] = 'customer_'.$dupField;
				// no errors, update complete, return to page 
			} else {
				CWpageMessage("confirm","Customer Updated");
				// get region (stateprov) details 
				$customerTaxRegionQuery = CWquerySelectStateProvDetails($_POST['customer_billing_state']);
				$customerShipRegionQuery = CWquerySelectStateProvDetails($_POST['customer_ship_state']);
				if($_ENV["application.cw"]["taxChargeBasedOn"] == 'billing') {
					if($customerTaxRegionQuery['totalRows'] > 0) {
						$_SESSION["cwclient"]["cwTaxRegionID"] = $customerTaxRegionQuery['stateprov_id'][0];
						$_SESSION["cwclient"]["cwTaxCountryID"] = $customerTaxRegionQuery['stateprov_country_id'][0];
					}
				}
				if($customerShipRegionQuery['totalRows'] > 0) {
					$_SESSION["cwclient"]["cwShipRegionID"] = $customerShipRegionQuery['stateprov_id'][0];
					$_SESSION["cwclient"]["cwShipCountryID"] = $customerShipRegionQuery['stateprov_country_id'][0];
					if($_ENV["application.cw"]["taxChargeBasedOn"] == 'shipping') {
						$_SESSION["cwclient"]["cwTaxRegionID"] = $customerShipRegionQuery['stateprov_id'][0];
						$_SESSION["cwclient"]["cwTaxCountryID"] = $customerShipRegionQuery['stateprov_country_id'][0];
					}
				}
				$_SESSION["cwclient"]["cwCustomerName"] = trim($_POST['customer_username']);
				$_SESSION["cwclient"]["cwCustomerType"] = trim($_POST['customer_type_id']);
				// remove guest status if not on checkout page 
				$coArr = explode("/", $_ENV["request.cwpage"]["urlCheckout"]);
				if ($_ENV["request.cw"]["thisPage"] != $coArr[sizeof($coArr)-1]) {
					$_SESSION["cwclient"]["cwCustomerCheckout"] = 'account';
				}
				// reset ship total, since ship address may have changed 
				$_SESSION["cwclient"]["cwShipTotal"] = 0;
				header("Location: ".$module_settings["success_url"]);
				exit;	
			}
			// /END duplicate/error check 
			// /////// 
			// /END UPDATE CUSTOMER 
			// /////// 
			// if not logged in, add new customer 
		} else {
			// /////// 
			// ADD NEW CUSTOMER 
			// /////// 
			// set boolean variable for customer guest yes/no 
			if ($_SESSION["cwclient"]["cwCustomerCheckout"] == 'guest')
				$_ENV["request.cwpage"]["customerGuest"] = 1;
			else
				$_ENV["request.cwpage"]["customerGuest"] = 0;

			// QUERY: Add new customer (all customer form variables) 
			// this query returns the customer id, or an error like '0-fieldname' 
			$newCustomerID = CWqueryInsertCustomer(
					$_POST['customer_type_id'],
					$_POST['customer_first_name'],
					$_POST['customer_last_name'],
					$_POST['customer_email'],
					$_POST['customer_username'],
					$_POST['customer_password'],
					$_POST['customer_company'],
					$_POST['customer_phone'],
					$_POST['customer_phone_mobile'],
					$_POST['customer_address1'],
					$_POST['customer_address2'],
					$_POST['customer_city'],
					$_POST['customer_billing_state'],
					$_POST['customer_zip'],
					$_POST['customer_ship_name'],
					$_POST['customer_ship_company'],
					$_POST['customer_ship_address1'],
					$_POST['customer_ship_address2'],
					$_POST['customer_ship_city'],
					$_POST['customer_ship_state'],
					$_POST['customer_ship_zip'],
					$_ENV["request.cwpage"]["dupCheck"],
					$_ENV["request.cwpage"]["customerGuest"]
					);
			// if no error returned from insert query 
			if(!(substr($newCustomerID,0,2) == '0-')) {
				// set client vars 
				$_SESSION["cwclient"]["cwCustomerID"] = $newCustomerID;
				if ($_ENV["request.cwpage"]["customerGuest"] != 1) {
					$_SESSION["cwclient"]["cwCustomerType"] = $_POST["customer_type_id"];
				}
				$_SESSION["cwclient"]["cwCustomerName"] = trim($_POST['customer_username']);
				// get region (stateprov) details 
				$customerTaxRegionQuery = CWquerySelectStateProvDetails($_POST['customer_billing_state']);    
				$customerShipRegionQuery = CWquerySelectStateProvDetails($_POST['customer_ship_state']);
				if($_ENV["application.cw"]["taxChargeBasedOn"] == 'billing') {
					if($customerTaxRegionQuery['totalRows'] > 0) {
						$_SESSION["cwclient"]["cwTaxRegionID"] = $customerTaxRegionQuery['stateprov_id'][0];
						$_SESSION["cwclient"]["cwTaxCountryID"] = $customerTaxRegionQuery['stateprov_country_id'][0];
					}
				}
				if($customerShipRegionQuery['totalRows'] > 0) {
					$_SESSION["cwclient"]["cwShipRegionID"] = $customerShipRegionQuery['stateprov_id'][0];
					$_SESSION["cwclient"]["cwShipCountryID"] = $customerShipRegionQuery['stateprov_country_id'][0];
					if($_ENV["application.cw"]["taxChargeBasedOn"] == 'shipping') {
						$_SESSION["cwclient"]["cwTaxRegionID"] = $customerShipRegionQuery['stateprov_id'][0];
						$_SESSION["cwclient"]["cwTaxCountryID"] = $customerShipRegionQuery['stateprov_country_id'][0];
					}
				}
				$_SESSION["cwclient"]["cwCustomerName"] = trim($_POST['customer_username']);
				// update complete: return to page 
				CWpageMessage("confirm","Customer Added");
				header("Location: ".$module_settings["success_url"]);
				exit;
				// if we have an insert error, show message, do not insert 
			} else {
				$newVarForList2 = explode('-',$newCustomerID);
				$dupField = $newVarForList2[count($newVarForList2) - 1];
				CWpageMessage("alert","Error: " . $dupField . " already exists");
				$_ENV["request.cwpage"]["formErrors"][] = 'customer_'.$dupField;
			}
			// /END duplicate/error check 
			// /////// 
			// /END ADD NEW CUSTOMER 
			// /////// 
		}
		// /end if customer logged in 
	}
	// /end if no errors 
}
// /end if form submitted 

// QUERY: get customer details
$customerQuery = CWquerySelectCustomerDetails($_SESSION["cwclient"]["cwCustomerID"]);
// QUERY: get customer's shipping info (customer id)
$shippingQuery = CWquerySelectCustomerShipping($_SESSION["cwclient"]["cwCustomerID"]);
// QUERY: get all states / countries 
$statesQuery = CWquerySelectStates();    
// setting this to 0 hides the customer types dropdown 
$typesQuery['totalRows'] = 0;    
// uncomment the line below, and remove the line above, to show customer type selection 
// QUERY: get all customer types 
// params for all form fields - if new entry, customer query returns blank values for all fields 
if(!(isset($customerQuery['customer_type_id'][0]) && is_numeric($customerQuery['customer_type_id'][0]))) $customer_type = 1;
else $customer_type = $customerQuery['customer_type_id'][0];
if(!(isset($_POST['customer_type_id']))) $_POST['customer_type_id'] = $customer_type;
if(!(isset($_POST['customer_first_name']))) $_POST['customer_first_name'] = ((isset($customerQuery['customer_first_name'][0])) ? $customerQuery['customer_first_name'][0] : "" );
if(!(isset($_POST['customer_last_name']))) $_POST['customer_last_name'] = ((isset($customerQuery['customer_last_name'][0])) ? $customerQuery['customer_last_name'][0] : "" );
if(!(isset($_POST['customer_email']))) $_POST['customer_email'] = ((isset($customerQuery['customer_email'][0])) ? $customerQuery['customer_email'][0] : "" );
if(!(isset($_POST['customer_username']))) $_POST['customer_username'] = ((isset($customerQuery['customer_username'][0])) ? $customerQuery['customer_username'][0] : "" );
if(!(isset($_POST['customer_password']))) $_POST['customer_password'] = ((isset($customerQuery['customer_password'][0])) ? $customerQuery['customer_password'][0] : "" );
if(!(isset($_POST['customer_password2']))) $_POST['customer_password2'] = ((isset($customerQuery['customer_password'][0])) ? $customerQuery['customer_password'][0] : "" );
if(!(isset($_POST['customer_company']))) $_POST['customer_company'] = ((isset($customerQuery['customer_company'][0])) ? $customerQuery['customer_company'][0] : "" );
if(!(isset($_POST['customer_phone']))) $_POST['customer_phone'] = ((isset($customerQuery['customer_phone'][0])) ? $customerQuery['customer_phone'][0] : "" );
if(!(isset($_POST['customer_phone_mobile']))) $_POST['customer_phone_mobile'] = ((isset($customerQuery['customer_phone_mobile'][0])) ? $customerQuery['customer_phone_mobile'][0] : "" );
if(!(isset($_POST['customer_address1']))) $_POST['customer_address1'] = ((isset($customerQuery['customer_address1'][0])) ? $customerQuery['customer_address1'][0] : "" );
if(!(isset($_POST['customer_address2']))) $_POST['customer_address2'] = ((isset($customerQuery['customer_address2'][0])) ? $customerQuery['customer_address2'][0] : "" );
if(!(isset($_POST['customer_city']))) $_POST['customer_city'] = ((isset($customerQuery['customer_city'][0])) ? $customerQuery['customer_city'][0] : "" );
if(!(isset($_POST['customer_billing_state']))) $_POST['customer_billing_state'] = ((isset($customerQuery['customer_billing_state'][0])) ? $customerQuery['customer_billing_state'][0] : "" );
if(!(isset($_POST['customer_billing_country_id']))) $_POST['customer_billing_country_id'] = ((isset($customerQuery['country_id'][0])) ? $customerQuery['country_id'][0] : 0 );
if(!(isset($_POST['customer_zip']))) $_POST['customer_zip'] = ((isset($customerQuery['customer_zip'][0])) ? $customerQuery['customer_zip'][0] : "" );
if(!(isset($_POST['customer_ship_name']))) $_POST['customer_ship_name'] = ((isset($shippingQuery['customer_ship_name'][0])) ? $shippingQuery['customer_ship_name'][0] : "" );
if(!(isset($_POST['customer_ship_company']))) $_POST['customer_ship_company'] = ((isset($shippingQuery['customer_ship_company'][0])) ? $shippingQuery['customer_ship_company'][0] : "" );
if(!(isset($_POST['customer_ship_address1']))) $_POST['customer_ship_address1'] = ((isset($shippingQuery['customer_ship_address1'][0])) ? $shippingQuery['customer_ship_address1'][0] : "" );
if(!(isset($_POST['customer_ship_address2']))) $_POST['customer_ship_address2'] = ((isset($shippingQuery['customer_ship_address2'][0])) ? $shippingQuery['customer_ship_address2'][0] : "" );
if(!(isset($_POST['customer_ship_city']))) $_POST['customer_ship_city'] = ((isset($shippingQuery['customer_ship_city'][0])) ? $shippingQuery['customer_ship_city'][0] : "" );
if(!(isset($_POST['customer_ship_state']))) $_POST['customer_ship_state'] = ((isset($shippingQuery['customer_state_destination'][0])) ? $shippingQuery['customer_state_destination'][0] : "" );
if(!(isset($_POST['customer_ship_country_id']))) $_POST['customer_ship_country_id'] = ((isset($shippingQuery['country_id'][0])) ? $shippingQuery['country_id'][0] : 0 );
if(!(isset($_POST['customer_ship_zip']))) $_POST['customer_ship_zip'] = ((isset($shippingQuery['customer_ship_zip'][0])) ? $shippingQuery['customer_ship_zip'][0] : "");
// determine selected country if using split country/state lists 
if ($_POST["customer_billing_country_id"] > 0) {
	$_ENV["request.cwpage"]["selectedCountry"] = $_POST["customer_billing_country_id"];
} else if (isset($_ENV["application.cw"]["defaultCountryID"]) && $_ENV["application.cw"]["defaultCountryID"] > 0) {
	$_ENV["request.cwpage"]["selectedCountry"] = $_ENV["application.cw"]["defaultCountryID"];
} else {
	$_ENV["request.cwpage"]["selectedCountry"] = 0;
}
// selected shipping country 
if ($_POST["customer_ship_country_id"] > 0) {
	$_ENV["request.cwpage"]["selectedShipCountry"] = $_POST["customer_ship_country_id"];
} else if (isset($_ENV["application.cw"]["defaultCountryID"]) && $_ENV["application.cw"]["defaultCountryID"] > 0) {
	$_ENV["request.cwpage"]["selectedShipCountry"] = $_ENV["application.cw"]["defaultCountryID"];
} else {
	$_ENV["request.cwpage"]["selectedShipCountry"] = 0;
}
// javascript for form, includes 'same as billing' checkbox function 
if(!(isset($_ENV["request.cwpage"]["customerFormScript"]))) {
	$_ENV["request.cwpage"]["customerFormScript"] = '
	<script type="text/javascript">
		jQuery(document).ready(function() {
			';
	if($shipDisplayInfo) {
		$_ENV["request.cwpage"]["customerFormScript"] .= "// reset list of shipping states from reserve element
			var \$resetShipState = function(){
				jQuery('#customer_ship_state').remove();
				jQuery('#customer_ship_state_reserve').clone(true).show().insertBefore('#customer_ship_state_reserve').attr('name','customer_ship_state').attr('id','customer_ship_state');
			};
			// copy billing info to shipping
			jQuery('span#sameAs').show();
			jQuery('#copyInfo').click(function() {
			// if checking the box
			if (jQuery(this).prop('checked')==true) {
				// get values of shipping
				var valName = jQuery('#customer_first_name').val() + ' ' + jQuery('#customer_last_name').val();
				var valCo = jQuery('#customer_company').val();
				var valAddr1 = jQuery('#customer_address1').val();
				var valAddr2 = jQuery('#customer_address2').val();
				var valCity = jQuery('#customer_city').val();
				var valCountry = jQuery('#customer_billing_country_id').val();
				var valState = jQuery('#customer_billing_state').val();
				var valZip = jQuery('#customer_zip').val();
				var valCountryText = jQuery('#CWcustomerBillingCountry').text();
				// copy to billing, remove functionality
				jQuery('#customer_ship_name').val(valName).attr('readonly','readonly').addClass('inputNull');
				jQuery('#customer_ship_company').val(valCo).attr('readonly','readonly').addClass('inputNull');
				jQuery('#customer_ship_address1').val(valAddr1).attr('readonly','readonly').addClass('inputNull');
				jQuery('#customer_ship_address2').val(valAddr2).attr('readonly','readonly').addClass('inputNull');
				jQuery('#customer_ship_city').val(valCity).attr('readonly','readonly').addClass('inputNull');
				jQuery('#customer_ship_zip').val(valZip).attr('readonly','readonly').addClass('inputNull');
				jQuery('#CWcustomerShippingCountry').text(valCountryText);
				// remove remaining state dropdown options
				";
		if ($_ENV["request.cwpage"]["stateSelectType"] == 'split') {
			$_ENV["request.cwpage"]["customerFormScript"] .= "\$resetShipState();
					jQuery('#customer_ship_country_id').val(valCountry).attr('readonly','readonly').addClass('inputNull').trigger('change');
				";
		} else {
			$_ENV["request.cwpage"]["customerFormScript"] .= "					jQuery('#customer_ship_state').clone(true).hide().insertAfter('#customer_ship_state').attr('name','customer_ship_state_reserve').attr('id','customer_ship_state_reserve');
				jQuery('#customer_ship_country_id').val(valCountry).attr('readonly','readonly').addClass('inputNull');
				jQuery('#customer_ship_state').val(valState).find('option').not(':selected').remove();
				jQuery('#customer_ship_state option').unwrap();
				jQuery('#customer_ship_state optgroup').remove();
				";
		}
		$_ENV["request.cwpage"]["customerFormScript"] .= "jQuery('#customer_ship_state').val(valState).attr('readonly','readonly').addClass('inputNull');
				// if the box is NOT checked
			} else {
				// restore functionality
				jQuery(this).parents('table').find('.inputNull').removeAttr('readonly').removeClass('inputNull');
				// restore original dropdown
				\$resetShipState();
				var valState = jQuery('#customer_billing_state').val();
				jQuery('#customer_ship_state').val(valState);";
		if ($_ENV["request.cwpage"]["stateSelectType"] == "split") {
			$_ENV["request.cwpage"]["customerFormScript"] .= "
				\$setShipState();";
		}
		$_ENV["request.cwpage"]["customerFormScript"] .= "
			}
			});
			// monitor changes after same as billing box checked
			var \$copyData = function(billField,shipField) {
				if (jQuery('#copyInfo').prop('checked')==true) {
					var billVal = jQuery(billField).val();
					jQuery(shipField).val(billVal);
				}
			};
				jQuery('#customer_company').keyup(function() {
					\$copyData(this,'#customer_ship_company');
				});
				jQuery('#customer_address1').keyup(function() {
					\$copyData(this,'#customer_ship_address1');
				});
				jQuery('#customer_address2').keyup(function() {
					\$copyData(this,'#customer_ship_address2');
				});
				jQuery('#customer_city').keyup(function() {
					\$copyData(this,'#customer_ship_city');
				});
				jQuery('#customer_billing_state').keyup(function() {
					\$copyData(this,'#customer_ship_state');
				});
				jQuery('#customer_zip').keyup(function() {
					\$copyData(this,'#customer_ship_zip');
				});
				// name gets special treatment
				jQuery('#customer_first_name').keyup(function() {
					if (jQuery('#copyInfo').prop('checked')==true) {
						var valName = jQuery('#customer_first_name').val() + ' ' + jQuery('#customer_last_name').val();
						jQuery('#customer_ship_name').val(valName);
					}
				});
				jQuery('#customer_last_name').keyup(function() {
					if (jQuery('#copyInfo').prop('checked')==true) {
						var valName = jQuery('#customer_first_name').val() + ' ' + jQuery('#customer_last_name').val();
						jQuery('#customer_ship_name').val(valName);
					}
				});
				// state dropdown";
		if ($_ENV["request.cwpage"]["stateSelectType"] == "split") {
			$_ENV["request.cwpage"]["customerFormScript"] .= "
				var \$setShipState = function(){
					var valState = jQuery('#customer_billing_state').val();
					var valCountry = jQuery('#customer_ship_country_id').val();
					var keepClass = 'optCS-' + valCountry;
					jQuery('#customer_ship_state').find('option').not('.' + keepClass).remove();
					if (jQuery('#copyInfo').prop('checked')==true){
						\$resetShipState();
						jQuery('#customer_ship_state').val(valState).attr('readonly','readonly').addClass('inputNull');
					}
				};
				jQuery('#customer_ship_country_id').change(function(){
						var countryID = jQuery(this).val();
						var keepClass = 'optCS-' + countryID;
						\$resetShipState();
						jQuery('#customer_ship_state').find('option').not('.' + keepClass).remove();
						if (jQuery('#copyInfo').prop('checked')==true){
							jQuery('#customer_ship_state').attr('readonly','readonly').addClass('inputNull');
						}
				});
				jQuery('#customer_billing_state').change(function(){
					\$setShipState();
				});
				jQuery('#customer_billing_state_reserve').change(function(){
					\$setShipState();
				});";
		} else {
			$_ENV["request.cwpage"]["customerFormScript"] .= "
				// state dropdown
				jQuery('#customer_billing_state').change(function(){
					var countryText = jQuery('#customer_billing_state option:selected').parents('optgroup').attr('label');
					jQuery('#CWcustomerBillingCountry').text(countryText);
					if (jQuery('#copyInfo').prop('checked')==true){
						jQuery('#customer_ship_state').remove();
						jQuery('#customer_ship_state_reserve').clone(true).show().insertBefore('#customer_ship_state_reserve').attr('name','customer_ship_state').attr('id','customer_ship_state');
						var valState = jQuery('#customer_billing_state').val();
						jQuery('#customer_ship_state').val(valState).attr('readonly','readonly').addClass('inputNull').find('option').not(':selected').remove();
						jQuery('#customer_ship_state option').unwrap();
						jQuery('#customer_ship_state optgroup').remove();
						jQuery('#CWcustomerShippingCountry').text(countryText);
					}
				});
				jQuery('#customer_ship_state').change(function(){
					var countryText = jQuery('#customer_ship_state option:selected').parents('optgroup').attr('label');
					jQuery('#CWcustomerShippingCountry').text(countryText);
				});";
		}
		// /end if split or single country list 
	}
	// /end if shipping shown 
	if ($_ENV["request.cwpage"]["stateSelectType"] == "split") {
		$_ENV["request.cwpage"]["customerFormScript"] .= "
				jQuery('#customer_billing_country_id').change(function(){
					var countryID = jQuery(this).find('option:selected').attr('value');
					var keepClass = 'optCB-' + countryID;
					var valState = jQuery('#customer_billing_state').val();
					jQuery('#customer_billing_state').remove();
					jQuery('#customer_billing_state_reserve').clone(true).show().insertBefore('#customer_billing_state_reserve').attr('name','customer_billing_state').attr('id','customer_billing_state');
					jQuery('#customer_billing_state').find('option').not('.' + keepClass).remove();
					if (jQuery('#copyInfo').prop('checked')==true){
						jQuery('#customer_ship_country_id').val(countryID).trigger('change');
						jQuery('#customer_ship_state').val(valState);
					};
				});";
	}
	$_ENV["request.cwpage"]["customerFormScript"] .= "
			// show submit link instead of button
			jQuery('#customer_submit').hide();
			jQuery('#CWlinkCustomer').show().click(function() {
				jQuery('form#CWformCustomer').submit();
				return false;
			});
		});
	</script>
";
	CWinsertHead($_ENV["request.cwpage"]["customerFormScript"]);
}
	
// //////////// 
// START OUTPUT 
// //////////// ?>
<form class="CWvalidate" id="CWformCustomer" name="CWformCustomer" method="post" action="<?php echo $module_settings["form_action"];?>">
<?php
// ALERTS: capture any customer form errors 
if(count($_ENV["request.cwpage"]["formErrors"])) {    
?>	
	<div class="CWalertBox validationAlert" id="customerFormAlert">
	<?php
	// if form errors exist, but other alerts do not, show default alert 
	if((!is_array($_ENV["request.cwpage"]["userAlert"])) || (count($_ENV["request.cwpage"]["userAlert"]) == 0) || (is_array($_ENV["request.cwpage"]["userAlert"]) && count($_ENV["request.cwpage"]["userAlert"]) == 1 && $_ENV["request.cwpage"]["userAlert"][0] == '')) {     
	?>	
        <div class="alertText">
            Error: Complete all required information
        </div>
	<?php
	} else {
		foreach ($_ENV["request.cwpage"]["userAlert"] as $key => $aa) {
			if(strlen(trim($aa))) {
			?>	
		<div class="alertText">
			<?php echo str_replace('<br>','',$aa);?>
		</div>
			<?php
			}
		}
	}
	?>
	</div>
<?php
}

// customer details table ?>
    <table class="CWformTable">
        <tr>
            <td class="customerInfo" id="contactCell" colspan="2">
                <?php // contact details ?>
                <table class="CWformTable">
                    <tr class="headerRow">
                        <th colspan="2">
                            <h3>
                                Contact Details
                            </h3>
                        </th>
                    </tr>
                    <tr>
                        <th class="label required">
                            First Name
                        </th>
                        <td>
                            <input name="customer_first_name" class="{required:true}<?php if(in_array('customer_first_name',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="First Name is required" size="20" maxlength="254" type="text" id="customer_first_name" value="<?php echo $_POST['customer_first_name'];?>">
                        </td>
                    </tr>
                    <tr>
                        <th class="label required">
                            Last Name
                        </th>
                        <td>
                            <input name="customer_last_name" class="{required:true}<?php if(in_array('customer_last_name',$_ENV["request.cwpage"]["formErrors"])) { ?> warning <?php }?>" title="Last Name is required" size="20" maxlength="254" type="text" id="customer_last_name" value="<?php echo $_POST['customer_last_name'];?>">
                        </td>
                    </tr>
                    <tr>
                        <th class="label required">
                            Email
                        </th>
                        <td>
                            <input type="text" class="{required:true,email:true}<?php if(in_array('customer_email',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="Valid Email is required"  size="20" maxlength="254" name="customer_email" id="customer_email" value="<?php echo $_POST['customer_email'];?>">
                        </td>
                    </tr>
                    <tr>
                        <th class="label required">
                            Phone
                        </th>
                        <td>
                            <input type="text" class="{required:true}<?php if(in_array('customer_phone',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="Phone Number is required" size="14" maxlength="20" name="customer_phone" id="customer_phone" value="<?php echo $_POST['customer_phone'];?>">
                        </td>
                    </tr>
                    <tr>
                        <th class="label">
                            Mobile
                        </th>
                        <td>
                            <input type="text" size="14" maxlength="14" name="customer_phone_mobile" id="customer_phone_mobile" value="<?php echo $_POST['customer_phone_mobile'];?>">
                        </td>
                    </tr>
                </table>
                <?php // /end contact details
				// username / password
if($module_settings["show_account_info"]) {            
?>	
                <table class="CWformTable" id="customerAccount">
                    <tr class="headerRow">
                        <th colspan="2">
                            <h3>
                                Customer Account
                            </h3>
                        </th>
                    </tr>
	<?php
    // if we have more than one type, show the selector 
    if($typesQuery['totalRows'] > 1) {                 
    ?>	
                    <tr>
                        <th class="label">
                            Customer Type
                        </th>
                        <td>
                            <select name="customer_type_id" id="customer_type_id">
		<?php
        for($i = 0; $i < $typesQuery['totalRows']; $i++) {
        ?>
								<option value="<?php echo $typesQuery['customer_type_id'][$i];?>"<?php if($typesQuery['customer_type_id'][$i] == $_POST['customer_type_id']) {?> selected="selected"<?php }?>><?php echo $typesQuery['customer_type_name'][$i];?></option>
		<?php
		}
		?>
                            </select>
                        </td>
                    </tr>                  
	<?php
    // if only one type exists, use this by default 
    } else {
    ?>	
                    <input type="hidden" name="customer_type_id" id="customer_type_id" value="<?php echo $_POST['customer_type_id'];?>">
	<?php
    }
    // /end customer type ?> 	
                    <tr>
                        <th class="label required">
                            Username
                        </th>
                        <td>
                            <input name="customer_username" class="{required:true,minlength:6}<?php if(in_array('customer_username',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="Username is required (min. length 6)" size="20" maxlength="254" type="text" id="customer_username" value="<?php echo $_POST['customer_username'];?>">
                            <span class="smallPrint">&nbsp;&nbsp;(min. 6)</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="label required">
                            Password
                        </th>
                        <td>
                            <input name="customer_password" class="{required:true,minlength:6}<?php if(in_array('customer_password',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="Password is required (min. length 6)" size="20" maxlength="254" type="password" id="customer_password" value="<?php echo $_POST['customer_password'];?>">
                            <span class="smallPrint">&nbsp;&nbsp;(min. 6)</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="label required">
                            Confirm Password
                        </th>
                        <td>
                            <input name="customer_password2" class="{required:true,equalTo:'#customer_password'}<?php if(in_array('customer_password2',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="Password must match" size="20" maxlength="254" type="password" id="customer_password2" value="<?php echo $_POST['customer_password2'];?>">
                        </td>
                    </tr>
                </table>
<?php
} else {
	// if account info is not shown, use hidden fields ?>
                <div<?php if ($shipDisplayInfo) { ?> style="padding-top:135px;"<?php } ?> class="clear">
                    <?php // hidden inputs ?>
                    <input name="customer_username" type="hidden" id="customer_username_hidden" value="<?php echo $_POST['customer_username'];?>">
                    <input name="customer_password" type="hidden" id="customer_password_hidden" value="<?php echo $_POST['customer_password'];?>">
                    <input name="customer_password2" type="hidden" id="customer_password2_hidden" value="<?php echo $_POST['customer_password2'];?>">
                    <input name="customer_type_id" type="hidden" id="customer_type_id" value="<?php echo $_POST['customer_type_id'];?>">
                </div>
<?php
}
// /end username/pw
// SUBMIT BUTTON ?>
                <div class="CWclear"></div>
                <div class="center top40">
                    <input id="customer_submit" name="customer_submit" type="submit" class="CWformButton" value="<?php echo $module_settings["submit_value"];?>">
                    <?php // submit link : javascript replaces button with this link ?>
                    <a style="display:none;" href="#" class="CWcheckoutLink" id="CWlinkCustomer"><?php echo $module_settings["submit_value"];?></a>
                </div>
            </td>
            <td class="customerInfo" id="shippingCell" colspan="2">
                <?php // billing info ?>
                <table class="CWformTable">
                    <tr class="headerRow">
                        <th colspan="2">
                            <h3>
                                Billing Information
                            </h3>
                        </th>
                    </tr>
                    <tr>
                        <th class="label">
                            Company
                        </th>
                        <td>
                            <input type="text" size="20" maxlength="254" name="customer_company" id="customer_company" value="<?php echo $_POST['customer_company'];?>">
                        </td>
                    </tr>
                    <tr>
                        <th class="label required">
                            Address
                        </th>
                        <td>
                            <input type="text" class="{required:true}<?php if(in_array('customer_address1',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" size="20" maxlength="254" title="Billing Address is required"  name="customer_address1" id="customer_address1" value="<?php echo $_POST['customer_address1'];?>">
                            <br>
                            <br>
                            <input type="text" name="customer_address2" size="20" maxlength="254" id="customer_address2" value="<?php echo $_POST['customer_address2'];?>">
                        </td>
                    </tr>
                    <tr>
                        <th class="label required">
                            City
                        </th>
                        <td>
                            <input type="text" name="customer_city" size="20" maxlength="254" id="customer_city" class="{required:true}<?php if(in_array('customer_city',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="Billing City is required" value="<?php echo $_POST['customer_city'];?>">
                        </td>
                    </tr>
<?php
// country/state separated 
if ($_ENV["request.cwpage"]["stateSelectType"] == "split") {
?>
					<tr>
						<th class="label required">
							Country
						</th>
						<td>
							<?php // country value for selection of stateprov below, not individually validated or inserted ?>
							<select name="customer_billing_country_id" id="customer_billing_country_id"<?php if (in_array("customer_billing_state", $_ENV["request.cwpage"]["formErrors"])) {?> class="warning"<?php } ?>>
<?php
	for ($n=0; $n<$statesQuery["totalRows"]; $n++) {
		$lastCountry = $statesQuery["country_name"][$n];
?>
								<option value="<?php echo $statesQuery["country_id"][$n]; ?>"<?php if ($_ENV["request.cwpage"]["selectedCountry"] == $statesQuery["country_id"][$n]) { ?> selected="selected"<?php } ?>><?php echo $statesQuery["country_name"][$n]; ?></option>
<?php
		while ($n < $statesQuery["totalRows"] && $lastCountry == $statesQuery["country_name"][$n]) { $n++; }
		$n--;
	}
?>
							</select>
						</td>
					</tr>
					<tr>
						<th class="label required">
							State/Prov
						</th>
						<td>
							<?php // customer state, restricted by country ?>
							<select name="customer_billing_state" id="customer_billing_state"<?php if (in_array("customer_billing_state", $_ENV["request.cwpage"]["formErrors"])) {?> class="warning"<?php } ?>>
<?php
	for ($n=0; $n<$statesQuery["totalRows"]; $n++) {
		$lastCountry = $statesQuery["country_name"][$n];
		while ($n<$statesQuery["totalRows"] && $lastCountry == $statesQuery["country_name"][$n]) {
			if ($_ENV["request.cwpage"]["selectedCountry"] != 0 && $statesQuery["country_id"][$n] == $_ENV["request.cwpage"]["selectedCountry"]) {
?>
											<option value="<?php echo $statesQuery["stateprov_id"][$n]; ?>"<?php if ((isset($customerQuery["stateprov_id"][0]) && $statesQuery["stateprov_id"][$n] == $customerQuery["stateprov_id"][0]) || $statesQuery["stateprov_id"][$n] == $_POST["customer_billing_state"]) { ?> selected="selected"<?php } ?>><?php echo substr($statesQuery["stateprov_name"][$n],0,35); ?></option>
<?php
			}
			$n++;
		}
		$n--;
		// default for selection 
		if ($_ENV["request.cwpage"]["selectedCountry"] == 0) {
?>
										<option value="" selected="selected">--</option>
<?php
		}
	}
?>
							</select>
							<?php // hidden element used for jQuery DOM manipulation ?>
							<select name="" style="display:none;" id="customer_billing_state_reserve">
<?php
	for ($n=0; $n<$statesQuery["totalRows"]; $n++) {
		$lastCountry = $statesQuery["country_name"][$n];
		while ($n<$statesQuery["totalRows"] && $lastCountry == $statesQuery["country_name"][$n]) {
?>
										<option class="optCB-<?php echo $statesQuery["country_id"][$n]; ?>" value="<?php echo $statesQuery["stateprov_id"][$n]; ?>"><?php echo substr($statesQuery["stateprov_name"][$n],0,35); ?></option>
<?php
		
			$n++;
		}
		$n--;
	}
?>
							</select>
						</td>
					</tr>
<?php
// single list country/state output 
} else {
?>
					<tr>
						<th class="label required">
							State/Prov
						</th>
						<td>
							<select name="customer_billing_state" id="customer_billing_state"<?php if (in_array("customer_billing_state", $_ENV["request.cwpage"]["formErrors"])) {?> class="warning"<?php } ?>>
<?php
	for ($n=0; $n<$statesQuery["totalRows"]; $n++) {
?>
								<optgroup label="<?php echo $statesQuery["country_name"][$n]; ?>">
<?php
		$lastCountry = $statesQuery["country_name"][$n];
		while ($n<$statesQuery["totalRows"] && $lastCountry == $statesQuery["country_name"][$n]) {
?>
										<option value="<?php echo $statesQuery["stateprov_id"][$n]; ?>"<?php if ((isset($customerQuery["stateprov_id"][0]) && $statesQuery["stateprov_id"][$n] == $customerQuery["stateprov_id"][0]) || $statesQuery["stateprov_id"][$n] == $_POST["customer_billing_state"]) { ?> selected="selected"<?php } ?>><?php echo substr($statesQuery["stateprov_name"][$n],0,35); ?></option>
<?php
		
			$n++;
		}
		$n--;
?>
								</optgroup>
<?php
	}
?>
							</select>
						</td>
					</tr>
<?php
	// only show country if a saved record exists 
	if ($customerQuery["totalRows"]) {
?>
						<tr>
							<th class="label required">
								Country
							</th>
							<td class="CWtextCell">
								<span id="CWcustomerBillingCountry"><?php echo $customerQuery["country_name"][0]; ?></span>
							</td>
						</tr>
<?php
	}
	// /end country 
}
// /end separated or single lists 
?>
                    <tr>
                        <th class="label required">
                            Post Code/Zip
                        </th>
                        <td>
                            <input type="text" name="customer_zip" id="customer_zip" class="{required:true}<?php if(in_array('customer_zip',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="Billing Post Code is required" value="<?php echo $_POST['customer_zip'];?>" size="8" maxlength="20">
                        </td>
                    </tr>
                </table>
<?php
// /end billing info 

// shipping info 
if($shipDisplayInfo) {
?>	
                <table class="CWformTable">
                    <tr class="headerRow">
                        <th colspan="2">
                            <h3>
                                Shipping Information
                                <?php // the same-as checkbox is hidden unless javascript enabled ?>
                                <span class="smallPrint" id="sameAs" style="display:none;"><input type="checkbox" id="copyInfo">&nbsp;Same as Billing</span>
                            </h3>
                        </th>
                    </tr>
                    <tr>
                        <th class="label required">
                            Ship To (Name)
                        </th>
                        <td>
                            <input name="customer_ship_name" id="customer_ship_name" class="{required:true}<?php if(in_array('customer_ship_name',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="Ship To (name) is required" type="text" size="20" maxlength="254" value="<?php echo $_POST['customer_ship_name'];?>">
                        </td>
                    </tr>
                    <tr>
                        <th class="label">
                            Company
                        </th>
                        <td>
                            <input type="text" size="20" maxlength="254" name="customer_ship_company" id="customer_ship_company" value="<?php echo $_POST['customer_ship_company'];?>">
                        </td>
                    </tr>
                    <tr>
                        <th class="label required">
                            Address
                        </th>
                        <td>
                            <input type="text" name="customer_ship_address1" id="customer_ship_address1" class="{required:true}<?php if(in_array('customer_ship_address',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" size="20" maxlength="254" title="Shipping Address is required" value="<?php echo $_POST['customer_ship_address1'];?>">
                            <br>
                            <br>
                            <input type="text" name="customer_ship_address2" id="customer_ship_address2" size="20" maxlength="254" value="<?php echo $_POST['customer_ship_address2'];?>">
                        </td>
                    </tr>
                    <tr>
                        <th class="label required">
                            City
                        </th>
                        <td>
                            <input type="text" name="customer_ship_city" id="customer_ship_city" class="{required:true}<?php if(in_array('customer_ship_city',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" size="20" maxlength="254" title="Shipping City is required" value="<?php echo $_POST['customer_ship_city'];?>">
                        </td>
                    </tr>
<?php
	// country/state separated 
	if ($_ENV["request.cwpage"]["stateSelectType"] == "split") {
?>
					<tr>
						<th class="label required">
							Country
						</th>
						<td>
							<?php // country value for selection of stateprov below, not individually validated or inserted ?>
							<select name="customer_ship_country_id" id="customer_ship_country_id"<?php if (in_array("customer_ship_state", $_ENV["request.cwpage"]["formErrors"])) {?> class="warning"<?php } ?>>
<?php
		for ($n=0; $n<$statesQuery["totalRows"]; $n++) {
			$lastCountry = $statesQuery["country_name"][$n];
?>
								<option value="<?php echo $statesQuery["country_id"][$n]; ?>"<?php if ($_ENV["request.cwpage"]["selectedShipCountry"] == $statesQuery["country_id"][$n]) { ?> selected="selected"<?php } ?>><?php echo $statesQuery["country_name"][$n]; ?></option>
<?php
			while ($n < $statesQuery["totalRows"] && $lastCountry == $statesQuery["country_name"][$n]) { $n++; }
			$n--;
		}
?>
							</select>
						</td>
					</tr>
					<tr>
						<th class="label required">
							State/Prov
						</th>
						<td>
							<?php // customer state, restricted by country ?>
							<select name="customer_ship_state" id="customer_ship_state"<?php if (in_array("customer_ship_state", $_ENV["request.cwpage"]["formErrors"])) {?> class="warning"<?php } ?>>
<?php
		for ($n=0; $n<$statesQuery["totalRows"]; $n++) {
			$lastCountry = $statesQuery["country_name"][$n];
			while ($n<$statesQuery["totalRows"] && $lastCountry == $statesQuery["country_name"][$n]) {
				if ($_ENV["request.cwpage"]["selectedShipCountry"] != 0 && $statesQuery["country_id"][$n] == $_ENV["request.cwpage"]["selectedCountry"]) {
?>
											<option value="<?php echo $statesQuery["stateprov_id"][$n]; ?>"<?php if ((isset($shippingQuery["stateprov_id"][0]) && $statesQuery["stateprov_id"][$n] == $shippingQuery["stateprov_id"][0]) || $statesQuery["stateprov_id"][$n] == $_POST["customer_ship_state"]) { ?> selected="selected"<?php } ?>><?php echo substr($statesQuery["stateprov_name"][$n],0,35); ?></option>
<?php
				}
				$n++;
			}
			$n--;
			// default for selection 
			if ($_ENV["request.cwpage"]["selectedShipCountry"] == 0) {
?>
										<option value="" selected="selected">--</option>
<?php
			}
		}
?>
							</select>
							<?php // hidden element used for jQuery DOM manipulation ?>
							<select name="" style="display:none;" id="customer_ship_state_reserve">
<?php
		for ($n=0; $n<$statesQuery["totalRows"]; $n++) {
			$lastCountry = $statesQuery["country_name"][$n];
			while ($n<$statesQuery["totalRows"] && $lastCountry == $statesQuery["country_name"][$n]) {
?>
										<option class="optCS-<?php echo $statesQuery["country_id"][$n]; ?>" value="<?php echo $statesQuery["stateprov_id"][$n]; ?>"><?php echo substr($statesQuery["stateprov_name"][$n],0,35); ?></option>
<?php
		
				$n++;
			}
			$n--;
		}
?>
							</select>
						</td>
					</tr>
<?php
	// single list country/state output 
	} else {
?>
					<tr>
						<th class="label required">
							State/Prov
						</th>
						<td>
							<select name="customer_ship_state" id="customer_ship_state"<?php if (in_array("customer_billing_state", $_ENV["request.cwpage"]["formErrors"])) {?> class="warning"<?php } ?>>
<?php
		for ($n=0; $n<$statesQuery["totalRows"]; $n++) {
?>
								<optgroup label="<?php echo $statesQuery["country_name"][$n]; ?>">
<?php
			$lastCountry = $statesQuery["country_name"][$n];
			while ($n<$statesQuery["totalRows"] && $lastCountry == $statesQuery["country_name"][$n]) {
?>
										<option value="<?php echo $statesQuery["stateprov_id"][$n]; ?>"<?php if ((isset($shippingQuery["stateprov_id"][0]) && $statesQuery["stateprov_id"][$n] == $shippingQuery["stateprov_id"][0]) || $statesQuery["stateprov_id"][$n] == $_POST["customer_ship_state"]) { ?> selected="selected"<?php } ?>><?php echo substr($statesQuery["stateprov_name"][$n],0,35); ?></option>
<?php
				$n++;
			}
			$n--;
?>
								</optgroup>
<?php
		}
?>
							</select>
						</td>
					</tr>
<?php
		// only show country if a saved record exists 
		if ($customerQuery["totalRows"]) {
?>
						<tr>
							<th class="label required">
								Country
							</th>
							<td class="CWtextCell">
								<span id="CWcustomerShippingCountry"><?php echo $shippingQuery["country_name"][0]; ?></span>
							</td>
						</tr>
<?php
		}
		// /end country 
	}
	// /end separated or single lists 
?>
                    <tr>
                        <th class="label required">
                            Post Code/Zip
                        </th>
                        <td>
                            <input type="text" name="customer_ship_zip" id="customer_ship_zip" class="{required:true}<?php if(in_array('customer_ship_zip',$_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="Shipping Post Code is required"value="<?php echo $_POST['customer_ship_zip'];?>" size="8" maxlength="20">
                        </td>
                    </tr>
                </table>
                <?php
}
// /end shipping info
?>
            </td>
            <?php // /END shipping info ?>
        </tr>
        <?php // /END billing shipping ?>
    </table>
    <div class="CWclear"></div>
</form>
