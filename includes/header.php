<!DOCTYPE html>
<html lang="en" class="h-100">
	<head>
		<meta charset="utf-8">
		<title>Node List</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="format-detection" content="telephone=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<link rel="shortcut icon" href="./favicon.ico">
		<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.3/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/4.5.3/flatly/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

		<?php
		if (isset($css) && is_array($css)) {
			echo '	<link href="' . implode("\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n\t\t<link href=\"", $css) . "\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n\n";
		}

		if (isset($styles)) {
			echo "\t\t" . implode("\n\t\t", [
				'<style type="text/css">',
				'<!--',
				"\t" . $styles,
				'//-->',
				'</style>',
			]) . "\n\n";
		}
		?>

		<link rel="stylesheet" href="./assets/css/style.css">
		<link rel="apple-touch-icon" href="./assets/img/icon.png" />
	</head>

	<body class="d-flex flex-column h-100">
		<nav class="navbar navbar-expand-lg navbar-dark bg-secondary fixed-top">
			<div class="container">
				<a class="navbar-brand" href="./"><img src="./assets/img/icon.png" width="32" height="32" align="absmiddle" title="Node List" alt="Node List"></a>
				<?php if (!preg_match('/setup\.php$/', $GLOBALS['PAGE']->getCurrentUrl()) && $GLOBALS['SESSION']->get('username')): ?>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarNav">
					<ul class="navbar-nav nav-pills nav-fill mr-auto">
						<li class="nav-item mr-3">
							<a class="nav-link<?php if ($_PAGE == 'machine'): echo ' active'; endif; ?>" aria-current="page" href="<?php echo getUrl('machine'); ?>"><i class="fa fa-server"></i> Machines</a>
						</li>
						<li class="nav-item">
							<a class="nav-link<?php if ($_PAGE == 'provider'): echo ' active'; endif; ?>" href="<?php echo getUrl('provider'); ?>"><i class="fa fa-users"></i> Providers</a>
						</li>
					</ul>

					<ul class="navbar-nav">
						<li><a href="<?php echo getUrl('sign-in', [
							'action' => 'sign-out',
						]); ?>" class="text-light"><i class="fa fa-sign-out"></i> Sign Out</a></li>
					</ul>
				</div>
				<?php endif; ?>
			</div>
		</nav>