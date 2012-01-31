<?php

class StaticsController extends Controller {

	function index() {
		$this->set('title','api.redigone.com');
	}

	function robots() {
		$this->doNotRenderHeader = 1;
	}
}