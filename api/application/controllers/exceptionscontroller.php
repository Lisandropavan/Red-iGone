<?php

class ExceptionsController extends Controller {

	function status_404() {
		Logger::log_msg('Error 404 File not found');
		header("HTTP/1.0 404 Not Found");
		$this->set('title','Error 404. File not found.');
	}

	function status_404_file() {
		Logger::log_msg('Error 404 Image file not found');
		header("HTTP/1.0 404 Not Found");
		$this->set('title','Error 404. File not found.');
	}

	function status_500() {
		Logger::log_msg('Error 500 Internal Server Error');		
		header('HTTP/1.1 500 Internal Server Error');
		$this->set('title','Error 500. Internal Server Error.');		
	}

}