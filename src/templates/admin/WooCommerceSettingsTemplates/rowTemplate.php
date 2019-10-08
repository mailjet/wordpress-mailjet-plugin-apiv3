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
        <div class="mj-badge <?= $isNotificationActive ? '' : 'mj-hidden' ?>"><p>Sending active</p></div>
        <button class="mj-btnSecondary mj-inrow" onclick="location.href='<?= $templateLink ?>'" type="button">
            <?php _e('Edit', 'mailjet-for-wordpress'); ?>
        </button>
    </div>
    <div<?= $isEditModeAvailable ? ' class="mj-template-from"' : '' ?>>
        <span style="margin-right: 16px"><strong>From: &nbsp;</strong> <?= $templateFrom ?></span>
        <span><strong>Subject: &nbsp;</strong>  <?= $templateSubject ?></span>
    </div>
</div>
