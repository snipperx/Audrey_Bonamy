<?php

/**
 * @class FLEvenHistoryModule
 */
if(class_exists('FLBuilderModule') && !class_exists('FLEvenHistoryModule')) {


    class FLEvenHistoryModule extends FLBuilderModule
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
            parent::__construct(array(
                'name' => __('Events Timeline', 'fl-builder'),
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
    global $wpdb;
    $args = array(
        'hide_empty'    => 0,
        'hierarchical'  => 1,
        'taxonomy'      => 'political-category',
        // 'pad_counts' => false
    );
    $options = array(
        'no'=>__('No value', 'fl-builder'),
        'all'=>__('No Categories Created', 'fl-builder'),
    );

    $categories = $wpdb->get_results( "SELECT {$wpdb->prefix}term_taxonomy.term_id,{$wpdb->prefix}terms.name FROM  {$wpdb->prefix}term_taxonomy inner join  {$wpdb->prefix}terms on {$wpdb->prefix}term_taxonomy.term_id={$wpdb->prefix}terms.term_id where {$wpdb->prefix}term_taxonomy.taxonomy='political-category'" );

    if ( !empty( $categories ) && !is_wp_error( $categories ) && !isset($categories['errors']) ){

        $options = array(
            'no'=>__('No value', 'fl-builder'),
            'all' => 'All Categories', // default
        );
        usort($categories, 'compareByName');
        if (is_array($categories)) {
            foreach ($categories as $key => $value) {
                $level = count(get_ancestors($value->term_id, 'political-category'));
                $options[$value->term_id] = str_repeat('&mdash; &nbsp;', $level) . esc_html( $value->name );
            }
        }
    }else{
        $options = array(
            'no'=>__('No value', 'fl-builder'),
            'all'=>__('No Categories Created', 'fl-builder'),
            );
    }

    FLBuilder::register_module('FLEvenHistoryModule', array(
        'general' => array(
            'title' => __('General', 'fl-builder'),
            'sections' => array(
                'general' => array(
                    'title' => '',
                    'fields' => array(
                        'bb_events_more_button'     => array(
                            'type'          => 'text',
                            'default'       => __( 'More Events', 'fl-builder' ),
                            'placeholder'   => __( 'More Events', 'fl-builder' ),
                            'label'         => __( 'More Button', 'fl-builder' ),
                            'help'			=> __("The text for more button", 'fl-builder' )
                        ),
                        'bb_events_categories'     => array(
                            'type'          => 'select',
                            'multi-select'  => true,
                            'label'         => __( 'Categories', 'fl-builder' ),
                            'default'       => 'no',
                            'options'       => $options,
                            'help'			=> __("Select the categories to optionally limit event results. Ctrl/Cmd + click to select multiple items", 'fl-builder' )
                        ),
                        'bb_events_count'     => array(
                            'type'          => 'fr-number-custom-field',
                            'default'       => '4',
                            'label'         => __( 'Event Count', 'fl-builder' ),
                            'help'			=> __("Select the number of posts to display. Recommended setting: 4", 'fl-builder' )
                        ),
                    )
                )
            )
        )
    ));
}
