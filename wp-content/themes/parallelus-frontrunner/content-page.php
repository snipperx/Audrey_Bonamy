<?php
/**
 * The template used for displaying page content in page.php
 */

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
