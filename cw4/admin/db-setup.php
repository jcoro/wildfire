<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: db-setup.php
File Date: 2012-04-29
Description: Creates Cartweaver database, removes this file after running once
Note: Execute with caution! All operations are permanent
==========================================================
*/
// time out the page if it takes too long - avoid server overload for massive product deletions 
if (!ini_get("safe_mode") && !in_array("set_time_limit", explode(",", ini_get("disable_functions")))) @set_time_limit(9000);
$dbSetupErrors = array();
if (!isset($_ENV["request.cwapp"])) $_ENV["request.cwapp"] = array();
$_ENV["NODBLINK"] = true;
require_once("../cwconfig/cw-config.php");
unset($_ENV["NODBLINK"]);
//attempt database connection
$_ENV["request.cwapp"]["db_link"] = @mysql_connect($_ENV["request.cwapp"]["db_hostname"], $_ENV["request.cwapp"]["db_user"], $_ENV["request.cwapp"]["db_password"]);
if (!$_ENV["request.cwapp"]["db_link"]) {
	//show error if could not connect
	$dbSetupErrors[] = "Could not connect: " . mysql_error() . "<br />Please check your settings in the cw4/cwconfig/cw-config.php file and create a corresponding database to continue.";
}
//attempt to select database
if(!sizeof($dbSetupErrors)) {
	$dbSetup = mysql_select_db($_ENV["request.cwapp"]["db_databasename"]);
	if(!$dbSetup) {
		//show error if could not connect
		$dbSetupErrors[] = "A connection to the ".$_ENV["request.cwapp"]["db_databasename"]." database could not be made. Please ensure that the database has been created on your host (".$_ENV["request.cwapp"]["db_hostname"].") and that the user (".$_ENV["request.cwapp"]["db_user"].") has been properly configured.";
	}
}
//check for existing tables if they haven't chosen to submit with the tables to be overwritten, must have a valid db link to continue, though
if(!sizeof($dbSetupErrors) && !isset($_POST["dbcreate"]) && $_ENV["request.cwapp"]["db_link"]) {
	$getTables = mysql_query('SHOW TABLES');
	$cwTables = array();
	if (mysql_num_rows($getTables) > 0) {
		while($row = mysql_fetch_array($getTables)) {
			if(strpos(strtolower($row[0]), "cw_") !== false && strlen($row[0]) > 9 && strtolower(substr($row[0], -9)) != "_replaced") {
				$cwTables[] = $row[0];
			}
		}
	}
	if (sizeof($cwTables)) {
		$dbSetupErrors[] = "Your ".$_ENV["request.cwapp"]["db_databasename"]." database already contains the following Cartweaver tables:<br>
							".implode('<br>', $cwTables)."<br>
							<em><br><br>IMPORTANT: Create a new database and/or DSN, and update the settings in cw4/cwconfig/cw-config.php,
							<br>or all data in these tables will be removed from your database when you run this script!</em>";
	}
}
//check the file contents if they've chosen to continue without removing the old tables
$noScript = false;
$sqlFile = "db-mysql.sql";
if (!sizeof($dbSetupErrors) || (isset($_POST["dbcreate"]) && $_ENV["request.cwapp"]["db_link"])) {
	if (file_exists($sqlFile)) {
		$fileContents = @file_get_contents($sqlFile);
		if(!strlen(trim($fileContents))) {
			$noScript = true;
			$dbSetupErrors[] = "The {$sqlFile} file does not exist or is empty, please retrieve it from your original Cartweaver download
								<br>and upload it to your server into the cw4/admin directory.";
		}
	}
	else {
		$noScript = true;
		$dbSetupErrors[] = "Could not find the {$sqlFile} file, please retrieve it from your original Cartweaver download and upload it to your server into the cw4/admin directory.";
	}
}
if (isset($_POST["dbcreate"]) && !$noScript) {
	//read and run sql script if they have posted the form and the dblink is okay
	$anyError = 0;
	if (!sizeof($dbSetupErrors) || $_ENV["request.cwapp"]["db_link"]) {
		if (file_exists($sqlFile)) {
			$fileContents = @file_get_contents($sqlFile);
			if(strlen(trim($fileContents))) {
				//run each statement
				$sqlCommands = preg_split("/;[\r\n]/", $fileContents);
				$anyError = -1;
				foreach ($sqlCommands as $key => $sql) {
					if (trim($sql)) {
						$sqlResult = mysql_query($sql);
						if (!$sqlResult) {
							$anyError = 1;
							$dbSetupErrors[] = "Error during setup: ".mysql_error()."<br>".htmlentities($sql);
							break;
						}
					}
				}
			}
		}
	}
	// if no errors and deletion is selected, remove sql file and setup script 
	if ((!sizeof($dbSetupErrors) || $anyError == -1) && (isset($_GET["deleteFiles"]) || isset($_POST["deleteFiles"]))) {
		unlink($sqlFile);
		unlink("db-setup.php");
	}
	// redirect to admin home page 
	if (!sizeof($dbSetupErrors) || $anyError == -1) {
		header("Location: index.php?dbsetup=ok");
		exit;
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Cartweaver : Database Setup</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/light blue/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<style type="text/css" media="screen">
			#CWadminContent p, form p{
				line-height:1.4em;
				margin-left:0;
			}
		</style>
	</head>
<?php
// body gets a class to match the filename 
@$page = explode('.',$_ENV["request.cw"]["thisPage"]);
$page_First = $page[0];
?>
	<body <?php echo 'class="'.$page_First.'"'; ?>>
		<div id="CWadminWrapper">
			<!-- Main Content Area -->
			<div id="CWadminPage">
				<!-- inside div to provide padding -->
				<div class="CWinner">
                	<h1>Database Setup</h1>
					<!-- Page Content Area -->
					<div id="CWadminContent">
						<!-- //// PAGE CONTENT ////  -->
						<p>&nbsp;</p>
<?php
// show errors
if (sizeof($dbSetupErrors)) {
	foreach ($dbSetupErrors as $key => $i) {
?>
						<p class="alert"><?php echo $i; ?></p>
<?php
	}
}
// form for db creation 
?>
						<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" name="setupForm" id="setupForm" method="post">
                            <p>This page is used to set up your database using a .sql text file.
								<br>Before running this script, please verify the following information is correct for your database. </p>
                            <p>&nbsp;</p>
                            <p><strong>Hostname:</strong> <?php echo $_ENV["request.cwapp"]["db_hostname"]; ?></p>
                            <p><strong>Database:</strong> <?php echo $_ENV["request.cwapp"]["db_databasename"]; ?></p>
                            <p><strong>Username:</strong> <?php echo $_ENV["request.cwapp"]["db_user"]; ?></p>
                            <p><strong>Password:</strong> <?php echo $_ENV["request.cwapp"]["db_password"]; ?></p>
                            <p>&nbsp;</p>
							<p>You must have a database set up at this location with proper access permissions for the database. <br>(If not, please access the cw4/cwconfig/cw-config.php file to update your settings before you continue.)</p>
                            <p>&nbsp;</p>
<?php
if (!$noScript) {
?>
                            <p><input name="deletefiles" type="checkbox" id="deletefiles" value="deletefiles">&nbsp;&nbsp;Delete setup files (recommended for production installation)</p>
                            <p>&nbsp;</p>
							<p><input name="dbcreate" type="submit" class="submitButton" id="dbcreate" value="Start Setup"></p>
<?php
}
?>
                        </form>
					</div>
					<!-- /end Page Content -->
				</div>
				<!-- /end CWinner -->
			</div>
<?php
// page end content / debug 
include("cwadminapp/inc/cw-inc-admin-page-end.php");
?>
			<!-- /end CWadminPage-->
			<div class="clear"></div>
		</div>
		<!-- /end CWadminWrapper -->
	</body>
</html>