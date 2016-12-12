<?php
defined('INDEX') or die('Access is denied.');

if(!$session->get('username'))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> 'Session expired. Please sign in again to continue.',
	)));

$id = (isset($_GET['id'])) ? $_GET['id'] : '';

$machine = new SimpleDB(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');

$row = $machine->select('machine_id', '=' . $id);

if($machine->affectedRows() == 0)
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> 'Machine with specified ID is not found.',
	)));

echo json_encode(array_merge(array(
	'status'	=> 'OK',
), $row[0]));
?>