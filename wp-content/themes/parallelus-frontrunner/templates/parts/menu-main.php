<?php
/**
 * The template part for the MAIN MENU
 */
?>

<?php

// Page #
$page = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;

// Classes
$wrapper_class = 'navbar-wrapper';
$navbar_class  = 'navbar navbar-default';

$home_page = (function_exists('theme_is_custom_home_page')) ? theme_is_custom_home_page() : is_front_page();

if ($home_page && get_options_data('home-page', 'home-header-size') == 'large') {
	// Home Page - Menu and Logo Position
	$menu = get_options_data('home-page', 'home-header-menu-position');
	$next_page = ($page > 1) ? true : false; // default top menu on page 2, 3, etc.
	if ($menu == 'top' || $next_page) {
		$navbar_class .= ' navbar-fixed-top';
	} else {
		$wrapper_class .= ' do-transition';
		$navbar_class  .= ' navbar-vertical';
	}
} elseif (rf_is_cover_template()) {
	// Cover template with menus
	$navbar_class .= ' navbar-fixed-top';
} elseif (rf_has_custom_header()) {
	// Page with header content or background image
	$navbar_class .= ' navbar-static-top navbar-sticky';
} else {
	// Page with menu only
	$navbar_class .= ' navbar-fixed-top';
}

?>

<div class="header-inner menu-container">
	<div class="<?php echo  $wrapper_class; ?>">
		<nav class="<?php echo  $navbar_class; ?>" id="nav-main">
			<div class="container-fluid">

				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-main" aria-expanded="false">
						<span class="sr-only"><?php _e('Toggle navigation', 'framework'); ?></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home" class="navbar-brand">
						<?php
						$logo_image = get_options_data('options-page', 'logo', '');
						$has_logo = false;
						if (!empty($logo_image)) {
							echo '<img src="'.$logo_image.'" alt="'.esc_attr(get_bloginfo('name', 'display')).'">';
							$has_logo = true;
						}
						$brand_title = get_options_data('options-page', 'brand-title', '');
						if (!empty($brand_title)) {
							if ($has_logo) {
								echo ' &nbsp;';
							}
							echo esc_attr($brand_title);
						}
						?>
					</a>

				</div>

				<div class="collapse navbar-collapse" id="navbar-main">
				<?php
					// Left Menu
					if (class_exists('wp_bootstrap_navwalker')) {
						// Main navbar (left)
						wp_nav_menu( array(
							'menu'              => 'primary',
							'theme_location'    => 'primary',
							'container'         => false,
							'menu_class'        => 'nav navbar-nav',
							'menu_id'           => 'nav-left',
							'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
							'walker'            => new wp_bootstrap_navwalker()
						));
					} else {
						_e('Please make sure the Bootstrap Navigation extension is active. Go to "Runway > Extensions" to activate.', 'framework');
					}

					// Right Menu
					if (class_exists('wp_bootstrap_navwalker')) {
						// Main navbar (right)
						wp_nav_menu( array(
							'menu'              => 'menu-right',
							'theme_location'    => 'menu-right',
							'container'         => false,
							'menu_class'        => 'nav navbar-nav',
							'menu_id'           => 'nav-right',
							'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
							'walker'            => new wp_bootstrap_navwalker()
						));
					} else {
						_e('Please make sure the Bootstrap Navigation extension is active. Go to "Runway > Extensions" to activate.', 'framework');
					}
				?>
				</div>
			</div>
		</nav>  <!-- end default nav -->
	</div>  <!-- end navbar-wrapper -->
</div>  <!-- end header-inner -->
