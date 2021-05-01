<?php
/**
 * Template Name: Cover with Page and Menu
 *
 * The template for displaying full background cover at the
 * top, main menu and content below.
 *
 * Cover templates must be registered with the filter 'rf_is_cover_template'
 * See the example in functions.php or search the template files.
 */

get_header(); ?>

	<div class="row">

		<div class="col-sm-12">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'page' ); ?>

				<?php
					// If comments are open or we have at least one comment, load up the comment template
					if ( comments_open() || '0' != get_comments_number() )
						comments_template();
				?>

			<?php endwhile; // end of the loop. ?>

		</div>

	</div><!-- /.row -->

<?php get_footer(); ?>