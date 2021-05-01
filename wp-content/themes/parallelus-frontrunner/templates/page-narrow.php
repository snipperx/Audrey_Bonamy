<?php
/**
 * Template Name: Narrow (no sidebars)
 *
 * Displays a narrow content column
 *
 */

get_header(); ?>

<div class="row">

	<?php while ( have_posts() ) : the_post(); ?>

		<div class="main-section col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">

			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if ( rf_show_page_title() ) : ?>
				<header class="page-header">
					<h1 class="page-title"><?php the_title(); ?></h1>
				</header>
				<?php endif; ?>

				<div class="entry-content">
					<?php the_content(); ?>
					<?php
						wp_link_pages( array(
							'before' => '<div class="page-links">' . __( 'Pages:', 'framework' ),
							'after'  => '</div>',
						) );
					?>
				</div><!-- .entry-content -->
				<?php // edit_post_link( __( 'Edit', 'framework' ), '<footer class="entry-meta"><span class="edit-link">', '</span></footer>' ); ?>

			</article><!-- #page-<?php the_ID(); ?> -->
		</div>


		<?php
			// If comments are open or we have at least one comment, load up the comment template
			if ( comments_open() || '0' != get_comments_number() )
				comments_template();
		?>

	<?php endwhile; // end of the loop. ?>

</div>

<?php get_footer(); ?>
