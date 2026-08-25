jQuery(function ($) {
	$('.rle-select-btn').on('click', function (e) {
		e.preventDefault();
		var $slot = $(this).closest('.rle-logo-slot');
		var frame = wp.media({
			title: 'Select Logo Image',
			button: { text: 'Use this image' },
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$slot.find('.rle-id-field').val(attachment.id);
			$slot.find('.rle-preview').attr('src', attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url).show();
			$slot.find('.rle-remove-btn').show();
		});

		frame.open();
	});

	$('.rle-remove-btn').on('click', function (e) {
		e.preventDefault();
		var $slot = $(this).closest('.rle-logo-slot');
		$slot.find('.rle-id-field').val('');
		$slot.find('.rle-preview').hide();
		$(this).hide();
	});
});
