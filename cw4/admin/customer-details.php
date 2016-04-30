<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: customer-details.php
File Date: 2012-02-01
Description: Displays customer details and options
==========================================================
*/
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
// default search values 
if(!isset($_GET['custName'])) { $_GET['custName'] = ""; } 
if(!isset($_GET['custID'])) { $_GET['custID'] = ""; }
if(!isset($_GET['custEmail'])) { $_GET['custEmail'] = ""; }
if(!isset($_GET['custAddr'])) { $_GET['custAddr'] = ""; }
if(!isset($_GET['orderStr'])) { $_GET['orderStr'] = ""; }
// define showtab to set up default tab display 
if(!isset($_GET['showtab'])) { $_GET['showtab'] = 1; }
// default values for paging/include
if(!isset($_GET['pagenumresults'])) { $_GET['pagenumresults'] = 1;}
if(!isset($_GET['maxrows'])) { $_GET['maxrows'] = $_ENV["application.cw"]["adminRecordsPerPage"]; }
if(!isset($_GET['sortby'])) { $_GET['sortby'] = "custName"; }
if(!isset($_GET['sortdir'] )) { $_GET['sortdir'] = "asc"; }
// this customer_id var is used for current page lookup - different from search var 
if(!isset($_GET['customer_id'])) { $_GET['customer_id'] = 0; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("view,userconfirm,useralert,sortby,sortdir");
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_SERVER['SCRIPT_NAME']);
// QUERY: get details on customer based on url (customer id)--;->
$customerQuery = CWquerySelectCustomerDetails($_GET['customer_id']);
// QUERY: get users's order details (customer id,number of orders to return 
$ordersQuery = CWquerySelectCustomerOrderDetails($_GET['customer_id'],50);
// QUERY: get user's shipping info (customer id)
$shippingQuery = CWquerySelectCustomerShipping($_GET['customer_id']);
// if we do not have a customer ID, add new 
// QUERY: get all states / countries 
$statesQuery = CWquerySelectStates();
// QUERY: get all customer types 
$typesQuery = CWquerySelectCustomerTypes();
	// if we have a customer ID in the url, redirect to main page if no customer found 
if($_GET['customer_id'] > 0 && !$customerQuery['totalRows']) {
	CWpageMessage("alert",'Customer '.$_GET['customer_id'].' not found');
	header("Location: customers.php?useralert='".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."'");
}
else if($_GET['customer_id'] == 0) {
	// if we did not have customer ID in the url, only allow higher levels here 
	$_ENV["request.cwpage"]["accessLevel"] = CWauth("manager,merchant,developer");
}
// starting value for order total row 
$_ENV["request.cwpage"]["orderTotal"] = 0;
// /////// 
// UPDATE CUSTOMER 
// /////// 	
if(isset($_POST['UpdateCustomer'])) {
	// QUERY: update customer record (all customer form variables) 
	$updateCustomerID = CWqueryUpdateCustomer(
		$_POST['customer_id'],
		$_POST['customer_type_id'],
		$_POST['customer_first_name'],
		$_POST['customer_last_name'],
		$_POST['customer_email'],
		$_POST['customer_username'],
		$_POST['customer_password'],
		$_POST['customer_company'],
		$_POST['customer_phone'],
		$_POST['customer_phone_mobile'],
		$_POST['customer_address1'],
		$_POST['customer_address2'],
		$_POST['customer_city'],
		$_POST['customer_billing_state'],
		$_POST['customer_zip'],
		$_POST['customer_ship_name'],
		$_POST['customer_ship_company'],
		$_POST['customer_ship_address1'],
		$_POST['customer_ship_address2'],
		$_POST['customer_ship_city'],
		$_POST['customer_ship_state'],
		$_POST['customer_ship_zip']);
	// query checks for duplicate fields 
	if((substr($updateCustomerID,0,2)) == '0-') {	
		$cusid = explode('-', $updateCustomerID);
		$cusid_last = $cusid[count($cusid) - 1];
		$dupField = $cusid_last;
		CWpageMessage("alert","Error: ".$dupField." already exists");
		// update complete: return to page showing message 
	} else {
		CWpageMessage("confirm","Customer Updated");
		header("Location: ".$_ENV["request.cw"]["thisPage"]."?customer_id=".$_GET['customer_id']."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."");
	}	
}
// /////// 
// /END UPDATE CUSTOMER 
// /////// 
// /////// 
// ADD NEW CUSTOMER 
// /////// 
if(isset($_POST['AddCustomer'])) {
	// QUERY: Add new customer (all customer form variables) 
	// this query returns the customer id, or an error like '0-fieldname' 
	$newCustomerID = CWqueryInsertCustomer(
		$_POST['customer_type_id'],
		$_POST['customer_first_name'],
		$_POST['customer_last_name'],
		$_POST['customer_email'],
		$_POST['customer_username'],
		$_POST['customer_password'],
		$_POST['customer_company'],
		$_POST['customer_phone'],
		$_POST['customer_phone_mobile'],
		$_POST['customer_address1'],
		$_POST['customer_address2'],
		$_POST['customer_city'],
		$_POST['customer_billing_state'],
		$_POST['customer_zip'],
		$_POST['customer_ship_name'],
		$_POST['customer_ship_company'],
		$_POST['customer_ship_address1'],
		$_POST['customer_ship_address2'],
		$_POST['customer_ship_city'],
		$_POST['customer_ship_state'],
		$_POST['customer_ship_zip'],
		$_ENV["application.cw"]["customerAccountEnabled"]);
	// if no error returned from insert query 
	if((substr($newCustomerID,0,2)) != '0-') {
		// update complete: return to page showing message 
		CWpageMessage("confirm","Customer Added");
		header("Location: ".$_ENV["request.cw"]["thisPage"]."?customer_id=".$newCustomerID."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."");
		exit;
	} else {
		// if we have an insert error, show message, do not insert 
		$newcust = explode('-',$newCustomerID);
		$dupField = $newcust[count($newcust) - 1];
		CWpageMessage("alert","Error: ".$dupField." already exists");
	}
	// /END duplicate/error check 
}
// /////// 
// /END ADD NEW CUSTOMER 
// /////// 
// /////// 
// DELETE CUSTOMER 
// /////// 
if(isset($_GET['deleteCst'])) {
	if (!isset($_GET["returnUrl"]) || $_GET["returnUrl"] == '') { $_GET["returnUrl"] = "customers.php?useralert=".CWurlSafe('Unable to delete: customer '.$_GET['deleteCst'].' not found'); }
	// QUERY: delete customer record (id from url)
	$deleteCustomer = CWqueryDeleteCustomer($_GET['deleteCst']);
	header("Location: ".$_GET["returnUrl"]);
	exit;
}
// /////// 
// /END DELETE CUSTOMER 
// /////// 
// Params for form fields below 
if(!isset($_POST['customer_type_id'] )) { $_POST['customer_type_id'] = ((isset($customerQuery['customer_type_id'][0])) ? $customerQuery['customer_type_id'][0] : "" ); }
if(!isset($_POST['customer_first_name'])) { $_POST['customer_first_name'] = ((isset($customerQuery['customer_first_name'][0])) ? $customerQuery['customer_first_name'][0] : "" ); }
if(!isset($_POST['customer_last_name'])) { $_POST['customer_last_name'] = ((isset($customerQuery['customer_last_name'][0])) ? $customerQuery['customer_last_name'][0] : "" ); }
if(!isset($_POST['customer_email'])) { $_POST['customer_email'] = ((isset($customerQuery['customer_email'][0])) ? $customerQuery['customer_email'][0] : "" ); }
if(!isset($_POST['customer_username'] )) { $_POST['customer_username'] = ((isset($customerQuery['customer_username'][0])) ? $customerQuery['customer_username'][0] : "" ); }
if(!isset($_POST['customer_password'])) { $_POST['customer_password'] = ((isset($customerQuery['customer_password'][0])) ? $customerQuery['customer_password'][0] : "" ); }
if(!isset($_POST['customer_company'])) { $_POST['customer_company'] = ((isset($customerQuery['customer_company'][0])) ? $customerQuery['customer_company'][0] : "" ); }
if(!isset($_POST['customer_phone'])) { $_POST['customer_phone'] = ((isset($customerQuery['customer_phone'][0])) ? $customerQuery['customer_phone'][0] : "" ); }
if(!isset($_POST['customer_phone_mobile'])) { $_POST['customer_phone_mobile'] = ((isset($customerQuery['customer_phone_mobile'][0])) ? $customerQuery['customer_phone_mobile'][0] : "" ); }
if(!isset($_POST['customer_address1'])) { $_POST['customer_address1'] = ((isset($customerQuery['customer_address1'][0])) ? $customerQuery['customer_address1'][0] : "" ); }
if(!isset($_POST['customer_address2'])) { $_POST['customer_address2'] = ((isset($customerQuery['customer_address2'][0])) ? $customerQuery['customer_address2'][0] : "" ); }
if(!isset($_POST['customer_city'])) { $_POST['customer_city'] = ((isset($customerQuery['customer_city'][0])) ? $customerQuery['customer_city'][0] : "" ); }
if(!isset($_POST['customer_billing_state'])) { $_POST['customer_billing_state'] = ((isset($customerQuery['stateprov_id'][0])) ? $customerQuery['stateprov_id'][0] : "" ); }
if(!isset($_POST['customer_zip'])) { $_POST['customer_zip'] = ((isset($customerQuery['customer_zip'][0])) ? $customerQuery['customer_zip'][0] : "" ); }
if(!isset($_POST['customer_ship_name'])) { $_POST['customer_ship_name'] = ((isset($shippingQuery['customer_ship_name'][0])) ? $shippingQuery['customer_ship_name'][0] : "" ); }
if(!isset($_POST['customer_ship_company'])) { $_POST['customer_ship_company'] = ((isset($customerQuery['customer_ship_company'][0])) ? $customerQuery['customer_ship_company'][0] : "" ); }
if(!isset($_POST['customer_ship_address1'])) { $_POST['customer_ship_address1'] = ((isset($shippingQuery['customer_ship_address1'][0])) ? $shippingQuery['customer_ship_address1'][0] : "" ); }
if(!isset($_POST['customer_ship_address2'])) { $_POST['customer_ship_address2'] = ((isset($shippingQuery['customer_ship_address2'][0])) ? $shippingQuery['customer_ship_address2'][0] : "" ); }
if(!isset($_POST['customer_ship_city'])) { $_POST['customer_ship_city'] = ((isset($shippingQuery['customer_ship_city'][0])) ? $shippingQuery['customer_ship_city'][0] : "" ); }
if(!isset($_POST['customer_ship_state'])) { $_POST['customer_ship_state'] = ((isset($shippingQuery['stateprov_id'][0])) ? $shippingQuery['stateprov_id'][0] : "" ); }
if(!isset($_POST['customer_ship_zip'])) { $_POST['customer_ship_zip'] = ((isset($shippingQuery['customer_ship_zip'][0])) ? $shippingQuery['customer_ship_zip'][0] : "" ); }
// set up heading 
$_ENV["request.cwpage"]["headText"] = "";
// if we are editing, show details 
if($customerQuery['totalRows']) {
	$_ENV["request.cwpage"]["headText"] = "<span class='subhead'>Customer Details &nbsp;&nbsp;&nbsp;".$customerQuery['customer_first_name'][0]." ".$customerQuery['customer_last_name'][0].' (ID:'.$customerQuery['customer_id'][0].')</span>';
		// if adding new, show simple heading 
		$_ENV["request.cwpage"]["editMode"] = 'edit';
} else {
	$_ENV["request.cwpage"]["headText"] = "Customer Management: Add New Customer";
	$_ENV["request.cwpage"]["editMode"] = 'add';
}
// set up subheading 
$_ENV["request.cwpage"]["subHead"] = "";
if(isset($customerQuery['customer_phone'][0]) && strlen(trim($customerQuery['customer_phone'][0]))) {
	$_ENV["request.cwpage"]["subHead"] .= 'Phone: ' .$customerQuery['customer_phone'][0]."&nbsp;&nbsp;&nbsp;";
}
if(isset($customerQuery['customer_email'][0]) && strlen(trim($customerQuery['customer_email'][0]))) {
	$_ENV["request.cwpage"]["subHead"] .= 'Email: ';
	if (isValidEmail(trim($customerQuery['customer_email'][0]))) {
		$_ENV["request.cwpage"]["subHead"] .= '<'.'a href="mailto:'.trim($customerQuery['customer_email'][0]).'">'.trim($customerQuery['customer_email'][0]).'<'.'/'.'a>';
	}
	else {
		$_ENV["request.cwpage"]["subHead"] .= ''.trim($customerQuery['customer_email'][0]).'';
	}
}
$_ENV["request.cwpage"]["subHead"] .= 'Orders: ' .$ordersQuery['totalRows'].((isset($ordersQuery['order_date'][0])) ? '&nbsp;&nbsp;&nbsp;Last Order: '.cartweaverDate($ordersQuery['order_date'][0]) : "" ).((isset($ordersQuery['order_total'][0])) ? ' ('.cartweaverMoney($ordersQuery['order_total'][0]).')' : "" );
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"] = "Customer Details";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = $_ENV["request.cwpage"]["headText"];
// Page request.cwpage.subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = $_ENV["request.cwpage"]["subHead"];
// current menu marker 
if($customerQuery['totalRows']) {
	$_ENV["request.cwpage"]["currentNav"] = "customers.php";
} else {
	$_ENV["request.cwpage"]["currentNav"] = "customer-details.php";
}
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"] = 1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"] = 1;
// START OUTPUT ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php include("cwadminapp/inc/cw-inc-admin-scripts.php"); ?>
		<!-- page javascript -->
		<script type="text/javascript">
		jQuery(document).ready(function() {
			// copy billing info to shipping
			jQuery('#copyInfo').click(function() {
			// if checking the box
			if (jQuery(this).prop('checked')==true) {
				// get values of shipping
				var valName = jQuery('#customer_first_name').val() + ' ' + jQuery('#customer_last_name').val();
				var valCo = jQuery('#customer_company').val();
				var valAddr1 = jQuery('#customer_address1').val();
				var valAddr2 = jQuery('#customer_address2').val();
				var valCity = jQuery('#customer_city').val();
				var valState = jQuery('#customer_billing_state').val();
				var valZip = jQuery('#customer_zip').val();
				// add to billing
				jQuery('#customer_ship_name').val(valName);
				jQuery('#customer_ship_company').val(valCo);
				jQuery('#customer_ship_address1').val(valAddr1);
				jQuery('#customer_ship_address2').val(valAddr2);
				jQuery('#customer_ship_city').val(valCity);
				jQuery('#customer_ship_state').val(valState);
				jQuery('#customer_ship_zip').val(valZip);
			}
			});

		});
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
<?php include("cwadminapp/inc/cw-inc-admin-nav.php"); ?> 
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
						// Order Search Form 
 						include("cwadminapp/inc/cw-inc-search-customer.php");  
?>
						</div>
<?php
						// /END SEARCH 
						// /////// 
						// ADD/UPDATE CUSTOMER 
						// /////// ?>
						<form name="CustomerDetails" method="post" action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate CWobserve" enctype="multipart/form-data">
                                                        <?php if (isset($_GET['customer_id'])) { echo '<input type="hidden" name="customer_id" value="'.$_GET['customer_id'].'" />'; } ?>
							<!-- TABBED LAYOUT -->
							<div id="CWadminTabWrapper">
								<!-- TAB LINKS -->
								<ul class="CWtabList">
									<?php // tab 1 ?>
									<li><a href="#tab1" title="Customer Info">Customer Info</a></li>
<?php
									// tab 2 
if($ordersQuery['totalRows']) {
?>
										<li><a href="#tab2" title="Purchase History">Purchase History</a></li>
<?php } ?>									
								</ul>
								<div class="CWtabBox">
									<?php // FIRST TAB (status) ?>
									<div id="tab1" class="tabDiv">
										<h3>Customer Details</h3>
										<?php // customer details table ?>
										<table class="CWformTable wide">
											<?php // split into billing/shipping ?>
											<tr>
												<td class="customerInfo" id="contactCell" colspan="2">
												<?php // contact / login info ?>
													<table class="CWformTable">
														<tr class="headerRow">
															<th colspan="2"><h3>Contact Details</h3></th>
														</tr>
														<tr>
															<th class="label">First Name</th>
															<td><input name="customer_first_name" class="{required:true}" title="First Name is required" size="17" type="text" id="customer_first_name" value="<?php echo $_POST['customer_first_name']; ?>"></td>
														</tr>
														<tr>
															<th class="label">Last Name</th>
															<td><input name="customer_last_name" class="{required:true}" title="Last Name is required" size="17" type="text" id="customer_last_name" value="<?php echo $_POST['customer_last_name']; ?>"></td>
														</tr>
														<tr>
															<th class="label">Email</th>
															<td><input type="text" class="{required:true,email:true}" title="Valid Email is required"  size="21" name="customer_email" id="customer_email" value="<?php echo $_POST['customer_email']; ?>"></td>
														</tr>
														<tr>
															<th class="label">Phone</th>
															<td><input type="text" class="{required:true}" title="Phone Number is required" size="14" name="customer_phone" id="customer_phone" value="<?php echo $_POST['customer_phone']; ?>"></td>
														</tr>
														<tr>
															<th class="label">Mobile</th>
															<td><input type="text" size="14" name="customer_phone_mobile" id="customer_phone_mobile" value="<?php echo $_POST['customer_phone_mobile']; ?>"></td>
														</tr>
													</table>
                                                    <?php
													// /end general info 
													// contact / billing ?>
													<table class="CWformTable">
														<tr class="headerRow"><th colspan="2"><h3>Billing Information</h3></th></tr>

														<tr>
															<th class="label">Company</th>
															<td><input type="text" size="21" name="customer_company" id="customer_company" value="<?php echo $_POST['customer_company']; ?>"></td>
														</tr>
														<tr>
															<th class="label">Address</th>
															<td>
																<input type="text" class="{required:true}" title="Billing Address is required"  name="customer_address1" id="customer_address1" value="<?php echo $_POST['customer_address1']; ?>">
																<br>
																<br>
																<input type="text" name="customer_address2" id="customer_address2" value="<?php echo $_POST['customer_address2']; ?>">
															</td>
														</tr>
														<tr>
															<th class="label">City</th>
															<td>
																<input type="text" name="customer_city" id="customer_city" class="{required:true}" title="Billing City is required" value="<?php echo $_POST['customer_city']; ?>">
															</td>
														</tr>
														<tr>
															<th class="label">State/Prov</th>
															<td>
																<select name="customer_billing_state" id="customer_billing_state">
<?php
$lastCountry = "";
for($i=0; $i<$statesQuery['totalRows']; $i++) {
	$lastCountry = $statesQuery['country_name'][$i];
?>
																	<optgroup label="<?php echo $statesQuery['country_name'][$i]; ?>">
<?php
	while ($i<$statesQuery["totalRows"] && $lastCountry == $statesQuery['country_name'][$i]) {
?>
																		<option value="<?php echo $statesQuery['stateprov_id'][$i]; ?>"<?php if($statesQuery['stateprov_id'][$i] == $_POST['customer_billing_state']) { ?> selected="selected"<?php } ?>><?php echo $statesQuery['stateprov_name'][$i]; ?></option>
<?php
		$i++;
	}
?>
																	</optgroup>
<?php
	$i--;
}																
?>
																</select>
															</td>
														</tr>
														<tr>
															<th class="label">Post Code/Zip</th>
															<td>
																<input type="text" name="customer_zip" id="customer_zip" class="{required:true}" title="Billing Post Code is required" value="<?php echo $_POST['customer_zip']; ?>" size="8">
															</td>
														</tr>
<?php
// only show country if a saved record exists 
if($customerQuery['totalRows']) { ?>
															<tr>
																<th class="label">Country</th>
																<td>
<?php                                                             echo $customerQuery['country_name'][0];
?>
																</td>
															</tr>		
<?php } ?>
													</table>
												</td>
												<?php // /END billing info ?>
												<td class="customerInfo" id="shippingCell" colspan="2">
												<?php // general customer info ?>
													<table class="CWformTable">
														<tr class="headerRow">
															<th colspan="2"><h3>Customer Account<?php if (isset($customerQuery["customer_guest"][0]) && $customerQuery["customer_guest"][0] == 1) { ?> *Guest Account<?php } ?></h3></th>
														</tr>
<?php
// if we have more than one type, show the selector 
if($typesQuery['totalRows'] > 1) { ?>
															<tr>
																<th class="label">Customer Type</th>
																<td>
																	<select name="customer_type_id" id="customer_type_id">
<?php
	for($i=0; $i<$typesQuery['totalRows']; $i++) { ?>
																		<option value="<?php echo $typesQuery['customer_type_id'][$i];?>"<?php if($typesQuery['customer_type_id'][$i] == $_POST['customer_type_id']) { ?> selected="selected"<?php } ?>><?php echo $typesQuery['customer_type_name'][$i]; ?></option>
<?php
	}
?>
																	</select>
																</td>
															</tr>
<?php														
	// if only one type exists, use this by default 
} else { ?>
															<input type="hidden" name="customer_type_id" id="customer_type_id" value="<?php echo $_POST['customer_type_id']; ?>">
<?php
}
// /end customer type ?>
														<tr>
														<th class="label">Username</th>
														<td><input name="customer_username" class="{required:true}" title="Username is required" size="17" type="text" id="customer_username" value="<?php echo $_POST['customer_username']; ?>"></td>
														</tr>
														<tr>
														<th class="label">Password</th>
														<td><input name="customer_password" class="{required:true}" title="Password is required" size="17" type="text" id="customer_password" value="<?php echo $_POST['customer_password']; ?>"></td>
														</tr>
														<tr>
														<th class="label">Order Details</th>
														<td>
<?php
if($ordersQuery['totalRows']) { ?>
                                                        	<a href="orders.php?custName=<?php echo $_GET['customer_id']; ?>&startDate=<?php echo urlencode(cartweaverScriptDate('2000-01-01')); ?>">View Order History</a>
<?php
} else {
?>
															No Orders Placed
<?php
}
?> 
														</td>
                                                        </tr>
													</table>		                                                     
													<table class="CWformTable">
														<tr class="headerRow">
															<th colspan="2">
																<h3>Shipping Information
																<span class="smallPrint"><input type="checkbox" id="copyInfo">&nbsp;Same as Billing</span>
																</h3>
															</th>
														</tr>
														<tr>
															<th class="label">Ship To (Name)</th>
															<td><input name="customer_ship_name" id="customer_ship_name" class="{required:true}" title="Ship To (name) is required" type="text" value="<?php echo $_POST['customer_ship_name']; ?>"></td>
														</tr>
														<tr>
															<th class="label">Company</th>
															<td><input type="text" size="21" name="customer_ship_company" id="customer_ship_company" value="<?php echo $_POST['customer_ship_company']; ?>"></td>
														</tr>
														<tr>
															<th class="label">Address</th>
															<td>
																<input type="text" name="customer_ship_address1" id="customer_ship_address1" class="{required:true}" title="Shipping Address is required" value="<?php echo $_POST['customer_ship_address1']; ?>">
																<br>
																<br>
																<input type="text" name="customer_ship_address2" id="customer_ship_address2" value="<?php echo $_POST['customer_ship_address2']; ?>">
															</td>
														</tr>
														<tr>
															<th class="label">City</th>
															<td><input type="text" name="customer_ship_city" id="customer_ship_city" class="{required:true}" title="Shipping City is required" value="<?php echo $_POST['customer_ship_city']; ?>"></td>
														</tr>
														<tr>
															<th class="label">State/Prov</th>
															<td>
																<select name="customer_ship_state" id="customer_ship_state">
<?php
$lastCountry = "";
for($i=0; $i<$statesQuery['totalRows']; $i++) {
	$lastCountry = $statesQuery['country_name'][$i];
?>
																	<optgroup label="<?php echo $statesQuery['country_name'][$i]; ?>">
<?php
	while ($i<$statesQuery["totalRows"] && $lastCountry == $statesQuery['country_name'][$i]) {
?>
																		<option value="<?php echo $statesQuery['stateprov_id'][$i]; ?>"<?php if($statesQuery['stateprov_id'][$i] == $_POST['customer_ship_state']) { ?> selected="selected"<?php } ?>><?php echo $statesQuery['stateprov_name'][$i]; ?></option>
<?php
		$i++;
	}
?>
																	</optgroup>
<?php
	$i--;
}																
?>
																</select>
															</td>
														</tr>
														<tr>
															<th class="label">Post Code/Zip</th>
															<td><input type="text" name="customer_ship_zip" id="customer_ship_zip" class="{required:true}" title="Shipping Post Code is required"value="<?php echo $_POST['customer_ship_zip']; ?>" size="8"></td>
														</tr>
<?php
// only show country if a saved record exists 
if($customerQuery['totalRows']) {
?>                                                      
															<tr>
																<th class="label">Country</th>
																<td>
																	<?php echo ((isset($shippingQuery['country_name'][0])) ? $shippingQuery['country_name'][0] : "" ); ?>
																</td>
															</tr>
<?php
}
?>														
													</table>
<?php
// SUBMIT BUTTON 
if($customerQuery['totalRows']) { ?>
														<input name="UpdateCustomer" type="submit" class="CWformButton" id="UpdateCustomer" value="Save Changes" />
<?php
} else { ?>
														<input name="AddCustomer" type="submit" class="CWformButton" id="AddCustomer" value="Save Customer">
<?php
} 
?>													
												</td>
												<?php // /END shipping info ?>
											</tr>
											<?php // /END billing shipping ?>
										</table>
									</div>
                                    
<?php
if($ordersQuery['totalRows']) {
	// SECOND TAB (details) ?>
                                    
										<div id="tab2" class="tabDiv">
											<h3>Order Summary
											<span class="smallPrint"><a href="orders.php?custName=<?php echo $_GET['customer_id']; ?>&startDate=<?php echo urlencode(cwDateFormat('2000-01-01',$_ENV["application.cw"]["globalDateMask"])); ?>">View all orders for this customer</a></span>
											</h3>
											<table id="tblOrderDetails" class="wide CWinfoTable" style="width:735px;">
												<thead>
												<tr class="sortRow">
													<th class="noSort">View</th>
													<th class="order_id">Order ID</th>
													<th width="75" class="order_date">Date</th>
													<th >Products</th>
													<th class="order_total">Total</th>
												</tr>
												</thead>
												<tbody>
<?php
	$lastOrderID = "";
	for($i=0; $i<$ordersQuery['totalRows']; $i++) {
		$lastOrderID = $ordersQuery['order_id'][$i];
		$_ENV["request.cwpage"]["orderTotal"] += $ordersQuery["order_total"][$i];
		$order_id = $ordersQuery['order_id'][$i];
?>
												<tr>
													<td style="text-align:center">
														<a href="order-details.php?order_id=<?php echo $order_id; ?>&amp;returnUrl=<?php echo urlencode($_ENV["request.cwpage"]["baseURL"]); ?>">
														<img src="img/cw-edit.gif" alt="View Order Details" width="15" height="15"></a>
													</td>
													<td>
														<a href="order-details.php?order_id=<?php echo $order_id; ?>" class="productLink"><?php
		if((strlen($order_id)) > 16) {
			echo substr($order_id,0,16)."...";
		}
		else {
			echo $order_id;
		}
?></a>
													</td>                                                      
													<td style="text-align:right;"><?php echo cartweaverDate($ordersQuery['order_date'][$i]); ?>
                                                    </td>
													<td class="noLink"><?php
		while ($i<$ordersQuery['totalRows'] && $lastOrderID == $ordersQuery['order_id'][$i]) {
			if($_ENV["request.cwpage"]["accessLevel"] == 'service') {
?>
														<a href="<?php echo $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["request.cwpage"]["urlDetails"];?>?product=<?php echo $ordersQuery['product_id'][$i]; ?>" class="columnLink"><?php echo $ordersQuery['product_name'][$i]; ?></a>
<?php
			} else {
?>
														<a href="product-details.php?productid=<?php echo $ordersQuery['product_id'][$i]; ?>" class="columnLink"><?php echo $ordersQuery['product_name'][$i]; ?></a>
<?php													
			}
?>
														<span class="smallprint"><?php echo $ordersQuery['sku_merchant_sku_id'][$i]; ?></span>
														<br>
<?php
			$i++;
		}
		$i--;
?>
                                                    </td>
													<td style="text-align:right;"><?php echo cartweaverMoney($ordersQuery['order_total'][$i]); ?></td>
												</tr>
<?php
	}
	// sum total row 
?>
                                                <tr class="dataRow">
													<th colspan="4" style="text-align:right;"><strong>Total Spending</strong></th>
													<td style="text-align:right;"><strong><?php echo cartweaverMoney($_ENV["request.cwpage"]["orderTotal"]); ?></strong></td>
												</tr>
												</tbody>
											</table>
										</div>
<?php
}
// /END tab 2 ?>                                                
								</div>
											
                                            
<?php
if($_ENV["request.cwpage"]["editMode"] == 'edit') {
?>
									<div class="CWformButtonWrap">
										<p>&nbsp;</p>
										<p>&nbsp;</p>
<?php 
	// If there are no orders show delete button 
	if($ordersQuery['totalRows'] == 0) { ?>
											<a class="CWbuttonLink deleteButton" onClick="return confirm('Delete Customer <?php echo cwStringFormat($customerQuery['customer_first_name'][0]); ?> <?php echo cwStringFormat($customerQuery['customer_last_name'][0]); ?>?')" href="customer-details.php?deleteCst=<?php echo $_GET['customer_id']; ?>&returnUrl=customers.php?userconfirm=Customer Deleted">Delete Customer</a>
<?php
	} else { ?>
											<p>(Orders placed, delete disabled)</p>
<?php
	}
?>  
										</div>
<?php
}
?>                                        
							</div>
						</form>                                      
								
<?php 
						// /////// 
						// END ADD/UPDATE CUSTOMER 
						// /////// ?>
						<div class="clear"></div>
					</div>
					<!-- /end Page Content -->
				</div>
				<!-- /end CWinner -->
<?php 
				// page end content / debug 
				include("cwadminapp/inc/cw-inc-admin-page-end.php");
?>                
				<!-- /end CWadminPage-->
				<div class="clear"></div>
			</div>
			<!-- /end CWadminPage -->
		</div>
		<!-- /end CWadminWrapper -->
	</body>
</html>
