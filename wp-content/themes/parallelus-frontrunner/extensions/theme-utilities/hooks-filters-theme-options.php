<?php
/**
 * Add dynamic values to theme options by filter to include and
 * select from pages, categories, slide shows and other content
 * created by the user.
 */


#-----------------------------------------------------------------
# Include page list in 404 Error select for theme options
#-----------------------------------------------------------------

if (is_admin() && !function_exists('theme_option_404_content_select')) :
	function theme_option_404_content_select( $options ) {

		$current_language = theme_option_switch_lang('default');

		$allPages = get_pages();
		$options = array('default' => 'Default');

		if (is_array($allPages)) {
			foreach ($allPages as $page) {
				$options[$page->ID] = esc_attr($page->post_title);
			}
		}

		theme_option_switch_lang($current_language);

		return $options;
	}
	// add filter: [field alias]_data_options
	add_filter( 'error-content_data_options', 'theme_option_404_content_select' );
endif;


#-----------------------------------------------------------------
# Include POST Categories list select for theme options
#-----------------------------------------------------------------

if (is_admin() && !function_exists('theme_option_blog_categories_select')) :
	function theme_option_blog_categories_select( $options ) {

		$current_language = theme_option_switch_lang('default');

		$args = array(
			'hide_empty'    => 0,
			'hierarchical'  => 1,
			'taxonomy'      => 'category',
			// 'pad_counts' => false
		);
		$categories = get_categories( $args );
		if ( !empty( $categories ) && !is_wp_error( $categories ) ){

			$items = categories_order_by_hierarchy($categories); // categories_parent_hierarchy($categories);
			// $options = array('none' => ''); // default
			$options = (is_array($options) && !empty($options)) ? $options : array('none' => 'None');
			if (is_array($items)) {
				foreach ($items as $key => $value) {
					$level = count(get_ancestors($value->term_id, 'category'));
					$options[$value->term_id] = str_repeat('&mdash; &nbsp;', $level) . esc_html( $value->name .' ('.$value->count.')' );
				}
			}
		}

		theme_option_switch_lang($current_language);

		return $options;
	}
	// add filter: [field alias]_data_options
	add_filter( 'home-section-4-source-categories_data_options', 'theme_option_blog_categories_select' );
endif;


#-----------------------------------------------------------------
# Include Content Blocks list select for theme options
#-----------------------------------------------------------------

if (is_admin() && !function_exists('theme_option_static_blocks_select')) :
	function theme_option_static_blocks_select( $options = array() ) {

		$current_language = theme_option_switch_lang('default');

		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'static_block'
		);
		$items = get_posts($args);
		$options = (is_array($options) && !empty($options)) ? $options : array('disabled' => 'Disabled');
		if (is_array($items)) {
			foreach ($items as $key => $value) {
				$options[$value->ID] = esc_html( $value->post_title );
			}
		}

		theme_option_switch_lang($current_language);

		return $options;
	}
	// add filter: [field alias]_data_options
	add_filter( 'home-section-2-content-1_data_options', 'theme_option_static_blocks_select' );
	add_filter( 'home-section-2-content-2_data_options', 'theme_option_static_blocks_select' );
	add_filter( 'home-section-4-content-block_data_options', 'theme_option_static_blocks_select' );
	add_filter( 'footer-content-block_data_options', 'theme_option_static_blocks_select' );
	add_filter( 'footer-overlap-content-block_data_options', 'theme_option_static_blocks_select' );
	// add_filter( 'sub-footer-content_data_options', 'theme_option_static_blocks_select' );
endif;

#-----------------------------------------------------------------
# Include Ninja Forms list select for theme options
#-----------------------------------------------------------------

if (is_admin() && !function_exists('theme_option_ninja_form_select')) :
	function theme_option_ninja_form_select( $options = array() ) {

		if (!function_exists('ninja_forms_get_all_forms')) {
			return $options;
		}

		$current_language = theme_option_switch_lang('default');

		// Get Ninja Forms list
		$ninja_forms = array();
		$all_forms = ninja_forms_get_all_forms();
		foreach ((array) $all_forms as $form) {
			$ninja_forms[$form['id']] = $form['name'];
		}

		$options = (is_array($options) && !empty($options)) ? $options : array('disabled' => 'Disabled');
		if (is_array($ninja_forms)) {
			foreach ($ninja_forms as $key => $value) {
				$options[$key] = esc_html( $value );
			}
		}

		theme_option_switch_lang($current_language);

		return $options;
	}
	// add filter: [field alias]_data_options
	add_filter( 'footer-overlap-ninja-form_data_options', 'theme_option_ninja_form_select' );
endif;


#-----------------------------------------------------------------
# Include POLITICAL ISSUES Categories list select for theme options
#-----------------------------------------------------------------

if (is_admin() && !function_exists('theme_option_issues_categories_select')) :
	function theme_option_issues_categories_select( $options ) {

		$current_language = theme_option_switch_lang('default');

		$args = array(
			'hide_empty'    => 0,
			'hierarchical'  => 1,
			'taxonomy'      => 'political-category',
			// 'pad_counts' => false
		);
		$categories = get_categories( $args );
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

		theme_option_switch_lang($current_language);

		return $options;
	}
	// add filter: [field alias]_data_options
	add_filter( 'home-section-3-video-categories_data_options', 'theme_option_issues_categories_select' );
	add_filter( 'home-section-6-categories_data_options', 'theme_option_issues_categories_select' );
endif;


#-----------------------------------------------------------------
# Include Simple Theme Slider list select for theme options
#-----------------------------------------------------------------

if (is_admin() && !function_exists('theme_option_st_slider_select')) :
	function theme_option_st_slider_select( $options ) {

		if (function_exists('sts_get_all_sliders')) :

			$current_language = theme_option_switch_lang('default');

			$the_query = sts_get_all_sliders();
			if ( $the_query->have_posts() ) {
				$options = array();
				while ( $the_query->have_posts() ) : $the_query->the_post();

					$id    = esc_attr(get_the_ID());
					$title = esc_attr(get_the_title());
					// Select options
					$options[$id] = $title;
				endwhile;

				/* Restore original Post Data */
				wp_reset_postdata();

			} else {
				$options = array('none' => __('No Sliders Created', 'framework'));
			}

			theme_option_switch_lang($current_language);
		else:
			$options = array('none' => __('Plugin not installed', 'framework'));
		endif; // function_exists('sts_get_all_sliders')

		return $options;
	}
	// add filter: [field alias]_data_options
	add_filter( 'home-section-5-simple-slideshow-source_data_options', 'theme_option_st_slider_select' );
endif;


#-----------------------------------------------------------------
# Include Revolution Slider list select for theme options
#-----------------------------------------------------------------

if (is_admin() && !function_exists('theme_option_rev_slider_select')) :
	function theme_option_rev_slider_select( $options ) {

		if (class_exists('RevSlider')) :

			$current_language = theme_option_switch_lang('default');

			$ss = new RevSlider();
			$arrSliders = $ss->getArrSliders();
			$options = array();
			if (count($arrSliders)) {
				foreach($arrSliders as $ss):
					// Slide data
					$id    = $ss->getID();
					$title = $ss->getTitle();
					$alias = $ss->getAlias();
					// Select options
					$options[$alias] = $title;
				endforeach;
			} else {
				$options = array('none' => __('No Sliders Created', 'framework'));
			}

			theme_option_switch_lang($current_language);
		else:
			$options = array('none' => __('Plugin not installed', 'framework'));
		endif; // class_exists('RevSlider')

		return $options;
	}
	// add filter: [field alias]_data_options
	add_filter( 'home-section-5-rev-slider-source_data_options', 'theme_option_rev_slider_select' );
endif;

// Creates multi-dimensional array of categories with hierarchy
if (is_admin() && !function_exists('categories_parent_hierarchy')) :
function categories_parent_hierarchy( $items, $parent = 0 ) {
	$op = array();
	foreach( $items as $item ) {
		if( isset($item->category_parent) && $item->category_parent == $parent ) {
			$op[$item->term_id]['post'] = $item;
			// using recursion
			$children = categories_parent_hierarchy( $items, $item->term_id );
			if( $children ) {
				$op[$item->term_id]['children'] = $children; // Use this for multidimensional (nested) categories
			}
		}
	}
	return $op;
}
endif;

// Creates one dimensional array ordered by category hierarchy
if (is_admin() && !function_exists('categories_order_by_hierarchy')) :
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

if (is_admin() && !function_exists('theme_option_switch_lang')) :
	function theme_option_switch_lang( $lang = 'default' ) {
		global $sitepress;
		if(!isset($sitepress))  // if WPML is not active
			return;

		$default_language = $sitepress->get_default_language();

		if($lang == 'default') {
			$current_language = $sitepress->get_current_language();
			$sitepress->switch_lang($default_language);
			return $current_language;
		} else {
			$sitepress->switch_lang($lang);
		}
	}
endif;