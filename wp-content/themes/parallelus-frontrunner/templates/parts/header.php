<?php
/**
 * The template part for the default header content
 */
?>

<?php

if (rf_has_custom_header()) {
	$header_class = 'header-bg header-nav-bottom';
} else {
	$header_class = 'header-nav-top';
}

?>

<div id="header" <?php theme_header_class( $header_class );?> <?php theme_header_styles() ?>>

	<?php
	// Header content
	if (rf_has_custom_header()) {
		?>
		<div class="header-bg-wrapper" style="display:none;">

			<?php

			// Content
			$content_header_template = apply_filters('theme-content-header-template', 'header');
			get_template_part('templates/parts/content', $content_header_template);

			?>

		</div>  <!-- end header-bg-wrapper -->
		<?php
	}

	// Main Menu
	$menu_template = apply_filters('theme-menu-template', 'main');
	get_template_part('templates/parts/menu', $menu_template);

	?>

</div>  <!-- / #header -->
