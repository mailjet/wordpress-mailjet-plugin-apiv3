<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\Mailjeti18n;
use MailjetPlugin\Includes\MailjetLogger;
use MailjetPlugin\Includes\MailjetSettings;

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
class SubscriptionOptionsSettings
{
    CONST PROP_USER_FIRSTNAME = 'firstname';
    CONST PROP_USER_LASTNAME = 'lastname';
    CONST WP_PROP_USER_ROLE = 'wp_user_role';

    private static $instance;

    private $subscrFieldset = '/settingTemplates/SubscriptionSettingsPartials/subscrFieldset.php';
    private $profileFields = '/settingTemplates/SubscriptionSettingsPartials/profileFields.php';

    private function __construct()
    {
	    add_action( 'admin_enqueue_scripts', [$this, 'enqueueAdminScripts' ]);
	    add_action( 'wp_ajax_resync_mailjet', [$this, 'ajaxResync']);
	    add_action( 'wp_ajax_get_contact_lists_menu', [$this, 'getContactListsMenu']);
    }

    public static function getInstance() {
        if (is_null(self::$instance))  {
            self::$instance = new SubscriptionOptionsSettings();
        }
        return self::$instance;
    }

	public function mailjet_section_subscription_options_cb($args)
    {
        ?>
<!--        <p id="--><?php //echo esc_attr( $args['id'] ); ?><!--">-->
<!--            --><?php //esc_html_e( 'Automatically add WordPress users to a Mailjet list. Each user’s email address and role (subscriber, administrator, author, …) is synchronized to the list and available for use inside Mailjet.', 'mailjet-for-wordpress' ); ?>
<!--        </p>-->
        <?php
    }


    public function mailjet_subscription_options_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $allWpUsers = get_users(array('fields' => array('ID', 'user_email')));
        $wpUsersCount = count($allWpUsers);

        $mailjetSyncContactLists = MailjetApi::getMailjetContactLists();
        $mailjetSyncContactList = MailjetApi::getContactListByID(get_option('mailjet_sync_list'));
        $mailjetSyncActivated = get_option('activate_mailjet_sync');
        $mailjetInitialSyncActivated = get_option('activate_mailjet_initial_sync');
        $mailjetCommentAuthorsList = get_option('mailjet_comment_authors_list');
        $mailjetCommentAuthorsSyncActivated = get_option('activate_mailjet_comment_authors_sync');

        $mailjetSyncContactListId = !empty($mailjetSyncContactList) ? $mailjetSyncContactList[0]['ID'] : -1;
        $mailjetSyncContactListName = !empty($mailjetSyncContactList) ? $mailjetSyncContactList[0]['Name'] . ' ('.$mailjetSyncContactList[0]['SubscriberCount'].')' : 'No list selected';

        set_query_var('wpUsersCount', $wpUsersCount);
        set_query_var('mailjetContactLists', $mailjetSyncContactLists);
        set_query_var('mailjetSyncContactListId', $mailjetSyncContactListId);
	    set_query_var('mailjetSyncContactListName', $mailjetSyncContactListName);
	    set_query_var('mailjetSyncActivated', $mailjetSyncActivated);
	    set_query_var('mailjetCommentAuthorsList', $mailjetCommentAuthorsList);
	    set_query_var('mailjetInitialSyncActivated', $mailjetInitialSyncActivated);
	    set_query_var('mailjetCommentAuthorsSyncActivated', $mailjetCommentAuthorsSyncActivated);

	    load_template(MAILJET_ADMIN_TAMPLATE_DIR . $this->subscrFieldset);

    }


    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_subscription_options_page_html()
    {
        // check user capabilities
        if (!current_user_can('read')) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }


	    // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_subscription_options_settings',
            null,
            array($this, 'mailjet_section_subscription_options_cb'),
            'mailjet_subscription_options_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_subscription_options', // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __( 'Subscription Options', 'mailjet-for-wordpress' ),
            array($this, 'mailjet_subscription_options_cb'),
            'mailjet_subscription_options_page',
            'mailjet_subscription_options_settings',
            [
                'label_for' => 'mailjet_subscription_options',
                'class' => 'mailjet_row',
                'mailjet_custom_data' => 'custom',
            ]
        );


        // add error/update messages

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {
            $executionError = false;
            // Initial sync WP users to Mailjet
            $activate_mailjet_initial_sync = get_option('activate_mailjet_initial_sync');
            $mailjet_sync_list = get_option('mailjet_sync_list');
            if ((int)$activate_mailjet_initial_sync === 1 && (int)$mailjet_sync_list > 0) {
                $syncResponse = self::syncAllWpUsers();
                if (false === $syncResponse) {
                    $executionError = true;
                    add_settings_error('mailjet_messages', 'mailjet_message', __('The settings could not be saved. Please try again or in case the problem persists contact Mailjet support.', 'mailjet-for-wordpress'), 'error');
                }
            }
            if ((int)$mailjet_sync_list <= 0) {
                update_option('mailjet_woo_edata_sync', '');
                update_option('mailjet_woo_checkout_checkbox', '');
                update_option('mailjet_woo_banner_checkbox', '');
            }
            if (false === $executionError) {
                // add settings saved message with the class of "updated"
                add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet-for-wordpress'), 'updated');
            }
        }

        // show error/update messages
        settings_errors('mailjet_messages');
        load_template(MAILJET_ADMIN_TAMPLATE_DIR . '/settingTemplates/mainSettingsTemplate.php');
    }


    public static function syncAllWpUsers()
    {
        $mailjet_sync_list = get_option('mailjet_sync_list');
        if ((int)$mailjet_sync_list <= 0) {
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please select a contact list.', 'mailjet-for-wordpress'), 'error');
            return false;
        }

        $subContacts = array();
        $unsubContacts = array();
        foreach (get_users() as $user) {
            if ($user->roles[0] === 'customer') {
                $unsubContacts[$user->user_email] = $user;
            }
            else {
                $subContacts[$user->user_email] = $user;
            }
        }

        $subscribers = MailjetApi::getSubscribersFromList($mailjet_sync_list);
        foreach ($subscribers as $sub) {
            $email = $sub['Contact']['Email']['Email'];
            if (array_key_exists($email, $unsubContacts)) {
                $subContacts[$email] = $unsubContacts[$email];
                unset($unsubContacts[$email]);
            }
        }
        if (count($subContacts) <= 0 && count($unsubContacts) <= 0) {
            add_settings_error('mailjet_messages', 'mailjet_message', __('No WordPress users to add to Mailjet contact list', 'mailjet-for-wordpress'), 'error');
            return false;
        }

        $error = false;
        if (false === self::syncContactsToMailjetList($mailjet_sync_list, $subContacts, 'addnoforce')) {
            $error = true;
        }
        if (false === self::syncContactsToMailjetList($mailjet_sync_list, $unsubContacts, 'unsub')) {
            $error = true;
        }

        if ($error) {
            add_settings_error('mailjet_messages', 'mailjet_message', __('Something went wrong with adding existing WordPress users to your Mailjet contact list', 'mailjet-for-wordpress'), 'error');
            return false;
        } else {
            add_settings_error('mailjet_messages', 'mailjet_message', __('All WordPress users were successfully added to your Mailjet contact list', 'mailjet-for-wordpress'), 'updated');
        }
        return true;
    }


    /**
     * Add or Remove a contact to Mailjet contact list
     *
     * @param $contactListId
     * @param $users - can be array of users or a single user
     * @param $action - addnoforce, addforce, remove
     * @return array|bool|int
     */
    public static function syncContactsToMailjetList($contactListId, $users, $action)
    {
        $contacts = array();

        if (!is_array($users)) {
            $users = array($users);
        }

        foreach ($users as $user) {
            $userInfo = get_userdata($user->ID);
            $userRoles = $userInfo->roles;
            $userMetadata = get_user_meta($user->ID);
            $userNames = '';

            $contactProperties = array();
            if (!empty($userMetadata['first_name'][0])) {
                $contactProperties[self::PROP_USER_FIRSTNAME] = $userMetadata['first_name'][0];
                $userNames = $contactProperties[self::PROP_USER_FIRSTNAME];
            }
            if (!empty($userMetadata['last_name'][0])) {
                $contactProperties[self::PROP_USER_LASTNAME] = $userMetadata['last_name'][0];
                $userNames.= ' ' . $contactProperties[self::PROP_USER_LASTNAME];
            }
            if (!empty($userRoles[0])) {
                $contactProperties[self::WP_PROP_USER_ROLE] = $userRoles[0];
            }

            $contacts[] = array(
                'Email' => $user->user_email,
                'Name' => $userNames,
                'Properties' => $contactProperties
            );
        }

        return MailjetApi::syncMailjetContacts($contactListId, $contacts, $action);
    }


    public static function syncSingleContactEmailToMailjetList($contactListId, $email, $action, $contactProperties = [])
    {
        if (empty($email)) {
            return false;
        }

        $contact = [];
        $contact['Email'] = $email;
        $contact['Properties'] = $contactProperties;

        return MailjetApi::syncMailjetContact($contactListId, $contact, $action);
    }

    public function checkUserSubscription($userLogin, $user) {
        $activate_mailjet_sync = get_option('activate_mailjet_sync');
        $mailjet_sync_list = get_option('mailjet_sync_list');
        if (!empty($activate_mailjet_sync) && !empty($mailjet_sync_list)) {
            $subscribed = MailjetApi::checkContactSubscribedToList($user->user_email, $mailjet_sync_list);
            update_user_meta($user->ID, 'mailjet_subscribe_ok', $subscribed ? '1' : '');
        }
    }

    public function mailjet_register_user($userId) {
        $activate_mailjet_sync = get_option('activate_mailjet_sync');
        $mailjet_sync_list = get_option('mailjet_sync_list');
        $user = get_userdata($userId);
        if (!empty($activate_mailjet_sync) && !empty($mailjet_sync_list) && !empty($user)) {
            $email = $user->user_email;
            $role = $user->roles[0];
            $firstname = $user->first_name;
            $lastname = $user->last_name;
            if ((int)get_option('mailjet_woo_edata_sync') === 1) {
                $registration_date = $user->user_registered;
            }
            try {
                $contactId = MailjetApi::isContactInList($email, $mailjet_sync_list, true);
            }
            catch (\Exception $e) {
                MailjetLogger::log('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' . $e->getMessage() . ' ]');
                return;
            }
            if ($contactId > 0) {
                $data = array(
                    array('Name' => self::WP_PROP_USER_ROLE, 'Value' => $role),
                    array('Name' => self::PROP_USER_FIRSTNAME, 'Value' => $firstname),
                    array('Name' => self::PROP_USER_LASTNAME, 'Value' => $lastname)
                );
                if (isset($registration_date)) {
                    $data[] = array('Name' => WooCommerceSettings::WOO_PROP_ACCOUNT_CREATION_DATE, 'Value' => $registration_date);
                }
                try {
                    MailjetApi::updateContactData($email, $data);
                }
                catch (\Exception $e) {
                    MailjetLogger::log('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' . $e->getMessage() . ' ]');
                    return;
                }
            }
            else {
                $properties = array(
                    self::WP_PROP_USER_ROLE => $role,
                    self::PROP_USER_FIRSTNAME => $firstname,
                    self::PROP_USER_LASTNAME => $lastname
                );
                if (isset($registration_date)) {
                    $properties[WooCommerceSettings::WOO_PROP_ACCOUNT_CREATION_DATE] = $registration_date;
                }
                $contact = array(
                    'Email' => $email,
                    'Properties' => $properties
                );
                MailjetApi::syncMailjetContact($mailjet_sync_list, $contact, 'unsub');
            }
        }
    }

    /**
     *  Adding checkboxes and extra fields for subscribing user and comment authors
     */
    public function mailjet_show_extra_profile_fields($user)
    {
        // If contact list is not selected, then do not show the extra fields
        $activate_mailjet_sync = get_option('activate_mailjet_sync');
        $mailjet_sync_list = get_option('mailjet_sync_list');
        if (!empty($activate_mailjet_sync) && !empty($mailjet_sync_list)) {
            if (empty($user)) {
                $user = wp_get_current_user();
            }
            // Update the extra fields
            $checked = (is_object($user) && (int)$user->ID > 0 && (int)get_user_meta($user->ID, 'mailjet_subscribe_ok', true) === 1) ? 'checked="checked" ' : '';
            set_query_var('checked', $checked);
            load_template(MAILJET_ADMIN_TAMPLATE_DIR . $this->profileFields);
        }
    }


    /**
     *  Update extra profile fields when the profile is saved
     */
    public function mailjet_save_extra_profile_fields($user_id)
    {
        if (isset($_POST ['mailjet_subscribe_extra_field'])) {
            $subscribe = isset($_POST ['mailjet_subscribe_ok']) ? filter_var($_POST ['mailjet_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT) : 0;
            update_user_meta($user_id, 'mailjet_subscribe_ok', $subscribe);
            $this->mailjet_subscribe_unsub_user_to_list($subscribe, $user_id);
        }
    }


    /**
     *  Subscribe or unsubscribe a wordpress user (admin, editor, etc.) in/from a Mailjet's contact list when the profile is saved
     */
    public function mailjet_subscribe_unsub_user_to_list($subscribe, $user_id)
    {
        $mailjet_sync_list = get_option('mailjet_sync_list');
        if (!empty($mailjet_sync_list)) {
            $user = get_userdata($user_id);
            $action = (int)$subscribe === 1 ? 'addforce' : 'unsub';
            $contactProperties = array();
            if (!empty($user->first_name)) {
                $contactProperties[self::PROP_USER_FIRSTNAME] = $user->first_name;
            }
            if (!empty($user->last_name)) {
                $contactProperties[self::PROP_USER_LASTNAME] = $user->last_name;
            }
            if (!empty($user->roles[0])) {
                $contactProperties[self::WP_PROP_USER_ROLE] = $user->roles[0];
            }
            if (!empty($user->user_registered) && (int)get_option('mailjet_woo_edata_sync') === 1) {
                $contactProperties[WooCommerceSettings::WOO_PROP_ACCOUNT_CREATION_DATE] = $user->user_registered;
            }
            // Add the user to a contact list
            if (false === self::syncSingleContactEmailToMailjetList(get_option('mailjet_sync_list'), $user->user_email, $action, $contactProperties)) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function mailjet_subscribe_confirmation_from_widget($subscription_email, $instance, $subscription_locale, $widgetId = false)
    {
        $homeUrl = get_home_url();
        $language = Mailjeti18n::getCurrentUserLanguage();
        $thankYouPageId = !empty($instance[$language]['thank_you']) ? $instance[$language]['thank_you'] : false;
        $thankYouURI = $homeUrl;
        if ($thankYouPageId) {
            $thankYouURI = get_page_link($thankYouPageId);
        }
        $locale = Mailjeti18n::getLocale();

        $email_subject = !empty($instance[$locale]['email_subject']) ? apply_filters('widget_email_subject', $instance[$locale]['email_subject']) : Mailjeti18n::getTranslationsFromFile($locale, 'Subscription Confirmation');
        $email_title = !empty($instance[$locale]['email_content_title']) ? apply_filters('widget_email_content_title', $instance[$locale]['email_content_title']) : Mailjeti18n::getTranslationsFromFile($locale, 'Please confirm your subscription');
        $email_button_value = !empty($instance[$locale]['email_content_confirm_button']) ? apply_filters('widget_email_content_confirm_button', $instance[$locale]['email_content_confirm_button']) : Mailjeti18n::getTranslationsFromFile($locale, 'Yes, subscribe me to this list');
        $wpUrl = sprintf('<a href="%s" target="_blank">%s</a>', $homeUrl, $homeUrl);
        $test = sprintf(Mailjeti18n::getTranslationsFromFile($locale, 'To receive newsletters from %s please confirm your subscription by clicking the following button:'), $wpUrl);
//        $test = sprintf(__('To receive newsletters from %s please confirm your subscription by clicking the following button:', 'mailjet-for-wordpress'), $wpUrl);
        $email_main_text = !empty($instance[$locale]['email_content_main_text']) ? apply_filters('widget_email_content_main_text', sprintf($instance[$locale]['email_content_main_text'], get_option('blogname'))) : $test;
        $email_content_after_button = !empty($instance[$locale]['email_content_after_button']) ? $instance[$locale]['email_content_after_button'] : Mailjeti18n::getTranslationsFromFile($locale, 'If you received this email by mistake or don\'t wish to subscribe anymore, simply ignore this message.');
        $properties = isset($_POST['properties']) ? $_POST['properties'] : array();
        $params = array(
            'subscription_email' => $subscription_email,
            'subscription_locale' => $subscription_locale,
            'list_id' => isset($instance[$subscription_locale]['list']) ? $instance[$subscription_locale]['list'] : '',
            'thanks_id' => isset($instance[$language]['thank_you']) ? $instance[$language]['thank_you'] : '',
            'properties' => $properties
        );

        if ($widgetId){
            $params['widget_id'] = $widgetId;
        }
        $params = http_build_query($params);
        $subscriptionTemplate = apply_filters('mailjet_confirmation_email_filename', dirname(dirname(dirname(__FILE__))) . '/templates/confirm-subscription-email.php');
        $message = file_get_contents($subscriptionTemplate);

        $permalinkStructure = get_option('permalink_structure');
        if (!$thankYouPageId) {
            $qm = '?';
        } else {
            $qm = ("" === $permalinkStructure) ? '&' : '?';
        }

        $emailData = array(
            '__EMAIL_TITLE__' => $email_title,
            '__EMAIL_HEADER__' => $email_main_text,
            '__WP_URL__' => $homeUrl,
            '__CONFIRM_URL__' => $thankYouURI . $qm . $params . '&mj_sub_token=' . sha1($params . MailjetSettings::getCryptoHash()),
            '__CLICK_HERE__' => $email_button_value,
            '__FROM_NAME__' => $homeUrl, //get_option('blogname'),
            '__IGNORE__' => $email_content_after_button,
        );
        $emailParams = apply_filters('mailjet_subscription_widget_email_params', $emailData);
        foreach ($emailParams as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
        return wp_mail($subscription_email, $email_subject, $message, array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
//        echo '<p class="success">' . __('Subscription confirmation email sent. Please check your inbox and confirm the subscription.', 'mailjet-for-wordpress') . '</p>';
//        die;
    }

    public function getContactListsMenu()
    {
	    $allWpUsers = get_users(array('fields' => array('ID', 'user_email')));
	    $wpUsersCount = count($allWpUsers);
	    $mailjetSyncList = (int) get_option('mailjet_sync_list');
	    $mailjetContactLists = MailjetApi::getMailjetContactLists();
	    $mailjetContactLists = !empty($mailjetContactLists) ? $mailjetContactLists : array();
	    $mailjetSyncActivated = get_option('activate_mailjet_sync');

	    wp_send_json_success([
	            'wpUsersCount' => $wpUsersCount,
                'mailjetContactLists' => $mailjetContactLists,
                'mailjetSyncActivated' => $mailjetSyncActivated,
                'mailjetSyncList' => $mailjetSyncList
        ]);
    }

    public function set_html_content_type()
    {
        return 'text/html';
    }

    public function ajaxResync()
    {
         if ($this->syncAllWpUsers()) {
            $response = [
                    'message' => __('Contact list resync has started. You can check the progress inside the Mailjet %s', 'mailjet-for-wordpress'),
                    'url' => admin_url('admin.php') . '?page=mailjet_settings_contacts_menu',
                    'url_string' => __('contact list page', 'mailjet-for-wordpress')
            ];
	        wp_send_json_success( $response );
         }else{
	        wp_send_json_error();
         }
    }

    public function enqueueAdminScripts()
    {
        wp_enqueue_script('mailjet-ajax', plugins_url('/src/admin/js/mailjet-ajax.js', MAILJET_PLUGIN_DIR . 'src'));
    }
}
