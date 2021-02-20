<?php

defined('INDEX') or die('Access is denied.');

if (!$GLOBALS['SESSION']->get('username')) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Session expired. Please sign in again to continue.</span></div>',
		'name'     => 'label',
	]));
}

$providerId = $GLOBALS['PAGE']->request('id', \PAGE\Request::POST);
$name = $GLOBALS['PAGE']->request('name', \PAGE\Request::POST);
$website = trim($GLOBALS['PAGE']->request('website', \PAGE\Request::POST), '/');
$controlPanel = $GLOBALS['PAGE']->request('controlPanel', \PAGE\Request::POST);
$cpUrl = $GLOBALS['PAGE']->request('cpUrl', \PAGE\Request::POST);

if (empty($name)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert provider name.</span></div>',
		'name'     => 'name',
	]));
}

if (mb_strlen($name) > 100) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Provider name exceed 100 characters in length.</span></div>',
		'name'     => 'name',
	]));
}

if (!filter_var($website, FILTER_VALIDATE_URL)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid provider website.</span></div>',
		'name'     => 'website',
	]));
}

if (empty($controlPanel)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert control panel name.</span></div>',
		'name'     => 'controlPanel',
	]));
}

if (mb_strlen($controlPanel) > 50) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Control panel name exceed 50 characters in length.</span></div>',
		'name'     => 'controlPanel',
	]));
}

if (!filter_var($cpUrl, FILTER_VALIDATE_URL)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid control panel URL.</span></div>',
		'name'     => 'cpUrl',
	]));
}

$provider = new \SimpleDB\Database(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');

if (!$providerId) {
	$provider->insert([
		'name'          => html_entity_decode($name),
		'website'       => $website,
		'control_panel' => html_entity_decode($controlPanel),
		'cp_url'        => $cpUrl,
	]);

	$GLOBALS['SESSION']->set('response', '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-check-circle"></i> "' . $name . '" has been added.</span></div>');
} else {
	$provider->update('provider_id', '=' . $providerId, [
		'name'          => html_entity_decode($name),
		'website'       => $website,
		'control_panel' => html_entity_decode($controlPanel),
		'cp_url'        => $cpUrl,
	]);

	$GLOBALS['SESSION']->set('response', '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-check-circle"></i> "' . $name . '" has been updated.</span></div>');
}

die(json_encode([
	'status' => 'OK',
]));
