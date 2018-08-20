<?php

namespace MailjetPlugin\Includes;

use MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings;

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
        \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Settings Init Start]');


        // Redirect the user to the Dashboard if he already configured his initial settings
        $currentPage = $_REQUEST['page'] ? $_REQUEST['page'] : null;
        if ('mailjet_settings_page' == $currentPage && !empty(get_option('mailjet_apikey')) && !empty(get_option('mailjet_apisecret'))) {
            if (!empty(get_option('mailjet_sync_list'))) {
                //wp_redirect(admin_url('/admin.php?page=mailjet_dashboard_page'));
                //exit;
            }
//            wp_redirect(admin_url('/admin.php?page=mailjet_initial_contact_lists_page'));
//            exit;
        }
        // If defined some contact list settings the we skip that page
        if ('mailjet_initial_contact_lists_page' == $currentPage && get_option('settings_step') == 'initial_contact_lists_settings_step') {
            //wp_redirect(admin_url('/admin.php?page=mailjet_dashboard_page'));
            //exit;
        }

        $this->addMailjetActions();

        // register a new setting for "mailjet" page
        register_setting('mailjet_initial_settings_page', 'mailjet_apikey');
        register_setting('mailjet_initial_settings_page', 'mailjet_apisecret');
        register_setting('mailjet_initial_settings_page', 'mailjet_activate_logger');
        register_setting('mailjet_initial_settings_page', 'settings_step');

        register_setting('mailjet_initial_contact_lists_page', 'activate_mailjet_sync');
        register_setting('mailjet_initial_contact_lists_page', 'mailjet_sync_list');
        register_setting('mailjet_initial_contact_lists_page', 'activate_mailjet_initial_sync');
        register_setting('mailjet_initial_contact_lists_page', 'create_contact_list_btn');
        register_setting('mailjet_initial_contact_lists_page', 'list_name');
        register_setting('mailjet_initial_contact_lists_page', 'settings_step');





        register_setting('mailjet_connect_account_page', 'mailjet_apikey');
        register_setting('mailjet_connect_account_page', 'mailjet_apisecret');
        register_setting('mailjet_connect_account_page', 'settings_step');

        register_setting('mailjet_sending_settings_page', 'mailjet_enabled');
        register_setting('mailjet_sending_settings_page', 'mailjet_from_name');
        register_setting('mailjet_sending_settings_page', 'mailjet_from_email');
        register_setting('mailjet_sending_settings_page', 'mailjet_port');
        register_setting('mailjet_sending_settings_page', 'mailjet_ssl');
        register_setting('mailjet_sending_settings_page', 'mailjet_from_email_extra');
        register_setting('mailjet_sending_settings_page', 'mailjet_from_email_extra_hidden');
        register_setting('mailjet_sending_settings_page', 'mailjet_test_address');
        register_setting('mailjet_sending_settings_page', 'send_test_email_btn');
        register_setting('mailjet_sending_settings_page', 'settings_step');

        register_setting('mailjet_subscription_options_page', 'activate_mailjet_sync');
        register_setting('mailjet_subscription_options_page', 'mailjet_sync_list');
        register_setting('mailjet_subscription_options_page', 'activate_mailjet_initial_sync');
        register_setting('mailjet_subscription_options_page', 'activate_mailjet_comment_authors_sync');
        register_setting('mailjet_subscription_options_page', 'mailjet_comment_authors_list');
        register_setting('mailjet_subscription_options_page', 'settings_step');

        register_setting('mailjet_user_access_page', 'mailjet_access_administrator');
        register_setting('mailjet_user_access_page', 'mailjet_access_editor');
        register_setting('mailjet_user_access_page', 'mailjet_access_author');
        register_setting('mailjet_user_access_page', 'mailjet_access_contributor');
        register_setting('mailjet_user_access_page', 'mailjet_access_subscriber');
        register_setting('mailjet_user_access_page', 'settings_step');


        \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Settings Init End ]');
    }


    /**
     * Adding a Mailjet logic and functionality to some WP actions - for example - inserting checkboxes for subscription
     */
    private function addMailjetActions()
    {
        \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Adding some custom mailjet logic to WP actions - Start ]');

        if (!empty(get_option('activate_mailjet_sync')) && !empty(get_option('mailjet_sync_list'))) {

            $subscriptionOptionsSettings = new SubscriptionOptionsSettings();

            // When user is viewing another users profile page (not their own).
            add_action('edit_user_profile', array($subscriptionOptionsSettings, 'mailjet_show_extra_profile_fields'));
            // - If you want to apply your hook to ALL profile pages (including the current user) then you also need to use this one.
            add_action('show_user_profile', array($subscriptionOptionsSettings, 'mailjet_show_extra_profile_fields'));

            // Runs just before the end of the new user registration form.
            add_action('register_form', array($subscriptionOptionsSettings, 'mailjet_show_extra_profile_fields'));
            // Runs near the end of the "Add New" user screen.
            add_action('user_new_form', array($subscriptionOptionsSettings, 'mailjet_show_extra_profile_fields'));

            // Runs when a user updates personal options from the admin screen.
            add_action('personal_options_update', array($subscriptionOptionsSettings, 'mailjet_my_save_extra_profile_fields'));
            // Runs at the end of the Personal Options section of the user profile editing screen.
            add_action('profile_personal_options ', array($subscriptionOptionsSettings, 'mailjet_my_save_extra_profile_fields'));
            //
            add_action('edit_user_profile_update', array($subscriptionOptionsSettings, 'mailjet_my_save_extra_profile_fields'));
            // Runs when a user's profile is updated. Action function argument: user ID.
            add_action('profile_update', array($subscriptionOptionsSettings, 'mailjet_save_extra_profile_fields'));
            // Runs immediately after the new user is added to the database.
            add_action('user_register', array($this, array($subscriptionOptionsSettings, 'mailjet_save_extra_profile_fields')));
        }


        /* Add custom field to comment form and process it on form submit */
        if (!empty(get_option('activate_mailjet_comment_authors_sync')) && !empty(get_option('mailjet_comment_authors_list'))) {
            add_action('comment_form_after_fields', array($subscriptionOptionsSettings, 'mailjet_show_extra_comment_fields'));
            add_action('wp_insert_comment',array($subscriptionOptionsSettings, 'mailjet_subscribe_comment_author'));

            \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Comment Authors Sync active - added custome actions to sync them ]');

            // Verify the token from the confirmation email link and subscribe the comment author to the Mailjet contacts list
            if (!empty($_GET['mj_sub_comment_author_token'])
                &&
                $_GET['mj_sub_comment_author_token'] == sha1($_GET['subscribe'] . str_ireplace(' ', '+', $_GET['user_email']))) {
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Subscribe/Unsubscribe Comment Author To List ]');
                $subscriptionOptionsSettings->mailjet_subscribe_unsub_comment_author_to_list($_GET['subscribe'], str_ireplace(' ', '+', $_GET['user_email']));
            }
        }


        // Add a Link to Mailjet settings page next to the activate/deactivate links in WP Plugins page
        add_filter('plugin_action_links', array($this, 'mailjet_settings_link'), 10, 2);

        \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Adding some custom mailjet logic to WP actions - End ]');

    }



    /**
     * Display settings link on plugins page
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function mailjet_settings_link($links, $file)
    {
        if ($file != plugin_basename(dirname(dirname(dirname(__FILE__)))) . '/mailjet.php') {
            return $links;
        }

        $settings_link = '<a href="admin.php?page=mailjet_dashboard_page">' . __('Settings', 'mailjet') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }


}
