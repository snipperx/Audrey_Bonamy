<?php
/**
 * The home page file.
 */

if (get_option('show_on_front') == 'posts' || get_option('page_on_front') == '0') {

	global $paged;

	if (isset($paged) && $paged > 1) {

		// Posts page 2, 3, etc... does not use home page layout
		// -----------------------------------------------------

		get_template_part('index');

	} else {

		// Theme Home Page Layout
		// -------------------------------------------------

		add_filter('theme_template_has_layout', function(){ return true; }); // the template has layout containers

		// Load the home page template
		get_template_part('templates/parts/home', 'layout-1');

	} // end if $paged

} else {

	// User selected a page, so show it instead.
	include( get_page_template() );
}
