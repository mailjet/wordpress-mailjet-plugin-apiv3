<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\MailjetMail;

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
class EnableSendingSettings
{
    public function mailjet_section_enable_sending_cb($args)
    {echo get_option('settings_step');
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'mailjet' ); ?></p>
        <?php
    }


    public function mailjet_enable_sending_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $mailjetEnabled = get_option('mailjet_enabled');

        // output the field
        ?>

        <fieldset>
            <legend class="screen-reader-text"><span><?php echo  __('Enable email through <b>Mailjet</b>', 'mailjet'); ?></span></legend>
            <label for="mailjet_enabled">
                <input name="mailjet_enabled" type="checkbox" id="mailjet_enabled" value="1" <?=($mailjetEnabled == 1 ? ' checked="checked"' : '') ?> > <?php echo __('Enable email through <b>Mailjet</b>', 'mailjet'); ?></label>
        </fieldset>

        <input name="settings_step" type="hidden" id="settings_step" value="enable_sending_step">

        <?php
    }



    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_sending_settings_page_html()
    {
        global $phpmailer;

        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_enable_sending_settings',
            __( 'The Matrix has you.', 'mailjet' ),
            array($this, 'mailjet_section_enable_sending_cb'),
            'mailjet_sending_settings_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_enable_sending', // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __( 'Mailjet Enable Email Sending', 'mailjet' ),
            array($this, 'mailjet_enable_sending_cb'),
            'mailjet_sending_settings_page',
            'mailjet_enable_sending_settings',
            [
                'label_for' => 'mailjet_enable_sending',
                'class' => 'mailjet_row',
                'mailjet_custom_data' => 'custom',
            ]
        );


        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

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
            add_filter('wp_mail_from', array('MailjetMail', 'wp_sender_email'));
            add_filter('wp_mail_from_name', array('MailjetMail', 'wp_sender_name'));

            // add settings saved message with the class of "updated"
            add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet'), 'updated');
        }

        // show error/update messages
        settings_errors('mailjet_messages');

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting "mailjet"
                settings_fields('mailjet_sending_settings_page');
                // output setting sections and their fields
                // (sections are registered for "mailjet", each field is registered to a specific section)
                do_settings_sections('mailjet_sending_settings_page');
                // output save settings button
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }


}
