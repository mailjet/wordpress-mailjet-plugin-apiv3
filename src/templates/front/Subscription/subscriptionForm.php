<?php

namespace MailjetWp;

$order    = get_query_var('orderId');
$text     = get_query_var('text');
$btnLabel = get_query_var('btnLabel');
?>
<div class="mj-front-container">
        <span id="mj-subscription-text">
        <?php
        echo esc_textarea($text);
        ?>
        </span>
        <button class="mj-subscribe-btn" data-order="
        <?php
        echo esc_textarea($order);
        ?>
        " onclick="subscribeMe(this)">
        <?php
		_e($btnLabel, 'mailjet-for-wordpress');
		?>
</button>
</div>


<?php
