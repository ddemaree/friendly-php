<?php

function smarty_function_asset_tag($params, &$smarty)
{
	return asset_tag(($params['type'] ? $params['type'] : "image"),$params['file'],extract_options($params,"/type|file/i"));
}


?>