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
        $fromName = get_option('mailjet_from_name');

        $isAbandonedCartActivated = get_option('mailjet_woo_abandoned_cart_activate') === '1';
        $sendingTime = get_option('mailjet_woo_abandoned_cart_sending_time'); // time in seconds
        $sendingTimeScaleInMinutes = $sendingTime <= 3600; // scale in minutes if time <= 1h (60 * 60)
        $sendingTimeScaled = $sendingTimeScaleInMinutes ? $sendingTime / 60 : $sendingTime / 3600;
        $abandonedCartTemplate = WooCommerceSettings::getWooTemplate('mailjet_woocommerce_abandoned_cart');
    ?>
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
                <fieldset>
                    <div>
                        <h1 class="page_top_title mj-order-notifications-labels"><?php _e('Abandoned cart', 'mailjet-for-wordpress'); ?> </h1>
                        <div class="mj-badge <?= !$isAbandonedCartActivated ? 'mj-hidden' : '' ?>"><p><?php _e('Sending active', 'mailjet-for-wordpress'); ?></p></div>
                        <p class="page_top_subtitle">
                            <?php _e('Recover visitors and turn them into customers by reminding them what they left in their carts.', 'mailjet-for-wordpress'); ?>
                        </p>
                        <button type="button" id="mj-ac-tip-btn" class="mj-toggleTextBtn" data-toggle="mj-ac-tip-text"><?php _e('Who\'s going to receive this email?', 'mailjet-for-wordpress'); ?></button>
                        <div id="mj-ac-tip-text" class="mj-text-collapsible">
                            <p><?php _e('This email will be automatically sent to those customers that have added at least one item to the cart and then have left you store without completing the purchase.', 'mailjet-for-wordpress'); ?></p>
                            <p><?php _e('We will send abandoned cart emails only to those customers that have accepted marketing communication and that are active.', 'mailjet-for-wordpress'); ?></p>
                        </div>
                    </div>
                    <div>
                        <h2 class="section_inner_title">
                            <?php _e('Sending time', 'mailjet-for-wordpress'); ?>
                        </h2>
                    </div>
                    <div>
                        <div <?= $isAbandonedCartActivated ? 'class="hidden"' : '' ?>>
                            <input type="number" id="timeInput" name="abandonedCartSendingTime" value="<?= $sendingTimeScaled ?>" />
                            <select id="abandonedCartTimeScale" name="abandonedCartTimeScale">
                                <option value="MINUTES" <?= $sendingTimeScaleInMinutes ? 'selected' : '' ?>><?php _e('minutes', 'mailjet-for-wordpress'); ?></option>
                                <option value="HOURS" <?= $sendingTimeScaleInMinutes ? '' : 'selected' ?>><?php _e('hours', 'mailjet-for-wordpress'); ?></option>
                            </select>
                        </div>
                        <span id="abandonedCartTimeScaleTxt" <?= !$isAbandonedCartActivated ? 'class="hidden"' : '' ?>><strong><?= $sendingTimeScaled . ' ' . ($sendingTimeScaleInMinutes ? __('minutes') : __('hours')) ?></strong></span>
                        <span><?php _e('after cart abandonment.', 'mailjet-for-wordpress'); ?></span>
                        <span><a href="#" onclick="" <?= !$isAbandonedCartActivated ? 'class="hidden"' : '' ?>><?php _e('Edit sending time', 'mailjet-for-wordpress') ?></a></span>
                    </div>
                    <div>
                        <h2 class="section_inner_title">
                            <?php _e('Template', 'mailjet-for-wordpress'); ?>
                        </h2>
                    </div>
                    <hr>
                    <div class="mailjet_row mj-notifications">
                        <label class="mj-order-notifications-labels">
                            <section class="mj-checkbox-label">
                                <?php _e('Abandoned Cart', 'mailjet-for-wordpress'); ?>
                            </section>
                        </label>
                        <p class="mj-notifications-from">
                            <span style="margin-right: 16px"><strong>From: &nbsp;</strong> <?php echo $fromName . ' &#60' .$abandonedCartTemplate['Headers']['SenderEmail'] . '&#62'; ?></span>
                            <span><strong>Subject: &nbsp;</strong>  <?= $abandonedCartTemplate['Headers']['Subject'] ?></span>
                            <button class="mj-btnSecondary" href="https://app.mailjet.com/template/<?= $abandonedCartTemplate['Headers']['ID']?>/build" target="_blank">
                                <?php _e('Edit', 'mailjet-for-wordpress'); ?>
                            </button>
                        </p>
                    </div>
                    <hr>
                </fieldset>
                <div class="mailjet_row">
                    <?php if (!$isAbandonedCartActivated) { ?>
                        <button id="mj-activate-ac-submit" class="mj-btn btnPrimary" type="submit" name="activate_ac" value="1">
                            <?php _e('Activate sending', 'mailjet-for-wordpress'); ?>
                        </button>
                    <?php }
                    else { ?>
                        <button id="mj-activate-ac-submit" class="mj-btn mj-btnSecondary" type="submit" name="activate_ac" value="">
                            <?php _e('Stop sending', 'mailjet-for-wordpress'); ?>
                        </button>
                    <?php } ?>
                </div>
                <input type="hidden" name="action" value="abandoned_cart_settings_custom_hook">
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

            function displayTimeSettings() {

            }
        </script>
    </div>

    <?php
    }
}