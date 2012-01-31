<?php
	function xmlbuilder($root_name='RiG', $version=API_VERSION) {
		static $xb = null;
		if(is_null($xb))
			$xb = new XMLBuilder($root_name, $version);
		return $xb;
	}

	class XMLBuilder {
		
		var $w;

		function __construct($root_name, $version) {
			$this->w = new XMLWriter();
			$this->w->openMemory(); 
			$this->w->setIndent(true);
			$this->w->startDocument('1.0','UTF-8');
			$this->w->startElement($root_name);
			if ($version != null) {
				$this->w->writeAttribute("version", $version);
			}
			$this->w->writeAttribute("created", strftime("%Y-%m-%d %H:%M %z"));
		}
		
		protected function set_element($element_name, $element_text) {
			$this->w->startElement($element_name);
				$this->w->text($element_text);
			$this->w->endElement();
		}

		//$error_msg['error']['missing_image'] = 'Missing image file';
		
		public function build_xml($data) {
			if (is_array($data)) {
				foreach($data as $e=>$t) {
					if(is_array($t)) {
						$this->w->startElement($e);
						$this->build_xml($t);
						$this->w->endElement();
					} else {
						$this->set_element($e, $t);
					}
				}
			} else {
				$this->set_element('error', 'Unknown error');
			}
		}

		protected function getDocument() {
			$this->w->endElement();
			$this->w->endDocument();
			return $this->w->outputMemory();
		}

		public function output() {
			header('HTTP/1.0 200 OK');
			header('Content-Type:text/xml');
			echo $this->getDocument();
		}

		public function output_error() {
			header('HTTP/1.0 400 Bad Request');
			header('Content-Type:text/xml');
			echo $this->getDocument();
		}

	}
