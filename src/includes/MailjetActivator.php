<?php

namespace MailjetPlugin\Includes;

use MailjetPlugin\Includes\SettingsPages\UserAccessSettings;

/**
 * Triggered on mailjet plugin activation
 * Check if server php version is higher enough
 */
class MailjetActivator
{

    function __construct()
    {
        add_action('admin_init', array($this, 'check_version'));

        // Don't run anything else in the plugin, if we're on an incompatible WordPress version
        if (!self::compatible_version()) {
            return;
        }
    }

    // Set initial settings on plugin activation
    function activation_settings() {
         $adminRole = get_role('administrator');
         $adminRole->add_cap(UserAccessSettings::ACCESS_CAP_NAME, true);
    }

    // The primary sanity check, automatically disable the plugin on activation if it doesn't
    // meet minimum requirements.
    function activation_check()
    {
        if (!self::compatible_version()) {
            deactivate_plugins(plugin_basename(__FILE__));
            $phpVersion = phpversion();
            $message = sprintf(esc_html__('Mailjet for WordPress requires PHP 5.5 or later. Your server currently runs on PHP %s. Please upgrade your PHP and activate the plugin again.', 'mailjet-for-wordpress'), $phpVersion);
            wp_die($message);
        }
    }

    // The backup sanity check, in case the plugin is activated in a weird way,
    // or the versions change after activation.
    function check_version()
    {
        if (!self::compatible_version()) {
            if (is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(plugin_basename(__FILE__));
                add_action('admin_notices', array($this, 'disabled_notice'));
                if (isset($_GET['activate'])) {
                    unset($_GET['activate']);
                }
            }
        }
    }

    function disabled_notice()
    {
        $phpVersion = phpversion();
        $message = sprintf(esc_html__('Mailjet for WordPress requires PHP 5.5 or later. Your server currently runs on PHP %s. Please upgrade your PHP and activate the plugin again.', 'mailjet-for-wordpress'), $phpVersion);
        echo '<strong>' . $message . '</strong>';
    }

    static function compatible_version()
    {
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '5.5', '<')) {
            return false;
        }

        // Add sanity checks for other version requirements here

        return true;
    }

}