<?php

function smarty_function_link_to($params, &$smarty)
{
    $extra = '';

    /*if (empty($params['address'])) {
        $smarty->trigger_error("mailto: missing 'address' parameter");
        return;
    } else {
        $address = $params['address'];
    }*/
	global $cfg;
	
	
	$_url = $cfg['base_url'];

	if($params['path']) {
		$_url .= $params['path'];		
		
		if($params['id'])
			$_url .= $params['id'];
			
		return $_url;
	}
	elseif($params['controller'] || $params['action']) {
	
		if($params['controller'] && !$params['action']) {
			$_url .= "{$params['controller']}/";
		}
		elseif($params['controller'] && $params['action']) {
			$_url .= "{$params['controller']}/{$params['action']}/";
			
			if($params['id']) {
				$_url .= $params['id'];
				
				if($params['y'])
					$_url .= "/{$params['y']}";
			}
		}
		elseif(!$params['controller'] && $params['action']) {
			$params['controller'] = $_GET['c'];
			$_url .= "{$params['controller']}/{$params['action']}/";
			
			if($params['id'])
				$_url .= $params['id'];
			elseif(is_numeric($_GET['x']) && !$params['id']) {
				$_url .= $_GET['x'];
			}
			
			if($params['y'])
				$_url .= "/{$params['y']}";
		}
	}
	
	return $_url;
}

/* vim: set expandtab: */

?>
