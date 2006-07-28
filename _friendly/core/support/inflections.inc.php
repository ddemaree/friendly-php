<?php

function camelize_upcase($matches) {
	return strtoupper($matches[2]);
}

function camelize($lower_case_and_underscored_word) {
	return preg_replace_callback("/(^|_)(.)/","camelize_upcase",$lower_case_and_underscored_word);
}

function underscore($camel_cased_word) {
	$return = preg_replace("/([A-Z]+)([A-Z][a-z])/","$1_$2",$camel_cased_word);
	$return = preg_replace("/([a-z\d])([A-Z])/","$1_$2",$return);
	$return = strtolower($return);
	return $return;
}


function inflect($word,$type) {
	
	$inflections_plural = array(
		
		// Irregulars
		"/person$/i" => 'people',
		"/man$/i"	 => 'men',
		"/child$/i"  => 'children',
		"/sex$/i"	 => 'sexes',
		"/move$/i"	 => 'moves',
			
		"/(alias|status)$/i"		=> "$1es",
		"/(octop|vir)us$/i"			=> "$1i",
		"/(ax|test)is$/i"			=> "$1es",
		"/(bu)s$/i"					=> "$1ses",
		"/(buffal|tomat)o$/i"		=> "$1oes",
		"/([ti])um$/i"				=> "$1a",
		"/sis$/i"					=> "ses",
		"/(?:([^f])fe|([lr])f)$/i"	=> "$1$2ves",
		"/(hive)$/i"				=> "$1s",
		"/([^aeiouy]|qu)y$/i" 		=> "$1ies",
		"/([^aeiouy]|qu)ies$/i" 	=> "$1y",
		"/(x|ch|ss|sh)$/i" 			=> "$1es",
		"/(matr|vert|ind)ix|ex$/i" 	=> "$1ices",
		"/([m|l])ouse$/i" 			=> "$1ice",
		"/^(ox)$/i" 				=> "$1en",
		"/(quiz)$/i" 				=> "$1zes",
		"/s$/i"						=> 's',
		"/$/"						=> "s"

	);
	
	$inflections_singular = array(
		
		// Irregulars
		"/people$/i" 	=> 'person',
		"/men$/i"	 	=> 'man',
		"/children$/i"  => 'child',
		"/sexes$/i"	 	=> 'sex',
		"/moves$/i"	 	=> 'move',
		
		"/(n)ews$/i" => "$1ews",
		"/([ti])a$/i" => "$1um",
		"/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i" => "$1$2sis",
		"/(^analy)ses$/i" => "$1sis",
		"/([^f])ves$/i" => "$1fe",
		"/(hive)s$/i" => "$1",
		"/(tive)s$/i" => "$1",
		"/([lr])ves$/i" => "$1f",
		"/([^aeiouy]|qu)ies$/i" => "$1y",
		"/(s)eries$/i" => "$1eries",
		"/(m)ovies$/i" => "$1ovie",
		"/(x|ch|ss|sh)es$/i" => "$1",
		"/([m|l])ice$/i" => "$1ouse",
		"/(bus)es$/i" => "$1",
		"/(o)es$/i" => "$1",
		"/(shoe)s$/i" => "$1",
		"/(cris|ax|test)es$/i" => "$1is",
		"/([octop|vir])i$/i" => "$1us",
		"/(alias|status)es$/i" => "$1",
		"/^(ox)en/i" => "$1",
		"/(vert|ind)ices$/i" => "$1ex",
		"/(matr)ices$/i" => "$1ix",
		"/(quiz)zes$/i" => "$1",
		"/s$/i" => ""
	);
	
	## First check to make sure that it's not irregular or uncountable
	$inflections_uncountable = array("equipment","information","rice","money","species","series","fish","sheep");
	
	foreach($inflections_uncountable as $_uc_word) {
		if(preg_match("/{$_uc_word}/i",$word)) {
			return $word;
		}
	}
	
	## Now we can pluralize/singularize
	$array = "inflections_".$type;
	$array = $$array;

	foreach($array as $_from => $_to) {
		if(preg_match($_from,$word)) {
			return preg_replace($_from,$_to,$word);
		}
	}
	
	return $word;
}


function pluralize($word) {
	return inflect($word,'plural');	
}

function singularize($word) {
	return inflect($word,'singular');	
}

function table_to_class($table_name) {
	$class_name = singularize($table_name);
	$class_name = camelize($class_name);
	return $class_name;
}

function class_to_table($class_name) {
	$table_name = underscore($class_name);
	$table_name = pluralize($table_name);
	return $table_name;
}

?>