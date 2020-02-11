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
delete_option('skip_mailjet_list');
delete_option('mailjet_post_update_message');

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

delete_option('activate_mailjet_cf7_integration');
delete_option('mailjet_cf7_list');
delete_option('cf7_email');
delete_option('cf7_fromname');

delete_option('activate_mailjet_woo_integration');

delete_option('widget_mailjet');
delete_option('widget_wp_mailjet_subscribe_widget');

delete_option('mailjet_thank_you_page_Italian');
delete_option('mailjet_thank_you_page_Spanish');
delete_option('mailjet_thank_you_page_German');
delete_option('mailjet_thank_you_page_English');
delete_option('mailjet_thank_you_page_French');
delete_option('mailjet_locale_subscription_list_it_IT');
delete_option('mailjet_locale_subscription_list_es_ES');
delete_option('mailjet_locale_subscription_list_en_US');
delete_option('mailjet_locale_subscription_list_fr_FR');
delete_option('mailjet_locale_subscription_list_de_DE');

delete_option('mailjet_woo_abandoned_cart_activate');
delete_option('mailjet_woo_abandoned_cart_sending_time');
delete_option('mailjet_woo_edata_sync');
delete_option('mailjet_woo_checkout_checkbox');
delete_option('mailjet_woo_checkout_box_text');
delete_option('mailjet_woo_banner_checkbox');
delete_option('mailjet_woo_banner_text');
delete_option('mailjet_woo_banner_label');
delete_option('mailjet_wc_abandoned_cart_active_hooks');
delete_option('mailjet_wc_active_hooks');
delete_option('mailjet_order_notifications');

delete_option('mailjet_woocommerce_abandoned_cart');
delete_option('mailjet_woocommerce_order_confirmation');
delete_option('mailjet_woocommerce_shipping_confirmation');
delete_option('mailjet_woocommerce_refund_confirmation');

delete_option('mailjet_widget_options');

// Delete all DB tables for Mailjet plugin
global $wpdb;
$sql_delete = "DROP TABLE IF EXISTS {$wpdb->prefix}mailjet_wc_abandoned_carts";
$wpdb->query($sql_delete);

$sql_delete = "DROP TABLE IF EXISTS {$wpdb->prefix}mailjet_wc_abandoned_cart_emails";
$wpdb->query($sql_delete);

$sql_delete = "DROP TABLE IF EXISTS {$wpdb->prefix}mailjet_wc_guests";
$wpdb->query($sql_delete);