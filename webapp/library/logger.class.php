<?php
class Logger {

	static function log_msg($message ,$mode=0) {
		$message .= ', url: '.$_SERVER['SCRIPT_URI'];
		error_log($message, $mode);
	}

}