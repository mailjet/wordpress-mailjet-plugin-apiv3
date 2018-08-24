<?php

namespace MailjetPlugin\Widget;

use MailjetPlugin\Includes\MailjetApi;

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
    protected $widget_slug = 'mailjet';

    private $mailjetClient = null;
    private $instance;
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

//        var_dump($this->get_settings());
        // Register site styles and scripts
        add_action('admin_print_styles', array($this, 'register_widget_styles'));
        add_action('admin_enqueue_scripts', array($this, 'register_widget_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'register_widget_styles'));
        add_action('wp_enqueue_scripts', array($this, 'register_widget_scripts'));

        // Refreshing the widget's cached output with each new post
        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));
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
     * Provide mailjet client instance
     * @return \Mailjet\Client | null
     */
    private function getMailjetClient()
    {
        if ($this->mailjetClient === null) {
            $mailjetApikey = get_option('mailjet_apikey');
            $mailjetApiSecret = get_option('mailjet_apisecret');
            $this->mailjetClient = new \Mailjet\Client($mailjetApikey, $mailjetApiSecret);
        }
        return $this->mailjetClient;
    }

    /**
     * Check if subscription form is submited
     * Check if the user is already subscribed
     * Send subscription email if need
     * @param \MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings $subscriptionOptionsSettings
     * @return boolean
     */
    private function sendSubscriptionEmail($subscriptionOptionsSettings) 
    {
        // Check if subscription form is submited
        if (empty($_POST['subscription_email'])) {
            // Subscription form is not submited
            return true;
        }

        // Check if the user is subscribed
        // Todo

        // Send subscription email
        $subscription_email = $_POST['subscription_email'];
        if (!is_email($subscription_email)) {
            _e('Invalid email', 'mailjet');
            die;
        }
        $subscriptionOptionsSettings->mailjet_subscribe_confirmation_from_widget($subscription_email);
    }

    /**
     * Validete the confirmation link
     * Subscribe to mailjet list
     * @param type $subscriptionOptionsSettings
     */
    private function activateConfirmSubscriptionUrl($subscriptionOptionsSettings, $instance)
    {
        $contacts = array();

        // Check if subscription email is confirmed
        if (empty($_GET['mj_sub_token'])) {
            return true;
        }

        $subscription_email = $_GET['subscription_email'];
        $params = http_build_query(array('subscription_email' => $subscription_email));

        // The token is valid we can subscribe the user
        if ($_GET['mj_sub_token'] == sha1($params . $subscriptionOptionsSettings::WIDGET_HASH)) {
            $locale = \MailjetPlugin\Includes\Mailjeti18n::getLocale();
            $contactListId = !empty($instance[$locale]['list']) ? (int) $instance[$locale]['list'] : false;

            // List id is not provided
            if(!$contactListId) {
                // Log
            }
            
//            $contactProperties = array();
            $contacts[] = array(
                'Email' => $subscription_email,
//                'Name' => $contactProperties['first_name'] . ' ' . $contactProperties['last_name'],
//                'Properties' => $contactProperties
            );
            $result = MailjetApi::syncMailjetContacts($contactListId, $contacts);
            var_dump($result);
//            $mailjetClient
        } else {
            // Invalid token
            // Todo add Log and message
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
        $subscriptionOptionsSettings = new \MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings;

        // Widget front form is submited
        // TODO: Check if the user is already subscribed
        // Send subscription email if need
        $this->sendSubscriptionEmail($subscriptionOptionsSettings);

        // Subscribe user
        $this->activateConfirmSubscriptionUrl($subscriptionOptionsSettings, $instance);
        
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

        // TODO: Here is where you manipulate your widget's values based on their input fields
        ob_start();
        include(plugin_dir_path(__FILE__) . 'views/widget.php');
        $widget_string .= ob_get_clean();
        $widget_string .= $after_widget;


        $cache[$args['widget_id']] = $widget_string;

        wp_cache_set($this->get_widget_slug(), $cache, 'widget');

        print $widget_string;
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

        $languages = \MailjetPlugin\Includes\Mailjeti18n::getSupportedLocales();

        foreach ($languages as $language => $locale) {
            $instance[$locale]['language_checkbox'] = isset($new_instance[$locale]['language_checkbox']) ? 1 : false;
            $instance[$locale]['title'] = isset($new_instance[$locale]['title']) ? wp_strip_all_tags($new_instance[$locale]['title']) : '';
            $instance[$locale]['list'] = isset($new_instance[$locale]['list']) ? wp_strip_all_tags($new_instance[$locale]['list']) : '';

            // Translations update
            \MailjetPlugin\Includes\Mailjeti18n::updateTranslationsInFile($locale, $instance[$locale]);
        }
        $this->instance = $instance;
        return $instance;
    }

    /**
     * Generates the administration form for the widget.
     *
     * @param array instance The array of keys and values for the widget.
     */
    public function form($instance)
    {
        // TODO: Define default values for your variables
        $instance = wp_parse_args(
                (array) $instance
        );

        // Mailjet contact lists
        $contactLists = MailjetApi::getMailjetContactLists();
        $contactLists = !empty($contactLists) ? $contactLists : array();

        // Display the admin form
        $languages = \MailjetPlugin\Includes\Mailjeti18n::getSupportedLocales();
        include(plugin_dir_path(__FILE__) . 'views/admin.php');
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
        \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'mailjet\' text domain loaded ] - ' . dirname(dirname(dirname(plugin_basename(__FILE__)))) . '/languages/');
    }

// end widget_textdomain

    /**
     * Registers and enqueues widget-specific styles.
     */
    public function register_widget_styles()
    {
        wp_enqueue_style($this->get_widget_slug() . '-widget-styles', plugins_url('css/widget.css', __FILE__));
        wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        wp_enqueue_style('prefix_bootstrap');
    }

// end register_widget_styles

    /**
     * Registers and enqueues widget-specific scripts.
     */
    public function register_widget_scripts()
    {
        wp_enqueue_script($this->get_widget_slug() . '-script', plugins_url('js/widget.js', __FILE__), array('jquery'));
        wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
        wp_enqueue_script('prefix_bootstrap');
    }

// end register_widget_scripts
}

// end class
