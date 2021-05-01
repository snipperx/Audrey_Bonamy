<?php
/**
 * Redirect first time visitors to a landing page.
*/

// don't load directly
if ( !defined( 'ABSPATH' ) ) die( '-1' );

// The class
if ( ! class_exists( 'Political_Options_Landing_Pages' ) ) :
class Political_Options_Landing_Pages {

	private $cookiePrefix = 'po_landing_pages';
	private $cookie = false;

	public function __construct() {

		$this->init();
	}

	private function init() {

		// Actions
		add_action( 'wp', array( $this, 'landing_page_redirect' ) );

		// Settings
		$this->settings = political_settings_get_options();

	}

	function load_scripts($hook) {
		// Nothing here
	}

	/**
	 * Get the cookie name
	 */
	function cookieName() {

		return $this->cookiePrefix .'_'. $this->settings['landing_cookies_reset_date'];
	}

	/**
	 * Check the cookie data
	 */
	function getCookie() {

		$cookie_name = $this->cookieName();
		return ( isset( $_COOKIE[$cookie_name] ) && $_COOKIE[$cookie_name] ) ? $_COOKIE[$cookie_name] : false;
	}

	/**
	 * Perform the Redirect Actions
	 */
	function landing_page_redirect() {

		// Stop if disabled or we're in the WP admin
		if ( $this->settings['landing_enabled'] !== 'enabled' || is_admin() )
			return;

		// Retrieve cookie data
		$this->cookie = $this->getCookie();

		if ( $this->cookie ) {

			// Cookie set, so they've alerady visited the landing page
			do_action( 'political_options_landing_already_visited' );

		} else {

			// They have not visited the landing page yet, so we may need to redirect them.
			$entryPage = $this->settings['landing_entry_page'];
			if ( $entryPage == 'all' || ( $entryPage == 'home' && $this->is_real_frontpage() ) ) {

				$redirectURL = '';
				if ( $this->settings['landing_redirect_id'] == 'custom' ) {
					// Custom URL
					$redirectURL = $this->settings['landing_redirect_url'];
				} elseif ( ! empty($this->settings['landing_redirect_id']) ) {
					// WordPress page URL
					$redirectURL = get_page_link( $this->settings['landing_redirect_id'] );
				}

				// Make sure we have a valid redirect target
				if ( empty( $redirectURL ) )
					return;

				// Set a cookie for this
				$cookie_name = $this->cookieName();
				$timeout = (int) $this->settings['landing_timeout'];
				$expires = ( ! empty( $timeout ) ) ? time()+60*60*24*$timeout : time()+60*60*24*365; // default to 1 year (people don't like eternal cookies)
				setcookie( $cookie_name, true, $expires );
				// Do the redirect
				// header("Location: ". esc_url_raw($redirectURL) );
				// die();
				wp_redirect( esc_url_raw( $redirectURL ) );
				exit;
			}
		}
	}

	/**
	 * WordPress functions for is_home and is_front_page are not
	 * consistant in cases where the front page is set to use a static
	 * page, yet no selection for the page ID is made. When that
	 * happens the user sees the default "posts" home page but can
	 * still set a "blog" page. The result, testing for is_front_page
	 * will never return true but is_home will be true.
	 *
	 * This function let's us know if the home page we expect to be
	 * seeing is in fact what is visible.
	 *
	 */
	function is_real_frontpage() {

		if ( is_front_page() || is_home() ) {
			// Home Page, but not a 'page_for_posts'
			if ( get_option('show_on_front') == 'page' && (int) get_option( 'page_for_posts' ) === get_queried_object_ID() ) {
				// Do nothing. This is the blog page.
				return false;
			} elseif ( get_option( 'show_on_front' ) == 'page' && (int) get_option( 'page_on_front' ) === 0 ) {
				// It's the real home, with posts by default because no static pages is set.
				return true;
			} elseif ( get_option( 'show_on_front' ) == 'page') {
				// This is the home page, but it's a static page
				return false;
			}

			return true;
		}

		return false;
	}


} // end class Political_Options_Landing_Pages()
endif;  // if (!class_exists( 'Political_Options_Landing_Pages' ))
