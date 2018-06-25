<?php

/*
Plugin Name:	Mailjet for Wordpress
Version:		4.3.0
Plugin URI:		https://www.mailjet.com/plugin/wordpress.htm
Description:	Use mailjet SMTP to send email, manage lists and contacts within wordpress
Author:			Mailjet SAS
Author URI:		http://www.mailjet.com/
*/

/**
 * Copyright 2014  MAILJET  (email : plugins@mailjet.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

# Include required files
require('api/mailjet-api-v3.php');
require('api/mailjet-api-v1.php');
require('view/options-form.php');
require('mailjet-class.php');
require('mailjet-options.php');
require('mailjet-widget.php');
require('mailjet-api-strategy.php');
require('mailjet-utils.php');


/**
 * Change plugin locale to de_DE if the current locale is de_DE_formal
 *
 * @param $locale
 * @return string
 */
function mailjet_change_language($locale)
{
    if (in_array($locale, array('de_DE', 'de_DE_formal'))) {
        $locale = 'de_DE';
    }
    return $locale;
}
// commented as it change the locale for other plugins as well, which sometimes is not the desired solution. - we have local logic for locale for widget and plugin
//add_filter('plugin_locale', 'mailjet_change_language');


# Define mailjet options object
$optionsMj = new WP_Mailjet_Options();

// Check the PHP version and display warning message if it is < 5.3
// (we may also deactivate automatically the plugin, but for now that code is commented)
if (version_compare(PHP_VERSION, '5.3', '<')) {
    add_action('admin_notices', 'wp_mailjet_check_php_version');
   /*
    add_action('admin_init', 'wp_mailjet_deactivate_self');
    function wp_mailjet_deactivate_self() {
        deactivate_plugins(plugin_basename(__FILE__));
    }
   */
    return;
}

function wp_mailjet_check_php_version()
{
    echo '<div class="error"><p>';
    _e('Mailjet plugin requires PHP 5.3 to function properly. Please upgrade PHP', 'wp-mailjet');
    echo "</p></div>";
/*
    if (isset($_GET['activate'])) {
        unset($_GET['activate']);
    }
*/
}


# Check if the plugin is set up properly
if (get_option('mailjet_password') && get_option('mailjet_username')) {
    global $phpmailer, $WPMailjet;
    $MailjetApi = new WP_Mailjet_Api(get_option('mailjet_username'), get_option('mailjet_password'));

    if (!is_object($phpmailer) || !is_a($phpmailer, 'PHPMailer')) {
        require_once ABSPATH . WPINC . '/class-phpmailer.php';
        require_once ABSPATH . WPINC . '/class-smtp.php';

        $phpmailer = new PHPMailer();
    }

    $WPMailjet = new WP_Mailjet($MailjetApi, $phpmailer);
    add_action('widgets_init', 'wp_mailjet_register_widgets');
} elseif (get_option('mailjet_enabled') && (!get_option('mailjet_password') || !get_option('mailjet_username'))) {
    // Display a notice that can be dismissed
    add_action('admin_notices', 'wp_mailjet_admin_notice');
}

add_action('admin_init', 'wp_mailjet_notice_ignore');

function wp_mailjet_notice_ignore()
{
    global $current_user;
    $user_id = $current_user->ID;

    // If user clicks to ignore the notice, add that to their user meta
    if (isset($_GET['wp_mailjet_notice_ignore']) && '1' == $_GET['wp_mailjet_notice_ignore'])
        add_user_meta($user_id, 'wp_mailjet_notice_ignore', 'true', TRUE);
}


function wp_mailjet_admin_notice()
{
    global $current_user;
    $user_id = $current_user->ID;

    // Check that the user hasn't already clicked to ignore the message
    if (!get_user_meta($user_id, 'wp_mailjet_notice_ignore')) {
        echo '<div class="error"><p>';
        printf(__('The mailjet plugin is enabled but your credentials are not set. Please <a href="admin.php?page=wp_mailjet_options_top_menu" title="enable Mailjet plugin">do so now</a> to send your emails through <b>Mailjet</b> <a href="%1$s" style="display:block; float:right;">Hide Notice</a>', 'wp-mailjet'), 'admin.php?page=wp_mailjet_options_top_menu?wp_mailjet_notice_ignore=1');
        echo "</p></div>";
    }
}


/**
 *  Register Mailjet's widget
 */
function wp_mailjet_register_widgets()
{
    register_widget('WP_Mailjet_Subscribe_Widget');
}


/**
 * Display settings link on plugins page
 *
 * @param array $links
 * @param string $file
 * @return array
 */
function mailjet_settings_link($links, $file)
{
    if ($file != plugin_basename(__FILE__))
        return $links;

    $settings_link = '<a href="admin.php?page=wp_mailjet_options_top_menu">' . __('Settings', 'wp-mailjet') . '</a>';

    array_unshift($links, $settings_link);

    return $links;
}


add_filter('plugin_action_links', 'mailjet_settings_link', 10, 2);

/* Add additional custom field */
add_action('show_user_profile', 'mailjet_show_extra_profile_fields');
add_action('edit_user_profile', 'mailjet_show_extra_profile_fields');
add_action('register_form', 'mailjet_show_extra_profile_fields');
add_action('user_new_form', 'mailjet_show_extra_profile_fields');

function mailjet_show_extra_profile_fields($user)
{
    // If contact list is not selected, then do not show the extra fields
    if (get_option('mailjet_auto_subscribe_list_id')) {
        // Update the extra fields
        if (is_object($user) && intval($user->ID) > 0) {
            mailjet_subscribe_unsub_user_to_list(esc_attr(get_the_author_meta('mailjet_subscribe_ok', $user->ID)), $user->ID);
        }
        ?>
            <label for="admin_bar_front">
                <input type="checkbox" name="mailjet_subscribe_ok" id="mailjet_subscribe_ok" value="1"
                    <?php echo(is_object($user) && intval($user->ID) > 0 && esc_attr(get_the_author_meta('mailjet_subscribe_ok', $user->ID)) ? 'checked="checked" ' : ''); ?>
                       class="checkbox" /> <?php _e('Subscribe to our mailing list', 'wp-mailjet') ?></label>
        </br>
        </br>
        <?php
    }
}


function mailjet_show_extra_comment_fields($user)
{
    global $current_user;
    $user_id = $current_user->ID;

    // Display the checkbox only for NOT-logged in users
    if (!$user_id && get_option('mailjet_comment_authors_list_id')) {
        ?>
            <label for="admin_bar_front">
                <input type="checkbox" name="mailjet_comment_authors_subscribe_ok" id="mailjet_comment_authors_subscribe_ok" value="1" class="checkbox" />
                <?php _e('Subscribe to our mailing list', 'wp-mailjet') ?>
            </label>
            <br>
        <?php
    }
}


add_action('personal_options_update', 'mailjet_my_save_extra_profile_fields');
add_action('edit_user_profile_update', 'mailjet_my_save_extra_profile_fields');


/* Add custom field to registration form */
if (get_option('mailjet_auto_subscribe_list_id')) {
    add_action('user_register', 'mailjet_register_extra_fields');
}

/* Add custom field to comment form and process it on form submit */
if (get_option('mailjet_comment_authors_list_id')) {
    add_action('comment_form_after_fields', 'mailjet_show_extra_comment_fields');
    add_action('wp_insert_comment','mailjet_subscribe_comment_author');
}

function mailjet_subscribe_comment_author($id){

    $comment = get_comment($id);
    $authorEmail = filter_var($comment->comment_author_email, FILTER_SANITIZE_EMAIL);
    $userId = filter_var($comment->user_id, FILTER_SANITIZE_NUMBER_INT);

    if (!mailjet_validate_email($authorEmail)) {
        _e('Invalid email', 'wp-mailjet');
        die;
    }

    $subscribe = filter_var($_POST['mailjet_comment_authors_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);
    mailjet_subscribe_confirmation_from_comment_form($subscribe, $authorEmail);
}
function mailjet_validate_email($email)
{
    return (preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) ||
        !preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $email)) ? FALSE : TRUE;
}

/**
 *  Subscribe or unsubscribe a wordpress comment author in/from a Mailjet's contact list when the comment is saved
 */
function mailjet_subscribe_unsub_comment_author_to_list($subscribe, $user_email)
{
    if (get_option('mailjet_password') && get_option('mailjet_username') && isset($user_email)) {
        $MailjetApi = new WP_Mailjet_Api(get_option('mailjet_username'), get_option('mailjet_password'));
        if ($subscribe && $list_id = get_option('mailjet_comment_authors_list_id')) {
            // Add the user to a contact list
            $contacts[] = array(
                'Email' => $user_email
            );

            $MailjetApi->addContact(array(
                'action' => 'addforce',
                'ListID' => $list_id,
                'contacts' => $contacts
            ));

            echo '<p class="success" listId="' . get_option('mailjet_comment_authors_list_id') . '">';
            echo sprintf(__("Thanks for subscribing with %s", 'wp-mailjet-subscription-widget'), $user_email);
            echo '</p>';
        } elseif (!$subscribe && $list_id = get_option('mailjet_comment_authors_list_id')) {
            // Remove a user from a contact lists
            $MailjetApi->removeContact(array(
                'Email' => $user_email,
                'ListID' => $list_id
            ));
            echo '<p class="error" listId="' . get_option('mailjet_comment_authors_list_id') . '">';
            echo sprintf(__("The contact %s is unsubscribed", 'wp-mailjet-subscription-widget'), $user_email);
            echo '</p>';
        }
        die();
    }
}


/**
 *  Set extra profile fields when the profile is saved
 */
function mailjet_register_extra_fields($user_id, $password = "", $meta = array())
{
    $subscribe = filter_var($_POST ['mailjet_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);

    update_user_meta($user_id, 'mailjet_subscribe_ok', $subscribe);
    mailjet_subscribe_unsub_user_to_list($subscribe, $user_id);
}

/**
 * Email the collected widget data to the customer with a verification token
 * @param void
 * @return void
 */
function mailjet_subscribe_confirmation_from_comment_form($subscribe, $user_email)
{
    $error = empty($user_email) ? 'Email field is empty' : false;
    if (false !== $error) {
        _e($error, 'wp-mailjet-subscription-widget');
        die;
    }

    if (!mailjet_validate_email($user_email)) {
        _e('Invalid email', 'wp-mailjet-subscription-widget');
        die;
    }

    $message = file_get_contents(dirname(__FILE__) . '/templates/confirm-subscription-email.php');
    $emailParams = array(
        '__EMAIL_TITLE__' => __('Confirm your mailing list subscription', 'wp-mailjet-subscription-widget'),
        '__EMAIL_HEADER__' => __('Please Confirm Your Subscription To', 'wp-mailjet-subscription-widget'),
        '__WP_URL__' => sprintf('<a href="%s" target="_blank">%s</a>', get_home_url(), get_home_url()),
        '__CONFIRM_URL__' => get_home_url() . '?subscribe=' . $subscribe . '&user_email=' . $user_email . '&mj_sub_comment_author_token=' . sha1($subscribe . $user_email),
        '__CLICK_HERE__' => __('Click here to confirm', 'wp-mailjet-subscription-widget'),
        '__COPY_PASTE_LINK__' => __('You may copy/paste this link into your browser:', 'wp-mailjet-subscription-widget'),
        '__FROM_NAME__' => get_option('blogname'),
        '__IGNORE__' => __('Did not ask to subscribe to this list? Or maybe you have changed your mind? Then simply ignore this email and you will not be subscribed', 'wp-mailjet-subscription-widget'),
        '__THANKS__' => __('Thanks,', 'wp-mailjet-subscription-widget')
    );
    foreach ($emailParams as $key => $value) {
        $message = str_replace($key, $value, $message);
    }
    add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
    wp_mail($_POST['email'], __('Subscription Confirmation', 'wp-mailjet-subscription-widget'), $message,
        array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
}


/**
 *  Subscribe or unsubscribe a wordpress user (admin, editor, etc.) in/from a Mailjet's contact list when the profile is saved
 */
function mailjet_subscribe_unsub_user_to_list($subscribe, $user_id)
{
    if (get_option('mailjet_password') && get_option('mailjet_username')) {
        $user = get_userdata($user_id);
        $MailjetApi = new WP_Mailjet_Api(get_option('mailjet_username'), get_option('mailjet_password'));

        if ($subscribe && $list_id = get_option('mailjet_auto_subscribe_list_id')) {
            // Add the user to a contact list

            $MailjetApi->createMetaContactProperty(array(
                'name' => 'wp_user_role',
                'dataType' => 'str'
            ));

            $userInfo = get_userdata($user_id);
            $userRoles = $userInfo->roles;

            if (!empty($userRoles[0])) {
                $contactProperties['wp_user_role'] = $userRoles[0];
            }
            $contacts[] = array(
                'Email' => (isset($_POST['email'])) ? $_POST['email'] : $user->data->user_email,
                'Properties' => $contactProperties
            );

            $MailjetApi->addContact(array(
                'action' => 'addforce',
                'ListID' => $list_id,
                'contacts' => $contacts
            ));

        } elseif (!$subscribe && $list_id = get_option('mailjet_auto_subscribe_list_id')) {
            // Remove a user from a contact lists
            $MailjetApi->removeContact(array(
                'Email' => (isset($_POST['email'])) ? $_POST['email'] : $user->data->user_email,
                'ListID' => $list_id
            ));
        }
    }
}


/**
 *  Update extra profile fields when the profile is saved
 */
function mailjet_my_save_extra_profile_fields($user_id)
{
    if (!current_user_can('edit_user', $user_id))
        return FALSE;

    $subscribe = filter_var($_POST ['mailjet_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);

    update_user_meta($user_id, 'mailjet_subscribe_ok', $subscribe);
    mailjet_subscribe_unsub_user_to_list($subscribe, $user_id);
}

load_plugin_textdomain('wp-mailjet', FALSE, dirname(plugin_basename(__FILE__)) . '/i18n');
load_plugin_textdomain('wp-mailjet-subscription-widget', FALSE, dirname(plugin_basename(__FILE__)) . '/i18n');

if (!empty($_GET['mj_sub_comment_author_token'])
    &&
    $_GET['mj_sub_comment_author_token'] == sha1($_GET['subscribe'] . str_ireplace(' ', '+', $_GET['user_email']))) {
    mailjet_subscribe_unsub_comment_author_to_list($_GET['subscribe'], str_ireplace(' ', '+', $_GET['user_email']));
}
