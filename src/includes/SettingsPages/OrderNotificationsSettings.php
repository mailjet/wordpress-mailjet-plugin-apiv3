<?php


namespace MailjetPlugin\Includes\SettingsPages;


use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\MailjetLogger;
use MailjetPlugin\Includes\MailjetSettings;

class OrderNotificationsSettings {

	public function mailjet_order_notifications_settings_page_html() {

		$iconDir           = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'admin/images/woo.svg';
		$wooCommerceExists = get_option( 'activate_mailjet_woo_integration' ) === 'on' ? 'mj-show' : 'hidden';
		$wooCommerceExists = 'mj-show';
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
                <form action="options.php" method="post" id="order-notification-form">
                    <div>
                        <a class="mj-btn btnCancel" href="admin.php?page=mailjet_dashboard_page">
                            <svg width="8" height="8" viewBox="0 0 16 16">
                                <path d="M7.89 11.047L4.933 7.881H16V5.119H4.934l2.955-3.166L6.067 0 0 6.5 6.067 13z"/>
                            </svg>
							<?php _e( 'Back to dashboard', 'mailjet-for-wordpress' ) ?>
                        </a>
                    </div>

                    <div id="mj-top_bar">
                        <section
                                id="mj-title"><?php _e( 'Order notification emails', 'mailjet-for-wordpress' ); ?>
                        </section>
                    </div>
                    <div>
                        <section>
							<?php _e( 'Enable the sending of order notification emails to make sure that your customers are always notified when a new purchase occurs and when their order has been shipped.', 'mailjet-for-wordpress' ); ?>
                        </section>
                    </div>
                    <div>
                        <h3>
							<?php _e( 'Templates', 'mailjet-for-wordpress' ); ?>
                        </h3>
                    </div>
                    <fieldset class="settingsSubscrFldset">
                        <hr>
                        <div class="mailjet_row mj-notifications">
                            <label class="mj-order-notifications-labels">
                                <input class="checkbox mj-order-checkbox" name="mailjet_order_confirmation" type="checkbox"
                                       id="order_confirmation" value="1">
								<?php _e( 'Order confirmation', 'mailjet-for-wordpress' ); ?>
                            </label>
                            <button class="mj-btnSecondary mj-inrow" type="button">Edit</button>
                        </div>
                        <hr>
                        <div class="mailjet_row mj-notifications">
                            <label class="mj-order-notifications-labels">
                                <input class="checkbox mj-order-checkbox" name="mailjet_shipping_confirmation" type="checkbox"
                                       id="shipping_confirmation" value="1">
								<?php _e( 'Shipping confirmation', 'mailjet-for-wordpress' ); ?>
                            </label>
                            <button class="mj-btnSecondary mj-inrow" type="button">Edit</button>
                        </div>
                        <hr>
                        <div class="mailjet_row mj-notifications">
                            <label class="mj-order-notifications-labels">
                                <input class="checkbox mj-order-checkbox" name="mailjet_refund_confirmation" type="checkbox"
                                       id="refund_confirmation" value="1">
								<?php _e( 'Refund confirmation', 'mailjet-for-wordpress' ); ?>
                            </label>
                            <button class="mj-btnSecondary mj-inrow" type="button">Edit</button>
                        </div>
                        <hr>
                    </fieldset>
                    <div>
                        <button id="mj-order-notifications-submit" class="mj-btn btnPrimary mj-disabled" type="submit">Activate sending</button>
                    </div>
                </form>
            </div>
			<?php
			MailjetAdminDisplay::renderBottomLinks();
			?>
            <script>
                document.getElementById("refund_confirmation").addEventListener("click", submitListener);
                document.getElementById("order_confirmation").addEventListener("click", submitListener);
                document.getElementById("shipping_confirmation").addEventListener("click", submitListener);

                function submitListener() {
                    let form = document.getElementById("order-notification-form");
                    let btnSubmit = document.getElementById("mj-order-notifications-submit");
                    let i;
                    let flag = false;
                    for (i = 0; i < form.length; i++) {
                        if(form[i].type === "checkbox" && form[i].checked) {
                            flag = true;
                        }
                    }

                    if (flag){
                        btnSubmit.classList.remove('mj-disabled')
                    }else {
                        btnSubmit.classList.add('mj-disabled')
                    }
                }
            </script>
        </div>
		<?php
	}
}