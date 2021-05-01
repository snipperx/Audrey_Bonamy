<?php
/*
    Extension Name: Custom Login Screen
    Version: 1.0
    Description: Customize the WordPress login screen background and logo.
*/


/**
 * Load a custom stylesheet on the login page
 */
if ( ! function_exists( 'rf_custom_login_stylesheet' ) ) :
function rf_custom_login_stylesheet() {

	$path = (function_exists('rf_get_template_directory_uri')) ? rf_get_template_directory_uri() : get_template_directory_uri();

	// Load CSS
    wp_enqueue_style( 'custom-login', $path . '/assets/css/bootstrap.min.css' );

    // Load JavaScript
    wp_enqueue_script( 'custom-login', $path . '/assets/js/login.js', 'jquery' );
}
endif;

add_action( 'login_enqueue_scripts', 'rf_custom_login_stylesheet' );


/**
 * Load custom design styles from theme options
 */
if ( ! function_exists( 'rf_custom_login_styles' ) ) :
function rf_custom_login_styles() {

	$login_logo     = get_options_data('options-page', 'login-logo', '');
	$login_bg_color = get_options_data('options-page', 'login-background-color', '');
	$login_bg_color = ($login_bg_color === '#') ? '' : $login_bg_color;
	$login_bg_image = get_options_data('options-page', 'login-background-image', '');
	$login_styles   = 'html { background: transparent !important; }';

	if (!empty($login_logo)) {
		$login_styles .= 'body.login div#login h1 a { background-image: url('.$login_logo.'); }'."\n";
	}
	if (!empty($login_bg_color) || !empty($login_bg_image)) {
		$login_styles .= 'html body.login { '."\n";
		if (!empty($login_bg_color)) {
			$login_styles .= 'background-color: '.$login_bg_color.';'."\n";
			if (empty($login_bg_image)) {
				$login_styles .= 'background-image: none;'."\n";
			}
		}
		if (!empty($login_bg_image)) {
			$login_styles .= 'background-image: url('.$login_bg_image.');'."\n";
		}
		$login_styles .= '}'."\n";
	}

	if ( !empty($login_styles) ) {
	?>
		<style type="text/css">
			<?php echo  $login_styles; ?>
		</style>
	<?php

	}
}
endif;

add_action( 'login_enqueue_scripts', 'rf_custom_login_styles' );