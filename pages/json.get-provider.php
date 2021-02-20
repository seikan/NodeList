<?php

defined('INDEX') or die('Access is denied.');

if (!$GLOBALS['SESSION']->get('username')) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => 'Session expired. Please sign in again to continue.',
	]));
}

$providerId = $GLOBALS['PAGE']->request('id', \PAGE\Request::GET);

$provider = new \SimpleDB\Database(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');

$row = $provider->select('provider_id', $providerId);

if ($provider->affectedRows() == 0) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => 'Provider with specified ID is not found.',
	]));
}

echo json_encode(array_merge([
	'status' => 'OK',
], $row[0]));
