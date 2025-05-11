<?php

namespace MailjetWp\MailjetPlugin\Includes;

use MailjetWp\MailjetPlugin\Includes\SettingsPages\UserAccessSettings;

/**
 * Triggered on mailjet plugin activation
 * Check if server php version is higher enough
 */
class MailjetActivator {

    public function __construct() {
        add_action('admin_init', array( $this, 'check_version' ));
        // Don't run anything else in the plugin, if we're on an incompatible WordPress version
        if ( ! self::compatible_version()) {
            return;
        }
    }

    /**
     * @return void
     */
    public function activation_settings(): void {
        $adminRole = get_role('administrator');
        if ($adminRole) {
            $adminRole->add_cap(UserAccessSettings::ACCESS_CAP_NAME, \true);
        }
    }

    /**
     * The primary sanity check, automatically disable the plugin on activation if it doesn't
     * meet minimum requirements.
     */
    public function activation_check(): void {
        if ( ! self::compatible_version()) {
            deactivate_plugins(plugin_basename(__FILE__));
            $phpVersion = \phpversion();
            $message    = \sprintf(esc_html__('Mailjet for WordPress requires PHP 5.5 or later. Your server currently runs on PHP %s. Please upgrade your PHP and activate the plugin again.', 'mailjet-for-wordpress'), $phpVersion);
            wp_die($message);
        }
    }
    /**
     * The backup sanity check, in case the plugin is activated in a weird way,
     * or the versions change after activation.
     */
    public function check_version(): void {
        if ( ! self::compatible_version()) {
            if (is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(plugin_basename(__FILE__));
                add_action('admin_notices', array( $this, 'disabled_notice' ));
                if (isset($_GET['activate'])) {
                    unset($_GET['activate']);
                }
            }
        }
    }

    public function disabled_notice(): void {
        $phpVersion = \phpversion();
        $message    = \sprintf(esc_html__('Mailjet for WordPress requires PHP 5.5 or later. Your server currently runs on PHP %s. Please upgrade your PHP and activate the plugin again.', 'mailjet-for-wordpress'), $phpVersion);
        echo '<strong>' . $message . '</strong>';
    }

    /**
     * @return bool
     */
    public static function compatible_version(): bool {
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '7.4', '<')) {
            return false;
        }
        // Add sanity checks for other version requirements here
        return true;
    }
}
