<?php
/**
 * The template part for the LARGE header
 */
?>

<?php

$header_class = 'header-bg header-large header-nav-top';
$menu = get_options_data('home-page', 'home-header-menu-position');
$menu_class = ( $menu == 'middle' ) ? 'menu-logo-middle' : '';
$header_bg_wrapper_style = ( $menu == 'middle' ) ? 'style="display:none;"' : '';
$home_page = (function_exists('theme_is_custom_home_page')) ? theme_is_custom_home_page() : is_front_page();
$page = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;


// Menu and Logo Position
if ($home_page && get_options_data('home-page', 'home-header-size') == 'large') {

	// Pages 2, 3, etc. of the home page posts
	if ($page > 1) {
		$menu = 'top';
	}

	if ($menu == 'top') {
		// no change
	} else {
		$header_class .= ' header-nav-toggle';
	}
}


// Large Header ?>
<div id="header" <?php theme_header_class( $header_class ); ?> <?php theme_header_styles() ?>>

	<!-- page header -->
	<div class="header-bg-wrapper" <?php echo $header_bg_wrapper_style; ?>>

	<?php
	if ($home_page && $page <= 1 && $menu !== 'top') { ?>
		<div class="header-inner logo-container <?php echo $menu_class; ?>">
			<div class="pull-left">
				<div class="logo">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
						<div class="logo-wrapper">
							<div class="logo-inner-wrapper">
								<?php
								$logo_image = get_options_data('home-page', 'home-header-logo-middle', '');
								$has_logo = false;
								if (!empty($logo_image)) {
									echo '<img src="'.$logo_image.'" alt="'.esc_attr(get_bloginfo('name', 'display')).'">';
									$has_logo = true;
								}
								$brand_title = get_options_data('options-page', 'brand-title', '');
								if (!empty($brand_title)) {
									echo '<div class="brand-title">' . esc_attr($brand_title) . '</div>';
								}
								?>
							</div>
						</div>
					</a>
				</div>
			</div>
		</div>
	<?php } // end middle align logo ?>
	<?php

		// Content
		$content_header_template = apply_filters('theme-content-header-template', 'header');
		get_template_part('templates/parts/content', $content_header_template);

		// Menu
		$menu_template = apply_filters('theme-menu-template', 'main');
		get_template_part('templates/parts/menu', $menu_template);

	?>

	</div>  <!-- end header-bg-wrapper -->
</div>  <!-- / #header -->
