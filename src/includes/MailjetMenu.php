<?php

namespace MailjetPlugin\Includes;

use MailjetIframe\MailjetIframe;
use MailjetPlugin\Includes\SettingsPages\ConnectAccountSettings;
use MailjetPlugin\Includes\SettingsPages\Dashboard;
use MailjetPlugin\Includes\SettingsPages\EnableSendingSettings;
use MailjetPlugin\Includes\SettingsPages\InitialContactListsSettings;
use MailjetPlugin\Includes\SettingsPages\InitialSettings;
use MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings;
use MailjetPlugin\Includes\SettingsPages\UserAccessSettings;
use Analog\Analog;

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

            if (function_exists('add_submenu_page')) {
                add_submenu_page('mailjet_settings_page', __('Initial mailjet API settings', 'mailjet'),
                    __('Settings', 'mailjet'), 'read', 'mailjet_settings_page', array($this, 'show_settings_page'));


                add_submenu_page('mailjet_settings_page', __('Manage your Mailjet lists', 'mailjet'),
                    __('Lists', 'mailjet'), 'read', 'mailjet_settings_contacts_menu',
                    array($this, 'show_contacts_page'));
                add_submenu_page('mailjet_settings_page', __('Manage your Mailjet campaigns', 'mailjet'),
                    __('Campaigns', 'mailjet'), 'read', 'mailjet_settings_campaigns_menu',
                    array($this, 'show_campaigns_page'));
                add_submenu_page('mailjet_settings_page', __('View your Mailjet statistics', 'mailjet'),
                    __('Statistics', 'mailjet'), 'read', 'mailjet_settings_stats_menu',
                    array($this, 'show_stats_page'));


                // Initial configuration pages
                add_submenu_page('mailjet_settings_page', __('Welcome to the Mailjet plugin for Wordpress', 'mailjet'), null, 'read', 'mailjet_initial_contact_lists_page',
                    array(new InitialContactListsSettings(), 'mailjet_initial_contact_lists_page_html'));


                // Dashboard page
                add_submenu_page('mailjet_settings_page', __('Welcome to the Mailjet plugin for Wordpress', 'mailjet'), null, 'read', 'mailjet_dashboard_page',
                    array(new Dashboard(), 'mailjet_dashboard_page_html'));


                // Settings pages
                add_submenu_page('mailjet_settings_page', __('Connect your Mailjet account', 'mailjet'), null, 'read', 'mailjet_connect_account_page',
                    array(new ConnectAccountSettings(), 'mailjet_connect_account_page_html'));
                add_submenu_page('mailjet_settings_page', __('Sending settings', 'mailjet'), null, 'read', 'mailjet_sending_settings_page',
                    array(new EnableSendingSettings(), 'mailjet_sending_settings_page_html'));
                add_submenu_page('mailjet_settings_page', __('Subscription options', 'mailjet'), null, 'read', 'mailjet_subscription_options_page',
                    array(new SubscriptionOptionsSettings(), 'mailjet_subscription_options_page_html'));
                add_submenu_page('mailjet_settings_page', __('User access', 'mailjet'), null, 'read', 'mailjet_user_access_page',
                    array(new UserAccessSettings(), 'mailjet_user_access_page_html'));
            }
        }
    }



    public function show_settings_page()
    {
        echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(dirname(__FILE__)) . '/admin/images/mj_logo_med.png" /></div><h2>';
        echo __('Welcome to the Mailjet plugin for Wordpress', 'mailjet');
        echo '</h2><div style="width:70%;float:left;">';

        $desc = '<ol>';
        $desc .= '<li>' . sprintf(__('<a target="_blank" href="https://www.mailjet.com/signup?aff=%s">Create your Mailjet account</a> if you don\'t have any.', 'mailjet'), 'wordpress-3.0') . '</li>';
        $desc .= '<li>' . __('Log in with your account through the login form below or visit your <a target="_blank" href="https://www.mailjet.com/account/api_keys">account page</a> to get your API keys and set up them below.', 'mailjet') . '</li>';
        $desc .= '<li>' . __('<a href="admin.php?page=mailjet_settings_contacts_menu">Create a new list</a> if you don\'t have one or need a new one.', 'mailjet') . '</li>';
        $desc .= '<li>' . __('<a href="widgets.php">Add</a> the email collection widget to your sidebar or footer.', 'mailjet') . '</li>';
        $desc .= '<li>' . __('<a href="admin.php?page=mailjet_settings_campaigns_menu">Create a campaign</a> on mailjet.com to send your newsletter.', 'mailjet') . '</li>';
        $desc .= '<li>' . __('Should you have any questions or encounter any difficulties, please consult our <a target="_blank" href="https://www.mailjet.com/guides/wordpress-user-guide/">User Guide</a> or contact our <a target="_blank" href="https://www.mailjet.com/support/ticket">technical Support Team</a>', 'mailjet') . '</li>';
        $desc .= '</ol>';

        echo $desc;

        echo '</div></div>';
    }



    private function getMailjetIframe()
    {
        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        $mj = new \Mailjet\Client($mailjetApikey, $mailjetApiSecret);

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
            ->turnNewContactListCreation(MailjetIframe::OFF)
            ->turnMenu(MailjetIframe::ON)
            ->turnFooter(MailjetIframe::OFF)
            ->turnBar(MailjetIframe::ON)
            ->turnCreateCampaignButton(MailjetIframe::ON)
            ->turnSendingPolicy(MailjetIframe::OFF);

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
            add_settings_error('mailjet_messages', 'mailjet_message', __('Invalid Mailjet API credentials', 'mailjet'), 'error');
            settings_errors('mailjet_messages');
        }
    }

    public function show_stats_page()
    {
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
            add_settings_error('mailjet_messages', 'mailjet_message', __('Invalid Mailjet API credentials', 'mailjet'), 'error');
            settings_errors('mailjet_messages');
        }
    }

    public function show_contacts_page()
    {
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
            add_settings_error('mailjet_messages', 'mailjet_message', __('Invalid Mailjet API credentials', 'mailjet'), 'error');
            settings_errors('mailjet_messages');
        }
    }

}
