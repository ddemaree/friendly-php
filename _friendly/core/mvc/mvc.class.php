<?php

class FriendlyMVC {
	
	var $controller;
	
	function FriendlyMVC() {
		
		// Handle routing
		$this->route();
		
		// Get model classes and include them
		$this->load_models();

		// Run the controller
		$this->request();
		
		// Run display
		$this->display();
		
	}
	
	function route() {
		global $cfg;	

		// Derive route from REQUEST_URI if not already set
		// Allows for support for _GET[route], i.e. backward compatibility with Friendly 0.x
		if(!$_GET['route']) {
			$_GET['route'] =& $_SERVER['REQUEST_URI'];
			$_GET['route'] =  preg_replace("/^\/(.*)$/","$1",$_GET['route']);
		}
		
		if(preg_match("/\?/",$_GET['route'])) {
			$offset = strpos($_GET['route'],"?");
			$_GET['route'] = substr($_GET['route'],0,$offset);
		}
		
		// Break URI into route array
		$_GET['route'] = explode("/",$_GET['route']);
		$route =& $_GET['route'];
		
		
		// Now attempt to correct for subfolder
		if($_SERVER['PHP_SELF'] != '/_index.php') {
			
			// Turn filesytem path into an array
			$file_path = explode("/",$_SERVER['PHP_SELF']);
			array_shift($file_path); // Correcting for leading slash
			
			// Downshifting the filesytem and URL paths together until we
			// catch up with the _index file
			while($file_path[0] != '_index.php') {
				$_base_path[] = $file_path[0];
				
				array_shift($route);
				array_shift($file_path);
			}
			$GLOBALS['base_path'] = implode("/",$_base_path);
		}
		
		$_protocol = $_SERVER['HTTPS'] == 'on' ? "https" : "http";
		
		#	Set default environment var for the base url
		$GLOBALS['http_host'] 		=  "{$_protocol}://".$_SERVER['SERVER_NAME']."/";
		$GLOBALS['base_url']  		=  $GLOBALS['http_host'].$GLOBALS['base_path'].($GLOBALS['base_path'] ? "/" : "");
		$GLOBALS['cfg']['base_url'] =& $GLOBALS['base_url'];
		
		define('BASE_URL',$GLOBALS['base_url']);
		
		
		// EXPERIMENTAL: RegEx-based URL remapping
		
		load_file(FRIENDLY_ROOT."/config/routes.inc.php");
		
		
		#  Default parameter naming
		
		if(is_dir($app_space = FRIENDLY_APP_PATH."{$route[0]}/")) {
			$routing_map = array('route' => 'app_space/controller/action/id');
		
			$_params = array(
				'app_space'	 => $route[0],
				'controller' => 'home',
				'action'	 => 'index'
			);
		}
		else {
			$routing_map = array('route' =>"controller/action/id");
			
			$_params = array(
				'controller' => 'home',
				'action'	 => 'index'
			);
		}
		
		$request_uri = implode("/",$route);
		
		foreach($GLOBALS['routes'] as $_regex => $_map) {
			
			if(preg_match($_regex,$request_uri)) {
				$routing_map = $_map;
				break;
			}
			
		}
		$routing_map_names = explode("/",$routing_map['route']);
		
		$y = 0;
		for($x = 0; $route[$x]; $x++) {
			if($routing_map_names[$x])
				$_params[$routing_map_names[$x]] = $route[$x];
			
			$_params['params'][$x] = $route[$x];
		}
		
		$_params = array_merge($_params, $routing_map);
		$_REQUEST = array_merge($_REQUEST, $_params);
		
		
		#  Set FRIENDLY_APP_SPACE constant to shift into a different set of app folders
		if($_REQUEST['app_space'] && file_exists($app_space = FRIENDLY_APP_PATH."{$_REQUEST['app_space']}/")) {
			define("FRIENDLY_APP_SPACE",$_REQUEST['app_space']);
			$_GET['app'] =& $_REQUEST['app_space'];
			array_shift($route);
		}
		
		
		// Assign _GETs incl. default values
		$_GET['c'] =& $_REQUEST['controller'];
		$_GET['a'] =& $_REQUEST['action'];
		$_GET['x'] =  $route[2];
		$_GET['y'] =  $route[3];
		$_GET['z'] =  $route[4];
		
		$GLOBALS['controller'] = array(
			'controller_name' => $_REQUEST['controller'],
			'action_name' 	  => $_REQUEST['action'],
		);
		
		
		// Populate _GET[params]
		for($x = 2; $_thisParam = $route[$x]; $x++) {
			$_GET['params'][$x - 1] = $_thisParam;
		}
	}
	
	function request() {
		global $cfg;

		#	Normalize our controller & action names.
			$c =& $_GET['c'];
			$a =& $_GET['a'];
		
		
		// Global before filters from the config file  ## DEPRECATED in 1.0x ##
		if($GLOBALS['cfg']['before_filters']) {
			$filters = explode(",",$GLOBALS['cfg']['before_filters']);
			
			foreach($filters as $function)
				if(function_exists($function)) $function();
		}


		// Set up path to app folders based on whether we're in the root or an app space
		// E.g.: app/controllers vs. app/admin/controllers
		if(defined("FRIENDLY_APP_SPACE"))
			$_app_path = FRIENDLY_APP_PATH.FRIENDLY_APP_SPACE."/";
		else
			$_app_path = FRIENDLY_APP_PATH;

		
		// Find and run controller & action
		if(file_exists($controlFile = $_app_path."controllers/{$_GET['c']}_controller.php")) {
			load_file($controlFile);

		
			if(class_exists($cU = camelize($c)."Controller")) {
				
				// Instantiate controller class
				$this->controller = new $cU();
				
				// Run _setups to get 
				if(method_exists($this->controller,"_globalsetup"))
					$this->controller->_globalsetup();
				if(method_exists($this->controller,"_setup"))
					$this->controller->_setup();
				
				// Run _init to commit any setup changes relevant to Magic Controllers
				if(method_exists($this->controller,"_init"))
					$this->controller->_init();
				
				// Running before filters as specified in the _setups
				if(method_exists($this->controller,"_bootstrap"))
					$this->controller->_bootstrap();
					
					
					
				// Run action method $a
				if(method_exists($this->controller,$a)){
					$this->controller->$a();
		
					#	Filter out internal stuff and send instance vars to Smarty
						$_instance_vars = get_object_vars($this->controller);
						foreach($_instance_vars as $_k => $_v) {
							if($_k[0] == "_")
								unset($_instance_vars[$_k]);
						}
						smarty_assign_array($_instance_vars);
				}
				
				
				
				// Running after filters as specified in the _setups
				if(method_exists($this->controller,"_teardown"))
					$this->controller->_teardown();
			
				// User-level cleanup function?
				if(method_exists($this->controller,"_cleanup"))
					$this->controller->_cleanup();
				
			}
			else
				raise("The controller <code>{$c}</code> was not found.");
		}
		else
			raise("The controller <code>{$c}</code> was not found");
	}
	
	
	function load_models() {
		global $cfg;
		
		if(file_exists($env_file = $cfg['basedir']."/config/environment.inc.php"))
			load_file($env_file);

		if(file_exists($model_dir = "./app/models")) {
			$d = dir($dp = $model_dir);
			while($f = $d->read()) {
				$f = "{$dp}/{$f}";
				if(is_file($f) && preg_match("/\.model\.php$/",$f))
					load_file($f);
			}
			$d->close();
		}
	}


	function display() {		
	   	global $cfg;
		global $smarty;
		
		if($_SESSION['flash']) {
        	smarty_assign('flash', $_SESSION['flash']['text']);
        	smarty_assign('flash_class', $_SESSION['flash']['class']);
			smarty_assign('problems',$_SESSION['flash']['problems']);
        	unset($_SESSION['flash']);
    	}

		if(!is_writeable($smarty->compile_dir))
			raise("The compile directory <code>$smarty->compile_dir</code> is not writeable.");
			

		$smarty->assign('session',$_SESSION);
		$smarty->assign('cfg',$cfg);
		$smarty->assign('globals',$GLOBALS);
		$smarty->assign('controller',$GLOBALS['controller']);
		
		
		#  Set up path to app folders based on whether we're in the root or an app space
		#  E.g.: app/controllers vs. app/admin/controllers
		if(defined("FRIENDLY_APP_SPACE"))
			$smarty->template_dir = FRIENDLY_APP_PATH.FRIENDLY_APP_SPACE."/views/";

			
		$controller_name = strtolower($GLOBALS['controller']['controller_name']);

		
		#  Allow for ability to set template file in controller
		if($this->controller->_template_file) {
			$template_file	 = $this->controller->_template_file;
		}
		else {
			$template_folder = $controller_name;
			$template_file	 = $template_folder."/".$GLOBALS['controller']['action_name'];
		}

		#  Absolute path to template
		$path_to_template	 = $smarty->template_dir . $template_file;
		
		
		#  Find and render action template
		if(file_exists($path_to_template)) {
			$smarty->clear_compiled_tpl($template_file);
			$content_for_layout = $smarty->fetch($template_file);
			$smarty->assign('content_for_layout',$content_for_layout);
		}
		elseif(file_exists($path_to_template.".html")) {
			$smarty->clear_compiled_tpl($template_file.".html");
			$content_for_layout = $smarty->fetch($template_file.".html");
			$smarty->assign('content_for_layout',$content_for_layout);
		}
		elseif(file_exists($file = $path_to_template.".textile")) {
			$content_for_layout = textile($smarty->fetch($file));
			$smarty->assign('content_for_layout',$content_for_layout);
		}
		elseif(file_exists($file = $path_to_template.".markdown")) {
			$content_for_layout = Markdown($smarty->fetch($file));
			$smarty->assign('content_for_layout',$content_for_layout);
		}
		else {
			raise("No template for the action <code>{$_GET['a']}</code>.");
		}


		#  Find and render layout
		if(isset($this->controller->_layout) && $this->controller->_layout)
			if(file_exists($smarty->template_dir . ($layoutFile = "_layouts/{$this->controller->_layout}.html") )){
				$smarty->clear_compiled_tpl($layoutFile);
				$smarty->display($layoutFile);
			}
			elseif(file_exists($smarty->template_dir . ($layoutFile = "_layouts/{$controller_name}.html") )) {
				$smarty->clear_compiled_tpl($layoutFile);
				$smarty->display($layoutFile);
			}
			else
				raise("The layout <code>$layoutFile</code> could not be found.");
		else
			echo $content_for_layout;
	}
	
}


?>