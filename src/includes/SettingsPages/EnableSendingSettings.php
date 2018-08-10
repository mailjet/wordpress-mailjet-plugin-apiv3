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
class EnableSendingSettings
{
    public function mailjet_section_enable_sending_cb($args)
    {echo get_option('settings_step');
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php echo __('Enable or disable the sending of your emails through your Mailjet account', 'mailjet' ); ?>
        </p>
        <?php
    }


    public function mailjet_enable_sending_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $mailjetEnabled = get_option('mailjet_enabled');
        $mailjetFromName = get_option('mailjet_from_name');
        $mailjetFromEmail = get_option('mailjet_from_email');
        $mailjetPort = get_option('mailjet_port');
        $mailjetSsl = get_option('mailjet_ssl');

        $mailjetSenders = $this->getMailjetSenders();
        // output the field
        ?>

        <fieldset>
            <input name="mailjet_enabled" type="checkbox" id="mailjet_enabled" value="1" <?=($mailjetEnabled == 1 ? ' checked="checked"' : '') ?> >
            <label for="mailjet_enabled"> <?php echo __('Enable sending emails through <b>Mailjet</b>', 'mailjet'); ?></label>
            <br />

            <div class="sending_options_div">
                <label for="mailjet_from_name"><?php echo __('From: name', 'mailjet'); ?></label> <br />
                <input name="mailjet_from_name" type="text" id="mailjet_from_name" value="<?=$mailjetFromName ?>" class="regular-text code" required="required" placeholder="<?php esc_html_e( 'e.g. Jenny Ford', 'mailjet' ); ?>">
                <br />

                <label for="mailjet_from_email"><?php echo __('From: email address', 'mailjet'); ?></label> <br />
                <select name="mailjet_from_email" id="mailjet_from_email" type="select">
                    <?php foreach ($mailjetSenders as $mailjetSender) {
                        if ($mailjetSender['Status'] != 'Active') {
                            continue;
                        }
                        if (!empty(get_option('mailjet_from_email_extra'))) {
                            if (stristr($mailjetSender['Email'],'*') && stristr(get_option('mailjet_from_email'), str_ireplace('*', '', $mailjetSender['Email']))) {
                                $mailjetFromEmail = $mailjetSender['Email'];
                            }
                        }
                    ?>
                        <option value="<?=$mailjetSender['Email'] ?>" <?=($mailjetFromEmail == $mailjetSender['Email'] ? 'selected="selected"' : '') ?> > <?=$mailjetSender['Email'] ?> </option>
                    <?php
                    } ?>
                </select>
                <?php
                    if (!empty(get_option('mailjet_from_email_extra'))) { ?>

                        <input name="mailjet_from_email_extra_hidden" type="hidden" id="mailjet_from_email_extra_hidden" value="<?=get_option('mailjet_from_email_extra') ?>">
                <?php

                    }
                ?>

                <br />

                <label for="mailjet_port"><?php echo __('Port to use for SMTP communication', 'mailjet'); ?></label>
                <select name="mailjet_port" id="mailjet_port" type="select">
                    <option value="25" <?=($mailjetPort == 25 ? 'selected="selected"' : '') ?> > 25 </option>
                    <option value="465" <?=($mailjetPort == 465 ? 'selected="selected"' : '') ?> > 465 </option>
                    <option value="587" <?=($mailjetPort == 587 ? 'selected="selected"' : '') ?> > 587 </option>
                    <option value="588" <?=($mailjetPort == 588 ? 'selected="selected"' : '') ?> > 588 </option>
                    <option value="80" <?=($mailjetPort == 80 ? 'selected="selected"' : '') ?> > 80 </option>
                </select>
                <br />

                <input name="mailjet_ssl" type="checkbox" id="mailjet_ssl" value="ssl" <?=($mailjetSsl == 'ssl' ? ' checked="checked"' : '') ?> >
                <label for="mailjet_ssl"> <?php echo __('Enable SSL communication with mailjet.com (only available with port 465)', 'mailjet'); ?></label>
                <br />

                <?php  if (!empty(get_option('mailjet_enabled')) && 1 == get_option('mailjet_enabled')) { ?>
                    <div class="test_email_popup pop">
                        <p><label for="email">Recipient of the test email</label><input type="text" size="30" name="mailjet_test_address" id="mailjet_test_address" /></p>
                        <p><input type="submit" value="Send" name="send_test_email_btn" id="send_test_email_btn"/> or <a class="close" href="/">Cancel</a></p>
                    </div>
                    <input name="mailjet_test" type="button" id="mailjet_test" value="Send a test">
                    <br />
                <?php } ?>
            </div>

            <input name="settings_step" type="hidden" id="settings_step" value="enable_sending_step">
        </fieldset>
        <?php
    }


    private function getMailjetSenders()
    {
        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        $mjApiClient = new \Mailjet\Client($mailjetApikey, $mailjetApiSecret);

        $responseSenders = $mjApiClient->get(\Mailjet\Resources::$Sender);
        if ($responseSenders->success()) {
            return $responseSenders->getData();
        } else {
            return $responseSenders->getStatus();
        }

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

            // If whitelisted domain is selected then we add the extra email name to that domain
            if (!empty(get_option('mailjet_from_email_extra'))) {
                update_option('mailjet_from_email', str_replace('*', '',get_option('mailjet_from_email_extra').get_option('mailjet_from_email')));
            }

            // Update From Email and Name
            add_filter('wp_mail_from', array(new MailjetMail(), 'wp_sender_email'));
            add_filter('wp_mail_from_name', array(new MailjetMail(), 'wp_sender_name'));

            // Check connection with selected port and protocol
            if (false === $this->checkConnection()) {
                add_settings_error('mailjet_messages', 'mailjet_message', __('Can not connect to Mailjet with the selected settings.', 'mailjet'), 'error');
            }

            if (!empty(get_option('send_test_email_btn')) && empty(get_option('mailjet_test_address'))) {
                add_settings_error('mailjet_messages', 'mailjet_message', __('You have to provide a valid email address to send test email to', 'mailjet'), 'error');
            } else if (!empty(get_option('send_test_email_btn')) && !empty(get_option('mailjet_test_address'))) {
                // Send a test email
                MailjetMail::sendTestEmail();
            }

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
                        settings_fields('mailjet_sending_settings_page');
                        // output setting sections and their fields
                        // (sections are registered for "mailjet", each field is registered to a specific section)
                        do_settings_sections('mailjet_sending_settings_page');
                        // output save settings button
                        submit_button('Save');
                        ?>
                    </form>
                </div>
            </div>
        </div>

        <?php

    }

    private function checkConnection()
    {
        // Check if there is a connection with the Mailjet's server
        $configs = array(
            array('', 25),
            array('tls', 25),
            array('ssl', 465),
            array('tls', 587),
            array('', 587),
            array('', 588),
            array('', 80),
        );

        $connected = FALSE;
        $protocol = '';
        if (get_option('mailjet_ssl')) {
            $protocol = 'ssl://';
        }

        $soc = @fsockopen($protocol . MailjetMail::MJ_HOST, get_option('mailjet_port'), $errno, $errstr, 5);

        if ($soc) {
            $connected = TRUE;
            $port = get_option('mailjet_port');
            $ssl = get_option('mailjet_ssl');
        } else {
            for ($i = 0; $i < count($configs); ++$i) {
                if ($configs[$i][0])
                    $protocol = $configs[$i][0] . '://';
                else
                    $protocol = '';

                $soc = @fsockopen($protocol . MailjetMail::MJ_HOST, $configs[$i][1], $errno, $errstr, 5);
                if ($soc) {
                    fclose($soc);
                    $connected = $i;
                    $port = $configs[$i][1];
                    $ssl = $configs[$i][0];
                    update_option('mailjet_ssl', $ssl);
                    update_option('mailjet_port', $port);
                    add_settings_error('mailjet_messages', 'mailjet_message', __('Your settings have been saved, but your port and SSL settings were changed as follows to ensure delivery', 'mailjet'), 'updated');
                    break;
                }
            }
        }

        return $connected;
    }

}
