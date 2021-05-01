<?php


#-----------------------------------------------------------------
# Minify and cache custom CSS
#-----------------------------------------------------------------

// Minify CSS
//-----------------------------------------------------------------
/**
 * Based on: http://davidwalsh.name/css-compression-php
 */

if ( ! function_exists( 'theme_minify_css' ) ) :
function theme_minify_css( $css = '' ) {
	// Remove comments
	$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
	// Remove spaces before and after symbols
	// $css = preg_replace('/(\s(?=\W))|((?<=\W)\s)/', '', $css);
	$css = str_replace(array( ': ', '; ', '} ', ' }', '{ ', ' {', ', ' ), array( ':', ';', '}', '}', '{', '{', ',' ), $css);
	// Remove remaining whitespace
	$css = str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '), '', $css);

	return $css;
}
endif;

// Cache the custom CSS
//-----------------------------------------------------------------
/**
 * Calling with no params will store (cache) the minified CSS
 *
 * Possible $return param valuse:
 * 	'alias' = return the alias of the cache DB field
 * 	'css' = return the minified css
 *  'cache' = cached database data
 *  'update' = update the cache in the DB
 */
if ( ! function_exists( 'theme_cache_custom_css' ) ) :
function theme_cache_custom_css( $return = false ) {
	global $shortname;

	$cacheAlias = md5($shortname).'_cacheCSS';

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

	// Get the compiled CSS from theme options
	$customCSS = theme_custom_styles();

	// Prepare the CSS
	if (!empty($customCSS)) {

		// Minify
		$css = theme_minify_css($customCSS);
		if ($return == 'css') {
			return $css;
		}

		// Update the cache in DB
		if ($return == 'update') {
			update_option( $cacheAlias, $css );
			return $css;
		}
	}
}
endif;

// Clear the CSS cache in DB
//-----------------------------------------------------------------
if ( ! function_exists( 'theme_cache_update_custom_css' ) ) :
function theme_cache_update_custom_css($option, $old_value, $value) {
	global $shortname;

	/**
	 * When the theme options are updated we delete the saved cache
	 * value. Next time the site loads it will notice the empty
	 * cache and create it again automatically
	 */
	if ( $option == $shortname."options-page") {
		theme_cache_custom_css( 'reset' );
	}
}
endif;
add_action( "update_option", 'theme_cache_update_custom_css', 10, 3 );



#-----------------------------------------------------------------
# Include Custom CSS in Theme Header
#-----------------------------------------------------------------


// Add styles to header
//-----------------------------------------------------------------
if ( ! function_exists( 'theme_options_custom_css' ) ) :
function theme_options_custom_css() {

	$custom_css = theme_get_cache_styles();

	if (!empty($custom_css)) {
		wp_add_inline_style( 'theme-style', $custom_css ); // $handle must match existing CSS file.
	}
}
endif;
add_action( 'wp_enqueue_scripts', 'theme_options_custom_css', 11 );


// Get custom styles cache (or backup theme options)
//-----------------------------------------------------------------
if ( ! function_exists( 'theme_get_cache_styles' ) ) :
function theme_get_cache_styles() {
	global $wp_customize;

	$customCSS = '';

	// Custom Styles
	$customCSS = theme_cache_custom_css('cache'); // get the cached CSS

	if (empty($customCSS) || !empty($wp_customize)) {
		// maybe there's no cache because of some error, or it's been cleared and we need to recreate it.
		// or maybe this is just the Customizer view and we want live updates...
		$customCSS = theme_cache_custom_css('update');
	}

	return  $customCSS; // escaped above
}
endif;


// Get custom styles from theme options
//-----------------------------------------------------------------
if ( ! function_exists( 'theme_custom_styles' ) ) :
function theme_custom_styles() {

	// Styles variable
	$CustomStyles = '';

	#-----------------------------------------------------------------
	# Styles from Theme Options
	#-----------------------------------------------------------------

	// Accent Color - Primary
	//................................................................

	$accent_index = array('1','2','3');

	// Accent Colors
	foreach( $accent_index as $index ) {
		$accent_color[$index] = get_options_data('options-page', 'color-accent-'.$index);

		if (!empty($accent_color[$index]) && $accent_color[$index] !== '#') {

			// get the color so we can modify it.
			$color = new Color($accent_color[$index]);
			// text over accent color
			$color_alt = $color->lighten(10);
			$color_text = get_as_rgba('#ffffff', 0.9);
			$color_text_alt = $color->lighten(20);
			if ($color->isLight()) {
				$color_alt = $color->darken(10);
				$color_text = get_as_rgba('#000000', 0.9);
				$color_text_alt = $color->darken(20);
			}

			$accentStyles  = '';

			// Color 1 Only
			//................................................................
			if ($index == '1') {
				// Accent Background
				$accentStyles .= '.accent-1-bg, .navbar-default, .header-bg-wrapper .logo-container .logo, .header-links-item .overlay, .header-links-item.solid-primary, .header-links-item.solid-primary .overlay { background-color: #'. $color->getHex() .'; color: '.$color_text.'; }';
				// Accent Text
				$accentStyles .= '.accent-1-text { color: #'. $color->getHex() .'; }';
				// Alternate Text (hover: lighten/darken)
				$accentStyles .= 'a.accent-1-text:hover { color: #'. $color_text_alt.'; }';
				// Border color
				$accentStyles .= '.navbar-default, .navbar-default .navbar-collapse, .navbar-default .navbar-form, .navbar-form .form-control { border-color: #'. $color->getHex() .'; }';
			}

			// Color 2 Only
			//................................................................
			if ($index == '2') {
				// Accent Background
				$accentStyles .= '.accent-2-bg, .accent-box, .donate-box, .newsletter-box, .navbar-wrapper .navbar ul#nav-right li, .navbar-vertical ul#nav-right li, .header-links-item.accent .overlay, .header-links-item.solid-accent, .header-links-item.solid-accent .overlay { background-color: #'. $color->getHex() .'; color: '.$color_text.'; }';
				// Accent Text
				$accentStyles .= '.accent-2-text, .list-group-item.active > .badge, .nav-pills > .active > a > .badge, h1 a:hover, .h1 a:hover, h2 a:hover, .h2 a:hover, h3 a:hover, .h3 a:hover, h4 a:hover, .h4 a:hover, h5 a:hover, .h5 a:hover, h6 a:hover, .h6 a:hover, .box .amount label.btn.on, .box .amount labelbutton.on, .box .amount labelinput[type="button"].on, .box .amount labelinput[type="submit"].on, .post a.more-link:hover, .entry a.more-link:hover, .issue a.more-link:hover, .timeline .date-title, .search-result h3 a, .search-result .result-title a  { color: #'. $color->getHex() .'; }';
				// Alternate Text (hover: lighten/darken)
				$accentStyles .= 'a.accent-2-text:hover { color: #'. $color_text_alt.'; }';
				// Border color
				$accentStyles .= 'a.thumbnail:hover, a.thumbnail:focus, a.thumbnail.active, .nav .open > a, .nav .open > a:hover, .nav .open > a:focus { border-color: #'. $color->getHex() .'; }';
				$accentStyles .= '.video-list .video-thumbnail { border-bottom-color: #'. $color->getHex() .'; }';
			}

			// Color 3 Only
			//................................................................
			if ($index == '3') {
				// Accent Background
				$accentStyles .= '.accent-3-bg, .bg-primary, .btn-primary, body input[type="submit"], .btn-primary.disabled, .btn-primary[disabled], .btn-primary[disabled]:hover, .btn-primary[disabled]:focus, .btn-primary[disabled]:active, body input[type="submit"][disabled], body input[type="submit"][disabled]:hover, body input[type="submit"][disabled]:focus, body input[type="submit"][disabled]:active, .dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus, .nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus, .label-primary, .progress-bar, .list-group-item.active, .list-group-item.active:hover, .list-group-item.active:focus, .panel-primary, .panel-primary > .panel-heading, .btn-group .dropdown-toggle.btn-primary ~ .dropdown-menu, .btn-group .dropdown-togglebody input[type="submit"] ~ .dropdown-menu { background-color: #'. $color->getHex() .'; color: '.$color_text.'; }';
				// Accent Text
				$accentStyles .= '.accent-3-text, .text-primary, .btn-primary .badge, body input[type="submit"] .badge, .panel-primary > .panel-heading .badge, .continue-link a, a.continue-link { color: #'. $color->getHex() .'; }';
				// Alternate Text (hover: lighten/darken)
				$accentStyles .= 'a.accent-3-text:hover, a.text-primary:hover, a.text-primary:focus, .continue-link a:hover, a.continue-link:hover { color: #'. $color_text_alt.'; }';
				// Border color
				$accentStyles .= '.form-control:focus, .btn-primary, body input[type="submit"], .btn-primary.disabled, .btn-primary[disabled], .btn-primary[disabled]:hover, .btn-primary[disabled]:focus, .btn-primary[disabled]:active, body input[type="submit"][disabled], body input[type="submit"][disabled]:hover, body input[type="submit"][disabled]:focus, body input[type="submit"][disabled]:active, .list-group-item.active, .list-group-item.active:hover, .list-group-item.active:focus, .panel-primary, .panel-primary > .panel-heading, .btn-group .dropdown-toggle.btn-primary ~ .dropdown-menu, .btn-group .dropdown-togglebody input[type="submit"] ~ .dropdown-menu, input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, textarea:focus, select:focus, input[type="text"].form-control:focus, input[type="email"].form-control:focus, input[type="password"].form-control:focus, textarea.form-control:focus, select.form-control:focus { border-color: #'. $color->getHex() .'; }';
				$accentStyles .= '.panel-primary > .panel-heading + .panel-collapse > .panel-body { border-top-color: #'. $color->getHex() .'; }';
				$accentStyles .= '.panel-primary > .panel-footer + .panel-collapse > .panel-body { border-bottom-color: #'. $color->getHex() .'; }';
				// Alternate Background (lighten/darken)
				$accentStyles .= 'a.bg-primary:hover, a.bg-primary:focus, .btn-primary:focus, .btn-primary.focus, body input[type="submit"]:focus, body input[type="submit"].focus, .btn-primary:hover, body input[type="submit"]:hover, .btn-primary:active, .btn-primary.active, .open > .dropdown-toggle.btn-primary, body input[type="submit"]:active, body input[type="submit"].active, .open > .dropdown-togglebody input[type="submit"], .label-primary[href]:hover, .label-primary[href]:focus { background-color: #'. $color_alt .'; color: '.$color_text.'; }';
				// Alternate Border color (lighten/darken)
				$accentStyles .= '.btn-primary:focus, .btn-primary.focus, body input[type="submit"]:focus, body input[type="submit"].focus, .btn-primary:active:hover, .btn-primary.active:hover, .open > .dropdown-toggle.btn-primary:hover, .btn-primary:active:focus, .btn-primary.active:focus, .open > .dropdown-toggle.btn-primary:focus, .btn-primary:active.focus, .btn-primary.active.focus, .open > .dropdown-toggle.btn-primary.focus, body input[type="submit"]:active:hover, body input[type="submit"].active:hover, .open > .dropdown-togglebody input[type="submit"]:hover, body input[type="submit"]:active:focus, body input[type="submit"].active:focus, .open > .dropdown-togglebody input[type="submit"]:focus, body input[type="submit"]:active.focus, body input[type="submit"].active.focus, .open > .dropdown-togglebody input[type="submit"].focus { border-color: #'. $color->getHex() .'; }';

				// Ninja Forms submit buttons
				$accentStyles .= '.submit-wrap input[type="button"], .submit-wrap input[type="button"][disabled], .submit-wrap input[type="button"][disabled]:hover, .submit-wrap input[type="button"][disabled]:focus, .submit-wrap input[type="button"][disabled]:active { background-color: #'. $color->getHex() .'; color: '.$color_text.'; border-color: #'. $color->getHex() .'; }';
				$accentStyles .= '.submit-wrap input[type="button"]:focus, .submit-wrap input[type="button"]:hover, .submit-wrap input[type="button"]:active { background-color: #'. $color_alt .'; color: '.$color_text.'; }';
				$accentStyles .= '.submit-wrap input[type="button"]:focus, .submit-wrap input[type="button"]:active:hover, .submit-wrap input[type="button"]:active:focus { border-color: #'. $color->getHex() .'; }';
			}

			// Add styles to CSS variable
			$CustomStyles .= $accentStyles;
		}

		unset($color);
	}

	// Links
	//................................................................

	$linkColor = get_options_data('options-page', 'link-color');
	if (!empty($linkColor) && $linkColor != '#') {
		$linkStyles  = "a, .widget a, .btn.btn-link { color: ". $linkColor ."; }";
		$linkStyles .= ".btn.btn-link { border-color: ". $linkColor ."; }";
		// Add styles to CSS variable
		$CustomStyles .= $linkStyles;
	}
	// Hover (links)
	$hoverColor = get_options_data('options-page', 'link-hover-color');
	if (!empty($hoverColor) && $hoverColor != '#') {
		$linkHoverStyles  = "a:hover, a:focus, .widget a:hover, .btn.btn-link:hover, .btn.btn-link:focus, .search-result h3 a:hover, .search-result .result-title a:hover { color: ". $hoverColor ."; }";
		$linkHoverStyles .= ".btn.btn-link:hover, .btn.btn-link:focus { border-color: ". $hoverColor ."; }";
		// Add styles to CSS variable
		$CustomStyles .= $linkHoverStyles;
	}

	// Navigation Menus
	//----------------------------------------------------------------
	$menuStyles = '';
	$menuBackgroundDefault = get_options_data('options-page', 'color-accent-1');
	$menuBackground = get_options_data('options-page', 'menu-background');
	$menuTextColor = get_options_data('options-page', 'menu-text-color');
	$menuAccentColor = get_options_data('options-page', 'menu-accent');
	$menuSubNavColor = get_options_data('options-page', 'menu-drop-down');
	$menuSubNavText = get_options_data('options-page', 'menu-drop-down-text');
	$menuSubNavHover = get_as_rgba('#000000', 0.15);
	// Alternate menu styles
	$menuAltBackgroundDefault = get_options_data('options-page', 'color-accent-2');
	$menuAltBackground = get_options_data('options-page', 'menu-alt-background');
	$menuAltTextColor = get_options_data('options-page', 'menu-alt-text-color');
	$menuAltAccentColor = get_options_data('options-page', 'menu-alt-accent');
	$menuAltSubNavColor = get_options_data('options-page', 'menu-alt-drop-down');
	$menuAltSubNavText = get_options_data('options-page', 'menu-alt-drop-down-text');
	$menuAltSubNavHover = get_as_rgba('#000000', 0.15);

	// Text color
	$menuTextColor = (!empty($menuTextColor) && $menuTextColor !== '#') ? $menuTextColor : '';
	$menuAltTextColor = (!empty($menuAltTextColor) && $menuAltTextColor !== '#') ? $menuAltTextColor : '';
	// Sub-Navigation colors
	$subNavText = (!empty($menuSubNavText) && $menuSubNavText !== '#') ? $menuSubNavText : '';
	$subNavBg = (!empty($menuSubNavColor) && $menuSubNavColor !== '#') ? $menuSubNavColor : '';
	$subNavAltText = (!empty($menuAltSubNavText) && $menuAltSubNavText !== '#') ? $menuAltSubNavText : '';
	$subNavAltBg = (!empty($menuAltSubNavColor) && $menuAltSubNavColor !== '#') ? $menuAltSubNavColor : '';
	// Menu Background Color
	$menuBackgroundDefault = (!empty($menuBackgroundDefault) && $menuBackgroundDefault !== '#') ? $menuBackgroundDefault : '';
	$menuBackground = (!empty($menuBackground) && $menuBackground !== '#') ? $menuBackground : $menuBackgroundDefault; // set to default if no color
	$menuAltBackgroundDefault = (!empty($menuAltBackgroundDefault) && $menuAltBackgroundDefault !== '#') ? $menuAltBackgroundDefault : '';
	$menuAltBackground = (!empty($menuAltBackground) && $menuAltBackground !== '#') ? $menuAltBackground : $menuAltBackgroundDefault; // set to default if no color

	// Default Navbar
	//................................................................
	$style_menuBackground = '';
	$style_menuBackgroundHover = '';
	$style_menuText = '';
	$style_borderTop = '';
	if (!empty($menuBackground) && $menuBackground !== '#') {

		// color variations...
		$navColor = new Color($menuBackground);
		// Bg
		if (empty($subNavBg)) {
			$subNavBg = ($navColor->isDark()) ? '#'.$navColor->lighten(12) : '#'.$navColor->darken(12);
		}
		// Text
		if (empty($menuTextColor)) {
			$menuText = ($navColor->isDark()) ? get_as_rgba('#ffffff', 0.9) : get_as_rgba('#000000', 0.9);
		} else {
			$menuText = $menuTextColor;
		}
		$menuBackgroundHover = $subNavBg;
		unset($navColor);

		// styles
		$style_menuBackground = "background-color: ". $menuBackground ."; ";
		$style_menuBackgroundHover = "background-color: ". $menuBackgroundHover ."; ";
		$style_menuBorder = "border-color: ".$menuBackground."; ";
	}
	// Default Navbar Text
	if (!empty($menuText) && $menuText !== '#') {
		$style_menuText = "color: ".$menuText."; ";
		$style_borderTop = "border-top-color: ".$menuText."; ";
	}
	if (!empty($style_menuBackground) || !empty($style_menuText)) {
		$menuStyles .= ".navbar-default { ". $style_menuBackground . $style_menuText ." }";
		$menuStyles .= ".navbar-default .navbar-brand, .navbar-default .navbar-brand:hover, .navbar-default .navbar-brand:focus, .navbar-default .navbar-text, .navbar-default .navbar-nav > li > a, .navbar-default .navbar-nav > li > a:hover, .navbar-default .navbar-nav > li > a:focus, .navbar-default .navbar-nav > .active > a, .navbar-default .navbar-nav > .active > a:hover, .navbar-default .navbar-nav > .active > a:focus, .navbar-default .navbar-toggle .icon-bar { ". $style_menuText ." }";
		$menuStyles .= ".navbar-default .navbar-form { ". $style_menuBorder ." }";
		// hover
		$menuStyles .= ".navbar-wrapper .navbar .navbar-nav > li.open > a, .navbar-wrapper .navbar .navbar-nav > li > a:hover, .navbar-wrapper .navbar .navbar-nav > li > a:focus { ". $style_menuBackgroundHover ." }";
		// sub-menu indicator arrows
		$menuStyles .= ".navbar .dropdown-toggle::after { ". $style_borderTop ." }";
		// menu toggle
		$menuStyles .= ".navbar-default .navbar-collapse { ". $style_menuText ." }";
	}
	// Sub-menu background
	$style_subNavBg = '';
	$style_subNavText = '';
	$style_menuSubNavHover = '';
	$style_borderLeft = '';
	$style_borderTop = '';
	if (!empty($subNavBg)) {
		// color variations...
		$SubNavColor = new Color($subNavBg);
		if (empty($subNavText)) {
			$subNavText = ($SubNavColor->isDark()) ? get_as_rgba('#ffffff', 0.9) : get_as_rgba('#000000', 0.9);
		}
		$menuSubNavHover = ($SubNavColor->isDark()) ? 'rgba(255,255,255,.14)' : 'rgba(0,0,0,.08)';
		unset($SubNavColor);
		// styles
		$style_subNavBg = "background-color: ". $subNavBg ."; ";
		$style_menuSubNavHover = "background-color: ". $menuSubNavHover ."; ";
	}
	// Sub-menu text
	if (!empty($subNavText)) {
		$style_subNavText = "color: ".$subNavText."; ";
		$style_borderLeft = "border-left-color: ".$subNavText."; border-top-color: transparent !important; ";
		$style_borderTop  = "border-top-color: ".$subNavText." !important; ";
	}
	if (!empty($style_subNavBg) || !empty($style_subNavText)) {
		$menuStyles .= ".navbar-default .dropdown-menu, .navbar-default .navbar-nav > li > a:hover, .navbar-default .navbar-nav > li > a:focus, .navbar-default .navbar-nav > .active > a, .navbar-default .navbar-nav > .active > a:hover, .navbar-default .navbar-nav > .active > a:focus, .navbar-default .navbar-nav > .open > a, .navbar-default .navbar-nav > .open > a:hover, .navbar-default .navbar-nav > .open > a:focus { ". $style_subNavBg . $style_subNavText ." }";
		$menuStyles .= "@media (max-width: 767px) { ".
		                   ".navbar-default .navbar-nav .open .dropdown-menu > li > a:hover, .navbar-default .navbar-nav .open .dropdown-menu > li > a:focus, .navbar-default .navbar-nav .open .dropdown-menu > .active > a, .navbar-default .navbar-nav .open .dropdown-menu > .active > a:hover, .navbar-default .navbar-nav .open .dropdown-menu > .active > a:focus { ". $style_subNavBg . $style_subNavText ." } ".
		               "}";
		// hover
		$menuStyles .= ".navbar-default .dropdown-menu > li > a,  .navbar-default .dropdown-menu > li > a:hover, .navbar-default .dropdown-menu > li > a:focus { ". $style_subNavText ." }";
		$menuStyles .= ".navbar-wrapper .navbar .navbar-nav > li.open li a:hover, .navbar-wrapper .navbar .navbar-nav > li.open li a:focus { ". $style_menuSubNavHover ." }";
		// sub-menu indicator arrow
		$menuStyles .= ".navbar .dropdown.open > .dropdown-toggle::after { ". $style_borderTop ." }";
		$menuStyles .= ".navbar .dropdown-submenu > a.dropdown-toggle:after, .navbar .dropdown-submenu > a.dropdown-toggle:hover:after { ". $style_borderLeft ." }";
	}
	// Accent menu item (active item)
	if (!empty($menuAccentColor)) {
		$style_accentColor = "background: ".$menuAccentColor."; color: ".$subNavText."; ";
		$style_accentArrow = "border-top-color: ".$subNavText."; ";
	}
	if (!empty($style_accentColor)) {
		$menuStyles .= ".navbar-default .navbar-nav > .active > a, .navbar-default .navbar-nav > .active > a:hover, .navbar-default .navbar-nav > .active > a:focus { ". $style_accentColor ." }";
		$menuStyles .= ".navbar .active > .dropdown-toggle::after, .navbar .dropdown.open.active > .dropdown-toggle::after { ". $style_accentArrow ." }";

	}

	// Alternate Navbar
	//................................................................
	$style_menuBackground = '';
	$style_menuBackgroundHover = '';
	$style_menuText = '';
	$style_borderTop = '';
	if (!empty($menuAltBackground) && $menuAltBackground !== '#') {

		// color variations...
		$navColor = new Color($menuAltBackground);
		// Bg
		if (empty($subNavAltBg)) {
			$subNavAltBg = ($navColor->isDark()) ? '#'.$navColor->lighten(12) : '#'.$navColor->darken(12);
		}
		// Text
		if (empty($menuAltTextColor)) {
			$menuText = ($navColor->isDark()) ? get_as_rgba('#ffffff', 0.9) : get_as_rgba('#000000', 0.9);
		} else {
			$menuText = $menuAltTextColor;
		}
		$menuAltBackgroundHover = $subNavAltBg;
		unset($navColor);

		// styles
		$style_menuBackground = "background-color: ". $menuAltBackground ."; ";
		$style_menuBackgroundHover = "background-color: ". $menuAltBackgroundHover ."; ";
		$style_menuBorder = "border-color: ".$menuAltBackground."; ";
	}
	// Default Navbar Text
	if (!empty($menuText) && $menuText !== '#') {
		$style_menuText = "color: ".$menuText."; ";
		$style_borderTop = "border-top-color: ".$menuText."; ";
	}
	if (!empty($style_menuBackground) || !empty($style_menuText)) {
		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right > li, .navbar-vertical ul#nav-right > li { ". $style_menuBackground . $style_menuText ." }";

		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right { ". $style_menuBackground . $style_menuText ." }";
		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right .navbar-text, .navbar-wrapper .navbar ul#nav-right > li > a, .navbar-wrapper .navbar ul#nav-right > li > a:hover, .navbar-wrapper .navbar ul#nav-right > li > a:focus, .navbar-wrapper .navbar ul#nav-right > .active > a, .navbar-wrapper .navbar ul#nav-right > .active > a:hover, .navbar-wrapper .navbar ul#nav-right > .active > a:focus, .navbar-default .navbar-toggle .icon-bar { ". $style_menuText ." }";
		// hover
		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right.navbar-nav > li.open > a, .navbar-wrapper .navbar ul#nav-right.navbar-nav > li > a:hover, .navbar-wrapper .navbar ul#nav-right.navbar-nav > li > a:focus { ". $style_menuBackgroundHover ." }";
		// sub-menu indicator arrows
		$menuStyles .= ".navbar-vertical ul#nav-right .dropdown-toggle::after, .navbar-wrapper .navbar ul#nav-right .dropdown-toggle::after { ". $style_borderTop ." }";
	}
	// Sub-menu background
	$style_subNavBg = '';
	$style_subNavText = '';
	$style_menuSubNavHover = '';
	$style_borderLeft = '';
	$style_borderTop = '';
	if (!empty($subNavAltBg)) {
		// color variations...
		$SubNavColor = new Color($subNavAltBg);
		if (empty($subNavAltText)) {
			$subNavAltText = ($SubNavColor->isDark()) ? get_as_rgba('#ffffff', 0.9) : get_as_rgba('#000000', 0.9);
		}
		// $menuSubNavHover = '#'.$SubNavColor->darken(8); // get_as_rgba('#ffffff', 0.1);
		$menuSubNavAltHover = ($SubNavColor->isDark()) ? 'rgba(255,255,255,.14)' : 'rgba(0,0,0,.08)';
		unset($SubNavColor);
		// styles
		$style_subNavBg = "background-color: ". $subNavAltBg ."; ";
		$style_menuSubNavHover = "background-color: ". $menuSubNavAltHover ."; ";
	}
	// Sub-menu text
	if (!empty($subNavAltText)) {
		$style_subNavText = "color: ".$subNavAltText."; ";
		$style_borderLeft = "border-left-color: ".$subNavAltText."; border-top-color: transparent !important; ";
		$style_borderTop  = "border-top-color: ".$subNavAltText." !important; ";
	}
	if (!empty($style_subNavBg) || !empty($style_subNavText)) {
		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right .dropdown-menu, .navbar-wrapper .navbar ul#nav-right li ul li, .navbar-vertical ul#nav-right li ul li, .navbar-wrapper .navbar ul#nav-right > li > a:hover, .navbar-wrapper .navbar ul#nav-right > li > a:focus, .navbar-wrapper .navbar ul#nav-right > .active > a, .navbar-wrapper .navbar ul#nav-right > .active > a:hover, .navbar-wrapper .navbar ul#nav-right > .active > a:focus, .navbar-wrapper .navbar ul#nav-right > .open > a, .navbar-wrapper .navbar ul#nav-right > .open > a:hover, .navbar-wrapper .navbar ul#nav-right > .open > a:focus { ". $style_subNavBg . $style_subNavText ." }";
		$menuStyles .= "@media (max-width: 767px) { ".
		                   ".navbar-wrapper .navbar ul#nav-right .open .dropdown-menu > li > a:hover, .navbar-wrapper .navbar ul#nav-right .open .dropdown-menu > li > a:focus, .navbar-wrapper .navbar ul#nav-right .open .dropdown-menu > .active > a, .navbar-wrapper .navbar ul#nav-right .open .dropdown-menu > .active > a:hover, .navbar-wrapper .navbar ul#nav-right .open .dropdown-menu > .active > a:focus { ". $style_subNavBg . $style_subNavText ." } ".
		               "}";
		// hover
		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right .dropdown-menu > li > a, .navbar-wrapper .navbar ul#nav-right .dropdown-menu > li > a:hover, .navbar-wrapper .navbar ul#nav-right .dropdown-menu > li > a:focus { ". $style_subNavText ." }";
		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right.navbar-nav > li.open li a:hover, .navbar-wrapper .navbar ul#nav-right.navbar-nav > li.open li a:focus { ". $style_menuSubNavHover ." }";
		// sub-menu indicator arrow
		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right .dropdown.open > .dropdown-toggle::after { ". $style_borderTop ." }";
		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right .dropdown-submenu > a.dropdown-toggle:after, .navbar-wrapper .navbar ul#nav-right .dropdown-submenu > a.dropdown-toggle:hover:after { ". $style_borderLeft ." }";
	}
	// Accent menu item (active item)
	if (!empty($menuAltAccentColor)) {
		$style_accentColor = "background: ".$menuAltAccentColor."; color: ".$subNavAltText."; ";
		$style_accentArrow = "border-top-color: ".$subNavAltText."; ";
	}
	if (!empty($style_accentColor)) {
		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right > .active > a, .navbar-wrapper .navbar ul#nav-right > .active > a:hover, .navbar-wrapper .navbar ul#nav-right > .active > a:focus { ". $style_accentColor ." }";
		$menuStyles .= ".navbar-wrapper .navbar ul#nav-right .active > .dropdown-toggle::after, .navbar-wrapper .navbar ul#nav-right .dropdown.open.active > .dropdown-toggle::after { ". $style_accentArrow ." }";
	}

	// Add styles to CSS variable
	if (!empty($menuStyles)) {
		$CustomStyles .= $menuStyles;
	}

	// Fonts (body)
	//................................................................

	$font = array();
	if (get_options_data('options-page', 'font-body') == 'google') {
		// get google font data
		$gFont = get_options_data('options-page', 'font-body-google');

		// for properly work in Customize
		if (is_object($gFont)) {
			$gFont = json_decode(json_encode($gFont), true);
		}

		$gFontWeight = explode(',', $gFont['weight']);
		$font['family'] = $gFont['family'];
		// $font['weight'] = (count($gFontWeight)) ? $gFontWeight[0] : 'normal';
		$font['size']   = $gFont['size'];
		$font['color']  = $gFont['color'];
	} else {
		// get standard font data
		$font['family'] = get_options_data('options-page', 'font-body-family');
		// $font['weight'] = get_options_data('options-page', 'font-body-weight');
		$font['size']   = get_options_data('options-page', 'font-body-size');
		$font['color']  = get_options_data('options-page', 'font-body-color');
	}

	$elementStyles = '';
	$elementStyles_noSize = '';
	$elementStyles_color = '';
	$elementStyles_family = '';
	$elementStyles_family_weight = '';
	if ( count($font) ) {
		foreach ($font as $attribute => $style) {
			if (!empty($style)) {
				$property = ($attribute != 'color') ? 'font-'.$attribute : $attribute;
				$elementStyles .= $property.': '. $style .';';
				if ($attribute != 'size') {
					$elementStyles_noSize .= $property.': '. $style .';';
				}
				if ($attribute == 'color') {
					$elementStyles_color .= $property.': '. $style .';';
				}
				if ($attribute == 'family') {
					$elementStyles_family .= $property.': '. $style .';';
				}
				if ($attribute == 'family' || $attribute == 'weight') {
					$elementStyles_family_weight .= $property.': '. $style .';';
				}
			}
		}
	}

	if ( !empty($elementStyles)) {
		// default - all boty font styles
		$CustomStyles .= 'body { '.$elementStyles.' }';
		// all but font size
		$CustomStyles .= '.tooltip, .popover, blockquote small, blockquote cite, .heading, #footer.with-overlap .container-box h3, body.videos .video-entry .video-title, body.post-type-archive-political-video .video-entry .video-title, .search-result h3, .search-result .result-title { '.$elementStyles_noSize.' }';
		// only family and weight
		$CustomStyles .= '.box h3, .box .ninja-forms-form-title { '.$elementStyles_family_weight.' }';
		// only family
		$CustomStyles .= '.widget-title, .header-links-item article h3, #footer.with-overlap .container-box .ninja-forms-form-title, .popover-title, .post-nav-popover h3.popover-title { '.$elementStyles_family.' }';
		// color only
		$CustomStyles .= '.thumbnail .caption, blockquote em, .comment-list .media-body .panel-heading .media-heading a, .nav-tabs > li > a, .nav-justified:not([class*="nav-pills"]):not([class*="nav-tabs"]) > li > a, .panel-default .close, .modal .close, .cover-container .light-bg h1, .cover-container .light-bg .h1, .cover-container .light-bg h2, .cover-container .light-bg .h2, .cover-container .light-bg h3, .cover-container .light-bg .h3, .cover-container .light-bg h4, .cover-container .light-bg .h4, .cover-container .light-bg h5, .cover-container .light-bg .h5, .cover-container .light-bg h6, .cover-container .light-bg .h6, .cover-container .light-bg p,  .cover-container .light-bg .entry-content, .copyright, .timeline .tl-panel .tl-body i, #login #nav a:hover, #login #backtoblog a:hover { '.$elementStyles_color.' }';
	}


	// Fonts (heading)
	//................................................................

	$font = array();
	if (get_options_data('options-page', 'font-heading') == 'google') {
		// get google font data
		$gFont = get_options_data('options-page', 'font-heading-google');

		// for properly work in Customize
		if (is_object($gFont)) {
			$gFont = json_decode(json_encode($gFont), true);
		}

		$gFontWeight = explode(',', $gFont['weight']);
		$font['family'] = $gFont['family'];
		$font['weight'] = (count($gFontWeight)) ? $gFontWeight[0] : 'normal';
		// $font['size']   = $gFont['size'];
		$font['color']  = $gFont['color'];
	} else {
		// get standard font data
		$font['family'] = get_options_data('options-page', 'font-heading-family');
		$font['weight'] = get_options_data('options-page', 'font-heading-weight');
		// $font['size']   = get_options_data('options-page', 'font-heading-size');
		$font['color']  = get_options_data('options-page', 'font-heading-color');
	}

	$elementStyles = '';
	$elementStyles_noColor = '';
	$elementStyles_color = '';
	$elementStyles_family = '';
	if ( count($font) ) {
		foreach ($font as $attribute => $style) {
			if (!empty($style)) {
				$property = ($attribute != 'color') ? 'font-'.$attribute : $attribute;
				$elementStyles .= $property.': '. $style .';';
				// if ($attribute != 'size') {
				// 	$elementStyles_noSize .= $property.': '. $style .';';
				// }
				if ($attribute == 'color') {
					$elementStyles_color .= $property.': '. $style .';';
				} else {
					$elementStyles_noColor .= $property.': '. $style .';';
				}
				if ($attribute == 'family') {
					$elementStyles_family .= $property.': '. $style .';';
				}
			}
		}
	}

	if ( !empty($elementStyles)) {
		// Take away a default italic on some headings... because probably nobody wants that if using custom fonts
		$special_h1 = $elementStyles_family;
		if (!empty($font['family']) && $font['family'] != 'Droid Serif') {
			$special_h1 .= 'font-style: normal;';
		}
		$CustomStyles .= '.intro-wrap h1, .cover .cover-container h1, blockquote.big-quote { '.$special_h1.' }';
		// All styles for Headings
		$CustomStyles .= 'h1, h2, h3, .h1, .h2, .h3, .page-title { '.$elementStyles.' }';
		// family only
		$CustomStyles .= 'h4, .h4, h5, .h5, h6, .h6, .search-result h3, blockquote, .featured-slider article h3 { '.$elementStyles_family.' }';
		// color only
		$CustomStyles .= 'h4, .h4, h5, .h5, h6, .h6, .search-result h3, h1 a, .h1 a, h2 a, .h2 a, h3 a, .h3 a, h4 a, .h4 a, h5 a, .h5 a, h6 a, .h6 a { '. $elementStyles_color .' }';
	}

	// Font (heading sizes)
	//................................................................

	$size_H = array(
		'h1',
		'h2' => '.post .entry-title, .entry .entry-title, .issue .entry-title',
		'h3',
		'h4',
		'h5',
		'h6'
	);
	// Headings sizes
	foreach ($size_H as $h => $tags) {
		$size = trim(get_options_data('options-page', 'font-heading-size-'.$h, 'false'));
		if ($size !== 'false' && !empty($size)) {
			if (!strpos($size,'px') && !strpos($size,'em') && !strpos($size,'rem') ) {
				$size .= 'px';
			}
			$h_tags = $h;
			if (!empty($tags)) {
				$h_tags .= ','. $tags;
			}
			$CustomStyles .= $h_tags .' { font-size: '.$size.' }';
		}
	}


	// Custom CSS (user generated)
	//................................................................

	$userStyles = stripslashes(htmlspecialchars_decode(get_options_data('options-page', 'custom-styles'),ENT_QUOTES));

	// Add styles to CSS variable
	$CustomStyles .= $userStyles;

	// all done!
	return $CustomStyles;

}

endif;

