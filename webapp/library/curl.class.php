<?php

function curl($url, $post_fields=null) {
	$curl = new Curl($url, $post_fields);
	return $curl;
}

class Curl {
	var $data;
	var $headers;

	function __construct($url, $post_fields) {
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_TIMEOUT, 240);

		if (!empty($post_fields)) {
			$headers[] = 'Connection: Keep-Alive';
			$headers[] = 'Content-type: multipart/form-data;charset=UTF-8';
			curl_setopt($c, CURLOPT_HTTPHEADER, $headers); 
			
			curl_setopt($c, CURLOPT_POST, true);
			curl_setopt($c, CURLOPT_POSTFIELDS, $post_fields);
		}

		$this->data = curl_exec($c);
		$this->headers = curl_getinfo($c);
		curl_close($c);
	}

	function get_json() {
		return json_decode($this->data, true);
	}

	function get_xml() {
		return $this->data;
	}
	
	function get_data() {
		$images = array('image/png', 'image/jpeg', 'image/bmp', 'image/gif', 'image/tiff');
		
		if (in_array($this->headers['content_type'], $images)) {
			header('HTTP/1.0 200 OK');
			header('Content-Type: '.$this->headers['content_type']);
		}
		return $this->data;
	}
	
	function get_headers() {
		return $this->headers;
	}
}