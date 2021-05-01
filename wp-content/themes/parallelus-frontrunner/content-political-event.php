<?php
/**
 * Post content used in Event loop
 */

global $event_post_increment, $event_post_month, $event_post_day, $wp_locale;

$timelineClass = ($event_post_increment%2 == 0) ? 'standard' : 'inverted';
$location = '';
$directions = '';

// Meta dates and event data
$meta  = get_post_meta( $post->ID, 'political-event-details');
$start = get_post_meta( $post->ID, 'political-event-start' );
$end   = get_post_meta( $post->ID, 'political-event-end' );
$timezone = get_post_meta( $post->ID, 'political-event-timezone' );
$settings = political_settings_get_options();
$date_format = get_option('date_format');
$time_format = get_option('time_format');

//Show title as link
$show_title_as_link = ( isset( $settings['show_title_as_link'] ) && $settings['show_title_as_link'] == 'on' )? true : false;

// Event start
$event_start = array();
if (isset($start[0])) {
	$dt_start = new DateTime();
	$dt_start->setTimestamp($start[0]);
	$event_start['Y']   = $dt_start->format('Y');     // 2015
	$event_start['F']   = $dt_start->format('F');     // September
	//$event_start['F']   = WP_Locale::get_month($event_start['F']);
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

// Timezone
$event_timezone = '';
if( isset( $timezone[0] ) && ! empty( $timezone[0] ) ) {
	$event_timezone = ' ( ' . str_replace( '_', ' ', $timezone[0] ) . ' )';
}

if (isset($meta[0])) {
	$event_data = json_decode($meta[0]);
	$location = $event_data->location;
	$directions = $event_data->directions;
}


// Output Month/Day marker
if ( isset($event_start['F']) && isset($event_start['d']) && ($event_start['d'] !== $event_post_day || $event_start['F'] !== $event_post_month) ) {
	?>
	<li class="timeline-date">
		<div class="date"><?php echo esc_attr($event_start['d']) ?></div>
		<div class="month"><?php echo esc_attr($event_start['F']) ?></div>
	</li>
	<?php
	// Update for next loop
	$event_post_month = (isset($event_start['F'])) ? $event_start['F'] : $event_post_month;
	$event_post_day   = (isset($event_start['d'])) ? $event_start['d'] : $event_post_day;
}

// Output the Events timeline items ?>

<li class="timeline-<?php echo esc_attr($timelineClass) ?>">
	<div class="circle"></div>
	<div class="tl-panel">
		<?php if( $show_title_as_link ): ?>
			<div class="tl-heading"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
		<?php else: ?>
			<div class="tl-heading"><?php the_title(); ?></div>
		<?php endif ?>
		<div class="tl-body">
			<?php the_excerpt(); ?>
			<?php
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

				// output the start-end(timezone) time meta
				if (!empty($time)) {
					?>
					<div class="time"><i class="fa fa-clock-o"></i> <?php echo esc_attr($time) ?></div>
					<?php
				}

				// Location meta
				// --------------------------------
				if (!empty($location)) {
					if (!empty($directions)) {
						$place = '<a href="'. esc_url($directions) .'" target="_blank">'. esc_attr($location) .'</a>';
					} else {
						$place = esc_attr($location);
					}
					?>
					<div class="location"><i class="fa fa-map-marker"></i> <?php echo  $place; // escaped above ?></div>
					<?php
				}

			?>
		</div>

	</div>
	<?php
	if (!empty($time) && isset($event_start['h:i'])  && isset($event_start['A'])) {
		if (isset($event_start['H:i']) && $event_start['H:i'] !== '00:01') {
			?>
			<div class="date-title"><?php echo $dt_start->format($time_format);//esc_attr($event_start['h:i']) ?> <span><?php //echo esc_attr($event_start['A']) ?></span></div>
			<?php
		} else {
			?>
			<div class="date-title"><?php _e('All Day', 'framework'); ?></div>
			<?php
		}
	}
	?>
</li>
