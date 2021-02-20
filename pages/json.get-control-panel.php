<?php

defined('INDEX') or die('Access is denied.');

if (!$GLOBALS['SESSION']->get('username')) {
	die(json_encode([]));
}

$provider = new \SimpleDB\Database(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');

$rows = $provider->select('control_panel', '*', 'control_panel');

if ($provider->affectedRows() == 0) {
	die(json_encode([]));
}

$tmp = [];

foreach ($rows as $row) {
	$tmp[$row['control_panel']] = true;
}

echo json_encode(array_keys($tmp));
