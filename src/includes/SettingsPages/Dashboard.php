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
class Dashboard
{

    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_dashboard_page_html()
    {
        // check user capabilities
        if (!current_user_can('read')) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        ?>
        <div class="mj-pluginPage">
            <div id="initialSettingsHead"><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/LogoMJ_White_RVB.svg'; ?>" alt="Mailjet Logo" /></div>
            <div class="mainContainer dashboard">
                <!--            <h1>--><?php //echo esc_html(get_admin_page_title());  ?><!--</h1>-->
                <h1  class="page_top_title"><?php echo __('Welcome to the Mailjet plugin for Wordpress', 'mailjet-for-wordpress'); ?> </h1 >

                <div class="initialSettingsMainCtn">
                    <div class="block leftCol">
                        <h2 class="section_inner_title"><?php _e('Send newsletter', 'mailjet-for-wordpress'); ?></h2>
                        <p><?php echo __('Create and manage your newsletters. Vew your campaign statistics', 'mailjet-for-wordpress'); ?></p>
                        <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary" id="nextBtnReverseDashboard1" onclick="location.href = 'admin.php?page=mailjet_settings_campaigns_menu'"><?= __('My campaigns', 'mailjet-for-wordpress') ?></button>
                        <div class="passportImage" style="background-image: url(<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/dashboard_main_image.png'; ?>);"></div>
                    </div>

                    <div class="rightCol">
                        <div class="block">
                            <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/dashboard_top_left_image.png'; ?>" />
                            <h3 class="blockTitle"><?php echo __('Manage my contacts', 'mailjet-for-wordpress'); ?></h3>
                            <p class="blockText"><?php echo __('View and manage your contact lists', 'mailjet-for-wordpress'); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary" id="nextBtnReverseDashboard2" onclick="location.href = 'admin.php?page=mailjet_settings_contacts_menu'"><?php _e('My contact lists', 'mailjet-for-wordpress') ?></button>
                        </div>
                        <div class="block">
                            <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/dashboard_top_right_image.png'; ?>" />
                            <h3 class="blockTitle"><?php echo __('Add a subscription form', 'mailjet-for-wordpress'); ?></h3>
                            <p class="blockText"><?php echo __('Customize a subscription form and add it to your Wordpress website', 'mailjet-for-wordpress'); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary" id="nextBtnReverseDashboard3" onclick="location.href = 'widgets.php'"><?= __('My widgets', 'mailjet-for-wordpress') ?></button>
                        </div>
                        <div class="block">
                            <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/dashboard_bottom_left_image.png'; ?>" />
                            <h3 class="blockTitle"><?php echo __('View statistics', 'mailjet-for-wordpress'); ?></h3>
                            <p class="blockText"><?php echo __('View your sending statistics over a period of time', 'mailjet-for-wordpress'); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary" id="nextBtnReverseDashboard4" onclick="location.href = 'admin.php?page=mailjet_settings_stats_menu'"><?php _e('My statistics', 'mailjet-for-wordpress') ?></button>
                        </div>
                        <div class="block">
                            <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/dashboard_bottom_right_image.png'; ?>" />
                            <h3 class="blockTitle"><?php echo __('Update settings', 'mailjet-for-wordpress'); ?></h3>
                            <p class="blockText"><?php echo __('Review and modify your plugin settings', 'mailjet-for-wordpress'); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary" id="nextBtnReverseDashboard5" onclick="location.href = 'admin.php?page=mailjet_connect_account_page'"><?php _e('Settings', 'mailjet-for-wordpress') ?></button>
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

}
