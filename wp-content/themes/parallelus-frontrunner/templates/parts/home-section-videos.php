<?php
/**
 * Template part: Home Page Section - Videos
 */

// The Categoreis set in theme options
$category = (array) get_options_data('home-page', 'home-section-3-video-categories');
$categories = (is_array($category) && !empty($category[0])) ? $category : '';

// // Section title
$section_title = get_options_data('home-page', 'home-section-3-title');
$section_more  = get_options_data('home-page', 'home-section-3-more-text');

// // Items to show
$item_count = get_options_data('home-page', 'home-section-3-video-count');
$item_count = ( $item_count == 'auto' ) ? -1 : $item_count; // default

// // Use random
$random = get_options_data('home-page', 'home-section-3-source-random');
$random = ( $random == 'true' ) ? true : false;

// // section styles
$styles = '';
$container_style['background-color'] = get_options_data('home-page', 'home-section-3-bg-color');
$container_style['background-image'] = get_options_data('home-page', 'home-section-3-bg-image');
foreach ($container_style as $attribute => $style) {
	if ( isset($style) && !empty($style) && $style !== '#') {
		if ($attribute == 'background-image') {
			$style = 'url('. $style .')';
		}
		$styles .= $attribute .':'. $style .';';
	}
}
$styles = (!empty($styles)) ? 'style="'.esc_attr($styles).'"' : '';

$wrapper_class = 'wrapper video-list';

// Has action links above?
$actionLinks = array();
for ($i = 1; $i <= 4; $i++) {
	$actionLinks[$i] = get_options_data('home-page', 'home-action-link-'.$i, 'show');
}
if (in_array('show', $actionLinks) && get_options_data('home-page', 'home-section-2-active', 'show') == 'hide') {
	$wrapper_class .= ' header-links-overlay';
}

// Content Sections
// -------------------------------------------------

// The Query
$args = array(
	'post_type' => 'political-video',
	'posts_per_page' => (!empty($item_count)) ? $item_count : 4, // could set a max here if needed
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
if ($random) {
	// remove_all_filters('posts_orderby');
	$args['orderby'] = 'rand';
}
$the_query = new WP_Query( $args );

// The Loop
if ( $the_query->have_posts() ) {

	?>

	<!-- Campaign Video
	================================================== -->
	<div id="section-videos" class="<?php echo esc_attr($wrapper_class) ?>" <?php echo  $styles; // escaped above ?>>

		<div class="container">

			<h2 class="heading"><?php echo esc_attr($section_title) ?></h2>

			<div class="row">
				<div class="col-md-12">
					<div class="video-wrapper">
						<div class="close-button">
							<i class="fa fa-times close-icon"></i>
						</div>
						<div id="player_container" class="video-container">

						<?php

						// for each post...
						while ( $the_query->have_posts() ) : $the_query->the_post();

							// Video ID
							$videoID = 'custom';
							$videoClass = 'video-custom';
							$youTubeID = '';
							$meta = get_post_meta ( $post->ID, 'political-video-options', false );
							if (isset($meta[0])) {
								$video_data = json_decode($meta[0]);
								$youTubeID = (isset($video_data->youtube_id)) ? $video_data->youtube_id : '';
								if ( strpos($youTubeID, 'http') !== false ) {
									// provided a URL so extract ID
									$youTubeID = theme_get_youtube_id( $youTubeID );
								}
							}
							if (!empty($youTubeID)) {
								$videoID = $youTubeID;
								$videoClass = 'video-youtube';
							}

							// Output the video thumbnail
							?>
							<div class="video-element video-element-<?php echo esc_attr($videoID) ?>">
								<div id="<?php echo esc_attr($videoID) ?>" class="<?php echo esc_attr($videoClass) ?>"></div><!-- Use YouTube Video ID here and in 'video-element-######' class of parent -->
							</div>
							<?php

						endwhile;

						rewind_posts(); // start over the loop

						?>

						</div>
					</div>
				</div>
			</div>

			<div class="row">

				<?php

				// Specify the container column class to use based on # of videos
				$colClass = '';
				switch ($item_count) {
					case 1:
						$colClass = 'col-sm-8 col-sm-push-2 col-md-6 col-md-push-3';
						break;
					case 2:
						$colClass = 'col-lg-4 col-lg-push-2 col-sm-6';
						break;
					case 3:
					case 6:
					case 9:
						$colClass = 'col-sm-4';
						break;
					case 5:
						$colClass = 'col-sm-2 col-sm-push-1';
						break;
					case 8:
					case 12:
					case 16:
					default:
						$colClass = 'col-md-3 col-sm-6'; // default (4)
						break;
				}

				// for each post...
				while ( $the_query->have_posts() ) : $the_query->the_post();

					// Video ID
					$videoID = 'custom';
					$youTubeID = '';
					$meta = get_post_meta ( $post->ID, 'political-video-options', false );
					if (isset($meta[0])) {
						$video_data = json_decode($meta[0]);
						$youTubeID = (isset($video_data->youtube_id)) ? $video_data->youtube_id : '';
						if ( strpos($youTubeID, 'http') !== false ) {
							// provided a URL so extract ID
							$youTubeID = theme_get_youtube_id( $youTubeID );
						}
					}
					if (!empty($youTubeID)) {
						$videoID = $youTubeID;
					}

					// Output the video thumbnail
					?>
					<div class="<?php echo esc_attr($colClass) ?>">
						<?php
						// image CSS string
						$image_style = '';

						// Get the image
						if ( has_post_thumbnail() ) : ?>
							<?php
							$image_ID = get_post_thumbnail_id( $post->ID );
							$image = wp_get_attachment_image_src( $image_ID, 'blog-landscape' );
							$image_style = 'background-image: url('.$image[0].')'; // the URL
						endif; ?>
						<div class="video-thumbnail" id="thumb-<?php echo esc_attr($videoID) ?>" data-video-index="<?php echo esc_attr($videoID) ?>" style="<?php echo esc_attr($image_style) ?>">
							<i class="fa fa-play-circle"></i>
							<div class="overlay"></div>
						</div>
					</div>
				<?php endwhile; ?>

				<?php if (!empty($section_more)) { ?>
				<div class="col-sm-12">
					<p class="section-more"><a href="<?php echo esc_url(get_post_type_archive_link( 'political-video' )); ?>" class="btn btn-default"><?php echo esc_attr($section_more) ?></a></p>
				</div>
				<?php } ?>

			</div> <!-- end row -->
		</div> <!-- end container -->
	</div> <!-- end section-videos -->

<?php

} // End if has_content
