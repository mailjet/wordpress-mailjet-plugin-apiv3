<?php


namespace MailjetPlugin\Includes\SettingsPages;


use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\MailjetLogger;
use MailjetPlugin\Includes\MailjetSettings;

class OrderNotificationsSettings
{

    public function mailjet_order_notifications_settings_page_html()
    {

        $fromName = get_option('mailjet_from_name');
        $fromMail = get_option('mailjet_from_email');


        $orderConfirmationBadge = get_option('mailjet_order_confirmation') === '1' ? '' : 'style="visibility: hidden"'; // Processing Order
        $shippingConfirmationBadge = get_option('mailjet_shipping_confirmation') === '1' ? '' : 'style="visibility: hidden"'; //Order complected WC
        $refundConfirmationBadge = get_option('mailjet_refund_confirmation') === '1' ? '' : 'style="visibility: hidden"'; // Refunded order

        $orderConfirmationBox = $orderConfirmationBadge !== '' ?: 'style="visibility: hidden"';
        $shippingConfirmationBox = $shippingConfirmationBadge !== '' ?: 'style="visibility: hidden"';
        $refundConfirmationBox = $refundConfirmationBadge !== '' ?: 'style="visibility: hidden"';


        $shippingConfirmationSubject = 'blaladsasdadsadsasdasdadadadsadadsalal';
        $wooCommerceExists = get_option('activate_mailjet_woo_integration') === 'on' ? true : false;



        if (!MailjetApi::isValidAPICredentials() || !$wooCommerceExists) {
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_dashboard_page'));
        }
        // check user capabilities
        if (!current_user_can('read')) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }
        ?>
        <div class="mj-pluginPage mj-order-notificartion-popup hidden" id="mj-popup">
            <div class="mj-popup-header">
                <h1>Sending active</h1><span> <a class="buttons-desktop-04-icon-01-def" id="mj-close" href="#"> X </a></span>
            </div>
            <hr>
            <div class="mj-popup-body">
                <div class="mj-popup-info">
                    <p>Make sure to re-activate the emails inside Woocommerce (Settings > Emails) to ensure your customers get notified for their transactions.</p>
                </div>
                <div class="mj-popup-message">
                    <p>By stopping the sending of all order notification emails, your customers will no more receive communications related to their purchases. Do you wish to stop all sendings?</p>
                </div>
            </div>
            <hr>
            <div class="mj-popup-footer mailjet_row">
                <button class="mj-btn btnPrimary"  type="button">Stop all sendings</button>
                <button class="mj-btnSecondary" data-toggle="hide" onclick="toggleMailjetPopup(this)">Cancel</button>
            </div>
        </div>


        <div>
            <div id="initialSettingsHead"><img
                        src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/LogoMJ_White_RVB.svg'; ?>"
                        alt="Mailjet Logo"/></div>
            <div class="mainContainer dashboard">

                <div>
                    <a class="mj-btn btnCancel" href="admin.php?page=mailjet_dashboard_page">
                        <svg width="8" height="8" viewBox="0 0 16 16">
                            <path d="M7.89 11.047L4.933 7.881H16V5.119H4.934l2.955-3.166L6.067 0 0 6.5 6.067 13z"/>
                        </svg>
                        <?php _e('Back to dashboard', 'mailjet-for-wordpress') ?>
                    </a>
                </div>
                <form action="options.php" method="post" id="order-notification-form">
                    <fieldset class="order-notification-field">
                        <div id="mj-top_bar">
                            <h1><?php _e('Order notification emails', 'mailjet-for-wordpress'); ?> </h1>
                        </div>

                        <div>
                            <section>
                                <?php _e('Enable the sending of order notification emails to make sure that your customers are always notified when a new purchase occurs and when their order has been shipped.', 'mailjet-for-wordpress'); ?>
                            </section>
                        </div>
                        <div>
                            <h3>
                                <?php _e('Templates', 'mailjet-for-wordpress'); ?>
                            </h3>
                        </div>

                        <hr>
                        <div class="mailjet_row mj-notifications">
                            <label class="mj-order-notifications-labels">
                                <input class="checkbox mj-order-checkbox" <?=$orderConfirmationBox?> name="mailjet_order_confirmation"
                                       type="checkbox"
                                       id="order_confirmation" value="1">
                                <section class="mj-checkbox-label">
                                    <?php _e('Order confirmation', 'mailjet-for-wordpress'); ?>
                                </section>
                            </label>
                            <div class="mj-badge" <?=$orderConfirmationBadge?>><p>Sending active</p></div>
                            <a class="mj-btnSecondary mj-inrow" href="https://app.mailjet.com/template/888062/build" target="_blank" type="button">Edit</a>
                            <p class="mj-notifications-from">
                                <span style="margin-right: 16px"><strong>From: &nbsp;</strong> <?php echo $fromName . ' &#60' .$fromMail . '&#62'; ?></span>
                                <span><strong>Subject: &nbsp;</strong>  <?= $shippingConfirmationSubject?></span>
                            </p>
                        </div>
                        <hr>
                        <div class="mailjet_row mj-notifications">
                            <label class="mj-order-notifications-labels">
                                <input class="checkbox mj-order-checkbox" <?=$shippingConfirmationBox?> name="mailjet_shipping_confirmation"
                                       type="checkbox"
                                       id="shipping_confirmation" value="1">
                                <section class="mj-checkbox-label">
                                    <?php _e('Shipping confirmation', 'mailjet-for-wordpress'); ?>
                                </section>
                            </label>
                            <div class="mj-badge" <?=$shippingConfirmationBadge?>><p>Sending active</p></div>
                            <button class="mj-btnSecondary mj-inrow" type="button">Edit</button>
                            <p class="mj-notifications-from">
                                <span style="margin-right: 16px"><strong>From: &nbsp;</strong>  <?php echo $fromName . '&nbsp; &#60' .$fromMail . '&#62'; ?></span>
                                <span><strong>Subject: &nbsp;</strong>  <?= $shippingConfirmationSubject?></span>
                            </p>
                        </div>
                        <hr>
                        <div class="mailjet_row mj-notifications">
                            <label class="mj-order-notifications-labels">
                                <input class="checkbox mj-order-checkbox" <?=$refundConfirmationBox?> name="mailjet_refund_confirmation"
                                       type="checkbox"
                                       id="refund_confirmation" value="1">
                                <section class="mj-checkbox-label">
                                    <?php _e('Refund confirmation', 'mailjet-for-wordpress'); ?>
                                </section>
                            </label>
                            <div class="mj-badge" <?=$refundConfirmationBadge?>><p>Sending active</p></div>
                            <button class="mj-btnSecondary mj-inrow" type="button">Edit</button>
                            <p class="mj-notifications-from">
                                <span style="margin-right: 16px"><strong>From: &nbsp;</strong> <?php echo $fromName . ' &#60' .$fromMail . '&#62'; ?></span>
                                <span><strong>Subject: &nbsp;</strong>  <?= $shippingConfirmationSubject?></span>
                            </p>
                        </div>
                        <hr>
                    </fieldset>
                    <div class="mailjet_row mj-pluginPage">
                        <button id="mj-order-notifications-submit" class="mj-btn btnPrimary mj-disabled" type="submit" style="margin-right: 16px">
                            Activate sending
                        </button>
                        <button id="stop-all" class="mj-btnSecondary hidden" data-toggle="show" onclick="toggleMailjetPopup(this)" type="button">Stop all sendings</button>
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
                    let btnStop = document.getElementById("stop-all");
                    let i;
                    let flag = false;
                    for (i = 0; i < form.length; i++) {
                        if (form[i].type === "checkbox" && form[i].checked) {
                            flag = true;
                        }
                    }

                    if (flag) {
                        btnSubmit.classList.remove('mj-disabled')
                        btnStop.classList.remove('hidden')
                    } else {
                        btnSubmit.classList.add('mj-disabled')
                        btnStop.classList.add('hidden')
                    }
                }

                function toggleMailjetPopup(element) {
                    let popupBox = document.getElementById('mj-popup');
                    let action = element.getAttribute('data-toggle');

                    if (action === 'show'){
                        popupBox.classList.remove('hidden')
                    } else {
                        popupBox.classList.add('hidden')
                    }
                }
            </script>
        </div>
        <?php
    }
}