<?php

defined('INDEX') or die('Access is denied.');

if (!$GLOBALS['SESSION']->get('username')) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Session expired. Please sign in again to continue.</span></div>',
		'id'       => 'label',
	]));
}

$units = ['mb', 'gb', 'tb'];

$isActive = ($GLOBALS['PAGE']->request('isActive', \PAGE\Request::POST)) ? true : false;
$machineId = $GLOBALS['PAGE']->request('id', \PAGE\Request::POST);
$label = $GLOBALS['PAGE']->request('label', \PAGE\Request::POST);
$vmType = $GLOBALS['PAGE']->request('vmType', \PAGE\Request::POST);
$memory = $GLOBALS['PAGE']->request('memory', \PAGE\Request::POST);
$memoryUnit = $GLOBALS['PAGE']->request('memoryUnit', \PAGE\Request::POST);
$swap = $GLOBALS['PAGE']->request('swap', \PAGE\Request::POST);
$swapUnit = $GLOBALS['PAGE']->request('swapUnit', \PAGE\Request::POST);
$diskSpace = $GLOBALS['PAGE']->request('diskSpace', \PAGE\Request::POST);
$diskSpaceUnit = $GLOBALS['PAGE']->request('diskSpaceUnit', \PAGE\Request::POST);
$hddType = $GLOBALS['PAGE']->request('hddType', \PAGE\Request::POST);
$bandwidth = $GLOBALS['PAGE']->request('bandwidth', \PAGE\Request::POST);
$bandwidthUnit = $GLOBALS['PAGE']->request('bandwidthUnit', \PAGE\Request::POST);
$isNat = ($GLOBALS['PAGE']->request('isNat', \PAGE\Request::POST)) ? true : false;
$ipAddress = array_filter($GLOBALS['PAGE']->request('ipAddress', \PAGE\Request::POST));
$countryCode = $GLOBALS['PAGE']->request('countryCode', \PAGE\Request::POST);
$cityName = $GLOBALS['PAGE']->request('cityName', \PAGE\Request::POST);
$providerId = $GLOBALS['PAGE']->request('providerId', \PAGE\Request::POST);
$currencyCode = $GLOBALS['PAGE']->request('currencyCode', \PAGE\Request::POST);
$price = ($GLOBALS['PAGE']->request('price', \PAGE\Request::POST)) ? $GLOBALS['PAGE']->request('price', \PAGE\Request::POST) : '0.00';
$billingCycle = $GLOBALS['PAGE']->request('billingCycle', \PAGE\Request::POST);
$dueDate = $GLOBALS['PAGE']->request('dueDate', \PAGE\Request::POST);
$notes = $GLOBALS['PAGE']->request('notes', \PAGE\Request::POST);

if (empty($label)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert a label.</span></div>',
		'name'     => 'label',
	]));
}

if (mb_strlen($label) > 100) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Label exceed 100 characters in length.</span></div>',
		'name'     => 'label',
	]));
}

if (!in_array($vmType, [1, 2, 3, 4, 5])) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please select a virtualization type.</span></div>',
		'name'     => 'type',
	]));
}

if (!preg_match('/^\d+$/', $memory)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert integer only for memory amount.</span></div>',
		'name'     => 'memory',
	]));
}

if (!in_array($memoryUnit, $units)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please select a valid unit for memory.</span></div>',
		'name'     => 'memoryUnit',
	]));
}

if (!preg_match('/^\d+$/', $swap)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert integer only for swap amount.</span></div>',
		'name'     => 'swap',
	]));
}

if (!in_array($swapUnit, $units)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please select a valid unit for swap.</span></div>',
		'name'     => 'swapUnit',
	]));
}

if (!preg_match('/^\d+$/', $diskSpace)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert integer only for disk space amount.</span></div>',
		'name'     => 'diskSpace',
	]));
}

if (!in_array($diskSpaceUnit, $units)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please select a valid unit for disk space.</span></div>',
		'name'     => 'diskSpaceUnit',
	]));
}

if (!in_array($hddType, [1, 2, 3, 4])) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please select a HDD type.</span></div>',
		'name'     => 'hddType',
	]));
}

if (!preg_match('/^\d+$/', $bandwidth)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert integer only for bandwidth amount.</span></div>',
		'name'     => 'bandwidth',
	]));
}

if (!in_array($bandwidthUnit, $units)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please select a valid unit for bandwidth.</span></div>',
		'name'     => 'bandwidthUnit',
	]));
}

if (!is_array($ipAddress)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid IP address.</span></div>',
		'name'     => 'ipAddresss',
	]));
}

foreach ($ipAddress as $ip) {
	if (!filter_var($ip, FILTER_VALIDATE_IP)) {
		die(json_encode([
			'status'   => 'ERROR',
			'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid IP address.</span></div>',
			'name'     => 'ipAddress\\[\\]',
		]));
	}
}

if (count(array_unique($ipAddress)) != count($ipAddress)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Duplicated IP address found.</span></div>',
		'name'     => 'ipAddress\\[\\]',
	]));
}

if (!getCountryNameByCode($countryCode)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please select a valid country.</span></div>',
		'name'     => 'countryCode',
	]));
}

$provider = new \SimpleDB\Database(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');
$provider->select('*', $providerId);

if ($provider->affectedRows() == 0) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please select a provider.</span></div>',
		'name'     => 'providerId',
	]));
}

if (!in_array($currencyCode, array_keys($config['currencies']))) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please select a valid currency.</span></div>',
		'name'     => 'currencyCode',
	]));
}

if (!preg_match('/^\d+\.\d{2}$/', $price)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid figure for price.</span></div>',
		'name'     => 'price',
	]));
}

if (!in_array($billingCycle, [1, 2, 3, 4, 5])) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please select a valid billing cycle.</span></div>',
		'name'     => 'billingCycle',
	]));
}

if (!empty($dueDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
	die(json_encode([
		'status'   => 'ERROR',
		'response' => '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid due date.</span></div>',
		'name'     => 'dueDate',
	]));
}

$machine = new \SimpleDB\Database(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');

if (!$machineId) {
	$machine->insert([
		'provider_id'      => $providerId,
		'label'            => html_entity_decode($label),
		'is_active'        => true,
		'vm_type'          => $vmType,
		'is_nat'           => $isNat,
		'ip_address'       => implode(';', $ipAddress),
		'city_name'        => html_entity_decode($cityName),
		'country_code'     => $countryCode,
		'total_ram'        => $memory,
		'ram_unit'         => $memoryUnit,
		'total_swap'       => $swap,
		'swap_unit'        => $swapUnit,
		'total_disk_space' => $diskSpace,
		'disk_space_unit'  => $diskSpaceUnit,
		'hdd_type'         => $hddType,
		'total_bandwidth'  => $bandwidth,
		'bandwidth_unit'   => $bandwidthUnit,
		'price'            => $price,
		'currency_code'    => $currencyCode,
		'billing_cycle'    => $billingCycle,
		'due_date'         => $dueDate,
		'notes'            => html_entity_decode($notes),
	]);

	$GLOBALS['SESSION']->set('response', '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-check-circle"></i> "' . $label . '" has been added.</span></div>');
} else {
	$machine->update('machine_id', '=' . $machineId, [
		'provider_id'      => $providerId,
		'label'            => html_entity_decode($label),
		'is_active'        => $isActive,
		'vm_type'          => $vmType,
		'is_nat'           => $isNat,
		'ip_address'       => implode(';', $ipAddress),
		'city_name'        => html_entity_decode($cityName),
		'country_code'     => $countryCode,
		'total_ram'        => $memory,
		'ram_unit'         => $memoryUnit,
		'total_swap'       => $swap,
		'swap_unit'        => $swapUnit,
		'total_disk_space' => $diskSpace,
		'disk_space_unit'  => $diskSpaceUnit,
		'hdd_type'         => $hddType,
		'total_bandwidth'  => $bandwidth,
		'bandwidth_unit'   => $bandwidthUnit,
		'price'            => $price,
		'currency_code'    => $currencyCode,
		'billing_cycle'    => $billingCycle,
		'due_date'         => $dueDate,
		'notes'            => html_entity_decode($notes),
	]);

	$GLOBALS['SESSION']->set('response', '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-check-circle"></i> "' . $label . '" has been updated.</span></div>');
}

die(json_encode([
	'status' => 'OK',
]));
