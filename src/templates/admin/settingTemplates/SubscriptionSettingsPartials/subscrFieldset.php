<?php
$wpUsersCount = get_query_var('wpUsersCount');
$mailjetContactLists = get_query_var('mailjetContactLists');
$mailjetSyncActivated = get_query_var('mailjetSyncActivated');
$mailjetSyncContactListId = get_query_var('mailjetSyncContactListId');
$mailjetSyncContactListName = get_query_var('mailjetSyncContactListName');
$mailjetCommentAuthorsList = get_query_var('mailjetCommentAuthorsList');
$mailjetInitialSyncActivated = get_query_var('mailjetInitialSyncActivated');
$mailjetCommentAuthorsSyncActivated = get_query_var('mailjetCommentAuthorsSyncActivated');
$resyncBtn = $mailjetSyncActivated == 1 ? '&nbsp&nbsp<a href="#" onclick="ajaxResync()">Resync</a>' : '';
$displaySyncListChoice = $mailjetSyncContactListId <= 0;
$changeList = ($displaySyncListChoice ? 'Select List' : 'Change List');
?>

<fieldset class="settingsSubscrFldset">
    <label class="mailjet-label" for="activate_mailjet_sync"><?php _e('Automatically add Wordpress users to a Mailjet list', 'mailjet-for-wordpress'); ?></label>
	<label class="checkboxLabel">
		<input name="activate_mailjet_sync" type="checkbox" id="activate_mailjet_sync" value="1" <?php echo ($mailjetSyncActivated == 1 ? ' checked="checked"' : '') ?>  autocomplete="off">
		<span><?php _e('Automatically add Wordpress users to a Mailjet list. Each user’s email address and role (subscriber, administrator, author, …) is synchronized to the list and available for use inside Mailjet.', 'mailjet-for-wordpress'); ?></span>
	</label>

	<div id="activate_mailjet_sync_form" class="<?= ($mailjetSyncActivated == 1 ? 'mj-show' : 'mj-hide') ?>">
		<div class="mailjet_sync_options_div">
			<div id="contact_list" class="mailjet_sync_woo_div">
				<div id="div-for-ajax" class="mj-woocommerce-contacts <?= ($displaySyncListChoice ? 'mj-hide' : 'mj-show') ?>">
					<?= $mailjetSyncContactListName ?>  <span><?= $resyncBtn?>&nbsp&nbsp<a href="#" onclick="displaySyncListChoice()"><?= $changeList?></a></span>
				</div>
                <div id="changeSyncList" <?= !$displaySyncListChoice ? 'class="mj-hide"' : '' ?>>
                    <label for="mailjet_sync_list" class="mj-wp-sync-label">Mailjet contact lists</label>
                    <select id="mailjet_sync_list" class="mj-select" name="mailjet_sync_list">
                        <?php
                        foreach ($mailjetContactLists as $mailjetContactList) {
                            if ($mailjetContactList["IsDeleted"] == true) {
                                continue;
                            }
                            ?>
                            <option value="<?= $mailjetContactList['ID'] ?>" <?= ($mailjetSyncContactListId == $mailjetContactList['ID'] ? 'selected="selected"' : '') ?> > <?= $mailjetContactList['Name'] ?>
                                (<?= $mailjetContactList['SubscriberCount'] ?>)
                            </option>
                        <?php }
                        ?>
                    </select>
                    <label id="checkbox-add-users" class="checkboxLabel">
                        <input name="activate_mailjet_initial_sync" type="checkbox" id="activate_mailjet_initial_sync" value="1" checked="checked">
                        <span><?php echo sprintf(__('Also, add existing %s Wordpress users (initial synchronization)', 'mailjet-for-wordpress'), $wpUsersCount); ?></span>
                    </label>
                </div>
			</div>
		</div>
	</div>
	<label class="mailjet-label" for="activate_mailjet_comment_authors_sync">Opt-in inside « Leave a reply » form</label>
	<label class="checkboxLabel">
		<input name="activate_mailjet_comment_authors_sync" type="checkbox" id="activate_mailjet_comment_authors_sync" value="1" <?php echo ($mailjetCommentAuthorsSyncActivated == 1 ? ' checked="checked"' : '') ?> autocomplete="off">
		<span><?php _e('Display "Subscribe to our mailjet list" checkbox in the "Leave a reply" form to allow comment authors to join a specific contact list', 'mailjet-for-wordpress'); ?></span>
	</label>
    <div id="comment_authors_contact_list" class="mailjet_sync_woo_div <?= ($mailjetCommentAuthorsSyncActivated == 1 ? 'mj-show' : 'mj-hide') ?>">
        <div class="mailjet_sync_options_div">
            <label for="mailjet_comment_authors_list" class="mj-wp-sync-label">Mailjet contact lists</label>
            <select id="mailjet_comment_authors_list" class="mj-select" name="mailjet_comment_authors_list">
                <?php
                foreach ($mailjetContactLists as $mailjetContactList) {
                    if ($mailjetContactList["IsDeleted"] == true) {
                        continue;
                    }
                    ?>
                    <option value="<?= $mailjetContactList['ID'] ?>" <?= ($mailjetCommentAuthorsList == $mailjetContactList['ID'] ? 'selected="selected"' : '') ?> > <?= $mailjetContactList['Name'] ?>
                        (<?= $mailjetContactList['SubscriberCount'] ?>)
                    </option>
                <?php }
                ?>
            </select>
        </div>
    </div>
</fieldset>

<input name="settings_step" type="hidden" id="settings_step" value="subscription_options_step">