jQuery(function($) {

	var file_frame;

	$(".gallery-metabox").on('click', 'a.gm-add', function(e) {
		e.preventDefault();

		var $box = $(this).closest(".gallery-metabox");

		if (file_frame)
			file_frame.close();

		file_frame = wp.media.frames.file_frame = wp.media({
			title: $(this).data('uploader-title'),
			button: {
				text: $(this).data('uploader-button-text')
			},
			multiple: "toggle"
		});

		file_frame.on('open', function() {
			var selection = file_frame.state().get("selection");
			$('.gm-list input[type=hidden]', $box).each(function() {
				selection.add(wp.media.attachment($(this).val()));
			});
		});

		file_frame.on('select', function() {
			var listIndex = $('.gm-list li', $box).index($('#gallery-metabox-list li:last')),
				selection = file_frame.state().get('selection');

			selection.map(function(attachment, i) {
				attachment = attachment.toJSON(),
				index      = listIndex + (i + 1);

				if($('.gm-list input[type=hidden][value="' + attachment.id + '"]', $box).length)
					return;

				var src = attachment.type === "video" ? attachment.image : attachment.sizes.thumbnail.url;

				$('.gm-list', $box).append('<li><input type="hidden" name="' + $box.data("meta-key") + '[' + index + ']" value="' + attachment.id + '"><img class="gm-image-preview" src="' + src + '"><a class="gm-change-image button button-small" href="#" data-uploader-title="Změnit obrázek" data-uploader-button-text="Změnit obrázek">Změnit</a><small><a class="gm-remove-image" href="#">Odstranit</a></small></li>');
			});
		});

		makeSortable();

		file_frame.open();
	});

	$(document).on('click', '.gallery-metabox a.gm-change-image', function(e) {
		e.preventDefault();

		var that = $(this);

		if (file_frame) file_frame.close();

		file_frame = wp.media.frames.file_frame = wp.media({
			title: $(this).data('uploader-title'),
			button: {
				text: $(this).data('uploader-button-text'),
			},
			multiple: false
		});

		file_frame.on( 'select', function() {
			attachment = file_frame.state().get('selection').first().toJSON();

			console.log(attachment);

			var src = attachment.type === "video" ? attachment.image : attachment.sizes.thumbnail.url;

			that.parent().find('input:hidden').attr('value', attachment.id);
			that.parent().find('img.gm-image-preview').attr('src', src);
		});

		file_frame.open();

	});

	function resetIndex() {
		$('.gm-list li').each(function(i) {
			var meta_key = $(this).closest("gallery-metabox").data("meta-key");
			$(this).find('input:hidden').attr('name', meta_key + '[' + i + ']');
		});
	}

	function makeSortable() {
		$('.gm-list').sortable({
			opacity: 0.6,
			stop: function() {
				resetIndex();
			}
		});
	}

	$(document).on('click', '.gallery-metabox a.gm-remove-image', function(e) {
		e.preventDefault();

		$(this).parents('li').animate({ opacity: 0 }, 200, function() {
			$(this).remove();
			resetIndex();
		});
	});

	makeSortable();

});
