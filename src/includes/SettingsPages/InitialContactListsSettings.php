<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\MailjetLogger;
use MailjetPlugin\Includes\MailjetSettings;

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
        <!--<h2 class="section_inner_title"><?php _e('Configure your lists.', 'mailjet-for-wordpress'); ?> </h2>-->
        <!--        <p class="top_descrption_helper" id="<?php echo esc_attr($args['id']); ?>">
        <?php _e('Here are the contact lists we have detected on your Mailjet account. You can add your WordPress subscribers to one of them, or use them to collect new email addresses.', 'mailjet-for-wordpress'); ?>
        </p>-->
        <?php
    }

    private function updateMailjetProfileName()
    {
        $profileName = get_option('mj_profile_name');
        $newProfileName = MailjetApi::getProfileName();
        if (!$profileName) {
            add_option('mj_profile_name', $newProfileName);
        } else {
            update_option('mj_profile_name', $newProfileName);
        }
    }

    private function createMailjetContactPropertiesThatWpSync()
    {
        MailjetApi::createMailjetContactProperty(SubscriptionOptionsSettings::PROP_USER_FIRSTNAME);
        MailjetApi::createMailjetContactProperty(SubscriptionOptionsSettings::PROP_USER_LASTNAME);
        MailjetApi::createMailjetContactProperty(SubscriptionOptionsSettings::WP_PROP_USER_ROLE);
    }

    public function mailjet_initial_contact_lists_cb($args)
    {
        $this->updateMailjetProfileName();
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
        $this->createMailjetContactPropertiesThatWpSync();

        $mailjetContactLists = !empty($mailjetContactLists) ? $mailjetContactLists : array();
        $mailjetSyncActivated = get_option('activate_mailjet_sync');
        $mailjetInitialSyncActivated = get_option('activate_mailjet_initial_sync');
        $mailjetSyncList = get_option('mailjet_sync_list');

        // output the field
        ?>

        </div>

        <fieldset class="initialContactListsFieldset">
            <h2 class="section_inner_title"><?php _e('Synchronize your WordPress users', 'mailjet-for-wordpress'); ?></h2>
            <p><?php echo __("Please select a Mailjet contact list below to automatically add all future WordPress users. Each new user's email address and role (subscriber, administrator, author, …) will be synchronized to the list and available for use inside Mailjet.", 'mailjet-for-wordpress'); ?></p>
            <legend class="screen-reader-text"><span><?php echo __('Automatically add WordPress subscribers to a specific list', 'mailjet-for-wordpress'); ?></span></legend>
            <div class="activate_mailjet_sync_field">
                <div id="activate_mailjet_sync_form" class="mj-show">
                    <div class="mailjet_sync_options_div">
                        <h4><?php _e('Your Mailjet contact lists', 'mailjet-for-wordpress'); ?></h4>
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
                                <?php }
                            ?>
                        </select>
                        <a id="create_contact_list" class="mj-toggleBtn" data-target="create_contact_list_popup">
                            <img width="16" id="createContactListImg" src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/create_contact_list.svg'; ?>" alt="<?php echo __('Create a new list', 'mailjet-for-wordpress'); ?>" />
                            <?php echo __('Create a new list', 'mailjet-for-wordpress'); ?>
                        </a>
                        <div class="mj-hide create_contact_list_popup" id="create_contact_list_popup">
                            <div class="create_contact_list_fields">
                                <label class="mj-label" for="create_list_name"><b><?php _e('Name your list (max. 50 characters)', 'mailjet-for-wordpress'); ?></b></label>
                                <input type="text" size="30" name="create_list_name" id="create_list_name" />
                            </div>
                            <div class="create_contact_list_btns">
                                <input type="submit" name="create_contact_list_btn" class="MailjetSubmit mj-btn btnPrimary btnSmall nextBtn" id="create_contact_list_btn" value="<?php _e('Save', 'mailjet-for-wordpress') ?>" >
                                <input name="cancelBtn" class="mj-btn btnCancel" type="button" id="cancel_create_list" value="<?= __('Cancel', 'mailjet-for-wordpress') ?>">
                            </div>
                        </div>
                        <label class="checkboxLabel" for="activate_mailjet_initial_sync" style="margin-bottom: 157px!important;">
                            <input name="activate_mailjet_initial_sync" type="checkbox" id="activate_mailjet_initial_sync" value="1" <?= ($mailjetInitialSyncActivated == 1 ? ' checked="checked"' : '') ?> >
                            <span><?php echo sprintf(__('Also, add existing <b>%s WordPress users</b> (initial synchronization)', 'mailjet-for-wordpress'), $wpUsersCount); ?></span>
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

        if (MailjetApi::getContactProperties() === false) {
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page&from=plugins'));
        }
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
            __('Enable sending emails through Mailjet', 'mailjet-for-wordpress'), array($this, 'mailjet_initial_contact_lists_cb'), 'mailjet_initial_contact_lists_page', 'mailjet_initial_contact_lists_settings', [
                'label_for' => 'mailjet_initial_contact_lists',
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
            $applyAndContinueBtnClicked = false;

            // Initial sync WP users to Mailjet - when the 'create_contact_list_btn' button is not the one that submits the form
            $create_contact_list_btn = get_option('create_contact_list_btn');
            $activate_mailjet_initial_sync = get_option('activate_mailjet_initial_sync');
            $mailjet_sync_list = get_option('mailjet_sync_list');
            if (empty($create_contact_list_btn) && !empty($activate_mailjet_initial_sync) && intval($mailjet_sync_list) > 0) {
                $syncResponse = SubscriptionOptionsSettings::syncAllWpUsers();
                if (false === $syncResponse) {
                    $executionError = true;
                    update_option('contacts_list_ok', 0);
                    add_settings_error('mailjet_messages', 'mailjet_message', __('The settings could not be saved. Please try again or in case the problem persists contact Mailjet support.', 'mailjet-for-wordpress'), 'error');
                }
            }

            // Create new Contact List
            $create_list_name = get_option('create_list_name');
            if (!empty($create_contact_list_btn)) {
                if (!empty($create_list_name)) {
                    $createListResponse = MailjetApi::createMailjetContactList(get_option('create_list_name'));

                    if ($createListResponse->success()) {
                        add_settings_error('mailjet_messages', 'mailjet_message', __('Congratulations! You have just created a new contact list!', 'mailjet-for-wordpress'), 'updated');
                    } else {
                        $executionError = true;
                        update_option('contacts_list_ok', 0);

                        $createListResponseBody = $createListResponse->getBody();
                        if (isset($createListResponseBody['ErrorMessage']) && stristr($createListResponseBody['ErrorMessage'], 'already exists')) {
                            add_settings_error('mailjet_messages', 'mailjet_message', sprintf(__('A contact list with name <b>%s</b> already exists', 'mailjet-for-wordpress'), $create_list_name), 'error');
                        } else {
                            $executionError = true;
                            update_option('contacts_list_ok', 0);

                            add_settings_error('mailjet_messages', 'mailjet_message', __('The settings could not be saved. Please try again or in case the problem persists contact Mailjet support.', 'mailjet-for-wordpress'), 'error');
                        }
                    }
                } else { // New list name empty
                    $executionError = true;
                    add_settings_error('mailjet_messages', 'mailjet_message', __('Please enter a valid contact list name', 'mailjet-for-wordpress'), 'error');
                }
            } else {
                $applyAndContinueBtnClicked = true;
            }

            if (false === $executionError) {
                update_option('contacts_list_ok', 1);

                // add settings saved message with the class of "updated"
                add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet-for-wordpress'), 'updated');
                $contacts_list_ok = get_option('contacts_list_ok');
                if (!($fromPage == 'plugins') || (!empty($contacts_list_ok) && '1' == $contacts_list_ok)) {

                    // Redirect if the create contact button is not set
                    if (empty($create_contact_list_btn)) {
                        MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_allsetup_page'));
                    }
                }
            }
        }
        $contacts_list_ok = get_option('contacts_list_ok');
        $skipped = get_option('skip_mailjet_list');
        if (!($fromPage == 'plugins') && (!empty($contacts_list_ok) && '1' == $contacts_list_ok) && $skipped !== '') {
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_dashboard_page'));
        }

        // show error/update messages
        settings_errors('mailjet_messages');
        ?>

        <div class="mj-pluginPage">
            <div id="initialSettingsHead"><img
                        src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/LogoMJ_White_RVB.svg'; ?>"
                        alt="Mailjet Logo"/></div>
            <div class="mainContainer">

                <!--                <div>
                                    <h1 class="page_top_title"><?php _e('Welcome to the Mailjet plugin for WordPress', 'mailjet-for-wordpress'); ?> </h1>
                                    <p class="page_top_subtitle">
                <?php _e('Mailjet is an email service provider. With this plugin, easily send newsletters to your website users, directly from WordPress.', 'mailjet-for-wordpress'); ?>
                                    </p>
                                </div>-->

                <div id="initialContactListsForm">
                    <form action="options.php" method="post">
                        <input id="skip_mailjet_list" type="hidden" name="skip_mailjet_list" value="0">
                        <input id="activate_mailjet_sync" type="hidden" name="activate_mailjet_sync" value="1">
                        <?php
                        // output security fields for the registered setting "mailjet"
                        settings_fields('mailjet_initial_contact_lists_page');
                        // output setting sections and their fields
                        // (sections are registered for "mailjet", each field is registered to a specific section)
                        do_settings_sections('mailjet_initial_contact_lists_page');
                        // output save settings button
                        if (MailjetApi::isValidAPICredentials()) {
                            ?>
                            <button type="submit" id="initialContactListsSubmit" onclick="activateMjSync()" class="mj-btn btnPrimary MailjetSubmit"
                                    name="submit"><?= __('Apply & Continue', 'mailjet-for-wordpress'); ?></button>
                            <?php
                        } else {
                            update_option('settings_step', 'initial_step')
                            ?>
                            <input name="nextBtn" class="mj-btn btnPrimary nextBtn" type="button" id="nextBtn"
                                   onclick="location.href = 'admin.php?page=mailjet_settings_page'"
                                   value="<?= __('Back', 'mailjet-for-wordpress') ?>">
                        <?php }
                        ?>

                        <?php if ($applyAndContinueBtnClicked){ ?>

                            <input name="nextBtn" class="mj-btn btnSecondary nextBtn" type="button" id="nextBtn"  onclick="location.href = 'admin.php?page=mailjet_allsetup_page'"
                                   value="<?php _e('Next', 'mailjet-for-wordpress'); ?>">
                        <?php }else{?>
                            <input name="nextBtn" class="mj-btn btnSecondary nextBtn" type="submit" id="nextBtn"  onclick="skipMailjetSync()"
                                   value="<?php _e('Skip this step', 'mailjet-for-wordpress'); ?>">
                        <?php }?>
                        <br/>
                    </form>
                </div>

            </div>
            <script>
                function activateMjSync() {
                    document.getElementById('activate_mailjet_sync').value = 1;
                }

                function skipMailjetSync() {
                    document.getElementById('skip_mailjet_list').value = '1';
                    document.getElementById('activate_mailjet_sync').value = '0';
                    document.getElementById('mailjet_sync_list').value = '';
                }
            </script>
            <?php
            MailjetAdminDisplay::renderBottomLinks();
            ?>
        </div>

        <?php
    }

}
