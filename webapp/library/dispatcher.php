<?php
function setReporting() {
	if (Utils::env('DEBUG') == 'true') {
		error_reporting(E_ALL);
		ini_set('display_errors', "1");
		define('LOAD_TIME', true);
	} else {
		error_reporting(E_ALL);
		ini_set('display_errors', "0");
		ini_set('log_errors', "0");
		define('LOAD_TIME', false);
	}
}

/**************
	All cases
	http://rig.blomqwist.com/ - index page = default
	http://rig.blomqwist.com/images/ - valid controller no action = 404
	http://rig.blomqwist.com/images/upload - valid controller valid action = valid
	http://rig.blomqwist.com/test - invalid controller = 404
	http://rig.blomqwist.com/images/test - valid controller invalid action = 404
	Block directory listings in htconf of all folders under /public

***************/
function main() {
	//override the session class with custom APC cache session handler
	$session = new Session();
	session_start();
	
	$status = "";

	//explode URL
	$url = strtok($_SERVER['REQUEST_URI'], '?');
	$url = explode('/',$url);
	$url = array_filter($url);

	//Save url for ajax check before imploding
	$ajax_url = $url;

	//Last item in $url is the cname
	$c = count($url);
	
	//Allow for deep path in cname e.g. api/1.0/upload-image
	if($c > 1) {
		$url[$c] = implode('/',$url);
	}

	//API logic
	if(isset($url[1]) && $url[1] == 'api') {
		define('API_VERSION', $url[2]);
	}

	//canonical urls
	$page = new Page();
	if(isset($url[$c]) && isset($page->cnames[$url[$c]])) {
		$controllerName = $page->cnames[$url[$c]]['controller'];
		$controller = ucwords($controllerName);
		$controller .= 'Controller';
		$model = $page->cnames[$url[$c]]['model'];
		$action = $page->cnames[$url[$c]]['action'];
	} else if(!empty($ajax_url[1])) {
 		if(Utils::is_Ajax()) {
			//controller
			$controller = $ajax_url[1];

			//action
			if(!empty($ajax_url[2])) {
				$action = $ajax_url[2];
				$controllerName = $controller;
				$controller = ucwords($controller);
				if (file_exists(BASE_DIR.'/application/models/'.rtrim($controller, 's').'.php')) {				
					$model = rtrim($controller, 's');
				}	else {
					$model = '';
				}
				$controller .= 'Controller';
			} else {
				$status = "404";	//no action specified
			}
		} else {
			$status = "404";	//not an Ajax call
		}
	} else {
		//default page
		$default = Page::default_controller();
		$controller = ucwords($default['controller']) . 'Controller';
		$model = $default['model'];
		$action = $default['action'];
		$controllerName = $default['controller'];
		$queryString = array();
	}

	//404 page
	if($status == "404") {
		$controller = "ExceptionsController";
		$model = "Exception";
		$controllerName = "exceptions";
		$action = "status_404";	
	}

	$dispatch = new $controller($model,$controllerName,$action);

	if((int)method_exists($controller, $action)) {
		try {
			call_user_func(array($dispatch,$action));
		}	catch (Exception $e) {
			
			switch ($e->getCode()) {
				case '404':
					$action = "status_404";
					Logger::log_msg('Error 404 Exception message '.$e->getMessage());
					break;
			
				case '6001':
					$action = "status_404_file";
					Logger::log_msg('Error 404 File Exception message '.$e->getMessage());					
					break;

				default:
				//Uncaught exceptions are caught and logged
					$action = "status_500";

					if ($_SERVER['SERVER_NAME'] == 'rig.blomqwist.com') {
						echo 'Exception: <br />'.$e->getMessage();
						Logger::log_msg('Exception: '.$e->getMessage());
						exit;
					} else {
						Logger::log_msg('Error 500 Exception message: '.$e->getMessage());
					}	
			}
						
			$controller = "ExceptionsController";
			//$model = "Exception";	//uncomment to implement model layer for Exceptions
			$model = '';
			$controllerName = "exceptions";
			$dispatch = new $controller($model,$controllerName,$action);			
			call_user_func(array($dispatch,$action));
		}		
	} else {
		/* Error Generation Code Here */
		
		//investigate if it is possible to get here at all
	}
}

/** Try to autoload any classes that are required **/
function __autoload($className) {
	if (file_exists(BASE_DIR.'/library/'.strtolower($className).'.class.php')) {
		require_once(BASE_DIR.'/library/'.strtolower($className).'.class.php');
	} else if (file_exists(BASE_DIR.'/application/controllers/'.strtolower($className).'.php')) {
		require_once(BASE_DIR.'/application/controllers/'.strtolower($className).'.php');
	} else if (file_exists(BASE_DIR.'/application/models/'.strtolower($className).'.php')) {
		require_once(BASE_DIR.'/application/models/'.strtolower($className).'.php');
	} else {
		Logger::log_msg('Error: __autoload() '.$className.' class not found ');
	}
}

setReporting();
main();