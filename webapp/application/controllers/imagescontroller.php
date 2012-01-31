<?php
class ImagesController extends Controller {

	function upload() {
		$this->set('title','Red iGone - Upload Image');
		$this->set('session_id', session_id());
		
		if(Utils::env('REQUEST_URI') == '/new-upload') {
			if(ini_get("session.use_cookies")) {
			    $params = session_get_cookie_params();
			    setcookie(session_id(), '', time() - 42000,
			        $params["path"], $params["domain"],
			        $params["secure"], $params["httponly"]
			    );
				session_destroy();
				$session = new Session();
				session_start();
			}
		}
		
		if(isset($_SESSION['images'])) {
				$images = $_SESSION['images'];
			
				if(isset($images['thumbs']) || isset($images['queue'])) {
					header("Location: /image-preview");
				} else {
					header("Location: /select-red-eyes");
				}
		}
	}

	function simple_upload() {
		$this->set('title','Red iGone - Simple Image Upload');
		$this->set('session_id', session_id());

		if(isset($_SESSION['images'])) {
				$images = $_SESSION['images'];
			
				if(isset($images['thumbs']) || isset($images['queue'])) {
					header("Location: /image-preview");
				} else {
					header("Location: /select-red-eyes");
				}
		}
		
		if(ini_get("session.use_cookies")) {		  
		    $params = session_get_cookie_params();
		    setcookie(session_id(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
			session_destroy();
			$session = new Session();
			session_start();
		}
		

	}
	
	function get_selection() {
		$id = $_REQUEST['id'];
		$this->doNotRenderHeader = 1;
		$this->render = 0;
		$selections = isset($_SESSION['selections']) ? $_SESSION['selections'] : '';
		if(!empty($selections)) {
			echo json_encode($selections[$id]);
		} else {
			Logger::log_msg('Error: get_selection() returned empty result');
		}
	}
	
	function get_all_selections() {
		$this->doNotRenderHeader = 1;
		$this->render = 0;		
		$selections = isset($_SESSION['selections']) ? $_SESSION['selections'] : '';
		if(!empty($selections)) {
			echo count($selections);
		}
	}
	
	function save_selection() {
		$this->doNotRenderHeader = 1;
		$this->render = 0;

		$x1 = $_REQUEST['x1'];
		$y1 = $_REQUEST['y1'];
		$x2 = $_REQUEST['x2'];
		$y2 = $_REQUEST['y2'];

		$id = $_REQUEST['id'];

		$selections = isset($_SESSION['selections']) ? $_SESSION['selections'] : '';

		if(empty($selections)) {
			$id = 1;
			$selections[$id] = array('x1' => $x1, 'y1' => $y1, 'x2' => $x2, 'y2' => $y2);
		}	else if($id > 0) {
			$selections[$id] = array('x1' => $x1, 'y1' => $y1, 'x2' => $x2, 'y2' => $y2);
		} else {
			$id = count($selections)+1;
			$selections[$id] = array('x1' => $x1, 'y1' => $y1, 'x2' => $x2, 'y2' => $y2);
		}
		$_SESSION['selections'] = $selections;		
		echo count($selections);
	}

	function remove_selection() {
		$this->doNotRenderHeader = 1;
		$this->render = 0;
		$id = substr($_REQUEST['id'], 1, 1);
		$selections = isset($_SESSION['selections']) ? $_SESSION['selections'] : '';				
		
		if(array_key_exists($id, $selections)) {
			unset($selections[$id]);
			$i = 1;
			if(!empty($selections)) {
				foreach($selections as $s) {
					$temp[$i] = $s;
					$i++;
				}
				$selections = $temp;
			}
		} else {
			Logger::log_msg('Error: remove_selection() failed to remove selection');	
		}
		
		$_SESSION['selections'] = $selections;		
		echo count($selections);
	}
	
	function remove_all_selections() {
		$this->doNotRenderHeader = 1;
		$this->render = 0;
		unset($_SESSION['selections']);
		echo '0';
	}

	function select() {

		//Work-around for setting up a session
		//because Flash Player doesn't send the cookies
		if(isset($_POST["PHPSESSID"])) {
		  session_id($_POST["PHPSESSID"]);
				
			$this->doNotRenderHeader = 1;
			$this->render = 0;
			if(!empty($_FILES)) {
				if($_FILES['filename']['error'] != 0) {
					Logger::log_msg('Error: File upload failed. Error code: '.$_FILES['filename']['error'].', Error message: '.$this->file_upload_error_message($_FILES['filename']['error']));
					if($_POST["simple_upload"]) {
					  setcookie('error_msg', 'An error occured while uploading your file');
				    header("Location: /simple-upload");
				  }
				} else if($_FILES['filename']['size'] > MAX_UPLOAD_SIZE){
					Logger::log_msg('Error: File uploaded exceeeds max upload size '.MAX_UPLOAD_SIZE.' bytes as set in framework configuration');
					if($_POST["simple_upload"]) {
					  setcookie('error_msg', 'Error: The maxmium file size allowed is 2MB');
					  header("Location: /simple-upload");
				  }
				} else {
					//check tmp file for correct mime type
					
					$mime_type = $this->identify_mime_type($_FILES['filename']);
										
					if(empty($mime_type['mime_type'])) {
						Logger::log_msg('Error: Uploaded file MIME Type is not allowed');
  					if($_POST["simple_upload"]) {
  					  setcookie('error_msg', 'Error: Allowed filetypes are png, jpeg, gif, tif and bmp');
  					  header("Location: /simple-upload");
  				  }						
					} else {

						$url = Utils::env('API_URL').API_VERSION_URL.API_IMAGE_UPLOAD_URL;
						
						$post_fields['format'] = 'json';
						$post_fields['device_id'] = DEVICE_ID.'-'.$_SERVER['REMOTE_ADDR'];
						$post_fields['device_name'] = DEVICE_NAME;
						$post_fields['device_systemName'] = DEVICE_SYSTEM_NAME;
						$post_fields['device_systemVersion'] = DEVICE_SYSTEM_VERSION;
						$post_fields['device_model'] = DEVICE_MODEL;
						$post_fields['device_localizedModel'] = DEVICE_LOCALIZED_MODEL;
						$post_fields['app_name'] = APP_NAME;
						$post_fields['app_version'] = APP_VERSION;
						$post_fields['resize'] = DEFAULT_RESIZE;

						//Logger::log_msg('type: '.$_FILES['filename']['type']);exit;
						
						if((isset($_FILES['filename']['error']) && $_FILES['filename']['error'] == 0) ||
								(!empty($_FILES['filename']['tmp_name']) && $_FILES['filename']['tmp_name'] != 'none')) {

							//Check that file has sucessfully uploaded
							if(is_uploaded_file($_FILES['filename']['tmp_name'])) {
									$tmpfile = $_FILES['filename']['tmp_name'];
									$filename = basename($_FILES['filename']['name']);
									$type = $_FILES['filename']['type'];

									$post_fields['filename'] = '@'.$tmpfile.';filename='.$filename.';type='.$type;
							} else {
								Logger::log_msg('Error: select() failed with error code '.$_FILES['filename']['error']);
								$data['error'] = true;
							}
						}

						$c = curl($url, $post_fields)->get_json();						

						if (is_array($c) && !isset($c['error'])) {
							$original['name'] = $c['original_name'];
							//$original['path'] = Utils::env('API_URL').API_VERSION_URL.API_IMAGE_DOWNLOAD_URL;
							$original['path'] = WEBAPP_DOWNLOAD_URL;
							$original_thumb['name'] = $c['original_thumb_name'];
							//$original_thumb['path'] = Utils::env('API_URL').API_VERSION_URL.API_IMAGE_DOWNLOAD_URL;
							$original_thumb['path'] = WEBAPP_DOWNLOAD_URL;

							$url = Utils::env('API_URL').API_VERSION_URL.API_IMAGE_DOWNLOAD_URL.$original['name'];							
							$original_size = getimagesize($url);
							
							$url = Utils::env('API_URL').API_VERSION_URL.API_IMAGE_DOWNLOAD_URL.$original_thumb['name'];
							$original_thumb_size = getimagesize($url);

							if($original_thumb_size[0] > 0)
								$w_ratio = (float) ($original_size[0] / $original_thumb_size[0]);
							else
								Logger::log_msg('Error: Division by zero original_thumb_size[0]');
						
							if($original_thumb_size[1] > 0)
								$h_ratio = (float) ($original_size[1] / $original_thumb_size[1]);
							else
								Logger::log_msg('Error: Division by zero original_thumb_size[1]');
						
							$images['frame_width'] = $original_thumb_size[0];
							$images['frame_height'] = $original_thumb_size[1];

							$this->set('frame_width', $images['frame_width']);
							$this->set('frame_height', $images['frame_height']);						

							$this->set('original',$original);
							$this->set('original_thumb',$original_thumb);

							$images['w_ratio'] = $w_ratio;
							$images['h_ratio'] = $h_ratio;

							$images['original'] = $original;
							$images['original_thumb'] = $original_thumb;

							$_SESSION['images'] = $images;

						} else {
							if(isset($_POST['simple_upload']) && $_POST['simple_upload'] == true) {
							  setcookie('error_msg', 'Error: File upload failed. Please try again.');
							  header("Location: /simple-upload");
						  }
						}

            if(isset($_POST['simple_upload'])) {
              header("Location: /select-red-eyes");
            } else {
              //Mac flash bug, has to return something or else uploadSuccess won't be called
              echo '&nbsp;';
					  }
					}
				}
			}
		} else {
				$this->set('title','Red iGone - Red eye selection');

				if(isset($_SESSION['images'])) {
					$images = $_SESSION['images'];
					
					$this->set('frame_width', $images['frame_width']);
					$this->set('frame_height', $images['frame_height']);
												
					if(isset($images['thumbs']) || isset($images['queue'])) {
						header("Location: /image-preview");
					}
				}	else {
					header("Location: /image-upload");
				}

				$this->set('original_thumb',$images['original_thumb']);	
		}
	}


	function preview() {		
		$this->set('title','Red iGone - Preview Image');
		$th = DEFAULT_THRESHOLD;
		$images = '';
		
		if(isset($_SESSION['images'])) {
			$images = $_SESSION['images'];
		} else {
			header("Location: /image-upload");
		}

		if (isset($_REQUEST['th'])) {
			$th = $_REQUEST['th'];
			$_SESSION['current_th'] = $th;
		} else if(isset($_SESSION['current_th'])) {
			$th = $_SESSION['current_th'];
		}

		if(isset($_SESSION['selections'])) {
			$selections = $_SESSION['selections'];
		} else {
			$selections = array();
		}

		if(isset($images['queue'][$th]) || isset($images['thumbs'])) {
			$this->set('original',$images['original']);
			$this->set('original_thumb',$images['original_thumb']);
			if(isset($images['thumbs'])) {
				ksort($images['thumbs']);
				$this->set('thumbs',$images['thumbs']);
			}
			$this->set('th', $th);
			$this->set('frame_width', $images['frame_width']);
			$this->set('frame_height', $images['frame_height']);

			$queue = isset($images['queue'][$th]) ? $images['queue'][$th] : '';
			$this->set('queue', $queue);

			if(isset($images['thumbs'][$th]) && isset($images['filtered'][$th])) {
				$this->set('current_thumb', $images['thumbs'][$th]['name']);
				$this->set('current_image', $images['filtered'][$th]);
			}
		} else if(isset($images['original'])){
			
				$original = $images['original'];
				$original_thumb = $images['original_thumb'];

				$this->set('original',$original);
				$this->set('original_thumb',$original_thumb);

				$original['th'] = $th;
				$this->set('th', $th);

				$extension = $this->getFileExtension($original['name']);
				$extension = !empty($extension) ? $extension : 'jpg';
				
				$rw = $images['w_ratio'];
				$rh = $images['h_ratio'];

				$url = Utils::env('API_URL').API_VERSION_URL.API_IMAGE_GENERATE_THUMB_URL;


				$post_fields = array();
				$post_fields['format'] = 'json';
				$post_fields['device_id'] = DEVICE_ID.'-'.$_SERVER['REMOTE_ADDR'];
				$post_fields['name'] = $original['name'];
				$post_fields['threshold'] = $original['th'];
				$post_fields['extension'] = $extension;
				$post_fields['resize'] = DEFAULT_RESIZE;
				
				if(isset($_SESSION['selections'])) {
					$selections = $_SESSION['selections'];
					$i = 0;	
					if(!empty($selections)) {
						foreach($selections as $s) {
							$s['x1'] = (int)($s['x1'] * $rw);
							$s['y1'] = (int)($s['y1'] * $rh);					
							$s['x2'] = (int)($s['x2'] * $rw);
							$s['y2'] = (int)($s['y2'] * $rh);

							$comma_separated = implode(",", $s);
							$post_fields['selection'.$i] = 'rect,'.$comma_separated;
							$i++;
						}
						$num_selections = $i;
					}
				} else {
					$num_selections = 0;
				}
				
				$post_fields['num_selections'] = $num_selections;

				$c = curl($url, $post_fields)->get_json();

//				$this->doNotRenderHeader = 1;
//				$this->render = 0;			
//				print_r($c);exit;

				if(!isset($c['error'])) {
					$this->set('queue', $c['queue_id']);
					$images['queue'][$th] = $c['queue_id'];

					$images['original'] = $original;

					$this->set('frame_width', $images['frame_width']);
					$this->set('frame_height', $images['frame_height']);

					$_SESSION['images'] = $images;
					$_SESSION['current_th'] = $th;
				} else {
					Logger::log_msg('Error: preview() Failed to add job to image queue');	
				}
		} else {
			header("Location: /image-upload");
		}
	}
	
	function thumb() {
		$this->doNotRenderHeader = 1;
		$th = DEFAULT_THRESHOLD;
		if(isset($_SESSION['current_th'])) {
			$th = $_SESSION['current_th'];
		}		
		$this->set('th', $th);
					
		if(isset($_SESSION['images'])) {
			$images = $_SESSION['images'];
			if(isset($images['thumbs'])) {
				ksort($images['thumbs']);
				$this->set('thumbs',$images['thumbs']);
			}
		}
	}
	
	function get_status() {
		$id = intval($_REQUEST['id']);
		$this->doNotRenderHeader = 1;
		$this->render = 0;
				
		if(!empty($id)) {
			$format = 'json';
			$url = Utils::env('API_URL').API_VERSION_URL.API_IMAGE_STATUS_URL.$id.'&format='.$format;
			$c = curl($url)->get_json();
		
			if(isset($c['processed_image_th']) && isset($c['processed_image'])) {

				if(isset($_SESSION['images'])) {
					$images = $_SESSION['images'];
				}

				if(isset($images['thumbs'])) {
					$thumbs = $images['thumbs'];
				}

				$th = $c['processed_image_th'];

				$thumbs[$th]['name'] = 'thumb_'.$c['processed_image'];
				$thumbs[$th]['path'] = WEBAPP_DOWNLOAD_URL;
				$images['thumbs'] = $thumbs;
				$images['filtered'][$th] = $c['processed_image'];

				if(isset($images['queue'])) {
					unset($images['queue']);
				}			

				$_SESSION['images'] = $images;
				$_SESSION['current_th'] = $th;

				echo $c['processed_image'];
			} else if (isset($c['error'])) {
				Logger::log_msg('Error: get_status() failed to retrieve image status');	
			} else if (isset($c['status'])) {
				echo $c['status'];
			}		
		
		}
	}

	function generate_thumb() {
		$this->doNotRenderHeader = 1;
		$this->render = 0;

		$steps = $this->get_thumb_steps();
		$th = isset($_REQUEST['th']) ? $_REQUEST['th'] : 50;
		
		if(in_array($th, $steps)) {
			$all_th = array();
			$images = $_SESSION['images'];

			if(!empty($images['queue'])) {
				header('Cache-Control: no-cache, must-revalidate');
				header('Content-type: application/json');
				$response['status'] = 'busy';
				echo json_encode($response);
				exit;			
			}
			
			$filtered = $images['thumbs'];
			if(!empty($filtered)) {
				$all_th = array_keys($filtered);
			}		

			if(!in_array($th, $all_th)) {
				$original = $images['original'];
				$original['th'] = $th;

				$extension = $this->getFileExtension($original['name']);
				$extension = !empty($extension) ? $extension : 'jpg';
				
				$rw = $images['w_ratio'];
				$rh = $images['h_ratio'];

				$url = Utils::env('API_URL').API_VERSION_URL.API_IMAGE_GENERATE_THUMB_URL;

				$post_fields = array();
				$post_fields['format'] = 'json';
				$post_fields['device_id'] = DEVICE_ID.'-'.$_SERVER['REMOTE_ADDR'];
				$post_fields['name'] = $original['name'];
				$post_fields['threshold'] = $original['th'];
				$post_fields['extension'] = $extension;
				$post_fields['resize'] = DEFAULT_RESIZE;
				
				
				if(isset($_SESSION['selections'])) {
					$selections = $_SESSION['selections'];
					$i = 0;	
					if(!empty($selections)) {
						foreach($selections as $s) {
							$s['x1'] = (int)($s['x1'] * $rw);
							$s['y1'] = (int)($s['y1'] * $rh);					
							$s['x2'] = (int)($s['x2'] * $rw);
							$s['y2'] = (int)($s['y2'] * $rh);

							$comma_separated = implode(",", $s);
							$post_fields['selection'.$i] = 'rect,'.$comma_separated;
							$i++;
						}
						$num_selections = $i;
					}
				} else {
					$num_selections = 0;
				}
				
				$post_fields['num_selections'] = $num_selections;
				
				$c = curl($url, $post_fields)->get_json();
				
				if(!isset($c['error'])) {
					$images['queue'][$th] = $c['queue_id'];

					$_SESSION['current_th'] = $th;
					$_SESSION['images'] = $images;
					$response['status'] = 'queued';
					$response['queue_id'] = $c['queue_id'];
					header('Cache-Control: no-cache, must-revalidate');
					header('Content-type: application/json');
					echo json_encode($response);
					exit;
				} else {
					Logger::log_msg('Error: generate_thumb() failed to add job to image queue');	
				}
			}
			$_SESSION['current_th'] = $th;
			header('Cache-Control: no-cache, must-revalidate');
			header('Content-type: application/json');
			$response['status'] = 'done';
			$response['th'] = $th;
			$response['thumb'] = $images['thumbs'][$th]['name'];
			$response['name'] = $images['filtered'][$th];
			$response['path'] = $images['thumbs'][$th]['path'];
			echo json_encode($response);
			exit;
			
		} else {
			Logger::log_msg('Error: generate_thumb() requested threshold is not among allowed values');
			header('Cache-Control: no-cache, must-revalidate');
			header('Content-type: application/json');
			$response['status'] = 'error';
			echo json_encode($response);
			exit;
		}
	}

	function download() {

	$img = isset($_REQUEST['img']) ? $_REQUEST['img'] : '';

		if(!empty($img)) {
			$this->doNotRenderHeader = 1;
			$this->render = 0;
			$url = Utils::env('API_URL').API_VERSION_URL.API_IMAGE_DOWNLOAD_URL.$img;

			$c = curl($url)->get_data();
			echo $c;
		}	
	}

	function email_form() {
		$this->renderSpecialHeader = 1;
		$this->doNotRenderHeader = 1;
		
		if(isset($_REQUEST['image']) && !empty($_REQUEST['image'])) {
			$img = $_REQUEST['image'];
			$this->set('image', $img);
			$this->set('thumb', WEBAPP_DOWNLOAD_URL.'thumb_'.$img);
		} else {
			$this->set('error', true);
		}
	}

	function send_email() {

		if(file_exists(BASE_DIR.'/library/phpmailer.class.php')) {
			require_once(BASE_DIR.'/library/phpmailer.class.php');
		} else {
			Logger::log_msg('Error: send_email() failed to include phpmailer class');
			$this->set('sent', false);
			exit;
		}

		if(file_exists(BASE_DIR.'/library/smtp.class.php')) {
			require_once(BASE_DIR.'/library/smtp.class.php');
		} else {
			Logger::log_msg('Error: send_email() failed to include smtp class');
			$this->set('sent', false);
			exit;
		}

		$this->renderSpecialHeader = 1;
		$this->doNotRenderHeader = 1;
		
		$required = array('to_email', 'email_subject', 'email_message');
		$valid_form = true;
		foreach($required as $key=>$value) {
			if(!isset($_REQUEST[$value]) || empty($_REQUEST[$value])) {
				$valid_form = false;
			}
		}
		
		if($valid_form != true && (!isset($_REQUEST['filename']) || !file_exists(Utils::env('API_URL').API_VERSION_URL.API_IMAGE_DOWNLOAD_URL.$_REQUEST['filename']))) {
			$this->set('sent', false);
			exit;
		}
		
		if($valid_form) {
			$to = $_REQUEST['to_email'];
			$subject = strip_tags($_REQUEST['email_subject']);
			$message = strip_tags($_REQUEST['email_message']);
			$filename = $_REQUEST['filename'];
			
			try {
				$mail             = new PHPMailer(true);
				$body             = $message;
				$body             = str_replace('\\','',$body);
				$mail->IsSMTP(); // telling the class to use SMTP
				$mail->Host       = MAIL_HOST;						// SMTP server
				//$mail->SMTPDebug  = 0;                  // enables SMTP debug information (for testing)
				                                          // 1 = errors and messages
				                                          // 2 = messages only
				$mail->SMTPAuth   = true;                 // enable SMTP authentication
				$mail->SMTPSecure = "ssl";                // sets the prefix to the servier
				$mail->Host       = MAIL_SERVER;      		// sets GMAIL as the SMTP server
				$mail->Port       = MAIL_SERVER_PORT;     // set the SMTP port for the GMAIL server
				$mail->Username   = MAIL_USERNAME;				// GMAIL username
				$mail->Password   = MAIL_PASSWORD;        // GMAIL password

				$mail->SetFrom(MAIL_FROM, MAIL_FROM_NAME);
				$mail->AddReplyTo(MAIL_FROM);
				$mail->Subject    = $subject;
				$mail->AltBody    = "\r\n".$body."\r\n";								// optional, text body
				$mail->MsgHTML($body)."\r\n";
				$address = $to;
				$mail->AddAddress($address, $to);

				//$mail->AddAttachment(Utils::env('API_URL').API_VERSION_URL.API_IMAGE_DOWNLOAD_URL.$filename); // attachment
				//$mail->AddAttachment('http://'.$_SERVER['HTTP_HOST'].WEBAPP_DOWNLOAD_URL.$filename);
				$mail->AddAttachment('/export/uploads/'.$filename);
				
				$mail->Send();
				$this->set('sent', true);
			} catch (phpmailerException $e) {
				Logger::log_msg('Error: send_email() exception message '.$e->errorMessage());
				$this->set('sent', false);
			} catch (Exception $e) {
				Logger::log_msg('Error: send_email() exception message '.$e->getMessage());
				$this->set('sent', false);
			}
		}	else {
			$this->set('form_error', true);
		}
	}


  /**
	 *	@param filename
	 *	@return extension
	 */
	protected function getFileExtension($filename) {		
    $extension = explode('.', $filename);
		if(count($extension) > 0) {
	    $n = count($extension)-1;
	    $extension = $extension[$n];
	    return $extension;
		} else {
			return '';
		}
	}


	/**
	 * @param ['path'] ['name'] ['tmp_name']
	 * @return ['mime_type'] ['error']	
	 */	
	
	protected function identify_mime_type($fileInfo) {
		$original = isset($fileInfo['tmp_name']) ? $fileInfo['tmp_name'] : BASE_DIR.$fileInfo['path'].$fileInfo['name'];
		exec(IDENTIFY_PATH."identify -format %m $original", $output, $status);
		
		if($status == 0) {
			empty($output[0]) ? $data['error'] = "$original is not an image" : $data['mime_type'] = $this->get_php_mime_type($output[0]);
		}	else {
			Logger::log_msg('Error: identify_mime_type() Failed to identify '.$original.' Exited with status code '.$status);
			$data['error'] = true;
		}
		
		return $data;
	}
	
	protected function get_thumb_steps() {
		return array(0, 25, 50, 75, 100);
	}
	
	protected function get_php_mime_type($mimetype) {
		$mapping = array(
			'png'		=>	'image/png',
			'jpg'		=>	'image/jpeg',
			'jpeg'	=>	'image/jpeg',
			'bmp'		=>	'image/bmp',
			'gif'		=>	'image/gif',
			'tif'		=>	'image/tiff',
		);
		
		return array_key_exists(strtolower($mimetype), $mapping) ? $mapping[strtolower($mimetype)] : '';
	}

  protected function file_upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension';
        default:
            return 'Unknown upload error';
    }
  }	
}