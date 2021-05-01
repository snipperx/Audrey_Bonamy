<?php
/**
 * Template for the Videos CPT archive page
 */

// Include YouTube API
wp_enqueue_script( 'youtube-iframe_api', 'https://www.youtube.com/iframe_api', array('jquery', 'theme-js'), '1.0', true );

get_header(); ?>

	<div class="row">
		<div class="main-section col-md-12">

			<header class="page-header">
				<?php if ( rf_show_page_title() ) : ?>
					<h1 class="page-title"><?php _e('Campaign Videos', 'framework'); ?></h1>
				<?php endif; ?>
			</header>

			<div class="entry-content">
				<div class="video-list">

				<?php if ( have_posts() ) :

					$total_events = (isset($wp_query->found_posts)) ? $wp_query->found_posts : 0;
					?>

					<div class="row">
						<div class="col-md-12">
							<div class="video-wrapper">
								<div class="close-button">
									<i class="fa fa-times close-icon"></i>
								</div>
								<div id="player_container" class="video-container">

									<?php

									// for each post...
									while ( have_posts() ) : the_post();

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

					<div id="player_list" class="row">

						<?php

						// for each video...
						while ( have_posts() ) : the_post();

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
							<div class="col-md-4 col-sm-6">
								<article class="video-entry">
									<?php
									// image CSS string
									$image_style = '';

									// Get the image
									if ( has_post_thumbnail() ) :
										$image_ID = get_post_thumbnail_id( $post->ID );
										$image = wp_get_attachment_image_src( $image_ID, 'gallery' );
										$image_style = 'background-image: url('.$image[0].')'; // the URL
									endif; ?>
									<div class="video-thumbnail" id="thumb-<?php echo esc_attr($videoID) ?>" data-video-index="<?php echo esc_attr($videoID) ?>" style="<?php echo esc_attr($image_style) ?>">
										<i class="fa fa-play-circle"></i>
										<div class="overlay"></div>
									</div>
									<h3 class="video-title"><?php the_title(); ?></h3>
									<p class="video-desc"><?php echo get_the_excerpt(); ?></p>
								</article>
							</div>
						<?php endwhile; ?>

					</div>

					<?php // Load more button
					$ppp = 6; // get_option('posts_per_page');

					if (isset($total_events) && $total_events > $ppp) {

						$data_paged = (get_query_var( 'paged' )) ? (int) get_query_var( 'paged' ) : 1; // current page
						$data_count = $data_paged * $ppp; // number of events already shown (for keeping track of alternate classes)
						$data_max = ceil( (int) $total_events / $ppp ); // total number of pages

						?>
						<p class="section-more text-center">
							<button class="btn btn-default" id="more_videos" data-paged="<?php echo esc_attr($data_paged) ?>" data-max="<?php echo esc_attr($data_max) ?>" data-count="<?php echo esc_attr($data_count) ?>"><?php _e('More Campaign Videos', 'framework'); ?>
							</button>
						</p>
						<?php
					}
					?>

				<?php endif; // end have_posts() ?>

				</div>
			</div>
		</div>
	</div>

<?php get_footer(); ?>