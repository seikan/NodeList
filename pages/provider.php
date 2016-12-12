<?php
defined('INDEX') or die('Access is denied.');

if(!$session->get('username'))
	die(header('Location: ' . getURL('sign-in', array(
		'return'	=> urlencode(getPageURL())
	))));

$css[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/css/bootstrap-select.min.css';
$css[] = '//cdn.datatables.net/1.10.10/css/dataTables.bootstrap.min.css';
$css[] = '//cdn.datatables.net/plug-ins/1.10.10/integration/font-awesome/dataTables.fontAwesome.css';

$js[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js';
$js[] = '//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js';
$js[] = '//cdn.datatables.net/1.10.10/js/dataTables.bootstrap.min.js';
$js[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/3.1.1/bootstrap3-typeahead.min.js';

$scripts = '
		$(function(){
			$(\'#add-new-provider\').on(\'click\', function(e){
				e.preventDefault();

				$(\'#modal-provider input[name=id]\').val(\'\');
				$(\'#modal-provider .modal-title\').html(\'New Provider\');
				$(\'#modal-provider .btn-primary\').html(\'<span class="hidden-xs">Add Provider</span><span class="visible-xs"><i class="fa fa-save"></i></span>\');
				$(\'#modal-provider .btn-danger\').addClass(\'hidden\');
				$(\'#modal-provider .btn-danger\').removeAttr(\'data-id\');
				$(\'#modal-provider\').modal(\'show\');
			});

			$(\'#modal-provider\').on(\'hide.bs.modal\', function(){
				$(\'#modal-provider .has-error\').removeClass(\'has-error\');
				$(\'#response\').html(\'\');
				$(\'#modal-provider form\')[0].reset();
			});

			$(\'#modal-provider .btn-primary\').on(\'click\', function(e){
				e.preventDefault();

				$(\'#modal-provider .has-error\').removeClass(\'has-error\');

				var $btn = $(this);
				var width = $btn.width();
				var text = $btn.html();

				$btn.attr(\'disabled\', true).width(width).html(\'<i class="fa fa-refresh fa-spin"></i><span class="hidden-xs"> Saving...</span>\');

				$.post(\'' . getURL('save-provider.json') . '\', $(\'#modal-provider form\').serialize(), function(json){
					$btn.attr(\'disabled\', false).html(text);

					if(json.status == \'OK\'){
						window.location.href = window.location.href;
						return;
					}

					$(\'#modal-provider [name=\' + json.id + \']\').parent().addClass(\'has-error\');
					$(\'#response\').html(json.response);
				}, \'json\');
			});

			$(\'#modal-provider .btn-danger\').on(\'click\', function(e){
				e.preventDefault();

				var $btn = $(this);
				var width = $btn.width();
				var text = $btn.html();

				$btn.attr(\'disabled\', true).width(width).html(\'<i class="fa fa-refresh fa-spin"></i> Wait...\');

				var $form = $(\'<form method="post">\').html(\'<input type="hidden" name="remove" value="\' + $(this).attr(\'data-id\') +\'">\');
				$form.appendTo(\'body\');
				$form.submit();
			});

			$(\'#provider-list\').dataTable({
				pageLength: 50,
				sDom: \'<"top"f>rt<"bottom"ip><"hidden-xs"l><"clear">\',
				stateSave: true,
				order: [[ 0, \'asc\' ]],
				columnDefs: [
					{
						"targets": \'no-sort\', "orderable": false
					}
				],
				\'drawCallback\': function(settings){
					$(\'select\').selectpicker();

					$(\'.edit-provider\').off(\'click\').on(\'click\', function(e){
						e.preventDefault();

						var id = $(this).attr(\'data-id\');

						$.getJSON(\'' . getURL('get-provider.json') . '\', { id: id })
						.done(function(data){
							if(data.status != \'OK\'){
								alert(data.response);
								window.location.href = window.location.href;
								return;
							}

							$(\'#modal-provider .modal-title\').html(data.name);
							$(\'#modal-provider [name=id]\').val(id);
							$(\'#modal-provider .btn-primary\').html(\'<span class="hidden-xs">Save Changes</span><span class="visible-xs"><i class="fa fa-save"></i></span>\');

							$(\'#modal-provider [name=name]\').val(data.name);
							$(\'#modal-provider [name=website]\').val(data.website);
							$(\'#modal-provider [name=controlPanel]\').val(data.control_panel);
							$(\'#modal-provider [name=cpUrl]\').val(data.cp_url);

							$(\'#modal-provider .btn-danger\').removeClass(\'hidden\');
							$(\'#modal-provider .btn-danger\').attr(\'data-id\', id);

							$(\'#modal-provider\').modal(\'show\');
						});
					});
				}
			});

			$.get(\'' . getURL('get-control-panel.json') . '\', function(data){
				$(\'input[name=controlPanel]\').typeahead({ source:data });
			}, \'json\');
		});';

if(isset($_GET['id'])){
	$scripts .= '
	$(function(){
		$(\'.edit-provider[data-id=' . $_GET['id'] . ']\').trigger(\'click\');
	});';
}

$machine = new SimpleDB(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');
$provider = new SimpleDB(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');

if(isset($_POST['remove'])){
	$machine->select('provider_id', '=' . $_POST['remove']);

	if($machine->affectedRows() == 0){
		$provider->delete('provider_id', '=' . $_POST['remove']);
		$session->set('response', '<div class="alert alert-success alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span><i class="fa fa-check-circle"></i> The provider has been removed.</span></div>');
	}
	else{
		$session->set('response', '<div class="alert alert-danger alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span><i class="fa fa-exclamation-circle"></i> Cannot remove provider. There ' . (($machine->affectedRows() > 1) ? 'are ' . $machine->affectedRows() . ' machines' : 'is 1 machine') . ' assigned to this provider.</span></div>');
	}
}

require_once INCLUDES . 'header.php';
?>

		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<p>
						<button class="btn btn-default" id="add-new-provider"><i class="fa fa-plus-circle"></i> Add New Provider</button>
					</p>
					<?php
					if($session->get('response')){
						echo $session->get('response');
						$session->set('response', NULL);
					}
					?>
					<hr>
					<?php
					$rows = $provider->select('*', '*', 'name');

					if($provider->affectedRows() > 0){
						echo '
						<table id="provider-list" class="table table-hover">
							<thead>
								<tr>
									<th>Name</th>
									<th class="text-center hidden-xs">VMs</th>
									<th class="hidden-xs">Website</th>
									<th class="hidden-xs">Control Panel</th>
									<th class="no-sort"></th>
								</tr>
							</thead>
							<tbody>';


						foreach($rows as $row){
							$machine->select('provider_id', '=' . $row['provider_id']);

							echo '
								<tr>
									<td>
										<span class="hidden-xs">' . $row['name'] . '</span>
										<span class="visible-xs">
											<a href="' . $row['website'] . '" target="_blank">' . $row['name'] . '</a> <span class="badge">' . $machine->affectedRows() . '</span>
											<div class="control-panel">
												<a href="' . $row['cp_url'] . '" target="_blank"><i class="fa fa-dashboard"></i> ' . $row['control_panel'] . '</a>
											</div>
										</span>
									</td>
									<td class="text-center hidden-xs">' . $machine->affectedRows() . '</td>
									<td class="hidden-xs"><a href="' . $row['website'] . '" target="_blank">' . $row['website'] . '</a></td>
									<td class="hidden-xs">
										<div class="dropdown">
											<a href="#" id="cp-for-provider-' . $row['provider_id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">' . $row['control_panel'] . ' <span class="caret"></span></a>

											<ul class="dropdown-menu" aria-labelledby="provider-for-provider-' . $row['provider_id'] . '">
												<li><a href="' . $row['cp_url'] . '" target="_blank"><i class="fa fa-link"></i>  ' . $row['cp_url'] . '</a></li>
											</ul>
										</div>
									</td>
									<td class="col-md-1 text-right"><a href="javascript:;" data-id="' . $row['provider_id'] . '" class="edit-provider"><i class="fa fa-pencil-square-o"></i></a></td>
								</tr>';
						}

						echo '
							</tbody>
						</table>';
					}
					else{
						echo '
						<div class="alert alert-info alert-dismissible">
						  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							<span><i class="fa fa-exclamation-circle"></i> There is no provider added.</span>
						</div>';
					}
					?>
				</div>
			</div>
		</div>

		<div id="modal-provider" class="modal" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">New Provider</h4>
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
						<div class="col-md-6">
							<button type="button" class="btn btn-danger pull-left hidden">
								<span class="hidden-xs">Remove</span>
								<span class="visible-xs"><i class="fa fa-trash"></i></span>
							</button>
						</div>
						<div class="col-md-6">
							<button type="button" class="btn btn-default" data-dismiss="modal">
								<span class="hidden-xs">Cancel</span>
								<span class="visible-xs"><i class="fa fa-close"></i></span>
							</button>
							<button type="button" class="btn btn-primary"><span class="hidden-xs">Add Provider</span><span class="visible-xs"><i class="fa fa-save"></i></span></button>
						</div>

					</div>
				</div>
			</div>
		</div>
<?php
require_once INCLUDES . 'footer.php';
?>