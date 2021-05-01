<?php



$wrapper_class = 'wrapper';


if(isset($settings->bb_simple_slideshow) && !empty($settings->bb_simple_slideshow)) {

    $id = $settings->bb_simple_slideshow;
    if (function_exists('sts_get_slider') && !empty($id)) {

        $simple_slider = sts_get_slider($id);
        if(is_array($simple_slider) && empty($simple_slider)){
            $simple_slider = sts_get_slider($id[0]);
        }
        if (is_array($simple_slider) && !empty($simple_slider)) {

            // content exists and which output method to use

            foreach ($simple_slider as $index => $slide) {

                // Title
                $content[$index]['title'] = (isset($slide['title'])) ? $slide['title'] : '';

                // Description
                $content[$index]['description'] = (isset($slide['description'])) ? $slide['description'] : '';

                // Image
                $content[$index]['image'] = (isset($slide['source'])) ? $slide['source'] : '';
                if (!empty($content[$index]['image'])) :
                    // the image with CSS
                    $content[$index]['image'] = 'background-image: url(' . $content[$index]['image'] . ')';
                endif;

                // Link
                $content[$index]['link'] = (isset($slide['slide-link'])) ? $slide['slide-link'] : '';
                $content[$index]['target'] = (isset($slide['open-new-window']) && $slide['open-new-window'] == 'checked') ? '_blank' : '';
            }
        }
    }

// We have some content!

    ?>
    <div id="section-slider" class="featured-slider">
        <div class="featured-carousel">


            <?php
            if(isset($content) && is_array($content)){
            foreach ($content as $key => $value) {

                // check for a link target (like a new window)
                $target = '';
                if (isset($value['target']) && !empty($value['target'])) {
                    $target = 'target="' . esc_attr($value['target']) . '"';
                }
                ?>

                <div class="item">
                    <div class="bg-img" style="<?php echo esc_attr($value['image']) ?>"></div>
                    <div class="color-hue"></div>
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-8">
                                <article>
                                    <h3>
                                        <?php echo wp_kses_post(html_entity_decode($value['title'], ENT_COMPAT)) ?>
                                        <span class="sub-title"><?php echo wp_kses_post(html_entity_decode($value['description'], ENT_COMPAT)) ?></span>
                                        <?php if (!empty($value['link'])) : ?>
                                            <a href="<?php echo esc_url($value['link']) ?>" <?php echo $target; // escaped above ?>
                                               class="btn btn-primary"><?php _e('More', 'framework') ?></a>
                                        <?php endif; ?>
                                    </h3>
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            }?>
        </div>
    </div>
    <script>
        $ = jQuery;
        if ( $('.featured-carousel').length && jQuery.isFunction($(".featured-carousel").owlCarousel) ) {

            $(".featured-carousel").owlCarousel({
                items: 1,
                loop: true,
                autoplay: true,
                autoplayHoverPause: true,
                autoplayTimeout: 3800,
                autoplaySpeed: 800,
                navSpeed: 500,
                dots: false,
                nav: true,
                navText: [
                    '<i class="fa fa-angle-left"></i>',
                    '<i class="fa fa-angle-right"></i>'
                ]
            });
        }
    </script>
    <?php
}