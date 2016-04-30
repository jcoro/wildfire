<?php
// GLOBAL INCLUDES 
require_once("Application.php");
?>
<!DOCTYPE HTML>
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
    <a href="index.php" id='logo'></a>
      <div id="mycart">
      <?php
			// cart links, log in links, alerts 
			include("cw4/cwapp/inc/cw-inc-pagestart.php");
			?>
      </div><!--Close mycart-->
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
						
						<?php
						if($_ENV["request.cw"]["thisPage"] != $urlshowcart[count($urlshowcart)- 1]) { echo
								'<a class="cartsmallicon" href="'.$_ENV["request.cwpage"]["urlShowCart"].'?returnUrl='.'"></a>';
							}
						?>
						
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


        </div><!--top--->
        <div class="content index" id='content'>

          <!-- Page Content -->
          <div class="display">

           <div class="left_content">

		   			   <div class="contentblock">
			   
			   <div class="header-image">
			   <img src="Images/headerimage2.png">
			   </div><!--/header-image-->
               
			     <h1>Wildfire Health Is&#8230;</h1>
<div class="sectionblock">
<h2>The Best Health Resources – All in One Place</h2>

<p>Some of the most valuable (and low-cost) health resources rarely seem to get the attention they deserve.  That’s why Wildfire Health brings the best health tools together for you in one place.  Diet tracking software, the highest-quality nutritional supplements, tools for emotional and psychological support, in-depth nutritional and exercise information – together, these tools create a powerful arsenal in your battle to lose weight, build muscle, and improve performance.</p>

<p>Each tool or product we promote has been carefully chosen to offer real, meaningful benefits for health, weight loss, and performance enhancement.  Wildfire Health weeds out the frauds so you can focus your time and energy on things that work.

<p class="get-started"><a href="">Get Started</a></p>
</div><!--/sectionblock-->

<div class="sectionblock">
<h2>Weight Loss That Works</h2>

<p>Research shows that most weight loss efforts are destined to fail in the long run.  There’s even strong evidence to show that many dieters end up heavier than if they didn’t diet at all.  Americans spend over $60 billion on diet products each year, and yet the epidemics of overweight and obesity continue to grow.  Clearly, conventional weight loss wisdom isn’t working.</p>  

<p>Wildfire Health can help you put the odds back in your favor.  We’ll help you construct a “do-it-yourself” weight loss solution that really works.</p>

<p class="get-started"><a href="">Get Started</a></p>

</div><!--/sectionblock-->

<div class="sectionblock">

<h2>Sports Nutrition and Performance Enhancement</h2>

<p>Given the value of potential college scholarships or professional contracts, the field of performance-enhancement coaching has become an increasingly viable business in recent years. Athletes under the care of supposed experts, however, often suffer from the same nutritional shortcomings as the population at large.  Though obesity and related ills don’t usually affect young athletes, their performance and potential is often hindered by poor dietary advice.  This problem is exacerbated by the low-quality nutritional supplements often promoted in the name of “sports nutrition.”  Wildfire Health can help athletes, parents, and coaches construct an optimal sports nutrition strategy.
</p>

<p class="get-started"><a href="">Get Started</a></p>

</div><!--/sectionblock-->

<div class="sectionblock">

<h2>Simple, Low-Cost Solutions</h2>

<p>Whether you realize it or not, most of the health and fitness information you receive is market driven.  Diet foods, pharmaceuticals, exercise programs, health foods, nutritional supplements, and even purely informational resources, like diet books, health articles, and USDA dietary guidelines, are developed with the primary goal of maximizing sales.  Many of the most powerful health strategies are ignored simply because they don’t generate much profit for anyone. Our modern consumer-oriented culture has a tendency to divert our attention away from simple, low-cost, and common sense solutions to our health challenges. Wildfire Health can help you focus on what's truly important. </p>

<p class="get-started"><a href="">Get Started</a></p>
</div><!--/sectionblock-->
          
		      </div><!--contentblock-->

          </div><!--left_content-->
<?php
include("wfincludes/rightsidebar.php");
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