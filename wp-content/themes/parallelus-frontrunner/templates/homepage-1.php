<?php
/**
 * Template Name: Home Page Layout 1
 * 
 * A template to replicate the home page output.
 *
 */

// Template has built-in content containers.
add_filter('theme_template_has_layout', function(){ return true; });

get_header(); ?>

<?php

// Get the template for home page layout
get_template_part('templates/parts/home', 'layout-1');

?>

<?php get_footer(); ?>