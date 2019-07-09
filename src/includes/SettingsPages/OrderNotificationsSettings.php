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
        $wooCommerceExists = get_option('activate_mailjet_woo_integration') === '1' ? true : false;

        $notifications = get_option('mailjet_order_notifications');

        $orderConfirmationBox = !isset($notifications['mailjet_order_confirmation']) ? '' : 'checked="checked"'; // Processing Order
        $shippingConfirmationBox = !isset($notifications['mailjet_shipping_confirmation']) ? '' : 'checked="checked"'; //Order complected WC
        $refundConfirmationBox = !isset($notifications['mailjet_refund_confirmation']) ? '' : 'checked="checked"'; // Refunded order


        $orderConfTemplate = WooCommerceSettings::getWooTemplate('mailjet_woocommerce_order_confirmation');
        $abandonedCartTemplate = WooCommerceSettings::getWooTemplate('mailjet_woocommerce_abandoned_cart');
        $refundTemplate = WooCommerceSettings::getWooTemplate('mailjet_woocommerce_refund_confirmation');
        $shippingTemplate = WooCommerceSettings::getWooTemplate('mailjet_woocommerce_shipping_confirmation');
        $nonce = wp_create_nonce('mailjet_order_notifications_settings_page_html');


        if (!MailjetApi::isValidAPICredentials() || !$wooCommerceExists) {
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_dashboard_page'));
        }
        // check user capabilities
        if (!current_user_can('read')) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        $post_update = get_option('mailjet_post_update_message');

        if ($post_update) {
            update_option('mailjet_post_update_message', '');
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
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="order-notification-form">
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
                        <div style="width: fit-content">
                            <?php if ($post_update) {
                                echo $this->displayMessage($post_update);
                            }
                            ?>
                        </div>
                        <hr>
                        <div class="mailjet_row mj-notifications">
                            <label class="mj-order-notifications-labels">
                                <input class="checkbox mj-order-checkbox" style="visibility: hidden" <?=$orderConfirmationBox?> name="mailjet_wc_active_hooks[mailjet_order_confirmation]"
                                       type="checkbox"
                                       id="order_confirmation" value="1">
                                <section class="mj-checkbox-label">
                                    <?php _e('Order confirmation', 'mailjet-for-wordpress'); ?>
                                </section>
                            </label>
                            <div class="mj-badge <?= empty($orderConfirmationBox)? 'mj-hidden' : ''; ?>"><p>Sending active</p></div>
                            <a class="mj-btnSecondary mj-inrow mj-linkBtn" href="https://app.mailjet.com/template/<?= $orderConfTemplate['Headers']['ID']?>/build" target="_blank" type="button">Edit</a>
                            <p class="mj-notifications-from">
                                <span style="margin-right: 16px"><strong>From: &nbsp;</strong> <?php echo $fromName . ' &#60' .$orderConfTemplate['Headers']['SenderEmail'] . '&#62'; ?></span>
                                <span><strong>Subject: &nbsp;</strong>  <?= $orderConfTemplate['Headers']['Subject']?></span>
                            </p>
                        </div>
                        <hr>
                        <div class="mailjet_row mj-notifications">
                            <label class="mj-order-notifications-labels">
                                <input class="checkbox mj-order-checkbox"style="visibility: hidden" <?=$shippingConfirmationBox?> name="mailjet_wc_active_hooks[mailjet_shipping_confirmation]"
                                       type="checkbox"
                                       id="shipping_confirmation" value="1">
                                <section class="mj-checkbox-label">
                                    <?php _e('Shipping confirmation', 'mailjet-for-wordpress'); ?>
                                </section>
                            </label>
                            <div class="mj-badge <?= empty($shippingConfirmationBox)? 'mj-hidden' : ''; ?>"><p>Sending active</p></div>
                            <a class="mj-btnSecondary mj-inrow mj-linkBtn" href="https://app.mailjet.com/template/<?= $shippingTemplate['Headers']['ID']?>/build" target="_blank" type="button">Edit</a>
                            <p class="mj-notifications-from">
                                <span style="margin-right: 16px"><strong>From: &nbsp;</strong>  <?php echo $fromName . '&nbsp; &#60' .$shippingTemplate['Headers']['SenderEmail'] . '&#62'; ?></span>
                                <span><strong>Subject: &nbsp;</strong>  <?= $shippingTemplate['Headers']['Subject']?></span>
                            </p>
                        </div>
                        <hr>
                        <div class="mailjet_row mj-notifications">
                            <label class="mj-order-notifications-labels">
                                <input class="checkbox mj-order-checkbox" style="visibility: hidden" <?=$refundConfirmationBox?> name="mailjet_wc_active_hooks[mailjet_refund_confirmation]"
                                       type="checkbox"
                                       id="refund_confirmation" value="1">
                                <section class="mj-checkbox-label">
                                    <?php _e('Refund confirmation', 'mailjet-for-wordpress'); ?>
                                </section>
                            </label>
                            <div class="mj-badge <?= empty($refundConfirmationBox)? 'mj-hidden' : ''; ?>"><p>Sending active</p></div>
                            <a class="mj-btnSecondary mj-inrow mj-linkBtn" href="https://app.mailjet.com/template/<?= $refundTemplate['Headers']['ID']?>/build" target="_blank" type="button">Edit</a>
                            <p class="mj-notifications-from">
                                <span style="margin-right: 16px"><strong>From: &nbsp;</strong> <?php echo $fromName . ' &#60' .$refundTemplate['Headers']['SenderEmail'] . '&#62'; ?></span>
                                <span><strong>Subject: &nbsp;</strong>  <?= $refundTemplate['Headers']['Subject']?></span>
                            </p>
                        </div>
                        <hr>
                    </fieldset>
                    <div class="mailjet_row mj-pluginPage">
                        <button id="mj-order-notifications-submit" class="mj-btn btnPrimary mj-disabled" disabled type="submit" style="margin-right: 16px">
                            Activate sending
                        </button>
                        <button id="stop-all" class="mj-btnSecondary hidden "  data-toggle="show" onclick="toggleMailjetPopup(this)" type="button">Stop all sendings</button>
                        <button id="edit-all" class="mj-btnSecondary " data-toggle="visible" onclick="toggleEddit(this)" type="button">Edit Options</button>
                    </div>
                    <input type="hidden" name="action" value="order_notification_settings_custom_hook">
                    <input type="hidden" name="custom_nonce" value="<?=$nonce?>">
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
                        btnSubmit.disabled = false;
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

                function toggleEddit(element) {
                    let action = element.getAttribute('data-toggle');
                    let refund = document.getElementById("refund_confirmation");
                    let confirm = document.getElementById("order_confirmation");
                    let shipping = document.getElementById("shipping_confirmation");

                    refund.style.visibility = action;
                    confirm.style.visibility = action;
                    shipping.style.visibility = action;

                    if(action === 'visible'){
                        element.setAttribute('data-toggle', 'hidden')
                    }else {
                        element.setAttribute('data-toggle', 'visible')
                    }

                }
            </script>
        </div>
        <?php
    }

    private function displayMessage($data)
    {
        $type = $data['success'] === true ? 'notice-success' : 'notice-error';
        $msg = $data['message'];
        $div = "<div class=\"notice is-dismissible $type \" style=\"display: inline-block; height: 39px; width: 100%;\">
                    <p><strong>$msg</strong></p>
                    <button type=\"button\" class=\"notice-dismiss\"><span class=\"screen-reader-text\">Dismiss this notice.</span>
                    </button>
                </div>";

        return $div;
    }


}