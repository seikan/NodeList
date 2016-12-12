<?php
defined('INDEX') or die('Access is denied.');

if(!$session->get('username'))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> 'Session expired. Please sign in again to continue.',
	)));

$id = (isset($_GET['id'])) ? $_GET['id'] : '';

$provider = new SimpleDB(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');

$row = $provider->select('provider_id', $id);

if($provider->affectedRows() == 0)
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> 'Provider with specified ID is not found.',
	)));

echo json_encode(array_merge(array(
	'status'	=> 'OK',
), $row[0]));
?>