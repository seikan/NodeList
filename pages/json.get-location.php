<?php
defined('INDEX') or die('Access is denied.');

if(!$session->get('username'))
	die(json_encode(array()));

$machine = new SimpleDB(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');

$rows = $machine->select('city_name', '*', 'city_name');

if($machine->affectedRows() == 0)
	die(json_encode(array()));

$tmp = array();

foreach($rows as $row)
	$tmp[$row['city_name']] = TRUE;

echo json_encode(array_keys($tmp));
?>