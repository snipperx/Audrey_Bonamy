<?php


#-----------------------------------------------------------------
# Minify and cache custom JS
#-----------------------------------------------------------------

// Minify the custom JS
//-----------------------------------------------------------------
if ( ! function_exists( 'theme_minify_custom_js' ) ) :
function theme_minify_custom_js( $js = '', $comments = false ) {

	if ($comments) {
		// Basic (default) usage.
		$js = \JShrink\Minifier::minify($js);
	} else {
		// Disable YUI style comment preservation.
		$js = \JShrink\Minifier::minify($js, array('flaggedComments' => false));
	}

	return $js;
}
endif;

// Cache the custom JS
//-----------------------------------------------------------------
/**
 * Calling with no params will store (cache) the minified JS
 *
 * Possible $return param valuse:
 * 	'alias' = return the alias of the cache DB field
 * 	'js' = return the minified js
 *  'cache' = cached database values
 */
if ( ! function_exists( 'theme_cache_custom_js' ) ) :
function theme_cache_custom_js( $return = false ) {
	global $shortname;

	$optionsPageAlias = 'options-page';
	$optionsFieldAlias = 'custom-script';
	$cacheAlias = md5($shortname).'_cacheJS';

	if ($return == 'alias') {
		return $cacheAlias;
	}
	if ($return == 'cache') {
		return get_option( $cacheAlias );
	}
	// Clear old cache value
	if ($return == 'reset') {
		return update_option( $cacheAlias, '' );
	}

	// Get the saved JS from theme options
	$customJS = htmlspecialchars_decode(get_options_data($optionsPageAlias, $optionsFieldAlias),ENT_QUOTES);
	$customJS = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "$1", $customJS);

	// Prepare the JS
	if (!empty($customJS)) {
		$customJS = str_replace(array('\r\n','\r','\n'), PHP_EOL, esc_js($customJS)); // remove line break characters
		$customJS = str_replace(array('&gt;','&lt;','&quot;'), array('>','<', ""), $customJS); // fix some escaped characters
		$customJS = stripslashes($customJS); // strip slashes

		// Minify
		$js = theme_minify_custom_js($customJS);
		if ($return == 'js') {
			return $js;
		}

		// Update the cache in DB
		if ($return == 'update') {
			update_option( $cacheAlias, $js );
			return $js;
		}
	}
}
endif;

// Clear the JS cache in DB
//-----------------------------------------------------------------
if ( ! function_exists( 'theme_cache_update_custom_js' ) ) :
function theme_cache_update_custom_js($option, $old_value, $value) {
	global $shortname;

	/**
	 * When the theme options are updated we delete the saved cache
	 * value. Next time the site loads it will notice the empty
	 * cache and create it again automatically
	 */
	if ( $option == $shortname."options-page") {
		theme_cache_custom_js( 'reset' );
	}
}
endif;
add_action( "update_option", 'theme_cache_update_custom_js', 10, 3 );


#-----------------------------------------------------------------
# Include Custom JavaScript in Theme Footer
#-----------------------------------------------------------------


// Add scripts to footer
//-----------------------------------------------------------------
if ( ! function_exists( 'theme_footer_custom_js' ) ) :
function theme_footer_custom_js() {
	// Custom Scripts from Theme Options
	echo '<script type="text/javascript" id="custom-theme-js">';
	theme_footer_custom_scripts();
	echo '</script>';
}
endif;
// Add hook for WP footer
add_action('wp_footer', 'theme_footer_custom_js', 101); // low priority to get it near the end


// Get custom JavaScript from theme options
//-----------------------------------------------------------------
if ( ! function_exists( 'theme_footer_custom_scripts' ) ) :
function theme_footer_custom_scripts() {

	// Custom JavaScript
	$customJS = theme_cache_custom_js('cache'); // get the cached JS

	if (empty($customJS)) {
		// maybe there's no cache because of some error, or it's been cleared and we need to recreate it.
		$customJS = theme_cache_custom_js('update');
	}

	if (!empty($customJS)) {
		echo  $customJS; // escaped above
	}

	// Fallbacks for CDN. This ensures a local copy is loaded if a script from a CDN fails.
	echo 'if (typeof jQuery.fn.fitVids === "undefined") { document.write("<script src=\''. rf_get_template_directory_uri().'/assets/js/jquery.fitvids.min.js\'>\x3C/script>"); }'; // FitVids
}
endif;



#-----------------------------------------------------------------
# Include Custom JavaScript in Theme Header
#-----------------------------------------------------------------


// Add scripts to header
//-----------------------------------------------------------------
if ( ! function_exists( 'theme_header_custom_js' ) ) :
function theme_header_custom_js() {
	// Custom Scripts from Theme Options
	echo '<script type="text/javascript">';
	theme_header_custom_scripts();
	echo '</script> ';
}
endif;
// Add hook for WP header
add_action('wp_header', 'theme_header_custom_js', 101); // low priority to get it near the end


// Get custom JavaScript from theme options
//-----------------------------------------------------------------
if ( ! function_exists( 'theme_header_custom_scripts' ) ) :
function theme_header_custom_scripts() {

	/**
	 * POSSIBLE FUTURE USE (theme option does not exist currently)
	 **********************************************************************
		// Custom JavaScript
		$customJS = htmlspecialchars_decode(get_options_data('options-page', 'custom-script-header'),ENT_QUOTES);
	        $customJS = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "$1", $customJS);
		if (!empty($customJS)) {
			echo stripslashes(esc_js($customJS));
		}
	 *
	 **********************************************************************
	*/

	// Fallbacks for CDN. This ensures a local copy is loaded if a script from a CDN fails.
	// echo 'if (typeof jQuery.fn.fitVids === "undefined") { document.write("<script src=\'js/libs/jquery.tooltip.min.js\'>\x3C/script>"); }'; // FitVids
}
endif;

