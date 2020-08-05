<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\MailjetLogger;
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
    {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php echo __('Enable or disable the sending of your emails through your Mailjet account', 'mailjet-for-wordpress' ); ?>
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
        $mailjet_from_email_extra = get_option('mailjet_from_email_extra');

        $mailjetSenders = MailjetApi::getMailjetSenders();
        $mailjetSenders = !empty($mailjetSenders) ? $mailjetSenders : array();

        // output the field
        ?>

        <fieldset class="settingsSendingFldset">
            <label class="checkboxLabel" for="mailjet_enabled">
                <input name="mailjet_enabled" type="checkbox" id="mailjet_enabled" value="1" <?php echo ($mailjetEnabled == 1 ? ' checked="checked"' : '') ?> autocomplete="off">
                <span><?php _e('Enable sending emails through <b>Mailjet</b>', 'mailjet-for-wordpress'); ?></span>
            </label>
            <div id="enable_mj_emails" class="sending_options_div <?php echo $mailjetEnabled ? 'mj-show' : 'mj-hide' ?>">
                <div>
                    <label class="mj-label" for="mailjet_from_name"><b><?php _e('From: Name', 'mailjet-for-wordpress'); ?></b></label>
                    <input name="mailjet_from_name" type="text" id="mailjet_from_name" value="<?php echo $mailjetFromName ?>" class="regular-text code">
                </div>
                <div id="mailjet_from_email_fields" class="fromFld">
                    <label class="mj-label" for="mailjet_from_email"><b><?php _e('From: name@email.com', 'mailjet-for-wordpress'); ?></b></label>
                    <div class="fromFldGroup">
                        <select class="mj-select" name="mailjet_from_email" id="mailjet_from_email" type="select" style="display: inline;">
                        <?php foreach ($mailjetSenders as $mailjetSender) {
                            if ($mailjetSender['Status'] != 'Active') {
                                continue;
                            }
                            if (!empty($mailjet_from_email_extra)) {
                                if (stristr($mailjetSender['Email'],'*') && stristr($mailjetFromEmail, str_ireplace('*', '', $mailjetSender['Email']))) {
                                    $mailjetFromEmail = $mailjetSender['Email'];
                                }
                            }
                        ?>
                            <option value="<?=$mailjetSender['Email'] ?>" <?=($mailjetFromEmail == $mailjetSender['Email'] ? 'selected="selected"' : '') ?> > <?=$mailjetSender['Email'] ?> </option>
                        <?php } ?>
                        </select>
                    </div>
                </div>
                <?php
                    if (!empty($mailjet_from_email_extra)) { ?>
                        <input name="mailjet_from_email_extra_hidden" type="hidden" id="mailjet_from_email_extra_hidden" value="<?php _e($mailjet_from_email_extra) ?>">
                <?php } ?>
                <div class="smtpFld">
                    <label class="mj-label" for="mailjet_port"><b><?php _e('Port to use for SMTP communication', 'mailjet-for-wordpress'); ?></b></label>
                    <select class="mj-select" name="mailjet_port" id="mailjet_port" type="select">
                        <option value="25" <?=($mailjetPort == 25 ? 'selected="selected"' : '') ?> > 25 </option>
                        <option value="465" <?=($mailjetPort == 465 ? 'selected="selected"' : '') ?> > 465 </option>
                        <option value="587" <?=($mailjetPort == 587 ? 'selected="selected"' : '') ?> > 587 </option>
                        <option value="588" <?=($mailjetPort == 588 ? 'selected="selected"' : '') ?> > 588 </option>
                        <option value="80" <?=($mailjetPort == 80 ? 'selected="selected"' : '') ?> > 80 </option>
                    </select>
                </div>
                <div class="sslFld">
                    <label class="checkboxLabel" for="mailjet_ssl">
                        <input name="mailjet_ssl"  type="checkbox" id="mailjet_ssl" value="ssl" <?=($mailjetSsl == 'ssl' || $mailjetSsl == 'tls' ? ' checked="checked"' : '') ?> autocomplete="off">
                        <span><?php echo __('Enable SSL communication with mailjet.com (only available with port 465)', 'mailjet-for-wordpress'); ?></span>
                    </label>
                </div>
                <div id="testEmail">
                    <button type="button" id="mailjet_test" class="sendTestEmailBtn mj-toggleBtn" data-target="test_email_collapsible"><?php _e('Send a test', 'mailjet-for-wordpress')?></button>
                    <div id="test_email_collapsible" class="mj-hide test_email_collapsible">
                        <label class="mj-label" for="mailjet_test_address"><b><?php _e('Recipient of the test email', 'mailjet-for-wordpress'); ?></b></label>
                        <div class="test_email_fields_group">
                            <input type="text" size="30" name="mailjet_test_address" id="mailjet_test_address" />
                            <input type="submit" value="<?php _e('Send', 'mailjet-for-wordpress')?>" name="send_test_email_btn" class="mj-btn btnSecondary MailjetSubmit" id="send_test_email_btn"/>
                        </div>
                    </div>
                </div>
            </div>

            <input name="settings_step" type="hidden" id="settings_step" value="enable_sending_step">
        </fieldset>
        <?php
    }


    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_sending_settings_page_html()
    {
        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_enable_sending_settings',
            null,
            array($this, 'mailjet_section_enable_sending_cb'),
            'mailjet_sending_settings_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_enable_sending', // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __('Enable sending emails through Mailjet', 'mailjet-for-wordpress' ),
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
        if (!current_user_can('read')) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        // add error/update messages

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {

            $executionError = false;
            $testSent = false;

            // If whitelisted domain is selected then we add the extra email name to that domain
            $mailjet_from_email_extra = get_option('mailjet_from_email_extra');
            if (!empty($mailjet_from_email_extra)) {
                update_option('mailjet_from_email', str_replace('*', '', $mailjet_from_email_extra.get_option('mailjet_from_email')));
            }

            // Update From Email and Name
            add_filter('wp_mail_from', array(new MailjetMail(), 'wp_sender_email'));
            add_filter('wp_mail_from_name', array(new MailjetMail(), 'wp_sender_name'));

            // Check connection with selected port and protocol
            if (false === $this->checkConnection()) {
                $executionError = true;
                add_settings_error('mailjet_messages', 'mailjet_message', __('Can not connect to Mailjet with the selected settings. Check if a firewall is blocking connections to the Mailjet ports.', 'mailjet-for-wordpress'), 'error');
            }
            
            $send_test_email_btn = get_option('send_test_email_btn');
            $mailjet_test_address = get_option('mailjet_test_address');
            if (!empty($send_test_email_btn) && empty($mailjet_test_address)) {
                $executionError = true;
                add_settings_error('mailjet_messages', 'mailjet_message', __('Please provide a valid email address', 'mailjet-for-wordpress'), 'error');
            } else if (!empty($send_test_email_btn) && !empty($mailjet_test_address)) {
                // Send a test email
                $testSent = MailjetMail::sendTestEmail();
                if (false === $testSent) {
                    //MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Your test message was NOT sent, please review your settings ]');
                    $executionError = true;
                    add_settings_error('mailjet_messages', 'mailjet_message', __('The test email could not be sent. Please make sure your server doesn\'t block the SMTP ports. Also double check that you are using correct API and Secret keys and a valid sending address from your Mailjet account.', 'mailjet-for-wordpress'), 'error');
                } else {
                    // MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Your test message was sent succesfully ]');
                    add_settings_error('mailjet_messages', 'mailjet_message', __('Your test email has been successfully sent', 'mailjet-for-wordpress'), 'updated');
                }
            }

            if (true !== $testSent && false === $executionError) {
                // add settings saved message with the class of "updated"
                add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet-for-wordpress'), 'updated');
            }
        }

        // show error/update messages
        settings_errors('mailjet_messages');

        ?>

        <div class="mj-pluginPage">
            <div id="initialSettingsHead"><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/LogoMJ_White_RVB.svg'; ?>" alt="Mailjet Logo" /></div>
            <div class="mainContainer">
                
            <div class="backToDashboard">
                    <a class="mj-btn btnCancel" href="admin.php?page=mailjet_dashboard_page">
                    <svg width="8" height="8" viewBox="0 0 16 16"><path d="M7.89 11.047L4.933 7.881H16V5.119H4.934l2.955-3.166L6.067 0 0 6.5 6.067 13z"/></svg>
                    <?php _e('Back to dashboard', 'mailjet-for-wordpress') ?>
                    </a>
                </div>

                <h1 class="page_top_title"><?php _e('Settings', 'mailjet-for-wordpress') ?></h1>
                <div class="mjSettings">
                    <div class="left">
                        <?php
                        MailjetAdminDisplay::getSettingsLeftMenu();
                        ?>
                    </div>

                    <div class="right">
                        <div class="centered"  style="width:650px;">
                            <!--                    <h1>--><?php //echo esc_html(get_admin_page_title());  ?><!--</h1>-->
                            <h2 class="section_inner_title"><?php _e('Sending settings', 'mailjet-for-wordpress'); ?></h2>
                            <form action="options.php" method="post">
                                <?php
                                // output security fields for the registered setting "mailjet"
                                settings_fields('mailjet_sending_settings_page');
                                // output setting sections and their fields
                                // (sections are registered for "mailjet", each field is registered to a specific section)
                                do_settings_sections('mailjet_sending_settings_page');
                                // output save settings button
                                $saveButton = __('Save', 'mailjet-for-wordpress');
                                ?>
                                <button type="submit" id="enableSendingSubmit" class="mj-btn btnPrimary MailjetSubmit" name="submit"><?= $saveButton; ?></button>
                                <!-- <input name="cancelBtn" class="mj-btn btnCancel" type="button" id="cancelBtn" onClick="location.href=location.href" value="<?= __('Cancel', 'mailjet-for-wordpress') ?>"> -->
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php
                MailjetAdminDisplay::renderBottomLinks();
            ?>
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
        $encryption = '';
        if (get_option('mailjet_ssl') && get_option('mailjet_port') == 465) {
            $encryption = 'ssl';
            $protocol = 'ssl://';
        } else if (get_option('mailjet_ssl')) {
            $protocol = 'tls://';
            $encryption = 'tls';
        }
        if ($encryption == 'ssl' || $encryption == '') {
            $soc = @fsockopen($protocol . MailjetMail::MJ_HOST, get_option('mailjet_port'), $errno, $errstr, 5);
        } else if ($encryption == 'tls') {
            $remote_socket = MailjetMail::MJ_HOST.":587";
            $soc = @stream_socket_client($remote_socket, $errno, $errstr);
            @stream_socket_enable_crypto($soc, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        }

        if ($soc) {
            $connected = TRUE;
            $port = get_option('mailjet_port');
            $ssl = get_option('mailjet_ssl');
        } else {
            for ($i = 0; $i < count($configs); ++$i) {
                if ($configs[$i][0]) {
                    $protocol = $configs[$i][0] . '://';
                } else {
                    $protocol = '';
                }

                $soc = @fsockopen($protocol . MailjetMail::MJ_HOST, $configs[$i][1], $errno, $errstr, 5);
                if ($soc) {
                    fclose($soc);
                    $connected = $i;
                    $port = $configs[$i][1];
                    $ssl = $configs[$i][0];
                    update_option('mailjet_ssl', $ssl);
                    update_option('mailjet_port', $port);
                    add_settings_error('mailjet_messages', 'mailjet_message', __('Your settings have been saved, but your port and SSL settings were changed to ensure delivery', 'mailjet-for-wordpress'), 'updated');
                    break;
                }
            }
        }

        return $connected;
    }

}
