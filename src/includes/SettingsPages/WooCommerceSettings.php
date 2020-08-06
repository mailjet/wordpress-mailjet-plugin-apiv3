<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\MailjetApi;
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
class WooCommerceSettings
{
    CONST WOO_PROP_TOTAL_ORDERS = 'woo_total_orders_count';
    CONST WOO_PROP_TOTAL_SPENT = 'woo_total_spent';
    CONST WOO_PROP_LAST_ORDER_DATE = 'woo_last_order_date';
    CONST WOO_PROP_ACCOUNT_CREATION_DATE = 'woo_account_creation_date';

    private static $instance;

    private function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontScripts']);
        add_action('wp_ajax_get_contact_lists', [$this, 'subscribeViaAjax']);
        // cron settings for abandoned cart feature
        add_filter('cron_schedules', [$this, 'add_cron_schedule']);
        add_filter( 'template_include', [$this, 'manage_action']);
    }

    public static function getInstance() {
        if (is_null(self::$instance))  {
            self::$instance = new WooCommerceSettings();
        }
        return self::$instance;
    }

    public function add_cron_schedule($schedules) {
        $schedules['one_minute'] = array(
            'interval'  => 60,
            'display'   => 'Once Every Minute',
        );
        return $schedules;
    }

    public function manage_action($template) {
        $action_name = '';

        if (isset( $_GET['mj_action'])) {
            $action_name = sanitize_text_field($_GET['mj_action']);
        }
        if ($action_name == 'track_cart') {
            $emailId = sanitize_text_field($_GET['email_id']);
            $key = sanitize_text_field($_GET['key']);
            $this->retrieve_cart($emailId, $key);
        }
        else if ($action_name == 'to_cart') {
            if (is_user_logged_in()) {
                wp_safe_redirect(get_permalink(wc_get_page_id('cart')));
            }
        }
        return $template;
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
                $boxMsg = stripslashes(get_option('mailjet_woo_checkout_box_text')) ?: __('Subscribe to our newsletter', 'mailjet-for-wordpress');

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
            if (!$this->mailjet_subscribe_confirmation_from_woo_form($subscribe, $wooUserEmail, $firstName, $lastName)) {
                die;
            }
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
            MailjetApi::createMailjetContactProperty(SubscriptionOptionsSettings::PROP_USER_FIRSTNAME);
            $contactproperties[SubscriptionOptionsSettings::PROP_USER_FIRSTNAME] = $first_name;
        }
        if (!empty($last_name)) {
            MailjetApi::createMailjetContactProperty(SubscriptionOptionsSettings::PROP_USER_LASTNAME);
            $contactproperties[SubscriptionOptionsSettings::PROP_USER_LASTNAME] = $last_name;
        }

        // e-data sync if needed for WooCommerce guest subscription
        if ($action === 'addforce' && get_user_by('email', $user_email) === false) { // check for guest subscription
            $activate_mailjet_woo_integration = get_option('activate_mailjet_woo_integration');
            $mailjet_woo_edata_sync = get_option('mailjet_woo_edata_sync');
            if ((int)$activate_mailjet_woo_integration === 1 && (int)$mailjet_woo_edata_sync === 1) {
                $mailjet_sync_list = get_option('mailjet_sync_list');
                if (!empty($mailjet_sync_list) && $mailjet_sync_list > 0) {
                    $edataGuestProperties = $this->get_guest_edata($user_email);
                    if (is_array($edataGuestProperties) && !empty($edataGuestProperties)) {
                        $contactproperties = array_merge($contactproperties, $edataGuestProperties);
                    }
                }
            }
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
            return false;
        }

        if (!is_email($user_email)) {
            _e('Invalid email', 'mailjet-for-wordpress');
            return false;
        }
        $wpUrl = sprintf('<a href="%s" target="_blank">%s</a>', get_home_url(), get_home_url());

        $subscriptionTemplate = apply_filters('mailjet_confirmation_email_filename', dirname(dirname(dirname(__FILE__))) . '/templates/confirm-subscription-email.php');
        $message = file_get_contents($subscriptionTemplate);

        $emailParams = array(
            '__EMAIL_TITLE__' => __('Please confirm your subscription', 'mailjet-for-wordpress'),
            '__EMAIL_HEADER__' => sprintf(__('To receive newsletters from %s please confirm your subscription by clicking the following button:', 'mailjet-for-wordpress'), $wpUrl),
            '__WP_URL__' => $wpUrl,
            '__CONFIRM_URL__' => get_home_url() . '?subscribe=' . $subscribe . '&user_email=' . $user_email . '&first_name=' . $first_name . '&last_name=' . $last_name . '&mj_sub_woo_token=' . sha1($subscribe . $user_email . $first_name . $last_name . MailjetSettings::getCryptoHash()),
            '__CLICK_HERE__' => __('Yes, subscribe me to this list', 'mailjet-for-wordpress'),
            '__FROM_NAME__' => get_option('blogname'),
            '__IGNORE__' => __('If you received this email by mistake or don\'t wish to subscribe anymore, simply ignore this message.', 'mailjet-for-wordpress'),
        );
        foreach ($emailParams as $key => $value) {
            $message = str_replace($key, $value, $message);
        }

        $email_subject = __('Subscription Confirmation', 'mailjet-for-wordpress');
        add_filter('wp_mail_content_type', array(SubscriptionOptionsSettings::getInstance(), 'set_html_content_type'));
        $res = wp_mail($user_email, $email_subject, $message,
            array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
        return $res;
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
        $user = wp_get_current_user();
        $contactList = $this->getWooContactList();
        if (!empty($order) && $contactList !== false) {
            $contactAlreadySubscribedToList = false;
            if ($user->exists()) {
                $contactAlreadySubscribedToList = MailjetApi::checkContactSubscribedToList($user->data->user_email, $contactList);
            }
            if (!$contactAlreadySubscribedToList) {
                $subscribe = get_post_meta($order->get_id(), 'mailjet_woo_subscribe_ok', true);
                if ((int)$subscribe === 1) {
                    $str .= ' <br /><br /><i><b>' . __('We have sent the newsletter subscription confirmation link to you: ') . '<b>' . $order->get_billing_email() . '</b>. ' . __('To confirm your subscription you have to click on the provided link.') . '</i></b>';
                } elseif (get_option('mailjet_woo_banner_checkbox') === '1') {
                    $str .= $this->addThankYouSubscription($order);
                }
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
        $availableActions = [
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

        foreach ($availableActions as $key => $hook) {
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
        if (!isset($data['activate_mailjet_woo_integration']) || $data['activate_mailjet_woo_integration'] !== '1') {
            update_option('activate_mailjet_woo_integration', '');
            $activate = false;
        }

        if ($activate && isset($data['mailjet_woo_edata_sync']) && $data['mailjet_woo_edata_sync'] === '1') {
            if (get_option('mailjet_woo_edata_sync') !== '1') {
                if ($this->all_customers_edata_sync() === false) {
                    $result['success'] = false;
                    $result['message'] = __('An error occured during e-commerce data sync! Please try again later.');
                    return $result;
                }
            }
        }

        $checkboxesNames = array(
            'activate_mailjet_woo_integration',
            'mailjet_woo_edata_sync',
            'mailjet_woo_checkout_checkbox',
            'mailjet_woo_banner_checkbox'
        );
        foreach ($checkboxesNames as $checkboxName) {
            if ($activate && (int)$data[$checkboxName] === 1) {
                update_option($checkboxName, '1');
            }
            else {
                update_option($checkboxName, '');
            }
        }
        foreach ($data as $dataName => $dataValue) {
            if (!in_array($dataName, $checkboxesNames, true)) {
                update_option($dataName, $dataValue);
            }
        }

        if ($activate) {
            if ($this->createTemplates() === false) {
                $result['success'] = false;
                $result['message'] = __('An error occured during templates creation! Please try again later.');
                return $result;
            }

            // Abandoned cart default data
            update_option('mailjet_woo_abandoned_cart_activate', 0);
            add_option('mailjet_woo_abandoned_cart_sending_time', 1200); // 20 * 60 = 1200s

            $this->createAbandonedCartTables();
        }
        $this->toggleAbandonedCart();

        $result['message'] = $result['success'] === true ? 'Integrations updated successfully.' : 'Something went wrong! Please try again later.';

        return $result;

    }

    private function createAbandonedCartTables() {
        global $wpdb;
        $wcap_collate = '';
        if ( $wpdb->has_cap( 'collation' ) ) {
            $wcap_collate = $wpdb->get_charset_collate();
        }
        $table_name = $wpdb->prefix . 'mailjet_wc_abandoned_carts';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `abandoned_cart_info` text NOT NULL,
                    `abandoned_cart_time` int(11) NOT NULL,
                    `cart_ignored` boolean NOT NULL,
                    `user_type` text NOT NULL,
                    PRIMARY KEY (`id`)
                    ) $wcap_collate AUTO_INCREMENT=1 ";

        require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $table_name = $wpdb->prefix . 'mailjet_wc_abandoned_cart_emails';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `abandoned_cart_id` text NOT NULL,
                    `sent_time` int(11) NOT NULL,
                    `security_key` text NOT NULL,
                    PRIMARY KEY (`id`)
                    ) $wcap_collate AUTO_INCREMENT=1 ";
        dbDelta( $sql );

        $table_name = $wpdb->prefix . 'mailjet_wc_guests';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `billing_email` text NOT NULL,
                    `guest_name` text,
                    PRIMARY KEY (`id`)
                    ) $wcap_collate AUTO_INCREMENT=75000000";
        dbDelta( $sql );
    }

    public function createTemplates($forAbandonedCart = true, $forOrderNotif = true) {
        $templatesDetails = [];
        if ($forAbandonedCart) {
            $templates['woocommerce_abandoned_cart'] = ['id' => get_option('mailjet_woocommerce_abandoned_cart'), 'callable' => 'abandonedCartTemplateContent'];
        }
        if ($forOrderNotif) {
            $templates['woocommerce_order_confirmation'] = ['id' => get_option('mailjet_woocommerce_order_confirmation'), 'callable' => 'orderCreatedTemplateContent'];
            $templates['woocommerce_refund_confirmation'] = ['id' => get_option('mailjet_woocommerce_refund_confirmation'), 'callable' => 'orderRefundTemplateContent'];
            $templates['woocommerce_shipping_confirmation'] = ['id' => get_option('mailjet_woocommerce_shipping_confirmation'), 'callable' => 'shippingConfirmationTemplateContent'];
        }

        $templateArgs = [
            "Author" => "Mailjet WC integration",
            "Categories" => ['e-commerce'],
            "Copyright" => "Mailjet",
            "Description" => "Used to send automation emails.",
            "EditMode" => 1,
            "IsStarred" => false,
            "IsTextPartGenerationEnabled" => true,
            "Locale" => "en_US",
            "Name" => "",
            "OwnerType" => "apikey",
            "Presets" => "string",
            "Purposes" => ['transactional']
        ];

        foreach ($templates as $name => $value) {
            $templateArgs['Name'] = ucwords(str_replace('_', ' ', $name));

            // Create template or retrieve it if exists
            $template = MailjetApi::createTemplate(['body' => $templateArgs, 'filters' => []]);
            if ($template && !empty($template)) {
                $templateDetails = MailjetApi::getTemplateDetails($template['ID']);
                if (!$templateDetails || empty($templateDetails)) {
                    $templateContent = [];
                    $templateContent['id'] = $template['ID'];
                    $templateContent['body'] = $this->getTemplateContent($value['callable']);
                    $templateContent['filters'] = [];
                    $result = MailjetApi::createTemplateContent($templateContent);
                    if (!$result || empty($result)) {
                        return false;
                    }
                    $templateDetails = $result[0];
                }
                $templateDetails['Headers']['ID'] = $template['ID'];
                $templatesDetails['mailjet_' . $name] = $templateDetails;
                update_option('mailjet_' . $name, $template['ID']);
            }
            else {
                return false;
            }
        }
        return $templatesDetails;
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
            'order_total' => wc_price($order->get_total()),
            'store_email' => get_option('mailjet_from_email'),
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
            'order_link' => $order->get_view_order_url(),
        ];

        $templateId = get_option('mailjet_woocommerce_refund_confirmation');
        $data = $this->getFormattedEmailData($this->getOrderRecipients($order, $vars), $templateId);
        $response = MailjetApi::sendEmail($data);
        if ($response === false){
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Automation email fails ][Request:]' . json_encode($data));
            return false;
        }

        return true;
    }

    public function send_order_processing_paid_by_cheque($status, $order) {
        if ($order && (int)$order->get_id() > 0) {
            $this->send_order_status_processing_once($order->get_id());
        }
    }

    public function send_order_status_processing_once($orderId) {
        $order = wc_get_order($orderId);
        if (!empty($order) && $order->get_meta('processing_email_sent') !== 'true') {
            $this->send_order_status_processing($orderId);
        }
    }

    public function send_order_status_processing($orderId)
    {
        $order = wc_get_order( $orderId );
        $templateId = get_option('mailjet_woocommerce_order_confirmation');
        if (!$order || empty($order) || !$templateId || empty($templateId)){
            return;
        }

        $items = $order->get_items();
        $products = [];
        foreach ($items as $item){
            $itemData = $item->get_data();
            $productImgUrl = wp_get_attachment_url(get_post_thumbnail_id($item['product_id']));
            $data['variant_title'] = $itemData['name'];
            $data['price'] = wc_price($itemData['total']);
            $data['title'] = $itemData['name'];
            $data['quantity'] = $itemData['quantity'];
            $data['image'] =  $productImgUrl ?: '';
            $products[] = $data;
        }

        $vars = [
            'first_name' => $order->get_billing_first_name(),
            'order_number' => $orderId,
            'order_subtotal' => wc_price($order->get_subtotal()),
            'order_discount_total' => wc_price($order->get_discount_total()),
            'order_total_tax' => wc_price($order->get_tax_totals()),
            'order_shipping_total' => wc_price($order->get_shipping_total()),
            'order_shipping_address' => $order->get_formatted_shipping_address(),
            'order_billing_address' => $order->get_formatted_billing_address(),
            'order_total' => $order->get_formatted_order_total(),
            'order_link' => $order->get_view_order_url(),
            'store_email' => get_option('mailjet_from_email'),
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
            'products' => $products,
        ];

        $data = $this->getFormattedEmailData($this->getOrderRecipients($order, $vars), $templateId);
        $response = MailjetApi::sendEmail($data);

        if ($response === false){
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Automation email fails ][Request:]' . json_encode($data));
            return;
        }
        update_post_meta($orderId, 'processing_email_sent', 'true');
    }

    public function cart_change_timestamp() {
        global $wpdb, $woocommerce;

        if ($woocommerce->cart === null) {
            return;
        }

        $currentTime = current_time('timestamp');
        $sendingDelay = get_option( 'mailjet_woo_abandoned_cart_sending_time' );
        $ignoreCart = false;

        if ( is_user_logged_in() ) {
            $userType = 'REGISTERED';
            $user_id = get_current_user_id();
        }
        else {
            $userType = 'GUEST';
            $user_id = WC()->session->get( 'user_id' );
            $user_id = ($user_id >= 75000) ? $user_id : 0;
        }
        if ($user_id !== 0) {
            $query = 'SELECT * FROM `' . $wpdb->prefix . 'mailjet_wc_abandoned_carts`
                        WHERE user_id = %d
                        AND cart_ignored = %d
                        AND user_type = %s';
            $results = $wpdb->get_results($wpdb->prepare($query, $user_id, $ignoreCart, $userType));

            $cart = json_encode($woocommerce->cart->get_cart());

            if (0 === count($results)) {
                if (isset($cart) && !empty($cart) && $cart !== '[]') {
                    $insert_query = 'INSERT INTO `' . $wpdb->prefix . 'mailjet_wc_abandoned_carts`
                                 (user_id, abandoned_cart_info, abandoned_cart_time, cart_ignored, user_type)
                                 VALUES (%d, %s, %d, %d, %s)';
                    $wpdb->query($wpdb->prepare($insert_query, $user_id, $cart, $currentTime, $ignoreCart, $userType));
                }
            }
            elseif (isset($results[0]->abandoned_cart_time) && $results[0]->abandoned_cart_time + $sendingDelay > $currentTime) {
                if (isset($cart) && !empty($cart) && $cart !== '[]') {
                    $query_update = 'UPDATE `' . $wpdb->prefix . 'mailjet_wc_abandoned_carts`
                                     SET abandoned_cart_info = %s,
                                         abandoned_cart_time = %d
                                     WHERE user_id  = %d 
                                     AND user_type = %s
                                     AND cart_ignored = %s';
                    $wpdb->query($wpdb->prepare($query_update, $cart, $currentTime, $user_id, $userType, $ignoreCart));
                }
                else { // ignore cart if empty
                    $query_update = 'UPDATE `' . $wpdb->prefix . 'mailjet_wc_abandoned_carts`
                                     SET abandoned_cart_info = %s,
                                         abandoned_cart_time = %d,
                                         cart_ignored = %d
                                     WHERE user_id  = %d
                                     AND user_type = %s
                                     AND cart_ignored = %s';
                    $wpdb->query($wpdb->prepare($query_update, $cart, $currentTime, !$ignoreCart, $user_id, $userType, $ignoreCart));
                }
            }
            else {
                $query_update = 'UPDATE `' . $wpdb->prefix . 'mailjet_wc_abandoned_carts`
                                 SET cart_ignored = %d
                                 WHERE user_id  = %d
                                 AND user_type = %s';
                $wpdb->query($wpdb->prepare($query_update, !$ignoreCart, $user_id, $userType));

                if (isset($cart) && !empty($cart) && $cart !== '[]') {
                    $insert_query = 'INSERT INTO `' . $wpdb->prefix . 'mailjet_wc_abandoned_carts`
                                     (user_id, abandoned_cart_info, abandoned_cart_time, cart_ignored, user_type)
                                     VALUES (%d, %s, %d, %d, %s)';
                    $wpdb->query($wpdb->prepare($insert_query, $user_id, $cart, $currentTime, $ignoreCart, $userType));
                }
            }
        }
    }

    public function send_abandoned_cart_emails() {
        global $wpdb;
        $sendingDelay = get_option( 'mailjet_woo_abandoned_cart_sending_time' );
        $compareTime = current_time('timestamp') - $sendingDelay;
        $query = 'SELECT cart.*, wpuser.display_name as user_name, wpuser.user_email, wcguest.guest_name, wcguest.billing_email as guest_email  
                  FROM `' . $wpdb->prefix . 'mailjet_wc_abandoned_carts` AS cart 
                  LEFT JOIN `' . $wpdb->prefix . 'users` AS wpuser ON cart.user_id = wpuser.id 
                  LEFT JOIN `' . $wpdb->prefix . 'mailjet_wc_guests` AS wcguest ON cart.user_id = wcguest.id
                  WHERE cart_ignored = 0
                  AND abandoned_cart_time < %d';
        $results = $wpdb->get_results($wpdb->prepare($query, $compareTime));
        foreach ($results as $cart) {
            if ($this->send_abandoned_cart($cart)) {
                $query_update = 'UPDATE `' . $wpdb->prefix . 'mailjet_wc_abandoned_carts`
                         SET cart_ignored = %d
                         WHERE id  = %d';
                $wpdb->query($wpdb->prepare($query_update, 1, $cart->id));
            }
        }
    }

    private function register_sent_email($cart, $securityKey) {
        global $wpdb;
        $cartId = $cart->id;
        $currentTime = current_time('timestamp');
        $insert_query = 'INSERT INTO `' . $wpdb->prefix . 'mailjet_wc_abandoned_cart_emails`
                         (abandoned_cart_id, sent_time, security_key)
                         VALUES (%d, %d, %s)';
        $wpdb->query($wpdb->prepare($insert_query, $cartId, $currentTime, $securityKey));
        return $wpdb->insert_id;
    }

    private function send_abandoned_cart($cart) {
        $templateId = get_option('mailjet_woocommerce_abandoned_cart');
        if (!$cart || empty($cart) || !$templateId || empty($templateId)){
            return false;
        }
        $cartProducts = json_decode($cart->abandoned_cart_info, true);
        if (!is_array($cartProducts) || count($cartProducts) <= 0) {
            return false;
        }

        $products = [];
        foreach ($cartProducts as $key => $cartProduct) {
            $productDetails = wc_get_product($cartProduct['product_id']);
            $productImgUrl = wp_get_attachment_url(get_post_thumbnail_id($cartProduct['product_id']));
            $product = [];
            $product['title'] = $productDetails->get_title();
            $product['variant_title'] = '';
            $product['image'] = $productImgUrl ?: '';
            $product['quantity'] = $cartProduct['quantity'];
            $product['price'] = wc_price($productDetails->get_price());
            array_push($products, $product);
        }

        // generate a random string as security
        try {
            $securityKey = bin2hex(random_bytes(5));
        }
        catch(Exception $e) {
            $securityKey = mt_rand(99999);
        }
        $mailId = $this->register_sent_email($cart, $securityKey);
        $abandoned_cart_link = get_permalink(wc_get_page_id('cart')) . '?mj_action=track_cart&email_id=' . $mailId . '&key=' . $securityKey;
        $vars = [
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
            'abandoned_cart_link' => $abandoned_cart_link,
            'products' => $products
        ];

        $recipients = $this->getAbandonedCartRecipients($cart, $vars);
        if (!isset($recipients) || empty($recipients)) {
            return false;
        }
        $data = $this->getFormattedEmailData($recipients, $templateId);
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
        $tracking_number = $order->get_meta('_wcst_order_trackno');

        $vars = [
            'first_name' => $order->get_billing_first_name(),
            'order_number' => $orderId,
            'order_shipping_address' => $order->get_formatted_shipping_address(),
            'tracking_number' => !empty($tracking_number) ? $tracking_number : 'NA', // Arbitrary default value needed in the email template
            'order_total' => wc_price($order->get_total()),
            'order_link' => $order->get_view_order_url(),
            'tracking_url' => $order->get_meta('_wcst_order_track_http_url'),
            'store_email' => get_option('mailjet_from_email'),
            'store_name' => get_bloginfo(),
            'store_address' => get_option('woocommerce_store_address'),
        ];

        $data = $this->getFormattedEmailData($this->getOrderRecipients($order, $vars), $templateId);
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

        if (isset($data['submitAction']) && $data['submitAction'] === 'stop') {
            $activeHooks = [];
            $data['mailjet_wc_active_hooks'] = [];
        }
        else {
            $activeHooks = $this->prepareAutomationHooks($data);
            $this->createTemplates(false, true);
        }

        $this->toggleWooSettings($activeHooks);

        $notifications = isset($data['mailjet_wc_active_hooks']) ? $data['mailjet_wc_active_hooks'] : [];

        update_option('mailjet_wc_active_hooks', $activeHooks);
        update_option('mailjet_order_notifications', $notifications);

        update_option('mailjet_post_update_message', ['success' => true, 'message' => __('Automation settings updated!', 'mailjet-for-wordpress'), 'mj_order_notif_activate' => !empty($activeHooks)]);
        wp_redirect(add_query_arg(array('page' => 'mailjet_order_notifications_page'), admin_url('admin.php')));
    }

    private function prepareAutomationHooks($data)
    {
        if (!isset($data['mailjet_wc_active_hooks'])) {
            return [];
        }

        $actions = [
            'mailjet_order_confirmation' => [
                ['hook' => 'woocommerce_order_status_processing', 'callable' => 'send_order_status_processing_once'],
                ['hook' => 'woocommerce_before_resend_order_emails', 'callable' => 'send_order_status_processing'],
                ['hook' => 'woocommerce_cheque_process_payment_order_status', 'callable' => 'send_order_processing_paid_by_cheque']
            ],
            'mailjet_shipping_confirmation' =>  [['hook' => 'woocommerce_order_status_completed', 'callable' => 'send_order_status_completed']],
            'mailjet_refund_confirmation' =>  [['hook' => 'woocommerce_order_status_refunded', 'callable' => 'send_order_status_refunded']]
        ];
        $returnedHooks = [];
        foreach ($data['mailjet_wc_active_hooks'] as $key => $val) {
            if ($val === '1') {
                $hooks = $actions[$key];
                foreach ($hooks as $hookInfo) {
                    $returnedHooks[] = $hookInfo;
                }
            }
        }

        return $returnedHooks;
    }

    public function abandoned_cart_settings_post()
    {
        $data = $_POST;

        if (!wp_verify_nonce($data['custom_nonce'],'mailjet_order_notifications_settings_page_html')){
            update_option('mailjet_post_update_message', ['success' => false, 'message' => 'Invalid credentials!']);
            wp_redirect(add_query_arg(array('page' => 'mailjet_abandoned_cart_page'), admin_url('admin.php')));
        }

        $wasActivated = false;
        if (isset($data['activate_ac'])) {
            update_option('mailjet_woo_abandoned_cart_activate', $data['activate_ac']);
            $wasActivated = $data['activate_ac'] === '1';
            $this->toggleAbandonedCart();
        }
        if (isset($data['abandonedCartTimeScale']) && isset($data['abandonedCartSendingTime']) && is_numeric($data['abandonedCartSendingTime'])) {
            if ($data['abandonedCartTimeScale'] === 'HOURS') {
                $sendingTimeInSeconds = (int)$data['abandonedCartSendingTime'] * 3600; // 1h == 3600s
            }
            else {
                $sendingTimeInSeconds = (int)$data['abandonedCartSendingTime'] * 60;
            }
            update_option('mailjet_woo_abandoned_cart_sending_time', $sendingTimeInSeconds);
        }

        update_option('mailjet_post_update_message', ['success' => true, 'message' => 'Abandoned cart settings updated!', 'mjACWasActivated' => $wasActivated]);
        wp_redirect(add_query_arg(array('page' => 'mailjet_abandoned_cart_page'), admin_url('admin.php')));
    }

    private function toggleAbandonedCart() {
        $activeHooks = [];

        if (get_option('mailjet_woo_abandoned_cart_activate') === '1') {
            $this->createTemplates(true, false);
            if ( ! wp_next_scheduled( 'abandoned_cart_cron_hook' ) ) {
                wp_schedule_event( time(), 'one_minute', 'abandoned_cart_cron_hook' );
            }
            $this->createAbandonedCartTables();
            $activeHooks = [
                ['hook' => 'woocommerce_add_to_cart', 'callable' => 'cart_change_timestamp'],
                ['hook' => 'woocommerce_cart_item_removed', 'callable' => 'cart_change_timestamp'],
                ['hook' => 'woocommerce_cart_item_restored', 'callable' => 'cart_change_timestamp'],
                ['hook' => 'woocommerce_after_cart_item_quantity_update', 'callable' => 'cart_change_timestamp'],
                ['hook' => 'woocommerce_calculate_totals', 'callable' => 'cart_change_timestamp'],
                ['hook' => 'woocommerce_cart_is_empty', 'callable' => 'cart_change_timestamp'],
                ['hook' => 'woocommerce_order_status_changed', 'callable' => 'update_status_on_order'],
                ['hook' => 'woocommerce_cheque_process_payment_order_status', 'callable' => 'update_status_paid_by_cheque'],
                ['hook' => 'abandoned_cart_cron_hook', 'callable' => 'send_abandoned_cart_emails'],
                ['hook' => 'wp_ajax_nopriv_save_guest_data', 'callable' => 'save_guest_data']
            ];
        }
        else {
            global $wpdb;
            $timestamp = wp_next_scheduled( 'abandoned_cart_cron_hook' );
            wp_unschedule_event( $timestamp, 'abandoned_cart_cron_hook' );
            // empty tables to not send irrelevant emails when reactivating
            $table_name = $wpdb->prefix . 'mailjet_wc_abandoned_cart_emails';
            $sql_delete = "TRUNCATE " . $table_name ;
            $wpdb->get_results( $sql_delete );
            $table_name = $wpdb->prefix . 'mailjet_wc_abandoned_carts';
            $sql_delete = "TRUNCATE " . $table_name ;
            $wpdb->get_results( $sql_delete );
        }
        update_option('mailjet_wc_abandoned_cart_active_hooks', $activeHooks);
    }

    public function update_status_paid_by_cheque($status, $order) {
        if ($order && (int)$order->get_id() > 0) {
            $this->update_status_on_order($order->get_id());
        }
    }

    public function update_status_on_order($order_id) {
        global $wpdb;
        $order = wc_get_order( $order_id );
        if ($order->get_status() == 'processing' || $order->get_status() == 'completed' || ($order->get_status() == 'pending' && $order->get_payment_method() === 'cheque')) {
            if (is_user_logged_in()) {
                $userType = 'REGISTERED';
                $user_id = get_current_user_id();
                $query_update = 'UPDATE `' . $wpdb->prefix . 'mailjet_wc_abandoned_carts`
                                     SET cart_ignored = %d
                                     WHERE user_id  = %d
                                     AND user_type = %s
                                     AND cart_ignored = %d';
                $wpdb->query($wpdb->prepare($query_update, 1, $user_id, $userType, 0));
            }
        }
        else if ($order->get_status() !== 'refunded') {
            $this->cart_change_timestamp();
        }
    }

    private function getAbandonedCartRecipients($cart, $vars) {
        $recipients = [];
        if ($cart->user_type === 'REGISTERED') {
            $email = $cart->user_email;
            $name = $cart->user_name;
        }
        else {
            $email = $cart->guest_email;
            $name = empty($cart->guest_name) ? __('guest') : $cart->guest_name;
        }
        if (isset($email) && is_email($email)) {
            $recipients = [
                'Email' => $email,
                'Name' => $name,
                'Vars' => $vars
            ];
        }

        return $recipients;
    }

    private function getOrderRecipients($order, $vars) {
        $recipients = [
            'Email' => $order->get_billing_email(),
            'Name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'Vars' => $vars
        ];
        return $recipients;
    }

    private function getFormattedEmailData($recipients, $templateId)
    {
        $template = MailjetApi::getTemplateDetails($templateId);
        $data = [];
        if (isset($template['Headers'])) {
            $data['FromEmail'] = $template['Headers']['SenderEmail'];
            $data['FromName'] = $template['Headers']['SenderName'];
        }
        $data['Recipients'][] = $recipients;
        $data['Mj-TemplateID'] = $templateId;
        $data['Mj-TemplateLanguage'] = true;
        $data['Mj-TemplateErrorReporting'] = get_option('woocommerce_email_from_email');
        $data['Mj-TemplateErrorDeliver'] = true;
        $data['body'] = $data;
        return $data;
    }

    private function defaultSenderInfo() {
        $senderName = get_option('woocommerce_email_from_name');
        $senderEmail = get_option('woocommerce_email_from_email');
        return [
            'SenderName' => $senderName,
            'SenderEmail' => $senderEmail,
            'From' => $senderName . ' <' . $senderEmail . '>'
        ];
    }

    private function abandonedCartTemplateContent()
    {
        $templateDetail['MJMLContent'] = require(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceAbandonedCartArray.php');
        $templateDetail['Html-part'] = file_get_contents(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceAbandonedCart.html');
        $templateDetail['Headers'] = $this->defaultSenderInfo();
        $templateDetail['Headers']['Subject'] =  'There\'s something in your cart';

        return $templateDetail;
    }

    private function orderRefundTemplateContent()
    {
        $templateDetail['MJMLContent'] = require(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceRefundArray.php');
        $templateDetail['Html-part'] = file_get_contents(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceRefundConfirmation.html');
        $templateDetail['Headers'] = $this->defaultSenderInfo();
        $templateDetail['Headers']['Subject'] = 'Your refund from {{var:store_name}}';

        return $templateDetail;
    }

    private function shippingConfirmationTemplateContent()
    {
        $templateDetail['MJMLContent'] =  require(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceShippingConfArray.php');
        $templateDetail["Html-part"] = file_get_contents(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceShippingConfirmation.html');
        $templateDetail['Headers'] = $this->defaultSenderInfo();
        $templateDetail['Headers']['Subject'] = 'Your order from {{var:store_name}} has been shipped';

        return $templateDetail;
    }

    private function orderCreatedTemplateContent()
    {
        $templateDetail['MJMLContent'] = require(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceOrderConfArray.php');
        $templateDetail['Html-part'] = file_get_contents(MAILJET_ADMIN_TAMPLATE_DIR . '/IntegrationAutomationTemplates/WooCommerceOrderConfirmation.html');
        $templateDetail['Headers'] = $this->defaultSenderInfo();
        $templateDetail['Headers']['Subject'] = 'We just received your order from {{var:store_name}} - {{var:order_number}}';

        return $templateDetail;
    }

    private function addThankYouSubscription($order)
    {
        $scriptPath = plugins_url('/src/front/js/mailjet-front.js', MAILJET_PLUGIN_DIR . 'src');
        wp_register_script('mailjet-woo-ajax-subscribe', $scriptPath, array('jquery'), false, true);
        wp_localize_script('mailjet-woo-ajax-subscribe', 'mailjet', ['url' => admin_url( 'admin-ajax.php' )]);
        wp_enqueue_script('mailjet-woo-ajax-subscribe');
        $text = stripslashes(get_option('mailjet_woo_banner_text'));
        $label = stripslashes(get_option('mailjet_woo_banner_label'));
        set_query_var('orderId', $order->get_id());
        set_query_var('text', !empty($text) ? $text : __('Subscribe to our newsletter', 'mailjet-for-wordpress'));
        set_query_var('btnLabel', !empty($label) ? $label : __('Subscribe now', 'mailjet-for-wordpress'));
        return load_template(MAILJET_FRONT_TEMPLATE_DIR . '/Subscription/subscriptionForm.php');
    }

    public function enqueueFrontScripts()
    {
        $cssPath = plugins_url('/src/front/css/mailjet-front.css', MAILJET_PLUGIN_DIR . 'src');
        wp_register_style('mailjet-front', $cssPath);
        wp_enqueue_style('mailjet-front');
    }

    public function subscribeViaAjax()
    {
        $post = $_POST;

        if (isset($post['orderId'])) {
            $orderId = sanitize_text_field($post['orderId']);
            $order = wc_get_order($orderId);
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
           return ['success' => false, 'message' => 'You can\'t subscribe at this moment.'];
       }

       if (MailjetApi::checkContactSubscribedToList($email, $listId)){
           return ['success' => true, 'message' => 'You are already subscribed.'];
       }

       if ($this->mailjet_subscribe_confirmation_from_woo_form(1, $email, $fName, $lName)){
           $message = __('We have sent the newsletter subscription confirmation link to you: ') . $email . '. '
               . __('To confirm your subscription you have to click on the provided link.');
           return ['success' => true, 'message' => $message];
       }

       return ['success' => false, 'message' => 'Something went wrong.'];
    }

    public function save_guest_data() {
        $session = WC()->session;
        global $wpdb;
        if (isset($_POST['billing_email']) && is_email($_POST['billing_email'])) {
            $session->set('billing_email', sanitize_text_field( $_POST['billing_email']));
        }
        if (!empty($_POST['billing_first_name'])) {
            $session->set('billing_first_name', sanitize_text_field( $_POST['billing_first_name']));
        }
        if (!empty($_POST['billing_last_name'])) {
            $session->set('billing_last_name', sanitize_text_field( $_POST['billing_last_name']));
        }
        $email_address = $session->get( 'billing_email');
        if (!is_user_logged_in() && is_email($email_address)) {
            $name = trim($session->get( 'billing_first_name') . ' ' . $session->get( 'billing_last_name'));
            $query_guest = 'SELECT * FROM `' . $wpdb->prefix . 'mailjet_wc_guests`
                            WHERE billing_email = %s';
            $results_guest = $wpdb->get_results($wpdb->prepare($query_guest, $email_address));
            if ($results_guest) {
                $user_id = (int)$results_guest[0]->id;
                $session->set('user_id', $user_id);
                if ($name !== '' && $name !== $results_guest[0]->guest_name) {
                    $query_update = 'UPDATE `' . $wpdb->prefix . 'mailjet_wc_guests`
                                     SET guest_name = %s
                                     WHERE id  = %d';
                    $wpdb->query($wpdb->prepare($query_update, $name, $user_id));
                }
            }
            else {
                $insert_query = 'INSERT INTO `' . $wpdb->prefix . 'mailjet_wc_guests`
                         (billing_email, guest_name)
                         VALUES (%s, %s)';
                $wpdb->query($wpdb->prepare($insert_query, $email_address, $name));
                $user_id = $wpdb->insert_id;
                $session->set('user_id', $user_id);
            }
            $this->cart_change_timestamp();
        }
    }

    private function retrieve_cart($emailId, $key) {
        global $wpdb;
        $url = get_permalink(wc_get_page_id('shop'));
        $query = 'SELECT * FROM `' . $wpdb->prefix . 'mailjet_wc_abandoned_cart_emails` AS email
                    RIGHT JOIN `wp_mailjet_wc_abandoned_carts` AS cart ON email.abandoned_cart_id = cart.id
                    WHERE email.id = %d';
        $result = $wpdb->get_results($wpdb->prepare($query, $emailId));
        if ($result && $result[0]->security_key === $key) { // check if the key corresponds to the one in DB
            $userId = $result[0]->user_id;
            if ($result[0]->user_type === 'REGISTERED') {
                if (is_user_logged_in()) {
                    $url = get_permalink(wc_get_page_id('cart'));
                }
                else {
                    $url = get_permalink(wc_get_page_id('myaccount')) . '?mj_action=to_cart';
                }
            }
            else {
                $query = 'SELECT * FROM `' . $wpdb->prefix . 'mailjet_wc_guests`
                    WHERE id = %d';
                $result = $wpdb->get_results($wpdb->prepare($query, $userId));
                if ($result) {
                    $session = WC()->session;
                    $session->set('billing_email', $result[0]->billing_email);
                    $url = get_permalink(wc_get_page_id('cart'));
                }
                if (WC()->cart->get_cart_contents_count() == 0) {
                    $cartInfo = json_decode($result[0]->abandoned_cart_info, true);
                    foreach ($cartInfo as $productKey => $product) {
                        WC()->cart->add_to_cart($product['product_id'], $product['quantity']);
                    }
                }
            }
        }
        header( 'Location: ' . $url);
    }

    public function init_edata() {
        // check properties creation
        $propertyTypes = [
            SubscriptionOptionsSettings::PROP_USER_FIRSTNAME => 'str',
            SubscriptionOptionsSettings::PROP_USER_LASTNAME => 'str',
            SubscriptionOptionsSettings::WP_PROP_USER_ROLE => 'str',
            self::WOO_PROP_TOTAL_ORDERS => 'int',
            self::WOO_PROP_TOTAL_SPENT => 'float',
            self::WOO_PROP_LAST_ORDER_DATE => 'datetime',
            self::WOO_PROP_ACCOUNT_CREATION_DATE => 'datetime'
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

        // check predefined segments creation
        $segments = MailjetApi::getMailjetSegments();
        $newSegments = array(
            'Newcomers' => array(
                'expr' => '(IsInPreviousDays(' . self::WOO_PROP_ACCOUNT_CREATION_DATE . ',30))',
                'description' => __('Customers who have created an account in the past 30 days')
            ),
            'Potential customers' => array(
                'expr' => '(' . self::WOO_PROP_TOTAL_ORDERS . '<1)',
                'description' => __('Contacts that don\'t have any orders')
            ),
            'First time customers' => array(
                'expr' => '(' . self::WOO_PROP_TOTAL_ORDERS . '=1) and (IsInPreviousDays(' . self::WOO_PROP_LAST_ORDER_DATE . ',30))',
                'description' => __('Customers who have made their first purchase in the past 30 days')
            ),
            'Recent customers' => array(
                'expr' => '(IsInPreviousDays(' . self::WOO_PROP_LAST_ORDER_DATE . ',30))',
                'description' => __('Customers who have purchased in the past 30 days')
            ),
            'Repeat customers' => array(
                'expr' => '(' . self::WOO_PROP_TOTAL_ORDERS . '>1)',
                'description' => __('Customers who have purchased more than once')
            ),
            'Lapsed customers' => array(
                'expr' => '(not IsInPreviousDays(' . self::WOO_PROP_LAST_ORDER_DATE . ',180))',
                'description' => __('Customers who haven\'t purchased in the past 6 months')
            )
        );
        foreach ($segments as $seg) {
            if (array_key_exists($seg['Name'], $newSegments)) {
                unset($newSegments[$seg['Name']]);
            }
        }
        foreach ($newSegments as $segKey => $segValues){
            MailjetApi::createMailjetSegment($segKey, $segValues['expr'], $segValues['description']);
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
        $unsubGuestProperties = $this->get_guest_edata();
        foreach ($subscribers as $sub) {
            $email = $sub['Contact']['Email']['Email'];
            $properties = array();
            if (array_key_exists($email, $unsubUsers)) {
                $user = $unsubUsers[$email];
                $properties = $this->get_customer_edata($user->ID);
                if (array_key_exists($email, $unsubGuestProperties)) {
                    $properties = $this->merge_customer_and_guest_edata($properties, $unsubGuestProperties[$email]);
                    unset($unsubGuestProperties[$email]);
                }
                unset($unsubUsers[$email]);
            }
            else if (array_key_exists($email, $unsubGuestProperties)) {
                $properties = $unsubGuestProperties[$email];
                unset($unsubGuestProperties[$email]);
            }

            if (is_array($properties) && !empty($properties)) {
                $subscribedContacts[] = array(
                    'Email' => $email,
                    'Properties' => $properties
                );
            }
        }

        foreach ($unsubUsers as $user) {
            $userEmail = $user->user_email;
            if (!empty($userEmail)) {
                $properties = $this->get_customer_edata($user->ID);
                if (array_key_exists($userEmail, $unsubGuestProperties)) {
                    $properties = $this->merge_customer_and_guest_edata($properties, $unsubGuestProperties[$userEmail]);
                    unset($unsubGuestProperties[$userEmail]);
                }
                if (is_array($properties) && !empty($properties)) {
                    $unsubContacts[] = array(
                        'Email' => $user->user_email,
                        'Properties' => $properties
                    );
                }
            }
        }
        foreach ($unsubGuestProperties as $guestEmail => $guestProperties) {
            if (is_array($guestProperties) && !empty($guestProperties)) {
                $unsubContacts[] = array(
                    'Email' => $guestEmail,
                    'Properties' => $guestProperties
                );
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

    public function paid_by_cheque_order_edata_sync($status, $order) {
        if ($order && (int)$order->get_id() > 0) {
            $this->order_edata_sync($order->get_id());
        }
    }

    public function order_edata_sync($orderId) {
        $order = wc_get_order($orderId);
        $mailjet_sync_list = get_option('mailjet_sync_list');
        if ($order === false || empty($mailjet_sync_list) || $mailjet_sync_list < 0) {
            return;
        }
        $user = $order->get_user();
        if ($user === false) { // guest user
            $email = $order->get_billing_email();
            $properties = $this->get_guest_edata($email);
            // merge properties if guest also made orders as registered user
            $user = get_user_by('email', $email);
            if ($user) {
                $userProperties = $this->get_customer_edata($user->ID);
                if (!empty($userProperties)) {
                    $properties = $this->merge_customer_and_guest_edata($userProperties, $properties);
                }
            }
        }
        else {
            $email = $user->user_email;
            $properties = $this->get_customer_edata($user->ID);
            // merge properties if registered user also made orders as guest
            $guestProperties = $this->get_guest_edata($user->user_email);
            if (!empty($guestProperties)) {
                $properties = $this->merge_customer_and_guest_edata($properties, $guestProperties);
            }
        }
        try {
            $contactId = MailjetApi::isContactInList($email, $mailjet_sync_list);
        }
        catch (\Exception $e) {
            MailjetLogger::log('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ' . $e->getMessage() . ' ]');
            return;
        }
        $this->init_edata();
        if ($contactId > 0) {
            $data = array();
            foreach ($properties as $propertyKey => $propertyValue) {
                array_push($data, array('Name' => $propertyKey, 'Value' => $propertyValue));
            }
            if (empty($properties[self::WOO_PROP_LAST_ORDER_DATE])) {
                array_push($data, array('Name' => self::WOO_PROP_LAST_ORDER_DATE, 'Value' => ''));
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
            $contact = array(
                'Email' => $email,
                'Properties' => $properties
            );
            MailjetApi::syncMailjetContact($mailjet_sync_list, $contact, 'unsub');
        }
    }

    private function merge_customer_and_guest_edata($userData, $guestData) {
        if (!is_array($userData) || empty($userData) || !is_array($guestData)) {
            return [];
        }
        $mergedData = $userData;
        $mergedData[self::WOO_PROP_TOTAL_ORDERS] = ($userData[self::WOO_PROP_TOTAL_ORDERS] ?: 0) + ($guestData[self::WOO_PROP_TOTAL_ORDERS] ?: 0);
        $mergedData[self::WOO_PROP_TOTAL_SPENT] = ($userData[self::WOO_PROP_TOTAL_SPENT] ?: 0) + ($guestData[self::WOO_PROP_TOTAL_SPENT] ?: 0);
        if (isset($userData[self::WOO_PROP_LAST_ORDER_DATE], $guestData[self::WOO_PROP_LAST_ORDER_DATE]) &&
            $guestData[self::WOO_PROP_LAST_ORDER_DATE] > $userData[self::WOO_PROP_LAST_ORDER_DATE]) {
                $mergedData[self::WOO_PROP_LAST_ORDER_DATE] = $guestData[self::WOO_PROP_LAST_ORDER_DATE];
        }
        else if (!isset($userData[self::WOO_PROP_LAST_ORDER_DATE]) && isset($guestData[self::WOO_PROP_LAST_ORDER_DATE])) {
            $mergedData[self::WOO_PROP_LAST_ORDER_DATE] = $guestData[self::WOO_PROP_LAST_ORDER_DATE];
        }

        return $mergedData;
    }

    private function get_customer_edata($userId) {
        $userData = get_userdata($userId);

        $customerProperties = [];
        $userRoles = $userData->roles;
        if ($userRoles[0] === 'customer') {
            $args = array(
                'customer_id' => $userId,
                'orderby' => 'date',
                'order' => 'ASC',
                'type' => 'shop_order',
                'limit' => -1,
            );
            $orders = wc_get_orders($args);
            $customer = new \WC_Customer($userId);
            $customerProperties[SubscriptionOptionsSettings::PROP_USER_FIRSTNAME] = $userData->first_name;
            $customerProperties[SubscriptionOptionsSettings::PROP_USER_LASTNAME] = $userData->last_name;
            $customerProperties[SubscriptionOptionsSettings::WP_PROP_USER_ROLE] = 'customer';
            $customerProperties[self::WOO_PROP_ACCOUNT_CREATION_DATE] = get_gmt_from_date($customer->get_date_created(), 'Y-m-d\TH:i:s\Z');
            $customerProperties[self::WOO_PROP_TOTAL_ORDERS] = 0;
            $customerProperties[self::WOO_PROP_TOTAL_SPENT] = 0;
            foreach ($orders as $order) {
                if ($order->get_status() === 'completed') {
                    $date = get_gmt_from_date($order->get_date_completed(), 'Y-m-d\TH:i:s\Z');
                    $customerProperties[self::WOO_PROP_TOTAL_ORDERS]++;
                    $customerProperties[self::WOO_PROP_TOTAL_SPENT] += $order->get_total();
                    if (!isset($customerProperties[self::WOO_PROP_LAST_ORDER_DATE]) || $date > $customerProperties[self::WOO_PROP_LAST_ORDER_DATE]) {
                        $customerProperties[self::WOO_PROP_LAST_ORDER_DATE] = $date;
                    }
                }
            }
        }
        return $customerProperties;
    }

    /**
     * Get e-commerce data for a particular guest if email address is given or for all guests if not
     * @param string $guestEmail
     * @return array
     */
    private function get_guest_edata($guestEmail = '') {
        $args = array(
            'customer_id' => 0,
            'orderby' => 'date',
            'order' => 'ASC',
            'type' => 'shop_order',
            'limit' => -1,
        );
        if (!empty($guestEmail)) {
            $args['customer'] = $guestEmail;
        }
        $orders = wc_get_orders($args);
        $guestProperties = array();
        foreach ($orders as $order) {
            $email = $order->get_billing_email();
            if (!array_key_exists($email, $guestProperties)) {
                $guestProperties[$email] = array();
                $guestProperties[$email][self::WOO_PROP_TOTAL_ORDERS] = 0;
                $guestProperties[$email][self::WOO_PROP_TOTAL_SPENT] = 0;
            }
            if ($order->get_status() === 'completed') {
                $date = get_gmt_from_date($order->get_date_completed(), 'Y-m-d\TH:i:s\Z');
                $guestProperties[$email][self::WOO_PROP_TOTAL_ORDERS]++;
                $guestProperties[$email][self::WOO_PROP_TOTAL_SPENT] += $order->get_total();
                if (!isset($guestProperties[$email][self::WOO_PROP_LAST_ORDER_DATE]) || $date > $guestProperties[$email][self::WOO_PROP_LAST_ORDER_DATE]) {
                    $guestProperties[$email][self::WOO_PROP_LAST_ORDER_DATE] = $date;
                }
            }
            $guestProperties[$email][SubscriptionOptionsSettings::PROP_USER_FIRSTNAME] = $order->get_billing_first_name();
            $guestProperties[$email][SubscriptionOptionsSettings::PROP_USER_LASTNAME] = $order->get_billing_last_name();
        }
        if (!empty($guestEmail)) {
            return $guestProperties[$guestEmail];
        }
        else {
            return $guestProperties;
        }
    }
}
