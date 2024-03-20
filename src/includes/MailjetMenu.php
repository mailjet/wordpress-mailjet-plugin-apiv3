<?php

namespace MailjetWp\MailjetPlugin\Includes;

use MailjetWp\MailjetIframe\MailjetException;
use MailjetWp\MailjetIframe\MailjetIframe;
use MailjetWp\MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\AbandonedCartSettings;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\AllSetup;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\ConnectAccountSettings;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\Dashboard;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\EnableSendingSettings;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\InitialContactListsSettings;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\InitialSettings;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\IntegrationsSettings;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\UserAccessSettings;
use MailjetWp\MailjetPlugin\Includes\SettingsPages\OrderNotificationsSettings;

/**
 * Register all actions and filters for the plugin.
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 * @package    Mailjet
 * @subpackage Mailjet/includes
 * @author     Your Name <email@example.com>
 */
class MailjetMenu
{
    /**
     * Register the filters and actions with WordPress.
     * @since    5.0.0
     */
    public function display_menu()
    {
        if (current_user_can(UserAccessSettings::ACCESS_CAP_NAME)) {
            add_menu_page(__('Connect your Mailjet account to get started', 'mailjet-for-wordpress'), 'Mailjet', 'read', 'mailjet_settings_page', [new InitialSettings(), 'mailjet_initial_settings_page_html'], plugin_dir_url(__DIR__) . 'admin/images/mj_logo_small.png');
            if (\function_exists('add_submenu_page')) {
                add_submenu_page('', __('Manage your Mailjet lists', 'mailjet-for-wordpress'), __('Lists', 'mailjet-for-wordpress'), 'read', 'mailjet_settings_contacts_menu', [$this, 'show_contacts_page']);
                add_submenu_page('', __('Manage your Mailjet campaigns', 'mailjet-for-wordpress'), __('Campaigns', 'mailjet-for-wordpress'), 'read', 'mailjet_settings_campaigns_menu', [$this, 'show_campaigns_page']);
                add_submenu_page('', __('View your Mailjet statistics', 'mailjet-for-wordpress'), __('Statistics', 'mailjet-for-wordpress'), 'read', 'mailjet_settings_stats_menu', [$this, 'show_stats_page']);
                add_submenu_page('', __('View your Mailjet template', 'mailjet-for-wordpress'), __('Template', 'mailjet-for-wordpress'), 'read', 'mailjet_template', [$this, 'show_template_page']);
                // Initial configuration pages
                add_submenu_page('', __('Configure your lists.', 'mailjet-for-wordpress'), null, 'read', 'mailjet_initial_contact_lists_page', [new InitialContactListsSettings(), 'mailjet_initial_contact_lists_page_html']);
                // All Setup page
                add_submenu_page('', __('You\'re all set up!', 'mailjet-for-wordpress'), null, 'read', 'mailjet_allsetup_page', [new AllSetup(), 'mailjet_allsetup_page_html']);
                // Dashboard page
                add_submenu_page('', __('Welcome to the Mailjet plugin for WordPress', 'mailjet-for-wordpress'), null, 'read', 'mailjet_dashboard_page', [new Dashboard(), 'mailjet_dashboard_page_html']);
                // Order Notification page
                add_submenu_page('', __('Welcome to the Mailjet plugin for WordPress', 'mailjet-for-wordpress'), null, 'read', 'mailjet_order_notifications_page', [new OrderNotificationsSettings(), 'mailjet_order_notifications_settings_page_html']);
                // Abandoned Cart page
                add_submenu_page('', __('Welcome to the Mailjet plugin for WordPress', 'mailjet-for-wordpress'), null, 'read', 'mailjet_abandoned_cart_page', [new AbandonedCartSettings(), 'mailjet_abandoned_cart_settings_page_html']);
                // Settings pages
                add_submenu_page('', __('Connect your Mailjet account', 'mailjet-for-wordpress'), null, 'read', 'mailjet_connect_account_page', [new ConnectAccountSettings(), 'mailjet_connect_account_page_html']);
                add_submenu_page('', __('Sending settings', 'mailjet-for-wordpress'), null, 'read', 'mailjet_sending_settings_page', [new EnableSendingSettings(), 'mailjet_sending_settings_page_html']);
                add_submenu_page('', __('Subscription options', 'mailjet-for-wordpress'), null, 'read', 'mailjet_subscription_options_page', [SubscriptionOptionsSettings::getInstance(), 'mailjet_subscription_options_page_html']);
                add_submenu_page('', __('User access', 'mailjet-for-wordpress'), null, 'read', 'mailjet_user_access_page', [new UserAccessSettings(), 'mailjet_user_access_page_html']);
                add_submenu_page('', __('Integrations', 'mailjet-for-wordpress'), null, 'read', 'mailjet_integrations_page', [new IntegrationsSettings(), 'mailjet_integrations_page_html']);
                // Add old initial page to fix settings link after update
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

    /**
     * @return MailjetIframe
     * @throws MailjetException
     */
    private function getMailjetIframe(): MailjetIframe
    {
        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        $mailjetIframe = new MailjetIframe($mailjetApikey, $mailjetApiSecret, \false);
        $mailjetIframe->setCallback('')->setTokenExpiration(600)->setLocale($this->get_locale())->setTokenAccess(['campaigns', 'contacts', 'stats', 'transactional'])->turnDocumentationProperties(MailjetIframe::OFF)->turnNewContactListCreation(MailjetIframe::ON)->turnMenu(MailjetIframe::ON)->turnFooter(MailjetIframe::ON)->turnBar(MailjetIframe::ON)->turnCreateCampaignButton(MailjetIframe::ON)->turnSendingPolicy(MailjetIframe::ON);
        return $mailjetIframe;
    }

    /**
     * This method returns the current locale of the wordpress' user
     */
    private function get_locale()
    {
        $locale = get_locale();
        if (\in_array($locale, ['de_DE', 'de_DE_formal'])) {
            $locale = 'de_DE';
        }
        if (!\in_array($locale, ['fr_FR', 'en_US', 'en_GB', 'en_EU', 'de_DE', 'es_ES', 'it_IT'])) {
            $locale = 'en_US';
        }
        return $locale;
    }

    public function show_campaigns_page()
    {
        echo '<div class="mj-pluginPage iframePage">';
        try {
            $mailjetIframe = $this->getMailjetIframe();
            $mailjetIframe->setInitialPage(MailjetIframe::PAGE_CAMPAIGNS);
            echo '<div id="initialSettingsHead">
                    <img src="' . plugin_dir_url(dirname(__FILE__, 2)) . 'src/admin/images/LogoMJ_White_RVB.svg" alt="Mailjet Logo" />
                </div>
                <div class="mainContainer">';
            echo wp_kses($mailjetIframe->getHtml(), [
                'iframe' => [
                    'align' => true,
                    'width' => true,
                    'height' => true,
                    'frameborder' => true,
                    'name' => true,
                    'src' => true,
                    'id' => true,
                    'class' => true,
                    'style' => true,
                    'scrolling' => true,
                    'marginwidth' => true,
                    'marginheight' => true,
                ],
            ]);
            echo '</div>';
        } catch (MailjetException $e) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' . $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/apikeys">https://app.mailjet.com/account/apikeys</a>', 'mailjet-for-wordpress'), 'error');
            settings_errors('mailjet_messages');
            update_option('api_credentials_ok', 0);
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page'));
        }
        MailjetAdminDisplay::renderBottomLinks();
        echo '</div>';
    }

    public function show_stats_page()
    {
        echo '<div class="mj-pluginPage iframePage">';
        try {
            $mailjetIframe = $this->getMailjetIframe();
            $mailjetIframe->setInitialPage(MailjetIframe::PAGE_STATS);
            echo '<div id="initialSettingsHead">
                    <img src="' . plugin_dir_url(dirname(__FILE__, 2)) . 'src/admin/images/LogoMJ_White_RVB.svg" alt="Mailjet Logo" />
                </div>
                <div class="mainContainer">';
            echo wp_kses($mailjetIframe->getHtml(), [
                'iframe' => [
                    'src' => true,
                    'height' => true,
                    'width' => true,
                    'frameborder' => true,
                    'allowfullscreen' => true,
                ],
            ]);
            echo '</div>';
        } catch (MailjetException $e) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' . $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/apikeys">https://app.mailjet.com/account/apikeys</a>', 'mailjet-for-wordpress'), 'error');
            settings_errors('mailjet_messages');
            update_option('api_credentials_ok', 0);
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page'));
        }
        MailjetAdminDisplay::renderBottomLinks();
        echo '</div>';
    }

    public function show_contacts_page()
    {
        echo '<div class="mj-pluginPage iframePage">';
        try {
            $mailjetIframe = $this->getMailjetIframe();
            $mailjetIframe->setInitialPage(MailjetIframe::PAGE_CONTACTS);
            echo '<div id="initialSettingsHead">
                    <img src="' . plugin_dir_url(dirname(__FILE__, 2)) . 'src/admin/images/LogoMJ_White_RVB.svg" alt="Mailjet Logo" />
                </div>
                <div class="mainContainer">';
            echo wp_kses($mailjetIframe->getHtml(), [
                'iframe' => [
                    'src' => true,
                    'height' => true,
                    'width' => true,
                    'frameborder' => true,
                    'allowfullscreen' => true,
                ],
            ]);
            echo '</div>';
        } catch (MailjetException $e) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' . $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/apikeys">https://app.mailjet.com/account/apikeys</a>', 'mailjet-for-wordpress'), 'error');
            settings_errors('mailjet_messages');
            update_option('api_credentials_ok', 0);
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page'));
        }
        MailjetAdminDisplay::renderBottomLinks();
        echo '</div>';
    }

    public function show_template_page()
    {
        try {
            $mailjetIframe = $this->getMailjetIframe();
            $templateId = sanitize_text_field($_GET['id'] ?? '');
            if (isset($templateId)) {
                $mailjetIframe->setInitialPage(MailjetIframe::PAGE_EDIT_TEMPLATE, $templateId);
            } else {
                $mailjetIframe->setInitialPage(MailjetIframe::PAGE_TEMPLATES);
            }
            $iframeHtml = $mailjetIframe->getHtml(\true);
        } catch (MailjetException $e) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' . $e->getMessage() . ' ]');
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/apikeys">https://app.mailjet.com/account/apikeys</a>', 'mailjet-for-wordpress'), 'error');
            settings_errors('mailjet_messages');
            update_option('api_credentials_ok', 0);
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page'));
        }
        switch (sanitize_text_field($_GET['backto'])) {
            case 'ordernotif':
                $backButtonText = __('Back to order notification emails', 'mailjet-for-wordpress');
                $backButtonLink = 'admin.php?page=mailjet_order_notifications_page';
                break;
            case 'abandonedcart':
                $backButtonText = __('Back to abandoned cart email', 'mailjet-for-wordpress');
                $backButtonLink = 'admin.php?page=mailjet_abandoned_cart_page';
                break;
            default:
                $backButtonText = __('Back to dashboard', 'mailjet-for-wordpress');
                $backButtonLink = 'admin.php?page=mailjet_dashboard_page';
                break;
        }
        set_query_var('iframeHtml', $iframeHtml ?? '');
        set_query_var('backButtonLink', $backButtonLink);
        set_query_var('backButtonText', $backButtonText);
        load_template(MAILJET_ADMIN_TAMPLATE_DIR . '/Iframe/longerIframePage.php');
    }
}
