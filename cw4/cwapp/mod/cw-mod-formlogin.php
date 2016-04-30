<?php 
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-formlogin.php
File Date: 2012-07-03
Description: creates and displays customer login form, handles login processing
Can be included in any page or section of your site
==========================================================
*/
if(!(isset($_SESSION["cwclient"]["cwCustomerID"]))) $_SESSION["cwclient"]["cwCustomerID"] = 0;
if(!(isset($_POST['login_username']))) $_POST['login_username'] = "";
if(!(isset($_POST['login_password']))) $_POST['login_password'] = "";
if(!(isset($_POST['pw_email']))) $_POST['pw_email'] = "";
if(!(isset($_POST['login_remember']))) $_POST['login_remember'] = "";
// page for form base action 
if(!(isset($_ENV["request.cwpage"]["hrefUrl"]))) $_ENV["request.cwpage"]["hrefUrl"] = trim($_ENV["application.cw"]["appCWStoreRoot"]).$_ENV["request.cw"]["thisPage"];
// show login or 'get password' forms ( login | pw ) 
if(!(isset($_GET['mode']))) {$_GET['mode'] = "login" ;}
if(!(isset($module_settings["form_mode"]))) $module_settings["form_mode"] = $_GET['mode']; 
// page to relocate to on success 
if(!(isset($module_settings["success_url"]))) $module_settings["success_url"] = $_ENV["request.cw"]["thisPage"]; 
// page for form to post to 
if(!(isset($module_settings["form_action"]))) $module_settings["form_action"] = $_ENV["request.cwpage"]["hrefUrl"]; 
// heading for form markup 
if(!(isset($module_settings["form_heading"]))) $module_settings["form_heading"] = "Customer Login"; 
// message to show when password has been found 
if(!(isset($module_settings["pw_message"]))) $module_settings["pw_message"] = "Password has been sent to the email address provided";
// use the 'remember me' checkbox (boolean) 
if(!(isset($module_settings["remember_me"]))) $module_settings["remember_me"] = $_ENV["application.cw"]["customerRememberMe"];
// load the checkbox already checked (boolean) 
if(!(isset($module_settings["remember_me_checked"]))) $module_settings["remember_me_checked"] = $_POST['login_remember'];
// custom errors can be passed in here 
$_ENV["request.cwpage"]["loginErrors"] = array();
// global functions 
$myDir = getcwd();
chdir(dirname(__FILE__));
require_once("../inc/cw-inc-functions.php");
// clean up form and url variables 
require_once("../inc/cw-inc-sanitize.php");
chdir($myDir);
// base url for switching modes 
$loginVarsToKeep = CWremoveUrlVars("mode");
$baseLoginUrl = CWserializeURL($loginVarsToKeep,$_ENV["request.cw"]["thisPage"]);
if (strpos($baseLoginUrl, "=") !== false) $baseLoginUrl .= "&";
// HANDLE FORM SUBMISSION 
// LOGIN FORM 
if((isset($_POST['login_username']) && !$_POST['login_username'] == "") && (isset($_POST['login_password']) && !$_POST['login_password'] == "")) {
	// validate required fields (server side validation controlled here - each field contains rules for javascript validation separately) 
	$requiredTextFields = array('login_username','login_password');
	for($ff = 0; $ff < count($requiredTextFields); $ff++) {
		// verify some content exists for each field 
		if(!strlen(trim($_POST[$requiredTextFields[$ff]])) && !in_array($requiredTextFields[$ff],$_ENV["request.cwpage"]["loginErrors"])) {
			$_ENV["request.cwpage"]["loginErrors"][] = $requiredTextFields[$ff];
		}
	}
	// if errors exist 
	if(sizeof($_ENV["request.cwpage"]["loginErrors"])) {
		// if no errors, run login function 
	} else { 
		// QUERY: get username and pw 
		$loginQuery = CWqueryCustomerLogin(trim($_POST['login_username']),trim($_POST['login_password']));
		// if matched, login successful 
		if($loginQuery['totalRows'] == 1) {
			$_SESSION["cwclient"]["cwCustomerID"] = $loginQuery['customer_id'][0];
			$_SESSION["cwclient"]["cwCustomerName"] = $loginQuery['customer_username'][0];
			$_SESSION["cwclient"]["cwCustomerCheckout"] = 'account';
			// QUERY: get customer billing region and country 
			$customerQuery = CWquerySelectCustomerDetails($_SESSION["cwclient"]["cwCustomerID"]);
			// set customer type into session 
			if (is_numeric($customerQuery["customer_type_id"])) {
				$_SESSION["cwclient"]["cwCustomerType"] = $customerQuery["customer_type_id"];
			} else {
				$_SESSION["cwclient"]["cwCustomerType"] = "1";
			}
			// QUERY: get customer shipping region and country 
			$shippingQuery = CWquerySelectCustomerShipping($_SESSION["cwclient"]["cwCustomerID"]);
			// set customer tax region, ship region 
			$customerTaxRegionQuery = CWquerySelectStateProvDetails($customerQuery['stateprov_id'][0]);
			$customerShipRegionQuery = CWquerySelectStateProvDetails($shippingQuery['stateprov_id'][0]);
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
			// Store username inside a cookie if remember me was checked 
			if($module_settings["remember_me"]) {
				if(isset($_POST['login_remember'])) {
					setcookie("cwUserName", $_POST['login_username'], time()+(3600*24*365*2));
					// Else, clean any existing cookie 
				} else {
					setcookie("cwUserName","", time());
				}
			}
			// redirect to avoid reposting 
			header("Location: ".$module_settings["success_url"]);
			exit;
		} else {
			// if no match 
			$_SESSION["cwclient"]["cwCustomerID"] = 0;
			unset($_SESSION["cwclient"]["customerName"]);
			$_ENV["request.cwpage"]["loginErrors"][] = 'login_username';
			$_ENV["request.cwpage"]["loginErrors"][] = 'login_password';
			CWpageMessage("alert","Error: login not recognized");
			$module_settings["remember_me_checked"] = false;
		}
		// / end login match y/n 
	}
	// / end login validation errors 
}
// / end login form handling 

// PASSWORD FORM 
if(isset($_POST['pw_email']) && strlen(trim($_POST['pw_email']))) {
	// validate email 
	if(!isValidEmail(trim($_POST['pw_email'])) && !in_array('pw_email',$_ENV["request.cwpage"]["loginErrors"])) {
		$_ENV["request.cwpage"]["loginErrors"][] = 'pw_email';
	}
	// if errors exist 
	if(sizeof($_ENV["request.cwpage"]["loginErrors"])) {
		// set error for email address 
		CWpageMessage("alert","Error: Email must be a valid address");
		// if no errors, run login function 
	} else {
		// QUERY: get username and pw 
		$pwQuery = CWqueryCustomerLookup(trim($_POST['pw_email']));
		// if matched, login successful 
		if($pwQuery['totalRows'] == 1) {
			// send message: compile contents 
			$mailBody = CWtextPasswordReminder($pwQuery['customer_id'][0], $_ENV["request.cw"]["thisUrl"]);
			$mailContent = $_ENV["application.cw"]["mailDefaultPasswordSentIntro"]."
".chr(10).chr(13)."
".chr(10).chr(13)."
".$mailBody."
".chr(10).chr(13)."
".chr(10).chr(13)."
".$_ENV["application.cw"]["mailDefaultPasswordSendEnd"];
			// send the content to the customer, get response from the function 
			$messageResponse = CWsendMail($mailContent, 'Your '.$_ENV["application.cw"]["companyName"].' Account Information', $pwQuery['customer_email'][0]);
			// if there is any problem sending the message, add the response to the page message 
			$hadErrors = false;
			foreach ($messageResponse as $key => $value) {
				if (stripos($value, "error") !== false) {
					$hadErrors = true;
					break;
				}
			}
			if ($hadErrors) {
				CWpageMessage("alert",implode("<br>", $messageResponse));
				// second line of error - adding separately shows on a new line 
				CWpageMessage("alert","Contact Customer Service for assistance");
				$_ENV["request.cwpage"]["loginErrors"][] = 'pw_email';
				// if no error is returned 
			} else {
				// save temporary client (cookie) variable to show password sent 
				$_SESSION["cwclient"]["cwPwSent"] = trim($_POST['pw_email']);
				// set up url to redirect to 
				$pwSuccess_Url = $module_settings["success_url"];
				if(strpos($module_settings["success_url"],'?') === false) {
					$pwSuccess_Url .= '?';
				} else {
					$pwSuccess_Url .= '&'; 
				}
				$pwSuccess_Url .= 'mode=pw';
				// redirect user 
				header("Location: ".trim($pwSuccess_Url));
				exit;
			}
			// /end if error returned from mail function 
			// if no match, reset client cookie vars, show alert 
		} else {
			$_SESSION["cwclient"]["cwCustomerID"] = 0;
			unset($_SESSION["cwclient"]["customerName"]);
			$_ENV["request.cwpage"]["loginErrors"][] = 'pw_email';
			CWpageMessage("alert","Error: email address not recognized");
		}
		// / end email query match y/n 
	}
	// / end email validation errors 
}
/// /end password form handling 
// if using cookies, set blank value as default 
if($_ENV["application.cw"]["appCookieTerm"] != 0) {
	if(!isset($_COOKIE["cwUserName"])) {
		setcookie("cwUserName", "", time());
	}
	else if($_POST['login_username'] == '') {    
		$_POST['login_username'] = $_COOKIE["cwUserName"];
	}
}
// //////////// 
// START OUTPUT 
// ////////////?>
<form class="CWvalidate" id="CWformLogin" name="CWformLogin" method="post" action="<?php echo $module_settings["form_action"]; ?><?php if(!strstr($module_settings["form_action"],'?')) {  echo '?'; } else { echo '&';}?>mode=<?php echo $module_settings["form_mode"];?>">
<?php
// login form 
if($module_settings["form_mode"] == 'login') {
?>		
	<table class="CWformTable" id="loginFormTable">
        <tr class="headerRow">
            <th colspan="2">
	<?php				
    if(strlen(trim($module_settings["form_heading"]))) {
    ?>				
    			<h3>
					<?php echo $module_settings["form_heading"]; ?>
                </h3>
	<?php				
    } 
    // ALERTS: capture any login form errors 
	if(sizeof($_ENV["request.cwpage"]["loginErrors"])) {
	?>						 
				<div class="CWalertBox validationAlert" id="customerFormAlert">
		<?php                       
        // default alert 
		$alertArr = array();
		if (isset($_ENV["request.cwpage"]["userAlert"])) $alertArr = $_ENV["request.cwpage"]["userAlert"];
		if (!is_array($alertArr) && strlen($alertArr)) $alertArr = explode(",", $alertArr);
		else if (!is_array($alertArr)) $alertArr = array();
		if (!is_array($alertArr) || !count($alertArr)) {
		?>
        			<div class="alertText">Error: Complete all required information</div>
        <?php
		} else {
			foreach ($alertArr as $key => $aa) {
				if(strlen(trim($aa))) {
				?>
                	<div class="alertText"><?php echo str_replace('<br>','',$aa); ?></div>
                <?php
				}
			}
		}
		?>
        			<div class="alertText centered smallPrint"><a href="<?php echo $baseLoginUrl; ?>mode=pw">Recover Password</a></div>
              	</div>
	<?php
	}
	?>
            </th>
        </tr>
        <?php // username ?>
        <tr>
            <th class="label required">
                Username
            </th>
            <td>
                <input name="login_username" class="{required:true}<?php if(in_array('login_username',$_ENV["request.cwpage"]["loginErrors"])) {?> warning<?php }?>" title="Username is required" size="20" maxlength="254" type="text" id="login_username" value="<?php echo $_POST['login_username']; ?>">
            </td>
        </tr>
        <?php // password ?>
        <tr>
            <th class="label required">
                Password
            </th>
            <td>
                <input name="login_password" class="{required:true}<?php if(in_array('login_password',$_ENV["request.cwpage"]["loginErrors"])) {?> warning<?php }?>" title="Password is required" size="20" maxlength="254" type="password" id="login_password" value="<?php echo $_POST['login_password']; ?>">
            </td>
        </tr>
	<?php // customer message ?>
            <tr>
            	<td colspan="2">
					<p class="center smallPrint"><em>Note: username and password are case sensitive</em></p>
                </td>
            </tr>
	<?php
	// remember me 
    if($module_settings["remember_me"]) {
    ?>			
    	<tr>
            <td colspan="2">
                <p class="center">
                	<input type="checkbox" name="login_remember" value="true"<?php if($module_settings["remember_me_checked"] == true) { ?> checked="checked"<?php } ?>> Remember Me
                </p>
            </td>
        </tr>
    <?php
	}
	// submit ?>
        <tr>
            <td colspan="2" style="text-align:center;">
                <input name="login_submit" type="submit" class="CWformButtonSmall" id="login_submit" value="Log In">
            </td>
        </tr>
        <?php // password link ?>
        <tr>
            <td colspan="2" style="text-align:center;">
                <div class="centered smallPrint">
                    <a href="<?php echo $baseLoginUrl; ?>mode=pw">Recover Password</a>
                </div>
            </td>
        </tr>
    </table>
<?php
// forgot password form 
} else {
?>		
	<table class="CWformTable" id="loginFormTable">
        <tr class="headerRow">
            <th colspan="2">
                <h3>
                    Recover Password
                </h3>
<?php
	// ALERTS: capture any password form errors 
	if(sizeof($_ENV["request.cwpage"]["loginErrors"]) || isset($_SESSION["cwclient"]["cwPwSent"])) {
	?>
    			<div class="CWalertBox validationAlert" id="customerFormAlert">
    	<?php
		// default alert 
		$alertArr = array();
		if (isset($_ENV["request.cwpage"]["userAlert"])) $alertArr = $_ENV["request.cwpage"]["userAlert"];
		if (!is_array($alertArr) && sizeof($alertArr)) $alertArr = explode(",", $alertArr);
		else if (!is_array($alertArr)) $alertArr = array();
		if(isset($_SESSION["cwclient"]["cwPwSent"]) && strlen(trim($_SESSION["cwclient"]["cwPwSent"]))) {
		?>
        			<div class="alertText">Password has been sent to <?php echo trim($_SESSION["cwclient"]["cwPwSent"]); ?></div>
                    <div class="alertText centered smallPrint">
                        <a href="<?php echo $baseLoginUrl; ?>mode=login">Return to Login</a>
                    </div>
        <?php
		// remove client variable, message only shown once 
			unset($_SESSION["cwclient"]["cwPwSent"]);
		} else if(is_array($alertArr) && count($alertArr)) {
			foreach ($alertArr as $key => $aa) {
				if(strlen(trim($aa))) {
				?>
                	<div class="alertText"><?php echo str_replace('<br>','',$aa); ?></div>
                <?php
				}
			}
		} else if (isset($_ENV["request.cwpage"]["userAlert"]) && !is_array($_ENV["request.cwpage"]["userAlert"]) && strlen(trim($_ENV["request.cwpage"]["userAlert"]))) {
		?>
        			<div class="alertText">Error: Email must be a valid email</div>
        <?php
		}
		?>
        		</div>
	<?php
	}
	?>
    		</th>
        </tr>
        <?php // email ?>
        <tr>
            <th class="label required">
                Email Address
            </th>
            <td>
                <input name="pw_email" class="{required:true, email:true}<?php if(in_array('pw_email',$_ENV["request.cwpage"]["loginErrors"])) {?> warning<?php }?>" title="Email Address is required" size="20" maxlength="254" type="text" id="pw_email" value="<?php echo $_POST['pw_email']; ?>">
            </td>
        </tr>
        <?php // submit ?>
        <tr>
            <td colspan="2" style="text-align:center;">
                <input name="pw_submit" type="submit" class="CWformButtonSmall" id="pw_submit" value="Send Password">
            </td>
        </tr>
        <?php // login link ?>
        <tr>
            <td colspan="2" style="text-align:center;">
                <div class="centered smallPrint">
                    <a href="<?php echo $baseLoginUrl; ?>mode=login">Return to Login</a>
                </div>
            </td>
        </tr>
    </table>
<?php
}
// /end mode
?>
	<div class="CWclear"></div>
</form>
