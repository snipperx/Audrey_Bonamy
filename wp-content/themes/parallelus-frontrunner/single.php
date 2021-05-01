<?php
/**
 * The template for displaying all single posts.
 *
 */

get_header(); ?>

<div class="row">

	<?php
	while ( have_posts() ) : the_post();

		// Find the correct template file
		$template = 'content-single-'.get_post_type();

		if (locate_template($template .'.php') != '') {
			// Load the template 'content-single-{post type}-{post format}.php'
			get_template_part($template, get_post_format());
		} else {
			// Fallback to the default 'content-{post format}.php'
			get_template_part( 'content-single', get_post_format() );
		}

	endwhile; // end of the loop. ?>

</div>

<?php get_footer(); ?>