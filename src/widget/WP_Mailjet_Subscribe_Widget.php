<?php

namespace MailjetPlugin\Widget;

use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\Mailjeti18n;
use MailjetPlugin\Includes\MailjetLogger;
use MailjetPlugin\Includes\MailjetSettings;
use MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings;

class WP_Mailjet_Subscribe_Widget extends \WP_Widget
{

    const WIDGET_OPTIONS_NAME = 'mailjet_widget_options';

    private $subscriptionOptionsSettings = null;
    protected $widget_slug = 'wp_mailjet_subscribe_widget';
    private $propertyData = array();
    private $mailjetContactProperties = null;

    /* -------------------------------------------------- */
    /* Constructor
      /*-------------------------------------------------- */

    /**
     * Specifies the classname and description, instantiates the widget,
     * loads localization files, and includes necessary stylesheets and JavaScript.
     */
    public function __construct()
    {
        // load plugin text domain
        add_action('init', array($this, 'widget_textdomain'));

        // Build widget
        $widget_options = array(
            'classname' => 'WP_Mailjet_Subscribe_Widget',
            'description' => __('Allows your visitors to subscribe to one of your lists', 'mailjet-for-wordpress')
        );
        parent::__construct(
            $this->get_widget_slug(), __('Mailjet Subscription Widget', 'mailjet-for-wordpress'), $widget_options
        );

        // Register site styles and scripts
        add_action('admin_enqueue_scripts', array($this, 'register_widget_scripts'));

        add_action('wp_enqueue_scripts', array($this, 'register_widget_front_styles'));
        add_action('wp_enqueue_scripts', array($this, 'register_widget_front_scripts'));

        // Refreshing the widget's cached output with each new post
        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));

        add_action('wp_ajax_mailjet_add_contact_property', array($this, 'wp_ajax_mailjet_add_contact_property'));
        add_action('wp_ajax_send_mailjet_subscription_form', array($this, 'sendSubscriptionEmail'));
        add_action('wp_ajax_nopriv_send_mailjet_subscription_form', array($this, 'sendSubscriptionEmail'));

        // Subscribe user
        $this->activateConfirmSubscriptionUrl();
    }

// end constructor

    /**
     * Return the widget slug.
     *
     * @since    5.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug()
    {
        return $this->widget_slug;
    }

    /**
     * Check if subscription form is submitted
     * Send subscription email if need
     * Used in an ajax call, wp_die() prevents server error for ajax calls
     */
    public function sendSubscriptionEmail()
    {
        $subscriptionOptionsSettings = $this->getSubscriptionOptionsSettings();
        $instance = get_option(self::WIDGET_OPTIONS_NAME);
        $locale = Mailjeti18n::getLocale();
        // Check if subscription form is submitted
        if (!isset($_POST['subscription_email'], $_POST['widget_id'])) {
            wp_die();
        }

        $widgetId = sanitize_text_field($_POST['widget_id']);
        $subscription_locale = $locale;
        if (isset($_POST['subscription_locale'])) {
            $subscription_locale = sanitize_text_field($_POST['subscription_locale']);
        }

        // Submitted but empty
        if (empty($_POST['subscription_email'])) {
            echo !empty($instance[$locale]['empty_email_message_input']) ? $instance[$locale]['empty_email_message_input'] : __('Please provide an email address', 'mailjet-for-wordpress');
            wp_die();
        }

        // Send subscription email
        $subscription_email = sanitize_email($_POST['subscription_email']);
        if (!is_email($subscription_email)) {
            echo __('Invalid email', 'mailjet-for-wordpress');
            wp_die();
        }

        $properties = isset($_POST['properties']) ? $_POST['properties'] : array();
        $mailjetContactProperties = $this->getMailjetContactProperties();
        $isValueTypeIncorrect = false;
        if(!empty($properties) && is_array($mailjetContactProperties) && !empty($mailjetContactProperties)) {
            foreach($properties as $propertyId => $propertyValue) {
                $propertyValue = sanitize_text_field($propertyValue);
                if($propertyValue === '') {
                    continue;
                }
                foreach($mailjetContactProperties as $mailjetContactProperty) {
                    if($propertyId === $mailjetContactProperty['ID']) {
                        $dataType = $mailjetContactProperty['Datatype'];
                        switch($dataType) {
                            case 'str':
                                // by default
                                break;
                            case 'int':
                            case 'float':
                                if(!is_numeric($propertyValue)) {
                                    $isValueTypeIncorrect = true;
                                }
                                break;
                            case 'datetime':
                                $datetime = \DateTime::createFromFormat("Y-m-d", $propertyValue);
                                $errors = \DateTime::getLastErrors();
                                if (!$datetime instanceof \DateTime) {
                                    $isValueTypeIncorrect = true;
                                }
                                if (!empty($errors['warning_count'])) {
                                    $isValueTypeIncorrect = true;
                                }
                                break;
                            case 'bool':
                                $booleans = array('on', '', 1, true, false, 0);
                                if(!in_array($propertyValue, $booleans, false)) {
                                    $isValueTypeIncorrect = true;
                                }
                                break;
                        }
                        continue;
                    }
                }
            }
            if ($isValueTypeIncorrect) {
                $incorrectTypeValueMessage = !empty($instance[$locale]['invalid_data_format_message_input']) ? $instance[$locale]['invalid_data_format_message_input'] : __('The value you entered is not in the correct format.', 'mailjet-for-wordpress');
                echo $incorrectTypeValueMessage;
                wp_die();
            }
        }

        $sendingResult = $subscriptionOptionsSettings->mailjet_subscribe_confirmation_from_widget($subscription_email, $instance, $subscription_locale, $widgetId);
        if ($sendingResult) {
            echo !empty($instance[$locale]['confirmation_email_message_input']) ? $instance[$locale]['confirmation_email_message_input'] : __('Subscription confirmation email sent. Please check your inbox and confirm your subscription.', 'mailjet-for-wordpress');
        }
        else {
            echo !empty($instance[$locale]['technical_error_message_input']) ? $instance[$locale]['technical_error_message_input'] : __('A technical issue has prevented your subscription. Please try again later.', 'mailjet-for-wordpress');
        }
        wp_die();
    }

    /**
     * Validate the confirmation link
     * Subscribe to mailjet list
     * @param type $subscriptionOptionsSettings
     */
    private function activateConfirmSubscriptionUrl()
    {
        $locale = Mailjeti18n::getLocale();

        // Check if subscription email is confirmed
        if (empty($_GET['mj_sub_token'])) {
            return true;
        }

        if (!empty($_GET['subscription_locale'])) {
            $locale = sanitize_text_field($_GET['subscription_locale']);
        }

        $subscription_email = isset($_GET['subscription_email']) ? sanitize_email($_GET['subscription_email']) : '';
        $list_id = isset($_GET['list_id']) ? sanitize_text_field($_GET['list_id']) : '';
        $widget_id = isset($_GET['widget_id']) ? sanitize_text_field($_GET['widget_id']) : false;
        $thanks_id = isset($_GET['thanks_id']) ? sanitize_text_field($_GET['thanks_id']) : '';

        if (!$subscription_email) {
            _e('Subscription email is missing', 'wordpress-for-mailjet');
            die;
        }

        $properties = isset($_GET['properties']) ? $_GET['properties'] : array();

        $params = array(
            'subscription_email' => $subscription_email,
            'subscription_locale' => $locale,
            'list_id' => $list_id,
            'thanks_id' => $thanks_id,
            'properties' => $properties,
        );

        if ($widget_id) {
            $params['widget_id'] = $widget_id;
        }

        $params = http_build_query($params);

        if ($_GET['mj_sub_token'] !== sha1($params . MailjetSettings::getCryptoHash())) {
            // Invalid token
            _e('Invalid token', 'mailjet-for-wordpress');
            die;
        }

        // The token is valid we can subscribe the user
        $contactListId = $list_id;
        if (empty($list_id)) {
            _e('Contact list not provided', 'mailjet-for-wordpress');
            die;
        }

        $dataProperties = array();
        $mailjetContactProperties = $this->getMailjetContactProperties();
        if (!empty($mailjetContactProperties)) {
            foreach ($mailjetContactProperties as $property) {
                $propertyName = $property['Name'];
                if (!empty($properties[$property['ID']])) {
                    $propertyValue = $properties[$property['ID']];
                    $dataType = $property['Datatype'];
                    switch ($dataType) {
                        case 'datetime':
                            $datetime = \DateTime::createFromFormat('Y-m-d', $propertyValue);
                            if ($datetime instanceof \DateTime) {
                                $dataProperties[$propertyName] = $datetime->format(\DateTime::RFC3339);
                            }
                            break;
                        case 'int':
                            $dataProperties[$propertyName] = (int) $propertyValue;
                            break;
                        case 'float':
                            $dataProperties[$propertyName] = (float) $propertyValue;
                            break;
                        case 'bool':
                            $positiveBooleans = array('true', '1', 'on', 1, true);
                            if (in_array($propertyValue, $positiveBooleans, false)) {
                                $dataProperties[$propertyName] = true;
                            } else {
                                $dataProperties[$propertyName] = false;
                            }
                            break;
                        case 'str':
                        default:
                            $dataProperties[$propertyName] = $propertyValue;
                            break;
                    }
                }
            }
        }

        $contact = array(
            'Email' => $subscription_email,
//                'Name' => $contactProperties['first_name'] . ' ' . $contactProperties['last_name'],
            'Properties' => $dataProperties
        );

        $isActiveList = MailjetApi::isContactListActive($contactListId);
        if (!$isActiveList) {
            _e('Contact list is not active', 'mailjet-for-wordpress');
            die;
        }

        $result = MailjetApi::syncMailjetContact($contactListId, $contact);
        if (!$result) {
            _e('Contact subscription failed', 'mailjet-for-wordpress');
            die;
        }

        // If no selected page in widget settings, display default template
        if (empty($thanks_id)) {
            $newsletterRegistration = Mailjeti18n::getTranslationsFromFile($locale, 'Newsletter Registration');
            $congratsSubscribed = Mailjeti18n::getTranslationsFromFile($locale, 'Congratulations, you have successfully subscribed!');

            $thankYouPageTemplate = apply_filters('mailjet_thank_you_page_template', plugin_dir_path(__FILE__) . 'templates' . DIRECTORY_SEPARATOR . 'thankyou.php');
            include($thankYouPageTemplate);
            die;
        }
    }

    /* -------------------------------------------------- */
    /* Widget API Functions
      /*-------------------------------------------------- */

    /**
     * Outputs the content of the widget.
     *
     * @param array args  The array of form elements
     * @param array instance The current instance of the widget
     */
    public function widget($args, $instance)
    {
        $validApiCredentials = MailjetApi::isValidAPICredentials();
        if ($validApiCredentials === false) {
            return false;
        }

        if (get_option(self::WIDGET_OPTIONS_NAME) === false) {
            add_option(self::WIDGET_OPTIONS_NAME, $instance);
        }
        $mailjetContactProperties = $this->getMailjetContactProperties();
        if (!empty($mailjetContactProperties) && is_array($mailjetContactProperties)) {
            foreach ($mailjetContactProperties as $mjContactProperty) {
                $this->propertyData[$mjContactProperty['ID']] = array(
                    'Name' => $mjContactProperty['Name'],
                    'Datatype' => $mjContactProperty['Datatype']
                );
            }
        }

        // Check if there is a cached output
        $cache = wp_cache_get($this->get_widget_slug(), 'widget');

        if (!is_array($cache)) {
            $cache = array();
        }

        if (!isset($args['widget_id'])) {
            $args['widget_id'] = $this->id;
        }

        if (isset($cache[$args['widget_id']])) {
            return print $cache[$args['widget_id']];
        }

        // Show front widget form
        // go on with your widget logic, put everything into a string and …
        extract($args, EXTR_SKIP);

        ob_start();
        $front_widget_file = apply_filters('mailjet_widget_form_filename', plugin_dir_path(__FILE__) . 'views/widget.php');
        include($front_widget_file);
        $widget_string = ob_get_clean();

        $cache[$args['widget_id']] = $widget_string;

        wp_cache_set($this->get_widget_slug(), $cache, 'widget');
        print $widget_string;
    }

    // Used in widget.php
    private function getInputProperties($propType, $labelValue = '', $isHidden = false, $isMandatory = false) {
        $inputProperties = array();
        switch ($propType) {
            case 'float':
                $inputProperties['step'] = '0.001';
            case 'int':
                $inputProperties['min'] = '0';
                $inputProperties['type'] = 'number';
                break;
            case 'datetime':
                $inputProperties['type'] = 'date';
                break;
            case 'bool':
                $inputProperties['type'] = 'bool';
                break;
            default:
                $inputProperties['type'] = 'text';
                break;
        }

        if ($isHidden) {
            $inputProperties['type'] = 'hidden';
            $inputProperties['style'] = 'display: none';
            $inputProperties['value'] = $labelValue;
        }
        else {
            if ($isMandatory) {
                $inputProperties['required'] = '';
                $inputProperties['placeholder'] = '* ' . $labelValue;
            }
            else {
                $inputProperties['placeholder'] = $labelValue;
            }
        }


        return $inputProperties;
    }

    public function flush_widget_cache()
    {
        wp_cache_delete($this->get_widget_slug(), 'widget');
    }

    /**
     * Processes the widget's options to be saved.
     *
     * @param array new_instance The new instance of values to be generated via the update.
     * @param array old_instance The previous instance of values before the update.
     */
    public function update($new_instance, $old_instance)
    {

        // Here is where you update your widget's old values with the new, incoming values
        $instance = $old_instance;
        $languages = Mailjeti18n::getSupportedLocales();
        $admin_locale = Mailjeti18n::getLocale();
        $wp_version = get_bloginfo( 'version' );

        if (version_compare($wp_version, '4.4', '<')) {

            foreach ($languages as $language => $locale) {
                // Do not save if language is active but there is no contact list chosen for it
                if (isset($new_instance[$locale.'[language_checkbox']) && $new_instance[$locale.'[list'] == "0") {
                    continue;
                }

                // Initial
                $instance[$locale]['language_checkbox'] = isset($new_instance[$locale.'[language_checkbox']) ? 1 : false;
                $instance[$locale]['title'] = isset($new_instance[$locale.'[title']) ? wp_strip_all_tags($new_instance[$locale.'[title']) : '';
                $instance[$locale]['list'] = isset($new_instance[$locale.'[list']) ? wp_strip_all_tags($new_instance[$locale.'[list']) : '';

                $customLanguages = Mailjeti18n::getSupportedLocales();
                $getInstanceKeys = $this->getUpdateArrayKeysPairs();

                foreach ($getInstanceKeys as $key => $val){
                    $instance[$locale][$key] = isset($new_instance[$locale][$val]) ? wp_strip_all_tags($new_instance[$locale][$val]) : '';
                }

                for ($i = 0; $i <= 4; $i++) {
                    $instance[$locale]['contactProperties' . $i] = isset($new_instance[$admin_locale]['[contactProperties' . $i]) ? wp_strip_all_tags($new_instance[$admin_locale]['[contactProperties' . $i]) : '';
                    $instance[$locale]['propertyDataType' . $i] = isset($new_instance[$admin_locale]['[propertyDataType' . $i]) ? wp_strip_all_tags($new_instance[$admin_locale]['[propertyDataType' . $i]) : '';

                    foreach ($customLanguages as $name => $code){
                        $instance[$locale][$name . 'Label' . $i] = isset($new_instance[$admin_locale]['['. $name . 'Label' . $i]) ? wp_strip_all_tags($new_instance[$admin_locale]['['. $name . 'Label' . $i]) : '';
                    }
                }

                $instance[$language]['thank_you'] = isset($new_instance[$language.'[thank_you']) ? wp_strip_all_tags($new_instance[$language.'[thank_you']) : 0;

                // Translations update
                Mailjeti18n::updateTranslationsInFile($locale, $instance[$locale]);
            }
        } else {

            foreach ($languages as $language => $locale) {
                // Do not save if language is active but there is no contact list chosen for it
                if (isset($new_instance[$locale]['language_checkbox']) && $new_instance[$locale]['list'] == "0") {
                    continue;
                }

                // Initial
                $instance[$locale]['language_checkbox'] = isset($new_instance[$locale]['language_checkbox']) ? 1 : false;
                $instance[$locale]['title'] = isset($new_instance[$locale]['title']) ? wp_strip_all_tags($new_instance[$locale]['title']) : '';
                $instance[$locale]['list'] = isset($new_instance[$locale]['list']) ? wp_strip_all_tags($new_instance[$locale]['list']) : '';

                $customLanguages = Mailjeti18n::getSupportedLocales();
                $getInstanceKeys = $this->getUpdateArrayKeysPairs();

                foreach ($getInstanceKeys as $key => $val){
                    $instance[$locale][$key] = isset($new_instance[$locale][$key]) ? wp_strip_all_tags($new_instance[$locale][$key]) : '';
                }

                for ($i = 0; $i <= 4; $i++) {
                    $instance[$locale]['contactProperties' . $i] = isset($new_instance[$admin_locale]['contactProperties' . $i]) ? wp_strip_all_tags($new_instance[$admin_locale]['contactProperties' . $i]) : '';
                    $instance[$locale]['propertyDataType' . $i] = isset($new_instance[$admin_locale]['propertyDataType' . $i]) ? wp_strip_all_tags($new_instance[$admin_locale]['propertyDataType' . $i]) : '';

                    foreach ($customLanguages as $name => $code){
                        $instance[$locale][$name . 'Label' . $i] = isset($new_instance[$admin_locale][$name . 'Label' . $i]) ? wp_strip_all_tags($new_instance[$admin_locale][$name . 'Label' . $i]) : '';
                    }
                }

                $instance[$language]['thank_you'] = isset($new_instance[$language]['thank_you']) ? wp_strip_all_tags($new_instance[$language]['thank_you']) : 0;

                // Translations update
                Mailjeti18n::updateTranslationsInFile($locale, $instance[$locale]);
            }
        }

        update_option(self::WIDGET_OPTIONS_NAME, $instance);
        return $instance;
    }

    /**
     * Register the dynamic translations to the po files
     */
    private function registerCustomLanguageTranslations()
    {
        __('English', 'mailjet-for-wordpress');
        __('French', 'mailjet-for-wordpress');
        __('German', 'mailjet-for-wordpress');
        __('Spanish', 'mailjet-for-wordpress');
        __('Italian', 'mailjet-for-wordpress');
        __('your@email.com', 'mailjet-for-wordpress');
        __('Subscribe', 'mailjet-for-wordpress');
        __('Subscription confirmation email sent. Please check your inbox and confirm your subscription.', 'mailjet-for-wordpress');
        __('Your subscription was successfully confirmed.', 'mailjet-for-wordpress');
        __('Please provide an email address', 'mailjet-for-wordpress');
        __('Please confirm your subscription', 'mailjet-for-wordpress');
        __('This email address has already been subscribed.', 'mailjet-for-wordpress');
        __('The value you entered is not in the correct format.', 'mailjet-for-wordpress');
        __('A technical issue has prevented your subscription. Please try again later.', 'mailjet-for-wordpress');
        __('Yes, subscribe me to this list', 'mailjet-for-wordpress');
        __("If you received this email by mistake or don't wish to subscribe anymore, simply ignore this message", 'mailjet-for-wordpress');
    }

    /**
     *  Transition widget settings from v4 to v5
     */
    private function checkTransition($instance)
    {
        if (isset($instance['enableTaben']) && $instance['enableTaben'] == 'on') {
            $contactProperties0name = isset($instance['metaPropertyName1en']) ? $instance['metaPropertyName1en'] : false; // -> contactProperties0
            $contactProperties1name = isset($instance['metaPropertyName2en']) ? $instance['metaPropertyName2en'] : false; // -> contactProperties1
            $contactProperties2name = isset($instance['metaPropertyName3en']) ? $instance['metaPropertyName3en'] : false; // -> contactProperties2
            $property0Id = MailjetApi::getPropertyIdByName($contactProperties0name);
            $property1Id = MailjetApi::getPropertyIdByName($contactProperties1name);
            $property2Id = MailjetApi::getPropertyIdByName($contactProperties2name);

            $titleEn = isset($instance['titleen']) ? $instance['titleen'] : '';
            $listEn = isset($instance['list_iden']) ? $instance['list_iden'] : '';
            $property0En = isset($instance['metaProperty1en']) ? $instance['metaProperty1en'] : '';
            $property1En = isset($instance['metaProperty2en']) ? $instance['metaProperty2en'] : '';
            $property2En = isset($instance['metaProperty3en']) ? $instance['metaProperty3en'] : '';
            $buttonТextЕn = isset($instance['button_texten']) ? $instance['button_texten'] : '';

            $enableFr = isset($instance['enableTabfr']) && $instance['enableTabfr'] == 'on';
            $titleFr = isset($instance['titlefr']) ? $instance['titlefr'] : '';
            $listFr = isset($instance['list_idfr']) ? $instance['list_idfr'] : '';
            $property0Fr = isset($instance['metaProperty1fr']) ? $instance['metaProperty1fr'] : '';
            $property1Fr = isset($instance['metaProperty2fr']) ? $instance['metaProperty2fr'] : '';
            $property2Fr = isset($instance['metaProperty3fr']) ? $instance['metaProperty3fr'] : '';
            $buttonТextFr = isset($instance['button_textfr']) ? $instance['button_textfr'] : '';

            $enableDe = isset($instance['enableTabde']) && $instance['enableTabde'] == 'on';
            $titleDe = isset($instance['titlede']) ? $instance['titlede'] : '';
            $listDe = isset($instance['list_idde']) ? $instance['list_idde'] : '';
            $property0De = isset($instance['metaProperty1de']) ? $instance['metaProperty1de'] : '';
            $property1De = isset($instance['metaProperty2de']) ? $instance['metaProperty2de'] : '';
            $property2De = isset($instance['metaProperty3de']) ? $instance['metaProperty3de'] : '';
            $buttonТextDe = isset($instance['button_textde']) ? $instance['button_textde'] : '';

            $enableEs = isset($instance['enableTabes']) && $instance['enableTabes'] == 'on';
            $titleEs = isset($instance['titlees']) ? $instance['titlees'] : '';
            $listEs = isset($instance['list_ides']) ? $instance['list_ides'] : '';
            $property0Es = isset($instance['metaProperty1es']) ? $instance['metaProperty1es'] : '';
            $property1Es = isset($instance['metaProperty2es']) ? $instance['metaProperty2es'] : '';
            $property2Es = isset($instance['metaProperty3es']) ? $instance['metaProperty3es'] : '';
            $buttonТextЕs = isset($instance['button_textes']) ? $instance['button_textes'] : '';

            $data = array(
                'en_US' =>
                    array(
                        'language_checkbox' => true,
                        'title' => $titleEn,
                        'list' => $listEn,
                        'language_mandatory_email' => '',
                        'language_mandatory_button' => $buttonТextЕn,
                        'contactProperties0' => $property0Id,
                        'propertyDataType0' => '0',
                        'EnglishLabel0' => $property0En,
                        'FrenchLabel0' => $property0Fr,
                        'GermanLabel0' => $property0De,
                        'SpanishLabel0' => $property0Es,
                        'ItalianLabel0' => '',
                        'contactProperties1' => $property1Id,
                        'propertyDataType1' => '0',
                        'EnglishLabel1' => $property1En,
                        'FrenchLabel1' => $property1Fr,
                        'GermanLabel1' => $property1De,
                        'SpanishLabel1' => $property1Es,
                        'ItalianLabel1' => '',
                        'contactProperties2' => $property2Id,
                        'propertyDataType2' => '0',
                        'EnglishLabel2' => $property2En,
                        'FrenchLabel2' => $property2Fr,
                        'GermanLabel2' => $property2De,
                        'SpanishLabel2' => $property2Es,
                        'ItalianLabel2' => '',
                        'contactProperties3' => '',
                        'propertyDataType3' => '0',
                        'EnglishLabel3' => '',
                        'FrenchLabel3' => '',
                        'GermanLabel3' => '',
                        'SpanishLabel3' => '',
                        'ItalianLabel3' => '',
                        'contactProperties4' => '',
                        'propertyDataType4' => '0',
                        'EnglishLabel4' => '',
                        'FrenchLabel4' => '',
                        'GermanLabel4' => '',
                        'SpanishLabel4' => '',
                        'ItalianLabel4' => '',
                        'confirmation_email_message_input' => '',
                        'subscription_confirmed_message_input' => '',
                        'empty_email_message_input' => '',
                        'already_subscribed_message_input' => '',
                        'invalid_data_format_message_input' => '',
                        'generic_technical_error_message_input' => '',
                        'email_subject' => '',
                        'email_content_title' => '',
                        'email_content_main_text' => '',
                        'email_content_confirm_button' => '',
                        'email_content_after_button' => '',
                    ),
                'English' =>
                    array(
                        'thank_you' => 0,
                    ),
                'fr_FR' =>
                    array(
                        'language_checkbox' => $enableFr,
                        'title' => $titleFr,
                        'list' => $listFr,
                        'language_mandatory_email' => '',
                        'language_mandatory_button' => $buttonТextFr,
                        'contactProperties0' => $property0Id,
                        'propertyDataType0' => '0',
                        'EnglishLabel0' => $property0En,
                        'FrenchLabel0' => $property0Fr,
                        'GermanLabel0' => $property0De,
                        'SpanishLabel0' => $property0Es,
                        'ItalianLabel0' => '',
                        'contactProperties1' => $property1Id,
                        'propertyDataType1' => '0',
                        'EnglishLabel1' => $property1En,
                        'FrenchLabel1' => $property1Fr,
                        'GermanLabel1' => $property1Fr,
                        'SpanishLabel1' => $property1Fr,
                        'ItalianLabel1' => '',
                        'contactProperties2' => $property2Id,
                        'propertyDataType2' => '0',
                        'EnglishLabel2' => $property2En,
                        'FrenchLabel2' => $property2Fr,
                        'GermanLabel2' => $property2De,
                        'SpanishLabel2' => $property2Es,
                        'ItalianLabel2' => '',
                        'contactProperties3' => '',
                        'propertyDataType3' => '',
                        'EnglishLabel3' => '',
                        'FrenchLabel3' => '',
                        'GermanLabel3' => '',
                        'SpanishLabel3' => '',
                        'ItalianLabel3' => '',
                        'contactProperties4' => '',
                        'propertyDataType4' => '',
                        'EnglishLabel4' => '',
                        'FrenchLabel4' => '',
                        'GermanLabel4' => '',
                        'SpanishLabel4' => '',
                        'ItalianLabel4' => '',
                        'confirmation_email_message_input' => '',
                        'subscription_confirmed_message_input' => '',
                        'empty_email_message_input' => '',
                        'already_subscribed_message_input' => '',
                        'invalid_data_format_message_input' => '',
                        'generic_technical_error_message_input' => '',
                        'email_subject' => '',
                        'email_content_title' => '',
                        'email_content_main_text' => '',
                        'email_content_confirm_button' => '',
                        'email_content_after_button' => '',
                    ),
                'French' =>
                    array(
                        'thank_you' => 0,
                    ),
                'de_DE' =>
                    array(
                        'language_checkbox' => $enableDe,
                        'title' => $titleDe,
                        'list' => $listDe,
                        'language_mandatory_email' => '',
                        'language_mandatory_button' => $buttonТextDe,
                        'contactProperties0' => $property0Id,
                        'propertyDataType0' => '0',
                        'EnglishLabel0' => $property0En,
                        'FrenchLabel0' => $property0Fr,
                        'GermanLabel0' => $property0De,
                        'SpanishLabel0' => $property0Es,
                        'ItalianLabel0' => '',
                        'contactProperties1' => $property1Id,
                        'propertyDataType1' => '0',
                        'EnglishLabel1' => $property1En,
                        'FrenchLabel1' => $property1Fr,
                        'GermanLabel1' => $property1Fr,
                        'SpanishLabel1' => $property1Fr,
                        'ItalianLabel1' => '',
                        'contactProperties2' => $property2Id,
                        'propertyDataType2' => '0',
                        'EnglishLabel2' => $property2En,
                        'FrenchLabel2' => $property2Fr,
                        'GermanLabel2' => $property2De,
                        'SpanishLabel2' => $property2Es,
                        'ItalianLabel2' => '',
                        'contactProperties3' => '',
                        'propertyDataType3' => '',
                        'EnglishLabel3' => '',
                        'FrenchLabel3' => '',
                        'GermanLabel3' => '',
                        'SpanishLabel3' => '',
                        'ItalianLabel3' => '',
                        'contactProperties4' => '',
                        'propertyDataType4' => '',
                        'EnglishLabel4' => '',
                        'FrenchLabel4' => '',
                        'GermanLabel4' => '',
                        'SpanishLabel4' => '',
                        'ItalianLabel4' => '',
                        'confirmation_email_message_input' => '',
                        'subscription_confirmed_message_input' => '',
                        'empty_email_message_input' => '',
                        'already_subscribed_message_input' => '',
                        'invalid_data_format_message_input' => '',
                        'generic_technical_error_message_input' => '',
                        'email_subject' => '',
                        'email_content_title' => '',
                        'email_content_main_text' => '',
                        'email_content_confirm_button' => '',
                        'email_content_after_button' => '',
                    ),
                'German' =>
                    array(
                        'thank_you' => 0,
                    ),
                'es_ES' =>
                    array(
                        'language_checkbox' => $enableEs,
                        'title' => $titleEs,
                        'list' => $listEs,
                        'language_mandatory_email' => '',
                        'language_mandatory_button' => $buttonТextЕs,
                        'contactProperties0' => $property0Id,
                        'propertyDataType0' => '0',
                        'EnglishLabel0' => $property0En,
                        'FrenchLabel0' => $property0Fr,
                        'GermanLabel0' => $property0De,
                        'SpanishLabel0' => $property0Es,
                        'ItalianLabel0' => '',
                        'contactProperties1' => $property1Id,
                        'propertyDataType1' => '0',
                        'EnglishLabel1' => $property1En,
                        'FrenchLabel1' => $property1Fr,
                        'GermanLabel1' => $property1Fr,
                        'SpanishLabel1' => $property1Fr,
                        'ItalianLabel1' => '',
                        'contactProperties2' => $property2Id,
                        'propertyDataType2' => '0',
                        'EnglishLabel2' => $property2En,
                        'FrenchLabel2' => $property2Fr,
                        'GermanLabel2' => $property2De,
                        'SpanishLabel2' => $property2Es,
                        'ItalianLabel2' => '',
                        'contactProperties3' => '',
                        'propertyDataType3' => '',
                        'EnglishLabel3' => '',
                        'FrenchLabel3' => '',
                        'GermanLabel3' => '',
                        'SpanishLabel3' => '',
                        'ItalianLabel3' => '',
                        'contactProperties4' => '',
                        'propertyDataType4' => '',
                        'EnglishLabel4' => '',
                        'FrenchLabel4' => '',
                        'GermanLabel4' => '',
                        'SpanishLabel4' => '',
                        'ItalianLabel4' => '',
                        'confirmation_email_message_input' => '',
                        'subscription_confirmed_message_input' => '',
                        'empty_email_message_input' => '',
                        'already_subscribed_message_input' => '',
                        'invalid_data_format_message_input' => '',
                        'generic_technical_error_message_input' => '',
                        'email_subject' => '',
                        'email_content_title' => '',
                        'email_content_main_text' => '',
                        'email_content_confirm_button' => '',
                        'email_content_after_button' => '',
                    ),
                'Spanish' =>
                    array(
                        'thank_you' => 0,
                    ),
                'it_IT' =>
                    array(
                        'language_checkbox' => false,
                        'title' => '',
                        'list' => '0',
                        'language_mandatory_email' => '',
                        'language_mandatory_button' => '',
                        'contactProperties0' => '',
                        'propertyDataType0' => '',
                        'EnglishLabel0' => '',
                        'FrenchLabel0' => '',
                        'GermanLabel0' => '',
                        'SpanishLabel0' => '',
                        'ItalianLabel0' => '',
                        'contactProperties1' => '',
                        'propertyDataType1' => '',
                        'EnglishLabel1' => '',
                        'FrenchLabel1' => '',
                        'GermanLabel1' => '',
                        'SpanishLabel1' => '',
                        'ItalianLabel1' => '',
                        'contactProperties2' => '',
                        'propertyDataType2' => '',
                        'EnglishLabel2' => '',
                        'FrenchLabel2' => '',
                        'GermanLabel2' => '',
                        'SpanishLabel2' => '',
                        'ItalianLabel2' => '',
                        'contactProperties3' => '',
                        'propertyDataType3' => '',
                        'EnglishLabel3' => '',
                        'FrenchLabel3' => '',
                        'GermanLabel3' => '',
                        'SpanishLabel3' => '',
                        'ItalianLabel3' => '',
                        'contactProperties4' => '',
                        'propertyDataType4' => '',
                        'EnglishLabel4' => '',
                        'FrenchLabel4' => '',
                        'GermanLabel4' => '',
                        'SpanishLabel4' => '',
                        'ItalianLabel4' => '',
                        'confirmation_email_message_input' => '',
                        'subscription_confirmed_message_input' => '',
                        'empty_email_message_input' => '',
                        'already_subscribed_message_input' => '',
                        'invalid_data_format_message_input' => '',
                        'generic_technical_error_message_input' => '',
                        'email_subject' => '',
                        'email_content_title' => '',
                        'email_content_main_text' => '',
                        'email_content_confirm_button' => '',
                        'email_content_after_button' => '',
                    ),
                'Italian' =>
                    array(
                        'thank_you' => 0,
                    )
            );

            update_option('widget_wp_mailjet_subscribe_widget', $data);
            $instance = get_option('widget_wp_mailjet_subscribe_widget');
        }
        return $instance;
    }

    /**
     * Generates the administration form for the widget.
     *
     * @param array instance The array of keys and values for the widget.
     */
    public function form($instance)
    {
        wp_enqueue_style($this->get_widget_slug() . '-widget-styles', plugins_url('css/widget.css', __FILE__), array(), MAILJET_VERSION, 'all');
        wp_enqueue_script($this->get_widget_slug() . '-script');

        $validApiCredentials = MailjetApi::isValidAPICredentials();
        if ($validApiCredentials === false) {
            include(plugin_dir_path(__FILE__) . 'views' . DIRECTORY_SEPARATOR . 'designforfailure.php');
            return false;
        }

        $instance = $this->checkTransition($instance);

        // Define default values for your variables
        $instance = wp_parse_args(
            (array) $instance
        );

        // Mailjet contact lists
        try {
            $mailjetContactLists = MailjetApi::getMailjetContactLists();
        } catch (\Exception $ex) {
            include(plugin_dir_path(__FILE__) . 'views' . DIRECTORY_SEPARATOR . 'designforfailure.php');
            return false;
        }

        $contactLists = !empty($mailjetContactLists) ? $mailjetContactLists : array();
        $mailjetContactProperties = $this->getMailjetContactProperties();

        $propertiesOptions = array();
        if (!empty($mailjetContactProperties)) {
            foreach ($mailjetContactProperties as $property) {
                $propertiesOptions[$property['ID']] = $property['Name'];
            }
        }

        // Mailjet is down (widget can't be configured so show an error instead of form)
        if ($mailjetContactLists === false && $mailjetContactProperties === false) {
            $isMailjetDown = 'No connection with Mailjet.Please try a bit later.';
        }

        $mailjetContactProperties = $propertiesOptions;
        $admin_locale = Mailjeti18n::getLocale();
        // Display the admin form
        $languages = Mailjeti18n::getSupportedLocales();
        $pages = get_pages();
        include(plugin_dir_path(__FILE__) . 'views' . DIRECTORY_SEPARATOR . 'admin.php');
    }

    /* -------------------------------------------------- */
    /* Public Functions
      /*-------------------------------------------------- */

    /**
     * Loads the Widget's text domain for localization and translation.
     */
    public function widget_textdomain()
    {
        load_plugin_textdomain('mailjet-for-wordpress', false, dirname(dirname(dirname(plugin_basename(__FILE__)))) . '/languages/');
        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'mailjet\' text domain loaded ] - ' . dirname(dirname(dirname(plugin_basename(__FILE__)))) . '/languages/');
    }

// end widget_textdomain

    public function register_widget_front_styles()
    {
        wp_register_style($this->get_widget_slug() . '-widget-front-styles', plugins_url('css/front-widget.css', __FILE__), array(), MAILJET_VERSION, 'all');
    }

    /**
     * Registers and enqueues widget-specific scripts.
     */
    public function register_widget_scripts()
    {
        wp_register_script($this->get_widget_slug() . '-script', plugins_url('js/widget.js', __FILE__), array('jquery'));
        wp_localize_script($this->get_widget_slug() . '-script', 'myAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    }

    public function register_widget_front_scripts()
    {
        wp_enqueue_script( 'jquery' );
        wp_register_script($this->get_widget_slug() . '-front-script', plugins_url('js/front-widget.js', __FILE__));
        wp_localize_script($this->get_widget_slug() . '-front-script', 'mjWidget', array('ajax_url' => admin_url('admin-ajax.php')));
        wp_enqueue_script($this->get_widget_slug() . '-front-script');
        wp_enqueue_style($this->get_widget_slug() . '-widget-front-styles', plugins_url('css/front-widget.css', __FILE__));
    }

// end register_widget_scripts

    private function getMailjetContactProperties()
    {
        if ($this->mailjetContactProperties == null) {
            try {
                $this->mailjetContactProperties = MailjetApi::getContactProperties();
            } catch (Exception $ex) {
                return false;
            }
        }
        return $this->mailjetContactProperties;
    }

    private function getSubscriptionOptionsSettings()
    {
        if ($this->subscriptionOptionsSettings == null) {
            return SubscriptionOptionsSettings::getInstance();
        }
        return $this->subscriptionOptionsSettings;
    }

    function wp_ajax_mailjet_add_contact_property()
    {
        if (!empty($_POST['propertyName'])) {
            $type = !empty($_POST['propertyType']) ? sanitize_text_field($_POST['propertyType']) : 'str';
            $propertyName = sanitize_text_field($_POST['propertyName']);
            echo json_encode(MailjetApi::createMailjetContactProperty($propertyName, $type));
        }
        die;
    }

    private function getUpdateArrayKeysPairs()
    {
        $result = [
            'language_mandatory_email' => '[language_mandatory_email',
            'language_mandatory_button' => '[language_mandatory_button',
//            'contactProperties' => '[contactProperties',
//            'propertyDataType' => '[propertyDataType',
//            'Label' => '[Label',
            'confirmation_email_message_input' => '[confirmation_email_message_input',
            'subscription_confirmed_message_input' => '[subscription_confirmed_message_input',
            'empty_email_message_input' => '[empty_email_message_input',
            'already_subscribed_message_input' => '[already_subscribed_message_input',
            'invalid_data_format_message_input' => '[invalid_data_format_message_input',
            'generic_technical_error_message_input' => '[generic_technical_error_message_input',
            'email_subject' => '[email_subject',
            'email_content_title' => '[email_content_title',
            'email_content_main_text' => '[email_content_main_text',
            'email_content_confirm_button' => '[email_content_confirm_button',
            'email_content_after_button' => '[email_content_after_button',
//            'thank_you' => '[thank_you'
        ];

        return $result;
    }
}
