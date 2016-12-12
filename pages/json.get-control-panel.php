<?php
defined('INDEX') or die('Access is denied.');

if(!$session->get('username'))
	die(json_encode(array()));

$provider = new SimpleDB(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');

$rows = $provider->select('control_panel', '*', 'control_panel');

if($provider->affectedRows() == 0)
	die(json_encode(array()));

$tmp = array();

foreach($rows as $row)
	$tmp[$row['control_panel']] = TRUE;

echo json_encode(array_keys($tmp));
?>