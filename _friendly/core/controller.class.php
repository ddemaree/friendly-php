<?php

class FriendlyController {
	
	# Were this PHP 5, I would just do this as a __construct.
	# As Blue Box is stuck in the PHP 4 dark ages, however...
	function _init() {
		global $cfg;
		
		$this->_controller = strtolower(get_class($this));
		
		if(!$this->id) $this->_infer_id();
		
		if(!isset($this->_layout) or $this->_layout == '')
			$this->_layout = "default";

		if(!$this->_table_name && $cfg['table_prefix'])
			$this->_table_name = $cfg['table_prefix'] . strtolower(get_class($this));
		
		$this->_defaults = array(
		
			'_overview_finder' => '_index',
			'_detail_finder' => '_view',
			'_save_action' => '_save',
			'_delete_action' => '_delete',
		
			'_overview_finder_query' => "SELECT * FROM {$this->_table_name}",
			'_detail_finder_query'   => "SELECT * FROM {$this->_table_name} WHERE id = '{$this->id}' LIMIT 1",
			
			'_detail_where_clause'	=> "id = '{$this->id}' LIMIT 1",
			
			'_overview_sort_order'	=> "id ASC"
			
		);
		
		foreach($this->_defaults as $_key => $_val) {
			if(!$this->$_key)
				$this->$_key = $_val;
		}
		unset($this->_defaults);
	}
	

	# Action prototypes

	function index()  { $this->_find_overview(); }
	function view()   {	$this->_find_detail(); }
	function save()   {	$this->_save_action(); }
	function delete() { $this->_delete_action(); }
	function create() {}
	
	
	
	# Finder prototypes
	
	function _find_overview() {
		$fN = $this->_overview_finder;
		$this->$fN();
	}
	
	function _find_detail() {
		$fN = $this->_detail_finder;
		$this->$fN();
	}
	
	function _save_action() {
		$fN = $this->_save_action;
		$this->$fN();
	}

	function _delete_action() {
		$fN = $this->_delete_action;
		$this->$fN();
	}
	
	
	# Default finders

	function _index() {
		$this->items = db_fetch($this->_overview_finder_query." ORDER BY ".$this->_overview_sort_order,'assoc');
	}

	function _view() {
		if(is_numeric($this->id)) {
			$this->item = db_fetch_one($this->_detail_finder_query,'assoc');
		}
		else
			redirect_to("$this->_controller/");
	}
	
	
	function _save() {
		
		if($this->id && $_POST[$this->_controller]) {
			if($this->id == 'new') {
				$success = db_insert($_POST[$this->_controller],$this->_table_name);
				$this->id = mysql_insert_id();
			}
			elseif(is_numeric($this->id)) {
				$success = db_update($_POST[$this->_controller],$this->_table_name,$this->_detail_where_clause);
			}
			else {
				$success = false;
			}
			
			flash($success ? "Your changes were saved." : "Your changes could not be saved.");
			redirect_to("$this->_controller/" . ($success ? "view/{$this->id}" : ""));
		}
		else
			redirect_to("$this->_controller/");	
	}
	
	function _delete() {
		if(is_numeric($this->id)) {
			$success = db_delete($this->_table_name,$this->_detail_where_clause);
			
			flash($success ? "The item was deleted." : "The item could not be deleted.");
			redirect_to("$this->_controller/");
		}
		else
			redirect_to("$this->_controller/");
		
	}

	
	function _preprocess_files() {
		
		$this->_controller = strtolower(get_class($this));
		
		if(count($_FILES) > 0) {
			
			foreach($_FILES as $_k => $_fA) {
				if($_fA['error'] == 0) {
					switch($_fA['type']) {
						case "audio/mpeg":
							$ext = "mp3";
							break;
						case "image/jpeg":
							$ext = "jpg";
							break;
						case "video/quicktime":
						default:
							$ext = "mov";
							break;
					}
					
					
					$_POST[$this->_controller][$_k] = file_upload($_fA,$ext);
					$_POST[$this->_controller][$_k."_filename"] = $_fA['name'];
	} } }
	
	}
	
	function _infer_id() {
		if((is_numeric($_POST['id']) or $_POST['id'] == 'new') && !$_GET['x'])
			$this->id = $_POST['id'];
		elseif((is_numeric($_POST[$this->_controller.'_id']) or $_POST[$this->_controller.'_id'] == 'new') && !$_GET['x'])
			$this->id = $_POST[$this->_controller.'_id'];
		elseif(is_numeric($_GET['x']))
			$this->id = $_GET['x'];
	}

}


?>