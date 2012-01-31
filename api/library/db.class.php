<?php
/*
 *	Usage:
 *
 *	$query = 'INSERT INTO beta_signup(email, ip) VALUES (:email, :ip) RETURNING email';
 *	$email = 'user@redigone.com;
 *	$ip = $_SERVER['REMOTE_ADDR'];
 *
 *  $params[] = array('name'=>':email', 'value'=>$email);
 *  $params[] = array('name'=>':ip', 'value'=>$ip);
 *
 *	$res = db()->exec_query($query, $params);
 *
 */

function db() {
  static $db = null;
  if (is_null($db)) {
    $db = new DB();
	}
  return $db;
}

class DB extends PDO {
	
	protected $dbconn;
	protected $result;

  function __construct() {
    parent::__construct(
    sprintf('pgsql:host=%s;dbname=%s', DB_HOST, DB_DATABASE), DB_USER, DB_PASSWORD);
  }

	//Execute query as a stored procedure with optional named parameters
	public function exec_query($query, $params=null, $clean_single = true) {

		if (empty($query)) {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), empty SQL query string');
		}

		try {
			$stmt = $this->prepare($query);

			if (is_array($params)) {
				foreach($params as $p) {
					$stmt->bindParam($p['name'],$p['value']);
				}
			}

			$res = $stmt->execute();
			$res_set = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if(isset($res_set[0]) && $clean_single) {
				return $res_set[0];
			} else {
				return $res_set;
			}
		} catch(PDOException $e) {
			$stmt->rollBack();
			throw $e;
		}

	}

	//Returns the maximum length for a given database column name
	//or Exception if not found
	public function max_length($table, $column) {
		$map = array(

					'api_keys' => array(
						'key' => 32,
						'user' => 255
					),
					
					'api_sessions' => array('session_id' => 32),

					'api_thumbs'	=> array('device_id' => 255),

					'api_uploads' => array('device_id' => 255),

					'api_usage'		=> array(
						'device_id' 						=> 255,
						'device_name'						=> 255,
						'device_systemName'			=> 255,
						'device_systemVersion'	=> 255,
						'device_model'					=> 255,
						'device_localizedModel'	=> 255,
						'app_name'							=> 255,
						'app_version'						=> 30
						),

					'image_queue'	=> array(
						'image' 								=> 32,
						'selections'						=> 2048,
						'status'								=> 10,
						'owner'									=> 255,
						'resize'								=> 11,
						'device_id'							=> 255,
						'width'									=> 5,
						'height'								=> 5,
						'bytes'									=> 10
						),

					);

		if (array_key_exists($table, $map)) {
			if (array_key_exists($column, $map[$table])) {
				return $map[$table][$column];
			}
		} else {
			throw new Exception(__CLASS__.'::'.__FUNCTION__.'(), column: '.$column.' in table: '.$table.' not found');
		}
	}

}
