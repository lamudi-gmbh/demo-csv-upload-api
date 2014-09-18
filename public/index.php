<?php
ini_set('display_errors', true);
error_reporting(E_ALL);
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/../vendor/zendframework/zendframework1/library'),
            get_include_path(),
        )));

// Including Rocket libraries
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/../../vendor'),
            get_include_path(),
        )));

// Including modules libraries
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/../../bob/application/vendor/modules'),
            get_include_path(),
        )));


/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

require_once(APPLICATION_PATH . '/../../bob/application/local/init_autoloader_bob_local.php');
$application->bootstrap()
            ->run();