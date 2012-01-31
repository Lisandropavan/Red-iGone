var swfu;

var upload = {
	is_empty : function(val) {
		if(val == '')
			return true;
		else
			return false;
	},

	init : function() {

		var session_id = $('#session_id').text();

		function fileDialogComplete(file) {
			this.startUpload(file.id);
		}

		function uploadStart(file) {
			//console.log('starting upload..');
			$('#upload_error').hide();
			return true;
		}

		function uploadComplete(file) {
			//console.log('upload complete..');
			return true;
		}

		function fileQueueError(file, error_code, receivedResponse) {
			//use this to see all reponse codes
			//console.log(SWFUpload.QUEUE_ERROR);
			
			var error_msg;
			switch(error_code)
			{
			case -110:
			  error_msg = 'Error: The maxmium file size allowed is 2MB';
			  break;
			case -120:
					  error_msg = 'Error: The file is empty (0 bytes)';
					break;
			case -130:
				  error_msg = 'Error: Allowed filetypes are png, jpeg, gif, tif and bmp';
				break;			
			default:
			  error_msg = 'An error occured while uploading your file';
			}
			$('#upload_error').text(error_msg);
			$('#upload_error').show();
			return true;
		}

		function uploadError(file, error_code, receivedResponse) {
			//use this to see all reponse codes
			//console.log(SWFUpload.UPLOAD_ERROR);
			
			var error_msg;
			switch(error_code)
			{
			case -240:
			  error_msg = 'Error: The maxmium file size allowed is 2MB';
			  break;
			default:
			  error_msg = 'An error occured while uploading your file';
			}
			$('#upload_error').text(error_msg);
			$('#upload_error').show();
			return true;
		}

		function uploadSuccess(file, server_data, receivedResponse) {
			window.location.href = '/select-red-eyes';
			return true;
		}

		var settings = {
			//Prevent caching
			prevent_swf_caching : true,			
			
			// Backend Settings
			flash_url : "/public/swf/swfupload.swf",		
			upload_url: "/select-red-eyes",
			//upload_url: "/image-upload",
			file_post_name: "filename",
			post_params: {"PHPSESSID" : session_id},

			// File Upload Settings
			file_size_limit : "2048",	// default KB, accepts B, KB, MB, GB
			file_types : "*.jpeg;*.jpg;*.gif;*.png;*.tif;*.bmp;",
			file_types_description : "Images",
			file_upload_limit : "1",
			file_queue_limit : "1",		

			// Event Handler Settings
			// controls how the website reacts to the SWFUpload events.
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			//upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			//upload_start_handler : uploadStart,
			upload_success_handler : uploadSuccess,
			//upload_complete_handler : uploadComplete,

			// Button Settings
			button_image_url : "/public/img/images/add_file.png",
			button_placeholder_id : "upload_img",
			button_width: 128,
			button_height: 128,
			//button_text : '<div id="button_text">(2 MB Max)</div>',
			//button_text_style : '#button_text { text-align: bottom; font-family: Helvetica, Arial, sans-serif; font-size: 12pt; }',
			//button_text_top_padding: 0,
			//button_text_left_padding: 18,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,

			//custom_settings : {
			//	upload_target : "divFileProgressContainer"
			//},

			// Debug Settings			
			debug: false
			
		};

		swfu = new SWFUpload(settings);

	}
		
};

rig.ready('#upload', function() {
	upload.init();
});
