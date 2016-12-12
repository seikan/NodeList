<?php
defined('INDEX') or die('Access is denied.');

if(!$session->get('username'))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> 'Session expired. Please sign in again to continue.',
		'id'		=> 'label',
	)));

$machineId = (isset($_POST['id'])) ? $_POST['id'] : '';

$machine = new SimpleDB(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');

$row = $machine->select('machine_id', '=' . $machineId);

if($machine->affectedRows() == 0)
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> 'Machine with specified ID is not found.',
	)));

$months = array(0, 1, 3, 6, 12, 24);

$machine->update('machine_id', '=' . $machineId, array(
	'due_date'	=> date('Y-m-d', strtotime($row[0]['due_date'] . ' +' . $months[$row[0]['billing_cycle']] . ' months')),
));

$session->set('response', '<div class="alert alert-success alert-dismissible"><span><i class="fa fa-check-circle"></i> "' . $row[0]['label'] . '" has been renewed.</span></div>');

die(json_encode(array(
	'status'	=> 'OK',
)));
?>