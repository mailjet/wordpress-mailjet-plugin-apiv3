<?php

namespace MailjetWp;

use MailjetWp\MailjetPlugin\Admin\Partials\MailjetAdminDisplay;
$backButtonText = get_query_var('backButtonText');
$backButtonLink = get_query_var('backButtonLink');
$iframeHtml = get_query_var('iframeHtml');
?>
<div class="mj-pluginPage longIframePage">
    <div id="initialSettingsHead">
        <img src="<?php 
echo plugin_dir_url(dirname(__FILE__, 3));
?>admin/images/LogoMJ_White_RVB.svg" alt="Mailjet Logo" />
    </div>
    <div class="mainContainer">
        <div class="backToDashboard">
            <a class="mj-btn btnCancel" href="<?php 
echo esc_url($backButtonLink);
?>">
                <svg width="8" height="8" viewBox="0 0 16 16">
                    <path d="M7.89 11.047L4.933 7.881H16V5.119H4.934l2.955-3.166L6.067 0 0 6.5 6.067 13z"/>
                </svg>
                <?php 
echo esc_textarea($backButtonText);
?>
            </a>
        </div>
        <h1 class="section_inner_title"><?php 
echo __('Edit template', 'mailjet-for-wordpress');
?></h1>
        <div class="iframeContainer">
            <?php 
echo wp_kses($iframeHtml, [
    'iframe' => [
        'src'             => true,
        'height'          => true,
        'width'           => true,
        'frameborder'     => true,
        'allowfullscreen' => true,
    ]
]);
?>
        </div>
    </div>
    <?php 
MailjetAdminDisplay::renderBottomLinks();
?>
</div>
<?php 
