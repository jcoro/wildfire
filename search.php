<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: index.php
File Date: 2012-02-01
Description: shows samples of product search / store navigation
==========================================================
*/
?><?php
// GLOBAL INCLUDES 
require_once("Application.php");
?><?php
// default cat/subcat for demo purpose 
if((!isset($_GET['category'])) || (!is_numeric($_GET['category']))) {
	$_GET['category'] = 55;
}
if((!isset($_GET['secondary'])) || (!is_numeric($_GET['secondary']))) {
	$_GET['secondary'] = 73;
}
// clean up form and url variables 
require_once("cw4/cwapp/inc/cw-inc-sanitize.php");
// CARTWEAVER REQUIRED FUNCTIONS 
require_once("cw4/cwapp/inc/cw-inc-functions.php");
$_ENV["request.cwpage"]["categoryID"] = $_GET['category'];
$_ENV["request.cwpage"]["secondaryID"] = $_GET['secondary'];
?>
<!DOCTYPE html>
<html>
	<head>
		<title> Search | <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="Description" content="">
		<?php // CARTWEAVER CSS ?>
		  <link href="cw4/css/cw-core.css" rel="stylesheet" type="text/css">
  <link href='/img/favicon.png' rel='shortcut icon' />
  <link href='Scripts/Style2.css' rel='stylesheet' />
  <link href='Scripts/Boilerplate.css' rel='stylesheet' />
  <link href="Scripts/menuformat2.css" rel="stylesheet" />
		<?php // sample menu css ?>
		<style type="text/css">
		/* ------- search forms -------*/
		div.searchSample{
			border-top:none;
			margin:-30px 12px 0 0;
			padding:9px 0;
			clear:both;
		}
		div.searchSample form{
			margin:20px 0;
		}
		div.searchSample form input,
		div.searchSample form select{
		margin-right:8px;
		}
		
		div.searchSample #search3-keywords{
		
		font-size:13px;	
		width:175px;
		}
			
		
		
		div.searchSample form#search1 select,
		div.searchSample form#search2 select{
			width:175px;
		}

		/* ------- horizontal top nav menu -------*/
		#navH{
		min-height:285px;
		}
		.searchSample ul#nav1 li a.currentLink {
		font-weight:900;
		background-color: #EEF3FA !important;
		color:#DF772E !important;
		}
		.searchSample ul#nav1,
		.searchSample ul#nav1 ul {
		list-style: none;
		font-size:11px;
		}
		.searchSample ul#nav1,
		.searchSample ul#nav1 * {
		padding: 0;
		margin: 0;
		}
		.searchSample ul#nav1 li a span{
		font-size:9px;
		}
		.searchSample ul#nav1 li a:link,
		.searchSample ul#nav1 li a:visited,
		.searchSample ul#nav1 li a:active{
		background-color: #61B9E7;
		color:#FFF;
		}
		.searchSample ul#nav1 li a:hover {
		background-color: #FFF;
		color:#DF772E;
		}
		/* top level links */
		.searchSample ul#nav1 > li {
		width: 150px;
		float: left;
		margin-left: -1px;
		border: 1px black solid;
		background-color: #EEF3FA;
		text-align: center;
		}
		.searchSample ul#nav1 > li a {
		display: block;
		padding: 3px 4px;
		text-decoration:none;
		}
		/* second level dropdowns  */
		.searchSample ul#nav1 > li ul {
		display: none;
		border-top: 1px black solid;
		text-align: left;
		font-size:10px;
		}
		.searchSample ul#nav1 > li:hover ul {
		display: block;
		}
		.searchSample ul#nav1 > li ul li a {
		padding: 3px 1px 3px 15px;
		height: 17px;
		text-decoration:none;
		}

		/* ------- vertical side nav menu -------*/
		.searchSample ul#nav2{
		min-height:300px;
		}
		.searchSample ul#nav2 li,
		.searchSample ul#nav2 ul,
		.searchSample ul#nav2 ul li {
			font-size:13px;
			list-style:none;
		}
		.searchSample ul#nav2 li{
			list-style:none;
		}
		.searchSample ul#nav2 li a{
			width:160px;
			display:block;
			text-decoration:none;
			padding-left:3px;
			/* background color helps with IE anti-aliasing */
			background-color:#FFF;
		}
		.searchSample ul#nav2 li a:hover{

		}
		.searchSample ul#nav2 li ul li a{
			padding-left:13px;
		}
		.searchSample ul#nav2 li a.currentLink{
			font-weight:900;
		}

		/* ------- demo links for this page ------- */
		div#searchNav a{
		font-size:12px;
		line-height:1.4em;
		}
		</style>
		<?php // list menu javacript ?>
		<script type="text/javascript" src="cw4/js/jquery-1.7.1.min.js"></script>
		<script type="text/javascript">
		jQuery(document).ready(function(){
		// show/hide function for horizontal menu
			jQuery('#nav1 > li').hover(
				function() { jQuery('ul', this).css('display','none').slideDown(5).css('display', 'block'); },
				function() { jQuery('ul', this).slideUp(5).css('display','none'); }
			);
		 // show/hide function for nested vertical menu
		 jQuery('.searchSample ul#nav2 li ul').hide().parent('li').children('a').prepend('&raquo;&nbsp;').click(function(){
		 	jQuery(this).parent('li').children('ul').show(500);
		 	jQuery(this).parent('li').siblings('li').children('ul').hide(500);
		 	return false;
		 });
			// trigger click to open menu to current page
			jQuery('.searchSample ul#nav2 > li > ul > li > a').parents('.searchSample ul.CWnav > li ').children('a.currentLink').trigger('click').removeClass('currentLink');
			// create nav anchors for this page to match h3 section headings
			jQuery('.searchSample h3').each(function(){
				var linkBox = jQuery('#searchNav');
				var linkText = jQuery(this).text();
				var linkCount = jQuery(this).parents('.searchSample').prevAll('.searchSample').length;
				jQuery(linkBox).append('<a href="#link' + linkCount + '" class="CWlink">' + linkText + '</a><br><br>');
				jQuery(this).parents('.searchSample').before('<a name="link' + linkCount + '"></a>');
			});
		});
</script>
	</head>
	<!--<body class="cw">-->
    
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
						<!--
						<?php
						if($_ENV["request.cw"]["thisPage"] != $urlshowcart[count($urlshowcart)- 1]) { echo
								'<a class="cartsmallicon" href="'.$_ENV["request.cwpage"]["urlShowCart"].'?returnUrl='.'"></a>';
							}
						?>
						-->
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


<!--
<div class="CWcontent">
<p><strong>This is a sample page, demonstrating the various search options available.</strong></p>
<p>Cartweaver uses a single custom tag for all search forms and navigation links, with options
for the various types of display. <br>Some examples are shown below. See cw-mod-searchnav.php for all available options.</p>
<p><br><strong>NOTE: all search or navigation types will automatically select or highlight the current category and secondary category</strong>,
<br>based on the values in the current page's URL. This can be overridden on any given page for specific preselection. <br>For demonstration purposes, a category and subcategory are provided in the top of this page's code.</p>
<p>&nbsp;</p>
<div id="searchNav"></div>
<p>&nbsp;</p>

<?php // form - all options, related categories ?>
<div class="searchSample">
	<h3>Search Form with All Options and Related Categories</h3>
	<p>Keywords, Categories and Secondary Categories are all enabled.
	<br>Relation of categories and secondaries is enabled. (Select a main category to narrow down available secondary categories).
	<br>Empty categories (no products available) are not included, and the number of products available in each category is shown.
	<br>Default options (All Categories, All Subcategories) are provided.
	</p>
<?php

$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "form",
	"show_empty" => false,
	"form_keywords" => true,
	"form_keywords_text" => "Search Our Store",
	"form_category" => true,
	"form_category_label" => "All Categories",
	"form_secondary" => true,
	"form_secondary_label" => "All Subcategories",
	"show_product_count" => true,
	"relate_cats" => true,
	"form_id" => "search1");
include("cw4/cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
?>
</div>
<?php // form - empty cats ?>
<div class="searchSample">
	<h3>Search Form with No Keywords, No Defaults, Empty Categories Included</h3>
	<p>Categories and Secondary Categories are enabled, Keywords field is disabled.
	<br>Relation of categories and secondaries is disabled, and default options (e.g. All Categories) are not provided.
	<br>Empty categories with no products available are included, and the number of products available in each category is not shown.
	</p>
	
<?php
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "form",
	"show_empty" => true,
	"form_keywords" => false,
	"form_category" => true,
	"form_category_label" => "",
	"form_secondary" => true,
	"form_secondary_label" => "",
	"show_product_count" => false,
	"relate_cats" => true,
	"form_id" => "search2");
include("cw4/cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
?>
</div>-->
<?php // form - keywords only ?>
<div class="searchSample">
	<h3>Search Wildfire Health</h3>
<?php
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "form",
	"show_empty" => true,
	"form_keywords" => true,
	"form_category" => false,
	"form_secondary" => false,
	"form_id" => "search3");
include("cw4/cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
?>
</div>
<!--
<?php // links - all defaults ?>
<div class="searchSample">
	<h3>Category Links with All Defaults</h3>
	<p>Search links are created automatically for all top-level and secondary categories.
	<br>Secondary categories are related, and categories with no products are not shown.
	</p>
	<p>&nbsp;</p>
<?php
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "links");
include("cw4/cwapp/mod/cw-mod-searchnav.php"); 
unset($module_settings);
chdir($myDir);
?>	
</div>
<?php // links - top level only ?>
<div class="searchSample">
	<h3>Category Links with Main Categories and Custom Links</h3>
	<p>Search links are created automatically for all top-level categories.
	<br>Secondary links are disabled, and categories with no products are not shown.
	<br>The default link ('All Products') is provided, custom links (Home, Contact Us) are inserted, and a custom delimiter (&bull;) is used.
	</p>
	<p>&nbsp;</p>
<?php
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "links",
	"show_empty" => false,
	"show_product_count" => false,
	"show_secondary" => false,
	"separator" => " &bull; ",
	"prepend_links" => "1|index.php|Home^1|contact.php|Contact Us");
include("cw4/cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
?>	
</div>
<?php // breadcrumb navigation ?>
<div class="searchSample">
<h3>Breadcrumb Serial Links</h3>
<p>Simple breadcrumb navigation with minimal options set.

<?php
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "breadcrumb",
	"separator" => " &raquo; ");
include("cw4/cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
?></p>	<p>&nbsp;</p>
</div>

<?php // list - horizontal navigation menu ?>
<div class="searchSample" id="navH">
	<h3>Horizontal Dropdown Navigation Menu</h3>
	<p>Nested links are created automatically for all top-level and secondary categories.
	<br>This method generates pure HTML, which can be used in a number of ways.
	<br>CSS controls the layout, with custom jQuery javascript for the show/hide action.
	<br>The menu ID is user defined for easy selection.
	</p>
	<p>&nbsp;</p>
<?php
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "list",
	"show_empty" => false,
	"all_categories_label" => "",
	"all_secondaries_label" => "",
	"show_product_count" => true,
	"menu_id" => "nav1");
include("cw4/cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
?>
</div>
<?php // list - vertical navigation menu ?>
<div class="searchSample">
	<h3>Vertical Nested Show/Hide Navigation Menu</h3>
	<p>Nested links are created automatically for all top-level and secondary categories.
	<br>This method generates pure HTML, which can be used in a number of ways.
	<br>CSS controls the layout, with custom jQuery javascript for the show/hide action.
	<br>The menu ID is user defined for easy selection.
	</p>
	<p><br><strong>Note: this sample, and the horizontal menu above both use the same specifications.</strong>
	<br>The only difference is the application of CSS and javascript (jQuery).</p>
	<p>&nbsp;</p>
    
<?php
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "list",
	"show_empty" => false,
	"show_product_count" => true,
	"menu_id" => "nav2");
include("cw4/cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
?>

-->




          </div><!--display-->
          </div>

        <div id='bottom'>

        </div>
      </div>
    </div>
<?php
include("wfincludes/wffooter.php");
?>

    </div>
  </div>

</body>

</html>