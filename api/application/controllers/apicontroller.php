<?php
	if (file_exists(BASE_DIR.'/application/controllers/api/'.API_VERSION.'.php')) {
		require_once('api/'.API_VERSION.'.php');
	}