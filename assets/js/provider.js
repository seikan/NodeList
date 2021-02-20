$(function() {
	$('#add-new-provider').on('click', function(e) {
		e.preventDefault();

		$('#modal-provider input[name=id]').val('');
		$('#modal-provider .modal-title').html('New Provider');
		$('#modal-provider .btn-primary').html('<i class="fa fa-save"></i> <span class="d-none d-md-inline">Add Provider</span>');
		$('#modal-provider .btn-danger').addClass('d-none').removeAttr('data-id');
		$('#modal-provider').modal('show');
	});

	$('#modal-provider').on('hide.bs.modal', function() {
		$('#modal-provider .is-invalid').removeClass('is-invalid');
		$('#response').html('');
		$('#modal-provider form')[0].reset();
	});

	$('#modal-provider .btn-primary').on('click', function(e) {
		e.preventDefault();

		$('#modal-provider .is-invalid').removeClass('is-invalid');

		var $btn = $(this);
		var width = $btn.width();
		var text = $btn.html();

		$btn.attr('disabled', true).width(width).html('<i class="fa fa-refresh fa-spin"></i><span class="d-none d-md-inline"> Saving...</span>');

		$.post(url.save_provider, $('#modal-provider form').serialize(), function(json) {
			if(json.status == 'OK') {
				window.location.href = window.location.href;
				return;
			}

			$('#modal-provider [name="' + json.name + '"]').addClass('is-invalid');
			$('#response').html(json.response);
		}, 'json')
			.fail(function() {
				alert('Network connection issue encountered. Please try again later.');
			})
			.always(function() {
				$btn.attr('disabled', false).html(text);
			});
	});

	$('#modal-provider .btn-danger').on('click', function(e) {
		e.preventDefault();

		var $btn = $(this);
		var width = $btn.width();
		var text = $btn.html();

		$btn.attr('disabled', true).width(width).html('<i class="fa fa-refresh fa-spin"></i> Wait...');

		var $form = $('<form method="post">').html('<input type="hidden" name="remove" value="' + $(this).attr('data-id') +'">');
		$form.appendTo('body');
		$form.submit();
	});

	$('#provider-list').dataTable({
		pageLength: 50,
		dom: '<"pull-right"f>rt<"bottom pull-right"ip>',
		stateSave: true,
		order: [[ 0, 'asc' ]],
		columnDefs: [{
			targets: 'no-sort', orderable: false
		}],
		language: {
			search: '',
			searchPlaceholder: 'Search Provider...',
			info: '',
    		infoEmpty: '',
			infoFiltered: '',
		},
		'drawCallback': function() {
			$('select').selectpicker();

			$('.edit-provider').off('click').on('click', function(e) {
				e.preventDefault();

				var id = $(this).attr('data-id');

				$.getJSON(url.get_provider, { id: id })
					.done(function(data) {
						if(data.status != 'OK') {
							alert(data.response);
							window.location.href = window.location.href;
							return;
						}

						$('#modal-provider .modal-title').html(data.name);
						$('#modal-provider [name="id"]').val(id);
						$('#modal-provider .btn-primary').html('<i class="fa fa-save"></i> <span class="d-none d-md-inline">Save Changes</span>');
						$('#modal-provider [name=name]').val(data.name);
						$('#modal-provider [name=website]').val(data.website);
						$('#modal-provider [name=controlPanel]').val(data.control_panel);
						$('#modal-provider [name=cpUrl]').val(data.cp_url);
						$('#modal-provider .btn-danger').removeClass('d-none').attr('data-id', id);

						$('#modal-provider').modal('show');
					});
			});
		}
	});

	$.get(url.get_control_panel, function(data) {
		$('input[name="controlPanel"]').typeahead({ source: data });
	}, 'json');
});