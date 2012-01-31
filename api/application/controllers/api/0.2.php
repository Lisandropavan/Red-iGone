<?php
class ApiController extends Controller {
 
	var $version;
	var $label;

	//DO NOT override constructor and DO NOT call parent constructor
	/*function __construct() {
		parent::__construct();
	}*/


	public function image_upload() {
		$this->label = 'RiG';
		$this->version = '0.2';
		
		$this->doNotRenderHeader = 1;
		$this->render = 0;

		$device_id = isset($_REQUEST['device_id']) ? $_REQUEST['device_id'] : '';


/*

Array
(
    [format] => json
    [device_id] => redigone.com
    [device_name] => webapp
    [device_systemName] => webapp
    [device_systemVersion] => 1.0
    [device_model] => webapp
    [device_localizedModel] => webapp
    [app_name] => webapp
    [app_version] => 1.0
    [resize] => 256x256
)
Array
(
    [filename] => Array
        (
            [name] => redeye_sample.jpg
            [type] => application/octet-stream
            [tmp_name] => /tmp/php6Zfrgn
            [error] => 0
            [size] => 107787
        )

)
*/
	//print_r($_REQUEST);
	//print_r($_FILES);
	//exit;
		
		$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'xml';
		
		$keys = array('resize', 'num_selections', 'device_id', 'device_name', 'device_systemName', 'device_systemVersion', 'device_model', 'device_localizedModel', 'app_name', 'app_version');
		
		foreach($keys as $k) {
			$$k = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
		}

		try {
			if (!validator()->check('device_info', $device_id)) {
				throw new Exception('Parameter device_id: '.$device_id.' does not match '.validator()->device_info());
			}

			if (!validator()->max_length($device_id, db()->max_length('image_queue', 'device_id'))) {
				throw new Exception('Parameter device_id: '.$device_id.'  > max_length: '.db()->max_length('image_queue', 'device_id'));
			}

			if (empty($_FILES['filename'])) {
				throw new Exception('Missing parameter filename');
			}

			if ($_FILES['filename']['size'] > MAX_UPLOAD_SIZE_IOS) {
				throw new Exception('Uploaded file exceeds maximum size: '.MAX_UPLOAD_SIZE_IOS.' bytes');
			}
		
			if ($_FILES['filename']['error'] != 0) {
				Logger::log_msg(__CLASS__.'::'.__FUNCTION__.'(), File upload failed. Error code: '.$_FILES['filename']['error'].', Error message: '.$this->file_upload_error_message($_FILES['filename']['error'])
				.', Device info: ID: '.$device_id.', Name: '.$device_name.', SystemName: '.$device_systemName.', SystemVersion: '.$device_systemVersion.', Model: '.$device_model.', LocalizedModel: '.$device_localizedModel);
			
				throw new Exception('File upload failed.');
			}

			if ($_FILES['filename']['type'] == 'application/octet-stream') {
				$_FILES['filename']['type'] = $this->set_content_by_ext($this->getFileExtension($_FILES['filename']['name']));					
			} else if (!$this->allowed_mime_type($_FILES['filename']['type'])) {
				Logger::log_msg(__CLASS__.'::'.__FUNCTION__.'(), uploaded file MIME type: '.$_FILES['filename']['type'].' is not allowed');
				throw new Exception('Uploaded file MIME type: '.$_FILES['filename']['type'].' is not allowed');
			}

			try {
				$original = $this->moveUploaded($_FILES['filename']);
				//$original now contains ['path'] ['name'] ['extension']
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
				throw new Exception('Failed to move uploaded file.');
			}

			$original['resize'] = $resize;
			
			try {
				$original_thumb = $this->adaptiveResize($original);
				//$original_thumb now contains ['path'] ['name']
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
				throw new Exception('Failed to create thumbnail.');
			}

			$original['device_id'] = $device_id;
			$original['bytes'] = $_FILES['filename']['size'];
			
			$d = getimagesize(UPLOAD_DIR.$original['name']);
			if (is_array($d)) {
				$original['width'] = $d[0];
				$original['height'] = $d[1];
			}

			$log_data['device_id'] = $device_id;
			$log_data['last_ip'] = $_SERVER['REMOTE_ADDR'];

			try {
				$this->model->log_uploads($log_data);
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
			}

			$log_data['device_name'] = $device_name;
			$log_data['device_systemName'] = $device_systemName;
			$log_data['device_systemVersion'] = $device_systemVersion;
			$log_data['device_model'] = $device_model;
			$log_data['device_localizedModel'] = $device_localizedModel;
			$log_data['app_name'] = $app_name;
			$log_data['app_version'] = $app_version;

			try {
				$this->model->log_usage($log_data);
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
			}
			
		} catch (Exception $e) {
			$error_msg['error'] = $e->getMessage();
		}

		//print_r($original);
		//print_r($original_thumb);
		//print_r($queue_data);

		if (!empty($error_msg)) {
			if ($format == 'xml') {
				xmlbuilder()->build_xml($error_msg);
				xmlbuilder()->output_error();
			} else if ($format == 'json') {
				Utils::output_json($error_msg);
			}
		} else {
			$data = array();
			$data['original_name'] = $original['name'];
			$data['extension'] = $original['extension'];
			$data['original_thumb_name'] = $original_thumb['name'];

			if ($format == 'xml') {
				xmlbuilder($this->label,$this->version)->build_xml($data);
				xmlbuilder($this->label,$this->version)->output();
			} else if ($format == 'json') {
				Utils::output_json($data);
			}
		}
	}


	public function image_upload_process() {
		$this->label = 'RiG';
		$this->version = '0.2';
		
		$this->doNotRenderHeader = 1;
		$this->render = 0;

		$device_id = isset($_REQUEST['device_id']) ? $_REQUEST['device_id'] : '';
		$threshold = isset($_REQUEST['threshold']) ? $_REQUEST['threshold'] : '';
		
		$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'xml';
		
		$keys = array('resize', 'num_selections', 'device_id', 'device_name', 'device_systemName', 'device_systemVersion', 'device_model', 'device_localizedModel', 'app_name', 'app_version');
		
		foreach($keys as $k) {
			$$k = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
		}

		try {
			if (!validator()->check('device_info', $device_id)) {
				throw new Exception('Parameter device_id: '.$device_id.' does not match '.validator()->device_info());
			}

			if (!validator()->max_length($device_id, db()->max_length('image_queue', 'device_id'))) {
				throw new Exception('Parameter device_id: '.$device_id.'  > max_length: '.db()->max_length('image_queue', 'device_id'));
			}

			if ($num_selections > MAX_SELECTIONS) {
				throw new Exception('Parameter num_selections: '.$num_selections.'  > maximum number of allowed selections: '.MAX_SELECTIONS);
			}

			if ($threshold > MAX_THRESHOLD) {
				throw new Exception('Parameter threshold: '.$threshold.'  > maximum threshold: '.MAX_THRESHOLD);
			}

			if ($threshold < MIN_THRESHOLD) {
				throw new Exception('Parameter threshold: '.$threshold.'  < minimum threshold: '.MIN_THRESHOLD);
			}

			if (empty($_FILES['filename'])) {
				throw new Exception('Missing parameter filename');
			}

			if ($_FILES['filename']['size'] > MAX_UPLOAD_SIZE_IOS) {
				throw new Exception('Uploaded file exceeds maximum size: '.MAX_UPLOAD_SIZE_IOS.' bytes');
			}
		
			if ($_FILES['filename']['error'] != 0) {
				Logger::log_msg(__CLASS__.'::'.__FUNCTION__.'(), File upload failed. Error code: '.$_FILES['filename']['error'].', Error message: '.$this->file_upload_error_message($_FILES['filename']['error'])
				.', Device info: ID: '.$device_id.', Name: '.$device_name.', SystemName: '.$device_systemName.', SystemVersion: '.$device_systemVersion.', Model: '.$device_model.', LocalizedModel: '.$device_localizedModel);
			
				throw new Exception('File upload failed.');
			}

			if (!$this->allowed_mime_type($_FILES['filename']['type'])) {
				Logger::log_msg(__CLASS__.'::'.__FUNCTION__.'(), uploaded file MIME type: '.$_FILES['filename']['type'].' is not allowed');
				throw new Exception('Uploaded file MIME type: '.$_FILES['filename']['type'].' is not allowed');
			}

			try {
				$original = $this->moveUploaded($_FILES['filename']);
				//$original now contains ['path'] ['name'] ['extension']
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
				throw new Exception('Failed to move uploaded file.');
			}

			
			$original['th'] = intval($threshold);
			$original['resize'] = $resize;
			
			try {
				$original_thumb = $this->adaptiveResize($original);
				//$original_thumb now contains ['path'] ['name']
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
				throw new Exception('Failed to create thumbnail.');
			}

			
			$num_selections = intval($num_selections);
			$selections = array();
			if ($num_selections > 0) {
				for ($i = 0; $i < $num_selections; $i++) {
					$s = explode(',', $_REQUEST['selection'.$i]);

					if (!validator()->check('selection_type', $s[0])) {
						throw new Exception('Parameter selection_type: '.$s[0].' does not match '.validator()->selection_type());
					}

					for ($j = 1; $j < count($s); $j++) {
						if (isset($s[$j])) {
							if (!validator()->check('numeric', $s[$j])) {
								throw new Exception('Selection parameter: '.$s[$j].' does not match '.validator()->numeric());
							}
						}
					}
					if (count($s) == 4) {
						//2*r is a gimp bug workaround
						$selections[] = array('type'=>'circ', 'x'=>$s[1], 'y'=>$s[2], 'r'=>(2*$s[3]));
					} else if (count($s) == 5) {
						$selections[] = array('type'=>'rect', 'x'=>$s[1], 'y'=>$s[2], 'w'=>$s[3], 'h'=>$s[4]);
					} else {
						throw new Exception('Selection parameter count: '.count($s).' does not match 4 or 5');
					}
				}
			}

			$original['device_id'] = $device_id;
			$original['bytes'] = $_FILES['filename']['size'];
			
			$d = getimagesize(UPLOAD_DIR.$original['name']);
			if (is_array($d)) {
				$original['width'] = $d[0];
				$original['height'] = $d[1];
			}
			
			try {
				$queue_data = $this->enqueue($original, $selections);
				//$queue_data now contains ['path'] ['name'] ['extension'] ['th'] ['queue_id']
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
				throw new Exception('Failed to add job to queue.');			
			}


			$log_data['device_id'] = $device_id;
			$log_data['last_ip'] = $_SERVER['REMOTE_ADDR'];

			try {
				$this->model->log_uploads($log_data);
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
			}

			$log_data['device_name'] = $device_name;
			$log_data['device_systemName'] = $device_systemName;
			$log_data['device_systemVersion'] = $device_systemVersion;
			$log_data['device_model'] = $device_model;
			$log_data['device_localizedModel'] = $device_localizedModel;
			$log_data['app_name'] = $app_name;
			$log_data['app_version'] = $app_version;

			try {
				$this->model->log_usage($log_data);
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
			}
			
		} catch (Exception $e) {
			$error_msg['error'] = $e->getMessage();
		}

		//print_r($original);
		//print_r($original_thumb);
		//print_r($queue_data);

		if (!empty($error_msg)) {
			Logger::log_msg('API Call Error: '.$error_msg['error']);
			if ($format == 'xml') {
				xmlbuilder()->build_xml($error_msg);
				xmlbuilder()->output_error();
			} else if ($format == 'json') {
				Utils::output_json($error_msg);
			}
		} else {
			$data = array();
			$data['original_name'] = $original['name'];
			//$data['original_path'] = $original['path'];
			$data['original_thumb_name'] = $original_thumb['name'];
			//$data['original_thumb_path'] = $original_thumb['path'];
			$data['queue_id'] = $queue_data['queue_id'];
			$data['queue_name'] = $queue_data['name'];
			//$data['queue_path'] = $queue_data['path'];
			$data['queue_th'] = $queue_data['th'];


			if ($format == 'xml') {
				xmlbuilder($this->label,$this->version)->build_xml($data);
				xmlbuilder($this->label,$this->version)->output();
			} else if ($format == 'json') {
				Utils::output_json($data);
			}
		}
	}

	public function generate_thumb() {
		$this->label = 'RiG';
		$this->version = '0.2';
		
		$this->doNotRenderHeader = 1;
		$this->render = 0;

		$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'xml';
		$device_id = isset($_REQUEST['device_id']) ? $_REQUEST['device_id'] : '';
		$threshold = isset($_REQUEST['threshold']) ? $_REQUEST['threshold'] : '';

		$keys = array('name', 'extension', 'resize', 'num_selections', 'device_id');
		
		foreach($keys as $k) {
			$$k = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
		}

		try {
			if (!validator()->check('device_info', $device_id)) {
				throw new Exception('Parameter device_id: '.$device_id.' does not match '.validator()->device_info());
			}

			if (!validator()->max_length($device_id, db()->max_length('image_queue', 'device_id'))) {
				throw new Exception('Parameter device_id: '.$device_id.'  > max_length: '.db()->max_length('image_queue', 'device_id'));
			}

			if ($num_selections > MAX_SELECTIONS) {
				throw new Exception('Parameter num_selections: '.$num_selections.'  > maximum number of allowed selections: '.MAX_SELECTIONS);
			}

			if ($threshold > MAX_THRESHOLD) {
				throw new Exception('Parameter threshold: '.$threshold.'  > maximum threshold: '.MAX_THRESHOLD);
			}

			if ($threshold < MIN_THRESHOLD) {
				throw new Exception('Parameter threshold: '.$threshold.'  < minimum threshold: '.MIN_THRESHOLD);
			}

			if (!$this->allowed_extension($extension)) {
				throw new Exception('Parameter extension: '.$extension.'  is not allowed');
			}

			//$original['path'] = UPLOAD_DIR;
			$original['name'] = $name;
			$original['th'] = $threshold;
			$original['resize'] = $resize;
			$original['extension'] = $extension;
			$original['device_id'] = $device_id;

			try {
				$original_thumb = $this->adaptiveResize($original);
				//$original_thumb now contains ['path'] ['name']
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
				throw new Exception('Failed to create thumbnail.');
			}

			$num_selections = intval($_REQUEST['num_selections']);
			$selections = array();
			if ($num_selections > 0) {
				for ($i = 0; $i < $num_selections; $i++) {
					$s = explode(',', $_REQUEST['selection'.$i]);

					if (!validator()->check('selection_type', $s[0])) {
						throw new Exception('Parameter selection_type: '.$s[0].' does not match '.validator()->selection_type());
					}

					for ($j = 1; $j < count($s); $j++) {
						if (isset($s[$j])) {
							if (!validator()->check('numeric', $s[$j])) {
								throw new Exception('Selection parameter: '.$s[$j].' does not match '.validator()->numeric());
							}
						}
					}
					if (count($s) == 4) {
						//2*r is a gimp bug workaround
						$selections[] = array('type'=>'circ', 'x'=>$s[1], 'y'=>$s[2], 'r'=>(2*$s[3]));
					} else if (count($s) == 5) {
						$selections[] = array('type'=>'rect', 'x'=>$s[1], 'y'=>$s[2], 'w'=>$s[3], 'h'=>$s[4]);
					} else {
						throw new Exception('Selection parameter count: '.count($s).' does not match 4 or 5');
					}
				}
			}


			try {
				$queue_data = $this->enqueue($original, $selections);
				//$queue_data now contains ['path'] ['name'] ['extension'] ['th'] ['queue_id']
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
				throw new Exception('Failed to add job to queue.');			
			}
			
			$log_data['device_id'] = $device_id;
			$log_data['last_ip'] = $_SERVER['REMOTE_ADDR'];

			try {
				$this->model->log_thumbs($log_data);
			} catch (Exception $e) {
				Logger::log_msg($e->getMessage());
			}
			
		} catch (Exception $e) {
			$error_msg['error'] = $e->getMessage();
		}
		
		if (!empty($error_msg)) {
			if ($format == 'xml') {
				xmlbuilder()->build_xml($error_msg);
				xmlbuilder()->output();
			} else if ($format == 'json') {
				Utils::output_json($error_msg);
			}
		} else {
			$data = array();
			$data['queue_id'] = $queue_data['queue_id'];
			$data['queue_name'] = $queue_data['name'];
			//$data['queue_path'] = $queue_data['path'];
			$data['queue_th'] = $queue_data['th'];

			if ($format == 'xml') {
				xmlbuilder($this->label,$this->version)->build_xml($data);
				xmlbuilder($this->label,$this->version)->output();
			} else if ($format == 'json') {
				Utils::output_json($data);
			}
		}		
	}


	public function image_download() {
		$this->label = 'RiG';
		$this->version = '0.2';
		
		$this->doNotRenderHeader = 1;
		$this->render = 0;
		
		$image = isset($_REQUEST['img']) ? $_REQUEST['img'] : '';
		$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'xml';

		try {
			if (!validator()->check('image_name', $image)) {
				throw new Exception('Parameter image: '.$image.' does not match '.validator()->image_name());
			}

			$content_type = $this->set_content_by_ext($this->getFileExtension(basename($image)));

			if($content_type != false && file_exists(UPLOAD_DIR.$image)) {
				header('HTTP/1.0 200 OK');
				header("Content-Type: $content_type");
				readfile(UPLOAD_DIR.$image);
				exit(0);		
			} else {
				throw new Exception('Unsupported content type');
			}
			
		} catch (Exception $e) {
			throw new Exception("404: Tried to download $image", 6001);
		}
		
	}

	public function get_status() {
		$this->label = 'RiG';
		$this->version = '0.2';
		
		$this->doNotRenderHeader = 1;
		$this->render = 0;

		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
		$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'xml';

		try {

			if (!validator()->check('numeric', $id)) {
				throw new Exception('Parameter id: '.$id.' does not match '.validator()->numeric());
			}

			try {
				$res = $this->model->image_status(intval($id));
		
				if($res['status'] != 'done') {
					$data['status'] = $res['status'];
				} else {
					$data['processed_image_th'] = $res['th'];
					$data['processed_image'] = $res['image'];
				}
			} catch (Exception $e) {
				throw new Exception('Failed to get image status for id: '.$id);
			}
			
		} catch (Exception $e) {
			$error_msg['error'] = $e->getMessage();
		}
		
		if (!empty($error_msg)) {
			if ($format == 'xml') {
				xmlbuilder()->build_xml($error_msg);
				xmlbuilder()->output_error();
			} else if ($format == 'json') {
				Utils::output_json($error_msg);
			}
		} else {
			if ($format == 'xml') {
				xmlbuilder($this->label,$this->version)->build_xml($data);
				xmlbuilder($this->label,$this->version)->output();
			} else if ($format == 'json') {
				Utils::output_json($data);
			}
		}
	}

	public function is_alive() {
		$this->label = 'RiG';
		$this->version = '0.2';
		
		$this->doNotRenderHeader = 1;
		$this->render = 0;
		
		$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'xml';
		
		$data['alive_status'] = 'true';
		
		if ($format == 'xml') {
			xmlbuilder($this->label,$this->version)->build_xml($data);
			xmlbuilder($this->label,$this->version)->output();
		} else if ($format == 'json') {
			Utils::output_json($data);
		}
	}

	protected function generateID() {
    $id = uniqid("", true);
		$id = str_replace(".", "", $id);
		return $id;
	}


	protected function set_extension_by_mime($type) {
		$mime_types = $this->mime_to_extension();
		
		if(array_key_exists($type, $mime_types)) {
			return $mime_types[$type];
		} else {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), Failed to determine extension by mime type');
		}
	}

	protected function allowed_extension($extension) {
		$allowed_extensions = array(
				'png',
				'jpg',
				'bmp',
				'gif',
				'tif'
			);
		return in_array($extension, $allowed_extensions);
	}

	protected function allowed_mime_type($mimetype) {
		$allowed_mime_types = array(
			'image/png',
			'image/jpeg',
			'image/gif',
			'image/bmp',
			'image/tiff'
		);
		return in_array($mimetype, $allowed_mime_types);
	}


	protected function mime_to_extension() {
		return array(
			'image/png'		=>	'png',
			'image/jpeg'	=>	'jpg',
			'image/bmp'		=>	'bmp',
			'images/gif'	=>	'gif',
			'image/tiff'	=>	'tif'
		);
	}

	protected function set_content_by_ext($extension) {
	  return $this->get_php_mime_type($extension);
	}
	
	protected function get_php_mime_type($mimetype) {
		$mapping = array(
			'png'		=>	'image/png',
			'jpg'		=>	'image/jpeg',
			'jpeg'	=>	'image/jpeg',
			'bmp'		=>	'image/bmp',
			'gif'		=>	'image/gif',
			'tif'		=>	'image/tiff',
		);
		
		if (array_key_exists($mimetype, $mapping)) {
			return $mapping[$mimetype];
		} else {
			return false;
		}
	}

  /**
	 *	@param filename
	 *	@return extension
	 */
	protected function getFileExtension($filename) {		
    $extension = explode('.', $filename);
		if(count($extension) > 0) {
	    $n = count($extension)-1;
	    $extension = $extension[$n];
	    return $extension;
		} else {
			return '';
		}
	}
	
	/* 
	 * Takes an array which contains ['error'], ['tmp_name'], ['type']
	 * (usually $_FILES['filename'])
	 *
	 * Returns ['path'] ['name'] ['extension'] - of the moved file or
	 * Exception on error
	 * 
	 */
	protected function moveUploaded($fileInfo) {
		
		//This can be moved to calling function
		if ((isset($fileInfo['error']) && $fileInfo['error'] == 0) ||
				(!empty($fileInfo['tmp_name']) && $fileInfo['tmp_name'] != 'none')) {
		
			//Check that file has sucessfully uploaded and generate new unique filename
			if (is_uploaded_file($fileInfo['tmp_name'])) {
					$extension = $this->set_extension_by_mime($fileInfo['type']);
					$newName = $this->generateID().'.'.$extension;
					$target_path = UPLOAD_DIR.$newName;
			
					//Move file from php.ini tmp folder to framework target folder
					if (move_uploaded_file($fileInfo['tmp_name'], $target_path)) {
					
						$data['name'] = $newName;
						$data['extension'] = $extension;
						//$data['path'] = UPLOAD_DIR;
						return $data;
					
					} else {
						throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), temporary file: '.$fileInfo['tmp_name'].' does not exist');
					}
			} else {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), temporary file: '.$fileInfo['tmp_name'].' was not correctly uploaded');
			}
		} else {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), temporary file: '.$fileInfo['tmp_name'].' does not exist');
		}
	}


	/**
	 * @param ['path'] ['name'] ['resize'] optional Width x Height
	 * @return ['path'] ['name'] or Exception
	 */
	
	protected function adaptiveResize($fileInfo) {
		$original_name = $fileInfo['name'];
		$original = UPLOAD_DIR.$fileInfo['name'];

		$resize = isset($fileInfo['resize']) ? $fileInfo['resize'] : DEFAULT_RESIZE;
		$thumb_name = 'thumb_'.$original_name;
		$thumb = UPLOAD_DIR.$thumb_name;
		exec(CONVERT_PATH."convert -adaptive-resize $resize $original $thumb", $output, $status);
		//Logger::log_msg('Notice: adaptiveResize() size '.$resize.' original: '.$original.' thumb: '.$thumb.' status: '.$status);
			
		if ($status == 0) {
			chmod("$thumb", 0644);
			//$data['path'] = $fileInfo['path'];
			$data['name'] = $thumb_name;
			return $data;
		}	else {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), Failed to adaptive resize '.$original.' Exited with status code '.$status);
		}
	}


	/** 
	 * @param ['path'] ['name'] ['extension'] ['th']
	 * @return  ['path'] ['name'] ['extension'] ['th'] ['queue_id']
	 */
	protected function enqueue($fileInfo, $selected_areas) {
		$data['extension'] = $fileInfo['extension'];
		$th = (int)$fileInfo['th'];
		$original_name = $fileInfo['name'];
		$device_id = $fileInfo['device_id'];
		$original = UPLOAD_DIR.$fileInfo['name'];
		$copy_name = $this->generateID().'.'.$fileInfo['extension'];
		$copy = UPLOAD_DIR.$copy_name;
		$resize = isset($fileInfo['resize']) ? $fileInfo['resize'] : DEFAULT_RESIZE;
		$width = isset($fileInfo['width']) ? $fileInfo['width'] : '';
		$height = isset($fileInfo['height']) ? $fileInfo['height'] : '';
		$bytes = isset($fileInfo['bytes']) ? $fileInfo['bytes'] : '';

		$selections = '';
		if (!empty($selected_areas)) {
			foreach($selected_areas as $sa) {
				$type = array_shift($sa);
				$comma_separated = implode(",", $sa);
				if (empty($selections)) {
					$selections .= $type.' '.$comma_separated;
				} else {
					$selections .= ' '.$type.' '.$comma_separated;
				}
			}
		}
		
		if (copy($original, $copy)) {
			$output = array();
			chmod("$original", 0644);
			chmod("$copy", 0644);


			$data['image'] = $copy_name;
			$data['th'] = $th;
			$data['selections'] = $selections;
			$data['resize'] = $resize;
			$data['device_id'] = $device_id;
			$data['ip'] = $_SERVER['REMOTE_ADDR'];
			$data['width'] = $width;
			$data['height'] = $height;
			$data['bytes'] = $bytes;
			
			$id = $this->model->enqueue($data);

			if (!empty($id) && $id > 0) {
				//$data['path'] = $fileInfo['path'];
				$data['name'] = $copy_name;
				$data['th'] = $th;
				$data['queue_id'] = $id;
				return $data;
			} else {
				throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), Failed to add job to queue.');
			}
		} else {	
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), Failed to copy'.$original_name.' as '.$copy_name);
		}

	}


  protected function file_upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension';
        default:
            return 'Unknown upload error';
    }
  }
 
}
