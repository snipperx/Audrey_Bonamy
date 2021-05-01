<?php
/**
 * Template part: Home Page Section - Top Content Section
 */

// The content source set in theme options
$content_1    = get_options_data('home-page', 'home-section-2-content-1');
$content_2    = get_options_data('home-page', 'home-section-2-content-2');
$column_size  = get_options_data('home-page', 'home-section-2-column-size');
// $search_field = get_options_data('home-page', 'home-section-2-search');

// Section styles
$styles = '';
$container_style['background-color'] = get_options_data('home-page', 'home-section-2-bg-color');
$container_style['background-image'] = get_options_data('home-page', 'home-section-2-bg-image');
foreach ($container_style as $attribute => $style) {
	if ( isset($style) && !empty($style) && $style !== '#') {
		if ($attribute == 'background-image') {
			$style = 'url('. $style .')';
		}
		$styles .= $attribute .':'. $style .';';
	}
}
$styles = (!empty($styles)) ? 'style="'.esc_attr($styles).'"' : '';

// Column widths
$column_size = (!empty($column_size)) ? explode(':', $column_size) : array('4','8');
$class_left = 'col-md-'.$column_size[0];
$class_right = 'col-md-'.$column_size[1];
if ($column_size[0] == '10') {
	$class_left = 'col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2';
}
if ($column_size[1] == '10') {
	$class_right = 'col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2';
}

// Content check
$content_left  = (!empty($content_1) && $content_1 !== 'disabled') ? $content_1 : '';
$content_right = (!empty($content_2) && $content_2 !== 'disabled') ? $content_2 : '';
$has_content   = (!empty($content_left) || !empty($content_right)) ? true : false;

$wrapper_class = 'wrapper';

// Has action links above?
$actionLinks = array();
for ($i = 1; $i <= 4; $i++) {
	$actionLinks[$i] = get_options_data('home-page', 'home-action-link-'.$i, 'show');
}
if (in_array('show', $actionLinks)) {
	$wrapper_class .= ' header-links-overlay';
}

// Content Sections
// -------------------------------------------------

// Show content
if ( $has_content ) {

	?>
	<div id="section-top-content" class="<?php echo esc_attr($wrapper_class) ?>"  <?php echo  $styles; // escaped above ?>>
		<div class="container">
			<div class="row">
			<?php

			// Content Left
			if (!empty($content_left)) :
				?>
				<div class="<?php echo esc_attr($class_left); ?>">
					<?php the_static_block($content_left); ?>
				</div>
				<?php
			endif;

			// Content Right
			if (!empty($content_right)) :
				?>
				<div class="<?php echo esc_attr($class_right); ?>">
					<?php the_static_block($content_right); ?>
				</div>
				<?php
			endif;

			?>
			</div> <!-- end row -->
		</div> <!-- end container -->
	</div> <!-- end section-blockquote -->
	<?php
}
?>