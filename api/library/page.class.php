<?php
function page() {
  static $page = null; 
  if(is_null($page))
    $page = new Page();
  return $page;
}

class Page {
	public $cnames = array();

	function __construct() {
		$this->cnames = $this->get_cnames();
	}
	
	public static function default_controller() {
		return array('controller'=>'statics', 'model'=>'', 'action'=>'index');
	}
	
	protected function get_cnames() {
		return array(
			'index' => array(
				'controller'=>'statics', 'model'=>'', 'action'=>'index'
			),

			//Public API 0.3
			'0.3/image-upload' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'image_upload'
			),

			'0.3/image-upload-process' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'image_upload_process'
			),

			'0.3/image-status' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'get_status'
			),

			'0.3/generate-thumb' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'generate_thumb'
			),

			'0.3/image-download' => array(
				'controller'=>'api', 'model'=>'', 'action'=>'image_download'
			),

			'0.3/is-alive' => array(
				'controller'=>'api', 'model'=>'', 'action'=>'is_alive'
			),

			'0.3/generate-api-key' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'generate_api_key'
			),

			'0.3/validate-api-key' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'validate_api_key'
			),

			//Public API 0.2
  		'0.2/image-upload' => array(
  			'controller'=>'api', 'model'=>'api', 'action'=>'image_upload'
  		),

			'0.2/image-upload-process' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'image_upload_process'
			),

			'0.2/image-status' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'get_status'
			),
			
			'0.2/generate-thumb' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'generate_thumb'
			),

			'0.2/image-download' => array(
				'controller'=>'api', 'model'=>'', 'action'=>'image_download'
			),

			'0.2/is-alive' => array(
				'controller'=>'api', 'model'=>'', 'action'=>'is_alive'
			),

			//Public API 0.1
  		'0.1/image-upload' => array(
  			'controller'=>'api', 'model'=>'api', 'action'=>'image_upload'
  		),

			'0.1/image-status' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'get_status'
			),
			
			'0.1/generate-thumb' => array(
				'controller'=>'api', 'model'=>'api', 'action'=>'generate_thumb'
			),

			'0.1/image-download' => array(
				'controller'=>'api', 'model'=>'', 'action'=>'image_download'
			),

			'0.1/is-alive' => array(
				'controller'=>'api', 'model'=>'', 'action'=>'is_alive'
			),
					
      //Special files
		  'robots.txt' => array(
			  'controller'=>'statics', 'model'=>'', 'action'=>'robots'
		  ),		
		);
	}
}
