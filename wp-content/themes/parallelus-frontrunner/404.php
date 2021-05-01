<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 */

$error_page_id = (get_options_data('options-page', 'error-content')) ? get_options_data('options-page', 'error-content') : 'default';
$error_template = (get_options_data('options-page', 'error-template')) ? get_options_data('options-page', 'error-template') : 'default';

if ( !empty($error_template) && $error_template !== 'default') {

		// Based on cover template
		add_filter('theme_template_has_layout', function(){ return true; });

		get_header();

		get_footer();

} else {

	get_header(); ?>

		<section class="error-container error-404 not-found">

		<?php
			// $error_page_id = (get_options_data('options-page', 'error-content')) ? get_options_data('options-page', 'error-content') : 'default';
			$errorContent = get_post($error_page_id);
			if (isset($errorContent) && !empty($errorContent)) {

				// Custom content on default template
				echo apply_filters( 'the_content', $errorContent->post_content );

			} else {
				?>
				<header class="page-header">
					<h2 class="page-title"><?php _e( 'Whaaaaat??!?!!1', 'framework' ); ?></h2>
					<p class="lead"><?php _e( "It seems the page you're looking for isn't here.", 'framework' ); ?></p>
				</header><!-- .page-header -->

				<div class="404-search-box">
					<p><?php _e( 'Try looking somewhere else and you might get lucky!', 'framework' ); ?></p>
					<?php get_search_form(); ?>
					<br>
					<br>
				</div><!-- /.404-search-box -->

				<?php
			} ?>

		</section>

		<?php

	get_footer();
}