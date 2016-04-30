<?php
/*
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: cw-func-query.php
File Date: 2012-02-01
Description: misc. Cartweaver query functions
==========================================================
*/


function CWqueryBasicFilterRS($rsFilter, $compareArr) {
	$rsReturn = array();
	foreach ($rsFilter as $key => $values) {
		if ($key != "totalRows") {
			$rsReturn[$key] = array();
		}
	}
	$returnCt = 0;
	for ($i=0; $i<$rsFilter["totalRows"]; $i++) {
		$isValid = true;
		for ($j=0; $j<sizeof($compareArr); $j++) {
			if ($rsFilter[$compareArr[$j][0]][$i] != $compareArr[$j][1]) {
				$isValid = false;
				break;
			}
		}
		if ($isValid) {
			foreach ($rsFilter as $key => $valueArr) {
				$rsReturn[$key][] = $rsFilter[$key][$i];
			}
			$returnCt++;
		}
	}
	$rsReturn["totalRows"] = $returnCt;
	return $rsReturn;
}

// // ---------- Get ALL State/Provs ---------- // 

function CWquerySelectStates($country_id=0) {
        $rsGetStateList = "SELECT
								cw_countries.*,
								cw_stateprov.*
								FROM cw_countries
								INNER JOIN cw_stateprov
								ON cw_countries.country_id = cw_stateprov.stateprov_country_id
								WHERE
									cw_stateprov.stateprov_archive = 0
									AND cw_countries.country_archive = 0";
        if($country_id > 0) {
                $rsGetStateList.= " AND cw_countries.country_id = '".CWqueryParam($country_id)."'";
        }
        $rsGetStateList.= " ORDER BY
								cw_countries.country_sort,
								cw_countries.country_name,
								cw_stateprov.stateprov_name";
        return CWqueryGetRS($rsGetStateList);
}



// // ---------- Get ALL Countries ---------- // 

function CWquerySelectCountries($show_archived=true) {
        $rsGetCountryList = "SELECT country_id, country_name
                                                FROM cw_countries";
		if($show_archived == 0) {
			$rsGetCountryList.= " WHERE NOT country_archive = 1";
		}
        $rsGetCountryList.= " ORDER BY country_name";
        return CWqueryGetRS($rsGetCountryList);
}



// // ---------- Get ALL State/Provs by country ---------- // 

function CWquerySelectCountryStates($states_archived=0,$groupquery,$country=NULL) {
        $rsCountryStatesList = "SELECT
                                                                cw_countries.*,
                                                                cw_stateprov.*
                                                                FROM cw_countries
                                                                LEFT JOIN cw_stateprov
                                                                ON cw_countries.country_id = cw_stateprov.stateprov_country_id where 1=1";
        if($states_archived != 2) {
                $rsCountryStatesList.= " and cw_countries.country_archive = '".CWqueryParam($states_archived)."'";
        }
        $rsCountryStatesList .=$country;
        $rsCountryStatesList.= $groupquery." ORDER BY country_sort, country_name, stateprov_name ";
        return CWqueryGetRS($rsCountryStatesList);
}

// // ---------- Get Country IDs for user defined states ---------- // 

function CWquerySelectStateCountryIDs() {
        $rsGetStateCountryIDs = "SELECT DISTINCT stateprov_country_id
                                                                FROM cw_stateprov
                                                                WHERE NOT ".$_ENV["application.cw"]["sqlLower"]."(stateprov_name) in('none','all')";
        return CWqueryGetRS($rsGetStateCountryIDs);
}


// // ---------- Get State/Prov Details ---------- // 

function CWquerySelectStateProvDetails($stateprov_id,$stateprov_name="",$stateprov_code="",$country_id=0,$omit_list=0) {
	// look up stateprov 
	$rsSelectStateProv = "SELECT *
													FROM cw_stateprov
													WHERE 1 = 1";
	if($stateprov_id > 0) {
			$rsSelectStateProv.= " AND stateprov_id = '".CWqueryParam($stateprov_id)."'";
	}
	if(strlen($stateprov_name) > 0) {
			$rsSelectStateProv.= " AND stateprov_name = '".CWqueryParam($stateprov_name)."'";
	}
	if(strlen($stateprov_code) > 0 && $stateprov_code > 0) {
			$rsSelectStateProv.= " AND stateprov_code = '".CWqueryParam($stateprov_code)."'";
	}
	if($country_id > 0) {
			$rsSelectStateProv.= " AND stateprov_country_id = '".CWqueryParam($country_id)."'";
	}
	if($omit_list != 0) {
			$rsSelectStateProv.= " AND NOT stateprov_id in('".CWqueryParam($omit_list)."')";
	}
	return CWqueryGetRS($rsSelectStateProv);
}

// // ---------- Get All Credit Cards ---------- // 

function CWquerySelectCreditCards($card_code="") {
	$rsCCardList = "SELECT * FROM cw_credit_cards";
	if (strlen(trim($card_code))) {
		$rsCCardList .= " WHERE creditcard_code='".CWqueryParam(trim($card_code))."'";
	}
	$rsCCardList .= " ORDER BY creditcard_name";
	return CWqueryGetRS($rsCCardList);
}
?>