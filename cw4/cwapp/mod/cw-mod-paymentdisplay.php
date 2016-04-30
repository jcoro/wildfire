<?php
// only show or run processing if at least one payment method is selected in admin settings 
// default for list of available methods 
if (!isset($_ENV["application.cw"]["authMethods"])) { $_ENV["application.cw"]["authMethods"] = array(); }
$authMethodsArr = $_ENV["application.cw"]["authMethods"];
if (!is_array($authMethodsArr) && strlen($authMethodsArr)) $authMethodsArr = explode(",", $authMethodsArr);
else if (!is_array($authMethodsArr)) $authMethodsArr = array();
if (count($authMethodsArr) > 0) {
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-paymentdisplay.php
File Date: 2012-03-02
Description: creates and displays payment options, or credit card form for input
Parses information from files in the directory: cwapp/auth/
based on selections made in the Admin (see "Payment Settings")
NOTES:
Payment options and associated configuration data are stored as an array, $_ENV["application.cw"]["authMethods"]
==========================================================
*/
	// display mode (select|capture) 
	if (!isset($module_settings["display_mode"])) { $module_settings["display_mode"] = "select"; }
	// edit shipping url (not used for select mode - blank = not shown at all) 
	if (!isset($module_settings["edit_auth_url"])) { $module_settings["edit_auth_url"] = $_ENV["request.cw"]["thisPage"].'?authreset=1'; }
	// heading for selected method (in confirmation/submit order display) 
	if (!isset($module_settings["selected_title"])) { $module_settings["selected_title"] = "Payment Method Selected"; }
	// heading for credit card form 
	if (!isset($module_settings["form_title"])) { $module_settings["form_title"] = "Enter Credit Card Information"; }
	// heading for payment method selector 
	if (!isset($module_settings["option_title"])) { $module_settings["option_title"] = "Select Payment Method"; }
	// show logos if they exist in the auth file 
	if (!isset($module_settings["show_auth_logo"])) { $module_settings["show_auth_logo"] = true; }
	// if no logo exists, or show logo is false, show name of payment method 
	if (!isset($module_settings["show_auth_name"])) { $module_settings["show_auth_name"] = true; }
	// bypass payment, used in case of no-cost orders 
	if (!isset($module_settings["bypass_payment"])) { $module_settings["bypass_payment"] = false; }
	// defaults for cc form 
	if (!isset($_POST['customer_cardname'])) { $_POST['customer_cardname'] = ""; }
	if (!isset($_POST['customer_cardtype'])) { $_POST['customer_cardtype'] = ""; }
	if (!isset($_POST['customer_cardnumber'])) { $_POST['customer_cardnumber'] = ""; }
	if (!isset($_POST['customer_cardexpm'])) { $_POST['customer_cardexpm'] = ""; }
	if (!isset($_POST['customer_cardexpy'])) { $_POST['customer_cardexpy'] = ""; }
	if (!isset($_POST['customer_cardccv'])) { $_POST['customer_cardccv'] = ""; }
	// default list of cc input errors 
	if (!isset($_ENV["request.cwpage"]["formErrors"])) { $_ENV["request.cwpage"]["formErrors"] = array(); }
	// directory for ccv help image 
	if (!isset($_ENV["request.cw"]["assetSrcDir"])) { $_ENV["request.cw"]["assetSrcDir"] = $_ENV["application.cw"]["appCwContentDir"]; }
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	// global functions 
	include("../inc/cw-inc-functions.php");
	// clean up form and url variables 
	include("../inc/cw-inc-sanitize.php");
	chdir($myDir);
	// if only one payment option, preselect this method 
	$authMethodsArr = $_ENV["application.cw"]["authMethods"];
	if (!is_array($authMethodsArr) && strlen($authMethodsArr)) $authMethodsArr = explode(",", $authMethodsArr);
	else if (!is_array($authMethodsArr)) $authMethodsArr = array();
	if(count($authMethodsArr) == 1) {
		$_SESSION["cw"]["authPref"] = $authMethodsArr[0];
		$_SESSION["cw"]["confirmAuthPref"] = true;
		// set client variable for payment type 
		$_SESSION["cwclient"]["cwCustomerAuthPref"] = $_SESSION["cw"]["authPref"];
	}
	elseif (isset($_POST['authPref']) && $_POST['authPref'] > 0) {
		// HANDLE PAYMENT SELECTION SUBMISSION 
		// set in session memory, mark confirmed 
		$_SESSION["cw"]["authPref"] = $_POST['authPref'];
		$_SESSION["cw"]["confirmAuthPref"] = true;
		// set client variable for payment type 
		$_SESSION["cwclient"]["cwCustomerAuthPref"] = $_SESSION["cw"]["authPref"];
		// redirect to clear form variables 
		header("Location: ".$_ENV["request.cw"]["thisPageQS"]);
	}
	// if authreset exists in url, clear previously selected values 
	if(isset($_GET['authreset']) && $_GET['authreset'] == 1 && count($authMethodsArr) >= 2) {
		$_SESSION["cw"]["authPref"] = 0;
		$_SESSION["cw"]["authPrefName"] = '';
		$_SESSION["cw"]["confirmAuthPref"] = false;
		$_SESSION["cwclient"]["cwCustomerAuthPref"] = 0;
	}
	// only run if not bypassing payment 
	if (!$module_settings["bypass_payment"]) {
		// IF PAYMENT METHOD IS SELECTED in session, and id is in list of active options 
		if(isset($_SESSION["cw"]["authPref"]) && is_numeric($_SESSION["cw"]["authPref"]) && $_SESSION["cw"]["authPref"] > 0 && in_array($_SESSION["cw"]["authPref"], $authMethodsArr)) {
			// the id of the auth method is stored in the user's session 
			$authID = $_SESSION["cw"]["authPref"]-1;
			// defaults for auth method settings 
			if(!isset($_ENV["application.cw"]["authMethodData"][$authID]['methodID'])) { $_ENV["application.cw"]["authMethodData"][$authID]['methodID'] = 0; }
			if(!isset($_ENV["application.cw"]["authMethodData"][$authID]['methodName'])) { $_ENV["application.cw"]["authMethodData"][$authID]['methodName'] = ""; }
			if(!isset($_ENV["application.cw"]["authMethodData"][$authID]['methodType'])) { $_ENV["application.cw"]["authMethodData"][$authID]['methodType'] = "none"; }
			if(!isset($_ENV["application.cw"]["authMethodData"][$authID]['methodImg'])) { $_ENV["application.cw"]["authMethodData"][$authID]['methodImg'] = ""; }
			if(!isset($_ENV["application.cw"]["authMethodData"][$authID]['methodSelectMessage'])) { $_ENV["application.cw"]["authMethodData"][$authID]['methodSelectMessage'] = ""; }
			if(!isset($_ENV["application.cw"]["authMethodData"][$authID]['methodSubmitMessage'])) { $_ENV["application.cw"]["authMethodData"][$authID]['methodSubmitMessage'] = ""; }
			// look up details based on authMethodData stored in application scope 
			$CWauth = array();
			// defaults for auth method settings 
			$CWauth['methodID'] = $_ENV["application.cw"]["authMethodData"][$authID]['methodID']; 
			$CWauth['methodName'] = $_ENV["application.cw"]["authMethodData"][$authID]['methodName']; 
			$CWauth['methodType'] = $_ENV["application.cw"]["authMethodData"][$authID]['methodType']; 
			$CWauth['methodImg'] = $_ENV["application.cw"]["authMethodData"][$authID]['methodImg'];
			$CWauth['methodSelectMessage'] = $_ENV["application.cw"]["authMethodData"][$authID]['methodSelectMessage']; 
			$CWauth['methodSubmitMessage'] = $_ENV["application.cw"]["authMethodData"][$authID]['methodSubmitMessage']; 
			// payment selection details in customer session 
			$_SESSION["cw"]["authType"] = $CWauth['methodType'];
			$_SESSION["cw"]["authPrefName"] = $CWauth['methodName'];
			$_SESSION["cw"]["confirmAuthPref"] = true;
			// CAPTURE MODE 
			// CAPTURE MODE (credit card form) 
			// CAPTURE MODE 
			if($module_settings["display_mode"] == 'capture') {
				// submit order message 
				if(strlen(trim($CWauth['methodSubmitMessage'])) && $_SESSION["cw"]["confirmAuthPref"] = true) {
	?>
						<p class="CWformMessage CWclear"><?php echo trim($CWauth['methodSubmitMessage']);?></p>
	<?php			
				}
				// if gateway payment type, show credit card inputs  
				if($CWauth['methodType'] == 'gateway') {
					// QUERY: get credit cards available 
					$creditCardsQuery = CWquerySelectCreditCards();
					// CREDIT CARD INPUT FORM 
					// credit card form elements ?>
						<table class="CWformTable">
							<tbody>
								<tr class="headerRow">
									<th colspan="2">
	<?php
					if(strlen(trim($module_settings["form_title"]))) {
	?>
										<h3 class="CWformTitle"><?php echo $module_settings["form_title"]; ?></h3>
	<?php							
					}
	?> 
									</th>
								</tr>
								<?php // card holder name ?>
								<tr>
								<th class="label required">Card Holder Name</th>
								<td>
									<input name="customer_cardname" id="customer_cardname" class="{required:true}<?php if(in_array('customer_cardname', $_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" type="text" size="35" value="<?php echo $_POST['customer_cardname']; ?>" title="Enter Card Holder Name">
								</td>
								</tr>
								<?php // card type ?>
								<tr>
								<th class="label required">Card Type</th>
								<td>
									<select name="customer_cardtype" id="customer_cardtype" class="{required:true}<?php if(in_array('customer_cardtype', $_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" title="Select Credit Card Type">
										<option value="">-- Select --</option>
	<?php
					for($i=0; $i<$creditCardsQuery["totalRows"]; $i++) {
	?>
											<option value="<?php echo $creditCardsQuery["creditcard_code"][$i]; ?>"<?php if($_POST['customer_cardtype'] == $creditCardsQuery["creditcard_code"][$i]) {?> selected="selected"<?php } ?>><?php echo $creditCardsQuery["creditcard_name"][$i]; ?></option>
	<?php								
					}
	?>                                
									</select>
								</td>
								</tr>
								<?php // card number (value not persisted) ?>
								<tr>
								<th class="label required">Card Number</th>
								<td>
									<input name="customer_cardnumber" id="customer_cardnumber" class="{required:true,minlength:13,maxlength:19}<?php if(in_array('customer_cardnumer', $_ENV["request.cwpage"]["formErrors"])) {?> warning<?php } ?>" type="text" size="24" maxlength="19" value="" title="Enter Card Numer" onkeyup="extractNumeric(this,0,false)" autocomplete="off">
								</td>
								</tr>
								<?php // expiration ?>
								<tr>
								<th class="label required">Expiration Date</th>
								<td>
									Month <select name="customer_cardexpm" id="customer_cardexpm" class="{required:true}<?php if(in_array('customer_cardexpm', $_ENV["request.cwpage"]["formErrors"])) {?> warning<?php } ?>" title="Select Expiration Month">
												<option value="" selected="selected">--</option>
	<?php
					for($mm=1; $mm<=12; $mm++) {
						if (strlen($mm) == 1) $mm = "0".$mm;
	?>
													<option value="<?php echo $mm; ?>"><?php echo $mm; ?></option>
	<?php										
					}
	?>                                        
											</select>
									Year <select name="customer_cardexpy" id="customer_cardexpy" class="{required:true}<?php if(in_array('customer_cardexpy', $_ENV["request.cwpage"]["formErrors"])) {?> warning<?php } ?>" title="Select Expiration Year">
												<option value="" selected="selected">--</option>
	<?php
					$todayDate = date("Y");
					$dateEightYearsAdded = date("Y", strtotime("+8 Years"));
					for($yy=$todayDate; $yy<=$dateEightYearsAdded; $yy++) {
	?>
													<option value="<?php echo $yy; ?>"><?php echo $yy; ?></option>
	<?php											
					}
	?>                                        
										</select>
								</td>
								</tr>
								<?php // ccv code ?>
								<tr>
								<th class="label required">CCV Code</th>
								<td>
									<input name="customer_cardccv" id="customer_cardccv" class="{required:true,minlength:3,maxlength:4}<?php if(in_array('customer_cardccv', $_ENV["request.cwpage"]["formErrors"])) {?> warning<?php }?>" type="text" size="5" maxlength="4" value="" title="Enter Card CCV Code" onkeyup="extractNumeric(this,0,false)" autocomplete="off">
									<a id="CWccvLink" class="CWlink" style="display:none" href="#">What's this?</a>
									<div id="CWccvExplain" style="display:none;">
										<img alt="" src="<?php echo $_ENV["request.cw"]["assetSrcDir"]; ?>theme/ccv-location.png">
										<a href="#" id="CWccvClose">Close Window</a>
									</div>
								</td>
								</tr>
						</table>
	<?php
					// /end credit card form 
					$ccformjs = "<script type=\"text/javascript\">
					jQuery(document).ready(function() {
						// show ccv link, create popup
						jQuery('#CWccvLink').show().click(function() {
							jQuery('#CWccvExplain').toggle();
								return false;
						});
						// close window with click anywhere
						jQuery('#CWccvClose').click(function() {
							jQuery('#CWccvExplain').toggle();
								return false;
						}).parents('#CWccvExplain').click(function() {
							jQuery(this).toggle();
						});
					});
					</script>";
					CWinsertHead($ccformjs);
				}
				// /end if gateway 
				// /end CAPTURE mode 
				// SELECT MODE w/ authPref selected: display of option 
				// if a method is selected, and not using 'capture' mode, show the selected method here 
			} else {
				if(strlen(trim($module_settings["selected_title"]))) {
	?>
						<h3 class="CWformTitle"><?php echo $module_settings["selected_title"]; ?></h3>
		<?php				
				}
				// only create markup if some element exists 
				if ((strlen(trim($module_settings["edit_auth_url"])) && count($authMethodsArr) > 1) || (strlen(trim($CWauth['methodImg'])) && $module_settings["show_auth_logo"]) || $module_settings["show_auth_name"]) {
	?>
					<div class="CWpaymentOption">
						<p>
	<?php
					if(strlen(trim($module_settings["edit_auth_url"])) && count($authMethodsArr) > 1) {
	?>
							<span class="CWeditLink">&raquo;&nbsp;<a href="<?php echo $module_settings["edit_auth_url"]; ?>">Change Method</a></span>
	<?php						
					}
					// logo / image 
					if(strlen(trim($CWauth['methodImg'])) && $module_settings["show_auth_logo"]) {
	?>
							<span class="CWpaymentLogo">
							<img src="<?php echo $CWauth['methodImg']; ?>" alt="<?php echo $CWauth['methodName']; ?>" class="CWpaymentImage"><br>
							</span>
	<?php						
					} elseif ($module_settings["show_auth_name"]) {
						// if no logo, show name ?>
							<span class="CWpaymentName">
							<?php echo $CWauth['methodName']; ?>
							</span>
	<?php						
					}
	?>                    
						</p>
					</div>
	<?php
				}
			}
			// /end SELECT mode 
		// IF NO PAYMENT METHOD SELECTED (not in user's session) 
		} else {
			// clear value in case bogus value exists 
			$_SESSION["cwclient"]["cwCustomerAuthPref"] = 0;
			$_SESSION["cw"]["authPref"] = 0;
			$_SESSION["cw"]["confirmAuthPref"] = false;
			$_SESSION["cw"]["authType"] = 'none';
			// show payment selection system 
			if(strlen(trim($module_settings["option_title"]))) { ?>
					<h3 class="CWformTitle"><?php echo $module_settings["option_title"];?></h3>
	<?php
			}
			// if more than one method is available, show form for payment selection ?>
				<form id="CWformPaymentSelection" action="<?php echo $_ENV["request.cw"]["thisPage"]; ?>" method="post" class="CWvalidate">
	<?php
			// show output for each option 
			$authMethodsArr = $_ENV["application.cw"]["authMethods"];
			if (!is_array($authMethodsArr) && strlen($authMethodsArr)) $authMethodsArr = explode(",", $authMethodsArr);
			else if (!is_array($authMethodsArr)) $authMethodsArr = array();
			foreach ($authMethodsArr as $key => $i) {
				$i--;
				$CWauth = array();
				if(!isset($_ENV["application.cw"]["authMethodData"][$i]['methodID'])) { $_ENV["application.cw"]["authMethodData"][$i]['methodID'] = ""; }
				if(!isset($_ENV["application.cw"]["authMethodData"][$i]['methodName'])) { $_ENV["application.cw"]["authMethodData"][$i]['methodName'] = ""; }
				if(!isset($_ENV["application.cw"]["authMethodData"][$i]['methodType'])) { $_ENV["application.cw"]["authMethodData"][$i]['methodType'] = ""; }
				if(!isset($_ENV["application.cw"]["authMethodData"][$i]['methodImg'])) { $_ENV["application.cw"]["authMethodData"][$i]['methodImg'] = ""; }
				if(!isset($_ENV["application.cw"]["authMethodData"][$i]['methodSelectMessage'])) { $_ENV["application.cw"]["authMethodData"][$i]['methodSelectMessage'] = ""; }
				$CWauth['methodID'] = $_ENV["application.cw"]["authMethodData"][$i]['methodID'];
				$CWauth['methodName'] = $_ENV["application.cw"]["authMethodData"][$i]['methodName'];
				$CWauth['methodType'] = $_ENV["application.cw"]["authMethodData"][$i]['methodType'];
				$CWauth['methodImg'] = $_ENV["application.cw"]["authMethodData"][$i]['methodImg'];
				$CWauth['methodSelectMessage'] = $_ENV["application.cw"]["authMethodData"][$i]['methodSelectMessage'];
				// create container for each element w/ related info ?>
					<div class="CWpaymentOption">
	<?php
				// payment info message 
				if(strlen(trim($CWauth['methodSelectMessage'])) && $_SESSION["cw"]["confirmAuthPref"] == false) {
	?>
						<p class="CWformMessage CWclear"><?php echo trim($CWauth['methodSelectMessage']); ?></p>
	<?php					
				}
	?> 
					<label>
					<?php // hidden link, shown with javascript ?>
					<a href="#" class="CWselectLink" style="display:none;">Select</a>
					<input type="radio" name="authPref" class="required" value="<?php echo $CWauth['methodID']; ?>"<?php if(isset($_SESSION["cw"]["authPref"]) && $_SESSION["cw"]["authPref"] == $CWauth['methodID']) { ?> checked="checked"<?php } ?>>
					<span class="CWpaymentName">
					<?php echo $CWauth['methodName']; ?>
					</span>
	<?php
				// logo / image 
				if(strlen(trim($CWauth['methodImg']))) {
	?>
						<span class="CWpaymentLogo">
						<img src="<?php echo $CWauth['methodImg']; ?>" alt="<?php echo $CWauth['methodName']; ?>" class="CWpaymentImage"><br>
						</span>
	<?php				
				}
	?>  
					 </label>
					 </div>              
	<?php				
			}
			// submit button, hidden with javascript (options submit on click) ?>
				<div class="center CWclear top40">
					<input type="submit" class="CWformButton" id="CWpaymentSelectSubmit" value="Submit Selection&nbsp;&raquo;">
				</div>
				</form>
				<div class="CWclear"></div>
	<?php
			// javascript for selection 
			$paymentSelectJS = "<script type=\"text/javascript\">
			jQuery(document).ready(function(){
				// replace radio buttons with links
				jQuery('#CWformPaymentSelection').find('input:radio').each(function(){
					jQuery(this).hide().siblings('.CWselectLink').show();
				});
				// clicking link submits form
				jQuery('#CWformPaymentSelection a.CWselectLink').click(function(){
					jQuery(this).siblings('input:radio').prop('checked','checked');
					jQuery(this).parents('form').submit();
					return false;
				});
				// checkout link submits form (for validation)
				jQuery('#CWformPaymentSelection a.CWcheckoutLink').click(function(){
					jQuery('form#CWformPaymentSelection').submit();
					return false;
				});
				// hide submit button
				jQuery('#CWpaymentSelectSubmit').hide();
				// form submits on click of anything in label
				jQuery('#CWformPaymentSelection .CWpaymentOption > *').css('cursor','pointer').click(function(){
					jQuery(this).find('input:radio').prop('checked','checked');
					jQuery(this).parents('form').submit();
					return false;
				});
			});
			</script>";
			CWinsertHead($paymentSelectJS);
		// /end payment options 
		}
		// /end IF METHOD SELECTED 
	}
	// /end if bypass_payment = false 
}
// /end if payment methods exist 
?>