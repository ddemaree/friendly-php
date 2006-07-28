<?php

class FriendlyDB {
	
	function FriendlyDB($db_config_array) {
	// $db_config_array is an array containing all necessary info required by the adapter
		
		#	Default adapter is mysql
			if(!$db_config_array['adapter'])
				$db_config_array['adapter'] = 'mysql';
		
		#	Load in and instantiate adapter class
			load_file(FRIENDLY_PATH."core/db/adapters/{$db_config_array['adapter']}_adapter.php");
		
			$this->adapter = new FriendlyDBAdapter($db_config_array);
			$this->pointer = $this->adapter->pointer;
	}


	#	Methods for finding bunches of rows
	
		function find_many_by_sql($sql,$type = "object") {
			return $this->adapter->find_many_by_sql($sql,$type);
		}
		
		function find_by_sql($sql,$type = "object") {
			return $this->adapter->find_many_by_sql($sql,$type);
		}
		
		// Deprecated - Left over from Friendly 0.5
		function fetch($sql,$type = "object") {
			return $this->adapter->find_many_by_sql($sql,$type);
		}
		
		
		
	#	Methods for finding single rows	
	
		function find_one_by_sql($sql,$type = "object") {
			return $this->adapter->find_one_by_sql($sql,$type);
		}
	
		// Deprecated - Left over from Friendly 0.5
		function fetch_one($sql,$type = "object") {
			return $this->adapter->find_one_by_sql($sql,$type);
		}
	
	
	
	#	Methods for querying and writing to the database
		function insert($array,$table_name) {
			return $this->adapter->insert($array,$table_name);
		}
	
		function update($array,$table_name,$where_clause = NULL) {
			return $this->adapter->update($array,$table_name,$where_clause);
		}
	
		function sql_query($sql) {
			return $this->adapter->sql_query($sql);
		}
	
		// Deprecated - Left over from Friendly 0.5
		function delete($table_name, $where_clause) {	
			return $this->adapter->delete_by_sql($table_name,$where_clause);
		}
	
		// Deprecated - Left over from Friendly 0.5
		function query($sql) {
			return $this->adapter->sql_query($sql);
		}
		
	#	Utility methods for building sets and such
		function build_set($array,$table_name,$new_record = false) {

			$col_list = $this->find_many_by_sql("DESCRIBE $table_name");

			foreach($col_list as $col) {
				if($col->Field == "created_on" && $new_record)
					$array['created_on'] = date("Y-m-d h:i:s");
				elseif($col->Field == "updated_on")
					$array['updated_on'] = date("Y-m-d h:i:s");		
			}

			// Build set
			foreach($array as $k => $v) {

				# Handle date arrays from Smarty
				if(is_array($v) && $v['Month'] && $v['Day'] && $v['Year']) {
					// Is smarty date -- make string and continue
					$dS = sprintf("%04d-%02d-%02d",$v['Year'],$v['Month'],$v['Day']);

					if($v['Hour'] && $v['Minute'])
						$dS .= sprintf(" %02d:%02d:00",$v['Hour'],$v['Minute']);

					$v = $dS;
				}
				# Non-date arrays should be serialized
				elseif(is_array($v))
					$v = serialize($v);

				$scratch[] = "`$k` = '".mysql_escape_string($v)."'";
			}

			$set = implode(", ", $scratch);
			unset($scratch);

			return $set;
		}

}

?>