<?php
/**
 * The main template file. (loads blog posts)
 */

get_header(); ?>

	<div class="row">
		<div class="main-section">
			<div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">

			<?php

			if ( have_posts() ) :

				// Start the Loop
				while ( have_posts() ) : the_post();

					// Find the correct template file
					$template = 'content-'.get_post_type();

					if (locate_template($template .'.php') != '') {
						// Load the template 'content-{post type}-{post format}.php'
						get_template_part($template, get_post_format());
					} else {
						// Fallback to the default 'content-{post format}.php'
						get_template_part( 'content', get_post_format() );
					}

				endwhile;

				// Paging function
				if (function_exists( 'rf_get_pagination' )) :
					rf_get_pagination();
				endif;

			else :

				get_template_part( 'no-results', 'index' );

			endif; // end of loop. ?>

			</div>
		</div>
	</div>

<?php get_footer(); ?>