<?php

function file_upload($fA,$ext = false,$destination = false,$filename = false,$force = false) {
	
	# Requires that the `upload_dir` key be set in config/config.yml,
	# fails gracefully (to enable uploads, just add that parameter)
	if(!$GLOBALS['cfg']['upload_dir'])
		return false;
	
	# Requires that uploads directory is writeable
	elseif(!file_exists($GLOBALS['cfg']['upload_dir']))
		raise("The uploads directory could not be found. Check your permissions and try again.");
	
	# Requires that uploads directory is writeable
	elseif(!is_writeable($GLOBALS['cfg']['upload_dir']) && !chmod($GLOBALS['cfg']['upload_dir'],0766))
		raise("The uploads directory is not writeable. Check your permissions and try again.");
	
	// Requires that input is a files array and has been uploaded
	elseif(!is_array($fA) || !$fA['tmp_name'])
		return false;
	
	// Requires that input is an uploaded file, or that we don't care
	elseif(!is_uploaded_file($fA['tmp_name']) || $force)
		return false;
	
	// Infer the file extension from the original filename
	if(!$ext) {
		preg_match("/\.([A-Za-z0-9]+)$/i",$fA['name'],$_mat);
		$ext = $_mat[1];
	}
	
	// Alias the filename for brevity's sake
	$oP =& $fA['tmp_name'];
	
	// Get or make the filename
	$nP_file = !$filename ? sprintf("file_%s.%s",md5(time().$_SERVER['REMOTE_ADDR']),$ext) : $filename;
	if($destination) $nP_file = $destination."/".$nP_file;
	
	// Establish where this file is going
	$nP = $GLOBALS['cfg']['upload_dir'] ."/". $nP_file;
	
	if(file_exists($nP)) {
		for($x = 1; $x <= 5; $x++) {
		
			$nP_file = preg_replace("/^(.*)(\..{3,4})$/","\\1-{$x}\\2",$nP_file);
			$nP = $GLOBALS['cfg']['upload_dir'] ."/". $nP_file;
		
			if(file_exists($nP))
				continue;
			else
				break;
		}
	
		if(file_exists($nP)) raise("Error: Another file with the same name already exists.");
	}
	
	
	if(copy($oP,$nP))
		return $nP_file;
	else
		raise("Error: File $oP could not be copied to $nP in db_file_upload().");
}

?>