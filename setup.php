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

if (file_exists(ROOT . 'configuration.php')) {
	require ROOT . 'configuration.php';
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

include INCLUDES . 'header.php';

$response = '';
$errors = [];

if (!is_writable(ROOT . 'configuration.php')) {
	$errors[] = '<u>configuration.php</u> file is not found or not writable.';
} elseif (isset($config['username'])) {
	$errors[] = 'Existing configuration is found. Please delete <u>configuration.php</u> to continue.';
}

$username = strtolower($GLOBALS['PAGE']->request('username', \PAGE\REQUEST::POST));
$password = $GLOBALS['PAGE']->request('password', \PAGE\REQUEST::POST);
$confirmPassword = $GLOBALS['PAGE']->request('confirmPassword', \PAGE\REQUEST::POST);

if (empty($errors) && $GLOBALS['PAGE']->isPost()) {
	if (!preg_match('/^[a-z0-9]{6,}$/', $username)) {
		$errors['username'] = 'Username must at least 6 characters alphanumeric.';
	}

	if (!preg_match('/^.{6,}$/', $password)) {
		$errors['password'] = 'Password must at least 6 characters in length.';
	} elseif (strcmp($password, $confirmPassword) != 0) {
		$errors['confirmPassword'] = 'Password does not match.';
	}

	if (!is_writable(ROOT . 'configuration.php')) {
		$errors[] = 'No permission to write to "configuration.php".';
	}

	if (!is_writable(DATABASES)) {
		$errors[] = 'No permission to write to "' . DATABASES . '".';
	}

	if (empty($errors)) {
		$key = randomCode(16);

		file_put_contents(ROOT . 'configuration.php', implode("\n", [
			'<?php',
			'// define(\'URL_REWRITE\', 1);',
			'',
			'$config = [',
			'	\'username\'		=> \'' . $username . '\',',
			'	\'password\'		=> \'' . addslashes($password) . '\',',
			'	\'key\'			=> \'' . $key . '\',',
			'',
			'	\'currencies\'	=> [',
			'		\'usd\'	=> [',
			'			\'label\'	=> \'USD\',',
			'			\'rate\'	=> 1,',
			'		],',
			'		\'cad\'	=> [',
			'			\'label\'	=> \'CAD\',',
			'			\'rate\'	=> 0.79,',
			'		],',
			'		\'gbp\'	=> [',
			'			\'label\'	=> \'GBP\',',
			'			\'rate\'	=> 1.39,',
			'		],',
			'		\'eur\'	=> [',
			'			\'label\'	=> \'EUR\',',
			'			\'rate\'	=> 1.21,',
			'		],',
			'	],',
			'];',
			'?>',
		]));

		file_put_contents(DATABASES . 'machine_' . $key . '.db', implode("\n", [
			'machine_id(int);provider_id(int);is_active(bool);label(str);vm_type(str);ip_address(str);is_nat(bool);city_name(str);country_code(str);total_ram(int);ram_unit(str);total_swap(int);swap_unit(str);total_disk_space(int);disk_space_unit(str);hdd_type(str);total_bandwidth(int);bandwidth_unit(str);price(str);currency_code(str);billing_cycle(str);due_date(str);notes(str)',
			'',
		]));

		file_put_contents(DATABASES . 'provider_' . $key . '.db', implode("\n", [
			'provider_id(int);name(str);website(str);control_panel(str);cp_url(str)',
			'',
		]));

		die(header('Location: index.php'));
	}
}

if (!empty($errors)) {
	foreach ($errors as $error) {
		$response .= '
		<div class="alert alert-danger alert-dismissible">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<span><i class="fa fa-exclamation-triangle"></i> ' . $error . '</span>
		</div>';
	}
}

echo '
	<main class="flex-shrink-0">
		<div class="container pt-5">
			<div class="row mt-5">
				<div class="col-5">
					<h3>Setup Administrator Account</h3>

					' . $response . '

					<form method="post">
						<div class="form-group">
							<label for="username">Username</label>
							<input type="text" class="form-control' . ((isset($errors['username'])) ? ' is-invalid' : '') . '" id="username" name="username" placeholder="Username" value="' . $username . '" required>
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" class="form-control' . ((isset($errors['password'])) ? ' is-invalid' : '') . '" id="password" name="password" placeholder="Password" value="" required>
						</div>
						<div class="form-group">
							<label for="confirmPassword">Confirm Password</label>
							<input type="password" class="form-control' . ((isset($errors['confirmPassword'])) ? ' is-invalid' : '') . '" id="confirmPassword" name="confirmPassword" placeholder="Password Again" value="" required>
						</div>

						<button type="submit" class="btn btn-primary">Save Changes</button>
					</form>
				</div>
			</div>
		</div>
	</main>';

include INCLUDES . 'footer.php';
