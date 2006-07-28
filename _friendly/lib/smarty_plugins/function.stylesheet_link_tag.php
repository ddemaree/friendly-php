<?php

function smarty_function_stylesheet_link_tag($params, &$smarty)
{
	return asset_tag("stylesheet",$params['href'],extract_options($params,"/href/i"));
}

?>