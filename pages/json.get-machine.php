<?php

defined('INDEX') or die('Access is denied.');

if (!$GLOBALS['SESSION']->get('username')) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => 'Session expired. Please sign in again to continue.',
	]));
}

$machineId = $GLOBALS['PAGE']->request('id', \PAGE\Request::GET);

$machine = new \SimpleDB\Database(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');

$row = $machine->select('machine_id', '=' . $machineId);

if ($machine->affectedRows() == 0) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => 'Machine with specified ID is not found.',
	]));
}

echo json_encode(array_merge([
	'status' => 'OK',
], $row[0]));
