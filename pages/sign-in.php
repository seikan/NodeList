<?php
defined('INDEX') or die('Access is denied.');

$response = '';
$errors = array();

if($session->get('response')){
	$response = $session->get('response');
	$session->set('response', NULL);
}

if(isset($_GET['action']) && $_GET['action'] == 'sign-out'){
	$session->set('username', NULL);
	setcookie('auth', NULL, -1);

	$session->set('response', '
	<div class="alert alert-info alert-dismissible">
		<span><i class="fa fa-exclamation-triangle"></i> You have been signed out.</span>
	</div>');

	die(header('Location: ' . getURL('sign-in')));
}

$return = (isset($_GET['return'])) ? $_GET['return'] : '';
$username = (isset($_POST['username'])) ? $_POST['username'] : '';
$password = (isset($_POST['password'])) ? $_POST['password'] : '';

if(isset($_POST['username'])){
	if($username == $config['username'] && $password == $config['password']){
		$session->set('username', $username);
		setcookie('auth', sha1($username . $password), strtotime('+1 year'));

		die(header('Location: ' . (($return) ? rawurldecode($return) : getURL('machine'))));
	}

	$errors['username'] = 'Invalid username or password.';
}

if(isset($_COOKIE['auth']) && $_COOKIE['auth'] == sha1($config['username'] . $config['password']))
	$session->set('username', $config['username']);

if($session->get('username'))
	die(header('Location: ' . getURL('machine')));

if(!empty($errors)){
	foreach($errors as $error){
		$response .= '
		<div class="alert alert-danger alert-dismissible">
			<span><i class="fa fa-exclamation-triangle"></i> ' . $error . '</span>
		</div>';
	}
}

require_once INCLUDES . 'header.php';
?>
		<div class="container">
			<div class="row">
				<div class="col-lg-4">
					<form action="<?php echo getURL('sign-in', (($return) ? array('return', rawurlencode($return)) : array())); ?>" method="post" role="form" class="form">
						<?php echo $response; ?>
						<div class="form-group">
							<label>Username</label>
							<input type="text" name="username" value="<?php echo $username; ?>" class="form-control" maxlength="20" autocorrect="off" autocapitalize="off">
						</div>
						<div class="form-group">
							<label>Password</label>
							<input type="password" name="password" class="form-control" maxlength="100" autocorrect="off" autocapitalize="off">
						</div>

						<button class="btn btn-primary"><i class="fa fa-sign-in"></i> Sign In</button>
					</form>
				</div>
			</div>
		</div>
<?php
require_once INCLUDES . 'footer.php';
?>