<?php

$args = array(
  
    'post_type'     => 'political-video'
);

//If Random selected
if($settings->bb_video_random==1)
{
	$args['orderby'] ='rand';
}

if((is_array($settings->bb_video_cat))  && (!in_array("no",$settings->bb_video_cat) && !in_array("",$settings->bb_video_cat) && !in_array("all",$settings->bb_video_cat)))
{
  $args['tax_query'] = array(
                          array(
                             'taxonomy' => 'political-category',
                             'terms'    => $settings->bb_video_cat
                                )
                            );
}
if($settings->bb_video_count != "")
{
  $args['posts_per_page'] =$settings->bb_video_count;
}

// The Query
query_posts( $args );

?>
<div class="wrapper video-list" >
    <div class="row">
        <div class="col-md-12">
            <div class="video-wrapper">
                <div class="close-button"> <i class="fa fa-times close-icon"></i></div>
                <div class="video-container player_container">
                    <?php
                    $counter=1;
                    while ( have_posts() ) : the_post();
                        $politicaldata=json_decode( get_post_meta(get_the_ID(),'political-video-options',true));
                        $youtube_id=$politicaldata->youtube_id;
                    ?>
                        <div class="video-element video-element-<?php echo $youtube_id;?>"><iframe id="<?php echo $youtube_id?>" frameborder="0" allowfullscreen="1" title="YouTube video player" width="640" height="360" src="https://www.youtube.com/embed/<?php echo $youtube_id?>?showinfo=0&amp;rel=0&amp;wmode=opaque&amp;enablejsapi=1&amp;origin=http%3A%2F%2Fpara.llel.us&amp;widgetid=<?php echo $counter;?>"></iframe></div>
                    <?php
                        $counter++;
                    endwhile;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <?php
        $counter=1;
        while ( have_posts() ) : the_post();

            $politicaldata=json_decode( get_post_meta(get_the_ID(),'political-video-options',true));
            $youtube_id=$politicaldata->youtube_id;
        ?>
            <div class="col-md-3 col-sm-6">
                <div class="video-thumbnail" id="thumb-<?php echo $youtube_id;?>" data-video-index="<?php echo $youtube_id;?>" style="background-image: url(https://img.youtube.com/vi/<?php echo $youtube_id;?>/0.jpg)">
                    <i class="fa fa-play-circle"></i>
                    <div class="overlay"></div>
                </div>
            </div>
        <?php
            $counter++;
        endwhile;
        // Reset Query
        wp_reset_query();
        ?>
    </div>
</div>
