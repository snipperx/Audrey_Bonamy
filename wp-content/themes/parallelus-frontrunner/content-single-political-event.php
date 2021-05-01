<?php
/**
 * Event Details Page
 */
global $wp_locale;

$post_thumbnailSize = 'blog';

// Custom options
$meta_options = get_post_custom();

// Featured Image
$showFeaturedImage = false;

// Featured Image Location
if ( has_post_thumbnail() ) {
	$bg_setting = '';
	$header_size = '';
	if ( isset($meta_options['theme_custom_layout_metabox_options_header_bg']) ) {
		$bg_setting = $meta_options['theme_custom_layout_metabox_options_header_bg'][0];
		$header_size = (isset($meta_options['theme_custom_layout_metabox_options_header_style'][0])) ? $meta_options['theme_custom_layout_metabox_options_header_style'][0] : '';
	}
	$showFeaturedImage = ( $bg_setting !== 'featured-image' || $header_size == 'none' ) ? true : false;
}

// Sidebars
$class_mainSection  = 'col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2';
$class_sidebarLeft  = '';
$class_sidebarRight = '';

// Sidebar Left
$sidebarLeft = false;
if ( isset($meta_options['theme_custom_sidebar_options_left']) ) {
	$has_sidebarLeft = $meta_options['theme_custom_sidebar_options_left'][0];
	$sidebarLeft = ( !empty($has_sidebarLeft) && $has_sidebarLeft !== 'default' ) ? $has_sidebarLeft : false;
}
// Sidebar Right
$sidebarRight = false;
if ( isset($meta_options['theme_custom_sidebar_options_right']) ) {
	$has_sidebarRight = $meta_options['theme_custom_sidebar_options_right'][0];
	$sidebarRight = ( !empty($has_sidebarRight) && $has_sidebarRight !== 'default' ) ? $has_sidebarRight : false;
}

// Classes for sidebars
if ($sidebarLeft) {
	$class_mainSection  = 'col-md-9 col-md-push-3 col-lg-8 col-lg-push-4';
	$class_sidebarLeft  = 'col-md-3 col-md-pull-9 col-lg-pull-8';
	$class_sidebarRight = '';
}
if ($sidebarRight) {
	$class_mainSection  = 'col-md-9 col-lg-8';
	$class_sidebarLeft  = '';
	$class_sidebarRight = 'col-md-3 col-lg-3 col-lg-push-1';
}
if ($sidebarRight && $sidebarLeft) {
	$class_mainSection  = 'col-md-8 col-lg-6 col-lg-push-3';
	$class_sidebarLeft  = 'col-md-4 col-lg-3 col-lg-pull-6';
	$class_sidebarRight = 'col-md-4 col-lg-3';
}

// Event specific meta
// --------------------------------

$place = '';
$location = '';
$directions = '';

// Meta dates and event data
$event_meta = get_post_meta( $post->ID, 'political-event-details');
$start = get_post_meta( $post->ID, 'political-event-start' );
$end   = get_post_meta( $post->ID, 'political-event-end' );
$timezone = get_post_meta( $post->ID, 'political-event-timezone' );
$date_format = get_option('date_format');
$time_format = get_option('time_format');

// Event start
$event_start = array();
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

$event_timezone = '';
if( isset( $timezone[0] ) && ! empty( $timezone[0] ) ) {
	$event_timezone = ' ( ' . str_replace( '_', ' ', $timezone[0] ) . ' )';
}

if (isset($event_meta[0])) {
	$event_data = json_decode($event_meta[0]);
	$location = $event_data->location;
	$directions = $event_data->directions;
}

// Time / Date meta
$time = '';
// Date
if ( isset($event_start['F']) && isset($event_start['d']) && isset($event_start['Y']) ) {
	$date_start_val = date_i18n( $date_format, $start[0] );
	$time .= '<i class="fa fa-calendar"></i> <span>'. esc_attr($date_start_val) .'</span>';
}
// Start time
if (isset($event_start['t'])) {
	$time .= ' &nbsp; <i class="fa fa-clock-o"></i> ';
	if ($event_start['H:i'] == '00:01') {
		$time .= ' <span>'. __('All Day Event', 'framework'). '</span>';
	} else {
		$time .= ' <span>'. esc_attr(date($time_format, $start[0])) .'</span>';
	}
}
// End time
if (isset($event_end['t']) && $time !== $event_end['t'] && $event_start['H:i'] !== '00:01') {
	$time .= ' <span> &ndash; '. esc_attr(date($time_format, $end[0])) .'</span>';
}
// Timezone
if( isset( $event_timezone ) && ! empty( $event_timezone ) ) {
	$time .= '<span>' . esc_attr($event_timezone) . '</span>';
}

// Location meta
if (!empty($location)) {
	if (!empty($directions)) {
		$place = '<a href="'. esc_url($directions) .'" target="_blank">'. esc_attr($location) .'</a>';
	} else {
		$place = esc_attr($location);
	}
	?>
	<?php
}

?>

<div class="main-section <?php echo esc_attr($class_mainSection) ?>">

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<header class="page-header">
			<?php if ( rf_show_page_title() ) : ?>
				<h1 class="page-title"><?php the_title(); ?></h1>
			<?php endif; ?>
			<?php
			// output the start-end time meta
			if (!empty($time)) {
				?>
				<div class="time"><?php echo  $time; // escaped above ?></div>
				<?php
			}
			// output location meta
			if (!empty($place)) {
				?>
				<div class="location"><i class="fa fa-map-marker"></i> <span><?php echo  $place; // escaped above ?></span></div>
				<?php
			}
			?>
		</header>

		<div class="entry-content">

			<?php if ( $showFeaturedImage ) : ?>
			<p class="entry-thumbnail">
				<?php echo get_the_post_thumbnail( $post->ID, $post_thumbnailSize ); ?>
			</p>
			<?php endif; ?>

			<?php the_content(); ?>

			<?php

			// Event CPT Content

			// Meta dates and event data
			$notes = get_post_meta( $post->ID, 'political-event-notes' );
			$gallery = get_post_meta( $post->ID, 'political-events-gallery-ids' );

			?>

			<div class="event-meta-details">
				<?php
				// Has the event ended?
				$now = strtotime("today", time()) + (get_option('gmt_offset') * 3600);
				if ( isset($end[0]) && $end[0] < $now ) {

					// After the event notes
					if (isset($notes[0])) { ?>
						<div class="event-notes">
							<hr>
							<h4><?php _e('This event has ended', 'framework'); ?></h4>
							<p class="event-content"><?php echo wp_kses_post($notes[0]) ?></p>
						</div>
						<?php
					}

				}

				// Event photo gallery
				if (isset($gallery[0])) { ?>
					<div class="event-photo-gallery">
						<hr>
						<h4><?php _e('Photo Gallery', 'framework') ?></h4>
						<div class="event-gallery">
							<?php
							$images = explode(',', $gallery[0]);
							foreach ($images as $img_id) {
								$image = wp_get_attachment_image( $img_id, 'gallery' );
								$image_large = wp_get_attachment_image_src( $img_id, 'featured' );
								if (!empty($image)) {
									echo '<a href="'. $image_large[0] .'">'. $image .'</a>'; // cannot escape WP attachemnt full <img> tag
								}
							}
							?>
						</div>
					</div>
				<?php } ?>
			</div>


		</div>

		<?php
		// If comments are open or we have at least one comment, load up the comment template
		if ( comments_open() || '0' != get_comments_number() ) {
			comments_template();
		}
		?>

	</article>
</div>

<?php

// Sidebar Left
if ( $sidebarLeft ) { ?>
	<div class="sidebar <?php echo esc_attr($class_sidebarLeft) ?>">
		<?php get_sidebar('left'); ?>
	</div><!-- /.sidebar-left -->
	<?php
}

// Sidebar Right
if ( $sidebarRight ) { ?>
	<div class="sidebar <?php echo esc_attr($class_sidebarRight) ?>">
		<?php get_sidebar('right'); ?>
	</div><!-- /.sidebar-left -->
	<?php
} ?>
