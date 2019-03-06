<?php

namespace MailjetPlugin\Includes\SettingsPages;

use Analog\Handler\Mail;
use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\Mailjeti18n;
use MailjetPlugin\Includes\MailjetLogger;

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
class CommentAuthorsSettings
{
    public function mailjet_show_extra_comment_fields($user)
    {
        $user = wp_get_current_user();
        // Display the checkbox only for NOT-logged in users
        if (!$user->exists() && get_option('mailjet_comment_authors_list')) {
            ?>
            <label class="mj-label" for="mailjet_comment_authors_subscribe_ok">
                <input type="checkbox" name="mailjet_comment_authors_subscribe_ok" id="mailjet_comment_authors_subscribe_ok" value="1" class="checkbox" />
                <?php _e('Subscribe to our newsletter', 'mailjet-for-wordpress') ?>
            </label>
            <?php
        }
    }


    public function mailjet_subscribe_comment_author($id)
    {
        $comment = get_comment($id);
        $authorEmail = filter_var($comment->comment_author_email, FILTER_SANITIZE_EMAIL);
        $userId = filter_var($comment->user_id, FILTER_SANITIZE_NUMBER_INT);

        // We return if there is no provided email on a new comment - which is the case for WooCommerce - it adds a post and comment when making an order
        if (empty($authorEmail)) {
            return;
        }

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
        $action = intval($subscribe) === 1 ? 'addforce' : 'remove';
        // Add the user to a contact list
        return SubscriptionOptionsSettings::syncSingleContactEmailToMailjetList(get_option('mailjet_comment_authors_list'), $user_email, $action);
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

        // We return if there is no provided email on a new comment - which is the case for WooCommerce - it adds a post and comment when making an order
        if (empty($user_email)) {
            return;
        }

        if (!is_email($user_email)) {
            _e('Invalid email', 'mailjet');
            die;
        }
        $wpUrl = sprintf('<a href="%s" target="_blank">%s</a>', get_home_url(), get_home_url());
        $message = file_get_contents(dirname(dirname(dirname(__FILE__))) . '/templates/confirm-subscription-email.php');
        $emailParams = array(
            '__EMAIL_TITLE__' => __('Please confirm your subscription', 'mailjet'),
            '__EMAIL_HEADER__' => sprintf(__('To receive newsletters from %s please confirm your subscription by clicking the following button:', 'mailjet'), $wpUrl),
            '__WP_URL__' => $wpUrl,
            '__CONFIRM_URL__' => get_home_url() . '?subscribe=' . $subscribe . '&user_email=' . $user_email . '&mj_sub_comment_author_token=' . sha1($subscribe . $user_email),
            '__CLICK_HERE__' => __('Yes, subscribe me to this list', 'mailjet'),
            '__FROM_NAME__' => get_option('blogname'),
            '__IGNORE__' => __('If you received this email by mistake or don\'t wish to subscribe anymore, simply ignore this message.', 'mailjet'),
        );
        foreach ($emailParams as $key => $value) {
            $message = str_replace($key, $value, $message);
        }

        $email_subject = __('Subscription Confirmation', 'mailjet');
        add_filter('wp_mail_content_type', array(new SubscriptionOptionsSettings(), 'set_html_content_type'));
        wp_mail($_POST['email'], $email_subject, $message,
            array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
    }
}
