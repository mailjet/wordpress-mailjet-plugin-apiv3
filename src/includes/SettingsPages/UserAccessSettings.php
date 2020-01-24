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
    const PREFIX_ACCESS_INPUT_NAME = 'mailjet_access_';
    const ACCESS_CAP_NAME = 'mailjet_plugin_access';

    public function mailjet_section_user_access_cb($args)
    {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php echo __('Select which WordPress user roles (in addition to Administrator) will also have access to the Mailjet Plugin', 'mailjet-for-wordpress'); ?>
        </p>
        <?php
    }

    private function getRoles() {
        return get_editable_roles();
    }

    public function mailjet_user_access_cb($args)
    {
        $roles = $this->getRoles();

        ?>

        <fieldset class="settingsAccessFldset">
            <legend class="screen-reader-text"><span><?php  _e('User Access', 'mailjet-for-wordpress'); ?></span></legend>
            <?php foreach ($roles as $roleKey => $role) {
                $hasAccess = isset($role['capabilities'][self::ACCESS_CAP_NAME]) && $role['capabilities'][self::ACCESS_CAP_NAME];
            ?>
                <label class="checkboxLabel" for="<?php echo self::PREFIX_ACCESS_INPUT_NAME . $roleKey ?>">
                    <input name="<?php echo self::PREFIX_ACCESS_INPUT_NAME . $roleKey ?>" type="checkbox" id="<?php echo self::PREFIX_ACCESS_INPUT_NAME . $roleKey ?>" value="1" <?php echo ($hasAccess ? ' checked="checked"' : ''); echo ($roleKey === 'administrator' ? 'disabled' : '') ?> >
                    <span><?php _e($role['name'], 'mailjet-for-wordpress'); ?></span>
                </label>
            <?php } ?>
        </fieldset>

        <input name="settings_step" type="hidden" id="settings_step" value="user_access_step">

        <?php
    }

    public function user_access_post_handler() {
        $postData = $_POST;

        if (!isset($postData['custom_nonce']) || !wp_verify_nonce($postData['custom_nonce'], 'mailjet_user_access_page_html')){
            add_settings_error( 'mailjet_messages', 'mailjet_message', __( 'Your permissions don\'t match! Please refresh your session and if the problem persists, contact our support team.', 'mailjet-for-wordpress' ), 'error' );
            settings_errors( 'mailjet_messages' );
            exit;
        }

        foreach ($this->getRoles() as $roleKey => $role) {
            if ($roleKey === 'administrator') {
                continue;
            }
            $hasAccess = isset($role['capabilities'][self::ACCESS_CAP_NAME]) && $role['capabilities'][self::ACCESS_CAP_NAME];
            $inputName = self::PREFIX_ACCESS_INPUT_NAME . $roleKey;
            if ($hasAccess) {
                if (!isset($postData[$inputName])) {
                    $role = get_role($roleKey);
                    $role->remove_cap(self::ACCESS_CAP_NAME);
                }
            }
            else {
                if (isset($postData[$inputName])) {
                    $role = get_role($roleKey);
                    $role->add_cap(self::ACCESS_CAP_NAME, true);
                }
            }
        }

        wp_redirect(add_query_arg(array('page' => 'mailjet_user_access_page'), admin_url('admin.php')));
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

        $nonce = wp_create_nonce('mailjet_user_access_page_html');

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
                            <h2 class="section_inner_title"><?php _e('User access', 'mailjet-for-wordpress'); ?></h2>
                            <form action="<?php echo esc_url(admin_url('admin-post.php')) ?>" method="POST">
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
                                <input type="hidden" name="action" value="user_access_settings_custom_hook">
                                <input type="hidden" name="custom_nonce" value="<?= $nonce?>">
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

    /**
     * Never call !
     * Useful to register the dynamic translations to the po files
     */
    private function registerRoleTranslations() {
        __('Administrator', 'mailjet-for-wordpress');
        __('Editor', 'mailjet-for-wordpress');
        __('Author', 'mailjet-for-wordpress');
        __('Contributor', 'mailjet-for-wordpress');
        __('Subscriber', 'mailjet-for-wordpress');
    }
}
