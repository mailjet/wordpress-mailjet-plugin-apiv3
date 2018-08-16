<?php
namespace MailjetPlugin\Admin\Partials;

use Analog\Analog;

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      5.0.0
 *
 * @package    Mailjet
 * @subpackage Mailjet/admin/partials
 */
class MailjetAdminDisplay
{
    public static function getSettingsLeftMenu()
    {
        ?>
        <h1>Settings</h1>
        <?php
        $currentPage = $_REQUEST['page'];
        ?>
        <ul>
            <li>
                <div class="settingsMenuLink">
                <?php
                echo '<a class="' . ($currentPage == 'mailjet_connect_account_page' ? 'active' : '') . '" href="admin.php?page=mailjet_connect_account_page">'; ?>
                <img style="width: 50px;" src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/mj_logo_small.png'; ?>" alt="<?php echo __('Connect your Mailjet account', 'mailjet'); ?>" />
                <?php echo __('Connect your Mailjet account', 'mailjet'); ?>
                </a>
                </div>
            </li>
            <li>
                <div class="settingsMenuLink">
                <?php
                echo '<a class="' . ($currentPage == 'mailjet_sending_settings_page' ? 'active' : '') . '" href="admin.php?page=mailjet_sending_settings_page">'; ?>
                <img style="width: 50px;" src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/mj_logo_small.png'; ?>" alt="<?php echo __('Sending settings', 'mailjet'); ?>" />
                <?php echo __('Sending settings', 'mailjet'); ?>
                </a>
                </div>
            </li>
            <li>
                <div class="settingsMenuLink">
                <?php
                echo '<a class="' . ($currentPage == 'mailjet_subscription_options_page' ? 'active' : '') . '" href="admin.php?page=mailjet_subscription_options_page">'; ?>
                <img style="width: 50px;"  src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/mj_logo_small.png'; ?>" alt="<?php echo __('Subscription options', 'mailjet'); ?>" />
                <?php echo __('Subscription options', 'mailjet'); ?>
                </a>
                </div>
            </li>
            <li>
                <div class="settingsMenuLink">
                <?php
                echo '<a class="' . ($currentPage == 'mailjet_user_access_page' ? 'active' : '') . '" href="admin.php?page=mailjet_user_access_page">'; ?>
                <img style="width: 50px;"  src=" <?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/mj_logo_small.png'; ?>" alt="<?php echo __('User access', 'mailjet'); ?>" />
                <?php echo __('User access', 'mailjet'); ?>
                </a>
                </div>
            </li>
        </ul>


        <div class="bottom_links">
            <?php echo '<a target="_blank" href="https://www.mailjet.com/guides/wordpress-user-guide/">' . __('Read our user guide', 'mailjet') . '</a>'; ?>
            <?php echo ' | ' ?>
            <?php echo '<a target="_blank" href="https://www.mailjet.com/support/ticket">' . __('Contact our support team', 'mailjet') . '</a>'; ?>
        </div>
        <?php
    }
}


