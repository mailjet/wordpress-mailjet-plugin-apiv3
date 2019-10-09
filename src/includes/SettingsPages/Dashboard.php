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
class Dashboard {

	/**
	 * top level menu:
	 * callback functions
	 */
	public function mailjet_dashboard_page_html() {

		$iconDir = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'admin/images/woo.svg';
		$wooCommerceIntegActivated = get_option( 'activate_mailjet_woo_integration' ) === '1';
		if ( ! MailjetApi::isValidAPICredentials() ) {
			MailjetSettings::redirectJs( admin_url( '/admin.php?page=mailjet_settings_page&from=plugins' ) );
		}
		// check user capabilities
		if ( ! current_user_can( 'read' ) ) {
			MailjetLogger::error( '[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]' );

			return;
		}
		?>

        <div class="mj-pluginPage">
            <div id="initialSettingsHead"><img
                        src="<?php echo plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . '/admin/images/LogoMJ_White_RVB.svg'; ?>"
                        alt="Mailjet Logo"/></div>
            <div class="mainContainer dashboard">
                <div id="mj-top_bar">
                    <section
                            id="mj-title"><?php _e('Welcome to the Mailjet plugin for WordPress', 'mailjet-for-wordpress'); ?></section>
                    <div id="mj-settings_top_bar">
                        <i onclick="location.href = 'admin.php?page=mailjet_connect_account_page'"
                           class="dashicons dashicons-admin-generic">
                            <span><?php _e('Settings', 'mailjet-for-wordpress'); ?></span>
                        </i>
                    </div>
                </div>

                <div class="initialSettingsMainCtn">
                    <div class="col mj-grid">
                        <div class="block mj-box">
                            <h2 class="section-header"><?php _e( 'My campaigns', 'mailjet-for-wordpress' ); ?></h2>
                            <p><?php echo __( 'Create and manage your newsletters. View your campaign statistics', 'mailjet-for-wordpress' ); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary"
                                    id="nextBtnReverseDashboard1"
                                    onclick="location.href = 'admin.php?page=mailjet_settings_campaigns_menu'"><?= __( 'Manage campaigns', 'mailjet-for-wordpress' ) ?></button>
                        </div>
                        <div class="block mj-box">
                            <h3 class="section-header"><?php _e( 'My contact lists', 'mailjet-for-wordpress' ); ?></h3>
                            <p class="blockText"><?php _e( 'View and manage your contact lists', 'mailjet-for-wordpress' ); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary"
                                    id="nextBtnReverseDashboard2"
                                    onclick="location.href = 'admin.php?page=mailjet_settings_contacts_menu'"><?php _e( 'Manage contacts', 'mailjet-for-wordpress' ) ?></button>
                        </div>
                        <?php if ($wooCommerceIntegActivated) { ?>
                        <div class="block mj-box">
                            <div>
                                <h3 class="section-header"><?php _e( 'Order notification emails', 'mailjet-for-wordpress' ); ?></h3>
                                <img alt="asd" class="mj-woo-logo-small" src="<?= $iconDir ?>"/>
                            </div>
                            <p class="blockText"><?php _e( 'Activate order notification emails to inform customers of any new purchase, shipping or refund.', 'mailjet-for-wordpress' ); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary"
                                    id="nextBtnReverseDashboard4"
                                    onclick="location.href = 'admin.php?page=mailjet_order_notifications_page'"><?php _e( 'Manage transactional emails', 'mailjet-for-wordpress' ) ?></button>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="col mj-grid">
                        <div class="block mj-box">
                            <h3 class="section-header"><?php _e( 'Subscription form', 'mailjet-for-wordpress' ); ?></h3>
                            <p class="blockText"><?php _e( 'Customize a subscription form and add it to your WordPress website', 'mailjet-for-wordpress' ); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btnSecondary" id="nextBtnReverseDashboard3"
                                    onclick="location.href = 'widgets.php'"><?= __( 'Add widget', 'mailjet-for-wordpress' ) ?></button>
                        </div>
                        <div class="block mj-box">
                            <h3 class="section-header"><?php _e( 'Statistics', 'mailjet-for-wordpress' ); ?></h3>
                            <p class="blockText"><?php _e( 'View your sending statistics over a period of time', 'mailjet-for-wordpress' ); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary"
                                    id="nextBtnReverseDashboard4"
                                    onclick="location.href = 'admin.php?page=mailjet_settings_stats_menu'"><?php _e( 'View statistics', 'mailjet-for-wordpress' ) ?></button>
                        </div>
                        <?php if ($wooCommerceIntegActivated) { ?>
                        <div class="block mj-box">
                            <div>
                                <h3 class="section-header"><?php _e( 'Abandoned cart email', 'mailjet-for-wordpress' ); ?></h3>
                                <img alt="asd" class="mj-woo-logo-small" src="<?= $iconDir ?>"/>
                            </div>
                            <p class="blockText"><?php _e( 'Recover visitors and turn them into customers but reminding them what they left in their cart.', 'mailjet-for-wordpress' ); ?></p>
                            <button name="nextBtnReverseDashboard" class="mj-btn btnPrimary"
                                    id="nextBtnReverseDashboard3"
                                    onclick="location.href = 'admin.php?page=mailjet_abandoned_cart_page'"><?= __( 'Manage abandoned cart', 'mailjet-for-wordpress' ) ?></button>
                        </div>
                        <?php } ?>
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
