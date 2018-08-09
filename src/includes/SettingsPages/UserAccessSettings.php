<?php

namespace MailjetPlugin\Includes\SettingsPages;

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
    {echo get_option('settings_step');
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'mailjet' ); ?></p>
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

        <fieldset>
            <legend class="screen-reader-text"><span><?php echo  __('User Access', 'mailjet'); ?></span></legend>
            <label for="mailjet_access_administrator"><input name="mailjet_access_administrator" type="checkbox" id="mailjet_access_administrator" value="1" <?=($mailjetAccessAdministrator == 1 ? ' checked="checked"' : '') ?> > <?php echo __('Enable email through <b>Mailjet</b>', 'mailjet'); ?></label><br />
            <label for="mailjet_access_editor"><input name="mailjet_access_editor" type="checkbox" id="mailjet_access_editor" value="1" <?=($mailjetAccessEditor == 1 ? ' checked="checked"' : '') ?> > <?php echo __('Editor', 'mailjet'); ?></label><br />
            <label for="mailjet_access_author"><input name="mailjet_access_author" type="checkbox" id="mailjet_access_author" value="1" <?=($mailjetAccessAuthor == 1 ? ' checked="checked"' : '') ?> > <?php echo __('Author', 'mailjet'); ?></label><br />
            <label for="mailjet_access_contributor"><input name="mailjet_access_contributor" type="checkbox" id="mailjet_access_contributor" value="1" <?=($mailjetAccessContributor == 1 ? ' checked="checked"' : '') ?> > <?php echo __('Contributor', 'mailjet'); ?></label><br />
            <label for="mailjet_access_subscriber"><input name="mailjet_access_subscriber" type="checkbox" id="mailjet_access_subscriber" value="1" <?=($mailjetAccessSubscriber == 1 ? ' checked="checked"' : '') ?> > <?php echo __('Subscriber', 'mailjet'); ?></label><br />
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
        if (!current_user_can('manage_options')) {
            return;
        }

        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_user_access_settings',
            __( 'The Matrix has you.', 'mailjet' ),
            array($this, 'mailjet_section_user_access_cb'),
            'mailjet_user_access_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_user_access', // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __( 'User Access', 'mailjet' ),
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

            echo get_option('mailjet_access_editor');
            // add settings saved message with the class of "updated"
            add_settings_error('mailjet_messages', 'mailjet_message', __('User Access Settings Saved', 'mailjet'), 'updated');
        }

        // show error/update messages
        settings_errors('mailjet_messages');

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting "mailjet"
                settings_fields('mailjet_user_access_page');
                // output setting sections and their fields
                // (sections are registered for "mailjet", each field is registered to a specific section)
                do_settings_sections('mailjet_user_access_page');
                // output save settings button
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }

}
