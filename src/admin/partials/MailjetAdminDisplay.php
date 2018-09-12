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
                echo '<a data-img_id="settingsMenuLinkImg1" class="' . ($currentPage == 'mailjet_connect_account_page' ? 'active' : '') . '" href="admin.php?page=mailjet_connect_account_page">'; ?>
                <svg class="settingsMenuLinkImg1" width="20" viewBox="0 0 16 16"><defs><linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="b"><stop stop-color="#FFBC48" offset="0%"/><stop stop-color="#FFA414" offset="100%"/></linearGradient><rect id="a" width="16" height="16" rx="3"/></defs><g fill-rule="nonzero" fill="none"><g><use fill="#D8D8D8" xlink:href="#a"/><use fill="url(#b)" xlink:href="#a"/></g><path class="settingsMenuLinkImg1" d="M6.518 7.887l-.183 1.271-1.322 1.911.437-.244 7.208-3.965L14 6.118l-7.482 1.77zm-.051-.335l5.652-1.281-.366-.051-1.484-.203-3.152-.438L3 5l1.515 1.108 1.9 1.393.052.05z" fill="#000"/></g></svg>
                <?php echo __('Connect your Mailjet account', 'mailjet'); ?>
                </a>
                </div>
            </li>
            <li>
                <div class="settingsMenuLink">
                <?php
                echo '<a data-img_id="settingsMenuLinkImg2" class="' . ($currentPage == 'mailjet_sending_settings_page' ? 'active' : '') . '" href="admin.php?page=mailjet_sending_settings_page">'; ?>
                <svg class="settingsMenuLinkImg2" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path class="settingsMenuLinkImg2" d="M16.002 16h-16v-1c0-3.533 3.29-6 8-6s8 2.467 8 6v1zM2.161 14h11.683c-.598-1.808-2.833-3-5.841-3s-5.244 1.192-5.842 3zm5.841-6c-2.206 0-4-1.794-4-4 0-2.205 1.794-4 4-4s4 1.795 4 4c0 2.206-1.794 4-4 4zm0-6c-1.103 0-2 .896-2 2 0 1.103.897 2 2 2s2-.897 2-2c0-1.104-.897-2-2-2z"/></svg>
                <?php echo __('Sending settings', 'mailjet'); ?>
                </a>
                </div>
            </li>
            <li>
                <div class="settingsMenuLink">
                <?php
                echo '<a data-img_id="settingsMenuLinkImg3" class="' . ($currentPage == 'mailjet_subscription_options_page' ? 'active' : '') . '" href="admin.php?page=mailjet_subscription_options_page">'; ?>
                <svg class="settingsMenuLinkImg3" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path class="settingsMenuLinkImg3" d="M5 6.4h4.7V8H5zm9.6-2.2L15.9 0l-4 1.4 2 .7zM5 3.9h6.2v1.6H5z"/><path class="settingsMenuLinkImg3" d="M3.5 2.3h6.9V.7H1.9v5.7h1.6z"/><path class="settingsMenuLinkImg3" d="M14.6 6.5L10 10.3H5.9L1.3 6.5c-.1-.1-.3-.1-.5-.1-.4 0-.8.3-.8.8v7.2c0 .9.7 1.6 1.6 1.6h12.7c.9 0 1.6-.7 1.6-1.6V7.2c0-.2-.1-.4-.2-.5-.3-.4-.8-.4-1.1-.2zm-13 7.8V8.9l3.5 2.9c.1.1.3.2.5.2h4.8c.2 0 .4-.1.5-.2l3.5-2.9v5.5H1.6z"/></svg>
                <?php echo __('Subscription options', 'mailjet'); ?>
                </a>
                </div>
            </li>
            <li>
                <div class="settingsMenuLink">
                <?php
                echo '<a data-img_id="settingsMenuLinkImg4" class="' . ($currentPage == 'mailjet_user_access_page' ? 'active' : '') . '" href="admin.php?page=mailjet_user_access_page">'; ?>
                <svg class="settingsMenuLinkImg4" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 12"><circle cx="4.2" cy="8.7" r=".9"/><path class="settingsMenuLinkImg4" d="M7.2 5.4V3a3 3 0 0 0-6 0v2.4A1.2 1.2 0 0 0 0 6.6v4.2A1.2 1.2 0 0 0 1.2 12h6a1.2 1.2 0 0 0 1.2-1.2V6.6a1.2 1.2 0 0 0-1.2-1.2zM2.4 3A1.8 1.8 0 0 1 6 3v2.4H2.4zm-1.2 7.8V6.6h6v4.2z"/></svg>
                <?php echo __('User access', 'mailjet'); ?>
                </a>
                </div>
            </li>
        </ul>
        <?php
    }
}


