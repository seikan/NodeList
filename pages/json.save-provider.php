<?php
defined('INDEX') or die('Access is denied.');

if(!$session->get('username'))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Session expired. Please sign in again to continue.</span></div>',
		'id'		=> 'label',
	)));

$providerId = (isset($_POST['id'])) ? $_POST['id'] : '';
$name = (isset($_POST['name'])) ? $_POST['name'] : '';
$website = (isset($_POST['website'])) ? trim($_POST['website'], '/') : '';
$controlPanel = (isset($_POST['controlPanel'])) ? $_POST['controlPanel'] : '';
$cpUrl = (isset($_POST['cpUrl'])) ? trim($_POST['cpUrl'], '/') : '';

if(empty($name))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert provider name.</span></div>',
		'id'		=> 'name',
	)));

if(mb_strlen($name) > 100)
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Provider name exceed 100 characters in length.</span></div>',
		'id'		=> 'name',
	)));

if(!filter_var($website, FILTER_VALIDATE_URL))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid provider website.</span></div>',
		'id'		=> 'website',
	)));

if(empty($controlPanel))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert control panel name.</span></div>',
		'id'		=> 'controlPanel',
	)));

if(mb_strlen($controlPanel) > 50)
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Control panel name exceed 50 characters in length.</span></div>',
		'id'		=> 'controlPanel',
	)));

if(!filter_var($cpUrl, FILTER_VALIDATE_URL))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid control panel URL.</span></div>',
		'id'		=> 'cpUrl',
	)));

$provider = new SimpleDB(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');

if(!$providerId){
	$provider->insert(array(
		'name'			=> html_entity_decode($name),
		'website'		=> $website,
		'control_panel'	=> html_entity_decode($controlPanel),
		'cp_url'		=> $cpUrl,
	));

	$session->set('response', '<div class="alert alert-success"><span><i class="fa fa-check-circle"></i> "' . $name . '" has been added.</span></div>');
}
else{
	$provider->update('provider_id', '=' . $providerId, array(
		'name'			=> html_entity_decode($name),
		'website'		=> $website,
		'control_panel'	=> html_entity_decode($controlPanel),
		'cp_url'		=> $cpUrl,
	));

	$session->set('response', '<div class="alert alert-success"><span><i class="fa fa-check-circle"></i> "' . $name . '" has been updated.</span></div>');
}

die(json_encode(array(
	'status'	=> 'OK',
)));
?>