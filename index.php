<?php
// Preset PHP settings
error_reporting(E_ALL);
ini_set('display_errors', 0);
date_default_timezone_set('UTC');

// Define this as parent file
define('INDEX', 1);

// Define root directory
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__) . DS);

// Define folders directory
define('DATABASES', ROOT . 'databases' . DS);
define('INCLUDES', ROOT . 'includes' . DS);
define('LIBRARIES', ROOT . 'libraries' . DS);
define('LOGS', ROOT . 'logs' . DS);
define('PAGES', ROOT . 'pages' . DS);

if(!file_exists(ROOT . 'configuration.php'))
	die('PLEASE CREATE AN EMPTY FILE "configuration.php".');

// Configuration
require_once ROOT . 'configuration.php';

if(!isset($config) && file_exists(ROOT . 'setup.php'))
	die(header('Location: setup.php'));

if(isset($config) && file_exists(ROOT . 'setup.php'))
	die('PLEASE DELETE "setup.php" TO CONTINUE.');

// Add error handler
require_once INCLUDES . 'error-handler.php';

// Common functions
require_once INCLUDES . 'functions.php';

// Session
require_once LIBRARIES . 'class.Session.php';
$session = new Session();

// Clean up GET and POST requests to prevent XSS
$_GET = strips($_GET);

// Convert POST into SESSION for Post/Redirect/Get pattern (PRG)
if(!empty($_POST)){
	$session->set('_POST', strips($_POST));
    die(header('Location: ' . getPageURL()));
}

if($session->get('_POST')){
    $_POST = $session->get('_POST');
    $session->set('_POST', NULL);
}

// Database
require_once LIBRARIES . 'class.SimpleDB.php';

// Get requested page
$_PAGE = (isset($_GET['page'])) ? $_GET['page'] : 'machine';

// Validate pages to prevent file inclusion vulnerability
$pages = array();

if($handle = opendir(PAGES)){
	while(($entry = readdir($handle)) !== FALSE){
		if(substr($entry, -4) != '.php')
			continue;

		$pages[str_replace('.php', '', $entry)] = TRUE;
    }
    closedir($handle);
}

// Display requested page
require_once PAGES . ((isset($pages[$_PAGE])) ? $_PAGE : '404') . '.php';
?>