<?php



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
    'posts_per_page' => (!empty($settings->bb_events_count)) ? $settings->bb_events_count : 3, // could set a max here if needed
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
if ((is_array($settings->bb_events_categories)) && !in_array("no",$settings->bb_events_categories)  && !in_array("all",$settings->bb_events_categories)) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'political-category',
            'field'    => 'term_id',
            'terms'    => $settings->bb_events_categories,
        )
    );
}
$the_query = new WP_Query( $args );

// The Loop
if ( $the_query->have_posts() ) {

    ?>

    <div id="section-events" class="<?php echo esc_attr($wrapper_class) ?>">
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

                <?php if (!empty($settings->bb_events_more_button)) : ?>
                    <p class="section-more timeline-more"><a href="<?php echo esc_url(get_post_type_archive_link( 'political-event' )); ?>" class="btn btn-default"><i class="fa fa-2x fa-plus visible-xs-inline visible-sm-inline"></i><span class="visible-md-inline visible-lg-inline"><?php echo $settings->bb_events_more_button  ?></span></a></p>
                <?php endif; ?>
            </div>  <!-- end column -->
        </div>  <!-- end row -->
    </div>  <!-- end section-events -->

    <?php

} // End if have posts

