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
 * Version:           5.2.6
 * Author:            Mailjet SAS
 * Author URI:        http://mailjet.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mailjet-for-wordpress
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
use MailjetPlugin\Includes\MailjetActivator;

/**
 * Mailjet plugin version.
 */
define('MAILJET_VERSION', '5.2.6');

/**
 * Mailjet Plugid dir.
 */
define('MAILJET_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('MAILJET_ADMIN_TAMPLATE_DIR', plugin_dir_path( __FILE__ ) . 'src/templates/admin');
define('MAILJET_FRONT_TEMPLATE_DIR', plugin_dir_path( __FILE__ ). 'src/templates/front');


// Call the update logic
MailjetUpdate::updateToV5();
MailjetUpdate::updateToV5_2();
MailjetUpdate::updateToV5_2_1();

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
register_activation_hook( __FILE__, array($activator, 'activation_check'));
register_activation_hook( __FILE__, array($activator, 'activation_settings'));


register_deactivation_hook( __FILE__, array( 'MailjetPlugin\Includes\MailjetDeactivator', 'deactivate' ) );

run_mailjet();

