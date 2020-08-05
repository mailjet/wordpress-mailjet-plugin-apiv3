<?php

namespace MailjetPlugin\Includes;

use MailjetIframe\MailjetIframe;
use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\SettingsPages\AbandonedCartSettings;
use MailjetPlugin\Includes\SettingsPages\AllSetup;
use MailjetPlugin\Includes\SettingsPages\ConnectAccountSettings;
use MailjetPlugin\Includes\SettingsPages\Dashboard;
use MailjetPlugin\Includes\SettingsPages\EnableSendingSettings;
use MailjetPlugin\Includes\SettingsPages\InitialContactListsSettings;
use MailjetPlugin\Includes\SettingsPages\InitialSettings;
use MailjetPlugin\Includes\SettingsPages\IntegrationsSettings;
use MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings;
use MailjetPlugin\Includes\SettingsPages\UserAccessSettings;
use MailjetPlugin\Includes\SettingsPages\OrderNotificationsSettings;


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
        if (current_user_can(UserAccessSettings::ACCESS_CAP_NAME)) {
            add_menu_page(
                __('Connect your Mailjet account to get started', 'mailjet-for-wordpress'),
                'Mailjet',
                'read',
                'mailjet_settings_page',
                array(new InitialSettings(), 'mailjet_initial_settings_page_html'),
                plugin_dir_url( dirname( __FILE__ ) ) . 'admin/images/mj_logo_small.png'
            );

            MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet settings menu added ]');

            if (function_exists('add_submenu_page')) {

                add_submenu_page(null, __('Manage your Mailjet lists', 'mailjet-for-wordpress'),
                    __('Lists', 'mailjet-for-wordpress'), 'read', 'mailjet_settings_contacts_menu',
                    array($this, 'show_contacts_page'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Manage your Mailjet lists\' sub-menu added ]');
                add_submenu_page(null, __('Manage your Mailjet campaigns', 'mailjet-for-wordpress'),
                    __('Campaigns', 'mailjet-for-wordpress'), 'read', 'mailjet_settings_campaigns_menu',
                    array($this, 'show_campaigns_page'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Manage your Mailjet campaigns\' sub-menu added ]');
                add_submenu_page(null, __('View your Mailjet statistics', 'mailjet-for-wordpress'),
                    __('Statistics', 'mailjet-for-wordpress'), 'read', 'mailjet_settings_stats_menu',
                    array($this, 'show_stats_page'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'View your Mailjet statistics\' sub-menu added ]');
                add_submenu_page(null, __('View your Mailjet template', 'mailjet-for-wordpress'),
                    __('Template', 'mailjet-for-wordpress'), 'read', 'mailjet_template',
                    array($this, 'show_template_page'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'View your Mailjet templatess\' sub-menu added ]');

                // Initial configuration pages
                add_submenu_page(null, __('Configure your lists.', 'mailjet-for-wordpress'), null, 'read', 'mailjet_initial_contact_lists_page',
                    array(new InitialContactListsSettings(), 'mailjet_initial_contact_lists_page_html'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Initial contact lists configuration sub-menu added ]');

                // All Setup page
                add_submenu_page(null, __('You\'re all set up!', 'mailjet-for-wordpress'), null, 'read', 'mailjet_allsetup_page',
                    array(new AllSetup(), 'mailjet_allsetup_page_html'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet All Setup sub-menu added ]');


                // Dashboard page
                add_submenu_page(null, __('Welcome to the Mailjet plugin for WordPress', 'mailjet-for-wordpress'), null, 'read', 'mailjet_dashboard_page',
                    array(new Dashboard(), 'mailjet_dashboard_page_html'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet Dashboard sub-menu added ]');

				// Order Notification page
	            add_submenu_page(null, __('Welcome to the Mailjet plugin for WordPress', 'mailjet-for-wordpress'), null, 'read', 'mailjet_order_notifications_page',
		            array(new OrderNotificationsSettings(), 'mailjet_order_notifications_settings_page_html'));
	            MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet Order Notifications sub-menu added ]');

                // Abandoned Cart page
                add_submenu_page(null, __('Welcome to the Mailjet plugin for WordPress', 'mailjet-for-wordpress'), null, 'read', 'mailjet_abandoned_cart_page',
                    array(new AbandonedCartSettings(), 'mailjet_abandoned_cart_settings_page_html'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Mailjet Order Notifications sub-menu added ]');



	            // Settings pages
                add_submenu_page(null, __('Connect your Mailjet account', 'mailjet-for-wordpress'), null, 'read', 'mailjet_connect_account_page',
                    array(new ConnectAccountSettings(), 'mailjet_connect_account_page_html'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Connect your Mailjet account\' sub-menu added ]');
                add_submenu_page(null, __('Sending settings', 'mailjet-for-wordpress'), null, 'read', 'mailjet_sending_settings_page',
                    array(new EnableSendingSettings(), 'mailjet_sending_settings_page_html'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Sending settings\' sub-menu added ]');
                add_submenu_page(null, __('Subscription options', 'mailjet-for-wordpress'), null, 'read', 'mailjet_subscription_options_page',
                    array(SubscriptionOptionsSettings::getInstance(), 'mailjet_subscription_options_page_html'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Subscription options\' sub-menu added ]');
                add_submenu_page(null, __('User access', 'mailjet-for-wordpress'), null, 'read', 'mailjet_user_access_page',
                    array(new UserAccessSettings(), 'mailjet_user_access_page_html'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'User access\' sub-menu added ]');
                add_submenu_page(null, __('Integrations', 'mailjet-for-wordpress'), null, 'read', 'mailjet_integrations_page',
                    array(new IntegrationsSettings(), 'mailjet_integrations_page_html'));
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'Integrations\' sub-menu added ]');

                // Add old initial page to fix settings link after update
                add_submenu_page(null, 'Temporary page', null, 'read', 'wp_mailjet_options_top_menu', array($this, 'wp_mailjet_options_top'));

            }
        } else {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have required permissions to see Mailjet plugin ]');
        }
    }

    public function wp_mailjet_options_top()
    {
        // Redirect to current initial page
        MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page&from=plugins'));
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

       // MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe prepared ]');

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
        if (!in_array($locale, array('fr_FR', 'en_US', 'en_GB', 'en_EU', 'de_DE', 'es_ES', 'it_IT'))) {
            $locale = 'en_US';
        }
        return $locale;
    }


    public function show_campaigns_page()
    {
       // MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Campaigns page requested ]');

        echo '<div class="mj-pluginPage iframePage">';

        try {
            $mailjetIframe = $this->getMailjetIframe();
            $mailjetIframe->setInitialPage(\MailjetIframe\MailjetIframe::PAGE_CAMPAIGNS);
            echo '<div id="initialSettingsHead">
                    <img src="' . plugin_dir_url(dirname(dirname(__FILE__))) . 'src/admin/images/LogoMJ_White_RVB.svg" alt="Mailjet Logo" />
                </div>
                <div class="mainContainer">';
            echo $mailjetIframe->getHtml();
            echo '</div>';
        } catch (\MailjetIframe\MailjetException $e) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' .  $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/api_keys">https://app.mailjet.com/account/api_keys</a>', 'mailjet-for-wordpress'), 'error');
            settings_errors('mailjet_messages');
            update_option('api_credentials_ok', 0);
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page'));
        }

        MailjetAdminDisplay::renderBottomLinks();
        echo '</div>';
      //  MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Campaigns page displayed ]');
    }


    public function show_stats_page()
    {
    //    MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Stats page requested ]');

        echo '<div class="mj-pluginPage iframePage">';

        try {
            $mailjetIframe = $this->getMailjetIframe();
            $mailjetIframe->setInitialPage(\MailjetIframe\MailjetIframe::PAGE_STATS);
            echo '<div id="initialSettingsHead">
                    <img src="' . plugin_dir_url(dirname(dirname(__FILE__))) . 'src/admin/images/LogoMJ_White_RVB.svg" alt="Mailjet Logo" />
                </div>
                <div class="mainContainer">';
            echo $mailjetIframe->getHtml();
            echo '</div>';
        } catch (\MailjetIframe\MailjetException $e) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' .  $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/api_keys">https://app.mailjet.com/account/api_keys</a>', 'mailjet-for-wordpress'), 'error');
            settings_errors('mailjet_messages');
            update_option('api_credentials_ok', 0);
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page'));
        }
        MailjetAdminDisplay::renderBottomLinks();
        echo '</div>';
      //  MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Stats page displayed ]');
    }


    public function show_contacts_page()
    {
     //   MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Contacts page requested ]');

        echo '<div class="mj-pluginPage iframePage">';

        try {
            $mailjetIframe = $this->getMailjetIframe();
            $mailjetIframe->setInitialPage(\MailjetIframe\MailjetIframe::PAGE_CONTACTS);
            echo '<div id="initialSettingsHead">
                    <img src="' . plugin_dir_url(dirname(dirname(__FILE__))) . 'src/admin/images/LogoMJ_White_RVB.svg" alt="Mailjet Logo" />
                </div>
                <div class="mainContainer">';
            echo $mailjetIframe->getHtml();
            echo '</div>';
        } catch (\MailjetIframe\MailjetException $e) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' .  $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/api_keys">https://app.mailjet.com/account/api_keys</a>', 'mailjet-for-wordpress'), 'error');
            settings_errors('mailjet_messages');
            update_option('api_credentials_ok', 0);
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page'));
        }
        MailjetAdminDisplay::renderBottomLinks();
        echo '</div>';
      //  MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Iframe Contacts page displayed ]');
    }

    public function show_template_page()
    {
        try {
            $mailjetIframe = $this->getMailjetIframe();
            $templateId = sanitize_text_field($_GET['id']);
            if (isset($templateId)) {
                $mailjetIframe->setInitialPage(\MailjetIframe\MailjetIframe::PAGE_EDIT_TEMPLATE, $templateId);
            }
            else {
                $mailjetIframe->setInitialPage(\MailjetIframe\MailjetIframe::PAGE_TEMPLATES);
            }
            $iframeHtml = $mailjetIframe->getHtml(true);
        } catch (\MailjetIframe\MailjetException $e) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' .  $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/api_keys">https://app.mailjet.com/account/api_keys</a>', 'mailjet-for-wordpress'), 'error');
            settings_errors('mailjet_messages');
            update_option('api_credentials_ok', 0);
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page'));
        }

        switch ($_GET['backto']) {
            case 'ordernotif' :
                $backButtonText = __('Back to order notification emails', 'mailjet-for-wordpress');
                $backButtonLink = 'admin.php?page=mailjet_order_notifications_page';
                break;
            case 'abandonedcart' :
                $backButtonText = __('Back to abandoned cart email', 'mailjet-for-wordpress');
                $backButtonLink = 'admin.php?page=mailjet_abandoned_cart_page';
                break;
            default :
                $backButtonText = __('Back to dashboard', 'mailjet-for-wordpress');
                $backButtonLink = 'admin.php?page=mailjet_dashboard_page';
                break;
        }
        set_query_var('iframeHtml', $iframeHtml);
        set_query_var('backButtonLink', $backButtonLink);
        set_query_var('backButtonText', $backButtonText);

        load_template(MAILJET_ADMIN_TAMPLATE_DIR . '/Iframe/longerIframePage.php');
    }
}
