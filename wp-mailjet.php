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


function updateV5()
{
	$apikey = get_option('mailjet_apikey');
	$apisecret = get_option('mailjet_apisecret');

	// Check if transition from v4 to v5 is already done
	if ($apikey != FALSE && $apisecret != FALSE) {
		return true;
	}

	$username = get_option('mailjet_username');
	add_option('mailjet_apikey', $username);

	$password = get_option('mailjet_password');
	add_option('mailjet_apisecret', $password);

	// $mailjet_widget = get_option('widget_wp_mailjet_subscribe_widget');
	// add_option('widget_mailjet', $mailjet_widget);
	    $mailjet_widget = array (
  2 => 
  array (
    'en_US' => 
    array (
      'language_checkbox' => false,
      'title' => '',
      'list' => '0',
      'language_mandatory_email' => '',
      'language_mandatory_button' => '',
      'contactProperties0' => '',
      'propertyDataType0' => '0',
      'EnglishLabel0' => '',
      'FrenchLabel0' => '',
      'GermanLabel0' => '',
      'SpanishLabel0' => '',
      'ItalianLabel0' => '',
      'contactProperties1' => '',
      'propertyDataType1' => '0',
      'EnglishLabel1' => '',
      'FrenchLabel1' => '',
      'GermanLabel1' => '',
      'SpanishLabel1' => '',
      'ItalianLabel1' => '',
      'contactProperties2' => '',
      'propertyDataType2' => '0',
      'EnglishLabel2' => '',
      'FrenchLabel2' => '',
      'GermanLabel2' => '',
      'SpanishLabel2' => '',
      'ItalianLabel2' => '',
      'contactProperties3' => '',
      'propertyDataType3' => '0',
      'EnglishLabel3' => '',
      'FrenchLabel3' => '',
      'GermanLabel3' => '',
      'SpanishLabel3' => '',
      'ItalianLabel3' => '',
      'contactProperties4' => '',
      'propertyDataType4' => '0',
      'EnglishLabel4' => '',
      'FrenchLabel4' => '',
      'GermanLabel4' => '',
      'SpanishLabel4' => '',
      'ItalianLabel4' => '',
      'confirmation_email_message_input' => '',
      'subscription_confirmed_message_input' => '',
      'empty_email_message_input' => '',
      'already_subscribed_message_input' => '',
      'invalid_data_format_message_input' => '',
      'generic_technical_error_message_input' => '',
      'email_subject' => '',
      'email_content_title' => '',
      'email_content_main_text' => '',
      'email_content_confirm_button' => '',
      'email_content_after_button' => '',
    ),
    'English' => 
    array (
      'thank_you' => 0,
    ),
    'fr_FR' => 
    array (
      'language_checkbox' => false,
      'title' => '',
      'list' => '0',
      'language_mandatory_email' => '',
      'language_mandatory_button' => '',
      'contactProperties0' => '',
      'propertyDataType0' => '',
      'EnglishLabel0' => '',
      'FrenchLabel0' => '',
      'GermanLabel0' => '',
      'SpanishLabel0' => '',
      'ItalianLabel0' => '',
      'contactProperties1' => '',
      'propertyDataType1' => '',
      'EnglishLabel1' => '',
      'FrenchLabel1' => '',
      'GermanLabel1' => '',
      'SpanishLabel1' => '',
      'ItalianLabel1' => '',
      'contactProperties2' => '',
      'propertyDataType2' => '',
      'EnglishLabel2' => '',
      'FrenchLabel2' => '',
      'GermanLabel2' => '',
      'SpanishLabel2' => '',
      'ItalianLabel2' => '',
      'contactProperties3' => '',
      'propertyDataType3' => '',
      'EnglishLabel3' => '',
      'FrenchLabel3' => '',
      'GermanLabel3' => '',
      'SpanishLabel3' => '',
      'ItalianLabel3' => '',
      'contactProperties4' => '',
      'propertyDataType4' => '',
      'EnglishLabel4' => '',
      'FrenchLabel4' => '',
      'GermanLabel4' => '',
      'SpanishLabel4' => '',
      'ItalianLabel4' => '',
      'confirmation_email_message_input' => '',
      'subscription_confirmed_message_input' => '',
      'empty_email_message_input' => '',
      'already_subscribed_message_input' => '',
      'invalid_data_format_message_input' => '',
      'generic_technical_error_message_input' => '',
      'email_subject' => '',
      'email_content_title' => '',
      'email_content_main_text' => '',
      'email_content_confirm_button' => '',
      'email_content_after_button' => '',
    ),
    'French' => 
    array (
      'thank_you' => 0,
    ),
    'de_DE' => 
    array (
      'language_checkbox' => false,
      'title' => '',
      'list' => '0',
      'language_mandatory_email' => '',
      'language_mandatory_button' => '',
      'contactProperties0' => '',
      'propertyDataType0' => '',
      'EnglishLabel0' => '',
      'FrenchLabel0' => '',
      'GermanLabel0' => '',
      'SpanishLabel0' => '',
      'ItalianLabel0' => '',
      'contactProperties1' => '',
      'propertyDataType1' => '',
      'EnglishLabel1' => '',
      'FrenchLabel1' => '',
      'GermanLabel1' => '',
      'SpanishLabel1' => '',
      'ItalianLabel1' => '',
      'contactProperties2' => '',
      'propertyDataType2' => '',
      'EnglishLabel2' => '',
      'FrenchLabel2' => '',
      'GermanLabel2' => '',
      'SpanishLabel2' => '',
      'ItalianLabel2' => '',
      'contactProperties3' => '',
      'propertyDataType3' => '',
      'EnglishLabel3' => '',
      'FrenchLabel3' => '',
      'GermanLabel3' => '',
      'SpanishLabel3' => '',
      'ItalianLabel3' => '',
      'contactProperties4' => '',
      'propertyDataType4' => '',
      'EnglishLabel4' => '',
      'FrenchLabel4' => '',
      'GermanLabel4' => '',
      'SpanishLabel4' => '',
      'ItalianLabel4' => '',
      'confirmation_email_message_input' => '',
      'subscription_confirmed_message_input' => '',
      'empty_email_message_input' => '',
      'already_subscribed_message_input' => '',
      'invalid_data_format_message_input' => '',
      'generic_technical_error_message_input' => '',
      'email_subject' => '',
      'email_content_title' => '',
      'email_content_main_text' => '',
      'email_content_confirm_button' => '',
      'email_content_after_button' => '',
    ),
    'German' => 
    array (
      'thank_you' => 0,
    ),
    'es_ES' => 
    array (
      'language_checkbox' => false,
      'title' => '',
      'list' => '0',
      'language_mandatory_email' => '',
      'language_mandatory_button' => '',
      'contactProperties0' => '',
      'propertyDataType0' => '',
      'EnglishLabel0' => '',
      'FrenchLabel0' => '',
      'GermanLabel0' => '',
      'SpanishLabel0' => '',
      'ItalianLabel0' => '',
      'contactProperties1' => '',
      'propertyDataType1' => '',
      'EnglishLabel1' => '',
      'FrenchLabel1' => '',
      'GermanLabel1' => '',
      'SpanishLabel1' => '',
      'ItalianLabel1' => '',
      'contactProperties2' => '',
      'propertyDataType2' => '',
      'EnglishLabel2' => '',
      'FrenchLabel2' => '',
      'GermanLabel2' => '',
      'SpanishLabel2' => '',
      'ItalianLabel2' => '',
      'contactProperties3' => '',
      'propertyDataType3' => '',
      'EnglishLabel3' => '',
      'FrenchLabel3' => '',
      'GermanLabel3' => '',
      'SpanishLabel3' => '',
      'ItalianLabel3' => '',
      'contactProperties4' => '',
      'propertyDataType4' => '',
      'EnglishLabel4' => '',
      'FrenchLabel4' => '',
      'GermanLabel4' => '',
      'SpanishLabel4' => '',
      'ItalianLabel4' => '',
      'confirmation_email_message_input' => '',
      'subscription_confirmed_message_input' => '',
      'empty_email_message_input' => '',
      'already_subscribed_message_input' => '',
      'invalid_data_format_message_input' => '',
      'generic_technical_error_message_input' => '',
      'email_subject' => '',
      'email_content_title' => '',
      'email_content_main_text' => '',
      'email_content_confirm_button' => '',
      'email_content_after_button' => '',
    ),
    'Spanish' => 
    array (
      'thank_you' => 0,
    ),
    'it_IT' => 
    array (
      'language_checkbox' => false,
      'title' => '',
      'list' => '0',
      'language_mandatory_email' => '',
      'language_mandatory_button' => '',
      'contactProperties0' => '',
      'propertyDataType0' => '',
      'EnglishLabel0' => '',
      'FrenchLabel0' => '',
      'GermanLabel0' => '',
      'SpanishLabel0' => '',
      'ItalianLabel0' => '',
      'contactProperties1' => '',
      'propertyDataType1' => '',
      'EnglishLabel1' => '',
      'FrenchLabel1' => '',
      'GermanLabel1' => '',
      'SpanishLabel1' => '',
      'ItalianLabel1' => '',
      'contactProperties2' => '',
      'propertyDataType2' => '',
      'EnglishLabel2' => '',
      'FrenchLabel2' => '',
      'GermanLabel2' => '',
      'SpanishLabel2' => '',
      'ItalianLabel2' => '',
      'contactProperties3' => '',
      'propertyDataType3' => '',
      'EnglishLabel3' => '',
      'FrenchLabel3' => '',
      'GermanLabel3' => '',
      'SpanishLabel3' => '',
      'ItalianLabel3' => '',
      'contactProperties4' => '',
      'propertyDataType4' => '',
      'EnglishLabel4' => '',
      'FrenchLabel4' => '',
      'GermanLabel4' => '',
      'SpanishLabel4' => '',
      'ItalianLabel4' => '',
      'confirmation_email_message_input' => '',
      'subscription_confirmed_message_input' => '',
      'empty_email_message_input' => '',
      'already_subscribed_message_input' => '',
      'invalid_data_format_message_input' => '',
      'generic_technical_error_message_input' => '',
      'email_subject' => '',
      'email_content_title' => '',
      'email_content_main_text' => '',
      'email_content_confirm_button' => '',
      'email_content_after_button' => '',
    ),
    'Italian' => 
    array (
      'thank_you' => 0,
    ),
  ),
  '_multiwidget' => 1,
);
    add_option('widget_mailjet', $mailjet_widget);

	$initSyncListId = get_option('mailjet_initial_sync_list_id');
	add_option('mailjet_sync_list', $initSyncListId);

	$commentAuthorsListId = get_option('mailjet_comment_authors_list_id');
	add_option('mailjet_comment_authors_list', $commentAuthorsListId);

	// Default settings
	add_option('mailjet_activate_logger', 0);
	add_option('settings_step', 'user_access_step');
	add_option('api_credentials_ok', 1);
	add_option('activate_mailjet_sync', 1);
	add_option('activate_mailjet_initial_sync');
	add_option('create_contact_list_btn');
	add_option('create_list_name');

	// If no list set, contact list can not be ok
	$isContactListOk = $initSyncListId > 0 ? 1 : '';
	add_option('contacts_list_ok', $isContactListOk);
	add_option('mailjet_from_email_extra');
	add_option('mailjet_from_email_extra_hidden');
	add_option('send_test_email_btn');
	$authorSync = $commentAuthorsListId > 0 ? 1 : '';
	add_option('activate_mailjet_comment_authors_sync', $authorSync);

	// Delete unused options
	$deleteOptions = array(
		'mailjet_username',
		'mailjet_password',
		// 'widget_wp_mailjet_subscribe_widget',
		'mailjet_initial_sync_list_id',
		'mailjet_comment_authors_list_id',
		'mailjet_initial_sync_last_date',
		'mailjet_comment_authors_list_date',
		'mailjet_auto_subscribe_list_id',
		'mailjet_user_api_version'
	);

	foreach ($deleteOptions as $option) {
		delete_option($option);
	}
}
updateV5();

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
