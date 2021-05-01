<?php
/**
 * Filters to update and modify the output of content for template
 * files, theme functions and WordPress outputs.
 */

#-----------------------------------------------------------------
# Filters wp_title to create page specific title tags.
#-----------------------------------------------------------------

if ( ! function_exists( 'rf_wp_title' ) ) :
function rf_wp_title( $title, $sep ) {
	global $page, $paged;

	if ( is_feed() )
		return $title;

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title .= " $sep $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || (!is_object($page) && $page >= 2) ) {
		$page = (is_object($page)) ? 0 : $page;
		$title .= " $sep " . sprintf( __( 'Page %s', 'framework' ), max( $paged, $page ) );
	}

	return $title;
}
endif; // rf_wp_title
add_filter( 'wp_title', 'rf_wp_title', 10, 2 );


#-----------------------------------------------------------------
# Classes for header section
#-----------------------------------------------------------------
if ( ! function_exists( 'rf_theme_extra_header_classs' ) ) :
function rf_theme_extra_header_classs( $classes = '' ) {

	$add_class = array();

	// Home page class
	if (is_front_page() || is_home()) {
		if ( get_option('show_on_front') == 'page' && (int) get_option('page_for_posts') === get_queried_object_ID() ) {
			// nothing happens here... (for now)
		}
		$add_class[] = get_options_data('home-page', 'home-header-class', ''); // custom class
	// All other pages (defaults)
	} else {
		// Deafaults (none yet...)
	}

	// Formatting
	array_filter($add_class); // Get rid of empty values
	$classes .= ' '. implode(' ', $add_class); // make into a string

	return $classes;
}
endif; // rf_theme_extra_header_classs
add_filter('theme_header_class', 'rf_theme_extra_header_classs' );


#-----------------------------------------------------------------
# Filters for Header Styles
#-----------------------------------------------------------------

// Home Page Header Size
// ................................................................
if ( ! function_exists( 'theme_home_header_style_filter' ) ) :
function theme_home_header_style_filter( $header_template = 'default' ) {

	// requires the custom header theme functions
	if ( !function_exists('rf_has_custom_header') ) {
		return $header_template;
	}

	$home_page = (function_exists('theme_is_custom_home_page')) ? theme_is_custom_home_page() : is_front_page();

	if ($home_page) {
		// Home Page header settings
		$size = get_options_data('home-page', 'home-header-size');

		if ($size == 'small') {
			return 'small';
		} else {
			return 'large';
		}
	}

	// else...
	return $header_template;
}
add_filter( 'show_theme_header_template', 'theme_home_header_style_filter' );
endif;

// Pages/Posts - Header Styles
// ................................................................
if ( ! function_exists( 'rf_page_header_bg' ) ) :
function rf_page_header_bg( $style = array() ) {

	// Make sure not to overriding a destination header image
	if ( function_exists('get_the_destination_ID') && get_the_destination_ID()) {
		return $style;
	}

	// get the current page/post ID
	$id = get_queried_object_id();

	$meta_options = get_post_custom( $id );
	if ( $id && isset($meta_options['theme_custom_layout_metabox_options_header_bg']) ) {
		$bg_setting = $meta_options['theme_custom_layout_metabox_options_header_bg'][0];

		if ( isset($bg_setting) && !empty($bg_setting) ) {

			// Featured image background
			if ($bg_setting == 'featured-image' && has_post_thumbnail( $id )) {
				$thumb_id = get_post_thumbnail_id( $id );
				$thumb_src = wp_get_attachment_image_src( $thumb_id, 'header' );
				if ( isset($thumb_src[0]) && !empty($thumb_src[0]) ) {
					$style['background-image'] = 'url('. $thumb_src[0] .')';
				}
			}

			// Color background
			$meta_options = get_post_custom( $object_id = get_queried_object_id() );
			if ($bg_setting == 'color-1' || $bg_setting == 'color-2' || $bg_setting == 'color-3') {

				$accent_color = get_options_data('options-page', 'color-accent-'. str_replace('color-', '', $bg_setting) );
				if (!empty($accent_color) && $accent_color !== '#') {

					$style['background-image'] = 'none';
					$style['background-color'] = $accent_color;
				}
			}
		}
	}

	return $style;
}
endif;
add_filter( 'rf_get_header_style_attributes', 'rf_page_header_bg' );

// Pages/Posts - Hidden Header
// ................................................................
if ( ! function_exists( 'rf_page_show_hide_header' ) ) :
function rf_page_show_hide_header( $show_header ) {

	// Cover template must have header
	if (rf_is_cover_template())
		return true;

	if (is_page() || is_single()) {
		// Header on pages using meta options
		$meta_options = get_post_custom( get_queried_object_id() );
		if ( isset($meta_options['theme_custom_layout_metabox_options_header_style'][0]) ) {
			$style_setting = $meta_options['theme_custom_layout_metabox_options_header_style'][0];
			if ( isset($style_setting) && !empty($style_setting) && $style_setting != 'default' ) {
				$show_header = ($style_setting == 'none') ? false : true;
			}
		}
	}

	return $show_header;
}
endif;
add_filter( 'rf_has_custom_header', 'rf_page_show_hide_header' );


#-----------------------------------------------------------------
# Filters for Header Titles and Sub-titles
#-----------------------------------------------------------------

// Header title filters
// ................................................................
if ( ! function_exists( 'show_theme_header_title_filter' ) ) :
function show_theme_header_title_filter( $title = '' ) {
	global $shortname;

	// Pages and Posts
	if ( is_page() || is_single() ) {
		$title = ''; // no title in header, default for pages/posts
		// Meta options, title set to header
		if ( function_exists('rf_show_page_title') && rf_show_page_title('meta-value') === 'in-header' ) {
			$title = get_the_title( get_queried_object_id() ); // don't use get_the_ID(), it can change
		}
	}

	// Home Page
	if (is_front_page() || is_home()) {
		if ( get_option('show_on_front') == 'page' && (int) get_option('page_for_posts') === get_queried_object_ID() ) {
			return $title; // don't change the "blog page" if not front page
		} else {
			$title = get_options_data('home-page', 'home-header-title');
		}
	}

	// Author
	if ( is_author() ) {
		// Get author info
		$author = get_queried_object();
		$posts_by = '<span class="author-name">'. __('Posts by', 'framework'). ' ' .$author->display_name .'</span>';
		$avatar = '<div class="author-avatar">'. get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'theme_author_bio_avatar_size', 120 ) ) .'</div>';
		// Create the title
		$title = $avatar . $posts_by;
	}

	return $title;
}
endif;
add_filter( 'theme_header_title', 'show_theme_header_title_filter', 11 );


// Header sub-title filters
// ................................................................
if ( ! function_exists( 'show_theme_header_subtitle_filter' ) ) :
function show_theme_header_subtitle_filter( $content = '' ) {
	global $shortname;

	// Archive
	if (is_archive()) {
		$term_description = term_description();
		if ( ! empty( $term_description ) ) :
			$content = sprintf( '<div class="taxonomy-description">%s</div>', $term_description );
		endif;
	}

	// Author
	if ( is_author() ) {
		// Intro Text / Sub-title
		if ( get_the_author_meta( 'description' ) ) {
			$content = get_the_author_meta( 'description' );
		}
	}

	return $content;
}
endif;
add_filter( 'theme_header_content', 'show_theme_header_subtitle_filter', 11 );


#-----------------------------------------------------------------
# Filters for plugin: Simple Theme Slider
#-----------------------------------------------------------------
/**
 * Plugin: Simple Theme Slider
 * Define fileds available for each slide.
 *
 * Specify the default field name, value and lable creating a new
 * array instance for each input. These inputs will appear as the
 * options for each slide created in the admin for the plugin.
 *
 * Field types: text, textarea and checkbox
 *
 * Example:
 *
 * 	$fields[{field_name}] = array(
 * 		'type'  => {field_type},
 * 		'label' => {label_text},
 * 		'value' => {default_value}
 * 	);
 */
if ( ! function_exists( 'theme_simple_slider_input_fields' ) ) :
function theme_simple_slider_input_fields( $fields = array() ) {

	// Text (Title)
	$fields['title'] = array(
		'type'  => 'text',  // the field type
		'label' => __('Title', 'framework'), // the label
		'value' => '',      // the default value
	);
	// Textarea (Description)
	$fields['description'] = array(
		'type'  => 'textarea',
		'label' => __('Description', 'framework'),
		'value' => '',
	);
	// Text (URL)
	$fields['slide-link'] = array(
		'type'  => 'text',
		'label' => __('Link URL', 'framework'),
		'value' => '',
	);
	// Checkbox (Open in new window)
	$fields['open-new-window'] = array(
		'type'  => 'checkbox',
		'label' => __('Open in New Window', 'framework'),
		'value' => 'checked',
	);

	return $fields;
}
add_filter('st_slider_fields', 'theme_simple_slider_input_fields' );
endif;


#-----------------------------------------------------------------
# Filters for plugin: Beaver Builder
#-----------------------------------------------------------------
// BB Upgrade
//................................................................
if ( ! function_exists( 'parallelus_fl_builder_upgrade_url' ) ) :
	function parallelus_fl_builder_upgrade_url( $url ) {

		return 'http://para.llel.us/+/beaverbuilder';
	}
	add_filter( 'fl_builder_upgrade_url', 'parallelus_fl_builder_upgrade_url', 9999 );
endif;


#-----------------------------------------------------------------
# Filters for plugin: Ninja Forms
#-----------------------------------------------------------------
// NF Upgrade
//................................................................
if ( ! function_exists( 'parallelus_ninja_forms_affiliate_id' ) ) :
	function parallelus_ninja_forms_affiliate_id( $id ) {

		return '1311242';
	}
	add_filter( 'ninja_forms_affiliate_id', 'parallelus_ninja_forms_affiliate_id', 9999 );
endif;
