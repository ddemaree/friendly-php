<?php

function smarty_function_distance_of_time_in_words($params, &$smarty)
{
	if(!$params["to"]) $params["to"] = date("Y-m-d H:m:s");
	return distance_of_time_in_words($params["from"], $params["to"]);
}	

?>