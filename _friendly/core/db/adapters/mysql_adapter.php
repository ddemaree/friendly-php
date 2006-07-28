<?php

// Experimental adapter for MySQL db -- will serve as prototype for future adapters, like Postgres and SQLite

class FriendlyDBAdapter
{
	function FriendlyDBAdapter($db_config_array) {
		$this->pointer = mysql_connect($db_config_array['host'],$db_config_array['username'],$db_config_array['password']) or exit(mysql_error());
		mysql_select_db($db_config_array['database'],$this->pointer);
		
	}
	
	function delete_by_sql($table_name, $where_clause) {	
		$sql = "DELETE FROM `{$table_name}` WHERE {$where_clause}";
		mysql_query($sql,$this->pointer);
		if(mysql_affected_rows() > 0)
			return true;
		else
			return false;
	}
	
	function find_many_by_sql($sql,$type = "object") {
		$qq = $this->sql_query($sql);
		
		$funct_name = "mysql_fetch_".$type;
		
		if($qq->count > 0) {
			while($rr = $funct_name($qq->qq)) $response[] = $rr;
			return $response;
		}
		else return false;
	}
	
	function find_one_by_sql($sql,$type = "object") {
		$qq = $this->query($sql);
		
		$funct_name = "mysql_fetch_".$type;
		
		if($qq->count > 0) {
			return $funct_name($qq->qq);
		}
		else return false;
	}
	
	
	function insert($array,$table_name) {
		if($array['id'] == 'new') unset($array['id']);
		return $this->update($array,$table_name,NULL);
	}
	
	
	// DEPRECATED
	function query($sql) {
		return $this->sql_query($sql);
	}
	
	
	function sql_query($sql) {
	// $obj is a blank stdClass object used here as a container
	
		if(!$obj->qq = mysql_query($sql,$this->pointer)) {
			$obj->error = mysql_error();
			$obj->count = 0;
		}
		else {
			$obj->count   = mysql_num_rows($obj->qq);
			$obj->pointer =& $obj->qq;
		}
		
		return $obj;
	}
	
	
	function update($array,$table_name,$where_clause = NULL) {
		
		$DB =& $this->pointer;
		
		$col_list = $this->find_many_by_sql("DESCRIBE $table_name");
		foreach($col_list as $col) {
			if($col->Field == "created_on" && $where_clause == NULL)
				$array['created_on'] = date("Y-m-d h:i:s");
			elseif($col->Field == "updated_on")
				$array['updated_on'] = date("Y-m-d h:i:s");
		
			$_fields[] = $col->Field;
		}
		
		// Build set
		foreach($array as $k => $v) {
			if(!in_array($k, $_fields)) {
				continue; # Bogus field name, skipping to avoid SQL error
			}
			elseif(is_array($v) && $v['Month'] && $v['Day'] && $v['Year']) {
				// Is smarty date -- make string and continue
				$dS = sprintf("%04d-%02d-%02d",$v['Year'],$v['Month'],$v['Day']);
				
				if($v['Hour'] && $v['Minute'])
					$dS .= sprintf(" %02d:%02d:00",$v['Hour'],$v['Minute']);
				
				$v = $dS;
			}
			elseif(is_array($v))
				$v = serialize($v);
		
			$scratch[] = "`$k` = '".mysql_escape_string($v)."'";
		}
		
		$set = implode(", ", $scratch);
		unset($scratch);
		
		if($where_clause != NULL) {
		
			$sql = "UPDATE `{$table_name}` SET {$set} WHERE {$where_clause}";
		}
		else {
		
			$sql = "INSERT INTO `{$table_name}` SET {$set}";
		}
		
		mysql_query($sql,$DB);
		
		if(mysql_affected_rows($DB) > 0 || !mysql_error())
			return true;
		else
			raise("Error: Item could not be saved to the database. (".mysql_error().")");//return false;//
	}
	
	
}

?>