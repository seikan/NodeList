<?php
defined('INDEX') or die('Access is denied.');

if (!$GLOBALS['SESSION']->get('username')) {
	die(header('Location: ' . getURL('sign-in', [
		'return' => urlencode($GLOBALS['PAGE']->getCurrentUrl()),
	])));
}

$css[] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css';
$css[] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css';
// $css[] = 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css';
$css[] = 'https://cdn.datatables.net/plug-ins/1.10.21/integration/font-awesome/dataTables.fontAwesome.css';
$css[] = 'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/0.8.4/css/flag-icon.min.css';

$js[] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js';
$js[] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js';
$js[] = 'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js';
$js[] = 'https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js';
$js[] = 'https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js';
$js[] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js';
$js[] = 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.0/clipboard.min.js';
$js[] = './assets/js/machine.min.js';

$currencies = [];

foreach ($config['currencies'] as $key => $values) {
	$currencies[] = '\'' . $key . '\': {label: \'' . $values['label'] . '\', rate: ' . $values['rate'] . '}';
}

$scripts = '
	var url = {
		get_machine: \'' . getURL('get-machine.json') . '\',
		get_provider: \'' . getURL('get-provider.json') . '\',
		get_location: \'' . getURL('get-location.json') . '\',
		get_control_panel: \'' . getURL('get-control-panel.json') . '\',
		save_machine: \'' . getURL('save-machine.json') . '\',
		save_provider: \'' . getURL('save-provider.json') . '\',
		renew_machine: \'' . getURL('renew-machine.json') . '\'
	}

	var currencies = {' . implode(', ', $currencies) . '};';

$vmTypes = [
	'1' => [
		'code'  => 'OVZ',
		'name'  => 'OpenVZ',
		'class' => 'success',
	],
	'2' => [
		'code'  => 'KVM',
		'name'  => 'KVM',
		'class' => 'info',
	],
	'3' => [
		'code'  => 'XEN',
		'name'  => 'XEN',
		'class' => 'danger',
	],
	'4' => [
		'code'  => 'VMW',
		'name'  => 'VMware',
		'class' => 'warning',
	],
	'5' => [
		'code'  => 'DED',
		'name'  => 'Dedicated',
		'class' => 'primary',
	],
];

$hddTypes = [
	'1' => [
		'code'  => 'HDD',
		'class' => 'secondary',
	],
	'2' => [
		'code'  => 'SSHD',
		'class' => 'success',
	],
	'3' => [
		'code'  => 'SAS',
		'class' => 'info',
	],
	'4' => [
		'code'  => 'SSD',
		'class' => 'danger',
	],
];

$machine = new \SimpleDB\Database(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');
$provider = new \SimpleDB\Database(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');

if (isset($_POST['remove'])) {
	$machine->delete('machine_id', '=' . $_POST['remove']);
	$GLOBALS['SESSION']->set('response', '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-check-circle"></i> The machine has been removed.</span></div>');
}

if (isset($_POST['hideInactiveVM'])) {
	$GLOBALS['SESSION']->set('showInactiveVM', null);
}

if (isset($_POST['showInactiveVM'])) {
	$GLOBALS['SESSION']->set('showInactiveVM', true);
}

include INCLUDES . 'header.php';
?>
	<main class="flex-shrink-0">
		<div class="container pt-5">
			<div class="row mt-5">
				<div class="col-12">
					<p>
						<button class="btn btn-secondary pull-left" id="add-new-machine"><i class="fa fa-plus-circle"></i> Add<span class="d-xs-none"> New Machine</span></button>

						<?php
						if ($GLOBALS['SESSION']->get('showInactiveVM')) {
							echo '<button class="btn btn-warning pull-right" id="hide-inactive-vm" title="Hide inactive VM" data-toggle="tooltip"><i class="fa fa-eye-slash"></i> <span class="d-xs-none">Hide Inactive VM</span></button>';
						} else {
							echo '<button class="btn btn-success pull-right" id="show-inactive-vm" title="Show inactive VM" data-toggle="tooltip"><i class="fa fa-eye"></i> <span class="d-xs-none">Show Inactive VM</span></button>';
						}
						?>

						<div class="clearfix"></div>
					</p>
					<?php
					if ($GLOBALS['SESSION']->get('response')) {
						echo $GLOBALS['SESSION']->get('response');
						$GLOBALS['SESSION']->set('response', null);
					}
					?>
					<hr />
					<?php
					$rows = $machine->select('*', '*', 'label');

					if ($machine->affectedRows() > 0) {
						echo '
						<table id="machine-list" class="table table-hover">
							<thead>
								<tr>
									<th scope="col">Label</th>
									<th scope="col" class="d-none d-sm-table-cell">IP Address</th>
									<th scope="col" class="d-none d-sm-table-cell">Location</th>
									<th scope="col" class="d-none d-sm-table-cell">Memory</th>
									<th scope="col" class="d-none d-sm-table-cell">HDD</th>
									<th scope="col" class="d-none d-sm-table-cell">Provider</th>
									<th scope="col" class="d-none d-sm-table-cell">Price</th>
									<th scope="col" class="d-none d-sm-table-cell">Due</th>
									<th scope="col" class="no-sort d-none"></th>
									<th scope="col" class="no-sort"></th>
								</tr>
							</thead>
							<tbody>';

						foreach ($rows as $row) {
							if (!$GLOBALS['SESSION']->get('showInactiveVM')) {
								if (!$row['is_active']) {
									continue;
								}
							}

							$data = $provider->select('provider_id', '=' . $row['provider_id']);

							if ($provider->affectedRows() == 0) {
								continue;
							}

							echo '
								<tr data-id="' . $row['machine_id'] . '"' . (($row['is_active']) ? '' : ' class="table-active"') . '>
									<td>
										<span class="badge badge-' . $vmTypes[$row['vm_type']]['class'] . '">' . $vmTypes[$row['vm_type']]['code'] . '</span> <span class="machine-name" data-id="' . $row['machine_id'] . '">' . $row['label'] . '</span>
										<div class="d-block d-sm-none">
											<div class="dropdown">
												<a href="#" data-toggle="dropdown"><i class="fa fa-cloud"></i> ' . $data[0]['name'] . ' <span class="caret"></span></a>
												<ul class="dropdown-menu">
													<li><a href="' . $data[0]['cp_url'] . '" target="_blank"><i class="fa fa-tachometer"></i> ' . $data[0]['control_panel'] . '</a></li>
													<li><a href="' . $data[0]['website'] . '" target="_blank"><i class="fa fa-globe"></i> Website</a></li>
													<li><a href="javascript:;" data-id="' . $data[0]['provider_id'] . '" class="edit-provider"><i class="fa fa-pencil-square-o"></i> Edit Provider</a></li>
												</ul>
											</div>
										</div>
									</td>
									<td class="d-none d-sm-table-cell text-nowrap">' . $row['ip_address'] . (($row['is_nat']) ? '<span class="nat">NAT</span>' : '') . '</td>
									<td class="d-none d-sm-table-cell"><span data-toggle="tooltip" data-placement="top" title="' . $row['city_name'] . ', ' . getCountryNameByCode($row['country_code']) . '"><span class="flag-icon flag-icon-' . strtolower($row['country_code']) . '"></span> ' . $row['city_name'] . '</span></td>
									<td class="d-none d-sm-table-cell">' . $row['total_ram'] . ' ' . strtoupper($row['ram_unit']) . '<div class="swap">' . (($row['total_swap']) ? ($row['total_swap'] . ' ' . strtoupper($row['swap_unit'])) : '') . '</div></td>
									<td class="d-none d-sm-table-cell text-nowrap">' . $row['total_disk_space'] . ' ' . strtoupper($row['disk_space_unit']) . '<span class="badge badge-hdd badge-' . $hddTypes[$row['hdd_type']]['class'] . '">' . $hddTypes[$row['hdd_type']]['code'] . '</span></td>
									<td class="d-none d-sm-table-cell">' . implode(';', $data[0]) . '</td>
									<td class="d-none d-sm-table-cell text-nowrap">' . $row['billing_cycle'] . ' ' . strtoupper($row['currency_code']) . ' ' . $row['price'] . '</td>
									<td class="d-none d-sm-table-cell">' . (($row['due_date']) ? strtotime($row['due_date']) : '') . '</td>
									<td class="d-none">' . $row['notes'] . ',' . getCountryNameByCode($row['country_code']) . ',' . $row['country_code'] . '</td>
									<td class="dtext-right"><a href="javascript:;" data-id="' . $row['machine_id'] . '" class="edit-machine"><i class="fa fa-pencil-square-o"></i></a></td>
								</tr>';
						}

						echo '
							</tbody>
						</table>';
					} else {
						echo '
						<div class="alert alert-info">
							<span><i class="fa fa-exclamation-circle"></i> There is no machine added.</span>
						</div>';
					}
					?>
				</div>
			</div>
		</div>
	</main>

	<div id="modal-machine" class="modal" tabindex="-1" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">New Machine</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>

				<div class="modal-body">
					<form role="form" method="post">
						<div id="response"></div>

						<input type="hidden" name="id">

						<div class="row d-none">
							<div class="form-group col-6">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" value="1" name="isActive" id="isActive" checked>
									<label class="form-check-label" for="isActive">Active</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-9">
								<label>Label</label>
								<input type="text" name="label" class="form-control" maxlength="100">
							</div>

							<div class="form-group col-3">
								<label>Virtualization</label>
								<select name="vmType" class="form-control" data-none-selected-text="">
									<option value="1">OpenVZ</option>
									<option value="2">KVM</option>
									<option value="3">XEN</option>
									<option value="4">VMware</option>
									<option value="5">Dedicated</option>
								</select>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-4">
								<label>Memory</label>
								<div class="input-group">
									<input type="number" name="memory" class="form-control number">
									<div class="input-group-append">
										<select name="memoryUnit" class="form-control" data-width="60px">
											<option value="mb"> MB</option>
											<option value="gb"> GB</option>
											<option value="tb"> TB</option>
										</select>
									</div>
								</div>
							</div>
							<div class="form-group col-4">
								<label>Disk Space</label>
								<div class="input-group">
									<input type="number" name="diskSpace" class="form-control number">
									<div class="input-group-append">
										<select name="diskSpaceUnit" class="form-control" data-width="60px">
											<option value="mb"> MB</option>
											<option value="gb" selected> GB</option>
											<option value="tb"> TB</option>
										</select>
									</div>
								</div>
							</div>
							<div class="form-group col-4">
								<label>Bandwidth</label>
								<div class="input-group">
									<input type="number" name="bandwidth" class="form-control number">
									<div class="input-group-append">
										<select name="bandwidthUnit" class="form-control" data-width="60px">
											<option value="mb"> MB</option>
											<option value="gb" selected> GB</option>
											<option value="tb"> TB</option>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-4">
								<label>Swap</label>
								<div class="input-group">
									<input type="number" name="swap" class="form-control number">
									<div class="input-group-append">
										<select name="swapUnit" class="form-control" data-width="60px">
											<option value="mb"> MB</option>
											<option value="gb"> GB</option>
											<option value="tb"> TB</option>
										</select>
									</div>
								</div>
							</div>
							<div class="form-group col-4">
								<label>HDD Type</label>
								<div class="input-group">
									<span class="input-group-btn">
										<select name="hddType" class="form-control">
											<option value="1" selected> HDD</option>
											<option value="2"> SSHD</option>
											<option value="3"> SAS</option>
											<option value="4"> SSD</option>
										</select>
									</span>
								</div>
							</div>
							<div class="form-group col-4"></div>
						</div>

						<div class="row">
							<div class="form-group col-6">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" value="1" name="isNat" id="isNat">
									<label class="form-check-label" for="isNat">NAT VPS</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div id="ip-address" class="form-group col-6">
								<label>IP Address</label>

								<div class="input-group mb-3">
									<input type="text" name="ipAddress[]" class="form-control" maxlength="39">

									<span class="input-group-btn">
										<button class="btn btn-default add-ip-address" type="button"><i class="fa fa-plus-circle"></i></button>
									</span>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-6">
								<label>Country</label>

								<select name="countryCode" class="form-control" data-live-search="true" data-none-selected-text="">
									<option value=""></option>
									<?php
									foreach ($countries as $cc => $cn) {
										echo '
										<option value="' . $cc . '"> ' . $cn . '</option>';
									}
									?>
								</select>
							</div>
							<div class="form-group col-6">
								<label>City</label>
								<input type="text" name="cityName" class="form-control" autocomplete="off">
							</div>
						</div>

						<div class="row">
							<div class="form-group col-6">
								<label>Provider</label>
								<select name="providerId" class="form-control" data-live-search="true" data-none-selected-text="">
									<?php
									$rows = $provider->select('*', '*', 'name');

									if ($provider->affectedRows() > 0) {
										foreach ($rows as $row) {
											echo '
											<option value="' . $row['provider_id'] . '"> ' . $row['name'] . '</option>';
										}
									}
									?>
								</select>
							</div>
							<div class="form-group col-6">
								<label>Price</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<select name="currencyCode" class="form-control" data-width="70px">
											<?php
											foreach ($config['currencies'] as $code => $currency) {
												echo '
												<option value="' . $code . '"> ' . $currency['label'] . '</option>';
											}
											?>
										</select>
									</div>

									<input type="text" name="price" class="form-control price" data-thousands="" data-decimal=".">

									<div class="input-group-append">
										<select name="billingCycle" class="form-control" data-width="105px">
											<option value="1"> Monthly</option>
											<option value="2"> Quarterly</option>
											<option value="3"> Semi-Yearly</option>
											<option value="4"> Yearly</option>
											<option value="5"> Bi-Yearly</option>
										</select>
									</div>
								</div>
							</div>
							<div class="form-group col-6">
								<label>Due Date</label>
								<div class="input-group date">
									<input type="text" name="dueDate" class="form-control date" />
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
							<div class="form-group col-12">
								<label>Notes</label>
								<textarea name="notes" class="form-control"></textarea>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger d-none mr-auto">
						<i class="fa fa-trash"></i>
						<span class="d-none d-md-inline">Remove</span>
					</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">
						<i class="fa fa-close"></i>
						<span class="d-none d-md-inline">Cancel</span>
					</button>
					<button type="button" class="btn btn-primary">
						<i class="fa fa-save"></i>
						<span class="d-none d-md-inline">Add Machine</span>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div id="modal-provider" class="modal" tabindex="-1" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"></h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>

				<div class="modal-body">
					<form role="form" method="post">
						<div id="response"></div>

						<input type="hidden" name="id">

						<div class="form-group">
							<label>Name</label>
							<input type="text" name="name" class="form-control" maxlength="100">
						</div>

						<div class="form-group">
							<label>Website</label>
							<input type="text" name="website" class="form-control" maxlength="255">
						</div>

						<div class="form-group">
							<label>Control Panel</label>
							<input type="text" name="controlPanel" class="form-control" maxlength="50">
						</div>

						<div class="form-group">
							<label>Control Panel URL</label>
							<input type="text" name="cpUrl" class="form-control" maxlength="255">
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">
						<i class="fa fa-close"></i> <span class="d-none d-md-inline">Cancel</span>
					</button>
					<button type="button" class="btn btn-primary">
						<i class="fa fa-save"></i> <span class="d-none d-md-inline">Save Changes</span>
					</button>
				</div>
			</div>
		</div>
	</div>
<?php
include INCLUDES . 'footer.php';
?>
