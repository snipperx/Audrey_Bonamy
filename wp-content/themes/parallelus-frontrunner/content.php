<?php
/**
 * Generic content for loops
 */

$post_thumbnailSize = 'blog';
$full_post = false;

if (get_options_data('options-page', 'issue-list-style') == 'full-content') {
	$full_post = true;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
	<header>
		<h2 class="entry-title">
			<?php
			if ($full_post) {
				the_title();
			} else {
				?>
				<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
				<?php
			} ?>
		</h2>
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry-thumbnail">
			<?php if (!$full_post) : ?><a href="<?php the_permalink(); ?>" rel="bookmark"><?php endif; ?>
				<?php echo get_the_post_thumbnail( $post->ID, $post_thumbnailSize ); ?>
			<?php if (!$full_post) : ?></a><?php endif; ?>
		</div>
		<?php endif; ?>
	</header>

	<?php
	if ($full_post) {
		the_content();
	} else {
		the_excerpt();
	} ?>

	<?php if (!$full_post) : ?>
		<a href="<?php the_permalink(); ?>" rel="bookmark" class="more-link"><?php _e('Continue reading', 'framework'); ?></a>
	<?php endif; ?>

	<hr class="sep" />
</article> <!-- #post-<?php the_ID(); ?> -->
