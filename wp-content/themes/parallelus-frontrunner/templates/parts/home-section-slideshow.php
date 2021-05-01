<?php
/**
 * Template part: Home Page Section - Slideshow
 */
// The content source set in theme options
$content_format       = get_options_data('home-page', 'home-section-5-active');
$content_category     = get_options_data('home-page', 'home-section-5-destination-category');
$content_slideshow    = get_options_data('home-page', 'home-section-5-simple-slideshow-source');
$content_rev_slider   = get_options_data('home-page', 'home-section-5-rev-slider-source');
$content_layer_slider = get_options_data('home-page', 'home-section-5-layer-slider-source');
$content = array();
$has_content =  false;

// Content check
switch ($content_format) {

	case 'simple':

		$id = $content_slideshow;
		if (function_exists('sts_get_slider') && !empty($id)) {

			$simple_slider = sts_get_slider( $id );
			if ( is_array($simple_slider) && !empty($simple_slider) ) {

				// content exists and which output method to use
				$has_content = 'default';

				foreach ($simple_slider as $index => $slide) {

					// Title
					$content[$index]['title'] = (isset($slide['title'])) ? $slide['title'] : '';

					// Description
					$content[$index]['description'] = (isset($slide['description'])) ? $slide['description'] : '';

					// Image
					$content[$index]['image'] = (isset($slide['source'])) ? $slide['source'] : '';
					if ( !empty($content[$index]['image']) ) :
						// the image with CSS
						$content[$index]['image'] = 'background-image: url('.$content[$index]['image'].')';
					endif;

					// Link
					$content[$index]['link']   = (isset($slide['slide-link'])) ? $slide['slide-link'] : '';
					$content[$index]['target'] = (isset($slide['open-new-window']) && $slide['open-new-window'] == 'checked') ? '_blank' : '';
				}
			}
		}
		break;

	case 'rev-slider':
		// content exists and which output method to use
		$has_content = 'rev-slider';
		$alias = $content_rev_slider;
		break;

	case 'layer-slider':
		// content exists and which output method to use
		// $has_content = 'custom';
		$id = $content_layer_slider;
		break;

	default:
		# ???
		break;
}

// Slideshow
// -------------------------------------------------

// We have some content!
if ( $has_content ) :

	// Use the default HTML structure of the basic slide show
	// -------------------------------------------------------
	if ($has_content == 'default') {
		?>
		<div id="section-slider" class="featured-slider">
			<div class="featured-carousel">


			<?php
			foreach ($content as $key => $value) {

				// check for a link target (like a new window)
				$target = '';
				if (isset($value['target']) && !empty($value['target'])) {
					$target = 'target="'. esc_attr($value['target']) .'"';
				}
				?>

				<div class="item">
					<div class="bg-img" style="<?php echo esc_attr($value['image']) ?>"></div>
					<div class="color-hue"></div>
					<div class="container">
						<div class="row">
							<div class="col-sm-8">
								<article>
									<h3>
										<?php echo wp_kses_post(html_entity_decode($value['title'], ENT_COMPAT)) ?>
										<span class="sub-title"><?php echo wp_kses_post(html_entity_decode($value['description'], ENT_COMPAT)) ?></span>
										<?php if (!empty($value['link'])) : ?>
											<a href="<?php echo esc_url($value['link']) ?>" <?php echo  $target; // escaped above ?> class="btn btn-primary"><?php _e('More', 'framework') ?></a>
										<?php endif; ?>
									</h3>
								</article>
							</div>
						</div>
					</div>
				</div>
				<?php
			} ?>
			</div>
		</div>
		<?php

	// Use the custom structure of another slide show
	// -------------------------------------------------------
	} elseif ($has_content == 'rev-slider') {
		// Revolution Slider
		putRevSlider( $alias );

	} elseif ($has_content == 'layer-slider') {
		// Layer Slider (coming soon)

	}

endif;
