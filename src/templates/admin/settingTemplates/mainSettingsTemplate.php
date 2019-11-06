<?php
use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
?>

<div class="mj-pluginPage">
	<div id="initialSettingsHead"><img src="<?php echo plugin_dir_url(MAILJET_PLUGIN_DIR . '/src') . '/src/admin/images/LogoMJ_White_RVB.svg';?>" alt="Mailjet Logo" /></div>
	<div class="mainContainer">

		<div class="backToDashboard">
			<a class="mj-btn btnCancel" href="admin.php?page=mailjet_dashboard_page">
				<svg width="8" height="8" viewBox="0 0 16 16"><path d="M7.89 11.047L4.933 7.881H16V5.119H4.934l2.955-3.166L6.067 0 0 6.5 6.067 13z"/></svg>
				<?php _e('Back to dashboard', 'mailjet-for-wordpress') ?>
			</a>
		</div>

		<h1 class="page_top_title"><?php _e('Settings', 'mailjet-for-wordpress') ?></h1>
		<div class="mjSettings">
			<div class="left">
				<?php
				MailjetAdminDisplay::getSettingsLeftMenu();
				?>
			</div>

			<div class="right">
				<div class="centered">
					<!--                    <h1>--><?php //echo esc_html(get_admin_page_title()); ?><!--</h1>-->
					<h2 class="section_inner_title"><?php echo __('Subscription options', 'mailjet-for-wordpress'); ?></h2>
					<form action="options.php" method="post">
						<?php
						// output security fields for the registered setting "mailjet"
						settings_fields('mailjet_subscription_options_page');
						// output setting sections and their fields
						// (sections are registered for "mailjet", each field is registered to a specific section)
						do_settings_sections('mailjet_subscription_options_page');
						// output save settings button
						$saveButton = __('Save', 'mailjet-for-wordpress');
						?>
						<button type="submit" id="subscriptionOptionsSubmit" onclick="sanitizeInput()" class="mj-btn btnPrimary MailjetSubmit" name="submit"><?= $saveButton; ?></button>
						<!-- <input name="cancelBtn" class="mj-btn btnCancel" type="button" id="cancelBtn" onClick="location.href=location.href" value="<?=__('Cancel', 'mailjet-for-wordpress')?>"> -->
					</form>
				</div>
			</div>
		</div>
	</div>
	<script>
        function sanitizeInput() {
            let autorsCheck = document.getElementById('activate_mailjet_comment_authors_sync');
            let syncCheck = document.getElementById('activate_mailjet_sync');

            if (autorsCheck.checked === false){
                document.getElementById('mailjet_comment_authors_list').value = '';
            }

            if (syncCheck.checked === false){
                document.getElementById('mailjet_sync_list').value = '';
            }
        }
	</script>
	<?php
	MailjetAdminDisplay::renderBottomLinks();
	?>
</div>
