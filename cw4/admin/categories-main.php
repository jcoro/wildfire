<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: categories-main.php
File Date: 2012-05-22
Description: Manage top-level categories in Cartweaver
==========================================================
*/
// global queries
require_once("cwadminapp/func/cw-func-adminqueries.php");
// global functions
require_once("cwadminapp/func/cw-func-admin.php");
// GLOBAL INCLUDES 
require_once("Application.php");
// PAGE PERMISSIONS 
$_ENV["request.cwpage"]["accessLevel"] = CWauth("manager,merchant,developer");
// PAGE PARAMS 
// default values for sort / active or archived
if(!isset($_GET['sortby'])) { $_GET['sortby'] = "category_sort"; }
if(!isset($_GET['sortdir'])) { $_GET['sortdir'] = "asc"; }
if(!isset($_GET['view'])) { $_GET['view'] = "active"; }
// default form values 
if(!isset($_POST['category_name'])) { $_POST['category_name'] = ""; }
if(!isset($_POST['category_description'])) { $_POST['category_description'] = ""; }
if(!isset($_POST['category_sort'])) { $_POST['category_sort'] = 0; }
// BASE URL 
// get the vars to keep by omitting the ones we don't want repeated 
$varsToKeep = CWremoveUrlVars("sortby,sortdir,view,userconfirm,useralert,clickadd");
// create the base url for sorting out of serialized url variables
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
//$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep);
// PARAMS FOR CATEGORY LABELS 
if(!isset($_ENV["application.cw"]["adminLabelCategory"]) || $_ENV["application.cw"]["adminLabelCategory"] == '') { $_ENV["application.cw"]["adminLabelCategory"] = "category"; }
if(!isset($_ENV["application.cw"]["adminLabelCategories"]) || $_ENV["application.cw"]["adminLabelCategories"] == '') { $_ENV["application.cw"]["adminLabelCategories"] = "categories"; }
$_ENV["request.cwpage"]["catText"] = $_ENV["application.cw"]["adminLabelCategory"];
$_ENV["request.cwpage"]["catsText"] = $_ENV["application.cw"]["adminLabelCategories"];
// ACTIVE VS ARCHIVED 
if(preg_match("/arch/",$_GET['view'])) {
	$_ENV["request.cwpage"]["viewType"] = 'Archived';
	$_ENV["request.cwpage"]["subHead"] = 'Archived '.strtolower($_ENV["request.cwpage"]["catsText"]).' are not shown in the store';
} else {
	$_ENV["request.cwpage"]["viewType"] = 'Active';
	$_ENV["request.cwpage"]["subHead"] = 'Manage active '.strtolower($_ENV["request.cwpage"]["catsText"]).' or add a new '.strtolower($_ENV["request.cwpage"]["catsText"]).'';
}
// QUERIES: get categories 
if(preg_match("/Arch/", $_ENV["request.cwpage"]["viewType"]) || strstr('Arch',$_ENV["request.cwpage"]["viewType"]) !== false) {
	$_ENV["request.cwpage"]["catsArchived"] = 1;
} else {
	$_ENV["request.cwpage"]["catsArchived"] = 0;
}
$catsQuery = CWquerySelectStatusCategories($_ENV["request.cwpage"]["catsArchived"],true);
// /////// 
// ADD NEW CATEGORY 
// /////// 
if((isset($_POST['AddCat'])) && $_ENV["request.cwpage"]["catsArchived"] == 0) {
	// QUERY: insert new category (name, order, archived, description)
	// this query returns the category id, or an error like '0-fieldname' 
	$newCatID = CWqueryInsertCategory(
					trim($_POST['category_name']),
					$_POST['category_sort'],
					0,
					$_POST['category_description']);
	// if no error returned from insert query 
	if(substr($newCatID,0,2) != '0-') {
		CWpageMessage("confirm",$_ENV["request.cwpage"]["catText"]." '".$_POST['category_name']."' Added");
		header("Location: ".$_ENV["request.cwpage"]["baseURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&sortby=".$_GET['sortby']."&sortdir=".$_GET['sortdir']."&clickadd=1");
		exit;
	} else {
		$newcat = explode('-', $newCatID);
		$newcat_last = $newcat[count($newcat) - 1];
		$dupField = $newcat_last;
		CWpageMessage('alert','Error: '.$dupField.' already exists');
		$_GET['clickadd'] = 1; 
	}
	// /END duplicate/error check 
}
// /////// 
// /END ADD NEW CATEGORY 
// /////// 
// /////// 
// UPDATE / DELETE CATEGORIES 
// /////// 
if(isset($_POST['UpdateCats'])) {
	if(!isset($_POST['deleteCategory'])) { $_POST['deleteCategory'] = ""; }
	$loopCt = 0;
	$updateCt = 0;
	$deleteCt = 0;
	$archiveCt = 0;
	$activeCt = 0;
	// loop category ids, handle each one as needed 
	foreach ($_POST['catIDlist'] as $catKey => $ID) {
		// DELETE CATS 
		// if the category ID is marked for deletion 
		if(ListFind($_POST['deleteCategory'],$_POST['category_id'.$loopCt])) {
			// QUERY: delete category (category id) 
			$deleteCat = CWqueryDeleteCategory($ID);
			$deleteCt++;
		} else {
			// UPDATE CATS 
			// param for checkbox values 
			if(!isset($_POST['category_archive'.$loopCt])) { $_POST['category_archive'.$loopCt] = $_ENV["request.cwpage"]["catsArchived"]; }
			// verify numeric sort order 
			if(!isset($_POST['category_sort'.$loopCt]) || !is_numeric($_POST['category_sort'.$loopCt])) { $_POST['category_sort'.$loopCt] = 0; }
			// QUERY: update category record (id, name, sort, archive, description) 
			$updateCatID = CWqueryUpdateCategory(
								$_POST['category_id'.$loopCt],
								$_POST['category_name'.$loopCt],
								$_POST['category_sort'.$loopCt],
								$_POST['category_archive'.$loopCt],
								$_POST['category_description'.$loopCt]);
			if((substr($updateCatID,0,2)) == '0-') {
				$updateCat = explode('-',$updateCatID);
				$updateCat_last = $updateCat[count($updateCat) - 1];
				$dupField = $updateCat_last;
				$errorName = $_POST['category_name'.$loopCt];
				CWpageMessage("alert","Error: Name '".$errorName."' already exists");
			} else {
				// update complete: continue processing 
				if($_POST['category_archive'.$loopCt] == 1 && $_ENV["request.cwpage"]["catsArchived"] == 0) {
					$archiveCt++;
				}
				elseif($_POST['category_archive'.$loopCt] == 0 && $_ENV["request.cwpage"]["catsArchived"] == 1) {
					$archiveCt++;
				} else {
					$updateCt++;
				}
			}
			// /end if deleting or updating 
		}
		// /END duplicate check 
		$loopCt++;
	}
	// get the vars to keep by omitting the ones we don't want repeated 
	$varsToKeep = CWremoveUrlVars("userconfirm,useralert");
	// set up the base url 
	$_ENV["request.cwpage"]["relocateURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
	// return to page as submitted, clearing form scope 
	CWpageMessage("confirm","Changes Saved");
	$useralertText = "";
	if($archiveCt > 0) {
		$useralertText .= $archiveCt." ";
		if($archiveCt == 1) {
			$useralertText.= $_ENV["request.cwpage"]["catText"];
		} else {
			$useralertText.= $_ENV["request.cwpage"]["catsText"];
		}
		$useralertText .= " Archived"; 
	}
	elseif ($activeCt > 0) {
		$useralertText.= $activeCt." ";
		if($activeCt == 1) {
			$useralertText.= $_ENV["request.cwpage"]["catText"];
		} else {
			$useralertText.= $_ENV["request.cwpage"]["catsText"];
		}
		$useralertText .= " Activated"; 
	}
	if($deleteCt > 0) {
		if ($archiveCt > 0 || $activeCt > 0) { '<br>'; }
		$useralertText .= $deleteCt." ";
		if($deleteCt == 1) {
			$useralertText .= $_ENV["request.cwpage"]["catText"];
		} else {
			$useralertText.= $_ENV["request.cwpage"]["catsText"];
		}
		$useralertText .= " Deleted";
	}
	CWpageMessage("alert",$useralertText);
	if (!isset($_ENV["request.cwpage"]["userConfirm"])) $_ENV["request.cwpage"]["userConfirm"] = "";
	if (!isset($_ENV["request.cwpage"]["userAlert"])) $_ENV["request.cwpage"]["userAlert"] = "";
	header("Location: ".$_ENV["request.cwpage"]["relocateURL"]."&userconfirm=".CWurlSafe($_ENV["request.cwpage"]["userConfirm"])."&useralert=".CWurlSafe($_ENV["request.cwpage"]["userAlert"])."");
	exit;
}
// /////// 
// /END UPDATE / DELETE CATEGORIES 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title
//<title>
$_ENV["request.cwpage"]["title"] = $_ENV["request.cwpage"]["catsText"];
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"] = $_ENV["request.cwpage"]["catsText"].' Management: '. $_ENV["request.cwpage"]["viewType"];
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"] = $_ENV["request.cwpage"]["subHead"];
// current menu marker 
$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cw"]["thisPage"];
if(isset($_GET['clickadd'])) {
	$_ENV["request.cwpage"]["currentNav"] = $_ENV["request.cwpage"]["currentNav"] . '?clickadd=1';
}
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
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"]; ?>/cw-admin-theme.css" rel="stylesheet" type="text/css">
		<!-- admin javascript -->
<?php 	include("cwadminapp/inc/cw-inc-admin-scripts.php"); 
		// text editor javascript 
		if($_ENV["application.cw"]["adminEditorEnabled"] && $_ENV["application.cw"]["adminEditorCategoryDescrip"]) {
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
			if( jQuery(confirmBox).prop('checked')==true) {
				clickConfirm = confirm("This <?php echo strtolower($_ENV["request.cwpage"]["catText"]); ?> will be unassigned for " + prodCt + " " + prodWord + ".\nContinue?");
				// if confirm is returned false
				if(!clickConfirm) {
					jQuery(confirmBox).prop('checked','');
				};
			};
			// end if checked
		};
		// end if prodct
		};
		// end script
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
				jQuery('#category_nameAdd').focus();
				return false;
			});
			// auto-click the link if adding
			<?php
if(isset($_GET['clickadd'])) {
				?>
				jQuery('a#showCatFormLink').click();
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
						<?php // LINKS FOR VIEW TYPE  ?>
						<div class="CWadminControlWrap">
							<strong>
<?php
if($_GET['view'] =='arch') { ?>
								<a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>">View Active</a>
<?php                                
} else { ?>
								<a href="<?php echo $_ENV["request.cw"]["thisPage"]; ?>?view=arch">View Archived</a>
<?php   
	// link for add-new form 
	if($_ENV["request.cwpage"]["catsArchived"] == 0) { ?>
									&nbsp;&nbsp;<a class="CWbuttonLink" id="showCatFormLink" href="#">Add New <?php echo $_ENV["request.cwpage"]["catText"]; ?></a>
<?php 
	}
}
?>                            
							</strong>
						</div>
<?php 
// /END LINKS FOR VIEW TYPE 
// /////// 
// ADD NEW CATEGORY
// /////// 
if($_ENV["request.cwpage"]["catsArchived"] == 0) {
	// FORM ?>
							<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>" class="CWvalidate CWobserve" name="catAddForm" id="catAddForm" method="post">
								<p>&nbsp;</p>
								<h3>Add New <?php echo $_ENV["request.cwpage"]["catText"]; ?></h3>
								<table class="CWinfoTable wider">
									<thead>
									<tr>
										<th class="category_name"><?php echo $_ENV["request.cwpage"]["catText"]; ?> Name</th>
										<th width="485" class="category_description">Description</th>
										<th width="55" class="category_sort">Order</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<?php // name ?>
										<td>
											<input name="category_name" id="category_nameAdd" type="text" size="17"  class="required" value="<?php echo $_POST['category_name']; ?>" title="<?php echo $_ENV["request.cwpage"]["catText"]; ?> Name is required">
											<input name="AddCat" type="submit" class="CWformButton" id="AddCat" value="Save New <?php echo $_ENV["request.cwpage"]["catText"]; ?>">
										</td>
										<?php // description ?>
										<td class="noLink noHover">
											<?php // show text ?>
											<textarea name="category_description" class="textEdit" cols="45" rows="3"><?php echo $_POST['category_description']; ?></textarea>
										</td>
										<?php // order ?>
										<td><input name="category_sort" type="text" size="4" maxlength="7" class="required sort" title="Sort Order is required" value="<?php echo $_POST['category_sort']; ?>" onkeyup="extractNumeric(this,2,true)" onblur="checkValue(this)"></td>
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
						<form action="<?php echo $_ENV["request.cwpage"]["baseURL"]; ?>&view=<?php echo $_GET['view'] ?>" name="catForm" id="catForm" method="post" class="CWobserve">
							<h3>Manage <?php echo $_ENV["request.cwpage"]["catText"]; ?> Details</h3>
<?php
// save changes / submit button 
if($catsQuery['totalRows']) { ?>
								<div class="CWadminControlWrap">
									<input name="UpdateCats" type="submit" class="CWformButton" id="UpdateCats" value="Save Changes">
									<div style="clear:right;"></div>
<?php
}
?>							
								</div>
<?php
// /END submit button 
// if no records found, show message 
if(!$catsQuery['totalRows']) { ?>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p>&nbsp;</p>
								<p><strong>No <?php echo strtolower($_ENV["request.cwpage"]["viewType"])."  ".strtolower($_ENV["request.cwpage"]["catsText"]); ?> available.</strong> <br><br></p>
<?php
} else {
	// SHOW CATEGORIES 
	if($catsQuery['totalRows']) {
?>
									<table class="CWsort CWstripe" summary="<?php echo strtolower($_ENV["request.cwpage"]["baseURL"]); ?>">
										<thead>
										<tr class="sortRow">
											<th class="category_name"><?php echo strtolower($_ENV["request.cwpage"]["catText"]); ?> Name</th>
											<th width="24" class="category_id">ID</th>
											<th width="485" class="category_description">Description</th>
											<th width="55" class="category_sort">Order</th>
											<th width="65" class="catProdCount">Products</th>
<?php
		// view category link 
		if((isset($_ENV["application.cw"]["adminProductLinksEnabled"])) && $_ENV["application.cw"]["adminProductLinksEnabled"]) { ?>
												<th class="noSort" width="50">View</th>
<?php
		}
?>                                            
											<th width="55"><?php
		if(strpos(strtolower($_ENV["request.cwpage"]["viewType"]), "arch") !== false) { echo "Activate"; }
		else { echo "Archive"; }
?></th>
											<th width="55" class="noSort">Delete</th>
										</tr>
										</thead>
                                        <tbody>
<?php
		// OUTPUT THE CATEGORIES 
		for($i=0; $i<$catsQuery['totalRows']; $i++) { 
			// output the row ?>
										<tr>
											<?php // Category Name ?>
											<td>
                                            <input name="category_name<?php echo $i; ?>" type="text" size="17"  class="required" value="<?php echo $catsQuery['category_name'][$i]; ?>" title="<?php echo $_ENV["request.cwpage"]["catText"]; ?> Name is required" onblur="checkValue(this);"> 
                                            </td>
											<?php // Category ID ?>
											<td><?php 
			echo $catsQuery['category_id'][$i];
?>
												<input name="category_id<?php echo $i; ?>" type="hidden" value="<?php echo $catsQuery['category_id'][$i]; ?>">
												<input name="catIDlist[]" type="hidden" value="<?php echo $catsQuery['category_id'][$i]; ?>">
											</td>
											<?php // Description ?>
											<td><?php 
			// show text editor 
			if($_ENV["application.cw"]["adminEditorCategoryDescrip"]) { ?>
													<a class="descripEditLink" href="##" title="Click to Edit"><img src="img/cw-edit.gif" alt="Edit"></a>
                                                    <span class="descripText" title="Click to Edit"><?php echo $catsQuery['category_description'][$i]; ?></span>
													<span class="descripEdit">
														<textarea name="category_description<?php echo $i; ?>" class="textEdit" cols="45" rows="3"><?php echo $catsQuery['category_description'][$i]; ?></textarea>
													</span>
<?php
			} else {
													// show text input ?>
													<input type="text" name="category_description<?php echo $i; ?>" size="30" value="<?php echo $catsQuery['category_description'][$i]; ?>">
<?php
			}
?>
												</td>
<?php
// Sort Order ?>
											<td><input name="category_sort<?php echo $i; ?>" type="text" maxlength="7" size="4" class="required" title="Sort Order is required" value="<?php echo $catsQuery['category_sort'][$i]; ?>" onKeyUp="extractNumeric(this,2,true)" onblur="checkValue(this)"></td>
<?php
// Products ?>
											<td style="text-align:center;"><?php echo $catsQuery['catProdCount'][$i]; ?>
<?php
			if($catsQuery['catProdCount'][$i] > 0) { ?>
                                            	<br><br><a href="products.php?searchC=<?php echo $catsQuery['category_id'][$i]?>&search=1" title="Manage products in this category"><img alt="Manage products in this category" src="img/cw-product-edit.png"></a>
<?php
			}       
?>											</td>
<?php
// view category link 
			if((isset($_ENV["application.cw"]["adminProductLinksEnabled"])) && $_ENV["application.cw"]["adminProductLinksEnabled"]) { ?>
                                            	<td style="text-align:center;"><a href="<?php echo $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["request.cwpage"]["urlResults"]?>?category=<?php echo $catsQuery['category_id'][$i]; ?>" class="columnLink" title="View on Web:  <?php echo CWstringFormat($catsQuery['category_name'][$i]); ?>" rel="external"><img src="img/cw-product-view.png" alt="View on Web: <?php echo CWstringFormat($catsQuery['category_name'][$i]); ?>"></a>
                                                </td>
<?php
			}
// Activate/Archive ?>
											<td style="text-align:center"><input type="checkbox" name="category_archive<?php echo $i; ?>" class="formCheckbox radioGroup" value="<?php if($catsQuery['category_archive'][$i] == 0) { echo "1"; } else { echo "0"; }?>" rel="group<?php echo $i; ?>"> </td>
<?php                                           
// Delete ?>
											<td style="text-align:center"><input type="checkbox" name="deleteCategory[]" id="confirmBox<?php echo $catsQuery['category_id'][$i]; ?>" value="<?php echo $catsQuery['category_id'][$i]; ?>" class="formCheckbox radioGroup" rel="group<?php echo $i; ?>" onclick="confirmDelete('confirmBox<?php echo $catsQuery['category_id'][$i]; ?>',<?php echo $catsQuery['catProdCount'][$i]; ?>)"></td>
										</tr>
                                       
<?php
		}
?>
 </tbody>
                                        </table>

								
<?php
	}
	// /end categories table 
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
