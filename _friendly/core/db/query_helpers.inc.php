<?php

function condition_keyword_search($_field_name, $_query_string) {
	
	if(preg_match("/ /",$_query_string)) {
		$_words = preg_split("/ /", $_query_string);
		foreach($_words as $_word) {
			$_word = mysql_escape_string($_word);
			
			if(is_array($_field_name)) {
				foreach($_field_name as $_fn) {
					$_fs[] = "{$_fn} RLIKE '{$_word}'";
				}
				$_qs[] = "(" . implode(" OR ", $_fs) . ")";
				$_fs = false;
			}
			else {
				$_qs[] = "({$_field_name} RLIKE '{$_word}')";
			}
				
		}				
		$_qs = implode(" AND ",$_qs);
	}
	else {
		$_query_string = mysql_escape_string($_query_string);
		
		if(is_array($_field_name)) {
			foreach($_field_name as $_fn) {
				$_qs[] = "{$_fn} RLIKE '{$_query_string}'";
			}
			$_qs = implode(" OR ",$_qs);
		}
		else
			$_qs = "{$_field_name} RLIKE '{$_query_string}'";
	}
	
	return $_qs;
}

function condition_date_value($_field_name, $_start_date, $_operator = '=') {
	$_start_date = "{$_start_date['Year']}-{$_start_date['Month']}-{$_start_date['Day']}";
	return "{$_field_name} {$_operator} '{$_start_date}'";
}

function condition_datetime_value($_field_name, $_start_date, $_operator = '=') {
	$_start_date = "{$_start_date['Year']}-{$_start_date['Month']}-{$_start_date['Day']}";
	
	switch($_operator) {
		case "!=":
			return "({$_field_name} < '{$_start_date} 00:00:00' OR {$_field_name} > '{$_start_date} 23:59:59')";
			break;
		case "=":
		default:
			return "({$_field_name} >= '{$_start_date} 00:00:00' AND {$_field_name} <= '{$_start_date} 23:59:59')";
	}
}

function condition_date_range($_field_name,$_dates) {
	$_start_date =& $_dates['start_date'];
	$_start_date = "{$_start_date['Year']}-{$_start_date['Month']}-{$_start_date['Day']}";
	$_end_date =& $_dates['end_date'];
	$_end_date = "{$_end_date['Year']}-{$_end_date['Month']}-{$_end_date['Day']}";
	
	return "{$_field_name} BETWEEN '{$_start_date}' AND '{$_end_date}'";
}

?>