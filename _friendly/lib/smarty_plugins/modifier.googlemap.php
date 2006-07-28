<?php

function smarty_modifier_googlemap($string)
{ 
	$url  = "http://maps.google.com/maps?q=";
	$url .= urlencode(implode(", ", explode("\n", $string)));
	$url .= "&iwloc=A&hl=en";
	return $url;
}

?>