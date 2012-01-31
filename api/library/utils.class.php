<?php
class Utils {
	protected $starttime;

	function timerStart() {
		$this->starttime = time()+microtime();	
	}

	function timerStop() {
	  $endtime = time()+microtime();
	  $totaltime = round($endtime - $this->starttime,6);
	  echo $totaltime . 's';
	}

	static function is_Ajax() {
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		}	else {
			return false;
		}
	}
	
	static function env($var) {
		return $_SERVER[$var];
	}
	
	static function output_json($data) {
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');		
		//header('Content-type text/plain');
		echo json_encode($data);
	}

}