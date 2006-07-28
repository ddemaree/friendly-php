<?php

function is_prototype_request() {
	if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
		return true;
	}
	
	return false;
}

function send_javascript($js_string) {
	header("Content-Type: text/javascript");
	print $js_string;	
}


function escape_javascript($js) {
	$js = preg_replace("/\r\n|\n|\r/", "\\n", $js);
	$js = preg_replace("/([\"'])/","\\1",$js);
	return $js;
}


class PrototypeHelper {
	
	function PrototypeHelper() {
		header("Cache-control: no-cache");
		header("Pragma: no-cache");
		header("Content-type: text/javascript");
		$this->_js_content = '';
	}
	
	function alert($message) {
		$_js = "alert('" . escape_javascript($message) ."');\n";
		$this->_js_content .= $_js;
		return $_js;
	}
	
	function js_code($js_string) {
		#$js_string = trim($js_string);
		
		if(!preg_match("/;$/i",$js_string))
			$js_string .= ";";
		
		$this->_js_content .= $js_string;
	}
	
	function update_html($element_id,$content) {
		$content = escape_javascript($content);
		$js = "Element.update('{$element_id}','{$content}');\n";
		$this->_js_content .= $js;
		return $js;
	}
	
	function insert_html($element_id,$insertion_position,$content) {
		$content = escape_javascript($content);
		$insertion_position = ucwords($insertion_position);
		$this->_js_content .= "new Insertion.{$insertion_position}('{$element_id}','{$content}');\n";
	}
	
	function show_element() {
		$element_ids = func_get_args();
		foreach($element_ids as $element_id) {
			$_js_content .= "Element.show('{$element_id}');\n";
		}
		$this->_js_content .= $_js_content;
		return $_js_content;
	}
	
	function hide_element() {
		$element_ids = func_get_args();
		foreach($element_ids as $element_id) {
			$_js_content .= "Element.hide('{$element_id}');\n";
		}
		$this->_js_content .= $_js_content;
		return $_js_content;
	}
	
	function remove_element() {
		$element_ids = func_get_args();
		foreach($element_ids as $element_id) {
			$_js_content .= "Element.remove('{$element_id}');\n";
		}
		$this->_js_content .= $_js_content;
		return $_js_content;
	}
	
	function send() {
		echo $this->_js_content;
		$this->_js_content = '';
		friendly_exit();
	}
	
}


?>