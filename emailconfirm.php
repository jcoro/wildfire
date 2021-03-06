<?php
/*
==========================================================

==========================================================

File: emailconfirm.php

==========================================================
*/
?>
<?php
// GLOBAL INCLUDES
require_once("Application.php");
?>
<?php
if (isset($_POST['f_email'])){
require_once("cw4/cwapp/func/cw-func-mail.php");

$email= @$_POST['f_email'];

$message = <<<content
The following email was submitted to the Wildfire Health mailing list:

$email

content;

$result = CWsendMail(nl2br($message), $_ENV["application.cw"]["companyName"]." contact form ",$_ENV["application.cw"]["companyEmail"]);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
  <link rel="icon" href="http://wildfirehealth.com/favicon.ico" type="image/x-icon" />
  <link href='http://fonts.googleapis.com/css?family=Sorts+Mill+Goudy' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
  <title>Wildfire Health - Complete Nutritional Solutions</title>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="Viewport" content="width=device-width" />

  <meta name="Description" content="<?php echo ((isset($_ENV["request.cwpage"]["description"])) ? $_ENV["request.cwpage"]["description"] : "" ); ?>">
  <?php // CARTWEAVER CSS ?>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 10]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

  <link href="cw4/css/cw-core.css" rel="stylesheet" type="text/css">
  <link href='/img/favicon.png' rel='shortcut icon' />
  <link href='Scripts/Style2.css' rel='stylesheet' />
  <link href='Scripts/Boilerplate.css' rel='stylesheet' />


  <!-- end CSS -->

  <!-- All plugins -->

  </head>

  <body class="index">

  <!-- Header -->
  <header>

    <div id="topcartnav">



    </div><!--Close topcartnav-->

    <div id="logo-container">
	      <div id="mycart">
      <?php
			// cart links, log in links, alerts
			include("cw4/cwapp/inc/cw-inc-pagestart.php");
			?>
      </div><!--Close mycart-->
    <a href="index.php" id='logo'></a>

    </div><!--Close logo-container-->

        <div id="menu-bg">
            <div id="nav-holder">
                <nav id='topnav'>
					<div id="nav-center">
                    <span class="navigation">
                    <a class='item_new' href='aboutus.php'>About Us</a>
                    </span>

					<span class="navigation">
					    <div class='menu-hover-wrapper-background'>
					        <div class='menu-hover-wrapper-bar'></div>
					        <ul class='dropdown'>
						      <!--<li><a href=''>Consulting</a></li>-->
						      <li><a href="productlist.php">Supplements</a></li>
						      <!--<li><a href=''>Gift Cards</a></li>-->
					        </ul>
					    </div><!--Close menu-hover-wrapper-background-->
					    <a class='item_shop' href='productlist.php'>Shop</a>
					</span>

					<span class="navigation">
					<!--//Resources Dropdown List
					<div class='menu-hover-wrapper-background'>
					    <div class='menu-hover-wrapper-bar'></div>
						   <ul class='dropdown'>
						     <li><a class='item_lookbook' href=''>CRON-O-METER</a></li>
						     <li><a class='item_a_moment_with' href=''>Food Log</a></li>
						     <li><a class='item_on_location' href=''>Grocery List</a></li>
						     <li><a class="item_magazine" href="">E-Books</a></li>
						   </ul>
					    </div>
					-->
					    <a class='item_features' href='resources.php'>Resources</a>
					</span>

					<span class="navigation">
					<!--//Learn More Dropdown List
					<div class='menu-hover-wrapper-background'>
					    <div class='menu-hover-wrapper-bar'></div>
					      <ul class='dropdown'>
					        <li><a href="/">FAQ</a></li>
						    <li><a href="/">Articles</a></li>
						    <li><a href="/">Blog</a></li>
						    <li><a href="/">Advocacy</a></li>
					      </ul>
					    </div>
					-->
					    <a class='item_locations' href='learnmore.php'>Learn More</a>
					</span>

					<span class="navigation">
                        <a class='item_blog' href="blog/index.php">Blog</a>
					</span>

					<span><class='helpers'>
						<a class="searchsmallicon" href="search.php"></a>
					</span>

					<span><class='helpers'>
						<a class="cartsmallicon" href="cart.php"></a>
					</span>
					</div><!--/nav-center-->
                 </nav>
            </div><!--Close nav-holder-->
        </div><!--Close "menu-bg"-->
  </header>
<div id='container'>
   <div class='wrapper'>


         <div id='main' role='main'>
        <div id='top' style="padding-top:0px;">


        </div>
        <div class="content index" id='content'>

          <!-- Page Content -->
          <div class="display">

<!-- Begin Main Content ===================  -->
<div class="main-content">
   <h1>Wildfire Health Mailing List</h1>
<?php
include("config.php");
$email= @$_POST['f_email'];

if (filter_var($email, FILTER_VALIDATE_EMAIL))
  {
$request = "INSERT INTO `$tablename` (`f_email`) VALUES ('$email')";
$con=mysqli_connect("$host","$user","$password","$db");
if(!mysqli_query($con, $request))
	echo(mysqli_error($con));
else
	echo "Thank you for adding: <strong>$email</strong> to the Wildfire Health email list";
  }
else
	echo "The email address you entered: <strong>$email</strong> is invalid. Please try again below.";
  

?>

<!-- end .main-content --></div><!-- end .main-content -->

          </div><!--display-->

        <div id='bottom'>
        </div><!--/bottom-->
      </div><!--/content index-->
    </div><!--/main-->
   </div> <!--/wrapper-->
<?php
include("wfincludes/wffooter2.php");
?>

</div><!--/container-->
</body>
</html>
					