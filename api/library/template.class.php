<?php

class Template {

	protected $variables = array();
	protected $controller;
	protected $action;
	protected $utils;
	protected	$header;	

	function __construct($controller,$action) {
		$this->controller = $controller;
		$this->action = $action;
		$this->utils = new Utils;
		if(LOAD_TIME) {
			$this->utils->timerStart();
		}
	}

	/** Set Variables **/
	public function set($name,$value) {
		$this->variables[$name] = $value;
	}

	/** Display Template **/
	public function render($doNotRenderHeader = 0, $renderSpecialHeader = 0) {
		
		//If we do render header, a view must exist
		if($doNotRenderHeader == 0 && !file_exists(BASE_DIR.'/application/views/'.$this->controller.'/'.$this->action.'.php')) {
			//no matching view for action found
			//throw new Exception("404: File not found", 404);
			//how to propagate this exception?
			Logger::log_msg('Error: render() no matching view for action '.$this->controller.'/'.$this->action);
			header("Location: /");
			exit;
		}		
		
		extract($this->variables);

		if($doNotRenderHeader == 0 ||  $renderSpecialHeader == 1) {
			//here used to be js and css
    }

		if($doNotRenderHeader == 0) {
			if(file_exists(BASE_DIR.'/application/views/common/header.php')) {
				include (BASE_DIR.'/application/views/common/header.php');
			}

			if(file_exists(BASE_DIR.'/application/views/'.$this->controller .'/header_'.$this->action.'.php')) {
				include (BASE_DIR.'/application/views/'.$this->controller .'/header_'.$this->action.'.php');
			}

			if(file_exists(BASE_DIR.'/application/views/common/body.php')) {
				include (BASE_DIR.'/application/views/common/body.php');
			}

		} else if($renderSpecialHeader == 1) {
			//GOTCHA: This has to be included last, since it outputs the header
			if(file_exists(BASE_DIR.'/application/views/common/special_header.php')) {
				include (BASE_DIR.'/application/views/common/special_header.php');
			}
		}
	
		if(file_exists(BASE_DIR.'/application/views/'.$this->controller.'/'.$this->action.'.php')) {
			include (BASE_DIR.'/application/views/'.$this->controller.'/'.$this->action.'.php');		 
		}

	
		if($doNotRenderHeader == 0) {
			if(file_exists(BASE_DIR.'/application/views/'.$this->controller .'/footer.php')) {
				include (BASE_DIR.'/application/views/'.$this->controller .'/footer.php');
			}
		
			if(file_exists(BASE_DIR.'/application/views/common/footer.php')) {
				include (BASE_DIR.'/application/views/common/footer.php');
			}
		} else if($renderSpecialHeader == 1) {
			if(file_exists(BASE_DIR.'/application/views/'.$this->controller .'/footer.php')) {
				include (BASE_DIR.'/application/views/'.$this->controller .'/footer.php');
			}
		
			if(file_exists(BASE_DIR.'/application/views/common/special_footer.php')) {
				include (BASE_DIR.'/application/views/common/special_footer.php');
			}			
		}
	}
	
}
