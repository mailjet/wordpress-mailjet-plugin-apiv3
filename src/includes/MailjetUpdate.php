<?php

namespace MailjetPlugin\Includes;

class MailjetUpdate
{
    public static function updateToV5()
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

        $mailjet_widget = get_option('widget_wp_mailjet_subscribe_widget');
        add_option('widget_mailjet', $mailjet_widget);

        $initSyncListId = get_option('mailjet_initial_sync_list_id');
        add_option('mailjet_sync_list', $initSyncListId);

        $commentAuthorsListId = get_option('mailjet_comment_authors_list_id');
        add_option('mailjet_comment_authors_list', $commentAuthorsListId);
        $autoSubscribeListId = get_option('mailjet_auto_subscribe_list_id');

        // Default settings
        add_option('mailjet_activate_logger', 0);
        add_option('settings_step', 'user_access_step');
        add_option('api_credentials_ok', 1);
        add_option('activate_mailjet_sync');
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

    public static function updateToV5_2() {
        $activateMailjetWooSync = get_option('activate_mailjet_woo_sync');
        if (empty($activateMailjetWooSync)) {
            return;
        }
        add_option('mailjet_woo_checkout_checkbox', $activateMailjetWooSync);
        delete_option('activate_mailjet_woo_sync');
    }

    public static function updateToV5_2_1() {
        $pluginVersion = get_option('mailjet_plugin_version');
        if (!empty($pluginVersion)) {
            return;
        }
        add_option('mailjet_plugin_version', '5.2.6');
        delete_option('mailjet_access_administrator');
        delete_option('mailjet_access_author');
        delete_option('mailjet_access_editor');
        delete_option('mailjet_access_contributor');
        delete_option('mailjet_access_subscriber');

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
    }
}
