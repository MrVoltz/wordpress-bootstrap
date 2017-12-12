<?php

require_once "main.php";

/* UTIL FUNCTIONS */

function bs_is_login()
{
    global $pagenow;

    return $pagenow === "wp-login";
}

function bs_is_absolute($url)
{
    return substr($url, 0, 5) === "http:" || substr($url, 0, 6) === "https:" || substr($url, 0, 4) === "ftp:";
}

function bs_to_array($value)
{
    return is_array($value) ? $value : [$value];
}

function bs_to_value($value)
{
    return is_array($value) ? $value[0] : $value;
}

function bs_array_omit($array, $keys)
{
    return array_diff_key($array, array_flip($keys));
}

function bs_array_compact($array) {
    return array_filter($array, function($val) {
        return (bool) $val;
    });
}

function bs_defaults($array, $defaults, $filter = false)
{
    if (!$filter) {
        return wp_parse_args($array, $defaults);
    }

    return array_intersect_key(wp_parse_args($array, $defaults), $defaults);
}

function bs_regex_match($pattern, $subject, $flags = 0)
{
    $matches = [];
    if (preg_match($pattern, $subject, $matches, $flags)) {
        return $matches;
    }

    return null;
}

/* SHARED FUNCTIONS */

function bs_check_condition($condition, $post_type, $post, $hook = null)
{
    if ($condition === true) {
        return true;
    }

    if (is_callable($condition)) {
        return $condition($post_type, $post);
    }

    if (!is_array($condition) || !count($condition)) {
        return false;
    }

    switch ($condition[0]) {
        case "post_type":
            return in_array($post_type, array_slice($condition, 1));
        case "page_template":
            return in_array(get_page_template_slug($post), array_slice($condition, 1));
        case "hook":
            return in_array($hook, array_slice($condition, 1));
        case "is_front_page":
            return get_option("show_on_front") === "page" && get_option("page_on_front") === $post->ID;

        default:
            return false;
    }
}

function bs_assets_parse($array, $prefix = "", $attributes = [])
{
    $assets = [];

    foreach ($array as $name => $info) {
        $info = bs_to_array($info);

        $asset = [];

        $asset["url"]  = bs_url($info[0]);
        $asset["deps"] = count($info) > 1 ? bs_to_array($info[1]) : [];

        if (is_numeric($name)) {
            $name = bs_regex_match("/([^\\/]+?)\.[^\\/]+?$/", $asset["url"])[1];
        }

        $name = $prefix . $name;

        foreach ($attributes as $attr) {
            $asset[$attr] = isset($info[$attr]) ? $info[$attr] : null;
        }

        $assets[$name] = $asset;
    }

    foreach ($assets as $name => $asset) {
        foreach ($asset["deps"] as $i => $dep) {
            if (!isset($assets[$dep]) && isset($assets[$prefix . $dep])) {
                $assets[$name]["deps"][$i] = $prefix . $dep;
            }

        }
    }

    return $assets;
}

/* BOOTSTRAP */

$bs_config = [
    "registered_features" => [],
];

function bs_register_feature($feature, $callback, $defaults = null)
{
    global $bs_config;

    $bs_config["registered_features"][$feature] = [
        "defaults"           => $defaults,
        "configure_callback" => $callback,
    ];
}

function bs_configure($feature, $options = [])
{
    global $bs_config;

    if (!isset($bs_config["registered_features"][$feature])) {
        throw new Error("Can't configure '$feature': feature doesn't exist");
    }

    if (isset($bs_config[$feature])) {
        throw new Error("Can't configure '$feature': feature is already configured");
    }

    $desc = $bs_config["registered_features"][$feature];
    $args = $desc["defaults"] !== null ? bs_defaults($options, $desc["defaults"]) : $options;

    $bs_config[$feature] = $args;
    call_user_func($desc["configure_callback"], $args);
}
