<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: admin/cwadminapp/func/cw-func-admin.php
File Date: 2012-06-27
Description:
fucntions for admin side of site
==========================================================
*/
//If user is not login then redirect to home page (login page)
if (!function_exists("CWauth")) {
function CWauth($allowed_levels, $redirect_url = "index.php") {
	$userlevel = '';
	if (!is_array($allowed_levels) && strlen(trim($allowed_levels))) $allowed_levels = explode(",", $allowed_levels);
	else if (!is_array($allowed_levels)) $allowed_levels = array();
	if(sizeof($allowed_levels) == 1 && $allowed_levels[0] == 'any' && isset($_ENV["request.cwpage"]["accessLevel"])) {
		$userlevel = $_SESSION["cw"]["accessLevel"]; 
	} elseif (isset($_SESSION["cw"]["accessLevel"]) && isset($_SESSION["cw"]["loggedIn"]) && (in_array(trim($_SESSION["cw"]["accessLevel"]), $allowed_levels) !== false || in_array("any", $allowed_levels) !== false)) {
		$userlevel = $_SESSION["cw"]["accessLevel"];
	} else {
		header('Location: '.trim($redirect_url));
	}
	return $userlevel;	
}
}


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

if (!function_exists("CWremoveUrlVars")) {
function CWremoveUrlVars($omit_list=NULL, $parse_url= NULL, $return_content = "vars" ) {
	if($parse_url == NULL) { $parse_url = $_SERVER['QUERY_STRING']; }
	$list1 = explode(',',$omit_list);
	$varname = "";
	$varval = "";
	$newitem = "";
	$qsVarList = array();
	$cnt = explode('?',$parse_url);
	$chunks = explode('&',$cnt[count($cnt)-1]);
	for($vv=0;$vv<count($chunks);$vv++) {
		$firstv = explode('=',$chunks[$vv]);
		$newv = $firstv[0];
		$varname = $newv;
		$newvv = $firstv[count($firstv) - 1];
		$varval = $newvv;
		// if not an omitted value 
		if(!in_array($varname, $list1)) { 
			// if showing values 
			if(strcmp($return_content, "vals") === 0 ) {
				$newitem = $varval;
			} else {
				// otherwise, default = show vars as a list 
				$newitem = $varname;
			} 
			// avoid duplicates 
			if(!in_array($newitem, $qsVarList)) {
				$qsVarList[] = $newitem;
			}
		}
	}
	return $qsVarList;
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

if (!function_exists("CWformField")) {
function CWformField($field_type,$field_name,$field_id,$field_label=NULL,$field_value=NULL,$field_options=NULL,$field_class=NULL,$field_size=35,$field_rows=5) {
	$val = explode('|', $field_options);
	$formField = '';
	$formFieldTitle = $field_label;
	// email fields 
	if(strpos($field_class, 'email') !== false) {
		$formFieldTitle .= ' must be a valid email address';
	}
	else if(strpos($field_class, 'required') !== false) {
		// required fields 
		$formFieldTitle .= ' is required';
	}
	if ($field_class) $field_class .= " ";
	switch($field_type) {
		// single checkbox yes/no 
		case 'boolean':
			$formField = '<input name="' . $field_name .'" id="'. $field_id.'" type="checkbox" class="'.$field_class.'formCheckbox " value="true"';
			 if($field_value."" == "true" || $field_value."" == "1")
				$formField .= ' checked="checked"';
			$formField .= ' title="'. $formFieldTitle.'">';
			break;
		// radio 
		case "radio":
			$fvArr = explode(",", $field_value);
			foreach ($fvArr as $key => $nameValuePair) { $fvArr[$key] = str_replace("^comma^", ",", $nameValuePair); }
			$newarray = explode(chr(10), $field_options);
			foreach ($newarray as $key => $nameValuePair) {
				$nameValuePair = str_replace(",", "", $nameValuePair);
				$nvpArr = explode("|", trim($nameValuePair));
				// remove commas from values 
				$formField .= '<div class="checkboxWrap">
					<input name="'.$field_name.'" id="'.$field_id.'-'.$field_value.'" type="radio" class="'.$field_class.' formRadio" value="'.$nvpArr[sizeof($nvpArr)-1].'"';
				if(in_array(trim($nvpArr[sizeof($nvpArr)-1]), $fvArr))
					$formField  .= ' checked="checked"';
				$formField .=' title="'.$formFieldTitle.'">'.$nvpArr[0].'
					</div>';
			}
			break;		
		// checkbox array 
		case "checkboxgroup" :
			$fvArr = explode(",", $field_value);
			foreach ($fvArr as $key => $nameValuePair) { $fvArr[$key] = str_replace("^comma^", ",", $nameValuePair); }
			$newarray = explode(chr(10), $field_options);
                        if (sizeof($newarray) == 1) $newarray = explode(chr(13), $field_options);
			foreach ($newarray as $key => $nameValuePair) {
				$nameValuePair = str_replace(",", "", $nameValuePair);
				$nvpArr = explode("|", trim($nameValuePair));
				// remove commas from values 
				$formField .= '<div class="checkboxWrap">
					<input name="'.$field_name.'['.$key.']" id="'.$field_id.'-'.$field_value.'" type="checkbox" class="'.$field_class.' formCheckbox" value="'.$nvpArr[sizeof($nvpArr)-1].'"';
				if(in_array(trim($nvpArr[sizeof($nvpArr)-1]), $fvArr))
					$formField  .= ' checked="checked"';
				$formField .=' title="'.$formFieldTitle.'">'.$nvpArr[0].'
					</div>';
			}
			break;
		// textarea 
		case "textarea":
        	$formField .= '<textarea name="'.$field_name.'" id="'.$field_id.'" class="'. $field_class.'" cols="'.$field_size.'" rows="'.$field_rows.'" title="'.$formFieldTitle.'">'.$field_value.'</textarea>';
			break;
		//!--- texteditor rich text area 
		case "texteditor":
			$formField .= '<textarea name="'. $field_name.'" cols="'. $field_size.'" rows="'. $field_rows.'" id="'. $field_id.'" class="'.$field_class .' textEdit" title="'. $formFieldTitle.'">'. $field_value.'</textarea>';
		   break;
		// select 
		case "select":
			$fvArr = explode(",", $field_value);
			foreach ($fvArr as $key => $nameValuePair) { $fvArr[$key] = str_replace("^comma^", ",", $nameValuePair); }
			$formField = '<select name="'. $field_name;
			if($field_type == "multiselect") { $formField .= '[]'; }
			$formField .= '"  id="'. $field_id.'"';
			if($field_type == "multiselect") { 
		  		$formField .= 'multiple="multiple" size="'. $field_rows. '"';
			}
			$formField .= 'class="'.$field_class.'" title="'. $formFieldTitle.'">';
			$newarray = explode(chr(10), $field_options);
			foreach ($newarray as $key => $nameValuePair) {
				//$nameValuePair = str_replace(",", "", $nameValuePair);
				$nvpArr = explode("|", trim($nameValuePair));
				// remove commas from values 
				$formField .= '<option value="'.$nvpArr[sizeof($nvpArr)-1].'"';
				if(in_array(trim($nvpArr[sizeof($nvpArr)-1]), $fvArr))
					$formField  .= ' selected="selected"';
				$formField .= '>'.$nvpArr[0].'</option>';
			}
			$formField .= '</select>';
			break;
		// numeric input 
		case "number":	
			$formField = '<input type="text" id="'. $field_id.'" name="'. $field_name.'" class="'. $field_class.'" value="'. $field_value.'" size="'. $field_size.'" onblur="checkValue(this);" onkeyup="extractNumeric(this,2,true);" title="'. $formFieldTitle.'">';
			break;
		// text input (default) 
		default:
			$formField .='<input type="text" id="'. $field_id.'" name="'.$field_name.'" class="'. $field_class.'" value="'. htmlentities($field_value).'" size="'. $field_size.'" title="'. $formFieldTitle.'">';
			break;			
	} //end of switch case
	return $formField;	
}
}



if (!function_exists("CWgetImage")) {
function CWgetImage($product_id,$image_id,$image_scaleto=0) {
	// Query the database and return a url to an image, if it exists 
	$rs = "";
	$rs = "SELECT cw_product_images.product_image_filename, cw_image_types.imagetype_folder
			FROM cw_image_types
			INNER JOIN cw_product_images
			ON cw_image_types.imagetype_id = cw_product_images.product_image_imagetype_id
					 WHERE cw_product_images.product_image_product_id = '".CWqueryParam($product_id)."'
					 AND cw_product_images.product_image_imagetype_id =	'".CWqueryParam($image_id)."'";
	$result = mysql_query($rs,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$rs);
	$row = mysql_fetch_array($result);	
	//return $row;
 	 
	
	if($row  != 0) {
		// Process the image 
		$imageSRC = $row['imagetype_folder'].'/'.$row['product_image_filename'];
		$imagePath = "";
		if(file_exists('../../'.$_ENV["request.cwpage"]["adminImgPrefix"].$_ENV["application.cw"]["appImagesDir"].'/'.$imageSRC)) {
			return $imagePath.$imageSRC;
		} else {
			return $imageSRC;
		}
	} else {
		// There's no related image, return an empty string 
		return "";
	}
}
}
// // ---------- Show Image: CWdisplayImage() ---------- // 
// DESCRIPTION: This function returns an image complete with image tag
//	with alt attribute based on product id and image type
//ARGUMENTS
//productID: Integer product id from the database.
//ImageID: Type of image required (integer key representing large, thumbnail, etc).
//altText: Alternate text for the image, or blank if none.
//noImageText: Text to display if image doesn't exist, if any.
//class: optional css class.
//noImageText: optional element id.
//RETURNS
//A string with the image location.
if (!function_exists("CWdisplayImage")) {
function CWdisplayImage($product_id,$image_id,$alt_text=NULL,$noimage_text=NULL,$class=NULL,$id=NULL,$image_scaleto=0) {
	$displayImage = "";
	$imageSRC = CWgetImage($product_id,$image_id,$image_scaleto);
	$altText = '';
	if($class != "") {
		$class = 'class ='.$class;
	}
	if($id != "") {
		$id = 'id ='.$id;
	}
	$altText = str_replace('"','&quot;',$altText);
	if($imageSRC != "") {
		$DisplayImage =  '<img src="'.$imagesrc.'" alt="'.$altText.'"'.$class.$id.'>' ;
		//'<img src="#imagesrc#" alt="#arguments.alt_text#"#arguments.class##arguments.id#>'>
	} else {
		$DisplayImage = $noimage_text;
	}
	
}
}

// // ---------- Clean Numeric Query Values: CWsqlNumber() ---------- // 
// Remove commas from numbers for MySQL
if (!function_exists("CWsqlNumber")) {
function CWsqlNumber($clean_number) {
	$safeNumber = str_replace( ",",".",$clean_number);
	if($safeNumber > 0) {
		return $safeNumber;
	} else {
		return 0;
	}
}
}
if (!function_exists("CWsafeHTML")) {
function CWsafeHTML($convert_string) {
	$cleanString = str_replace("<","&lt;",$convert_string);
	$cleanString = str_replace(">","&gt;",$cleanString);
	$cleanString = str_replace('"',"&quot;",$cleanString);
	$cleanString = str_replace(chr(10),"<br>",$cleanString);
	
return 	$cleanString;
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
// ---------- Clean up characters for javascript: CWstringFormat() ---------- // 
// Replace characters for Javascript, similar to jsStringFormat
// only without the problem of double quotes
if (!function_exists("CWstringFormat")) {
function CWstringFormat($clean_string) {
	$cleanString = $clean_string;
	$cleanString = CWsafeHTML($cleanString);
	$cleanString = str_replace("'","\'",$cleanString);
	
return $cleanString;
}
}

if (!function_exists("CWurlSafe")) {
function CWurlSafe($convert_val=NULL) {
	$returnStr = '';
	$returnStrClean = '';
	
	if((is_array($convert_val)) && count($convert_val)) {
		$returnStr = implode('<br>',$convert_val);
	} else {
		$returnStr = trim($convert_val);
	}
	$returnStrClean = urlencode($returnStr);
return 	$returnStrClean;
}
}

// // ---------- Messages and Alerts: CWpageMessage() ---------- // 
if (!function_exists("CWpageMessage")) {
function CWpageMessage($message_type="alert",$messageString=NULL) {
	if ($messageString === NULL) {
		$messageString = $message_type;
		$message_type = "alert";
	}
	$alertArray = array();
        $messageArray = explode(",", $messageString);
        foreach ($messageArray as $message_string) {
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
}

	// // ---------- // Time Offset: CWtime() // ---------- // 
 
//$time = date("Y-m-d h:i:s");
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

// // ---------- // Get date mask for any locale: CWlocaleDateMask() // ---------- // 
//function CWlocaleDateMask($locale) {
//	if (!$locale) $locale = "Y-m-d";
if (!function_exists("CWlocaleDateMask")) {
function CWlocaleDateMask($locale=null) {
	if ($locale === null) {
		$testDate = strftime("%x", strtotime("2011-06-19"));
		$dateSep = "/";
		$dateArr = array("m","d","Y");
		if (strpos($testDate, "-") !== false) $dateSep = "-";
		if (strpos($testDate, ".") !== false) $dateSep = ".";
		if (strpos($testDate, "06") === false) {
			$dateArr[0] = "n";
			$dateArr[1] = "j";
		}
		if (strpos($testDate, "19") < strpos($testDate, "6")) {
			$tempVal = $dateArr[0];
			$dateArr[0] = $dateArr[1];
			$dateArr[1] = $tempVal;
		}
		if (strpos($testDate, "11") < strpos($testDate, "6")) {
			$tempVal = $dateArr[2];
			$dateArr[2] = $dateArr[1];
			$dateArr[1] = $dateArr[0];
			$dateArr[0] = $tempVal;
		}
		return implode($dateSep, $dateArr);
	}
	$returnMask = '';
	$defaultMask = 'Y-m-d';
	//EEA locale -> locales
	$locales = array("Dutch (Belgian)","Dutch (Standard)","English (Australian)","English (Canadian)","English (New Zealand)","English (UK)","English (US)","French (Belgian)","French (Canadian)","French (Standard)","French (Swiss)","German (Austrian)","German (Standard)","German (Swiss)","Italian (Standard)","Italian (Swiss)","Norwegian (Bokmal)","Norwegian (Nynorsk)","Portuguese (Brazilian)","Portuguese (Standard)","Spanish (Mexican)","Spanish (Modern)","Spanish (Standard)","Swedish");
	$masks = array("j/m/Y","j-n-Y","j/m/Y","d/m/Y","j/m/Y","d/m/Y","n/j/Y","j/m/Y","Y-m-d","d/m/Y","d.m.y","d.m.Y","d.m.Y","d.m.Y","d/m/Y","d.m.Y","d.m.Y","d.m.Y","j/n/Y","d-m-Y","d/m/Y","d/m/Y","d/m/Y","Y-m-d");
	if (strlen(trim($locale))) {
		$maskPos = array_search(trim($locale),$locales);
		if ($maskPos !== false) {
			$returnMask = $masks[$maskPos];
		} else {
			$returnMask = $defaultMask;
		}
	}
	return $returnMask;
}
}

// // ---------- // Set installation timestamp on first page request: CWsetInstallationDate()// ---------- // 
if (!function_exists("CWsetInstallationDate")) {
function CWsetInstallationDate() {
	$setDateSQL = "UPDATE cw_config_items
					SET config_value = ".CWqueryParam(CWtime())."
					WHERE config_variable = 'appInstallationDate'
					AND (config_value = ''
						OR config_value = NULL)";
	mysql_query($setDateSQL,$_ENV["request.cwapp"]["db_link"]) or CWpageMessage(mysql_error()."<br />".$setDateSQL);
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