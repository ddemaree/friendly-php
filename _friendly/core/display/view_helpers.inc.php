<?php

function asset_path($type = "image",$file) {
	$base_url = BASE_URL."assets/";
	
	// Infer type based on file extension
	if(preg_match("/[a-z0-9\-_]+\.(jpg|gif|png|js|css|swf)$/i",$file,$_matches)) {
		$ext = $_matches[1];
		
		if(preg_match("/(jpg|gif|png)/i",$ext))
			$type = "image";
		elseif(preg_match("/css/i",$ext))
			$type = "stylesheet";
		elseif(preg_match("/js/i",$ext))
			$type = "javascript";
	}
	
	switch($type) {
		case "javascript":
			$path = "javascripts/";
			break;
		case "stylesheet":
			$path = "stylesheets/";
			break;
		case "image":
		default:
			$path = "images/";
	}
	
	return "{$base_url}{$path}{$file}";
}


function asset_tag($type = "image", $file, $options) {
	$asset_uri = asset_path($type,$file);
	
	switch($type) {
		case "javascript":
			$_tag_name = "script";
			$_tag_style = "paired";
			
			$_attrs['type'] = 'type="text/javascript"';
			$_attrs['src']  = "src=\"{$asset_uri}\"";
			break;
		case "stylesheet":
			$_tag_name = "link";
			$_attrs['href'] = "href=\"{$asset_uri}\"";
			$_attrs['type'] = "type=\"text/css\"";
			$_attrs['rel']  = "rel=\"stylesheet\"";
			// $_attrs['media'] = "media=\"screen\"";
			break;
		case "image":
		default:
			$_tag_name = "img";
			$_attrs['src'] = "src=\"{$asset_uri}\"";
			$_attrs['alt'] = "alt=\"\"";
			
			if(file_exists($_file = FRIENDLY_ROOT."assets/images/".$file)) {
				// Width/height stuff would go here
				$_imginfo = getimagesize($_file);
				$_attrs['height'] = "height=\"{$_imginfo[1]}\"";
				$_attrs['width'] = "width=\"{$_imginfo[0]}\"";
			}
	}
	
	// Build options string
	foreach($options as $_attribute => $_value) {
		$_attrs[$_attribute] = "{$_attribute}=\"{$_value}\"";
	}
	
	$_attrs = implode(" ",$_attrs);

	if($_tag_style == "paired")
		$_tag = "<{$_tag_name} {$_attrs}></{$_tag_name}>";
	else
		$_tag = "<{$_tag_name} {$_attrs} />";
		
	return $_tag;
}

function extract_options($var,$regex) {
	$_options = array();
	
	foreach($var as $_k => $_v) {
		if(!preg_match($regex,$_k))
			$_options[$_k] = $_v;
	}
	
	return $_options;
}


/*=== Time display helpers ===*/

function distance_of_time_in_words($from_time, $to_time = 0) {
	if(is_string($from_time) && !is_numeric($from_time))
		$from_time = strtotime($from_time);
	
	if($to_time != 0 && is_string($to_time) && !is_numeric($to_time))
		$to_time   = strtotime($to_time);
	
	$distance_in_minutes = round(abs($to_time - $from_time) / 60);
	$distance_in_seconds = round(abs($to_time - $from_time));
	
	if($distance_in_minutes <= 1) {
		return $distance_in_minutes == 0 ? "less than a minute" : "1 minute";
	}
	elseif($distance_in_minutes >= 2 && $distance_in_minutes <= 45) {
		return "{$distance_in_minutes} minutes";
	}
	elseif($distance_in_minutes >= 46 && $distance_in_minutes <= 90) {
		return "about 1 hour";
	}
	elseif($distance_in_minutes >= 91 && $distance_in_minutes <= 1440) {
		$distance_in_hours = round($distance_in_minutes / 60);
		return "about {$distance_in_hours} hours";
	}
	elseif($distance_in_minutes >= 1441 && $distance_in_minutes <= 2880) {
		return "1 day";
	}
	else {
		$distance_in_days = round($distance_in_minutes / 1440);
		return "{$distance_in_days} days";
	}

}

function distance_of_time_in_words_to_now($from_time) {
	$to_time = time() - (3600 * 6); // Correcting UTC to CST for consistency w/ Movable Type
	return distance_of_time_in_words($from_time,$to_time);
}

function distance_to_now_with_prejudice($from_time) {
	$to_time = time() - (3600 * 6);
	
	$actual_dist = ($to_time - strtotime($from_time));
	
	$base_text = distance_of_time_in_words($from_time,$to_time);
	
	if($actual_dist > 0)
		return "<span class=\"time_ago\">{$base_text} ago</span>";
	else
		return "<span class=\"in_time\">In {$base_text}</span>";
}


/*=== Textile and Markdown wrapper functions ===*/

function format_as_textile($string,$lite = false) {
	$tx = new Textile();
	return $tx->TextileThis($string,$lite);
}

function format_textile($string,$lite = false) { return format_as_textile($string,$lite); }
function textile($string,$lite=false) {return format_as_textile($string,$lite); }
function smarty_modifier_textile($string,$lite="0") { return format_as_textile($string,$lite); }
function smarty_modifier_format_as_textile($string,$lite = "0") { return format_as_textile($string,$lite); }

function format_as_markdown($string) { return Markdown($string); }
function format_markdown($string) {	return format_as_markdown($string); }
function smarty_modifier_format_as_markdown($string) { return format_as_markdown($string); }

?>