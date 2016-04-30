<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-widget-search-customers.php
File Date: 2012-02-01
Description:
Displays basic orders search form on admin home page
==========================================================
*/
// QUERY: get all possible order status types 
$orderStatusQuery = CWquerySelectOrderStatus();
?>
<div class="CWadminHomeSearch">
	<?php // // SHOW FORM // ?>
	<form name="formCustomerSearch" method="get" action="customers.php" id="formCustomerSearch">
		<input name="Search" type="submit" class="CWformButton" id="Search" value="Search">
		<label for="searchCustName">Name:</label>
		<input name="custName" type="text" value="" size="18" id="searchCustName">
		<label for="searchCustID">ID:</label>
		<input name="custID" type="text" value="" size="15" id="searchCustID">
		<label for="searchOrderStr">Order ID:</label>
		<input name="orderStr" id="searchOrderStr" type="text" size="18" value="">
	</form>
</div>