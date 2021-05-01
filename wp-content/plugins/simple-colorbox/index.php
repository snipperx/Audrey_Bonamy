<?php
/*

Plugin Name: Simple Colorbox
Plugin URI: https://geek.hellyer.kiwi/products/simple-colorbox/
Description: Adds a Colorbox to your site with no configuration required.
Author: Ryan Hellyer
Version: 1.6.1
Author URI: https://geek.hellyer.kiwi/
Text Domain: simple-colorbox

Copyright (c) 2013 Ryan Hellyer

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
license.txt file included with this plugin for more information.

*/


/**
 * Define constants
 * 
 * @since 1.0
 * @author Ryan Hellyer <ryanhellyer@gmail.com>
 */
define( 'SIMPLECOLORBOX_DIR', dirname( __FILE__ ) . '/' ); // Plugin folder DIR
define( 'SIMPLECOLORBOX_URL', plugins_url( '', __FILE__ ) ); // Plugin folder URL
define( 'SIMPLECOLORBOX_VERSION', '1.6.1' );

/**
 * Simple Colorbox class
 * Adds the required CSS and JS files to front-end of the site
 * 
 * This class may be abstracted from the plugin and used in your own theme if you prefer.
 * This can allow you to offer easy to use colorbox functionality without the hassle of 
 * users needing to install a complicated plugin.
 * 
 * @copyright Copyright (c), Ryan Hellyer
 * @author Ryan Hellyer <ryanhellyer@gmail.com>
 * @since 1.0
 */
class Simple_Colorbox {

	/**
	 * Class constructor
	 * Adds all the methods to appropriate hooks or shortcodes
	 */
	public function __construct() {

		// Add action hooks
		add_action( 'init',               array( $this, 'set_definitions' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'css' ) );
		add_action( 'wp_head',            array( $this, 'ad' ) );

		// Localization
		load_plugin_textdomain(
			'simple-colorbox', // Unique identifier
			false, // Deprecated abs path
			dirname( plugin_basename( __FILE__ ) ) . '/languages/' // Languages folder
		);

	}

	/*
	 * Set definitions
	 *
	 * This plugin originally used constants to over-ride the default settings
	 * This was later changed to use filters instead, but these constants are kept for backwards compatibility
	 *
	 * Plugin developers should use the 'simple_colorbox_selector' filter instead of these definitions
	 */
	public function set_definitions() {
		// Do definition check - used by themes/plugins to over-ride the default settings
		if ( ! defined( 'SIMPLECOLORBOX_OPACITY' ) )
			define( 'SIMPLECOLORBOX_OPACITY', '0.6' );
		if ( ! defined( 'SIMPLECOLORBOX_WIDTH' ) )
			define( 'SIMPLECOLORBOX_WIDTH', '95' );
		if ( ! defined( 'SIMPLECOLORBOX_HEIGHT' ) )
			define( 'SIMPLECOLORBOX_HEIGHT', '95' );
		if ( ! defined( 'SIMPLECOLORBOX_SLIDESHOW' ) )
			define( 'SIMPLECOLORBOX_SLIDESHOW', 'group' );
		if ( ! defined( 'SIMPLECOLORBOX_THEME' ) )
			define( 'SIMPLECOLORBOX_THEME', '1' );
	}

	/**
	 * Print scripts onto pages
	 */
	public function scripts() {

		wp_enqueue_script(
			'colorbox',
			SIMPLECOLORBOX_URL . '/scripts/jquery.colorbox-min.js',
			array( 'jquery' ),
			1.0,
			true
		);

		$default_settings = array(
			'maxWidth'       => SIMPLECOLORBOX_WIDTH, // Set a maximum width for loaded content. Example: "100%", 500, "500px"
			'maxHeight'      => SIMPLECOLORBOX_HEIGHT, // Set a maximum height for loaded content. Example: "100%", 500, "500px"
			'opacity'        => SIMPLECOLORBOX_OPACITY,
			'rel'            => SIMPLECOLORBOX_SLIDESHOW, // This can be used as an anchor rel alternative for Colorbox. This allows the user to group any combination of elements together for a gallery, or to override an existing rel so elements are not grouped together. $("a.gallery").colorbox({rel:"group1"}); Note: The value can also be set to 'nofollow' to disable grouping.
		);

		$colorbox_settings = array( 
			'rel'            => SIMPLECOLORBOX_SLIDESHOW, 
			'maxWidth'       => SIMPLECOLORBOX_WIDTH . "%", 
			'maxHeight'      => SIMPLECOLORBOX_HEIGHT . "%", 
			'opacity'        => SIMPLECOLORBOX_OPACITY, 
			'current'        => sprintf( __( 'image %1$s of %2$s', 'simple-colorbox' ), '{current}', '{total}' ), // Text or HTML for the group counter while viewing a group. {current} and {total} are detected and replaced with actual numbers while Colorbox runs.
			'previous'       => _x( 'previous', 'simple-colorbox' ), // Text or HTML for the previous button while viewing a group.
			'next'           => _x( 'next', 'simple-colorbox' ), // Text or HTML for the next button while viewing a group.
			'close'          => _x( 'close', 'simple-colorbox' ), // Text or HTML for the close button. The 'esc' key will also close Colorbox.
			'xhrError'       => __( 'This content failed to load.', 'simple-colorbox' ), // Error message given when ajax content for a given URL cannot be loaded.
			'imgError'       => __( 'This image failed to load.', 'simple-colorbox' ), // Error message given when a link to an image fails to load.
			'slideshowStart' => __( 'start slideshow', 'simple-colorbox' ), // Text for the slideshow start button.
			'slideshowStop'  => __( 'stop slideshow', 'simple-colorbox' ), // Text for the slideshow stop button
		); 

		// Colorbox settings 
		$colorbox_selector = "a[href$=\'jpg\'],a[href$=\'jpeg\'],a[href$=\'png\'],a[href$=\'bmp\'],a[href$=\'gif\'],a[href$=\'JPG\'],a[href$=\'JPEG\'],a[href$=\'PNG\'],a[href$=\'BMP\'],a[href$=\'GIF\']"; 

		// Load Colorbox 
		$colorbox_settings['l10n_print_after'] = ' 
		jQuery(function($){ 
			// Examples of how to assign the ColorBox event to elements 
			$("' . apply_filters( 'simple_colorbox_selector', $colorbox_selector ) . '").colorbox(colorboxSettings); 
		});'; 

		// Add Colorbox settings
		wp_localize_script( 'colorbox', 'colorboxSettings', apply_filters( 'simple_colorbox_settings', $colorbox_settings ) );

	}

	/*
	 * Adds CSS to front end of site
	 */
	public function css() {
		// Load the stylesheet
		wp_enqueue_style( 'colorbox', SIMPLECOLORBOX_URL . '/themes/theme' . apply_filters( 'simple_colorbox_theme', SIMPLECOLORBOX_THEME ) . '/colorbox.css', false, '', 'screen' );
	}

	/**
	 * Display notice about the plugin in head
	 */
	public function ad() {
		echo "\n<!-- Simple Colorbox Plugin v" . SIMPLECOLORBOX_VERSION ." by Ryan Hellyer ... https://geek.hellyer.kiwi/products/simple-colorbox/ -->\n";
	}

}

/**
 * Instantiate the Simple Colorbox plugin
 * This is being substantiated via a hook to provide customization over when the class is instantiated
 * 
 * @copyright Copyright (c), Ryan Hellyer
 * @author Ryan Hellyer <ryanhellyer@gmail.com>
 * @since 1.5
 */
function simple_colorbox() {
	global $simple_colorbox;
	$simple_colorbox = new Simple_Colorbox();
}
add_action( 'plugins_loaded', 'simple_colorbox' );
