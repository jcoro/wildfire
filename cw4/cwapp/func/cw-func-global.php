<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-global.php
File Date: 2012-07-03
Description: handles global Cartweaver functions
See function notes for examples of use
==========================================================
*/
/* ---------- List Columns in a Query: CWlistQueryColumns() ---------- */
if (!function_exists("CWlistQueryColumns")) {
function CWlistQueryColumns($query_object) {
	$tArr = array();
	foreach ($query_object as $colname => $vals) {
		if ($colname != "totalRows") {
			$tArr[] = $colname;
		}
	}
	return implode(",", $tArr);
}
}
// // ---------- Serialize URL Variables: CWserializeURL() ---------- // 
if (!function_exists("CWserializeURL")) {
function CWserializeURL($var_list, $base_url=NULL) {
	if (!is_array($var_list)) {
		$var_list = explode(",", $var_list);
	}
	$varCt = count($var_list);
	// set the base for our url 
	if(strlen(trim($base_url))) {
		$persistURL = trim($base_url) . '?';
	} else {
		$persistURL = '';
	} 
	// if we have some vars to work with 
	if($varCt) {
		$persistQS = '';
		$loopCt = 0; 
		// loop the list 
		foreach ($var_list as $key => $vv) {
			// this param keeps from breaking on missing vars 
			if(!isset($_GET[trim($vv)])) 
			    { $_GET[trim($vv)] = ''; }
			$QSadd = trim($vv) . "=" . $_GET[trim($vv)];
			if($loopCt != 0) {
				$QSadd = '&' . $QSadd; 
			}
			$persistQS = $persistQS . $QSadd;
			$loopCt++;
		}
		$persistURL = $persistURL . $persistQS; 
	}
	return $persistURL;
}
}
		
// // ---------- Remove URL Variables from Persisted URL: CWremoveUrlVars()---------- // 
if (!function_exists("CWremoveUrlVars")) {
function CWremoveUrlVars($omit_list="", $parse_url=null, $return_content="vars") {
	if ($parse_url === null) $parse_url = $_SERVER['QUERY_STRING'];
	$qsVarList = array();
	$newItem = '';
	$varName = '';
	$varVal = '';
	$parseUrlList1 = explode("?", $parse_url);
	$parseUrlList2 = explode("&", $parseUrlList1[sizeof($parseUrlList1)-1]);
	$omitList = explode(",", $omit_list);
	foreach ($parseUrlList2 as $key => $vv) {
		$vvArr = explode("=", $vv);
		$varName = $vvArr[0];
		$varVal = $vvArr[sizeof($vvArr)-1];
		// if not an omitted value 
		if (!in_array($varName, $omitList)) {
			// if showing values 
			if ($return_content == 'vals') {
				$newItem = $varVal;
			} else {
				// otherwise, default = show vars as a list 
				$newItem = $varName;
			}
			// avoid duplicates 
			if (!in_array($newItem, $qsVarList)) {
				$qsVarList[] = $newItem;
			}
		}
	}
	return implode(",", $qsVarList);
}
}

// // ---------- // Create Navigation Menu from Listed Array of Links and URLs // ---------- // 
if (!function_exists("CWcreateNav")) {
function CWcreateNav($page_list,$current_url=NULL,$nav_id="",$count_delimiters="<span>(,)</span>",$list_delimiter=",",$nav_class="",$show_link_titles=true) {
	if ($current_url === null) $current_url = $_ENV["request.cw"]["thisPageQS"];
	if ($count_delimiters === null) $count_delimiters = "<span>(,)</span>";
	$navHTML = '';
	$firstChild = 1;
	$isStarted = 0;
	$thisLink = '';
	$thisLinkGroup = '';
	$lastItem = 0;
	$nextItem = '';
	$nextCount = 0;
	$selectedGroup = '';
	$newLinkText = '';
	$thisLinkText = '';
	$thisTitleText = '';
	$thisClass = '';
	$qsList = '';
	// clean trailing delimiter from page list
	$page_list = trim($page_list);
	if (substr($page_list, strlen($page_list)-1) == trim($list_delimiter)) {
		$page_list = substr($page_list, 0, strlen($page_list)-1);
	}
	// clean trailing ? from provided url 
	$current_url = trim($current_url);
	if (substr($current_url, strlen($current_url)-1) == "?") {
		$current_url = substr($current_url, 0, strlen($current_url)-1);
	}
	// if current url has a query string, remove unwanted vars 
	$currentUrlArr = explode("?", $current_url);
	if (strstr($current_url, "?") !== false && strlen($currentUrlArr[sizeof($currentUrlArr)-1])) {
		// get value of category / secondary from url (remove product from querystring) 
		$qsVars = CWremoveUrlVars('product,addedid',$current_url);
		$qsVals = CWremoveUrlVars('product,addedid',$current_url,'vals');
		$current_url = CWserializeUrl($qsVars,$currentUrlArr[0]);
	}
	// set up menu html 
	// create dynamic 'on' states by looking at the URL of the current page OR current page 'currentNav' variable 
	$linkCount = 1;
	$loopCt = 1;
	// set length of menu for closing final link 
	$menuArr = explode("^", $page_list);
	$menuLen = sizeof($menuArr);
	// loop the list, find selected group
	$pageArr = explode($list_delimiter, $page_list);
	$scriptNameArr = explode("/", $_SERVER['SCRIPT_NAME']);
	foreach ($pageArr as $key => $pl) {
		$pl = trim($pl);
		$plArr = explode("|", $pl);
		if (sizeof($plArr) >= 3) {
			// get the link 
			$thisLink = trim($plArr[1]);
			$thisLinkgroup = trim($plArr[0]);
			if ((!isset($current_url) && trim($scriptNameArr[sizeof($scriptNameArr)-1]) == trim($thisLink)) || (isset($current_url) && trim($current_url) == trim($thisLink))) {
				$selectedGroup = $thisLinkgroup;
			}
			$loopCt++;
		}
	}
	// loop the list, create links 
	$parentClass="CWnav";
	if (strlen(trim($nav_class))) {
		$parentClass .= ' '.trim($nav_class);
	}
	$navHTML .= '<ul class="'.$parentClass.'"';
	if (strlen(trim($nav_id))) {
		$navHTML .= ' id="'.trim($nav_id).'"';
	}
	$navHTML .= '>';
	$countDelimArr = explode(",", $count_delimiters);
	$lastLinkCount = null;
	$isStarted = 0;
	foreach ($pageArr as $key => $pl) {
		// if a valid link 
		$plArr = explode("|", $pl);
		if (sizeof($plArr) >= 3) {
			$thisLinkCount = trim($plArr[0]);
			$thisLink = trim($plArr[1]);
			$newLinkText = trim($plArr[sizeof($plArr)-1]);
			$thisLinkText = str_replace("]", $countDelimArr[1], str_replace("[", $countDelimArr[0], $newLinkText));
			$newLTArr = explode("[", $newLinkText);
			$thisTitleText = trim($newLTArr[0]);
			// get the counter for the next item 
			if ($menuLen > $linkCount) {
				$nextItem = $pageArr[$linkCount];
				$nextPLArr = explode("|", $nextItem);
				$nextCount = trim($nextItem[0]);
			} else {
				$nextCount = 0;
				$lastItem = 1;
			}
			// set up the class for each link 
			if ($linkCount == 1) {
				$thisClass = "firstLink";
			} else {
				$thisClass = "";
			}
			if ($selectedGroup == $thisLinkCount && $thisLinkCount != $lastLinkCount) {
				$thisClass .= ' currentLink';
			}
			// if this is the first link of the menu 
			if ($isStarted == 0) {
				$navHTML .= "
				<li>";
				$isStarted = 1;
			// all other links 
			} else {
				// if in the same group, create a sublink 
				if ($thisLinkCount == $lastLinkCount) {
					// if the primary sublink 
					if ($firstChild == 1) {
						$navHTML .= "
						<ul>
							<li>";
						$firstChild = 0;
					// if not the first sublink 
					} else {
						$navHTML .= "</li>
						<li>";
					}
					// /end if first sublink 
				// if not in same group, and not the primary link, close full list 
				} else if ($firstChild == 0) {
					$navHTML .= "</li>
					</ul>
				</li>
				<li>";
					$firstChild = 1;
				// if not in same group, and it is the primary link  
				} else if ($firstChild == 1) {
					$navHTML .= "</li>
						<li>";
				}
				// /end if in same group 
			}
			// end first link/other links 
			// set current nav link 
			if (isset($current_url) && strstr($thisClass, 'currentLink') === false) {
				// if link matches currentlink variable , add marker class 
				if (trim($current_url) == trim($thisLink)) {
					$thisClass .= ' currentLink';
				}
			}
			// create the link 
			$navHTML .= '<a href="'.$thisLink.'"';
			if (strlen(trim($thisClass))) {
				$navHTML .= ' class="'.trim($thisClass).'"';
			}
			if ($show_link_titles) {
				$navHTML .= ' title="'.$thisTitleText.'"';
			}
			$navHTML .= '>'.$thisLinkText.'</a>';
			$lastLinkCount = $thisLinkCount;
		}
		// if last link 
		if ($linkCount == $menuLen && $firstChild == 0) {
			$navHTML .= "</li>
					</ul>";
		}
		$linkCount++;
		// /end if valid link 
	}
	$navHTML .= "</li>
</ul>";
	// end list
	return $navHTML;
}
}
// // ---------- // Create Horizontal Links from Listed Array of Links and URLs // ---------- // 
// //NOTES:
//The first part of each string is a numeric 'group' for nested lists
//See CWcreateNav function for example of links formatting
//
if (!function_exists("CWcreateLinks")) {
function CWcreateLinks($page_list, $current_category=null, $current_secondary=null, $link_delimiter=null, $count_delimiters=null, $list_delimiter=null, $show_link_titles=true) {
	if ($current_category === null) $current_category = 0;
	if ($current_secondary === null) $current_secondary = 0;
	if ($link_delimiter === null) $link_delimiter = " | ";
	if ($count_delimiters === null) $count_delimiters = "<span>(,)</span>";
	if ($list_delimiter === null) $list_delimiter = ",";
	if ($show_link_titles === null) $show_link_titles = true;
	$navHTML = '';
	$firstParent = 1;
	$firstChild = 0;
	$lastChild = 0;
	$isStarted = 0;
	$thisLink = '';
	$thisLinkGroup = '';
	$selectedGroup = '';
	$newLinkText = '';
	$thisLinkText = '';
	$thisTitleText = '';
	$thisClass = '';
	$linkCt = 1;
	$loopCt = 1;
	$totalLinksArr = explode(",", $page_list);
	$totalLinks = sizeof($totalLinksArr);
	// set up menu html 
	$navHTML = "";
	// loop the list, find selected group
	$pageListArr = explode($list_delimiter, $page_list);
	$scrNmArr = explode("/", $_SERVER['SCRIPT_NAME']);
	foreach ($pageListArr as $key => $pl) {
		$pl = trim($pl);
		$plArr = explode("|", $pl);
		if (sizeof($plArr) >= 3) {
			// get the link 
			$thisLink = trim($plArr[1]);
			$thisLinkGroup = trim($plArr[0]);
			if ((!isset($current_url) && trim($scrNmArr[sizeof($scrNmArr)-1]) == $thisLink) || (isset($current_url) && trim($current_url) == $thisLink)) {
				$selectedGroup = $thisLinkGroup;
			}
			$loopCt++;
		}
	}
	// loop the list, create links 
	$navHTML .= '<div class="CWlinksNav">
	<div class="CWlinks">
';
	$countDelimArr = explode(",", $count_delimiters);
	$resURLArr = explode("/", $_ENV["request.cwpage"]["urlResults"]);
	$lastLinkCount = null;
	foreach ($pageListArr as $key => $pl) {
		$pl = trim($pl);
		$plArr = explode("|", $pl);
		if (sizeof($plArr) >= 3) {
			$thisLinkCount = trim($plArr[0]);
			$thisLink = trim($plArr[1]);
			$newLinkText = trim($plArr[sizeof($plArr)-1]);
			$thisLinkText = str_replace("]", $countDelimArr[1], str_replace("[", $countDelimArr[0], $newLinkText));
			$nltArr = explode("[", $newLinkText);
			$thisTitleText = trim($nltArr[0]);
			// set up the class for each link 
			if ($linkCt == 1) {
				$thisClass = "firstLink";
			} else {
				$thisClass = "";
			}
			if ($current_category > 0 && strpos($thisLink, 'category='.$current_category) !== false && strpos($thisClass, 'currentLink') === false && strpos($thisLink, 'secondary=') === false) {
				$thisClass .= ' currentLink';
			} else if ($current_secondary > 0 && strpos($thisLink, 'secondary='.$current_secondary) !== false && strpos($thisClass, 'currentLink') === false) {
				$thisClass .= ' currentLink';
			} else if ($current_category == 0 && strpos($thisLink, $resURLArr[sizeof($resURLArr)-1]) !== false && strpos($thisLink, 'category=') === false && strpos($thisLink, 'secondary=') === false) {
				$thisClass .= ' currentLink';
			}
			if ($isStarted == 0) {
				$isStarted = 1;
			} else {
				if ($thisLinkCount == $lastLinkCount) {
					if ($firstChild == 1) {
						$firstChild = 0;
						$firstParent = 1;
					}
				} else if ($firstParent == 1 && $firstChild == 0) {
					$navHTML .= '</div><div class="CWlinks">
';
					if (strstr($thisClass, 'firstLink') === false) {
						$thisClass .= ' firstLink';
					}
					// prevent false flag of first link 
					if ($current_secondary > 0) {
						$thisClass = str_replace('currentLink','',$thisClass);
					}
					$firstChild = 1;
				}
			}
			if ($thisLinkCount == $lastLinkCount) {
				$navHTML .= $link_delimiter;
			}
			$lastLinkCount = $thisLinkCount;
			$linkCt++;
			$navHTML .= '<a href="'.$thisLink.'" class="CWlink';
			if (strlen(trim($thisClass))) {
				$navHTML .= ' '.trim($thisClass);
			}
			$navHTML .= '"';
			if ($show_link_titles) {
				$navHTML .= ' title="'.$thisTitleText.'"';
			}
			$navHTML .= '>'.$thisLinkText.'</a>';
		}
	}
	$navHTML .= '</div>
</div>';
	// /end CWlinksNav
	return $navHTML;
}
}

if (!function_exists("CWsortableQuery")) {	
function CWsortableQuery($sort_query,$sort_dir="asc",$sort_col="") {
	// use CWlistQueryColumns function to get the correct column list, with case and order 
	$colList = array();
	foreach ($sort_query as $s_colname => $s_colvals) {
		if ($s_colname != "totalRows") {
			$colList[] = $s_colname;
		}
	}
	// default is sort by the first column ascending, if not given in page or url 
	if(!isset($_GET['sortby'])) { $_GET['sortby'] = $colList[0]; }
	// determine order to be used 
	if(!isset($_GET['sortdir'])) { $_GET['sortdir'] = $sort_dir; }
	// block hack attempts 

        if ($_GET['sortby'] == 'origsize') $_GET['sortby'] = 'origSize';
	if(in_array($_GET['sortby'],$colList)) {
		for ($sc = 1; $sc < $sort_query["totalRows"]; $sc++) {
			$sc3 = $sc;
			for ($sc2 = $sc-1; $sc2 >= 0; $sc2--) {
				if ($_GET["sortdir"] == "asc") {
					if (strtolower($sort_query[$_GET["sortby"]][$sc3]) > strtolower($sort_query[$_GET["sortby"]][$sc2])) break;
				} else {
					if (strtolower($sort_query[$_GET["sortby"]][$sc3]) < strtolower($sort_query[$_GET["sortby"]][$sc2])) break;
				}
				foreach ($sort_query as $colname => $valuearr) {
					if (is_array($valuearr)) {
						$temp_col_val = $valuearr[$sc2];
						$valuearr[$sc2] = $valuearr[$sc3];
						$valuearr[$sc3] = $temp_col_val;
						$sort_query[$colname] = $valuearr;
					}
				}
				$sc3--;
			}
		}
		return $sort_query;
	} else {
		return $sort_query;
	}
}

function CWqueryCanSort($rs, $sort) {
	if (strtolower(trim($sort)) == "undefined") { return false; }
	if (strlen(trim($sort)) && strpos($rs, "*") !== false) { return true; }
	$sortArr = explode(",", $sort);
	foreach ($sortArr as $key => $value) {
		if (strlen(trim($value))) {
			if (strpos($rs, trim($value)) === false) { return false; }
		}
	}
	return true;
}

function CWqueryGetSort($rs, $sort, $dir, $def) {
	$obClause = "ORDER BY";
	$sortArr = explode(",", $sort);
	foreach ($sortArr as $key => $value) {
		if (strlen(trim($value))) {
			$obClause .= ( ($obClause != "ORDER BY") ? "," : "" )." ".CWqueryParam($value)." ".CWqueryParam($dir);
		}
	}
	if (strlen(trim($def))) {
		$obClause .= ( ($obClause != "ORDER BY") ? ", " : " " ).$def;
	}
	return ( ($obClause != "ORDER BY") ? $obClause : "" );
}
}
	
// // ---------- Make a List Random: CWrandomList() ---------- // 
if (!function_exists("CWlistRandom")) {
function CWlistRandom($list_string=NULL,$max_items=0) {
	//global /["request.cwapp"]["db_link"];
	$returnCt = 0;
	$listStr = 0;
	$returnList = array();
	$loopCt = 0;
	$ii = '';
	// shorten list var 
	$listStr = trim($list_string);
	$listArr = explode(",", $listStr);
	// determine number of results to return 
	if($max_items == 0 || $max_items > count($listArr)) {
		$returnCt = count($listArr);
	} else {
		$returnCt = $max_items;
	}
	// scope the loop index 
	$loopArr = $listArr;
	foreach ($loopArr as $key => $ii) {
		// determine number of items to return 
		if($loopCt < $returnCt) {
			// get an item at random from the old list 
			$listIndex = rand(0,sizeof($listArr)-1);
			$listItem = $listArr[$listIndex];
			// add the item to the new list 
			$returnList[] = $listItem;
			// delete the item from the old list 
			if ($listIndex == 0) {
				array_shift($listArr);
			} else if ($listIndex == sizeof($listArr)-1) {
				array_pop($listArr);
			} else {
				$listArr = array_merge(array_slice($listArr, 0, $listIndex), array_slice($listArr, $listIndex+1));
			}
		}
		if (!sizeof($listArr)) break;
		$loopCt++;
	}
	return $returnList;
}
}

// // ---------- Clean Numeric Query Values: CWsqlNumber() ---------- // 
// Remove commas from numbers for MySQL//
if (!function_exists("CWsqlNumber")) {
function CWsqlNumber($clean_number) {
	$safeNumber = str_replace(",",".",$clean_number);
	if($safenumber > 0) {
		return $safeNumber;
	} else {
		return 0;
	}
}
}
		
// // ---------- Escape HTML characters: CWsafeHTML() ---------- // -
//DESCRIPTION: converts some html to htmlentities, prevent cross-site scripting (xss)
if (!function_exists("CWsafeHTML")) {
function CWsafeHTML($clean_string) {
	$cleanString = str_replace("<","&lt;",$clean_string);
	$cleanString = str_replace(">","&gt;",$cleanString);
	$cleanString = str_replace('"',"&quot;",$cleanString);
	$cleanString = str_replace(chr(10),"<br>",$cleanString);
	$cleanString = str_replace("@@","",$cleanString);
	$cleanString = str_replace( ";","",$cleanString);
	return $cleanString;
}
}

if (!function_exists("CWsafeHTMLAdmin")) {
function CWsafeHTMLAdmin($convert_string) {
	$cleanString = str_replace("<","&lt;",$convert_string);
	$cleanString = str_replace(">","&gt;",$cleanString);
	$cleanString = str_replace('"',"&quot;",$cleanString);
return 	$cleanString;
}
}
// ---------- Remove Escaped HTML characters, used for search based on url string: CWremoveEncoded() ---------- // 
if (!function_exists("CWremoveEncoded")) {
function CWremoveEncoded($clean_string) {
	$cleanString = str_replace("&lt", "", $clean_string);
	$cleanString = str_replace("&quot", "", $cleanString);
	$cleanString = str_replace("<br>", "", $cleanString);
	$cleanString = str_replace(";", "", $cleanString);
	$cleanString = str_replace("   ", " ", $cleanString);
	$cleanString = str_replace("  ", " ", $cleanString);

	return $cleanString;
} 
}
// // ---------- Remove HTML characters: CWcleanString() ---------- // ^M
// DESCRIPTION: completely removes unwanted characters from html strings^M ^M
if (!function_exists("CWcleanString")) {
function CWcleanString($clean_string, $replace_char="") {
	$cleanString = str_replace("<", $replace_char, $clean_string);
	$cleanString = str_replace(">", $replace_char, $cleanString);
	$cleanString = str_replace('"', $replace_char, $cleanString);
	$cleanString = str_replace(chr(10), $replace_char, $cleanString);
	$cleanString = str_replace("@@", $replace_char, $cleanString);
	$cleanString = str_replace(";", $replace_char, $cleanString);

	return $cleanString;
}
}
// // ---------- Clean up characters for javascript: CWstringFormat() ---------- // 
// Replace characters for Javascript, similar to jsStringFormat only without the problem of double quotes
if (!function_exists("CWstringFormat")) {
function CWstringFormat($clean_string) { 	
	$cleanString = $clean_string;
	$cleanString = CWsafeHTML($cleanString);
	$cleanString = str_replace("'","\'",$cleanString);
	return $cleanString;
}
}
// // ---------- Clean up characters for URL: CWurlSafe()---------- // 
if (!function_exists("CWurlSafe")) {
function CWurlSafe($convert_val=NULL) {
	$returnStr = '';
	$returnStrClean = '';

	if(is_array($convert_val) && count($convert_val)) {
		$returnStr = implode('<br>',$convert_val);
	} else {
		$returnStr = trim($convert_val);
	}

	$returnStrClean = urlencode($returnStr);

	return $returnStrClean;
}
}
// // ---------- Messages and Alerts: CWpageMessage() ---------- // 
if (!function_exists("CWpageMessage")) {
function CWpageMessage($message_type = "alert",$message_string=NULL) {
	if ($message_string === NULL) {
		$message_string = $message_type;
		$message_type = "alert";
	}
	$alertArray = array();
	$fixedMessage = trim(str_replace("\\\"", "\"", str_replace("\\'", "'", $message_string)));
	// if alert type 
	if($message_type == 'alert') {
		if((isset($_ENV["request.cwpage"]["userAlert"])) && is_array($_ENV["request.cwpage"]["userAlert"])) {
			$alertArray = $_ENV["request.cwpage"]["userAlert"];
		}
		if (!in_array($fixedMessage, $alertArray)) $alertArray[] = $fixedMessage;
		$_ENV["request.cwpage"]["userAlert"] = $alertArray;
	}
	else if($message_type == 'confirm') {
		// if confirm type 
		if((isset($_ENV["request.cwpage"]["userConfirm"])) && is_array($_ENV["request.cwpage"]["userConfirm"])) {
			$alertArray = $_ENV["request.cwpage"]["userConfirm"];
		}
		if (!in_array($fixedMessage, $alertArray)) $alertArray[] = $fixedMessage;
		$_ENV["request.cwpage"]["userConfirm"] = $alertArray;
	}
}
}
// ---------- // CWtime : calculate any time  value with global offset // ---------- // 
if (!function_exists("CWtime")) {
function CWtime($time_str = NULL, $offset_val=NULL) {
	if($time_str == NULL) { $time_str = date("Y-m-d h:i:s"); }
	if($offset_val == NULL) { $offset_val = $_ENV["application.cw"]["globalTimeOffset"]; }
	$newTime = '';
	$todayDate = date("Y-m-d g:i a");// current date
	$currentTime = time($todayDate); //Change date into time
	$newTime  = $currentTime + (60*60*$offset_val);
	return $newTime;
}
}

// // ---------- // CWtrailingChar : add or remove a trailing '/' or other character from any string, without duplicates // ---------- // 
if (!function_exists("CWtrailingChar")) {
function CWtrailingChar($textString,$action="add",$char="/",$process_empty=false) {
    $returnStr = trim($textString);
    $charStr = trim($char);
    $strLen = strlen($charStr);
    if (strlen($returnStr) || $process_empty) {
		// add the char(s) if the string does not already end with it 
		if ($action == 'add' && substr($returnStr,strlen($returnStr)-$strLen) != $charStr) {
			$returnStr .= $charStr;
		// remove the chars, if the string ends with it 
		} elseif ($action == 'remove' && substr($returnStr,strlen($returnStr)-$strLen) == $charStr) {
			$returnStr = substr($returnStr,0,strlen($returnStr)-$strLen);
		}
    }
    return $returnStr;
}   
}

if (!function_exists("CWqueryGetRS")) {
function CWqueryParam($query_param) {
	if (get_magic_quotes_gpc()) $query_param = stripslashes($query_param);
	return mysql_real_escape_string($query_param);
}
function CWqueryGetRS($sql_statement) {
	$resultReturn = array("totalRows" => 0);
	if ($_ENV["request.cwapp"]["db_link"] !== false) {
		if (!function_exists("CWpageMessage")) {
			$myDir = getcwd();
			chdir(dirname(__FILE__));
			// global functions 
			require_once("cw-func-global.php");
			chdir($myDir);
		}
		$resultRS = mysql_query($sql_statement, $_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$sql_statement);
		if ($resultRS !== false) {
			$rr = 0;
			while ($rowResult = mysql_fetch_assoc($resultRS)) {
				foreach ($rowResult as $keyRS => $rowVal) {
					if ($rr == 0) {
						$resultReturn[$keyRS] = array();
					}
					$resultReturn[$keyRS][] = $rowVal;
				}
				$rr++;
			}
			$resultReturn["totalRows"] = $rr;
		}
	}
	return $resultReturn;
}
}

if (!function_exists("CWquerySortRS")) {
function CWquerySortRS($sort_query,$sort_dir="asc",$sort_col="") {
	$colList = array();
	foreach ($sort_query as $s_colname => $s_colvals) {
		if ($s_colname != "totalRows") {
			$colList[] = $s_colname;
		}
	}
	// default is sort by the first column ascending, if not given in page or url 
	if (in_array($sort_col, $colList)) {
		for ($sc = 1; $sc < $sort_query["totalRows"]; $sc++) {
			$sc3 = $sc;
			for ($sc2 = $sc-1; $sc2 >= 0; $sc2--) {
				if ($sort_dir == "asc") {
					if ($sort_query[$sort_col][$sc3] > $sort_query[$sort_col][$sc2]) break;
				} else {
					if ($sort_query[$sort_col][$sc3] < $sort_query[$sort_col][$sc2]) break;
				}
				foreach ($sort_query as $colname => $valuearr) {
					if (is_array($valuearr)) {
						$temp_col_val = $valuearr[$sc2];
						$valuearr[$sc2] = $valuearr[$sc3];
						$valuearr[$sc3] = $temp_col_val;
						$sort_query[$colname] = $valuearr;
					}
				}
				$sc3--;
			}
		}
		return $sort_query;
	} else {
		return $sort_query;
	}
}
}

// // ---------- // CWleadingChar : add or remove a leading '/' or other character from any string, without duplicates // ---------- // 
if (!function_exists("CWleadingChar")) {
function CWleadingChar($textString,$action="add",$char="/",$process_empty=false) {
    $returnStr = trim($textString);
    $charStr = trim($char);
    $strLen = strlen($charStr);
    if (strlen($returnStr) || $process_empty) {
		// add the char(s) if the string does not already end with it 
		if ($action == 'add' && substr($returnStr,0,$strLen) != $charStr) {
			$returnStr = $charStr.$returnStr;
		// remove the chars, if the string ends with it 
		} elseif ($action == 'remove' && substr($returnStr,0,$strLen) == $charStr) {
			$returnStr = substr($returnStr,$strLen);
		}
    }
    return $returnStr;
}
}
?>