<?php

namespace MailjetPlugin;

/**
 * The plugin bootstrap file
 *
 *
 * @link              https://www.mailjet.com/partners/wordpress/
 * @since             5.0.0
 * @package           Mailjet
 *
 * @wordpress-plugin
 * Plugin Name:       Mailjet for WordPress
 * Plugin URI:        https://www.mailjet.com/partners/wordpress/
 * Description:       The Best WordPress Plugin For Email Newsletters.
 * Version:           5.0.7
 * Author:            Mailjet SAS
 * Author URI:        http://mailjet.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mailjet
 * Domain Path:       /languages
 */
/**
 * Copyright 2018  MAILJET  (email : plugins@mailjet.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Autoloading via composer
require_once __DIR__ . '/vendor/autoload.php';

//use Analog\Analog;
use MailjetPlugin\Includes\Mailjet;
use MailjetPlugin\Includes\MailjetUpdate;

/**
 * Mailjet plugin version.
 */
define('MAILJET_VERSION', '5.0.7');


// Call the update to V5 logic
MailjetUpdate::updateToV5();

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    5.0.0
 */
function run_mailjet()
{
    $plugin = new Mailjet();
    $plugin->run();
}


$activator = new MailjetActivator();
register_activation_hook( __FILE__, array( $activator, 'activation_check' ) );

run_mailjet();

/**
 * Triggered on mailjet plugin activation
 * Check if server php version is higher enough
 */
class MailjetActivator
{
    private $phpVersion;

    function __construct()
    {
        $this->phpVersion = phpversion();
        add_action('admin_init', array($this, 'check_version'));

        // Don't run anything else in the plugin, if we're on an incompatible WordPress version
        if (!self::compatible_version()) {
            return;
        }
    }

    // The primary sanity check, automatically disable the plugin on activation if it doesn't
    // meet minimum requirements.
    function activation_check()
    {
        if (!self::compatible_version()) {
            deactivate_plugins(plugin_basename(__FILE__));
            $message = sprintf(esc_html__('Mailjet for WordPress requires PHP 5.5 or later. Your server currently runs on PHP %s. Please upgrade your PHP and activate the plugin again.', 'wp-mailjet'), $this->phpVersion);
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
        $message = sprintf(esc_html__('Mailjet for WordPress requires PHP 5.5 or later. Your server currently runs on PHP %s. Please upgrade your PHP and activate the plugin again.', 'wp-mailjet'), $this->phpVersion);
        echo '<strong>' . $message . '</strong>';
    }

    static function compatible_version()
    {
        if (version_compare($this->phpVersion, '5.5', '<')) {
            return false;
        }

        // Add sanity checks for other version requirements here

        return true;
    }

}
