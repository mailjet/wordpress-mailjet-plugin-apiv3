<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\MailjetApi;
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

        <label for="mailjet_apikey"><?php echo __('<b>Api Key</b>', 'mailjet'); ?></label>
        <br />
        <input name="mailjet_apikey" type="text" id="mailjet_apikey" value="<?=$mailjetApikey ?>" class="regular-text code" required="required" placeholder="<?php esc_html_e( 'Your Mailjet API Key', 'mailjet' ); ?>">
        <br /><br />

        <label for="mailjet_apisecret"><?php echo __('<b>Secret Key</b>', 'mailjet'); ?></label>
        <br />
        <input name="mailjet_apisecret" type="text" id="mailjet_apisecret" value="<?=$mailjetApiSecret ?>" class="regular-text code" required="required" placeholder="<?php esc_html_e( 'Your Mailjet API Secret', 'mailjet' ); ?>">

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
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }


        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_section_connect_account_settings',
            null,
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

            // Validate Mailjet API credentials
            $isValidAPICredentials = MailjetApi::isValidAPICredentials();
            if (false == $isValidAPICredentials) {
//                \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Invalid Mailjet API credentials ]');
                add_settings_error('mailjet_messages', 'mailjet_message', __('Invalid Mailjet API credentials', 'mailjet'), 'error');
            } else {
//            \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Initial settings form submitted ]');

                // Initialize PhpMailer
                //
                if (!is_object($phpmailer) || !is_a($phpmailer, 'PHPMailer')) {
                    require_once ABSPATH . WPINC . '/class-phpmailer.php';
                    require_once ABSPATH . WPINC . '/class-smtp.php';
                    $phpmailer = new \PHPMailer();
//                \MailjetPlugin\Includes\MailjetLogger::warning('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ PHPMailer initialized by the Mailjet plugin ]');
                }

                // Update From Email and Name
                add_filter('wp_mail_from', array(new MailjetMail(), 'wp_sender_email'));
                add_filter('wp_mail_from_name', array(new MailjetMail(), 'wp_sender_name'));

                // add settings saved message with the class of "updated"
                add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet'), 'updated');
//            \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Initial settings saved successfully ]');
            }
        }

        // show error/update messages
        settings_errors('mailjet_messages');

        ?>

        <div class="mainContainerSettings">
            <div class="left"">
                <div class="centered">
                    <?php
                    MailjetAdminDisplay::getSettingsLeftMenu();
                    ?>
                </div>
            </div>

            <div class="right"">
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
                            submit_button('Save', 'MailjetSubmit', 'submit', false, array('id' => 'connectAccountSubmit'));
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom_links">
            <div class="needHelpDiv">
                <img src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/need_help.png'; ?>" alt="<?php echo __('Connect your Mailjet account', 'mailjet'); ?>" />
                <?php echo __('Need help?', 'mailjet' ); ?>
            </div>
            <?php echo '<a target="_blank" href="https://www.mailjet.com/guides/wordpress-user-guide/">' . __('Read our user guide', 'mailjet') . '</a>'; ?>
            &nbsp;&nbsp;&nbsp;
            <?php echo '<a target="_blank" href="https://www.mailjet.com/support/ticket">' . __('Contact our support team', 'mailjet') . '</a>'; ?>
        </div>


        <?php
    }


}
