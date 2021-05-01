<?php
/**
 * Generic single post.
 * Used for any post type not specified with a custom file "content-single-{post type}.php"
 */

$post_thumbnailSize = 'blog';

$class_mainSection  = 'col-md-12';
$class_sidebarLeft  = '';
$class_sidebarRight = '';

// Check for custom sidebars from meta options
$meta_options = get_post_custom();

// Sidebar Left
$sidebarLeft = false;
if ( isset($meta_options['theme_custom_sidebar_options_left']) ) {
	$has_sidebarLeft = $meta_options['theme_custom_sidebar_options_left'][0];
	$sidebarLeft = ( !empty($has_sidebarLeft) && $has_sidebarLeft !== 'default' ) ? $has_sidebarLeft : false;
}
// Sidebar Right
$sidebarRight = false;
if ( isset($meta_options['theme_custom_sidebar_options_right']) ) {
	$has_sidebarRight = $meta_options['theme_custom_sidebar_options_right'][0];
	$sidebarRight = ( !empty($has_sidebarRight) && $has_sidebarRight !== 'default' ) ? $has_sidebarRight : false;
}

// Classes for sidebars
if ($sidebarLeft) {
	$class_mainSection  = 'col-md-9 col-md-push-3 col-lg-8 col-lg-push-4';
	$class_sidebarLeft  = 'col-md-3 col-md-pull-9 col-lg-pull-8';
	$class_sidebarRight = '';
}
if ($sidebarRight) {
	$class_mainSection  = 'col-md-9 col-lg-8';
	$class_sidebarLeft  = '';
	$class_sidebarRight = 'col-md-3 col-lg-3 col-lg-push-1';
}
if ($sidebarRight && $sidebarLeft) {
	$class_mainSection  = 'col-md-8 col-lg-6 col-lg-push-3';
	$class_sidebarLeft  = 'col-md-4 col-lg-3 col-lg-pull-6';
	$class_sidebarRight = 'col-md-4 col-lg-3';
}
?>

<div class="main-section <?php echo esc_attr($class_mainSection) ?>">

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php if ( rf_show_page_title() ) : ?>
		<header class="page-header">
			<h1 class="page-title"><?php the_title(); ?></h1>
		</header>
		<?php endif; ?>

		<div class="entry-content">

			<?php if ( has_post_thumbnail() ) : ?>
			<p class="entry-thumbnail">
				<?php echo get_the_post_thumbnail( $post->ID, $post_thumbnailSize ); ?>
			</p>
			<?php endif; ?>

			<?php the_content(); ?>

		</div>

		<?php
		// If comments are open or we have at least one comment, load up the comment template
		if ( comments_open() || '0' != get_comments_number() ) {
			comments_template();
		}
		?>

	</article>
</div>

<?php

// Sidebar Left
if ( $sidebarLeft ) { ?>
	<div class="sidebar <?php echo esc_attr($class_sidebarLeft) ?>">
		<?php get_sidebar('left'); ?>
	</div><!-- /.sidebar-left -->
	<?php
}

// Sidebar Right
if ( $sidebarRight ) { ?>
	<div class="sidebar <?php echo esc_attr($class_sidebarRight) ?>">
		<?php get_sidebar('right'); ?>
	</div><!-- /.sidebar-left -->
	<?php
} ?>
