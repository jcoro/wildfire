<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-error-exception.php
File Date: 2012-02-01
Description: handles exception errors via error handling in Application.php
==========================================================
*/
?><?php
// GLOBAL INCLUDES 
require_once("Application.php");
?><?php
if (!isset($_ENV["application.cw"]["developerEmail"])) $_ENV["application.cw"]["developerEmail"] = "";
if (!isset($_GET["errno"])) $_GET["errno"] = "0";
if (!isset($_GET["errline"])) $_GET["errline"] = "0";
if (!isset($_GET["errfile"])) $_GET["errfile"] = "no file specified";
if (!isset($_GET["errstr"])) $_GET["errstr"] = "An error occurred.";
if (!isset($_GET["errpage"])) $_GET["errpage"] = "";
if (!isset($_GET["errqs"])) $_GET["errqs"] = "";
if (!isset($_GET["errpost"])) $_GET["errpost"] = "";
if (!isset($_GET["errget"])) $_GET["errget"] = "";
if (!isset($_ENV["application.cw"]["siteName"])) $_ENV["application.cw"]["siteName"] = "";
if (!isset($_ENV["application.cw"]["appSiteUrlHttp"])) $_ENV["application.cw"]["appSiteUrlHttp"] = "";
if (!isset($_ENV["application.cw"]["errorHeading"])) $_ENV["application.cw"]["errorHeading"] = "Error";
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : ERROR</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<?php // CARTWEAVER CSS ?>
		<link href="cw4/css/cw-core.css" rel="stylesheet" type="text/css">
	</head>
	<body class="cw">
		<div style="text-align:center;">
			<div class="CWcontent">
				<div style="padding:120px 0;margin:0 auto;width:370px;">
					<?php // Display error message ?>
					<h1 style="text-align:center;">Exception Error</h1>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p style="text-align:center;">The <a href="mailto:<?php echo $_ENV["application.cw"]["developerEmail"]; ?>">site administrator</a> has been notified.</p>
					<p style="text-align:center;">Please use your browser's back button <br>or select an alternative option to proceed.</p>
				</div>
			</div>
			<div class="clear"></div>
<?php
// send email 
if ($_ENV["application.cw"]["developerEmail"]) {
	try {
		require_once("cw4/cwapp/func/cw-func-mail.php");
		// set up the error content 
		$errorText = "<p>Current URL: ".$_ENV["request.cw"]["thisUrl"]."<br>
Time: ".strftime("%c")."<br>
</p>
<p>
User's Browser: ".$_SERVER["HTTP_USER_AGENT"]."<br>
URL Parameters: ".$_GET["errqs"]."<br>
Posted Form: ".(($_GET["errpost"]) ? "<br>".implode("<br>", explode("&", $_GET["errpost"])) : "" )."<br>
Get Form: ".(($_GET["errget"]) ? "<br>".implode("<br>", explode("&", $_GET["errget"])) : "" )."<br>
Previous Page: ".$_GET["errpage"]."<br>
<br>
------------------------------------<br>
An error ".
(($_GET["errno"]) ? "(".$_GET["errno"].") " : "" )."occured".
(($_GET["errline"]) ? " on line ".$_GET["errline"] : "" ).
(($_GET["errfile"]) ? " of file ".$_GET["errfile"] : "" ).":<br>".
$_GET["errstr"]."<br>
------------------------------------<br>
<br>
</p>
";
		if (isset($_ENV["application.cw"]["errorlog"])) {
			$errorText .= "<p>View Log File: <a href=\"".$logfileurl."\">".$logfileurl."</a></p>
";
		}
		// Send an email message to site administrator --->
		$result = CWsendMail($errorText, $_ENV["application.cw"]["companyName"]." : error on Page ".$_GET["errfile"],$_ENV["application.cw"]["developerEmail"]);
		if (!isset($result) || sizeof($result)) echo $errorText;
	} catch (Exception $e) { }
}
?>
		</div>
	</body>
</html>