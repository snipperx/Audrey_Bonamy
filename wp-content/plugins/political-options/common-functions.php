<?php

// Don't load directly
if ( !defined( 'ABSPATH' ) ) die( '-1' );

// Get meta field content - After Event Notes
if ( ! function_exists( 'pol_get_post_event_notes' ) ) :
function pol_get_post_event_notes( $post_id, $need_check_date_end = true ) {
	$meta = get_post_meta( $post_id, 'political-event-notes' );
	$notes = ! empty( $meta[0] ) ? $meta[0] : '';

	$meta = get_post_meta( $post_id, 'political-event-end' );
	$date_end = ! empty( $meta[0] ) ? $meta[0] : '';

	$notes = $need_check_date_end? ( (time() > $date_end ) ? $notes : '' ) : $notes;
	return $notes;
}
endif;


// Output meta field - After Event Notes
if ( ! function_exists( 'pol_post_event_notes' ) ) :
function pol_post_event_notes( $post_id, $need_check_date_end = true ) {
	echo pol_get_post_event_notes( $post_id, $need_check_date_end );
}
endif;

// Array of currency codes and their HTML symbols
if ( ! function_exists( 'pol_get_currency_symbols' ) ) :
function pol_get_currency_symbols( $currency = false ) {

	// all with HTML enttity code
	$currency_symbols = array(
		'AED' => '&#1583;.&#1573;', // ?
		'AFN' => '&#65;&#102;',
		'ALL' => '&#76;&#101;&#107;',
		'AMD' => '',
		'ANG' => '&#402;',
		'AOA' => '&#75;&#122;', // ?
		'ARS' => '&#36;',
		'AUD' => '&#36;',
		'AWG' => '&#402;',
		'AZN' => '&#1084;&#1072;&#1085;',
		'BAM' => '&#75;&#77;',
		'BBD' => '&#36;',
		'BDT' => '&#2547;', // ?
		'BGN' => '&#1083;&#1074;',
		'BHD' => '.&#1583;.&#1576;', // ?
		'BIF' => '&#70;&#66;&#117;', // ?
		'BMD' => '&#36;',
		'BND' => '&#36;',
		'BOB' => '&#36;&#98;',
		'BRL' => '&#82;&#36;',
		'BSD' => '&#36;',
		'BTN' => '&#78;&#117;&#46;', // ?
		'BWP' => '&#80;',
		'BYR' => '&#112;&#46;',
		'BZD' => '&#66;&#90;&#36;',
		'CAD' => '&#36;',
		'CDF' => '&#70;&#67;',
		'CHF' => '&#67;&#72;&#70;',
		'CLF' => '', // ?
		'CLP' => '&#36;',
		'CNY' => '&#165;',
		'COP' => '&#36;',
		'CRC' => '&#8353;',
		'CUP' => '&#8396;',
		'CVE' => '&#36;', // ?
		'CZK' => '&#75;&#269;',
		'DJF' => '&#70;&#100;&#106;', // ?
		'DKK' => '&#107;&#114;',
		'DOP' => '&#82;&#68;&#36;',
		'DZD' => '&#1583;&#1580;', // ?
		'EGP' => '&#163;',
		'ETB' => '&#66;&#114;',
		'EUR' => '&#8364;',
		'FJD' => '&#36;',
		'FKP' => '&#163;',
		'GBP' => '&#163;',
		'GEL' => '&#4314;', // ?
		'GHS' => '&#162;',
		'GIP' => '&#163;',
		'GMD' => '&#68;', // ?
		'GNF' => '&#70;&#71;', // ?
		'GTQ' => '&#81;',
		'GYD' => '&#36;',
		'HKD' => '&#36;',
		'HNL' => '&#76;',
		'HRK' => '&#107;&#110;',
		'HTG' => '&#71;', // ?
		'HUF' => '&#70;&#116;',
		'IDR' => '&#82;&#112;',
		'ILS' => '&#8362;',
		'INR' => '&#8377;',
		'IQD' => '&#1593;.&#1583;', // ?
		'IRR' => '&#65020;',
		'ISK' => '&#107;&#114;',
		'JEP' => '&#163;',
		'JMD' => '&#74;&#36;',
		'JOD' => '&#74;&#68;', // ?
		'JPY' => '&#165;',
		'KES' => '&#75;&#83;&#104;', // ?
		'KGS' => '&#1083;&#1074;',
		'KHR' => '&#6107;',
		'KMF' => '&#67;&#70;', // ?
		'KPW' => '&#8361;',
		'KRW' => '&#8361;',
		'KWD' => '&#1583;.&#1603;', // ?
		'KYD' => '&#36;',
		'KZT' => '&#1083;&#1074;',
		'LAK' => '&#8365;',
		'LBP' => '&#163;',
		'LKR' => '&#8360;',
		'LRD' => '&#36;',
		'LSL' => '&#76;', // ?
		'LTL' => '&#76;&#116;',
		'LVL' => '&#76;&#115;',
		'LYD' => '&#1604;.&#1583;', // ?
		'MAD' => '&#1583;.&#1605;.', //?
		'MDL' => '&#76;',
		'MGA' => '&#65;&#114;', // ?
		'MKD' => '&#1076;&#1077;&#1085;',
		'MMK' => '&#75;',
		'MNT' => '&#8366;',
		'MOP' => '&#77;&#79;&#80;&#36;', // ?
		'MRO' => '&#85;&#77;', // ?
		'MUR' => '&#8360;', // ?
		'MVR' => '.&#1923;', // ?
		'MWK' => '&#77;&#75;',
		'MXN' => '&#36;',
		'MYR' => '&#82;&#77;',
		'MZN' => '&#77;&#84;',
		'NAD' => '&#36;',
		'NGN' => '&#8358;',
		'NIO' => '&#67;&#36;',
		'NOK' => '&#107;&#114;',
		'NPR' => '&#8360;',
		'NZD' => '&#36;',
		'OMR' => '&#65020;',
		'PAB' => '&#66;&#47;&#46;',
		'PEN' => '&#83;&#47;&#46;',
		'PGK' => '&#75;', // ?
		'PHP' => '&#8369;',
		'PKR' => '&#8360;',
		'PLN' => '&#122;&#322;',
		'PYG' => '&#71;&#115;',
		'QAR' => '&#65020;',
		'RON' => '&#108;&#101;&#105;',
		'RSD' => '&#1044;&#1080;&#1085;&#46;',
		'RUB' => '&#1088;&#1091;&#1073;',
		'RWF' => '&#1585;.&#1587;',
		'SAR' => '&#65020;',
		'SBD' => '&#36;',
		'SCR' => '&#8360;',
		'SDG' => '&#163;', // ?
		'SEK' => '&#107;&#114;',
		'SGD' => '&#36;',
		'SHP' => '&#163;',
		'SLL' => '&#76;&#101;', // ?
		'SOS' => '&#83;',
		'SRD' => '&#36;',
		'STD' => '&#68;&#98;', // ?
		'SVC' => '&#36;',
		'SYP' => '&#163;',
		'SZL' => '&#76;', // ?
		'THB' => '&#3647;',
		'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
		'TMT' => '&#109;',
		'TND' => '&#1583;.&#1578;',
		'TOP' => '&#84;&#36;',
		'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
		'TTD' => '&#36;',
		'TWD' => '&#78;&#84;&#36;',
		'TZS' => '',
		'UAH' => '&#8372;',
		'UGX' => '&#85;&#83;&#104;',
		'USD' => '&#36;',
		'UYU' => '&#36;&#85;',
		'UZS' => '&#1083;&#1074;',
		'VEF' => '&#66;&#115;',
		'VND' => '&#8363;',
		'VUV' => '&#86;&#84;',
		'WST' => '&#87;&#83;&#36;',
		'XAF' => '&#70;&#67;&#70;&#65;',
		'XCD' => '&#36;',
		'XDR' => '',
		'XOF' => '',
		'XPF' => '&#70;',
		'YER' => '&#65020;',
		'ZAR' => '&#82;',
		'ZMK' => '&#90;&#75;', // ?
		'ZWL' => '&#90;&#36;',
	);

	// return specific symbol as HTML
	if ( ! empty( $currency ) ) {
		return ( isset( $currency_symbols[$currency] ) ) ? $currency_symbols[$currency] : '';
	}

	// Default returns full array
	return $currency_symbols;
}
endif;

/**
 * Get the saved values for the Donations page and set defaults if no saved value.
 *
 * @param  boolean $field_name The single field value to return. Returns full array if false.
 * @return mixed               Array of all values or single value if $field_name is set.
 */
if ( ! function_exists( 'political_donations_get_options' ) ) :
function political_donations_get_options( $field_name = false ) {

	// Retrieve donations
	$options  = get_option( 'political_options_donations' );
	$all_options = ( ! empty($options) ) ? json_decode( $options, true ) : array();

	$defaults = array(
		'preset_donations'        => '5,15,*25,100',
		'currency_symbol'         => 'USD',
		'symbol_location'         => 'before',
		'title'                   => __('Donate To The Campaign', 'political-options'),
		'button'                  => __('Donate', 'political-options'),
		'paypal_mode'             => 'enabled',
		'paypal_transaction_type' => 'donation',
		'ninja_forms_integration' => 'disabled',
		'ninja_form_name'         => 0,
		'ninja_donation_page'     => 0
	);

	// Overwrite defaults with saved values
	// ------------------------------------------
	$settings = array_merge( $defaults, (array) $all_options );

	// Was a specific field value requested?
	if ( $field_name ) {
		$settings = ( isset( $settings[$field_name] ) ) ? $settings[$field_name] : '';
	}

	return apply_filters( 'political_donations_get_options', $settings );
}
endif;

/**
 * Get the saved values for the Donations page and set defaults if no saved value.
 *
 * @param  boolean $field_name The single field value to return. Returns full array if false.
 * @return mixed               Array of all values or single value if $field_name is set.
 */
if ( ! function_exists( 'political_settings_get_options' ) ) :
function political_settings_get_options( $field_name = false ) {

	// Retrieve donations
	$options = get_option( 'political_options_settings' );
	$all_options = ( ! empty( $options ) ) ? json_decode( $options, true ) : array();

	$defaults = array(
		// Permalinks
		'slug_issues'          			=> '',
		'slug_events'          			=> '',
		'slug_videos'          			=> '',
		// Landing pages
		'landing_enabled'      			=> 'disabled',
		'landing_redirect_id'  			=> 0,
		'landing_redirect_url' 			=> '',
		'landing_entry_page'   			=> 'home',
		'landing_timeout'      			=> '',
		'landing_cookies_reset'     	=> false, // should it be reset?
		'landing_cookies_reset_date' 	=> '', // date of last reset
		// Events
		'show_title_as_link'  			=> 'off'
	);

	// Overwrite defaults with saved values
	// ------------------------------------------
	$settings = array_merge( $defaults, (array) $all_options );

	// Was a specific field value requested?
	if ( $field_name ) {
		$settings = ( isset( $settings[$field_name] ) ) ? $settings[$field_name] : '';
	}

	return apply_filters( 'political_settings_get_options', $settings );
}
endif;

if ( ! function_exists( 'unescaped_json' ) ) :
function unescaped_json( $arr ) {
	return preg_replace_callback(
		'/\\\\u([0-9a-f]{4})/i',
		function ( $matches ) {
			$sym = mb_convert_encoding(
					pack( 'H*', $matches[1] ),
					'UTF-8',
					'UTF-16'
					);
			return $sym;
		},
		json_encode( $arr )
	);
}
endif;

if ( ! function_exists( 'political_donate_panel_shortcode' ) ) :
function political_donate_panel_shortcode( $atts ){
	$settings = political_donations_get_options();
	$amounts = explode( ',', $settings['preset_donations'] );
	$currency = pol_get_currency_symbols( $settings['currency_symbol'] );
	$hidden_fields = array();
	$form_action = '';

	// PayPal Settings
	if ( $settings['paypal_mode'] !== 'disabled' ) {
		// Submit URL
		$sub_domain = ( $settings['paypal_mode'] == 'sandbox' ) ? 'sandbox' : 'www';
		$form_action = 'https://'.$sub_domain.'.paypal.com/cgi-bin/webscr';
		// Hidden fields
		$hidden_fields['business'] = ( isset( $settings['paypal_email'] ) ) ? $settings['paypal_email'] : '';
		$hidden_fields['cmd'] = ( $settings['paypal_transaction_type'] == 'donation' ) ? '_donations' : '_xclick';
		$hidden_fields['item_name'] = ( isset( $settings['paypal_description'] ) ) ? $settings['paypal_description'] : '';
		$hidden_fields['currency_code'] = $settings['currency_symbol'];
		$hidden_fields['return'] = home_url( '/' );
	}

	// Ninja Forms Integration
	if ( $settings['ninja_forms_integration'] == 'enabled' ) {
		// Submit URL
		$form_action = get_page_link( $settings['ninja_donation_page'] );
		// Hidden fields
		$hidden_fields = array();
	}



	ob_start();	?>

	<div class="box-wrapper">
		<div class="box donate-box">

			<h3 class="text-left"><?php esc_attr_e( $settings['title'] ); ?></h3>

			<form class="donate-form" action="<?php echo esc_url( $form_action ) ?>" method="post">
				<ul class="amount-list">
					<?php
					if ( ! empty( $amounts ) ) {
						$i = 1;
						foreach ( $amounts as $amount ) {
							if ( ! empty( $amount ) ) {

								// check for default setting
								$selected = '';
								if ( strpos( $amount, "*" ) !== false ) {
									$selected = 'on';
									$amount = str_replace( "*", "", $amount );
								}
								$value = ( $settings['symbol_location'] == 'after' ) ? $amount . $currency : $currency . $amount;
								?>
								<li class="amount">
									<label for="amount-<?php esc_attr_e( $i ) ?>" class="btn btn-sm btn-clear <?php esc_attr_e( $selected ) ?>">
										<input name="amount" type="radio" id="amount-<?php esc_attr_e( $i ) ?>" value="<?php esc_attr_e( $amount ); ?>" <?php echo ( ! empty( $selected ) ) ? 'checked' : '' ?>>
										<span class="amount-text"><?php esc_attr_e( $value ) ?></span>
									</label>
								</li>
								<?php
								$i++; // increment
							}
						}
					}
					?>
				</ul>

				<?php
				// Output hidden fields
				if ( ! empty( $hidden_fields ) ) {

					foreach ( $hidden_fields as $name => $value ) {
						?>
						<input type="hidden" name="<?php esc_attr_e( $name ) ?>" value="<?php esc_attr_e( $value ) ?>">
						<?php
					}
				}
				?>

				<button type="submit" class="btn btn-default"><?php esc_attr_e( $settings['button'] ); ?></button>
			</form>
		</div>
	</div><?php

	return ob_get_clean();
}
endif;
add_shortcode( 'political_quick_donate', 'political_donate_panel_shortcode' );

if ( ! function_exists( 'political_newsletter_panel_shortcode' ) ) :
function political_newsletter_panel_shortcode( $atts ) {
	$form_id  = get_option( 'political_options_newsletter' );

	ob_start();	?>

	<div class="box-wrapper">
		<div class="box newsletter-box">
		<?php
		// Ninja Form
		if ( ! empty( $form_id ) && $form_id !== 'none' ) :
			?>
			<div class="row">
				<div class="col-sm-12">
					<div class="form-wrapper">
						<?php
						if ( function_exists( 'Ninja_Forms' ) ) {
							$ninja_forms = Ninja_Forms();

							if ( method_exists( $ninja_forms, 'display' ) ) {
								Ninja_Forms()->display( $form_id );
							} else if ( function_exists( 'ninja_forms_display_form' ) ) {
								ninja_forms_display_form( $form_id );
							}
						}
						?>
					</div>
				</div>
			</div>
			<?php
		endif;
		?>
		</div>
	</div><?php

	return ob_get_clean();
}
endif;
add_shortcode( 'political_newsletter', 'political_newsletter_panel_shortcode' );


// Set value of donate field passed from "Quick Donate" form
if ( ! function_exists( 'political_donate_nf_default_value_render' ) ) :
function political_donate_nf_default_value_render( $default_value, $field_type, $field_settings ) {
    global $wpdb;

    $amount = ( isset( $_POST['amount']) && ! empty( $_POST['amount'] ) ) ? (int) $_POST['amount'] : $default_value;

    if ( isset($field_settings['key']) && $field_settings['key'] == 'donate_amount' ) {
    	// a later evolution of NF v3 and updated donate form and PayPal code...
		$default_value = $amount;
    } else {
	    $field_id = $field_settings[ 'id' ];
	    $form_id = $wpdb->get_var( "SELECT `parent_id` FROM {$wpdb->prefix}nf3_fields WHERE `id` = {$field_id}" );
		$form_model = Ninja_Forms()->form( $form_id )->get();
		$amount_field = $form_model->get_setting( 'political_pp_amount' );

		// Set the default value
		if ( $field_id == $amount_field ) {
			if ( isset( $_POST['amount']) && ! empty( $_POST['amount'] ) ) {
				$amount = (int) $_POST['amount']; // for security cast to integer
				$default_value = $amount;
			}
		}
	}

	return $default_value;
}
endif;
add_filter( 'ninja_forms_render_default_value', 'political_donate_nf_default_value_render', 10, 3 );

if ( ! function_exists( 'datepicker_localization_rf' ) ) :
function datepicker_localization_rf() {
	global $wp_locale;

	$len_strip = 2;
	$date_format = get_option('date_format');

	$args = array(
	    'closeText'         		=> __( 'Close', 'political-options' ),
	    'currentText'       		=> __( 'Today', 'political-options' ),
	    'monthNames'        		=> strip_names_in_array( $wp_locale->month ),
	    'monthNamesShort'   		=> strip_names_in_array( $wp_locale->month_abbrev ),
	    'dayNames'          		=> strip_names_in_array( $wp_locale->weekday ),
	    'dayNamesShort'     		=> strip_names_in_array( $wp_locale->weekday_abbrev ),
	    'dayNamesMin'       		=> ( $wp_locale->text_direction == 'rtl' ) ? strip_names_in_array( $wp_locale->weekday_initial ) : strip_names_in_array( $wp_locale->weekday_abbrev, $len_strip ),
	    'dateFormat'        		=> convert_dateformat_PHP_to_jQueryUI( $date_format ),
	    'firstDay'         			=> get_option( 'start_of_week' ),
	    'isRTL'             		=> ( $wp_locale->text_direction == 'rtl' ) ? true : false,
	    'dates_validation_message' 	=> __( 'The end date cannot be before the start date', 'political-options' ),
	);

	return $args;
}
endif;

if ( ! function_exists( 'convert_dateformat_PHP_to_jQueryUI' ) ) :
function convert_dateformat_PHP_to_jQueryUI( $php_format ) {
    $SYMBOLS_MATCHING = array(
        // Day
        'd' => 'dd',
        'D' => 'D',
        'j' => 'd',
        'l' => 'DD',
        'N' => '',
        'S' => '',
        'w' => '',
        'z' => 'o',
        // Week
        'W' => '',
        // Month
        'F' => 'MM',
        'm' => 'mm',
        'M' => 'M',
        'n' => 'm',
        't' => '',
        // Year
        'L' => '',
        'o' => '',
        'Y' => 'yy',
        'y' => 'y',
        // Time
        'a' => '',
        'A' => '',
        'B' => '',
        'g' => '',
        'G' => '',
        'h' => '',
        'H' => '',
        'i' => '',
        's' => '',
        'u' => ''
    );

    $jqueryui_format = "";
    $escaping = false;
    for( $i = 0; $i < strlen( $php_format ); $i++) {
        $char = $php_format[$i];
        if($char === '\\') { // PHP date format escaping character
            $i++;
            if( $escaping )
            	$jqueryui_format .= $php_format[$i];
            else
            	$jqueryui_format .= '\'' . $php_format[$i];
            $escaping = true;
        }
        else {
            if( $escaping ) {
            	$jqueryui_format .= "'";
            	$escaping = false;
            }
            if( isset( $SYMBOLS_MATCHING[$char] ) )
                $jqueryui_format .= $SYMBOLS_MATCHING[$char];
            else
                $jqueryui_format .= $char;
        }
    }
    return $jqueryui_format;
}
endif;

if ( ! function_exists( 'strip_names_in_array' ) ) :
function strip_names_in_array( $old_array, $len = 0 ) {
    $new_array = array();
    foreach( $old_array as $item ) {
        $new_array[] = $len ? mb_substr( $item, 0, $len ) : $item;
    }
 
    return $new_array;
}
endif;