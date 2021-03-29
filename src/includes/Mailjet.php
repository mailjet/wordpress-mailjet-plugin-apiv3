<?php

namespace MailjetPlugin\Includes;

use MailjetPlugin\Admin\MailjetAdmin;
use MailjetPlugin\Front\MailjetPublic;
use MailjetPlugin\Includes\SettingsPages\IntegrationsSettings;
use MailjetPlugin\Includes\SettingsPages\UserAccessSettings;
use MailjetPlugin\Includes\SettingsPages\WooCommerceSettings;


/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      5.0.0
 * @package    Mailjet
 * @subpackage Mailjet/includes
 * @author     Your Name <email@example.com>
 */
class Mailjet
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    5.0.0
     * @access   protected
     * @var      Mailjet_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    5.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    5.0.0
     */
    public function __construct()
    {
        if (defined('MAILJET_VERSION')) {
            $this->version = MAILJET_VERSION;
        } else {
            $this->version = '5.2.6';
        }
        $this->plugin_name = 'mailjet';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->addMailjetMenu();
        $this->addMailjetSettings();
        $this->addMailjetPHPMailer();
        $this->registerMailjetWidget();

        add_shortcode('mailjet_subscribe', array($this, 'display_mailjet_widget'));
    }


    public static function display_mailjet_widget($atts = [], $content = null, $tag = '')
    {
        extract(shortcode_atts(array(
            'widget_id' => null
        ), $atts, $tag));

        // GET All Mailjet widgets - to find the one that user actually configured with the shortcode
        $instance = get_option('widget_wp_mailjet_subscribe_widget');

        // In case we don't have 'widget_id' attribute in the shrotcode defined by user - we use the first widget id from the collection
        if (empty($widget_id)) {
            $widgetIds = [];
            foreach (array_keys($instance) as $key) {
                if (is_integer($key)) {
                    $widgetIds[] = $key;
                }
            }
            $widget_id = min($widgetIds);
        }

        ob_start();
        the_widget('MailjetPlugin\Widget\WP_Mailjet_Subscribe_Widget', $instance[intval($widget_id)]);
        return ob_get_clean();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Mailjet_Loader. Orchestrates the hooks of the plugin.
     * - Mailjeti18n. Defines internationalization functionality.
     * - Mailjet_Admin. Defines all hooks for the admin area.
     * - Mailjet_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    5.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        $this->loader = new MailjetLoader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Mailjeti18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    5.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Mailjeti18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new MailjetAdmin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_post_user_access_settings_custom_hook', new UserAccessSettings(), 'user_access_post_handler');
        $this->loader->add_action('admin_post_integrationsSettings_custom_hook', new IntegrationsSettings(), 'integrations_post_handler');

        if (get_option('activate_mailjet_woo_integration') === '1'){
            $this->addWoocommerceActions();
        }

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    5.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new MailjetPublic($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    private function addMailjetMenu()
    {
        $plugin_menu = new MailjetMenu();

        $this->loader->add_action('admin_menu', $plugin_menu, 'display_menu');
    }

    private function addMailjetSettings()
    {
        $plugin_settings = new MailjetSettings();

        $this->loader->add_action('admin_init', $plugin_settings, 'mailjet_settings_admin_init');
        $this->loader->add_action('init', $plugin_settings, 'mailjet_settings_init');
    }

    private function addMailjetPHPMailer()
    {
        $plugin_mails = new MailjetMail();

        $this->loader->add_action('phpmailer_init', $plugin_mails, 'phpmailer_init_smtp');
        $this->loader->add_action('wp_mail_failed', $plugin_mails, 'wp_mail_failed_cb');
    }

    private function registerMailjetWidget()
    {
        $this->loader->add_action('widgets_init', $this, 'wp_mailjet_register_widgets');
    }

    function wp_mailjet_register_widgets()
    {
        // It works only for wp >= 4.6.0
        // $widget = new WP_Mailjet_Subscribe_Widget()

        // This works for wp function < 4.6
        $widget = 'MailjetPlugin\Widget\WP_Mailjet_Subscribe_Widget';
        register_widget($widget);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    5.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     5.0.0
     * @return    Mailjet_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     5.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

    private function addWoocommerceActions()
    {
        $wooCommerceSettings = WooCommerceSettings::getInstance();

        $this->loader->add_action('admin_post_order_notification_settings_custom_hook', $wooCommerceSettings, 'orders_automation_settings_post');
        $this->loader->add_action('admin_post_abandoned_cart_settings_custom_hook', $wooCommerceSettings, 'abandoned_cart_settings_post');

        if (get_option('mailjet_woo_edata_sync') === '1'){
            $this->loader->add_action('woocommerce_order_status_changed', $wooCommerceSettings, 'order_edata_sync', 10, 1);
            $this->loader->add_action('woocommerce_cheque_process_payment_order_status', $wooCommerceSettings, 'paid_by_cheque_order_edata_sync', 10, 2);
        }

        $activeActions = get_option('mailjet_wc_active_hooks');
        $abandonedCartActiveActions = get_option('mailjet_wc_abandoned_cart_active_hooks');
        if ($activeActions && !empty($activeActions)){
            foreach ($activeActions as $action){
                $this->loader->add_action($action['hook'],$wooCommerceSettings, $action['callable'], 10, 2);
            }
        }
        if ($abandonedCartActiveActions && !empty($abandonedCartActiveActions)) {
            foreach ($abandonedCartActiveActions as $action) {
                $this->loader->add_action($action['hook'], $wooCommerceSettings, $action['callable'], 10, 2);
            }
        }
    }
}
