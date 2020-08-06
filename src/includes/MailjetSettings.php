<?php

namespace MailjetPlugin\Includes;

use MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings;
use MailjetPlugin\Includes\SettingsPages\WooCommerceSettings;
use MailjetPlugin\Includes\SettingsPages\ContactForm7Settings;
use MailjetPlugin\Includes\SettingsPages\CommentAuthorsSettings;

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
    public function mailjet_settings_admin_init()
    {
        // register a new settings for Mailjet pages
        register_setting('mailjet_initial_settings_page', 'mailjet_apikey');
        register_setting('mailjet_initial_settings_page', 'mailjet_apisecret');
        register_setting('mailjet_initial_settings_page', 'mailjet_activate_logger');
        register_setting('mailjet_initial_settings_page', 'settings_step');

        register_setting('mailjet_initial_contact_lists_page', 'activate_mailjet_sync');
        register_setting('mailjet_initial_contact_lists_page', 'mailjet_sync_list');
        register_setting('mailjet_initial_contact_lists_page', 'activate_mailjet_initial_sync');
        register_setting('mailjet_initial_contact_lists_page', 'create_contact_list_btn');
        register_setting('mailjet_initial_contact_lists_page', 'create_list_name');
        register_setting('mailjet_initial_contact_lists_page', 'settings_step');
        register_setting('mailjet_initial_contact_lists_page', 'skip_mailjet_list');

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

        register_setting('mailjet_user_access_page', 'settings_step');

        // Woo integration
        register_setting('mailjet_integrations_page', 'activate_mailjet_woo_integration');
        register_setting('mailjet_integrations_page', 'mailjet_woo_edata_sync');
        register_setting('mailjet_integrations_page', 'mailjet_woo_checkout_checkbox');
        register_setting('mailjet_integrations_page', 'mailjet_woo_checkout_box_text');
        register_setting('mailjet_integrations_page', 'mailjet_woo_banner_checkbox');
        register_setting('mailjet_integrations_page', 'mailjet_woo_banner_text');
        register_setting('mailjet_integrations_page', 'mailjet_woo_banner_label');

        // Contact Form 7 integration
        register_setting('mailjet_integrations_page', 'activate_mailjet_cf7_integration');
        register_setting('mailjet_integrations_page', 'activate_mailjet_cf7_sync');
        register_setting('mailjet_integrations_page', 'mailjet_cf7_list');
        register_setting('mailjet_integrations_page', 'cf7_email');
        register_setting('mailjet_integrations_page', 'cf7_fromname');
        register_setting('mailjet_integrations_page', 'settings_step');
    }

    public function mailjet_settings_init()
    {
        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Settings Init Start]');

        $this->addMailjetActions();

        $this->addSubscriptionConfirmations();

        $currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : null;
        $fromPage = !empty($_REQUEST['from']) ? $_REQUEST['from'] : null;
        if (in_array($currentPage, array(
                'mailjet_allsetup_page',
                'mailjet_dashboard_page',
                'mailjet_user_access_page',
                'mailjet_integrations_page',
                'mailjet_subscription_options_page',
                'mailjet_sending_settings_page',
                'mailjet_connect_account_page',
                'mailjet_initial_contact_lists_page',
                'mailjet_settings_page'
            ))) {
            $apiCredentialsOk = get_option('api_credentials_ok');
            if (!($fromPage == 'plugins') && !empty($apiCredentialsOk) && '1' != $apiCredentialsOk) {
                MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page'));
            }
        }
        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Settings Init End ]');
    }

    /**
     * Adding a Mailjet logic and functionality to some WP actions - for example - inserting checkboxes for subscription
     */
    private function addMailjetActions()
    {
        $activate_mailjet_sync = get_option('activate_mailjet_sync');
        $mailjet_sync_list = get_option('mailjet_sync_list');
        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Adding some custom mailjet logic to WP actions - Start ]');
        if (!empty($activate_mailjet_sync) && !empty($mailjet_sync_list)) {
            $subscriptionOptionsSettings = SubscriptionOptionsSettings::getInstance();

            // Check after login if the user is subscribed to the contact list
            add_action('wp_login', array($subscriptionOptionsSettings, 'checkUserSubscription'), 10, 2);

            // When user is viewing another users profile page (not their own).
            add_action('edit_user_profile', array($subscriptionOptionsSettings, 'mailjet_show_extra_profile_fields'));
            // - If you want to apply your hook to ALL profile pages (including the current user) then you also need to use this one.
            add_action('show_user_profile', array($subscriptionOptionsSettings, 'mailjet_show_extra_profile_fields'));
            // Runs just before the end of the new user registration form.
            if (get_option('activate_mailjet_woo_integration') === '1') {
                add_action('woocommerce_edit_account_form', array($subscriptionOptionsSettings, 'mailjet_show_extra_profile_fields'));
            }

            // Runs just before the end of the new user registration form.
            add_action('register_form', array($subscriptionOptionsSettings, 'mailjet_show_extra_profile_fields'));
            // Runs near the end of the "Add New" user screen.
            add_action('user_new_form', array($subscriptionOptionsSettings, 'mailjet_show_extra_profile_fields'));

            // Runs when a user's profile is updated. Action function argument: user ID.
            add_action('profile_update', array($subscriptionOptionsSettings, 'mailjet_save_extra_profile_fields'));
            // Runs immediately after the new user is added to the database.
            add_action('user_register', array($subscriptionOptionsSettings, 'mailjet_register_user'));
        }

        /* Add custom field to comment form and process it on form submit */
        $activate_mailjet_comment_authors_sync = (int)get_option('activate_mailjet_comment_authors_sync');
        $mailjet_comment_authors_list = (int)get_option('mailjet_comment_authors_list');
        if ($activate_mailjet_comment_authors_sync === 1 && $mailjet_comment_authors_list > 1) {
            $commentAuthorsSettings = new CommentAuthorsSettings();
            if (wp_get_current_user()->exists()) {
                add_action('comment_form', array($commentAuthorsSettings, 'mailjet_show_extra_comment_fields'));
            }
            else {
                add_action('comment_form_after_fields', array($commentAuthorsSettings, 'mailjet_show_extra_comment_fields'));
            }
            add_action('wp_insert_comment', array($commentAuthorsSettings, 'mailjet_subscribe_comment_author'));
            MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Comment Authors Sync active - added custom actions to sync them ]');
        }


        /* Add custom field to WooCommerce checkout form and process it on form submit */
        $activate_mailjet_woo_integration = get_option('activate_mailjet_woo_integration');
        $activate_mailjet_sync = get_option('activate_mailjet_sync');

        if ((int)$activate_mailjet_woo_integration === 1 && (int)$activate_mailjet_sync === 1) {
            $wooCommerceSettings = WooCommerceSettings::getInstance();
            // Add the checkbox
            add_action('woocommerce_after_checkout_billing_form', array($wooCommerceSettings, 'mailjet_show_extra_woo_fields'), 10, 2);
            // Process the checkbox on submit
            add_action('woocommerce_checkout_create_order', array($wooCommerceSettings, 'mailjet_subscribe_woo'), 10, 2);
            add_action('wp_ajax_nopriv_mj_ajax_subscribe', array($wooCommerceSettings, 'subscribeViaAjax'));
            add_action('wp_ajax_mj_ajax_subscribe', array($wooCommerceSettings, 'subscribeViaAjax'));

            // Add filter for changing "Thank you" text on order processed page
            add_filter('woocommerce_thankyou_order_received_text', array($wooCommerceSettings, 'woo_change_order_received_text'), 10, 2);

            MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Comment Authors Sync active - added custom actions to sync them ]');

        }

        $isContactFormActivated = get_option('activate_mailjet_cf7_integration');
        $cfList = get_option('mailjet_cf7_list');
        if ($isContactFormActivated && $cfList) {
            $this->activateCf7Url($cfList);
        }

        // Add a Link to Mailjet settings page next to the activate/deactivate links in WP Plugins page
        add_filter('plugin_action_links', array($this, 'mailjet_settings_link'), 10, 2);

        $currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : null;
        if (in_array($currentPage, array('mailjet_initial_contact_lists_page', 'mailjet_sending_settings_page', 'mailjet_subscription_options_page'))) {
            if (!MailjetApi::isValidAPICredentials()) {
                add_action('admin_notices', array($this, 'apiCredentialsInvalid'));
            }
        }

        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Adding some custom mailjet logic to WP actions - End ]');
    }

    private function activateCf7Url($contactListId)
    {
        $locale = Mailjeti18n::getLocale();
        $technicalIssue = Mailjeti18n::getTranslationsFromFile($locale, 'A technical issue has prevented your subscription. Please try again later.');

        $contactForm7Settings = new ContactForm7Settings();
        add_action('wpcf7_submit', array($contactForm7Settings, 'sendConfirmationEmail'), 10, 2);
        if (!empty($_GET['cf7list']) && $_GET['cf7list'] === $contactListId) {

            if (empty($_GET['email'])) {
                echo $technicalIssue;
                MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Subscription failed ]');
                die;
            }

            $email = sanitize_email($_GET['email']);
            $name = sanitize_text_field($_GET['prop']);

            $params = http_build_query(array(
                'cf7list' => $contactListId,
                'email' => $email,
                'prop' => $name
            ));

            if (sha1($params . MailjetSettings::getCryptoHash()) !== $_GET['token']) {
                return false;
            }

            // Hardcode this in order to pass the check inside `$this->>subsctiptionConfirmationAdminNoticeSuccess()`
            $_GET['subscribe'] = 1;

            $contact = array();
            $contact['Email'] = $email;
            $contact['Properties']['name'] = $name;
            MailjetApi::createMailjetContactProperty('name');
            $syncSingleContactEmailToMailjetList = MailjetApi::syncMailjetContact($contactListId, $contact);
            if (false === $syncSingleContactEmailToMailjetList) {
                echo $technicalIssue;
            } else {
                $this->subsctiptionConfirmationAdminNoticeSuccess();
            }

            die;
        }
    }

    /**
     * Add admin notice saying that current API credentials are not valid
     */
    public function apiCredentialsInvalid()
    {
        add_settings_error('mailjet_messages', 'mailjet_message', __('Your Mailjet API credentials are invalid or not yet configured. Please check and configure them to proceed further.', 'mailjet-for-wordpress'), 'error');
    }

    /**
     * Adding a Mailjet logic and functionality to some WP actions - for example - inserting checkboxes for subscription
     */
    public function addSubscriptionConfirmations()
    {
        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Handling subscription confirmations - Start ]');

        $subscribeParam = isset($_GET['subscribe']) ? sanitize_text_field($_GET['subscribe']) : '';
        $subscriptionEmail = isset($_GET['user_email']) ? sanitize_email($_GET['user_email']) : '';
        /* Add custom field to comment form and process it on form submit */
        $activate_mailjet_comment_authors_sync = get_option('activate_mailjet_comment_authors_sync');
        $mailjet_comment_authors_list = get_option('mailjet_comment_authors_list');
        if (!empty($activate_mailjet_comment_authors_sync) && !empty($mailjet_comment_authors_list) && !empty($_GET['mj_sub_comment_author_token'])) {
            // Verify the token from the confirmation email link and subscribe the comment author to the Mailjet contacts list
            $mj_sub_comment_author_token = sanitize_text_field($_GET['mj_sub_comment_author_token']);
            $tokenCheck  = sha1($subscribeParam . str_ireplace(' ', '+', $subscriptionEmail) . self::getCryptoHash());
            if ($mj_sub_comment_author_token === $tokenCheck) {
                $commentAuthorsSettings = new CommentAuthorsSettings();
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Subscribe/Unsubscribe Comment Author To List ]');
                $syncSingleContactEmailToMailjetList = $commentAuthorsSettings->mailjet_subscribe_unsub_comment_author_to_list($subscribeParam, str_ireplace(' ', '+', $subscriptionEmail));
                if (false === $syncSingleContactEmailToMailjetList) {
                    $this->subsctiptionConfirmationAdminNoticeFailed();
                } else {
                    $this->subsctiptionConfirmationAdminNoticeSuccess();
                }

                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Handling subscription confirmations - End ]');
            }
        }

        /* Add custom field to WooCommerce checkout form and process it on form submit */
        $activate_mailjet_woo_integration = get_option('activate_mailjet_woo_integration');
        if (!empty($activate_mailjet_woo_integration) && !empty($_GET['mj_sub_woo_token'])) {
            // Verify the token from the confirmation email link and subscribe the comment author to the Mailjet contacts list
            $mj_sub_woo_token = $_GET['mj_sub_woo_token'];
            $firstName = sanitize_text_field($_GET['first_name']);
            $lastName = sanitize_text_field($_GET['last_name']);
            $tokenCheck  = sha1($subscribeParam . str_ireplace(' ', '+', $subscriptionEmail) . $firstName . $lastName . self::getCryptoHash());
            if ($mj_sub_woo_token === $tokenCheck) {
                $wooCommerceSettings = WooCommerceSettings::getInstance();
                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Subscribe/Unsubscribe WooCommerce user To List ]');
                $syncSingleContactEmailToMailjetList = $wooCommerceSettings->mailjet_subscribe_unsub_woo_to_list($_GET['subscribe'], str_ireplace(' ', '+', $subscriptionEmail), $firstName, $lastName);
                if (false === $syncSingleContactEmailToMailjetList) {
                    $this->subsctiptionConfirmationAdminNoticeFailed();
                } else {
                    $this->subsctiptionConfirmationAdminNoticeSuccess();
                }

                MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Handling subscription confirmations - End ]');
            }
        }
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
        if ($file != plugin_basename(dirname(dirname(dirname(__FILE__)))) . '/wp-mailjet.php') {
            return $links;
        }

        $settings_link = '<a href="admin.php?page=mailjet_settings_page&from=plugins">' . __('Setup account', 'mailjet-for-wordpress') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function subsctiptionConfirmationAdminNoticeSuccess()
    {
        if ((int)sanitize_text_field($_GET['subscribe']) > 0) {
            $locale = Mailjeti18n::getLocaleByPll();
            $newsletterRegistration = Mailjeti18n::getTranslationsFromFile($locale, 'Newsletter Registration');
            $congratsSubscribed = Mailjeti18n::getTranslationsFromFile($locale, 'Congratulations, you have successfully subscribed!');
            $tankyouPageTemplate = apply_filters('mailjet_thank_you_page_template', plugin_dir_path(__FILE__) . '..' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'thankyou.php');
            // Default page is selected
            include($tankyouPageTemplate);
//            echo '<div class="notice notice-info is-dismissible" style="padding-right: 38px; position: relative; display: block; background: #fff; border-left: 4px solid #46b450; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); margin: 5px 15px 2px; padding: 1px 12px;">' . __('You have been successfully subscribed to a Mailjet contact list', 'mailjet-for-wordpress') . '</div>';
        } else {
            echo '<div class="notice notice-info is-dismissible" style="padding-right: 38px; position: relative; display: block; background: #fff; border-left: 4px solid #46b450; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); margin: 5px 15px 2px; padding: 1px 12px;">' . 'You have been successfully unsubscribed from a Mailjet contact list' . '</div>';
        }
        die; //We die here to not continue loading rest of the WP home page
    }

    public function subsctiptionConfirmationAdminNoticeFailed()
    {
        echo '<div class="notice notice-error is-dismissible" style="padding-right: 38px; position: relative; display: block; background: #fff; border-left: 4px solid #dc3232; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); margin: 5px 15px 2px; padding: 1px 12px;">' . __('Something went wrong with adding a contact to Mailjet contact list', 'mailjet-for-wordpress') . '</div>';
        die; //We die here to not continue loading rest of the WP home page
    }

    /**
     * Automatically redirect to the next step - we use javascript to prevent the WP issue when using `wp_redirect` method and headers already sent
     *
     * @param $urlToRedirect
     */
    public static function redirectJs($urlToRedirect)
    {
        if (empty($urlToRedirect)) {
            return;
        }
        ?>
        <script type="text/javascript">
            window.location.href = '<?php echo $urlToRedirect; ?>';
        </script>
        <?php
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $urlToRedirect . '">';
        exit;
    }

    public static function getCryptoHash() {
        $hash = get_option('crypto_hash');
        if (empty($hash)) {
            try {
                $hash = bin2hex(random_bytes(10));
            }
            catch (Exception $e) {
                $hash = (string)mt_rand();
            }
            update_option('crypto_hash', $hash);
        }
        return get_option('crypto_hash');
    }
}
