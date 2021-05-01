<?php
/**
 * The template part for the default home page (layout 1)
 */

// Check for active Action Links
$actionLinks = array();
for ($i = 1; $i <= 4; $i++) {
	$actionLinks[$i] = get_options_data('home-page', 'home-action-link-'.$i, 'show');
}
if (in_array('show', $actionLinks)) {
	add_filter('theme_header_class', function( $class ){ return $class .' offset-bottom'; });
}

// Check for active home page sections
$topContentSection  = get_options_data('home-page', 'home-section-2-active', 'show');
$videosSection      = get_options_data('home-page', 'home-section-3-active', 'show');
$mainContentSection = get_options_data('home-page', 'home-section-4-active', 'show');
$slideshowSection   = get_options_data('home-page', 'home-section-5-active', 'show');
$eventsSection      = get_options_data('home-page', 'home-section-6-active', 'show');

// Home page CSS and JS
if ($slideshowSection != 'hide') {
	wp_enqueue_style( 'owl-carousel', rf_get_template_directory_uri() . '/assets/css/owl-carousel.css', '2.0.0-beta.2.4' ); // carousel base CSS
	wp_enqueue_script( 'owl-carousel', rf_get_template_directory_uri().'/assets/js/owl.carousel.min.js', array('jquery'), '2.0.0-beta.2.4', true );
}
if ($videosSection != 'hide') {
	wp_enqueue_script( 'youtube-iframe_api', 'https://www.youtube.com/iframe_api', array('jquery', 'theme-js'), '1.0', true );
}

// Regular home page with theme styling
add_filter('theme_template_has_layout', function(){ return true; }); // the template has layout containers


// Assemble the template parts for the default home page

get_header();

// Action Links Section
if (in_array('show', $actionLinks)) {
	get_template_part('templates/parts/home-section', 'action-links');
}
// Top Content Section
if ($topContentSection != 'hide') {
	get_template_part('templates/parts/home-section', 'top-content');
}
// Videos Section
if ($videosSection != 'hide') {
	get_template_part('templates/parts/home-section', 'videos');
}
// Main Content Posts / Static Block Section
if ($mainContentSection != 'hide') {
	get_template_part('templates/parts/home-section', 'main-content');
}
// Slideshow
if ($slideshowSection != 'hide') {
	get_template_part('templates/parts/home-section', 'slideshow');
}
// Events
if ($eventsSection != 'hide') {
	get_template_part('templates/parts/home-section', 'events');
}

get_footer();
