#!/usr/bin/php -q
<?php

//update image_queue set status = 'new', processed_at = null, completed_at = null, owner=null;
//sudo -u www-data /usr/bin/php /var/www/rig/application/controllers/queuecontroller.php
//sudo -u www-data /usr/bin/php /var/www/application/controllers/queuecontroller.php

//* * * * * www-data /usr/bin/php /var/www/rig/application/controllers/queuecontroller.php

//need to chmod a+X /var/log/apache2
//and chmod a+w /var/log/apache2/error.log

error_reporting(E_ALL);
ini_set('display_errors','Off');
ini_set('log_errors', 'On');

define('DB_DATABASE', 'rig');
define('DB_USER', 'rig');
define('DB_PASSWORD', 'rig@rig0102');
define('DB_HOST', 'localhost');

define('UPLOAD_DIR', '/export/uploads/');

define('GIMP_PATH', "/usr/bin/");
define('CONVERT_PATH', "/usr/bin/");
define('COMPOSITE_PATH', "/usr/bin/");

define('DEFAULT_RESIZE', "256x256");

define('ERROR_LOG', "/var/log/rigq.log");
define('VERBOSE_LOG', true);

$sleeptime = null;

if($argc > 1) {
	$sleeptime = $argv[1];
}

main($sleeptime);

function main($sleeptime) {
	if(isset($sleeptime)) {
		sleep(intval($sleeptime));
	}

	do {
		set_time_limit(300);
		unset($job);
		
		$job = q()->getJob();
		if($job['status'] == true) {
			$queue = true;
			//$image = q()->processJob($job);
			if(q()->processJob($job)) {
				$status = q()->updateJob($job['id']);
				if(!$status) {
					error_log('Queue Error: Failed to update job id: '.$job['id'].' after processing'."\n", 3, ERROR_LOG);
					$queue = false;
				}	
			} else {
				error_log('Queue Error: Failed to process job id: '.$job['id']."\n", 3, ERROR_LOG);
				$queue = false;	
			}
		} else {
			$queue = false;
		}
	} while($queue == true);
}

//Queue class
function q() {
  static $q = null;
  if(is_null($q)) 
		$q = new Queue();
  return $q;
}

class Queue {
	
	private $owner;
	
	function __construct() {
		$this->owner = php_uname('n').'_'.posix_getpid();
  }

	public function getJob() {
		try {
			$query1 = "UPDATE image_queue SET status='processing', processed_at='now()', owner=(?) WHERE id = (SELECT id FROM image_queue WHERE status='new' ORDER BY id LIMIT 1) RETURNING status";
			$query2 = "SELECT id, image, th, selections, resize FROM image_queue WHERE owner = (?) AND status = 'processing'";

			$stmt = db()->prepare($query1);			 	
			$stmt->bindParam(1,$this->owner);
			$res = $stmt->execute();
			$res_set = $stmt->fetch(PDO::FETCH_ASSOC);
		
			if($res_set['status'] == 'processing') {
				unset($res);
				unset($res_set);
				unset($stmt);
				$stmt = db()->prepare($query2);			 	
				$stmt->bindParam(1,$this->owner);
				$res = $stmt->execute();
				$res_set = $stmt->fetch(PDO::FETCH_ASSOC);
				if(is_array($res_set)) {
					$res_set['status'] = true;
					return $res_set;
				} else {
					return array('status'=>false);
				}
			} else {
				return array('status'=>false);
			}

		} catch(Exception $e) {
				$stmt->rollBack();
				error_log('Queue Error: '.$e->getMessage()."\n", 3, ERROR_LOG);
				return array('status'=>false);
		}
	}

	public function processJob($image) {
		$fileInfo = $this->redEyeFilter($image);
		
		if(isset($image['resize'])) {
		  $fileInfo['size'] = $image['resize'];
		}
		
		if(isset($fileInfo['image'])) {
			$res = $this->adaptiveResize($fileInfo);
		}

		return $res;
	}

	public function updateJob($id) {
		try {
			$query = "UPDATE image_queue SET status='done', completed_at='now()' WHERE id = (?)";
			$stmt = db()->prepare($query);
			$stmt->bindParam(1,$id);
			$res = $stmt->execute();
			if($res) {
				return true;
			}
		} catch(Exception $e) {
			$stmt->rollBack();
			error_log('Queue Error: '.$e->getMessage()."\n", 3, ERROR_LOG);
			return false;
		}
	}
	
	protected function generateID() {
    $id = uniqid("", true);
		$id = str_replace(".", "", $id);
		return $id;
	}	

	protected function getFileExtension($filename) {		
    $extension = split("[/\\.]", $filename);
		if(count($extension) > 0) {
	    $n = count($extension)-1;
	    $extension = $extension[$n];
	    return $extension;
		} else {
			return '';
		}
	}

	//removed 2010-04-02
	/*
	protected function watermark($fileLogo, $fileOriginal) {
		$data['error'] = "";
		$original_name = $fileOriginal['image'];
		$original = UPLOAD_DIR.$fileOriginal['image'];
		$watermark_logo = $fileLogo['image'];
		$watermarked = $original;
		
		exec(COMPOSITE_PATH."composite -gravity south-east $watermark_logo $original $watermarked", $output, $status);

		if($status == 0) {
			chmod("$watermarked", 0644);
			//$data['path'] = $fileOriginal['path'];
			//$data['name'] = $fileOriginal['name'];
		}	else {
			error_log("Failed to watermark $original. Exited with status code $status", 0);
			return false;
		}
		
		return true;
	}
	*/

	protected function adaptiveResize($fileInfo) {
		$data['error'] = "";
		$original_name = $fileInfo['image'];
		$original = UPLOAD_DIR.$fileInfo['image'];

		$size = isset($fileInfo['size']) ? $fileInfo['size'] : DEFAULT_RESIZE;
				  
		$thumb_name = 'thumb_'.$original_name;		
		$thumb = UPLOAD_DIR.$thumb_name;		

	  if(VERBOSE_LOG) {
	    $log_msg = CONVERT_PATH."convert -adaptive-resize $size $original $thumb > /dev/null 2>&1";
	    error_log("Convert command: $log_msg\n", 3, ERROR_LOG);
    }
    
		$lastline = exec(CONVERT_PATH."convert -adaptive-resize $size $original $thumb > /dev/null 2>&1", $output, $status);

		if(VERBOSE_LOG) {
		  error_log("Convert status code: $status\n", 3, ERROR_LOG);
		  //error_log("Convert output: $output[0]\n", 3, ERROR_LOG);
		  error_log("Convert last line of exec: $lastline\n", 3, ERROR_LOG);
	  }
	
		if($status == 0) {
			chmod("$thumb", 0644);
		}	else {
			error_log("Convert Error: Failed to adaptive resize $original. Exited with status code $status\n", 3, ERROR_LOG);
			return false;
		}
		return true;
	}

	protected function redEyeFilter($image) {
		$data['error'] = "";
		$th = intval($image['th']);
		$selections = !empty($image['selections']) ? $image['selections'] : '';
		$copy_name = $image['image'];
		$copy = UPLOAD_DIR.$image['image'];
		$output = array();

    system("export HOME=/tmp", $status);
    if($status != 0) {
    	error_log("Queue Error: Failed export GIMP home directory. Exited with status code $status\n", 3, ERROR_LOG);
    	exit(1);
    }

		if(empty($selections)) {
		  
		  if(VERBOSE_LOG) {
		    $log_msg = 'export GIMP2_DIRECTORY=/tmp/.gimp && '.GIMP_PATH."gimp -d -f -i -b '(python-rig 1 0 0 \"$copy\" $th \"\")' -b '(gimp-quit 0)' > /dev/null 2>&1";
		    error_log("Gimp command: $log_msg\n", 3, ERROR_LOG);
	    }
			
			$lastline = exec('export GIMP2_DIRECTORY=/tmp/.gimp && '.GIMP_PATH."gimp -d -f -i -b '(python-rig 1 0 0 \"$copy\" $th \"\")' -b '(gimp-quit 0)' > /dev/null 2>&1", $output, $status);
			
			if(VERBOSE_LOG) {
			  error_log("Gimp status code: $status\n", 3, ERROR_LOG);
			  //error_log("Gimp output: $output[0]\n", 3, ERROR_LOG);
			  error_log("Gimp last line of exec: $lastline\n", 3, ERROR_LOG);
		  }
			
			//system(GIMP_PATH."gimp -i -b '(python-rig 1 0 0 \"$copy\" $th \"\")' -b '(gimp-quit 0)'", $status);
		} else {
		  
		  if(VERBOSE_LOG) {
		    $log_msg = 'export GIMP2_DIRECTORY=/tmp/.gimp && '.GIMP_PATH."gimp -d -f -i -b '(python-rig 1 0 0 \"$copy\" $th \"$selections\")' -b '(gimp-quit 0)' > /dev/null 2>&1";
		    error_log("Gimp command: $log_msg\n", 3, ERROR_LOG);
	    }
		  
			$lastline = exec('export GIMP2_DIRECTORY=/tmp/.gimp && '.GIMP_PATH."gimp -d -f -i -b '(python-rig 1 0 0 \"$copy\" $th \"$selections\")' -b '(gimp-quit 0)' > /dev/null 2>&1", $output, $status);
			
			if(VERBOSE_LOG) {
			  error_log("Gimp status code: $status\n", 3, ERROR_LOG);
			  //error_log("Gimp output: $output[0]\n", 3, ERROR_LOG);
			  error_log("Gimp last line of exec: $lastline\n", 3, ERROR_LOG);
		  }
			//system(GIMP_PATH."gimp -i -b '(python-rig 1 0 0 \"$copy\" $th \"$selections\")' -b '(gimp-quit 0)'", $status);
		}

		if($status == 0) { //status == 0 means successful gimp execution
			$data['image'] = $copy_name;
		} else {
			error_log("Queue Error: Failed to apply red-eye removal filter. Exited with status code $status\n", 3, ERROR_LOG);
			$data['error'] = true;
		}
		
		return $data;
	}
	
}


//Database functions
function db() {
  static $db = null;
  if(is_null($db))
    $db = new DB();
  return $db;
}

class DB extends PDO {
	
    protected $dbconn;
    protected $result;

	  function __construct() {
	    parent::__construct(
	    sprintf('pgsql:host=%s;dbname=%s', DB_HOST, DB_DATABASE), DB_USER, DB_PASSWORD);
	  }
	
	  public function query() {
	    $args = func_get_args();
	    return call_user_func_array(array('parent', 'query'), $args);
	  }
}
