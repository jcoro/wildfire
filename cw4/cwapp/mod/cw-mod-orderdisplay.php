<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-mod-orderdisplay.php
File Date: 2012-02-01
Description: creates and displays order details, with options for various uses
==========================================================
Mode "summary" shows cart details without update/edit functions
Mode "totals" shows order totals only, useful for split-display or quick reports
*/
// order details passed in (query) 
if(!(isset($module_settings["order_query"]))) $module_settings["order_query"] = "";
// transfer order passed in to shorter variable 
$orderQuery = $module_settings["order_query"];
// show cart edit form or summary ( summary | totals ) 
if(!(isset($module_settings["display_mode"]))) $module_settings["display_mode"] = "summary";
// show images next to products 
if(!(isset($module_settings["show_images"]))) $module_settings["show_images"] = false;
// show options and custom values for product 
if(!(isset($module_settings["show_options"]))) $module_settings["show_options"] = false;
// show sku next to product name 
if(!(isset($module_settings["show_sku"]))) $module_settings["show_sku"] = false;
// show row w/ order totals 
if(!(isset($module_settings["show_total_row"]))) $module_settings["show_total_row"] = true;
// show specific totals 
if(!(isset($module_settings["show_tax_total"]))) $module_settings["show_tax_total"] = true;
if(!(isset($module_settings["show_discount_total"]))) $module_settings["show_discount_total"] = true;
if(!(isset($module_settings["show_ship_total"]))) $module_settings["show_ship_total"] = true;
if(!(isset($module_settings["show_order_total"]))) $module_settings["show_order_total"] = true;
if(!(isset($module_settings["show_tax_id"]))) $module_settings["show_tax_id"] = $_ENV["application.cw"]["taxDisplayID"];
// show discount descriptions 
if(!(isset($module_settings["show_discount_descriptions"]))) $module_settings["show_discount_descriptions"] = true;
// show payments made, deduct from order total 
if(!(isset($module_settings["show_payment_total"]))) $module_settings["show_payment_total"] = true;
// link products to details page 
if(!(isset($module_settings["link_products"]))) $module_settings["link_products"] = false;
// shipping method name (overrides auto lookup below) 
if(!(isset($module_settings["ship_method_name"]))) $module_settings["ship_method_name"] = "";
// custom errors can be passed in here 
$_ENV["request.cwpage"]["cartErrors"] = '';
// order defaults 
if(!(isset($orderQuery['totalRows']))) $orderQuery['totalRows'] = 0;
// determine which columns to show 
if(!(isset($_ENV["application.cw"]["taxDisplayLineItem"]))) $_ENV["application.cw"]["taxDisplayLineItem"] = false;
if(!(isset($_ENV["application.cw"]["discountDisplayLineItem"]))) $_ENV["application.cw"]["discountDisplayLineItem"] = false;
if(!(isset($_ENV["application.cw"]["taxChargeOnShipping"]))) $_ENV["application.cw"]["taxChargeOnShipping"] = false;
if(!(isset($_ENV["application.cw"]["shipDisplayInfo"]))) $_ENV["application.cw"]["shipDisplayInfo"] = true;
$_ENV["request.cwpage"]["shipDisplayInfo"] = $_ENV["application.cw"]["shipDisplayInfo"];
$_ENV["request.cwpage"]["taxDisplayLineItem"] = $_ENV["application.cw"]["taxDisplayLineItem"];
// if discounts are enabled, and at least one discount applied  
if($_ENV["application.cw"]["discountsEnabled"] && $orderQuery['order_discount_total'][0] > 0) {
	$_ENV["request.cwpage"]["discountDisplayLineItem"] = $_ENV["application.cw"]["discountDisplayLineItem"];
} else {
	$_ENV["request.cwpage"]["discountDisplayLineItem"] = false;
}
$_ENV["application.cw"]["taxChargeOnShipping"] = $_ENV["application.cw"]["taxChargeOnShipping"];
// number of columns 
$_ENV["request.cwpage"]["cartColumnCount"] = 3;
// tax adds 2 columns 
if($_ENV["request.cwpage"]["taxDisplayLineItem"]) {
	$_ENV["request.cwpage"]["cartColumnCount"] = $_ENV["request.cwpage"]["cartColumnCount"] + 2;
}
// discount adds 1 column 
if($_ENV["request.cwpage"]["discountDisplayLineItem"]) {
	$_ENV["request.cwpage"]["cartColumnCount"] = $_ENV["request.cwpage"]["cartColumnCount"] + 1;
}
$myDir = getcwd();
chdir(dirname(__FILE__));
// global functions 
require_once("../inc/cw-inc-functions.php");
// clean up form and url variables 
require_once("../inc/cw-inc-sanitize.php");
chdir($myDir);
// QUERY: get payment total applied to order 
$orderPayments = CWorderPaymentTotal($orderQuery['order_id'][0]);
// payment total 
if($module_settings["show_payment_total"]) {
	// get payment types associatd to an order 
	$paymentQuery = CWorderPaymentTypes($orderQuery['order_id'][0]);
	// only show paid in full message if there is no 'account' payment 
	if (isset($paymentQuery['payment_type']) && in_array("account", $paymentQuery['payment_type']) && isset($paymentQuery['payment_status']) && in_array('approved', $paymentQuery['payment_status'])) {
		// note: custom notes or paid-order status can be added here 
		$_ENV["request.cwpage"]["zeroBalanceMessage"] = "On Account" ;
	} else {
		$_ENV["request.cwpage"]["zeroBalanceMessage"] = "Paid in Full";
	}
}
// use orderquery name for shipping method if not passed in 
if($module_settings["ship_method_name"] = '' && strlen(trim($orderQuery['ship_method_name'][0]))) {
	$module_settings["ship_method_name"] = trim($orderQuery['ship_method_name'][0]);
}

// //////////// 
// START OUTPUT 
// //////////// 
// VERIFY ORDER EXISTS 
if($orderQuery['totalRows'] > 0) {
	// TOTALS ONLY 
	if($module_settings["display_mode"] == 'totals') {
		// order subtotal
?>
<p>
	<span class="label">Subtotal:</span> 
	<?php echo cartweaverMoney($orderQuery['order_SubTotal'][0],'local'); ?>
	<br>
<?php
		// discounts total  
		if($_ENV["application.cw"]["discountsEnabled"] && $module_settings["show_discount_total"] && $orderQuery['order_discount_total'][0] > 0) {
?>
    <span class="CWdiscountText">
        <span class="label">Discounts:</span> -
        <?php echo cartweaverMoney($orderQuery['order_discount_total'][0],'local'); ?>
    </span>
<?php
		}
		// tax label 
		if($module_settings["show_tax_total"]) {
?>
    <span class="label"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?>: </span>
    <?php echo cartweaverMoney($orderQuery['order_tax'][0],'local'); ?>
    <br>
<?php
		}
		// shipping total 
		if($_ENV["application.cw"]["shipEnabled"] && $module_settings["show_ship_total"]) {
			if($orderQuery['order_shipping'][0] > 0) {
?>
    <span class="label">Shipping/Handling: </span>
    <?php echo cartweaverMoney(($orderQuery['order_shipping'][0] + $orderQuery['order_ship_discount_total'][0]),'local'); ?>
<?php
				// shipping discounts 
				if (($_ENV["application.cw"]["discountsEnabled"]) && ($orderQuery["order_ship_discount_total"][0] > 0) && ($module_settings["show_discount_total"])){
?>
    <br>
    <span class="CWdiscountText">
    	<span class="label">Shipping Discounts:</span> -
		<?php echo cartweaverMoney($orderQuery["order_ship_discount_total"][0],'local'); ?>
	</span>
    <br>
    <strong>
		<span class="label">Shipping Total:</span>
		<?php echo cartweaverMoney($orderQuery["order_shipping"][0],'local') ?>
	</strong>
<?php
				}
			}
?>
	<br>
<?php
			// shipping tax 
			if($orderQuery['order_shipping_tax'][0] > 0 && $_ENV["application.cw"]["taxChargeOnShipping"]) {
?>
    <br>
    <span class="label">Shipping <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?>: </span>
<?php
				echo cartweaverMoney($orderQuery['order_shipping_tax'][0],'local');
			}
		}
?>
</p>
<?php
		// complete order total (payment due amount) 
		if($module_settings["show_order_total"]) {
?>
<p class="CWtotal">
	<span class="label"><strong>Order Total:</strong></span>
	<?php echo cartweaverMoney($orderQuery['order_total'][0] ,'local'); ?>
</p>
<?php
		}
		// payment total 
		if($module_settings["show_payment_total"]) {
?>
<p class="CWtotal">
<?php
			if(cartweaverMoney($orderPayments,'local') == cartweaverMoney($orderQuery['order_total'][0],'local')) {
?>
	<span class="label"><?php echo $_ENV["request.cwpage"]["zeroBalanceMessage"]; ?></span>
<?php
			} else if($orderPayments > 0) {
?>
	<span class="label"><strong>Payments:</strong></span> -
<?php
				echo cartweaverMoney($orderPayments,'local');
			}
?>
</p>
<?php
		}
?>
<div class="CWclear"></div>
<?php
	// /end Totals 
	// FULL CONTENT (summary) 
	} else {
		// CUSTOMER INFO
?>
<table class="CWtable">
    <thead>
        <tr class="headerRow">
            <th>
                Order Details
            </th>
<?php
		if($_ENV["application.cw"]["shipEnabled"] && $_ENV["request.cwpage"]["shipDisplayInfo"]) {
?>
			<th>
				Shipping Information
			</th>
<?php
		}
?>                        
		</tr>
	</thead>
	<tbody>
		<tr>
		<?php // order details ?>
			<td>
				<p>
					<strong>Order ID: <?php echo $orderQuery['order_id'][0]; ?></strong><br>
                    <strong>Status: <?php echo $orderQuery['shipstatus_name'][0]; ?></strong><br>
<?php
		if($module_settings["show_tax_id"]  && strlen(trim($_ENV["application.cw"]["taxIDNumber"]))) {
			echo $_ENV["application.cw"]["taxSystemLabel"]; ?> ID: <?php echo trim($_ENV["application.cw"]["taxIDNumber"]);
?>
                    <br>
<?php
		}
?>          
                    Sold To: <?php echo $orderQuery['customer_first_name'][0]." ".$orderQuery['customer_last_name'][0]; ?>
                    <br>
                    Email: <?php echo $orderQuery['customer_email'][0]; ?>
                    <br>
                    Customer ID: <?php echo $orderQuery['customer_id'][0]; ?>
				</p>
			</td>
<?php
		// shipping info
        if($_ENV["application.cw"]["shipEnabled"] && $_ENV["request.cwpage"]["shipDisplayInfo"]) {
?>
			<td>
				<p>
					Ship To:  
<?php
			if(strlen(trim($orderQuery['order_ship_name'][0]))) {
				echo $orderQuery['order_ship_name'][0];
				echo '<br>';
			}
			if(strlen(trim($orderQuery['order_company'][0]))) {
				echo $orderQuery['order_company'][0];
				echo '<br>';
			}
			if(strlen(trim($orderQuery['order_address1'][0]))) {
				echo $orderQuery['order_address1'][0];
			}
			if(strlen(trim($orderQuery['order_address2'][0]))) {
				echo ", ".$orderQuery['order_address2'][0];
			}
			echo '<br>';
			echo $orderQuery['order_city'][0].', '.$orderQuery['order_state'][0].' '.$orderQuery['order_zip'][0];
			if(strlen(trim($orderQuery['order_country'][0]))) {
				echo " ".$orderQuery['order_country'][0];
			}
			if(strlen(trim($module_settings["ship_method_name"]))) {
				echo '<br>Ship Via: '.$module_settings["ship_method_name"];
			}
?>
				</p>
			</td>
<?php
		}
?>                        
		</tr>
	</tbody>
</table>
<?php
		// PRODUCTS TABLE  
		// products in order
?>
<table class="CWtable" id="CWcartProductsTable">
	<thead>
	<?php // table headers ?>
        <tr class="headerRow">
            <th>Item</th>
            <th class="center">Qty.</th>
            <th>Price</th>
<?php
		if($_ENV["request.cwpage"]["discountDisplayLineItem"]) {
?>
			<th class="CWleft">Discount</th>
<?php
		}
?>                        
<?php
		if($_ENV["request.cwpage"]["taxDisplayLineItem"]) {
?>
            <th class="CWleft">Subtotal</th>
            <th class="CWleft"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?></th>
<?php
		}
?>
            <th class="center">Total</th>
        </tr>
    </thead>
    <tbody>
<?php
		// OUTPUT PRODUCTS 
        for($i = 0; $i < $orderQuery['totalRows']; $i++) {
            // get image for item ( add to cart item info )
            if($module_settings["show_images"]) {
                $itemImg = CWgetImage($orderQuery['product_id'][$i],4,$_ENV["application.cw"]["appImageDefault"]);
            } else {
                $itemImg = '';
            }
            // url for linked info 
            $itemUrl = $_ENV["request.cwpage"]["urlDetails"]."?product=".$orderQuery['product_id'][$i];
            $rowClass = 'itemRow row-'.($i+1);
			// SHOW 1 ROW FOR EACH PRODUCT 
?>
        <tr class="<?php echo $rowClass; ?>">
            <?php // product name, image, options ?>
            <td class="productCell">
<?php
			// product image
            if(strlen(trim($itemImg))) { 
?>
             	<div class="CWcartImage">
<?php
                if($module_settings["link_products"]) {
?>
                 	<a href="<?php echo $itemUrl;?>" title="View Product">
                      	<img src="<?php echo $itemImg;?>" alt="<?php echo $orderQuery['product_name'][$i]; ?>">
                    </a>
<?php											
                } else { 
?>
                   	<img src="<?php echo $itemImg;?>" alt="<?php echo $orderQuery['product_name'][$i]; ?>">
<?php
                }
?>
                </div>	                                     
<?php									
            }
			// product name 
?>
                <div class="CWcartItemDetails">
                    <span class="CWcartProdName"> 
<?php
            if($module_settings["link_products"]) { 
?>
                    	<a href="<?php echo $itemUrl;?>" title="View Product" class="CWlink"><?php echo $orderQuery['product_name'][$i];?></a>
<?php											
            } else {
				echo $orderQuery['product_name'][$i];
            }
            if($module_settings["show_sku"]) {
?>
                        <span class="CWcartSkuName">(<?php echo $orderQuery['sku_merchant_sku_id'][$i];?>)</span>
<?php 
			}
?>
            		</span>
<?php
			if($module_settings["show_options"]) {
				// QUERY: get sku options 
				$optionsQuery = CWquerySelectSkuOptions($orderQuery['product_id'][$i],$orderQuery['sku_id'][$i]);
				// if the sku has options 
				if($optionsQuery['totalRows']) {
					for($j = 0; $j < $optionsQuery['totalRows']; $j++) { 
?>
					<div class="CWcartOption">
						<span class="CWcartOptionName"><?php echo $optionsQuery['optiontype_name'][$j]; ?>:</span>
						<span class="CWcartOptionValue"><?php echo $optionsQuery['option_name'][$j]; ?></span>
					</div>
<?php
					}
				}
			   // /end sku options 
			}
			// custom value 
			if($orderQuery['sku_id'][$i] != $orderQuery['ordersku_unique_id'][$i] && $_ENV["application.cw"]["appDisplayCartCustom"] &&  $module_settings["show_options"]) {
				$uniqueid = explode('-',$orderQuery['ordersku_unique_id'][$i]);
				$uniqueid_last = $uniqueid[count($uniqueid) - 1];
				$phraseID = $uniqueid_last;
				$phraseText = CWgetCustomInfo($phraseID);
				$trimLength = 35;
				if(strlen(trim($phraseText))) {
?>
					<div class="CWcartOption">
<?php	
					if(strlen(trim($orderQuery['product_custom_info_label'][$i]))) { 
?>
						<span class="CWcartCustomLabel"><?php echo $orderQuery['product_custom_info_label'][$i]; ?>:</span>
<?php											
					}
?>
						<span class="CWcartCustomValue">
<?php
					echo substr(trim($phraseText),0,$trimLength);
					if(strlen(trim($phraseText)) > $trimLength)	echo "...";
?>
                    	</span>
                	</div>	
<?php											
                }
            }
			// /end custom value 
?>
        		</div>
     			<?php // /end cartitemdetails ?>
            </td>
			<?php // qty ?>
            <td class="qtyCell center">
                <?php echo $orderQuery['ordersku_quantity'][$i]; ?>
            </td> 
            <?php // price ?>
            <td class="priceCell">
                <span class="CWcartPrice"><?php echo cartweaverMoney($orderQuery['ordersku_unit_price'][$i],'local'); ?></span>
            </td>
<?php
			// discounzs
            if($_ENV["request.cwpage"]["discountDisplayLineItem"]) { 
?>
          	<td class="priceCell">
                <span class="CWcartPrice"><?php echo cartweaverMoney($orderQuery['ordersku_discount_amount'][$i],'local'); ?></span>
            </td>
<?php
            }
            // taxes (subtotal before tax, and the tax amount)
            if($_ENV["request.cwpage"]["taxDisplayLineItem"]) {
	            // item subtotal
?>
           	<td class="priceCell"><?php echo cartweaverMoney($orderQuery['ordersku_unit_price'][$i] * $orderQuery['ordersku_quantity'][$i],'local'); ?></td>
            <?php // item tax ?>
            <td class="priceCell"><?php echo cartweaverMoney($orderQuery['ordersku_tax_rate'][$i],'local'); ?></td>
<?php
            }
			// total 
?>
            <td class="totalCell center">
                <span class="CWcartPrice"><?php echo cartweaverMoney($orderQuery['ordersku_sku_total'][$i],'local');?></span>
            </td>
        </tr>
<?php
			// clear values, increment counter  
			$itemImg = '';
			$itemURL = '';
       	}
		// totals row 
		if($module_settings["show_total_row"] || $module_settings["show_discount_descriptions"]) { 
?>
       	<tr class="totalRow">
            <?php // order comments ?>
           	<td>
<?php
	        if(strlen(trim($orderQuery['order_comments'][0]))) {
?>
                 Order Comments:
                 <br>
<?php
				 echo trim($orderQuery['order_comments'][0]);
			 }
			 // applied discount descriptions 
			 if ($module_settings["show_discount_descriptions"]) {
				 // reset description list 
				 $_ENV["request.cwpage"]["discountDescriptions"] = array();
				 // QUERY: get discounts applied to this order 
				 $orderDiscounts = CWgetOrderDiscounts($orderQuery["order_id"][0]);
				 // output list of applied discounts 
				 for ($n=0; $n<$orderDiscounts["totalRows"]; $n++) {
					 $discountDescription = "".$orderDiscounts["discount_usage_discount_name"][$n];
					 if (strlen(trim($orderDiscounts["discount_usage_promocode"][$n]))) {
						 $discountDescription .= " (".$orderDiscounts["discount_usage_promocode"][$n].")";
					 }
					 if (strlen(trim($orderDiscounts["discount_usage_discount_description"][$n]))) {
						 $discountDescription .= "
					 	<br><span class=\"CWdiscountDescription\">".$orderDiscounts["discount_usage_discount_description"][$n]."</span>";
					 }
					 // if description exists, add it to a list 
					 if (strlen(trim($discountDescription))) {
						 $_ENV["request.cwpage"]["discountDescriptions"][] = trim($discountDescription);
					 }
				 }
				 // if we have descriptions to show 
				 $discDescArr = $_ENV["request.cwpage"]["discountDescriptions"];
				 if (!is_array($discDescArr) && strlen($discDescArr)) $discDescArr = explode("|", $discDescArr);
				 else if (!is_array($discDescArr)) $discDescArr = array();
				 if (sizeof($discDescArr)) {
?>
										<div class="CWcartDiscounts">
										<p class="CWdiscountHeader">Discounts applied to this order:</p>
<?php
					foreach ($discDescArr as $key => $ii) {
?>
											<p><?php echo $ii; ?></p>
<?php
					}
?>
										</div>
<?php
				}
			}
?>
            </td>
<?php
			// ORDER TOTALS 
			// text labels for totals 
?>
            <td colspan="<?php echo $_ENV["request.cwpage"]["cartColumnCount"] - 2;?>" class="CWright totalCell">
<?php
			if ($module_settings["show_total_row"]) {
				// item total, if showing discounts 
				if ($module_settings["show_discount_total"] && $orderQuery["order_discount_total"][0] > 0) {
?>
				<span class="CWdiscountText label">Item Total: </span><br>
				<?php //  discount ?>
				<span class="CWdiscountText label">Discounts: </span><br>
<?php
				}
				// cart subtotal label 
?>
                <span class="label">Subtotal:</span>
<?php
				// tax label 
				if ($module_settings["show_tax_total"]) {
?>
				<br><span class="label"><?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?>: </span>
<?php
				}
				// shipping total labels
				if($_ENV["application.cw"]["shipEnabled"] && $module_settings["show_ship_total"]) {
					if($orderQuery['order_shipping'][0] > 0) { 
?>
                <br><span class="label">Shipping/Handling: </span>
<?php										
					}
					// shipping discounts 
					if ($_ENV["application.cw"]["discountsEnabled"] && $orderQuery["order_ship_discount_total"][0] > 0 && $module_settings["show_discount_total"]) {
?>
									<br><span class="CWdiscountText label">Shipping Discounts: </span>
									<br><span class="label CWsubtotalText">Shipping Total: </span>
<?php
					}
					// shipping tax 
					if($orderQuery['order_shipping_tax'][0] > 0 && $_ENV["application.cw"]["taxChargeOnShipping"]) { 
?>
                <br><span class="label">Shipping <?php echo $_ENV["application.cw"]["taxSystemLabel"]; ?>: </span>
<?php										
					}
				}
				if($module_settings["show_order_total"]) { 
?>
                <br><span class="label CWsubtotalText">Order Total: </span>
<?php                                    
				}
				// payment total
				if($module_settings["show_payment_total"]) {
					if(cartweaverMoney($orderPayments,'local') == cartweaverMoney($orderQuery['order_total'][0],'local')) { 
?>
                <br><span class="label"><?php echo $_ENV["request.cwpage"]["zeroBalanceMessage"]; ?></span>
<?php                                    
					} else if($orderPayments > 0) { 
?>
                <br><span class="label CWsubtotalText">Payments: </span>
                <br><span class="label CWsubtotalText">Balance Due: </span>
<?php
					}
				}
			}
?>                                
           	</td>
            <?php // total amounts ?>
            <td class="totalCell totalAmounts">
<?php
			if ($module_settings["show_total_row"]) {
				// discount
				if($module_settings["show_discount_total"] && $orderQuery['order_discount_total'][0] > 0) {
?>
				<span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney($orderQuery["order_SubTotal"][0]+$orderQuery["order_discount_total"][0],'local'); ?> </span><br>
                <span class="CWdiscountText CWtotalText"><?php echo cartweaverMoney($orderQuery['order_discount_total'][0],'local');?></span><br>
<?php									
				}
				// subtotal
?>
				<span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney($orderQuery['order_SubTotal'][0],'local'); ?> </span>
<?php
				// tax total
				if($module_settings["show_tax_total"]) {
?>
					<br><span class="CWtotalText"><?php echo cartweaverMoney($orderQuery['order_tax'][0],'local'); ?> </span>
<?php
				}
				// if shipping is selected, shipping total
				if($_ENV["application.cw"]["shipEnabled"]) {
					if($module_settings["show_ship_total"]) {
						if($orderQuery['order_shipping'][0] > 0) {
?>
                <br><span class="CWtotalText"><?php echo cartweaverMoney($orderQuery['order_shipping'][0] + $orderQuery['order_ship_discount_total'][0],'local'); ?> </span>
<?php
						}
						// shipping discounts 
						if ($_ENV["application.cw"]["discountsEnabled"] && $orderQuery["order_ship_discount_total"][0] > 0 && $module_settings["show_discount_total"]) {
?>
										<br><span class="CWdiscountText CWtotaltext">-<?php echo cartweaverMoney($orderQuery["order_ship_discount_total"][0],'local'); ?> </span>
										<br><span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney($orderQuery["order_shipping"][0],'local'); ?> </span>
<?php
						}
						// shipping tax 
						if($orderQuery['order_shipping_tax'][0] > 0 && $_ENV["application.cw"]["taxChargeOnShipping"]) {
?>
                <br><span class="CWtotalText"><?php echo cartweaverMoney($orderQuery['order_shipping_tax'][0],'local'); ?> </span>
<?php
						}
					}
				}
				// complete order total (payment due amount) 
				if($module_settings["show_order_total"]) {
?>
                <br><span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney($orderQuery['order_total'][0],'local');?> </span>
<?php
				}
				// payment total 
				if($orderPayments > 0 && $module_settings["show_payment_total"] && (!(cartweaverMoney($orderPayments,'local') == cartweaverMoney($orderQuery['order_total'][0],'local')))) {
					$orderBalance = $orderQuery['order_total'][0] - $orderPayments;
?>
                <br><span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney($orderPayments,'local'); ?> </span>
                <br><span class="CWtotalText CWsubtotalText"><?php echo cartweaverMoney($orderBalance,'local'); ?> </span>
<?php
    	    	}
			}
?>                                
            </td>
        </tr>
<?php
			// /end totals row
		}
?>                   
    </tbody>
</table>
<?php
		// /end products table
	}
// /END Display Mode 
// IF ORDER NOT VALID 
} else { 
?>
	<p>Invalid Order : Details Unavailable</p>
<?php
}
?>

