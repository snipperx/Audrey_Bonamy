<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class PoliticalOptions_PayPal_Admin_Settings
 */
final class PoliticalOptions_PayPal_Admin_Settings
{
    public function __construct()
    {
        add_filter( 'ninja_forms_plugin_settings',                  array( $this, 'plugin_settings'             ), 10, 1 );
        add_filter( 'ninja_forms_plugin_settings_groups',           array( $this, 'plugin_settings_groups'      ), 10, 1 );
    }

    public function plugin_settings( $settings )
    {
        $settings[ 'paypal_express' ] = PoliticalOptions_PayPal()->config( 'PluginSettings' );

        return $settings;
    }

    public function plugin_settings_groups( $groups )
    {
        $groups = array_merge( $groups, PoliticalOptions_PayPal()->config( 'PluginSettingsGroups' ) );
        return $groups;
    }

} // End Class PoliticalOptions_PayPal_Admin_Settings
