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
  <link href='Scripts/Style2.css' rel='stylesheet' />
  <link href='Scripts/Boilerplate.css' rel='stylesheet' />

  <link href="Scripts/assets/css/style.min.css" rel="stylesheet">
  <link href="Scripts/css/lush.animations.min.css" rel="stylesheet" />        
  <link href="Scripts/css/lush.min.css" rel="stylesheet" />
  <link href="Scripts/assets/css/bootstrap-responsive.css" rel="stylesheet">
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
					    <a class='item_locations' href='index.php'>Learn More</a>
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
<ul id="slider-1" class="lush-slider shadow-a">
  <li class="lush">
				<img src="Images/wf-bg4.png"
                      data-slide-in="at 300 from fade use easeInCirc during 600" 
                      data-slide-out="at 20000 to fade use easeOutCirc during 600" 
                      style="top: 0; left: 0; " />
					  
				<img src="Images/wf-woman1.png" 
                      data-slide-in="at 100 from fade use easeOutQuart during 600" 
                      data-slide-out="at 20000 to fade use easeInBack during 600" 
                      style="top: 0%; left: 10%; "  /> 
					  
				<img src="Images/wflogotrans.png"
                    data-slide-in="at 14000 from front use easeOutBack  during 500"  
                    data-slide-out="at 4000 to front use easeInBack during 600"
                    style="top: 0px; left: 645px;" />
    
				<h1 id="a1" class="slider_text" 
                    data-slide-in="at 2000 from fade use easeOutCirc during 2500"  
                    data-slide-out="at 2000 to fade use easeInCirc during 600 force"
                    style="top: 70px; left: 527px;">Isn't It Time</h1>

                <h1 id="a1" class="slider_text" 
                    data-slide-in="at 4000 from fade use easeOutCirc during 2500"  
                    data-slide-out="at 0 to fade use easeInCirc during 600 force"
                    style="top: 120px; left: 527px;">For A <span style="color:#be1622;">REAL</span> Weight Loss Solution?</h1>

                <h1 class="slider_text_small" 
                    data-slide-in="at 7500 from front use easeOutCirc during 500"  
                    data-slide-out="at 500 to fade use easeInCirc during 500 force"
                    style="top: 235px; left: 668px;">Food Selection</h1>

					
				<h1 class="slider_text_small" 
                    data-slide-in="at 9000 from front use easeOutCirc during 500"  
                    data-slide-out="at 500 to fade use easeInCirc during 500 force"
                    style="top: 235px; left: 670px;">Meal Planning</h1>
                
				
				<h1 class="slider_text_small" 
                    data-slide-in="at 10500 from front use easeOutCirc during 500"  
                    data-slide-out="at 500 to fade use easeInCirc during 500 force"
                    style="top: 235px; left: 660px;">Supplementation</h1>
					
				<h1 class="slider_text_small" 
                    data-slide-in="at 12000 from front use easeOutCirc during 500"  
                    data-slide-out="at 500 to fade use easeInCirc during 500 force"
                    style="top: 235px; left: 650px;">Emotional Support</h1>
					
				<h1 class="slider_text" 
                    data-slide-in="at 14000 from fade use easeOutCirc during 4000"  
                    data-slide-out="at 1000 to fade use easeInCirc during 600 force"
                    style="top: 235px; left: 600px;">We Put It <span style="color:#be1622;">ALL</span> Together</h1>

				<a class="" href="aboutus.php" target="_top" 
                    data-slide-in="at 500 from fade use swing during 1000" 
                    data-slide-out="at 19500 to fade use swing during 500" 
                    style="top: 400px; left: 1010px; width: 103px; height: 37px;" >
                    <img src="Images/learnmore.png" /></a>
  </li>

	<li class="lush">
				<img src="Images/wf-bg4.png"
                      data-slide-in="at 0 from fade use easeInCirc during 600" 
                      data-slide-out="at 13000 to fade use easeOutCirc during 600" 
                      style="top: 0; left: 0; " />
					  
				<img src="Images/wf-man1.png" 
                      data-slide-in="at 100 from fade use easeOutQuart during 600" 
                      data-slide-out="at 12000 to fade use easeInBack during 600" 
                      style="top: 0%; left: 50%;"  /> 
					  
				<img src="Images/wflogotrans.png"
                    data-slide-in="at 1500 from front use easeOutBack  during 500"  
                    data-slide-out="at 10500 to front use easeInBack during 600"
                    style="top: 5%; left: 20%;" />
    
                <h1 class="slider_text"
                    data-slide-in="at 2000 from front use easeOutCirc during 1500"  
                    data-slide-out="at 1500 to r use easeInCirc during 400 force"
                    style="top: 50%; left: 17%;">Looking To Build Muscle</h2>
    
                <h1 class="slider_text" 
                    data-slide-in="at 3000 from front use easeOutCirc during 1500"  
                    data-slide-out="at 1500 to l use easeInCirc  during 400 force"
                    style="top: 60%; left: 17%;">For Sports or Fitness?</h2>
    
                <h1 class="slider_text" 
                    data-slide-in="at 7000 from front use easeOutCirc during 2500"  
                    data-slide-out="at 3000 to r use easeInCirc during 400"
                    style="top: 55%; left: 17%;">Don't Go It Alone </h2>
    
                <h1 class="slider_text" 
                    data-slide-in="at 9000 from front use easeOutCirc during 2000"  
                    data-slide-out="at 1000 to l use easeInCirc  during 600"
                    style="top: 70%; left: 17%;">Wildfire Health Can Help </h2>
					
				<a class="" href="aboutus.php" target="_top" 
                    data-slide-in="at 500 from fade use swing during 1000" 
                    data-slide-out="at 12000 to fade use swing during 500" 
                    style="top: 400px; left: 1010px; width: 103px; height: 37px;" >
                    <img src="Images/learnmore.png" /></a>
  </li>
	
	
					  
 
  
</ul>
       
	   <div id='main' role='main'>
           <div class="content index" id='content'>
          
          <!-- Page Content -->
          
				<div class='columns'>
					<section class='left_col'>
						<article>
							<h2>Featured Products</h2>
 
<ul id="slider-2" class="lush-slider white no-shadow" style="border:none;" >
<li class="lush" >
<img src="Images/featuredlargewhey.png" 
data-slide-in="at 0 from left use swing during 500" 
data-slide-out="at 500 to right use swing during 500" 
style="top: 0; left: 0; width: auto; height: auto;" />

<a class="" href="productlist.php?keywords=whey" target="_top" 
data-slide-in="at 500 from fade use swing during 1000" 
data-slide-out="at 0 to fade use swing during 500" 
style="top: 361px; left: 315px; width: 100%; height: 100%;" >
<img src="Images/learnmore.png" /></a>

<h3 class="featured_brand" 
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 25px; right:8px;">Integrated Supplements </h3>

<h3 class="featured_brand" 
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 45px; right:8px;">Whey Protein Isolate </h3>

<h1 class="featured_price"
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 80px; left: 310px;">$44.95</h1>

</li>

<li class="lush" >
<img src="Images/featuredlargemagnesium.png" 
data-slide-in="at 0 from left use swing during 500" 
data-slide-out="at 500 to right use swing during 500" 
style="top: 0; left: 0; width: auto; height: auto;" />

<a class="" href="product.php?product=127" target="_top" 
data-slide-in="at 500 from fade use swing during 1000" 
data-slide-out="at 0 to fade use swing during 500" 
style="top: 361px; left: 315px; width: 100%; height: 100%;" >
<img src="Images/learnmore.png" /></a>

<h3 class="featured_brand" 
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 25px; right:8px;">Integrated Supplements </h3>

<h3 class="featured_brand" 
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 45px; right:6px;">Bio&#45;Available Magnesium </h3>

<h1 class="featured_price"
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 80px; left: 310px;">$19.95</h1>

</li>

<li class="lush" >
<img src="Images/featuredlargeherbalenergy.png" 
data-slide-in="at 0 from left use swing during 500" 
data-slide-out="at 500 to right use swing during 500" 
style="top: 0; left: 0; width: auto; height: auto;" />

<a class="" href="product.php?product=129" target="_top" 
data-slide-in="at 500 from fade use swing during 1000" 
data-slide-out="at 0 to fade use swing during 500" 
style="top: 361px; left: 315px; width: 100%; height: 100%;" >
<img src="Images/learnmore.png" /></a>

<h3 class="featured_brand" 
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 25px; right:8px;">Integrated Supplements </h3>

<h3 class="featured_brand" 
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 45px; right:4px;">Herbal Energy</h3>

<h1 class="featured_price"
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 80px; left: 310px;">$29.95</h1>
</li>

<li class="lush" >
<img src="Images/featuredlargegreens.png" 
data-slide-in="at 0 from left use swing during 500" 
data-slide-out="at 500 to right use swing during 500" 
style="top: 0; left: 0; width: auto; height: auto;" />

<a class="" href="product.php?product=128" target="_top" 
data-slide-in="at 500 from fade use swing during 1000" 
data-slide-out="at 0 to fade use swing during 500" 
style="top: 361px; left: 315px; width: 100%; height: 100%;" >
<img src="Images/learnmore.png" /></a>

<h3 class="featured_brand" 
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 25px; right:8px;">Integrated Supplements </h3>

<h3 class="featured_brand" 
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 45px; right:4px;">Green Detox</h3>

<h1 class="featured_price"
data-slide-in="at 500 from fade use easeOutCirc during 1000"  
data-slide-out="at 0 to fade use easeInCirc  during 500"
style="top: 80px; left: 310px;">$19.95</h1>

</li>

</ul>

					</article>
					</section>
	
					<section class='right_col'>
						<div class="block-border"></div>
							<div class="wf-block">
								<div class="modulelatest">
									<h2>More About Wildfire Health</h2>
										<ul class="list-content">
											<li class="latestitem">
												<div class="latestimage"><a href="/aboutus.php"><img src="/Images/logothumb.png" title="What is Wildfire Health?" height="60" width="90"></a></div>
												<div class="latestcontent"> <h3>What is Wildfire Health?</h3>
												<section class="latesttext">Wildfire Health can help you achieve your fitness goals.<a href="aboutus.php"> &raquo; Learn More</a></section>
												</div><!--latestcontent-->
											</li>
     
											<li class="latestitem">
												<div class="latestimage"><a href="/resources.php"><img src="/Images/womanthumb.png" title="Get Started with Wildfire Health" height="60" width="90"></a></div>
												<div class="latestcontent"> <h3>Getting Started</h3>
												<section class="latesttext">Wildfire Health has plenty of free tools to get you started.<a href="resources.php"> &raquo; Learn More</a></section>
												</div><!--latestcontent-->
											</li>    
     
											<li class="latestitem">
												<div class="latestimage"><a href="/productlist.php"><img src="/Images/suppthumb.png" title="Quality Nutritional Supplements" height="60" width="90"></a></div>
												<div class="latestcontent"> <h3>Quality Nutritional Supplements</h3>
												<section class="latesttext">Wildfire Health offers the highest-quality and most-effective supplements.<a href="productlist.php"> &raquo; Learn More</a></section>
												</div><!--latestcontent-->
											</li>  
											
											<li class="latestentry">
											<h2>Latest From The Wildfire Health Blog:</h2>
											</li>
											
											<li class="latestitem">
												<div class="latestimage"><a href="/blog"><img src="/Images/dairythumb.png" title="In Defense of Dairy" height="60" width="90"></a></div>
												<div class="latestcontent"> <h3>In Defense of Dairy</h3>
												<section class="latesttext">Is dairy's bad reputation really justified?
												<br /><a href="productlist.php"> &raquo; Learn More</a></section>
												</div><!--latestcontent-->
											</li> 
									</ul>
							
										<div class="more_articles"><a href="#">&raquo; More Wildfire Health Articles</a></div>
								
								</div><!--Close modulelatest-->

							</div><!--wf-block-->
	  
					</section><!--right_col-->
				
				</div><!--Close Columns-->

			</div><!--Close content index-->
        		
       </div><!--Close main-->
    
    </div><!--Close wrapper-->	
<?php
include("wfincludes/wffooter.php");
?>

 