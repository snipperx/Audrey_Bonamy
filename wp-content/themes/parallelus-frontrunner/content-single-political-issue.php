<?php
/**
 * Political Issue Page
 */

$post_thumbnailSize = 'blog';

// Custom options
$meta_options = get_post_custom();

// Featured Image
$showFeaturedImage = false;

// Featured Image Location
if ( has_post_thumbnail() ) {
	$bg_setting = '';
	$header_size = '';
	if ( isset($meta_options['theme_custom_layout_metabox_options_header_bg']) ) {
		$bg_setting = $meta_options['theme_custom_layout_metabox_options_header_bg'][0];
		$header_size = (isset($meta_options['theme_custom_layout_metabox_options_header_style'][0])) ? $meta_options['theme_custom_layout_metabox_options_header_style'][0] : '';
	}
	$showFeaturedImage = ( $bg_setting !== 'featured-image' || $header_size == 'none' ) ? true : false;
}

// Sidebars
$class_mainSection  = 'col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2';
$class_sidebarLeft  = '';
$class_sidebarRight = '';

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

			<?php if ( $showFeaturedImage ) : ?>
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
