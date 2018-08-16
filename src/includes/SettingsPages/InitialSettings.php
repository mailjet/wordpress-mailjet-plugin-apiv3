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
class InitialSettings
{

    /**
     * custom option and settings:
     * callback functions
     */

    // developers section cb

    // section callbacks can accept an $args parameter, which is an array.
    // $args have the following keys defined: title, id, callback.
    // the values are defined at the add_settings_section() function.
    public function mailjet_section_initial_settings_cb($args)
    {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'mailjet' ); ?></p>
        <?php
    }

    // pill field cb
    // field callbacks can accept an $args parameter, which is an array.
    // $args is defined at the add_settings_field() function.
    // wordpress has magic interaction with the following keys: label_for, class.
    // the "label_for" key value is used for the "for" attribute of the <label>.
    // the "class" key value is used for the "class" attribute of the <tr> containing the field.
    // you can add custom key value pairs to be used inside your callbacks.
    public function mailjet_initial_settings_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        $mailjetActivateLogger = get_option('mailjet_activate_logger');

        // output the field
        ?>
        <fieldset>
            <legend class="screen-reader-text"><span><b><?php echo  __('Connect your Mailjet account to get started', 'mailjet'); ?></b></span></legend>

            <input name="settings_step" type="hidden" id="settings_step" value="initial_step">

            <label for="mailjet_apikey"><?php echo __('<b>ApiKey</b>', 'mailjet'); ?></label>
            <input name="mailjet_apikey" type="text" id="mailjet_apikey" value="<?=$mailjetApikey ?>" class="regular-text code" required="required" placeholder="<?php esc_html_e( 'Your Mailjet API Key', 'mailjet' ); ?>">

            <p class="description">
                <?php esc_html_e('Enter your Mailjet accoutn API Key value','mailjet'); ?>
            </p>

            <label for="mailjet_apisecret"><?php echo __('<b>ApiSecret</b>', 'mailjet'); ?></label>
                <input name="mailjet_apisecret" type="text" id="mailjet_apisecret" value="<?=$mailjetApiSecret ?>" class="regular-text code" required="required" placeholder="<?php esc_html_e( 'Your Mailjet API Secret', 'mailjet' ); ?>">

            <p class="description">
                <?php esc_html_e('Enter your Mailjet accoutn API Secret value','mailjet'); ?>
            </p>
        </fieldset>

        <hr>
        <label for="mailjet_activate_logger">
            <input name="mailjet_activate_logger" type="checkbox" id="mailjet_activate_logger" value="1" <?=($mailjetActivateLogger == 1 ? ' checked="checked"' : '') ?> >
            <?php echo __('Also activate Mailjet plugin logger, to track your expirience', 'mailjet'); ?></label>
        <br />

        <hr>
        <p>
            <h2><?php esc_html_e('Dont have a Mailjet account yet?' , 'mailjet'); ?></h2>
            <?php echo sprintf('<a target="_blank" href="https://www.mailjet.com/signup?aff=%s">', 'wordpress-3.0') . __('Create an account', 'mailjet') . '</a>'; ?>
        </p>

        <?php
    }


    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_initial_settings_page_html()
    {
        global $phpmailer;

        // check user capabilities
        if (!current_user_can('manage_options')) {
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }



        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_section_initial_settings',
            __( 'Connect your Mailjet account to get started', 'mailjet' ),
            array($this, 'mailjet_section_initial_settings_cb'),
            'mailjet_initial_settings_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_initial_settings', // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __( 'Mailjet API credentials', 'mailjet' ),
            array($this, 'mailjet_initial_settings_cb'),
            'mailjet_initial_settings_page',
            'mailjet_section_initial_settings',
            [
                'label_for' => 'initial_settings',
                'class' => 'mailjet_row',
                'mailjet_custom_data' => 'custom',
            ]
        );


        // add error/update messages

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {

            \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Initial settings form submitted ]');
            // Initialize PhpMailer
            //
            if (!is_object($phpmailer) || !is_a($phpmailer, 'PHPMailer')) {
                require_once ABSPATH . WPINC . '/class-phpmailer.php';
                require_once ABSPATH . WPINC . '/class-smtp.php';
                $phpmailer = new \PHPMailer();
                \MailjetPlugin\Includes\MailjetLogger::warning('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ PHPMailer initialized by the Mailjet plugin ]');
            }

            // Update From Email and Name
            add_filter('wp_mail_from', array(new MailjetMail(), 'wp_sender_email'));
            add_filter('wp_mail_from_name', array(new MailjetMail(), 'wp_sender_name'));

            // add settings saved message with the class of "updated"
            add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet'), 'updated');
            \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Initial settings saved successfully ]');
        }

        //// show error/update messages
        settings_errors('mailjet_messages');

        ?>

        <div class="split left"">
            <div class="centered">
                <h1>Jane Flex</h1>
                <p>Some text.</p>
                <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/mj_logo_med.png'; ?>" alt="Welcome to the Mailjet" />
            </div>
        </div>

        <div class="split right"">
            <div class="centered">
                <div class="wrap">
                    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                    <form action="options.php" method="post">
                        <?php
                        // output security fields for the registered setting "mailjet"
                        settings_fields('mailjet_initial_settings_page');
                        // output setting sections and their fields
                        // (sections are registered for "mailjet", each field is registered to a specific section)
                        do_settings_sections('mailjet_initial_settings_page');
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
