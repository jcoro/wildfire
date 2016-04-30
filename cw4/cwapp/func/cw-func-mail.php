<?php 
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-mail.php
File Date: 2012-07-07
Description: Displays order management table
==========================================================
*/
// set global mail variables 
$mailtime = CWtime();
$mailTimeStamp = date($_ENV["application.cw"]["globalDateMask"], $mailtime) . ' ' . date("H:i", $mailtime);
$_ENV["request.cw"]["mailDate"] = date("r", $mailtime);
// // ---------- // CWsendMail : Send email message(s) // ---------- // 
function CWsendMail($mail_body,$mail_subject,$mail_address_list) {
	$mailResults = array();
	$mailCt = 0;
	$s = '';
	// set up mail contents 
	$mailcontent = CWmailContents(trim($mail_body),trim($mail_subject));	
	$mailAddresses = $mail_address_list;
	if (!is_array($mailAddresses) && strlen($mailAddresses)) $mailAddresses = explode(",", $mailAddresses);
	else if (!is_array($mailAddresses)) $mailAddresses = array();
	// loop address list, delivering mail and tracking success or errors 
	foreach ($mailAddresses as $key => $aa) {
		try {
			// verify mail valid 
			if(isValidEmail(trim($aa))) {
				$mailSuccess = false;
				if(strlen(trim($_ENV["application.cw"]["mailSmtpServer"]))) {
					$from = $_ENV["application.cw"]["companyName"]." <".$_ENV["application.cw"]["companyEmail"].">";
					$to = trim($aa);
					$subject = trim($mail_subject);
					$failto = $_ENV["application.cw"]["developerEmail"];
					// multipart message (html/text) 
					if($_ENV["application.cw"]["mailMultipart"] == true) {
							// text part 
						$mailSuccess = CWsendEmail($to,$from,$subject,$mailcontent["messageText"]."|cwmultipartseparator|".$mailcontent["messageHtml"], true);	
					} else {
						$mailSuccess = CWsendEmail($to,$from,$subject,$mailcontent["messageText"], false);
					}
				// if server not specified, use shorter attributes 
				} else {
					$from = $_ENV["application.cw"]["companyName"]." <".$_ENV["application.cw"]["companyEmail"].">";
					$to = $aa;
					$subject = trim($mail_subject); 
					$failto = $_ENV["application.cw"]["developerEmail"];
					// multipart message (html/text) 
					if($_ENV["application.cw"]["mailMultipart"] == true) {
							/// text part 
						$mailSuccess = CWsendEmail($to,$from,$subject,$mailcontent["messageText"]."|cwmultipartseparator|".$mailcontent["messageHtml"], true);
							// plain text only 
					} else {
						$mailSuccess = CWsendEmail($to,$from,$subject,$mailcontent["messageText"], false);
					}
				}
				if ($mailSuccess && is_array($mailSuccess) && !$mailSuccess["success"]) {
					$mailResults[] = $mailSuccess["error"];
				}
			// if email address is not valid 
			} else {
				throw new Exception($aa." is not a valid email address");
			}
			$mailCt = $mailCt + 1;
		// catch errors, add error to results 
		}
		catch(Exception $e) {
			$mailResults[] = 'Error with address: '.$aa;
		}	
	}
	// add success message with number sent 
	if($mailCt >0) {
		if($mailCt == 1) {
			$s = '';
		} else {
			$s = 's';
		}
		$mailResults[] = $mailCt." message".$s." sent";
	}
	// return the results as a string 
	return $mailResults;
}
		
// // ---------- // CWmailContents : assembles email message from provided components // ---------- // 
function CWmailContents($mail_body,$mail_subject) {
	global $mailTimeStamp;
	$mailBodyText = $mail_body;
	$mailMessageText = '';
	$mailBodyHtml = CWhtmlText($mailBodyText);
	$mailMessageHtml = '';
	$lineBr = chr(10).chr(13);
	$mailMessageText ="";
	if(strlen(trim($_ENV["application.cw"]["mailHeadText"]))) {
		$mailMessageText.= $_ENV["application.cw"]["mailHeadText"];
		$mailMessageText.=$lineBr;
	}
	$mailMessageText.=$mailBodyText;
	if(strlen(trim($_ENV["application.cw"]["mailFootText"]))) {
		$mailMessageText.=$lineBr;
		$mailMessageText.=$_ENV["application.cw"]["mailFootText"];
	}
	$mailMessageText .= $lineBr."Sent: ".$mailTimeStamp.$lineBr;
	if($_ENV["application.cw"]["mailMultipart"]) {
		$mailMessageHtml = '<div id="wrapper">
			<table id="mailcontent" cellpadding="0" cellspacing="0" align="center">';
		// header 
		if(strlen(trim($_ENV["application.cw"]["mailHeadHtml"]))) {
			$mailMessageHtml .= '
				<tr>
					<td id="header">'.$_ENV["application.cw"]["mailHeadHtml"].'</td>
				</tr>';
		}
		$mailMessageHtml .= '
				<tr>
					<td class="content">'.$mailBodyHtml.'</td>
				</tr>';
		// footer 
		if(strlen(trim($_ENV["application.cw"]["mailFootHtml"]))) {
		$mailMessageHtml .= '
				<tr>
                    <td id="footer">'.$_ENV["application.cw"]["mailFootHtml"].'</td>
				</tr>';
		}
		// TimeStamp 
		$mailMessageHtml .= '
				<tr>
					<td id="footnotes">
						<p class="timestamp">Sent: '.$mailTimeStamp.'</p>
					</td>
				</tr>
			</table>';
		// / end wrapper div 
		$mailMessageHtml .= '
		</div>';
		// add head and body sections (assume both missing if no <head> tag is present) 
		if(strstr($mailMessageHtml, '<head>') === false) {
			// add meta tags to message 
			$mailHtmlMeta = '<meta content="'.$_ENV["application.cw"]["companyName"].$mailTimeStamp.'">
<meta http-equiv="Content-type" content="text/html; charset=us-ascii">
';
			// also add css to body (some mail services strip off the 'head') 
			$mailMessageHtml =  '<head>'.'<title>'.$mail_subject.'</title>'.$mailHtmlMeta.'</head><body>'.CWmailCss().$mailMessageHtml.'</body>';
		}
		// wrap in html tags 
		if(strstr($mailMessageHtml, '<html>') === false) {
			$mailMessageHtml =  '<html>'.$mailMessageHtml.'</html>';
		}
		// add doctype  
		if(strstr($mailMessageHtml, '!DOCTYPE') === false) {
			$mailMessageHtml =  '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'.$mailMessageHtml;
		}
	}
	$mailcontent = array();
	$mailcontent["messageText"] = $mailMessageText;
	$mailcontent["messageHtml"] = $mailMessageHtml;
	return $mailcontent;
}
		
// // ---------- // CWmailCss : contains inline styles to be applied to html formatted email // ---------- // 
function CWmailCss() {
	$mailcss = '
<style type="text/css" media="screen">
* {
padding: 0;
margin: 0;
}
html {
font-size: 62.5%;
}
/* --------- text align center on body works for older IE centering ---*/
body {
margin: 0;
padding: 0;
font-family: Verdana, Arial, Helvetica, sans-serif;
text-align: center;
line-height: 1;
background: #F5f5f5;
}
/* ------ WRAPPER:------- */
#wrapper {
margin: 12px auto;
width: 480px;
text-align:center;
}
/* ----------- MAIN TABLE text align left fixes text within body which is centered ----------*/
table#mailcontent{
border-collapse:collapse;
text-align:left;
width:480px;
}
table#mailcontent td{
border:0;
padding:0 28px;
}
/* ---------- STANDARD TEXT ---------*/
p, ul{
font-size: 11px;
margin: 5px 0 10px 0;
line-height: 15px;
font-family: Verdana, Arial, Helvetica, sans-serif;
color: #232323;
text-align: left;
}

/* ----------- HEADER ----------*/
table#mailcontent td#header {
text-align: center;
background-color: #e3e8ec;
color:#232323;
padding:0;
}
table#mailcontent td#header a {
text-decoration: none;
}
table#mailcontent td#header p {
margin:0;
text-align:center;
padding:0;
}
table#mailcontent td#header h1,
table#mailcontent td#header h2{
text-align:center;
color:#232323;
}
table#mailcontent td#header a:link,
table#mailcontent td#header a:visited,
table#mailcontent td#header a:hover,
table#mailcontent td#header a:active{
text-decoration:none;
color:#004080;
}

/*------- CONTENT -------*/
table#mailcontent td.content {
background-color: #FFFFFF;
padding-top:12px;
padding-bottom:12px;
}
p.timestamp {
text-align: center;
font-size: 10px;
color: #FFFFFF;
}
p.sentinfo {
text-align: left;
font-size: 10px;
font-style: italic;
margin-left: 28px;
color: #f9f9f9;
line-height:1.4em;
}
h1 {
font-size: 16px;
color: #232323;
margin:0;
line-height:1.2em;
text-align:left;
}
h2 {
font-size: 18px;
color: #232323;
margin: 8px 0 8px 0;
padding-bottom: 4px;
line-height:1.2em;
text-align:left;
}
h3 {
font-size: 12px;
font-weight:bold;
color: #232323;
margin: 8px 0 8px 0;
padding-bottom: 4px;
line-height:1.2em;
text-align:left;
}
h3 a:link, h3 a:visited, h3 a:active, h3 a:hover{
color: #004080;
}
/*------- FOOTER -------*/
#footer {
text-align: center;
padding: 4px 0;
background-color: #e3e8ec;
}
#footer p {
text-align: center;
}
</style>';
	return $mailcss;
}
		
// CWhtmlText : translate plain text into html with links for multipart email messages // ---------- // 
function CWhtmlText($text_content) {
	$tempText = preg_replace("/(http:\/\/)?(\w+([\.\-]\w+)+\.[a-z]{2,4}(\/\w+([\-\.\/]\w+)*(\?\w+([\-\.\/\%\&\#\=]\w+)*)?)?)/i","<a href=\"http://\\2\">\\2</a>",$text_content);
	$tempText = preg_replace("/[\n\r]/i","<br>",$tempText);
	$tempText = preg_replace("/<br>(.*)<br><br>/i","<p>\\1</p>","<br>".$tempText."<br><br>");
	$cleanText = preg_replace("/<br><br>/i",'<br>',$tempText);
	return $cleanText;
}

// // ---------- Email Contents : construct order details for email confirmation ---------- // 
function CWtextOrderDetails($order_id=0,$show_payments=0,$show_tax_id=null) {
	if ($show_tax_id === null) $show_tax_id = $_ENV["application.cw"]["taxDisplayID"];
	$orderQuery = '';
	$optionsQuery = '';
	$paymentsQuery = '';
	$orderDetails = '';
	$lineBr = chr(13);
        $orderDownloads = '';
	$orderQueryS="SELECT
					ss.shipstatus_name,
					o.*,
					c.customer_first_name,
					c.customer_last_name,
					c.customer_id,
					c.customer_email,
					p.product_name,
					p.product_id,
					p.product_custom_info_label,
					p.product_out_of_stock_message,
					s.sku_id,
					s.sku_merchant_sku_id,
                    s.sku_download_id,
					sm.ship_method_name,
					os.ordersku_sku,
					os.ordersku_unique_id,
					os.ordersku_quantity,
					os.ordersku_unit_price,
					os.ordersku_sku_total,
					os.ordersku_tax_rate,
					os.ordersku_discount_amount,
					(o.order_total - (o.order_tax + o.order_shipping + o.order_shipping_tax)) as order_SubTotal
				FROM (
					cw_products p
					INNER JOIN cw_skus s
					ON p.product_id = s.sku_product_id)
					INNER JOIN ((cw_customers c
						INNER JOIN (cw_order_status ss
							RIGHT JOIN (cw_ship_methods sm
								RIGHT JOIN cw_orders o
								ON sm.ship_method_id = o.order_ship_method_id)
							ON ss.shipstatus_id = o.order_status)
						ON c.customer_id = o.order_customer_id)
						INNER JOIN cw_order_skus os
						ON o.order_ID = os.ordersku_order_id)
					ON s.sku_id = os.ordersku_sku
				WHERE o.order_ID = '".CWqueryParam($order_id)."'
				ORDER BY
					p.product_name,
					s.sku_sort,
					s.sku_merchant_sku_id";
	$orderQuery = CWqueryGetRS($orderQueryS);
	// if including payments 
	if($show_payments) {
		$paymentsQueryS = "SELECT *
							FROM cw_order_payments
							WHERE order_id = '".CWqueryParam($order_id)."'";
		$paymentsQuery = CWqueryGetRS($paymentsQueryS);
	}
	
	if($orderQuery['totalRows']) {
		$orderDetails = "Order ID:".$orderQuery['order_id'][0];
		if ($show_tax_id && strlen(trim($_ENV["application.cw"]["taxIDNumber"]))) {
			$orderDetails .= $lineBr."
".$_ENV["application.cw"]["taxSystemLabel"]." ID: ".trim($_ENV["application.cw"]["taxIDNumber"]);
		}
		$orderDetails .= "

Ship To
====================
".$orderQuery["order_ship_name"][0].$lineBr."
";
		if (strlen(trim($orderQuery["order_company"][0]))) {
			$orderDetails .= $orderQuery["order_company"][0].$lineBr;
		}
		$orderDetails .= "
".$orderQuery["order_address1"][0].$lineBr."
";
		if (strlen(trim($orderQuery["order_address2"][0]))) {
			$orderDetails .= $orderQuery["order_address2"][0].$lineBr;
		}
		$orderDetails .= "
".$orderQuery["order_city"][0].", ".$orderQuery["order_state"][0].$lineBr.$orderQuery["order_zip"][0]."
".$orderQuery["order_country"][0]."
".$lineBr."
Order Contents
====================
";
		$lastOrderSku = "";
		for($i=0;$i<$orderQuery["totalRows"];$i++) {
			$lastOrderSku = $orderQuery["ordersku_sku"][$i];
                        // set flag for download text if any downloadable items exist
                        if (strlen(trim($orderQuery["sku_download_id"][$i]))) {
                            $orderDownloads = true;
                        }
			$optionsQueryS = "SELECT cw_option_types.optiontype_name, cw_options.option_name
								FROM (cw_option_types
								INNER JOIN cw_options
								ON (cw_option_types.optiontype_id = cw_options.option_type_id)
								AND (cw_option_types.optiontype_id	= cw_options.option_type_id))
								INNER JOIN cw_sku_options
								ON cw_options.option_id	= cw_sku_options.sku_option2option_id
								WHERE cw_sku_options.sku_option2sku_id=".CWqueryParam($orderQuery["ordersku_sku"][$i])."
								ORDER BY cw_option_types.optiontype_name, cw_options.option_sort";
			$optionsQuery = CWqueryGetRS($optionsQueryS);
			if($i > 0) {
				$orderDetails .= $lineBr;
			}
			$orderDetails .= $orderQuery["product_name"][$i]." (".$orderQuery['sku_merchant_sku_id'][$i].")";
			for($j=0;$j<$optionsQuery["totalRows"];$j++) {
				$orderDetails .= $lineBr.$optionsQuery['optiontype_name'][$j].": ".$optionsQuery['option_name'][$j]."
";
			}
			$orderDetails .= "
Quantity: ".$orderQuery["ordersku_quantity"][$i]."
Price: ". cartweaverMoney($orderQuery["ordersku_unit_price"][$i]);
			if($_ENV["application.cw"]["discountDisplayLineItem"] && $orderQuery["ordersku_discount_amount"][$i] > 0) {
				$orderDetails.="
Discount: ".cartweaverMoney($orderQuery["ordersku_discount_amount"][$i]);
			}
			if($_ENV["application.cw"]["taxDisplayLineItem"] && $orderQuery["ordersku_tax_rate"][$i]) {
				$orderDetails .= "
".$_ENV["application.cw"]["taxSystemLabel"].": ".cartweaverMoney($orderQuery["ordersku_tax_rate"][$i],'local');
			}
			$orderDetails.= "
Item Total: ".cartweaverMoney($orderQuery['ordersku_sku_total'][$i]).$lineBr;
			while ($i < $orderQuery["totalRows"] && $lastOrderSku == $orderQuery["ordersku_sku"][$i]) $i++;
			$i--;
		}
		$orderDetails.="

Order Totals
====================
Subtotal: ".cartweaverMoney($orderQuery["order_SubTotal"][0] + $orderQuery["order_discount_total"][0])."
";
		if ($orderQuery["order_discount_total"][0] > 0) {
			$orderDetails .= "
Discounts: - ".cartweaverMoney($orderQuery["order_discount_total"][0]);
		}
		$orderDetails .= "
";
		if ($orderQuery["order_tax"][0] > 0) {
			$orderDetails .= $_ENV["application.cw"]["taxSystemLabel"].": ".cartweaverMoney($orderQuery["order_tax"][0]);
		}
		$orderDetails .= "
Shipping";
		if (strlen(trim($orderQuery["ship_method_name"][0]))) {
			$orderDetails .= " (".$orderQuery["ship_method_name"][0].")";
		}
		$orderDetails .= ": ".cartweaverMoney($orderQuery["order_shipping"][0] + $orderQuery["order_ship_discount_total"][0])."
";
		if ($orderQuery["order_ship_discount_total"][0] > 0) {
			$orderDetails .= "Shipping Discount: - ".cartweaverMoney($orderQuery["order_ship_discount_total"][0]);
		}
		$orderDetails .= "
";
		if ($orderQuery["order_shipping_tax"][0] > 0) {
			$orderDetails .= "Shipping ".$_ENV["application.cw"]["taxSystemLabel"].": ".cartweaverMoney($orderQuery["order_shipping_tax"][0]);
		}
		$orderDetails .= $lineBr."
ORDER TOTAL: ".cartweaverMoney($orderQuery["order_total"][0])."
";
		if ($orderQuery["order_status"][0] == 4) {
			$orderDetails .= "
Shipped: ".cartweaverDate($orderQuery["order_ship_date"][0])."
";
			if ($orderQuery["order_ship_tracking_id"][0] != "") {
				$orderDetails .= "
Tracking Number: ".$orderQuery["order_ship_tracking_id"][0]."
";
			}
		}
		// /end if shipped 
		if ($show_payments && $paymentsQuery["totalRows"]) { // if showing payments 
			$orderDetails .= $lineBr."
Payment Details
==================== ";
			for ($i=0; $i<$paymentsQuery["totalRows"]; $i++) {
				$orderDetails .= "
Payment Method: ".$paymentsQuery["payment_method"][$i]."
Amount: ".cartweaverMoney($paymentsQuery["payment_amount"][$i]).$lineBr;
			}
			$orderDetails .= "
";
		}
		// /end payments 
		// ordercomments 
		if (strlen(trim($orderQuery["order_comments"][0]))) {
			$orderDetails .= "
Order Comments:".$lineBr."====================
".$orderQuery["order_comments"][0]."
".$lineBr;
		}
                // download text 
                $dlstatArr = $_ENV["application.cw"]["appDownloadStatusCodes"];
                if (!is_array($dlstatArr) && strlen($dlstatArr)) $dlstatArr = explode(",", $dlstatArr);
                else if (!is_array($dlstatArr)) $dlstatArr = array();
                if ($orderDownloads && $_ENV["application.cw"]["appDownloadsEnabled"] && in_array($orderQuery["order_status"][0], $dlstatArr)) {
                    $orderDetails .= "
".$lineBr.$lineBr."Downloads:".$lineBr."====================
Log in to your account at ".$_ENV["application.cw"]["appPageAccountUrl"]." to download your purchased items.
";
                }
                // end download text 
	}
	// /end if order exists 
	// trim extra whitespace 
	$orderText = $lineBr . $lineBr . trim($orderDetails) . $lineBr . $lineBr;
	// return text 
	return $orderText;
}


// // ---------- // Customer Password Email // ---------- // 
function CWtextPasswordReminder($customer_id,$login_url,$company_name=NULL,$company_email=NULL,$company_phone=NULL,$company_url=NULL) {
	if($company_name === null) { $company_name=$_ENV["application.cw"]["companyName"];  }
	if($company_email === null) { $company_email=$_ENV["application.cw"]["companyEmail"];  }
	if($company_phone === null) { $company_phone=$_ENV["application.cw"]["companyPhone"];  }
	if($company_url === null) { $company_url=$_ENV["application.cw"]["companyURL"];  }
	$messageText = '';
	$messageContent = '';
	$rsPasswordLookup = ''; 
	$lineBr = chr(10).chr(13);
	if(strlen(trim($customer_id)) && $customer_id !== 0 && $customer_id !== "0") {
		// QUERY: get customer details 
		$rsPasswordLookupS = "SELECT customer_id, customer_email, customer_username, customer_password
								FROM cw_customers
								WHERE ".$_ENV["application.cw"]["sqlLower"]."(customer_id) = '".strtolower(CWqueryParam($customer_id))."'";
		$rsPasswordLookup = CWqueryGetRS($rsPasswordLookupS);
		$messageContent = 'Username: '.$rsPasswordLookup['customer_username'][0].$lineBr.'
Password: '.$rsPasswordLookup['customer_password'][0].$lineBr.'
Log in to your account here:'.$lineBr.$login_url.$lineBr;
		if(strlen(trim($company_name))) { $messageContent.= $company_name.$lineBr;}
		if(strlen(trim($company_email))) { $messageContent.= $company_email.$lineBr;}
		if(strlen(trim($company_url))) {  $messageContent.= $company_url.$lineBr;}
		if(strlen(trim($company_phone))) {  $messageContent.= $company_phone.$lineBr;}
	}
	// trim extra whitespace 
	$messageText = trim($messageContent);
	// return text 
	return $messageText;
}



//-------------------------- Send PHP Email -----------------------------------  
function CWsendEmail($to, $from, $subject, $body, $isMulti) {
	if (isset($_ENV["application.cw"]["mailSmtpServer"]) && $_ENV["application.cw"]["mailSmtpServer"] && (!isset($_ENV["request.cw"]["pearInstalled"]) || !$_ENV["request.cw"]["pearInstalled"])) {
		$_SESSION["cw"]["errorsOff"] = true;
		try {
			$_ENV["request.cw"]["pearInstalled"] = include_once("Mail.php");
		} catch (Exception $e) {}
		if (!class_exists("Mail") && !$_ENV["request.cw"]["pearInstalled"]) {
			try {
				$pearPath = $_SERVER["DOCUMENT_ROOT"];
				$systemSlash = ((strpos($pearPath, "/") !== false) ? "\\" : "/" );
				if (substr($pearPath, -1) == $systemSlash) $pearPath = substr($pearPath, 0, strlen($pearPath)-1);
				$pearPathArr = explode($systemSlash, $pearPath);
				$newPath = "";
				$pearPath = "";
				while (sizeof($pearPathArr)) {
					$newPath = implode($systemSlash, $pearPathArr);
					$testPath = realpath($newPath);
					$testPath .= ( (substr($testPath, -1) != $systemSlash) ? $systemSlash : "" );
					if (file_exists($testPath."php/Mail.php")) {
						$pearPath = $testPath."php/Mail.php";
						break;
					}
					array_pop($pearPathArr);
				}
				if ($pearPath) {
					$_ENV["request.cw"]["pearInstalled"] = include_once($pearPath);
				} else {
					$_ENV["request.cw"]["pearInstalled"] = include_once("Mail.php");
				}
			} catch (Exception $e) {}
		}
		$_ENV["request.cw"]["pearInstalled"] = class_exists("Mail");
		$_SESSION["cw"]["errorsOff"] = false;
	}
	$boundary = uniqid('cwm');
	$from_address = $from;
	if (strpos($from, "<") !== false) {
		$from_address = substr($from, strpos($from, "<") + 1);
		$from_endpos = strpos($from_address, ">", strlen($from_address)-2);
		if ($from_endpos !== false) {
			$from_address = substr($from_address, 0, $from_endpos);
		}
	}
	$lineBr = "\n";
	if (isset($_SERVER["SERVER_SOFTWARE"]) && stripos($_SERVER["SERVER_SOFTWARE"], "microsoft") !== false) {
		$lineBr = "\r\n";
	}
	$server = $_ENV["application.cw"]["mailSmtpServer"];
	$fromArr = explode("@", $from_address);
	$message_id = "<".md5(uniqid(microtime()))."@";
	if (sizeof($fromArr) == 1) $message_id .= $fromArr[0];
	else $message_id .= $fromArr[1];
	$message_id .= ">";
	$cwMailHead = array();
	$cwMailContent = array();
	$usingPear = false;
	if ($server) {
		$usingPear = $_ENV["request.cw"]["pearInstalled"];
	}
	if ($usingPear) {
		if ($isMulti) {
			$cwMailHead["MIME-Version"] = "1.0";
			$cwMailHead["Content-Type"] = "multipart/alternative;boundary=".$boundary;
		}
		$cwMailHead["From"] = $from;
		$cwMailHead["To"] = $to;
		$cwMailHead["Subject"] = $subject;
		$cwMailHead["Reply-To"] = $from_address;
		$cwMailHead["Return-Path"] = $from_address;
		$cwMailHead["X-Sender"] = $from;
		$cwMailHead["X-Priority"] = "3";
		$cwMailHead["Date"] = $_ENV["request.cw"]["mailDate"];
		$cwMailHead["Message-ID"] = $message_id;
		$username = $_ENV["application.cw"]["mailSmtpUsername"];
		$password = $_ENV["application.cw"]["mailSmtpPassword"];
		$port = "";
		if (strpos($server, ":") !== false) {
			$port = substr($server, strpos($server, ":")+1);
			$server = substr($server, 0, strpos($server, ":"));
		}
		$cwMailParams = array();
		$cwMailParams["host"] = $server;
		if ($port) $cwMailParams["port"] = $port;
		if ($username || $password) $cwMailParams["auth"] = true;
		if ($username) $cwMailParams["username"] = $username;
		if ($password) $cwMailParams["password"] = $password;
		if ($isMulti) {
			$cwMailContent[] = "This is a MIME encoded message.";
			$cwMailContent[] = "";
			$cwMailContent[] = "--".$boundary;
			$cwMailContent[] = "Content-Type: text/plain;charset=utf-8";
			$mailSplit = explode("|cwmultipartseparator|", $body);
			$cwMailContent[] = preg_replace("/\n/", $lineBr, preg_replace("/\r/", "", $mailSplit[0]));
			$cwMailContent[] = "";
			$cwMailContent[] = "--".$boundary;
			$cwMailContent[] = "Content-Type: text/html;charset=utf-8";
			$cwMailContent[] = "";
			$cwMailContent[] = preg_replace("/\n/", $lineBr, preg_replace("/\r/", "", $mailSplit[1]));
			$cwMailContent[] = "";
			$cwMailContent[] = "--".$boundary."--";
		} else {
			$cwMailContent[] = preg_replace("/\n/", $lineBr, preg_replace("/\r/", "", $body));
		}
		$headStr = "";
		foreach ($cwMailHead as $key => $value) {
			if ($headStr) $headStr .= $lineBr;
			$headStr .= $key.": ".$value;
		}
		$returnObj = array("success" => false, "header" => $headStr, "content" => implode($lineBr, $cwMailContent), "error" => "");
		try {
			$mail_object = Mail::factory("smtp", $cwMailParams); 
			$mailResult = $mail_object->send($to, $cwMailHead, $returnObj["content"]);
			$returnObj["success"] = true;
			if (PEAR::isError($mailResult)) {
				$returnObj["success"] = false;
				$returnObj["error"] = $mailResult->getMessage();
			}
		} catch (Exception $e) {
			$returnObj["success"] = false;
			$returnObj["error"] = $e->getMessage();
		}
	} else {
		$cwMailHead[] = "MIME-Version: 1.0";
		if ($isMulti) {
			$cwMailHead[] = "Content-Type: multipart/alternative;boundary=".$boundary;
		} else {
			$cwMailHead[] = "Content-Type: text/plain";
		}
		$cwMailHead[] = "From: ".$from;
		$cwMailHead[] = "Subject: ".$subject;
		$cwMailHead[] = "Reply-To: ".$from_address;
		$cwMailHead[] = "Return-Path: ".$from_address;
		$cwMailHead[] = "X-Sender: ".$from;
		$cwMailHead[] = "X-Priority: 3";
		$cwMailHead[] = "Date: ".$_ENV["request.cw"]["mailDate"];
		$cwMailHead[] = "Message-ID: ".$message_id;
		if ($isMulti) {
			$cwMailContent[] = "This is a MIME encoded message.";
			$cwMailContent[] = "";
			$cwMailContent[] = "--".$boundary;
			$cwMailContent[] = "Content-Type: text/plain;charset=utf-8";
			$mailSplit = explode("|cwmultipartseparator|", $body);
			$cwMailContent[] = preg_replace("/\n/", $lineBr, preg_replace("/\r/", "", $mailSplit[0]));
			$cwMailContent[] = "";
			$cwMailContent[] = "--".$boundary;
			$cwMailContent[] = "Content-Type: text/html;charset=utf-8";
			$cwMailContent[] = "";
			$cwMailContent[] = preg_replace("/\n/", $lineBr, preg_replace("/\r/", "", $mailSplit[1]));
			$cwMailContent[] = "";
			$cwMailContent[] = "--".$boundary."--";
		}
		else {
			$cwMailContent[] = preg_replace("/\n/", $lineBr, preg_replace("/\r/", "", $body));
		}
		$returnObj = array("success" => true, "header" => implode($lineBr, $cwMailHead), "content" => implode($lineBr, $cwMailContent), "error" => "");
		try {
			mail($to, $subject, $returnObj["content"], $returnObj["header"]);
		} catch (Exception $e) {
			$returnObj["success"] = false;
			$returnObj["error"] = $e->getMessage();
		}
	}
	return $returnObj;
}
?>