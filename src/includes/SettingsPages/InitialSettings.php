<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\MailjetMail;
use MailjetPlugin\Includes\MailjetSettings;
use MailjetPlugin\Includes\MailjetLogger;
use PHPMailer\PHPMailer\PHPMailer;

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
        <p class="top_descrption_helper" id="<?php echo esc_attr($args['id']); ?>">
        <?php echo __('If you already have a Mailjet account, go to <a class="greenLink" target="_blank" href="https://app.mailjet.com/account/api_keys">My Account > API Keys</a> and paste your credentials below', 'mailjet-for-wordpress'); ?>
        </p>
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
            <legend class="screen-reader-text"><span><b><?php _e('Connect your Mailjet account to get started', 'mailjet-for-wordpress'); ?></b></span></legend>

            <input name="settings_step" type="hidden" id="settings_step" value="initial_step">

            <label class="mj-label" for="mailjet_apikey"><?php _e('<b>Api Key</b>', 'mailjet-for-wordpress'); ?></label>
            <input name="mailjet_apikey" type="text" id="mailjet_apikey" value="<?= $mailjetApikey ?>" class="mailjet_apikey" required="required">
            <label class="mj-label" for="mailjet_apisecret"><?php _e('<b>Secret Key</b>', 'mailjet-for-wordpress'); ?></label>
            <input name="mailjet_apisecret" type="text" id="mailjet_apisecret" value="<?= $mailjetApiSecret ?>" class="mailjet_apisecret" required="required">
        </fieldset>

        <!--        <br />-->
        <!--        <label for="mailjet_activate_logger">-->
        <!--            <input name="mailjet_activate_logger" type="checkbox" id="mailjet_activate_logger" value="1" --><?//=($mailjetActivateLogger == 1 ? ' checked="checked"' : '') ?><!-- >-->
        <!--            --><?php //echo __('Also activate Mailjet plugin logger, to track your expirience', 'mailjet-for-wordpress');  ?><!--</label>-->
        <!--        <br />-->
        <?php
    }

    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_initial_settings_page_html()
    {
        $fromPage = !empty($_REQUEST['from']) ? $_REQUEST['from'] : null;

        // check user capabilities
        if (!current_user_can('read')) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }


        // register a new section in the "mailjet" page
        add_settings_section(
                'mailjet_section_initial_settings', null, array($this, 'mailjet_section_initial_settings_cb'), 'mailjet_initial_settings_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
                'mailjet_initial_settings', // as of WP 4.6 this value is used only internally
                // use $args' label_for to populate the id inside the callback
                __('Mailjet API credentials', 'mailjet-for-wordpress'), array($this, 'mailjet_initial_settings_cb'), 'mailjet_initial_settings_page', 'mailjet_section_initial_settings', [
            'label_for' => 'initial_settings',
            'class' => 'mailjet_row',
            'mailjet_custom_data' => 'custom',
                ]
        );


        // add error/update messages
        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {

            $executionError = false;

            // Validate Mailjet API credentials
            $isValidAPICredentials = MailjetApi::isValidAPICredentials();
            if (false == $isValidAPICredentials) {
                update_option('api_credentials_ok', 0);
                $executionError = true;
//                MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Invalid Mailjet API credentials ]');
                add_settings_error('mailjet_messages', 'mailjet_message', __('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/api_keys">https://app.mailjet.com/account/api_keys</a>', 'mailjet-for-wordpress'), 'error');
            } else {
//            MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Initial settings form submitted ]');

                // Update From Email and Name
                add_filter('wp_mail_from', array(new MailjetMail(), 'wp_sender_email'));
                add_filter('wp_mail_from_name', array(new MailjetMail(), 'wp_sender_name'));

                // add settings saved message with the class of "updated"
                add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet-for-wordpress'), 'updated');
                $executionError = false;

                // Automatically redirect to the next step - we use javascript to prevent the WP issue when using `wp_redirect` method and headers already sent
                if (false === $executionError) {
                    // Update the flag for passed API credentials check
                    update_option('api_credentials_ok', 1);
                    // Redirect to the next page
                    MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_initial_contact_lists_page' . (!empty($_REQUEST['from']) ? '&from=' . $_REQUEST['from'] : '')));
                }

                //MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Initial settings saved successfully ]');
            }
        }
        $api_credentials_ok = get_option('api_credentials_ok');
        if (!($fromPage == 'plugins') && (!empty($api_credentials_ok) && '1' == $api_credentials_ok)) {
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_initial_contact_lists_page'));
        }

        //// show error/update messages
        settings_errors('mailjet_messages');
        ?>
        <div class="mj-pluginPage">
            <div id="initialSettingsHead"><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/LogoMJ_White_RVB.svg'; ?>" alt="Mailjet Logo" /></div>
            <div class="mainContainer">

                <div>
                    <h1 class="page_top_title"><?php _e('Welcome to the Mailjet plugin for WordPress', 'mailjet-for-wordpress'); ?> </h1>
                    <p class="page_top_subtitle">
        <?php _e('Mailjet is an email service provider. With this plugin, easily send newsletters to your website users, directly from WordPress.', 'mailjet-for-wordpress'); ?>
                    </p>
                </div>
                <div class="initialSettings">
                    <div id="initialSettingsForm">
                        <h2 class="section_inner_title"><?php echo esc_html(get_admin_page_title()); ?></h2>
                        <form action="options.php" method="post">
        <?php
        // output security fields for the registered setting "mailjet"
        settings_fields('mailjet_initial_settings_page');
        // output setting sections and their fields
        // (sections are registered for "mailjet", each field is registered to a specific section)
        do_settings_sections('mailjet_initial_settings_page');
        // output save settings button
        $connectYourAccount = __('Connect your account', 'mailjet-for-wordpress');
        /* No Next btn on Initial API settings page - we redirect automatically
          if (MailjetApi::isValidAPICredentials() && get_option('settings_step') == 'initial_step') { ?>
          <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" style="width: 311px;" onclick="location.href = 'admin.php?page=mailjet_initial_contact_lists_page<?php echo !empty($_REQUEST['from']) ? '&from='.$_REQUEST['from'] : null; ?>'" value="<?=__('Next', 'mailjet-for-wordpress')?>">
          <?php }
         */
        ?>
        <button type="submit" id="initialSettingsSubmit" class="mj-btn btnPrimary" name="submit"><?= $connectYourAccount; ?></button>
                            <p class="dont_have_account">
                            <?php esc_html_e('You don\'t have a Mailjet account yet?', 'mailjet-for-wordpress'); ?>
                                <br />
                            <?php echo sprintf('<a class="greenLink" target="_blank" href="https://www.mailjet.com/signup?aff=%s">', 'wordpress-3.0') . __('Create an account', 'mailjet-for-wordpress') . '</a>'; ?>
                            </p>
                        </form>
                    </div>

                    <div id="initialSettingsDescription">
                        <div class="availableContactListsContainer">
                            <div class="initialSettingsDescriptionRow">
                                <div class="initialSettingsImageCell"><img width="96" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/initial_screen_image1.png'; ?>" alt="Welcome to the Mailjet" /></div>
                                <p class="initialSettingsTextCell"><b><?php _e('Collect email addresses...', 'mailjet-for-wordpress'); ?></b><?php _e('Email addresses are collected from your website', 'mailjet-for-wordpress'); ?></p>
                            </div>
                            <div class="initialSettingsDescriptionRow">
                                <div class="initialSettingsImageCell"><img width="96" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/initial_screen_image2.png'; ?>" alt="Welcome to the Mailjet" /></div>
                                <p class="initialSettingsTextCell"><b><?php _e('...and add them automatically to a contact list', 'mailjet-for-wordpress'); ?></b><?php _e('Email are added to your contact list', 'mailjet-for-wordpress'); ?></p>
                            </div>
                            <div class="initialSettingsDescriptionRow">
                                <div class="initialSettingsImageCell"><img width="96" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/initial_screen_image3.png'; ?>" alt="Welcome to the Mailjet" /></div>
                                <p class="initialSettingsTextCell"><b><?php _e('We will take care of delivering your newsletter', 'mailjet-for-wordpress'); ?></b><?php _e('Easily create and send newsletters to your subscribers from WordPress. Mailjet will deliver them!', 'mailjet-for-wordpress'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--        <br style="clear: left;"/>-->
            <?php
                 MailjetAdminDisplay::renderBottomLinks();
            ?>
        </div>

        <?php
    }

}
