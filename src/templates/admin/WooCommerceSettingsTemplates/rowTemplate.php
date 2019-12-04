<?php
$isEditModeAvailable = (isset($isEditModeAvailable) ? $isEditModeAvailable : false);
?>

<div class="mailjet_row mj-template-info">
    <div class="mj-template-info-header">
        <?php if ($isEditModeAvailable) { ?>
        <input class="checkbox mj-order-checkbox" <?= $isEditActive ? 'style="visibility: hidden"' : '' ?> <?= $isNotificationActive ? 'checked' : '' ?>
               name="<?= $checkboxName ?>"
               type="checkbox" id="<?= $checkboxId ?>" value="1">
        <?php } ?>
        <section class="mj-checkbox-label">
            <?= $title ?>
        </section>
        <?php if (isset($isNotificationActive)) { ?>
        <div class="mj-badge <?= $isNotificationActive ? '' : 'mj-hidden' ?>"><p><?php _e('Sending active', 'mailjet-for-wordpress'); ?></p></div>
        <?php } ?>
        <button class="mj-btnSecondary mj-inrow" onclick="location.href='<?= $templateLink ?>'" type="button">
            <?php _e('Edit', 'mailjet-for-wordpress'); ?>
        </button>
    </div>
    <div<?= $isEditModeAvailable ? ' class="mj-template-from"' : '' ?>>
        <span style="margin-right: 16px"><strong><?php _e('From:', 'mailjet-for-wordpress'); ?> &nbsp;</strong> <?= $templateFrom ?></span>
        <span><strong><?php _e('Subject:', 'mailjet-for-wordpress'); ?> &nbsp;</strong>  <?= $templateSubject ?></span>
    </div>
</div>
