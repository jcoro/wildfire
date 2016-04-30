<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-pageend.php
File Date: 2012-07-03
Description: sets cookie values
shows debugging output in store pages
==========================================================
*/
// SET COOKIES 
if (!isset($_ENV["application.cw"]["appCookieTerm"])) $_ENV["application.cw"]["appCookieTerm"] = '';
// if using cookies 
if ($_ENV["application.cw"]["appCookieTerm"] != 0) {
	// set all variables in 'session.cwclient' into cookie scope 
	if (isset($_SESSION["cwclient"])) {
		// vars not to write to cookies 
		if (!isset($_ENV["request.cw"]["noCookieSessionVars"])) $_ENV["request.cw"]["noCookieSessionVars"] = array("sessionid","jsessionid","urltoken","cwCustomerType","cwOrderTotal","cwShipTotal","cwShipCountryID","cwShipRegionID","cwShipTaxTotal","cwTaxCountryID","cwTaxRegionID","cwTaxTotal","cwPwSent");
		$ncsvArr = $_ENV["request.cw"]["noCookieSessionVars"];
		if (!is_array($ncsvArr) && strlen($ncsvArr)) $ncsvArr = explode(",", $ncsvArr);
		else if (!is_array($ncsvArr)) $ncsvArr = array();
		foreach ($_SESSION["cwclient"] as $cc => $cookieVal) {
			try {
				if (in_array($cc, $ncsvArr)) {
					if (is_array($cookieVal)) $cookieVal = "CWPHPARRAY:".implode(",", $cookieVal);
					if (strlen(trim($cookieVal))) {
						setcookie($cc, $cookieVal, $_ENV["application.cw"]["appCookieTerm"]);
					} else {
						setcookie($cc, "", 0);
					}
				}
			} catch (Exception $e) {
				// if debugging is on, output any errors to the page 
				if (isset($_SESSION["cw"]["debug"]) && $_SESSION["cw"]["debug"]) {
					echo "<strong>Cookie Error</strong>";
					echo "<pre>";
					var_dump($e);
					echo "</pre>";
				}
				// else fail silently on error 
			}
		}
	}
}
// DEBUG OUTPUT 
// session.cw.debug is controlled in init functions for page request 
if (isset($_SESSION["cw"]["debug"]) && $_SESSION["cw"]["debug"]) {
	// BASE LINK FOR DEBUG ON/OFF 
	// debug link url 
	$resetVarsToKeep = CWremoveUrlVars("debug,userConfirm,userAlert,resetapplication");
	$_ENV["request.cw"]["baseString"] = CWserializeUrl($resetVarsToKeep, $_ENV["request.cw"]["thisPage"]);
	$_ENV["request.cw"]["baseDebugLink"] = $_ENV["request.cw"]["baseString"] . '&debug=' . $_ENV["application.cw"]["storePassword"];
	if (isset($_ENV["application.cw"]["debugDisplayExpanded"]) && $_ENV["application.cw"]["debugDisplayExpanded"] == 1) {
		$_ENV["request.cwpage"]["showDump"] = true;
	} else {
		$_ENV["request.cwpage"]["showDump"] = false;
	}
	// set up list of variables to show 
	$_SESSION["cw"]["debugList"] = '';
	if (isset($_ENV["application.cw"]["debugLocal"]) && $_ENV["application.cw"]["debugLocal"]) {
		if ($_SESSION["cw"]["debugList"]) $_SESSION["cw"]["debugList"] .= ",";
		$_SESSION["cw"]["debugList"] .= "Variables";
	}
	if (isset($_ENV["application.cw"]["debugSession"]) && $_ENV["application.cw"]["debugSession"]) {
		if ($_SESSION["cw"]["debugList"]) $_SESSION["cw"]["debugList"] .= ",";
		$_SESSION["cw"]["debugList"] .= "Session";
	}
	if (isset($_ENV["application.cw"]["debugApplication"]) && $_ENV["application.cw"]["debugApplication"]) {
		if ($_SESSION["cw"]["debugList"]) $_SESSION["cw"]["debugList"] .= ",";
		$_SESSION["cw"]["debugList"] .= "Application";
	}
	if (isset($_ENV["application.cw"]["debugForm"]) && $_ENV["application.cw"]["debugForm"]) {
		if ($_SESSION["cw"]["debugList"]) $_SESSION["cw"]["debugList"] .= ",";
		$_SESSION["cw"]["debugList"] .= "Form";
	}
	if (isset($_ENV["application.cw"]["debugUrl"]) && $_ENV["application.cw"]["debugUrl"]) {
		if ($_SESSION["cw"]["debugList"]) $_SESSION["cw"]["debugList"] .= ",";
		$_SESSION["cw"]["debugList"] .= "URL";
	}
	if (isset($_ENV["application.cw"]["debugRequest"]) && $_ENV["application.cw"]["debugRequest"]) {
		if ($_SESSION["cw"]["debugList"]) $_SESSION["cw"]["debugList"] .= ",";
		$_SESSION["cw"]["debugList"] .= "Request";
	}
	if (isset($_ENV["application.cw"]["debugCookies"]) && $_ENV["application.cw"]["debugCookies"]) {
		if ($_SESSION["cw"]["debugList"]) $_SESSION["cw"]["debugList"] .= ",";
		$_SESSION["cw"]["debugList"] .= "Cookie";
	}
	if (isset($_ENV["application.cw"]["debugCGI"]) && $_ENV["application.cw"]["debugCGI"]) {
		if ($_SESSION["cw"]["debugList"]) $_SESSION["cw"]["debugList"] .= ",";
		$_SESSION["cw"]["debugList"] .= "CGI";
	}
	if (isset($_ENV["application.cw"]["debugServer"]) && $_ENV["application.cw"]["debugServer"]) {
		if ($_SESSION["cw"]["debugList"]) $_SESSION["cw"]["debugList"] .= ",";
		$_SESSION["cw"]["debugList"] .= "Server";
	}
	// debugging anchor links - jump to each section directly if shown expanded
	$_ENV["request.cwpage"]["debugAnchors"] = '<p class="debugAnchors">';
	// anchor links to each section 
	$debugList = explode(",", $_SESSION["cw"]["debugList"]);
	foreach ($debugList as $key => $dd) {
		$dd = trim($dd);
		$_ENV["request.cwpage"]["debugAnchors"] .= " <a href=\"#debug-".$dd."\">".$dd."</a> ";
	}
	$_ENV["request.cwpage"]["debugAnchors"] .= "</p>";
	// SHOW DEBUG OUTPUT ?>
	<div id="CWdebugWrapper" class="CWcontent">
	<?php // turn off debugging / show top of page ?>
	<a name="debug-top" class="debugAnchorLink"></a>
	<?php // loop list, show debug output ?>
	<div class="inner">

	<h1>DEBUGGING OUTPUT</h1>
<?php
	if ($_ENV["request.cwpage"]["showDump"]) {
		echo $_ENV["request.cwpage"]["debugAnchors"];
	}
?>
		<strong><a class="controlButton" href="<?php echo $_ENV["request.cw"]["baseDebugLink"]; ?>">Turn Off Debugging</a></strong>
<?php
	foreach ($debugList as $key => $dd) {
		if(strlen(trim($dd))) {
			$dd = trim($dd);
			echo '<a name="debug-'.$dd.'" class="debugAnchorLink"></a>';	
			echo '<h1>'.$dd;
			echo '<span class="smallPrint">( <a href="#debug-top">top</a> )</span>';
			echo '</h1>';
			$dumpVar = false;
			switch ($dd) {
				case "Local":
					$dumpVar = $GLOBALS;
					break;
				case "Session":
					$dumpVar = $_SESSION;
					break;
				case "Application":
					foreach ($_ENV as $key => $dumpVar) {
						if (strpos($key, "application") === 0) {
							echo "<pre>";
							var_dump($dumpVar);
							echo "</pre>";
						}
					}
					$dumpVar = false;
					break;
				case "Form":
					$dumpVar = $_POST;
					break;
				case "URL":
					$dumpVar = $_GET;
					break;
				case "Request":
					foreach ($_ENV as $key => $dumpVar) {
						if (strpos($key, "request") === 0) {
							echo "<pre>";
							var_dump($dumpVar);
							echo "</pre>";
						}
					}
					$dumpVar = $_REQUEST;
					break;
				case "Cookie":
					$dumpVar = $_COOKIE;
					break;
				case "CGI":
					$dumpVar = false;
					break;
				case "Server":
					$dumpVar = $_SERVER;
					break;
				case "Client":
					$dumpVar = false;
					break;
			}
			if ($dumpVar) {
				echo "<pre>";
				var_dump($dumpVar);
				echo "</pre>";
			}
		}
	}
	// /END Inner Div ?>
	</div>
	<div class="CWclear"></div>
	<?php // /END Debug Wrapper ?>
	</div>
<?php
}
?>