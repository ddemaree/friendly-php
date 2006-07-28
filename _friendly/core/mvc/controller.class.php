<?php

// Bare-bones structure for a Friendly controller, minus
// Magic functionality like default actions

class FriendlyController {
	
	function _init() {
		$cfg =& $GLOBALS['cfg'];
		
		if(!isset($this->_layout) or $this->_layout == '')
			$this->_layout = "default";
			
		$this->_controller = preg_replace("/(.*)_controller$/","$1",underscore(get_class($this)));
	}
	
	
	// Prototype for setup method - platform-agnostic approach to constructors
	function _setup() {
		
		
		
	}
	
	
	// Don't like this name...to run after _setup but before the action method on each req
	function _bootstrap() {
		
		if(is_array($this->_before_filter)) {
			foreach($this->_before_filter as $_filter) {
				if(method_exists($this,$_filter)) {
					$this->$_filter();
				}
			}
		}
		elseif(is_string($this->_before_filter) && method_exists($this,$this->_before_filter)) {
			$_filter = $this->_before_filter;
			$this->$_filter();
		}
		
	}
	
	
	// To be run following the action method on each request
	function _teardown() {
		// After filters run here
		if(is_array($this->_after_filter)) {
			foreach($this->_after_filter as $_filter) {
				if(method_exists($this,$_filter)) {
					$this->$_filter();
				}
			}
		}
		elseif(is_string($this->_after_filter) && method_exists($this,$this->_after_filter)) {
			$_filter = $this->_after_filter;
			$this->$_filter();
		}
	}
	
}


// Magic controller including default actions
// This is to be used in place of ApplicationController

class MagicController extends FriendlyController {
	
	function _init() {
		$cfg =& $GLOBALS['cfg'];
		
		if(!$this->id) $this->_infer_id();
		
		if(!isset($this->_layout) or $this->_layout == '')
			$this->_layout = "default";

		if(!$this->_table_name)
			$this->_infer_table();
			
		$this->_controller = preg_replace("/(.*)_controller$/","$1",underscore(get_class($this)));
		
		$this->_defaults = array(
		
			'_overview_finder' => '_index',
			'_detail_finder' => '_view',
			'_save_action' => '_save',
			'_delete_action' => '_delete',
		
			'_overview_finder_query' => "SELECT * FROM {$this->_table_name}",
			'_detail_finder_query'   => "SELECT * FROM {$this->_table_name} WHERE id = '{$this->id}' LIMIT 1",
			
			'_detail_where_clause'	=> "id = '{$this->id}' LIMIT 1",
			
			'_overview_sort_order'	=> "id ASC",
			
			'_allow_save' => false,
			'_allow_delete' => false,
			'_allow_export' => false
			
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
	
	function save()   {
		if($this->_allow_save) $this->_save_action();
		else raise("Saving items has not been enabled for this Magic Controller.");
	}
	
	function delete() {
		if($this->_allow_delete) $this->_delete_action();
		else raise("Deleting items has not been enabled for this Magic Controller.");
	}
	
	function export_xml() {
		if($this->_allow_export) $this->_export_xml();
		else raise("Exporting items has not been enabled for this Magic Controller.");
	}
	
	function export_csv() {
		if($this->_allow_export) $this->_export_csv();
		else raise("Exporting items has not been enabled for this Magic Controller.");
	}
	
	
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
	
	function _export_xml() {
		$this->fields = db_fetch("SHOW COLUMNS IN {$this->_table_name}","assoc");
		$this->values = db_fetch("SELECT * FROM {$this->_table_name} ORDER BY created_on DESC","assoc");
		
		foreach($this->values as $_v) {
			
			$output .= "<contact>\n";
			
			foreach($this->fields as $_f) {
				$tag = $_f['Field'];
				$val = rtrim($_v[$tag]);
				
				$output .= "\t<$tag>$val</$tag>\n";
			}
			$output .= "</contact>\n";
		}
		
		$filename = $this->_table_name . date("_Ymd") . ".xml";
		
		header ("Content-type: text/xml\nContent-Disposition: attachment; filename=$filename");
		
		friendly_exit($output);
		
	}
	
	function _export_csv() {
		$this->fields = db_fetch("SHOW COLUMNS IN {$this->_table_name}","assoc");
		$this->values = db_fetch("SELECT * FROM {$this->_table_name} ORDER BY created_on DESC","assoc");
		
		foreach($this->values as $_v) {
			
			$output .= "<contact>\n";
			
			foreach($this->fields as $_f) {
				$this_row[] = rtrim($_v[$_f['Field']]);
			}
			$output .= implode(",",$this_row) . "\n";
			unset($this_row);
		}
		
		$filename = $this->_table_name . date("_Ymd") . ".csv";
		
		header ("Content-type: application/csv\nContent-Disposition: attachment; filename=$filename");
		
		friendly_exit($output);
		
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
	
	function _infer_table() {
		global $cfg;
		
		if(PHP_VERSION >= 5.0) {
			$this->_table_name = $cfg['table_prefix'].class_to_table(preg_replace("/^(.*)Controller$/i","$1",get_class($this)));
		}
		else {
			$this->_table_name = $cfg['table_prefix'].pluralize($_GET['c']);
		}
	}

}


?>