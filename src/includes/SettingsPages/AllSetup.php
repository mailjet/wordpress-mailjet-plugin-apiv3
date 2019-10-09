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
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        ?>
        <div class="mj-pluginPage">
            <div id="initialSettingsHead"><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/LogoMJ_White_RVB.svg'; ?>" alt="Mailjet Logo" /></div>
            <div class="mainContainer allsetup">
                <!--            <h1>--><?php //echo esc_html(get_admin_page_title());  ?><!--</h1>-->
                <h1 class="page_top_title"><?php echo __('You\'re all set up!', 'mailjet-for-wordpress'); ?></h1>
                <p class="page_top_subtitle"><?php echo __('What would you like to do next?', 'mailjet-for-wordpress'); ?></p>
                <div class="allsetup_blocks">
                    <div class="block_single">
                        <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/all_setup_screen_signup_to_newsletter.png'; ?>" />
                        <div class="section_inner_title"><?php echo __('Add a subscription form to your website', 'mailjet-for-wordpress'); ?></div>
                        <p class="blockText"><?php _e('Go to the widget management page and add the Mailjet Subscription Widget to your website to start collecting email addresses.', 'mailjet-for-wordpress'); ?></p>
                        <div class="bottomBtn"><input name="nextBtnReverse" class="mj-btn btnPrimary" type="button" id="nextBtnReverse1" onclick="location.href = 'widgets.php'" value="<?php _e('Manage my widgets', 'mailjet-for-wordpress') ?>"></div>
                    </div>
                    <div class="block_single">
                        <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/all_setup_screen_send_campaign.png'; ?>" />
                        <div class="section_inner_title"><?php echo __('Send an email campaign', 'mailjet-for-wordpress'); ?><br /></div>
                        <p class="blockText"><?php _e('Ready to send a newsletter to your subscribers? Simply go to your Campaigns and click on "Create a campaign" to create and send your email.', 'mailjet-for-wordpress'); ?></p>
                        <div class="bottomBtn"><input name="nextBtnReverse" class="mj-btn btnPrimary" type="button" id="nextBtnReverse2" onclick="location.href = 'admin.php?page=mailjet_settings_campaigns_menu'" value="<?php _e('Create a campaign', 'mailjet-for-wordpress') ?>"></div>
                    </div>
                    <div class="block_single">
                        <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/all_setup_screen_configure.png'; ?>" />
                        <div class="section_inner_title"><?php echo __('Configure WordPress email sending', 'mailjet-for-wordpress'); ?></div>
                        <p class="blockText"><?php _e('Enable and configure sending of all your WordPress emails (transactional emails, etc...) through Mailjet.', 'mailjet-for-wordpress'); ?></p>
                        <div class="bottomBtn"><input name="nextBtnReverse" class="mj-btn btnPrimary" type="button" id="nextBtnReverse3" onclick="location.href = 'admin.php?page=mailjet_sending_settings_page'" value="<?php _e('Configure', 'mailjet-for-wordpress') ?>"></div>
                    </div>
                </div>

                <div class="allsetupGreenLinkDiv">
                    <?php echo sprintf(__('or <a class="greenLink" href="%s">Go to your Mailjet Plugin Homepage</a>', 'mailjet-for-wordpress'), "admin.php?page=mailjet_dashboard_page"); ?>
                </div>

            </div>
            <?php
                MailjetAdminDisplay::renderBottomLinks();
            ?>
        </div>
        <?php
    }

}
