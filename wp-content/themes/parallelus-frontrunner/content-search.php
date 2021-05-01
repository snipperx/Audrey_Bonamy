<?php
/**
 * The content for each Search Result.
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('search-result'); ?>>
	<h3 class="result-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->
</article>
