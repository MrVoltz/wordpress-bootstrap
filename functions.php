<?php

require_once "bootstrap/features.php";
// require_once "bootstrap/meta-boxes.php";
// require_once "gallery-metabox/gallery-metabox.php";

// Your code here...

// require_once "inc/meta-boxes.php";

bs_configure("assets", ["path" => "assets"]);

// Enqueue styles & scripts

// bs_enqueue_admin_assets(); // required if you use repeater metabox or image uploader
bs_configure("styles", [
    "frontend" => [
        bs_url("style.css"),
    ],
]);
bs_configure("scripts", [
    "frontend" => [],
    "admin"    => [],
]);

// Configure main menu

/*
    bs_configure("menus", [
        "menus" => [
            "primary" => [
                "name" => "", // what will be shown in admin interface
            ],
        ],
    ]);

    bs_configure("menu-classes", [
        "first"  => "",
        "last"   => "",
        "active" => "",
    ]);
*/

// Create theme options page

/*
    bs_configure("options-pages", [
        "xx_theme_options" => [
            "title"   => "",
            "section" => "xx",
            "options" => [],
        ],
    ]);

    function xx_theme_options()
    {
        ?>

        <table class="form-table">

        </table>

        <?php
    }
*/

// Load ACF

/*
    bs_configure("acf", [
        "show_in_admin" => true, // set to false after exporting your fields
    ]);
*/
