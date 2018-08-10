<?php

namespace MailjetPlugin\Includes;

use MailjetPlugin\Includes\MailjetLoader;
use MailjetPlugin\Includes\Mailjeti18n;
use MailjetPlugin\Admin\MailjetAdmin;
use MailjetPlugin\Front\MailjetPublic;
use MailjetPlugin\Includes\MailjetMenu;
use MailjetPlugin\Includes\MailjetSettings;
use MailjetPlugin\Includes\MailjetMail;

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
		if ( defined( 'MAILJET_VERSION' ) ) {
			$this->version = MAILJET_VERSION;
		} else {
			$this->version = '5.0.0';
		}
		$this->plugin_name = 'mailjet';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
        $this->define_public_hooks();
        $this->addMailjetMenu();
        $this->addMailjetSettings();
        $this->addMailjetPHPMailer();

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
		$plugin_admin = new MailjetAdmin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
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

//        $this->loader->add_action('admin_init', $plugin_settings, 'mailjet_settings_init');
        $this->loader->add_action('init', $plugin_settings, 'mailjet_settings_init');
    }


    private function addMailjetPHPMailer()
    {
        $plugin_mails = new MailjetMail();

        $this->loader->add_action('phpmailer_init', $plugin_mails, 'phpmailer_init_smtp');
        $this->loader->add_action('wp_mail_failed', $plugin_mails, 'wp_mail_failed_cb');

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
}
