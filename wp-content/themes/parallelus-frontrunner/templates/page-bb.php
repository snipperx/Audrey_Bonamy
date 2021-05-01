<?php
/**
 * Template Name: Beaver Builder
 *
 * This template is designed to work with the Beaver Builder plugin. Applying
 * design structures to enable the best possible integration with the plugin.
 */

get_template_part('templates/parts/header', 'bb'); ?>


    <?php while ( have_posts() ) : the_post(); ?>

    <?php the_content(); ?>

    <?php endwhile; // end of the loop. ?>

<?php get_template_part('templates/parts/footer', 'bb');  ?>
