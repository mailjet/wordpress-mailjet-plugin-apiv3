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
        // Redirect the user to the Dashboard if he already configured his initial settings
        $currentPage = $_REQUEST['page'];
        if ('mailjet_settings_page' == $currentPage && !empty(get_option('mailjet_apikey')) && !empty(get_option('mailjet_apisecret'))) {
            wp_redirect(admin_url('/admin.php?page=mailjet_dashboard_page'));
            exit;
        }

        $this->addMailjetActions();

        // register a new setting for "mailjet" page
        register_setting('mailjet_initial_settings_page', 'mailjet_apikey');
        register_setting('mailjet_initial_settings_page', 'mailjet_apisecret');
        register_setting('mailjet_initial_settings_page', 'settings_step');

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


    }


    private function addMailjetActions()
    {
        if (!empty(get_option('activate_mailjet_sync')) && !empty(get_option('mailjet_sync_list'))) {

            add_action('show_user_profile', array($this, 'mailjet_show_extra_profile_fields'));
            add_action('edit_user_profile', array($this, 'mailjet_show_extra_profile_fields'));
            add_action('register_form', array($this, 'mailjet_show_extra_profile_fields'));
            add_action('user_new_form', array($this, 'mailjet_show_extra_profile_fields'));

            add_action('personal_options_update', array($this, 'mailjet_my_save_extra_profile_fields'));
            add_action('edit_user_profile_update', array($this, 'mailjet_my_save_extra_profile_fields'));

            add_action('user_register', array($this, array($this, 'mailjet_register_extra_fields')));
        }


        /* Add custom field to comment form and process it on form submit */
        if (!empty(get_option('activate_mailjet_comment_authors_sync')) && !empty(get_option('mailjet_comment_authors_list'))) {
            add_action('comment_form_after_fields', array($this, 'mailjet_show_extra_comment_fields'));
            add_action('wp_insert_comment',array($this, 'mailjet_subscribe_comment_author'));


            if (!empty($_GET['mj_sub_comment_author_token'])
                &&
                $_GET['mj_sub_comment_author_token'] == sha1($_GET['subscribe'] . str_ireplace(' ', '+', $_GET['user_email']))) {
                $this->mailjet_subscribe_unsub_comment_author_to_list($_GET['subscribe'], str_ireplace(' ', '+', $_GET['user_email']));
            }
        }

    }



    public function mailjet_show_extra_profile_fields($user)
    {
        // If contact list is not selected, then do not show the extra fields
        if (!empty(get_option('activate_mailjet_sync')) && !empty(get_option('mailjet_sync_list'))) {
            // Update the extra fields
            if (is_object($user) && intval($user->ID) > 0) {
                $this->mailjet_subscribe_unsub_user_to_list(esc_attr(get_the_author_meta('mailjet_subscribe_ok', $user->ID)), $user->ID);
            }
            ?>
            <label for="admin_bar_front">
                <input type="checkbox" name="mailjet_subscribe_ok" id="mailjet_subscribe_ok" value="1"
                    <?php echo(is_object($user) && intval($user->ID) > 0 && esc_attr(get_the_author_meta('mailjet_subscribe_ok', $user->ID)) ? 'checked="checked" ' : ''); ?>
                       class="checkbox" /> <?php _e('Subscribe to our mailing list', 'mailjet') ?></label>
            </br>
            <?php
        }
    }


    /**
     *  Update extra profile fields when the profile is saved
     */
    public function mailjet_my_save_extra_profile_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return FALSE;
        }

        $subscribe = filter_var($_POST ['mailjet_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);

        update_user_meta($user_id, 'mailjet_subscribe_ok', $subscribe);
        $this->mailjet_subscribe_unsub_user_to_list($subscribe, $user_id);
    }


    /**
     *  Subscribe or unsubscribe a wordpress user (admin, editor, etc.) in/from a Mailjet's contact list when the profile is saved
     */
    public function mailjet_subscribe_unsub_user_to_list($subscribe, $user_id)
    {
        if (!empty(get_option('mailjet_sync_list'))) {
            $user = get_userdata($user_id);
            $action = $subscribe ? 'addforce' : 'remove';
            // Add the user to a contact list
            if (false == SubscriptionOptionsSettings::syncContactsToMailjetList(get_option('mailjet_sync_list'), $user, $action)) {
                add_settings_error('mailjet_messages', 'mailjet_message', __('Something went wrong with adding existing Wordpress users to your Mailjet contact list', 'mailjet'), 'error');
            } else {
                add_settings_error('mailjet_messages', 'mailjet_message', __('All Wordpress users were succesfully added to your Mailjet contact list', 'mailjet'), 'updated');
            }
        }
    }

    /**
     *  Set extra profile fields when the profile is saved
     */
    public function mailjet_register_extra_fields($user_id, $password = "", $meta = array())
    {
        $subscribe = filter_var($_POST ['mailjet_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);

        update_user_meta($user_id, 'mailjet_subscribe_ok', $subscribe);
        $this->mailjet_subscribe_unsub_user_to_list($subscribe, $user_id);
    }




    public function mailjet_show_extra_comment_fields($user)
    {
        global $current_user;
        $user_id = $current_user->ID;

        // Display the checkbox only for NOT-logged in users
        if (!$user_id && get_option('mailjet_comment_authors_list')) {
            ?>
            <label for="admin_bar_front">
                <input type="checkbox" name="mailjet_comment_authors_subscribe_ok" id="mailjet_comment_authors_subscribe_ok" value="1" class="checkbox" />
                <?php _e('Subscribe to our mailing list', 'mailjet') ?>
            </label>
            <br>
            <?php
        }
    }


    public function mailjet_subscribe_comment_author($id)
    {
        $comment = get_comment($id);
        $authorEmail = filter_var($comment->comment_author_email, FILTER_SANITIZE_EMAIL);
        $userId = filter_var($comment->user_id, FILTER_SANITIZE_NUMBER_INT);

        if (!is_email($authorEmail)) {
            _e('Invalid email', 'mailjet');
            die;
        }

        $subscribe = filter_var($_POST['mailjet_comment_authors_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);
        $this->mailjet_subscribe_confirmation_from_comment_form($subscribe, $authorEmail);
    }



    /**
     *  Subscribe or unsubscribe a wordpress comment author in/from a Mailjet's contact list when the comment is saved
     */
    public function mailjet_subscribe_unsub_comment_author_to_list($subscribe, $user_email)
    {
        $action = $subscribe ? 'addforce' : 'remove';
        // Add the user to a contact list
        if (false === SubscriptionOptionsSettings::syncSingleContactEmailToMailjetList(get_option('mailjet_comment_authors_list'), $user_email, $action)) {
            _e('Something went wrong with adding a contact to Mailjet contact list', 'mailjet');
        } else {
            _e('Contact succesfully added to Mailjet contact list', 'mailjet');
        }
        die();
    }



    /**
     * Email the collected widget data to the customer with a verification token
     * @param void
     * @return void
     */
    public function mailjet_subscribe_confirmation_from_comment_form($subscribe, $user_email)
    {
        $error = empty($user_email) ? 'Email field is empty' : false;
        if (false !== $error) {
            _e($error, 'mailjet');
            die;
        }

        if (!is_email($user_email)) {
            _e('Invalid email', 'mailjet');
            die;
        }

        $message = file_get_contents(dirname(dirname(__FILE__)) . '/templates/confirm-subscription-email.php');
        $emailParams = array(
            '__EMAIL_TITLE__' => __('Confirm your mailing list subscription', 'mailjet'),
            '__EMAIL_HEADER__' => __('Please Confirm Your Subscription To', 'mailjet'),
            '__WP_URL__' => sprintf('<a href="%s" target="_blank">%s</a>', get_home_url(), get_home_url()),
            '__CONFIRM_URL__' => get_home_url() . '?subscribe=' . $subscribe . '&user_email=' . $user_email . '&mj_sub_comment_author_token=' . sha1($subscribe . $user_email),
            '__CLICK_HERE__' => __('Click here to confirm', 'mailjet'),
            '__COPY_PASTE_LINK__' => __('You may copy/paste this link into your browser:', 'mailjet'),
            '__FROM_NAME__' => get_option('blogname'),
            '__IGNORE__' => __('Did not ask to subscribe to this list? Or maybe you have changed your mind? Then simply ignore this email and you will not be subscribed', 'mailjet'),
            '__THANKS__' => __('Thanks,', 'mailjet')
        );
        foreach ($emailParams as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
        wp_mail($_POST['email'], __('Subscription Confirmation', 'mailjet-'), $message,
            array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
    }
}
