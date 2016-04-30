<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-checkoutsteps.php
File Date: 2012-02-01
Description:
Creates graduated visual links representing steps for checkout process
Indicator variable = 'request.cwpage.currentStep', set in CW checkout include
==========================================================
*/ 
// characters shown between links 
$separator = ' &gt; ';
if(!(isset($_ENV["request.cwpage"]['currentStep']))) $_ENV["request.cwpage"]['currentStep'] = 0;
if(!(isset($_ENV["request.cwpage"]['shipDisplay']))) $_ENV["request.cwpage"]['shipDisplay'] = $_ENV["application.cw"]["shipEnabled"];
if(!(isset($_SESSION["cwclient"]["cwCustomerID"]))) $_SESSION["cwclient"]["cwCustomerID"] = 0;
?>	
<span id="CWcheckoutStepLinks">
<?php
if($_ENV["application.cw"]["customerAccountEnabled"]) {
// STEP 1: select new account/login ?>
    <span id="CWcheckoutStep1" class="step1">
    <a href="<?php echo $_ENV["request.cw"]["thisPage"];?>" <?php if($_ENV["request.cwpage"]['currentStep'] >= 1) {?> class="currentLink"<?php }?>>Login/Register</a>
    </span>
	<?php echo $separator;
}
// STEP 2: user/billing/shipping info ?>
	<span id="CWcheckoutStep2" class="step2">
	<a href="<?php echo $_ENV["request.cw"]["thisPage"];?>"<?php  if($_ENV["request.cwpage"]['currentStep'] >= 2 ) {?> class="currentLink"<?php }?>>Address<?php if($_ENV["application.cw"]["customerAccountEnabled"]) {?>/Account<?php } else {?> Details<?php }?></a>
	</span>
	<?php echo $separator;	
// STEP 3: select shipping method 
if($_ENV["request.cwpage"]["shipDisplay"]) {
?>	
    <span id="CWcheckoutStep3" class="step3">
    <a href="<?php echo $_ENV["request.cw"]["thisPage"];?>"<?php if($_ENV["request.cwpage"]['currentStep'] >= 3 ) {?> class="currentLink"<?php }?>>Shipping</a>
	</span>
	<?php echo  $separator;
}
// STEP 4: review and confirm order ?>
	<span id="CWcheckoutStep4" class="step4">
	<a href="<?php echo $_ENV["request.cw"]["thisPage"];?>"<?php if($_ENV["request.cwpage"]['currentStep'] >= 4) {?> class="currentLink"<?php }?>>Review &amp; Confirm</a>
	</span>
	<?php echo $separator;
// STEP 5: select / submit payment ?>
	<span id="CWcheckoutStep5" class="step5">
	<a href="<?php echo $_ENV["request.cw"]["thisPage"];?>"<?php if($_ENV["request.cwpage"]['currentStep']  >= 5 ) {?> class="currentLink"<?php }?>>Submit Order</a>
	</span>
</span>
