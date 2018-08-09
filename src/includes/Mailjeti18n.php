<?php

namespace MailjetPlugin\Includes;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      5.0.0
 * @package    Mailjet
 * @subpackage Mailjet/includes
 * @author     Your Name <email@example.com>
 */
class Mailjeti18n
{
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    5.0.0
	 */
	public function load_plugin_textdomain()
    {
		load_plugin_textdomain(
			'mailjet',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}
}
