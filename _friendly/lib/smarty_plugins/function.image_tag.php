<?php

function smarty_function_image_tag($params, &$smarty)
{
	return asset_tag("image",$params['src'],extract_options($params,"/src/i"));
}

?>