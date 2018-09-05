<?php
namespace MailjetPlugin\Admin\Partials;

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
        $currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : null;
        ?>
        <ul>
            <li>
                <div class="settingsMenuLink">
                <?php
                $defaultImg1 = plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/images/connect_account_screen_icon.png';
                $hoverImg1 = plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/images/sending_options_screen_icon.png';
                echo '<a data-img_id="settingsMenuLinkImg1" data-default="' . $defaultImg1 . '" data-hover="' . $hoverImg1 . '" class="' . ($currentPage == 'mailjet_connect_account_page' ? 'active' : '') . '" href="admin.php?page=mailjet_connect_account_page">'; ?>
                <img id="settingsMenuLinkImg1" src=" <?php echo $defaultImg1; ?>" alt="<?php echo __('Connect your Mailjet account', 'mailjet'); ?>" />
                <?php echo __('Connect your Mailjet account', 'mailjet'); ?>
                </a>
                </div>
            </li>
            <li>
                <div class="settingsMenuLink">
                <?php
                $defaultImg2 = plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/images/sending_options_screen_icon.png';
                $hoverImg2 = plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/images/connect_account_screen_icon.png';
                echo '<a data-img_id="settingsMenuLinkImg2" data-default="' . $defaultImg2 . '" data-hover="' . $hoverImg2 . '" class="' . ($currentPage == 'mailjet_sending_settings_page' ? 'active' : '') . '" href="admin.php?page=mailjet_sending_settings_page">'; ?>
                <img id="settingsMenuLinkImg2" src=" <?php echo $defaultImg2; ?>" alt="<?php echo __('Sending settings', 'mailjet'); ?>" />
                <?php echo __('Sending settings', 'mailjet'); ?>
                </a>
                </div>
            </li>
            <li>
                <div class="settingsMenuLink">
                <?php
                $defaultImg3 = plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/images/subscription_options_screen_icon.png';
                $hoverImg3 = plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/images/connect_account_screen_icon.png';
                echo '<a data-img_id="settingsMenuLinkImg3" data-default="' . $defaultImg3 . '" data-hover="' . $hoverImg3 . '" class="' . ($currentPage == 'mailjet_subscription_options_page' ? 'active' : '') . '" href="admin.php?page=mailjet_subscription_options_page">'; ?>
                <img id="settingsMenuLinkImg3" src=" <?php echo $defaultImg3; ?>" alt="<?php echo __('Subscription options', 'mailjet'); ?>" />
                <?php echo __('Subscription options', 'mailjet'); ?>
                </a>
                </div>
            </li>
            <li>
                <div class="settingsMenuLink">
                <?php
                $defaultImg4 = plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/images/user_access_screen_icon.png';
                $hoverImg4 = plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/images/connect_account_screen_icon.png';
                echo '<a data-img_id="settingsMenuLinkImg4" data-default="' . $defaultImg4 . '" data-hover="' . $hoverImg4 . '"  class="' . ($currentPage == 'mailjet_user_access_page' ? 'active' : '') . '" href="admin.php?page=mailjet_user_access_page">'; ?>
                <img id="settingsMenuLinkImg4" src=" <?php echo $defaultImg4; ?>" alt="<?php echo __('User access', 'mailjet'); ?>" />
                <?php echo __('User access', 'mailjet'); ?>
                </a>
                </div>
            </li>
        </ul>
        <?php
    }
}


