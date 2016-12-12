<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Node List</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="format-detection" content="telephone=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="./includes/css/style.css">

		<link rel="apple-touch-icon" href="./images/icon.png" />

		<!--[if lt IE 9]>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->

		<?php
		if(isset($css) && is_array($css))
			echo "<link href=\"" . implode("\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n\t\t<link href=\"", $css) . "\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n\n";

		if(isset($styles)){
			echo "\t\t" . implode("\n\t\t", array(
				'<style type="text/css">',
				'<!--',
				"\t" . $styles,
				'//-->',
				'</style>',
			)) . "\n\n";
		}
		?>
	</head>

	<body>
		<div class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<a href="./" class="navbar-brand">
						<span class="fa-stack fa-lg">
							<i class="fa fa-circle fa-stack-2x"></i>
							<i class="fa fa-rocket fa-stack-1x fa-inverse"></i>
						</span>
					</a>

					<button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>

				<div class="navbar-collapse collapse" id="navbar-main">
					<ul class="nav navbar-nav">
						<li<?php if($_PAGE == 'machine') echo ' class="active"'; ?>><a href="<?php echo getURL('machine'); ?>">Machines</a></li>
						<li<?php if($_PAGE == 'provider') echo ' class="active"'; ?>><a href="<?php echo getURL('provider'); ?>">Providers</a></li>
					</ul>

					<?php
					if($session->get('username'))
						echo '
						<ul class="nav navbar-nav navbar-right">
							<li><a href="' . getURL('sign-in', array(
								'action'	=> 'sign-out'
							)) . '"><i class="fa fa-sign-out"></i> Sign Out</a></li>
						</ul>';
					?>
				</div>
			</div>
		</div>