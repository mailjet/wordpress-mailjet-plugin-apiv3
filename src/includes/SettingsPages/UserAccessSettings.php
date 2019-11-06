<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
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
class UserAccessSettings
{

    public function mailjet_section_user_access_cb($args)
    {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php echo __('Select which WordPress user roles (in addition to Administrator) will also have access to the Mailjet Plugin', 'mailjet-for-wordpress'); ?>
        </p>
        <?php
    }


    public function mailjet_user_access_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $mailjetAccessAdministrator = get_option('mailjet_access_administrator');
        $mailjetAccessEditor = get_option('mailjet_access_editor');
        $mailjetAccessAuthor = get_option('mailjet_access_author');
        $mailjetAccessContributor = get_option('mailjet_access_contributor');
        $mailjetAccessSubscriber = get_option('mailjet_access_subscriber');

        ?>

        <fieldset class="settingsAccessFldset">
            <legend class="screen-reader-text"><span><?php  _e('User Access', 'mailjet-for-wordpress'); ?></span></legend>
            <label class="checkboxLabel" for="mailjet_access_administrator">
                <input name="mailjet_access_administrator" type="checkbox" id="mailjet_access_administrator" value="1" <?=( TRUE || $mailjetAccessAdministrator == 1 ? ' checked="checked" disabled' : '') ?> >
                <span><?php _e('Administrator', 'mailjet-for-wordpress'); ?></span>
            </label>
            <label class="checkboxLabel" class="mj-label" for="mailjet_access_editor">
                <input name="mailjet_access_editor" type="checkbox" id="mailjet_access_editor" value="1" <?=($mailjetAccessEditor == 1 ? ' checked="checked"' : '') ?> >
                <span><?php _e('Editor', 'mailjet-for-wordpress'); ?></span>
            </label>
            <label class="checkboxLabel" class="mj-label" for="mailjet_access_author">
                <input name="mailjet_access_author" type="checkbox" id="mailjet_access_author" value="1" <?=($mailjetAccessAuthor == 1 ? ' checked="checked"' : '') ?> >
                <span><?php _e('Author', 'mailjet-for-wordpress'); ?></span>
            </label>
            <label class="checkboxLabel" class="mj-label" for="mailjet_access_contributor">
                <input name="mailjet_access_contributor" type="checkbox" id="mailjet_access_contributor" value="1" <?=($mailjetAccessContributor == 1 ? ' checked="checked"' : '') ?> >
                <span><?php _e('Contributor', 'mailjet-for-wordpress'); ?></label>
            <label class="checkboxLabel" class="mj-label" for="mailjet_access_subscriber">
                <input name="mailjet_access_subscriber" type="checkbox" id="mailjet_access_subscriber" value="1" <?=($mailjetAccessSubscriber == 1 ? ' checked="checked"' : '') ?> >
                <?php _e('Subscriber', 'mailjet-for-wordpress'); ?></span>
            </label>
        </fieldset>

        <input name="settings_step" type="hidden" id="settings_step" value="user_access_step">

        <?php
    }





    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_user_access_page_html()
    {
        // check user capabilities
        if (!current_user_can('read')) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_user_access_settings',
            null,
            array($this, 'mailjet_section_user_access_cb'),
            'mailjet_user_access_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_user_access', // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __( 'User Access', 'mailjet-for-wordpress' ),
            array($this, 'mailjet_user_access_cb'),
            'mailjet_user_access_page',
            'mailjet_user_access_settings',
            [
                'label_for' => 'mailjet_user_access',
                'class' => 'mailjet_row',
                'mailjet_custom_data' => 'custom',
            ]
        );


        // add error/update messages

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {

            // add settings saved message with the class of "updated"
            add_settings_error('mailjet_messages', 'mailjet_message', __('User Access Settings Saved', 'mailjet-for-wordpress'), 'updated');
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
                            <h2 class="section_inner_title"><?php _e('User access', 'mailjet-for-wordpress'); ?></h2>
                            <form action="options.php" method="post">
                                <?php
                                // output security fields for the registered setting "mailjet"
                                settings_fields('mailjet_user_access_page');
                                // output setting sections and their fields
                                // (sections are registered for "mailjet", each field is registered to a specific section)
                                do_settings_sections('mailjet_user_access_page');
                                // output save settings button
                                $saveButton = __('Save', 'mailjet-for-wordpress');
                                ?>
                                <button type="submit" id="userAccessSubmit" class="mj-btn btnPrimary MailjetSubmit" name="submit"><?= $saveButton; ?></button>
                                <!-- <input name="cancelBtn" class="mj-btn btnCancel" type="button" id="cancelBtn" onClick="location.href=location.href" value="<?=__('Cancel', 'mailjet-for-wordpress')?>"> -->
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

}
