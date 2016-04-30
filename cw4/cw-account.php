<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-account.php
File Date: 2012-05-12
Description:manages customer account and order history
==========================================================
*/
// if accounts are not enabled, send user to cart page 
if(!$_ENV["application.cw"]["customerAccountEnabled"]) {
	header("Location: ".$_POST['urlShowCart']);
}
// customerID needed to handle account / billing 
if(!isset($_SESSION["cwclient"]["cwCustomerID"])) { $_SESSION["cwclient"]["cwCustomerID"] = 0; }
// customer type for advanced wholesal/retail mods 
if(!isset($_SESSION["cwclient"]["cwCustomerType"])) { $_SESSION["cwclient"]["cwCustomerType"] = 0; }
// customer name for 'logged in as' link 
if(!isset($_SESSION["cwclient"]["cwCustomerName"])) { $_SESSION["cwclient"]["cwCustomerName"] = ""; }
// list of recently viewed products 
if(!isset($_SESSION["cwclient"]["cwProdViews"])) { $_SESSION["cwclient"]["cwProdViews"] = ""; }
// errors from forms being submitted 
if(!isset($_ENV["request.cwpage"]["formErrors"])) { $_ENV["request.cwpage"]["formErrors"] = ""; }
// form and link actions 
if(!isset($_ENV["request.cwpage"]["hrefUrl"])) { $_ENV["request.cwpage"]["hrefUrl"] = trim($_ENV["application.cw"]["appCWStoreRoot"]).$_ENV["request.cw"]["thisPage"]; }
// content to show (account|orders|details|products|views) 
if(!isset($_GET['view'])) { $_GET['view'] = "account"; }
// order details 
if(!isset($_GET['order'])) { $_GET['order'] = 0; }
// spacer between navigation links 
$linkDelim = "&nbsp;&nbsp;&bull;&nbsp;&nbsp;" ;
$myDir = getcwd();
chdir(dirname(__FILE__));
// clean up form and url variables 
include("cwapp/inc/cw-inc-sanitize.php");
// CARTWEAVER REQUIRED FUNCTIONS 
include("cwapp/inc/cw-inc-functions.php");
chdir($myDir);
// if not logged in, persist account view 
if(!strlen($_SESSION["cwclient"]["cwCustomerID"]) || $_SESSION["cwclient"]["cwCustomerID"] === 0 || $_SESSION["cwclient"]["cwCustomerID"] === "0" || $_SESSION["cwclient"]["cwCustomerType"] == 0 || (isset($_SESSION["cwclient"]["cwCustomerCheckout"]) && strtolower($_SESSION["cwclient"]["cwCustomerCheckout"]) == "guest")) { $_ENV["request.cwpage"]["viewmode"] = 'account' ;} else { $_ENV["request.cwpage"]["viewmode"] = $_GET['view']; }
//downloads checks
if (!isset($_ENV["request.cwpage"]["downloadsEnabled"])) $_ENV["request.cwpage"]["downloadsEnabled"] = $_ENV["application.cw"]["appDownloadsEnabled"];
// empty list, used to prevent duplicate links below 
$dlSkus = array();
$downloadsQuery = CWselectCustomerDownloads($_SESSION["cwclient"]["cwCustomerID"]);
// if customer has no downloadable items, downloads are not enabled for this page 
if ($downloadsQuery["totalRows"] < 1) {
	$_ENV["request.cwpage"]["downloadsEnabled"] = false;
}
// /////// START OUTPUT /////// 
// cart links, log in links, alerts 
$myDir = getcwd();
chdir(dirname(__FILE__));
include_once("cwapp/inc/cw-inc-pagestart.php");
chdir($myDir);
// breadcrumb navigation 
$myDir = getcwd();
chdir(dirname(__FILE__));
$module_settings = array(
	"search_type" => "breadcrumb",
	"separator" => ' &raquo; ',
	"end_label" => "My Account",
	"all_categories_label" => "",
	"all_secondaries_label" => "",
	"all_products_label" => "");
include("cwapp/mod/cw-mod-searchnav.php");
unset($module_settings);
chdir($myDir);
// customer account info ?>
<div id="CWaccount" class="CWcontent">
	<h1>Customer Account Information</h1>
	<?php // login section ?>
	<div class="CWformSection">
<?php
// if not logged in 
if(!strlen($_SESSION["cwclient"]["cwCustomerID"]) || $_SESSION["cwclient"]["cwCustomerID"] === 0 || $_SESSION["cwclient"]["cwCustomerID"] === "0" || $_SESSION["cwclient"]["cwCustomerType"] == 0 || (isset($_SESSION["cwclient"]["cwCustomerCheckout"]) && strtolower($_SESSION["cwclient"]["cwCustomerCheckout"]) == "guest")) {
?>
                    <h3 class="CWformSectionTitle">Returning Customers: Log In</h3> 
<?php
	// login form 
	$myDir = getcwd();
	chdir(dirname(__FILE__));
	$module_settings = array();
	include("cwapp/mod/cw-mod-formlogin.php");
	unset($module_settings);
	chdir($myDir);
	// if logged in 
} else {
?>
					<h3 class="CWformSectionTitle">Account Options</h3>
<?php  	
	if(strlen(trim($_SESSION["cwclient"]["cwCustomerName"]))) { 
?>
						<div class="sideSpace">
						<p>Logged in as
						<?php echo $_SESSION["cwclient"]["cwCustomerName"]; ?>&nbsp;&nbsp;
						<?php // logout link ?>
						<span class="smallPrint"><a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]."?logout=1"; ?>">Not your account?</a></span>
						</p>
						<?php // switch view links ?>
				  		<p class='CWlinks'>
						<a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>?view=account"<?php if($_ENV["request.cwpage"]["viewmode"] =='account') {?> class="currentLink"<?php } ?>>Account Details</a><?php echo $linkDelim; ?>
						<a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]."?view=orders"; ?>"<?php if($_ENV["request.cwpage"]["viewmode"] =='orders' || $_ENV["request.cwpage"]["viewmode"] == 'details') {?> class="currentLink" <?php } ?>>Order History</a><?php echo $linkDelim; ?>
					   <a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]."?view=products"; ?>"<?php if($_ENV["request.cwpage"]["viewmode"] =='products') {?> class="currentLink"<?php } ?>>Purchased Items</a>
                       <?php if ($_ENV["request.cwpage"]["downloadsEnabled"]) { echo $linkDelim; ?><a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>?view=downloads"<?php if ($_ENV["request.cwpage"]["viewmode"] == 'downloads') { ?> class="currentLink"<?php } ?>>Downloads</a><?php } ?>
				<?php  if(strlen(trim($_SESSION["cwclient"]["cwProdViews"]))) { echo $linkDelim; ?> <a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]."?view=views"; ?>"<?php if($_ENV["request.cwpage"]["viewmode"] =='views') {?> class="currentLink"<?php } ?>>Recently Viewed Items</a><?php } ?>
						</p>
						</div>
<?php
	}   
}
// end if logged in 
?>
	</div>
<?php
// /end login section 
// customer account section ?>
	<div class="CWformSection">
<?php
// content switch, based on url view 
switch($_ENV["request.cwpage"]["viewmode"]) {
	// ORDER HISTORY 
	case "orders":
	{
		// QUERY: get orders by customer id 
		$ordersQuery = CWquerySelectOrders($customer_id = $_SESSION["cwclient"]["cwCustomerID"]);
?>
                <h3 class="CWformSectionTitle">Order History</h3>
<?php
		// if orders exist 
		if($ordersQuery['totalRows'] > 0 ) {
?>
                	<table id="CWcustomerTable" class="CWformTable">
						<tbody>
<?php
			for ($n=0; $n<$ordersQuery["totalRows"]; $n++) {
				// QUERY: get products in order 
				$skusQuery = CWquerySelectOrderDetails($ordersQuery["order_ID"][$n]);
				// header w/ date ?>
							<tr>
								<th colspan="3">
									<h3>
                                    Date: <?php echo cartweaverDate($ordersQuery['order_date'][$n]);?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Order ID: <?php echo $ordersQuery['order_ID'][$n];?>
                                    </h3> 
                                 </th>
							</tr>
							<?php // order details ?>
							<tr>
								<?php // order total ?>
                                <td style="width:140px;">
										<strong>
										Order Total:
										</strong>
										<br>
										<?php echo cartweaverMoney($ordersQuery["order_total"][$n]); ?>
										<br>
										<br>
										&raquo;&nbsp;
										<a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]."?view=details&order=".$ordersQuery["order_ID"][$n]; ?>">Order Details</a> 
                                </td>
                                <?php // ship to / status ?>
								<td style="width:170px;">
<?php
					if ($ordersQuery["order_status"][$n] > 3) {
?>
										<strong>Shipped To:</strong>
                                        <br>
                                        <?php echo $ordersQuery['order_ship_name'][$n];?>
                                        <br>
                                        <?php echo $ordersQuery['order_address1'][$n];?>
                                        <br>
                                        <?php echo $ordersQuery['order_city'][$n].$ordersQuery['order_state'][$n].$ordersQuery['order_zip'][$n];?>
<?php
					} else {
?>
										<strong>Status:</strong>
                                        <br>
                                        Processing, <?php echo $ordersQuery['shipstatus_name'][$n];?>
<?php
					}
?>
									</td>  
                                    <?php // products ?>
                                    <td>
<?php
					if($skusQuery['totalRows']) {
?>
													<strong>
													Products:
													</strong>
													<br>
<?php
						$lastSkuUniqueID = -1;
						for($ii=0; $ii < $skusQuery['totalRows']; $ii++) {
							if ($skusQuery["ordersku_unique_id"][$ii] != $lastSkuUniqueID) {
								$lastSkuUniqueID = $skusQuery["ordersku_unique_id"][$ii];
								$dlOK = false;
								$dlMessage = '';
								if ($_ENV["request.cwpage"]["downloadsEnabled"]) {
									// if sku has a download attached 
									if (strlen(trim($skusQuery["sku_download_id"][$ii]))) {
										// check for download from this order: Cartweaver Digital Downloads 
										$downloadCheck = CWcheckCustomerDownload(
											$skusQuery["sku_id"][$ii],
											$_SESSION["cwclient"]["cwCustomerID"],
											$ordersQuery["order_ID"][$n]
											);
										// if 0 is returned, no limit, no message 
										if ($downloadCheck == '0') {
											$dlOK = true;
										// if a "0-" error message is returned, no dl available, show text string ---> 
										} else if (substr($downloadCheck, 0, 2) == '0-') {
											$dlMessage = substr($downloadCheck, 2);
										// if other number returned, create message
										} else if (is_numeric($downloadCheck)) {
											$dlMessage = $downloadCheck . ' downloads remaining';
											$dlOK = true;
										}
									}
									// /end download sku check 
								}
?>
                                                    <a class="CWlink" href="<?php echo $_ENV["request.cwpage"]["urlDetails"]; ?>?product=<?php echo $skusQuery['product_id'][$ii];?>"><?php echo $skusQuery['product_name'][$ii]; ?></a>
                                                    <br>
<?php
								if ($dlOK) {
?>
													&nbsp;<span class="smallPrint">
<?php
									$myDir = getcwd();
									chdir(dirname(__FILE__));
									$module_settings = array(
										"sku_id" => $skusQuery["sku_id"][$ii],
										"customer_id" => $_SESSION["cwclient"]["cwCustomerID"],
										"show_file_size" => true,
										"show_file_name" => false,
										"show_file_version" => false,
										"show_last_version" => false,
										"show_last_date" => false,
										"show_remaining" => false);
									include("cwapp/mod/cw-mod-downloadlink.php");
									unset($module_settings);
									chdir($myDir);
?>
													</span>
<?php
								}
							}
						}
					}
?>
                                    </td>
                                </tr>
<?php
			}
?>
                            </tbody>
                        </table>
<?php
		} else {
			// if no orders ?>
					<p class="sideSpace">No orders found for <?php if(strlen(trim($_SESSION["cwclient"]["cwCustomerName"]))) {?>customer '<?php echo $_SESSION["cwclient"]["cwCustomerName"]; ?>'<?php } else { ?>this account<?php } ?>.</p>
<?php
	 	}
		break;
	}
	// ORDER DETAILS 
	case "details":
		// QUERY: get order details based on ID in url 
		$orderQuery = CWquerySelectOrderDetails($_GET["order"]);
?>
				<h3 class="CWformSectionTitle">Order Details<?php if ($orderQuery["totalRows"] > 0) { ?>&nbsp;&nbsp;<span class="smallPrint">Date: <?php echo cartweaverDate($orderQuery["order_date"][0]); ?>&nbsp;&nbsp;Order ID: <?php echo $orderQuery["order_id"][0]; ?></span><?php } ?></h3>
<?php
		// if order is found 
		if ($orderQuery["totalRows"] > 0) {
			// display order contents, passing in order query from above ?>
					<div class="headSpace sideSpace">
<?php
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			$module_settings = array(
				"order_query" => $orderQuery,
				"display_mode" => "summary",
				"show_images" => $_ENV["application.cw"]["appDisplayCartImage"],
				"show_sku" => $_ENV["application.cw"]["appDisplayCartSku"],
				"show_options" => true,
				"link_products" => true);
			include("cwapp/mod/cw-mod-orderdisplay.php");
			unset($module_settings);
			chdir($myDir);
?>
					</div>
<?php
		// no order found 
		} else {
?>
					<p>Invalid Order ID</p>
<?php
		}
?>
				<p class="sideSpace">&raquo;&nbsp;<a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>?view=orders">View All Past Orders</a></p>
				<p>&nbsp;</p>
<?php
		break;
	// PRODUCT HISTORY 
	case "products":
?>
				<h3 class="CWformSectionTitle">Purchased Items</h3>
<?php
		// QUERY: get products by customer id 
		$productsQuery = CWgetProductsByCustomer($_SESSION["cwclient"]["cwCustomerID"]);
		// if orders exist 
		if ($productsQuery["totalRows"] > 0) {
?>
					<div class="headSpace">
					</div>
					<table id="CWcustomerTable" class="CWformTable">
						<tbody>
<?php
			$lastPID = -1;
			for ($p=0; $p<$productsQuery["totalRows"]; $p++) {
				if ($productsQuery["product_id"][$p] != $lastPID) {
					$lastPID = $productsQuery["product_id"][$p];
					// get image for item ( add to cart item info )
					if ($_ENV["application.cw"]["appDisplayCartImage"]) {
						$itemImg = CWgetImage($productsQuery["product_id"][$p],4,$_ENV["application.cw"]["appImageDefault"]);
					} else {
						$itemImg = '';
					}
					if ($productsQuery["product_on_web"][$p] != 0 && $productsQuery["product_archive"][$p] != 1) {
?>
								<tr>
<?php
						if ($_ENV["application.cw"]["appDisplayCartImage"]) {
?>
									<td class="CWimgCell">
<?php
							// product image 
							if (strlen(trim($itemImg))) {
?>
											<div class="CWcartImage">
												<a href="<?php echo $_ENV["request.cwpage"]["urlDetails"]; ?>?product=<?php echo $productsQuery["product_id"][$p]; ?>" title="View Product">
												<img src="<?php echo $itemImg; ?>" alt="<?php echo htmlentities($productsQuery["product_name"][$p]); ?>">
												</a>
											</div>
<?php
							}
?>
									</td>
<?php
						}
?>
									<td style="width:223px;">
										<strong>
										<a class="CWlink smallPrint" href="<?php echo $_ENV["request.cwpage"]["urlDetails"]; ?>?product=<?php echo $productsQuery["product_id"][$p]; ?>"><?php echo $productsQuery["product_name"][$p]; ?></a>
										</strong>
									</td>
									<td>
										<p class="smallPrint">Order ID: <a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>?view=details&order=<?php echo $productsQuery["order_id"][$p]; ?>"><?php echo $productsQuery["order_id"][$p]; ?></a></p>
									</td>
									<td>
										<p class="smallPrint">Date: <?php echo cartweaverDate($productsQuery["order_date"][$p]); ?></p>
									</td>
								</tr>
<?php
					}
				}
			}
?>
						</tbody>
					</table>
<?php
		// if no orders 
		} else {
?>
					<p class="sideSpace">No products found for <?php if (strlen(trim($_SESSION["cwclient"]["cwCustomerName"]))) { ?>customer '<?php echo $_SESSION["cwclient"]["cwCustomerName"]; ?>'<?php } else { ?>this account<?php } ?>.</p>
<?php
		}
		break;
	// DOWNLOADS 
	case "downloads":
?>
				<h3 class="CWformSectionTitle">Downloads</h3>
<?php
		// if orders exist 
		if ($downloadsQuery["totalRows"]) {
?>
					<div class="headSpace">
					</div>
					<table id="CWcustomerTable" class="CWformTable">
						<tbody>
<?php
			for ($i=0; $i<$downloadsQuery["totalRows"]; $i++) {
				$lastProdID = $downloadsQuery["product_id"][$i];
				if ($downloadsQuery["product_on_web"][$i] != 0 && $downloadsQuery["product_archive"] != 1) {
?>
								<tr>
									<td style="width:223px;">
										<strong><a class="CWlink smallPrint" href="<?php echo $_ENV["request.cwpage"]["urlDetails"]; ?>?product=<?php echo $downloadsQuery["product_id"][$i]; ?>"><?php echo $downloadsQuery["product_name"][$i]; ?></a></strong>
									</td>
									<td>
<?php
					if ($_ENV["request.cwpage"]["downloadsEnabled"]) {
						// ungroup query output to loop each sku 
						while ($i<$downloadsQuery["totalRows"] && $lastProdID == $downloadsQuery["product_id"][$i]) {
							$dlOK = false;
							$dlMessage = '';
							// if sku has a download attached 
							if (strlen(trim($downloadsQuery["sku_download_id"][$i])) && !in_array($downloadsQuery["sku_id"][$i], $dlSkus)) {
								// check for download from this order: Cartweaver Digital Downloads 
								$downloadCheck = CWcheckCustomerDownload(
													$downloadsQuery["sku_id"][$i],
													$_SESSION["cwclient"]["cwCustomerID"],
													$downloadsQuery["order_id"][$i]
													);
								// if 0 is returned, no limit, no message 
								if ($downloadCheck == '0') {
									$dlOK = true;
								// if a "0-" error message is returned, no dl available, show text string --->
								} else if (substr($downloadCheck,0,2) == '0-') {
									$dlMessage = substr($downloadCheck,2);
								// if other number returned, create message 
								} else if (is_numeric($downloadCheck)) {
									$dlMessage = $downloadCheck & ' downloads remaining';
									$dlOK = true;
								}
							}
							// if download is ok, show link 
							if ($dlOK) {
								// expand dl data, show message 
?>
												<span class="cwDlLink">
<?php
								$myDir = getcwd();
								chdir(dirname(__FILE__));
								$module_settings = array(
									"sku_id" => $downloadsQuery["sku_id"][$i],
									"customer_id" => $_SESSION["cwclient"]["cwCustomerID"],
									"download_text" => "Download File:");
								include("cwapp/mod/cw-mod-downloadlink.php");
								unset($module_settings);
								chdir($myDir);
?>
												<br>
												</span>
<?php
							} else if (strlen(trim($dlMessage))) {
								$dlData = CWgetCustomerDownloadData($downloadsQuery["sku_id"][$i],$_SESSION["cwclient"]["cwCustomerID"]);
								echo trim($dlMessage);
								if (strlen(trim($downloadsQuery["sku_download_version"][$i]))) {
?>
														<br>Current Version: <?php echo $downloadsQuery["sku_download_version"][$i]; ?>
<?php
								}
?>
														<br><span class="smallPrint">
<?php
								// last download date 
								if ($dlData["date"] && strtotime($dlData["date"])) {
?>Last Download: <?php echo cartweaverDate($dlData["date"]); ?><?php
								}
								// last download version 
								if (strlen(trim($dlData["version"]))) {
?> Version: <?php echo trim($dlData["version"]); ?><?php
								}
?>
														</span>
<?php
							}
							// add to list of skus already shown 
							$dlSkus[] = $downloadsQuery["sku_id"][$i];
							// end grouped output 
							$i++;
						}
						$i--;
					}
					// /end if downloads enabled 
?>
									</td>
									<td>
										<p class="smallPrint">Order ID: <a href="<?php echo $_ENV["request.cwpage"]["hrefUrl"]; ?>?view=details&order=<?php echo $downloadsQuery["order_id"][$i]; ?>"><?php echo $downloadsQuery["order_id"][$i]; ?></a>
										<br>Date: <?php echo cartweaverDate($downloadsQuery["order_date"][$i],$_ENV["application.cw"]["globalDateMask"]); ?></p>
									</td>
								</tr>
<?php
				}
			}
?>
						</tbody>
					</table>
<?php
			// if no orders 
		} else {
?>
					<p class="sideSpace">No downloads found for <?php if (strlen(trim($_SESSION["cwclient"]["cwCustomerName"]))) { ?>customer '<?php echo $_SESSION["cwclient"]["cwCustomerName"]; ?>'<?php } else { ?>this account<?php } ?>.</p>
<?php
		}
		break;
	// PRODUCT VIEWS 
	case "views":
	{
?>
				<h3 class="CWformSectionTitle">Recently Viewed Items</h3>
				<div class="headSpace">
				</div>
<?php
		$prodViewArr = $_SESSION["cwclient"]["cwProdViews"];
		if (!is_array($prodViewArr) && strlen($prodViewArr)) $prodViewArr = explode(",", $prodViewArr);
		else if (!is_array($prodViewArr)) $prodViewArr = array();
		foreach ($prodViewArr as $key => $pp) {
			// show the product include ?>
					<div class="CWrecentProduct">
<?php
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			$module_settings = array(
				"product_id" => $pp,
				"add_to_cart" => $_ENV["application.cw"]["appDisplayListingAddToCart"],
				"show_description" => false,
				"show_image" => true,
				"show_price" => false,
				"image_class" => "CWimgRecent",
				"image_position" => "above",
				"title_position" => "below",
				"details_link_text" => "");
			include("cwapp/mod/cw-mod-productpreview.php");
			unset($module_settings);
			chdir($myDir);
?>
					</div>
<?php
		}
?>		
				<div class="CWclear">
				</div>
<?php
		break;
	}

	// CUSTOMER INFO FORM (default) 
	default:
	{
		// heading / submit button text for logged in vs. new customer 
		if(!strlen($_SESSION["cwclient"]["cwCustomerID"]) || $_SESSION["cwclient"]["cwCustomerID"] === 0 || $_SESSION["cwclient"]["cwCustomerID"] === "0" || $_SESSION["cwclient"]["cwCustomerType"] === "0" || (isset($_SESSION["cwclient"]["cwCustomerCheckout"]) && strtolower($_SESSION["cwclient"]["cwCustomerCheckout"]) == "guest")) {
?>
				<h3 class="CWformSectionTitle">New Customers: Complete Details Below</h3>
<?php
			$submitButtonText = "Create Account&nbsp;&raquo;";
		} else {
?>
				<h3 class="CWformSectionTitle">Address &amp; Account Details</h3>
<?php
			$submitButtonText = "Save Changes&nbsp;&raquo;";
		}
		$myDir = getcwd();
		chdir(dirname(__FILE__));
		$module_settings = array(
			"success_url" => $_ENV["request.cwpage"]["hrefUrl"],
			"submit_value" => $submitButtonText);
		include("cwapp/mod/cw-mod-formcustomer.php");
		unset($module_settings);
		chdir($myDir);
		break;
	}
}
// /end alerts ?>
	</div>
	<?php // /end customer account section ?>
</div>
<?php
// page end / debug 
$myDir = getcwd();
chdir(dirname(__FILE__));
include("cwapp/inc/cw-inc-pageend.php");
chdir($myDir);
?>
