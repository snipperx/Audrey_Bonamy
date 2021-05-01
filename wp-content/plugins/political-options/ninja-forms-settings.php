<?php
/**
 * Include PayPal settings into Ninja Forms for taking donations
 *
 * Adapted from the plugin: "PP Standard Gateway for NF" by Aman Saini (http://amansaini.me)
*/

// don't load directly
if ( !defined( 'ABSPATH' ) ) die( '-1' );

// The class
class Political_Options_NF_PP_Integration {

	/**
	 * Paypal Live Url
	 *
	 * @var string
	 */
	private static $production_url = "https://www.paypal.com/cgi-bin/webscr/";

	/**
	 * Paypal Sandbox Url
	 *
	 * @var string
	 */
	private static $sandbox_url = "https://www.sandbox.paypal.com/cgi-bin/webscr/";

	private $paypal_status_export_position = 0;

	public function __construct() {

		if ( ! self::is_ninjaform_installed() ) {
			return;
		}

		$this->init();

	}

	private function init() {

		$version = self::get_ninja_forms_version();

		if ( version_compare( $version, '3.0', '<' ) || get_option( 'ninja_forms_load_deprecated', false ) ) {

			if ( is_admin() ) {

				// Add config metabox to the form setting tab
				add_filter( 'admin_init', array( $this, 'paypal_settings_metabox' ) );
				// Add payment metabox to submission details page
				add_action( 'add_meta_boxes', array( $this, 'add_payment_status_meta_box' ) );
				// Add Payment status Label to Export file.
				add_filter( 'nf_subs_csv_label_array', array( $this, 'add_payment_label_sub_export' ), 11, 2 );
				// Add Payment status field to Export file.
				add_filter( 'nf_subs_csv_value_array', array( $this, 'add_payment_field_sub_export' ), 11, 2 );

			} else {
				
				add_action( 'init', array( $this, 'nf_init_political_options_hook' ) );
				
			}

			add_action( 'parse_request', array( $this, 'process_ipn' ) );

		}
		// else {

		// 	if ( is_admin() ) {

		// 		// Add config metabox to the form advanced tab
		// 		add_filter( 'ninja_forms_from_settings_types', array( $this, 'add_paypal_options' ) );
		// 		add_filter( 'ninja_forms_localize_form_paypal_settings', array( $this, 'add_paypal_fields' ) );
		// 		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		// 		// Add payment metabox to submission details page
		// 		add_action( 'add_meta_boxes', array( $this, 'add_payment_status_meta_box' ) );
		// 		// Add Payment status Label to Export file.
		// 		add_filter( 'nf_subs_csv_label_array_before_fields', array( $this, 'add_payment_label_sub_export_nf_v3' ), 11, 2 );
		// 		// Add Payment status field to Export file.
		// 		add_filter( 'nf_subs_csv_value_array', array( $this, 'add_payment_field_sub_export_nf_v3' ), 11, 2 );

		// 	} else {

		// 		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// 	}

		// 	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// 		add_action( 'init', array( $this, 'nf_init_political_options_hook' ) );
		// 	}

		// }

		// add_action( 'parse_request', array( $this, 'process_ipn' ) );

	}

	public function nf_v3_political_options_processing( $data ) {

		$settings = $data['settings'];

		// Check is paypal is enabled for this form
		$paypal_mode = array_key_exists( 'political_pp_mode', $settings ) ? $settings['political_pp_mode'] : '';
		if ( empty( $paypal_mode ) || $paypal_mode == 'disabled' ) {
			return;
		}

		//check if business email is set
		$business_email = array_key_exists( 'political_pp_business_email', $settings ) ? $settings['political_pp_business_email'] : '';
		if ( empty( $business_email ) ) {
			return;
		}

		//submission Id
		$actions = array_key_exists( 'actions', $data ) ? $data['actions'] : array();
		$save    = array_key_exists( 'save', $actions ) ? $actions['save'] : array();
		$sub_id  = array_key_exists( 'sub_id', $save ) ? $save['sub_id'] : 0;
		update_post_meta( $sub_id, 'payment_options_status', 'Not Paid' );
		$custom_field = $sub_id . '|' . wp_hash( $sub_id );

		$description      = array_key_exists( 'political_pp_description', $settings ) ? $settings['political_pp_description'] : '';
		$transaction_type = array_key_exists( 'political_pp_transaction_type', $settings ) ? $settings['political_pp_transaction_type'] : '';

		// Fields
		$first_name_field_id = array_key_exists( 'political_pp_first_name', $settings ) ? $settings['political_pp_first_name'] : 0;
		$last_name_field_id  = array_key_exists( 'political_pp_last_name', $settings ) ? $settings['political_pp_last_name'] : 0;
		$email_field_id      = array_key_exists( 'political_pp_email', $settings ) ? $settings['political_pp_email'] : 0;
		$phone_field_id      = array_key_exists( 'political_pp_phone', $settings ) ? $settings['political_pp_phone'] : 0;
		$address1_field_id   = array_key_exists( 'political_pp_address1', $settings ) ? $settings['political_pp_address1'] : 0;
		$address2_field_id   = array_key_exists( 'political_pp_address2', $settings ) ? $settings['political_pp_address2'] : 0;
		$city_field_id       = array_key_exists( 'political_pp_city', $settings ) ? $settings['political_pp_city'] : 0;
		$state_field_id      = array_key_exists( 'political_pp_state', $settings ) ? $settings['political_pp_state'] : 0;
		$zip_field_id        = array_key_exists( 'political_pp_zip', $settings ) ? $settings['political_pp_zip'] : 0;
		$country_field_id    = array_key_exists( 'political_pp_country', $settings ) ? $settings['political_pp_country'] : 0;
		$amount_field_id     = array_key_exists( 'political_pp_amount', $settings ) ? $settings['political_pp_amount'] : 0;

		$first_name = '';
		$last_name  = '';
		$email      = '';
		$phone      = '';
		$address_1  = '';
		$address_2  = '';
		$city       = '';
		$zip        = '';
		$state      = '';
		$country    = '';
		$amount     = '';
		$total      = 0;

		foreach ( $data['fields'] as $field ) {
			switch ( $field['id'] ) {

				case $first_name_field_id:
					$first_name = $field['value'];
					break;

				case $last_name_field_id:
					$last_name = $field['value'];
					break;

				case $email_field_id:
					$email = $field['value'];
					break;

				case $phone_field_id:
					$phone = $field['value'];
					break;

				case $address1_field_id:
					$address_1 = $field['value'];
					break;

				case $address2_field_id:
					$address_2 = $field['value'];
					break;

				case $city_field_id:
					$city = $field['value'];
					break;

				case $state_field_id:
					$state = $field['value'];
					break;

				case $zip_field_id:
					$zip = $field['value'];
					break;

				case $country_field_id:
					$country = $field['value'];
					break;

				case $amount_field_id:
					$amount = $field['value'];
					break;

			}

			if ( $field['type'] === 'total' ) {
				$total = $field['value'];
			}

		}

		// A user may specify an amount field in the settings, or allow a total calculation field to do this instead.
		$form_cost = empty( $amount ) ? $total : $amount;

		// Currency
		$currency_type = array_key_exists( 'political_pp_currency_type', $settings ) ? $settings['political_pp_currency_type'] : '';

		// URL strings
		$ipn_url     = get_bloginfo( 'url' ) . '/?page=nf_political_options_ipn';
		$success_url = array_key_exists( 'political_pp_success_page', $settings ) ? $settings['political_pp_success_page'] : '';
		$cancel_url  = array_key_exists( 'political_pp_cancel_page', $settings ) ? $settings['political_pp_cancel_page'] : '';

		// PayPal form values
		$paypal_args = apply_filters( 'nf_political_options_args', array(
			'business'      => $business_email,
			'currency_code' => $currency_type,
			'charset'       => 'UTF-8',
			'rm'            => 2,
			'upload'        => 1,
			'no_note'       => 1,
			'return'        => $success_url,
			'cancel_return' => $cancel_url,
			'custom'        => $custom_field,
			'notify_url'    => $ipn_url,
			'success_url'   => $success_url,
			'cancel_url'    => $cancel_url,
			'no_shipping'   => 1,
			'item_name'     => $description,
			'quantity'      => 1,
			'first_name'    => $first_name,
			'last_name'     => $last_name,
			'lc'            => '',
			'address1'      => $address_1,
			'address2'      => $address_2,
			'country'       => $country,
			'state'         => $state,
			'city'          => $city,
			'zip'           => $zip,
			'night_phone_a' => $phone,
			'email'         => $email,
			'on0'           => '',
			'cmd'           => ( empty( $transaction_type ) ) ? '_xclick' : $transaction_type,
			'amount'        => $form_cost
		) );

		// Build query string from args
		$paypal_args = http_build_query( $paypal_args, '', '&' );

		if ( $paypal_mode == 'sandbox' ) {
			$paypal_adr = self::$sandbox_url . '?test_ipn=1&';
		} else {
			$paypal_adr = self::$production_url . '?';
		}

		$payment_link = $paypal_adr . $paypal_args;

		// determine whether to proceed to paypal or not
		if ( $this->format_number( $form_cost ) > 0 ) {
			$redirect = true;
		} else {
			$redirect = apply_filters( 'nf_political_options_process_zero_cost_payments', true );
		}

		if ( $redirect ) {
			$data['redirect'] = $payment_link;

			$response = array(
				'errors' => array(),
				'data'   => $data
			);

			echo wp_json_encode( $response );

			wp_die();
		}

	}

	public function enqueue_scripts() {

		wp_enqueue_script( 'political-options-ninja-forms-v3', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/js/political-options-ninja-forms-v3.js', array( 'nf-front-end' ) );

	}

	public function enqueue_admin_scripts() {

		wp_register_script( 'paypal-options', POLITICAL_OPTIONS_PLUGIN_URL . 'assets/js/ninja-forms-settings.js', array(
			'backbone-marionette',
			'backbone-radio'
		) );
		wp_localize_script( 'paypal-options', 'info', array(
			'add_fields_text' => __( 'Please add fields', 'political-options' ),
		) );
		wp_enqueue_script( 'paypal-options' );

	}
	
	public function add_paypal_fields() {

		if ( $_GET['form_id'] !== 'new' ) {

			$fields_list = array(
				array(
					'label' => '',
					'value' => ''
				)
			);

			$all_fields = Ninja_Forms()->form( $_GET['form_id'] )->get_fields();
			foreach ( $all_fields as $field ) {
				array_push( $fields_list, array(
					'label' => $field->get_setting( 'label' ),
					'value' => $field->get_id()
				) );
			}

		} else {

			$fields_list = array();
			array_push( $fields_list, array(
				'label' => __( 'Please add fields', 'political-options' ),
				'value' => ''
			) );

		}

		// List of all WP pages
		$pages      = get_pages();
		$pages_list = array();
		array_push( $pages_list, array(
			'label' => __( '- None', 'political-options' ),
			'value' => ''
		) );
		foreach ( $pages as $page ) {
			array_push( $pages_list, array(
				'label' => $page->post_title,
				'value' => get_page_link( $page->ID )
			) );
		}

		// Array of different currency types and HTML output
		$currencies     = pol_get_currency_symbols();
		$currency_types = array();
		array_push( $currency_types, array(
			'name'  => '',
			'value' => ''
		) );
		foreach ( $currencies as $name => $value ) {
			array_push( $currency_types, array(
				'label' => $name . ' - ' . $value,
				'value' => $name
			) );
		}

		return array(

			// Status
			'political_pp_mode' => array(
				'name'    => 'political_pp_mode',
				'label'   => __( 'Enable PayPal', 'political-options' ),
				'width'   => 'full',
				'group'   => 'primary',
				'type'    => 'select',
				'options' => array(
					array(
						'label' => __( 'Disabled', 'political-options' ),
						'value' => 'disabled'
					),
					array(
						'label' => __( 'Enabled', 'political-options' ),
						'value' => 'enabled'
					),
					array(
						'label' => __( 'Sandbox (for testing)', 'political-options' ),
						'value' => 'sandbox'
					)
				),
			),

			// PayPal Account Details
			'political_pp_business_email' => array(
				'name'  => 'political_pp_business_email',
				'type'  => 'textbox',
				'label' => __( 'Business Email', 'political-options' ),
				'group' => 'primary',
				'width' => 'full',
				'value' => '',
			),

			// Transaction Details
			'political_pp_currency_type' => array(
				'name'          => 'political_pp_currency_type',
				'type'          => 'select',
				'label'         => __( 'Currency Type', 'political-options' ),
				'options'       => $currency_types,
				'default_value' => 'USD',
				'help'          => __( 'The currency symbol to be used.', 'political-options' ),
				'group'         => 'primary',
				'width'         => 'full',
			),

			// Transaction Description
			'political_pp_description' => array(
				'name'  => 'political_pp_description',
				'type'  => 'textbox',
				'label' => __( 'Transaction Description', 'political-options' ),
				'help'  => __( 'This will show in Paypal as the item name for which you are taking payment.You can use hidden field in form and set that field here', 'political-options' ),
				'group' => 'primary',
				'width' => 'full',
			),

			// Transaction Type
			'political_pp_transaction_type' => array(
				'name'          => 'political_pp_transaction_type',
				'type'          => 'select',
				'default_value' => '_donations',
				'label'         => __( 'Transaction Type', 'political-options' ),
				'options'       => array(
					array(
						'label' => __( 'Donation', 'political-options' ),
						'value' => '_donations'
					),
					array(
						'label' => __( 'Payment', 'political-options' ),
						'value' => '_xclick'
					)
				),
				'help'          => '',
				'group'         => 'primary',
				'width'         => 'full',
			),

			// Amount field
			'political_pp_amount' => array(
				'name'    => 'political_pp_amount',
				'type'    => 'select',
				'label'   => __( 'Amount', 'political-options' ),
				'options' => $fields_list,
				'help'    => __( 'Select the field containing the total amount for the transaction. You can use any field type or insert a special payment field such as the "total" field type to calculate the value from several other fields.', 'political-options' ),
				'group'   => 'primary',
				'width'   => 'full',
			),

			// Details fields
			'political_pp_first_name' => array(
				'name'    => 'political_pp_first_name',
				'type'    => 'select',
				'label'   => __( 'First Name', 'political-options' ),
				'options' => $fields_list,
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_last_name' => array(
				'name'    => 'political_pp_last_name',
				'type'    => 'select',
				'label'   => __( 'Last Name', 'political-options' ),
				'options' => $fields_list,
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_email' => array(
				'name'    => 'political_pp_email',
				'type'    => 'select',
				'label'   => __( 'Email', 'political-options' ),
				'options' => $fields_list,
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_phone' => array(
				'name'    => 'political_pp_phone',
				'type'    => 'select',
				'label'   => __( 'Phone', 'political-options' ),
				'options' => $fields_list,
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_address1' => array(
				'name'    => 'political_pp_address1',
				'type'    => 'select',
				'label'   => __( 'Address 1', 'political-options' ),
				'options' => $fields_list,
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_address2' => array(
				'name'    => 'political_pp_address2',
				'type'    => 'select',
				'label'   => __( 'Address 2', 'political-options' ),
				'options' => $fields_list,
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_city' => array(
				'name'    => 'political_pp_city',
				'type'    => 'select',
				'label'   => __( 'City', 'political-options' ),
				'options' => $fields_list,
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_state' => array(
				'name'    => 'political_pp_state',
				'type'    => 'select',
				'label'   => __( 'State', 'political-options' ),
				'options' => $fields_list,
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_zip' => array(
				'name'    => 'political_pp_zip',
				'type'    => 'select',
				'label'   => __( 'ZIP', 'political-options' ),
				'options' => $fields_list,
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_country' => array(
				'name'    => 'political_pp_country',
				'type'    => 'select',
				'label'   => __( 'Country', 'political-options' ),
				'options' => $fields_list,
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_success_page' => array(
				'name'    => 'political_pp_success_page',
				'type'    => 'select',
				'label'   => __( 'Payment Success Page', 'political-options' ),
				'options' => $pages_list,
				'help'    => __( 'Select the page user will return after making successful payment.', 'political-options' ),
				'group'   => 'primary',
				'width'   => 'full',
			),

			'political_pp_cancel_page' => array(
				'name'    => 'political_pp_cancel_page',
				'type'    => 'select',
				'options' => $pages_list,
				'label'   => __( 'Payment Cancel Page', 'political-options' ),
				'help'    => __( 'Select the page user will return if payment is canceled.', 'political-options' ),
				'group'   => 'primary',
				'width'   => 'full',
			),

		);

	}

	public function add_paypal_options( $settings_types = array() ) {

		$settings_types['paypal'] = array(
			'id'       => 'paypal',
			'nicename' => __( 'Paypal Options', 'political-options' )
		);

		return $settings_types;

	}

	function nf_init_political_options_hook() {

		if ( version_compare( self::get_ninja_forms_version(), '3.0', '<' ) || get_option( 'ninja_forms_load_deprecated', FALSE ) ) {

			add_action( 'ninja_forms_post_process', array( $this, 'nf_political_options_processing' ), 1200 );

		} else {

			add_action( 'ninja_forms_after_submission', array( $this, 'nf_v3_political_options_processing' ), 1200 );

		}

	}

	function nf_political_options_processing() {

		global $ninja_forms_processing;

		// Check is paypal is enabled for this form
		$paypal_enabled = $ninja_forms_processing->get_form_setting( 'political_pp_mode' );
		if ( empty( $paypal_enabled ) || $paypal_enabled == 'disabled' ) {
			return;
		}

		//check if business email is set
		$business_email = $ninja_forms_processing->get_form_setting( 'political_pp_business_email' );
		if ( empty( $business_email ) ) {
			return;
		}

		//submission Id
		$sub_id = $ninja_forms_processing->get_form_setting( 'sub_id' );
		update_post_meta( $sub_id, 'payment_options_status', 'Not Paid' );
		$custom_field = $sub_id . "|" . wp_hash( $sub_id );

		//Get an array of all user-submitted values:
		$all_fields = $ninja_forms_processing->get_all_fields();

		// get the user info
		$user_info = $ninja_forms_processing->get_user_info();

		// Plugin mode-- Test/Live
		$plugin_mode = $ninja_forms_processing->get_form_setting( 'political_pp_mode' );

		// Fields
		$description_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_description' );
		$first_name_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_first_name' );
		$last_name_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_last_name' );
		$email_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_email' );
		$phone_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_phone' );
		$address1_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_address1' );
		$address2_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_address2' );
		$city_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_city' );
		$state_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_state' );
		$zip_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_zip' );
		$country_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_country' );
		$transaction_type_id = $ninja_forms_processing->get_form_setting( 'political_pp_transaction_type' );
		$amount_field_id = $ninja_forms_processing->get_form_setting( 'political_pp_amount' );

		$description = ( ! empty( $all_fields[$description_field_id] ) )?strip_tags( $all_fields[$description_field_id] ) : '';
		$first_name = ( ! empty( $all_fields[$first_name_field_id] ) ) ? $all_fields[$first_name_field_id] : '';
		$last_name = ( ! empty( $all_fields[$last_name_field_id] ) ) ? $all_fields[$last_name_field_id] : '';
		$email = ( ! empty( $all_fields[$email_field_id] ) ) ? $all_fields[$email_field_id] : '';
		$phone = ( ! empty( $all_fields[$phone_field_id] ) ) ? $all_fields[$phone_field_id] : '';
		$address_1 = ( ! empty( $all_fields[$address1_field_id] ) ) ? $all_fields[$address1_field_id] : '';
		$address_2 = ( !empty( $all_fields[$address2_field_id] ) ) ? $all_fields[$address2_field_id]:'';
		$city = ( ! empty( $all_fields[$city_field_id] ) ) ? $all_fields[$city_field_id] : '';
		$zip = ( ! empty( $all_fields[$zip_field_id] ) ) ? $all_fields[$zip_field_id] : '';
		$state = ( ! empty( $all_fields[$state_field_id] ) ) ? $all_fields[$state_field_id] : '';
		$country = ( ! empty( $all_fields[$country_field_id] ) ) ? $all_fields[$country_field_id]:'';
		$transaction_type = ( ! empty( $all_fields[$transaction_type_id] ) ) ? $all_fields[$transaction_type_id] : '';
		$amount = ( ! empty( $all_fields[$amount_field_id] ) ) ? $all_fields[$amount_field_id] : '';

		// A user may specify an amount field in the settings, or allow a total calculation field to do this instead.
		$form_cost = 0;
		$form_total = $ninja_forms_processing->get_calc_total();
		if ( ! empty( $form_total['total'] ) ) {
			// Get the form total
			$form_cost = $form_total['total'];
		} else {
			$form_cost = $amount;
		}

		// Currency
		$currency_type = $ninja_forms_processing->get_form_setting( 'political_pp_currency_type' );

		// URL strings
		$ipn_url = get_bloginfo( "url" ) . "/?page=nf_political_options_ipn";
		$success_url = $ninja_forms_processing->get_form_setting( 'political_pp_success_page' );
		$cancel_url = $ninja_forms_processing->get_form_setting( 'political_pp_cancel_page' );

		// PayPal form values
		$paypal_args = apply_filters( 'nf_political_options_args', array(
				'business'      => $business_email,
				'currency_code' => $currency_type,
				'charset'       => 'UTF-8',
				'rm'            => 2,
				'upload'        => 1,
				'no_note'       => 1,
				'return'        => $success_url,
				'cancel_return' => $cancel_url,
				'custom'        => $custom_field,
				'notify_url'    => $ipn_url,
				'success_url'   => $success_url,
				'cancel_url'    => $cancel_url,
				'no_shipping'   => 1,
				'item_name'     => $description,
				'quantity'      => 1,
				'first_name'    => $first_name,
				'last_name'     => $last_name,
				'lc'            => '',
				'address1'      => $address_1,
				'address2'      => $address_2,
				'country'       => $country,
				'state'         => $state,
				'city'          => $city,
				'zip'           => $zip,
				'night_phone_a' => $phone,
				'email'         => $email,
				'on0'           => '',
				'cmd'           => ( empty( $transaction_type ) ) ? '_xclick' : $transaction_type,
				'amount'        => $form_cost
			) );

		// Build query string from args
		$paypal_args = http_build_query( $paypal_args, '', '&' );

		if ( $plugin_mode == 'sandbox' ) {
			$paypal_adr = self::$sandbox_url . '?test_ipn=1&';
		} else {
			$paypal_adr = self::$production_url . '?';
		}

		$payment_link = $paypal_adr . $paypal_args;

		// determine whether to proceed to paypal or not
		if ( $this->format_number( $form_cost ) > 0 ) {
			$redirect = true;
		} else {
			$redirect = apply_filters( 'nf_political_options_process_zero_cost_payments', true );
		}

		if ( $redirect ) {
			// wp_redirect( $payment_link );
			$ninja_forms_processing->update_form_setting( 'landing_page', esc_url_raw( $payment_link ) );
			// exit;
		}
	}

	/**
	 * Add Paypal Settings metabox on Form setting Page
	 *
	 * @param unknown
	 */
	function paypal_settings_metabox() {

		if ( ! isset( $_GET['page'] )  || !isset( $_GET['tab'] ) )
			return;

		if ( $_GET['page'] != 'ninja-forms' || $_GET['tab'] != 'form_settings' )
			return;

		if ( $_GET['form_id'] != 'new' ) {

			// Get all the fields in form.
			$all_fields = ninja_forms_get_fields_by_form_id( $_GET['form_id'] );

			$fields_list = array( array( 'name'=>'', 'value'=>'' ) );

			foreach ( $all_fields as $field ) {
				array_push( $fields_list, array( 'name' =>$field['data']['label'] , 'value' => $field['id'] ) );
			}

		} else {
			$fields_list = array();
			array_push( $fields_list, array( 'name' => __( 'Please add fields', 'political-options' ), 'value' => '' ) );
		}

		// List of all WP pages
		$pages = get_pages();
		$pages_list = array();
		array_push( $pages_list, array( 'name' => __( '- None', 'political-options' ), 'value' => '' ) );
		foreach ( $pages as $pagg ) {
			array_push( $pages_list, array( 'name' => $pagg->post_title, 'value' => get_page_link( $pagg->ID ) ) );
		}

		// Array of different currency types and HTML output
		$currencies = pol_get_currency_symbols();
		$currency_types = array();
		array_push( $currency_types, array( 'name' => '', 'value' => '' ) );
		foreach ( $currencies as $name => $value ) {
			array_push( $currency_types, array( 'name' => $name .' - '. $value, 'value' => $name ) );
		}

		$fields = array(
			'page' 	=> 'ninja-forms',
			'tab' 	=> 'form_settings',
			'slug' 	=> 'political_options_settings',
			'title' => __( 'Paypal Options', 'political-options' ),
			'state' => 'closed',

			'settings' => array(

				// Status
				array(
					'name' 			=> 'political_pp_mode',
					'type' 			=> 'radio',
					'default_value' => 'disabled',
					'label' 		=> __( 'Enable PayPal', 'political-options' ),
					'options'		=> array(
											array(
												'name'	=> __( 'Disabled', 'political-options' ),
												'value'	=> 'disabled'
											),
											array(
												'name'	=> __( 'Enabled', 'political-options' ),
												'value'	=> 'enabled'
											),
											array(
												'name'	=> __( 'Sandbox (for testing)', 'political-options' ),
												'value'	=> 'sandbox'

											)
					),
					'desc' 			=> '',
				),

				// PayPal Account Details
				array(
					'name' 	=> 'political_pp_business_email',
					'type' 	=> 'text',
					'label' => __( 'Business Email', 'political-options' ),
					'desc' 	=> '',
				),

				// Transaction Details
				array(
					'name' 			=> 'political_pp_currency_type',
					'type' 			=> 'select',
					'label' 		=> __( 'Currency Type', 'political-options' ),
					'options' 		=> $currency_types,
					'default_value' => 'USD',
					'desc' 			=> __( 'The currency symbol to be used.', 'political-options' ),
				),
				array(
					'name' 	=> 'political_pp_description',
					'type' 	=> 'text',
					'label' => __( 'Transaction Description', 'political-options' ),
					'desc'	=> __( 'This will show in Paypal as the item name for which you are taking payment.You can use hidden field in form and set that field here', 'political-options' ),
				),
				array(
					'name' 			=> 'political_pp_transaction_type',
					'type' 			=> 'radio',
					'default_value' => '_donations',
					'label' 		=> __( 'Transaction Type', 'political-options' ),
					'options'		=> array(
											array(
												'name'	=> __( 'Donation', 'political-options' ),
												'value'	=> '_donations'
											),
											array(
												'name'	=> __( 'Payment', 'political-options' ),
												'value'	=> '_xclick'
											)
					),
					'desc' 			=> '',
				),
				array(
					'name' 		=> 'political_pp_amount',
					'type' 		=> 'select',
					'label' 	=> __( 'Amount', 'political-options' ),
					'options' 	=> $fields_list,
					'desc'		=> __( 'Select the field containing the total amount for the transaction. You can use any field type or insert a special payment field such as the "total" field type to calculate the value from several other fields.', 'political-options' ),
				),

				// Details fields
				array(
					'name' 		=> 'political_pp_first_name',
					'type' 		=> 'select',
					'label' 	=> __( 'First Name', 'political-options' ),
					'options'	=> $fields_list
				),

				array(
					'name' 		=> 'political_pp_last_name',
					'type' 		=> 'select',
					'label' 	=> __( 'Last Name', 'political-options' ),
					'options'	=> $fields_list
				),
				array(
					'name' 		=> 'political_pp_email',
					'type' 		=> 'select',
					'label' 	=> __( 'Email', 'political-options' ),
					'options' 	=> $fields_list
				),
				array(
					'name' 		=> 'political_pp_phone',
					'type' 		=> 'select',
					'label' 	=> __( 'Phone', 'political-options' ),
					'options' 	=> $fields_list
				),
				array(
					'name' 		=> 'political_pp_address1',
					'type' 		=> 'select',
					'label' 	=> __( 'Address 1', 'political-options' ),
					'options' 	=> $fields_list
				),

				array(
					'name' 		=> 'political_pp_address2',
					'type' 		=> 'select',
					'label' 	=> __( 'Address 2', 'political-options' ),
					'options' 	=> $fields_list
				),
				array(
					'name' 		=> 'political_pp_city',
					'type' 		=> 'select',
					'label' 	=> __( 'City', 'political-options' ),
					'options' 	=>$fields_list
				),

				array(
					'name' 		=> 'political_pp_state',
					'type' 		=> 'select',
					'label' 	=> __( 'State', 'political-options' ),
					'options' 	=> $fields_list
				),

				array(
					'name' 		=> 'political_pp_zip',
					'type' 		=> 'select',
					'label' 	=> __( 'ZIP', 'political-options' ),
					'options' 	=> $fields_list
				),

				array(
					'name' 		=> 'political_pp_country',
					'type' 		=> 'select',
					'label' 	=> __( 'Country', 'political-options' ),
					'options' 	=> $fields_list
				),

				array(
					'name' 		=> 'political_pp_success_page',
					'type' 		=> 'select',
					'label' 	=> __( 'Payment Success Page', 'political-options' ),
					'options' 	=> $pages_list,
					'desc' 		=> __( 'Select the page user will return after making successful payment.', 'political-options' ),
				),
				array(
					'name' 		=> 'political_pp_cancel_page',
					'type' 		=> 'select',
					'options' 	=> $pages_list,
					'label' 	=> __( 'Payment Cancel Page', 'political-options' ),
					'desc' 		=> __( 'Select the page user will return if payment is canceled.', 'political-options' ),
				),
			)
		);

		ninja_forms_register_tab_metabox( $fields );
	}

	/**
	 * Get value of a setting field
	 *
	 * @param [type]  $current_settings [description]
	 * @param [type]  $name             [description]
	 * @return [type]                   [description]
	 */
	public static function get_value( $current_settings, $name ) {

		if ( isset( $current_settings[$name] ) ) {
			if ( is_array( $current_settings[$name] ) ) {
				$value = ninja_forms_stripslashes_deep( $current_settings[$name] );
			}else {
				$value = stripslashes( $current_settings[$name] );
			}
		}else {
			$value = '';
		}

		return $value;
	}

	/**
	 * Add payment box to submission page for Ninja Forms
	 * Shows an additional meta box on the details page for individual submissions.
	 */
	function add_payment_status_meta_box( $post_type ) {

		if ( $post_type == 'nf_sub' ) {
			add_meta_box(
				'nf_political_options_box'
				, __( 'Paypal Data', 'political-options' )
				, array( $this, 'render_payment_meta_box_content' )
				, $post_type
				, 'side'
				, 'low'
			);
		}
	}

	/**
	 * Displays the payment status in the submission detail page in admin
	 *
	 * @return [type] [description]
	 */
	function render_payment_meta_box_content( $post ) {

		$payment_status = get_post_meta( $post->ID, 'payment_options_status', true );

		if ( empty( $payment_status ) ) {
			$payment_status = $this->fixPaymentStatus($post->ID);
		}

		echo '<span >';
		_e( 'Payment Status : ', 'ninja_forms' );

		echo $payment_status . '</span>';

	}

	/**
	 * Set 'payment_options_status' meta equal to 'payment_standard_status' meta if last one exists
	 *
	 * @param int $post_id
	 *
	 * @return string $payment_status
	 */
	private function fixPaymentStatus( $post_id ) {

		$payment_status = get_post_meta( $post_id, 'payment_standard_status', true );

		if ( ! empty( $payment_status ) ) {
			update_post_meta( $post_id, 'payment_options_status', $payment_status );
		}

		return $payment_status;

	}

	/**
	 * Add column header to export file
	 *
	 * @param array $field_labels
	 * @param array $sub_ids
	 *
	 * @return array $field_labels
	 */
	public function add_payment_label_sub_export_nf_v3( $field_labels, $sub_ids ) {

		// save status position
		if ( empty( $this->paypal_status_export_position ) ) {
			$this->paypal_status_export_position = count( $field_labels );
		}

		$field_labels['paypal_payment_status'] = 'Payment Status';

		return $field_labels;

	}

	/**
	 * Add status of payment to export file
	 *
	 * @param array $value_array
	 * @param array $sub_ids
	 *
	 * @return array $value_array
	 */
	public function add_payment_field_sub_export_nf_v3( $value_array, $sub_ids ) {

		//create subs
		$subs = array();
		foreach ( $sub_ids as $sub_id ) {
			$subs[] = new NF_Database_Models_Submission( $sub_id );
		}

		foreach ( $value_array as &$values ) {
			$seq_num = intval( $values['_seq_num'] );

			foreach ( $subs as $sub ) {
				if ( $seq_num === $sub->get_seq_num() ) {
					$payment_status = get_post_meta( $sub->get_id(), 'payment_options_status', true );

					if ( empty( $payment_status ) ) {
						$payment_status = $this->fixPaymentStatus( $sub->get_id() );
					}

					$before_status = array_slice( $values, 0, $this->paypal_status_export_position );
					$after_status  = array_slice( $values, $this->paypal_status_export_position );

					$values = array_merge(
						$before_status,
						array( 'paypal_payment_status' => $payment_status ),
						$after_status
					);
				}
			}
		}

		return $value_array;

	}

	/**
	 * Add coloumn header to export file
	 *
	 * @param unknown $label_array
	 * @param unknown $sub_ids
	 */
	function add_payment_label_sub_export( $label_array, $sub_ids ) {

		$label_array[0]['paypal_payment_status'] = 'Payment Status';
		return $label_array;

	}

	/**
	 * Add status of payment to export file
	 *
	 * @param unknown $value_array
	 * @param unknown $sub_ids
	 */
	function add_payment_field_sub_export( $value_array, $sub_ids ) {
		$payment_status = get_post_meta( $sub_ids[0], 'payment_options_status', true );

		if ( empty( $payment_status ) ) {
			$payment_status = $this->fixPaymentStatus( $sub_ids[0] );
		}

		$value_array[0]['paypal_payment_status'] = $payment_status;

		return $value_array;

	}

	/**
	 * Convert string to PHP floatval
	 *
	 * @param unknown $number
	 * @return floatval
	 */
	function format_number( $number ) {
		$locale_info = localeconv();
		$decimal_point = $locale_info['decimal_point'];

		// strip possible thousands separators
		if ( $decimal_point == '.' ) {
			$number = str_replace( ',', '', $number );
		} else {
			$number = str_replace( '.', '', $number );
		}

		$number = floatval( sanitize_text_field( str_replace( $decimal_point, '.', $number ) ) );

		return $number;
	}

	public static function process_ipn( $wp ) {

		if ( ! self::is_ninjaform_installed() )
			return;

		// Ignore requests that are not IPN
		if ( self::get( 'page' ) != "nf_political_options_ipn" )
			return;

		// Send request to paypal and verify it has not been spoofed
		if ( ! self::verify_paypal_ipn() ) {

			return;
		}

		// Valid IPN requests must have a custom field
		$custom = self::post( "custom" );
		if ( empty( $custom ) ) {

			return;
		}

		// Getting submission associated with this IPN message (sub id is sent in the "custom" field)
		list( $sub_id, $hash ) = explode( "|", $custom );

		$hash_matches = wp_hash( $sub_id ) == $hash;
		// Validates that Sub Id wasn't tampered with
		if ( ! self::post( "test_ipn" ) && ! $hash_matches ) {

			return;
		}

		// Update payment status
		if ( self::post( "payment_status" ) == 'Completed' ) {
			update_post_meta( $sub_id, 'payment_options_status', 'Paid' );

			do_action( 'nf_political_options_success', $_POST );
		}
	}

	private static function verify_paypal_ipn() {

		// Read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		foreach ( $_POST as $key => $value ) {
			$value = urlencode( stripslashes( $value ) );
			$req .= "&$key=$value";
		}
		$url = self::post( "test_ipn" ) ? self::$sandbox_url : self::$production_url;

		//Post back to PayPal system to validate
		$request = new WP_Http();
		$response = $request->post( $url, array( "sslverify" => false, "ssl" => true, "body" => $req, "timeout" => 20 ) );

		return ! is_wp_error( $response ) && $response["body"] == "VERIFIED";
	}

	public static function get( $name, $array = null ) {
		if ( ! $array )
			$array = $_GET;

		if ( isset( $array[$name] ) )
			return $array[$name];

		return "";
	}


	public static function post( $name ) {
		if ( isset( $_POST[$name] ) )
			return $_POST[$name];

		return "";
	}

	/**
	 * Check if Ninja form is  installed
	 */
	public static function is_ninjaform_installed() {

		return version_compare( self::get_ninja_forms_version(), '0.0.0', '>' );

	}

	public static function get_ninja_forms_version() {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( ! is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
			return '0.0.0';
		} else {
			return defined( 'NINJA_FORMS_VERSION' ) ? NINJA_FORMS_VERSION : get_option( 'ninja_forms_version', '0.0.0' );
		}

	}

}
