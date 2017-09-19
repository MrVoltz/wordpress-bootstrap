<?php

require_once "base.php";

/* HEADER MENU */
function bs_configure_header_menu($args) {
	add_theme_support("menus");

	function bs_header_menu_register() {
		global $bs_config;

		register_nav_menu("header-menu", $bs_config["header-menu"]["name"]);
	}
	add_action("after_setup_theme", "bs_header_menu_register");

	function bs_header_menu() {
		global $bs_config;

		wp_nav_menu([
			"theme_location"  => "header-menu",
			"container"       => $bs_config["header-menu"]["container"],
			"container_class" => $bs_config["header-menu"]["container_class"],
			"container_id"    => $bs_config["header-menu"]["container_id"],
			"menu_class"      => $bs_config["header-menu"]["class"],
			"menu_id"         => $bs_config["header-menu"]["id"],
			"echo"            => true,
			"before"          => "",
			"after"           => "",
			"link_before"     => $bs_config["header-menu"]["link_before"],
			"link_after"      => $bs_config["header-menu"]["link_after"],
			"depth"           => 0,
		]);
	}

	if($args["first_class"] || $args["last_class"]) {
		function bs_header_menu_first_last($items) {
			global $bs_config;

				$items[1]->classes[] = $bs_config["header-menu"]["first_class"];
				$items[count($items)]->classes[] = $bs_config["header-menu"]["last_class"];
				return $items;
		}
		add_filter("wp_nav_menu_objects", "bs_header_menu_first_last");
	}

	if($args["active_class"]) {
		function bs_header_menu_active($classes) {
			global $bs_config;

			if(in_array("current-menu-item", $classes))
				$classes[] = $bs_config["header-menu"]["active_class"];

				return $classes;
		}
		add_filter("nav_menu_css_class", "bs_header_menu_active");
	}
}
bs_register_feature("header-menu", "bs_configure_header_menu", [
	"name" => "Main menu",
	"class" => "header-menu",
	"id" => "",

	"link_before" => "",
	"link_after" => "",

	"container" => false,
	"container_class" => "header-menu-container",
	"container_id" => "",

	"first_class"     => false,
	"last_class"      => false,
	"active_class"    => false
]);

/* CUSTOM BACKGROUND */
function bs_configure_custom_background($args) {
	add_theme_support("custom-background", [
		"default-color" => $args["color"],
		"default-size" => $args["size"],
		"default-position-x" => $args["position_x"],
		"default-position-y" => $args["position_y"],
		"default-repeat" => $args["repeat"],
		"default-attachment" => $args["attachment"],
		"default-image" => $args["image"] ? bs_url($args["image"]) : "",
		"wp-head-callback" => $args["callback"]
	]);

	function bs_get_custom_background_css() {
		$background = set_url_scheme(get_background_image());
		$color      = get_background_color();

		if($color === get_theme_support('custom-background', 'default-color'))
				$color = false;

		if(!$background && !$color)
				return "";

		$style = $color ? "background-color: #$color;" : '';

		if($background) {
				$image      = " background-image: url(" . wp_json_encode($background) . ");";

				$position_x = get_theme_mod('background_position_x', get_theme_support('custom-background', 'default-position-x'));
				$position_y = get_theme_mod('background_position_y', get_theme_support('custom-background', 'default-position-y'));
				if(!in_array($position_x, array(
						'left',
						'center',
						'right'
				), true))
						$position_x = 'left';

				if(!in_array($position_y, array(
						'top',
						'center',
						'bottom'
				), true))
						$position_y = 'top';
				$position = " background-position: $position_x $position_y;";

				$size = get_theme_mod('background_size', get_theme_support('custom-background', 'default-size'));
				if(!in_array($size, array(
						'auto',
						'contain',
						'cover'
				), true))
						$size = 'auto';
				$size   = " background-size: $size;";

				$repeat = get_theme_mod('background_repeat', get_theme_support('custom-background', 'default-repeat'));
				if(!in_array($repeat, array(
						'repeat-x',
						'repeat-y',
						'repeat',
						'no-repeat'
				), true))
						$repeat = 'repeat';
				$repeat     = " background-repeat: $repeat;";

				$attachment = get_theme_mod('background_attachment', get_theme_support('custom-background', 'default-attachment'));
				if('fixed' !== $attachment)
						$attachment = 'scroll';

				$attachment = " background-attachment: $attachment;";
				$style .= $image . $position . $size . $repeat . $attachment;
		}

		return $style;
	}
}
bs_register_feature("custom-background", "bs_configure_custom_background", [
	"color" => "",
	"size" => "",
	"position_x" => "",
	"position_y" => "",
	"repeat" => "",
	"attachment" => "",
	"image" => "",
	"callback" => "_custom_background_cb"
]);

/* CUSTOM POST TYPES */
function bs_configure_custom_post_types($args) {
	function bs_custom_post_types() {
		global $bs_config;

		foreach($bs_config["custom-post-types"] as $key => $type) {
			$type = bs_defaults($type, [
				"public" => true
			]);

			if(isset($type["name"]))
				$type["labels"]["name"] = $type["name"];

			if(isset($type["slug"]))
				$type["rewrite"]["slug"] = $type["slug"];

			if(isset($type["admin_only"]) && $type["admin_only"]) {
				$type["public"] = false;
				$type["show_ui"] = true;
			}

			register_post_type($key, array_omit($type, [ "slug", "name", "admin_only" ]));
		}
	}
	add_action("init", "bs_custom_post_types");
}
bs_register_feature("custom-post-types", "bs_configure_custom_post_types");

/* POST THUMBNAILS */
function bs_configure_post_thumbnails($args) {
	add_theme_support("post-thumbnails", bs_to_array($args["post_types"]));

	foreach(bs_to_array($args["post_types"]) as $type)
		if(!post_type_supports($type, "thumbnail"))
			add_post_type_support($type, "thumbnail");

	foreach($args["image_sizes"] as $key => $size)
		add_image_size($key, $size["width"], $size["height"], $size["crop"]);
}
bs_register_feature("post-thumbnails", "bs_configure_post_thumbnails", [
	"post_types" => [ "post" ],
	"image_sizes" => []
]);

/* REMOVE ADMIN LINKS */
function bs_configure_remove_admin_links($args) {
	function bs_remove_admin_links() {
		global $bs_config;

		foreach($bs_config["remove-admin-links"] as $link) {
			if(is_array($link))
				remove_submenu_page($link[0], $link[1]);
			else
				remove_menu_page($link);
		}
	}
	add_action("admin_menu", "bs_remove_admin_links");
}
bs_register_feature("remove-admin-links", "bs_configure_remove_admin_links", []);

/* OPTIMIZE */
function bs_configure_optimize($args) {
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'feed_links', 2);
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'parent_post_rel_link', 10, 0);
	remove_action('wp_head', 'start_post_rel_link', 10, 0);
	remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
	remove_action('wp_head', 'rel_canonical');
	remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

	if(!$args["support_emoji"]) {
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');
		add_filter('emoji_svg_url', '__return_false');
	}

	add_filter('the_generator', '__return_false');

	if(!$args["show_admin_bar"])
		add_filter('show_admin_bar','__return_false');

}
bs_register_feature("optimize", "bs_configure_optimize", [
	"show_admin_bar" => true,
	"support_emoji" => false
]);

/* POSTS PER PAGE */
function bs_configure_posts_per_page($args) {
	function bs_posts_per_page($query) {
		global $bs_config;

		if(is_admin() || !$query->is_main_query())
				return;

		if(is_home() && isset($bs_config["posts-per-page"]["home"])) {
			$query->set("posts-per-page", $bs_config["posts-per-page"]["home"]);
			return;
		}

		if(!$query->is_post_type_archive)
			return;

		$post_type = $query->get('post_type');
		if(is_array($post_type))
			$post_type = reset($post_type);
		$post_type_object = get_post_type_object($post_type);

		if(isset($bs_config["posts-per-page"][$post_type_object->name]))
			$query->set("posts-per-page", $bs_config["posts-per-page"][$post_type_object->name]);
	}
	add_action("pre_get_posts", "bs_posts_per_page");
}
bs_register_feature("posts-per-page", "bs_configure_posts_per_page");

/* SCRIPTS */
function bs_configure_scripts($args) {
	if(count($args["frontend"]) > 0) {
		function bs_scripts_frontend() {
			global $bs_config, $post;

			foreach(bs_assets_parse($bs_config["scripts"]["frontend"], $bs_config["scripts"]["prefix"], [ "condition", "footer" ]) as $name => $info) {
				wp_register_script($name, $info["url"], $info["deps"], false, (bool) $info["footer"]);

				if(!$info["condition"] || bs_check_condition($info["condition"], $post->post_type, $post->ID))
					wp_enqueue_script($name);
			}
		}
		add_action("wp_enqueue_scripts", "bs_scripts_frontend");
	}

	if(count($args["admin"]) > 0) {
		function bs_scripts_admin($hook) {
			global $bs_config;

			foreach(bs_assets_parse($bs_config["scripts"]["admin"], $bs_config["scripts"]["prefix"], [ "footer" ]) as $name => $info)
				wp_enqueue_script($name, $info["url"], $info["deps"], false, (bool) $info["footer"]);
		}
		add_action("admin_enqueue_scripts", "bs_scripts_admin");
	}
}
bs_register_feature("scripts", "bs_configure_scripts", [
	"prefix" => "",
	"frontend" => [],
	"admin" => [],
	"editor" => []
]);

/* STYLES */
function bs_configure_styles($args) {
	if(count($args["frontend"]) > 0) {
		function bs_styles_frontend() {
			global $bs_config, $post;

			foreach(bs_assets_parse($bs_config["styles"]["frontend"], $bs_config["styles"]["prefix"], [ "condition" ]) as $name => $info) {
				wp_register_style($name, $info["url"], $info["deps"], (bool) $info["footer"]);

				if(!$info["condition"] || bs_check_condition($info["condition"], $post->post_type, $post->ID))
					wp_enqueue_style($name);
			}
		}
		add_action("wp_enqueue_scripts", "bs_styles_frontend");
	}

	if(count($args["admin"]) > 0) {
		function bs_styles_admin($hook) {
			global $bs_config;

			foreach(bs_assets_parse($bs_config["styles"]["admin"], $bs_config["styles"]["prefix"]) as $name => $info)
				wp_enqueue_style($name, $info["url"], $info["deps"]);
		}
		add_action("admin_enqueue_scripts", "bs_styles_admin");
	}

	if(count($args["editor"]) > 0)
		foreach($args["editor"] as $style)
			add_editor_style(bs_url($style));
}
bs_register_feature("styles", "bs_configure_styles", [
	"prefix" => "",
	"frontend" => [],
	"admin" => [],
	"editor" => []
]);

/* OPTIONS PAGES */
function bs_configure_options_pages($args) {
	function bs_options_pages_parse_options($options, $prefix="") {
		$out = [];
		foreach($options as $section => $option) {
			if(is_array($option)) {
				$out = array_merge($out, bs_options_pages_parse_options($option, $prefix.$section."_"));
			} else {
				$out[] = $prefix.$option;
			}
		}
		return $out;
	}

	function bs_options_pages_parse($callback, $page) {
		$page = bs_defaults($page, [
			"parent" => null,
			"capability" => "administrator",
			"slug" => $callback,
			"section" => "",
			"options" => [],
			"callback" => $callback
		]);

		$page["options"] = bs_options_pages_parse_options($page["options"], $page["section"] ? ($page["section"]."_") : "");

		return $page;
	}

	function bs_options_pages_locate($slug) {
		global $bs_config;

		foreach($bs_config["options-pages"] as $callback => $page) {
			$page = bs_options_pages_parse($callback, $page);

			if($page["slug"] === $slug) {
				return $page;
			}
		}

		return null;
	}

	function bs_options_pages_admin_menu() {
		global $bs_config;

		foreach($bs_config["options-pages"] as $callback => $page) {
			$page = bs_options_pages_parse($callback, $page);

			add_menu_page($page["title"], $page["title"], $page["capability"], $page["slug"], "bs_options_pages_render");
		}
	}
	add_action("admin_menu", "bs_options_pages_admin_menu");

	function bs_options_pages_register_settings() {
		global $bs_config;

		foreach($bs_config["options-pages"] as $callback => $page) {
			$page = bs_options_pages_parse($callback, $page);

			foreach($page["options"] as $option) {
				register_setting($page["section"], $option);
			}
		}
	}
	add_action("admin_init", "bs_options_pages_register_settings");

	function bs_options_pages_render() {
		$page = bs_options_pages_locate($_GET['page']);

		if(!$page || !current_user_can($page["capability"])) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>'.$page["title"].'</h1>';
		echo '<form method="post" action="options.php">';

		settings_fields($page["section"]);
		do_settings_sections($page["section"]);

		$page["callback"]();

		submit_button();

		echo '</form>';
		echo '</div>';
	}

	function bso_row($label) {
		echo '<tr valign="top"><th scope="row">'.$label.'</th><td>';
	}
	function bso_row_end() {
		echo '</td></tr>';
	}

	function bso_editor($name, $value, $args=[]) {
		global $editor_styles;

		$args = bs_defaults($args, [
			"use_styles" => true
		]);

		if(!$args["use_styles"]) {
			$old_styles = $editor_styles;
			$editor_styles = [];
		}

		wp_editor($value, $name, $args);

		if(!$args["use_styles"])
			$editor_styles = $old_styles;
	}
}
bs_register_feature("options-pages", "bs_configure_options_pages");

/* ACF */
function bs_configure_acf($args) {
	if(!$args["show_in_admin"])
		define("ACF_LITE", true);

	require_once get_template_directory()."/advanced-custom-fields/acf.php";
}
bs_register_feature("acf", "bs_configure_acf", [
	"show_in_admin" => false
]);

/* BODY CLASS SLUG */
function bs_configure_body_class_slug() {
	function bs_body_class_slug($classes) {
		global $post;

		if(isset($post))
			$classes[] = $post->post_type."-".$post->post_name;

		return $classes;
	}
	add_filter("body_class", "bs_body_class_slug");
}
bs_register_feature("body-class-slug", "bs_configure_body_class_slug");

/* ASSETS */
function bs_configure_assets() {
	function bs_asset($path, $echo=false) {
		global $bs_config;

		$url = bs_url(rtrim($bs_config["assets"]["path"], "/")."/".ltrim($path, "/"));

		if(!$echo)
			return $url;

		echo $url;
	}
}
bs_register_feature("assets", "bs_configure_assets", [
	"path" => ""
]);

/* === DEPRECATED === */

/* OPTIONS */
function bs_configure_options($args) {
	function bs_options_register() {
		global $bs_config;

		foreach($bs_config["options"] as $section_name => $section)
			foreach($section as $option)
				register_setting($section_name, $section_name."_".bs_to_value($option));
	}
	add_action("admin_init", "bs_options_register");

	function bso_attrs($args, $allowed, $echo=true) {
		$attrs = [];
		foreach($args as $arg => $value) {
			if(!in_array($arg, $allowed))
				continue;
			$attrs[] = $arg.'="'.esc_attr($value).'"';
		}

		if($echo)
			echo implode(" ", $attrs);
		else
			return implode(" ", $attrs);
	}

	function bso_image_uploader($option, $label, $args=[]) {
		$args = bs_defaults($args, [ "preview-width" => 200, "preview-height" => 200 ]);

		bso_field($label);
		echo '<input type="hidden" name="'.$option.'" class="image_path" value="'.esc_attr(get_option($option)).'">';
		echo '<input type="button" value="Nahrát obrázek" class="button-primary upload_image">';
		echo '<div id="show_upload_preview">';

		if(!empty(get_option($option))) {
			echo '<img src="'.esc_attr(get_option($option)).'" style="margin: 10px 10px 0 0;max-width:'.$args["preview-width"].'px;max-height:'.$args["preview-height"].'px;">';
			echo '<input type="button" value="Odebrat obrázek" class="button-secondary remove_image" style="margin-top:10px">';
		}

		echo '</div>';

		bso_field_end($label);
	}
}
bs_register_feature("options", "bs_configure_options");
