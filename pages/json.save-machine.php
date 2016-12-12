<?php
defined('INDEX') or die('Access is denied.');

if(!$session->get('username'))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Session expired. Please sign in again to continue.</span></div>',
		'id'		=> 'label',
	)));

$units = array('mb', 'gb', 'tb');

$isActive = (isset($_POST['isActive'])) ? TRUE: FALSE;
$machineId = (isset($_POST['id'])) ? $_POST['id'] : '';
$label = (isset($_POST['label'])) ? $_POST['label'] : '';
$vmType = (isset($_POST['vmType'])) ? $_POST['vmType'] : '';
$memory = (isset($_POST['memory'])) ? $_POST['memory'] : '';
$memoryUnit = (isset($_POST['memoryUnit'])) ? $_POST['memoryUnit'] : '';
$swap = (isset($_POST['swap'])) ? $_POST['swap'] : '';
$swapUnit = (isset($_POST['swapUnit'])) ? $_POST['swapUnit'] : '';
$diskSpace = (isset($_POST['diskSpace'])) ? $_POST['diskSpace'] : '';
$diskSpaceUnit = (isset($_POST['diskSpaceUnit'])) ? $_POST['diskSpaceUnit'] : '';
$hddType = (isset($_POST['hddType'])) ? $_POST['hddType'] : '';
$bandwidth = (isset($_POST['bandwidth'])) ? $_POST['bandwidth'] : '';
$bandwidthUnit = (isset($_POST['bandwidthUnit'])) ? $_POST['bandwidthUnit'] : '';
$isNAT = (isset($_POST['isNAT'])) ? TRUE: FALSE;
$ipAddress = (isset($_POST['ipAddress'])) ? array_filter($_POST['ipAddress']) : '';
$countryCode = (isset($_POST['countryCode'])) ? $_POST['countryCode'] : '';
$cityName = (isset($_POST['cityName'])) ? $_POST['cityName'] : '';
$providerId = (isset($_POST['providerId'])) ? $_POST['providerId'] : '';
$currencyCode = (isset($_POST['currencyCode'])) ? $_POST['currencyCode'] : '';
$price = (isset($_POST['price'])) ? ((empty($_POST['price'])) ? '0.00' : $_POST['price']) : '0.00';
$billingCycle = (isset($_POST['billingCycle'])) ? $_POST['billingCycle'] : '';
$dueDate = (isset($_POST['dueDate'])) ? $_POST['dueDate'] : '';
$notes = (isset($_POST['notes'])) ? $_POST['notes'] : '';

if(empty($label))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert a label.</span></div>',
		'id'		=> 'label',
	)));

if(mb_strlen($label) > 100)
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Label exceed 100 characters in length.</span></div>',
		'id'		=> 'label',
	)));

if(!in_array($vmType, array(1, 2, 3, 4, 5)))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please select a virtualization type.</span></div>',
		'id'		=> 'type',
	)));

if(!preg_match('/^\d+$/', $memory))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert integer only for memory amount.</span></div>',
		'id'		=> 'memory',
	)));

if(!in_array($memoryUnit, $units))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please select a valid unit for memory.</span></div>',
		'id'		=> 'memoryUnit',
	)));

if(!preg_match('/^\d+$/', $swap))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert integer only for swap amount.</span></div>',
		'id'		=> 'swap',
	)));

if(!in_array($swapUnit, $units))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please select a valid unit for swap.</span></div>',
		'id'		=> 'swapUnit',
	)));

if(!preg_match('/^\d+$/', $diskSpace))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert integer only for disk space amount.</span></div>',
		'id'		=> 'diskSpace',
	)));

if(!in_array($diskSpaceUnit, $units))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please select a valid unit for disk space.</span></div>',
		'id'		=> 'diskSpaceUnit',
	)));

if(!in_array($hddType, array(1, 2, 3, 4)))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please select a HDD type.</span></div>',
		'id'		=> 'hddType',
	)));

if(!preg_match('/^\d+$/', $bandwidth))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert integer only for bandwidth amount.</span></div>',
		'id'		=> 'bandwidth',
	)));

if(!in_array($bandwidthUnit, $units))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please select a valid unit for bandwidth.</span></div>',
		'id'		=> 'bandwidthUnit',
	)));

if(!is_array($ipAddress))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid IP address.</span></div>',
		'id'		=> 'ipAddresss',
	)));

foreach($ipAddress as $ip)
	if(!filter_var($ip, FILTER_VALIDATE_IP))
		die(json_encode(array(
			'status'	=> 'ERROR',
			'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid IP address.</span></div>',
			'id'		=> 'ipAddress\\[\\]',
		)));

if(!getCountryNameByCode($countryCode))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please select a valid country.</span></div>',
		'id'		=> 'countryCode',
	)));

$provider = new SimpleDB(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');
$provider->select('*', $providerId);

if($provider->affectedRows() == 0)
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please select a provider.</span></div>',
		'id'		=> 'providerId',
	)));

if(!in_array($currencyCode, array_keys($config['currencies'])))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please select a valid currency.</span></div>',
		'id'		=> 'currencyCode',
	)));

if(!preg_match('/^\d+\.\d{2}$/', $price))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid figure for price.</span></div>',
		'id'		=> 'price',
	)));

if(!in_array($billingCycle, array(1, 2, 3, 4, 5)))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please select a valid billing cycle.</span></div>',
		'id'		=> 'billingCycle',
	)));

if(!empty($dueDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate))
	die(json_encode(array(
		'status'	=> 'ERROR',
		'response'	=> '<div class="alert alert-danger"><span><i class="fa fa-exclamation-triangle"></i> Please insert a valid due date.</span></div>',
		'id'		=> 'dueDate',
	)));

$machine = new SimpleDB(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');

if(!$machineId){
	$machine->insert(array(
		'provider_id'		=> $providerId,
		'label'				=> html_entity_decode($label),
		'is_active'			=> TRUE,
		'vm_type'			=> $vmType,
		'is_nat'			=> $isNAT,
		'ip_address'		=> implode(';', $ipAddress),
		'city_name'			=> html_entity_decode($cityName),
		'country_code'		=> $countryCode,
		'total_ram'			=> $memory,
		'ram_unit'			=> $memoryUnit,
		'total_swap'		=> $swap,
		'swap_unit'			=> $swapUnit,
		'total_disk_space'	=> $diskSpace,
		'disk_space_unit'	=> $diskSpaceUnit,
		'hdd_type'			=> $hddType,
		'total_bandwidth'	=> $bandwidth,
		'bandwidth_unit'	=> $bandwidthUnit,
		'price'				=> $price,
		'currency_code'		=> $currencyCode,
		'billing_cycle'		=> $billingCycle,
		'due_date'			=> $dueDate,
		'notes'				=> html_entity_decode($notes),
	));

	$session->set('response', '<div class="alert alert-success"><span><i class="fa fa-check-circle"></i> "' . $label . '" has been added.</span></div>');
}
else{
	$machine->update('machine_id', '=' . $machineId, array(
		'provider_id'		=> $providerId,
		'label'				=> html_entity_decode($label),
		'is_active'			=> $isActive,
		'vm_type'			=> $vmType,
		'is_nat'			=> $isNAT,
		'ip_address'		=> implode(';', $ipAddress),
		'city_name'			=> html_entity_decode($cityName),
		'country_code'		=> $countryCode,
		'total_ram'			=> $memory,
		'ram_unit'			=> $memoryUnit,
		'total_swap'		=> $swap,
		'swap_unit'			=> $swapUnit,
		'total_disk_space'	=> $diskSpace,
		'disk_space_unit'	=> $diskSpaceUnit,
		'hdd_type'			=> $hddType,
		'total_bandwidth'	=> $bandwidth,
		'bandwidth_unit'	=> $bandwidthUnit,
		'price'				=> $price,
		'currency_code'		=> $currencyCode,
		'billing_cycle'		=> $billingCycle,
		'due_date'			=> $dueDate,
		'notes'				=> html_entity_decode($notes),
	));

	$session->set('response', '<div class="alert alert-success"><span><i class="fa fa-check-circle"></i> "' . $label . '" has been updated.</span></div>');
}

die(json_encode(array(
	'status'	=> 'OK',
)));
?>
