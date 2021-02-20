<?php

// Preset PHP settings
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');

// Define this as parent file
define('INDEX', 1);

// Define root directory
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__ . DS);

// Define folders directory
define('DATABASES', ROOT . 'databases' . DS);
define('INCLUDES', ROOT . 'includes' . DS);
define('LIBRARIES', ROOT . 'libraries' . DS);
define('LOGS', ROOT . 'logs' . DS);
define('PAGES', ROOT . 'pages' . DS);

// Common functions
require INCLUDES . 'functions.php';

// Autoload libraries
require LIBRARIES . 'autoload.php';

if (!file_exists(ROOT . 'configuration.php')) {
	die(displayMessage('Missing Configuration File', 'Please create an empty file <strong>configuration.php</strong> under the "' . __DIR__ . '".'));
}

// Configuration
require_once ROOT . 'configuration.php';

if (!isset($config) && file_exists(ROOT . 'setup.php')) {
	die(header('Location: setup.php'));
}

if (isset($config) && file_exists(ROOT . 'setup.php')) {
	die(displayMessage('Security Alert', 'Please delete <strong>setup.php</strong> under the "' . __DIR__ . '".'));
}

// Error Handler
new \Error\Handler(function ($file, $line, $error) {
	// Log error
	file_put_contents(LOGS . 'error.log', json_encode([
		'date'  => date('Y-m-d H:i:s'),
		'error' => $error,
		'file'  => $file . ': ' . $line,
		'ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
		'ua'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
		'url'   => $_SERVER['REQUEST_URI'] ?? ($_SERVER['SCRIPT_NAME'] ?? '-'),
	]) . "\n", FILE_APPEND);

	ob_clean();

	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
	include ROOT . '50x.html';
	ob_end_flush();
	die;
});

// Initialize session
$GLOBALS['SESSION'] = new \Session\Session();

// Initialize Page Request
$GLOBALS['PAGE'] = new \Page\Request();

// Get requested page
$_PAGE = ($GLOBALS['PAGE']->request('_PAGE', PAGE\REQUEST::GET)) ? $GLOBALS['PAGE']->request('_PAGE', PAGE\REQUEST::GET) : 'machine';

// Validate pages to prevent file inclusion vulnerability
$pages = [];

if ($handle = opendir(PAGES)) {
	while (($entry = readdir($handle)) !== false) {
		if (substr($entry, -4) != '.php') {
			continue;
		}

		$pages[str_replace('.php', '', $entry)] = true;
	}
	closedir($handle);
}

// Display requested page
require_once PAGES . ((isset($pages[$_PAGE])) ? $_PAGE : '404') . '.php';
