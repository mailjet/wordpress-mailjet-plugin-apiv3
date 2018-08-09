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
class SubscriptionOptionsSettings
{

    public function mailjet_section_subscription_options_cb($args)
    {echo get_option('settings_step');
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'mailjet' ); ?></p>
        <?php
    }


    public function mailjet_subscription_options_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $mailjetEnabled = get_option('mailjet_enabled');

        // output the field
        ?>

        <fieldset>
            <legend class="screen-reader-text"><span><?php echo  __('Enable email through <b>Mailjet</b>', 'mailjet'); ?></span></legend>
            <label for="mailjet_enabled">
                <input name="subscription_options" type="checkbox" id="subscription_options" value="1" <?=($mailjetEnabled == 1 ? ' checked="checked"' : '') ?> > <?php echo __('Enable email through <b>Mailjet</b>', 'mailjet'); ?></label>
        </fieldset>

        <input name="settings_step" type="hidden" id="settings_step" value="subscription_options_step">

        <?php
    }







    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_subscription_options_page_html()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_subscription_options_settings',
            __( 'The Matrix has you.', 'mailjet' ),
            array($this, 'mailjet_section_subscription_options_cb'),
            'mailjet_subscription_options_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_subscription_options', // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __( 'Subscription Options', 'mailjet' ),
            array($this, 'mailjet_subscription_options_cb'),
            'mailjet_subscription_options_page',
            'mailjet_subscription_options_settings',
            [
                'label_for' => 'mailjet_subscription_options',
                'class' => 'mailjet_row',
                'mailjet_custom_data' => 'custom',
            ]
        );


        // add error/update messages

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {

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
                settings_fields('mailjet_subscription_options_page');
                // output setting sections and their fields
                // (sections are registered for "mailjet", each field is registered to a specific section)
                do_settings_sections('mailjet_subscription_options_page');
                // output save settings button
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }


}
