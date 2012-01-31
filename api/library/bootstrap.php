<?php

	//Add files to be included on every page here
	require_once (BASE_DIR.'/config/config.php');
	require_once (BASE_DIR.'/library/cache.class.php');
	require_once (BASE_DIR.'/library/db.class.php');
	require_once (BASE_DIR.'/library/logger.class.php');
	require_once (BASE_DIR.'/library/page.class.php');
	require_once (BASE_DIR.'/library/session.class.php');
	require_once (BASE_DIR.'/library/utils.class.php');
	require_once (BASE_DIR.'/library/validator.class.php');
	require_once (BASE_DIR.'/library/xmlbuilder.class.php');

	//GOTCHA: Make sure to include the dispatcher last
	require_once (BASE_DIR.'/library/dispatcher.php');
