<?php

require FRIENDLY_PATH."vendor/smarty/Smarty.class.php";


/*---- Startup function(s) ----*/

function smarty_start() {
	global $cfg;

	#	Instantiate, set up global Smarty
		$smarty = new Smarty;
		$smarty->caching = false;
		$smarty->template_dir = FRIENDLY_APP_PATH."views/";
		
	#	Set the template tag delimiters
		$smarty->left_delimiter  = $cfg['tag_format']['left']  ? $cfg['tag_format']['left']  : "{{";
		$smarty->right_delimiter = $cfg['tag_format']['right'] ? $cfg['tag_format']['right'] : "}}";
	
	#	Infer/set Smarty's compile directory
		if($cfg['compile_dir'])
			$smarty->compile_dir = FRIENDLY_ROOT."/".$cfg['compile_dir'];
		elseif(is_writeable(FRIENDLY_ROOT."cache/"))
			$smarty->compile_dir = FRIENDLY_ROOT."cache/";
		else
			$smarty->compile_dir = "/tmp";
			
	#	Adding a plugins_dir for Friendly's view helpers and extensions
		$smarty->plugins_dir[] = FRIENDLY_PATH."lib/smarty_plugins";
		$smarty->plugins_dir[] = FRIENDLY_ROOT."/lib/smarty_plugins"; // FRIENDLY_ROOT lacks a trailing slash for some reason
	
	#	Assigning some fixtures to Smarty
		$fixtures = yaml_unserialize(FRIENDLY_PATH."lib/fixtures.yml");
		$smarty->assign($fixtures);
	
	return $smarty;
}


/*---- Assignment functions ----*/

function smarty_capture($name,$value) {
	global $smarty;
	$smarty->assign($name,$value);
}

function smarty_assign_array($array) {
	global $smarty;
	$smarty->assign($array);
}

function smarty_assign($n,$v) {
	smarty_capture($n,$v);
}


/*---- Template output functions ----*/

function smarty_fetch($_template = false, $_locals = false) {
	global $smarty;
	
	if(defined("FRIENDLY_APP_SPACE"))
		$smarty->template_dir = FRIENDLY_APP_PATH.FRIENDLY_APP_SPACE."/views/";
		
	if(!$_template)
		$_template = "{$_REQUEST['controller']}/{$_REQUEST['action']}.html";
	
	if($_locals) {
		# $_locals can be either an array of variables in the form 'varname' => $value,
		# or an object. If it's an object, Friendly assumes that it's a controller and
		# extracts variables just as in FriendlyMVC#route
		
		if(is_array($_locals))
			$smarty->assign($_locals);
		elseif(is_object($_locals)) {
			
			$_controller =& $_locals;
			
			$_instance_vars = get_object_vars($_controller);
			foreach($_instance_vars as $_k => $_v) {
				if($_k[0] == "_")
					unset($_instance_vars[$_k]);
			}
			
			$smarty->assign($_instance_vars);
		
		}
	}
	
	$smarty->assign('cfg',$GLOBALS['cfg']);
	#return $_template;
	
	if(file_exists($tp = $smarty->template_dir . $_template))
		$_response = $smarty->fetch($_template);
	else {
		if(is_prototype_request()) {
			$js = new PrototypeHelper;
			$js->alert("The template {$tp} could not be found.");
			$js->send();
		}
		else
			raise("The template {$tp} could not be found.");
	}
	
	return $_response;
	
	#if($_ret = $smarty->fetch($_template))
	#	return $_ret;
	#else
	#	return "No response from template {$_template}";
}

function render_partial($_tpl = false, $_locals = false) {
	return smarty_fetch($_tpl,$_locals);
}

function smarty_render_partial() {
	if(!$tpl = func_get_arg(0))
		$tpl = "{$_GET['c']}/{$_GET['a']}.tpl";
	elseif(func_get_arg(1))
		$tpl = $tpl;
	else
		$tpl = "{$_GET['c']}/_{$tpl}.tpl";

	global $smarty;
	return $smarty->fetch($tpl);

}

function smarty_return() {
	global $smarty;
	global $cfg;
	
	if(!$tpl = func_get_arg(0))
		$tpl = "{$_GET['c']}/{$_GET['a']}.tpl";
	
	if(!file_exists($smarty->template_dir.$tpl))
		friendly_exit("Error: The template <code>$tpl</code> does not exist.");
	elseif(!is_writeable($smarty->compile_dir))
		friendly_exit("Error: The compile directory <code>{$smarty->compile_dir}</code> is not writeable.");
	
	friendly_exit($smarty->fetch($tpl));
}

function smarty_ajax() {
	global $smarty;
	
	$headers = getallheaders();
	
	if($headers["X-Prototype-Version"]) {
		$template = "{$_GET['c']}/{$_GET['a']}.tpl";
		$smarty->display($template);	
		friendly_exit();
	}
}

function smarty_clear($varname) {
	global $smarty;
	$smarty->clear_assign($varname);
}

function smarty_replace($varname,$newvalue) {
	global $smarty;
	$smarty->clear_assign($varname);
	$smarty->assign($varname,$newvalue);
}


?>