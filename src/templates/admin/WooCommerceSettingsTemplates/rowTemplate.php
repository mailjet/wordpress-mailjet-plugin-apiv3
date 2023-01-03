<?php

namespace MailjetWp;

$isEditModeAvailable = isset($isEditModeAvailable) ? $isEditModeAvailable : \false;
?>

<div class="mailjet_row mj-template-info">
    <div class="mj-template-info-header">
        <?php 
if ($isEditModeAvailable) {
    ?>
        <input class="checkbox mj-order-checkbox" <?php 
    echo esc_attr($isEditActive) ? 'style="visibility: hidden"' : '';
    ?> <?php 
    echo esc_attr($isNotificationActive) ? 'checked' : '';
    ?>
               name="<?php 
    echo esc_attr($checkboxName);
    ?>"
               type="checkbox" id="<?php 
    echo esc_attr($checkboxId);
    ?>" value="1">
        <?php 
}
?>
        <section class="mj-checkbox-label">
            <?php 
echo esc_attr($title);
?>
        </section>
        <?php 
if (isset($isNotificationActive)) {
    ?>
        <div class="mj-badge <?php 
    echo esc_attr($isNotificationActive) ? '' : 'mj-hidden';
    ?>"><p><?php 
    _e('Sending active', 'mailjet-for-wordpress');
    ?></p></div>
        <?php 
}
?>
        <button class="mj-btnSecondary mj-inrow" onclick="location.href='<?php 
echo esc_attr($templateLink);
?>'" type="button">
            <?php 
_e('Edit', 'mailjet-for-wordpress');
?>
        </button>
    </div>
    <div<?php 
echo esc_attr($isEditModeAvailable) ? ' class="mj-template-from"' : '';
?>>
        <span style="margin-right: 16px"><strong><?php 
_e('From:', 'mailjet-for-wordpress');
?> &nbsp;</strong> <?php 
echo esc_attr($templateFrom);
?></span>
        <span><strong><?php 
_e('Subject:', 'mailjet-for-wordpress');
?> &nbsp;</strong>  <?php 
echo esc_attr($templateSubject);
?></span>
    </div>
</div>
<?php 
