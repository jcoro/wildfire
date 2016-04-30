<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-page-end.php
File Date: 2012-02-01
Description: shows debugging output in admin pages
==========================================================
*/
if(isset($_SESSION["cw"]["debug"]) && $_SESSION["cw"]["debug"] == true) {
	if(isset($_ENV["application.cw"]["debugDisplayExpanded"]) && $_ENV["application.cw"]["debugDisplayExpanded"])
		$_ENV["request.cwpage"]["showDump"] = true;
	else
		$_ENV["request.cwpage"]["showDump"] = false;

	// set up list of variables to show 
	
	$_SESSION["cw"]["debugList"] = array();
	if(isset($_ENV["application.cw"]["debugLocal"]) && $_ENV["application.cw"]["debugLocal"] == true) {
		$_SESSION["cw"]["debugList"][] = "Variables";
		echo $_SESSION["cw"]["debugList"];
	} 
	if(isset($_ENV["application.cw"]["debugSession"]) && $_ENV["application.cw"]["debugSession"] == true) {
		$_SESSION["cw"]["debugList"][] = "Session";
	}
	if(isset($_ENV["application.cw"]["debugApplication"]) && $_ENV["application.cw"]["debugApplication"] == true) {
		$_SESSION["cw"]["debugList"][] = "Application";
	}
	if(isset($_ENV["application.cw"]["debugForm"]) && $_ENV["application.cw"]["debugForm"] == true) {
		$_SESSION["cw"]["debugList"][] = "Form";
	}
	if(isset($_ENV["application.cw"]["debugURL"]) && $_ENV["application.cw"]["debugURL"] == true) {
		$_SESSION["cw"]["debugList"][] = "URL";
	}
	if(isset($_ENV["application.cw"]["debugRequest"]) && $_ENV["application.cw"]["debugRequest"] == true) {
		$_SESSION["cw"]["debugList"][] = "Request";
	}
	if(isset($_ENV["application.cw"]["debugCookies"]) && $_ENV["application.cw"]["debugCookies"] == true) {
		$_SESSION["cw"]["debugList"][] = "Cookie";
	}
	if(isset($_ENV["application.cw"]["debugCGI"]) && $_ENV["application.cw"]["debugCGI"] == true) {
		$_SESSION["cw"]["debugList"][] = "CGI";
	}
	if(isset($_ENV["application.cw"]["debugServer"]) && $_ENV["application.cw"]["debugServer"] == true) {
		$_SESSION["cw"]["debugList"][] = "Server";
	}
	if(isset($_ENV["application.cw"]["debugClient"]) && $_ENV["application.cw"]["debugClient"] == true) {
		$_SESSION["cw"]["debugList"][] = "Client";
	}
	// debugging anchor links - jump to each section directly if shown expanded
	$_ENV["request.cwpage"]["debuganchors"] = "<p class = 'debugAnchors'>";
	// anchor links to each section 
	foreach ($_SESSION["cw"]["debugList"] as $key => $dd) {
		$dd=trim($dd);
		$_ENV["request.cwpage"]["debuganchors"] .= '<a href="#debug-'.$dd.'">'. $dd .'</a> ';
	}
	$_ENV["request.cwpage"]["debuganchors"] .= "</p>";
?>


<div class="clear"></div>
<?php // SHOW DEBUG OUTPUT  ?>
<div id="CWdebugWrapper">
<?php // help link  ?>
<div id="CWdebugHelp">
	<a href="http://help.cartweaver.com/index.cfm?pagename=<?php echo $_ENV["request.cwpage"]["helpFileName"]; ?>" class="zoomHelp" rel="external" title="Help for this page">
	<img width="16" height="16" align="absmiddle" alt="Help for this page" src="img/cw-help.png">
	</a>
</div>

<?php
// turn off debugging / show top of page 
	if(isset($_ENV["request.cw"]["baseDebugLink"])) {
?>
	<strong><a class="controlButton" href="config-settings.php?group_ID=10">Debug Settings</a></strong>
	<strong><a class="controlButton" href="#">Scroll Up</a></strong>
	<strong><a class="controlButton" href="<?php echo $_ENV["request.cw"]["baseDebugLink"]; ?>">Turn Off Debugging</a></strong>
<?php		 	
	}
?>

<a name="debug-top" class="debugAnchorLink"></a>
<h1>DEBUGGING OUTPUT</h1>
<div class="clear"></div>
<div class="inner">
	<?php
// loop list, show debug cfdumps 
    	if($_ENV["request.cwpage"]["showDump"]) {
			echo $_ENV["request.cwpage"]["debuganchors"];
		}
		foreach ($_SESSION["cw"]["debugList"] as $key => $dd) {
			if(strlen(trim($dd))) {
				$dd = trim($dd);
				echo '<a name="debug-'.$dd.'" class="debugAnchorLink"></a>';	
				echo '<h1>'.$dd;
				echo '<span class="smallPrint">( <a href="#debug-top">top</a> )</span>';
				echo '</h1>';
				$dumpvar = false;
				switch ($dd) {
					case "Local":
						$dumpvar = $GLOBALS;
						break;
					case "Session":
						$dumpvar = $_SESSION;
						break;
					case "Application":
						foreach ($_ENV as $key => $dumpvar) {
							if (strpos($key, "application") === 0) {
								echo "<pre>";
								var_dump($dumpvar);
								echo "</pre>";
							}
						}
						$dumpvar = false;
						break;
					case "Form":
						$dumpvar = $_POST;
						break;
					case "URL":
						$dumpvar = $_GET;
						break;
					case "Request":
						foreach ($_ENV as $key => $dumpvar) {
							if (strpos($key, "request") === 0) {
								echo "<pre>";
								var_dump($dumpvar);
								echo "</pre>";
							}
						}
						$dumpvar = $_REQUEST;
						break;
					case "Cookie":
						$dumpvar = $_COOKIE;
						break;
					case "CGI":
						$dumpvar = false;
						break;
					case "Server":
						$dumpvar = $_SERVER;
						break;
					case "Client":
						$dumpvar = false;
						break;
				}
				if ($dumpvar) {
					echo "<pre>";
					var_dump($dumpvar);
					echo "</pre>";
				}
			}
		}
		echo $_ENV["request.cwpage"]["debuganchors"];
	// /END Inner Div 
	?>
</div>
<?php // /END Debug Wrapper  ?>
</div>
<?php
	if(isset($_GET["throw"]) && $_GET["throw"] == $_ENV["application.cw"]["storePassword"]) {
		echo '<div class="clear"></div>';
		$throwError = $xxx["yyy"] ;	
	}
}
?>
