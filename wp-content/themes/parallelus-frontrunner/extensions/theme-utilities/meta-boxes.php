<?php

#-----------------------------------------------------------------
# Meta Boxes
#-----------------------------------------------------------------

// Layout Options (header background, title, etc.)
//=================================================================

if( ! class_exists( 'Theme_Custom_Layout_Metabox' ) ) :
class Theme_Custom_Layout_Metabox {

	public $index;
	public $field_keys;
	public $post_types;
	public $nonce_id;

	public function __construct() {

		$this->init();
	}

	private function init() {

		// Options
		$this->index = 'theme_custom_layout_metabox';
		$this->field_keys = array(
			'title',
			'header_style',
			'header_bg',
		);
		$this->post_types = array(
			'page',
			'post',
			'political-issue',
			'political-event',
			'political-video'
		);
		$this->nonce_id = $this->index.'_nonce';

		// Register with WP
		add_action( 'add_meta_boxes', array( $this, 'custom_metabox') );
		add_action( 'save_post', array( $this, 'custom_metabox_save') );

	}

	// Add boxes
	function custom_metabox() {

		// post types to include
		$post_types = apply_filters( $this->index.'_post_types', $this->post_types );

		foreach ($post_types as $type) {
			// Sidebar meta box
			add_meta_box(
				$this->index.'_options', //'custom_sidebar',
				__( 'Layout Options', 'framework' ),
				array( $this, 'metabox_fields'),
				$type, // 'post',
				'side'
			);
		}
	}

	/* Add meta box content (sidebar list) */
	function metabox_fields( $post ) {

		// Top text
		// $output = '<p>'. __( 'Select a custom sidebar.', 'framework' ) .'</p>';
		$output = '';

		// Use nonce for verification
		wp_nonce_field( $this->index, $this->nonce_id );

		// Page Title
		$options = array(
			'title'         => __("Page Title", 'framework' ),
			'default-value' => '',
			'options'       => array(
				'show' => __('Show', 'framework'),
				'hide' => __('Hide', 'framework'),
				'in-header' => __('In Header', 'framework')
			)
		);
		$output .= $this->custom_metabox_field_select( $post, 'title', $options );

		// Header Size
		$options = array(
			'title'         => __("Header Style", 'framework' ),
			'default-value' => '',
			'options'       => array(
				'show' => __('Show Header', 'framework'),
				'none' => __('Hide Header', 'framework')
			)
		);
		$output .= $this->custom_metabox_field_select( $post, 'header_style', $options );

		// Header Background
		$options = array(
			'title'         => __("Header Background", 'framework' ),
			'default-value' => '',
			'options'       => array(
				'featured-image' => __('Featured Image', 'framework'),
				'color-1' => __('Accent Color 1', 'framework'),
				'color-2' => __('Accent Color 2', 'framework'),
				'color-3' => __('Accent Color 3', 'framework'),
			)
		);
		$output .= $this->custom_metabox_field_select( $post, 'header_bg', $options );

		// Bottom text
		// $output .= '<p><em>'. __( 'The template must support the sidebar location or it will have no effect.', 'framework' ) .'</em></p>';

		echo  $output; // escaped above
	}

	// Sidebar select builder
	function custom_metabox_field_select( $post, $key = 'default', $options = array() ) {
		global $wp_registered_sidebars;

		// Field settings
		$defaults = array(
			'title'         => __("Options", 'framework' ),
			'description'   => '',
			'field-name'    => $this->index.'_options_'.$key,
			'default-key'   => 'default',
			'default-value' => '(default)',
			'options'        => array()
		);
		$settings = array_merge($defaults, $options);

		$custom_data = get_post_custom($post->ID);
		if ( isset($custom_data[$settings['field-name']][0]) ) {
			$val = $custom_data[$settings['field-name']][0];
		}
		else {
			$val = $settings['default-key'];
		}

		// The actual fields for data entry
		$output = '<p style="margin-bottom:0.5em; font-weight:bold;"><label for="myplugin_new_field">'. $settings['title'] .'</label></p>';
		$output .= '<select name="'. esc_attr($settings['field-name']) .'">';

		// Add a default option
		$output .= '<option';
		if($val == $settings['default-key'])
			$output .= ' selected="selected"';
		$output .= ' value="'. esc_attr($settings['default-key']) .'">'. $settings['default-value'] .'</option>';

		// Fill the select element with all values
		foreach($settings['options'] as $value => $name) {
			$output .= "<option";
			if($value == $val)
				$output .= " selected='selected'";
			$output .= " value='". esc_attr($value) ."'>".$name."</option>";
		}

		$output .= "</select>";

		// Additional text at bottom of meta box.
		if (!empty($settings['description'])) {
			$output .= '<p style="margin-top:0;">'. $settings['description'] .'</p>';
		}

		return $output;

	}

	/* When the post is saved, saves our custom data */
	function custom_metabox_save( $post_id ) {
		// Verify if this is an auto save routine. If not our form we dont nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		  return;

		// // verify this came from our screen and with proper authorization,
		// // because save_post can be triggered at other times
		if ( isset($_POST[$this->nonce_id]) && !wp_verify_nonce( $_POST[$this->nonce_id], $this->index ) )
		  return;

		if ( !current_user_can( 'edit_pages', $post_id ) )
			return;

		foreach ($this->field_keys as $key) {
			$alias = $this->index.'_options_'.$key;
			if ( isset($_POST[$alias]) ) {
				$data = $_POST[$alias];
				update_post_meta( $post_id, $alias, $data);
			}
		}
	}
}
endif;

// Load the meta boxes
function theme_custom_layout_metabox_load() {
	$theme_custom_layout_metabox = new Theme_Custom_Layout_Metabox();
}
add_action( 'after_setup_theme', 'theme_custom_layout_metabox_load' );




// Custom Sidebar Select
//================================================================

if( ! class_exists( 'Theme_Custom_Sidebar_Metabox' ) ) :
class Theme_Custom_Sidebar_Metabox {

	public function __construct() {

		$this->init();
	}

	private function init() {

		add_action( 'add_meta_boxes', array( $this, 'theme_custom_sidebar_metabox') );
		add_action( 'save_post', array( $this, 'theme_select_custom_meta_sidebar_save') );

	}

	// Add boxes
	function theme_custom_sidebar_metabox() {

		// post types to include
		$post_types = array('page', 'post', 'destination', 'destination-page', 'guide-lists');
		$post_types = apply_filters( 'theme_custom_sidebar_metabox_post_types', $post_types );

		foreach ($post_types as $type) {
			// Sidebar meta box
			add_meta_box(
				'theme_custom_sidebar_options', //'custom_sidebar',
				__( 'Sidebar Options', 'framework' ),
				array( $this, 'theme_select_custom_meta_sidebar'),
				$type, // 'post',
				'side'
			);
		}
	}

	/* Add meta box content (sidebar list) */
	function theme_select_custom_meta_sidebar( $post ) {

		// Top text
		// $output = '<p>'. __( 'Select a custom sidebar.', 'framework' ) .'</p>';
		$output = '';

		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'theme_custom_sidebar_options_nonce' );

		// Left Sidebar Select
		$options = array(
			'title' => __("Left Sidebar", 'framework' ),
		);
		$output .= $this->theme_select_custom_meta_sidebar_field( $post, 'left', $options );

		// Right Sidebar Select
		$options = array(
			'title' => __("Right Sidebar", 'framework' ),
		);
		$output .= $this->theme_select_custom_meta_sidebar_field( $post, 'right', $options );

		// Bottom text
		$output .= '<p><em>'. __( 'The template must support the sidebar location or it will have no effect.', 'framework' ) .'</em></p>';

		echo  $output; // escaped above
	}

	// Sidebar select builder
	function theme_select_custom_meta_sidebar_field( $post, $key = 'default', $options = array() ) {
		global $wp_registered_sidebars;

		// Field settings
		$defaults = array(
			'title'         => __("Choose a sidebar", 'framework' ),
			'description'   => '',
			'field-name'    => 'theme_custom_sidebar_options_'.$key,
			'default-key'   => 'default',
			'default-value' => '(default)'
		);
		$settings = array_merge($defaults, $options);

		$custom_data = get_post_custom($post->ID);
		if ( isset($custom_data[$settings['field-name']][0]) ) {
			$val = $custom_data[$settings['field-name']][0];
		}
		else {
			$val = $settings['default-key'];
		}

		// The actual fields for data entry
		$output = '<p style="margin-bottom:0.5em; font-weight:bold;"><label for="myplugin_new_field">'. $settings['title'] .'</label></p>';
		$output .= '<select name="'. esc_attr($settings['field-name']) .'">';

		// Add a default option
		$output .= '<option';
		if($val == $settings['default-key'])
			$output .= ' selected="selected"';
		$output .= ' value="'. esc_attr($settings['default-key']) .'">'. $settings['default-value'] .'</option>';

		// Fill the select element with all registered sidebars
		foreach($wp_registered_sidebars as $sidebar_id => $sidebar) {
			$output .= "<option";
			if($sidebar_id == $val)
				$output .= " selected='selected'";
			$output .= " value='". esc_attr($sidebar_id) ."'>".$sidebar['name']."</option>";
		}

		$output .= "</select>";

		// Description text below field.
		if (!empty($settings['description'])) {
			$output .= '<p style="margin-top:0;">'. $settings['description'] .'</p>';
		}

		return $output;

	}

	/* When the post is saved, saves our custom data */
	function theme_select_custom_meta_sidebar_save( $post_id ) {
		// Verify if this is an auto save routine. If not our form we dont nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		  return;

		// verify this came from our screen and with proper authorization,
		// because save_post can be triggered at other times
		$nonce_id = 'theme_custom_sidebar_options_nonce';

		if ( isset($_POST[$nonce_id]) && !wp_verify_nonce( $_POST[$nonce_id], plugin_basename( __FILE__ ) ) )
		  return;

		if ( !current_user_can( 'edit_pages', $post_id ) )
			return;

		$keys = array('left', 'right');
		foreach ($keys as $key) {
			$alias = 'theme_custom_sidebar_options_'.$key;
			if ( isset($_POST[$alias]) ) {
				$data = $_POST[$alias];
				update_post_meta( $post_id, $alias, $data);
			}
		}
	}
}
endif;

// Load the meta boxes
function theme_custom_sidebar_metabox_load() {
	$theme_custom_sidebar_metabox = new Theme_Custom_Sidebar_Metabox();
}
add_action( 'after_setup_theme', 'theme_custom_sidebar_metabox_load' );


