<?php
defined('INDEX') or die('Access is denied.');

$response = '';
$errors = [];

if ($GLOBALS['SESSION']->get('response')) {
	$response = $GLOBALS['SESSION']->get('response');
	$GLOBALS['SESSION']->set('response', null);
}

if ($GLOBALS['PAGE']->request('action', PAGE\REQUEST::GET) == 'sign-out') {
	$GLOBALS['SESSION']->set('username', null);
	setcookie('auth', null, -1);

	$GLOBALS['SESSION']->set('response', '
	<div class="alert alert-info alert-dismissible">
	  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
		<span><i class="fa fa-exclamation-triangle"></i> You have been signed out.</span>
	</div>');

	die(header('Location: ' . getUrl('sign-in')));
}

$return = $GLOBALS['PAGE']->request('return', PAGE\REQUEST::GET);
$username = $GLOBALS['PAGE']->request('username', PAGE\REQUEST::POST);
$password = $GLOBALS['PAGE']->request('password', PAGE\REQUEST::POST);

if ($GLOBALS['PAGE']->isPost()) {
	if (strcmp($username, $config['username']) == 0 && strcmp($password, $config['password']) == 0) {
		$GLOBALS['SESSION']->set('username', $username);
		setcookie('auth', sha1($username . $password), strtotime('+1 year'));

		die(header('Location: ' . (($return) ? rawurldecode($return) : getUrl('machine'))));
	}

	$errors['username'] = 'Invalid username or password.';
}

if (isset($_COOKIE['auth']) && $_COOKIE['auth'] == sha1($config['username'] . $config['password'])) {
	$GLOBALS['SESSION']->set('username', $config['username']);
}

if ($GLOBALS['SESSION']->get('username')) {
	die(header('Location: ' . getUrl('machine')));
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

include INCLUDES . 'header.php';
?>
	<main class="flex-shrink-0">
		<div class="container pt-5">
			<div class="row mt-5">
				<div class="col-lg-4 col-lg-offset-4">
					<h3>Sign In</h3>
					<form action="<?php echo getUrl('sign-in', (($return) ? ['return', rawurlencode($return)] : [])); ?>" method="post" role="form" class="form">
						<?php echo $response; ?>
						<div class="form-group">
							<label>Username</label>
							<input type="text" name="username" value="<?php echo $username; ?>" class="form-control" maxlength="20" autocorrect="off" autocapitalize="off" required>
						</div>
						<div class="form-group">
							<label>Password</label>
							<input type="password" name="password" class="form-control" maxlength="100" autocorrect="off" autocapitalize="off" required>
						</div>

						<button class="btn btn-primary"><i class="fa fa-sign-in"></i> Sign In</button>
					</form>
				</div>
			</div>
		</div>
	</main>
<?php
include INCLUDES . 'footer.php';
?>