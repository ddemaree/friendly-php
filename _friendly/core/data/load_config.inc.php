<?php

function load_config() {
	
	#If config path isn't already defined, set it to /public/config/
	if(!defined(FRIENDLY_CONFIG_PATH)) 
		define(FRIENDLY_CONFIG_PATH, FRIENDLY_ROOT."/config/");
	
	
	# Determine which Config format we're using and load 'er up
	if(file_exists($config_php_file = FRIENDLY_CONFIG_PATH.'config.inc.php')) {
		# 	Load config
			require $config_php_file;
		
		#	Setting extension for remaining configs (environments, et al) to PHP-style
			$config_ext = ".inc.php";
	}
	elseif(file_exists($config_yml_file = FRIENDLY_CONFIG_PATH.'config.yml')) {
		#	Load and parse config
			$cfg = yaml_unserialize($config_yml_file);
		
		#	Setting extension for remaining configs
			$config_ext = ".yml";
	}
	else {
		raise("The config file could not be found.");
	}
	
	
	# Environments -- allows multiple server setups to be stored and switched via a single variable
	if($cfg['environment']) {
		if($config_ext == ".yml" && file_exists($eF = FRIENDLY_CONFIG_PATH."environments/{$cfg['environment']}.yml")) {
			
			#	Load and parse environment vars
				$env_config = yaml_unserialize($eF);
			
			#	Merge environment array ($env_config) with the root array
				$cfg = array_merge($cfg, $env_config);
		
		}
		elseif($config_ext == ".inc.php" && file_exists($eF = FRIENDLY_CONFIG_PATH."environments/{$cfg['environment']}.inc.php")) {
			
			#	Load environment vars
				include $eF;
			
			#	Merge environment array ($cfg[$environment]) with the root array
				$cfg = array_merge($cfg, $cfg[$cfg['environment']]);
				
		}
		
	}
	
	#	Set environment var for the root path
		$cfg['basedir'] = FRIENDLY_PUBLIC_PATH; #realpath('./');

	
	#	Assign a config item for friendly_version for backward compatibility
		$cfg['friendly_version'] = FRIENDLY_VERSION;
	
	#	Make global copy, return to loader
		$GLOBALS['cfg'] = $cfg;
		return $cfg;
}



?>