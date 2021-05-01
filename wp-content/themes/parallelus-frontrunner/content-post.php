<?php
/**
 * Default post content used in loops
 */

$post_thumbnailSize = 'blog';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
	<header>
		<div class="header-meta">
			<span class="posted-on"><?php echo esc_html(get_the_date()); ?></span>
		</div>
		<h2 class="entry-title">
			<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
		</h2>
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry-thumbnail">
			<a href="<?php the_permalink(); ?>" rel="bookmark"><?php echo get_the_post_thumbnail( $post->ID, $post_thumbnailSize ); ?></a>
		</div>
		<?php endif; ?>
	</header>

	<?php the_excerpt(); ?>

	<a href="<?php the_permalink(); ?>" rel="bookmark" class="more-link"><?php _e('Continue reading', 'framework'); ?></a>

	<hr class="sep" />
</article> <!-- #post-<?php the_ID(); ?> -->
