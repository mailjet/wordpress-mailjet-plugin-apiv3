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
class InitialContactListsSettings
{
    public function mailjet_section_initial_contact_lists_cb($args)
    {
        ?>
        <h2><?php echo __('Choose a list so synchronize', 'mailjet' ); ?></h2>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php echo __('You can choose to add your Wordpress subscribers to an existing list or create a new list', 'mailjet' ); ?>
        </p>
        <?php
    }




    public function mailjet_initial_contact_lists_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $mailjetContactLists = $this->getMailjetContactLists();
        $mailjetSyncActivated = get_option('activate_mailjet_sync');
        $mailjetInitialSyncActivated = get_option('activate_mailjet_initial_sync');
        $mailjetSyncList = get_option('mailjet_sync_list');

        // output the field
        ?>

        <fieldset>
            <legend class="screen-reader-text"><span><?php echo  __('Automatically add Wordpress users to a specific list', 'mailjet'); ?></span></legend>
            <label for="activate_mailjet_sync">
                <input name="activate_mailjet_sync" type="checkbox" id="activate_mailjet_sync" value="1" <?=($mailjetSyncActivated == 1 ? ' checked="checked"' : '') ?> >
                <?php echo __('Automatically add all my future Wordpress users to a specific contact list', 'mailjet'); ?></label>
            <br />

            <select name="mailjet_sync_list" id="mailjet_sync_list" type="select">
                <?php
                foreach ($mailjetContactLists as $mailjetContactList) {
                    if ($mailjetContactList["IsDeleted"] == true) {
                        continue;
                    }
                    ?>
                    <option value="<?=$mailjetContactList['ID'] ?>" <?=($mailjetSyncList == $mailjetContactList['ID'] ? 'selected="selected"' : '') ?> > <?=$mailjetContactList['Name'] ?> (<?=$mailjetContactList['SubscriberCount'] ?>) </option>
                    <?php
                } ?>
            </select>
            <br />

            <label for="activate_mailjet_initial_sync">
                <input name="activate_mailjet_initial_sync" type="checkbox" id="activate_mailjet_initial_sync" value="1" <?=($mailjetInitialSyncActivated == 1 ? ' checked="checked"' : '') ?> >
                <?php echo sprintf(__('Also, add existing %s Wordpress users (initial synchronization)', 'mailjet'), $wpUsersCount); ?></label>
            <br />


            <div class="create_contact_list_popup pop">
                <p><label for="list_name">Contact list name</label><input type="text" size="30" name="list_name" id="list_name" /></p>
                <p><input type="submit" value="Create" name="create_contact_list_btn" id="create_contact_list_btn"/> or <a class="closeCreateList" href="/">Cancel</a></p>
            </div>
            <input name="create_contact_list" type="button" id="create_contact_list" value="Create new contactlist">
            <br />


        </fieldset>

        <input name="settings_step" type="hidden" id="settings_step" value="initial_contact_lists_settings_step">

        <?php
    }



    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_initial_contact_lists_page_html()
    {
        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_initial_contact_lists_settings',
            __( 'Configure your lists.', 'mailjet' ),
            array($this, 'mailjet_section_initial_contact_lists_cb'),
            'mailjet_initial_contact_lists_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_enable_sending', // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __( 'Mailjet Enable Email Sending', 'mailjet' ),
            array($this, 'mailjet_initial_contact_lists_cb'),
            'mailjet_initial_contact_lists_page',
            'mailjet_initial_contact_lists_settings',
            [
                'label_for' => 'mailjet_initial_contact_lists',
                'class' => 'mailjet_row',
                'mailjet_custom_data' => 'custom',
            ]
        );


        // check user capabilities
        if (!current_user_can('manage_options')) {
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        // add error/update messages

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {

            // Initial sync WP users to Mailjet
            if (!empty(get_option('activate_mailjet_initial_sync')) && intval(get_option('mailjet_sync_list')) > 0) {
                $syncResponse = SubscriptionOptionsSettings::syncAllWpUsers();
            }

            // Create new Contact List
            if (!empty(get_option('create_contact_list_btn')) && !empty(get_option('list_name'))) {
                $this->createMailjetContactList(get_option('list_name'));
            }

            // add settings saved message with the class of "updated"
            add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet'), 'updated');


        }

        // show error/update messages
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
                    settings_fields('mailjet_initial_contact_lists_page');
                    // output setting sections and their fields
                    // (sections are registered for "mailjet", each field is registered to a specific section)
                    do_settings_sections('mailjet_initial_contact_lists_page');
                    // output save settings button
                    submit_button('Save');
                    ?>

                    <?php
                    if (!empty(get_option('activate_mailjet_initial_sync')) && intval(get_option('mailjet_sync_list')) > 0) { ?>
                        <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_dashboard_page'" value="<?=__('Next', 'mailjet')?>">
                    <?php }
                    ?>

                </form>

            </div>
        </div>
        </div>

        <?php

    }



    private function getMailjetContactLists()
    {
        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        $mjApiClient = new \Mailjet\Client($mailjetApikey, $mailjetApiSecret);

        $filters = [
            'Limit' => '0'
        ];
        $responseSenders = $mjApiClient->get(\Mailjet\Resources::$Contactslist, ['filters' => $filters]);
        if ($responseSenders->success()) {
            return $responseSenders->getData();
        } else {
            return $responseSenders->getStatus();
        }

    }





    private function createMailjetContactList($listName)
    {
        if (empty($listName)) {
            return false;
        }

        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        $mjApiClient = new \Mailjet\Client($mailjetApikey, $mailjetApiSecret);

        $body = [
            'Name' => $listName
        ];
        $responseSenders = $mjApiClient->post(\Mailjet\Resources::$Contactslist, ['body' => $body]);
        if ($responseSenders->success()) {
            return $responseSenders->getData();
        } else {
            return false;
//            return $responseSenders->getStatus();
        }

    }


}
