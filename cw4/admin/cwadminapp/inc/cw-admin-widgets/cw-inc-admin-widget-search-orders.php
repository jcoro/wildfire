<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-inc-admin-widget-search-orders.php
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
    <form name="formOrderSearch" id="formOrderSearch" method="get" action="orders.php">
        <input name="Search" type="submit" class="CWformButton" id="Search" value="Search" style="margin-bottom: 2px;" />
        <label for="orderStr">Order ID:</label>
        <input name="orderStr" id="orderStr" type="text" size="10" value="" />
        <label for="selectStartDate" style="padding-left:23px;">From:</label>
<?php
$dateOneMonth = strtotime("-1 Month");
?>
        <input name="startDate" type="text" class="date_input_past" value="<?php echo cartweaverScriptDate($dateOneMonth); ?>" size="10" id="selectStartDate" />
      
        <label for="selectEndDate">To:</label>
        <input name="endDate" type="text" class="date_input_past" value="<?php echo cartweaverScriptDate(CWtime()); ?>" size="10" id="selectEndDate" />
      
        <label for="status">Status:</label>
        <select name="Status" id="status">
            <option value="0">Any</option>
            <?php
                for($i=0;$i<$orderStatusQuery['totalRows'];$i++) {
             ?>
                    <option value="<?php echo $orderStatusQuery['shipstatus_id'][$i]; ?>"><?php echo $orderStatusQuery['shipstatus_name'][$i]; ?></option>
             <?php
                }
              ?>
          
        </select>
    </form>
</div>

