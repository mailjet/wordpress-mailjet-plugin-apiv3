<?php


namespace MailjetPlugin\Includes\SettingsPages;


use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
use MailjetPlugin\Includes\MailjetApi;
use MailjetPlugin\Includes\MailjetLogger;
use MailjetPlugin\Includes\MailjetSettings;

class AbandonedCartSettings
{

    public function mailjet_abandoned_cart_settings_page_html()
    {
        $nonce = wp_create_nonce('mailjet_abandoned_cart_settings_page_html');

        $isAbandonedCartActivated = get_option('mailjet_woo_abandoned_cart_activate') === '1';
        $sendingTime = get_option('mailjet_woo_abandoned_cart_sending_time'); // time in seconds
        $sendingTimeScaleInMinutes = $sendingTime <= 3600; // scale in minutes if time <= 1h (60 * 60)
        $sendingTimeScaled = $sendingTimeScaleInMinutes ? $sendingTime / 60 : $sendingTime / 3600;
        $abandonedCartTemplate = WooCommerceSettings::getWooTemplate('mailjet_woocommerce_abandoned_cart');
        if (!$abandonedCartTemplate) {
            $wooCommerceSettings = WooCommerceSettings::getInstance();
            $templates = $wooCommerceSettings->createTemplates(true, false);
            if ($templates) {
                $abandonedCartTemplate = $templates['mailjet_woocommerce_abandoned_cart'];
            }
        }
        $postUpdateMsg = get_option('mailjet_post_update_message');
        $wasActivated = false;
        if (is_array($postUpdateMsg) && !is_null($postUpdateMsg['mjACWasActivated'])) {
            $wasActivated = get_option('mailjet_post_update_message')['mjACWasActivated'];
            if ($wasActivated) {
                update_option('mailjet_post_update_message', '');
            }
        }
        $templateRowTemplate = MAILJET_ADMIN_TAMPLATE_DIR . '/WooCommerceSettingsTemplates/rowTemplate.php';
        set_query_var('isEditModeAvailable', false);
    ?>
    <?php if ($wasActivated) { ?>
    <div class="mj-pluginPage mj-mask-popup" id="mj-popup-confirm-ac">
        <div class="mj-popup">
            <div class="mj-popup-header">
                <h1><?php _e('Sending active', 'mailjet-for-wordpress') ?></h1><span> <a class="buttons-desktop-04-icon-01-def" id="mj-close" href="#" data-toggle="hide" onclick="togglePopup('mj-popup-confirm-ac')"><svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="16px" width="16px" viewBox="0 0 16 16" style="vertical-align: middle;"><g><path d="M14.4 0L8 6.4 1.601 0 0 1.6l6.4 6.399-6.4 6.4L1.601 16 8 9.6l6.4 6.4 1.6-1.601-6.4-6.4L16 1.6z"></path></g></svg></a></span>
            </div>
            <hr>
            <div class="mj-popup-body">
                <div class="mj-popup-message">
                    <p><?php _e('Abandoned cart emails have been enabled for sending. You can change and stop the sending at any time.', 'mailjet-for-wordpress'); ?></p>
                </div>
            </div>
            <hr>
            <div class="mj-popup-footer mailjet_row">
                <button class="mj-btn btnPrimary" data-toggle="hide" onclick="togglePopup('mj-popup-confirm-ac')"><?php _e('Close', 'mailjet-for-wordpress'); ?></button>
            </div>
        </div>
    </div>
    <?php } ?>
    <?php if ($isAbandonedCartActivated) { ?>
    <div class="mj-pluginPage mj-mask-popup mj-hidden" id="mj-popup-stop-ac">
        <div class="mj-popup">
            <div class="mj-popup-header">
                <h1><?php _e('Stop sending', 'mailjet-for-wordpress') ?></h1><span> <a class="buttons-desktop-04-icon-01-def" id="mj-close" href="#" data-toggle="hide" onclick="togglePopup('mj-popup-stop-ac')"><svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="16px" width="16px" viewBox="0 0 16 16" style="vertical-align: middle;"><g><path d="M14.4 0L8 6.4 1.601 0 0 1.6l6.4 6.399-6.4 6.4L1.601 16 8 9.6l6.4 6.4 1.6-1.601-6.4-6.4L16 1.6z"></path></g></svg></a></span>
            </div>
            <hr>
            <div class="mj-popup-body">
                <div class="mj-popup-message">
                    <p><?php _e('By stopping the sending of abandoned cart emails, your visitors will no more be notified of the items they left inside their cart. Do you wish to stop the sending?', 'mailjet-for-wordpress'); ?></p>
                </div>
            </div>
            <hr>
            <div class="mj-popup-footer mailjet_row">
                <button id="mj-popup-stop-ac-btn" class="mj-btn btnPrimary" onclick=""><?php _e('Stop sending', 'mailjet-for-wordpress'); ?></button>
                <button class="mj-btnSecondary" data-toggle="hide" onclick="togglePopup('mj-popup-stop-ac')"><?php _e('Cancel', 'mailjet-for-wordpress'); ?></button>
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
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="abandoned-cart-form">
                <fieldset class="mj-form-content">
                    <div>
                        <div id="mj-top_bar">
                            <h1 class="page_top_title mj-template-labels"><?php _e('Abandoned cart', 'mailjet-for-wordpress'); ?> </h1>
                            <div class="mj-badge <?= !$isAbandonedCartActivated ? 'mj-hidden' : '' ?>"><p><?php _e('Sending active', 'mailjet-for-wordpress'); ?></p></div>
                        </div>
                        <p class="page_top_subtitle">
                            <?php _e('Recover visitors and turn them into customers by reminding them what they left in their carts.', 'mailjet-for-wordpress'); ?>
                        </p>
                        <button type="button" id="mj-ac-tip-btn" class="mj-toggleTextBtn mj-toggleBtn" data-target="mj-ac-tip-text"><?php _e('Who\'s going to receive this email?', 'mailjet-for-wordpress'); ?></button>
                        <div id="mj-ac-tip-text" class="mj-hide mj-ac-tip-text">
                            <p><?php _e('This email will be automatically sent to those customers that have added at least one item to the cart and then have left you store without completing the purchase.', 'mailjet-for-wordpress'); ?></p>
                            <!--<p><?php _e('We will send abandoned cart emails only to those customers that have accepted marketing communication and that are active.', 'mailjet-for-wordpress'); ?></p>-->
                        </div>
                    </div>
                    <div class="mailjet_row">
                        <h2>
                            <?php _e('Sending time', 'mailjet-for-wordpress'); ?>
                        </h2>
                        <div class="mj-time-setting">
                            <div id="sendingTimeInputs" <?= $isAbandonedCartActivated ? 'class="hidden"' : '' ?>>
                                <input type="number" id="timeInput" name="abandonedCartSendingTime" value="<?= $sendingTimeScaled ?>" />
                                <select id="abandonedCartTimeScale" name="abandonedCartTimeScale">
                                    <option value="MINUTES" <?= $sendingTimeScaleInMinutes ? 'selected' : '' ?>><?php _e('minutes', 'mailjet-for-wordpress'); ?></option>
                                    <option value="HOURS" <?= $sendingTimeScaleInMinutes ? '' : 'selected' ?>><?php _e('hours', 'mailjet-for-wordpress'); ?></option>
                                </select>
                            </div>
                            <p>
                                <strong id="abandonedCartTimeScaleTxt" <?= !$isAbandonedCartActivated ? 'class="hidden"' : '' ?>><?= $sendingTimeScaled . ' ' . ($sendingTimeScaleInMinutes ? __('minutes') : __('hours')) ?></strong>
                                <?php _e('after cart abandonment.', 'mailjet-for-wordpress'); ?>
                            </p>
                            <span id="linkSendingTimeSetting" <?= !$isAbandonedCartActivated ? 'class="hidden"' : '' ?>><a href="#" onclick="toggleTimeSettings(true)"><?php _e('Edit sending time', 'mailjet-for-wordpress') ?></a></span>
                            <div id="sendingTimeButtons" class="hidden">
                                <button id="mj-ac-edit-time" class="mj-btn btnPrimary">
                                    <?php _e('Save', 'mailjet-for-wordpress'); ?>
                                </button>
                                <button class="mj-btnSecondary" type="button" onclick="toggleTimeSettings(false)">
                                    <?php _e('Cancel', 'mailjet-for-wordpress'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h2>
                            <?php _e('Template', 'mailjet-for-wordpress'); ?>
                        </h2>
                    </div>
                    <hr>
                    <?php
                    set_query_var('title', __('Abandoned Cart', 'mailjet-for-wordpress'));
                    set_query_var('templateFrom', sprintf('%s &lt%s&gt', ($abandonedCartTemplate['Headers']['SenderName'] ?: ''), $abandonedCartTemplate['Headers']['SenderEmail']));
                    set_query_var('templateSubject', $abandonedCartTemplate['Headers']['Subject']);
                    set_query_var('templateLink', 'admin.php?page=mailjet_template&backto=abandonedcart&id=' . $abandonedCartTemplate['Headers']['ID']);
                    load_template($templateRowTemplate, false);
                    ?>
                    <hr>
                </fieldset>
                <div class="mailjet_row mj-row-btn">
                    <?php if (!$isAbandonedCartActivated) { ?>
                        <button id="mj-activate-ac-submit" class="mj-btn btnPrimary" type="submit" name="activate_ac" value="1">
                            <?php _e('Activate sending', 'mailjet-for-wordpress'); ?>
                        </button>
                    <?php }
                    else { ?>
                        <button id="mj-stop-ac-submit" class="mj-btnSecondary" type="submit" name="activate_ac" value="">
                            <?php _e('Stop sending', 'mailjet-for-wordpress'); ?>
                        </button>
                    <?php } ?>
                </div>
                <input type="hidden" name="action" value="abandoned_cart_settings_custom_hook">
                <input type="hidden" name="custom_nonce" value="<?=$nonce?>">
            </form>
        </div>
        <?php
        MailjetAdminDisplay::renderBottomLinks();
        ?>
        <script>
            let timeScaleSelect = document.getElementById("abandonedCartTimeScale");
            timeScaleSelect.addEventListener("change", changeTimeScale);
            let timeField = document.getElementById("timeInput");
            changeTimeScale();

            function changeTimeScale() {
                let max, min;
                if (timeScaleSelect.value == "HOURS") {
                    min = 1;
                    max = 48;
                }
                else {
                    min = 20;
                    max = 60;
                }
                timeField.setAttribute("min", min);
                timeField.setAttribute("max", max);
            }

            function toggleTimeSettings(show) {
                if (show) {
                    document.getElementById("linkSendingTimeSetting").classList.add("hidden");
                    document.getElementById("abandonedCartTimeScaleTxt").classList.add("hidden");
                    document.getElementById("sendingTimeButtons").classList.remove("hidden");
                    document.getElementById("sendingTimeInputs").classList.remove("hidden");
                }
                else {
                    document.getElementById("linkSendingTimeSetting").classList.remove("hidden");
                    document.getElementById("abandonedCartTimeScaleTxt").classList.remove("hidden");
                    document.getElementById("sendingTimeButtons").classList.add("hidden");
                    document.getElementById("sendingTimeInputs").classList.add("hidden");
                    // reset values in case of form submit after cancel
                    timeField.value = <?= $sendingTimeScaled ?>;
                    timeScaleSelect.value = <?= $sendingTimeScaleInMinutes ? '"MINUTES"' : '"HOURS"' ?>;
                }
            }

            function togglePopup(popupId) {
                let popupBox = document.getElementById(popupId);

                if (popupBox.classList.contains('mj-hidden')){
                    popupBox.classList.remove('mj-hidden')
                } else {
                    popupBox.classList.add('mj-hidden')
                }
            }

            <?php if ($isAbandonedCartActivated) { ?>
            // confirmation popup to stop sending
            let form = document.forms[0];
            form.onsubmit = submitListener;

            let displayPopup = false;
            document.getElementById("mj-stop-ac-submit").onclick = () => {displayPopup = true;};
            document.getElementById("mj-ac-edit-time").onclick = () => {displayPopup = false;};

            document.getElementById("mj-popup-stop-ac-btn").onclick = submitStopForm;

            function submitListener(e) {
                if (displayPopup) {
                    e.preventDefault();
                    document.getElementById("mj-popup-stop-ac").classList.remove("mj-hidden");
                    return false;
                }
                return true;
            }

            function submitStopForm() {
                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = "activate_ac";
                hiddenInput.value = "";
                form.appendChild(hiddenInput);
                form.submit();
            }
            <?php } ?>
        </script>
    </div>

    <?php
    }
}