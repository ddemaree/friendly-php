<?php

function session_open() {
    global $cfg;
	global $DB;
	
	$_opts = array(
		'name' 				=> 'FRIENDLY_SESS',
		'expires'     		=> '86400',
		'path'		  		=> '/',
		'domain'	  		=> $_SERVER['SERVER_NAME'],
		'storage_method'	=> 'db'
	);
	$_opts = array_merge($_opts, $cfg['session']);
	
	session_name($_opts['name']);
	session_set_cookie_params($_opts['expires'],$_opts['path'],$_opts['domain']);
	
	if(!$_opts['storage_method'] == 'db')
		session_set_save_handler("friendly_session_open", "friendly_session_close", "friendly_session_read", "friendly_session_write", "friendly_session_destroy", "friendly_session_gc");

	session_start();
}

function friendly_session_open($save_path, $session_name) {
	
	// If sessions table does not exist, create it
	if(!mysql_num_rows(mysql_query("SELECT * FROM _friendly_sessions"))) {
		mysql_query("CREATE TABLE `_friendly_sessions` (`id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,`sessid` VARCHAR(255),`data` TEXT,`created_on` DATETIME);");
		if(mysql_errno())
			raise("Could not create sessions table.");
		else return true;
	}
	
	return(true);
}

function friendly_session_close() {
	return true;
}


function friendly_session_read($id) {
	if($_session_data = db_fetch_one($sql = "SELECT * FROM _friendly_sessions WHERE sessid = '{$id}' ORDER BY created_on LIMIT 1")) {
		return $_session_data->data;
	}
	else {
		db_insert(array('sessid' => $id),'_friendly_sessions');
		return "";
	}
}


function friendly_session_write($id,$sess_data){
	if(db_update(array('data'=>$sess_data),'_friendly_sessions',"sessid = '{$id}'"))
		return true;
	else
		raise("Could not save session data to database.");
}

function friendly_session_destroy($id) {
	return db_delete('_friendly_sessions',"sessid = '{$id}'");
}


/*********************************************
 * WARNING - You will need to implement some *
 * sort of garbage collection routine here.  *
 *********************************************/
function friendly_session_gc($maxlifetime)
{
  return true;
}





function flash($text, $class="message") {
    unset($_SESSION['flash']);

    $_SESSION['flash'] = array(
        'text'  => $text,
        'class' => $class
    );
    
    smarty_assign('flash',$_SESSION['flash']['text']);
    smarty_assign('flash_class',$_SESSION['flash']['class']);

    return true;
}


?>