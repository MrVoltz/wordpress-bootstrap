<?php

require_once "base.php";

$bs_config["meta_boxes"] = [];

function bs_add_meta_box($id, $args=[]) {
	global $bs_config;

	$info = bs_defaults($args, [
		"title" => "My Metabox",
		"condition" => true,
		"callback_prefix" => null,
		"display_callback" => "__return_false",
		"save_callback" => "__return_false",
		"enqueue_callback" => "__return_false",
		"priority" => "low"
	]);

	$info["id"] = $id;

	if($info["callback_prefix"]) {
		$info["display_callback"] = $info["callback_prefix"]."display";
		$info["save_callback"] = $info["callback_prefix"]."save";
		$info["enqueue_callback"] = $info["callback_prefix"]."enqueue";
	}

	$bs_config["meta_boxes"][$id] = $info;
}

function bs_meta_boxes_add($post_type, $post) {
	global $bs_config;

	foreach($bs_config["meta_boxes"] as $id => $box) {
		if(!bs_check_condition($box["condition"], $post_type, $post))
			continue;

		add_meta_box($id, $box["title"], "bs_meta_boxes_display", $post_type, "normal", $box["priority"], [ $id ]);
	}
}
add_action("add_meta_boxes", "bs_meta_boxes_add", 10, 2);

function bs_meta_boxes_display($post, $box) {
	global $bs_config;

	$id = $box["args"][0];

	wp_nonce_field($id, $id."_nonce");

	$info = $bs_config["meta_boxes"][$id];
	$info["__box"] = $box;

	if(is_callable($info["display_callback"]))
		$info["display_callback"]($post, $info);
}

function bs_meta_boxes_save($post_id) {
	global $bs_config;

	if(!current_user_can("edit_post", $post_id))
		return;

	if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
		return;

	foreach($bs_config["meta_boxes"] as $id => $box) {
		if(!isset($_POST[$id."_nonce"]) || !wp_verify_nonce($_POST[$id."_nonce"], $id))
			continue;

		if(is_callable($box["save_callback"]))
			$box["save_callback"](get_post($post_id), $box);
	}
}
add_action("save_post", "bs_meta_boxes_save");

function bs_meta_boxes_enqueue($hook) {
	global $bs_config, $post;

	if(!in_array($hook, [ "post.php", "post-new.php" ]))
		return;

	foreach($bs_config["meta_boxes"] as $id => $box) {
		if(!bs_check_condition($box["condition"], $post->post_type, $post))
			continue;

		$box["__hook"] = $hook;

		if(is_callable($box["enqueue_callback"]))
			$box["enqueue_callback"]($post, $box);
	}
}
add_action("admin_enqueue_scripts", "bs_meta_boxes_enqueue");

/* REPEATER BOX */

function bs_repeater_scripts($table_class, $options=[]) {
	$options = bs_defaults($options, [
		"sortable" => false,
		"row_template" => "",
		"max_items" => -1,

		"before_script" => "",
		"after_script" => "",
		"before_add" => "",
		"after_add" => "",
		"after_remove" => ""
	]);

	if($options["sortable"])
		$options["after_script"] = '
			$table.sortable({
				items: "tr:not(.dr-metabox-header)",
				stop: reindex
			});
		'.$options["after_script"];

	if($options["max_items"] > 0) {
		$options["before_script"] = '
			function updateButton() {
				$table.next(".dr-metabox-add").toggleClass("disabled", $("tr:not(.dr-metabox-header)", $table).length >= '.$options["max_items"].');
			}
		'.$options["before_script"];

		$options["before_add"] = '
			if($("tr:not(.dr-metabox-header)", $table).length >= '.$options["max_items"].')
				return;
		'.$options["before_add"];

		$options["after_add"] = 'updateButton();'.$options["after_add"];
		$options["after_script"] = 'updateButton();'.$options["after_script"];
	}

	echo '
		<script type="text/javascript">
			jQuery(function($) {
				var $table = $(".'.$table_class.'");

				'.$options["before_script"].'

				$table.next(".dr-metabox-add").on("click", function(e) {
					e.preventDefault();

					'.$options["before_add"].'

					$table.append('.json_encode($options["row_template"]).');
					$("tr", $table).last().find("input,textarea").first().focus();

					'.$options["after_add"].'
					reindex();
				});

				$table.on("click", ".dr-metabox-remove", function(e) {
					e.preventDefault();

					$(this).closest("tr").remove();
					'.$options["after_remove"].'
					reindex();
				}).on("keydown", "input", function(e) {
					if(e.which === 13) { // enter
						e.preventDefault();
						$table.next(".dr-metabox-add").click();
					}
				});

				function reindex() {
					$("tr", $table).each(function(index, $row) {
						$("[name]", $row).each(function() {
							$(this).attr("name", $(this).attr("name").replace(/\[[0-9]*\]/, "[" + (index-1) + "]"));
						});
					});
				}

				'.$options["after_script"].'
			});
		</script>
	';
}
