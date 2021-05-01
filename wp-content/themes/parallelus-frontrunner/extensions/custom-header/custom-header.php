<?php
/*
    Extension Name: Custom Header
    Description: Custom theme header content.
*/



/**
 * Default template parts folder for design templates
 *
 * You can modify this path with a filter.
 * Example:
 *
 *     function set_custom_template_folder() {
 *         return 'my/folder/path/';
 *     }
 *     add_filter('rf_header_template', 'set_custom_template_folder' );
 */
function rf_header_template_base( $options = array() ) {

	return apply_filters('rf_header_template', 'templates/header');

}

#-----------------------------------------------------------------
# Headers
#-----------------------------------------------------------------

/**
 * Header for "Cover" templates
 */
if ( ! function_exists( 'rf_cover_theme_header' ) ) :
function rf_cover_theme_header() {

	// Load the cover template
	get_template_part( rf_header_template_base(), 'cover' );

}
endif;


/**
 * Classes applied to the header
 */
if ( ! function_exists( 'theme_header_class' ) ) :
function theme_header_class( $extra_classes = '', $echo = true ) {

	$all_classes = apply_filters( 'theme_header_class', $extra_classes);
	if ($echo) {
		echo 'class="'. esc_attr($all_classes) .'"';
	}

	return $all_classes;

}
endif;


/**
 * Styles applied to the header
 */
if ( ! function_exists( 'theme_header_styles' ) ) :
function theme_header_styles( $extra_styles = '') {

	$inline_styles = '';
	$styles = (rf_has_custom_header()) ? rf_get_header_style_attributes() : '';

	if ( is_array($styles) && !empty($styles) ) {
		foreach ($styles as $attr => $style) {
			$inline_styles .= $attr .':'. $style .';';
		}
	}

	// Assemble all the styles
	$inline_styles .= $extra_styles;

	echo 'style="'.apply_filters('show_theme_header_styles', $inline_styles).'"';
}
endif;


// Get the header styles
if ( ! function_exists( 'rf_get_header_style_attributes' ) ) :
function rf_get_header_style_attributes() {

	$styles = array();

	// Defaults
	$background_color  = get_options_data('options-page', 'header-color-default', '');
	$background_image  = get_options_data('options-page', 'header-bg-default', '');
	$background_height = '';

	// some defaults
	if (is_front_page() || is_home()) {
		// Home Page, but not a 'page_for_posts'
		if ( get_option('show_on_front') == 'page' && (int) get_option('page_for_posts') === get_queried_object_ID() ) {
			// Do nothing. This is the blog page.
		} else {
			// It's the real home, set the values.
			$background_color  = get_options_data('home-page', 'home-header-background-color', '');
			$background_image  = get_options_data('home-page', 'home-header-background-image', '');
		}
	} else {
		// Not the home page
		$background_height = get_options_data('options-page', 'header-height-default', '');
	}

	// error checking
	if ( !empty($background_color) && $background_color !== '#' ) {
		$styles['background-color'] = $background_color;
	}
	if ( !empty($background_image) ) {
		$styles['background-image'] = 'url('.$background_image.')';
	}
	if ( !empty($background_height) ) {
		// % or px
		if ( strpos($background_height, '%') !== false ) {
			$styles['padding-top'] = (int) $background_height .'%';
			$styles['max-height'] = 'none';
			// $styles['height'] = 'auto';
		} else {
			// assume any other value is px
			$styles['max-height'] = 'none';
			$styles['height'] = (int) $background_height .'px';
			$styles['padding-top'] = '0';
		}
	}

	return apply_filters('rf_get_header_style_attributes', $styles);
}
endif;

// Header inner container
// if ( ! function_exists( 'rf_header_container_styles' ) ) :
// function rf_header_container_styles( $extra_styles = '') {

// 	$header_inline_styles = '';
// 	$background_height = '';

// 	if (is_front_page() || is_home()) {
// 		// Home Page
// 	} else {
// 		// All other pages
// 		$background_height = get_options_data('options-page', 'header-height-default', '');
// 	}

// 	// error checking
// 	if ( !empty($background_height) ) {
// 		// % or px
// 		if ( strpos($background_height, '%') !== false ) {
// 			$header_inline_styles .= 'padding-top: '. (int) $background_height .'%;';
// 			$header_inline_styles .= 'max-height: none; height: auto;';
// 		} else {
// 			// assume any other value is px
// 			$header_inline_styles .= 'max-height: none; height: '. (int) $background_height .'px;';
// 			$header_inline_styles .= 'padding-top: 0;';
// 		}
// 	}

// 	// Assemble all the styles
// 	$header_inline_styles .= $extra_styles;

// 	echo 'style="'.apply_filters('rf_header_container_styles', $header_inline_styles ).'"';

// }
// endif;


#-----------------------------------------------------------------
# Title Functions
#-----------------------------------------------------------------

/**
 * Page Title in Header. Similar to titles generaged by wp_title()
 * for use in headers and other areas outside the loop.
 */
if ( ! function_exists( 'rf_generate_the_title' ) ) :
function rf_generate_the_title( $title = '' ) {
	global $wpdb, $wp_locale;

	$m        = get_query_var('m');
	$year     = get_query_var('year');
	$monthnum = get_query_var('monthnum');
	$day      = get_query_var('day');
	$search   = get_search_query();
	$t_sep    = ' ';

	// If there is a post
	if ( is_single() || ( is_home() && !is_front_page() ) || ( is_page() && !is_front_page() ) ) {
		$title = single_post_title( '', false );
	}
	// If there's a category or tag
	if ( is_category() || is_tag() ) {
		$title = single_term_title( '', false );
	}
	// If there's a taxonomy
	if ( is_tax() ) {
		// $term = get_queried_object();
		// $tax = get_taxonomy( $term->taxonomy );
		// $title = single_term_title( $tax->labels->name . $t_sep, false );
		$title = single_term_title( '', false );
	}
	// If there's an author
	if ( is_author() ) {
		$author = get_queried_object();
		$title = __('Posts by', 'framework'). ' ' .$author->display_name;
	}
	// If there's a post type archive
	if ( is_post_type_archive() )
		$title = post_type_archive_title( '', false );
	// If there's a month
	if ( is_archive() && !empty($m) ) {
		$my_year = substr($m, 0, 4);
		$my_month = $wp_locale->get_month(substr($m, 4, 2));
		$my_day = intval(substr($m, 6, 2));
		$title = ( $my_month ? $my_month .  $t_sep : '' ) . ( $my_day ? $my_day . $t_sep : '' ) . $my_year;
	}
	// If there's a year
	if ( is_archive() && !empty($year) ) {
		$title = '';
		if ( !empty($monthnum) )
			$title .= $wp_locale->get_month($monthnum) . $t_sep;
		if ( !empty($day) )
			$title .= zeroise($day, 2) . $t_sep;
		$title .= $year;
	}
	// If it's a search
	if ( is_search() ) {
		/* translators: 1: separator, 2: search phrase */
		$title = sprintf(__('Search Results for: %1$s', 'framework'), '<em>"'.strip_tags($search).'"</em>');
	}
	// If it's a 404 page
	if ( is_404() ) {
		$title = __('Page not found', 'framework');
	}

	return apply_filters('rf_generate_the_title', $title);

}
endif;
add_filter( 'theme_header_title', 'rf_generate_the_title' );


/**
 * Page Sub-Title/Content in Header
 */
if ( ! function_exists( 'rf_generate_the_subtitle' ) ) :
function rf_generate_the_subtitle( $subtitle = '' ) {
	global $wpdb, $wp_locale;

	$t_sep    = ' ';

	// If there is a post
	if (is_page() && has_excerpt()) {
		$subtitle = get_the_excerpt();
	} elseif ( is_single() || ( is_home() && !is_front_page() ) ) {
		$subtitle = ( !function_exists( 'rf_posted_on' ) ) ? '' : rf_posted_on( false ); // use false to return, not echo
	}

	return apply_filters('rf_generate_the_subtitle', $subtitle);

}
endif;

add_filter( 'theme_header_subtitle', 'rf_generate_the_subtitle' );



#-----------------------------------------------------------------
# Header Helpers
#-----------------------------------------------------------------

/**
 * Check if this is a cover template
 */
if ( ! function_exists( 'rf_is_cover_template' ) ) :
function rf_is_cover_template( $templates = array() ) {

	$templates = apply_filters('rf_is_cover_template', $templates);
	$is_cover = false;

	foreach ($templates as $type => $template) {
		if ( function_exists('is_'.$type) ) {
			if ( call_user_func('is_'.$type) ) {
				$is_cover = true;
			}
		} elseif ( is_page_template($template) ) {
			$is_cover = true;
		}
	}

	return apply_filters('filter_is_cover_template', $is_cover);
}
endif;

/**
 * Custom class on HTML element for "Cover" templates
 */
if ( ! function_exists( 'rf_html_cover_class' ) ) :
function rf_html_cover_class() {

	if ( rf_is_cover_template() ) {
		echo 'class="cover"';
	}
}
endif;


/**
 * Show the header for the specific area.
 */
if ( ! function_exists( 'show_theme_header' ) ) :
function show_theme_header() {

	if ( rf_is_cover_template() ) {

		rf_cover_theme_header();

	} else {

		if (rf_has_custom_header()) {

			$template = apply_filters( 'show_theme_header_template', rf_has_custom_header() );

			// Load the template file "header-{ $template }.php"
			get_template_part( rf_header_template_base(), $template );
		}
	}
}
endif;


/**
 * Checks if custom headers are enabled for current page from theme options
 *
 * @return bool Returns true if custom headers are enabled.
 */
if ( ! function_exists( 'rf_has_custom_header' ) ) :
function rf_has_custom_header() {

	// Use Page Headers
	$queried_ID = get_queried_object_ID();
	$show_the_header = false;
	$show_headers = (array) get_options_data('options-page', 'use-page-headers');
	$header_template = 'default';
	$home_page = (function_exists('theme_is_custom_home_page')) ? theme_is_custom_home_page() : is_front_page();


	if ($home_page) {

		// Custom home page settings
		$show_the_header = (get_options_data('home-page', 'home-header-size') !== 'none') ? true : false;
		$header_template = 'front_page';

		// No header on home page 2, 3, etc.
		$page = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;
		if (isset($page) && $page > 1) {
			$show_the_header = false;
		}

	} elseif (isset($show_headers) && !empty($show_headers)) {

		// Standard page headers
		foreach ($show_headers as $section) {
			// Create conditions
			$user_func = 'is_'.$section; // default functions: is_home, is_single, is_page...
			$user_param = ''; // parmeter to pass: is_page(123)...
			if ( strpos($section,':') !== false ) {
				$condition = explode(':', $section);
				$user_func = (isset($condition[0])) ? $condition[0] : '';;
				$user_param = (isset($condition[1])) ? $condition[1] : '';
				$section = $user_func;
			}
			// Test
			if ( function_exists($user_func) ) {
				if ( call_user_func($user_func, $user_param) ) {
					$show_the_header = true;
					$header_template = $section;
				}

				continue;
			}
		}

		// include post archive & categories as part of blog setting (but not CPT archives)
		if ( in_array('home', $show_headers) && (is_archive() || is_category()) && !is_post_type_archive() ) {
			$show_the_header = true;
			$header_template = 'archive';
		}

		// Check destinations (make sure $queried_ID isn't 0 or get_post_type sees it as unset and uses current post)
		if ( (get_post_type( $queried_ID ) == 'political-issue' && $queried_ID !== 0) || is_post_type_archive('political-issue') ) {
			$show_the_header = (in_array('political-issue', $show_headers)) ? true : false;
			$header_template = 'issue';
		}
		if ( (get_post_type( $queried_ID ) == 'political-event' && $queried_ID !== 0) || is_post_type_archive('political-event') ) {
			$show_the_header = (in_array('political-event', $show_headers)) ? true : false;
			$header_template = 'event';
		}
		if ( (get_post_type( $queried_ID ) == 'political-video' && $queried_ID !== 0) || is_post_type_archive('political-video') ) {
			$show_the_header = (in_array('political-video', $show_headers)) ? true : false;
			$header_template = 'video';
		}

		// 404 Error template
		if ( is_404() ) {
			$error_template = (get_options_data('options-page', 'error-template')) ? get_options_data('options-page', 'error-template') : 'default';
			if ( !empty($error_template) && $error_template == 'cover') {
				$show_the_header = true;
				$header_template = 'cover';
			}
		}

	}

	$show_the_header = apply_filters('rf_has_custom_header', $show_the_header);
	$header_template = apply_filters('rf_has_custom_header_template', $header_template);

	return ($show_the_header) ? $header_template : false;
}
endif;


#-----------------------------------------------------------------
# Filters and Actions
#-----------------------------------------------------------------

// Filter Home Header for Auto Paragraphs
// if ( ! function_exists( 'wpautop_home_header' ) ) :
// function wpautop_home_header( $content ) {

// 	$home_autop = get_options_data('home-page', 'home-header-autop');
// 	if ( !is_front_page() || $home_autop == 'true') {
// 		$content = wpautop($content);
// 	}
// 	return $content;
// }
// endif;
// remove_filter( 'get_options_data_type_text-editor', 'wpautop', 10 ); // remove for all Runway text editors
// add_filter( 'get_options_data_type_text-editor', 'wpautop_home_header', 10 ); // add custom version


// Add custom CSS from header options.
function home_header_custom_styles() {
	$custom_css = get_options_data('home-page', 'home-header-custom-css', '');
	$home_page = (function_exists('theme_is_custom_home_page')) ? theme_is_custom_home_page() : is_front_page();

	if (!empty($custom_css) && $home_page) {
		wp_add_inline_style( 'theme-style', $custom_css ); // $handle must match existing CSS file.
	}
}
add_action( 'wp_enqueue_scripts', 'home_header_custom_styles', 11 );


// Add support for excerpts in pages.
function rf_add_page_excerpts() {
	add_post_type_support( 'page', 'excerpt' );
}
add_action('init', 'rf_add_page_excerpts');
