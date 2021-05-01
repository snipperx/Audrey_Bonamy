<?php
/**
 * Template part: Home Page Section - Blog Posts or Content Block
 */

// Content Source
$source = get_options_data('home-page', 'home-section-4-active', 'posts');

// section styles
$styles = '';
$container_style['background-color'] = get_options_data('home-page', 'home-section-4-bg-color');
$container_style['background-image'] = get_options_data('home-page', 'home-section-4-bg-image');
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
if (in_array('show', $actionLinks) && get_options_data('home-page', 'home-section-2-active', 'show') == 'hide' && get_options_data('home-page', 'home-section-3-active', 'show') == 'hide') {
	$wrapper_class .= ' header-links-overlay';
}


// Get Posts Content
if ($source == 'posts') {

	// Section title
	$section_title = get_options_data('home-page', 'home-section-4-title');
	$section_more  = get_options_data('home-page', 'home-section-4-more-text');

	// The Categoreis set in theme options
	$category = (array) get_options_data('home-page', 'home-section-4-source-categories');
	$categories = (is_array($category) && !empty($category[0])) ? $category : '';

	// Category or Next Posts link
	$next_post_link = explode('"', get_next_posts_link()); 
	$next_post_url = (isset($next_post_link[1])) ? $next_post_link[1] : '';
	$more_link = (!empty($categories) && $categories[0] !== 'none') ? get_category_link( (int) $categories[0] ) : $next_post_url;

	if( get_option('show_on_front') && get_option('page_on_front') !== '0' ) {
		$home_args = get_home_page_settings();
		$args = array(
				'post_type' => 'post',
				'posts_per_page' => $home_args['posts_per_page']
			);

		if ( !empty($home_args['categories']) && $home_args['categories'][0] !== 'none' ) {
			$args['category__and'] = $home_args['categories'];
		}
		query_posts( $args );
	}

	// Has content? (default WP query)
	$has_content = have_posts(); // $the_query->have_posts();

}

// Get Content Block Content
if ($source == 'content-block') {

	// The content source
	$content_id  = get_options_data('home-page', 'home-section-4-content-block');
	$column_size = get_options_data('home-page', 'home-section-4-column-size');

	// Column widths
	$column_size = (!empty($column_size)) ? $column_size : '10';
	$column_class = 'col-md-'.$column_size;
	if ($column_size == '10') {
		$column_class = 'col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2';
	}

	// Content check
	$content_block = (!empty($content_id) && $content_id !== 'none') ? $content_id : '';
	$has_content   = (!empty($content_block)) ? true : false;

}

// Show content
if ( $has_content ) {
	?>

	<div id="section-news" class="<?php echo esc_attr($wrapper_class) ?>" <?php echo  $styles; // escaped above ?>>
		<div class="container">

		<?php

		// Content - Blog Posts
		// -------------------------------------------------

		if ($source == 'posts') {

			// Blog Posts ?>
			<div class="row">
				<?php if (!empty($section_title)) : ?>
				<div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
					<h2 class="heading"><?php echo esc_attr($section_title) ?></h2>
				</div>
				<?php endif; ?>
			</div>

			<div class="row">
				<div class="news-list col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">

					<?php
					// Posts Loop (if set as source)
					/**
					 * Uses default WP query, modified by user selections for number of posts and category
					 * settings in theme options. Changes to query are made using 'pre_get_posts' action,
					 * function called from 'extensions/theme-utilities/hooks-actions.php'
					 */
					while ( have_posts() ) : the_post();

						get_template_part( 'content-post', get_post_format() );

					endwhile;

					// Restore original Post Data
					wp_reset_postdata();


					// More link
					if (!empty($more_link) && !empty($section_more)) : ?>
						<p class="section-more"><a href="<?php echo esc_url($more_link); ?>" class="btn btn-default"><?php echo esc_attr($section_more) ?></a></p>
					<?php endif; ?>

					</div>  <!-- end column -->
			</div>  <!-- end row -->
			<?php

		// Content - Static Block
		// -------------------------------------------------

		} elseif ($source == 'content-block') {

			// Content Block ?>
			<div class="row">
				<div class="<?php echo esc_attr($column_class); ?>">
					<?php the_static_block($content_block); ?>
				</div>
			</div>  <!-- end row -->
			<?php
		}

		?>

		</div>  <!-- end container -->
	</div> <!-- end section-news -->
	<?php

} // end $has_content
