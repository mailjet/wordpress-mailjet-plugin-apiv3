<?php

namespace MailjetWp;

$wpUsersCount        = get_query_var('wpUsersCount', $wpUsersCount);
$mailjetSyncList     = get_query_var('mailjetSyncList', $mailjetSyncList);
$mailjetContactLists = get_query_var('mailjetContactLists', $mailjetContactLists);
?>

<div id="activate_mailjet_sync_form" class="mj-show">
    <div class="mailjet_sync_options_div">
        <select class="mj-select" name="mailjet_sync_list" id="mailjet_sync_list" type="select">
            <?php
            foreach ($mailjetContactLists as $mailjetContactList) {
                if ($mailjetContactList['IsDeleted'] == \true) {
                    continue;
                }
                ?>
                <option value="
                <?php
                echo esc_attr($mailjetContactList['ID']);
                ?>
                " 
                <?php
				echo esc_attr($mailjetSyncList == $mailjetContactList['ID']) ? 'selected="selected"' : '';
				?>
> 
				<?php
				echo esc_attr($mailjetContactList['Name']);
				?>
(
				<?php
				echo esc_attr($mailjetContactList['SubscriberCount']);
				?>
) </option>
                <?php
            }
            ?>
        </select>
        <label class="checkboxLabel">
            <input name="activate_mailjet_initial_sync" type="checkbox" id="activate_mailjet_initial_sync" value="1" 
            <?php
            echo esc_attr($mailjetInitialSyncActivated) == 1 ? ' checked="checked"' : '';
            ?>
            >
            <span>
            <?php
            \printf(__('Also, add existing <b>%s WordPress users</b> (initial synchronization)', 'mailjet-for-wordpress'), $wpUsersCount);
            ?>
            </span>
        </label>
    </div>
</div><?php
