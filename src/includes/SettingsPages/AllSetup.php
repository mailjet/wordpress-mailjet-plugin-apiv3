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
class AllSetup
{

    /**
     * top level menu:
     * callback functions
     */
    public function mailjet_allsetup_page_html()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        ?>
        <div class="dashboard">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="container">
                <div class="block_single">
                    <h2><?php echo __('Add a subscription form to ypur website', 'mailjet' ); ?></h2>
                    <p><?php echo __('Go to the widget management page and add the Mailjet Subscription Widget to your website to start collecting email addresses.', 'mailjet' ); ?></p>
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/all_setup_screen_signup_to_newsletter.png'; ?>" />
                    <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'widgets.php'" value="<?=__('Manage my widgets', 'mailjet')?>">
                </div>
            </div>
            <div class="container">
                <div class="block_single">
                    <h2><?php echo __('Send an email campaign', 'mailjet' ); ?><br /><br /></h2>
                    <p><?php echo __('Ready to send a newsletter to your subscribers? Simply go to your Campaigns and click on "Create a campaign" to create and send your email.', 'mailjet' ); ?></p>
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/all_setup_screen_send_campaign.png'; ?>" />
                    <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_settings_campaigns_menu'" value="<?=__('Create a campaign', 'mailjet')?>">
                </div>
            </div>
            <div class="container">
                <div class="block_single">
                    <h2><?php echo __('Configure Wordpress email sending', 'mailjet' ); ?></h2>
                    <p><?php echo __('Enable and configure sending of all your Wordpress emails (transactional emails, etc...) through Mailjet.', 'mailjet' ); ?></p>
                    <br /><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/all_setup_screen_configure.png'; ?>" />
                    <input name="nextBtn" class="nextBtn" type="button" id="nextBtn" onclick="location.href = 'admin.php?page=mailjet_sending_settings_page'" value="<?=__('Configure', 'mailjet')?>">
                </div>
            </div>

            <div style="margin: 20px; margin-left: 220px;clear: left;"> or <?php echo '<a target="_blank" href="admin.php?page=mailjet_dashboard_page">' . __('Go to your Mailjet Plugin Homepage', 'mailjet') . '</a>'; ?> </div>

        </div>

        <div class="bottom_links_dashboard">
            <h2><?php echo __('Need help getting started?', 'mailjet' ); ?></h2>
            <?php echo '<a target="_blank" href="https://www.mailjet.com/guides/wordpress-user-guide/">' . __('Read our user guide', 'mailjet') . '</a>'; ?>
            <?php echo ' | ' ?>
            <?php echo '<a target="_blank" href="https://www.mailjet.com/support/ticket">' . __('Contact our support team', 'mailjet') . '</a>'; ?>
        </div>
        <?php
    }

}
