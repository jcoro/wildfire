<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: customers.php
File Date: 2012-02-01
Description: Displays customer management table
==========================================================
*/
if (!ini_get("safe_mode") && !in_array("set_time_limit", explode(",", ini_get("disable_functions")))) @set_time_limit(9000);
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("any");
// PAGE PARAMS 
if(!isset($_ENV["application.cw"]["adminRecordsPerPage"])) { $_ENV["application.cw"]["adminRecordsPerPage"] = 30; }
// default values for paging/sorting
if(!isset($_GET['pagenumresults'])) { $_GET['pagenumresults'] = 1; }
if(!isset($_GET['maxrows'])) { $_GET['maxrows'] = $_ENV["application.cw"]["adminRecordsPerPage"]; }
if(!isset($_GET['sortby'])) { $_GET['sortby'] = "customer_last_name"; }
if(!isset($_GET['sortdir'])) { $_GET['sortdir'] = "asc"; }
// default search values 
if(!isset($_GET['custName'])) { $_GET['custName'] = ""; } 
if(!isset($_GET['custID'])) { $_GET['custID'] = ""; }
if(!isset($_GET['custEmail'])) { $_GET['custEmail'] = ""; }
if(!isset($_GET['custAddr'])) { $_GET['custAddr'] = ""; }
if(!isset($_GET['orderStr'])) { $_GET['orderStr'] = ""; }
// BASE URL 
// create the base url for sorting out of serialized url variables
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,pagenumresults,userconfirm,useralert");
// set up the base url 
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Manage Customers";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = "Customer Management";
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Use the search options and table links to view and manage customer info";
// current menu marker 
//$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"];
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"] = 1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"] = 1;
// START OUTPUT ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
        <link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"];?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
 <?php 	include("cwadminapp/inc/cw-inc-admin-scripts.php"); ?>
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
	 <?php
	  			include("cwadminapp/inc/cw-inc-admin-nav.php");
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
					if(strlen(trim($_ENV["request.cwpage"]["heading1"]))) {
						echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]). '</h1>';
					}
					if(strlen(trim($_ENV["request.cwpage"]["heading2"]))) {
						echo '<h2>'.trim($_ENV["request.cwpage"]["heading2"]).'</h2>';
					}
?>
					<!-- Admin Alert - message shown to user -->
<?php
					include("cwadminapp/inc/cw-inc-admin-alerts.php");
?>
					<!-- Page Content Area -->
					<div id="CWadminContent">
						<!-- //// PAGE CONTENT ////  -->
						<?php // SEARCH ?>
						<div id="CWadminCustomerSearch" class="CWadminControlWrap">
    <?php 
							// Order Search Form : Contains Customers QUERY 
	 						include("cwadminapp/inc/cw-inc-search-customer.php");
							if (!isset($customersQuery) || !isset($customersQuery["totalRows"])) {
								$customersQuery=CWquerySelectCustomers($_GET['custName'], $_GET['custID'], $_GET['custEmail'], $_GET['custAddr'], $_GET['orderStr'], null, true);
							}
	 						// if customers found, show the paging links 
							if($customersQuery['totalRows'] > 0) {
								echo $_ENV["request.cwpage"]["pagingLinks"];
								// set up the table display output 
								if(!isset($_ENV["application.cw"]["adminCustomerPaging"])) { $_ENV["application.cw"]["adminCustomerPaging"] = 1;}
								if(!$_ENV["application.cw"]["adminCustomerPaging"]) {
									$startRow_Results = 1;
									$maxRows_Results = $customersQuery['totalRows'];	
								
								}
							}
	 ?>                     
						</div>
                        <?php
						// /END SEARCH 
						// CUSTOMERS TABLE 
						// if no records found, show message 
	 					if(!$customersQuery['totalRows']) { ?>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p><strong>No customers found.</strong> <br><br>Try a different search above or click the 'Manage Customers' link to see all customers.</p>
	<?php				} else {
							// if we have some records to show ?>
							<table class="CWsort CWstripe" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
								<thead>
								<tr class="sortRow">
									<th class="noSort" width="50">View</th>
									<th class="customer_last_name">Last</th>
									<th class="customer_first_name">First</th>
									<th class="customer_zip">Address (Post Code)</th>
									<th class="customer_email">Email</th>
									<th class="customer_phone">Phone</th>
									<th class="customer_guest">Guest</th>
									<th width="85" class="order_date">Last Order</th>
									<th width="35" class="order_total">Amount</th>
									<th width="55" class="total_spending">Total</th>
								</tr>
								</thead>
								<tbody>
    <?php
								// OUTPUT CUSTOMERS 
								for($i=$startRow_Results-1; $i<$customersQuery['totalRows'] && $i<$endRow_Results; $i++) {
									// set up location  
									$customer_location = $customersQuery['customer_address1'][$i].', '. $customersQuery['customer_city'][$i]. ', '. $customersQuery['stateprov_name'][$i].' '.$customersQuery['customer_zip'][$i];
    							// output the row ?>
								<tr>
									<?php // details link ?>
									<td style="text-align:center;"><a href="customer-details.php?customer_id=<?php echo $customersQuery['customer_id'][$i];?>" title="View Customer Details"><img src="img/cw-edit.gif" width="15" height="15" alt="View Customer Details"></a></td>
									<?php // last name ?>
									<td><strong><a class="productLink" href="customer-details.php?customer_id=<?php echo $customersQuery['customer_id'][$i]; ?>"><?php echo $customersQuery['customer_last_name'][$i]; ?></a></strong>
                                    </td>
                                    <?php // first name ?>
									<td><strong><a class="productLink" href="customer-details.php?customer_id=<?php echo $customersQuery['customer_id'][$i]; ?>"><?php echo $customersQuery['customer_first_name'][$i]; ?></a></strong>
                                    </td>
									<?php // address ?>
									<td><?php echo $customer_location; ?></td>
									<?php // email ?>
									<td><?php if(isValidEmail($customersQuery['customer_email'][$i])) {
													echo $customersQuery['customer_email'][$i];
												}
										?>
                                    </td>
									<?php // phone ?>
									<td><?php echo $customersQuery['customer_phone'][$i]; ?></td>
									<?php // guest ?>
									<td><?php if (isset($customersQuery['customer_guest'][$i]) && $customersQuery['customer_guest'][$i] == 1) { ?>Yes<?php } else { ?>No<?php } ?></td>
      <?php 
									// order date 
	  								if((isset($customersQuery['top_order_date'][$i]))) {
										// if searching by order, we have this info already above 
										$customer_date = (($customersQuery['top_order_date'][$i] != "") ? date('M d',strtotime($customersQuery['top_order_date'][$i])) : "" );
									} else {
										// QUERY: get customer's last order via simple query (customer id, no. of rows to return) 
										$lastOrderQuery = CWquerySelectCustomerOrders($customersQuery['customer_id'][$i],1);
										$customer_date = ((isset($lastOrderQuery['customer_date'][0]) && $lastOrderQuery['customer_date'][0] != "") ? date('M d',strtotime($lastOrderQuery['customer_date'][0])) : "" );
									}
	  ?>    		
      								<td style="white-space: nowrap;"><?php echo $customer_date; ?>
                                    </td>
									<?php // order total ?>
									<td>
    <?php 							if(($customersQuery['top_order_date'][$i])) {
										echo cartweaverMoney($customersQuery['order_total'][$i]);
									}
	?>			
									</td>
                                    <?php // total spending ?>
									<td>
   <?php                            if((isset($customersQuery['total_spending'][$i]))) {
                                       	echo cartweaverMoney($customersQuery['total_spending'][$i]);
                                    }                   
	?>
    								</td>
								</tr>							
	<?php   					}
  	                        	// /END OUTPUT CUSTOMERS ?>
								</tbody>
							</table>
                            <?php // footer links ?>
							<div class="tableFooter"><?php echo $_ENV["request.cwpage"]["pagingLinks"]; ?></div>
	<?php				}
	?>
                        </div>
					<!-- /end Page Content -->
					<div class="clear"></div>
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
						
									
									
								
							
					
			
