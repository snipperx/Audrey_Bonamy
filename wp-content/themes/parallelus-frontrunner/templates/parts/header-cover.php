<?php
/**
 * The template part for the cover (full screen) background hero image in the header
 */

$has_menu = false;

// Check if this is an error template
$error_template = get_options_data('options-page', 'error-template');
if ( is_404() && !empty($error_template) && $error_template === 'cover') {
	$error_page_id = get_options_data('options-page', 'error-content');
	$has_menu = true;
}

// Header classes
$header_class = '';
if (is_page_template( 'templates/cover-with-page.php' )) {
	$header_class .= 'cover-with-page';
}

// Container classes (for extended use)
$container_class = '';
if (isset($has_overlay) && $has_overlay) {
	$container_class .= 'overlay';
}
$container_class = apply_filters('theme_cover_container_class', $container_class);

// Show a menu?
if (is_page_template( 'templates/cover-with-menu.php' ) || is_page_template( 'templates/cover-with-page-and-menu.php' ) || $has_menu === true) {
	$has_menu = true;
	$header_class .= ' header-nav-top';
}

?>

<!-- Cover element -->
<section id="header" <?php theme_header_class( $header_class ); ?>>
	<?php

	// Main Menu
	if ($has_menu) {
		$menu_template = apply_filters('theme-menu-template', 'main');
		get_template_part('templates/parts/menu', $menu_template);
	}

	// Background (featured image)
	if ( has_post_thumbnail() ) {
		$bg_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
	} elseif ( is_404() && has_post_thumbnail( $error_page_id ) ) {
		// check for featured image by error content page ID
		$bg_image = wp_get_attachment_url( get_post_thumbnail_id($error_page_id) );
	} ?>

	<div class="cover-wrapper" style="background-image: url(<?php echo esc_url($bg_image) ?>)">
		<div class="cover-container <?php echo esc_attr($container_class) ?>">
			<div class="cover-inner">

				<?php if (is_page_template( 'templates/cover-content-left.php' )) : ?>
					<div class="cover-left-content">
						<div class="container">
							<div class="row">
								<div class="col-sm-9 col-sm-offset-3 col-md-7 col-md-offset-5 col-lg-6 col-lg-offset-6">
								<?php while ( have_posts() ) : the_post(); ?>

									<?php if ( function_exists('rf_show_page_title') && rf_show_page_title('meta-value') !== 'hide' ) : ?>
									<header class="page-header">
										<h1 class="page-title"><?php the_title(); ?></h1>
									</header>
									<?php endif; ?>

									<div class="entry-content">
										<?php the_content(); ?>
									</div>

								<?php endwhile; ?>
								</div>
							</div>
						</div>
					</div>
				<?php elseif (is_page_template( 'templates/cover-content-right.php' )) : ?>
					<div class="cover-right-content">
						<div class="container">
							<div class="row">
								<div class="col-sm-9 col-md-8 col-lg-7">
								<?php while ( have_posts() ) : the_post(); ?>

									<?php if ( function_exists('rf_show_page_title') && rf_show_page_title('meta-value') !== 'hide' ) : ?>
									<header class="page-header">
										<h1 class="page-title"><?php the_title(); ?></h1>
									</header>
									<?php endif; ?>

									<div class="entry-content">
										<?php the_content(); ?>
									</div>

								<?php endwhile; ?>
								</div>
							</div>
						</div>
					</div>
				<?php elseif (is_page_template( 'templates/cover-with-page.php' ) || is_page_template( 'templates/cover-with-page-and-menu.php' )) : ?>
					<div class="container">
						<?php while ( have_posts() ) : the_post(); ?>
							<?php if ( function_exists('rf_show_page_title') && rf_show_page_title('meta-value') == 'in-header' ) : ?>
								<header class="page-header">
									<h1 class="page-title"><?php the_title(); ?></h1>
								</header>
							<?php endif; ?>

							<?php if ( !empty($post->post_excerpt) ) : ?>
							<div class="entry-content">
								<p><?php echo wp_kses_post($post->post_excerpt); ?></p>
							</div>
							<?php endif; ?>

						<?php endwhile; ?>
					</div>
				<?php elseif (is_404()) : ?>
					<div id="error">
						<div class="container">
							<div class="entry-content">
							<?php
								$errorContent = get_post($error_page_id);
								if (isset($errorContent) && !empty($errorContent)) {
									// Custom content on default template
									echo apply_filters( 'the_content', $errorContent->post_content );
								} ?>
							</div>
						</div>
					</div>
				<?php else : ?>
					<div class="container">
						<?php while ( have_posts() ) : the_post(); ?>
							<?php
							if ( function_exists('rf_show_page_title') && rf_show_page_title('meta-value') !== 'hide' ) :
								// Default cover Title ?>
								<header class="page-header">
									<h1 class="page-title"><?php the_title(); ?></h1>
								</header>
								<?php
							endif; ?>

							<div class="entry-content">
								<?php the_content(); ?>
							</div>

						<?php endwhile; ?>
					</div>
				<?php endif; ?>

			</div><!-- /.cover-inner -->
		</div><!-- /.cover-container -->
	</div><!-- /.cover-wrapper -->

</section><!-- /#header -->