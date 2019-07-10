<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\MailjetLoader;
use MailjetPlugin\Includes\MailjetLogger;
use MailjetPlugin\Includes\MailjetMail;
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

    private $loader;

    public function mailjet_show_extra_woo_fields($checkout)
    {
        $user = wp_get_current_user();
        $chaeckoutBox = get_option('mailjet_woo_checkout_checkbox');
        $chaeckoutText = get_option('mailjet_woo_checkout_box_text');

        $contactList = $this->getWooContactList();
        // Display the checkbox only for NOT-logged in users or for logged-in but not subscribed to the Woo list
//        if (get_option('activate_mailjet_woo_integration') && get_option('mailjet_woo_list')){
        if ($contactList !== false) {

            // Check if user is logged-in and already Subscribed to the contact list
            $contactAlreadySubscribedToList = false;
            if ($user->exists()) {
                $contactAlreadySubscribedToList = MailjetApi::checkContactSubscribedToList($user->data->user_email, $contactList);
            }

            if (!$contactAlreadySubscribedToList) {
                if (!function_exists('woocommerce_form_field')) {
                    return;
                }
                $boxMsg = get_option('mailjet_woo_checkout_box_text') ?: 'Subscribe to our newsletter';

                woocommerce_form_field('mailjet_woo_subscribe_ok', array(
                    'type' => 'checkbox',
                    'label' => __($boxMsg, 'mailjet-for-wordpress'),
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
            _e('Invalid email', 'mailjet-for-wordpress');
            die;
        }

        if (isset($_POST['_my_field_name']) && !empty($_POST['_my_field_name']))
            $order->update_meta_data('_my_field_name', sanitize_text_field($_POST['_my_field_name']));


        $subscribe = filter_var($_POST['mailjet_woo_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);
        if ($subscribe) {
            $order->update_meta_data('mailjet_woo_subscribe_ok', sanitize_text_field($_POST['mailjet_woo_subscribe_ok']));
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
        return SubscriptionOptionsSettings::syncSingleContactEmailToMailjetList($this->getWooContactList(), $user_email, $action, $contactproperties);
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
            _e($error, 'mailjet-for-wordpress');
            die;
        }

        if (!is_email($user_email)) {
            _e('Invalid email', 'mailjet-for-wordpress');
            die;
        }
        $wpUrl = sprintf('<a href="%s" target="_blank">%s</a>', get_home_url(), get_home_url());

        $message = file_get_contents(dirname(dirname(dirname(__FILE__))) . '/templates/confirm-subscription-email.php');
        $emailParams = array(
            '__EMAIL_TITLE__' => __('Please confirm your subscription', 'mailjet-for-wordpress'),
            '__EMAIL_HEADER__' => sprintf(__('To receive newsletters from %s please confirm your subscription by clicking the following button:', 'mailjet-for-wordpress'), $wpUrl),
            '__WP_URL__' => $wpUrl,
            '__CONFIRM_URL__' => get_home_url() . '?subscribe=' . $subscribe . '&user_email=' . $user_email . '&first_name=' . $first_name . '&last_name=' . $last_name . '&mj_sub_woo_token=' . sha1($subscribe . $user_email . $first_name . $last_name),
            '__CLICK_HERE__' => __('Yes, subscribe me to this list', 'mailjet-for-wordpress'),
            '__FROM_NAME__' => get_option('blogname'),
            '__IGNORE__' => __('If you received this email by mistake or don\'t wish to subscribe anymore, simply ignore this message.', 'mailjet-for-wordpress'),
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
            if ('1' == get_post_meta($order->get_id(), 'mailjet_woo_subscribe_ok', true)) {
                $str .= ' <br /><br /><i><b>We have sent the newsletter subscription confirmation link to you (<b>' . $order->get_billing_email() . '</b>). To confirm your subscription you have to click on the provided link.</i></b>';
            }
        }
        return $str;
    }

    private function getWooContactList()
    {
        $wooActiv = get_option('activate_mailjet_woo_integration');
        if (!$wooActiv) {

            return false;
        }
        $checkoutBox = get_option('mailjet_woo_checkout_checkbox');
        $mainList = get_option('mailjet_sync_list');
        $wooList = get_option('mailjet_woo_list');
        if (!empty($wooList)) {

            return $wooList;
        } elseif (!empty($mainList) && !empty($checkoutBox)) {

            return $mainList;
        }

        return false;
    }

    public static function getWooTemplate($templateType)
    {
        $templateId = get_option($templateType);

        if (!$templateId || empty($templateId)) {
            return false;
        }
        $templateDetails = MailjetApi::getTemplateDetails($templateId);

        if (!$templateDetails || empty($templateDetails)) {
            return false;
        }

        $templateDetails['Headers']['ID'] = $templateId;

        return $templateDetails;
    }

    public function toggleWooSettings($activeHooks)
    {

        if (get_option('mailjet_enabled') !== '1'){
            return false;
        }

        $avaliableActions = [
            'woocommerce_order_status_processing' => 'woocommerce_customer_processing_order_settings',
            'woocommerce_order_status_completed' => 'woocommerce_customer_completed_order_settings',
            'woocommerce_order_status_refunded' => 'woocommerce_customer_refunded_order_settings'
        ];

        $hooks = [];

        foreach ($activeHooks as $activeHook){
            $hooks[] = $activeHook['hook'];
        }

        $defaultSettings = [
            'enabled' => 'yes',
            'subject' => '',
            'heading' => '',
            'email_type' => 'html',
        ];

        foreach ($avaliableActions as $key => $hook) {
            $wooSettings = get_option($hook);
            $setting = $defaultSettings;
            if ($wooSettings) {
                $setting = $wooSettings;
                $setting['enabled'] = 'yes';
            }
            if (in_array($key, $hooks)){
                $setting['enabled'] = 'no';
            }
            update_option($hook, $setting);
        }

        return true;
    }


    private function getTemplateContent($templateName)
    {
        $templateFiles = [
            'woocommerce_abandoned_cart' => MAILJET_ADMIN_TAMPLATE_DIR . '\IntegrationAutomationTemplates\WooCommerceAbandonedCart.txt',
            'woocommerce_order_confirmation' => MAILJET_ADMIN_TAMPLATE_DIR . '\IntegrationAutomationTemplates\WooCommerceOrderConfirmation.txt',
            'woocommerce_refund_confirmation' => MAILJET_ADMIN_TAMPLATE_DIR . '\IntegrationAutomationTemplates\WooCommerceRefundConfirmation.txt',
            'woocommerce_shipping_confirmation' => MAILJET_ADMIN_TAMPLATE_DIR . '\IntegrationAutomationTemplates\WooCommerceShippingConfirmation.txt',
        ];

        $fileTemp = file_get_contents($templateFiles[$templateName]);

        if (!$fileTemp || empty($fileTemp)) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Teplate (' . $templateName . ') can\'t be found!]');
            return [];
        }

        $fileTemp = json_decode($fileTemp, true);
        $fileTemp = $fileTemp['Data'][0];

        //Add sender Email to headers
        $fromEmail = get_option('mailjet_from_email');
        $fileTemp['Headers']['SenderEmail'] = $fromEmail;

        return $fileTemp;

    }

    public function activateWoocommerce($data)
    {
        $result['success'] = true;

        $activate = true;
        if (!isset($data->activate_mailjet_woo_integration) || $data->activate_mailjet_woo_integration !== '1') {
            update_option('activate_mailjet_woo_integration', '');
            $activate = false;
        }
        foreach ($data as $key => $val) {
            $optionVal = $activate ? $val : '';
            update_option($key, sanitize_text_field($optionVal));
        }

        if ($activate) {
            $templates['woocommerce_abandoned_cart'] = get_option('mailjet_woocommerce_abandoned_cart');
            $templates['woocommerce_order_confirmation'] = get_option('mailjet_woocommerce_order_confirmation');
            $templates['woocommerce_refund_confirmation'] = get_option('mailjet_woocommerce_refund_confirmation');
            $templates['woocommerce_shipping_confirmation'] = get_option('mailjet_woocommerce_shipping_confirmation');

            foreach ($templates as $name => $value) {
                if (!$value || empty($value)) {
                    $templateArgs = [
                        "Author" => "Mailjet WC integration",
                        "Categories" => ['e-commerce'],
                        "Copyright" => "Mailjet",
                        "Description" => "Used to send automation emails.",
                        "EditMode" => 1,
                        "IsStarred" => false,
                        "IsTextPartGenerationEnabled" => true,
                        "Locale" => "en_US",
                        "Name" => ucwords(str_replace('_', ' ', $name)),
                        "OwnerType" => "user",
                        "Presets" => "string",
                        "Purposes" => ['automation']
                    ];

                    $template = MailjetApi::createAutomationTemplate(['body' => $templateArgs, 'filters' => []]);

                    if ($template && !empty($template)) {
                        $templateContent = [];
                        $templateContent['id'] = $template['ID'];
                        $templateContent['body'] = $this->getTemplateContent($name);
                        $templateContent['filters'] = [];
                        add_option('mailjet_' . $name, $template['ID']);
                        $contentCreation = MailjetApi::createAutomationTemplateContent($templateContent);
                        if (!$contentCreation || empty($contentCreation)) {
                            $result['success'] = false;
                        }
                    } else {
                        $result['success'] = false;
                    }
                }
            }
        }

        $result['message'] = $result['success'] === true ? 'Integrations updated successfully.' : 'Something went wrong! Please try again later.';

        return $result;

    }

    public function send_order_status_refunded($orderId)
    {
        $order = wc_get_order( $orderId );

        if (!$order || empty($order)){
            return false;
        }

        $vars = [
            'customer_firstname' => $order->get_billing_first_name(),
            'order_number' => $orderId,
            'order_total' => $order->get_total(),
            'store_email' => get_option('mailjet_from_email'),
            'store_phone' => '',
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
        ];

        $templateId = get_option('mailjet_woocommerce_refund_confirmation');

        $data = $this->getFormattedEmailData($order, $vars, $templateId);

        MailjetApi::sendEmail($data);



    }

    public function send_order_status_processing($orderId)
    {
        $order = wc_get_order( $orderId );

        if (!$order || empty($order)){
            return false;
        }
        $items = $order->get_items();
        $vars = [
            'customer_firstname' => $order->get_billing_first_name(),
            'order_number' => $orderId,
            'order_total' => $order->get_formatted_order_total(),
            'store_email' => get_option('mailjet_from_email'),
            'store_phone' => '',
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
            'products' => $order->get_items(),
        ];




        $templateId = get_option('mailjet_woocommerce_refund_confirmation');

        $data = $this->getFormattedEmailData($order, $vars, $templateId);


    }

    public function send_abandoned_cart($orderId)
    {
        $order = wc_get_order( $orderId );

        if (!$order || empty($order)){
            return false;
        }

        $vars = [
            'customer_firstname' => $order->get_billing_first_name(),
            'order_number' => $orderId,
            'order_total' => $order->get_formatted_order_total(),
            'store_email' => '',
            'store_phone' => '',
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
        ];

        $templateId = get_option('mailjet_woocommerce_refund_confirmation');

        $data = $this->getFormattedEmailData($order, $vars, $templateId);
    }

    public function send_order_status_completed($orderId)
    {
        $order = wc_get_order( $orderId );

        if (!$order || empty($order)){
            return false;
        }

        $vars = [
            'customer_firstname' => $order->get_billing_first_name(),
            'order_number' => $orderId,
            'order_shipping_address' => $order->get_shipping_address_1(),
            'tracking_number' => $order->get_shipping_state(),
            'store_phone' => '',
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
        ];

        $templateId = get_option('mailjet_woocommerce_refund_confirmation');

        $data = $this->getFormattedEmailData($order, $vars, $templateId);
    }

    public function orders_automation_settings_post()
    {
        $data = $_POST;

        if (!wp_verify_nonce($data['custom_nonce'],'mailjet_order_notifications_settings_page_html')){
            update_option('mailjet_post_update_message', ['success' => false, 'message' => 'Invalid credentials!']);
            wp_redirect(add_query_arg(array('page' => 'mailjet_order_notifications_page'), admin_url('admin.php')));
        }

        $activeHooks = $this->prepareAutomationHooks($data);

        $this->toggleWooSettings($activeHooks);

        $notifications = isset($data['mailjet_wc_active_hooks']) ? $data['mailjet_wc_active_hooks'] : [];

        update_option('mailjet_wc_active_hooks', $activeHooks);
        update_option('mailjet_order_notifications', $notifications);

        update_option('mailjet_post_update_message', ['success' => true, 'message' => 'Automation settings updated!']);
        wp_redirect(add_query_arg(array('page' => 'mailjet_order_notifications_page'), admin_url('admin.php')));
    }

    private function prepareAutomationHooks($data)
    {
        if (!isset($data['mailjet_wc_active_hooks'])){
           return [];
        }

        $actions = [
            'mailjet_order_confirmation' => ['hook' => 'woocommerce_order_status_processing', 'callable' => 'send_order_status_processing'],
            'mailjet_shipping_confirmation' =>  ['hook' => 'woocommerce_order_status_completed', 'callable' => 'send_order_status_completed'],
            'mailjet_refund_confirmation' =>  ['hook' => 'woocommerce_order_status_refunded', 'callable' => 'send_order_status_refunded']
        ];
        $result = [];
        foreach ($data['mailjet_wc_active_hooks'] as $key => $val){
            if ($val === '1'){
                $result[] = $actions[$key];
            }
        };

        return $result;

    }

    private function getFormattedEmailData($order, $vars, $templateId)
    {
        $recipients = [
            'Email' => $order->get_billing_email(),
            'Name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'Vars' => json_encode($vars)
        ];

        $data = [];
        $data['FromEmail'] = get_option('mailjet_from_email');
        $data['FromName'] = get_option('mailjet_from_name');
        $data['Recipients'] = json_encode([$recipients]);
        $data['Mj-TemplateID'] = $templateId;
        $data['Mj-TemplateLanguage'] = true;

        return $data;
    }
}
