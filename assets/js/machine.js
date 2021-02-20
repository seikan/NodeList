var clipboard;

$(function() {
	$('#add-new-machine').on('click', function(e) {
		e.preventDefault();

		$('#modal-machine input[name=id]').val('');
		$('#modal-machine .modal-title').html('New Machine');
		$('#modal-machine .btn-primary').html('<i class="fa fa-save"></i> <span class="d-none d-md-inline">Add Machine</span>');
		$('#modal-machine .btn-danger').addClass('d-none');
		$('#modal-machine .btn-danger').removeAttr('data-id');
		$('#modal-machine').modal('show');
	});

	$('#hide-inactive-vm').on('click', function(e) {
		e.preventDefault();

		var $form = $('<form method="post" />').html('<input type="hidden" name="hideInactiveVM" />');
		$('body').append($form);
		$form.submit();
	});

	$('#show-inactive-vm').on('click', function(e) {
		e.preventDefault();

		var $form = $('<form method="post" />').html('<input type="hidden" name="showInactiveVM" />');
		$('body').append($form);
		$form.submit();
	});

	$('#modal-machine').on('hide.bs.modal', function() {
		$('#modal-machine .is-invalid').removeClass('is-invalid');
		$('#modal-machine [name="isActive"]').parent().parent().parent().addClass('d-none');
		$('#response').html('');
		$('#modal-machine form')[0].reset();
		$('#ip-address div').not(':last').remove();
		$('select').selectpicker('refresh');
	});

	$('#modal-machine').on('shown.bs.modal', function() {
		$('select').selectpicker('destroy').selectpicker({
			noneSelectedText: '(Empty)',
		});

		$('input.price').maskMoney('destroy').maskMoney();

		$('.date').datepicker('destroy').datepicker({
			todayHighlight: true,
			format: 'yyyy-mm-dd',
			autoclose: true
		}).on('hide', function() {
			return false;
		});

		$('input.number').off('input').on('input', function() {
			$(this).val($(this).val().replace(/[^0-9]/g, ''));
		});

		$('.add-ip-address').off('click').on('click', function(e) {
			e.preventDefault();

			var $node = $(this).parent().parent();
			$node.clone(true).appendTo($node.parent()).find('input[type=text]').val('');

			$(this).html('<i class="fa fa-minus-circle"></i>').off('click').on('click', function(e) {
				e.preventDefault();
				$node.remove();
			});
		});
	});

	$('#modal-machine .btn-primary').on('click', function(e) {
		e.preventDefault();

		$('#modal-machine .is-invalid').removeClass('is-invalid');

		var $btn = $(this);
		var width = $btn.width();
		var text = $btn.html();

		$btn.attr('disabled', true).width(width).html('<i class="fa fa-refresh fa-spin"></i><span class="d-xs-none"> Saving...</span>');

		$.post(url.save_machine, $('#modal-machine form').serialize(), function(json) {
			$btn.attr('disabled', false).html(text);

			if (json.status == 'OK') {
				window.location.href = window.location.href;
				return;
			}

			$('#modal-machine [name="' + json.name + '"]').addClass('is-invalid');
			$('#response').html(json.response);
		}, 'json');
	});

	$('#modal-machine .btn-danger').on('click', function(e) {
		e.preventDefault();

		var $btn = $(this);
		var width = $btn.width();
		var text = $btn.html();

		$btn.attr('disabled', true).width(width).html('<i class="fa fa-refresh fa-spin"></i> Wait...');

		var $form = $('<form method="post">').html('<input type="hidden" name="remove" value="' + $(this).attr('data-id') +'">');
		$form.appendTo('body');
		$form.submit();
	});

	$('#modal-provider .btn-primary').on('click', function(e) {
		e.preventDefault();

		$('#modal-provider .is-invalid').removeClass('is-invalid');

		var $btn = $(this);
		var width = $btn.width();
		var text = $btn.html();

		$btn.attr('disabled', true).width(width).html('<i class="fa fa-refresh fa-spin"></i><span class="d-xs-none"> Saving...</span>');

		$.post(url.save_provider, $('#modal-provider form').serialize(), function(json) {
			$btn.attr('disabled', false).html(text);

			if (json.status == 'OK') {
				window.location.href = window.location.href;
				return;
			}

			$('#modal-provider [name="' + json.name + '"]').addClass('is-invalid');
			$('#response').html(json.response);
		}, 'json');
	});

	$('#machine-list').dataTable({
		pageLength: 50,
		//sDom: '<"top"f>rt<"bottom"ip><"d-xs-none"l><"clear">',
		dom: '<"pull-right"f>rt<"bottom pull-right"ip>',
		stateSave: true,
		order: [[ 0, 'asc' ]],
		language: {
			search: '',
			searchPlaceholder: 'Search Machine...',
			info: '',
    		infoEmpty: '',
			infoFiltered: '',
		},
		/*'fnRowCallback': function(row) {
			$('td:eq(8)', row).hide();
		},
		'initComplete': function() {
			$('select').selectpicker();
		},*/
		'drawCallback': function() {
			$('[data-toggle="tooltip"]').tooltip();

			if (clipboard) {
				clipboard.destroy();
			}

			clipboard = new Clipboard('.machine-ip', {
				text: function(trigger) {
					if ($(trigger).data('clipboard-text')) {
						return $(trigger).data('clipboard-text');
					}

					return $(trigger).text().trim();
				}
			});

			clipboard.on('success', function(e) {
				$(e.trigger).fadeOut(100).fadeIn(500);
			});

			$('.edit-machine, .machine-name').off('click').on('click', function(e) {
				e.preventDefault();

				var id = $(this).attr('data-id');

				$.getJSON(url.get_machine, { id: id })
				.done(function(data) {
					if (data.status != 'OK') {
						alert(data.response);
						window.location.href = window.location.href;
						return;
					}

					$('#modal-machine .modal-title').html(data.label);
					$('#modal-machine [name=id]').val(id);
					$('#modal-machine .btn-primary').html('<i class="fa fa-save"></i> <span class="d-none d-md-inline">Save Changes</span>');

					$('#modal-machine [name=isActive]').parent().parent().parent().removeClass('d-none');
					$('#modal-machine [name=isActive]').prop('checked', (data.is_active) ? true : false);

					$('#modal-machine [name=label]').val(data.label);
					$('#modal-machine [name=vmType]').val(data.vm_type);
					$('#modal-machine [name=memory]').val(data.total_ram);
					$('#modal-machine [name=memoryUnit]').val(data.ram_unit);
					$('#modal-machine [name=swap]').val(data.total_swap);
					$('#modal-machine [name=swapUnit]').val(data.swap_unit);
					$('#modal-machine [name=diskSpace]').val(data.total_disk_space);
					$('#modal-machine [name=diskSpaceUnit]').val(data.disk_space_unit);
					$('#modal-machine [name=hddType]').val(data.hdd_type);
					$('#modal-machine [name=bandwidth]').val(data.total_bandwidth);
					$('#modal-machine [name=bandwidthUnit]').val(data.bandwidth_unit);
					$('#modal-machine [name=countryCode]').val(data.country_code);
					$('#modal-machine [name=cityName]').val(data.city_name);
					$('#modal-machine [name=providerId]').val(data.provider_id);
					$('#modal-machine [name=currencyCode]').val(data.currency_code);
					$('#modal-machine [name=price]').val(data.price);
					$('#modal-machine [name=billingCycle]').val(data.billing_cycle);
					$('#modal-machine [name=dueDate]').val(data.due_date);
					$('#modal-machine [name=notes]').val(data.notes);

					$('#modal-machine [name="isNat"]').prop('checked', (data.is_nat) ? true : false);

					var parts = data.ip_address.split(';');
					var $node = $('#modal-machine [name="ipAddress\\[\\]"]').parent();

					$.each(parts, function(i, ip) {
						$node.clone().appendTo($node.parent()).find('input[type=text]').val(ip);
					});

					$node.remove();

					$('#ip-address .add-ip-address').not(':last').html('<i class="fa fa-minus-circle"></i>').off('click').on('click', function(e) {
						e.preventDefault();
						$(this).parent().parent().remove();
					});

					$('#ip-address .add-ip-address:last').on('click', function(e) {
						e.preventDefault();

						var $node = $(this).parent().parent();
						$node.clone(true).appendTo($node.parent()).find('input[type=text]').val('');

						$(this).html('<i class="fa fa-minus-circle"></i>').off('click').on('click', function(e) {
							e.preventDefault();
							$node.remove();
						});
					});

					$('#modal-machine .btn-danger').removeClass('d-none');
					$('#modal-machine .btn-danger').attr('data-id', id);

					$('select').selectpicker('refresh');
					$('#modal-machine').modal('show');
				});
			});

			$('.edit-provider').off('click').on('click', function(e) {
				e.preventDefault();

				var id = $(this).attr('data-id');

				$.getJSON(url.get_provider, { id: id })
				.done(function(data) {
					$('#modal-provider .modal-title').html(data.name);
					$('#modal-provider [name=id]').val(id);

					$('#modal-provider [name=name]').val(data.name);
					$('#modal-provider [name=website]').val(data.website);
					$('#modal-provider [name=controlPanel]').val(data.control_panel);
					$('#modal-provider [name=cpUrl]').val(data.cp_url);

					$('#modal-provider').modal('show');
				});
			});

			$('.renew-machine').off('click').on('click', function(e) {
				e.preventDefault();

				$.post(url.renew_machine, { id: $(this).parent().parent().parent().parent().data('id') }, function(json) {
					if (json.status == 'OK') {
						window.location.href = window.location.href;
						return;
					}

					alert(json.response);
				}, 'json');
			});
		},
		"columnDefs": [
			{
				targets: 'no-sort', orderable: false
			},
			{
				targets: [7], type: 'non-empty-string'
			},
			{
				targets: 0, render: function(data, type, row) {
					if (type != 'sort') {
						return data;
					}

					return data.replace(/<span.+span> /, '');
				}
			},
			{
				targets: 1, sType: 'numeric', render: function(data, type, row) {
					switch(type) {
						case 'sort':
							var ip = data.split(';');

							var regex = /(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/gi;
							var match = regex.exec(ip[0]);
							var parts = match[1].split('.');
							var int = parseInt(parts[0]) * Math.pow(256, 3) + parseInt(parts[1]) * Math.pow(256, 2) + parseInt(parts[2]) * 256 + parseInt(parts[3]);

							return int;

						case 'display':
							var raw = data.replace(/<span.*/g, '');
							var isNat = (raw.length != data.length) ? true : false;

							var parts = raw.split(';');

							if (parts.length == 1) {
								return ((isNat) ? '<span class="badge badge-warning nat">NAT</span> ' : '') + '<span class="machine-ip" data-clipboard-text="' + raw + '">' + ((raw.length > 18) ? (raw.substr(0, 6) + '...' + raw.substr(-2)) : raw) + ' <i class="fa fa-clipboard"></i></span>';
							}

							var html =
								'<div class="dropdown ip-address-list">' +
								((isNat) ? '	<span class="badge badge-warning nat">NAT</span> ' : '') +
								'	<a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">' + ((parts[0].length > 18) ? (parts[0].substr(0, 6) + '...' + parts[0].substr(-2)) : parts[0]) + '</a>' +
								'	<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">' +
								'		<a href="javascript:;" class="dropdown-item"><span class="machine-ip">' + parts.join(' <i class="fa fa-clipboard"></i></span></li><li><span class="machine-ip">') + ' <i class="fa fa-clipboard"></i></span></a>' +
								'	</div>' +
								'</div>';

							return html;
					}

					return data;
				}
			},
			{
				targets: 2, render: function(data, type, row) {
					if (type != 'sort') {
						return data;
					}

					var regex = /title="([^"]+).+span> (.+)/gi;
					var match = regex.exec(data);

					return (match[1] + " " + match[2]);
				}
			},
			{
				targets: [3, 4], sType: "numeric", render: function(data, type, row) {
					if (type != 'sort') {
						return data;
					}

					data = data.replace(/<div.*/g, '');

					var parts = data.split(' ');

					switch(parts[1]) {
						case 'GB':
							return (parseInt(parts[0]) * 1024);

						case 'TB':
							return (parseInt(parts[0]) * 1024 * 1024);

						default:
							return parseInt(parts[0]);
					}
				}
			},
			{
				targets: 5, render: function(data, type, row) {
					var parts = data.split(';');

					if (type != 'display') {
						return parts[1];
					}

					if (parts.length != 5) {
						return '';
					}

					var html =
						'<div class="dropdown">' +
						'	<a href="#" data-toggle="dropdown">' + parts[1] + ' <span class="caret"></span></a>' +
						'	<ul class="dropdown-menu">' +
						'		<li><a href="' + parts[4] + '" target="_blank"><i class="fa fa-tachometer"></i> ' + parts[3] + '</a></li>' +
						'		<li><a href="' + parts[2] + '" target="_blank"><i class="fa fa-globe"></i> Website</a></li>' +
						'		<li><a href="javascript:;" data-id="' + parts[0] + '" class="edit-provider"><i class="fa fa-pencil-square-o"></i> Edit Provider</a></li>' +
						'	</ul>' +
						'</div>';

					var html =
						'<div class="dropdown">' +
						'	<a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">' + parts[1] + '</a>' +
						'	<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">' +
						'		<a href="' + parts[4] + '" class="dropdown-item" target="_blank"><i class="fa fa-tachometer"></i> ' + parts[3] + '</a>' +
						'		<a href="' + parts[2] + '" class="dropdown-item" target="_blank"><i class="fa fa-globe"></i> Website</a>' +
						'		<a href="javascript:;" data-id="' + parts[0] + '" class="dropdown-item edit-provider"><i class="fa fa-pencil-square-o"></i> Edit Provider</a>' +
						'	</div>' +
						'</div>';

					return html;
				}
			},
			{
				targets: 6, sType: "numeric", render: function(data, type, row) {
					switch(type) {
						case 'display':
							var parts = data.split(' ');

							switch(parts[0]) {
								case '2':
									var row = { color:'primary', text:'Quarterly' };
									break;

								case '3':
									var row = { color:'secondary', text:'Semi-Yearly' };
									break;

								case '4':
									var row = { color:'danger', text:'Yearly' };
									break;

								case '5':
									var row = { color:'warning', text:'Bi-Yearly' };
									break;

								default:
									var row = { color:'success', text:'Monthly' };
							}

							return '<span class="badge badge-' + row.color + '" data-toggle="tooltip" data-placement="top" title="' + row.text + '">' + row.text.substr(0, 1) + '</span>' + ' ' + parts[1] + ' ' + parts[2];

						case 'sort':
							var parts = data.split(' ');
							var price = parseFloat(parts[2]);

							$.each(currencies, function(key, item) {
								if (key == parts[1]) {
									price = parseFloat($parts[2] * item.rate);
								}
							});

							switch(parts[0]) {
								case '2':
									return (price / 3);

								case '3':
									return (price / 6);

								case '4':
									return (price / 12);

								case '5':
									return (price / 24);

								default:
									return price;
							}

						default:
							return data;
					}
				}
			},
			{
				targets: 7, sType: "numeric", render: function(data, type, row) {
					if (type != 'display') {
						return data;
					}

					if (!data) {
						return data;
					}

					var now = new Date();
					var date = new Date(data * 1000);
					var nextWeek = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
					var day = (date.getUTCDate() < 10 ? '0' : '') + date.getUTCDate();
					var month = (date.getUTCMonth() < 9 ? '0' : '') + (date.getUTCMonth() + 1);
					var year = date.getUTCFullYear();

					if (date > now) {
						var days = 'Expires on ' + parseInt((date - now) / 86400 / 1000) + ' days later';
					}

					var html =
						'<div class="dropdown" data-toggle="tooltip" title="' + ((date < now) ? 'Expired' : days) + '">' +
						'	<a class="dropdown-toggle' + ((now > date) ? ' text-danger' : ((nextWeek > date) ? ' text-warning' : '')) + '" href="#" role="button" data-toggle="dropdown">' + ((now > date) ? '<i class="fa fa-exclamation-circle"></i> ' : ((nextWeek > date) ? '<i class="fa fa-exclamation"></i> ' : '')) + year + '-' + month + '-' + day + '</a>' +
						'	<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">' +
						'		<a href="javascript:;" class="dropdown-item renew-machine"><i class="fa fa-refresh"></i> Renew</a>' +
						'	</div>' +
						'</div>';

					return html;
				}
			}
		]
	});

	$.get(url.get_location, function(data) {
		$('input[name="cityName"]').typeahead({ source:data });
	}, 'json');

	$.get(url.get_control_panel, function(data) {
		$('input[name="controlPanel"]').typeahead({ source:data });
	}, 'json');
});

$.fn.selectpicker.Constructor.BootstrapVersion = '4';

$.extend($.fn.dataTableExt.oSort, {
	'non-empty-string-asc': function (str1, str2) {
		if (str1 == '') {
			return 1;
		}

		if (str2 == '') {
			return -1;
		}

		return ((str1 < str2) ? -1 : ((str1 > str2) ? 1 : 0));
	},
	'non-empty-string-desc': function (str1, str2) {
		if (str1 == '') {
			return 1;
		}

		if (str2 == '') {
				return -1;
		}

		return ((str1 < str2) ? 1 : ((str1 > str2) ? -1 : 0));
	}
});