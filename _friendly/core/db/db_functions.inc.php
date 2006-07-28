<?php


/*---- DB interface functions (because I don't want to have to global the DB) ----*/


function db_delete($table_name, $where_clause) {
	global $DB;
	return $DB->delete($table_name, $where_clause);
}

function db_fetch($sql,$type = "object") {
	global $DB;
	return $DB->fetch($sql,$type);
}

function db_fetch_one($sql,$type = "object") {
	global $DB;
	return $DB->fetch_one($sql,$type);
}

function db_unserialize_all($array) {
	foreach($array as $k => $v)
		$array[$k] = ($uv = unserialize($v)) ? $uv : $v;
	return $array;
}

function db_insert($array,$table_name) {
	global $DB;
	return $DB->update($array,$table_name,NULL);
}

function db_query($sql) {
	global $DB;
	return $DB->query($sql);
}

function db_update($array,$table_name,$where_clause = NULL) {
	global $DB;
	return $DB->update($array,$table_name,$where_clause);
}



#	Utility methods for building sets and such
function db_build_set($array,$table_name,$new_record = false) {
	global $DB;
	return $DB->build_set($array,$table_name,$new_record);
}



?>