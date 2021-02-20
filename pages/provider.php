<?php
defined('INDEX') or die('Access is denied.');

if (!$GLOBALS['SESSION']->get('username')) {
	die(header('Location: ' . getURL('sign-in', [
		'return' => urlencode($GLOBALS['PAGE']->getCurrentUrl()),
	])));
}

$css[] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css';
$css[] = 'https://cdn.datatables.net/plug-ins/1.10.21/integration/font-awesome/dataTables.fontAwesome.css';
// $css[] = 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css';

$js[] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js';
$js[] = 'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js';
$js[] = 'https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js';
$js[] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js';
$js[] = './assets/js/provider.min.js';

$scripts = '
	var url = {
		get_machine: \'' . getURL('get-machine.json') . '\',
		get_provider: \'' . getURL('get-provider.json') . '\',
		get_location: \'' . getURL('get-location.json') . '\',
		get_control_panel: \'' . getURL('get-control-panel.json') . '\',
		save_machine: \'' . getURL('save-machine.json') . '\',
		save_provider: \'' . getURL('save-provider.json') . '\',
		renew_machine: \'' . getURL('renew-machine.json') . '\'
	}';

if ($GLOBALS['PAGE']->request('id', \PAGE\Request::GET)) {
	$scripts .= '
	$(function(){
		$(\'.edit-provider[data-id=' . $GLOBALS['PAGE']->request('id', \PAGE\Request::GET) . ']\').trigger(\'click\');
	});';
}

$machine = new \SimpleDB\Database(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');
$provider = new \SimpleDB\Database(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');

if ($GLOBALS['PAGE']->request('remove', \PAGE\Request::POST)) {
	$machine->select('provider_id', '=' . $GLOBALS['PAGE']->request('remove', \PAGE\Request::POST));

	if ($machine->affectedRows() == 0) {
		$provider->delete('provider_id', '=' . $GLOBALS['PAGE']->request('remove', \PAGE\Request::POST));
		$GLOBALS['SESSION']->set('response', '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-check-circle"></i> The provider has been removed.</span></div>');
	} else {
		$GLOBALS['SESSION']->set('response', '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-exclamation-circle"></i> Cannot remove provider. There ' . (($machine->affectedRows() > 1) ? 'are ' . $machine->affectedRows() . ' machines' : 'is 1 machine') . ' assigned to this provider.</span></div>');
	}
}

require_once INCLUDES . 'header.php';
?>

	<main class="flex-shrink-0">
		<div class="container pt-5">
			<div class="row mt-5">
				<div class="col-12">
					<p>
						<button class="btn btn-secondary" id="add-new-provider"><i class="fa fa-plus-circle"></i> Add New Provider</button>
					</p>
					<?php
					if ($GLOBALS['SESSION']->get('response')) {
						echo $GLOBALS['SESSION']->get('response');
						$GLOBALS['SESSION']->set('response', null);
					}
					?>
					<hr>
					<?php
					$rows = $provider->select('*', '*', 'name');

					if ($provider->affectedRows() > 0) {
						echo '
						<table id="provider-list" class="table table-hover">
							<thead>
								<tr>
									<th scope="col">Name</th>
									<th scope="col" class="text-center d-none d-sm-table-cell">VMs</th>
									<th scope="col" class="d-none d-sm-table-cell">Website</th>
									<th scope="col" class="d-none d-sm-table-cell">Control Panel</th>
									<th scope="col" class="no-sort"></th>
								</tr>
							</thead>
							<tbody>';

						foreach ($rows as $row) {
							$machine->select('provider_id', '=' . $row['provider_id']);

							echo '
								<tr>
									<td>
										<span class="d-none d-sm-block">' . $row['name'] . '</span>
										<span class="d-block d-sm-none">
											<a href="' . $row['website'] . '" target="_blank">' . $row['name'] . '</a> <span class="badge badge-primary">' . $machine->affectedRows() . '</span>
											<div>
												<a href="' . $row['cp_url'] . '" target="_blank"><i class="fa fa-dashboard"></i> ' . $row['control_panel'] . '</a>
											</div>
										</span>
									</td>
									<td class="text-center d-none d-sm-table-cell">' . $machine->affectedRows() . '</td>
									<td class="d-none d-sm-table-cell"><a href="' . $row['website'] . '" target="_blank">' . $row['website'] . '</a></td>
									<td class="d-none d-sm-table-cell">
										<div class="dropdown">
											<a href="#" id="cp-for-provider-' . $row['provider_id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">' . $row['control_panel'] . ' <span class="caret"></span></a>

											<ul class="dropdown-menu" aria-labelledby="provider-for-provider-' . $row['provider_id'] . '">
												<li><a href="' . $row['cp_url'] . '" target="_blank"><i class="fa fa-link"></i>  ' . $row['cp_url'] . '</a></li>
											</ul>
										</div>
									</td>
									<td class="text-right"><a href="javascript:;" data-id="' . $row['provider_id'] . '" class="edit-provider"><i class="fa fa-pencil-square-o"></i></a></td>
								</tr>';
						}

						echo '
							</tbody>
						</table>';
					} else {
						echo '
						<div class="alert alert-info">
							<span><i class="fa fa-exclamation-circle"></i> There is no provider added.</span>
						</div>';
					}
					?>
				</div>
			</div>
		</div>
	</main>

	<div id="modal-provider" class="modal" tabindex="-1" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">New Provider</h4>
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
						<span class="d-none d-md-inline">Add Provider</span>
					</button>
				</div>
			</div>
		</div>
	</div>
<?php
require_once INCLUDES . 'footer.php';
?>