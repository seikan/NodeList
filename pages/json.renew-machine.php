<?php

defined('INDEX') or die('Access is denied.');

if (!$GLOBALS['SESSION']->get('username')) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => 'Session expired. Please sign in again to continue.',
		'id'       => 'label',
	]));
}

$machineId = $GLOBALS['PAGE']->request('id', \PAGE\Request::POST);

$machine = new \SimpleDB\Database(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');

$row = $machine->select('machine_id', '=' . $machineId);

if ($machine->affectedRows() == 0) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => 'Machine with specified ID is not found.',
	]));
}

$months = [0, 1, 3, 6, 12, 24];

$machine->update('machine_id', '=' . $machineId, [
	'due_date' => date('Y-m-d', strtotime($row[0]['due_date'] . ' +' . $months[$row[0]['billing_cycle']] . ' months')),
]);

$GLOBALS['SESSION']->set('response', '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-check-circle"></i> "' . $row[0]['label'] . '" has been renewed.</span></div>');

die(json_encode([
	'status' => 'OK',
]));
