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
		return array('controller'=>'statics', 'model'=>'', 'action'=>'splash');
	}
	
	public function generate_sitemap() {
		$cnames = $this->get_cnames();
		$index = array(
			'image-upload',
			'contact',
			'help',
			'about',
			'share',
		);
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">';

		$xml .= '<url>';
	  $xml .='<loc>http://www.redigone.com/</loc>';
	  $xml .= '<changefreq>weekly</changefreq>';
		$xml .= '<priority>0.5</priority>';
		$xml .= '</url>';
		
		foreach($cnames as $key=>$val) {
			if(in_array($key, $index)) {
				$xml .= '<url>';
			  $xml .='<loc>http://www.redigone.com/'.$key.'</loc>';
			  $xml .= '<changefreq>weekly</changefreq>';
				$xml .= '<priority>0.5</priority>';
				$xml .= '</url>';
			}
		}
		
		$xml .= '</urlset>';
		return $xml;
	}
	
	protected function get_cnames() {
		return array(
			'index' => array(
				'controller'=>'statics', 'model'=>'', 'action'=>'splash'
			),

			'oldsplash' => array(
				'controller'=>'statics', 'model'=>'', 'action'=>'index'
			),

      //Image handling
			'image-upload' => array(
				'controller'=>'images', 'model'=>'', 'action'=>'upload'
			),

  		'simple-upload' => array(
  			'controller'=>'images', 'model'=>'', 'action'=>'simple_upload'
  		),

			'new-upload' => array(
				'controller'=>'images', 'model'=>'', 'action'=>'upload'
			),
			
			'select-red-eyes' => array(
				'controller'=>'images', 'model'=>'', 'action'=>'select'
			),

			'image-preview' => array(
				'controller'=>'images', 'model'=>'', 'action'=>'preview'
			),

			'image-download' => array(
				'controller'=>'images', 'model'=>'', 'action'=>'download'
			),

			'image-status' => array(
				'controller'=>'images', 'model'=>'', 'action'=>'get_status'
			),

      //Email
			'email-image' => array(
				'controller'=>'images', 'model'=>'', 'action'=>'email_form'
			),
			
			'send-email' => array(
				'controller'=>'images', 'model'=>'', 'action'=>'send_email'
			),
			
			//Static pages
			'about' => array(
				'controller'=>'statics', 'model'=>'', 'action'=>'about'
			),
			
			'help' => array(
				'controller'=>'statics', 'model'=>'', 'action'=>'help'
			),
						
			'contact' => array(
				'controller'=>'statics', 'model'=>'', 'action'=>'contact'
			),
			
			'share' => array(
				'controller'=>'statics', 'model'=>'', 'action'=>'share'
			),

			/*'ios' => array(
				'controller'=>'statics', 'model'=>'', 'action'=>'ios'
			),*/

			'sitemap.xml' => array(
				'controller'=>'statics', 'model'=>'', 'action'=>'sitemap'
			),
					
      //Special files
		  'robots.txt' => array(
			  'controller'=>'statics', 'model'=>'', 'action'=>'robots'
		  ),

		  'favicon.ico' => array(
			  'controller'=>'statics', 'model'=>'', 'action'=>'favicon'
		  ),
		
		);
	}
}