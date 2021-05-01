<?php
/**
 * Actions to apply and output theme behavior and content on the
 * template files and other WP specific areas.
 */


#-----------------------------------------------------------------
# Outputs the default template layout container wrapper
#-----------------------------------------------------------------
/**
 * This helps the theme decide if it needs to include a content container for specific pages, templates and content sources.
 * The container acts as a content wrapper for any templates without built in content containers.
 *
 * For templates not needing wrapper elements, before the get_header() function, include the following filter:
 *
 * add_filter('theme_template_has_layout', function(){ return true; });
 */
if ( ! function_exists( 'rf_default_template_wrapper' ) ) :
function rf_default_template_wrapper( $position = 'start' ) {

	// Templates not needing wrappers don't continue
	if (apply_filters('theme_template_has_layout', false)) {
		return;
	}

	$container = apply_filters('theme_template_wrapper_type', 'div'); // the container type
	$class = apply_filters('theme_template_wrapper_class', 'main-content container'); // class attribute for container

	// The opening container
	if ($position == 'start') {
		echo '<'.$container.' class="'.$class.'">';
	}
	// The closing container
	if ($position == 'end') {
		echo '</'.$container.'> <!-- /'.$class.' -->';
	}
}
endif; // rf_default_template_wrapper
add_action('output_layout', 'rf_default_template_wrapper', 1 );


if ( ! function_exists( 'get_home_page_settings' ) ) :
function get_home_page_settings() {
	$args = array();

	// Number of posts to show
	$post_count = get_options_data('home-page', 'home-section-4-post-count');
	$args['posts_per_page'] = (!empty($post_count)) ? (int) $post_count : 4;

	// The Categoreis set in theme options
	$category = (array) get_options_data('home-page', 'home-section-4-source-categories');
	$args['categories'] = (is_array($category) && !empty($category[0])) ? $category : '';

	return $args;
}
endif;

#-----------------------------------------------------------------
# Home Page Posts Query
#-----------------------------------------------------------------

// Edit the default home page posts query based on theme options
// ................................................................
if ( ! function_exists( 'theme_home_posts_query' ) ) :
function theme_home_posts_query( $query ) {

	// Make sure we're not querying another post type
	if (isset($query->query['post_type']) && $query->query['post_type'] !== 'post') {

		return;

	} else {

		// Only use on home page
		if ($query->is_main_query() && $query->is_home && !$query->is_posts_page) {

			// Are posts selected in theme options
			if (get_options_data('home-page', 'home-section-4-active', 'posts') == 'posts') {

				$args = get_home_page_settings();
				if ($query->is_paged) {

					// pages 2, 3, etc...
					$ppp = get_option('posts_per_page'); // posts per page (using WP setting)

					//Manually determine query offset (home posts + current page (minus 2) x posts per page)
					$page_offset = $args['posts_per_page'] + ( ($query->query_vars['paged']-2) * $ppp );

					//Apply page offset
					$query->set( 'offset', $page_offset );
					$query->set( 'posts_per_page', $ppp );

				} else {

					// first page shows number specified in theme options
					$query->set( 'posts_per_page', $args['posts_per_page'] );

				}

				if ( !empty($args['categories']) && $args['categories'][0] !== 'none' && $query->is_main_query() ) {
					$query->set( 'category__and', $args['categories'] );
				}
			}
		}
	}
}
endif;
add_action( 'pre_get_posts', 'theme_home_posts_query' );


// Adjust pagination for home page posts
// ................................................................
function theme_home_posts_query_pagination_adjust($found_posts, $query) {

	// Make sure we're querying posts and not another post type
	if (isset($query->query['post_type']) && $query->query['post_type'] !== 'post')
	// if ( !$query->is_posts_page )
		return $found_posts;

	// Only use on home page
	// if ($query->is_main_query() && $query->is_paged && theme_is_custom_home_page()) {
	if ($query->is_main_query() && $query->is_home && !$query->is_posts_page && $query->is_paged) {

		// Are posts selected in theme options
		if (get_options_data('home-page', 'home-section-4-active', 'posts') == 'posts') {

			// Number of posts to show
			$post_count = get_options_data('home-page', 'home-section-4-post-count');
			$home_posts = (!empty($post_count)) ? (int) $post_count : 4;
			$ppp = get_option('posts_per_page'); // posts per page (using WP setting)

			// Adjust total to appear page 1 has same number as pages 2, 3, etc.
			return ($found_posts - $home_posts) + $ppp;
		}
	}

	return $found_posts;
}
add_filter( 'found_posts', 'theme_home_posts_query_pagination_adjust', 1, 2 );


#-----------------------------------------------------------------
# Issues query
#-----------------------------------------------------------------

// Output issues ordered by "menu_order" then date.
// Future: Might add a custom posts_per_page value here later.
// ................................................................
/**
 * This may be moved into the Political Options plugin rather than
 * being in the theme code.
 */
if ( ! function_exists( 'political_issues_posts_query' ) ) :
function political_issues_posts_query( $query ) {

	// out($query);

	// Make sure we're not querying another post type
	if (isset($query->query['post_type']) && $query->query['post_type'] !== 'political-issue') {
		return;
	} else {

		// This works on the Public and Admin side
		if ($query->is_main_query() && isset($query->query['post_type']) &&  $query->query['post_type'] === 'political-issue' ) {

			// Add meta key and order parameters
			// $query->set( 'order', 'ASC');
			$query->set( 'orderby', array('menu_order' => 'ASC', 'date' => 'DESC') );
			// $query->set( 'meta_key', 'political-event-start' );

			// Add meta date params
			// $meta_dates = array(
			// 	array(
			// 		'key'     => 'political-event-start',
			// 		'value'   => strtotime("today", time()) + (get_option('gmt_offset') * 3600), // current day, offset with WP timezone setting
			// 		'type'    => 'NUMERIC',
			// 		'compare' => '>='
			// 	)
			// );
			// $query->set( 'meta_query', $meta_dates );
		}
	}
}
endif;
add_action( 'pre_get_posts', 'political_issues_posts_query' );


#-----------------------------------------------------------------
# Events query
#-----------------------------------------------------------------

// Show only future events in the archive page query.
// ................................................................
/**
 * This may be moved into the Political Options plugin rather than
 * being in the theme code.
 */
if ( ! function_exists( 'political_events_posts_query' ) ) :
function political_events_posts_query( $query ) {

	// Make sure we're not querying another post type
	if (isset($query->query['post_type']) && $query->query['post_type'] !== 'political-event') {
		return;
	} else {

		// Only use on home page
		if ($query->is_main_query() && $query->is_post_type_archive && isset($query->query['post_type']) &&  $query->query['post_type'] === 'political-event' && !is_admin()) {

			// Add meta key and order parameters
			$query->set( 'order', 'ASC');
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', 'political-event-end' );

			// Add meta date params
			$meta_dates = array(
				array(
					'key'     => 'political-event-end',
					'value'   => strtotime("today", time()) + (get_option('gmt_offset') * 3600), // current day, offset with WP timezone setting
					'type'    => 'NUMERIC',
					'compare' => '>='
				)
			);
			$query->set( 'meta_query', $meta_dates );
		}
	}
}
endif;
add_action( 'pre_get_posts', 'political_events_posts_query' );

// Get results for AJAX query for "more events"
// ................................................................
if ( ! function_exists( 'political_events_more_posts' ) ) :
function political_events_more_posts() {
	global $wp_locale;

	// posts per page (using WP setting)
	$ppp = get_option('posts_per_page');

	// The Query
	$args = array(
		'post_type'      => 'political-event',
		'posts_per_page' => -1, // get all future posts in the AJAX query
		// 'offset'         => $ppp, // skip posts already output (ignored if posts_per_page = -1)
		'order'          => 'ASC',
		'orderby'        => 'meta_value',
		'meta_key'       => 'political-event-end',
		'meta_query'     => array(
			array(
				'key'     => 'political-event-end',
				'value'   => strtotime("today", time()) + (get_option('gmt_offset') * 3600), // current day, offset with WP timezone setting
				'type'    => 'NUMERIC',
				'compare' => '>='
			)
		)
	);
	$the_query = new WP_Query( $args );

	// The Loop
	if ( $the_query->have_posts() ) {

	    $events = $the_query->get_posts();
		$resluts = array();
		$page = 1; // query starts from 2nd page of results

		$i = 0;
        $options = political_settings_get_options();
		// for each post...
        foreach ($events as $event) {

            $post = $event;

			// $timelineClass = ($i%2 == 0) ? 'standard' : 'inverted';
			$time = '';
			$place = '';
			$location = '';
			$directions = '';
			$event_date_title = '';

			// Meta dates and event data
			$meta  = get_post_meta( $post->ID, 'political-event-details');
			$start = get_post_meta( $post->ID, 'political-event-start' );
			$end   = get_post_meta( $post->ID, 'political-event-end' );

			// Event start
			$event_start = array();
			$time_format = get_option('time_format');
			$timezone = get_post_meta( $post->ID, 'political-event-timezone' );

			// Timezone
			$event_timezone = '';
			if( isset( $timezone[0] ) && ! empty( $timezone[0] ) ) {
				$event_timezone = ' ( ' . str_replace( '_', ' ', $timezone[0] ) . ' )';
			}

			if (isset($start[0])) {
				$dt_start = new DateTime();
				$dt_start->setTimestamp($start[0]);
				$event_start['Y']   = $dt_start->format('Y');     // 2015
				//$event_start['F']   = $dt_start->format('F');     // September
				$event_start['F']   = $wp_locale->get_month($dt_start->format('n'));
				$event_start['d']   = $dt_start->format('d');     // 26
				$event_start['t']   = $dt_start->format('h:i A'); // 1:00 AM
				$event_start['h:i'] = $dt_start->format('h:i');   // 1:00
				$event_start['H:i'] = $dt_start->format('H:i');   // 01:00
				$event_start['A']   = $dt_start->format('A');     // AM / PM
			}

			// Event end
			$event_end = array();
			if (isset($end[0])) {
				$dt_end = new DateTime();
				$dt_end->setTimestamp($end[0]);
				$event_end['Y']   = $dt_end->format('Y');     // 2015
				//$event_end['F']   = $dt_end->format('F');     // September
				$event_end['F']   = $wp_locale->get_month($dt_end->format('n'));
				$event_end['d']   = $dt_end->format('d');     // 26
				$event_end['t']   = $dt_end->format('h:i A'); // 1:00 AM
				$event_end['h:i'] = $dt_end->format('h:i');   // 1:00
				$event_end['H:i'] = $dt_end->format('H:i');   // 01:00
				$event_end['A']   = $dt_end->format('A');     // AM / PM
			}

			if (isset($meta[0])) {
				$event_data = json_decode($meta[0]);
				$location = $event_data->location;
				$directions = $event_data->directions;
			}

			// Time meta
			// --------------------------------
			$time = '';

			// Start time
			if (isset($event_start['t'])) {
				if ($event_start['H:i'] == '00:01') {
					$time = __('All Day Event', 'framework');
				} else {
					$time = $dt_start->format($time_format);
				}
			}
			// End time
			if (isset($event_end['t']) && $time !== $event_end['t'] && $event_start['H:i'] !== '00:01') {
				$time .= ' &ndash; '. $dt_end->format($time_format);
			}
			// Timezone
			$time .= $event_timezone;

			// Location meta
			// --------------------------------
			if (!empty($location)) {
				if (!empty($directions)) {
					$place = '<a href="'. esc_url($directions) .'" target="_blank">'. esc_attr($location) .'</a>';
				} else {
					$place = esc_attr($location);
				}

				$place = '<div class="location"><i class="fa fa-map-marker"></i>'. $place .'</div>';
			}

			// The event time show above event
			// --------------------------------
			if (!empty($time) && isset($event_start['h:i'])  && isset($event_start['A'])) {
				if (isset($event_start['H:i']) && $event_start['H:i'] !== '00:01') {

					$event_date_title = '<div class="date-title">'. esc_attr( $dt_start->format($time_format) ) .'</div>';

				} else {

					$event_date_title = '<div class="date-title">'. __('All Day', 'framework') .'</div>';

				}
			}

            $event_title = $post->post_title;
			if( $options['show_title_as_link'] == 'on' ) {
                $event_title = '<div class="tl-heading"><a href="'.get_the_permalink().'">'. $event_title .'</a></div>';
            }else{
                $event_title = '<div class="tl-heading">'.$event_title.'</div>';
            }

			// Update the results array
			$resluts[$page]['id_'.$post->ID] = array(
				'event_month' => esc_attr($event_start['F']),
				'event_day' => esc_attr($event_start['d']),
				'event_time' => $time,
				'event_time_start_period' => $event_start['A'],
				'event_time_end_period' => $event_end['A'],
				'event_title' => $event_title,
				'event_date_title' => $event_date_title,
				'event_place' => $place,
				'event_desc' => get_the_excerpt(),
				'event_loc' => $location,
				'event_loc_link' => $directions
			);

			// increment count
			$i++;

			// increment paging
			if ($i%$ppp == 0 || $page == 0) {
				$page++;
			}

        }

		// Restore original Post Data
		wp_reset_postdata();

	} // End if have posts

	// response output
	header( "Content-Type: application/json" );
	echo json_encode( $resluts );

	wp_die();

}
endif;
if ( is_admin() ) {
	// AJAX functions must be wrapped in admin only condition (even public ones)
	add_action( 'wp_ajax_political_events_more_posts', 'political_events_more_posts' );
	add_action( 'wp_ajax_nopriv_political_events_more_posts', 'political_events_more_posts' );

	// Fire AJAX calls on JS request
	if (isset($_REQUEST['political_event_action'])) {
		do_action( 'wp_ajax_nopriv_' . $_REQUEST['political_event_action'] );
	}
	if (isset($_POST['political_event_action'])) {
		do_action( 'wp_ajax_' . $_POST['political_event_action'] );
	}
}

#-----------------------------------------------------------------
# Video query
#-----------------------------------------------------------------

// Change the posts per page for Video CPT queries
// ................................................................
if ( ! function_exists( 'political_videos_posts_query' ) ) :
function political_videos_posts_query( $query ) {

	// Make sure we're not querying another post type
	if (isset($query->query['post_type']) && $query->query['post_type'] !== 'political-video') {

		return;

	} else {

		// Only use on home page
		if ($query->is_main_query() && $query->is_post_type_archive && isset($query->query['post_type']) &&  $query->query['post_type'] === 'political-video' && !is_admin()) {

			// Add meta key and order parameters
			$query->set( 'posts_per_page', 6);
		}
	}
}
endif;
add_action( 'pre_get_posts', 'political_videos_posts_query' );

// Get results for AJAX query for "more videos"
// ................................................................
if ( ! function_exists( 'political_videos_more_posts' ) ) :
function political_videos_more_posts() {

	// posts per page (using WP setting)
	$ppp = 6; // get_option('posts_per_page');

	// The Query
	$args = array(
		'post_type' => 'political-video',
		'posts_per_page' => -1,
		// 'offset'         => $ppp, // skip posts already output (ignored if posts_per_page = -1)
	);

	$the_query = new WP_Query( $args );

	// The Loop
	if ( $the_query->have_posts() ) {

		$resluts = array();
		$page = 1; // query starts from 2nd page of results

		$i = 0;

		// for each post...
		while ( $the_query->have_posts() ) : $the_query->the_post();

			$post = $the_query->post;

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

			// Thumbnail
			$video_thumb = '';
			if ( has_post_thumbnail() ) {
				$image_ID = get_post_thumbnail_id( $post->ID );
				$image = wp_get_attachment_image_src( $image_ID, 'gallery' );
				$video_thumb = (isset($image[0])) ? $image[0] : '';
			}

			// Update the results array
			$resluts[$page]['id_'.$post->ID] = array(
				'video_id' => $videoID,
				'video_thumb' => $video_thumb,
				'video_title' => get_the_title(),
				'video_desc' => get_the_excerpt()
			);

			// increment count
			$i++;

			// increment paging
			if ($i%$ppp == 0 || $page == 0) {
				$page++;
			}

		endwhile;

		// Restore original Post Data
		wp_reset_postdata();

	} // End if have posts

	// response output
	header( "Content-Type: application/json" );
	echo json_encode( $resluts );

	wp_die();

}
endif;
if ( is_admin() ) {
	// AJAX functions must be wrapped in admin only condition (even public ones)
	add_action( 'wp_ajax_political_videos_more_posts', 'political_videos_more_posts' );
	add_action( 'wp_ajax_nopriv_political_videos_more_posts', 'political_videos_more_posts' );

	// Fire AJAX calls on JS request
	if (isset($_REQUEST['political_video_action'])) {
		do_action( 'wp_ajax_nopriv_' . $_REQUEST['political_video_action'] );
	}
	if (isset($_POST['political_video_action'])) {
		do_action( 'wp_ajax_' . $_POST['political_video_action'] );
	}
}