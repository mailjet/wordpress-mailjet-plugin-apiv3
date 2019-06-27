<?php
$mailjetSyncActivated = get_query_var('mailjetSyncActivated');
$mailjetContactLists = get_query_var('mailjetContactLists');
$mailjetCommentAuthorsList = get_query_var('mailjetCommentAuthorsList');
$mailjetInitialSyncActivated = get_query_var('mailjetInitialSyncActivated');
$mailjetCommentAuthorsSyncActivated = get_query_var('mailjetCommentAuthorsSyncActivated');
$resyncBtn = $mailjetSyncActivated == 1 ? '&nbsp&nbsp<a href="#" onclick="ajaxResync()">Resync</a>' : '';
$changeList = $mailjetContactLists === 'No list selected' ? 'Select List' : 'Change List';
?>

<fieldset class="settingsSubscrFldset">
    <label class="mailjet-label" for="activate_mailjet_sync"><?php _e('Automatically add Wordpress users to a Mailjet list', 'mailjet-for-wordpress'); ?></label>
	<label class="checkboxLabel">
		<input name="activate_mailjet_sync" type="checkbox" id="activate_mailjet_sync" value="1" <?php echo ($mailjetSyncActivated == 1 ? ' checked="checked"' : '') ?>  autocomplete="off">
		<span><?php _e('Automatically add Wordpress users to a Mailjet list. Each user’s email address and role (subscriber, administrator, author, …) is synchronized to the list and available for use inside Mailjet.', 'mailjet-for-wordpress'); ?></span>
	</label>

	<div id="activate_mailjet_sync_form" class="mj-show">
		<div class="mailjet_sync_options_div">
			<div id="contact_list" class="mailjet_sync_woo_div">
				<div class="mj-woocommerce-contacts" id="div-for-ajax">
					<?= $mailjetContactLists ?>  <span><?= $resyncBtn?>&nbsp&nbsp<a href="#" onclick="loadLists()"><?= $changeList?></a></span>
				</div>
			</div>
		</div>
	</div>
	<label class="mailjet-label" for="activate_mailjet_comment_authors_sync">Opt-in inside « Leave a reply » form</label>
	<label class="checkboxLabel">
		<input name="activate_mailjet_comment_authors_sync" type="checkbox" id="activate_mailjet_comment_authors_sync" value="1" <?php echo ($mailjetCommentAuthorsSyncActivated == 1 ? ' checked="checked"' : '') ?> autocomplete="off">
		<span><?php _e('Display "Subscribe to our mailjet list" checkbox in the "Leave a reply" form to allow comment authors to join a specific contact list', 'mailjet-for-wordpress'); ?></span>
	</label>
</fieldset>

<input name="settings_step" type="hidden" id="settings_step" value="subscription_options_step">