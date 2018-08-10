<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\MailjetMail;
use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;

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
class ConnectAccountSettings
{
    public function mailjet_section_connect_account_cb($args)
    {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php echo __('In your Mailjet account, go to <a target="_blank" href="https://www.mailjet.com/account/api_keys">My Account > API Keys</a> and paste your credentials bellow', 'mailjet'); ?>
        </p>
        <?php
    }


    public function mailjet_connect_account_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');

        // output the field
        ?>
        <input name="settings_step" type="hidden" id="settings_step" value="initial_step">

        <input name="mailjet_apikey" type="text" id="mailjet_apikey" value="<?=$mailjetApikey ?>" class="regular-text code" required="required" placeholder="<?php esc_html_e( 'Your Mailjet API Key', 'mailjet' ); ?>">
        <p class="description">
            <?php esc_html_e('Enter your Mailjet accoutn API Key value','mailjet'); ?>
        </p>
        <input name="mailjet_apisecret" type="text" id="mailjet_apisecret" value="<?=$mailjetApiSecret ?>" class="regular-text code" required="required" placeholder="<?php esc_html_e( 'Your Mailjet API Secret', 'mailjet' ); ?>">
        <p class="description">
            <?php esc_html_e('Enter your Mailjet accoutn API Secret value','mailjet'); ?>
        </p>
        <?php
    }




    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_connect_account_page_html()
    {
        global $phpmailer;

        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }


        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_section_connect_account_settings',
            __( 'The Matrix has you.', 'mailjet' ),
            array($this, 'mailjet_section_connect_account_cb'),
            'mailjet_connect_account_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_connect_account_settings', // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __( 'Mailjet API credentials', 'mailjet' ),
            array($this, 'mailjet_connect_account_cb'),
            'mailjet_connect_account_page',
            'mailjet_section_connect_account_settings',
            [
                'label_for' => 'mailjet_connect_account_settings',
                'class' => 'mailjet_row',
                'mailjet_custom_data' => 'custom',
            ]
        );


        // add error/update messages

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {

            // Initialize PhpMailer
            //
            if (!is_object($phpmailer) || !is_a($phpmailer, 'PHPMailer')) {
                require_once ABSPATH . WPINC . '/class-phpmailer.php';
                require_once ABSPATH . WPINC . '/class-smtp.php';
                $phpmailer = new \PHPMailer();
            }

            // Update From Email and Name
            add_filter('wp_mail_from', array(new MailjetMail(), 'wp_sender_email'));
            add_filter('wp_mail_from_name', array(new MailjetMail(), 'wp_sender_name'));

            // add settings saved message with the class of "updated"
            add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet'), 'updated');
        }

        // show error/update messages
        settings_errors('mailjet_messages');

        ?>
        <div class="split left">
            <div class="centered">
                <?php
                    MailjetAdminDisplay::getSettingsLeftMenu();
                ?>
            </div>
        </div>

        <div class="split right">
            <div class="centered">
                <div class="wrap">
                    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                    <form action="options.php" method="post">
                        <?php
                        // output security fields for the registered setting "mailjet"
                        settings_fields('mailjet_connect_account_page');
                        // output setting sections and their fields
                        // (sections are registered for "mailjet", each field is registered to a specific section)
                        do_settings_sections('mailjet_connect_account_page');
                        // output save settings button
                        submit_button('Save');
                        ?>
                    </form>
                </div>
            </div>
        </div>


        <?php
    }


}
