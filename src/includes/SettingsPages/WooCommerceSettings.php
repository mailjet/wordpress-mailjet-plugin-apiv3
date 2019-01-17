<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\MailjetApi;
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
class WooCommerceSettings
{
    public function mailjet_show_extra_woo_fields($checkout)
    {
        $user = wp_get_current_user();

        // Display the checkbox only for NOT-logged in users or for logged-in but not subscribed to the Woo list
        if (get_option('activate_mailjet_woo_integration') && get_option('mailjet_woo_list')){

            // Check if user is logged-in and already Subscribed to the contact list
            if ($user->exists()) {
                $contactAlreadySubscribedToList = MailjetApi::checkContactSubscribedToList($user->data->user_email, get_option('mailjet_woo_list'));
            }

            if (!$user->exists() || false == $contactAlreadySubscribedToList) {
                if (!function_exists('woocommerce_form_field')) {
                    return;
                }
                woocommerce_form_field('mailjet_woo_subscribe_ok', array(
                    'type' => 'checkbox',
                    'label' => __('Subscribe to our newsletter', 'mailjet'),
                    'required' => false,
                ), $checkout->get_value('mailjet_woo_subscribe_ok'));
            }
        }
    }


    public function mailjet_subscribe_woo($order, $data)
    {
        $wooUserEmail = filter_var($order->get_billing_email(), FILTER_SANITIZE_EMAIL);
        $firstName = $order->get_billing_first_name();
        $lastName = $order->get_billing_last_name();

        if (!is_email($wooUserEmail)) {
            _e('Invalid email', 'mailjet');
            die;
        }

        if( isset($_POST['_my_field_name']) && ! empty($_POST['_my_field_name']) )
            $order->update_meta_data( '_my_field_name', sanitize_text_field( $_POST['_my_field_name'] ) );


        $subscribe = filter_var($_POST['mailjet_woo_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);
        if ($subscribe) {
            $order->update_meta_data( 'mailjet_woo_subscribe_ok', sanitize_text_field( $_POST['mailjet_woo_subscribe_ok'] ) );
            $this->mailjet_subscribe_confirmation_from_woo_form($subscribe, $wooUserEmail, $firstName, $lastName);
        }
    }


    /**
     *  Subscribe or unsubscribe a wordpress comment author in/from a Mailjet's contact list when the comment is saved
     */
    public function mailjet_subscribe_unsub_woo_to_list($subscribe, $user_email, $first_name, $last_name)
    {
        $action = intval($subscribe) === 1 ? 'addforce' : 'remove';
        $contactproperties = [];
        if (!empty($first_name)) {
            MailjetApi::createMailjetContactProperty('firstname');
            $contactproperties['firstname'] = $first_name;
        }
        if (!empty($last_name)) {
            MailjetApi::createMailjetContactProperty('lastname');
            $contactproperties['lastname'] = $last_name;
        }

        // Add the user to a contact list
        return SubscriptionOptionsSettings::syncSingleContactEmailToMailjetList(get_option('mailjet_woo_list'), $user_email, $action, $contactproperties);
    }




    /**
     * Email the collected widget data to the customer with a verification token
     * @param void
     * @return void
     */
    public function mailjet_subscribe_confirmation_from_woo_form($subscribe, $user_email, $first_name, $last_name)
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
        $wpUrl = sprintf('<a href="%s" target="_blank">%s</a>', get_home_url(), get_home_url());

        $message = file_get_contents(dirname(dirname(dirname(__FILE__))) . '/templates/confirm-subscription-email.php');
        $emailParams = array(
            '__EMAIL_TITLE__' => __('Please confirm your subscription', 'mailjet'),
            '__EMAIL_HEADER__' => sprintf(__('To receive newsletters from %s please confirm your subscription by clicking the following button:', 'mailjet'), $wpUrl),
            '__WP_URL__' => $wpUrl,
            '__CONFIRM_URL__' => get_home_url() . '?subscribe=' . $subscribe . '&user_email=' . $user_email . '&first_name=' . $first_name . '&last_name=' . $last_name . '&mj_sub_woo_token=' . sha1($subscribe . $user_email . $first_name . $last_name),
            '__CLICK_HERE__' => __('Yes, subscribe me to this list', 'mailjet'),
            '__FROM_NAME__' => get_option('blogname'),
            '__IGNORE__' => __('If you received this email by mistake or don\'t wish to subscribe anymore, simply ignore this message.', 'mailjet'),
        );
        foreach ($emailParams as $key => $value) {
            $message = str_replace($key, $value, $message);
        }

        $email_subject = __('Subscription Confirmation', 'mailjet');
        add_filter('wp_mail_content_type', array(new SubscriptionOptionsSettings(), 'set_html_content_type'));
        $res = wp_mail($user_email, $email_subject, $message,
            array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
    }


    /**
     * Function to change the "Thank you" text for WooCommerce order processed page - to add message that user has received subscription confirmation email
     *
     * @param $str
     * @param $order
     * @return string
     */
    public function woo_change_order_received_text($str, $order)
    {
        if (!empty($order)) {
            if ('1' == get_post_meta($order->get_id(), 'mailjet_woo_subscribe_ok', true )) {
                $str .= ' <br /><br /><i><b>We have sent the newsletter subscription confirmation link to you (<b>' . $order->get_billing_email() . '</b>). To confirm your subscription you have to click on the provided link.</i></b>';
            }
        }
        return $str;
    }

}
