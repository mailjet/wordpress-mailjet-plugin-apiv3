<?php

namespace MailjetPlugin\Front;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mailjet
 * @subpackage Mailjet/public
 * @author     Your Name <email@example.com>
 */
class MailjetPublic
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    5.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    5.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    5.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version )
    {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    5.0.0
	 */
	public function enqueue_styles()
    {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mailjet_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mailjet_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

//		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mailjet-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    5.0.0
	 */
	public function enqueue_scripts()
    {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mailjet_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mailjet_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

//		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mailjet-public.js', array( 'jquery' ), $this->version, false );
        if (get_option('activate_mailjet_woo_integration') === '1' &&
            get_option('mailjet_woo_abandoned_cart_activate') === '1') {
            global $wp;
            $currentUrl = trim(home_url(add_query_arg(array(), $wp->request)), '/ ');
            // check current page is wc checkout page
            if ($currentUrl === trim(get_permalink(wc_get_page_id('checkout')), '/ ')) {
                wp_enqueue_script(
                    'woocommerce_capture_guest',
                    plugins_url('../front/js/woocommerce_capture_guest.js', __FILE__),
                    '',
                    '',
                    true
                );

                wp_localize_script(
                    'woocommerce_capture_guest',
                    'woocommerce_capture_guest_params',
                    array(
                        'ajax_url' => admin_url('admin-ajax.php')
                    )
                );
            }
        }
	}
}
