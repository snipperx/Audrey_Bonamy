<?php

$args = array(
    'post_type'     => 'post'
);



if((is_array($settings->bb_blog_categories))&& (!in_array("",$settings->bb_blog_categories) && !in_array("all",$settings->bb_blog_categories)))
{
    // $args['cat'] =  $settings->bb_video_cat;
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'category',
            'terms'    => $settings->bb_blog_categories
        )
    );
}
if($settings->bb_blog_posts_count != "")
{
    $args['posts_per_page'] =$settings->bb_blog_posts_count;
}

// The Query
query_posts( $args );

?>
<!-- News
================================================== -->
<div id="section-news" class="wrapper">
    <div class="row">
        <div class="col-md-12">
            <h2 class="heading">News &amp; Headlines</h2>
        </div>
    </div>

    <div class="row">
        <div class="news-list col-md-12">

            <?php
            $counter=1;
            while ( have_posts() ) : the_post();
                ?>

                <article class="post">
                    <header>
                        <div class="header-meta">
                            <span class="posted-on"><?php echo get_the_date(); ?></span>
                        </div>
                        <h2 class="entry-title">
                            <a href="<?php the_permalink();?>" title="article"><?php the_title();?></a>
                        </h2>
                    </header>

                    <p>
                        <?php the_excerpt();?>
                    </p>
                    <a href="<?php the_permalink();?>" class="more-link">Continue reading</a>

                    <hr class="sep" />
                </article>
                <?php

                $counter++;
            endwhile;

            // Reset Query
            wp_reset_query();

            ?>
            <p class="section-more"><a href="<?php echo get_permalink( get_option( 'page_for_posts' ) );?>" class="btn btn-default"><?php echo $settings->bb_more_blog_posts_button?></a></p>

        </div>  <!-- end column -->
    </div>  <!-- end row -->
</div> <!-- end section-news -->