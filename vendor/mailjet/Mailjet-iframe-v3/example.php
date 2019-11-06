<?php

require 'vendor/autoload.php';

use MailjetIframe\MailjetIframe;

/**
 *
 * Instantiate the iframe helper
 */
$mailjetIframe = new MailjetIframe(
    'YOUR-APIKEY',
    'YOUR-APISECRET'
);

/**
 *
 * Configure the iframe
 */
$mailjetIframe
    ->setCallback('')
    ->setTokenExpiration(600)
    ->setLocale('fr_FR')
    ->setTokenAccess(array(
        'campaigns',
        'contacts',
        'stats',
        'email_automation',
        'widget',
        'transactional',
    ))
    ->turnDocumentationProperties(MailjetIframe::OFF)
    ->turnNewContactListCreation(MailjetIframe::OFF)
    ->turnMenu(MailjetIframe::ON)
    ->turnFooter(MailjetIframe::OFF)
    ->turnBar(MailjetIframe::ON)
    ->turnCreateCampaignButton(MailjetIframe::ON)
    ->turnSendingPolicy(MailjetIframe::OFF)
    ->setInitialPage(MailjetIframe::PAGE_STATS);

/**
 *
 * Show the iframe wherever you want
 */
echo $mailjetIframe->getHtml();
