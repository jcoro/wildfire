<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/legal/eula.cfm
Support: http://www.cartweaver.com/support
==========================================================
Cartweaver 4 PHP Free Template v1.0
File: contact-us.php
File Date: 03-11-2012
==========================================================
*/
?>
<?php
// GLOBAL INCLUDES 
require_once("Application.php");
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
   <h1>Contact Wildfire Health</h1>
    <p> Got a question for Wildfire Health?  We'll be happy to help you out.  Just fill out the form below to contact us. </p>
    <form id="form1" name="form1" method="post" action="contact-us-thanks.php">
      <p>First Name: <br />
        <label for="first_name"></label>
        <input type="text" name="first_name" id="first_name" />
      </p>
      <p>Last Name: <br />
        <input type="text" name="last_name" id="last_name" />
      </p>
      <p>Email: <br />
        <input type="text" name="email" id="email" />
      </p>
      <p>Phone: <br />
        <input type="text" name="phone" id="phone" />
      </p>
      <p>Comments:
        <br />
        <label for="comments"></label>
        <textarea name="comments" id="comments" cols="45" rows="5"></textarea>
        <br />
      </p>
      <p>
        <input name="contactform" type="hidden" id="contactform" value="1" />
        <input type="submit" name="button" id="button" value="Send Contact" />
      </p>
    </form>
 
    <h4>Contact Us via email:</h4>
    
    <p><a href="mailto:info@WildfireHealth.com">info@WildfireHealth.com</a></p>
    <p><br />
    </p>

</div><!--/main-content-->

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