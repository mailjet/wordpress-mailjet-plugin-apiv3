<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\Mailjeti18n;
use MailjetPlugin\Includes\MailjetLogger;

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
class IntegrationsSettings
{

    public function mailjet_section_integrations_cb($args)
    {
//        ?>
<!--        <p id="--><?php //echo esc_attr( $args['id'] ); ?><!--">-->
<!--            --><?php //echo __('Select which Wordpress user roles (in addition to Administrator) will also have access to the Mailjet Plugin', 'mailjet-for-wordpress' ); ?>
<!--        </p>-->
<!--        --><?php
    }

    private function wooIntegration($mailjetContactLists)
    {
        $mailjetWooList = get_option('mailjet_woo_list');
        $mailjetWooSyncActivated = get_option('activate_mailjet_woo_sync');
        $mailjetWooIntegrationActivated = get_option('activate_mailjet_woo_integration');
        $wooCommerceNotInstalled = false;
        // One can also check for `if (defined('WC_VERSION')) { // WooCommerce installed }`
        if (!class_exists('WooCommerce')) { 
            delete_option('activate_mailjet_woo_integration');
            delete_option('activate_mailjet_woo_sync');
            delete_option('mailjet_woo_list');
            $wooCommerceNotInstalled = true;
        }
        ?>
        <fieldset class="settingsSubscrFldset">
                <legend style="font-weight: bold; padding: 10px 10px 10px 0;"><?php  _e('WooCommerce integration', 'mailjet'); ?></legend>

            <label class="checkboxLabel">
                <input name="activate_mailjet_woo_integration" type="checkbox" id="activate_mailjet_woo_integration" value="1" <?php echo ($mailjetWooIntegrationActivated == 1 ? ' checked="checked"' : '') ?>  <?php echo ($wooCommerceNotInstalled == true ? ' disabled="disabled"' : '') ?>  autocomplete="off">
                <span><?php _e('Enable WooCommerce integration', 'mailjet-for-wordpress'); ?></span>
            </label>

            <div id="activate_mailjet_woo_form" class="<?=($mailjetWooIntegrationActivated == 1 ? ' mj-show' : 'mj-hide') ?>">
                <label class="checkboxLabel">
                    <input name="activate_mailjet_woo_sync" type="checkbox" id="activate_mailjet_woo_sync" value="1" <?php echo ($mailjetWooSyncActivated == 1 ? ' checked="checked"' : '') ?> <?php echo ($wooCommerceNotInstalled == true ? ' disabled="disabled"' : '') ?> autocomplete="off">
                    <span><?php _e('Display "Subscribe to our newsletter" checkbox in the checkout page and add subscibers to this list', 'mailjet-for-wordpress'); ?></span>
                </label>

                <div id="woo_contact_list" class="<?php echo ($mailjetWooSyncActivated == 1 ? ' mj-show' : 'mj-hide') ?> mailjet_sync_woo_div">
                    <select class="mj-select" name="mailjet_woo_list" id="mailjet_woo_list" type="select" <?php echo ($wooCommerceNotInstalled == true ? ' disabled="disabled"' : '') ?>>
                        <?php
                        foreach ($mailjetContactLists as $mailjetContactList) {
                            if ($mailjetContactList["IsDeleted"] == true) {
                                continue;
                            }
                            ?>
                            <option value="<?=$mailjetContactList['ID'] ?>" <?=($mailjetWooList == $mailjetContactList['ID'] ? 'selected="selected"' : '') ?> > <?=$mailjetContactList['Name'] ?> (<?=$mailjetContactList['SubscriberCount'] ?>) </option>
                            <?php
                        } ?>
                    </select>
                </div>
            </div>
        </fieldset>
        <?php
    }

    public function mailjet_integrations_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $mailjetContactLists = MailjetApi::getMailjetContactLists();
        $mailjetContactLists = !empty($mailjetContactLists) ? $mailjetContactLists : array();
        $this->wooIntegration($mailjetContactLists);
        $this->cf7Integration($mailjetContactLists);
        ?><input name="settings_step" type="hidden" id="settings_step" value="integrations_step"><?php
    }

    private function cf7Integration($mailjetContactLists)
    {
        $mailjetCF7IntegrationActivated = get_option('activate_mailjet_cf7_integration');
        $mailjetCF7List = get_option('mailjet_cf7_list');
        $email = get_option('cf7_email');
        $from = get_option('cf7_fromname');
//        $mailjetCF7SyncActivated = get_option('activate_mailjet_cf7_sync');

        $isCF7Installed = class_exists('WPCF7') ? true : false;
        ?>
        <fieldset class="settingsSubscrFldset">
                <legend style="font-weight: bold; padding: 10px 10px 10px 0;"><?php  _e('Contact Form 7 integration', 'mailjet'); ?></legend>

            <label class="checkboxLabel">
                <input name="activate_mailjet_cf7_integration" type="checkbox" id="activate_mailjet_cf7_integration" value="1" <?php echo ($mailjetCF7IntegrationActivated == 1 ? ' checked="checked"' : '') ?>  <?php echo ($isCF7Installed === false ? ' disabled="disabled"' : '') ?>  autocomplete="off">
                <span><?php _e('Enable Contact Form 7 integration', 'mailjet-for-wordpress'); ?></span>
            </label>

<!--        <div id="activate_mailjet_cf7_form" class="<?=($mailjetCF7IntegrationActivated == 1 ? ' mj-show' : 'mj-hide') ?> >"-->
            <div id="activate_mailjet_cf7_form" >
<!--                <label class="checkboxLabel">
                    <input name="activate_mailjet_cf7_sync" type="checkbox" id="activate_mailjet_cf7_sync" value="1" <?php echo ($mailjetCF7IntegrationActivated == 1 ? ' checked="checked"' : '') ?> <?php echo ($isCF7Installed === false ? ' disabled="disabled"' : '') ?> autocomplete="off">
                    <span><?php _e('Display "Subscribe to our newsletter" checkbox in the checkout page and add subscibers to this list', 'mailjet-for-wordpress'); ?></span>
                </label>-->

                <!--<div id="woo_contact_list" class="<?php echo ($mailjetCF7IntegrationActivated == 1 ? ' mj-show' : 'mj-hide') ?> mailjet_sync_cf7_div">-->
                <div id="woo_contact_list" >
                    <label><?php _e('Mailjet list', 'mailjet') ?></label>
                    <select class="mj-select" name="mailjet_cf7_list" id="mailjet_cf7_list" type="select" <?php echo ($isCF7Installed === false ? ' disabled="disabled"' : '') ?>>
                        <option value="0"><?php _e('Select a list', 'mailjet') ?></option>
                        <?php
                        foreach ($mailjetContactLists as $mailjetContactList) {
                            if ($mailjetContactList["IsDeleted"] == true) {
                                continue;
                            }
                            ?>
                            <option value="<?=$mailjetContactList['ID'] ?>" <?=($mailjetCF7List == $mailjetContactList['ID'] ? 'selected="selected"' : '') ?> > <?=$mailjetContactList['Name'] ?> (<?=$mailjetContactList['SubscriberCount'] ?>) </option>
                            <?php
                        } ?>
                    </select>
                </div>
                <div>
                    <label><?php _e('Email field tag', 'mailjet') ?></label>
                    <input name="cf7_email" id="cf7_email" value="<?php echo $email ?>" placeholder="<?php _e('i.e. [your-email]', 'mailjet') ?>" class="widefat" />
                </div>
                <div>
                    <label><?php _e('Name field tag (optional)', 'mailjet') ?></label>
                    <input name="cf7_fromname" id="cf7_fromname" value="<?php echo $from ?>" placeholder="<?php _e('i.e. [your-name]', 'mailjet') ?>" class="widefat" />
                </div>
                <div>
                    <div><span><?php _e('To enable the integration, include the following shortcode to your contact form:', 'mailjet') ?></span></div>
                    <div>
                        <input name="cf7_contact_properties" id="cf7_contact_properties" value='[checkbox mailjet-opt-in default:0 class:cf7_mailjet_checkbox "Subscribe to our newsletter"]' class="widefat" disabled="disabled"/>
                        <button id="copy_properties" type="button">copy</button>
                    </div>
                </div>
            </div>
        </fieldset><?php
    }

    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_integrations_page_html()
    {
        // check user capabilities
        if (!current_user_can('read')) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_integrations_settings',
            null,
            array($this, 'mailjet_section_integrations_cb'),
            'mailjet_integrations_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_integrations', // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __( 'Integrations', 'mailjet-for-wordpress'),
            array($this, 'mailjet_integrations_cb'),
            'mailjet_integrations_page',
            'mailjet_integrations_settings',
            [
                'label_for' => 'mailjet_integrations',
                'class' => 'mailjet_row',
                'mailjet_custom_data' => 'custom',
            ]
        );


        // add error/update messages

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {
            $executionError = false;

            // Check if selected Contact list - only if the Sync checkbox is checked
            $activate_mailjet_woo_sync = get_option('activate_mailjet_woo_sync');
            $mailjet_woo_list = get_option('mailjet_woo_list');
            if (!empty($activate_mailjet_woo_sync) && !intval($mailjet_woo_list) > 0) {
                    $executionError = true;
                    add_settings_error('mailjet_messages', 'mailjet_message', __('The settings could not be saved. Please select a contact list to subscribe WooCommerce users to.', 'mailjet-for-wordpress'), 'error');
            }

            if (false === $executionError) {
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
                        <div class="centered">
                            <!--                    <h1>--><?php //echo esc_html(get_admin_page_title()); ?><!--</h1>-->
                            <h2 class="section_inner_title"><?php _e('Integrations', 'mailjet-for-wordpress'); ?></h2>
                            <form action="options.php" method="post">
                                <?php
                                // output security fields for the registered setting "mailjet"
                                settings_fields('mailjet_integrations_page');
                                // output setting sections and their fields
                                // (sections are registered for "mailjet", each field is registered to a specific section)
                                do_settings_sections('mailjet_integrations_page');
                                // output save settings button
                                $saveButton = __('Save', 'mailjet-for-wordpress');
                                ?>
                                <button type="submit" id="integrationsSubmit" class="mj-btn btnPrimary MailjetSubmit" name="submit"><?= $saveButton; ?></button>
                                <!-- <input name="cancelBtn" class="mj-btn btnCancel" type="button" id="cancelBtn" onClick="location.href=location.href" value="<?=__('Cancel', 'mailjet-for-wordpress')?>"> -->
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bottom_links">
                <div class="needHelpDiv">
                    <img src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/need_help.png'; ?>" alt="<?php echo __('Need help?', 'mailjet-for-wordpress'); ?>" />
                    <?php echo __('Need help?', 'mailjet-for-wordpress' ); ?>
                </div>
                <?php echo '<a target="_blank" href="' . Mailjeti18n::getMailjetUserGuideLinkByLocale() . '">' . __('Read our user guide', 'mailjet-for-wordpress') . '</a>'; ?>
                <?php echo '<a target="_blank" href="' . Mailjeti18n::getMailjetSupportLinkByLocale() . '">' . __('Contact our support team', 'mailjet-for-wordpress') . '</a>'; ?>
            </div>
        </div>

        <?php
    }



}
