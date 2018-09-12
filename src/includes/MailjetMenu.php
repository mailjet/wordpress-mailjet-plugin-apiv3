<?php

namespace MailjetPlugin\Includes;

use MailjetIframe\MailjetIframe;
use MailjetPlugin\Includes\SettingsPages\ConnectAccountSettings;
use MailjetPlugin\Includes\SettingsPages\Dashboard;
use MailjetPlugin\Includes\SettingsPages\AllSetup;
use MailjetPlugin\Includes\SettingsPages\EnableSendingSettings;
use MailjetPlugin\Includes\SettingsPages\InitialContactListsSettings;
use MailjetPlugin\Includes\SettingsPages\InitialSettings;
use MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings;
use MailjetPlugin\Includes\SettingsPages\UserAccessSettings;
use MailjetPlugin\Includes\MailjetApi;

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
class MailjetMenu
{
	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    5.0.0
	 */
    public function display_menu()
    {
        if (
            current_user_can('administrator')
            ||
            (current_user_can('editor') && get_option('mailjet_access_editor') == 1)
            ||
            (current_user_can('author') && get_option('mailjet_access_author') == 1)
            ||
            (current_user_can('contributor') && get_option('mailjet_access_contributor') == 1)
            ||
            (current_user_can('subscriber') && get_option('mailjet_access_subscriber') == 1)
        ) {

            add_menu_page(
                'Mailjet',
                'Mailjet',
                'manage_options',
                'mailjet_settings_page',
                array(new InitialSettings(), 'mailjet_initial_settings_page_html'),
                plugin_dir_url( dirname( __FILE__ ) ) . 'admin/images/mj_logo_small.png',
                20
            );

            \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet settings menu added ]');

            if (function_exists('add_submenu_page')) {
                add_submenu_page('mailjet_settings_page', __('Connect your Mailjet account to get started', 'mailjet'),
                    __('Settings', 'mailjet'), 'read', 'mailjet_settings_page', array($this, 'show_settings_page'));

                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Initial mailjet API settings sub-menu added ]');

                add_submenu_page(null, __('Manage your Mailjet lists', 'mailjet'),
                    __('Lists', 'mailjet'), 'read', 'mailjet_settings_contacts_menu',
                    array($this, 'show_contacts_page'));
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Manage your Mailjet lists\' sub-menu added ]');
                add_submenu_page(null, __('Manage your Mailjet campaigns', 'mailjet'),
                    __('Campaigns', 'mailjet'), 'read', 'mailjet_settings_campaigns_menu',
                    array($this, 'show_campaigns_page'));
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Manage your Mailjet campaigns\' sub-menu added ]');
                add_submenu_page(null, __('View your Mailjet statistics', 'mailjet'),
                    __('Statistics', 'mailjet'), 'read', 'mailjet_settings_stats_menu',
                    array($this, 'show_stats_page'));
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'View your Mailjet statistics\' sub-menu added ]');

                // Initial configuration pages
                add_submenu_page(null, __('Configure your lists.', 'mailjet'), null, 'read', 'mailjet_initial_contact_lists_page',
                    array(new InitialContactListsSettings(), 'mailjet_initial_contact_lists_page_html'));
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Initial contact lists configuration sub-menu added ]');

                // All Setup page
                add_submenu_page(null, __('You\'re all set up!', 'mailjet'), null, 'read', 'mailjet_allsetup_page',
                    array(new AllSetup(), 'mailjet_allsetup_page_html'));
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet All Setup sub-menu added ]');


                // Dashboard page
                add_submenu_page(null, __('Welcome to the Mailjet plugin for Wordpress', 'mailjet'), null, 'read', 'mailjet_dashboard_page_html',
                    array(new Dashboard(), 'mailjet_dashboard_page_html'));
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet Dashboard sub-menu added ]');


                // Settings pages
                add_submenu_page(null, __('Connect your Mailjet account', 'mailjet'), null, 'read', 'mailjet_connect_account_page',
                    array(new ConnectAccountSettings(), 'mailjet_connect_account_page_html'));
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Connect your Mailjet account\' sub-menu added ]');
                add_submenu_page(null, __('Sending settings', 'mailjet'), null, 'read', 'mailjet_sending_settings_page_html',
                    array(new EnableSendingSettings(), 'mailjet_sending_settings_page_html'));
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Sending settings\' sub-menu added ]');
                add_submenu_page(null, __('Subscription options', 'mailjet'), null, 'read', 'mailjet_subscription_options_page',
                    array(new SubscriptionOptionsSettings(), 'mailjet_subscription_options_page_html'));
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Subscription options\' sub-menu added ]');
                add_submenu_page(null, __('User access', 'mailjet'), null, 'read', 'mailjet_user_access_page_html',
                    array(new UserAccessSettings(), 'mailjet_user_access_page_html'));
                \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'User access\' sub-menu added ]');
            }
        } else {
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have required permissions to see Mailjet plugin ]');
        }
    }



    public function show_settings_page()
    {
//        echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(dirname(__FILE__)) . '/admin/images/mj_logo_med.png" /></div><h2>';
//        echo __('Welcome to the Mailjet plugin for Wordpress', 'mailjet');
//        echo '</h2><div style="width:70%;float:left;">';
//        echo __('Mailjet is an email service provider. With this plugin, easily send newsletters to your website users, directly from Wordpress.', 'mailjet');
//        echo '</div></div>';
    }



    private function getMailjetIframe()
    {
        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        $mailjetIframe = new MailjetIframe($mailjetApikey, $mailjetApiSecret, false);

        $mailjetIframe
            ->setCallback('')
            ->setTokenExpiration(600)
            ->setLocale($this->get_locale())
            ->setTokenAccess(array(
                'campaigns',
                'contacts',
                'stats',
            ))
            ->turnDocumentationProperties(MailjetIframe::OFF)
            ->turnNewContactListCreation(MailjetIframe::ON)
            ->turnMenu(MailjetIframe::ON)
            ->turnFooter(MailjetIframe::ON)
            ->turnBar(MailjetIframe::ON)
            ->turnCreateCampaignButton(MailjetIframe::ON)
            ->turnSendingPolicy(MailjetIframe::ON);

       // \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe prepared ]');

        return $mailjetIframe;
    }

    /**
     * This method returns the current locale of the wordpress' user
     */
    private function get_locale()
    {
        $locale = get_locale();
        if (in_array($locale, array('de_DE', 'de_DE_formal'))) {
            $locale = 'de_DE';
        }
        if (!in_array($locale, array('fr_FR', 'en_US', 'en_GB', 'en_EU', 'de_DE', 'es_ES'))) {
            $locale = 'en_US';
        }
        return $locale;
    }


    public function show_campaigns_page()
    {
       // \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Campaigns page requested ]');

        echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/admin/images/mj_logo_med.png' . '" /></div><h2>';
        echo __('Campaigns', 'mailjet');
        echo '</h2></div>';

        try {
            $mailjetIframe = $this->getMailjetIframe();
            $mailjetIframe->setInitialPage(\MailjetIframe\MailjetIframe::PAGE_CAMPAIGNS);
            echo '<div style="margin-left:0px; width:1040px; height:1260px;">';
            echo $mailjetIframe->getHtml();
            echo '</div>';
        } catch (\MailjetIframe\MailjetException $e) {
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' .  $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/api_keys">https://app.mailjet.com/account/api_keys</a>', 'mailjet'), 'error');
            settings_errors('mailjet_messages');
        }
      //  \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Campaigns page displayed ]');
    }


    public function show_stats_page()
    {
    //    \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Stats page requested ]');

        echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/admin/images/mj_logo_med.png' . '" /></div><h2>';
        echo __('Statistics', 'mailjet');
        echo '</div>';

        try {
            $mailjetIframe = $this->getMailjetIframe();
            $mailjetIframe->setInitialPage(\MailjetIframe\MailjetIframe::PAGE_STATS);
            echo '<div style="margin-left:0px; width:1040px; height:1260px;">';
            echo $mailjetIframe->getHtml();
            echo '</div>';
        } catch (\MailjetIframe\MailjetException $e) {
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' .  $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/api_keys">https://app.mailjet.com/account/api_keys</a>', 'mailjet'), 'error');
            settings_errors('mailjet_messages');
        }
      //  \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Stats page displayed ]');
    }


    public function show_contacts_page()
    {
     //   \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Contacts page requested ]');

        echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/admin/images/mj_logo_med.png' . '" /></div><h2>';
        echo __('Contacts', 'mailjet');
        echo '</h2></div>';

        try {
            $mailjetIframe = $this->getMailjetIframe();
            $mailjetIframe->setInitialPage(\MailjetIframe\MailjetIframe::PAGE_CONTACTS);
            echo '<div style="margin-left:0px; width:1040px; height:1260px;">';
            echo $mailjetIframe->getHtml();
            echo '</div>';
        } catch (\MailjetIframe\MailjetException $e) {
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' .  $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/api_keys">https://app.mailjet.com/account/api_keys</a>', 'mailjet'), 'error');
            settings_errors('mailjet_messages');
        }
      //  \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Contacts page displayed ]');
    }

}
