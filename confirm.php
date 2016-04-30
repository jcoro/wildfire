<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: confirm.php
File Date: 2012-02-01
Description: manages order confirmation and post-transaction processing
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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title> Check Out | <?php echo $_ENV["request.cwpage"]["title"]; ?></title>

<!--- CARTWEAVER CSS --->
<link href='Scripts/Style2.css' rel='stylesheet' />
<link href="cw4/css/cw-core.css" rel="stylesheet" type="text/css">
<!--- Cartweaver jQuery file --->
<script type="text/javascript" src="cw4/js/jquery-1.7.1.min.js"></script>
<!--- Cartweaver jQuery script for accordian slide menu --->
<script type="text/javascript" src="_cw4-tmplt-ft1/js/cw_side-accordian-menu.js"></script>
<!--- Cartweaver accordian slide menu css --->
<link href="_cw4-tmplt-ft1/css/cw-slide-accordian-menu.css" rel="stylesheet" type="text/css" />	

		
</head>
<body>

  <!-- Header -->
  <header>

    <div id="topcartnav">
			
			
			
    </div><!--Close topcartnav-->

    <div id="logo-container">
    <a href="index.php" id='logo'></a>

    </div><!--Close logo-container-->

        <div id="menu-bg">
            <div id="nav-holder">   
                <nav id='topnav'>
					<div id="nav-center">
                    <span class="navigation">
                    <a class='item_new' href='index.php'>About Us</a>
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
					    <a class='item_features' href='/features'>Resources</a>
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
					    <a class='item_locations' href='/locations'>Learn More</a>
					</span>

					<span class="navigation">
                        <a class='item_blog' href="blog/index.php">Blog</a>
					</span>

					<span><class='helpers'>
						<a class="searchsmallicon" href="search.php"></a>
					</span>

					<span><class='helpers'>
					</span>
					</div><!--/nav-center-->
                 </nav>
            </div><!--Close nav-holder-->   
        </div><!--Close "menu-bg"-->
  </header>
<div id='container'>
   <div class='wrapper'>







         <div id='main' role='main'>
        <div id='top' style="padding-top:40px;">


        </div>
        <div class="content index" id='content'>

          <!-- Page Content -->
          <div class="display">








<?php
// CARTWEAVER INCLUDE: confirmation content 
include('cw4/cw-confirm.php');
?>

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