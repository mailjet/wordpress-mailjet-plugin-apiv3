<?php

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
 * Version:           5.0.0
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

use MailjetPlugin\Includes\Mailjet;
use MailjetPlugin\Includes\MailjetActivator;
use MailjetPlugin\Includes\MailjetDeactivator;
use Analog\Analog;

// Change the handler to any other if you need to.
//Analog::handler(\Analog\Handler\File::init(dirname(__FILE__) . '/logs.txt'));
Analog::handler(\Analog\Handler\ChromeLogger::init());

\MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . plugin_basename(__FILE__) . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet - Start ]');


/**
 * Currently plugin version.
 */
define('MAILJET_VERSION', '5.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/MailjetActivator.php
 */
function activate_mailjet() {
	MailjetActivator::activate();
    \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . plugin_basename(__FILE__) . ' ]  [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ activate_mailjet ]');
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/MailjetDeactivator.php
 */
function deactivate_mailjet() {
	MailjetDeactivator::deactivate();
    \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ]  [ ' . plugin_basename(__FILE__) . ' ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ deactivate_mailjet ]');
}

register_activation_hook( __FILE__, 'activate_mailjet' );
register_deactivation_hook( __FILE__, 'deactivate_mailjet' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    5.0.0
 */
function run_mailjet() {
	$plugin = new Mailjet();
    \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . plugin_basename(__FILE__) . ' ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet class initialized ]');
	$plugin->run();
}
run_mailjet();

\MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ]  [ ' . plugin_basename(__FILE__) . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet - End ]');