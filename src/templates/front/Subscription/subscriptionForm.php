<?php
$order = get_query_var('orderId');

$text = get_query_var('text');
$btnLabel = get_query_var('btnLabel');
?>
<div class="mj-front-container">
        <span id="mj-subscription-text"><?= $text ?></span>
        <button class="mj-subscribe-btn" data-order="<?= $order ?>" onclick="subscribeMe(this)"><?php _e($btnLabel, 'mailjet-for-wordpress')?></button>
</div>


