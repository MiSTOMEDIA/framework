<?php

// Define basic constants
define('DS', DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', realpath (getcwd()));
define('PACKAGES_PATH', PUBLIC_PATH.DS.'..'.DS.'packages'.DS);
define('CONFIG_PATH', PUBLIC_PATH.DS.'..'.DS.'packages'.DS.'core'.DS.'config'.DS);

// Require the necessary files
require PACKAGES_PATH.'core/autoloader.php';
require CONFIG_PATH.'routes.php';

// Display the app
echo Core\Dispatcher::run();