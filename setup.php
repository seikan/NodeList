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

// Common functions
require_once INCLUDES . 'functions.php';

// Session
require_once LIBRARIES . 'class.Session.php';
$session = new Session();

include_once INCLUDES . 'header.php';

$errors = array();

$username = (isset($_POST['username'])) ? $_POST['username'] : '';
$password = (isset($_POST['password'])) ? $_POST['password'] : '';

if(isset($_POST['username'])){
	if(!preg_match('/^[a-z0-9]{6,}$/', $username))
		$errors[] = 'Please enter at least 6 characters alphanumeric as username.';

	if(!preg_match('/^.{6,}$/', $password))
		$errors[] = 'Please enter at least 6 characters in length for password.';

	if(!is_writable(ROOT . 'configuration.php'))
		$errors[] = 'No permission to write to "configuration.php".';

	if(!is_writable(DATABASES))
		$errors[] = 'No permission to write to "' . DATABASES . '".';

	$key = randomCode();

	file_put_contents(ROOT . 'configuration.php', implode("\n", array(
		'<?php',
		'// define(\'URL_REWRITE\', 1);',
		'',
		'$config = array(',
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
		'			\'rate\'	=> 0.78,',
		'		],',
		'		\'gbp\'	=> [',
		'			\'label\'	=> \'GBP\',',
		'			\'rate\'	=> 1.51,',
		'		],',
		'		\'eur\'	=> [',
		'			\'label\'	=> \'EUR\',',
		'			\'rate\'	=> 1.06,',
		'		],',
		'	],',
		');',
		'?>',
	)));

	file_put_contents(DATABASES . 'machine_' . $key . '.db', implode("\n", array(
		'machine_id(int);provider_id(int);is_active(bool);label(str);vm_type(str);ip_address(str);is_nat(bool);city_name(str);country_code(str);total_ram(int);ram_unit(str);total_swap(int);swap_unit(str);total_disk_space(int);disk_space_unit(str);hdd_type(str);total_bandwidth(int);bandwidth_unit(str);price(str);currency_code(str);billing_cycle(str);due_date(str);notes(str)',
		'',
	)));

	file_put_contents(DATABASES . 'provider_' . $key . '.db', implode("\n", array(
		'provider_id(int);name(str);website(str);control_panel(str);cp_url(str)',
		'',
	)));

	die(header('Location: index.php'));
}

echo '
		<div class="container">
			<div class="row">
				<div class="col-lg-6">
					<h1>Setup</h1>

					' . ((!empty($errors)) ? '<div class="alert alert-danger">' . implode('<br />', $errors) . '</div>' : '' ) . '

					<form method="post">
						<div class="form-group">
							<label for="username">Username</label>
							<input type="text" class="form-control" id="username" name="username" aria-describedby="usernameHelp" placeholder="Username" value="' . $username . '">
							<small id="usernameHelp" class="form-text text-muted">Administrator username.</small>
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" class="form-control" id="password" name="password" aria-describedby="passwordHelp" placeholder="Password" value="' . $password . '">
							<small id="passwordHelp" class="form-text text-muted">Administrator password.</small>
						</div>

						<button type="submit" class="btn btn-primary">Submit</button>
					</form>
				</div>
			</div>
		</div>';

include_once INCLUDES . 'footer.php';
?>