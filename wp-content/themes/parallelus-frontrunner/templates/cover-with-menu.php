<?php
/**
 * Template Name: Cover with Main Menu
 *
 * The template for displaying full background cover at the
 * top and a main menu at the top.
 *
 * Cover templates must be registered with the filter 'rf_is_cover_template'
 * See the example in functions.php or search the template files.
 */

// Disable default content containers.
add_filter('theme_template_has_layout', function(){ return true; });

get_header();

get_footer();
