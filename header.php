<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title><?php bs_title(true); ?></title>

	<link rel="icon" href="<?php bs_asset("img/favicon.ico", true); ?>">

	<?php wp_head(); ?>

	<script type="text/javascript">
		jQuery("html").attr("class", "js");

		var themeUrl = <?php echo json_encode(bs_url("/")); ?>,
			assetsUrl = <?php echo json_encode(bs_asset("/")); ?>;
	</script>
</head>

<body <?php body_class(); ?>>
