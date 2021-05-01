<?php

/**
 * @class FLPostsModule
 */
if(class_exists('FLBuilderModule') && !class_exists('FLPostsModule')) {

    class FLPostsModule extends FLBuilderModule
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
                'name' => __('Posts', 'fl-builder'),
                'description' => __('', 'fl-builder'),
                'category' => __('FrontRunner Modules', 'fl-builder'),
                'partial_refresh' => true
            ));
        }
    }
}
// Creates one dimensional array ordered by category hierarchy
if (!function_exists('categories_order_by_hierarchy')) :
    function categories_order_by_hierarchy( $items, $parent = 0 ) {
        $op = array();
        foreach( $items as $item ) {
            if( isset($item->category_parent) && $item->category_parent == $parent ) {
                $op[$item->term_id] = $item;
                // using recursion
                $children = categories_order_by_hierarchy( $items, $item->term_id );
                if( $children ) {
                    $op = array_merge($op, $children); // Use this for multidimensional (nested) categories
                }
            }
        }
        return $op;
    }
endif;
/**
 * Register the module and its form settings.
 */
if (class_exists('FLBuilder')) {

    $args = array(
        'hide_empty'    => 0,
        'hierarchical'  => 1,
        // 'pad_counts' => false
    );
    $categories = get_categories( $args );
    $options = array(
        'no'=>__('No value', 'fl-builder'),
        'all'=>__('All Categories', 'fl-builder'),
    );
    if ( !empty( $categories ) && !is_wp_error( $categories ) && !isset($categories['errors']) ){

        $items = categories_order_by_hierarchy($categories); // categories_parent_hierarchy($categories);
        $options = array(
            'all' => 'All Categories', // default
        );
        if (is_array($items)) {
            foreach ($items as $key => $value) {
                $level = count(get_ancestors($value->term_id, 'political-category'));
                $options[$value->term_id] = str_repeat('&mdash; &nbsp;', $level) . esc_html( $value->name );
            }
        }
    }


    FLBuilder::register_module('FLPostsModule', array(
        'general' => array(
            'title' => __('General', 'fl-builder'),
            'sections' => array(
                'general' => array(
                    'title' => '',
                    'fields' => array(
                        'bb_more_blog_posts_button'     => array(
                            'type'          => 'text',
                            'default'       => __( 'More News', 'fl-builder' ),
                            'placeholder'   => __( 'More News', 'fl-builder' ),
                            'label'         => __( 'More Button', 'fl-builder' ),
                            'help'			=> __("The text for the more button.", 'fl-builder' )
                        ),
                        'bb_blog_categories'     => array(
                            'type'          => 'select',
                            'multi-select'  => true,
                            'label'         => __( 'Blog Categories', 'fl-builder' ),
                            'default'       => 'no',
                            'options'       => $options,
                            'help'			=> __("Select the blog categories to include.", 'fl-builder' )
                        ),
                        'bb_blog_posts_count'     => array(
                            'type'          => 'fr-number-custom-field',
                            'default'       => '4',
                            'label'         => __( 'Post Count', 'fl-builder' ),
                            'help'			=> __("Select the number of posts to display. Recommended setting: 4", 'fl-builder' )
                        ),
                    )
                )
            )
        )
    ));
}
