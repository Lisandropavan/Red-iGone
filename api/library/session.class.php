<?php

class Session {

  function __construct() {
		session_set_save_handler( 
			array( &$this, "open" ), 
			array( &$this, "close" ),
			array( &$this, "read" ),
			array( &$this, "write"),
			array( &$this, "destroy"),
			array( &$this, "gc" )
		);
  }

	public function open($save_path, $session_name) {
		global $sess_save_path;
		$sess_save_path = $save_path;
		return true;
	}

	public function close() {
		return true;
	}

	public function read($id) {
		$data = '';
		$session_data = unserialize(apc_fetch($id));
		if(!empty($session_data)) {
			$data = $session_data;
		}
		return $data;
	}

	public function write($id, $data) {           
		$ttl = SESSION_TTL;
		apc_store($id, serialize($data), $ttl);
		return true;
   }

	public function destroy($id) {
		apc_delete($id);
		return true;
	}

	public function gc() {
		return true;
	}
}