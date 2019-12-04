<?php
namespace MailjetPlugin\Admin\Partials;

use MailjetPlugin\Includes\Mailjeti18n;

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
    private static $leftMenuFile = '/settingTemplates/SubscriptionSettingsPartials/leftMenu.php';
    private static $bottomLinksFile = '/bottomLinks.php';

    public static function getSettingsLeftMenu()
    {
        $currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : null;
	    set_query_var('currentPage', $currentPage);
        load_template(MAILJET_ADMIN_TAMPLATE_DIR . self::$leftMenuFile);
    }

    public static function renderBottomLinks()
    {
        $userGuideLink = Mailjeti18n::getMailjetUserGuideLinkByLocale();
        $supportLink = Mailjeti18n::getMailjetSupportLinkByLocale();

	    set_query_var('supportLink', $supportLink);
	    set_query_var('userGuideLink', $userGuideLink);
	    load_template(MAILJET_ADMIN_TAMPLATE_DIR . self::$bottomLinksFile);

    }
}


