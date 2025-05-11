<?php

namespace MailjetWp\MailjetPlugin\Includes;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      5.0.0
 * @package    Mailjet
 * @subpackage Mailjet/includes
 */
class MailjetDeactivator {

    /**
     * @return void
     */
    public function deactivate() {
        $timestamp = wp_next_scheduled('bl_cron_hook');
        wp_unschedule_event($timestamp, 'bl_cron_hook');
        update_option('mailjet_woo_abandoned_cart_activate', 0);
    }
}
