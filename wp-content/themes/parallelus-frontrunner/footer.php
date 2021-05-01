<?php
/**
 * The template for displaying the footer.
 */
?>

<?php do_action('output_layout','end'); // Layout Manager - End Layout ?>

<?php if ( (function_exists('rf_is_cover_template') && !rf_is_cover_template()) || is_page_template( 'templates/cover-with-page.php' ) || is_page_template( 'templates/cover-with-page-and-menu.php' ) ) : ?>

	<?php

	// Footer Overlap Source
	$footer_overlap_source = get_options_data('options-page', 'footer-overlap-active', 'hide');

	// Footer class
	$footer_class = 'wrapper';
	$footer_class .= ($footer_overlap_source !== 'hide') ? ' with-overlap' : '';

	// Footer styles
	$styles = '';
	$container_style['background-color'] = get_options_data('options-page', 'footer-bg-color', '');
	$container_style['background-image'] = get_options_data('options-page', 'footer-bg-image', '');
	foreach ($container_style as $attribute => $style) {
		if ( isset($style) && !empty($style) && $style !== '#') {
			if ($attribute == 'background-image') {
				$style = 'url('. $style .')';
			}
			$styles .= $attribute .':'. $style .';';
		}
	}
	$styles = (!empty($styles)) ? 'style="'.esc_attr($styles).'"' : '';

	?>

	<footer id="footer" class="<?php echo esc_attr($footer_class) ?>" <?php echo  $styles; // escaped above ?>>

	<?php

	// Footer Overlap
	if ($footer_overlap_source !== 'hide') {

		// Footer overlap styles
		$styles = '';
		$container_style['background-color'] = get_options_data('options-page', 'footer-overlap-bg-color', '');
		$container_style['background-image'] = get_options_data('options-page', 'footer-overlap-bg-image', '');
		foreach ($container_style as $attribute => $style) {
			if ( isset($style) && !empty($style) && $style !== '#') {
				if ($attribute == 'background-image') {
					$style = 'url('. $style .')';
				}
				$styles .= $attribute .':'. $style .';';
			}
		}
		$styles = (!empty($styles)) ? 'style="'.esc_attr($styles).'"' : '';

		?>
		<div class="container-box">
			<div class="container">
				<div class="container-box-wrapper accent-box" <?php echo  $styles; // escaped above ?>>
				<?php
				// Content Source
				if ($footer_overlap_source == 'ninja-form') {

					// Ninja Form
					$form_id = get_options_data('options-page', 'footer-overlap-ninja-form', '');
					if (!empty($form_id) && $form_id !== 'none') :
						?>
						<div class="row">
							<div class="col-sm-12">
								<div class="form-wrapper">
									<div class="form-inline">
										<?php
										if ( function_exists( 'Ninja_Forms' ) ) {
											$ninja_forms = Ninja_Forms();

											if ( method_exists( $ninja_forms, 'display' ) ) {
												Ninja_Forms()->display( $form_id );
											} else if ( function_exists( 'ninja_forms_display_form' ) ) {
												ninja_forms_display_form( $form_id );
											}
										}
										?>
									</div>
								</div>
							</div>
						</div>
						<?php
					endif;

				} elseif ($footer_overlap_source == 'content-block') {

					// Content Block
					$content_id = get_options_data('options-page', 'footer-overlap-content-block', '');
					if (!empty($content_id) && $content_id !== 'none') :
						?>
						<div class="inner-padder">
							<div class="row">
								<div class="col-sm-12">
									<?php the_static_block($content_id); ?>
								</div>
							</div>
						</div>
						<?php

					endif;

				}
				?>
				</div>
			</div>
		</div>

		<?php
	} // End footer overlap ?>

		<div class="container">
			<div class="row">

				<div class="col-md-12">

					<?php

					// Social Icons
					$social_icons_active = get_options_data('options-page', 'footer-social-icons-active', 'hide');

					if ($social_icons_active !== 'hide') {

						$social_icons = '';
						for ($i = 1; $i <= 8; $i++) {
							$icon = get_options_data('options-page', 'footer-social-icon-'.$i, 'none');
							$link = get_options_data('options-page', 'footer-social-icon-'.$i.'-url', '');
							if ( !empty($icon) && $icon !== 'none' ) {
								$this_icon = '<i class="fa fa-'. esc_attr($icon) .'"></i>';
								$this_icon = (!empty($link)) ? '<a href="'. esc_url($link) .'" target="_blank">'. $this_icon . '</a>' : $this_icon;
								$this_icon = '<li>'. $this_icon . '</li>';
								$social_icons .= $this_icon;
							}
						}

						// Output the social icons
						if (!empty($social_icons)) { ?>
							<ul class="footer-social icon-blocks">
								<?php echo  $social_icons; // escaped above ?>
							</ul>
							<?php
						}

					}
						?>

					<?php

					// Footer Logo
					$logo_image = get_options_data('options-page', 'footer-logo', '');
					if (!empty($logo_image)) {
						$logo_height = (int) get_options_data('options-page', 'footer-logo-height', '');
						$height = (!empty($logo_height) && $logo_height > 0) ? ' style=" height:'.$logo_height.'px"' : '';
						echo '<p class="footer-logo"><img src="'.$logo_image.'" '.$height.' alt="'.esc_attr(get_bloginfo('name', 'display')).'"></p>';
					}

					// Footer Menu
					wp_nav_menu( array(
						'menu'              => 'menu-footer',
						'theme_location'    => 'menu-footer',
						'container'         => 'div',
						'container_class'   => 'footer-nav',
						'depth'             => -1,
						'menu_class'        => 'footer-nav-menu',
						'menu_id'           => 'footer-menu',
						'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
						'walker'            => new wp_bootstrap_navwalker()
					));

					// Main footer content
					$footer_content = get_options_data('options-page', 'footer-content-block', '');
					if (!empty($footer_content) && $footer_content !== 'none') {
						the_static_block($footer_content);
					}

					?>

				</div>

			</div>

		</div>
	</footer>

<?php endif; // !is_page_template( 'templates/cover.php' ) ?>

<?php wp_footer(); ?>

</body>
</html>