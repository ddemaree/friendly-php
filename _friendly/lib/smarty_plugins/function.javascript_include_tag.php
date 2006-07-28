<?php

function smarty_function_javascript_include_tag($params, &$smarty)
{
	if($params['src'] == "defaults") {
		$_defaults = array('lib/prototype.js','lib/scriptaculous.js','application.js');
		foreach($_defaults as $_file)
			$_output .= asset_tag("javascript",$_file) . "\n";
		
		return $_output;
	}
	else
		return asset_tag("javascript",$params['src'],extract_options($params,"/src/i"));
}


?>