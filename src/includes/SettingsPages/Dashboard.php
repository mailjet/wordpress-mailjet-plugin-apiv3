<?php

namespace MailjetPlugin\Includes\SettingsPages;

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
        if (!current_user_can('manage_options')) {
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        ?>
        <div class="wrap dashboard">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div id="container">
                <div class="block_single">
                    <h2><?php echo __('Send newsletter', 'mailjet' ); ?></h2>
                    <p><?php echo __('Create and manage your newsletters. Vew your campaign statistics', 'mailjet' ); ?></p>
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/mj_logo_med.png'; ?>" />
                    <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_settings_campaigns_menu'" value="<?=__('My campaigns', 'mailjet')?>">
                </div>

            </div>
            <div id="container">
                <div class="block">
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/mj_logo_med.png'; ?>" />
                    <h2><?php echo __('Manage my contacts', 'mailjet' ); ?></h2>
                    <p><?php echo __('View and manage your contact lists', 'mailjet' ); ?></p>
                    <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_settings_contacts_menu'" value="<?=__('My contact lists')?>">
                </div>
                <div class="block">
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/mj_logo_med.png'; ?>" />
                    <h2><?php echo __('Add a subscription form', 'mailjet' ); ?></h2>
                    <p><?php echo __('Customize a subscription form and add it to your Wordpress website', 'mailjet' ); ?></p>
                    <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'widgets.php'" value="<?=__('My widgets', 'mailjet')?>">
                </div>
                <div class="block">
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/mj_logo_med.png'; ?>" />
                    <h2><?php echo __('View statistics', 'mailjet' ); ?></h2>
                    <p><?php echo __('View your sending statistics over a period of time', 'mailjet' ); ?></p>
                    <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_settings_stats_menu'" value="<?=__('My statistics')?>">
                </div>
                <div class="block">
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/mj_logo_med.png'; ?>" />
                    <h2><?php echo __('Update settings', 'mailjet' ); ?></h2>
                    <p><?php echo __('Review and modify your plugin settings', 'mailjet' ); ?></p>
                    <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_connect_account_page'" value="<?=__('Settings')?>">
                </div>
            </div>


            <div class="bottom_links_dashboard">
                <h2><?php echo __('Need help getting started?', 'mailjet' ); ?></h2>
                <?php echo '<a target="_blank" href="https://www.mailjet.com/guides/wordpress-user-guide/">' . __('Read our user guide', 'mailjet') . '</a>'; ?>
                <?php echo ' | ' ?>
                <?php echo '<a target="_blank" href="https://www.mailjet.com/support/ticket">' . __('Contact our support team', 'mailjet') . '</a>'; ?>
            </div>
        </div>
        <?php
    }

}
