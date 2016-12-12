<?php
defined('INDEX') or die('Access is denied.');

header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');

require_once INCLUDES . 'header.php';
?>
		<div class="container">
			<div class="row">
				<div class="col-lg-12 error-page">
					<h2>File Not Found</h2>

					<p>We're sorry, but the page you were looking for doesn't exist.</p>

					<div class="text-center"><a href="./" class="btn btn-default">Back To Home</a></div>
				</div>
			</div>
		</div>
<?php
require_once INCLUDES . 'footer.php';
?>