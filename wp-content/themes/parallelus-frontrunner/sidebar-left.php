<?php
/**
 * The left sidebar tempalte.
 */

$sidebar_meta_suffix = 'left';
$theme_sidebar = 'default';
$id = get_queried_object_ID();

// Pages like search return a '0' so we can't do lookups for this
if ($id) {
	// Check for custom sidebar from meta box options
	$meta_options = get_post_custom(get_the_ID());
	if ( isset($meta_options['theme_custom_sidebar_options_'.$sidebar_meta_suffix]) ) {
		$theme_sidebar = $meta_options['theme_custom_sidebar_options_'.$sidebar_meta_suffix][0];
	}
}

?>

<div class="sidebar-container">
	<?php

	do_action( 'before_sidebar', $sidebar_meta_suffix );

	// Determine the sidebar to use
	if ( isset($theme_sidebar) && $theme_sidebar !== 'default' ) {

		// Custom sidebar specified in meta options
		if ( ! dynamic_sidebar( $theme_sidebar ) ) : endif;

	} else {

		// Select a default sidebar based on the $post_type
		if (is_search()) {
			// If we're searching
			$post_type_sidebar = 'search';
		} else {
			// Look up the post type by ID
			$post_type_sidebar = ($id) ? get_post_type($id) : 'default';
		}

		// Get the Sidebar
		switch( $post_type_sidebar ) {
			case "something-custom":
				if ( is_active_sidebar( 'sidebar-something-custom' ) ) {
					if ( ! dynamic_sidebar( 'sidebar-something-custom' ) ) : endif;
				}
				break;
			default:
				// First, check for custom "sidebar-{$post_type}"
				if ( is_active_sidebar( 'sidebar-'.$post_type_sidebar ) ) {
					if ( ! dynamic_sidebar( 'sidebar-'.$post_type_sidebar ) ) :  // covers our default 'page' and 'post' sidebars
						// Get the default sidebar
						if ( is_active_sidebar( 'sidebar-main' ) ) {
							if ( ! dynamic_sidebar( 'sidebar-main' ) ) : endif;
						}
					endif;
				}
		}
	}

	do_action( 'after_sidebar', $sidebar_meta_suffix );

	?>
</div><!-- close .sidebar-padder -->