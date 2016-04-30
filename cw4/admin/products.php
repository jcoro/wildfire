<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: products.php
File Date: 2012-05-23
Description: Displays product management table
==========================================================
*/
require_once("Application.php");
// GLOBAL INCLUDES 
// global queries
require_once('cwadminapp/func/cw-func-adminqueries.php');
require_once('cwadminapp/func/cw-func-admin.php');
$_ENV["request.cwpage"]["accesslevel"]=CWauth("manager,merchant,developer");
if(!isset($_ENV["application.cw"]["adminProductPaging"])) $_ENV["application.cw"]["adminProductPaging"]=1;
if(!isset($_ENV["application.cw"]["adminRecordsPerPage"])) $_ENV["application.cw"]["adminRecordsPerPage"]=30;
if(!isset($_GET['pagenumresults'])) $_GET['pagenumresults']=1;
if(!isset($_GET['searchby'])) $_GET['searchby']='1';
if(!isset($_GET['search'])) $_GET['search']='';
if(!isset($_GET['matchtype'])) $_GET['matchtype']='anyMatch';
if(!isset($_GET['find'])) $_GET['find']='';
if(!isset($_GET['maxrows'])) $_GET['maxrows']=$_ENV["application.cw"]["adminRecordsPerPage"];
if(!isset($_GET['sortby'])) $_GET['sortby']="product_name";
if(!isset($_GET['sortdir'])) $_GET['sortdir']="asc";
if(!isset($_GET['view'])) $_GET['view']="active";
if(!isset($_GET['searchC'])) $_GET['searchC']=0;
if(!isset($_GET['searchSC'])) $_GET['searchSC']=0;
$varsToKeep=CWremoveUrlVars('sortby','sortdir','pagenumresults','reactivateid','archiveid','userconfirm','useralert');
$_ENV["request.cwpage"]["baseURL"] = CWserializeURL($varsToKeep,$_ENV["request.cw"]["thisPage"]);
// ARCHIVE VS. ACTIVE 
if(strpos($_GET['view'],'arch') !== false) {
	$_ENV["request.cwpage"]["viewProdType"]='Archived';
	$_ENV["request.cwpage"]["subHead"]='Archived products are no longer in circulation and cannot be changed without reactivating';
} else {
	$_ENV["request.cwpage"]["viewProdType"]='Active';
	$_ENV["request.cwpage"]["subHead"]='Use the search options and table links to view and manage active products';
}
// /////// 
// ARCHIVE PRODUCT 
// /////// 
if(isset($_GET['archiveid']) && $_GET['archiveid'] > 0) {
	// QUERY: archive the product (product id) 
	$temp  = CWqueryArchiveProduct($_GET['archiveid']);
	$confirmMsg='Product Archived: Use Archived Products menu link to view or reactivate';
	CWpageMessage("confirm",$confirmMsg);
}
// /////// 
// /END ARCHIVE PRODUCT 
// /////// 
// /////// 
// ACTIVATE PRODUCT 
// /////// 
if(isset($_GET['reactivateid']) && $_GET['reactivateid'] > 0) {
	// QUERY: reactivate product (product ID) 
	$temp=CWqueryReactivateProduct($_GET['reactivateid']);
	$_ENV["request.cwpage"]["userconfirmText"]='Product Reactivated: <a href="product-details.php?productid='.$_GET['reactivateid'].'"> View Product Details</a>';
	CWpageMessage("confirm",$_ENV["request.cwpage"]["userconfirmText"]);
}
// /////// 
// /END ACTIVATE PRODUCT 
// /////// 
// PAGE SETTINGS 
// Page Browser Window Title 
$_ENV["request.cwpage"]["title"]='Manage Products';
// Page Main Heading <h1> 
$_ENV["request.cwpage"]["heading1"]='Product Management: '.$_ENV["request.cwpage"]["viewProdType"].' Products';
// Page Subheading (instructions) <h2> 
$_ENV["request.cwpage"]["heading2"]=$_ENV["request.cwpage"]["subHead"];
// current menu marker 
if (strpos($_GET['view'],"arch") !== false) $_ENV["request.cwpage"]["currentNav"] = 'products.php?view=arch';
// load form scripts 
$_ENV["request.cwpage"]["isFormPage"]=1;
// load table scripts 
$_ENV["request.cwpage"]["isTablePage"]=1;
// START OUTPUT ?>	
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
        <title><?php echo $_ENV["application.cw"]["companyName"]; ?> : <?php echo $_ENV["request.cwpage"]["title"];?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- admin styles -->
		<link href="css/cw-layout.css" rel="stylesheet" type="text/css">
		<link href="theme/<?php echo $_ENV["application.cw"]["adminThemeDirectory"];?>/cw-admin-theme.css" rel="stylesheet" type="text/css">

        <?php
        	include('cwadminapp/inc/cw-inc-admin-scripts.php');
		?>
	</head>
	<?php // body gets a class to match the filename ?>
    
    <body class="<?php 
						$pageClass=explode(".",$_ENV["request.cw"]["thisPage"]);
						echo $pageClass[0];
						
				   ?>">
		<div id="CWadminWrapper">
			<!-- Navigation Area -->
			<div id="CWadminNav">
				<div class="CWinner">
                <?php
					include('cwadminapp/inc/cw-inc-admin-nav.php');
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
                    	include('cwadminapp/inc/cw-inc-admin-page-start.php');
                    	if(strlen(trim($_ENV["request.cwpage"]["heading1"]))) {
							echo '<h1>'.trim($_ENV["request.cwpage"]["heading1"]).'</h1>';
						}
                    	if(strlen(trim($_ENV["request.cwpage"]["heading2"]))) {
							echo '<h2>'.trim($_ENV["request.cwpage"]["heading2"]).'</h2>';
						}
						// user alerts 
                    	include('cwadminapp/inc/cw-inc-admin-alerts.php');
					?>
					<!-- Page Content Area -->
					<div id="CWadminContent">
						<!-- //// PAGE CONTENT ////  -->
						<?php // SEARCH ?>
						<div id="CWadminProductSearch" class="CWadminControlWrap">
						<?php
						// include the search form 
						include('cwadminapp/inc/cw-inc-search-product.php');
						// if products found, show the paging links 
						if($productsQuery['totalRows'] > 0) {
							echo $_ENV["request.cwpage"]["pagingLinks"];
							// set up the table display output 
							if(!($_ENV["application.cw"]["adminProductPaging"])) {
								$startRow_Results=1;
								$maxRows_Results=$productsQuery['totalRows'];
							}
						}
						?>
						</div>
                        <?php
						// /END SEARCH 
						// PRODUCTS TABLE 
						// if no records found, show message 
		
                       	if(!($productsQuery['totalRows'])) {
						?>
						        <p>&nbsp;</p>
                                <p>&nbsp;</p>
                                <p>&nbsp;</p>
                                <p><strong>No products found.</strong> <br><br>Try a different search above or click the 'Active Products' link to see all currently active items.</p>
<?php
			} else {
				// if we have some records to show ?>
                                <table class="CWsort CWstripe" summary="<?php $_ENV["request.cwpage"]["baseURL"]; ?>">
                                    <thead>
                                    <tr class="sortRow">
						<?php

                            if(!($_ENV["request.cwpage"]["viewProdType"]=='Archived')) {
                        ?>
                                            <th  class="noSort" width="20">Edit</th>
						<?php
                            }
                        ?>
									<th class="product_name">Product Name</th>
									<th class="product_merchant_product_id">Product ID</th>
						<?php
                            // cats, subcats 
                            if($listActiveCats['totalRows'] >= 1) {
                        ?>
										<th class="noSort">
						<?php 
                            $fadminLabelCategory=explode(" ",$_ENV["application.cw"]["adminLabelCategory"]);
                            echo 'Main';
                         ?>
										</th>
						<?php
                        }
                        if($listActiveScndCats['totalRows'] >= 1) {
						?>
											<th class="noSort">
							<?php
                            $adminSecondary=explode(" ",$_ENV["application.cw"]["adminLabelSecondary"]);
                            echo 'Secondary';
                            ?>
                                          </th>
						<?php
                        }
                        ?>
									<th class="noSort" width="80">Photo</th>
									<?php // add date modified ?>
									<th class="product_date_modified">Modified</th>
						<?php
                        if(!($_ENV["request.cwpage"]["viewProdType"]=='Archived')) {
                        ?>
											<th class="product_on_web" width="60">On&nbsp;Web</th>
						 <?php
                            // add 'view on site' link 
							if(isset($_ENV["application.cw"]["adminProductLinksEnabled"]) && $_ENV["application.cw"]["adminProductLinksEnabled"]) {
                         ?>
												<th class="noSort" width="50">View</th>
							  <?php
							}
						}
						// archive ?>     
									<th class="noSort" width="50">
						<?php if(!($_ENV["request.cwpage"]["viewProdType"] =='Archived' )) { ?>Archive<?php } else { ?>Activate<?php } ?>
                                    </th>
								</tr>
								</thead>
								<tbody>
						<?php
                        // OUTPUT THE PRODUCTS 
			for($i=$startRow_Results-1; $i<$productsQuery['totalRows'] && $i<$endRow_Results; $i++) {
						?>
								<tr>
							<?php
								    // edit link 
								    if(!($_ENV["request.cwpage"]["viewProdType"]=='Archived')) {
                            ?>
								    <td style="text-align:center;"><a href="product-details.php?productid=<?php echo $productsQuery['product_id'][$i];?>" title="Edit Product Details: <?php echo $productsQuery['product_name'][$i];?>" class="columnLink"><img src="img/cw-edit.gif" alt="Edit <?php echo $productsQuery['product_name'][$i];?>" width="15" height="15" border="0"></a></td>
							<?php
								    }
								    // name (linked) ?>
								    <td>
									<strong>
							<?php
									if(!($_ENV["request.cwpage"]["viewProdType"]=='Archived')) {
                            ?>
									    <a class="productLink" href="product-details.php?productid=<?php echo $productsQuery['product_id'][$i];?>" title="Edit Product Details: <?php echo $productsQuery['product_name'][$i];?>"><?php echo $productsQuery['product_name'][$i];?></a>
							<?php
									} else {
									     echo $productsQuery['product_name'][$i]; 
									}
							?>
									</strong>
								    </td>
								    <td>
                                    <?php
									echo $productsQuery['product_merchant_product_id'][$i];
							if($listActiveCats['totalRows'] >= 0) {
                                // QUERY: get categories 
								$listProdCats=CWquerySelectRelCategories($productsQuery['product_id'][$i]);
									?>
                                			<td>
									<?php
								for ($currentRow=0; $currentRow < $listProdCats["totalRows"]; $currentRow++) {
									echo $listProdCats['category_name'][$currentRow];
									if ($listProdCats["totalRows"] > 1 && $currentRow < $listProdCats["totalRows"]-1) {
										echo "<br>
										";
									}
								}
									?>
										</td>
							<?php
                            }
                            if($listActiveScndCats['totalRows'] >= 1) {
                                $listProdSubcats=CWquerySelectRelScndCategories($productsQuery['product_id'][$i]);
							?>
										<td>
							<?php
								for ($currentRow=0; $currentRow < $listProdSubcats["totalRows"]; $currentRow++) {
									echo $listProdSubcats['secondary_name'][$currentRow];
									if ($listProdSubcats["totalRows"] > 1 && $currentRow < $listProdSubcats["totalRows"]-1) {
										echo "<br>
										";
									}
								}
							?>
										</td>
							<?php
                            }
							// PHOTO 
							// get the first image 
						 	$imageFn=CWgetImage($productsQuery['product_id'][$i],1);
							$imageFn_a = explode('/',CWgetImage($productsQuery['product_id'][$i],1));					
							$imageFn = $imageFn_a[count($imageFn_a)-1];
							if(!isset($_ENV["request.cwpage"]["adminImgPrefix"])) $_ENV["request.cwpage"]["adminImgPrefix"]='../';
							if(strlen(trim($imageFn))) {								
								$imageFn = $_ENV["request.cwpage"]["adminImgPrefix"].$_ENV["application.cw"]["appImagesDir"].'/admin_preview/'.$imageFn;
							} elseif(isset($_ENV["application.cw"]["appImageDefault"]) && strlen(trim($_ENV["application.cw"]["appImageDefault"]))) {
								$imageFn = $_ENV["request.cwpage"]["adminImgPrefix"].$_ENV["application.cw"]["appImagesDir"].'/admin_preview/'.trim($_ENV["application.cw"]["appImageDefault"]);
							} else {
								$imageFn='';
							}
							?>
									<td style="text-align:center;" class="imageCell">
							<?php
							if(strlen(trim($imageFn)) && file_exists($imageFn) ) {
								if(!($_ENV["request.cwpage"]["viewProdType"]=='Archived')) {
							?>
							    <a href="product-details.php?productid=<?php echo $productsQuery['product_id'][$i]; ?>&showtab=3" title="View product details">
							    <img src="<?php echo $imageFn;?>" alt="View product details"></a>
							    <?php
							    } else { ?>
							    <img src="<?php echo $imageFn;?>" title="Activate product to edit">
	                                              <?php
							    }
                                                        }
							?>
                                            									</td>
									<?php // TIMESTAMP ?>
									<td>
										<span class="dateStamp">
											<?php echo cartweaverDate($productsQuery['product_date_modified'][$i]); ?>
											<br><?php echo date("H:s", strtotime($productsQuery['product_date_modified'][$i])); ?>
										</span>
									</td>
						<?php
                            // if archived, don't show these columns 
                            if(!($_ENV["request.cwpage"]["viewProdType"]=='Archived')) {
                                $onweb = $productsQuery['product_on_web'][$i];
                        ?>
                                            <td style="text-align:center;"><?php
                                if($onweb==0) {
                                    echo 'No';
                                } else {
                                    echo 'Yes';
                                }
							?></td>
                            <?php
                               	// view product link 
								if(isset($_ENV["application.cw"]["adminProductLinksEnabled"]) && $_ENV["application.cw"]["adminProductLinksEnabled"]) {
							?>
												<td style="text-align:center;"><a href="<?php echo $_ENV["application.cw"]["appSiteUrlHttp"].$_ENV["request.cwpage"]["urlDetails"]; ?>?product=<?php echo $productsQuery['product_id'][$i]; ?>" class="columnLink" title="View on Web: <?php echo $productsQuery['product_name'][$i];?>" rel="external"><img src="img/cw-product-view.png" alt="View on Web: <?php echo $productsQuery['product_name'][$i];?>"></a></td>
									     <?php
								}
							}
							// ARCHIVE / ACTIVATE 
							// keep same page when archiving 
							// get the vars to keep by omitting the ones we don't want repeated 
							$varsToPass= CWremoveUrlVars('reactivateid,archiveid,userconfirm,useralert');
							// set up the base url 
						  	$passQS=CWserializeURL($varsToPass);
							// archive / activate button ?>
									<td style="text-align:center;">
                                    <a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?<?php 
													if(!($_ENV["request.cwpage"]["viewProdType"] =='Archived' ))
														 	echo 'view=arch&archiveid';
														else
															echo 'reactivateid';
														echo '='.$productsQuery['product_id'][$i].'&'.$passQS;	
									?>" class="columnLink" title="<?php 
										if(!($_ENV["request.cwpage"]["viewProdType"] == 'Archived'))
										 	echo 'Archive';
										 else
										 	echo 'Reactivate';
										echo ' Product: '.$productsQuery['product_name'][$i];
									?>"><img src="img/<?php 
									if(!($_ENV["request.cwpage"]["viewProdType"] == 'Archived'))
										echo 'cw-archive';
									else		
										echo 'cw-archive-restore';
									?>.gif" alt="Archive" border="0"></a>
                                    </td>
								</tr>
					<?php } ?>
                                	</tbody>
							</table>
							<div class="tableFooter">
				<?php
                    echo $_ENV["request.cwpage"]["pagingLinks"];
                }
				// /END PRODUCTS TABLE ?>
					</div>
					<!-- /end Page Content -->
				</div>
				<!-- /end CWinner -->
			</div>
            <?php
			// page end content / debug 
            include('cwadminapp/inc/cw-inc-admin-page-end.php');
			?>
			<!-- /end CWadminPage-->
			<div class="clear"></div>
		</div>
		<!-- /end CWadminWrapper -->
	</body>
</html>