<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-widget-search-products.php
File Date: 2012-02-01
Description:
Displays basic product search form on admin home page
==========================================================
*/
// get all cats and subcats 
// QUERY: get all active categories 
$listActiveCats = CWquerySelectActiveCategories();
?>
<div class="CWadminHomeSearch">
	<?php // // SHOW FORM // ?>
	<form name="formProductSearch" method="get" action="products.php" id="formProductSearch">
		<input name="Search" type="submit" class="CWformButton" id="Search" value="Search">
		<label for="Find">Keyword:</label>
		<input name="Find" type="text" size="12" id="Find" value="" class="focusField">
		<label for="searchBy">Search In:</label>
		<select name="searchBy" id="searchBy">
			<option value="any">All Fields</option>
			<option value="prodID">Product ID</option>
			<option value="prodName">Product Name</option>
			<option value="descrip">Description</option>
		</select>
		<?php // categories 
			if($listActiveCats['totalRows'] > 1) { ?>
                 
                    <label for="searchC"><?php echo $_ENV["application.cw"]["adminLabelCategory"]; ?>:</label>
                    <select name="searchC" id="searchC" onkeyup="this.change();">
                        <option value="">All</option>
                        <?php for($i=0; $i<$listActiveCats['totalRows']; $i++) { ?>
                        			<option value="<?php $listActiveCats['category_id'][$i]; ?>"><?php echo substr($listActiveCats['category_name'][$i],0,15); ?></option>
                        <?php   } ?>
                    </select>
<?php 			} else { ?>
					<input type="hidden" name="searchC" value="">
<?php 			} ?>
	</form>
</div>