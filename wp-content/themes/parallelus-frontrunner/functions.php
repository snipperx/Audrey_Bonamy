<?php if ( __FILE__ == $_SERVER['SCRIPT_FILENAME'] ) { die(); }


// Execute hooks before framework loads
do_action( 'functions_before' );


#-----------------------------------------------------------------
# Load framework
#-----------------------------------------------------------------
include_once get_template_directory() . '/framework/load.php';



// Execute hooks after framework loads
do_action( 'functions_after' ); ?><?php
/**
 * Theme registration and WP connections
 *
 */

/**
 * Toggle template directory and URI for Runway child/standalone themes
 *
 * These functions can be used to replace the defaults in WordPress so the correct path is
 * generated for both child themes and standalone. It will ensure the paths being referenced
 * to your themes folder are always correct.
 */
if (!function_exists('rf_get_template_directory_uri')) :
	function rf_get_template_directory_uri() {
		return (IS_CHILD) ? get_stylesheet_directory_uri() : get_template_directory_uri();
	}
endif;
if (!function_exists('rf_get_template_directory')) :
	function rf_get_template_directory() {
		return (IS_CHILD) ? get_stylesheet_directory() : get_template_directory();
	}
endif;

/**
 * Init integrated plugins
 */
// include_once get_stylesheet_directory(). '/inc/init.php';

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 1170; /* pixels */


if ( ! function_exists( 'rf_theme_setup' ) ) :
/**
 * Set up theme defaults and register support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function rf_theme_setup() {

	if ( function_exists( 'add_theme_support' ) ) {

		// WP Stuff
		add_editor_style(); // Admin editor styles
		add_theme_support( 'automatic-feed-links' ); // RSS feeds
		// add_theme_support( 'post-formats', array( 'image', 'video' ) ); // Post formats. Unused: quote, link
		register_nav_menu( 'primary', __( 'Primary Menu', 'framework' ) ); // Main menu

		// Post thumbnails
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 1200, 9999 );

		// Additional image sizes
		add_image_size( 'blog', 1130, 565, true ); // Blog: vertical 2:1 ratio, hard crop
		// add_image_size( 'blog-landscape', 800, 600, true ); // Blog: horizontal 4:3 ratio, hard crop
		add_image_size( 'header', 1920, 1080 ); // Header background: 16:9 ratio
		// add_image_size( 'place', 960, 540, true ); // Places in destinations: 16:9 ratio, hard crop
		add_image_size( 'gallery', 500, 375, true ); // Image gallery 4:3 ratio, hard crop

		// WooCommerce
		add_theme_support( 'woocommerce' );
	}

	// Translation
	load_theme_textdomain( 'framework', rf_get_template_directory() . '/languages' );

	// Navigation menus
	register_nav_menus( array(
		'primary'  => __( 'Main Menu (left)', 'framework' ),
		'menu-right'  => __( 'Alternate Menu (right)', 'framework' ),
		'menu-footer'  => __( 'Footer Menu', 'framework' ),
	) );

}
endif; // rf_theme_setup
add_action( 'after_setup_theme', 'rf_theme_setup' );


/**
 * Enqueue scripts and styles
 */
function rf_enqueue_scripts() {
	global $wp_scripts;

	// Load CSS
	if( wp_style_is( 'font-awesome', 'registered' ) )
		wp_deregister_style( 'font-awesome' );
	wp_enqueue_style( 'font-awesome', rf_get_template_directory_uri() . '/assets/css/font-awesome.min.css', '', '4.7.0' ); // Source: http://cdnjs.com/libraries/font-awesome
	wp_enqueue_style( 'theme-bootstrap', rf_get_template_directory_uri() . '/assets/css/bootstrap.min.css' ); // can be changed to 'bootstrap.css' for testing.
	wp_enqueue_style( 'theme-style', get_stylesheet_uri() );

	// Load scripts
	wp_enqueue_script( 'theme-js', rf_get_template_directory_uri().'/assets/js/theme-scripts.js', array('jquery'), '1.0', true );
		wp_localize_script( 'theme-js', 'ThemeJS', array( 'ajax_url' => admin_url('admin-ajax.php') ) ); // localize for AJAX URL
	wp_enqueue_script( 'theme-bootstrapjs', rf_get_template_directory_uri().'/assets/js/bootstrap.min.js', array('jquery'), '1.0', true );
	wp_enqueue_script( 'fitvids', '//cdnjs.cloudflare.com/ajax/libs/fitvids/1.1.0/jquery.fitvids.min.js', array('jquery'), '1.1.0', true );


	// IE only JS
	wp_enqueue_script( 'theme-html5shiv', '//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv-printshiv.min.js', '3.7.2' ); // Source: https://cdnjs.com/libraries/html5shiv
	$wp_scripts->add_data( 'theme-html5shiv', 'conditional', 'lt IE 9' );
	wp_enqueue_script( 'theme-respondjs', '//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js', '1.4.2' ); // Source: https://cdnjs.com/libraries/respond.js
	$wp_scripts->add_data( 'theme-respondjs', 'conditional', 'lt IE 9' );

    // IE10 viewport hack for Surface/desktop Windows 8 bug -->
	wp_enqueue_script( 'theme-ie10-viewport-bug', rf_get_template_directory_uri().'/assets/js/ie10-viewport-bug-workaround.js', '1.0.0', true );

	// Load comment reply ajax
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Load keyboard navigation for image template
	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'theme-keyboard-image-nav', rf_get_template_directory_uri() . '/assets/js/keyboard-image-nav.js', array( 'jquery' ), '1.0.0' );
	}

	// Google Fonts
    if ( get_options_data( 'options-page', 'font-body' ) == 'google' ) {
        $body_google_font = get_options_data( 'options-page', 'font-body-google' );
        $gFontQuery       = rf_google_font_query( $body_google_font, 'body' );
        // Load Google Font
        if ( ! empty( $gFontQuery ) ) {
            wp_enqueue_style( 'theme-google-font-body', $gFontQuery, array(), null );
        }
    }

    if ( get_options_data( 'options-page', 'font-heading' ) == 'google' ) {
        $heading_google_font = get_options_data( 'options-page', 'font-heading-google' );
        $gFontQuery          = rf_google_font_query( $heading_google_font, 'heading' );
        // Load Google Font
        if ( ! empty( $gFontQuery ) ) {
            wp_enqueue_style( 'theme-google-font-heading', $gFontQuery, array(), null );
        }
    }

}
add_action( 'wp_enqueue_scripts', 'rf_enqueue_scripts' );


/**
 * Set the default header content template path.
 *
 * This is not the same as the WP header file. This file is used for
 * header design and content function within the theme.
 */
if ( ! function_exists( 'theme_cover_templates_filter' ) ) :
function theme_set_custom_header_template() {
	return 'templates/parts/header';
}
endif;
add_filter('rf_header_template', 'theme_set_custom_header_template' );

/**
 * Register the Cover style templates to be recognized by the theme
 *
 * Cover templates use a full screen background image and are only
 * recognized by the theme when properly registered.
 */
if ( ! function_exists( 'theme_cover_templates_filter' ) ) :
function theme_cover_templates_filter( $templates = array() ) {

	// Add templates to the array of cover templates
	$templates[] = 'templates/cover.php';
	$templates[] = 'templates/cover-content-left.php';
	$templates[] = 'templates/cover-content-right.php';
	$templates[] = 'templates/cover-with-page.php';
	$templates[] = 'templates/cover-with-menu.php';
	$templates[] = 'templates/cover-with-page-and-menu.php';

	if (get_options_data('options-page', 'error-template') !== 'default') {
		$templates['404'] = '404.php'; // including an array key tests against "is_$key"
	}

	return $templates;
}
endif;
add_filter( 'rf_is_cover_template', 'theme_cover_templates_filter' );


/**
 * Custom Titles in Headers
 *
 * Using a filter, we can override default titles in headers
 * with a nother string.
 */

// Issues Archive Page
if ( ! function_exists( 'theme_issues_archive_title_filter' ) ) :
function theme_issues_archive_title_filter( $title ) {

	if (is_post_type_archive('political-issue')) {
		return __('On the Issues', 'framework');
	}

	return $title;
}
endif;
add_filter( 'theme_header_title', 'theme_issues_archive_title_filter', 11 );

/*if ( ! function_exists( 'theme_issues_archive_sub_title_filter' ) ) :
function theme_issues_archive_sub_title_filter( $content ) {

	if (is_post_type_archive('political-issue')) {
		return __('This is where we stand.', 'framework');
	}

	return $title;
}
endif;
add_filter( 'theme_header_content', 'theme_issues_archive_sub_title_filter' );*/


// Additional filters for Ninja Forms v3
// -------------------------------------

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
	$nf_version = defined( 'NINJA_FORMS_VERSION' ) ? NINJA_FORMS_VERSION : get_option( 'ninja_forms_version', '0.0.0' );

	if ( version_compare( $nf_version, '3.0', '>=' ) && ! get_option( 'ninja_forms_load_deprecated', false ) ) {
		add_action( 'ninja_forms_enqueue_scripts', 'enqueue_nf3_forms_helpers' );
		add_filter( 'ninja_forms_render_options', 'fix_nf3_unchecked_options' );
	}

}

if ( ! function_exists( 'enqueue_nf3_forms_helpers' ) ) :
function enqueue_nf3_forms_helpers( $form_id ) {

	wp_enqueue_script( 'nf3-helpers', rf_get_template_directory_uri() . '/assets/js/nf3-helpers.js', '1.0.0', true );

}
endif;

if ( ! function_exists( 'fix_nf3_unchecked_options' ) ) :
// The value "0" of the "selected" property for the options perceived as TRUE after form importing
function fix_nf3_unchecked_options( array $options, $settings = null ) {

	foreach ( $options as &$option ) {
		if ( array_key_exists( 'selected', $option ) && $option['selected'] === '0' ) {
			$option['selected'] = 0;
		}
	}

	return $options;

}
endif;

/**
 * EXAMPLE CODE for custom BODY google font subsets
 */
//if ( ! function_exists( 'theme_body_font_subset' ) ) {
//	function theme_body_font_subset( array $subset ) {
//		$subset[] = 'cyrillic';
//		$subset[] = 'cyrillic-ext';
//
//		return $subset;
//	}
//}
//add_filter( 'google_font_subsets_body', 'theme_body_font_subset' );

/**
 * EXAMPLE CODE for custom HEADING google font subsets
 */
//if ( ! function_exists( 'theme_heading_font_subset' ) ) {
//	function theme_heading_font_subset( array $subset ) {
//		$subset[] = 'greek';
//
//		return $subset;
//	}
//}
//add_filter( 'google_font_subsets_heading', 'theme_heading_font_subset' );