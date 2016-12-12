<?php
defined('INDEX') or die('Access is denied.');

if(!$session->get('username'))
	die(header('Location: ' . getURL('sign-in', [
		'return'	=> urlencode(getPageURL())
	])));

$css[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker3.min.css';
$css[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css';
$css[] = '//cdn.datatables.net/1.10.10/css/dataTables.bootstrap.min.css';
$css[] = '//cdn.datatables.net/plug-ins/1.10.10/integration/font-awesome/dataTables.fontAwesome.css';
$css[] = '//cdnjs.cloudflare.com/ajax/libs/flag-icon-css/0.8.4/css/flag-icon.min.css';

$js[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js';
$js[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js';
$js[] = '//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js';
$js[] = '//cdn.datatables.net/1.10.10/js/dataTables.bootstrap.min.js';
$js[] = '//cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js';
$js[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/3.1.1/bootstrap3-typeahead.min.js';
$js[] = '//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.12/clipboard.min.js';

$scripts = '
		$(function(){
			$(\'select\').selectpicker();
			$(\'input.price\').maskMoney();
			$(\'.date\').datepicker({
				todayHighlight: true,
				format: "yyyy-mm-dd",
				autoclose: true
			}).on(\'hide\', function(e){
				e.stopPropogation();
			});

			$(\'input.number\').on(\'input\', function(e){
				$(this).val($(this).val().replace(/[^0-9]/g, \'\'));
			});

			$(\'.add-ip-address\').on(\'click\', function(e){
				e.preventDefault();

				var $node = $(this).parent().parent();
				$node.clone(true).appendTo($node.parent()).find(\'input[type=text]\').val(\'\');

				$(this).html(\'<i class="fa fa-minus-circle"></i>\').off(\'click\').on(\'click\', function(e){
					e.preventDefault();
					$node.remove();
				});
			});

			$(\'#add-new-machine\').on(\'click\', function(e){
				e.preventDefault();

				$(\'#modal-machine input[name=id]\').val(\'\');
				$(\'#modal-machine .modal-title\').html(\'New Machine\');
				$(\'#modal-machine .btn-primary\').html(\'<span class="hidden-xs">Add Machine</span><span class="visible-xs"><i class="fa fa-save"></i></span>\');
				$(\'#modal-machine .btn-danger\').addClass(\'hidden\');
				$(\'#modal-machine .btn-danger\').removeAttr(\'data-id\');
				$(\'#modal-machine\').modal(\'show\');
			});

			$(\'#hide-inactive-vm\').on(\'click\', function(e){
				e.preventDefault();

				var $form = $(\'<form method="post" />\').html(\'<input type="hidden" name="hideInactiveVM" />\');
				$(\'body\').append($form);
				$form.submit();
			});

			$(\'#show-inactive-vm\').on(\'click\', function(e){
				e.preventDefault();

				var $form = $(\'<form method="post" />\').html(\'<input type="hidden" name="showInactiveVM" />\');
				$(\'body\').append($form);
				$form.submit();
			});

			$(\'#modal-machine\').on(\'hide.bs.modal\', function(){
				$(\'#modal-machine .has-error\').removeClass(\'has-error\');
				$(\'#modal-machine [name=isActive]\').parent().parent().parent().parent().addClass(\'hidden\');
				$(\'#response\').html(\'\');
				$(\'#modal-machine form\')[0].reset();
				$(\'#ip-address div\').not(\':last\').remove();
				$(\'select\').selectpicker(\'refresh\');
			});

			$(\'#modal-machine .btn-primary\').on(\'click\', function(e){
				e.preventDefault();

				$(\'#modal-machine .has-error\').removeClass(\'has-error\');

				var $btn = $(this);
				var width = $btn.width();
				var text = $btn.html();

				$btn.attr(\'disabled\', true).width(width).html(\'<i class="fa fa-refresh fa-spin"></i><span class="hidden-xs"> Saving...</span>\');

				$.post(\'' . getURL('save-machine.json') . '\', $(\'#modal-machine form\').serialize(), function(json){
					$btn.attr(\'disabled\', false).html(text);

					if(json.status == \'OK\'){
						window.location.href = window.location.href;
						return;
					}

					$(\'#modal-machine [name=\' + json.id + \']\').parent().addClass(\'has-error\');
					$(\'#response\').html(json.response);
				}, \'json\');
			});

			$(\'#modal-machine .btn-danger\').on(\'click\', function(e){
				e.preventDefault();

				var $btn = $(this);
				var width = $btn.width();
				var text = $btn.html();

				$btn.attr(\'disabled\', true).width(width).html(\'<i class="fa fa-refresh fa-spin"></i> Wait...\');

				var $form = $(\'<form method="post">\').html(\'<input type="hidden" name="remove" value="\' + $(this).attr(\'data-id\') +\'">\');
				$form.appendTo(\'body\');
				$form.submit();
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

			var clipboard;

			$(\'#machine-list\').dataTable({
				pageLength: 50,
				sDom: \'<"top"f>rt<"bottom"ip><"hidden-xs"l><"clear">\',
				stateSave: true,
				order: [[ 0, \'asc\' ]],
				\'fnRowCallback\': function(row){
					$(\'td:eq(8)\', row).hide();
				},
				\'initComplete\': function(){
					$(\'select\').selectpicker();
				},
				\'drawCallback\': function(settings){
					$(\'[data-toggle="tooltip"]\').tooltip();

					if(clipboard)
						clipboard.destroy();

					clipboard = new Clipboard(\'.machine-ip\', {
						text: function(trigger){
					        return $(trigger).text().trim();
					    }
					});

					clipboard.on(\'success\', function(e) {
					    $(e.trigger).fadeOut(100).fadeIn(500);
					});

					/*$(\'.ip-address-list .dropdown-menu a\').on(\'click\', function(e){
						e.stopPropagation();

						var ip = $(this).html();
						var $input = $(\'<input type="text" value="\' + ip + \'" style="border:0;padding:0;margin:0;background:transparent" class="text-center" readonly />\');

						$(this).parent().append($input);
						$input.on(\'focusout\', function(){
							$(this).prev().removeClass(\'hidden\');
							$(this).remove();
						}).select();

						$(this).addClass(\'hidden\');
					});

					$(\'.ip-address-list\').on(\'hide.bs.dropdown\', function(){
						$(this).find(\'input\').remove();
						$(this).find(\'.hidden\').removeClass(\'hidden\');
					});*/

					$(\'.edit-machine,.machine-name\').off(\'click\').on(\'click\', function(e){
						e.preventDefault();

						var id = $(this).attr(\'data-id\');

						$.getJSON(\'' . getURL('get-machine.json') . '\', { id: id })
						.done(function(data){
							if(data.status != \'OK\'){
								alert(data.response);
								window.location.href = window.location.href;
								return;
							}

							$(\'#modal-machine .modal-title\').html(data.label);
							$(\'#modal-machine [name=id]\').val(id);
							$(\'#modal-machine .btn-primary\').html(\'<span class="hidden-xs">Save Changes</span><span class="visible-xs"><i class="fa fa-save"></i></span>\');

							$(\'#modal-machine [name=isActive]\').parent().parent().parent().parent().removeClass(\'hidden\');
							$(\'#modal-machine [name=isActive]\').prop(\'checked\', (data.is_active) ? true : false);

							$(\'#modal-machine [name=label]\').val(data.label);
							$(\'#modal-machine [name=vmType]\').val(data.vm_type);
							$(\'#modal-machine [name=memory]\').val(data.total_ram);
							$(\'#modal-machine [name=memoryUnit]\').val(data.ram_unit);
							$(\'#modal-machine [name=swap]\').val(data.total_swap);
							$(\'#modal-machine [name=swapUnit]\').val(data.swap_unit);
							$(\'#modal-machine [name=diskSpace]\').val(data.total_disk_space);
							$(\'#modal-machine [name=diskSpaceUnit]\').val(data.disk_space_unit);
							$(\'#modal-machine [name=hddType]\').val(data.hdd_type);
							$(\'#modal-machine [name=bandwidth]\').val(data.total_bandwidth);
							$(\'#modal-machine [name=bandwidthUnit]\').val(data.bandwidth_unit);
							$(\'#modal-machine [name=countryCode]\').val(data.country_code);
							$(\'#modal-machine [name=cityName]\').val(data.city_name);
							$(\'#modal-machine [name=providerId]\').val(data.provider_id);
							$(\'#modal-machine [name=currencyCode]\').val(data.currency_code);
							$(\'#modal-machine [name=price]\').val(data.price);
							$(\'#modal-machine [name=billingCycle]\').val(data.billing_cycle);
							$(\'#modal-machine [name=dueDate]\').datepicker(\'update\', data.due_date);
							$(\'#modal-machine [name=notes]\').val(data.notes);

							$(\'#modal-machine [name=isNAT]\').prop(\'checked\', (data.is_nat) ? true : false);

							var parts = data.ip_address.split(\';\');
							var $node = $(\'#modal-machine [name=ipAddress\\\\[\\\\]]\').parent();

							$.each(parts, function(i, ip){
								$node.clone().appendTo($node.parent()).find(\'input[type=text]\').val(ip);
							});

							$node.remove();

							$(\'#ip-address .add-ip-address\').not(\':last\').html(\'<i class="fa fa-minus-circle"></i>\').off(\'click\').on(\'click\', function(e){
								e.preventDefault();
								$(this).parent().parent().remove();
							});

							$(\'#ip-address .add-ip-address:last\').on(\'click\', function(e){
								e.preventDefault();

								var $node = $(this).parent().parent();
								$node.clone(true).appendTo($node.parent()).find(\'input[type=text]\').val(\'\');

								$(this).html(\'<i class="fa fa-minus-circle"></i>\').off(\'click\').on(\'click\', function(e){
									e.preventDefault();
									$node.remove();
								});
							});

							$(\'#modal-machine .btn-danger\').removeClass(\'hidden\');
							$(\'#modal-machine .btn-danger\').attr(\'data-id\', id);

							$(\'select\').selectpicker(\'refresh\');
							$(\'#modal-machine\').modal(\'show\');
						});
					});

					$(\'.edit-provider\').off(\'click\').on(\'click\', function(e){
						e.preventDefault();

						var id = $(this).attr(\'data-id\');

						$.getJSON(\'' . getURL('get-provider.json') . '\', { id: id })
						.done(function(data){
							$(\'#modal-provider .modal-title\').html(data.name);
							$(\'#modal-provider [name=id]\').val(id);

							$(\'#modal-provider [name=name]\').val(data.name);
							$(\'#modal-provider [name=website]\').val(data.website);
							$(\'#modal-provider [name=controlPanel]\').val(data.control_panel);
							$(\'#modal-provider [name=cpUrl]\').val(data.cp_url);

							$(\'#modal-provider\').modal(\'show\');
						});
					});

					$(\'.renew-machine\').off(\'click\').on(\'click\', function(e){
						e.preventDefault();

						$.post(\'' . getURL('renew-machine.json') . '\', {id: $(this).parent().parent().parent().parent().parent().attr(\'data-id\')}, function(json){
							if(json.status == \'OK\'){
								window.location.href = window.location.href;
								return;
							}

							alert(json.response);
						}, \'json\');
					});
				},
				"columnDefs": [
					{
						"targets": \'no-sort\', "orderable": false
					},{
						type: \'non-empty-string\', targets: [7]
					},{
						"targets": 0, "render": function(data, type, row){
							if(type != \'sort\')
								return data;

							return data.replace(/<span.+span> /, \'\');
						}
					},{
						"targets": 1, "sType": "numeric", "render": function(data, type, row){
							switch(type){
								case \'sort\':
									var ip = data.split(\';\');

									var regex = /(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/gi;
									var match = regex.exec(ip[0]);
									var parts = match[1].split(\'.\');
									var int = parseInt(parts[0]) * Math.pow(256, 3) + parseInt(parts[1]) * Math.pow(256, 2) + parseInt(parts[2]) * 256 + parseInt(parts[3]);

									return int;

								case \'display\':
									var raw = data.replace(/<span.*/g, \'\');
									var isNAT = (raw.length != data.length) ? true : false;

									var parts = raw.split(\';\');

									if(parts.length == 1)
										return ((isNAT) ? \'<span class="label label-pill label-warning nat">NAT</span> \' : \'\') + \'<span class="machine-ip">\' + raw + \' <i class="fa fa-clipboard"></i></span>\';

									var html = \'<div class="dropdown ip-address-list">\' +
											   ((isNAT) ? \' <span class="label label-pill label-warning nat">NAT</span> \' : \'\') +
											   \'	<a href="#" data-toggle="dropdown">\' + parts[0] + \' <span class="caret"></span></a>\' +
											   \'	<ul class="dropdown-menu">\' +
											   \'		<li>&nbsp;&nbsp;<span class="machine-ip">\' + parts.join(\' <i class="fa fa-clipboard"></i></span></li><li>&nbsp;&nbsp;<span class="machine-ip">\') + \' <i class="fa fa-clipboard"></i></span></li>\' +
											   \'	</ul>\' +
											   \'</div>\';

									return html;
							}

							return data;
						}
					},{
						"targets": 2, "render": function(data, type, row){
							if(type != \'sort\')
								return data;

							var regex = /title="([^"]+).+span> (.+)/gi;
							var match = regex.exec(data);

							return (match[1] + " " + match[2]);
						}
					},{
						"targets": [3, 4], "sType": "numeric", "render": function(data, type, row){
							if(type != \'sort\')
								return data;

							data = data.replace(/<div.*/g, \'\');

							var parts = data.split(\' \');

							switch(parts[1]){
								case \'GB\':
									return (parseInt(parts[0]) * 1024);

								case \'TB\':
									return (parseInt(parts[0]) * 1024 * 1024);

								default:
									return parseInt(parts[0]);
							}
						}
					},{
						"targets": 5, "render": function(data, type, row){
							var parts = data.split(\';\');

							if(type != \'display\')
								return parts[1];

							if(parts.length != 5)
								return \'\';

							var html = \'<div class="dropdown">\' +
									   \'	<a href="#" data-toggle="dropdown">\' + parts[1] + \' <span class="caret"></span></a>\' +
									   \'	<ul class="dropdown-menu">\' +
									   \'		<li><a href="\' + parts[4] + \'" target="_blank"><i class="fa fa-tachometer"></i> \' + parts[3] + \'</a></li>\' +
									   \'		<li><a href="\' + parts[2] + \'" target="_blank"><i class="fa fa-globe"></i> Website</a></li>\' +
									   \'		<li><a href="javascript:;" data-id="\' + parts[0] + \'" class="edit-provider"><i class="fa fa-pencil-square-o"></i> Edit Provider</a></li>\' +
									   \'	</ul>\' +
									   \'</div>\';

							return html;
						}
					},{
						"targets": 6, "sType": "numeric", "render": function(data, type, row){
							switch(type){
								case \'display\':
									var parts = data.split(\' \');

									switch(parts[0]){
										case \'2\':
											var row = { color:\'#ffcc66\', text:\'Quarterly\' };
											break;

										case \'3\':
											var row = { color:\'#99cc00\', text:\'Semi-Yearly\' };
											break;

										case \'4\':
											var row = { color:\'#ff6699\', text:\'Yearly\' };
											break;

										case \'5\':
											var row = { color:\'#808080\', text:\'Bi-Yearly\' };
											break;

										default:
											var row = { color:\'#00ccff\', text:\'Monthly\' };
									}

									return \'<span class="label" style="background:\' + row.color + \'" data-toggle="tooltip" data-placement="top" title="\' + row.text + \'">\' + row.text.substr(0, 1) + \'</span>\' + \' \' + parts[1] + \' \' + parts[2];

								case \'sort\':
									var parts = data.split(\' \');

									switch(parts[1]){';

foreach($config['currencies'] as $currency){
	$scripts .= '

									case \'' . $currency['label'] . '\':
										var price = parseFloat(parts[2]) * ' . $currency['rate'] . ';
										break;';
}

$scripts .= '

										default:
											var price = parseFloat(parts[2]);
									}

									switch(parts[0]){
										case \'2\':
											return (price / 3);

										case \'3\':
											return (price / 6);

										case \'4\':
											return (price / 12);

										case \'5\':
											return (price / 24);

										default:
											return price;
									}

									break;
								default:
									return data;
							}
						}
					},{
						"targets": 7, "sType": "numeric", "render": function(data, type, row){
							if(type != \'display\')
								return data;

							if(!data)
								return data;

							var now = new Date();
							var date = new Date(data * 1000);
							var day = (date.getUTCDate() < 10 ? \'0\' : \'\') + date.getUTCDate();
							var month = (date.getUTCMonth() < 9 ? \'0\' : \'\') + (date.getUTCMonth() + 1);
							var year = date.getUTCFullYear();

							if(date > now){
								var days = \'Expires on \' + parseInt((date - now) / 86400 / 1000) + \' days later\';
							}

							var html = \'<div class="dropdown" data-toggle="tooltip" title="\' + ((date < now) ? \'Expired\' : days) + \'">\' +
									   \'	<a href="#" data-toggle="dropdown"\' + ((now > date) ? \' class="text-danger"\' : \'\') + \'>\' + ((now > date) ? \'<i class="fa fa-exclamation-circle"></i> \' : \'\') + year + \'-\' + month + \'-\' + day + \' <span class="caret"></span></a>\' +
									   \'	<ul class="dropdown-menu">\' +
									   \'		<li><a href="javascript:;" class="renew-machine"><i class="fa fa-refresh"></i> Renew</a></li>\' +
									   \'	</ul>\' +
									   \'</div>\';

							return html;
						}
					}
				]
			});

			$.get(\'' . getURL('get-location.json') . '\', function(data){
				$(\'input[name=cityName]\').typeahead({ source:data });
			}, \'json\');

			$.get(\'' . getURL('get-control-panel.json') . '\', function(data){
				$(\'input[name=controlPanel]\').typeahead({ source:data });
			}, \'json\');
		});

		$.extend($.fn.dataTableExt.oSort, {
			\'non-empty-string-asc\': function (str1, str2){
				if(str1 == \'\')
					return 1;
				if(str2 == \'\')
					return -1;
				return ((str1 < str2) ? -1 : ((str1 > str2) ? 1 : 0));
			},

			\'non-empty-string-desc\': function (str1, str2) {
				if(str1 == \'\')
					return 1;
				if(str2 == \'\')
					return -1;
				return ((str1 < str2) ? 1 : ((str1 > str2) ? -1 : 0));
			}
		} );';

$vmTypes = [
	'1'	=> [
		'code'	=> 'OVZ',
		'name'	=> 'OpenVZ',
		'class'	=> 'success',
	],
	'2'	=> [
		'code'	=> 'KVM',
		'name'	=> 'KVM',
		'class'	=> 'primary',
	],
	'3'	=> [
		'code'	=> 'XEN',
		'name'	=> 'XEN',
		'class'	=> 'danger',
	],
	'4'	=> [
		'code'	=> 'VMW',
		'name'	=> 'VMware',
		'class'	=> 'info',
	],
	'5'	=> [
		'code'	=> 'DED',
		'name'	=> 'Dedicated',
		'class'	=> 'default',
	],
];

$hddTypes = [
	'1'	=> [
		'code'	=> 'HDD',
		'class'	=> 'default',
	],
	'2'	=> [
		'code'	=> 'SSHD',
		'class'	=> 'success',
	],
	'3'	=> [
		'code'	=> 'SAS',
		'class'	=> 'info',
	],
	'4'	=> [
		'code'	=> 'SSD',
		'class'	=> 'danger',
	],
];

$machine = new SimpleDB(DATABASES . 'machine_' . $config['key'] . '.db', 'machine_id');
$provider = new SimpleDB(DATABASES . 'provider_' . $config['key'] . '.db', 'provider_id');

if(isset($_POST['remove'])){
	$machine->delete('machine_id', '=' . $_POST['remove']);
	$session->set('response', '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span><i class="fa fa-check-circle"></i> The machine has been removed.</span></div>');
}

if(isset($_POST['hideInactiveVM']))
	$session->set('showInactiveVM', NULL);

if(isset($_POST['showInactiveVM']))
	$session->set('showInactiveVM', TRUE);

require_once INCLUDES . 'header.php';
?>

		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<p>
						<button class="btn btn-default pull-left" id="add-new-machine"><i class="fa fa-plus-circle"></i> Add<span class="hidden-xs"> New Machine</span></button>

						<?php
						if($session->get('showInactiveVM'))
							echo '<button class="btn btn-warning pull-right" id="hide-inactive-vm" title="Hide inactive VM" data-toggle="tooltip"><i class="fa fa-eye-slash"></i> <span class="hidden-xs">Hide Inactive VM</span></button>';

						else
							echo '<button class="btn btn-success pull-right" id="show-inactive-vm" title="Show inactive VM" data-toggle="tooltip"><i class="fa fa-eye"></i> <span class="hidden-xs">Show Inactive VM</span></button>';
						?>

						<div class="clearfix"></div>
					</p>
					<?php
					if($session->get('response')){
						echo $session->get('response');
						$session->set('response', NULL);
					}
					?>
					<hr />
					<?php
					$rows = $machine->select('*', '*', 'label');

					if($machine->affectedRows() > 0){
						echo '
						<table id="machine-list" class="table table-hover nowrap">
							<thead>
								<tr>
									<th class="col-md-3 col-xs-11">Label</th>
									<th class="hidden-xs">IP Address</th>
									<th class="hidden-xs">Location</th>
									<th class="hidden-xs">Memory</th>
									<th class="hidden-xs">HDD</th>
									<th class="hidden-xs">Provider</th>
									<th class="hidden-xs">Price</th>
									<th class="hidden-xs">Due</th>
									<th class="no-sort hidden"></th>
									<th class="no-sort"></th>
								</tr>
							</thead>
							<tbody>';


						foreach($rows as $row){
							if(!$session->get('showInactiveVM'))
								if(!$row['is_active'])
									continue;

							$data = $provider->select('provider_id', '=' . $row['provider_id']);

							if($provider->affectedRows() == 0)
								continue;

							echo '
								<tr data-id="' . $row['machine_id'] . '"'  . (($row['is_active']) ? '' : ' class="active"') . '>
									<td>
										<span class="label label-pill label-' . $vmTypes[$row['vm_type']]['class'] . '">' . $vmTypes[$row['vm_type']]['code'] . '</span> <span class="machine-name" data-id="' . $row['machine_id'] . '">' . $row['label'] . '</span>
										<div class="visible-xs location"><span data-toggle="tooltip" data-placement="top" title="' . $row['city_name'] . ', ' . getCountryNameByCode($row['country_code']) . '"><span class="flag-icon flag-icon-' . strtolower($row['country_code']) . '"></span> ' . $row['city_name'] . '</span></div>
										<div class="visible-xs">
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
									<td class="hidden-xs">' . $row['ip_address'] . (($row['is_nat']) ? '<span class="nat">NAT</span>' : '') . '</td>
									<td class="hidden-xs"><span data-toggle="tooltip" data-placement="top" title="' . $row['city_name'] . ', ' . getCountryNameByCode($row['country_code']) . '"><span class="flag-icon flag-icon-' . strtolower($row['country_code']) . '"></span> ' . $row['city_name'] . '</span></td>
									<td class="hidden-xs">' . $row['total_ram'] . ' ' . strtoupper($row['ram_unit']) . '<div class="swap">' . (($row['total_swap']) ? ($row['total_swap'] . ' ' . strtoupper($row['swap_unit'])) : '') . '</div></td>
									<td class="hidden-xs">' . $row['total_disk_space'] . ' ' . strtoupper($row['disk_space_unit']) . '<span class="label label-pill label-hdd label-' . $hddTypes[$row['hdd_type']]['class'] . '">' . $hddTypes[$row['hdd_type']]['code'] . '</span></td>
									<td class="hidden-xs">' . implode(';', $data[0]) . '</td>
									<td class="hidden-xs">' . $row['billing_cycle'] . ' ' . strtoupper($row['currency_code']) . ' ' . $row['price'] . '</td>
									<td class="hidden-xs">' . (($row['due_date']) ? strtotime($row['due_date']) : '') . '</td>
									<td>' . $row['notes'] . ',' . getCountryNameByCode($row['country_code']) . ',' . $row['country_code'] . '</td>
									<td class="col-md-1 text-right"><a href="javascript:;" data-id="' . $row['machine_id'] . '" class="edit-machine"><i class="fa fa-pencil-square-o"></i></a></td>
								</tr>';
						}

						echo '
							</tbody>
						</table>';
					}
					else{
						echo '
						<div class="alert alert-info alert-dismissible">
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<span><i class="fa fa-exclamation-circle"></i> There is no machine added.</span>
						</div>';
					}
					?>
				</div>
			</div>
		</div>

		<div id="modal-machine" class="modal" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">New Machine</h4>
					</div>

					<div class="modal-body">
						<form role="form" method="post">
							<div id="response"></div>

							<input type="hidden" name="id">

							<div class="row hidden">
								<div class="form-group col-md-6">
									<div class="checkbox">
										<label><input type="checkbox" name="isActive" value="" checked>Active</label>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-md-9">
									<label>Label</label>
									<input type="text" name="label" class="form-control" maxlength="100">
								</div>

								<div class="form-group col-md-3">
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
								<div class="form-group col-md-4">
									<label>Memory</label>
									<div class="input-group">
										<span class="input-group-btn full-width">
											<input type="number" name="memory" class="form-control number">
										</span>
										<span class="input-group-btn">
											<select name="memoryUnit" class="form-control" data-width="60px">
												<option value="mb"> MB</option>
												<option value="gb"> GB</option>
												<option value="tb"> TB</option>
											</select>
										</span>
									</div>
								</div>
								<div class="form-group col-md-4">
									<label>Disk Space</label>
									<div class="input-group">
										<span class="input-group-btn full-width">
											<input type="number" name="diskSpace" class="form-control number">
										</span>
										<span class="input-group-btn">
											<select name="diskSpaceUnit" class="form-control" data-width="60px">
												<option value="mb"> MB</option>
												<option value="gb" selected> GB</option>
												<option value="tb"> TB</option>
											</select>
										</span>
									</div>
								</div>
								<div class="form-group col-md-4">
									<label>Bandwidth</label>
									<div class="input-group">
										<span class="input-group-btn full-width">
											<input type="number" name="bandwidth" class="form-control number">
										</span>
										<span class="input-group-btn">
											<select name="bandwidthUnit" class="form-control" data-width="60px">
												<option value="mb"> MB</option>
												<option value="gb" selected> GB</option>
												<option value="tb"> TB</option>
											</select>
										</span>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-md-4">
									<label>Swap</label>
									<div class="input-group">
										<span class="input-group-btn full-width">
											<input type="number" name="swap" class="form-control number">
										</span>
										<span class="input-group-btn">
											<select name="swapUnit" class="form-control" data-width="60px">
												<option value="mb"> MB</option>
												<option value="gb"> GB</option>
												<option value="tb"> TB</option>
											</select>
										</span>
									</div>
								</div>
								<div class="form-group col-md-4">
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
								<div class="form-group col-md-4"></div>
							</div>

							<div class="row">
								<div class="form-group col-md-6">
									<div class="checkbox">
										<label><input type="checkbox" name="isNAT" value="">NAT VPS</label>
									</div>
								</div>
							</div>

							<div id="ip-address" class="form-group">
								<label>IP Address</label>

								<div class="input-group col-md-6" style="margin-bottom:3px">
									<input type="text" name="ipAddress[]" class="form-control" maxlength="15">

									<span class="input-group-btn">
										<button class="btn btn-default add-ip-address" type="button"><i class="fa fa-plus-circle"></i></button>
									</span>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-md-6">
									<label>Country</label>

									<select name="countryCode" class="form-control" data-live-search="true" data-none-selected-text="">
										<option value=""></option>
										<?php
										foreach($countries as $cc => $cn)
											echo '
											<option value="' . $cc . '"> ' . $cn . '</option>';
										?>
									</select>
								</div>
								<div class="form-group col-md-6">
									<label>City</label>
									<input type="text" name="cityName" class="form-control" autocomplete="off">
								</div>
							</div>

							<div class="row">
								<div class="form-group col-md-6">
									<label>Provider</label>
									<select name="providerId" class="form-control" data-live-search="true" data-none-selected-text="">
										<?php
										$rows = $provider->select('*', '*', 'name');

										if($provider->affectedRows() > 0)
											foreach($rows as $row)
												echo '
												<option value="' . $row['provider_id'] . '"> ' . $row['name'] . '</option>';
										?>
									</select>
								</div>
								<div class="form-group col-md-6">
									<label>Price</label>
									<div class="input-group">
										<span class="input-group-btn">
											<select name="currencyCode" class="form-control" data-width="70px">
												<?php
												foreach($config['currencies'] as $code => $currency)
													echo '
													<option value="' . $code . '"> ' . $currency['label'] . '</option>';
												?>
											</select>
										</span>
										<span class="input-group-btn full-width">
											<input type="text" name="price" class="form-control price">
										</span>
										<span class="input-group-btn">
											<select name="billingCycle" class="form-control" data-width="105px">
												<option value="1"> Monthly</option>
												<option value="2"> Quarterly</option>
												<option value="3"> Semi-Yearly</option>
												<option value="4"> Yearly</option>
												<option value="5"> Bi-Yearly</option>
											</select>
										</span>
									</div>
								</div>
								<div class="form-group col-md-6">
									<label>Due Date</label>
									<div class="input-group date">
										<input type="text" name="dueDate" class="form-control date" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
								<div class="form-group col-md-12">
									<label>Notes</label>
									<textarea name="notes" class="form-control"></textarea>
								</div>
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
							<button type="button" class="btn btn-primary">
								<span class="hidden-xs">Add Machine</span>
								<span class="visible-xs"><i class="fa fa-save"></i></span>
							</button>
						</div>

					</div>
				</div>
			</div>
		</div>

		<div id="modal-provider" class="modal" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"></h4>
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
						<button type="button" class="btn btn-default" data-dismiss="modal">
							<span class="hidden-xs">Cancel</span>
							<span class="visible-xs"><i class="fa fa-close"></i></span>
						</button>
						<button type="button" class="btn btn-primary"><span class="hidden-xs">Save Changes</span><span class="visible-xs"><i class="fa fa-save"></i></span></button>
					</div>
				</div>
			</div>
		</div>
<?php
require_once INCLUDES . 'footer.php';
?>
