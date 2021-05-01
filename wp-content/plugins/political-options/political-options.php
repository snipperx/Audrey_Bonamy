<?php
/*
Plugin Name: Political Options
Plugin URI: http://para.llel.us
Description: Create political options.
Author: Parallelus
Author URI: http://para.llel.us
Version: 1.1.4
*/

// Don't load directly
if ( !defined( 'ABSPATH' ) ) die( '-1' );

// Includes
require_once( plugin_dir_path( __FILE__ ) . 'common-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'ninja-forms-settings.php' );
if ( ! class_exists('NF_Stripe') ) {
	require_once( plugin_dir_path( __FILE__ ) . 'paypal/paypal.php' );
}
require_once( plugin_dir_path( __FILE__ ) . 'landing-pages.php' );

// Main plugin class
if( ! class_exists( 'Political_Options_CPT' ) ) :
class Political_Options_CPT {

	public function __construct() {

		$this->init();
	}

	private function init() {

		$this->setup_constants();

		// Actions
		add_action( 'plugins_loaded', array( $this, 'load_languages' ), 11 );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ), 100 );
		add_action( 'init', array( $this, 'register_post_types' ), 101 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_function' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_data' ) );

		add_action( 'admin_menu', array( $this, 'change_political_options_menu' ) );
		add_action( 'manage_political-event_posts_custom_column', array( $this, 'manage_event_custom_columns'), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'add_event_filtering' ) );
		add_action( 'pre_get_posts', array( $this, 'change_query_after_filtering' ) );
		add_action( 'wp_ajax_get_ninja_fields_by_form_id', array( $this, 'get_ninja_fields_by_form_id_ajax') );

		// Filters
		add_filter( 'manage_political-event_posts_columns', array( $this, 'manage_event_columns') );
		add_filter( 'manage_political-video_posts_columns', array( $this, 'manage_video_columns') );
		add_filter( 'months_dropdown_results', '__return_empty_array' );

		// WP Init
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

		// Compatibility
		$this->backward();

		// Settings
		$this->donations = political_donations_get_options();
		$this->settings  = political_settings_get_options();

		// Landing Pages
		$this->landing_pages = new Political_Options_Landing_Pages();

	}

	function backward() {

		// Old data needs to be moved (temporary for backwards compatibility)
		if ( get_option( 'front_runner_donations_options' ) ) {
			update_option( 'political_options_donations', get_option( 'front_runner_donations_options' ) );
			delete_option('front_runner_donations_options');
		}
		if ( get_option( 'front_runner_newsletter_options' ) ) {
			update_option( 'political_options_newsletter', get_option( 'front_runner_newsletter_options' ) );
			delete_option( 'front_runner_newsletter_options' );
		}
	}

	function plugins_loaded() {
		 // PayPal settings in Ninja Forms
		if ( $this->donations['ninja_forms_integration'] == 'enabled' ) {
			$this->nf_pp_integration = new Political_Options_NF_PP_Integration();
		}
	}

	function load_scripts($hook) {
		if( is_admin() ) {
			global $current_screen;

			// JS
			if ( $current_screen->base == 'post' && $current_screen->post_type == 'political-event' ) {
				// Events admin
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_script( 'political-options-script', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/js/political-options.js', array( 'jquery' ), '', true );
				wp_enqueue_script( 'political-options-event-script', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/js/political-options-event.js', array( 'jquery' ), '', true );
				wp_localize_script( 'political-options-event-script', 'dp_translations', datepicker_localization_rf() );
			}
			if ( $current_screen->base == 'post' && $current_screen->post_type == 'political-video' ) {
				// Videos admin
				wp_enqueue_script( 'political-options-script', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/js/political-options.js', array( 'jquery' ), '', true );
			}
			if ( $current_screen->base == 'political-issue_page_political-options-donations' ) {
				// Donations admin
				wp_enqueue_script( 'political-options-script', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/js/political-options.js', array( 'jquery' ), '', true );
				wp_enqueue_script( 'political-options-donation-script', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/js/political-options-donation.js', array( 'jquery' ), '', true );
			}
			if ( $current_screen->base == 'political-issue_page_political-options-settings' ) {
				// Settings admin
				wp_enqueue_script( 'political-options-script', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/js/political-options.js', array( 'jquery' ), '', true );
				wp_enqueue_script( 'political-options-settings-script', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/js/political-options-settings.js', array( 'jquery' ), '', true );
			}

			// CSS
			if ( $current_screen->base == 'post' && $current_screen->post_type == 'political-event' ) {
				wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
				wp_enqueue_style( 'political-options-event-style', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/css/political-options-event.css' );
			}
			if ( $current_screen->base == 'post' && $current_screen->post_type == 'political-video' ) {
				wp_enqueue_style( 'political-options-video-style', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/css/political-options-video.css' );
			}
		}
	}


	private function setup_constants() {

		// Plugin version
		if ( ! defined( 'POLITICAL_OPTIONS_VERSION' ) ) {
			define( 'POLITICAL_OPTIONS_VERSION', '1.0.0' );
		}

		// Plugin Folder Path
		if ( ! defined( 'POLITICAL_OPTIONS_PLUGIN_DIR' ) ) {
			define( 'POLITICAL_OPTIONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'POLITICAL_OPTIONS_PLUGIN_URL' ) ) {
			define( 'POLITICAL_OPTIONS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'POLITICAL_OPTIONS_PLUGIN_FILE' ) ) {
			define( 'POLITICAL_OPTIONS_PLUGIN_FILE', __FILE__ );
		}

	}

	/**
	 * Load our language files
	 *
	 * @access public
	 * @return void
	 */
	public function load_languages() {
		// Set unique textdomain string
		$textdomain = 'political-options';

		// The 'plugin_locale' filter is also used by default in load_plugin_textdomain()
		$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

		// Set filter for WordPress languages directory
		$wp_lang_dir = apply_filters( 'political_options_wp_lang_dir', WP_LANG_DIR . '/political-options/' . $textdomain . '-' . $locale . '.mo' );

		// Translations: First, look in WordPress' "languages" folder
		load_textdomain( $textdomain, $wp_lang_dir );

		// Translations: Next, look in plugin's "lang" folder (default)
		$plugin_dir = basename( dirname( __FILE__ ) );
		$lang_dir = apply_filters( 'political_options_wp_lang_dir', $plugin_dir . '/languages/' );
		load_plugin_textdomain( $textdomain, FALSE, $lang_dir );
	}

	public function register_post_types() {

		$this->register_issues_cpt();
		$this->register_events_cpt();
		$this->register_videos_cpt();
	}

	public function register_issues_cpt() {

		$rewrite_slug = 'issues';

		// custom rewrite slug
		if ( isset( $this->settings['slug_issues'] ) && ! empty( $this->settings['slug_issues'] ) ) {
			$rewrite_slug = $this->settings['slug_issues'];
		}

		$labels = array(
			'name' 				=> _x( 'Issues', 'post type general name', 'political-options' ),
			'singular_name' 	=> _x( 'Issue', 'post type singular name', 'political-options' ),
			'add_new' 			=> __( 'Add New Issue', 'political-options' ),
			'add_new_item' 		=> __( 'Add New Issue', 'political-options' ),
			'edit_item' 		=> __( 'Edit Issue', 'political-options' ),
			'new_item' 			=> __( 'New Issue', 'political-options' ),
			'all_items' 		=> __( 'Issues', 'political-options' ),
			'view_item' 		=> __( 'View Issue', 'political-options' ),
			'search_items' 		=> __( 'Search Issue', 'political-options' ),
			'not_found' 		=> __( 'No Issue found', 'political-options' ),
			'not_found_in_trash'=> __( 'No Issue found in Trash', 'political-options' ),
			'parent_item_colon' => '',
			'menu_name' 		=> __( 'Political Options', 'political-options' )
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'query_var'           => true, // $rewrite_slug,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-groups',
			'menu_position'       => null,
			'rewrite'             => array('slug' => $rewrite_slug /*, 'hierarchical' => false, 'with_front' => true*/ ),
			'supports'            => array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail', 'tags', 'page-attributes' ),
			'taxonomies' 		  => array( 'political-category', 'post_tag' )
		);

		register_post_type( 'political-issue', $args );

		$this->flush_rewrite_rules();
	}

	public function register_events_cpt() {

		$rewrite_slug = 'events';

		// custom rewrite slug
		if ( isset( $this->settings['slug_events'] ) && ! empty( $this->settings['slug_events'] ) ) {
			$rewrite_slug = $this->settings['slug_events'];
		}

		$labels = array(
			'name' 				=> _x( 'Events', 'post type general name', 'political-options' ),
			'singular_name' 	=> _x( 'Event', 'post type singular name', 'political-options' ),
			'add_new' 			=> __( 'Add New Event', 'political-options' ),
			'add_new_item' 		=> __( 'Add New Event', 'political-options' ),
			'edit_item' 		=> __( 'Edit Event', 'political-options' ),
			'new_item' 			=> __( 'New Event', 'political-options' ),
			'all_items' 		=> __( 'Events', 'political-options' ),
			'view_item' 		=> __( 'View Event', 'political-options' ),
			'search_items' 		=> __( 'Search Event', 'political-options' ),
			'not_found' 		=> __( 'No Event found', 'political-options' ),
			'not_found_in_trash'=> __( 'No Event found in Trash', 'political-options' ),
			'parent_item_colon' => '',
			'menu_name' 		=> __( 'Events Hidden', 'political-options' )
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'query_var'           => true, // $rewrite_slug,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-groups',
			//'menu_position'       => null,
			'show_in_menu'        => 'edit.php?post_type=political-issue',
			'rewrite'             => array( 'slug' => $rewrite_slug /*, 'hierarchical' => false, 'with_front' => true*/ ),
			'supports'            => array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail' ),
			'taxonomies' 		  => array( 'political-category', 'post_tag' )
		);

		register_post_type( 'political-event', $args );

		$this->flush_rewrite_rules();
	}

	public function register_videos_cpt() {

		$rewrite_slug = 'videos';

		// custom rewrite slug
		if ( isset( $this->settings['slug_videos'] ) && ! empty($this->settings['slug_videos'] ) ) {
			$rewrite_slug = $this->settings['slug_videos'];
		}

		$labels = array(
			'name' 				=> _x( 'Videos', 'post type general name', 'political-options' ),
			'singular_name' 	=> _x( 'Video', 'post type singular name', 'political-options' ),
			'add_new' 			=> __( 'Add New Video', 'political-options' ),
			'add_new_item' 		=> __( 'Add New Video', 'political-options' ),
			'edit_item' 		=> __( 'Edit Video', 'political-options' ),
			'new_item' 			=> __( 'New Video', 'political-options' ),
			'all_items' 		=> __( 'Videos', 'political-options' ),
			'view_item' 		=> __( 'View Video', 'political-options' ),
			'search_items' 		=> __( 'Search Video', 'political-options' ),
			'not_found' 		=> __( 'No Video found', 'political-options' ),
			'not_found_in_trash'=> __( 'No Video found in Trash', 'political-options' ),
			'parent_item_colon' => '',
			'menu_name' 		=> __( 'Videos Hidden', 'political-options' )
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'query_var'           => true, // $rewrite_slug,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-groups',
			//'menu_position'       => null,
			'show_in_menu'        => 'edit.php?post_type=political-issue',
			'rewrite'             => array( 'slug' => $rewrite_slug /*, 'hierarchical' => false, 'with_front' => true*/ ),
			'supports'            => array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail', 'tags' ),
			'taxonomies' 		  => array( 'political-category', 'post_tag' )
		);

		register_post_type( 'political-video', $args );

		$this->flush_rewrite_rules();
	}

	public function register_taxonomies() {

		$labels = array(
			'name' 							=> __( 'Categories', 'political-options' ),
			'singular_name' 				=> __( 'Category', 'political-options' ),
			'search_items' 					=> __( 'Search Categories', 'political-options' ),
			'popular_items' 				=> __( 'Popular Categories', 'political-options' ),
			'all_items' 					=> __( 'All Categories', 'political-options' ),
			'edit_item' 					=> __( 'Edit Category', 'political-options' ),
			'update_item' 					=> __( 'Update Category', 'political-options' ),
			'add_new_item' 					=> __( 'Add New Category', 'political-options' ),
			'new_item_name' 				=> __( 'New Category Name', 'political-options' ),
			'separate_items_with_commas' 	=> __( 'Separate categories with commas', 'political-options' ),
			'add_or_remove_items' 			=> __( 'Add or remove categories', 'political-options' ),
			'choose_from_most_used' 		=> __( 'Choose from the most frequent Categories', 'political-options' ),
		);

		register_taxonomy(
			'political-category',
			'political-issue',
			array(
				'hierarchical' 	=> true,
				'labels' 		=> $labels,
				'show_ui'		=> true,
				'query_var' 	=> true,
				'rewrite' 		=> array( 'slug' => '','hierarchical' => true,  'with_front'=> true )
			)
		);
	}

	/**
	 * Flush Rewrite Rules?
	 */
	function flush_rewrite_rules() {
		global $wp_rewrite;

		// Have permalink settings changed?
		if( isset( $this->settings['rewrite_flush_rules'] ) && $this->settings['rewrite_flush_rules'] ) {
			$wp_rewrite->flush_rules();
			$this->settings['rewrite_flush_rules'] = 0;
			if ( defined( 'JSON_UNESCAPED_UNICODE' ) ) {
				update_option( 'political_options_settings', json_encode( $this->settings, JSON_UNESCAPED_UNICODE ) );
			} else {
				update_option( 'political_options_settings', unescaped_json( $this->settings ) );
			}
		}
	}

	function change_political_options_menu() {
		global $menu, $submenu;

		add_submenu_page( 'edit.php?post_type=political-issue', __( 'Newsletter', 'political-options' ), __( 'Newsletter', 'political-options' ), 'manage_options', 'political-options-newsletter', array( $this, 'newsletter_page' ) );
		add_submenu_page( 'edit.php?post_type=political-issue', __( 'Donations', 'political-options' ), __( 'Donations', 'political-options' ), 'manage_options', 'political-options-donations', array( $this, 'donation_page' ) );
		add_submenu_page( 'edit.php?post_type=political-issue', __( 'Settings', 'political-options' ), __( 'Settings', 'political-options' ), 'manage_options', 'political-options-settings', array( $this, 'settings_page' ) );

		if ( isset( $submenu['edit.php?post_type=political-issue'] ) ) {
			$new_political_submenu = array();
			foreach( $submenu['edit.php?post_type=political-issue'] as $mkey => $m ) {
				if( strpos( $m[2], 'edit-tags.php?taxonomy=post_tag' ) !== false )
					continue;
				switch ( $m[2] ) {
					case 'edit.php?post_type=political-event':
					   $new_political_submenu[] = $m;
					   $new_political_submenu[] = array( 0 => __( 'Add New Event', 'political-options' ), 1 => 'edit_posts', 2 => 'post-new.php?post_type=political-event' );
						break;

					case 'edit.php?post_type=political-video':
						$new_political_submenu[] = $m;
						$new_political_submenu[] = array(0 => __( 'Add New Video', 'political-options' ), 1 => 'edit_posts', 2 => 'post-new.php?post_type=political-video' );
						break;

					default:
						$new_political_submenu[] = $m;
						break;
				}
			}
			$submenu['edit.php?post_type=political-issue'] = $new_political_submenu;
		}
	}

	function add_meta_box_after_title( $post ) {
		global $wp_meta_boxes, $_wp_post_type_features;

		if( $post->post_type == 'political-event' ) {
			do_meta_boxes( 'political-event', 'normal', '' );
			unset( $wp_meta_boxes[get_post_type( $post )]['normal'] );
		}
	}

	function add_meta_boxes_function() {
		global $post;

		add_meta_box(
			 'event_details',
			__( 'Event Details', 'political-options' ),
			array( $this, 'render_meta_box_event_details' ),
			'political-event',
			'normal',
			'high'
		);

		add_meta_box(
			 'event_notes',
			__( 'Post Event Notes', 'political-options' ),
			array( $this, 'render_meta_box_event_notes' ),
			'political-event',
			'advanced',
			'high'
		);

		add_meta_box(
			 'event_photos',
			__( 'Event Photos', 'political-options' ),
			array( $this, 'render_meta_box_event_photos' ),
			'political-event',
			'advanced',
			'high'
		);

		add_meta_box(
			 'video_options',
			__( 'Video', 'political-options' ),
			array( $this, 'render_meta_box_video_options' ),
			'political-video',
			'normal',
			'high'
		);
	}

	function manage_event_columns( $columns ) {
		return array( 'cb' 			=> '<input type="checkbox" />',
					  'title' 		=> __( 'Title', 'political-options' ),
					  'start_date' 	=> __( 'Start Date', 'political-options' ),
					  'end_date' 	=> __( 'End Date', 'political-options' ),
		);
	}

	function manage_event_custom_columns( $column, $post_id ) {
		$start = get_post_meta( $post_id, 'political-event-start' );
		$end = get_post_meta( $post_id, 'political-event-end' );

		switch ( $column ) {

			case 'start_date' :
				$dt_start = new DateTime();
				$dt_start->setTimestamp( $start[0] );
				$date_start = $dt_start->format( get_option( ' date_format' ).' '.get_option( ' time_format' ) );
				echo $date_start;

				break;

			case 'end_date' :
				$dt_end = new DateTime();
				$dt_end->setTimestamp( $end[0] );
				$date_end = $dt_end->format( get_option( ' date_format' ).' '.get_option( ' time_format' ) );
				echo $date_end;
				break;
		}
	}

	function add_event_filtering() {
		global $current_screen;

		if ( $current_screen->post_type == 'political-event' ) {

			$sort_selected = isset( $_GET['event_sort'] ) ? $_GET['event_sort'] : 'political-event-start' ;

			echo '<select name="event_sort">';
			printf( '<option value="%s"%s>%s</option>', 'political-event-start', selected( 'political-event-start', $sort_selected, false ), __( 'Start Date', 'political-options' ) );
			printf( '<option value="%s"%s>%s</option>', 'political-event-end', selected( 'political-event-end', $sort_selected, false ), __( 'End Date', 'political-options' ) );
			echo '</select>';
		}
	}

	function manage_video_columns( $columns ) {
		$new_columns = array();
		foreach( $columns as $key => $val ) {
			if( $key == 'tags' )
				$new_columns['author'] = __( 'Author', 'political-options' );
			else
				$new_columns[$key] = $val;
		}

		return $new_columns;
	}

	function change_query_after_filtering( $query ) {
		global $pagenow;

		if ( is_admin() && $pagenow == 'edit.php' && $query->query_vars['post_type'] == 'political-event' ) {
			$meta_name = ( isset( $_GET['event_sort'] ) && ! empty( $_GET['event_sort'] ) ) ? $_GET['event_sort'] : 'political-event-start' ;
			$query->set( 'meta_key', $meta_name );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
		}
	}

	public function get_time_intervals( $ampm, $time_format ) {
		$time_values = array(
			0 => array( '0:01' => __( 'All Day', 'political-options' ) )
		);
		$intervals = array( '00', '15', '30', '45' );
		$hours = ! empty( $ampm ) ? 12 : 24;
		for( $i = 1; $i <= $hours; $i++ ) {
			foreach( $intervals as $val ) {
				$time_values[0][date( $time_format, strtotime($i.':'.$val) )] = date( $time_format, strtotime($i.':'.$val) );
			}
		}
		switch ( $ampm ) {
			case 'a':
				$time_values[1] = array( 'am' => 'am', 'pm' => 'pm' );
				break;
			case 'A':
				$time_values[1] = array( 'AM' => 'AM', 'PM' => 'PM' );
				break;			
			default:
				$time_values[1] = array();
				break;
		}

		return $time_values;
	}

	public function render_meta_box_event_details() {
		global $post;

		$start = get_post_meta( $post->ID, 'political-event-start' );
		$start = empty( $start ) ? time() : $start[0];

		$end = get_post_meta( $post->ID, 'political-event-end' );
		$end = empty( $end ) ? time() : $end[0];

		$timezone = get_post_meta( $post->ID, 'political-event-timezone' );
		$timezone = empty( $timezone ) ? '' : $timezone[0];

		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		$ampm = ( strpos( $time_format, 'a' ) !== false ) ? 'a' : ( ( strpos( $time_format, 'A' ) !== false ) ? 'A' : '' );
		$time_format = str_replace( array( 'a', 'A' ), array( '' , '' ), $time_format );

		// Start: date/time with timezone
		$dt_start = new DateTime();
		$dt_start->setTimestamp( $start );
		$date_start_default = $dt_start->format( 'm/d/Y' );

		$date_start = date_i18n( $date_format, $start );
		$time_start = $dt_start->format( $time_format );
		$ampm_start = $dt_start->format( $ampm );

//		$date_start = $dt_start->format( $date_format );
//		$time_start = $dt_start->format( 'g:i' );
//		$ampm_start = $dt_start->format( 'A' );

		// End: date/time with timezone
		$dt_end = new DateTime();
		$dt_end->setTimestamp( $end );
		$date_end_default = $dt_end->format( 'm/d/Y' );

		$date_end = date_i18n( $date_format, $end );
		$time_end = $dt_end->format( $time_format );
		$ampm_end = $dt_end->format( $ampm );

//		$date_end = $dt_end->format( $date_format );
//		$time_end = $dt_end->format( 'g:i' );
//		$ampm_end = $dt_end->format( 'A' );

		$time_intervals = $this->get_time_intervals( $ampm, $time_format );

		$meta = get_post_meta( $post->ID, 'political-event-details' );
		$details = isset( $meta[0] ) ? json_decode( $meta[0] ) : '';

		wp_nonce_field( basename( __FILE__ ) , 'political-event-details-nonce' );
		?>
		<p>

			<table class="form-table" >
				<colgroup>
				   <col span="1" style="width: 12%;">
				   <col span="1" style="width: 12%;">
				   <col span="1" style="width: 8%;">
				   <col span="1" style="width: 68%;">
				</colgroup>
				<tbody>
					<tr>
						<td>
							<label for="event_date_start"><?php _e( 'Start', 'political-options' ); ?></label>
						</td>
						<td>
							<input class="political-options-datepicker" type="text" name="event_date_start" value="<?php echo ( isset( $date_start ) ? $date_start : '' ); ?>" />
							<input type="hidden" name="event_date_start_default" value="<?php echo $date_start_default; ?>" />
						</td>
						<td>
							<select name="event_time_start">
								<?php
								if ( ! empty( $time_intervals[0] ) ) {
									foreach ( $time_intervals[0] as $key => $val ) {
										printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $key, $time_start, false ), esc_html( $val ) );
									}
								} ?>
							</select>
						</td>
						<td>
							<?php if ( ! empty( $time_intervals[1] ) ): ?>
										<select name="event_ampm_start">
											<?php foreach ( $time_intervals[1] as $key => $val ):
														printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $key, $ampm_start, false ), esc_html( $val ) );
												  endforeach;
											?>
										</select>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="event_date_end"><?php _e( 'End', 'political-options' ); ?></label>
						</td>
						<td>
							<input class="political-options-datepicker" type="text" name="event_date_end" value="<?php echo ( isset( $date_end ) ? $date_end : '' ); ?>" />
							<input type="hidden" name="event_date_end_default" value="<?php echo $date_end_default; ?>" />
						</td>
						<td>
							<select name="event_time_end">
								<?php
								if ( ! empty( $time_intervals[0] ) ) {
									foreach ( $time_intervals[0] as $key => $val ) {
										printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $key, $time_end, false ), esc_html( $val ) );
									}
								} ?>
							</select>
						</td>
						<td>
							<?php if ( ! empty( $time_intervals[1] ) ): ?>
										<select name="event_ampm_end">
											<?php foreach ( $time_intervals[1] as $key => $val ):
														printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $key, $ampm_end, false ), esc_html( $val ) );
												  endforeach;
											?>
										</select>
							<?php endif; ?>
							<span class="event-wrong-dates"></span>
						</td>
					</tr>
					<tr>
						<td>
							<label for="event_timezone"><?php _e( 'Timezone', 'political-options' ); ?></label>
						</td>
						<td>
							<input class="widefat" type="text" name="event_timezone" id="event_timezone" value="<?php echo ( isset( $timezone ) ? $timezone : '' ); ?>" />
							<p class="description"></p>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="form-table" >
				<colgroup>
				   <col span="1" style="width: 10%;">
				   <col span="1" style="width: 90%;">
				</colgroup>
				<tbody>
					<tr>
						<td class="label-meta-box">
							<label for="event_location"><?php _e( 'Location', 'political-options' ); ?></label>
							<p></p>
						</td>
						<td>
							<input class="widefat" type="text" name="event_location" id="event_location" value="<?php echo ( isset( $details->location ) ? $details->location : '' ); ?>" />
							<p class="description"><?php _e( 'The address to display in the event details.', 'political-options' ) ?></p>
						</td>
					</tr>
					<tr>
						<td class="label-meta-box">
							<label for="event_directions"><?php _e( 'Directions', 'political-options' ); ?></label>
						</td>
						<td>
							<input class="widefat" type="text" name="event_directions" value="<?php echo ( isset( $details->directions ) ? $details->directions : '' ); ?>" />
							<p class="description"><?php _e( 'Google (or other) Map URL.', 'political-options' ) ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</p><?php
	}

	public function render_meta_box_event_notes() {
		global $post;

		$meta = get_post_meta( $post->ID, 'political-event-notes' );
		$notes = ! empty( $meta[0] ) ? $meta[0] : '';
		echo '<p><label><span>' . __( 'Information to show after the event has concluded.', 'political-options' ) . '</span></label></p>';
		echo '<textarea name="event_notes" class="settings-textarea widefat" rows=5>' . esc_textarea( $notes ) . '</textarea>';
	}

	public function render_meta_box_event_photos() {
		global $post; ?>

		<div id="cfpf-format-gallery-preview" class="cf-elm-block cf-elm-block-image">
			<p><label><span><?php _e( 'Select photos taken at the event', 'political-options' ); ?></span></label></p>
			<div class="cf-elm-container">
				<div id="post-format-gallery-items">
				<?php
					// running this in the view so it can be used by multiple functions
					$ids = esc_attr( get_post_meta( $post->ID, 'political-events-gallery-ids', true ) );
					$attachments = get_posts( array(
										        'post__in' 		=> explode( ",", $ids ),
									            'orderby' 		=> 'post__in',
									            'post_type' 	=> 'attachment',
									            'post_mime_type'=> 'image',
									            'post_status' 	=> 'any',
									            'numberposts' 	=> -1
					        ));
					if ( $attachments ) {
						echo '<ul class="gallery">';
						foreach ( $attachments as $attachment ) {
							echo '<li>'.wp_get_attachment_image( $attachment->ID, 'thumbnail' ).'</li>';
						}
						echo '</ul>';
					}
				?>
				</div>
				<p class="none"><a href="#" class="button"><?php ( $ids ) ? _e( 'Edit Gallery', 'political-options' ) : _e( 'Add Images to Gallery', 'political-options' ); ?></a></p>
				<input type="hidden" name="postformat_gallery_ids" value="<?php echo esc_attr( get_post_meta( $post->ID, 'political-events-gallery-ids', true ) ); ?>" id="cfpf-format-gallery-ids-field">
				<input type="hidden" name="_format_gallery_nonce" value="<?php echo wp_create_nonce( 'do_ajax' ); ?>" id="cfpf-format-gallery-nonce-field" >
			</div>
		</div> <?php
	}

	public function render_meta_box_video_options() {
		global $post;

		$meta = get_post_meta( $post->ID, 'political-video-options' );
		$videos = isset( $meta[0] ) ? json_decode( $meta[0] ) : '';

		wp_nonce_field( basename( __FILE__ ) , 'political-video-options-nonce' ); ?>

		<table class="form-table" >
			<!-- <colgroup>
			   <col span="1" style="width: 20%;">
			   <col span="1" style="width: 80%;">
			</colgroup> -->
			<tbody>
				<!-- <tr>
					<td class="label-meta-box">
						<label for="video_url"><?php _e('Video URL', 'political-options'); ?></label>
					</td>
					<td>
						<textarea name="video_url" class="settings-textarea widefat" rows=5><?php echo isset($videos->video_url) ? esc_textarea($videos->video_url) : ''; ?></textarea>
						<p class="description"><?php _e('Enter a URL for a YouTube, Vimeo, etc.<br>If not using self hosted video embedded code can be entered here.', 'political-options') ?></p>
					</td>
				</tr>
				<tr>
					<td class="label-meta-box">
						<label for="webm_url"><?php _e('WEBM File URL', 'political-options'); ?></label>
					</td>
					<td>
						<input class="widefat" type="text" name="webm_url" id="webm_url" value="<?php echo isset($videos->webm_url) ? $videos->webm_url : ''; ?>" />
						<p class="description"><?php _e('The URL to the .webm videp file', 'political-options') ?></p>
					</td>
				</tr>
				<tr>
					<td class="label-meta-box">
						<label for="ogv_url"><?php _e('OGV File URL', 'political-options'); ?></label>
					</td>
					<td>
						<input class="widefat" type="text" name="ogv_url" id="m4v_url" value="<?php echo isset($videos->ogv_url) ? $videos->ogv_url : ''; ?>" />
						<p class="description"><?php _e('The URL to the .ogv video file', 'political-options') ?></p>
					</td>
				</tr>
				<tr>
					<td class="label-meta-box">
						<label for="m4v_url"><?php _e('M4V ile URL', 'political-options'); ?></label>
					</td>
					<td>
						<input class="widefat" type="text" name="m4v_url" id="m4v_url" value="<?php echo isset($videos->m4v_url) ? $videos->m4v_url : ''; ?>" />
						<p class="description"><?php _e('The URL to the .m4v video file', 'political-options') ?></p>
					</td>
				</tr> -->
				<tr>
					<td class="label-meta-box">
						<label for="youtube_id"><?php _e( 'YouTube Video ID', 'political-options' ); ?></label>
					</td>
					<td>
						<input class="widefat" type="text" name="youtube_id" id="youtube_id" value="<?php echo isset( $videos->youtube_id ) ? $videos->youtube_id : ''; ?>" />
						<p class="description"><?php printf( __( 'The YouTube video ID. This can be found in the URL, for example: %s From the URL: %s', 'political-options' ), '<code>re0VRK6ouwI</code><br>', 'https://www.youtube.com/watch?v=<code>re0VRK6ouwI</code>' ) ?></p>
					</td>
				</tr>
			</tbody>
		</table> <?php
	}

	public function save_meta_box_data( $post_id ) {
		global $post;

		if ( get_post_type() == 'political-event' ) {
			if ( ! isset( $_POST['political-event-details-nonce'] ) || ! wp_verify_nonce( $_POST['political-event-details-nonce'], basename( __FILE__ ) ) ) {
				return $post_id;
			}

			$details = array();

//			$format = get_option('date_format') . ' g:i A';
			$format = get_option('date_format') . ' ' . get_option('time_format');
			$format_default = 'm/d/Y '.get_option('time_format');

			// $timezone = new DateTimeZone(get_option('timezone_string'));
			if( isset( $_POST['event_date_start'] ) && ! empty( $_POST['event_date_start'] ) ) {
				$all_day = ( $_POST['event_time_start'] == '0:01' ) ? true : false;
				$am_pm_start = ! isset( $_POST['event_ampm_start'] ) ? '' : ( $all_day ? 'AM' : $_POST['event_ampm_start'] );
				//$dt = ! empty( $am_pm_start ) ? $_POST['event_date_start'].' '.$_POST['event_time_start'].' '.$am_pm_start : $_POST['event_date_start'].' '.$_POST['event_time_start'];
				$dt = ! empty( $am_pm_start ) ? $_POST['event_date_start_default'].' '.$_POST['event_time_start'].' '.$am_pm_start : $_POST['event_date_start_default'].' '.$_POST['event_time_start'];

				$dt_start = DateTime::createFromFormat( $format_default, $dt );
				//$dt_start = DateTime::createFromFormat( $format, $dt );

				update_post_meta( $post_id, 'political-event-start', $dt_start->getTimestamp() );
			}

			if( isset( $_POST['event_date_end']) && ! empty($_POST['event_date_end'] ) ) {
				$all_day = ( $_POST['event_time_end'] == '0:01' ) ? true : false;
				$am_pm_end = ! isset( $_POST['event_ampm_end'] ) ? '' : ( $all_day ? 'AM' : $_POST['event_ampm_end'] );
				//$dt = ! empty( $am_pm_end ) ? $_POST['event_date_end'].' '.$_POST['event_time_end'].' '.$am_pm_end : $_POST['event_date_end'].' '.$_POST['event_time_end'];
				$dt = ! empty( $am_pm_end ) ? $_POST['event_date_end_default'].' '.$_POST['event_time_end'].' '.$am_pm_end : $_POST['event_date_end_default'].' '.$_POST['event_time_end'];

				$dt_end = DateTime::createFromFormat( $format_default, $dt );

				update_post_meta( $post_id, 'political-event-end', $dt_end->getTimestamp() );
			} else {
				if( isset( $all_day ) && $all_day ) {
					update_post_meta( $post_id, 'political-event-end', $dt_start->getTimestamp() + 60*60*24 - 61 );
				}
			}
			if( isset( $_POST['event_timezone'] ) ) {
				update_post_meta( $post_id, 'political-event-timezone', $_POST['event_timezone'] );
			}

			$details['location'] = ( isset($_POST['event_location'] ) && ! empty( $_POST['event_location'] ) ) ? $_POST['event_location'] : '';
			$details['directions'] = ( isset($_POST['event_directions'] ) && ! empty( $_POST['event_directions'] ) ) ? $_POST['event_directions'] : '';
			if ( defined( 'JSON_UNESCAPED_UNICODE' ) ) {
				update_post_meta( $post_id, 'political-event-details', json_encode( $details, JSON_UNESCAPED_UNICODE ) );
			} else {
				update_post_meta( $post_id, 'political-event-details', unescaped_json( $details ) );
			}

			if( isset( $_POST['event_notes'] ) ) {
				$notes = wp_kses_post( $_POST['event_notes'] );
				update_post_meta( $post_id, 'political-event-notes', $notes );
			}

			if ( isset( $_POST['postformat_gallery_ids'] ) )
				update_post_meta( $post_id, 'political-events-gallery-ids', $_POST['postformat_gallery_ids'] );
		}

		if( get_post_type() == 'political-video' ) {
			if ( ! isset( $_POST['political-video-options-nonce'] ) || ! wp_verify_nonce( $_POST['political-video-options-nonce'], basename( __FILE__ ) ) ) {
				return $post_id;
			}

			$video_urls = array();
			$video_urls['video_url'] = ( isset( $_POST['video_url'] ) && ! empty( $_POST['video_url'] ) ) ? wp_kses_post( $_POST['video_url'] ) : '';
			$video_urls['webm_url'] = ( isset( $_POST['webm_url'] ) && ! empty( $_POST['webm_url'] ) ) ? $_POST['webm_url'] : '';
			$video_urls['ogv_url'] = ( isset( $_POST['ogv_url'] ) && ! empty( $_POST['ogv_url'] ) ) ? $_POST['ogv_url'] : '';
			$video_urls['m4v_url'] = ( isset( $_POST['m4v_url'] ) && ! empty( $_POST['m4v_url'] ) ) ? $_POST['m4v_url'] : '';
			$video_urls['youtube_id'] = ( isset( $_POST['youtube_id'] ) && ! empty( $_POST['youtube_id'] ) ) ? $_POST['youtube_id'] : '';
			update_post_meta( $post_id, 'political-video-options', json_encode( $video_urls ) );
		}
	}

	function newsletter_page() {
		if (
			isset( $_GET['action'] )
			&& $_GET['action'] == 'save-newsletter'
			&& isset( $_POST['newsletter-page-nonce'] )
			&& wp_verify_nonce( $_POST['newsletter-page-nonce'], basename( __FILE__ ) )
		) {
			$data = stripslashes_deep( $_POST );
			$data['ninja_forms_name'] = isset( $data['ninja_forms_name'] ) ? esc_attr( $data['ninja_forms_name'] ) : '';

			// update_option( 'front_runner_newsletter_options', $data['ninja_forms_name'] );
			update_option( 'political_options_newsletter', $data['ninja_forms_name'] );
		}

		$this->display_newsletter_page();
	}

	function display_newsletter_page() {
		$ninja_form_id  = get_option( 'political_options_newsletter' );

		$ninja_forms = array();
		$all_forms = ( function_exists( 'ninja_forms_get_all_forms' ) ) ? ninja_forms_get_all_forms() : array();
		foreach ( $all_forms as $form ) {
			$ninja_forms[$form['id']] = $form['name'];
		}
		$ninja_form_selected = $ninja_form_id; ?>

		<div class="wrap">

			<h1><?php _e( 'Newsletter', 'political-options' ) ?></h1>

			<?php if ( ! Political_Options_NF_PP_Integration::is_ninjaform_installed() ) : ?>

				<p>
					<?php _e( 'This feature requires the free plugin Ninja Forms to be installed. Download the plugin or install directly from your plugins directory.', 'political-options' ); ?>
				</p>
				<p>
					<a href="https://wordpress.org/plugins/ninja-forms/" target="_blank"><?php _e( 'Download Ninja Forms', 'political-options' ); ?></a>
					&nbsp;<?php _e( 'or', 'political-options' ) ?>&nbsp;
					<a href="<?php echo admin_url('plugin-install.php?tab=search&s=ninja+forms') ?>"><?php _e( 'Install Ninja Forms', 'political-options' ); ?></a>
				</p>

			<?php else : ?>

				<p><?php _e( 'Add a simple newsletter sign up to your site. Select a form created with the Ninja Forms plugin as the source for the fields.', 'political-options' ); ?></p>
				<p><?php _e( 'Output with', 'political-options' ); ?>:&nbsp; <code>[political_newsletter]</code></p>

				<div id="poststuff">

					<form action="<?php echo admin_url( 'edit.php?post_type=political-issue&page=political-options-newsletter&action=save-newsletter' ); ?>" method="post">

						<?php wp_nonce_field( basename( __FILE__ ) , 'newsletter-page-nonce' ); ?>
						<div class="postbox" style="max-width: 800px;">
							<h3 class="hndle" style="cursor: default;"><span><?php _e( 'Ninja Forms Newsletter', 'political-options' ); ?></span></h3>
							<div class="inside">
								<table class="form-table" >
									<tbody>
										<tr>
											<th scope="row">
												<label for="ninja_form_name"><?php _e( 'Form name', 'political-options' ); ?></label>
											</th>
											<td>
												<select name="ninja_forms_name" id="ninja_form_name">
													<option value="0"><?php _e( '- Select a form -', 'political-options' ); ?></option>
													<?php
													if ( !empty( $ninja_forms ) ) {
														foreach ( $ninja_forms as $key => $val ) {
															printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $key, $ninja_form_selected, false ), esc_html( $val ) );
														}
													} ?>
												</select>
												<p class="description"><?php _e( 'The form used for Newsletter.', 'political-options' ) ?></p>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<input class="button-primary" type="submit" value="<?php _e( 'Save Changes', 'political-options' ) ?>">

						<p>&nbsp;</p>
						<hr>

						<h4><?php _e( 'Styling', 'political-options' ); ?></h4>
						<p><?php
							_e( 'The newsletter layout and styling output by the shortcode can be modified with CSS. There are also helper classes supported on the Ninja Forms fields. Under the "Advanced" section of a field setting you can use the following custom classes:', 'political-options' );
						?></p>
						<p>
							<code>field-full-width</code> - <?php _e( 'Specify the field to display 100% width.', 'political-options' ); ?> <br>
							<code>field-half-width</code> - <?php _e( 'Set the field to display 50% width. Using this you can have fields appear side by side.', 'political-options' ); ?>
						</p>

						<h4><?php _e( 'Extend and Enhance', 'political-options' ); ?></h4>
						<p><?php
							printf( __( 'Ninja Forms makes it possible to add direct integration with services including MailChimp, Constant Contact, Campaign Monitor and more. To see a full list of add-ons, visit the %sNinja Forms Extensions%s directory.', 'political-options' ), '<a href="https://ninjaforms.com/extensions/" target="_blank">', '</a>' );
						?></p>
					</form>

				</div>

			<?php endif; ?>

		</div> <?php
	}

	function donation_page() {
		if (
			isset( $_GET['action'] )
			&& $_GET['action'] == 'save-donations'
			&& ! empty( $_POST )
			&& isset( $_POST['donation-page-nonce'] )
			&& wp_verify_nonce( $_POST['donation-page-nonce'], basename( __FILE__ ) )
		) {
			$this->donations = stripslashes_deep( $_POST );
			$this->donations['preset_donations'] = isset( $this->donations['preset_donations'] ) ? esc_attr( $this->donations['preset_donations'] ) : '';
			$this->donations['title'] = isset( $this->donations['title'] ) ? esc_attr( $this->donations['title'] ) : '';
			$this->donations['button'] = isset( $this->donations['button'] ) ? esc_attr( $this->donations['button'] ) : '';
			$this->donations['currency_symbol'] = isset( $this->donations['currency_symbol'] ) ? esc_attr( $this->donations['currency_symbol'] ) : '';
			$this->donations['symbol_location'] = isset( $this->donations['symbol_location'] ) ? esc_attr( $this->donations['symbol_location'] ) : 'before';
			$this->donations['paypal_mode'] = isset( $this->donations['paypal_mode'] ) ? esc_attr( $this->donations['paypal_mode'] ) : 'enabled';
			$this->donations['paypal_email'] = isset( $this->donations['paypal_email'] ) ? esc_attr( $this->donations['paypal_email'] ) : '';
			$this->donations['paypal_description'] = isset( $this->donations['paypal_description'] ) ? esc_attr( $this->donations['paypal_description'] ) : '';
			$this->donations['paypal_transaction_type'] = isset( $this->donations['paypal_transaction_type'] ) ? esc_attr( $this->donations['paypal_transaction_type'] ) : 'donation';
			$this->donations['ninja_forms_integration'] = isset( $this->donations['ninja_forms_integration'] ) ? esc_attr( $this->donations['ninja_forms_integration'] ) : 'enabled';

			if ( defined( 'JSON_UNESCAPED_UNICODE' ) ) {
				update_option( 'political_options_donations', json_encode( $this->donations, JSON_UNESCAPED_UNICODE ) );
			} else {
				update_option( 'political_options_donations', unescaped_json( $this->donations ) );
			}
		}
		$this->display_donation_page();
	}

	function get_ninja_fields_by_form_id() {
		$ninja_fields = array();
		if( is_admin() && isset( $_POST['id'] ) && ! empty( $_POST['id'] ) ) {
			$all_fields = ninja_forms_get_fields_by_form_id( $_POST['id'] );
			foreach( $all_fields as $field )
				$ninja_fields[$field['id']] = $field['data']['label'];
		}

		return $ninja_fields;
	}

	function get_ninja_fields_by_form_id_ajax() {
		if ( defined( 'JSON_UNESCAPED_UNICODE' ) ) {
			echo json_encode( $this->get_ninja_fields_by_form_id(), JSON_UNESCAPED_UNICODE );
		} else {
			echo unescaped_json( $this->get_ninja_fields_by_form_id() );
		}
		die();
	}

	function display_donation_page() {

		$ninja_forms = array();

		$all_forms = function_exists( 'ninja_forms_get_all_forms' ) ? ninja_forms_get_all_forms() : array();
		foreach( $all_forms as $form ) {
			$ninja_forms[$form['id']] = $form['name'];
		}
		$ninja_form_selected = ( isset( $this->donations['ninja_form_name'] ) ) ? $this->donations['ninja_form_name'] : '';

		$ninja_pages = array();
		$all_pages = get_pages();
		foreach( $all_pages as $page ) {
			$ninja_pages[$page->ID] = $page->post_title;
		}
		$ninja_page_selected = $this->donations['ninja_donation_page'];

		?>

		<div class="wrap">

			<h1><?php _e( 'Donations', 'political-options' ) ?></h1>
			<p><?php _e( 'Settings for the Quick Donate feature. To use Quick Donate, add the shortcode to any content area of your site.', 'political-options' ); ?></p>

			<div id="poststuff">
				<form action="<?php echo admin_url( 'edit.php?post_type=political-issue&page=political-options-donations&action=save-donations' ); ?>" method="post">
					<?php wp_nonce_field( basename( __FILE__ ) , 'donation-page-nonce' ); ?>
					<table class="form-table" style="max-width: 800px;">
						<thead class="image-header">
						<p><?php _e( 'Output with', 'political-options' ) ?>:&nbsp; <code>[political_quick_donate]</code></p>
						</thead>
						<tbody>
							<tr>
								<th scope="row">
									<label for="preset_donations"><?php _e( 'Preset Donations', 'political-options' ); ?></label>
								</th>
								<td>
									<input class="regular-text" type="text" name="preset_donations" id="preset_donations" value="<?php echo isset( $this->donations['preset_donations'] ) ? $this->donations['preset_donations'] : '' ; ?>">
									<p class="description">
										<?php _e( 'Comma separated values for suggested donation values.', 'political-options' ) ?><br>
										<?php _e( 'Specify defaults with an asterisk, e.g. <code>5,15,*25,100</code>', 'political-options' ) ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="currency_symbol"><?php _e( 'Currency Symbol', 'political-options' ); ?></label>
								</th>
								<td>
									<select name="currency_symbol" id="currency_symbol">
										<option value="">&nbsp;</option>
										<?php
										$currency = pol_get_currency_symbols();
										$currency_selected = ( isset( $this->donations['currency_symbol'] ) ) ? $this->donations['currency_symbol'] : 'USD'; // default: USD
										if ( ! empty( $currency ) ) {
											foreach ( $currency as $key => $val ) {
												printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $key, $currency_selected, false ), esc_html( $key .' &ndash; '. $val ) );
											}
										} ?>
									</select>
									<p class="description"><?php _e( 'The currency symbol to be used.', 'political-options' ) ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="symbol_location"><?php _e( 'Symbol Location', 'political-options' ); ?></label>
								</th>
								<td>
									<p><input type="radio" name="symbol_location" <?php echo ( ! isset($this->donations['symbol_location'] ) || $this->donations['symbol_location'] == 'before' ) ? 'checked' : ''; ?> value="before"><?php _e( ' Before', 'political-options' ); ?></p>
		   							<p><input type="radio" name="symbol_location" <?php echo ( isset($this->donations['symbol_location'] ) && $this->donations['symbol_location'] == 'after') ? 'checked' : ''; ?> value="after"><?php _e( ' After', 'political-options' ); ?></p>
									<p class="description"><?php _e( 'Show currency symbol before or after value', 'political-options' ) ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="symbol_location"><?php _e( 'Title', 'political-options' ); ?></label>
								</th>
								<td>
									<input class="regular-text" type="text" name="title" id="title" value="<?php echo isset( $this->donations['title'] ) ? $this->donations['title'] : '' ; ?>">
									<p class="description"><?php _e( 'The title to show in the donate container.', 'political-options' ) ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="symbol_location"><?php _e( 'Button Text', 'political-options' ); ?></label>
								</th>
								<td>
									<input class="regular-text" type="text" name="button" id="button" value="<?php echo isset( $this->donations['button'] ) ? $this->donations['button'] : '' ; ?>">
									<p class="description"><?php _e( 'The text to show on the donate submit button.', 'political-options' ) ?></p>
								</td>
							</tr>
						</tbody>
					</table>

					<div class="postbox" style="max-width: 800px;">
						<h3 class="hndle" style="cursor: default;"><span><?php _e( 'Ninja Forms Integration', 'political-options' ); ?></span></h3>
						<div class="inside">
							<table class="form-table" >
								<thead class="image-header">
									<p><?php _e( 'Collect details from donors before they make a donation. This feature requires Ninja Forms plugin to be installed', 'political-options' ); ?></p>
								</thead>
								<tbody>
									<tr>
										<th scope="row">
											<label for="ninja_forms_integration"><?php _e( 'Ninja Forms Integration', 'political-options' ); ?></label>
										</th>
										<td>
											<p><input type="radio" name="ninja_forms_integration" class="ninja_forms_integration" <?php echo ( ! isset( $this->donations['ninja_forms_integration'] ) || $this->donations['ninja_forms_integration'] == 'enabled' ) ? 'checked' : ''; ?> value="enabled"><?php _e( 'Enabled', 'political-options' ); ?></p>
				   							<p><input type="radio" name="ninja_forms_integration" class="ninja_forms_integration" <?php echo ( isset( $this->donations['ninja_forms_integration'] ) && $this->donations['ninja_forms_integration'] == 'disabled' ) ? 'checked' : ''; ?> value="disabled"><?php _e( 'Disabled', 'political-options' ); ?></p>
										</td>
									</tr>
								</tbody>
							</table>
							<div id="ninja-forms-integration-area">
								<table class="form-table" >
									<tbody>
										<tr>
											<th scope="row">
												<label for="ninja_transaction_type"><?php _e( 'Form Page', 'political-options' ); ?></label>
											</th>
											<td>
												<select name="ninja_donation_page" id="ninja_donation_page">
													<option value="0"><?php _e( '- Select a page -', 'political-options' ); ?></option>
													<?php
													if ( ! empty( $ninja_pages ) ) {
														foreach ( $ninja_pages as $key => $val ) {
															printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $key, $ninja_page_selected, false ), esc_html( $val ) );
														}
													} ?>
												</select>
												<p class="description"><?php _e( 'Select the page where this form is in use. This is where the "Quick Donate" tool will take the user to complete the form. For this feature to work you must add the Ninja Form to a page and select that page here.', 'political-options' ) ?></p>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<?php if ( class_exists('NF_Stripe') ) : ?>
					
					<div id="message" class="updated"><p><?php _e( 'PayPal integration is currently disabled. To enable, please deactivate your Stripe plugin.', 'political-options' ); ?></p></div>
					
					<?php else :?>

					<div class="postbox" style="max-width: 800px;">
						<h3 class="hndle" style="cursor: default;"><span><?php _e( 'PayPal Details', 'political-options' ); ?></span></h3>
						<div class="inside">

							<div id="paypal-on-ninja-forms-area">
								<?php if ( version_compare( Political_Options_NF_PP_Integration::get_ninja_forms_version(), '3.0', '<' ) ) { ?>
									<p><?php _e( 'Using Ninja Forms you will configure your PayPal options directly in the form settings. Create or edit a form and click the "Settings" tab. In that area you can enter the account details in the "PayPal Options" container.', 'political-options' ); ?></p>
								<?php } else { ?>
									<p><?php _e( 'Using Ninja Forms you will configure your PayPal options directly in the form settings. Create or edit a form and click the "Advanced" tab. In that area you can enter the account details in the "PayPal Options" container.', 'political-options' ); ?></p>
								<?php } ?>
							</div>

							<div id="paypal-area">
								<table class="form-table" >
									<tbody>
										<tr>
											<th scope="row">
												<label for="paypal_mode"><?php _e( 'Mode', 'political-options' ); ?></label>
											</th>
											<td>
												<p><input type="radio" name="paypal_mode" class="paypal_mode" <?php echo ( isset( $this->donations['paypal_mode'] ) && $this->donations['paypal_mode'] == 'disabled' ) ? 'checked' : ''; ?> value="disabled"><?php _e(' Disabled', 'political-options' ); ?></p>
					   							<p><input type="radio" name="paypal_mode" class="paypal_mode" <?php echo ( ! isset( $this->donations['paypal_mode'] ) || $this->donations['paypal_mode'] == 'enabled' ) ? 'checked' : ''; ?> value="enabled"><?php _e(' Enabled', 'political-options' ); ?></p>
					   							<p><input type="radio" name="paypal_mode" class="paypal_mode" <?php echo ( isset( $this->donations['paypal_mode'] ) && $this->donations['paypal_mode'] == 'sandbox' ) ? 'checked' : ''; ?> value="sandbox"><?php _e(' Sandbox (for testing)', 'political-options' ); ?></p>
												<p class="description"><?php _e( 'To testing transactions, use sandbox mode.', 'political-options' ) ?></p>
											</td>
										</tr>
									</tbody>
								</table>


								<div id="paypal-details-area">
									<table class="form-table" >
										<tbody>
											<tr>
												<th scope="row">
													<label for="paypal_email"><?php _e( 'Email Address', 'political-options' ); ?></label>
												</th>
												<td>
													<input class="regular-text" type="text" name="paypal_email" id="paypal_email" value="<?php echo isset( $this->donations['paypal_email'] ) ? $this->donations['paypal_email'] : ''; ?>" />
													<p class="description"><?php _e('The address associated with your PayPal account', 'political-options') ?></p>
												</td>
											</tr>
											<tr>
												<th scope="row">
													<label for="paypal_description"><?php _e('Description', 'political-options'); ?></label>
												</th>
												<td>
													<input class="regular-text" type="text" name="paypal_description" id="paypal_description" value="<?php echo isset( $this->donations['paypal_description'] ) ? $this->donations['paypal_description'] : ''; ?>" />
													<p class="description"><?php _e( 'This label will appear on the transactions for your PayPal statement.', 'political-options' ) ?></p>
												</td>
											</tr>
											<tr>
												<th scope="row">
													<label for="paypal_transaction_type"><?php _e( 'Transaction Type', 'political-options' ); ?></label>
												</th>
												<td>
													<p><input type="radio" name="paypal_transaction_type" <?php echo ( ! isset($this->donations['paypal_transaction_type'] ) || $this->donations['paypal_transaction_type'] == 'donation' ) ? 'checked' : ''; ?> value="donation"><?php _e( ' Donation', 'political-options' ); ?></p>
						   							<p><input type="radio" name="paypal_transaction_type" <?php echo ( isset($this->donations['paypal_transaction_type'] ) && $this->donations['paypal_transaction_type'] == 'payment' ) ? 'checked' : ''; ?> value="payment"><?php _e( ' Payment', 'political-options' ); ?></p>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div> <!-- PayPal Area -->
						</div>
					</div>
					
					<?php endif ?>
					
					<input class="button-primary" type="submit" value="<?php _e( 'Save Changes', 'political-options' ) ?>">
				</form>
			</div>

			<p>&nbsp;</p>
			<hr>

			<h4><?php _e( 'Extend and Enhance', 'political-options' ); ?></h4>
			<p><?php
				printf( __( 'Integrating the Quick Donate tool with Ninja Forms makes it possible to add more payment solutions, conditional logic, multi-part forms, custom form styles, newsletter integration and much more. To see a full list of add-ons, visit the %sNinja Forms Extensions%s directory.', 'political-options' ), '<a href="https://ninjaforms.com/extensions/" target="_blank">', '</a>' );
			?></p>
		</div><?php
	}


	/**
	 * Settings Page Load
	 */
	function settings_page() {

		// On save...
		if (
			isset( $_GET['action'] )
			&& $_GET['action'] == 'save-settings'
			&& ! empty( $_POST )
			&& isset( $_POST['settings-page-nonce'] )
			&& wp_verify_nonce( $_POST['settings-page-nonce'], basename( __FILE__ ) )
		) {

			$old_settings = $this->settings; // current database values
			$this->settings = stripslashes_deep( $_POST );
			// CPT custom permalinks
			$this->settings['slug_issues'] = isset( $this->settings['slug_issues'] ) ? esc_attr( $this->settings['slug_issues'] ) : '';
			$this->settings['slug_events'] = isset( $this->settings['slug_events'] ) ? esc_attr( $this->settings['slug_events'] ) : '';
			$this->settings['slug_videos'] = isset( $this->settings['slug_videos'] ) ? esc_attr( $this->settings['slug_videos'] ) : '';
			// Landing pages
			$this->settings['landing_enabled'] = isset( $this->settings['landing_enabled'] ) ? esc_attr( $this->settings['landing_enabled'] ) : '';
			$this->settings['landing_redirect_id'] = isset( $this->settings['landing_redirect_id'] ) ? esc_attr( $this->settings['landing_redirect_id'] ) : 0;
			$this->settings['landing_redirect_url'] = isset( $this->settings['landing_redirect_url'] ) ? esc_url_raw( $this->settings['landing_redirect_url'] ) : '';
			$this->settings['landing_entry_page'] = isset( $this->settings['landing_entry_page'] ) ? esc_attr( $this->settings['landing_entry_page'] ) : '';
			$this->settings['landing_timeout'] = isset( $this->settings['landing_timeout'] ) ? esc_attr( $this->settings['landing_timeout'] ) : ''; // specify a cookie expiration?
			$this->settings['landing_cookies_reset'] = isset( $this->settings['landing_cookies_reset'] ) ? esc_attr( $this->settings['landing_cookies_reset'] ) : false; // should it be reset?
			// Events
			$this->settings['show_title_as_link'] = isset( $this->settings['show_title_as_link'] ) ? esc_attr( $this->settings['show_title_as_link'] ) : 'off';

			// Date of last reset
			if ( $this->settings['landing_cookies_reset'] === 'reset' ) {
				$new_date = new DateTime();
				$this->settings['landing_cookies_reset_date'] = $new_date->getTimestamp();
			} else {
				$this->settings['landing_cookies_reset_date'] = isset( $old_settings['landing_cookies_reset_date'] ) ? esc_attr( $old_settings['landing_cookies_reset_date'] ) : ''; // date of last reset
			}

			// Updated slugs? We need to make sure rewrite rules are updated.
			if (
				 $old_settings['slug_issues'] !== $this->settings['slug_issues'] ||
				 $old_settings['slug_events'] !== $this->settings['slug_events'] ||
				 $old_settings['slug_videos'] !== $this->settings['slug_videos']
				) {
				$this->settings['rewrite_flush_rules'] = 1;
			}

			if ( defined( 'JSON_UNESCAPED_UNICODE' ) ) {
				update_option( 'political_options_settings', json_encode( $this->settings, JSON_UNESCAPED_UNICODE ) );
			} else {
				update_option( 'political_options_settings', unescaped_json( $this->settings ) );
			}

			$link = admin_url() . 'edit.php?post_type=political-issue&page=political-options-settings';
			echo '<script type="text/javascript">window.location = "'. esc_url_raw( $link ) .'";</script>';
		}

		// Render the settings page content
		$this->display_settings_page();
	}

	/**
	 * Settings Page Content
	 */
	function display_settings_page() {

		$redirect_pages = array(
							'' 			=> '',
							'custom' 	=> __( 'CUSTOM: Enter a URL', 'political-options' ),
							0 			=> __( '&mdash; Select a page &mdash;', 'political-options' ),
		);
		$all_pages = get_pages();
		foreach( $all_pages as $page ) {
			$redirect_pages[$page->ID] = $page->post_title;
		}
		$redirect_page_selected = $this->settings['landing_redirect_id'];

		// Cookie last reset
		if ( ! empty( $this->settings['landing_cookies_reset_date'] ) ) {
			$resetDate = new DateTime();
			$resetDate->setTimestamp( $this->settings['landing_cookies_reset_date'] + ( get_option( 'gmt_offset' ) * 3600 ) );
			$reset_on = $resetDate->format( get_option( 'date_format' ).' '.get_option( 'time_format' ) );
			$reset_date_text = __( 'Last reset: ', 'political-options' ) .'<b>'. $reset_on .'</b>';
		} else {
			$reset_date_text = __( 'There are no previous resets.', 'political-options' );
		}
		?>

		<div class="wrap">

			<h1><?php _e( 'Settings', 'political-options' ) ?></h1>
			<p><?php _e( 'Configure the settings for the Political Options plugin.', 'political-options' ); ?></p>

			<div> <!-- <div id="poststuff"> If we wanted 'postbox' containers -->
				<form action="<?php echo admin_url( 'edit.php?post_type=political-issue&page=political-options-settings&action=save-settings' ); ?>" method="post">
					<?php wp_nonce_field( basename( __FILE__ ) , 'settings-page-nonce' ); ?>
					<h3 class="title"><?php _e( 'Permalinks', 'political-options' ) ?></h3>
					<p>
						<?php _e( 'Set a base URL for the content types.', 'political-options' ) ?>
						<?php printf( __( 'For example, using %s%s%s as your base would appear: ', 'political-options' ), '<code>', __( 'topics', 'political-options' ), '</code>'); ?>
						<strong><code><?php echo esc_url( site_url() ) .'/'. __( 'topics', 'political-options' ) .'/the-page-name/'; ?></code></strong>
						<?php _e( 'If you leave these blank the defaults will be used.', 'political-options' ) ?>
					</p>
					<table class="form-table" style="max-width: 800px;">
						<tbody>
							<tr>
								<th scope="row">
									<label for="slug_issues"><?php _e( 'Issues Base', 'political-options' ); ?></label>
								</th>
								<td>
									<input class="regular-text" type="text" name="slug_issues" id="slug_issues" value="<?php echo isset( $this->settings['slug_issues'] ) ? $this->settings['slug_issues'] : '' ; ?>">
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="slug_events"><?php _e( 'Events Base', 'political-options' ); ?></label>
								</th>
								<td>
									<input class="regular-text" type="text" name="slug_events" id="slug_events" value="<?php echo isset( $this->settings['slug_events'] ) ? $this->settings['slug_events'] : '' ; ?>">
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="slug_videos"><?php _e( 'Videos Base', 'political-options' ); ?></label>
								</th>
								<td>
									<input class="regular-text" type="text" name="slug_videos" id="slug_videos" value="<?php echo isset( $this->settings['slug_videos'] ) ? $this->settings['slug_videos'] : '' ; ?>">
								</td>
							</tr>
						</tbody>
					</table>

					<h3 class="title"><?php _e( 'Landing Pages', 'political-options' ) ?></h3>
					<p>
						<?php _e( 'Assign a page to show when someone first visits your site. This can be used for a special promotion, newsletter sign up, donation form and much more. Users are only redirected to this page on their first visit to your site.', 'political-options' ) ?><br>
					</p>
					<table class="form-table" style="max-width: 800px;">
						<tbody>
							<tr>
								<th scope="row">
									<label for="landing_enabled"><?php _e( 'Show Landing Page', 'political-options' ); ?></label>
								</th>
								<td>
									<p><input type="radio" name="landing_enabled" <?php echo ( isset( $this->settings['landing_enabled'] ) && $this->settings['landing_enabled'] == 'enabled' ) ? 'checked' : ''; ?> value="enabled"><?php _e( ' Enabled', 'political-options' ); ?></p>
		   							<p><input type="radio" name="landing_enabled" <?php echo ( ! isset( $this->settings['landing_enabled'] ) || $this->settings['landing_enabled'] == 'disabled' ) ? 'checked' : ''; ?> value="disabled"><?php _e( ' Disabled', 'political-options' ); ?></p>
								</td>
							</tr>
							<tr class="landing-page-options">
								<th scope="row">
									<label for="landing_redirect_id"><?php _e( 'Landing Page', 'political-options' ); ?></label>
								</th>
								<td>
									<select name="landing_redirect_id" id="landing_redirect_id">
										<?php
										if ( ! empty( $redirect_pages ) ) {
											foreach ( $redirect_pages as $key => $val ) {
												printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $key, $redirect_page_selected, false ), esc_html( $val ) );
											}
										} ?>
									</select>
									<div id="landing-custom-url-field">
										<br>
										<strong><label for="landing_redirect_url"><?php _e( 'Custom URL:', 'political-options' ); ?></label></strong>
										<br>
										<input class="regular-text" type="text" name="landing_redirect_url" id="landing_redirect_url" value="<?php echo isset( $this->settings['landing_redirect_url'] ) ? $this->settings['landing_redirect_url'] : '' ; ?>">
									</div>
									<p class="description"><?php _e( 'Select a page or enter a custom URL for the landing page.', 'political-options' ) ?></p>
								</td>
							</tr>
							<tr class="landing-page-options">
								<th scope="row">
									<label for="landing_entry_page"><?php _e( 'Entry Page', 'political-options' ); ?></label>
								</th>
								<td>
									<p><input type="radio" name="landing_entry_page" <?php echo ( ! isset( $this->settings['landing_entry_page'] ) || $this->settings['landing_entry_page'] == 'home' ) ? 'checked' : ''; ?> value="home"><?php _e( ' Home Page Only', 'political-options' ); ?></p>
		   							<p><input type="radio" name="landing_entry_page" <?php echo ( isset( $this->settings['landing_entry_page'] ) && $this->settings['landing_entry_page'] == 'all' ) ? 'checked' : ''; ?> value="all"><?php _e( ' Entire Site', 'political-options' ); ?></p>
									<p class="description"><?php _e('Redirect for all first time visitors or only those entering from the home page.', 'political-options') ?></p>
								</td>
							</tr>
							<tr class="landing-page-options">
								<th scope="row">
									<label for="landing_timeout"><?php _e( 'Show Again Timeout', 'political-options' ); ?></label>
								</th>
								<td>
									<input class="regular-text" type="text" name="landing_timeout" id="landing_timeout" value="<?php echo isset( $this->settings['landing_timeout'] ) ? $this->settings['landing_timeout'] : '' ; ?>">
									<p class="description"><?php _e( 'An optional setting for returning visitors to see the landing page again, after a specified number of days. If you do not want to show the page again, leave this blank or set to 0.', 'political-options' ) ?></p>
								</td>
							</tr>
							<tr class="landing-page-options">
								<th scope="row">
									<label for="landing_cookies_reset"><?php _e( 'Clear Old Sessions', 'political-options' ); ?></label>
								</th>
								<td>
									<input type="checkbox" name="landing_cookies_reset" id="landing_cookies_reset" value="reset"> <em class="description"><?php echo  wp_kses_post( $reset_date_text ); ?></em>
									<p class="description"><?php _e( 'Reset the tracker for past visitors. If you started a new promotion this is a good way to ensure all visitors see the new landing page.', 'political-options' ) ?></p>
								</td>
							</tr>
						</tbody>
					</table>

					<h3 class="title"><?php _e( 'Events', 'political-options' ) ?></h3>
					<table class="form-table" style="max-width: 800px;">
						<tbody>
							<tr>
								<th scope="row">
									<label for="landing_enabled"><?php _e( 'Show Title As Link', 'political-options' ); ?></label>
								</th>
								<td>
									<p><input type="checkbox" name="show_title_as_link" <?php echo ( isset( $this->settings['show_title_as_link'] ) && $this->settings['show_title_as_link'] == 'on' ) ? 'checked' : ''; ?>><?php _e( 'Enabled', 'political-options' ) ?></p>
								</td>
							</tr>
						</tbody>
					</table>

					<input class="button-primary" type="submit" value="<?php _e( 'Save Changes', 'political-options' ) ?>">
				</form>
			</div>

			<p>&nbsp;</p>

		</div><?php
	}

} // end class( 'Political_Options_CPT' )
endif; // end (!class_exists( 'Political_Options_CPT' ))

// Main function to call Political_Options_CPT class
function political_options_load() {
	$political_options = new Political_Options_CPT();
}

// Go!
political_options_load();
