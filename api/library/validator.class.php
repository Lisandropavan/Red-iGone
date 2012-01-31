<?php

/*
 * Usage: validator()->check('email','somebody@redigone.com')
 */

function validator() {
  static $validator = null;
  if(is_null($validator))
    $validator = new Validator();
  return $validator;
}

class Validator {
	
	public function check($fname, $subject) {
		$regex = call_user_func(array(__CLASS__, $fname));
		if(preg_match($regex, $subject)) {
			return true;
		} else {
			return false;
		}
	}	

	public function max_length($str, $max_length) {
		return strlen($str) <= $max_length ? true : false;
	}

	public function min_length($str, $min_length) {
		return strlen($str) >= $min_length ? true : false;
	}

	public function ip($ip) {
		return filter_var($ip, FILTER_VALIDATE_IP) == false ? false : true;
	}

	//Functions below are used as argument to check()
	public function email() {
		return '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@'.$this->hostname().'$/i';
	}

	private function hostname() {
		return '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)';
	}

	public function alpha_num() {
		return '/^[a-zA-Z0-9]+$/';
	}

	public function numeric() {
		return '/^[0-9]+$/';
	}

	public function device_info() {
		return '/^[-a-zA-Z0-9 \.]+$/';
	}
	
	public function image_info() {
		return '/^[-a-zA-Z0-9 ,\.\(\)]+$/';
	}
	
	public function image_name() {
		return '/^[a-zA-Z0-9\._]+$/';
	}
	
	public function selection_type() {
		return '/^rect|circ$/';
	}
}
