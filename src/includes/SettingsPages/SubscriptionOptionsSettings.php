<?php

namespace MailjetPlugin\Includes\SettingsPages;

use Analog\Handler\Mail;
use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\MailjetApi;
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
class SubscriptionOptionsSettings
{

    const WIDGET_HASH = '[\^=34|>5i!? {xIas';

    public function mailjet_section_subscription_options_cb($args)
    {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e( 'Automatically add Wordpress subscribers to a specific list', 'mailjet' ); ?>
        </p>
        <?php
    }


    public function mailjet_subscription_options_cb($args)
    {
        // get the value of the setting we've registered with register_setting()
        $allWpUsers = get_users(array('fields' => array('ID', 'user_email')));
        $wpUsersCount = count($allWpUsers);
        $mailjetContactLists = MailjetApi::getMailjetContactLists();
        $mailjetContactLists = !empty($mailjetContactLists) ? $mailjetContactLists : array();
        $mailjetSyncActivated = get_option('activate_mailjet_sync');
        $mailjetInitialSyncActivated = get_option('activate_mailjet_initial_sync');
        $mailjetSyncList = get_option('mailjet_sync_list');
        $mailjetCommentAuthorsList = get_option('mailjet_comment_authors_list');
        $mailjetCommentAuthorsSyncActivated = get_option('activate_mailjet_comment_authors_sync');


        // output the field
        ?>

        <fieldset>
            <legend class="screen-reader-text"><span><?php echo  __('Automatically add Wordpress subscribers to a specific list', 'mailjet'); ?></span></legend>

            <div>
                <label>
                    <span style="display: table-cell;">
                        <input name="activate_mailjet_sync" type="checkbox" id="activate_mailjet_sync" value="1" <?=($mailjetSyncActivated == 1 ? ' checked="checked"' : '') ?> >
                    </span>
                    <span style="display: table-cell;text-align: justify;"><?php echo __('Automatically add all my future Wordpress subscribers to a specific contact list', 'mailjet'); ?></span>
                </label>
            </div>
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
                <br /><br />

                <div>
                    <label>
                    <span style="display: table-cell;">
                        <input name="activate_mailjet_initial_sync" type="checkbox" id="activate_mailjet_initial_sync" style="vertical-align: middle;" value="1" <?=($mailjetInitialSyncActivated == 1 ? ' checked="checked"' : '') ?> >
                    </span>
                        <span style="display: table-cell;text-align: justify;"><?php echo sprintf(__('Also, add existing <b>%s Wordpress users</b> (initial synchronization)', 'mailjet'), $wpUsersCount); ?></span>
                    </label>
                </div>
            </div>
<hr>


            <div>
                <label>
                    <span style="display: table-cell;">
                        <input name="activate_mailjet_comment_authors_sync" type="checkbox" id="activate_mailjet_comment_authors_sync" style="vertical-align: middle;" value="1" <?=($mailjetCommentAuthorsSyncActivated == 1 ? ' checked="checked"' : '') ?> >
                    </span>
                    <span style="display: table-cell;text-align: justify;"><?php echo __('Display "Subscribe to our mailjet list" checkbox in the "Leave a reply" form to allow comment authors to join a specific contact list', 'mailjet'); ?></span>
                </label>
            </div>
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
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        // register a new section in the "mailjet" page
        add_settings_section(
            'mailjet_subscription_options_settings',
            null,
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
            $executionError = false;
            // Initial sync WP users to Mailjet
            if (!empty(get_option('activate_mailjet_initial_sync')) && intval(get_option('mailjet_sync_list')) > 0) {
                $syncResponse = self::syncAllWpUsers();
                if (false === $syncResponse) {
                    $executionError = true;
                    add_settings_error('mailjet_messages', 'mailjet_message', __('The settings could not be saved. Please try again or in case the problem persists contact Mailjet support.', 'mailjet'), 'error');
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
        <div class="mainContainer mjSettings">
            <div class="left">
                <div class="centered">
                    <?php
                    MailjetAdminDisplay::getSettingsLeftMenu();
                    ?>
                </div>
            </div>

            <div class="right">
                <div class="centered">
<!--                    <h1>--><?php //echo esc_html(get_admin_page_title()); ?><!--</h1>-->
                    <h2><?php echo __('Subscription options', 'mailjet'); ?></h2>
                    <form action="options.php" method="post">
                        <?php
                        // output security fields for the registered setting "mailjet"
                        settings_fields('mailjet_subscription_options_page');
                        // output setting sections and their fields
                        // (sections are registered for "mailjet", each field is registered to a specific section)
                        do_settings_sections('mailjet_subscription_options_page');
                        // output save settings button
                        submit_button('Save', 'MailjetSubmit', 'submit', false, array('id' => 'subscriptionOptionsSubmit'));
                        ?>
                        <input name="cancelBtn" class="cancelBtn" type="button" id="cancelBtn" onClick="location.href=location.href" value="<?=__('Cancel', 'mailjet')?>">
                    </form>
                </div>
            </div>
        </div>

        <div class="bottom_links">
            <div class="needHelpDiv">
                <img src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/need_help.png'; ?>" alt="<?php echo __('Need help?', 'mailjet'); ?>" />
                <?php echo __('Need help?', 'mailjet' ); ?>
            </div>
            <?php echo '<a target="_blank" href="' . Mailjeti18n::getMailjetUserGuideLinkByLocale() . '">' . __('Read our user guide', 'mailjet') . '</a>'; ?>
            <?php echo '<a target="_blank" href="' . Mailjeti18n::getMailjetSupportLinkByLocale() . '">' . __('Contact our support team', 'mailjet') . '</a>'; ?>
        </div>

        <?php
    }


    public function syncAllWpUsers()
    {
        if (empty(get_option('mailjet_sync_list'))) {
            add_settings_error('mailjet_messages', 'mailjet_message', __('Please select a contact list.', 'mailjet'), 'error');
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
            return false;
        } else {
            add_settings_error('mailjet_messages', 'mailjet_message', __('All Wordpress users were successfully added to your Mailjet contact list', 'mailjet'), 'updated');
        }
        return true;
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
            $userNames = '';

            $contactProperties = array();
            if (!empty($userMetadata['first_name'][0])) {
                $contactProperties['first_name'] = $userMetadata['first_name'][0];
                $userNames = $contactProperties['first_name'];
            }
            if (!empty($userMetadata['last_name'][0])) {
                $contactProperties['last_name'] = $userMetadata['last_name'][0];
                $userNames.= ' ' . $contactProperties['last_name'];
            }
            if (!empty($userRoles[0])) {
                $contactProperties['wp_user_role'] = $userRoles[0];
            }

            $contacts[] = array(
                'Email' => $user->user_email,
                'Name' => $userNames,
                'Properties' => $contactProperties
            );
        }

        return MailjetApi::syncMailjetContacts($contactListId, $contacts, $action);
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

        return MailjetApi::syncMailjetContacts($contactListId, $contacts, $action);
    }




    /**
     *  Adding checkboxes and extra fields for subscribing user and comment authors
     */
    public function mailjet_show_extra_profile_fields($user)
    {
        // If contact list is not selected, then do not show the extra fields
        if (!empty(get_option('activate_mailjet_sync')) && !empty(get_option('mailjet_sync_list'))) {
            // Update the extra fields
            if (is_object($user) && intval($user->ID) > 0) {
                $this->mailjet_subscribe_unsub_user_to_list(esc_attr(get_the_author_meta('mailjet_subscribe_ok', $user->ID)), $user->ID);
            }
            ?>
            <label for="admin_bar_front">
                <input type="checkbox" name="mailjet_subscribe_ok" id="mailjet_subscribe_ok" value="1"
                    <?php echo(is_object($user) && intval($user->ID) > 0 && esc_attr(get_the_author_meta('mailjet_subscribe_ok', $user->ID)) ? 'checked="checked" ' : ''); ?>
                       class="checkbox" /> <?php _e('Subscribe to our mailing list', 'mailjet') ?></label>
            </br>
            <?php
        }
    }


    /**
     *  Update extra profile fields when the profile is saved
     */
    public function mailjet_save_extra_profile_fields($user_id)
    {
        $subscribe = filter_var($_POST ['mailjet_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);
        update_user_meta($user_id, 'mailjet_subscribe_ok', $subscribe);
        $this->mailjet_subscribe_unsub_user_to_list($subscribe, $user_id);
    }


    /**
     *  Subscribe or unsubscribe a wordpress user (admin, editor, etc.) in/from a Mailjet's contact list when the profile is saved
     */
    public function mailjet_subscribe_unsub_user_to_list($subscribe, $user_id)
    {
        if (!empty(get_option('mailjet_sync_list'))) {
            $user = get_userdata($user_id);
            $action = intval($subscribe) === 1 ? 'addforce' : 'remove';
            // Add the user to a contact list
            if (false == SubscriptionOptionsSettings::syncContactsToMailjetList(get_option('mailjet_sync_list'), $user, $action)) {
                return false;
            } else {
                return true;
            }
        }
    }



    public function mailjet_show_extra_comment_fields($user)
    {
        $user = wp_get_current_user();
        // Display the checkbox only for NOT-logged in users
        if (!$user->exists() && get_option('mailjet_comment_authors_list')) {
            ?>
            <label for="admin_bar_front">
                <input type="checkbox" name="mailjet_comment_authors_subscribe_ok" id="mailjet_comment_authors_subscribe_ok" value="1" class="checkbox" />
                <?php _e('Subscribe to our mailing list', 'mailjet') ?>
            </label>
            <br>
            <?php
        }
    }


    public function mailjet_subscribe_comment_author($id)
    {
        $comment = get_comment($id);
        $authorEmail = filter_var($comment->comment_author_email, FILTER_SANITIZE_EMAIL);
        $userId = filter_var($comment->user_id, FILTER_SANITIZE_NUMBER_INT);

        if (!is_email($authorEmail)) {
            _e('Invalid email', 'mailjet');
            die;
        }

        $subscribe = filter_var($_POST['mailjet_comment_authors_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);
        $this->mailjet_subscribe_confirmation_from_comment_form($subscribe, $authorEmail);
    }



    /**
     *  Subscribe or unsubscribe a wordpress comment author in/from a Mailjet's contact list when the comment is saved
     */
    public function mailjet_subscribe_unsub_comment_author_to_list($subscribe, $user_email)
    {
        $action = intval($subscribe) === 1 ? 'addforce' : 'remove';
        // Add the user to a contact list
        return SubscriptionOptionsSettings::syncSingleContactEmailToMailjetList(get_option('mailjet_comment_authors_list'), $user_email, $action);
    }



    /**
     * Email the collected widget data to the customer with a verification token
     * @param void
     * @return void
     */
    public function mailjet_subscribe_confirmation_from_comment_form($subscribe, $user_email)
    {
        $error = empty($user_email) ? 'Email field is empty' : false;
        if (false !== $error) {
            _e($error, 'mailjet');
            die;
        }

        if (!is_email($user_email)) {
            _e('Invalid email', 'mailjet');
            die;
        }
        $wpUrl = sprintf('<a href="%s" target="_blank">%s</a>', get_home_url(), get_home_url());
        $message = file_get_contents(dirname(dirname(dirname(__FILE__))) . '/templates/confirm-subscription-email.php');
        $emailParams = array(
            '__EMAIL_TITLE__' => __('Please confirm your subscription', 'mailjet'),
            '__EMAIL_HEADER__' => sprintf(__('To receive newsletters from %s please confirm your subscription by clicking the following button:', 'mailjet'), $wpUrl),
            '__WP_URL__' => $wpUrl,
            '__CONFIRM_URL__' => get_home_url() . '?subscribe=' . $subscribe . '&user_email=' . $user_email . '&mj_sub_comment_author_token=' . sha1($subscribe . $user_email),
            '__CLICK_HERE__' => __('Yes, subscribe me to this list', 'mailjet'),
            '__FROM_NAME__' => get_option('blogname'),
            '__IGNORE__' => __('If you received this email by mistake or don\'t wish to subscribe anymore, simply ignore this message.', 'mailjet'),
        );
        foreach ($emailParams as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        
        $email_subject = __('Subscription Confirmation', 'mailjet');
        add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
        wp_mail($_POST['email'], $email_subject, $message,
            array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
    }
    
    public function mailjet_subscribe_confirmation_from_widget($subscription_email, $instance)
    {
        $homeUrl = get_home_url();
        $language = Mailjeti18n::getCurrentUserLanguage();
        $thankYouPageId = !empty($instance[$language]['thank_you']) ? $instance[$language]['thank_you'] : false;
        $post = get_post( $thankYouPageId );
        $thankYouURI = !empty($post->guid) ? $post->guid : $homeUrl;
        $locale = \MailjetPlugin\Includes\Mailjeti18n::getLocale();

        $email_subject = !empty($instance[$locale]['email_subject']) ? apply_filters('widget_email_subject', $instance[$locale]['email_subject']) : __('Subscription Confirmation', 'mailjet');
        $email_title = !empty($instance[$locale]['email_content_title']) ? apply_filters('widget_email_content_title', $instance[$locale]['email_content_title']) : __('Please confirm your subscription', 'mailjet');
        $email_button_value = !empty($instance[$locale]['email_content_confirm_button']) ? apply_filters('widget_email_content_confirm_button', $instance[$locale]['email_content_confirm_button']) : __('Yes, subscribe me to this list', 'mailjet');
        $wpUrl = sprintf('<a href="%s" target="_blank">%s</a>', $homeUrl, $homeUrl);
        $test = sprintf(__('To receive newsletters from %s please confirm your subscription by clicking the following button:', 'mailjet'), $wpUrl);
        $email_main_text = !empty($instance[$locale]['email_content_main_text']) ? apply_filters('widget_email_content_main_text', $instance[$locale]['email_content_main_text']) : $test;

        $params = http_build_query(array(
            'subscription_email' => $subscription_email,
            'properties' => $_POST['properties'],
//            'thank_id' => $thankYouURI
        ));
        $subscriptionTemplate = apply_filters('mailjet_confirmation_email_filename', dirname(dirname(dirname(__FILE__))) . '/templates/confirm-subscription-email.php');
        $message = file_get_contents($subscriptionTemplate);

        // Check if subscription is done via home page or some post
        $confirmUrl = $thankYouURI == $homeUrl ? '?' : '&';

        $emailData = array(
            '__EMAIL_TITLE__' => $email_title,
            '__EMAIL_HEADER__' => $email_main_text,
            '__WP_URL__' => $homeUrl,
            '__CONFIRM_URL__' => $thankYouURI . $confirmUrl . $params . '&mj_sub_token=' . sha1($params . self::WIDGET_HASH),
            '__CLICK_HERE__' => $email_button_value,
            '__FROM_NAME__' => get_option('blogname'),
            '__IGNORE__' => __('If you received this email by mistake or don\'t wish to subscribe anymore, simply ignore this message.', 'mailjet'),
        );
        $emailParams = apply_filters('mailjet_subscription_widget_email_params', $emailData);
        foreach ($emailParams as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
        return wp_mail($subscription_email, $email_subject, $message, array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
//        echo '<p class="success">' . __('Subscription confirmation email sent. Please check your inbox and confirm the subscription.', 'mailjet') . '</p>';
//        die;
    }

}
