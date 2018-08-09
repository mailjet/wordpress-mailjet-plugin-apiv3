<?php

namespace MailjetPlugin\Includes;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Mailjet
 * @subpackage Mailjet/includes
 * @author     Your Name <email@example.com>
 */
class MailjetSettings
{
    /**
     * custom option and settings
     *  IMPORTANT - add each setting here, in order to be processed by the WP Settings API
     */
    public function mailjet_settings_init()
    {
        // register a new setting for "mailjet" page
        register_setting('mailjet_settings_page', 'mailjet_apikey');
        register_setting('mailjet_settings_page', 'mailjet_apisecret');
        register_setting('mailjet_settings_page', 'settings_step');

        register_setting('mailjet_sending_settings_page', 'mailjet_enabled');
        register_setting('mailjet_sending_settings_page', 'settings_step');

        register_setting('mailjet_connect_account_page', 'mailjet_connect_account');
        register_setting('mailjet_subscription_options_page', 'mailjet_subscription_options');

        register_setting('mailjet_user_access_page', 'mailjet_access_administrator');
        register_setting('mailjet_user_access_page', 'mailjet_access_editor');
        register_setting('mailjet_user_access_page', 'mailjet_access_author');
        register_setting('mailjet_user_access_page', 'mailjet_access_contributor');
        register_setting('mailjet_user_access_page', 'mailjet_access_subscriber');
        register_setting('mailjet_user_access_page', 'settings_step');


    }

}
