<?php

namespace MailjetPlugin\Widget;

use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\Mailjeti18n;
use MailjetPlugin\Includes\MailjetLogger;
use MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings;

class WP_Mailjet_Subscribe_Widget extends \WP_Widget
{

    /**
     *
     * Unique identifier for your widget.
     *
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * widget file.
     *
     * @since    5.0.0
     *
     * @var      string
     */
    private $subscriptionOptionsSettings = null;
    // protected $widget_slug = 'mailjet';
    protected $widget_slug = 'wp_mailjet_subscribe_widget';
    private $instance;
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
            'description' => __('Allows your visitors to subscribe to one of your lists', $this->get_widget_slug())
        );
        parent::__construct(
                $this->get_widget_slug(), __('Mailjet Subscription Widget', $this->get_widget_slug()), $widget_options
        );

        // Register site styles and scripts
        add_action('admin_print_styles', array($this, 'register_widget_styles'));
        add_action('admin_enqueue_scripts', array($this, 'register_widget_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'register_widget_styles'));
        add_action('admin_enqueue_scripts', array($this, 'register_widget_scripts'));

        // Refreshing the widget's cached output with each new post
        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));

        add_action('wp_ajax_mailjet_add_contact_property', array($this, 'wp_ajax_mailjet_add_contact_property'));

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
     * Check if subscription form is submited
     * Check if the user is already subscribed
     * Send subscription email if need
     * @param SubscriptionOptionsSettings $subscriptionOptionsSettings
     * @return boolean
     */
    private function sendSubscriptionEmail($subscriptionOptionsSettings, $instance)
    {
        $locale = Mailjeti18n::getLocale();
        // Check if subscription form is submited
        if (!isset($_POST['subscription_email'])) {
            // Subscription form is not submited
            return false;
        }

        // Submited but empty
        if (empty($_POST['subscription_email'])) {
            return !empty($instance[$locale]['empty_email_message_input']) ? $instance[$locale]['empty_email_message_input'] : Mailjeti18n::getTranslationsFromFile($locale, 'Please provide an email address');
        }

        // Send subscription email
        $subscription_email = $_POST['subscription_email'];
        if (!is_email($subscription_email)) {
            return Mailjeti18n::getTranslationsFromFile($locale, 'Invalid email');
        }

        $properties = isset($_POST['properties']) ? $_POST['properties'] : array();
        $mailjetContactProperties = $this->getMailjetContactProperties();
        $incorectTypeValue = !empty($instance[$locale]['invalid_data_format_message_input']) ? $instance[$locale]['invalid_data_format_message_input'] : Mailjeti18n::getTranslationsFromFile($locale, 'The value you entered is not in the correct format.');

        if(!empty($properties) && is_array($mailjetContactProperties) && !empty($mailjetContactProperties)) {
            foreach($properties as $propertyId => $propertyName) {
                if($propertyName == '') {
                    continue;
                }
                foreach($mailjetContactProperties as $mailjetContactProperty) {
                    if($propertyId == $mailjetContactProperty['ID']) {
                        $dataType = $mailjetContactProperty['Datatype'];
                        switch($dataType) {
                            case "str":
                                // by default
                                break;
                            case "int":
                                $propertyNameCopy = $propertyName;
                                $intProperty = (int) $propertyNameCopy;
                                if ($intProperty == 0 && $propertyName !== "0") {
                                    return $incorectTypeValue;
                                }
                                break;
                            case "float":
                                $propertyNameCopy = $propertyName;
                                $fProperty = (float) $propertyNameCopy;
                                if(!is_float($fProperty) || $fProperty == 0) {
                                    return $incorectTypeValue;
                                }
                                if ($fProperty == 0 && $propertyName !== "0") {
                                    return $incorectTypeValue;
                                }
                                break;
                            case "datetime":
                                $propertyDate = str_replace('-', '/', $properties[$propertyId]);
                                $datetime = \DateTime::createFromFormat("d/m/Y", $propertyDate);
                                $errors = \DateTime::getLastErrors();
                                if (!$datetime instanceof \DateTime) {
                                    return $incorectTypeValue;
                                }
                                if (!empty($errors['warning_count'])) {
                                     return $incorectTypeValue;
                                }
                                break;
                            case "bool":
                                $booleans = array('true', 'false', '1', '0','yes', 'no', 'ok');
                                if(!in_array($propertyName, $booleans)) {
                                    return $incorectTypeValue;
                                }
                                break;
                        }
                        continue;
                    }
                }
            }
        }

        $sendingResult = $subscriptionOptionsSettings->mailjet_subscribe_confirmation_from_widget($subscription_email, $instance);
        if ($sendingResult) {
            return !empty($instance[$locale]['confirmation_email_message_input']) ? $instance[$locale]['confirmation_email_message_input'] : Mailjeti18n::getTranslationsFromFile($locale, 'Subscription confirmation email sent. Please check your inbox and confirm the subscription.');
        }
        return !empty($instance[$locale]['technical_error_message_input']) ? $instance[$locale]['technical_error_message_input'] : Mailjeti18n::getTranslationsFromFile($locale, 'A technical issue has prevented your subscription. Please try again later.');
    }

    /**
     * Validete the confirmation link
     * Subscribe to mailjet list
     * @param type $subscriptionOptionsSettings
     */
    private function activateConfirmSubscriptionUrl()
    {
        $locale = Mailjeti18n::getLocale();
        $subscriptionOptionsSettings = $this->getSubscriptionOptionsSettings();
        $contacts = array();

        // Check if subscription email is confirmed
        if (empty($_GET['mj_sub_token'])) {
            return true;
        }

        $technicalIssue = Mailjeti18n::getTranslationsFromFile($locale, 'A technical issue has prevented your subscription. Please try again later.');

        $subscription_email = isset($_GET['subscription_email']) ? $_GET['subscription_email'] : '';
        if (!$subscription_email) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Subscription email is missing ]');
            echo $technicalIssue;
            die;
        }

        $properties = isset($_GET['properties']) ? $_GET['properties'] : array();
        $params = http_build_query(array(
            'subscription_email' => $subscription_email,
            'properties' => $properties,
        ));

        // The token is valid we can subscribe the user
        if ($_GET['mj_sub_token'] == sha1($params . $subscriptionOptionsSettings::WIDGET_HASH)) {

            $contactListId = get_option('mailjet_locale_subscription_list_' . $locale);

            // List id is not provided
            if (!$contactListId) {
                // Use en_US list id as default
                $listIdEn = get_option('mailjet_locale_subscription_list_en_US');
                if (!$listIdEn) {
                    MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ContactList ID is not provided for ' . $locale . '! ]');
                    die;
                }
                $contactListId = $listIdEn;
            }

            $dataProperties = array();
            $mailjetContactProperties = $this->getMailjetContactProperties();
            if (!empty($mailjetContactProperties)) {
                foreach ($mailjetContactProperties as $property) {
                    if (isset($properties[$property['ID']]) && $properties[$property['ID']] != '' ) {
                        $dataType = $property['Datatype'];
                        switch ($dataType) {
                            case "datetime":
                                $datetime = \DateTime::createFromFormat("d/m/Y", $properties[$property['ID']]);
                                if ($datetime instanceof \DateTime) {
                                    $dataProperties[$property['Name']] = $datetime->format(\DateTime::RFC3339);
                                }
                                break;
                            case "int":
                                $dataProperties[$property['Name']] = (int)$properties[$property['ID']];
                                break;
                            case "float":
                                $dataProperties[$property['Name']] = (float)$properties[$property['ID']];
                                break;
                            case "bool":
                                $positiveBooleans = array('true', '1', 'yes', 'ok');
                                if(in_array($properties[$property['ID']], $positiveBooleans)) {
                                    $dataProperties[$property['Name']] = true;
                                }else{
                                    $dataProperties[$property['Name']] = false;
                                }
                                break;
                            case "str":
                            default:
                                $dataProperties[$property['Name']] = $properties[$property['ID']];
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
                MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ ContactList: ' . $contactListId . ' is deleted and ' . $subscription_email . ' was not subscribed ]');
                echo $technicalIssue;
                die;
            }

            $result = MailjetApi::syncMailjetContact($contactListId, $contact);
            if (!$result) {
                MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Subscription failed ]');
                echo $technicalIssue;
                die;
            }

            // Subscribed
            $language = Mailjeti18n::getCurrentUserLanguage();
            $thankYouPageId = get_option('mailjet_thank_you_page_' . $language);

            // If no selected page, select default template
            if (!$thankYouPageId) {
                $locale = Mailjeti18n::getLocaleByPll();
                $newsletterRegistration = Mailjeti18n::getTranslationsFromFile($locale, 'Newsletter Registration');
                $congratsSubscribed = Mailjeti18n::getTranslationsFromFile($locale, 'Congratulations, you have successfully subscribed!');

                $tankyouPageTemplate = apply_filters('mailjet_thank_you_page_template', plugin_dir_path(__FILE__) . 'templates' . DIRECTORY_SEPARATOR . 'thankyou.php');
                // Default page is selected
                include($tankyouPageTemplate);
                die;
            }
        } else {
            // Invalid token
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ User token is invalid.Subscription email ' . $subscription_email . ']');
            echo $technicalIssue;
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
        $mailjetContactProperties = $this->getMailjetContactProperties();
        if (!empty($mailjetContactProperties) && is_array($mailjetContactProperties)) {
            foreach ($mailjetContactProperties as $mjContactProperty) {
                $this->propertyData[$mjContactProperty['ID']] = array(
                    'Name' => $mjContactProperty['Name'],
                    'Datatype' => $mjContactProperty['Datatype']
                );
            }
        }
        $subscriptionOptionsSettings = $this->getSubscriptionOptionsSettings();

        // Send subscription email if need
        $form_message = $this->sendSubscriptionEmail($subscriptionOptionsSettings, $instance);

        // Subscribe user
//        $this->activateConfirmSubscriptionUrl();
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

        $widget_string = $before_widget;

        ob_start();
        $front_widget_file = apply_filters('mailjet_widget_form_filename', plugin_dir_path(__FILE__) . 'views/widget.php');
        include($front_widget_file);
        $widget_string .= ob_get_clean();
        $widget_string .= $after_widget;

        $cache[$args['widget_id']] = $widget_string;

        wp_cache_set($this->get_widget_slug(), $cache, 'widget');
        print $widget_string;
    }

    private function getInputType($inputType)
    {
        switch ($inputType) {
            case 'str':
                $inputType = 'text';
                break;
            case 'int':
                $inputType = 'number';
                break;
            case 'datetime':
                $inputType = 'date';
                break;
            case 'float':
            case 'bool':
                $inputType = 'text';
                break;
            default:
                $inputType = 'text';
                break;
        }
        return $inputType;
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
        foreach ($languages as $language => $locale) {
            // Do not save if language is active but there is no contact list chosen for it
            if (isset($new_instance[$locale]['language_checkbox']) && $new_instance[$locale]['list'] == "0") {
                continue;
            }

            // Initial
            $instance[$locale]['language_checkbox'] = isset($new_instance[$locale]['language_checkbox']) ? 1 : false;
            $instance[$locale]['title'] = isset($new_instance[$locale]['title']) ? wp_strip_all_tags($new_instance[$locale]['title']) : '';
            $instance[$locale]['list'] = isset($new_instance[$locale]['list']) ? wp_strip_all_tags($new_instance[$locale]['list']) : '';
            update_option('mailjet_locale_subscription_list_' . $locale, $instance[$locale]['list']);

            // Tab 1
            $instance[$locale]['language_mandatory_email'] = isset($new_instance[$locale]['language_mandatory_email']) ? wp_strip_all_tags($new_instance[$locale]['language_mandatory_email']) : '';
//            $buttonLabel = isset($new_instance[$locale]['language_mandatory_button']) ? apply_filters('widget_title', $new_instance[$locale]['language_mandatory_button']) : '';
//            $instance[$locale]['language_mandatory_button'] = isset($new_instance[$locale]['language_mandatory_button']) ? wp_strip_all_tags($new_instance[$locale]['language_mandatory_button']) : $buttonLabel;
            $instance[$locale]['language_mandatory_button'] = isset($new_instance[$locale]['language_mandatory_button']) ? wp_strip_all_tags($new_instance[$locale]['language_mandatory_button']) : '';

            for ($i = 0; $i <= 4; $i++) {
                $instance[$locale]['contactProperties' . $i] = isset($new_instance[$locale]['contactProperties' . $i]) ? wp_strip_all_tags($new_instance[$locale]['contactProperties' . $i]) : '';
                $instance[$locale]['propertyDataType' . $i] = isset($new_instance[$locale]['propertyDataType' . $i]) ? wp_strip_all_tags($new_instance[$locale]['propertyDataType' . $i]) : '';

//                $instance[$locale][$language.'Label'.$i] = isset($new_instance[$locale][$language.'Label'.$i]) ? wp_strip_all_tags($new_instance[$locale][$language.'Label'.$i]) : '';
                $instance[$locale]['EnglishLabel' . $i] = isset($new_instance[$locale]['EnglishLabel' . $i]) ? wp_strip_all_tags($new_instance[$locale]['EnglishLabel' . $i]) : '';
                $instance[$locale]['FrenchLabel' . $i] = isset($new_instance[$locale]['FrenchLabel' . $i]) ? wp_strip_all_tags($new_instance[$locale]['FrenchLabel' . $i]) : '';
                $instance[$locale]['GermanLabel' . $i] = isset($new_instance[$locale]['GermanLabel' . $i]) ? wp_strip_all_tags($new_instance[$locale]['GermanLabel' . $i]) : '';
                $instance[$locale]['SpanishLabel' . $i] = isset($new_instance[$locale]['SpanishLabel' . $i]) ? wp_strip_all_tags($new_instance[$locale]['SpanishLabel' . $i]) : '';
                $instance[$locale]['ItalianLabel' . $i] = isset($new_instance[$locale]['ItalianLabel' . $i]) ? wp_strip_all_tags($new_instance[$locale]['ItalianLabel' . $i]) : '';
            }

            // Tab 2
            $instance[$locale]['confirmation_email_message_input'] = isset($new_instance[$locale]['confirmation_email_message_input']) ? wp_strip_all_tags($new_instance[$locale]['confirmation_email_message_input']) : '';
            $instance[$locale]['subscription_confirmed_message_input'] = isset($new_instance[$locale]['subscription_confirmed_message_input']) ? wp_strip_all_tags($new_instance[$locale]['subscription_confirmed_message_input']) : '';
            $instance[$locale]['empty_email_message_input'] = isset($new_instance[$locale]['empty_email_message_input']) ? wp_strip_all_tags($new_instance[$locale]['empty_email_message_input']) : '';
            $instance[$locale]['already_subscribed_message_input'] = isset($new_instance[$locale]['already_subscribed_message_input']) ? wp_strip_all_tags($new_instance[$locale]['already_subscribed_message_input']) : '';
            $instance[$locale]['invalid_data_format_message_input'] = isset($new_instance[$locale]['invalid_data_format_message_input']) ? wp_strip_all_tags($new_instance[$locale]['invalid_data_format_message_input']) : '';
            $instance[$locale]['generic_technical_error_message_input'] = isset($new_instance[$locale]['generic_technical_error_message_input']) ? wp_strip_all_tags($new_instance[$locale]['generic_technical_error_message_input']) : '';

            // Tab 3
            $instance[$locale]['email_subject'] = isset($new_instance[$locale]['email_subject']) ? wp_strip_all_tags($new_instance[$locale]['email_subject']) : '';
            $instance[$locale]['email_content_title'] = isset($new_instance[$locale]['email_content_title']) ? wp_strip_all_tags($new_instance[$locale]['email_content_title']) : '';
            $instance[$locale]['email_content_main_text'] = isset($new_instance[$locale]['email_content_main_text']) ? wp_strip_all_tags($new_instance[$locale]['email_content_main_text']) : '';
            $instance[$locale]['email_content_confirm_button'] = isset($new_instance[$locale]['email_content_confirm_button']) ? wp_strip_all_tags($new_instance[$locale]['email_content_confirm_button']) : '';
            $instance[$locale]['email_content_after_button'] = isset($new_instance[$locale]['email_content_after_button']) ? wp_strip_all_tags($new_instance[$locale]['email_content_after_button']) : '';

            // Tab 4
            $instance[$language]['thank_you'] = isset($new_instance[$language]['thank_you']) ? wp_strip_all_tags($new_instance[$language]['thank_you']) : 0;
            update_option('mailjet_thank_you_page_' . $language, $instance[$language]['thank_you']);

            // Translations update
            Mailjeti18n::updateTranslationsInFile($locale, $instance[$locale]);
        }
        $this->instance = $instance;
        return $instance;
    }

    /**
     * Register the dynamic translations to the po files
     */
    private function registerCustomLanguageTranslations()
    {
        __('English', 'mailjet');
        __('French', 'mailjet');
        __('German', 'mailjet');
        __('Spanish', 'mailjet');
        __('Italian', 'mailjet');
        __('your@email.com', 'mailjet');
        __('Subscribe', 'mailjet');
        __('Subscription confirmation email sent. Please check your inbox and confirm your subscription.', 'mailjet');
        __('Your subscription was successfully confirmed.', 'mailjet');
        __('Please provide an email address', 'mailjet');
        __('Please confirm your subscription', 'mailjet');
        __('This email address has already been subscribed.', 'mailjet');
        __('The value you entered is not in the correct format.', 'mailjet');
        __('A technical issue has prevented your subscription. Please try again later.', 'mailjet');
        __('Yes, subscribe me to this list', 'mailjet');
        __("If you received this email by mistake or don't wish to subscribe anymore, simply ignore this message", 'mailjet');
    }

    /**
     *  Transition widget settings from v4 t ov5
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
            $property2En = isset($instance['metaProperty2en']) ? $instance['metaProperty3en'] : '';
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
        $validApiCredentials = MailjetApi::isValidAPICredentials();
        if (false == $validApiCredentials) {
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

        $mailjetContactProperties = null;
        $mailjetContactProperties = $propertiesOptions;
//        $admin_locale = get_locale();
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
        load_plugin_textdomain($this->get_widget_slug(), false, dirname(dirname(dirname(plugin_basename(__FILE__)))) . '/languages/');
        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'mailjet\' text domain loaded ] - ' . dirname(dirname(dirname(plugin_basename(__FILE__)))) . '/languages/');
    }

// end widget_textdomain

    /**
     * Registers and enqueues widget-specific styles.
     */
    public function register_widget_styles()
    {
        wp_enqueue_style($this->get_widget_slug() . '-widget-styles', plugins_url('css/widget.css', __FILE__));
        wp_register_style('prefix_bootstrap', plugins_url('css/bootstrap.css', __FILE__));
//        wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        wp_enqueue_style('prefix_bootstrap');
    }

    public function register_widget_front_styles()
    {
        wp_enqueue_style($this->get_widget_slug() . '-widget-styles', plugins_url('css/front-widget.css', __FILE__));
    }

// end register_widget_styles

    /**
     * Registers and enqueues widget-specific scripts.
     */
    public function register_widget_scripts()
    {
        wp_register_script($this->get_widget_slug() . '-script', plugins_url('js/widget.js', __FILE__), array('jquery'));
        wp_localize_script($this->get_widget_slug() . '-script', 'myAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
        wp_enqueue_script($this->get_widget_slug() . '-script');

        wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
        wp_enqueue_script('prefix_bootstrap');
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
            return new SubscriptionOptionsSettings;
        }
        return $this->subscriptionOptionsSettings;
    }

    function wp_ajax_mailjet_add_contact_property()
    {
        if (!empty($_POST['propertyName'])) {
            $type = !empty($_POST['propertyType']) ? $_POST['propertyType'] : 'str';
            echo json_encode(MailjetApi::createMailjetContactProperty($_POST['propertyName'], $type));
        }
        die;
    }

}

// end class
