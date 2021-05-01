<?php
/**
 * The template for displaying Search Results pages.
 */

$has_sidebar = is_sidebar_active('sidebar-search');
if ($has_sidebar) {
	$content_class = 'col-md-8';
} else {
	$content_class = 'col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2';
}

get_header(); ?>

	<div class="row">
		<div class="<?php echo esc_attr($content_class) ?>">

			<div class="row">
				<div class="col-sm-12 home-search-field">
					<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ) ?>">
						<div class="input-group">
							<!-- <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span> -->
							<input type="text" name="s" class="form-control" placeholder="<?php echo esc_attr_x( 'Search...', 'placeholder', 'framework') ?>" value="<?php echo esc_attr( get_search_query() ) ?>">
							<span class="input-group-btn">
								<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
							</span>
						</div>
					</form>
				</div>
			</div>
			<p>&nbsp;</p>

		<?php if ( have_posts() ) : ?>

			<?php if ( function_exists('rf_has_custom_header') && !rf_has_custom_header() ) : ?>
				<header class="page-header">
					<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'framework' ), '<span><em>' . get_search_query() . '</em></span>' ); ?></h1>
				</header>
			<?php endif; ?>

			<?php

			while ( have_posts() ) : the_post();

				// Output the content
				get_template_part( 'content', 'search' );

			endwhile;

			// Paging function
			if (function_exists( 'rf_get_pagination' )) :
				rf_get_pagination();
			endif;

		else :

			get_template_part( 'no-results', 'search' );

		endif; // end of loop. ?>

		</div>

		<?php if ($has_sidebar) : ?>
		<div class="col-md-4 col-lg-3 col-lg-offset-1">

			<?php get_sidebar('sidebar-search'); ?>

		</div>
		<?php endif; ?>

	</div><!-- /.row -->

<?php get_footer(); ?>