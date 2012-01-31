<?php
class Controller {

	protected $model;
	protected $controller;
	protected $action;
	protected $template;

	public $doNotRenderHeader;
	public $renderSpecialHeader;
	public $render;

	function __construct($model, $controller, $action) {

		$this->controller = $controller;
		$this->action = $action;
		$this->model = $model;
		$this->doNotRenderHeader = 0;
		$this->render = 1;
		$this->renderSpecialHeader = 0;

		//We do not require models for all controllers
		if(!empty($model)) {
			$this->model = new $model;
		}
		$this->template = new Template($controller,$action);
	}

	function get($var) {
		if(isset($this->$var)) {
			return $this->$var;
		} else {
			return null;
		}
	}

	function set($name,$value) {
		$this->template->set($name,$value);
	}

	function __destruct() {
		if ($this->render) {
			$this->template->render($this->doNotRenderHeader, $this->renderSpecialHeader);
		}
	}

}
