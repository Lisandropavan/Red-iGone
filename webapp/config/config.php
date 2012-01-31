<?php

	define('DB_DATABASE', 'rig');
	define('DB_USER', 'rig');
	define('DB_PASSWORD', 'foobar');
	define('DB_HOST', 'localhost');

	define('MAX_UPLOAD_SIZE', 2097152);
	define('DEFAULT_THRESHOLD', 50);
	define('MIN_THRESHOLD', 0);
	define('MAX_THRESHOLD', 100);
	define('DEFAULT_RESIZE', "256x256");
	define('DEFAULT_DEVICE', "redigone.com");
	define('MAX_SELECTIONS', 20);

	define('MAX_UPLOAD_SIZE_IOS', 5242880);

	define('GIMP_PATH', "/usr/bin/");
	define('CONVERT_PATH', "/usr/bin/");
	define('COMPOSITE_PATH', "/usr/bin/");
	define('IDENTIFY_PATH', "/usr/bin/");

	define('SESSION_TTL', 7200);

	define('MAIL_HOST', 'mail.foobar.com');
	define('MAIL_SERVER', 'smtp.gmail.com');
	define('MAIL_SERVER_PORT', 465);
	define('MAIL_USERNAME', 'no-reply@foobar.com');
	define('MAIL_FROM', 'no-reply@foobar.com');
	define('MAIL_FROM_NAME', 'Red iGone');
	define('MAIL_PASSWORD', 'foopassword');

	define('API_VERSION_URL', "0.2/");
	define('API_IMAGE_UPLOAD_URL', "image-upload");
	define('API_IMAGE_DOWNLOAD_URL', "image-download?img=");
	define('API_IMAGE_STATUS_URL', "image-status?id=");
	define('API_IMAGE_GENERATE_THUMB_URL', "generate-thumb");
	
	define('WEBAPP_DOWNLOAD_URL', "/image-download?img=");
	
	define('DEVICE_ID', "redigone.com");
	define('DEVICE_NAME', "webapp");
	define('DEVICE_SYSTEM_NAME', "webapp");
	define('DEVICE_SYSTEM_VERSION', "1.0");
	define('DEVICE_MODEL', "webapp");
	define('DEVICE_LOCALIZED_MODEL', "webapp");
	define('APP_NAME', "webapp");
	define('APP_VERSION', "1.0");
