<?php
    use MailjetPlugin\Admin\Partials\MailjetAdminDisplay;

    $backButtonText = get_query_var('backButtonText');
    $backButtonLink = get_query_var('backButtonLink');
    $iframeHtml = get_query_var('iframeHtml');
?>
<div class="mj-pluginPage longIframePage">
    <div id="initialSettingsHead">
        <img src="<?= plugin_dir_url(dirname(dirname(dirname(__FILE__)))) ?>admin/images/LogoMJ_White_RVB.svg" alt="Mailjet Logo" />
    </div>
    <div class="mainContainer">
        <div class="backToDashboard">
            <a class="mj-btn btnCancel" href="<?= $backButtonLink ?>">
                <svg width="8" height="8" viewBox="0 0 16 16">
                    <path d="M7.89 11.047L4.933 7.881H16V5.119H4.934l2.955-3.166L6.067 0 0 6.5 6.067 13z"/>
                </svg>
                <?= $backButtonText ?>
            </a>
        </div>
        <h1 class="section_inner_title"><?= __('Edit template', 'mailjet-for-wordpress') ?></h1>
        <div class="iframeContainer">
            <?= $iframeHtml ?>
        </div>
    </div>
    <?php MailjetAdminDisplay::renderBottomLinks(); ?>
</div>
