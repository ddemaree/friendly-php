<?php

##  Friendly: An easier way to build websites with PHP
##  --------------------------------------------------
##  
##  Version 1.0 Developer Preview / Released February 15, 2006    
##  <http://friendlyphp.org/>
##  
##  Friendly is a small and simple MVC-style web development framework intended for use
##  in server environments where more powerful development tools may not be an option --
##  in other words, a common shared hosting plan. Friendly contributes structure and clarity
##  to your code with an easy learning curve and no unnecessary hassle.
##  
##  Information on installing and using Friendly can be found in ./docs/getting_started.html,
##  or on the web at <http://friendlyphp.org/docs/getting_started>.
##  
##  This software is made available free of charge under the MIT license. For specific terms
##  please read the MIT_LICENSE file.
##  
##  Friendly is open-source software; feel free to make modifications as you see fit.
##  Under the terms of the MIT license you are not required to report or submit any changes
##  to the source code. That being said, any improvements or patches you'd like to share with
##  the community of Friendly users are very much appreciated -- to submit a patch or get more
##  information, please e-mail <info@friendlyphp.org>
##  
##  This version of Friendly incorporates the following third-party open source projects:
##  
##  * [Smarty](http://smarty.php.net), a templating engine for PHP.
##  * [Prototype](http://prototype.conio.net/), a JavaScript library for easy Ajax and DHTML.
##  * [script.aculo.us](http://script.aculo.us/), a collection of special effects extensions for Prototype.
##  * [Textile](http://textism.com/tools/textile/), a humane text formatting syntax
##  * [Spyc](http://spyc.sourceforge.net/), a PHP-based solution for parsing and making YAML files.



#	Hello, my name is Friendly. My version is:
	define(FRIENDLY_VERSION,"1.0d10");
	ini_set("display_errors",false);
	

#	Infer path to app's public directory (i.e., the location of the dispatcher)

	define(FRIENDLY_PUBLIC_PATH,realpath("./"));
	

#	Get/infer paths to Friendly libraries
	
	if($friendly_path || $GLOBALS['friendly_path']) {
		define('FRIENDLY_PATH',$friendly_path);
	}
	elseif(file_exists($friendly_path = realpath("../")."/_friendly/") && is_dir($friendly_path)) {
	// 	Rails-style layout
	// 	Friendly located one level beneath the dispatcher (the dispatcher presumably being in ../public)
		
		define('FRIENDLY_PATH',$friendly_path);
		define('FRIENDLY_ROOT',realpath("../"));
		define('FRIENDLY_MODE',"rails");
	}
	elseif(file_exists($friendly_path = realpath("./")."/_friendly/") && is_dir($friendly_path)) {
	//	Cake-style layout
	//	Friendly, app and config located on same level as dispatcher
	
		define('FRIENDLY_PATH',$friendly_path);
		define('FRIENDLY_ROOT',realpath("./"));
		define('FRIENDLY_MODE',"cake");
	}


#	Set default config path
	if($friendly_config_path)
		define('FRIENDLY_CONFIG_PATH',$friendly_config_path);
	elseif(!defined(FRIENDLY_CONFIG_PATH))
		define('FRIENDLY_CONFIG_PATH',FRIENDLY_ROOT."/config/");

#	Set default app path
	if($friendly_app_path)
		define('FRIENDLY_APP_PATH',$friendly_app_path);
	elseif(!defined(FRIENDLY_APP_PATH))
		define('FRIENDLY_APP_PATH',FRIENDLY_ROOT."/app/");


#	Load Friendly libraries
	require FRIENDLY_PATH."core/friendly.class.php";
	require FRIENDLY_PATH."core/db/_load.php";
	require FRIENDLY_PATH."core/data/_load.php";
	require FRIENDLY_PATH."core/display/_load.php";
	require FRIENDLY_PATH."core/support/_load.php";
	require FRIENDLY_PATH."core/mvc/_load.php";

#	Load config data
	$cfg = load_config();

#	Instantiate useful classes
    $smarty = smarty_start();

#	Instantiate the DB connection if it's been configured
	if(!empty($cfg['db']['adapter']))
		$DB = new FriendlyDB($cfg['db']);

#   Open a session
    session_open();


?>