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
class Dashboard
{

    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_dashboard_page_html()
    {
        if (!MailjetApi::isValidAPICredentials()){
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_settings_page&from=plugins'));
        }
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
                <span>  <h1  class="page_top_title"><?php _e('Welcome to the Mailjet plugin for Wordpress', 'mailjet-for-wordpress'); ?> </h1 > <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary" id="nextBtnReverseDashboard5" onclick="location.href = 'admin.php?page=mailjet_connect_account_page'"><?php _e('Settings', 'mailjet-for-wordpress') ?></button>
</span>

                <div class="initialSettingsMainCtn">
                    <div class="col">
                        <div class="block">
                            <h2 class="section-header"><?php _e('My campaigns', 'mailjet-for-wordpress'); ?></h2>
                            <p><?php echo __('Create and manage your newsletters. Vew your campaign statistics', 'mailjet-for-wordpress'); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary" id="nextBtnReverseDashboard1" onclick="location.href = 'admin.php?page=mailjet_settings_campaigns_menu'"><?= __('Manage campaigns', 'mailjet-for-wordpress') ?></button>
                        </div>
                        <div class="block">
                            <h3 class="section-header"><?php _e('My contact lists', 'mailjet-for-wordpress'); ?></h3>
                            <p class="blockText"><?php _e('View and manage your contact lists', 'mailjet-for-wordpress'); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary" id="nextBtnReverseDashboard2" onclick="location.href = 'admin.php?page=mailjet_settings_contacts_menu'"><?php _e('Manage contacts', 'mailjet-for-wordpress') ?></button>
                        </div>
                    </div>
                    <div class="col">
                        <div class="block">
                            <h3 class="section-header"><?php _e('Subscription form', 'mailjet-for-wordpress'); ?></h3>
                            <p class="blockText"><?php _e('Customize a subscription form and add it to your Wordpress website', 'mailjet-for-wordpress'); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btn-info" id="nextBtnReverseDashboard3" onclick="location.href = 'widgets.php'"><?= __('Add widget', 'mailjet-for-wordpress') ?></button>
                        </div>
                        <div class="block">
                            <h3 class="section-header"><?php _e('Statistics', 'mailjet-for-wordpress'); ?></h3>
                            <p class="blockText"><?php _e('View your sending statistics over a period of time', 'mailjet-for-wordpress'); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary" id="nextBtnReverseDashboard4" onclick="location.href = 'admin.php?page=mailjet_settings_stats_menu'"><?php _e('View statistics', 'mailjet-for-wordpress') ?></button>
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
