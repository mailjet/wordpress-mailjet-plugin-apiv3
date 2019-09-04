<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\MailjetApi;
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
class WooCommerceSettings
{
    private  $debuggerEmail = '';

    public function __construct()
    {
        $this->enqueueScripts();
        add_action('wp_ajax_get_contact_lists', [$this, 'subscribeViaAjax']);
    }

    public function mailjet_show_extra_woo_fields($checkout)
    {
        $user = wp_get_current_user();
        $checkoutBox = get_option('mailjet_woo_checkout_checkbox');
        $contactList = $this->getWooContactList();

        // Display the checkbox only for NOT-logged in users or for logged-in but not subscribed to the Woo list
        if ($contactList !== false && $checkoutBox === '1') {
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

        $subscribe = (int)filter_var($_POST['mailjet_woo_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);
        if ($subscribe === 1) {
            $order->update_meta_data('mailjet_woo_subscribe_ok', sanitize_text_field($_POST['mailjet_woo_subscribe_ok']));
            $this->mailjet_subscribe_confirmation_from_woo_form($subscribe, $wooUserEmail, $firstName, $lastName);
        }
    }

    /**
     *  Subscribe or unsubscribe a wordpress comment author in/from a Mailjet's contact list when the comment is saved
     */
    public function mailjet_subscribe_unsub_woo_to_list($subscribe, $user_email, $first_name, $last_name)
    {
        $action = (int)$subscribe === 1 ? 'addforce' : 'unsub';
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
            $subscribe = get_post_meta($order->get_id(), 'mailjet_woo_subscribe_ok', true);
            if ($subscribe == '1') {
                $str .= ' <br /><br /><i><b>We have sent the newsletter subscription confirmation link to you (<b>' . $order->get_billing_email() . '</b>). To confirm your subscription you have to click on the provided link.</i></b>';
            } elseif (get_option('mailjet_woo_banner_checkbox') === '1') {
                $str = $this->addThankYouSubscription($order);
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
        $mainList = get_option('mailjet_sync_list');
        if ((int)$mainList > 0) {
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

    private function getTemplateContent($callable)
    {

        if (!method_exists($this, $callable)){
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Method (' . $callable . ') can\'t be found!]');
            return [];
        }

        $fileTemp = call_user_func([$this, $callable]);

        if (!$fileTemp || empty($fileTemp)) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Method (' . $callable . ') has error!]');
            return [];
        }

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

        if ($activate && isset($data->mailjet_woo_edata_sync) && $data->mailjet_woo_edata_sync === '1') {
            if (get_option('mailjet_woo_edata_sync') !== '1') {
                if ($this->all_customers_edata_sync() === false) {
                    $result['success'] = false;
                    $result['message'] = __('An error occured during e-commerce data sync! Please try again later.');
                    return $result;
                }
            }
        }
        else {
            update_option('mailjet_woo_edata_sync', '');
        }

        foreach ($data as $key => $val) {
            $optionVal = $activate ? $val : '';
            update_option($key, sanitize_text_field($optionVal));
        }

        if ($activate) {
            $templates['woocommerce_abandoned_cart'] = ['id' => get_option('mailjet_woocommerce_abandoned_cart'), 'callable' => 'abandonedCartTemplateContent'];
            $templates['woocommerce_order_confirmation'] = ['id' => get_option('mailjet_woocommerce_order_confirmation'), 'callable' => 'orderCreatedTemplateContent'];
            $templates['woocommerce_refund_confirmation'] = ['id' => get_option('mailjet_woocommerce_refund_confirmation'), 'callable' => 'orderRefundTemplateContent'];
            $templates['woocommerce_shipping_confirmation'] = ['id' => get_option('mailjet_woocommerce_shipping_confirmation'), 'callable' => 'shippingConfirmationTemplateContent'];

            foreach ($templates as $name => $value) {
                if (!$value['id'] || empty($value['id'])) {
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
                        $templateContent['body'] = $this->getTemplateContent($value['callable']);
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
            'first_name' => $order->get_billing_first_name(),
            'order_number' => $orderId,
            'order_total' => $order->get_total(),
            'store_email' => get_option('mailjet_from_email'),
            'store_phone' => '',
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
            'order_link' => $order->get_view_order_url(),
        ];

        $templateId = get_option('mailjet_woocommerce_refund_confirmation');
        $data = $this->getFormattedEmailData($order, $vars, $templateId);
        $response = MailjetApi::sendEmail($data);
        if ($response === false){
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Automation email fails ][Request:]' . json_encode($data));
            return false;
        }

        return true;
    }

    public function send_order_status_processing($orderId)
    {
        $order = wc_get_order( $orderId );
        $templateId = get_option('mailjet_woocommerce_order_confirmation');
        if (!$order || empty($order) || !$templateId || empty($templateId)){
            return false;
        }
        if (!$order || empty($order)){
            return false;
        }


        $items = $order->get_items();
        $products = [];
        foreach ($items as $item){
            $itemData = $item->get_data();
            $data['variant_title'] = $itemData['name'];
            $data['price'] = $itemData['total'];
            $data['title'] = $itemData['name'];
            $data['quantity'] = $itemData['quantity'];
            $product = wc_get_product( $item['product_id'] );
            $data['image'] =  $product->get_image();
            $products[] = $data;
        }

        $vars = [
            'first_name' => $order->get_billing_first_name(),
            'order_number' => $orderId,
            'order_subtotal' => $order->get_subtotal(),
            'order_discount_total' => $order->get_discount_total(),
            'order_total_tax' => $order->get_tax_totals(),
            'order_shipping_total' => $order->get_shipping_total(),
            'order_shipping_address' => $order->get_shipping_address_1(),
            'order_billing_address' => $order->get_billing_address_1(),
            'order_total' => $order->get_formatted_order_total(),
            'order_link' => $order->get_view_order_url(),
            'store_email' => get_option('mailjet_from_email'),
            'store_phone' => '',
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
            'products' => $products,
        ];

        $data = $this->getFormattedEmailData($order, $vars, $templateId);
        $response = MailjetApi::sendEmail($data);



        if ($response === false){
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Automation email fails ][Request:]' . json_encode($data));
            return false;
        }

        return true;

    }

    public function send_abandoned_cart($orderId)
    {
        $order = wc_get_order( $orderId );
        $templateId = get_option('mailjet_woocommerce_abandoned_cart');
        if (!$order || empty($order) || !$templateId || empty($templateId)){
            return false;
        }

        $vars = [
            'first_name' => $order->get_billing_first_name(),
            'order_number' => $orderId,
            'order_total' => $order->get_formatted_order_total(),
            'store_email' => '',
            'store_phone' => '',
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
        ];

        $data = $this->getFormattedEmailData($order, $vars, $templateId);
        $response = MailjetApi::sendEmail($data);
        if ($response === false){
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Automation email fails ][Request:]' . json_encode($data));
            return false;
        }

        return true;
    }

    public function send_order_status_completed($orderId)
    {
        $order = wc_get_order( $orderId );
        $templateId = get_option('mailjet_woocommerce_shipping_confirmation');
        if (!$order || empty($order) || !$templateId || empty($templateId)){
            return false;
        }

        $vars = [
            'first_name' => $order->get_billing_first_name(),
            'order_number' => $orderId,
            'order_shipping_address' => $order->get_shipping_address_1(),
            'tracking_number' => $order->get_shipping_state(),
            'order_total' => $order->get_total(),
            'order_link' => $order->get_view_order_url(),
            'tracking_url' => $order->get_shipping_state(),
            'store_email' => get_option('mailjet_from_email'),
            'store_phone' => '',
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
        ];

        $data = $this->getFormattedEmailData($order, $vars, $templateId);
        $response = MailjetApi::sendEmail($data);

        if ($response === false){
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Automation email fails ][Request:]' . json_encode($data));
            return false;
        }

        return true;
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
            'Vars' => $vars
        ];

        $data = [];
        $data['FromEmail'] = get_option('mailjet_from_email');
        $data['FromName'] = get_option('mailjet_from_name');
        $data['Recipients'][] = $recipients;
        $data['Mj-TemplateID'] = $templateId;
        $data['Mj-TemplateLanguage'] = true;
        $data['Mj-TemplateErrorReporting'] = $this->debuggerEmail;
        $data['Mj-TemplateErrorDeliver'] = true;
        $data['body'] = $data;
        return $data;
    }

    private function abandonedCartTemplateContent()
    {
        $templateDetail['MJMLContent'] = require_once(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceAbandonedCartArray.php');
        $templateDetail['Html-part'] = file_get_contents(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceAbandonedCart.html');
        $templateDetail['Headers']= [
            'Subject' => 'There\'s something in your cart',
            'SenderName' => '{{var:store_name}}',
            'From' => '{{var:store_name:""}} <{{var:store_email:""}}>',
        ];

        return $templateDetail;
    }

    private function orderRefundTemplateContent()
    {
        $templateDetail['MJMLContent'] = require_once(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceRefundArray.php');
        $templateDetail['Html-part'] = file_get_contents(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceRefundConfirmation.html');
        $templateDetail['Headers']= [
            'Subject' => 'Your refund from {{var:store_name}}',
            'SenderName' => '{{var:store_name}}',
            'From' => '{{var:store_name:""}} <{{var:store_email:""}}>'
        ];

        return $templateDetail;
    }

    private function shippingConfirmationTemplateContent()
    {
        $templateDetail['MJMLContent'] =  require_once(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceShippingConfArray.php');
        $templateDetail["Html-part"] = file_get_contents(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceShippingConfirmation.html');
        $templateDetail['Headers']= [
            'Subject' => 'Your order from {{var:store_name}} has been shipped',
            'SenderName' => '{{var:store_name}}',
            'From' => '{{var:store_name:""}} <{{var:store_email:""}}>'
        ];

        return $templateDetail;
    }

    private function orderCreatedTemplateContent()
    {
        $templateDetail['MJMLContent'] = require_once(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceOrderConfArray.php');
        $templateDetail['Html-part'] = file_get_contents(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceOrderConfirmation.html');
        $templateDetail['Headers']= [
            'Subject' => 'We just received your order from {{var:store_name}} - {{var:order_number}}',
            'SenderName' => '{{var:store_name}}',
            'From' => '{{var:store_name:""}} <{{var:store_email:""}}>'
        ];

        return $templateDetail;
    }

    private function addThankYouSubscription($order)
    {
        $text = get_option('mailjet_woo_banner_text');
        $label = get_option('mailjet_woo_banner_label');
        set_query_var('orderId', $order->get_id());
        set_query_var('text', !empty($text) ? $text : 'Subscribe to our newsletter to get product updates.');
        set_query_var('btnLabel', !empty($label) ? $label : 'Subscribe now!');
        return load_template(MAILJET_FRONT_TEMPLATE_DIR . '/Subscription/subscriptionForm.php');
    }


    public function enqueueScripts()
    {
        $cssPath = plugins_url('/src/front/css/mailjet-front.css', MAILJET_PLUGIN_DIR . 'src');
        $scryptPath = plugins_url('/src/front/js/mailjet-front.js', MAILJET_PLUGIN_DIR . 'src');
        wp_register_style('mailjet-front', $cssPath);
        wp_register_script('ajaxHandle', $scryptPath, array('jquery'), false, true);
        wp_localize_script('ajaxHandle', 'mailjet', ['url' => admin_url( 'admin-ajax.php' )]);
        wp_enqueue_style('mailjet-front');
        wp_enqueue_script('ajaxHandle');
    }

    public function subscribeViaAjax()
    {
        $post = $_POST;

        if (isset($post['orderId'])) {
            $order = wc_get_order($post['orderId']);
            $message = 'You\'v subscribed successfully to our mail list.';
            $success = true;

            if (empty($order)){
                $message = 'Something went wrong.';
                $success = false;
            }else{
                $subscribe = $this->ajaxSubscription($order->get_billing_email(), $order->get_billing_first_name(), $order->get_billing_last_name());
                wp_send_json_success($subscribe);
            }

            wp_send_json_success([
                'message' => $message,
                'success' => $success
            ]);
        } else {
            wp_send_json_error();
        }
    }

    private function ajaxSubscription($email, $fName, $lName)
    {
       $listId = $this->getWooContactList();

       if (!$listId){
           return ['success' => false, 'message' => 'You can\'t be subscribed at this moment.'];
       }

       if (MailjetApi::checkContactSubscribedToList($email, $listId)){
           return ['success' => true, 'message' => 'You are already subscribed.'];
       }

       if ($this->mailjet_subscribe_unsub_woo_to_list(1, $email, $fName, $lName)){
           return ['success' => true, 'message' => 'You\'re successfully subscribed to our E-mail list.'];
       }

       return ['success' => false, 'message' => 'Something went wrong.'];
    }

    public function init_edata() {
        $propertyTypes = [
            'woo_total_orders_count' => 'int',
            'woo_total_spent' => 'float',
            'woo_last_order_date' => 'datetime',
            'woo_account_creation_date' => 'datetime'
        ];
        $properties = MailjetApi::getContactProperties();

        foreach ($properties as $prop){
            if (array_key_exists($prop['Name'], $propertyTypes)){
                unset($propertyTypes[$prop['Name']]);
            }
        }

        if (!empty($propertyTypes)){
            foreach ($propertyTypes as $propKey => $propType){
                MailjetApi::createMailjetContactProperty($propKey, $propType);
            }
        }
    }

    public function all_customers_edata_sync() {
        $mailjet_sync_list = get_option('mailjet_sync_list');
        if (empty($mailjet_sync_list) || $mailjet_sync_list < 0) {
            return false;
        }

        $this->init_edata();
        $users = get_users(array('fields' => array('ID', 'user_email'), 'role__in' => 'customer'));

        $unsubUsers = array();
        foreach ($users as $user) {
            $unsubUsers[$user->user_email] = $user;
        }

        $subscribedContacts = array();
        $unsubContacts = array();
        $subscribers = MailjetApi::getSubscribersFromList($mailjet_sync_list);
        if ($subscribers === false) {
            return false;
        }
        foreach ($subscribers as $sub) {
            $email = $sub['Contact']['Email']['Email'];
            if (array_key_exists($email, $unsubUsers)) {
                $user = $unsubUsers[$email];
                $properties = $this->get_customer_edata($user->ID);
                if (is_array($properties) && !empty($properties)) {
                    array_push($subscribedContacts, array(
                        'Email' => $user->user_email,
                        'Properties' => $properties
                    ));
                }
                unset($unsubUsers[$email]);
            }
        }

        foreach($unsubUsers as $user) {
            $userEmail = $user->user_email;
            if (!empty($userEmail)) {
                $properties = $this->get_customer_edata($user->ID);
                if (is_array($properties) && !empty($properties)) {
                    array_push($unsubContacts, array(
                        'Email' => $user->user_email,
                        'Properties' => $properties
                    ));
                }
            }
        }

        $success = true;
        if (!empty($subscribedContacts)) {
            if (false === MailjetApi::syncMailjetContacts($mailjet_sync_list, $subscribedContacts, 'addnoforce')) {
                $success = false;
            }
        }
        if (!empty($unsubContacts)) {
            if (false === MailjetApi::syncMailjetContacts($mailjet_sync_list, $unsubContacts, 'unsub')) {
                $success = false;
            }
        }

        return $success;
    }

    public function order_edata_sync($orderId) {
        $order = wc_get_order($orderId);
        $mailjet_sync_list = get_option('mailjet_sync_list');
        if ($order === false || empty($mailjet_sync_list) || $mailjet_sync_list < 0) {
            return false;
        }
        $status = $order->get_status();
        if ($status === 'processing' || $status === 'completed' || $status === 'cancelled' || $status === 'refunded') {
            $user = $order->get_user();
            if ($user === false) {
                return false;
            }
            $properties = $this->get_customer_edata($user->ID);
            if (is_array($properties) && !empty($properties)) {
                $contact = array(array(
                    'Email' => $user->user_email,
                    'Properties' => $properties
                ));
                $isSubscribed = MailjetApi::checkContactSubscribedToList($user->user_email, $mailjet_sync_list, true);
                $action = $isSubscribed ? 'addnoforce' : 'unsub';
                MailjetApi::syncMailjetContacts($mailjet_sync_list, $contact, $action);
            }
        }

        return true;
    }

    public function get_customer_edata($userId) {
        $userData = get_userdata($userId);

        $customerProperties = [];
        $userRoles = $userData->roles;
        if ($userRoles[0] === 'customer') {
            $args = array(
                'customer_id' => $userId,
                'status' => ['completed', 'processing'],
                'limit' => -1,
            );
            $orders = wc_get_orders($args);
            $customer = new \WC_Customer($userId);
            $customerProperties['woo_total_orders_count'] = (string)count($orders);
            $customerProperties['woo_total_spent'] = (string)$customer->get_total_spent();
            $customerProperties['woo_account_creation_date'] = $customer->get_date_created()->date('Y-m-d\TH:i:s\Z');
            if (is_array($orders) && !empty($orders)) {
                $customerProperties['woo_last_order_date'] = $orders[0]->get_date_paid()->date('Y-m-d\TH:i:s\Z');
            }
        }
        return $customerProperties;
    }
}
