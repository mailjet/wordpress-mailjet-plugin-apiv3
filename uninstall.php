<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://example.com
 * @since      5.0.0
 *
 * @package    Mailjet
 */

// If uninstall not called from WordPress, then exit.
if (!defined( 'WP_UNINSTALL_PLUGIN')) {
	exit;
}


// Delete all form settings stored for Mailjet plugin
//
delete_option('mailjet_apikey');
delete_option('mailjet_apisecret');
delete_option('mailjet_activate_logger');
delete_option('settings_step');

delete_option('api_credentials_ok');
delete_option('contacts_list_ok');

delete_option('activate_mailjet_sync');
delete_option('mailjet_sync_list');
delete_option('activate_mailjet_initial_sync');
delete_option('create_contact_list_btn');
delete_option('create_list_name');

delete_option('mailjet_enabled');
delete_option('mailjet_from_name');
delete_option('mailjet_from_email');
delete_option('mailjet_port');
delete_option('mailjet_ssl');
delete_option('mailjet_from_email_extra');
delete_option('mailjet_from_email_extra_hidden');
delete_option('mailjet_test_address');
delete_option('mailjet_test_address');
delete_option('send_test_email_btn');

delete_option('activate_mailjet_comment_authors_sync');
delete_option('mailjet_comment_authors_list');

delete_option('mailjet_access_administrator');
delete_option('mailjet_access_editor');
delete_option('mailjet_access_author');
delete_option('mailjet_access_contributor');
delete_option('mailjet_access_subscriber');


delete_option('activate_mailjet_woo_integration');
delete_option('activate_mailjet_woo_sync');
delete_option('mailjet_woo_list');

delete_option('widget_wp_mailjet_subscribe_widget');

delete_option('mailjet_woo_abandoned_cart_activate');
delete_option('mailjet_woo_abandoned_cart_sending_time');
