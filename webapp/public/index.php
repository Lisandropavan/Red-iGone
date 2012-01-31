<?php	
/*
application - application specific code
config - database/server configuration
db - database backups
library - framework code
public - application specific js/css/images
scripts - command-line utilities

Coding Conventions

1. Database tables will always be lowercase and plural e.g. items, cars
2. Models will always be singular and first letter capital e.g. Item, Car
3. Controllers will always have “Controller” appended to them. e.g. ItemsController, CarsController
4. Views will have plural name followed by action name as the file. e.g. items/view.php, cars/buy.php

*/

// /var/www/rig
define('BASE_DIR', dirname(dirname(__FILE__)));
require_once (BASE_DIR.'/library/bootstrap.php');