<?php

/**
 * @class FLSimpleSliderModule
 */
if(class_exists('FLBuilderModule') && !class_exists('FLSimpleSliderModule')) {

    class FLSimpleSliderModule extends FLBuilderModule
    {

        /**
         * @property $data
         */
        public $data = null;

        /**
         * @method __construct
         */
        public function __construct()
        {
            $directory_uri = (IS_CHILD) ? get_stylesheet_directory_uri() : get_template_directory_uri();

            $this->add_js( 'owl-carousel-qwe', $directory_uri . '/assets/js/owl.carousel.min.js', array('jquery'), '2.0.0-beta.2.4', true );

            $this->add_css( 'owl-carousel-qwe', $directory_uri . '/assets/css/owl-carousel.css');


            parent::__construct(array(
                'name' => __('Simple Theme Slider', 'fl-builder'),
                'description' => __('', 'fl-builder'),
                'category' => __('FrontRunner Modules', 'fl-builder'),
                'partial_refresh' => true
            ));
        }
    }
}

/**
 * Register the module and its form settings.
 */
if (class_exists('FLBuilder')) {

    $options = array('none' => __('Plugin not installed', 'framework'));
    if (function_exists('sts_get_all_sliders')) {

        $the_query = sts_get_all_sliders();
        if ($the_query->have_posts()) {
            $options = array();
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $id = esc_attr(get_the_ID());
                $title = esc_attr(get_the_title());
                // Select options
                $options[$id] = $title;
            }
            wp_reset_postdata();

        } else {
            $options = array('none' => __('No Sliders Created', 'framework'));
        }


    }


    FLBuilder::register_module('FLSimpleSliderModule', array(
        'general' => array(
            'title' => __('General', 'fl-builder'),
            'sections' => array(
                'general' => array(
                    'title' => '',
                    'fields' => array(
                        'bb_simple_slideshow'     => array(
                            'type'          => 'select',
                            'multi-select'  => true,
                            'label'         => __( 'Simple Slideshow', 'fl-builder' ),
                            'default'       => '123',
                            'options'       => $options,
                            'help'			=> __("Select a slideshow.", 'fl-builder' )
                        ),
                    )
                )
            )
        )
    ));
}