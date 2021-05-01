<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up to the start of the content output.
 *
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> <?php if (function_exists( 'rf_html_cover_class' )) : rf_html_cover_class(); endif; ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php
	$favorites = get_options_data('options-page', 'favorites-icon');
	if (!empty( $favorites )) : ?><link rel="shortcut icon" href="<?php echo esc_url($favorites); ?>"><?php endif; ?>
	<?php
	$bookmark = get_options_data('options-page', 'mobile-bookmark');
	if (!empty( $bookmark )) : ?><link rel="apple-touch-icon-precomposed" href="<?php echo esc_url($bookmark); ?>"><?php endif; ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php do_action( 'before' ); ?>

	<?php

	// Header Content and Menu
	// ----------------------------------------------------------------------
	if ( function_exists('rf_has_custom_header') && rf_has_custom_header() ) {
		// Load a header template based on theme options
		show_theme_header();

	} else {

		// This happens for pages with only a menu
		get_template_part( 'templates/parts/header', 'menu-only' );

	}


	// Layout Manager Support - start layout here...
	// ----------------------------------------------------------------------
	/**
	 * We're also using the output_layout action to add a theme specific HTML container
	 * for all template files that do not explicitly state they have pre-defined elements
	 * the applying content containers.
	 */
	do_action('output_layout','start');

	?>