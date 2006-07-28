<?php

require $friendly_path."core/data/yaml.inc.php";
require $friendly_path."core/data/load_config.inc.php";
require $friendly_path."core/data/file.inc.php";
require $friendly_path."core/data/sessions.inc.php";

function friendly_exit($output = false) {
	session_write_close();
	exit($output);
}

?>