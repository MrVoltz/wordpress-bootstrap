<?php

require_once "base.php";

function bs_url($path, $echo = false)
{
    $url = bs_is_absolute($path) ? $path : (get_template_directory_uri() . "/" . ltrim($path, "/"));

    if (!$echo) {
        return $url;
    }

    echo $url;
}

function bs_title($echo = false, $sep = "&ndash;")
{
    if (is_front_page()) {
        $title = get_bloginfo("name");
    } else {
        $title = wp_title($sep, false, "right") . get_bloginfo("name");
    }

    if (!$echo) {
        return $title;
    }

    echo $title;
}

function bs_build_shortcode($name, $args = [], $content = null)
{
    $shortcode = "[" . $name;

    foreach ($args as $key => $value) {
        $shortcode .= " " . $key . "=\"" . $value . "\"";
    }

    if ($content === null) {
        return $shortcode . "]";
    }

    return $shortcode . "]" . $content . "[/" . $name . "]";
}

function bs_default($value, $default, $echo = false)
{
    if (is_null($value) || $value === "" || $value === false) {
        $value = $default;
    }

    if (!$echo) {
        return $value;
    }

    echo $value;
}

function bs_locate_page($slug, $title, $create = null)
{
    $page_id = get_option("bs_pageid_" . $slug);

    if ($page_id) {
        $page = get_post((int) $page_id);
    }

    if ($create !== true && !$page) {
        $page = get_page_by_path($slug);

        if ($page) {
            update_option("bs_pageid_" . $slug, $page->ID);
        }
    }

    if ($create !== false && !$page) {
        $page_id = wp_insert_post([
            "post_type"   => "page",
            "post_name"   => $slug,
            "post_status" => "publish",
            "post_title"  => $title,
            "post_author" => get_users([
                "role"   => "administrator",
                "number" => 1,
            ])[0]->ID,
        ]);

        update_option("bs_pageid_" . $slug, $page_id);
        $page = get_post($page_id);
    }

    return $page;
}

function bs_load_page($slug, $title, $reset_postdata = true)
{
    global $post;

    if ($reset_postdata) {
        wp_reset_postdata();
    }

    $post = bs_locate_page($slug, $title);
    setup_postdata($post);
}

function bs_page_permalink($query = null, $echo = false)
{
    if ($query === null) {
        $url = home_url();
    } else if (is_numeric($query) || $query instanceof WP_Post) {
        $url = get_permalink($query);
    } else {
        $url = get_permalink(get_page_by_path($query));
    }

    if (!$echo) {
        return $url;
    }

    echo $url;
}

function bs_update_post($post)
{
    global $bs_config;

    $bs_config["meta_boxes_save_disabled"] = true;
    $res                                   = wp_update_post($post);
    $bs_config["meta_boxes_save_disabled"] = false;

    return $res;
}

function bs_enqueue_admin_assets()
{
    function bs_admin_assets_enqueue()
    {
        wp_eneuque_media();

        wp_enqueue_script("bootstrap-admin-css", bs_url("bootstrap/admin.css"), [], false);
        wp_enqueue_script("bootstrap-admin-js", bs_url("bootstrap/admin.js"), ["jquery"], false, true);
    }
    add_action("admin_enqueue_scripts", "bs_admin_assets_enqueue");
}
