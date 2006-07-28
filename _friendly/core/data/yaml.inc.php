<?php

require $friendly_path."vendor/spyc.class.php";

function yaml_serialize($arr) {
	$par = new Spyc;
	return $par->dump($arr);
}

function yaml_unserialize($yaml_file) {
	$par = new Spyc;
	return $par->load($yaml_file);
}


?>