<?php
/**
 * Template part: Home Page Section - Events
 */

// The Categoreis set in theme options
$category = (array) get_options_data('home-page', 'home-section-6-categories');
$categories = (is_array($category) && !empty($category[0])) ? $category : '';

// Section title
$section_title = get_options_data('home-page', 'home-section-6-title');
$section_more  = get_options_data('home-page', 'home-section-6-more-text');

// // Items to show
$item_count = get_options_data('home-page', 'home-section-6-event-count');
$item_count = ( $item_count == 'auto' ) ? -1 : $item_count; // default

// section styles
$styles = '';
$container_style['background-color'] = get_options_data('home-page', 'home-section-6-bg-color');
$container_style['background-image'] = get_options_data('home-page', 'home-section-6-bg-image');
foreach ($container_style as $attribute => $style) {
	if ( isset($style) && !empty($style) && $style !== '#') {
		if ($attribute == 'background-image') {
			$style = 'url('. $style .')';
		}
		$styles .= $attribute .':'. $style .';';
	}
}
$styles = (!empty($styles)) ? 'style="'.esc_attr($styles).'"' : '';

$wrapper_class = 'wrapper';

// Has action links above?
$actionLinks = array();
for ($i = 1; $i <= 4; $i++) {
	$actionLinks[$i] = get_options_data('home-page', 'home-action-link-'.$i, 'show');
}
if (
	in_array('show', $actionLinks) &&
	get_options_data('home-page', 'home-section-2-active', 'show') == 'hide' &&
	get_options_data('home-page', 'home-section-3-active', 'show') == 'hide' &&
	get_options_data('home-page', 'home-section-4-active', 'show') == 'hide' &&
	get_options_data('home-page', 'home-section-5-active', 'show') == 'hide'
   ) {

		$wrapper_class .= ' header-links-overlay';
}

// Content Sections
// -------------------------------------------------

// The Query
$args = array(
	'post_type' => 'political-event',
	'posts_per_page' => (!empty($item_count)) ? $item_count : 3, // could set a max here if needed
	'order'		=> 'ASC',
	'orderby'	=> 'meta_value',
	'meta_key' 	=> 'political-event-end',
	'meta_query' => array(
		array(
			'key' => 'political-event-end',
			'value' => strtotime("today", time()) + (get_option('gmt_offset') * 3600), // current day, offset with WP timezone setting
			'type' => 'NUMERIC',
			'compare' => '>='
		)
	)
);
if (!empty($categories) && $categories[0] !== 'no' && $categories[0] !== 'all') {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'political-category',
			'field'    => 'term_id',
			'terms'    => $categories,
		)
	);
}
$the_query = new WP_Query( $args );

// The Loop
if ( $the_query->have_posts() ) {

	?>

	<div id="section-events" class="<?php echo esc_attr($wrapper_class) ?>" <?php echo  $styles; // escaped above ?>>

		<div class="container">

			<div class="row">
				<div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
					<h2 class="heading"><?php echo esc_attr($section_title) ?></h2>
				</div>  <!-- end column -->
			</div>  <!-- end row -->

			<div class="row">
				<div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">

					<ul class="timeline">

						<?php

						global $event_post_increment, $event_post_month, $event_post_day;

						$event_post_increment = 1;
						$event_post_month = '';
						$event_post_day = '';
						$total_events = (isset($the_query->found_posts)) ? $the_query->found_posts : 0;

						// for each post...
						while ( $the_query->have_posts() ) : $the_query->the_post();

							get_template_part( 'content', 'political-event' );

							$event_post_increment++; // increment for next loop

						endwhile;

						// Restore original Post Data
						wp_reset_postdata();

						?>
					</ul>

					<?php if (!empty($section_more)) : ?>
						<p class="section-more timeline-more"><a href="<?php echo esc_url(get_post_type_archive_link( 'political-event' )); ?>" class="btn btn-default"><i class="fa fa-2x fa-plus visible-xs-inline visible-sm-inline"></i><span class="visible-md-inline visible-lg-inline"><?php echo esc_attr($section_more) ?></span></a></p>
					<?php endif; ?>
				</div>  <!-- end column -->
			</div>  <!-- end row -->

		</div> <!-- end container -->
	</div>  <!-- end section-events -->

<?php

} // End if have posts

