jQuery(function($) {

	var file_frame;

	$(".bso-iu-change-image").click(function(e) {
		e.preventDefault();

		var $t = $(this);

		if (file_frame)
			file_frame.close();

		file_frame = wp.media.frames.file_frame = wp.media({
			title: $(this).data('uploader-title'),
			button: {
				text: $(this).data('uploader-button-text'),
			},
			multiple: false
		});

		file_frame.on('select', function() {
			attachment = file_frame.state().get('selection').first().toJSON();

			console.log(attachment);

			var src = attachment.type === "video" ? attachment.image : attachment.sizes.thumbnail.url;

			$t.siblings("input").val(attachment.id);

			var $img = $t.siblings(".bso-iu-image-preview");

			$img.attr("src", attachment.url);
			$img.siblings(".bso-iu-remove-image").addBack().show();
		});

		console.log("opening frame");

		file_frame.open();
	});

	$(".bso-iu-remove-image").click(function(e) {
		e.preventDefault();

		$(this).siblings("input").val("");
		$(this).siblings(".bso-iu-image-preview").addBack().hide();
	});

});
