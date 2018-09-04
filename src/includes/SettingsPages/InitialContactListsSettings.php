<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\MailjetApi;
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
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php echo __('Here are the contact lists we have detected on your Mailjet account. You can add your Wordpress subscribers to one of them, or use them to collect new email addresses.', 'mailjet' ); ?>
        </p>
        <?php
    }


    public function mailjet_initial_contact_lists_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $allWpUsers = get_users(array('fields' => array('ID', 'user_email')));
        $wpUsersCount = count($allWpUsers);
        $mailjetContactLists = MailjetApi::getMailjetContactLists();
        $mailjetContactLists = !empty($mailjetContactLists) ? $mailjetContactLists : array();
        $mailjetSyncActivated = get_option('activate_mailjet_sync');
        $mailjetInitialSyncActivated = get_option('activate_mailjet_initial_sync');
        $mailjetSyncList = get_option('mailjet_sync_list');

        // output the field
        ?>
<hr>
        <h2> <?php echo __('Your Mailjet contact lists', 'mailjet' ); ?> </h2>

        <div class="availableContactListsContainerParent">
        <div class="availableContactListsContainer">
            <?php // Display available contact lists and containing contacts
            foreach ($mailjetContactLists as $mailjetContactList) {
                if ($mailjetContactList["IsDeleted"] == true) {
                    continue;
                }
                ?>
                <div class="availableContactListsRow">
                    <div class="availableContactListsNameCell"><?=$mailjetContactList['Name'] ?></div>
                    <div class="availableContactListsCountCell"><?=$mailjetContactList['SubscriberCount'] ?> <?php echo  __('contacts', 'mailjet'); ?></div>
                </div>
                <?php
            }
            ?>
        </div>
        </div>

        <div class="create_contact_list_popup pop" id="create_contact_list_popup">
            <p><label for="create_list_name"><?php echo __('Name your list', 'mailjet' ); ?></label>
                <input type="text" size="30" name="create_list_name" id="create_list_name" />
            </p>
            <input type="submit" value="Save" name="create_contact_list_btn" class="MailjetSubmit" id="create_contact_list_btn"/>
            <input name="nextBtn" class="nextBtn closeCreateList" type="button" id="nextBtn" value="<?=__('Cancel', 'mailjet')?>">
            <br style="clear: left;"/>
        </div>

        <img id="createContactListImg" src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/create_contact_list.png'; ?>" alt="<?php echo __('Create a new list', 'mailjet'); ?>" />
        <a id="create_contact_list" href="#"><?php echo __('Create a new list', 'mailjet' ); ?></a>
        <br /><br />
<hr>

        <fieldset class="initialContactListsFieldset">
            <h3><?php echo __('Synchronize your Wordpress users', 'mailjet' ); ?></h3>
            <p><?php echo __('If you wish, you can add your Wordpress website users (readers, authors, administrators, …) to a contact list.', 'mailjet' ); ?></p>
            <legend class="screen-reader-text"><span><?php echo  __('Automatically add Wordpress subscribers to a specific list', 'mailjet'); ?></span></legend>
            <label for="activate_mailjet_sync">
                <input name="activate_mailjet_sync" type="checkbox" id="activate_mailjet_sync" value="1" <?=($mailjetSyncActivated == 1 ? ' checked="checked"' : '') ?> >
                <?php echo __('Automatically add all my future Wordpress subscribers to a specific contact list', 'mailjet'); ?></label>
            <br /><br />

            <div class="mailjet_sync_options_div">
                <select name="mailjet_sync_list" id="mailjet_sync_list" type="select">
                    <?php
                    foreach ($mailjetContactLists as $mailjetContactList) {
                        if ($mailjetContactList["IsDeleted"] == true) {
                            continue;
                        }
                        ?>
                        <option value="<?= $mailjetContactList['ID'] ?>" <?= ($mailjetSyncList == $mailjetContactList['ID'] ? 'selected="selected"' : '') ?> > <?= $mailjetContactList['Name'] ?>
                            (<?= $mailjetContactList['SubscriberCount'] ?>)
                        </option>
                        <?php
                    } ?>
                </select>
                <br /><br />

                <label for="activate_mailjet_initial_sync">
                    <input name="activate_mailjet_initial_sync" type="checkbox" id="activate_mailjet_initial_sync" value="1" <?=($mailjetInitialSyncActivated == 1 ? ' checked="checked"' : '') ?> >
                    <?php echo sprintf(__('Also, add existing <b>%s Wordpress users</b> (initial synchronization)', 'mailjet'), $wpUsersCount); ?></label>
                <br /><br />
            </div>
        </fieldset>
<hr>
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
            null,
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

            $executionError = false;

            // Initial sync WP users to Mailjet - when the 'create_contact_list_btn' button is not the one that submits the form
            if (empty(get_option('create_contact_list_btn')) && !empty(get_option('activate_mailjet_initial_sync')) && intval(get_option('mailjet_sync_list')) > 0) {
                $syncResponse = SubscriptionOptionsSettings::syncAllWpUsers();
                if (false === $syncResponse) {
                    add_settings_error('mailjet_messages', 'mailjet_message', __('The settings could not be saved. Please try again or in case the problem persists contact Mailjet support.', 'mailjet'), 'error');
                }
            }

            // Create new Contact List
            if (!empty(get_option('create_contact_list_btn'))) {
                if (!empty(get_option('create_list_name'))) {
                    $createListResponse = MailjetApi::createMailjetContactList(get_option('create_list_name'));
                    if (false === $createListResponse) {
                        $executionError = true;
                        add_settings_error('mailjet_messages', 'mailjet_message',
                            __('The settings could not be saved. Please try again or in case the problem persists contact Mailjet support.',
                                'mailjet'), 'error');
                    } else {
                        $executionError = true;
                        add_settings_error('mailjet_messages', 'mailjet_message',
                            __('Your new contact list has been successfully created.', 'mailjet'), 'updated');
                    }
                } else { // New list name empty
                    $executionError = true;
                    add_settings_error('mailjet_messages', 'mailjet_message',
                        __('Please enter a valid contact list name', 'mailjet'), 'error');
                }
            }

            if (false === $executionError) {
                // add settings saved message with the class of "updated"
                add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet'), 'updated');
            }
        }

        // show error/update messages
        settings_errors('mailjet_messages');

        ?>


        <div id="initialSettingsHead"><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/LogoMJ_White_RVB.svg'; ?>" alt="Mailjet Logo" /></div>
        <div class="mainContainer">

            <div class="wrap">
                <h1><?php echo __('Mailjet plugin for Wordpress', 'mailjet'); ?> </h1>
                <p>
                    <?php echo __('Mailjet is an email service provider. With this plugin, easily send newsletters to your contacts, directly from Wordpress.', 'mailjet'); ?>
                </p>
            </div>



            <div id="initialContactListsForm">
                <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
                <form action="options.php" method="post">
                    <?php
                    // output security fields for the registered setting "mailjet"
                    settings_fields('mailjet_initial_contact_lists_page');
                    // output setting sections and their fields
                    // (sections are registered for "mailjet", each field is registered to a specific section)
                    do_settings_sections('mailjet_initial_contact_lists_page');
                    // output save settings button
                    if (MailjetApi::isValidAPICredentials()) {
                        submit_button('Apply and continue', 'MailjetSubmit', 'submit', false, array('id' => 'initialContactListsSubmit'));
                    } else {
                        update_option('settings_step', 'initial_step')
                        ?>
                        <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_settings_page'" value="<?=__('Back', 'mailjet')?>">
                    <?php
                    } ?>

                    <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_allsetup_page'" value="<?=__('Skip this step', 'mailjet')?>">

                    <br />
                </form>
            </div>

        </div>

        <div class="bottom_links">
            <div class="needHelpDiv">
                <img src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/need_help.png'; ?>" alt="<?php echo __('Connect your Mailjet account', 'mailjet'); ?>" />
                <?php echo __('Need help getting started?', 'mailjet' ); ?>
            </div>
            <?php echo '<a target="_blank" href="https://www.mailjet.com/guides/wordpress-user-guide/">' . __('Read our user guide', 'mailjet') . '</a>'; ?>
            &nbsp;&nbsp;&nbsp;
            <?php echo '<a target="_blank" href="https://www.mailjet.com/support/ticket">' . __('Contact our support team', 'mailjet') . '</a>'; ?>
        </div>

        <?php

    }



}
