<?php

class Api {


	//Returns key status, db column active = true or false
	//Exception if not found
	public function validate_key($key) {

		if (empty($key)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), required parameter key is empty');
		}

		if (!validator()->max_length($key, db()->max_length('api_keys', 'key'))) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), key: '.$key.'  > max_length');
		}

		$query = 'SELECT active FROM api_keys WHERE key = :key';
		$params[] = array('name'=>':key', 'value'=>$key);

		try {
			$res = db()->exec_query($query, $params);
		} catch (Exception $e) {
			throw $e;
		}

		if (isset($res['active'])) {
			return $res;
		}	else {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), unable to validate key: '.$key);
		}

	}

	//Returns true on success or
	//Exception on error
	public function insert_key($data) {

		$keys = array('key', 'username', 'active');

		foreach($keys as $k) {
			$$k = isset($data[$k]) ? trim($data[$k]) : '';

			if (empty($$k)) {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), required parameter '.$k.' is empty');
			}
		}

		if (!validator()->max_length($key, db()->max_length('api_keys', 'key'))) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), key: '.$key.'  > max_length');
		}

		if (!validator()->max_length($username, db()->max_length('api_keys', 'username'))) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), username: '.$username.'  > max_length');
		}

		$query = 'INSERT INTO api_keys(key, username, active) VALUES (:key, :username, :active) RETURNING key';

		$params[] = array('name'=>':key', 'value'=>$key);
		$params[] = array('name'=>':username', 'value'=>$username);
		$params[] = array('name'=>':active', 'value'=>$active);

		try {
			$res = db()->exec_query($query, $params);
		} catch (Exception $e) {
			throw $e;
		}

		if(isset($res['key'])) {
			return true;
		}	else {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(),  unable to insert to the database');
		}

	}


	//Returns session_id on success, empty otherwise
	//Exception on error
	public function validate_session_id($session_id) {

		if (empty($session_id)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), required parameter session_id is empty');
		}

		if (!validator()->max_length($session_id, db()->max_length('api_sessions', 'session_id'))) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), session_id: '.$session_id.'  > max_length');
		}

		$query = "SELECT session_id FROM api_sessions WHERE session_id = :session_id AND created_at > (now() - INTERVAL '15 minutes')";
		$params[] = array('name'=>':session_id', 'value'=>$session_id);

		try {
			$res = db()->exec_query($query, $params);
		} catch (Exception $e) {
			throw $e;
		}

		if (isset($res['session_id'])) {
			return $res;
		}	else {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), unable to validate session_id: '.$session_id);
		}

	}


	//Returns true on success or
	//Exception on error
	public function insert_session_id($session_id) {

		if (empty($session_id)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), required parameter session_id is empty');
		}

		if (!validator()->max_length($session_id, db()->max_length('api_sessions', 'session_id'))) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), session_id: '.$session_id.'  > max_length');
		}

		$query = 'INSERT INTO api_sessions(session_id) VALUES (:session_id) RETURNING session_id';

		$params[] = array('name'=>':session_id', 'value'=>$session_id);

		try {
			$res = db()->exec_query($query, $params);
		} catch (Exception $e) {
			throw $e;
		}

		if(isset($res['session_id'])) {
			return true;
		}	else {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(),  unable to insert to the database');
		}

	}


	//Returns id of newly inserted image on success or
	//Exception on error
	public function enqueue($data) {

		if (!is_array($data)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), data is not an array');	
		}

		$device_id = isset($data['device_id']) ? $data['device_id'] : '';

		if (!validator()->check('device_info', $device_id)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), device_id: '.$device_id.' does not match '.validator()->device_info());
		}

		if (!validator()->max_length($device_id, db()->max_length('api_usage', 'device_id'))) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), device_id: '.$device_id.'  > max_length');
		}

		$ip = isset($data['ip']) ? $data['ip'] : '';

		if (!validator()->ip($ip)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), ip: '.$ip.' is not a valid IP address');
		}
		
		$th = isset($data['th']) ? intval($data['th']) : '';

		if ($th > MAX_THRESHOLD) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), th: '.$th.'  > MAX_THRESHOLD: '.MAX_THRESHOLD);
		}

		if ($th < MIN_THRESHOLD) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), th: '.$th.'  < MIN_THRESHOLD: '.MIN_THRESHOLD);
		}
		
		$selections = isset($data['selections']) ? $data['selections'] : '';
		if(!empty($selections)) {
			if (!validator()->check('image_info', $selections)) {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), selections: '.$selections.' does not match '.validator()->image_info());
			}
		}
		
		$keys = array('image', 'resize');

		foreach($keys as $k) {
			$$k = isset($data[$k]) ? trim($data[$k]) : '';
						
			if (!validator()->check('image_info', $$k)) {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), '.$k.': '.$$k.' does not match '.validator()->image_info());
			}

			if (!validator()->max_length($$k, db()->max_length('image_queue', $k))) {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), '.$k.': '.$$k.' > max_length');
			}
		}

		$keys = array('width', 'height', 'bytes');

		foreach($keys as $k) {	
			$$k = isset($data[$k]) ? trim($data[$k]) : '';
			
			if(!empty($$k)) {
				if (!validator()->max_length($$k, db()->max_length('image_queue', $k))) {
					throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), '.$k.': '.$$k.' > max_length');
				}
			}
		}

		
		$query = 'INSERT INTO image_queue(image, th, selections, resize, device_id, ip, width, height, bytes) VALUES (:image, :th, :selections, :resize, :device_id, :ip, :width, :height, :bytes) RETURNING id';

		$params[] = array('name'=>':image', 'value'=>$image);
		$params[] = array('name'=>':th', 'value'=>$th);
		$params[] = array('name'=>':selections', 'value'=>$selections);
		$params[] = array('name'=>':resize', 'value'=>$resize);
		$params[] = array('name'=>':device_id', 'value'=>$device_id);
		$params[] = array('name'=>':ip', 'value'=>$ip);
		$params[] = array('name'=>':width', 'value'=>$width);
		$params[] = array('name'=>':height', 'value'=>$height);
		$params[] = array('name'=>':bytes', 'value'=>$bytes);		
		
		try {
			$res = db()->exec_query($query, $params);
		} catch (Exception $e) {
			throw $e;
		}

		if (isset($res['id'])) {
			return $res['id'];
		}	else {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(),  unable to insert to the database');
		}		
		
	}

	//Returns status, image and th or
	//Exception if not found
	public function image_status($id) {

		if (!is_int($id)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), id: '.$id.' is not an integer');
		}		

		if ($id < 1) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), id: '.$id.' < 1');
		}

		$query = 'SELECT status, image, th FROM image_queue WHERE id = :id';
		$params[] = array('name'=>':id', 'value'=>$id);
		
		try {
			$res = db()->exec_query($query, $params);
		} catch (Exception $e) {
			throw $e;
		}

		if (isset($res['status'])) {
			return $res;
		}	else {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), unable to get image status for id: '.$id);
		}
		
	}


	//Returns true on success or
	//Exception on error
	public function log_usage($data) {

		if (!is_array($data)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), data is not an array');	
		}

		$device_id = isset($data['device_id']) ? $data['device_id'] : '';

		if (!validator()->check('device_info', $device_id)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), device_id: '.$device_id.' does not match '.validator()->device_info());
		}

		if (!validator()->max_length($device_id, db()->max_length('api_usage', 'device_id'))) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), device_id: '.$device_id.'  > max_length');
		}

		$last_ip = isset($data['last_ip']) ? $data['last_ip'] : '';

		if (!validator()->ip($last_ip)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), last_ip: '.$last_ip.' is not a valid IP address');
		}

		$keys = array('device_name', 'device_systemName', 'device_systemVersion', 'device_model', 'device_localizedModel', 'app_name', 'app_version');

		foreach($keys as $k) {	
			$$k = isset($data[$k]) ? trim($data[$k]) : '';
			
			if(!empty($$k)) {
				/*if (!validator()->check('device_info', $$k)) {
					throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), '.$k.': '.$$k.' does not match '.validator()->device_info());
				}*/

				if (!validator()->max_length($$k, db()->max_length('api_usage', $k))) {
					throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), '.$k.': '.$$k.' > max_length');
				}
			}
		}

		$query = 'SELECT device_id, counter FROM api_usage WHERE device_id = :device_id';
		$params[] = array('name'=>':device_id', 'value'=>$device_id);
		
		try {
			$res = db()->exec_query($query, $params);
		} catch (Exception $e) {
			throw $e;
		}

		if (isset($res['device_id']) && isset($res['counter'])) {
			
			$counter = intval($res['counter']) + 1;

			$query = "UPDATE api_usage SET device_name = :device_name, device_systemName = :device_systemName, device_systemVersion = :device_systemVersion, device_model = :device_model, device_localizedModel = :device_localizedModel, app_name = :app_name, app_version = :app_version, counter = :counter, last_ip = :last_ip, last_accessed = 'now()' WHERE device_id = :device_id RETURNING device_id";

			$params[] = array('name'=>':device_name', 'value'=>$device_name);
			$params[] = array('name'=>':device_systemName', 'value'=>$device_systemName);
			$params[] = array('name'=>':device_systemVersion', 'value'=>$device_systemVersion);
			$params[] = array('name'=>':device_model', 'value'=>$device_model);
			$params[] = array('name'=>':device_localizedModel', 'value'=>$device_localizedModel);
			$params[] = array('name'=>':app_name', 'value'=>$app_name);
			$params[] = array('name'=>':app_version', 'value'=>$app_version);
			$params[] = array('name'=>':counter', 'value'=>$counter);
			$params[] = array('name'=>':last_ip', 'value'=>$last_ip);
			$params[] = array('name'=>':device_id', 'value'=>$device_id);

			try {
				$res = db()->exec_query($query, $params);
			} catch (Exception $e) {
				throw $e;
			}

			if(isset($res['device_id'])) {
				return true;
			} else {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(),  unable to update the database');
			}
			
		}	 else {

				$counter = 1;

				$query = 'INSERT INTO api_usage(device_id, device_name, device_systemName, device_systemVersion, device_model, device_localizedModel, app_name, app_version, counter, last_ip) VALUES (:device_id, :device_name, :device_systemName, :device_systemVersion, :device_model, :device_localizedModel, :app_name, :app_version, :counter, :last_ip) RETURNING device_id';

				$params[] = array('name'=>':device_id', 'value'=>$device_id);
				$params[] = array('name'=>':device_name', 'value'=>$device_name);
				$params[] = array('name'=>':device_systemName', 'value'=>$device_systemName);
				$params[] = array('name'=>':device_systemVersion', 'value'=>$device_systemVersion);
				$params[] = array('name'=>':device_model', 'value'=>$device_model);
				$params[] = array('name'=>':device_localizedModel', 'value'=>$device_localizedModel);
				$params[] = array('name'=>':app_name', 'value'=>$app_name);
				$params[] = array('name'=>':app_version', 'value'=>$app_version);				
				$params[] = array('name'=>':counter', 'value'=>$counter);
				$params[] = array('name'=>':last_ip', 'value'=>$last_ip);
				
				try {
					$res = db()->exec_query($query, $params);
				} catch (Exception $e) {
					throw $e;
				}

				if(isset($res['device_id'])) {
					return true;
				}	else {
					throw new Exception(__CLASS__.'::'.__FUNCTION__.'(),  unable to insert to the database');
				}
			}

			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), general error');
	}


	//Returns true on success or
	//Exception on error
	public function log_uploads($data) {
		
		$device_id = isset($data['device_id']) ? $data['device_id'] : '';

		if (!validator()->check('device_info', $device_id)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), device_id: '.$device_id.' does not match '.validator()->alpha_num());
		}

		if (!validator()->max_length($device_id, db()->max_length('api_uploads', 'device_id'))) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), device_id: '.$device_id.' > max_length');
		}

		$last_ip = isset($data['last_ip']) ? $data['last_ip'] : '';

		if (!validator()->ip($last_ip)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), last_ip: '.$last_ip.' is not a valid IP address');
		}

		$query = 'SELECT device_id, counter FROM api_uploads WHERE device_id = :device_id';
		$params[] = array('name'=>':device_id', 'value'=>$device_id);
		
		try {
			$res = db()->exec_query($query, $params);
		} catch (Exception $e) {
			throw $e;
		}

		if (isset($res['device_id']) && isset($res['counter'])) {
			
			$counter = intval($res['counter']) + 1;
			
			$query = "UPDATE api_uploads SET counter = :counter, last_ip = :last_ip, last_accessed = 'now()' WHERE device_id = :device_id RETURNING device_id";
		
			$params[] = array('name'=>':counter', 'value'=>$counter);
			$params[] = array('name'=>':last_ip', 'value'=>$last_ip);
			$params[] = array('name'=>':device_id', 'value'=>$res['device_id']);

			try {
				$res = db()->exec_query($query, $params);
			} catch (Exception $e) {
				throw $e;
			}

			if(isset($res['device_id'])) {
				return true;
			} else {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(),  unable to update the database');
			}
			
		} else {
			
			$counter = 1;

			$query = 'INSERT INTO api_uploads(device_id, counter, last_ip) VALUES (:device_id, :counter, :last_ip) RETURNING device_id';

			$params[] = array('name'=>':device_id', 'value'=>$device_id);
			$params[] = array('name'=>':counter', 'value'=>$counter);
			$params[] = array('name'=>':last_ip', 'value'=>$last_ip);
			
			try {
				$res = db()->exec_query($query, $params);
			} catch (Exception $e) {
				throw $e;
			}

			if (isset($res['device_id'])) {
				return true;
			}	else {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(),  unable to insert to the database');
			}
		}

		throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), general error');
	}

	
	//Returns true on success or
	//Exception on error
	public function log_thumbs($data) {

		$device_id = isset($data['device_id']) ? $data['device_id'] : '';

		if (!validator()->check('device_info', $device_id)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), device_id: '.$device_id.' does not match '.validator()->alpha_num());
		}
		
		if (!validator()->max_length($device_id, db()->max_length('api_thumbs', 'device_id'))) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), device_id: '.$device_id.' > max_length');
		}

		$last_ip = isset($data['last_ip']) ? $data['last_ip'] : '';

		if (!validator()->ip($last_ip)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), last_ip: '.$last_ip.' is not a valid IP address');
		}

		$query = 'SELECT device_id, counter FROM api_thumbs WHERE device_id = :device_id';
		$params[] = array('name'=>':device_id', 'value'=>$device_id);
		
		try {
			$res = db()->exec_query($query, $params);
		} catch (Exception $e) {
			throw $e;
		}
		
		if (isset($res['device_id']) && isset($res['counter'])) {
			
			$counter = intval($res['counter']) + 1;
			
			$query = "UPDATE api_thumbs SET counter = :counter, last_ip = :last_ip, last_accessed = 'now()' WHERE device_id = :device_id RETURNING device_id";
		
			$params[] = array('name'=>':counter', 'value'=>$counter);
			$params[] = array('name'=>':last_ip', 'value'=>$last_ip);
			$params[] = array('name'=>':device_id', 'value'=>$res['device_id']);

			try {
				$res = db()->exec_query($query, $params);
			} catch (Exception $e) {
				throw $e;
			}

			if (isset($res['device_id'])) {
				return true;
			} else {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(),  unable to update the database');
			}
			
		} else {
			
			$counter = 1;

			$query = 'INSERT INTO api_thumbs(device_id, counter, last_ip) VALUES (:device_id, :counter, :last_ip) RETURNING device_id';

			$params[] = array('name'=>':device_id', 'value'=>$device_id);
			$params[] = array('name'=>':counter', 'value'=>$counter);
			$params[] = array('name'=>':last_ip', 'value'=>$last_ip);
			
			try {
				$res = db()->exec_query($query, $params);
			} catch (Exception $e) {
				throw $e;
			}

			if (isset($res['device_id'])) {
				return true;
			}	else {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(),  unable to insert to the database');
			}
		}
		
		throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), general error');
	}
	
}
