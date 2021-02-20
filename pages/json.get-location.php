<?php

defined('INDEX') or die('Access is denied.');

if (!$GLOBALS['SESSION']->get('username')) {
	die(json_encode([]));
}

$machine = new \SimpleDB\Database(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');

$rows = $machine->select('city_name', '*', 'city_name');

if ($machine->affectedRows() == 0) {
	die(json_encode([]));
}

$tmp = [];

foreach ($rows as $row) {
	$tmp[$row['city_name']] = true;
}

echo json_encode(array_keys($tmp));
