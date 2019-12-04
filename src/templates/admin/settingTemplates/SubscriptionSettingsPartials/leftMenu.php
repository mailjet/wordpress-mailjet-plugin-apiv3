<?php
$currentPage = get_query_var('currentPage');
?>
<ul>
	<li>
		<div class="settingsMenuLink settingsMenuLink1">
			<?php
			echo '<a data-img_id="settingsMenuLinkImg1" class="' . ($currentPage == 'mailjet_connect_account_page' ? 'active' : '') . '" href="admin.php?page=mailjet_connect_account_page">'; ?>
			<svg class="settingsMenuLinkImg1" width="16" viewBox="0 0 16 16"><defs><linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="b"><stop stop-color="#FFBC48" offset="0%"/><stop stop-color="#FFA414" offset="100%"/></linearGradient><rect id="a" width="16" height="16" rx="3"/></defs><g fill-rule="nonzero" fill="none"><g><use fill="#D8D8D8" xlink:href="#a"/><use fill="url(#b)" xlink:href="#a"/></g><path class="settingsMenuLinkImg1" d="M6.518 7.887l-.183 1.271-1.322 1.911.437-.244 7.208-3.965L14 6.118l-7.482 1.77zm-.051-.335l5.652-1.281-.366-.051-1.484-.203-3.152-.438L3 5l1.515 1.108 1.9 1.393.052.05z" fill="#FFFFFF"/></g></svg>
			<span><?php echo __('Connect your Mailjet account', 'mailjet-for-wordpress'); ?></span>
			</a>
		</div>
	</li>
	<li>
		<div class="settingsMenuLink settingsMenuLink2">
			<?php
			echo '<a data-img_id="settingsMenuLinkImg2" class="' . ($currentPage == 'mailjet_sending_settings_page' ? 'active' : '') . '" href="admin.php?page=mailjet_sending_settings_page">'; ?>
			<svg class="settingsMenuLinkImg2" width="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path class="settingsMenuLinkImg2" d="M16.002 16h-16v-1c0-3.533 3.29-6 8-6s8 2.467 8 6v1zM2.161 14h11.683c-.598-1.808-2.833-3-5.841-3s-5.244 1.192-5.842 3zm5.841-6c-2.206 0-4-1.794-4-4 0-2.205 1.794-4 4-4s4 1.795 4 4c0 2.206-1.794 4-4 4zm0-6c-1.103 0-2 .896-2 2 0 1.103.897 2 2 2s2-.897 2-2c0-1.104-.897-2-2-2z"/></svg>
			<span><?php echo __('Sending settings', 'mailjet-for-wordpress'); ?></span>
			</a>
		</div>
	</li>
	<li>
		<div class="settingsMenuLink settingsMenuLink3">
			<?php
			echo '<a data-img_id="settingsMenuLinkImg3" class="' . ($currentPage == 'mailjet_subscription_options_page' ? 'active' : '') . '" href="admin.php?page=mailjet_subscription_options_page">'; ?>
			<svg class="settingsMenuLinkImg3" width="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path class="settingsMenuLinkImg3" d="M5 6.4h4.7V8H5zm9.6-2.2L15.9 0l-4 1.4 2 .7zM5 3.9h6.2v1.6H5z"/><path class="settingsMenuLinkImg3" d="M3.5 2.3h6.9V.7H1.9v5.7h1.6z"/><path class="settingsMenuLinkImg3" d="M14.6 6.5L10 10.3H5.9L1.3 6.5c-.1-.1-.3-.1-.5-.1-.4 0-.8.3-.8.8v7.2c0 .9.7 1.6 1.6 1.6h12.7c.9 0 1.6-.7 1.6-1.6V7.2c0-.2-.1-.4-.2-.5-.3-.4-.8-.4-1.1-.2zm-13 7.8V8.9l3.5 2.9c.1.1.3.2.5.2h4.8c.2 0 .4-.1.5-.2l3.5-2.9v5.5H1.6z"/></svg>
			<span><?php echo __('Subscription options', 'mailjet-for-wordpress'); ?></span>
			</a>
		</div>
	</li>
	<li>
		<div class="settingsMenuLink settingsMenuLink4">
			<?php
			echo '<a data-img_id="settingsMenuLinkImg4" class="' . ($currentPage == 'mailjet_user_access_page' ? 'active' : '') . '" href="admin.php?page=mailjet_user_access_page">'; ?>
			<svg class="settingsMenuLinkImg4" width="16" xmlns="http://www.w3.org/2000/svg" viewBox="2 0 18 22"><g fill="none"><path d="M0 0h24v24H0V0z"/><path class="settingsMenuLinkImg4" opacity=".87" d="M0 0h24v24H0V0z"/></g><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm9 14H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
			<span><?php echo __('User access', 'mailjet-for-wordpress'); ?></span>
			</a>
		</div>
	</li>
	<li>
		<div class="settingsMenuLink settingsMenuLink4">
			<?php
			echo '<a data-img_id="settingsMenuLinkImg4" class="' . ($currentPage == 'mailjet_integrations_page' ? 'active' : '') . '" href="admin.php?page=mailjet_integrations_page">'; ?>
			<svg class="settingsMenuLinkImg5" xmlns="http://www.w3.org/2000/svg" width="16" viewBox="3 0 18 22"><path fill="none" d="M0 0h24v24H0V0z"/><path class="settingsMenuLinkImg5" d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92c0-1.61-1.31-2.92-2.92-2.92zM18 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM6 13c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm12 7.02c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/></svg>
			<span><?php echo __('Integrations', 'mailjet-for-wordpress'); ?></span>
			</a>
		</div>
	</li>
</ul>