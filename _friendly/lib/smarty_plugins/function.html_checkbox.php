<?php

function smarty_function_html_checkbox($params, &$smarty) {
	$checked = ($params['input'] == $params['value'] || $params['checked']) ? 'checked="checked" ' : '';
	
	return "<input type=\"checkbox\" name=\"{$params['name']}\" value=\"{$params['value']}\" class=\"CheckBox {$params['class']}\" {$checked}/>";
}

?>