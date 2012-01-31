<?php	
/*
application - application specific code
config - database/server configuration
private/pgsql - database schemas and backups
library - framework code
public - application specific js/css/images
scripts - command-line utilities
*/

// /var/www/rig
define('BASE_DIR', dirname(dirname(__FILE__)));
require_once (BASE_DIR.'/library/bootstrap.php');
