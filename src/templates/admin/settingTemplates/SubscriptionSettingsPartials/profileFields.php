<?php
$checked = get_query_var('checked');
?>

<label class="mj-label" for="admin_bar_front">
	<input type="checkbox" name="mailjet_subscribe_ok" id="mailjet_subscribe_ok" value="1" <?php echo $checked ?>
		   class="checkbox" /><?php _e('Subscribe to our newsletter', 'mailjet-for-wordpress') ?>
</label>
<input type="hidden" id="mailjet_subscribe_extra_field" name="mailjet_subscribe_extra_field" value="on" />
</br>