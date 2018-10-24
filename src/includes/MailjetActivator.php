<?php

namespace MailjetPlugin\Includes;

class MailjetActivator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    5.0.0
     */
    public static function activate()
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

        /**
         * mailjet_activate_logger
         * settings_step                user_access_step
         * api_credentials_ok           1
         * activate_mailjet_sync	1
         * activate_mailjet_initial_sync	
         * create_contact_list_btn	
         * create_list_name	
         * contacts_list_ok             1
         * mailjet_from_email_extra
         * mailjet_from_email_extra_hidden
         * send_test_email_btn
         * activate_mailjet_comment_authors_sync
         */
    }

}
