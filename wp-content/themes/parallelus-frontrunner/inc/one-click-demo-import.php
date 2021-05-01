<?php
/**
 * Starter Kits | One Click Demo Import: Theme Functions
 */

// No direct access
if ( ! defined( 'ABSPATH' ) ) exit;

/***********************************************
 * Load the Demo Content Importer
 ***********************************************/

/**
 * Load the template file for the plugin
 *
 * @since 1.0
 */

define( 'PT_OCDI_PATH', get_stylesheet_directory(). '/inc/one-click-demo-import/');
define( 'PT_OCDI_URL', get_stylesheet_directory_uri(). '/inc/one-click-demo-import/' );

require_once __DIR__ . '/one-click-demo-import/one-click-demo-import.php';


/***********************************************
 * Customize the Importer for Starter Kits
 ***********************************************/

/**
 * Starter Kits
 *
 * @since 1.0
 */
function ocdi_import_files() {
	return array(

		// Defaults
	/*array(
			'import_file_name'             => 'Starter Kit (default)',
			'local_import_file'            => trailingslashit( get_template_directory() ) . 'assets/starter-kits/kit-0-content.xml',
			'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'assets/starter-kits/kit-0-widgets.wie',
			'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'assets/starter-kits/kit-0-customizer.dat',
			'local_import_redux'           => array(
				array(
					'file_path'   => trailingslashit( get_template_directory() ) . 'assets/starter-kits/kit-0-redux.json',
				'option_name' => 'tt_temptt_opt',
				)
			),
			'import_preview_image_url'     => trailingslashit( get_template_directory_uri() ) . 'assets/starter-kits/kit-0-screenshot.jpg',
		),*/

	);
}
add_filter( 'pt-ocdi/import_files', 'ocdi_import_files' );

/**
 * Before widgets and customize options import
 *
 * @since 1.0
 */
function upthemes_before_widgets_import() {
	// Don't import customizer images (we already import them in the demo content)
	add_filter( 'pt-ocdi/customizer_import_images',  function() {
		return false;
	});
}
add_action( 'pt-ocdi/before_widgets_import', 'upthemes_before_widgets_import' );

/**
 * After Import Functions
 *
 * Applies to all kits. You can set kit specific options in the next function.
 *
 * @since 1.0
 */
function upthemes_after_kit_setup() {

}
add_action( 'pt-ocdi/after_import', 'upthemes_after_kit_setup' );

/**
 * After Import (Kit Specific)
 *
 * Actions to do after import, depending on the kit ID
 *
 * @since 1.0
 */
function upthemes_after_specific_kit( $selected_import ) {

	// Add theme options and settings for each kit
	//-----------------------------------------------

	if ( 'Starter Kit (default)' === $selected_import['import_file_name'] ) {
		// nothing
	}

	// Assign menus to locations based (if needed)
	//-----------------------------------------------

	// menu objects
//	$main_menu = get_term_by( 'name', 'Main Menu', 'nav_menu' );
//	$social_menu = get_term_by( 'name', 'Social', 'nav_menu' );
//	$theme_menus = array(
//		'masthead' => $main_menu->term_id,
//		'social' => $social_menu->term_id,
//	);
//
//	// Set nav locations
//	set_theme_mod( 'nav_menu_locations', $theme_menus );

}
add_action( 'pt-ocdi/after_import', 'upthemes_after_specific_kit' );

/**
 * Starter Kit Page Content
 *
 * @since 1.0
 */
function upthemes_starter_kit_intro_text( $default_text ) {

	$default_text = ''; // First we're going to clear the default text (replacing it, rather than just appending on to it)

	$default_text .= '<p class="about-description">';
	$default_text .= esc_html__( 'Applying a Starter Kit will get your site up and running in a flash. Each Starter Kit automatically configures theme settings, widgets, menus and site content with just a single click.', 'upthemes' );
	$default_text .= '</p>';
	$default_text .= '<hr>';
	$default_text .= '<p class="about-description">';
		$default_text .= esc_html__( 'Things to know about importing Starter Kits:', 'upthemes' );
		$default_text .= '<ul>';
			$default_text .= '<li><span class="dashicons dashicons-yes"></span> '. esc_html__( 'Your existing content is safe. Starter Kits will not delete or modify any existing content, categories, images or other data.', 'upthemes' ) .'</li>';
			$default_text .= '<li><span class="dashicons dashicons-yes"></span> '. esc_html__( 'New content, pages, images, widgets and menus will be added to your site.', 'upthemes' ) .'</li>';
			$default_text .= '<li><span class="dashicons dashicons-yes"></span> '. esc_html__( 'The import process may take several minutes. If it takes a long time, be patient. Do not click the import button more than once.', 'upthemes' ) .'</li>';
		$default_text .= '</ul>';
	$default_text .= '</p>';
	$default_text .= '<hr>';

    return $default_text;
}
add_filter( 'pt-ocdi/plugin_intro_text', 'upthemes_starter_kit_intro_text' );

function upthemes_starter_kit_page_setup( $default_settings ) {
    $default_settings['parent_slug'] = 'themes.php';
    $default_settings['page_title']  = esc_html__( 'Starter Kits' , 'upthemes' ); // Starter Kits - One Click Setup
    $default_settings['menu_title']  = esc_html__( 'Starter Kits' , 'upthemes' );
    $default_settings['capability']  = 'import';
    $default_settings['menu_slug']   = 'upthemes-starter-kits';

    return $default_settings;
}
add_filter( 'pt-ocdi/plugin_page_setup', 'upthemes_starter_kit_page_setup', 99 );

function upthemes_starter_kit_plugin_notices( $notices ) {
	if ( ! current_user_can( 'install_plugins' ) ) {
		return $notices;
	}

	if ( class_exists( 'TGM_Plugin_Activation' ) ) {
		$TGMPA                = TGM_Plugin_Activation::get_instance();
		$plugins              = $TGMPA->plugins;
		$not_installed_exists = false;
		foreach ( $plugins as $slug => $plugin ) {
			if ( true === $plugin['required'] ) {
				if ( ! $TGMPA->is_plugin_installed( $slug ) || ! $TGMPA->is_plugin_active( $slug ) ) {
					$not_installed_exists = true;
					break;
				}
			}
		}

		if ( true === $not_installed_exists ) {
			$plugin_installer_url = esc_url( $TGMPA->get_tgmpa_url() );
			$notices              = '<div class="ocdi__intro-notice notice notice-warning is-dismissible">' . $notices . '<p>';
			$notices .= sprintf( esc_html__(
				'Before you begin, make sure all required plugins are %sinstalled and activated%s.', 'upthemes' ),
				'<a href="' . $plugin_installer_url . '">',
				'</a>'
			);
			$notices .= '</p></div>';
		}
	}

	return $notices;
}
add_filter( 'pt-ocdi/plugin_notices', 'upthemes_starter_kit_plugin_notices', 99 );


// A way to add some custom inline styles without modifying the Starter Kits addon core files.
/*function upthemes_starter_kit_extra_styles() {

	$custom_css  = ".ocdi__title { display: none; }";
	$custom_css .= ".ocdi__intro-text { display: none; }";
	$custom_css .= ".upthemes_starter-kit-title { margin-right: 0 !important; }";

	wp_add_inline_style( 'ocdi-main-css', $custom_css ); // inline styles to include after demo import CSS file loads
}
add_action( 'admin_enqueue_scripts', 'upthemes_starter_kit_extra_styles' );*/


// Helper to redirect to the Starter Kits setup screen after first activating the theme
function upthemes_theme_activation_redirect() {
	global $pagenow;

	if (is_admin() && $pagenow == 'themes.php' && isset($_GET['activated'])) {
		header( 'Location: '.admin_url().'themes.php?page=upthemes-starter-kits' ) ;
	}
}
add_action( 'admin_init', 'upthemes_theme_activation_redirect' );
