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
        $wooCommerceActivated = get_option('activate_mailjet_woo_integration') === '1' ? true : false;

        $notifications = get_option('mailjet_order_notifications');

        $isOrderConfirmationActive = isset($notifications['mailjet_order_confirmation']); // Processing Order
        $isShippingConfirmationActive = isset($notifications['mailjet_shipping_confirmation']); //Order completed WC
        $isRefundConfirmationActive = isset($notifications['mailjet_refund_confirmation']); // Refunded order

        $isEditActive = $isOrderConfirmationActive || $isShippingConfirmationActive || $isRefundConfirmationActive;

        $orderConfTemplate = WooCommerceSettings::getWooTemplate('mailjet_woocommerce_order_confirmation');
        $refundTemplate = WooCommerceSettings::getWooTemplate('mailjet_woocommerce_refund_confirmation');
        $shippingTemplate = WooCommerceSettings::getWooTemplate('mailjet_woocommerce_shipping_confirmation');
        if (!$orderConfTemplate || !$refundTemplate || !$shippingTemplate) {
            $wooCommerceSettings = WooCommerceSettings::getInstance();
            $templates = $wooCommerceSettings->createTemplates(false, true);
            if ($templates) {
                $orderConfTemplate = $templates['mailjet_woocommerce_order_confirmation'];
                $refundTemplate = $templates['mailjet_woocommerce_refund_confirmation'];
                $shippingTemplate = $templates['mailjet_woocommerce_shipping_confirmation'];
            }
        }

        $nonce = wp_create_nonce('mailjet_order_notifications_settings_page_html');

        if (!MailjetApi::isValidAPICredentials() || !$wooCommerceActivated) {
            MailjetSettings::redirectJs(admin_url('/admin.php?page=mailjet_dashboard_page'));
        }
        // check user capabilities
        if (!current_user_can('read')) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Current user don\'t have \`manage_options\` permission ]');
            return;
        }

        $showActivatePopup = false;
        $post_update = get_option('mailjet_post_update_message');
        if ($post_update) {
            if (isset($post_update['mj_order_notif_activate']) && $post_update['mj_order_notif_activate']) {
                $showActivatePopup = true;
            }
            update_option('mailjet_post_update_message', '');
        }

        $templateRowTemplate = MAILJET_ADMIN_TAMPLATE_DIR . '/WooCommerceSettingsTemplates/rowTemplate.php';
        set_query_var('isEditModeAvailable', true);
        set_query_var('isEditActive', $isEditActive);
        ?>
        <div class="mj-pluginPage mj-mask-popup mj-hidden" id="mj-popup_confirm_stop">
            <div class="mj-popup">
                <div class="mj-popup-header">
                    <h1><?php _e('Sending active', 'mailjet-for-wordpress') ?></h1><span> <a class="buttons-desktop-04-icon-01-def" id="mj-close" href="#" data-toggle="hide" onclick="togglePopup('mj-popup_confirm_stop')"><svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="16px" width="16px" viewBox="0 0 16 16" style="vertical-align: middle;"><g><path d="M14.4 0L8 6.4 1.601 0 0 1.6l6.4 6.399-6.4 6.4L1.601 16 8 9.6l6.4 6.4 1.6-1.601-6.4-6.4L16 1.6z"></path></g></svg></a></span>
                </div>
                <hr>
                <div class="mj-popup-body">
                    <div class="mj-popup-message mj-popup-info">
                        <svg width="20" height="20" viewBox="0 0 12 12">
                            <path d="M6 12a6 6 0 1 1 6-6 6 6 0 0 1-6 6zM6 1.2A4.8 4.8 0 1 0 10.8 6 4.81 4.81 0 0 0 6 1.2z"/><path d="M6.6 7.8V5.4a.6.6 0 0 0-.6-.6H4.8V6h.6v1.8H4.2V9h3.6V7.8z"/><circle cx="6" cy="3.6" r=".75"/>
                        </svg>
                        <p>Make sure to re-activate the emails inside WooCommerce (Settings > Emails) to ensure your customers get notified for their transactions.</p>
                    </div>
                    <div class="mj-popup-message">
                        <p>By stopping the sending of all order notification emails, your customers will no more receive communications related to their purchases. Do you wish to stop all sendings?</p>
                    </div>
                </div>
                <hr>
                <div class="mj-popup-footer mailjet_row">
                    <button id="mj-popup-stop-order-notif" class="mj-btn btnPrimary" type="button" onclick=""><?php _e('Stop all sendings', 'mailjet-for-wordpress'); ?></button>
                    <button class="mj-btnSecondary" data-toggle="hide" onclick="togglePopup('mj-popup_confirm_stop')"><?php _e('Cancel', 'mailjet-for-wordpress'); ?></button>
                </div>
            </div>
        </div>
        <?php if ($showActivatePopup) { ?>
            <div class="mj-pluginPage mj-mask-popup" id="mj-popup-info-notif">
                <div class="mj-popup">
                    <div class="mj-popup-header">
                        <h1><?php _e('Sending active', 'mailjet-for-wordpress') ?></h1><span> <a class="buttons-desktop-04-icon-01-def" id="mj-close" href="#" data-toggle="hide" onclick="togglePopup('mj-popup-info-notif')"><svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="16px" width="16px" viewBox="0 0 16 16" style="vertical-align: middle;"><g><path d="M14.4 0L8 6.4 1.601 0 0 1.6l6.4 6.399-6.4 6.4L1.601 16 8 9.6l6.4 6.4 1.6-1.601-6.4-6.4L16 1.6z"></path></g></svg></a></span>
                    </div>
                    <hr>
                    <div class="mj-popup-body">
                        <div class="mj-popup-message">
                            <p><?php _e('The transactional emails that you selected have been enabled for sending. You can change and stop the sending at any time.', 'mailjet-for-wordpress'); ?></p>
                        </div>
                        <div class="mj-popup-message mj-popup-info">
                            <svg width="20" height="20" viewBox="0 0 12 12">
                                <path d="M6 12a6 6 0 1 1 6-6 6 6 0 0 1-6 6zM6 1.2A4.8 4.8 0 1 0 10.8 6 4.81 4.81 0 0 0 6 1.2z"/><path d="M6.6 7.8V5.4a.6.6 0 0 0-.6-.6H4.8V6h.6v1.8H4.2V9h3.6V7.8z"/><circle cx="6" cy="3.6" r=".75"/>
                            </svg>
                            <p><?php _e('Make sure that the same notifications are deactivated inside WooCommerce (Settings > Emails) to avoid the reception of duplicate emails by your customers.', 'mailjet-for-wordpress'); ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="mj-popup-footer mailjet_row">
                        <button class="mj-btn btnPrimary" data-toggle="hide" onclick="togglePopup('mj-popup-info-notif')"><?php _e('Close', 'mailjet-for-wordpress'); ?></button>
                    </div>
                </div>
            </div>
        <?php } ?>


        <div class="mj-pluginPage">
            <div id="initialSettingsHead"><img
                        src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . '/admin/images/LogoMJ_White_RVB.svg'; ?>"
                        alt="Mailjet Logo"/></div>
            <div class="mainContainer dashboard">
                <div class="backToDashboard">
                    <a class="mj-btn btnCancel" href="admin.php?page=mailjet_dashboard_page">
                        <svg width="8" height="8" viewBox="0 0 16 16">
                            <path d="M7.89 11.047L4.933 7.881H16V5.119H4.934l2.955-3.166L6.067 0 0 6.5 6.067 13z"/>
                        </svg>
                        <?php _e('Back to dashboard', 'mailjet-for-wordpress') ?>
                    </a>
                </div>
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="order-notification-form">
                    <fieldset class="mj-form-content">
                        <div id="mj-top_bar">
                            <h1><?php _e('Order notification emails', 'mailjet-for-wordpress'); ?> </h1>
                        </div>
                        <p>
                            <?php _e('Enable the sending of order notification emails to make sure that your customers are always notified when a new purchase occurs and when their order has been shipped.', 'mailjet-for-wordpress'); ?>
                        </p>
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
                        <?php
                        set_query_var('title', __('Order confirmation', 'mailjet-for-wordpress'));
                        set_query_var('isNotificationActive', $isOrderConfirmationActive);
                        set_query_var('checkboxName', 'mailjet_wc_active_hooks[mailjet_order_confirmation]');
                        set_query_var('checkboxId', 'order_confirmation');
                        set_query_var('templateFrom', sprintf('%s &lt%s&gt', ($orderConfTemplate['Headers']['SenderName'] ?: ''), $orderConfTemplate['Headers']['SenderEmail']));
                        set_query_var('templateSubject', $orderConfTemplate['Headers']['Subject']);
                        set_query_var('templateLink', 'admin.php?page=mailjet_template&backto=ordernotif&id=' . $orderConfTemplate['Headers']['ID']);
                        load_template($templateRowTemplate, false);
                        ?>
                        <hr>
                        <?php
                        set_query_var('title', __('Shipping confirmation', 'mailjet-for-wordpress'));
                        set_query_var('isNotificationActive', $isShippingConfirmationActive);
                        set_query_var('checkboxName', 'mailjet_wc_active_hooks[mailjet_shipping_confirmation]');
                        set_query_var('checkboxId', 'shipping_confirmation');
                        set_query_var('templateFrom', sprintf('%s &lt%s&gt', ($shippingTemplate['Headers']['SenderName'] ?: ''), $shippingTemplate['Headers']['SenderEmail']));
                        set_query_var('templateSubject', $shippingTemplate['Headers']['Subject']);
                        set_query_var('templateLink', 'admin.php?page=mailjet_template&backto=ordernotif&id=' . $shippingTemplate['Headers']['ID']);
                        load_template($templateRowTemplate, false);
                        ?>
                        <hr>
                        <?php
                        set_query_var('title', __('Refund confirmation', 'mailjet-for-wordpress'));
                        set_query_var('isNotificationActive', $isRefundConfirmationActive);
                        set_query_var('checkboxName', 'mailjet_wc_active_hooks[mailjet_refund_confirmation]');
                        set_query_var('checkboxId', 'refund_confirmation');
                        set_query_var('templateFrom', sprintf('%s &lt%s&gt', ($refundTemplate['Headers']['SenderName'] ?: ''), $refundTemplate['Headers']['SenderEmail']));
                        set_query_var('templateSubject', $refundTemplate['Headers']['Subject']);
                        set_query_var('templateLink', 'admin.php?page=mailjet_template&backto=ordernotif&id=' . $refundTemplate['Headers']['ID']);
                        load_template($templateRowTemplate, false);
                        ?>
                        <hr>
                    </fieldset>
                    <div class="mailjet_row mj-row-btn">
                        <?php if (!$isEditActive) { ?>
                        <div>
                            <button id="mj-order-notifications-submit" class="mj-btn btnPrimary mj-disabled" disabled type="submit" name="submitAction" value="save">
                                <?= __('Activate sending', 'mailjet-for-wordpress') ?>
                            </button>
                        </div>
                        <?php } else { ?>
                        <div id="edit-not-active-buttons">
                            <button class="mj-btn btnPrimary mj-btnSpaced" onclick="toggleEdit(true)" type="button"><?= __('Edit sendings', 'mailjet-for-wordpress') ?></button>
                            <button id="mj-button-stop-order-notif" class="mj-btnSecondary "type="submit"><?= __('Stop all sendings', 'mailjet-for-wordpress') ?></button>
                        </div>
                        <div id="edit-active-buttons" class="hidden">
                            <button id="mj-button-edit-order-notif" class="mj-btn btnPrimary mj-btnSpaced" type="submit" name="submitAction" value="save"><?= __('Save changes', 'mailjet-for-wordpress') ?></button>
                            <button class="mj-btnSecondary " onclick="toggleEdit(false)" type="button"><?= __('Cancel', 'mailjet-for-wordpress') ?></button>
                        </div>
                        <?php } ?>
                    </div>
                    <input type="hidden" name="action" value="order_notification_settings_custom_hook">
                    <input type="hidden" name="custom_nonce" value="<?=$nonce?>">
                </form>
            </div>
            <?php
            MailjetAdminDisplay::renderBottomLinks();
            ?>
            <script>
                <?php if (!$isEditActive) { ?>
                    document.getElementById("refund_confirmation").addEventListener("click", submitListener);
                    document.getElementById("order_confirmation").addEventListener("click", submitListener);
                    document.getElementById("shipping_confirmation").addEventListener("click", submitListener);

                    function submitListener() {
                        let form = document.getElementById("order-notification-form");
                        let btnSubmit = document.getElementById("mj-order-notifications-submit");
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
                        } else {
                            btnSubmit.classList.add('mj-disabled')
                        }
                    }
                <?php } else { ?>
                    let displayPopup = false;
                    document.getElementById("mj-button-stop-order-notif").onclick = () => {displayPopup = true;};
                    document.getElementById("mj-button-edit-order-notif").onclick = () => {displayPopup = false;};
                    function submitListener(e) {
                        if (displayPopup) {
                            e.preventDefault();
                            togglePopup("mj-popup_confirm_stop");
                            return false;
                        }
                        return true;
                    }
                    let form = document.getElementById("order-notification-form");
                    form.onsubmit = submitListener;

                    function submitStopForm() {
                        let hiddenInput = document.createElement('input');
                        let form = document.getElementById("order-notification-form");
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = "submitAction";
                        hiddenInput.value = "stop";
                        form.appendChild(hiddenInput);
                        form.submit();
                    }
                    document.getElementById("mj-popup-stop-order-notif").onclick = submitStopForm;
                <?php } ?>

                function togglePopup(popupId) {
                    let popupBox = document.getElementById(popupId);

                    if (popupBox.classList.contains('mj-hidden')){
                        popupBox.classList.remove('mj-hidden')
                    } else {
                        popupBox.classList.add('mj-hidden')
                    }
                }

                function toggleEdit(show) {
                    let refund = document.getElementById("refund_confirmation");
                    let confirm = document.getElementById("order_confirmation");
                    let shipping = document.getElementById("shipping_confirmation");
                    let divEditActive = document.getElementById("edit-active-buttons");
                    let divEditNotActive = document.getElementById("edit-not-active-buttons");

                    let visibility;
                    if (show) {
                        visibility = "visible";
                        divEditActive.classList.remove('hidden');
                        divEditNotActive.classList.add('hidden');
                    }
                    else {
                        visibility = 'hidden';
                        divEditActive.classList.add('hidden');
                        divEditNotActive.classList.remove('hidden');
                    }

                    refund.style.visibility = visibility;
                    confirm.style.visibility = visibility;
                    shipping.style.visibility = visibility;
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