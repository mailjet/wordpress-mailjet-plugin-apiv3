<div id="activate_mailjet_sync_form" class="<?=($mailjetSyncActivated == 1 ? ' mj-show' : 'mj-hide') ?>">
	<div class="mailjet_sync_options_div">
		<select class="mj-select" name="mailjet_sync_list" id="mailjet_sync_list" type="select">
			<?php
			foreach ($mailjetContactLists as $mailjetContactList) {
				if ($mailjetContactList["IsDeleted"] == true) {
					continue;
				}
				?>
				<option value="<?=$mailjetContactList['ID'] ?>" <?=($mailjetSyncList == $mailjetContactList['ID'] ? 'selected="selected"' : '') ?> > <?=$mailjetContactList['Name'] ?> (<?=$mailjetContactList['SubscriberCount'] ?>) </option>
				<?php
			} ?>
		</select>
		<label class="checkboxLabel">
			<input name="activate_mailjet_initial_sync" type="checkbox" id="activate_mailjet_initial_sync" value="1" <?=($mailjetInitialSyncActivated == 1 ? ' checked="checked"' : '') ?> >
			<span><?php echo sprintf(__('Also, add existing <b>%s Wordpress users</b> (initial synchronization)', 'mailjet-for-wordpress'), $wpUsersCount); ?></span>
		</label>
	</div>
</div>