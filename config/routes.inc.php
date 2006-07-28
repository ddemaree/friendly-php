<?php

##  Routes are custom URL mappings used by Friendly to determine what
##  controller/action method to pass a given request to.
##
##  The default route is controller/action/id


$GLOBALS['routes'] = array(
	
	"/^$/i" => array(
		'controller' => home,
		'action' => index
	),
	
	"/^(.*)$/i" => array(
	    'route' => 'controller/action/id'
	)

);

?>