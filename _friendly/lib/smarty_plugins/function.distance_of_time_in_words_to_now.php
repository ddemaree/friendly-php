<?php

function smarty_function_distance_of_time_in_words_to_now($params,&$smarty) {
	return distance_of_time_in_words_to_now($params['from']);
}

?>