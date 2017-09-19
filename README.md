# Wordpress Bootstrap

Wordpress Bootstrap is a simple framework I built for myself to help me with developing Wordpress themes. It consists of some utility functions, modules (called "features") and two external plugins (Advanced Custom Fields and Gallery Metabox).

I tried to make it really simple to use. To enable some "feature", include the bootstrap/features.php file and use the bs_configure() function directly inside your functions.php file (no hooks required).

## Features

### Header Menu (header-menu) (DEPRECATED)

Add theme support for menus and create main header menu.

### Custom Background (custom-background)

Add theme support for custom backgrounds, but don't print any styles. You can get those styles using the bs_get_custom_background_css() function and print them yourself in the <head> tag. This function only returns the CSS properties, not the selector, so you can choose one yourself (useful if you don't want to style the <body> element directly).

### Custom Post Types (custom-post-types)

Simple wrapper around register_post_type. Adds some shortcut parameters, for example `name`, `slug` or `admin_only`.

### Post Thumbnails (post-thumbnails)

Add theme support for post thumbnails for selected post types, register custom image sizes.

### Remove Admin Links (remove-admin-links)

Remove unnecessary links from admin menu, for example Comments, Posts etc. Useful for non-blog themes to simplify the menu.

### Optimize (optimize)

Clean-up wp_head(), optionally remove Emoji support and Admin Bar.

### Posts Per Page (posts-per-page)

Set custom number of posts per page per post type. Added fake post type "home", which sets posts per page on HP.

### Scripts (scripts) and Styles (styles)

A really useful part of Wordpress Bootstrap. Allows you to simply include needed scripts and styles. Automatically passes URLs through bs_url() function, so you don't need to specify absolute URLs or bother with get_template_directory_uri() etc. But you can, if you want.

Syntax is: "optional name" => [ "url", dependencies, "condition" => condition, "footer" => in footer]

If name is not specified (just numeric index), then everything from start of last / to first . is used as name. You can then use this name in the dependency list. Dependencies can be a string (if only 1), or array if more. Condition can be used to limit, on which pages will the script be enqueued.

### Options Pages (options-pages)

Useful tool, which will help you with creating your own options pages.

### ACF (acf)

Includes the famous Advanced Custom Fields plugin, you can toggle it's lite mode using the "show_in_admin" flag.

### Body Class Slug (body-class-slug)

Add "$post_type-$post_slug" class to the <body> tag. Useful for styling custom pages, but sometimes it is better to create custom page template.

### Assets (assets)

If you put your images and other assets in some subdirectory, you can use this feature to simplify your live. It enables function bs_asset() which behaves exactly like bs_url(), but works relative to your asset directory you specified.

## More documentation (for Metaboxes and Gallery Metabox) is coming. Feel free to submit PR or Issues.
