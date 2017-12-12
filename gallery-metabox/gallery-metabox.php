<?php
/*
 * @author    Daan Vos de Wael
 * @copyright Copyright (c) 2013, Daan Vos de Wael, http://www.daanvosdewael.com
 * @license   http://en.wikipedia.org/wiki/MIT_License The MIT License
*/

require_once __DIR__."/../bootstrap/meta-boxes.php";

$bs_config["gallery_metaboxes"] = [];

function bs_add_gallery_meta_box($id, $args=[]) {
	global $bs_config;

	$box = $bs_config["gallery_metaboxes"][$id] = bs_defaults($args, [
		"title" => "Gallery",
		"condition" => true,
		"priority" => "high",
		"meta_key" => "gallery_photos"
	]);

	bs_add_meta_box($id, [
		"title" => $box["title"],
		"condition" => $box["condition"],
		"priority" => $box["priority"],
		"callback_prefix" => "bs_gallery_metabox_"
	]);
}

function bs_gallery_metabox_get_photos($post_id=null, $meta_key="gallery_photos", $thumbnail_size="thumbnail", $full_size="full") {
	if($post_id === null)
		$post_id = get_the_ID();

	$photos = get_post_meta($post_id, $meta_key, true) ?: [];

	foreach($photos as $i => $id) {
		$thumb = wp_get_attachment_image_src($id, $thumbnail_size);
		$full = wp_get_attachment_image_src($id, $full_size);

		$photos[$i] = [
			"src" => $full[0],

			"thumbnail" => [
				"src" => $thumb[0],
				"width" => $thumb[1],
				"height" => $thumb[2]
			],
			"full" => [
				"src" => $full[0],
				"width" => $full[1],
				"height" => $full[2]
			]
		];
	}

	return $photos;
}

function bs_gm_get_photos($post_id=null, $meta_key="gallery_photos", $thumbnail_size="thumbnail", $full_size="full") {
	return bs_gallery_metabox_get_photos($post_id, $meta_key, $thumbnail_size, $full_size);
}

function bs_gallery_metabox_get_config($box) {
	global $bs_config;

	return $bs_config["gallery_metaboxes"][$box["id"]];
}

function bs_gallery_metabox_enqueue($post, $box) {
	wp_enqueue_script("gallery-metabox", bs_url("gallery-metabox/js/gallery-metabox.js"), [ "jquery", "jquery-ui-sortable" ]);
	wp_enqueue_style("gallery-metabox", bs_url("/gallery-metabox/css/gallery-metabox.css"));
}

function bs_gallery_metabox_display($post, $box) {
	$config = bs_gallery_metabox_get_config($box);
	$meta_key = $config["meta_key"];
	$ids = get_post_meta($post->ID, $meta_key, true) ?: [];
?>
	<div class="gallery-metabox" data-meta-key="<?=$meta_key?>">
		<a class="gm-add button" href="#" data-uploader-title="Přidat obrázky" data-uploader-button-text="Přidat obrázky">Přidat obrázky</a>

		<ul class="gm-list">
		<?php foreach($ids as $key => $value): $image = wp_get_attachment_image_src($value); ?>
			<li>
				<input type="hidden" name="<?=$meta_key?>[<?=$key?>]" value="<?=$value?>">
				<img class="gm-image-preview" src="<?=$image[0]?>" width="<?=$image[1]?>" height="<?=$image[2]?>">
				<a class="gm-change-image button button-small" href="#" data-uploader-title="Změnit obrázek" data-uploader-button-text="Změnit obrázek">Změnit</a>
				<small><a class="gm-remove-image" href="#">Odstranit</a></small>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
<?php }

function bs_gallery_metabox_save($post, $box) {
	$config = bs_gallery_metabox_get_config($box);
	$meta_key = $config["meta_key"];

	if(isset($_POST[$meta_key]))
		update_post_meta($post->ID, $meta_key, $_POST[$meta_key]);
	else
		delete_post_meta($post->ID, $meta_key);
}
