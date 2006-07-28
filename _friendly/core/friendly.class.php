<?php

# Loader function to avoid PHP's no-includes-within-class-defs restriction
function load_file($file) {
	global  $cfg;
	include $file;
}

class Friendly
{

	function Friendly($start = true,$load = true,$options = false) {
		
		if(is_array($options))
			foreach($options as $_k => $_v)
				$this->$_k = $_v;

		if($start)
			$this->start();
	}
	
	function start() {
		session_open();
		$f = new FriendlyMVC;
	}
	
	function export() {
		return get_object_vars($this);
	}
	
}

?>