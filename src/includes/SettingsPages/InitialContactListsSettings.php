<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\MailjetSettings;
use MailjetPlugin\Includes\Mailjeti18n;

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
        <h2 class="section_inner_title"><?php _e('Configure your lists.', 'mailjet'); ?> </h2>
        <p class="top_descrption_helper" id="<?php echo esc_attr($args['id']); ?>">
            <?php _e('Here are the contact lists we have detected on your Mailjet account. You can add your Wordpress subscribers to one of them, or use them to collect new email addresses.', 'mailjet'); ?>
        </p>
        <?php
    }

    public function mailjet_initial_contact_lists_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $allWpUsers = get_users(array('fields' => array('ID', 'user_email')));
        $wpUsersCount = count($allWpUsers);
        try {
            $mailjetContactLists = MailjetApi::getMailjetContactLists();
        } catch (\Exception $ex) {
            update_option('api_credentials_ok', 0);
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page'));
            die;
        }

        $mailjetContactLists = !empty($mailjetContactLists) ? $mailjetContactLists : array();
        $mailjetSyncActivated = get_option('activate_mailjet_sync');
        $mailjetInitialSyncActivated = get_option('activate_mailjet_initial_sync');
        $mailjetSyncList = get_option('mailjet_sync_list');

        // output the field
        ?>

        <div class="availableContactLists">
            <h3 class="section_inner_title_slave"> <?php echo __('Your Mailjet contact lists', 'mailjet'); ?></h3>
            <div class="availableContactListsContainerParent" id="availableContactListsContainerParent">
                <div class="availableContactListsContainer">
                    <?php
                    // Display available contact lists and containing contacts
                    foreach ($mailjetContactLists as $mailjetContactList) {
                        if ($mailjetContactList["IsDeleted"] == true) {
                            continue;
                        }
                        ?>
                        <div class="availableContactListsRow">
                            <span class="availableContactListsNameCell"><?php echo $mailjetContactList['Name'] ?></span>
                            <b class="availableContactListsCountCell"><?php echo $mailjetContactList['SubscriberCount'] ?> <?php _e('contacts', 'mailjet'); ?></b>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <a id="create_contact_list" class="mj-toggleBtn" data-target="create_contact_list_popup">
                <img width="16" id="createContactListImg" src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/create_contact_list.svg'; ?>" alt="<?php echo __('Create a new list', 'mailjet'); ?>" />
        <?php echo __('Create a new list', 'mailjet'); ?>
            </a>
            <div class="mj-hide create_contact_list_popup" id="create_contact_list_popup">
                <div class="create_contact_list_fields">
                    <label class="mj-label" for="create_list_name"><?php echo __('Name your list (max. 50 characters)', 'mailjet'); ?></label>
                    <input type="text" size="30" name="create_list_name" id="create_list_name" />
                </div>
                <div class="create_contact_list_btns">
                    <input type="submit" value="<?= __('Save', 'mailjet') ?>" name="create_contact_list_btn" class="MailjetSubmit mj-btn btnPrimary btnSmall nextBtn" id="create_contact_list_btn"/>
                    <input name="nextBtn" class="mj-btn btnCancel btnSmall nextBtn closeCreateList" type="button" id="cancel_create_list" value="<?= __('Cancel', 'mailjet') ?>">
                </div>
            </div>
        </div>

        <fieldset class="initialContactListsFieldset">
            <h2 class="section_inner_title"><?php _e('Synchronize your Wordpress users', 'mailjet'); ?></h2>
            <p><?php echo __('If you wish, you can add your Wordpress website users (readers, authors, administrators, …) to a contact list.', 'mailjet'); ?></p>
            <legend class="screen-reader-text"><span><?php echo __('Automatically add Wordpress subscribers to a specific list', 'mailjet'); ?></span></legend>
            <div class="activate_mailjet_sync_field">
                <label class="checkboxLabel" for="activate_mailjet_sync">
                <input name="activate_mailjet_sync" type="checkbox" id="activate_mailjet_sync" value="1" <?=($mailjetSyncActivated == 1 ? ' checked="checked"' : '') ?>  autocomplete="off">
                    <span><?php echo __('Automatically add all my future Wordpress subscribers to a specific contact list', 'mailjet'); ?></span>
                </label>
                
                <div id="activate_mailjet_sync_form" class="<?=($mailjetSyncActivated == 1 ? ' mj-show' : 'mj-hide') ?>">
                    <div class="mailjet_sync_options_div">
                        <select class="mj-select" name="mailjet_sync_list" id="mailjet_sync_list" type="select">
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
                        <label class="checkboxLabel" for="activate_mailjet_initial_sync">
                            <input name="activate_mailjet_initial_sync" type="checkbox" id="activate_mailjet_initial_sync" value="1" <?=($mailjetInitialSyncActivated == 1 ? ' checked="checked"' : '') ?> >
                            <span><?php echo sprintf(__('Also, add existing <b>%s Wordpress users</b> (initial synchronization)', 'mailjet'), $wpUsersCount); ?></span>
                        </label>
                    </div>
                </div>
            </div>
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
        $applyAndContinueBtnClicked = false;
        $fromPage = !empty($_REQUEST['from']) ? $_REQUEST['from'] : null;

        // register a new section in the "mailjet" page
        add_settings_section(
                'mailjet_initial_contact_lists_settings', null, array($this, 'mailjet_section_initial_contact_lists_cb'), 'mailjet_initial_contact_lists_page'
        );

        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
                'mailjet_enable_sending', // as of WP 4.6 this value is used only internally
                // use $args' label_for to populate the id inside the callback
                __('Enable sending emails through Mailjet', 'mailjet'), array($this, 'mailjet_initial_contact_lists_cb'), 'mailjet_initial_contact_lists_page', 'mailjet_initial_contact_lists_settings', [
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
            $applyAndContinueBtnClicked = false;

            // Initial sync WP users to Mailjet - when the 'create_contact_list_btn' button is not the one that submits the form
            if (empty(get_option('create_contact_list_btn')) && !empty(get_option('activate_mailjet_initial_sync')) && intval(get_option('mailjet_sync_list')) > 0) {
                $syncResponse = SubscriptionOptionsSettings::syncAllWpUsers();
                if (false === $syncResponse) {
                    $executionError = true;
                    update_option('contacts_list_ok', 0);
                    add_settings_error('mailjet_messages', 'mailjet_message', __('The settings could not be saved. Please try again or in case the problem persists contact Mailjet support.', 'mailjet'), 'error');
                }
            }

            // Create new Contact List
            if (!empty(get_option('create_contact_list_btn'))) {
                if (!empty(get_option('create_list_name'))) {
                    $createListResponse = MailjetApi::createMailjetContactList(get_option('create_list_name'));

                    if ($createListResponse->success()) {
                        add_settings_error('mailjet_messages', 'mailjet_message', __('Congratulations! You have just created a new contact list!', 'mailjet'), 'updated');
                    } else {
                        $executionError = true;
                        update_option('contacts_list_ok', 0);

                        if (isset($createListResponse->getBody()['ErrorMessage']) && stristr($createListResponse->getBody()['ErrorMessage'], 'already exists')) {
                            add_settings_error('mailjet_messages', 'mailjet_message', sprintf(__('A contact list with name <b>%s</b> already exists', 'mailjet'), get_option('create_list_name')), 'error');
                        } else {
                            $executionError = true;
                            update_option('contacts_list_ok', 0);

                            add_settings_error('mailjet_messages', 'mailjet_message', __('The settings could not be saved. Please try again or in case the problem persists contact Mailjet support.', 'mailjet'), 'error');
                        }
                    }
                } else { // New list name empty
                    $executionError = true;
                    add_settings_error('mailjet_messages', 'mailjet_message', __('Please enter a valid contact list name', 'mailjet'), 'error');
                }
            } else {
                $applyAndContinueBtnClicked = true;
            }

            if (false === $executionError) {
                update_option('contacts_list_ok', 1);

                // add settings saved message with the class of "updated"
                add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet'), 'updated');

                if (!($fromPage == 'plugins') || (!empty(get_option('contacts_list_ok')) && '1' == get_option('contacts_list_ok'))) {

                    // Redirect if the create contact button is not set
                    if (empty(get_option('create_contact_list_btn'))) {
                        MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_allsetup_page'));
                    }
                }
            }
        }
        if (!($fromPage == 'plugins') && (!empty(get_option('contacts_list_ok')) && '1' == get_option('contacts_list_ok'))) {
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_dashboard_page'));
        }

        // show error/update messages
        settings_errors('mailjet_messages');
        ?>


        <div id="initialSettingsHead"><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/LogoMJ_White_RVB.svg'; ?>" alt="Mailjet Logo" /></div>
        <div class="mainContainer">

            <div class="backToDashboard">
                <a href="admin.php?page=mailjet_dashboard_page">
                <svg width="8" height="8" viewBox="0 0 16 16"><path d="M7.89 11.047L4.933 7.881H16V5.119H4.934l2.955-3.166L6.067 0 0 6.5 6.067 13z"/></svg>
                <?php _e('Back to dashboard', 'mailjet') ?>
                </a>
            </div>

            <div>
                <h1 class="page_top_title"><?php echo __('Welcome to the Mailjet plugin for Wordpress', 'mailjet'); ?> </h1>
                <p class="page_top_subtitle">
        <?php _e('Mailjet is an email service provider. With this plugin, easily send newsletters to your website users, directly from Wordpress.', 'mailjet'); ?>
                </p>
            </div>

            <div id="initialContactListsForm">
                <p class="section_title"><?php echo esc_html(get_admin_page_title()); ?></p>
                <form action="options.php" method="post">
                   <?php
                    // output security fields for the registered setting "mailjet"
                    settings_fields('mailjet_initial_contact_lists_page');
                    // output setting sections and their fields
                    // (sections are registered for "mailjet", each field is registered to a specific section)
                    do_settings_sections('mailjet_initial_contact_lists_page');
                    // output save settings button
                    if (MailjetApi::isValidAPICredentials()) {
                        submit_button(__('Apply & Continue', 'mailjet'), 'mj-btn btnPrimary MailjetSubmit', 'submit', false, array('id' => 'initialContactListsSubmit'));
                    } else {
                        update_option('settings_step', 'initial_step')
                        ?>
                        <input name="nextBtn" class="mj-btn btnPrimary nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_settings_page'" value="<?=__('Back', 'mailjet')?>">
                    <?php
                    } ?>

                    <input name="nextBtn" class="mj-btn btnSecondary nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_allsetup_page'" value="<?php (true !== $applyAndContinueBtnClicked) ? _e('Skip this step', 'mailjet') : _e('Next', 'mailjet'); ?>">

                    <br />
                </form>
            </div>

        </div>

        <div class="bottom_links">
            <div class="needHelpDiv">
                <img src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/need_help.png'; ?>" alt="<?php echo __('Connect your Mailjet account', 'mailjet'); ?>" />
            <?php echo __('Need help getting started?', 'mailjet'); ?>
            </div>
            <?php echo '<a target="_blank" href="' . Mailjeti18n::getMailjetUserGuideLinkByLocale() . '">' . __('Read our user guide', 'mailjet') . '</a>'; ?>
        <?php echo '<a target="_blank" href="' . Mailjeti18n::getMailjetSupportLinkByLocale() . '">' . __('Contact our support team', 'mailjet') . '</a>'; ?>
        </div>

        <?php
    }

}
