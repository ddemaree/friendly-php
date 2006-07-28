<?php

function friendly_error($error_msg) {
	global $friendly_path;
	global $cfg;
	
	if($cfg['compile_dir'])
		$compile_path = $cfg['basedir']."/".$cfg['compile_dir'];
	else {
		$compile_path = "/tmp";
	}
	
	if(!is_writeable($compile_path))
		friendly_exit("Error: ".$error_msg."<br><br>Additionally, the compile directory <code>$compile_path</code> is not writeable.");
	
	#  This function logs an error, then displays a pretty, templated error message. 
	$internal_smarty = new Smarty();
	
	$internal_smarty->template_dir = $friendly_path."/lib/views";
	$internal_smarty->compile_dir  = $compile_path;
	
	$internal_smarty->assign('error_msg',$error_msg);
	$internal_smarty->display('error.html');
	
	friendly_exit();
}

function raise($error_msg) {
	friendly_error($error_msg);
}



?>