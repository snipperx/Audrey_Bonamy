<?php

add_action( 'init', 'fl_load_module_examples' );

function fl_load_module_examples()
{
    if (class_exists('FLBuilder')) {

        #-----------------------------------------------------------------
        # Load Load Custom Module BB
        #-----------------------------------------------------------------

        require_once __DIR__ . '/beaver_builder_modules/beaver_builder_events_timeline_module/beaver_builder_events_timeline_module.php';
        require_once __DIR__ . '/beaver_builder_modules/beaver_builder_simple_slider_module/beaver_builder_simple_slider_module.php';
        require_once __DIR__ . '/beaver_builder_modules/beaver_builder_posts_module/beaver_builder_posts_module.php';
        require_once __DIR__ . '/beaver_builder_modules/beaver_builder_video_module/beaver_builder_video_module.php';


    }
}

    #-----------------------------------------------------------------
    # Custom input fields
    #-----------------------------------------------------------------

function fr_number_custom_field($name, $value, $field, $settings)
{
    if ($value == '') {
        $value = $field['default'];
    }
    echo '<input type="number" class="text text-full" name="' . $name . '" value="' . $value . '" />';
}

add_action('fl_builder_control_fr-number-custom-field', 'fr_number_custom_field', 1, 4);
if(!function_exists('compareByName')){
    function compareByName($a, $b) {
        return strcmp($a->name, $b->name);
    }
}