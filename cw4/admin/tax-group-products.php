<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: tax-group-products.php
File Date: 2012-02-01
Description: Manage Products in a tax group
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"]=CWauth('merchant,developer');
// PAGE PARAMS 
// default value for active or archived view
if(!(isset($_GET['view']))) $_GET['view']='active';
if(!(isset($_GET['tax_group_id']))) $_GET['tax_group_id']=0;
if(!(isset($_ENV["request.cwpage"]["currentRecord"]))) $_ENV["request.cwpage"]["currentRecord"]=$_GET['tax_group_id'];
if(!(isset($_GET['sortby']))) $_GET['sortby']='product_name';		    
if(!(isset($_GET['sortdir']))) $_GET['sortdir']='asc';	
// default form values 
if(!(isset($_POST['ProductCount']))) $_POST['ProductCount']='';
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars('view,userconfirm,useralert,clickadd,sortby,sortdir');
// create the base url out of serialized url variables
$_ENV["request.cwpage"]["baseURL"]=CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// /////// 
// UPDATE PRODUCT TAX GROUPS 
// /////// 
// look for at least one valid ID field 
if(isset($_POST['tax_group_id0'])) {
	$loopCt=0;
	$updateCt=0;
	// loop record ids, handle each one as needed 
	foreach ($_POST['recordIDlist'] as $key => $ID) {
	// UPDATE RECORDS 
		if (!(isset($_POST['tax_group_id'.$loopCt]))) $_POST['tax_group_id'.$loopCt]=0;
		// verify numeric tax group 
		if (!(is_numeric($_POST['tax_group_id' . $loopCt]))) $_POST['tax_group_id' . $loopCt]=0;
		// only update those not already set to this group 
		if ($_POST['tax_group_id'.$loopCt] != $_ENV["request.cwpage"]["currentRecord"]) {
			// QUERY: update record (product ID, tax group ID) 
			$updateRecord = CWqueryUpdateProductTaxGroup($_POST["product_id" .$loopCt],$_POST["tax_group_id" . $loopCt]);
			$updateCt++;
		}
		// /end only those not in this group 
		$loopCt++;
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars("userconfirm,useralert");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"]=CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// save confirmation text 
	$_ENV["request.cwpage"]["userconfirmText"] ='';
	if($updateCt > 0) {
		$_ENV["request.cwpage"]["userconfirmText"] .= $updateCt . ' Product';
		if($updateCt > 1)
			$_ENV["request.cwpage"]["userconfirmText"] .= 's';
		$_ENV["request.cwpage"]["userconfirmText"] .= ' Reassigned';
	}
	CWpageMessage("confirm",$_ENV["request.cwpage"]["userConfirm"]);
	// return to page as submitted, clearing form scope 
	header("Location: ".$_ENV["request.cwpage"]["relocateURL"] . '&userconfirm=' . CWurlSafe($_ENV["request.cwpage"]["userconfirmText"]));    
	exit;
}
// /////// 
// /END UPDATE PRODUCT TAX GROUPS 
// /////// 
// QUERY: get tax group (active/archived, group ID)
$taxGroupQuery = CWquerySelectTaxGroups(0, $_ENV["request.cwpage"]["currentRecord"]);
$currentGroup = $taxGroupQuery['tax_group_id'][0];
$_ENV["request.cwpage"]["groupName"] = $taxGroupQuery['tax_group_name'][0];
// QUERY: get products by tax group (group ID)
$taxGroupProductsQuery = CWquerySelectTaxGroupProducts($_ENV["request.cwpage"]["currentRecord"],true);
// QUERY: get all active tax groups  
$taxGroupsQuery = CWquerySelectTaxGroups(0);
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"]= "Manage ".$_ENV["application.cw"]["taxSystemLabel"]." Group";
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"]= "Manage ".$_ENV["application.cw"]["taxSystemLabel"]." Group: ".$_ENV["request.cwpage"]["groupName"];
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = "Manage products within this ".$_ENV["application.cw"]["taxSystemLabel"]." Group";
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = "tax-groups.php";
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"]=1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"]=1;
// START OUTPUT ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		
		<title><?php echo $_ENV["application.cw"]["companyName"]; ?> : <?php echo $_ENV["request.cwpage"]["title"]; ?></title>
		
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php
	include("cwadminapp/inc/cw-inc-admin-scripts.php");        
	
	?>	
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
		echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]).'</h1>';
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
						<?php // LINKS FOR VIEW OPTIONS ?>
						<div class="CWadminControlWrap">
							<strong>
							<p><a href="tax-group-details.php?tax_group_id=<?php echo $_ENV["request.cwpage"]["currentRecord"]; ?>">Tax Rates</a></p>
							</strong>
						</div>
<?php
	// /END LINKS FOR VIEW OPTIONS 
	// if a valid record is not found 
	if(!($taxGroupQuery['totalRows'] ==1)) {
			
	?>		
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>Invalid <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> group id. Please return to the <a href="tax-groups.php"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group Listing</a> and choose a valid <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> group.</p>
<?php
							// if a record is found 
	} else {
	 						// /////// 
							// UPDATE TAX RATES 
							// /////// 
							// check for existing records ?>
							<p>&nbsp;</p>
							<h3>Associated Products</h3>
<?php
		if(!($taxGroupProductsQuery['totalRows'])) {
			
		?>	
								<p>&nbsp;</p>
								<p>There are currently no products assigned to this <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> group</p>
		<?php
								// if existing records found 
		} else {
			
		?>						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" name="recordForm" id="recordForm" method="post" class="CWobserve">
									<table class="CWsort CWstripe wide" summary="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>">
										<thead>
										<tr class="sortRow">
											<th class="product_name">Product Name</th>
											<th class="noSort"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?> Group</th>
										</tr>
										</thead>
										<tbody>
	<?php
			for($i =0; $i < $taxGroupProductsQuery['totalRows'];$i++) {
	?>									<tr>
											<td><a href="product-details.php?productid=<?php echo $taxGroupProductsQuery['product_id'][$i]; ?>" class="detailsLink" title="Edit Product Details: <?php echo $taxGroupProductsQuery['product_name'][$i]; ?>"><?php echo $taxGroupProductsQuery['product_name'][$i]; ?></a></td>
											<td>
												<select name="tax_group_id<?php echo $i; ?>">
													<option value="0">No <?php echo  $_ENV["application.cw"]["taxSystemLabel"]; ?></option>
													<?php
                                                    for($j=0 ; $j < $taxGroupsQuery['totalRows'];$j++) {
														?>
														<option value="<?php  echo  $taxGroupsQuery['tax_group_id'][$j]?>"<?php if($taxGroupsQuery['tax_group_id'][$j] == $_ENV["request.cwpage"]["currentRecord"]) {?> selected="selected"<?php }?>><?php echo $taxGroupsQuery['tax_group_name'][$j]; ?></option>
													<?php
													}
													?>
												</select>
												<input type="hidden" name="product_id<?php echo $i; ?>" value="<?php echo $taxGroupProductsQuery['product_id'][$i]; ?>" >
												<input name="recordIDlist[<?php echo $i; ?>]" type="hidden" value="<?php echo $taxGroupProductsQuery['product_id'][$j]; ?>">
											</td>
										</tr>
									<?php
										
			}
									?>	</tbody>
									</table>
									<input name="SubmitUpdate" type="submit" class="submitButton" id="UpdateProducts" value="Save Changes">
									<input type="hidden" value="<?php echo $taxGroupProductsQuery['totalRows']; ?>" name="recordCounter">
								</form>
						<?php
						
		}
		// /end check for existing records 
		// /////// 
		// /END UPDATE TAX RATES 
		// /////// 
	}
	// /end valid record ?>
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