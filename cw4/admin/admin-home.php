<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: admin-home.php
File Date: 2012-02-01
Description: default Home page for store admin area
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth('any');
// define showtab to set up default tab display 
if(!isset($_GET['showtab'])) { $_GET['showtab'] = 1; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars('showtab,userconfirm,useralert,sortby,sortdir'); 
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]); 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Store Administration";  // Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Store Administration Main Page";  // Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Select an option from the navigation menu or view system highlights below"; 
// START OUTPUT ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html><head>
		
		<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"];?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
		<?php
		include("cwadminapp/inc/cw-inc-admin-scripts.php");
		// jquery for form show/hide ?>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('#showProductSearchLink').click(function() {
				jQuery('form#formProductSearch').show();
				jQuery(this).hide();
			});
			jQuery('#showOrderSearchLink').click(function() {
				jQuery('form#formOrderSearch').show();
				jQuery(this).hide();
			});
			jQuery('#showCustomerSearchLink').click(function() {
				jQuery('form#formCustomerSearch').show();
				jQuery(this).hide();
			});
		});
		// end jquery
		</script>

    </head>
<?php
// body gets a class to match the filename 
$page = explode('.',$_ENV["request.cw"]["thisPage"]);
$page_First = $page[0];
?>
	<body <?php echo 'class="'.$page_First.'"'; ?>>
    	<div id="CWadminWrapper">
			<!-- Navigation Area -->
			<div id="CWadminNav">
				<div class="CWinner">
				<?php include("cwadminapp/inc/cw-inc-admin-nav.php"); 
				?>
				</div>
				<!-- /end CWinner -->
			</div>
			<!-- /end CWadminNav -->
			<!-- Main Content Area -->
			<div id="CWadminPage">
				<!-- inside div to provide padding -->
				<div class="CWinner">
                                        <?php
					// page start content / dashboard 
					include("cwadminapp/inc/cw-inc-admin-page-start.php");
					if(strlen(trim($_ENV["request.cwpage"]["heading1"]))) {  echo  '<h1>'.trim($_ENV["request.cwpage"]["heading1"]).'</h1>'; }
					if(strlen(trim($_ENV["request.cwpage"]["heading2"]))) {  echo  '<h2>'.trim($_ENV["request.cwpage"]["heading2"]).'</h2>'; }
					// user alerts 
			
					 include("cwadminapp/inc/cw-inc-admin-alerts.php"); ?>
					<!-- Page Content Area -->
					<div id="CWadminContent">
                    
						<!-- //// PAGE CONTENT ////  -->
						<!-- TABBED SEARCH LAYOUT -->
						<div id="CWadminHomeSearch">
                        
							<div id="CWadminTabWrapper">
								<!-- TAB LINKS -->
                           
								<ul class="CWtabList">
                                
									<?php // product search 
									if($_SESSION["cw"]["accessLevel"] != 'service') {
										
											if($_ENV["application.cw"]["adminWidgetSearchProducts"]) { ?>
												
                                                <li><a href="#tab1" title="Search Products">Search Products</a></li>
										<?php }
											} 
									// order search 
									if($_ENV["application.cw"]["adminWidgetSearchProducts"]) { ?>
										<li><a href="#tab2" title="Search Orders">Search Orders</a></li>						
									<?php }
									// customer search 
									if($_ENV["application.cw"]["adminWidgetSearchCustomers"]) { ?>
										<li><a href="#tab3" title="Search Customers">Search Customers</a></li>
									<?php } ?>
								</ul>
                                                                <?php // WIDGETS 
								// SEARCH TABS ?>
                                
								<div class="CWtabBox">
                                                                        <?php // product search 
									if($_SESSION["cw"]["accessLevel"] != 'service') {
										
										if($_ENV["application.cw"]["adminWidgetSearchProducts"]) { ?>
											<div id="tab1" class="tabDiv">
                                          
											<?php
											
											
											 include("cwadminapp/inc/cw-admin-widgets/cw-inc-admin-widget-search-products.php") ?>
											</div>	
									<?php } } ?>
                                                                        <?php
									// order search 
                                                                        if(isset($_ENV["application.cw"]["adminWidgetSearchOrders"])) { ?>
											<div id="tab2" class="tabDiv">
											<?php  include("cwadminapp/inc/cw-admin-widgets/cw-inc-admin-widget-search-orders.php") 
											?>
											</div>	
									<?php  } ?>
                                                                        <?php 
									// cutomer search 
                                                                        if(isset($_ENV["application.cw"]["adminWidgetSearchOrders"])) { ?>
											<div id="tab3" class="tabDiv">
											<?php 
											
											 include("cwadminapp/inc/cw-admin-widgets/cw-inc-admin-widget-search-customers.php") ?>
											</div>	
									<?php  } ?>
								</div>
								<div class="clear"></div>
							</div>
						</div>
                                                <?php // /END SEARCH TABS ?>
						<div class="clear"></div>
						<?php  $_POST['widgetCt'] = 0;
						// recent orders 
						if($_ENV["application.cw"]["adminWidgetOrders"]) {
							include("cwadminapp/inc/cw-admin-widgets/cw-inc-admin-widget-orders.php");
							 $_POST['widgetCt'] = $_POST['widgetCt'] + 1; 
						}
						// top products
						if($_SESSION["cw"]["accessLevel"] != 'service') {
								if($_ENV["application.cw"]["adminWidgetProductsBestselling"]) {
									include("cwadminapp/inc/cw-admin-widgets/cw-inc-admin-widget-products-bestselling.php");
									 $_POST['widgetCt'] = $_POST['widgetCt'] + 1; 
								}
						} 
						if($_POST['widgetCt'] == 2) { ?>
							<div class="clear"></div>
						<?php }
						// top customers 
						if($_ENV["application.cw"]["adminWidgetCustomers"]) {
								include("cwadminapp/inc/cw-admin-widgets/cw-inc-admin-widget-customers.php");
								 $_POST['widgetCt'] = $_POST['widgetCt'] + 1; 
							  } 
							  if($_POST['widgetCt']%2 == 0) { ?>
									<div class="clear"></div>
						<?php }
						// recently added/modified products
						if($_SESSION["cw"]["accessLevel"] != 'service') {
								  	if($_ENV["application.cw"]["adminWidgetProductsRecent"]) {
										include("cwadminapp/inc/cw-admin-widgets/cw-inc-admin-widget-products-recent.php");
										 $_POST['widgetCt'] = $_POST['widgetCt'] + 1; 
									}
								}
						 ?>
							<div class="clear"></div>
					</div>
                                    <!-- /end Page Content -->
				</div>
                                <!-- /end CWinner -->
			</div>
			<?php // page end content / debug 
            include("cwadminapp/inc/cw-inc-admin-page-end.php");
            ?>
			<!-- /end CWadminPage-->
			<div class="clear"></div>
		</div>
		<!-- /end CWadminWrapper -->
	</body>
</html>
