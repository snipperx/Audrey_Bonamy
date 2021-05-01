<?php

#-----------------------------------------------------------------
# Enable shortdoces in sidebar default Text widget
#-----------------------------------------------------------------
add_filter('widget_text', 'do_shortcode');


#-----------------------------------------------------------------
# Conditional for Custom Theme Home Page
#-----------------------------------------------------------------

// Returns true when the home page shows the custom theme design.
//................................................................
/**
 * WordPress functions for is_home and is_front_page are not
 * consistant in cases where the front page is set to use a static
 * page, yet no selection for the page ID is made. When that
 * happens the user can have the custom theme home page and set a
 * blog posts page also. The result, testing for is_front_page
 * will never return true but is_home will be true.
 *
 * This function let's us know if the home page we expect to be
 * seeing is in fact what is visible.
 *
 */
if ( ! function_exists( 'theme_is_custom_home_page' ) ) :
function theme_is_custom_home_page() {

	if (is_front_page() || is_home()) {
		// out('is a front/home page...');
		// Home Page, but not a 'page_for_posts'
		if ( get_option('show_on_front') == 'page' && (int) get_option('page_for_posts') === get_queried_object_ID() ) {
			// out('page on front: '.get_option('page_for_posts').' query id: '. get_queried_object_ID());
			// Do nothing. This is the blog page.
			return false;
		} elseif ( get_option('show_on_front') == 'page' && (int) get_option('page_on_front') === 0 ) {
			// out('page on front: '.get_option('page_for_posts').' query id: '. get_queried_object_ID());
			// It's the real home, with posts by default because no static pages is set.
			return true;
		} elseif (get_option('show_on_front') == 'page') {
			// out('else... show on front: '.get_option('show_on_front'));
			// This is the home page, but it's a static page
			return (basename(get_page_template()) == 'homepage-1.php')? true : false;
		}

		return true;
	}

	return false;
}
endif;


#-----------------------------------------------------------------
# Excerpt Functions
#-----------------------------------------------------------------

// Replace "[...]" in excerpt with "..."
//................................................................
function new_excerpt_more($excerpt) {
	return str_replace(array('[...]','[&hellip;]'), '&hellip;', $excerpt);
}
add_filter('wp_trim_excerpt', 'new_excerpt_more');


// Modify the WordPress excerpt length
//................................................................
/**
 * We set this pretty high because our "customExcerpt" function
 * uses the WordPress excerpt for its source since it is already
 * stripped of HTML, images, shortcodes, etc.
 *
 */
function new_excerpt_length($length) {
	return 60;
}
add_filter('excerpt_length', 'new_excerpt_length');


// Custom Length Excerpts
//................................................................
/**
 * Usage:
 *
 * echo customExcerpt(get_the_content(), 30);
 * echo customExcerpt(get_the_content(), 50);
 * echo customExcerpt($your_content, 30);
 *
 */
function customExcerpt($excerpt = '', $excerpt_length = 50, $tags = '', $trailing = '...') {
	global $post;

	if (has_excerpt()) {
		// see if there is a user created excerpt, if so we use that without any trimming
		return  get_the_excerpt();
	} else {
		// otherwise make a custom excerpt
		$string_check = explode(' ', $excerpt);
		if (count($string_check, COUNT_RECURSIVE) > $excerpt_length) {
			$excerpt = strip_shortcodes( $excerpt );
			$new_excerpt_words = explode(' ', $excerpt, $excerpt_length+1);
			array_pop($new_excerpt_words);
			$excerpt_text = implode(' ', $new_excerpt_words);
			$temp_content = strip_tags($excerpt_text, $tags);
			$short_content = preg_replace('`\[[^\]]*\]`','',$temp_content);
			$short_content .= $trailing;

			return $short_content;
		} else {
			// no trimming needed, excerpt is too short.
			return $excerpt;
		}
	}
}

#-----------------------------------------------------------------
# Enqueue and script registration
#-----------------------------------------------------------------

// Google Fonts Query
//................................................................
/**
 * Returns a font query to enqueue Google fonts from Runway font
 * picker array.
 *
 * Example Usage:
 *
 * $gFont = array();
 * $gFont[] = get_options_data('options-page', 'font-body-google');
 * $gFont[] = get_options_data('options-page', 'font-heading-google');
 *
 * // Get the Google Font query string
 * $gFontQuery = rf_google_fonts_query( $gFont );
 *
 * // Enqueue the fonts
 * wp_enqueue_style( 'google-font', $gFontQuery, array(), null );
 *
 */
if ( ! function_exists( 'rf_google_fonts_query' ) ) :
function rf_google_fonts_query( $fonts = array() ) {

	$query = false;

	// Main fonts array
	$googleFonts = array();

	// Parse the data of each font
	if ( is_array($fonts) && count($fonts)) {
		foreach ($fonts as $font) {

			// for properly work in Customize
			if (is_object($font)) {
				$font = json_decode(json_encode($font), true);
			}

			if (!empty($font)) {
				// get all the font styles (300, 400, 400italic, etc...)
				$style = array();
				foreach (explode(',', $font['weight']) as $weight) {
					$weight = trim($weight);
					if (!empty($weight)) {
						$style[] = $weight;
						$style[] = $weight .'italic';
					}
				}

				// combine each font's options (fontname:400,400italic,800,800italic)
				$googleFonts[] = $font['family'] .':'. implode(',', $style);
			}
		}
	}

	// Convert to query string
	if ( count($googleFonts) ) {

		$gFontList  = str_replace(' ', '+', implode('|', $googleFonts)); // make ready for query string
		$protocol   = is_ssl() ? 'https' : 'http';
		$subsets    = 'latin,latin-ext';
		$query_args = array( 'family' => $gFontList, 'subset' => $subsets );
		$query = add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" );
	}

	return apply_filters('google_fonts_query', $query);
}
endif; // rf_google_fonts_query

/**
 * Google Font Query
 *
 * Returns a separate font query to enqueue Google fonts from Runway font
 * picker array.
 *
 * Has an additional filter for font subsets
 *
 * @param array $font    Runway font picker array
 * @param string $alias  The alias for font subsets filtering
 *
 * @return string $query The Google font query string
 */
if ( ! function_exists( 'rf_google_font_query' ) ) :
    function rf_google_font_query( $font = array(), $alias = '' ) {

        $query      = false;
        $googleFont = '';

        // for properly work in Customize
        if ( is_object( $font ) ) {
            $font = json_decode( json_encode( $font ), true );
        }
        $font = (array) $font;

        if ( ! empty( $font ) ) {
            $style = array();
            foreach ( explode( ',', $font['weight'] ) as $weight ) {
                $weight = trim( $weight );
                if ( ! empty( $weight ) ) {
                    $style[] = $weight;
                    $style[] = $weight . 'italic';
                }
            }

            $googleFont = $font['family'] . ':' . implode( ',', $style );
            $googleFont = str_replace( ' ', '+', $googleFont ); // make ready for query string
            $protocol   = is_ssl() ? 'https' : 'http';

            // subsets
            $subsets = (array) apply_filters(
                'google_font_subsets_' . $alias,
                isset( $font['subset'] ) ? $font['subset'] : array( 'latin', 'latin-ext' )
            );
            $subsets = array_unique( $subsets );

            $query_args = array( 'family' => $googleFont, 'subset' => implode( ',', $subsets ) );
            $query      = add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" );
        }

        return apply_filters( 'google_fonts_query', $query );

    }
endif; // rf_google_font_query

#-----------------------------------------------------------------
# Color Converstions
#-----------------------------------------------------------------

// HEX->RGB
//................................................................
if ( ! function_exists( 'HexToRGB' ) ) :
function HexToRGB($hex) {
	$hex = str_replace("#", "", $hex);
	$color = array();

	if(strlen($hex) == 3) {
		$color['r'] = hexdec(substr($hex, 0, 1) . $r);
		$color['g'] = hexdec(substr($hex, 1, 1) . $g);
		$color['b'] = hexdec(substr($hex, 2, 1) . $b);
	}
	else if(strlen($hex) == 6) {
		$color['r'] = hexdec(substr($hex, 0, 2));
		$color['g'] = hexdec(substr($hex, 2, 2));
		$color['b'] = hexdec(substr($hex, 4, 2));
	}

	return $color;
}
endif;

// Convert Hex to RGB with opacity. Returns: string, 'rgba(123,123,123, 1)'
if ( ! function_exists( 'get_as_rgba' ) ) :
function get_as_rgba($hex = '#000000', $opacity = 1) {
	$rgb = HexToRGB($hex);
	$rgba = 'rgba('.$rgb['r'].','.$rgb['g'].','.$rgb['b'].','.$opacity.')';

	return $rgba;
}
endif;

// Calls 'get_as_rgba()' and prints return value.
if ( ! function_exists( 'as_rgba' ) ) :
function as_rgba($hex = '#000000', $opacity = 1) {
	$rgba = get_as_rgba($hex, $opacity);
	echo  $rgba;
}
endif;

/**
 * Other color helper functions
 * ...............................................................
 * Additional color functions in 'class-color.php' for tasks
 * including: lighten, darken, mix, CSS gradient, complementary,
 * isLight, isDark (detect brightness), etc...
 */


#-----------------------------------------------------------------
# Test for Widgets in a Sidebar
#-----------------------------------------------------------------

if ( ! function_exists( 'is_sidebar_active' ) ) :
function is_sidebar_active($index) {
	global $wp_registered_sidebars;

	$widgetcolums = wp_get_sidebars_widgets();

	if ( isset($widgetcolums[$index]) && $widgetcolums[$index] )
		return true;

	return false;
}
endif;


#-----------------------------------------------------------------
# Other stuff
#-----------------------------------------------------------------

// Fix wmode in WP oEmbeds
//................................................................
/**
 * Prevents iframes (like YouTube) from floating over menus z-indexed CSS
 */
function add_video_wmode_transparent($html, $url, $attr) {
   if (strpos($html, "<embed src=" ) !== false) {
        $html = str_replace('</param><embed', '</param><param name="wmode" value="transparent"></param><embed wmode="transparent" ', $html);
        return $html;
   } else {
        return $html;
   }
}
add_filter('embed_oembed_html', 'add_video_wmode_transparent', 10, 3);

// Extract YouTube ID
//................................................................
/**
 * Gets a YouTube ID from the URL
 */
function theme_get_youtube_id( $url = '' ) {

	if ( !empty($url) && preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match) )
	    return $match[1];

	return false;
}

// Fix for Jetpack error
//................................................................
/**
 * Overrides problem with opengraph tags resulting in error message:
 * EXAMPLE: Warning: preg_match() expects parameter 2 to be string, object given in .../wp-includes/post-template.php
 *
 * The problem is specific to the way the opengraph functions get
 * excerpts when applying certain filters, which calls the_content()
 * and returns an object instead of a string, resulting in the error.
 */

// Oh, Jetpack. :/
add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );

// Add Runway credits
//................................................................
function built_with_runway() {
	echo '<style type="text/css">#footer-thankyou, .vc-license-activation-notice, .rs-update-notice-wrap { display:none; } </style>';
	echo '<script>jQuery("p#footer-left").html(\'Built with <a href="http://runwaywp.com" target="_blank">Runway</a> for <a href="http://wordpress.org" target="_blank">WordPress</a>\');</script>';
}
add_action('admin_footer', 'built_with_runway');


?>