<?php

function smarty_function_asset_path($params, &$smarty)
{
	return asset_path(($params['type'] ? $params['type'] : "image"),$params['file']);
}

?>