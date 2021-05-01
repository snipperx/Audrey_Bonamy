<?php
/**
 * @class FLVideosModule
 */
if (class_exists('FLBuilderModule')  && !class_exists('FLVideosModule')) {
    class FLVideosModule extends FLBuilderModule {

        public function __construct()
        {
            parent::__construct(array(
                'name'            => __( 'Campaign Videos', 'fl-builder' ),
                'description'     => __( 'This module for home page videos', 'fl-builder' ),
                'category'        => __( 'FrontRunner Modules', 'fl-builder' ),
                'editor_export'   => true, // Defaults to true and can be omitted.
                'enabled'         => true, // Defaults to true and can be omitted.
                'partial_refresh' => false, // Defaults to false and can be omitted.
                ));
        }
    }
}


global $wpdb;

$categories = $wpdb->get_results( "SELECT {$wpdb->prefix}term_taxonomy.term_id,{$wpdb->prefix}terms.name FROM  {$wpdb->prefix}term_taxonomy inner join  {$wpdb->prefix}terms on {$wpdb->prefix}term_taxonomy.term_id={$wpdb->prefix}terms.term_id where {$wpdb->prefix}term_taxonomy.taxonomy='political-category'" );
$options = array(
    'no'=>__('No value', 'fl-builder'),
    'all'=>__('All Categories', 'fl-builder'),
);
if ( !empty( $categories ) && !is_wp_error( $categories ) && !isset($categories['errors']) ){

    $options = array(
        'no'=>__('No value', 'fl-builder'),
        'all' => 'All Categories',
    );
    usort($categories, 'compareByName');
    if (is_array($categories)) {
        foreach ($categories as $key => $value) {
            $level = count(get_ancestors($value->term_id, 'political-category'));
            $options[$value->term_id] = str_repeat('&mdash; &nbsp;', $level) . esc_html( $value->name );
        }
    }
}

if (class_exists('FLBuilder') && class_exists('FLVideosModule')) {
    FLBuilder::register_module( 'FLVideosModule', array(
        'my-tab-1'      => array(
            'title'         => __( 'Campaign Videos', 'fl-builder' ),
            'sections'      => array(
                'my-section-1'  => array(

                    'fields'        => array(
                        'bb_video_cat'     => array(
                            'type'          => 'select',
                            'multi-select'  => true,
                            'label'         => __( 'Video Categories', 'fl-builder' ),
                            'options'       => $options,
                            'help'			=> "Select the video categories to optionally limit results. Ctrl/Cmd + click to select multiple items"
                            ),
                        'bb_video_count'     => array(
                            'type'          => 'select',
                            'label'         => __( 'Count', 'fl-builder' ),
                            'default'       => '4',
                            'options'       => array(
                                ''      => __('Select', 'fl-builder'),
                                '-1'      => __('Auto', 'fl-builder'),
                                '1'      => __('1', 'fl-builder'),
                                '2'      => __('2', 'fl-builder'),
                                '3'      => __('3', 'fl-builder'),
                                '4'      => __('4', 'fl-builder'),
                                '5'      => __('5', 'fl-builder'),
                                '6'      => __('6', 'fl-builder'),
                                '7'      => __('7', 'fl-builder'),
                                '8'      => __('8', 'fl-builder'),
                                '9'      => __('9', 'fl-builder'),
                                '10'      => __('10', 'fl-builder')
                                ),
                            'help'          => 'Select number of videos to display. The "Auto" option will display all videos for the selected category. Recommended setting: 4',
                            ),

                        'bb_video_random' => array(
                            'type'          => 'select',
                            'label'         => __( 'Random', 'fl-builder' ),
                            'default'       => '0',
                            'options'       => array(

                                '1'      => __('Yes', 'fl-builder'),
                                '0'      => __('No', 'fl-builder'),

                                ),
                            ),
                        )
                    )
                )
            )
        ));
}



?>