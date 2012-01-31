<?php

class StaticsController extends Controller {

	function index() {
		$this->set('title',"Red iGone - The world's easiest red-eye removal tool");
	}

	function splash() {
		$this->doNotRenderHeader = 1;
	}

	function about() {
		$this->set('title','Red iGone - About');
	}

	function help() {
		$this->set('title','Red iGone - Help');
	}
	
	function share() {
		$this->set('title','Red iGone - Spread the Love');
	}
	
	function contact() {
		$this->set('title','Red iGone - Contact Us');
	}	

	function ios() {
		$this->set('title','Red iGone - Red iGone for the iPad');		
	}

	function sitemap() {
		$this->doNotRenderHeader = 1;
		$this->render = 0;
		header ("Content-Type:text/xml");
		echo page()->generate_sitemap();
	}

	function robots() {
		$this->doNotRenderHeader = 1;
	}

	function favicon() {
		$this->doNotRenderHeader = 1;
	}

}