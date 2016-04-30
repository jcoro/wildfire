<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: categories-secondary.php
File Date: 2012-05-01
Description: Manage second-level categories in Cartweaver
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"]= CWauth('manager,merchant,developer');
// PAGE PARAMS 
// default values for sort / active or archived
if(!(isset($_GET['sortby']))) $_GET['sortby']="secondary_sort";
if(!(isset($_GET['sortdir']))) $_GET['sortdir']='asc';
if(!(isset($_GET['view']))) $_GET['view']='active';
// default form values 
if(!(isset($_POST['secondary_name']))) $_POST['secondary_name']='';
if(!(isset($_POST['secondary_description']))) $_POST['secondary_description']='';
if(!(isset($_POST['secondary_sort']))) $_POST['secondary_sort']=0;
// default value for order type label
if(!(isset($_ENV["request.cwpage"]["orderType"]))) $_ENV["request.cwpage"]["orderType"]='All';
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,view,userconfirm,useralert,clickadd");
// create the base url for sorting out of serialized url variables
$_ENV["request.cwpage"]["baseURL"]=CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// PARAMS FOR CATEGORY LABELS 
if(!(isset($_ENV["application.cw"]["adminLabelSecondary"]))) $_ENV["application.cw"]["adminLabelSecondary"]='category';
if(!(isset($_ENV["application.cw"]["adminLabelSecondaries"]))) $_ENV["application.cw"]["adminLabelSecondaries"]='categories';
$_ENV["request.cwpage"]["catText"]=$_ENV["application.cw"]["adminLabelSecondary"];
$_ENV["request.cwpage"]["catsText"]=$_ENV["application.cw"]["adminLabelSecondaries"];
// ACTIVE VS ARCHIVED 
if (strpos(strtolower($_GET['view']), "arch") !== false) {
	$_ENV["request.cwpage"]["viewType"]='Archived';
	$_ENV["request.cwpage"]["subHead"]='Archived '.strtolower($_ENV["request.cwpage"]["catsText"]).' are not shown in the store';
} else {
	$_ENV["request.cwpage"]["viewType"]='Active';
	$_ENV["request.cwpage"]["subHead"]='Manage active '.strtolower($_ENV["request.cwpage"]["catsText"]).' or add a new '.strtolower($_ENV["request.cwpage"]["catText"]);
}
// QUERIES: get categories 
if(strpos(strtolower($_ENV["request.cwpage"]["viewType"]), "arch") !== false) {
	$_ENV["request.cwpage"]["catsArchived"]=1;
} else {
	$_ENV["request.cwpage"]["catsArchived"]=0;
}
$catsQuery=CWquerySelectStatusSecondaryCategories($_ENV["request.cwpage"]["catsArchived"],true);
// /////// 
// ADD NEW CATEGORY 
// /////// 
if(isset($_POST['AddCat']) && $_ENV["request.cwpage"]["catsArchived"]==0) {
	// QUERY: insert new category (name, order, archived, description)
	// this query returns the category id, or an error like '0-fieldname' 
	$newCatID = CWqueryInsertSecondaryCategory(
					trim($_POST['secondary_name']),
					$_POST['secondary_sort'],
					0,
					$_POST['secondary_description']);
	// if no error returned from insert query 
	if(!(substr($newCatID,0,2) == '0-')) {
		CWpageMessage("confirm",$_ENV["request.cwpage"]["catText"]." '".$_POST['secondary_name']."' Added");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&sortby='.$_GET['sortby'].'&sortdir='.$_GET['sortdir'].'&clickadd=1');
		exit;
	} else {
		$newvarforlist=explode('-',$newCatID);
		$dupField = $newvarforlist[count($newvarforlist)-1];
		CWpageMessage("alert","Error: ".$dupField." already exists");
		$_GET['clickadd']=1;
	}
	// update complete: return to page showing message 
	// if we have an insert error, show message, do not insert 
	// /END duplicate/error check 
}
// /////// 
// /END ADD NEW CATEGORY 
// /////// 
// /////// 
// UPDATE / DELETE CATEGORIES 
// /////// 
if(isset($_POST['UpdateCats'])) {
	if(!(isset($_POST['deleteCategory']))) $_POST['deleteCategory']=array();
	$loopCt = 0;
	$updateCt = 0;
	$deleteCt = 0;
	$archiveCt = 0;
	$activeCt = 0;
	foreach ($_POST['catIDlist'] as $catKey => $ID) {
		// DELETE CATS 
		// if the category ID is marked for deletion 
		if(in_array($_POST['secondary_id'.$loopCt], $_POST['deleteCategory'])) {
			// QUERY: delete category (category id) 
			$deleteCat=CWqueryDeleteSecondaryCategory($ID);
			$deleteCt++;
		} else {
			// if not deleting, update 
			// UPDATE CATS 
			// param for checkbox values 
			if(!isset($_POST['secondary_archive'.$loopCt])) $_POST['secondary_archive'.$loopCt] = $_ENV["request.cwpage"]["catsArchived"];
			// QUERY: update category record (id, name, sort, archive, description) 
			$updateCatID=CWqueryUpdateSecondaryCategory(
							$_POST['secondary_id'.$loopCt],
							$_POST["secondary_name".$loopCt],
							$_POST['secondary_sort'.$loopCt],
							$_POST['secondary_archive'.$loopCt],
							$_POST['secondary_description'.$loopCt]
							);	
			// query checks for duplicate fields 
			if(substr($updateCatID,0,2) == '0-') {	
				$newvarFordupField=explode('-',$updateCatID);
				$dupField=$newvarFordupField[count($newvarFordupField)-1];
				$errorName=$_POST['secondary_name'.$loopCt];
				CWpageMessage("alert","Error: Name ".$errorName.' already exists');
			} else {	
				// update complete: continue processing 
				if($_POST['secondary_archive'.$loopCt] ==1 && $_ENV["request.cwpage"]["catsArchived"] ==0)
					$archiveCt++;
				elseif($_POST['secondary_archive'.$loopCt] == 0 && $_ENV["request.cwpage"]["catsArchived"]==1)
					$activeCt++;
				else
					$updateCt++;		
				// /END if deleting or updating 
			}
		// /END duplicate check 
		}
		$loopCt++;
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars('userconfirm,useralert');
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"]= CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// return to page as submitted, clearing form scope 
	CWpageMessage("confirm","Changes Saved");
	$_ENV["request.cwpage"]["userAlertText"] = '';
	if($archiveCt > 0) {
		$_ENV["request.cwpage"]["userAlertText"] .= $archiveCt." ";
		if($archiveCt == 1)
			$_ENV["request.cwpage"]["userAlertText"] .= $_ENV["request.cwpage"]["catText"].' Archived';
		else
			$_ENV["request.cwpage"]["userAlertText"] .= $_ENV["request.cwpage"]["catsText"].' Archived';
	}
	elseif($activeCt >0) {
		$_ENV["request.cwpage"]["userAlertText"] .= $activeCt." ";
		if($activeCt == 1)
			$_ENV["request.cwpage"]["userAlertText"] .= $_ENV["request.cwpage"]["catText"].' Activated';
		else
			$_ENV["request.cwpage"]["userAlertText"] .= $_ENV["request.cwpage"]["catsText"].' Activated';
	}
	if($deleteCt > 0) {
		if($archiveCt > 0 || $activeCt > 0)
			$_ENV["request.cwpage"]["userAlertText"] .= '<br>';
		$_ENV["request.cwpage"]["userAlertText"] .= $deleteCt." ";
		if($deleteCt == 1)
			$_ENV["request.cwpage"]["userAlertText"].=$_ENV["request.cwpage"]["catText"].' Deleted';
		else
			$_ENV["request.cwpage"]["userAlertText"].=$_ENV["request.cwpage"]["catsText"].' Deleted';
	}
	CWpageMessage("alert",$_ENV["request.cwpage"]["userAlertText"]);
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_ENV["request.cwpage"]["relocateURL"].'&userconfirm='.CWurlSafe($_ENV["request.cwpage"]["userConfirm"]).'&useralert='.CWurlSafe($_ENV["request.cwpage"]["userAlert"]));
	exit;
}
// /////// 
// /END UPDATE / DELETE CATEGORIES 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"]=$_ENV["request.cwpage"]["catsText"];
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"]=$_ENV["request.cwpage"]["catsText"].' Management: '.$_ENV["request.cwpage"]["viewType"];
// Page Subheading (instructions) <h2> 
$_POST['headig2']=$_ENV["request.cwpage"]["subHead"];
// current menu marker 
$_ENV["request.cwpage"]["currentNav"]=$_ENV["request.cw"]["thisPage"];	
if(isset($_GET['clickadd'])) {
	$_ENV["request.cwpage"]["currentNav"]=$_ENV["request.cwpage"]["currentNav"] . '?clickadd=1';
}
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"]=1;	
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"]=1;
// START OUTPUT ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo $_ENV["application.cw"]["companyName"];?> : <?php  echo $_ENV["request.cwpage"]["title"];?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"];?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php 
include("cwadminapp/inc/cw-inc-admin-scripts.php");
// text editor javascript 
if($_ENV["application.cw"]["adminEditorEnabled"]) {
	include("cwadminapp/inc/cw-inc-admin-script-editor.php");
}
// PAGE JAVASCRIPT ?>
		<script type="text/javascript">
			function confirmDelete(boxID,prodCt) {
			// if this cat has products
			if (prodCt > 0) {
				if (prodCt > 1) {var prodWord = 'products'}else{var prodWord = 'product'};
				var confirmBox = '#'+ boxID;
					// if the box is checked
					if( jQuery(confirmBox).is(':checked') ) {
					clickConfirm = confirm("This<?php echo strtolower($_ENV["request.cwpage"]["catText"]);?> will be unassigned for " + prodCt + " " + prodWord + ".\nContinue?");
					// if confirm is returned false
					if(!clickConfirm) {
						jQuery(confirmBox).prop('checked','');
					};
				};
				// end if checked
			};
			// end if prodct
		};
		// end function
		</script>
		<script type="text/javascript">
		jQuery(document).ready(function() {
		// description edit
		jQuery('span.descripEdit').hide();
		// show-editor link
		jQuery('a.descripEditLink, span.descripText').click(function() {
			jQuery(this).hide().siblings('span.descripText, a.descripEditLink').hide();
			jQuery(this).siblings('span.descripEdit').show();
			return false;
		});
		// add new show-hide
		jQuery('form#catAddForm').hide();
		jQuery('a#showCatFormLink').click(function() {
			jQuery(this).hide();
			jQuery('form#catAddForm').show();
			jQuery('#secondary_nameAdd').focus();
			return false;
		});
		// auto-click the link if adding
<?php
if(isset($_GET['clickadd'])) {
			?>jQuery('a#showCatFormLink').click();
<?php
}
?>
		});
		</script>
		<?php // /END PAGE JAVASCRIPT ?>
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

?>	</div>
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
					?>
                    <h1><?php echo trim( $_ENV["request.cwpage"]["heading1"]);?></h1><?php 

}
if(strlen($_ENV["request.cwpage"]["heading2"])) {
?>
					<h2><?php echo trim($_ENV["request.cwpage"]["heading2"]); ?></h2>
<?php
}
?><!-- Admin Alert - message shown to user -->
<?php
include("cwadminapp/inc/cw-inc-admin-alerts.php");    
					?><!-- Page Content Area -->
					<div id="CWadminContent">
						<!-- //// PAGE CONTENT ////  -->
<?php
						// FORM 
						// LINKS FOR VIEW TYPE ?>
						<div class="CWadminControlWrap">
							<strong>
				<?php
if($_GET['view']=='arch') {		
					?><a href="<?php echo $_SERVER['SCRIPT_NAME'];?>">View Active</a>
<?php
} else {
?>
                            	<a href="<?php echo $_ENV["request.cw"]["thisPage"];?>?view=arch">View Archived</a>
<?php
	// link for add-new form 
	if($_ENV["request.cwpage"]["catsArchived"]==0) {
	
								?>	&nbsp;&nbsp;<a class="CWbuttonLink" id="showCatFormLink" href="#">Add New <?php echo $_ENV["request.cwpage"]["catText"];?></a>
								<?php
	}
}?>
							</strong>
						</div>
<?php
// /END LINKS FOR VIEW TYPE 
// /////// 
// ADD NEW CATEGORY
// /////// 
if($_ENV["request.cwpage"]["catsArchived"]===0) {
							?><form action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>" class="CWvalidate CWobserve" name="catAddForm" id="catAddForm" method="post">
								<p>&nbsp;</p>
								<h3>Add New <?php echo $_ENV["request.cwpage"]["catText"];?></h3>
								<table class="CWinfoTable wider">
									<thead>
									<tr>
										<th class="secondary_name"><?php echo $_ENV["request.cwpage"]["catText"];?> Name</th>
										<th width="485" class="secondary_description">Description</th>
										<th width="55" class="secondary_sort">Order</th>
									</tr>
									</thead>
								
									<tbody>
									<tr>
										<?php // name ?>
										<td>
											<input name="secondary_name" id="secondary_nameAdd" type="text" size="17"  class="required" value="<?php echo $_POST['secondary_name'];?>" title="<?php echo $_ENV["request.cwpage"]["catText"];?> Name is required">
											<input name="AddCat" type="submit" class="CWformButton" id="AddCat" value="Save New <?php echo $_ENV["request.cwpage"]["catText"];?>">
										</td>
										<?php // description ?>
										<td class="noLink noHover">
											<?php // show text ?>
											<textarea name="secondary_description" class="textEdit" cols="45" rows="3"><?php echo $_POST['secondary_description'];?></textarea>
										</td>
										<?php // order ?>
										<td><input name="secondary_sort" type="text" size="4" maxlength="7" class="required sort" title="Sort Order is required" value="<?php echo $_POST['secondary_sort'];?>" onkeyup="extractNumeric(this,2,true)" onblur="checkValue(this)"></td>
									</tr>
									</tbody>
									
								</table>
							</form>
							<p>&nbsp;</p>
<?php
}
// /////// 
// /END ADD NEW CATEGORY
// /////// 
// /////// 
// EDIT CATEGORIES 
// /////// ?>
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"];?>&view=<?php echo $_GET['view'];?>" name="catForm" id="catForm" method="post" class="CWobserve">
							<h3>Manage <?php echo $_ENV["request.cwpage"]["catText"];?> Details</h3>
<?php
// save changes / submit button 
if($catsQuery['totalRows']) {
?>			
								<div class="CWadminControlWrap">
									<input name="UpdateCats" type="submit" class="CWformButton" id="UpdateCats" value="Save Changes">
									<div style="clear:right;"></div>
								</div>
<?php
}
// /END submit button 
// if no records found, show message 
if(!($catsQuery['totalRows'])) {
                    		?>	<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>No <?php echo strtolower($_ENV["request.cwpage"]["viewType"])." ".strtolower($_ENV["request.cwpage"]["catsText"]);?> available.</strong> <br><br></p>
<?php
} else {
	// SHOW CATEGORIES ?>	<table class="CWsort CWstripe" summary="<?php echo $_ENV["request.cwpage"]["baseURL"];
?>">
									<thead>
									<tr class="sortRow">
										<th class="secondary_name"><?php echo $_ENV["request.cwpage"]["catText"];?> Name</th>
										<th width="24" class="secondary_id">ID</th>
										<th width="485" class="secondary_description">Description</th>
										<th width="55" class="secondary_sort">Order</th>
										<th width="65" class="catProdCount">Products</th>
<?php	
	// view category link 
	if(isset($_ENV["application.cw"]["adminProductLinksEnabled"]) && $_ENV["application.cw"]["adminProductLinksEnabled"]) {
										?>	<th class="noSort" width="50">View</th>
										
<?php 
	}
?>	
										<th width="55" class="noSort"><?php
	if(strstr(strtolower($_ENV["request.cwpage"]["viewType"]), "arch") !== false) {
?>Activate<?php 
	} else {
?>Archive<?php
	}
										?></th>
										<th width="55" class="noSort">Delete</th>
									</tr>
									</thead>
									<tbody>
									<?php
	// OUTPUT THE CATEGORIES 
	for($i=0; $i <$catsQuery['totalRows'];$i++) {
		// output the row ?>
									<tr>
										<?php // Category Name ?>
										<td><input name="secondary_name<?php echo $i; ?>" type="text" size="17"  class="required" value="<?php echo $catsQuery['secondary_name'][$i];?>" title="<?php echo $_ENV["request.cwpage"]["catText"];?> Name is required" onblur="checkValue(this)"> </td>
                                        <?php // Category ID ?>
										<td>
											<?php echo $catsQuery['secondary_id'][$i]; ?>
											<input name="secondary_id<?php echo $i; ?>" type="hidden" value="<?php echo $catsQuery['secondary_id'][$i]; ?>">
											<input name="catIDlist[<?php echo $i; ?>]" type="hidden" value="<?php echo $catsQuery['secondary_id'][$i]; ?>">
										</td>
										<?php // Description ?>
										<td>
<?php
		// show text editor 
		if(isset($_ENV["application.cw"]["adminEditorCategoryDescrip"])) {
											?>	<a class="descripEditLink" href="#" title="Edit"><img src="img/cw-edit.gif" alt="Edit"></a>
												<span class="descripText"><?php echo $catsQuery['secondary_description'][$i]; ?></span>
												<span class="descripEdit">
													<textarea name="secondary_description<?php echo $i; ?>" class="textEdit" cols="45" rows="3"><?php echo $catsQuery['secondary_description'][$i]; ?></textarea>
												</span>
										<?php
		// show text input 
		} else {?>
										
                                            <input type="text" name="secondary_description<?php echo $i; ?>" size="30" value="<?php echo $catsQuery['secondary_description'][$i];?>">
<?php
		}
?>
										</td>
										<?php // Sort Order ?>
										<td><input name="secondary_sort<?php echo $i; ?>" type="text" size="4" maxlength="7" class="required" title="Sort Order is required" value="<?php echo $catsQuery['secondary_sort'][$i];?>" onkeyup="extractNumeric(this,2,true)" onblur="checkValue(this)"></td>
                                        <?php // Products ?>
										<td style="text-align:center;"><?php echo $catsQuery['catProdCount'][$i]; 
		if( $catsQuery['catProdCount'][$i] > 0) {?><br><br><a href="products.php?searchSC=<?php echo $catsQuery['secondary_id'][$i];?>&search=1" title="Manage products in this category"><img alt="Manage products in this category" src="img/cw-product-edit.png"></a><?php
		}?></td>
										
                                       <?php
		// view category link 
		if(isset($_ENV["application.cw"]["adminProductLinksEnabled"]) && $_ENV["application.cw"]["adminProductLinksEnabled"]) {	
                                   	?>	<td style="text-align:center;"><a href="<?php echo  $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["request.cwpage"]["urlResults"];?>?secondary=<?php echo $catsQuery['secondary_id'][$i];?>" class="columnLink" title="View on Web: <?php echo $catsQuery['secondary_name'][$i];?>" rel="external"><img src="img/cw-product-view.png" alt="View on Web: <?php echo $catsQuery['secondary_name'][$i];?>"></a></td>
										
<?php
		}
		// Activate/Archive ?>
										<td style="text-align:center"><input type="checkbox" name="secondary_archive<?php echo $i; ?>" class="formCheckbox radioGroup" rel="group<?php echo $i?>" value="<?php if($catsQuery['secondary_archive'][$i] ==0) {?>1<?php } else {?>0<?php }?>"> </td>
                                        <?php // Delete ?>
										<td style="text-align:center"><input type="checkbox" name="deleteCategory[<?php echo $i; ?>]" id="confirmBox<?php echo $catsQuery['secondary_id'][$i];?>" value="<?php echo $catsQuery['secondary_id'][$i]; ?>" class="formCheckbox radioGroup" rel="group<?php echo $i; ?>" onclick="confirmDelete('confirmBox<?php echo $catsQuery['secondary_id'][$i]; ?>',<?php echo $catsQuery['totalRows']; ?>)"></td>
									</tr>
<?php
	}
?>
								</table>
								<?php 
	// /end categories table 
	// save changes / submit button 
	if($catsQuery['totalRows']) {										
                                		?>
									<div class="CWadminControlWrap">
										<input name="UpdateCats" type="submit" class="CWformButton" id="UpdateCats2" value="Save Changes">
										<div style="clear:right;"></div>
									</div>
                                    <?php
	}
	// /END submit button 
}
// /end if records found ?>
						</form>
<?php
						// /////// 
						// /END EDIT CATEGORIES 
						// /////// ?>
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
