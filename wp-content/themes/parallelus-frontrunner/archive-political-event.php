<?php
/**
 * Archive template for Events CPT.
 */
global $wp_locale;

$class_mainSection  = 'col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2';

// Check for Past Events (may become a widget)
$past_event_count = 4; // Number of past events to show
$date_format = get_option('date_format');
$time_format = get_option('time_format');

// Query
$args = array(
	'post_type' => 'political-event',
	'posts_per_page' => (!empty($past_event_count)) ? $past_event_count : 4,
	'order'		=> 'DESC',
	'orderby'	=> 'meta_value',
	'meta_key' 	=> 'political-event-end',
	'meta_query' => array(
		array(
			'key' => 'political-event-end',
			'value' => strtotime("today", time()) + (get_option('gmt_offset') * 3600), // current day, offset with WP timezone setting
			'type' => 'NUMERIC',
			'compare' => '<'
		)
	)
);
$past_events_query = new WP_Query( $args );

if ($past_events_query->have_posts()) {
	$class_mainSection = 'col-md-8';
}

// Start the output
get_header(); ?>

	<div class="row">
		<div class="main-section">
			<div class="<?php echo esc_attr($class_mainSection); ?>">

				<header class="page-header">
					<?php if ( rf_show_page_title() ) : ?>
						<h1 class="page-title"><?php _e('Campaign Events', 'framework'); ?></h1>
					<?php endif; ?>
				</header>

				<?php
				if ( have_posts() ) : ?>

					<ul class="timeline" id="events_list">
					    <li class="end-of-line">
					      <div class="line-fade-out"></div>
					    </li>	<?php

						$event_post_increment = 1;
						$event_post_month = '';
						$event_post_day = '';
						$total_events = (isset($wp_query->found_posts)) ? $wp_query->found_posts : 0;

						// for each post...
						while ( have_posts() ) : the_post();

							get_template_part( 'content', 'political-event' );

							$event_post_increment++; // increment for next loop

						endwhile;

						// Restore original Post Data
						wp_reset_postdata(); ?>
					</ul> <?php
				else :

					get_template_part( 'no-results', 'index' );

				endif; // end of loop. ?>

				<?php // Load more button
				$ppp = get_option('posts_per_page'); // posts per page (using WP setting)

				if (isset($total_events) && $total_events > $ppp) {

					$data_paged = (get_query_var( 'paged' )) ? (int) get_query_var( 'paged' ) : 1; // current page
					$data_count = $data_paged * $ppp; // number of events already shown (for keeping track of alternate classes)
					$data_max = ceil( (int) $total_events / $ppp ); // total number of pages

					?>
					<p class="more-events timeline-more">
						<button class="btn btn-default" id="more_events" data-paged="<?php echo esc_attr($data_paged) ?>" data-max="<?php echo esc_attr($data_max) ?>" data-count="<?php echo esc_attr($data_count) ?>">
							<i class="fa fa-2x fa-plus visible-xs-inline visible-sm-inline"></i><span class="visible-md-inline visible-lg-inline"><?php _e('More Events', 'framework'); ?></span>
						</button>
					</p>
					<?php
				}
				?>



			</div>


			<?php

			// The Loop for Past Events
			if ( $past_events_query->have_posts() ) { ?>

			<div class="sidebar col-md-3 col-md-offset-1">
				<div class="sidebar-container">

					<aside class="widget">
						<h3 class="heading"><?php _e('RECENT EVENTS', 'framework') ?></h3>

						<div class="widget-events">
							<?php

							$event_post_increment = 1;
							$event_post_month = '';
							$event_post_day = '';
							$total_events = (isset($past_events_query->found_posts)) ? $past_events_query->found_posts : 0;

							// for each post...
							while ( $past_events_query->have_posts() ) : $past_events_query->the_post();

								// Meta dates and event data
								$notes = get_post_meta( $post->ID, 'political-event-notes' );
								$gallery = get_post_meta( $post->ID, 'political-events-gallery-ids' );
								$start = get_post_meta( $post->ID, 'political-event-start' );
								$end   = get_post_meta( $post->ID, 'political-event-end' );

								// Event start
								$event_start = array();
								if (isset($start[0])) {
									$date_start_val = date_i18n( $date_format, $start[0] );
									$dt_start = new DateTime();
									$dt_start->setTimestamp($start[0]);
									$event_start['Y']   = $dt_start->format('Y');     // 2015
									//$event_start['F']   = $dt_start->format('F');     // September
									$event_start['F']   = $wp_locale->get_month($dt_start->format('n'));									
									$event_start['d']   = $dt_start->format('d');     // 26
									$event_start['t']   = $dt_start->format('h:i A'); // 1:00 AM
									$event_start['H:i'] = $dt_start->format('H:i');   // 01:00
								}

								if (isset($meta[0])) {
									$event_data = json_decode($meta[0]);
								}

								// Start time
								if (isset($event_start['t'])) {

									$time = $date_start_val;

									if ($event_start['H:i'] !== '00:01') {
										$time .= ' '. __('at', 'framework') .' '. $dt_start->format($time_format);
									}
								}

								?>

								<div class="event-entry">
									<h5 class="event-title"><?php the_title(); ?></h5>
									<p class="event-date"><?php echo esc_attr($time) ?></p>
									<?php if (isset($notes[0])) { ?>
										<p class="event-content"><?php echo wp_kses_post($notes[0]) ?></p>
									<?php } ?>
									<?php if (isset($gallery[0])) { ?>
										<div class="event-gallery">
											<a href="<?php the_permalink(); ?>">
												<?php
												$max_img = 6; $img_count = 0;
												$images = explode(',', $gallery[0]);
												foreach ($images as $img_id) {
													$image = wp_get_attachment_image( $img_id, 'thumbnail' );
													if (!empty($image)) {
														echo  $image; // cannot escape WP attachemnt full <img> tag
													}
													$img_count++;

													// Keep going?
													if ( $img_count >= $max_img ) {
														break;
													}
												}
												?>
											</a>
										</div>
									<?php } ?>
									<a href="<?php the_permalink(); ?>" class="small"><?php _e('Event Details', 'framework') ?></a>
								</div>

								<?php
							endwhile;

							// Restore original Post Data
							wp_reset_postdata();

							?>

						</div>
					</aside>

				</div>
				<?php // get_sidebar(); ?>
			</div>

			<?php

			} // End if have posts

			?>
		</div>
	</div>

<?php get_footer(); ?>