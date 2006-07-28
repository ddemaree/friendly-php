<?php

class FriendlyModel {
	
	function _wrap($_data) {
		foreach($_data as $_k => $_v) {
			$this->$_k = $_v;
		}
	}
	
	function _basicFetch($_table_name,$_id,$_id_field = 'id') {
		$this->_model_info->_table_name = $_table_name;
		$this->_model_info->_primary_key = $_id_field;
		$this->_model_info->_where_clause = "{$this->_model_info->_primary_key} = '{$_id}'";
		
		$this->_model_info->_finder_sql = "SELECT * FROM {$this->_model_info->_table_name} WHERE {$this->_model_info->_where_clause} LIMIT 1";
		
		if($_data = db_fetch_one($this->_model_info->_finder_sql)){
			$this->_wrap($_data);
			
			foreach(db_fetch("DESCRIBE {$this->_model_info->_table_name}") as $_field) {
				if($_field->Key != 'PRI') {
					$this->_model_info->_fields[$_field->Field] = $_field->Field;

					$field_name = $_field->Field;

					$this->_model_info->_data[$_field->Field] =& $this->$field_name;
				}
			}
			
			return true;
		}
		else {
			return false;
		}
	}
	
	function _update($_array) {
		if(!$this->_model_info) {
			friendly_exit("No model info, stopping. (FriendlyModel#_update, line 57)");
		}
		
		return db_update($_array,$this->_model_info->_table_name,$this->_model_info->_where_clause);
	}
	
	
	function find_all($_class, $_conditions = false, $_order = false, $_limit = false) {
		
		# We have to eval this because PHP's inflection features are for shit
		eval("\$_setup = {$_class}::_setup_model();");
		
		$_fields = "{$_setup['primary_key']}";
		
		# And now for some really ghetto single table inheritance
		if($_setup['inheritance_column']) {
			$_fields .= ", {$_setup['inheritance_column']}";
		}
		
		$_sql[] = "SELECT {$_fields} FROM {$_setup['table_name']}";
		
		if($_conditions) {
			if(is_array($_conditions)) {
				$_sql[] = " WHERE " . implode(" AND ", $_conditions);
			}
			else
				$_sql[] = "WHERE {$_conditions}";
		}
		
		if($_order) {
			if(is_array($_order)) {
				$_sql[] = "ORDER BY " . implode(", ", $_order);
			}
			else
				$sql[] = "ORDER BY {$_order}";
		}
		else {
			$_sql[] = " ORDER BY id ASC";
		}
		
		#if(!$_limit) $_limit = "0,30";
		
		if($_limit)
			$_sql[] = "LIMIT {$_limit}";
		
		foreach(db_fetch(implode(" ",$_sql)) as $_pmt) {
			if($_ic = $_setup['inheritance_column'])
				$_class = $_pmt->$_ic;
			
			if(!$_pkey = $_setup['primary_key'])
				$_pkey = 'id';
				
			$_ret[] = new $_class($_pmt->$_pkey);
		}
		
		return $_ret;
	}
	
	function find($_class,$_pmt_id) {		

		# We have to eval this because PHP's inflection features are for shit
		eval("\$_setup = {$_class}::_setup_model();");
		
		$_fields = "{$_setup['primary_key']}";
		
		# And now for some really ghetto single table inheritance
		if($_setup['inheritance_column']) {
			$_fields .= ", {$_setup['inheritance_column']}";
		}
		
		if(is_numeric($_pmt_id)) {
			$_pmt = db_fetch_one("SELECT {$_fields} FROM {$_setup['table_name']} WHERE {$_setup['primary_key']} = '{$_pmt_id}' LIMIT 1");
		}
		else {
			return false;
		}
		
		if($_ic = $_setup['inheritance_column'])
			$_class = $_pmt->$_ic;
		
		if(!$_pkey = $_setup['primary_key'])
			$_pkey = 'id';
			
		return new $_class($_pmt->$_pkey);
	}
	
	
}

?>