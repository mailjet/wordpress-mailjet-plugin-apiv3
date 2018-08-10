<?php

namespace MailjetPlugin\Includes\SettingsPages;

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
class SubscriptionOptionsSettings
{

    public function mailjet_section_subscription_options_cb($args)
    {echo get_option('settings_step');
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Subscription options', 'mailjet' ); ?></p>
        <?php
    }


    public function mailjet_subscription_options_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $allWpUsers = get_users(array('fields' => array('ID', 'user_email')));
        $wpUsersCount = count($allWpUsers);
        $mailjetContactLists = $this->getMailjetContactLists();
        $mailjetSyncActivated = get_option('activate_mailjet_sync');
        $mailjetInitialSyncActivated = get_option('activate_mailjet_initial_sync');
        $mailjetSyncList = get_option('mailjet_sync_list');
        $mailjetCommentAuthorsList = get_option('mailjet_comment_authors_list');
        $mailjetCommentAuthorsSyncActivated = get_option('activate_mailjet_comment_authors_sync');


        // output the field
        ?>

        <fieldset>
            <legend class="screen-reader-text"><span><?php echo  __('Automatically add Wordpress users to a specific list', 'mailjet'); ?></span></legend>
            <label for="activate_mailjet_sync">
            <input name="activate_mailjet_sync" type="checkbox" id="activate_mailjet_sync" value="1" <?=($mailjetSyncActivated == 1 ? ' checked="checked"' : '') ?> >
            <?php echo __('Automatically add all my future Wordpress users to a specific contact list', 'mailjet'); ?></label>
            <br />

            <div class="mailjet_sync_options_div">
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
            </div>

<hr>


            <label for="activate_mailjet_comment_authors_sync">
            <input name="activate_mailjet_comment_authors_sync" type="checkbox" id="activate_mailjet_comment_authors_sync" value="1" <?=($mailjetCommentAuthorsSyncActivated == 1 ? ' checked="checked"' : '') ?> >
            <?php echo __('Display "Subscribe to our mailjet list" checkbox in the "Leave a reply" form to allow comment authors to join a specific contact list)', 'mailjet'); ?></label>
            <br />

            <div class="mailjet_sync_comment_authors_div">
                <select name="mailjet_comment_authors_list" id="mailjet_comment_authors_list" type="select">
                    <?php
                    foreach ($mailjetContactLists as $mailjetContactList) {
                        if ($mailjetContactList["IsDeleted"] == true) {
                            continue;
                        }
                        ?>
                        <option value="<?=$mailjetContactList['ID'] ?>" <?=($mailjetCommentAuthorsList == $mailjetContactList['ID'] ? 'selected="selected"' : '') ?> > <?=$mailjetContactList['Name'] ?> (<?=$mailjetContactList['SubscriberCount'] ?>) </option>
                        <?php
                    } ?>
                </select>
                <br />
            </div>

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

            // Initial sync WP users to Mailjet
            if (!empty(get_option('activate_mailjet_initial_sync')) && intval(get_option('mailjet_sync_list')) > 0) {
                $syncResponse = $this->syncAllWpUsers();
            }

            // add settings saved message with the class of "updated"
            add_settings_error('mailjet_messages', 'mailjet_message', __('Settings Saved', 'mailjet'), 'updated');
        }

        // show error/update messages
        settings_errors('mailjet_messages');


        ?>

        <div class="split left">
            <div class="centered">
                <?php
                    MailjetAdminDisplay::getSettingsLeftMenu();
                ?>
            </div>
        </div>

        <div class="split right">
            <div class="centered">
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
                        submit_button('Save');
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

        $responseSenders = $mjApiClient->get(\Mailjet\Resources::$Contactslist);
        if ($responseSenders->success()) {
            return $responseSenders->getData();
        } else {
            return $responseSenders->getStatus();
        }

    }



    public function syncAllWpUsers()
    {
        if (empty(get_option('mailjet_sync_list'))) {
            add_settings_error('mailjet_messages', 'mailjet_message', __('Select a Mailjet contact list to add Wordpress users to', 'mailjet'), 'error');
            return false;
        }
        $contactListId = get_option('mailjet_sync_list');

        $users = get_users(array('fields' => array('ID', 'user_email')));
        if (!(count($users) > 0)) {
            add_settings_error('mailjet_messages', 'mailjet_message', __('No Wordpress users to add to Mailjet contact list', 'mailjet'), 'error');
            return false;
        }

        if (false === self::syncContactsToMailjetList($contactListId, $users, 'addnoforce')) {
            add_settings_error('mailjet_messages', 'mailjet_message', __('Something went wrong with adding existing Wordpress users to your Mailjet contact list', 'mailjet'), 'error');
        } else {
            add_settings_error('mailjet_messages', 'mailjet_message', __('All Wordpress users were succesfully added to your Mailjet contact list', 'mailjet'), 'updated');
        }

    }


    /**
     * Add or Remove a contact to Mailjet contact list
     *
     * @param $contactListId
     * @param $users - can be array of users or a single user
     * @param $action - addnoforce, addforce, remove
     * @return array|bool|int
     */
    public static function syncContactsToMailjetList($contactListId, $users, $action)
    {
        $contacts = array();

        if (!is_array($users)) {
            $users = array($users);
        }

        foreach ($users as $user) {
            $userInfo = get_userdata($user->ID);
            $userRoles = $userInfo->roles;
            $userMetadata = get_user_meta($user->ID);

            $contactProperties = array();
            if (!empty($userMetadata['first_name'][0])) {
                $contactProperties['first_name'] = $userMetadata['first_name'][0];
            }
            if (!empty($userMetadata['last_name'][0])) {
                $contactProperties['last_name'] = $userMetadata['last_name'][0];
            }
            if (!empty($userRoles[0])) {
                $contactProperties['wp_user_role'] = $userRoles[0];
            }

            $contacts[] = array(
                'Email' => $user->user_email,
                'Name' => $contactProperties['first_name'] . ' ' . $contactProperties['last_name'],
                'Properties' => $contactProperties
            );
        }

        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        $mjApiClient = new \Mailjet\Client($mailjetApikey, $mailjetApiSecret);

        $body = [
            'Action' => $action,
            'Contacts' => $contacts
        ];

        $responseSenders = $mjApiClient->post(\Mailjet\Resources::$ContactslistManagemanycontacts, ['id' => $contactListId, 'body' => $body]);
        if ($responseSenders->success()) {
            return $responseSenders->getData();
        } else {
            return false;
//            return $responseSenders->getStatus();
        }

        return false;
    }


    public static function syncSingleContactEmailToMailjetList($contactListId, $email, $action)
    {
        $contacts = array();

        if (empty($email)) {
            return false;
        }

        $contacts[] = array(
            'Email' => $email
        );

        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        $mjApiClient = new \Mailjet\Client($mailjetApikey, $mailjetApiSecret);

        $body = [
            'Action' => $action,
            'Contacts' => $contacts
        ];

        $responseSenders = $mjApiClient->post(\Mailjet\Resources::$ContactslistManagemanycontacts, ['id' => $contactListId, 'body' => $body]);
        if ($responseSenders->success()) {
            return $responseSenders->getData();
        } else {
            return false;
//            return $responseSenders->getStatus();
        }

        return false;
    }
}
