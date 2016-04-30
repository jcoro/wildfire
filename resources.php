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
               
			     <h1>Wildfire Health Resources</h1>

<a href="http://www.cronometer.com/" target="_blank"> <h2>CRON-O-Meter</h2></a>
<div class="screenbullets">
<div class="screenimg">
<a href="http://www.cronometer.com/" target="_blank"><img src="Images/cronimg.png"></a>
</div><!--/screenimg-->
<ul>
<li>Track The Foods You Eat</li>
<li>Track The Calories You Consume</li>
<li>Track the Vitamin and Mineral Content of Your Diet</li>
<li>The Basic Version of CRON-O-Meter is Completely Free</li>
</ul>
</div><!--/screenbullets-->
<div class="screendesc">
<p>No matter which type of diet you follow, keeping track of your calorie and nutrient intake is nearly essential to long-term weight loss success.  The vast majority of diets fail largely because most dieters don't really know how many calories they're consuming, or which nutrients they're diets are lacking. </p>

<p>While meticulous day-in and day-out calorie counting isn't necessary for weight loss success, all dieters should strive to gain a better understanding of the <strong><em>true</em></strong> caloric and nutrient composition of their diets.  Cron-O-Meter can help you build these valuable skills.
</div><!--/screendesc"-->

<p class="get-started"><a href="http://www.cronometer.com/" target="_blank">Get Started with CRON-O-Meter</a></p>

<a href="http://nutritiondata.self.com/" target="_blank"><h2>NutritionData.com</h2></a>
<div class="screenbullets">
<div class="screenimg">
<a href="http://nutritiondata.self.com/" target="_blank"><img src="Images/nutritiondataimg.png"></a>
</div><!--/screenimg-->
<ul>
<li>Quickly Analyze Foods</li>
<li>Find Foods by Nutrient Content</li>
<li>No Login or Membership Required</li>

</ul>
</div><!--/screenbullets-->
<div class="screendesc">
<p>
NutritionData.com offers the ability to quickly (without login or membership) view the complete nutritional profile of thousands of various foods.  Particularly valuable is their <a href="http://nutritiondata.self.com/tools/nutrient-search">Nutrient Search Tool</a> which filters foods based on levels of particular nutrients.
</p>
<p>
Looking to find low-fat foods that are high in zinc, or low-calorie foods that are high in fiber?  <a href="http://nutritiondata.self.com/tools/nutrient-search">The Nutrient Search Tool</a> can be a very valuable resource. 
</p>

</div><!--/screendesc"-->

<p class="get-started"><a href="http://nutritiondata.self.com/" target="_blank">Get Started with NutritionData.com</a></p>

<a href="http://lpi.oregonstate.edu/infocenter/" target="_blank"><h2>Linus Pauling Institute</h2></a>
<div class="screenbullets">
<div class="screenimg">
<a href="http://lpi.oregonstate.edu/infocenter/" target="_blank"><img src="Images/lpiimg.png"></a>
</div><!--/screenimg-->
<ul>
<li>Quickly Analyze Foods</li>
<li>Find Foods by Nutrient Content</li>
<li>No Login or Membership Required</li>

</ul>
</div><!--/screenbullets-->
<div class="screendesc">
<p>
NutritionData.com offers the ability to quickly (without login or membership) view the complete nutritional profile of thousands of various foods.  Particularly valuable is their <a href="http://nutritiondata.self.com/tools/nutrient-search">Nutrient Search Tool</a> which filters foods based on levels of particular nutrients.
</p>
<p>
Looking to find low-fat foods that are high in zinc, or low-calorie foods that are high in fiber?  <a href="http://nutritiondata.self.com/tools/nutrient-search">The Nutrient Search Tool</a> can be a very valuable resource. 
</p>

</div><!--/screendesc"-->

<p class="get-started"><a href="http://lpi.oregonstate.edu/infocenter/" target="_blank">Get Started With The Linus Pauling Institute</a></p>




<h2>Simple, Low-Cost Solutions</h2>

<p>Whether you realize it or not, most of the health and fitness information you receive is market driven.  Diet foods, pharmaceuticals, exercise programs, health foods, nutritional supplements, and even purely informational resources, like diet books, health articles, and USDA dietary guidelines, are developed with the primary goal of maximizing sales.  Many of the most powerful health strategies are ignored simply because they don’t generate much profit for anyone. Our modern consumer-oriented culture has a tendency to divert our attention away from simple, low-cost, and common sense solutions to our health challenges. Wildfire Health can help you focus on what's truly important. </p>

<p class="get-started"><a href="">Get Started</a></p>

          
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