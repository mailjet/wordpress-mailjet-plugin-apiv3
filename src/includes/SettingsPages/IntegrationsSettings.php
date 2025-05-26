<?php

namespace MailjetWp\MailjetPlugin\Includes\SettingsPages;

use MailjetWp\MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetWp\MailjetPlugin\Includes\Mailjet;
use MailjetWp\MailjetPlugin\Includes\MailjetApi;
use MailjetWp\MailjetPlugin\Includes\MailjetLogger;

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
class IntegrationsSettings {

    public function mailjet_section_integrations_cb( $args ) {
    }

    /**
     * @param $mailjetSyncContactList
     * @return void
     */
    private function wooIntegration( $mailjetSyncContactList ) {
        $wooCommerceNotInstalled = \false;
        // One can also check for `if (defined('WC_VERSION')) { // WooCommerce installed }`
        if ( ! \class_exists('WooCommerce')) {
            delete_option('activate_mailjet_woo_integration');
            delete_option('mailjet_woo_edata_sync');
            delete_option('mailjet_woo_checkout_checkbox');
            delete_option('mailjet_woo_checkout_box_text');
            delete_option('mailjet_woo_register_checkbox');
            delete_option('mailjet_woo_register_box_text');
            delete_option('mailjet_woo_banner_checkbox');
            delete_option('mailjet_woo_banner_text');
            delete_option('mailjet_woo_banner_label');
            delete_option('mailjet_woo_abandoned_cart_activate');
            delete_option('mailjet_woo_abandoned_cart_sending_time');
            $wooCommerceNotInstalled = \true;
        }
        $mailjetWooSyncActivated        = Mailjet::getOption('mailjet_woo_edata_sync');
        $mailjetWooIntegrationActivated = Mailjet::getOption('activate_mailjet_woo_integration');
        $checkoutCheckbox               = Mailjet::getOption('mailjet_woo_checkout_checkbox');
        if ($checkoutCheckbox !== '1') {
            delete_option('mailjet_woo_checkout_box_text');
        }
        $checkoutCheckboxText = \stripslashes(Mailjet::getOption('mailjet_woo_checkout_box_text'));
        $registerCheckbox     = Mailjet::getOption('mailjet_woo_register_checkbox');
        if ($registerCheckbox !== '1') {
            delete_option('mailjet_woo_register_box_text');
        }
        $registerCheckboxText = \stripslashes(Mailjet::getOption('mailjet_woo_register_box_text'));
        $bannerCheckbox       = Mailjet::getOption('mailjet_woo_banner_checkbox');
        if ($bannerCheckbox !== '1') {
            delete_option('mailjet_woo_banner_text');
            delete_option('mailjet_woo_banner_label');
        }
        $bannerText                     = \stripslashes(Mailjet::getOption('mailjet_woo_banner_text'));
        $bannerLabel                    = \stripslashes(Mailjet::getOption('mailjet_woo_banner_label'));
        $isSyncListSelected             = ! empty($mailjetSyncContactList);
        $mailjetSyncContactListName     = $isSyncListSelected ? $mailjetSyncContactList['Name'] . '(' . $mailjetSyncContactList['SubscriberCount'] . ')' : 'No list selected';
        $syncNotActivatedTooltipMessage = __('To use this option you need to select a contact list', 'mailjet-for-wordpress');
        ?>
        <fieldset class="settingsSubscrFldset">
            <span class="mj-integrations-label">
            <?php
            _e('WooCommerce', 'mailjet-for-wordpress');
            ?>
            </span>
            <label class="mj-switch">
                <input name="woocommerce[activate_mailjet_woo_integration]" value="1" id="activate_mailjet_woo_integration"
                        type="checkbox" 
                        <?php
                        echo esc_attr($mailjetWooIntegrationActivated) === '1' ? 'checked="checked"' : '';
                        ?>
                        <?php
                        echo esc_attr($wooCommerceNotInstalled) === \true ? ' disabled="disabled"' : '';
                        ?>
                >
                <span class="mj-slider mj-round"></span>
            </label>
            <div id="activate_mailjet_woo_form" class="
            <?php
            echo esc_attr($mailjetWooIntegrationActivated) === '1' ? ' mj-show' : 'mj-hide';
            ?>
            ">
                <div class="mj-woocommerce-contacts">
                    <?php
                    _e('WooCommerce contacts will be automatically synced to your Mailjet list (with a “customer” contact property).', 'mailjet-for-wordpress');
                    ?>
                </div>
                <div id="woo_contact_list">
                    <label class="mj-contact-list">
                    <?php
                    _e('Contact List', 'mailjet-for-wordpress');
                    ?>
                    </label>
                    <div>
                        <?php
                        echo esc_attr($mailjetSyncContactListName);
                        ?>
                        <span class="dashicons dashicons-editor-help tooltip">
                            <span class="tooltiptext">
                            <?php
                            _e('You can change list inside Settings > Subscription options.', 'mailjet-for-wordpress');
                            ?>
                            </span>
                        </span>
                    </div>
                </div>
                <div id="woo_contact_list">
                    <label >
                    <?php
                    _e('Ecommerce customer data', 'mailjet-for-wordpress');
                    ?>
                    </label>
                    <label class="checkboxLabel">
                        <span 
                        <?php
                        echo esc_attr($isSyncListSelected) ? '' : 'class="tooltip"';
                        ?>
                        >
                            <input name="woocommerce[mailjet_woo_edata_sync]" type="checkbox"
                                    value="1" 
                                    <?php
                                    echo esc_attr($mailjetWooSyncActivated) === '1' ? ' checked="checked"' : '';
                                    ?>
                                    <?php
									echo esc_attr($wooCommerceNotInstalled) == \true ? ' disabled' : '';
									?>
                                    autocomplete="off" 
                                    <?php
                                    echo esc_attr($isSyncListSelected) ? '' : 'disabled';
                                    ?>
                                    >
                            <?php
                            if ( ! $isSyncListSelected) {
                                ?>
                                <span class="tooltiptext">
                                <?php
								echo esc_attr($syncNotActivatedTooltipMessage);
								?>
</span>
								<?php
                            }
                            ?>
                        </span>
                        <span>
                        <?php
                        _e('Import e-commerce data for all synced customers (total orders count, total spent, account creation date, last order date) and store it as a contact property inside Mailjet. This will allow you to segment your list and personalise your email content and sending.', 'mailjet-for-wordpress');
                        ?>
                        </span>
                    </label>
                </div>
                <div id="woo_contact_list">
                    <label >
                    <?php
                    _e('Opti-in inside checkout', 'mailjet-for-wordpress');
                    ?>
                    </label>
                    <div class="mj-woocommerce-contacts" style="margin-bottom: 20px;">
                        <?php
                        _e('WooCommerce contacts will be automatically synced to your Mailjet list (with a “customer” contact property).', 'mailjet-for-wordpress');
                        ?>
                    </div>
                    <label class="checkboxLabel">
                        <span 
                        <?php
                        echo esc_attr($isSyncListSelected) ? '' : 'class="tooltip"';
                        ?>
                        >
                            <input name="woocommerce[mailjet_woo_checkout_checkbox]" id="activate_mailjet_woo_checkbox" type="checkbox"
                                value="1" 
                                <?php
                                echo esc_attr($checkoutCheckbox) === '1' ? ' checked="checked"' : '';
                                ?>
                                <?php
								echo esc_attr($wooCommerceNotInstalled) == \true ? ' disabled' : '';
								?>
                                autocomplete="off" 
                                <?php
                                echo esc_attr($isSyncListSelected) ? '' : 'disabled';
                                ?>
                                >
                            <?php
                            if ( ! $isSyncListSelected) {
                                ?>
                                <span class="tooltiptext">
                                <?php
								echo esc_attr($syncNotActivatedTooltipMessage);
								?>
</span>
								<?php
                            }
                            ?>
                        </span>
                        <span>
                        <?php
                        _e('Activate opt-in checkbox inside checkout page.', 'mailjet-for-wordpress');
                        ?>
                        </span>
                    </label>
                    <div id="mailjet_woo_sub_letter" class="
                    <?php
                    echo esc_attr($checkoutCheckbox) === '1' ? ' mj-show' : 'mj-hide';
                    ?>
                    mj-text-div">
                        <label  class="mailjet-label" for="sub_letter">
                        <?php
                        echo __('Checkbox text', 'mailjet-for-wordpress');
                        ?>
                        </label>
                        <input name="woocommerce[mailjet_woo_checkout_box_text]" id="sub_letter" type="text" value="<?php echo esc_attr($checkoutCheckboxText);?>" class="mj-text-field" placeholder="<?php echo __('Subscribe to our newsletter', 'mailjet-for-wordpress');?>">
                    </div>
                    <label class="checkboxLabel">
                        <span 
                        <?php
                        echo esc_attr($isSyncListSelected) ? '' : 'class="tooltip"';
                        ?>
                        >
                            <input name="woocommerce[mailjet_woo_register_checkbox]" id="activate_mailjet_woo_register_checkbox" type="checkbox"
                                value="1" 
                                <?php
                                echo esc_attr($registerCheckbox) === '1' ? ' checked="checked"' : '';
                                ?>
                                <?php
								echo esc_attr($wooCommerceNotInstalled) == \true ? ' disabled' : '';
								?>
                                autocomplete="off" 
                                <?php
                                echo esc_attr($isSyncListSelected) ? '' : 'disabled';
                                ?>
                                >
                            <?php
                            if ( ! $isSyncListSelected) {
                                ?>
                                <span class="tooltiptext">
                                <?php
								echo esc_attr($syncNotActivatedTooltipMessage);
								?>
</span>
								<?php
                            }
                            ?>
                        </span>
                        <span>
                        <?php
                        _e('Activate opt-in checkbox inside register form.', 'mailjet-for-wordpress');
                        ?>
                        </span>
                    </label>
                    <div id="mailjet_woo_sub_letter_register" class="
                    <?php
                    echo esc_attr($registerCheckbox) === '1' ? ' mj-show' : 'mj-hide';
                    ?>
                    mj-text-div">
                        <label  class="mailjet-label" for="sub_letter_register">
                        <?php
                        echo __('Checkbox text', 'mailjet-for-wordpress');
                        ?>
                        </label>
                        <input name="woocommerce[mailjet_woo_register_box_text]" id="sub_letter_register" type="text" value="<?php echo esc_attr($registerCheckboxText); ?>" class="mj-text-field" placeholder="<?php echo __('Subscribe to our newsletter', 'mailjet-for-wordpress');?>
">
                    </div>
                    <label class="checkboxLabel">
                        <span 
                        <?php
                        echo esc_attr($isSyncListSelected) ? '' : 'class="tooltip"';
                        ?>
                        >
                            <input name="woocommerce[mailjet_woo_banner_checkbox]" id="activate_mailjet_woo_bannerbox"  type="checkbox"
                                value="1" 
                                <?php
                                echo esc_attr($bannerCheckbox) === '1' ? ' checked="checked"' : '';
                                ?>
                                <?php
								echo esc_attr($wooCommerceNotInstalled) == \true ? ' disabled' : '';
								?>
                                autocomplete="off" 
                                <?php
                                echo esc_attr($isSyncListSelected) ? '' : 'disabled';
                                ?>
                                >
                            <?php
                            if ( ! $isSyncListSelected) {
                                ?>
                                <span class="tooltiptext">
                                <?php
								echo esc_attr($syncNotActivatedTooltipMessage);
								?>
</span>
								<?php
                            }
                            ?>
                        </span>
                        <span>
                        <?php
                        _e('Activate opt-in banner inside thank you page.', 'mailjet-for-wordpress');
                        ?>
                        </span>
                    </label>
                    <div id="mailjet_woo_sub_banner" class="
                    <?php
                    echo esc_attr($bannerCheckbox) === '1' ? ' mj-show' : 'mj-hide';
                    ?>
                    ">
                        <div class="mj-text-div" >
                            <label class="mailjet-label" for="banner_text">
                            <?php
                            echo __('Banner text', 'mailjet-for-wordpress');
                            ?>
                            </label>
                            <input name="woocommerce[mailjet_woo_banner_text]" id="banner_text" value="<?php echo esc_attr($bannerText); ?>" class="mj-text-field" type="text" placeholder="<?php echo __('Subscribe to our newsletter', 'mailjet-for-wordpress'); ?>
">
                        </div>
                        <div class="mj-text-div" >
                            <label class="mailjet-label" for="banner_label">
                            <?php
                            echo __('Button label', 'mailjet-for-wordpress');
                            ?>
                            </label>
                            <input name="woocommerce[mailjet_woo_banner_label]" id="banner_label" value="<?php echo esc_attr($bannerLabel); ?>" class="mj-text-field" type="text" placeholder="<?php echo __('Subscribe now', 'mailjet-for-wordpress');?>
">
                        </div>
                    </div>
                </div>

            </div>
        </fieldset>
        <hr>
        <?php
    }
    public function mailjet_integrations_cb( $args ) {
        // get the value of the setting we've registered with register_setting()
        $mailjetListId   = Mailjet::getOption('mailjet_sync_list');
        $wooContactList  = MailjetApi::getContactListByID($mailjetListId);
        $wooContactList  = ! empty($wooContactList) ? $wooContactList[0] : array();
        $cf7ContactLists = MailjetApi::getMailjetContactLists();
        $cf7ContactLists = ! empty($cf7ContactLists) ? $cf7ContactLists : array();
        $this->wooIntegration($wooContactList);
        $this->cf7Integration($cf7ContactLists);
        ?>
        <input name="settings_step" type="hidden" id="settings_step" value="integrations_step">
        <?php
    }
    private function cf7Integration( $mailjetContactLists ) {
        $isCF7Installed = \class_exists('WPCF7');
        if ($isCF7Installed === false) {
            delete_option('activate_mailjet_cf7_integration');
            delete_option('mailjet_cf7_list');
            delete_option('cf7_email');
            delete_option('cf7_fromname');
        }
        $mailjetCF7IntegrationActivated = Mailjet::getOption('activate_mailjet_cf7_integration');
        $mailjetCF7List                 = Mailjet::getOption('mailjet_cf7_list');
        $email                          = \stripslashes(Mailjet::getOption('cf7_email'));
        $from                           = \stripslashes(Mailjet::getOption('cf7_fromname'));
        ?>
        <fieldset class="settingsSubscrFldset">
            <span class="mj-integrations-label">
            <?php
            _e('Contact Form 7', 'mailjet-for-wordpress');
            ?>
            </span>
            <label class="mj-switch">
                <input name="cf7[activate_mailjet_cf7_integration]"  id="activate_mailjet_cf7_integration" value="1"
                        type="checkbox" 
                        <?php
                        echo esc_attr($mailjetCF7IntegrationActivated) === '1' ? 'checked="checked"' : '';
                        ?>
                        <?php
                        echo (bool) esc_attr($isCF7Installed) === false ? ' disabled="disabled"' : '';
                        ?>
                >
                <span class="mj-slider mj-round"></span>
            </label>

            <div id="activate_mailjet_cf7_form" class="
            <?php
            echo esc_attr($mailjetCF7IntegrationActivated) == 1 ? ' mj-show' : 'mj-hide';
            ?>
            ">
                <div id="mj-select-block">
                    <label for="mailjet_cf7_list"
                            class="mailjet-label">
                            <?php
                            _e('Mailjet list', 'mailjet-for-wordpress');
                            ?>
                            </label>
                    <select class="mj-select" name="cf7[mailjet_cf7_list]" id="mailjet_cf7_list"
                            type="select" 
                            <?php
                            echo (bool) esc_attr($isCF7Installed) === \false ? ' disabled="disabled"' : '';
                            ?>
                            >
                        <?php
                        foreach ($mailjetContactLists as $mailjetContactList) {
                            if ($mailjetContactList['IsDeleted'] == \true) {
                                continue;
                            }
                            ?>
                            <option value="
                            <?php
                            echo esc_attr($mailjetContactList['ID']);
                            ?>
                            " 
                            <?php
							echo esc_attr($mailjetCF7List == $mailjetContactList['ID']) ? 'selected="selected"' : '';
							?>
> 
							<?php
							echo esc_attr($mailjetContactList['Name']);
							?>
                                (
                                <?php
                                echo esc_attr($mailjetContactList['SubscriberCount']);
                                ?>
                                )
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="cf7_email"
                            class="mailjet-label">
                            <?php
                            _e('Email field tag', 'mailjet-for-wordpress');
                            ?>
                            </label>
                    <input name="cf7[cf7_email]" id="cf7_email" value="<?php echo esc_attr($email);?>"  type="text"
                            placeholder="<?php _e('e.g. [your-email]', 'mailjet-for-wordpress');?>"
                            class="mj-text-field"/>
                </div>
                <div>
                    <label for="cf7_fromname"
                            class="mailjet-label">
                            <?php
                            _e('Name field tag (optional)', 'mailjet-for-wordpress');
                            ?>
                            </label>
                    <input name="cf7[cf7_fromname]" id="cf7_fromname" value="<?php echo esc_attr($from); ?>" type="text"
                            placeholder="<?php _e('e.g. [your-name]', 'mailjet-for-wordpress'); ?>"
                            class="mj-text-field"/>
                </div>
                <div>
                    <div>
                        <span>
                        <?php
                        _e('Include the following shortcode in your contact form in order to display the newsletter subscription checkbox and complete the integration.', 'mailjet-for-wordpress');
                        ?>
                        </span>
                    </div>
                    <div class="mj-copy-wrapper">
                        <input name="cf7[cf7_contact_properties]" id="cf7_contact_properties"
                                value='[checkbox mailjet-opt-in default:0 "<?php echo __('Subscribe to our newsletter', 'mailjet-for-wordpress');?>"]'
                                class="widefat cf7_input" disabled="disabled"/>
                        <i class="fa fa-copy mj-copy-icon" id="copy_properties"></i>
                    </div>
                </div>
            </div>
        </fieldset>
        <hr>
        <?php
    }
    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_integrations_page_html() {
        // check user capabilities
        if ( ! current_user_can('read')) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \\`manage_options\\` permission ]');
            return;
        }
        // register a new section in the "mailjet" page
        add_settings_section('mailjet_integrations_settings', null, array( $this, 'mailjet_section_integrations_cb' ), 'mailjet_integrations_page');
        // register a new field in the "mailjet_section_developers" section, inside the "mailjet" page
        add_settings_field(
            'mailjet_integrations',
            // as of WP 4.6 this value is used only internally
            // use $args' label_for to populate the id inside the callback
            __('Integrations', 'mailjet-for-wordpress'),
            array( $this, 'mailjet_integrations_cb' ),
            'mailjet_integrations_page',
            'mailjet_integrations_settings',
            array(
				'label_for'           => 'mailjet_integrations',
				'class'               => 'mailjet_row',
				'mailjet_custom_data' => 'custom',
			)
        );
        // show error/update messages
        settings_errors('mailjet_messages');
        $nonce       = wp_create_nonce('mailjet_integrations_page_html');
        $post_update = Mailjet::getOption('mailjet_post_update_message');
        if ($post_update) {
            update_option('mailjet_post_update_message', '');
        }
        ?>
        <div class="mj-pluginPage">
            <div id="initialSettingsHead"><img
                        src="
                        <?php
                        echo plugin_dir_url(\dirname(__DIR__)) . '/admin/images/LogoMJ_White_RVB.svg';
                        ?>
                        "
                        alt="Mailjet Logo"/></div>
            <div class="mainContainer">

                <div class="backToDashboard">
                    <a class="mj-btn btnCancel" href="admin.php?page=mailjet_dashboard_page">
                        <svg width="8" height="8" viewBox="0 0 16 16">
                            <path d="M7.89 11.047L4.933 7.881H16V5.119H4.934l2.955-3.166L6.067 0 0 6.5 6.067 13z"/>
                        </svg>
                        <?php
                        _e('Back to dashboard', 'mailjet-for-wordpress');
                        ?>
                    </a>
                </div>

                <h1 class="page_top_title">
                <?php
                _e('Settings', 'mailjet-for-wordpress');
                ?>
                </h1>
                <div class="mjSettings">
                    <div class="left">
                        <?php
                        MailjetAdminDisplay::getSettingsLeftMenu();
                        ?>
                    </div>

                    <div class="right">
                        <?php
                        if ($post_update) {
                            echo wp_kses_data($this->displayMessage($post_update));
                        }
                        ?>
                        <div class="centered">
                            <!--                    <h1>-->
                            <?php
							// echo esc_html(get_admin_page_title());
                            ?>
                            <!--</h1>-->

                            <h2 class="section_inner_title">
                            <?php
                            _e('Integrations', 'mailjet-for-wordpress');
                            ?>
                            </h2>
                            <p>
                            <?php
                            _e('Enable and configure Mailjet integrations with other WordPress plugins', 'mailjet-for-wordpress');
                            ?>
                            </p>
                            <hr>
                            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                                <?php
								// output security fields for the registered setting "mailjet"
                                settings_fields('mailjet_integrations_page');
								// output setting sections and their fields
								// (sections are registered for "mailjet", each field is registered to a specific section)
                                do_settings_sections('mailjet_integrations_page');
								// output save settings button
                                $saveButton = __('Save', 'mailjet-for-wordpress');
                                ?>
                                <input type="hidden" name="action" value="integrationsSettings_custom_hook">
                                <input type="hidden" name="custom_nonce" value="<?php echo esc_attr($nonce); ?>">
                                <button type="submit" id="integrationsSubmit" class="mj-btn btnPrimary MailjetSubmit"
                                        name="submit"><?php echo esc_attr($saveButton);?>
                                </button>
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
    public function integrations_post_handler() {
        $postData = (object) $_POST;
        if ( ! isset($postData->custom_nonce) || !wp_verify_nonce($postData->custom_nonce, 'mailjet_integrations_page_html')) {
            add_settings_error('mailjet_messages', 'mailjet_message', __('Your permissions don\'t match! Please refresh your session and if the problem persists, contact our support team.', 'mailjet-for-wordpress'), 'error');
            settings_errors('mailjet_messages');
            exit;
        }
        $this->toggleCF7Feature( (object) $postData->cf7);
        $wooSettings = WooCommerceSettings::getInstance();
        $response    = $wooSettings->activateWoocommerce($postData->woocommerce);
        update_option('mailjet_post_update_message', $response);
        wp_redirect(add_query_arg(array( 'page' => 'mailjet_integrations_page' ), admin_url('admin.php')));
    }
    private function toggleCF7Feature( $data ) {
        $activate = (int) $data->activate_mailjet_cf7_integration === 1;
        if ( ! $activate) {
            update_option('activate_mailjet_cf7_integration', '');
        }
        foreach ($data as $key => $val) {
            $optionVal = $activate ? $val : '';
            update_option($key, sanitize_text_field($optionVal));
        }
    }
    private function displayMessage( $data ) {
        $type = $data['success'] === \true ? 'notice-success' : 'notice-error';
        $msg  = $data['message'];
        $div  = "<div class=\"notice is-dismissible {$type} \" style=\"display: inline-block; height: 39px; width: 100%;\">\n                    <p><strong>{$msg}</strong></p>\n                    <button type=\"button\" class=\"notice-dismiss\"><span class=\"screen-reader-text\">Dismiss this notice.</span>\n                    </button>\n                </div>";
        return $div;
    }
}
