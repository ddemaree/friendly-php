<?php


function url_for($path) {
	
	if(is_array($path) && $path['path']) {
		return BASE_URL.$path['path'];
	}
	elseif(is_array($path)) {
		
		if(!$path['controller'])
			$_controller = $_REQUEST['controller'];		
		else
			$_controller = $path['controller'];
			
		
		if($_REQUEST['app_space'] && !preg_match("/\/(\w+)/i",$_controller,$_matches) && !$path['app_space']) {
			$_controller = $_REQUEST['app_space'] . "/" . $_controller;
		}
		else {
			$_controller = $_matches[1];
		}
		
		$_root = BASE_URL."{$_controller}";
		
		$_others = array('action','id','sid');
		
		foreach($_others as $_key) {
			if($path[$_key])
				$_root .= "/{$path[$_key]}";
			else
				break;
		}
		
		return $_root;
	}
	else {
		/* STUB: Need function for quickly generating URLs */
		return BASE_URL.$path;
	}
}

function redirect_to($dest) {
	header("Location: ".url_for($dest));
	friendly_exit();
}


?>